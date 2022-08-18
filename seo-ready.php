<?php
/**
 * The `SEO Ready` bootstrap file.
 *
 * The plugin bootstrap file.
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * SEO Ready is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * @link                https://www.mypreview.one
 * @since               2.0.0
 * @package             seo-ready
 * @author              MyPreview (Github: @mahdiyazdani, @gooklani, @mypreview)
 * @copyright           © 2015 - 2022 MyPreview. All Rights Reserved.
 *
 * @wordpress-plugin
 * Plugin Name:         SEO Ready
 * Plugin URI:          https://www.mypreview.one
 * Description:         This plugin offers a GUI to generate most commonly used meta tags, so you can optimize your WordPress site for search engines… and do it in less time than it takes to brew a cup of coffee.
 * Version:             2.0.0
 * Requires at least:   5.5
 * Requires PHP:        7.4
 * Author:              Mahdi Yazdani
 * Author URI:          https://www.mahdiyazdani.com
 * License:             GPL-3.0+
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:         seo-ready
 * Domain Path:         /languages
 */

namespace Seo_Ready;

use function Seo_Ready\Includes\Utils\has_tags as has_tags;
use function Seo_Ready\Includes\Utils\get_localized_post_id as get_localized_post_id;

define(
	__NAMESPACE__ . '\PLUGIN',
	array(
		'basename' => plugin_basename( __FILE__ ),
		'dir_path' => untrailingslashit( plugin_dir_path( __FILE__ ) ),
		'dir_url'  => untrailingslashit( plugin_dir_url( __FILE__ ) ),
		'slug'     => 'seo-ready',
	)
);

require_once PLUGIN['dir_path'] . '/includes/utils.php';

/**
 * Load the plugin text domain for translation.
 *
 * @since     2.0.0
 * @return    void
 */
function textdomain(): void {
	load_plugin_textdomain( 'seo-ready', false, dirname( PLUGIN['basename'] ) . '/languages' );
}
add_action( 'init', __NAMESPACE__ . '\textdomain' );

/**
 * Registers custom meta keys for a specific combination of object type and object subtype.
 *
 * @since     2.0.0
 * @return    void
 */
function register_meta(): void {
	register_post_meta(
		'',
		'seo_ready',
		array(
			'single'        => true,
			'type'          => 'object',
			'show_in_rest'  => array(
				'schema' => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'title' => array(
							'sanitize_callback' => 'sanitize_text_field',
							'type'              => 'string',
						),
						'keywords' => array(
							'sanitize_callback' => 'sanitize_text_field',
							'type'              => 'string',
						),
						'description' => array(
							'sanitize_callback' => 'sanitize_text_field',
							'type'              => 'string',
						),
						'canonical' => array(
							'sanitize_callback' => 'sanitize_text_field',
							'type'              => 'string',
						),
						'noindex' => array(
							'sanitize_callback' => 'rest_sanitize_boolean',
							'type'              => 'boolean',
						),
						'nofollow' => array(
							'sanitize_callback' => 'rest_sanitize_boolean',
							'type'              => 'boolean',
						),
					),
				),
			),
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\register_meta' );

/**
 * Registers custom meta keys for a specific combination of object type and object subtype.
 *
 * @since     2.0.0
 * @return    void
 */
function print_tags(): void {
	// Retrieve ID of the current queried object.
	$meta = has_tags();

	if ( empty( $meta ) ) {
		return;
	}

	$print = '';
	// Keywords.
	$keywords = $meta['keywords'] ?? null;
	if ( ! is_null( $keywords ) ) {
		$print .= '<meta name="keywords" content="' . esc_html( $keywords ) . '" />' . PHP_EOL;
	}
	// Description.
	$description = $meta['description'] ?? null;
	if ( ! is_null( $description ) ) {
		$print .= '<meta name="description" content="' . esc_html( $description ) . '" />' . PHP_EOL;
	}
	// Canonical.
	$canonical = $meta['canonical'] ?? null;
	if ( ! is_null( $canonical ) ) {
		$print .= '<meta name="canonical" href="' . esc_url( $canonical ) . '" />' . PHP_EOL;
	}
	// No-Index.
	$noindex = $meta['noindex'] ?? false;
	if ( ! ! $noindex ) {
		$print .= '<meta name="robots" content="noindex" />' . PHP_EOL;
	}
	// No-Follow.
	$nofollow = $meta['nofollow'] ?? false;
	if ( ! ! $nofollow ) {
		$print .= '<meta name="robots" content="nofollow" />' . PHP_EOL;
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo apply_filters( 'seo_ready_wp_head_print_tags', $print, get_localized_post_id() );
}
add_action( 'wp_head', __NAMESPACE__ . '\print_tags' );

/**
 * Overrides the document title before it is generated.
 *
 * @since     2.0.0
 * @return    string
 */
function overwrite_title(): ?string {
	$return = null;
	$meta   = has_tags();

	if ( empty( $meta ) ) {
		return $return;
	}

	// Title.
	$title = $meta['title'] ?? null;
	if ( ! is_null( $title ) ) {
		// Replaces common plain text characters with formatted entities.
		$return = wptexturize( $title );
		// Converts lone & characters into &#038; (a.k.a. &amp;).
		$return = convert_chars( $return );
		// Escaping for HTML parts.
		$return = esc_html( $return );
		// Forever eliminate “WordPress” from the planet!!
		$return = capital_P_dangit( $return );
	}

	return $return;
}
add_filter( 'pre_get_document_title', __NAMESPACE__ . '\overwrite_title' );

/**
 * Enqueue the plugin script after block assets have been enqueued for the editing interface.
 *
 * @since     2.0.0
 * @return    void
 */
function enqueue(): void {
	$file_path       = PLUGIN['dir_path'] . '/build/index.js';
	$file_asset_path = PLUGIN['dir_path'] . '/build/index.asset.php';
	$asset           = file_exists( $file_asset_path ) ? require $file_asset_path : array(
		'dependencies' => $dependencies,
		'version'      => filemtime( $file_path ),
	);

	wp_enqueue_script( PLUGIN['slug'], PLUGIN['dir_url'] . '/build/index.js', $asset['dependencies'] ?? array(), $asset['version'] ?? '1.0', true );
	wp_set_script_translations( PLUGIN['slug'], 'seo-ready', PLUGIN['dir_path'] . '/languages/' );
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue' );

/**
 * Add additional helpful links to the plugin’s metadata.
 *
 * @since     2.0.0
 * @param     array  $links    An array of the plugin’s metadata.
 * @param     string $file     Path to the plugin file relative to the plugins directory.
 * @return    array
 */
function add_meta_links( array $links, string $file ): array {
	if ( PLUGIN['basename'] !== $file ) {
		return $links;
	}

	$plugin_links = array();
	/* translators: 1: Open anchor tag, 2: Close anchor tag. */
	$plugin_links[] = sprintf( _x( '%1$sCommunity support%2$s', 'plugin link', 'seo-ready' ), sprintf( '<a href="https://wordpress.org/support/plugin/%s" target="_blank" rel="noopener noreferrer nofollow">', PLUGIN['slug'] ), '</a>' );
	/* translators: 1: Open anchor tag, 2: Close anchor tag. */
	$plugin_links[] = sprintf( _x( '%1$sDonate%2$s', 'plugin link', 'seo-ready' ), sprintf( '<a href="https://www.buymeacoffee.com/mahdiyazdani" class="button-link-delete" target="_blank" rel="noopener noreferrer nofollow" title="%s">☕ ', esc_attr__( 'Donate to support this plugin', 'seo-ready' ) ), '</a>' );

	return array_merge( $links, $plugin_links );
}
add_filter( 'plugin_row_meta', __NAMESPACE__ . '\add_meta_links', 10, 2 );

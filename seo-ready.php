<?php
/**
 * The `SEO Ready` bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * You can redistribute this plugin/software and/or modify it under
 * the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * @link https://www.mypreview.one
 *
 * @since 1.0.0
 *
 * @package seo-ready
 *
 * @author MyPreview (Github: @mahdiyazdani, @gooklani, @mypreview)
 *
 * @copyright © 2015 - 2023 MyPreview. All Rights Reserved.
 *
 * @wordpress-plugin
 * Plugin Name: SEO Ready
 * Plugin URI: https://mypreview.one
 * Description: A lightweight SEO plugin to generate most commonly used meta tags. Designed for privacy, speed, and accessibility.
 * Version: 2.1.0
 * Author: MyPreview
 * Author URI: https://mypreview.one
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * License: GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: seo-ready
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

define( 'SEO_READY_VERSION', '2.1.0' );

/**
 * Load the plugin text domain for translation.
 *
 * @since 2.1.0
 *
 * @return void
 */
function seo_ready_textdomain() {

	load_plugin_textdomain( 'seo-ready', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'seo_ready_textdomain' );

/**
 * Registers custom meta keys for a specific combination of object type and object subtype.
 *
 * @since 2.0.1
 * @
 * @return void
 */
function seo_ready_register_meta() {

	$allowed_post_types = apply_filters( 'seo_ready_allowed_post_types', array( 'post', 'page' ) );

	// Bail early if no post types are allowed.
	if ( empty( $allowed_post_types ) ) {
		return;
	}

	foreach ( $allowed_post_types as $post_type ) {

		register_post_meta(
			$post_type,
			'seo_ready',
			array(
				'single'        => true,
				'type'          => 'object',
				'show_in_rest'  => array(
					'schema' => array(
						'type'                 => 'object',
						'additionalProperties' => false,
						'properties'           => seo_ready_get_properties(),
					),
				),
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'seo_ready_register_meta' );

/**
 * Registers custom meta keys for a specific combination of object type and object subtype.
 *
 * @since 2.0.0
 *
 * @return void
 */
function seo_ready_print_tags() {

	$post_id = seo_ready_get_localized_post_id();

	if ( ! seo_ready_is_post_exists( $post_id ) ) {
		return;
	}

	$post_meta = get_post_meta( $post_id, 'seo_ready', true );

	if ( ! is_array( $post_meta ) || empty( $post_meta ) ) {
		return;
	}

	$meta_tags = array(
		'<!-- This site is optimized with the SEO Ready plugin v' . esc_attr( SEO_READY_VERSION ) . ' - https://mypreview.one -->',
		'<meta name="generator" content="SEO Ready v' . esc_attr( SEO_READY_VERSION ) . '" class="seo-ready" />',
		'<meta property="og:locale" content="' . esc_attr( get_locale() ) . '" class="seo-ready" />',
		'<meta property="og:url" content="' . esc_url( get_permalink( $post_id ) ) . '" class="seo-ready" />',
		'<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="seo-ready" />',
		'<meta property="article:modified_time" content="' . esc_attr( get_the_modified_time( 'c', $post_id ) ) . '" class="seo-ready" />',
	);

	// Keywords.
	if ( ! empty( $post_meta['keywords'] ) ) {
		$meta_tags[] = '<meta name="keywords" content="' . esc_html( $post_meta['keywords'] ) . '" class="seo-ready" />';
	}

	// Description.
	if ( ! empty( $post_meta['description'] ) ) {
		$meta_tags[] = '<meta name="description" content="' . esc_html( $post_meta['description'] ) . '" class="seo-ready" />';
	}

	// Canonical.
	if ( ! empty( $post_meta['canonical'] ) ) {
		$meta_tags[] = '<link rel="canonical" href="' . esc_url( $post_meta['canonical'] ) . '" class="seo-ready" />';
	}

	// Noindex.
	if ( ! empty( $post_meta['noindex'] ) ) {
		$meta_tags[] = '<meta name="robots" content="noindex" class="seo-ready" />';
	}

	// Nofollow.
	if ( ! empty( $post_meta['nofollow'] ) ) {
		$meta_tags[] = '<meta name="robots" content="nofollow" class="seo-ready" />';
	}

	// Schema.
	if ( ! empty( $post_meta['schema_type'] ) ) {
		$meta_tags[] = '<meta itemprop="itemtype" content="http://schema.org/' . esc_html( $post_meta['schema_type'] ) . '" class="seo-ready" />';
	}

	// Schema.
	if ( ! empty( $post_meta['schema_article_type'] ) ) {
		$meta_tags[] = '<meta itemprop="articleType" content="' . esc_html( $post_meta['schema_article_type'] ) . '" class="seo-ready" />';
	}

	// Facebook image.
	if ( ! empty( $post_meta['facebook_image'] ) ) {
		$meta_tags[] = '<meta property="og:image" content="' . esc_url( wp_get_attachment_url( $post_meta['facebook_image'] ) ) . '" class="seo-ready" />';
	}

	// Facebook title.
	if ( ! empty( $post_meta['facebook_title'] ) ) {
		$meta_tags[] = '<meta property="og:title" content="' . esc_html( $post_meta['facebook_title'] ) . '" class="seo-ready" />';
	}

	// Facebook description.
	if ( ! empty( $post_meta['facebook_description'] ) ) {
		$meta_tags[] = '<meta property="og:description" content="' . esc_html( $post_meta['facebook_description'] ) . '" class="seo-ready" />';
	}

	// Twitter image.
	if ( ! empty( $post_meta['twitter_image'] ) ) {
		$meta_tags[] = '<meta name="twitter:image" content="' . esc_url( wp_get_attachment_url( $post_meta['twitter_image'] ) ) . '" class="seo-ready" />';
	}

	// Twitter title.
	if ( ! empty( $post_meta['twitter_title'] ) ) {
		$meta_tags[] = '<meta name="twitter:title" content="' . esc_html( $post_meta['twitter_title'] ) . '" class="seo-ready" />';
	}

	// Twitter description.
	if ( ! empty( $post_meta['twitter_description'] ) ) {
		$meta_tags[] = '<meta name="twitter:description" content="' . esc_html( $post_meta['twitter_description'] ) . '" class="seo-ready" />';
	}

	$meta_tags[] = '<!-- / SEO Ready -->';

	echo implode( "\n", $meta_tags ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'seo_ready_print_tags', 2 );

/**
 * Overrides the document title before it is generated.
 *
 * @since 2.0.0
 *
 * @param string $title Default document title.
 *
 * @return string
 */
function seo_ready_overwrite_title( $title ) {

	$post_id = seo_ready_get_localized_post_id();

	if ( ! seo_ready_is_post_exists( $post_id ) ) {
		return $title;
	}

	$post_meta = get_post_meta( $post_id, 'seo_ready', true );

	if ( ! is_array( $post_meta ) || empty( $post_meta ) || empty( $post_meta['title'] ) ) {
		return $title;
	}

	// Title.
	$title = $post_meta['title'];
	$title = esc_html( wptexturize( convert_chars( $title ) ) );

	return $title;
}
add_filter( 'pre_get_document_title', 'seo_ready_overwrite_title', 99 );

/**
 * Enqueue scripts and styles for the editor.
 *
 * @since 2.1.0
 * @
 * @return void
 */
function seo_ready_enqueue_editor() {

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : trailingslashit( 'minified' );

	wp_enqueue_script(
		'seo-ready-editor',
		untrailingslashit( plugin_dir_url( __FILE__ ) ) . "/assets/js/{$min}plugin.js",
		array( 'lodash', 'react', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-element', 'wp-i18n', 'wp-plugins', 'wp-primitives' ),
		'2.1.0',
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'seo_ready_enqueue_editor' );

/**
 * Registers custom meta keys for a specific combination of object type and object subtype.
 *
 * @since 2.1.0
 * @
 * @return array
 */
function seo_ready_get_properties() {

	return array(
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
		'schema_type' => array(
			'sanitize_callback' => 'sanitize_text_field',
			'type'              => 'string',
		),
		'schema_article_type' => array(
			'sanitize_callback' => 'sanitize_text_field',
			'type'              => 'string',
		),
		'facebook_image' => array(
			'sanitize_callback' => 'absint',
			'type'              => 'integer',
		),
		'facebook_title' => array(
			'sanitize_callback' => 'sanitize_text_field',
			'type'              => 'string',
		),
		'facebook_description' => array(
			'sanitize_callback' => 'sanitize_text_field',
			'type'              => 'string',
		),
		'twitter_image' => array(
			'sanitize_callback' => 'absint',
			'type'              => 'integer',
		),
		'twitter_title' => array(
			'sanitize_callback' => 'sanitize_text_field',
			'type'              => 'string',
		),
		'twitter_description' => array(
			'sanitize_callback' => 'sanitize_text_field',
			'type'              => 'string',
		),
	);
}

/**
 * Determines if a post, identified by the specified ID,
 * exist within the WordPress database.
 *
 * @since 2.0.0
 *
 * @param null|string $post_id Post ID.
 *
 * @return bool
 */
function seo_ready_is_post_exists( $post_id = '' ) {

	return ! empty( $post_id ) && is_string( get_post_type( $post_id ) );
}

/**
 * Retrieves post id of given post-object or currently queried object id.
 *
 * @since 2.0.0
 *
 * @param int|WP_Post|null $post Post ID or post object.
 *
 * @return int
 */
function seo_ready_get_post_id( $post = null ) {

	$get_post = get_post( $post, 'OBJECT' );

	if ( ! is_null( $get_post ) && property_exists( $get_post, 'ID' ) ) {
		return (int) $get_post->ID;
	}

	return (int) get_queried_object_id();
}

/**
 * Post id of the translation if exists, null otherwise.
 *
 * @since 2.0.0
 *
 * @param string $post_id Post ID.
 *
 * @return string
 */
function seo_ready_get_localized_post_id( $post_id = null ) {

	if ( is_null( $post_id ) ) {
		$post_id = seo_ready_get_post_id();
	}

	$post_id = strval( $post_id );

	if ( ! function_exists( 'pll_get_post' ) ) {
		return $post_id;
	}

	$pll_post_id = pll_get_post( $post_id );

	if ( ! is_null( $pll_post_id ) ) {
		return $pll_post_id;
	}

	return $post_id;
}

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

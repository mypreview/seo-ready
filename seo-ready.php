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
 * @copyright © 2015 - 2024 MyPreview. All Rights Reserved.
 *
 * @wordpress-plugin
 * Plugin Name: SEO Ready
 * Plugin URI: https://mypreview.one
 * Description: A lightweight SEO plugin to generate most commonly used meta tags. Designed for privacy, speed, and accessibility.
 * Version: 2.4.0
 * Author: MyPreview
 * Author URI: https://mypreview.one
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * License: GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: seo-ready
 * Domain Path: /languages
 */

use SEO_Ready\Breadcrumbs;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

define( 'SEO_READY_VERSION', '2.4.0' );

/**
 * Loads the PSR-4 autoloader implementation.
 */
require_once untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/vendor/autoload.php';

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
 * Add additional helpful links to the plugin’s metadata.
 *
 * @since 2.0.0
 *
 * @param array  $links An array of the plugin’s metadata.
 * @param string $file  Path to the plugin file relative to the plugins directory.
 *
 * @return array
 */
function seo_ready_add_meta_links( $links, $file ) {

	if ( plugin_basename( __FILE__ ) !== $file ) {
		return $links;
	}

	$plugin_links = array();
	/* translators: 1: Open anchor tag, 2: Close anchor tag. */
	$plugin_links[] = sprintf( _x( '%1$sCommunity support%2$s', 'plugin link', 'seo-ready' ), '<a href="https://wordpress.org/support/plugin/seo-ready" target="_blank" rel="noopener noreferrer nofollow">', '</a>' );
	/* translators: 1: Open anchor tag, 2: Close anchor tag. */
	$plugin_links[] = sprintf( _x( '%1$sDonate%2$s', 'plugin link', 'seo-ready' ), sprintf( '<a href="https://www.buymeacoffee.com/mahdiyazdani" class="button-link-delete" target="_blank" rel="noopener noreferrer nofollow" title="%s">☕ ', esc_attr__( 'Donate to support this plugin', 'seo-ready' ) ), '</a>' );

	return array_merge( $links, $plugin_links );
}
add_filter( 'plugin_row_meta', 'seo_ready_add_meta_links', 10, 2 );

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

	// Leave early if no post id is found.
	if ( ! seo_ready_is_post_exists( $post_id ) ) {
		return;
	}

	$post_type = get_post_type( $post_id );

	// Leave early if the current post type is not allowed.
	if ( ! in_array( $post_type, apply_filters( 'seo_ready_allowed_post_types', array( 'post', 'page' ) ), true ) || ! is_singular( $post_type ) ) {
		return;
	}

	$post_meta = get_post_meta( $post_id, 'seo_ready', true );

	// Leave early if no post meta is found.
	if ( ! is_array( $post_meta ) || empty( $post_meta ) ) {
		return;
	}

	$schema_types = array( 'WebPage' );
	$meta_tags[]  = '<!-- This site is optimized with the SEO Ready plugin v' . esc_attr( SEO_READY_VERSION ) . ' -->';

	// Keywords.
	if ( ! empty( $post_meta['keywords'] ) ) {
		$meta_tags[] = '<meta name="keywords" content="' . esc_html( $post_meta['keywords'] ) . '" class="seo-ready-meta-tag" />';
	}

	// Description.
	if ( ! empty( $post_meta['description'] ) ) {
		$meta_tags[] = '<meta name="description" content="' . esc_html( $post_meta['description'] ) . '" class="seo-ready-meta-tag" />';
	}

	// Noindex.
	if ( ! empty( $post_meta['noindex'] ) ) {
		$meta_tags[] = '<meta name="robots" content="noindex" class="seo-ready-meta-tag" />';
	}

	// Nofollow.
	if ( ! empty( $post_meta['nofollow'] ) ) {
		$meta_tags[] = '<meta name="robots" content="nofollow" class="seo-ready-meta-tag" />';
	}

	// Schema.
	if ( ! empty( $post_meta['schema_type'] ) ) {
		$schema_types[] = $post_meta['schema_type'];
		$meta_tags[]    = '<meta itemprop="itemtype" content="http://schema.org/' . esc_html( $post_meta['schema_type'] ) . '" class="seo-ready-meta-tag" />';
	}

	// Schema.
	if ( ! empty( $post_meta['schema_article_type'] ) ) {
		$meta_tags[] = '<meta itemprop="articleType" content="' . esc_html( $post_meta['schema_article_type'] ) . '" class="seo-ready-meta-tag" />';
	}

	$meta_tags[] = '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '" class="seo-ready-meta-tag" />';
	$meta_tags[] = '<meta property="og:url" content="' . esc_url( get_permalink( $post_id ) ) . '" class="seo-ready-meta-tag" />';
	$meta_tags[] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="seo-ready-meta-tag" />';

	// Facebook image.
	if ( ! empty( $post_meta['facebook_image'] ) ) {
		$meta_tags[] = '<meta property="og:image" content="' . esc_url( wp_get_attachment_url( $post_meta['facebook_image'] ) ) . '" class="seo-ready-meta-tag" />';
		$meta_tags[] = '<meta property="og:image:type" content="' . esc_attr( get_post_mime_type( $post_meta['facebook_image'] ) ) . '" class="seo-ready-meta-tag" />';
	}

	// Facebook title.
	if ( ! empty( $post_meta['facebook_title'] ) ) {
		$meta_tags[] = '<meta property="og:title" content="' . esc_html( $post_meta['facebook_title'] ) . '" class="seo-ready-meta-tag" />';
	}

	// Facebook description.
	if ( ! empty( $post_meta['facebook_description'] ) ) {
		$meta_tags[] = '<meta property="og:description" content="' . esc_html( $post_meta['facebook_description'] ) . '" class="seo-ready-meta-tag" />';
	}

	// Twitter image.
	if ( ! empty( $post_meta['twitter_image'] ) ) {
		$meta_tags[] = '<meta name="twitter:card" content="summary_large_image" class="seo-ready-meta-tag" />';
		$meta_tags[] = '<meta name="twitter:image" content="' . esc_url( wp_get_attachment_url( $post_meta['twitter_image'] ) ) . '" class="seo-ready-meta-tag" />';
	}

	// Twitter title.
	if ( ! empty( $post_meta['twitter_title'] ) ) {
		$estimated_reading_time_minutes = seo_ready_estimated_reading_time_minutes( $post_id );

		$meta_tags[] = '<meta name="twitter:title" content="' . esc_html( $post_meta['twitter_title'] ) . '" class="seo-ready-meta-tag" />';
		$meta_tags[] = '<meta name="twitter:label1" content="' . esc_html__( 'Est. reading time', 'seo-ready' ) . '" class="seo-ready-meta-tag" />';
		/* translators: %s: Estimated reading time in minutes. */
		$meta_tags[] = '<meta name="twitter:data1" content="' . sprintf( _n( '%s minute', '%s minutes', $estimated_reading_time_minutes, 'seo-ready' ), number_format_i18n( $estimated_reading_time_minutes ) ) . '" class="seo-ready-meta-tag" />';

		$meta_tags[] = '<meta name="twitter:label2" content="' . esc_html__( 'Written by', 'seo-ready' ) . '" class="seo-ready-meta-tag" />';
		$meta_tags[] = '<meta name="twitter:data2" content="' . esc_html( get_the_author() ) . '" class="seo-ready-meta-tag" />';
	}

	// Twitter description.
	if ( ! empty( $post_meta['twitter_description'] ) ) {
		$meta_tags[] = '<meta name="twitter:description" content="' . esc_html( $post_meta['twitter_description'] ) . '" class="seo-ready-meta-tag" />';
	}

	// Published and modified time.
	$meta_tags[] = '<meta property="article:published_time" content="' . esc_attr( get_the_time( 'c', $post_id ) ) . '" class="seo-ready-meta-tag" />';
	$meta_tags[] = '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_time( 'c', $post_id ) ) . '" class="seo-ready-meta-tag" />';

	// Canonical.
	$meta_tags[] = '<link rel="canonical" href="' . esc_url( $post_meta['canonical'] ?? get_permalink( $post_id ) ) . '" class="seo-ready-link-tag" />';

	// Redirect.
	if ( ! empty( $post_meta['redirect'] ) ) {
		$meta_tags[] = '<meta http-equiv="refresh" content="' . absint( $post_meta['redirect_delay'] ) . ';url=' . esc_url( $post_meta['redirect'] ) . '" class="seo-ready-meta-tag" />';
	}

	// Schema.
	$meta_tags[] = '<script type="application/ld+json" class="seo-ready-schema-graph">' . seo_ready_get_schema_json_ld( $schema_types, ! empty( $post_meta['schema_article_type'] ) ) . '</script>';

	/**
	 * Filters the SEO Ready meta tags.
	 *
	 * @since 2.0.0
	 *
	 * @param array $meta_tags Meta tags.
	 * @param int   $post_id   Post ID.
	 */
	$meta_tags = apply_filters( 'seo_ready_meta_tags', $meta_tags, $post_id );

	// Close.
	$meta_tags[] = '<!-- / SEO Ready -->';

	echo implode( "\n", $meta_tags ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'seo_ready_print_tags', 3 );

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

	// Leave early if no post id is found.
	if ( ! seo_ready_is_post_exists( $post_id ) ) {
		return $title;
	}

	$post_type = get_post_type( $post_id );

	// Leave early if the current post type is not allowed.
	if ( ! in_array( $post_type, apply_filters( 'seo_ready_allowed_post_types', array( 'post', 'page' ) ), true ) || ! is_singular( $post_type ) ) {
		return $title;
	}

	$post_meta = get_post_meta( $post_id, 'seo_ready', true );

	// Leave early if no post meta is found.
	if ( ! is_array( $post_meta ) || empty( $post_meta['title'] ) ) {
		return $title;
	}

	$custom_title = $post_meta['title'];

	return esc_html( wptexturize( convert_chars( $custom_title ) ) );
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
		'seo-ready-plugin',
		untrailingslashit( plugin_dir_url( __FILE__ ) ) . "/assets/js/{$min}plugin.js",
		array( 'lodash', 'react', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-element', 'wp-i18n', 'wp-plugins', 'wp-primitives' ),
		SEO_READY_VERSION,
		true
	);

	wp_enqueue_script(
		'seo-ready-breadcrumbs',
		untrailingslashit( plugin_dir_url( __FILE__ ) ) . "/assets/js/{$min}breadcrumbs.js",
		array( 'lodash', 'react', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n', 'wp-primitives', 'wp-html-entities' ),
		SEO_READY_VERSION,
		true
	);

	wp_enqueue_script(
		'seo-ready-faq',
		untrailingslashit( plugin_dir_url( __FILE__ ) ) . "/assets/js/{$min}faq.js",
		array( 'lodash', 'react', 'wp-components', 'wp-element', 'wp-i18n' ),
		SEO_READY_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'seo_ready_enqueue_editor' );

/**
 * Renders the breadcrumbs block.
 *
 * @since 2.3.0
 *
 * @param string $block_content The block content.
 * @param array  $block         The block.
 *
 * @return string
 */
function seo_ready_render_breadcrumbs_block( $block_content, $block ) {

	// Return early if in admin or the block is not 'seo-ready/breadcrumbs'.
	if ( is_admin() || 'seo-ready/breadcrumbs' !== $block['blockName'] ) {
		return $block_content;
	}

	$attributes = $block['attrs'] ?? array();
	$trails     = seo_ready_generate_breadcrumbs_trails( null, $attributes );

	// Return early if there are no breadcrumb trails to render.
	if ( empty( $trails ) ) {
		return $block_content;
	}

	$delimiter    = $attributes['delimiter'] ?? '→';
	$output       = '';
	$trails_count = count( $trails );

	foreach ( $trails as $index => $trail ) {
		$output .= '<li class="wp-block-seo-ready-breadcrumbs__crumb">';

		// Render the leading delimiter if conditions are met.
		if ( 0 === $index && ! ( $attributes['hideLeadingDelimiter'] ?? true ) && ! empty( $delimiter ) ) {
			$output .= sprintf(
				'<span class="wp-block-seo-ready-breadcrumbs__delimiter" style="margin-right:var(--wp--style--block-gap, 0.5em)">%s</span>',
				esc_html( $delimiter )
			);
		}

		// Determine if the current trail item should be a link or plain text.
		$is_last_trail = $index === $trails_count - 1;
		$output       .= $is_last_trail
			? sprintf( '<span class="wp-block-seo-ready-breadcrumbs__delimiter">%s</span>', esc_html( $trail[0] ) )
			: sprintf( '<a href="%s">%s</a>', esc_url( $trail[1] ), esc_html( $trail[0] ) );

		// Append a delimiter if it's not the last trail item.
		if ( ! $is_last_trail && ! empty( $delimiter ) ) {
			$output .= sprintf(
				'<span class="wp-block-seo-ready-breadcrumbs__delimiter" style="margin-left:var(--wp--style--block-gap, 0.5em)">%s</span>',
				esc_html( $delimiter )
			);
		}

		$output .= '</li>';
	}

	// Insert the breadcrumbs markup before closing the `<ol>` tag.
	return str_replace( '</ol>', $output . '</ol>', $block_content );
}
add_filter( 'render_block', 'seo_ready_render_breadcrumbs_block', 10, 2 );

/**
 * Renders the FAQ block.
 *
 * @since 2.4.0
 *
 * @param string $block_content The block content.
 * @param array  $block         The block.
 *
 * @return string
 */
function seo_ready_render_faq_block( $block_content, $block ) {

	// Return early if in admin or the block is not 'seo-ready/breadcrumbs'.
	if ( is_admin() || 'seo-ready/faq' !== $block['blockName'] ) {
		return $block_content;
	}

	seo_ready_generate_faq_page_item( $block['innerBlocks'] );

	return $block_content;
}
add_filter( 'render_block', 'seo_ready_render_faq_block', 10, 2 );

/**
 * Enqueue scripts and styles for the frontend.
 *
 * @since 2.1.0
 *
 * @param array $schema_types     Schema types. Default is `WebPage`.
 * @param bool  $has_person_graph Whether the graph has a person or not. Default is `false`.
 *
 * @return string
 */
function seo_ready_get_schema_json_ld( $schema_types = array( 'WebPage' ), $has_person_graph = false ) {

	$current_url = get_permalink();
	$blog_url    = get_bloginfo( 'url' );
	$blog_name   = get_bloginfo( 'name' );
	$language    = get_bloginfo( 'language' );

	// WebPage or other schema types.
	$webpage_itempage = array(
		'@type'           => array_unique( $schema_types ),
		'@id'             => $current_url,
		'url'             => $current_url,
		'name'            => get_the_title() . ' - ' . $blog_name,
		'isPartOf'        => array( '@id' => path_join( $blog_url, '#website' ) ),
		'datePublished'   => get_the_time( 'c' ),
		'dateModified'    => get_the_modified_time( 'c' ),
		'breadcrumb'      => array( '@id' => path_join( $current_url, '#breadcrumb' ) ),
		'inLanguage'      => $language,
		'potentialAction' => array(
			array(
				'@type'  => 'ReadAction',
				'target' => array( $current_url ),
			),
		),
	);

	// FAQPage schema type.
	$faq_main_entity = seo_ready_generate_faq_page_item();
	$faq_list        = ! empty( $faq_main_entity ) ? array(
		'@type'      => 'FAQPage',
		'@id'        => path_join( $current_url, '#faq' ),
		'url'        => $current_url,
		'mainEntity' => $faq_main_entity,
		'inLanguage' => $language,
		'isPartOf'   => array( '@id' => path_join( $blog_url, '#website' ) ),
		'breadcrumb' => array( '@id' => path_join( $current_url, '#breadcrumb' ) ),
	) : array();

	// BreadcrumbList schema type.
	$breadcrumb_list = array(
		'@type'           => 'BreadcrumbList',
		'@id'             => path_join( $current_url, '#breadcrumb' ),
		'itemListElement' => seo_ready_generate_breadcrumb_list_item(),
	);

	// WebSite schema type.
	$website = array(
		'@type'           => 'WebSite',
		'@id'             => path_join( $blog_url, '#website' ),
		'url'             => $blog_url,
		'name'            => $blog_name,
		'description'     => get_bloginfo( 'description' ),
		'potentialAction' => array(
			array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => path_join( $blog_url, '?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		),
		'inLanguage'      => $language,
	);

	// Person schema type if applicable.
	$person = array();
	if ( $has_person_graph ) {
		$author_id       = get_the_author_meta( 'ID' );
		$author_name     = get_the_author();
		$author_url      = get_author_posts_url( $author_id );
		$author_gravatar = get_avatar_url( get_the_author_meta( 'user_email' ), array( 'size' => 96 ) );

		$person = array(
			'@type'  => 'Person',
			'@id'    => path_join( path_join( $blog_url, '#/schema/person' ), seo_ready_get_current_user_hash( $author_id ) ),
			'name'   => $author_name,
			'image'  => array(
				'@type'      => 'ImageObject',
				'inLanguage' => $language,
				'@id'        => path_join( $author_url, '#/schema/person/image' ),
				'url'        => $author_gravatar,
				'contentUrl' => $author_gravatar,
				'caption'    => $author_name,
			),
			'sameAs' => array( $author_url ),
			'url'    => $author_url,
		);
	}

	// Final schema graph.
	$graph = array(
		'@context' => 'https://schema.org',
		'@graph'   => array_values( // Make sure the array is indexed.
			array_filter( // Remove empty values.
				array(
					$webpage_itempage,
					$faq_list,
					$breadcrumb_list,
					$website,
					$person,
				)
			)
		),
	);

	return wp_json_encode( $graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}

/**
 * Generates FAQPage item.
 *
 * @since 2.4.0
 *
 * @param array $faqs FAQ items.
 *
 * @return array
 */
function seo_ready_generate_faq_page_item( $faqs = array() ) {

	static $faq_page_item = array();

	if ( empty( $faqs ) ) {
		return $faq_page_item;
	}

	foreach ( $faqs as $position => $details_block ) {

		$answer   = '';
		$question = $details_block['innerHTML'];
		$answer   = $details_block['innerBlocks'][0]['innerHTML'];

		$faq_page_item[] = array(
			'@type'          => 'Question',
			'position'       => $position + 1,
			'answerCount'    => 1,
			'name'           => wp_strip_all_tags( $question ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( $answer ),
			),
		);
	}

	return $faq_page_item;
}

/**
 * Generates breadcrumb list item.
 *
 * @since 2.1.0
 *
 * @param array $trails Breadcrumb trails.
 *
 * @return array
 */
function seo_ready_generate_breadcrumb_list_item( $trails = array() ) {

	static $breadcrumb_list_item = array();

	if ( empty( $trails ) ) {
		return $breadcrumb_list_item;
	}

	foreach ( $trails as $position => $breadcrumb ) {
		$breadcrumb_list_item[] = array(
			'@type'    => 'ListItem',
			'position' => $position + 1,
			'name'     => $breadcrumb[0],
			'item'     => $breadcrumb[1],
		);
	}

	return $breadcrumb_list_item;
}

/**
 * Retrieves the hash of the current user.
 *
 * @since 2.1.0
 *
 * @param int $user_id User ID.
 *
 * @return string
 */
function seo_ready_get_current_user_hash( $user_id ) {

	$user = get_userdata( $user_id );

	if ( ! ( $user instanceof WP_User ) ) {
		return '';
	}

	return wp_hash( $user->user_login . $user->ID );
}

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
		'redirect' => array(
			'sanitize_callback' => 'sanitize_text_field',
			'type'              => 'string',
		),
		'redirect_delay' => array(
			'sanitize_callback' => 'absint',
			'type'              => 'integer',
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

	// Bail early if Polylang is not active.
	if ( ! function_exists( 'pll_get_post' ) ) {
		return $post_id;
	}

	$pll_post_id = pll_get_post( $post_id );

	if ( ! is_null( $pll_post_id ) ) {
		return $pll_post_id;
	}

	return $post_id;
}

/**
 * Retrieves the estimated reading time in minutes.
 *
 * @since 2.0.0
 *
 * @param int $post_id Post ID.
 *
 * @return int
 */
function seo_ready_estimated_reading_time_minutes( $post_id ) {

	$content    = get_post_field( 'post_content', $post_id );
	$word_count = str_word_count( wp_strip_all_tags( $content ) );

	// Average reading speed in words per minute.
	$words_per_minute = 200;

	// Calculate the estimated reading time in minutes.
	$reading_time_minutes = ceil( $word_count / $words_per_minute );

	return $reading_time_minutes;
}

/**
 * Generates breadcrumb trails.
 *
 * @since 2.3.0
 *
 * @param int   $post_id    Post ID.
 * @param array $attributes Attributes.
 *
 * @return array
 */
function seo_ready_generate_breadcrumbs_trails( $post_id = null, $attributes = array() ) {

	$breadcrumbs = new Breadcrumbs\Trails();

	// Prepend site title breadcrumb if available and set to show.
	$site_title          = get_bloginfo( 'name' );
	$site_title_override = $attributes['siteTitleOverride'] ?? '';

	if ( $site_title && empty( $attributes['hideSiteTitle'] ) ) {
		$site_title = ! empty( $site_title_override ) ? $site_title_override : $site_title;

		$breadcrumbs->add_crumb( $site_title, get_bloginfo( 'url' ) );
	}

	$trails = $breadcrumbs->generate();

	// Leave early if no trails are found.
	if ( count( $trails ) <= 1 ) {
		return array();
	}

	seo_ready_generate_breadcrumb_list_item( $trails );

	return $trails;
}


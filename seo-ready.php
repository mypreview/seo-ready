<?php
/**
 * The `SEO Ready` bootstrap file.
 *
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
 * @link                    https://www.mypreview.one
 * @since                   1.0.0
 * @package                 seo-ready
 * @author                  MyPreview (Github: @mahdiyazdani, @mypreview)
 * @copyright               © 2015 - 2020 MyPreview. All Rights Reserved.
 *
 * @wordpress-plugin
 * Plugin Name:             SEO Ready
 * Plugin URI:              https://www.mypreview.one
 * Description:             A lightweight SEO plugin to generate most commonly used meta tags, but designed for privacy, speed, and accessibility.
 * Version:                 1.0.0
 * Author:                  MyPreview
 * Author URI:              https://mahdiyazdani.com
 * License:                 GPL-3.0
 * License URI:             http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:             seo-ready
 * Domain Path:             /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die();
} // End If Statement

/**
 * Gets the path to a plugin file or directory.
 *
 * @see     https://codex.wordpress.org/Function_Reference/plugin_basename
 * @see     http://php.net/manual/en/language.constants.predefined.php
 */
$seo_ready_plugin_data = get_file_data(
	__FILE__,
	array(
		'name'       => 'Plugin Name',
		'author_uri' => 'Author URI',
		'version'    => 'Version',
	),
	'plugin'
);
define( 'SEO_READY_NAME', $seo_ready_plugin_data['name'] );
define( 'SEO_READY_VERSION', $seo_ready_plugin_data['version'] );
define( 'SEO_READY_AUTHOR_URI', $seo_ready_plugin_data['author_uri'] );
define( 'SEO_READY_SLUG', 'seo-ready' );
define( 'SEO_READY_PREFIX', 'seo_ready' );
define( 'SEO_READY_FILE', __FILE__ );
define( 'SEO_READY_BASENAME', basename( SEO_READY_FILE ) );
define( 'SEO_READY_PLUGIN_BASENAME', plugin_basename( SEO_READY_FILE ) );
define( 'SEO_READY_DIR_URL', plugin_dir_url( SEO_READY_FILE ) );
define( 'SEO_READY_DIR_PATH', plugin_dir_path( SEO_READY_FILE ) );

if ( ! class_exists( 'SEO_Ready' ) ) :

	/**
	 * The SEO Ready - Class
	 */
	final class SEO_Ready {

		/**
		 * Instance of the class.
		 *
		 * @var  object   $instance
		 */
		private static $instance = null;

		/**
		 * Main `SEO_Ready` instance
		 * Ensures only one instance of `SEO_Ready` is loaded or can be loaded.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @return   instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			} // End If Statement

			return self::$instance;
		}

		/**
		 * Setup class.
		 *
		 * @access   protected
		 * @since    1.0.0
		 * @return   void
		 */
		protected function __construct() {
			add_action( 'init', array( $this, 'textdomain' ) );
			add_action( 'init', array( $this, 'register_meta' ) );
			add_action( 'wp_head', array( $this, 'print_tags' ) );
			add_filter( 'pre_get_document_title', array( $this, 'overwrite_title' ) );
			add_action( 'enqueue_block_editor_assets', array( $this, 'editor_enqueue' ) );
			add_filter( sprintf( 'plugin_action_links_%s', SEO_READY_PLUGIN_BASENAME ), array( $this, 'additional_links' ) );
		}

		/**
		 * Cloning instances of this class is forbidden.
		 *
		 * @access   protected
		 * @since    1.0.0
		 * @return   void
		 */
		protected function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html_x( 'Cloning instances of this class is forbidden.', 'clone', 'seo-ready' ), esc_html( SEO_READY_VERSION ) );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @return   void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html_x( 'Unserializing instances of this class is forbidden.', 'wakeup', 'seo-ready' ), esc_html( SEO_READY_VERSION ) );
		}

		/**
		 * Load languages file and text domains.
		 * Define the internationalization functionality.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @return   void
		 */
		public function textdomain() {
			load_plugin_textdomain( 'seo-ready', false, dirname( dirname( SEO_READY_PLUGIN_BASENAME ) ) . '/languages/' );
		}

		/**
		 * Registers custom meta keys for a specific
		 * combination of object type and object subtype.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @return   void
		 */
		public function register_meta() {
			register_post_meta(
				'',
				SEO_READY_PREFIX,
				array(
					'single'        => true,
					'type'          => 'object',
					'show_in_rest'  => array(
						'schema' => array(
							'type'                 => 'object',
							'additionalProperties' => false,
							'properties'           => array(
								'title' => array(
									'type' => 'string',
								),
								'keywords' => array(
									'type' => 'string',
								),
								'description' => array(
									'type' => 'string',
								),
								'canonical' => array(
									'type' => 'string',
								),
								'noindex' => array(
									'type' => 'boolean',
								),
								'nofollow' => array(
									'type' => 'boolean',
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

		/**
		 * Meta tag template used for search engine optimization.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @return   void
		 */
		public function print_tags() {
			// Retrieve ID of the current queried object.
			$metas = self::has_tags();

			if ( is_array( $metas ) ) {
				$output = '';
				// Keywords.
				$keywords = isset( $metas['keywords'] ) ? $metas['keywords'] : null;
				if ( ! is_null( $keywords ) ) {
					$output .= sprintf( '<meta name="keywords" content="%s" />%s', esc_html( $keywords ), PHP_EOL );
				}
				// Description.
				$description = isset( $metas['description'] ) ? $metas['description'] : null;
				if ( ! is_null( $description ) ) {
					$output .= sprintf( '<meta name="description" content="%s" />%s', esc_html( $description ), PHP_EOL );
				}
				// Canonical.
				$canonical = isset( $metas['canonical'] ) ? $metas['canonical'] : null;
				if ( ! is_null( $canonical ) ) {
					$output .= sprintf( '<meta name="canonical" href="%s" />%s', esc_url( $canonical ), PHP_EOL );
				}
				// No-Index.
				$noindex = isset( $metas['noindex'] ) ? $metas['noindex'] : false;
				if ( ! ! $noindex ) {
					$output .= sprintf( '<meta name="robots" content="noindex" />%s', PHP_EOL );
				}
				// No-Follow.
				$nofollow = isset( $metas['nofollow'] ) ? $metas['nofollow'] : false;
				if ( ! ! $nofollow ) {
					$output .= sprintf( '<meta name="robots" content="nofollow" />%s', PHP_EOL );
				}

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.ValidHookName.UseUnderscores, WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
				echo apply_filters( sprintf( '%s_print_tags', SEO_READY_PREFIX ), $output );
			}
		}

		/**
		 * Overrides the document title before it is generated.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @param    string $document_title      The document title.
		 * @return   string
		 */
		public function overwrite_title( $document_title ) {
			// Retrieve ID of the current queried object.
			$metas = self::has_tags();

			if ( is_array( $metas ) ) {
				// Title.
				$title = isset( $metas['title'] ) ? $metas['title'] : null;
				if ( ! is_null( $title ) ) {
					// Replaces common plain text characters with formatted entities.
					$document_title = wptexturize( $title );
					// Converts lone & characters into &#038; (a.k.a. &amp;).
					$document_title = convert_chars( $document_title );
					// Escaping for HTML parts.
					$document_title = esc_html( $document_title );
					// Forever eliminate “WordPress” from the planet!!
					$document_title = capital_P_dangit( $document_title );
				}
			}

			return $document_title;
		}

		/**
		 * Register the stylesheets and JavaScript for the Gutenberg (editor) side of the site.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @return   void
		 */
		public function editor_enqueue() {
			$script_path       = sprintf( '%sdist/script.js', SEO_READY_DIR_PATH );
			$script_asset_path = sprintf( '%sdist/script.asset.php', SEO_READY_DIR_PATH );
			$script_asset      = file_exists( $script_asset_path ) ? require $script_asset_path : array(
				'dependencies' => array( 'wp-blocks', 'wp-dom-ready', 'lodash' ),
				'version'      => filemtime( $script_path ),
			);
			$script_url        = sprintf( '%sdist/script.js', SEO_READY_DIR_URL );
			// Enqueue the script.
			wp_enqueue_script( SEO_READY_SLUG, $script_url, $script_asset['dependencies'], $script_asset['version'], true );
			wp_set_script_translations( SEO_READY_SLUG, 'seo-ready', sprintf( '%s/languages/', SEO_READY_DIR_PATH ) );
			wp_localize_script(
				SEO_READY_SLUG,
				SEO_READY_PREFIX,
				array(
					'author_uri'  => SEO_READY_AUTHOR_URI,
				)
			);
		}

		/**
		 * Display additional links in plugins table page.
		 * Filters the list of action links displayed for a specific plugin in the Plugins list table.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @param    array $links      An array of plugin action links.
		 * @return   array
		 */
		public function additional_links( $links ) {
			$plugin_links = array();
			/* translators: 1: Open anchor tag, 2: Close anchor tag. */
			$plugin_links[] = sprintf( _x( '%1$sHire Me!%2$s', 'plugin link', 'seo-ready' ), sprintf( '<a href="%s" class="button-link-delete" target="_blank" rel="noopener noreferrer nofollow" title="%s">', esc_url( SEO_READY_AUTHOR_URI ), esc_attr_x( 'Looking for help? Hire Me!', 'upsell', 'seo-ready' ) ), '</a>' );
			/* translators: 1: Open anchor tag, 2: Close anchor tag. */
			$plugin_links[] = sprintf( _x( '%1$sSupport%2$s', 'plugin link', 'seo-ready' ), sprintf( '<a href="https://wordpress.org/support/plugin/%s" target="_blank" rel="noopener noreferrer nofollow">', SEO_READY_SLUG ), '</a>' );

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Whether there are any SEO meta tags to print-out?
		 * If so, then return a list of available tags/values.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @return   bool|array
		 */
		public static function has_tags() {
			// Retrieve ID of the current queried object.
			$object_id = (int) get_queried_object_id();

			if ( $object_id ) {
				// Retrieves the SEO post meta field for the queried post ID.
				$metas = get_post_meta( $object_id, SEO_READY_PREFIX, true );
				if ( is_array( $metas ) ) {
					return $metas;
				}
			}

			return false;
		}

	}
endif;

if ( ! function_exists( 'seo_ready_init' ) ) :

	/**
	 * Returns the main instance of SEO_Ready to prevent the need to use globals.
	 *
	 * @return  object(class)   SEO_Ready::instance
	 */
	function seo_ready_init() {
		return SEO_Ready::instance();
	}

	seo_ready_init();
endif;

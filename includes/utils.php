<?php
/**
 * Helper functions.
 *
 * @link          https://www.mypreview.one
 * @author        MyPreview (Github: @mahdiyazdani, @gooklani, @mypreview)
 * @since         2.0.0
 *
 * @package       seo-ready
 * @subpackage    seo-ready/includes
 */

namespace Seo_Ready\Includes\Utils;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'has_tags' ) ) :
	/**
	 * Whether there are any SEO meta tags to print-out?
	 * If so, then return a list of available tags/values.
	 *
	 * @since     2.0.0
	 * @return    array
	 */
	function has_tags(): array {
		$return  = array();
		$post_id = get_localized_post_id();

		if ( $post_id ) {
			$meta = get_post_meta( $post_id, 'seo_ready', true );

			if ( is_array( $meta ) ) {
				$return = $meta;
			}
		}

		return $return;
	}
endif;

if ( ! function_exists( 'is_plugin_activated' ) ) :
	/**
	 * Query a third-party plugin activation.
	 * This statement prevents from producing fatal errors,
	 * in case the the plugin is not activated on the site.
	 *
	 * @since     2.0.0
	 * @param     string $slug        Plugin slug to check for the activation state.
	 * @param     string $filename    Optional. Plugin’s main file name.
	 * @return    bool
     * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	 */
	function is_plugin_activated( string $slug, string $filename = '' ): bool {
		$filename               = empty( $filename ) ? $slug : $filename;
		$plugin_path            = apply_filters( 'seo_ready_third_party_plugin_path', trailingslashit( esc_html( $slug ) ) . esc_html( $filename ) . '.php' );
		$subsite_active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		$network_active_plugins = apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins' ) );

		// Bail early in case the plugin is not activated on the website.
        // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( ( empty( $subsite_active_plugins ) || ! in_array( $plugin_path, $subsite_active_plugins ) ) && ( empty( $network_active_plugins ) || ! array_key_exists( $plugin_path, $network_active_plugins ) ) ) {
			return false;
		}

		return true;
	}
endif;

if ( ! function_exists( 'is_polylang_activated' ) ) :
	/**
	 * Return `true` if "Polylang" is installed/activated and `false` otherwise.
	 *
	 * @since     2.0.0
	 * @return    bool
	 */
	function is_polylang_activated(): bool {
		return is_plugin_activated( 'polylang' ) || is_plugin_activated( 'polylang-pro', 'polylang' );
	}
endif;

if ( ! function_exists( 'is_post_exists' ) ) :
	/**
	 * Determines if a post, identified by the specified ID,
	 * exist within the WordPress database.
	 *
	 * @since     2.0.0
	 * @param     null|string $post_id    Post ID.
	 * @return    bool
	 */
	function is_post_exists( ?string $post_id = '' ): bool {
		return ! empty( $post_id ) && is_string( get_post_type( $post_id ) );
	}
endif;

if ( ! function_exists( 'get_post_id' ) ) :
	/**
	 * Retrieves post id of given post-object or currently queried object id.
	 *
	 * @since     2.0.0
	 * @param     int|WP_Post|null $post    Post ID or post object.
	 * @return    int
	 */
	function get_post_id( $post = null ): ?int {
		$post_id  = null;
		$get_post = get_post( $post, 'OBJECT' );

		if ( is_null( $get_post ) ) {
			$post_id = (int) get_queried_object_id();
		} elseif ( property_exists( $get_post, 'ID' ) ) {
			$post_id = (int) $get_post->ID;
		}

		return $post_id;
	}
endif;

if ( ! function_exists( 'get_localized_post_id' ) ) :
	/**
	 * Post id of the translation if exists, null otherwise.
	 *
	 * @since     2.0.0
	 * @param     null|string $post_id    Post ID.
	 * @return    null|string
	 */
	function get_localized_post_id( ?string $post_id = null ): ?string {
		$return  = null;
		$post_id = is_null( $post_id ) ? get_post_id( $post_id ) : strval( $post_id );

		if ( is_post_exists( $post_id ) ) {
			$return = $post_id;
			if ( is_polylang_activated() ) {
				$pll_post_id = pll_get_post( $post_id );
				if ( $pll_page_id && ! is_null( $pll_page_id ) ) {
					$return = $pll_post_id;
				}
			}
		}

		return $return;
	}
endif;

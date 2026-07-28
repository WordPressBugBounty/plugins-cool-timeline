<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Legacy ctl_* helpers/hooks are intentionally public for addon interoperability.
/**
 * Cool Timeline — global header screen check and hook wiring.
 *
 * @package CoolTimeline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ctl_is_timeline_addon_page' ) ) {
	/**
	 * Whether the current admin screen belongs to the Timeline Addons menu.
	 *
	 * @return bool
	 */
	function ctl_is_timeline_addon_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen detection.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( in_array( $page, array( 'cool_timeline_settings', 'ctl-getting-started', 'cool-plugins-timeline-addon', 'timeline-addons-license' ), true ) ) {
			return true;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen && 'cool_timeline' === $screen->post_type ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen detection.
		$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';

		if ( 'cool_timeline' === $post_type ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen detection.
		if ( isset( $_GET['post'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen detection.
			$post_id = absint( wp_unslash( $_GET['post'] ) );
			if ( $post_id && 'cool_timeline' === get_post_type( $post_id ) ) {
				return true;
			}
		}

		return false;
	}
}

require_once __DIR__ . '/timeline-global-header.php';

add_action(
	'admin_enqueue_scripts',
	static function () {
		if ( ! ctl_is_timeline_addon_page() ) {
			return;
		}

		$version = defined( 'CTL_V' ) ? CTL_V : '1.0.0';
		cp_timeline_header_enqueue_styles( $version );
	}
);

add_filter(
	'admin_body_class',
	static function ( $classes ) {
		if ( ctl_is_timeline_addon_page() ) {
			$classes .= ' ctl-timeline-addon-page cph-timeline-addon-page';
		}

		return $classes;
	}
);

add_action(
	'in_admin_header',
	static function () {
		if ( ! ctl_is_timeline_addon_page() ) {
			return;
		}
		$default_heading = __( 'Timeline Addons', 'cool-timeline' );
		$heading         = apply_filters( 'ctl_global_header_heading', $default_heading );
		$utm_params      = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=docs&utm_content=global-header';
		cp_timeline_header_render(
			array(
				'heading'       => $heading,
				'icon_url'      => CTL_PLUGIN_URL . 'assets/images/timeline-icon.svg',
				'docs_url'      => 'https://cooltimeline.com/docs/' . $utm_params,
				'support_url'   => 'https://coolplugins.net/support/' . $utm_params,
				'docs_label'    => __( 'Check Docs', 'cool-timeline' ),
				'support_label' => __( 'Get Support', 'cool-timeline' ),
				'text_domain'   => 'cool-timeline',
			)
		);
	}
);

<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Legacy helper function name kept stable for compatibility.
/**
 * Shared Getting Started URL for timeline family plugins.
 *
 * @package CoolTimeline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ctp_timeline_getting_started_url' ) ) {
	/**
	 * Build ctl-getting-started URL with the correct onboarding method tab.
	 *
	 * @param string $plugin_key Plugin key: ctl, ctp, twae, tmdivi, ctlb, cptb.
	 * @return string
	 */
	function ctp_timeline_getting_started_url( $plugin_key = 'ctl' ) {
		$methods = array(
			'ctl'    => 'block',
			'ctp'    => 'block',
			'ctlb'   => 'block',
			'cptb'   => 'block',
			'twae'   => 'elementor-widget',
			'tmdivi' => 'divi-module',
		);

		$method = isset( $methods[ $plugin_key ] ) ? $methods[ $plugin_key ] : 'block';

		return add_query_arg(
			array(
				'page'   => 'ctl-getting-started',
				'mode'   => 'onboarding',
				'method' => $method,
			),
			admin_url( 'admin.php' )
		);
	}
}

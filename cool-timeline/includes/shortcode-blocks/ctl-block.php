<?php

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Hook scripts function into block assets hook (iframe editor canvas).
add_action( 'enqueue_block_assets', 'ctl_gutenberg_scripts' );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function ctl_gutenberg_scripts() {
	if ( ! is_admin() ) {
		return;
	}

	$blockPath = '/dist/block.js';
	$stylePath = '/dist/block.css';

	// Enqueue the bundled block JS file
	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
	wp_enqueue_script(
		'ctl-block-js',
		plugins_url( $blockPath, __FILE__ ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data', 'wp-api' ),
		filemtime( plugin_dir_path( __FILE__ ) . $blockPath )
	);

	// Enqueue frontend and editor block styles
	wp_enqueue_style(
		'ctl-block-css',
		plugins_url( $stylePath, __FILE__ ),
		array( 'wp-block-editor' ),
		filemtime( plugin_dir_path( __FILE__ ) . $stylePath )
	);

	// Localize script for safe URL usage
	wp_localize_script( 'ctl-block-js', 'ctlUrl', array( esc_url( CTL_PLUGIN_URL ) ) ); // Escape URL
}

/**
 * Block Initializer.
 */
add_action(
	'plugins_loaded',
	function () {
		if ( function_exists( 'register_block_type' ) ) {
			// Hook server side rendering into render callback
			register_block_type(
				'cool-timleine/shortcode-block',
				array(
					'api_version'     => 3,
					'render_callback' => 'ctl_block_callback',
					'attributes'      => array(
						'layout'       => array(
							'type'    => 'string',
							'default' => 'default',
						),
						'skin'         => array(
							'type'    => 'string',
							'default' => 'default',
						),
						'dateformat'   => array(
							'type'    => 'string',
							'default' => 'F j',
						),
						'postperpage'  => array(
							'type'    => 'string',
							'default' => 10,
						),
						'slideToShow'  => array(
							'type'    => 'string',
							'default' => '',
						),
						'animation'    => array(
							'type'    => 'string',
							'default' => 'none',
						),
						'icons'        => array(
							'type'    => 'string',
							'default' => 'NO',
						),
						'order'        => array(
							'type'    => 'string',
							'default' => 'DESC',
						),
						'storycontent' => array(
							'type'    => 'string',
							'default' => 'short',
						),
					),
				)
			);
		}
	}
);

/**
 * Block Output.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function ctl_block_callback( $attr ) {
	$layout        = isset( $attr['layout'] ) ? sanitize_text_field( $attr['layout'] ) : 'default';
	$skin          = isset( $attr['skin'] ) ? sanitize_text_field( $attr['skin'] ) : 'default';
	$dateformat    = isset( $attr['dateformat'] ) ? sanitize_text_field( $attr['dateformat'] ) : 'F j';
	$postperpage   = isset( $attr['postperpage'] ) && '-1' === trim( (string) $attr['postperpage'] ) ? '-1' : (string) max( 1, absint( isset( $attr['postperpage'] ) ? $attr['postperpage'] : 10 ) );
	$slide_to_show   = isset( $attr['slideToShow'] ) && '' !== $attr['slideToShow'] ? (string) max( 1, absint( $attr['slideToShow'] ) ) : '';
	$animation     = isset( $attr['animation'] ) ? sanitize_text_field( $attr['animation'] ) : 'none';
	$icons         = isset( $attr['icons'] ) ? sanitize_text_field( $attr['icons'] ) : 'NO';
	$order         = isset( $attr['order'] ) ? sanitize_text_field( $attr['order'] ) : 'DESC';
	$storycontent  = isset( $attr['storycontent'] ) ? sanitize_text_field( $attr['storycontent'] ) : 'short';
	$shortcode_string = '[cool-timeline layout="%s" skin="%s"
		show-posts="%s" date-format="%s" icons="%s" animation="%s" order="%s" story-content="%s" items="%s"]';

	return sprintf(
		$shortcode_string,
		esc_attr( $layout ),
		esc_attr( $skin ),
		esc_attr( $postperpage ),
		esc_attr( $dateformat ),
		esc_attr( $icons ),
		esc_attr( $animation ),
		esc_attr( $order ),
		esc_attr( $storycontent ),
		esc_attr( $slide_to_show )
	);
}

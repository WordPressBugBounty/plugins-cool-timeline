<?php

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Editor scripts (sidebar / block registration).
add_action( 'enqueue_block_editor_assets', 'ctl_gutenberg_scripts' );
// Block styles for iframe editor (Block API v3) + timeline CSS for SSR preview.
add_action( 'enqueue_block_assets', 'ctl_gutenberg_block_assets' );
// Run late so we can dequeue framework styles that share timeline class names.
add_action( 'enqueue_block_assets', 'ctl_dequeue_framework_editor_styles', 100 );

/**
 * Dequeue admin framework CSS that clashes with timeline preview classes.
 */
function ctl_dequeue_framework_editor_styles() {
	if ( ! is_admin() ) {
		return;
	}
	wp_dequeue_style( 'ctl' );
	wp_dequeue_style( 'ctl-rtl' );
}

/**
 * Timeline frontend styles for ServerSideRender preview.
 */
function ctl_gutenberg_block_assets() {
	if ( ! is_admin() ) {
		return;
	}

	$style_path = '/dist/block.css';
	$ext        = '.min';
	$ver        = defined( 'CTL_V' ) ? CTL_V : '1.0';
	$css_base   = CTL_PLUGIN_URL . 'includes/shortcodes/assets/css/';

	wp_enqueue_style( 'ctl_font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), $ver, 'all' );
	wp_enqueue_style( 'ctl_swiper_style', esc_url( $css_base . 'swiper.css' ), array(), $ver, 'all' );
	wp_enqueue_style( 'ctl_common_style', esc_url( $css_base . 'ctl-common-styles' . $ext . '.css' ), array(), $ver, 'all' );
	wp_enqueue_style( 'ctl_vertical_style', esc_url( $css_base . 'ctl-vertical-timeline' . $ext . '.css' ), array( 'ctl_common_style' ), $ver, 'all' );
	wp_enqueue_style( 'ctl_horizontal_style', esc_url( $css_base . 'ctl-horizontal-timeline' . $ext . '.css' ), array( 'ctl_common_style' ), $ver, 'all' );
	wp_enqueue_style( 'ctl_compact_style', esc_url( $css_base . 'ctl-compact-style' . $ext . '.css' ), array( 'ctl_common_style', 'ctl_vertical_style' ), $ver, 'all' );

	// Keep the editor preview in step with the front end: the same refreshed
	// spacing, on the same new-install gate, loaded after the layout sheets.
	if ( ! class_exists( 'CTL_Helpers' ) ) {
		require_once CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-helpers.php';
	}
	$ctl_style_deps = array( 'ctl_common_style', 'ctl_vertical_style', 'ctl_horizontal_style', 'ctl_compact_style' );
	if ( class_exists( 'CTL_Helpers' ) && CTL_Helpers::ctl_is_new_install() ) {
		$ctl_refresh_rel = 'includes/shortcodes/assets/css/ctl-design-refresh' . $ext . '.css';
		$ctl_refresh_ver = method_exists( 'CTL_Assets_Loader', 'ctl_asset_ver' )
			? CTL_Assets_Loader::ctl_asset_ver( $ctl_refresh_rel )
			: $ver;
		wp_enqueue_style( 'ctl_design_refresh', esc_url( $css_base . 'ctl-design-refresh' . $ext . '.css' ), $ctl_style_deps, $ctl_refresh_ver, 'all' );
		$ctl_style_deps[] = 'ctl_design_refresh';
	}

	wp_enqueue_style(
		'ctl-block-css',
		plugins_url( $style_path, __FILE__ ),
		$ctl_style_deps,
		filemtime( plugin_dir_path( __FILE__ ) . $style_path )
	);

	if ( ! class_exists( 'CTL_Styles_Generator' ) ) {
		require_once CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-styles-generator.php';
	}
	if ( class_exists( 'CTL_Styles_Generator' ) ) {
		CTL_Styles_Generator::ctl_global_style();
	}

	// Compact + horizontal need JS in the editor iframe (SSR HTML is CSS-only without it).
	$js_base = CTL_PLUGIN_URL . 'includes/shortcodes/assets/js/';
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-masonry' );

	// Always (re)register so admin gets the frontend Swiper handle.
	wp_deregister_script( 'ctl_swiper_script' );
	wp_register_script(
		'ctl_swiper_script',
		esc_url( $js_base . 'swiper.js' ),
		array( 'jquery' ),
		$ver,
		true
	);
	wp_register_script(
		'ctl_compact_script',
		esc_url( $js_base . 'ctl-compact' . $ext . '.js' ),
		array( 'jquery', 'jquery-masonry' ),
		$ver,
		true
	);
	wp_enqueue_script( 'ctl_swiper_script' );
	wp_enqueue_script( 'ctl_compact_script' );
	wp_deregister_script( 'ctl-editor-ssr-init' );
	wp_enqueue_script(
		'ctl-editor-ssr-init',
		plugins_url( '/editor-ssr-init.js', __FILE__ ),
		array( 'jquery', 'jquery-masonry', 'ctl_swiper_script' ),
		(string) filemtime( plugin_dir_path( __FILE__ ) . 'editor-ssr-init.js' ),
		true
	);

	// CTL_Styles_Generator above already emits the typography rules, so the
	// preview asks for the configured font-family - but the font FILES are a
	// separate stylesheet (ctl-gfonts), registered only on ctl_google_fonts()
	// off the frontend's wp_enqueue_scripts hook, which never fires for the
	// editor's REST render. So the canvas named a family it had never loaded
	// and silently fell back, which is why the preview and the live page
	// disagreed. Register + enqueue it here too. ctl_google_fonts() registers
	// the handle only when a Google font is actually configured; if none is,
	// the enqueue below is a no-op. Matches Pro
	// (includes/shortcode-block/ctl-block.php:73).
	if ( ! class_exists( 'CTL_Assets_Loader' ) ) {
		require_once CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-assets-loader.php';
	}
	if ( class_exists( 'CTL_Assets_Loader' ) ) {
		( new CTL_Assets_Loader() )->ctl_google_fonts();
		wp_enqueue_style( 'ctl-gfonts' );
	}
}

/**
 * Enqueue block editor script.
 */
function ctl_gutenberg_scripts() {
	if ( ! is_admin() ) {
		return;
	}

	$block_path = '/dist/block.js';
	$style_path = '/dist/block.css';

	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
	wp_enqueue_script(
		'ctl-block-js',
		plugins_url( $block_path, __FILE__ ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data', 'wp-api', 'wp-server-side-render' ),
		filemtime( plugin_dir_path( __FILE__ ) . $block_path )
	);

	// InspectorControls render in the parent editor frame (not the canvas iframe).
	wp_enqueue_style(
		'ctl-block-editor-css',
		plugins_url( $style_path, __FILE__ ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . $style_path )
	);

	wp_localize_script(
		'ctl-block-js',
		'ctlUrl',
		array(
			'pluginURL' => esc_url( CTL_PLUGIN_URL ),
			'adminURL'  => esc_url( admin_url( '/' ) ),
		)
	);

	// Reuse the onboarding framework's telemetry AJAX endpoint so editor actions
	// land in the same `ctl_onboarding_telemetry` option the dashboard reads.
	wp_localize_script(
		'ctl-block-js',
		'ctlOnboardingTrack',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'action'  => 'ctl_onboarding_track',
			'nonce'   => wp_create_nonce( 'ctl_onboarding_track' ),
		)
	);
}

/**
 * Block Initializer.
 */
add_action(
	'plugins_loaded',
	function () {
		if ( function_exists( 'register_block_type' ) ) {
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
							'default' => '10',
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
						'isPreview'    => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'timelineWidth'        => array(
							'type'    => 'string',
							'default' => 'custom',
						),
						'timelineCustomWidth'  => array(
							'type'    => 'string',
							'default' => '1140',
						),
						'showTitle'            => array(
							'type'    => 'string',
							'default' => 'show',
						),
						'titleLink'            => array(
							'type'    => 'string',
							'default' => 'enable',
						),
						'contentLength'        => array(
							'type'    => 'string',
							'default' => '50',
						),
						'readMoreVisibility'   => array(
							'type'    => 'string',
							'default' => 'show',
						),
						'readMoreText'         => array(
							'type'    => 'string',
							'default' => 'Read More',
						),
						'readMoreTarget'       => array(
							'type'    => 'string',
							'default' => '_self',
						),
						'yearLabel'            => array(
							'type'    => 'string',
							'default' => '',
						),
					),
				)
			);
		}
	}
);

/**
 * Whether the current request is a block editor ServerSideRender preview.
 *
 * @return bool
 */
function ctl_block_is_ssr_preview() {
	return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

/**
 * Render shortcode HTML for the block (frontend + editor SSR).
 *
 * @param string $shortcode Shortcode markup.
 * @return string
 */
function ctl_block_render_shortcode( $shortcode ) {
	$html = do_shortcode( $shortcode );

	// In editor/REST preview, neutralize links so image clicks cannot navigate the canvas iframe.
	if ( ctl_block_is_ssr_preview() || is_admin() ) {
		$html = preg_replace( '/\s(href)=([\'"])(.*?)\2/i', ' data-ctl-preview-href=$2$3$2 href=$2#$2', $html );
	}

	return $html;
}

/**
 * Block Output.
 *
 * @param array $attr Block attributes.
 * @return string
 */
function ctl_block_callback( $attr ) {
	$layout       = isset( $attr['layout'] ) ? sanitize_text_field( $attr['layout'] ) : 'default';
	$skin         = isset( $attr['skin'] ) ? sanitize_text_field( $attr['skin'] ) : 'default';
	$dateformat   = isset( $attr['dateformat'] ) ? sanitize_text_field( $attr['dateformat'] ) : 'F j';
	$postperpage  = isset( $attr['postperpage'] ) && '-1' === trim( (string) $attr['postperpage'] ) ? '-1' : (string) max( 1, absint( isset( $attr['postperpage'] ) ? $attr['postperpage'] : 10 ) );
	$slide_to_show = isset( $attr['slideToShow'] ) && '' !== $attr['slideToShow'] ? (string) max( 1, absint( $attr['slideToShow'] ) ) : '';
	$animation    = isset( $attr['animation'] ) ? sanitize_text_field( $attr['animation'] ) : 'none';
	$icons        = isset( $attr['icons'] ) ? sanitize_text_field( $attr['icons'] ) : 'NO';
	$order        = isset( $attr['order'] ) ? sanitize_text_field( $attr['order'] ) : 'DESC';
	$storycontent = isset( $attr['storycontent'] ) ? sanitize_text_field( $attr['storycontent'] ) : 'short';
	$timelineWidth       = isset( $attr['timelineWidth'] ) ? sanitize_text_field( $attr['timelineWidth'] ) : 'custom';
	$timelineCustomWidth = isset( $attr['timelineCustomWidth'] ) ? sanitize_text_field( $attr['timelineCustomWidth'] ) : '1140';
	$showTitle            = isset( $attr['showTitle'] ) ? sanitize_text_field( $attr['showTitle'] ) : 'show';
	$titleLink            = isset( $attr['titleLink'] ) ? sanitize_text_field( $attr['titleLink'] ) : 'enable';
	$contentLength        = isset( $attr['contentLength'] ) ? sanitize_text_field( $attr['contentLength'] ) : '50';
	$readMoreVisibility   = isset( $attr['readMoreVisibility'] ) ? sanitize_text_field( $attr['readMoreVisibility'] ) : 'show';
	$readMoreText         = isset( $attr['readMoreText'] ) ? sanitize_text_field( $attr['readMoreText'] ) : 'Read More';
	$readMoreTarget       = isset( $attr['readMoreTarget'] ) ? sanitize_text_field( $attr['readMoreTarget'] ) : '_self';
	$yearLabel            = isset( $attr['yearLabel'] ) ? sanitize_text_field( $attr['yearLabel'] ) : '';

	// Editor SSR: settle layout (no AOS) and cap stories so the canvas stays fast.
	if ( ctl_block_is_ssr_preview() ) {
		$animation = 'none';

		// Respect the Stories Per Page setting in the editor up to the cap, and cap
		// it above that, so the preview stays fast regardless of what the live site
		// is set to. '-1' (all stories) only makes sense on the front end, so it
		// takes the cap too.
		//
		// Horizontal gets 4 rather than 3: it is a slider that shows several
		// stories at once (Slides To Show defaults to 4), so a 3-story preview
		// leaves it short of a full row and misrepresents the layout. The vertical
		// family stacks one story per row, where 3 is enough to show the pattern.
		$ctl_preview_cap = ( 'horizontal' === $layout ) ? 4 : 3;
		$requested       = ( '-1' === $postperpage ) ? $ctl_preview_cap : absint( $postperpage );
		$postperpage     = (string) max( 1, min( $requested, $ctl_preview_cap ) );
	}

	$shortcode_string = '[cool-timeline layout="%s" skin="%s"
		show-posts="%s" date-format="%s" icons="%s" animation="%s" order="%s" story-content="%s" items="%s" timeline-width="%s" timeline-custom-width="%s" show-title="%s" title-link="%s" content-length="%s" read-more="%s" read-more-text="%s" read-more-target="%s" year-label="%s"]';

	$shortcode = sprintf(
		$shortcode_string,
		esc_attr( $layout ),
		esc_attr( $skin ),
		esc_attr( $postperpage ),
		esc_attr( $dateformat ),
		esc_attr( $icons ),
		esc_attr( $animation ),
		esc_attr( $order ),
		esc_attr( $storycontent ),
		esc_attr( $slide_to_show ),
		esc_attr( 'contained' === $timelineWidth ? 'custom' : $timelineWidth ),
		esc_attr( $timelineCustomWidth ),
		esc_attr( $showTitle ),
		esc_attr( $titleLink ),
		esc_attr( $contentLength ),
		esc_attr( $readMoreVisibility ),
		esc_attr( $readMoreText ),
		esc_attr( $readMoreTarget ),
		esc_attr( $yearLabel )
	);

	return ctl_block_render_shortcode( $shortcode );
}

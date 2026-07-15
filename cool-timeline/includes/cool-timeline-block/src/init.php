<?php


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'enqueue_block_assets', 'ctl_timeline_block_editor_assets' );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function ctl_timeline_block_editor_assets() {

	$id = get_the_ID();

	if ( has_block( 'cp-timeline/content-timeline', $id ) ) {
		if ( ! is_admin() ) {
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_style(
				'cp_timeline-cgb-style', // Handle.
				plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
				null,
				null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			);
			wp_enqueue_script( 'ctl_block_common_script' );
			return;
		}

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_style(
			'cp_timeline-cgb-style', // Handle.
			plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
			array( 'wp-block-editor' ),
			null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		);
	} else {
		if ( ! is_admin() ) {
			wp_dequeue_style( 'cp_timeline-cgb-style' );
		}
	}
}

add_action( 'enqueue_block_assets', 'ctl_editor_side_css' );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function ctl_editor_side_css() {
	if ( ! is_admin() ) {
		return;
	}
	// Common Editor style.
	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_style(
		'timeline-block-common-editor-css', // Handle.
		plugin_dir_url( __FILE__ ) . '../assets/common-block-editor.css', // Block editor CSS.
		array( 'wp-block-editor' )// Dependency to include the CSS after it.
	);
}

add_action( 'wp_head', 'timeline_block_load_post_assets' );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function timeline_block_load_post_assets() {
	global $post;
	$this_post = $post;
	if ( ! is_object( $this_post ) ) {
		return;
	}
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	$this_post = apply_filters( 'timeline-block_post_for_stylesheet', $this_post );
	if ( ! is_object( $this_post ) ) {
		return;
	}

	if ( ! isset( $this_post->ID ) ) {
		return;
	}

	if ( has_blocks( $this_post->ID ) && isset( $this_post->post_content ) ) {

		$blocks = parse_blocks( $this_post->post_content );
		
		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && 'core/block' === $block['blockName'] && ! empty( $block['attrs']['ref'] ) ) {
				$reusable_post = get_post( (int) $block['attrs']['ref'] );
				if ( $reusable_post && 'wp_block' === $reusable_post->post_type && ! empty( $reusable_post->post_content ) ) {
					$blocks = array_merge( $blocks, parse_blocks( $reusable_post->post_content ) );
				}
			}
		}

		if ( ! is_array( $blocks ) || empty( $blocks ) ) {
			return;
		}
		foreach ( $blocks as $i => $block ) {

			if ( is_array( $block ) ) {

				if ( '' === $block['blockName'] ) {
					continue;
				}
				$default_Fonts = array( '', 'Arial', 'Helvetica', 'Times New Roman', 'Georgia' );
				ctl_block_print_font_stylesheet( $block['attrs'], 'head', $default_Fonts );
				ctl_block_print_font_stylesheet( $block['attrs'], 'subHead', $default_Fonts );
				ctl_block_print_font_stylesheet( $block['attrs'], 'date', $default_Fonts );
			}
		}
	}

}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function ctl_block_print_font_stylesheet( $attrs, $prefix, $default_fonts ) {
	$family_key = $prefix . 'FontFamily';

	if ( ! isset( $attrs[ $family_key ] ) || in_array( $attrs[ $family_key ], $default_fonts ) ) {
		return;
	}

	$font_set = array( $attrs[ $family_key ] );

	$weight_key = $prefix . 'FontWeight';
	if ( isset( $attrs[ $weight_key ] ) ) {
		$font_set[] = $attrs[ $weight_key ];
	}

	$subset_key = $prefix . 'FontSubset';
	if ( isset( $attrs[ $subset_key ] ) ) {
		$font_set[] = $attrs[ $subset_key ];
	}

	$font_url = ctl_block_get_font_url( $font_set );

	// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
	echo '<link href="' . esc_url( $font_url ) . '" rel="stylesheet">';
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function ctl_block_get_font_url( $font_set ) {
	$font_url = add_query_arg(
		array(
			'family' => rawurlencode( implode( ':', $font_set ) ),
		),
		'https://fonts.googleapis.com/css'
	);
	
	return $font_url;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function cp_timeline_cgb_block_assets() {
	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_register_style(
		'cp_timeline-cgb-style', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
		is_admin() ? array( 'wp-block-editor' ) : null,
		null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	);

	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_register_script(
		'cp_timeline-cgb-block-js', // Handle.
		plugins_url( 'dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-block-editor' ),
		null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		true
	);

	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_register_style(
		'cp_timeline-cgb-block-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ),
		array( 'wp-block-editor' ),
		null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	);
	
	wp_register_script( 'ctl_block_common_script', CTL_PLUGIN_URL . 'includes/cool-timeline-block/assets/js/common.js', array( 'jquery' ), CTL_V, false );
	wp_localize_script(
		'cp_timeline-cgb-block-js',
		'cgbGlobal',
		array(
			'pluginDirPath' => plugin_dir_path( __DIR__ ),
			'pluginDirUrl'  => esc_url( plugin_dir_url( __DIR__ ) ), // Escape URL
		)
	);

	if ( function_exists( 'register_block_type' ) ) {

		register_block_type(
			'cp-timeline/content-timeline',
			array(
				'api_version'   => 3,
				// Enqueue blocks.style.build.css on both frontend & backend.
				'style'         => 'cp_timeline-cgb-style',
				// Enqueue blocks.build.js in the editor only.
				'editor_script' => 'cp_timeline-cgb-block-js',
				// Enqueue blocks.editor.build.css in the editor only.
				'editor_style'  => 'cp_timeline-cgb-block-editor-css',
			)
		);
		register_block_type(
			'cp-timeline/content-timeline-child',
			array(
				'api_version' => 3,
			)
		);
	}
}

// Hook: Block assets.
add_action( 'init', 'cp_timeline_cgb_block_assets' );

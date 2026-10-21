<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
/**
 * Block editor assets and registration for Cool Timeline.
 *
 * @package CoolTimeline
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'gctl_handle_new_design' ) ) {
	/**
	 * Check if the new timeline design should be applied.
	 *
	 * Matches Cool Timeline 5.0.0 migration logic:
	 * - New installs on or after 5.0.0 (CTL_Helpers::ctl_is_new_install())
	 * - Users who migrated via notice or option (ctl_migrate_new_design === 'yes')
	 * - Users who previously opted into new design in timeline-block (ctlb_migrate_new_design === 'yes')
	 * - Initial save version >= 5.0.0
	 *
	 * @return bool
	 */
	function gctl_handle_new_design() {
		if ( class_exists( 'CTL_Helpers' ) && method_exists( 'CTL_Helpers', 'ctl_is_new_install' ) ) {
			if ( CTL_Helpers::ctl_is_new_install() ) {
				return true;
			}
		}

		if ( 'yes' === get_option( 'ctl_migrate_new_design' ) || 'yes' === get_option( 'ctlb_migrate_new_design' ) ) {
			return true;
		}

		$free_initial_ver = get_option( 'ctlb-initial-save-version', '' );
		$free_legacy      = get_option( 'ctlb_keep_legacy_design', '' );
		if ( ! empty( $free_initial_ver ) && version_compare( $free_initial_ver, '2.0.0', '>=' ) && 'yes' !== $free_legacy ) {
			return true;
		}

		$design_refresh_version = ( class_exists( 'CTL_Helpers' ) && defined( 'CTL_Helpers::DESIGN_REFRESH_VERSION' ) )
			? CTL_Helpers::DESIGN_REFRESH_VERSION
			: '4.0.0';

		$initial_version = get_option( 'ctl_initial_save_version', false );
		if ( false === $initial_version ) {
			$initial_version = get_option( 'ctlb_initial_save_version', false );
		}

		if ( false === $initial_version || null === $initial_version || '' === $initial_version ) {
			return false;
		}

		$initial_version = trim( (string) $initial_version );
		if ( version_compare( $initial_version, $design_refresh_version, '<' ) ) {
			return false;
		}

		return true;
	}
}

add_filter( 'render_block', 'gctl_apply_new_design_class', 10, 2 );
/**
 * Adds the new-design classes to timelines that were saved before migration.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function gctl_apply_new_design_class( $block_content, $block ) {
	if ( ! isset( $block['blockName'] ) || 'cp-timeline/content-timeline' !== $block['blockName'] ) {
		return $block_content;
	}

	if ( ! gctl_handle_new_design() ) {
		return $block_content;
	}

	$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();

	if ( empty( $attrs['coreSpacingReset'] ) ) {
		$block_content = gctl_strip_legacy_story_spacing( $block_content );
	}

	if ( false !== strpos( $block_content, 'ctlb-new-design' ) ) {
		return $block_content;
	}

	$patched = preg_replace(
		'/(<div\b[^>]*\bclass="[^"]*\bctlb-wrapper\b)/',
		'$1 ctlb-new-design',
		$block_content,
		1
	);

	return ( null === $patched ) ? $block_content : $patched;
}

/**
 * Removes inline padding/margin from headings and paragraphs.
 *
 * @param string $html Rendered timeline block HTML.
 * @return string
 */
function gctl_strip_legacy_story_spacing( $html ) {
	$stripped = preg_replace_callback(
		'/<(h[1-6]|p)\b([^>]*?)\sstyle="([^"]*)"([^>]*)>/i',
		function ( $matches ) {
			$kept = array();
			foreach ( explode( ';', $matches[3] ) as $declaration ) {
				$declaration = trim( $declaration );
				if ( '' === $declaration ) {
					continue;
				}
				if ( preg_match( '/^(padding|margin)(-(top|right|bottom|left|block|inline)(-(start|end))?)?\s*:/i', $declaration ) ) {
					continue;
				}
				$kept[] = $declaration;
			}
			$style = $kept ? ' style="' . implode( ';', $kept ) . '"' : '';
			return '<' . $matches[1] . $matches[2] . $style . $matches[4] . '>';
		},
		$html
	);

	return ( null === $stripped ) ? $html : $stripped;
}

if ( ! function_exists( 'gctl_get_all_blocks' ) ) {
	/**
	 * Recursively retrieve all blocks including inner blocks and reusable blocks.
	 *
	 * @param array $blocks Blocks array.
	 * @param array $seen   Seen reusable block IDs.
	 * @param int   $depth  Current recursion depth.
	 * @return array
	 */
	function gctl_get_all_blocks( $blocks, &$seen = array(), $depth = 0 ) {
		if ( $depth > 10 || ! is_array( $blocks ) ) {
			return array();
		}

		$all_blocks = array();

		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			$all_blocks[] = $block;

			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$all_blocks = array_merge(
					$all_blocks,
					gctl_get_all_blocks(
						$block['innerBlocks'],
						$seen,
						$depth + 1
					)
				);
			}

			if (
				isset( $block['blockName'] ) &&
				'core/block' === $block['blockName'] &&
				! empty( $block['attrs']['ref'] )
			) {
				$reusable_id = absint( $block['attrs']['ref'] );

				if (
					$reusable_id &&
					! isset( $seen[ $reusable_id ] ) &&
					'wp_block' === get_post_type( $reusable_id )
				) {
					$seen[ $reusable_id ] = true;
					$reusable_post        = get_post( $reusable_id );

					if ( $reusable_post && ! empty( $reusable_post->post_content ) ) {
						$reusable_blocks = parse_blocks( $reusable_post->post_content );
						$all_blocks      = array_merge(
							$all_blocks,
							gctl_get_all_blocks(
								$reusable_blocks,
								$seen,
								$depth + 1
							)
						);
					}
				}
			}
		}

		return $all_blocks;
	}
}

add_action( 'wp_head', 'gctl_timeline_block_load_post_assets' );
/**
 * Load Google Fonts for Timeline Block headings and dates.
 */
function gctl_timeline_block_load_post_assets() {
	global $post;
	$this_post = $post;
	if ( ! is_object( $this_post ) ) {
		return;
	}
	$this_post = apply_filters( 'timeline-block_post_for_stylesheet', $this_post );
	if ( ! is_object( $this_post ) || ! isset( $this_post->ID ) ) {
		return;
	}

	if ( has_blocks( $this_post->ID ) && isset( $this_post->post_content ) ) {
		$blocks      = parse_blocks( $this_post->post_content );
		$page_blocks = gctl_get_all_blocks( $blocks );

		if ( ! is_array( $page_blocks ) || empty( $page_blocks ) ) {
			return;
		}

		$default_fonts    = array( '', 'Arial', 'Helvetica', 'Times New Roman', 'Georgia' );
		$loaded_font_urls = array();

		foreach ( $page_blocks as $block ) {
			if ( ! is_array( $block ) || empty( $block['blockName'] ) ) {
				continue;
			}

			if ( isset( $block['attrs']['headFontFamily'] ) && ! in_array( $block['attrs']['headFontFamily'], $default_fonts, true ) ) {
				$head_font = array( sanitize_text_field( $block['attrs']['headFontFamily'] ) );
				if ( isset( $block['attrs']['headFontWeight'] ) ) {
					$head_font[] = sanitize_text_field( $block['attrs']['headFontWeight'] );
				}
				if ( isset( $block['attrs']['headFontSubset'] ) ) {
					$head_font[] = sanitize_text_field( $block['attrs']['headFontSubset'] );
				}
				$font_url = gctl_timeline_get_font_url( $head_font );
				if ( ! in_array( $font_url, $loaded_font_urls, true ) ) {
					$loaded_font_urls[] = $font_url;
					// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
					echo '<link href="' . esc_url( $font_url ) . '" rel="stylesheet">';
				}
			}

			if ( isset( $block['attrs']['subHeadFontFamily'] ) && ! in_array( $block['attrs']['subHeadFontFamily'], $default_fonts, true ) ) {
				$subhead_font = array( sanitize_text_field( $block['attrs']['subHeadFontFamily'] ) );
				if ( isset( $block['attrs']['subHeadFontWeight'] ) ) {
					$subhead_font[] = sanitize_text_field( $block['attrs']['subHeadFontWeight'] );
				}
				if ( isset( $block['attrs']['subHeadFontSubset'] ) ) {
					$subhead_font[] = sanitize_text_field( $block['attrs']['subHeadFontSubset'] );
				}
				$font_url = gctl_timeline_get_font_url( $subhead_font );
				if ( ! in_array( $font_url, $loaded_font_urls, true ) ) {
					$loaded_font_urls[] = $font_url;
					// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
					echo '<link href="' . esc_url( $font_url ) . '" rel="stylesheet">';
				}
			}

			if ( isset( $block['attrs']['dateFontFamily'] ) && ! in_array( $block['attrs']['dateFontFamily'], $default_fonts, true ) ) {
				$date_font = array( sanitize_text_field( $block['attrs']['dateFontFamily'] ) );
				if ( isset( $block['attrs']['dateFontWeight'] ) ) {
					$date_font[] = sanitize_text_field( $block['attrs']['dateFontWeight'] );
				}
				if ( isset( $block['attrs']['dateFontSubset'] ) ) {
					$date_font[] = sanitize_text_field( $block['attrs']['dateFontSubset'] );
				}
				$font_url = gctl_timeline_get_font_url( $date_font );
				if ( ! in_array( $font_url, $loaded_font_urls, true ) ) {
					$loaded_font_urls[] = $font_url;
					// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
					echo '<link href="' . esc_url( $font_url ) . '" rel="stylesheet">';
				}
			}
		}
	}
}

if ( ! function_exists( 'gctl_timeline_get_font_url' ) ) {
	function gctl_timeline_get_font_url( $font_set ) {
		return add_query_arg(
			array(
				'family' => rawurlencode( implode( ':', $font_set ) ),
			),
			'https://fonts.googleapis.com/css'
		);
	}
}

add_action( 'enqueue_block_assets', 'gctl_timeline_block_editor_assets' );
/**
 * Fallback to guarantee stylesheet enqueueing when block is present on page.
 */
function gctl_timeline_block_editor_assets() {
	$id = get_the_ID();
	if ( $id && has_block( 'cp-timeline/content-timeline', $id ) ) {
		wp_enqueue_style( 'cp_timeline-cgb-style' );
	}
}

/**
 * Register block scripts and styles.
 */
function gctl_cp_timeline_cgb_block_assets() {
	$ctlb_block_dir  = plugin_dir_path( dirname( __FILE__ ) );
	$ctlb_asset_path = $ctlb_block_dir . 'dist/index.asset.php';
	$ctlb_asset      = file_exists( $ctlb_asset_path ) ? include $ctlb_asset_path : array();
	$ctlb_version    = defined( 'CTL_V' ) ? CTL_V : '5.0.0';
	$ctlb_build_ver  = ! empty( $ctlb_asset['version'] ) ? $ctlb_asset['version'] : $ctlb_version;
	$ctlb_common_css = $ctlb_block_dir . 'assets/common-block-editor.css';
	$ctlb_common_ver = file_exists( $ctlb_common_css ) ? (string) filemtime( $ctlb_common_css ) : $ctlb_version;

	// Frontend / shared block CSS – no editor/admin dependencies.
	wp_register_style(
		'cp_timeline-cgb-style',
		plugins_url( 'dist/style-index.css', dirname( __FILE__ ) ),
		array(),
		$ctlb_build_ver
	);

	$editor_deps = ! empty( $ctlb_asset['dependencies'] ) && is_array( $ctlb_asset['dependencies'] )
		? array_values( array_unique( array_merge( $ctlb_asset['dependencies'], array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-block-editor' ) ) ) )
		: array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-block-editor' );

	wp_register_script(
		'cp_timeline-cgb-block-js',
		plugins_url( 'dist/index.js', dirname( __FILE__ ) ),
		$editor_deps,
		$ctlb_build_ver,
		true
	);

	wp_localize_script(
		'cp_timeline-cgb-block-js',
		'cgbGlobal',
		array(
			'pluginDirPath' => plugin_dir_path( __DIR__ ),
			'pluginDirUrl'  => esc_url( plugin_dir_url( __DIR__ ) ),
			'isNewDesign'   => gctl_handle_new_design(),
		)
	);

	wp_localize_script(
		'cp_timeline-cgb-block-js',
		'ctlBlockData',
		array(
			'isPro' => false,
		)
	);

	wp_register_style(
		'timeline-block-common-editor-css',
		plugins_url( 'assets/common-block-editor.css', dirname( __FILE__ ) ),
		array( 'wp-edit-blocks' ),
		$ctlb_common_ver
	);

	wp_register_style(
		'cp_timeline-cgb-block-editor-css',
		plugins_url( 'dist/index.css', dirname( __FILE__ ) ),
		array( 'wp-edit-blocks', 'timeline-block-common-editor-css' ),
		$ctlb_build_ver
	);

	if ( function_exists( 'register_block_type' ) ) {
		register_block_type(
			'cp-timeline/content-timeline',
			array(
				'api_version'   => 3,
				'style'         => 'cp_timeline-cgb-style',
				'editor_script' => 'cp_timeline-cgb-block-js',
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

add_action( 'init', 'gctl_cp_timeline_cgb_block_assets' );

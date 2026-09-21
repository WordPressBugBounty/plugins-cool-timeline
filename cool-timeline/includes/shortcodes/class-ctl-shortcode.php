<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Legacy CTL_* class name kept for compatibility.
/**
 * CTL Shortcode.
 *
 * @package CTL
 */

/**
 * Do not access the page directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Create shortcode class if class not exists
 */
if ( ! class_exists( 'CTL_Shortcode' ) ) {

	/**
	 * Class Shortcode.
	 */
	class CTL_Shortcode {

		/**
		 * Shortcode assets object variable
		 *
		 * @var object
		 */
		public $ctl_asset_obj = array();

		/**
		 * Configure settings array
		 *
		 * @var settings
		 */
		public $settings = array();

		/**
		 * Shortcode attribute array configure
		 *
		 * @var attribute
		 */
		public $attributes = array();


		/**
		 * Constructor
		 *
		 * @param object $settings_obj timeline settings object.
		 */
		public function __construct( $settings_obj ) {
			$this->settings = $settings_obj->ctl_get_settings();
			// register actions.
			add_action( 'init', array( $this, 'ctl_register_shortcode' ) );
			// layout files loader.
			$this->ctl_layout_loader();

			$this->ctl_asset_obj = new CTL_Assets_Loader();

			// Preview Ajax request instance
			CTL_Ajax_Handler::get_instance();
		}

		/**
		 * Load all layouts files
		 */
		public function ctl_layout_loader() {
			require_once CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-loop-helpers.php';
			require_once CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-layout-manager.php';
			require_once CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-assets-loader.php';
			require_once CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-styles-generator.php';
			require_once CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-ajax-handler.php';
		}

		/**
		 * Add shortcode
		 */
		public function ctl_register_shortcode() {
			add_shortcode( 'cool-timeline', array( $this, 'ctl_shortcode_handler' ) );

		}
		/**
		 * Set shortcode attribute
		 * render shortcode block
		 *
		 * @param array  $atts shortcode attributes.
		 * @param string $content shortcode content.
		 */
		public function ctl_shortcode_handler( $atts ) {
			/**
			 * Demo shortcode attributes
			 * [cool-timeline layout="default" skin="default" show-posts="10" date-format="F j" icons="NO" animation="none" story-content="short" order="DESC" ]
			 */

			// animations,animation.

			$default_attr = array(
				// Main configuration attributes.
				'post_per_page' => 10,
				'layout'        => '',
				'order'         => '',
				'story-content' => '',
				'animation'     => '',
				'date-format'   => '',
				'icons'         => 'YES',
				'show-posts'    => '',
				'skin'          => 'default',
				'items'         => '',
				'timeline-width'        => 'custom',
				'timeline-custom-width' => '1140',
				'show-title'            => 'show',
				'title-link'            => 'enable',
				'content-length'        => '50',
				'read-more'             => 'show',
				'read-more-text'        => 'Read More',
				'read-more-target'      => '_self',
				'year-label'            => '',
			);

			// Set shortcode attribute.
			$this->attributes = shortcode_atts( $default_attr, $atts );

			$this->attributes = $this->attributes_migration( $this->attributes, $atts );

			// Shortcode type define.
			$this->attributes['ctl_type'] = 'story_timeline';

			CTL_Helpers::has_shortcode_added( get_the_ID(), $this->attributes['layout'] );

			// load timeline global assets.
			$this->ctl_asset_obj->ctl_global_assets( $this->attributes );

			$this->attributes['config'] = $this->ctl_config_layouts( $this->attributes );
			$paged                      = 1;
			// include pagination arguments.
			if ( get_query_var( 'paged' ) ) {
				$paged = get_query_var( 'paged' );
			} elseif ( get_query_var( 'page' ) ) {
				$paged = get_query_var( 'page' );
			}
			$this->attributes['paged'] = $paged;
			$wp_query                  = $this->ctl_query_object();
			// timeline html based on layout.
			$layout_manager_object = new CTL_Layout_Manager( $this->attributes, $wp_query, $this->settings );
			$output                = $layout_manager_object->render_layout();
			return $output;
		}

		/**
		 * Wp_query to get all stories.
		 */
		public function ctl_query_object() {
			$attributes = $this->attributes;
			$show_posts = ! empty( $attributes['show-posts'] ) ? $attributes['show-posts'] : $attributes['post_per_page'];
			$show_posts = ( '-1' === trim( (string) $show_posts ) ) ? -1 : max( 1, absint( $show_posts ) );
			$order      = ! empty( $attributes['order'] ) ? strtoupper( sanitize_key( $attributes['order'] ) ) : strtoupper( sanitize_key( $this->settings['story_orders'] ) );
			$order      = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';

			$query_args               = array(
				'post_type'      => 'cool_timeline',
				'post_status'    => array( 'publish', 'future' ),
				'order'          => $order,
				'meta_key'       => 'ctl_story_timestamp', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'orderby'        => 'meta_value_num',
				'posts_per_page' => $show_posts,
				'paged'          => max( 1, absint( $attributes['paged'] ) ),
			);

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$query_args = apply_filters( 'ctp_story_query_args', $query_args, $attributes );
			return new WP_Query( $query_args );
		}

		/**
		 * Configure layout based on the settings.
		 *
		 * @param object $attributes shortcode attributes.
		 */
		public function ctl_config_layouts( $attributes ) {
			$config_arr                   = array();
			$main_wrapper_cls             = 'ctl-wrapper';

			// Layout is resolved further down for the wrapper classes, but the
			// design gate below needs it now.
			$ctl_layout_early = $this->ctl_set_val( $attributes['layout'], 'default' );

			// Refreshed design CSS is scoped under this class, so existing sites
			// keep rendering the unscoped/legacy rules untouched.
			//
			// Horizontal is excluded outright. The refresh is a port of Pro's
			// default design for the vertical family - card border, shadow, radius,
			// full-bleed title band, the card-to-line gap. Free's horizontal layout
			// is a different thing entirely: a stripped card whose body lives in a
			// lightbox popup, with no title band to hang any of that on. Giving it
			// the card treatment framed a card that was never meant to be one.
			// Without the class none of the refreshed sheet applies and horizontal
			// renders exactly as it did before.
			if ( CTL_Helpers::ctl_is_new_install() && 'horizontal' !== $ctl_layout_early ) {
				$main_wrapper_cls .= ' ctl-new-design';
			}

			// Mark the block editor's ServerSideRender pass. The refreshed sheet
			// centres the marker on the title bar only from 768px up, because below
			// that Free switches to its own single-column anchors -- but the editor
			// canvas is its own viewport and is routinely narrower than that while
			// still previewing the desktop layout, so it needs the rule regardless
			// of width. Never present on a published page.
			$ctl_is_preview = function_exists( 'ctl_block_is_ssr_preview' ) && ctl_block_is_ssr_preview();

			// The shortcode generator's modal preview renders over admin-ajax, not
			// REST, so the check above never sees it. Its iframe is its own viewport
			// too, so it needs the same width-independent rules. The handler itself
			// verifies the nonce before rendering; this only picks a CSS class.
			if ( ! $ctl_is_preview && function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$ctl_ajax_action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
				$ctl_is_preview  = ( 'get_shortcode_preview' === $ctl_ajax_action );
			}

			if ( $ctl_is_preview ) {
				$main_wrapper_cls .= ' ctl-editor-preview';
			}
			$wrapper_cls                  = array( 'cool-timeline-wrapper' );
			$ctl_animation                = '';
			$config_arr['data_attribute'] = array();

			$layout = $this->ctl_set_val( $attributes['layout'], 'default' );
			$skin   = $this->ctl_set_val( $attributes['skin'], 'default' );
			if ( ! in_array( $skin, array( 'default', 'clean' ), true ) ) {
				$skin = 'default';
			}

			$timeline_width = isset( $attributes['timeline-width'] ) ? sanitize_key( $attributes['timeline-width'] ) : 'custom';
			if ( 'contained' === $timeline_width ) {
				$timeline_width = 'custom';
			}
			if ( ! in_array( $timeline_width, array( 'full', 'custom' ), true ) ) {
				$timeline_width = 'custom';
			}
			$main_wrapper_cls  .= ' ctl-timeline-' . $timeline_width;
			$main_wrapper_style = '';
			if ( 'custom' === $timeline_width ) {
				$custom_width       = isset( $attributes['timeline-custom-width'] ) && '' !== $attributes['timeline-custom-width'] && is_numeric( $attributes['timeline-custom-width'] )
					? absint( $attributes['timeline-custom-width'] )
					: 1140;
				$custom_width       = max( 400, min( 1920, $custom_width ) );
				$main_wrapper_style = '--ctl-timeline-max-width: ' . $custom_width . 'px; max-width: ' . $custom_width . 'px';
			} else {
				$main_wrapper_style = 'max-width: none; width: 100%';
			}

			// create wrapper class based upon layout.
			switch ( $layout ) {
				case 'one-side':
					$wrapper_cls[]                 = 'ctl-one-sided';
					$wrapper_cls['ctl_design_cls'] = 'ctl-vertical-wrapper';
					break;
				case 'compact':
					$wrapper_cls[]                 = 'ctl-both-sided';
					$wrapper_cls[]                 = 'ctl-vertical-wrapper';
					$wrapper_cls['ctl_design_cls'] = 'ctl-compact-wrapper';
					break;
				case 'horizontal':
					$wrapper_cls[]                 = 'ctl-horizontal';
					$wrapper_cls['ctl_design_cls'] = 'ctl-horizontal-wrapper';
					$wrapper_cls[]                 = 'ctl-horizontal-timeline';
					break;
				default:
					$wrapper_cls[]                 = 'ctl-both-sided';
					$wrapper_cls['ctl_design_cls'] = 'ctl-vertical-wrapper';
					break;
			}

			if ( 'clean' === $skin ) {
				$wrapper_cls[] = 'ctl-clean-skin';
			}
			// create here a deprcated logic.

			// Deprecated animations attribute setting if the user has configured the shortcode with animations attribute.
			$ctl_animation = isset( $attributes['animation'] ) ? sanitize_text_field( $attributes['animation'] ) : 'none';

			// Legacy VC / shortcode generator mistakenly stored animate.css labels as values.
			$animation_aliases = array(
				'fadeInUp'   => 'fade-up',
				'fadein'     => 'fade-up',
				'fade-in'    => 'fade-up',
				'fadeIn'     => 'fade-up',
				'fadeinup'   => 'fade-up',
			);
			if ( isset( $animation_aliases[ $ctl_animation ] ) ) {
				$ctl_animation = $animation_aliases[ $ctl_animation ];
			}

			if ( in_array( $ctl_animation, CTL_Helpers::get_deprecated_animations(), true ) ) {
				$ctl_animation = 'fade-up';
			}

			// Free supports None + Fade In Up only (same as Gutenberg).
			if ( ! in_array( $ctl_animation, array( 'none', 'fade-up' ), true ) ) {
				$ctl_animation = 'none';
			}

			if ( 'horizontal' === $layout ) {
				$config_arr['data_attribute'][] = 'data-items="' . esc_attr( $this->attributes['items'] ) . '"';
			}

			$config_arr['layout']         = esc_attr( $layout );
			$config_arr['active_skin']    = esc_attr( $skin );
			$config_arr['main_wrp_cls']   = esc_attr( $main_wrapper_cls );
			$config_arr['main_wrp_style'] = $main_wrapper_style;
			$config_arr['wrapper_cls']    = $wrapper_cls;
			$config_arr['wrapper_id']     = wp_unique_id( 'cool_timeline_' );
			$config_arr['animation']      = ! empty( $ctl_animation ) ? esc_attr( $ctl_animation ) : 'none';
			return $config_arr;
		}

		public function attributes_migration( $attr, $shortcode_attr ) {
			$shortcode_attr = empty( $shortcode_attr ) ? array() : $shortcode_attr;
			if ( ( 'horizontal' === $attr['layout'] && ! array_key_exists( 'items', $shortcode_attr ) ) || ( array_key_exists( 'items', $shortcode_attr ) && empty( $shortcode_attr['items'] ) ) ) {
				$attr['items']      = isset( $attr['show-posts'] ) ? max( 1, absint( $attr['show-posts'] ) ) : absint( $this->settings['post_per_page'] );
				$attr['show-posts'] = '-1';
			}
			if ( isset( $attr['date-format'] ) && 'default' === $attr['date-format'] ) {
				$attr['date-format'] = 'F j';
			}
			return $attr;
		}

		/**
		 * Configure default values
		 *
		 * @param string $value shortcode attribute new value.
		 * @param string $default shortcode attribute default value.
		 */
		public function ctl_set_val( $value, $default ) {
			if ( isset( $value ) && ! empty( $value ) ) {
				return $value;
			}
			return $default;
		}
	}

}

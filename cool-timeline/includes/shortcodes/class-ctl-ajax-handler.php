<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Legacy CTL_* class name kept for compatibility.
/**
 * CTL Ajax Handler
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
 * Handle Cool Timeline ajax requests
 */
if ( ! class_exists( 'CTL_Ajax_Handler' ) ) {

	/**
	 * Class Ajax Hanlder.
	 *
	 * @package CTL
	 */
	class CTL_Ajax_Handler {
		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Gets an instance of our plugin.
		 *
		 * @param object $settings_obj timeline settings.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @param object $settings_obj Plugin settings.
		 */
		public function __construct() {
			if ( is_admin() ) {
				add_action( 'wp_ajax_get_shortcode_preview', array( $this, 'ctl_shortcode_preview' ) );
			}
		}

		/**
		 * Cool Timeline story shortcode preview.
		 */
		public function ctl_shortcode_preview() {
			if ( ! check_ajax_referer( 'ctl_preview', 'nonce', false ) ) {
				return wp_send_json_error( __( 'Invalid security token sent.', 'cool-timeline' ), 403 );
				
			}

			if ( ! current_user_can( 'edit_posts' ) ) {
				return wp_send_json_error(
					__( 'Unauthorized', 'cool-timeline' ),
					403
				);
				
			}

			if ( ! isset( $_POST['shortcode'] ) ) {
				return wp_send_json_error( __( 'Try Again.', 'cool-timeline' ) );
			
			}
		
			// Properly sanitize the shortcode array
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$shortcode_data = isset( $_POST['shortcode'] ) && is_array( $_POST['shortcode'] )? wp_unslash( $_POST['shortcode'] ) : array();

			$allowed_shortcode_keys = array(
				'shortcodeType'     => true,
				'layout'            => true,
				'skin'              => true,
				'date-format'       => true,
				'custom-date-format' => true,
				'timeline-title'    => true,
				'show-posts'        => true,
				'items'             => true,
				'animation'         => true,
				'icons'             => true,
				'order'             => true,
				'story-content'     => true,
				'pagination'        => true,
			);
			$ctl_shortcode         = array_intersect_key(
				map_deep( $shortcode_data, 'sanitize_text_field' ),
				$allowed_shortcode_keys
			);

			$enum_defaults = array(
				'shortcodeType' => 'cool-timeline',
				'layout'        => 'default',
				'skin'          => 'default',
				'order'         => 'DESC',
				'icons'         => 'NO',
				'story-content' => 'short',
			);
			$enum_values   = array(
				'shortcodeType' => array( 'cool-timeline' ),
				'layout'        => array( 'default', 'horizontal', 'one-side', 'simple', 'compact' ),
				'skin'          => array( 'default', 'clean' ),
				'order'         => array( 'ASC', 'DESC' ),
				'icons'         => array( 'YES', 'NO', 'yes', 'no' ),
				'story-content' => array( 'short', 'full' ),
			);

			foreach ( $enum_values as $key => $allowed_values ) {
				if ( ! isset( $ctl_shortcode[ $key ] ) ) {
					continue;
				}

				if ( ! in_array( $ctl_shortcode[ $key ], $allowed_values, true ) ) {
					$ctl_shortcode[ $key ] = $enum_defaults[ $key ];
				}
			}

			require_once CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-shortcode-preview.php';

			$assets_data = new CTL_Shortcode_Preview( $ctl_shortcode );
			$shortcode   = $assets_data->ctl_preview_shortcode();
			$assets_obj  = $assets_data->assets_obj();

			// Render the shortcode.
			$rendered_shortcode = do_shortcode( $shortcode );
			$assets_obj         = is_array( $assets_obj ) ? $assets_obj : array();

			// Assuming $this->assets_obj is defined.
			$data['shortcode'] = $rendered_shortcode;
			$data['assets']    = array(
				'style'        => array_map(
					'esc_url_raw',
					isset( $assets_obj['style'] ) && is_array( $assets_obj['style'] ) ? $assets_obj['style'] : array()
				),
				'script'       => array_map(
					'esc_url_raw',
					isset( $assets_obj['script'] ) && is_array( $assets_obj['script'] ) ? $assets_obj['script'] : array()
				),
				'custom_style' => isset( $assets_obj['custom_style'] ) && is_string( $assets_obj['custom_style'] )
					? sanitize_textarea_field( $assets_obj['custom_style'] )
					: '',
			);
			wp_send_json_success( $data );
		}
	}
}

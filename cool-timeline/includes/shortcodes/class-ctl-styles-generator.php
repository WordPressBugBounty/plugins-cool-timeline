<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Legacy CTL_* class name kept for compatibility.
/**
 * CTP Assets Loader.
 *
 * @package CTP
 */

/**
 * Do not access the page directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CTL_Styles_Generator' ) ) {
	/**
	 * CTP Style loader Class.
	 */
	class CTL_Styles_Generator {

		/**
		 * Timeline global style settings
		 */
		public static function ctl_global_style() {
			$ctl_options_arr = get_option( 'cool_timeline_settings' );

			// Style options.
			$style_vars = self::styles_settings_vars( $ctl_options_arr );

			$style  = self::render_global_style( $style_vars );
			$style .= self::ctl_global_typography( $ctl_options_arr );

			$custom_css = isset( $ctl_options_arr['custom_styles'] ) ? self::sanitize_custom_css( $ctl_options_arr['custom_styles'] ) : '';
			$custom_css = preg_replace( '/\\\\/', '', $custom_css );
			$final_css  = self::clt_minify_css( $style );

			wp_add_inline_style( 'ctl_common_style', $custom_css . ' ' . $final_css );
		}

		/**
		 * Get global style settings variables
		 *
		 * @param object $settings Timeline settings object.
		 */
		public static function styles_settings_vars( $settings ) {
			$background_type = isset( $settings['timeline_background'] ) ? $settings['timeline_background'] : '0';
			$bg_color        = '';

			if ( '1' === $background_type ) {
				$bg_color = self::sanitize_css_color_value( isset( $settings['timeline_bg_color'] ) ? $settings['timeline_bg_color'] : 'none', 'none' );
			}

			$color_defaults = array(
				'first_post'          => '#02c5be',
				'second_post'         => '#f12945',
				'content_bg_color'    => '#f9f9f9',
				'circle_border_color' => '#025149',
				'line_color'          => '#000',
			);

			$sanitized_colors = array();
			foreach ( $color_defaults as $setting_key => $default_color ) {
				$sanitized_colors[ $setting_key ] = self::sanitize_css_color_value(
					isset( $settings[ $setting_key ] ) ? $settings[ $setting_key ] : $default_color,
					$default_color
				);
			}

			$global_styles = array(
				'--ctl-bg-color'           => $bg_color,
				'--ctw-first-story-color'  => $sanitized_colors['first_post'],
				'--ctw-second-story-color' => $sanitized_colors['second_post'],
				'--ctw-cbx-des-background' => $sanitized_colors['content_bg_color'],
				'--ctw-ybx-bg'             => $sanitized_colors['circle_border_color'],
				'--ctw-line-bg'            => $sanitized_colors['line_color'],
			);

			return $global_styles;
		}

		/**
		 * Global typography settings
		 */
		public static function ctl_global_typography( $ctl_options_arr = null ) {
			$ctl_options_arr           = is_array( $ctl_options_arr ) ? $ctl_options_arr : get_option( 'cool_timeline_settings' );
			$ctl_main_title_typo_all   = isset( $ctl_options_arr['main_title_typo'] ) ? self::ctl_typo_output( $ctl_options_arr['main_title_typo'] ) : '';
			$ctl_post_title_typo_all   = isset( $ctl_options_arr['post_title_typo'] ) ? self::ctl_typo_output( $ctl_options_arr['post_title_typo'] ) : '';
			$ctl_post_content_typo_all = isset( $ctl_options_arr['post_content_typo'] ) ? self::ctl_typo_output( $ctl_options_arr['post_content_typo'] ) : '';
			$ctl_date_typo_all         = isset( $ctl_options_arr['ctl_date_typo'] ) ? self::ctl_typo_output( $ctl_options_arr['ctl_date_typo'] ) : '';

			$global_styles = array(
				'main-title' => $ctl_main_title_typo_all,
				'title'      => $ctl_post_title_typo_all,
				'desc'       => $ctl_post_content_typo_all,
				'date'       => $ctl_date_typo_all,
			);

			$style = self::render_global_typo_style( $global_styles );
			return $style;

		}

		/**
		 * Render global styles
		 *
		 * @param array $styles timeline style setting css.
		 */
		public static function render_global_style( $styles ) {
			$style = '.ctl-wrapper{';
			foreach ( $styles as $key => $value ) {
				if ( '' !== $value ) {
					$style .= $key . ': ' . $value . ';';
				}
			}

			return $style;
		}

		/**
		 * Render global typography style
		 *
		 * @param array $style timeline typography settings.
		 */
		public static function render_global_typo_style( $style ) {
			$typo_value = '';
			foreach ( $style as $key => $value ) {
				$typography = explode( ';', $value );

				foreach ( $typography as $property ) {
					$property = trim( $property );

					if ( ! empty( $property ) ) {
						$property_parts = explode( ':', $property );
						$property_key   = trim( $property_parts[0] );
						$property_value = trim( $property_parts[1] );

						$typo_value .= "--ctw-cbx-$key-$property_key: $property_value;";
					}
				}
			}
			$typo_value .= '}';
			return $typo_value;
		}

		/**
		 * Timeline Type Settings output with key value pair.
		 *
		 * @param object $settings get typograph settings.
		 */
		public static function ctl_typo_output( $settings ) {
			$output        = '';
			$important     = '';
			$font_family   = ( ! empty( $settings['font-family'] ) && is_string( $settings['font-family'] ) ) ? preg_replace( '/[^a-zA-Z0-9,\s\'"_-]/', '', sanitize_text_field( $settings['font-family'] ) ) : '';
			$backup_family = ( ! empty( $settings['backup-font-family'] ) && is_string( $settings['backup-font-family'] ) ) ? preg_replace( '/[^a-zA-Z0-9,\s\'"_-]/', '', sanitize_text_field( $settings['backup-font-family'] ) ) : '';

			if ( $backup_family ) {
				$backup_family = ', ' . $backup_family;
			}

			if ( $font_family ) {
				$output .= 'font-family:' . $font_family . '' . $backup_family . $important . ';';
			}

			// Common font properties.
			$properties = array(
				'color',
				'font-weight',
				'font-style',
				'font-variant',
				'text-align',
				'text-transform',
				'text-decoration',
			);

			$allowed_property_values = array(
				'font-weight'    => array( 'normal', 'bold', 'bolder', 'lighter', '100', '200', '300', '400', '500', '600', '700', '800', '900' ),
				'font-style'     => array( 'normal', 'italic', 'oblique' ),
				'font-variant'   => array( 'normal', 'small-caps' ),
				'text-align'     => array( 'left', 'right', 'center', 'justify' ),
				'text-transform' => array( 'none', 'capitalize', 'uppercase', 'lowercase' ),
				'text-decoration'=> array( 'none', 'underline', 'overline', 'line-through' ),
			);

			foreach ( $properties as $property ) {
				if ( isset( $settings[ $property ] ) && $settings[ $property ] !== '' ) {
					$value = is_string( $settings[ $property ] ) ? sanitize_text_field( $settings[ $property ] ) : '';

					if ( 'color' === $property ) {
						$value = self::sanitize_css_color_value( $value );
					} elseif ( ! isset( $allowed_property_values[ $property ] ) || ! in_array( $value, $allowed_property_values[ $property ], true ) ) {
						$value = '';
					}

					if ( '' !== $value ) {
						$output .= $property . ':' . $value . $important . ';';
					}
				}
			}

			$properties = array(
				'font-size',
				'line-height',
				'letter-spacing',
				'word-spacing',
			);

			$allowed_units    = array( 'px', 'em', 'rem', '%' );
			$unit             = ( ! empty( $settings['unit'] ) && in_array( $settings['unit'], $allowed_units, true ) ) ? $settings['unit'] : 'px';
			$line_height_unit = ( ! empty( $settings['line_height_unit'] ) && in_array( $settings['line_height_unit'], $allowed_units, true ) ) ? $settings['line_height_unit'] : 'px';

			foreach ( $properties as $property ) {
				if ( isset( $settings[ $property ] ) && $settings[ $property ] !== '' ) {
					$current_unit = ( $property === 'line-height' ) ? $line_height_unit : $unit;
					$value        = floatval( $settings[ $property ] );
					$output      .= $property . ':' . $value . $current_unit . $important . ';';
				}
			}

			return $output;
		}

		/**
		 * Minify CSS
		 *
		 * @param string $css timeline css.
		 */
		public static function clt_minify_css( $css ) {
			$buffer = $css;
			// Remove comments.
			$buffer = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer );
			// Remove space after colons.
			$buffer = str_replace( ': ', ':', $buffer );
			// Remove whitespace.
			$buffer = str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $buffer );
			$buffer = preg_replace( '/\s{2,}/', ' ', $buffer );
			// Write everything out.
			return $buffer;
		}

		/**
		 * Sanitize custom CSS before output.
		 *
		 * @param string $css Custom CSS.
		 */
		public static function sanitize_custom_css( $css ) {
			return is_string( $css ) ? wp_strip_all_tags( $css ) : '';
		}

		/**
		 * Sanitize CSS color values used in style variables.
		 *
		 * @param string $value   CSS color value.
		 * @param string $default Fallback value.
		 */
		public static function sanitize_css_color_value( $value, $default = '' ) {
			$value = is_string( $value ) ? trim( $value ) : '';

			if ( 'none' === strtolower( $value ) ) {
				return 'none';
			}

			$sanitized = sanitize_hex_color( $value );

			return $sanitized ? $sanitized : $default;
		}

	}
};

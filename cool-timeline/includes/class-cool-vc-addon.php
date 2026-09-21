<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoolTmVCAddon {

	/**
	 * Registers our plugin with WordPress.
	 */
	public static function register() {
		$vc_addon = new self();
		add_action( 'init', array( $vc_addon, 'ctl_vc_addon' ) );
		add_action( 'admin_enqueue_scripts', array( $vc_addon, 'ctl_vc_admin_assets' ) );
		add_action( 'vc_backend_editor_enqueue_js_css', array( $vc_addon, 'ctl_vc_admin_assets' ) );
		add_action( 'vc_frontend_editor_enqueue_js_css', array( $vc_addon, 'ctl_vc_admin_assets' ) );
	}

	/**
	 * Compact Pro teaser styles for WPBakery edit form.
	 */
	public function ctl_vc_admin_assets() {
		if ( ! defined( 'WPB_VC_VERSION' ) ) {
			return;
		}

		$css = '
		.wpb_el_type_ctl_pro_teaser {
			margin: 6px 0 !important;
			padding: 0 15px !important;
		}
		.wpb_el_type_ctl_pro_teaser > .wpb_element_label,
		.wpb_el_type_ctl_pro_teaser > .vc_description,
		.wpb_el_type_ctl_pro_teaser .vc_description {
			display: none !important;
		}
		.wpb_el_type_ctl_pro_teaser .edit_form_line {
			padding: 0 !important;
		}
		.ctl-vc-pro-teaser {
			display: flex !important;
			align-items: center !important;
			justify-content: space-between !important;
			gap: 10px !important;
			width: 100% !important;
			max-width: 100% !important;
			margin: 0 !important;
			padding: 8px 10px !important;
			border: 1px solid #e2e4e7 !important;
			border-radius: 6px !important;
			background: #f6f7f7 !important;
			box-sizing: border-box !important;
			text-decoration: none !important;
			color: #1d2327 !important;
			box-shadow: none !important;
		}
		.ctl-vc-pro-teaser:hover,
		.ctl-vc-pro-teaser:focus {
			border-color: #c3a6d6 !important;
			background: #faf7fc !important;
			color: #1d2327 !important;
			text-decoration: none !important;
		}
		.ctl-vc-pro-teaser__main {
			display: block !important;
			min-width: 0 !important;
			flex: 1 1 auto !important;
		}
		.ctl-vc-pro-teaser__head {
			display: flex !important;
			align-items: center !important;
			flex-wrap: wrap !important;
			gap: 6px !important;
			margin: 0 !important;
			font-size: 12px !important;
			font-weight: 600 !important;
			line-height: 1.3 !important;
			color: #1d2327 !important;
		}
		.ctl-vc-pro-teaser__title {
			color: #1d2327 !important;
			text-decoration: none !important;
		}
		.ctl-vc-pro-teaser__badge {
			display: inline-block !important;
			background: #8e44ad !important;
			color: #fff !important;
			font-size: 9px !important;
			line-height: 1 !important;
			padding: 2px 5px !important;
			border-radius: 3px !important;
			font-weight: 700 !important;
			letter-spacing: 0.04em !important;
			text-transform: uppercase !important;
		}
		.ctl-vc-pro-teaser__desc {
			display: block !important;
			margin: 3px 0 0 !important;
			font-size: 11px !important;
			line-height: 1.35 !important;
			font-weight: 400 !important;
			color: #787c82 !important;
			white-space: nowrap !important;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
		}
		.ctl-vc-pro-teaser__lock {
			flex: 0 0 auto !important;
			width: 22px !important;
			height: 22px !important;
			border-radius: 50% !important;
			background: #fff !important;
			border: 1px solid #dcdcde !important;
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			color: #8e44ad !important;
		}
		.wpb_el_type_ctl_vc_section {
			margin: 10px 0 2px !important;
			padding: 0 15px !important;
		}
		.wpb_el_type_ctl_vc_section > .wpb_element_label,
		.wpb_el_type_ctl_vc_section > .vc_description,
		.wpb_el_type_ctl_vc_section .vc_description {
			display: none !important;
		}
		.wpb_el_type_ctl_vc_section .edit_form_line {
			padding: 0 !important;
		}
		';

		wp_register_style( 'ctl-vc-pro-teaser', false, array(), CTL_V );
		wp_enqueue_style( 'ctl-vc-pro-teaser' );
		wp_add_inline_style( 'ctl-vc-pro-teaser', $css );

		$pro_url = esc_url( CTL_BUY_PRO . '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=vc-addon-teaser' );
		$js      = '(function($){var proUrl=' . wp_json_encode( $pro_url ) . ';var locks={skin:["default"],pagination:["default"],"pagination-design":["style-1"],based:["default"],"date-format":["F j","F j Y","Y-m-d","m/d/Y","d/m/Y","F j Y g:i A","Y"],animation:["none","fade-up"]};$(document).on("change","select[name=\'skin\'],select[name=\'pagination\'],select[name=\'pagination-design\'],select[name=\'based\'],select[name=\'date-format\'],select[name=\'animation\']",function(){var $el=$(this),name=$el.attr("name"),allowed=locks[name];if(!allowed){return;}if(allowed.indexOf(String($el.val()))!==-1){return;}$el.val(allowed[0]);window.open(proUrl,"_blank","noopener,noreferrer");});}(window.jQuery));';

		wp_register_script( 'ctl-vc-pro-lock', false, array( 'jquery' ), CTL_V, true );
		wp_enqueue_script( 'ctl-vc-pro-lock' );
		wp_add_inline_script( 'ctl-vc-pro-lock', $js );
	}

	/**
	 * Compact Pro marketing row param for WPBakery.
	 *
	 * @param array  $settings Param settings.
	 * @param string $value    Stored value (unused).
	 * @return string
	 */
	public function ctl_vc_pro_teaser_param( $settings, $value ) {
		$title = isset( $settings['teaser_title'] ) ? $settings['teaser_title'] : '';
		$desc  = isset( $settings['teaser_desc'] ) ? $settings['teaser_desc'] : '';
		$url   = esc_url( CTL_BUY_PRO . '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=vc-addon-teaser' );
		$name  = isset( $settings['param_name'] ) ? $settings['param_name'] : '';

		// Inline styles keep layout intact even if WPB loads the form before enqueued CSS.
		$row   = 'display:flex;align-items:center;justify-content:space-between;gap:10px;width:100%;margin:0;padding:8px 10px;border:1px solid #e2e4e7;border-radius:6px;background:#f6f7f7;box-sizing:border-box;text-decoration:none;color:#1d2327;';
		$head  = 'display:flex;align-items:center;flex-wrap:wrap;gap:6px;margin:0;font-size:12px;font-weight:600;line-height:1.3;color:#1d2327;';
		$badge = 'display:inline-block;background:#8e44ad;color:#fff;font-size:9px;line-height:1;padding:2px 5px;border-radius:3px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;';
		$desc_s = 'display:block;margin:3px 0 0;font-size:11px;line-height:1.35;font-weight:400;color:#787c82;';
		$lock  = 'flex:0 0 auto;width:22px;height:22px;border-radius:50%;background:#fff;border:1px solid #dcdcde;display:inline-flex;align-items:center;justify-content:center;color:#8e44ad;';

		$html  = '<a class="ctl-vc-pro-teaser" href="' . $url . '" target="_blank" rel="noopener noreferrer" title="Upgrade to Pro" style="' . esc_attr( $row ) . '">';
		$html .= '<span class="ctl-vc-pro-teaser__main" style="display:block;min-width:0;flex:1 1 auto;">';
		$html .= '<span class="ctl-vc-pro-teaser__head" style="' . esc_attr( $head ) . '">';
		$html .= '<span class="ctl-vc-pro-teaser__title" style="color:#1d2327;text-decoration:none;">' . esc_html( $title ) . '</span>';
		$html .= '<span class="ctl-vc-pro-teaser__badge" style="' . esc_attr( $badge ) . '">PRO</span>';
		$html .= '</span>';
		$html .= '<span class="ctl-vc-pro-teaser__desc" style="' . esc_attr( $desc_s ) . '">' . esc_html( $desc ) . '</span>';
		$html .= '</span>';
		$html .= '<span class="ctl-vc-pro-teaser__lock" aria-hidden="true" style="' . esc_attr( $lock ) . '"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>';
		$html .= '</a>';
		$html .= '<input type="hidden" name="' . esc_attr( $name ) . '" class="wpb_vc_param_value ' . esc_attr( $name ) . ' ctl_pro_teaser" value="" />';

		return $html;
	}

	/**
	 * Section heading param for cleaner WPBakery grouping.
	 *
	 * @param array  $settings Param settings.
	 * @param string $value    Stored value (unused).
	 * @return string
	 */
	public function ctl_vc_section_param( $settings, $value ) {
		$title = isset( $settings['section_title'] ) ? $settings['section_title'] : '';
		$name  = isset( $settings['param_name'] ) ? $settings['param_name'] : '';
		$style = 'display:block;width:100%;margin:0;padding:8px 10px;border:1px solid #e2e4e7;border-radius:4px;background:#f0f0f1;font-size:11px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#1d2327;box-sizing:border-box;';

		$html  = '<div class="ctl-vc-section" style="' . esc_attr( $style ) . '">' . esc_html( $title ) . '</div>';
		$html .= '<input type="hidden" name="' . esc_attr( $name ) . '" class="wpb_vc_param_value ' . esc_attr( $name ) . ' ctl_vc_section" value="" />';

		return $html;
	}

	/**
	 * Build a compact Pro teaser param definition.
	 *
	 * @param string $param_name Unique param name (not used by free shortcode).
	 * @param string $title      Row label.
	 * @param string $desc       One-line hint.
	 * @param string $group      WPBakery settings tab.
	 * @return array
	 */
	private function ctl_vc_pro_teaser( $param_name, $title, $desc, $group = 'General' ) {
		return array(
			'type'         => 'ctl_pro_teaser',
			'heading'      => '',
			'param_name'   => $param_name,
			'teaser_title' => $title,
			'teaser_desc'  => $desc,
			'group'        => $group,
			'save_always'  => true,
		);
	}

	/**
	 * Build a visual section heading for a WPBakery tab.
	 *
	 * @param string $param_name Unique param name.
	 * @param string $title      Section label.
	 * @param string $group      WPBakery settings tab.
	 * @return array
	 */
	private function ctl_vc_section( $param_name, $title, $group ) {
		return array(
			'type'          => 'ctl_vc_section',
			'heading'       => '',
			'param_name'    => $param_name,
			'section_title' => $title,
			'group'         => $group,
			'save_always'   => true,
		);
	}

	public function ctl_vc_addon() {
		if ( ! defined( 'WPB_VC_VERSION' ) ) {
			return;
		}

		if ( function_exists( 'vc_add_shortcode_param' ) ) {
			vc_add_shortcode_param( 'ctl_pro_teaser', array( $this, 'ctl_vc_pro_teaser_param' ) );
			vc_add_shortcode_param( 'ctl_vc_section', array( $this, 'ctl_vc_section_param' ) );
		}

		$date_formats      = array(
			'F j'           => 'F j',
			'F j Y'         => 'F j Y',
			'Y-m-d'         => 'Y-m-d',
			'm/d/Y'         => 'm/d/Y',
			'd/m/Y'         => 'd/m/Y',
			'F j Y g:i A'   => 'F j Y g:i A',
			'Y'             => 'Y',
			'Custom (Pro)'  => 'custom',
		);
		$animation_effects = array(
			'None'              => 'none',
			'Fade In Up'        => 'fade-up',
			'Zoom In (Pro)'     => 'zoom-in',
			'Flip Left (Pro)'   => 'flip-left',
			'Slide Up (Pro)'    => 'slide-up',
			'Fade Down (Pro)'   => 'fade-down',
			'Fade Left (Pro)'   => 'fade-left',
			'Fade Right (Pro)'  => 'fade-right',
		);

		$group_layout  = 'Layout';
		$group_content = 'Content';
		$group_extra   = 'Extra';

		vc_map(
			array(
				'name'        => 'Cool Timeline',
				'description' => 'Create Stories Timeline',
				'base'        => 'cool-timeline',
				'class'       => '',
				'controls'    => 'full',
				'icon'        => CTL_PLUGIN_URL . 'assets/images/timeline-icon2-32x32.png',
				'category'    => 'Cool Timeline',
				'params'      => array(
					// ---- Layout ----
					$this->ctl_vc_section( 'ctl_sec_layout', 'Layout', $group_layout ),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Layout',
						'param_name'  => 'layout',
						'value'       => array(
							'Vertical'   => 'default',
							'Horizontal' => 'horizontal',
							'One Side'   => 'one-side',
							'Compact'    => 'compact',
						),
						'description' => 'Choose how stories are arranged on the page.',
						'group'       => $group_layout,
						'save_always' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Timeline Width',
						'param_name'  => 'timeline-width',
						'value'       => array(
							'Full-width' => 'full',
							'Custom'     => 'custom',
						),
						'description' => 'Full-width spans the content area; Custom sets a max width.',
						'group'       => $group_layout,
						'save_always' => true,
						'std'         => 'custom',
					),
					array(
						'type'        => 'textfield',
						'heading'     => 'Custom Width',
						'param_name'  => 'timeline-custom-width',
						'value'       => '1140',
						'description' => 'Timeline max width. (px)',
						'group'       => $group_layout,
						'save_always' => true,
						'dependency'  => array(
							'element' => 'timeline-width',
							'value'   => array( 'custom' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Skin',
						'param_name'  => 'skin',
						'value'       => array(
							'Default'     => 'default',
							'Dark (Pro)'  => 'dark',
							'Light (Pro)' => 'light',
						),
						'description' => 'Dark and Light skins are available in Pro.',
						'group'       => $group_layout,
						'save_always' => true,
						'std'         => 'default',
						'dependency'  => array(
							'element' => 'layout',
							'value'   => array( 'default', 'one-side', 'compact' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Story Marker',
						'param_name'  => 'icons',
						'value'       => array(
							'Dot'  => 'NO',
							'Icon' => 'YES',
						),
						'description' => 'Icon shown on the timeline line.',
						'group'       => $group_layout,
						'save_always' => true,
					),
					$this->ctl_vc_section( 'ctl_sec_slides', 'Horizontal Slides', $group_layout ),
					array(
						'type'        => 'textfield',
						'heading'     => 'Slides To Show',
						'param_name'  => 'items',
						'value'       => 4,
						'description' => 'Stories visible at once. Autoplay & starting story are in Pro.',
						'group'       => $group_layout,
						'save_always' => true,
						'dependency'  => array(
							'element' => 'layout',
							'value'   => array( 'horizontal' ),
						),
					),
					$this->ctl_vc_section( 'ctl_sec_design', 'Design', $group_layout ),
					$this->ctl_vc_pro_teaser(
						'ctl_pro_designs',
						'Unlock designs',
						'Pro adds Flat, Classic, Elegant, Clean, Modern, and Minimal presets',
						$group_layout
					),
					$this->ctl_vc_section( 'ctl_sec_connector', 'Connector & Spacing', $group_layout ),
					$this->ctl_vc_pro_teaser(
						'ctl_pro_connector',
						'More control over timeline appearance',
						'Connector style · story padding & radius · marker position',
						$group_layout
					),

					// ---- Content ----
					$this->ctl_vc_section( 'ctl_sec_categories', 'Categories', $group_content ),
					$this->ctl_vc_pro_teaser(
						'ctl_pro_categories',
						'Filter stories by category',
						'Multi-category selection · frontend filters · post timeline',
						$group_content
					),
					$this->ctl_vc_section( 'ctl_sec_order', 'Story Order', $group_content ),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Order',
						'param_name'  => 'order',
						'value'       => array(
							'Oldest' => 'ASC',
							'Newest' => 'DESC',
						),
						'description' => 'Oldest (1900 → 2017) / Newest (2017 → 1900).',
						'group'       => $group_content,
						'save_always' => true,
						'admin_label' => true,
						'std'         => 'DESC',
					),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Timeline Based On',
						'param_name'  => 'based',
						'value'       => array(
							'Default (Date Based)' => 'default',
							'Custom Order (Pro)'   => 'custom',
						),
						'description' => 'Date-based or custom story order (drag & drop). Custom Order is Pro.',
						'group'       => $group_content,
						'save_always' => true,
						'std'         => 'default',
					),
					$this->ctl_vc_section( 'ctl_sec_story_date', 'Story Date', $group_content ),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Date Format',
						'param_name'  => 'date-format',
						'value'       => $date_formats,
						'description' => 'How the story date appears.',
						'group'       => $group_content,
						'save_always' => true,
					),
					$this->ctl_vc_pro_teaser(
						'custom-date-format',
						'Custom Date Format',
						'Use any PHP date format string when Date Format is set to Custom.',
						$group_content
					),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Year Label',
						'param_name'  => 'year-label',
						'value'       => array(
							'Default' => '',
							'Show'    => 'show',
							'Hide'    => 'hide',
						),
						'description' => 'Only for Vertical and One sided layout.',
						'group'       => $group_content,
						'save_always' => true,
						'dependency'  => array(
							'element' => 'layout',
							'value'   => array( 'default', 'one-side' ),
						),
					),
					$this->ctl_vc_section( 'ctl_sec_story_content', 'Story Content', $group_content ),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Title',
						'param_name'  => 'show-title',
						'value'       => array(
							'Show' => 'show',
							'Hide' => 'hide',
						),
						'description' => 'Show or hide the story title.',
						'group'       => $group_content,
						'save_always' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Title Link',
						'param_name'  => 'title-link',
						'value'       => array(
							'Enable'  => 'enable',
							'Disable' => 'disable',
						),
						'description' => 'Make the story title clickable.',
						'group'       => $group_content,
						'save_always' => true,
						'dependency'  => array(
							'element' => 'show-title',
							'value'   => array( 'show' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Content Type',
						'param_name'  => 'story-content',
						'value'       => array(
							'Short' => 'short',
							'Full'  => 'full',
							'Hide'  => 'hide',
						),
						'description' => 'Short shows an excerpt. Full shows the complete story. Hide removes the description.',
						'group'       => $group_content,
						'save_always' => true,
					),
					array(
						'type'        => 'textfield',
						'heading'     => 'Content Length',
						'param_name'  => 'content-length',
						'value'       => '50',
						'description' => 'Number of words shown in the excerpt.',
						'group'       => $group_content,
						'save_always' => true,
						'dependency'  => array(
							'element' => 'story-content',
							'value'   => array( 'short' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Read More',
						'param_name'  => 'read-more',
						'value'       => array(
							'Show' => 'show',
							'Hide' => 'hide',
						),
						'description' => 'Adds a link to open the full story.',
						'group'       => $group_content,
						'save_always' => true,
					),
					array(
						'type'        => 'textfield',
						'heading'     => 'Read More Text',
						'param_name'  => 'read-more-text',
						'value'       => 'Read More',
						'description' => 'Custom label for the Read More.',
						'group'       => $group_content,
						'save_always' => true,
						'dependency'  => array(
							'element' => 'read-more',
							'value'   => array( 'show' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Open Read More in',
						'param_name'  => 'read-more-target',
						'value'       => array(
							'Same Tab' => '_self',
							'New Tab'  => '_blank',
						),
						'group'       => $group_content,
						'save_always' => true,
						'dependency'  => array(
							'element' => 'read-more',
							'value'   => array( 'show' ),
						),
					),
					$this->ctl_vc_section( 'ctl_sec_pagination', 'Pagination', $group_content ),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Pagination Type',
						'param_name'  => 'pagination',
						'value'       => array(
							'Default'               => 'default',
							'Off (Pro)'             => 'off',
							'Ajax Load More (Pro)'  => 'ajax_load_more',
						),
						'description' => 'Off shows all stories at once. Off and Ajax Load More are Pro.',
						'group'       => $group_content,
						'save_always' => true,
						'std'         => 'default',
					),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Pagination Design',
						'param_name'  => 'pagination-design',
						'value'       => array(
							'Classic'       => 'style-1',
							'Minimal (Pro)' => 'style-2',
							'Circle (Pro)'  => 'style-3',
							'Joined (Pro)'  => 'style-4',
						),
						'description' => 'Preset look for the numeric pagination. Extra designs are Pro.',
						'group'       => $group_content,
						'save_always' => true,
						'std'         => 'style-1',
					),
					array(
						'type'        => 'textfield',
						'heading'     => 'Stories Per Page',
						'param_name'  => 'show-posts',
						'value'       => 10,
						'description' => 'Number of stories loaded at a time.',
						'group'       => $group_content,
						'save_always' => true,
						'admin_label' => true,
					),

					// ---- Extra ----
					$this->ctl_vc_section( 'ctl_sec_animation', 'Animation', $group_extra ),
					array(
						'type'        => 'dropdown',
						'heading'     => 'Entrance Animation',
						'param_name'  => 'animation',
						'value'       => $animation_effects,
						'description' => 'How stories appear as visitors scroll. Extra effects are Pro.',
						'group'       => $group_extra,
						'save_always' => true,
						'std'         => 'none',
						'dependency'  => array(
							'element' => 'layout',
							'value'   => array( 'default', 'one-side', 'compact' ),
						),
					),
					$this->ctl_vc_pro_teaser(
						'ctl_pro_animations',
						'More entrance effects in Pro',
						'Zoom, flip, slide & more variants',
						$group_extra
					),
					$this->ctl_vc_section( 'ctl_sec_year_nav', 'Year Navigation', $group_extra ),
					$this->ctl_vc_pro_teaser(
						'ctl_pro_year_nav',
						'Jump between years instantly',
						'Clickable year menu · left, right, or bottom · 3 styles',
						$group_extra
					),
					$this->ctl_vc_section( 'ctl_sec_cat_filters', 'Category Filters', $group_extra ),
					$this->ctl_vc_pro_teaser(
						'ctl_pro_cat_filters',
						'Let visitors filter the timeline',
						'Category filter bar · choose which categories appear',
						$group_extra
					),
					$this->ctl_vc_section( 'ctl_sec_scroll', 'Scroll Fill', $group_extra ),
					$this->ctl_vc_pro_teaser(
						'ctl_pro_scroll',
						'Highlight progress as users scroll',
						'Animated line fill on vertical & horizontal layouts',
						$group_extra
					),
					$this->ctl_vc_section( 'ctl_sec_spacing', 'Spacing & Size', $group_extra ),
					$this->ctl_vc_pro_teaser(
						'ctl_pro_spacing',
						'Fine-tune every spacing control',
						'Story padding & spacing · corner radius · marker position',
						$group_extra
					),
				),
			)
		);
	}
}
CoolTmVCAddon::register();

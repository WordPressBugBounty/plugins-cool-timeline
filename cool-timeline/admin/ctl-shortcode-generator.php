<?php
/**
 *
 * This file is responsible for creating all admin settings in Timeline Builder (post)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CSF_free_shortcode_generator' ) ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
	class CSF_free_shortcode_generator {


		/**
		 * The Constructor
		 */
		public function __construct() {
			// Must register before CSF::setup() (after_setup_theme / init) picks up shortcoders.
			$this->init_shortcode_generator();
			add_action( 'admin_print_styles', array( $this, 'ctl_custom_shortcode_style' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'ctl_preview_script' ) );
		}

		public function ctl_custom_shortcode_style() {
			$pro_url = esc_url( CTL_BUY_PRO . '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=shortcode-generator-teaser' );
			echo '<style>
        span.dashicon.dashicons.dashicons-ctl-custom-icon:before {
        content:"";
        background: url(' . esc_url( CTL_PLUGIN_URL ) . 'assets/images/timeline-icon2-32x32.png);
        background-size: contain;
        background-repeat: no-repeat;
        height: 20px;
        display: block;

        }
       #wp-content-wrap a[data-modal-id="ctl_timeline_shortcode"]:before {
        content: "";
        background: url(' . esc_url( CTL_PLUGIN_URL ) . 'assets/images/cool-timeline-icon.svg);
        background-size: contain;
        background-repeat: no-repeat;
        height: 17px;
        display: inline-block;
        margin: 0px 1px -3px 0;
        width: 20px;
        }
        .csf-shortcode-single .csf-modal-content {
            height: 655px !important;

        }
        
        #csf-modal-ctl_timeline_shortcode .csf-modal-inner {
            height: 500px !important;
        }
        #csf-modal-ctl_timeline_shortcode .csf-modal-content {            
            height: 400px !important;        
        }   
                     
        #ctl_preview{
			width: 200%;
			height: 200%;
			transform: translate(-25%, -25%) scale(0.5);
		}

		#csf-modal-ctl_timeline_shortcode .csf-field-content:has(.ctl-sc-pro-teaser) {
			padding-top: 4px !important;
			padding-bottom: 4px !important;
		}
		#csf-modal-ctl_timeline_shortcode .ctl-sc-pro-teaser {
			position: relative;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			margin: 2px 0 4px;
			padding: 7px 10px;
			border: 1px solid #e2e4e7;
			border-radius: 6px;
			background: #f6f7f7;
			cursor: pointer;
			transition: border-color 0.15s ease, background 0.15s ease;
		}
		#csf-modal-ctl_timeline_shortcode .ctl-sc-pro-teaser:hover {
			border-color: #c3a6d6;
			background: #faf7fc;
		}
		#csf-modal-ctl_timeline_shortcode .ctl-sc-pro-teaser__main {
			min-width: 0;
			flex: 1;
		}
		#csf-modal-ctl_timeline_shortcode .ctl-sc-pro-teaser__head {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 6px;
			margin: 0;
			font-size: 12px;
			font-weight: 600;
			line-height: 1.3;
			color: #1d2327;
		}
		#csf-modal-ctl_timeline_shortcode .ctl-sc-pro-teaser__badge {
			display: inline-block;
			background: #8e44ad;
			color: #fff;
			font-size: 9px;
			line-height: 1;
			padding: 2px 5px;
			border-radius: 3px;
			font-weight: 700;
			letter-spacing: 0.04em;
			text-transform: uppercase;
		}
		#csf-modal-ctl_timeline_shortcode .ctl-sc-pro-teaser__desc {
			margin: 2px 0 0;
			font-size: 11px;
			line-height: 1.35;
			color: #787c82;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}
		#csf-modal-ctl_timeline_shortcode .ctl-sc-pro-teaser__lock {
			flex-shrink: 0;
			width: 22px;
			height: 22px;
			border-radius: 50%;
			background: #fff;
			border: 1px solid #dcdcde;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			color: #8e44ad;
		}
		#csf-modal-ctl_timeline_shortcode .ctl-sc-pro-teaser__overlay {
			position: absolute;
			inset: 0;
			z-index: 2;
		}
		#csf-modal-ctl_timeline_shortcode .ctl-sc-section {
			margin: 14px 0 4px;
			padding: 7px 10px;
			border: 1px solid #e2e4e7;
			border-left: 3px solid #8e44ad;
			border-radius: 4px;
			background: #f6f7f7;
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 0.04em;
			text-transform: uppercase;
			color: #1d2327;
		}
		#csf-modal-ctl_timeline_shortcode .csf-tabbed-content > .csf-field-content:has(.ctl-sc-section):first-child .ctl-sc-section,
		#csf-modal-ctl_timeline_shortcode .csf-tabbed-content > .csf-field:first-child .ctl-sc-section {
			margin-top: 2px;
		}
		#csf-modal-ctl_timeline_shortcode .csf-field-content:has(.ctl-sc-section) {
			padding-top: 2px !important;
			padding-bottom: 2px !important;
			border-top: 0 !important;
		}
		#csf-modal-ctl_timeline_shortcode .csf-field-content:has(.ctl-sc-section) + .csf-field-content:has(.ctl-sc-pro-teaser) {
			padding-top: 0 !important;
			border-top: 0 !important;
		}
		#csf-modal-ctl_timeline_shortcode .csf-field-content:has(.ctl-sc-section) + .csf-field-content:has(.ctl-sc-pro-teaser) .ctl-sc-pro-teaser {
			margin-top: 0;
			border-top-left-radius: 0;
			border-top-right-radius: 0;
			border-top-color: transparent;
		}
        </style>
		<script>
		(function () {
			var proUrl = ' . wp_json_encode( $pro_url ) . ';
			var freeLocks = {
				skin: ["default"],
				pagination: ["default"],
				"pagination-design": ["style-1"],
				based: ["default"],
				"date-format": ["F j", "F j Y", "Y-m-d", "m/d/Y", "d/m/Y", "F j Y g:i A", "Y"],
				animation: ["none", "fade-up"]
			};

			document.addEventListener("click", function (event) {
				var teaser = event.target.closest(".ctl-sc-pro-teaser");
				if (!teaser) { return; }
				event.preventDefault();
				window.open(proUrl, "_blank", "noopener,noreferrer");
			});

			document.addEventListener("change", function (event) {
				var el = event.target;
				if (!el || !el.name || el.tagName !== "SELECT") { return; }
				if (!el.closest("#csf-modal-ctl_timeline_shortcode")) { return; }

				var key = null;
				Object.keys(freeLocks).forEach(function (id) {
					if (el.name.indexOf("[" + id + "]") !== -1 || el.name === id) {
						key = id;
					}
				});
				if (!key) { return; }

				var allowed = freeLocks[key];
				if (allowed.indexOf(String(el.value)) !== -1) { return; }
				el.value = allowed[0];
				window.open(proUrl, "_blank", "noopener,noreferrer");
			});
		})();
		</script>';
		}

		/**
		 * Compact Pro marketing row for classic-editor shortcode modal.
		 *
		 * @param string     $title       Row label.
		 * @param string     $description One-line hint.
		 * @param array|null $dependency  Optional CSF dependency rule.
		 * @return array CSF content field.
		 */
		private function ctl_pro_teaser_field( $title, $description, $dependency = null ) {
			$html  = '<div class="ctl-sc-pro-teaser" role="link" tabindex="0" title="Upgrade to Pro">';
			$html .= '<div class="ctl-sc-pro-teaser__overlay" aria-hidden="true"></div>';
			$html .= '<div class="ctl-sc-pro-teaser__main">';
			$html .= '<div class="ctl-sc-pro-teaser__head"><span>' . esc_html( $title ) . '</span><span class="ctl-sc-pro-teaser__badge">PRO</span></div>';
			$html .= '<p class="ctl-sc-pro-teaser__desc">' . esc_html( $description ) . '</p>';
			$html .= '</div>';
			$html .= '<span class="ctl-sc-pro-teaser__lock" aria-hidden="true"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>';
			$html .= '</div>';

			$field = array(
				'type'    => 'content',
				'content' => $html,
			);

			if ( null !== $dependency ) {
				$field['dependency'] = $dependency;
			}

			return $field;
		}

		/**
		 * Small section label inside a shortcode-generator tab.
		 *
		 * @param string     $title      Section title.
		 * @param array|null $dependency Optional CSF dependency rule.
		 * @return array CSF content field.
		 */
		private function ctl_sc_section( $title, $dependency = null ) {
			$field = array(
				'type'    => 'content',
				'content' => '<div class="ctl-sc-section">' . esc_html( $title ) . '</div>',
			);

			if ( null !== $dependency ) {
				$field['dependency'] = $dependency;
			}

			return $field;
		}

		// Preview Scripts Loaded
		public function ctl_preview_script() {
			wp_enqueue_script( 'ctl_preview_scripts', esc_url( CTL_PLUGIN_URL . 'includes/shortcodes/assets/js/ctl-preview.min.js' ), array( 'jquery' ), CTL_V, false );
			wp_localize_script(
				'ctl_preview_scripts',
				'myAjax',
				array(
					'ajaxurl'   => esc_url( admin_url( 'admin-ajax.php' ) ),
					'nonce'     => wp_create_nonce( 'ctl_preview' ),
					'pluginUrl' => esc_url( CTL_PLUGIN_URL ),
				)
			);
		}

		public function init_shortcode_generator() {

		
			// Sanitize input data (read-only admin screen detection).
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$id = isset( $_GET['post'] ) ? intval( wp_unslash( $_GET['post'] ) ) : 0;
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : get_post_type( $id );

			// change block name if older block exists in current page condition start
			$block_name = 'ctl-gutenberg-block';
			if ( $id > 0 ) {
				$ctl_post_id  = (int) $id;
				$all_blocks   = array();
				$post_content = get_post( $ctl_post_id );
				if ( null !== $post_content ) {
					$parse_data = parse_blocks( $post_content->post_content );
					foreach ( $parse_data as $parse ) {
						if ( isset( $parse['blockName'] ) && null !== $parse['blockName'] ) {
							array_push( $all_blocks, $parse['blockName'] );
						}
					};
				};

				if ( ! in_array( 'csf/ctl-timeline-shortcode', $all_blocks ) ) {
					$block_name = 'ctl-gutenberg-block';
				} else {
					$block_name = 'ctl_timeline_shortcode';
				};
			}
			// change block name if older block exists in current page condition end

			if ( $post_type !== 'page' && $post_type !== 'post' && $post_type != '' ) {
				return;
			}
			if ( class_exists( 'CSF' ) ) {

				//
				// Set a unique slug-like ID
				$prefix = 'ctl_timeline_shortcode';

				//
				// Create a shortcoder
				CSF::createShortcoder(
					$prefix,
					array(
						'button_title' => 'Add Shortcode',
						'insert_title' => 'Insert shortcode',
						'gutenberg'    => array(
							'block_name'   => $block_name,
							'title'        => 'Cool Timeline Shortcode Generator',
							'icon'         => 'ctl-custom-icon',
							'description'  => 'Cool Timeline Shortcode Generator Block.',
							'category'     => 'widgets',
							'keywords'     => array( 'shortcode', 'ctl', 'cooltimeline', 'timeline' ),
							'previewImage' => CTL_PLUGIN_URL . 'includes/cool-timeline-block/images/timeline.27d3f3c7.png',
						),
					)
				);

				//
				// A basic shortcode

				CSF::createSection(
					$prefix,
					array(
						'title'     => 'Cool Timeline Shortcode',
						'view'      => 'normal', // View model of the shortcode. `normal` `contents` `group` `repeater`
						'shortcode' => 'cool-timeline', // Set a unique slug-like name of shortcode.
						'fields'    => array(
							array(
								'id'   => 'ctl-story-tab',
								'type' => 'tabbed',
								'tabs' => array(
									array(
										'title'  => 'Layout',
										'fields' => array(
											$this->ctl_sc_section( 'Layout' ),
											array(
												'id'      => 'layout',
												'type'    => 'select',
												'title'   => 'Layout',
												'default' => 'default',
												'desc'    => 'Choose how stories are arranged on the page.',
												'options' => array(
													'default'    => 'Vertical',
													'horizontal' => 'Horizontal',
													'one-side'   => 'One Side',
													'compact'    => 'Compact',
												),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											array(
												'id'      => 'timeline-width',
												'type'    => 'select',
												'title'   => 'Timeline Width',
												'default' => 'custom',
												'options' => array(
													'full'   => 'Full-width',
													'custom' => 'Custom',
												),
												'desc'    => 'Full-width spans the content area; Custom sets a max width.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											array(
												'id'         => 'timeline-custom-width',
												'type'       => 'text',
												'title'      => 'Custom Width',
												'default'    => '1140',
												'desc'       => 'Timeline max width. (px)',
												'dependency' => array( 'timeline-width', '!=', 'full' ),
												'attributes' => array(
													'style' => 'width: 50%;',
													'type'  => 'number',
													'min'   => '400',
													'max'   => '1920',
													'step'  => '10',
												),
											),
											array(
												'id'      => 'skin',
												'type'    => 'select',
												'title'   => 'Skin',
												'default' => 'default',
												'dependency' => array( 'layout', '!=', 'horizontal' ),
												'options' => array(
													'default' => 'Default',
													'dark'    => 'Dark (Pro)',
													'light'   => 'Light (Pro)',
												),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											array(
												'id'      => 'icons',
												'type'    => 'select',
												'title'   => 'Story Marker',
												'default' => 'NO',
												'options' => array(
													'NO'  => 'Dot',
													'YES' => 'Icon',
												),
												'desc'    => 'Icon shown on the timeline line.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											$this->ctl_sc_section( 'Horizontal Slides', array( 'layout', '==', 'horizontal' ) ),
											array(
												'id'      => 'items',
												'type'    => 'text',
												'title'   => 'Slides To Show',
												'default' => '4',
												'desc'    => 'Stories visible at once. Autoplay & starting story are in Pro.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'dependency' => array( 'layout', '==', 'horizontal' ),
											),
											$this->ctl_sc_section( 'Design' ),
											$this->ctl_pro_teaser_field(
												'Unlock designs',
												'Pro adds Flat, Classic, Elegant, Clean, Modern, and Minimal presets'
											),
											$this->ctl_sc_section( 'Connector & Spacing' ),
											$this->ctl_pro_teaser_field(
												'More control over timeline appearance',
												'Connector style · story padding & radius · marker position'
											),
										),
									),
									array(
										'title'  => 'Content',
										'fields' => array(
											$this->ctl_sc_section( 'Categories' ),
											$this->ctl_pro_teaser_field(
												'Filter stories by category',
												'Multi-category selection · frontend filters · post timeline'
											),
											$this->ctl_sc_section( 'Story Order' ),
											array(
												'id'      => 'order',
												'type'    => 'select',
												'title'   => 'Order',
												'default' => 'DESC',
												'options' => array(
													'ASC'  => 'Oldest',
													'DESC' => 'Newest',
												),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
												'desc'    => 'Oldest (1900 → 2017) / Newest (2017 → 1900).',
											),
											array(
												'id'      => 'based',
												'type'    => 'select',
												'title'   => 'Timeline Based On',
												'default' => 'default',
												'options' => array(
													'default' => 'Default (Date Based)',
													'custom'  => 'Custom Order (Pro)',
												),
												'desc'    => 'Date-based or custom story order (drag & drop).',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											$this->ctl_sc_section( 'Story Date' ),
											array(
												'id'      => 'date-format',
												'type'    => 'select',
												'title'   => 'Date Format',
												'default' => 'F j',
												'options' => array(
													'F j' => 'F j',
													'F j Y' => 'F j Y',
													'Y-m-d' => 'Y-m-d',
													'm/d/Y' => 'm/d/Y',
													'd/m/Y' => 'd/m/Y',
													'F j Y g:i A' => 'F j Y g:i A',
													'Y'   => 'Y',
													'custom' => 'Custom (Pro)',
												),
												'desc'    => 'How the story date appears.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											$this->ctl_pro_teaser_field(
												'Custom Date Format',
												'Use any PHP date format string when Date Format is set to Custom.'
											),
											array(
												'id'      => 'year-label',
												'type'    => 'select',
												'title'   => 'Year Label',
												'default' => '',
												'options' => array(
													''     => 'Default',
													'show' => 'Show',
													'hide' => 'Hide',
												),
												'desc'    => 'Only for Vertical and One sided layout.',
												'dependency' => array(
													array( 'layout', '!=', 'horizontal', true ),
													array( 'layout', '!=', 'compact', true ),
												),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											$this->ctl_sc_section( 'Story Content' ),
											array(
												'id'      => 'show-title',
												'type'    => 'select',
												'title'   => 'Title',
												'default' => 'show',
												'options' => array(
													'show' => 'Show',
													'hide' => 'Hide',
												),
												'desc'    => 'Show or hide the story title.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											array(
												'id'         => 'title-link',
												'type'       => 'select',
												'title'      => 'Title Link',
												'default'    => 'enable',
												'options'    => array(
													'enable'  => 'Enable',
													'disable' => 'Disable',
												),
												'desc'       => 'Make the story title clickable.',
												'dependency' => array( 'show-title', '==', 'show' ),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											array(
												'id'      => 'story-content',
												'type'    => 'select',
												'title'   => 'Content Type',
												'default' => 'short',
												'options' => array(
													'short' => 'Short',
													'full'  => 'Full',
													'hide'  => 'Hide',
												),
												'desc'    => 'Short shows an excerpt. Full shows the complete story. Hide removes the description.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											array(
												'id'         => 'content-length',
												'type'       => 'text',
												'title'      => 'Content Length',
												'default'    => '50',
												'desc'       => 'Number of words shown in the excerpt.',
												'dependency' => array(
													array( 'story-content', '!=', 'full' ),
													array( 'story-content', '!=', 'hide' ),
												),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											array(
												'id'      => 'read-more',
												'type'    => 'select',
												'title'   => 'Read More',
												'default' => 'show',
												'options' => array(
													'show' => 'Show',
													'hide' => 'Hide',
												),
												'desc'    => 'Adds a link to open the full story.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											array(
												'id'         => 'read-more-text',
												'type'       => 'text',
												'title'      => 'Read More Text',
												'default'    => 'Read More',
												'desc'       => 'Custom label for the Read More.',
												'dependency' => array( 'read-more', '==', 'show' ),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											array(
												'id'         => 'read-more-target',
												'type'       => 'select',
												'title'      => 'Open Read More in',
												'default'    => '_self',
												'options'    => array(
													'_self'  => 'Same Tab',
													'_blank' => 'New Tab',
												),
												'dependency' => array( 'read-more', '==', 'show' ),
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											$this->ctl_sc_section( 'Pagination' ),
											array(
												'id'      => 'pagination',
												'type'    => 'select',
												'title'   => 'Pagination Type',
												'default' => 'default',
												'options' => array(
													'default'        => 'Default',
													'off'            => 'Off (Pro)',
													'ajax_load_more' => 'Ajax Load More (Pro)',
												),
												'desc'    => 'Off shows all stories at once.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											array(
												'id'      => 'pagination-design',
												'type'    => 'select',
												'title'   => 'Pagination Design',
												'default' => 'style-1',
												'options' => array(
													'style-1' => 'Classic',
													'style-2' => 'Minimal (Pro)',
													'style-3' => 'Circle (Pro)',
													'style-4' => 'Joined (Pro)',
												),
												'desc'    => 'Preset look for the numeric pagination.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											array(
												'id'      => 'show-posts',
												'type'    => 'text',
												'title'   => 'Stories Per Page',
												'default' => '10',
												'desc'    => 'Number of stories loaded at a time.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
										),
									),
									array(
										'title'  => 'Extra',
										'fields' => array(
											$this->ctl_sc_section( 'Animation', array( 'layout', '!=', 'horizontal', true ) ),
											array(
												'id'      => 'animation',
												'type'    => 'select',
												'title'   => 'Entrance Animation',
												'default' => 'none',
												'dependency' => array( 'layout', '!=', 'horizontal', true ),
												'options' => array(
													'none'       => 'None',
													'fade-up'    => 'Fade In Up',
													'zoom-in'    => 'Zoom In (Pro)',
													'flip-left'  => 'Flip Left (Pro)',
													'slide-up'   => 'Slide Up (Pro)',
													'fade-down'  => 'Fade Down (Pro)',
													'fade-left'  => 'Fade Left (Pro)',
													'fade-right' => 'Fade Right (Pro)',
												),
												'desc'    => 'How stories appear as visitors scroll.',
												'attributes' => array(
													'style' => 'width: 50%;',
												),
											),
											$this->ctl_pro_teaser_field(
												'More entrance effects in Pro',
												'Zoom, flip, slide & more variants',
												array( 'layout', '!=', 'horizontal', true )
											),
											$this->ctl_sc_section( 'Year Navigation' ),
											$this->ctl_pro_teaser_field(
												'Jump between years instantly',
												'Clickable year menu · left, right, or bottom · 3 styles'
											),
											$this->ctl_sc_section( 'Category Filters' ),
											$this->ctl_pro_teaser_field(
												'Let visitors filter the timeline',
												'Category filter bar · choose which categories appear'
											),
											$this->ctl_sc_section( 'Scroll Fill' ),
											$this->ctl_pro_teaser_field(
												'Highlight progress as users scroll',
												'Animated line fill on vertical & horizontal layouts'
											),
											$this->ctl_sc_section( 'Spacing & Size' ),
											$this->ctl_pro_teaser_field(
												'Fine-tune every spacing control',
												'Story padding & spacing · corner radius · marker position'
											),
										),
									),
									array(
										'title'  => 'Preview',
										'fields' => array(
											array(
												'id'      => 'preview',
												'type'    => 'content',
												'content' => '<iframe id="ctl_preview" name="my_iframe" src="' . esc_url( CTL_PLUGIN_URL . 'includes/shortcodes/class-ctl-shortcode-preview.php' ) . '" title="preview iframe" scrolling="auto" frameborder="0" data-preloader="' . esc_url( CTL_PLUGIN_URL . 'assets/images/clt-preloader.gif' ) . '"></iframe>',
											),
										),
									),
								),
							),
						),
					)
				);

			}

		}

	}

}

new CSF_free_shortcode_generator();

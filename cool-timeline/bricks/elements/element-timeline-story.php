<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTL_Bricks_Element_Story_Timeline extends \Bricks\Element {

	public $category     = 'timeline';
	public $name         = 'timeline-story';
	public $icon         = 'ctl-eicon-timeline-story';
	public $styles       = array( 'ctl-bricks-common', 'ctl-bricks-vertical' );
	public $css_selector = '.ctl-bricks-wrapper';

	public function get_label() {
		return esc_html__( 'Story Timeline', 'cool-timeline' );
	}

	public function get_keywords() {
		return array( 'timeline', 'story', 'history', 'roadmap', 'vertical' );
	}
	public function enqueue_scripts() {
		$styles = array(
			'ctl-bricks-common'   => array(
				'src'  => CTL_BRICKS_URL . 'assets/css/ctl-bricks-common-styles.css',
				'deps' => array(),
				'ver'  => filemtime( CTL_PLUGIN_DIR . 'bricks/assets/css/ctl-bricks-common-styles.css' ),
			),
			'ctl-bricks-vertical' => array(
				'src'  => CTL_BRICKS_URL . 'assets/css/ctl-bricks-vertical-timeline.css',
				'deps' => array( 'ctl-bricks-common' ),
				'ver'  => filemtime( CTL_PLUGIN_DIR . 'bricks/assets/css/ctl-bricks-vertical-timeline.css' ),
			),
		);

		foreach ( $styles as $handle => $style ) {
			if ( empty( $style['src'] ) ) {
				continue;
			}

			wp_enqueue_style(
				$handle,
				$style['src'],
				( isset( $style['deps'] ) && is_array( $style['deps'] ) ) ? $style['deps'] : array(),
				( isset( $style['ver'] ) ) ? $style['ver'] : CTL_V
			);
		}
	}


	/**
	 * Current Free appearance, shared by editor controls and missing builder settings.
	 *
	 * @return array
	 */
	public static function ctl_editor_defaults() {
		return array(
			'lineWidth' => '3px',
			'lineBorderWidth' => '0px',
			'iconBoxSize' => '34px',
			'iconBoxSize:tablet_portrait' => '32px',
			'iconBoxSize:mobile_landscape' => '30px',
			'iconBoxSize:mobile_portrait' => '30px',
			'iconSize' => '22px',
			'iconSize:tablet_portrait' => '21px',
			'iconSize:mobile_landscape' => '19px',
			'iconRadius' => '50',
			'iconBorderWidth' => '3px',
			'iconBorderColor' => '#02c5be',
			'labelsGap' => '5px',
			'contentBoxBorderRadius' => '15px',
			'contentBoxShadow' => '0 10px 40px rgba(15, 23, 42, 0.14)',
			'connectorBgColor' => '#02c5be',
			'connectorBdColor' => '#e6e6e6',
			'lineColor' => array( 'hex' => '#02c5be' ),
			'iconBgColor' => array( 'hex' => '#02c5be' ),
			'iconColor' => array( 'hex' => '#ffffff' ),
			'dateLabelColor' => array( 'hex' => '#02c5be' ),
			'subLabelColor' => array( 'hex' => '#9CA3AF' ),
			'yearLabelColor' => array( 'hex' => '#ffffff' ),
			'yearLabelBg' => array( 'hex' => '#4f4f4f' ),
			'contentBoxBg' => array( 'hex' => '#ffffff' ),
			'contentBoxBorderColor' => array( 'hex' => '#e6e6e6' ),
			'titleColor' => array( 'hex' => '#ffffff' ),
			'descriptionColor' => array( 'hex' => '#7c7c7c' ),
			'dateLabelTypography' => array( 'font-family' => 'Inter', 'font-weight' => '600' ),
			'subLabelTypography' => array( 'font-family' => 'Inter', 'font-weight' => '400' ),
			'yearTypography' => array( 'font-size' => '18px', 'font-weight' => '600' ),
			'yearTypography:mobile_landscape' => array( 'font-size' => '16px' ),
			'titleTypography' => array( 'font-family' => 'Inter', 'font-size' => '20px', 'font-weight' => '600', 'line-height' => '30px' ),
			'titleTypography:tablet_portrait' => array( 'font-size' => '18px', 'line-height' => '28px' ),
			'titleTypography:mobile_portrait' => array( 'font-size' => '17px', 'line-height' => '26px' ),
			'descriptionTypography' => array( 'font-family' => 'Inter', 'font-size' => '17px', 'font-weight' => '400', 'line-height' => '33px' ),
			'descriptionTypography:tablet_portrait' => array( 'font-size' => '16px', 'line-height' => '28px' ),
			'descriptionTypography:mobile_portrait' => array( 'font-size' => '15px', 'line-height' => '26px' ),
			'dateLabelSize' => '18px',
			'dateLabelSize:mobile_landscape' => '16px',
			'subLabelSize' => '15px',
			'subLabelSize:mobile_landscape' => '14px',
			'yearSize' => '70px',
			'contentBoxPadding' => array( 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0' ),
			'contentBoxBottomSpacing' => '100px',
			'contentBoxBorderWidth' => '1px',
			'titleBottomSpacing' => '0px',
			'descriptionBottomSpacing' => '0px',
		);
	}

	public static function ctl_empty_story() {
		return array(
			'ctl_story_tab'   => 'content',
			'showYearLabel'   => false,
			'year'            => '2000',
			'chooseMedia'     => 'image',
			'iconType'        => 'icon',
			'icon'            => array(
				'library' => 'themify',
				'icon'    => 'ti-time',
			),
		);
	}

	/**
	 * Demo stories for newly inserted elements (not used for repeater "Add item").
	 *
	 * @return array
	 */
	public static function ctl_default_stories() {
		return array(
			array(
				'showYearLabel' => false,
				'year'          => '1994',
				'dateLabel'     => 'July 5',
				'subLabel'      => 'Introduced',
				'title'         => 'Amazon is born',
				'description'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Erat enim res aperta. Ne discipulum abducam, times.',
				'chooseMedia'   => 'image',
				'image'         => CTL_Bricks_Demo_Images::get_image( 'amazon1' ),
				'iconType'      => 'icon',
				'icon'          => array(
					'library' => 'themify',
					'icon'    => 'ti-time',
				),
			),
			array(
				'showYearLabel' => false,
				'year'          => '2005',
				'dateLabel'     => 'February 2',
				'subLabel'      => 'Expanded',
				'title'         => 'Amazon Prime debuts',
				'description'   => 'Aliter homines, aliter philosophos loqui putas oportere? Sin aliud quid voles, postea. Mihi enim satis est, ipsis non satis.',
				'chooseMedia'   => 'image',
				'image'         => CTL_Bricks_Demo_Images::get_image( 'amazon2' ),
				'iconType'      => 'icon',
				'icon'          => array(
					'library' => 'themify',
					'icon'    => 'ti-time',
				),
			),
			array(
				'showYearLabel' => false,
				'year'          => '2008',
				'dateLabel'     => 'January 31',
				'subLabel'      => 'Expanded',
				'title'         => 'Amazon acquires Audible',
				'description'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
				'chooseMedia'   => 'image',
				'image'         => CTL_Bricks_Demo_Images::get_image( 'amazon3' ),
				'iconType'      => 'icon',
				'icon'          => array(
					'library' => 'themify',
					'icon'    => 'ti-time',
				),
			),
		);
	}

	private static function ctl_require_story_tab( $base_required, $tab ) {
		$tab_condition = ( isset( $tab ) && 'content' === $tab )
			? array( 'ctl_story_tab', '!=', 'style' )
			: array( 'ctl_story_tab', '=', 'style' );

		if ( ! is_array( $base_required ) || empty( $base_required ) ) {
			return $tab_condition;
		}

		$is_single_condition = isset( $base_required[0] ) && ! is_array( $base_required[0] );
		$conditions          = $is_single_condition ? array( $base_required ) : $base_required;
		$conditions[]        = $tab_condition;

		return $conditions;
	}

	/**
	 * Cool Timeline Pro URL used by the Bricks Get Pro tag.
	 *
	 * @return string
	 */
	public static function ctl_get_tracked_url( $base, $campaign, $utm_content ) {
		return add_query_arg(
			array(
				'utm_source'   => 'ctl_plugin',
				'utm_medium'   => 'inside',
				'utm_campaign' => $campaign,
				'utm_content'  => sanitize_key( $utm_content ),
			),
			$base
		);
	}

	public static function ctl_get_pro_url( $utm_content = 'bricks_editor_panel' ) {
		$base = defined( 'CTL_BUY_PRO' ) ? CTL_BUY_PRO : 'https://cooltimeline.com/plugin/cool-timeline-pro/';

		return self::ctl_get_tracked_url( $base, 'get_pro', $utm_content );
	}

	public static function ctl_get_demo_url( $slug, $utm_content ) {
		return self::ctl_get_tracked_url( 'https://cooltimeline.com/bricks/' . sanitize_title( $slug ) . '/', 'demo', $utm_content );
	}

	/**
	 * Plain "Get Pro" sentence used as a control description.
	 *
	 * @param string $after Unlinked trailing copy.
	 * @return string
	 */
	public static function ctl_get_pro_notice_html( $after, $utm_content = 'bricks_editor_content' ) {
		$link = '<a class="ctl-bricks-get-pro-link" href="' . esc_url( self::ctl_get_pro_url( $utm_content ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get Pro', 'cool-timeline' ) . '</a>';

		return $link . ' ' . esc_html( $after );
	}

	/**
	 * Pro controls wrapped by the gold Upgrade to Pro box.
	 * Each group keeps its own Get Pro tracking URL.
	 *
	 * @return array<int,array{keys:string[],url:string}>
	 */
	public static function ctl_pro_lock_groups() {
		return array(
			array(
				'keys' => array(
					'iconText',
					'iconImage',
					'showReadMore',
					'buttonText',
					'buttonLink',
				),
				'url'  => self::ctl_get_pro_url( 'bricks_editor_content_icon' ),
			),
			array(
				'keys' => array(
					'presetVertical',
					'initialAlignment',
					'displayIcons',
					'connectorStyle',
					'mediaOrder',
					'imageOutsideBox',
					'contentInPopup',
					'animation',
					'lightbox',
				),
				'url'  => self::ctl_get_pro_url( 'bricks_editor_content_layout' ),
			),
			array(
				'keys' => array(
					'lineWidth',
					'lineFilling',
					'lineFillingColor',
					'lineBorder',
					'lineBorderStyle',
					'lineBorderWidth',
					'lineBorderColor',
				),
				'url'  => self::ctl_get_pro_url( 'bricks_editor_style' ),
			),
			array(
				'keys' => array(
					'iconBoxSize',
					'iconSize',
					'iconRadius',
					'iconBorderStyle',
					'iconBorderWidth',
					'iconBorderColor',
				),
				'url'  => self::ctl_get_pro_url( 'bricks_editor_style_icon' ),
			),
			array(
				'keys' => array(
					'labelsGap',
					'labelContentTop',
					'labelInside',
					'labelConnectorStyle',
					'labelBgColor',
					'labelBorderStyle',
				),
				'url'  => self::ctl_get_pro_url( 'bricks_editor_style_label' ),
			),
			array(
				'keys' => array(
					'contentBoxBgType',
					'contentBoxBorderStyle',
					'contentBoxBorderRadius',
					'contentBoxShadow',
					'connectorBgColor',
					'connectorBdColor',
				),
				'url'  => self::ctl_get_pro_url( 'bricks_editor_style_border' ),
			),
		);
	}

	/**
	 * Panel config for the builder script.
	 *
	 * @return array
	 */
	public static function ctl_pro_panel_config() {
		return array(
			'upgradeUrl'    => self::ctl_get_pro_url( 'bricks_pro_lock_editor_panel' ),
			'upgradeLabel'  => __( 'Upgrade to Pro', 'cool-timeline' ),
			'proLockGroups' => self::ctl_pro_lock_groups(),
			'proOptionUrls'   => array(
				'iconType'    => self::ctl_get_demo_url( 'vertical-left-right-timeline', 'bricks_editor_content' ),
				'chooseMedia' => self::ctl_get_demo_url( 'right-side-vertical-timeline', 'bricks_editor_content' ),
			),
			'proOptionLabels' => array(
				'Text (PRO only)'      => self::ctl_get_demo_url( 'vertical-left-right-timeline', 'bricks_editor_content' ),
				'Image (PRO only)'     => self::ctl_get_demo_url( 'vertical-left-right-timeline', 'bricks_editor_content' ),
				'Video (PRO only)'     => self::ctl_get_demo_url( 'right-side-vertical-timeline', 'bricks_editor_content' ),
				'Slideshow (PRO only)' => self::ctl_get_demo_url( 'right-side-vertical-timeline', 'bricks_editor_content' ),
			),
		);
	}

	public function set_control_groups() {
		$this->control_groups['stories'] = array(
			'title' => esc_html__( 'Timeline Stories', 'cool-timeline' ),
			'tab'   => 'content',
			'icon'  => 'twpb-stories',
		);

		$this->control_groups['layout'] = array(
			'title' => esc_html__( 'Layout Settings', 'cool-timeline' ),
			'tab'   => 'content',
			'icon'  => 'twpb-layout',
		);

		$this->control_groups['line'] = array(
			'title' => esc_html__( 'Line', 'cool-timeline' ),
			'tab'   => 'style',
			'icon'  => 'twpb-line',
		);

		$this->control_groups['iconBox'] = array(
			'title' => esc_html__( 'Icon Box / Dot', 'cool-timeline' ),
			'tab'   => 'style',
			'icon'  => 'twpb-icon-box',
		);

		$this->control_groups['labels'] = array(
			'title' => esc_html__( 'Labels / Date / Year Box', 'cool-timeline' ),
			'tab'   => 'style',
			'icon'  => 'twpb-labels',
		);

		$this->control_groups['contentBox'] = array(
			'title' => esc_html__( 'Content Background / Border', 'cool-timeline' ),
			'tab'   => 'style',
			'icon'  => 'twpb-content-box',
		);

		$this->control_groups['storyContent'] = array(
			'title' => esc_html__( 'Title / Description', 'cool-timeline' ),
			'tab'   => 'style',
			'icon'  => 'twpb-title',
		);
	}

	public function set_controls() {
		$this->controls['stories'] = array(
			'tab'           => 'content',
			'group'         => 'stories',
			'label'         => esc_html__( 'Stories', 'cool-timeline' ),
			'placeholder'   => esc_html__( 'Story', 'cool-timeline' ),
			'type'          => 'repeater',
			'min'           => 1,
			'titleProperty' => 'title',
			'itemDefault'   => self::ctl_empty_story(),
			'fields'        => array(
				'ctl_story_tab'       => array(
					'label'     => '',
					'type'      => 'toggle',
					'separator' => false,
					'options'   => array(
						'content' => esc_html__( 'CONTENT', 'cool-timeline' ),
						'style'   => esc_html__( 'STYLE', 'cool-timeline' ),
					),
					'default'   => 'content',
				),
				'showYearLabel'       => array(
					'label'    => esc_html__( 'Year / Label (Top)', 'cool-timeline' ),
					'type'     => 'checkbox',
					'rerender' => true,
					'required' => self::ctl_require_story_tab( null, 'content' ),
				),
				'year'                => array(
					'label'       => esc_html__( 'Year / Label Text', 'cool-timeline' ),
					'type'        => 'text',
					'placeholder' => '2000',
					'default'     => '2000',
					'rerender'    => true,
					'required'    => self::ctl_require_story_tab( array( 'showYearLabel', '!=', '' ), 'content' ),
				),
				'dateLabel'           => array(
					'label'       => esc_html__( 'Label / Date', 'cool-timeline' ),
					'type'        => 'text',
					'placeholder' => esc_html__( 'Add Label', 'cool-timeline' ),
					'required'    => self::ctl_require_story_tab( null, 'content' ),
				),
				'subLabel'            => array(
					'label'       => esc_html__( 'Sub Label', 'cool-timeline' ),
					'type'        => 'text',
					'placeholder' => esc_html__( 'Add Sub Label', 'cool-timeline' ),
					'required'    => self::ctl_require_story_tab( null, 'content' ),
				),
				'title'               => array(
					'label'       => esc_html__( 'Title', 'cool-timeline' ),
					'type'        => 'text',
					'placeholder' => esc_html__( 'Add title here', 'cool-timeline' ),
					'required'    => self::ctl_require_story_tab( null, 'content' ),
				),
				'chooseMedia'         => array(
					'label'       => esc_html__( 'Choose Media', 'cool-timeline' ),
					'type'        => 'select',
					'options'     => array(
						'image'   => esc_html__( 'Image', 'cool-timeline' ),
						'video'   => esc_html__( 'Video (PRO only)', 'cool-timeline' ),
						'gallery' => esc_html__( 'Slideshow (PRO only)', 'cool-timeline' ),
					),
					'inline'      => true,
					'default'     => 'image',
					'placeholder' => esc_html__( 'Image', 'cool-timeline' ),
					'description' => self::ctl_get_pro_notice_html( __( 'to unlock more media types.', 'cool-timeline' ) ),
					'required'    => self::ctl_require_story_tab( null, 'content' ),
				),
				'image'               => array(
					'label'           => esc_html__( 'Choose Image', 'cool-timeline' ),
					'type'            => 'image',
					'placeholder'     => esc_html__( 'Add image here', 'cool-timeline' ),
					'hasDynamicData'  => false,
					'dd'              => false,
					'exclude'         => array( 'url', 'externalUrl', 'customUrl' ),
					'required'        => self::ctl_require_story_tab( array( 'chooseMedia', '=', 'image' ), 'content' ),
				),
				'description'         => array(
					'label'       => esc_html__( 'Description', 'cool-timeline' ),
					'type'        => 'editor',
					'placeholder' => esc_html__( 'Add description here', 'cool-timeline' ),
					'required'    => self::ctl_require_story_tab( null, 'content' ),
				),
				'iconType'            => array(
					'label'       => esc_html__( 'Icon Type', 'cool-timeline' ),
					'type'        => 'select',
					'options'     => array(
						'icon'       => esc_html__( 'Icon', 'cool-timeline' ),
						'dot'        => esc_html__( 'Dot', 'cool-timeline' ),
						'customtext' => esc_html__( 'Text (PRO only)', 'cool-timeline' ),
						'image'      => esc_html__( 'Image (PRO only)', 'cool-timeline' ),
					),
					'inline'      => true,
					'default'     => 'icon',
					'required'    => self::ctl_require_story_tab( null, 'content' ),
				),
				'icon'                => array(
					'label'    => esc_html__( 'Icon', 'cool-timeline' ),
					'type'     => 'icon',
					'default'  => array(
						'library' => 'themify',
						'icon'    => 'ti-time',
					),
					'required' => self::ctl_require_story_tab( array( 'iconType', '=', 'icon' ), 'content' ),
				),
				'iconText'            => array(
					'label'    => esc_html__( 'Icon Text', 'cool-timeline' ),
					'type'     => 'text',
					'inline'   => true,
					'rerender' => false,
					'required' => self::ctl_require_story_tab( null, 'content' ),
				),
				'iconImage'           => array(
					'label'    => esc_html__( 'Icon Image', 'cool-timeline' ),
					'type'     => 'text',
					'inline'   => true,
					'rerender' => false,
					'required' => self::ctl_require_story_tab( null, 'content' ),
				),
				'showReadMore'        => array(
					'label'    => esc_html__( 'Read More Button', 'cool-timeline' ),
					'type'     => 'checkbox',
					'rerender' => false,
					'required' => self::ctl_require_story_tab( null, 'content' ),
				),
				'buttonText'          => array(
					'label'       => esc_html__( 'Button Text', 'cool-timeline' ),
					'type'        => 'text',
					'inline'      => true,
					'placeholder' => esc_html__( 'Read More', 'cool-timeline' ),
					'rerender'    => false,
					'required'    => self::ctl_require_story_tab( null, 'content' ),
				),
				'buttonLink'          => array(
					'label'    => esc_html__( 'Button Link', 'cool-timeline' ),
					'type'     => 'text',
					'inline'   => true,
					'rerender' => false,
					'required' => self::ctl_require_story_tab( null, 'content' ),
				),
				'ctl_story_style_info' => array(
					'type'     => 'info',
					'content'  => esc_html__( 'Styles are available in the PRO version only.', 'cool-timeline' ) . ' <a class="ctl-bricks-get-pro-link" href="' . esc_url( self::ctl_get_pro_url( 'bricks_editor_content' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Upgrade to Pro', 'cool-timeline' ) . '</a>',
					'required' => self::ctl_require_story_tab( null, 'style' ),
				),
			),
			'default'       => array( self::ctl_empty_story() ),
		);

		$this->controls['layout'] = array(
			'tab'       => 'content',
			'group'     => 'layout',
			'label'     => esc_html__( 'Layout', 'cool-timeline' ),
			'type'      => 'select',
			'options'   => array(
				'centered'   => esc_html__( 'Vertical (Right / Left)', 'cool-timeline' ),
				'one-sided'  => esc_html__( 'Vertical (Right Only)', 'cool-timeline' ),
				'left-sided' => esc_html__( 'Vertical (Left Only) (PRO only)', 'cool-timeline' ),
			),
			'inline'    => true,
			'default'   => 'centered',
			'rerender'  => true,
		);

		$this->controls['contentAlignment'] = array(
			'tab'      => 'content',
			'group'    => 'layout',
			'label'    => esc_html__( 'Content Alignment (Right)', 'cool-timeline' ),
			'type'     => 'text-align',
			'exclude'  => array( 'justify' ),
			'css'      => array( array( 'property' => '--tw-cbx-text-align' ) ),
			'inline'   => true,
			'default'  => 'left',
			'required' => array( 'layout', '=', 'centered' ),
		);

		$this->controls['contentAlignmentOneSided'] = array(
			'tab'      => 'content',
			'group'    => 'layout',
			'label'    => esc_html__( 'Content Alignment', 'cool-timeline' ),
			'type'     => 'text-align',
			'exclude'  => array( 'justify' ),
			'css'      => array( array( 'property' => '--tw-cbx-text-align-one-sided' ) ),
			'inline'   => true,
			'default'  => 'left',
			'required' => array( 'layout', '=', 'one-sided' ),
		);

		$this->controls['contentAlignmentAlternate'] = array(
			'tab'      => 'content',
			'group'    => 'layout',
			'label'    => esc_html__( 'Content Alignment (Left)', 'cool-timeline' ),
			'type'     => 'text-align',
			'exclude'  => array( 'justify' ),
			'css'      => array( array( 'property' => '--tw-cbx-text-align-alternate' ) ),
			'inline'   => true,
			'default'  => 'left',
			'required' => array( 'layout', '=', 'centered' ),
		);

		$this->controls['iconPositionVertical'] = array(
			'tab'         => 'content',
			'group'       => 'layout',
			'label'       => esc_html__( 'Icon / Labels Position', 'cool-timeline' ),
			'type'        => 'number',
			'description' => esc_html__( 'Each step moves the icon and labels down by 0.5rem.', 'cool-timeline' ),
			'min'         => 0,
			'max'         => 100,
			'step'        => 1,
			'default'     => '4',
			'placeholder' => '4',
			'css'         => array( array( 'property' => '--tw-ibx-position' ) ),
		);

		// Style — Line.
		$this->controls['lineColor'] = array(
			'tab'   => 'style',
			'group' => 'line',
			'label' => esc_html__( 'Line Color', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => '--tw-line-bg' ),
				array( 'property' => '--tw-line-bd-color' ),
				array( 'property' => '--tw-ibx-bd-color' ),
				array( 'property' => '--tw-ybx-bd-color' ),
				array( 'property' => 'background-color', 'selector' => '.ctl-bricks-timeline:before, .ctl-bricks-start, .ctl-bricks-end' ),
				array( 'property' => 'border-color', 'selector' => '.ctl-bricks-icon, .ctl-bricks-icondot-inner, .ctl-bricks-year-text' ),
			),
		);

		$this->controls['iconBgColor'] = array(
			'tab'   => 'style',
			'group' => 'iconBox',
			'label' => esc_html__( 'Icon / Dot Background', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => '--tw-ibx-bg' ),
				array( 'property' => 'background-color', 'selector' => '.ctl-bricks-icon, .ctl-bricks-icondot-inner' ),
			),
		);

		$this->controls['iconColor'] = array(
			'tab'   => 'style',
			'group' => 'iconBox',
			'label' => esc_html__( 'Icon Color', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => '--tw-ibx-color' ),
				array( 'property' => 'color', 'selector' => '.ctl-bricks-icon, .ctl-bricks-icon i, .ctl-bricks-icon svg' ),
			),
		);

		$this->controls['dateLabelSection'] = array(
			'tab'   => 'style',
			'group' => 'labels',
			'type'  => 'separator',
			'label' => esc_html__( 'Date Label', 'cool-timeline' ),
		);

		$this->controls['dateLabelColor'] = array(
			'tab'   => 'style',
			'group' => 'labels',
			'label' => esc_html__( 'Label Color', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => '--tw-lbl-big-color' ),
				array( 'property' => 'color', 'selector' => '.ctl-bricks-label-big' ),
			),
		);

		$this->controls['dateLabelTypography'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Label Typography', 'cool-timeline' ),
			'type'    => 'typography',
			'exclude' => array( 'color', 'text-align', 'font-size', 'font-variation-settings' ),
			'css'     => array( array( 'property' => 'font', 'selector' => '.ctl-bricks-label-big' ) ),
		);

		$this->controls['dateLabelSize'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Label Size', 'cool-timeline' ),
			'type'    => 'number',
			'units'   => true,
			'css'     => array( array( 'property' => '--tw-lbl-big-size' ) ),
			'default' => '18px',
		);

		$this->controls['subLabelSection'] = array(
			'tab'   => 'style',
			'group' => 'labels',
			'type'  => 'separator',
			'label' => esc_html__( 'Sub Label', 'cool-timeline' ),
		);

		$this->controls['subLabelColor'] = array(
			'tab'   => 'style',
			'group' => 'labels',
			'label' => esc_html__( 'Sub Label Color', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => '--tw-lbl-small-color' ),
				array( 'property' => 'color', 'selector' => '.ctl-bricks-label-small' ),
			),
		);

		$this->controls['subLabelTypography'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Sub Label Typography', 'cool-timeline' ),
			'type'    => 'typography',
			'exclude' => array( 'color', 'text-align', 'font-size', 'font-variation-settings' ),
			'css'     => array( array( 'property' => 'font', 'selector' => '.ctl-bricks-label-small' ) ),
		);

		$this->controls['subLabelSize'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Sub Label Size', 'cool-timeline' ),
			'type'    => 'number',
			'units'   => true,
			'css'     => array( array( 'property' => '--tw-lbl-small-size' ) ),
			'default' => '15px',
		);

		$this->controls['yearLabelSection'] = array(
			'tab'   => 'style',
			'group' => 'labels',
			'type'  => 'separator',
			'label' => esc_html__( 'Year / Label (On Line)', 'cool-timeline' ),
		);

		$this->controls['yearLabelColor'] = array(
			'tab'   => 'style',
			'group' => 'labels',
			'label' => esc_html__( 'Year Color', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => '--tw-ybx-text-color' ),
				array( 'property' => 'color', 'selector' => '.ctl-bricks-year-text' ),
			),
		);

		$this->controls['yearLabelBg'] = array(
			'tab'   => 'style',
			'group' => 'labels',
			'label' => esc_html__( 'Year Background', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => '--tw-ybx-bg' ),
				array( 'property' => 'background-color', 'selector' => '.ctl-bricks-year-text' ),
			),
		);

		$this->controls['yearTypography'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Year Typography', 'cool-timeline' ),
			'type'    => 'typography',
			'exclude' => array( 'color', 'text-align', 'font-variation-settings' ),
			'css'     => array( array( 'property' => 'font', 'selector' => '.ctl-bricks-year-text' ) ),
		);

		$this->controls['yearSize'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Year Box Size', 'cool-timeline' ),
			'type'    => 'number',
			'units'   => true,
			'css'     => array( array( 'property' => '--tw-ybx-size' ) ),
			'default' => '70px',
		);

		$this->controls['contentBoxPadding'] = array(
			'tab'   => 'style',
			'group' => 'contentBox',
			'label' => esc_html__( 'Padding', 'cool-timeline' ),
			'type'  => 'spacing',
			'css'   => array( array( 'property' => 'padding', 'selector' => '.ctl-bricks-content' ) ),
			'rerender' => true,
		);

		$this->controls['contentBoxBottomSpacing'] = array(
			'tab'   => 'style',
			'group' => 'contentBox',
			'label' => esc_html__( 'Bottom Spacing', 'cool-timeline' ),
			'type'  => 'number',
			'units' => true,
			'css'   => array(
				array( 'property' => 'margin-bottom', 'selector' => '.ctl-bricks-story' ),
				array( 'property' => '--tw-cbx-bottom-margin' ),
			),
		);

		$this->controls['contentBoxBgSection'] = array(
			'tab'   => 'style',
			'group' => 'contentBox',
			'type'  => 'separator',
			'label' => esc_html__( 'Background', 'cool-timeline' ),
		);

		$this->controls['contentBoxBg'] = array(
			'tab'   => 'style',
			'group' => 'contentBox',
			'label' => esc_html__( 'Background Color', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => 'background-color', 'selector' => '.ctl-bricks-content' ),
				array( 'property' => '--tw-cbx-bg' ),
			),
		);

		$this->controls['contentBoxBorderSection'] = array(
			'tab'   => 'style',
			'group' => 'contentBox',
			'type'  => 'separator',
			'label' => esc_html__( 'Border', 'cool-timeline' ),
		);

		$this->controls['contentBoxBorderColor'] = array(
			'tab'   => 'style',
			'group' => 'contentBox',
			'label' => esc_html__( 'Border Color', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => 'border-color', 'selector' => '.ctl-bricks-content' ),
				array( 'property' => '--tw-cbx-bd-color' ),
				array( 'property' => '--tw-arw-bd-color', 'value' => 'var(--tw-cbx-bd-color)' ),
			),
		);

		$this->controls['contentBoxBorderWidth'] = array(
			'tab'   => 'style',
			'group' => 'contentBox',
			'label' => esc_html__( 'Border Width', 'cool-timeline' ),
			'type'  => 'number',
			'units' => true,
			'css'   => array(
				array( 'property' => '--tw-cbx-bd-top-width' ),
				array( 'property' => '--tw-cbx-bd-right-width' ),
				array( 'property' => '--tw-cbx-bd-bottom-width' ),
				array( 'property' => '--tw-cbx-bd-left-width' ),
			),
		);

		$this->controls['titleSection'] = array(
			'tab'   => 'style',
			'group' => 'storyContent',
			'type'  => 'separator',
			'label' => esc_html__( 'Title', 'cool-timeline' ),
		);

		$this->controls['titleColor'] = array(
			'tab'   => 'style',
			'group' => 'storyContent',
			'label' => esc_html__( 'Title Color', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => 'color', 'selector' => '.ctl-bricks-title' ),
				array( 'property' => '--tw-cbx-title-color' ),
			),
		);

		$this->controls['titleTypography'] = array(
			'tab'     => 'style',
			'group'   => 'storyContent',
			'label'   => esc_html__( 'Title Typography', 'cool-timeline' ),
			'type'    => 'typography',
			'exclude' => array( 'color', 'text-align', 'font-variation-settings' ),
			'css'     => array( array( 'property' => 'font', 'selector' => '.ctl-bricks-title' ) ),
		);

		$this->controls['titleBottomSpacing'] = array(
			'tab'     => 'style',
			'group'   => 'storyContent',
			'label'   => esc_html__( 'Title Bottom Spacing', 'cool-timeline' ),
			'type'    => 'number',
			'units'   => true,
			'default' => '0px',
			'css'     => array(
				array( 'property' => '--tw-cbx-title-margin-bottom' ),
			),
		);

		$this->controls['descriptionSection'] = array(
			'tab'   => 'style',
			'group' => 'storyContent',
			'type'  => 'separator',
			'label' => esc_html__( 'Description', 'cool-timeline' ),
		);

		$this->controls['descriptionColor'] = array(
			'tab'   => 'style',
			'group' => 'storyContent',
			'label' => esc_html__( 'Description Color', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => 'color', 'selector' => '.ctl-bricks-description' ),
				array( 'property' => '--tw-cbx-des-color' ),
			),
		);

		$this->controls['descriptionTypography'] = array(
			'tab'     => 'style',
			'group'   => 'storyContent',
			'label'   => esc_html__( 'Description Typography', 'cool-timeline' ),
			'type'    => 'typography',
			'exclude' => array( 'color', 'text-align', 'font-variation-settings' ),
			'css'     => array( array( 'property' => 'font', 'selector' => '.ctl-bricks-description' ) ),
		);

		$this->controls['descriptionBottomSpacing'] = array(
			'tab'     => 'style',
			'group'   => 'storyContent',
			'label'   => esc_html__( 'Description Bottom Spacing', 'cool-timeline' ),
			'type'    => 'number',
			'units'   => true,
			'default' => '0px',
			'css'     => array(
				array( 'property' => 'margin-bottom', 'selector' => '.ctl-bricks-description' ),
			),
		);

		$this->ctl_register_pro_controls();

		foreach ( self::ctl_editor_defaults() as $key => $value ) {
			if ( isset( $this->controls[ $key ] ) ) {
				$this->controls[ $key ]['default'] = $value;
			}
		}
	}

	/**
	 * Pro options shown as normal text or selects. They do not change the timeline.
	 */
	private function ctl_register_pro_controls() {

		$this->ctl_register_pro_select(
			'presetVertical',
			'content',
			'layout',
			esc_html__( 'Preset Style', 'cool-timeline' ),
			array(
				'v-style-0' => esc_html__( 'Default', 'cool-timeline' ),
				'v-style-1' => esc_html__( 'Classic', 'cool-timeline' ),
				'v-style-2' => esc_html__( 'Elegant', 'cool-timeline' ),
				'v-style-3' => esc_html__( 'Clean', 'cool-timeline' ),
				'v-style-4' => esc_html__( 'Minimal', 'cool-timeline' ),
				'v-style-5' => esc_html__( 'Bold', 'cool-timeline' ),
				'v-style-6' => esc_html__( 'Flat', 'cool-timeline' ),
			),
			'v-style-0'
		);
		$this->ctl_register_pro_select(
			'initialAlignment',
			'content',
			'layout',
			esc_html__( 'Initial Alignment', 'cool-timeline' ),
			array(
				'right' => esc_html__( 'Right', 'cool-timeline' ),
				'left'  => esc_html__( 'Left', 'cool-timeline' ),
			),
			'right'
		);
		$this->ctl_register_pro_select(
			'displayIcons',
			'content',
			'layout',
			esc_html__( 'Display Icons', 'cool-timeline' ),
			array(
				'displayicons' => esc_html__( 'Icons', 'cool-timeline' ),
				'displaydots'  => esc_html__( 'Dots', 'cool-timeline' ),
				'displaynone'  => esc_html__( 'None', 'cool-timeline' ),
			),
			'displayicons'
		);
		$this->ctl_register_pro_select(
			'connectorStyle',
			'content',
			'layout',
			esc_html__( 'Connector Style', 'cool-timeline' ),
			array(
				'arrow' => esc_html__( 'Arrow', 'cool-timeline' ),
				'line'  => esc_html__( 'Line', 'cool-timeline' ),
				'none'  => esc_html__( 'None', 'cool-timeline' ),
			),
			'arrow'
		);
		$this->ctl_register_pro_select(
			'mediaOrder',
			'content',
			'layout',
			esc_html__( 'Media Position', 'cool-timeline' ),
			array(
				'below_title'       => esc_html__( 'Below Title', 'cool-timeline' ),
				'below_description' => esc_html__( 'Below Description', 'cool-timeline' ),
			),
			'below_title'
		);
		$this->ctl_register_pro_checkbox( 'imageOutsideBox', 'content', 'layout', esc_html__( 'Image Outside Box', 'cool-timeline' ) );
		$this->ctl_register_pro_checkbox( 'contentInPopup', 'content', 'layout', esc_html__( 'Content In Popup', 'cool-timeline' ) );
		$this->ctl_register_pro_select(
			'animation',
			'content',
			'layout',
			esc_html__( 'Animation', 'cool-timeline' ),
			array(
				'none'       => esc_html__( 'None', 'cool-timeline' ),
				'fade'       => esc_html__( 'Fade', 'cool-timeline' ),
				'fade-up'    => esc_html__( 'Fade Up', 'cool-timeline' ),
				'fade-down'  => esc_html__( 'Fade Down', 'cool-timeline' ),
				'fade-left'  => esc_html__( 'Fade Left', 'cool-timeline' ),
				'fade-right' => esc_html__( 'Fade Right', 'cool-timeline' ),
				'zoom-in'    => esc_html__( 'Zoom In', 'cool-timeline' ),
				'zoom-out'   => esc_html__( 'Zoom Out', 'cool-timeline' ),
				'flip-up'    => esc_html__( 'Flip Up', 'cool-timeline' ),
				'flip-down'  => esc_html__( 'Flip Down', 'cool-timeline' ),
				'slide-up'   => esc_html__( 'Slide Up', 'cool-timeline' ),
				'slide-down' => esc_html__( 'Slide Down', 'cool-timeline' ),
			),
			'none'
		);
		$this->ctl_register_pro_checkbox( 'lightbox', 'content', 'layout', esc_html__( 'Image Popup', 'cool-timeline' ) );

		$this->ctl_register_pro_text( 'lineWidth', 'style', 'line', esc_html__( 'Line Width', 'cool-timeline' ) );
		$this->ctl_register_pro_checkbox( 'lineFilling', 'style', 'line', esc_html__( 'Line Filling', 'cool-timeline' ) );
		$this->ctl_register_pro_text( 'lineFillingColor', 'style', 'line', esc_html__( 'Line Filling Color', 'cool-timeline' ) );
		$this->ctl_register_pro_checkbox( 'lineBorder', 'style', 'line', esc_html__( 'Line Border', 'cool-timeline' ) );
		$this->ctl_register_pro_select(
			'lineBorderStyle',
			'style',
			'line',
			esc_html__( 'Border Style', 'cool-timeline' ),
			array(
				'none'   => esc_html__( 'None', 'cool-timeline' ),
				'solid'  => esc_html__( 'Solid', 'cool-timeline' ),
				'dashed' => esc_html__( 'Dashed', 'cool-timeline' ),
				'dotted' => esc_html__( 'Dotted', 'cool-timeline' ),
			),
			'none'
		);
		$this->ctl_register_pro_text( 'lineBorderWidth', 'style', 'line', esc_html__( 'Border Width', 'cool-timeline' ) );
		$this->ctl_register_pro_text( 'lineBorderColor', 'style', 'line', esc_html__( 'Border Color', 'cool-timeline' ) );

		$this->ctl_register_pro_text( 'iconBoxSize', 'style', 'iconBox', esc_html__( 'Icon Box Size', 'cool-timeline' ) );
		$this->ctl_register_pro_text( 'iconSize', 'style', 'iconBox', esc_html__( 'Icon Size', 'cool-timeline' ) );
		$this->ctl_register_pro_text( 'iconRadius', 'style', 'iconBox', esc_html__( 'Icon Radius (%)', 'cool-timeline' ) );
		$this->ctl_register_pro_select(
			'iconBorderStyle',
			'style',
			'iconBox',
			esc_html__( 'Border Style', 'cool-timeline' ),
			array(
				'none'   => esc_html__( 'None', 'cool-timeline' ),
				'solid'  => esc_html__( 'Solid', 'cool-timeline' ),
				'dashed' => esc_html__( 'Dashed', 'cool-timeline' ),
				'dotted' => esc_html__( 'Dotted', 'cool-timeline' ),
			),
			'none'
		);
		$this->ctl_register_pro_text( 'iconBorderWidth', 'style', 'iconBox', esc_html__( 'Border Width', 'cool-timeline' ) );
		$this->ctl_register_pro_text( 'iconBorderColor', 'style', 'iconBox', esc_html__( 'Border Color', 'cool-timeline' ) );

		$this->ctl_register_pro_text( 'labelsGap', 'style', 'labels', esc_html__( 'Labels Gap', 'cool-timeline' ) );
		$this->ctl_register_pro_checkbox( 'labelContentTop', 'style', 'labels', esc_html__( 'Label Top Of The Content', 'cool-timeline' ) );
		$this->ctl_register_pro_checkbox( 'labelInside', 'style', 'labels', esc_html__( 'Label Inside The Content', 'cool-timeline' ) );
		$this->ctl_register_pro_select(
			'labelConnectorStyle',
			'style',
			'labels',
			esc_html__( 'Connector Style', 'cool-timeline' ),
			array(
				'arrow' => esc_html__( 'Arrow', 'cool-timeline' ),
				'line'  => esc_html__( 'Line', 'cool-timeline' ),
				'none'  => esc_html__( 'None', 'cool-timeline' ),
			),
			'arrow'
		);
		$this->ctl_register_pro_text( 'labelBgColor', 'style', 'labels', esc_html__( 'Label Background Color', 'cool-timeline' ) );
		$this->ctl_register_pro_select(
			'labelBorderStyle',
			'style',
			'labels',
			esc_html__( 'Label Border Style', 'cool-timeline' ),
			array(
				'solid'  => esc_html__( 'Solid', 'cool-timeline' ),
				'dashed' => esc_html__( 'Dashed', 'cool-timeline' ),
				'dotted' => esc_html__( 'Dotted', 'cool-timeline' ),
				'double' => esc_html__( 'Double', 'cool-timeline' ),
				'none'   => esc_html__( 'None', 'cool-timeline' ),
			),
			'none'
		);

		$this->ctl_register_pro_select(
			'contentBoxBgType',
			'style',
			'contentBox',
			esc_html__( 'Background Type', 'cool-timeline' ),
			array(
				'simple'     => esc_html__( 'Simple', 'cool-timeline' ),
				'gradient'   => esc_html__( 'Gradient', 'cool-timeline' ),
				'multicolor' => esc_html__( 'Multi Color', 'cool-timeline' ),
			),
			'simple'
		);
		$this->ctl_register_pro_select(
			'contentBoxBorderStyle',
			'style',
			'contentBox',
			esc_html__( 'Border Style', 'cool-timeline' ),
			array(
				'none'   => esc_html__( 'None', 'cool-timeline' ),
				'solid'  => esc_html__( 'Solid', 'cool-timeline' ),
				'dashed' => esc_html__( 'Dashed', 'cool-timeline' ),
				'dotted' => esc_html__( 'Dotted', 'cool-timeline' ),
			),
			'solid'
		);
		$this->ctl_register_pro_text( 'contentBoxBorderRadius', 'style', 'contentBox', esc_html__( 'Border Radius', 'cool-timeline' ) );
		$this->ctl_register_pro_text( 'contentBoxShadow', 'style', 'contentBox', esc_html__( 'Box Shadow', 'cool-timeline' ) );
		$this->ctl_register_pro_text( 'connectorBgColor', 'style', 'contentBox', esc_html__( 'Connector Color', 'cool-timeline' ) );
		$this->ctl_register_pro_text( 'connectorBdColor', 'style', 'contentBox', esc_html__( 'Connector Border Color', 'cool-timeline' ) );
	}

	/**
	 * @param string $key     Control key.
	 * @param string $tab     content|style.
	 * @param string $group   Control group.
	 * @param string $label   Label.
	 * @param array  $options Select options.
	 * @param string $default Default value.
	 * @param array  $extra   Extra control args.
	 */
	private function ctl_register_pro_select( $key, $tab, $group, $label, $options, $default, $extra = array() ) {
		$this->controls[ $key ] = array_merge(
			array(
				'tab'      => $tab,
				'group'    => $group,
				'label'    => $label,
				'type'     => 'select',
				'options'  => $options,
				'inline'   => true,
				'default'  => $default,
				'rerender' => false,
			),
			$extra
		);
	}

	/**
	 * @param string $key   Control key.
	 * @param string $tab   content|style.
	 * @param string $group Control group.
	 * @param string $label Label.
	 * @param array  $extra Extra control args.
	 */
	private function ctl_register_pro_checkbox( $key, $tab, $group, $label, $extra = array() ) {
		$this->controls[ $key ] = array_merge(
			array(
				'tab'      => $tab,
				'group'    => $group,
				'label'    => $label,
				'type'     => 'checkbox',
				'default'  => false,
				'rerender' => false,
			),
			$extra
		);
	}

	/**
	 * @param string $key   Control key.
	 * @param string $tab   content|style.
	 * @param string $group Control group.
	 * @param string $label Label.
	 * @param array  $extra Extra control args.
	 */
	private function ctl_register_pro_text( $key, $tab, $group, $label, $extra = array() ) {
		$this->controls[ $key ] = array_merge(
			array(
				'tab'      => $tab,
				'group'    => $group,
				'label'    => $label,
				'type'     => 'text',
				'inline'   => true,
				'rerender' => false,
			),
			$extra
		);
	}

	private function ctl_strip_stale_root_layout_classes() {
		$stale = CTL_Bricks_Loader::stale_root_layout_classes();

		if ( ! isset( $this->attributes['_root']['class'] ) || ! is_array( $this->attributes['_root']['class'] ) || empty( $this->attributes['_root']['class'] ) ) {
			return;
		}

		$cleaned = array();

		foreach ( $this->attributes['_root']['class'] as $item ) {
			if ( ! is_string( $item ) || '' === trim( $item ) ) {
				continue;
			}

			$parts = preg_split( '/\s+/', trim( $item ) );

			if ( ! is_array( $parts ) || empty( $parts ) ) {
				continue;
			}

			$parts = array_values( array_diff( $parts, $stale ) );

			if ( ! empty( $parts ) ) {
				$cleaned[] = implode( ' ', $parts );
			}
		}

		$this->attributes['_root']['class'] = $cleaned;
	}

	public function render() {
		$settings = ( isset( $this->settings ) && is_array( $this->settings ) ) ? $this->settings : array();
		$stories  = ( isset( $settings['stories'] ) && is_array( $settings['stories'] ) ) ? $settings['stories'] : array();
		$layout   = ( isset( $settings['layout'] ) && 'one-sided' === $settings['layout'] ) ? 'one-sided' : 'centered';

		if ( empty( $stories ) && empty( $this->is_frontend ) ) {
			$stories                   = array( self::ctl_empty_story() );
			$this->settings['stories'] = $stories;
		}

		if ( empty( $stories ) && ! empty( $this->is_frontend ) ) {
			return;
		}

		$this->ctl_strip_stale_root_layout_classes();

		require_once CTL_BRICKS_PATH . 'elements/frontend-layouts/ctl-story-loop.php';
		require CTL_BRICKS_PATH . 'elements/frontend-layouts/ctl-vertical-timeline.php';
	}
}

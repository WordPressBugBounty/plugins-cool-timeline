<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTL_Bricks_Element_Story_Timeline extends \Bricks\Element {

	public $category     = 'timeline';
	public $name         = 'timeline-story';
	public $icon         = 'ctl-eicon-timeline-story';
	public $styles       = array( 'ctl-bricks-common', 'ctl-bricks-vertical' );
	public $css_selector = '.ctl-wrapper';

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
			),
			'ctl-bricks-vertical' => array(
				'src'  => CTL_BRICKS_URL . 'assets/css/ctl-bricks-vertical-timeline.css',
				'deps' => array( 'ctl-bricks-common' ),
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
				CTL_V
			);
		}
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
						'icon' => esc_html__( 'Icon', 'cool-timeline' ),
						'dot'  => esc_html__( 'Dot', 'cool-timeline' ),
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
					'required' => self::ctl_require_story_tab( array( 'iconType', '!=', 'dot' ), 'content' ),
				),
				'ctl_story_style_info' => array(
					'type'     => 'info',
					'content'  => esc_html__( 'Styles are available in the PRO version.', 'cool-timeline' ),
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
				'centered'  => esc_html__( 'Vertical (Right / Left)', 'cool-timeline' ),
				'one-sided' => esc_html__( 'Vertical (Right Only)', 'cool-timeline' ),
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
			'min'         => 0,
			'max'         => 100,
			'step'        => 1,
			'default'     => '0',
			'placeholder' => '0',
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
				array( 'property' => 'background-color', 'selector' => '.ctl-timeline:before, .ctl-start, .ctl-end' ),
				array( 'property' => 'border-color', 'selector' => '.ctl-icon, .ctl-icondot-inner, .ctl-year-text' ),
			),
		);

		$this->controls['iconBgColor'] = array(
			'tab'   => 'style',
			'group' => 'iconBox',
			'label' => esc_html__( 'Icon / Dot Background', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => '--tw-ibx-bg' ),
				array( 'property' => 'background-color', 'selector' => '.ctl-icon, .ctl-icondot-inner' ),
			),
		);

		$this->controls['iconColor'] = array(
			'tab'   => 'style',
			'group' => 'iconBox',
			'label' => esc_html__( 'Icon Color', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => '--tw-ibx-color' ),
				array( 'property' => 'color', 'selector' => '.ctl-icon, .ctl-icon i, .ctl-icon svg' ),
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
				array( 'property' => 'color', 'selector' => '.ctl-label-big' ),
			),
		);

		$this->controls['dateLabelTypography'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Label Typography', 'cool-timeline' ),
			'type'    => 'typography',
			'exclude' => array( 'color', 'text-align', 'font-size', 'font-variation-settings' ),
			'css'     => array( array( 'property' => 'font', 'selector' => '.ctl-label-big' ) ),
		);

		$this->controls['dateLabelSize'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Label Size', 'cool-timeline' ),
			'type'    => 'number',
			'units'   => true,
			'css'     => array( array( 'property' => '--tw-lbl-big-size' ) ),
			'default' => '22px',
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
				array( 'property' => 'color', 'selector' => '.ctl-label-small' ),
			),
		);

		$this->controls['subLabelTypography'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Sub Label Typography', 'cool-timeline' ),
			'type'    => 'typography',
			'exclude' => array( 'color', 'text-align', 'font-size', 'font-variation-settings' ),
			'css'     => array( array( 'property' => 'font', 'selector' => '.ctl-label-small' ) ),
		);

		$this->controls['subLabelSize'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Sub Label Size', 'cool-timeline' ),
			'type'    => 'number',
			'units'   => true,
			'css'     => array( array( 'property' => '--tw-lbl-small-size' ) ),
			'default' => '16px',
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
				array( 'property' => 'color', 'selector' => '.ctl-year-text' ),
			),
		);

		$this->controls['yearLabelBg'] = array(
			'tab'   => 'style',
			'group' => 'labels',
			'label' => esc_html__( 'Year Background', 'cool-timeline' ),
			'type'  => 'color',
			'css'   => array(
				array( 'property' => '--tw-ybx-bg' ),
				array( 'property' => 'background-color', 'selector' => '.ctl-year-text' ),
			),
		);

		$this->controls['yearTypography'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Year Typography', 'cool-timeline' ),
			'type'    => 'typography',
			'exclude' => array( 'color', 'text-align', 'font-variation-settings' ),
			'css'     => array( array( 'property' => 'font', 'selector' => '.ctl-year-text' ) ),
		);

		$this->controls['yearSize'] = array(
			'tab'     => 'style',
			'group'   => 'labels',
			'label'   => esc_html__( 'Year Box Size', 'cool-timeline' ),
			'type'    => 'number',
			'units'   => true,
			'css'     => array( array( 'property' => '--tw-ybx-size' ) ),
			'default' => '80px',
		);

		$this->controls['contentBoxPadding'] = array(
			'tab'   => 'style',
			'group' => 'contentBox',
			'label' => esc_html__( 'Padding', 'cool-timeline' ),
			'type'  => 'spacing',
			'css'   => array( array( 'property' => 'padding', 'selector' => '.ctl-content' ) ),
		);

		$this->controls['contentBoxBottomSpacing'] = array(
			'tab'   => 'style',
			'group' => 'contentBox',
			'label' => esc_html__( 'Bottom Spacing', 'cool-timeline' ),
			'type'  => 'number',
			'units' => true,
			'css'   => array(
				array( 'property' => 'margin-bottom', 'selector' => '.ctl-story' ),
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
				array( 'property' => 'background-color', 'selector' => '.ctl-content' ),
				array( 'property' => '--tw-cbx-bg' ),
				array( 'property' => '--tw-arw-bg', 'value' => 'var(--tw-cbx-bg)' ),
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
				array( 'property' => 'border-color', 'selector' => '.ctl-content' ),
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
				array( 'property' => 'color', 'selector' => '.ctl-title' ),
				array( 'property' => '--tw-cbx-title-color' ),
			),
		);

		$this->controls['titleTypography'] = array(
			'tab'     => 'style',
			'group'   => 'storyContent',
			'label'   => esc_html__( 'Title Typography', 'cool-timeline' ),
			'type'    => 'typography',
			'exclude' => array( 'color', 'text-align', 'font-variation-settings' ),
			'css'     => array( array( 'property' => 'font', 'selector' => '.ctl-title' ) ),
		);

		$this->controls['titleBottomSpacing'] = array(
			'tab'     => 'style',
			'group'   => 'storyContent',
			'label'   => esc_html__( 'Title Bottom Spacing', 'cool-timeline' ),
			'type'    => 'number',
			'units'   => true,
			'default' => '10px',
			'css'     => array(
				array( 'property' => 'margin-bottom', 'selector' => '.ctl-title' ),
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
				array( 'property' => 'color', 'selector' => '.ctl-description' ),
				array( 'property' => '--tw-cbx-des-color' ),
			),
		);

		$this->controls['descriptionTypography'] = array(
			'tab'     => 'style',
			'group'   => 'storyContent',
			'label'   => esc_html__( 'Description Typography', 'cool-timeline' ),
			'type'    => 'typography',
			'exclude' => array( 'color', 'text-align', 'font-variation-settings' ),
			'css'     => array( array( 'property' => 'font', 'selector' => '.ctl-description' ) ),
		);

		$this->controls['descriptionBottomSpacing'] = array(
			'tab'     => 'style',
			'group'   => 'storyContent',
			'label'   => esc_html__( 'Description Bottom Spacing', 'cool-timeline' ),
			'type'    => 'number',
			'units'   => true,
			'default' => '15px',
			'css'     => array(
				array( 'property' => 'margin-bottom', 'selector' => '.ctl-description' ),
			),
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

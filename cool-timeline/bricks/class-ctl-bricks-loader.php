<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CTL_BRICKS_PATH', plugin_dir_path( __FILE__ ) );
define( 'CTL_BRICKS_URL', plugin_dir_url( __FILE__ ) );

final class CTL_Bricks_Loader {

	private static $_instance = null;
	private $builder_assets_enqueued = false;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'init', array( $this, 'register_elements' ), 11 );
		add_filter( 'bricks/builder/i18n', array( $this, 'builder_i18n' ) );
		add_filter( 'bricks/element/settings', array( $this, 'ensure_builder_story_item' ), 20, 2 );
		add_filter( 'bricks/element/set_root_attributes', array( $this, 'strip_layout_classes_from_root' ), 20, 2 );
		foreach ( array( 'wp_enqueue_scripts', 'admin_enqueue_scripts' ) as $enqueue_hook ) {
			add_action( $enqueue_hook, array( $this, 'enqueue_builder_panel_assets' ), 30 );
		}
	}

	public function enqueue_builder_panel_assets() {
		if ( ! empty( $this->builder_assets_enqueued ) ) {
			return;
		}

		$is_builder = ( function_exists( 'bricks_is_builder_main' ) && bricks_is_builder_main() )
			|| ( function_exists( 'bricks_is_builder' ) && bricks_is_builder() )
			|| ( function_exists( 'bricks_is_builder_iframe' ) && bricks_is_builder_iframe() );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Bricks iframe may only expose ?bricks=.
		if ( ! $is_builder && ! empty( $_GET['bricks'] ) && is_user_logged_in() ) {
			$is_builder = ( class_exists( '\Bricks\Capabilities' ) && is_callable( array( '\Bricks\Capabilities', 'current_user_can_use_builder' ) ) )
				? (bool) \Bricks\Capabilities::current_user_can_use_builder()
				: current_user_can( 'edit_posts' );
		}

		if ( ! $is_builder ) {
			return;
		}

		$this->builder_assets_enqueued = true;

		wp_enqueue_style(
			'ctl-bricks-builder-panel',
			CTL_BRICKS_URL . 'assets/css/ctl-bricks-builder-panel.css',
			array(),
			CTL_V
		);

		wp_enqueue_script(
			'ctl-bricks-builder-panel',
			CTL_BRICKS_URL . 'assets/js/ctl-bricks-builder-panel.js',
			array(),
			CTL_V,
			true
		);

		wp_localize_script(
			'ctl-bricks-builder-panel',
			'ctlBricksFreeBuilderData',
			array(
				'emptyStory'  => $this->get_empty_story_item(),
				'demoStories' => class_exists( 'CTL_Bricks_Element_Story_Timeline' )
					? CTL_Bricks_Element_Story_Timeline::ctl_default_stories()
					: array(),
				'proPanel'    => class_exists( 'CTL_Bricks_Element_Story_Timeline' )
					? CTL_Bricks_Element_Story_Timeline::ctl_pro_panel_config()
					: array(),
			)
		);
	}

	public static function stale_root_layout_classes() {
		return array(
			'ctl-wrapper',
			'ctl-vertical',
			'ctl-both-sided',
			'ctl-vertical-right',
		);
	}

	public function strip_layout_classes_from_root( $attributes, $element ) {
		if ( ! is_object( $element ) || ! isset( $element->name ) || 'timeline-story' !== $element->name ) {
			return $attributes;
		}

		if ( ! is_array( $attributes ) ) {
			return $attributes;
		}

		if ( ! isset( $attributes['class'] ) || ! is_array( $attributes['class'] ) || empty( $attributes['class'] ) ) {
			return $attributes;
		}

		$stale = self::stale_root_layout_classes();
		$clean = array();

		foreach ( $attributes['class'] as $item ) {
			if ( ! is_string( $item ) || '' === trim( $item ) ) {
				continue;
			}

			$parts = preg_split( '/\s+/', trim( $item ) );

			if ( ! is_array( $parts ) || empty( $parts ) ) {
				continue;
			}

			$parts = array_values( array_diff( $parts, $stale ) );

			if ( ! empty( $parts ) ) {
				$clean[] = implode( ' ', $parts );
			}
		}

		$attributes['class'] = $clean;

		return $attributes;
	}

	private function is_bricks_builder_context() {
		return ( function_exists( 'bricks_is_builder_main' ) && bricks_is_builder_main() )
			|| ( function_exists( 'bricks_is_builder' ) && bricks_is_builder() )
			|| ( function_exists( 'bricks_is_builder_iframe' ) && bricks_is_builder_iframe() )
			|| ( function_exists( 'bricks_is_builder_call' ) && bricks_is_builder_call() );
	}

	private function get_empty_story_item() {
		if ( class_exists( 'CTL_Bricks_Element_Story_Timeline' ) ) {
			return CTL_Bricks_Element_Story_Timeline::ctl_empty_story();
		}

		return array(
			'ctl_story_tab' => 'content',
			'chooseMedia'   => 'image',
			'iconType'      => 'icon',
			'icon'          => array(
				'library' => 'themify',
				'icon'    => 'ti-time',
			),
		);
	}

	public function ensure_builder_story_item( $settings, $element ) {
		if ( ! is_object( $element ) || ! isset( $element->name ) || 'timeline-story' !== $element->name ) {
			return $settings;
		}

		if ( ! $this->is_bricks_builder_context() ) {
			return $settings;
		}

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		if ( ! empty( $settings['_twpbDemoStoriesSeeded'] ) ) {
			return $settings;
		}

		$stories = ( isset( $settings['stories'] ) && is_array( $settings['stories'] ) ) ? $settings['stories'] : array();

		if ( empty( $stories ) ) {
			if ( class_exists( 'CTL_Bricks_Element_Story_Timeline' ) ) {
				$settings['stories']                 = CTL_Bricks_Element_Story_Timeline::ctl_default_stories();
				$settings['_twpbDemoStoriesSeeded'] = true;
			} else {
				$settings['stories'] = array( $this->get_empty_story_item() );
			}
		} elseif ( 1 === count( $stories ) && $this->story_is_blank( $stories[0] ) && class_exists( 'CTL_Bricks_Element_Story_Timeline' ) ) {
			$settings['stories']                 = CTL_Bricks_Element_Story_Timeline::ctl_default_stories();
			$settings['_twpbDemoStoriesSeeded'] = true;
		}

		// Keep the default position as a string so the Bricks number field displays it.
		if ( ! isset( $settings['iconPositionVertical'] ) || '' === $settings['iconPositionVertical'] ) {
			$settings['iconPositionVertical'] = '4';
		}

		return $settings;
	}

	/**
	 * Whether a story item is still the blank template (no user content).
	 *
	 * @param array $story Story settings.
	 * @return bool
	 */
	private function story_is_blank( $story ) {
		if ( ! is_array( $story ) ) {
			return true;
		}

		if ( ! empty( $story['title'] ) || ! empty( $story['description'] ) ) {
			return false;
		}

		if ( ! empty( $story['dateLabel'] ) || ! empty( $story['subLabel'] ) ) {
			return false;
		}

		if ( ! empty( $story['image']['id'] ) || ! empty( $story['image']['url'] ) ) {
			return false;
		}

		return true;
	}

	public function register_elements() {
		if ( ! class_exists( '\Bricks\Elements' ) ) {
			return;
		}

		require_once CTL_BRICKS_PATH . 'class-ctl-bricks-demo-images.php';

		\Bricks\Elements::register_element( CTL_BRICKS_PATH . 'elements/element-timeline-story.php' );
	}

	public function builder_i18n( $i18n ) {
		if ( ! is_array( $i18n ) ) {
			$i18n = array();
		}

		$i18n['timeline'] = esc_html__( 'Timeline', 'cool-timeline' );
		return $i18n;
	}
}

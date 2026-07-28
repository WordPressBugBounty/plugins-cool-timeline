<?php
// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain,WordPress.WP.I18n.TextDomainMismatch,WordPress.NamingConventions.PrefixAllGlobals -- Dynamic text domains and legacy onboarding APIs are intentional for shared compatibility.
/**
 * Cool Timeline (Full edition) onboarding wiring.
 *
 * Everything plugin-specific lives here. The framework code never changes.
 *
 * @package CoolTimeline
 */

use CoolPlugins\Onboarding\Config;
use CoolPlugins\Onboarding\Framework;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the onboarding Config array for Cool Timeline.
 */
final class CTL_Onboarding_Config {

	/**
	 * Plugin text domain.
	 */
	const TEXT_DOMAIN = 'cool-timeline';
	
	/**
	 * Build the full config array passed to CoolPlugins\Onboarding\Config.
	 *
	 * @param string $page       Current admin page slug from $_GET['page'].
	 * @param string $mode       Screen mode from $_GET['mode'].
	 * @param array  $telemetry  Telemetry counters (shortcode_clicks, block_clicks).
	 * @return array
	 */
	public function build( $page, $mode, array $telemetry ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$is_onboarding = ( 'ctl-getting-started' === $page && 'onboarding' === $mode );

		$config     = $this->identity();
		$elementor  = $this->method_elementor( $telemetry, $is_onboarding );
	
		$divi       = $this->method_divi( $telemetry, $is_onboarding );
		// Cross-sell cards in the bottom "addon section". Each addon supports three modes:
		//   1. Free install (default)  → 'type' => 'free' (or omit). Shows Install/Activate.
		//   2. Pro promotion           → 'type' => 'pro' with 'upgrade_url' (+ optional
		//                                 'upgrade_label'). Shows an external upgrade button only.
		//   3. Hidden                  → 'show' => false, or a 'condition' returning false, or
		//                                 simply leave the array empty. The whole section is
		//   
		//                              hidden automatically when nothing resolves.

		$config['addons'] = array_filter(
			array_merge(
				array(
					$elementor['addon'],
					$divi['addon'],
				),
				$is_onboarding ? array() : $this->pro_addons( $is_onboarding )
			)
		);
		

		$config['links']   = array(
			'footer' => $this->footer_cards( 'global', $is_onboarding ),
		);

		$config['methods'] = array(
			'block'     => $this->method_block( $telemetry, $is_onboarding ),
			'shortcode' => $this->method_shortcode( $telemetry, $is_onboarding ),
		);

		// Builder tabs go first, except on the dashboard when the builder is active
		// but its companion plugin is still inactive — then push them last.
		if ( ! $is_onboarding && is_plugin_active( 'elementor/elementor.php' )
			&& ! is_plugin_active( 'timeline-widget-addon-for-elementor/timeline-widget-addon-for-elementor.php' ) ) {
			$config['methods']['elementor-widget'] = $elementor['method'];
		} else {
			$config['methods'] = array( 'elementor-widget' => $elementor['method'] ) + $config['methods'];
		}

		if ( ! $is_onboarding && $this->is_divi_theme_active()
			&& ! is_plugin_active( 'timeline-module-for-divi/timeline-module-for-divi.php' ) ) {
			$config['methods']['divi-module'] = $divi['method'];
		} else {
			$config['methods'] = array( 'divi-module' => $divi['method'] ) + $config['methods'];
		}
		return $config;
	}
	/**
	 * Core plugin identity and page copy.
	 *
	 * @return array
	 */
	private function identity() {
		$td = self::TEXT_DOMAIN;

		return array(
			'slug'            => 'ctl',
			'prefix'          => 'ctl',
			'text_domain'     => $td,
			'version'         => defined( 'CTL_V' ) ? CTL_V : '1.0.0',
			'plugin_dir'      => defined( 'CTL_PLUGIN_DIR' ) ? CTL_PLUGIN_DIR : plugin_dir_path( __FILE__ ),
			'plugin_url'      => defined( 'CTL_PLUGIN_URL' ) ? CTL_PLUGIN_URL : plugin_dir_url( __FILE__ ),
			'parent_slug'     => 'cool-plugins-timeline-addon',
			'edition'         => 'full',
			'tier'            => defined( 'CTL_PRO' ) ? 'pro' : 'free',
			'only_new_user'   => false,
			'new_user_option' => 'ctl_is_new_user',
			'colors'          => array(
				'primary'      => '#2e9e9d',
				'primary_dark' => '#257f7e',
			),
			'page'            => array(
				'menu_title' => __( 'Dashboard', $td ),
				'heading'    => __( 'Create your timeline', $td ),
				'subheading' => __( 'Follow the quick setup guides to create your timeline in minutes.', $td ),
				'chooser'    => __( 'Choose your editor or Timeline builder', $td ),
			),
		);
	}

	/**
	 * Build a single footer card.
	 *
	 * @param string $icon  Emoji or icon character.
	 * @param string $title Card title.
	 * @param string $text  Card body text.
	 * @param array  $links Link rows (label + url).
	 * @return array
	 */
	private function card( $icon, $title, $text, array $links ) {
		return array(
			'icon'  => $icon,
			'title' => $title,
			'text'  => $text,
			'links' => $links,
		);
	}

	/**
	 * Elementor editor method.
	 *
	 * @param array $telemetry     Telemetry counters.
	 * @param bool  $is_onboarding Whether onboarding mode is active.
	 * @return array{method: array, addon: array}
	 */
	private function method_elementor( array $telemetry, $is_onboarding ) {
		$td = self::TEXT_DOMAIN;
	
		$utm_params = '';
		if($is_onboarding === false){
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard';
		}else{
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=onboarding';
		}
		$view_demo_url = 'https://cooltimeline.com/elementor-widget/free-timeline/'.$utm_params;
	
		$method = array(
			'type'          => 'elementor-based',
			'title'         => __( 'Elementor', $td ),
			'badge'         => __( 'Separate Plugin', $td ),
			'content_badge' => __( 'Separate Plugin', $td ),
			'icon'          => '<img src="' . esc_url( CTL_PLUGIN_URL . 'assets/images/elementor-icon.png' ) . '" alt="">',
			'description'   => __( 'Timeline Widget for Elementor', $td ),
			'best_for'      => __( 'Sites built with Elementor', $td ),
			'editions'      => array( 'full', 'liter' ),
			'addon'         => 'timeline-widget-addon-for-elementor',
			'video'         => array(
				'id'       => 'mau6jLJZY1s',
				'title'    => __( 'Create a Timeline in Elementor', 'timeline-widget-addon-for-elementor' ),
				'duration' => __( '2:28', 'timeline-widget-addon-for-elementor' ),
			),
			'redirect_url'  => admin_url( 'edit.php?post_type=page' ),
			'fallback_url'  => admin_url( 'edit.php?post_type=page' ),
			'secondary'     => array(
				'label' => __( 'View Demo', $td ),
				'url'   => $view_demo_url,
			),
			'footer'        => $this->footer_cards( 'elementor', $is_onboarding ),
		);

		$method['condition'] = function () {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			return is_plugin_active( 'elementor/elementor.php' )
				&& ! $this->has_timeline_widget_elementor_pro();
		};
		if ( ! is_plugin_active( 'timeline-widget-addon-for-elementor/timeline-widget-addon-for-elementor.php' ) ) {
			$method['content']   = $this->content_elementor();
		} else {
			$method['badge']         = __( 'Timeline Widget', $td );
			$method['content_badge'] = __( 'Timeline Widget', $td );
			$method['steps']         = array(
				array(
					'title' => __( 'Open the Elementor Editor', $td ),
					'desc'  => __( 'Create a new page or edit an existing one using Elementor.', $td ),
				),
				array(
					'title' => __( 'Add the Timeline Widget', $td ),
					'desc'  => __( 'Search for Story Timeline in the Elementor widget panel and drag it onto your page.', $td ),
				),
				array(
					'title' => __( 'Customize Timeline', $td ),
					'desc'  => __( 'Add your timeline details and choose the layout, adjust colors and styling according to the website.', $td ),
				),
				array(
					'title' => __( 'Publish Timeline', $td ),
					'desc'  => __( 'Save or publish to display your timeline.', $td ),
				),
			);
			// Same rule as Block/Shortcode: hide CTA after first successful click.
			if ( empty( $telemetry['elementor_clicks'] ) ) {
				$method['cta'] = array( 'label' => __( 'Create Sample Timeline', $td ) );
			} else {
				$method['cta'] = array();
			}
		}

		$utm_params = '';
		if($is_onboarding === false){
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard';
		}else{
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=docs&utm_content=onboarding';
		}
		$learn_more_url = 'https://cooltimeline.com/elementor-widget/vertical-timeline-default/'.$utm_params;
		
	
	$addon= array(
			'slug'           => 'timeline-widget-addon-for-elementor',
			'type'           => 'free',
			'group'          => 'elementor-based',
			'install_method' => 'manually',
			'title'          => __( 'Timeline Widget for Elementor', $td ),
			'description'    => __( 'Create beautiful  Horizontal and Vertical timelines directly in Elementor.', $td ),
			'icon'           => CTL_PLUGIN_URL . '/assets/images/timeline-widget-addon-for-elementor.png',
			'setup_url'      => admin_url( 'admin.php?page=ctl-getting-started&method=elementor-widget' ),
			'label_text'     => __( 'Using Elementor? Activate Timeline Widget', $td ),
			'learn_more'     => $learn_more_url,
			'condition'      => function () {
				if ( ! function_exists( 'is_plugin_active' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				return is_plugin_active( 'elementor/elementor.php' )
					&& ! is_plugin_active( 'timeline-widget-addon-for-elementor/timeline-widget-addon-for-elementor.php' );
			},
		);

		return array(
			'method' => $method,
			'addon'  =>	$addon,
		);
	}

	/**
	 * Divi editor method.
	 *
	 * @param array $telemetry     Telemetry counters.
	 * @param bool  $is_onboarding Whether onboarding mode is active.
	 * @return array{method: array, addon: array}
	 */
	private function method_divi( array $telemetry, $is_onboarding ) {
		$td = self::TEXT_DOMAIN;


		$utm_params = '';
		if($is_onboarding === false){
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard';
		}else{
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=onboarding';
		}
		$view_demo_url = 'https://cooltimeline.com/plugin/timeline-module-for-divi/'.$utm_params;
	

		$method = array(
			'type'          => 'divi-module-based',
			'title'         => __( 'Divi', $td ),
			'badge'         => __( 'Separate module', $td ),
			'content_badge' => __( 'Separate module', $td ),
			'icon'          => '<img src="' . esc_url( CTL_PLUGIN_URL . 'assets/images/divi-icon.png' ) . '" alt="">',
			'description'   => __( 'Timeline Module for Divi', $td ),
			'best_for'      => __( 'Sites built with Divi', $td ),
			'editions'      => array( 'full', 'liter' ),
			'addon'         => 'timeline-module-for-divi',
			'video'         => array(
				'id'       => 'V9dEoN0PvFI',
				'thumb'    => 'https://i.ytimg.com/vi_webp/V9dEoN0PvFI/sddefault.webp',
				'title'    => __( 'Create a Timeline in Divi', $td ),
				'duration' => __( '3:43', $td ),
			),
			'secondary'     => array(
				'label' => __( 'View Demo', $td ),
				'url'   => $view_demo_url,
			),
			'footer'        => $this->footer_cards( 'divi', $is_onboarding ),
		);

		$method['condition'] = function () {
			return $this->is_divi_theme_active()
				&& ! $this->has_timeline_module_divi_pro();
		};
		if ( ! is_plugin_active( 'timeline-module-for-divi/timeline-module-for-divi.php' ) ) {
			
			$method['badge']         = __( 'Separate module', $td );
			$method['content_badge'] = __( 'Separate module', $td );
			$method['content']       = $this->content_divi();
		} else {
			$method['badge']         = __( 'Timeline Module', $td );
			$method['content_badge'] = __( 'Timeline Module', $td );
			$method['steps']         = array(
				array(
					'title' => __( 'Add the Timeline Module', 'timeline-module-for-divi' ),
					'desc'  => __( 'Create a new page and edit it with Divi, then drag in the Story Timeline module and pick the layout you want.', 'timeline-module-for-divi' ),
				),
				array(
					'title' => __( 'Add Timeline Stories', 'timeline-module-for-divi' ),
					'desc'  => __( 'Click "Add Item" for each story, then set its date, sub-label, title, description, and a custom image.', 'timeline-module-for-divi' ),
				),
				array(
					'title' => __( 'Configure Timeline Settings', 'timeline-module-for-divi' ),
					'desc'  => __( 'In the Style tab, choose the line color and customize the Label, Year Box, and typography — then save and preview your page.', 'timeline-module-for-divi' ),
				),
			);
			if ( empty( $telemetry['divi_clicks'] ) ) {
				$method['cta'] = array( 'label' => __( 'Create Sample Timeline', $td ) );
			} else {
				$method['cta'] = array();
			}
		}

		if($is_onboarding === false){
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard';
		}else{
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=docs&utm_content=onboarding';
		}
		$learn_more_url = 'https://cooltimeline.com/divi/'.$utm_params;
		
		$addon = array(
			'slug'           => 'timeline-module-for-divi',
			'type'           => 'free',
			'group'          => 'divi-module-based',
			'install_method' => 'manually',
			'title'          => __( 'Timeline Module for Divi', $td ),
			'description'    => __( 'Create beautiful Vertical timelines directly in Divi theme & builder.', $td ),
			'icon'           => CTL_PLUGIN_URL . '/assets/images/divi-timeline-logo.png',
			'setup_url'      => admin_url( 'admin.php?page=ctl-getting-started&method=divi-module' ),
			'label_text'     => __( 'Using Divi? Activate Timeline Module for Divi', $td ),
			'learn_more'     => $learn_more_url,
			'condition'      => function () {
				if ( ! function_exists( 'is_plugin_active' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				return $this->is_divi_theme_active()
					&& ! is_plugin_active( 'timeline-module-for-divi/timeline-module-for-divi.php' );
			},
		);

		return array(
			'method' => $method,
			'addon'  => $addon,
		);
	}

	/**
	 * Block editor method.
	 *
	 * @param array $telemetry Telemetry counters.
	 * @return array
	 */
	private function method_block( array $telemetry, $is_onboarding ) {
		$td = self::TEXT_DOMAIN;

		$utm_params = '';
		if($is_onboarding === false){
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard';
		}else{
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=onboarding';
		}
		$view_demo_url = 'https://cooltimeline.com/demo/gutenberg-timeline-block/'.$utm_params;
	

		$method = array(
			'type'          => 'block-based',
			'title'         => __( 'Block Editor', $td ),
			'badge'         => __( 'Recommended', $td ),
			'content_badge' => __( 'Best for Beginners', $td ),
			'icon'          => '<img src="' . esc_url( CTL_PLUGIN_URL . 'assets/images/gutenberg-icon.png' ) . '" alt="">',
			'description'   => __( 'Create timelines using Timeline Blocks', $td ),
			'best_for'      => __( 'Beginners and block-first sites', $td ),
			'editions'      => array( 'full' ),
			'video'         => array(
				'id'       => 'oOmuBdssPTc',
				'title'    => __( 'Create a Timeline with Block Editor', $td ),
				'duration' => __( '1:28', $td ),
			),
			'steps'         => array(
				array(
					'title' => __( 'Open any page or post', $td ),
					'desc'  => __( 'Go to Pages → Add Page (or Posts → Add Post), or edit an existing page/post where you want to display the timeline', $td ),
				),
				array(
					'title' => __( 'Add Cool Timeline Block', $td ),
					'desc'  => sprintf(
						/* translators: %s: block inserter icon */
						__( 'Click %s and search for "Cool Timeline Block".', $td ),
						'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M11 12.5V17.5H12.5V12.5H17.5V11H12.5V6H11V11H6V12.5H11Z"></path></svg>'
					),
				),
				array(
					'title' => __( 'Add Timeline Stories', $td ),
					'desc'  => __( 'Add title, date, description, media and icons.', $td ),
				),
				array(
					'title' => __( 'Publish Your Timeline', $td ),
					'desc'  => __( 'Save or publish to display your timeline.', $td ),
				),
			),
			'redirect_url'  => add_query_arg(
				array(
					'post_type'        => 'page',
					'action'           => 'filter-ctl-blocks',
					'ctl_insert_nonce' => wp_create_nonce( 'ctl_insert_block' ),
				),
				admin_url( 'post-new.php' )
			),
			'fallback_url'  => add_query_arg(
				array(
					'post_type'        => 'page',
					'action'           => 'filter-ctl-blocks',
					'ctl_insert_nonce' => wp_create_nonce( 'ctl_insert_block' ),
				),
				admin_url( 'post-new.php' )
			),
			'secondary'     => array(
				'label' => __( 'View Demo', $td ),
				'url'   => $view_demo_url,
			),
			'footer'        => $this->footer_cards( 'block', $is_onboarding ),
		);

		if ( empty( $telemetry['block_clicks'] ) ) {
			$method['cta'] = array( 'label' => __( 'Create Sample Timeline', $td ) );
		} else {
			$method['cta'] = array();
		}

		return $method;
	}

	/**
	 * Shortcode method.
	 *
	 * @param array $telemetry Telemetry counters.
	 * @return array
	 */
	private function method_shortcode( array $telemetry, $is_onboarding	 ) {
		$td = self::TEXT_DOMAIN;

		$utm_params = '';
		if($is_onboarding === false){
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard';
		}else{
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=onboarding';
		}
		$view_demo_url = 'https://cooltimeline.com/demo/free-timeline/'.$utm_params;
	
		$method = array(
			'type'          => 'shortcode-based',
			'title'         => __( 'Shortcode', $td ),
			'badge'         => __( 'Work anywhere', $td ),
			'content_badge' => __( 'Advanced Users', $td ),
			'icon'          => '<img src="' . esc_url( CTL_PLUGIN_URL . 'assets/images/shortcode-icon.png' ) . '" alt="">',
			'description'   => __( 'Display timelines anywhere using a shortcode', $td ),
			'best_for'      => __( 'Work anywhere in your website', $td ),
			'editions'      => array( 'full', 'liter' ),
			'video'         => array(
				'id'       => 'oOmuBdssPTc',
				'start'    => 88,
				'title'    => __( 'Create a Timeline with Shortcode', $td ),
				'duration' => __( '1:55', $td ),
			),
			'steps'         => array(
				array(
					'title' => __( 'Add Timeline Stories', $td ),
					'desc'  => __( 'Create stories with titles, descriptions and media.', $td ),
				),
				array(
					'title' => __( 'Insert the Shortcode', $td ),
					'desc'  => __( 'Add the [cool-timeline] shortcode to any page or post using either the Block Editor (Gutenberg) or the Classic Editor.', $td ),
				),
				array(
					'title' => __( 'Customize Timeline', $td ),
					'desc'  => __( 'Select a layout and customize the design, colors, and timeline settings.', $td ),
				),
			),
			'redirect_url'  => admin_url( 'edit.php?post_type=cool_timeline' ),
			'fallback_url'  => admin_url( 'edit.php?post_type=cool_timeline' ),
			'secondary'     => array(
				'label' => __( 'View Demo', $td ),
				'url'   => $view_demo_url,
			),
		);

		if ( empty( $telemetry['shortcode_clicks'] ) ) {
			$method['cta'] = array( 'label' => __( 'Create Sample Timeline', $td ) );
		} else {
			$method['cta'] = array();
		}

		return $method;
	}

	/**
	 * Whether Divi theme context is active.
	 *
	 * @return bool
	 */
	private function is_divi_theme_active() {
		return 'Divi' === wp_get_theme()->name
			|| 'Divi' === wp_get_theme()->get_template()
			|| false !== stripos( (string) wp_get_theme()->parent_theme, 'Divi' )
			|| ( version_compare( (string) wp_get_theme( 'Divi' )->get( 'Version' ), '5', '>=' )
				&& false !== stripos( (string) wp_get_theme()->get( 'Name' ), 'Divi' ) )
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			|| 'Divi' === apply_filters( 'divi_ghoster_ghosted_theme', '' );
	}

	/**
	 * Whether Cool Timeline Pro is installed.
	 *
	 * @return bool
	 */
	private function is_cool_timeline_pro_installed() {
		return '' !== self::pro_plugin_file( 'cool-timeline-pro' );
	}

	/**
	 * Whether Cool Timeline Pro is active.
	 *
	 * @return bool
	 */
	private function has_cool_timeline_pro() {
		if ( defined( 'CTLPV' ) ) {
			return true;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$file = self::pro_plugin_file( 'cool-timeline-pro' );
		return '' !== $file && is_plugin_active( $file );
	}

	/**
	 * Resolve an installed plugin bootstrap file by folder slug.
	 *
	 * @param string $folder Plugin folder slug.
	 * @return string Relative plugin file, or empty string if not installed.
	 */
	public static function pro_plugin_file( $folder ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( get_plugins() as $file => $data ) {
			if ( dirname( $file ) === $folder ) {
				return $file;
			}
		}

		return '';
	}

	/**
	 * Whether Timeline Widget Pro for Elementor is installed.
	 *
	 * @return bool
	 */
	private function is_timeline_widget_elementor_pro_installed() {
		return '' !== self::pro_plugin_file( 'timeline-widget-addon-for-elementor-pro' );
	}

	/**
	 * Whether Timeline Widget Pro for Elementor is active.
	 *
	 * @return bool
	 */
	private function has_timeline_widget_elementor_pro() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$file = self::pro_plugin_file( 'timeline-widget-addon-for-elementor-pro' );
		return '' !== $file && is_plugin_active( $file );
	}

	/**
	 * Whether Timeline Module Pro for Divi is installed.
	 *
	 * @return bool
	 */
	private function is_timeline_module_divi_pro_installed() {
		return '' !== self::pro_plugin_file( 'cp-timeline-module-pro-for-divi' );
	}

	/**
	 * Whether Timeline Module Pro for Divi is active.
	 *
	 * @return bool
	 */
	private function has_timeline_module_divi_pro() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$file = self::pro_plugin_file( 'cp-timeline-module-pro-for-divi' );
		return '' !== $file && is_plugin_active( $file );
	}

	/**
	 * Pro upsell addons for each editor method (included only when relevant).
	 *
	 * @return array<int, array>
	 */
	private function pro_addons( $is_onboarding ) {
		$td     = self::TEXT_DOMAIN;
		$addons = array();

		$utm_params = '';
		$utm_params2 = '';
		if($is_onboarding === false){
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard';
			$utm_params2 = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard';
		}else{
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=onboarding';
			$utm_params2 = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=onboarding';
		}

		if ( $this->is_cool_timeline_pro_installed() ) {
			// Installed Pro: free-type card so the UI can show Activate (not Buy).
			$ctl_pro = array(
				'slug'           => 'cool-timeline-pro',
				'type'           => 'free',
				'install_method' => 'manually',
				'title'          => __( 'Cool Timeline Pro', $td ),
				'description'    => __( 'Unlock Horizontal layouts, advanced settings, and premium designs.', $td ),
				'icon'           => CTL_PLUGIN_URL . '/assets/images/cool-timeline-logo.png',
				'setup_url'      => admin_url( 'admin.php?page=ctl-getting-started' ),
				'label_text'     => __( 'Want more layouts and designs? Activate Cool Timeline Pro', $td ),
				'learn_more'     => 'https://cooltimeline.com/demo/cool-timeline-pro/' . $utm_params,
				'show'           => ! $this->has_cool_timeline_pro(),
			);
			$addons[] = array_merge( $ctl_pro, array( 'group' => 'block-based' ) );
			$addons[] = array_merge( $ctl_pro, array( 'group' => 'shortcode-based' ) );
		} elseif ( ! $this->has_cool_timeline_pro() ) {
			$ctl_pro = array(
				'slug'          => 'cool-timeline-pro',
				'type'          => 'pro',
				'title'         => __( 'Cool Timeline Pro', $td ),
				'description'   => __( 'Unlock Horizontal layouts, advanced settings, and premium designs.', $td ),
				'icon'          => CTL_PLUGIN_URL . '/assets/images/cool-timeline-logo.png',
				'label_text'    => __( 'Want more layouts and designs?', $td ),
				'upgrade_label' => __( 'Buy Cool Timeline Pro', $td ),
				'upgrade_url'   => 'https://cooltimeline.com/plugin/cool-timeline-pro/'.$utm_params2,
				'learn_more'    => 'https://cooltimeline.com/demo/cool-timeline-pro/'.$utm_params,
			);
			$addons[] = array_merge( $ctl_pro, array( 'group' => 'block-based' ) );
			$addons[] = array_merge( $ctl_pro, array( 'group' => 'shortcode-based' ) );
		}

		if ( is_plugin_active( 'elementor/elementor.php' ) ) {
			if ( $this->is_timeline_widget_elementor_pro_installed() ) {
				// Installed Pro: free-type card so the UI can show Activate (not Buy).
				$addons[] = array(
					'slug'           => 'timeline-widget-addon-for-elementor-pro',
					'type'           => 'free',
					'group'          => 'elementor-based',
					'install_method' => 'manually',
					'title'          => __( 'Timeline Widget Pro for Elementor', $td ),
					'description'    => __( 'Create beautiful Horizontal and Vertical timelines directly in Elementor.', $td ),
					'icon'           => CTL_PLUGIN_URL . '/assets/images/timeline-widget-addon-for-elementor.png',
					'setup_url'      => admin_url( 'admin.php?page=twae-getting-started' ),
					'learn_more'     => 'https://cooltimeline.com/elementor-widget/vertical-timeline-default/'.$utm_params,
					'show'           => ! $this->has_timeline_widget_elementor_pro(),
				);
			} else {
				$addons[] = array(
					'slug'          => 'timeline-widget-addon-for-elementor-pro',
					'type'          => 'pro',
					'group'         => 'elementor-based',
					'title'         => __( 'Timeline Widget Pro for Elementor', $td ),
					'description'   => __( 'Unlock horizontal layouts, premium designs, and advanced settings.', $td ),
					'icon'          => CTL_PLUGIN_URL . '/assets/images/timeline-widget-addon-for-elementor.png',
					'label_text'    => __( 'Need advanced layouts and designs?', $td ),
					'upgrade_label' => __( 'Buy Timeline Widget Pro', $td ),
					'upgrade_url'   =>'https://cooltimeline.com/plugin/elementor-timeline-widget-pro/'.$utm_params2,
					'learn_more'    => 'https://cooltimeline.com/elementor-widget/vertical-timeline-default/'.$utm_params,
				);
			}
		}

		if ( $this->is_divi_theme_active() ) {
			if ( $this->is_timeline_module_divi_pro_installed() ) {
				// Installed Pro: free-type card so the UI can show Activate (not Buy).
				$addons[] = array(
					'slug'           => 'cp-timeline-module-pro-for-divi',
					'type'           => 'free',
					'group'          => 'divi-module-based',
					'install_method' => 'manually',
					'title'          => __( 'Timeline Module Pro for Divi', $td ),
					'description'    => __( 'Create beautiful Vertical timelines directly in Divi theme & builder.', $td ),
					'icon'           => CTL_PLUGIN_URL . '/assets/images/divi-timeline-logo.png',
					'setup_url'      => admin_url( 'admin.php?page=tmdivi-getting-started' ),
					'learn_more'     => 'https://cooltimeline.com/divi/'.$utm_params,
					'show'           => ! $this->has_timeline_module_divi_pro(),
				);
			} else {
				$addons[] = array(
					'slug'          => 'cp-timeline-module-pro-for-divi',
					'type'          => 'pro',
					'group'         => 'divi-module-based',
					'title'         => __( 'Timeline Module Pro for Divi', $td ),
					'description'   => __( 'Unlock horizontal layouts, premium designs, and advanced module settings.', $td ),
					'icon'          => CTL_PLUGIN_URL . '/assets/images/divi-timeline-logo.png',
					'label_text'    => __( 'Need advanced layouts and designs?', $td ),
					'upgrade_label' => __( 'Buy Timeline Module Pro', $td ),
					'upgrade_url'   => 'https://cooltimeline.com/plugin/timeline-module-for-divi/'.$utm_params2,
					'learn_more'    => 'https://cooltimeline.com/divi/'.$utm_params,
				);
			}
		}

		return $addons;
	}



	
	/**
	 * Footer card set for a given context.
	 *
	 * @param string $context 'global' | 'block' | 'elementor' | 'divi'.
	 * @param bool $is_onboarding Whether onboarding mode is active.
	 * @return array
	 */
	private function footer_cards( $context, $is_onboarding ) {
		$td = self::TEXT_DOMAIN;

		$utm_params = '';
		if($is_onboarding === false){
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard';
		}else{
			$utm_params = '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=docs&utm_content=onboarding';
		}

		switch ( $context ) {
			case 'elementor':
				return array(
					$this->card(
						'<span class="dashicons dashicons-editor-help"></span>',
						__( 'Support', $td ),
						__( 'Building with Elementor? Our team can help with the timeline widget setup.', $td ),
						array(
							array(
								'label' => __( 'Get Support', $td ),
								'url'   => 'https://coolplugins.net/support/'.$utm_params,
								'class' => 'cpo-button cpo-button-secondary cpo-button-small',
								'target' => '_blank',
								'rel' => 'noopener noreferrer',
							),
						)
					),
					$this->card(
						'<span class="dashicons dashicons-book"></span>',
						__( 'Documentation', $td ),
						__( 'Learn how to design timelines inside Elementor.', $td ),
						array(	array(
									'class' => 'ctl_doc_link',
									'label' => __( 'How to Create Stories', $td ),
									'url'   => 'https://cooltimeline.com/doc/create-story-timeline/'.$utm_params,
								),
								
								array(
									'class' => 'ctl_doc_link',
									'label' => __( 'FAQs', $td ),
									'url'   => 'https://cooltimeline.com/doc/faqs-timeline-widget-for-elementor/'.$utm_params,
								),
								array(
									'class' => 'ctl_doc_link',
									'label' => __( 'View All Documentation', $td ),
									'url'   => 'https://cooltimeline.com/docs/timeline-widget-pro-addon-for-elementor/'.$utm_params,
								),
						)
					),
					$this->card(
						'<span class="dashicons dashicons-star-filled"></span>',
						__( 'Your Feedback Matters', $td ),
						__( 'If you \'re happy with the plugin, we \'d greatly appreciate a quick review. Your support helps us continue improving it', $td ),
						array(
							array(
								'label' => __( 'Leave a Review', $td ),
								'url'   => 'https://wordpress.org/support/plugin/timeline-widget-addon-for-elementor/reviews/#new-post',
								'class' => 'cpo-button cpo-button-secondary cpo-button-small',
							),
						)
					),
				);

			case 'divi':
				return array(
					$this->card(
						'<span class="dashicons dashicons-editor-help"></span>',
						__( 'Support', $td ),
						__( 'Building with Divi? Our team can help with the timeline module setup.', $td ),
						array(
							array(
								'class' => 'cpo-button cpo-button-secondary cpo-button-small',
								'label' => __( 'Get Support', $td ),
								'url'   => 'https://coolplugins.net/support/'.$utm_params,
							),
						)
					),
					$this->card(
						'<span class="dashicons dashicons-book"></span>',
						__( 'Documentation', $td ),
						__( 'Learn how to design timelines inside the Divi Builder.', $td ),
						array(
							array(
								'label' => __( 'How to add Timeline Module', $td ),
								'class' => 'ctl_doc_link',
								'url'   => 'https://cooltimeline.com/doc/add-timeline-module/' . $utm_params,
							),
							array(
								'label' => __( 'FAQs', $td ),
								'class' => 'ctl_doc_link',
								'url'   => 'https://cooltimeline.com/doc/faqs-timeline-module-for-divi/' . $utm_params,
							),
							array(
								'label' => __( 'View All Documentation', $td ),
								'class' => 'ctl_doc_link',
								'url'   => 'https://cooltimeline.com/docs/timeline-module-pro-for-divi/' . $utm_params,
							),
						)
					),
					$this->card(
						'<span class="dashicons dashicons-star-filled"></span>',
						__( 'Your Feedback Matters', $td ),
						__( 'If you \'re happy with the plugin, we \'d greatly appreciate a quick review. Your support helps us continue improving it', $td ),
						array(
							array(
								'label' => __( 'Leave a Review', $td ),
								'url'   => 'https://wordpress.org/support/plugin/timeline-module-for-divi/reviews/#new-post',
								'target' => '_blank',
								'rel' => 'noopener noreferrer',
								'class' => 'cpo-button cpo-button-secondary cpo-button-small',
							),
						)
					),
				);

			case 'block':
				return array(
					$this->card(
						'<span class="dashicons dashicons-editor-help"></span>',
						__( 'Support', $td ),
						__( 'Need help? Our team can assist with setup and troubleshooting.', $td ),
						array(
							array(
								'class' => 'cpo-button cpo-button-secondary cpo-button-small',
								'label' => __( 'Get Support', $td ),
								'url'   => 'https://coolplugins.net/support/'.$utm_params,
							),
						)
					),
					$this->card(
						'<span class="dashicons dashicons-book"></span>',
						__( 'Documentation', $td ),
						__( 'Use the most common setup guides first.', $td ),
						array(
							array(
								'label' => __( 'How to add cool timeline block', $td ),
								'class' => 'ctl_doc_link',
								'url'   => 'https://cooltimeline.com/doc/gutenberg-timeline-block/'.$utm_params,
							),
							array(
								'label' => __( 'FAQ', $td ),
								'class' => 'ctl_doc_link',
								'url'   => 'https://cooltimeline.com/doc/faq/'.$utm_params,
							),
							array(
								'label' => __( 'View All Docs', $td ),
								'class' => 'ctl_doc_link',
								'url'   => 'https://cooltimeline.com/docs/cool-timeline-pro/'.$utm_params,
							),
						)
					),
					$this->card(
						'<span class="dashicons dashicons-star-filled"></span>',
						__( 'Your Feedback Matters', $td ),
						__( 'If you \'re happy with the plugin, we \'d greatly appreciate a quick review. Your support helps us continue improving it', $td ),
						array(
							array(
								'label' => __( 'Leave a Review', $td ),
								'url'   => 'https://wordpress.org/support/plugin/cool-timeline/reviews/#new-post',
								'class' => 'cpo-button cpo-button-secondary cpo-button-small',
							),
						)
					),
				);

			default:
				return array(
					$this->card(
						'<span class="dashicons dashicons-editor-help"></span>',
						__( 'Support', $td ),
						__( 'Need help? Our team can assist with setup and troubleshooting.', $td ),
						array(
							array(
								'class' => 'cpo-button cpo-button-secondary cpo-button-small',
								'label' => __( 'Get Support', $td ),
								'url'   => 'https://coolplugins.net/support/'.$utm_params,
							),
						)
					),
					$this->card(
						'<span class="dashicons dashicons-book"></span>',
						__( 'Documentation', $td ),
						__( 'Use the most common setup guides first.', $td ),
						array(
							array(
								'label' => __( 'How to Add Timeline Stories', $td ),
								'class' => 'ctl_doc_link',
								'url'   => 'https://cooltimeline.com/doc/add-timeline-stories/'.$utm_params,
							),
							array(
								'label' => __( 'FAQ', $td ),
								'class' => 'ctl_doc_link',
								'url'   => 'https://cooltimeline.com/doc/cool-timeline-custom-order-queries/'.$utm_params,
							),
							array(
								'label' => __( 'View All Docs', $td ),
								'class' => 'ctl_doc_link',
								'url'   => 'https://cooltimeline.com/docs/cool-timeline-pro/'.$utm_params,
							),
						)
					),
					$this->card(
						'<span class="dashicons dashicons-star-filled"></span>',
						__( 'Your Feedback Matters', $td ),
						__( 'If you \'re happy with the plugin, we \'d greatly appreciate a quick review. Your support helps us continue improving it', $td ),
						array(
							array(
								'label' => __( 'Leave a Review', $td ),
								'url'   => 'https://wordpress.org/support/plugin/cool-timeline/reviews/#new-post',
								'class' => 'cpo-button cpo-button-secondary cpo-button-small',
							),
						)
					),
				);
		}
	}

	/**
	 * Onboarding-mode Elementor panel HTML.
	 *
	 * @return string
	 */
	private function content_elementor() {
		return '<div class="cpo-content-header">
					<h2>Use Timeline Widget for Elementor</h2>
					<span class="cpo-content-badge">Best for Elementor Users</span>
				</div>
				<div class="cpo-content-wrapper">
				<div class="cpo-guide">
					<p><strong>Build beautiful timelines directly inside Elementor — no coding required.</strong></p>
				</div>
				</div>';
	}

	/**
	 * Onboarding-mode Divi panel HTML.
	 *
	 * @return string
	 */
	private function content_divi() {
		return '<div class="cpo-content-header">
					<h2>Use Timeline Module for Divi</h2>
					<span class="cpo-content-badge">Best for Divi Users</span>
				</div>
				<div class="cpo-content-wrapper">
				<div class="cpo-guide">
					<p><strong>Build beautiful timelines directly inside Divi — no coding required.</strong></p>
				</div>
				</div>';
	}
}

/**
 * Register plugin-specific onboarding hooks.
 *
 * @param Config $config Resolved onboarding config.
 * @return void
 */
function ctl_onboarding_register_hooks( Config $config ) {
	add_filter(
		'ctl_onboarding_script_data',
		static function ( $data ) {
			$data['action'] = 'ctl_onboarding_create_demo';

			if ( isset( $data['install']['labels'] ) ) {
				$data['install']['labels'] = array(
					'installing' => __( 'Installing…', 'timeline-widget-addon-for-elementor' ),
					'activating' => __( 'Activating…', 'timeline-widget-addon-for-elementor' ),
					'activated'  => __( 'Activated', 'timeline-widget-addon-for-elementor' ),
					'setupGuide' => __( 'Check Setup Guide', 'timeline-widget-addon-for-elementor' ),
					'error'      => __( 'Plugin could not be installed. Please try again.', 'timeline-widget-addon-for-elementor' ),
				);
			}

			return $data;
		}
	);

	add_action(
		'wp_ajax_' . $config->ajax_action( 'track' ),
		static function () use ( $config ) {
			check_ajax_referer( $config->option( 'track' ), 'nonce' );

		},
		5
	);

	add_filter(
		'ctl_onboarding_labels',
		static function ( $labels ) {
			$labels['loading']     = __( 'Creating Timeline…', 'cool-timeline' );
			$labels['redirecting'] = __( 'Redirecting…', 'cool-timeline' );
			$labels['error']       = __( 'Something went wrong. Please try again.', 'cool-timeline' );
			return $labels;
		}
	);

	add_action(
		'wp_ajax_ctl_onboarding_create_demo',
		static function () use ( $config ) {
			check_ajax_referer( $config->option( 'prepare' ), 'nonce' );

			if ( ! current_user_can( $config->capability() ) ) {
				wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'cool-timeline' ) ), 403 );
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above.
			$type = isset( $_POST['method_type'] ) ? sanitize_key( wp_unslash( $_POST['method_type'] ) ) : '';

			// Divi shows a builder chooser on clean post-new screens — open a draft with builder off instead.
			if ( 'block-based' === $type && function_exists( 'et_pb_is_pagebuilder_used' ) ) {
				$page_id = wp_insert_post(
					array(
						'post_title'  => __( 'Sample Timeline', 'cool-timeline' ),
						'post_status' => 'draft',
						'post_type'   => 'page',
					),
					true
				);

				if ( is_wp_error( $page_id ) || ! $page_id ) {
					wp_send_json_error( array( 'message' => __( 'Could not create the timeline page.', 'cool-timeline' ) ), 500 );
				}

				update_post_meta( (int) $page_id, '_et_pb_use_builder', 'off' );

				wp_send_json_success(
					array(
						'redirectUrl' => add_query_arg(
							array(
								'post'             => (int) $page_id,
								'action'           => 'edit',
								'ctl_insert_block' => '1',
								'ctl_insert_nonce' => wp_create_nonce( 'ctl_insert_block' ),
							),
							admin_url( 'post.php' )
						),
						'type' => $type,
					)
				);
				return;
			}

			// Elementor: create a draft page with Timeline Widget Free (shared CTL dashboard).
			if ( 'elementor-based' === $type ) {
				if ( ! function_exists( 'is_plugin_active' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				if ( ! is_plugin_active( 'timeline-widget-addon-for-elementor/timeline-widget-addon-for-elementor.php' ) ) {
					wp_send_json_error(
						array( 'message' => __( 'Please install and activate Timeline Widget for Elementor first.', 'cool-timeline' ) ),
						400
					);
				}

				if ( ! function_exists( 'twae_onboarding_create_timeline_page' )
					|| ! function_exists( 'twae_onboarding_elementor_edit_url' )
					|| ! function_exists( 'twae_onboarding_page_has_timeline' ) ) {
					wp_send_json_error(
						array( 'message' => __( 'Timeline Widget demo creator is unavailable. Please update Timeline Widget for Elementor.', 'cool-timeline' ) ),
						500
					);
				}

				$existing = (int) get_option( 'twae_onboarding_demo_page_id' );
				if ( $existing && get_post( $existing ) && 'trash' !== get_post_status( $existing )
					&& twae_onboarding_page_has_timeline( $existing )
					&& ! apply_filters( 'twae_onboarding_force_new_page', false ) ) {
					wp_send_json_success(
						array(
							'redirectUrl' => twae_onboarding_elementor_edit_url( $existing ),
							'type'        => $type,
							'already'     => true,
						)
					);
				}

				$page_id = twae_onboarding_create_timeline_page();
				if ( is_wp_error( $page_id ) || ! $page_id ) {
					wp_send_json_error( array( 'message' => __( 'Could not create the timeline page.', 'cool-timeline' ) ), 500 );
				}

				update_option( 'twae_onboarding_demo_page_id', (int) $page_id, false );
				wp_send_json_success(
					array(
						'redirectUrl' => twae_onboarding_elementor_edit_url( (int) $page_id ),
						'pageId'      => (int) $page_id,
						'type'        => $type,
					)
				);
			}

			// Divi: create a draft page and open Visual Builder (shared CTL dashboard).
			if ( 'divi-module-based' === $type ) {
				if ( ! function_exists( 'is_plugin_active' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				if ( ! is_plugin_active( 'timeline-module-for-divi/timeline-module-for-divi.php' ) ) {
					wp_send_json_error(
						array( 'message' => __( 'Please install and activate Timeline Module for Divi first.', 'cool-timeline' ) ),
						400
					);
				}

				if ( ! function_exists( 'tmdivi_onboarding_create_timeline_page' )
					|| ! function_exists( 'tmdivi_onboarding_divi_edit_url' )
					|| ! function_exists( 'tmdivi_onboarding_page_has_timeline' ) ) {
					wp_send_json_error(
						array( 'message' => __( 'Timeline Module demo creator is unavailable. Please update Timeline Module for Divi.', 'cool-timeline' ) ),
						500
					);
				}

				$existing = (int) get_option( 'tmdivi_onboarding_demo_page_id' );
				if ( $existing && get_post( $existing ) && 'trash' !== get_post_status( $existing )
					&& tmdivi_onboarding_page_has_timeline( $existing )
					&& ! apply_filters( 'tmdivi_onboarding_force_new_page', false ) ) {
					$redirect = tmdivi_onboarding_divi_edit_url( $existing );
					if ( '' === $redirect ) {
						wp_send_json_error( array( 'message' => __( 'Could not open the Divi builder.', 'cool-timeline' ) ), 500 );
					}
					wp_send_json_success(
						array(
							'redirectUrl' => $redirect,
							'type'        => $type,
							'already'     => true,
						)
					);
				}

				$page_id = tmdivi_onboarding_create_timeline_page();
				if ( is_wp_error( $page_id ) ) {
					wp_send_json_error( array( 'message' => $page_id->get_error_message() ), 500 );
				}
				if ( ! $page_id ) {
					wp_send_json_error( array( 'message' => __( 'Could not create the timeline page.', 'cool-timeline' ) ), 500 );
				}

				update_option( 'tmdivi_onboarding_demo_page_id', (int) $page_id, false );

				$redirect = tmdivi_onboarding_divi_edit_url( (int) $page_id );
				if ( '' === $redirect ) {
					wp_send_json_error( array( 'message' => __( 'Could not open the Divi builder.', 'cool-timeline' ) ), 500 );
				}

				wp_send_json_success(
					array(
						'redirectUrl' => $redirect,
						'pageId'      => (int) $page_id,
						'type'        => $type,
					)
				);
			}

			if ( 'shortcode-based' !== $type ) {
				foreach ( $config->methods() as $method ) {
					if ( isset( $method['type'] ) && $method['type'] === $type ) {
						$url = ! empty( $method['redirect_url'] )
							? esc_url_raw( $method['redirect_url'] )
							: ( ! empty( $method['fallback_url'] ) ? esc_url_raw( $method['fallback_url'] ) : '' );

						if ( '' !== $url ) {
							wp_send_json_success( array( 'redirectUrl' => $url, 'type' => $type ) );
						}
					}
				}
				wp_send_json_error( array( 'message' => __( 'No destination configured.', 'cool-timeline' ) ), 400 );
			}

			if ( ! class_exists( 'CTL_Demo_Generator' ) ) {
				require_once CTL_PLUGIN_DIR . 'admin/cp-onboarding/class-ctl-demo-generator.php';
			}

			if ( ! class_exists( 'CTL_Demo_Generator' ) ) {
				wp_send_json_error( array( 'message' => __( 'Demo generator is unavailable.', 'cool-timeline' ) ), 500 );
			}

			$result = ( new CTL_Demo_Generator() )->generate();

			if ( is_wp_error( $result ) ) {
				wp_send_json_error(
					array(
						'message' => $result->get_error_message(),
						'code'    => $result->get_error_code(),
					),
					500
				);
			}

			$redirect = ! empty( $result['preview_url'] ) ? $result['preview_url'] : $result['redirect_url'];

			wp_send_json_success(
				array(
					'redirectUrl' => $redirect,
					'editUrl'     => $result['redirect_url'],
					'pageId'      => (int) $result['page_id'],
					'postIds'     => array_map( 'absint', (array) $result['post_ids'] ),
					'already'     => ! empty( $result['already'] ),
					'type'        => $type,
				)
			);
		}
	);
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin page routing.
$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin page routing.
$mode = isset( $_GET['mode'] ) ? sanitize_key( $_GET['mode'] ) : 'dashboard';

$telemetry_data   = get_option( 'ctl_onboarding_telemetry', array() );
$shortcode_clicks = isset( $telemetry_data['counters']['cta_clicked.shortcode-based'] )
	? $telemetry_data['counters']['cta_clicked.shortcode-based']
	: 0;
$block_clicks     = isset( $telemetry_data['counters']['cta_clicked.block-based'] )
	? $telemetry_data['counters']['cta_clicked.block-based']
	: 0;

$elementor_clicks = isset( $telemetry_data['counters']['cta_clicked.elementor-based'] )
	? $telemetry_data['counters']['cta_clicked.elementor-based']
	: 0;	
$divi_clicks = isset( $telemetry_data['counters']['cta_clicked.divi-module-based'] )
	? $telemetry_data['counters']['cta_clicked.divi-module-based']
	: 0;


$builder      = new CTL_Onboarding_Config();
$config_array = $builder->build(
	$page,
	$mode,
	array(
		'shortcode_clicks' => $shortcode_clicks,
		'block_clicks'     => $block_clicks,
		'elementor_clicks' => $elementor_clicks,
		'divi_clicks'     => $divi_clicks,	
	)
);

$config = new Config( $config_array );

ctl_onboarding_register_hooks( $config );

( new Framework( $config ) )->init();

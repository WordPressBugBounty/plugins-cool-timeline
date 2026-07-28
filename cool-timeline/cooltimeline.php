<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Legacy constants/hooks are retained for backward compatibility.
/*
  Plugin Name: Cool Timeline
  Plugin URI:https://cooltimeline.com
  Description:Showcase your story, company history, events, or roadmap using stunning vertical or horizontal layouts.
  Version:3.4.0
  Author:Cool Plugins
  Author URI:https://coolplugins.net/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=plugins_list
  License:GPLv2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /languages
  Text Domain:cool-timeline
*/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/** Configuration */
if ( ! defined( 'CTL_V' ) ) {
	define( 'CTL_V', '3.4.0' );
}
// define constants for later use
define( 'CTL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CTL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'CTL_DEMO_URL' ) ) {
	define( 'CTL_DEMO_URL', 'https://cooltimeline.com/demo/cool-timeline-pro/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo' );
}
define( 'CTL_FEEDBACK_API', 'https://feedback.coolplugins.net/' );
if ( ! defined( 'CTL_BUY_PRO' ) ) {
	define( 'CTL_BUY_PRO', 'https://cooltimeline.com/plugin/cool-timeline-pro/' );
}
// Lightweight — only registers this copy as a version candidate.
require_once CTL_PLUGIN_DIR . 'admin/cp-onboarding/loader.php';
cpo_onboarding_register( '1.1.5', CTL_PLUGIN_DIR . 'admin/cp-onboarding' );


if ( ! class_exists( 'CoolTimeline' ) ) {
	final class CoolTimeline {


		/**
		 * The unique instance of the plugin.
		 */
		private static $instance;

		/**
		 * Gets an instance of our plugin.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Registers our plugin with WordPress.
		 */
		public static function registers() {
			$thisIns = self::$instance;
			if ( class_exists( 'CoolTimelinePro' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				deactivate_plugins( 'cool-timeline/cooltimeline.php' );
				return;
			}

			self::register_lifecycle_hooks( $thisIns );
			self::register_core_hooks( $thisIns );

			if ( is_admin() ) {
				self::register_admin_hooks( $thisIns );
			}

			self::register_frontend_hooks( $thisIns );
			self::register_block_integration();
		}

		/**
		 * Register activation/deactivation hooks.
		 *
		 * @param CoolTimeline $plugin Plugin instance.
		 */
		private static function register_lifecycle_hooks( $plugin ) {
			register_activation_hook( __FILE__, array( $plugin, 'ctl_activate' ) );
			register_deactivation_hook( __FILE__, array( $plugin, 'ctl_deactivate' ) );
		}

		/**
		 * Register hooks used by both admin and frontend requests.
		 *
		 * @param CoolTimeline $plugin Plugin instance.
		 */
		private static function register_core_hooks( $plugin ) {
			add_action( 'activated_plugin', array( $plugin, 'ctl_plugin_redirection' ) );
			add_action( 'plugins_loaded', array( $plugin, 'ctl_include_files' ) );
			add_action( 'init', array( $plugin, 'ctl_flush_rules' ) );
			add_action( 'init', array( $plugin, 'ctl_maybe_init_plugin_options' ) );
		}

		/**
		 * Register admin-only hooks and dependencies.
		 *
		 * @param CoolTimeline $plugin Plugin instance.
		 */
		private static function register_admin_hooks( $plugin ) {
			$pluginpath = plugin_basename( __FILE__ );

			// Plugin settings links hook.
			add_filter( "plugin_action_links_$pluginpath", array( $plugin, 'ctl_settings_link' ) );
			add_filter( 'plugin_action_links_timeline-widget-addon-for-elementor/timeline-widget-addon-for-elementor.php', array( $plugin, 'ctl_addon_getting_started_link' ), 999 );
			add_filter( 'plugin_action_links_timeline-module-for-divi/timeline-module-for-divi.php', array( $plugin, 'ctl_addon_getting_started_link' ), 999 );
			// Save extra story meta for timeline sorting.
			add_action( 'save_post', array( $plugin, 'ctl_save_story_meta' ), 10, 3 );
			require_once plugin_dir_path( __FILE__ ) . 'admin/marketing/ctl-marketing.php';
			// Register the shared "Timeline Addons" top-level menu before WordPress
			// attaches the post type submenu to it (admin_menu default priority 10).
			add_action( 'admin_menu', array( $plugin, 'ctl_suppress_legacy_timeline_pro_addons_ui' ), 0 );
			// Parent must register before legacy Pro License submenus (priority 2).
			add_action( 'admin_menu', array( $plugin, 'ctl_register_timeline_addons_menu' ), 1 );
			add_action( 'admin_menu', array( $plugin, 'ctl_register_add_new_story_menu' ) );
			add_action( 'admin_menu', array( $plugin, 'ctl_remove_addons_duplicate_submenu' ), 999 );
			// Keep the "Timeline Addons" menu highlighted on the Getting Started page.
			add_filter( 'parent_file', array( $plugin, 'ctl_highlight_addons_menu' ) );
			add_filter( 'submenu_file', array( $plugin, 'ctl_highlight_addons_submenu' ) );
			add_action( 'admin_head', array( $plugin, 'ctl_timeline_addons_menu_style' ) );
			add_action( 'admin_print_scripts', array( $plugin, 'ctl_hide_unrelated_notices' ), 999 );
			add_action( 'enqueue_block_editor_assets', array( $plugin, 'ctl_enqueue_onboarding_script' ) );
		}

		/**
		 * Register frontend hooks.
		 *
		 * @param CoolTimeline $plugin Plugin instance.
		 */
		private static function register_frontend_hooks( $plugin ) {
			add_action( 'wp_print_scripts', array( $plugin, 'ctl_deregister_javascript' ), 100 );
		}

		/**
		 * Load Gutenberg block integration.
		 */
		private static function register_block_integration() {
			require CTL_PLUGIN_DIR . 'includes/shortcode-blocks/ctl-block.php';
		}

		

		/** Constructor */
		public function __construct() {
			 // Setup your plugin object here
			 $this->cpfm_load_file();
			 add_action('csf_cool_timeline_settings_save_after', array($this,'ctl_plugin_settings_saved'));
		}
		public function cpfm_load_file(){
			require_once __DIR__ . '/admin/ctp-getting-started-url.php';
			if(!class_exists('CPFM_Feedback_Notice')){
					require_once __DIR__ . '/admin/cpfm-feedback/cpfm-feedback-notice.php';
				}
			require_once __DIR__ . '/includes/cron/class-cron.php';
		}

		/**
		 * Register the shared "Timeline Addons" top-level admin menu.
		 *
		 * The post type ("Timeline Stories") and the settings page are attached
		 * under this menu as submenus. The menu itself opens the Getting Started page.
		 */
		public function ctl_register_timeline_addons_menu() {
			$hook = add_menu_page(
				__( 'Timeline Addons', 'cool-timeline' ),
				__( 'Timeline Addons', 'cool-timeline' ),
				'manage_options',
				'cool-plugins-timeline-addon',
				'__return_null',
				CTL_PLUGIN_URL . 'assets/images/cool-timeline-icon.svg',
				5
			);

			// Point the menu at the Getting Started onboarding page.
			add_action( 'load-' . $hook, array( $this, 'ctl_redirect_addons_menu_to_getting_started' ) );
		}

		/**
		 * Stop legacy Timeline Widget Pro (<= 2.5.4) / Timeline Block Pro (<= 3.2.1)
		 * / Timeline Module Pro for Divi (<= 2.1.4) from registering Timeline Addons
		 * menu + their global header.
		 *
		 * @return void
		 */
		public function ctl_suppress_legacy_timeline_pro_addons_ui() {
			$legacy = ( defined( 'TWAE_PRO_VERSION' ) && version_compare( (string) TWAE_PRO_VERSION, '2.5.5', '<=' ) )
				|| ( defined( 'CTLBV' ) && version_compare( (string) CTLBV, '3.2.1', '<=' ) )
				|| ( defined( 'TM_DIVI_PRO_V' ) && version_compare( (string) TM_DIVI_PRO_V, '2.1.4', '<=' ) );

			if ( ! $legacy || ! class_exists( 'cool_plugins_timeline_addons' ) ) {
				return;
			}

			$page = cool_plugins_timeline_addons::init();
			remove_action( 'admin_menu', array( $page, 'init_plugins_dasboard_page' ), 1 );
			remove_action( 'admin_notices', array( $page, 'maybe_render_global_header' ), 1 );
		}

		/**
		 * Remove the auto-generated duplicate submenu and keep Dashboard first
		 * so the Timeline Addons top-level click opens Getting Started (not License).
		 *
		 * @return void
		 */
		public function ctl_remove_addons_duplicate_submenu() {
			remove_submenu_page( 'cool-plugins-timeline-addon', 'cool-plugins-timeline-addon' );

			global $submenu;
			if ( empty( $submenu['cool-plugins-timeline-addon'] ) || ! is_array( $submenu['cool-plugins-timeline-addon'] ) ) {
				return;
			}

			$dashboard = null;
			$rest      = array();
			foreach ( $submenu['cool-plugins-timeline-addon'] as $item ) {
				if ( isset( $item[2] ) && 'ctl-getting-started' === $item[2] ) {
					$dashboard = $item;
				} else {
					$rest[] = $item;
				}
			}

			if ( null !== $dashboard ) {
				$submenu['cool-plugins-timeline-addon'] = array_merge( array( $dashboard ), $rest );
			}
		}
		
		public function ctl_register_add_new_story_menu() {

			add_submenu_page(
				'cool-plugins-timeline-addon',
				'Add New Story',
				'Add New Story',
				'manage_options',
				'post-new.php?post_type=cool_timeline'
			);
		}

		/**
		 * Redirect the "Timeline Addons" menu landing page to Getting Started.
		 */
		public function ctl_redirect_addons_menu_to_getting_started() {
			wp_safe_redirect( ctp_timeline_getting_started_url( 'ctl' ) );
			exit;
		}

		/**
		 * Sizes the "Timeline Addons" top-level menu icon in the admin sidebar.
		 */
		public function ctl_timeline_addons_menu_style() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen detection.
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			// On Timeline Stories / Settings screens the menu becomes the current one,
			// so target the active (current-submenu) state as well.
			$is_timeline_page = (
				false !== strpos( $request_uri, 'post_type=cool_timeline' )
				|| false !== strpos( $request_uri, 'page=cool_timeline_settings' )
			);

			echo '<style>li#toplevel_page_cool-plugins-timeline-addon img {
				width: 60%;
			}';

			echo '</style>';
		}

		/**
		 * Force the "Timeline Addons" top-level menu to appear active on the
		 * Getting Started onboarding page (which is registered as an orphan submenu).
		 *
		 * @param string $parent_file Current parent menu slug.
		 * @return string
		 */
		public function ctl_highlight_addons_menu( $parent_file ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen detection.
			$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

			if ( in_array( $page, array( 'ctl-getting-started', 'timeline-addons-license' ), true ) ) {
				return 'cool-plugins-timeline-addon';
			}

			return $parent_file;
		}

		/**
		 * Highlight the "Getting Started" submenu item under "Timeline Addons".
		 *
		 * @param string|null $submenu_file Current submenu slug.
		 * @return string|null
		 */
		public function ctl_highlight_addons_submenu( $submenu_file ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen detection.
			$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

			if ( 'ctl-getting-started' === $page ) {
				return 'ctl-getting-started';
			}

			if ( 'timeline-addons-license' === $page ) {
				return 'timeline-addons-license';
			}

			return $submenu_file;
		}

		public function ctl_plugin_settings_saved(){
			
			if ( $this->ctl_is_tracking_enabled() ) {
				$this->ctl_maybe_schedule_tracking_cron();
			} else {
				if ( wp_next_scheduled( 'ctl_extra_data_update' ) ) {
					wp_clear_scheduled_hook('ctl_extra_data_update');
				}
				
			}
		}

		/**
		 * Check whether usage tracking is enabled in plugin settings.
		 */
		private function ctl_is_tracking_enabled() {
			$data = get_option( 'cool_timeline_settings' );

			return ! empty( $data['ctl_cpfm_feedback_data'] );
		}

		/**
		 * Schedule the usage tracking cron when it is not already scheduled.
		 */
		private function ctl_maybe_schedule_tracking_cron() {
			if ( ! wp_next_scheduled( 'ctl_extra_data_update' ) ) {
				wp_schedule_event( time(), 'every_30_days', 'ctl_extra_data_update' );
			}
		}

		/**
		 * On timeline addon pages, hide unrelated admin notices by pruning the core notice hooks.
		 *
		 * Desired behavior:
		 * - On ALL admin pages: our own plugin notices behave normally.
		 * - Only on Timeline Addons pages: third‑party notices are removed, but our notices remain.
		 *
		 * This follows the same core idea as the Events plugin's ect_hide_unrelated_notices()
		 * but keeps Cool Timeline notices (by class/function name) instead of routing through a
		 * separate dispatcher hook.
		 */
		public function ctl_hide_unrelated_notices() {
			// Always register dispatcher once, on all admin pages (Events-style).
			if ( ! defined( 'CTL_ADMIN_NOTICE_HOOKED' ) ) {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
				define( 'CTL_ADMIN_NOTICE_HOOKED', true );
				add_action(
					'admin_notices',
					array( $this, 'ctl_dash_admin_notices' ),
					PHP_INT_MAX
				);
			}

			// If this is not a Timeline Addons page, don't prune anything.
			if ( ! function_exists( 'ctl_is_timeline_addon_page' ) || ! ctl_is_timeline_addon_page() ) {
				return;
			}

			global $wp_filter;

			$rules = array(
				'user_admin_notices'    => array(), // remove all non‑Cool Plugins callbacks.
				'admin_notices'         => array(),
				'all_admin_notices'     => array(),
				'network_admin_notices' => array(),
				'admin_footer'          => array(
					'render_delayed_admin_notices', // remove this particular callback (e.g. Elementor delayed notices).
				),
			);

			foreach ( array_keys( $rules ) as $notice_type ) {
				if ( empty( $wp_filter[ $notice_type ] ) || empty( $wp_filter[ $notice_type ]->callbacks ) || ! is_array( $wp_filter[ $notice_type ]->callbacks ) ) {
					continue;
				}

				$remove_all = empty( $rules[ $notice_type ] );

				foreach ( $wp_filter[ $notice_type ]->callbacks as $priority => $hooks ) {
					foreach ( $hooks as $name => $arr ) {
						if ( ! isset( $arr['function'] ) ) {
							continue;
						}
						$fn = $arr['function'];

						// When remove_all is true, drop everything EXCEPT Cool Plugins/TWAe callbacks.
						if ( $remove_all ) {
							$keep  = false;
							$class = '';

							if ( is_array( $fn ) && ! empty( $fn[0] ) && is_object( $fn[0] ) ) {
								$class = strtolower( get_class( $fn[0] ) );
							} elseif ( is_object( $fn ) ) {
								$class = strtolower( get_class( $fn ) );
							}

							if ( $class ) {
								$keep = (
									false !== strpos( $class, 'cooltimeline' ) ||
									false !== strpos( $class, 'cool_plugins' ) ||
									false !== strpos( $class, 'ctl_admin' ) ||
									false !== strpos( $class, 'ctp_' ) ||
									false !== strpos( $class, 'license_helper' ) ||
									false !== strpos( $class, 'twae' ) ||
									false !== strpos( $class, 'tmdivi' )
								);
							}

							// Also keep callbacks whose function name clearly belongs to Cool Plugins stack.
							if ( ! $keep && is_string( $fn ) ) {
								$keep = ( 0 === strpos( $fn, 'ctl_' ) || 0 === strpos( $fn, 'cool_' ) || 0 === strpos( $fn, 'twae_' ) || 0 === strpos( $fn, 'tmdivi_' ) );
							}

							if ( ! $keep ) {
								unset( $wp_filter[ $notice_type ]->callbacks[ $priority ][ $name ] );
							}
							continue;
						}

						// When rules[notice_type] is non‑empty (e.g. admin_footer), remove only specific callbacks.
						$cb = is_array( $fn ) ? $fn[1] : $fn;
						if ( in_array( $cb, $rules[ $notice_type ], true ) ) {
							unset( $wp_filter[ $notice_type ]->callbacks[ $priority ][ $name ] );
						}
					}
				}
			}
		}

		/**
		 * Dispatcher for admin notices (fired once at PHP_INT_MAX on admin_notices).
		 * Ensures CTL notices can be rendered after pruning on timeline addon pages.
		 */
		// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		public function ctl_dash_admin_notices() {
			if ( defined( 'CTL_ADMIN_NOTICE_RENDERED' ) ) {
				return;
			}

			define( 'CTL_ADMIN_NOTICE_RENDERED', true );

			do_action( 'ctl_display_admin_notices' );
		}
		// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

		/*
		  Including required files
		*/
		public function ctl_include_files() {
			$this->include_core_files();
			$this->include_shortcode_files();
			$this->include_vc_addon_files();

			if ( is_admin() ) {
				$this->include_admin_files();
			}

			$this->include_block_files();
			$this->include_shortcode_generator_files();
			$this->register_feedback_hooks();
		}

		/**
		 * Include core plugin classes.
		 */
		private function include_core_files() {
			require CTL_PLUGIN_DIR . 'admin/class.cool-timeline-posttype.php';
			require CTL_PLUGIN_DIR . 'includes/class-stories-migration.php';
			require_once CTL_PLUGIN_DIR . 'admin/class-migration.php';
			include_once CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-helpers.php';
		}

		/**
		 * Include and initialize shortcode classes.
		 */
		private function include_shortcode_files() {
			require CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-settings.php';
			$settings_obj = new CTL_Settings();

			require CTL_PLUGIN_DIR . 'includes/shortcodes/class-ctl-shortcode.php';
			new CTL_Shortcode( $settings_obj );
		}

		/**
		 * Include and initialize Visual Composer addon support.
		 */
		private function include_vc_addon_files() {
			require CTL_PLUGIN_DIR . '/includes/class-cool-vc-addon.php';
		}

		/**
		 * Include admin-only files.
		 */
		private function include_admin_files() {
			require_once CTL_PLUGIN_DIR . 'admin/cpfm-feedback/users-feedback.php';
			require_once CTL_PLUGIN_DIR . 'admin/codestar-framework/codestar-framework.php';

			/*** Plugin review notice file */
			require_once CTL_PLUGIN_DIR . '/admin/notices/admin-notices.php';

			// Including onboarding config file for timeline (after framework loads).
			add_action(
				'cpo_onboarding_loaded',
				function () {
					require CTL_PLUGIN_DIR . '/admin/cp-onboarding/onboarding-config.php';
				}
			);

			require_once CTL_PLUGIN_DIR . 'admin/ctl-timeline-header.php';
		}

		/**
		 * Include block editor integration files.
		 */
		private function include_block_files() {
			require CTL_PLUGIN_DIR . 'includes/cool-timeline-block/src/init.php';
		}

		/**
		 * Include shortcode generator files.
		 */
		private function include_shortcode_generator_files() {
			require_once CTL_PLUGIN_DIR . 'admin/ctl-shortcode-generator.php';
		}

		/**
		 * Register feedback hooks after feedback files are available.
		 */
		private function register_feedback_hooks() {
			add_action('cpfm_register_notice', function () {
            
				if (!class_exists('CPFM_Feedback_Notice') || !current_user_can('manage_options')) {
					return;
				}
	
		$notice = [
	
			// phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
			'title' => __('Timeline Plugins by Cool Plugins', 'ctl'),
			// phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
			'message' => __('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', 'cool-plugins-feedback'),
			'pages' => ['cool_timeline_settings', 'cool-plugins-timeline-addon', 'ctl-getting-started'],
			'always_show_on' => ['cool_timeline_settings', 'cool-plugins-timeline-addon', 'ctl-getting-started'],
			'plugin_name'=>'ctl'
		];
	
			
				CPFM_Feedback_Notice::cpfm_register_notice('cool-timeline', $notice);
	
					if (!isset($GLOBALS['cool_plugins_feedback'])) {
						$GLOBALS['cool_plugins_feedback'] = [];
					}
					
				
					$GLOBALS['cool_plugins_feedback']['cool-timeline'][] = $notice;
		   
			});
			add_action('cpfm_after_opt_in_ctl', function($category){
			

				if ($category === 'cool-timeline') {
					$data = get_option('cool_timeline_settings'); 
					$data['ctl_cpfm_feedback_data'] = true;
			update_option('cool_timeline_settings', $data);
		
					if(class_exists('CTL_CRONJOB')){
						CTL_CRONJOB::ctl_send_data();
					}
				}
			});
		}

		function ctl_enqueue_onboarding_script() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified below.
			$legacy = isset( $_GET['action'] ) && 'filter-ctl-blocks' === $_GET['action'];
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified below.
			$insert = isset( $_GET['ctl_insert_block'] ) && '1' === $_GET['ctl_insert_block'];

			if ( ! $legacy && ! $insert ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified below.
			$nonce = isset( $_GET['ctl_insert_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['ctl_insert_nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'ctl_insert_block' ) ) {
				return;
			}

			// Only load on the post/page edit screen.
			$screen = get_current_screen();
			if ( ! $screen || ! $screen->is_block_editor() ) {
				return;
			}

			wp_enqueue_script(
				'ctl-block-inserter',
				plugin_dir_url( __FILE__ ) . 'admin/cp-onboarding/assets/inserter.js',
				array( 'wp-dom-ready', 'wp-blocks', 'wp-data', 'wp-editor', 'wp-block-editor' ),
				'1.0.0',
				true
			);
		}

		// flush rewrite rules after activation
		public function ctl_flush_rules() {
			if ( get_option( 'ctl_flush_rewrite_rules_flag' ) ) {
				flush_rewrite_rules();
				delete_option( 'ctl_flush_rewrite_rules_flag' );
			}
		}

		// Initialize plugin options and admin settings files.
		public function ctl_maybe_init_plugin_options() {

			$this->ctl_init_install_options();

			if ( is_admin() ) {
				
				require_once CTL_PLUGIN_DIR . 'admin/ctl-admin-settings.php';
				require CTL_PLUGIN_DIR . 'admin/ctl-meta-fields.php';

				
			}
		}

		/**
		 * Initialize install/version options when they do not already exist.
		 */
		private function ctl_init_install_options() {
			if ( ! get_option( 'ctl_initial_save_version' ) ) {
				add_option( 'ctl_initial_save_version', CTL_V );
			}

			if ( ! get_option( 'ctl-install-date' ) ) {
				add_option( 'ctl-install-date', gmdate( 'Y-m-d h:i:s' ) );
			}
		}

		public function ctl_plugin_redirection( $plugin ) {
			if ( plugin_basename( __FILE__ ) !== $plugin ) {
				return;
			}

			// Only redirect on a fresh first-time activation. The transient
			// is set in ctl_activate() and absent for upgrades/reactivations.
			if ( ! get_transient( 'ctl_activation_redirect' ) ) {
				return;
			}
			delete_transient( 'ctl_activation_redirect' );

			// Skip the redirect during bulk plugin activations so we don't
			// hijack a multi-plugin activate request.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only check, no state change.
			if ( isset( $_GET['activate-multi'] ) ) {
				return;
			}

			wp_safe_redirect( ctp_timeline_getting_started_url( 'ctl' ) );
			exit;
		}

		/**
		 * Replace addon Getting Started links with the shared dashboard URL + method tab.
		 *
		 * @param array $links Plugin row action links.
		 * @return array
		 */
		public function ctl_addon_getting_started_link( $links ) {
			if ( ! is_array( $links ) ) {
				return $links;
			}

			$plugin_keys = array(
				'plugin_action_links_timeline-widget-addon-for-elementor/timeline-widget-addon-for-elementor.php' => 'twae',
				'plugin_action_links_timeline-module-for-divi/timeline-module-for-divi.php'                    => 'tmdivi',
			);

			$plugin_key = isset( $plugin_keys[ current_filter() ] ) ? $plugin_keys[ current_filter() ] : '';
			if ( '' === $plugin_key ) {
				return $links;
			}

			$links = array_values(
				array_filter(
					$links,
					static function ( $link ) {
						return false === stripos( $link, 'Getting Started' )
							&& false === stripos( $link, 'ctl-getting-started' )
							&& false === stripos( $link, 'twae-getting-started' );
					}
				)
			);

			$links[] = '<a href="' . esc_url( ctp_timeline_getting_started_url( $plugin_key ) ) . '">' . esc_html__( 'Getting Started', 'cool-timeline' ) . '</a>';

			return $links;
		}

		// Add the settings link to the plugins page.
		public function ctl_settings_link( $links ) {
			array_unshift( $links, '<a href="admin.php?page=cool_timeline_settings">Settings</a>' );
			$links[] = '<a href="' . esc_url( ctp_timeline_getting_started_url( 'ctl' ) ) . '">' . esc_html__( 'Getting Started', 'cool-timeline' ) . '</a>';
			$links[] = '<a style="font-weight:bold; color:#852636;" href="https://cooltimeline.com/plugin/cool-timeline-pro/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=plugins_list" target="_blank">Get Pro</a>';

			return $links;
		}

		/**
		 * Save post metadata when a story is saved.
		 *
		 * @param int  $post_id The post ID.
		 * @param post $post The post object.
		 * @param bool $update Whether this is an existing post being updated or not.
		 */
		public function ctl_save_story_meta( $post_id, $post, $update ) {
			// Bail on autosaves/revisions.
			if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
				return;
			}

			// If this isn't a 'cool_timeline' post, don't update it.
			$post_type = get_post_type( $post_id );
			if ( 'cool_timeline' !== $post_type ) {
				return;
			}

			// Permission check.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			$story_date = '';

			/**
			 * Prefer using posted metabox value when a valid WP core post nonce exists.
			 * This avoids relying on a custom nonce that may not be present in all editors/requests.
			 */
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['_wpnonce'] ) ) {
				$wp_nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
				if ( wp_verify_nonce( $wp_nonce, 'update-post_' . $post_id ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing
					if ( isset( $_POST['ctl_post_meta']['story_type']['ctl_story_date'] ) ) {
						// phpcs:ignore WordPress.Security.NonceVerification.Missing
						$story_date = sanitize_text_field( wp_unslash( $_POST['ctl_post_meta']['story_type']['ctl_story_date'] ) );
					}
				}
			}

			// Fallback: read already-saved metabox meta (works for REST/Gutenberg flows too).
			if ( empty( $story_date ) ) {
				$ctl_post_meta = get_post_meta( $post_id, 'ctl_post_meta', true );
				if ( is_array( $ctl_post_meta ) && isset( $ctl_post_meta['story_type']['ctl_story_date'] ) ) {
					$story_date = sanitize_text_field( $ctl_post_meta['story_type']['ctl_story_date'] );
				}
			}

			// Fallback: read legacy/meta fields if present.
			if ( empty( $story_date ) ) {
				$story_date = sanitize_text_field( (string) get_post_meta( $post_id, 'ctl_story_date', true ) );
			}

			if ( ! empty( $story_date ) ) {
				$story_timestamp = CTL_Helpers::ctlfree_generate_custom_timestamp( $story_date );
				update_post_meta( $post_id, 'ctl_story_timestamp', $story_timestamp );
				update_post_meta( $post_id, 'story_based_on', 'default' );
				update_post_meta( $post_id, 'ctl_story_date', $story_date );
			}

		}



		/*
		* Fixed Bridge theme confliction
		*/
		public function ctl_deregister_javascript() {
			if ( is_admin() ) {
				global $post;

				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$current_page  = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
				$allowed_pages = array(
					'cool_timeline_settings',
					'cool-plugins-timeline-addon',
					'timeline-addons-license',
				);
				
				if ( !empty( $current_page ) && in_array( $current_page, $allowed_pages, true ) && function_exists( 'wp_deregister_script' ) ) {
					wp_deregister_script( 'default' );
				}

				if ( isset( $post ) && isset( $post->post_type ) && $post->post_type === 'cool_timeline' ) {
					wp_deregister_script( 'acf-timepicker' );
					wp_deregister_script( 'jquery-ui-timepicker-js' );
					wp_deregister_script( 'thrive-admin-datetime-picker' ); // datepicker conflict with Rise theme
					wp_deregister_script( 'et_bfb_admin_date_addon_js' ); // datepicker conflict with Divi theme
					wp_deregister_script( 'zeen-engine-admin-vendors-js' ); // datepicker conflict with zeen engine plugin
				}
			}
		}

		/* Activating plugin and adding some info */
		public function ctl_activate() {

			/**
			 * Detect if this is a new user.

			 * This prevents the redirect from firing every time an existing user
			 * deactivates/reactivates or updates the plugin.
			 */
			$is_new_user = ( false === get_option( 'cool-timelne-installDate' ) )
						&& ( false === get_option( 'ctl-install-date' ) );
					
			// Only show welcome redirect for genuine first-time installs.
			if ( $is_new_user ) {
				update_option( 'ctl_is_new_user', 'yes' );
				update_option( 'ctl_onboarding_method', 'default', false );
				set_transient( 'ctl_activation_redirect', 1, 5 * MINUTE_IN_SECONDS );
			}

			// Always update version and plugin type — these reflect current state.
			update_option( 'cool-free-timeline-v', CTL_V );
			update_option( 'cool-timelne-plugin-type', 'FREE' );

			/**
			 * Preserve original install date for existing users.
			 *
			 */
			if ( ! get_option( 'cool-timelne-installDate' ) ) {
				update_option( 'cool-timelne-installDate', gmdate( 'Y-m-d H:i:s' ) );
			}

			// Initialize rating flag only if missing — preserves "rated" status for existing users.
			add_option( 'cool-timeline-already-rated', 'no' );

			// Flag for rewrite rules flush — actual flush happens later on admin_init
			// (flushing inside activation hook is a WP best-practice violation).
			update_option( 'ctl_flush_rewrite_rules_flag', true );



			$this->ctl_init_install_options();

			if ( $this->ctl_is_tracking_enabled() ) {
				$this->ctl_maybe_schedule_tracking_cron();
			}
	}

		/* Deactivate the plugin */
		public function ctl_deactivate() {
			if (wp_next_scheduled('ctl_extra_data_update')) {
				wp_clear_scheduled_hook('ctl_extra_data_update');
			}
		}

	/**
	 * Retrieves server, theme, plugin, and onboarding information for telemetry.
	 *
	 */
	public static function ctl_get_user_info() {

		global $wpdb;
	
		// Server and WP environment details
		  
		$server_info = [

		'server_software'        => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'N/A',
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			'mysql_version'          => $wpdb ? sanitize_text_field( $wpdb->get_var( 'SELECT VERSION()' ) ) : 'N/A',
			'php_version'            => sanitize_text_field(phpversion() ?: 'N/A'),
			'wp_version'             => sanitize_text_field(get_bloginfo('version') ?: 'N/A'),
			'wp_debug'               => (defined('WP_DEBUG') && WP_DEBUG) ? 'Enabled' : 'Disabled',
			'wp_memory_limit'        => sanitize_text_field(ini_get('memory_limit') ?: 'N/A'),
			'wp_max_upload_size'     => sanitize_text_field(ini_get('upload_max_filesize') ?: 'N/A'),
			'wp_permalink_structure' => sanitize_text_field(get_option('permalink_structure') ?: 'Default'),
			'wp_multisite'           => is_multisite() ? 'Enabled' : 'Disabled',
			'wp_language'            => sanitize_text_field(get_option('WPLANG') ?: get_locale()),
			'wp_prefix'              => isset($wpdb->prefix) ? sanitize_key($wpdb->prefix) : 'N/A',

		];
	
		// Theme details
		$theme = wp_get_theme();
		$theme_data = [
			'name'      => sanitize_text_field($theme->get('Name')),
			'version'   => sanitize_text_field($theme->get('Version')),
			'theme_uri' => esc_url($theme->get('ThemeURI')),
		];
	

		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if (!function_exists('get_plugin_data')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	

		$plugin_data = [];
		$active_plugins = get_option('active_plugins', []);
	
		foreach ($active_plugins as $plugin_path) {
			$plugin_file = WP_PLUGIN_DIR . '/' . ltrim($plugin_path, '/');
	
			if (file_exists($plugin_file)) {

				$plugin_info = get_plugin_data($plugin_file, false, false);
				$plugin_url = !empty($plugin_info['PluginURI']) ? esc_url($plugin_info['PluginURI']) : (!empty($plugin_info['AuthorURI']) ? esc_url($plugin_info['AuthorURI']) : 'N/A');
				$plugin_data[] = [
					'name'       => sanitize_text_field($plugin_info['Name']),
					'version'    => sanitize_text_field($plugin_info['Version']),
					'plugin_uri' => !empty($plugin_url) ? $plugin_url : 'N/A',
				];
			}
		}
		 $onboarding_data=[];
		 $telemetry = get_option('ctl_onboarding_telemetry', array()); // cp-onboarding framework Telemetry store.
			if (!empty($telemetry)) {
				$onboarding_data = $telemetry;
				$onboarding_data['install_date']=get_option('ctl-install-date', 'N/A');
			}

		return [
			'server_info'   => $server_info,
			'extra_details' => [
				'wp_theme'        => $theme_data,
				'active_plugins'  => $plugin_data,
				'onboarding_data' => $onboarding_data
			],
		];
	}
	
	}
}

/*** THANKS - CoolPlugins.net ) */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$ctl = CoolTimeline::get_instance();
$ctl->registers();


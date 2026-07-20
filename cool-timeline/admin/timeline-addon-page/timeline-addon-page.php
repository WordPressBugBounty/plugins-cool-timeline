<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ctl_is_timeline_addon_page' ) ) {
	 // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	function ctl_is_timeline_addon_page() {
		global $pagenow;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended 
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : '';
		if ( 'admin.php' === $pagenow && ( 'cool-plugins-timeline-addon' === $page || 'cool_timeline_settings' === $page || 'timeline-addons-license' === $page ) ) {
			return true;
		}
		if ( ( 'edit.php' === $pagenow || 'post-new.php' === $pagenow ) && 'cool_timeline' === $type ) {
			return true;
		}
		// Single post edit screen: post_type is not in $_GET, so read from the current screen.
		if ( 'post.php' === $pagenow ) {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( $screen && 'cool_timeline' === $screen->post_type ) {
				return true;
			}
		}
		// Treat Cool Timeline story taxonomy screens (list + edit individual term) as timeline addon pages.
		if ( ( 'edit-tags.php' === $pagenow || 'term.php' === $pagenow ) && 'cool_timeline' === $type && 'ctl-stories' === $taxonomy ) {
			return true;
		}
		// Show the header on the TWAE welcome page only when a Cool Plugins pro plugin is active.
		// Each pro plugin defines a unique PHP constant on load; any one match is sufficient.
		if ( 'admin.php' === $pagenow && 'twae-welcome-page' === $page ) {
			$pro_constants = array(
				'CTP_PLUGIN_URL',    // cool-timeline-pro
				'CTLB_Pro_File',     // timeline-block-pro-for-gutenberg
				'TM_DIVI_PRO_V',     // cp-timeline-module-pro-for-divi
				'CTL_PLUGIN_URL',    // cool-timeline-free
			);
			foreach ( $pro_constants as $const ) {
				if ( defined( $const ) ) {
					return true;
				}
			}
		}
		return false;
	}
}

// Do not use namespace to keep this on global space to keep the singleton initialization working.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
if ( ! class_exists( 'cool_plugins_timeline_addons' ) ) {

	/**
	 * Main class for creating dashboard addon page and all submenu items.
	 * Do not call or initialize this class directly; use the function at the bottom of this file.
	 */
	class cool_plugins_timeline_addons {

		/** @var cool_plugins_timeline_addons|null */
		private static $instance;

		/** @var array */
		private $pro_plugins = array();

		/** @var array */
		private $plugin_demo_docs_urls = array();

		/** @var string|null */
		private $main_menu_slug = null;

		/** @var string|null */
		private $plugin_tag = null;

		/** @var string|null */
		private $dashboard_page_heading = null;

		/** @var string */
		private $addon_dir = '';

		/** @var string */
		private $addon_file = '';

		/** @var string */
		private $menu_title = 'Addon Dashboard';

		/** @var string|false */
		private $menu_icon = false;

		/** @var bool True when header was output at admin_notices (so dashboard body skips it). */
		private static $global_header_rendered = false;


		/** @var array Discontinued Pro plugin slugs that should never appear on the dashboard. */
		private static $discontinued_pro_slugs = array(
			'timeline-builder-pro',
		);

		/** Allowed plugin slugs for install/activate from this dashboard (whitelist). */
		private static $allowed_slugs = array(
			'cool-timeline',
			'timeline-widget-addon-for-elementor',
			'timeline-widget-addon-for-elementor-pro',
			'cool-timeline-pro',
			'timeline-block',
			'timeline-module-for-divi',
			'timeline-block-pro',
			'timeline-block-pro-for-gutenberg',
			'timeline-module-for-divi-pro',
			'cp-timeline-module-pro-for-divi',
		);

		/** Pro plugin slugs (no download from WP.org; activate if already installed). */
		private static $pro_plugin_slugs = array(
			'cool-timeline-pro',
			'timeline-widget-addon-for-elementor-pro',
			'timeline-block-pro',
			'timeline-block-pro-for-gutenberg',
			'timeline-module-for-divi-pro',
			'cp-timeline-module-pro-for-divi',
		);

		/** Map old slugs to current JSON slug (for cached dashboard data and backward compatibility). */
		private static $pro_slug_aliases = array(
			'timeline-module-for-divi-pro' => 'cp-timeline-module-pro-for-divi',
			'timeline-block-pro'            => 'timeline-block-pro-for-gutenberg',
		);

		public function __construct() {
			$this->addon_dir  = __DIR__;
			$this->addon_file = __FILE__;
		}

		/**
		 * Initialize the class and create dashboard page only one time.
		 *
		 * @return cool_plugins_timeline_addons
		 */
		public static function init() {
			if ( empty( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Initialize the dashboard with specific plugins as per plugin tag.
		 *
		 * @param string $plugin_tag         Tag for plugin grouping.
		 * @param string $menu_slug          Main menu slug.
		 * @param string $dashboard_heading  Dashboard heading.
		 * @param string $main_menu_title     Menu title.
		 * @param string $icon                Menu icon URL or dashicon.
		 * @return bool
		 */
		public function show_plugins( $plugin_tag, $menu_slug, $dashboard_heading, $main_menu_title, $icon ) {
			if ( empty( $plugin_tag ) || empty( $menu_slug ) || empty( $dashboard_heading ) ) {
				return false;
			}
			$this->plugin_tag            = sanitize_text_field( $plugin_tag );
			$this->main_menu_slug        = sanitize_text_field( $menu_slug );
			$this->dashboard_page_heading = sanitize_text_field( $dashboard_heading );
			$this->menu_title            = sanitize_text_field( $main_menu_title );
			$this->menu_icon             = sanitize_text_field( $icon );

			add_action( 'admin_menu', array( $this, 'init_plugins_dasboard_page' ), 1 );
			add_action( 'wp_ajax_ctl_dashboard_install_plugin', array( $this, 'ctl_dashboard_install_plugin' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_required_scripts' ) );
			add_action( 'admin_notices', array( $this, 'maybe_render_global_header' ), 1 );

			return true;
		}

		/**
		 * Get the dashboard heading.
		 *
		 * @return string|null
		 */
		public function get_dashboard_heading() {
			return $this->dashboard_page_heading;
		}

		/**
		 * Output the timeline header at the very top (admin_notices priority 1) on all timeline addon pages
		 * so that all notices (ours and third-party) display below the header.
		 */
		public function maybe_render_global_header() {
			if ( ! function_exists( 'ctl_is_timeline_addon_page' ) || ! ctl_is_timeline_addon_page() ) {
				return;
			}
			echo '<div class="ctl-global-timeline-header">';
			$prefix              = 'ctl';
			$show_wrapper        = false;
			$dashboard_instance  = $this;
			include $this->addon_dir . '/includes/dashboard-header.php';
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			do_action( 'ctl_after_timeline_header' );
			echo '</div>';
			self::$global_header_rendered = true;
		}

		/**
		 * Handle AJAX: install plugin via WordPress core or activate if already installed (including Pro).
		 */
		public function ctl_dashboard_install_plugin() {
			check_ajax_referer( 'ctl-plugins-download', 'wp_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				return wp_send_json_error( array(
					'errorMessage' => __( 'Sorry, you are not allowed to install plugins on this site.', 'cool-timeline' ),
				) );
				
				
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked above
			$slug = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
			if ( empty( $slug ) ) {
				return wp_send_json_error( array(
					'slug'         => '',
					'errorCode'    => 'no_plugin_specified',
					'errorMessage' => __( 'No plugin specified.', 'cool-timeline' ),
				) );
				
			}

			if ( ! in_array( $slug, self::$allowed_slugs, true ) ) {
				return wp_send_json_error( array(
					'slug'         => $slug,
					'errorCode'    => 'plugin_not_allowed',
					'errorMessage' => __( 'This plugin cannot be installed from here.', 'cool-timeline' ),
				) );
				
			}

			$status = array(
				'install' => 'plugin',
				'slug'    => $slug,
			);

			require_once __DIR__ . '/../class-ctl-plugin-installer.php';
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			$installer = new CTL_Plugin_Installer();

			// Pro plugins: only activate if already installed (no download from WP.org).
			if ( in_array( $slug, self::$pro_plugin_slugs, true ) ) {
				$slug_for_data = isset( self::$pro_slug_aliases[ $slug ] ) ? self::$pro_slug_aliases[ $slug ] : $slug;
				$pro_plugins   = $this->request_pro_plugins_data( $this->plugin_tag );
				$main_file     = ( ! empty( $pro_plugins[ $slug_for_data ]['main_file'] ) ) ? $pro_plugins[ $slug_for_data ]['main_file'] : ( $slug_for_data . '.php' );
				if ( substr( $main_file, -4 ) !== '.php' ) {
					$main_file .= '.php';
				}
				$plugin_file = $slug . '/' . $main_file;
				$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
				if ( ! file_exists( $plugin_path ) ) {
					$plugin_file = $slug_for_data . '/' . $main_file;
					$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
				}
				if ( ! file_exists( $plugin_path ) ) {
					// Fallback: discover main file from plugin directory (handles cached data without main_file or different filename).
					$all_plugins = get_plugins();
					foreach ( $all_plugins as $path => $plugin_data ) {
						if ( dirname( $path ) === $slug || dirname( $path ) === $slug_for_data ) {
							$plugin_file = $path;
							$plugin_path = WP_PLUGIN_DIR . '/' . $path;
							break;
						}
					}
				}
				if ( ! file_exists( $plugin_path ) && ! empty( $pro_plugins[ $slug_for_data ]['incompatible'] ) ) {
					// Pro may be installed in free_version folder (e.g. Timeline Block Pro in timeline-block/).
					$free_slug = $pro_plugins[ $slug_for_data ]['incompatible'];
					$free_dir  = WP_PLUGIN_DIR . '/' . $free_slug;
					if ( file_exists( $free_dir ) ) {
						$all_plugins = get_plugins();
						foreach ( $all_plugins as $path => $plugin_data ) {
							if ( dirname( $path ) === $free_slug ) {
								$plugin_file = $path;
								$plugin_path = WP_PLUGIN_DIR . '/' . $path;
								break;
							}
						}
					}
				}
				if ( ! file_exists( $plugin_path ) ) {
					return wp_send_json_error( array(
						'errorMessage' => __( 'Pro plugin must be installed manually. Purchase and download from the product page.', 'cool-timeline' ),
					) );
					
				}

				$result = $installer->activate_plugin_file( $plugin_file, $slug );
				if ( $result['success'] ) {
					wp_send_json_success( $result['data'] );
				}

				return wp_send_json_error( $result['data'] );
			}

			$result = $installer->install_and_activate( $slug, $status );
			if ( $result['success'] ) {
				wp_send_json_success( $result['data'] );
			}

			return wp_send_json_error( $result['data'] );
		}

		/**
		 * Register the main dashboard menu and submenu.
		 */
		public function init_plugins_dasboard_page() {
			add_menu_page(
				$this->menu_title,
				$this->menu_title,
				'manage_options',
				$this->main_menu_slug,
				array( $this, 'displayPluginAdminDashboard' ),
				$this->menu_icon,
				9
			);
			add_submenu_page(
				$this->main_menu_slug,
				__( 'Dashboard', 'cool-timeline' ),
				__( 'Dashboard', 'cool-timeline' ),
				'manage_options',
				$this->main_menu_slug,
				array( $this, 'displayPluginAdminDashboard' ),
				1
			);
		}

		/**
		 * Render the dashboard: load data, build activated/available/pro lists with Free→Pro mapping, then output via templates.
		 */
	public function displayPluginAdminDashboard() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Access denied.', 'cool-timeline' ) );
			}

			$tag     = $this->plugin_tag;
			$plugins = $this->request_wp_plugins_data( $tag );
			$pro_plugins = $this->request_pro_plugins_data( $tag );

			$pro_plugin_slugs = array_keys( $pro_plugins );
			$free_to_pro_mapping = array();
			if ( ! empty( $pro_plugins ) ) {
				foreach ( $pro_plugins as $slug => $data ) {
					if ( ! empty( $data['incompatible'] ) && 'false' !== $data['incompatible'] ) {
						$free_to_pro_mapping[ $data['incompatible'] ] = $slug;
					}
				}
			}

		$prefix = 'ctl';
		$activated_addons = array();
		$available_addons = array();
		$pro_addons       = array();

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$elementor_active = function_exists( 'is_plugin_active' ) && is_plugin_active( 'elementor/elementor.php' );
		$elementor_slugs  = array(
			'timeline-widget-addon-for-elementor',
			'timeline-widget-addon-for-elementor-pro',
		);

		$theme            = wp_get_theme();
		$divi_active      = ( 'Divi' === $theme->get( 'Name' ) || 'Divi' === $theme->get( 'Template' ) );
		$divi_slugs       = array(
			'timeline-module-for-divi',
			'cp-timeline-module-pro-for-divi',
			'timeline-module-for-divi-pro',
		);

		if ( ! empty( $plugins ) ) {
			foreach ( $plugins as $plugin ) {
				$plugin_slug = $plugin['slug'];
				if ( in_array( $plugin_slug, $pro_plugin_slugs, true ) ) {
					continue;
				}
					if ( isset( $free_to_pro_mapping[ $plugin_slug ] ) ) {
						$pro_slug    = $free_to_pro_mapping[ $plugin_slug ];
						$pro_dir     = WP_PLUGIN_DIR . '/' . $pro_slug;
						if ( file_exists( $pro_dir ) ) {
							$pro_active = false;
							$files      = glob( $pro_dir . '/*.php' );
							if ( ! empty( $files ) ) {
								foreach ( $files as $pf ) {
									if ( is_plugin_active( plugin_basename( $pf ) ) ) {
										$pro_active = true;
										break;
									}
								}
							}
							if ( $pro_active ) {
								continue;
							}
						}
					}

					$plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
					if ( file_exists( $plugin_dir ) ) {
						$plugin_files = glob( $plugin_dir . '/*.php' );
						$is_active    = false;
						$main_file    = '';
						foreach ( $plugin_files as $pf ) {
							$basename = plugin_basename( $pf );
							if ( empty( $main_file ) ) {
								$headers = get_file_data( $pf, array( 'Plugin Name' => 'Plugin Name' ) );
								if ( ! empty( $headers['Plugin Name'] ) ) {
									$main_file = $basename;
								}
							}
							if ( is_plugin_active( $basename ) ) {
								$is_active = true;
								$main_file = $basename;
								break;
							}
						}
						if ( ! empty( $main_file ) ) {
							$plugin['plugin_basename'] = $main_file;
							$path = WP_PLUGIN_DIR . '/' . $main_file;
							if ( file_exists( $path ) ) {
								$data = get_plugin_data( $path, false, false );
								if ( ! empty( $data['Version'] ) ) {
									$plugin['installed_version'] = $data['Version'];
								}
							}
						}
				$plugin['has_update'] = $this->check_plugin_update( $plugin_slug );
				$needs_elementor = in_array( $plugin_slug, $elementor_slugs, true ) && ! $elementor_active;
				$needs_divi      = in_array( $plugin_slug, $divi_slugs, true ) && ! $divi_active;
				if ( $is_active && ! $needs_elementor && ! $needs_divi ) {
					$activated_addons[] = $plugin;
				} else {
					$plugin['needs_activation'] = true;
						$available_addons[]        = $plugin;
					}
					} else {
						$available_addons[] = $plugin;
					}
				}
			}

		if ( ! empty( $pro_plugins ) ) {
			foreach ( $pro_plugins as $plugin ) {
				$plugin_slug = $plugin['slug'];
				$has_buy     = ! empty( $plugin['buyLink'] );
				$is_pro      = ( strpos( $plugin_slug, '-pro' ) !== false ) || in_array( $plugin_slug, self::$pro_plugin_slugs, true );
			if ( ! $has_buy && ! $is_pro ) {
				continue;
			}
					$plugin_dir     = WP_PLUGIN_DIR . '/' . $plugin_slug;
					$used_free_dir  = false;
					$pro_name       = isset( $plugin['name'] ) ? trim( $plugin['name'] ) : '';
					$main_file      = '';
					$is_active      = false;
					// If pro folder does not exist, try to find Pro by name in get_plugins() (handles different folder names).
					if ( ! file_exists( $plugin_dir ) && $pro_name ) {
						$all_plugins = get_plugins();
						$pro_name_lower = strtolower( $pro_name );
						foreach ( $all_plugins as $p_path => $p_data ) {
							$p_name = isset( $p_data['Name'] ) ? trim( $p_data['Name'] ) : '';
							$exact_match = ( $p_name === $pro_name || strtolower( $p_name ) === $pro_name_lower );
							// Also match if plugin name contains key parts of pro name (e.g. "Timeline Block Pro" vs "Timeline Block (Pro)").
							$loose_match = false;
							if ( $p_name && ! $exact_match ) {
								$p_lower = strtolower( $p_name );
								if ( 'timeline-block-pro-for-gutenberg' === $plugin_slug ) {
									// Gutenberg Timeline Block Pro – look for any variant of "timeline block" + "pro".
									$loose_match = ( strpos( $p_lower, 'timeline block' ) !== false && strpos( $p_lower, 'pro' ) !== false );
								} elseif ( in_array( $plugin_slug, array( 'cp-timeline-module-pro-for-divi', 'timeline-module-for-divi-pro' ), true ) ) {
									// Divi module Pro – look for "timeline module" + "divi" + "pro".
									$loose_match = ( strpos( $p_lower, 'timeline module' ) !== false && strpos( $p_lower, 'divi' ) !== false && strpos( $p_lower, 'pro' ) !== false );
								}
							}
							// Match when plugin is in a known old slug folder (e.g. timeline-block-pro for Timeline Block Pro).
							$p_dir = dirname( $p_path );
							$folder_match = ( $p_dir === 'timeline-block-pro' && stripos( $p_name, 'pro' ) !== false )
								|| ( $p_dir === 'timeline-module-for-divi-pro' && stripos( $p_name, 'pro' ) !== false );
							if ( $exact_match || $loose_match || $folder_match ) {
								$plugin_dir    = WP_PLUGIN_DIR . '/' . dirname( $p_path );
								$used_free_dir = ( dirname( $p_path ) === ( isset( $plugin['incompatible'] ) ? $plugin['incompatible'] : '' ) );
								$main_file     = $p_path;
								$is_active     = is_plugin_active( $p_path );
								break;
							}
						}
					}
					// If still no dir, try free_version folder (pro sometimes shipped in same dir as free).
					if ( ! file_exists( $plugin_dir ) && ! empty( $plugin['incompatible'] ) && 'false' !== $plugin['incompatible'] ) {
						$free_dir = WP_PLUGIN_DIR . '/' . $plugin['incompatible'];
						if ( file_exists( $free_dir ) ) {
							$plugin_dir    = $free_dir;
							$used_free_dir = true;
						}
					}
					if ( file_exists( $plugin_dir ) ) {
						$plugin_files = glob( $plugin_dir . '/*.php' );
						if ( empty( $main_file ) ) {
							$is_active = false;
						}
						// When using free dir, try to find the Pro plugin file (same folder may have both free and pro).
						if ( empty( $main_file ) && $used_free_dir && ! empty( $plugin_files ) && $pro_name ) {
							$pro_name_lower = strtolower( $pro_name );
							foreach ( $plugin_files as $pf ) {
								$fdata = get_plugin_data( $pf, false, false );
								$fname = isset( $fdata['Name'] ) ? trim( $fdata['Name'] ) : '';
								$fbase = plugin_basename( $pf );
								$name_matches = $fname && ( $fname === $pro_name || strtolower( $fname ) === $pro_name_lower );
								$file_looks_pro = strpos( $fbase, '-pro' ) !== false && stripos( $fname, 'Pro' ) !== false;
								if ( $name_matches || $file_looks_pro ) {
									$main_file = $fbase;
									$is_active = is_plugin_active( $fbase );
									break;
								}
							}
						}
						if ( empty( $main_file ) ) {
							foreach ( $plugin_files as $pf ) {
								$basename = plugin_basename( $pf );
								if ( empty( $main_file ) ) {
									$headers = get_file_data( $pf, array( 'Plugin Name' => 'Plugin Name' ) );
									if ( ! empty( $headers['Plugin Name'] ) ) {
										$main_file = $basename;
									}
								}
								if ( is_plugin_active( $basename ) ) {
									$is_active = true;
									$main_file = $basename;
									break;
								}
							}
						}
						if ( ! empty( $main_file ) ) {
							$plugin['plugin_basename'] = $main_file;
							$path = WP_PLUGIN_DIR . '/' . $main_file;
							$data = array();
							if ( file_exists( $path ) ) {
								$data = get_plugin_data( $path, false, false );
								if ( ! empty( $data['Version'] ) ) {
									$plugin['installed_version'] = $data['Version'];
								}
							}
							$plugin['has_update'] = $this->check_plugin_update( $plugin_slug );
							// When we used the free dir: only show Pro in Premium if we didn't find the Pro plugin file in the folder.
							$installed_is_pro = false;
							if ( $used_free_dir ) {
								$installed_is_pro = ! empty( $data['Name'] ) && ( $data['Name'] === $pro_name || ( strpos( $main_file, '-pro' ) !== false && stripos( $data['Name'], 'Pro' ) !== false ) );
							}
						$needs_elementor = in_array( $plugin_slug, $elementor_slugs, true ) && ! $elementor_active;
						$needs_divi      = in_array( $plugin_slug, $divi_slugs, true ) && ! $divi_active;
						if ( $used_free_dir && ! $installed_is_pro ) {
							$pro_addons[] = $plugin;
						} elseif ( $is_active && ! $needs_elementor && ! $needs_divi ) {
							$activated_addons[] = $plugin;
						} else {
								$plugin['needs_activation']  = true;
								$plugin['is_pro_installed'] = true;
								$available_addons[]        = $plugin;
							}
						} else {
							$pro_addons[] = $plugin;
						}
					} else {
						$pro_addons[] = $plugin;
					}
				}
			}

			if ( ! empty( $activated_addons ) || ! empty( $available_addons ) || ! empty( $pro_addons ) ) {
				$this->render_modern_dashboard( $prefix, $activated_addons, $available_addons, $pro_addons );
			} else {
				echo '<div class="notice notice-warning"><p>' . esc_html__( 'No plugins data available at the moment.', 'cool-timeline' ) . '</p></div>';
			}
	}

		/**
		 * Check if a plugin has an update available.
		 *
		 * @param string $plugin_slug Plugin directory slug.
		 * @return string|false New version string or false.
		 */
		public function check_plugin_update( $plugin_slug ) {
			$updates = get_site_transient( 'update_plugins' );
			if ( ! empty( $updates->response ) && is_array( $updates->response ) ) {
				foreach ( $updates->response as $file => $data ) {
					if ( strpos( $file, $plugin_slug ) !== false && isset( $data->new_version ) ) {
						return $data->new_version;
					}
				}
			}
			return false;
		}

		/**
		 * Render the modern dashboard layout (header + content + sidebar) with prefix-based markup.
		 *
		 * @param string $prefix             CSS/JS prefix (e.g. 'ctl').
		 * @param array  $activated_addons  Activated plugins.
		 * @param array  $available_addons  Available (install or activate) plugins.
		 * @param array  $pro_addons        Pro plugins not installed.
		 */
		/**
             * Render Modern Dashboard UI (Using Modular Include Files)
             */
            function render_modern_dashboard($prefix, $activated_addons, $available_addons, $pro_addons){

                // Store instance for use in included files
                $dashboard_instance = $this;
                
                // Sanitize prefix
                $prefix = sanitize_key($prefix);
                
                ?>
                
                <div class="<?php echo esc_attr( $prefix ); ?>-dashboard-wrapper">
                    <?php
					
                    if ( ! self::$global_header_rendered ) {
                        include $this->addon_dir . '/includes/dashboard-header.php';
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
                        do_action( 'ctl_after_timeline_header' );
                    }
                    ?>

                    <div class="<?php echo esc_attr($prefix); ?>-main-grid">
                        <?php 
                        // Include Main Content (Plugin Cards)
                        include $this->addon_dir . '/includes/dashboard-page.php'; 
                        
                        // Include Sidebar
                        include $this->addon_dir . '/includes/dashboard-sidebar.php'; 
                        ?>
                    </div>
                </div>
                <?php
            }  // End of render_modern_dashboard function

		/**
		 * Get demo and docs URLs for a plugin.
		 *
		 * @param string $plugin_slug   Slug.
		 * @param bool   $is_pro_plugin Whether it is a pro plugin.
		 * @return array{ demo: string, docs: string }
		 */
		public function get_plugin_demo_docs_urls( $plugin_slug, $is_pro_plugin = false ) {
			$url_map = $this->get_plugin_demo_docs_url_map( $is_pro_plugin );

			if ( isset( $url_map[ $plugin_slug ] ) ) {
				return $url_map[ $plugin_slug ];
			}

			return $this->get_default_plugin_demo_docs_urls();
		}

		/**
		 * Get cached demo/docs URLs indexed by plugin slug.
		 *
		 * @param bool $is_pro_plugin Whether to load pro plugin URLs.
		 * @return array
		 */
		private function get_plugin_demo_docs_url_map( $is_pro_plugin = false ) {
			$type = $is_pro_plugin ? 'pro' : 'free';

			if ( isset( $this->plugin_demo_docs_urls[ $type ] ) ) {
				return $this->plugin_demo_docs_urls[ $type ];
			}

			$plugins = $is_pro_plugin ? $this->request_pro_plugins_data() : $this->request_wp_plugins_data();
			$urls    = array();
			$defaults = $this->get_default_plugin_demo_docs_urls();

			foreach ( $plugins as $slug => $plugin ) {
				$plugin_slug = ! empty( $plugin['slug'] ) ? sanitize_key( $plugin['slug'] ) : sanitize_key( $slug );
				if ( empty( $plugin_slug ) ) {
					continue;
				}

				$urls[ $plugin_slug ] = array(
					'demo' => ! empty( $plugin['demo_url'] ) ? esc_url( $plugin['demo_url'] ) : $defaults['demo'],
					'docs' => ! empty( $plugin['docs_url'] ) ? esc_url( $plugin['docs_url'] ) : $defaults['docs'],
				);
			}

			$this->plugin_demo_docs_urls[ $type ] = $urls;

			return $this->plugin_demo_docs_urls[ $type ];
		}

		/**
		 * Get default demo/docs URLs.
		 *
		 * @return array{ demo: string, docs: string }
		 */
		private function get_default_plugin_demo_docs_urls() {
			return array(
				'demo' => esc_url( 'https://cooltimeline.com/demo/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard' ),
				'docs' => esc_url( 'https://cooltimeline.com/docs/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard' ),
			);
		}

		/**
		 * Output demo + docs links markup for a plugin card.
		 *
		 * @param string $prefix        CSS prefix.
		 * @param string $plugin_slug   Slug.
		 * @param bool   $is_pro_plugin Whether pro.
		 */
		private function render_plugin_card_demo_docs_links( $prefix, $plugin_slug, $is_pro_plugin ) {
			$urls = $this->get_plugin_demo_docs_urls( $plugin_slug, $is_pro_plugin );
			$demo = $urls['demo'];
			$docs = $urls['docs'];
			?>
			<div class="<?php echo esc_attr( $prefix ); ?>-card-links">
				<a href="<?php echo esc_url( $demo ); ?>" target="_blank" rel="noopener" title="<?php esc_attr_e( 'View Demo', 'cool-timeline' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><g fill="currentColor"><path d="M10.5 8a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7a3.5 3.5 0 0 0 0 7"/></g></svg>
					<?php esc_html_e( 'Demo', 'cool-timeline' ); ?>
				</a>
				<a href="<?php echo esc_url( $docs ); ?>" target="_blank" rel="noopener" title="<?php esc_attr_e( 'Documentation', 'cool-timeline' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56 56"><path fill="currentColor" d="M15.555 53.125h24.89c4.852 0 7.266-2.461 7.266-7.336V24.508H30.742c-3 0-4.406-1.43-4.406-4.43V2.875H15.555c-4.828 0-7.266 2.484-7.266 7.36v35.554c0 4.898 2.438 7.336 7.266 7.336m15.258-31.828h16.64c-.164-.961-.844-1.899-1.945-3.047L32.57 5.102c-1.078-1.125-2.062-1.805-3.047-1.97v16.9c0 .843.446 1.265 1.29 1.265m-11.836 13.36c-.961 0-1.641-.68-1.641-1.594c0-.915.68-1.594 1.64-1.594h18.07c.938 0 1.665.68 1.665 1.593c0 .915-.727 1.594-1.664 1.594Zm0 8.929c-.961 0-1.641-.68-1.641-1.594s.68-1.594 1.64-1.594h18.07c.938 0 1.665.68 1.665 1.594s-.727 1.594-1.664 1.594Z"/></svg>
					<?php esc_html_e( 'Docs', 'cool-timeline' ); ?>
				</a>
			</div>
			<?php
		}

		/**
		 * Render a single plugin card (activated, available, or pro).
		 *
		 * @param string $prefix CSS prefix.
		 * @param array  $plugin Plugin data.
		 * @param string $type   'activated'|'available'|'pro'.
		 */
		public function render_plugin_card( $prefix, $plugin, $type = 'activated' ) {
			$prefix = sanitize_key( $prefix );
			$type   = sanitize_key( $type );

			$plugin_name = isset( $plugin['name'] ) ? sanitize_text_field( $plugin['name'] ) : '';
			$plugin_desc = isset( $plugin['desc'] ) ? wp_kses_post( $plugin['desc'] ) : '';
			$plugin_slug = isset( $plugin['slug'] ) ? sanitize_key( $plugin['slug'] ) : '';
			$plugin_logo = ! empty( $plugin['logo'] ) ? $plugin['logo'] : '';

			$has_update  = isset( $plugin['has_update'] ) ? $plugin['has_update'] : false;
			$avail_ver   = isset( $plugin['latest_version'] ) ? $plugin['latest_version'] : ( isset( $plugin['version'] ) ? $plugin['version'] : '' );
			$show_ver    = isset( $plugin['installed_version'] ) ? sanitize_text_field( $plugin['installed_version'] ) : sanitize_text_field( $avail_ver );

			if ( empty( $plugin_name ) || empty( $plugin_slug ) ) {
				return;
			}

			$is_pro = ( 'pro' === $type ) || ( ! empty( $plugin['is_pro_installed'] ) ) || ( 'activated' === $type && ( strpos( $plugin_slug, '-pro' ) !== false || in_array( $plugin_slug, self::$pro_plugin_slugs, true ) ) );
			?>
			<div class="<?php echo esc_attr( $prefix ); ?>-card">
				<?php if ( ! empty( $has_update ) ) : ?>
					<div title="<?php esc_attr_e( 'Update available', 'cool-timeline' ); ?>" class="<?php echo esc_attr( $prefix ); ?>-pulse-wrapper"></div>
					<div title="<?php esc_attr_e( 'Update available', 'cool-timeline' ); ?>" class="<?php echo esc_attr( $prefix ); ?>-notification-dot"></div>
				<?php endif; ?>
				<?php if ( $is_pro ) : ?>
					<span class="<?php echo esc_attr( $prefix ); ?>-badge <?php echo esc_attr( $prefix ); ?>-badge-premium"><?php esc_html_e( 'Pro', 'cool-timeline' ); ?></span>
				<?php endif; ?>
				<div class="<?php echo esc_attr( $prefix ); ?>-icon-box">
					<img src="<?php echo esc_url( $plugin_logo ); ?>" alt="<?php echo esc_attr( $plugin_name ); ?>">
				</div>
				<div class="<?php echo esc_attr( $prefix ); ?>-info">
					<h3><?php echo esc_html( $plugin_name ); ?></h3>
					<p><?php echo esc_html( $plugin_desc ); ?></p>
					<?php if ( 'activated' === $type ) : ?>
						<div class="<?php echo esc_attr( $prefix ); ?>-badge-group">
							<div class="<?php echo esc_attr( $prefix ); ?>-active-update">
								<span class="<?php echo esc_attr( $prefix ); ?>-badge <?php echo esc_attr( $prefix ); ?>-badge-active"><?php esc_html_e( 'Active', 'cool-timeline' ); ?></span>
								<?php if ( $show_ver ) : ?>
									<span class="<?php echo esc_attr( $prefix ); ?>-badge <?php echo esc_attr( $prefix ); ?>-badge-version">v <?php echo esc_html( $show_ver ); ?></span>
								<?php endif; ?>
							</div>
							<?php if ( 'pro' !== $type ) : ?>
								<?php $this->render_plugin_card_demo_docs_links( $prefix, $plugin_slug, $is_pro ); ?>
							<?php endif; ?>
						</div>
					<?php elseif ( 'available' === $type ) : ?>
						<div class="<?php echo esc_attr( $prefix ); ?>-card-footer">
							<?php
							$needs_activation = ! empty( $plugin['needs_activation'] ) && ! empty( $plugin['plugin_basename'] );
							$install_nonce    = wp_create_nonce( 'ctl-plugins-download' );
							?>
							<button type="button"
								class="button <?php echo esc_attr( $prefix ); ?>-button-primary <?php echo esc_attr( $prefix ); ?>-install-plugin <?php echo $needs_activation ? esc_attr( $prefix ) . '-btn-activate' : esc_attr( $prefix ) . '-btn-install'; ?>"
								data-slug="<?php echo esc_attr( $plugin_slug ); ?>"
								data-nonce="<?php echo esc_attr( $install_nonce ); ?>">
								<?php echo $needs_activation ? esc_html__( 'Activate Now', 'cool-timeline' ) : esc_html__( 'Install Now', 'cool-timeline' ); ?>
							</button>
							<?php $this->render_plugin_card_demo_docs_links( $prefix, $plugin_slug, $is_pro ); ?>
						</div>
					<?php elseif ( 'pro' === $type ) : ?>
						<div class="<?php echo esc_attr( $prefix ); ?>-card-footer">
							<a href="<?php echo esc_url( isset( $plugin['buyLink'] ) ? $plugin['buyLink'] : '#' ); ?>" target="_blank" rel="noopener" class="button <?php echo esc_attr( $prefix ); ?>-button-primary <?php echo esc_attr( $prefix ); ?>-btn-buy">
								<?php esc_html_e( 'Buy Pro', 'cool-timeline' ); ?>
							</a>
							<?php $this->render_plugin_card_demo_docs_links( $prefix, $plugin_slug, true ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Enqueue dashboard CSS/JS and localize script.
		 * The menu icon style is global; dashboard assets are loaded only on timeline addon pages.
		 */
		public function enqueue_required_scripts() {
			$this->enqueue_menu_icon_style();

			if ( ! function_exists( 'ctl_is_timeline_addon_page' ) || ! ctl_is_timeline_addon_page() ) {
				return;
			}

			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_style( 'cool-plugins-timeline-addon', plugin_dir_url( __FILE__ ) . 'assets/css/styles.css', array(), null, 'all' );
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
			if ( $page === $this->main_menu_slug ) {
				// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				wp_enqueue_script( 'cool-plugins-timeline-addon', plugin_dir_url( __FILE__ ) . 'assets/js/script.js', array( 'jquery' ), null, true );
			if ( ! function_exists( 'is_plugin_active' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}
				wp_localize_script( 'cool-plugins-timeline-addon', 'cp_events', array(
					'ajax_url'              => admin_url( 'admin-ajax.php' ),
					'plugin_tag'            => $this->plugin_tag,
					'prefix'                => 'ctl',
					'install_action'        => 'ctl_dashboard_install_plugin',
					'install_nonce'         => wp_create_nonce( 'ctl-plugins-download' ),
					'activated_label'       => __( 'Activated', 'cool-timeline' ),
				'elementor_active'      => function_exists( 'is_plugin_active' ) && is_plugin_active( 'elementor/elementor.php' ),
				'elementor_slugs'       => array( 'timeline-widget-addon-for-elementor', 'timeline-widget-addon-for-elementor-pro' ),
				'elementor_required_msg'=> __( 'Elementor plugin is required. Please install and activate it first.', 'cool-timeline' ),
				'divi_active'           => ( function_exists( 'wp_get_theme' ) && ( wp_get_theme()->get( 'Name' ) === 'Divi' || wp_get_theme()->get( 'Template' ) === 'Divi' ) ),
				'divi_slugs'            => array( 'timeline-module-for-divi', 'cp-timeline-module-pro-for-divi', 'timeline-module-for-divi-pro' ),
				) );
			}

			wp_enqueue_script(
				'ctl-migration-js',
				plugin_dir_url( __FILE__ ) . 'assets/js/migration.js',
				array( 'jquery' ),
				'1.0',
				true
			);
			wp_localize_script( 'ctl-migration-js', 'ctl_migration', array(
				'nonce'        => wp_create_nonce( 'ctl_migrate_nonce' ),
				'redirect_url' => esc_url( admin_url( 'edit.php?post_type=cool_timeline' ) ),
				'ajax_url'     => admin_url( 'admin-ajax.php' ),
			) );
		}

		/**
		 * Enqueue the small global style required for the wp-admin menu icon.
		 */
		private function enqueue_menu_icon_style() {
			wp_register_style( 'cool-plugins-timeline-addon-menu-icon', false, array(), null );
			wp_enqueue_style( 'cool-plugins-timeline-addon-menu-icon' );
			wp_add_inline_style(
				'cool-plugins-timeline-addon-menu-icon',
				'li#toplevel_page_cool-plugins-timeline-addon img{width:18px;height:18px;}'
			);
		}

		/**
		 * Load plugins data from JSON fallback file (no external API).
		 *
		 * @param string $type 'free'|'pro'.
		 * @return array
		 */
		private function load_json_fallback( $type = 'free' ) {
			$json_file = $this->addon_dir . '/data/' . $type . '-plugins.json';
			if ( ! file_exists( $json_file ) ) {
				return array();
			}

			$json_content = file_get_contents( $json_file );
			$placeholders = array( '{{CTL_V}}' => 'CTL_V' );
			foreach ( $placeholders as $placeholder => $constant_name ) {
				if ( defined( $constant_name ) ) {
					$json_content = str_replace( $placeholder, constant( $constant_name ), $json_content );
				}
			}

			$plugin_info = json_decode( $json_content, true );
			if ( empty( $plugin_info ) || ! is_array( $plugin_info ) ) {
				return array();
			}

			$plugins_data = array();
			foreach ( $plugin_info as $plugin ) {
				if ( empty( $plugin['slug'] ) ) {
					continue;
				}
				$json_image_url = isset( $plugin['image_url'] ) ? $plugin['image_url'] : '';
				$image_url      = '';
				if ( ! empty( $json_image_url ) ) {
					if ( strpos( $json_image_url, 'http' ) === 0 ) {
						$image_url = $json_image_url;
					} else {
						$image_url = plugin_dir_url( $this->addon_file ) . 'assets/images/' . $json_image_url;
					}
			} else {
				$image_url = '';
			}
				$static_version  = isset( $plugin['version'] ) ? $plugin['version'] : '';
				$latest_version  = isset( $plugin['latest_version'] ) ? $plugin['latest_version'] : $static_version;
				$data = array(
					'name'          => isset( $plugin['name'] ) ? $plugin['name'] : '',
					'logo'          => $image_url,
					'slug'          => $plugin['slug'],
					'desc'          => isset( $plugin['info'] ) ? $plugin['info'] : '',
					'version'       => $static_version,
					'latest_version'=> $latest_version,
					'demo_url'      => isset( $plugin['demo_url'] ) ? $plugin['demo_url'] : '',
					'docs_url'      => isset( $plugin['docs_url'] ) ? $plugin['docs_url'] : '',
				);
				if ( 'pro' === $type ) {
					$data['buyLink']       = isset( $plugin['buy_url'] ) ? $plugin['buy_url'] : '';
					$data['download_link'] = null;
					$data['incompatible']  = isset( $plugin['free_version'] ) ? $plugin['free_version'] : null;
					$data['main_file']    = isset( $plugin['main_file'] ) ? $plugin['main_file'] : '';
				} else {
					$data['tags']           = isset( $plugin['tag'] ) ? $plugin['tag'] : '';
					$data['download_link']  = isset( $plugin['download_url'] ) ? $plugin['download_url'] : '';
				}
				$plugins_data[ $plugin['slug'] ] = $data;
			}
			return $plugins_data;
		}

		/**
		 * Get pro plugins data (from JSON, cached in transient/option).
		 *
		 * @param string|null $tag Optional tag filter.
		 * @return array
		 */
		public function request_pro_plugins_data( $tag = null ) {
			$this->pro_plugins = $this->request_plugins_data(
				'pro',
				$this->main_menu_slug . '_pro_api_cache' . $this->plugin_tag,
				$this->main_menu_slug . '-' . $this->plugin_tag . '-pro',
				$this->main_menu_slug . '_' . $this->plugin_tag . '_pro_json_sig'
			);

			return $this->pro_plugins;
		}

		/**
		 * Get free plugins data (from JSON, cached in transient/option). No external API.
		 *
		 * @param string|null $tag Optional tag filter.
		 * @return array
		 */
		public function request_wp_plugins_data( $tag = null ) {
			return $this->request_plugins_data(
				'free',
				$this->main_menu_slug . '_api_cache' . $this->plugin_tag,
				$this->main_menu_slug . '-' . $this->plugin_tag,
				$this->main_menu_slug . '_' . $this->plugin_tag . '_free_json_sig'
			);
		}

		/**
		 * Get plugin data from local JSON with transient/option fallback.
		 *
		 * @param string $type        Plugin data type: free or pro.
		 * @param string $trans_name  Transient key.
		 * @param string $option_name Option fallback key.
		 * @param string $ver_option  JSON signature option key.
		 * @return array
		 */
		private function request_plugins_data( $type, $trans_name, $option_name, $ver_option ) {
			$json_file  = $this->addon_dir . '/data/' . $type . '-plugins.json';
			$json_sig   = file_exists( $json_file ) ? (string) filemtime( $json_file ) : '';
			$stored_sig = (string) get_option( $ver_option, '' );

			if ( $json_sig !== '' && $stored_sig !== $json_sig ) {
				delete_transient( $trans_name );
				delete_option( $option_name );
			}

			$plugins = $this->filter_discontinued_pro_addons( $this->load_json_fallback( $type ) );
			if ( ! empty( $plugins ) && is_array( $plugins ) ) {
				set_transient( $trans_name, $plugins, DAY_IN_SECONDS );
				update_option( $option_name, $plugins );
				if ( $json_sig !== '' ) {
					update_option( $ver_option, $json_sig );
				}
				return $plugins;
			}

			$cached = get_transient( $trans_name );
			if ( false !== $cached && ! empty( $cached ) && is_array( $cached ) ) {
				return $this->filter_discontinued_pro_addons( $cached );
			}

			if ( get_option( $option_name, false ) ) {
				return $this->filter_discontinued_pro_addons( get_option( $option_name ) );
			}

			return array();
		}

		/**
		 * Remove discontinued Pro addons from a plugins array, regardless of source (JSON, transient, or option).
		 *
		 * @param array $plugins Raw plugins array (expected to be keyed by slug).
		 * @return array Filtered plugins array.
		 */
		private function filter_discontinued_pro_addons( $plugins ) {
			if ( empty( $plugins ) || ! is_array( $plugins ) ) {
				return array();
			}
			$filtered = array();
			foreach ( $plugins as $slug => $plugin ) {
				$slug_key = is_string( $slug ) ? $slug : ( isset( $plugin['slug'] ) ? $plugin['slug'] : '' );
				if ( $slug_key && in_array( $slug_key, self::$discontinued_pro_slugs, true ) ) {
					continue;
				}
				$key = $slug_key ? $slug_key : $slug;
				$filtered[ $key ] = $plugin;
			}
			return $filtered;
		}

	}

	/**
	 * Initialize the main dashboard class with all required parameters.
	 *
	 * @param string $tag                  Plugin tag.
	 * @param string $settings_page_slug   Menu slug.
	 * @param string $dashboard_heading    Heading.
	 * @param string $main_menu_title      Menu title.
	 * @param string $icon                 Icon URL or dashicon.
	 */
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	function cool_plugins_timeline_addons_settings_page( $tag, $settings_page_slug, $dashboard_heading, $main_menu_title, $icon ) {
		$page = cool_plugins_timeline_addons::init();
		$page->show_plugins( $tag, $settings_page_slug, $dashboard_heading, $main_menu_title, $icon );
	}
}

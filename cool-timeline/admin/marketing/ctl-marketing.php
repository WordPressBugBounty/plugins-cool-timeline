<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'Ctl_Marketing_Controllers' ) ) {

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
    class Ctl_Marketing_Controllers {

        private static $instance = null;

        /**
         *  Singleton instance
         */
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         *  Constructor
         *
         * Initializes hooks and actions.
         */
        public function __construct() {
            add_action( 'wp_ajax_ctl_install_plugin', [ $this, 'ctl_install_plugin' ] );
            add_action( 'admin_init', [ $this, 'show_marketing_notices' ] ); 
        }

        /**
         * Check if a theme (or parent) matches target name.
         * Uses WP_Theme API to be safe.
         */
        public static function is_theme_activate( $target ) {
            $theme  = wp_get_theme();
            $name   = (string) $theme->get( 'Name' );
            $parent = (string) $theme->get( 'Template' );    // parent theme folder (template)
            $sheet  = (string) $theme->get_stylesheet();     // current stylesheet folder

            $target_l = strtolower( $target );

            if ( stripos( $name, $target ) !== false ) {
                return true;
            }
            // check parent/template or stylesheet folder
            if ( stripos( $parent, $target_l ) !== false || stripos( $sheet, $target_l ) !== false ) {
                return true;
            }

            return false;
        }

        public function show_marketing_notices() {

            $is_tec_settings = $this->is_timeline_admin_screen();

            if ( $is_tec_settings ) {
                $this->enqueue_marketing_script();
            }
            $active_plugins              = get_option( 'active_plugins', [] );
            $divi_pro_path               = 'timeline-module-pro-for-divi/timeline-module-pro-for-divi.php';
            $timeline_widget_pro_path    = 'timeline-widget-addon-for-elementor-pro/timeline-widget-addon-pro-for-elementor.php';
            $all_plugins                 = get_plugins();
            $is_divi_pro_path            = isset( $all_plugins[ $divi_pro_path ] );
            $is_timeline_widget_pro_path = isset( $all_plugins[ $timeline_widget_pro_path ] );
            // Only call the admin-notice helper if it's available to avoid fatal.
            if ( function_exists( 'ctl_free_create_admin_notice' ) ) {
                $install_nonce = wp_create_nonce( 'twae_install_nonce' );
             
                if ( $this->should_show_divi_notice( $is_tec_settings, $is_divi_pro_path, $active_plugins ) ) {
                    


                    $button_label = esc_html__( 'Install Timeline Module for Divi', 'cool-timeline' );

                    $message_text = sprintf(
                        /* translators: 1: opening strong tag, 2: closing strong tag, 3: opening link tag, 4: closing link tag */
                        __( 'We noticed you’re using %1$sDivi Page Builder%2$s. Try our latest %3$sTimeline Module For Divi%4$s plugin to showcase your life story or %1$scompany history%2$s.', 'cool-timeline' ),
                        '<strong>',
                        '</strong>',
                        '<a href="https://wordpress.org/plugins/timeline-module-for-divi/" target="_blank" rel="noopener noreferrer">',
                        '</a>'
                    );
                    $message_text = wp_kses_post( $message_text );

                    $notice_message = sprintf(
                        '<div class="ctl-new-mkt-notice ctl-divi-notice" style="display:flex !important;">
                            <div style="width:fit-content;">
                                <button type="button" style="padding:2px 10px; margin-right:5px;" class="button button-primary ctl-install-plugin" data-plugin="%1$s" data-nonce="%2$s">%3$s</button>
                            </div>
                            <div>%4$s</div>
                        </div>',
                        esc_attr( 'timeline-divi' ),
                        esc_attr( $install_nonce ),
                        $button_label,
                        $message_text
                    );
                    
                    ctl_free_create_admin_notice(
                        array(
                            'id' => 'ctl-divi-module-notice',
                            'message' => $notice_message,
                            'review_interval' => 3,
                            'plugin_name'     => 'Timeline Module For Divi',
                        )
                    );
                    

                }

                if ( did_action( 'elementor/loaded' ) ) {
                    $old_user_ele_install_notice = get_option( 'dismiss_ele_addon_notice', 'no' );

                    if ( $this->should_show_elementor_notice( $is_tec_settings, $is_timeline_widget_pro_path, $old_user_ele_install_notice ) ) {
                        ctl_free_create_admin_notice(
                            array(
                                'id'      => 'ctl-elementor-addon-notice',
                                'message' => __(
                                    '<div class="ctl-new-mkt-notice" style=" display:flex !important;">
                                        <div style="width:fit-content;">
                                            <a style="padding:2px 10px; margin-right:5px;" class="button button-primary" href="https://cooltimeline.com/plugin/elementor-timeline-widget-pro/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=twea_inside_notice" target="_blank">Try it now!</a>
                                        </div>
                                        <div style="width:87%;">We noticed you&rsquo;re using <strong>Elementor Page Builder</strong>. Try our latest <strong><a href="https://cooltimeline.com/plugin/elementor-timeline-widget-pro/?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=twea_inside_notice" target="_blank">Timeline Widget Pro for Elementor</a></strong> plugin to showcase your life story or <strong>company history</strong>.</div>
                                    </div>',
                                    'cool-timeline'
                                ),
                                'review_interval' => 3,
                                'plugin_name'     => 'Timeline Widget Pro for Elementor',
                            )
                        );
                    }
                }

                $this->add_review_notice();
            } // end function_exists check
        }

        private function is_timeline_admin_screen() {
            // Read-only admin screen detection for conditional script enqueue (no state change; nonce not required).
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $admin_page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $post_type  = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';

            return (
                'cool-plugins-timeline-addon' === $admin_page
                || 'cool_timeline' === $post_type
                || 'twae-welcome-page' === $admin_page
                || 'cool_timeline_settings' === $admin_page
                || ( isset( $_SERVER['PHP_SELF'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ) ), 'plugins.php' ) )
            );
        }

        private function enqueue_marketing_script() {
            wp_enqueue_script(
                'ctl-marketing',
                CTL_PLUGIN_URL . 'admin/marketing/ctl-marketing.js',
                array( 'jquery' ),
                CTL_V,
                true
            );
        }

        private function should_show_divi_notice( $is_tec_settings, $is_divi_pro_path, $active_plugins ) {
            return (
                self::is_theme_activate( 'Divi' )
                && $is_tec_settings
                && ! $is_divi_pro_path
                && ! defined( 'TM_DIVI_PRO_V' )
                && ! in_array( 'timeline-module-for-divi/timeline-module-for-divi.php', $active_plugins, true )
            );
        }

        private function should_show_elementor_notice( $is_tec_settings, $is_timeline_widget_pro_path, $old_user_ele_install_notice ) {
            return (
                'no' === $old_user_ele_install_notice
                && $is_tec_settings
                && ! $is_timeline_widget_pro_path
                && ! defined( 'TWAE_PRO_VERSION' )
            );
        }

        private function add_review_notice() {
            ctl_free_create_admin_notice(
                array(
                    'id'              => 'ctl_review_box',
                    'slug'            => 'ctl',
                    'review'          => true,
                    'review_url'      => esc_url(
                        'https://wordpress.org/support/plugin/cool-timeline/reviews/#new-post'
                    ),
                    'plugin_name'     => 'Cool Timeline',
                    'review_interval' => 3,
                )
            );
        }

        public function ctl_install_plugin() {

            check_ajax_referer( 'twae_install_nonce' );

            if ( ! current_user_can( 'install_plugins' ) ) {
                return wp_send_json_error(
                    array(
                        'errorMessage' => __( 'Sorry, you are not allowed to install plugins on this site.', 'cool-timeline' ),
                    )
                );
               
                
            }

            if ( empty( $_POST['slug'] ) ) {
                return wp_send_json_error(
                    array(
                        'slug'         => '',
                        'errorCode'    => 'no_plugin_specified',
                        'errorMessage' => __( 'No plugin specified.','cool-timeline' ),
                    )
                );
                
            }

            $plugin_slug = sanitize_key( wp_unslash( $_POST['slug'] ) );

            	// Only allow installation of known marketing plugins (ignore client-manipulated slugs).
			$allowed_slugs = array(
				'timeline-module-for-divi',
				'timeline-module-pro-for-divi/timeline-module-pro-for-divi.php',
			);
			if ( ! in_array( $plugin_slug, $allowed_slugs, true ) ) {
				return wp_send_json_error( array(
					'slug'         => $plugin_slug,
					'errorCode'    => 'plugin_not_allowed',
					// phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
					'errorMessage' => __( 'This plugin cannot be installed from here.', 'ctl' ),
				));
                
			}

            $status = array(
                'install' => 'plugin',
                'slug'    => $plugin_slug,
            );

            require_once __DIR__ . '/../class-ctl-plugin-installer.php';

            $installer = new CTL_Plugin_Installer();
            $result    = $installer->install_and_activate( $plugin_slug, $status );

            if ( $result['success'] ) {
                wp_send_json_success( $result['data'] );
            }

            return wp_send_json_error( $result['data'] );
        
        }
    }

    Ctl_Marketing_Controllers::get_instance();
}

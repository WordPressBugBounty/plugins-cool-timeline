<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'Ctl_Marketing_Controllers' ) ) {

    class Ctl_Marketing_Controllers {

        private static $instance = null;

        /**
         * ✅ Singleton instance
         */
        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * ✅ Constructor
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

        public function show_marketing_notices(){
           

            $is_tec_settings = (
                ( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === 'cool-plugins-timeline-addon' )
                || ( isset( $_GET['post_type'] ) && sanitize_key( $_GET['post_type'] ) === 'cool_timeline' )
                || ( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === 'twae-welcome-page' )
                || ( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === 'cool_timeline_settings' )
                || ( isset( $_SERVER['PHP_SELF'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ) ), 'plugins.php' ) !== false )

            );

                if ( $is_tec_settings ) {
        // enqueue your marketing.js script here
        wp_enqueue_script(
            'ctl-marketing',
            CTL_PLUGIN_URL . 'admin/marketing/ctl-marketing.js',
            array( 'jquery' ),
            CTL_V,
            true
        );
                 }
                 $active_plugins = get_option( 'active_plugins', [] );
                 $divi_pro_path = 'timeline-module-pro-for-divi/timeline-module-pro-for-divi.php';
                 $Timeline_Widget_pro_path = 'timeline-widget-addon-for-elementor-pro/timeline-widget-addon-pro-for-elementor.php';
                $all_plugins = get_plugins();
                $is_divi_pro_path = isset($all_plugins[$divi_pro_path]);
                $is_Timeline_Widget_pro_path =isset($all_plugins[$Timeline_Widget_pro_path]);
            // Only call the admin-notice helper if it's available to avoid fatal.
            if ( function_exists( 'ctl_free_create_admin_notice' ) ) {
                 $nonce  = esc_attr( wp_create_nonce( 'twae_install_nonce' ) );
             
                if ( self::is_theme_activate( 'Divi' ) && $is_tec_settings && !$is_divi_pro_path  && !defined('TM_DIVI_PRO_V') && !in_array('timeline-module-for-divi/timeline-module-for-divi.php', $active_plugins, true) ) {
                    
                    ctl_free_create_admin_notice(

                        
                        array(
                            'id'              => 'ctl-divi-module-notice',
                            'message'         => __(
                                '<div class="ctl-new-mkt-notice ctl-divi-notice" style=" display:flex !important;">
                                    <div style="width:fit-content;">
                                      <button style="padding:2px 10px; margin-right:5px;"
                                         class="button button-primary ctl-install-plugin"
                                         data-plugin="timeline-divi"
                                      data-nonce="' . $nonce . '">    Install Timeline Module for Divi
                                     </button>
                                    </div>
                                    <div>We noticed you&rsquo;re using <strong>Divi Page Builder</strong>. Try our latest <a href="https://wordpress.org/plugins/timeline-module-for-divi/" target="_blank"><strong> Timeline Module For Divi</strong></a> plugin to showcase your life story or <strong>company history</strong>.</div>
                                </div>',
                                'cool-timeline'
                            ),
                            'review_interval' => 3,
                            'plugin_name'     => 'Timeline Module For Divi',
                        )
                    );
                }

                if ( did_action( 'elementor/loaded' ) ) {
                    $old_user_ele_install_notice = get_option( 'dismiss_ele_addon_notice', 'no' );

                    if ( $old_user_ele_install_notice === 'no' && $is_tec_settings && !$is_Timeline_Widget_pro_path && !defined( 'TWAE_PRO_VERSION' ) ) {
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

                // Plugin review notice file
                ctl_free_create_admin_notice(
                    array(
                        'id'              => 'ctl_review_box',
                        'slug'            => 'ctl',
                        'review'          => true,
                        'review_url'      => esc_url( 'https://wordpress.org/support/plugin/cool-timeline/reviews/?filter=5#new-post' ),
                        'plugin_name'     => 'Cool Timeline',
                        'review_interval' => 3,
                    )
                );
            } // end function_exists check
        }

        public function ctl_install_plugin() {

            if ( ! current_user_can( 'install_plugins' ) ) {
                $status['errorMessage'] = __( 'Sorry, you are not allowed to install plugins on this site.' );
                wp_send_json_error( $status );
            }

            check_ajax_referer( 'twae_install_nonce' );

            if ( empty( $_POST['slug'] ) ) {
                wp_send_json_error(
                    array(
                        'slug'         => '',
                        'errorCode'    => 'no_plugin_specified',
                        'errorMessage' => __( 'No plugin specified.' ),
                    )
                );
            }

            $plugin_slug = sanitize_key( wp_unslash( $_POST['slug'] ) );

            $status = array(
                'install' => 'plugin',
                'slug'    => $plugin_slug,
            );

            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';  
        
            $api = plugins_api(
                'plugin_information',
                array(
                    'slug'   => $plugin_slug,
                    'fields' => array(
                        'sections' => false,
                    ),
                )
            );

            if ( is_wp_error( $api ) ) {
                $status['errorMessage'] = $api->get_error_message();
                wp_send_json_error( $status );
            }

            $status['pluginName'] = $api->name;

            $skin     = new WP_Ajax_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader( $skin );
            $result   = $upgrader->install( $api->download_link );

            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                $status['debug'] = $skin->get_upgrade_messages();
            }

            if ( is_wp_error( $result ) ) {

                $status['errorCode']    = $result->get_error_code();
                $status['errorMessage'] = $result->get_error_message();
                wp_send_json_error( $status );

            } elseif ( is_wp_error( $skin->result ) ) {

                if ( $skin->result->get_error_message() === 'Destination folder already exists.' ) {

                    $install_status = install_plugin_install_status( $api );
                    $pagenow        = isset( $_POST['pagenow'] ) ? sanitize_key( $_POST['pagenow'] ) : '';

                    if ( current_user_can( 'activate_plugin', $install_status['file'] ) ) {

                        $network_wide      = ( is_multisite() && 'import' !== $pagenow );
                        $activation_result = activate_plugin( $install_status['file'], '', $network_wide );

                        if ( is_wp_error( $activation_result ) ) {

                            $status['errorCode']    = $activation_result->get_error_code();
                            $status['errorMessage'] = $activation_result->get_error_message();
                            wp_send_json_error( $status );

                        } else {

                            $status['activated'] = true;

                        }
                        wp_send_json_success( $status );
                    }
                } else {

                    $status['errorCode']    = $skin->result->get_error_code();
                    $status['errorMessage'] = $skin->result->get_error_message();
                    wp_send_json_error( $status );
                }

            } elseif ( $skin->get_errors()->has_errors() ) {

                $status['errorMessage'] = $skin->get_error_messages();
                wp_send_json_error( $status );

            } elseif ( is_null( $result ) ) {

                global $wp_filesystem;

                $status['errorCode']    = 'unable_to_connect_to_filesystem';
                $status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

                if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
                    $status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
                }

                wp_send_json_error( $status );
            }

            $install_status = install_plugin_install_status( $api );
            $pagenow        = isset( $_POST['pagenow'] ) ? sanitize_key( $_POST['pagenow'] ) : '';

            // 🔄 Auto-activate the plugin right after successful install
            if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {

                $network_wide      = ( is_multisite() && 'import' !== $pagenow );
                $activation_result = activate_plugin( $install_status['file'], '', $network_wide );

                if ( is_wp_error( $activation_result ) ) {
                    $status['errorCode']    = $activation_result->get_error_code();
                    $status['errorMessage'] = $activation_result->get_error_message();
                    wp_send_json_error( $status );
                } else {
                    $status['activated'] = true;
                }
            }

            wp_send_json_success( $status );
        
        }
    }

    Ctl_Marketing_Controllers::get_instance();
}

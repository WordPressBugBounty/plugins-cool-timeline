<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Admin notice class for wordpress plugin.
 * This class can not be initialized or extended.
 */

/**************************************************************************************************
 *  HOW TO USE.
 * After including this file, use the below example to start creating admin notice / review box
 *
 * Two arguments, id & message are required and can not be ommitied.
 * id must be unique for every message or it will override the previous message with same id.
 * 
 *               create a simple admin text message
 *   ctl_free_create_admin_notice( array('id'=>'bp-greeting-mesage','message'=>'Hey there!') );
 * 
 *              create a admin text error message
 * ctl_free_create_admin_notice( array('id'=>'bp-error-mesage','message'=>'this is an example of error!','type'=>'error') );
 * The argument 'type' can be: error, success, warning
 * 
 *              create a review box by passing minimum arguments
 * $slug = 'bp';
 * update_option($slug . '_activation_time,strtotime('now') ); // must create an activation time 
 * ctl_free_create_admin_notice( 
 *          array(
 *              'id'=>'bp_review_box',  // required and must be unique
 *              'slug'=>$slug,      // required in case of review box
 *              'review'=>true,     // required and set to be true for review box
 *              'review_url'=>'http://coolplugins.net', // required
 *              'plugin_name'=>'Boiler Plate Plugin',    // required
 *              'logo'=>'http://example.com/logo.png',   // optional: it will display logo
 *              'review_interval'=>5                    // optional: this will display review notice
 *                                                      //   after 5 days from the installation_time
 *                                                      // default is 3
 *          )
 * );
 * 
 * NOTE: Review box does not be displayed unless the $slug _activation_time is equals or
 * more than the 3 days from current time. This can also be changed by setting 'review_interval' arguments
 ***************************************************************************************************** 
 */
if (!class_exists('ctl_admin_notices')):

    final class ctl_admin_notices{

        private static $instance = null;
        private $messages = array();
        private $hooks_registered = false;

        /**
         * initialize the class with single instance
         */
        public static function ctl_create_notice(){
            if (!empty(self::$instance)) {
                return self::$instance;
            }
            return self::$instance = new self;
        }

        /**
         * add messages for admin notice
         * @param array $notice this array contains $id,$message,$type,$class,$id
         *
         */
        public function ctl_add_message($notice){
            if( !isset( $notice['id']) || empty($notice['id']) ){
                $this->ctl_show_error('id is required for integrating admin notice.');
                return;
            }

            if ( isset($notice['review']) && true != (bool)$notice['review'] && ( !isset($notice['message']) || empty($notice['message']) )) {
                $this->ctl_show_error('message can not be null. You must provide some text for message field');
                return;
            }
            $message = ( isset( $notice['message'] ) && ! empty( $notice['message'] ) ) ? $notice['message'] : null;
            $type = (isset($notice['type']) && !empty($notice['type'])) ? 'notice-' . sanitize_text_field( $notice['type'] ) : 'notice-info' ;
            $class = (isset($notice['class']) && !empty($notice['class'])) ? sanitize_text_field( $notice['class'] ): '';
            $review = (bool)(isset($notice['review'] ) && !empty( $notice['review'] ) ) ? sanitize_text_field( $notice['review'] ) : false;
            $slug = (isset($notice['slug']) && !empty($notice['slug'])) ? sanitize_text_field( $notice['slug'] ): '' ;
            $plugin_name = (isset($notice['plugin_name']) && !empty($notice['plugin_name'])) ? sanitize_text_field( $notice['plugin_name'] ) : '' ;
            $logo = (isset($notice['logo']) && !empty($notice['logo'])) ? esc_url( $notice['logo'] ) : null ;
            $review_url = (isset($notice['review_url']) && !empty($notice['review_url'])) ? esc_url( $notice['review_url'] ) : '' ;
            $review_interval = (isset($notice['review_interval']) && !empty($notice['review_interval'])) ? sanitize_text_field( $notice['review_interval'] ) : '3' ;
            if( $review == true && ( empty( $slug ) || empty( $plugin_name ) || empty( $review_url ) )){
                $this->ctl_show_error( 'slug / plugin_name / review_url can not be empty if admin notice is set to review' );
                return;
            }
            $this->messages[$notice['id']] = array(
                                            'message' => $message,
                                            'type' => $type,
                                            'class' => $class,
                                            'review' => $review,
                                            'logo'=>$logo,
                                            'slug' => $slug,
                                            'plugin_name' => $plugin_name,
                                            'review_url' => $review_url,
                                            'review_interval' => $review_interval
                                        );

            if ( ! $this->hooks_registered ) {
                if ( ! $this->has_renderable_notice() ) {
                    return;
                }
                $this->register_notice_hook();
                add_action( 'admin_enqueue_scripts', array($this, 'ctl_load_script' ) );
                add_action('wp_ajax_ctl_admin_notice_dismiss', array($this, 'ctl_admin_notice_dismiss'));
                add_action('wp_ajax_ctl_admin_review_notice_dismiss', array($this, 'ctl_admin_review_notice_dismiss'));
                $this->hooks_registered = true;
            }

        }

        /**
         * Register the notice-render hook once per request.
         */
        private function register_notice_hook() {
            // On Timeline Addon pages, show notices after the timeline header (not above it).
            if ( function_exists( 'ctl_is_timeline_addon_page' ) && ctl_is_timeline_addon_page() ) {
                add_action( 'ctl_after_timeline_header', array( $this, 'ctl_show_notice' ), 10 );
            } else {
                add_action( 'admin_notices', array( $this, 'ctl_show_notice' ) );
            }
        }

        /**
    	 * Load script to dismiss notices.
    	 *
    	 * @return void
    	 */
    	public function ctl_load_script() {
            if ( ! $this->has_renderable_notice() ) {
                return;
            }

            wp_register_style( 'ctl-feedback-notice-styles', CTL_PLUGIN_URL.'assets/css/ctl-admin-notices.css',array(),CTL_V,'all' );
            wp_register_script( 'admin-notices-js', CTL_PLUGIN_URL . 'admin/notices/admin-notices.js', array( 'jquery' ), CTL_V, true );
             wp_enqueue_style( 'ctl-feedback-notice-styles' );
            wp_enqueue_script( 'admin-notices-js' );

            
                if (!empty($this->messages)) {
                    foreach ($this->messages as $id => $message) {
                        if ( ! $this->can_render_notice( $id, $message ) ) {
                            continue;
                        }

                        $nonce = $message['review']
                            ? wp_create_nonce($id . '_review_nonce')
                            : wp_create_nonce($id . '_notice_nonce');

                    $localized_data = array(
                'id'            => $id,
                'ajax_url'      => admin_url('admin-ajax.php'),
                'wp_nonce'      => $nonce,
                'plugin_slug'   => $message['slug'] ?? $id,
                'review'        => $message['review'],
                'ajax_callback' => $message['review']
                                    ? 'ctl_admin_review_notice_dismiss'
                                    : 'ctl_admin_notice_dismiss',
            );
                        $js_safe_id = str_replace('-', '_', $id);

                        wp_localize_script('admin-notices-js', 'CtlNoticeData_' . $js_safe_id, $localized_data);
                    }
            }
        
        
        }

        /**
         * Check whether any registered notice can render on this request.
         *
         * @return bool
         */
        private function has_renderable_notice() {
            if ( empty( $this->messages ) ) {
                return false;
            }

            foreach ( $this->messages as $id => $message ) {
                if ( $this->can_render_notice( $id, $message ) ) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Check whether a notice is eligible to render.
         *
         * @param string $id      Notice ID.
         * @param array  $message Notice config.
         * @return bool
         */
        private function can_render_notice( $id, $message ) {
            if ( ! empty( $message['review'] ) ) {
                return $this->can_render_review_notice( $message );
            }

            return ! get_option( $id . '_remove_notice' );
        }

        /**
         * Check whether a review notice is eligible to render.
         *
         * @param array $messageObj Notice config.
         * @return bool
         */
        private function can_render_review_notice( $messageObj ) {
            if ( ! current_user_can( 'update_plugins' ) ) {
                return false;
            }

            if ( ! get_option( 'cool-timelne-installDate' ) ) {
                return false;
            }

            $old_alreadyRated = get_option( 'cool-timelne-ratingDiv' ) !== false ? get_option( 'cool-timelne-ratingDiv' ) : 'no';
            $alreadyRated     = get_option( 'cool-timeline-already-rated' ) !== false ? get_option( 'cool-timeline-already-rated' ) : 'no';

            if ( $old_alreadyRated === 'yes' || $alreadyRated === 'yes' ) {
                return false;
            }

            $installation_date = gmdate( 'Y-m-d h:i:s', strtotime( get_option( 'cool-timelne-installDate' ) ) );
            $install_date      = new DateTime( $installation_date );
            $current_date      = new DateTime( gmdate( 'Y-m-d h:i:s' ) );
            $difference        = $install_date->diff( $current_date );
            $days              = $messageObj['review_interval'];

            return isset( $difference->days ) && $difference->days >= $days;
        }

        /**
         * Create simple admin notice
         */
        public function ctl_show_notice(){
            if (count($this->messages) > 0) {
                
                foreach ($this->messages as $id => $message) {
                    if ( ! empty( $message['review'] ) ) {
                        $this->ctl_admin_notice_for_review( $id, $message);
                    }else{
                        $this->ctl_simple_notice($id, $message );
                    }
                }
            }
        }

        /**
         * Due to the nature of private function. This must not be called dirctlly
         * Create simple text/html admin notice and initialize required JS
         * @param array $message This is an array of message objctl
         */
        private function ctl_simple_notice($id, $message ){

            if( get_option($id . '_remove_notice') ) return;
            
            $classes = 'notice ' . trim( $message['type'] ) . ' is-dismissible ' . trim( $message['class'] );
            $nonce = wp_create_nonce( $id . '_notice_nonce' );
            $logo_container_link_href = "";
            switch($message['plugin_name']){
                case 'Timeline Widget Pro for Elementor':
                    $logo_container_link_href = 'https://wordpress.org/plugins/timeline-widget-addon-for-elementor';
                    break;
                case 'Timeline Module For Divi':
                    $logo_container_link_href = 'https://wordpress.org/plugins/timeline-module-for-divi/';
                    break;
                default:
                    $logo_container_link_href = 'https://wordpress.org/plugins/cool-timeline/';
            }      

            $img_path= ( isset( $message['logo'] ) && !empty($message['logo'] ) ) ? $message['logo'] : null;
            if( $img_path != null ){
                $image_html ='<div class="logo_container"><a href="'.esc_attr($logo_container_link_href).'"><img src="'.esc_url($img_path).'" style="max-width:70px;"></a></div>';
            }
            else{
                $image_html ='';
            }
            echo "<div class='".esc_attr($id)."_admin_notice ".esc_attr($classes)."
             ctl-simple-notice' data-ajax-url='".esc_url(admin_url('admin-ajax.php'))."'
              data-wp-nonce='". esc_attr($nonce) . "' 
              data-plugin-slug='".esc_attr($id)."'>".wp_kses_post($image_html)."<div class='message_container'>
              <span>" . wp_kses_post($message['message']) . "</span></div></div>";
        }

        /**
         * This function decides if its good to show the review notice or not
         * Review notice will only be displayed if $slug_activation_time is greater or equals to the 3 days
         */
        private function ctl_admin_notice_for_review( $id, $messageObj ){
            if ( ! $this->can_render_review_notice( $messageObj ) ) {
                return;
            }

            echo wp_kses_post( $this->ctl_create_notice_content( $id, $messageObj ) );
        }

        /**
         * Generate review notice HTMl with all required css & js
         *
         * @param array $messageObj array of a message objctl 
         **/ 
       function ctl_create_notice_content( $id, $messageObj ){

        $ajax_url=admin_url( 'admin-ajax.php' );
        $ajax_callback = 'ctl_admin_review_notice_dismiss';
        $wrap_cls="notice notice-info is-dismissible";
        $slug = $messageObj['slug'];
        $plugin_name= $messageObj['plugin_name'];
        $like_it_text='Rate Now! ★★★★★';
        $already_rated_text=esc_html__( 'Already Reviewed', 'cool-timeline' );
        $not_like_it_text=esc_html__( 'Not Interested', 'cool-timeline' );
        $plugin_link=  $messageObj['review_url'] ;
        $review_nonce = wp_create_nonce( $id . '_review_nonce' ); 
        $message="Thanks for using <b>".esc_html($plugin_name)."</b> - WordPress plugin.
        We hope you liked it ! <br/>Please give us a quick rating, it works as a boost for us to keep working on more <a href='https://coolplugins.net' target='_blank'><strong>Cool Plugins</strong></a>!<br/>";
      
            $html='<div data-ajax-url="%7$s"
            data-plugin-slug="%10$s"
            data-wp-nonce="%11$s"
            id="%12$s"
            data-ajax-callback="%8$s"
            class="%10$s-feedback-notice-wrapper %1$s">';
    

        $html .='<div class="message_container">%3$s
        <div class="callto_action">
        <ul>
            <li class="love_it">
                <a href="%4$s" class="like_it_btn button button-primary" target="_new" title="%5$s">%5$s</a>
            </li>
        
            <li class="already_rated">
                <a href="javascript:void(0);" class="already_rated_btn button %10$s_dismiss_notice" title="%6$s">%6$s</a>
            </li>  
        
            <li class="already_rated">
                <a href="javascript:void(0);" class="already_rated_btn button %10$s_dismiss_notice" title="%9$s">%9$s</a>
            </li>    
        </ul>
        
        <div class="clrfix"></div>
        </div>
        </div>
        </div>';
  
        return sprintf(
                $html,
                esc_attr( $wrap_cls ),
                esc_attr( $plugin_name ),
                wp_kses_post( $message ),
                esc_url( $plugin_link ),
                esc_attr( $like_it_text ),
                esc_attr( $already_rated_text ),
                esc_url( $ajax_url ),// 7
                esc_attr( $ajax_callback ),//8
                esc_attr( $not_like_it_text ),//9
                esc_attr( $slug ), //10
                esc_attr( $review_nonce ), //13
                esc_attr( $id ) //12
        );
        
       }

       /**
        * This function will dismiss the review notice.
        * This is called by a wordpress ajax hook
        */
        public function ctl_admin_review_notice_dismiss(){
            // Check user capabilities
            if ( ! current_user_can( 'manage_options' ) ) {
                return wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'cool-timeline' ) ) );
             
                
            }

            $slug = isset( $_REQUEST['slug'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['slug'] ) ) : '';
            $id = isset( $_REQUEST['id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : ''; 
            $nonce_key = $id . '_review_nonce' ;

            if ( isset( $_REQUEST['_nonce'] ) && ! empty( $_REQUEST['_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_nonce'] ) ), $nonce_key ) ) {
                update_option( 'cool-timeline-already-rated','yes' );
                wp_send_json_success( array( 'success' => 'true' ) );
            }else{
                return wp_send_json_error( array( 'error' => 'nonce verification failed!' ) );
                
            }
        }

        /************************************************************
         * This function will dismiss the text/html admin notice    *
         * This is called by a wordpress ajax hook                  *
         ************************************************************/
        public function ctl_admin_notice_dismiss(){
            // Check user capabilities
            if ( ! current_user_can( 'manage_options' ) ) {
                return wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'cool-timeline' ) ) );
                
               
            }
           
            $id = isset( $_REQUEST['id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : ''; 

            if ( empty( $id ) || ! array_key_exists( $id, $this->messages ) ) {
                return wp_send_json_error( array( 'message' => __( 'Invalid notice ID!', 'cool-timeline' ) ) );
                
            }

            $wp_nonce = $id . '_notice_nonce';

            if ( isset( $_REQUEST['_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_nonce'] ) ), $wp_nonce ) ) {
                update_option( $id . '_remove_notice','yes' );
                wp_send_json_success( array( 'message' => __( 'Admin message removed!', 'cool-timeline' ) ) );
            } else {
                return wp_send_json_error( array( 'message' => __( 'Nonce verification failed!', 'cool-timeline' ) ) );
                
            }

        }

        /**************************************************************
         * This function is used by the class for displaying error    *
         *  in case of wrong implementation of the class.             *
         **************************************************************/
        private function ctl_show_error($error_text){
            $er = "<div style='text-align:center;margin-left:20px;padding:10px;background-color: #cc0000; color: #fce94f; font-size: x-large;'>";
            $er .= "Error: ".esc_html($error_text);
            $er .= "</div>";
            echo wp_kses_post($er);
        }

    }   // end of main class ctl_admin_notices;
        endif;
    /********************************************************************************
     * A global function to create admin notice/review box using the above class.   *
     * This function makes it easy to use above class                               *
     ********************************************************************************/
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
    function ctl_free_create_admin_notice($notice){
        // Do not initialize anything if it's not wordpress admin dashboard
        if (!is_admin()) {
            return;
        }
        
        $main_class = ctl_admin_notices::ctl_create_notice();
        $main_class->ctl_add_message($notice);
        return $main_class;
    }

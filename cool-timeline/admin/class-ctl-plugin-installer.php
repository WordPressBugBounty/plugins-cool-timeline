<?php
/**
 * Shared plugin install/activation service.
 *
 * @package CoolTimeline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CTL_Plugin_Installer' ) ) {
	/**
	 * Installs WordPress.org plugins and activates existing plugin files.
	 */
	class CTL_Plugin_Installer {

		/**
		 * Install a plugin from WordPress.org and activate it when possible.
		 *
		 * @param string $slug   WordPress.org plugin slug.
		 * @param array  $status Base response payload.
		 * @return array{success:bool,data:array}
		 */
		public function install_and_activate( $slug, $status = array() ) {
			if(!current_user_can('install_plugins')){
				return $this->error( array( 'message' => __( 'Permission denied', 'cool-timeline' ) ) );
			}

			$this->load_dependencies();

			$status = array_merge(
				array(
					'install' => 'plugin',
					'slug'    => $slug,
				),
				$status
			);

			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array(
						'sections' => false,
					),
				)
			);

			if ( is_wp_error( $api ) ) {
				$status['errorMessage'] = $api->get_error_message();
				return $this->error( $status );
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
				return $this->error( $status );
			}

			if ( is_wp_error( $skin->result ) ) {
				if ( 'Destination folder already exists.' === $skin->result->get_error_message() ) {
					return $this->activate_installed_plugin( $api, $status );
				}

				$status['errorCode']    = $skin->result->get_error_code();
				$status['errorMessage'] = $skin->result->get_error_message();
				return $this->error( $status );
			}

			if ( $skin->get_errors()->has_errors() ) {
				$status['errorMessage'] = $skin->get_error_messages();
				return $this->error( $status );
			}

			if ( is_null( $result ) ) {
				global $wp_filesystem;

				$status['errorCode']    = 'unable_to_connect_to_filesystem';
				$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'cool-timeline' );

				if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
					$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
				}

				return $this->error( $status );
			}

			return $this->activate_installed_plugin( $api, $status );
		}

		/**
		 * Activate an already installed plugin file.
		 *
		 * @param string $plugin_file Plugin basename.
		 * @param string $slug        Plugin slug for response payload.
		 * @return array{success:bool,data:array}
		 */
		public function activate_plugin_file( $plugin_file, $slug ) {
			$this->load_dependencies();

			if ( ! current_user_can( 'activate_plugin', $plugin_file ) ) {
				return $this->error( array( 'message' => __( 'Permission denied', 'cool-timeline' ) ) );
			}

			$activation_result = activate_plugin( $plugin_file, '', $this->is_network_wide_request() );
			if ( is_wp_error( $activation_result ) ) {
				return $this->error( array( 'message' => $activation_result->get_error_message() ) );
			}

			return $this->success(
				array(
					'message'     => __( 'Plugin activated successfully', 'cool-timeline' ),
					'activated'   => true,
					'plugin_slug' => $slug,
				)
			);
		}

		/**
		 * Activate plugin reported by install_plugin_install_status().
		 *
		 * @param object $api    Plugin API response.
		 * @param array  $status Response payload.
		 * @return array{success:bool,data:array}
		 */
		private function activate_installed_plugin( $api, $status ) {
			$install_status = install_plugin_install_status( $api );

			if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {
				$activation_result = activate_plugin( $install_status['file'], '', $this->is_network_wide_request() );
				if ( is_wp_error( $activation_result ) ) {
					$status['errorCode']    = $activation_result->get_error_code();
					$status['errorMessage'] = $activation_result->get_error_message();
					return $this->error( $status );
				}

				$status['activated'] = true;
			}

			return $this->success( $status );
		}

		/**
		 * Load WordPress plugin installer dependencies.
		 */
		private function load_dependencies() {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		/**
		 * Determine whether activation should be network-wide.
		 *
		 * @return bool
		 */
		private function is_network_wide_request() {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is checked in AJAX handlers.
			$pagenow = isset( $_POST['pagenow'] ) ? sanitize_key( wp_unslash( $_POST['pagenow'] ) ) : '';

			return is_multisite() && 'import' !== $pagenow;
		}

		/**
		 * Build a success result.
		 *
		 * @param array $data Response data.
		 * @return array{success:bool,data:array}
		 */
		private function success( $data ) {
			return array(
				'success' => true,
				'data'    => $data,
			);
		}

		/**
		 * Build an error result.
		 *
		 * @param array $data Response data.
		 * @return array{success:bool,data:array}
		 */
		private function error( $data ) {
			return array(
				'success' => false,
				'data'    => $data,
			);
		}
	}
}

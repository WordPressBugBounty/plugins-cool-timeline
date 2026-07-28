<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
class CTL_free_migrations {


	/**
	 * Constructor.
	 */
	public function __construct() {
		
		add_action( 'admin_init', array( $this, 'ctl_postmeta_migration' ) );
		add_action( 'admin_init', array( $this, 'ctl_settings_migration' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'ctl_enqueue_migration_assets' ) );
		add_action( 'wp_ajax_ctl_migrate_stories', array( $this, 'ctl_migrate_stories' ) );
	}

	/**
	 * Enqueue Timeline Express migration UI script on plugin settings.
	 *
	 * @return void
	 */
	public function ctl_enqueue_migration_assets() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen detection.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'cool_timeline_settings' !== $page ) {
			return;
		}

		if ( ! defined( 'CTL_PLUGIN_URL' ) ) {
			return;
		}

		wp_enqueue_script(
			'ctl-migration-js',
			CTL_PLUGIN_URL . 'admin/assets/js/migration.js',
			array( 'jquery' ),
			defined( 'CTL_V' ) ? CTL_V : '1.0',
			true
		);

		wp_localize_script(
			'ctl-migration-js',
			'ctl_migration',
			array(
				'nonce'        => wp_create_nonce( 'ctl_migrate_nonce' ),
				'redirect_url' => esc_url( admin_url( 'edit.php?post_type=cool_timeline' ) ),
				'ajax_url'     => admin_url( 'admin-ajax.php' ),
			)
		);
	}
	
	function ctl_postmeta_migration() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		
		if ( get_option( 'ctl-postmeta-migration' ) ) {
			return;
		}
		
		if ( version_compare( get_option( 'cool-free-timeline-v' ), '2.1', '>' ) && ! ( get_option( 'cool-timelne-v' ) ) ) {
			return;
		}

		$args  = array(
			'post_type'   => 'cool_timeline',
			'post_status' => array( 'publish', 'future' ),
			'numberposts' => -1,
		);
		$posts = get_posts( $args );

		
		$story_type_key = array(
			'ctl_story_date',
		);
		
		$story_media_key = array(
			'img_cont_size',
		);
		$story_icon_key  = array(
			'fa_field_icon',
		);

		if ( isset( $posts ) && is_array( $posts ) && ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				
				$post_id = intval( $post->ID );
				$array_icon_type  = array(
					'story_icon_type' => 'fontawesome',
				);
				$array_story_type = array(
					'story_based_on' => 'default',
				);
				$array_story_media = array(
					'story_format' => 'default',
				);

				foreach ( $story_icon_key as $item ) {
					$item_value               = sanitize_text_field( get_post_meta( $post_id, $item, true ) );
					$array_icon_type[ $item ] = $item_value;
				}

				foreach ( $story_type_key as $item ) {
					$item_value                = sanitize_text_field( get_post_meta( $post_id, $item, true ) );
					$array_story_type[ $item ] = $item_value;
				}

				foreach ( $story_media_key as $item ) {
					$item_value                 = sanitize_text_field( get_post_meta( $post_id, $item, true ) );
					$array_story_media[ $item ] = $item_value;
				}

				update_post_meta( $post_id, 'story_type', $array_story_type );
				update_post_meta( $post_id, 'story_media', $array_story_media );
				update_post_meta( $post_id, 'story_icon', $array_icon_type );
			}

			update_option( 'ctl-postmeta-migration', 'done' );
		}
	}


	function ctl_settings_migration() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! get_option( 'cool_timeline_options' ) ) {
			return;
		}

		$old_settings = get_option( 'cool_timeline_options' );

		if ( ! is_array( $old_settings ) ) {
			return;
		}

		$old_settings = map_deep( $old_settings, 'sanitize_text_field' );

		$new_settings = $this->ctl_save_settings(
			$old_settings,
			array(
				'face'   => 'font-family',
				'size'   => 'font-size',
				'weight' => 'font-weight',
				'src'    => 'url',
			)
		);

		$new_settings = map_deep( $new_settings, 'sanitize_text_field' );

		update_option( 'cool_timeline_settings', $new_settings );
		update_option( 'ctl_settings_migration_status', 'done' );
		delete_option( 'cool_timeline_options' );
	}

	function ctl_recursive_change_key( $arr, $set ) {
		if ( is_array( $arr ) && is_array( $set ) ) {
			$newArr = array();
			foreach ( $arr as $k => $v ) {
				$key            = array_key_exists( $k, $set ) ? $set[ $k ] : $k;
				$newArr[ $key ] = is_array( $v ) ? $this->ctl_recursive_change_key( $v, $set ) : $v;
				if ( 'font-size' === $key ) {
					$newArr[ $key ] = str_replace( 'px', '', $v );
				}
			}

			return $newArr;
		}
		return $arr;
	}

	function ctl_save_settings( $arr, $set ) {
		if ( is_array( $arr ) && is_array( $set ) ) {
			$newArr                 = array();
			$timeline_header        = array();
			$story_date_settings    = array();
			$story_content_settings = array();

			$timeline_header_key = array( 'title_text', 'user_avatar' );

			$story_date_settings_key    = array( 'year_label_visibility' );
			$story_content_settings_key = array( 'content_length', 'display_readmore' );
			$arr                        = $this->ctl_recursive_change_key( $arr, $set );
			foreach ( $arr as $key => $value ) {
				if ( in_array( $key, $timeline_header_key, true ) ) {
					if ( $key === 'user_avatar' ) {
						if ( ! empty( $value ) ) {
							$value            = $this->ctl_recursive_change_key( $value, array( 'src' => 'url' ) );
							$thumbnail_img    = wp_get_attachment_image_src( $value['id'], 'thumbnail' );
							$value           += array(
								'thumbnail' => $thumbnail_img[0],
								'width'     => '843',
								'height'    => '450',
							);
							$timeline_header += array( $key => $value );
						}
					} else {
						$timeline_header += array( $key => $value );
					}
				} elseif ( in_array( $key, $story_date_settings_key, true ) ) {
					$story_date_settings += array( $key => $value );
				} elseif ( in_array( $key, $story_content_settings_key, true ) ) {
					$story_content_settings += array( $key => $value );
				} elseif ( $key === 'main_title_typo' ) {
					$title_alignment           = isset( $arr['title_alignment'] ) ? $arr['title_alignment'] : 'center';
					$value                    += array(
						'text-align' => $title_alignment,
						'type'       => 'google',
					);
					$newArr['main_title_typo'] = $value;
				} elseif ( $key === 'post_title_text_style' ) {
					$newArr['post_title_typo']['text-transform'] = $value;
				} elseif ( $key === 'background' ) {
					if ( isset( $value['enabled'] ) ) {
						$newArr['timeline_background'] = '1';
						$newArr['timeline_bg_color']   = $value['bg_color'];
					} else {
						$newArr['timeline_background'] = '0';
					}
				} elseif ( $key === 'post_title_typo' ) {
					$value                                 += array( 'type' => 'google' );
					$newArr['ctl_date_typo']['font-family'] = $value['font-family'];
					$newArr['ctl_date_typo']['font-weight'] = $value['font-weight'];
					$newArr['ctl_date_typo']['font-size']   = '21';
					$newArr['ctl_date_typo']['type']        = 'google';
					$newArr['post_title_typo']              = $value;
				} elseif ( $key === 'post_content_typo' ) {
					$value                      += array( 'type' => 'google' );
					$newArr['post_content_typo'] = $value;
				} else {
					$newArr[ $key ] = $value;
				}
			}

			$newArr['timeline_header']        = $timeline_header;
			$newArr['story_date_settings']    = $story_date_settings;
			$newArr['story_content_settings'] = $story_content_settings;
			return $newArr;
		}
		return $arr;
	}

	/**
	 * Migrate data from Timeline Express to Cool Timeline
	 */
	public function migrate_timeline_express_to_cool_timeline() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( get_option( 'timeline_express_migrated' ) ) {
			return;
		}

		$timeline_express_posts = $this->fetch_timeline_express_stories();
		if ( empty( $timeline_express_posts ) ) {
			return;
		}

		$migrate_stories       = 0;
		$cooltimeline_settings = $this->prepare_timeline_express_settings();

		foreach ( $timeline_express_posts as $old_post ) {
			if ( empty( $old_post->ID ) ) {
				continue;
			}

			$migrate_stories++;
			$this->migrate_timeline_express_story( $old_post );
		}

		update_option( 'timeline_express_migrated', 1 );
		update_option( 'cool_timeline_settings', $cooltimeline_settings );
		return $migrate_stories;
	}

	/**
	 * Fetch published Timeline Express stories.
	 *
	 * @return array
	 */
	private function fetch_timeline_express_stories() {
		$args = array(
			'post_type'      => 'te_announcements',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		return get_posts( $args );
	}

	/**
	 * Map Timeline Express settings into Cool Timeline settings.
	 *
	 * @return array
	 */
	private function prepare_timeline_express_settings() {
		$timeline_settings     = get_option( 'timeline_express_storage' );
		$cooltimeline_settings = (array) get_option( 'cool_timeline_settings', [] );

		if ( ! is_array( $timeline_settings ) ) {
			$timeline_settings = array();
		}

		if ( ! isset( $cooltimeline_settings['story_content_settings'] ) || ! is_array( $cooltimeline_settings['story_content_settings'] ) ) {
			$cooltimeline_settings['story_content_settings'] = array();
		}

		$cooltimeline_settings = array_merge(
			array(
				'story_content_settings' => array(),
				'first_post'             => '',
				'content_bg_color'       => '',
				'line_color'             => '',
			),
			$cooltimeline_settings
		);

		if ( ! empty( $timeline_settings['excerpt-trim-length'] ) ) {
			$cooltimeline_settings['story_content_settings']['content_length'] = (string) (int) $timeline_settings['excerpt-trim-length'];
		}

		if ( isset( $timeline_settings['read-more-visibility'] ) ) {
			$cooltimeline_settings['story_content_settings']['display_readmore'] = $timeline_settings['read-more-visibility'] === '1' ? 'yes' : 'no';
		}

		if ( isset( $timeline_settings['default-announcement-color'] ) ) {
			$default_announcement_color = sanitize_hex_color( $timeline_settings['default-announcement-color'] );
			if ( $default_announcement_color ) {
				$cooltimeline_settings['first_post'] = $default_announcement_color;
			}
		}

		if ( isset( $timeline_settings['announcement-bg-color'] ) ) {
			$announcement_bg_color = sanitize_hex_color( $timeline_settings['announcement-bg-color'] );
			if ( $announcement_bg_color ) {
				$cooltimeline_settings['content_bg_color'] = $announcement_bg_color;
			}
		}
		
		if ( isset( $timeline_settings['announcement-background-line-color'] ) ) {
			$announcement_line_color = sanitize_hex_color( $timeline_settings['announcement-background-line-color'] );
			if ( $announcement_line_color ) {
				$cooltimeline_settings['line_color'] = $announcement_line_color;
			}
		}

		return $cooltimeline_settings;
	}

	/**
	 * Migrate one Timeline Express story into a Cool Timeline story.
	 *
	 * @param WP_Post $old_post Timeline Express post.
	 */
	private function migrate_timeline_express_story( $old_post ) {
		$story_meta = $this->map_timeline_express_story_meta( $old_post );
		$new_post   = array(
			'post_title'   => sanitize_text_field( $old_post->post_title ),
			'post_content' => wp_kses_post( $old_post->post_content ),
			'post_excerpt' => $story_meta['excerpt'],
			'post_type'    => 'cool_timeline',
			'post_status'  => $old_post->post_status,
			'post_date'    => $old_post->post_date,
			'post_name'    => sanitize_title( $old_post->post_title ),
		);

		$new_post_id = wp_insert_post( $new_post );

		if ( is_wp_error( $new_post_id ) || $new_post_id <= 0 ) {
			return;
		}

		$this->save_timeline_express_story_meta( $new_post_id, $story_meta );
	}

	/**
	 * Map Timeline Express post meta to sanitized Cool Timeline meta values.
	 *
	 * @param WP_Post $old_post Timeline Express post.
	 * @return array
	 */
	private function map_timeline_express_story_meta( $old_post ) {
		$event_timestamp = intval( get_post_meta( $old_post->ID, 'announcement_date', true ) );
		$icon_raw        = get_post_meta( $old_post->ID, 'announcement_icon', true );
		$color_raw       = get_post_meta( $old_post->ID, 'announcement_color', true );

		if ( strpos( $icon_raw, 'fa-' ) === false ) {
			$icon_class = 'fa fa-' . sanitize_html_class( $icon_raw );
		} else {
			$icon_class = 'fa ' . sanitize_html_class( $icon_raw );
		}

		return array(
			'attachment_id'      => intval( get_post_meta( $old_post->ID, 'announcement_image_id', true ) ),
			'color'              => sanitize_hex_color( $color_raw ),
			'event_timestamp'    => $event_timestamp,
			'excerpt'            => wp_kses_post( get_post_meta( $old_post->ID, 'announcement_custom_excerpt', true ) ),
			'formatted_for_meta' => $event_timestamp ? gmdate( 'm/d/Y h:i A', $event_timestamp ) : '',
			'icon_class'         => $icon_class,
		);
	}

	/**
	 * Save migrated story metadata on the new Cool Timeline post.
	 *
	 * @param int   $new_post_id New Cool Timeline post ID.
	 * @param array $story_meta  Mapped Timeline Express story meta.
	 */
	private function save_timeline_express_story_meta( $new_post_id, $story_meta ) {
		clean_post_cache( $new_post_id );

		if ( $story_meta['attachment_id'] && get_post_type( $story_meta['attachment_id'] ) === 'attachment' ) {
			set_post_thumbnail( $new_post_id, $story_meta['attachment_id'] );
		}

		wp_update_post(
			array(
				'ID'          => $new_post_id,
				'post_status' => 'publish',
			)
		);

		update_post_meta( $new_post_id, '_ctl_visible', 'yes' );

		if ( ! empty( $story_meta['formatted_for_meta'] ) ) {
			$story_type_serialized = array(
				'ctl_story_date' => $story_meta['formatted_for_meta'],
			);

			update_post_meta( $new_post_id, 'ctl_story_timestamp', $story_meta['event_timestamp'] );
			update_post_meta( $new_post_id, 'story_date', $story_meta['formatted_for_meta'] );
			update_post_meta( $new_post_id, 'story_type', $story_type_serialized );
		}

		if ( ! empty( $story_meta['color'] ) ) {
			update_post_meta( $new_post_id, 'story_color', $story_meta['color'] );
		}

		if ( ! empty( $story_meta['icon_class'] ) ) {
			$story_icon_serialized = array(
				'fa_field_icon' => $story_meta['icon_class'],
			);
			update_post_meta( $new_post_id, 'story_icon', $story_icon_serialized );
		}
	}

	public function ctl_migrate_stories() {

		check_ajax_referer( 'ctl_migrate_nonce', 'nonce' );
	
		if ( ! current_user_can( 'manage_options' ) ) {
			return wp_send_json_error( array(
				'message' => __( 'Unauthorized', 'cool-timeline' ),
			) );
			
		}
	
		$total_stories = $this->migrate_timeline_express_to_cool_timeline();
	
		if ( empty( $total_stories ) || $total_stories === 0 ) {
		
			return wp_send_json_error( array(
				'status'  => 'no_attachments',
				'message' => __( 'No Attachment Found To Migrate.', 'cool-timeline' ),
			) );
			
		}
	
		wp_send_json_success([
			'message' => __( 'Migration Completed', 'cool-timeline' ),
			'total_stories' => $total_stories
		]);
	}
	
	
}
new CTL_free_migrations();

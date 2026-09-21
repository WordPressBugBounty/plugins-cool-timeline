<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import bundled demo images into the media library for Bricks image controls.
 */
final class CTL_Bricks_Demo_Images {

	private static $files = array(
		'amazon1' => 'amazon1.jpg',
		'amazon2' => 'amazon2.jpg',
		'amazon3' => 'amazon3.png',
	);

	public static function get_image( $key ) {
		if ( ! is_string( $key ) || ! isset( self::$files[ $key ] ) || empty( self::$files[ $key ] ) ) {
			return array();
		}

		$attachment_id = self::get_attachment_id( $key );

		return ! empty( $attachment_id )
			? array(
				'id'   => $attachment_id,
				'url'  => wp_get_attachment_url( $attachment_id ),
				'size' => 'large',
			)
			: array(
				'url'  => CTL_BRICKS_URL . 'assets/images/' . self::$files[ $key ],
				'size' => 'large',
			);
	}

	private static function get_attachment_id( $key ) {
		$stored_ids = get_option( 'ctl_bricks_demo_image_ids', array() );
		$stored_ids = is_array( $stored_ids ) ? $stored_ids : array();

		if ( isset( $stored_ids[ $key ] ) && ! empty( $stored_ids[ $key ] ) ) {
			$attachment_id = (int) $stored_ids[ $key ];

			if ( wp_attachment_is_image( $attachment_id ) ) {
				return $attachment_id;
			}

			unset( $stored_ids[ $key ] );
			update_option( 'ctl_bricks_demo_image_ids', $stored_ids, false );
		}

		if ( ! self::can_import_demo_images() ) {
			return 0;
		}

		if ( ! isset( self::$files[ $key ] ) || empty( self::$files[ $key ] ) ) {
			return 0;
		}

		$attachment_id = self::import_file( self::$files[ $key ] );

		if ( ! empty( $attachment_id ) ) {
			$stored_ids[ $key ] = $attachment_id;
			update_option( 'ctl_bricks_demo_image_ids', $stored_ids, false );
		}

		return (int) $attachment_id;
	}

	private static function can_import_demo_images() {
		return current_user_can( 'upload_files' ) && (
			is_admin()
			|| ( function_exists( 'bricks_is_builder' ) && bricks_is_builder() )
			|| ( function_exists( 'bricks_is_builder_call' ) && bricks_is_builder_call() )
		);
	}

	private static function import_file( $filename ) {
		if ( ! is_string( $filename ) || '' === $filename ) {
			return 0;
		}

		$source = CTL_BRICKS_PATH . 'assets/images/' . $filename;

		if ( ! file_exists( $source ) ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp_file = wp_tempnam( $filename );

		if ( ! $tmp_file || ! copy( $source, $tmp_file ) ) {
			if ( $tmp_file && file_exists( $tmp_file ) ) {
				wp_delete_file( $tmp_file );
			}
			return 0;
		}

		$file_array = array(
			'name'     => $filename,
			'tmp_name' => $tmp_file,
		);

		$attachment_id = media_handle_sideload( $file_array, 0 );

		if ( is_wp_error( $attachment_id ) || empty( $attachment_id ) ) {
			wp_delete_file( $tmp_file );
			return 0;
		}

		return (int) $attachment_id;
	}
}

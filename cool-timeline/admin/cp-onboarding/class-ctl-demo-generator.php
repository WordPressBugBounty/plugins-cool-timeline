<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Legacy CTL demo generator APIs/hooks are retained for compatibility.
/**
 * Cool Timeline Demo Generator
 *
 * Creates demo timeline stories and a draft page with the shortcode.
 * Used during onboarding for the Classic Shortcode-Based flow.
 *
 * @package CoolTimeline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles demo content creation for shortcode-based onboarding.
 */
final class CTL_Demo_Generator {

	/**
	 * Option keys.
	 */
	const OPTION_CREATED  = 'ctl_demo_created';
	const OPTION_PAGE_ID  = 'ctl_demo_page_id';
	const OPTION_POST_IDS = 'ctl_demo_post_ids';
	const META_DEMO_FLAG  = '_ctl_is_demo';

	/**
	 * Custom post type slug.
	 */
	const CPT = 'cool_timeline';

	/**
	 * Story meta keys (each holds an array of fieldset fields — matches CSF structure).
	 */
	const STORY_META_KEY = 'story_type';
	const ICON_META_KEY  = 'story_icon';
	const MEDIA_META_KEY = 'story_media';

	/**
	 * Demo stories data.
	 *
	 * Dates use format: m/d/Y h:i K  (e.g., 01/15/2018 10:00 AM)
	 *
	 * @return array
	 */
	protected function get_demo_data() {
		$data = array(
			array(
				'title' => __( 'Company Founded', 'cool-timeline' ),
				'date'  => '01/15/2010 10:00 AM',
				'desc'  => __( 'Our journey began with a small team and a big vision to build something meaningful.', 'cool-timeline' ),
				'icon'  => 'fa fa-flag',
			),
			array(
				'title' => __( 'First Product Launch', 'cool-timeline' ),
				'date'  => '06/20/2011 09:30 AM',
				'desc'  => __( 'We launched our first product and received an overwhelming response from early users.', 'cool-timeline' ),
				'icon'  => 'fa fa-rocket',
			),
		);

		return apply_filters( 'ctl_demo_data', $data );
	}

	/**
	 * Main entry — generate demo timeline.
	 *
	 * @param string $layout Shortcode layout attribute.
	 * @return array|WP_Error
	 */
	public function generate( $layout = 'default' ) {

		// Step 1 — duplicate check.
		$existing = $this->get_existing_demo();
		if ( $existing ) {
			return $existing;
		}

		// Step 2 — reuse existing stories when ≥ 2; otherwise create demo stories.
		$post_ids = get_posts(
			array(
				'post_type'      => self::CPT,
				'post_status'    => array( 'publish', 'future' ),
				'posts_per_page' => 2,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		$created_demo = false;
		if ( count( $post_ids ) < 2 ) {
			$post_ids = $this->create_demo_stories();
			if ( is_wp_error( $post_ids ) ) {
				return $post_ids;
			}
			$created_demo = true;
		}

		// Step 3 — create timeline page with shortcode.
		$page_id = $this->create_timeline_page( $layout );
		if ( is_wp_error( $page_id ) ) {
			if ( $created_demo ) {
				$this->rollback_posts( $post_ids );
			}
			return $page_id;
		}

		// Step 4 — persist state.
		update_option( self::OPTION_CREATED, current_time( 'mysql' ) );
		update_option( self::OPTION_PAGE_ID, $page_id );
		update_option( self::OPTION_POST_IDS, $post_ids );

		return array(
			'page_id'      => $page_id,
			'post_ids'     => $post_ids,
			'redirect_url' => get_edit_post_link( $page_id, 'raw' ),
			'preview_url'  => get_preview_post_link( $page_id ),
		);
	}

	/**
	 * Check if demo already exists and is still valid.
	 *
	 * @return array|false
	 */
	protected function get_existing_demo() {

		$allow_recreate = apply_filters( 'ctl_demo_allow_recreate', false );
		if ( $allow_recreate ) {
			return false;
		}

		if ( ! get_option( self::OPTION_CREATED ) ) {
			return false;
		}

		$page_id  = (int) get_option( self::OPTION_PAGE_ID );
		$post_ids = (array) get_option( self::OPTION_POST_IDS, array() );

		// The demo is only valid if the page still exists (and isn't trashed) AND
		// at least one of its stories survives. A demo whose stories were deleted
		// would render an empty timeline, so treat it as stale and recreate.
		$page         = $page_id ? get_post( $page_id ) : null;
		$page_ok      = $page && 'trash' !== $page->post_status;
		$has_story    = false;

		foreach ( $post_ids as $story_id ) {
			$story = get_post( (int) $story_id );
			if ( $story && 'trash' !== $story->post_status ) {
				$has_story = true;
				break;
			}
		}

		if ( ! $page_ok || ! $has_story ) {
			$this->reset_demo_state();
			return false;
		}

		return array(
			'page_id'      => $page_id,
			'post_ids'     => $post_ids,
			'redirect_url' => get_edit_post_link( $page_id, 'raw' ),
			'preview_url'  => get_preview_post_link( $page_id ),
			'already'      => true,
		);
	}

	/**
	 * Remove a stale/partial demo and clear its options so a fresh one is built.
	 *
	 * Only deletes posts that still carry the demo flag, so a page the user has
	 * repurposed is never destroyed.
	 *
	 * @return void
	 */
	protected function reset_demo_state() {

		$page_id  = (int) get_option( self::OPTION_PAGE_ID );
		$post_ids = (array) get_option( self::OPTION_POST_IDS, array() );

		foreach ( array_merge( array( $page_id ), $post_ids ) as $id ) {
			$id = (int) $id;
			if ( $id && get_post( $id ) && get_post_meta( $id, self::META_DEMO_FLAG, true ) ) {
				wp_delete_post( $id, true );
			}
		}

		delete_option( self::OPTION_CREATED );
		delete_option( self::OPTION_PAGE_ID );
		delete_option( self::OPTION_POST_IDS );
	}

	/**
	 * Create demo timeline stories.
	 *
	 * @return array|WP_Error Array of post IDs on success.
	 */
	protected function create_demo_stories() {

		$demo_data = $this->get_demo_data();

		if ( empty( $demo_data ) ) {
			return new WP_Error( 'no_demo_data', __( 'Demo data is missing or invalid.', 'cool-timeline' ) );
		}

		$post_ids = array();

		foreach ( $demo_data as $story ) {

			$post_id = wp_insert_post(
				array(
					'post_type'    => self::CPT,
					'post_title'   => sanitize_text_field( $story['title'] ),
					'post_content' => wp_kses_post( $story['desc'] ),
					'post_status'  => 'publish',
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				$this->rollback_posts( $post_ids );
				return $post_id;
			}

			// story_type (date) — read by Story Year / Story Date columns.
			update_post_meta(
				$post_id,
				self::STORY_META_KEY,
				array(
					'ctl_story_date' => sanitize_text_field( $story['date'] ),
				)
			);

			// story_icon — read by the Story Icon column.
			$icon = isset( $story['icon'] ) ? sanitize_text_field( $story['icon'] ) : '';
			update_post_meta(
				$post_id,
				self::ICON_META_KEY,
				array(
					'fa_field_icon' => $icon,
				)
			);

			// story_media — matches CSF default (full).
			update_post_meta(
				$post_id,
				self::MEDIA_META_KEY,
				array(
					'img_cont_size' => 'full',
				)
			);

			// Mirror the flat keys the save_post hook normally generates from $_POST
			// (cooltimeline.php). Required because the timeline shortcode query orders by
			// ctl_story_timestamp and excludes posts that lack it.
			$story_date = sanitize_text_field( $story['date'] );
			if ( class_exists( 'CTL_Helpers' ) ) {
				$story_timestamp = CTL_Helpers::ctlfree_generate_custom_timestamp( $story_date );
				if ( ! empty( $story_timestamp ) ) {
					update_post_meta( $post_id, 'ctl_story_timestamp', $story_timestamp );
				}
			}
			update_post_meta( $post_id, 'ctl_story_date', $story_date );
			update_post_meta( $post_id, 'story_based_on', 'default' );

			// Demo flag for cleanup.
			update_post_meta( $post_id, self::META_DEMO_FLAG, 1 );

			$post_ids[] = $post_id;
		}

		return $post_ids;
	}

	/**
	 * Create the timeline page with shortcode.
	 *
	 * @param string $layout Shortcode layout.
	 * @return int|WP_Error
	 */
	protected function create_timeline_page( $layout = 'default' ) {

		$layout    = sanitize_key( $layout );
		$shortcode ='[cool-timeline layout="default" skin="default" show-posts="10" date-format="F j" icons="NO" animation="none" story-content="short" order="DESC" ]';

		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => __( 'Demo Timeline', 'cool-timeline' ),
				'post_content' => $shortcode,
				'post_status'  => 'draft',
				'meta_input'   => array(
					self::META_DEMO_FLAG => 1,
				),
			),
			true
		);

		return $page_id;
	}

	/**
	 * Delete partially created posts if generation fails midway.
	 *
	 * @param array $post_ids Post IDs to delete.
	 */
	protected function rollback_posts( $post_ids ) {
		if ( empty( $post_ids ) ) {
			return;
		}
		foreach ( $post_ids as $id ) {
			wp_delete_post( $id, true );
		}
	}
}

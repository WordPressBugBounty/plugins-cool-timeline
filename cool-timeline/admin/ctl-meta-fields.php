<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Enqueue Pro story-settings teaser styles on Cool Timeline story screens.
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function ctl_enqueue_story_pro_teaser_assets($hook_suffix)
{
	$screen = function_exists('get_current_screen') ? get_current_screen() : null;

	if (!$screen || 'cool_timeline' !== $screen->post_type) {
		return;
	}

	if (!in_array($hook_suffix, array('post.php', 'post-new.php'), true)) {
		return;
	}

	$version = defined('CTL_V') ? CTL_V : '1.0.0';
	$css = CTL_PLUGIN_DIR . 'admin/css/ctl-story-pro-teaser.css';

	wp_enqueue_style(
		'ctl-story-pro-teaser',
		CTL_PLUGIN_URL . 'admin/css/ctl-story-pro-teaser.css',
		array(),
		file_exists($css) ? (string) filemtime($css) : $version
	);
}
add_action('admin_enqueue_scripts', 'ctl_enqueue_story_pro_teaser_assets');

/**
 * Marketing teaser for Pro story settings (Buy Pro link must keep add_stories UTM).
 *
 * @return string
 */
function ctl_get_story_pro_teaser_markup()
{
	$buy_url = esc_url(CTL_BUY_PRO . '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=add_stories');

	ob_start();
	?>
	<div class="ctl-story-pro-teaser">
		<div class="ctl-story-pro-teaser__banner">
			<div class="ctl-story-pro-teaser__copy">
				<span class="ctl-story-pro-teaser__eyebrow">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
						stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path
							d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
					</svg>
					<?php esc_html_e('Premium Story Options', 'cool-timeline'); ?>
				</span>
				<h4 class="ctl-story-pro-teaser__title">
					<?php esc_html_e('Unlock Advanced Story Options in Cool Timeline Pro', 'cool-timeline'); ?>
				</h4>
				<p class="ctl-story-pro-teaser__desc">
					<?php esc_html_e('Custom order labels, slideshow galleries, video embeds, custom icons, links, and per-story colors.', 'cool-timeline'); ?>
				</p>
			</div>
			<div class="ctl-story-pro-teaser__actions">
				<a class="button button-primary ctl-story-pro-teaser__btn-buy"
					href="<?php echo $buy_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>"
					target="_blank" rel="noopener noreferrer">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
						stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
						<path d="M7 11V7a5 5 0 0 1 10 0v4" />
					</svg>
					<span><?php esc_html_e('Upgrade to Pro', 'cool-timeline'); ?></span>
				</a>
			</div>
		</div>

		<div class="ctl-story-pro-teaser__preview-wrap">
			<a class="ctl-story-pro-teaser__preview-overlay"
				href="<?php echo $buy_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>"
				target="_blank" rel="noopener noreferrer">
				<span class="ctl-story-pro-teaser__overlay-badge">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
						stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
						<path d="M7 11V7a5 5 0 0 1 10 0v4" />
					</svg>
					<?php esc_html_e('Pro Feature Preview — Click to Unlock All Options', 'cool-timeline'); ?>
				</span>
			</a>

			<div class="ctl-story-pro-teaser__mock">
				<!-- Mock Row 1: Story Type & Custom Order -->
				<div class="ctl-story-pro-teaser__mock-row">
					<div class="ctl-story-pro-teaser__mock-label">
						<span><?php esc_html_e('Story Type', 'cool-timeline'); ?></span>
						<span class="ctl-story-pro-teaser__tag-pro">PRO</span>
					</div>
					<div class="ctl-story-pro-teaser__mock-content">
						<div class="ctl-story-pro-teaser__mock-group">
							<span
								class="ctl-story-pro-teaser__mock-field-title"><?php esc_html_e('Story Based On', 'cool-timeline'); ?></span>
							<div class="ctl-story-pro-teaser__mock-btn-group">
								<span
									class="ctl-story-pro-teaser__mock-btn"><?php esc_html_e('Date Based', 'cool-timeline'); ?></span>
								<span
									class="ctl-story-pro-teaser__mock-btn ctl-story-pro-teaser__mock-btn--active"><?php esc_html_e('Custom Order Based', 'cool-timeline'); ?></span>
							</div>
						</div>

						<div class="ctl-story-pro-teaser__mock-group">
							<span
								class="ctl-story-pro-teaser__mock-field-title"><?php esc_html_e('Add Custom Label', 'cool-timeline'); ?></span>
							<div class="ctl-story-pro-teaser__mock-input-wrap">
								<div class="ctl-story-pro-teaser__mock-input">Step 1</div>
								<span
									class="ctl-story-pro-teaser__mock-hint"><?php esc_html_e('Use custom labels like "Phase 1", "Step 1", "Q1 Release"', 'cool-timeline'); ?></span>
							</div>
						</div>

						<div class="ctl-story-pro-teaser__mock-group ctl-story-pro-teaser__mock-group--inline">
							<span
								class="ctl-story-pro-teaser__mock-field-title"><?php esc_html_e('Mid Line Label', 'cool-timeline'); ?></span>
							<div class="ctl-story-pro-teaser__mock-toggle">
								<span
									class="ctl-story-pro-teaser__mock-toggle-switch ctl-story-pro-teaser__mock-toggle-switch--on"></span>
								<span
									class="ctl-story-pro-teaser__mock-toggle-text"><?php esc_html_e('Display mid line label above story', 'cool-timeline'); ?></span>
							</div>
						</div>
					</div>
				</div>

				<!-- Mock Row 2: Media Options -->
				<div class="ctl-story-pro-teaser__mock-row">
					<div class="ctl-story-pro-teaser__mock-label">
						<span><?php esc_html_e('Story Media', 'cool-timeline'); ?></span>
						<span class="ctl-story-pro-teaser__tag-pro">PRO</span>
					</div>
					<div class="ctl-story-pro-teaser__mock-content">
						<div class="ctl-story-pro-teaser__mock-group">
							<span
								class="ctl-story-pro-teaser__mock-field-title"><?php esc_html_e('Media Type', 'cool-timeline'); ?></span>
							<div class="ctl-story-pro-teaser__mock-btn-group">
								<span
									class="ctl-story-pro-teaser__mock-btn"><?php esc_html_e('Default (Image)', 'cool-timeline'); ?></span>
								<span
									class="ctl-story-pro-teaser__mock-btn"><?php esc_html_e('Video', 'cool-timeline'); ?></span>
								<span
									class="ctl-story-pro-teaser__mock-btn ctl-story-pro-teaser__mock-btn--active"><?php esc_html_e('Slideshow Gallery', 'cool-timeline'); ?></span>
							</div>
						</div>

						<div class="ctl-story-pro-teaser__mock-group">
							<span
								class="ctl-story-pro-teaser__mock-field-title"><?php esc_html_e('Slideshow Gallery Images', 'cool-timeline'); ?></span>
							<div class="ctl-story-pro-teaser__mock-gallery">
								<div class="ctl-story-pro-teaser__mock-thumb ctl-story-pro-teaser__mock-thumb--1"><span>📷
										Photo 1</span></div>
								<div class="ctl-story-pro-teaser__mock-thumb ctl-story-pro-teaser__mock-thumb--2"><span>📷
										Photo 2</span></div>
								<div class="ctl-story-pro-teaser__mock-thumb ctl-story-pro-teaser__mock-thumb--3"><span>📷
										Photo 3</span></div>
								<span class="ctl-story-pro-teaser__mock-add-media">+
									<?php esc_html_e('Add Images', 'cool-timeline'); ?></span>
							</div>
						</div>
					</div>
				</div>

				<!-- Mock Row 3: Styling & Custom Links -->
				<div class="ctl-story-pro-teaser__mock-row">
					<div class="ctl-story-pro-teaser__mock-label">
						<span><?php esc_html_e('Custom Styles & Link', 'cool-timeline'); ?></span>
						<span class="ctl-story-pro-teaser__tag-pro">PRO</span>
					</div>
					<div class="ctl-story-pro-teaser__mock-content ctl-story-pro-teaser__mock-content--grid">
						<div class="ctl-story-pro-teaser__mock-group">
							<span
								class="ctl-story-pro-teaser__mock-field-title"><?php esc_html_e('Story Custom Link', 'cool-timeline'); ?></span>
							<div class="ctl-story-pro-teaser__mock-input">https://example.com/story-details</div>
						</div>
						<div class="ctl-story-pro-teaser__mock-group">
							<span
								class="ctl-story-pro-teaser__mock-field-title"><?php esc_html_e('Story Accent Color', 'cool-timeline'); ?></span>
							<div class="ctl-story-pro-teaser__mock-color-picker">
								<span class="ctl-story-pro-teaser__mock-color-swatch" style="background: #1a6463;"></span>
								<code>#1a6463</code>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Right-sidebar Cool Timeline Pro upsell markup (Buy Now keeps add_stories UTM).
 *
 * @return string
 */
function ctl_get_story_sidebar_pro_markup()
{
	$buy_url = esc_url(CTL_BUY_PRO . '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=add_stories');
	$demo_url = esc_url(CTL_DEMO_URL . '&utm_content=add_stories');

	$features = array(
		__('25+ Beautiful Timeline Designs', 'cool-timeline'),
		__('Multiple Layouts (Vertical & Horizontal)', 'cool-timeline'),
		__('Timeline Filters & Search', 'cool-timeline'),
		__('AJAX Load More & Pagination', 'cool-timeline'),
		__('Shortcode Builder & Gutenberg Block', 'cool-timeline'),
		__('Customize Colors & Typography', 'cool-timeline'),
		__('Advanced Animations', 'cool-timeline'),
		__('Flexible Date Formats & Sorting', 'cool-timeline'),
		__('Premium Support & Regular Updates', 'cool-timeline'),
	);

	ob_start();
	?>
	<div class="ctl-story-sidebar-pro">
		<ul class="ctl-story-sidebar-pro__list">
			<?php foreach ($features as $feature): ?>
				<li>
					<svg class="ctl-story-sidebar-pro__check" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
						focusable="false">
						<path fill-rule="evenodd"
							d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
							clip-rule="evenodd" />
					</svg>
					<span><?php echo esc_html($feature); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="ctl-story-sidebar-pro__footer">
			<p class="ctl-story-sidebar-pro__cta-label"><?php esc_html_e('Upgrade to Pro version', 'cool-timeline'); ?>
			</p>
			<div class="ctl-story-sidebar-pro__actions">
				<a class="button ctl-story-sidebar-pro__link"
					href="<?php echo $demo_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>"
					target="_blank" rel="noopener noreferrer">
					<?php esc_html_e('View Demos', 'cool-timeline'); ?>
				</a>
				<a class="button button-primary ctl-story-sidebar-pro__buy"
					href="<?php echo $buy_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>"
					target="_blank" rel="noopener noreferrer">
					<?php esc_html_e('Buy Now', 'cool-timeline'); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Right-sidebar review request markup.
 *
 * @return string
 */
function ctl_get_story_sidebar_review_markup()
{
	$review_url = esc_url('https://wordpress.org/support/plugin/cool-timeline/reviews/#new-post');
	$star_svg = '<svg class="ctl-story-sidebar-review__star" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M10 1.6l2.59 5.25 5.79.84-4.19 4.08.99 5.77L10 14.77l-5.18 2.77.99-5.77L1.62 7.69l5.79-.84L10 1.6z"/></svg>';

	ob_start();
	?>
	<div class="ctl-story-sidebar-review">
		<p class="ctl-story-sidebar-review__text">
			<?php esc_html_e('If you find our plugin and support helpful, please rate and review us. It helps us grow and improve our services.', 'cool-timeline'); ?>
		</p>
		<a class="ctl-story-sidebar-review__stars"
			href="<?php echo $review_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>"
			target="_blank" rel="noopener noreferrer"
			aria-label="<?php esc_attr_e('Five star rating', 'cool-timeline'); ?>">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG markup, no user input.
			echo str_repeat($star_svg, 5);
			?>
		</a>
		<a class="button ctl-story-sidebar-review__button"
			href="<?php echo $review_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>"
			target="_blank" rel="noopener noreferrer">
			<?php esc_html_e('Submit Review', 'cool-timeline'); ?>
		</a>
	</div>
	<?php
	return (string) ob_get_clean();
}

/*
Creating Meta boxes for timeline stories section
*/
// Control core classes for avoid errors
if (class_exists('CSF')) {

	// Set a unique slug-like ID
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$prefix = 'ctl_post_meta';

	//
	// Create a metabox
	CSF::createMetabox(
		$prefix,
		array(
			'title' => 'Timeline Story Settings',
			'post_type' => 'cool_timeline',
			'data_type' => 'unserialize',
			'context' => 'normal', // The context within the screen where the boxes should display. `normal`, `side`, `advanced`
		)
	);

	// Create a section
	CSF::createSection(
		$prefix,
		array(
			// 'title'  => 'Add Icon',
			'data_type' => 'unserialize',
			'fields' => array(

				// Story Date
				array(
					'id' => 'story_type',
					'type' => 'fieldset',
					'title' => 'Story Type',
					'fields' => array(
						array(
							'id' => 'ctl_story_date',
							'type' => 'datetime',
							'title' => __('Story Date / Year <span class="ctl_required">*</span>', 'cool-timeline'),
							'desc' => '<p class="ctl_required">Please select story Story Date / Year / Time using datepicker only. <strong>Date Format( mm/dd/yy hh:mm )</strong></p>',
							'default' => gmdate('m/d/Y h:i a'),
							'settings' => array(
								'enableTime' => true,
								'dateFormat' => 'm/d/Y h:i K',
								'minuteIncrement' => '1',
							),
						),
					),
				),

				// ICON
				array(
					'id' => 'story_icon',
					'type' => 'fieldset',
					'title' => 'Story Icon',
					'fields' => array(
						array(
							'id' => 'fa_field_icon',
							'type' => 'icon',
							'title' => 'Font Awesome Icon',
						),
					),
				),

				array(
					'id' => 'story_media',
					'type' => 'fieldset',
					'title' => 'Story Media',
					'fields' => array(
						array(
							'id' => 'img_cont_size',
							'type' => 'button_set',
							'title' => 'Story image size',
							'options' => array(
								'full' => 'Full',
								'small' => 'Small',
							),
							'default' => 'full',
						),


					),
				),

				array(
					'id' => 'ctl_pro_screenshot',
					'type' => 'content',
					'content' => ctl_get_story_pro_teaser_markup(),
					'class' => 'story_format_image',
				),


			),

		)
	);

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$pro_list = 'feautre_list';
	// Create a metabox
	CSF::createMetabox(
		$pro_list,
		array(
			'title' => __('Cool Timeline Pro', 'cool-timeline'),
			'post_type' => 'cool_timeline',
			'priority' => 'low',
			'data_type' => 'unserialize',
			'context' => 'side', // The context within the screen where the boxes should display. `normal`, `side`, `advanced`
		)
	);

	CSF::createSection(
		$pro_list,
		array(
			'fields' => array(
				array(
					'id' => 'pro_feature_list_section',
					'type' => 'content',
					'content' => ctl_get_story_sidebar_pro_markup(),
					'class' => 'ctl-story-sidebar-pro-field',
				),
			),
		)
	);

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$review_us = 'review_us';
	CSF::createMetabox(
		$review_us,
		array(
			'title' => __('Share Your Feedback', 'cool-timeline'),
			'post_type' => 'cool_timeline',
			'data_type' => 'unserialize',
			'priority' => 'low',
			'context' => 'side', // The context within the screen where the boxes should display. `normal`, `side`, `advanced`
		)
	);

	CSF::createSection(
		$review_us,
		array(
			'fields' => array(
				array(
					'id' => 'review_us_section',
					'type' => 'content',
					'content' => ctl_get_story_sidebar_review_markup(),
					'class' => 'ctl-story-sidebar-review-field',
				),
			),
		)
	);

}


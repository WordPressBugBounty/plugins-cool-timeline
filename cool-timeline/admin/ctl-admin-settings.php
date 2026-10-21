<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Enqueue Advance Settings tab assets.
 *
 * @return void
 */
function ctl_enqueue_advance_settings_assets()
{
	if (function_exists('ctl_is_timeline_addon_page')) {
		if (!ctl_is_timeline_addon_page()) {
			return;
		}
	} else {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen detection.
		if (!isset($_GET['page']) || 'cool_timeline_settings' !== sanitize_key(wp_unslash($_GET['page']))) {
			return;
		}
	}

	$version = defined('CTL_V') ? CTL_V : '1.0.0';

	wp_enqueue_style(
		'ctl-advance-settings',
		CTL_PLUGIN_URL . 'admin/css/ctl-advance-settings.css',
		array(),
		$version
	);

	wp_enqueue_script(
		'ctl-advance-settings',
		CTL_PLUGIN_URL . 'admin/js/ctl-advance-settings.js',
		array('jquery'),
		$version,
		true
	);

	wp_localize_script(
		'ctl-advance-settings',
		'ctlAdvanceSettings',
		array(
			'i18n' => array(
				'showTerms' => __('See terms', 'cool-timeline'),
				'hideTerms' => __('Hide terms', 'cool-timeline'),
			),
		)
	);
}
add_action('admin_enqueue_scripts', 'ctl_enqueue_advance_settings_assets');

/**
 * Load Style / Typography live preview assets on the settings screen.
 *
 * @return void
 */
function ctl_enqueue_settings_preview_assets()
{
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used only to scope admin assets.
	$current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

	if ('cool_timeline_settings' !== $current_page) {
		return;
	}

	$version = defined('CTL_V') ? CTL_V : '1.0.0';
	$css = CTL_PLUGIN_DIR . 'admin/css/ctl-settings-preview.css';
	$js = CTL_PLUGIN_DIR . 'admin/js/ctl-settings-preview.js';

	wp_enqueue_style(
		'ctl-settings-preview',
		CTL_PLUGIN_URL . 'admin/css/ctl-settings-preview.css',
		array(),
		file_exists($css) ? (string) filemtime($css) : $version
	);

	wp_enqueue_script(
		'ctl-settings-preview',
		CTL_PLUGIN_URL . 'admin/js/ctl-settings-preview.js',
		array('jquery'),
		file_exists($js) ? (string) filemtime($js) : $version,
		true
	);
}
add_action('admin_enqueue_scripts', 'ctl_enqueue_settings_preview_assets');

/**
 * Return the dummy timeline used by the Style and Typography live previews.
 *
 * @return string
 */
function ctl_settings_preview_markup()
{
	static $markup = null;

	if (null !== $markup) {
		return $markup;
	}

	ob_start();
	?>
	<section class="ctl-settings-preview"
		aria-label="<?php esc_attr_e('Timeline settings live preview', 'cool-timeline'); ?>">
		<header class="ctl-settings-preview__header">
			<h3><?php esc_html_e('Live Preview with dummy stories', 'cool-timeline'); ?></h3>
			<span class="ctl-settings-preview__status"><span
					aria-hidden="true"></span><?php esc_html_e('Updates instantly', 'cool-timeline'); ?></span>
		</header>

		<div class="ctl-settings-preview__stage">
			<div class="ctl-settings-preview__timeline" aria-hidden="true">
				<div class="ctl-settings-preview__line-fill"></div>
			</div>
			<div class="ctl-settings-preview__year">2026</div>

			<article class="ctl-settings-preview__story ctl-settings-preview__story--first">
				<div class="ctl-settings-preview__date">12 AUG 2026</div>
				<div class="ctl-settings-preview__dot"><span aria-hidden="true"></span></div>
				<div class="ctl-settings-preview__card">
					<h4><?php esc_html_e('A New Chapter Begins', 'cool-timeline'); ?></h4>
					<div class="ctl-settings-preview__media-wrap">
						<img class="ctl-settings-preview__media"
							src="<?php echo esc_url(CTL_PLUGIN_URL . 'assets/images/ctl-settings-preview-story.jpg'); ?>"
							alt="<?php esc_attr_e('A creative team celebrating a project milestone', 'cool-timeline'); ?>"
							width="1200" height="675" loading="lazy" />
						<p class="ctl-settings-preview__caption">
							<?php esc_html_e('Team celebrates launch day', 'cool-timeline'); ?></p>
					</div>
					<p class="ctl-settings-preview__content">
						<?php esc_html_e('Preview your story text, colors, and typography before saving.', 'cool-timeline'); ?>
					</p>
					<span class="ctl-settings-preview__read-more"><?php esc_html_e('Read more', 'cool-timeline'); ?> <span
							aria-hidden="true">&rarr;</span></span>
				</div>
			</article>

			<article class="ctl-settings-preview__story ctl-settings-preview__story--second">
				<div class="ctl-settings-preview__date">24 MAY 2026</div>
				<div class="ctl-settings-preview__dot"><span aria-hidden="true"></span></div>
				<div class="ctl-settings-preview__card">
					<h4><?php esc_html_e('The Story Takes Shape', 'cool-timeline'); ?></h4>
					<div class="ctl-settings-preview__media-wrap">
						<img class="ctl-settings-preview__media ctl-settings-preview__media--second"
							src="<?php echo esc_url(CTL_PLUGIN_URL . 'assets/images/ctl-settings-preview-story.jpg'); ?>"
							alt="<?php esc_attr_e('A team planning its next project milestone', 'cool-timeline'); ?>"
							width="1200" height="675" loading="lazy" />
						<p class="ctl-settings-preview__caption">
							<?php esc_html_e('Planning the next milestone', 'cool-timeline'); ?></p>
					</div>
					<p class="ctl-settings-preview__content">
						<?php esc_html_e('See how alternating story colors work together.', 'cool-timeline'); ?></p>
				</div>
			</article>
		</div>
		<p class="ctl-settings-preview__note">
			<?php esc_html_e('Preview only — changes are saved when you click Save.', 'cool-timeline'); ?></p>
	</section>
	<?php

	$markup = (string) ob_get_clean();

	return $markup;
}

/**
 * Render a Free / Pro comparison table cell.
 *
 * @param string $level yes|no|basic
 * @return string
 */
function ctl_advance_compare_cell($level)
{
	if ('yes' === $level) {
		return '<span class="ctl-advance-table__yes" aria-label="' . esc_attr__('Included', 'cool-timeline') . '"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span></span>';
	}

	if ('basic' === $level) {
		return '<span class="ctl-advance-table__partial" aria-label="' . esc_attr__('Basic', 'cool-timeline') . '">' . esc_html__('Basic', 'cool-timeline') . '</span>';
	}

	return '<span class="ctl-advance-table__no" aria-label="' . esc_attr__('Not included', 'cool-timeline') . '"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></span>';
}

/**
 * Build Free vs Pro comparison table markup.
 *
 * @return string
 */
function ctl_get_advance_settings_compare_markup()
{
	$rows = array(
		array(
			'label' => __('Vertical Timeline', 'cool-timeline'),
			'free' => 'yes',
			'pro' => 'yes',
		),
		array(
			'label' => __('Vertical One-Sided Layout', 'cool-timeline'),
			'free' => 'yes',
			'pro' => 'yes',
		),
		array(
			'label' => __('Compact Timeline Layout', 'cool-timeline'),
			'free' => 'no',
			'pro' => 'yes',
		),
		array(
			'label' => __('Horizontal Timeline Layout', 'cool-timeline'),
			'free' => 'no',
			'pro' => 'yes',
		),
		array(
			'label' => __('Gutenberg Timeline Block', 'cool-timeline'),
			'free' => 'yes',
			'pro' => 'yes',
		),
		array(
			'label' => __('Shortcode Generator', 'cool-timeline'),
			'free' => 'yes',
			'pro' => 'yes',
		),
		array(
			'label' => __('Premium Designs & Skins', 'cool-timeline'),
			'free' => 'basic',
			'pro' => 'yes',
		),
		array(
			'label' => __('Category Filters', 'cool-timeline'),
			'free' => 'no',
			'pro' => 'yes',
		),
		array(
			'label' => __('Images, Videos & Slideshows', 'cool-timeline'),
			'free' => 'no',
			'pro' => 'yes',
		),
		array(
			'label' => __('Ajax Load More & Pagination', 'cool-timeline'),
			'free' => 'no',
			'pro' => 'yes',
		),
		array(
			'label' => __('Per-Story Custom Colors', 'cool-timeline'),
			'free' => 'no',
			'pro' => 'yes',
		),
		array(
			'label' => __('Animation Effects', 'cool-timeline'),
			'free' => 'basic',
			'pro' => 'yes',
		),
		array(
			'label' => __('Priority Support', 'cool-timeline'),
			'free' => 'no',
			'pro' => 'yes',
		),
	);

	$tbody = '';
	foreach ($rows as $row) {
		$tbody .= '<tr>';
		$tbody .= '<td class="ctl-advance-compare__feature">' . esc_html($row['label']) . '</td>';
		$tbody .= '<td class="ctl-advance-compare__cell">' . ctl_advance_compare_cell($row['free']) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$tbody .= '<td class="ctl-advance-compare__cell">' . ctl_advance_compare_cell($row['pro']) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$tbody .= '</tr>';
	}

	$buy_url = esc_url(CTL_BUY_PRO . '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=advance_settings');
	$demo_url = esc_url(CTL_BUY_PRO . '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=advance_settings');

	ob_start();
	?>
	<div class="ctl-advance-panel">
		<div class="ctl-advance-pro-actions ctl-advance-pro-actions--top">
			<a href="<?php echo $buy_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
				target="_blank" rel="noopener noreferrer" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important; border: 1px solid #2dd4bf !important; color: #ffffff !important; padding: 6px 16px !important; font-size: 12px !important; font-weight: bold !important; border-radius: 6px !important; box-shadow: 0 3px 10px rgba(20, 184, 166, 0.4) !important; text-decoration: none !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; text-shadow: none !important; line-height: 1.4 !important; transition: all 0.2s ease-in-out;" onmouseover="this.style.background='linear-gradient(135deg, #14b8a6 0%, #0d9488 100%)'; this.style.boxShadow='0 5px 15px rgba(20, 184, 166, 0.6)';" onmouseout="this.style.background='linear-gradient(135deg, #0d9488 0%, #0f766e 100%)'; this.style.boxShadow='0 3px 10px rgba(20, 184, 166, 0.4)';">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; pointer-events: none; display: inline-block; vertical-align: middle;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
				<span><?php esc_html_e('Upgrade to Pro', 'cool-timeline'); ?></span>
			</a>
			<a class="button button-secondary"
				href="<?php echo $demo_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
				target="_blank" rel="noopener noreferrer">
				<?php esc_html_e('View Pro Demo', 'cool-timeline'); ?>
			</a>
		</div>
		<table class="ctl-advance-compare">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e('Feature', 'cool-timeline'); ?></th>
					<th scope="col" class="ctl-advance-compare__col-plan"><?php esc_html_e('Free', 'cool-timeline'); ?>
					</th>
					<th scope="col" class="ctl-advance-compare__col-plan ctl-advance-compare__col-pro">
						<?php esc_html_e('Pro', 'cool-timeline'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php echo $tbody; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per row above. ?>
			</tbody>
		</table>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Usage-data sharing field for Advance Settings.
 *
 * @param string $review_option Stored opt-in choice.
 * @return array<string, mixed>
 */
function ctl_get_advance_settings_privacy_field($review_option)
{
	$terms_url = esc_url('https://my.coolplugins.net/terms/usage-tracking/');

	$desc = '<div class="ctl-advance-privacy-desc">';
	$desc .= esc_html__('Share non-sensitive site data to help us improve compatibility (WordPress version, plugins, theme, and server details).', 'cool-timeline');
	$desc .= ' <a href="#" class="cpfm-see-terms">' . esc_html__('See terms', 'cool-timeline') . '</a>';
	$desc .= '<div class="ctl-advance-terms-box">';
	$desc .= '<p>' . sprintf(
		/* translators: %s: usage tracking terms URL. */
		esc_html__('Details: %s', 'cool-timeline'),
		'<a href="' . $terms_url . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Usage tracking policy', 'cool-timeline') . '</a>'
	) . '</p>';
	$desc .= '<ul>';
	$desc .= '<li>' . esc_html__('Your site URL and admin email.', 'cool-timeline') . '</li>';
	$desc .= '<li>' . esc_html__('Active plugins, theme, PHP/MySQL versions, memory limit, and site language.', 'cool-timeline') . '</li>';
	$desc .= '</ul></div></div>';

	return array(
		'id' => 'ctl_cpfm_feedback_data',
		'type' => 'checkbox',
		'title' => __('Usage Data Sharing', 'cool-timeline'),
		'default' => ('yes' === $review_option),
		'desc' => $desc,
	);
}

// Add admin notice for timeline express migration
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function ctl_admin_notice_for_migration()
{

	// Check if we're on the Cool Timeline settings page (read-only screen detection).
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if (!isset($_GET['page']) || 'cool_timeline_settings' !== sanitize_key(wp_unslash($_GET['page']))) {
		return;
	}
	// Check if timeline express is installed and migration is not completed
	if (file_exists(WP_PLUGIN_DIR . '/timeline-express/timeline-express.php')) {
		$migration_completed = get_option('timeline_express_migrated');

		// Only show notice if migration is not completed
		if (!$migration_completed) {
			?>
			<div class="notice  ctl_migration notice-info is-dismissible">
				<div class="migration_message_container">
					<p>
						<?php echo esc_html__('Timeline Express plugin is installed on your site. To move your announcements into Cool Timeline, you can now start the migration process.', 'cool-timeline'); ?>
						<a href="admin.php?page=cool_timeline_settings#tab=migration-settings"
							class="button button-small ctl_migration_btn"><?php echo esc_html__('Start Migration', 'cool-timeline'); ?></a>
					</p>
				</div>
			</div>
			<?php
		}
	}
}
add_action('admin_notices', 'ctl_admin_notice_for_migration');

function ctl_get_pro_field_teaser_markup($title, $description, $html_control)
{
	$pro_url = esc_url(CTL_BUY_PRO . '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=general_settings_teaser');

	ob_start();
	?>
	<div class="csf-field csf-field-callback ctl-pro-locked-field-wrapper"
		style="opacity: 0.65; position: relative; cursor: pointer; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px; transition: all 0.3s ease;"
		onmouseover="this.style.opacity='0.9'; this.style.borderColor='#8e44ad';"
		onmouseout="this.style.opacity='0.65'; this.style.borderColor='#eee';"
		onclick="window.open('<?php echo esc_js($pro_url); ?>', '_blank');">
		<div class="csf-title" style="float: left; width: 33%; padding-right: 20px; box-sizing: border-box; margin: 0;">
			<h4 style="margin: 0; font-size: 14px; color: #23282d; line-height: 1.4;"><?php echo esc_html($title); ?>
				<span class="ctl-pro-locked-badge"
					style="background: #8e44ad; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 3px; font-weight: bold; margin-left: 5px; vertical-align: middle; text-transform: uppercase;">PRO</span>
			</h4>
			<?php if (!empty($description)): ?>
				<div class="csf-subtitle-text" style="color: #666; font-size: 12px; margin-top: 3px;">
					<?php echo esc_html($description); ?></div>
			<?php endif; ?>
		</div>
		<div class="csf-fieldset" style="float: left; width: 67%; pointer-events: none; margin: 0;">
			<?php echo $html_control; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<div class="ctl-pro-locked-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 10;">
		</div>
		<div class="clear" style="clear: both;"></div>
	</div>
	<?php
	return ob_get_clean();
}

// Control core classes for avoid errors
if (class_exists('CSF')) {

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$prefix = 'cool_timeline_settings';
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');

	// Create options
	CSF::createOptions(
		$prefix,
		array(
			'framework_title' => 'Timeline Settings',
			'menu_title' => 'Timeline Settings',
			'menu_slug' => 'cool_timeline_settings',
			'menu_type' => 'submenu',
			'menu_parent' => 'cool-plugins-timeline-addon',
			'menu_capability' => 'manage_options', // The capability needed to view the page
			'menu_icon' => CTL_PLUGIN_URL . 'assets/images/cool-timeline-icon.svg',
			'menu_position' => 6,
			'nav' => 'inline',
			'show_reset_section' => false,
			'show_reset_all' => false,
			'show_bar_menu' => false,

			'footer_text' => '',
		)
	);

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$fields = array(



		// Create a Fieldset
		array(
			'id' => 'timeline_header',
			'type' => 'fieldset',
			'title' => 'Content Before Timeline',
			'fields' => array(
				array(
					'id' => 'title_text',
					'type' => 'text',
					'title' => __('Timeline Title', 'cool-timeline'),
				),
			),
		), // End Fieldset



		array(
			'id' => 'first_story_position',
			'type' => 'button_set',
			'title' => 'Vertical Timeline Stories Starts From',
			'desc' => 'Not for Compact and Horizontal layout',
			'options' => array(
				'left' => 'Left',
				'right' => 'Right',
			),
			'default' => 'right',
		),

		array(
			'type'    => 'content',
			'content' => '<div class="ctl-go-pro-wrapper" style="display: flex; justify-content: flex-end; margin-bottom: -20px; position: relative; z-index: 10;">
				<a class="ctl-go-pro-body-link" href="' . esc_url( CTL_BUY_PRO . '?utm_source=ctl_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=general_settings_body_link' ) . '" target="_blank" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important; border: 1px solid #2dd4bf !important; color: #ffffff !important; padding: 6px 16px !important; font-size: 12px !important; font-weight: bold !important; border-radius: 6px !important; box-shadow: 0 3px 10px rgba(20, 184, 166, 0.4) !important; text-decoration: none !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; text-shadow: none !important; line-height: 1.4 !important; transition: all 0.2s ease-in-out;" onmouseover="this.style.background=\'linear-gradient(135deg, #14b8a6 0%, #0d9488 100%)\'; this.style.boxShadow=\'0 5px 15px rgba(20, 184, 166, 0.6)\';" onmouseout="this.style.background=\'linear-gradient(135deg, #0d9488 0%, #0f766e 100%)\'; this.style.boxShadow=\'0 3px 10px rgba(20, 184, 166, 0.4)\';">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; pointer-events: none;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
					<span>' . esc_html__( 'Upgrade to Pro', 'cool-timeline' ) . '</span>
				</a>
			</div>',
		),

		array(
			'id' => 'story_content_pro_teaser',
			'type' => 'fieldset',
			'title' => 'Story Default icon',
			'fields' => array(
				array(
					'type' => 'content',
					'content' => ctl_get_pro_field_teaser_markup(
						'Stories default Icon',
						'',
						'<button class="button button-primary" style="background-color: #007cba; border-color: #007cba; color: #fff;" type="button">Add Icon</button>'
					)
				),
			),
		),

		array(
			'id' => 'story_media_pro_teaser',
			'type' => 'fieldset',
			'title' => 'Story Media',
			'fields' => array(
				array(
					'type' => 'content',
					'content' => ctl_get_pro_field_teaser_markup(
						'Stories Images ?',
						'* Choose theme lightbox if your theme supports an image lightbox .',
						'<label style="margin-right: 15px;"><input type="radio" checked> In Popup( CT Lightbox )</label>
								<label style="margin-right: 15px;"><input type="radio"> In Popup( Theme Lightbox )</label>
								<label style="margin-right: 15px;"><input type="radio"> Story detail link</label>
								<label><input type="radio"> Disable links</label>'
					)
				),
				array(
					'type' => 'content',
					'content' => ctl_get_pro_field_teaser_markup(
						'Stories Slideshow ?',
						'* Enable or Disable Media slider autoplay .',
						'<label style="margin-right: 15px;"><input type="radio"> Enable</label>
								<label><input type="radio" checked> Disable</label>'
					)
				),
				array(
					'type' => 'content',
					'content' => ctl_get_pro_field_teaser_markup(
						'Image Captions ?',
						'Enable or disable image and image slideshow captions.',
						'<label style="margin-right: 15px;"><input type="radio"> Enable</label>
								<label><input type="radio" checked> Disable</label>'
					)
				),
			),
		),
	);

	// Create a section
	CSF::createSection(
		$prefix,
		array(
			'title' => 'General Settings',
			'fields' => $fields,
		)
	);





	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$timeline_express_installed = file_exists(WP_PLUGIN_DIR . '/timeline-express/timeline-express.php');



	if ($timeline_express_installed && !get_option('timeline_express_migrated')) {
		CSF::createSection(
			$prefix,
			array(
				'title' => 'Migration Settings',
				'fields' => array(
					array(
						'id' => 'migration_fieldset',
						'type' => 'fieldset',
						'title' => 'Timeline Express Migration',
						'fields' => array(
							array(
								'id' => 'migrate_stories',
								'type' => 'content',
								'content' => '
										<div class="ctl-buttons-migrate">
										<button class="button button-primary ctl-migrate">Migrate Content</button>
										<div class="ctl-progress-bar">
											<div class="ctl-progress-bar-inner">
											</div>
										</div>
									</div>',

								'desc' => is_plugin_active('timeline-express/timeline-express.php')
									? 'Timeline Express is active. You can migrate stories now.'
									: 'Timeline Express is installed but not active. Please activate it to migrate stories.',


							)
						)
					)
				)
			)
		);
	}

	// Create a section
	CSF::createSection(
		$prefix,
		array(
			'title'  => 'Style Settings',
			'fields' => array(

				array(
					'type'    => 'content',
					'class'   => 'ctl-settings-preview-field',
					'content' => ctl_settings_preview_markup(),
				),

				array(
					'type'    => 'heading',
					'content' => 'Story Colors',
				),
				array(
					'id'      => 'content_bg_color',
					'type'    => 'color',
					'title'   => 'Story Background Color',
					'default' => '#ffffff',
				),

				array(
					'type'    => 'heading',
					'content' => 'Timeline & Year Colors',
				),
				array(
					'id'      => 'circle_border_color',
					'type'    => 'color',
					'title'   => 'Year Background Color',
					'default' => '#38aab7',
				),
				array(
					'id'      => 'line_color',
					'type'    => 'color',
					'title'   => 'Timeline Line Color',
					'default' => '#025149',
				),

				array(
					'type'    => 'heading',
					'content' => 'Alternating Story Colors',
				),
				array(
					'id'      => 'first_post',
					'type'    => 'color',
					'title'   => 'Primary Story Color',
					'desc'    => 'Used for alternating story colors in supported timeline layouts.',
					'default' => '#29b246',
				),
				array(
					'id'      => 'second_post',
					'type'    => 'color',
					'title'   => 'Secondary Story Color',
					'default' => '#ce792f',
				),

				array(
					'type'    => 'heading',
					'content' => 'Custom Styles',
				),
				array(
					'id'       => 'custom_styles',
					'type'     => 'code_editor',
					'title'    => 'Custom Styles',
					'settings' => array(
						'theme' => 'mbo',
						'mode'  => 'css',
					),
				),

			),
		)
	);

	// Create a section
	CSF::createSection(
		$prefix,
		array(
			'title' => 'Typography Setings',
			'fields' => array(

				array(
					'type' => 'content',
					'class' => 'ctl-settings-preview-field',
					'content' => ctl_settings_preview_markup(),
				),

				array(
					'id' => 'ctl_date_typo',
					'type' => 'typography',
					'title' => 'Story Date',
					'default' => array(
						'font-family' => 'Maven Pro',
						'font-size' => '21',
						'line-height' => '',
						'unit' => 'px',
						'type' => 'google',
						'font-weight' => '700',
					),
					'text_align' => false,
					'color' => false,
				),

				array(
					'id' => 'post_title_typo',
					'type' => 'typography',
					'title' => 'Story Title',
					'default' => array(
						'font-family' => 'Maven Pro',
						'font-size' => '20',
						'line-height' => '',
						'unit' => 'px',
						'type' => 'google',
						'font-weight' => '700',
					),
					'color' => false,
				),

				array(
					'id' => 'post_content_typo',
					'type' => 'typography',
					'title' => 'Post Content',
					'default' => array(
						'font-family' => 'Maven Pro',
						'font-size' => '16',
						'line-height' => '',
						'unit' => 'px',
						'type' => 'google',
					),
					'color' => false,
				),


			),


		)
	);

	// Advance Settings
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$advance_fields = array();

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$review_option = get_option('cpfm_opt_in_choice_cool-timeline');

	if (!class_exists('CoolTimelinePro')) {
		$advance_fields[] = array(
			'id' => 'ctl_free_pro_compare',
			'type' => 'content',
			'content' => ctl_get_advance_settings_compare_markup(),
		);
	}

	if ($review_option) {
		$advance_fields[] = array(
			'id' => 'ctl_privacy_settings',
			'type' => 'fieldset',
			'title' => '',
			'fields' => array(
				ctl_get_advance_settings_privacy_field($review_option),
			),
		);
	}

	if (empty($advance_fields)) {
		$advance_fields[] = array(
			'type' => 'content',
			'content' => '<p>' . esc_html__('Advanced options are available in Cool Timeline Pro.', 'cool-timeline') . '</p>',
		);
	}

	CSF::createSection(
		$prefix,
		array(
			'title' => 'Advance Settings',
			'fields' => $advance_fields,
		)
	);


}

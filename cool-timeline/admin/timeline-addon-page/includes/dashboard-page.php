<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 * This page serves as the dashboard template
 */
// do not render this page if it's found outside of the main class
if ( ! isset( $this->main_menu_slug ) ) {
	return false;
}
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$is_active             = false;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$classes               = 'plugin-block';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$is_installed          = false;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$button                = null;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$available_version     = null;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$update_available      = false;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$update_stats          = '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$pro_already_installed = false;

// Let's see if a pro version is already installed
if ( isset( $this->disable_plugins[ $plugin_slug ] ) ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$pro_version = $this->disable_plugins[ $plugin_slug ];
	if ( file_exists( WP_PLUGIN_DIR . '/' . $pro_version['pro'] ) ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$pro_already_installed = true;
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$classes              .= ' plugin-not-required';
	}
}

if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug ) ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$is_installed      = true;
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$plugin_file       = null;
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$installed_plugins = get_plugins();
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$is_active         = false;
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$classes          .= ' installed-plugin';

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	foreach ( $installed_plugins as $plugin => $data ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$thisPlugin = substr( $plugin, 0, strpos( $plugin, '/' ) );
		if ( strcasecmp( $thisPlugin, $plugin_slug ) == 0 ) {
			if ( isset( $plugin_version ) && version_compare( $plugin_version, $data['Version'] ) > 0 ) {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$available_version = $plugin_version;
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$plugin_version    = $data['Version'];
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$update_stats      = '<span class="plugin-update-available">Update Available: v ' . esc_html( $available_version ) . '</span>';
			}

			if ( is_plugin_active( $plugin ) ) {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$is_active = true;
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$classes  .= ' active-plugin';
				break;
			} else {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$plugin_file = $plugin;
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$classes    .= ' inactive-plugin';
			}
		}
	}

	if ( $is_active ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$button = '<button class="button button-disabled">Active</button>';
	} else {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$wp_nonce = wp_create_nonce( 'cp-nonce-activate-' . $plugin_slug );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$button  .= '<button class="button activate-now cool-plugins-addon plugin-activator" data-plugin-tag="' . esc_attr( $tag ) . '" data-plugin-id="' . esc_attr( $plugin_file ) . '" 
        data-action-nonce="' . esc_attr( $wp_nonce ) . '" data-plugin-slug="' . esc_attr( $plugin_slug ) . '">' . esc_html__( 'Activate', 'cool-timeline' ) . '</button>';
	}
} else {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$wp_nonce = wp_create_nonce( 'cp-nonce-download-' . $plugin_slug );
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$classes .= ' available-plugin';
	if ( $plugin_url != null ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$button = '<button class="button install-now cool-plugins-addon plugin-downloader" data-plugin-tag="' . esc_attr( $tag ) . '"  data-action-nonce="' . esc_attr( $wp_nonce ) . '" data-plugin-slug="' . esc_attr( $plugin_slug ) . '">' . esc_html__( 'Install', 'cool-timeline' ) . '</button>';
	} elseif ( isset( $plugin_pro_url ) ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$button = '<a class="button install-now cool-plugins-addon pro-plugin-downloader" href="' . esc_url( $plugin_pro_url ) . '" target="_new">Buy Pro</a>';
	}
}

// Remove install / activate button if pro version is already installed
if ( $pro_already_installed === true ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$pro_ver = $this->disable_plugins[ $plugin_slug ];
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$button  = '<button class="button button-disabled" title="' . esc_attr__( 'This plugin is no longer required as you already have ', 'cool-timeline' ) . esc_html( $pro_ver['pro'] ) . '">' . esc_html__( 'Pro Installed', 'cool-timeline' ) . '</button>';
}

// All PHP condition formation is over here
?>

<div class="<?php echo esc_attr( $classes ); ?>">
  <div class="plugin-block-inner">

	<div class="plugin-logo">
	<img src="<?php echo esc_url( $plugin_logo ); ?>" width="250px" alt="<?php esc_attr__( 'Plugin Logo', 'cool-timeline' ); ?>" /> 
	</div>

	<div class="plugin-info">
	  <h4 class="plugin-title"> <?php echo esc_html( $plugin_name ); ?></h4>
	  <div class="plugin-desc"><?php echo wp_kses_post( $plugin_desc ); ?></div>
	  <div class="plugin-stats">
	  <?php echo wp_kses_post( $button ); ?> 
	  <?php if ( isset( $plugin_version ) && ! empty( $plugin_version ) ) : ?>
		<div class="plugin-version">v <?php echo wp_kses_post( $plugin_version ); ?></div>
			<?php echo wp_kses_post( $update_stats ); ?>
	  <?php endif; ?>
	  </div>
	</div>

  </div>
</div>

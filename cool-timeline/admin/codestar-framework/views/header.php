<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.

  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
  $demo    = get_option( 'csf_demo_mode', false );
  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
  $text    = ( ! empty( $demo ) ) ? 'Deactivate' : 'Activate';
  $status  = ( ! empty( $demo ) ) ? 'deactivate' : 'activate';
  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
  $class   = ( ! empty( $demo ) ) ? ' csf-warning-primary' : '';
  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound,WordPress.Security.NonceVerification.Recommended
  $section = ( ! empty( $_GET[ 'section' ] ) ) ? sanitize_text_field( wp_unslash( $_GET[ 'section' ] ) ) : 'about';
  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
  $links   = array(
    'about'           => 'About',
    'quickstart'      => 'Quick Start',
    'documentation'   => 'Documentation',
    'free-vs-premium' => 'Free vs Premium',
    'support'         => 'Support',
    'relnotes'        => 'Release Notes',
  );

?>
<div class="csf-welcome csf-welcome-wrap">

  <h1>Welcome to Codestar Framework v<?php echo esc_attr( CSF::$version ); ?></h1>

  <p class="csf-about-text">A Simple and Lightweight WordPress Option Framework for Themes and Plugins</p>

  <p class="csf-demo-button"><a href="<?php echo esc_url( add_query_arg( array( 'csf-demo' => $status ) ) ); ?>" class="button button-primary<?php echo esc_attr( $class ); ?>"><?php echo esc_attr( $text ); ?> Demo</a></p>

  <div class="csf-logo">
    <div class="csf--effects"><i></i><i></i><i></i><i></i></div>
    <div class="csf--wp-logos">
      <div class="csf--wp-logo"></div>
      <div class="csf--wp-plugin-logo"></div>
    </div>
    <div class="csf--text">Codestar Framework</div>
    <div class="csf--text csf--version">v<?php echo esc_attr( CSF::$version ); ?></div>
  </div>

  <h2 class="nav-tab-wrapper wp-clearfix">
    <?php

      // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
      foreach ( $links as $key => $link ) {

        if ( CSF::$premium && $key === 'free-vs-premium' ) { continue; }

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
        $activate = ( $section === $key ) ? ' nav-tab-active' : '';

        echo '<a href="'. esc_url( add_query_arg( array( 'page' => 'csf-welcome', 'section' => $key ), admin_url( 'tools.php' ) ) ) .'" class="nav-tab'. esc_attr( $activate ) .'">'. esc_attr( $link ) .'</a>';

      }

    ?>
  </h2>

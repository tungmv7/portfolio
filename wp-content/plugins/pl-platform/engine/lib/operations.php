<?php
/**
 * Installation and Activation Operations & Other file related actions.
 *
 * @class     PL_Platform_Operations
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Platform_Operations {

  function __construct( PL_Globals $platform ) {
    $this->platform = $platform;
  }

  function init() {
    add_filter( 'plugins_api',                      array( $this, 'installation_api_intercept' ), 99999, 3 );
    add_filter( 'themes_api',                       array( $this, 'installation_api_intercept' ), 99999, 3 );
    add_filter( 'install_plugin_complete_actions',  array( $this, 'complete_action' ), 10, 3 );
    add_filter( 'install_theme_complete_actions',   array( $this, 'complete_action' ), 10, 4 );
    add_action( 'admin_head-update.php',            array( $this, 'extension_ftp_fix' ) );
  }

  /**
   * Fix issue with WordPress FTP credential form stripping params from URL
   */
  function extension_ftp_fix() {

    // if no pl GET param then do nothing. Or if using direct fs
    if ( ! isset( $_GET['pl'] ) || 'direct' == get_filesystem_method() ) {
      return false; }

    // The WordPress FTP form strips our custom parameters for fetching the zip
    // So we need to add these back in.
    ?><script>
      ! function($) {
        $(document).ready(function(){
          var url = window.location.href
          var form = $('#request-filesystem-credentials-form')
          if( $(form).length ) {
            $(form).closest('form').attr('action',url)
          }
        })
      }(window.jQuery)
    </script>
    <?php
  }

  /**
   * If the pl_install var is set, then we know its a PL product.
   * Intercept the APIs with a hook and replace the product with our information including
   * download url, title, version, etc..
   */
  function installation_api_intercept( $api, $action, $args ) {

    if ( ! isset( $_GET['pl'] ) ) {
      return $api; }

    $api                = new stdClass();
    $api->name          = $_GET['name'];
    $api->version       = $_GET['version'];
    $api->download_link = base64_decode( $_GET['download_link'] );
    $api->pl            = $_GET['pl'];

    return $api;

  }

  /**
   * Add link on install complete page.
   */
  function complete_action( $install_actions, $api, $theme ) {

    if ( isset( $api->pl ) && 'none' != $api->pl ) {

      $return = sprintf( '<a href="%s">More PageLines Extensions &rarr;</a>', add_query_arg( array( 'refresh' => 1 ), PL_Platform()->url( 'extend' ) ) );

      $install_actions['plugins_page']  = $return;
    }

    return $install_actions;

  }

  /**
   * Generate an install URL
   */
  function install_url( $args ) {

    if ( ! is_string( $args['download_link'] ) ) {
      return 'No Download Link';
    }

    $plugin_file = sprintf( '%s/%s.php', $args['slug'], $args['slug'] );

    $defaults = array(
        'version' => '(Latest Version)',
        'pl'      => 1,
        'theme'   => $args['slug'],
        'plugin'  => urlencode( $args['slug'] ),
        'action'  => sprintf( 'install-%s', $args['install_type'] ),
      );

    $args = wp_parse_args( $args, $defaults );

    $args['download_link'] = base64_encode( $args['download_link'] );

    $base_url = add_query_arg( $args, network_admin_url( 'update.php' ) );

    // upgrades use a different nonce setup
    if ( isset( $args['installed'] ) && $args['installed'] ) {
      if ( 'plugin' == $args['install_type'] ) {
        $file = $plugin_file;
      } else {
        $file = $args['slug'];
      }
      // taken directly from WP so this works.
      $admin_url    = sprintf( 'update.php?action=upgrade-%s&%s=%s', $args['install_type'], $args['install_type'], $file );
      $nonce        = sprintf( 'upgrade-%s_%s', $args['install_type'], $file );
      $url          = wp_nonce_url( network_admin_url( $admin_url ), $nonce );
    } else {
      $url          = wp_nonce_url( $base_url, sprintf( '%s_%s', $args['action'], $args['slug'] ) );
    }
    return $url;
  }

  function platform_install_link() {
    $args = array(
      'install_type'   => 'plugin',
      'name'           => 'PageLines Platform 5',
      'download_link'  => $this->get_build_link( 'pl-platform' ),
      'slug'           => 'pl-platform',
      'installed'      => false,
      'pl'             => 'none',
    );
    return $this->install_url( $args );
  }

  /**
   * This function can be used to install a 'GET FRAMEWORK' style button anywhere.
   */
  function framework_install_url() {

    $args = array(
      'install_type'   => 'theme',
      'name'           => 'PageLines Framework',
      'download_link'  => $this->get_build_link( 'pl-framework' ),
      'slug'           => 'pl-framework',
    );
    return $this->install_url( $args );
  }
  function get_build_link( $slug ) {
    return sprintf( 'http://deploy.pagelines.io/build/%s.zip', $slug );
  }

  /**
   * Check if any pro plugins are active.
   */
  function check_pro_plugins() {

    // get array of PageLines plugins.
    $plugins = PL_Platform()->functions->pl_get_plugins( true );

    $count   = 0;

    include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); // need this to see if plugin is active.

    foreach ( $plugins as $key => $plugin ) {
      $cats = explode( ', ', $plugin['Category'] );
      if ( in_array( 'pro', $cats ) && is_plugin_active( $key ) ) {
        $count++;
      }
    }
    return $count;
  }

  /**
   * Get the number of PageLines plugins
   */
  function pl_plugins_count() {

    // get array of PageLines plugins.
    $plugins = PL_Platform()->functions->pl_get_plugins( true );

    return count( $plugins );
  }
}

<?php
/**
 * Global functions used in Platform
 *
 * @class     PL_Platform_Functions
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Platform_Functions {

  function __construct( PL_Platform $platform ) {
    $this->platform = $platform;
  }

  /**
  * Get plugins and optionally filter out WP plugins
  */
  static function pl_get_plugins( $filter = false, $pro = false ) {

    $default_headers = array(
     'Version'      => 'Version',
     'PageLines'    => 'PageLines',
     'Plugin Name'  => 'Plugin Name',
     'Description'  => 'Description',
     'Version'      => 'Version',
     'Category'     => 'Category',
    );

    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    $plugins = get_plugins();

    if ( ! $filter ) {
      return $plugins;
    }
    // get the headers for each plugin file
    foreach ( $plugins as $path => $data ) {
      $fullpath = sprintf( '%s%s', trailingslashit( WP_PLUGIN_DIR ), $path );
      $plugins[ $path ] = get_file_data( $fullpath, $default_headers );
    }
    // if the headers do not contain 'PageLines' then unset from the array and let WP handle them
    foreach ( $plugins as $path => $data ) {
      if ( ! $data['PageLines'] ) {
        unset( $plugins[ $path ] );
      }
    }

    // only look for pro plugins
    if ( $pro ) {
      foreach ( $plugins as $path => $data ) {

        $cats = array_map( 'trim', explode( ',', $data['Category'] ) );

        if ( ! array_search( 'pro', $cats ) ) {
          unset( $plugins[ $path ] );
        }
      }
      return $plugins;
    }

    return $plugins;
  }

  /**
  * Cache something into a transient, default is one day
  */
  function cache_set( $args ) {

    $defaults = array(
     'expires'  => DAY_IN_SECONDS,
     'slug'     => '',
     'data'     => '',
    );
    $args = wp_parse_args( $args, $defaults );

    // no slug set or no data
    if ( ! $args['slug'] || ! $args['data'] ) {
      return false;
    }
    return set_transient( $args['slug'], $args['data'], $args['expires'] );
  }

  /**
   * Fetch from cache
   */
  function cache_get( $slug ) {
    return get_transient( $slug );
  }

  /**
   * Clear cache
   */
  function cache_clear( $slug ) {
    return delete_transient( $slug );
  }
}

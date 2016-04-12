<?php
/**
 * Extensions Updates Handler
 *
 * Handles Updates for plugins/themes installed via Platforms Extend Menu
 *
 * ALL UPDATES FOR THE CORE PLUGIN WILL BE DEFFERED TO STANDARD WORDPRESS UPDATES
 *
 * Only PageLines Plugins/Themes installed from the extension menu that are not and/or cannot
 * be hosted on wordpress.org are updated, all normal plugin/theme updates are simply returned
 * in the filters.
 *
 * @class     PL_Platform_Updater
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Platform_Updater {

  var $cache_slug = 'connect_updates';

  function __construct() {

    add_action( 'load-update-core.php',                  array( $this, 'updates_page_init' ) );

    // filter the site transients to inject updates data
    add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'injectUpdatePlugins' ), 20000 );
    add_filter( 'pre_set_site_transient_update_themes',  array( $this, 'injectUpdateThemes' ), 20000 );

    // filter the plugins api to show plugin info popup
    add_filter( 'plugins_api',                           array( $this, 'check_info_popup' ), 10, 3 );
  }

  /**
   * Code to run on update-core.php
   */
  function updates_page_init() {

    // if the user has clicked 'Check Again' then clear the updates data
    if ( isset( $_REQUEST['force-check'] ) ) {
      $this->clear_cache();
    }
  }

  /**
   * This is used on main WP plugins and themes page to display info about theme/plugin
   * So we add our info too. It draws the modal window when you click update info link.
   */
  function check_info_popup( $false, $action, $arg ) {

    $data = $this->updates_data();

    // sanity checks, if we dont have the required data then just return false
    if ( ! is_object( $arg ) || ! is_array( $data ) || ! isset( $arg->slug ) || ! isset( $data[ $arg->slug ] ) || defined( 'PLWPORG' ) ) {
      return false; }

    // build the data object for the WP model
    $slug = $arg->slug;
    $data = $data[ $slug ];

    $count  = ( isset( $data->download_count ) ) ? $data->download_count : 0;
    $rating = ( isset( $data->rating ) ) ? ( (int) $data->rating / 5 ) * 100 : 0;
    $reviews = ( isset( $data->reviews ) ) ? (int) $data->reviews : 0;

    $obj = new stdClass();
    $obj->slug          = $data->slug;
    $obj->plugin_name   = $data->post_title;
    $obj->name          = $data->post_title;
    $obj->new_version   = $data->version;
    $obj->requires      = '4.0';
    $obj->tested        = '4.5';
    $obj->active_installs = $count;
    $obj->last_updated  = $data->post_modified;
    $obj->rating        = $rating;
    $obj->num_ratings   = $reviews;
    $obj->sections      = array(
                            'description' => $data->post_content,
                        );
    $obj->homepage = $data->product_link;
    return $obj;
  }

  /**
   * Maybe add update info to a theme hosted on pagelines.com
   * wordpress.org themes are always handled by WordPress.
   */
  function injectUpdateThemes( $updates ) {

    $data = $this->updates_data();
    // make sure we have data
    if ( ! is_array( $data ) ) {
      return $updates; }

    // get all themes, match themes with updates data
    $themes = wp_get_themes( array( 'errors' => false ) );

    $installed = array();

    foreach ( $themes as $slug => $theme ) {
      if ( isset( $data[ $slug ] ) ) {
        $themedata = wp_get_theme( $slug );
        $installed[ $slug ] = $themedata->get( 'Version' );
      }
    }

    foreach ( $installed as $slug => $version ) {

      // if remote version is greater than installed version then inject an update
      if ( version_compare( $data[ $slug ]->version, $version, '>' ) ) {

        // we have an update..

        // If we are not logged in, bail with a message.
        if ( ! PL_Platform()->oauth->is_connected() ) {
          add_action( 'admin_notices', array( $this, 'notice_updates_needed' ) );
          return $updates;
        }

        $url = $this->get_download_link( $data[ $slug ]->download_data );
        $args = array(
          'theme'          => $slug,
          'new_version'    => $data[ $slug ]->version,
          'url'            => $data[ $slug ]->product_link,
          'package'        => $url,
        );
        $updates->response[ $slug ] = (array) $this->build_update( $args );
        continue;
      }
    }
    return $updates;
  }

  /**
   * Show a notice to users if they have pending updates but are not connected.
   */
  function notice_updates_needed() {

    global $notice_updates_needed;

    // action has already run.. no need for more than 1 notice.
    if ( isset( $notice_updates_needed ) ) {
      // return false;
    }
    $notice_updates_needed = true;

    $link = sprintf( '<a href="%s" class="button button-primary">%s &rarr;</a>',
        PL_Platform()->oauth->connect_account_link(),
        __( 'Add PageLines Account', 'pl-platform' )
    );

    $text = __( 'You appear to have one or more pending updates for PageLines extensions.', 'pl-platform' );

    pl_create_notice( array(
        'title'     => __( 'PageLines Account Missing', 'pl-platform' ),
        'msg'       => $text,
        'action'    => $link,
        'icon'      => 'warning',
    ));
  }

  /**
   * Get download link
   */
  static function get_download_link( $data ) {
    // reset, in case there are more than one
    reset( $data );
    // get the key
    $key = key( $data );
    // return the url now we know the key
    if ( isset( $data->{$key}->file ) ) {
      return add_query_arg( array( 'nostats' => 1 ), $data->{$key}->file );
    }
  }

  /**
   * Build an update object
   */
  function build_update( $args ) {

    $defaults = array(
      'slug'            => '',
      'new_version'     => '',
      'upgrade_notice'  => '',
      'package'         => '',
      'download_link'   => '',
      'plugin'          => '',
      'downloads'       => '',
      'active_installs' => '',
    );
    $args = wp_parse_args( $args, $defaults );

    // WP updates needs this to be an object
    $out = new stdClass;
    foreach ( $args as $k => $setting ) {
      $out->$k = $setting;
    }
    return $out;
  }

  /**
   * Maybe add update info for a plugin hosted by PageLines.
   * All plugins hosted on wordpress.org are handled by WordPress.
   *
   * NOTE: This plugin pl-platform is also deffered to wordpress.org because it is hosted there.
   */
  function injectUpdatePlugins( $updates ) {

    $data = $this->updates_data();

    // make sure we have data
    if ( ! is_array( $data ) ) {
      return $updates; }

    $pl_plugins = PL_Platform()->functions->pl_get_plugins( true );

    foreach ( $pl_plugins as $path => $plugin ) {
      $slug = dirname( $path );

      // NOTE if PLWPORG is defined do not try and update, defer to wp.org
      if ( defined( 'PLWPORG' ) && 'pl-platform' == $slug ) {
        continue;
      }

      // If PageLines plugin has no API data pass on it.
      // we dont want updates from wordpress for PL plugins
      if ( ! isset( $data[ $slug ] ) ) {
        if ( is_object( $updates ) && isset( $updates->response ) && isset( $updates->response[ $path ] ) ) {
          unset( $updates->response[ $path ] );
          continue;
        }
      }

      // If PageLines plugin has API data and a version check it and build a response.
      if ( isset( $data[ $slug ]->version ) && version_compare( $data[ $slug ]->version, $plugin['Version'], '>' ) ) {

        // we have an update..

        // If we are not logged in, bail with a message.
        if ( ! PL_Platform()->oauth->is_connected() ) {
          add_action( 'admin_notices', array( $this, 'notice_updates_needed' ) );
          return $updates;
        }

        $url = $this->get_download_link( $data[ $slug ]->download_data );

        $args = array(
          'slug'            => $slug,
          'new_version'     => $data[ $slug ]->version,
          // 'upgrade_notice'  => $data[$slug]->changelog,
          'package'         => $url,
          'download_link'   => $url,
          'plugin'          => $path,
          'downloads'       => 10000,
          'active_installs' => 383829,
        );

        $updates->response[ $path ] = $this->build_update( $args );
        continue;
      }
    }
    return $updates;
  }

  /**
   * Get the users products and versions
   */
  function updates_data( $raw = false ) {

    // fetch data
    $data = $this->get_cached_data();

    // if we dont have data, fetch new and then save if valid
    if ( ! $data ) {
      // fetch store data from api, set posts_per_page to -1, we dont want paging
      $data = PL_Platform()->oauth->request('updates', array(
          'posts_per_page' => '-1',
          'rnd'            => PL_Platform()->config['rand'],
      ));

      // store the data if valid
      if ( is_array( $data ) && count( (array) $data ) > 1 ) {
        // we want to trim the data, its far too big otherwise..
        $vars = array(
          'slug',
          'post_name',
          'post_title',
          'version',
          'changelog',
          'post_modified',
          'post_content' => array(
            'func' => array( $this, 'getExcerpt' ),// run the content through a filter to trim length
          ),
          'product_link',
          'download_count',
          'reviews',
          'rating',
          'download_data'
        );

        $data = $this->rework_data( $data, $vars );

        // store the data
        $this->store_cached_data( $data );

        // return rawdata
        if ( $raw ) {
          return $data;
        }
      }
    }

    // loop through the array data, we want the slugs as keys so they are easy to find.
    foreach ( (array) $data as $k => $product ) {
      if ( isset( $product->slug ) ) {
        $slug = $product->slug;
        $data[ $slug ] = $product;
        unset( $data[ $k ] );
      }
    }
    return $data;
  }

  /**
   * Trim the dataset down, far too much uneeded data for updates.
   */
  function rework_data( $data, $vars ) {

    $cleaned = array();

    // loop through the array of objects
    foreach ( $data as $k => $object ) {
      $cleaned[ $k ] = new stdClass;

      // loop through the varibles to allow
      foreach ( $vars as $key => $var ) {

        // some varibles might have a callback...
        if ( is_array( $var ) && isset( $var['func'] ) && isset( $object->{$key} ) ) {
          $cleaned[ $k ]->{$key} = call_user_func( $var['func'], $object->{$key} );
        } // if not just set the variable
        else {
          if ( isset( $object->{$var} ) ) {
            $cleaned[ $k ]->{$var} = $object->{$var};
          } else {
            $object->{$var} = '';
          }
        }
      }
    }
    // return the cleaned array of objects
    return $cleaned;
  }

  /**
   * Fetch data from cache
   */
  function get_cached_data() {
    return PL_Platform()->functions->cache_get( $this->cache_slug );
  }

  /**
   * Store data in cache
   */
  function store_cached_data( $data ) {

    $args = array(
      'slug'  => $this->cache_slug,
      'data'  => $data,
    );
    return PL_Platform()->functions->cache_set( $args );
  }

  /**
   * Clear the cache
   */
  function clear_cache() {
    return PL_Platform()->functions->cache_clear( $this->cache_slug );
  }

  /**
  * Get excerpt from string
  *
  * @param String $str String to get an excerpt from
  * @param Integer $startPos Position int string to start excerpt from
  * @param Integer $maxLength Maximum length the excerpt may be
  * @return String excerpt
  */
  function getExcerpt( $str, $startpos = 0, $maxlength = 350 ) {
    if ( strlen( $str ) > $maxlength ) {
      $str = strip_tags( $str );
      $excerpt   = substr( $str, $startpos, $maxlength -3 );
      $lastspace = strrpos( $excerpt, ' ' );
      $excerpt   = substr( $excerpt, 0, $lastspace );
      $excerpt  .= '...';
    } else {
      $excerpt = $str;
    }
    return $excerpt;
  }
} // end of class

global $pl_platform_updates;
$pl_platform_updates = new PL_Platform_Updater;

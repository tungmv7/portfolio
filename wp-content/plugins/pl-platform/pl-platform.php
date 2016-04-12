<?php
/*
  Plugin Name:  PageLines Platform 5
  Description:  An insanely-fast real time design platform for WordPress.

  Version:      5.0.134

  Author:       PageLines
  Author URI:   http://www.pagelines.com

  PageLines:    true

  Tags:         account, extend, settings

  Category:     platform, plugins

  Filter:       pagelines

  Network:      true
  Text Domain:  pl-platform
  Domain Path:  /languages
*/

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}

/**
* Declare WPORG version.
*/
define( 'PLWPORG', true );

class PL_Platform {

  function __construct() {

    $this->set_config();
    $this->include_engine();

    register_activation_hook( __FILE__,     array( $this, 'platform_plugin_activate' ) );

    add_action( 'wp_loaded',                array( $this, 'include_files' ), 5 );
    add_action( 'wp_loaded',                array( $this, 'initialize_components' ), 5 );
    add_action( 'pl_ab_menu',               array( $this, 'add_framework_menu_link' ) );
    add_filter( 'views_plugins',            array( $this, 'add_sorting' ) );
    add_filter( 'body_class',               array( $this, 'body_class' ) );
    add_action( 'activated_plugin',         array( $this, 'this_plugin_first' ) );
    add_action( 'admin_print_styles',       array( $this, 'refine_notices' ), 1000 );
    add_action( 'add_meta_boxes',           array( $this, 'add_meta_interface' ) );
    add_action( 'show_user_profile',        array( $this, 'build_interface_profile' ) );
    add_action( 'edit_user_profile',        array( $this, 'build_interface_profile' ) );
    add_action( $this->config['menu_hook'], array( $this, 'add_wp_menus' ), 15 );

    add_action( 'admin_init',               array( $this, 'redirect_to_welcome' ) );
    add_filter( 'plugin_row_meta',          array( $this, 'plugin_row_meta' ), 10, 2 );

    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

    add_action( 'plugins_loaded',           array( $this, 'load_textdomain' ) );
    // require_once( 'lib/customizer.php' );
    // require_once( 'lib/widgets.php' );
    /** Requests / AJAX / Saving. Always load.  */
    new PL_Platform_Save_Actions();
  }

  /**
   * Load Language files.
   */
  function load_textdomain() {
      load_plugin_textdomain( 'pl-platform', false, basename( dirname( __FILE__ ) ) . '/languages/' );
  }

  function platform_plugin_activate() {
    add_option( 'platform_plugin_do_activation_redirect', true );
  }

  function redirect_to_welcome() {
    if ( get_option( 'platform_plugin_do_activation_redirect', false ) ) {
      delete_option( 'platform_plugin_do_activation_redirect' );
      if ( ! isset( $_GET['activate-multi'] ) ) {
          wp_redirect( PL_Platform()->url( 'settings', array( 'initial' => true ) ) );
          exit();
      }
    }
  }

  /**
   * Adds links to plugin item in WP plugins page
   */
  function plugin_row_meta( $links, $file ) {

    if ( plugin_basename( __FILE__ ) == $file ) {

      $row_meta = array(
      'docs'    => sprintf( '<a href="%s">%s</a>', PL()->urls->docs, __( 'Docs', 'pl-platform' ) ),
      'support' => sprintf( '<a href="%s">%s</a>', PL()->urls->support, __( 'Support', 'pl-platform' ) ),
      );

      return array_merge( $links, $row_meta );
    }

      return (array) $links;
  }

  /**
   * Adds action link under plugin item in WP plugin page
   */
  function plugin_action_links( $links ) {
    $action_links = array(
      'settings' => '<a href="' . PL_Platform()->url( 'settings' ) . '">' . __( 'Settings', 'pl-platform' ) . '</a>',
    );

    return array_merge( $action_links, $links );
  }

  function set_config() {
    
    $this->base_dir           = plugin_dir_path( __FILE__ );
    $this->slug               = basename( $this->base_dir );
    $this->slug_framework     = 'pl-framework';

    $this->base_url = plugins_url( '', __FILE__ );

    $this->images = $this->base_url . '/engine/ui/images';

    $this->config = array(
        'slug'      => $this->slug,
        'plugin'    => __FILE__,
        'name'      => __( 'PageLines Platform', 'pl-platform' ),
        'menu_slug' => 'PageLines-Admin',
        'rand'      => rand(),
        'icon'      => $this->images . '/admin-icon.png',
        'priv'      => 'edit_theme_options',
        'ui'        => array( $this, 'build_interface' ),
        'mui'       => array( $this, 'build_interface_meta' ),
        'menu_hook' => ( is_multisite() && is_network_admin() ) ? 'network_admin_menu' : 'admin_menu',
      );
  }

  function include_engine() {
    $engine_url = $this->base_url . '/engine';
    $engine_dir = $this->base_dir . '/engine';
    require_once( 'engine/engine.php' );

  }

  /**
   * Add pl-platform as a global body_class.
   */
  function body_class( $classes ) {
    $classes[] = 'pl-platform';
    return $classes;
  }

  /**
   * Makes sure that Connect loads first of all plugins,
   * Allows us to assume its loaded in extensions/engines
   */
  function this_plugin_first() {
    // ensure path to this file is via main wp plugin path
    $wp_path_to_this_file = preg_replace( '/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR.'/$2', __FILE__ );

    $this_plugin          = plugin_basename( trim( $wp_path_to_this_file ) );
    $active_plugins       = get_option( 'active_plugins' );

    $this_plugin_key = array_search( $this_plugin, $active_plugins );

    // if it's 0 it's the first plugin already, no need to continue
    if ( $this_plugin_key ) {

      array_splice( $active_plugins, $this_plugin_key, 1 );
      array_unshift( $active_plugins, $this_plugin );
      update_option( 'active_plugins', $active_plugins );

    }
  }


  /**
   * Refine admin notices, have to do this before the UI is actually loaded.
   */
  function refine_notices() {

    // make sure we use the correct action
    $action = ( is_multisite() ) ? 'network_admin_notices' : 'admin_notices';

    /** We know we're on a Connect page, adjust things... */
    if ( isset( $_GET['page'] ) && false !== strpos( $_GET['page'], $this->slug ) ) {

      remove_all_actions( 'admin_notices' );
    }

    // only network admins and single install admins can install/activate etc..
    if ( ! pl_can_use_tools( 'update_core' ) ) {
      return false;
    }

    // if on WP updates page, dont show messages
    $screen = get_current_screen();
    if ( 'update' == $screen->id ) {
      return false;
    }

    /** Number of plugins installed */
    $pl_plugins = PL_Platform()->functions->pl_get_plugins( true );

    $is_core_page = ('account' == $this->page() || 'extend' == $this->page() || 'settings' == $this->page()) ? true : false;

    /** NOTICES START HERE */

    if ( ! $this->oauth->is_connected()  ) {

      if ( ! $is_core_page ) {
        add_action( $action, array( $this, 'connect_account_notice' ) );
      }
    } elseif ( isset( $_GET['connected'] ) ) {

      add_action( $action, array( $this, 'notice_connected' ) );

    } elseif ( ! $this->has_installed_something() ) {

      if ( 'extend' != $this->page() ) {
        add_action( $action, array( $this, 'notice_install_something' ) );
      }
    } elseif ( count( $pl_plugins ) > 1 && ! get_option( 'pl_notice_installed_first' ) ) {

      add_action( $action, array( $this, 'installed_first_plugin' ) );

    } elseif ( ! $this->is_pro() ) {

      add_action( $action, array( $this, 'notice_upgrade_site' ) );

    }

    // elseif( ! PL_Platform()->is_framework_installed() && ! get_option('pl_notice_install_framework') ) {
    // add_action( $action, array( $this, 'notice_install_framework'  ) );
    // }
    // if user has pro plugins and is not pro this notice gets displayed
    if ( ! $this->is_pro() && $this->oauth->is_connected() ) {

      add_action( $action, array( $this, 'notice_pro_plugins' ) );

    }

  }

  function installed_first_plugin() {

    pl_create_notice( array(
        'title'   => __( 'Using PageLines Menus and Tools', 'pl-platform' ),
        'msg'     => sprintf( __( 'Use the PageLines logo %s in the admin bar to access PageLines front-end tools. You will find new sections in the page editor.', 'pl-platform' ), '(<i class="pl-icon pl-icon-pagelines"></i>)' ),
        'alink'   => PL()->urls->editor,
        'atext'   => __( 'View Page Editor', 'pl-platform' ),
        'exp'     => 'option',
        'icon'    => 'list-alt',
        'id'      => 'installed_first',
    ));

  }


  /**
   * Notice shown on non PL Connect pages.
   */
  function connect_account_notice() {

    pl_create_notice( array(
        'title'     => __( 'PageLines Setup Step 1: Add Your PageLines Account', 'pl-platform' ),
        'msg'       => sprintf( '%s.',
            __( 'Welcome! Add your account for one-click extensions and updates.', 'pl-platform' )
        ),
        'alink'    => $this->oauth->connect_account_link(),
        'atext'    => __( 'Add Your PageLines Account', 'pl-platform' ),
        'icon'      => 'plus',
    ));
  }

  /**
   *
   * Show this notice if they've never installed something
   */
  function notice_install_something() {

    $url = ( ! $this->is_pro() ) ? $this->url( 'extend' ) . '&special=multicat&special_key=framework__free&navitem=extensions_free' : $this->url( 'extend' );

    pl_create_notice( array(
          'title'   => __( 'PageLines Setup Step 2: Add First Section or Plugin', 'pl-platform' ),
          'msg'     => __( 'PageLines is addon based. Install some items with the extension engine.', 'pl-platform' ),
          'alink'   => $url,
          'atext'   => __( 'Install First Extension', 'pl-platform' ),
          'exp'     => 2 * DAY_IN_SECONDS,
          'icon'    => 'download',
    ));
  }

  /**
   * Show a warning if site is not registered but 'pro' plugins are activated.
   */
  function notice_pro_plugins() {

    $plugins = PL()->operations->check_pro_plugins();

    // Do not show notice if no pro plugins.
    if ( ! $plugins ) {
      return false;
    }

    $text = sprintf( __( 'There are %s professional extensions %s active, it is important to pro activate your site to use them.', 'pl-platform' ),
        $plugins,
        _n( 'plugin', 'plugins', $plugins, 'pl-platform' )
    );

    pl_create_notice( array(
        'title'     => __( 'PageLines Pro Activation Required', 'pl-platform' ),
        'msg'       => $text,
        'alink'     => PL_Platform()->oauth->domain_activate_link(),
        'atext'     => __( 'Activate Pro', 'pl-platform' ),
        'icon'      => 'warning',
    ));
  }

  /**
   * Notice shown on non PL Connect pages.
   */
  function notice_install_framework() {

    pl_create_notice( array(
        'title'   => __( 'Use PageLines Framework?', 'pl-platform' ),
        'msg'     => __( 'PageLines Framework is a simple and beautiful theme optimized for use with PageLines Platform.', 'pl-platform' ),
        'alink'   => PL()->operations->framework_install_url(),
        'atext'   => __( 'Install', 'pl-platform' ),
        'exp'     => 'option',
        'icon'    => 'file-text-o',
        'id'      => 'install_framework',
    ));

  }

  /**
   * Notice shown on non PL Connect pages.
   */
  function notice_connected() {

    $message = array(
      'title' => __( 'Success!', 'pl-platform' ),
      'icon'  => 'check',
      'msg'   => __( 'Your site is successfully connected to PageLines.com', 'pl-platform' ),
    );

    if ( ! $this->has_installed_something() ) {

      $message['alink'] = PL_Platform()->url( 'extend' );
      $message['atext'] = 'Next Step: Install First Extension';
    } elseif ( ! $this->is_pro() ) {

      $message['action'] = $this->step_activation_action();

    }

    pl_create_notice( $message );

  }

  function step_activation_action() {

    $dformat = PL_Platform()->oauth->get_domain_data_format();

    $action = '';

    $action .= '<a class="button button-secondary button-disabled">1. Add Account <i class="pl-icon pl-icon-check"></i></a>';

    $action .= '<a class="button button-secondary button-disabled">2. Install First Extension <i class="pl-icon pl-icon-check"></i></a>';

    if ( ! PL_Platform()->oauth->can_register_site() ) {
      $action .= sprintf( '<a class="button button-primary" href="%s">3. Get License <i class="pl-icon pl-icon-shopping-cart"></i></a>', PL()->urls->purchase );

      $action .= sprintf( '<a class="button button-primary button-disabled">4. Activate Pro %s <i class="pl-icon pl-icon-bolt"></i></a>', $dformat );
    } else {
      $action .= '<a class="button button-primary button-disabled">3. Get License <i class="pl-icon pl-icon-check"></i></a>';

      $action .= sprintf( '<a class="button button-primary" href="%s">4. Activate Pro %s <i class="pl-icon pl-icon-bolt"></i></a>', PL_Platform()->oauth->domain_activate_link(), $dformat );
    }

    return $action;

  }

  /**
   * Notice shown on non PL Connect pages.
   */
  function notice_upgrade_site() {

    pl_create_notice( array(
          'title'   => __( 'Get the most out of PageLines...', 'pl-platform' ),
          'msg'     => sprintf( '%s <strong>%s</strong> %s',
              __( 'You are all set up! To get monthly', 'pl-platform' ),
              __( 'pro', 'pl-platform' ),
              __( 'extensions, updates, options and support just Pro activate your site.', 'pl-platform' )
          ),
          'action'  => $this->step_activation_action(),
          'id'      => 'upgrade_pro',
          'exp'     => 5 * DAY_IN_SECONDS,
          'icon'    => 'plus',
    ));
  }



  /**
   * Create the notice
   */
  // function create_notice( $args = array() ){
  // pl_create_notice( $args );
  // }
  /**
   * Include core files
   */
  function include_files() {

    $inc = array(
      'upgrade',
      'functions.connect',
      'oauth',
      'updates',
      'settings',
      'shortcodes',
      'account',
      'extend',
      'ui',
    );

    foreach ( $inc as $file ) {
      require_once( 'lib/' . $file . '.php' );
    }
  }

  /**
   * Load dependencies
   */
  function initialize_components() {

    /** Connections to PageLines.com Server  */
    $this->oauth      = new PL_Platform_OAuth( $this );

    $this->extend     = new PL_Platform_Extend;

    $this->functions  = new PL_Platform_Functions( $this );

    pl_add_image_sizes();

    do_action( 'pl_platform_loaded' );
  }

  /**
   * Add main WP menus
   */
  function add_wp_menus() {

    /** Main Menu Item */
    add_menu_page( 'PageLines', 'PageLines', $this->config['priv'], $this->page_slug( 'settings' ),  $this->config['ui'], false, '2.4' );

    $menus = array(
      'settings'  => true,                                // Setting page should always show.
      'extend'    => ( is_super_admin() ) ? true : false, // only show for super admins
      'account'   => ( is_super_admin() ) ? true : false,// only show for super admins
    );

    // loop through items and add menus if $allowed == true
    foreach ( apply_filters( 'pl_platform_menus', $menus ) as $slug => $allowed ) {
      if ( $allowed ) {
        add_submenu_page( $this->page_slug( 'settings' ), pl_ui_key( $slug ), pl_ui_key( $slug ), $this->config['priv'], $this->page_slug( $slug ), $this->config['ui'] );
      }
    }
  }

  /**
   * Add items to PL dropdown menus
   */
  function add_framework_menu_link( $m ) {
    $m['settings'] = array(
      'pos'     => 160,
      'id'      => 'pl-ab-settings',
      'title'   => sprintf( '<i class="pl-icon pl-icon-cog"></i> %s', __( 'Settings', 'pl-platform' ) ),
      'href'    => $this->url( 'settings' ),
    );

    $m['extend'] = array(
      'pos'     => 200,
      'id'      => 'pl-ab-extend',
      'title'   => sprintf( '<i class="pl-icon pl-icon-plug"></i> %s', __( 'Extend', 'pl-platform' ) ),
      'href'    => $this->url( 'extend' ),
      'priv'    => 'install_plugins',
    );

    $m['account'] = array(
      'pos'     => 250,
      'id'      => 'pl-ab-account',
      'title'   => sprintf( '<i class="pl-icon pl-icon-user"></i> %s', __( 'Account', 'pl-platform' ) ),
      'href'    => $this->url( 'account' ),
      'priv'    => 'install_plugins',
    );

    return apply_filters( 'pl_platform_dropdown_menus', $m );
  }

  function add_meta_interface() {

    $meta_config = $this->get_config_array( 'meta', get_current_screen()->post_type );

    if ( ! empty( $meta_config['pagelines_settings']['groups'] ) ) {

      add_meta_box(
          $this->slug . '-meta',
          __( 'PageLines Meta Settings', 'pl-platform' ),
      $this->config['mui']);

    }
  }

  /**
   * create page slug
   */
  function page_slug( $page = '' ) {

    if ( '' != $page ) {
      $page = '-' . $page; }

    return $this->slug . $page;

  }

  function page() {

    if ( isset( $_GET['page'] ) ) {
      $page = str_replace( $this->slug . '-', '', $_GET['page'] );
    } else {
      $page = false;
    }
    return $page;
  }

  function tab() {

    $page = ( isset( $_GET['tab'] ) && 'default' != $_GET['tab'] ) ? $_GET['tab'] : $this->page();

    return $page;
  }

  /**
   * Generate a url based on admin slug
   */
  function url( $page = 'account', $query_args = array() ) {

    if ( 'buy_platform' == $page ) {
      return PL_Platform()->oauth->url( 'buy_platform' );
    }

    $url = admin_url( 'admin.php?page=' . $this->page_slug( $page ) );

    return add_query_arg( $query_args, $url );

  }


  /**
   * Build Extension Interface
   * Will handle adding additional sections, plugins, child themes
   */
  function build_interface() {

    $args = array(
      'title'       => get_admin_page_title(),
      'page_slug'   => $_GET['page'],
      'config'      => $this->get_config_array( $this->page() ),
    );

    $this->ui = new PL_Platform_Settings_UI( $args, $this );

  }

  /**
   * Build Single (Page, Post, CPT) Options Engine UI
   */
  function build_interface_meta() {

    $args = array(
      'title'       => get_admin_page_title(),
      'config'      => $this->get_config_array( 'meta', get_current_screen()->post_type ),
      'render'      => 'meta',
    );

    $this->ui = new PL_Platform_Settings_UI( $args, $this );

  }

  /**
   * Build User Profile Options Engine UI
   */
  function build_interface_profile( $user ) {

    $args = array(
      'title'       => get_admin_page_title(),
      'config'      => $this->get_config_array( 'profile' ),
      'render'      => 'profile',
      'user'        => $user,
    );

    $this->ui = new PL_Platform_Settings_UI( $args, $this );

  }

  /**
   * Build up admin page config arrays
   */
  function get_config_array( $page, $type = '' ) {

    $config = array(
      'tab_slug'  => array(
        'title'   => get_admin_page_title(),
        'groups'  => array(),
      ),
    );

    $config = apply_filters( 'pl_platform_config_' . $page,  $config, $page );

    if ( '' != $type ) {
      $config = apply_filters( 'pl_platform_config_' . $page . '_' . $type,  $config, $page ); }

    return $config;

  }

  /**
   * Check if a theme is installed
   */
  function is_theme_installed( $theme ) {

    $installed = wp_get_theme( $theme );
    return ( $installed->exists() ) ? true : false;
  }

  /**
   * User has framework theme active?
   */
  function is_framework_active() {

    return ( wp_get_theme()->template == $this->slug_framework ) ? true : false;

  }

  /**
   * User has framework theme installed?
   */
  function is_framework_installed() {

    return ( $this->is_theme_installed( $this->slug_framework ) ) ? true : false;

  }

  /**
   * Is DMS the current theme?
   */
  function is_dms_active() {

    return ( 'dms' == wp_get_theme()->template) ? true : false;

  }

  function is_pro() {
    return PL_Platform()->oauth->is_site_registered();
  }


  /**
   * Has the user gone through initial setup steps
   */
  function is_oriented() {

    if ( $this->oauth->is_connected() && $this->has_installed_something() ) {
      return true;
    } else {
      return false;
    }
  }

  function has_installed_something() {

    $plugin_count = PL()->operations->pl_plugins_count();

    return ($plugin_count > 1) ? true : false;

  }

  /**
   * Add a PageLines sorting method to plugins page.
   */
  function add_sorting( $views ) {
    $pl_plugins = PL_Platform()->functions->pl_get_plugins( true );
    $count      = count( $pl_plugins );
    $current    = ( isset( $_GET['s'] ) && 'pagelines' == $_GET['s'] ) ? 'current' : '';
    $views['pl'] = sprintf( "<a class='%s' href='%s'>PageLines <span class='count'>(%s)</span><a>",
        $current,
        admin_url( 'plugins.php?s=pagelines' ),
        $count
    );
    return $views;
  }
}


/**
 * Admin Requests Class > ALWAYS SHOULD RUN
 *
 * Adds actions for saving options and other functions.
 * Some are called via AJAX, others via GET and POST
 */
class PL_Platform_Save_Actions {

  function __construct() {

    $this->form_action = 'pl_admin_save_settings';

    add_action( 'admin_post_' . $this->form_action, array( $this, 'handle_settings_form' ) );
    add_action( 'save_post',                        array( $this, 'handle_meta_settings_form' ) );
    add_action( 'personal_options_update',          array( $this, 'handle_profile_settings_form' ) );
    add_action( 'edit_user_profile_update',         array( $this, 'handle_profile_settings_form' ) );
    add_action( 'wp_ajax_pl_platform',              array( $this, 'ajax' ) );
    add_action( 'wp_ajax_pl_admin_notice',          array( $this, 'notices' ) );

  }

  function notices() {

    /** Incoming post data */
    $postdata = $_POST;

    if ( isset( $postdata['nonce'] ) ) {
      pl_verify_ajax( $postdata['nonce'] );
    } else {
      die( 'No Nonce!' );
    }

    $postdata = $_POST;
    $response = array( 'post' => $postdata );
    $id       = $postdata['id'];
    $exp      = $postdata['exp'];

    if ( is_numeric( $exp ) ) {
      set_transient( $id, 1, $exp );
    } else {
      update_option( $id, 1 );
    }

    /** JSON response for output and UI actions */
    header( 'Content-Type: application/json' );
    echo json_encode( pl_convert_arrays_to_objects( $response ) );

    die(); // don't forget this, always returns 0 w/o
  }

  function ajax() {

    /** Incoming post data */
    $postdata = $_POST;

    if ( isset( $postdata['nonce'] ) ) {
      pl_verify_ajax( $postdata['nonce'] );
    } else {
      die( 'No Nonce!' );
    }

    /** Start response variable, sent back at end of request. */
    $response = array( 'post' => $postdata );

    if ( pl_can_use_tools() ) {
      $response = apply_filters( 'pl_platform_server_' . $postdata['hook'], $response, $postdata );
      $response = apply_filters( 'pl_platform_server_nopriv_' . $postdata['hook'], $response, $postdata );
    } else {
      $response = apply_filters( 'pl_platform_server_nopriv_' . $postdata['hook'], $response, $postdata );
    }

    /** JSON response for output and UI actions */
    header( 'Content-Type: application/json' );
    echo json_encode( pl_convert_arrays_to_objects( $response ) );

    die(); // don't forget this, always returns 0 w/o
  }

  /**
   * Update user profile
   */
  function handle_profile_settings_form( $user_id ) {

    if ( ! pl_can_use_tools( 'edit_user', $user_id ) ) {
      return false; }

    if ( isset( $_POST['pl_platform_settings'] ) ) {

      $postsettings = $_POST['pl_platform_settings'];
      $postsettings = $this->sanitize_post_options( $postsettings );

      foreach ( $postsettings as $key => $val ) {
        update_user_meta( $user_id, $key, $val );
      }
    }
  }

  /**
   * Update post meta
   */
  function handle_meta_settings_form( $post_id ) {

    if ( ! isset( $_POST['pl_platform_settings'] ) || ! isset( $_POST['pl_platform_settings_nonce'] ) || ! wp_verify_nonce( $_POST['pl_platform_settings_nonce'], $this->form_action ) ) {
      return;
    }

    if ( 'page' == $_POST['post_type'] && ! pl_can_use_tools( 'edit_page', $post_id ) ) {
      return;
    } elseif ( ! pl_can_use_tools( 'edit_post', $post_id ) ) {
      return;
    }

    $postsettings = $_POST['pl_platform_settings'];
    $postsettings = $this->sanitize_post_options( $postsettings );

    foreach ( $postsettings as $key => $val ) {
      update_post_meta( $post_id, $key, $val );
    }
  }


  function handle_settings_form() {
      // Handle request then generate response using echo or leaving PHP and using HTML
    if ( ! wp_verify_nonce( $_POST['pl_platform_settings_nonce'], $this->form_action ) ) {
      die( 'Invalid nonce. pl_platform_settings_nonce' ); }

    if ( ! isset( $_POST['pl_platform_settings'] ) ) {
      die( 'No Settings Posted' ); }

    // save settings
    $this->save_post_settings( $_POST['pl_platform_settings'] );

    $msg = 'updated';

    if ( isset( $_POST['_wp_http_referer'] ) ) {

      $url = add_query_arg( array( 'msg' => $msg ), urldecode( $_POST['_wp_http_referer'] ) );
      $url = add_query_arg( array( 'settings_tab' => $_POST['settings_tab'] ), $url );
    } else {         die( 'Missing target.' ); }

    wp_safe_redirect( $url );
    exit;
  }

  /**
   * Update settings
   */
  function save_post_settings( $settings_array ) {

    $old_settings = pl_get_all_settings( );
    $new_settings = wp_parse_args( $this->sanitize_post_options( $settings_array ), $old_settings );

    pl_update_all_settings( $new_settings );
  }

  /**
   * Sanitize options with kses
   */
  function sanitize_post_options( $post_settings ) {

    $kses_array = ( isset( $_POST['settings_kses'] ) ) ? $_POST['settings_kses'] : array();

    foreach ( $post_settings as $key => $setting ) {

      if ( isset( $kses_array[ $key ] ) ) {
        $kses = json_decode( stripslashes( $kses_array[ $key ] ) );
        $kses = json_decode( json_encode( $kses ), true );
      } else {
        $kses = false;
      }

      // check for bypass.. can be anything but an array.. kses options have to be an array..
      if ( isset( $kses ) && ! is_array( $kses ) ) {
        continue;
      }

      // if the setting has a kses option like array( 'a' => 'href' ) then use it
      if ( isset( $kses ) && is_array( $kses ) ) {
        $post_settings[ $key ] = wp_kses( $setting, $kses );
      } else {
        $post_settings[ $key ] = wp_kses( $setting, array() );
      }
    }

    return $post_settings;
  }
}

global $pl_platform;
$pl_platform = new PL_Platform;

/**
 * Main Global function
 */
function PL_Platform() {
  global $pl_platform;
  return $pl_platform;
}

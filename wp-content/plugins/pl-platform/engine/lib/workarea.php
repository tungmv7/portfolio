<?php
/**
 * Workarea Rendering Class
 *
 * @class     PL_Workarea
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Workarea {

  function __construct( PL_Site_Engine $engine ) {

    $this->engine = $engine;

    // Add admin bar back.
    add_filter( 'show_admin_bar', '__return_true', 100 );

    add_action( 'wp_enqueue_scripts',   array( $this, 'workarea_enqueue' ), 1000 );

    add_action( 'template_redirect',    array( $this, 'workarea' ) );

    add_action( 'pl_workarea_scripts', 'wp_admin_bar_render', 1000 );

    /** Page Edit Selector */
    global $plselector;
    $this->selector = $plselector;

  }

  function workarea() {
    ?>
<!DOCTYPE HTML>
<html lang="en">
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>" />
  <link rel="profile" href="http://gmpg.org/xfn/11" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta http-equiv="cache-control" content="no-cache" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  
  <?php echo pl_favicon();?>

  <title>PageLines Workarea</title>
  
  <?php do_action( 'pl_workarea_head' ); ?>

  <?php wp_head(); ?>
</head>
<body <?php body_class( 'pl-workarea' ); ?>>
  <?php do_action( 'pl_workarea_body_open' ); ?>
  <div class="workarea-loading-overlay"></div>
  <div class="pl-composer">
    <div class="iframe-container">
      <?php

      $current_url = pl_get_current_url();

      $iframe_url = add_query_arg( array(
          'iframe'  => 'true',
          'rand'    => rand( 1, 999999 ),
      ), $current_url);

      $iframe_url = remove_query_arg( 'pl_edit', $iframe_url );

      printf( '<iframe class="site-frame" src="%s" scrolling="yes" ></iframe>', $iframe_url ); ?>
      <div class="iframe-loading-overlay show-overlay">
        <div class="loading-graphic"><i class="iframe-loading-icon pl-icon pl-icon-cog pl-icon-spin"></i></div>
      </div>
    </div>
    <div class="pl-workarea-sidebar-container">
      <div class="pl-workarea-sidebar"></div>
    </div>
  </div>
  <?php
      pl_hook( 'pl_workarea_footer' );
      pl_hook( 'pl_workarea_scripts' ); // dep
  ?>
</body>
</html><?php
/** Prevents output of anything from templates or plugins */
die( '<!-- Have a great day! -->' );

  }

  function workarea_enqueue() {

    wp_deregister_script( 'jquery-ui' );

    global $wp_styles;

    $wp_styles->queue = array( 'admin-bar' );

    global $wp_scripts;
    $wp_scripts->queue = array();

    pl_script( 'pl-common',     pl_framework_url( 'js' ) . '/common.js', array( 'jquery' ), false, false );

    pl_style( 'pl-components',  pl_framework_url( 'css' ) . '/components.css' );

    pl_style( 'pl-editing',     pl_framework_url( 'css' ) . '/workarea.css' );

    pl_script( 'pl-editing',    pl_framework_url( 'js' ) . '/editing.js', array( 'jquery' ), false, false );

    pl_script( 'pl-utils',      pl_framework_url( 'js' ) . '/utils.js', array( 'jquery' ), false, false );

    /** WordPress Color Picker */
    pl_enqueue_color_picker();

    wp_enqueue_script( 'jquery' );

    global $pl_page;
    $this->page = $pl_page;

    global $maps_data_handler;
    $this->map = $maps_data_handler;

    global $plfactory;
    $this->factory = $plfactory;

    global $plselector;
    $this->selector = $plselector;

    wp_localize_script( 'pl-editing', 'PLWorkarea', $this->workarea_config() );

    pl_hook( 'pl_workarea_enqueue' );

  }


  function workarea_config() {

    global $pl_medialib, $pl_notifications;

    $a = array(
      'siteName'        => get_bloginfo( 'name' ),
      'siteDescription' => get_bloginfo( 'description' ),
      'plURL'           => get_template_directory_uri(),
      'siteURL'         => do_shortcode( '[pl_site_url]' ),
      'homeURL'         => do_shortcode( '[pl_home_url]' ),
      'uploadsURL'      => do_shortcode( '[pl_uploads_url]' ),
      'adminURL'        => admin_url(),
      'PLUI'            => pl_framework_url( 'ui' ),
      'frontPage'       => get_option( 'show_on_front', 'posts' ),
      'ajaxURL'         => pl_ajax_url(),
      'security'        => wp_create_nonce( 'pl-ajax-nonce' ),
      'models'          => array(),
    );

    $a['factory']     = $this->set_factory();

    // Edit current post URL
    $a['editPost']    = plns_edit_post_link();

    // Add menus URL
    $a['menus']       = admin_url( 'nav-menus.php' );

    // Add/edit widgets URL
    $a['widgets']     = admin_url( 'widgets.php' );

    // URL of core framework
    $a['CoreURL']     = get_template_directory_uri();

    // URL of parent theme
    $a['ParentStyleSheetURL'] = get_template_directory_uri();

    // URL of child theme, if active
    $a['ChildStyleSheetURL'] = get_stylesheet_directory_uri();

    $a['extendURL']   = ( function_exists( 'PL_Platform' ) ) ? PL_Platform()->url( 'extend' ) : '';

    // Media library link for use in iFrame
    $a['mediaLibrary'] = $pl_medialib->pl_media_library_link();

    //  Media library videos link for use in iFrame
    $a['mediaLibraryVideo'] = $pl_medialib->pl_media_library_link( 'video' );

    // Add media link
    $a['addMediaURL'] = admin_url( 'media-new.php' );

    // ID of the currently active user
    $a['userID']      = wp_get_current_user()->ID;

    // A cache key for various settings
    $a['CacheKey']    = pl_cache_key();

    // Is the user on localhost
    $a['LocalHost']   = pl_is_local();

    // Is developer mode activated?
    $a['debug']       = ( pl_dev_mode() ) ? 'true' : 'false';

    $a['btns']        = pl_btn_classes();

    $a['btnSizes']    = pl_button_sizes();

    $a['imgSizes']    = get_intermediate_image_sizes();

    $a['animations']  = pl_animations();

    $a['taxes']       = pl_get_taxonomies();

    $a['icons']       = pl_icons();

    $a['menus']       = pl_get_menus();

    $a['sidebars']    = pl_get_sidebars();

    $a['notifications'] = $pl_notifications;

    $a['urls'] = pl_get_system_urls();

    $a['translate']     = array();

    $a['locale']        = get_locale();
    
    return apply_filters( 'pl_workarea_json', $a );
  }

  /**
   * Get the sections factory for use in 'add_new' ++
   */
  function set_factory() {

    $factory = array();

    /** factory is all the sections, lets get only the data we need */

    if ( is_array( $this->factory->factory ) ) {

      foreach ( $this->factory->factory as $class => $s ) {

        $is_container = ( 'PL_Container' == $class || ( isset( $s->settings['contain'] ) && 'yes' == $s->settings['contain'] ) ) ? 1 : 0;

        // no spaces since they are arrays
        $filters  = str_replace( ' ', '', $s->settings['filter'] );

        // If Static Content, rename content section

        $factory[ $class ] = array(
            'name'    => $s->name,
            'class'   => $class,
            'desc'    => $s->description,
            'contain' => $is_container,
            'opts'    => apply_filters( 'pl_section_opts', $s->section_opts() ),
            'filter'  => $filters,
            'icon'    => $s->settings['icon'],
            'loading' => $s->settings['loading'],
          );
      }
    }
    /** Add regions in so we can use the factory in a wider amount of cases. */
    foreach ( pl_site_regions() as $region ) {

      $factory[ $region ] = array(
          'name'    => $region,
          'class'   => $region,
          'desc'    => '',
          'contain' => 1,
          'opts'    => array(),
          'filter'  => 'regions',
          'icon'    => '',
        );

    }
    return $factory;
  }
}

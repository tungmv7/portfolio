<?php
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
/** Don't include this file twice.  */
if ( ! class_exists( 'PL_Site_Engine' ) ) {

  class PL_Site_Engine{

    function __construct( $base_dir, $base_url ) {

      $this->base_dir   = $base_dir;
      $this->base_url   = $base_url;
      $this->cache_key  = 'plcache_';

      $this->include_functions();

      add_action( 'wp_loaded',          array( $this, 'include_components' ) );

      // TEMPLATE ACTIONS
      // !important - must load after $post variable
      // ALSO, bbPress and other plugins make adjustments to queries which change pages from 404 to something else.
      // Therefore must come after WP_Query (parse query)
      add_action( 'wp',         array( $this, 'initialize_libs' ), 10 );
      add_action( 'admin_init', array( $this, 'initialize_libs' ), 5 );

      add_filter( 'pl_platform_sections_dirs', array( $this, 'add_sections_dir' ) );

      add_action( 'after_setup_theme',         array( $this, 'pl_theme_support' ) );
      add_action( 'after_setup_theme',         array( $this, 'pl_check_path' ), 9 );

      add_action( 'admin_enqueue_scripts',     array( $this, 'admin_scripts' ) );

      add_action( 'upgrader_process_complete', array( $this, 'clear_caches' ) );

    }




    function admin_scripts() {

      wp_enqueue_media();
      pl_enqueue_color_picker();
      pl_load_codemirror( pl_framework_url( 'plugins' ) );

      wp_enqueue_script( 'pl-admin',       pl_framework_url( 'js' )  . '/admin.js',  array( 'jquery', 'wp-color-picker' ) );
      wp_enqueue_style( 'pl-components',  pl_framework_url( 'css' ) . '/components.css' );
      wp_enqueue_style( 'pl-admin',       pl_framework_url( 'css' ) . '/admin.css' );

      global $plfactory;

      $config = apply_filters('pl_admin_json', array(
          'ajaxurl'       => pl_ajax_url(),
          'baseURL'       => $this->base_url,
          'cachekey'      => $this->cache_key . $this->current_admin_page(),
          'sections'      => $plfactory->section_ids,
          'security'      => wp_create_nonce( 'pl-ajax-nonce' ),
          'extendURL'     => PL_Platform()->url( 'extend' ),
      ));

      wp_localize_script( 'pl-admin', 'PLAdmin', $config );
    }

    function current_admin_page() {

      if ( isset( $_GET['tab'] ) && 'default' != $_GET['tab'] ) {
        $page = $_GET['tab'];
      } elseif ( isset( $_GET['page'] ) ) {
        $page = str_replace( '-', '', $_GET['page'] );
      } else {
        $page = false;
      }
      return $page;
    }

    function add_sections_dir( $dirs ) {

      $dirs['engine'] = array(
      'dir' => $this->base_dir . '/sections',
      'url' => $this->base_url . '/sections',
      );

      return $dirs;
    }

    function include_functions() {

      $inc = array(
      'functions',
      'operations',
      'globals',
      'site',
      'wrapping',
      'ajax',
      'ajax.save',
      'ajax.medialib',
      'ajax.upload',
      'ajax.binding',
      'factory',
      'page',
      'data.maps',
      'sections.register',
      'data.sections',
      'sections.class',
      'sections.load',
      'sections.settings',
      'compat',
      'json',
      'widgets',
      'customizer',
      'forms',
      );

      foreach ( $inc as $file ) {
        require_once( 'lib/' . $file . '.php' );
      }
    }

    function include_components() {

      $inc = array(
      'adminbar',
      'workarea',
      'i18n',
      );

      foreach ( $inc as $file ) {
        require_once( 'lib/'. $file . '.php' );
      }

    }

    function initialize_libs() {

      /** Config Classes */
      global $pl_page;
      $pl_page = new PL_Page;

      /** Maps Data */
      global $maps_data_handler;
      $maps_data_handler = new PL_Map_Data;

      /** Section Handling Classes */
      global $pl_sections_data;
      $pl_sections_data = new PL_Section_Data;

      pl_hook( 'pl_after_data_setup' );

      /** The media library link and iFrame handling */
      global $pl_medialib;
      $pl_medialib = new PL_Media_Library;

      /** AJAX */

      global $plajaxinit;
      $plajaxinit = new PLAJAXInit();

      global $pl_data_save;
      $pl_data_save = new PL_Save_Data();

      /** Base AJAX Handling API */
      global $plajaxupload;
      $plajaxupload = new PLAJAXUpload();

      /** Sections AJAX Loading */
      global $plsectionsload;
      $plsectionsload = new PL_Sections_Load();

      /** BINDING */

      /** Binding and handling of binds */
      global $plbinding;
      $plbinding = new PL_Binding();

      if ( pl_tools_active() ) {

        do_action( 'pl_reset_sections' );

        new PL_Workarea( $this );

      } else {
        new PL_JSON;
        new PL_UI_Site;
      }

      // run installer init actions
      if ( is_admin() ) {
        PL()->operations->init();
      }
    }

    /**
   * Support optional WordPress functionality 'add_theme_support'
   */

    function pl_theme_support() {

      /** Support Post Featured Images */
      add_theme_support( 'post-thumbnails' );

      /** Supported Image Thumb Sizes */
      /** Supported Image Thumb Sizes */
      add_image_size( 'aspect-thumb',     1500, 1000, true );
      add_image_size( 'basic-thumb',      750,    750,  true );
      add_image_size( 'landscape-thumb',  1500, 750,  true );
      add_image_size( 'tall-thumb',       750,    1500, true );
      add_image_size( 'big-thumb',        1500, 1500, true );

      /** Support WP Menus System */
      add_theme_support( 'menus' );
    }

    /**
   * WordPress uses ABSPATH to determine the server path for scripts, so lets check it hasnt changed
   * Fixes an issue on hosts such as pagely that migrate your site without letting you know, so db saved paths
   * will result in a WSOD because section paths will be all wrong.
   */
    function pl_check_path() {
      $path       = ABSPATH;
      $saved_path = get_theme_mod( 'pl_check_path' );
      if ( $path !== $saved_path ) {
        do_action( 'pl_reset_sections' );
        set_theme_mod( 'pl_check_path', ABSPATH );
      }
    }

    function clear_caches() {
      do_action( 'pl_reset_sections' );
      pl_reset_pl_cache_key();
    }
  }

  global $pl_framework;
  $pl_framework = new PL_Site_Engine( $engine_dir, $engine_url );

}

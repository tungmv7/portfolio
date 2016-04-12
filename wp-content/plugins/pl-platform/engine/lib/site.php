<?php
/**
 * PageLines Website Rendering Class
 *
 * @class     PL_UI_Site
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_UI_Site {

  function __construct() {

    global $plfactory;
    $this->factory = $plfactory;
    $this->id = pl_current_page_id();

    add_action( 'template_include',     array( $this->factory, 'preprocess' ), 100 );

    /** Sections Factory: Enqueue scripts & styles */
    add_action( 'wp_enqueue_scripts',   array( $this->factory, 'process_styles' ) );

    /** Sections Factory: Process section head and foot */
    add_action( 'wp_head',              array( $this->factory, 'process_head' ) );
    add_action( 'wp_footer',            array( $this->factory, 'process_foot' ) );

    /** Sections Factory: Render sections for each region of the page */
    add_action( 'pl_region_header',     array( $this, 'process_header' ) );
    add_action( 'pl_region_template',   array( $this, 'process_template' ) );
    add_action( 'pl_region_footer',     array( $this, 'process_footer' ) );

    add_filter( 'body_class',           array( $this, 'pl_body_classes' ) );
    
    // Add checkboxes for disabling header/footer render.
    add_filter( 'pl_platform_meta_settings_array',  array($this, 'meta_settings') );
    
    // Add json to main PLData
    add_filter( 'pl_site_json',         array( $this, 'disabled_json' ) );

    add_action( 'wp_footer',            array( $this, 'pl_link_credit' ) );
    
  }

  function process_header() {

    if( ! pl_is_region_disabled( 'header' ) ) {
      $this->factory->process_region( 'header' );
    } 
  }

  function process_template() {

    $this->factory->process_region( 'template' );
  }

  function process_footer() {

    if( ! pl_is_region_disabled( 'footer' ) ) {
      $this->factory->process_region( 'footer' );
    }
  }

  function pl_link_credit(){

    if( ! pl_user_setting( 'hide_pl_cred' ) || ! pl_is_professional() ) {
      printf('<a class="pl-link-credit" href="https://www.pagelines.com" title="Built using PageLines" style="display: block; visibility: visible;"><i class="pl-icon pl-icon-pagelines"></i> <span class="txt">PageLines</span></a>');
    }
     

  }


  /**
   * PageLines Body Classes
   *
   * Sets up classes for controlling design and layout and is used on the body tag
   *
   */
  function pl_body_classes( $wp_classes ) {

    // child theme name
    $wp_classes[] = sanitize_html_class( strtolower( get_option( 'stylesheet' ) ) );

    if ( ! is_user_logged_in() ) {
      $wp_classes[] = 'logged-out';
    }

    // ensure no duplicates or empties
    $wp_classes = array_unique( array_filter( $wp_classes ) );

    return $wp_classes;
  }
  
  /**
   * Add header/footer disable to page meta
   */
  function meta_settings( $settings ) {
    
    if( ! current_theme_supports( 'pagelines' ) ) {
      return $settings;
    }

    if( 'page' == get_current_screen()->post_type ){

      $settings['header_footer'] = array(
        'key'       => 'pl_headfoot',
        'icon'      => 'file-o',
        'pos'       => 25,
        'location'  => 'page',
        'title'     => __( 'Header/Footer', 'pl-platform' ),
        'opts'  => array(

          array(
           'key'           => '_pl_header',
           'type'          => 'checkbox',
           'default'       => false,
           'title'         => __( 'Disable header output on this page', 'pl-platform' ),
           'help'          => __( 'No sections will be rendered in this region.', 'pl-platform' ),
          ),
          array(
           'key'           => '_pl_footer',
           'type'          => 'checkbox',
           'default'       => false,
           'title'         => __( 'Disable footer output on this page', 'pl-platform' ),
           'help'          => __( 'No sections will be rendered in this region.', 'pl-platform' ),
          ),
        )
      );
    }
    return $settings;
  }
  
  // Add disabled regions as array to $pl().extraData 
  function disabled_json( $array ) {

    $array['extraData']['disabled_regions'] = array(
      'header'    => pl_is_region_disabled( 'header' ),
      'template'  => pl_is_region_disabled( 'template' ),
      'footer'    => pl_is_region_disabled( 'footer' )
    );
    
    return $array;
  }
} /* fin */

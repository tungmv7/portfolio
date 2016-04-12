<?php
/**
 * PL_Section
 *
 * Base class API for creating and using PageLines sections
 *
 * @class     PL_Section
 * @version    5.0.0
 * @package    PageLines/Classes
 * @category  Class
 * @author     PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Section {

  var $id;    // Root id for section.
  var $name;    // Name for this section.
  var $settings;  // Settings for this section
  var $base_dir;  // Directory for section
  var $base_url;  // Directory for section
  var $format;  // <section> format.
  var $classes;  // <section> classes.

  var $meta;

  function __construct( $settings = array() ) {

    $this->class_name = get_class( $this );

    $this->set_section_info( $settings );

  }

  /**
   * Set Section Info
   *
   * Read information from the section header; assigns values found, or sets general default values if not
   *
   */
  function set_section_info( $settings = array() ) {

    global $pl_sections_register;

    /** Set good defaults to allow maximum flexibility with overriding and creating multiple sections */
    $factory = $pl_sections_register->get_sections();

    $default_config = ( isset( $factory['plugins'][ $this->class_name ] ) ) ? $factory['plugins'][ $this->class_name ] : array();

    $default_settings = wp_parse_args( $default_config, $pl_sections_register->default_headers() );

    $this->settings = wp_parse_args( $settings, $default_settings );

    if ( isset( $this->settings['id'] ) && ! empty( $this->settings['id'] ) ) {
      $this->id = $this->settings['id'];
    } else {
      $this->id = str_replace( 'pl-section-', '', basename( $this->settings['base_dir'] ) );
    }

    /** Shorthand attributes: Set common section variables for easy use */

    $this->base_dir     = $this->settings['base_dir'];
    $this->base_file    = $this->settings['base_file'];
    $this->base_url     = $this->settings['base_url'];

    $this->name         = $this->settings['name'];
    $this->description  = $this->settings['description'];

    $this->map          = '';
    $this->set          = '';

    $this->icon = $this->settings['icon'] = ( is_file( sprintf( '%s/icon.png', $this->base_dir ) ) )
      ? sprintf( '%s/icon.png', $this->base_url )
      : pl_framework_url( 'images' ) . '/default-section-icon.png';

    /** Do localization for section */
    $langfile = sprintf( '%s/%s.po', $this->base_dir, get_locale() );
    if ( is_file( $langfile ) ) {
      load_textdomain( $this->id, $langfile ); }

    // set to true before ajax load
    $this->active_loading = false;

  }

  /**
   * Main option getter used in section functions to get settings.
   */
  function opt( $key, $args = array() ) {

    if ( ! is_array( $args ) ) {

      $a['default']   = $args;
      $a['shortcode'] = true;

    } else {
      $d = array(
        'default'   => false,
        'shortcode' => true,
      );

      $a = wp_parse_args( $args, $d );
    }

    if (
      property_exists( $this, 'meta' )
      && isset( $this->meta['set'] )
      && isset( $this->meta['set'][ $key ] )
      && '' != $this->meta['set'][ $key ]
    ) {

      $val = $this->meta['set'][ $key ];

    } elseif (
      property_exists( $this, 'meta' )
      && isset( $this->meta['data'] )
      && isset( $this->meta['data'][ $key ] )
    ) {
      $val = $this->meta['data'][ $key ];
    } else {
      $val = $a['default'];
    }

    // Section output safe mode
    if ( pl_can_use_tools() && isset( $_GET['plsafemode'] ) ) {
      return 'NO OUTPUT ( SAFE MODE )';
    }

    if ( has_filter( "pl_opt-$key" ) ) {
      return apply_filters( "pl_opt-$key", $val ); }

    if ( '' == $val ) {
      return false; } elseif ( is_array( $val ) ) {
      return $val; } else {
        if ( true == $a['shortcode'] ) {
          return do_shortcode( $val ); } else {         return $val; }
      }
  }

  /**
   * Parse the option array and setup a configuration array for callbacks
   * NOTE: Recursive
   */
  function get_config( $o = false, $inherit = false ) {

    $config = array();

    $o = ( ! $o ) ? $this->section_opts() : $o;

    foreach ( $o as $opt ) {

      $add = ( isset( $opt['conf'] ) ) ? $opt['conf'] : $inherit;

      if ( $add && isset( $opt['key'] ) && '' != $opt['key'] ) {

        $default = ( isset( $opt['default'] ) ) ? $opt['default'] : '';

        $config[ $opt['key'] ] = $this->opt( $opt['key'], array( 'shortcode' => false, 'default' => $default ) );

      }

      if ( isset( $opt['opts'] ) && is_array( $opt['opts'] ) ) {

        $config = array_merge( $config, $this->get_config( $opt['opts'], $add ) );

      }
    }
    return $config;
  }

  /**
   * Section Template
   *
   * The 'section_template()' function is the most important section function.
   * Use this function to output all the HTML for the section on pages/locations where it's placed.
   *
   * Subclasses should over-ride this function to generate their section code.
   *
   * @since 1.0.0
   */
  function section_template() {
    die( 'function PL_Section::section_template() must be over-ridden in a sub-class.' );
  }

  /**
   * Render the actual section
   */
  function render( $map_meta = array(), $level = 0 ) {

    /**
     * Set the specific information for this section in the map
     */

    // Hiding section on specific page.
    $hide_on_pages = ''; //$this->opt( 'hide_on' );

    $hide_section = false;

    if ( false != $hide_on_pages ) {
      $hide_on_pages_ids = explode( ',', $hide_on_pages );

      if (  in_array( pl_current_page_id(), $hide_on_pages_ids ) ) {
        $hide_section = true; }
    }

    /** Do main section template */
    if ( has_filter( 'platform_render_section' ) ) {

      $output = apply_filters( 'platform_render_section', $this );

    } else {

      ob_start();
      $this->section_template_load( );
      $output = ob_get_clean();

    }

    $render = ( ! isset( $output ) || '' == $output || $hide_section ) ? false : true;

    if ( $render ) {

      // set to true if standard title is to be placed non standard
      $this->alt_standard_title = false;

      $this->before_section_template( );

      $this->before_section( $map_meta, $level );

      echo $output;

      $this->after_section( );

      $this->after_section_template( );

    }

  }

  /**
   * Display a message if section has no output.
   */
  function blank_template( $name = '' ) {
    if ( pl_can_use_tools() ) {
      return sprintf( '<div class="blank-section-template pl-editor-only"><strong>%s</strong> %a.</div>', $name, __( 'is hidden or returned no output', 'pl-platform' ) ); } else {       return ''; }

  }

  /**
   * Runs before the section_template function
   */
  function before_section( $map_meta, $level ) {

    echo pl_html_comment( $this->name . ' | Section', 2 ); // Add Comment

    pl_hook( 'pl_before_'.$this->id, $this->id ); // hook
    do_action( 'pl_before_section', $this ); // hook

    $sid = $this->id;

    $class[]   = 'pl-sn';

    $class = apply_filters( 'before_section_classes', $class, $this );

    $class[]   = ( $this->opt( 'col' ) )      ? sprintf( 'pl-col-sm-%s',       $this->opt( 'col' ) )  : 'pl-col-sm-12';
    $class[]   = ( $this->opt( 'offset' ) )    ? sprintf( 'pl-col-sm-offset-%s',   $this->opt( 'offset' ) )   : 'pl-col-sm-offset-0';

    $pad_class   = 'pl-sn-pad';

    $class = array_unique( array_filter( $class ) ); // ensure no empties or duplicates

    $video = ''; // dont think we use this cuz JS.... pl_standard_video_bg( $this );

    $bindings = 'data-bind="pledit: true"';

    $combo_id = sprintf( '%s_%s', $this->id, $map_meta['clone'] );

    $extra_data = apply_filters( 'before_section_extra_data', '', $this );

    /**
     * the section wrapper start
     * TODO We should remove all data variables that are used in $.pl and can be accessed by JS. Mapping information should be here.
     */
    printf(
        '<section id="%s" class="%s pl-sn-%s" %s data-object="%s" data-clone="%s" data-level="%s"><div class="pl-sn-wrap">%s<div class="%s fix" %s >',
        $combo_id,
        implode( ' ', $class ),
        $sid,
        $extra_data,
        $this->class_name,
        $map_meta['clone'],
        $level,
        $video,
        $pad_class,
        $bindings
    );

    pl_hook( 'pl_top_'.$this->id, $this->id ); // hook

  }

  function after_section() {

    pl_hook( 'pl_bottom_'.$this->id, $this->id );

    printf( '</div></div></section>' );

    pl_hook( 'pl_after_'.$this->id, $this->id );
  }



  /**
    * Before Section Template
    *
    * For template code that should show before the standard section markup
    *
    * @since   ...
    *
    * @param   null $clone_id
    */
  function before_section_template( $clone_id = null ) {}

  /**
   * After Section Template
   *
   * For template code that should show after the standard section markup
   *
   * @since   ...
   *
   * @param   null $clone_id
   */
  function after_section_template( $clone_id = null ) {}

  /**
   * Section Template Load
   *
   * Checks for overrides and loads section template function
   *
   * @since   ...
   *
   * @param   $clone_id
   *
   * @uses    section_template
   *
   * TODO This is OLD code from pre PlateformPro and not used anymore.
   */
  function section_template_load() {

    // Variables for override
    $override_template = 'template.' . $this->id .'.php';
    $override = ( '' != locate_template( array( $override_template ), false, false )) ? locate_template( array( $override_template ) ) : false;

    if ( false != $override ) { require( $override ); } else {
      $this->section_template();
    }

  }

  /**
   * Section Persistent
   *
   * Use this function to add code that will run on every page in your site & admin
   * Code here will run ALL the time, and is useful for adding post types, options etc.
   *
   * @since   1.0.0
   */
  function section_persistent(){}

  /**
   * Section Head
   *
   * Code added in this function will be run during the <head> element of the
   * site's 'front-end' pages. Use this to add custom Javascript, or manually
   * add scripts and meta information. It will *only* be loaded if the section
   * is present on the page template.
   *
   * @since   1.0.0
   */
  function section_head(){}

  /**
   * Section Foot
   *
   * Code added in this function will be run during the wp_footer hook
   *
   */
  function section_foot(){}


  /**
   * Function for Section Enqueues
   *
   */
  function section_styles(){}

  /**
   * Returns an array for setting defaults for the section model
   */
  function section_defaults() {
    return array(); }





  /**
   * Section Opts
   *
   * Loads section options simply
   *
   * @since 1.0.0
   */
  function section_opts() {
    return array(); }
}
/********** END OF SECTION CLASS  **********/

/**
 * PageLines Section Factory (class)
 *
 * Singleton that registers and instantiates PL_Section classes.
 *
 * @package     PageLines Framework
 * @subpackage  Sections
 * @since       1.0.0
 */
class PL_Section_Factory {

  var $sections          = array();
  var $section_ids      = array();
  var $unavailable_sections    = array();

  /**
   * Register a section
   */
  function register( $section_class, $args ) {

    if ( class_exists( $section_class ) ) {

      $class = $this->sections[ $section_class ] = new $section_class( $args );

      $this->section_ids[ $class->id ] = $section_class;

    }
  }

  /**
   * Unregister a section
   */
  function unregister( $section_class ) {
    if ( isset( $this->sections[ $section_class ] ) ) {
      unset( $this->sections[ $section_class ] ); }
  }
}

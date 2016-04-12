<?php
/**
 * Renders standard elements with and around sections
 *
 * @class     PL_Factory
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Factory{

  function __construct() {

    add_action( 'after_setup_theme',    array( $this, 'pl_load_factory' ) );
    add_action( 'pl_after_data_setup',  array( $this, 'initialize_processing' ), 5 );
    add_action( 'init',                 array( $this, 'load_section_persistent' ) );
    add_action( 'wp_head',              array( $this, 'live_scripts_init' ), 100 );
    add_action( 'pl_json_data',         array( $this, 'live_scripts_process' ) );

  }

  function initialize_processing() {

    global $maps_data_handler;
    global $pl_sections_data;

    $this->map = $maps_data_handler->map;

    /** A listing of the sections on the current page with object name */
    $this->list = array( 'sections' => array(), 'uids' => array() );

    /** Parse and modify map based on templates and other stuffs */
    $this->recursive_set_map_data( $this->map, 'list' );

    // Get all data for on page sections.
    $this->sections_data = apply_filters( 'pl_section_data', $pl_sections_data->get_section_data( $this->list['uids'] ) );

    /** Attach option values to sections on map, has to be after we get data based on UIDs  */
    $this->map = $this->recursive_set_map_data( $this->map, 'settings' );

    $this->page_data = $this->get_page_data();

  }

  function preprocess( $tpl ) {

    foreach ( $this->factory as $section_class => &$section ) {

      if ( pl_is_static_template( 'pre' ) && 'PL_Content' == $section_class ) {

        $section->name = 'Static ' . $section->name;
        $section->id   = 'static-' . $section->id;
      }
    }
    unset( $section );
    return $tpl;
  }

  function pl_load_factory() {

    global $pl_sections_register;

    $factory_register = $pl_sections_register->register_sections( );

    $this->factory = $factory_register->sections;

    $this->section_ids = $factory_register->section_ids;

  }

  function load_section_persistent() {

    foreach ( $this->factory as $section ) {
      $section->section_persistent();
    }
  }

  function live_scripts_init() {

    global $pl_live_scripts;
    global $pl_live_styles;

    $pl_live_scripts = array();
    $pl_live_styles = array();

  }

  function live_scripts_process() {

    global $pl_live_scripts;
    global $pl_live_styles;

    foreach ( $pl_live_scripts as $script ) {
      printf( '<script src="%s"></script>', $script );
    }

    foreach ( $pl_live_styles as $style ) {
      printf( '<link rel="stylesheet"  href="%s" type="text/css" media="all" />', $style );
    }
  }

  function add_section_to_factory( $id, $object, $settings ) {

    $this->list['sections'][ $id ] = array( 'object' => $object );
    $this->list['uids'][] = $id;

    $this->sections_data[ $id ] = $settings;
  }


  /**
   * Sets appicable option data to each map element recursively
   * Different modes are available since some data may be needed to get other data
   */
  function recursive_set_map_data( $container, $mode ) {

    foreach ( $container as $index => &$item ) {

      if ( 'settings' == $mode ) {
        $item['set'] = $this->get_section_settings( $item['clone'] );
      } else {

        $this->list['sections'][ $item['clone'] ]   = array( 'object' => $item['object'] );

        $this->list['uids'][]            = $item['clone'];

      }

      if ( ! empty( $item['content'] ) ) {
        $item['content'] = $this->recursive_set_map_data( $item['content'], $mode );
      }
    }
    unset( $item );
    return $container;
  }


  /**
   * Get all information related to drawing a page.
   * Includes sections, data, and their model keys
   * @return array uniqueIDs, Data, option keys for model
   */

  function get_view_model() {

    $view_model = $this->list['sections'];

    foreach ( $view_model as $uid => &$info ) {

      // Dont need the data added below

      unset( $info['data'] );

      $info['values'] = array(
        'type'  => '',
        'value' => '',
        'opts'  => '',
      );

      $defaults = ( isset( $this->factory[ $info['object'] ] ) )  ?  $this->factory[ $info['object'] ]->section_defaults() : array();

      $data = ( isset( $this->sections_data[ $uid ] ) ) ? $this->sections_data[ $uid ] : $defaults;

      $modelized_data = pl_defaults_model( $data );

      $keys = array();
      $opts = array();

      if ( pl_is_static_template( 'viewmodel' ) && 'PL_Content' == $info['object'] ) {

        $opts = pl_get_template_settings();

      } /** Get from factory */
      elseif ( isset( $this->factory[ $info['object'] ] ) ) {
        $opts = $this->factory[ $info['object'] ]->section_opts();
      }

      $keys = $this->recursive_parse_opts( $opts, $data );

      /** Parse together for standard section options which aren't in array */
      $info['values'] = wp_parse_args( $keys, $modelized_data );

      $info = apply_filters( 'pl_model_'.$info['object'], $info );

    }

    unset( $info ); // set by reference

    if ( isset( $view_model[''] ) ) {
      unset( $view_model[''] );
    }

    return $view_model;
  }


  /**
   * Recursively parse the options array, can be adapted for various uses
   * Currently is only used to get keys for the model
   * @param  array $opts options array
   * @param  string $get  values or operation to get
   * @return array       array of values
   */
  function recursive_parse_opts( $opts, $data = array(), $mode = 'model' ) {

    $return = array();

    if ( is_array( $opts ) && ! empty( $opts ) ) {

      foreach ( $opts as $o ) {

        $type = (isset( $o['type'] )) ? $o['type'] : '';

        if ( isset( $o['key'] ) ) {

          $key  = $o['key'];
          $opts   = array();

          if ( isset( $data[ $key ] ) ) {
            $value = $data[ $key ]; } elseif ( empty( $data ) && isset( $o['default'] ) ) {
            $value = $o['default']; } else {             $value = ''; }

            if ( 'accordion' == $type ) {

              /** Gets all the opt keys */
              $opts = $this->handle_accordion_opts( $o );

              /** Verifies value and sets defaults */
              $value = $this->check_accordion_value( $opts, $value, $o );
            }

            /** Just get key value pairs */
            if ( 'keyval' == $mode ) {

              if ( '' != $value ) {
                $return[ $key ] = $value; }
            } /** Get a model with associated option data for rendering */
            else {

              $return[ $key ] = array(
              'type'  => $type,
              'value' => $value,
              'opts'  => $opts,
              );
            }
        }

        /** Recursive Parsing */
        if (
          isset( $o['opts'] )
          && 'accordion' != $type
        ) {
          $return = array_merge( $return, $this->recursive_parse_opts( $o['opts'], $data, $mode ) );
        }
      }
    }
    return $return;
  }

  /** Gets all opt keys for an accordion option */
  function handle_accordion_opts( $o ) {

    $opts = array();

    /**
     * This is better than above^^ accounts for changes, always sets defaults.
     * Get the opts so we can make sure we set observables.
     */
    foreach ( $o['opts'] as $opt ) {

      if ( isset( $opt['key'] ) && '' != $opt['key'] ) {
        $opts[ $opt['key'] ] = isset( $opt['default'] ) ? $opt['default'] : '';
      }

      if ( isset( $opt['opts'] ) && is_array( $opt['opts'] ) ) {
        $opts = array_merge( $opts, $this->handle_accordion_opts( $opt ) );
      }
    }
    return $opts;
  }

  /**
   * Set the default accordion value if its not set (or array)
   * Also make sure all OPTS are represented in each array value
   * so that we dont get observable issues at runtime.
   *
   * This also allows us to add options at will...
   */
  function check_accordion_value( $opts, $value, $o ) {

    /** Set basic array is one isn't set */
    if ( ! is_array( $value ) || empty( $value ) ) {

      /** Does the option specifically declare num of default items? */
      $num_items = ( isset( $o['num_items'] ) ) ? $o['num_items'] : 3;

      $value = array();

      for ( $i = 1; $i <= $num_items; $i++ ) {
        $value[] = array();
      }
    }

    foreach ( $value as $index => $entry ) {
      $value[ $index ] = wp_parse_args( $entry, $opts );
    }
    return $value;
  }

  function get_page_data() {

    $this->content_data = array();

    foreach ( $this->list['sections'] as $uid => &$info ) {

      $info['data'] = ( isset( $this->sections_data[ $uid ] ) ) ? $this->sections_data[ $uid ] : array();

      if ( 'PL_Content' == $info['object'] ) {
        $this->content_data = $info['data'];
      }
    }

    unset( $info ); // set by reference
    return $this->list;
  }

  /**
   * Gets the Unique IDs (uids) for all page sections and adds them to an array.
   * @return [array]      [uids on the page]
   */
  function get_uids( $list ) {

    $uids = array();

    foreach ( $list as $uid => $class_name ) {
      $uids[] = $uid;
    }
    return $uids;
  }

  function get_section_settings( $uid ) {

    if ( isset( $this->sections_data[ $uid ] ) ) {
      return $this->sections_data[ $uid ];
    } else {       return array(); }
  }

  /**
   * Process a static region of the page.
   */
  function process_region( $region ) {

    foreach ( $this->map[ $region ]['content'] as $index => $map_meta ) {

      if (
        isset( $map_meta['object'] )
        && ! empty( $map_meta['object'] )
      ) {
        $this->render_section( $map_meta, 0 );
      }
    }
  }

  function render_section( $map_meta, $level = 1 ) {

    /** Section is available, render it in the UI */
    if ( $this->in_factory( $map_meta['object'] ) ) {

      $s = $this->factory[ $map_meta['object'] ];

      $s->meta = $map_meta;

      $s->level = $level;

      $s->content = $map_meta['content'];

      $s->render( $map_meta, $level );

      wp_reset_postdata(); // Reset $post data
      wp_reset_query(); // Reset wp_query

    } else {
      pl_missing_section( $map_meta['object'], $map_meta['clone'], $level );
    }
  }

  function section_standard_wrap( $object, $clone, $content ) {

    return sprintf( '<div class="pl-sn" data-object="%s" data-clone="%s"><div class="pl-sn-wrap"><div class="pl-sn-pad"><div class="pl-content-area">%s</div></div></div></div>', $object, $clone, $content );
  }

  function section_template_load( $s ) {

    // Variables for override
    $override_template = 'template.' . $s->id .'.php';
    $override = ( '' != locate_template( array( $override_template ), false, false )) ? locate_template( array( $override_template ) ) : false;

    if ( false != $override ) {
      require( $override );
    } else {
        $s->section_template();
    }

  }

  function process_styles() {

    foreach ( $this->list['sections'] as $key => $meta ) {

      if ( $this->in_factory( $meta['object'] ) ) {

        $s = $this->factory[ $meta['object'] ];

        $s->meta = $meta;

        $s->section_styles();

        /** Auto load style.css document */
        if ( is_file( $s->base_dir . '/style.css' ) ) {
          $slug = $s->id;

          wp_register_style( 'pl-' . $slug, $s->base_url . '/style.css', array(), $s->settings['version'], 'screen' );
          wp_enqueue_style( 'pl-' . $slug );

        }

        /** Auto load build.css document which is generated from build.less */
        if ( is_file( $s->base_dir . '/build.css' ) ) {
          $slug = $s->id . '-build';
          wp_register_style( $slug, $s->base_url . '/build.css', array(), $s->settings['version'], 'screen' );
          wp_enqueue_style( $slug );

        }
      }
    }

  }

  function process_head() {

    foreach ( $this->list['sections'] as $key => $meta ) {

      if ( $this->in_factory( $meta['object'] ) ) {

        $s = $this->factory[ $meta['object'] ];

        $s->meta = $meta;

        $clone = ( isset( $meta['clone'] ) ) ? $meta['clone'] : '';

        $s->section_head( $clone );
      }
    }
    // show user Scripts
    echo pl_user_setting( 'custom_scripts' );
  }

  function process_foot() {

    foreach ( $this->list['sections'] as $key => $meta ) {

      if ( $this->in_factory( $meta['object'] ) ) {

        $s = $this->factory[ $meta['object'] ];

        $s->meta = $meta;

        $s->section_foot( );

      }
    }
  }

  function get_options_config() {

    $opts_config = array();

    foreach ( $this->list['sections'] as $key => $meta ) {

      if ( $this->in_factory( $meta['object'] ) ) {

        $s = $this->factory[ $meta['object'] ];

        $s->meta = $meta;

        $opts_config[ $s->meta['clone'] ] = array(
          'name'  => $s->name,
        );

        $opts = array();

        // Grab the options
        $opts = $s->section_opts();

        $opts_config[ $s->meta['clone'] ]['opts'] = $opts;

      }
    }
  }

  function opts_add_values( $opts ) {

    if ( is_array( $opts ) ) {
      foreach ( $opts as $index => &$o ) {

        if ( 'multi' == $o['type'] ) {
          $o['opts'] = $this->opts_add_values( $o['opts'] );
        } else {

          if ( 'select_taxonomy' == $o['type'] ) {

            $taxonomy_id = isset( $o['taxonomy_id'] ) ?  $o['taxonomy_id'] : 'category';

            $terms_array = get_terms( $taxonomy_id );

            if ( 'category' == $taxonomy_id ) {
              $o['opts'][] = array( 'name' => '*Show All*' ); }

            foreach ( $terms_array as $term ) {
              if ( is_object( $term ) ) {
                $o['opts'][ $term->slug ] = array( 'name' => $term->name ); }
            }
          }

          // Add the value
          $o['val'] = ( isset( $this->optset->set[ $o['key'] ] ) ) ? $this->optset->set[ $o['key'] ] : array();

        }
      }
      unset( $o );
    }
    return $opts;
  }

  /**
   * Tests if the section is in the factory singleton
   */
  function in_factory( $section ) {

    if ( is_string( $section ) ) {

      return ( isset( $this->factory[ $section ] ) && is_object( $this->factory[ $section ] ) ) ? true : false;

    } else {       return 'Map error. Section ID is not a string'; }
  }
}
/** Factory and parsing classes */
global $plfactory;
$plfactory = new PL_Factory;

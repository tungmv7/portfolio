<?php
/**
 * PageLines Sections Registration Class
 *
 * An object that is used to scan and load sections, then add them to the factory object.
 *
 * @class     PL_Section_Register
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Section_Register {

  /** @var string The slug in the option DB associated with sections data. */
  public $slug = 'sections-register';

  /** @var array Contains all sections data at runtime. */
  public $sections_data = array();

  /** @var array The standard name for sections folders */
  public $sections_folder = '/sections';

  public $name_prefix     = 'PageLines Section ';

  /** @var array Array of the sections associated folders */
  public $sections_dirs = array();


  function __construct() {

      add_action( 'pl_reset_sections',           array( $this, 'reset_sections' ) );
      add_action( 'activate_plugin',             array( $this, 'reset_sections' ) );
      add_action( 'deactivate_plugin',           array( $this, 'reset_sections' ) );
      add_action( 'upgrader_process_complete',   array( $this, 'reset_sections' ) );
      add_action( 'after_switch_theme',          array( $this, 'reset_sections' ) );
  }


  function default_headers() {
    $a = array(
      'name'            => '',                          // Name of the extension
      'pagelines'       => '',                          // Class name if section or true if just a regular plugin
      'author'          => 'PageLines',                 // Author name
      'author_uri'      => 'http://www.pagelines.com/', // URL for author
      'description'     => 'No description',            // Description of the extension
      'plugin_name'     => '',                          // Plugin name (if plugin)
      'tags'            => '',                          // Extension tags for filtering
      'version'         => pl_cache_key(),              // Version number for the extension used for styles/js
      'base_url'        => '',                          // Base URL for extension
      'base_dir'        => '',                          // Directory for extension
      'base_file'       => '',                          // Base file name
      'demo'            => '',                          // Demo URL
      'notes'           => '',                          // Release Post URL
      'filter'          => '',                          // Filter by type (i.e. social vs widget vs gallery etc.. )
      'docs'            => false,                       // Documentation URL
      'loading'         => 'active',                    // How to handle the initial loading of the section (refresh, active)
      'icon'            => '',                          // Path to the section icon
      'section'         => '',
      'contain'         => '',// Can contain other sections
    );

    return $a;
  }


  /**
   * Return sections data as an array
   * Also runs on 'after_setup_theme' to make sure they are ready to go at run time.
   * @param  boolean $type [type of section data to get]
   * @return [array]        [sections data]
   */
  function get_sections( $type = false, $reregister = false ) {

    /** Check if this has been done already on this page load... */
    if ( is_array( $this->sections_data ) && ! empty( $this->sections_data ) ) {

      $data = $this->sections_data;

    } else {

      /** Allow for reregister via plreg variable in URL */
      $reregister = ( isset( $_GET['plreg'] ) || $reregister ) ? true : false;

      /** Get whatever is in the DB for sections now. */
      $data = get_theme_mod( $this->slug );

      /**  if no data is stored, it was either reset or never created */
      if ( ! is_array( $data ) || empty( $data ) || $reregister ) {

        $data = $this->save_sections( $this->generate_data() );

      }

      $this->sections_data = $data;

    }

    /** by now we are sure to have an array even if its empty */
    return ( $type && isset( $data[ $type ] ) ) ? $data[ $type ] : $data;

  }



  /**
   * Parse all sections.
   * Grabs them from all dirs and from ones installed via plugin.
   * @return [array] [All sections data]
   */
  function generate_data() {

    $sections = array();

    $section_dirs = $this->get_section_dirs();

    $sections['plugins'] = $this->get_all_plugins();

    /** Get raw sections data using the folders */
    foreach ( $section_dirs as $type => $d ) {

      if ( is_dir( $d['dir'] ) ) {

        $sections[ $type ] = $this->get_sections_data( $d['dir'], $d['url'] );

      }
    }

    return $sections;
  }

  /**
   * Set data for section
   */
  function set_data( $data ) {

    $a = $this->default_headers();

    $d = wp_parse_args( $data, $a );

    $d['screenshot'] = ( is_file( $d['base_dir'] . '/screenshot.png' ) ) ? $d['base_url'] . '/screenshot.png' : '';

    return $d;
  }

  function set_default_wp_headers() {

    $a = $this->default_headers();

    $headers = array();

    foreach ( $a as $key => $name ) {

      if ( 'author_uri' == $key ) {
        $headers[ $key ] = 'Author URI';
      } else {
        $headers[ $key ] = ucwords( str_replace( '_', ' ', $key ) );
      }
    }

    return $headers;
  }

  /**
   * Get section data form PHP files.
   */
  function get_sections_data( $dir, $url ) {

    if ( ! is_dir( $dir ) ) {
      return; }

    /** WordPress requires that you tell it what headers you want */
    $default_headers = $this->set_default_wp_headers();

    $sections = array();

    // setup out directory iterator.
    // symlinks were only supported after 5.3.1
    // so we need to check first ;)
    $it = ( strnatcmp( phpversion(), '5.3.1' ) >= 0 )
      ? new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::FOLLOW_SYMLINKS ) , RecursiveIteratorIterator::SELF_FIRST )
      : new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, RecursiveIteratorIterator::CHILD_FIRST )
      );

    foreach ( $it as $fullfilename => $filesplobject ) {

      /** If not a PHP file continue */
      if ( 'php' != pathinfo( $fullfilename, PATHINFO_EXTENSION ) ) {
        continue; }

      /** Get header information from file in array */
      $headers  = get_file_data( $fullfilename, $default_headers );

      $headers = $this->unset_empty_values( $headers );

      // If there is a class_name header, then its a section
      if ( ! isset( $headers['pagelines'] ) ) {
        continue; }

      /** Get the Folder name for the section */
      preg_match( '#[\/|\-]sections[\/|\\\]([^\/|\\\]+)#', $fullfilename, $out );

      $folder = sprintf( '/%s', $out[1] );

      $headers['id'] = str_replace( '/', '', $folder );

      // base values

      $base_dir = $dir . $folder;
      $base_url = $url . $folder;

      // $filters = ( isset( $headers['filter'] ) ) ? explode( ',', $headers['filter'] ) : array();

      // $filters = implode( $filters, ',' );

      $name = ( isset( $headers['plugin_name'] ) && '' != $headers['plugin_name'] ) ? $headers['plugin_name'] : $headers['section'];

      /** Remove standard prefix from name */
      $name = str_replace( $this->name_prefix, '', $name );

      $name = str_replace( 'Section', '', $name );

      $new = array(
        'name'        => $name,
        'base_url'    => $base_url,
        'base_dir'    => $base_dir,
        'base_file'    => $fullfilename,
      );

      $headers = wp_parse_args( $new, $headers );

      $objects = $this->headers_get_objects( $headers );

      if ( is_array( $objects ) ) {
        foreach ( $objects as $obj ) {

          $headers['class'] = $obj;

          $sections[ $obj ] = $this->set_data( $headers );
        }
      }
    }

    return $sections;

  }

  /**
   * WP gets information and sets empty strings for headers that aren't set.
   * Lets unset all that so we can use wp_parse_args effectively to set better defaults
   */
  function unset_empty_values( $raw ) {

    $clean = array();

    foreach ( $raw as $key => $value ) {
      if ( '' !== $value ) {
        $clean[ $key ] = $value;
      }
    }
    return $clean;
  }


  /**
   * Resets section data to empty and forces a reparse
   */
  function reset_sections() {
    set_theme_mod( $this->slug , array() );
  }

  /**
   * Saves updated section information
   * @param  array $data All sections data
   * @return arrra $data So we have latest data to assign
   */
  function save_sections( $data ) {

    set_theme_mod( $this->slug , $data );

    /** return so we have latest data */
    return $data;
  }


  /**
   * Get an array of all folders that contain sections within this install.
   * @return [array] Array of the paths.
   */
  function get_section_dirs() {

    // TODO remove duplicated directories

    $this->section_dirs = array(
      'parent'  => array(
        'dir' => get_template_directory() . $this->sections_folder,
        'url'  => get_template_directory_uri() . $this->sections_folder,
      ),
      'custom'  => array(
        'dir' => get_stylesheet_directory() . $this->sections_folder,
        'url'  => get_stylesheet_directory_uri() . $this->sections_folder,
      ),
    );

    if ( get_stylesheet_directory() == get_template_directory() ) {
      unset( $this->section_dirs['custom'] );
    }

    return apply_filters( 'pl_platform_sections_dirs', $this->section_dirs );
  }

  /**
   * Uses the SectionFactory class to instantiate all section classes and add to factory
   */
  function register_sections() {

    $factory_register = new PL_Section_Factory();

    /** Needed to prevent file double loading */
    $included_files = array();

    foreach ( $this->get_sections() as $group => $sections ) {

      foreach ( $sections as $s ) {

        /** Don't double load files if they have multiple sections included */
        if ( ! isset( $included_files[ $s['base_file'] ] ) && ! class_exists( $s['class'] ) && is_file( $s['base_file'] ) ) {

          $included_files[ $s['base_file'] ] = true;

          include( $s['base_file'] );

        }

        if ( class_exists( $s['class'] ) ) {
          $factory_register->register( $s['class'], $s );
        }
      }
    }

    return $factory_register;

  }

  // list plugins filter out non PageLines
  function get_all_plugins() {

    /** WP requires we tell this function what we want */
    $default_headers = $this->set_default_wp_headers();

    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    $installed_plugins = get_plugins();

    $pl_plugins = array();

    foreach ( $installed_plugins as $path => $plugin ) {

      if ( ! is_plugin_active( $path ) ) {
        continue; }

      $fullpath = sprintf( '%s%s', trailingslashit( WP_PLUGIN_DIR ), $path );

      $headers = get_file_data( $fullpath, $default_headers );

      if ( empty( $headers['pagelines'] )
          || 'true' == $headers['pagelines']
          || 'True' == $headers['pagelines']
          || 'internal' == $headers['pagelines']
          || 'plugin' == $headers['pagelines']
          ) {

        unset( $installed_plugins[ $path ] );

      } else {

        $headers['base_dir']   = dirname( $fullpath );
        $headers['base_url']   = untrailingslashit( plugins_url( '', $path ) );
        $headers['base_file']  = $fullpath;

        $name = str_replace( $this->name_prefix, '', $headers['plugin_name'] );

        $new = array(
          'type'      => 'plugin',
          'name'      => $name,
        );

        $headers = wp_parse_args( $new, $headers );

        $objects = $this->headers_get_objects( $headers );

        if ( is_array( $objects ) ) {
          foreach ( $objects as $obj ) {

            $headers['class'] = $obj;

            $pl_plugins[ $obj ] = $this->set_data( $headers );
          }
        }
      }
    }

    return $pl_plugins;
  }
  /**
   * find classname, should be in PageLines, use Class Name as fallback
   * @return [string] ClassName
   */
  function headers_get_objects( $headers ) {

    if ( isset( $headers['pagelines'] ) && 'true' != $headers['pagelines'] ) {
      $class = $headers['pagelines'];
    } /** Old way of doing this. */
    elseif ( isset( $headers['class_name'] ) ) {
      $class = $headers['class_name'];
    } /** Plugin in a sections folder. Should be included but not called. */
    elseif ( strpos( $headers['base_dir'], 'sections' ) !== false ) {
      $class = $headers['id']; } else {       $class = false; }

    return explode( ',', str_replace( ' ', '', $class ) );

  }
}

/**
 * Start the registration engines...
 */
global $pl_sections_register;
$pl_sections_register = new PL_Section_Register;

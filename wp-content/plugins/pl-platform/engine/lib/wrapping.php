<?php
/**
 * Theme Wrapper
 *
 * @class     PL_Wrapping
 * @version   5.0.0
 * @link http://scribu.net/wordpress/theme-wrappers.html
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
function pl_template_path() {
  return PL_Wrapping::$main_template;
}


/**
 * The base file name for the template
 */
function pl_template_base() {

  return PL_Wrapping::$base;
}


class PL_Wrapping {
  // Stores the full path to the main template file q
  public static $main_template;

  // Basename of template file
  public $slug;

  // Array of templates
  public $templates;

  // Stores the base name of the template file; e.g. 'page' for 'page.php' etc.
  public static $base;

  public function __construct( $template = 'pl-base.php' ) {

    $this->slug = basename( $template, '.php' );

    $this->templates = array( $template );

    /** Allow base.php override */
    if ( self::$base ) {

      $str = substr( $template, 0, -4 );

      array_unshift( $this->templates, sprintf( $str . '-%s.php', self::$base ) );

    }

    new PL_Integration;
  }

  //
  // Magic : http://php.net/manual/en/language.oop5.magic.php#object.tostring
  //
  public function __toString() {

    $this->templates = apply_filters( 'pl_wrapping_' . $this->slug, $this->templates );

    $path = locate_template( $this->templates );

    if ( '' == $path ) {
      return pl_framework_dir() . '/lib/pl-base.php';
    } else {
      return $path;
    }

  }

  public static function wrap( $main ) {

    // Check for other filters returning null
    if ( ! is_string( $main ) ) {
      return $main;
    }

    self::$main_template = $main;
    self::$base = basename( self::$main_template, '.php' );

    // DMS Hack, if we're on index.php then it should have render set to true. This wrap prevents it without.
    if (  'index' == self::$base ) {
      global $pagelines_render;
      $pagelines_render = true;
    }

    return new PL_Wrapping();
  }
}

add_filter( 'template_include', array( 'PL_Wrapping', 'wrap' ), 99 );

/**
 * Special content wrap is for plugins that operate outside of pagelines
 * We started doing things manually, so there are legacy extensions still using manual methodology
 *
 * @uses $pl_template_render // this is set in the main pagelines setup_pagelines_template(); function
 **/

class PL_Integration {

  function __construct() {

    global $pl_static_template_output;
    global $pl_running_integration;

    $pl_static_template_output = false;

    add_action( 'pl_start_template', array( $this, 'do_footer' ) );

    /** Capture template and output in content */
    if ( pl_is_static_template( 'int' )  ) {

      add_action( 'pl_start_template', array( $this, 'start_new_integration' ) );
      add_action( 'pl_after_template', array( $this, 'get_integration_output' ) );
    }
  }


  function do_footer() {

    remove_all_actions( 'pagelines_start_footer' );

    /**
     * Problem / Solution Statement
     * 1. All themes run get_footer in template which prevents PL from working correctly
     * 2. However, some themes/shortcodes add stuff to globals or add new actions to wp_footer
     *
     * Solution:
     * So solve first problem we run get_footer here, but first move all actions off of wp_footer to a workaround action
     * Then we reset wp_footer where it can pick up new actions.
     * Run the workaround action and wp_footer again in the real footer of the page.
     */

    global $wp_filter;

    $wp_filter['pl_footer_workaround'] = $wp_filter['wp_footer'];

    remove_all_actions( 'wp_footer' );

    global $get_footer_output;

    ob_start();

    get_footer();

    $get_footer_output = ob_get_clean();

  }



  function start_new_integration() {

    global $pl_running_integration;
    $pl_running_integration = true;

    /** Start a buffer to capture plugin output (which we'll add to our content section ) */
    ob_start();

  }

  function get_integration_output() {

    global $pl_static_template_output;
    global $pl_running_integration;

    $this->wrap_start   = '<div class="static-template" ><div class="static-template-content">';
    $this->wrap_end   = '</div></div>';

    $content = apply_filters( 'pl_static_template_output', ob_get_clean() );

    $pl_static_template_output = sprintf( '%s%s%s', $this->wrap_start, $content, $this->wrap_end );

    $pl_running_integration = false;

    pl_primary_template();

  }
}

/** Add information to the header */
function pl_get_header() {

  ob_start();

  get_header();

  $header = ob_get_clean();

  $header = str_replace( '<head>', sprintf( '<head>%1$s %2$s %1$s', "\n", '<!-- Built With PageLines Platform 5 | http://www.pagelines.com/platform -->' ), $header );

  echo $header;
}

function pl_get_footer() {

  global $get_footer_output;

  echo pl_remove_closing_tags( $get_footer_output );

  /** Run WP Footer action again for any shortcodes, etc.. that may have placed new actions there. */
  do_action( 'wp_footer' );

  /** Takes all original wp_footer actions (see above) */
  do_action( 'pl_footer_workaround' );

  do_action( 'wp_print_footer_scripts' );

  /** All JSON data from PL */
  do_action( 'pl_json_data' );

  printf( '</body></html><!-- Thanks for stopping by. Have an amazing day! -->' );

}

function pl_remove_closing_tags( $in ) {

  $out = str_replace( '</body>', '', $in );

  $out = str_replace( '</html>', '', $out );

  return $out;
}

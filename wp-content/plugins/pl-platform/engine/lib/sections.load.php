<?php
/**
 * Section AJAX Loading
 *
 * Called from JS. This class loads section templates and other information and returns it to the page via AJAX.
 *
 * @class     PL_Sections_Load
 * @version    5.0.0
 * @package    PageLines/Classes
 * @category  Class
 * @author     PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Sections_Load {

  function __construct() {

    add_action( 'pl_server_load_section', array( $this, 'load_section' ), 10, 2 );

  }

  /**
   * Load a section via ajax
   */
  function load_section( $response, $data ) {

    $map_meta = array(
      'clone'     => $data['UID'],
      'object'    => $data['object'],
      'content'   => array(),
    );

    $level = 0;

    global $plfactory;

    if ( is_object( $plfactory->factory[ $map_meta['object'] ] ) ) {

      /** We need to setup the $post variable as if this is a normal page load */
        global $wp_query;
        global $pl_page;

      /** Get the section object */

        $s = $plfactory->factory[ $map_meta['object'] ];

        $s->level   = 0;
        $s->meta    = $map_meta;
        $s->content = $map_meta['content'];

      /** Set basic page variables, for loop */

        $pl_page->type = 'loading';

      /** Set post data in case its used */

        //$post = get_post( $data['pageID'] );

      /** Set query data in case its used, for example in PostLoop or Docker, will be incorrect for archive/category pages ( no ID ) */

      $query = ( empty( $data['query'] ) ) ? 'post_type=post&post_status=published' : $data['query'];

      $wp_query = new WP_Query( $query );

      $GLOBALS['post'] = $wp_query->post;

      /** CSS - style.css */
      if ( is_file( $s->base_dir . '/style.css' ) ) {
        $response['css_style'] = $s->base_url . '/style.css';
      }

      /** CSS - build.css */
      if ( is_file( $s->base_dir . '/build.css' ) ) {
        $response['css_build'] = $s->base_url . '/build.css';
      }

      /**
       * JAVASCRIPT + CUSTOM STYLES
       * Load scripts using PL enqueue function and pl_live_scripts global
       */

      global $pl_live_scripts;
      global $pl_live_styles;

      $pl_live_scripts = array();
      $pl_live_styles = array();

      $s->section_styles();

      $response['scripts'] = $pl_live_scripts;

      /**
       * OPTIONS & MODEL - Get the options for the section, json encoded
       */
      global $plfactory;

      $response['model'] = array_merge( $plfactory->recursive_parse_opts( $s->section_opts() ), pl_defaults_model( $s->section_defaults() ) );

      /**
       * TEMPLATE - Get the section template and assign to a variable to be returned in response
       */
      ob_start();

        $s->active_loading = true;

        $s->render( $map_meta, $level );

      $section_template = ob_get_clean();

      $response['template'] = $section_template;

      /** HEAD -- Do section <head> */
      ob_start();
      $s->section_head();
      $head = ob_get_clean();

      $response['head'] = $head;

      /** FOOT -- Do section <foot> */
      ob_start();
      $s->section_foot();
      $foot = ob_get_clean();

      $response['foot'] = $foot;
    }

    return $response;

  }
}

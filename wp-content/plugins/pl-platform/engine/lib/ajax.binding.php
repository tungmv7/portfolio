<?php
/**
 * Binding Class
 *
 * Handles PHP related to knockout binding and editing
 *
 * @class     PL_Binding
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Binding {


  /**
   * Constructor for the class. Loads globals, options and hooks in the init method.
   *
   * @access public
   * @return void
   */
  function __construct() {

    add_action( 'pl_server_async_binding', array( $this, 'do_binding' ), 10, 2 );

  }

  function do_binding( $response, $data ) {

    $mode   = $data['mode'];
    $value  = $data['value'];
    $id     = $data['pageID'];
    $args   = isset( $data['args'] ) ? $data['args'] : array();

    global $post;

    if ( isset( $args['id'] ) ) {
      $post = get_post( $args['id'] ); } else {       $post = get_post( $id ); }

    if ( 'media' == $mode ) {

      $media_args = array( 'thumb-size' => $value );

      $media_args = wp_parse_args( $args, $media_args );

      $response['template'] = pagelines_media( $media_args );
    } elseif ( 'shortcodes' == $mode ) {
      $response['template'] = stripslashes_deep( do_shortcode( $value ) );

      //global $pl_shortcode_engine;
      //$response['engine']   =  array( 'scripts' => $pl_shortcode_engine->scripts, 'styles' => $pl_shortcode_engine->styles );
    } elseif ( 'autop' == $mode ) {
      $response['template'] = do_shortcode( wpautop( $value ) );
    } elseif ( 'sidebar' == $mode ) {

      $response['template'] = pl_draw_sidebar( $value );
    } elseif ( 'menu' == $mode ) {

      $menu_args = array( 'menu' => $data['value'], 'do_fallback' => true );

      $menu_args = wp_parse_args( $args, $menu_args );

      $response['template'] = pl_nav( $menu_args );
    } else {
      $response = apply_filters( 'pl_binding_'.$mode, $response, $data );
    }

    return $response;
  }
}

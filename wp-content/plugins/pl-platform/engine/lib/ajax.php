<?php
/**
 * Saving Interaction Class
 *
 * Takes AJAX requests and creates a hook and other interfaces related to JS based saving of information.
 *
 * @class     PLAJAXInit
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PLAJAXInit {

  /**
   * Constructor for the class. Loads globals, options and hooks in the init method.
   */
  function __construct() {

    /**
     * Main saving hook tied to WPs AJAX interface
     * Uses an $_POST['action'] variable of 'pl_server'
     */
    add_action( 'wp_ajax_pl_server', array( $this, 'save_hook' ) );

    add_action( 'wp_ajax_nopriv_pl_server', array( $this, 'save_hook' ) );
  }


  function save_hook() {

    /** Incoming post data */
    $postdata = $_POST;

    if ( isset( $postdata['nonce'] ) ) {
      pl_verify_ajax( $postdata['nonce'] );
    } else {
      die( 'No Nonce set!' );
    }

    /** If doing an upload. */
    if ( isset( $_FILES ) ) {
      $postdata['files'] = $_FILES; }

    /** Start response variable, sent back at end of request. */
    $response = array();

    /** Send back the data we recieved */
    $response['post'] = $postdata;

    /** The saving hook  */
    $hook = $postdata['hook'];

    /** The specific thing to run  ( not used? )*/
    //  $run = $postdata['run'];

    /** Page information TODO used? */
    // $pageID = $postdata['pageID'];
    // $typeID = $postdata['typeID'];

    /** Debug information  */
    $response['dataAmount'] = ( isset( $_SERVER['CONTENT_LENGTH'] ) ) ? (int) $_SERVER['CONTENT_LENGTH'] : 'No Value';

    /**
     * Trigger hook, send the functions the data and response for update
     * If user is logged out then only do nopriv options
     */
    if ( pl_can_use_tools() ) {
      $response = apply_filters( 'pl_server_' . $hook, $response, $postdata );
      $response = apply_filters( 'pl_server_nopriv_' . $hook, $response, $postdata );
    } else {
      $response = apply_filters( 'pl_server_nopriv_' . $hook, $response, $postdata );
    }

    /** JSON response for output and UI actions */
    header( 'Content-Type: application/json' );
    echo json_encode( pl_convert_arrays_to_objects( $response ) );

    die(); // don't forget this, always returns 0 w/o
  }
}

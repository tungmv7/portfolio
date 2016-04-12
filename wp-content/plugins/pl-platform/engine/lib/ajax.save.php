<?php
/**
 * Save Page Data
 *
 * Takes an AJAX Request with map, model from page and saves it.
 *
 * @class     PL_Save_Data
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Save_Data{

  function __construct() {

    /** Save Page Data */
    add_action( 'pl_server_save_page', array( $this, 'save_page' ), 10, 2 );

    /** Save and Create New Template */
    add_action( 'pl_server_tpl_action', array( $this, 'tpl_action' ), 10, 2 );

  }

  function tpl_action( $response, $data ) {

    $response = pl_tpl_action( $response, $data );

    return $response;

  }

  function save_page( $response, $data ) {

    $this->pageid       = $data['pageID'];  // Used for page specifically
    $this->editid       = $data['editID'];  // Used based on whether scoped to type, tax or page

    $this->editslug     = $data['editslug'];  // Used based on whether scoped to type, tax or page

    $response = $this->save_model( $response, $data );

    $response = $this->save_map( $response, $data );

    $response = $this->save_meta( $response, $data );

    return apply_filters( 'pl_standard_save', $response, $data );

  }

  /**
   * Save meta information to the page post ID.
   * We need this to run queries and such.
   */
  function save_meta( $response, $data ) {

    $tplmode      = $data['tplMode'];

    /** Save scope template mode */
    update_post_meta( $this->pageid, 'pl_template_mode',    $tplmode );

    return $response;
  }

  function save_model( $response, $data ) {

    /** Convert json.stringify into an associative array */
    $model = json_decode( stripslashes( $data['model'] ), true );

    if ( is_array( $model ) ) {

      global $pl_sections_data;

      foreach ( $model as $uid => $mod ) {

        /** Add a saved flag so we dont set defaults anymore... */
        $mod['saved'] = 1;

        $pl_sections_data->update_or_insert( $uid, $mod );

      }
    }

    $response['model'] = $model;

    return $response;
  }

  function save_map( $response, $data ) {

    global $maps_data_handler;

    $response['result'] = $maps_data_handler->save_map( $data['map'], $this->editslug );

    return $response;
  }
}

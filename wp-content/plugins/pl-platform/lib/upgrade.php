<?php
/**
 * Data Updates Handler
 *
 * Handles Updates for Platform data
 *
 * @class     PL_Platform_Upgrader
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Platform_Upgrader {

  function __construct() {
    add_action( 'admin_init', array( $this, 'oauth_data_update' ) );
  }

  function oauth_data_update() {

    // do we run?
    if ( ! get_option( '_pl_oauth_data' ) ) {
      $data = get_user_meta( wp_get_current_user()->ID, '_pl_oauth_data', true );
      if ( $data ) {
        update_option( '_pl_oauth_data', $data );
        delete_user_meta( wp_get_current_user()->ID, '_pl_oauth_data' );
      }
    }
  }
}
new PL_Platform_Upgrader;

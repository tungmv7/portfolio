<?php
/**
 * Section Settings Class
 *
 * @class     PL_Sections_Settings
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Sections_Settings {

  function __construct() {

    /** Select Term AJAX Option */
    add_action( 'pl_server_select_term', array( $this, 'select_term' ), 10, 2 );

  }

  function select_term( $response, $data ) {

    $response['opts'] = pl_get_terms_for_selection( $data['pt'] );

    return $response;
  }
}

global $pl_section_settings_handling;
$pl_section_settings_handling = new PL_Sections_Settings();

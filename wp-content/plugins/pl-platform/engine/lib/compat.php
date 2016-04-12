<?php
/**
 * Compatibility layer for themes & plugins
 *
 * @class     PL_Compatibility
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Compatibility {

  function __construct() {

    add_filter( 'pl_static_template_output',  array( $this, 'dms_integration_output_buffer_cascade_issue' ) );
    add_action( 'plugins_loaded',             array( $this, 'plugins_loaded_action' ) );
    add_filter( 'pl_content_archive_special', array( $this, 'check_archive' ) );
  }

  // bbpress special pages are sort of archives and single pages
  // confuses the template system, so we add a filter in the content section
  // to check if we are in a bbpress archive, user profile page etc etc
  function check_archive() {
    if ( function_exists( 'is_bbpress' ) ) {
      return is_bbpress();
    }
  }

  /**
   * DMS Hack for compatibility
   * DMS uses a output buffer for integrations, they are nested. Look at same trigger function
   * If it is active then actually grab the other buffer as the output, then start a new one that is blank to match
   * the ob_get_clean applied in DMS footer.
   */
  function dms_integration_output_buffer_cascade_issue( $content ) {

    if ( function_exists( 'do_special_content_wrap' ) && do_special_content_wrap() ) {

      global $integration_out;

      $integration_out = $content;

      ob_start();

      pagelines_template_area( 'pagelines_template', 'templates' );

      $content = ob_get_clean();

    }
    return $content;
  }
  /**
   * Action run during 'plugins_loaded'
   */
  function plugins_loaded_action() {

    /**
     * NextGen Gallery uses buffers to insert scripts and styles
     * This conflicts with Platform, so we turn it off.
     */
    define( 'NGG_DISABLE_RESOURCE_MANAGER', true );
  }
}
new PL_Compatibility();

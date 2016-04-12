<?php
/**
 * AJAX Upload Handling
 *
 * Handles an image that is uploaded.
 *
 * @class     PLAJAXUpload
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PLAJAXUpload {


  /**
   * Constructor for the class. Loads globals, options and hooks in the init method.
   */
  function __construct() {

    /**
     * Main saving hook tied to WPs AJAX interface
     * Uses an $_POST['action'] variable of 'pl_server'
     */
    add_action( 'pl_server_pl_image_upload', array( $this, 'handle_upload' ), 10, 2 );
  }

  function handle_upload( $response, $data ) {

    $files_base = $data['files']['file'];

    /** Add SVG and other new types */
    add_filter( 'upload_mimes', array( $this, 'tmp_mime_overide' ) );

    $arr_file_type = wp_check_filetype( basename( $files_base['name'] ) );
    $uploaded_file_type = $arr_file_type['type'];

    // Set an array containing a list of acceptable formats
    $allowed_file_types = array( 'image/jpg','image/jpeg','image/gif','image/png', 'image/x-icon', 'image/svg+xml' );

    if ( in_array( $uploaded_file_type, $allowed_file_types ) ) {

      $files_base['name'] = preg_replace( '/[^a-zA-Z0-9._\-]/', '', $files_base['name'] );

      $override['test_form'] = false;
      $override['action'] = 'wp_handle_upload';

      $uploaded_file = wp_handle_upload( $files_base, $override );

      $name = sprintf( '%s%s',
          apply_filters( 'pl_up_image_prefix', 'PageLines-' ),
          addslashes( $files_base['name'] )
      );

      $attachment = array(
              'guid'        => $uploaded_file['url'],
              'post_mime_type'  => $uploaded_file_type,
              'post_title'    => $name,
              'post_content'    => '',
              'post_status'   => 'inherit',
            );

      $attach_id = wp_insert_attachment( $attachment, $uploaded_file['file'] );
      $attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] );
      wp_update_attachment_metadata( $attach_id,  $attach_data );

      do_action( 'after_pl_up_image', $attach_id, $attach_data );

    } else {       $uploaded_file['error'] = __( 'Unsupported file type!', 'pl-platform' ); }

    /** if there was an error...  */
    if ( ! empty( $uploaded_file['error'] ) ) {

      $error = sprintf( __( 'Upload Error: %s', 'pl-platform' ) , $uploaded_file['error'] );

      $response = wp_parse_args( array( 'error' => $error ), $response );

    } /** We're all good, tread the image and send it back. */
    else {

      /** SIZE - If a specific crop is requested */
      if ( isset( $data['size'] ) && '' != $data['size'] ) {

        $image_array = wp_get_attachment_image_src( $attach_id, $data['size'] );
        $url = $image_array[0];

      } else {         $url = $uploaded_file['url']; }

      /** SHORTCODE - Replace with path shortcodes */
      $url = pl_uploads_shortcode_url( $url );

      /** Set the return response */
      $response = wp_parse_args( array( 'url' => $url, 'success' => true, 'attach_id' => $attach_id ), $response );

    }
    remove_filter( 'upload_mimes', 'pl_tmp_mime_overide' );

    return $response;
  }

  function tmp_mime_overide( $existing_mimes = array() ) {
    $existing_mimes['svg'] = 'image/svg+xml';
    return $existing_mimes;
  }
}

<?php
/*

  Plugin Name:    PageLines Plugin Import Export
  Description:    Import and Export all Platform Settings.

  Author:         PageLines
  Author URI:     http://www.pagelines.com
  Demo:           

  Version:        5.0.3
  PageLines:      plugin

  Category:       wordpress, free, plugins, featured, framework

  Tags:           export,import,templates

*/
class PL_Import_export {

  function __construct(){
    add_filter( 'pl_platform_settings_array',  array( $this, 'add_plugin_options' ) );
    add_action( 'admin_enqueue_scripts',       array( $this, 'enqueue' ) );
    add_action( 'admin_init',                  array( $this, 'export_data' ) );
    add_action( 'wp_ajax_pl_import',           array( $this, 'pl_import_callback' ) );
  }
  
  function pl_import_callback() {
    
    // read the uploaded file
    $data = file_get_contents( $_FILES['file']['tmp_name'] );
    /** JSON response for output and UI actions */
    header('Content-Type: application/json');
    echo json_encode( $this->import_data( $data ) );
    die();
  }
  
  function enqueue() {        
  	wp_enqueue_script( 'ajax-script', plugins_url( '/js/export.js', __FILE__ ), array('jquery') );
  }
  
  function export_data() {
    if( isset( $_GET['pl_export_data'] ) ) {
      // verify nonce
      if( wp_verify_nonce( $_GET['_wpnonce'], 'pl_export') ) {
        // output data as jason file to browser.
        $data = $this->generate_export_json();
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=platform_data.json');
        echo $data;
        die();
      }
    }
  }
  
  function add_plugin_options( $options ){

    $options['export_page'] = array(
        'icon'  => 'upload',
        'title' => __( 'Import / Export', 'pl-plugin-import-export' ),
        'location'   => array('settings'),
        'pos'   => 1000,
        'opts'  => array(
          array(
            'key'       => 'pl_export',
            'type'      => 'link',
            'title'     => 'Export PageLines Data',
            'label'     => '<i class="pl-icon pl-icon-download"></i> Download PageLines Data',
            'val'       => wp_nonce_url( admin_url( 'admin.php?page=pl-platform-settings&pl_export_data=1'), 'pl_export' ),
            'class'     => 'button-primary',
            'help'      => 'This button will download a PageLines settings file which includes an export of PageLines settings, templates and section data in csv format.'
          ),
          
          array(
            'key'       => 'pl_import',
            'title'     => 'Import PageLines Data',
            'type'      => 'longform',
            'label'     => 'Upload PageLines Data',
            'text'     => '<input id="pl_import" type="file" name="pl_import" /><br /><br /><button class="button button-primary pl_import_submit">Upload</button>&nbsp;&nbsp;&nbsp;<span class="pl_import_feedback"></span>',
            'help'      => 'Upload a PageLines settings file. Note that post IDs must match between installs for templates to match.'
          ),
          
          array(
            'key'       => 'wp_import_export',
            'title'     => 'WordPress Import/Export',
            'label'     => '<i class="pl-icon pl-icon-wordpress"></i> WordPress Import/Export utility',
            'type'      => 'link',
            'class'     => 'button-secondary',
            'val'       =>  admin_url( 'export.php' ),
            'help'      => 'For importing and exporting standard WordPress data, use the native import/export utility.',
          )

        )
      );
    return $options;
  }

  /**
   * Generate data dump callback function
   */
  function generate_export_json() {
    
    global $wpdb, $pl_sections_data, $maps_data_handler;
    $data = array();
    
    // fetch map data..
    $data['map'] = $maps_data_handler->dump_map();
    
    // we need to loop through the map and grab post_meta
    foreach( $data['map'] as $k => $map ) {
      if( is_numeric( $map->uid ) ) {
        $post = get_post($map->uid );
        $data['map'][$k]->pl_template_mode = get_post_meta( $map->uid, 'pl_template_mode', true );
        $data['map'][$k]->slug = $post->post_name;
      }
    }
    
    // fetch sections data..
    $data['sections'] = $pl_sections_data->dump_sections();
    
    // now we just fetch usermata > 60000000 and > 70000000    
    $data['special'] = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE post_id > 600000" );
    
    $data['main'] = get_option( 'pagelines-settings' );
    
    $data = apply_filters( 'pl_export_data', $data );
    
    return json_encode( $data );
  }

  /**
   * Data coming back from the upload button?
   * 
   * this should just be a reverse of the generate function
   */
  function import_data( $data ) {
    global $wpdb, $pl_sections_data, $maps_data_handler;
    
    $data = stripslashes_deep( json_decode( $data ) );
    $defaults = array(
      'sections'  => array(),
      'map'       => array(),
      'special'   => array(),
      'main'      => array()
    );
    
    $import = wp_parse_args( $data, $defaults );
    update_option( 'pagelines-settings', (array) $import['main'] );
    
    // special 1st
    foreach( $import['special'] as $k => $special ) {
      if( isset( $special->key_value ) ) {
        update_post_meta( $special->post_id, $special->meta_key, $special->key_value );
      }
    }
    
    // now sections..
    foreach( $import['sections'] as $k => $section ) {
      $pl_sections_data->update_or_insert( $section->uid, json_decode( $section->json ) );
    }
    
    // now map
    // if the map object has post_meta we need to find post with same slug..
    foreach( $import['map'] as $k => $map ) {
      if( isset( $map->slug ) ) {
        $slug = $map->slug;
        $id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s", $slug ) );
        
        // if we have a matching post slug then insert our data...
        if( $id ) {
          update_post_meta( $id, 'pl_template_mode', $map->pl_template_mode );
          
          $maps_data_handler->update_or_insert( $map->uid, json_decode( $map->live ) );
        }
      } else {
        // header, footer etc etc
        $maps_data_handler->update_or_insert( $map->uid, json_decode( $map->live ) );
      }
    }
    
    do_action( 'pl_import_data', $import );
    // done!
    return array('success' => true );
  }

}
new PL_Import_export();

<?php
/*
  
  Plugin Name:    PageLines Plugin Custom LESS
  Description:    Add custom LESS code real-time and globally from the front end.

  Author:         PageLines
  Author URI:     http://www.pagelines.com

  Version:        5.0.5
  
  PageLines:      true
  
  Tags:           utilities, page, navigation, panels
  
  Category:       framework, plugins, featured, free
  
  Filter:         plugins

*/

new PL_Plugin_Custom_Less(); 

class PL_Plugin_Custom_Less {

  function __construct(){

    $this->id = 'pl-custom-less';
    $this->url = plugins_url( '', __FILE__ );

    /** If no PL Framework, then prevent errors. */
    if( ! function_exists('PL') || ! function_exists('PL_Platform') )
      return;

    add_action( 'pl_after_data_setup', array($this, 'setup_ui' ) ); 

    /** Always load since AJAX */
    add_filter( 'pl_standard_save',  array($this, 'save_styles'), 10, 2 );


    add_action( 'pl_workarea_enqueue',  array($this, 'scripts'));
  }

  

  function setup_ui(){


    add_filter( 'pl_ab_menu',           array($this, 'add_menu_item') );
        
    add_action( 'wp_head',              array($this, 'css_head'),     1000  );
    add_action( 'wp_footer',            array($this, 'custom_less'),  99 );
  }

  function add_menu_item( $items ){
    
    $editor_url = ( is_admin() ) ? home_url() : pl_get_current_url();
    
    $items['less'] = array(
          'id'      => 'pl-ab-code',
          'title'   => '<i class="pl-icon pl-icon-code"></i> LESS/CSS',
          'rel'     => 'plCode',
          'href'    => add_query_arg( array( 'pl_tool' => 'plCode', 'pl_edit' => 'on', 'pl_start' => 'yes' ), 
         $editor_url )
        );

    return $items;
  }

  function scripts(){
    

    pl_script( 'less',    $this->url . '/less.js',    array('jquery'), false, false );
    pl_script( $this->id, $this->url . '/script.js',  array('jquery'), false, false );

    pl_load_codemirror( PL_Platform()->base_url . '/engine/ui/plugins' ); 

  }

  function css_head(){

    printf('<style id="pl-custom-css">%s</style>', pl_user_setting('custom_css') );
    
  }

  function custom_less(){

    printf('<script type="text/plain" id="pl-custom-less">%s</script>', pl_user_setting('custom_less') );

  }

  function save_styles( $response, $data ){

    if( isset( $data['styles'] ) ){

      $styles = $data['styles'];

      pl_user_setting_update( 'custom_css',     $styles['css'] );
      pl_user_setting_update( 'custom_less',    $styles['less'] );

    }

    return $response;

  }


}

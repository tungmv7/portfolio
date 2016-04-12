<?php
/*

  Plugin Name:   PageLines Section Maps
  Description:   Google maps with customizable markers and multiple locations support.

  Author:         PageLines
  Author URI:    http://www.pagelines.com
  Version:       5.0.5
  Demo:          yes

  PageLines:     PL_Maps



  Category:     framework, sections, free, featured

  Filter:       localsocial
  Tags:         maps, google, location

*/



if( ! class_exists('PL_Section') )
  return;

class PL_Maps extends PL_Section {

  function section_persistent(){

    add_filter('pl_binding_maps', array( $this, 'callback'), 10, 2);

  }

  /**
   * Load our scripts in the footer
   */
  function section_styles(){

    /** Maps JS Callback, must come before google maps src */
    pl_script( 'pl-maps',           $this->base_url . '/assets/maps.js'   );
    pl_script( 'pl-maps-resize',   $this->base_url . '/assets/resize.js', array( 'jquery', 'pl-maps' ) );
    pl_script( 'google-maps',     'https://maps.googleapis.com/maps/api/js?v=3.exp&callback=pl_initialize_maps', array( 'jquery', 'pl-maps' ), NULL, true );

  }

  function section_opts(){

    $help = 'To find map the coordinates use this simple tool: <a target="_blank" href="http://www.mapcoordinates.net/en">www.mapcoordinates.net</a>';

    $default = $this->default_location();

    $options = array();

    $options[] = array(
      'type'  => 'multi',
      'key'    => 'plmap_config',
      'title'  => __( 'Google Maps Configuration', 'pl-section-maps' ),
      'help'    => $help,
      'opts'  => array(

        array(
          'key'      => 'center_lat',
          'type'    => 'text_small',
          'default'  => $default['lat'],
          'default'    => $default['lat'],
          'label'    => __( 'Latitude', 'pl-section-maps' ),

        ),
        array(
          'key'      => 'center_lng',
          'type'    => 'text_small',
          'default'  => $default['lng'],
          'default'    => $default['lng'],
          'label'    => __( 'Longitude', 'pl-section-maps' ),

        ),
        array(
          'type'    => 'dragger',
          'label'    => __( 'Map Height', 'pl-section-maps' ),
          'opts'    => array(
            array(
              'key'      => 'map_height',
              'min'      => 5,
              'max'      => 100,
              'default'  => '35',
              'unit'    => 'vw',
            )
          ),
        ),
        array(
          'key'      => 'pointer_image',
          'label'   => __( 'Pointer Image', 'pl-section-maps' ),
          'type'    => 'image_upload',
          'help'    => __( 'For best results use an image size of 64px x 64px.', 'pl-section-maps' )
        ),
        array(
          'type'        => 'count_select',
          'key'          => 'map_zoom_level',
          'default'      => '12',
          'label'        => __( 'Default Map Zoom Level ( default 10)', 'pl-section-maps' ),
          'count_start'  => 1,
          'count_number'=> 18,
          'default'      => '10',
        ),
        array(
          'type'    => 'check',
          'key'      => 'map_zoom_enable',
          'label'    => __( 'Enable Zoom Controls', 'pl-section-maps' ),
          'default'  => 1
        ),
      )
    );

    $options[] = array(
      'key'        => 'locations_array',
      'type'      => 'accordion',
      'num_items'  => 1,
      'title'      => __('Pointer Locations', 'pl-section-maps'),
      'post_type'  => __('Location', 'pl-section-maps'),
      'help'      => $help,
      'opts'      => array(

        array(
          'key'        => 'latitude',
          'label'      => __( 'Latitude', 'pl-section-maps' ),
          'type'      => 'text_small',
          'default'    => '37.7749295',

        ),
        array(
          'key'      => 'longitude',
          'label'    => __( 'Longitude', 'pl-section-maps' ),
          'type'    => 'text_small',
          'default'    => '-122.4194155',

        ),
        array(
          'key'      => 'text',
          'label'    => 'Location Description',
          'type'    => 'textarea',
          'default'  => '',
          'place'    => ''
        ),

      )
    );
    return $options;
  }


  /** Standard callback format */
  function callback( $response, $data ){

    $response['template'] = $this->get_maps( $data['value'] );
    $response['debug'] = $data;
    return $response;
  }


  function get_map( $value = false ) {
    ob_start();

    ?>

    <div class="pl-map" id="pl_map_<?php echo $this->meta['clone'];?>" data-bind="style: {'height': map_height() ? map_height() + 'vw' : '350px'}"></div>
    <div class="pl-json-el map-data"  data-id="pl_map_<?php echo $this->meta['clone'];?>" data-bind="plmap: true, attr: { 'data-json': ko.toJSON($root) }" data-callback="maps" data-json="{}"></div>

    <?php
    return ob_get_clean();
  }


  function section_template( ) {

  ?><div class="pl-map-wrap"><?php echo $this->get_map(); ?> </div><?php

  }


  function default_location(){

    $default = array(
      'lat'     => floatval('37.774929'),
      'lng'      => floatval('-122.419416'),
      'desc'    => '<a href="http://www.pagelines.com">PageLines</a>',
      'mapinfo'  => '<a href="http://www.pagelines.com">PageLines</a>',
      'image'    => $this->base_url.'/assets/marker.png'
    );

    return $default;
  }

}

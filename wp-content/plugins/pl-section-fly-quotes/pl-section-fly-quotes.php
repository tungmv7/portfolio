<?php
/*

  Plugin Name:   PageLines Section Fly Quotes
  Description:   A simple revolving quote widget, great for testimonials or quotes.
  
  Author:        PageLines
  Author URI:    http://www.pagelines.com
  Version:       5.0.4

  Demo:          yes
  
  PageLines:     PL_Fly_Quotes
  
  Tags:          quotes, testimonials
  Category:      framework, free, sections, featured
  Filter:        component
*/

/** Required for PageLines Sections installed as plugins. */
if( ! class_exists('PL_Section') )
  return;

class PL_Fly_Quotes extends PL_Section {

  function section_styles(){
    pl_script( $this->id, $this->base_url . '/script.js' );
  }
  
  function section_opts(){
    
    $options = array();
    
    $options[] = array(
      'key'      => 'pl_fly-quote_config',
      'type'     => 'multi', 
      
      'title'    => __('Config', 'pl-section-fly-quotes'), 
      'opts'     => array(
        array(
          'key'      => 'quote_mode',
          'label'    => __( 'Mode', 'pl-section-fly-quotes' ),
          'type'     => 'select',
          'default'  => 'dots',
          'opts'     => array(
            'dots'     => array('name' => 'Dots'),
            'image'    => array('name' => 'Images'),
          ),
        ),
        array(
          'key'            => 'quote_speed',
          'label'          => __( 'Time per quote', 'pl-section-fly-quotes' ),
          'type'           => 'count_select', 
          'count_start'    => 0,
          'count_number'   => 20000,
          'count_mult'     => 1000,
          'default'        => 5000,
          'help'           => __( 'In milliseconds. Set to 0 for no auto transitions.', 'pl-section-fly-quotes' ),
        )
      )
    );
    
    $options[] = array(
      'key'      => 'item_array',
      'type'    => 'accordion', 
      'title'    => __('Quotes Setup', 'pl-section-fly-quotes'), 
      'opts'  => array(
        array(
          'key'      => 'text',
          'default'  => '"Quote."',
          'label'    => __( 'Text / Quote', 'pl-section-fly-quotes' ),
          'type'     => 'text'
        ),
        array(
          'key'      => 'cite',
          'default'  => 'John Smith',
          'label'    => __( 'Citation', 'pl-section-fly-quotes' ),
          'type'     => 'text'
        ),
        array(
          'key'      => 'thumb',
          'label'    => __( 'Image (Image Mode Only)', 'pl-section-fly-quotes' ),
          'type'     => 'image_upload',
          'default'  => pl_fallback_image(),
        )
      )
    );
    return $options;
  }

  function section_template() { ?>
    <div class="pl-fly-quotes-container pl-trigger" data-bind="plattr: {'data-mode': quote_mode, 'data-speed': quote_speed}">
      <div class="pl-fly-quotes pl-trigger" data-bind="plforeach: item_array">
        <div class="the-fly-quote pl-trigger" data-bind="plattr: { 'data-image': thumb }" >
          <div class="pl-quote">
            <p data-bind="pltext: text"></p>
          </div>
          <div class="pl-cite" data-bind="pltext: cite"></div>
        </div>
      </div>
      <div class="controls"><ul class="the-nav"></ul></div>
    </div>
  <?php
  }
}

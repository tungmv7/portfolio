<?php
/*
  Plugin Name: PageLines Section Massive
  
  Description: Create huge responsive text fields that dynamically size to fit their container.

  Author:      PageLines
  Version:     5.0.3

  PageLines:   PL_Section_Massive

  Demo:        https://www.pagelines.com/extensions/pl-section-massive/#demo
  
  Filter:      component

  Tags:        lettering, fonts, responsive, heading, typography, text

  Category:    framework, free, sections, featured
  

*/


if( ! class_exists('PL_Section') ){
  return;
}

class PL_Section_Massive extends PL_Section {

  function section_styles(){

    // http://pixelcog.github.io/parallax.js/

    pl_script( 'slabtext', $this->base_url . '/slabtext.js' );

    pl_script( $this->id, $this->base_url . '/massive.js' );

  }

  function section_opts(){

    $options = array();

    

    $options[] = array(
      'key'        => 'item_array',
      'type'        => 'accordion', 
      'num_items' => 1,
      'opts'      => array(
        array(
          'key'     => 'text',
          'type'    => 'text', 
          'default' => 'Massive', 
          'label'   => 'Row Text'
        ), 
        array(
          'key'     => 'weight', 
          'type'    => 'select', 
          'default' => '800',
          'opts'    => array(
              '300'   => array('name' => 'Light'), 
              '500'   => array('name' => 'Normal'),
              '800'   => array('name' => 'Bold')
            )
        ), 
        array(
          'key'     => 'family', 
          'type'    => 'text', 
          'label'   => 'Font Family (CSS Stack)'
        ), 
        array(
          'key'     => 'lineheight', 
          'type'    => 'text', 
          'label'   => 'Line Height', 
          'help'    => 'Enter a full line height value. For example, 1.2em, .8, or 30px.'
        )
        
       
      )
    );

    return $options;

  }
  
  function section_template(){

    ?>
    <div class="massive-text-wrap pl-trigger" data-bind="foreach: item_array()">
      <div class="massive-text pl-trigger" data-bind="pltext: text, plstyle: { 'font-weight': weight, 'font-family': family, 'line-height': lineheight}"></p>

    </div>
    <?php 

  }

}

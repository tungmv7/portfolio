<?php
/*
  
  Plugin Name: PageLines Section Starter

  Description: A short description.

  Author:      PageLines

  Version:     1.0.1

  PageLines:   PL_Section_Starter

  Tags:         starter
  
  Category:     framework, sections

  Filter:       component

*/


/** A guard to prevent the section from being loaded if platform isn't installed or at the wrong time */
if( ! class_exists('PL_Section') ){
  return;
}

class PL_Section_Starter extends PL_Section {

  /** 
   * This function will load on all site page loads, both front and back end.
   * Use it for hooks, global settings, etc... 
   */
  function section_persistent(){

  }

  /** 
   * Include extra scripts and styles here
   * Use the pl_script and pl_style functions (which enqueues the files)
   */
  function section_styles(){

    /** Include the sample script */
    pl_script( $this->id, $this->base_url . '/starter.js' );

  }

  /**
   * Return the option array for the section.
   */
  function section_opts(){

    $options = array(
      'key'      => 'alert_text_key',
      'type'     => 'text',
      'default'  => __('Starter Section Output', 'pagelines'),
      'title'    => __( 'Text', 'pagelines' )
    ); 

    return $options;
  }
  
  /**
   * The section HTML output
   */
  function section_template(){ ?>

    <!-- (pl-render-item) The render item class is used when you want to create a JS item like a slider, carousel, etc.. (it is picked up and duplicated for rendering) -->
    <!-- (pl-trigger)     The trigger class will cause the template_ready event to file on the section whenever the bindings to this element are changed. -->
    <div class="pl-render-item pl-trigger"></div>

    <!-- This text will be synced to the value of the option from the option array with key alert_text_key -->
    <div class="pl-alert pl-alert-default" data-bind="pltext: alert_text_key"></div>

    <?php 
  }

}

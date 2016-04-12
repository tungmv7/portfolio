<?php
/*
  Plugin Name: PageLines Section Lax
  Description: A simple parallax section that supports nested sections.

  Author:      PageLines
  Version:     5.0.4
  
  Demo:        https://www.pagelines.com/extensions/pl-section-lax/#demo

  PageLines:   PL_Section_Lax

  Filter:      component

  Tags:         parallax, animation
  Category:     framework, free, sections, featured
  
  Contain:      yes

*/


if( ! class_exists('PL_Section') ){
  return;
}

class PL_Section_Lax extends PL_Section {

  function section_styles(){

    // http://pixelcog.github.io/parallax.js/

    pl_script( 'simple-parallax', $this->base_url . '/parallax.min.js' );

    pl_script( $this->id, $this->base_url . '/lax.js' );

  }

  function section_opts(){

    $options = array();

    $options[] = array(

      'title'  => 'Lax Basics',
      'type'  => 'multi',
      'opts'  => array(
        pl_std_opt('image', array( 
            'label'     => __('Lax Image', 'pl-section-lax'),
            'key'       => 'media_image', 
            'default'   => $this->base_url . '/cover.png', 
            'help'      => 'Note that if the image is too large it may cause lag on scroll.'
          )
        ),
        array(
          'type'    => 'dragger',
          'label'   => __( 'Section Height', 'pl-section-lax' ),
          'opts'  => array(
            array(
              'key'     => 'laxheight',
              'min'     => 200,
              'max'     => 1000,
              'def'     => 400,
              'unit'    => 'Height/PX', 
              'scale'   => 5
            ),
          ),
        ),

      )
    ); 

    $options[] = array(

      'title'  => 'Lax Content',
      'guide' => __('Optional. Note that Lax supports nested sections via the builder.', 'pl-section-lax'),
      'type'  => 'multi',
      'opts'  => array(
        array(
          'key'        => 'header',
          'type'       => 'text',
          'label'   => __( 'Header', 'pl-section-lax' ),
          'default'  => __( 'PageLines', 'pl-section-lax' )
        ),
        array(
          'key'        => 'subheader',
          'type'       => 'text',
          'label'   => __( 'Sub Header', 'pl-section-lax' ),
          'default'  => __( 'Let\'s build something beautiful together.', 'pl-section-lax' )
        ),
        array(
          'title'      => __( 'Action Button', 'pl-section-lax' ),
          'type'        => 'multi',
          'stylize'    => 'button-config',
          'opts'      => pl_button_link_options( 'button_primary', array(
            'button_primary'        => '', 
            'button_primary_size'   => 'lg'
          ) )
        ),

      )
    ); 

    return $options;

  }
  
  function section_template(){

    ?>

    <div class="pl-lax-window lax-window pl-trigger"  data-bind="plstyle: {'min-height': (laxheight()) ? laxheight() + 'px' : '' }, plattr: {'data-image': media_image() }">
      <div class="lax-window-pad">
        <div class="lax-mast" data-bind="visible: header() || subheader() || button_primary()">
          <h2 data-bind="pltext: header"></h2>
          <div class="subhead" data-bind="pltext: subheader"></div>
          <a class="pl-btn" href="#" data-bind="plbtn: 'button_primary'"></a>
        </div>
        <?php pl_nested_container( $this );?>
      </div>
    </div>

    <?php 

  }

}

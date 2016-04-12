<?php
/*

  Plugin Name:   PageLines Section Elements
  Description:   Add simple elements to your page. Text, nav, button, media, etc.. 

  Author:       PageLines
  Author URI:   http://www.pagelines.com

  Version:      5.0.11
  
  PageLines:     PL_Elements
  Filter:       component

  Tags:         formats, component, hero, masthead

  Category:     framework, sections, featured, free

*/

/** Required for PageLines Sections installed as plugins. */
if( ! class_exists('PL_Section') )
  return;


class PL_Elements extends PL_Section {


  function section_opts(){
    $opts = array(
      array(
        'type'       => 'select',
        'key'      => 'format',
        'label'     => __( 'Element Format', 'pl-section-elements' ),
        'opts'=> array(
          'masthead'      => array( 'name' => __( 'Masthead', 'pl-section-elements' ) ),
          'masthead-flip'  => array( 'name' => __( 'Masthead Reverse', 'pl-section-elements' ) ),
          'hero'           => array( 'name' => __( 'Hero' , 'pl-section-elements' )),
          'hero-flip'      => array( 'name' => __( 'Hero Reverse' , 'pl-section-elements' )),
          'callout'         => array( 'name' => __( 'Callout', 'pl-section-elements' ) ),
          'callout-flip'   => array( 'name' => __( 'Callout Reverse', 'pl-section-elements' ) ), 
          
        ),
      ),
      array(
        'type'       => 'multi',
        
        'opts'  => array(
          array(
            'key'      => 'header',
            'default'    => 'Hello.',
            'type'       => 'text',
            'label'     => __( 'Header', 'pl-section-elements' ),
          ),
          array(
            'key'      => 'subheader',
            'type'       => 'richtext',
            'label'     => __( 'Subheader', 'pl-section-elements' ),
          ), 
          array(
            'type'       => 'image_upload',
            'key'      => 'media',
            'label'     => __( 'Image', 'pl-section-elements' )
          ),
          array(
            'key'            => 'image_max_width',
            'label'          => __( 'Image Max Percent Width', 'pl-section-elements' ),
            'type'          => 'select_percent', 
            'count_number'  => 300
          ),
          array(
            'key'            => 'image_margin',
            'label'          => __( 'Image Left Margin', 'pl-section-elements' ),
            'type'          => 'select_percent', 
            'count_mult'    => 5,
            'count_start'     => -100, 
            'count_number'  => 100
          ),

        )
      ),
      array(
        'type'       => 'multi',
        'title'     => __( 'Buttons', 'pl-section-elements' ),
        'opts'  => array(
          array(
            'title'      => __( 'Primary Button', 'pl-section-elements' ),
            'type'      => 'multi',
            'stylize'    => 'button-config',
            'opts'      => pl_button_link_options('button_primary')
          ),
          array(
            'title'      => __( 'Secondary Button', 'pl-section-elements' ),
            'type'      => 'multi',
            'stylize'    => 'button-config',
            'opts'      => pl_button_link_options('button_secondary')
          ),
        ),
      ),
      array(
        'type'     => 'multi',
        'title'    => __( 'Navigation', 'pl-section-elements' ),
        'opts'    => array(
          array(
              'key'      => 'nav' ,
              'type'       => 'select_menu',
              'label'   => __( 'Select Menu', 'pl-section-elements' ),
            ),
        ),
      ),
      array(
        'type'       => 'multi',
        'title'     => __( 'Other Content', 'pl-section-elements' ),
        'opts'  => array(
          
          array(
            'key'      => 'text',
            'type'       => 'richtext',
            'label'     => __( 'Paragraph Text', 'pl-section-elements' ),
          ),
          array(
            'type'       => 'html',
            'key'      => 'media_html',
            'label'     => __( 'Media Embed HTML', 'pl-section-elements' ),
            'help'      => __( 'Enter rich media "embed" HTML in this field to add videos, etc.. instead of an image.', 'pl-section-elements' )
          ),
          
        )
      ),

      

      

    );

    return $opts;

  }

  function nav_config(){
    $config = array(
        'key'        => 'nav', 
        'menu'       => $this->opt('nav'), 
        'mode'      => 'simple', 
        'default'    => false
      ); 

    return $config; 
  }

  function media_config(){
    $config = array(
        'key'      => 'media', 
        'src'       => $this->opt('media'), 
        'html'       => $this->opt('media_html'),
        'classes'    => array('elements-media'),
        'default'    => false
      ); 

    return $config; 
  }

  function pl_dynamic_media( $config = array()  ){

      $defaults = array(
          'key'       => '',
          'alt'       => '',
          'classes'   => array(),
          'bind'      => '',
          'src'       => '',
          'html'      => '',
          'default'   => pl_fallback_image()
      );



      $config = wp_parse_args( $config, $defaults );

      $htmlkey = $config['key'] . '_html';

      $classes = ( ! empty( $config['classes'] ) ) ? join( ' ', $config['classes'] ) : false;

      ?>
      <div class="media-wrap <?php echo $classes;?>" data-bind="visible: <?php echo $config['key'];?>() || <?php echo $htmlkey;?>()">

          <div class="img-wrap"><img src="<?php echo $config['src'];?>" alt="<?php echo $config['alt'];?>" data-bind="plimg: <?php echo $config['key'];?>, style: {'max-width': image_max_width() || '', 'margin-left': image_margin() || ''}" /></div>

          <div class="media-html" data-bind="visible: <?php echo $htmlkey;?>, plshortcode: <?php echo $htmlkey;?>"><?php echo do_shortcode( $config['html'] ); ?></div>

      </div>
      <?php
  }



  function section_template(){

    

    ?>
  

    <div class="elements-wrap pl-content-area" data-bind="class: 'format-' + format()">
      <div class="elements-pad">
        
        <div class="elements-hero">

          <?php echo $this->pl_dynamic_media( $this->media_config() ); ?>

          <div class="elements-head">
          
            <div class="elements-text">

              <h1 class="elements-header" data-bind="plshortcode: header"><?php echo $this->opt('header'); ?></h1>

              <h3 class="elements-subheader" data-bind="plshortcode: subheader"><?php echo $this->opt('subheader'); ?></h3>           
              
            </div>

            <div class="elements-nav">
              <div class="elements-btns" data-bind="visible: button_primary || button_secondary">
                <a class="pl-btn" href="#" data-bind="visible: button_primary, plbtn: 'button_primary', plattr: {'target': ( button_primary_newwindow() == 1 ) ? '_blank' : ''}" ></a>
                <a class="pl-btn" href="#" data-bind="visible: button_secondary, plbtn: 'button_secondary', plattr: {'target': ( button_secondary_newwindow() == 1 ) ? '_blank' : ''}" ></a>
              
              </div>

              
            </div>
          </div>
        </div>
        <div class="elements-menu" data-bind="visible: nav">
          <?php echo pl_dynamic_nav( $this->nav_config() ); ?>
        </div>
        <div class="elements-richtext" data-bind="plshortcode: text">
          <?php echo $this->opt('text'); ?>
        </div>

      </div>
    </div>
      

    <?php
  }

}

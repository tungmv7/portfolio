<?php
/*

  Plugin Name:   PageLines Section Heroes
  Description:   A ridiculously clean and simple way to create nice, big, hero splash elements and stack them.

  Author:       PageLines
  Author URI:   http://www.pagelines.com
  Demo:         yes

  Version:      5.0.10
  
  PageLines:    PL_Heroes
  Filter:       component

  Category:     framework, sections, free, featured

  Tags:         hero, items
  

*/

if( ! class_exists( 'PL_Section' ) )
  return;

class PL_Heroes extends PL_Section {

  function section_opts(){

    $options = array();


    $options[] = array(
      'key'        => 'item_array',
      'type'      => 'accordion', 
      'title'      => __('Hero Items Setup', 'pl-section-heroes'), 
      'post_type'  => __('Box', 'pl-section-heroes'), 
      'opts'      => array(
        pl_std_opt('title'),
        pl_std_opt('text'),
        pl_std_opt('text_alignment'),
        pl_std_opt('image'),
        pl_std_opt('section_alignment'),
        array(
          'key'            => 'image_max_width',
          'label'          => __( 'Image Percent Width', 'pl-section-heroes' ),
          'type'          => 'select_percent', 
          'count_number'  => 300
        ),
        
        
        pl_std_opt('button'),
        array(
          'type'     => 'multi',
          'toggle'  => 'closed', 
          'title'    => 'Background Options',
          'opts'  => array(
              pl_std_opt('scheme'),
              pl_std_opt('background_color'),
              pl_std_opt('background_image'),
            )
        )
        
        
      )
      );

    return $options;
  }


  function section_template(){ ?>

    <div class="pl-heroes-container" data-bind="plforeach: item_array">  
      <div class="pl-hero pl-bg-cover" data-bind="plbg: background_image, style: {'background-color': background_color}">
        <div class="pl-content-area">
          <div class="pl-content-pad fix" data-bind="plclassname: section_alignment">
            
            <div class="hero-span pl-hero-content-wrap" data-bind="plclassname: [text_alignment(), scheme()]">
              <div class="pl-hero-content">
                <h2 data-bind="pltext: title"></h2>
                <p class="pl-hero-excerpt subhead" data-bind="pltext: text"></p>
                <p><a class="pl-btn" href="#" data-bind="plbtn: 'button',plattr: {'target': ( button_newwindow() == 1 ) ? '_blank' : ''}" ></a></p>
              </div>
            </div>

            <div class="hero-span pl-hero-media">
              <div class="center-media">
                <div class="img-wrap"><img src="" data-bind="plimg: image, style: {'max-width': image_max_width() || '100%'}" /></div>
              </div>
            </div>

          </div>
        </div>

      </div>
      
    </div>
  
<?php 
  }



  



}

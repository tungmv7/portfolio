<?php
/*

  Plugin Name:   PageLines Section Boxes
  Description:   Display information easily with Boxes; quickly create columned boxes showcasing icons, images, or numbers for your visitors.

  Author:       PageLines
  Author URI:   http://www.pagelines.com

  Docs:         http://www.pagelines.com/resources/boxes/
  Demo:         yes

  Version:      5.0.14
  
  PageLines:    PL_Boxes
  Filter:       component

  Category:     framework, sections, free, featured

  Tags:         boxes, items

*/

if( ! class_exists('PL_Section') )
  return;

class PL_Boxes extends PL_Section {

  function section_styles(){

    /** http://kenwheeler.github.io/slick/ */
    pl_script( 'countto',     $this->base_url . '/countto.js');

    pl_script( $this->id,     $this->base_url.'/boxes.js');

  }

  function section_opts(){

    $options = array();    

    $options[] = array(
      'key'    => 'ibox_array',
      'type'    => 'accordion', 
      
      'title'    => __('Boxes Setup', 'pl-section-boxes'), 
      'post_type'  => __('Box', 'pl-section-boxes'), 
      'opts'  => array(
        array(
          'key'      => 'title',
          'label'    => __( 'Title', 'pl-section-boxes' ),
          'type'    => 'text', 
          'default'  => 'Hello'
        ),
        array(
          'key'      => 'text',
          'label'    => __( 'Text', 'pl-section-boxes' ),
          'type'    => 'richtext', 
          'default'  => 'This is a box.'
        ),
        array(
          'key'      => 'link',
          'label'    => __( 'Link (Optional)', 'pl-section-boxes' ),
          'type'    => 'text'
        ),
        array(
          'key'      => 'icon',
          'label'    => __( 'Icon (Icon Mode)', 'pl-section-boxes' ),
          'default'  => 'check',
          'type'    => 'select_icon',
          'help'    => '<a target="_blank" href="http://fortawesome.github.io/Font-Awesome/icons/">Click here</a> for a complete list of Font Awesome Icons'
        ),
        array(
          'key'    => 'count',
          'default'  => '1000',
          'label'    => __( 'Count Number (Counter Mode)', 'pl-section-boxes' ),
          'type'    => 'text_small'
        ),
        array(
          'key'       => 'image',
          'default'   => $this->base_url . '/check.svg',
          'label'     => __( 'Box Image (Image Mode)', 'pl-section-boxes' ),
          'type'      => 'image_upload'
        ),
        array(
          'key'    => 'color',
          'label'    => __( 'Icon/Count Color', 'pl-section-boxes' ),
          'type'    => 'color'
        ),
        array(
          'key'      => 'boxclass',
          'label'    => __( 'Class (Optional)', 'pl-section-boxes' ),
          'type'    => 'text',
          'help'    => __( 'Adds a class to this box', 'pl-section-boxes' )
        ),

      )
    );



    $options[] = array(

      'title' => 'Section Configuration',
      'type'  => 'multi',
      'opts'  => array(
        array(
          'key'      => 'ibox_cols',
          'type'       => 'select',
          'opts'    => array(
            '2'       => array( 'name' => __( '2 of 12 Columns', 'pl-section-boxes' ) ),
            '3'       => array( 'name' => __( '3 of 12 Columns', 'pl-section-boxes' ) ),
            '4'       => array( 'name' => __( '4 of 12 Columns', 'pl-section-boxes' ) ),
            '6'       => array( 'name' => __( '6 of 12 Columns', 'pl-section-boxes' ) ),
            '12'      => array( 'name' => __( '12 of 12 Columns', 'pl-section-boxes' ) )
          ),
          'count_start'   => 2,
          'count_number'  => 6,
          'default'       => 4,
          'label'         => __( 'Columns Per Box (12 Col Grid)', 'pl-section-boxes' ),
        ),
        array(
          'key'      => 'ibox_cols_mobile',
          'type'       => 'select',
          'opts'    => array(
            '2'       => array( 'name' => __( '2 of 12 Columns', 'pl-section-boxes' ) ),
            '3'       => array( 'name' => __( '3 of 12 Columns', 'pl-section-boxes' ) ),
            '4'       => array( 'name' => __( '4 of 12 Columns', 'pl-section-boxes' ) ),
            '6'       => array( 'name' => __( '6 of 12 Columns', 'pl-section-boxes' ) ),
            '12'      => array( 'name' => __( '12 of 12 Columns', 'pl-section-boxes' ) )
          ),
          'count_start'   => 2,
          'count_number'  => 6,
          'default'       => 6,
          'label'         => __( 'Columns On Mobile (12 Col Grid)', 'pl-section-boxes' ),
        ),
        array(
          'key'      => 'ibox_cols_desktop',
          'type'       => 'select',
          'opts'    => array(
            '2'       => array( 'name' => __( '2 of 12 Columns', 'pl-section-boxes' ) ),
            '3'       => array( 'name' => __( '3 of 12 Columns', 'pl-section-boxes' ) ),
            '4'       => array( 'name' => __( '4 of 12 Columns', 'pl-section-boxes' ) ),
            '6'       => array( 'name' => __( '6 of 12 Columns', 'pl-section-boxes' ) ),
            '12'      => array( 'name' => __( '12 of 12 Columns', 'pl-section-boxes' ) )
          ),
          'count_start'   => 2,
          'count_number'  => 6,
          'default'       => 3,
          'label'         => __( 'Columns On Desktop (12 Col Grid)', 'pl-section-boxes' ),
        ),
        array(
          'key'      => 'ibox_media',
          'type'       => 'select',
          'opts'    => array(
            'icon'      => array( 'name' => __( 'Icon Font', 'pl-section-boxes' ) ),
            'count'     => array( 'name' => __( 'Counter', 'pl-section-boxes' ) ),
            'image'     => array( 'name' => __( 'Images / SVG', 'pl-section-boxes' ) ),
            'text'      => array( 'name' => __( 'Text Only, No Media', 'pl-section-boxes' ) )
          ),
          'default'    => 'image',
          'label'   => __( 'Select iBox Media Type', 'pl-section-boxes' ),
        ),
        array(
          'key'     => 'ibox_format',
          'type'    => 'select',
          'opts'    => array(
            'top'     => array( 'name' => __( 'Media on Top', 'pl-section-boxes' ) ),
            'left'    => array( 'name' => __( 'Media at Left', 'pl-section-boxes' ) ),
          ),
          'default'    => 'top',
          'label'   => __( 'Select the iBox Media Location', 'pl-section-boxes' ),
        ),
        array(
          'key'     => 'box_arrange',
          'type'    => 'select',
          'opts'    => array(
            'std'      => array( 'name' => __( 'Full Width', 'pl-section-boxes' ) ),
            'cntr'     => array( 'name' => __( 'Center Content', 'pl-section-boxes' ) ),
          ),
          'default'    => 'top',
          'label'   => __( 'Container Handling', 'pl-section-boxes' ),
        ),

        
        array(
          'key'        => 'header',
          'type'       => 'text',
          'label'   => __( 'Title', 'pl-section-boxes' ),
        ),
      )

    );

    $options[] = array(

      'title' => 'Image Mode',
      'type'  => 'multi',
      'opts'  => array(
        array(
          'key'     => 'image_format',
          'type'    => 'select',
          'opts'    => array(
            'std'     => array( 'name' => __( 'Standard / Default', 'pl-section-boxes' ) ),
            'round'    => array( 'name' => __( 'Rounded', 'pl-section-boxes' ) ),
          ),
          'default'    => 'top',
          'label'   => __( 'Image Appearance', 'pl-section-boxes' ),
        ),

        array(
          'key'     => 'image_width',
          'type'    => 'count_select',
          'label'   => __( 'Image/Icon Width (PX)', 'pl-section-boxes' ),
          'count_start'    => 30,
          'count_number'   => 400,
          'count_mult'     => 10,
          'default'        => 50
          
        ),
        array(
          'key'     => 'image_opacity',
          'type'    => 'select_proportion',
          'label'   => __( 'Image Opacity', 'pl-section-boxes' ),
          'default' => .5
        ),
       
      )

    );

    return $options;
  }

  function section_template(){
?>  
  
    <script type="text/html" id="boxes-template">

      <a class="boxes" data-bind="plclassname:[boxclass(), 'pl-col-lg-' + $root.ibox_cols_desktop(), 'pl-col-sm-' + $root.ibox_cols(), 'pl-col-xs-' + $root.ibox_cols_mobile()], plhref: link" >

        <div class="boxes-pad" data-bind="class: 'pl-control-'+ $root.ibox_format()">
          

          <div class="boxes-media media-left">
            
            <span class="the-boxes-media" data-bind="class: 'media-'+$root.ibox_media(), style: { color: color, width:  'image' == $root.ibox_media() ? $root.image_width() + 'px' : '' , opacity: $root.image_opacity}">

              <div class="pl-image" data-bind="plbg: image, class: $root.image_format"></div>

              <span class="pl-counter" data-bind="pltext: count"></span>

              <i class="boxes-icon pl-icon" data-bind="class: 'pl-icon-'+icon(), style: { color: color, 'font-size': $root.image_width() + 'px'}"></i>

            </span>
            
          </div>
          <div class="boxes-text media-right">
            <h3 class="boxes-title" data-bind="pltext: title"></h3>
            <div class="boxes-desc" data-bind="pltext: text"></div>
            
          </div>

        </div>
      </a>
      
    </script>
    
    <div class="pl-content-area">
      <h2 class="box-title" data-bind="visible: header, pltext: header" > </h2>
      <div class="the-boxes-container" data-bind="class: box_arrange">
        <div class="pl-row half-space" data-bind="pltemplate: {name: 'boxes-template', foreach:ibox_array()}" > </div>
      </div>
    </div>
  
<?php 
  }



  



}

<?php
/*

  Plugin Name:   PageLines Section Image
  Description:   Simple image section

  Author:       PageLines
  Author URI:   http://www.pagelines.com

  PageLines:     PL_Image_Section
  Filter:       basic

*/
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Image_Section extends PL_Section {

  function section_opts() {
    $opts = array(
      array(
        'type'      => 'image_upload',
        'key'       => 'image',
        'label'     => __( 'Image', 'pl-platform' ),
        'default'   => pl_fallback_image(),
      ),
      array(
        'type'      => 'text',
        'key'       => 'alt',
        'label'     => __( 'Alt Text', 'pl-platform' ),
      ),
      array(
        'type'      => 'dragger',
        'label'     => __( 'Image Width / Height', 'pl-platform' ),
        'opts'      => array(
          array(
            'key'     => 'height',
            'min'     => 0,
            'max'     => 100,
            'default' => 20,
            'unit'    => __( 'Height (vw)', 'pl-platform' ),
          ),
          array(
            'key'     => 'width',
            'min'     => 0,
            'max'     => 100,
            'unit'    => __( 'Width (vw)', 'pl-platform' ),
          ),
        ),
      ),
      array(
        'type'      => 'text',
        'key'       => 'link',
        'label'     => __( 'Link URL', 'pl-platform' ),
      ),
      array(
        'type'      => 'check',
        'key'       => 'newwindow',
        'label'     => __( 'Open in new window?', 'pl-platform' ),
      ),
    );

    return $opts;
  }

  function section_template() {
    $image = $this->opt( 'image', pl_fallback_image() );
    $class = ( ! $this->opt( 'link' ) ) ? 'pl-img' : '';
    ?>
  <div class="pl-img-wrap pl-alignment-default-center">
    <a class="<?php echo $class; ?>" data-bind="plhref: link, plattr: {'target': ( newwindow() == 1 ) ? '_blank' : ''}">
      <img src="<?php echo $image; ?>" alt="" data-bind="plimg: image, attr: {alt: alt, title: alt}, style: {'height': height() ? height() + 'vw' : '', 'width': width() ? width() + 'vw' : ''}" />
    </a>
  </div>
<?php
  }
}

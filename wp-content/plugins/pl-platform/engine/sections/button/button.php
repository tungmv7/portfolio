<?php
/*

  Plugin Name:   PageLines Section Button
  Description:   Simple button section.

  Author:       PageLines
  Author URI:   http://www.pagelines.com

  PageLines:    PL_Button_Section
  Filter:       basic

*/
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Button_Section extends PL_Section {

  function section_opts() {
    $opts = array(
      array(
        'title'     => __( 'Button Setup', 'pl-platform' ),
        'type'      => 'multi',
        'stylize'   => 'button-config',
        'opts'      => pl_button_link_options( 'button', array( 'button_text' => __( 'Click me', 'pl-platform' ), 'button' => '#' ) ),
      ),
    );
    return $opts;
  }

  function section_template() {
  ?>

  <div class="pl-btn-wrap pl-alignment-default-center"><a class="pl-btn" href="#" data-bind="plbtn: 'button', plattr: {'target': ( button_newwindow() == 1 ) ? '_blank' : ''}" ></a></div>

<?php
  }
}

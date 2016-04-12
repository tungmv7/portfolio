<?php
/*

  Plugin Name:   PageLines Section Text
  Description:   Simple text/html section.

  Author:       PageLines
  Author URI:   http://www.pagelines.com

  PageLines:     PL_Text_Section
  Filter:       basic

*/
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Text_Section extends PL_Section {

  function section_opts() {
    $opts = array(
        pl_std_opt( 'text' )
      );

    return $opts;
  }

  function section_template() {
  ?>
  <div class="pl-text-wrap">
    <div class="pl-text" data-bind="plshortcode: text"><?php echo do_shortcode( $this->opt( 'text' ) ); ?></div>
  </div>
<?php
  }
}

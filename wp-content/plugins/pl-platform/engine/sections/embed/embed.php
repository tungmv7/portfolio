<?php
/*

  Plugin Name:   PageLines Section Embed
  Description:   Simple text/html section.

  Author:       PageLines
  Author URI:   http://www.pagelines.com

  PageLines:     PL_Embed_Section
  Filter:       basic

*/
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Embed_Section extends PL_Section {

  function section_opts() {
    $opts = array(
        pl_std_opt( 'html' )
      );

    return $opts;
  }

  function section_template() {
  ?>
  <div class="pl-text-wrap">
    <div class="pl-text" data-bind="plshortcode: html"><?php echo do_shortcode( $this->opt( 'html' ) ); ?></div>
  </div>
<?php
  }
}

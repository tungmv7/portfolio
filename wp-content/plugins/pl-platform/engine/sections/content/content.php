<?php
/*

  Plugin Name:  PageLines Section Content
  Description:  The Main Content area (Post Loop in WP speak). Includes content and post information.

  Author:       PageLines
  Author URI:   http://www.pagelines.com

  PageLines:    PL_Content
  Filter:       basic

  Loading:      refresh

*/
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Content extends PL_Section {

  function section_template() {

    if ( pl_is_static_template() ) {

      global $pl_static_template_output;

      $binding = "plclassname: [ (tplwrap() == 'wrapped') ? 'pl-content-area pl-content-layout' : '' ]";

      printf( '<div class="pl-page-content" data-bind="%s">%s</div>', $binding, $pl_static_template_output );

    }

  }
}

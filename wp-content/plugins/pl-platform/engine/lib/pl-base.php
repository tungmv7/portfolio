<?php
/**
 * Basic page handler
 *
 * @version   5.0.0
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
pl_get_header();

pl_hook( 'pl_start_template' );

include pl_template_path();

pl_hook( 'pl_after_template' );


pl_get_footer();

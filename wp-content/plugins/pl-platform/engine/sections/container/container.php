<?php
/*

  Plugin Name:   PageLines Section Container
  Description:   Multi level section container.

  Author:       PageLines
  Author URI:   http://www.pagelines.com

  PageLines:     PL_Container
  Filter:       basic, system

  Contain:      yes

*/
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Container extends PL_Section {

  function section_template() {
  ?>
<div class="pl-container-wrap" >
  <div class="pl-content-area">
    <div class="pl-row nested-section-content" data-bind="stopBinding: true" data-contains-level="<?php echo $this->level + 1;?>" >
      <?php echo pl_render_nested_sections( $this ); ?>
    </div>
  </div>
</div>
<?php
  }
}

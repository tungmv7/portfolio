<?php
/*

  Plugin Name:  PageLines Section MegaNav

  Description:  An advanced site navigation that supports drop-downs, full content width nav panels, sticky nav and more.

  Author:       PageLines
  Author URI:   http://www.pagelines.com
  Demo:         yes

  Version:      5.0.12
  
  PageLines:    PL_MegaNav

  Filter:       nav

  Category:     framework, sections, free, featured

  Tags:         navigation, sticky
  
*/

if( ! class_exists( 'PL_Section' ) )
  return;

class PL_MegaNav extends PL_Section {

  function section_persistent(){

    // add menu settings
    // 
    add_action( 'wp_footer', array( $this, 'mobile_menu_template' ) );
    register_nav_menus( array( 'pl-meganav' => $this->name . __( ' Section', 'pl-section-meganav' ) ) );
  }

  function section_styles(){

    pl_script( 'superfish',   $this->base_url . '/superfish.js' );
    pl_script( 'sticky',      $this->base_url . '/sticky.js' );
    pl_script( $this->id,     $this->base_url . '/meganav.js' );

  }


  function section_opts(){

    $opts = array(
      array(
        'type'  => 'multi',
        'key'    => 'navi_content',
        'title'  => __( 'Logo', 'pl-section-meganav' ),
        'opts'  => array(
          array(
            'type'    => 'image_upload',
            'key'      => 'logo',
            'label'    => __( 'Logo', 'pl-section-meganav' ),
            'has_alt'  => true
          ),
          array(
            'type'    => 'dragger',
            'label'    => __( 'Logo Size / Height', 'pl-section-meganav' ),
            'opts'  => array(
              array(
                'key'      => 'logo_height',
                'min'      => 0,
                'max'      => 30,
                'default'  => 0,
                'unit'    => 'vw'
              ),
              array(
                'key'      => 'logo_height_min',
                'min'      => 0,
                'max'      => 300,
                'unit'    => 'Min (px)'
              )
            ),
          ),
          array(
            'key'    => 'hide_logo',
            'type'    => 'check',
            'label'  => __( 'Hide logo?', 'pl-section-meganav' ),
          ),
          
        )

      ),
      array(
        'type'  => 'multi',
        'key'  => 'navi_nav',
        'title'  => 'Navigation',
        'col'  => 2,
        'opts'  => array(

          array(
            'key'    => 'menu',
            'type'  => 'select_menu',
            'label'  => __( 'Select Menu', 'pl-section-meganav' ),
          ),
          array(
            'key'    => 'search',
            'type'  => 'check',
            'label'  => __( 'Show Search?', 'pl-section-meganav' ),
          ),
          array(
            'key'    => 'sticky',
            'type'  => 'check',
            'label'  => __( 'Make sticky on scroll?', 'pl-section-meganav' ),
            'help'  => __( 'The navigation will stick to the top on scroll. Note: since this breaks the page flow, "stickyness" can act strangely in many design situations.', 'pl-section-meganav' )
          ),
          
          array(
            'key'    => 'megamenus',
            'type'  => 'text',
            'place'  => '1, 3',
            'label'  => __( 'Megamenus (multi column drop down)', 'pl-section-meganav' ),
            'help'  => __( 'Identify the index number of the navigation items you want to make megamenus. For example, first on left is 1, second, from left is 2, etc... Add them in a comma separated list here.', 'pl-section-meganav' )
          ),
          array(
            'key'    => 'dropleft',
            'type'  => 'text',
            'place'  => '3, 4',
            'label'  => __( 'Drop Down Left', 'pl-section-meganav' ),
            'help'  => __( 'For items towards the right of the screen you may want your drop downs to the left. Enter the index of the items you would like to drop left here in a comma separated list.', 'pl-section-meganav' )
          ),
        )
      )
    );

    return $opts;

  }

  function nav_config(){
    $config = array(
      'key'             => 'menu',
      'menu'            => $this->opt('menu'),
      'menu_class'      => 'meganav-menu inline-list pl-nav sf-menu',
      'wrap_class'      => 'nav-respond meganav-nav',
      'mode'           => 'superfish',
      'walker'         => new PL_Walker_Nav_Menu, 
      'depth'           => 4,
      'theme_location' => 'pl-meganav'
    );

    return $config;
  }


  /**
  * Section template.
  */
   function section_template( $location = false ) {

  ?>
  <div class="meganav-wrap pl-trigger fix" data-bind="plclassname: [sticky() == 1 ? 'do-sticky' : '']">
    <div class="meganav-content pl-content-area" >

      <div class="meganav-branding" data-bind="visible: hide_logo() != 1">
        <a class="meganav-logo site-logo" href="<?php echo home_url('/');?>" >
          <img itemprop="logo" src="" alt="<?php echo get_bloginfo('name');?>" data-bind="visible: logo(), plimg: logo, style: {'height': logo_height() ? logo_height() + 'vw' : '50px', 'min-height': logo_height_min() ? logo_height_min() + 'px' : '30px'}" />
          <span class="site-name meganav-name" data-bind="visible: ! logo()">
            <?php echo get_bloginfo('name');?>
          </span>
        </a>
      </div>
      <div class="meganav-nav-wrap pl-trigger mmenu-show" data-bind="css: { 'no-nav': ! menu() }, plattr: {'data-megas': megamenus, 'data-dropleft': dropleft}">
        <?php echo pl_dynamic_nav( $this->nav_config() ); ?>
        <span class="nav-toggle mm-toggle" data-selector=".meganav-menu" ><i class="pl-icon pl-icon-navicon"></i></span>

        <div class="meganav-search nav-respond" data-bind="visible: search() == 1">
          <?php pl_searchform( true, 'nav-searchform'); ?>
        </div>
      
      </div>
    </div>
  </div>
<?php }




  function mobile_menu_template(){
  
    ?>
    <div class="pl-meganav-mobile mm-hidden"><div class="mm-holder"><div class="mm-menus"></div></div></div>
  
    <?php 
    
  }

}

if( ! class_exists('PL_Walker_Nav_Menu') ){

  // Adds arrows and classes
  class PL_Walker_Nav_Menu extends Walker_Nav_Menu {

      function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {

        $id_field = $this->db_fields['id'];

        /** Add dropdown */
          if ( ! empty($children_elements[$element->$id_field]) && $element->menu_item_parent == 0 ) {

              $element->title =  $element->title . ' <span class="sub-indicator"><i class="pl-icon pl-icon-caret-down"></i></span>';
        $element->classes[] = 'sf-with-ul';

          }

          /** Sub menu within the dropdown */
      if (!empty($children_elements[$element->$id_field]) && $element->menu_item_parent != 0) {
              $element->title =  $element->title . ' <span class="sub-indicator"><i class="pl-icon pl-icon-caret-right"></i></span>';
          }

          Walker_Nav_Menu::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
      }
  }

}

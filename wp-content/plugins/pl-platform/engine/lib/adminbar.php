<?php
/**
 * Admin Bar
 *
 * Adds the builder control menu to the WordPress adminbar
 *
 * @class     PL_Admin_Bar
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Admin_Bar{


  function __construct() {

    if ( ! pl_can_use_tools() ) {
      return false; }

    add_action( 'wp_before_admin_bar_render', array( $this, 'admin_bar_init' ) );

    if ( pl_is_workarea_iframe()  ) {
      add_filter( 'show_admin_bar', '__return_false' );
    }

    if ( pl_tools_active()  ) {

      add_action( 'admin_bar_menu', array( $this, 'admin_bar_save' ), 3 );
    }

    add_action( 'admin_bar_menu', array( $this, 'admin_bar_tools' ), 220 );
  }

  /** Remove WP Branding nonsense */
  function admin_bar_init() {

    global $wp_admin_bar;
    $this->bar = $wp_admin_bar;

    $wp_admin_bar->remove_node( 'wp-logo' );
  }


  function admin_bar_save() {

    global $wp_admin_bar;

    $wp_admin_bar->add_menu( array(
        'id'        => 'pl-ab-save',
        'parent'    => 'top-secondary',
        'title'     => sprintf( '<i class="pl-icon pl-icon-ok"></i> %s', __( 'Page Saved', 'pl-platform' ) ),
        'meta'      => array( 'class' => 'pl-ab-item pl-ab-save' ),
        'href'      => '#',
    ));
  }

  function admin_bar_array() {

    $m = array();

    $suffix = ( pl_tools_active()  ) ? 'down' : 'right';

    $m['leaf'] = array(
      'pos'     => 10,
      'parent'  => '',
      'id'      => 'pl-ab-menu',
      'title'   => sprintf( '<i class="pl-icon pl-icon-pagelines"></i> <i class="pl-icon pl-icon-action pl-icon-caret-%s"></i>', $suffix ),
      'rel'     => 'plBuilder',
      'href'    => PL()->urls->editor,
    );

    $m['builder'] = array(
      'pos'     => 50,
      'id'      => 'pl-ab-builder',
      'title'   => sprintf( '<i class="pl-icon pl-icon-list-alt"></i> %s', __( 'Page Editor', 'pl-platform' ) ),
      'rel'     => 'plBuilder',
      'href'    => PL()->urls->editor,
    );

    if ( pl_tools_active()  ) {

      $m['off'] = array(
        'pos'     => 300,
        'id'      => 'pl-ab-off',
        'rel'     => '_plDeactivate',
        'title'   => sprintf( '<i class="pl-icon pl-icon-remove"></i> %s', __( 'Turn Off', 'pl-platform' ) ),
        'href'    => esc_url( PL()->urls->deactivate ),
      );

    }

    $m = apply_filters( 'pl_ab_menu', $m );

    return apply_filters( 'pl_ab_menu_always', $m );

  }

  function get_sorted_menu_array() {

    $menu = $this->admin_bar_array();

    $default = array(
      'parent'  => 'pl-ab-menu',
      'pos'     => 100,
      'id'      => 'pl-ab-no-id',
      'title'   => __( 'No Title', 'pl-platform' ),
      'href'    => '#',
      'rel'     => '',
      'meta'    => array(),
    );

    foreach ( $menu as $i => &$m ) {

      if ( isset( $m['rel'] ) && '_' != substr( $m['rel'], 0, 1 ) ) {
        $m['meta'] = array( 'class' => 'pl-ab-item', 'rel' => $m['rel'] );
      } elseif ( isset( $m['rel'] ) ) {
        $m['meta'] = array( 'class' => 'pl-ab-link', 'rel' => $m['rel'] );
      }

      $m = wp_parse_args( $m, $default );

    }
    unset( $m ); // set by reference ^^

    uasort( $menu, 'pl_compare_position' );

    return apply_filters( 'pl_sorted_ab_menu', $menu );
  }

  function admin_bar_tools() {

    global $wp_admin_bar;

    foreach ( $this->get_sorted_menu_array() as $i => $item ) {

      // if the menu has a priv bit set, see if current user has that privilage
      // if not then move alone to next menu in the array.
      if ( isset( $item['priv'] ) && ! current_user_can( $item['priv'] ) ) {
        continue; }

      $wp_admin_bar->add_menu( array(
          'parent'  => $item['parent'],
          'id'      => $item['id'],
          'title'   => $item['title'],
          'meta'    => $item['meta'],
          'href'    => $item['href'],
      ));
    }
  }
}

global $pl_admin_bar;
$pl_admin_bar = new PL_Admin_Bar;

/**
 * Adds an item to the admin bar drop down under "PageLines"
 * Use 'pos' to control position.
 * $item = array(
 *     'pos'     => 100,
 *     'id'      => 'pl-ab-no-id',
 *     'title'   => 'No Title',
 *     'href'    => '#',
 *     'meta'    => array()
 *   );
 * TODO add to functions.php?
 */
global $pl_ab_items;
$pl_ab_items = array();
function pl_add_ab_menu( $item ) {

  global $pl_ab_items;

  $pl_ab_items[] = $item;
}

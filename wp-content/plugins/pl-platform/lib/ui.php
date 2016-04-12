<?php
/**
* Main Platform Admin UI
*
* @class     PL_Platform_Settings_UI
* @version   5.0.0
* @package   PageLines/Classes
* @category  Class
* @author    PageLines
*/
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Platform_Settings_UI {

  /**
   * Build The Layout
   */
  function __construct( $args = array(), PL_Platform $platform ) {

    $this->platform = $platform;

    $defaults = array(
        'title'       => '',
        'callback'    => false,
        'page_slug'   => '',
        'config'      => array(),
        'render'      => 'settings',
      );

    $this->set = wp_parse_args( $args, $defaults );

    /** Gets the control array */
    $this->config = (isset( $this->set['callback'] ) && $this->set['callback']) ? call_user_func( $this->set['callback'] ) : $this->set['config'];

    $this->current_tab_slug = (isset( $_GET['sel_tab'] )) ? $_GET['sel_tab'] : 'default';

    $this->settings_tab_slug = (isset( $_GET['settings_tab'] )) ? $_GET['settings_tab'] : 'default';

    $tab_default = array(
        'title'     => '',
        'groups'    => false,
        'mode'      => 'banner',
      );

    $current_tab_info = ( isset( $this->config[ $this->current_tab_slug ] ) ) ? $this->config[ $this->current_tab_slug ] : current( $this->config );

    $this->current_tab_config = wp_parse_args( $current_tab_info, $tab_default );

    $this->current_page = $this->platform->tab();

    $is_core_page = ('account' == $this->current_page || 'extend' == $this->current_page || 'settings' == $this->current_page) ? true : false;

    // Draw the thing
    $this->build_header();

    // if not logged in and user is a super admin.. show banner to connect account
    if ( $is_core_page && ! $this->platform->oauth->is_connected() && is_super_admin() ) {
      $this->platform_banner();
    } elseif ( has_action( 'pl_ui_build_body' ) ) {
      do_action( 'pl_ui_build_body', $this );
    } else {
      $this->build_body();
    }

    $this->build_footer();

  }

  /**
   * Option Interface Header
   */
  function build_header() {
?>

    <div class="wrap pagelines-admin">
<?php
if ( 'settings' == $this->set['render'] ) {
  $this->get_nav();
}
  }

  /**
   * Option Interface Footer
   */
  function build_footer() {
?>
    </div>
  <?php }

  function get_nav() {
    ?>
    <h2 class="nav-tab-wrapper">
      <?php

      $count = 1;
      foreach ( $this->config as $slug => $tabs ) {

        if ( $slug == $this->current_page || ( ( ! isset( $_GET['tab'] ) || 'default' == $_GET['tab'] ) && 1 == $count ) ) {
          $class = 'nav-tab-active';
        } else {           $class = ''; }

        printf( '<a class="nav-tab %s" href="?page=%s&tab=%s">%s</a>', $class, $this->set['page_slug'], $slug, $tabs['title'] );

        $count++;
      }

      ?>
    </h2>

    <?php
  }

  /**
   * Option Interface Body, including vertical tabbed nav
   */
  function build_body() {

    /** Switch for common modalities */
    if ( 'engine' == $this->current_tab_config['mode'] ) {
      $this->options_engine(); } else {       do_action( 'pl_platform_ui_body_' . $this->current_page, $this ); }

  }

  function options_engine() {

    $option_engine = new PL_Form_Engine( $this->set );

    if ( 'settings' == $this->set['render'] ) {
      printf( '<form id="%s" action="%s" method="POST">', $this->current_tab_slug, admin_url( 'admin-post.php' ) );
    }

    echo '<div class="pl-admin-settings tabinfo">';

    if ( isset( $_GET['msg'] ) && 'updated' == $_GET['msg'] ) {
      printf( '<div class="updated"><p><i class="pl-icon pl-icon-save"></i> %s</p></div>', __( 'PageLines Settings Updated!', 'pl-platform' ) );
    }

    printf( '<div class="pl-settings-tabs render-%s" data-selected="%s">',  $this->set['render'], $this->settings_tab_slug );

    $nav_items = false;

    foreach ( $this->current_tab_config['groups'] as $ind => $groups ) {

      $key = (isset( $groups['key'] )) ? $groups['key'] : 'key_' . $ind;

      $disabled = ( isset( $groups['disabled'] ) && $groups['disabled'] ) ? 'tab-disabled' : '';

      $nav_items .= sprintf( '<li><a class="%s" href="#%s"><i class="pl-icon pl-icon-%s"></i> %s</a></li>', $disabled, $key, $groups['icon'], $groups['title'] );

    }

    if ( ! $nav_items ) {
      return;
    }

    printf( '<div class="pl-settings-nav-wrap"><ul class="pl-settings-nav">%s</ul>%s</div>', $nav_items, $this->get_save() );

    printf( '<div class="pl-tab-panel-container">' );

    foreach ( $this->current_tab_config['groups'] as $ind => $groups ) {

      $key = (isset( $groups['key'] )) ? $groups['key'] : 'key_' . $ind;

      $disabled = ( isset( $groups['disabled'] ) && $groups['disabled'] ) ? 'tab-disabled' : '';

      printf( '<div id="%s" class="pl-tab-panel %s">', $key, $disabled );

      printf( '<h3 class="pl-panel-header"><i class="pl-icon pl-icon-%s"></i>%s</h3>', $groups['icon'], $groups['title'] );

      if ( isset( $groups['desc'] ) ) {
        printf( '<div class="pl-panel-description">%s</div>', $groups['desc'] ); }

      foreach ( $groups['opts'] as $o ) {

        $option_engine->option_engine( $o );

      }

      echo '</div>';
    }

    echo '</div></div>';

    echo '<div class="clear"></div></div>';

    if ( 'settings' == $this->set['render'] ) {
      echo '</form>';
    }
  }

  function get_save() {

    ob_start();

    if ( ! isset( $this->current_tab_config['hide_save'] ) || empty( $this->current_tab_config['hide_save'] ) ) {

      $redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );
      $redirect = urlencode( $_SERVER['REQUEST_URI'] );
      $action = 'pl_admin_save_settings';
      ?>
      <div class="pl-save">
      <?php if ( 'settings' == $this->set['render'] ) :  ?>
        <input type="hidden" name="settings_tab" class="selected_tab_input" value="<?php echo esc_attr( $this->current_tab_slug );?>">
        <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
        <input type="hidden" name="action" value="<?php echo $action;?>">
      <?php endif; ?>

        <?php wp_nonce_field( $action, 'pl_platform_settings_nonce', false ); ?>
        <input type="submit"  class="pl-save-settings button button-primary" value="<?php _e( 'Save Changes', 'pl-platform' );?>" />
      </div>
      <?php

    }

    return ob_get_clean();
  }



  function platform_banner() {

    echo $this->banner( array(
        'classes' => 'pl-dashboard banner-dashboard',
        'suphead' => 'PageLines Step 1',
        'header'  => 'Add Your Account',
        'subhead' => '<strong>Congrats!</strong> You have successfully installed PageLines. <br/>Next step is to add your account for extensions and updates.',
        'content' => sprintf( '<a class="pl-save-settings button button-large button-primary pl-platform-auth" href="%s"><i class="pl-icon pl-icon-link"></i> Step 1: Add PageLines Account</a>', $this->platform->oauth->connect_account_link() ),
        'img'     => sprintf( '<img src="%s/PL.png" alt="" class="avatar" />', $this->platform->images ),
    ));

  }

  /**
   * Display a HTML banner
   */
  function banner( $args = array() ) {

    return pl_admin_banner( $args );
  }

  /**
   * Main cards display function
   */
  function cards( $args = array() ) {

    $search           = ( isset( $_GET['s'] ) )       ? esc_attr( $_GET['s'] )  : '';
    $current_nav_item = ( isset( $_GET['navitem'] ) ) ? $_GET['navitem']        : '';

    $args = wp_parse_args( $args, array(

        'hook'  => '',
        'navscheme' => array(),
        'baseURL'   => PL_Platform()->url(),
        'sbitems'   => array(),
    ));
    ?>
    <div class="pl-cards-ui" data-baseurl="<?php echo $args['baseURL'];?>">
      <div class="pl-store-head fix">
        <h2 class="pl-store-title"></h2>
        <div class="pl-store-tag"><a href="https://www.pagelines.com/extensions" class="button" target="_blank">View on PageLines.com</a></div>

      </div>

      <div class="pl-cards-wrap fix">
        <div class="pl-cards-nav">
          
          <ul class="pl-filter-links">
            <?php foreach ( $args['navscheme'] as $type => $items ) :

                $name = ( isset( $items['name'] ) ) ? $items['name'] : pl_ui_key( $type );
                unset( $items['name'] );
            ?>
 
              <lh><span class="hdng"><?php echo $name;?></span></lh>
              
              <?php foreach ( $items as $cat => $details ) :

                      $navitem = $type . '_' . $cat;

                      $class = ( $current_nav_item == $navitem ) ? 'current' : '';

                      $details['q']['navitem'] = $navitem;

                      $url = add_query_arg( $details['q'], $args['baseURL'] );
              ?>
                 <li><a href="<?php echo $url ?>" class="<?php echo $class . ' ' . $navitem;?>" title="<?php echo $details['title'];?>"><?php echo $details['name'];?></a></li>
              <?php endforeach; ?>

            <?php endforeach; ?>


          </ul>
          
          <div class="card-nav-actions">


            <p><a class="button button-refresh" href="<?php echo add_query_arg( array( 'refresh' => 1 ), $args['baseURL'] );?>"><i class="pl-icon pl-icon-refresh"></i> <?php _e( 'Refresh Items', 'pl-platform' ); ?></a></p>
          </div>
        </div>

        <div class="pl-cards-pad">
          <div class="pl-cards-actions">
            <div class="pl-row">
              <div class="ca-item card-actions-primary pl-col-sm-8">

                <a href="<?php echo add_query_arg( array( 'navitem' => 'extensions_multi_install' ), PL_Platform()->url() );?>" class="button button-secondary disabled extensions_multi_install" title="Multiple Addons"></a>
                
                <?php if ( ! PL_Platform()->is_framework_installed() ) : ?>
                  <a class="button" href="<?php echo PL()->operations->framework_install_url(); ?>"><i class="pl-icon pl-icon-cloud-download"></i> Install Framework</a>
                <?php endif; ?>
              </div>
              
              <div class="ca-item card-actions-secondary pl-col-sm-4">
                <form class="search-form search-plugins" action="<?php echo admin_url( 'admin-post.php' ) ?>" method="post">
                   <input type="hidden" name="action" value="storesearch" />
                      <label><span class="screen-reader-text">Search</span>
                    <input class="wp-filter-search" label="search" type="text" name="s" placeholder="<?php _e( 'Search Extensions', 'pl-platform' ); ?>" value="<?php echo $search; ?>"/>
                  </label>
                  <input type="submit" id="search-submit" class="button screen-reader-text" value="Search">
                </form>

              </div>
            </div>
          </div>
          <div class="pl-cards" data-hook="<?php echo $args['hook'];?>"></div>
        </div>

      </div>
    </div>
    <?php
  }
  /**
   * Show connect/activate/purchase based on status
   */
  function header_buttons() {
    $button = array();
    if ( ! PL_PLatform()->oauth->is_connected() ) {
      $button['txt'] = 'Connect Account <i class="pl-icon pl-icon-angle-right"></i>';
      $button['url'] = PL_Platform()->oauth->connect_account_link();
      $button['icon'] = 'cogs';
      return $button;
    }
    if ( ! PL_Platform()->oauth->is_local_and_has_pro()
        && ! PL_Platform()->is_pro()
        && PL_Platform()->oauth->can_register_site() ) {
      $button['txt'] = sprintf( 'Activate Pro %s', PL_Platform()->oauth->get_domain_data_format() );
      $button['url'] = PL_PLatform()->url( 'account' );
      $button['icon'] = 'bolt';
      return $button;
    }

    if ( ! PL_Platform()->is_pro()
        && ! PL_Platform()->oauth->can_register_site() ) {
      $button['txt'] = 'Purchase';
      $button['url'] = PL_PLatform()->url( 'account' );
      $button['icon'] = 'cart';
      return $button;
    }
    return false;
  }
} // End Class

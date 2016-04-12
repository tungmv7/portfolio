<?php
/**
 * Account Handling Class
 *
 * @class     PL_Platform_Account
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Platform_Account {

  function __construct() {

    add_filter( 'pl_platform_config_account',  array( $this, 'config' ) );
    add_action( 'pl_platform_ui_body_account', array( $this, 'template' ), 10, 2 );
  }

  /**
   * Master Settings configuration for the admin.
   */
  function config() {

    $d = array(
      'default'   => array(
      'title'     => get_admin_page_title(),
      ),
    );

    return $d;
  }

  /**
   * Account page template
   */
  function template( $ui ) {

    $data = $ui->platform->oauth->get_cache_data();

    if ( ! $data || ! isset( $data->user->email ) ) {
      return; }

    echo '<div class="pl-dashboard">';

      $this->version( $data, $ui );
      $this->account( $data, $ui );

    echo '</div>';
  }

  function account( $data, $ui ) {

    $btns = sprintf( '<div class="actions"><a class="button button-primary" href="%s" target="_blank"><i class="pl-icon pl-icon-user"></i>&nbsp; %s</a> <a class="button" href="%s">%s</a> <a class="button" href="%s">%s</a></div>',
        PL()->urls->my_account,
        __( 'My Account', 'pl-platform' ),
        $ui->platform->url( 'account', array( 'action' => 'refresh_user' ) ),
        __( 'Refresh', 'pl-platform' ),
        $ui->platform->url( 'account', array( 'pl_platform_logout' => 1 ) ),
        __( 'Disconnect', 'pl-platform' )
    );

    echo $ui->banner( array(
          'classes' => 'banner-dashboard',
          'title'   => __( 'PageLines Account', 'pl-platform' ),
          'header'  => $data->user->display_name,
          'suphead' => __( 'Site Connected As', 'pl-platform' ),
          'subhead' => sprintf( '%s %s',
              __( 'Member Since', 'pl-platform' ),
              date_i18n( 'F Y', strtotime( $data->user->user_registered ) )
          ),
          'content' => $btns,
          'img'     => get_avatar( $data->user->email, 250 ),
    ));

  }

  /**
   * Work out the installed licence and display buttons accordingly
   */
  function version( $data, $ui ) {

    $domain_data        = $ui->platform->oauth->get_domains_data();
    $registered         = $ui->platform->oauth->is_site_registered();
    $local_pro          = $ui->platform->oauth->is_local_and_has_pro();
    $can_register       = $ui->platform->oauth->can_register_site();
    $grandfathered      = $ui->platform->oauth->is_grandfathered();
    $grandfathered_txt  = '';

    // if user has NOT registered this domain and is able to
    if ( ! $registered ) {

      $version  = __( 'Free', 'pl-platform' );
      $desc     = __( 'Only free features and extensions are available.', 'pl-platform' );

      if ( $can_register ) {

        $domain_format = PL_Platform()->oauth->get_domain_data_format();

        $action_url = PL_Platform()->oauth->domain_activate_link();

        $txt        = __( 'Activate Pro ', 'pl-platform' ) . $domain_format;
        $action_txt = sprintf( '<i class="pl-icon pl-icon-bolt"></i> %s', $txt );
      } else {
        $action_url = PL()->urls->purchase;
        $txt        = __( '<strong>Get License</strong>', 'pl-platform' );
        $action_txt = sprintf( '<i class="pl-icon pl-icon-shopping-cart"></i> %s', $txt );
      }

      $thumb = pl_framework_url( 'images' ) . '/thumb-free.png';
    } // user has already registered this domain so show button to unregister it.
    else {

      $version    = __( 'Professional', 'pl-platform' );
      $desc       = __( 'All features are available. Yay!', 'pl-platform' );

      $desc       .= ( $local_pro ) ? __( '<p>(<strong>Localhost and at least one pro license detected in account.</strong>)</p>', 'pl-platform' ) : '';

      $action_url = PL_Platform()->url( 'extend' );
      $action_txt = sprintf( '<i class="pl-icon pl-icon-download"></i> %s', __( 'Install New Extensions', 'pl-platform' ) );
      $thumb      = pl_framework_url( 'images' ) . '/thumb-pro.png';
    }
    // see if user is grandfathered in to platform.
    if ( true == $grandfathered ) {
      $grandfathered_txt = sprintf( ' (%s)', __( 'Grandfathered', 'pl-platform' ) );
    }

    $desc .= sprintf( '<p><strong>%s</strong> of <strong>%s total</strong> Pro licenses available in your account%s</p>',
        $domain_data->remaining,
        $domain_data->allowed,
        $grandfathered_txt
    );

    if ( 0 == $domain_data->allowed ) {
      $desc = sprintf( '<p>%s</p>', __( 'There are no Pro licenses available in your account.', 'pl-platform' ) );
    }

    $actions  = sprintf( '<div class="actions"><a class="button button-primary" href="%s">%s</a> &nbsp; <a class="button" href="%s">%s</a></div>',
        $action_url,
        $action_txt,
        PL()->urls->pro,
        __( 'Learn More', 'pl-platform' )
    );

    echo $ui->banner( array(
             'classes'     => 'banner-dashboard',
             'title'       => __( 'PageLines Version', 'pl-platform' ),
             'suphead'     => sprintf( 'PageLines Version' ),
             'header'      => sprintf( '<strong>%s</strong>', $version ),
             'subhead'     => $desc,
             'content'     => $actions,
             'src'         => $thumb,
    ));

    if ( $registered && ! pl_is_local() ) {

      $content  = sprintf( '<p class="banner-subheader">You will have %s %s after a successful handoff.</p>',
          $domain_data->remaining + 1,
          _n( 'slot', 'slots', $domain_data->remaining + 1, 'pl-platform' )
      );

      echo $ui->banner( array(
               'classes'     => 'banner-dashboard',
               'title'       => __( 'Switching Accounts', 'pl-platform' ),
               'suphead'     => sprintf( 'Need to handoff to a client?' ),
               'subhead'     => sprintf( 'Get your professional slot back by activating pro with any other account.' ),
               'header'      => 'Switch Accounts',
               'content'     => $content,
      ));
    }

  }
}
new PL_Platform_Account;

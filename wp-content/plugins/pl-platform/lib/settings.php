<?php
/**
 * Platform Admin Settings Handler
 *
 * @class     PL_Platform_Settings
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Platform_Settings {

  function __construct() {

    add_filter( 'pl_platform_config_settings',    array( $this, 'config' ), 10, 2 );
    add_filter( 'pl_platform_config_meta',        array( $this, 'config' ), 10, 2 );
    add_filter( 'pl_platform_config_profile',     array( $this, 'config' ), 10, 2 );
    add_filter( 'pl_platform_customizer_config',  array( $this, 'settings' ) );

  }

  /**
   * Master Settings configuration for the admin.
   */
  function config( $d, $page ) {

    if ( 'meta' == $page ) {
      $config = $this->meta_settings();
    } elseif ( 'profile' == $page ) {
      $config = $this->profile_settings();
    } else {
      $config = $this->settings();
    }

    $d = array(
      'pagelines_settings' => array(
        'title'   => __( 'PageLines Settings', 'pl-platform' ),
        'mode'    => 'engine',
        'groups'  => $config,
        'hide_save' => ( is_multisite() && is_network_admin() ) ? true : false, // hide save button if on network super admin page
      ),
    );

    return $d;
  }

  function settings() {

    $settings = array();

    $settings['welcome'] = array(
      'key'       => 'about',
      'icon'      => 'dashboard',
      'pos'       => 15,
      'location'  => array( 'settings' ),
      'title'     => __( 'Dashboard', 'pl-platform' ),
      'opts'  => array(

        array(
          'key'     => 'welcometext',
          'type'    => 'longform',
          'text'    => $this->welcome_message(),
        ),

      ),
    );

    

    $settings['site_social'] = array(
      'key'         => 'site_social',
      'icon'        => 'facebook-square',
      'location'    => array( 'settings' ),
      'pos'         => 400,
      'title'       => __( 'Social / Local', 'pl-platform' ),
      'opts'  => array(
        array(
          'key'     => 'username_facebook',
          'type'    => 'text',
          'title'   => __( 'Sitewide Facebook Username', 'pl-platform' ),
        ),
        array(
          'key'     => 'username_twitter',
          'type'    => 'text',
          'title'   => __( 'Sitewide Twitter Username', 'pl-platform' ),
        ),

        array(
          'type'    => 'multi',
          'title'   => __( 'Sitewide Instagram', 'pl-platform' ),
          'help'   => __( 'Your Instagram API access token.<br/> <a href="http://instagram.pixelunion.net/" target="_blank">Look it up here</a>.'
          , 'pl-platform' ),
          'opts'    => array(
              array(
                'key'     => 'instagram_token',
                'type'    => 'text',
                'title'   => __( 'Instagram Access Token', 'pl-platform' ),

              ),
              array(
                'key'     => 'username_instagram',
                'type'    => 'text',
                'title'   => __( 'Instagram Username', 'pl-platform' ),
              ),
            ),
        ),
        array(
          'key'     => 'username_github',
          'type'    => 'text',
          'title'   => __( 'Sitewide Github Username', 'pl-platform' ),
        ),

      ),
    );

    

    $pl_advanced_opts = apply_filters('pl_advanced_settings', array(
        array(
          'key'     => 'custom_scripts',
          'type'    => 'script',
          'mode'    => 'html',
          'layout'  => 'full',
          'kses'    => 'bypass', // bypass wp_keses() for this option.
          'title'   => __( 'Header Scripts', 'pl-platform' ),
          'label'   => __( 'Enter Header HTML/JS', 'pl-platform' ),
          'place'   => '<!-- Add HTML / Scripts here -->',
        ),
    ));

    $disabled = ( ! pl_is_professional() ) ? true : false;
    
    $settings['pl_professional'] = array(
      'disabled'  => $disabled,
      'key'       => 'pl_professional',
      'icon'      => 'check-square',
      'pos'       => 450,
      'title'     => __( 'Pro Options', 'pl-platform' ),
      'location'   => array('settings'),
      'opts'  => array(
        array(
          'key'       => 'hide_pl_cred',
          'type'      => 'checkbox',
          'label'     => 'Remove PageLines link credit in Platform 5?',
          'title'     =>  __( 'Remove PageLines Link Credit?', 'pl-platform' ),
        ),
      )
    );

    $settings['advanced'] = array(
      'key'       => 'advanced',
      'icon'      => 'code',
      'pos'       => 500,
      'location'  => array( 'settings' ),
      'title'     => __( 'Advanced', 'pl-platform' ),
      'opts'      => $pl_advanced_opts,
    );

    $settings = apply_filters( 'pl_platform_settings_array', $settings );

    uasort( $settings, 'pl_compare_position' );

    // if were on a multisite and we are in the network adnin area we *only* want to see the dashboard
    // as none of the settings panel will actually work.
    if ( is_multisite() && is_network_admin() ) {
      $settings = array(
        'welcome' => $settings['welcome'],
      );
    }
    return $settings;
  }

  function meta_settings() {

    $settings = apply_filters( 'pl_platform_meta_settings_array', array() );

    uasort( $settings, 'pl_compare_position' );

    return $settings;
  }

  function profile_settings() {

    $settings = array();

    $settings['basics'] = array(
      'key'       => 'basics',
      'icon'      => 'pagelines',
      'pos'       => 6,
      'title'     => __( 'PageLines Info' , 'pl-platform' ),
      'opts'  => array(
        array(
         'key'           => 'pl_company',
         'type'          => 'text',
         'title'         => __( 'Company Name', 'pl-platform' ),
        ),

        array(
         'key'           => 'pl_site',
         'type'          => 'text',
         'title'         => __( 'Company Website URL', 'pl-platform' ),
        ),

        array(
         'key'           => 'pl_position',
         'type'          => 'text',
         'title'         => __( 'Your Position / Role', 'pl-platform' ),
        ),
        array(
         'key'           => 'pl_facebook',
         'type'          => 'text',
         'title'         => __( 'Your Facebook Username', 'pl-platform' ),
        ),
        array(
         'key'           => 'pl_instagram',
         'type'          => 'text',
         'title'         => __( 'Your Instagram Username', 'pl-platform' ),
        ),
        array(
         'key'           => 'pl_instagram_token',
         'type'          => 'text',
         'title'         => __( 'Your Instagram Token', 'pl-platform' ),
         'help'          => __( 'Your Instagram API access token.<br/> <a href="http://instagram.pixelunion.net/" target="_blank">Look it up here</a>.', 'pl-platform' ),
        ),
        array(
         'key'           => 'pl_github',
         'type'          => 'text',
         'title'         => __( 'Your Github Username', 'pl-platform' ),
        ),
        array(
         'key'           => 'pl_twitter',
         'type'          => 'text',
         'title'         => __( 'Your Twitter Username', 'pl-platform' ),
        ),

        array(
         'key'           => 'pl_city',
         'type'          => 'text',
         'title'         => __( 'Your Home City', 'pl-platform' ),
        ),
        array(
         'key'           => 'pl_country',
         'type'          => 'text',
         'title'         => __( 'Your Home Country', 'pl-platform' ),
        ),

      ),
    );

    $settings = apply_filters( 'pl_platform_profile_settings_array', $settings );

    uasort( $settings, 'pl_compare_position' );

    return $settings;
  }

  function welcome_steps() {

    if ( ! PL_Platform()->oauth->is_connected() ) : ?>

    <h3><?php _e( '<span class="subtle">Next Step:</span> Add PageLines Account', 'pl-platform' ); ?></h3>
    <ul class="reference-list nextstep">
      <li>
        <div class="list-icon"><i class="pl-icon pl-icon-plus"></i></div>
        <h4><?php _e( 'Add Your PageLines.com Account', 'pl-platform' ); ?></h4>
        <p>
          <?php _e( 'Needed for one click install, upgrades and support.', 'pl-platform' ); ?>
        </p>
        <p>
          <a class="button button-primary" href="<?php echo PL_Platform()->oauth->connect_account_link();?>"><i class="pl-icon pl-icon-plus"></i>&nbsp;<?php _e( 'Add', 'pl-platform' ); ?> <i class="pl-icon pl-icon-caret-right"></i></a>
        </p>
      </li>
    </ul>

    <?php elseif ( ! PL_Platform()->has_installed_something() ) :  ?>

      <h3><?php _e( '<span class="subtle">Next Step:</span> Install Extensions', 'pl-platform' ); ?></h3>
      <ul class="reference-list nextstep">
        <li>
          <div class="list-icon"><i class="pl-icon pl-icon-plug"></i></div>
          <h4><?php _e( 'Get PageLines Extensions', 'pl-platform' ); ?></h4>
          <p>
            <?php _e( 'Platform is extension based, go and install some extensions.', 'pl-platform' ); ?>
          </p>
          <p>
            <a class="button button-primary" target="_blank" href="<?php echo PL_Platform()->url( 'extend' );?>"><i class="pl-icon pl-icon-plug"></i> <?php _e( 'Extension Engine', 'pl-platform' ); ?> <i class="pl-icon pl-icon-caret-right"></i></a>
          </p>
        </li>
      </ul>

    <?php elseif ( PL_Platform()->is_oriented() && ! PL_Platform()->is_pro() ) :  ?>

          <h3><?php _e( '<span class="subtle">Next Step:</span> Upgrade to Pro Version', 'pl-platform' ); ?></h3>
          <ul class="reference-list nextstep">
            <li>
              <div class="list-icon"><i class="pl-icon pl-icon-pagelines"></i></div>
              <h4><?php _e( 'Get the most from PageLines with Pro.', 'pl-platform' ); ?></h4>
              <p>
                <?php _e( 'Get all pro extensions plus more every month. Also updates, options &amp; support.', 'pl-platform' ); ?>
              </p>
              <p>
                <?php
                if ( ! PL_Platform()->oauth->can_register_site() ) {
                  $getclass = 'primary';
                  $actclass = 'disabled';
                } else {
                  $getclass = 'secondary';
                  $actclass = 'primary';
                }

                $domains = PL_Platform()->oauth->get_domain_data_format();

                ?>
                <a class="button button-<?php echo $getclass;?>" target="_blank" href="<?php echo PL()->urls->purchase;?>"><i class="pl-icon pl-icon-shopping-cart"></i>&nbsp;<?php _e( 'Get License', 'pl-platform' ); ?> <i class="pl-icon pl-icon-caret-right"></i></a>

                <a class="button button-<?php echo $actclass;?>" target="_blank" href="<?php echo PL_Platform()->oauth->domain_activate_link();?>">
                  <i class="pl-icon pl-icon-bolt"></i>&nbsp;
                  <?php _e( 'Activate Pro', 'pl-platform' ); ?>
                  <?php printf( '%s', $domains ); ?>
                  </a>
              </p>
            </li>
          </ul>

    <?php endif;

  }

  function use_pagelines_framework() {

    if ( ! PL_Platform()->is_framework_installed() || ! PL_Platform()->is_framework_active() ) :

      if ( ! PL_Platform()->is_framework_installed() ) {

        $install_class = 'button-primary';

        $activate_class = 'button-secondary disabled';

      } else {

        $install_class = 'button-secondary disabled';

        $activate_class = 'button-primary';

      }
      ?>

      <h3><?php _e( '<span class="subtle">Tip:</span> Use PageLines Framework', 'pl-platform' ); ?></h3>
      <ul class="reference-list nextstep">
        <li>
          <div class="list-icon"><i class="pl-icon pl-icon-list-alt"></i></div>
          <h4><?php _e( 'Use PageLines Framework', 'pl-platform' ); ?></h4>
          <p>
            <?php _e( 'PageLines Framework is optimized for use with PageLines Platform.', 'pl-platform' ); ?>
          </p>
          <p>
            <?php _e( 'It is fully-responsive, has a ton of options, and has support for child themes, templates and more.', 'pl-platform' ); ?>
          </p>
          <p>
           

            <a class="button <?php echo $install_class;?>" href="<?php echo PL()->operations->framework_install_url();?>">
              <i class="pl-icon pl-icon-download"></i>&nbsp;<?php _e( 'Install', 'pl-platform' ); ?> <i class="pl-icon pl-icon-caret-right"></i>
            </a>
            <a class="button <?php echo $activate_class;?>" href="<?php echo admin_url( 'themes.php' );   ?>">
              <i class="pl-icon pl-icon-bolt"></i>&nbsp;<?php _e( 'Activate', 'pl-platform' ); ?> <i class="pl-icon pl-icon-caret-right"></i>
            </a>
            
          </p>
        </li>
      </ul>
  


    <?php endif;

  }

  function welcome_message() {

    $store_url = sprintf( '<a href="%s">%s</a>', PL_Platform()->url( 'store' ), __( 'Extension Engine', 'pl-platform' ) );
    ob_start();
?>
    <div class="intro clearfix">
      <img alt="Platform 5" class="theme-screen" src="<?php echo pl_framework_url( 'images' ) . '/PL5.png';?>" />
      <h3><?php _e( 'Welcome to PageLines', 'pl-platform' ); ?></h3>
      <p><?php _e( "Congratulations! You're running PageLines. PageLines adds insanely-fast editing and customization tools to your site.", 'pl-platform' ); ?></p>
      <div class="clear" ></div>
    </div>

    <?php $this->welcome_steps();?> 

    <?php $this->use_pagelines_framework();?> 

    <h3><?php _e( 'What can you do with PageLines? Everything.', 'pl-platform' ); ?></h3>
    <ul class="reference-list">
      <li>
        <div class="list-icon"><i class="pl-icon pl-icon-bolt"></i></div>
        <h4><?php _e( 'Quick Start: "Insanely Fast Design &amp; Editing"', 'pl-platform' ); ?></h4>
        <p>
          <?php _e( 'Quickly learn how to customize in real time with no coding, debugging or frustration.', 'pl-platform' ); ?>
        </p>
        <p>
          <a class="button button-primary" target="_blank" href="<?php echo PL()->urls->quickstart;?>"><i class="pl-icon pl-icon-video-camera"></i>&nbsp;<?php _e( 'Quick Start Video', 'pl-platform' ); ?></a>
        </p>
      </li>
      <li>
        <div class="list-icon"><i class="pl-icon pl-icon-plug"></i></div>
        <h4><?php _e( 'Do anything with the extension engine. Really.', 'pl-platform' ); ?></h4>
        <p>
          <?php printf( __( 'PageLines is built to be extended. Add some with the %s.', 'pl-platform' ), $store_url ); ?>
        </p>
        <p>
          <a class="button button-secondary" href="<?php echo PL_Platform()->url( 'extend' );?>"><i class="pl-icon pl-icon-plug"></i> <?php _e( 'Install New Extensions', 'pl-platform' ); ?></a>
        </p>
      </li>
      <li>
        <div class="list-icon"><i class="pl-icon pl-icon-slack"></i></div>
        <h4><?php _e( 'Connect with the community.', 'pl-platform' ); ?></h4>
        <p>
          <?php printf( __( 'Join the PageLines community channels for help, updates, and great conversation.', 'pl-platform' ), $store_url ); ?>
        </p>
        <p>
          <a class="button" target="_blank" href="http://www.pagelines.com/community"><i class="pl-icon pl-icon-slack"></i> <?php _e( 'Community Overview', 'pl-platform' ); ?></a>
        </p>
      </li>
      <li>
        <div class="list-icon"><i class="pl-icon pl-icon-smile-o"></i></div>
        <h4><?php _e( 'We love to help.', 'pl-platform' ); ?></h4>
        <p>
          <?php printf( __( 'Check out the PageLines Forums or documentation resources.', 'pl-platform' ), $store_url ); ?>
        </p>
        <p>
          <a class="button" href="http://www.pagelines.com/support" target="_blank"><i class="pl-icon pl-icon-smile-o"></i> <?php _e( 'Support Overview', 'pl-platform' ); ?></a>
        </p>
      </li>
      <li>
        <div class="list-icon"><img alt="Andrew Powers" src="<?php echo pl_framework_url( 'images' ) . '/avatar-powers.jpg';?>" /></div>
        <h4><?php _e( 'A note from me...', 'pl-platform' ); ?></h4>
        <p><?php _e( "Good luck with your site. We're happy and excited for you. Don't forget that we're here to help and your feedback is always welcome.", 'pl-platform' ); ?> <a href="http://www.pagelines.com/about" target="_blank">About PageLines</a></p>
        <div class="signature">
          <p><?php _e( 'Sincerely', 'pl-platform' ); ?>,</p>
          <img alt="Signature" src="<?php echo pl_framework_url( 'images' ) . '/signature-founder.png';?>" />
          <div class="citation"><?php _e( 'Andrew Powers, Founder', 'pl-platform' ); ?></div>
        </div>
      </li>
    </ul>
    
    <?php
    return ob_get_clean();
  }
}
new PL_Platform_Settings;

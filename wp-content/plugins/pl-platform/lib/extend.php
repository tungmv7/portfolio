<?php
/**
 * Extension Handling Class
 *
 * @class     PL_Platform_Extend
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Platform_Extend{

  function __construct() {

    add_filter( 'pl_platform_config_extend',  array( $this, 'config' ) );

    add_action( 'pl_platform_ui_body_extend', array( $this, 'store' ), 10, 2 );

    add_action( 'pl_platform_server_extend', array( $this, 'ajax' ), 10, 2 );

    /**
     * Post hook for search
     */
    add_action( 'admin_post_storesearch',    array( $this, 'storesearch' ) );
    add_action( 'wp_ajax_pl_card_fav',       array( $this, 'pl_card_fav_callback' ) );
    add_action( 'wp_ajax_pl_card_fav_list',  array( $this, 'pl_card_fav_callback_list' ) );

    add_action( 'pl_ui_build_body',          array( $this, 'pl_ui_build_body' ) );
  }

  function pl_ui_build_body( $obj ) {

    if ( isset( $_GET['install_multi'] ) && 'true' == $_GET['install_multi'] ) {

      $products = explode( ',', $_GET['slugs'] );
      foreach ( $products as $k => $product ) {
        $products[ $product ] = $product;
        unset( $products[ $k ] );
      }

      printf( '<h2>%s</h2>', __( 'Installing Selected Products', 'pl-platform' ) );

      // lets go!
      if ( ! empty( $products ) ) {

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'type', 'title', 'nonce', 'url' ) ) );

        $data = PL_Platform()->functions->cache_get( 'connect_updates' );

        // if no cache we need to fetch the data
        if ( ! $data ) {
          global $pl_platform_updates;
          $data = $pl_platform_updates->updates_data( true );
        }

        foreach ( $data as $k => $prod ) {

          $data[ $prod->slug ] = $prod;

          unset( $data[ $k ] );

        }

        foreach ( $products as $slug => $k ) {

            $path = sprintf( '%s/%s.php', $slug, $slug );

          if ( PL_Platform()->extend->is_plugin_installed( $path ) ) {
            printf( '<p>%s is already installed, skipping.</p>', $data[ $slug ]->post_title );
          } else {

            printf( '<p><div class="%s-loading-spinner"><i class=" pl-icon pl-icon-cog pl-icon-spin" style="opacity: .4;"></i> Installing <strong>%s</strong></div> <div style="display:none">',
                $slug,
                $data[ $slug ]->post_title
            );

            $link = PL_Platform_Updater::get_download_link( $data[ $slug ]->download_data );

            $res = $upgrader->install( $link );

            printf( '</div></p><script>jQuery(".%s-loading-spinner").hide()</script>', $slug );

            if ( $upgrader->plugin_info() ) {

              $result = activate_plugin( $path );

              printf( '<p><strong>%s</strong> has been successfully installed and activated.</p>', $data[ $slug ]->post_title );

            } else {

              echo $res;
              break;

            }
          }
        }

          echo '<h3>All Done!</h3></p><script>localStorage.clear(); setTimeout( function(){ window.location.href=window.PLAdmin.extendURL }, 3000 );</script>';

      }
    } else {

      $obj->build_body();

    }

  }

  /**
   * Process ajax response.
   */
  function ajax( $response, $data ) {

    $query = (isset( $data['queryVars'] )) ? $data['queryVars'] : array();

    if ( 'pros' == $query['special'] ) {

      $banner = pl_admin_banner( array(
            'header'  => '3rd Party Developers',
            'subhead' => sprintf( 'Supported 3rd Party Extensions. Coming Soon!' ),
            'content' => '<a href="http://www.pagelines.com/developer/apply" class="button button-primary">Apply Now</a>',
            'src'     => pl_framework_url( 'images' ) . '/thumb-badge.png',
            'classes' => 'banner-cards',
      ));

      $response['cards'] = array(
          'html'  => $banner,
        );
    } else {

      $cards = PL_Platform()->oauth->request( 'store', $query );

      $response['cards'] = $this->refine_cards( $cards );

    }

    return $response;
  }
  function pl_card_fav_callback_list() {
    $user_id = wp_get_current_user()->ID;
    $favs = (array) get_user_meta( $user_id, '_card_fav', true );
    header( 'Content-Type: application/json' );
    echo json_encode( array( 'favs' => $favs ) );

    exit(); // don't forget this, always returns 0 w/o
  }

  function pl_card_fav_callback() {

    /** Incoming post data */
    $postdata = $_POST;
    if ( isset( $postdata['nonce'] ) ) {
      pl_verify_ajax( $postdata['nonce'] );
    } else {
      die( 'No Nonce set!' );
    }
    //we send fav request to server... and do same locally

    $user_id = wp_get_current_user()->ID;

    $response = PL_Platform()->oauth->request( 'fav', array( 'slug' => $postdata['slug'] ) );

    update_user_meta( $user_id, '_card_fav', (array) $response->favs );

    /** JSON response for output and UI actions */
    header( 'Content-Type: application/json' );
    echo json_encode( $response );

    exit(); // don't forget this, always returns 0 w/o
  }

  /**
   * Master Settings configuration for the admin.
   */
  function config() {

    $d = array(
      'default'   => array(
      'title'     => __( 'PageLines Extension Engine', 'pl-platform' ),
      ),
    );
    return apply_filters( 'pl_platform_store_config', $d );
  }


  function nav_schema() {

    $nav_schema = array();

    $nav_schema['extensions'] = array(
            'featured'  => array(
                'name'    => __( 'New &amp; Featured', 'pl-platform' ),
                'title'   => __( 'New &amp; Featured Extensions', 'pl-platform' ),
                'q'       => array( 'special' => 'multicat', 'special_key' => 'framework__featured' ),
              ),
            'downloads' => array(
                'name'      => __( 'Most Downloads', 'pl-platform' ),
                'title'     => __( 'Most Downloads', 'pl-platform' ),
                'q'         => array( 'special' => 'multicat', 'special_key' => 'framework', 'downloads' => 'true', 'posts_per_page' => -1 ),
            ),
            'pro'   => array(
                'name'      => __( 'Top Pro', 'pl-platform' ),
                'title'     => __( 'Top Pro Extensions', 'pl-platform' ),
                'q'         => array( 'special' => 'multicat', 'special_key' => 'framework__pro' ),
              ),
            'free'   => array(
                'name'      => __( 'Top Free', 'pl-platform' ),
                'title'     => __( 'Top Free Extensions', 'pl-platform' ),
                'q'         => array( 'special' => 'multicat', 'special_key' => 'framework__free' ),
              ),
            'sections'  => array(
                'name'      => __( 'Sections', 'pl-platform' ),
                'title'     => __( 'Framework Sections', 'pl-platform' ),
                'q'         => array( 'special' => 'multicat', 'special_key' => 'framework__sections' ),
              ),
            'themes'    => array(
                'name'      => __( 'Themes', 'pl-platform' ),
                'title'     => __( 'Framework Themes', 'pl-platform' ),
                'q'         => array( 'special' => 'multicat', 'special_key' => 'framework__framework-theme' ),
              ),
            'plugins'   => array(
                'name'      => __( 'Plugins', 'pl-platform' ),
                'title'     => __( 'Framework Plugins', 'pl-platform' ),
                'q'         => array( 'special' => 'multicat', 'special_key' => 'framework__plugins' ),
              ),
            'third'   => array(
                'name'      => __( '3rd Party', 'pl-platform' ),
                'title'     => __( '3rd Party Developers', 'pl-platform' ),
                'q'         => array( 'special' => 'pros', 'special_key' => 'developers' ),
              ),
            'favs'    => array(
              'name'      => __( 'My favorites', 'pl-platform' ),
              'title'     => __( 'favorite Addons', 'pl-platform' ),
            ),
          );

    return $nav_schema;
  }

  function store_sb_items() {

    $items = array();

    $registered = PL_Platform()->oauth->is_site_registered();
    $can_register = PL_Platform()->oauth->can_register_site();

    if ( ! $registered ) {

      // ok site not registered, but is there a slot available?
      if ( $can_register ) {
        $args = array(
          'domain' => PL_Platform()->oauth->get_site_domain(),
          'action' => 'add_domain',
        );

        $items['pro'] = array(
                        'title'   => 'Register Domain',
                        'details' => 'Activate this domain to recieve updates and install all the extensions',
                        'action'  => sprintf( '<a class="button button-large button-primary" href="%s">Activate</a>', PL_Platform()->url( 'account', $args ) ),
                        'img'     => PL_Platform()->images . '/sq-platform.png',
                      );
      } else {
        $items['pro'] = array(
                        'title'   => 'The Pro Platform',
                        'details' => 'Go professional get all premium PageLines extensions free, instantly. You\'ll love it, guaranteed!',
                        'action'  => sprintf( '<a class="button button-primary" href="%s"><i class="icon icon-key"></i> Go Pro</a>', PL_Platform()->url( 'buy_platform' ) ),
                        'img'     => PL_Platform()->images . '/sq-platform.png',
                      );
      }
    }

    if ( ! PL_Platform()->is_framework_active() ) {

      $framework_action = ( PL_Platform()->is_framework_installed() )
        ? sprintf( '<a class="button button-primary" href="%s"><i class="icon icon-bolt"></i> Activate</a>', admin_url( 'themes.php' ) )
        : sprintf( '<a class="button button-primary" href="%s"><i class="icon icon-download"></i> Install</a>', PL()->operations->framework_install_url() );

      $items['framework'] = array(
                      'title'   => 'The Framework',
                      'details' => 'Use PageLines Framework. A drag and drop front-end made for professionals and their clients.',
                      'action'  => $framework_action,
                      'img'     => PL_Platform()->images . '/sq-framework.png',
                    );

    }

    return $items;
  }

  /**
   * Display store object
   */
  function store( $ui ) {

    echo $ui->cards(array(
        'hook'      => 'extend',
        'navscheme' => $this->nav_schema(),
        'baseURL'   => PL_Platform()->url( 'extend' ),
        'sbitems'   => $this->store_sb_items(),
    ));
  }

  /**
   * Refine store card data, add thumbs etc
   */
  function refine_cards( $cards ) {

    $user_id = wp_get_current_user()->ID;
    $favs    = (array) get_user_meta( $user_id, '_card_fav', true );

    if ( is_array( $cards ) ) {
      foreach ( $cards as $i => $p ) {
        if ( ! is_array( $p->thumb ) ) {
          $p->thumb = array( pl_fallback_image() );
        }
        $cards[ $i ]->thumb        = $this->format_thumb( $p->thumb[0] );
        $cards[ $i ]->actionlink   = $this->get_action_link( $p );
        $cards[ $i ]->version_html = $this->card_version_html( $p );
        $cards[ $i ]->modified     = human_time_diff( strtotime( $p->post_modified ) );
        $cards[ $i ]->fav          = ( isset( $favs[ $p->slug ] ) ) ? 'pl-icon pl-icon-star' : 'pl-icon pl-icon-star-o';
        //  error_log(print_r( $cards[$i], true ) );
      }
    }
    return $cards;
  }

  /**
   * If we are in dev mode, strip cdn code..
   */
  function format_thumb( $thumb ) {
    if ( pl_dev_mode() ) {
      // need to strip the replace the cdn with www and add a cachebusting random number.
      $thumb = str_replace( 'wpecdn', 'www', $thumb );
      return add_query_arg( array( 'rnd' => PL_Platform()->config['rand'] ), $thumb );
    }
    return $thumb;
  }

  /**
   * Show item version
   */
  function card_version_html( $p ) {
    return sprintf( '<span class="version">v%s</span>', $p->version );
  }

  /**
   * Returns local installed version or false if not installed
   * Used to determine updates
   */
  function get_product_version( $p ) {

    // if not installed dont bother with version checks.
    if ( ! $this->product_installed( $p ) ) {
      return false;
    }

    // if theme installed, return theme version
    if ( wp_get_theme( $p->slug )->exists() ) {
      return wp_get_theme( $p->slug )->version;
    }

    // if not theme must be a plugin
    $plugin_file = sprintf( '%s/%s.php', $p->slug, $p->slug );
    if ( $this->is_plugin_installed( $plugin_file ) ) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
      $plugin = get_plugin_data( sprintf( '%s/%s', WP_PLUGIN_DIR, $plugin_file ) );
      return $plugin['Version'];
    }

    // if all else just return false
    return false;
  }

  /**
   * Action links for products, install etc
   */
  function get_action_link( $p ) {

    // is the product installed?
    if ( $this->product_installed( $p ) ) {

      $mode = 'installed';
      $p->installed = true;

      $version = $this->get_product_version( $p );

      if ( version_compare( $p->version, $version, '>' ) ) {
        $mode = 'update';
      }

      // check the installed product is not a symlink
      if ( $this->is_symlink_installed( $p ) ) {
        $mode = 'symlink';
      }

      // product is not installed so offer install buttons based on product type, free pro etc
    } else {

      // is the product free?
      if ( $this->is_product_free( $p ) ) {
        $mode = 'install_free';
      } else {
        // is site pro connected?
        if ( PL_Platform()->is_pro() ) {
          $mode = 'install_pro';
        } else {
          $mode = 'register_pro';
        }
      }

      if ( 'theme' == $p->install_type ) {
        if ( 'pl-framework' !== $p->slug ) {
          // check if parent is installed
          if ( ! wp_get_theme( 'pl-framework' )->exists() ) {
            $mode = 'need_parent';
          }
        }
      }
    }

    $mode = apply_filters( 'pl_actionlink_mode', $mode, $p );

    $btn  = apply_filters( 'pl_actionlink_args', $this->actionlink_engine( $mode, $p ), $p );

    return sprintf( '<a href="%s" target="%s" class="button actionlink %s">%s</a><div class="actionlink-post">%s</div>', $btn['url'], $btn['tgt'], $btn['cls'], $btn['txt'], $btn['post'] );

  }

  /**
   * Is a product free for user to download?
   */
  function is_product_free( $product ) {
    if ( ! isset( $product->categories ) ) {
      $product->categories = array();
    }
    foreach ( (array) $product->categories as $cat ) {
      if ( isset( $cat->slug ) && 'free' == $cat->slug ) {
        return true; }
    }
  }

  function actionlink_engine( $mode, $p ) {

    $defaults = array(
      'url' => '',
      'txt' => '',
      'cls' => '',
      'tgt' => '',
      'post' => '',
    );

    if ( 'install_pro' == $mode ) {

      $btn = array(
          'url'   => $this->product_file_action_url( $p ),
          'txt'   => sprintf( '<i class="pl-icon pl-icon-download"></i> %s', __( 'Install', 'pl-platform' ) ),
          'cls'   => 'button-primary',
        );

    } elseif ( 'register_pro' == $mode ) {

      $btn = array(
          'url'   => PL()->urls->purchase,
          'txt'   => sprintf( '<i class="pl-icon pl-icon-remove"></i> %s', __( 'Pro Needed', 'pl-platform' ) ),
          'cls'   => 'button-disabled',
          'post'   => 'Pro activation is required for this plugin.',
        );

    } elseif ( 'update' == $mode ) {

      $btn = array(
          'url'   => network_admin_url( 'update-core.php' ),
          'txt'   => sprintf( '<i class="pl-icon pl-icon-refresh"></i> %s v%s', __( 'Update to', 'pl-platform' ), $p->version ),
        );
    } elseif ( 'installed' == $mode ) {

      // setup defaults
      $btn = array(
          'url'   => '#',
          'txt'   => sprintf( '<i class="pl-icon pl-icon-check"></i> %s', __( 'Installed', 'pl-platform' ) ),
          'cls'   => 'button-disabled',
        );

    } elseif ( 'symlink' == $mode ) {

      // setup defaults
      $btn = array(
          'url'   => '#',
          'txt'   => sprintf( '<i class="pl-icon pl-icon-link"></i> %s', __( 'Symlink', 'pl-platform' ) ),
          'cls'   => 'button-disabled',
        );
    } elseif ( 'need_parent' == $mode ) {

      // setup defaults
      $btn = array(
          'url'   => '#',
          'txt'   => sprintf( '<i class="pl-icon pl-icon-warning"></i> %s', __( 'Needs Framework Theme', 'pl-platform' ) ),
          'cls'   => 'disabled',
        );
    } elseif ( 'install_free' == $mode ) {
      // setup defaults
      $btn = array(
          'url'   => $this->product_file_action_url( $p ),
          'txt'   => sprintf( '<i class="pl-icon pl-icon-download"></i> %s', __( 'Install', 'pl-platform' ) ),
          'cls'   => 'button-primary',
        );
    }

    return wp_parse_args( $btn, $defaults );

  }

  /**
   * Is a product installed already? Could be a theme or a plugin so check both.
   */
  function product_installed( $p ) {

    if ( $this->is_plugin_installed( sprintf( '%s/%s.php', $p->slug, $p->slug ) ) ) {

      return true;

    } elseif ( wp_get_theme( $p->slug )->exists() ) {

      return true;

    } else {
      return false;
    }

  }

  /**
   * Is the product folder a symlink?
   */
  function is_symlink_installed( $p ) {

    $folder = sprintf( '%s%ss/%s', trailingslashit( WP_CONTENT_DIR ), $p->install_type,  $p->slug );

    return ( is_link( $folder ) ) ? true : false;

  }

  /**
   * Check if a plugin is installed
   */
  function is_plugin_installed( $file ) {

    /** Object Cache This */
    if ( ! isset( $this->plugs ) ) {
      $this->plugs = PL_Platform()->functions->pl_get_plugins();
    }
    return ( isset( $this->plugs[ $file ] ) ) ? true : false;
  }

  /**
   * Create the URL for action link
   */
  function product_file_action_url( $p ) {

    /** WC formatted download info */
    if ( is_object( $p->download_data ) ) {

      $download_data  = reset( $p->download_data );

      $download_link  = ( isset( $download_data->file ) ) ? $download_data->file : 'No File';

    } /** Manually set (as in dev connect) */
    else {
      $download_link = $p->download_data;
    }

    $args = array(
      'install_type'   => $p->install_type,
      'name'           => $p->post_title,
      'download_link'  => $download_link,
      'slug'           => $p->slug,
      'installed'      => false,
    );

    if ( isset( $p->installed ) && $p->installed ) {
      $args['action']  = sprintf( 'upgrade-%s', $args['install_type'] );
      $args['installed'] = true;
    }

    if ( isset( $p->version ) ) {
      $args['version'] = $p->version; }

    return PL()->operations->install_url( $args );

  }

  function get_your_products() {

    $products = PL_Platform()->oauth->request( 'products' );

    return $products;

  }


  function get_store_listing() {

    global $wp_query;

    $request_args = $wp_query->query_vars;

    unset( $request_args['page'] );

    if ( empty( $request_args ) ) {
      $request_args['special'] = 'featured';
    }

    return PL_Platform()->oauth->request( 'store', $request_args );

  }

  /**
   * Redirect store search
   */
  function storesearch() {

    if ( isset( $_REQUEST['s'] ) ) {

      $query = array(
        'special'      => 'search',
        's'            => $_REQUEST['s'],
      );

      wp_redirect( add_query_arg( $query, PL_Platform()->url( 'extend' ) ) );
      die();
    }
  }
}

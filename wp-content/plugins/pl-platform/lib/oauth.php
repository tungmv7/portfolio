<?php
/**
 * Oauth Handler
 *
 * @class     PL_Platform_OAuth
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Platform_OAuth{

  protected $client = 'kGBbZ1mhiGsANbmYPf1jZWp5mc1APe';

  protected $secret = 'DaUemUgIbzpDacOwbELcYL8nQuzS5p';

  public $cache_key = 'pl_oauth_cache_key';

  public $data_key  = '_pl_oauth_data';

  function __construct( PL_Platform $platform ) {

    $this->platform   = $platform;

    $this->base_url   = PL()->urls->oauth;
    $this->cache_url  = PL()->urls->cdn;

    // run actions on domain add and delete
    add_action( 'pl_register_domain_add', array( $this, 'set_site_registered_filter' ), 10, 2 );
    add_action( 'pl_register_domain_del', array( $this, 'set_site_unregistered_filter' ), 10, 2 );
    add_filter( 'pl_admin_json',          array( $this, 'add_refresh_url' ) );

    /** Main OAuth return response. */
    if ( isset( $_GET['code'] ) && isset( $_GET['page'] ) && 'pl-platform-settings' == $_GET['page'] ) {
      $this->primary_authorization( $_GET['code'] );
    }

    // logout from domain.
    // Remove user data.
    if ( isset( $_REQUEST['pl_platform_logout'] ) ) {

      $this->logout();
    }

    // Register this domain
    if ( isset( $_GET['action'] ) && 'add_domain' == $_GET['action'] ) {

      $this->domain_action( 'add' );
      wp_redirect( PL_Platform()->url( 'account', array( 'refresh' => 1 ) ) );

    }

    // Refresh user data.
    if ( isset( $_GET['action'] ) && 'refresh_user' == $_GET['action'] ) {

      $this->refresh_userdata();
      wp_redirect( PL_Platform()->url( 'account', array( 'refresh' => 1 ) ) );
    }
    add_action( 'admin_init', array( $this, 'maybe_refresh_token' ) );
    add_action( 'admin_notices', array( $this, 'show_error_message' ) );
  }

  function add_refresh_url( $data ) {
    $data['refreshURL'] = PL_Platform()->url( 'account', array( array( 'action' => 'refresh_user' ) ) );
    return $data;
  }

  /**
   * Set this site as registered filter.
   */
  function set_site_registered_filter( $domain, $response ) {
    if ( is_array( $response ) && true == reset( $response ) ) {
      $this->update_site_registered( true );
    }
  }

  /**
   * Unregister this site filter.
   */
  function set_site_unregistered_filter( $domain, $response ) {
    if ( is_array( $response ) && true == reset( $response ) ) {
      $this->update_site_registered( false );
    }
  }

  /**
   * Update site status
   */
  function update_site_registered( $arg ) {
      update_site_option( 'pl_site_registered', $arg );
  }

  function json() {

      $config = array(
          'base'          => $this->base_url,
          'cache_url'     => $this->cache_url,
          'access_token'  => $this->get_user_token(),
          'rnd'           => $this->get_cache_key(),
          'ajaxurl'       => pl_ajax_url(),
          'baseURL'       => $this->platform->base_url,
        );

      wp_localize_script( $this->platform->slug, 'plplatform', $config );
  }

  function get_cache_key() {
    return PL_Platform()->config['rand'];
  }

  /**
   * Initial auth request, we fetch a code then a token.
   */
  function primary_authorization( $code, $page = 'settings' ) {

    $args = array(
      'token'      => $code,
      'redirect_uri' => esc_url( $this->platform->url( $page ) ),
    );
    $data = $this->oauth_send_post( $args );

    if ( 'error' == $data ) {
      return false; }

    $token          = $data->access_token;
    $refresh_token  = $data->refresh_token;
    $expires        = $data->expires_in;
    $rand           = PL_Platform()->config['rand'];

    $data = $this->request( 'me', array(
        'access_token'  => $token,
        'rnd'           => $rand,
    ), 'GET', true );

    if ( ! isset( $data->error ) && is_object( $data ) ) {

      $authdata = new stdClass;
      $authdata->user                 = $data;
      $authdata->user->token          = $token;
      $authdata->user->token_expire   = time() + $expires;
      $authdata->user->refresh_token  = $refresh_token;
      $this->save_cache_data( $authdata );
      wp_safe_redirect( $this->platform->url( $page, array( 'connected' => true ) ) );
      exit;
    }
    wp_safe_redirect( $this->platform->url( $page ) );
    exit;
  }

  /**
   * Get a refresh token
   */
  function refresh_token( $refresh_token ) {

    $data      = $this->get_user_data_var( 'domain_data' );
    $plugins   = count( PL_Platform_Functions::pl_get_plugins( true, true ) );

    $args = array(
      'endpoint'   => 'token',
      'token'      => $refresh_token,
      'token_name' => 'refresh_token',
      'grant_type' => 'refresh_token',
      'extra'      => array(
        'domain'     => $this->get_site_domain(),
        'user'       => $this->get_user_data_var( 'user_login' ),
        'gf'         => ( is_object( $data ) && $data->grandfathered ) ? $data->grandfathered : false,
        'pro'        => get_site_option( 'pl_site_registered', false ),
        'used'       => ( is_object( $data ) && $data->used )          ? $data->used : 0,
        'allowed'    => ( is_object( $data ) && $data->allowed )       ? $data->allowed : 0,
        'pro_plug'   => $plugins,
        'local'      => pl_is_local(),
        'version'    => PL()->version,
      ),
    );

    if ( defined( 'PLWPORG' ) ) {
      $args['extra'] = array();
    }

    $response = $this->oauth_send_post( $args );
    if ( is_object( $response ) && isset( $response->refresh_token ) ) {
      return $response;
    } else {
      // we got an error, send error back as message and defer for 1 hour.
      $data = $this->data();
      if ( isset( $data->user->token_expire ) ) {
        $data->user->token_expire = time() + 3600;
        $this->save_cache_data( $data );
      }
      return 'error';
    }

  }

  /**
   * Send a authorized POST object, used to get token and refresh token
   */
  function oauth_send_post( $args ) {

    $defaults = array(
      'grant_type'    => 'authorization_code',
      'token'         => '',
      'token_name'    => 'code',
      'redirect_uri'  => '',
      'endpoint'      => 'token',
      'extra'         => false,
    );
    $args = wp_parse_args( $args, $defaults );

    $key  = sprintf( 'Basic %s', base64_encode( $this->client . ':' . $this->secret ) );

    $data = $this->request( $args['endpoint'], array(
        'method'  => 'POST',
        'headers' => array( 'Authorization' => $key ),
        'body'    => array(
        'grant_type'        => $args['grant_type'],
        'redirect_uri'      => $args['redirect_uri'],
        $args['token_name'] => $args['token'],
        'extra'             => $args['extra'],
        ),
    ), 'POST', true );
    return $data;
  }

  function is_connected() {
    return ( false !== $this->get_cache_data() ) ? true : false;
  }

  function save_cache_data( $data ) {
    update_option( $this->data_key, json_encode( $data ) );
  }

  function get_cache_data() {

    $data = json_decode( get_option( $this->data_key ) );

    if ( ! isset( $data->user ) || ! isset( $data->user->email ) ) {
      $this->delete_cache_data();
      return false;
    } else {       return $data; }
  }

  /**
   * Get a user data var
   */
  function get_user_data_var( $arg ) {
    $authdata = $this->get_cache_data();
    if ( $authdata && isset( $authdata->user ) && isset( $authdata->user->{$arg} ) ) {
      return $authdata->user->{$arg}; } else {       return false; }
  }


  function get_user_token() {
    return $this->get_user_data_var( 'token' );
  }

  function get_refresh_token() {
    return $this->get_user_data_var( 'refresh_token' );
  }

  function get_token_expire() {
    return $this->get_user_data_var( 'token_expire' );
  }

  function delete_cache_data() {
    delete_option( $this->data_key );
  }

  /**
   * Activate site link
   */
  function domain_activate_link() {

    $args = array(
      'domain' => $this->get_site_domain(),
      'action' => 'add_domain',
      'refresh' => 1,
    );

    return PL_Platform()->url( 'account', $args );
  }

  function data() {
    return $this->get_cache_data();
  }

  function check_token_expired() {
    $token_expire = $this->get_token_expire();
    if ( $token_expire && $token_expire < time() ) {
      return true;
    } else {
      return false;
    }
  }

  function maybe_refresh_token() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
      return false;
    }
    if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'pl-platform-account', 'pl-platform-settings' ) ) ) {
      if ( $this->check_token_expired() ) {
        error_log( 'expired token' );
        $this->refresh_userdata();
      }
    }
  }

  /**
   * Main HTTP request function
   */
  function request( $endpoint = '', $args = array(), $method = 'GET', $bypass = false ) {

    // check we have a valid token
    if ( ! in_array( $endpoint, array( 'updates', 'store' ) ) && false === $bypass ) {
      $this->maybe_refresh_token();
    }
    /**
     * Set timeout to 15s
     */
    $defaults = array(
      'timeout'     => 15,
    );

    // Allow sslverify to be bypassed.
    // see: http://kb.yoast.com/article/263-update-errors-with-certificate-subject-name
    if ( defined( 'PL_SSL_NO_VERIFY' ) ) {
      $defaults['sslverify'] = false;
    }

    if ( 'POST' == $method ) {
      $data = wp_remote_request( $this->url( $endpoint ), wp_parse_args( $args, $defaults ) );
    } else {
      $data = wp_remote_get( $this->url( $endpoint, $args ), $defaults );
    }
    if ( is_wp_error( $data ) ) {
      $error_string = $data->get_error_message();
      // set a temp option so message gets displayed..
      // we cant show the message yet as we are before init.
      update_option( 'pl_oauth_error', $error_string );
      return json_encode( array() );
    }
    update_option( 'pl_oauth_error', false );
    return json_decode( wp_remote_retrieve_body( $data ) );
  }

  function show_error_message() {
    global $pl_platform;

    $error_string = get_option( 'pl_oauth_error', false );

    if ( ! $error_string ) {
      return false;
    }

    remove_action( 'admin_print_scripts', array( $pl_platform, 'refine_notices' ), 1000 );

    // show the error message.
    pl_create_notice( array(
        'title'     => __( 'Platform 5 Connection Issue', 'pl-platform' ),
        'msg'       => $error_string,
        'action'    => false,
        'icon'      => 'warning',
        'alink'     => 'https://forum.pagelines.com/topic/40415-readme-first-most-commonly-asked-questions-and-answers/',
        'atext'     => __( 'Connection FAQ', 'pl-platform' ),
    ));

    if ( false !== strpos( $error_string, 'SSL' ) && ! defined( 'OPENSSL_TLSEXT_SERVER_NAME' ) ) {
      $text = __( "The server you are hosted on uses a very old version of CURL and / or OpenSSL, the library used to connect to other servers.<br />Please see <a href='http://kb.yoast.com/article/263-update-errors-with-certificate-subject-name'>This link</a> for more information.<br />To connect to PageLines insecurly you can add this to wp-config.php, please only do this as a last resort.<br /><kbd>define( 'PL_SSL_NO_VERIFY', true );", 'pl-platform' );
      pl_create_notice( array(
          'title'     => __( 'SSL Issues', 'pl-platform' ),
          'msg'       => $text,
          'action'    => false,
          'icon'      => 'warning',
      ));
    }
  }

  /**
   * Generate a URL for request
   */
  function url( $endpoint = '', $args = array() ) {

    if ( 'store' == $endpoint && ! pl_dev_mode() ) {
      $url = $this->cache_url;
      $defaults = array(
        'store-cached' => 1,
      );
    } else {
      $url = $this->base_url;
      $defaults = array(
        'access_token' => $this->get_user_token(),
        'rnd'          => $this->get_cache_key(),
      );
    }

    if ( '' !== $endpoint ) {
      $url = sprintf( '%s/%s', $url, $endpoint );
    }

    $args = wp_parse_args( $args, $defaults );

    $url = add_query_arg( $args, $url );
    return $url;
  }

  function connect_account_link() {

    $link = $this->url( 'authorize', array(
        'response_type' => 'code',
        'redirect_uri'  => esc_url( $this->platform->url( 'settings' ) ),
        'client_id'     => $this->client,
        'rnd'           => $this->get_cache_key(),
    ));

    return $link;
  }
  /**
   * Logout the user from platform, delete user data and set site to unregistered.
   */
  function logout() {
    $this->update_site_registered( false );
    $this->delete_cache_data();
  }

  /**
   * Main is site registered function
   */
  function is_site_registered() {

    // is it localhost?
    if ( $this->is_local_and_has_pro() ) {
      $this->update_site_registered( true );
      return true;
    }

    $domain_name = $this->get_site_domain();
    $data = $this->data();

    if ( ! isset( $data->user->domain_data ) ) {
      return false; }

    $domains = (array) $data->user->domain_data->domains;

    if ( in_array( $domain_name, $domains ) ) {
      $this->update_site_registered( true );
      return true;
    } else {
      $this->update_site_registered( false );
      return false;
    }
  }

  /**
   * Can the user register this domain? Do they have a remaining slot available?
   */
  function can_register_site() {

    $data = $this->get_domains_data();
    $remaining = (int) $data->remaining;
    return ( $remaining > 0 ) ? true : false;
  }

  /**
   * Get the root domain, minus http(s) and www
   */
  function get_site_domain() {
    return urlencode( preg_replace( '/^www\./', '' , $_SERVER['SERVER_NAME'] ) );
  }

  /**
   * Get domain data from the main user data
   */
  function get_domains_data() {
    $data = $this->data();

    if ( isset( $data->user->domain_data ) ) {
      return $data->user->domain_data;
    } else {
      // user has no domain data, probably an early tester, this will stop undefined property errors.
      $domain_data = new StdClass;
      $domain_data->domains   = array();
      $domain_data->used      = 0;
      $domain_data->allowed   = 0;
      $domain_data->remaining = 0;
      return $domain_data;
    }
  }

  function get_domain_data_format() {

    $d = $this->get_domains_data();

    return sprintf( '<span class="ddata">(%s<span class="divide">/</span>%s)</span>', $d->remaining, $d->allowed );

  }

  function get_connected_account() {

    $d = $this->get_cache_data();

    return sprintf( '<span class="ddata">(%s)</span>', $d->user->display_name );

  }

  /**
   * Check domain data to see if user is grandfathered in
   */
  function is_grandfathered() {
    $data = $this->data();
    if ( isset( $data->user->domain_data->grandfathered ) && true == $data->user->domain_data->grandfathered ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Domain actions add/del
   */
  function domain_action( $action ) {
    $args = array(
      'domain' => $this->get_site_domain(),
      'action'  => $action,
    );
    $response = $this->request( 'domain', $args );
    do_action( 'pl_register_domain_' . $action, $this->get_site_domain(), $response );
    $this->refresh_userdata();
  }

  /**
   * helper function to refresh users data from the oauth server.
   */
  function refresh_userdata() {

    $rand = PL_Platform()->config['rand'];

    $refresh_token = $this->get_refresh_token();

    if ( false == $refresh_token ) {
      $this->logout();
      return;
    }

    $token_data = $this->refresh_token( $this->get_refresh_token() );

    // fetch error..do not continue, do not write any data.
    if ( 'error' == $token_data ) {
      return;
    }

    if ( ! isset( $token_data->access_token ) ) {
      $this->logout();
      return;
    }

    $data = $this->request( 'me', array(
        'access_token'  => $token_data->access_token,
        'rnd'           => $rand,
    ), 'GET', true );

    if ( ! isset( $data->error ) && is_object( $data ) ) {

      $authdata = new stdClass;
      $authdata->user                 = $data;
      $authdata->user->token          = $token_data->access_token;
      $authdata->user->token_expire   = time() + $token_data->expires_in;
      $authdata->user->refresh_token  = $token_data->refresh_token;
      $this->save_cache_data( $authdata );
      update_user_meta( wp_get_current_user()->ID, '_card_fav', (array) $data->fav );
      // checking if the user has a valid reason to have site activated.
      // if domains allowed = 0 then the users sub has probably expired.
      if ( ! isset( $data->domain_data->allowed ) || 0 == $data->domain_data->allowed ) {
        $this->update_site_registered( false );
      } else {

      }
    }
  }
  /**
   * is this localhost?
   * Must have at least one active subscription and be running local
   * $domain_data->allowed must be more than 0
   */
  function is_local_and_has_pro() {

    $domain_data = $this->get_domains_data();
    return ( $domain_data->allowed > 0 && pl_is_local() ) ? true : false;
  }
}

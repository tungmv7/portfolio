<?php
/**
 * Some basic low level functions if platform is not installed
 *
 * @class     PL_Framework_Ops
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
class PL_Framework_Ops {

  function __construct() {
    add_filter( 'plugins_api',     array( $this, 'installation_api_intercept' ), 99999, 3 );
    add_action( 'admin_notices',   array( $this, 'get_plugin_notice'  ), 100);
    if( ! is_admin() ) {
      $this->frontend_messages();
    }
  }
  
  /**
   * Show a frontend message if platfoem is now installed.
   * If the user is an admin, then show a link to install/activate the plugin
   * Else show a logged out user the usual WP maintenance message. 
   */
  function frontend_messages() {
    
    global $pagenow;

    if( is_user_logged_in() && current_user_can( 'edit_themes' ) ) {
      ob_start();
      $this->get_plugin_notice();
      wp_die( ob_get_clean() );
    } elseif( 'wp-login.php' != $pagenow ) {
      // taken directly from WP core, there is a function but it looks for a .maintenance file to work :/
      wp_load_translations_early();
    	$protocol = wp_get_server_protocol();
    	header( "$protocol 503 Service Unavailable", true, 503 );
    	header( 'Content-Type: text/html; charset=utf-8' );
    	header( 'Retry-After: 600' );
?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml"<?php if ( is_rtl() ) echo ' dir="rtl"'; ?>>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php _e( 'Maintenance', 'pl-framework' ); ?></title>
	</head>
	<body>
		<h1><?php _e( 'Briefly unavailable for scheduled maintenance. Check back in a minute.', 'pl-framework' ); ?></h1>
	</body>
	</html>
<?php
	   die();
    }
  }
  
  /**
   * Create the notice
   */
  function get_plugin_notice() {
    
    // we need the plugin code when on frontend of site.
    if( ! function_exists( 'get_plugins' ) ) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $plugins = get_plugins();
    // activate or install link?
    if( ! isset( $plugins['pl-platform/pl-platform.php'] ) ) {
      $link = sprintf( '<a class="button button-primary" href="%s">%s &rarr;</a>', $this->platform_install_link(), __( 'Install Platform 5', 'pl-framework' ) );
    } else {
      $link = sprintf( '<a class="button button-primary" href="%s">%s &rarr;</a>', $this->platform_activate_link(), __( 'Activate Platform 5', 'pl-framework' ) );
    }

    $this->pl_create_notice( array(
      'title'   => __( 'You need PageLines Platform 5', 'pl-framework' ),
      'msg'     => __( '<br />PageLines Framework needs the PageLines Platform plugin.<br />Please install and activate it now.<br />', 'pl-framework' ), 
      'action'  => $link, 
    )); 
  }
  
  /**
   * Generate an activate link with correct nonces, works front and backend.
   */
  function platform_activate_link() {
    
    $activate_url = network_admin_url( 'plugins.php?action=activate&plugin=' . urlencode( 'pl-platform/pl-platform.php' ) . '&_wpnonce=' . urlencode( wp_create_nonce( 'activate-plugin_pl-platform/pl-platform.php' ) ) );
    return $activate_url;
  }
  
  /**
   * If the pl_install var is set, then we know its a PL product.
   * Intercept the APIs with a hook and replace the product with our information including
   * download url, title, version, etc..
   */
  function installation_api_intercept( $api, $action, $args ) {

    if( ! isset( $_GET['pl'] ) )
      return $api;

    $api                = new stdClass();
    $api->name          = $_GET['name'];
    $api->version       = $_GET['version'];
    $api->download_link = base64_decode( $_GET['download_link'] );
    $api->pl            = $_GET['pl'];

    return $api;

  }
  /**
   * Build the install link.
   */
  function install_url( $args ){

    if( ! is_string($args['download_link']) ){
      return 'No Download Link';
    }

    $plugin_file = sprintf( '%s/%s.php', $args['slug'], $args['slug'] );

    $defaults = array(
        'version' => '(Latest Version)',
        'pl'      => 1,
        'plugin'  => urlencode( $args['slug'] ),
        'action'  => sprintf('install-%s', $args['install_type'] )
      );

    $args = wp_parse_args( $args, $defaults );


    $args['download_link'] = base64_encode( $args['download_link'] );

    $base_url = add_query_arg( $args, network_admin_url( 'update.php' ) );

    // upgrades use a different nonce setup
    if( isset( $args['installed'] ) && $args['installed'] ) {
      if( 'plugin' == $args['install_type'] ) {
        $file = $plugin_file;
      } else {
        $file = $args['slug'];
      }
      // taken directly from WP so this works.
      $admin_url    = sprintf( 'update.php?action=upgrade-%s&%s=%s', $args['install_type'], $args['install_type'], $file );
      $nonce        = sprintf( 'upgrade-%s_%s', $args['install_type'], $file );
      $url          = wp_nonce_url( network_admin_url( $admin_url ), $nonce );
    } else {
      $url          = wp_nonce_url( $base_url, sprintf( '%s_%s', $args['action'], $args['slug'] ) );
    }
    return $url;
  }
  
  function platform_install_link() {
    $args = array(
      'install_type'   => 'plugin',
      'name'           => 'PageLines Platform 5',
      'download_link'  => $this->get_build_link( 'pl-platform' ),
      'slug'           => 'pl-platform',
      'installed'      => false,
      'pl'             => 'none'
    );
    return $this->install_url( $args );
  }
  

  function get_build_link( $slug ){
    return sprintf( 'http://deploy.pagelines.io/build/%s.zip', $slug );
  }
  
  /**
   * Simplified create notice taken from platform core.
   */
  function pl_create_notice( $args ) {

    // if multisite install, if we are network super admin do not show messages as we cant install anything.
    if( ! is_super_admin() ) {
      return false;
    }

    $args = wp_parse_args( $args, array(
        'title'   => __( 'Notice', 'pl-framework' ),
        'msg'     => false,
        'action'  => false
      ));

      printf( '<div id="message" class="updated pl-notice"><p><strong>%s</strong>%s</p><p>%s</p></div>', $args['title'] ,$args['msg'], $args['action'] );

  }
}

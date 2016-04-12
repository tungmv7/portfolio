<?php
/**
 * Globals used throughout Platform
 *
 * @class     PL_Globals
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Globals {

  public $version;

  public $name = 'PageLines Platform';

  public $name_short = 'PageLines';

  public $db_version = '1.12';

  protected static $_instance = null;

  /**
   * Main PageLines Instance
   *
   * Ensures only one instance of PageLines is loaded or can be loaded.
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  function __construct() {

    $this->version = $this->get_version();
    $this->set_urls();

    // reference with PL()->operations
    $this->operations = new PL_Platform_Operations( $this );
  }

  /**
   * Make urls available example: PL()->urls->forum
   */
  public function set_urls() {

    $home = ( isset( $_GET['post'] ) ) ? get_permalink( $_GET['post'] ) : home_url();

    $editor_url = ( is_admin() ) ? $home : pl_get_current_url();

    $url_array = array(
        'pagelines'   => 'http://www.pagelines.com',
        'platform'    => 'http://www.pagelines.com/platform/',
        'purchase'    => 'http://www.pagelines.com/purchase/',
        'pro'         => 'http://www.pagelines.com/pro/',
        'oauth'       => 'https://www.pagelines.com/oauth',
        'cdn'         => 'http://wpecdn.pagelines.com',
        'docs'        => 'http://www.pagelines.com/resources',
        'forum'       => 'https://forum.pagelines.com',
        'support'     => 'https://www.pagelines.com/support/',
        'my_account'  => 'https://www.pagelines.com/my-account',
        'quickstart'  => 'https://www.youtube.com/watch?v=1p7hEy9h06g',
        'editor'      => add_query_arg( array( 'pl_edit' => 'on', 'pl_start' => 'yes' ), $editor_url ),
        'deactivate'  => add_query_arg( array( 'pl_edit' => 'off' ), $editor_url ),
      );

    $this->urls = new stdclass;

    foreach ( $url_array as $key => $url ) {
      $this->urls->$key = $url;
    }

  }

  /**
   * We need the plugin version, it is used to set versions in styles/js and cant be hard coded
   */
  private function get_version() {

    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $plugin   = get_plugin_data( PL_Platform()->config['plugin'] );
    $version  = $plugin['Version'];
    return $version;
  }
}
/*
 * TODO Going to use this to replace globals.
 *
 */
function PL() {
  return PL_Globals::instance();
}

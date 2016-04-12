<?php
/**
 * Front End JSON Model Information
 *
 * This class renders the information for front end editing in a json blob that is output in the footer.
 *
 * @class     PL_JSON
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_JSON {

  /**
   * Constructor for the class. Loads globals, options and hooks in the init method.
   *
   * @access public
   * @return void
   */
  function __construct() {

    $this->additions = array();

    add_action( 'pl_json_data', array( $this, 'load_info' ), 1 );

    add_action( 'pl_json_data', array( $this, 'render' ), 99 );

    add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 1000 );

  }

  function scripts() {

    pl_script( 'pl-site',     pl_framework_url( 'js' ) . '/site.js', array( 'jquery' ) );

    pl_script( 'pl-common',   pl_framework_url( 'js' ) . '/common.js' );

    pl_style( 'pl-components',  pl_framework_url( 'css' ) . '/components.css' );

    //  wp_enqueue_media();

  }

  function load_info() {

    global $pl_page;
    $this->page = $pl_page;

    global $maps_data_handler;
    $this->map = $maps_data_handler;

    global $plfactory;
    $this->factory = $plfactory;

    global $plselector;
    $this->selector = $plselector;

  }

  /**
   * Get all json for output
   * @return string nested json string
   */
  function get_json() {
    $code = $this->encode( $this->set_data() );
    return sprintf( 'PLData = %s%s', $code, "\n" );
  }

  function render() {

    ob_start();
    ?><script>!function($){ <?php echo $this->get_json(); ?>}(window.jQuery);</script>
<?php
    echo apply_filters( 'pl_data_blob', ob_get_clean() );

  }

  /**
   * Adds all data to a single array.
   */
  function set_data() {

    $array['config']      = $this->set_config_data();

    $array['urls']        = pl_get_system_urls();

    $array['modelData']   = $this->factory->get_view_model();

    $array['extraData']   = array();

    return apply_filters( 'pl_site_json', $array );

  }

  function set_config_value( $variable, $value ) {

    $this->additions[ $variable ] = $value;

  }

  function get_query() {

    global $wp_query;

    return $wp_query->query;

  }

  function get_page_data() {

    $data = array();

    $data['type']   = $this->page->type;

    $data['id']   = $this->page->id;

    return $data;
  }

  /**
   * TODO not used?
   */
  function prep_localized_script_data( $script_data ) {

    $prepped_script_data = array();

    foreach ( $script_data as $key => $data ) {

      $prepped_script_data[ $key ] = $this->prep_data( $data );

    }

    return $prepped_script_data;

  }

  /**
   * TODO Not used?
   */
  function prep_data( $data ) {

    if ( is_array( $data ) || is_object( $data ) ) {
      $data = $this->encode( $data ); } elseif ( is_int( $data ) ) {
      $data = $data; } elseif ( is_bool( $data ) ) {
        $data = ($data) ? 'true' : 'false'; } else {       $data = sprintf( "'%s'", $data ); }

      return $data;
  }

  function do_workarea_json() {

    wp_localize_script( 'pl-editing', 'PLWorkarea', $this->workarea_config() );

  }

  /**
   * Gets all config data in an array
   */
  function set_config_data() {

    global $wp;
    global $wp_query;
    global $pl_dynamic_templates;
    //print_r( $GLOBALS['wp_query']);

    $a['query']       = $GLOBALS['wp_query']->query;

    // ID of the currently active user
    $a['userID']      = wp_get_current_user()->ID;

    // URL of the current page
    $a['currentURL']  = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );

    /** Should we save styles? set to 1 on change */
    $a['saveStyles']  = 0;

    /** Render mode is set on the fly as template is loaded */

    $a['tplRender']   = $pl_dynamic_templates;

    // Currently active page template
    $a['tplActive']   = $this->page->template();

    // The current template scope mode
    $a['tplMode']     = $this->page->template_mode();

    $a['editID']      = pl_edit_id();
    $a['editslug']    = pl_edit_slug();

    // The ID for the current page
    $a['pageID']      = $this->page->id;
    $a['pageslug']    = $this->page->meta_slug;

    // The ID for the current type
    $a['typeID']      = $this->page->typeid;
    $a['typeslug']    = $this->page->type_slug;

    // The ID for the current type
    $a['termID']      = $this->page->termid;
    $a['termslug']    = $this->page->term_slug;

    // The slug ID of the current type (?)
    $a['typename']    = $this->page->type;

    // Page Name and Slug Format
    $a['currentPageName']   = esc_attr( $this->page->get_current_page_name() );
    $a['currentPageSlug']   = $this->page->get_current_page_slug();
    $a['currentTaxonomy']   = $this->page->get_current_taxonomy();

    $a['themename']   = wp_get_theme()->name;

    // A cache key for various settings
    $a['CacheKey']    = pl_cache_key();

    // Is the user on localhost
    $a['LocalHost']   = pl_is_local();

    // Is developer mode activated?
    $a['debug']       = ( pl_dev_mode() ) ? 'true' : 'false';

    // Is this page a WP special page: e.g. multi-post, 404, etc..
    $a['isSpecial']   = $this->page->is_special();

    $a['needsave']    = ( isset( $_GET['needsave'] ) ) ? 1 : 0;

    $a['tplMap']      = $this->map->map;

    $a['templateOpts']  = pl_get_template_settings();

    $a['nonce'] = wp_create_nonce( 'pl-ajax-nonce' );
    return array_merge( $a, $this->additions );

  }

  /**
   * Encode arrays and php objects for JSON
   * @param  object or array $object_or_array a PHP object or aray
   * @return json string
   */
  function encode( $object_or_array ) {

    return json_encode( pl_convert_arrays_to_objects( $object_or_array ) );

  }
}

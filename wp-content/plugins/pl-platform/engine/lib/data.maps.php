<?php
/**
 * Map Data Class
 *
 * @class     PL_Map_Data
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Map_Data{

  private $table_slug = 'pl_data_maps';

  private $version_slug = 'pl_maps_table_version';

  private $version = 1.14;

  function __construct() {

    global $wpdb;
    $this->wpdb = $wpdb;

    $this->table_name = $wpdb->prefix . $this->table_slug;

    $this->installed_db_version = get_option( $this->version_slug );

    // Container for any settings added dynamically
    $this->map_settings = array();

    // check if install needed, if so, run install routine
    if ( $this->installed_db_version != $this->version ) {
      $this->install_table();
    }

    $this->map = $this->get_map_data();

    /** Any settings added via map can be added to data after its pulled from DB */
    add_filter( 'pl_section_data', array( $this, 'add_template_data' ) );
  }

  function install_table() {

    $sql = "CREATE TABLE $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            uid VARCHAR(50) NOT NULL,
            live LONGTEXT NOT NULL,
            UNIQUE KEY id (id),
            UNIQUE KEY uid (uid)
          );";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta( $sql );

    update_option( $this->version_slug, $this->version );
  }

  function save_map( $map, $editslug ) {

    $result = '';

    if ( is_array( $map ) ) {

      foreach ( $map as $region => $m ) {

        if ( 'template' == $region  ) {
          $uid = $editslug;
        } else {
          $uid = $region;
        }

        $this->update_or_insert( $uid, $m );

        $result .= '_' . $uid;

      }
    }

    return $result;
  }

  function update_or_insert( $uid, $data ) {

    $data = json_encode( $data );

    $query = $this->wpdb->prepare( "INSERT INTO $this->table_name (uid, live)
                                    VALUES ( %s, %s)
                                    ON DUPLICATE KEY UPDATE
                                    live = VALUES(live)", $uid, $data);

    $result = $this->wpdb->query( $query );

    return $result;
  }

  function get_map_data() {

    $uids = array();

    foreach ( pl_site_regions() as $r ) {

      if ( 'template' == $r ) {
        $uids[ $r ] = pl_edit_slug();
      } else {
        $uids[ $r ] = $r;
      }
    }

    return $this->get_data( $uids );
  }

  function get_data( $uids ) {

    $imploded_uids = join( "','", $uids );

    $query = sprintf("SELECT uid, live
                      FROM $this->table_name
                      WHERE uid
                      IN ( '%s' )", $imploded_uids );

    $rows = $this->wpdb->get_results( $query );

    $config = $this->configure_data( $uids, $rows );

    return $config;
  }

  function configure_data( $uids, $rows ) {

    $map_data = array();

    foreach ( $uids as $region => $uid ) {

      foreach ( $rows as $m ) {

        if ( $m->uid == $uid ) {

          $map_data[ $region ] = pl_unserialize_or_decode( $m->live );

        }
      }
    }

    /** Properly ordered map */
    $map_for_output = array();

    foreach ( pl_site_regions() as $region ) {

      if ( ! isset( $map_data[ $region ] ) ) {

        $map_data[ $region ] = $this->default_region( $region );

      }
      $map_for_output[ $region ] = $map_data[ $region ];
    }

    /** UPGRADE DATA */
    $map_for_output = apply_filters( 'pl_map_raw', $map_for_output );

    /** Add new sections if needed */
    $map_for_output = $this->add_new_sections( $map_for_output );

    /**
     * Check and Set Data
     * MUST COME LAST!
     */
    $map_for_output = $this->replace_recursive( $map_for_output );

    return $map_for_output;

  }

  /**
   * Recursive function that iterates over a map and performs upgrades and substitutions.
   */
  function replace_recursive( $container ) {

    foreach ( $container as $index => &$item ) {

      //$item = $this->replace_custom_sections( $item );

      $item = $this->replace_data( $index, $item );

      /** Index can't be content, as we skip levels in array format */
      if ( isset( $item['content'] ) && is_array( $item['content'] ) ) {
        $item['content'] = $this->replace_recursive( $item['content'] );
      }
    }
    unset( $item );
    return $container;
  }

  function replace_data( $index, $item ) {

    if ( ! isset( $item['object'] ) || '' == $item['object'] ) {

      $item['object'] = 'PL_Container';
    }

    $item['object'] = apply_filters( 'pl_map_object', $item['object'] );

    if ( ! isset( $item['content'] ) || ! is_array( $item['content'] ) ) {
      $item['content'] = array();
    }

    /** If template, manually assign UID as page meta ID, else create new section if empty */
    if ( ! isset( $item['clone'] ) ) {

      /** Create new section ID and set default settings */
      $item['clone'] = $this->setup_new_section_from_map( $item );

    }

    if ( 'template' == $item['object'] ) {
        $item['clone'] = $item['object'];
    }

    $item = apply_filters( 'pl_map_item', $item );
    return $item;
  }

  /**
   * If a new section is being added from a default template, we need to set it up
   * These allow for settings to be included the map array. When we create the clone ID, lets set the default
   * table entry for the settings to default.
   *
   * This allows us to set default settings from the map itself.
   *
   * Query is rerun inside the factory and will get this data based on Clone ID
   */
  function setup_new_section_from_map( $map_meta ) {

    global $pl_sections_data;

    $clone_id = pl_create_clone_id();

    if ( isset( $map_meta['settings'] ) ) {
      $pl_sections_data->create_items( array( $clone_id => $map_meta['settings'] ) );
    }
    return $clone_id;
  }


  /**
   * Add new sections via $_GET in iframe if needed
   */
  function add_new_sections( $map ) {

    /** Security Precaution since we're using $_GET */
    if ( ( isset( $_GET['addSections'] ) || isset( $_GET['loadMap'] ) ) && ! current_user_can( 'edit_theme_options' ) ) {
      return $map;
    }

    /** Get Add new data in GET */
    if ( isset( $_GET['addSections'] ) ) {

      $add = json_decode( stripslashes( urldecode( $_GET['addSections'] ) ), true );

      if ( is_array( $add ) ) {

        $formatted_add = array();
        foreach ( $add as $object ) {

          $formatted_add[] = array(
            'object' => $object,
          );
        }

        // make sure index exists..
        if ( ! isset( $map['template']['content'] ) ) {
          $map['template']['content'] = array();
        }

        $map['template']['content'] = array_merge( $formatted_add, $map['template']['content'] );
      }
    }

    /** Get Add new data in GET */
    if ( isset( $_GET['loadMap'] ) ) {

      $add = json_decode( stripslashes_deep( urldecode( $_GET['loadMap'] ) ), true );

      if ( is_array( $add ) ) {

        // make sure index exists..
        if ( ! isset( $map['template']['content'] ) ) {
          $map['template']['content'] = array();
        }

        // echo '<pre>';
        // print_r( $_GET['loadMap'] );
        // echo '</pre>';

        /** Only do this if you dont want them linked */
        $configured = $this->configure_template( $add );

        $map['template']['content'] = array_merge( array( $configured ), $map['template']['content'] );

      }
    }

    return $map;
  }

  function add_template_data( $data ) {

    $data = array_merge( $this->map_settings, $data );

    return $data;
  }

  function configure_template( $map ) {

    $oldid = $map['clone'];

    $newid = pl_create_clone_id();

    if ( 'template' != $oldid ) {

      $map['clone'] = $newid;

      if ( isset( $map['settings'] ) ) {
        $this->map_settings[ $newid ] = $map['settings'];
      }
    }

    if ( isset( $map['content'] ) && is_array( $map['content'] ) && ! empty( $map['content'] ) ) {

      foreach ( $map['content'] as $key => $item ) {

         $map['content'][ $key ] = $this->configure_template( $item );

      }
    }

    return $map;
  }

  function default_region( $region ) {

    $d = array();

    if ( 'header' == $region ) {
      $d = array();
    } elseif ( 'footer' == $region ) {
      $d = array();
    } elseif ( 'template' == $region ) {

      $d = array(
        array(
          'object'  => 'PL_Content',
        ),
      );
    }

    $config = array(
      'content'   => $d,
      'clone'   => $region,
      'object'  => $region,
    );

    return $config;
  }

  /**
   * Used by exporter to get all map data as an array.
   */
  function dump_map() {
    $query = sprintf( "SELECT * FROM $this->table_name;" );
    return $this->wpdb->get_results( $query );
  }
}

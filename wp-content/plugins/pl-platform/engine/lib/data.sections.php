<?php
/**
 * Sections Data Class
 *
 * @class     PL_Section_Data
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Section_Data{


  private $table_slug = 'pl_data_sections';

  private $version_slug = 'pl_sections_table_version';

  private $version = 5.1;

  function __construct() {

    global $wpdb;
    $this->wpdb = $wpdb;

    $this->table_name = $wpdb->prefix . $this->table_slug;

    $this->installed_db_version = get_option( $this->version_slug );

    // check if install needed, if so, run install routine
    if ( $this->installed_db_version != $this->version ) {
      $this->install_table();
    }

  }

  function install_table() {

    $sql = "CREATE TABLE $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            uid VARCHAR(50) NOT NULL,
            draft LONGTEXT NOT NULL,
            live LONGTEXT NOT NULL,
            json LONGTEXT NOT NULL,
            UNIQUE KEY id (id),
            UNIQUE KEY uid (uid)
          );";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta( $sql );

    // db version 5.1 new col json created, migrate data to it.
    if ( version_compare( $this->installed_db_version, '5.1', '<' ) ) {
      // run update
      $result = $this->wpdb->get_results( "UPDATE $this->table_name set json = live" );
    }

    update_option( $this->version_slug, $this->version );

  }

  function update_or_insert( $uid, $data ) {

    $data = json_encode( $data );

    $query = $this->wpdb->prepare( "INSERT INTO $this->table_name (uid, json)
                                    VALUES ( %s, %s)
                                    ON DUPLICATE KEY UPDATE
                                    json = VALUES(json)", $uid, $data);

    $result = $this->wpdb->query( $query );

    return $result;
  }

  function create_items( $items ) {

    if ( ! empty( $items ) ) {
      foreach ( $items as $uid => $dat ) {

        $result = array();
        $query = $this->wpdb->prepare( "INSERT INTO $this->table_name (uid, json)
                                        VALUES ( %s, %s )
                                        ON DUPLICATE KEY
                                        UPDATE json = VALUES(json);", $uid, json_encode( $dat ), json_encode( $dat ));

        $result[] = $this->wpdb->query( $query );
      }
    } else {       $result = 'No items sent to create.'; }

    return $result;

  }

  function delete_items( $items ) {

    $imploded_uids = join( "','", $items );

    $query = $this->wpdb->prepare( "DELETE from $this->table_name
                                    Where uid
                                    IN ( %s )", $imploded_uids );

    $result = $this->wpdb->query( $query );

    return $result;

  }

  function update_section_settings( $uid, $data ) {

    $query = $this->wpdb->prepare( "SELECT *
                                    FROM $this->table_name
                                    WHERE uid = %s", $uid );

    $result = $this->wpdb->get_results( $query );

    // no result returns empty array

    if ( ! empty( $result ) ) {

      foreach ( $result as $section ) {

        $live_settings = pl_unserialize_or_decode( $section->json );

        $live_settings = wp_parse_args( $data, $live_settings );

        $new_live_settings = json_encode( $live_settings );

        $query = $this->wpdb->prepare( "UPDATE $this->table_name SET json = %s WHERE uid = %s", $new_live_settings, $uid );

        $this->wpdb->query( $query );

        $query = $this->wpdb->prepare( "SELECT * FROM $this->table_name WHERE uid = %s", $uid );

        $result = $this->wpdb->get_results( $query );
      }
    } else {
      $result = $this->update_or_insert( $uid, $data );
    }

    return $result;

  }


  function get_section_data( $uids ) {

    $imploded_uids = join( "','", $uids );

    $query = sprintf("SELECT uid, json
                      FROM $this->table_name
                      WHERE uid
                      IN ( '%s' )", $imploded_uids );

    $rows = $this->wpdb->get_results( $query );

    $config = $this->configure_section_data( $uids, $rows );

    return $config;
  }

  function configure_section_data( $uids, $rows ) {

    $config = array();
    $rows_added = false;

    foreach ( $uids as $uid ) {
      $num_rows = 0;
      foreach ( $rows as $set ) {

        if ( $set->uid == $uid ) {

          $num_rows++;

          $config[ $uid ] = pl_unserialize_or_decode( $set->json );
        }
      }
    }

    // Remove empties, can be saved by default, etc..
    foreach ( $config as $i => $model ) {

      if ( is_array( $model ) ) {

        foreach ( $model as $key => $val ) {

          if ( '' === $val || null === $val ) {
            unset( $config[ $i ][ $key ] ); }
        }
      }
    }
    return $config;
  }

  /**
   * Used by exporter to get all sections data as an array.
   */
  function dump_sections() {
    $query = sprintf( "SELECT * FROM $this->table_name;" );
    return $this->wpdb->get_results( $query );
  }
}

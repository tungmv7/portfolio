<?php
/**
 * Page Class
 *
 * Assigns configuration and handling information for the current page.
 *
 * @class     PL_Page
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Page {

  var $special_base = 70000000;
  var $special_base_archive = 60000000;
  var $opt_type_info = 'pl-type-info';

  function __construct( $args = array() ) {

    $args = wp_parse_args( $args, $this->defaults() );

    $mode = $args['mode'];

    add_filter( 'parse_request', array( $this, 'check_for_type' ) );

    if ( 'ajax' == $mode ) {

      $this->id = $args['pageID'];

      $this->typeid = $args['typeID'];

    } else {

      $slug             = $this->get_current_page_slug();

      $page_info        = $this->get_page_slug_info( $slug );

      /** ID for the type of page */
      $this->typeid     = $this->get_index( $slug, 'type' );

      /** Most Specific ID for Page */
      $this->id         = $this->get_index( $slug, 'page' );

      /** If a category or taxonomy, ID of the taxonomy archive */
      $this->termid     = $this->get_index( $slug, 'term' );

      $this->type_slug  = $this->get_index( $slug, 'type', true );
      $this->meta_slug  = $this->get_index( $slug, 'page', true );
      $this->term_slug  = $this->get_index( $slug, 'term', true );

      $this->type       = $page_info['type'];
      $this->term       = $page_info['term'];

      $this->template   = $this->template();

    }

  }

  /**
   * Setup pl_404 variable
   */
  function check_for_type( $wp ) {

    global $pl_404;

    if ( isset( $wp->query_vars['pagename'] ) && false !== strpos( $wp->query_vars['pagename'], 'members' ) ) {

      $pl_404 = false;
    } else {
      $pl_404 = true;
    }
  }

  function defaults() {
    $d = array(
      'mode'    => 'standard',
      'pageID'  => '',
      'typeID'  => '',
    );
    return $d;
  }

  /**
   * Get the editing scope of the current page.
   * Either local for specific ID or type wide for entire category default.
   */
  function template_mode() {

    if ( isset( $_GET['tplScope'] ) ) {
      $mode = $_GET['tplScope'];
    } /** Set template scope on most specific ID */
    elseif ( get_post_meta( $this->id, 'pl_template_mode', true ) ) {

      $mode = get_post_meta( $this->id, 'pl_template_mode', true );

    } /**
     * If its a taxonomy archive, set to all of term mode, instead of individual taxonomy category
     * If its a page template, start by using the settings for that specific template
     */
    elseif ( 'taxonomy' == $this->type || ( 'page' == $this->type && '' != $this->term_slug && ! is_numeric( $this->term_slug ) ) ) {

      $mode = 'term';
    } elseif ( 'page' == $this->type ||  'post_type' == $this->type ) {

      $mode = 'local';

    } else {

      $mode = 'type';

    }

    if ( 'meta' == $mode ) {
      $mode = 'local'; }

    return $mode;

  }

  function get_edit_id() {

    $mode = $this->template_mode();

    if ( 'type' == $mode ) {
      $id = $this->typeid;
    } elseif ( 'term' == $mode ) {
      $id = $this->termid;
    } else {
      $id = $this->id;
    }

    return $id;

  }

  function get_edit_slug() {

    $mode = $this->template_mode();

    if ( 'type' == $mode ) {
      $id = $this->type_slug;
    } elseif ( 'term' == $mode ) {
      $id = $this->term_slug;
    } else {
      $id = $this->meta_slug;
    }

    return $id;

  }


  function template() {

    global $pl_custom_template;

    if ( isset( $pl_custom_template ) && isset( $pl_custom_template['key'] ) ) {
      return $pl_custom_template['key']; } else {       return ''; }

  }

  function meta_id() {

    if (  'local' == $this->mode ) {
      return $this->id;
    } else {
        return $this->typeid;
    }

  }


  function lookup_array() {
    $lookup_array = array(
      'blog',
      'category',
      'search',
      'post_tag',
      'author',
      'date',
      'page',
      'post',
      'four04',
    );

    return $lookup_array;
  }

  function special_index_lookup( $typeid = '', $metaid = '', $termid = '' ) {

    $typeid = ( ! empty( $typeid ) ) ? $typeid : 0;

    $index = array_search( $typeid, $this->lookup_array() );

    if ( ! $index ) {
      $index = pl_generate_number_from_string( $typeid . $metaid . $termid );
    }

    $base = ( empty( $metaid ) ) ? $this->special_base : $this->special_base_archive;

    return $base + $index;

  }

  /**
   * Determines if the current page supports meta ID by default or if one needs to be assigned.
   */
  function is_special() {

    if ( is_404() || is_home() || is_search() || is_archive() ) {
      return true; } else {       return false; }

  }

  function is_posts_page() {

    if ( is_home() || is_search() || is_archive() || is_category() ) {
      return true; } else {       return false; }

  }

  function get_current_page_slug() {

    $current_page = array();

    $queried_object = get_queried_object();

    if ( is_singular() ) {

      $post = $queried_object;
      $post_type = get_post_type_object( $post->post_type );

      $current_page[] = 'single';

      if ( $post_type->name ) {
        $current_page[] = 'single__' . $post_type->name; }

      $posts = array( $post->ID );

      /** I dont know why we origially looked at parent pages.
        * Assuming its post type related
       */
      while ( 0 != $post->post_parent ) {

        $post = get_post( $post->post_parent );
        $posts[] = $post->ID;

      }

      foreach ( array_reverse( $posts ) as $post_id ) {
        if ( $post_type->name && $post_id ) {
          $current_page[] = sprintf( 'single__%s__%s', $post_type->name, $post_id );

          if ( 'page' == $post_type->name ) {

            $template_name = str_replace( '.php', '', get_page_template_slug( $post_id ) );

            if ( '' != $template_name ) {
              $current_page[] = sprintf( 'single__%s__%s__%s', $post_type->name, $post_id, $template_name );
            }
          }
        }
      }
    } elseif ( is_home() || is_archive() || is_search() ) {

      $current_page[] = 'archive';

      if ( is_home() ) {

        $current_page[] = 'archive__post__blog';

      } elseif ( is_date() ) {

        $current_page[] = 'archive__post__date';

      } elseif ( is_author() ) {

        $current_page[] = 'archive__post__author';
        if ( isset( $queried_object->ID ) ) {
          $current_page[] = 'archive__post__author__' . $queried_object->ID; }
      } elseif ( is_category() ) {

        $category = $queried_object;
        $ancestor_categories = array();

        $current_page[] = 'archive__post__category';

        /* Ancestor categories */
        while ( 0 != $category->category_parent ) {
          $category = get_category( $category->category_parent );
          $ancestor_categories[] = $category->term_id;
        }

        foreach ( array_reverse( $ancestor_categories ) as $ancestor_category_id ) {
          $current_page[] = 'archive__post__category__' . $ancestor_category_id; }

        /* Original queried category */
        $current_page[] = 'archive__post__category__' . $queried_object->term_id;

      } elseif ( is_search() ) {

        $current_page[] = 'archive__post__search';

        if ( isset( $_GET['post_type'] ) ) {
          $current_page[] = 'archive__' . $_GET['post_type'] . '__search-'. $_GET['post_type'];
        }
      } elseif ( is_tag() ) {

        $current_page[] = 'archive__post__post_tag';
        $current_page[] = 'archive__post__post_tag__' . $queried_object->term_id;

      } elseif ( is_tax() ) {

        $base = ( 'post_format' == $queried_object->taxonomy ) ? 'archive__post' : 'archive__' . get_post_type();

        $current_page[] = $base;
        $current_page[] = $base. '__' . $queried_object->taxonomy;
        $current_page[] = $base. '__' . $queried_object->taxonomy . '__' . $queried_object->term_id;

      } elseif ( is_post_type_archive() ) {

        $current_page[] = 'archive__' . $queried_object->name . '__archive';

      }
    } elseif ( is_404() ) {

      $current_page[] = 'special__four04';

    }

    /** Return only last entry in the array */
    return end( $current_page );

  }

  function get_current_page_name() {

    return $this->get_page_slug_info( $this->get_current_page_slug(), 'name' );

  }

  function get_current_taxonomy() {

    return $this->get_page_slug_info( $this->get_current_page_slug(), 'term' );

  }

  function get_scope( $slug ) {

    $scope = array();

    $page_slug_fragments = explode( '__', $slug );

    $scope['group']   = $page_slug_fragments[0];

    $scope['type']    = ( isset( $page_slug_fragments[1] ) ) ? $page_slug_fragments[1] : '';

    $scope['meta']    = ( isset( $page_slug_fragments[2] ) ) ? $page_slug_fragments[2] : '';

    $scope['term']    = ( isset( $page_slug_fragments[3] ) ) ? $page_slug_fragments[3] : '';

    return $scope;

  }

  /**
   * Gets a unique post index based on the slug for the page.
   * The base number is arbitrary but large enough not to conflict with normal page/post IDs
   */
  function get_index( $slug, $mode = 'page', $slug_mode = false ) {

    $base = true;

    $scope = $this->get_scope( $slug );

    if ( true === $slug_mode ) {

      if ( 'type' == $mode ) {
        return $scope['type'];
      }

      /** Page template should just have slug of page template as has nothing to do with type/id */
      if ( is_numeric( $scope['meta'] ) && 'term' == $mode && '' != $scope['term'] ) {

        return $scope['term'];

      } /** If page mode, return post id if its set .. this will be same for term mode if no template is selected */
      elseif ( is_numeric( $scope['meta'] ) ) {

        return $scope['meta'];

      } else {

        $index = $scope['type'];

        if ( '' != $scope['meta'] ) {
          $index .= '__' . $scope['meta']; }

        /** Add term if its for page scope, but term scope is for entire category so remove remove term from that */
        if ( '' != $scope['term'] && 'term' != $mode ) {
          $index .= '__' . $scope['term']; }

        return $index;
      }
    }

    if ( is_numeric( $scope['meta'] ) && 'page' == $mode ) {

      return $scope['meta'];

    }

    /**
     * If Type Mode Unset Meta and Term
     * If Meta Mode Unset Term
     */
    if ( 'type' == $mode ) {
      $scope['meta'] = '';
      $scope['term'] = '';
    }

    if ( 'page' == $mode ) {
      $scope['term'] = '';
    }

    if ( '' == $scope['term'] ) {

      /** Type only provided */
      if ( '' == $scope['meta'] ) {

        $index = array_search( $scope['type'], $this->lookup_array() );

      } elseif ( is_numeric( $scope['meta'] ) ) {

        $base   = false;
        $index  = $scope['meta'];

      } else {
        // var_dump( $scope['type'] );
        // var_dump( $scope['meta'] );
        // var_dump( $mode );

        $index = array_search( $scope['meta'], $this->lookup_array() );

        // var_dump( $index );
      }
    }

    if ( ! isset( $index ) || false === $index ) {
      $index = pl_generate_number_from_string( $scope['type'] . $scope['meta'] . $scope['term'] );
    }

    if ( $base ) {

      $base = $this->special_base;

      return $base + $index;

    } else {
      return $index;
    }

  }


  function get_page_slug_info( $page_slug, $item = 'all' ) {

    $return = array();

    $page_slug_fragments = explode( '__', $page_slug );

    $groupid    = $page_slug_fragments[0];

    $typeid     = ( isset( $page_slug_fragments[1] ) ) ? $page_slug_fragments[1] : '';

    $metaid     = ( isset( $page_slug_fragments[2] ) ) ? $page_slug_fragments[2] : '';

    $termid     = ( isset( $page_slug_fragments[3] ) ) ? $page_slug_fragments[3] : '';

    $term_slug        = ( '' != $termid && is_numeric( $metaid ) ) ? $termid : $metaid;

    $type_slug        = ( '' != $typeid ) ? $typeid : $groupid;

    $defaults = array(
      'url'     => '',
      'name'    => pl_ui_key( $type_slug ),
      'slug'    => $page_slug,
      'type'    => $type_slug,
      'term'    => $term_slug,

    );

    /** Single Posts, Pages, etc..  */
    if ( 'single' == $groupid ) {

      /* If a page ID is provided, we have a link */
      $return['url'] = ( isset( $metaid ) ) ? get_permalink( $metaid ) : '';

      if ( is_numeric( $metaid ) ) {

        $return['name'] = get_the_title( $metaid ) ? stripslashes( get_the_title( $metaid ) ) : __( '(No Title)', 'pl-platform' );

        if ( get_option( 'show_on_front' ) == 'page' && get_option( 'page_on_front' ) == $metaid ) {

          $return['url'] = home_url();
          $return['name'] = sprintf( __( 'Home Page', 'pl-platform' ) );

        }
      }
    } /** ALL Archive Page Types... */
    elseif ( 'archive' == $groupid ) {

      if ( 'post' == $typeid ) {
        $return['name'] = pl_ui_key( $metaid ); }

      if ( 'blog' == $metaid ) {

        if ( get_option( 'show_on_front' ) == 'page' && get_option( 'page_for_posts' ) ) {
          $return['url'] = get_permalink( get_option( 'page_for_posts' ) );
        } else {           $return['url'] = home_url(); }
      } elseif ( 'date' == $metaid ) {

        $return['url'] = home_url( '?m=' . date( 'Y' ) );

      } elseif ( 'search' == $metaid ) {

        $return['url'] = home_url( '?s=and' );

      } elseif ( 'category' == $metaid ) {

        /* Specific Category ID provided */
        if ( isset( $termid ) && is_numeric( $termid ) ) {

          $term = get_term( $termid, 'category' );

          $return['name'] = ( $term->name ) ? stripslashes( $term->name ) : '(No Title)';

          $return['url'] = home_url( '?cat=' . $termid );

        }
      } elseif ( 'author' == $metaid ) {

        /* Author ID Provided */
        if ( isset( $termid ) && is_numeric( $termid ) ) {

          $user_data = get_userdata( $termid );

          $return['name'] = stripslashes( $user_data->display_name );

        } else {

          $current_user = wp_get_current_user();
          $termid = $current_user->ID;

        }

        $return['url'] = home_url( '?author=' . $termid );

      } elseif ( is_object( get_post_type_object( $typeid ) ) ) {

        $pt = get_post_type_object( $typeid );

        $return['name'] = $pt->labels->name;

        $return['url']  = get_post_type_archive_link( $typeid );

        if ( ! empty( $metaid ) && false !== strpos( $metaid, 'search' ) ) {

          $return['name'] = __( 'Search: ', 'pl-platform' ) . $return['name'];

          $return['url'] = $return['url'] . '?s=and&post_type='.$typeid;

        } elseif ( ! empty( $termid ) && is_numeric( $termid ) ) {

          $term = get_term( $termid, $metaid );

          $return['name'] = $term->name;

          $return['url'] = get_term_link( $termid, $metaid );

        }
      } elseif ( 'taxonomy' == $typeid || 'post_tag' == $metaid || 'post_format' == $metaid ) {

        if ( 'taxonomy' == $typeid ) {

          $return['name'] = pl_ui_key( $typeid );

          $return['type'] = $metaid;

        }

        if ( '' != $metaid ) {

          $taxonomy       = get_taxonomy( $metaid );
          $return['name'] = ( $taxonomy->labels->singular_name ) ? stripslashes( $taxonomy->labels->singular_name ) : '(No Title)';

        }

        /* Term Provided */
        if ( ! empty( $termid ) ) {

          if ( is_numeric( $termid ) ) {
            $termid = (int) $termid;
          }

          $term = get_term( $termid, $metaid );

          $return = array(
            'url'     => get_term_link( $termid, $metaid ),
            'name'    => isset( $term->name ) ? $term->name : '(No Title)',
            'type'    => $typeid,
          );

        }
      }
    } elseif ( 'four04' == $typeid ) {

      $return['name'] = '404 Error Page';
      $return['url'] = home_url( 'set404-' . rand( 100, 99999 ) );

    }

    $return = wp_parse_args( $return, $defaults );

    return ( 'all' != $item ) ? $return[ $item ] : $return;

  }
}

/**
 * Gets the slug for the page type
 */
function pl_page_type() {
  global $pl_page;
  return $pl_page->type;
}

function pl_current_page_name() {
  global $pl_page;
  return $pl_page->get_current_page_name();
}



function pl_current_page_id() {
  global $pl_page;
  return $pl_page->id;
}

function pl_current_type_id() {
  global $pl_page;
  return $pl_page->typeid;
}

function pl_edit_id() {
  global $pl_page;
  return $pl_page->get_edit_id();
}

function pl_edit_slug() {
  global $pl_page;
  return $pl_page->get_edit_slug();
}

function pl_template_mode() {
  global $pl_page;
  return $pl_page->template_mode();
}

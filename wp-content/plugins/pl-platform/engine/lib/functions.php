<?php
/**
 * Main Platform functions
 *
 * @version   5.0.0
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
/**
 * Get Taxonomy Terms for Select Options
 */
function pl_get_terms_for_selection( $post_type = false ) {

  $post_type = ( ! empty( $post_type ) ) ? $post_type : 'post';

  $taxonomies = get_object_taxonomies( $post_type, 'objects' );

  $opts = array();

  foreach ( $taxonomies as $slug => $data ) {

    $terms = get_terms( $slug );

    foreach ( $terms as $k => $term ) {

      $joint = sprintf( '%s__%s', $slug, $term->slug );

      $opts[ $joint ] = array(
        'name'  => sprintf( '%s -> %s (%s)', $data->labels->name, $term->name, $term->count ),
      );
    }
  }

  return $opts;

}

/**
 * Nested container HTML
 */
function pl_nested_container( $section ) {
    ?>
    <div class="pl-container-wrap" >
      <div class="pl-content-area">
        <div class="pl-row nested-section-content" data-bind="stopBinding: true" data-contains-level="<?php echo $section->level + 1;?>" >
          <?php echo pl_render_nested_sections( $section ); ?>
        </div>
      </div>
    </div>
    <?php
}

function pl_nested_sections( $section ) {
    ?>
    <div class="pl-row nested-section-content" data-bind="stopBinding: true" data-contains-level="<?php echo $section->level + 1;?>" >
      <?php echo pl_render_nested_sections( $section ); ?>
    </div>
    <?php
}

/**
 * Get standard HTML head for user in header.php
 * This function is needed if header.php is used in child themes
 */
function pl_standard_head( $content ) {

?><!DOCTYPE html>
<html class="no-js" <?php language_attributes();  do_action( 'the_html_tag' ); ?>>
    <head>
      <meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' );?>; charset=<?php bloginfo( 'charset' );?>" />
      <?php
        printf( apply_filters( 'pl_xfn', '<link rel="profile" href="http://gmpg.org/xfn/11" />'."\n      " ) );
        printf( apply_filters( 'pl_mobile_viewport', '<meta name="viewport" content="width=device-width, initial-scale=1">'."\n" ) );
      ?>
      <?php wp_head(); ?>
    </head>
    <body <?php body_class( pl_scheme_class( 'site_scheme' ) ); ?>><?php pl_hook( 'pagelines_before_site' ); ?>
      <div id="site" class="site-wrap" ><?php pl_hook( 'pagelines_before_page' ); ?>
        <div  class="site-wrap-pad"><?php pl_hook( 'pagelines_site_wrap' ); ?>
            <div id="page" class="page-wrap"><?php pl_hook( 'pagelines_page' ); ?>
                <div class="page-wrap-pad"><?php pl_hook( 'pagelines_before_main' ); ?>

                <?php echo $content; ?>

<?php
}

/**
 * Standard Footer HTML
 */
function pl_standard_foot( $content ) {

        ?></div>
            </div><?php pl_hook( 'pagelines_after_page' ); ?> 

        <?php echo $content; ?>

        </div><?php pl_hook( 'pagelines_after_footer' ); ?> 
      </div>
    <?php wp_footer(); ?>
    </body>
</html>
<?php
}

function pl_edit_head() {

    ob_start(); ?>
    <header id="header" class="header pl-region" data-clone="header">
      <div class="region-wrap pl-region-wrap-header pl-row no-pad" data-contains-level="0">
        <?php pl_template_hook( 'pl_region_header', 'header' ); ?>
      </div>
    </header>
<?php

    return ob_get_clean();
}


function pl_edit_foot() {

    ob_start();
?>

    <footer id="footer" class="footer pl-region <?php echo pl_scheme_class( 'footer_scheme' );?>" data-clone="footer">
      <div class="region-wrap pl-region-wrap-footer pl-row no-pad" data-contains-level="0">
        <?php pl_template_hook( 'pl_region_footer', 'footer' );  ?>
      </div>
    </footer>

<?php

    return ob_get_clean();
}


/**
 * URLs used on the front end
 */
function pl_get_system_urls() {

  $a = array();

  // Site Admin URL
  $a['adminURL']    = admin_url();

  $a['ajaxURL']     = pl_ajax_url();

  // Edit current post URL
  $a['editPost']    = plns_edit_post_link();

  // Add menus URL
  $a['menus']       = admin_url( 'nav-menus.php' );

  // Add/edit widgets URL
  $a['widgets']     = admin_url( 'widgets.php' );

  // URL of core framework
  $a['CoreURL']     = get_template_directory_uri();

  // URL of parent theme
  $a['ParentStyleSheetURL'] = get_template_directory_uri();

  // URL of child theme, if active
  $a['ChildStyleSheetURL'] = get_stylesheet_directory_uri();

  // Base website URL
  $a['siteURL']     = do_shortcode( '[pl_site_url]' );

  $a['uploadsURL']  = do_shortcode( '[pl_uploads_url]' );

  $a['logoURL']     = pl_get_site_logo( false );

  global $pl_medialib;

  // Media library link for use in iFrame
  $a['mediaLibrary']    = $pl_medialib->pl_media_library_link();

  //  Media library videos link for use in iFrame
  $a['mediaLibraryVideo'] = $pl_medialib->pl_media_library_link( 'video' );

  // Add media link
  $a['addMedia']    = admin_url( 'media-new.php' );

  return $a;
}


/**
 * Return the option array information
 * for a commonly used option type
 */
function pl_std_opt( $type, $additions = array() ) {

  if ( 'image' == $type ) {
    $o = array(
                'type'                  => 'image_upload',
            );
  } elseif ( 'global_settings' == $type ) {

    $o = array(
                'type'                  => 'link',
                'label'                 => __( '<i class="pl-icon pl-icon-cog"></i> View Global Settings', 'pl-platform' ),
                'classes'               => 'pl-btn-default pl-btn-sm pl-btn-block',
                'url'                   => admin_url( sprintf( 'admin.php?page=pl-platform-settings&settings_tab=%s', $additions['tab'] ) ),
                'help'                  => __( 'This section uses global settings set in your PageLines settings panel.', 'pl-platform' ),
            );
  } elseif ( 'icons' == $type ) {

    $o = array(
                'type'                  => 'textarea',
                'place'                 => "facebook http://facebook.com/pagelines\ntwitter http://twitter.com/pagelines",
                'ref'                  => __( '<p>Add linked icons using slug/url pairs.</p><p>Add each on a new line. <br/><strong>Format:</strong><br/> [icon slug] [link url]</p><p/><strong>Example:</strong><br/> facebook http://facebook.com/you</p> <p>Use <a href="https://fortawesome.github.io/Font-Awesome/icons/">Font Awesome</a> to get the icon slug.</p>', 'pl-platform' ),
            );
  } elseif ( 'icon' == $type ) {

    $o = array(
                'type'                  => 'select_icon',
                'default'               => 'ok',
            );
  } elseif ( 'title' == $type ) {

    $o = array(
                'type'                  => 'text',
                'default'               => 'Hello.',
            );
  } elseif ( 'text' == $type ) {

    $o = array(
                'type'                  => 'richtext',
                'default'               => 'Just some text.',
            );
  } elseif ( 'html' == $type ) {

    $o = array(
                'type'                  => 'textarea',
                'default'               => '<p>Just some html.</p>',
            );
  } elseif ( 'link' == $type || 'header' == $type ) {

    $o = array(
                'type'                  => 'text',
            );
  } elseif ( 'logo' == $type ) {

    $o = array(
        'type'      => 'image_upload',
        'has_alt'   => true,
    );

  } elseif ( 'menu' == $type ) {

    $o = array(
        'type'      => 'select_menu',
        'label'     => __( 'Select Menu', 'pl-platform' ),
    );

  } elseif ( 'search' == $type ) {

    $o = array(
        'type'      => 'check',
        'label'     => __( 'Show Search?', 'pl-platform' ),
    );

  } elseif ( 'button' == $type ) {

    $key = ( isset( $additions['key'] ) ) ? $additions['key'] : $type;

    $o = array(
                'title'                 => __( 'Button / Link', 'pl-platform' ),
                'type'                  => 'multi',
                'stylize'               => 'button-config',
                'opts'                  => pl_button_link_options( $key, $additions ),
            );
  } elseif ( 'background_image' == $type ) {

    $o = array(
                'type'                  => 'image_upload',
            );
  } elseif ( 'background_color' == $type ) {

    $o = array(
                'type'                  => 'color',
            );
  } elseif ( 'columns' == $type ) {

    $o = array(
        'type'  => 'select',
        'opts'    => array(
          '2'       => array( 'name' => __( '2 of 12 Columns', 'pl-platform' ) ),
          '3'       => array( 'name' => __( '3 of 12 Columns', 'pl-platform' ) ),
          '4'       => array( 'name' => __( '4 of 12 Columns', 'pl-platform' ) ),
          '6'       => array( 'name' => __( '6 of 12 Columns', 'pl-platform' ) ),
          '12'      => array( 'name' => __( '12 of 12 Columns', 'pl-platform' ) ),
        ),
    );
  } elseif ( 'posts_per_page' == $type ) {

    $o = array(
        'type'          => 'count_select',
        'count_start'   => 1,
        'count_number'  => 36,
    );
  } elseif ( 'text_alignment' == $type ) {

    $o = array(
                'type'                  => 'radio',
                'opts'                  => array(
                        array( 'txt' => '<i class="pl-icon pl-icon-minus"></i>',        'val' => '' ),
                        array( 'txt' => '<i class="pl-icon pl-icon-align-left"></i>',   'val' => 'pl-alignment-left' ),
                        array( 'txt' => '<i class="pl-icon pl-icon-align-center"></i>', 'val' => 'pl-alignment-center' ),
                        array( 'txt' => '<i class="pl-icon pl-icon-align-right"></i>',  'val' => 'pl-alignment-right' ),
                    ),
            );
  } elseif ( 'section_alignment' == $type ) {

    $o = array(
                'type'                  => 'radio',
                'opts'                  => array(
                        array( 'txt' => '<i class="pl-icon pl-icon-minus"></i>',            'val' => '' ),
                        array( 'txt' => '<i class="pl-icon pl-icon-long-arrow-left"></i>',  'val' => 'pl-section-left' ),
                        array( 'txt' => '<i class="pl-icon pl-icon-arrows-h"></i>',         'val' => 'pl-section-center' ),
                        array( 'txt' => '<i class="pl-icon pl-icon-long-arrow-right"></i>', 'val' => 'pl-section-right' ),
                    ),
            );
  } elseif ( 'scheme' == $type ) {

    $o = array(
                'label'                 => __( 'Text/Element Color Scheme', 'pl-platform' ),
                'type'                  => 'select',
                'default'               => 'pl-scheme-default',
                'opts'                  => array(
                        'pl-scheme-default'     => array( 'name' => __( 'Default', 'pl-platform' ) ),
                        'pl-scheme-light'       => array( 'name' => __( 'White', 'pl-platform' ) ),
                        'pl-scheme-dark'        => array( 'name' => __( 'Black', 'pl-platform' ) ),
                    ),
                'help'                  => 'Note: This will override link colors if set.',
            );
  }

    $o = wp_parse_args( $additions, $o );

    $key = ( isset( $o['key'] ) ) ? $o['key'] : $type;

    $o = wp_parse_args( $o, array(
            'key'           => $key,
            'label'         => pl_ui_key( $key ),
    ));

    return $o;
}

/**
  * In plugins they dont have access to the sections base class.
  * So here we include it for them.
  */
function setup_section_plugin() {

    include( PL_LIB . '/sections.class.php' );

}

/**
 * Get a sections data
 */
function get_section_data( $s, $config = array() ) {

    global $pl_sections_data;
    global $plfactory;

    $uid = $s->meta['clone'];

    $results = $pl_sections_data->get_section_data( array( $uid ) );

    /** If saved then just use that */
  if ( isset( $results[ $uid ] ) ) {
      $settings = $results[ $uid ]; } /** If user config'd then thats next best */
  else {

        $settings = wp_parse_args( $s->section_defaults(), $plfactory->recursive_parse_opts( $s->section_opts(), array(), 'keyval' ) );

        $settings = wp_parse_args( $config, $settings );

  }

    return $settings;
}

function pl_defaults_model( $defaults ) {

    $model = array();

    $d = array(
        'type'  => 'text',
        'value' => '',
        'opts'  => array(),
      );

    if ( is_array( $defaults ) && ! empty( $defaults ) ) {

      foreach ( $defaults as $key => $value ) {

        $model[ $key ] = wp_parse_args( array( 'value' => $value ), $d );

      }
    }

    return $model;

}

add_shortcode( 'plsection', 'pl_get_section' );

/**
 * Function for plsection shortcode
 */
function pl_get_section( $config ) {

    $defaults               = array();
    $defaults['section']    = 'content';
    $defaults['id']         = ( isset( $config['section'] ) ) ? $config['section'] . pl_edit_id() : $defaults['section'];

    $defaults['settings'] = array();

    $config = shortcode_atts( $defaults, $config );

    global $plfactory;

    $sid = $config['section'];
    $uid = $config['id'];

  if ( ! isset( $plfactory->section_ids[ $sid ] ) ) {

    pl_missing_section( $sid );

    return;

  } else {
    $object = $plfactory->section_ids[ $sid ];
  }

    $s = $plfactory->factory[ $object ];

    $s->meta = array(
        'clone'     => $uid,
        'object'    => $object,
        'content'   => array(),
    );

    $plfactory->list['sections'][ $uid ] = array( 'object' => $object );

    /** Allow for a function that can be used to set defaults */

    $settings = get_section_data( $s, $config['settings'] );

    $plfactory->sections_data[ $uid ] = $settings;

    $s->meta['set'] = $settings;

    ob_start();

    $s->section_styles();

    /** Auto load build.css document which is generated from build.less */
    if ( is_file( $s->base_dir . '/build.css' ) ) {
        pl_style( $sid, $s->base_url . '/build.css' ); }

    if ( is_file( $s->base_dir . '/style.css' ) ) {
        pl_style( $sid, $s->base_url . '/style.css' ); }

    echo '<div class="temp-wrap" data-bind="stopBinding: true" >';

    $s->render( $s->meta );

    echo "\n</div>\n";

    wp_reset_postdata(); // Reset $post data
    wp_reset_query(); // Reset wp_query

    return apply_filters( 'pl_get_section', ob_get_clean(), $config );

}


/**
 * Sections Functions Library
 */

/** GLOBALS */

/** For live loading we need this to store JS Urls */
global $pl_live_scripts;
$pl_live_scripts = array();

global $pl_live_styles;
$pl_live_styles = array();

/** FUNCTIONS */

/**
 * Enqueue Script Wrapper
 * Special enqueue so we can auto load in JS via ajax
 */
function pl_script( $handle, $src = false, $deps = array( 'jquery' ), $ver = false, $in_footer = true ) {
    global $pl_live_scripts;

    $pl_live_scripts[ $handle ] = $src;

    $ver = ( ! $ver ) ? pl_cache_key() : $ver;

    wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

/**
 * Enqueue Style Wrapper
 * Special enqueue so we can auto load in JS via ajax
 */
function pl_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
    global $pl_live_styles;

    $pl_live_styles[ $handle ] = $src;

    $ver = ( ! $ver ) ? pl_cache_key() : $ver;

    wp_enqueue_style( $handle, $src, $deps, $ver, $media );

}

/**
 * To enable filtering by taxonomy, we add a list of taxes to each post
 * Used in Masonic
 * TODO make optional output
 */
function pl_get_post_filters( $taxonomy, $post ) {

    $filters = wp_get_post_terms( $post->ID, $taxonomy );

    $filter_classes = array();
  if ( is_array( $filters ) && ! empty( $filters ) ) {
    foreach ( $filters as $f ) {
      $filter_classes[] = $f->slug;
    }
  }
    return join( ' ', $filter_classes );
}

function pl_button_link_options( $key, $defaults = array() ) {

    $opts = array();

    $opts[] = array(
                    'key'           => $key,
                    'type'          => 'text',
                    'label'         => __( 'Button URL', 'pl-platform' ),
                );

    $opts[] = array(
                    'key'           => $key . '_text',
                    'type'          => 'text',
                    'label'         => __( 'Button Text', 'pl-platform' ),
                );

    $opts[] = array(
                    'key'           => $key . '_style',
                    'type'          => 'select_button',
                    'label'         => __( 'Button Color', 'pl-platform' ),
                );

    $opts[] = array(
                    'key'           => $key . '_size',
                    'type'          => 'select_button_size',
                    'label'         => __( 'Button Size', 'pl-platform' ),
                );

    $opts[] = array(
                    'key'           => $key . '_newwindow',
                    'type'          => 'check',
                    'label'         => __( 'Open New Tab?', 'pl-platform' ),
                );

    foreach ( $opts as &$o ) {
      if ( isset( $defaults[ $o['key'] ] ) ) {
        $o['default'] = $defaults[ $o['key'] ];
      }
    }

    unset( $o ); // set by reference

    return $opts;
}

/**
 * Is template static?
 */
function pl_is_static_template( $loc = 'no' ) {

  $non_static_templates = apply_filters( 'pl_dynamic_templates', array() );

  if ( function_exists( 'pl_template_base' ) && in_array( pl_template_base(), $non_static_templates ) ) {
    $static = false; } else {     $static = true; }

    return $static;
}

global $pl_template_settings;
$pl_template_settings = array();

function pl_add_static_settings( $opts ) {

    global $pl_template_settings;

    $pl_template_settings = array_merge( $opts, $pl_template_settings );

}

function pl_get_template_settings() {

    global $pl_template_settings;

  if ( ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) || ( function_exists( 'is_bbpress' ) && is_bbpress() ) ) {
    $default = 'wrapped';
  } else {
    $default = 'unwrapped';
  }

    $standard_template_settings = array(
            array(
              'key'       => 'tplwrap',
              'type'      => 'select',
              'default'   => $default,
              'label'     => wp_get_theme()->name . __( ' Content Wrap', 'pl-platform' ),
              'opts'      => array(
                'wrapped'     => array( 'name' => __( 'Wrap: Content Width', 'pl-platform' ) ),
                'unwrapped'   => array( 'name' => __( 'Unwrapped: Full Width', 'pl-platform' ) ),
              ),
            ),
        );

    return array_merge( $pl_template_settings, $standard_template_settings );
}

function pl_static_opt( $key ) {
    global $plfactory;

    return ( isset( $plfactory->content_data[ $key ] ) ) ? $plfactory->content_data[ $key ] : false;
}

function pl_dynamic_media( $config = array() ) {

    $defaults = array(
        'key'       => '',
        'alt'       => '',
        'classes'   => array(),
        'bind'      => '',
        'src'       => '',
        'html'      => '',
        'default'   => pl_fallback_image(),
    );

    $config = wp_parse_args( $config, $defaults );

    $htmlkey = $config['key'] . '_html';

    $classes = ( ! empty( $config['classes'] ) ) ? join( ' ', $config['classes'] ) : false;

    ?>
    <div class="media-wrap <?php echo $classes;?>" data-bind="visible: <?php echo $config['key'];?>() || <?php echo $htmlkey;?>()">

        <div class="img-wrap"><img src="<?php echo $config['src'];?>" alt="<?php echo $config['alt'];?>" data-bind="plimg: <?php echo $config['key'];?>" /></div>

        <div class="media-html" data-bind="visible: <?php echo $htmlkey;?>, plshortcode: <?php echo $htmlkey;?>"><?php echo do_shortcode( $config['html'] ); ?></div>

    </div>
    <?php
}

function pl_dynamic_nav( $config = array() ) {

    $defaults = array(
            'key'           => '',
            'menu'          => '',
            'menu_class'    => '',
            'wrap_class'    => '',
            'default'       => true,
            'binds'         => '',
            'do_fallback'   => false,
        );

    $config = wp_parse_args( $config, $defaults );

    $wrap_classes = sprintf( 'pl-nav-container %s', $config['wrap_class'] );

    $binds = 'plnav:' . $config['key'];

    $binds .= ( '' != $config['binds'] ) ? ','.$config['binds'] : '';

    ?>
    <div class="<?php echo $wrap_classes;?>"
        data-bind="<?php echo $binds;?>"
        data-class="<?php echo $config['menu_class'];?>"
        data-default="<?php echo $config['default'];?>"
        data-fallback="<?php echo $config['do_fallback'];?>"
    >
        <?php echo pl_nav( $config );?>
    </div>
    <?php
}


function pl_dynamic_button( $key, $classes = 'pl-btn-default', $default = 'default' ) {

  ?>
    <a class="pl-btn" href="#" data-bind="plbtn: '<?php echo $key;?>'" ></a>
    <?php
}

function pl_dynamic_sidebar( $config ) {

    $defaults = array(
            'key'               => '',
            'cls'           => '',
            'val'               => '',
            'default'       => '',
            'binds'         => '',
        );

    $config = wp_parse_args( $config, $defaults );

    $load = ($config['val']) ? $config['val'] : $config['default'];

    $binds = 'plsidebar:' . $config['key'];

    $binds .= ( '' != $config['binds'] ) ? ','.$config['binds'] : '';

    ?>
    <div class="plsb <?php echo $config['cls'];?>" data-bind="<?php echo $binds;?>">
        <?php echo pl_draw_sidebar( $load ); ?>
    </div>
    <?php
}


function pl_make_callback( $config ) {

    $calls = array();

  foreach ( $config as $key => $v ) {
    $calls[] = sprintf( '%s:%s()', $key, $key );
  }

    return sprintf( 'plcallback: {%s}', join( ',', $calls ) );

}

/**
 * Are the PageLines tools active.
 * Makes sure user is Admin and get variable is set.
 *
 * @since 5.0.0
 */
function pl_tools_active() {

  $edit_change = ( isset( $_GET['pl_edit'] ) ) ? $_GET['pl_edit'] : false;

  if ( $edit_change ) {

    /** If not logged in, redirect to auth page so they can */
    if ( ! is_user_logged_in() ) {
      auth_redirect();
    } elseif ( pl_can_use_tools() && 'on' == $edit_change ) {
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }

}

/**
 * Determines if we are inside the iFrame within the workarea parent frame
 */
function pl_is_workarea_iframe() {

  return ( isset( $_GET['iframe'] ) && $_GET['iframe'] ) ? true : false;

}

/**
 * SETTINGS FUNCTIONS
 */

/**
 * Gets the PageLines settings for the current theme and requested page ID
 */
if ( ! function_exists( 'pl_get_all_settings' ) ) {

  function pl_get_all_settings( $editid = false, $slug = false ) {

    $slug = ( $slug ) ? $slug : pl_base_settings_slug();

    if ( false !== $editid ) {
      $settings = get_post_meta( $editid, $slug, true );
    } else {
        $settings = get_option( $slug );
    }

    return apply_filters( 'pl_get_all_settings', stripslashes_deep( $settings ), $editid, $slug );

  }
}

/**
 * Assigns a special slug based on the theme if it is not a core framework
 * this prevents data leakage between themes.
 */

if ( ! function_exists( 'pl_base_settings_slug' ) ) {

  function pl_base_settings_slug() {
    return 'pagelines-settings';
  }
}


/**
 * Gets a single PageLines option from a key.
 * Returns false if not set.
 */
if ( ! function_exists( 'pl_user_setting' ) ) {

  function pl_user_setting( $key, $default = false, $editid = false ) {

    $settings = pl_get_all_settings( $editid );

    $value = ( isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) ? $settings[ $key ] : $default;

    if ( has_filter( "pl_user_setting_$key" ) ) {
      return apply_filters( "pl_user_setting_$key", $value ); }

    return $value;

  }
}


/**
 * Update a single setting value.
 */
if ( ! function_exists( 'pl_user_setting_update' ) ) {

  function pl_user_setting_update( $key, $value = false, $editid = false ) {

    $settings = pl_get_all_settings( $editid );

    $settings[ $key ] = $value;

    pl_update_all_settings( $settings, $editid );

  }
}





/**
 * Updates all settings of a certain scope ...
 */
if ( ! function_exists( 'pl_update_all_settings' ) ) {

  function pl_update_all_settings( $settings, $editid = false ) {

    if ( false !== $editid ) {

      update_post_meta( $editid, pl_base_settings_slug(), $settings );

    } else {
      update_option( pl_base_settings_slug(), $settings );
    }

  }
}




if ( ! function_exists( 'pl_set_default_settings' ) ) {

  function pl_set_default_settings() {

    $settings = pl_get_all_settings();

    if ( ! $settings ) {
      $settings = pl_settings_default();
    }

    pl_update_all_settings( $settings );

    return $settings;

  }
}


/**
 * WordPress Libraries and Utilities
 */

/*
 * Enqueue CodeMirror, used in admin and front end
 */
if ( ! function_exists( 'pl_load_codemirror' ) ) {

  function pl_load_codemirror( $path = '' ) {

    // Codemirror Styles
    pl_style( 'codemirror',  $path . '/codemirror/codemirror.css' );

    // CodeMirror Syntax Highlighting
    pl_script( 'pl-codemirror', $path . '/codemirror/pl.codemirror.js', array( 'jquery' ), '', false );

    // Codebox defaults
    $base_editor_config = array(
      'lineNumbers'   => true,
      'lineWrapping'  => false,
      'tabSize'   => 2,
    );
    wp_localize_script( 'pl-codemirror', 'cm_base_config', apply_filters( 'pagelines_cm_config', $base_editor_config ) );
  }
}

/**
 * Enqueues WP color picker
 * HOOK in 'wp_enqueue_scripts'
 * Enqueues the native WP color picker on the front end, using a special technique
 */

if ( ! function_exists( 'pl_enqueue_color_picker' ) ) {

  function pl_enqueue_color_picker() {

    wp_enqueue_style( 'wp-color-picker' );

    wp_enqueue_script(
        'iris',
        admin_url( 'js/iris.min.js' ),
        array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' )
    );

      // dequeue if already queued
      wp_dequeue_script( 'wp-color-picker' );

      // enqueue patched version for multi pickers.
      wp_enqueue_script(
          'wp-color-picker',
          pl_framework_url( 'plugins' ) . '/wp/colorpicker-wp.js',
          array( 'iris' )
      );

      $colorpicker_l10n = array(
          'clear'       => __( 'Clear', 'pl-platform' ),
          'defaultString'   => __( 'Default', 'pl-platform' ),
          'pick'        => __( 'Select Color', 'pl-platform' ),
      );
      wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
  }
}

/**
 * Enqueue Jquery UI Libraries
 * HOOK in 'wp_enqueue_scripts'
 * Needed for drag and drop editing, settings, etc..
 * Added in footer and depends on 'jquery'
 */
if ( ! function_exists( 'pl_enqueue_jquery_ui' ) ) {

  function pl_enqueue_jquery_ui() {

    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-tabs' );
    wp_enqueue_script( 'jquery-ui-widget' );
    wp_enqueue_script( 'jquery-ui-mouse' );
    wp_enqueue_script( 'jquery-ui-draggable' );
    wp_enqueue_script( 'jquery-ui-droppable' );
    wp_enqueue_script( 'jquery-ui-resizable' );
    wp_enqueue_script( 'jquery-ui-accordion' );
    wp_enqueue_script( 'jquery-ui-sortable' );
  }
}

function pl_color_setting( $color ) {

  if ( is_int( $color ) ) {
    $color = strval( $color ); }

  $clean_hex = str_replace( '#', '', $color );

  return ( '' != $clean_hex ) ? sprintf( '#%s', $clean_hex ) : '';
}


/**
 * Polishes a Key for UI presentation
 */

if ( ! function_exists( 'pl_ui_key' ) ) {

  function pl_ui_key( $key ) {

    $replace = array( 'pl_', '_' );

    return ucwords( str_replace( $replace, ' ', $key ) );
  }
}

/*
 * This function recursively converts an multi dimensional array into a multi layer object
 * Needed for json conversion in < php 5.2
 */
if ( ! function_exists( 'pl_convert_arrays_to_objects' ) ) {

  function pl_convert_arrays_to_objects( $input ) {

    $objects = new stdClass;

    if ( is_array( $input ) ) {
      foreach ( $input as $key => $val ) {

        if ( '' === $key ) {
          $key = 0;
        }

        if ( is_array( $val ) && ! empty( $val ) ) {

          $objects->{$key} = pl_convert_arrays_to_objects( $val );

        } else {

          $objects->{$key} = $val;

        }
      }
    }
      return $objects;
  }
}



/**
 * Check if a site has been registered.
 * Return (bool)
 */
function pl_is_professional() {

  if ( true === pl_is_local() ) {
    return true;
  } else {
    return get_site_option( 'pl_site_registered', false );
  }

}

/**
 * For sorting by comparison based on a number input in array
 * Used in settings arrays for ordering.
 */
function pl_compare_position( $a, $b ) {

  $a['pos'] = ( isset( $a['pos'] ) ) ? (int) $a['pos'] : 100;
  $b['pos'] = ( isset( $b['pos'] ) ) ? (int) $b['pos'] : 100;

  if ( is_int( $a['pos'] ) && is_int( $b['pos'] ) ) {
    return $a['pos'] - $b['pos']; } else {     return 0; }
}

if ( ! function_exists( 'pl_icons' ) ) {

  function pl_icons() {

    $icons = array(
      '500px',
      'adjust',
      'adn',
      'align-center',
      'align-justify',
      'align-left',
      'align-right',
      'amazon',
      'ambulance',
      'anchor',
      'android',
      'angellist',
      'angle-double-down',
      'angle-double-left',
      'angle-double-right',
      'angle-double-up',
      'angle-down',
      'angle-left',
      'angle-right',
      'angle-up',
      'apple',
      'archive',
      'area-chart',
      'arrow-circle-down',
      'arrow-circle-left',
      'arrow-circle-o-down',
      'arrow-circle-o-left',
      'arrow-circle-o-right',
      'arrow-circle-o-up',
      'arrow-circle-right',
      'arrow-circle-up',
      'arrow-down',
      'arrow-left',
      'arrow-right',
      'arrow-up',
      'arrows-alt',
      'arrows-h',
      'arrows-v',
      'arrows',
      'asterisk',
      'at',
      'automobile',
      'backward',
      'balance-scale',
      'ban',
      'bank',
      'bar-chart-o',
      'bar-chart',
      'barcode',
      'bars',
      'battery-0',
      'battery-1',
      'battery-2',
      'battery-3',
      'battery-4',
      'battery-empty',
      'battery-full',
      'battery-half',
      'battery-quarter',
      'battery-three-quarters',
      'bed',
      'beer',
      'behance-square',
      'behance',
      'bell-o',
      'bell-slash-o',
      'bell-slash',
      'bell',
      'bicycle',
      'binoculars',
      'birthday-cake',
      'bitbucket-square',
      'bitbucket',
      'bitcoin',
      'black-tie',
      'bluetooth-b',
      'bluetooth',
      'bold',
      'bolt',
      'bomb',
      'book',
      'bookmark-o',
      'bookmark',
      'briefcase',
      'btc',
      'bug',
      'building-o',
      'building',
      'bullhorn',
      'bullseye',
      'bus',
      'buysellads',
      'cab',
      'calculator',
      'calendar-check-o',
      'calendar-minus-o',
      'calendar-o',
      'calendar-plus-o',
      'calendar-times-o',
      'calendar',
      'camera-retro',
      'camera',
      'car',
      'caret-down',
      'caret-left',
      'caret-right',
      'caret-square-o-down',
      'caret-square-o-left',
      'caret-square-o-right',
      'caret-square-o-up',
      'caret-up',
      'cart-arrow-down',
      'cart-plus',
      'cc-amex',
      'cc-diners-club',
      'cc-discover',
      'cc-jcb',
      'cc-mastercard',
      'cc-paypal',
      'cc-stripe',
      'cc-visa',
      'cc',
      'certificate',
      'chain-broken',
      'chain',
      'check-circle-o',
      'check-circle',
      'check-square-o',
      'check-square',
      'check',
      'chevron-circle-down',
      'chevron-circle-left',
      'chevron-circle-right',
      'chevron-circle-up',
      'chevron-down',
      'chevron-left',
      'chevron-right',
      'chevron-up',
      'child',
      'chrome',
      'circle-o-notch',
      'circle-o',
      'circle-thin',
      'circle',
      'clipboard',
      'clock-o',
      'clone',
      'close',
      'cloud-download',
      'cloud-upload',
      'cloud',
      'cny',
      'code-fork',
      'code',
      'codepen',
      'codiepie',
      'coffee',
      'cog',
      'cogs',
      'columns',
      'comment-o',
      'comment',
      'commenting-o',
      'commenting',
      'comments-o',
      'comments',
      'compass',
      'compress',
      'connectdevelop',
      'contao',
      'copy',
      'copyright',
      'creative-commons',
      'credit-card-alt',
      'credit-card',
      'crop',
      'crosshairs',
      'css3',
      'cube',
      'cubes',
      'cut',
      'cutlery',
      'dashboard',
      'dashcube',
      'database',
      'dedent',
      'delicious',
      'desktop',
      'deviantart',
      'diamond',
      'digg',
      'dollar',
      'dot-circle-o',
      'download',
      'dribbble',
      'dropbox',
      'drupal',
      'edge',
      'edit',
      'eject',
      'ellipsis-h',
      'ellipsis-v',
      'empire',
      'envelope-o',
      'envelope-square',
      'envelope',
      'eraser',
      'eur',
      'euro',
      'exchange',
      'exclamation-circle',
      'exclamation-triangle',
      'exclamation',
      'expand',
      'expeditedssl',
      'external-link-square',
      'external-link',
      'eye-slash',
      'eye',
      'eyedropper',
      'facebook-f',
      'facebook-official',
      'facebook-square',
      'facebook',
      'fast-backward',
      'fast-forward',
      'fax',
      'feed',
      'female',
      'fighter-jet',
      'file-archive-o',
      'file-audio-o',
      'file-code-o',
      'file-excel-o',
      'file-image-o',
      'file-movie-o',
      'file-o',
      'file-pdf-o',
      'file-photo-o',
      'file-picture-o',
      'file-powerpoint-o',
      'file-sound-o',
      'file-text-o',
      'file-text',
      'file-video-o',
      'file-word-o',
      'file-zip-o',
      'file',
      'files-o',
      'film',
      'filter',
      'fire-extinguisher',
      'fire',
      'firefox',
      'flag-checkered',
      'flag-o',
      'flag',
      'flash',
      'flask',
      'flickr',
      'floppy-o',
      'folder-o',
      'folder-open-o',
      'folder-open',
      'folder',
      'font',
      'fonticons',
      'fort-awesome',
      'forumbee',
      'forward',
      'foursquare',
      'frown-o',
      'futbol-o',
      'gamepad',
      'gavel',
      'gbp',
      'ge',
      'gear',
      'gears',
      'genderless',
      'get-pocket',
      'gg-circle',
      'gg',
      'gift',
      'git-square',
      'git',
      'github-alt',
      'github-square',
      'github',
      'gittip',
      'glass',
      'globe',
      'google-plus-square',
      'google-plus',
      'google-wallet',
      'google',
      'graduation-cap',
      'gratipay',
      'group',
      'h-square',
      'hacker-news',
      'hand-grab-o',
      'hand-lizard-o',
      'hand-o-down',
      'hand-o-left',
      'hand-o-right',
      'hand-o-up',
      'hand-paper-o',
      'hand-peace-o',
      'hand-pointer-o',
      'hand-rock-o',
      'hand-scissors-o',
      'hand-spock-o',
      'hand-stop-o',
      'hashtag',
      'hdd-o',
      'header',
      'headphones',
      'heart-o',
      'heart',
      'heartbeat',
      'history',
      'home',
      'hospital-o',
      'hotel',
      'hourglass-1',
      'hourglass-2',
      'hourglass-3',
      'hourglass-end',
      'hourglass-half',
      'hourglass-o',
      'hourglass-start',
      'hourglass',
      'houzz',
      'html5',
      'i-cursor',
      'ils',
      'image',
      'inbox',
      'indent',
      'industry',
      'info-circle',
      'info',
      'inr',
      'instagram',
      'institution',
      'internet-explorer',
      'intersex',
      'ioxhost',
      'italic',
      'joomla',
      'jpy',
      'jsfiddle',
      'key',
      'keyboard-o',
      'krw',
      'language',
      'laptop',
      'lastfm-square',
      'lastfm',
      'leaf',
      'leanpub',
      'legal',
      'lemon-o',
      'level-down',
      'level-up',
      'life-bouy',
      'life-buoy',
      'life-ring',
      'life-saver',
      'lightbulb-o',
      'line-chart',
      'link',
      'linkedin-square',
      'linkedin',
      'linux',
      'list-alt',
      'list-ol',
      'list-ul',
      'list',
      'location-arrow',
      'lock',
      'long-arrow-down',
      'long-arrow-left',
      'long-arrow-right',
      'long-arrow-up',
      'magic',
      'magnet',
      'mail-forward',
      'mail-reply-all',
      'mail-reply',
      'male',
      'map-marker',
      'map-o',
      'map-pin',
      'map-signs',
      'map',
      'mars-double',
      'mars-stroke-h',
      'mars-stroke-v',
      'mars-stroke',
      'mars',
      'maxcdn',
      'meanpath',
      'medium',
      'medkit',
      'meh-o',
      'mercury',
      'microphone-slash',
      'microphone',
      'minus-circle',
      'minus-square-o',
      'minus-square',
      'minus',
      'mixcloud',
      'mobile-phone',
      'mobile',
      'modx',
      'money',
      'moon-o',
      'mortar-board',
      'motorcycle',
      'mouse-pointer',
      'music',
      'navicon',
      'neuter',
      'newspaper-o',
      'object-group',
      'object-ungroup',
      'odnoklassniki-square',
      'odnoklassniki',
      'opencart',
      'openid',
      'opera',
      'optin-monster',
      'outdent',
      'pagelines',
      'paint-brush',
      'paper-plane-o',
      'paper-plane',
      'paperclip',
      'paragraph',
      'paste',
      'pause-circle-o',
      'pause-circle',
      'pause',
      'paw',
      'paypal',
      'pencil-square-o',
      'pencil-square',
      'pencil',
      'percent',
      'phone-square',
      'phone',
      'photo',
      'picture-o',
      'pie-chart',
      'pied-piper-alt',
      'pied-piper',
      'pinterest-p',
      'pinterest-square',
      'pinterest',
      'plane',
      'play-circle-o',
      'play-circle',
      'play',
      'plug',
      'plus-circle',
      'plus-square-o',
      'plus-square',
      'plus',
      'power-off',
      'print',
      'product-hunt',
      'puzzle-piece',
      'qq',
      'qrcode',
      'question-circle',
      'question',
      'quote-left',
      'quote-right',
      'ra',
      'random',
      'rebel',
      'recycle',
      'reddit-alien',
      'reddit-square',
      'reddit',
      'refresh',
      'registered',
      'remove',
      'renren',
      'reorder',
      'repeat',
      'reply-all',
      'reply',
      'retweet',
      'rmb',
      'road',
      'rocket',
      'rotate-left',
      'rotate-right',
      'rouble',
      'rss-square',
      'rss',
      'rub',
      'ruble',
      'rupee',
      'safari',
      'save',
      'scissors',
      'scribd',
      'search-minus',
      'search-plus',
      'search',
      'sellsy',
      'send-o',
      'send',
      'server',
      'share-alt-square',
      'share-alt',
      'share-square-o',
      'share-square',
      'share',
      'shekel',
      'sheqel',
      'shield',
      'ship',
      'shirtsinbulk',
      'shopping-bag',
      'shopping-basket',
      'shopping-cart',
      'sign-in',
      'sign-out',
      'signal',
      'simplybuilt',
      'sitemap',
      'skyatlas',
      'skype',
      'slack',
      'sliders',
      'slideshare',
      'smile-o',
      'soccer-ball-o',
      'sort-alpha-asc',
      'sort-alpha-desc',
      'sort-amount-asc',
      'sort-amount-desc',
      'sort-asc',
      'sort-desc',
      'sort-down',
      'sort-numeric-asc',
      'sort-numeric-desc',
      'sort-up',
      'sort',
      'soundcloud',
      'space-shuttle',
      'spinner',
      'spoon',
      'spotify',
      'square-o',
      'square',
      'stack-exchange',
      'stack-overflow',
      'star-half-empty',
      'star-half-full',
      'star-half-o',
      'star-half',
      'star-o',
      'star',
      'steam-square',
      'steam',
      'step-backward',
      'step-forward',
      'stethoscope',
      'sticky-note-o',
      'sticky-note',
      'stop-circle-o',
      'stop-circle',
      'stop',
      'street-view',
      'strikethrough',
      'stumbleupon-circle',
      'stumbleupon',
      'subscript',
      'subway',
      'suitcase',
      'sun-o',
      'superscript',
      'support',
      'table',
      'tablet',
      'tachometer',
      'tag',
      'tags',
      'tasks',
      'taxi',
      'television',
      'tencent-weibo',
      'terminal',
      'text-height',
      'text-width',
      'th-large',
      'th-list',
      'th',
      'thumb-tack',
      'thumbs-down',
      'thumbs-o-down',
      'thumbs-o-up',
      'thumbs-up',
      'ticket',
      'times-circle-o',
      'times-circle',
      'times',
      'tint',
      'toggle-down',
      'toggle-left',
      'toggle-off',
      'toggle-on',
      'toggle-right',
      'toggle-up',
      'trademark',
      'train',
      'transgender-alt',
      'transgender',
      'trash-o',
      'trash',
      'tree',
      'trello',
      'tripadvisor',
      'trophy',
      'truck',
      'try',
      'tty',
      'tumblr-square',
      'tumblr',
      'turkish-lira',
      'tv',
      'twitch',
      'twitter-square',
      'twitter',
      'umbrella',
      'underline',
      'undo',
      'university',
      'unlink',
      'unlock-alt',
      'unlock',
      'unsorted',
      'upload',
      'usb',
      'usd',
      'user-md',
      'user-plus',
      'user-secret',
      'user-times',
      'user',
      'users',
      'venus-double',
      'venus-mars',
      'venus',
      'viacoin',
      'video-camera',
      'vimeo-square',
      'vimeo',
      'vine',
      'vk',
      'volume-down',
      'volume-off',
      'volume-up',
      'warning',
      'wechat',
      'weibo',
      'weixin',
      'whatsapp',
      'wheelchair',
      'wifi',
      'wikipedia-w',
      'windows',
      'won',
      'wordpress',
      'wrench',
      'xing-square',
      'xing',
      'y-combinator-square',
      'y-combinator',
      'yahoo',
      'yc-square',
      'yc',
      'yelp',
      'yen',
      'youtube-play',
      'youtube-square',
      'youtube',
    );
    asort( $icons );

    $icons = array_values( $icons );

    return apply_filters( 'pl_icons', $icons );
  }
}

/** Possible button styles / classes */
if ( ! function_exists( 'pl_btn_classes' ) ) {

  function pl_btn_classes() {
    $array = array(
      'default'       => 'Default',
      'ol-white'      => 'Outline White',
      'ol-black'      => 'Outline Black',
      'primary'       => 'Blue',
      'info'          => 'Light Blue',
      'success'       => 'Green',
      'warning'       => 'Orange',
      'danger'        => 'Red',
      'inverse'       => 'Black',
      'link'          => 'Link',
    );
    return $array;
  }
}

/** Possible Button Sizes  */
if ( ! function_exists( 'pl_button_sizes' ) ) {

  function pl_button_sizes() {
    $array = array(
      'st'      => 'Standard',
      'lg'      => 'Large',
      'xl'      => 'X-Large',
      'sm '     => 'Small',
      'xs'      => 'Mini',
    );
    return $array;
  }
}

/** Various available Image Sizes/Ratios */
if ( ! function_exists( 'pl_add_image_sizes' ) ) {

  function pl_add_image_sizes() {

      /** Supported Image Thumb Sizes */
      add_image_size( 'aspect-thumb',     1500, 1000, true );
      add_image_size( 'basic-thumb',      750,    750,  true );
      add_image_size( 'landscape-thumb',  1500, 750,  true );
      add_image_size( 'tall-thumb',       750,    1500, true );
      add_image_size( 'big-thumb',        1500, 1500, true );
  }
}


function pl_framework_dir() {

  global $pl_framework;

  return $pl_framework->base_dir;
}


function pl_framework_url( $area ) {

    global $pl_framework;

  $ui = $pl_framework->base_url . '/ui';

  if ( 'ui' == $area ) {
    $url = $ui;
  } elseif ( 'css' == $area ) {
    $url = $ui . '/css';
  } elseif ( 'js' == $area ) {
    $url = $ui . '/js';
  } elseif ( 'images' == $area ) {
    $url = $ui . '/images';
  } elseif ( 'plugins' == $area ) {
    $url = $ui . '/plugins';
  }

  return $url;
}


function pl_missing_section( $object, $clone = '', $level = '' ) {

  if ( pl_can_use_tools() ) {

    $text = apply_filters( 'pl_missing_section_text', sprintf( '<strong>%s</strong> %s: "%s"',
        __( 'Admin Notice:', 'pl-platform' ),
        __( 'Missing section at this location with ID', 'pl-platform' ),
        $object
    ), $object, $clone, $level );

    $datas = sprintf( 'data-object="%s" data-clone="%s" data-level="%s"', $object, $clone, $level );

    echo apply_filters( 'pl_missing_section', sprintf( '<div class="pl-sn pl-col-sm-12" %s><div class="pl-alert pl-alert-info">%s</div></div>', $datas, $text ), $object, $clone, $level );
  }
}

function pl_disabled_region( $region ) {

  if ( pl_can_use_tools() && pl_is_workarea_iframe() ) {

    $text = apply_filters( 'pl_disabled_region_text', sprintf( '<strong>%s</strong> %s',
        __( 'Admin Notice:', 'pl-platform' ),
        sprintf( __( 'The %s region has been disabled.', 'pl-platform' ), $region )
      ), $region );

    echo apply_filters( 'pl_disabled_section', sprintf( '<div class="pl-col-sm-12"><div class="pl-alert pl-alert-info">%s</div></div>',$text ), $region );
  }
}

function pl_is_region_disabled( $region ) {
  
  $post_id = pl_current_page_id();
  
  $setting = "_pl_$region";
  
  $disabled = false;
  
  if( get_post_meta( $post_id, $setting, true ) ) {
    $disabled = true;
  }
  return apply_filters( 'pl_disabled_region', $disabled, $region, $post_id );
}

function pl_tpl_classes() {

  $classes      = array( 'pl-region' );
  $attributes   = array( 'data-clone="template"' );

  return sprintf( 'class="%s" %s', join( ' ', $classes ), join( ' ', $attributes ) );
}


/**
 * Gets an ordered array of the dynamic regions available
 */
function pl_site_regions() {

  $regions = array(
    'header',
    'template',
    'footer',
  );

  return $regions;

}

/**
 * Function Library for controller and processing
 */

function is_blog_page_type() {

  if ( is_home()    ||
    is_category()   ||
    is_archive()  ||
    is_author()   ||
    is_tag()    ||
    is_search()
  ) {
    return true; } else {     return false; }

}

global $pl_notifications;
$pl_notifications = array();

function pl_workarea_notification( $message ) {

  global $pl_notifications;

  if ( isset( $pl_notifications ) && is_array( $pl_notifications ) ) {
    $pl_notifications[] = $message;
  }

}


/**
 * Replace modified slugs with ones needed for queries
 */
function pl_replace_query_terms( $taxonomy ) {

  if ( 'postformat' == $taxonomy ) {
    $query_tax = 'post_format';
  } elseif ( 'posttag' == $taxonomy ) {
    $query_tax = 'post_tag';
  } else {
    $query_tax = $taxonomy;
  }

    return $query_tax;

}

function pl_fix_data_type( $data ) {

  if ( is_numeric( $data ) ) {

    if ( floatval( $data ) == intval( $data ) ) {
      return (int) $data; } else {       return (float) $data; }
  } elseif ( 'true' === $data ) {

    return true;

  } elseif ( 'false' === $data ) {

    return false;

  } elseif ( '' === $data || 'null' === $data ) {

    return null;

  } else {

    $data = pl_maybe_unserialize( $data );

    if ( ! is_array( $data ) ) {
      return stripslashes( $data );

      //If it's an array, run this function across all of the nodes in the array.
    } else {

      return array_map( 'pl_maybe_unserialize', $data );
    }
  }
}

function pl_maybe_unserialize( $data ) {

  try {

    $result = maybe_unserialize( $data );

  } catch ( Exception $e ) {

    if ( is_string( $data ) ) {

      $data = preg_replace( '!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $data );
      $result = maybe_unserialize( $data );

    } else {

      $result = $data;
    }
  }
  return $result;
}



function pl_scheme_class( $key ) {
  return pl_user_setting( $key, 'pl-scheme-default' );
}

function pl_cache_key() {

  if ( '' != get_theme_mod( 'pl_cache_key' ) ) {
    return get_theme_mod( 'pl_cache_key' );
  } else {
    return pl_reset_pl_cache_key();
  }
}

function pl_reset_pl_cache_key() {
  $key = substr( uniqid(), -6 );
  set_theme_mod( 'pl_cache_key', $key );
  return $key;
}

function pl_cred() {

  $cred = sprintf( '<div class="pl-cred"><a href="%s" title="%s"><i class="pl-icon pl-icon-pagelines"></i></a></div>',
      PL()->urls->pagelines,
      __( 'Built With PageLines for WordPress', 'pl-platform' )
  );

  if ( pl_user_setting( 'hide_pl_cred' ) && true == pl_is_professional() ) {
    $cred = '';
  }

  return apply_filters( 'pl_cred', $cred );

}

/**
 * Global AjaxURL variable
 * http://www.garyc40.com/2010/03/5-tips-for-using-ajax-in-wordpress/
 *
 * @return string The WP url used for ajax requests
 */
function pl_get_ajax_url() {

  $ajax_url = pl_ajax_url();

  if ( has_action( 'pl_force_ssl' ) ) {
    $ajax_url = str_replace( 'http://', 'https://', $ajax_url ); }

  return $ajax_url;
}

/**
 * Encode arrays and php objects for JSON
 * @param  object or array $object_or_array a PHP object or aray
 * @return json string
 */
function pl_json_encode( $object_or_array ) {

  return json_encode( pl_convert_arrays_to_objects( $object_or_array ) );

}



function pl_unserialize_or_decode( $data ) {
  if ( is_serialized( $data ) ) {
    $output = unserialize( $data ); } else {     $output = json_decode( $data, true ); }

  return stripslashes_deep( $output );
}

/**
 * Takes data and converts it as needed for JSON output
 * @return json string
 */
function pl_json_prep_data( $data ) {

  if ( is_array( $data ) || is_object( $data ) ) {
    $data = pl_json_encode( $data ); } elseif ( is_int( $data ) ) {
    $data = $data; } elseif ( is_bool( $data ) ) {
      $data = ($data) ? 'true' : 'false'; } else {     $data = sprintf( "'%s'", $data ); }

    return $data;
}


/**
 * Takes a comma separated string, removes white space and creates an array split at commas
 * Example: Used for headers from sections which are strings, then converted to sections
 */
function pl_comma_string_to_array( $string ) {

  /** explode into array */
  $array = explode( ',', $string );

  return $array;
}

/**
 * Create a fake filler post that says that it's not a real post.
 * This is used for example in the flipper section to ensure consistent UI.
 * @return [type] [description]
 * TODO even used?
 */
function pl_create_filler_post() {

  $filler_post = new stdClass();

  $filler_post->ID = rand();
  $filler_post->post_category   = array( 'uncategorized' );   //Add some categories. an array()???
  $filler_post->post_content    = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam ut mauris et arcu sagittis feugiat ac non augue. Duis volutpat ut ex quis convallis. Quisque vitae sagittis turpis.';     //The full text of the post.
  $filler_post->post_status     = 'publish';      // Set the status of the new post.
  $filler_post->post_title      = 'Filler Post';    //The title of your post.
  $filler_post->post_type     = 'post';         //Sometimes you might want to post a page.

  return $filler_post;

}

/**
 * Is the user capable of using PageLines editings tools
 *
 * @since 5.0.0
 */
function pl_can_use_tools( $role = 'edit_theme_options', $args = false ) {

  $allowed = apply_filters( 'pl_can_use_tools', current_user_can( $role, $args ) );

  if ( $allowed ) {
    return true;
  } else {
    return false;
  }
}

/**
 * Render sections that are nested within another section
 * @param  array  $sections the sections that are nested
 * @param  integer $level    the level of the section in overall hierarchy
 * @return html            the html output
 */
function pl_render_nested_sections( $current_section_object ) {

  $nested_sections = $current_section_object->content;
  $container_level = $current_section_object->level;
  $container_meta  = $current_section_object->meta;

  /** Get nested section HTML */
  ob_start();

  global $plfactory;

  if ( ! empty( $nested_sections ) ) {

    foreach ( $nested_sections as $key => $map_meta ) {
      $plfactory->render_section( $map_meta, $container_level + 1 ); }
  }

  $nested_section_output = ob_get_clean();

  /** Make things are still what they started with... nested sections mess with these values.... */
  $current_section_object->content = $nested_sections;
  $current_section_object->level = $container_level;
  $current_section_object->meta = $container_meta;

  return $nested_section_output;
}


/**
 * Gets the edit post link for the current page
 * @return string url to edit post
 */
function plns_edit_post_link() {

  $url = get_edit_post_link( pl_current_page_id(), '' );

  return $url;
}


// TODO is this used anywhere?
function pl_get_url() {

  return 'http://www.pagelines.com/';
}


function pl_create_id_from_name( $string ) {

  if ( ! empty( $string ) ) {
    $string = str_replace( ' ', '_', trim( strtolower( $string ) ) );
    $string = preg_replace( '/[^A-Za-z0-9\-]/', '', $string );
  } else {     $string = pl_create_clone_id(); }

  return ( ! is_int( $string ) ) ? $string : 's'.$string;
}

function pl_create_clone_id() {
  return 'u' . substr( uniqid(), -7 );
}


function pl_generate_number_from_string( $str ) {

  return (int) substr( preg_replace( '/[^0-9,.]/', '', md5( $str ) ), -6 );
}


function pl_enc( $object_or_array, $quotes = false ) {
  $encode = json_encode( pl_convert_arrays_to_objects( $object_or_array ) );

  return ( $quotes ) ? sprintf( "'%s'", $encode ) : $encode;
}



function pl_animations() {
  $animations = array(
    ''          => __( 'No Animation', 'pl-platform' ),
    'pla-fade'      => __( 'Fade', 'pl-platform' ),
    'pla-scale'     => __( 'Scale', 'pl-platform' ),
    'pla-from-left'   => __( 'From Left', 'pl-platform' ),
    'pla-from-right'  => __( 'From Right', 'pl-platform' ),
    'pla-from-bottom' => __( 'From Bottom', 'pl-platform' ),
    'pla-from-top'    => __( 'From Top', 'pl-platform' ),
  );

  return $animations;
}

function pl_get_taxonomies() {
  $args = array(
    'public'   => true,

  );
  return get_taxonomies( $args,'names' );
}

function pl_get_menus() {

  $output = array();

  $menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );

  foreach ( $menus as $menu ) {
    $output[ $menu->term_id ] = $menu->name;
  }

  return $output;

}

function pl_get_sidebars() {

  global $wp_registered_sidebars;
  $allsidebars = $wp_registered_sidebars;
  ksort( $allsidebars );

  $sidebar_select = array();
  foreach ( $allsidebars as $key => $sb ) {

    $sidebar_select[ $sb['id'] ] = $sb['name'];
  }

  return $sidebar_select;
}

function pl_shortcode_url( $full_url ) {

  $url = str_replace( home_url(), '[pl_site_url]', $full_url );

  return $url;
}

function pl_uploads_shortcode_url( $full_url ) {

  $uploads = wp_upload_dir();
  $url = str_replace( $uploads['baseurl'], '[pl_uploads_url]', $full_url );

  return $url;
}

/**
 * Gets a default image based on a size
 * @param  string $size thumb size
 * @return url       image url
 */
function pl_fallback_image( $size = 'aspect-thumb', $shortcode = false ) {

  $user_default = pl_user_setting( 'pl_fallback_image' );

  if ( $user_default ) {

    $att = wp_get_attachment_image_src( $user_default, $size );

    $url = $att[0];
  } elseif ( 'tall-thumb' == $size ) {

    $url = pl_framework_url( 'images' ) . '/default-tall.png';
  } elseif ( 'landscape-thumb' == $size ) {

    $url = pl_framework_url( 'images' ).'/default-landscape.png';
  } elseif ( 'basic-thumb' == $size || 'thumbnail' == $size ) {

    $url = pl_framework_url( 'images' ) . '/default-thumb.png';

  } else {     $url = pl_framework_url( 'images' ) . '/default-image.png'; }

  if ( $shortcode ) {
    $url = pl_uploads_shortcode_url( $url );
  }

  return $url;
}


// gets the url of an avatar image
function pl_get_avatar_src( $avatar ) {

  preg_match( "/src='(.*?)'/i", $avatar, $matches );

  return (isset( $matches ) && isset( $matches[1] )) ? $matches[1] : '';

}


function pl_get_site_logo( $shortcode = true ) {

  if ( pl_user_setting( 'site_logo' ) ) {
    $logo = do_shortcode( pl_user_setting( 'site_logo' ) );
  } elseif ( file_exists( get_template_directory() . '/logo.png' ) ) {
    $logo = get_template_directory_uri() . '/logo.png';
  } else {
    $logo = pl_framework_url( 'images' ). '/leaf.png';
  }

  if ( $shortcode ) {
    return pl_uploads_shortcode_url( $logo ); } else {     return $logo; }
}


/**
 * Print current post info
 */
function pl_posts_info() {

  $special = apply_filters( 'pl_content_archive_special', false );

  if ( ( is_category() || is_archive() || is_search() || is_author() ) && ! $special ) :

    $info = sprintf( '<div class="current_posts_info"><div class="title">%s <i class="pl-icon pl-icon-angle-right"></i> %s</div></div>',
        pl_page_title(),
        pl_page_subtitle()
    );
    echo apply_filters( 'pl_posts_info', $info );
  endif;
}

// Gets a smart page title
function pl_page_title() {

  $link = get_permalink( get_option( 'page_for_posts' ) );

  $pt = get_post_type();

  if ( ! empty( $pt ) ) {

    $title = get_post_type_object( $pt )->labels->name;
    $link   = get_post_type_archive_link( $pt );
  } elseif ( is_page() || is_single() ) {

    global $post;

    $title = get_the_title( $post->ID );

    $link  = get_permalink( $post->ID );

  } elseif ( is_home() ) {
    $title = __( 'Blog', 'pl-platform' ); } elseif ( is_search() ) {
    $title = __( 'Search', 'pl-platform' ); } elseif ( is_category() ) {
      $title = __( 'Category', 'pl-platform' ); } elseif ( is_tag() ) {
      $title = __( 'Tag', 'pl-platform' ); } elseif ( is_author() ) {
        $title = __( 'Author', 'pl-platform' ); } elseif ( is_archive() ) {
        $title = __( 'Archive', 'pl-platform' ); } elseif ( is_404() ) {
          return __( '404 Error!', 'pl-platform' ); } else {     return false; }

        return sprintf( '<a href="%s">%s</a>', $link, $title );

}

function pl_page_subtitle() {

  if ( is_home() ) {

    return false;

  } elseif ( is_category() ) {

    return sprintf( '%s "%s"', __( 'Currently viewing the category:', 'pl-platform' ), single_cat_title( false, false ) );

  } elseif ( is_search() ) {

    return sprintf( '%s "%s"', __( 'Showing search results for', 'pl-platform' ), get_search_query() );

  } elseif ( is_tag() ) {

    return sprintf( '%s "%s"', __( 'Currently viewing the tag:', 'pl-platform' ), single_tag_title( false, false ) );

  } elseif ( is_archive() ) {

    if ( is_author() ) {
      global $author;
      global $author_name;
      $curauth = ( isset( $_GET['author_name'] ) ) ? get_user_by( 'slug', $author_name ) : get_userdata( intval( $author ) );
      $out = sprintf( '%s <strong>"%s"</strong>', __( 'Posts by:', 'pl-platform' ), $curauth->display_name );
    } elseif ( is_day() ) {
      $out = sprintf( '%s <strong>"%s"</strong>', __( 'From the daily archives:', 'pl-platform' ), get_the_time( 'l, F j, Y' ) );
    } elseif ( is_month() ) {
      $out = sprintf( '%s <strong>"%s"</strong>', __( 'From the monthly archives:', 'pl-platform' ), get_the_time( 'F Y' ) );
    } elseif ( is_year() ) {
      $out = sprintf( '%s <strong>"%s"</strong>', __( 'From the yearly archives:', 'pl-platform' ), get_the_time( 'Y' ) );
    } else {

      if ( is_post_type_archive() ) {
        $title = post_type_archive_title( null,false ); }

      if ( ! isset( $title ) ) {
        $o = get_queried_object();
        if ( isset( $o->name ) ) {
          $title = $o->name; }
      }

      if ( ! isset( $title ) ) {
        $title = the_date(); }

      $out = sprintf( '%s <strong>"%s"</strong>', __( 'Viewing archives for ', 'pl-platform' ), $title );
    }

    return $out;

  } else {     return false; }
}

function pl_post_types_with_thumbs( $thumb = true ) {

  $pt_objects = get_post_types( array( 'public' => true ), 'objects' );

  $pts = array();

  foreach ( $pt_objects as $key => $pt ) {

    if ( $thumb ) {

      if ( post_type_supports( $key, 'thumbnail' ) && $pt->public ) {
        $pts[ $key ] = array(
          'name' => $pt->label,
        );
      }
    } else {

      $pts[ $key ] = array(
        'name' => $pt->label,
      );

    }
  }
  return $pts;
}

function pl_convert_array_to_object( $array, $defaults = array() ) {

  $objects = array();

  foreach ( $array as $index => $l ) {
    $l = wp_parse_args( $l, $defaults );

    $obj = new stdClass();

    foreach ( $l as $key => $value ) {
        $obj->$key = $value;
    }

    if ( isset( $l['id'] ) ) {
      $objects[ $l['id'] ] = $obj; } else {       $objects[] = $obj; }
  }

  return $objects;

}


// ------------------------------------------
// HOOK/FILTER UTILITIES
// ------------------------------------------

/**
 * PageLines Register Hook
 *
 * Calls a hook and passes the hook name in as an argument
 *
 */
function pl_hook( $hook_name, $hook_area_id = null ) {

  do_action( $hook_name, $hook_name, $hook_area_id );

}


/**
 * Draws the dynamic drag/drop area for specific regions.
 * If they are overridden, then the pl_dynamic_templates index wont be added
 */
global $pl_dynamic_templates;
$pl_dynamic_templates = array();

function pl_template_hook( $hook_name, $hook_area_id = null ) {

  global $pl_dynamic_templates;

  $pl_dynamic_templates[ $hook_area_id ] = true;

  do_action( $hook_name, $hook_name, $hook_area_id );

}

function pl_primary_template() {
  ?>
<div <?php echo pl_tpl_classes();?>>
  <div class="region-wrap pl-region-wrap-template pl-row no-pad" data-contains-level="0">
    <?php pl_template_hook( 'pl_region_template', 'templates' ); ?>
  </div>
</div>
<?php }


// ------------------------------------------
// ARRAY FUNCTIONS
// ------------------------------------------



// ------------------------------------------
// WORDPRESS UTILITIES
// ------------------------------------------


/*
* Gets the excerpt of a specific post ID or object
* @param - $post - object/int - the ID or object of the post to get the excerpt of
* @param - $length - int - the length of the excerpt in words
* @param - $tags - string - the allowed HTML tags. These will not be stripped out
* @param - $extra - string - text to append to the end of the excerpt
*/
function pl_excerpt_by_id( $post = false, $length = 55, $tags = '<a><em><strong>', $extra = '&hellip;', $always_use = true ) {

  if ( is_int( $post ) ) {
    // get the post object of the passed ID
    $post = get_post( $post );
  } else {
    global $post;

    if ( ! is_object( $post ) ) {
      return false;
    }
  }

  if ( has_excerpt( $post->ID ) ) {

    $the_excerpt = $post->post_excerpt;

    if ( ! $always_use ) {
        return apply_filters( 'the_content', $the_excerpt ); }
  } else {
    $the_excerpt = $post->post_content;
  }

  $the_excerpt = strip_shortcodes( strip_tags( $the_excerpt ), $tags );

  $the_excerpt = preg_split( '/\b/', $the_excerpt, $length * 2 + 1 );

  $excerpt_waste = array_pop( $the_excerpt );

  $the_excerpt = implode( $the_excerpt );

  $words = preg_split( '/[\s,]+/', $the_excerpt, $length + 1, PREG_SPLIT_NO_EMPTY );

  if ( count( $words ) > $length ) {
    array_pop( $words );
    $the_excerpt = implode( ' ', $words );
  } else {
    $the_excerpt = implode( ' ', $words );
  }

  $the_excerpt = $the_excerpt . $extra;

  return apply_filters( 'the_content', $the_excerpt );
}

/**
 * Generates an really short excerpt from the content or postid for tweets, facebook etc
 *
 * @param int|object $post_or_id_or_content can be the post ID, or the actual $post object itself
 * @param int $words the amount of words to allow
 * @param string $excerpt_more the text that is applied to the end of the excerpt if we algorithically snip it
 * @return string the snipped excerpt or the manual excerpt if it exists
 */
function pl_custom_excerpt( $post_or_id_or_content, $number_words = 10, $excerpt_more = ' [...]' ) {

  if ( is_object( $post_or_id_or_content ) ) {
    $postobj = $post_or_id_or_content;
  } elseif ( is_numeric( $post_or_id_or_content ) ) {
    $postobj = get_post( $post_or_id_or_content );
  } else {
    $postobj = false;
  }

  if ( is_object( $postobj ) ) {

    $text = ( '' == $postobj->post_excerpt ) ? $postobj->post_content : $postobj->post_excerpt;

  } else {
    $text = $post_or_id_or_content;
  }

  $text = strip_shortcodes( $text );

  $text = sanitize_text_field( $text );

  $text = apply_filters( 'the_content', $text );
  $text = str_replace( ']]>', ']]&gt;', $text );
  $text = strip_tags( $text );

  $words = preg_split( '/[\s,]+/', $text, $number_words + 1, PREG_SPLIT_NO_EMPTY );

  if ( count( $words ) > $number_words ) {
    array_pop( $words );
    $text = implode( ' ', $words );
    $text = $text . $excerpt_more;
  } else {
    $text = implode( ' ', $words );
  }

  return $text;
}


// ------------------------------------------
// IMAGE UTILITIES
// ------------------------------------------

/**
 * Get just the WordPress thumbnail URL - False if not there.
 */
function pl_post_image_url( $post_id, $size = false ) {

  if ( has_post_thumbnail( $post_id ) ) {

    $img_data = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size, false );

    $a['img'] = ( '' != $img_data[0] ) ? $img_data[0] : '';

    return $a['img'];

  } else {
    return false;
  }
}

// ------------------------------------------
// EXTENSIONS UTILITIES
// ------------------------------------------



function pl_dev_mode() {

  if ( defined( 'PL_DEBUG' ) && true === PL_DEBUG ) {
    return true; } else {     return false; }
}


function pl_html_comment( $text, $spacing = 1 ) {

  $newline = ( $spacing ) ? "\n" : '';

  $double = ( 2 == $spacing ) ? "\n\n" : $newline;

  return sprintf( '%s<!-- %s -->%s', $double, $text, $newline );

}

/**
 * Check if we are on localhost or a staging site
 */
function pl_is_local() {
    $whitelist = array( '127.0.0.1', '::1' );
    $domain    = $_SERVER['SERVER_NAME'];
    $ip        = $_SERVER['REMOTE_ADDR'];
    $parts     = explode( '.', $domain );

    // try localhost 1st
  if ( in_array( $ip, $whitelist ) ) {
    return true; }

    // now see if 'staging' is part of the domain.
  if ( in_array( 'staging', $parts ) ) {
    return true; }

    // else return false
    return false;
}

/**
 * Print data to php logs
 */
function pl_error_log( $data ) {
  error_log( print_r( $data, true ) );
}

function pl_favicon() {

    // WP 4.3 added favicons to core, if its available then let WP take over here.
  if ( function_exists( 'wp_site_icon' ) && has_site_icon() ) {
    return false;
  }
    $favicon = pl_user_setting( 'pl_favicon', pl_framework_url( 'images' ) . '/default-favicon.png' );

    return sprintf( '<link rel="shortcut icon" href="%s" type="image/x-icon" />%s', $favicon, "\n" );

}


/**
 * PageLines Default Widget
 *
 * Calls default sidebar widget, or allows user with 'edit_themes' capability to adds widgets
 *
 */
function pl_default_widget( $id, $default = false ) {

    global $wp_registered_sidebars;

  if ( isset( $wp_registered_sidebars[ $id ] ) && isset( $wp_registered_sidebars[ $id ]['name'] ) ) {
      $name = sprintf( '"%s"', $wp_registered_sidebars[ $id ]['name'] ); } else {
    $name = __( 'This sidebar', 'pl-platform' );
      }

      if ( $default ) {

        get_template_part( $default );
      } else { ?>

        <li class="widget"><div class="widget widget-default setup_area no_<?php echo $id;?>">
            <div class="widget-pad">
            <h3 class="widgettitle">No Widgets</h3>
            <p class="fix"><?php echo $name;?> needs some <a href="<?php echo admin_url( 'widgets.php' );?>">widgets</a>.
            </p>
            </div>
        </div></li>

    <?php }
}


/**
 * The template for WP comments.
 */
function pl_comments() {

    ?>
    <div id="comments" class="wp-comments">
        <div class="wp-comments-pad">
        <?php

            /* Stop the rest of comments.php from being processed,
             * but don't kill the script entirely -- we still have
             * to fully load the template.
             */
        if ( post_password_required() ) {
          return;
        }

        if ( have_comments() ) : ?>
                <h5 id="comments-title"><?php
                printf( _n( 'One Response to %2$s', '%1$s Responses to %2$s', get_comments_number(), 'pl-platform' ),
                number_format_i18n( get_comments_number() ), '"' . get_the_title() . '"' );
                ?></h5>
            <ol class="commentlist">
                <?php wp_list_comments( apply_filters( 'pl_list_comments', array( 'type' => 'comment', 'avatar_size' => '60' ) ) ); ?>
            </ol>
            <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
                <div class="navigation fix">
                    <div class="alignleft"><?php previous_comments_link( __( "<span class='meta-nav'>&larr;</span> Older Comments", 'pl-platform' ) ); ?></div>
                    <div class="alignright"><?php next_comments_link( __( "Newer Comments <span class='meta-nav'>&rarr;</span>", 'pl-platform' ) ); ?></div>
                </div> <!-- .navigation -->
            <?php endif; // check for comment navigation

            endif; // end have_comments()
        if ( comments_open() ) {

          $args = array(
            'comment_field' => '<p class="comment-form-comment pl-form-group"><label for="comment">' . _x( 'Comment', 'noun', 'pl-platform' ) . '</label><br /><textarea id="comment" class="pl-form-control" name="comment" aria-required="true"></textarea></p>',
          );
          comment_form( $args );

        }

            ?>
        </div>
    </div><?php
}

/**
 * PageLines Search Form
 *
 * Writes the default "Search" text to the search form's input field.
 * Allows the $searchform to be filtered via the pl_searchform hook
 *
 * @since   ...
 *
 * @param   bool $echo - defaults to true, outputs $searchform
 *
 * @return  mixed|void - if $echo is false, returns $searchform
 */
function pl_searchform( $echo = true, $class = 'search-form', $pt = false ) {

    /** Prevent admin bar on editing */

    $edit = ( pl_is_workarea_iframe() ) ? '<input type="hidden" name="iframe" value="1"/>' : '';

    $post_type = ( $pt ) ? sprintf( '<input type="hidden" name="post_type" value="%s"/>', $pt ) : '';

    $searchfield = sprintf( '<span class="btn-search"><i class="pl-icon pl-icon-search"></i></span><input type="text" value="" name="s" class="search-field" placeholder="%s" />', __( 'Search', 'pl-platform' ) );

    $searchform = sprintf(
        '<form method="get" class="%s pl-searcher" onsubmit="this.submit();return false;" action="%s/" ><fieldset>%s%s</fieldset></form>',
        $class,
        home_url(),
        $searchfield,
        $edit,
        $post_type
    );

    if ( $echo ) {
        echo apply_filters( 'pl_searchform', $searchform ); } else {         return apply_filters( 'pl_searchform', $searchform ); }
}


function pl_get_current_url( $add_get = true ) {

    global $wp;

    $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );

  if ( $add_get ) {
      $current_url = add_query_arg( $_GET, $current_url ); }

    return $current_url;
}

/**
 *
 *  Pagination Function
 *
 *  @package PageLines DMS
 *  @subpackage Functions Library
 *  @since 2.0.b12 moved
 *
 */
function pl_pagination() {

  if ( function_exists( 'wp_pagenavi' ) ) :
    wp_pagenavi();

  else :
      ?>
        <ul class="pl-pager page-nav-default fix">
            <li class="previous previous-entries">
                <?php next_posts_link( __( '&larr; Previous Entries','pl-platform' ) ) ?>
            </li>
            <li class="next next-entries">
            <?php previous_posts_link( __( 'Next Entries &rarr;','pl-platform' ) ) ?>
            </li>
        </ul>
      <?php
  endif;
}

// As a callback with nav args associated
function pl_menu_callback( $args ) {
    pl_menu_fallback( $args['menu_class'] );
}

function pl_menu_fallback( $class = '', $limit = 5 ) {

    $pages_out = pl_page_list( $limit );

    printf( '<ul class="%s">%s</ul>', $class, $pages_out );
}


function get_pl_nav_default( $args ) {

    $limit = ( isset( $args['limit'] ) ) ? $args['limit'] : 6;

    $pages_out = pl_page_list( $limit );

    return sprintf( '<ul class="%s">%s</ul>', $args['menu_class'], $pages_out );
}


function get_sidebar_section_opts( $key ) {

    $opts = array(
        array(
            'key'       => $key,
            'type'      => 'select_sidebar',
            'label'     => __( 'Select Sidebar', 'pl-platform' ),
            'help'      => __( 'Select the widgetized area you would like to use with this instance of Widgetizer.', 'pl-platform' ),
        ),
    );
    return $opts;
}
/**
 * PageLines Draw Sidebar
 *
 * Writes sidebar markup.
 * If no dynamic sidebar (widget) exists it calls the default widget
 */
function pl_draw_sidebar( $id, $default = false, $class = '' ) {

    ob_start();

    printf( '<div id="%s" class="sidebar_widgets %s fix">', 'list_'.$id, $class );

  if ( ! dynamic_sidebar( $id ) ) {

    if ( ! $default ) {

      pl_default_widget( $id, $default );

    } else {         echo $default; }
  }

    printf( '</div>' );

    return ob_get_clean();

}

function pl_nav( $args = array() ) {

    $out = '';

    $defaults = array(
            'menu_class'                => '',
            'menu'                      => false,
            'container'                 => null,
            'container_class'           => '',
            'depth'                     => 1,
            'do_fallback'               => false,
            'fallback_cb'               => false,
            'style'                     => false,
            'echo'                      => false,
            'mode'                      => 'simple',
            'theme_location'            => '',
            'default'                   => true,
        );

    $args = wp_parse_args( $args, $defaults );

    if ( empty( $args['menu'] ) && ! $args['do_fallback'] ) {
        return ''; }

    // if user has selected a theme location in customizer
    // or wp-admin then it should be used 1st.
    if ( '' !== $args['theme_location'] && has_nav_menu( $args['theme_location'] ) ) {
      $args['menu'] = '';
    }

    /** [items_wrap] The sprint for wrapping each menu item */
    $args['items_wrap'] = '<ul id="%1$s" class="%2$s">%3$s</ul>';

    $out = wp_nav_menu( $args );

    return $out ;

}

function pl_page_list( $number = 6 ) {

    $pages_out = '';

    $pages = wp_list_pages( 'echo=0&title_li=&sort_column=menu_order&depth=1' );

    $pages_arr = explode( "\n", $pages );

  for ( $i = 0; $i < $number; $i++ ) {

    if ( isset( $pages_arr[ $i ] ) ) {
        $pages_out .= $pages_arr[ $i ]; }
  }

    return $pages_out;

}

function pl_admin_banner( $args = array() ) {

  $args = wp_parse_args( $args, array(
      'header'    => 'Banner Title',
      'title'     => false,
      'subhead'   => false,
      'suphead'   => false,
      'content'   => 'Content goes here',
      'img'       => '',
      'src'       => '',
      'classes'   => '',
  ));

  ob_start();
    ?>
  <div class="pl-banner-wrap <?php echo $args['classes'];?>">

    <div class="pl-platform-banner ">
    <?php if ( $args['title'] ) :  ?>
      <div class="banner-title"><?php echo $args['title'];?></div>
    <?php endif; ?>
      <div class="pl-platform-banner-inner">

        <div class="banner-body with-image">
          <div class="banner-body-pad">

            <?php if ( $args['suphead'] ) :  ?>
              <div class="banner-supheader"><?php echo $args['suphead'];?></div>
            <?php endif; ?>

            <h2 class="banner-header" ><?php echo $args['header'];?></h2>

            <?php if ( $args['subhead'] ) :  ?>
              <div class="banner-subheader"><?php echo $args['subhead'];?></div>
            <?php endif; ?>

            <div class="pl-platform-banner-content">
              <?php echo $args['content'];?>
            </div>
          </div>
        </div>
        <?php if ( '' != $args['img'] ) { printf( '<div class="image-wrap">%s</div>', $args['img'] ); } ?>
        <?php if ( '' != $args['src'] ) { printf( '<div class="image-wrap"><img class="avatar" src="%s" /></div>', $args['src'] ); } ?>
        
      </div>
    </div>
  </div>
    <?php

    return ob_get_clean();
}

function pl_search_mast( $args = array() ) {

    $default = array(
            'subhead_url' => home_url(),
            'subhead'     => __( 'Post', 'pl-platform' ),
            'title'       => __( 'Search', 'pl-platform' ),
            'pt'          => 'post',
        );

    $args = wp_parse_args( $args, $default );

    ob_start();

    ?>
    <div class="pl-search-mast">
      <div class="pl-content-area">
        <h4><a href="<?php echo $args['subhead_url'];?>"><?php echo $args['subhead'];?></a></h4>
        <h1><?php echo $args['title'];?></h1>

        <form class="pl-search-mast-search" action="<?php echo home_url( '/' ); ?>" method="get">
          <fieldset>
            <button type="submit" class="search-button" onClick="submit()">
              <i class="pl-icon pl-icon-search"></i>
            </button>
            <input class="pl-border" type="text" name="s" id="search" value="<?php the_search_query(); ?>" placeholder="Search..." />
            <input type="hidden" value="<?php echo $args['pt'];?>" name="post_type" id="post_type" />
            <?php echo ( pl_is_workarea_iframe() ) ? '<input type="hidden" name="iframe" value="1"/>' : ''; ?>
          </fieldset>
        </form>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Create an admin notice
 */
function pl_create_notice( $args ) {

  // Only show notices to users with update_core role, thats single site admins and multisite network admins.
  if ( ! pl_can_use_tools( 'update_core' ) ) {
    return false;
  }
  
  // If we are not in the admin area, do not show an admin notice.
  if( ! is_admin() ) {
    return false;
  }

  $args = wp_parse_args( $args, array(
      'title'    => __( 'Notice', 'pl-platform' ),
      'msg'      => false,
      'action'   => false,
      'alink'    => '#',
      'atext'    => false,
      'id'       => 'none',
      'exp'      => 30 * DAY_IN_SECONDS,
      'class'    => '',
      'icon'     => 'cog',
      'icon_btn' => false,
  ));

  $hide = false;
  $action = '';
  $icon_btn = '';

  $id = 'pl_notice_' . $args['id'];

  /** Special URL parameter for testing. Deletes transients if present... */
  if ( isset( $_GET['pl_clear_transients'] ) ) {
    delete_transient( $id );
    delete_option( $id );
  }

  if ( 'pl_notice_none' != $id ) {

    $args['class'] .= ' pl-is-dismissible';

    if ( 'option' == $args['exp'] ) {
      $hide = get_option( $id );
      $exp  = 'option';
      $txt  = 'Dismiss';

    } else {
      $hide = get_transient( $id );
      $exp  = '';
      $txt = 'Hide';
    }

    $hide_btn = sprintf( '<span class="pl-notice-dismiss button button-secondary"><i class="pl-icon pl-icon-remove"></i> %s</span>', $txt );
  } else {
    $hide_btn = '';
  }

  $icon = sprintf( '<div class="pl-notice-icon"><i class="pl-icon pl-icon-%s"></i></div>', $args['icon'] );

  if ( $args['action'] ) {

    $action = $args['action'];

  } elseif ( $args['atext'] ) {

    if ( true == $args['icon_btn'] ) {
      $icon_btn = sprintf( '<i class="pl-icon pl-icon-%s"></i> ', $args['icon'] );
    }

    $action = sprintf('<a class="button button-primary" href="%s">%s%s <i class="pl-icon pl-icon-angle-right"></i></a>',
        $args['alink'],
        $icon_btn,
    $args['atext']);

  }

  if ( 'option' == $args['exp'] ) {
    $action .= ' ' . $hide_btn;

    $hide_btn = '';
  }

  $action = sprintf( '<span class="act">%s</span>', $action );

  if ( false === $hide ) {
    printf( '<div id="message" class="updated pl-notice %s" data-id="%s" data-exp="%s">%s <div class="pl-notice-content">%s<span class="ttl">%s</span> <span class="msg">%s</span>%s</div></div>',
        $args['class'],
        $id,
        $args['exp'],
        $icon,
        $hide_btn,
        $args['title'],
        $args['msg'],
        $action
    );
  }
}

/**
 * Verify an ajax nonce
 */
function pl_verify_ajax( $nonce ) {
  $nonce = wp_verify_nonce( $nonce, 'pl-ajax-nonce' );
  if ( ! $nonce ) {
    die( 'Unable to verify AJAX nonce' );
  }
}

/**
 * Get Ajax URL.
 * @return string
 */
function pl_ajax_url() {
  return admin_url( 'admin-ajax.php', 'relative' );
}

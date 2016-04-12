<?php
/**
* PL Shortcodes Admin
*
* @class     PL_Shortcodes_Engine
* @version   5.0.0
* @package   PageLines/Classes
* @category  Class
* @author    PageLines
*/
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}

$GLOBALS['pl_shortcode_engine'] = new PL_Shortcodes_Engine;

class PL_Shortcodes_Engine {

  public $shortcodes  = array();

  public $scripts     = array();

  public $styles     = array();

  function __construct() {

    //$this->slug = 'pl-shortcodes';

    /** Add the Shortcodes...  */
    add_action( 'wp', array( $this, 'create_shortcodes_array' ) );

    add_action( 'wp_footer',    array( $this, 'print_shortcode_styles' ) );
    add_action( 'wp_footer',  array( $this, 'print_shortcode_scripts' ) );

    add_action( 'admin_init', array( $this, 'create_shortcodes_array' ) );

    /** Tools for adding shortcodes */
    if ( $this->is_edit_post() ) {

      add_action( 'media_buttons', array( $this, 'pl_shortcodes_button' ), 1500 );

      add_action( 'admin_footer', array( $this, 'pl_shortcodes_iframe' ) );

      add_filter( 'pl_admin_json', array( $this, 'json' ) );

    }

    /** Keep the paragraph and break tags away */
    add_filter( 'the_content', array( $this, 'pl_shortcodes_fix' ) );

  }

  function print_shortcode_scripts() {

    foreach ( $this->scripts as $script ) {
      echo $script;
    }

  }

  function print_shortcode_styles() {

    if ( ! empty( $this->styles ) ) {

      $out = '';

      foreach ( $this->styles as $style ) {
        if ( '' != $style ) {
          $out .= $style;
        }
      }

      if ( $out ) {
        printf( '<style class="pl-shortcode-styles">%s</style>', $out );
      }
    }
  }

  function is_edit_post() {

    global $pagenow;

    // Only run on add/edit screens
    if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
      return true; } else {       return false; }

  }

  function create_shortcodes_array() {

    $this->shortcodes_options = array();

    if ( isset( $this->shortcodes ) && is_array( $this->shortcodes ) && ! empty( $this->shortcodes ) ) {

      foreach ( $this->shortcodes as $sc_slug => $sc ) {

        $options = $sc->options();

        if ( 'system' != $options['filter'] ) {
          $this->shortcodes_options[ $sc_slug ] = ( method_exists( $sc, 'options' ) ) ? $sc->options() : array();

        }

        add_shortcode( $sc_slug, array( $sc, 'shortcode' ) );

      }
    }

  }

  function json( $array ) {

    global $plfactory;
    // add shortcodes into JSON
    $array['shortcodes'] = $this->shortcodes_options;

    // loops through available sections
    // if they have system filter set in header do NOT allow them to be shortcodes.
    foreach ( $array['sections'] as $k => $section ) {
      $s = $plfactory->factory[ $section ];
      $filter = $s->settings['filter'];
      if ( false !== strpos( $filter, 'system' ) ) {
        unset( $array['sections'][ $k ] );
      }
    }
    return $array;
  }

  /**
   * Draw shortcode button
   */
  function pl_shortcodes_button( $editor_id = 'content' ) {

    $button = '<a href="#/?TB_inline&width=750&height=500&inlineId=pl-select-shortcode" class="thickbox pl-shortcode-tb button" title=""><i class="pl-icon pl-icon-pagelines"></i> Shortcodes</a>';

    echo $button;

  }



  function pl_shortcodes_iframe() {

    ?><div id="pl-select-shortcode" style="display: none;">

    <div class="plsc">
      <div class="plsc-engine">

        <div class="plsc-workarea plsc-options-container">
        </div>
        <div class="plsc-actions">
          <input type="button" class="button-primary plsc-insert" value="Insert Shortcode" />
          <a href="#" class="button-secondary" onclick="tb_remove();">Cancel</a>
          <a href="#" class="button plsc-show-list"><i class="pl-icon pl-icon-chevron-left"></i> Back to list</a>
        </div>
      </div>
      <div class="plsc-list">
        <div class="plsc-iframe-label">Available PageLines Shortcodes</div>
        <div class="plsc-drawer plsc-workarea fix">
          <?php

          foreach ( $this->shortcodes_options as $id => $shortcode ) {

            $icon = (isset( $shortcode['icon'] )) ? $shortcode['icon'] : 'pencil';

            $shortcode_item = sprintf('<div class="plsc-item"><a class="btn-add-shortcode" href="#" id="%s" data-key="%s"><i class="pl-icon pl-icon-%s"></i> %s</a></div> ',
                $id,
                $id,
                $icon,
                $shortcode['title']
            );
            echo $shortcode_item;
          } ?>
          <div class="clear"></div>
        </div>
        </div>
      </div>
    </div>

  </div>
    <?php

  }

  /**
   * Fixes <br> & <p> tag spacing added by wpautop WordPress formatting
   */
  function pl_shortcodes_fix( $content ) {
      $array = array(
          '<p>[' => '[',
          ']</p>' => ']',
          ']<br />' => ']',
      );

      $content = strtr( $content, $array );
      return $content;
  }
}

/**
 * Shortcode Base Class
 * Provides a method for handling scripts.
 * When the shortcode is called, it calls and assigns the script function
 * This means the script will only be included if the shortcode is used, and the script will
 * only be output to the page once
 */
class PL_Shortcode {

  function __construct() {
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
  }

  function enqueue_scripts(){}

  /**
   * Main shortcode function, each shorcode will override
   */
  function shortcode( $atts, $content ) {

    global $pl_shortcode_engine;
    global $post;

    if ( ! isset( $atts['post_id'] ) ) {

      $atts['post_id'] = ( isset( $post ) ) ? $post->ID : false;

    }

    $pl_shortcode_engine->scripts[ get_class( $this ) ] = $this->scripts();

    $pl_shortcode_engine->styles[ get_class( $this ) ] = $this->styles();

    return $this->template( $atts, $content );

  }

  /**
   * Template for the shortcode
   */
  function template( $atts, $content ) {
    return '';
  }

  function options() {
    return array();
  }

  function scripts() {
    return '';
  }

  function styles() {
    return '';
  }
}

/**
 * Utility function for adding new shortcodes
 */
function pl_add_shortcode( $slug, $object ) {

  global $pl_shortcode_engine;

  $pl_shortcode_engine->shortcodes[ $slug ] = $object;

}


class PLSC_Section extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'PageLines Section',
      'icon'        => 'random',
      'desc'        => 'Add a section using a shortcode.',
      'filter'      => 'utility',
      'settings'    => array(
        'section' => array(

          'type'    => 'select_section',
          'label'   => 'Section ID',
          'desc'    => 'Select the ID of section you want.',
        ),
        'id' => array(

          'type'    => 'text',
          'label'   => 'Unique ID',
          'place'   => 'my_unique_id',
          'default' => rand(),
          'desc'    => 'Add a unique section ID. Can be any combination of numbers and letters. <strong>Tip!</strong> Reference the same section data across pages by using the same ID.',
        ),
      ),
    );

    return $opts;
  }

  /**
   * Template for the shortcode
   */
  function template( $atts, $content ) {

      extract(shortcode_atts(array(
          'section'     => 'content',
          'id'          => ( isset( $atts['section'] ) ) ? $atts['section'] . pl_edit_id() : false,
          'settings'    => array(),
      ), $atts));

      global $plfactory;

      if ( ! isset( $plfactory->section_ids[ $section ] ) ) {

          pl_missing_section( $section );
          return;
      } else {
          $object = $plfactory->section_ids[ $section ];
      }

      $s = $plfactory->factory[ $object ];

      $s->meta = array(
          'clone'     => $id,
          'object'    => $object,
          'content'   => array(),
      );

      /** Allow for a function that can be used to set defaults */
      $settings = get_section_data( $s );

      $plfactory->add_section_to_factory( $id, $object, $settings );

      $s->meta['set'] = $settings;

      ob_start();

      $s->section_styles();

      /** Auto load build.css document which is generated from build.less */
      if ( is_file( $s->base_dir . '/build.css' ) ) {
          pl_style( $section, $s->base_url . '/build.css' ); }

      if ( is_file( $s->base_dir . '/style.css' ) ) {
          pl_style( $section, $s->base_url . '/style.css' ); }

      echo '<div class="temp-wrap pl-sn-shortcode" data-bind="stopBinding: true" >';

      $s->render( $s->meta );

      echo "\n</div>\n";

      wp_reset_postdata(); // Reset $post data
      wp_reset_query(); // Reset wp_query

      return apply_filters( 'pl_section', ob_get_clean(), $atts, $content );
  }
}

pl_add_shortcode( 'pl_section', new PLSC_Section );


class PL_FB_Like extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Facebook Like',
      'icon'        => 'facebook',
      'desc'        => 'Create a Facebook Like button',
      'filter'      => 'localsocial',
      'settings'    => array(
        'url' => array(
          'place'   => 'http://www.facebook.com/pagelines',
          'type'    => 'text',
          'label'   => 'Like URL',
          'desc'    => 'Defaults to your facebook page based on username.',
        ),
      ),
    );

    return $opts;
  }

  function template( $atts, $content ) {

      global $post;

      extract(shortcode_atts(array(
          'url'     => pl_get_current_url( false ),
      ), $atts));

      $out = sprintf( '<div class="fb-like" data-href="%s" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>', $url );

      return $out;
  }

  function styles() {
    return '.fb-like{vertical-align: top; line-height: 20px; margin-right: .5em;}';
  }

  function scripts() {

      ob_start(); ?>

      <div id="fb-root"></div>
      <script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.4&appId=827338790610682";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));</script>

<?php
      return ob_get_clean();
  }
}

pl_add_shortcode( 'pl_facebook_like', new PL_FB_Like );

class PL_Twitter_Follow extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Twitter Follow',
      'icon'        => 'twitter',
      'desc'        => 'Create a Twitter Follow button',
      'filter'      => 'localsocial',
      'settings'    => array(
        'username' => array(
          'place'   => 'pagelines',
          'type'    => 'text',
          'label'   => 'Twitter Username',
        ),
      ),
    );

    return $opts;
  }

  function template( $atts, $content ) {

      extract(shortcode_atts(array(
          'username' => pl_user_setting( 'username_twitter', 'pagelines' ),
      ), $atts));

      $out = sprintf('<a href="http://www.twitter.com/%s" class="twitter-follow-button" data-show-count="true" data-show-screen-name="false"></a>',
          $username,
          $username
      );

      return $out;
  }

  function styles() {
    return '.twitter-follow-button{vertical-align: top; line-height: 20px; margin-right: .5em;}';
  }


  function scripts() {

      ob_start(); ?>
      <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
<?php
      return ob_get_clean();
  }
}
pl_add_shortcode( 'pl_twitter_follow', new PL_Twitter_Follow );


/**
 * SHORTCODE: Get Site Logo URL
 */
class PL_Logo_URL extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Site Logo URL',
      'icon'        => 'link',
      'desc'        => 'The URL of your site logo',
      'filter'      => 'images',
    );

    return $opts;
  }

  function template( $atts, $content ) {
      return pl_get_site_logo( false );
  }
}

pl_add_shortcode( 'pl_logo_url', new PL_Logo_URL );

/**
 * SHORTCODE: Get Site Logo URL
 */
class PL_Logo_Img extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Site Logo IMG',
      'icon'        => 'photo',
      'desc'        => 'HTML for Site Logo',
      'filter'      => 'images',
      'settings'    => array(
        'href' => array(
          'place'   => get_bloginfo( 'url' ),
          'type'    => 'text',
          'label'   => 'Image Link',
        ),
        'title' => array(
          'place'   => get_bloginfo( 'name' ),
          'type'    => 'text',
          'label'   => 'Image Alt Text',
        ),
      ),
    );

    return $opts;
  }

  function template( $atts, $content ) {

    $defaults = array(
      'href'  => get_bloginfo( 'uri' ),
      'title' => get_bloginfo( 'name' ),
      'target'  => '_parent',
    );
    $atts = wp_parse_args( $atts, $defaults );

    return sprintf( '<a target="%s" href="%s"><img title="%s" src="%s" /></a>',
        $atts['target'],
        $atts['href'],
        $atts['title'],
        pl_get_site_logo( false )
    );
  }
}

pl_add_shortcode( 'pl_logo_img', new PL_Logo_Img );


/**
 * SHORTCODE: Get Post Time
 */
class PL_Post_Time extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Post Time',
      'icon'        => 'circle-o',
      'desc'        => 'The time the post was created',
      'filter'      => 'posts',
    );

    return $opts;
  }

  function template( $atts, $content ) {

      $defaults = array(
        'format'  => get_option( 'time_format' ),
        'before'  => '',
        'after'   => '',
        'label'   => '',
      );
      $atts = wp_parse_args( $atts, $defaults );

      $output = sprintf( '<time class="time published sc" datetime="%5$s">%1$s%3$s%4$s%2$s</time> ',
          $atts['before'],
          $atts['after'],
          $atts['label'],
          get_the_time( $atts['format'],  $atts['post_id'] ),
          get_the_time( 'Y-m-d\TH:i:sO',  $atts['post_id'] )
      );

      return apply_filters( 'plsc_post_time', $output, $atts );

  }
}

pl_add_shortcode( 'post_time', new PL_Post_Time );

/**
 * SHORTCODE: Get Post Date
 */
class PL_Post_Date extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Post Date',
      'icon'        => 'circle-o',
      'desc'        => 'The date the post was created',
      'filter'      => 'posts',
    );

    return $opts;
  }

  function template( $atts, $content ) {
      $defaults = array(
        'format'  => get_option( 'date_format' ),
        'before'  => '',
        'after'   => '',
        'label'   => '',
      );

      $atts = wp_parse_args( $atts, $defaults );

      $output = sprintf( '<time class="date time published updated sc" datetime="%5$s">%1$s%3$s%4$s%2$s</time> ',
          $atts['before'],
          $atts['after'],
          $atts['label'],
          get_the_time( $atts['format'], $atts['post_id'] ),
          get_the_time( 'c', $atts['post_id'] )
      );

      return apply_filters( 'plsc_post_date', $output, $atts );
  }
}

pl_add_shortcode( 'post_date', new PL_Post_Date );

/**
 * SHORTCODE: Get Post Categories
 */
class PL_Post_Categories extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Post Categories',
      'icon'        => 'circle-o',
      'desc'        => 'The categories assigned to the post.',
      'filter'      => 'posts',
    );

    return $opts;
  }

  function template( $atts, $content ) {
    $defaults = array(
      'sep' => ', ',
      'before' => '',
      'after' => '',
    );

    $atts = wp_parse_args( $atts, $defaults );

    $cats = get_the_category_list( trim( $atts['sep'] ) . ' ' );

    $output = sprintf( '<span class="categories sc">%2$s%1$s%3$s</span> ', $cats, $atts['before'], $atts['after'] );

    return apply_filters( 'plsc_post_categories', $output, $atts );
  }
}

pl_add_shortcode( 'post_categories', new PL_Post_Categories );

/**
 * SHORTCODE: Get Post Tags
 */
class PL_Post_Tags extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Post Tags',
      'icon'        => 'circle-o',
      'desc'        => 'The tags assigned to the post.',
      'filter'      => 'posts',
    );

    return $opts;
  }

  function template( $atts, $content ) {
    $defaults = array(
      'sep' => ', ',
      'before' => __( 'Tagged With: ', 'pl-platform' ),
      'after' => '',
    );
    $atts = shortcode_atts( $defaults, $atts );

    $tags = get_the_tag_list( $atts['before'], trim( $atts['sep'] ) . ' ', $atts['after'] );

    if ( ! $tags ) { return; }

    $output = sprintf( '<span class="tags sc">%s</span> ', $tags );

    return apply_filters( 'pagelines_post_tags_shortcode', $output, $atts );
  }
}

pl_add_shortcode( 'post_tags', new PL_Post_Tags );

/**
 * SHORTCODE: Get Post Comments Info / Link
 */
class PL_Post_Comments extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Post Comments Link',
      'icon'        => 'circle-o',
      'desc'        => 'The comments info link for the post.',
      'filter'      => 'posts',
    );

    return $opts;
  }

  function template( $atts, $content ) {

    $defaults = array(
      'zero'    => __( 'Add Comment', 'pl-platform' ),
      'one'     => __( '1 Comment',   'pl-platform' ),
      'more'    => __( '% Comments',  'pl-platform' ),
      'hide_if_off' => 'disabled',
      'before'  => '',
      'after'   => '',
      'output'  => 'span',
    );
    $atts = wp_parse_args( $atts, $defaults );

    if ( ( ! comments_open() ) && 'enabled' === $atts['hide_if_off'] ) {
      return; }

    // Prevent automatic WP Output
    ob_start();
      comments_number( $atts['zero'], $atts['one'], $atts['more'] );
    $comments = ob_get_clean();

    $comments = sprintf( '<a href="%s#comments">%s</a>', get_permalink(), $comments );

    if ( 'link' == $atts['output'] ) {
      $output = $comments; } else {       $output = sprintf( '<span class="post-comments sc">%2$s%1$s%3$s</span>', $comments, $atts['before'], $atts['after'] ); }

    return apply_filters( 'plsc_post_comments', $output, $atts );

  }
}

pl_add_shortcode( 'post_comments', new PL_Post_Comments );

/**
 * SHORTCODE: Get Post Edit Link
 */
class PL_Post_Edit_Link extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => __( 'Post Edit Link', 'pl-platform' ),
      'icon'        => 'circle-o',
      'desc'        => __( 'Gets a post edit link for the post.', 'pl-platform' ),
      'filter'      => 'posts',
    );

    return $opts;
  }

  function template( $atts, $content ) {

    $defaults = array(
      'link' => sprintf( "<span class='editpage sc'>(%s)</span>", __( 'Edit', 'pl-platform' ) ),
      'before' => '',
      'after' => '',
    );
    $atts = wp_parse_args( $atts, $defaults );

    // Prevent automatic WP Output
    ob_start();
    edit_post_link( $atts['link'], $atts['before'], $atts['after'] ); // if logged in
    $edit = ob_get_clean();

    $output = $edit;

    return apply_filters( 'plsc_post_edit', $output, $atts );

  }
}

pl_add_shortcode( 'post_edit', new PL_Post_Edit_Link );

/**
 * SHORTCODE: Get Post Author Link
 */
class PL_Post_Author extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Post Author Link',
      'icon'        => 'circle-o',
      'desc'        => 'Gets a post author link for the post.',
      'filter'      => 'posts',
    );

    return $opts;
  }

  function template( $atts, $content ) {

    $defaults = array(
      'before' => '',
      'after' => '',
      'class' => '',
    );
    $atts = wp_parse_args( $atts, $defaults );

    // Prevent automatic WP Output
    ob_start();
    the_author_posts_link();
    $author = ob_get_clean();

    $output = sprintf( '<span class="author vcard sc %4$s">%2$s<span class="fn">%1$s</span>%3$s</span>', $author, $atts['before'], $atts['after'], $atts['class'] );

    return apply_filters( 'pagelines_post_author_shortcode', $output, $atts );

  }
}

pl_add_shortcode( 'post_author', new PL_Post_Author );

/**
 * SHORTCODE: Get Child Theme URL
 */
class PLSC_Child_URL extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Child Theme URL',
      'icon'        => 'file',
      'desc'        => 'The URL for the child theme or parent theme if none is active.',
      'filter'      => 'system',
    );

    return $opts;
  }

  function template( $atts, $content ) {
    return get_stylesheet_directory_uri();
  }
}

pl_add_shortcode( 'pl_child_url', new PLSC_Child_URL );

/**
 * SHORTCODE: Get Parent Theme URL
 */
class PLSC_Parent_URL extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Parent Theme URL',
      'icon'        => 'file',
      'desc'        => 'The URL for the parent theme.',
      'filter'      => 'system',
    );

    return $opts;
  }

  function template( $atts, $content ) {
    return get_template_directory_uri();
  }
}

pl_add_shortcode( 'pl_parent_url', new PLSC_Parent_URL );

/**
 * SHORTCODE: Get Site URL
 */
class PLSC_Site_URL extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Site URL',
      'icon'        => 'file',
      'desc'        => 'The basic site URL.',
      'filter'      => 'system',
    );

    return $opts;
  }

  function template( $atts, $content ) {
      return site_url();
  }
}

pl_add_shortcode( 'pl_site_url', new PLSC_Site_URL );

/**
 * SHORTCODE: Get Uploads URL
 */
class PLSC_Uploads_URL extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Uploads Directory URL',
      'icon'        => 'file',
      'desc'        => 'The basic site Uploads URL.',
      'filter'      => 'system',
    );

    return $opts;
  }

  function template( $atts, $content ) {

    $upload_dir = wp_upload_dir();
    return $upload_dir['baseurl'];
  }
}

pl_add_shortcode( 'pl_uploads_url', new PLSC_Uploads_URL );


/**
 * SHORTCODE: Get Site URL
 */
class PLSC_Home_URL extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Home URL',
      'icon'        => 'file',
      'desc'        => 'The basic home URL.',
      'filter'      => 'system',
    );

    return $opts;
  }

  function template( $atts, $content ) {
    return home_url();
  }
}

pl_add_shortcode( 'pl_home_url', new PLSC_Home_URL );

/**
 * SHORTCODE: Get Framework Images URL
 */
class PLSC_Images_URL extends PL_Shortcode{

  function options() {
    $opts = array(
      'title'       => 'Framework Images URL',
      'icon'        => 'file',
      'desc'        => 'The site image folder URL.',
      'filter'      => 'system',
    );

    return $opts;
  }

  function template( $atts, $content ) {
    return pl_get_template_directory_uri() . '/ui/images/';
  }
}

pl_add_shortcode( 'pl_images_url', new PLSC_Images_URL );

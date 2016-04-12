<?php

/** Start the engines ... */
add_action( 'after_setup_theme', 'framework_load_controller' );

function framework_load_controller() {
  global $pl_controller;
  $pl_controller = new PL_Controller;
}
/**
 * Master controller class for the framework
 */
class PL_Controller {


  function __construct() {

    $this->include_files();

    //add_filter('pl_dynamic_templates', array( $this, 'dynamic_templates') );

    load_theme_textdomain( 'pl-framework', get_template_directory() . '/languages' );


    $this->content = new PL_Framework_Content;
    
    // if platform is not installed we have to fail gracefully and let the user install Platform
    // we will not have any of the tools available, so we can only use basic WP functions.
    if( ! function_exists( 'PL_Platform' ) ) {
      include_once( 'libs/ops.php' );
      new PL_Framework_Ops();
      return;
    }

    if( ! pl_tools_active() ){
      add_action( 'wp_head',           array( $this, 'site_meta' )); 
      add_action( 'wp_head',           array( $this, 'setting_css' ), 1000); 
    }
    
    

    add_action( 'init',                array( $this, 'pl_theme_support'));
    add_action( 'init',                array( $this, 'pl_check_sidebar_markup'));

    add_action( 'wp_enqueue_scripts',  array( $this, 'enqueue' ));



    
  }

  function enqueue(){
    /** If child theme wants to include parent style.css */
    if ( is_child_theme() && ! pl_user_setting('disable_parent_styles') ){
      
      wp_enqueue_style( 'core', get_template_directory_uri().'/style.css', array(), filemtime( get_template_directory() . '/style.css' ) );
    }
    
    wp_enqueue_style( 'style', get_stylesheet_directory_uri().'/style.css', array(), filemtime( get_stylesheet_directory() . '/style.css' ) );
  }

  function dynamic_templates( $templates ){
    $templates['index'] = 'index'; 

    return $templates;
  }

  function include_files(){

    $pl_inc = array(            
      'settings',
    );


    foreach ( $pl_inc as $file ) {

      require_once get_template_directory() .'/'. $file . '.php';
    }
    unset($file, $filepath);
  }



  function site_meta(){
    
    // Meta Images
    echo pl_favicon();
  }




  /**
   * Support optional WordPress functionality 'add_theme_support'
   */

  function pl_theme_support(  ){

    /** WordPress sets this wrong */
    global $content_width; 
    $content_width = 2000;


    /** Support Post Featured Images */
    add_theme_support( 'post-thumbnails' );

    /** Support Media Post Formats */
    add_theme_support( 'post-formats', array(
      'quote', 'video', 'audio', 'gallery', 'link'
    ) );

    /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
    add_theme_support( 'html5', array(
      'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
    ) );

    /** Supported Image Thumb Sizes */
    add_image_size( 'aspect-thumb',     1500, 1000, true );
    add_image_size( 'basic-thumb',      750,    750,  true );
    add_image_size( 'landscape-thumb',  1500, 750,  true );
    add_image_size( 'tall-thumb',       750,    1500, true );
    add_image_size( 'big-thumb',        1500, 1500, true );

    /** Support WP Menus System */
    add_theme_support( 'menus' );

    /** Generate Feed Links in Header */
    add_theme_support( 'automatic-feed-links' );

    /** Support WooCommerce */
    add_theme_support( 'woocommerce' );

    /*
     * Let WordPress manage the document title.
     * By adding theme support, we declare that this theme does not use a
     * hard-coded <title> tag in the document head, and expect WordPress to
     * provide it for us.
     */
    add_theme_support( 'title-tag' );
    
    // Tell PL5 that we are a supported theme.
    add_theme_support( 'pagelines' );
  }


  function pl_check_sidebar_markup( ){

    global $wp_registered_sidebars;

    foreach( $wp_registered_sidebars as &$sb ){
      if( $sb['before_widget'] == '' ){

        $sb['before_widget'] = '<div id="%1$s" class="widget %2$s">';
        $sb['after_widget']  = '</div>';
        $sb['before_title']  = '<h3 class="widgettitle">';
        $sb['after_title']   = '</h3>';

      }
    }
    unset($sb);

  }

  function setting_css(){

      ob_start();

      echo pl_user_setting('font_extra');

      $out = '';

      if( pl_user_setting('base_font_size') )
        $out .= sprintf('html{ font-size: %spx; }', pl_user_setting('base_font_size') );

      if( pl_user_setting('font_primary') )
        $out .= sprintf('body{ %s }', pl_user_setting('font_primary') );

      if( pl_user_setting('font_headers') )
        $out .= sprintf('h1, h2 { %s }', pl_user_setting('font_headers') );
        
      if( pl_user_setting('font_headers_minor') )
        $out .= sprintf('h3, h4, h5, h6{ %s }', pl_user_setting('font_headers_minor') );

      if( pl_user_setting('bodybg') || pl_user_setting('background_image') ) {
        
        $bg_image = '';
        
        $image_style = pl_user_setting( 'background_style' );
        $image = pl_user_setting( 'background_image' );

        if( $image ) {
          
          if( 'cover' == $image_style || 'contain' == $image_style ) {
            $bg_image = sprintf( 'background-image: url(%s);background-repeat:no-repeat;background-size:%s;background-attachment:fixed;background-position:center', $image, $image_style );
          } 

          else {
            $bg_image = sprintf( 'background-image: url(%s);background-repeat:repeat;background-size:%s%% auto;', $image, pl_user_setting( 'background_image_size' ) );
          }
        }
        
        $out .= sprintf('.site-wrap{%s}', $bg_image );
      }

      $bg_color = ( pl_user_setting('bodybg') && pl_user_setting('bodybg') != '#' ) ? pl_user_setting('bodybg') : false;

      if( $bg_color )
        $out .= sprintf('body{ background-color: %s}', $bg_color );

      $footer_bg = ( pl_user_setting('footerbg') && pl_user_setting('footerbg') != '#' ) ? pl_user_setting('footerbg') : false;

      if( $footer_bg )
        $out .= sprintf('.footer{ background-color: %s}', $footer_bg );

      if( pl_user_setting('layout_width') )
        $out .= sprintf('body .pl-content-area{ max-width: %s; }', pl_user_setting('layout_width') );

      if( pl_user_setting('read_width') )
        $out .= sprintf('body .pl-read-width{ max-width: %sem; }', pl_user_setting('read_width') );


      if( pl_user_setting('linkcolor') && pl_user_setting('linkcolor') != '#' )
        $out .= sprintf('a, a:hover{ color: %s; }', pl_user_setting('linkcolor') );

  ?>
  <style id="pl-framework-settings-css">
  <?php echo $out;?>
  </style>
  <?php

  }
  
}

class PL_Framework_Content {

  function __construct(){


    add_action( 'comment_form_before',              array( $this, 'comment_form_js' ) );

    add_filter( 'pl_binding_media_size',            array( $this, 'callback_media_size'), 10, 2);

    add_filter( 'pl_platform_settings_array',       array($this, 'settings') );

    add_filter( 'pl_platform_meta_settings_array',  array($this, 'meta_settings') );

    register_sidebar( array(
        'id'          => 'primary',
        'name'        => __( 'Primary', 'pl-framework' )
    ) );

    register_sidebar( array(
        'id'          => 'secondary',
        'name'        => __( 'Secondary', 'pl-framework' )
    ) );

    register_sidebar( array(
        'id'          => 'tertiary',
        'name'        => __( 'Tertiary', 'pl-framework' )
    ) );
  }

  /**
   * Enqueue special WP script for threaded comments
   */
  function comment_form_js() {

    if ( get_option( 'thread_comments' ) )
      wp_enqueue_script( 'comment-reply' );
  }


  function settings( $settings ){

    $settings['content_section'] = array(
        'key'       => 'content_section',
        'icon'      => 'file-text-o',
        'pos'       => 50,
        'title'     => __( 'Content', 'pl-framework' ),
        'opts'  => array(

          array(
            'key'       => 'metabar',
            'type'      => 'text',
            'place'     => $this->default_metabar(),
            'title'     => __( 'Post Metabar', 'pl-framework' ),
            'help'      => __( 'Configure the meta information that will show below post titles. Use shortcodes to create dynamic content, such as [post_date].</p>', 'pl-framework' )
          ),
          array(
            'key'       => 'page_title_format',
            'type'      => 'select',
            'default'   => 'format_center',
            'title'     => __( 'Page Title Format', 'pl-framework' ),
            'help'      => __( 'Control how page titles are displayed by default. Can be overridden on a page by page basis.', 'pl-framework' ),
            'opts' => array(
                'format_hidden' => array( 'name' => __( 'Hidden', 'pl-framework' ) ),
                'format_center' => array( 'name' => __( 'Center Aligned', 'pl-framework' ) ),
                'format_left'   => array( 'name' => __( 'Left Aligned', 'pl-framework' ) ),
            ),
          ),
          array(
            'key'       => 'post_title_format',
            'type'      => 'select',
            'default'   => 'format_center',
            'title'     => __( 'Post Title Format', 'pl-framework' ),
            'help'      => __( 'Control how post titles are displayed by default. Can be overridden on a page by page basis.', 'pl-framework' ),
            'opts' => array(
                'format_center' => array( 'name' => __( 'Center Aligned', 'pl-framework' ) ),
                'format_left'   => array( 'name' => __( 'Left Aligned', 'pl-framework' ) ),
            ),
          ),
          array(
            'key'        => 'media_size',
            'type'       => 'select_imagesizes',
            'default'    => 'aspect-thumb',
            'label'     => __( 'Select Featured Image Size', 'pl-framework' ),
            'title'     => __( 'Featured Image Size', 'pl-framework' ),
            'help'      => __( 'Select from a variety of different size and aspect values for your featured images in posts.', 'pl-framework' )
          )
        )
      );
    return $settings ;
  }

  function meta_settings( $settings ){

    if( 'page' == get_current_screen()->post_type ){

      $settings['page_titles'] = array(
        'key'       => 'pl_title',
        'icon'      => 'file-o',
        'pos'       => 14,
        'location'  => 'page',
        'title'     => __( 'Page Title', 'pl-framework' ),
        'opts'  => array(

          array(
           'key'           => '_pl_title',
           'type'          => 'text',
           'title'         => __( 'Title (Override)', 'pl-framework' ),
           'help'          => __( 'Overrides the default WP page title.', 'pl-framework' ),
          ),

          array(
           'key'           => '_pl_title_sub',
           'type'          => 'text',
           'title'         => __( 'Sub Title', 'pl-framework' ),
           'help'          => __( 'Shows underneath the page title', 'pl-framework' ),
          ),

          array(
            'key'           => '_pl_title_icon',
            'type'          => 'select_icon',
            'title'         => __( 'Title Icon', 'pl-framework' ),
            'help'          => __( 'Adds an icon to the page.', 'pl-framework' ),
          ),

          array(
            'key'           => '_pl_title_format',
            'type'          => 'select',
            'title'         => __( 'Title Format', 'pl-framework' ),
            'opts' => array(
                    'format_center' => array( 'name' => __( 'Center Aligned', 'pl-framework' ) ),
                    'format_left'   => array( 'name' => __( 'Left Aligned', 'pl-framework' ) ),
            ),
            'help'          => __( 'Select the format style of page title.', 'pl-framework' ),
          ),

          array(
            'key'           => '_pl_title_hide',
            'type'          => 'checkbox',
            'title'         => __( 'Hide Page Title?', 'pl-framework' ),
            'help'          => __( 'Hide the title area on this page.', 'pl-framework' ),
          ),

          // array(
          //   'key'           => '_pl_hide_image',
          //   'type'          => 'checkbox',
          //   'title'         => __( 'Hide Featured Image (If Set)', 'pagelines' ),
          // ),

        )
      );

    }

    if( post_type_supports( get_current_screen()->post_type, 'post-formats' ) ){

      $settings['post_formats'] = array(
        'key'       => 'about',
        'icon'      => 'thumb-tack',
        'pos'       => 15,
        'location'  => 'post',
        'title'     => __( 'Post Formats', 'pl-framework' ),
        'opts'  => array(

          array(
           'key'           => '_pl_quote',
           'type'          => 'text',
           'title'         => __( 'Quote Text', 'pl-framework' ),
           'help'          => __( 'When using quote post format, enter the quote text here.', 'pl-framework' ),
          ),

          array(
            'key'           => '_pl_link',
            'type'          => 'text',
            'title'         => __( 'Link URL', 'pl-framework' ),
            'help'          => __( 'Enter link URL here', 'pl-framework' ),
          ),

          array(
            'type'          => 'multi',
            'title'         => __( 'Video Format', 'pl-framework' ),
            'opts'          => array(
                array(
                  'key'           => '_pl_video',
                  'type'          => 'video_upload',
                  'label'         => __( 'MP4 Video File', 'pl-framework' ),
                  'help'          => __( 'Enter the URL for your MP4 file here.', 'pl-framework' ),
                ),
                array(
                  'key'           => '_pl_video_poster',
                  'type'          => 'image_upload',
                  'label'         => __( 'Preview Image', 'pl-framework' ),
                ),
                array(
                  'key'           => '_pl_video_embed',
                  'type'          => 'textarea',
                  'kses'          => 'bypass',
                  'label'         => __( 'Embedded Code', 'pl-framework' ),
                )
              )
          ),
          array(
            'type'          => 'multi',
            'title'         => __( 'Audio Format', 'pl-framework' ),
            'opts'          => array(
                array(
                  'key'           => '_pl_audio_mp3',
                  'type'          => 'audio_upload',
                  'label'         => __( 'MP3 Audio File', 'pl-framework' ),
                  'help'          => __( 'Enter the URL for your MP4 file here.', 'pl-framework' ),
                )
              )
          )
        )
      );
    }

    return $settings;

  }

  function section_opts(){


    $opts = array(
      array(
        'key'     => 'post_content',
        'type'    => 'edit_post',
        'label'   => __( '<i class="pl-icon pl-icon-pencil"></i> Edit Post Info', 'pl-framework' ),
        'help'    => __( 'This section uses WordPress posts. Edit post information using WordPress admin.', 'pl-framework' ),
        'classes' => 'pl-btn-primary'
      ),
      array(
        'type'    => 'multi',
        'label'   => __( 'Layout', 'pl-framework' ),

        'opts'    => array(
          array(
            'key'        => 'content_layout',
            'type'       => 'select',
            'label'       => __( 'Select Layout', 'pl-framework' ),
            'opts' => array(
                      
                      'read-width'          => array( 'name' => __( 'Read Width Content', 'pl-framework' ) ),
                      'one-sidebar-right'   => array( 'name' => __( 'One Sidebar Right', 'pl-framework' ) ),
                      'one-sidebar-left'    => array( 'name' => __( 'One Sidebar Left', 'pl-framework' ) ),
                      'two-sidebar-center'  => array( 'name' => __( 'Left / Right Sidebar', 'pl-framework' ) ),
                      'two-sidebar-right'   => array( 'name' => __( 'Two Sidebar Right', 'pl-framework' ) ),
                      'two-sidebar-left'    => array( 'name' => __( 'Two Sidebar Left', 'pl-framework' ) ),
                      'full-width'          => array( 'name' => __( 'Full Width Content', 'pl-framework' ) ),
              )
          )
        )
      ),
      array(
        'type'   => 'multi',

        'title'  => 'Sidebars',
        'label'  => __( 'Sidebars', 'pl-framework' ),

        'opts'   => array(
          array(
            'key'       => 'sb1',
            'type'      => 'select_sidebar',
            'label'     => __( 'Select Sidebar 1', 'pl-framework' ),

          ),
          array(
            'key'       => 'sb2',
            'type'      => 'select_sidebar',
            'label'     => __( 'Select Sidebar 2', 'pl-framework' ),

          ),
         
        )
      )
    );

    return $opts;
  }

  function default_metabar(){

    return sprintf( '[post_date] %s [post_author] [post_edit]', __( 'by', 'pl-framework' ) );
  }

  /**
  * Section template.
  */
   function section_template() {

      if( pl_is_static_template('sec template') ){

        global $pl_static_template_output;

        $binding = "plclassname: [ (tplwrap() == 'wrapped') ? 'pl-content-area pl-content-layout' : '' ]";

        printf( '<div class="pl-page-content" data-bind="%s">%s</div>', $binding, $pl_static_template_output );

      }

      else{

        $this->get_loop();

      }


  }



  /*
   * Decide which loop we need and load it.
   */
  function get_loop() {

    global $wp_query;

    if( isset( $wp_query ) && is_object( $wp_query ) ){

      $format_class = ( $wp_query->post_count > 1 ) ? 'multi-post' : 'single-post';
    }
    ?>
    <div class="pl-content-area pl-content-layout post-type-<?php echo pl_page_type();?> <?php echo $format_class;?>">
      <div class="pl-row row-nowrap pl-content-container" data-bind="plclassname: content_layout() || 'default-layout'">
        <div class="pl-content-loop pl-col-flex" >
          <?php pl_posts_info();?>

          <?php echo $this->loop(); ?>

          <?php pl_pagination(); ?>

        </div>

        <?php pl_dynamic_sidebar( array( 'key' => 'sb1', 'val' => pl_static_opt('sb1'), 'cls' => 'sb-primary pl-col-flex', 'default' => 'primary', 'binds' => "") ); ?>
        <?php pl_dynamic_sidebar( array( 'key' => 'sb2', 'val' => pl_static_opt('sb2'), 'cls' => 'sb-secondary pl-col-flex', 'default' => 'secondary', 'binds' => "" ) ); ?>

      </div>
    </div>

<?php
  }


  function get_post_classes( $id ){

    $class = array();

    $class[ ] = ( is_archive() || is_search() || is_home() ) ? 'multi-post' : '';

    $class[ ] = ( is_single() ) ? 'single-post' : '';

    $class[ ] = 'pl-border pl-text-wrap';

    $class[ ] = ( get_post_meta( $id, '_pagelines_gallery_slider', true) == 'on' ) ? 'use-flex-gallery' : '';

    $classes = apply_filters( 'pagelines_get_article_post_classes', join( " ", $class) );

    return $classes;
  }

  function get_post_media( $id ){

    $thumb_size = pl_user_setting('media_size', 'landscape-thumb');

    $the_media = $this->post_media( array( 'thumb-size' => $thumb_size ) );

    $media_class = ($the_media != '') ? 'has-media' : 'no-media';

    ?>
    <div class="metamedia <?php echo $media_class;?>" >
      <?php echo $the_media;?>
    </div>
<?php

  }

  function callback_media_size( $response, $data ){

    $media_args = array(
        'thumb-size'   => $data['value']['size'],
        'id'           => $data['value']['id']
    );

    $response['template'] = $this->post_media( $media_args );

    return $response;

  }

  function do_title( $id ){

    $out = ''; 

    $format = get_post_meta( $id, '_pl_title_format', true ); 

    if( ! $format ){

      if( is_page() ){
        $format = pl_user_setting('page_title_format'); 
      }
      else{
        $format = pl_user_setting('post_title_format'); 
      }
      
    }

    $out .= sprintf('<header class="entry-header %s">', $format); 


    $title  = get_post_meta( $id, '_pl_title', true ); 
    $sub    = get_post_meta( $id, '_pl_title_sub', true ); 

    $sub    = ( ! $sub && ! is_page() && get_post_type() != 'page' ) ? do_shortcode( apply_filters( 'pl_content_metabar', pl_user_setting( 'metabar', $this->default_metabar() ) ) ) : false;

    $icon   = get_post_meta( $id, '_pl_title_icon', true );

    $hide = ( get_post_meta( $id, '_pl_title_hide', true ) || 'format_hidden' == $format ) ? true : false;


    if( ! $hide ){

      if( $icon ){
        $out .= sprintf( '<div class="page-title-icon"><i class="pl-icon pl-icon-%s"></i></div>', $icon );  
      }
      
      $title = ( $title ) ? $title : get_the_title( $id ); 

      $title = ( is_page() || is_single() ) ? $title : sprintf('<a href="%s" rel="bookmark">%s</a>', esc_url( get_permalink() ), $title);

      $out .= sprintf('<div class="page-title-text">');
      
      $out .= sprintf( '<h1 class="page-title">%s</h1>', $title );  

      if( $sub ){
        $out .= sprintf( '<h4 class="page-title-sub metabar">%s</h4>', $sub );  
      }

      $out .= sprintf('</div>');

    }

    $out .= sprintf('</header>'); 

    echo $out; 

  }

  function loop(){

    $count = 0;
    global $wp_query;


    if( ! empty( $wp_query->posts ) ){

      while ( have_posts() ) : the_post();

      $count++;
      
      $special = apply_filters( 'pl_content_archive_special', false );

      ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class( $this->get_post_classes( get_the_ID() ) ); ?>>

        <?php $this->get_post_media( get_the_ID() ); ?>

        <?php if( get_post_format() != 'quote' && get_post_format() != 'link' && ! $special ) : ?>
        
            <?php $this->do_title( get_the_ID() ); ?>
        <?php endif; ?>
        
      <div class="entry-content">

      <?php if( is_single() || is_page() || $special ): ?>

        <?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'pl-framework' ) ); ?>

        <?php
          wp_link_pages( array(
              'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'pl-framework' ) . '</span>',
              'after'       => '</div>',
              'link_before' => '<span>',
              'link_after'  => '</span>',
            ) );
        ?>

        <?php if( is_single() ) comments_template(); ?>

      <?php elseif( get_post_format() != 'quote' && get_post_format() != 'link' ): ?>

        <?php  $link = sprintf(
              '<a class="continue_reading_link" href="%s" title="%s %s">(%s)</a>',
              get_permalink(),
              __("Read Full Article", 'pl-framework'),
              the_title_attribute(array('echo'=> 0)),
              __('Read More', 'pl-framework')
            );

            echo pl_excerpt_by_id( false, 55, '<em><strong>', $extra = '&hellip;' . apply_filters('continue_reading_link', $link));
?>

      <?php endif; ?>
    </div>
  </article>
<?php
    endwhile;
  } else
    $this->posts_404();
  }

  function posts_404(){

    $head = ( is_search() ) ? sprintf(__('No results for &quot;%s&quot;', 'pl-framework'), get_search_query()) : __('Nothing Found', 'pl-framework');

    $subhead = ( is_search() ) ? __('Try another search?', 'pl-framework') : __("Sorry, what you are looking for isn't here.", 'pl-framework');

    $the_text = sprintf('<h2 class="center">%s</h2><p class="subhead center">%s</p>', $head, $subhead);

    printf( '<section class="pl-billboard">%s <div class="center fix">%s</div></section>', apply_filters('pagelines_posts_404', $the_text), pl_searchform( false ));

  }


  function post_media( $args = array() ){

    global $post;

    $defaults = array(

      'thumb-size'  => 'aspect-thumb',
      'id'          => ( is_object( $post ) ) ? $post->ID : 0,

    );

    $args = wp_parse_args( $args, $defaults );

    $vars = array(
      'embed'      => get_post_meta( $args['id'], '_pl_video_embed',    true ),
      'm4v'        => get_post_meta( $args['id'], '_pl_video_m4v',      true ),
      'ogv'        => get_post_meta( $args['id'], '_pl_video_ogv',      true ),
      'poster'     => get_post_meta( $args['id'], '_pl_video_poster',   true ),
      'gallery'    => get_post_meta( $args['id'], '_pl_gallery_slider', true ),
      'mp3'        => get_post_meta( $args['id'], '_pl_audio_mp3',      true ),
      'ogg'        => get_post_meta( $args['id'], '_pl_audio_ogg',      true ),
      'quote'      => get_post_meta( $args['id'], '_pl_quote',          true ),
      'link'       => get_post_meta( $args['id'], '_pl_link',           true )
    );



    $args = wp_parse_args( $args, $vars );

    $post_format = get_post_format( $args['id'] );


    // VIDEO
    if( $post_format == 'video' && ( ! empty( $args['embed'] ) || ! empty( $args['m4v'] ) || ! empty( $args['mov'] ) ) ){

        if( !empty( $args['embed'] ) ) {

        $media = sprintf( '<div class="video">%s</div>', do_shortcode( $args['embed'] ) );

      } else {

        $media = sprintf( '<div class="video">[video mp4="%s" ogv="%s"  poster="%s"]</div>', $args['m4v'], $args['ogv'], $args['poster']);

      }
    }

    // QUOTE
    else if( $post_format == 'quote' ){

      $quote = ( $args['quote'] ) ? $args['quote'] : get_the_content();

      $content = sprintf( '<h2 class="entry-title">%s</h2> <span class="author">%s</span><span class="linkbox-icon"><i class="icon icon-quote-right icon-2x"></i></span></h2>', $quote, get_the_title());

      $wrapped = ( is_single()) ? sprintf('<div class="pl-linkbox pl-quote fix">%s</div>', $content ) : sprintf('<a href="%s" class="pl-linkbox pl-quote">%s</a>', get_permalink(), $content );

      $media = $wrapped;
    }

    // LINK
    else if( $post_format == 'link' ){

      $link = $args['link'];

      $link = str_replace( 'http://', '', $link );
      $link = str_replace( 'https://', '', $link );

      $content = sprintf( '<h2 class="entry-title">%s</h2> <span class="destination">%s</span><span class="linkbox-icon"><i class="icon icon-link icon-2x"></i></span></h2>', get_the_title(), $link );

      $wrapped = sprintf('<a href="http://%s" class="pl-linkbox pl-quote fix">%s</a>', $link, $content );

      $media = $wrapped;
    }

    // AUDIO
    else if( $post_format == 'audio' && ( ! empty( $args['mp3'] ) || ! empty( $args['ogg'] ) ) ){

      $audio_output = sprintf('[audio mp3="%s" ogg="%s"]', $args['mp3'], $args['ogg']);

      $media = sprintf( '<div class="pl-audio-player">%s</div>', do_shortcode( $audio_output ) );
    }

    // GALLERY
    else if( $post_format == 'gallery' && !empty( $args['gallery'] ) ){

      $gallery_ids = $this->pl_get_attachment_ids_from_gallery( $args['id'] );



      ob_start();
       ?>

      <div class="flex-gallery">
        <ul class="slides">
        <?php
          foreach( $gallery_ids as $image_id ) {

            $attachment = get_post( $image_id );

            $image = wp_get_attachment_image( $image_id, $args['thumb-size'], false  );

            $caption = ( $attachment->post_excerpt != '' ) ? sprintf('<p class="flex-caption">%s</p>', $attachment->post_excerpt) : '';

            printf( '<li>%s %s</li>', $image, $caption);

          } ?>
        </ul>
      </div>
      <?php

      $media = ob_get_clean();
    }

    // STANDARD THUMB
    elseif ( has_post_thumbnail( $args['id'] ) ) {

       $media = sprintf('<a class="post-thumbnail-link" href="%s">%s</a>', get_permalink(), get_the_post_thumbnail( $args['id'], $args['thumb-size'], array('title' => '')));

    }

    else
      $media = '';



    return do_shortcode( $media );
  }

  function pl_get_attachment_ids_from_gallery( $id ) {

    $attachment_ids = array();

    $pattern = get_shortcode_regex();

    $ids = array();

    //finds the "gallery" shortcode, puts the image ids in an associative array: $matches[3]
    if (preg_match_all( '/'. $pattern .'/s', get_post_field('post_content', $id), $matches ) ) {


      $count = count( $matches[3] );      //in case there is more than one gallery in the post.

      for ($i = 0; $i < $count; $i++){

        $atts = shortcode_parse_atts( $matches[3][$i] );

        if ( isset( $atts['ids'] ) ){

          $attachment_ids = explode( ',', $atts['ids'] );
          $ids = array_merge($ids, $attachment_ids);
        }
      }
    }
    return $ids;
  }
}

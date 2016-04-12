<?php
/*

  Plugin Name:   PageLines Section Widgets
  Description:   Versatile widgets and sidebars section.

  Author:       PageLines
  Author URI:   http://www.pagelines.com

  PageLines:     PL_Widgets
  Filter:       widgetized

*/
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Widgets extends PL_Section {

  function section_persistent() {

    add_filter( 'pl_binding_widgets', array( $this, 'callback_widgets' ), 10, 2 );

  }

  function section_opts() {

    $opts = array();

    $opts[] = array(
      'key'     => 'widgets',
      'type'    => 'select_sidebar',
      'label'   => __( 'Select Sidebar', 'pl-platform' ),
      'help'    => __( 'Select the widgetized area you would like to use with this instance of Widgetizer.', 'pl-platform' ),
    );

    $opts[] = array(
      'key'       => 'cols',
      'label'     => __( 'Columns Per Widget (12 Col Grid)', 'pl-platform' ),
      'type'      => 'select',
      'default'   => 4,
      'opts'      => array(
        '12'      => array( 'name' => __( 'Sidebar (Full Width)', 'pl-platform' ) ),
        '3'       => array( 'name' => __( '3 of 12 Columns', 'pl-platform' ) ),
        '4'       => array( 'name' => __( '4 of 12 Columns', 'pl-platform' ) ),
        '6'       => array( 'name' => __( '6 of 12 Columns', 'pl-platform' ) ),
      ),
    );

    return $opts;
  }

  function section_template() {

?>
<div class="pl-widgets pl-content-area" data-bind="plcallback: widgets" data-callback="widgets"><?php echo $this->get_widgets( $this->opt( 'widgets' ) );?></div>
<?php

  }

  function callback_widgets( $response, $data ) {

    $response['template'] = $this->get_widgets( $data['value'] );

    return $response;
  }



  function get_widgets( $area ) {

    if ( $area ) {

      ob_start();

        add_filter( 'dynamic_sidebar_params', array( $this, 'column_markup' ) );

        echo pl_draw_sidebar( $area, false, 'pl-row' );

        remove_filter( 'dynamic_sidebar_params', array( $this, 'column_markup' ) );

      return ob_get_clean();

    } else {

      return sprintf( '<div class="fix sidebar_widgets pl-row" >%s</ul>', $this->get_default() );

    }

  }

  function column_markup( $params ) {

    $params[0]['before_widget'] = sprintf('<div class="widget-col" data-bind="%s">%s',
        $this->column_binding(),
        $params[0]['before_widget']
    );
    $params[0]['after_widget']  = sprintf( '%s</div>', $params[0]['after_widget'] );

    return $params;
  }

  function column_binding() {
    return "class: (cols()) ? 'pl-col-sm-' + cols() : 'pl-col-sm-12'";
  }

  /**
   * The default output for the section.
   */
  function get_default() {

    $defaults = array(

      array(
        'title'  => __( 'Latest Posts','pl-platform' ),
        'cont'   => $this->pl_recent_posts(),
        'type'   => 'media-list',
      ),

      array(
        'title'  => __( 'Top Categories','pl-platform' ),
        'cont'   => $this->pl_popular_taxonomy(),
        'type'   => 'media-list',
      ),
      array(
        'title'  => __( 'Recent Comments','pl-platform' ),
        'cont'   => $this->pl_recent_comments(),
        'type'   => 'media-list',
      ),
    );
    ob_start();
    ?>

    <?php foreach ( $defaults as $widget ) :
    ?>
    <div class="widget-col" data-bind="<?php echo $this->column_binding(); ?>">
      <div class="widget">
        <div class="widget-pad">
          <h3 class="widgettitle"><?php echo $widget['title']; ?></h3>
          <ul class="<?php echo $widget['type']; ?>">
            <?php echo $widget['cont']; ?>
          </ul>
        </div>
      </div>
    </div>
    <?php endforeach;
    return ob_get_clean();
  }

  function pl_recent_comments( $number = 3 ) {

     ob_start();
     $comments = get_comments( array( 'number' => $number, 'status' => 'approve' ) );
    if ( $comments ) {
      foreach ( (array) $comments as $comment ) {

        if ( 'comment' != get_comment_type( $comment ) ) {
            continue; }

        $post = get_post( $comment->comment_post_ID );
        $link = get_comment_link( $comment->comment_ID );

        $avatar = pl_get_avatar_src( get_avatar( $comment ) );

        $img = ($avatar) ? sprintf( '<div class="img"><a class="the-media" href="%s" style="background-image: url(%s)"></a></div>', $link, $avatar ) : '';

        printf(
            '<li class="media fix">%s<div class="bd"><div class="wrp"><div class="title" >"%s"</div><div class="excerpt">%s <a href="%s">%s</a></div></div></div></li>',
            $img,
            stripslashes( mb_substr( wp_filter_nohtml_kses( $comment->comment_content ), 0, 50 ,'UTF-8' ) ),
            __( 'on', 'pl-platform' ),
            $link,
            pl_custom_excerpt( $post->post_title, 3 )
        );
      }
    }
      return ob_get_clean();
  }

  function pl_recent_posts( $number = 3 ) {
     ob_start();
      ?>
      <ul class="media-list">
          <?php

          foreach ( get_posts( array( 'numberposts' => $number ) ) as $p ) {

              $thumb_url = (has_post_thumbnail( $p->ID )) ? pl_post_image_url( $p->ID, 'thumbnail' ) : pl_fallback_image( 'thumbnail' );

              $img = sprintf( '<div class="img"><a class="the-media" href="%s" style="background-image: url(%s)"></a></div>', get_permalink( $p->ID ), $thumb_url );

              printf(
                  '<li class="media fix">%s<div class="bd"><div class="wrp"><a class="title" href="%s">%s</a></div></div></li>',
                  $img,
                  get_permalink( $p->ID ),
                  $p->post_title,
                  pl_custom_excerpt( $p->ID, 13 )
              );

          } ?>
       </ul>
    <?php
     return ob_get_clean();
  }

  function pl_popular_taxonomy( $number_of_categories = 6, $taxonomy = 'category' ) {

     $args = array(
         'number'     => $number_of_categories,
         'depth'      => 1,
         'title_li'   => '',
         'orderby'    => 'count',
         'show_count' => 1,
         'order'      => 'DESC',
         'taxonomy'   => $taxonomy,
         'echo'       => 0,
     );
     return wp_list_categories( $args );
  }
}

<?php
/*
  Plugin Name:   PageLines Section ShareBar
  Description:   Buttons for social sharing including PageLines Like, Facebook, Twitter, LinkedIn and Pinterest.
  
  Author:        PageLines
  Author URI:    http://www.pagelines.com
  
  Version:       5.0.6
  Demo:          yes
  
  PageLines:     PL_Section_ShareBar

  Category:      framework, free, sections, featured
  
  Tags:          social, icons
  
  Filter:        localsocial
*/

if( ! class_exists('PL_Section') )
  return;

class PL_Section_ShareBar extends PL_Section {

  function section_styles(){

    pl_script( $this->id, $this->base_url . '/sharebar.js', array( 'jquery' ), null, true );
    global $post;

    if( ! is_blog_page_type() && ! is_404() ){
      $id = ( $post->ID  ) ? $post->ID : '';
      $title  = get_the_title( $id );
      $perm   = get_permalink( $id );
      $exc    = pl_custom_excerpt( $id, 15 );
      $thmb   = pl_post_image_url( $id, 'big-thumb');
      $thumb  = ( $thmb != '') ? $thmb : pl_get_site_logo( false );
      $name   = get_bloginfo( 'name' );
      $type   = 'article';
    }
    else {
      global $wp;
      $title  = get_bloginfo( 'name' );
      $perm   = home_url( $wp->request );
      $exc    = get_bloginfo( 'description' );
      $thumb  = pl_get_site_logo();
      $name   = get_bloginfo( 'name' );
      $type   = 'website';
    }
    wp_localize_script( $this->id, 'pl_shares', array(
        'title' => $title,
        'url'   => $perm,
        'desc'  => $exc,
        'thumb' => $thumb,
        'name'  => $name,
        'type'  => $type
     ) );
  }
  


  function section_opts(){

    $opts[] = array(
      'type'  => 'multi',
      'title'  => __( 'Text', 'pl-section-sharebar' ),
      'opts'  => array(
        array(
          'type'    => 'text',
          'key'      => 'text',
          'label'    => __( 'Description Text', 'pl-section-sharebar' ),
          'default' => __('If you like this, then please share!', 'pl-section-sharebar')
        )
      )
    );

    return $opts;

  }

  function section_template(){
    
    ?><div class="pl-sharebar">
        <div class="pl-sharebar-pad">
          <?php if (!empty($this->opt('text'))): ?>
          <div class="txt-wrap pla-from-bottom pl-animation subtle">
            <div class="txt" data-bind="pltext: text">
              <?php echo $this->opt('text'); ?>
            </div>
          </div>
          <?php endif; ?>
          <?php echo $this->get_shares(); ?>

        </div>
      </div>
       
  <?php }


    function get_shares(){
      ?>
      <div class="pl-social-counters pl-animation-group">
        <?php
          foreach( $this->the_icons() as $key => $icon ){
            echo $this->pl_get_social_button( array( 'txt' => $icon['txt'], 'btn' => $icon['icon'], 'classes' => 'pl-animation pla-from-top subtle icon') );
          }

          do_action( 'pl_sharebar_after_icons' ); 
        ?>

      </div>

      <?php 
    }


    function the_icons( ){

      $icons = array(
        array( 'txt' => __( 'Share On Facebook',  'pl-section-sharebar' ), 'icon' => 'facebook-square' ),
        array( 'txt' => __( 'Share On Linkedin',  'pl-section-sharebar' ), 'icon' => 'linkedin-square' ),
        array( 'txt' => __( 'Share On Google+', 'pl-section-sharebar' ), 'icon' => 'google-plus-square' ),
        array( 'txt' => __( 'Share On Twitter',   'pl-section-sharebar' ), 'icon' => 'twitter-square' ),
        array( 'txt' => __( 'Share On Pinterest', 'pl-section-sharebar' ), 'icon' => 'pinterest-square' ),
      );
      
      return $icons;
    }

    function pl_get_social_button( $args = array() ){
    
      $defaults = array(
        'url'     => get_permalink(),
        'btn'     => 'facebook-sqaure',
        'classes' => '',
        'count'   => '',
        'atts'    => '',
        'txt'     => __( 'Share On Facebook', 'pl-section-sharebar' )
      );
      
      $atts = wp_parse_args( $args, $defaults );
      
      $icon = sprintf('<span class="pl-social-icon"><i class="pl-icon pl-icon-%s"></i> </span><span class="pl-social-count">%s</span>', $atts['btn'], $atts['count']);

      $btn = sprintf('<span title="%s" class="pl-social-counter pl-social-%s %s" data-social="%s" %s >%s</span>', 
            $atts['txt'], 
            $atts['btn'], 
            $atts['classes'], 
            str_replace('-square', '', $atts['btn']), 
            $atts['atts'],
            $icon 
          );
        
      return $btn;
      
    }


}

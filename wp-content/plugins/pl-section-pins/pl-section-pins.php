<?php
/*

  Plugin Name:   PageLines Section Pins
  Description:   A pins style posts section with infinite scrolling and masonry layout.

  Author:       PageLines
  Author URI:   http://www.pagelines.com
  
  Demo:         https://www.pagelines.com/extensions/pl-section-pins/#demo

  Version:      5.0.4

  
  PageLines:    PL_Pins_Section
  Filter:       content

  Loading:      refresh
  
  Category:     framework, sections, free, featured

  Tags:         posts, pins, ajax, blog, masonry, infinitescroll
  

*/

if( ! class_exists('PL_Section') )
  return;

class PL_Pins_Section extends PL_Section {

  function section_persistent(){
    add_filter('pl_binding_' . $this->id, array( $this, 'callback'), 10, 2);
  }

  function callback( $response, $data ){

    $response['template'] = $this->do_callback( $data['value']);


    return $response;
  }

  function section_styles(){

    pl_script( 'infinitescroll',    $this->base_url.'/script.infinitescroll.js');
    pl_script( 'isotope',           $this->base_url.'/isotope.js');
    pl_script( $this->id,           $this->base_url.'/pins.js');

  }

  

  function section_opts(){

    $options = array();

    
    $options[] = array(
      'type'      => 'multi',
      'title'     => __( 'Loading Mode', 'pl-section-pins' ),
      'opts'      => array(
    
        array(
          'key'       => 'pins_loading',
          'type'      => 'select',
          'default'   => 'ajax',
          'conf'      => true,
          'opts' => array(
            'infinite'    => array('name' => __( 'Use Infinite Scrolling', 'pl-section-pins' ) ),
            'ajax'        => array('name' => __( 'Use Load Posts Link (AJAX)', 'pl-section-pins' ) ),
          ),
          'label'     => __( 'Pin Loading Method', 'pl-section-pins' ),
          'help'      => __( "Use infinite scroll loading to automatically load new pins when users get to the bottom of the page. Alternatively, you can use a link that users can click to 'load new pins' into the page.", 'pl-section-pins' ),
        ),
      )
    );

    $options[] = array(
        'type'      => 'multi',
        'title'     => __( 'Pins Post Handling', 'pl-section-pins' ),
        'conf'      => true,
        'opts'      => array(
          array(
            'key'        => 'media_size',
            
            'type'       => 'select_imagesizes',
            'default'    => 'landscape-thumb',
            'label'     => __( 'Select Featured Image Size', 'pl-section-pins' ),
        
            'help'      => __( 'Select from a variety of different size and aspect values for your featured images in posts.', 'pl-section-pins' )
          ),
          array(
            'key'       => 'posts_per_page',
            'default'   => '6',
            'type'      => 'count_select',
            'count_number'  => '40',
            'label'   => __( 'Number of Posts', 'pl-section-pins' ),
          ),
          array(
            'key'   => 'pins_meta',
            'default' => '[post_date] &middot; [post_comments] [post_edit]',
            'type'    => 'text',
            'label'   => __( 'Pin Meta Info', 'pl-section-pins' ),
            'help'    => __( 'Use shortcodes to customize the meta information for these pins.', 'pl-section-pins' )
          ),
          array(
            
            'label'     => __( 'Which post type should we use?', 'pl-section-pins' ),
            'key'       => 'post_type',
            'default'   => 'post',
            'type'      => 'select',
            'opts'      => pl_post_types_with_thumbs(),

          ),
          array(
            'key'       => 'taxonomy',
            'type'      => 'select_term',
            'trigger'   => 'post_type',
            'label'     => __('Select Taxonomy Term', 'pl-section-pins'),
          ),
   
        )
      );

    $options[] = array(
      'type'      => 'multi',
      'title'     => __( 'Pins Meta', 'pl-section-pins' ),
      'opts'      => array(
    
        array(
          'key'       => 'pins_avatar_hide',
          'type'      => 'check',
          'label'     => __( 'Hide Avatar?', 'pl-section-pins' )
        ),
        array(
          'key'       => 'pins_meta_hide',
          'type'      => 'check',
          'label'     => __( 'Hide Meta Information?', 'pl-section-pins' )
        ),
      )
    );


    return $options;
  }

  function load_posts( $config ){



    if(  $config['taxonomy'] != '' ) {

      $bits = explode( '__', $config['taxonomy'] );

      $config['tax_query'] = array(
           array(
             'taxonomy' => $bits[0],
             'field'    => 'slug',
             'terms'    => array( $bits[1] )
           )
       );
    }
    

    $q = new WP_Query( $config );

  
    return $q->posts;
  }

  function do_callback( $config = array() ){

          global $wp_query;
          global $post;

          // JAVASCRIPT VARIABLES
          $loading = ( $this->opt('pins_loading') ) ? $this->opt('pins_loading') : 'ajax';
          
          $get = ( isset( $_GET['config'] ) ) ? $_GET['config'] : array();

          $config = wp_parse_args( $get, $config );

          $config['paged'] = (isset($config['pins']) && $config['pins'] != 1) ? $config['pins'] : 1;

          $out = '';

          

          foreach( $config as $tag => $it ){
            if( is_numeric( $it )){
              $config[ $tag ] = (int) $it;
            }
          }


            
          $pins = $this->load_posts( $config );

          

          if( !empty( $pins) ){
          
            foreach( $pins as $key => $p ){

              global $post;
              $post = $p;
              setup_postdata($p);

              if(has_post_thumbnail($p->ID) && get_the_post_thumbnail($p->ID) != ''){

                $thumb = get_the_post_thumbnail($p->ID, $config['media_size'] );

                $check = strpos( $thumb, 'data-lazy-src' );

                if( $check ) {
                  // detected lazy-loader.
                  $thumb = preg_replace( '#\ssrc="[^"]*"#', '', $thumb );
                  $thumb = str_replace( 'data-lazy-', '', $thumb );
                }

                $image = sprintf('<div class="pin-img-wrap"><a class="pin-img" href="%s">%s</a></div>', get_permalink( $p->ID ), $thumb);

              } else
                $image = '';




              $author_name = get_the_author();
              // $author_desc = pl_custom_excerpt( get_the_author_meta('description', $p->post_author), 10);
              $author_email = get_the_author_meta('email', $p->post_author);
              $avatar = get_avatar( $author_email, '32' );


              $meta_bottom = sprintf(
                '<div class="meta fix"><div class="pins-author-avatar"><div class="img-wrap">%s</div></div><div class="pin-meta subtext"><div class="author">%s</div class="author"> <div class="pin-metabar">%s</div> </div></div>',
                $avatar,
                ucwords ( $author_name ),
                do_shortcode( urldecode( $config['pins_meta'] ) )

              );

              $content = sprintf(
                '<div class="postpin-pad"><h4 class="headline pin-title"><a href="%s">%s</a></h4></div><div class="postpin-pad pin-bottom">%s</div>',
                get_permalink( $p->ID ),
                $p->post_title,
                $meta_bottom
              );

              $out .= sprintf(
                '<div class="pl-col-sm-4 pl-col-xs-12 isotope-item"><div class="span-wrap postpin-wrap" ><article class="postpin">%s%s</article></div></div>',
              
                $image,
                $content
              );
            }
            wp_reset_postdata();
            
          } 

          else {
            echo 'Nothing Found';
          }


          $config['pins_meta'] = urlencode( $config['pins_meta'] ); 
          
         // $u = add_query_arg('pins', $config['paged'] + 1, pl_get_current_url( ));
          
          $next_config = $config; 

          $next_config['pins'] = $config['paged'] + 1;


          $u = add_query_arg('config', $next_config, pl_get_current_url( ));

          // just to see if we should show link
          $next_posts = $this->load_posts( $next_config );
            
          
          

          if( !empty($next_posts) ){

            $class = ( $config['pins_loading'] == 'infinite' ) ? 'iscroll' : 'fetchpins';

            $display = ($class == 'iscroll') ? 'style="display: none"' : '';

            $next_url = sprintf('<div class="%s fetchlink" %s><a class="pl-btn pl-btn-sm pl-btn-default iframe-ignore-link" href="%s">%s</a></div>', $class, $display, $u, __('Load More Posts', 'pl-section-pins'));

          } else
            $next_url = '';
          
          
         
          
         $output = sprintf( 
            '<div class="pinboard fix" data-id="%s"> 
              <div class="postpin-list pl-row half-space fix"  data-loading="%s" data-url="%s">%s</div> 
              %s 
              <div class="clear"></div>
            </div>', 
            $this->meta['clone'],
            $config['pins_loading'],
            $this->base_url,
            $out, 
            $next_url
          );

         return $output;


  }


  function section_template(){

?>  
  
  <div class="pins-wrap" data-bind="<?php echo pl_make_callback( $this->get_config() );?>, plclassname: [ 1 == pins_avatar_hide() ? 'hide-avatar' : '', 1 == pins_meta_hide() ? 'hide-meta' : '']" data-callback="<?php echo $this->id;?>"><?php echo $this->do_callback( $this->get_config() ); ?></div>
  
<?php 
  }



  



}

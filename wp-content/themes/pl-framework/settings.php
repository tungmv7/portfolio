<?php


new PL_Framework_Settings;

class PL_Framework_Settings {

  function __construct(){

    add_filter('pl_platform_settings_array',  array($this, 'settings') );
  }

  function settings( $settings ){

    $add = array();

    
        $add['pl_site_images'] = array(

              'key'   => 'pl_site_images',
              'icon'  => 'image',
              'title' => __( 'Site Images', 'pl-framework' ),
              'location'   => array('customizer'),
              'opts'  => array(
                array(
                  'key'       => 'site_logo',
                  'type'      =>  'image_upload',
                  'title'     =>  __( 'Default Logo', 'pl-framework' ),
                  'help'      =>  __( '<p>Set a default site logo. This can be overridden by individual sections.</p>', 'pl-framework' ),
                ),
                array(
                  'key'       => 'site_icon',
                  'type'      =>  'image_upload',
                  'location'  => array('settings'),
                  'title'     =>  __( 'Default Icon', 'pl-framework' ),
                  'help'      =>  __( '<p>Set a default icon to be used in secondary locations and in places where an icon based logo is appropriate.</p>', 'pl-framework' ),
                ),
                array(
                  'key'       => 'pl_default_image',
                  'location'  => array('settings'),
                  'type'      =>  'image_upload_id',
                  'title'     => __( 'Site Default Image ID', 'pl-framework' ),
                  'help'      => __( '<p>Set the ID of the default image fallback. This can be used with various sizes and can be shown whenever an image is needed but not set yet.</p>', 'pl-framework' )
                )
              )
        );
    
    // WP 4.3 added favicons to core, if its available then let WP take over here.
    if( ! function_exists( 'wp_site_icon' ) ) {
      $add['pl_site_images']['opts'][] = array(
        'key'       => 'pl_favicon',
        'label'     => __( 'Upload Favicon (32px by 32px)', 'pl-framework' ),
        'type'      =>  'image_upload',
        'location'  => array('settings'),
        'imgsize'   =>  '16',
        'extension' => 'ico,png',
        'title'     =>  __( 'Favicon Image', 'pl-framework' ),
        'help'      =>  __( '<p>This is the small icon you see in your browser tabs or favorites.</p> <p><strong>The image must be .png or .ico file - 32px by 32px</strong>.</p>', 'pl-framework' ),
        'default'   =>  '[pl_image_url]/default-favicon.png'
       );
    }

    $add['site_colors'] = array(
      'key'   => 'site_colors',
      'icon'  => 'paint-brush',
      'title' => __( 'Colors / Background', 'pl-framework' ),
      'opts'  => array(

        array(
          'key'           => 'bodybg',
          'type'          => 'color',
          'default'       => 'fff',
          'title'         => __( 'Site Background Color', 'pl-framework' ),
          'help'          => __( 'This sets the global background color for your site. It does not effect any other colors, so choose it to work well with the scheme you have chosen.', 'pl-framework' ),

        ),
        pl_std_opt('scheme', array('key' => 'site_scheme', 'label' => __( 'Site Text Color Scheme', 'pl-framework' ) ) ),

        array(
          'key'           => 'footerbg',
          'type'          => 'color',
          'title'         => __( 'Footer Background Color', 'pl-framework' ),
          'help'          => __( 'This sets the background color for your site footer.', 'pl-framework' ),
        ),
        pl_std_opt('scheme', array('key' => 'footer_scheme', 'label' => __( 'Footer Text Color Scheme', 'pl-framework' ) ) ),

        array(
          'key'           => 'background_image',
          'type'          => 'image_upload',
          'title'         => __( 'Site Background Image', 'pl-framework' ),
          'help'          => __( 'This sets a global background image for your site.', 'pl-framework' ),

        ),

        array(
          'key'           => 'background_style',
          'title'         => __( 'Background Image Style', 'pl-framework' ),
          'type'          => 'select',
          'transport'     => 'postMessage',
          'default'       => 'cover',
          'opts'     => array(
              'cover'   => array('name' => 'Cover'),
              'contain' => array('name' => 'Contain'),
              'repeat'  => array('name' => 'Tile'),
            ),

        ),
        array(
          'key'           => 'background_image_size',
          'title'         => __( 'Background Image Tile Size', 'pl-framework' ),
          'type'          => 'range',
          'default'       => 100,
          'transport'     => 'postMessage',
          'opts'          => array(
                'min'     => 1,
                'max'     => 200,
                'step'    => 1,
            ),
          'help'          => __( 'Note that this will only have an effect if you have selected "tile" as your background image style.', 'pl-framework' ),
        ),
        array(
          'key'     => 'linkcolor',
          'type'    => 'color',
          'title'   => __( 'Link Color', 'pl-framework' ),
          'help'    => __( 'This will be the default color for links on your site. <strong>Note</strong> that it will be overridden by scheme changes on sections, due to the framework assuming you will be using contrasting backgrounds.', 'pl-framework' ),

        ),
      )
    );

    $add['site_fonts'] = array(
      'key'   => 'site_fonts',
      'icon'  => 'font',
      'title' => __( 'Typography', 'pl-framework' ),
      'opts'  => array(
        array(
          'title'         => __( 'Base Font Size', 'pl-framework' ),
          'key'           => 'base_font_size',
          'type'          => 'select_count',
          'count_start'   => 10,
          'count_number'  => 18,
          'suffix'        => 'px',
          'default_text'  => 'inherit',
          'help'          => __( '<p>This sets the base font size of your site, that all other font sizes are based on.</p> <p>Each section allows you to scale text relative to this.</p>', 'pl-framework' ),

        ),

        array(
          'title'         => __( 'External Font Scripts', 'pl-framework' ),
          'key'           => 'font_extra',
          'type'          => 'script',
          'mode'          => 'html',
          'kses'          => 'bypass',
          'place'         => "<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>",
          'help'          => __( '<p>To ensure cross browser compatibility and the best visual appeal, we recommend you use an external font library. Our recommended services for this are <a href="http://www.google.com/fonts" target="_blank">Google Fonts</a> and <a href="http://www.typekit.com" target="_blank">Typekit</a>.</p>  <p>To add specific fonts from these sites, place their scripts here and they will be placed in your site &lt;head&gt;.</p>', 'pl-framework' ),

        ),
        array(
          'key'           => 'font_primary',
          'type'          => 'script',
          'mode'          => 'less',
          'title'         => __( 'Primary Type Style', 'pl-framework' ),
          'place'         => "font-family: 'Lato', helvetica, sans-serif;\nfont-weight: 400;",
          'help'          => __( '<p>Add CSS directed at your global primary text.</p> <p>Leave blank and the default styles will be used.</p>', 'pl-framework' ),

        ),
        array(
          'key'           => 'font_headers',
          'type'          => 'script',
          'mode'          => 'less',
          'title'         => __( 'Major Header Style (H1, H2)', 'pl-framework' ),
          'place'         => 'font-family: inherit;',
          'help'          => __( '<p>Add CSS directed at your website text headers h1-h2.</p> <p>Leave blank and they will inherit your primary text style.</p>', 'pl-framework' ),

        ),
        array(
          'key'           => 'font_headers_minor',
          'type'          => 'script',
          'mode'          => 'less',
          'title'         => __( 'Minor Header Style (H3, H4, H5, H6)', 'pl-framework' ),
          'place'         => 'font-family: inherit;',
          'help'          => __( '<p>These styles will apply to elements h3, h4, h5, h6.</p>', 'pl-framework' ),

        ),

      )
    );

    $add['layout_navigation'] = array(
      'key'   => 'layout_navigation',
      'icon'  => 'list-alt',
      'title' => __( 'Layout / Nav', 'pl-framework' ),

      'opts'  => array(
        array(
          'key'     => 'layout_width',
          'type'      => 'select',
          'title'     => __( 'Content Width', 'pl-framework' ),
          'default_text'  => 'default',
          'opts'    => array(
            '800px'   => array('name' => __( '800px', 'pl-framework' )),
            '960px'   => array('name' => __( '960px', 'pl-framework' )),
            '1000px'  => array('name' => __( '1000px', 'pl-framework' )),
            '1200px'  => array('name' => __( '1200px', 'pl-framework' )),
            '80%'     => array('name' => __( '80%', 'pl-framework' )),
            '90%'     => array('name' => __( '90%', 'pl-framework' )),
            '100%'    => array('name' => __( '100%', 'pl-framework' )),
          ),
          'help'      => __( '<p>Set the standard width for content within your full width containers. This can be a fixed pixel width or percent of window width.</p> <p>Note, it will be responsive, content resizes to 100% width on mobile devices.</p>', 'pl-framework' )
        ),
        array(
          'key'       => 'read_width',
          'type'      => 'select_count',
          'title'     => __( 'Reading Width', 'pl-framework' ),
          'default_text'  => 'default',
          'count_start'   => 25,     // Count starts at this value
          'count_number'  => 60,   // Count total
          'suffix'        => 'em',  // Suffix added to option text
          'help'      => __( '<p>Optionally override the default "reading" width. This width should have an optimized line length for eye tracking, usually 50 to 60 characters.</p>', 'pl-framework' )
        ),
        array(
          'key'     => 'primary_navigation_menu',
          'type'    => 'select_menu',
          'location'  => array('settings'),
          'title'   => __( 'Mobile / Default Menu', 'pl-framework' ),
          'help'    => __( '<p>Set the primary/default navigation for your site. This will be used in the pop out mobile menu and as the default in navigation sections.</p>', 'pl-framework' )


        ),
        array(
          'key'   => 'secondary_navigation_menu',
          'location'  => array('settings'),
          'type'    => 'select_menu',
          'title'   => __( 'Mobile Sub / Secondary Menu', 'pl-framework' ),

        ),
      )
    );

   // $disabled = ( ! pl_is_professional() ) ? true : false;
   
   // $add['pl_professional'] = array(
   //   'disabled'  => $disabled,
   //   'key'       => 'pl_professional',
   //   'icon'      => 'star',
   //   'pos'       => 450,
   //   'title'     => __( 'Pro Settings', 'pagelines' ),
   //   'location'   => array('settings'),
   //   'opts'  => array(
   //     array(
   //       'key'       => 'hide_pl_cred',
   //       'type'      => 'checkbox',
   //       'label'     => 'Hide PageLines Link Credit In Footer?',
   //       'title'     =>  __( 'Hide PageLines Leaf?', 'pagelines' ),
   //     ),
   //   )
   // );


    if( is_child_theme() ){
      $add['child_theme'] = array(
        'key'       => 'child_theme',
        'icon'      => 'copy',
        'pos'       => 450,
        'title'     => __( 'Child Theme', 'pl-framework' ),
        'opts'  => array(

          array(
            'key'     => 'disable_parent_styles',
            'type'    => 'checkbox',
            'title'   => __( 'Framework Parent Styles', 'pl-framework' ),
            'label'    => __( 'Disable parent framework style.css?', 'pl-framework' ),
            'help'    => __( '<p>If you are creating a custom framework child theme, disable parent styling if you are using your own or have pasted the framework style.css there.</p>', 'pl-framework' )
          ),

        )
      );
    }




    return $add + $settings ;
  }


}

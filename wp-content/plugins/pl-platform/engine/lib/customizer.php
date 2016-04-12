<?php
/**
 * WP Customizer Handling Class
 *
 * @class     PL_Platform_WP_Customizer
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class PL_Platform_WP_Customizer{

  function __construct() {

    add_action( 'customize_register', array( $this, 'customizer_load' ), 11 );

  }



  function customizer_load( $wp_customize ) {

    pl_add_customizer_classes();

    // if ( $wp_customize->is_preview() && ! is_admin() ) {
    //   add_action( 'wp_footer', array( $this, 'customize_preview' ), 21 );
    // }

    $wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
    $wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

    $config = apply_filters( 'pl_platform_customizer_config', array() );

    /**
     * register the sections
     */

    if ( is_array( $config ) && ! empty( $config ) ) {

      foreach ( $config as $id => $section ) {

        /** Dont show in customizer if not meant for it... */
        if ( ! $this->check_location( $section ) ) {
          continue; }

        $priority = ( isset( $section['priority'] ) ) ? $section['priority'] : 20;

        $section_config = array(
            'title'     => strip_tags( $section['title'] ),
            'priority'  => $priority,
          );

        $key = ( isset( $section['key'] ) ) ? $section['key'] : pl_ui_key( $section['title'] );

        $wp_customize->add_section( $section['key'], $section_config );

        foreach ( $section['opts'] as $i => $o ) {

          /** Dont show in customizer if not meant for it... */
          if ( ! $this->check_location( $o ) ) {
            continue; }

          $o['section'] = $section['key'];

          $o = $this->customizer_engine( $o, $section );

          $handling = ( isset( $o['handling'] ) ) ? $o['handling'] : array();

          $handling = wp_parse_args( $handling, array(
              'default'         => '',
              'type'            => 'option',
              'capability'      => 'edit_theme_options',
          ));

          $wp_customize->add_setting( $this->prepare_option( $o['key'] ), $handling );

          if ( isset( $o['class'] ) ) {

            $set = $o;

            unset( $set['type'] );

            $wp_customize->add_control( new $o['class']( $wp_customize, $o['key'], $set ) );
          } else {
            $wp_customize->add_control( $this->prepare_option( $o['key'] ), $o );
          }
        }
      }
    }
  }

  function check_location( $array ) {

    $location = ( isset( $array['location'] ) ) ? $array['location'] : array();

    /** Dont show in customizer if not meant for it... */
    if ( ! empty( $location ) && ! in_array( 'customizer', $location ) ) {
      return false; } else {       return true; }

  }

  function customizer_engine( $o, $section ) {

    if ( isset( $o['place'] ) && ! isset( $o['placeholder'] ) ) {
      $o['placeholder'] = $o['place'];
    }

    if ( isset( $o['help'] ) && ! isset( $o['description'] ) ) {
      $o['description'] = $o['help'];
    }

    $o = wp_parse_args( $o, array(
        'placeholder' => '',
        'description' => '',
        'section'     => $section['key'],
        'settings'    => $this->prepare_option( $o['key'] ),
        'label'       => ( isset( $o['title'] ) ) ? $o['title'] : '',
    ));

    if ( 'image_upload' == $o['type'] ) {
      $o['class'] = 'WP_Customize_Image_Control'; } elseif ( 'color' == $o['type'] ) {
      $o['class'] = 'WP_Customize_Color_Control'; } elseif ( 'select_menu' == $o['type'] ) {
        $o['class'] = 'PL_Menu_Dropdown_Custom_Control'; } elseif ( 'script' == $o['type'] ) {
        $o['class'] = 'PL_Script_Custom_Control'; } elseif (
        'select' == $o['type']
        || 'radio' == $o['type']
        || 'select_imagesizes' == $o['type']
        ) {

          $o['choices'] = array( '' => 'Default' );

          if ( 'select_imagesizes' == $o['type'] ) {
            $items = get_intermediate_image_sizes();

            if ( is_array( $items ) ) {
              foreach ( $items as $m ) {
                $o['opts'][ $m ] = array( 'name' => $m );
              }
            }

            $o['type'] = 'select';
          }

          foreach ( $o['opts'] as $key => $c ) {
            $o['choices'][ $key ] = $c['name'];
          }
        } elseif ( 'select_count' == $o['type'] ) {

          $o['type'] = 'select';

          $count_start = (isset( $o['count_start'] )) ? $o['count_start'] : 0;

          $suffix = (isset( $o['suffix'] )) ? $o['suffix'] : '';

          for ( $i = $count_start; $i <= $o['count_number']; $i++ ) {
            $o['choices'][ $i ] = $i . $suffix;
          }
        } elseif ( 'range' == $o['type'] ) {

          foreach ( $o['opts'] as $key => $c ) {
            $o['input_attrs'][ $key ] = $c;
          }
        }

        return $o;

  }


  /**
  * helper function that prefixes the options with current theme option slug.
  */
  function prepare_option( $opt ) {

    return sprintf( '%s[%s]', pl_base_settings_slug(), $opt );
  }
}



function pl_add_customizer_classes() {

  /**
   * Class to create a custom menu control
   */
  class PL_Menu_Dropdown_Custom_Control extends WP_Customize_Control {
    private $menus = false;

    public function __construct( $manager, $id, $args = array(), $options = array() ) {
        $this->menus = wp_get_nav_menus( $options );
        parent::__construct( $manager, $id, $args );
    }

    /**
     * Render the content on the theme customizer page
    */
    public function render_content() {
      if ( ! empty( $this->menus ) ) {
        ?>
        <label>
        <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
          <?php if ( $this->description ) : ?>
            <span class="pl-description customize-control-description"><?php echo $this->description; ?></span>
          <?php endif; ?>
          <select name="<?php echo $this->id; ?>" id="<?php echo $this->id; ?>">
          <?php
          foreach ( $this->menus as $menu ) {
            printf( '<option value="%s" %s>%s</option>', $menu->term_id, selected( $this->value(), $menu->term_id, false ), $menu->name );
          }
            ?>
            </select>
        </label>
<?php
      }
    }
  }

  /**
   * Create a scripts / Codemirror control
   */
  class PL_Script_Custom_Control extends WP_Customize_Control {

    public $type = 'script';

    public function __construct( $manager, $id, $args = array(), $options = array() ) {
        $this->args = $args;
        parent::__construct( $manager, $id, $args );
    }

    /**
     * Add a second textarea to treat codemirror and wp.customize separetely
     * CM freaks out if we dynamically set the textarea value, and the customizer wont update if we dont
    */
    public function render_content() {
  ?>
    <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
    <textarea class="html-textarea code_textarea pl-code-editor large-text" placeholder="<?php echo $this->args['placeholder'];?>" data-mode="<?php echo $this->args['mode'];?>"><?php echo esc_textarea( $this->value() ); ?></textarea>
    
    <textarea class="the-value" type="text" style="display: none;" <?php $this->link(); ?>></textarea>

<?php

    }
  }
}
new PL_Platform_WP_Customizer;

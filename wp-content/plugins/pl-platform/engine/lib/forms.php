<?php
/**
 * Form Handling
 *
 * Sets actions and filters for global handling. Run this once.
 *
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
$GLOBALS['pl_forms_config'] = new PL_Form_Handling;

class PL_Form_Handling{


  function __construct() {

    /** forms handling */
    add_filter( 'posts_where',    array( $this, 'prevent_view_others_media' ) );

    add_action( 'init',           array( $this, 'check_auth' ) );

  }

  /** Has to be logged in to submit, auth_redirect has to be before headers... */
  function check_auth() {

    if ( isset( $_GET['plauth'] ) && ! is_user_logged_in() ) {
      auth_redirect();
    }
  }

  /**
   * Prevent users that can't edit others posts from seeing other's media
   * Since the forms engine allows contributors to upload this is important.
   */
  function prevent_view_others_media( $where ) {
      global $current_user;

    if ( is_user_logged_in() && ! current_user_can( 'edit_others_posts' ) ) {
       // logged in user, but are we viewing the library?
      if ( isset( $_POST['action'] ) && ( 'query-attachments' == $_POST['action'] ) ) {
        // here you can add some extra logic if you'd want to.
        $where .= ' AND post_author=' . $current_user->data->ID;
      }
    }

      return $where;
  }
}


/**
 * Option Engine Class
 *
 * Sorts and Draws options based on the 'option array'
 * Option array is loaded in config.option.php and through filters
 *
 */
class PL_Form_Engine {

  function __construct( $settings ) {

    $this->form_settings = wp_parse_args( $settings, array(
        'render'    => 'settings',
    ));

    $this->defaults = array(

      'place'       => '',
      'disabled'    => false,
      'type'        => 'text',
      'label'       => false,
      'key'         => '',
      'title'       => false,
      'help'        => '',
      'desc'        => '',
      'layout'      => 'normal',
      'placeholder' => '',
      'min'         => '0',
      'max'         => '100',
      'step'        => '1',
      'required'    => false,
      'class'       => '',
    );

  }



  function set_option_up( $o ) {

    $o = wp_parse_args( $o, $this->defaults );

    $o['name'] = sprintf( 'pl_platform_settings[%s]', $o['key'] );

    $o['id'] = $o['key'];

    $o['required'] = ( ! $o['required'] ) ? '' : 'required';

    global $post;

    if ( ! isset( $o['val'] ) || ! $o['val'] ) {

      if ( 'meta' == $this->form_settings['render'] && isset( $post ) && isset( $post->ID ) ) {
        $o['val'] = stripslashes_deep( get_post_meta( $post->ID, $o['key'], true ) );
      } elseif ( 'profile' == $this->form_settings['render'] && isset( $this->form_settings['user'] ) ) {
        $o['val'] = stripslashes_deep( get_the_author_meta( $o['key'], $this->form_settings['user']->ID ) );
      } elseif ( 'frontend' == $this->form_settings['render'] ) {
        $o['val'] = '';
      } else {
        $o['val'] = stripslashes_deep( pl_user_setting( $o['key'] ) );
      }
    }

    /** If label and no title, set title to label */
    if ( ! $o['title'] && $o['label'] ) {
      $o['title'] = $o['label'];
      $o['label'] = '';
    }

    return $o;

  }


  function option_engine( $o ) {

    $o = $this->set_option_up( $o );
    $o['placeholder'] = esc_html( $o['placeholder'] );

    if ( $o['disabled'] ) {
      return; }

    $asterisk = ( ! empty( $o['required'] ) ) ? '<span class="ast">*</span>' : '';

    if ( 'hidden' == $o['type'] || 'custom' == $o['type'] ) :

      $this->option_breaker( $o );

    else :
    ?>

    <div class="pl-option-row pl-form-group type-<?php echo $o['type'];?> layout-<?php echo $o['layout'];?>">
      <div class="pl-option-cols">
        <div class="pl-option ">
          <div class="pl-input-head">
          <label class="pl-option-title pl-input-title" ><?php echo $o['title']; ?> <?php echo $asterisk; ?></label>

          <?php if ( '' != $o['desc'] ) :  ?>
            <div class="pl-option-desc pl-input-help">
              <?php echo $o['desc'];?>
            </div>
          <?php endif;?>
          </div>
          <?php $this->option_breaker( $o ); ?>

        </div>

        <?php if ( '' != $o['help'] ) :  ?>
          <div class="pl-settings-help pl-input-help">
            <div class="pl-help-content"><?php echo $o['help'];?></div>
          </div>
        <?php endif;?>
      </div>

    </div>

<?php

    endif;
  }



  /**
   *
   * Option Breaker
   * Switches through an option array, generating the option handling and markup
   *
   */
  function option_breaker( $o ) {

    switch ( $o['type'] ) {

      case 'multi':
        $this->option_multi( $o );
      break;

      case 'file_upload':
        $this->option_image_upload( $o, 'file' );
      break;

      case 'image_upload':
        $this->option_image_upload( $o, 'image' );
      break;

      case 'image_upload_id':
        $this->option_image_upload( $o, 'image', 'id' );
      break;

      case 'video_upload':
        $this->option_image_upload( $o, 'video' );
      break;

      case 'audio_upload':
        $this->option_image_upload( $o, 'audio' );
      break;

      case 'checkbox':
        $this->option_check( $o );
      break;

      case 'select':
        $this->option_select( $o );
      break;

      case 'radio':
        $this->option_radio( $o );
      break;

      case 'select_menu':
        $this->option_select( $o, 'menu' );
      break;

      case 'select_sidebar':
        $this->option_select( $o, 'sidebar' );
      break;

      case 'select_icon':
        $this->option_select( $o, 'icon' );
      break;

      case 'select_count':
        $this->option_select( $o, 'count' );
      break;

      case 'select_imagesizes':
        $this->option_select( $o, 'imagesizes' );
      break;

      case 'link':
        $this->option_link( $o );
      break;

      case 'action_button':
        $this->option_button( $o, 'action' );
      break;

      case 'color':
        $this->option_color( $o );
      break;

      case 'text':
        $this->option_text( $o );
      break;

      case 'textarea':
        $this->option_textarea( $o );
      break;

      case 'hidden':
        $this->option_hidden( $o );
      break;

      case 'range':
        $this->option_range( $o );
      break;

      case 'longform':
        $this->option_longform( $o );
      break;

      case 'custom':
        $this->option_longform( $o );
      break;

      case 'script':
        $this->option_script( $o );
      break;

      default :
        do_action( 'pagelines_options_' . $o['type'] , $o );
        break;

    }

  }

  function option_multi( $o ) {

    foreach ( $o['opts'] as $key => $opt ) {
      $opt = $this->set_option_up( $opt );

      if ( isset( $opt['title'] ) ) {
        printf( '<label class="pl-option-label">%s</label>', $opt['title'] );
      }

      $this->option_breaker( $opt );

      if ( isset( $opt['help'] ) ) {
        printf( '<div class="pl-multi-option-help">%s</div>', $opt['help'] );
      }
    }
  }

  function option_link( $o, $type = '' ) {

    $class = ( '' != $o['class'] ) ? $o['class'] : 'button-primary';

    ?>

    <p><a href="<?php echo $o['val'];?>" class="button <?php echo $class;?>"><?php echo $o['label'];?></a></p>

    <?php
  }

  function option_button( $o, $type = '' ) {
    ?>

    <p><button for="upload_image" class="image_uploader button button-primary"><?php echo $o['label'];?></button></p>

    <?php
  }

  function option_script( $o ) {

    $mode = ( ! empty( $o['mode'] ) ) ? $o['mode'] : 'htmlmixed';

    ?>
    <div class="label-standard" for="<?php echo $o['id'];?>"><?php echo $o['label'];?></div>
    <div class="script_input_wrap codemirror-wrap">
      <textarea id="<?php echo $o['id'];?>" name="<?php echo $o['name'];?>" placeholder="<?php echo $o['place'];?>" class="html-textarea code_textarea pl-code-editor large-text" data-mode="<?php echo $mode;?>"><?php echo $o['val'];?></textarea>
      <?php $this->setup_kses( $o ); ?>
    </div>

    <?php
  }

  function option_color( $o, $type = '' ) {

    $current = $this->get_option( $o );
    ?>
    <p><input id="<?php echo $o['id'];?>" class="pl-opt pl-colorpicker" type="text" name="<?php echo $o['name'];?>" placeholder="" value="<?php echo pl_color_setting( $current );?>"/> <span class="pl-description"><?php echo $o['label'];?></span></p>
    <?php
  }

  function option_check( $o ) {

    $val = ( ! empty( $o['val'] ) ) ? 'checked' : '';
    ?>

    <p><label for="<?php echo $o['id'];?>" class="image_uploader"><input type="hidden" class="checkbox-toggle" name="<?php echo $o['name'];?>" value="<?php echo $o['val'];?>"><input id="<?php echo $o['id'];?>" class="pl-opt checkbox-input" type="checkbox" <?php echo $val;?> /> <span class="pl-description"><?php echo $o['label'];?></span></label></p>

    <?php
  }

  function option_text( $o ) {
    ?>
    <input id="<?php echo $o['id'];?>" class="pl-opt pl-form-control opt-text" type="text" name="<?php echo $o['name'];?>" placeholder="<?php echo $o['place'];?>" value="<?php echo esc_attr( $o['val'] );?>" <?php echo $o['required'];?> /> <span class="pl-description"><?php echo $o['label'];?></span>
    <?php $this->setup_kses( $o ); ?>

    <?php
  }

  function option_hidden( $o ) {
    ?>
    <input id="<?php echo $o['id'];?>" class="pl-opt pl-form-control opt-text" type="hidden" name="<?php echo $o['name'];?>" value="<?php echo $o['val'];?>" <?php echo $o['required'];?> />

    <?php
  }

  function option_range( $o ) {
    ?>
    <input id="<?php echo $o['id'];?>" oninput="amount.value=<?php echo $o['id'];?>.value" id="<?php echo $o['id'];?>" class="pl-opt opt-text pl-range-input" type="range" name="<?php echo $o['name'];?>" min="<?php echo $o['opts']['min'];?>" max="<?php echo $o['opts']['max'];?>" step="<?php echo $o['opts']['step'];?>" value="<?php echo $o['val'];?>" <?php echo $o['required'];?>/> <output class="pl-range-output" name="amount" for="<?php echo $o['id'];?>"><?php echo $o['val'];?></output><span class="pl-description"><?php echo $o['label'];?></span>

    <?php
  }

  function option_longform( $o ) {
    printf( '<div class="pl-long-form %s">%s</div>', $o['key'], $o['text'] );
  }

  function option_textarea( $o ) {
    ?>
    <textarea id="<?php echo $o['id'];?>" class="pl-opt pl-form-control" rows="8" type="text" name="<?php echo $o['name'];?>" placeholder="<?php echo $o['place'];?>" <?php echo $o['required'];?>><?php echo $o['val'];?></textarea>
    <?php $this->setup_kses( $o ); ?>
    <?php
  }



  function option_radio( $o, $type = ' ' ) {

    $o['opts'] = array( '' => array( 'name' => 'Default' ) ) + $o['opts'];

    foreach ( $o['opts'] as $key => $s ) {

      $selected = ( $o['val'] == $key ) ? 'checked' : '';

      printf( '<label class="label-radio"><input type="radio" name="%s" value="%s" %s />%s</label>', $o['name'], $key, $selected, $s['name'] );
    }

  }

  function option_select( $o, $type = '' ) {

    $select_opts = array();

    $default_text = ( isset( $o['default_text'] ) ) ? $o['default_text'] : 'Select...';
    $default      = ( isset( $o['default'] ) )       ? $o['default']     : '';

    if ( 'menu' == $type ) {
      $items = wp_get_nav_menus( array( 'orderby' => 'name' ) );

      if ( is_array( $items ) ) {
        foreach ( $items as $m ) {
          $select_opts[ $m->term_id ] = array( 'name' => $m->name );
        }
      }
    } elseif ( 'sidebar' == $type ) {
      $items = pl_get_sidebars( );

      if ( is_array( $items ) ) {
        foreach ( $items as $k => $m ) {
          $select_opts[ $k ] = array( 'name' => $m );
        }
      }
    } elseif ( 'icon' == $type ) {

      $items = pl_icons( );

      if ( is_array( $items ) ) {
        foreach ( $items as $m ) {
          $select_opts[ $m ] = array( 'name' => $m );
        }
      }
    } elseif ( 'count' == $type ) {

      $count_start = (isset( $o['count_start'] )) ? $o['count_start'] : 0;

      $suffix = (isset( $o['suffix'] )) ? $o['suffix'] : '';

      for ( $i = $count_start; $i <= $o['count_number']; $i++ ) {
        $select_opts[ $i ] = array( 'name' => $i . $suffix );

      }
    } elseif ( 'imagesizes' == $type ) {

      $items = get_intermediate_image_sizes();

      if ( is_array( $items ) ) {
        foreach ( $items as $m ) {
          $select_opts[ $m ] = array( 'name' => $m );
        }
      }
    } else {

      if ( is_array( $o['opts'] ) ) {
        $select_opts = $o['opts']; }
    }

    // loop through options and set 'selected' if it is set.
    $selected = false;
    foreach ( $select_opts as $v => $s ) {
      if ( $o['val'] == $v ) {
        $select_opts[ $v ]['val'] = 'selected';
        $selected = true;
        break;
      }
    }
    // if no value was selected and a default is provided then show the default as selected.
    if ( ! $selected && $default ) {
      $select_opts[ $default ]['val'] = 'selected';
    }
    ?>

      <span class="pl-description"><?php echo $o['label'];?></span>
      <select id="<?php echo $o['id'];?>" class="pl-opt" type="select" name="<?php echo $o['name'];?>" <?php echo $o['required'];?> >
        <option value=""><?php echo $default_text;?></option>
        <?php foreach ( $select_opts as $key => $s ) {
                $val = ( isset( $s['val'] ) ) ? $s['val'] : '';
                printf( '<option value="%s" %s>%s</option>', $key, $val, $s['name'] );
}
        ?>
      </select>
      <?php if ( 'menu' == $type ) { printf( '<a href="%s" class="button">Add/Edit Menus</a>', admin_url( 'nav-menus.php' ) ); }
      ?>

    <?php
  }

  function option_image_upload( $o, $mode = 'image', $handling = 'url' ) {

    if ( 'id' == $handling && ! empty( $o['val'] ) ) {
      $att = wp_get_attachment_image_src( $o['val'] );
      $url = $att[0];
    } elseif ( ! empty( $o['val'] ) ) {
      $url = $o['val'];
    } else {
      $url = pl_framework_url( 'images' ) . '/image-preview.png';
    }

    $size = ( 'id' == $mode ) ? '14' : '36';

    $place = ucfirst( $mode );

    $place .= ( 'id' == $handling ) ? ' ID' : ' URL';

    $button_text = ( 'file' == $mode ) ? 'Upload' : 'Select';

    $button_text = ( '' != $o['label'] ) ? $o['label'] : $button_text;

    ?>
    <label for="upload_image" class="image_uploader">
      <?php if ( 'video' != $mode && 'audio' != $mode && 'file' != $mode ) :  ?>
      <div class="image_preview">
        <div class="image_preview_wrap pl-contrast">
          <img class="the_preview_image" src="<?php echo $url;?>" />
        </div>
      </div>
      <?php endif; ?>
      <div class="image_input">
          <input id="<?php echo $o['id'];?>" class="upload_image_option pl-opt pl-form-control" type="text" size="<?php echo $size;?>" name="<?php echo $o['name'];?>" placeholder="<?php echo $place;?>" value="<?php echo $o['val'];?>" <?php echo $o['required'];?> />
          <p>
            <button class="button button-primary image_upload_button pl-form-image-upload" data-mode="<?php echo $mode;?>" data-handling="<?php echo $handling;?>"><i class="pl-icon pl-icon-upload"></i> <?php echo $button_text;?></button>
          </p>

      </div>
      <div class="clear"></div>
    </label>

    <?php
  }

  function get_option( $o ) {
    if ( '' != $o['val'] ) {
      return $o['val']; }

    if ( isset( $o['default'] ) && '' != $o['default'] ) {
      return $o['default']; }
  }

  function setup_kses( $o ) {
    if ( isset( $o['kses'] ) ) {
      if ( ! is_array( $o['kses'] ) ) { // if kses is set to anything but an array, then it means bypass..
        $kses = 'bypass';
      } else {
        $kses = $o['kses'];
      }
      printf( "<input type='hidden' name='settings_kses[%s]' value='%s' />", $o['key'], json_encode( $kses ) );
    }
  }
} // End of Class


function pl_create_form( $config ) {

  $config = wp_parse_args( $config, array(
      'auth'      => true,
      'perm'      => 'read', // default permission
      'auth_fail' => __( "You don't have the account permissions to do this.", 'pl-platform' ),
      'msg_sent'  => __( 'The form was submitted. Thank you!', 'pl-platform' ),
  ));

  $engine = new PL_Form_Engine( $config );

  $title  = ( ! empty( $config['title'] ) )  ? sprintf( '<h3 class="pl-standard-form-title">%s</h3>', $config['title'] ) : '';
  $submit = ( ! empty( $config['submit'] ) ) ? $config['submit'] : 'Submit';
  ?>  
  <form class="pl-standard-form" data-state="form" action="#" method="post" data-callback="<?php echo $config['callback'];?>">

    <div class="pl-form-sending pl-banner">
      <i class="pl-icon pl-icon-cog pl-icon-spin"></i>
    </div>
    <div class="pl-form-sent pl-banner">
      <div clas=s="pl-form-sent-message"><?php echo $config['msg_sent'];?></div>
    </div>
    <div class="pl-form">

<?php

if ( $config['auth'] && ( ! is_user_logged_in() || ! current_user_can( $config['perm'] ) ) ) :

  printf( '<div class="pl-banner">%s</div>', $config['auth_fail'] );

    else :

      echo $title;

      foreach ( $config['opts'] as $o ) {

        $engine->option_engine( $o );

      }
  ?>
      <input type="submit" class="pl-btn pl-btn-primary" value="<?php echo $submit; ?>" />
<?php endif; ?>


    </div>
  </form>
  
  <?php

}

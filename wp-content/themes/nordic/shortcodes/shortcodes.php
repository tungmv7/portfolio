<?php
/*Accordion*/
function umbrella_accordion_shortcode($atts, $content){
    extract(shortcode_atts( array('title' => ''), $atts));
    $return_statement = "
<li>
    <a href='#'>{$title}<i class='icon-plus-sign'></i></a>
    <div class='section_content'>
	    <p>{$content}</p>
    </div>
</li>
";
    return $return_statement;
}
add_shortcode('accordion', 'umbrella_accordion_shortcode');

function umbrella_accordiongroup_shortcode($atts,$content){
    $content = do_shortcode($content);
    $return_statement = "
	<ul class='accordion'>
		{$content}
	</ul>
";
    return $return_statement;
}
add_shortcode('accordiongroup', 'umbrella_accordiongroup_shortcode');
/*Accordion*/

/*Toggles*/
function umbrella_toggle_shortcode($atts, $content){
    extract(shortcode_atts( array('title' => ''), $atts));
	$content = do_shortcode($content);
    $return_statement = "
<li>
    <a href='#'>{$title}<i class='icon-plus-sign'></i></a>
    <div class='section_content'>
	    <p>{$content}</p>
    </div>
</li>
";
    return $return_statement;
}
add_shortcode('toggle', 'umbrella_toggle_shortcode');

function umbrella_togglegroup_shortcode($atts,$content){
    $content = do_shortcode($content);
    $return_statement = "
	<ul class='toggle'>
		{$content}
	</ul>
";
    return $return_statement;
}
add_shortcode('togglegroup', 'umbrella_togglegroup_shortcode');
/*Toggles*/

/*Tabs*/
$UM_GLOBAL_TABS = array();
global $UM_GLOBAL_TABS;
function umbrella_tab_shortcode($atts, $content){
    extract(shortcode_atts( array('title' => ''), $atts));
    global $UM_GLOBAL_TABS;
    array_push($UM_GLOBAL_TABS,array($title,$content));
    return "";
}
add_shortcode('tab', 'umbrella_tab_shortcode');

function umbrella_tabgroup_shortcode($atts, $content){
    $content = do_shortcode($content);
    global $UM_GLOBAL_TABS;

    $return_statemenet = '<div class="tabs">
	<ul class="tab_buttons">';
    foreach($UM_GLOBAL_TABS as $tab){
        $return_statemenet .= '<li><a href="#">'.$tab[0].'</a></li>';
    }
    $return_statemenet .= '</ul>
		<ul class="tab_content">';
    foreach($UM_GLOBAL_TABS as $tab){
        $return_statemenet .= '<li>
			<p>'.$tab[1].'</p>
		</li>';
    }
    $return_statemenet .= '</ul>
	</div>';

    $UM_GLOBAL_TABS = array();
    return $return_statemenet;
}

add_shortcode('tabgroup', 'umbrella_tabgroup_shortcode');
/*Tabs*/

/*Notification*/
function umbrella_notification_shortcode($atts,$content){
    extract(shortcode_atts( array('title' => '','type'=>''), $atts));
    return "<div class='{$type} alert_container'>
                        <a href='#' class='close'></a>
                        <strong>{$title} </strong>{$content}
                    </div>";
}
add_shortcode("notification","umbrella_notification_shortcode");
/*Notification*/

/*Dropcaps*/
function umbrella_dropcap1_shortcode($atts,$content){
    return "<span class='dropcap_01'>{$content}</span>";
}

add_shortcode("dropcap1","umbrella_dropcap1_shortcode");

function umbrella_dropcap2_shortcode($atts,$content){
    return "<span class='dropcap_02'>{$content}</span>";
}

add_shortcode("dropcap2","umbrella_dropcap2_shortcode");
/*Dropcaps*/

/*Highlights*/
function umbrella_highlight_shortcode($atts,$content){
    return "<span class='highlight'>{$content}</span>";
}

add_shortcode("highlight","umbrella_highlight_shortcode");
/*Highlights*/

/*Boxed Content*/
function umbrella_boxed_shortcode($atts,$content){
    return "<div class='background_box'>
                <p>{$content}</p>
            </div>";
}

add_shortcode("boxed","umbrella_boxed_shortcode");

function umbrella_boxed2_shortcode($atts,$content){
    return "<div class='simple_box'>
                <p>{$content}</p>
            </div>";
}

add_shortcode("boxed2","umbrella_boxed2_shortcode");
/*Boxed Content*/

/*Buttons*/
function umbrella_button_shortcode($atts,$content){
    extract(shortcode_atts( array('type'=>'','color'=>'','url'=>''), $atts));

    return "<a href='{$url}' class='{$color} {$type}'>{$content}</a>";
}
add_shortcode("button","umbrella_button_shortcode");
/*Buttons*/

/*Video*/
function umbrella_video_shortcode($atts,$content){
    extract(shortcode_atts( array('poster'=>'','videomp4'=>'','videovp8'=>'','video_ogg'=>''), $atts));
    $sources = "";
    if($videomp4){
        $sources .= "<source type='video/mp4' src='{$videomp4}' />";
    }
    if($videovp8){
        $sources .= "<source type='video/webm' src='{$videovp8}' />";
    }
    if($video_ogg){
        $sources .= "<source type='video/ogg' src='{$video_ogg}' />";
    }

    return "<video controls='controls' preload='none' poster='{$poster}'>
                {$sources}
            </video>";
}
add_shortcode("video","umbrella_video_shortcode");
/*Video*/

/*Layout*/
function umbrella_full_width_shortcode($atts,$content){
	$content = do_shortcode($content);
    return "<div class='col-md-12'><p>{$content}</p></div>";
}
add_shortcode("full_width","umbrella_full_width_shortcode");

function umbrella_half_width_shortcode($atts,$content){
	$content = do_shortcode($content);
    return "<div class='col-md-6'><p>{$content}</p></div>";
}
add_shortcode("half_width","umbrella_half_width_shortcode");

function umbrella_one_third_shortcode($atts,$content){
$content = do_shortcode($content);
    return "<div class='col-md-4'><p>{$content}</p></div>";
}
add_shortcode("one_third","umbrella_one_third_shortcode");

function umbrella_one_fourth_shortcode($atts,$content){
$content = do_shortcode($content);
    return "<div class='col-md-3'><p>{$content}</p></div>";
}
add_shortcode("one_fourth","umbrella_one_fourth_shortcode");

function umbrella_one_sixth_shortcode($atts,$content){
$content = do_shortcode($content);
    return "<div class='col-md-2'><p>{$content}</p></div>";
}
add_shortcode("one_sixth","umbrella_one_sixth_shortcode");

function umbrella_layout_group_shortcode($atts,$content){
    $content = do_shortcode($content);
    return "<div class='row'>{$content}</div>";
}
add_shortcode("layout_group","umbrella_layout_group_shortcode");

function umbrella_layout_shortcode($atts,$content){
    $content = do_shortcode($content);
    return "<div class='columns1'>
                {$content}
            </div>";
}
add_shortcode("layout","umbrella_layout_shortcode");
/*Layout*/

/*Generics*/
add_filter('admin_head', 'um_add_css');
function um_add_css(){
	echo '<style> .dashicons { font-family: "dashicons" !important; } </style>';
}
add_action( 'wp_enqueue_scripts', 'jk_load_dashicons' );
function jk_load_dashicons() {
    wp_enqueue_style( 'dashicons' );
}

add_action('init', 'add_buttons');
function add_buttons() {
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
        return;
    }
    if ( get_user_option('rich_editing') == 'true' ) {
        add_filter( 'mce_external_plugins', 'add_plugin' );
        add_filter( 'mce_buttons_3', 'register_button' );
    }
}

function register_button( $buttons ) {
    array_push( $buttons, "separator", "accordion_btn" );
    array_push( $buttons, "separator", "toggle_btn" );
    array_push( $buttons, "separator", "tab_btn" );
    array_push( $buttons, "separator", "alert_btn" );
    array_push( $buttons, "separator", "dropcap1_btn" );
    array_push( $buttons, "separator", "dropcap2_btn" );
    array_push( $buttons, "separator", "highlight_btn" );
    array_push( $buttons, "separator", "boxed_btn" );
    array_push( $buttons, "separator", "boxed2_btn" );
    array_push( $buttons, "separator", "button_btn" );
    array_push( $buttons, "separator", "video_btn" );
    array_push( $buttons, "separator", "um_layout_btn" );
    return $buttons;
}

function add_plugin( $plugin_array ) {
    $plugin_array['accordion_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    $plugin_array['toggle_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    $plugin_array['tab_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    //$plugin_array['alert_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    //$plugin_array['dropcap1_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    //$plugin_array['dropcap2_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    $plugin_array['highlight_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    //$plugin_array['boxed_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    //$plugin_array['boxed2_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    $plugin_array['button_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    //$plugin_array['video_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    $plugin_array['um_layout_btn'] = get_template_directory_uri() . '/shortcodes/tiny_mce_buttons.js';
    return $plugin_array;
}
/*Generics*/

require_once "dialog-forms.php";
?>
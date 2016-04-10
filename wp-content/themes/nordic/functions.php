<?php
add_theme_support( 'title-tag' );
add_theme_support('post-thumbnails');
add_theme_support('automatic-feed-links');
/*Image Sizes*/
add_image_size("work_post", 249, 187, true);
add_image_size("work_post_medium", 528, 396, true);
add_image_size("work_art_slide", 737, 553, true);
add_image_size("team_member", 352, 352, true);
add_image_size("blog_post_head", 970, 420, true);
/*Image Sizes*/

if (!isset($content_width)) $content_width = 900;

define('ACF_LITE', true);
include_once('acf/acf.php');
/*Retina Part*/
add_action("init", "um_retina_part");

function um_retina_part()
{
    $portrait_images = get_field("portraiot_images", "options") == "Enabled" ? false : true;
    add_image_size("work_post_large", 1100, 675, $portrait_images);
    if (get_field("retina_images", "options") != "Disabled") {
        add_image_size("work_post@2x", 249 * 2, 187 * 2, true);
        add_image_size("work_post_medium@2x", 528 * 2, 396 * 2, true);
        add_image_size("work_art_slide@2x", 737 * 2, 553 * 2, true);
        add_image_size("team_member@2x", 352 * 2, 352 * 2, true);
        add_image_size("blog_post_head@2x", 970 * 2, 420 * 2, true);
        add_image_size("work_post_large@2x", 1100 * 2, 675 * 2, $portrait_images);

        function um_is_high_res()
        {
            if (isset($_COOKIE['devicePixelRatio']) && $_COOKIE['devicePixelRatio'] > 1.5)
                return true;
            else
                return get_field("allways_retina", "options") == "Enabled" ? true : false;
        }

        add_filter('post_thumbnail_html', 'um_post_thumbnail_html', 999, 5);

        function um_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr)
        {
            if (um_is_high_res()) {
                $src = wp_get_attachment_image_src($post_thumbnail_id, $size . "@2x");
                if ($src[3]) {
                    $patterns[] = '/src="(.*?)"/';
                    $replacements[] = 'src="' . $src[0] . '"';

                    return preg_replace($patterns, $replacements, $html);
                } else {
                    return $html;
                }
                return $html;
            } else {
                return $html;
            }
        }
    }
}

/*Retina Part*/

/*Lang*/
add_action('after_setup_theme', 'my_theme_setup');
function my_theme_setup()
{
    load_theme_textdomain('um_lang', get_template_directory() . '/lang');
}

/*Lang*/

require_once "includes/custom-fields.php";

require_once "includes/post-types.php";
require_once "widgets/widgets.php";
require_once "shortcodes/shortcodes.php";
require_once "includes/google-fonts.php";
//require_once "includes/custom_walker.php";
/*Register Option Pages*/
if (function_exists("register_options_page")) {
    register_options_page('Main');
    register_options_page('Branding');
}
/*Register Option Pages*/

/*Register Menu*/
add_action('init', 'register_my_menus');

function register_my_menus()
{
    register_nav_menus(
        array(
            'main_menu' => __('Main Menu', "um-lang"),
            'mobile_menu' => __('Mobile Menu', "um-lang")
        )
    );
}

/*Register Menu*/

/*Register New Fields*/
add_action('acf/register_fields', 'register_fields');
function register_fields()
{
    include_once('includes/acf-location-field/acf-location.php');
}

/*Register New Fields*/

/*Get Likes*/
function get_likes($set = false, $postid = null)
{
    global $post;
    $post_id = $postid ? $postid : $post->ID;
    $views = get_post_meta($post_id, "umbrella_post_likes", true);
    if ($set) {
        $views = intval($views) + 1;
        if ($views) {
            update_post_meta($post_id, "umbrella_post_likes", $views);
        } else {
            add_post_meta($post_id, "umbrella_post_likes", 1);
        }
    }
    return $views ? number_format($views, 0, ' ', ' ') : 0;
}

/*Get Likes*/

/*Change default image sizes*/
add_action('after_switch_theme', 'um_change_image_sizes');

function um_change_image_sizes()
{
    update_option("large_size_w", 1115);
    update_option("large_size_h", 1443);
}

/*Change default image sizes*/

/*Get Video Embedd*/
function getVideoEmbed($vurl, $height = "100%", $width = "100%")
{
    $image_url = parse_url($vurl);
    // Test if the link is for youtube
    $youtube_autoplay = get_field("video_autoplay", "options") == "Enabled" ? "&autoplay=1" : "";
    if ($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com') {
        $array = explode("&", $image_url['query']);
        return '<iframe class="youtube" src="http://www.youtube.com/embed/' . substr($array[0], 2) . '?wmode=transparent&enablejsapi=1' . $youtube_autoplay . '" width="' . $width . '" height="' . $height . '" frameborder="0" allowfullscreen></iframe>'; // Returns the youtube iframe embed code
        // Test if the link is for the shortened youtube share link
    } else if ($image_url['host'] == 'www.youtu.be' || $image_url['host'] == 'youtu.be') {
        $array = ltrim($image_url['path'], '/');
        return '<iframe class="youtube" src="http://www.youtube.com/embed/' . $array . '?wmode=transparent&enablejsapi=1' . $youtube_autoplay . '" width="' . $width . '" height="' . $height . '" frameborder="0" allowfullscreen></iframe>'; // Returns the youtube iframe embed code
        // Test if the link is for vimeo
    } else if ($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com') {
        $hash = substr($image_url['path'], 1);
        return '<iframe class="vimeo" src="http://player.vimeo.com/video/' . $hash . '?title=0&byline=0&portrait=0&api=1' . $youtube_autoplay . '" width="' . $width . '" height="' . $height . '" frameborder="0" webkitAllowFullScreen allowfullscreen></iframe>'; // Returns the vimeo iframe embed code
    }
}

/*Get Video Embedd*/

/*Like System*/
add_action('wp_ajax_um_like_post', 'um_like_post');
add_action('wp_ajax_nopriv_um_like_post', 'um_like_post');

function um_like_post()
{
    $post_id = $_REQUEST["um_post_id"];
    echo get_likes(true, $post_id);
    wp_reset_postdata();
    die;
}

/*Like System*/

/*Contact Form Ajax*/
add_action('wp_ajax_um_send_email', 'um_send_email');
add_action('wp_ajax_nopriv_um_send_email', 'um_send_email');

function um_send_email()
{
    $name = $_REQUEST["um_name"];
    $email = $_REQUEST["um_email"];
    $message = $_REQUEST["um_message"];
    $search_form = $_REQUEST["um_search_form"];
    $to_email = get_field("receiving_e-mal", $search_form);

    $subject = "[" . get_bloginfo('name') . "] - " . $email;
    $message = "
            Name : {$name},
            Email : {$email}

            $message
        ";
    $headers = 'From: ' . $name . ' <' . get_option("admin_email") . '>' . "\r\n";
    wp_mail($to_email, $subject, $message, $headers);
    die;
}

/*Contact Form Ajax*/

/*Load JS and CSS*/

function set_post_type_on_tax_page($query)
{
    if ($query->is_main_query() && is_tax() && get_query_var("portfolio_category")) {
        $query->set('post_type', 'portfolio');
    }
}

add_action('pre_get_posts', 'set_post_type_on_tax_page');

/*Documentation Option Page*/
add_action('admin_menu', 'register_my_custom_submenu_page', 99);

function register_my_custom_submenu_page()
{
    add_submenu_page('acf-options-main', 'Documentation', 'Documentation', 'manage_options', 'admin.php?page=acf-options-documentation', 'my_documentation_menu_callback');
}

function my_documentation_menu_callback()
{
    ?>
    <div class="icon32" id="icon-options-general"><br></div>
    <h2><?php _e("Documentation", "um_lang"); ?></h2>
    <iframe width="100%" height="800px" src="http://documentation.umbrella.al/nordic/" frameborder="0"></iframe>
    <?php
}


// load js and css files
add_action('wp_enqueue_scripts', 'my_load_assets_files');
function my_load_assets_files()
{
    // js
    wp_enqueue_script('jquery');
    wp_enqueue_script('html5shiv', get_template_directory_uri() . "/assets/scripts/html5shiv.js", [], null);
    wp_enqueue_script('modernizr', get_template_directory_uri() . "/assets/scripts/modernizr.js", [], null);
    wp_enqueue_script('move_js', get_template_directory_uri() . "/assets/scripts/move.min.js", [], null);
    wp_enqueue_script('mixitup', get_template_directory_uri() . "/assets/scripts/jquery.mixitup.min.js", [], null);
    wp_enqueue_script('wait_for_images', get_template_directory_uri() . "/assets/scripts/jquery.waitforimages.min.js", array(), 1.0, false);
    wp_enqueue_script('touch_swipe', get_template_directory_uri() . "/assets/scripts/jquery.touchSwipe.min.js", array(), 1.0, false);
    wp_enqueue_script('fancybox', get_template_directory_uri() . "/assets/fancybox/jquery.fancybox.min.js", array(), 1.0, false);
    wp_enqueue_script('um_script', get_template_directory_uri() . "/assets/scripts/start.min.js", [], null);

    // css
    wp_enqueue_style("bootstrap", get_template_directory_uri() . "/assets/css/twttrbootstrap.min.css", [], null);
    wp_enqueue_style("style", get_template_directory_uri() . "/assets/css/style.min.css", [], null);
    wp_enqueue_style("fancybox", get_template_directory_uri() . "/assets/fancybox/jquery.fancybox.min.css", false, "1.0");
    wp_enqueue_style("font_awesome", "//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css", false, null);
    wp_enqueue_style("google_font", "http://fonts.googleapis.com/css?family=Roboto:100,100italic,300,300italic,regular,italic,500,500italic,700,700italic,900,900italic&subset=vietnamese,cyrillic,greek,latin-ext,latin,cyrillic-ext,greek-ext", [], null);

}

// Remove WP Version From Styles
add_filter('style_loader_src', 'my_remove_ver_css_js', 9999);
add_filter('script_loader_src', 'my_remove_ver_css_js', 9999);
function my_remove_ver_css_js($src)
{
    if (strpos($src, 'ver='))
        $src = remove_query_arg('ver', $src);
    return $src;
}

// remove unused files
add_action('init', 'my_disable_embeds_init', 9999);
function my_disable_embeds_init()
{
    if (!is_admin()) {
        wp_deregister_script('wp-embed');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_action('wp_head', 'feed_links_extra', 3); // Display the links to the extra feeds such as category feeds
        remove_action('wp_head', 'feed_links', 2); // Display the links to the general feeds: Post and Comment Feed
        remove_action('wp_head', 'rsd_link'); // Display the link to the Really Simple Discovery service endpoint, EditURI link
        remove_action('wp_head', 'wlwmanifest_link'); // Display the link to the Windows Live Writer manifest file.
        remove_action('wp_head', 'index_rel_link'); // index link
        remove_action('wp_head', 'parent_post_rel_link', 10, 0); // prev link
        remove_action('wp_head', 'start_post_rel_link', 10, 0); // start link
        remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); // Display relational links for the posts adjacent to the current post.
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('template_redirect', 'rest_output_link_header', 11, 0);

    }
}

?>
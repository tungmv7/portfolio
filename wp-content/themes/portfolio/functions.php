<?php
/**
 * Created by PhpStorm.
 * User: tungmangvien
 * Date: 4/12/16
 * Time: 2:51 PM
 */
function excludeCat($query) {
    if ( $query->is_home ) {
        $query->set('cat', '-3'); // my works
    }
    return $query;
}
add_filter('pre_get_posts', 'excludeCat');


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
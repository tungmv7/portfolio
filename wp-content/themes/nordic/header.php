<?php 
	if(isset($_REQUEST["um_body_class"]) && $_REQUEST["um_body_class"]){
		echo join( ' ', get_body_class( ) );
		die;
	}
	if(!isset($_REQUEST["um_ajax_load_site"])): 
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if(get_field("site_favico","options")): ?>
        <link rel="icon" type="image/png" href="<?php the_field("site_favico","options"); ?>">
    <?php endif; ?>

    <script type="text/javascript">
        var um_ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        var home_columns_animation_delay = 200;
        var ajax_site = <?php echo get_field("ajax_site","options") == 'Disabled' ? "false" : "true"; ?>;
		var blog_page = 0;
		var project_page = 0;
		var slider_interval = null;
    </script>
    <?php if(get_field("custom_css","options")): ?>
        <style type="text/css">
            <?php the_field("custom_css","options"); ?>
        </style>
    <?php endif; ?>
    <?php if(get_field("custom_javascript","options")): ?>
        <script type="text/javascript">
            <?php the_field("custom_javascript","options"); ?>
        </script>
    <?php endif; ?>
    <?php wp_head(); ?>
    <link href="<?php echo get_stylesheet_directory_uri();?>/style.css" rel="stylesheet">
</head>
<body <?php body_class(); ?> >
<?php if(get_field("ajax_site","options") != 'Disabled'): ?>
<div class="loader">
    <div class="bubblingG">
        <span id="bubblingG_1">
        </span>
        <span id="bubblingG_2">
        </span>
        <span id="bubblingG_3">
        </span>
    </div>
</div>
<?php endif; ?>
<div id="header">
    <div class="site-title">
        <?php if(get_field("site_logo","options")): ?>
        <a href="<?php echo site_url(); ?>" class="logo">
            <img src="<?php the_field("site_logo","options"); ?>" alt="<?php bloginfo("name"); ?>" style="width: 100%; background-color: #f5f5f5; box-shadow: 1px 1px 1px #ccc;">
        </a>
        <?php endif; ?>
    </div>
    <div class="main-menu-holder">
        <?php
            wp_nav_menu(array("theme_location" => "main_menu","menu_id"=>"main_menu","menu_class"=>"main_menu visible-lg visible-md"));
            wp_nav_menu(array("theme_location" => "mobile_menu","menu_id"=>"mobile_menu","menu_class"=>"mobile_menu non-visible"));
        ?>
    </div>
    <a href="#" class="menu-toggle visible-xs visible-sm"><i class="icon-align-justify"></i></a>
</div>
<!--Open Inner Content-->
<div id="inner-content">
<?php endif; ?>
<title><?php bloginfo('name'); ?> | <?php if(is_home() || is_front_page()){ bloginfo("description"); } wp_title("",true,""); ?></title>
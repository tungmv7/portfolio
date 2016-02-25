<?php
    function sidebar_widgets_init(){
        register_sidebar( array(
            'name' => __('Footer' , "um_lang"),
            'id' => 'footer_sidebar_1',
            'description' => __( 'Footer' ,"um_lang" ),
            'before_widget' => '<div id="%1$s" class="um-about widget col-sm-4 %2$s">',
            'after_widget' => "</div>",
            'before_title' => '<h5 class="widget-title">',
            'after_title' => '</h5>',
        ) );
    }

    add_action( 'widgets_init', 'sidebar_widgets_init' );

    require_once "widget-latest-posts.php";
    require_once "widget-contact-form.php";
?>
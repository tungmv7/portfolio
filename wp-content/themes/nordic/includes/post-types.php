<?php
    add_action("init","my_post_types");
    function my_post_types(){
        $rewrite = array();
        $rewrite["slug"] = "portfolio";
        $rewrite["with_front"] = true;

        register_post_type( 'portfolio',
            array(
                'labels' => array(
                    'name' => __( "Portfolio" ,"um_lang"),
                    'singular_name' => __( "Portfolios" , "um_lang" )
                ),
                'public' => true,
                'supports' => array('title','editor','thumbnail','comments','author'),
                'rewrite' => $rewrite
            )
        );

        register_post_type( 'contact_form',
            array(
                'labels' => array(
                    'name' => __( "Contact Forms" ,"um_lang"),
                    'singular_name' => __( "Contact Form" , "um_lang" )
                ),
                'public' => true,
                'supports' => array('title')
            )
        );

        register_taxonomy('portfolio_category',array (
            0 => 'portfolio',
        ),array( 'hierarchical' => true, 'label' => 'Portfolio Category','show_ui' => true,'query_var' => true,'singular_label' => 'Portfolio Category', 'rewrite' => array( 'slug' => 'portfolio_category' )) );

    }
?>
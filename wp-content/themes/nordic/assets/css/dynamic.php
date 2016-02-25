<?php header('Content-Type: text/css; charset: UTF-8'); ?>

<?php if(isset($_GET["font"]) && $_GET["font"]): ?>
    body, a, h1, h2, h3, h4, h5, h6 {
        font-family: '<?php echo $_GET["font"]; ?>', Arial, sans-serif;
    }
<?php endif; ?>

<?php if(isset($_GET["bg_pattern"]) && $_GET["bg_pattern"]): ?>
    body{
        background-image:url('<?php echo $_GET["bg_pattern"]; ?>');
        background-repeat:repeat;
    }
<?php endif; ?>

<?php if(isset($_GET["bg_image"]) && $_GET["bg_image"]): ?>
    body{
        background-image:url('<?php echo $_GET["bg_image"]; ?>');
        background-repeat:no-repeat;
        background-size:cover;
        -webkit-background-size:cover;
        -moz-background-size:cover;
        -o-background-size:cover;
        background-attachment:fixed;
        background-position:center center;
    }
<?php endif; ?>

<?php if(isset($_GET["brand_color"]) && $_GET["brand_color"]): ?>
.post_block ul li i, .um-blog-w.widget ul li p i, .post-content ul.categories li i, .liked *, ul.accordion li a i, ul.toggle li a i, .widget.widget_calendar table a {
color: <?php echo $_GET["brand_color"]; ?> !important;
}

.service:hover .service-icon, .yellow, .yellow:hover, .highlight {
background-color: <?php echo $_GET["brand_color"]; ?> !important;
}
<?php endif; ?>

<?php if(isset($_GET["hover_color"]) && $_GET["hover_color"]): ?>
<?php
    function hex2rgb( $colour , $opacity) {
        if ( $colour[0] == '#' ) {
            $colour = substr( $colour, 1 );
        }
        if ( strlen( $colour ) == 6 ) {
            list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
        } elseif ( strlen( $colour ) == 3 ) {
            list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
        } else {
            return false;
        }
        $r = hexdec( $r );
        $g = hexdec( $g );
        $b = hexdec( $b );
        return "rgba({$r},{$g},{$b},{$opacity})";
    }
?>
    .post-thumb .hover-state {
        background-color: <?php echo hex2rgb($_GET["hover_color"],"0.8")?>;
    }
<?php endif; ?>
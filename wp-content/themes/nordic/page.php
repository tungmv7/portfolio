<?php get_header(); ?>
    <div class="container single-work short-code left-space">

        <div class="row project-content-top">
            <div class="col-sm-7">
                <h5 class="section-title"><?php the_title(); ?></h5>
            </div>
        </div>
        <div class="row project-content">
            <div class="col-sm-12">
                <?php
                global $post;
                setup_postdata($post);
                the_content();
                ?>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('.accordion li:first-child').find('a').addClass('active').find('i').removeClass('icon-plus-sign').addClass('icon-minus-sign');
                $('.accordion li:first-child').find('.section_content').show();

                $('.tabs .tab_buttons > li:first-child').find('a').addClass('active');
                $('.tabs .tab_content li:first-child').show();
            });
        </script>
    </div>
<?php get_footer(); ?>
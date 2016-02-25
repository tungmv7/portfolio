<?php
/*Template Name:Home (Columns)*/
get_header();
?>

    <div class="home_four container left-space">
	
        <div class="col-switcher">
            <a href="#" class="col-four project_col_four active"><i class="icon-th"></i></a>
            <a href="#" class="col-two project_col_two"><i class="icon-th-large"></i></a>
        </div>

        <div class="column-4 row" style="display:none1">
            <?php
            $arguments = array();
            $arguments["post_type"] = "portfolio";
            if(get_field("include_only_those_categories") || get_field("exclude_categories")){

                $arguments["tax_query"] = array();
                if(get_field("include_only_those_categories") && get_field("exclude_categories")){
                    $arguments["tax_query"]['relation'] = 'AND';
                }

                if(get_field("exclude_categories")){
                    $exclude_categories = explode(",",get_field("exclude_categories"));
                    array_push($arguments["tax_query"],array(
                        'taxonomy' => 'portfolio_category',
                        'field' => 'slug',
                        'terms' => $exclude_categories,
                        'operator' => 'NOT IN'
                    ));
                }

                if(get_field("include_only_those_categories")){
                    $include_categories = explode(",",get_field("include_only_those_categories"));
                    array_push($arguments["tax_query"],array(
                        'taxonomy' => 'portfolio_category',
                        'field' => 'slug',
                        'terms' => $include_categories,
                        'operator' => 'IN'
                    ));
                }
            }
            if(get_field("exclude_posts")){
                $exlude_posts = array();
                foreach(get_field("exclude_posts") as $tmpPost){
                    array_push($exlude_posts,$tmpPost->ID);
                }
                $arguments["post__not_in"] = $exlude_posts;
            }
            $arguments["posts_per_page"] = get_field("number_of_posts_small_grid");
            $the_query = new WP_Query( $arguments );
            while ( $the_query->have_posts() ) :  $the_query->the_post();
                ?>
                <div class="column post_block col-sm-3 widget_anim">
                    <div class="post-thumb">
                        <a href="<?php the_permalink(); ?>">
                            <div class="hover-state">
                                <p class="likes"><i class="icon-heart"></i> <?php echo get_likes(); ?></p>
                                <p class="cont"><i class="icon-search"></i></p>
                            </div>
                            <?php the_post_thumbnail("work_post"); ?>
                        </a>
                    </div>
                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                    <?php
                    $terms = wp_get_post_terms( $post->ID,"portfolio_category" );
                    $terms_html_array = array();
                    foreach($terms as $t){
                        $term_name = $t->name;
                        $term_link = get_term_link($t->slug,$t->taxonomy);
                        array_push($terms_html_array,"<a href='{$term_link}'>{$term_name}</a>");
                    }
                    $terms_html_array = implode(", ",$terms_html_array);
                    ?>
                    <ul>
                        <?php if($terms_html_array): ?>
                            <li><i class='icon-angle-right'></i><?php echo $terms_html_array; ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <div class="column-2 row" style="display:block1">
            <?php
            $arguments = array();
            $arguments["post_type"] = "portfolio";
            if(get_field("include_only_those_categories") || get_field("exclude_categories")){

                $arguments["tax_query"] = array();
                if(get_field("include_only_those_categories") && get_field("exclude_categories")){
                    $arguments["tax_query"]['relation'] = 'AND';
                }

                if(get_field("exclude_categories")){
                    $exclude_categories = explode(",",get_field("exclude_categories"));
                    array_push($arguments["tax_query"],array(
                        'taxonomy' => 'portfolio_category',
                        'field' => 'slug',
                        'terms' => $exclude_categories,
                        'operator' => 'NOT IN'
                    ));
                }

                if(get_field("include_only_those_categories")){
                    $include_categories = explode(",",get_field("include_only_those_categories"));
                    array_push($arguments["tax_query"],array(
                        'taxonomy' => 'portfolio_category',
                        'field' => 'slug',
                        'terms' => $include_categories,
                        'operator' => 'IN'
                    ));
                }
            }
			
            if(get_field("exclude_posts")){
                $exlude_posts = array();
                foreach(get_field("exclude_posts") as $tmpPost){
                    array_push($exlude_posts,$tmpPost->ID);
                }
                $arguments["post__not_in"] = $exlude_posts;
            }
            $arguments["posts_per_page"] = get_field("number_of_posts_large");
            $the_query = new WP_Query( $arguments );
            while ( $the_query->have_posts() ) :  $the_query->the_post();
                ?>
                <div class="column post_block col-sm-6">
                    <div class="post-thumb">
                        <a href="<?php the_permalink(); ?>">
                            <div class="hover-state">
                                <p class="likes"><i class="icon-heart"></i> <?php echo get_likes(); ?></p>
                                <p class="cont"><i class="icon-search"></i></p>
                            </div>
                            <?php the_post_thumbnail("work_post_medium"); ?>
                        </a>
                    </div>
                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                    <?php
                    $terms = wp_get_post_terms( $post->ID,"portfolio_category" );
                    $terms_html_array = array();
                    foreach($terms as $t){
                        $term_name = $t->name;
                        $term_link = get_term_link($t->slug,$t->taxonomy);
                        array_push($terms_html_array,"<a href='{$term_link}'>{$term_name}</a>");
                    }
                    $terms_html_array = implode(", ",$terms_html_array);
                    ?>
                    <ul>
                        <?php if($terms_html_array): ?>
                            <li><i class='icon-angle-right'></i><?php echo $terms_html_array; ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endwhile;wp_reset_postdata(); ?>
        </div>

        <script type="text/javascript">
            jQuery(".widget_anim").attr("style","");
            jQuery('#inner-content').waitForImages(function() {
                jQuery(".widget_anim").attr("style","").promise().done(function(){
                    var duration = home_columns_animation_delay;
                    var delay = duration;
                    jQuery(".widget_anim").each(function(){
                        /*move(jQuery(this))
                         .set('opacity', 1)
                         .scale(1)
                         .duration(duration)
                         .delay(delay)
                         .end();*/
                        var this_object = jQuery(this);
                        window.setTimeout(function(){
                            this_object.addClass("widget_anim_done");
                        },delay);
                        delay += duration;
                    });
                });				
            });
        </script>

    </div>

<?php get_footer(); ?>
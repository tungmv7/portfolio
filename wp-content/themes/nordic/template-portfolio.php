<?php if(isset($_REQUEST["um_page"])): ?>
    <div>
        <div class="column-4 row filterable">
            <?php
			$the_query;
			if(is_tax()){
				$the_query = $wp_query;
			}else{
				$arguments = array();
				$arguments["post_type"] = "portfolio";
				$exlucde_cats = get_field("exclude_categories");
				$exlucde_cats_arr = array();
				if($exlucde_cats){
					foreach($exlucde_cats as $cat){
						array_push($exlucde_cats_arr,$cat["category"]->slug);
					}
					$arguments["tax_query"] = array(array(
						'taxonomy' => 'portfolio_category',
						'field' => 'slug',
						'terms' => $exlucde_cats_arr,
						'operator' => 'NOT IN'
					));
				}
				$arguments["posts_per_page"] = 12;
				$arguments["paged"] = $_REQUEST["um_paged"];
				$the_query = new WP_Query( $arguments );
			}
            while ( $the_query->have_posts() ) :  $the_query->the_post();
                $terms = wp_get_post_terms( $post->ID,"portfolio_category" );
                $terms_html_array = array();
                $terms_id_array = array();
                $term_classes = "";
                foreach($terms as $t){
                    $term_name = $t->name;
                    $term_link = get_term_link($t->slug,$t->taxonomy);
                    array_push($terms_html_array,"<a href='{$term_link}'>{$term_name}</a>");
                    array_push($terms_id_array,$t->slug);
                    $term_classes .= "um_".$t->slug." ";
                }
                $terms_html_array = implode(", ",$terms_html_array);
                ?>
                <div class="column project_block post_block col-sm-3 mix_all <?php echo $term_classes; ?>" data-filter='<?php echo implode(" ",$terms_id_array); ?> mix_all'>
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
                    <ul>
                        <?php if($terms_html_array): ?>
                            <li><i class='icon-angle-right'></i><?php echo $terms_html_array; ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <div class="column-2 row filterable">
            <?php
			$the_query;
			if(is_tax()){
				$the_query = $wp_query;
			}else{
				$arguments = array();
				$arguments["post_type"] = "portfolio";
				$exlucde_cats = get_field("exclude_categories");
				$exlucde_cats_arr = array();
				if($exlucde_cats){
					foreach($exlucde_cats as $cat){
						array_push($exlucde_cats_arr,$cat["category"]->slug);
					}
					$arguments["tax_query"] = array(array(
						'taxonomy' => 'portfolio_category',
						'field' => 'slug',
						'terms' => $exlucde_cats_arr,
						'operator' => 'NOT IN'
					));
				}
				$arguments["posts_per_page"] = 4;
				$arguments["paged"] = $_REQUEST["um_paged"];
				$the_query = new WP_Query( $arguments );
			}
            while ( $the_query->have_posts() ) :  $the_query->the_post();
                $terms = wp_get_post_terms( $post->ID,"portfolio_category" );
                $terms_html_array = array();
                $terms_id_array = array();
                $term_classes = "";
                foreach($terms as $t){
                    $term_name = $t->name;
                    $term_link = get_term_link($t->slug,$t->taxonomy);
                    array_push($terms_html_array,"<a href='{$term_link}'>{$term_name}</a>");
                    array_push($terms_id_array,$t->slug);
                    $term_classes .= "um_".$t->slug." ";
                }
                $terms_html_array = implode(", ",$terms_html_array);
                ?>
                <div class="column project_block post_block col-sm-6 <?php echo $term_classes; ?>" data-filter='<?php echo implode(" ",$terms_id_array); ?> mix_all'>
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
                    <ul>
                        <?php if($terms_html_array): ?>
                            <li><i class='icon-angle-right'></i><?php echo $terms_html_array; ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endwhile;wp_reset_postdata(); ?>
        </div>
    </div>
    <?php die();endif;?>
<?php
/*Template Name:Portfolio*/
get_header();
?>
    <div class="container protfolio-page left-space">
        <div class="row">
            <div class="col-sm-12">
                <h5 class="section-title">
					<?php 
						if(is_tax()){
							$queried_object = get_queried_object();
							echo get_queried_object()->name;
						}else{
							the_title();
						}
					?>
				</h5>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 projects-intro">
                <h1><?php the_field("page_heading"); ?></h1>
                <h4><?php the_field("page_sub_heading"); ?></h4>
            </div>
        </div>

        <?php
        $cat_arguments = array();
        $exlucde_cats = get_field("exclude_categories");
        $exlucde_cats_arr = array();
        if($exlucde_cats){
            foreach($exlucde_cats as $cat){
                array_push($exlucde_cats_arr,$cat["category"]->term_id);
            }
            $cat_arguments["exclude_tree"] = $exlucde_cats_arr;
        }
        $terms = get_terms("portfolio_category",$cat_arguments);
        if($terms):
            ?>
            <div class="row categories-p">
                <ul class="col-sm-12">
                    <li><a href="#" data-filter="mix_all" class="active"><?php _e("All","um_lang"); ?></a></li>
                    <?php foreach($terms as $term): ?>
                        <li><a href="<?php echo get_term_link($term->slug,"portfolio_category"); ?>" data-filter="um_<?php echo $term->slug; ?>"><?php echo $term->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

    </div>
    <div class="projects container left-space">
        <div class="col-switcher">
            <a href="#" class="col-four project_col_four active"><i class="icon-th"></i></a>
            <a href="#" class="col-two project_col_two"><i class="icon-th-large"></i></a>
        </div>


        <div class="column-4 row filterable" style1="display:none">
            <?php
			$the_query;
			if(is_tax()){
				$the_query = $wp_query;
			}else{
				$arguments = array();
				$arguments["post_type"] = "portfolio";

				$exlucde_cats = get_field("exclude_categories");
				$exlucde_cats_arr = array();
				if($exlucde_cats){
					foreach($exlucde_cats as $cat){
						array_push($exlucde_cats_arr,$cat["category"]->slug);
					}
					$arguments["tax_query"] = array(array(
						'taxonomy' => 'portfolio_category',
						'field' => 'slug',
						'terms' => $exlucde_cats_arr,
						'operator' => 'NOT IN'
					));
				}

				$arguments["posts_per_page"] = 12;
				$the_query = new WP_Query( $arguments );
			}
            while ( $the_query->have_posts() ) :  $the_query->the_post();
                $terms = wp_get_post_terms( $post->ID,"portfolio_category" );
                $terms_html_array = array();
                $terms_id_array = array();
                $term_classes = "";
                foreach($terms as $t){
                    $term_name = $t->name;
                    $term_link = get_term_link($t->slug,$t->taxonomy);
                    array_push($terms_html_array,"<a href='{$term_link}'>{$term_name}</a>");
                    array_push($terms_id_array,$t->slug);
                    $term_classes .= "um_".$t->slug." ";
                }
                $terms_html_array = implode(", ",$terms_html_array);
                ?>
                <div class="column project_block post_block col-sm-3 mix_all <?php echo $term_classes; ?>" data-filter='<?php echo implode(" ",$terms_id_array); ?> mix_all'>
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
                    <ul>
                        <?php if($terms_html_array): ?>
                            <li><i class='icon-angle-right'></i><?php echo $terms_html_array; ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <div class="column-2 row filterable" style1="display:block">
            <?php
			$the_query;
			if(is_tax()){
				$the_query = $wp_query;
			}else{
				$arguments = array();
				$arguments["post_type"] = "portfolio";

				$exlucde_cats = get_field("exclude_categories");
				$exlucde_cats_arr = array();
				if($exlucde_cats){
					foreach($exlucde_cats as $cat){
						array_push($exlucde_cats_arr,$cat["category"]->slug);
					}
					$arguments["tax_query"] = array(array(
						'taxonomy' => 'portfolio_category',
						'field' => 'slug',
						'terms' => $exlucde_cats_arr,
						'operator' => 'NOT IN'
					));
				}

				$arguments["posts_per_page"] = 4;
				$the_query = new WP_Query( $arguments );
			}
            while ( $the_query->have_posts() ) :  $the_query->the_post();
                $terms = wp_get_post_terms( $post->ID,"portfolio_category" );
                $terms_html_array = array();
                $terms_id_array = array();
                $term_classes = "";
                foreach($terms as $t){
                    $term_name = $t->name;
                    $term_link = get_term_link($t->slug,$t->taxonomy);
                    array_push($terms_html_array,"<a href='{$term_link}'>{$term_name}</a>");
                    array_push($terms_id_array,$t->slug);
                    $term_classes .= "um_".$t->slug." ";
                }
                $terms_html_array = implode(", ",$terms_html_array);
                ?>
                <div class="column project_block post_block col-sm-6 <?php echo $term_classes; ?>" data-filter='<?php echo implode(" ",$terms_id_array); ?> mix_all'>
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
                    <ul>
                        <?php if($terms_html_array): ?>
                            <li><i class='icon-angle-right'></i><?php echo $terms_html_array; ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endwhile;wp_reset_postdata(); ?>
        </div>
		
		<?php if($the_query->max_num_pages > 1):wp_reset_postdata(); ?>
        <div class="col-sm-12 load-more-cont">
            <a href="<?php the_permalink(); ?>" class="load-more portfolio_load_more"><?php _e("Load More","um_lang"); ?></a>
        </div>
		<?php endif; ?>
		
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $(".filterable").mixitup({
                targetSelector : "div.project_block",
                filterLogic : "and",
                multiFilter : true,
				animation : false
            });
        });
        project_page = 1;
    </script>
<?php get_footer(); ?>
<?php if(isset($_REQUEST["um_page"])): ?>
    <?php while ( $wp_query->have_posts() ) :  $wp_query->the_post(); ?>
        <div class="column post_block col-sm-6">
            <div class="post-thumb">
                <a href="<?php the_permalink(); ?>">
                <div class="hover-state">
<!--                    <p class="likes"><i class="icon-heart"></i> --><?php //echo get_likes(); ?><!--</p>-->
                    <p class="cont"><i class="icon-search"></i></p>
                </div>
                <?php the_post_thumbnail("work_post_medium"); ?>
                </a>
            </div>
            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
            <?php
            $terms = wp_get_post_terms( $post->ID,"category" );
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
    <?php endwhile;?>
<?php die();endif; ?>
<?php
/*Template Name:Blog*/
get_header();
?>

<div class="container blog-page left-space">
	<div class="row blog-posts">
		<div class="col-sm-12">
			<h5 class="section-title"><?php wp_title(""); ?></h5>
		</div>
        <div class="posts_loop">
            <?php while ( $wp_query->have_posts() ) :  $wp_query->the_post(); ?>
                <div class="column post_block col-sm-6">
                    <div class="post-thumb">
                        <a href="<?php the_permalink(); ?>">
                        <div class="hover-state">
<!--                            <p class="likes"><i class="icon-heart"></i> --><?php //echo get_likes(); ?><!--</p>-->
                            <p class="cont"><i class="icon-search"></i></p>
                        </div>
                        <?php the_post_thumbnail("work_post_medium"); ?>
                        </a>
                    </div>
                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                    <?php
                    $terms = wp_get_post_terms( $post->ID,"category" );
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
            <?php endwhile; ?>
        </div>
		<?php if($wp_query->max_num_pages > 1): ?>
		<div class="col-sm-12 load-more-cont">
            <a href="<?php the_permalink(); ?>" class="load-more blog_load_more"><?php _e("Load More","um_lang"); ?></a>
        </div>
		<?php endif; ?>
	</div>
</div>

<script type="text/javascript">
    blog_page = parseInt("<?php echo get_query_var("paged") ? get_query_var("paged") : 1?>");
	jQuery(document).ready(function($){
		$('div.posts_loop').waitForImages( function() {
			$('div.posts_loop').masonry({
				itemSelector: '.post_block'
			});
		});
	});
	
</script>

<?php get_footer(); ?>


<div class="column post_block">
			
</div>
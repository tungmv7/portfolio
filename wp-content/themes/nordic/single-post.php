<?php get_header(); ?>

<div id="post-<?php the_ID(); ?>" <?php post_class(array("container","single-blogpost","left-space")); ?>>
	<div class="row">
		<div class="post-img col-sm-12">
			<?php the_post_thumbnail("blog_post_head"); ?>
		</div>
		<div class="post-buttons col-sm-12">
			<ul>
				<li>
					<i class="icon-time"></i><br>
					<p><?php echo get_the_date("d M"); ?></p>
				</li>
				<li>
					<i class="icon-user"></i><br>
					<p><?php the_author_meta( "display_name" , $post->post_author ); ?></p>
				</li>
                <li>
                    <?php
                    $coockie_offset = 'um_liked_'.$post->ID;
                    $liked = isset($_COOKIE[$coockie_offset]) && $_COOKIE[$coockie_offset] ? "liked" : "";
                    ?>
                    <a href="#" class="like <?php echo $liked; ?>" data-postid="<?php echo $post->ID; ?>">
                        <i class="icon-heart"></i><br>
                        <p><?php echo get_likes(); ?></p>
                    </a>
                </li>
			</ul>
		</div>
	</div>
	<div class="row post-content">
		<div class="col-sm-10">
			<h1 class="title-of-post"><?php the_title(); ?></h1>
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
            <?php if($terms_html_array): ?>
                <ul class="categories">
                    <li><i class='icon-angle-right'></i><?php echo $terms_html_array; ?></li>
                </ul>
            <?php endif; ?>
			<div class="content">
				<?php
                    global $post;
                    setup_postdata($post);
                    the_content();
                ?>
			</div>
		</div>
	</div>

	<div class="row tags">
		<div class="col-sm-10">
			<h5 class="section-title"><?php _e("Tags","um_lang"); ?></h5>
            <?php the_tags('<ul><li>','</li><li>','</li></ul>'); ?>
		</div>
	</div>

    <?php comments_template(); ?>

</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.accordion li:first-child').find('a').addClass('active').find('i').removeClass('icon-plus-sign').addClass('icon-minus-sign');
		$('.accordion li:first-child').find('.section_content').show();

		$('.tabs .tab_buttons > li:first-child').find('a').addClass('active');
		$('.tabs .tab_content li:first-child').show();
	});
</script>
<?php get_footer(); ?>
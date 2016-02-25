<?php
/*Template Name:Home (With Slider)*/
get_header();
?>

<div class="container slider-home left-space">

    <?php
        $slides = get_field("slider");
        if($slides):
    ?>
	<div class="row slider">
		<div class="col-sm-12">
			<ul class="home_slider">
                <?php foreach($slides as $slide): ?>
                    <li>
                        <?php if($slide["image"]): ?>
                            <?php
                                $html = "<img src='".$slide["image"]["sizes"]["large"]."' alt='".$slide["image"]["alt"]."'/>";
                                echo apply_filters( 'post_thumbnail_html', $html, $post->ID , $slide["image"]["id"], "large" , array("alt"=>$slide["image"]["alt"]) );
                            ?>
                        <?php endif; ?>
                        <h1><a href="<?php echo $slide["slide_url"]; ?>"><?php echo $slide["slide_caption"]; ?></a></h1>
                    </li>
                <?php endforeach; ?>
			</ul>
		</div>
	</div>
    <?php endif; ?>

    <?php
        $services = get_field("what_we_do");
        if($services):
    ?>
	<div class="row services">
		<div class="col-sm-12">
			<h5 class="section-title"><?php _e("What we do","um_lang"); ?></h5>
		</div>
        <?php foreach($services as $service): ?>
		<div class="col-lg-4 service">
            <?php if($service["font_awesome"]): ?>
                <a href="<?php echo $service["service_url"]; ?>" class="service-icon">
                    <i class="<?php echo $service["font_awesome"]; ?>"></i>
                </a>
            <?php endif; ?>
			<h3><?php echo $service["service_title"]; ?></h3>
			<p><?php echo $service["service_description"]; ?></p>
		</div>
        <?php endforeach; ?>
	</div>
    <?php endif; ?>

    <?php
        wp_reset_postdata();
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
        $arguments["posts_per_page"] = get_field("number_of_posts");
        $the_query = new WP_Query( $arguments );
        if($the_query->found_posts):
    ?>
	<div class="row latest-work">
        <div class="col-sm-12">
            <h5 class="section-title"><?php _e("Our latest works","um_lang"); ?></h5>
        </div>
		<div>
        <?php while ( $the_query->have_posts() ) :  $the_query->the_post(); ?>
		<div class="column post_block col-sm-3">
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
            <?php if($terms_html_array): ?>
			<ul>
				<li><i class='icon-angle-right'></i><?php echo $terms_html_array; ?></li>
			</ul>
            <?php endif; ?>
		</div>
        <?php endwhile; ?>
		</div>
	</div>
    <?php endif;wp_reset_postdata(); ?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
			function make_slider_height(){
				var visible_slide = $(".home_slider li:visible");
				$(".home_slider li").each(function(){
					var this_el = $(this);
					this_el.show();
					this_el.css("height",this_el.find("img").height());
				});				
				$(".home_slider li").hide();
				visible_slide.show();
				visible_slide.parent().css("height",visible_slide.height());
			}
			
			$('#inner-content').waitForImages( function() {
				make_slider_height();
			});
			$(window).resize(function(){
				make_slider_height();
			});
			
			if($(".slider-home .slider ul li").length > 1){
				clearInterval(slider_interval);
				slider_interval = setInterval(function(){
					var currentSlide = $(".slider-home .slider ul li:visible");
					var nextSlide = $(".slider-home .slider ul li:visible").next();
					var slideTo;
					if(nextSlide.length) {
						slideTo = nextSlide;
					} else {
						slideTo = $(".slider-home .slider ul li").eq(0);
					}
					currentSlide.stop(true,true).fadeOut('slow');
					currentSlide.parent().css("height",currentSlide.height());
					slideTo.stop(true,true).fadeIn('slow');
				}, 8000);
			}
        });
    </script>
</div>

<?php get_footer(); ?>
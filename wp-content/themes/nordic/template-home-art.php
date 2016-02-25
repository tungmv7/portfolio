<?php
/*Template Name:Home (Art)*/
get_header();
?>

<div class="galleryMain left-space">

	<div class="holder">
        <div class="figure_container">
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
                <figure class="post-thumb">
                    <div>
                        <?php the_post_thumbnail("work_art_slide"); ?>
                        <a href="<?php the_permalink(); ?>">
                        <div class="hover-state">
                            <p class="likes"><i class="icon-heart"></i> <?php echo get_likes(); ?></p>
                            <p class="cont"><i class="icon-search"></i></p>
                        </div>
                        </a>
                    </div>
                </figure>
            <?php endwhile; ?>
        </div>
		<div class="floor">
			<div>
				<a href="#" class="art_prev"><i class="icon-angle-left"></i></a>
				<a href="#" class="art_next"><i class="icon-angle-right"></i></a>
			</div>
		</div>
	</div>

    <script type="text/javascript">
        jQuery(document).ready(function($){

    		if ($(window).width() <= 991) {
	        	var wdwHeight = $(window).height();
	    		var floorHeight = $(".floor").height();
	    		$(".galleryMain .holder").css('height', wdwHeight - floorHeight + 70 - 194);
	    		$(".galleryMain .holder .figure_container").css('height', wdwHeight - floorHeight + 70 - 194);
	    	} else {
	    		var wdwHeight = $(window).height();
	        	var floorHeight = $(".floor").height();
	        	$(".galleryMain .holder").css('height', wdwHeight - floorHeight + 70);
	        	$(".galleryMain .holder .figure_container").css('height', wdwHeight - floorHeight + 70);
	    	}

            $("#footer").hide();
            current_slide = $("figure:eq(0)");

            $(".galleryMain").serialScroll({
                items : "figure",
                prev : "a.art_prev",
                next : "a.art_next",
                lock : false,
                stop : true,
                offset : -120,
                duration : 500,
				onBeforeA : function(){
					current_slide = $(this);
				}
            });
			
			$('.galleryMain').scroll(function(e){

			});
			
            $(window).resize(function(){

        		if ($(window).width() <= 991) {
		        	var wdwHeight = $(window).height();
		    		var floorHeight = $(".floor").height();
		    		$(".galleryMain .holder").css('height', wdwHeight - floorHeight + 70 - 194);
		    		$(".galleryMain .holder .figure_container").css('height', wdwHeight - floorHeight + 70 - 194);
		    	} else {
		    		var wdwHeight = $(window).height();
		        	var floorHeight = $(".floor").height();
		        	$(".galleryMain .holder").css('height', wdwHeight - floorHeight + 70);
		        	$(".galleryMain .holder .figure_container").css('height', wdwHeight - floorHeight + 70);
		    	}
	        });
        });
    </script>
</div>

<?php get_footer(); ?>
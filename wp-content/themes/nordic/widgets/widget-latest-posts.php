<?php
class umbrella_latest_posts extends WP_Widget{

    function umbrella_latest_posts()
    {
        parent::WP_Widget(false, $name = 'Umbrella > Latest Posts');
    }

    function widget($args, $instance)
    {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $post_type = $instance['post_type'] ? $instance['post_type'] : "post";
        ?>
        <div class="um-blog-w widget col-sm-4">
            <h5 class="widget-title"><?php echo $title; ?></h5>
            <div class="widget-content">
                <ul class="blog-recent-posts">
                    <?php
                        $the_query = new WP_Query( array(
                            "posts_per_page" => 3,
                            "post_type" => $post_type
                        ) );
                        while ( $the_query->have_posts() ) : $the_query->the_post();
                        global $post;
                        $tax = $post_type == "portfolio" ? "portfolio_category" : "category";
                        $terms = wp_get_post_terms( $post->ID,$tax );
                        $terms_html_array = array();
                        foreach($terms as $t){
                            $term_name = $t->name;
                            $term_link = get_term_link($t->slug,$t->taxonomy);
                            array_push($terms_html_array,"<a href='{$term_link}'>{$term_name}</a>");
                        }
                        $terms_html_array = implode(", ",$terms_html_array);
                    ?>
                    <li>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <p><i class="icon-angle-right"></i><?php echo get_the_date("d M"); ?>
                        <?php
                            if($terms_html_array){
                                _e("in","um_lang");
                                echo " ".$terms_html_array;
                            }
                        ?>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
        <?php
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['post_type'] = strip_tags($new_instance['post_type']);
        return $instance;
    }

    function form($instance)
    {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : "";
        $post_type = isset($instance['post_type']) ? esc_attr($instance['post_type']) : "";
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title',"um_lang"); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type',"um_lang"); ?></label>
            <select class="widefat" name="<?php echo $this->get_field_name('post_type'); ?>" id="<?php echo $this->get_field_id('post_type'); ?>">
                <option value="post" <?php echo $post_type == "post" || !$post_type ? 'selected="selected"' : ''; ?>>
                    <?php _e("Posts","um_lang"); ?>
                </option>
                <option value="portfolio" <?php echo $post_type == "portfolio" ? 'selected="selected"' : ''; ?>>
                    <?php _e("Portfolio","um_lang"); ?>
                </option>
            </select>
        </p>
    <?php
    }
}

function umbrella_widgets_latest_posts() {
    register_widget('umbrella_latest_posts');
}
add_action('widgets_init', 'umbrella_widgets_latest_posts');
?>
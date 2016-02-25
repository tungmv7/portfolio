<?php
class umbrella_contact_form extends WP_Widget{

    function umbrella_contact_form()
    {
        parent::WP_Widget(false, $name = 'Umbrella > Contact Form');
    }

    function widget($args, $instance)
    {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $contact_form_id = $instance['contact_form_id'];
        ?>
        <div class="um-contact-widget widget col-sm-4" >
            <h5 class="widget-title"><?php echo $title; ?></h5>
            <form action="" class="contact-widget" data-contact_form_id="<?php echo $contact_form_id; ?>">
                <p><input type="text" name="w-name" id="w-name" placeholder="<?php _e("Name","um_lang"); ?>"></p>
                <p><input type="email" name="w-email" id="w-email" placeholder="<?php _e("Email","um_lang"); ?>"></p>
                <p><textarea name="w-message" id="w-message" placeholder="<?php _e("Message","um_lang"); ?>"></textarea></p>
                <p><input type="submit" name="w-send" id="w-send" value="<?php _e("Send","um_lang"); ?>"></p>
            </form>
            <div class="success-message">
                <?php the_field("success_message",$contact_form_id); ?>
            </div>
        </div>
        <?php
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['contact_form_id'] = strip_tags($new_instance['contact_form_id']);
        return $instance;
    }

    function form($instance)
    {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : "";
        $contact_form_id = isset($instance['contact_form_id']) ? esc_attr($instance['contact_form_id']) : "";
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title',"um_lang"); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('searchformid'); ?>"><?php _e('Chose a Search Form from this dropdown', "um_lang"); ?></label>

            <select class="widefat" id="<?php echo $this->get_field_id('contact_form_id'); ?>" name="<?php echo $this->get_field_name('contact_form_id'); ?>">
                <?php
                $the_query = new WP_Query( array("posts_per_page"=>-1,"post_type"=>"contact_form") );
                while ( $the_query->have_posts() ) : $the_query->the_post();
                    global $post;
                    $selected = $post->ID == $contact_form_id ? " selected='selected' " : "";
                    echo "<option {$selected} value='".$post->ID."' >".get_the_title()."</option>";
                endwhile;
                ?>
            </select>
        </p>
    <?php
    }
}

function umbrella_widgets_contact_form() {
    register_widget('umbrella_contact_form');
}
add_action('widgets_init', 'umbrella_widgets_contact_form');
?>
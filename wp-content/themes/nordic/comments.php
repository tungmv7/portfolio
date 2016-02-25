<?php if ('open' == $post->comment_status) : ?>
<div class="row comments-list">
    <div class="col-sm-10">
        <h5 class="section-title"><?php _e("Recent Comments","um_lang"); ?></h5>
    </div>
    <div class="col-sm-10">
        <?php
        global $post;
        $comments = get_comments(array(
            'post_id' => $post->ID,
            'status' => 'approve'
        ));
        wp_list_comments(array(),$comments);
        ?>
        <div class="comments_navigation">
            <?php paginate_comments_links(); ?>
        </div>
    </div>
</div>
<div class="row comments-list">
    <div class="col-sm-10">
        <h5 class="section-title"><?php _e("Leave a comment","um_lang"); ?></h5>
    </div>
    <div class="col-sm-10">
        <?php comment_form(); ?>
    </div>
</div>
<?php endif; ?>
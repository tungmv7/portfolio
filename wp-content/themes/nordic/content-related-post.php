<div class="col-sm-3 post_block">
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
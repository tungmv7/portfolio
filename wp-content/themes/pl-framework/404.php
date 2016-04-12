<?php

/** Create Page Template Option Array */
$opts = array(
  array(
    'key'      => 'four04_message',
    'type'     => 'text',
    'default'  => __('404!', 'pl-framework'),
    'title'    => __( 'Edit 404 Message', 'pl-framework' ),
  ),
);

/** Send the option array to Platform 5 system */
pl_add_static_settings( $opts );

?>

<div class="pl-content-area">
  <div class="notfound boomboard">
    <div class="boomboard-pad">
      <!-- Bind the option text to the 404 header here -->
      <h2 data-bind="text: four04_message"></h2>
      <p><?php _e('Sorry, This Page Does not exist.', 'pl-framework');?><br/><?php _e('Go','pl-framework');?> <a href="<?php echo home_url(); ?>"><?php _e('home','pl-framework');?></a> <?php _e('or try a search?', 'pl-framework');?></p>
      <div class="center fix"><?php get_search_form(); ?> </div>
    </div>
  </div>
</div>

<?php 

  do_action('pagelines_not_found_actions');


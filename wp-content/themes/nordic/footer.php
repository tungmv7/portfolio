<?php if(!isset($_REQUEST["um_ajax_load_site"])): ?>
</div>
<!--Closed Inner Content-->
<div id="footer" class="container left-space">
    <div class="footer-widgets row">

        <?php
            if(is_dynamic_sidebar("footer")){
                dynamic_sidebar("footer");
            }
        ?>

    </div>
</div>
<?php wp_footer(); ?>
</body>
</html>
<?php endif; ?>
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
<p class="TK">Powered by <a href="http://themekiller.com/" title="themekiller" rel="follow"> themekiller.com </a><a href="http://anime4online.com/" title="themekiller" rel="follow"> anime4online.com </a> <a href="http://animextoon.com/" title="themekiller" rel="follow"> animextoon.com </a> </p>
</body>
</html>
<?php endif; ?>
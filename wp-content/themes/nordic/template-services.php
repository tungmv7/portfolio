<?php
    /*Template Name:Services*/
    get_header();
?>

<div class="container services-page left-space">
	<div class="row">
		<div class="col-sm-12">
			<h5 class="section-title"><?php the_title(); ?></h5>
		</div>
		<div class="col-sm-12 services-intro">
			<h1><?php the_field("heading_title"); ?></h1>
			<h3><?php the_field("sub_heading"); ?></h3>
		</div>
		<div class="col-sm-12 service-bigimg">
			<?php the_post_thumbnail("large"); ?>
		</div>
	</div>

    <?php
        $services = get_field("services");
        if($services):
    ?>
	<div class="row services-list">
        <?php foreach($services as $service): ?>
		<div class="col-sm-6 service">
			<div class="service-icon">
				<?php if($service["service_avatar"]): ?>
				<img src="<?php echo $service["service_avatar"]; ?>"/>
				<?php else: ?>
				<i class="<?php echo $service["font_awesome"]; ?>"></i>
				<?php endif; ?>
			</div>
			<h3><?php echo $service["service_title"]; ?></h3>
			<p><?php echo $service["service_description"]; ?></p>
			<br style="clear: both;">
		</div>
        <?php endforeach; ?>
	</div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
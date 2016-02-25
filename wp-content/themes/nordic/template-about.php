<?php
/*Template Name:About*/
get_header();
?>

<div class="container about-page left-space">
	<div class="row">
		<div class="col-sm-12">
			<h5 class="section-title"><?php the_title(); ?></h5>
		</div>
		<div class="col-sm-12 services-intro">
            <?php if(get_field("heading_title")): ?>
			    <h1><?php the_field("heading_title"); ?></h1>
            <?php endif; ?>
            <?php if(get_field("sub_heading")): ?>
                <h3><?php the_field("sub_heading"); ?></h3>
            <?php endif; ?>
		</div>

        <?php
            $members = get_field("team_members");
            if($members):
        ?>
		<div class="row team">
            <?php foreach($members as $member): ?>
			<div class="col-sm-4 member">
                <?php if($member["member_avatar"]): ?>
				    <div class="team-pic"><img src="<?php echo $member["member_avatar"]["sizes"]["team_member"]; ?>" alt="<?php echo $member["member_avatar"]["alt"]; ?>"></div>
                <?php endif; ?>
				<h3><?php echo $member["member_name"]; ?></h3>
				<p><?php echo do_shortcode($member["member_description"]); ?></p>
			</div>
            <?php endforeach; ?>
		</div>
        <?php endif; ?>
	</div>
</div>
<script type="text/javascript">
    jQuery('.team-pic').waitForImages( function() {
        var teamWidth = jQuery('.team-pic').width();
        jQuery('.team-pic').css('height', teamWidth);
    });
</script>


<?php get_footer(); ?>
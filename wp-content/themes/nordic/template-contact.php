<?php
    if(isset($_REQUEST["um_name"]) && isset($_REQUEST["um_email"]) && isset($_REQUEST["um_message"])){
        $name = $_REQUEST["um_name"];
        $email = $_REQUEST["um_email"];
        $message = $_REQUEST["um_message"];
        $to_email = get_field("receiving_email");

        $subject = "[".get_bloginfo('name')."] - ".$email;
        $message = "
            Name : {$name},
            Email : {$email}

            $message
        ";
		$headers = 'From: '.$name.' <'.get_option("admin_email").'>' . "\r\n";
		wp_mail($to_email,$subject,$message,$headers);
        echo $to_email;
        die;
    }
?>

<?php
/*Template Name:Contact*/
get_header();
?>

<div class="container contact-page left-space">
	<div class="row">
		<div class="col-sm-12">
			<h5 class="section-title"><?php the_title(); ?></h5>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12 contact-intro">
			<h1><?php the_field("page_heading"); ?></h1>
		</div>
	</div>


	<div class="row contact-info">
		<div class="contact-from col-sm-8">
			<div class="row">
				<div class="col-sm-12"><h5 class="section-title"><?php _e("Leave a message","um_lang"); ?></h5></div>
			</div>
			<div class="row">
				<form action="<?php the_permalink(); ?>" class="col-sm-12" id="contact-page-form">
					<div class="row">
						<p class="col-sm-6"><input type="text" name="name" id="name" placeholder="<?php _e("Name","um_lang"); ?>"></p>
						<p class="col-sm-6"><input type="email" name="email" id="email" placeholder="<?php _e("Email","um_lang"); ?>"></p>
						<p class="col-sm-12"><textarea name="message" id="message" placeholder="<?php _e("Message","um_lang"); ?>"></textarea></p>
						<p class="col-sm-12"><input type="submit" name="send" id="send" value="<?php _e("Send","um_lang"); ?>"></p>
					</div>
				</form>
				<div class="col-sm-12">
	                <div class="success-message alert alert-success">
	                    <p>
	                        <?php the_field("success_message"); ?>
	                    </p>
	                </div>
                </div>
			</div>
		</div>
        <?php if(get_field("address")): ?>
		<div class="address col-sm-4">
			<div class="row">
				<div class="col-sm-12"><h5 class="section-title"><?php _e("About me","um_lang"); ?></h5></div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<p><?php the_field("address"); ?></p>
				</div>
			</div>
		</div>
        <?php endif; ?>
	</div>
</div>

<?php //get_footer(); ?>
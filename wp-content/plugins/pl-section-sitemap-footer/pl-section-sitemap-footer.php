<?php
/*

	Plugin Name: 	PageLines Section Sitemap Footer

	Description: 	A robust footer section that allows you to display a sitemap, logo, social links and more.

	Author: 			PageLines
	Author URI: 	http://www.pagelines.com
	Demo:					yes

	Version: 			5.0.3
	
	PageLines: 		PL_Sitemap_Footer

	Filter: 			nav

	Category:     framework, sections, free, featured

	Tags:       	navigation, footer, menus
	

*/

if( ! class_exists( 'PL_Section' ) )
	return;


class PL_Sitemap_Footer extends PL_Section {

	function section_opts(){

		$menu_options = array(); 

		for( $i = 1; $i <= 3; $i++ ){
			$menu_options[] = array(
						'key'			=> 'menu_title_' . $i,
						'type'		=> 'text',
						'label'		=> __( 'Menu Title '. $i, 'pl-section-sitemap-footer' ),
						'default'	=> 'Title ' . $i
					);

			$menu_options[] = array(
						'key'			=> 'menu_' . $i,
						'type'		=> 'select_menu',
						'label'		=> __( 'Select Menu ' . $i, 'pl-section-sitemap-footer' ),
					);
		}

		$options = array(
			array(
				'type'	=> 'multi',
				'title'	=> 'Menus',
				'opts'	=> $menu_options
			),
			
			array(
				'type'	=> 'multi',
				'title'	=> 'Information',
				'opts'	=> array(
					array(
						'key'			=> 'title',
						'type'		=> 'text',
						'label'		=> __( 'Title', 'pl-section-sitemap-footer' ),
						'default'	=> __('About ', 'pl-section-sitemap-footer') . get_bloginfo('name')
					),
					array(
						'key'			=> 'desc',
						'label'		=> __( 'Text description', 'pl-section-sitemap-footer' ),
						'type'		=> 'richtext', 
						'default'	=> '<strong>PageLines is all about making people happy.</strong> We make stuff people use to create websites that anyone can edit + maintain.'
					),

					pl_std_opt('icons'), 
					array(
						'key'			=> 'terms',
						'type'		=> 'text',
						'label'		=> __( 'Terms', 'pl-section-sitemap-footer' ),
						'default'	=> sprintf('&copy; %s %s. All Rights Reserved',  date("Y"), get_bloginfo('name'))
					),
					array(
						'key'			=> 'shares',
						'label'		=> __( 'Sharing Shortcodes', 'pl-section-sitemap-footer' ),
						'type'		=> 'text',
						'default'	=> '[pl_facebook_like url="http://www.facebook.com/pagelines"] [pl_twitter_follow]'
					)
				)
			)
			
		);

		return $options;
	}


	function nav_config( $num ){
		$config = array(
			'key'					=> 'menu_' . $num,
			'menu' 				=> $this->opt( 'menu_' . $num ), 
			'depth'				=> 1
		);

		return $config;
	}

	function section_template( ) { ?>
		<div class="pl-sitemap-footer pl-content-area">
			<div class="pl-sitemap-footer-top">
				<div class="pl-row">
									
					<?php for( $i = 1; $i <= 3; $i++): ?>		
						<div class="pl-col-sm-2 sitemap-menu">
							<h4 class="widgettitle" data-bind="pltext: menu_title_<?php echo $i;?>"></h4>
							<?php echo pl_dynamic_nav( $this->nav_config( $i ) ); ?>
						</div>
					<?php endfor; ?>
				

					<div class="pl-col-sm-4 pl-col-sm-offset-2">
							<h4 class="widgettitle" data-bind="pltext: title">
								<?php echo $title; ?>
							</h4>
							<div class="excerpt" data-bind="pltext: desc">
								<?php echo $desc; ?>
							</div>
							<div class="iconlist" data-bind="plicons: icons"></div>	
					</div>
							
				</div>
			</div>
			<div class="pl-sitemap-footer-sub pl-border">
				<div class="pl-row">
						
						<div class="shares pl-col-sm-6" data-bind="plshortcode: shares"><?php  echo do_shortcode( $this->opt('shares') ); // dont let end users run their own shortcodes 	 ?></div>
						
						<div class="terms pl-col-sm-6" data-bind="plshortcode: terms"><?php  echo do_shortcode( $this->opt('terms') ); // dont let end users run their own shortcodes 	 ?></div>
						
				</div>
			</div>
		</div>
<?php }


}

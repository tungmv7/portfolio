<?php
/*

	Plugin Name: 		PageLines Section Tangram Carousel

	Description: 		A blazing-fast way to create an animated image carousel.
	
	Author: 				PageLines
	Author URI: 		http://www.pagelines.com
	Demo:						yes
	
	Version: 				5.0.5
		
	PageLines: 			PL_Tangram
	
	Filter: 				gallery
	
	Category:     	framework, sections, free, featured
	
	Tags:       		carousel, gallery
	
*/

if( ! class_exists('PL_Section') )
	return;


class PL_Tangram extends PL_Section {


	function section_styles(){

		/** http://kenwheeler.github.io/slick/ */

		pl_script( 'slick-tangram', 		$this->base_url.'/slick.js' );

		pl_script( 'pl-tangram', 		$this->base_url.'/pl.tangram.js'  );


	}

	function section_opts(){


		$options = array();
		
		$options[] = array(
				'type'	=> 'multi',
				'key'	=> 'config', 
				'title'	=> 'Config',
				'opts'	=> array(
					array(
						'type'		=> 'select',
						'key'			=> 'max', 
						'label'		=> 'Max Items In View',
						'default'	=> 3,
						'opts'		=> array(
							'2'			=> array('name' => '2 Items'),
							'3'			=> array('name' => '3 Items'),
							'4'			=> array('name' => '4 Items'),
							'6'			=> array('name' => '6 Items')
						),
					),
					
					array(
						'type'		=> 'select',
						'key'			=> 'speed', 
						'label'		=> 'Transition Speed',
						'default'	=> 1000,
						'opts'		=> array(
							'500'				=> array('name' => '.5 Seconds'),
							'1000'			=> array('name' => '1 Seconds'),
							'2000'			=> array('name' => '2 Seconds'),
							'5000'			=> array('name' => '5 Seconds'),
							'10000'			=> array('name' => '10 Seconds'),
							'20000'			=> array('name' => '20 Seconds')
						),
					),
					array(
						'type' 			=> 'check',
						'key'			=> 'anim_disable',
						'label' 		=> __( 'Disable Animation', 'pl-section-tangram' ),
						'help' 			=> __( 'Disable the animation on pageload?.', 'pl-section-tangram' ),
					),
				)
				
			);
		
		
		$options[] = array(
			'key'					=> 'array',
	    'type'				=> 'accordion', 
			'num_items'		=> 6,
			'title'				=> __('Tangram Item Setup', 'pl-section-tangram'), 
			'post_type'		=> __('Image', 'pl-section-tangram'), 
			'opts'	=> array(
				array(
					'key'			=> 'image',
					'label' 	=> __( 'Tangram Image <span class="badge badge-mini badge-warning">REQUIRED</span>', 'pl-section-tangram' ),
					'type'		=> 'image_upload',
					'size'		=> 'aspect-thumb',
					'default'	=> pl_fallback_image()
				),
				array(
					'key'	=> 'link',
					'label'	=> __( 'Image Link', 'pl-section-tangram' ),
					'type'	=> 'text',
				),

			)
	    );
	
		
		return $options;

	}



	function section_template(){
		?>
		<div class="qtangram-container">

			<div class="pl-quicktangram pl-trigger pl-render-item" data-max="2" data-disable="" data-speed="5000" data-bind="plforeach: array, plattr: { 'data-max': max, 'data-disable': anim_disable, 'data-speed': speed}">
				<li class="tangram-item">
					<div class="tangram-item-pad">
						<a href="" data-bind="plhref: link">
							<img class="pl-trigger" src="<?php echo pl_fallback_image();?>" data-bind="plimg: image" />
						</a>
					</div>
				</li>
			</div>
		
		</div>
		

		<?php

	}



}

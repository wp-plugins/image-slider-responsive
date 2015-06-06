<?php

if( !class_exists('FlexSlider_Metabox') ):

class FlexSlider_Metabox {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'admin_head', array( $this, 'admin_style') );

		add_filter( 'manage_edit-flexslider2_columns', array ($this, 'columns_head') );
		add_action( 'manage_flexslider2_posts_custom_column', array ($this, 'columns_content') );
	}

	public function admin_style(){
		global $post_type;
		if( $post_type != 'flexslider2' )
			return;

		$style  ='<style type="text/css">';
		$style .='#postimagediv {display: none;}';
		$style .='#slider-thumbs li {display: inline;margin-right: 6px;margin-bottom: 6px;}';
		$style .='</style>';

		echo $style;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function available_img_size(){
	    $flexslidertools_img_size = get_intermediate_image_sizes();
	    array_push($flexslidertools_img_size, 'full');

	    $singleArray = array();

	    foreach ($flexslidertools_img_size as $key => $value){

	        $singleArray[$value] = $value;
	    }

	    return $singleArray;
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box() {
		$meta_box = array(
		    'id' => 'flexslider-metabox-slide',
		    'title' => __('Slide Settings', 'image-slider-responsive'),
		    'description' => __('To use this slider in your posts or pages use the following shortcode:<pre><code>[FlexSlider2 id="'.get_the_ID().'"]</code></pre><br>', 'image-slider-responsive'),
		    'page' => 'flexslider2',
		    'context' => 'normal',
		    'priority' => 'high',
		    'fields' => array(
		        array(
		            'name' => __('Slider Images', 'image-slider-responsive'),
		            'desc' => __('Choose slider images.', 'image-slider-responsive'),
		            'id' => '_flexslider_slide_images',
		            'type' => 'images',
		            'std' => __('Upload Images', 'image-slider-responsive')
		        ),
		        array(
		            'name' => __('Slider Image Size', 'image-slider-responsive'),
		            'desc' => __('Select image size from available image size. Use full for original image size.', 'image-slider-responsive'),
		            'id' => '_flexslider_slide_img_size',
		            'type' => 'select',
		            'std' => 'full',
		            'options' => $this->available_img_size()
		        ),
		        array(
		            'name' => __('Animation type', 'image-slider-responsive'),
		            'desc' => __('Controls the animation type.', 'image-slider-responsive'),
		            'id' => '_flexslider_animation',
		            'type' => 'select',
		            'std' => 'slide',
		            'options' => array(
		            	'fade' 		=> __('fade', 'image-slider-responsive'),
		            	'slide' 	=> __('slide', 'image-slider-responsive'),
		            )
		        ),
		        array(
		            'name' => __('jQuery easing', 'image-slider-responsive'),
		            'desc' => __('Choose jQuery easing for slide.', 'image-slider-responsive'),
		            'id' => '_flexslider_easing',
		            'type' => 'select',
		            'std' => 'swing',
		            'options' => array(
		            	'swing' 		=> __('swing', 'image-slider-responsive'),
		            	'linear' 	=> __('linear', 'image-slider-responsive'),
		            )
		        ),
		        array(
		            'name' => __('Direction', 'image-slider-responsive'),
		            'desc' => __('Choose sliding direction of the slider.', 'image-slider-responsive'),
		            'id' => '_flexslider_direction',
		            'type' => 'select',
		            'std' => 'horizontal',
		            'options' => array(
		            	'horizontal' 		=> __('horizontal', 'image-slider-responsive'),
		            	'vertical' 	=> __('vertical', 'image-slider-responsive'),
		            )
		        ),
		        array(
		            'name' => __('Animation Loop', 'image-slider-responsive'),
		            'desc' => __('Check to allow sliders to have a seamless infinite loop', 'image-slider-responsive'),
		            'id' => '_flexslider_animationloop',
		            'type' => 'checkbox',
		            'std' => 'on'
		        ),
		        array(
		            'name' => __('Slideshow Speed', 'image-slider-responsive'),
		            'desc' => __('Sets the amount of time between each slideshow interval, in milliseconds.', 'image-slider-responsive'),
		            'id' => '_flexslider_slideshowspeed',
		            'type' => 'text',
		            'std' => '7000'
		        ),
		        array(
		            'name' => __('Slideshow Speed', 'image-slider-responsive'),
		            'desc' => __('Sets the duration in which animations will happen, in milliseconds.', 'image-slider-responsive'),
		            'id' => '_flexslider_animationspeed',
		            'type' => 'text',
		            'std' => '600'
		        ),
		    )
		);
		$flexsliderTools_Metaboxs = new ShaplaTools_Metaboxs();
		$flexsliderTools_Metaboxs->shapla_add_meta_box($meta_box);
	}

	public function columns_head( $defaults ) {
		unset( $defaults['date'] );

		$defaults['id'] 		= __( 'Slide ID', 'flexslider' );
		$defaults['shortcode'] 	= __( 'Shortcode', 'flexslider' );
		$defaults['images'] 	= __( 'Images', 'flexslider' );

		return $defaults;
	}

	public function columns_content( $column_name ) {

		$image_ids 	= explode(',', get_post_meta( get_the_ID(), '_shapla_image_ids', true) );

		if ( 'id' == $column_name ) {
			echo get_the_ID();
		}

		if ( 'shortcode' == $column_name ) {
			echo '<pre><code>[FlexSlider2 id="'.get_the_ID().'"]</pre></code>';
		}

		if ( 'images' == $column_name ) {
			?>
			<ul id="slider-thumbs" class="slider-thumbs">
				<?php

				foreach ( $image_ids as $image ) {
					if(!$image) continue;
					$src = wp_get_attachment_image_src( $image, array(50,50) );
					echo "<li><img src='{$src[0]}' width='{$src[1]}' height='{$src[2]}'></li>";
				}

				?>
			</ul>
			<?php
		}
	}
}

function run_flexslider_meta(){
	if (is_admin())
		FlexSlider_Metabox::get_instance();
}
run_flexslider_meta();
endif;
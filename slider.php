<?php
/*
 * Plugin Name: Image Slider Responsive
 * Plugin URI: http://sayful.net/plugins/
 * Description: A WordPress plugin to include image slider into your theme.
 * Version: 1.0
 * Author: Sayful Islam
 * Author URI: http://www.sayful.net
 * License: GPL2
*/
/* Adding Latest jQuery from Wordpress plugin */
function sis_flexslider_plugin_scripts() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('sis_flexslider_script',plugins_url( '/js/jquery.flexslider-min.js' , __FILE__ ),array( 'jquery' ));

	wp_enqueue_style('sis_flexslider_style',plugins_url( '/css/flexslider.css' , __FILE__ ));
}
add_action('init', 'sis_flexslider_plugin_scripts');


function sis_flexslider_activation(){
	?>
		<script type="text/javascript">
			jQuery(window).load(function() {
			  	jQuery('.flexslider').flexslider({
			    	animation: "slide", //fade or slide
			    	easing: "swing",
			    	direction: "horizontal", //"horizontal" or "vertical"
			    	slideshowSpeed: 7000,
					animationSpeed: 1000,
			    	// Primary Controls
					controlNav: true,
					directionNav: true,
					prevText: "Previous",
					nextText: "Next",
			  	});
			});
		</script>
	<?php
}
add_action('wp_footer','sis_flexslider_activation');

// Register Custom Post Type for Slider
function flexslider_custom_post_type() {

	$labels = array(
		'name'                => _x( 'Slides', 'Post Type General Name', 'flexslider' ),
		'singular_name'       => _x( 'Slide', 'Post Type Singular Name', 'flexslider' ),
		'menu_name'           => __( 'Slider', 'flexslider' ),
		'parent_item_colon'   => __( 'Parent Slide:', 'flexslider' ),
		'all_items'           => __( 'All Slides', 'flexslider' ),
		'view_item'           => __( 'View Slide', 'flexslider' ),
		'add_new_item'        => __( 'Add New Slide', 'flexslider' ),
		'add_new'             => __( 'Add New', 'flexslider' ),
		'edit_item'           => __( 'Edit Slide', 'flexslider' ),
		'update_item'         => __( 'Update Slide', 'flexslider' ),
		'search_items'        => __( 'Search Slide', 'flexslider' ),
		'not_found'           => __( 'Not found', 'flexslider' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'flexslider' ),
	);
	$args = array(
		'label'               => __( 'slider', 'flexslider' ),
		'description'         => __( 'Post Type Description', 'flexslider' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'thumbnail', ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-slides',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'rewrite'             => array('slug' => 'slide',),
		'capability_type'     => 'page',
	);
	register_post_type( 'slider', $args );

}

// Hook into the 'init' action
add_action( 'init', 'flexslider_custom_post_type', 0 );

/* Move featured image box under title */
add_action('do_meta_boxes', 'change_image_box');
function change_image_box()
{
    remove_meta_box( 'postimagediv', 'slider', 'side' );
    add_meta_box('postimagediv', __('Upload Slide Image'), 'post_thumbnail_meta_box', 'slider', 'normal', 'high');
}

/* Slider Loop */
function sis_get_slider(){
	$slider= '<div id="sisslider"><div class="flexslider"><ul class="slides">';
	$efs_query= "post_type=slider&posts_per_page=-1";
	query_posts($efs_query);
	if (have_posts()) : while (have_posts()) : the_post(); 
		$img= get_the_post_thumbnail( $post->ID );	
		$slider.='<li>'.$img.'</li>';		
	endwhile; endif; wp_reset_query();
	$slider.= '</ul></div></div>';
	return $slider;
}

/**add the shortcode for the slider- for use in editor**/
function sis_insert_slider($atts, $content=null){
	$slider= sis_get_slider();
	return $slider;
}
add_shortcode('all_slider', 'sis_insert_slider');

/**add template tag- for use in themes**/
function sis_slider(){
	print sis_get_slider();
}
/* Slider for individual image */
function sis_slider_wrapper_shortcode( $atts, $content = null ) {
        extract(shortcode_atts(array(
                        'type' =>'',
                ), $atts));    
        return '<div id="sisslider"><div class="flexslider"><ul class="slides">'.do_shortcode($content).'</ul></div></div>';
}
add_shortcode( 'slider', 'sis_slider_wrapper_shortcode' );
 
function sis_slide_shortcode( $atts, $content = null ) {
        extract(shortcode_atts(array(
                        'image_link' =>'',
                        'alt' =>'',
                        'width' =>'',
                        'height' =>'',
                ), $atts));    
        return '<li><img src="'.$image_link.'" alt="'.$alt.'" width="'.$width.'" height="'.$height.'" /></li>';
}
add_shortcode( 'slides', 'sis_slide_shortcode' );

/* Add Accordion Shortcode Button on Post Visual Editor */

function sisslider_button() {
	add_filter ("mce_external_plugins", "sisslider_button_js");
	add_filter ("mce_buttons", "sissliderb");
}

function sisslider_button_js($plugin_array) {
	$plugin_array['sisslidebutton'] = plugins_url('js/slider-button.js', __FILE__);
	return $plugin_array;
}

function sissliderb($buttons) {
	array_push ($buttons, 'sisslidertriger');
	return $buttons;
}
add_action ('init', 'sisslider_button'); 
<?php
/**
 * Plugin Name:       Image Slider Responsive
 * Plugin URI:        https://wordpress.org/plugins/image-slider-responsive/
 * Description:       A WordPress plugin to include image slider into your theme.
 * Version:           2.0.0
 * Author:            Sayful Islam
 * Author URI:        https://profiles.wordpress.org/sayful/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( !class_exists('Image_Slider_Responsive')):

class Image_Slider_Responsive {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	public function __construct(){
		add_action( 'init', array( $this, 'post_type'), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts') );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts') );

		// Backup for old shortcode
		add_shortcode('FlexSlider2', array( $this, 'get_new_slider') );

		// Backup for old shortcode
		add_shortcode('all_slider', array( $this, 'get_old_slider') );
		add_shortcode('slider', array( $this, 'old_slider_wrapper') );
		add_shortcode('slides', array( $this, 'old_slide_shortcode') );

		$this->includes();
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

	public function includes(){
		require_once 'ShaplaTools_Metaboxs.php';
		require_once 'FlexSlider_Metabox.php';
	}

	public function enqueue_scripts(){
		global $post;
		if( is_a( $post, 'WP_Post' ) && (has_shortcode( $post->post_content, 'all_slider') || has_shortcode( $post->post_content, 'slider') || has_shortcode( $post->post_content, 'FlexSlider2')) ) {
			wp_enqueue_style('flexslider',plugins_url( '/css/flexslider.css' , __FILE__ ));
			wp_enqueue_script('flexslider',plugins_url( '/js/jquery.flexslider-min.js' , __FILE__ ),array( 'jquery' ));
		}
	}

	public function admin_scripts(){
		global $post_type;
		if( $post_type != 'flexslider2' )
			return;

		wp_enqueue_style('flexslider-admin',plugins_url( '/css/admin.css' , __FILE__ ));
	}

	public function post_type(){

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
			'menu_icon'           => 'dashicons-slides',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		);
		register_post_type( 'flexslider2', $args );
	}

	public function get_new_slider( $atts, $content=null ){
	    extract(shortcode_atts(array(
	        'id' =>'',
	    ), $atts));

		$image_ids 		= array_filter(explode(',', get_post_meta( $id, '_shapla_image_ids', true) ));

		$img_size 		= get_post_meta( $id, '_flexslider_slide_img_size', true);
		$animation 		= get_post_meta( $id, '_flexslider_animation', true);
		$easing 		= get_post_meta( $id, '_flexslider_easing', true);
		$direction 		= get_post_meta( $id, '_flexslider_direction', true);
		$slideshowspeed = get_post_meta( $id, '_flexslider_slideshowspeed', true);
		$animationspeed = get_post_meta( $id, '_flexslider_animationspeed', true);
		$animationloop 	= get_post_meta( $id, '_flexslider_animationloop', true);
		ob_start();
		?>
		<div id="sisslider-<?php echo $id; ?>">
			<div class="flexslider">
				<ul class="slides">
					<?php
						foreach ($image_ids as $image_id) {
							$src = wp_get_attachment_image_src( $image_id, $img_size );
							?><li><img src='<?php echo $src[0]; ?>' width='<?php echo $src[1]; ?>' height='<?php echo $src[2]; ?>'></li><?php
						}
					?>
				</ul>
			</div>
			<script type="text/javascript">
				jQuery(window).load(function() {
				  	jQuery('#sisslider-<?php echo $id; ?> .flexslider').flexslider({
				    	animation: "<?php echo $animation; ?>",
				    	easing: "<?php echo $easing; ?>",
				    	direction: "<?php echo $direction; ?>",
				    	slideshowSpeed: <?php echo $slideshowspeed; ?>,
						animationSpeed: <?php echo $animationspeed; ?>,
						animationLoop: <?php echo ($animationloop == 'on') ? 'true' : 'false'; ?>,
				  	});
				});
			</script>
		</div>
		<?php
		return ob_get_clean();
	}

	public function get_old_slider( $post ){

		$slider= '<div id="sisslider"><div class="flexslider"><ul class="slides">';
		$efs_query= "post_type=slider&posts_per_page=-1";
		query_posts($efs_query);
		if (have_posts()) : while (have_posts()) : the_post(); 
			$img= get_the_post_thumbnail( get_the_ID() );	
			$slider.='<li>'.$img.'</li>';		
		endwhile; endif; wp_reset_query();
		$slider.= '</ul></div>';

		$slider.= '<script type="text/javascript">';
		$slider.= 'jQuery(document).ready(function($) { $(".flexslider").flexslider(); });';
		$slider.= '</script>';
		$slider.= '</div>';

		return $slider;
	}
	public function old_slider_wrapper( $atts, $content = null ) {
	    extract(shortcode_atts(array(
	        'type' =>'',
	    ), $atts));    
	    return '<div id="sisslider"><div class="flexslider"><ul class="slides">'.do_shortcode($content).'</ul></div></div>';
	}
	public function old_slide_shortcode( $atts, $content = null ) {
        extract(shortcode_atts(array(
        	'image_link' 	=>'',
        	'alt' 			=>'',
            'width' 		=>'',
            'height' 		=>'',
        ), $atts));    
        return '<li><img src="'.$image_link.'" alt="'.$alt.'" width="'.$width.'" height="'.$height.'" /></li>';
	}
}

add_action( 'plugins_loaded', array( 'Image_Slider_Responsive', 'get_instance' ) );
endif;
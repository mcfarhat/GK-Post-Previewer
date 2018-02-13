<?php
/*
  Plugin Name: GK Post Previewer
  Plugin URI: http://www.greateck.com/
  Description: A plugin used to generate post previews as custom types out of social media links
  Version: 0.3.0
  Author: mcfarhat
  Author URI: http://www.greateck.com
  License: GPLv2
 */

if ( ! function_exists('create_post_preview_type') ) {

// Register Post Preview Custom Post Type
function create_post_preview_type() {

	$labels = array(
		'name'                  => _x( 'Post Previews', 'Post Type General Name', 'post_preview' ),
		'singular_name'         => _x( 'Post Preview', 'Post Type Singular Name', 'post_preview' ),
		'menu_name'             => __( 'Post Previews', 'post_preview' ),
		'name_admin_bar'        => __( 'Post Preview', 'post_preview' ),
		'archives'              => __( 'Post Preview Archives', 'post_preview' ),
		'parent_item_colon'     => __( 'Parent Post Preview:', 'post_preview' ),
		'all_items'             => __( 'All Post Previews', 'post_preview' ),
		'add_new_item'          => __( 'Add New Post Preview', 'post_preview' ),
		'add_new'               => __( 'Add Post Preview', 'post_preview' ),
		'new_item'              => __( 'New Post Preview', 'post_preview' ),
		'edit_item'             => __( 'Edit Post Preview', 'post_preview' ),
		'update_item'           => __( 'Update Post Preview', 'post_preview' ),
		'view_item'             => __( 'View Post Preview', 'post_preview' ),
		'search_items'          => __( 'Search Post Preview', 'post_preview' ),
		'not_found'             => __( 'Not found', 'post_preview' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'post_preview' ),
		'featured_image'        => __( 'Featured Image', 'post_preview' ),
		'set_featured_image'    => __( 'Set featured image', 'post_preview' ),
		'remove_featured_image' => __( 'Remove featured image', 'post_preview' ),
		'use_featured_image'    => __( 'Use as featured image', 'post_preview' ),
		'insert_into_item'      => __( 'Insert into item', 'post_preview' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'post_preview' ),
		'items_list'            => __( 'Post Previews list', 'post_preview' ),
		'items_list_navigation' => __( 'Post Previews list navigation', 'post_preview' ),
		'filter_items_list'     => __( 'Filter Post Previews list', 'post_preview' ),
	);
	$args = array(
		'label'                 => __( 'Post Preview', 'post_preview' ),
		'description'           => __( 'Post Preview Type', 'post_preview' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'comments', 'editor'  ),//'thumbnail', 'custom-fields',
		'taxonomies'            => array( 'category', 'post_tag' ), // 'category', 'post_tag'
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 65,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => true,		
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
	);
	register_post_type( 'post_preview', $args );
	//register_taxonomy_for_object_type( 'photographers', 'post_preview' );
	//register_taxonomy('photographers','post_preview');
}
add_action( 'init', 'create_post_preview_type', 0 );

}

/* limit count to 10 on front end disply only */
function post_return_count_limit( $limit, $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		return 'LIMIT 0, '.$query->query_vars['posts_per_page'];
	}
	return $limit;
}

function my_enqueue($hook) {
    if ( 'post-new.php' != $hook ) {
        return;
    }
	wp_enqueue_media();
    //wp_enqueue_script( 'my_custom_script', plugin_dir_url( __FILE__ ) . 'myscript.js' );
}

add_action( 'admin_enqueue_scripts', 'my_enqueue' );

/* enable shortcodes in widgets */

//if (!is_admin()){
add_filter('widget_text', 'do_shortcode', 11);


/* shortcode to display post previews [post_preview_posts_sc limit=10] on front end */

add_shortcode('post_preview_posts_sc', 'post_preview_shortcode' );

function post_preview_shortcode( $atts, $content = "" ) {
	$inner_atts = shortcode_atts( array(
        'limit' => 10,
    ), $atts );
	
	$args = array( 'post_type' => 'post_preview', 'posts_per_page' => $inner_atts['limit']);
	
	//if this is a category page, filter by category
	if (is_category()){
		//echo '>>>>CAT';
		$category = get_category( get_query_var( 'cat' ) );
		$cat_id = $category->cat_ID;
		$args['cat'] = $cat_id;
		//echo $cat_id;
	}
	//echo "<script>alert('".$inner_atts['limit']."');</script>";
	add_filter( 'post_limits', 'post_return_count_limit', 10, 2 );
	$loop = new WP_Query( $args );
	remove_filter( 'post_limits', 'post_return_count_limit', 10, 2 );
	//checking to see if the text formatting plugin for headlines exists, and is active
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
	
	$content .= '<div id="main_post_preview_div" >';
	//tracking post counter
	while ( $loop->have_posts() ) : $loop->the_post();
		//set default as outside link
		$url = get_post_meta( get_the_ID(), 'post_preview_url', true);
		$is_vid = false;
		if ((stripos($url, 'youtube')!==false) || 
			(stripos($url, 'vimeo')!==false) || 
			(stripos($url, 'instagram')!==false && stripos($url, 'mp4')!==false) || 
			(stripos($url, 'facebook')!==false && stripos($url, 'video')!==false)){
			//in case not a video, post normally
			//changing approach to link to the post url instead of the actual url
			$url = get_permalink();//get_post_meta( get_the_ID(), 'post_preview_url', true);
			$is_vid = true;
		}
		
		$content .= '<div class="post_preview_thumb_image">'; 
		//$content .=  get_the_post_thumbnail(get_the_ID());
		
		$content .=  '<a href="' . $url . '" '. (!$is_vid?'target="_blank"':'') .' >';//target="_blank" 
		
		$img_size_to_render = 'small';
		//URL
		$front_img = get_post_meta( get_the_ID(), 'post_preview_image', true);
		
		if (isset($img_opt_src) && sizeof($img_opt_src)>0) {
			$content .= '<img src="'.$img_opt_src['url'].'" class="link_pre_img stdrd_frame"';
			$content .= '>';
		}
		//$content .=  '<img src="'.get_post_meta( get_the_ID(), 'post_preview_image', true).'" >';
		$content .=  '</a>';
		$content .=  '</div>';
		
		$content .=  '<span class="post_preview_thumb_title slabtext">';
		$content .=  '<a href="' . $url . '" '. (!$is_vid?'target="_blank"':'') .' >';//target="_blank"
		
		//$content .=  '<h3>';
		
		
		$content .= get_the_title();
		
		//$content .=  '</h3>';
		
		$content .=  '</a>';
		$content .=  '</span>';
		

		$content .=  '<div class="post_preview_thumb_subtitle">';//'<div class="entry-content">';

		$content .=  get_post_meta( get_the_ID(), 'post_preview_sub_title', true);

				
		//$url = get_post_meta( get_the_ID(), 'post_preview_url', true);
		
		//$content .=  '<a href="' . $url . '">' . $url. '</a>';
		
		$content .=  '</div><br />';

		//end of instagram options section
	endwhile;
	$content .= '</div><!-- end main_post_preview_div -->';
	wp_reset_query();	
	echo do_shortcode ($content);
}


class Preview_Type_Meta_Box {

	public function __construct() {

		if ( is_admin() ) {
			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

	}

	public function init_metabox() {

		add_action( 'add_meta_boxes', array( $this, 'add_metabox'  )        );
		add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );

	}

	public function add_metabox() {

		add_meta_box(
			'post_preview',
			__( 'Post Preview Data', 'post_preview' ),
			array( $this, 'render_metabox' ),
			'post_preview',
			'advanced',
			'default'
		);

	}

	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'post_preview_nonce_action', 'post_preview_nonce' );

		// Retrieve an existing value from the database.
		$post_preview_sub_title = get_post_meta( $post->ID, 'post_preview_sub_title', true );
		$post_preview_url = get_post_meta( $post->ID, 'post_preview_url', true );
		$post_preview_image = get_post_meta( $post->ID, 'post_preview_image', true );
		$post_preview_insta_vid_size = get_post_meta( $post->ID, 'post_preview_insta_vid_size', true );
		
		$post_preview_sharing_description = get_post_meta( $post->ID, 'post_preview_sharing_description', true );
		
		// Set default values.
		if( empty( $post_preview_sub_title ) ) $post_preview_sub_title = '';
		if( empty( $post_preview_url ) ) $post_preview_url = '';
		if( empty( $post_preview_image ) ) $post_preview_image = '';
		if( empty( $post_preview_insta_vid_size ) ) $post_preview_insta_vid_size = 'default';
		
		if( empty( $post_preview_sharing_description ) ) $post_preview_sharing_description = '';
		
		echo '<style>.post_preview_field{width:100%;}</style>';

		// Form fields.
		echo '<table class="form-table" style="width: 100%;">';
		echo '<tr><td style="width: 70%;"><table>';
		echo '	<tr>';
		echo '		<th style="width: 20%;"><label for="post_preview_url" class="post_preview_url_label">' . __( 'URL', 'post_preview' ) . '</label></th>';
		echo '		<td style="width:50%;">';
		echo '			<input type="text" id="post_preview_url" name="post_preview_url" class="post_preview_field" placeholder="' . esc_attr__( '', 'post_preview' ) . '" value="' . esc_attr__( $post_preview_url ) . '">';
		echo '			<p class="description">' . __( 'URL address of the post to preview', 'post_preview' ) . '</p>';
		echo ' 			<input type="button" value="Load" id="load_url_btn" src="'.plugins_url('/post-preview-post-type/img/ajax-loader.gif').'" >';		
		echo '		</td>';
		echo '		<td>';
		echo ' 			<img id="ajx_loadr" src="'.plugins_url('/post-preview-post-type/img/ajax-loader.gif').'" style="display:none">';
		echo '		</td>';
		echo '	</tr>';		

		echo '	<tr>';
		echo '		<th><label for="post_preview_image" class="post_preview_image_label">' . __( 'Image', 'post_preview' ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="post_preview_image" name="post_preview_image" class="post_preview_field" placeholder="' . esc_attr__( '', 'post_preview' ) . '" value="' . esc_attr__( $post_preview_image ) . '">';
		echo '			<p class="description">' . __( 'Image Preview', 'post_preview' ) . '</p>';
		echo ' 			<input type="button" value="Change Media" id="update_img_btn" >';
		echo '		</td>';
		echo '		<td>';
		echo '			<div id="content_preview"><img id="content_img_preview" src="' . esc_attr__( $post_preview_image ) . '" width="200px"></div>';
		echo '		</td>';
		echo '	</tr>';
		
		echo '	<tr>';
		echo '		<th><label for="post_preview_sub_title" class="post_preview_sub_title_label">' . __( 'Sub Title', 'post_preview' ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="post_preview_sub_title" name="post_preview_sub_title" class="post_preview_field" placeholder="' . esc_attr__( '', 'post_preview' ) . '" value="' . esc_attr__( $post_preview_sub_title ) . '">';
		echo '			<p class="description">' . __( 'Sub Title', 'post_preview' ) . '</p>';
		echo '		</td>';
		echo '		<td></td>';
		echo '	</tr>';
		
		echo '	<tr>';
		echo '		<th><label for="post_preview_sharing_description" class="post_preview_sharing_description_label">' . __( 'Facebook Sharing Description', 'post_preview' ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="post_preview_sharing_description" name="post_preview_sharing_description" class="post_preview_field" placeholder="' . esc_attr__( '', 'post_preview' ) . '" value="' . esc_attr__( $post_preview_sharing_description ) . '">';
		echo '			<p class="description">' . __( 'Facebook Sharing Description', 'post_preview' ) . '</p>';
		echo '		</td>';
		echo '		<td></td>';
		echo '	</tr>';		

		//section for instagram display options
		$sel_opt = get_post_meta( get_the_ID(), 'post_preview_insta_vid_size', true);
		
		echo '	<tr>';
		echo '		<th><label for="post_preview_insta_vid_size" class="post_preview_insta_vid_size_label">' . __( 'Instagram Video Dimensions', 'post_preview' ) . '</label></th>';
		echo '		<td>';
		echo '<input type="radio" name="post_preview_insta_vid_size" value="default"';
		if ($sel_opt!='640x640') echo "checked";
		echo ' >Default </input>';
		echo '<input type="radio" name="post_preview_insta_vid_size" value="640x640"';
		if ($sel_opt=='640x640') echo "checked";
		echo ' >640x640</input>';
		echo '			<p class="description">' . __( 'Chose layout dimensions for Instagram Video ', 'post_preview' ) . '</p>';
		echo '		</td>';
		echo '		<td></td>';
		echo '	</tr>';		

		echo '</table></td>';
		/*
		echo '<td style="width: 30%;">';
		echo '<table class="form-table">';
		echo '	<tr>';
		//echo '		<th><label for="insta_embedder" class="post_preview_url_label">' . __( 'Instagram Embedder', 'post_preview' ) . '</label></th>';		
		echo '		<td><iframe src="http://ctrlq.org/instagram/" style="width:350px; height:500px;"> </iframe></td>';		
		echo '	</tr>';
		echo '</table>';
		echo '</td>';
		*/
		echo '</tr></table>';
		echo '<div id="ajax_returned_content" style="display:none"></div>';
	}

	public function save_metabox( $post_id, $post ) {

		// Add nonce for security and authentication.
		$nonce_name   = $_POST['post_preview_nonce'];
		$nonce_action = 'post_preview_nonce_action';

		// Check if a nonce is set.
		if ( ! isset( $nonce_name ) )
			return;

		// Check if a nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) )
			return;

		// Check if the user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		// Check if it's not an autosave.
		if ( wp_is_post_autosave( $post_id ) )
			return;

		// Check if it's not a revision.
		if ( wp_is_post_revision( $post_id ) )
			return;

		// Sanitize user input.
		$post_preview_new_name = isset( $_POST[ 'post_preview_sub_title' ] ) ? sanitize_text_field( $_POST[ 'post_preview_sub_title' ] ) : '';
		$post_preview_new_url = isset( $_POST[ 'post_preview_url' ] ) ? ( $_POST[ 'post_preview_url' ] ) : '';
		$post_preview_new_image = isset( $_POST[ 'post_preview_image' ] ) ? esc_html( $_POST[ 'post_preview_image' ] ) : '';
		$post_preview_new_insta_vid_size = isset( $_POST[ 'post_preview_image' ] ) ? sanitize_text_field( $_POST[ 'post_preview_insta_vid_size' ] ) : '';
		
		$post_preview_new_sharing = isset( $_POST[ 'post_preview_sharing_description' ] ) ? sanitize_text_field( $_POST[ 'post_preview_sharing_description' ] ) : '';
		
		// Update the meta field in the database.
		update_post_meta( $post_id, 'post_preview_sub_title', $post_preview_new_name );
		update_post_meta( $post_id, 'post_preview_url', $post_preview_new_url );
		update_post_meta( $post_id, 'post_preview_image', $post_preview_new_image );
		update_post_meta( $post_id, 'post_preview_insta_vid_size', $post_preview_new_insta_vid_size );
		
		update_post_meta( $post_id, 'post_preview_sharing_description', $post_preview_new_sharing );

	}

}

new Preview_Type_Meta_Box;

/* updating view all post previews */

// Change the columns for the edit CPT screen
function post_preview_change_columns( $cols ) {
  $cols = array(
    'cb'       => '<input type="checkbox" />',
    'title'      => __( 'Title',      'post_preview' ),
	'url'      => __( 'URL',      'post_preview' ),
    'subtitle' => __( 'Sub Title', 'post_preview' ),
    'image'     => __( 'Image', 'post_preview' ),
	'facebook_description' => __( 'Facebook Sharing Description', 'post_preview' ),
  );
  return $cols;
}
add_filter( "manage_post_preview_posts_columns", "post_preview_change_columns" );

function post_preview_custom_columns( $column, $post_id ) {
  switch ( $column ) {
	case "title":
      //$url = get_post_meta( $post_id, 'url', true);
      //echo '<a href="' . $url . '">' . $url. '</a>';
	  echo get_the_title ($post_id);
      break;
    case "url":
      $url = get_post_meta( $post_id, 'post_preview_url', true);
      echo '<a href="' . $url . '">' . $url. '</a>';
      break;
    case "subtitle":
      $subtitle = get_post_meta( $post_id, 'post_preview_sub_title', true);
      echo $subtitle;
      break;
	case "facebook_description":
      $subtitle = get_post_meta( $post_id, 'post_preview_sharing_description', true);
      echo $subtitle;
      break;  
    case "image":
      $image = get_post_meta( $post_id, 'post_preview_image', true);
      echo '<img src="'.$image.'" width="150px">';
	  //echo get_post_meta( $post_id, 'host', true);
      break;
  }
}
add_action( "manage_post_preview_posts_custom_column", "post_preview_custom_columns", 10, 2 );

// Make these columns sortable
function post_preview_sortable_columns() {
  return array(
    'title'	   => 'title',
	'url'      => 'url',
    'subtitle' => 'subtitle',
    //'host'     => 'host'
  );
}
add_filter( "manage_edit-post_preview_sortable_columns", "post_preview_sortable_columns" );

/* section for handling instagram embed */
function clean_input($data){
    $data = trim($data);
    $data = strip_tags($data);
    return $data;
}

function clean_input_all($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data);
    return $data;
}

function parse_insta_video(){
	$instaLink = clean_input_all( $_POST['instaLink'] );
	if( !empty($instaLink) ){
		header('Content-Type: application/json; charset=utf-8');
		$response = clean_input( @ file_get_contents($instaLink) );
		$response_length = strlen($response);
		$start_position = strpos( $response ,'window._sharedData = ' ); 
		$start_positionlength = strlen('window._sharedData = ');
		$end_position = strpos($response ,"};")+strlen("};");
		$trimmed = trim( substr($response, ( $start_position + $start_positionlength ), $end_position - ( $start_position + $start_positionlength ) ) ); 
		$jsondata = substr( $trimmed, 0, -1); 
		echo json_encode($jsondata);
		exit();
	} elseif( empty($instaLink) ) {
		die();
	}
}


/*
* Parse remote document for real url and images
*/
function capture_url() {
	
	$url = trim($_POST['url']);
	//Create request
	$request = curl_init();
	curl_setopt_array($request, array(
		CURLOPT_URL => sanitize_url($url),
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_HEADER => FALSE,
		CURLOPT_SSL_VERIFYHOST => FALSE,
		CURLOPT_SSL_VERIFYPEER => FALSE,
		//CURLOPT_CAINFO => 'cacert.pem',
		CURLOPT_FOLLOWLOCATION => TRUE,
	));
	$response = curl_exec($request);
	//echo "response:".$response;
	$real_url = curl_getinfo($request, CURLINFO_EFFECTIVE_URL); //Throw away shorten url
	curl_close($request);

	//Save real url in a custom field
	$slug = sanitize_title($url);
	//update_post_meta($post_id, $this->cf_prefix . "url-" . $slug, $real_url);

	if($response) {
		//Create DOM document
		$document = new DOMDocument();

		//Load response into document, if we got any
		libxml_use_internal_errors(true);
		$document->loadHTML($response);
		libxml_clear_errors();
		
		//Disable warnings	
		//error_reporting(E_ERROR | E_PARSE);
		
		$image_url = "";
		$content_title = "";
		
		//Get OpenGraph image and title
		$parse_og_meta = true;//get_option($this->plugin_name . '_img_og_meta', true);
		if ($parse_og_meta) {
			foreach($document->getElementsByTagName('meta') as $meta_tag) {
				
				if (empty($image_url)) {
					if ($meta_tag->getAttribute('property') == 'og:image'){ 
						$image_url = $meta_tag->getAttribute('content');
					}
				}
				
				if (empty($content_title)) {
					if ($meta_tag->getAttribute('property') == 'og:title'){ 
						$content_title = $meta_tag->getAttribute('content');
					}
				}

				if (!empty($image_url) && !empty($content_title))
					break;
			}
			//case of facebook, or other older versions:
			if (empty($image_url)){

				
				//print_r($document);//->getElementsByTagName('img'));
				//print_r($document->getElementsByTagName('*'));
				foreach($document->getElementsByTagName('img') as $img_entry) {
					//echo $img_entry->getAttribute('class');
					//if (stripos($img_entry->getAttribute('class'), 'scaledImageFitWidth') !== false) {
					//if ($img_entry->getAttribute('class') == 'scaledImageFitWidth'){ 
						$image_url = $img_entry->getAttribute('src');
						$content_title = $img_entry->getAttribute('alt');
						
						//in case this image url is a sub-url, meaning it doesnt capture the full domain, append the base domain to it
						if (strpos($image_url,'/')==0){
							// var_dump(parse_url($url));
							$url_components = parse_url($url);
							$full_url = '';
							//scheme is the http or https portion
							if (isset($url_components["scheme"])){
								$full_url = $url_components["scheme"].'://';
							}
							//host is the domain
							if (isset($url_components["host"])){
								$full_url .= $url_components["host"];
							}
							$image_url = $full_url.$image_url;
							// echo $image_url;
							// die();
						}
						break;
					//}
				}
			}

		}
		
	}
	$returned_vals = array('img'=> $image_url,
							'title'=> $content_title);
	//echo json_encode(array('img'=> $image_url,'title'=> $content_title));
	$returned_vals_js = json_encode($returned_vals);
	echo '<script>';
	echo 'var returned_content_array = '. $returned_vals_js . ';';
	echo '</script>';

}

// capturing the URL via Ajax
add_action( 'wp_ajax_fetch_url_prev', 'capture_url' );
add_action( 'wp_ajax_parse_insta_video', 'parse_insta_video' );



// Creating the widget
class wpb_widget extends WP_Widget {
	function __construct() {
		parent::__construct(
		// Base ID of your widget
		'post_preview_widget',
		// Widget name will appear in UI
		__('Post Previews Widget', 'post_preview'),
		// Widget description
		array( 'description' => __( 'Widget Allowing Display of Post Previews', 'post_preview' ), )
		);
	}
	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$link_count = apply_filters( 'widget_link_count', $instance['link_count'] );
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
		// This is where you run the code and display the output
		//echo "<script>alert('".$link_count."');</script>";
		echo do_shortcode('[post_preview_posts_sc limit='.$link_count.']');
		echo $args['after_widget'];
	}
	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Post Previews', 'post_preview' );
		}
		if ( isset( $instance[ 'link_count' ] ) ) {
			$link_count = $instance[ 'link_count' ];
		}
		else {
			//default value
			$link_count = 10;
		}
		// Widget admin form
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p><label for="<?php echo $this->get_field_id( 'link_count' ); ?>">Number of Post Previews to show:</label>
		<input class="tiny-text" id="<?php echo $this->get_field_id( 'link_count' ); ?>" name="<?php echo $this->get_field_name( 'link_count' ); ?>" type="number" step="1" min="1" value="<?php echo esc_attr( $link_count ); ?>" size="3"></p>
		<?php
	}
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['link_count'] = ( ! empty( $new_instance['link_count'] ) ) ? $new_instance['link_count'] : '10';
		return $instance;
	}
} // Class wpb_widget ends here
// Register and load the widget
function wpb_load_widget() {
    register_widget( 'wpb_widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );


add_action( 'admin_footer', 'fetch_url_prev_javascript' );

function fetch_url_prev_javascript() { ?>
<script type="text/javascript" >
	<!-- ajax calls to parse the URL -->
	jQuery(document).ready(function($) {
		//focus on URL entry
		$( "#post_preview_url").focus();
		$('#load_url_btn').on('click', function() {
			//alert( this.value ); // or $(this).val()
			//check first if the entered value is a facebook iframe value, to extract proper URL and update entry
			if ($('#post_preview_url').val().indexOf('<iframe') == 0){
				var full_text = $('#post_preview_url').val();
				var sub_right = full_text.substr(full_text.indexOf('src="')+5);
				var sub_link = sub_right.substr(0, sub_right.indexOf('"'));
				$('#post_preview_url').val(sub_link);
			}
			
			if (!isUrlValid(document.getElementById('post_preview_url').value)){
				alert('Invalid URL. Please adjust before attempting to load');
				event.preventDefault() ;
				event.stopPropagation();
			}else{
				if ($('#post_preview_url').val().indexOf('instagram')>0){
					// console.log('instagram');
					//instagram case
					
					document.getElementById('ajx_loadr').style.display="block";
					
					/* posting code to grab embed values */
					var data = {
						'action': 'parse_insta_video',
						'url': document.getElementById('post_preview_url').value,
						'instaLink': $('#post_preview_url').val(),
					};
					
					var posting = jQuery.post(ajaxurl, data);
					posting.done(function( data ) {
						//alert(data);
						
						// console.log( data ); // returns json
						var insta_vals = $.parseJSON(data);
						var info_container = insta_vals.entry_data.PostPage[0].graphql.shortcode_media;
						// if( info_container.is_video ){
							// console.log("image_url:"+info_container.display_url);
							// console.log("video_url:"+info_container.video_url);
						// }
						
						// $( "#ajax_returned_content" ).empty().append( data );
						// $( "[name=post_title]").val(returned_content_array['title']);
						
						
						//checking if video url is available. If not, grab it from the edge_sidecar_to_children
						//this is confirmed via checking the value of the is_video attribute
						//by default, grab the value of the video_url attribute.
						var vid_url = info_container.video_url;
						if (!info_container.is_video){
							var child_edges = info_container.edge_sidecar_to_children.edges;
							$.each(child_edges, function(index,edge_entry){
								//check if this node is a video, if so we found our needed vid
								if (edge_entry.node.is_video){
									vid_url = edge_entry.node.video_url;
									//break from the loop
									return false;
								}
							});
						}
						//setting video URL properly
						$('#post_preview_url').val(vid_url);
						
						//modifying image URL according to returned val
						$( "#post_preview_image").val(info_container.display_url);
						$( "#content_img_preview").attr("src", info_container.display_url);
						
						//
						document.getElementById('ajx_loadr').style.display="none";
						
						$( "[name=post_title]").focus();
					});
					
				}else{
					//other cases
					var data = {
						'action': 'fetch_url_prev',
						'url': document.getElementById('post_preview_url').value,
					};
					
					document.getElementById('ajx_loadr').style.display="block";

					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					/*jQuery.post(ajaxurl, data, function(response) {
						alert('Got this from the server: ' + response);
					});			*/
					var posting = jQuery.post(ajaxurl, data);
					posting.done(function( data ) {
						//alert(data);
						$( "#ajax_returned_content" ).empty().append( data );

						$( "[name=post_title]").focus();
						$( "[name=post_title]").val(returned_content_array['title']);
						
						$( "#post_preview_image").val(returned_content_array['img']);
						$( "#content_img_preview").attr("src", returned_content_array['img']);
						
						//
						document.getElementById('ajx_loadr').style.display="none";
					});
				}
			}
		});
		
		
		//$('#update_img_btn').on('click', function(event) {
		$('#update_img_btn').live('click', function(event) {
			/*if (!isUrlValid(document.getElementById('post_preview_image').value)){
				alert('Invalid Image URL. Please adjust before attempting to update');
				event.preventDefault() ;
				event.stopPropagation();
			}else{
				$( "#content_img_preview").attr("src", $( "#post_preview_image").val());
			}*/
			// Uploading files		
			var file_frame;			
			event.preventDefault();		
			// If the media frame already exists, reopen it.		    
			if ( file_frame ) {		      
				file_frame.open();		      
				return;		    
			}	
			
			// Create the media frame.		    
			file_frame = wp.media.frames.file_frame = wp.media({		      
			title: jQuery( this ).data( 'uploader_title' ),		      
			button: {		        
				text: jQuery( this ).data( 'uploader_button_text' ),		      
			},		      
			multiple: false  // Set to true to allow multiple files to be selected		    
			});		
			// When an image is selected, run a callback.		    
			file_frame.on( 'select', function() {		      
				// We set multiple to false so only get one image from the uploader		      
				/*attachment = file_frame.state().get('selection').first().toJSON();		
				// Do something with attachment.id and/or attachment.url here		
				attachment = attachment.toJSON();				
				alert("att:".attachment.url);	*/
				var selection = file_frame.state().get('selection');
				//alert("att:"+selection.attributes.url);
				selection.map( function( attachment ) {		
					attachment = attachment.toJSON();		
					//alert("att:"+attachment.url);
					$( "#post_preview_image").attr("value", attachment.url);
					$( "#content_img_preview").attr("src", attachment.url);
				});				
			});		
			// Finally, open the modal		    
			file_frame.open();		  
		});
		
	});
	
	function isUrlValid(url) {
		url = url.trim();
		return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
	}
</script>

<?php

}

function add_JS_frontend(){

?>
	
<style>
.post_preview_thumb_title a{
	font-size: 24px;
	line-height: 1em;
	font-weight: bold;
}
</style>

<?php

}

add_action( 'wp_footer', 'add_JS_frontend' );


?>
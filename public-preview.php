<?php
/**
* Plugin Name: WP Public Preview
* Plugin URI: http://russellramey.me/wordpress/wp-no-comments
* Description: Enables non logged in users to preview posts, pages, or other content types in draft status.
* Version: 1.0
* Author: Russell Ramey
* Author URI: http://russellramey.me/
*/


// Add preview action to sleceted post types
add_filter( 'posts_results', 'wp_public_previews', null, 2 );
function wp_public_previews( $posts, &$query ) {

	// If 0 or more than 1 post is returned, return normally
	// else get post attributes as variables
    if ( sizeof($posts) != 1 ) {

    	return $posts;

    } else {

		// Get post type 
		$type_options = array("post", "page");
		$type = get_post_type( $posts[0] );

		// Get post status & set status object
		$status = get_post_status( $posts[0] );
		$post_status_obj = get_post_status_object( $status );
	
	}


	// If post status is public, return as normal as it is public
    if ( $post_status_obj->public ) {
    	return $posts;
    }

    // If post is draft, and ?_publicpreview=true
    if (!isset( $_GET['_publicpreview'] ) || $_GET['_publicpreview'] != 'true' ) {
		 return $posts;
    }

	// Store main post query
    $query->_public_preview_cache = $posts; /* stash away */

    // If correct post type
    if(in_array($type, $type_options)){

		// Remove filter
	    add_filter( 'the_posts', 'wp_public_previews_active', null, 2 );
		function wp_public_previews_active( $posts, &$query ) {
		    /* do only once */
		    remove_filter( 'the_posts', 'wp_public_previews_active', null, 2 );
		    return $query->_public_preview_cache;
		}

	}
	
}








// Add metabox to display valid preview link
add_action( 'add_meta_boxes', 'wp_public_preview_metabox' );
function wp_public_preview_metabox($post) {

	// Global post var
	global $post;
	
	// Get post status
    $status = get_post_status($post->ID);
    $post_status_obj = get_post_status_object( $status );



	// If post is not published show metabox
    if ( $status != 'publish' ) {
		// Add metabox
		add_meta_box('wp_public_preview', 'Public Preview', 'public_preview_markup', array('post', 'page'), 'side', 'default');
	}

	// Create content and output for metabox
	function public_preview_markup($post) {

		// Get post Preview Key
	    $current_key = get_post_meta($post->ID, '_publicpreview_key', true);
	    // If post does not have a current key
	    if($current_key) {
	    	$preview_key = $current_key;
	    } else {
	    	$preview_key = public_preview_key_generator();
	    }

	    // Echo out all markup
		echo '<p><label for="publicpreview"><input type="checkbox" name="publicpreview_toggle" id="publicpreview" value="true"> Enable Public Preview</label></p>';
		echo '<input type="hidden" name="publicpreview_key" value="' . $preview_key . '">';
		echo '<input style="width:100%" type="text" value="' . get_bloginfo('url') . '/?p=' . $post->ID  . '&_publicpreview=' . $preview_key . '" />';
		echo '<p><i>If enabled, use this link to provide a public preview link. This will allow someone to view "Draft" status posts without a login.</i></p>';
	}

}








// Save All Metadata
add_action("save_post", "public_preview_meta_save", 10, 3);
function public_preview_meta_save($post_id, $post, $update) {
	//Check to make sure the name of our field that is going to be saved is there. 
	if( isset($_POST['publicpreview_toggle']) ) {

		// Update meta value in DB
        update_post_meta($post_id, '_publicpreview_key', $_POST['publicpreview_key'] );
        update_post_meta($post_id, '_publicpreview_toggle', 'true');
	} else {
		// Update meta value in DB
        update_post_meta($post_id, '_publicpreview_toggle', 'false');
	}

}

// Random key generator
function public_preview_key_generator($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    // Return Key string
    return $randomString;
}
?>
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
	

	// Get post type 
	$type_options = array("post", "page");
	$type = get_post_type( $posts[0] );

	// Get post status & set status object
    $status = get_post_status( $posts[0] );
    $post_status_obj = get_post_status_object( $status );


	// If more than 1 post is returned, return normally
    if ( sizeof($posts) != 1 ) {
    	return $posts;
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
		add_meta_box('preview_output_code', 'Public Preview', 'public_preview_markup', array('post', 'page'), 'side', 'default');
		
	}

	// Create content and output for metabox
	function public_preview_markup($post) {
		echo '<p><label for="publicpreview"><input type="checkbox" name="_wp_pp_enabled" id="publicpreview" value="true"> Enable Public Preview</label></p>';

		echo '<input style="width:100%" type="text" value="' . get_bloginfo('url') . '/?p=' . $post->ID  . '&_publicpreview=true" />';
		echo '<p><i>If enabled, use this link to provide a public preview link. This will allow someone to view "Draft" status posts without a login.</i></p>';
	}

	// Save All Metadata
	add_action("save_post", "public_preview_meta_save", 10, 3);
	function public_preview_meta_save($post_id, $post, $update) {

	}
}
?>
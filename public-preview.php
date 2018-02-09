<?php
/**
* Plugin Name: WP Public Preview
* Plugin URI: http://russellramey.me/wordpress/wp-no-comments
* Description: Enables non logged in users to preview posts, pages, or other content types in draft status.
* Version: 1.0
* Author: Russell Ramey
* Author URI: http://russellramey.me/
*/


/************************************************************************************
*** Hook into the Post Results
	Check the posts for our parameters and return accordingly
************************************************************************************/
add_filter( 'posts_results', 'wp_public_previews', null, 2 );
function wp_public_previews( $posts, &$query ) {

	// If more than 1 post is returned, return normally
	// else get post attributes as variables
    if ( sizeof($posts) != 1 ) {

    	// Return the posts results
    	return $posts;

    // If single, we want it
    } else {

		// Get post type 
		$type_options = array("post", "page");
		$type = get_post_type( $posts[0] );

		// Get post status & set status object
		$status = get_post_status( $posts[0] );
		$post_status_obj = get_post_status_object( $status );

		// Get post meta
		// Preview Key
	    $current_preview_key = get_post_meta($posts[0]->ID, '_publicpreview_key', true);
	    // Preview Toggle
	    $current_preview_status = get_post_meta($posts[0]->ID, '_publicpreview_toggle', true);
	    
	    // If post does not have a current key
	    if($current_preview_key) {
	    	$preview_key = $current_preview_key;
	    } else {
	    	$preview_key = null;
	    }

	}

	// If post status is public, return as normal as it is public
    if ( $post_status_obj->public ) {

    	// Return post normally
    	return $posts;

    }

    // If post is draft, and ?_publicpreview=true
    if (!isset( $_GET['_publicpreview'] ) || $_GET['_publicpreview'] != $preview_key  ) {

    	// Return post normally
		return $posts;

    } else {

		// Store main post query
	    $query->_public_preview_cache = $posts; /* stash away */

	    // If correct post type
	    if(in_array($type, $type_options) && $current_preview_status != 'false'){

			// Remove filter
		    add_filter( 'the_posts', 'wp_public_previews_active', null, 2 );
			function wp_public_previews_active( $posts, &$query ) {
			    /* do only once */
			    remove_filter( 'the_posts', 'wp_public_previews_active', null, 2 );
			    return $query->_public_preview_cache;
			}

		}

	}
	
}


/************************************************************************************
*** Helper funcitons
	Load the required functions for the preview function to utlize
************************************************************************************/
include_once('inc/pp-metabox.php');
include_once('inc/pp-functions.php');
?>
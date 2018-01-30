<?php
/**
* Plugin Name: WP Public Preview
* Plugin URI: http://russellramey.me/wordpress/wp-no-comments
* Description: Enables non logged in users to preview posts, pages, or other content types in draft status.
* Version: 1.0
* Author: Russell Ramey
* Author URI: http://russellramey.me/
*/

// Add wp_public_preview function to wp post_results
add_filter( 'posts_results', 'wp_public_previews', null, 2 );
function wp_public_previews( $posts, &$query ) {

	// If more than 1 post is returned
    if ( sizeof( $posts ) != 1 ) return $posts; /* not interested */

	// Get post status
    $status = get_post_status( $posts[0] );
    $post_status_obj = get_post_status_object( $status );

	// If post is public, return as normal
    if ( $post_status_obj->public ) return $posts; /* it's public */

	// If post is draft, and ?_publicpreview=true
    if ( !isset( $_GET['_publicpreview'] ) || $_GET['_publicpreview'] != 'true' )
        return $posts; /* not for your eyes */

	// Store post query
    $query->_public_preview_stash = $posts; /* stash away */

	// Remove filter
    add_filter( 'the_posts', 'wp_public_previews_inject_private', null, 2 );
	function wp_public_previews_inject_private( $posts, &$query ) {
	    /* do only once */
	    remove_filter( 'the_posts', 'wp_public_previews_inject_private', null, 2 );
	    return $query->_public_preview_stash;
	}
}

// Add metabox to display valid preview link
add_action( 'add_meta_boxes', 'wp_public_preview_output' );
function wp_public_preview_output() {
	global $post;
	// Get post status
    $status = get_post_status($post->ID);
    $post_status_obj = get_post_status_object( $status );

	// If post is not published show metabox
    if ( $status != 'publish' ) {
		// Add metabox
		add_meta_box('preview_output_code', 'Public Preview', 'preview_output_code', 'post', 'side', 'default');
	}

	// Display public preview url in metabox
	function preview_output_code() {
		global $post;
		echo '<input style="width:100%" type="text" value="' . get_bloginfo('url') . '/?p=' . $post->ID  . '&_publicpreview=true" />';
		echo '<p><i>Use this link to provide a public preview link. This will allow someone to view "Draft" status posts without loggin in.</i></p>';
	}
}
?>
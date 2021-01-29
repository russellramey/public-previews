<?php
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

		// Add / Show metabox with preview details
		add_meta_box('wp_public_preview', 'Public Preview', 'public_preview_markup', array('post', 'page'), 'side', 'default');

	} else {

		// If published, delete preview meta data
        delete_post_meta($post->ID, '_publicpreview_key' );
        delete_post_meta($post->ID, '_publicpreview_toggle');
        
	}

	// Create content and output for metabox
	function public_preview_markup($post) {
		// WP Nonce Hook (required)
        wp_nonce_field(basename(__FILE__), "_publicpreview-nonce");

		// Get post Preview Meta
	    $current_preview_key = get_post_meta($post->ID, '_publicpreview_key', true);
	    $current_preview_status = get_post_meta($post->ID, '_publicpreview_toggle', true);

	    // If post does not have a current key
	    if($current_preview_key) {
	    	$preview_key = $current_preview_key;
	    } else {
	    	$preview_key = public_preview_key_generator();
	    }

	    // If preview is enabled
	    if($current_preview_status != 'false'){
	    	$checked = 'checked';
	    } else {
	    	$checked = '';
	    }

	    // Echo out all markup
		echo '<p><label for="publicpreview"><input type="checkbox" name="publicpreview_toggle" id="publicpreview" value="true" ' . $checked . '> Enable Public Preview</label></p>';
		echo '<input type="hidden" name="publicpreview_key" value="' . $preview_key . '">';
		echo '<input style="width:100%; padding:.5rem 1rem;" type="text" value="' . get_bloginfo('url') . '/?p=' . $post->ID  . '&_publicpreview=' . $preview_key . '" />';
		echo '<p style="margin-top:.5rem"><i>If enabled, use this link to provide a public preview link. This will allow someone to view "Draft" status posts without a login or account.</i></p>';
	}

}



// Save All Metadata
add_action("save_post", "public_preview_meta_save", 10, 2);
function public_preview_meta_save($post_id) {

	// Check WP Nonce (required)
	if (!isset($_POST["_publicpreview-nonce"]) || !wp_verify_nonce($_POST["_publicpreview-nonce"], basename(__FILE__))) {
        return $post_id;
	}
	// Check if WP is autosaving
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
		//Bail!
		return $post_id;
	}
	// Check if WP is doing AJAX
	if( defined('DOING_AJAX') && DOING_AJAX ) {
		//Bail!
		return $post_id;
	}

	//Check to make sure the name of our field that is going to be saved is there. 
	if( isset($_POST['publicpreview_toggle']) ) {

		// Sanitize key for db input
		$safe_preview_key = sanitize_text_field( $_POST['publicpreview_key'] );

		// Update meta value in DB
        update_post_meta($post_id, '_publicpreview_key', $safe_preview_key );
        update_post_meta($post_id, '_publicpreview_toggle', 'true');

	} else {

		// Update meta value in DB
		update_post_meta($post_id, '_publicpreview_key', null );
        update_post_meta($post_id, '_publicpreview_toggle', 'false');
	}

}
?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Folders Organizer - Pages, Posts and custom post types specific extras
// The shared sidebar, list filter, bulk actions and ajax live in
// folders-sidebar.php - this file only auto-assigns a new page / post /
// custom post to the folder that was open when "Add New" was clicked
// ===========================================================================

	// ---------------------------------------
	// post-new.php creates the auto-draft of the new item during this same
	// request, so hooking wp_insert_post here catches it and saves it into
	// the folder. an abandoned auto-draft is later deleted by WordPress and
	// removed again by the delete cleanup, and counts never include it
	// ---------------------------------------

	add_action( 'load-post-new.php', function() {

		if( !isset( $_GET['infinity23_fo_nonce'] ) ) { return; }

		if( !wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['infinity23_fo_nonce'] ) ), 'infinity23_fo_add_new' ) ) { return; }

		if( !isset( $_GET['infinity23_folder'] ) ) { return; }

		$folder_id = intval( $_GET['infinity23_folder'] );

		if( empty( $folder_id ) ) { return; }

		$folder = infinity23_folders_organizer_get_folder( $folder_id );

		if( empty( $folder ) ) { return; }

		$content_type = $folder->content_type;

		if( !infinity23_folders_organizer_is_content_type_enabled( $content_type ) ) { return; }
		if( !infinity23_folders_organizer_user_can_use( $content_type ) ) { return; }

		$post_type = infinity23_folders_organizer_get_post_type_for_content_type( $content_type );

		if( $post_type === 'attachment' ) { return; }

		add_action( 'wp_insert_post', function( $post_id, $post ) use ( $folder_id, $post_type ) {

			if( empty( $post ) || $post->post_type !== $post_type ) { return; }

			infinity23_folders_organizer_add_images_to_folder( $folder_id, array( $post_id ) );

		}, 10, 2 ); // add_action( 'wp_insert_post'

	} ); // add_action( 'load-post-new.php'

?>
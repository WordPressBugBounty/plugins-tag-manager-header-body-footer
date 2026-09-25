<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Folders Organizer - Media Library specific extras
// The shared sidebar, list filter, bulk actions and ajax live in
// folders-sidebar.php - this file only holds what is unique to uploads:
// forcing the List view, and auto-assigning a fresh upload to the open folder
// ===========================================================================

	// ---------------------------------------
	// helper - are we on any screen that can trigger a plupload upload
	// (the Media Library list itself, or the separate "Add New" screen)
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_is_media_upload_screen' ) ) {

		function infinity23_folders_organizer_is_media_upload_screen() {

			global $pagenow;

			return ( is_admin() && in_array( $pagenow, array( 'upload.php', 'media-new.php' ), true ) );

		} // function infinity23_folders_organizer_is_media_upload_screen() {

	} // if( !function_exists( 'infinity23_folders_organizer_is_media_upload_screen' ) ) {

	// ---------------------------------------
	// force the classic List view early, before any output - grid view
	// browses attachments by ajax and cannot be filtered by folder this way
	// ---------------------------------------

	add_action( 'load-upload.php', function() {

		if( !infinity23_folders_organizer_is_content_type_enabled( 'images' ) ) { return; }
		if( !infinity23_folders_organizer_user_can_use( 'images' ) ) { return; }

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view-mode check, only triggers a redirect to the same screen in list view
		if( !isset( $_GET['mode'] ) || $_GET['mode'] !== 'list' ) {
			wp_safe_redirect( add_query_arg( 'mode', 'list' ) );
			exit;
		} // if( !isset( $_GET['mode'] ) || $_GET['mode'] !== 'list' ) {

	} );

	// ---------------------------------------
	// tell plupload which folder is currently open, so a fresh upload can be
	// auto-assigned to it (read back below in infinity23_folders_organizer_auto_assign_upload)
	// ---------------------------------------

	add_filter( 'plupload_default_params', function( $params ) {

		if( !infinity23_folders_organizer_is_media_upload_screen() ) { return $params; }
		if( !infinity23_folders_organizer_is_content_type_enabled( 'images' ) ) { return $params; }

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- plain GET link from the folders sidebar, just pre-fills the uploader's target folder
		$current_folder_id = isset( $_GET['infinity23_folder'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_folder'] ) ) : '';

		if( ctype_digit( $current_folder_id ) ) {
			$params['infinity23_folder_id'] = intval( $current_folder_id );
			$params['infinity23_fo_upload_nonce'] = wp_create_nonce( 'infinity23_fo_upload' );
		} // if( ctype_digit( $current_folder_id ) ) {

		return $params;

	} );

	// ---------------------------------------
	// auto-assign a freshly uploaded image to the folder open at upload time
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_auto_assign_upload' ) ) {

		function infinity23_folders_organizer_auto_assign_upload( $attachment_id ) {

			if( !isset( $_REQUEST['infinity23_fo_upload_nonce'] ) ) { return; }

			if( !wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['infinity23_fo_upload_nonce'] ) ), 'infinity23_fo_upload' ) ) { return; }

			if( !infinity23_folders_organizer_is_content_type_enabled( 'images' ) ) { return; }
			if( !infinity23_folders_organizer_user_can_use( 'images' ) ) { return; }

			if( !isset( $_REQUEST['infinity23_folder_id'] ) ) { return; }

			$folder_id = intval( $_REQUEST['infinity23_folder_id'] );

			if( empty( $folder_id ) ) { return; }

			$folder = infinity23_folders_organizer_get_folder( $folder_id );

			if( empty( $folder ) || $folder->content_type !== 'images' ) { return; }

			infinity23_folders_organizer_add_images_to_folder( $folder_id, array( $attachment_id ) );

		} // function infinity23_folders_organizer_auto_assign_upload( $attachment_id ) {

	} // if( !function_exists( 'infinity23_folders_organizer_auto_assign_upload' ) ) {

	add_action( 'add_attachment', 'infinity23_folders_organizer_auto_assign_upload' );

?>
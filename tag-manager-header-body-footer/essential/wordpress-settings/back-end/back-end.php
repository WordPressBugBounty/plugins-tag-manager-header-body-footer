<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	include( dirname(__FILE__) . "/../../../include/settings.php" );
	include( dirname(__FILE__) . "/../internal-settings.php" );
	include( dirname(__FILE__) . "/../default-values.php" );

	$infinity23_ws_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

	$infinity23_ws_disable_block_editor = intval( infinity23_pro_get_data( 'disable_block_editor', $infinity23_ws_settings_data, $infinity23_defaults ) );
	$infinity23_ws_disable_widgets_block_editor = intval( infinity23_pro_get_data( 'disable_widgets_block_editor', $infinity23_ws_settings_data, $infinity23_defaults ) );
	$infinity23_ws_remove_gutenberg_frontend_css = intval( infinity23_pro_get_data( 'remove_gutenberg_frontend_css', $infinity23_ws_settings_data, $infinity23_defaults ) );
	$infinity23_ws_stop_wp_images_resize = intval( infinity23_pro_get_data( 'stop_wp_images_resize', $infinity23_ws_settings_data, $infinity23_defaults ) );
	$infinity23_ws_remove_default_image_title_on_upload = intval( infinity23_pro_get_data( 'remove_default_image_title_on_upload', $infinity23_ws_settings_data, $infinity23_defaults ) );
	$infinity23_ws_remove_empty_image_title_tag = intval( infinity23_pro_get_data( 'remove_empty_image_title_tag', $infinity23_ws_settings_data, $infinity23_defaults ) );
	$infinity23_ws_load_filename_in_media_library = intval( infinity23_pro_get_data( 'load_filename_in_media_library', $infinity23_ws_settings_data, $infinity23_defaults ) );
	$infinity23_ws_sanitize_non_english_image_names = intval( infinity23_pro_get_data( 'sanitize_non_english_image_names', $infinity23_ws_settings_data, $infinity23_defaults ) );

	// ===========================================================================
	// Gutenberg settings
	// ===========================================================================

	if( $infinity23_ws_disable_block_editor == 1 ) {
		add_filter( 'use_block_editor_for_post', '__return_false' );
	} // if( $infinity23_ws_disable_block_editor == 1 ) {

	if( $infinity23_ws_disable_widgets_block_editor == 1 ) {
		add_filter( 'use_widgets_block_editor', '__return_false' );
	} // if( $infinity23_ws_disable_widgets_block_editor == 1 ) {

	if( $infinity23_ws_remove_gutenberg_frontend_css == 1 ) {

		add_action( 'wp_enqueue_scripts', function() {
			wp_dequeue_style( 'wp-block-library' );
		} ); // add_action( 'wp_enqueue_scripts'

	} // if( $infinity23_ws_remove_gutenberg_frontend_css == 1 ) {

	// ===========================================================================
	// Media settings
	// ===========================================================================

	if( $infinity23_ws_stop_wp_images_resize == 1 ) {
		add_filter( 'intermediate_image_sizes', '__return_empty_array', 999 );
	} // if( $infinity23_ws_stop_wp_images_resize == 1 ) {

	if( $infinity23_ws_remove_default_image_title_on_upload == 1 ) {

		add_action( 'add_attachment', function( $attachment_id ) {

			$attachment = get_post( $attachment_id );

			if( $attachment && strpos( $attachment->post_mime_type, 'image' ) !== false ) {

				wp_update_post( array(
					'ID' => $attachment_id,
					'post_title' => '',
				) );

			} // if( $attachment && strpos( $attachment->post_mime_type, 'image' ) !== false ) {

		} ); // add_action( 'add_attachment'

	} // if( $infinity23_ws_remove_default_image_title_on_upload == 1 ) {

	if( $infinity23_ws_remove_empty_image_title_tag == 1 ) {

		add_filter( 'the_content', 'infinity23_wordpress_settings_remove_empty_image_title' );
		add_filter( 'post_thumbnail_html', 'infinity23_wordpress_settings_remove_empty_image_title' );
		add_filter( 'wp_get_attachment_image', 'infinity23_wordpress_settings_remove_empty_image_title' );

	} // if( $infinity23_ws_remove_empty_image_title_tag == 1 ) {

	if( $infinity23_ws_load_filename_in_media_library == 1 ) {

		add_filter( 'wp_prepare_attachment_for_js', function( $response, $attachment, $meta ) {

			if( empty( $response['title'] ) ) {

				$file_path = get_attached_file( $attachment->ID );
				$file_name = pathinfo( $file_path, PATHINFO_FILENAME );

				$response['title'] = $file_name;

			} // if( empty( $response['title'] ) ) {

			return $response;

		}, 10, 3 ); // add_filter( 'wp_prepare_attachment_for_js'

		add_filter( 'the_title', function( $title, $post_id = 0 ) {

			if( empty( $title ) && !empty( $post_id ) && is_admin() && get_post_type( $post_id ) === 'attachment' ) {

				$file_path = get_attached_file( $post_id );
				$title = esc_html( pathinfo( $file_path, PATHINFO_FILENAME ) );

			} // if( empty( $title ) && !empty( $post_id ) && is_admin() && get_post_type( $post_id ) === 'attachment' ) {

			return wp_kses_post( $title );

		}, 10, 2 ); // add_filter( 'the_title'

	} // if( $infinity23_ws_load_filename_in_media_library == 1 ) {

	if( $infinity23_ws_sanitize_non_english_image_names == 1 ) {
		add_filter( 'wp_handle_upload_prefilter', 'infinity23_wordpress_settings_sanitize_image_file_name' );
	} // if( $infinity23_ws_sanitize_non_english_image_names == 1 ) {

	// --------------------------------------------
	// removes an empty title="" attribute from image tags on the front-end
	// --------------------------------------------

	if( !function_exists( 'infinity23_wordpress_settings_remove_empty_image_title' ) ) {

		function infinity23_wordpress_settings_remove_empty_image_title( $content ) {

			$content = preg_replace( '/(<img[^>]+)\s+title=["\'][\s]*["\']([^>]*>)/i', '$1 $2', $content );

			return $content;

		} // function infinity23_wordpress_settings_remove_empty_image_title( $content ) {

	} // if( !function_exists( 'infinity23_wordpress_settings_remove_empty_image_title' ) ) {

	// --------------------------------------------
	// renames an uploaded image if its file name contains anything other than
	// English letters, numbers, dashes, underscores, dots or spaces
	// --------------------------------------------

	if( !function_exists( 'infinity23_wordpress_settings_sanitize_image_file_name' ) ) {

		function infinity23_wordpress_settings_sanitize_image_file_name( $file ) {

			$file_name = $file['name'];
			$file_name_no_ext = pathinfo( $file_name, PATHINFO_FILENAME );

			if( preg_match( '/[^a-zA-Z0-9\-_\.\s]/', $file_name_no_ext ) ) {

				$extension = pathinfo( $file_name, PATHINFO_EXTENSION );
				$file['name'] = 'upload-' . strtotime( 'NOW' ) . '.' . $extension;

			} // if( preg_match( '/[^a-zA-Z0-9\-_\.\s]/', $file_name_no_ext ) ) {

			return $file;

		} // function infinity23_wordpress_settings_sanitize_image_file_name( $file ) {

	} // if( !function_exists( 'infinity23_wordpress_settings_sanitize_image_file_name' ) ) {

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Folders Organizer - dedicated database table
	// One row per folder. content_type keeps images/pages/posts folders in the
	// same table without colliding with each other.
	// ===========================================================================

	// ---------------------------------------
	// table name helper
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_table' ) ) {

		function infinity23_folders_organizer_get_table() {

			global $wpdb;
			return $wpdb->prefix . 'infinity23_folders';

		} // function infinity23_folders_organizer_get_table() {

	} // if( !function_exists( 'infinity23_folders_organizer_get_table' ) ) {

	// ---------------------------------------
	// create the table - called only when the module is Activated
	// ---------------------------------------

	include_once( dirname(__FILE__) . "/../../../include/install-db/install-folders-db.php" );

	// ---------------------------------------
	// row functions
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_folders' ) ) {

		function infinity23_folders_organizer_get_folders( $content_type ) {

			global $wpdb;
			$table = infinity23_folders_organizer_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( $table ) . "` WHERE content_type = %s ORDER BY id DESC", $content_type ) );

		} // function infinity23_folders_organizer_get_folders( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_get_folders' ) ) {

	// ---------------------------------------
	// the values the folders limit select box can have
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_limit_options' ) ) {

		function infinity23_folders_organizer_get_limit_options() {

			return array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'unlimited' );

		} // function infinity23_folders_organizer_get_limit_options() {

	} // if( !function_exists( 'infinity23_folders_organizer_get_limit_options' ) ) {

	// ---------------------------------------
	// how many folders one section may have - returns -1 for unlimited, the
	// free limit is set in default-values.php
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_folder_limit' ) ) {

		function infinity23_folders_organizer_get_folder_limit() {

			include( dirname(__FILE__) . "/../default-values.php" );

			$folder_settings = infinity23_convert_json_to_array( infinity23_get_option( 'folders_organizer_settings' ) );
			$folder_limit = strval( infinity23_pro_get_data( 'folders_limit_per_type', $folder_settings, $infinity23_defaults ) );

			if( !in_array( $folder_limit, infinity23_folders_organizer_get_limit_options(), true ) || $folder_limit === 'unlimited' ) {
				return -1;
			} // if( !in_array( $folder_limit, infinity23_folders_organizer_get_limit_options(), true ) || $folder_limit === 'unlimited' ) {

			return intval( $folder_limit );

		} // function infinity23_folders_organizer_get_folder_limit() {

	} // if( !function_exists( 'infinity23_folders_organizer_get_folder_limit' ) ) {

	// ---------------------------------------
	// can one more folder be created in this section
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_can_add_folder' ) ) {

		function infinity23_folders_organizer_can_add_folder( $content_type ) {

			$folder_limit = infinity23_folders_organizer_get_folder_limit();

			if( $folder_limit < 0 ) {
				return true;
			} // if( $folder_limit < 0 ) {

			return ( count( infinity23_folders_organizer_get_folders( $content_type ) ) < $folder_limit );

		} // function infinity23_folders_organizer_can_add_folder( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_can_add_folder' ) ) {

	if( !function_exists( 'infinity23_folders_organizer_get_folder' ) ) {

		function infinity23_folders_organizer_get_folder( $folder_id ) {

			global $wpdb;
			$table = infinity23_folders_organizer_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . esc_sql( $table ) . "` WHERE id = %d", $folder_id ) );

		} // function infinity23_folders_organizer_get_folder( $folder_id ) {

	} // if( !function_exists( 'infinity23_folders_organizer_get_folder' ) ) {

	if( !function_exists( 'infinity23_folders_organizer_slug_exists' ) ) {

		function infinity23_folders_organizer_slug_exists( $content_type, $slug, $exclude_id = 0 ) {

			global $wpdb;
			$table = infinity23_folders_organizer_get_table();

			if( !empty( $exclude_id ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . esc_sql( $table ) . "` WHERE content_type = %s AND slug = %s AND id != %d", $content_type, $slug, $exclude_id ) );
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . esc_sql( $table ) . "` WHERE content_type = %s AND slug = %s", $content_type, $slug ) );
			} // if( !empty( $exclude_id ) ) {

			return ( intval( $count ) > 0 );

		} // function infinity23_folders_organizer_slug_exists( $content_type, $slug, $exclude_id = 0 ) {

	} // if( !function_exists( 'infinity23_folders_organizer_slug_exists' ) ) {

	if( !function_exists( 'infinity23_folders_organizer_get_next_position' ) ) {

		function infinity23_folders_organizer_get_next_position( $content_type ) {

			global $wpdb;
			$table = infinity23_folders_organizer_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$highest_position = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(position) FROM `" . esc_sql( $table ) . "` WHERE content_type = %s", $content_type ) );

			if( empty( $highest_position ) ) {
				return 1;
			} // if( empty( $highest_position ) ) {

			return floatval( $highest_position ) + 1;

		} // function infinity23_folders_organizer_get_next_position( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_get_next_position' ) ) {

	if( !function_exists( 'infinity23_folders_organizer_add_folder' ) ) {

		function infinity23_folders_organizer_add_folder( $content_type, $name, $slug ) {

			global $wpdb;
			$table = infinity23_folders_organizer_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
			$wpdb->insert(
				$table,
				array(
					'content_type' => $content_type,
					'name' => $name,
					'slug' => $slug,
					'position' => infinity23_folders_organizer_get_next_position( $content_type ),
					'time_created_utc' => infinity23_current_timestamp(),
					'time_updated_utc' => infinity23_current_timestamp(),
				),
				array( '%s', '%s', '%s', '%f', '%d', '%d' )
			);

			return $wpdb->insert_id;

		} // function infinity23_folders_organizer_add_folder( $content_type, $name, $slug ) {

	} // if( !function_exists( 'infinity23_folders_organizer_add_folder' ) ) {

	if( !function_exists( 'infinity23_folders_organizer_update_folder_name' ) ) {

		function infinity23_folders_organizer_update_folder_name( $folder_id, $name, $slug ) {

			global $wpdb;
			$table = infinity23_folders_organizer_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->update(
				$table,
				array(
					'name' => $name,
					'slug' => $slug,
					'time_updated_utc' => infinity23_current_timestamp(),
				),
				array( 'id' => $folder_id ),
				array( '%s', '%s', '%d' ),
				array( '%d' )
			);

		} // function infinity23_folders_organizer_update_folder_name( $folder_id, $name, $slug ) {

	} // if( !function_exists( 'infinity23_folders_organizer_update_folder_name' ) ) {

	if( !function_exists( 'infinity23_folders_organizer_delete_folder' ) ) {

		function infinity23_folders_organizer_delete_folder( $folder_id ) {

			global $wpdb;
			$table = infinity23_folders_organizer_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->delete(
				$table,
				array( 'id' => $folder_id ),
				array( '%d' )
			);

		} // function infinity23_folders_organizer_delete_folder( $folder_id ) {

	} // if( !function_exists( 'infinity23_folders_organizer_delete_folder' ) ) {

	if( !function_exists( 'infinity23_folders_organizer_delete_all_folders' ) ) {

		function infinity23_folders_organizer_delete_all_folders() {

			global $wpdb;
			$table = infinity23_folders_organizer_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->query( "DELETE FROM `" . esc_sql( $table ) . "`" );

		} // function infinity23_folders_organizer_delete_all_folders() {

	} // if( !function_exists( 'infinity23_folders_organizer_delete_all_folders' ) ) {

	// ---------------------------------------
	// content type to WordPress post_type mapping
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_post_type_for_content_type' ) ) {

		function infinity23_folders_organizer_get_post_type_for_content_type( $content_type ) {

			$map = array(
				'images' => 'attachment',
				'pages' => 'page',
				'posts' => 'post',
			);

			// custom post types use their own slug as the content type key
			return isset( $map[ $content_type ] ) ? $map[ $content_type ] : $content_type;

		} // function infinity23_folders_organizer_get_post_type_for_content_type( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_get_post_type_for_content_type' ) ) {

	if( !function_exists( 'infinity23_folders_organizer_get_content_type_for_post_type' ) ) {

		function infinity23_folders_organizer_get_content_type_for_post_type( $post_type ) {

			$map = array(
				'attachment' => 'images',
				'page' => 'pages',
				'post' => 'posts',
			);

			return isset( $map[ $post_type ] ) ? $map[ $post_type ] : $post_type;

		} // function infinity23_folders_organizer_get_content_type_for_post_type( $post_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_get_content_type_for_post_type' ) ) {

	// ---------------------------------------
	// read the item ids saved in one folder row's data1 column
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_folder_image_ids' ) ) {

		function infinity23_folders_organizer_get_folder_image_ids( $folder_id ) {

			global $wpdb;
			$table = infinity23_folders_organizer_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$raw_data1 = $wpdb->get_var( $wpdb->prepare( "SELECT data1 FROM `" . esc_sql( $table ) . "` WHERE id = %d", $folder_id ) );
			$ids_array = infinity23_convert_json_to_array( $raw_data1 );

			if( empty( $ids_array ) || !is_array( $ids_array ) ) {
				return array();
			} // if( empty( $ids_array ) || !is_array( $ids_array ) ) {

			return array_values( array_unique( array_map( 'intval', $ids_array ) ) );

		} // function infinity23_folders_organizer_get_folder_image_ids( $folder_id ) {

	} // if( !function_exists( 'infinity23_folders_organizer_get_folder_image_ids' ) ) {

	// ---------------------------------------
	// same as above but from a folder row already fetched by get_folders() -
	// saves one query per folder when looping the whole list
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_folder_ids_from_row' ) ) {

		function infinity23_folders_organizer_get_folder_ids_from_row( $folder ) {

			$ids_array = infinity23_convert_json_to_array( isset( $folder->data1 ) ? $folder->data1 : '' );

			if( empty( $ids_array ) || !is_array( $ids_array ) ) {
				return array();
			} // if( empty( $ids_array ) || !is_array( $ids_array ) ) {

			return array_values( array_unique( array_map( 'intval', $ids_array ) ) );

		} // function infinity23_folders_organizer_get_folder_ids_from_row( $folder ) {

	} // if( !function_exists( 'infinity23_folders_organizer_get_folder_ids_from_row' ) ) {

	// ---------------------------------------
	// overwrite the full ids list saved on one folder row
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_save_folder_image_ids' ) ) {

		function infinity23_folders_organizer_save_folder_image_ids( $folder_id, $image_ids ) {

			global $wpdb;
			$table = infinity23_folders_organizer_get_table();

			$clean_ids = array_values( array_unique( array_map( 'intval', (array) $image_ids ) ) );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->update(
				$table,
				array(
					'data1' => infinity23_convert_array_to_json( $clean_ids ),
					'time_updated_utc' => infinity23_current_timestamp(),
				),
				array( 'id' => $folder_id ),
				array( '%s', '%d' ),
				array( '%d' )
			);

		} // function infinity23_folders_organizer_save_folder_image_ids( $folder_id, $image_ids ) {

	} // if( !function_exists( 'infinity23_folders_organizer_save_folder_image_ids' ) ) {

	// ---------------------------------------
	// add one or more ids to a folder without losing what is already saved
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_add_images_to_folder' ) ) {

		function infinity23_folders_organizer_add_images_to_folder( $folder_id, $image_ids ) {

			$existing_ids = infinity23_folders_organizer_get_folder_image_ids( $folder_id );
			$new_ids = array_map( 'intval', (array) $image_ids );
			$merged_ids = array_values( array_unique( array_merge( $existing_ids, $new_ids ) ) );

			infinity23_folders_organizer_save_folder_image_ids( $folder_id, $merged_ids );

		} // function infinity23_folders_organizer_add_images_to_folder( $folder_id, $image_ids ) {

	} // if( !function_exists( 'infinity23_folders_organizer_add_images_to_folder' ) ) {

	// ---------------------------------------
	// remove one or more ids from a folder
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_remove_images_from_folder' ) ) {

		function infinity23_folders_organizer_remove_images_from_folder( $folder_id, $image_ids ) {

			$existing_ids = infinity23_folders_organizer_get_folder_image_ids( $folder_id );
			$remove_ids = array_map( 'intval', (array) $image_ids );
			$remaining_ids = array_values( array_diff( $existing_ids, $remove_ids ) );

			infinity23_folders_organizer_save_folder_image_ids( $folder_id, $remaining_ids );

		} // function infinity23_folders_organizer_remove_images_from_folder( $folder_id, $image_ids ) {

	} // if( !function_exists( 'infinity23_folders_organizer_remove_images_from_folder' ) ) {

	// ---------------------------------------
	// every id assigned to any folder of the given content type
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_all_assigned_image_ids' ) ) {

		function infinity23_folders_organizer_get_all_assigned_image_ids( $content_type ) {

			$folders = infinity23_folders_organizer_get_folders( $content_type );
			$all_ids = array();

			foreach( $folders as $folder ) {
				$all_ids = array_merge( $all_ids, infinity23_folders_organizer_get_folder_ids_from_row( $folder ) );
			} // foreach( $folders as $folder ) {

			return array_values( array_unique( $all_ids ) );

		} // function infinity23_folders_organizer_get_all_assigned_image_ids( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_get_all_assigned_image_ids' ) ) {

	// ---------------------------------------
	// every published item of the content type that is not inside any folder
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_unassigned_image_ids' ) ) {

		function infinity23_folders_organizer_get_unassigned_image_ids( $content_type ) {

			$all_ids = infinity23_folders_organizer_get_existing_item_ids( $content_type );
			$assigned_ids = infinity23_folders_organizer_get_all_assigned_image_ids( $content_type );

			return array_values( array_diff( $all_ids, $assigned_ids ) );

		} // function infinity23_folders_organizer_get_unassigned_image_ids( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_get_unassigned_image_ids' ) ) {

	// ---------------------------------------
	// every item of the content type that currently exists and is not in the
	// trash - the same items the regular WordPress list shows under "All".
	// every count in the sidebar is checked against this list, so a deleted
	// or trashed item is never counted even if its id is still saved
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_existing_item_ids' ) ) {

		function infinity23_folders_organizer_get_existing_item_ids( $content_type ) {

			global $wpdb;

			static $existing_ids_cache = array();

			if( isset( $existing_ids_cache[ $content_type ] ) ) {
				return $existing_ids_cache[ $content_type ];
			} // if( isset( $existing_ids_cache[ $content_type ] ) ) {

			$post_type = infinity23_folders_organizer_get_post_type_for_content_type( $content_type );

			if( $post_type === 'attachment' ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				$all_ids = $wpdb->get_col( "SELECT ID FROM `{$wpdb->posts}` WHERE post_type = 'attachment' AND post_status = 'inherit'" );
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				$all_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM `{$wpdb->posts}` WHERE post_type = %s AND post_status IN ( 'publish', 'draft', 'pending', 'private', 'future' )", $post_type ) );
			} // if( $post_type === 'attachment' ) {

			$existing_ids_cache[ $content_type ] = array_map( 'intval', $all_ids );

			return $existing_ids_cache[ $content_type ];

		} // function infinity23_folders_organizer_get_existing_item_ids( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_get_existing_item_ids' ) ) {

	// ---------------------------------------
	// remove one item id from every folder of its content type - used when
	// an item is permanently deleted from WordPress
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_remove_item_from_all_folders' ) ) {

		function infinity23_folders_organizer_remove_item_from_all_folders( $item_id, $content_type ) {

			$item_id = intval( $item_id );
			$folders = infinity23_folders_organizer_get_folders( $content_type );

			foreach( $folders as $folder ) {

				$folder_ids = infinity23_folders_organizer_get_folder_ids_from_row( $folder );

				if( in_array( $item_id, $folder_ids, true ) ) {
					infinity23_folders_organizer_save_folder_image_ids( $folder->id, array_diff( $folder_ids, array( $item_id ) ) );
				} // if( in_array( $item_id, $folder_ids, true ) ) {

			} // foreach( $folders as $folder ) {

		} // function infinity23_folders_organizer_remove_item_from_all_folders( $item_id, $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_remove_item_from_all_folders' ) ) {

?>
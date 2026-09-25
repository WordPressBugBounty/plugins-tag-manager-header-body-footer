<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Redirect 301/302 - one dedicated table for both folders and rows
	// This lives in its own table (not infinity23_options / infinity23_modules)
	// because the front-end needs a direct query for every visitor page load,
	// and mixing that into the shared settings/modules tables would force
	// unrelated rows to be scanned on every request.
	// A type column ('folder' or 'row') tells the two kinds of rows apart.
	// ===========================================================================

	// ---------------------------------------
	// table name helper
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_get_table' ) ) {

		function infinity23_redirect_get_table() {

			global $wpdb;
			return $wpdb->prefix . 'infinity23_redirects';

		} // function infinity23_redirect_get_table() {

	} // if( !function_exists( 'infinity23_redirect_get_table' ) ) {

	// ---------------------------------------
	// create the table - called only when the module is Activated
	// ---------------------------------------

	include_once( dirname(__FILE__) . "/../../../include/install-db/install-redirect-301-db.php" );

	// ---------------------------------------
	// folder functions - always type = 'folder'
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_get_folders' ) ) {

		function infinity23_redirect_get_folders() {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$folders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( $table ) . "` WHERE type = %s ORDER BY id DESC", 'folder' ) );

			if( empty( $folders ) ) {
				return $folders;
			} // if( empty( $folders ) ) {

			$favorite_folders = array();
			$other_folders = array();

			foreach( $folders as $folder ) {

				if( infinity23_redirect_folder_is_favorite( $folder ) ) {
					$favorite_folders[] = $folder;
				} else {
					$other_folders[] = $folder;
				} // if( infinity23_redirect_folder_is_favorite( $folder ) ) {

			} // foreach( $folders as $folder ) {

			usort( $favorite_folders, function( $folder_a, $folder_b ) {
				return strcasecmp( $folder_a->name, $folder_b->name );
			} );

			return array_merge( $favorite_folders, $other_folders );

		} // function infinity23_redirect_get_folders() {

	} // if( !function_exists( 'infinity23_redirect_get_folders' ) ) {

	if( !function_exists( 'infinity23_redirect_get_folder' ) ) {

		function infinity23_redirect_get_folder( $folder_id ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . esc_sql( $table ) . "` WHERE id = %d AND type = %s", $folder_id, 'folder' ) );

		} // function infinity23_redirect_get_folder( $folder_id ) {

	} // if( !function_exists( 'infinity23_redirect_get_folder' ) ) {

	if( !function_exists( 'infinity23_redirect_folder_slug_exists' ) ) {

		function infinity23_redirect_folder_slug_exists( $slug ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . esc_sql( $table ) . "` WHERE type = %s AND slug = %s", 'folder', $slug ) );

			return ( intval( $count ) > 0 );

		} // function infinity23_redirect_folder_slug_exists( $slug ) {

	} // if( !function_exists( 'infinity23_redirect_folder_slug_exists' ) ) {

	if( !function_exists( 'infinity23_redirect_add_folder' ) ) {

		function infinity23_redirect_add_folder( $name, $slug ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			$insert_data = array(
				'type' => 'folder',
				'name' => $name,
				'slug' => $slug,
				'time_created_utc' => infinity23_current_timestamp(),
				'time_updated_utc' => infinity23_current_timestamp(),
			);
			$insert_format = array( '%s', '%s', '%s', '%d', '%d' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
			$wpdb->insert( $table, $insert_data, $insert_format );

			// no proactive SHOW TABLES check here - only pay the recreate+retry
			// cost the one time the table is actually missing
			if( !empty( $wpdb->last_error ) ) {

				infinity23_redirect_install_tables();
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
				$wpdb->insert( $table, $insert_data, $insert_format );

			} // if( !empty( $wpdb->last_error ) ) {

			return $wpdb->insert_id;

		} // function infinity23_redirect_add_folder( $name, $slug ) {

	} // if( !function_exists( 'infinity23_redirect_add_folder' ) ) {

	if( !function_exists( 'infinity23_redirect_update_folder' ) ) {

		function infinity23_redirect_update_folder( $folder_id, $name, $slug ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->update(
				$table,
				array(
					'name' => $name,
					'slug' => $slug,
					'time_updated_utc' => infinity23_current_timestamp(),
				),
				array( 'id' => $folder_id, 'type' => 'folder' ),
				array( '%s', '%s', '%d' ),
				array( '%d', '%s' )
			);

		} // function infinity23_redirect_update_folder( $folder_id, $name, $slug ) {

	} // if( !function_exists( 'infinity23_redirect_update_folder' ) ) {

	if( !function_exists( 'infinity23_redirect_delete_folder' ) ) {

		function infinity23_redirect_delete_folder( $folder_id ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->delete( $table, array( 'id' => $folder_id, 'type' => 'folder' ), array( '%d', '%s' ) );
			infinity23_redirect_delete_rows_by_folder( $folder_id );

		} // function infinity23_redirect_delete_folder( $folder_id ) {

	} // if( !function_exists( 'infinity23_redirect_delete_folder' ) ) {

	if( !function_exists( 'infinity23_redirect_folder_is_favorite' ) ) {

		function infinity23_redirect_folder_is_favorite( $folder ) {

			if( empty( $folder ) || empty( $folder->data1 ) ) {
				return false;
			} // if( empty( $folder ) || empty( $folder->data1 ) ) {

			$folder_data = infinity23_convert_json_to_array( $folder->data1 );

			return ( isset( $folder_data['favorite'] ) && intval( $folder_data['favorite'] ) === 1 );

		} // function infinity23_redirect_folder_is_favorite( $folder ) {

	} // if( !function_exists( 'infinity23_redirect_folder_is_favorite' ) ) {

	if( !function_exists( 'infinity23_redirect_set_folder_favorite' ) ) {

		function infinity23_redirect_set_folder_favorite( $folder_id, $is_favorite ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			$folder_data = array( 'favorite' => $is_favorite ? 1 : 0 );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->update(
				$table,
				array(
					'data1' => infinity23_convert_array_to_json( $folder_data ),
					'time_updated_utc' => infinity23_current_timestamp(),
				),
				array( 'id' => $folder_id, 'type' => 'folder' ),
				array( '%s', '%d' ),
				array( '%d', '%s' )
			);

		} // function infinity23_redirect_set_folder_favorite( $folder_id, $is_favorite ) {

	} // if( !function_exists( 'infinity23_redirect_set_folder_favorite' ) ) {

	// ---------------------------------------
	// row functions - always type = 'row'
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_get_rows_by_folder' ) ) {

		function infinity23_redirect_get_rows_by_folder( $folder_id ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( $table ) . "` WHERE type = %s AND folder_id = %d ORDER BY position ASC", 'row', $folder_id ) );

		} // function infinity23_redirect_get_rows_by_folder( $folder_id ) {

	} // if( !function_exists( 'infinity23_redirect_get_rows_by_folder' ) ) {

	if( !function_exists( 'infinity23_redirect_get_all_rows' ) ) {

		function infinity23_redirect_get_all_rows() {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( $table ) . "` WHERE type = %s ORDER BY id ASC", 'row' ) );

		} // function infinity23_redirect_get_all_rows() {

	} // if( !function_exists( 'infinity23_redirect_get_all_rows' ) ) {

	if( !function_exists( 'infinity23_redirect_count_rows_by_folder' ) ) {

		function infinity23_redirect_count_rows_by_folder( $folder_id ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . esc_sql( $table ) . "` WHERE type = %s AND folder_id = %d", 'row', $folder_id ) ) );

		} // function infinity23_redirect_count_rows_by_folder( $folder_id ) {

	} // if( !function_exists( 'infinity23_redirect_count_rows_by_folder' ) ) {

	if( !function_exists( 'infinity23_redirect_get_row' ) ) {

		function infinity23_redirect_get_row( $row_id ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . esc_sql( $table ) . "` WHERE id = %d AND type = %s", $row_id, 'row' ) );

		} // function infinity23_redirect_get_row( $row_id ) {

	} // if( !function_exists( 'infinity23_redirect_get_row' ) ) {

	if( !function_exists( 'infinity23_redirect_request_url_exists' ) ) {

		function infinity23_redirect_request_url_exists( $request_url ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . esc_sql( $table ) . "` WHERE type = %s AND request_url = %s", 'row', $request_url ) );

			return ( intval( $count ) > 0 );

		} // function infinity23_redirect_request_url_exists( $request_url ) {

	} // if( !function_exists( 'infinity23_redirect_request_url_exists' ) ) {

	if( !function_exists( 'infinity23_redirect_get_next_position' ) ) {

		function infinity23_redirect_get_next_position( $folder_id ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$highest_position = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(position) FROM `" . esc_sql( $table ) . "` WHERE type = %s AND folder_id = %d", 'row', $folder_id ) );

			if( empty( $highest_position ) ) {
				return 1;
			} // if( empty( $highest_position ) ) {

			return floatval( $highest_position ) + 1;

		} // function infinity23_redirect_get_next_position( $folder_id ) {

	} // if( !function_exists( 'infinity23_redirect_get_next_position' ) ) {

	if( !function_exists( 'infinity23_redirect_add_row' ) ) {

		function infinity23_redirect_add_row( $row_data ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			$insert_data = array(
				'type' => 'row',
				'folder_id' => $row_data['folder_id'],
				'position' => $row_data['position'],
				'request_url' => $row_data['request_url'],
				'destination_url' => $row_data['destination_url'],
				'redirect_type' => $row_data['redirect_type'],
				'notes' => $row_data['notes'],
				'advertising_platform' => $row_data['advertising_platform'],
				'redirects_amount' => 0,
				'redirect_query' => $row_data['redirect_query'],
				'get_parameters' => $row_data['get_parameters'],
				'time_created_utc' => infinity23_current_timestamp(),
				'time_updated_utc' => infinity23_current_timestamp(),
			);
			$insert_format = array( '%s', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
			$wpdb->insert( $table, $insert_data, $insert_format );

			// no proactive SHOW TABLES check here - only pay the recreate+retry
			// cost the one time the table is actually missing
			if( !empty( $wpdb->last_error ) ) {

				infinity23_redirect_install_tables();
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
				$wpdb->insert( $table, $insert_data, $insert_format );

			} // if( !empty( $wpdb->last_error ) ) {

			return $wpdb->insert_id;

		} // function infinity23_redirect_add_row( $row_data ) {

	} // if( !function_exists( 'infinity23_redirect_add_row' ) ) {

	if( !function_exists( 'infinity23_redirect_update_row' ) ) {

		function infinity23_redirect_update_row( $row_id, $row_data ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->update(
				$table,
				array(
					'folder_id' => $row_data['folder_id'],
					'position' => $row_data['position'],
					'request_url' => $row_data['request_url'],
					'destination_url' => $row_data['destination_url'],
					'redirect_type' => $row_data['redirect_type'],
					'notes' => $row_data['notes'],
					'advertising_platform' => $row_data['advertising_platform'],
					'redirects_amount' => $row_data['redirects_amount'],
					'redirect_query' => $row_data['redirect_query'],
					'get_parameters' => $row_data['get_parameters'],
					'time_updated_utc' => infinity23_current_timestamp(),
				),
				array( 'id' => $row_id, 'type' => 'row' ),
				array( '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d' ),
				array( '%d', '%s' )
			);

		} // function infinity23_redirect_update_row( $row_id, $row_data ) {

	} // if( !function_exists( 'infinity23_redirect_update_row' ) ) {

	if( !function_exists( 'infinity23_redirect_delete_row' ) ) {

		function infinity23_redirect_delete_row( $row_id ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->delete( $table, array( 'id' => $row_id, 'type' => 'row' ), array( '%d', '%s' ) );

		} // function infinity23_redirect_delete_row( $row_id ) {

	} // if( !function_exists( 'infinity23_redirect_delete_row' ) ) {

	if( !function_exists( 'infinity23_redirect_delete_rows_by_folder' ) ) {

		function infinity23_redirect_delete_rows_by_folder( $folder_id ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->delete( $table, array( 'folder_id' => $folder_id, 'type' => 'row' ), array( '%d', '%s' ) );

		} // function infinity23_redirect_delete_rows_by_folder( $folder_id ) {

	} // if( !function_exists( 'infinity23_redirect_delete_rows_by_folder' ) ) {

	if( !function_exists( 'infinity23_redirect_increment_count' ) ) {

		function infinity23_redirect_increment_count( $row_id ) {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->query( $wpdb->prepare( "UPDATE `" . esc_sql( $table ) . "` SET redirects_amount = redirects_amount + 1 WHERE id = %d AND type = %s", $row_id, 'row' ) );

		} // function infinity23_redirect_increment_count( $row_id ) {

	} // if( !function_exists( 'infinity23_redirect_increment_count' ) ) {

	// ---------------------------------------
	// front-end matching data - always type = 'row'
	// single query that returns every redirect row, split into an exact-match
	// lookup array (keyed by request_url, preserving DB row order per key so
	// firing order matches the old IN() query exactly) and a plain contain-match
	// list - this replaces two separate queries per request with one
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_get_matching_data' ) ) {

		function infinity23_redirect_get_matching_data() {

			global $wpdb;
			$table = infinity23_redirect_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$all_rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( $table ) . "` WHERE type = %s", 'row' ) );

			// no proactive SHOW TABLES check here - this runs on every front-end
			// request, so we only pay the recreate+retry cost the one time the
			// table is actually missing (e.g. the main options table was removed
			// manually and only got recreated on an admin page load)
			if( !empty( $wpdb->last_error ) ) {

				infinity23_redirect_install_tables();
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				$all_rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( $table ) . "` WHERE type = %s", 'row' ) );

			} // if( !empty( $wpdb->last_error ) ) {

			$exact_lookup = array();
			$contain_rows = array();

			if( !empty( $all_rows ) ) {

				foreach( $all_rows as $row ) {

					if( $row->redirect_query === 'exact' ) {

						if( !isset( $exact_lookup[ $row->request_url ] ) ) {
							$exact_lookup[ $row->request_url ] = array();
						} // if( !isset( $exact_lookup[ $row->request_url ] ) ) {

						$exact_lookup[ $row->request_url ][] = $row;

					} elseif( $row->redirect_query === 'contain' ) {

						$contain_rows[] = $row;

					} // if( $row->redirect_query === 'exact' ) {

				} // foreach( $all_rows as $row ) {

			} // if( !empty( $all_rows ) ) {

			return array(
				'exact_lookup' => $exact_lookup,
				'contain_rows' => $contain_rows,
			);

		} // function infinity23_redirect_get_matching_data() {

	} // if( !function_exists( 'infinity23_redirect_get_matching_data' ) ) {

	// ---------------------------------------
	// URL Shortener - random code generation, lowercase letters and numbers only
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_generate_random_code' ) ) {

		function infinity23_redirect_generate_random_code( $length ) {

			$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
			$code = '';

			for( $i = 0; $i < $length; $i++ ) {
				$code .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
			} // for( $i = 0; $i < $length; $i++ ) {

			return $code;

		} // function infinity23_redirect_generate_random_code( $length ) {

	} // if( !function_exists( 'infinity23_redirect_generate_random_code' ) ) {

	// ---------------------------------------
	// URL Shortener - generate a code and keep retrying until it does not
	// already exist as a saved request_url, so a duplicate is never returned
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_generate_unique_shortcode' ) ) {

		function infinity23_redirect_generate_unique_shortcode( $length, $prefix = '' ) {

			$length = intval( $length );

			if( $length < 5 ) { $length = 5; }
			if( $length > 10 ) { $length = 10; }

			$prefix = trim( strval( $prefix ), '/' );
			$prefix_path = !empty( $prefix ) ? '/' . $prefix . '/' : '/';

			$infinity23_shortcode_attempts = 0;
			$infinity23_shortcode_max_attempts = 20;

			do {

				$random_code = infinity23_redirect_generate_random_code( $length );
				$request_url = $prefix_path . $random_code . '/';
				$infinity23_shortcode_attempts++;

			} while( infinity23_redirect_request_url_exists( $request_url ) && $infinity23_shortcode_attempts < $infinity23_shortcode_max_attempts );

			if( infinity23_redirect_request_url_exists( $request_url ) ) {
				return '';
			} // if( infinity23_redirect_request_url_exists( $request_url ) ) {

			return $request_url;

		} // function infinity23_redirect_generate_unique_shortcode( $length, $prefix = '' ) {

	} // if( !function_exists( 'infinity23_redirect_generate_unique_shortcode' ) ) {

?>
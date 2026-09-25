<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Page Notes - dedicated database table
	// Each note is its own row, grouped by page_post_id, following the same
	// dedicated table pattern used by Redirect 301/302.
	// ===========================================================================

	// ---------------------------------------
	// table name helper
	// ---------------------------------------

	if( !function_exists( 'infinity23_notes_get_table' ) ) {

		function infinity23_notes_get_table() {

			global $wpdb;
			return $wpdb->prefix . 'infinity23_notes';

		} // function infinity23_notes_get_table() {

	} // if( !function_exists( 'infinity23_notes_get_table' ) ) {

	// ---------------------------------------
	// create the table - called when the module is Activated,
	// and defensively every time the back-end loader runs
	// ---------------------------------------

	include_once( infinity23_plugin_dir_path( 'include/install-db/install-notes-db.php' ) );

	// ---------------------------------------
	// row functions
	// ---------------------------------------

	if( !function_exists( 'infinity23_notes_get_rows_by_page' ) ) {

		function infinity23_notes_get_rows_by_page( $page_post_id ) {

			global $wpdb;
			$table = infinity23_notes_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( $table ) . "` WHERE page_post_id = %s ORDER BY position ASC, id ASC", $page_post_id ) );

		} // function infinity23_notes_get_rows_by_page( $page_post_id ) {

	} // if( !function_exists( 'infinity23_notes_get_rows_by_page' ) ) {

	if( !function_exists( 'infinity23_notes_get_row' ) ) {

		function infinity23_notes_get_row( $note_id ) {

			global $wpdb;
			$table = infinity23_notes_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . esc_sql( $table ) . "` WHERE id = %d", $note_id ) );

		} // function infinity23_notes_get_row( $note_id ) {

	} // if( !function_exists( 'infinity23_notes_get_row' ) ) {

	if( !function_exists( 'infinity23_notes_get_all_pages_with_notes' ) ) {

		function infinity23_notes_get_all_pages_with_notes() {

			global $wpdb;
			$table = infinity23_notes_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$results = $wpdb->get_col( "SELECT DISTINCT page_post_id FROM `" . esc_sql( $table ) . "`" );

			if( !empty( $results ) ) {
				return $results;
			} // if( !empty( $results ) ) {

			return array();

		} // function infinity23_notes_get_all_pages_with_notes() {

	} // if( !function_exists( 'infinity23_notes_get_all_pages_with_notes' ) ) {

	if( !function_exists( 'infinity23_notes_get_all_rows' ) ) {

		function infinity23_notes_get_all_rows() {

			global $wpdb;
			$table = infinity23_notes_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return $wpdb->get_results( "SELECT * FROM `" . esc_sql( $table ) . "` ORDER BY time_created_utc DESC" );

		} // function infinity23_notes_get_all_rows() {

	} // if( !function_exists( 'infinity23_notes_get_all_rows' ) ) {

	if( !function_exists( 'infinity23_notes_add_row' ) ) {

		function infinity23_notes_add_row( $row_data ) {

			global $wpdb;
			$table = infinity23_notes_get_table();

			$insert_data = array(
				'page_post_id' => $row_data['page_post_id'],
				'note_text' => $row_data['note_text'],
				'direction' => $row_data['direction'],
				'position' => $row_data['position'],
				'time_created_utc' => infinity23_current_timestamp(),
				'time_updated_utc' => infinity23_current_timestamp(),
			);
			$insert_format = array( '%s', '%s', '%s', '%f', '%d', '%d' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
			$wpdb->insert( $table, $insert_data, $insert_format );

			// no proactive SHOW TABLES check here - notes save on every post save,
			// so we only pay the recreate+retry cost the one time the table is
			// actually missing, instead of checking existence on every insert
			if( !empty( $wpdb->last_error ) ) {

				infinity23_notes_install_table();
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
				$wpdb->insert( $table, $insert_data, $insert_format );

			} // if( !empty( $wpdb->last_error ) ) {

			return $wpdb->insert_id;

		} // function infinity23_notes_add_row( $row_data ) {

	} // if( !function_exists( 'infinity23_notes_add_row' ) ) {

	if( !function_exists( 'infinity23_notes_update_row' ) ) {

		function infinity23_notes_update_row( $note_id, $row_data ) {

			global $wpdb;
			$table = infinity23_notes_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->update(
				$table,
				array(
					'page_post_id' => $row_data['page_post_id'],
					'note_text' => $row_data['note_text'],
					'direction' => $row_data['direction'],
					'position' => $row_data['position'],
					'time_updated_utc' => infinity23_current_timestamp(),
				),
				array( 'id' => $note_id ),
				array( '%s', '%s', '%s', '%f', '%d' ),
				array( '%d' )
			);

		} // function infinity23_notes_update_row( $note_id, $row_data ) {

	} // if( !function_exists( 'infinity23_notes_update_row' ) ) {

	if( !function_exists( 'infinity23_notes_delete_row' ) ) {

		function infinity23_notes_delete_row( $note_id ) {

			global $wpdb;
			$table = infinity23_notes_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->delete( $table, array( 'id' => $note_id ), array( '%d' ) );

		} // function infinity23_notes_delete_row( $note_id ) {

	} // if( !function_exists( 'infinity23_notes_delete_row' ) ) {

	if( !function_exists( 'infinity23_notes_delete_rows_by_page' ) ) {

		function infinity23_notes_delete_rows_by_page( $page_post_id ) {

			global $wpdb;
			$table = infinity23_notes_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->delete( $table, array( 'page_post_id' => $page_post_id ), array( '%s' ) );

		} // function infinity23_notes_delete_rows_by_page( $page_post_id ) {

	} // if( !function_exists( 'infinity23_notes_delete_rows_by_page' ) ) {

	if( !function_exists( 'infinity23_notes_delete_all_rows' ) ) {

		function infinity23_notes_delete_all_rows() {

			global $wpdb;
			$table = infinity23_notes_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->query( "DELETE FROM `" . esc_sql( $table ) . "`" );

		} // function infinity23_notes_delete_all_rows() {

	} // if( !function_exists( 'infinity23_notes_delete_all_rows' ) ) {

?>
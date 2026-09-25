<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Email SMTP - email log dedicated table functions. One row per email send
// attempt (success or failed). include_once()'d from back-end.php (writes
// rows on every request that sends mail, plus per-row delete and clear-all
// admin-post handlers), from admin-output.php (install on save, and read
// for display on the Logs tab), and from the daily cron (retention cleanup).
// ===========================================================================

	// ---------------------------------------
	// table name - always call this instead of hardcoding the table name
	// ---------------------------------------

	if( !function_exists( 'infinity23_email_log_get_table' ) ) {

		function infinity23_email_log_get_table() {

			global $wpdb;

			return $wpdb->prefix . 'infinity23_email_log';

		} // function infinity23_email_log_get_table() {

	} // if( !function_exists( 'infinity23_email_log_get_table' ) ) {

	include_once( dirname(__FILE__) . "/../../../include/install-db/install-email-log-db.php" );

	// ---------------------------------------
	// check if the dedicated table currently exists
	// ---------------------------------------

	if( !function_exists( 'infinity23_email_log_table_exists' ) ) {

		function infinity23_email_log_table_exists() {

			global $wpdb;

			$table = infinity23_email_log_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return ( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" ) == $table );

		} // function infinity23_email_log_table_exists() {

	} // if( !function_exists( 'infinity23_email_log_table_exists' ) ) {

	// ---------------------------------------
	// insert one log row for a single email send attempt
	// ---------------------------------------

	// ---------------------------------------
	// detect if a saved message body contains HTML markup, so the log
	// viewer knows whether to offer a "View As HTML" rendered option
	// ---------------------------------------

	if( !function_exists( 'infinity23_email_log_detect_is_html' ) ) {

		function infinity23_email_log_detect_is_html( $message ) {

			if( empty( $message ) ) {
				return 0;
			} // if( empty( $message ) ) {

			return preg_match( '/<[a-z][\s\S]*>/i', $message ) ? 1 : 0;

		} // function infinity23_email_log_detect_is_html( $message ) {

	} // if( !function_exists( 'infinity23_email_log_detect_is_html' ) ) {

	if( !function_exists( 'infinity23_email_log_insert' ) ) {

		function infinity23_email_log_insert( $sent_to, $subject, $status, $infinity23_error_message = '', $smtp_provider = '', $message = '' ) {

			global $wpdb;

			$table = infinity23_email_log_get_table();

			$insert_data = array(
				'sent_to'          => $sent_to,
				'subject'          => $subject,
				'status'           => ( $status === 'failed' ) ? 'failed' : 'success',
				'error_message'    => $infinity23_error_message,
				'smtp_provider'    => $smtp_provider,
				'message'          => $message,
				'is_html'          => infinity23_email_log_detect_is_html( $message ),
				'time_created_utc' => infinity23_current_timestamp(),
			);
			$insert_format = array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
			$wpdb->insert( $table, $insert_data, $insert_format );

			// no proactive SHOW TABLES check here - this runs on every email sent,
			// so we only pay the recreate+retry cost the one time the table is
			// actually missing, instead of checking existence on every insert
			if( !empty( $wpdb->last_error ) ) {

				infinity23_email_log_install_table();
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
				$wpdb->insert( $table, $insert_data, $insert_format );

			} // if( !empty( $wpdb->last_error ) ) {

		} // function infinity23_email_log_insert( $sent_to, $subject, $status, $infinity23_error_message, $smtp_provider, $message ) {

	} // if( !function_exists( 'infinity23_email_log_insert' ) ) {

	// ---------------------------------------
	// one page of log rows, newest first, optionally filtered by status
	// $status_filter: '' (all) | 'success' | 'failed'
	// ---------------------------------------

	if( !function_exists( 'infinity23_email_log_get_page' ) ) {

		function infinity23_email_log_get_page( $offset, $per_page, $status_filter = '' ) {

			global $wpdb;

			$table = infinity23_email_log_get_table();

			if( in_array( $status_filter, array( 'success', 'failed' ), true ) ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				return $wpdb->get_results( $wpdb->prepare(
					"SELECT * FROM `" . esc_sql( $table ) . "` WHERE status = %s ORDER BY id DESC LIMIT %d, %d",
					$status_filter, $offset, $per_page
				), ARRAY_A );

			} // if( in_array(

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM `" . esc_sql( $table ) . "` ORDER BY id DESC LIMIT %d, %d",
				$offset, $per_page
			), ARRAY_A );

		} // function infinity23_email_log_get_page( $offset, $per_page, $status_filter = '' ) {

	} // if( !function_exists( 'infinity23_email_log_get_page' ) ) {

	// ---------------------------------------
	// total row count, optionally filtered - used to build pagination
	// ---------------------------------------

	if( !function_exists( 'infinity23_email_log_get_total_count' ) ) {

		function infinity23_email_log_get_total_count( $status_filter = '' ) {

			global $wpdb;

			$table = infinity23_email_log_get_table();

			if( in_array( $status_filter, array( 'success', 'failed' ), true ) ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				return intval( $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM `" . esc_sql( $table ) . "` WHERE status = %s",
					$status_filter
				) ) );

			} // if( in_array(

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return intval( $wpdb->get_var( "SELECT COUNT(*) FROM `" . esc_sql( $table ) . "`" ) );

		} // function infinity23_email_log_get_total_count( $status_filter = '' ) {

	} // if( !function_exists( 'infinity23_email_log_get_total_count' ) ) {

	// ---------------------------------------
	// delete one log row
	// ---------------------------------------

	if( !function_exists( 'infinity23_email_log_delete_one' ) ) {

		function infinity23_email_log_delete_one( $log_id ) {

			global $wpdb;

			$table = infinity23_email_log_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->delete( $table, array( 'id' => intval( $log_id ) ), array( '%d' ) );

		} // function infinity23_email_log_delete_one( $log_id ) {

	} // if( !function_exists( 'infinity23_email_log_delete_one' ) ) {

	// ---------------------------------------
	// delete every log row - used by the "Clear All Log Entries" button
	// ---------------------------------------

	if( !function_exists( 'infinity23_email_log_clear_all' ) ) {

		function infinity23_email_log_clear_all() {

			global $wpdb;

			$table = infinity23_email_log_get_table();

			$wpdb->query( "TRUNCATE TABLE `" . esc_sql( $table ) . "`" );

		} // function infinity23_email_log_clear_all() {

	} // if( !function_exists( 'infinity23_email_log_clear_all' ) ) {

	// ---------------------------------------
	// remove rows older than $retention_days, then trim down to $count_limit
	// rows (oldest first) if the table still has more rows than that -
	// called once a day from the daily cron
	// ---------------------------------------

	if( !function_exists( 'infinity23_email_log_cleanup' ) ) {

		function infinity23_email_log_cleanup( $retention_days, $count_limit ) {

			global $wpdb;

			if( !infinity23_email_log_table_exists() ) {
				return;
			} // if( !infinity23_email_log_table_exists() ) {

			$table = infinity23_email_log_get_table();
			$retention_days = intval( $retention_days );

			if( $retention_days > 0 ) {

				$cutoff_timestamp = infinity23_current_timestamp() - ( $retention_days * DAY_IN_SECONDS );

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				$wpdb->query( $wpdb->prepare(
					"DELETE FROM `" . esc_sql( $table ) . "` WHERE time_created_utc < %d",
					$cutoff_timestamp
				) );

			} // if( $retention_days > 0 ) {

			$count_limit = intval( $count_limit );

			if( $count_limit > 0 ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				$current_count = intval( $wpdb->get_var( "SELECT COUNT(*) FROM `" . esc_sql( $table ) . "`" ) );

				if( $current_count > $count_limit ) {

					$rows_to_delete = $current_count - $count_limit;

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
					$wpdb->query( $wpdb->prepare(
						"DELETE FROM `" . esc_sql( $table ) . "` ORDER BY id ASC LIMIT %d",
						$rows_to_delete
					) );

				} // if( $current_count > $count_limit ) {

			} // if( $count_limit > 0 ) {

		} // function infinity23_email_log_cleanup( $retention_days, $count_limit ) {

	} // if( !function_exists( 'infinity23_email_log_cleanup' ) ) {

?>
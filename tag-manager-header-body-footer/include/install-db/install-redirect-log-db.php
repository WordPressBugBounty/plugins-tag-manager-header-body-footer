<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// This page creates the Redirect 301/302 - Redirect Log dedicated database table
// ===========================================================================

	// ---------------------------------------
	// create the table - called only when Redirect Log is Enabled and saved
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_install_table' ) ) {

		function infinity23_redirect_log_install_table() {

			global $wpdb;

			$table = infinity23_redirect_log_get_table();
			$charset_collate = $wpdb->get_charset_collate();

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- schema install - SHOW TABLES / dbDelta on custom table, no WP core API; caching not yet implemented, tracked for later
			if( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" ) != $table ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- inherent to defining this plugin's dedicated table
			$sql = "CREATE TABLE " . $table . " (
				id INTEGER(11) UNSIGNED AUTO_INCREMENT,
				redirect_row_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
				request_url TEXT,
				actual_request_url TEXT,
				destination_url TEXT,
				redirect_type VARCHAR(3) NOT NULL DEFAULT '301',
				ip_address VARCHAR(100) NOT NULL DEFAULT '',
				country VARCHAR(10) NOT NULL DEFAULT '',
				user_agent VARCHAR(500) NOT NULL DEFAULT '',
				browser VARCHAR(50) NOT NULL DEFAULT '',
				platform VARCHAR(50) NOT NULL DEFAULT '',
				device_type VARCHAR(20) NOT NULL DEFAULT '',
				referrer TEXT,
				time_created_utc INT(11) DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY redirect_row_id_idx (redirect_row_id)
				) $charset_collate;";

				dbDelta( $sql );

			} // if( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" ) != $table ) {

		} // function infinity23_redirect_log_install_table() {

	} // if( !function_exists( 'infinity23_redirect_log_install_table' ) ) {

?>
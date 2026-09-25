<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// This page creates the Email SMTP module's Email Log dedicated database table
// ===========================================================================

	// ---------------------------------------
	// create the table - called only when Email Log is saved as enabled
	// ---------------------------------------

	if( !function_exists( 'infinity23_email_log_install_table' ) ) {

		function infinity23_email_log_install_table() {

			global $wpdb;

			$table = infinity23_email_log_get_table();
			$charset_collate = $wpdb->get_charset_collate();

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- schema install - SHOW TABLES / dbDelta on custom table, no WP core API; caching not yet implemented, tracked for later
			if( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" ) != $table ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- inherent to defining this plugin's dedicated table
			$sql = "CREATE TABLE " . $table . " (
				id INTEGER(11) UNSIGNED AUTO_INCREMENT,
				sent_to VARCHAR(500) NOT NULL DEFAULT '',
				subject VARCHAR(500) NOT NULL DEFAULT '',
				status VARCHAR(20) NOT NULL DEFAULT 'success',
				error_message TEXT,
				message LONGTEXT,
				is_html TINYINT(1) NOT NULL DEFAULT 0,
				smtp_provider VARCHAR(50) NOT NULL DEFAULT '',
				time_created_utc INT(11) DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY time_created_utc_idx (time_created_utc)
				) $charset_collate;";

				dbDelta( $sql );

			} // if( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" ) != $table ) {

		} // function infinity23_email_log_install_table() {

	} // if( !function_exists( 'infinity23_email_log_install_table' ) ) {

?>
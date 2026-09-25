<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// This page creates the Redirect 301/302 database table
// ===========================================================================

	// ---------------------------------------
	// create the table - called only when the module is Activated
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_install_tables' ) ) {

		function infinity23_redirect_install_tables() {

			global $wpdb;

			$table = infinity23_redirect_get_table();
			$charset_collate = $wpdb->get_charset_collate();

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- schema install - SHOW TABLES / dbDelta on custom table, no WP core API; caching not yet implemented, tracked for later
			if( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" ) != $table ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- inherent to defining this plugin's dedicated table
			$sql = "CREATE TABLE " . $table . " (
				id INTEGER(11) UNSIGNED AUTO_INCREMENT,
				type VARCHAR(20) NOT NULL DEFAULT 'row',
				folder_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
				name VARCHAR(500) NOT NULL DEFAULT '',
				slug VARCHAR(500) NOT NULL DEFAULT '',
				position FLOAT NOT NULL DEFAULT 1,
				request_url TEXT,
				destination_url TEXT,
				redirect_type VARCHAR(3) NOT NULL DEFAULT '301',
				notes VARCHAR(255) NOT NULL DEFAULT '',
				advertising_platform VARCHAR(255) NOT NULL DEFAULT '',
				redirects_amount INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
				redirect_query VARCHAR(20) NOT NULL DEFAULT 'exact',
				get_parameters TINYINT(1) NOT NULL DEFAULT 0,
				data1 LONGTEXT DEFAULT NULL,
				data2 LONGTEXT DEFAULT NULL,
				data3 LONGTEXT DEFAULT NULL,
				time_created_utc INT(11) DEFAULT NULL,
				time_updated_utc INT(11) DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY type_folder_idx (type, folder_id)
				) $charset_collate;";

				dbDelta( $sql );

			} // if( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" ) != $table ) {

		} // function infinity23_redirect_install_tables() {

	} // if( !function_exists( 'infinity23_redirect_install_tables' ) ) {

?>
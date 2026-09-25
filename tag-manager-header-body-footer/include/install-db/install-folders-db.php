<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// This page creates the Folders Organizer dedicated database table
// ===========================================================================

	// ---------------------------------------
	// create the table - called only when the module is Activated
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_install_table' ) ) {

		function infinity23_folders_organizer_install_table() {

			global $wpdb;

			$table = infinity23_folders_organizer_get_table();
			$charset_collate = $wpdb->get_charset_collate();

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- schema install - SHOW TABLES / dbDelta on custom table, no WP core API; caching not yet implemented, tracked for later
			if( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" ) != $table ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- inherent to defining this plugin's dedicated table
			$sql = "CREATE TABLE " . $table . " (
				id INTEGER(11) UNSIGNED AUTO_INCREMENT,
				content_type VARCHAR(50) NOT NULL DEFAULT 'images',
				name VARCHAR(255) NOT NULL DEFAULT '',
				slug VARCHAR(255) NOT NULL DEFAULT '',
				position FLOAT NOT NULL DEFAULT 1,
				data1 LONGTEXT DEFAULT NULL,
				data2 LONGTEXT DEFAULT NULL,
				time_created_utc INT(11) DEFAULT NULL,
				time_updated_utc INT(11) DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY content_type_idx (content_type)
				) $charset_collate;";

				dbDelta( $sql );

			} // if( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" ) != $table ) {

		} // function infinity23_folders_organizer_install_table() {

	} // if( !function_exists( 'infinity23_folders_organizer_install_table' ) ) {

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// This page creates the Page Notes dedicated database table
// ===========================================================================

	// ---------------------------------------
	// create the table - called only when the module is Activated
	// ---------------------------------------

	if( !function_exists( 'infinity23_notes_install_table' ) ) {

		function infinity23_notes_install_table() {

			global $wpdb;

			$notes_table = infinity23_notes_get_table();
			$charset_collate = $wpdb->get_charset_collate();

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- schema install - SHOW TABLES / dbDelta on custom table, no WP core API; caching not yet implemented, tracked for later
			if( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $notes_table ) . "'" ) != $notes_table ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- inherent to defining this plugin's dedicated table
			$sql = "CREATE TABLE " . $notes_table . " (
				id INTEGER(11) UNSIGNED AUTO_INCREMENT,
				page_post_id VARCHAR(200) NOT NULL DEFAULT '',
				note_text LONGTEXT,
				direction VARCHAR(20) NOT NULL DEFAULT 'direction-ltr',
				position FLOAT NOT NULL DEFAULT 1,
				time_created_utc INT(11) DEFAULT NULL,
				time_updated_utc INT(11) DEFAULT NULL,
				PRIMARY KEY  (id)
				) $charset_collate;";

				dbDelta( $sql );

			} // if( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $notes_table ) . "'" ) != $notes_table ) {

		} // function infinity23_notes_install_table() {

	} // if( !function_exists( 'infinity23_notes_install_table' ) ) {

?>
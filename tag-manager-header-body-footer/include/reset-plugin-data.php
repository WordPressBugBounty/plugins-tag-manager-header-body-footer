<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// This page is the single, central list of every database table the plugin
// creates, used by the Danger Zone feature to remove all of them in one place
// ===========================================================================

	// ---------------------------------------
	// returns every database table this plugin creates - each dedicated-table
	// module's own table-function file is only loaded when that module is
	// still registered in $infinity23_essential_modules_array (see
	// infinity23_get_all_database_tables() below), so a removed module never
	// gets included here
	// ---------------------------------------

	if( !function_exists( 'infinity23_get_all_database_tables' ) ) {

		function infinity23_get_all_database_tables() {

			global $wpdb, $infinity23_table_name, $infinity23_essential_modules_array;

			$tables = array();

			$tables[] = array(
				'label'      => 'Main Settings Table (infinity23_options)',
				'table_name' => $infinity23_table_name,
				'main'       => true,
			);

			if( isset( $infinity23_essential_modules_array['notes'] ) ) {

				include_once( dirname(__FILE__) . "/../essential/page-notes/back-end/notes-db-functions.php" );

				$tables[] = array(
					'label'      => 'Page Notes',
					'table_name' => infinity23_notes_get_table(),
					'main'       => false,
				);

			} // if( isset( $infinity23_essential_modules_array['notes'] ) ) {

			if( isset( $infinity23_essential_modules_array['redirect_301_and_302'] ) ) {

				include_once( dirname(__FILE__) . "/../essential/redirect-301-and-302/back-end/redirect-db-functions.php" );
				include_once( dirname(__FILE__) . "/../essential/redirect-301-and-302/back-end/redirect-log-db-functions.php" );

				$tables[] = array(
					'label'      => 'Redirect 301/302',
					'table_name' => infinity23_redirect_get_table(),
					'main'       => false,
				);

				$tables[] = array(
					'label'      => 'Redirect 301/302 - Redirect Log',
					'table_name' => infinity23_redirect_log_get_table(),
					'main'       => false,
				);

			} // if( isset( $infinity23_essential_modules_array['redirect_301_and_302'] ) ) {

			if( isset( $infinity23_essential_modules_array['folders_organizer'] ) ) {

				include_once( dirname(__FILE__) . "/../essential/folders-organizer/back-end/folders-db-functions.php" );

				$tables[] = array(
					'label'      => 'Folders Organizer',
					'table_name' => infinity23_folders_organizer_get_table(),
					'main'       => false,
				);

			} // if( isset( $infinity23_essential_modules_array['folders_organizer'] ) ) {

			if( isset( $infinity23_essential_modules_array['email_smtp'] ) ) {

				include_once( dirname(__FILE__) . "/../essential/email-smtp/back-end/email-log-db-functions.php" );

				$tables[] = array(
					'label'      => 'Email Log',
					'table_name' => infinity23_email_log_get_table(),
					'main'       => false,
				);

			} // if( isset( $infinity23_essential_modules_array['email_smtp'] ) ) {

			// only report a table if it was actually created - a module can be
			// registered in $infinity23_essential_modules_array (its code exists)
			// before its own settings-save handler has ever created its table
			$infinity23_existing_tables = array();

			foreach( $tables as $infinity23_db_table ) {

				if( !empty( $infinity23_db_table['main'] ) || $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $infinity23_db_table['table_name'] ) . "'" ) === $infinity23_db_table['table_name'] ) {
					$infinity23_existing_tables[] = $infinity23_db_table;
				} // if( !empty( $infinity23_db_table['main'] ) || $wpdb->get_var(

			} // foreach( $tables as $infinity23_db_table ) {

			return $infinity23_existing_tables;

		} // function infinity23_get_all_database_tables() {

	} // if( !function_exists( 'infinity23_get_all_database_tables' ) ) {

	// ---------------------------------------
	// returns the list of subfolders currently inside the shared storage
	// root folder, used to show what will be removed in the Danger Zone
	// ---------------------------------------

	if( !function_exists( 'infinity23_danger_zone_get_storage_folder_list' ) ) {

		function infinity23_danger_zone_get_storage_folder_list() {

			$root_folder = infinity23_get_storage_root_folder();
			$folder_list = array();

			if( !file_exists( $root_folder ) || !is_dir( $root_folder ) ) {
				return $folder_list;
			} // if( !file_exists( $root_folder ) || !is_dir( $root_folder ) ) {

			$items = scandir( $root_folder );

			if( empty( $items ) ) {
				return $folder_list;
			} // if( empty( $items ) ) {

			foreach( $items as $item ) {

				if( $item === '.' || $item === '..' || $item === 'index.php' ) {
					continue;
				} // if( $item === '.' || $item === '..' || $item === 'index.php' ) {

				if( is_dir( $root_folder . '/' . $item ) ) {
					$folder_list[] = $item;
				} // if( is_dir( $root_folder . '/' . $item ) ) {

			} // foreach( $items as $item ) {

			return $folder_list;

		} // function infinity23_danger_zone_get_storage_folder_list() {

	} // if( !function_exists( 'infinity23_danger_zone_get_storage_folder_list' ) ) {

	// ---------------------------------------
	// drops every dedicated table, empties (never drops) the main table,
	// and removes the shared storage folder
	// ---------------------------------------

	if( !function_exists( 'infinity23_danger_zone_remove_all_data' ) ) {

		function infinity23_danger_zone_remove_all_data() {

			global $wpdb, $infinity23_essential_modules_array;

			// restore every cron event Cron Management changed before the main
			// table is emptied - the saved original schedules live in that table
			if( isset( $infinity23_essential_modules_array['cron_management'] ) ) {

				include_once( dirname(__FILE__) . "/../essential/cron-management/back-end/cron-adjustments.php" );

				if( function_exists( 'infinity23_cron_mgmt_restore_all_interval_changes' ) ) {
					infinity23_cron_mgmt_restore_all_interval_changes();
				} // if( function_exists( 'infinity23_cron_mgmt_restore_all_interval_changes' ) ) {

			} // if( isset( $infinity23_essential_modules_array['cron_management'] ) ) {

			$tables = infinity23_get_all_database_tables();

			foreach( $tables as $table ) {

				if( !empty( $table['main'] ) ) {
					$wpdb->query( "TRUNCATE TABLE `" . esc_sql( $table['table_name'] ) . "`" );
				} else {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching -- admin-triggered danger-zone reset, custom tables, no WP core API; caching not yet implemented, tracked for later
					$wpdb->query( "DROP TABLE IF EXISTS `" . esc_sql( $table['table_name'] ) . "`" );
				} // if( !empty( $table['main'] ) ) {

			} // foreach( $tables as $table ) {

			global $infinity23_options_cache, $infinity23_options_data_cache, $infinity23_options_time_cache;

			$infinity23_options_cache = array();
			$infinity23_options_data_cache = array();
			$infinity23_options_time_cache = array();

			// the main table was emptied - restore the rows the plugin relies on
			infinity23_add_option( 'plugin_install_time', infinity23_current_timestamp() );
			infinity23_update_option( 'plugin_version', INFINITY23_VERSION );

			// the main table is only ever truncated here, never dropped, so this
			// row is added back right away - the same as plugin_install_time and
			// plugin_version above. Every module's own activate_code row was just
			// wiped, so it is already off, and we leave it that way on purpose -
			// nothing gets reactivated and no dedicated table gets recreated
			// automatically. The admin has to open Include / Exclude and save it
			// again before any module comes back.
			
			infinity23_update_option( INFINITY23_RESET_PENDING_OPTION, 1 );

			// runs last on purpose - the folder walk can throw or hit an
			// unwritable path, and it used to run while the three rows above
			// were still missing, which left the plugin with no way back
			infinity23_danger_zone_remove_storage_folder();

		} // function infinity23_danger_zone_remove_all_data() {

	} // if( !function_exists( 'infinity23_danger_zone_remove_all_data' ) ) {

	// ---------------------------------------
	// recursively delete the shared wp-content/infinity23 storage folder
	// ---------------------------------------

	if( !function_exists( 'infinity23_danger_zone_remove_storage_folder' ) ) {

		function infinity23_danger_zone_remove_storage_folder() {

			$root_folder = infinity23_get_storage_root_folder();

			if( !file_exists( $root_folder ) || !is_dir( $root_folder ) ) {
				return;
			} // if( !file_exists( $root_folder ) || !is_dir( $root_folder ) ) {

			global $wp_filesystem;

			if( !function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			} // if( !function_exists( 'WP_Filesystem' ) ) {

			WP_Filesystem();

			// WP_Filesystem() leaves $wp_filesystem null on hosts that ask for
			// ftp credentials, and RecursiveDirectoryIterator throws on a folder
			// it cannot read - either one used to abort the whole reset
			if( empty( $wp_filesystem ) ) {
				return;
			} // if( empty( $wp_filesystem ) ) {

			try {

				$items = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator( $root_folder, RecursiveDirectoryIterator::SKIP_DOTS ),
					RecursiveIteratorIterator::CHILD_FIRST
				);

				foreach( $items as $item ) {

					if( $item->isDir() ) {
						$wp_filesystem->rmdir( $item->getRealPath() );
					} else {
						wp_delete_file( $item->getRealPath() );
					} // if( $item->isDir() ) {

				} // foreach( $items as $item ) {

			} catch( Exception $infinity23_storage_folder_exception ) {
				return;
			} // try {

			$wp_filesystem->rmdir( $root_folder );

		} // function infinity23_danger_zone_remove_storage_folder() {

	} // if( !function_exists( 'infinity23_danger_zone_remove_storage_folder' ) ) {

?>
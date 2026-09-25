<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Tag Manager Upgrade - Detection
//
// Answers the two questions the install process asks before it chooses which
// install path to take:
//
//   1. is the old standalone Tag Manager plugin's data on this site
//   2. is this a genuine first install of Infinity23, with no trace of this
//      plugin anywhere
//
// Both have to be true for the upgrade path to run
// ===========================================================================

	// --------------------------------------------
	// every old wp_options row name the standalone plugin ever created
	// --------------------------------------------

	if( !defined( 'INFINITY23_TM_UPGRADE_LEGACY_OPTION' ) ) {
		define( 'INFINITY23_TM_UPGRADE_LEGACY_OPTION', 'yydev_tagmanager_settings' );
	} // if( !defined( 'INFINITY23_TM_UPGRADE_LEGACY_OPTION' ) ) {

	if( !defined( 'INFINITY23_TM_UPGRADE_LEGACY_VERSION_OPTION' ) ) {
		define( 'INFINITY23_TM_UPGRADE_LEGACY_VERSION_OPTION', 'yydev_tag_manager_version' );
	} // if( !defined( 'INFINITY23_TM_UPGRADE_LEGACY_VERSION_OPTION' ) ) {

	// --------------------------------------------
	// the wp_options row that remembers this site already went through the
	// install process once.
	//
	// It lives in wp_options and not in the plugin's own table on purpose -
	// the plugin's table gets truncated by a Danger Zone reset and can be
	// deleted by hand, and neither of those may ever turn a reinstall back
	// into a "first install" that replays the upgrade and the notice
	// --------------------------------------------

	if( !defined( 'INFINITY23_TM_UPGRADE_MARKER_OPTION' ) ) {
		define( 'INFINITY23_TM_UPGRADE_MARKER_OPTION', 'infinity23_install_decision' );
	} // if( !defined( 'INFINITY23_TM_UPGRADE_MARKER_OPTION' ) ) {

// ===========================================================================
// Is the old plugin's data here
// ===========================================================================

	// --------------------------------------------
	// the old plugin's own database table name
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_legacy_table' ) ) {

		function infinity23_tm_upgrade_legacy_table() {

			global $wpdb;

			return $wpdb->prefix . 'yydev_tagmanager';

		} // function infinity23_tm_upgrade_legacy_table() {

	} // if( !function_exists( 'infinity23_tm_upgrade_legacy_table' ) ) {

	// --------------------------------------------
	// does the old plugin's table still exist
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_legacy_table_exists' ) ) {

		function infinity23_tm_upgrade_legacy_table_exists() {

			global $wpdb;

			$infinity23_tm_upgrade_table = infinity23_tm_upgrade_legacy_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- reading another plugin's custom table, no WP core API for it; caching not yet implemented, tracked for later
			return ( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $infinity23_tm_upgrade_table ) . "'" ) === $infinity23_tm_upgrade_table );

		} // function infinity23_tm_upgrade_legacy_table_exists() {

	} // if( !function_exists( 'infinity23_tm_upgrade_legacy_table_exists' ) ) {

	// --------------------------------------------
	// was the old standalone Tag Manager plugin ever installed on this site.
	// Checks every trace it could have left behind, so a site that only kept
	// part of the old data is still recognised as an upgrade
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_legacy_detected' ) ) {

		function infinity23_tm_upgrade_legacy_detected() {

			// the main settings row of the old plugin
			if( get_option( INFINITY23_TM_UPGRADE_LEGACY_OPTION ) !== false ) {
				return true;
			} // if( get_option( INFINITY23_TM_UPGRADE_LEGACY_OPTION ) !== false ) {

			// the version row the old plugin wrote on every load
			if( get_option( INFINITY23_TM_UPGRADE_LEGACY_VERSION_OPTION ) !== false ) {
				return true;
			} // if( get_option( INFINITY23_TM_UPGRADE_LEGACY_VERSION_OPTION ) !== false ) {

			// the two standalone rows versions older than 2.1 used
			if( get_option( 'yydev_tag_mangage_wp_body_open' ) !== false ) {
				return true;
			} // if( get_option( 'yydev_tag_mangage_wp_body_open' ) !== false ) {

			if( get_option( 'yydev_tagmanager_main_menu' ) !== false ) {
				return true;
			} // if( get_option( 'yydev_tagmanager_main_menu' ) !== false ) {

			// and finally the old plugin's own tags table
			if( infinity23_tm_upgrade_legacy_table_exists() ) {
				return true;
			} // if( infinity23_tm_upgrade_legacy_table_exists() ) {

			return false;

		} // function infinity23_tm_upgrade_legacy_detected() {

	} // if( !function_exists( 'infinity23_tm_upgrade_legacy_detected' ) ) {

// ===========================================================================
// Is this a genuine first install of Infinity23
//
// The upgrade process and its notice are only ever allowed on a site where
// Infinity23 has never existed before. If ANY trace of this plugin is already
// on the site - its options table, one of its module tables, its storage
// folder, or the marker row - then this is a reactivation or a reinstall, and
// the upgrade must stay silent.
//
// Without this, deactivating and activating the plugin again on a site that
// still has the old Tag Manager data would replay the whole upgrade and bring
// the notice back every single time
// ===========================================================================

	// --------------------------------------------
	// is there any trace at all of this plugin having run on this site before
	//
	// $infinity23_tm_upgrade_skip_main_table is passed as true by install.php,
	// which asks this question at the exact moment it is creating the main
	// options table - that table existing is what is being decided there, so
	// it cannot count as a trace of a previous install
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_previous_install_found' ) ) {

		function infinity23_tm_upgrade_previous_install_found( $infinity23_tm_upgrade_skip_main_table = false ) {

			global $wpdb, $infinity23_table_name;

			// --------------------------------------------
			// the marker row in wp_options. This is the strongest signal - it
			// sits outside the plugin's own table on purpose, so deleting that
			// table, or running a Danger Zone reset, still leaves it behind
			// --------------------------------------------

			if( get_option( INFINITY23_TM_UPGRADE_MARKER_OPTION ) !== false ) {
				return true;
			} // if( get_option( INFINITY23_TM_UPGRADE_MARKER_OPTION ) !== false ) {

			// --------------------------------------------
			// every table this plugin can create
			// --------------------------------------------

			$infinity23_tm_upgrade_trace_tables = array(
				$wpdb->prefix . 'infinity23_notes',
				$wpdb->prefix . 'infinity23_redirects',
				$wpdb->prefix . 'infinity23_redirects_log',
				$wpdb->prefix . 'infinity23_folders',
				$wpdb->prefix . 'infinity23_email_log',
			);

			if( $infinity23_tm_upgrade_skip_main_table !== true ) {
				$infinity23_tm_upgrade_trace_tables[] = $infinity23_table_name;
			} // if( $infinity23_tm_upgrade_skip_main_table !== true ) {

			foreach( $infinity23_tm_upgrade_trace_tables as $infinity23_tm_upgrade_trace_table ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- schema check on this plugin's own tables, no WP core API; caching not yet implemented, tracked for later
				if( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $infinity23_tm_upgrade_trace_table ) . "'" ) === $infinity23_tm_upgrade_trace_table ) {
					return true;
				} // if( $wpdb->get_var(

			} // foreach( $infinity23_tm_upgrade_trace_tables as $infinity23_tm_upgrade_trace_table ) {

			// --------------------------------------------
			// the shared storage folder any module writes generated files into
			// --------------------------------------------

			if( function_exists( 'infinity23_get_storage_root_folder' ) ) {

				$infinity23_tm_upgrade_trace_folder = infinity23_get_storage_root_folder();

				if( !empty( $infinity23_tm_upgrade_trace_folder ) && file_exists( $infinity23_tm_upgrade_trace_folder ) && is_dir( $infinity23_tm_upgrade_trace_folder ) ) {
					return true;
				} // if( !empty( $infinity23_tm_upgrade_trace_folder ) && file_exists(

			} // if( function_exists( 'infinity23_get_storage_root_folder' ) ) {

			return false;

		} // function infinity23_tm_upgrade_previous_install_found( $infinity23_tm_upgrade_skip_main_table = false ) {

	} // if( !function_exists( 'infinity23_tm_upgrade_previous_install_found' ) ) {

	// --------------------------------------------
	// write the marker row that says this site has already been through the
	// install process once, so the upgrade path can never run again.
	//
	// Called by BOTH install processes - a fresh install writes
	// 'fresh_install' and an upgrade writes 'tag_manager_upgrade', and either
	// value stops every later check
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_mark_install_decision' ) ) {

		function infinity23_tm_upgrade_mark_install_decision( $infinity23_tm_upgrade_decision ) {

			update_option( INFINITY23_TM_UPGRADE_MARKER_OPTION, $infinity23_tm_upgrade_decision );

		} // function infinity23_tm_upgrade_mark_install_decision( $infinity23_tm_upgrade_decision ) {

	} // if( !function_exists( 'infinity23_tm_upgrade_mark_install_decision' ) ) {

?>

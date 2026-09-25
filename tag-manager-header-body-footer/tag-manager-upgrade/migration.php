<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Tag Manager Upgrade - Migration
//
// Writes everything the old standalone plugin saved into the Infinity23 Tag
// Manager module, sets the Include / Exclude state an upgrade should have,
// and flags the upgrade notice
// ===========================================================================

	// --------------------------------------------
	// copy every old Tag Manager option into the Infinity23 Tag Manager
	// module, and keep a full untouched backup of the old data inside the
	// plugin's own options table as well
	//
	// returns true when the settings were actually imported
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_migrate_data' ) ) {

		function infinity23_tm_upgrade_migrate_data() {

			// ----------------------------------------
			// read everything the old plugin left behind - read only, the old
			// rows and the old table are never changed or removed
			// ----------------------------------------

			$infinity23_tm_upgrade_raw_settings = get_option( INFINITY23_TM_UPGRADE_LEGACY_OPTION, '' );
			$infinity23_tm_upgrade_settings = infinity23_tm_upgrade_parse_settings( $infinity23_tm_upgrade_raw_settings );

			$infinity23_tm_upgrade_table_rows = infinity23_tm_upgrade_get_legacy_table_rows();

			$infinity23_tm_upgrade_head_raw = '';
			$infinity23_tm_upgrade_body_raw = '';
			$infinity23_tm_upgrade_footer_raw = '';

			foreach( $infinity23_tm_upgrade_table_rows as $infinity23_tm_upgrade_table_row ) {

				// the old plugin's front end only ever printed the page_id = 0 rows
				if( intval( $infinity23_tm_upgrade_table_row['page_id'] ) !== 0 ) { continue; }

				if( $infinity23_tm_upgrade_table_row['tag_type'] === 'head' ) {
					$infinity23_tm_upgrade_head_raw = $infinity23_tm_upgrade_table_row['tag_code'];
				} elseif( $infinity23_tm_upgrade_table_row['tag_type'] === 'body' ) {
					$infinity23_tm_upgrade_body_raw = $infinity23_tm_upgrade_table_row['tag_code'];
				} elseif( $infinity23_tm_upgrade_table_row['tag_type'] === 'footer' ) {
					$infinity23_tm_upgrade_footer_raw = $infinity23_tm_upgrade_table_row['tag_code'];
				} // if( $infinity23_tm_upgrade_table_row['tag_type'] === 'head' ) {

			} // foreach( $infinity23_tm_upgrade_table_rows as $infinity23_tm_upgrade_table_row ) {

			// ----------------------------------------
			// the two old rows that were written outside the settings string
			// by versions older than 2.1 - the old install.php read these as
			// its starting values, so they are the fallback here too
			// ----------------------------------------

			$infinity23_tm_upgrade_old_body_open_option = get_option( 'yydev_tag_mangage_wp_body_open' );
			$infinity23_tm_upgrade_old_menu_option = get_option( 'yydev_tagmanager_main_menu' );

			$infinity23_tm_upgrade_body_open_fallback = ( $infinity23_tm_upgrade_old_body_open_option !== false ) ? intval( $infinity23_tm_upgrade_old_body_open_option ) : 1;
			$infinity23_tm_upgrade_menu_fallback = ( $infinity23_tm_upgrade_old_menu_option !== false ) ? intval( $infinity23_tm_upgrade_old_menu_option ) : 0;

			// ----------------------------------------
			// always keep a full untouched copy of the old data inside the
			// plugin's own table, including the two old settings Infinity23
			// has no matching field for, so nothing is ever unrecoverable
			// ----------------------------------------

			$infinity23_tm_upgrade_unused_fields = array(
				'no_lazy_load_wait_pages' => infinity23_tm_upgrade_legacy_value( 'no_lazy_load_wait_pages', $infinity23_tm_upgrade_settings, '' ),
				'add_plugin_to_settings'  => intval( infinity23_tm_upgrade_legacy_value( 'add_plugin_to_settings', $infinity23_tm_upgrade_settings, $infinity23_tm_upgrade_menu_fallback ) ),
			);

			$infinity23_tm_upgrade_standalone_options = array(
				'yydev_tag_manager_version'      => get_option( INFINITY23_TM_UPGRADE_LEGACY_VERSION_OPTION, '' ),
				'yydev_tag_mangage_wp_body_open' => ( $infinity23_tm_upgrade_old_body_open_option !== false ) ? $infinity23_tm_upgrade_old_body_open_option : '',
				'yydev_tagmanager_main_menu'     => ( $infinity23_tm_upgrade_old_menu_option !== false ) ? $infinity23_tm_upgrade_old_menu_option : '',
			);

			infinity23_update_option(
				'tag_manager_legacy_backup',
				infinity23_convert_array_to_json( $infinity23_tm_upgrade_settings ),
				$infinity23_tm_upgrade_raw_settings,
				$infinity23_tm_upgrade_head_raw,
				$infinity23_tm_upgrade_body_raw,
				$infinity23_tm_upgrade_footer_raw,
				infinity23_convert_array_to_json( $infinity23_tm_upgrade_unused_fields ),
				infinity23_convert_array_to_json( $infinity23_tm_upgrade_standalone_options ),
				infinity23_convert_array_to_json( $infinity23_tm_upgrade_table_rows ),
				NULL,
				NULL,
				NULL,
				'legacy_backup'
			);

			// ----------------------------------------
			// if the Infinity23 Tag Manager module was already saved on this
			// site then it already holds the admin's own settings, and those
			// win - the backup above still keeps every old value
			// ----------------------------------------

			global $infinity23_options_cache;

			if( isset( $infinity23_options_cache['tag_manager_settings'] ) ) {
				return false;
			} // if( isset( $infinity23_options_cache['tag_manager_settings'] ) ) {

			// ----------------------------------------
			// build the Infinity23 settings array - every field the module
			// saves, in the same order its own save handler writes them
			// ----------------------------------------

			$infinity23_data_array = array();

			$infinity23_data_array['google_analytics_id'] = sanitize_text_field( stripslashes_deep( infinity23_tm_upgrade_legacy_value( 'google_analytics_id', $infinity23_tm_upgrade_settings, '' ) ) );
			$infinity23_data_array['facebook_pixel_id'] = sanitize_text_field( stripslashes_deep( infinity23_tm_upgrade_legacy_value( 'facebook_pixel_id', $infinity23_tm_upgrade_settings, '' ) ) );
			$infinity23_data_array['google_tag_manager_id'] = sanitize_text_field( stripslashes_deep( infinity23_tm_upgrade_legacy_value( 'google_tag_manager_id', $infinity23_tm_upgrade_settings, '' ) ) );
			$infinity23_data_array['yandex_metrika_id'] = sanitize_text_field( stripslashes_deep( infinity23_tm_upgrade_legacy_value( 'yandex_metrika_id', $infinity23_tm_upgrade_settings, '' ) ) );

			// the old default was 5000 milliseconds - a site that never saved
			// the field keeps that exact behaviour instead of jumping to the
			// Infinity23 default
			$infinity23_data_array['lazy_load_time'] = intval( infinity23_tm_upgrade_legacy_value( 'lazy_load_time', $infinity23_tm_upgrade_settings, 5000 ) );

			$infinity23_data_array['remove_custom_lazy_load_js_on_elementor'] = intval( infinity23_tm_upgrade_legacy_value( 'remove_custom_lazy_load_js_on_elementor', $infinity23_tm_upgrade_settings, 0 ) );

			// ----------------------------------------
			// the exclude settings use the exact same values in both plugins,
			// they are only validated here so a broken old row cannot write a
			// value the module does not know
			// ----------------------------------------

			$infinity23_data_array['exclude_users'] = sanitize_text_field( infinity23_tm_upgrade_legacy_value( 'exclude_users', $infinity23_tm_upgrade_settings, 'none' ) );

			if( !in_array( $infinity23_data_array['exclude_users'], array( 'none', 'exclude_admin', 'exclude_all_users' ), true ) ) {
				$infinity23_data_array['exclude_users'] = 'none';
			} // if( !in_array(

			$infinity23_data_array['exclude_ids'] = sanitize_text_field( stripslashes_deep( infinity23_tm_upgrade_legacy_value( 'exclude_ids', $infinity23_tm_upgrade_settings, '' ) ) );

			$infinity23_data_array['exclude_option'] = sanitize_text_field( infinity23_tm_upgrade_legacy_value( 'exclude_option', $infinity23_tm_upgrade_settings, 'none' ) );

			if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {
				$infinity23_data_array['exclude_option'] = 'none';
			} // if( !in_array(

			// ----------------------------------------
			// the old plugin stored this as 1 / 0, Infinity23 stores the name
			// of the loading method - 1 was the wp_body_open action, 0 was the
			// fallback that scans the page output and inserts the code itself
			// ----------------------------------------

			$infinity23_tm_upgrade_wp_body_open = intval( infinity23_tm_upgrade_legacy_value( 'wp_body_open', $infinity23_tm_upgrade_settings, $infinity23_tm_upgrade_body_open_fallback ) );
			$infinity23_data_array['wp_body_open'] = ( $infinity23_tm_upgrade_wp_body_open === 1 ) ? 'wp_body_open' : 'footer_fallback';

			// ----------------------------------------
			// fields the old plugin never had - they take the module's own
			// default so the page renders the same as a normal install
			// ----------------------------------------

			include( dirname(__FILE__) . "/../essential/tag-manager/default-values.php" );

			$infinity23_data_array['adjustable_textarea_height'] = intval( $infinity23_defaults['adjustable_textarea_height']['value'] );
			$infinity23_data_array['tag_manager_hide_menu'] = intval( $infinity23_defaults['tag_manager_hide_menu']['value'] );

			// ----------------------------------------
			// the repeating code boxes - these live in data1-data5 and not in
			// the settings json, the same way the module's own save writes them
			// ----------------------------------------

			$infinity23_head_tags = infinity23_tm_upgrade_split_rows( $infinity23_tm_upgrade_head_raw );
			$infinity23_body_tags = infinity23_tm_upgrade_split_rows( $infinity23_tm_upgrade_body_raw );
			$infinity23_footer_tags = infinity23_tm_upgrade_split_rows( $infinity23_tm_upgrade_footer_raw );

			$infinity23_custom_lazy_load_js = infinity23_tm_upgrade_split_rows( infinity23_tm_upgrade_legacy_value( 'custom_lazy_load_js', $infinity23_tm_upgrade_settings, '' ) );

			// the old plugin kept the notes box as one single value, the module
			// keeps it as an array of boxes
			$infinity23_storage_notes = array();
			$infinity23_tm_upgrade_notes = trim( stripslashes_deep( infinity23_tm_upgrade_legacy_value( 'save_notes', $infinity23_tm_upgrade_settings, '' ) ) );

			if( $infinity23_tm_upgrade_notes !== '' ) {
				$infinity23_storage_notes[] = $infinity23_tm_upgrade_notes;
			} // if( $infinity23_tm_upgrade_notes !== '' ) {

			// ----------------------------------------
			// write it all into the module's own option row
			// ----------------------------------------

			infinity23_update_option(
				'tag_manager_settings',
				infinity23_convert_array_to_json( $infinity23_data_array ),
				infinity23_convert_array_to_json( $infinity23_head_tags ),
				infinity23_convert_array_to_json( $infinity23_body_tags ),
				infinity23_convert_array_to_json( $infinity23_footer_tags ),
				infinity23_convert_array_to_json( $infinity23_custom_lazy_load_js ),
				infinity23_convert_array_to_json( $infinity23_storage_notes )
			);

			return true;

		} // function infinity23_tm_upgrade_migrate_data() {

	} // if( !function_exists( 'infinity23_tm_upgrade_migrate_data' ) ) {

// ===========================================================================
// Include / Exclude state for an upgrade
// ===========================================================================

	// --------------------------------------------
	// an upgrade from the old standalone plugin must not silently switch on
	// every module a fresh install turns on. Only Tag Manager is left open,
	// every other module starts off and waits for the admin to choose it on
	// the Include / Exclude page
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_activate_tag_manager_only' ) ) {

		function infinity23_tm_upgrade_activate_tag_manager_only() {

			include_once( dirname(__FILE__) . '/../essential/loader.php' );

			global $infinity23_essential_modules_array;

			if( empty( $infinity23_essential_modules_array ) ) { return; }

			$infinity23_tm_upgrade_ie_array = array();

			foreach( $infinity23_essential_modules_array as $infinity23_tm_upgrade_module_data ) {

				if( empty( $infinity23_tm_upgrade_module_data['include_exclude_data_key'] ) ) { continue; }

				$infinity23_tm_upgrade_ie_key = $infinity23_tm_upgrade_module_data['include_exclude_data_key'];
				$infinity23_tm_upgrade_ie_array[ $infinity23_tm_upgrade_ie_key ] = ( $infinity23_tm_upgrade_ie_key === 'tag_manager' ) ? 1 : 0;

			} // foreach( $infinity23_essential_modules_array as $infinity23_tm_upgrade_module_data ) {

			infinity23_update_option( 'include_exclude_plugins', infinity23_convert_array_to_json( $infinity23_tm_upgrade_ie_array ) );

			// keep every module's own Activate toggle equal to what was just written here
			infinity23_sync_include_exclude_to_activation( $infinity23_tm_upgrade_ie_array );

		} // function infinity23_tm_upgrade_activate_tag_manager_only() {

	} // if( !function_exists( 'infinity23_tm_upgrade_activate_tag_manager_only' ) ) {

// ===========================================================================
// Notice flag
// ===========================================================================

	// --------------------------------------------
	// turn on the "the Tag Manager plugin just became Infinity23" notice, and
	// switch off the normal new install welcome notice - this site is not a
	// new install and should not be welcomed as one
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_flag_notice' ) ) {

		function infinity23_tm_upgrade_flag_notice() {

			infinity23_update_option( 'tag_manager_upgrade_pending', 1 );

			// written straight to the option row the notices engine reads,
			// because notice-functions.php is not loaded this early
			infinity23_update_option( 'onboarding_notice', 'stop', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'notice_settings' );

		} // function infinity23_tm_upgrade_flag_notice() {

	} // if( !function_exists( 'infinity23_tm_upgrade_flag_notice' ) ) {

?>

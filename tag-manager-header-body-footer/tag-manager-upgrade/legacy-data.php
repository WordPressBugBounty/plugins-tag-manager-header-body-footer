<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Tag Manager Upgrade - Reading the old plugin's data
//
// Everything in this file is read only. The old wp_options rows and the old
// {prefix}yydev_tagmanager table are never changed, renamed or removed
// ===========================================================================

	// --------------------------------------------
	// the old plugin saved all of its settings in one wp_options string built
	// as "####key1####key2***####value1####value2". This turns that string
	// back into an array, using the same splitting the old plugin used, so the
	// values read here are exactly the values the old plugin was using
	//
	// the only difference on purpose - the old plugin took only the part
	// between the first and the second "***", which silently dropped every
	// value that came after a value containing "***" (a real possibility in
	// custom javascript). Here the value side is joined back together, so
	// those values are recovered instead of lost
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_parse_settings' ) ) {

		function infinity23_tm_upgrade_parse_settings( $infinity23_tm_upgrade_raw_string ) {

			$infinity23_tm_upgrade_settings = array();

			if( empty( $infinity23_tm_upgrade_raw_string ) || !is_string( $infinity23_tm_upgrade_raw_string ) ) {
				return $infinity23_tm_upgrade_settings;
			} // if( empty( $infinity23_tm_upgrade_raw_string ) || !is_string( $infinity23_tm_upgrade_raw_string ) ) {

			$infinity23_tm_upgrade_parts = explode( '***', $infinity23_tm_upgrade_raw_string );

			if( count( $infinity23_tm_upgrade_parts ) < 2 ) {
				return $infinity23_tm_upgrade_settings;
			} // if( count( $infinity23_tm_upgrade_parts ) < 2 ) {

			$infinity23_tm_upgrade_keys_string = array_shift( $infinity23_tm_upgrade_parts );
			$infinity23_tm_upgrade_values_string = implode( '***', $infinity23_tm_upgrade_parts );

			$infinity23_tm_upgrade_keys = explode( '####', $infinity23_tm_upgrade_keys_string );
			$infinity23_tm_upgrade_values = explode( '####', $infinity23_tm_upgrade_values_string );

			foreach( $infinity23_tm_upgrade_keys as $infinity23_tm_upgrade_index => $infinity23_tm_upgrade_key ) {

				$infinity23_tm_upgrade_key = trim( $infinity23_tm_upgrade_key );

				// the string always starts with "####" so the first piece is empty
				if( $infinity23_tm_upgrade_key === '' ) { continue; }

				$infinity23_tm_upgrade_settings[ $infinity23_tm_upgrade_key ] = isset( $infinity23_tm_upgrade_values[ $infinity23_tm_upgrade_index ] ) ? $infinity23_tm_upgrade_values[ $infinity23_tm_upgrade_index ] : '';

			} // foreach( $infinity23_tm_upgrade_keys as $infinity23_tm_upgrade_index => $infinity23_tm_upgrade_key ) {

			return $infinity23_tm_upgrade_settings;

		} // function infinity23_tm_upgrade_parse_settings( $infinity23_tm_upgrade_raw_string ) {

	} // if( !function_exists( 'infinity23_tm_upgrade_parse_settings' ) ) {

	// --------------------------------------------
	// read one old settings value, falling back to the value given when the
	// key was never saved on this site (older versions of the old plugin did
	// not have every field)
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_legacy_value' ) ) {

		function infinity23_tm_upgrade_legacy_value( $infinity23_tm_upgrade_key, $infinity23_tm_upgrade_settings, $infinity23_tm_upgrade_fallback = '' ) {

			if( !isset( $infinity23_tm_upgrade_settings[ $infinity23_tm_upgrade_key ] ) ) {
				return $infinity23_tm_upgrade_fallback;
			} // if( !isset( $infinity23_tm_upgrade_settings[ $infinity23_tm_upgrade_key ] ) ) {

			$infinity23_tm_upgrade_value = $infinity23_tm_upgrade_settings[ $infinity23_tm_upgrade_key ];

			if( $infinity23_tm_upgrade_value === '' ) {
				return $infinity23_tm_upgrade_fallback;
			} // if( $infinity23_tm_upgrade_value === '' ) {

			return $infinity23_tm_upgrade_value;

		} // function infinity23_tm_upgrade_legacy_value( $infinity23_tm_upgrade_key, $infinity23_tm_upgrade_settings, $infinity23_tm_upgrade_fallback = '' ) {

	} // if( !function_exists( 'infinity23_tm_upgrade_legacy_value' ) ) {

	// --------------------------------------------
	// the old plugin joined every repeating code box into one value separated
	// by "###^^^", and saved it straight from $_POST, so the saved value is
	// still slashed. This splits it back into the array of rows Infinity23
	// uses and removes those slashes, which is exactly what the old plugin
	// did when it printed the code to the page
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_split_rows' ) ) {

		function infinity23_tm_upgrade_split_rows( $infinity23_tm_upgrade_raw_code ) {

			$infinity23_tm_upgrade_rows = array();

			if( $infinity23_tm_upgrade_raw_code === null || !is_string( $infinity23_tm_upgrade_raw_code ) || trim( $infinity23_tm_upgrade_raw_code ) === '' ) {
				return $infinity23_tm_upgrade_rows;
			} // if( $infinity23_tm_upgrade_raw_code === null ||

			$infinity23_tm_upgrade_pieces = explode( '###^^^', $infinity23_tm_upgrade_raw_code );

			foreach( $infinity23_tm_upgrade_pieces as $infinity23_tm_upgrade_piece ) {

				$infinity23_tm_upgrade_piece = trim( stripslashes_deep( $infinity23_tm_upgrade_piece ) );

				if( $infinity23_tm_upgrade_piece !== '' ) {
					$infinity23_tm_upgrade_rows[] = $infinity23_tm_upgrade_piece;
				} // if( $infinity23_tm_upgrade_piece !== '' ) {

			} // foreach( $infinity23_tm_upgrade_pieces as $infinity23_tm_upgrade_piece ) {

			return $infinity23_tm_upgrade_rows;

		} // function infinity23_tm_upgrade_split_rows( $infinity23_tm_upgrade_raw_code ) {

	} // if( !function_exists( 'infinity23_tm_upgrade_split_rows' ) ) {

	// --------------------------------------------
	// read every row of the old plugin's tags table. The old plugin only ever
	// used page_id = 0, but every row is returned here so nothing on a site
	// that wrote per page rows is missed, and the raw rows can be backed up
	// --------------------------------------------

	if( !function_exists( 'infinity23_tm_upgrade_get_legacy_table_rows' ) ) {

		function infinity23_tm_upgrade_get_legacy_table_rows() {

			global $wpdb;

			if( !infinity23_tm_upgrade_legacy_table_exists() ) {
				return array();
			} // if( !infinity23_tm_upgrade_legacy_table_exists() ) {

			$infinity23_tm_upgrade_table = infinity23_tm_upgrade_legacy_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- reading another plugin's custom table, no WP core API for it; caching not yet implemented, tracked for later
			$infinity23_tm_upgrade_rows = $wpdb->get_results( "SELECT `id`, `page_id`, `tag_type`, `tag_code` FROM `" . esc_sql( $infinity23_tm_upgrade_table ) . "`", ARRAY_A );

			if( empty( $infinity23_tm_upgrade_rows ) ) {
				return array();
			} // if( empty( $infinity23_tm_upgrade_rows ) ) {

			return $infinity23_tm_upgrade_rows;

		} // function infinity23_tm_upgrade_get_legacy_table_rows() {

	} // if( !function_exists( 'infinity23_tm_upgrade_get_legacy_table_rows' ) ) {

?>

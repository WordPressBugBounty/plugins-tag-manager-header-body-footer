<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Cron Management - adjustment tracking storage
	// Every pause and interval-change adjustment is stored as one JSON object
	// inside data1 of the cron_management_settings option row, keyed by
	// adjustment_key - not in a dedicated database table. This follows the
	// same data-slot pattern used by Tag Manager's tag rows.
	// ===========================================================================

	// ---------------------------------------
	// read the full adjustments object from data1
	// ---------------------------------------

	if( !function_exists( 'infinity23_cron_mgmt_get_adjustments_data' ) ) {

		function infinity23_cron_mgmt_get_adjustments_data() {

			return infinity23_get_option_data_array( 'cron_management_settings', 'data1', array() );

		} // function infinity23_cron_mgmt_get_adjustments_data() {

	} // if( !function_exists( 'infinity23_cron_mgmt_get_adjustments_data' ) ) {

	// ---------------------------------------
	// write the full adjustments object back to data1, preserving data2-data10
	// ---------------------------------------

	if( !function_exists( 'infinity23_cron_mgmt_save_adjustments_data' ) ) {

		function infinity23_cron_mgmt_save_adjustments_data( $adjustments_data ) {

			$current_settings_value = infinity23_get_option( 'cron_management_settings' );

			$data2 = infinity23_get_option_data( 'cron_management_settings', 'data2' );
			$data3 = infinity23_get_option_data( 'cron_management_settings', 'data3' );
			$data4 = infinity23_get_option_data( 'cron_management_settings', 'data4' );
			$data5 = infinity23_get_option_data( 'cron_management_settings', 'data5' );
			$data6 = infinity23_get_option_data( 'cron_management_settings', 'data6' );
			$data7 = infinity23_get_option_data( 'cron_management_settings', 'data7' );
			$data8 = infinity23_get_option_data( 'cron_management_settings', 'data8' );
			$data9 = infinity23_get_option_data( 'cron_management_settings', 'data9' );
			$data10 = infinity23_get_option_data( 'cron_management_settings', 'data10' );

			infinity23_update_option( 'cron_management_settings', $current_settings_value, infinity23_convert_array_to_json( $adjustments_data ), $data2, $data3, $data4, $data5, $data6, $data7, $data8, $data9, $data10 );

		} // function infinity23_cron_mgmt_save_adjustments_data( $adjustments_data ) {

	} // if( !function_exists( 'infinity23_cron_mgmt_save_adjustments_data' ) ) {

	// ---------------------------------------
	// convert one entry of the adjustments object into a stdClass shaped
	// exactly like the old table row, so cron-adjustments.php and
	// cron-pause-filter.php never needed to change how they read the result
	// ---------------------------------------

	if( !function_exists( 'infinity23_cron_mgmt_row_to_object' ) ) {

		function infinity23_cron_mgmt_row_to_object( $adjustment_key, $row_data ) {

			$row_object = new stdClass();
			$row_object->adjustment_key = $adjustment_key;
			$row_object->hook = isset( $row_data['hook'] ) ? $row_data['hook'] : '';
			$row_object->args = infinity23_convert_array_to_json( isset( $row_data['args'] ) ? $row_data['args'] : array() );
			$row_object->original_schedule = isset( $row_data['original_schedule'] ) ? $row_data['original_schedule'] : null;
			$row_object->is_paused = isset( $row_data['is_paused'] ) ? intval( $row_data['is_paused'] ) : 0;
			$row_object->time_created_utc = isset( $row_data['time_created_utc'] ) ? $row_data['time_created_utc'] : null;
			$row_object->time_updated_utc = isset( $row_data['time_updated_utc'] ) ? $row_data['time_updated_utc'] : null;

			return $row_object;

		} // function infinity23_cron_mgmt_row_to_object( $adjustment_key, $row_data ) {

	} // if( !function_exists( 'infinity23_cron_mgmt_row_to_object' ) ) {

	// ---------------------------------------
	// row functions
	// ---------------------------------------

	if( !function_exists( 'infinity23_cron_mgmt_db_get_all' ) ) {

		function infinity23_cron_mgmt_db_get_all() {

			$adjustments_data = infinity23_cron_mgmt_get_adjustments_data();
			$rows = array();

			foreach( $adjustments_data as $adjustment_key => $row_data ) {
				$rows[] = infinity23_cron_mgmt_row_to_object( $adjustment_key, $row_data );
			} // foreach( $adjustments_data as $adjustment_key => $row_data ) {

			return $rows;

		} // function infinity23_cron_mgmt_db_get_all() {

	} // if( !function_exists( 'infinity23_cron_mgmt_db_get_all' ) ) {

	if( !function_exists( 'infinity23_cron_mgmt_db_get_by_key' ) ) {

		function infinity23_cron_mgmt_db_get_by_key( $adjustment_key ) {

			$adjustments_data = infinity23_cron_mgmt_get_adjustments_data();

			if( !isset( $adjustments_data[ $adjustment_key ] ) ) {
				return null;
			} // if( !isset( $adjustments_data[ $adjustment_key ] ) ) {

			return infinity23_cron_mgmt_row_to_object( $adjustment_key, $adjustments_data[ $adjustment_key ] );

		} // function infinity23_cron_mgmt_db_get_by_key( $adjustment_key ) {

	} // if( !function_exists( 'infinity23_cron_mgmt_db_get_by_key' ) ) {

	if( !function_exists( 'infinity23_cron_mgmt_db_upsert' ) ) {

		function infinity23_cron_mgmt_db_upsert( $adjustment_key, $hook, $args_json, $original_schedule, $is_paused ) {

			$adjustments_data = infinity23_cron_mgmt_get_adjustments_data();
			$existing = isset( $adjustments_data[ $adjustment_key ] ) ? $adjustments_data[ $adjustment_key ] : array();

			$adjustments_data[ $adjustment_key ] = array(
				'hook' => $hook,
				'args' => infinity23_convert_json_to_array( $args_json ),
				'original_schedule' => $original_schedule,
				'is_paused' => $is_paused ? 1 : 0,
				'time_created_utc' => isset( $existing['time_created_utc'] ) ? $existing['time_created_utc'] : infinity23_current_timestamp(),
				'time_updated_utc' => infinity23_current_timestamp(),
			);

			infinity23_cron_mgmt_save_adjustments_data( $adjustments_data );

		} // function infinity23_cron_mgmt_db_upsert( $adjustment_key, $hook, $args_json, $original_schedule, $is_paused ) {

	} // if( !function_exists( 'infinity23_cron_mgmt_db_upsert' ) ) {

	if( !function_exists( 'infinity23_cron_mgmt_db_delete' ) ) {

		function infinity23_cron_mgmt_db_delete( $adjustment_key ) {

			$adjustments_data = infinity23_cron_mgmt_get_adjustments_data();

			if( isset( $adjustments_data[ $adjustment_key ] ) ) {
				unset( $adjustments_data[ $adjustment_key ] );
				infinity23_cron_mgmt_save_adjustments_data( $adjustments_data );
			} // if( isset( $adjustments_data[ $adjustment_key ] ) ) {

		} // function infinity23_cron_mgmt_db_delete( $adjustment_key ) {

	} // if( !function_exists( 'infinity23_cron_mgmt_db_delete' ) ) {

?>
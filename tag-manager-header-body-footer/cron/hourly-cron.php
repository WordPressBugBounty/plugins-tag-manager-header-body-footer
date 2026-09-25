<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Cron Settings - change values here only, nothing below this block needs editing
// ===========================================================================

	global $infinity23_cron_hook, $infinity23_cron_recurrence, $infinity23_cron_custom_interval_seconds, $infinity23_cron_custom_display_name;

	// the hook name WordPress fires when the cron runs
	$infinity23_cron_hook = 'infinity23_hourly_cron_hook';

	// how often the cron runs - use 'hourly', 'twicedaily', 'daily' (built into WordPress)
	// or 'infinity23_custom' to use the custom interval set below
	$infinity23_cron_recurrence = 'hourly';

	// only used when $infinity23_cron_recurrence is 'infinity23_custom' - interval in seconds
	$infinity23_cron_custom_interval_seconds = 60; // 60 = every minute

	// only used when $infinity23_cron_recurrence is 'infinity23_custom' - label shown in WP Crontrol / cron lists
	$infinity23_cron_custom_display_name = 'Every Minute (Infinity23)';

// ===========================================================================
// Custom schedule - registers 'infinity23_custom' as an available WP Cron interval
// only needed when $infinity23_cron_recurrence above is set to 'infinity23_custom'
// ===========================================================================

	if( !function_exists( 'infinity23_add_cron_schedules' ) ) {

		function infinity23_add_cron_schedules( $schedules ) {

			global $infinity23_cron_custom_interval_seconds, $infinity23_cron_custom_display_name;

			$schedules['infinity23_custom'] = array(
				'interval' => $infinity23_cron_custom_interval_seconds,
				'display'  => $infinity23_cron_custom_display_name,
			);

			return $schedules;

		} // function infinity23_add_cron_schedules( $schedules ) {

	} // if( !function_exists( 'infinity23_add_cron_schedules' ) ) {

	add_filter( 'cron_schedules', 'infinity23_add_cron_schedules' );

// ===========================================================================
// Registration - schedule on activation, clear on deactivation
// ===========================================================================

	if( !function_exists( 'infinity23_schedule_hourly_cron' ) ) {

		function infinity23_schedule_hourly_cron() {

			global $infinity23_cron_hook, $infinity23_cron_recurrence;

			if( !wp_next_scheduled( $infinity23_cron_hook ) ) {
				wp_schedule_event( time(), $infinity23_cron_recurrence, $infinity23_cron_hook );
			} // if( !wp_next_scheduled( $infinity23_cron_hook ) ) {

		} // function infinity23_schedule_hourly_cron() {

	} // if( !function_exists( 'infinity23_schedule_hourly_cron' ) ) {

	if( !function_exists( 'infinity23_clear_hourly_cron' ) ) {

		function infinity23_clear_hourly_cron() {

			global $infinity23_cron_hook;

			$timestamp = wp_next_scheduled( $infinity23_cron_hook );

			if( $timestamp ) {
				wp_unschedule_event( $timestamp, $infinity23_cron_hook );
			} // if( $timestamp ) {

		} // function infinity23_clear_hourly_cron() {

	} // if( !function_exists( 'infinity23_clear_hourly_cron' ) ) {

	register_activation_hook( dirname( dirname( __FILE__ ) ) . "/index.php", 'infinity23_schedule_hourly_cron' );
	register_deactivation_hook( dirname( dirname( __FILE__ ) ) . "/index.php", 'infinity23_clear_hourly_cron' );

// ===========================================================================
// Auto reschedule check - runs on every page load (the 'init' hook)
// Detects when the hook name or recurrence in the Settings block above was
// changed, and automatically clears the old schedule and creates a new one -
// so a plugin reactivation is never needed after changing a setting.
// Also covers the case where the plugin was already active when the cron
// code was first added (register_activation_hook would not have fired).
// ===========================================================================

	if( !function_exists( 'infinity23_maybe_reschedule_hourly_cron' ) ) {

		function infinity23_maybe_reschedule_hourly_cron() {

			global $infinity23_cron_hook, $infinity23_cron_recurrence;

			$saved_signature = infinity23_get_option( 'infinity23_hourly_cron_signature' );
			$current_signature = $infinity23_cron_hook . '|' . $infinity23_cron_recurrence;

			if( $saved_signature !== $current_signature ) {

				// the hook name may have changed too - clear using the old saved hook name if it exists
				if( !empty( $saved_signature ) ) {

					$saved_hook = explode( '|', $saved_signature );
					$saved_hook = isset( $saved_hook[0] ) ? $saved_hook[0] : $infinity23_cron_hook;
					$old_timestamp = wp_next_scheduled( $saved_hook );

					if( $old_timestamp ) {
						wp_unschedule_event( $old_timestamp, $saved_hook );
					} // if( $old_timestamp ) {

				} // if( !empty( $saved_signature ) ) {

				infinity23_schedule_hourly_cron();
				infinity23_update_option( 'infinity23_hourly_cron_signature', $current_signature );

			} elseif( !wp_next_scheduled( $infinity23_cron_hook ) ) {

				infinity23_schedule_hourly_cron();

			} // if( $saved_signature !== $current_signature ) {

		} // function infinity23_maybe_reschedule_hourly_cron() {

	} // if( !function_exists( 'infinity23_maybe_reschedule_hourly_cron' ) ) {

	// check the cron if active only when user is logged in to the system
	if( is_admin() ) {
		add_action( 'init', 'infinity23_maybe_reschedule_hourly_cron' );
	} // if( is_admin() ) {

// ===========================================================================
// Hook connection - connects the scheduled event to the runner function below
// ===========================================================================

	add_action( $infinity23_cron_hook, 'infinity23_run_hourly_cron' );

// ===========================================================================
// Runner - the tasks that execute every time the cron fires
// ===========================================================================

	if( !function_exists( 'infinity23_run_hourly_cron' ) ) {

		function infinity23_run_hourly_cron() {

			global $infinity23_essential_modules_array;

			// ****************************************************************
			// ****************************************************************
			// Insert here the code and actions you want to have when the cron running
			// ****************************************************************
			// ****************************************************************

			// ----------------------------------------
			// clean up old zip files from plugins and themes downloader module
			// ----------------------------------------

			$infinity23_ptd_activate_code = intval( infinity23_get_option( 'plugins_and_themes_downloader_activate_code', 0 ) );

			if( isset( $infinity23_essential_modules_array['plugins_and_themes_downloader'] ) && $infinity23_ptd_activate_code == 1 ) {

				include_once( dirname(__FILE__) . "/../essential/plugins-and-themes-downloader/back-end/cleanup-functions.php" );

				infinity23_ptd_cleanup_all_old_zips();

			} // if( isset( $infinity23_essential_modules_array['plugins_and_themes_downloader'] ) && $infinity23_ptd_activate_code == 1 ) {

		} // function infinity23_run_hourly_cron() {

	} // if( !function_exists( 'infinity23_run_hourly_cron' ) ) {

?>
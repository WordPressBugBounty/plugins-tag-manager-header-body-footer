<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Daily Cron Settings - change values here only, nothing below this block needs editing
// ===========================================================================

	global $infinity23_daily_cron_hook, $infinity23_daily_cron_recurrence, $infinity23_daily_cron_custom_interval_seconds, $infinity23_daily_cron_custom_display_name;

	// the hook name WordPress fires when the cron runs
	$infinity23_daily_cron_hook = 'infinity23_daily_cron_hook';

	// how often the cron runs - use 'hourly', 'twicedaily', 'daily' (built into WordPress)
	// or 'infinity23_daily_custom' to use the custom interval set below
	$infinity23_daily_cron_recurrence = 'daily';

	// only used when $infinity23_daily_cron_recurrence is 'infinity23_daily_custom' - interval in seconds
	$infinity23_daily_cron_custom_interval_seconds = 86400; // 86400 = once a day

	// only used when $infinity23_daily_cron_recurrence is 'infinity23_daily_custom' - label shown in WP Crontrol / cron lists
	$infinity23_daily_cron_custom_display_name = 'Once Daily (Infinity23)';

// ===========================================================================
// Custom schedule - registers 'infinity23_daily_custom' as an available WP Cron interval
// only needed when $infinity23_daily_cron_recurrence above is set to 'infinity23_daily_custom'
// ===========================================================================

	if( !function_exists( 'infinity23_add_daily_cron_schedules' ) ) {

		function infinity23_add_daily_cron_schedules( $schedules ) {

			global $infinity23_daily_cron_custom_interval_seconds, $infinity23_daily_cron_custom_display_name;

			$schedules['infinity23_daily_custom'] = array(
				'interval' => $infinity23_daily_cron_custom_interval_seconds,
				'display'  => $infinity23_daily_cron_custom_display_name,
			);

			return $schedules;

		} // function infinity23_add_daily_cron_schedules( $schedules ) {

	} // if( !function_exists( 'infinity23_add_daily_cron_schedules' ) ) {

	add_filter( 'cron_schedules', 'infinity23_add_daily_cron_schedules' );

// ===========================================================================
// Registration - schedule on activation, clear on deactivation
// ===========================================================================

	if( !function_exists( 'infinity23_schedule_daily_cron' ) ) {

		function infinity23_schedule_daily_cron() {

			global $infinity23_daily_cron_hook, $infinity23_daily_cron_recurrence;

			if( !wp_next_scheduled( $infinity23_daily_cron_hook ) ) {
				wp_schedule_event( time(), $infinity23_daily_cron_recurrence, $infinity23_daily_cron_hook );
			} // if( !wp_next_scheduled( $infinity23_daily_cron_hook ) ) {

		} // function infinity23_schedule_daily_cron() {

	} // if( !function_exists( 'infinity23_schedule_daily_cron' ) ) {

	if( !function_exists( 'infinity23_clear_daily_cron' ) ) {

		function infinity23_clear_daily_cron() {

			global $infinity23_daily_cron_hook;

			$timestamp = wp_next_scheduled( $infinity23_daily_cron_hook );

			if( $timestamp ) {
				wp_unschedule_event( $timestamp, $infinity23_daily_cron_hook );
			} // if( $timestamp ) {

		} // function infinity23_clear_daily_cron() {

	} // if( !function_exists( 'infinity23_clear_daily_cron' ) ) {

	register_activation_hook( dirname( dirname( __FILE__ ) ) . "/index.php", 'infinity23_schedule_daily_cron' );
	register_deactivation_hook( dirname( dirname( __FILE__ ) ) . "/index.php", 'infinity23_clear_daily_cron' );

// ===========================================================================
// Auto reschedule check - runs on every page load (the 'init' hook)
// Detects when the hook name or recurrence in the Settings block above was
// changed, and automatically clears the old schedule and creates a new one -
// so a plugin reactivation is never needed after changing a setting.
// ===========================================================================

	if( !function_exists( 'infinity23_maybe_reschedule_daily_cron' ) ) {

		function infinity23_maybe_reschedule_daily_cron() {

			global $infinity23_daily_cron_hook, $infinity23_daily_cron_recurrence;

			$saved_signature = infinity23_get_option( 'infinity23_daily_cron_signature' );
			$current_signature = $infinity23_daily_cron_hook . '|' . $infinity23_daily_cron_recurrence;

			if( $saved_signature !== $current_signature ) {

				// the hook name may have changed too - clear using the old saved hook name if it exists
				if( !empty( $saved_signature ) ) {

					$saved_hook = explode( '|', $saved_signature );
					$saved_hook = isset( $saved_hook[0] ) ? $saved_hook[0] : $infinity23_daily_cron_hook;
					$old_timestamp = wp_next_scheduled( $saved_hook );

					if( $old_timestamp ) {
						wp_unschedule_event( $old_timestamp, $saved_hook );
					} // if( $old_timestamp ) {

				} // if( !empty( $saved_signature ) ) {

				infinity23_schedule_daily_cron();
				infinity23_update_option( 'infinity23_daily_cron_signature', $current_signature );

			} elseif( !wp_next_scheduled( $infinity23_daily_cron_hook ) ) {

				infinity23_schedule_daily_cron();

			} // if( $saved_signature !== $current_signature ) {

		} // function infinity23_maybe_reschedule_daily_cron() {

	} // if( !function_exists( 'infinity23_maybe_reschedule_daily_cron' ) ) {

	// check the cron if active only when user is logged in to the system
	if( is_admin() ) {
		add_action( 'init', 'infinity23_maybe_reschedule_daily_cron' );
	} // if( is_admin() ) {

// ===========================================================================
// Hook connection - connects the scheduled event to the runner function below
// ===========================================================================

	add_action( $infinity23_daily_cron_hook, 'infinity23_run_daily_cron' );

// ===========================================================================
// Runner - the tasks that execute every time the cron fires
// ===========================================================================

	if( !function_exists( 'infinity23_run_daily_cron' ) ) {

		function infinity23_run_daily_cron() {

			global $infinity23_essential_modules_array;

			// ----------------------------------------
			// clean up old cron log files from cron management module
			// ----------------------------------------

			$infinity23_cron_mgmt_activate_code = intval( infinity23_get_option( 'cron_management_activate_code', 0 ) );

			if( isset( $infinity23_essential_modules_array['cron_management'] ) && $infinity23_cron_mgmt_activate_code == 1 ) {

				include_once( dirname(__FILE__) . "/../essential/cron-management/back-end/cron-log-cleanup.php" );
				include( dirname(__FILE__) . "/../essential/cron-management/default-values.php" );

				$infinity23_cron_mgmt_settings_data = infinity23_convert_json_to_array( infinity23_get_option( 'cron_management_settings' ) );
				$infinity23_cron_mgmt_log_enabled = intval( infinity23_pro_get_data( 'cron_log_enabled', $infinity23_cron_mgmt_settings_data, $infinity23_defaults ) );
				$infinity23_cron_mgmt_retention_days = intval( infinity23_pro_get_data( 'cron_log_retention_days', $infinity23_cron_mgmt_settings_data, $infinity23_defaults ) );

				if( $infinity23_cron_mgmt_log_enabled == 1 ) {
					infinity23_cron_mgmt_cleanup_old_logs( $infinity23_cron_mgmt_retention_days );
				} // if( $infinity23_cron_mgmt_log_enabled == 1 ) {

			} // if( isset( $infinity23_essential_modules_array['cron_management'] ) && $infinity23_cron_mgmt_activate_code == 1 ) {

			// ----------------------------------------
			// clean up old redirect logs from redirect 301/302 module
			// ----------------------------------------

			$infinity23_redirect_activate_code = intval( infinity23_get_option( 'redirect_301_and_302_activate_code', 0 ) );

			if( isset( $infinity23_essential_modules_array['redirect_301_and_302'] ) && $infinity23_redirect_activate_code == 1 ) {

				include_once( dirname(__FILE__) . "/../essential/redirect-301-and-302/back-end/redirect-log-db-functions.php" );
				include_once( dirname(__FILE__) . "/../essential/redirect-301-and-302/back-end/redirect-log-cleanup.php" );
				include( dirname(__FILE__) . "/../essential/redirect-301-and-302/default-values.php" );

				$infinity23_redirect_log_settings_data = infinity23_convert_json_to_array( infinity23_get_option( 'redirect_301_and_302_settings' ) );
				$infinity23_redirect_log_cron_enabled = intval( infinity23_pro_get_data( 'redirect_log_enabled', $infinity23_redirect_log_settings_data, $infinity23_defaults ) );
				$infinity23_redirect_log_cron_retention_days = intval( infinity23_pro_get_data( 'redirect_log_retention_days', $infinity23_redirect_log_settings_data, $infinity23_defaults ) );
				$infinity23_redirect_log_cron_max_count = intval( infinity23_pro_get_data( 'redirect_log_max_count', $infinity23_redirect_log_settings_data, $infinity23_defaults ) );

				if( $infinity23_redirect_log_cron_enabled == 1 ) {
					infinity23_redirect_log_cleanup_old_logs( $infinity23_redirect_log_cron_retention_days, $infinity23_redirect_log_cron_max_count );
				} // if( $infinity23_redirect_log_cron_enabled == 1 ) {

			} // if( isset( $infinity23_essential_modules_array['redirect_301_and_302'] ) && $infinity23_redirect_activate_code == 1 ) {

			// ----------------------------------------
			// clean up old email log rows from email smtp module
			// ----------------------------------------

			$infinity23_email_smtp_activate_code = intval( infinity23_get_option( 'email_smtp_activate_code', 0 ) );

			if( isset( $infinity23_essential_modules_array['email_smtp'] ) && $infinity23_email_smtp_activate_code == 1 ) {

				include_once( dirname(__FILE__) . "/../essential/email-smtp/back-end/email-log-db-functions.php" );
				include( dirname(__FILE__) . "/../essential/email-smtp/default-values.php" );

				$infinity23_email_smtp_settings_data = infinity23_convert_json_to_array( infinity23_get_option( 'email_smtp_settings' ) );
				$infinity23_email_log_enabled = intval( infinity23_pro_get_data( 'email_log_enabled', $infinity23_email_smtp_settings_data, $infinity23_defaults ) );

				if( $infinity23_email_log_enabled == 1 ) {

					$infinity23_email_log_retention_days = intval( infinity23_pro_get_data( 'email_log_retention_days', $infinity23_email_smtp_settings_data, $infinity23_defaults ) );
					$infinity23_email_log_count_limit = intval( infinity23_pro_get_data( 'email_log_count_limit', $infinity23_email_smtp_settings_data, $infinity23_defaults ) );

					infinity23_email_log_cleanup( $infinity23_email_log_retention_days, $infinity23_email_log_count_limit );

				} // if( $infinity23_email_log_enabled == 1 ) {

			} // if( isset( $infinity23_essential_modules_array['email_smtp'] ) && $infinity23_email_smtp_activate_code == 1 ) {

		} // function infinity23_run_daily_cron() {

	} // if( !function_exists( 'infinity23_run_daily_cron' ) ) {

?>
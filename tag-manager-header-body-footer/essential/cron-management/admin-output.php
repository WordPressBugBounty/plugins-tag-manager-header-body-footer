<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// =================================================================================================================
	// Initial plugin load and getting default values
	// =================================================================================================================

	include( dirname(__FILE__) . "/../../include/settings.php" );
	include( dirname(__FILE__) . "/internal-settings.php" );
	include( dirname(__FILE__) . "/back-end/cron-row-helpers.php" );
	infinity23_load_essential_files();

	$infinity23_success_message = '';
	$infinity23_error_message = '';

	if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {
		$infinity23_success_message = "The data was updated successfully";
	} // if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {

	if( isset( $_GET['cron_run'] ) && $_GET['cron_run'] == '1' ) {
		$infinity23_success_message = "The cron event was executed successfully";
	} // if( isset( $_GET['cron_run'] ) && $_GET['cron_run'] == '1' ) {

	if( isset( $_GET['cron_reschedule'] ) && $_GET['cron_reschedule'] == '1' ) {
		$infinity23_success_message = "The cron event was rescheduled to start now";
	} // if( isset( $_GET['cron_reschedule'] ) && $_GET['cron_reschedule'] == '1' ) {

	if( isset( $_GET['cron_paused'] ) && $_GET['cron_paused'] == '1' ) {
		$infinity23_success_message = "The cron event was paused successfully";
	} // if( isset( $_GET['cron_paused'] ) && $_GET['cron_paused'] == '1' ) {

	if( isset( $_GET['cron_paused_and_interval_updated'] ) && $_GET['cron_paused_and_interval_updated'] == '1' ) {
		$infinity23_success_message = "The cron event was paused, and its interval was updated successfully";
	} // if( isset( $_GET['cron_paused_and_interval_updated'] ) && $_GET['cron_paused_and_interval_updated'] == '1' ) {

	if( isset( $_GET['cron_restored'] ) && $_GET['cron_restored'] == '1' ) {
		$infinity23_success_message = "The cron event was restored successfully";
	} // if( isset( $_GET['cron_restored'] ) && $_GET['cron_restored'] == '1' ) {

	if( isset( $_GET['cron_time_updated'] ) && $_GET['cron_time_updated'] == '1' ) {
		$infinity23_success_message = "The cron event's next run time was updated successfully";
	} // if( isset( $_GET['cron_time_updated'] ) && $_GET['cron_time_updated'] == '1' ) {

	if( isset( $_GET['cron_interval_updated'] ) && $_GET['cron_interval_updated'] == '1' ) {
		$infinity23_success_message = "The cron event's interval was updated successfully";
	} // if( isset( $_GET['cron_interval_updated'] ) && $_GET['cron_interval_updated'] == '1' ) {

	if( isset( $_GET['cron_interval_reverted'] ) && $_GET['cron_interval_reverted'] == '1' ) {
		$infinity23_success_message = "The cron event's interval was reverted to its original value";
	} // if( isset( $_GET['cron_interval_reverted'] ) && $_GET['cron_interval_reverted'] == '1' ) {

	if( isset( $_GET['cron_changes_reverted'] ) && $_GET['cron_changes_reverted'] == '1' ) {
		$infinity23_success_message = "The cron event was reverted to its original settings";
	} // if( isset( $_GET['cron_changes_reverted'] ) && $_GET['cron_changes_reverted'] == '1' ) {

	if( isset( $_GET['cron_logs_deleted'] ) && $_GET['cron_logs_deleted'] == '1' ) {
		$infinity23_success_message = "All cron log files were deleted successfully";
	} // if( isset( $_GET['cron_logs_deleted'] ) && $_GET['cron_logs_deleted'] == '1' ) {

	include( dirname(__FILE__) . "/default-values.php" );

	// =================================================================================================================
	// Update the data if the form was submitted
	// =================================================================================================================

	if( isset( $_POST['infinity23_cron_mgmt_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_cron_mgmt_reset_nonce'] ) ), 'infinity23_cron_mgmt_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_cron_mgmt_restore_all_interval_changes();
			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} elseif( isset( $_POST['infinity23_cron_mgmt_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_cron_mgmt_activate_nonce'] ) ), 'infinity23_cron_mgmt_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'cron_management', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "Cron Management was activated successfully";
			} else {
				infinity23_module_deactivate_redirect( $infinity23_module_display_name );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} elseif( isset( $_POST['infinity23_cron_mgmt_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_cron_mgmt_nonce'] ) ), 'infinity23_cron_mgmt_settings_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();

			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
			$infinity23_existing_external_cron_password_encrypted = infinity23_get_data( 'external_cron_password', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['enable_cron_actions'] = infinity23_pro_field_sanitize( 'enable_cron_actions', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['cron_log_enabled'] = infinity23_pro_field_sanitize( 'cron_log_enabled', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['cron_log_retention_days'] = infinity23_pro_field_sanitize( 'cron_log_retention_days', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['external_cron_enabled'] = infinity23_pro_field_sanitize( 'external_cron_enabled', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['disable_wp_built_in_cron'] = infinity23_pro_field_sanitize( 'disable_wp_built_in_cron', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_posted_external_cron_password = infinity23_post_sanitize('external_cron_password', 'text');
			$infinity23_posted_external_cron_password = preg_replace( '/[^a-zA-Z0-9]/', '', $infinity23_posted_external_cron_password );

			$errors = array();

			if( $infinity23_data_array['cron_log_retention_days'] < 1 ) {
				$errors[] = "Cron log retention days must be at least 1";
			} // if( $infinity23_data_array['cron_log_retention_days'] < 1 ) {

			$infinity23_allowed_cron_throttle_values = array( 'default', '3_minutes', '5_minutes', '10_minutes', 'fully_disabled' );

			if( !in_array( $infinity23_data_array['disable_wp_built_in_cron'], $infinity23_allowed_cron_throttle_values, true ) ) {
				$errors[] = "WP-Cron throttle option was invalid";
			} // if( !in_array( $infinity23_data_array['disable_wp_built_in_cron'], $infinity23_allowed_cron_throttle_values, true ) ) {

			if( !empty( $infinity23_posted_external_cron_password ) && strlen( $infinity23_posted_external_cron_password ) < 8 ) {
				$errors[] = "External cron password must be at least 8 characters (letters and numbers only)";
			} // if( !empty( $infinity23_posted_external_cron_password ) && strlen( $infinity23_posted_external_cron_password ) < 8 ) {

			// this field always shows its current plaintext value (unlike a normal
			// password field left blank), so an empty submission means it was
			// accidentally cleared - keep the existing password instead of silently
			// rotating it and breaking an already-configured external cron URL. A
			// brand new password is only generated when none was ever saved before.
			// pro is 'keep' for this field, so a non-pro save never touches it at all.
			if( !infinity23_is_pro() ) {

				$infinity23_data_array['external_cron_password'] = $infinity23_existing_external_cron_password_encrypted;

			} elseif( empty( $infinity23_posted_external_cron_password ) ) {

				if( !empty( $infinity23_existing_external_cron_password_encrypted ) ) {
					$infinity23_data_array['external_cron_password'] = $infinity23_existing_external_cron_password_encrypted;
				} else {
					$infinity23_posted_external_cron_password = wp_generate_password( 32, false );
					$infinity23_data_array['external_cron_password'] = infinity23_encrypt_value( $infinity23_posted_external_cron_password, 'cron_management_external_password' );
				} // if( !empty( $infinity23_existing_external_cron_password_encrypted ) ) {

			} else {
				$infinity23_data_array['external_cron_password'] = infinity23_encrypt_value( $infinity23_posted_external_cron_password, 'cron_management_external_password' );
			} // if( !infinity23_is_pro() ) {

			$infinity23_data_array['cron_management_hide_menu'] = infinity23_pro_field_sanitize( 'cron_management_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			if( empty( $errors ) ) {

				infinity23_update_option_keep_data( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
				$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-cron-management', 'saved' => '1' ), admin_url('admin.php') );
				infinity23_safe_redirect( $infinity23_redirect_url );

			} else {
				$infinity23_error_message = $errors;
			} // if( empty( $errors ) ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_cron_mgmt_reset_nonce'] ) ) {

	// =================================================================================================================
	// Load saved values
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_cron_log_enabled = intval( infinity23_pro_get_data( 'cron_log_enabled', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_cron_log_retention_days = intval( infinity23_pro_get_data( 'cron_log_retention_days', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_enable_cron_actions = intval( infinity23_pro_get_data( 'enable_cron_actions', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_external_cron_enabled = intval( infinity23_pro_get_data( 'external_cron_enabled', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_disable_wp_built_in_cron = infinity23_pro_get_data( 'disable_wp_built_in_cron', $infinity23_data_array, $infinity23_defaults );

	// a site that already had "Fully disable" saved before this checkbox
	// existed keeps its password/URL visible instead of getting silently
	// hidden behind the new (default off) checkbox - the throttle options
	// (3/5/10 minutes) never depended on this and are not affected
	if( $infinity23_external_cron_enabled != 1 && $infinity23_disable_wp_built_in_cron === 'fully_disabled' ) {
		$infinity23_external_cron_enabled = 1;
	} // if( $infinity23_external_cron_enabled != 1 && $infinity23_disable_wp_built_in_cron === 'fully_disabled' ) {

	$infinity23_external_cron_password_encrypted = infinity23_pro_get_data( 'external_cron_password', $infinity23_data_array, $infinity23_defaults );
	$infinity23_external_cron_password = infinity23_decrypt_value( $infinity23_external_cron_password_encrypted, 'cron_management_external_password' );
	$infinity23_external_cron_password_decrypt_failed = false;
	$infinity23_cron_management_hide_menu = intval( infinity23_pro_get_data( 'cron_management_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

	if( $infinity23_external_cron_enabled == 1 && empty( $infinity23_external_cron_password ) ) {

		if( empty( $infinity23_external_cron_password_encrypted ) ) {

			// never configured yet - safe to generate the first password automatically
			$infinity23_external_cron_password = wp_generate_password( 32, false );
			infinity23_update_option_keep_data( $infinity23_settings_array_db_name, infinity23_convert_array_to_json( array_merge( $infinity23_data_array, array( 'external_cron_password' => infinity23_encrypt_value( $infinity23_external_cron_password, 'cron_management_external_password' ) ) ) ) );

		} else {

			// a password WAS saved but can no longer be decrypted - typically caused
			// by the site's wp-config.php auth keys/salts being rotated. Never
			// silently overwrite it here - surface it so the admin notices and
			// regenerates deliberately, instead of the external cron URL silently
			// breaking with no explanation
			$infinity23_external_cron_password_decrypt_failed = true;

		} // if( empty( $infinity23_external_cron_password_encrypted ) ) {

	} // if( $infinity23_external_cron_enabled == 1 && empty( $infinity23_external_cron_password ) ) {

	// =================================================================================================================
	// Shared helper - converts a cron interval in seconds to a readable label
	// =================================================================================================================

	if( !function_exists( 'infinity23_cron_mgmt_interval_text' ) ) {

		function infinity23_cron_mgmt_interval_text( $interval ) {

			$interval_text = '';

			if( !empty( $interval ) ) {

				if( $interval < 60 ) {
					$interval_text = $interval . ' seconds';
				} elseif( $interval < 3600 ) {
					$interval_text = floor( $interval / 60 ) . ' minutes';
				} elseif( $interval < 86400 ) {
					$interval_text = floor( $interval / 3600 ) . ' hours';
				} else {
					$interval_text = floor( $interval / 86400 ) . ' days';
				} // if( $interval < 60 ) {

			} // if( !empty( $interval ) ) {

			return $interval_text;

		} // function infinity23_cron_mgmt_interval_text( $interval ) {

	} // if( !function_exists( 'infinity23_cron_mgmt_interval_text' ) ) {

	// =================================================================================================================
	// Check if we are viewing a single cron's edit screen
	// =================================================================================================================

	$infinity23_cron_edit_hook = isset( $_GET['cron_edit_hook'] ) ? sanitize_text_field( wp_unslash( $_GET['cron_edit_hook'] ) ) : '';
	$infinity23_cron_edit_timestamp = isset( $_GET['cron_edit_timestamp'] ) ? intval( wp_unslash( $_GET['cron_edit_timestamp'] ) ) : 0;
	$infinity23_cron_edit_args_key = isset( $_GET['cron_edit_args_key'] ) ? sanitize_text_field( wp_unslash( $_GET['cron_edit_args_key'] ) ) : '';

	$infinity23_cron_edit_data = null;

	if( $infinity23_enable_cron_actions == 1 && !empty( $infinity23_cron_edit_hook ) && !empty( $infinity23_cron_edit_timestamp ) && !empty( $infinity23_cron_edit_args_key ) ) {

		$infinity23_cron_edit_raw_row = infinity23_cron_mgmt_find_row( $infinity23_cron_edit_hook, $infinity23_cron_edit_timestamp, $infinity23_cron_edit_args_key );

		if( !empty( $infinity23_cron_edit_raw_row ) ) {

			$infinity23_cron_edit_current_time = time();
			$infinity23_cron_edit_schedule = isset( $infinity23_cron_edit_raw_row['schedule'] ) ? $infinity23_cron_edit_raw_row['schedule'] : false;
			$infinity23_cron_edit_interval = isset( $infinity23_cron_edit_raw_row['interval'] ) ? intval( $infinity23_cron_edit_raw_row['interval'] ) : 0;
			$infinity23_cron_edit_next_run = $infinity23_cron_edit_timestamp;

			if( $infinity23_cron_edit_timestamp < $infinity23_cron_edit_current_time && !empty( $infinity23_cron_edit_interval ) ) {
				$infinity23_cron_edit_missed_runs = ceil( ( $infinity23_cron_edit_current_time - $infinity23_cron_edit_timestamp ) / $infinity23_cron_edit_interval );
				$infinity23_cron_edit_next_run = $infinity23_cron_edit_timestamp + ( $infinity23_cron_edit_missed_runs * $infinity23_cron_edit_interval );
			} // if( $infinity23_cron_edit_timestamp < $infinity23_cron_edit_current_time && !empty( $infinity23_cron_edit_interval ) ) {

			$infinity23_cron_edit_interval_text = infinity23_cron_mgmt_interval_text( $infinity23_cron_edit_interval );

			$infinity23_cron_edit_args = isset( $infinity23_cron_edit_raw_row['args'] ) ? $infinity23_cron_edit_raw_row['args'] : array();
			$infinity23_cron_edit_adjustment = infinity23_cron_mgmt_get_adjustment( $infinity23_cron_edit_hook, $infinity23_cron_edit_args );
			$infinity23_cron_edit_is_paused = ( !empty( $infinity23_cron_edit_adjustment ) && !empty( $infinity23_cron_edit_adjustment['is_paused'] ) );
			$infinity23_cron_edit_is_modified = ( !empty( $infinity23_cron_edit_adjustment ) && $infinity23_cron_edit_schedule !== $infinity23_cron_edit_adjustment['original_schedule'] );

			$infinity23_cron_edit_original_schedule = !empty( $infinity23_cron_edit_adjustment ) ? $infinity23_cron_edit_adjustment['original_schedule'] : $infinity23_cron_edit_schedule;

			$infinity23_cron_edit_data = array(
				'hook'              => $infinity23_cron_edit_hook,
				'timestamp'         => $infinity23_cron_edit_timestamp,
				'args_key'          => $infinity23_cron_edit_args_key,
				'schedule'          => $infinity23_cron_edit_schedule,
				'original_schedule' => $infinity23_cron_edit_original_schedule,
				'interval'          => $infinity23_cron_edit_interval,
				'interval_text'     => $infinity23_cron_edit_interval_text,
				'next_run'          => $infinity23_cron_edit_next_run,
				'is_past'           => ( $infinity23_cron_edit_next_run < $infinity23_cron_edit_current_time ),
				'modified'          => $infinity23_cron_edit_is_modified,
				'paused'            => $infinity23_cron_edit_is_paused,
			);

		} // if( !empty( $infinity23_cron_edit_raw_row ) ) {

	} // if( $infinity23_enable_cron_actions == 1 && !empty( $infinity23_cron_edit_hook ) && !empty( $infinity23_cron_edit_timestamp ) && !empty( $infinity23_cron_edit_args_key ) ) {

	// =================================================================================================================
	// Check if we are viewing a single cron log file
	// =================================================================================================================

	$infinity23_cron_log_view_filename = isset( $_GET['cron_log_view'] ) ? sanitize_text_field( wp_unslash( $_GET['cron_log_view'] ) ) : '';
	$infinity23_cron_log_view_content = null;

	if( $infinity23_cron_log_enabled == 1 && !empty( $infinity23_cron_log_view_filename ) ) {

		$infinity23_cron_log_view_nonce = isset( $_GET['infinity23_cron_log_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_cron_log_nonce'] ) ) : '';

		if( infinity23_cron_mgmt_is_valid_log_filename( $infinity23_cron_log_view_filename ) && wp_verify_nonce( $infinity23_cron_log_view_nonce, infinity23_cron_mgmt_log_view_nonce_action( $infinity23_cron_log_view_filename ) ) ) {

			$infinity23_cron_log_view_raw_content = infinity23_cron_mgmt_read_log_file( $infinity23_cron_log_view_filename );

			if( $infinity23_cron_log_view_raw_content !== false ) {
				$infinity23_cron_log_view_content = $infinity23_cron_log_view_raw_content;
			} // if( $infinity23_cron_log_view_raw_content !== false ) {

		} // if( infinity23_cron_mgmt_is_valid_log_filename( $infinity23_cron_log_view_filename ) && wp_verify_nonce(

	} // if( $infinity23_cron_log_enabled == 1 && !empty( $infinity23_cron_log_view_filename ) ) {

	// =================================================================================================================
	// Build the active crons table
	// =================================================================================================================

	if( !function_exists( 'infinity23_cron_mgmt_build_rows' ) ) {

		function infinity23_cron_mgmt_build_rows() {

			$current_time = time();
			$crons_list = _get_cron_array();
			$rows = array();

			if( !empty( $crons_list ) ) {

				foreach( $crons_list as $timestamp => $cronhooks ) {

					foreach( $cronhooks as $hook => $args_list ) {

						foreach( $args_list as $args_key => $final_data ) {

							$schedule = isset( $final_data['schedule'] ) ? $final_data['schedule'] : false;
							$interval = isset( $final_data['interval'] ) ? intval( $final_data['interval'] ) : 0;
							$row_args = isset( $final_data['args'] ) ? $final_data['args'] : array();

							$next_run = $timestamp;

							if( $timestamp < $current_time && !empty( $interval ) ) {
								$missed_runs = ceil( ( $current_time - $timestamp ) / $interval );
								$next_run = $timestamp + ( $missed_runs * $interval );
							} // if( $timestamp < $current_time && !empty( $interval ) ) {

							// a paused cron still lives right here in the live schedule -
							// pausing never removes it, it only blocks its execution, so
							// paused/modified state is read directly per hook+args
							$adjustment = infinity23_cron_mgmt_get_adjustment( $hook, $row_args );
							$is_paused = ( !empty( $adjustment ) && !empty( $adjustment['is_paused'] ) );
							$is_modified = ( !empty( $adjustment ) && $schedule !== $adjustment['original_schedule'] );

							$rows[] = array(
								'paused'        => $is_paused,
								'hook'          => $hook,
								'timestamp'     => $timestamp,
								'args_key'      => $args_key,
								'schedule'      => $schedule,
								'interval'      => $interval,
								'interval_text' => infinity23_cron_mgmt_interval_text( $interval ),
								'next_run'      => $next_run,
								'is_past'       => ( $next_run < $current_time ),
								'modified'      => $is_modified,
							);

						} // foreach( $args_list as $args_key => $final_data ) {

					} // foreach( $cronhooks as $hook => $args_list ) {

				} // foreach( $crons_list as $timestamp => $cronhooks ) {

			} // if( !empty( $crons_list ) ) {

			$sort_interval_column = array();

			foreach( $rows as $row ) {
				$sort_interval_column[] = !empty( $row['interval'] ) ? $row['interval'] : PHP_INT_MAX;
			} // foreach( $rows as $row ) {

			array_multisort( $sort_interval_column, SORT_ASC, $rows );

			return $rows;

		} // function infinity23_cron_mgmt_build_rows() {

	} // if( !function_exists( 'infinity23_cron_mgmt_build_rows' ) ) {

	if( !function_exists( 'infinity23_cron_mgmt_build_table_html' ) ) {

		function infinity23_cron_mgmt_build_table_html( $rows, $infinity23_enable_cron_actions ) {

			if( empty( $rows ) ) {
				return '<div class="cron-table-empty">No cron events are currently scheduled on this site.</div>';
			} // if( empty( $rows ) ) {

			$table = '';
			$table .= '<table class="infinity23-cron-table">';
				$table .= '<thead>';
					$table .= '<tr>';
						$table .= '<th>Cron Hook</th>';
						$table .= '<th>Interval</th>';
						$table .= '<th>Next Run</th>';
						if( $infinity23_enable_cron_actions == 1 ) {
							$table .= '<th>Actions</th>';
						} // if( $infinity23_enable_cron_actions == 1 ) {
					$table .= '</tr>';
				$table .= '</thead>';
				$table .= '<tbody>';

			foreach( $rows as $row ) {

				$row_class = '';

				if( $row['paused'] ) {
					$row_class .= ' cron-row-paused';
				} // if( $row['paused'] ) {

				if( !$row['paused'] && $row['is_past'] ) {
					$row_class .= ' cron-row-overdue';
				} // if( !$row['paused'] && $row['is_past'] ) {

				$table .= '<tr class="' . esc_attr( trim( $row_class ) ) . '">';

					$plugin_name = infinity23_cron_mgmt_get_plugin_name_for_hook( $row['hook'] );

					$table .= '<td>' . esc_html( $row['hook'] );

					if( !empty( $plugin_name ) ) {
						$table .= '<br /><small class="cron-hook-plugin-name">' . esc_html( $plugin_name ) . '</small>';
					} // if( !empty( $plugin_name ) ) {

					if( $row['paused'] ) {
						$table .= '<span class="cron-badge cron-badge-paused">Paused</span>';
					} elseif( $row['is_past'] ) {
						$table .= '<span class="cron-badge cron-badge-overdue">Overdue</span>';
					} // if( $row['paused'] ) {

					if( !empty( $row['modified'] ) ) {
						$table .= '<span class="cron-badge cron-badge-modified">Interval Changed</span>';
					} // if( !empty( $row['modified'] ) ) {

					$table .= '</td>';

					if( !empty( $row['interval'] ) ) {
						$table .= '<td>' . esc_html( $row['interval'] ) . ' seconds <small style="color:#8F8F8F;">(' . esc_html( $row['interval_text'] ) . ')</small>';
					} else {
						$table .= '<td>One-time';
					} // if( !empty( $row['interval'] ) ) {

					$table .= '</td>';

					$table .= '<td class="cron-next-run"><small>(' . esc_html( wp_date( 'Y-m-d', $row['next_run'] ) ) . ')</small> ' . esc_html( wp_date( 'H:i:s', $row['next_run'] ) );

					if( $row['paused'] ) {
						$table .= '<br /><small style="color:#8F8F8F;">(execution blocked while paused)</small>';
					} // if( $row['paused'] ) {

					$table .= '</td>';

					if( $infinity23_enable_cron_actions == 1 ) {

						$table .= '<td>';

						$table .= '<a class="cron-action-btn cron-action-btn-edit" href="' . esc_url( infinity23_cron_mgmt_edit_url( $row['hook'], $row['timestamp'], $row['args_key'] ) ) . '">Edit Cron</a>';

						$table .= '</td>';

					} // if( $infinity23_enable_cron_actions == 1 ) {

				$table .= '</tr>';

			} // foreach( $rows as $row ) {

				$table .= '</tbody>';
			$table .= '</table>';

			return $table;

		} // function infinity23_cron_mgmt_build_table_html( $rows ) {

	} // if( !function_exists( 'infinity23_cron_mgmt_build_table_html' ) ) {

	$infinity23_cron_mgmt_rows = infinity23_cron_mgmt_build_rows();
	$infinity23_cron_mgmt_table_html = infinity23_cron_mgmt_build_table_html( $infinity23_cron_mgmt_rows, $infinity23_enable_cron_actions );

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<?php if( !empty( $infinity23_cron_edit_data ) ) { ?>

<div class="infinity23-warp cron-management-warp cron-edit-page-warp no-floating-save">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Edit Cron Event</h1>
		<p>Change when this cron event will next run, run it immediately, or disable it.</p>
	</div><!--page-header-->

	<a class="cron-edit-back-link" href="<?php echo esc_url( remove_query_arg( array( 'cron_edit_hook', 'cron_edit_timestamp', 'cron_edit_args_key' ) ) ); ?>">&larr; Back To All Crons</a>

	<div class="section-card" data-card-default="open">

		<h2>Cron Details:</h2>

		<div class="cron-edit-details">

			<div class="cron-edit-row"><span class="cron-edit-label">Cron Hook:</span><span class="cron-edit-value"><?php echo esc_html( $infinity23_cron_edit_data['hook'] ); ?></span></div>

			<?php if( !empty( $infinity23_cron_edit_data['interval'] ) ) { ?>
				<div class="cron-edit-row"><span class="cron-edit-label">Interval:</span><span class="cron-edit-value"><?php echo esc_html( $infinity23_cron_edit_data['interval'] ); ?> seconds <small style="color:#8F8F8F;">(<?php echo esc_html( $infinity23_cron_edit_data['interval_text'] ); ?>)</small></span></div>
			<?php } else { ?>
				<div class="cron-edit-row"><span class="cron-edit-label">Interval:</span><span class="cron-edit-value">One-time event</span></div>
			<?php } // if( !empty( $infinity23_cron_edit_data['interval'] ) ) { ?>

			<div class="cron-edit-row"><span class="cron-edit-label">Schedule:</span><span class="cron-edit-value"><?php echo esc_html( !empty( $infinity23_cron_edit_data['schedule'] ) ? $infinity23_cron_edit_data['schedule'] : 'single event' ); ?></span></div>

			<?php if( $infinity23_cron_edit_data['is_past'] ) { ?>
				<div class="cron-edit-row"><span class="cron-edit-label">Status:</span><span class="cron-edit-value"><span class="cron-badge cron-badge-overdue">Overdue</span></span></div>
			<?php } // if( $infinity23_cron_edit_data['is_past'] ) { ?>

			<?php if( !empty( $infinity23_cron_edit_data['modified'] ) ) { ?>
				<div class="cron-edit-row"><span class="cron-edit-label">Status:</span><span class="cron-edit-value"><span class="cron-badge cron-badge-modified">Interval Changed</span></span></div>
			<?php } // if( !empty( $infinity23_cron_edit_data['modified'] ) ) { ?>

			<?php if( !empty( $infinity23_cron_edit_data['paused'] ) ) { ?>
				<div class="cron-edit-row"><span class="cron-edit-label">Status:</span><span class="cron-edit-value"><span class="cron-badge cron-badge-paused">Paused - execution blocked</span></span></div>
			<?php } // if( !empty( $infinity23_cron_edit_data['paused'] ) ) { ?>

		</div><!--cron-edit-details-->

		<?php if( !empty( $infinity23_cron_edit_data['modified'] ) || !empty( $infinity23_cron_edit_data['paused'] ) ) { ?>

		<p><a class="cron-action-btn cron-action-btn-disable" data-confirm="Revert this cron back to its original settings?" href="<?php echo esc_url( infinity23_cron_mgmt_row_url( 'revert_changes', $infinity23_cron_edit_data['hook'], $infinity23_cron_edit_data['timestamp'], $infinity23_cron_edit_data['args_key'] ) ); ?>">Revert To Original Settings</a></p>

		<?php } // if( !empty( $infinity23_cron_edit_data['modified'] ) || !empty( $infinity23_cron_edit_data['paused'] ) ) { ?>

		<h3>Cron Status</h3>

		<p>Turn this cron event on or off. Pausing does not change its schedule - it only blocks execution while Infinity23 is active.</p>

		<form class="infinity23-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

			<input type="hidden" name="action" value="infinity23_cron_mgmt_update_time" />
			<input type="hidden" name="infinity23_cron_hook" value="<?php echo esc_attr( $infinity23_cron_edit_data['hook'] ); ?>" />
			<input type="hidden" name="infinity23_cron_timestamp" value="<?php echo esc_attr( $infinity23_cron_edit_data['timestamp'] ); ?>" />
			<input type="hidden" name="infinity23_cron_args_key" value="<?php echo esc_attr( $infinity23_cron_edit_data['args_key'] ); ?>" />
			<?php wp_nonce_field( infinity23_cron_mgmt_row_nonce_action( $infinity23_cron_edit_data['hook'], $infinity23_cron_edit_data['timestamp'], $infinity23_cron_edit_data['args_key'] ), 'infinity23_cron_row_nonce' ); ?>

			<?php if( !empty( $infinity23_cron_edit_data['schedule'] ) ) { ?>

			<div class="form-line">
				<label for="cron_edit_status">Cron Status: </label>
				<select id="cron_edit_status" name="cron_edit_status">
					<option value="active" <?php if( empty( $infinity23_cron_edit_data['paused'] ) ) { echo 'selected'; } ?>>Active</option>
					<option value="paused" <?php if( !empty( $infinity23_cron_edit_data['paused'] ) ) { echo 'selected'; } ?>>Paused (not recommended)</option>
				</select>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Pausing blocks this cron's other registered actions from running while Infinity23 is active.
						<br /><br />
						The schedule itself is never changed - WordPress still ticks it forward on time, it just won't fire while paused.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<?php } else { ?>

			<p>One-time cron events cannot be paused - WordPress removes a one-time event right before it runs, so pausing it would delete it.</p>

			<input type="hidden" name="cron_edit_status" value="active" />

			<?php } // if( !empty( $infinity23_cron_edit_data['schedule'] ) ) { ?>

			<?php if( !empty( $infinity23_cron_edit_data['schedule'] ) ) { ?>

			<h3>Interval</h3>

			<div class="form-line">
				<label for="cron_edit_schedule">Interval: </label>
				<select id="cron_edit_schedule" name="cron_edit_schedule">
					<option value="infinity23_default_interval" <?php if( $infinity23_cron_edit_data['schedule'] === $infinity23_cron_edit_data['original_schedule'] ) { echo 'selected'; } ?>>Default (Keep As Is)</option>
					<?php $infinity23_cron_mgmt_curated_schedules = infinity23_cron_mgmt_curated_schedules(); ?>
					<?php $infinity23_cron_mgmt_all_schedules = wp_get_schedules(); ?>
					<?php foreach( $infinity23_cron_mgmt_curated_schedules as $infinity23_cron_mgmt_schedule_key => $infinity23_cron_mgmt_schedule_label ) { ?>
						<?php $infinity23_cron_mgmt_schedule_interval = isset( $infinity23_cron_mgmt_all_schedules[ $infinity23_cron_mgmt_schedule_key ]['interval'] ) ? intval( $infinity23_cron_mgmt_all_schedules[ $infinity23_cron_mgmt_schedule_key ]['interval'] ) : 0; ?>
						<option value="<?php echo esc_attr( $infinity23_cron_mgmt_schedule_key ); ?>" <?php if( $infinity23_cron_edit_data['schedule'] === $infinity23_cron_mgmt_schedule_key && $infinity23_cron_edit_data['schedule'] !== $infinity23_cron_edit_data['original_schedule'] ) { echo 'selected'; } ?>><?php echo esc_html( $infinity23_cron_mgmt_schedule_label . ' (' . infinity23_cron_mgmt_interval_text( $infinity23_cron_mgmt_schedule_interval ) . ')' ); ?></option>
					<?php } // foreach( $infinity23_cron_mgmt_curated_schedules as $infinity23_cron_mgmt_schedule_key => $infinity23_cron_mgmt_schedule_label ) { ?>
				</select>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Pick "Default" to keep this cron's original interval - it will not be treated as changed when the module is deactivated.
						<br /><br />
						Or pick from a fixed set of common intervals - 5 minutes up to monthly.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<?php } // if( !empty( $infinity23_cron_edit_data['schedule'] ) ) { ?>

			<h3>Next Run Time <small>(Not Recommended to change)</small></h3>

			<p>The site timezone (<?php echo esc_html( wp_timezone_string() ); ?>) is used. Changing this is not tracked and will not be reverted later.</p>

			<div class="form-line">
				<label for="cron_edit_date">Next Run Date: </label>
				<input type="date" id="cron_edit_date" name="cron_edit_date" value="<?php echo esc_attr( wp_date( 'Y-m-d', $infinity23_cron_edit_data['next_run'] ) ); ?>" />
			</div><!--form-line-->

			<div class="form-line">
				<label for="cron_edit_time">Next Run Time: </label>
				<input type="time" id="cron_edit_time" name="cron_edit_time" value="<?php echo esc_attr( wp_date( 'H:i', $infinity23_cron_edit_data['next_run'] ) ); ?>" />
			</div><!--form-line-->

			<input type="submit" class="submit-btn" value="Save Changes" />

		</form>

		<div class="cron-edit-more-actions-wrap">

			<h3>More Actions:</h3>

			<p>Run this cron event immediately, or disable it so it stops running. Any feature relying on it may stop working.</p>

			<a class="cron-action-btn cron-action-btn-run" data-confirm="Run this cron event right now?" href="<?php echo esc_url( infinity23_cron_mgmt_row_url( 'run_now', $infinity23_cron_edit_data['hook'], $infinity23_cron_edit_data['timestamp'], $infinity23_cron_edit_data['args_key'] ) ); ?>">Run Cron Now</a>

		</div><!--cron-edit-more-actions-wrap-->

	</div><!--section-card-->

</div><!--infinity23-warp-->

<?php } else { ?>

<?php

	// --------------------------------------------
	// active tab - url based instead of javascript, so each tab has its own link
	// --------------------------------------------

	$infinity23_cron_mgmt_active_tab = isset( $_GET['cron_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['cron_tab'] ) ) : 'settings';

	if( !in_array( $infinity23_cron_mgmt_active_tab, array( 'settings', 'logs' ), true ) ) {
		$infinity23_cron_mgmt_active_tab = 'settings';
	} // if( !in_array( $infinity23_cron_mgmt_active_tab, array( 'settings', 'logs' ), true ) ) {

	$infinity23_cron_mgmt_tab_settings_url = esc_url( add_query_arg( 'cron_tab', 'settings', remove_query_arg( array( 'cron_log_view', 'infinity23_cron_log_nonce' ) ) ) );
	$infinity23_cron_mgmt_tab_logs_url = esc_url( add_query_arg( 'cron_tab', 'logs' ) );

?>

<div class="infinity23-warp cron-management-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Cron Management Settings</h1>
		<p>View every cron event currently scheduled on this site and click Edit Cron to change or manage one.</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_cron_mgmt_activate_action', 'infinity23_cron_mgmt_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Cron Management</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<div class="infinity23-module-tabs-wrap">

		<div class="infinity23-module-tabs">
			<a href="<?php echo esc_url( $infinity23_cron_mgmt_tab_settings_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_cron_mgmt_active_tab === 'settings' ) { echo 'infinity23-module-tab-active'; } ?>">Cron Settings</a>
			<?php if( $infinity23_cron_log_enabled == 1 ) { ?>
				<a href="<?php echo esc_url( $infinity23_cron_mgmt_tab_logs_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_cron_mgmt_active_tab === 'logs' ) { echo 'infinity23-module-tab-active'; } ?>">Cron Logs</a>
			<?php } // if( $infinity23_cron_log_enabled == 1 ) { ?>
		</div><!--infinity23-module-tabs-->

		<div class="infinity23-module-tab-content <?php if( $infinity23_cron_mgmt_active_tab !== 'settings' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="cron-mgmt-tab-settings">

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

		<div class="section-card" data-card-default="open">

			<h2>Active Crons:</h2>

			<p>Below is a live list of every cron event WordPress currently has scheduled on this site. Paused events are shown in the same table, marked with a "Paused" label.</p>

			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values in infinity23_cron_mgmt_build_table_html() ?>
			<?php echo $infinity23_cron_mgmt_table_html; ?>

		</div><!--section-card-->

		<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="open">

			<h2>Cron Log Settings: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

			<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="enable_cron_actions" name="enable_cron_actions" value="1" <?php if( $infinity23_enable_cron_actions == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<label for="enable_cron_actions">Allow making changes and editing crons directly from this page</label>
			</div><!--form-line-->

			<h3>Save Cron Logs:</h3>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="cron_log_enabled" name="cron_log_enabled" value="1" <?php if( $infinity23_cron_log_enabled == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<label for="cron_log_enabled">Keep a log of cron events that run on this site</label>
			</div><!--form-line-->

			<div class="form-line">
				<label for="cron_log_retention_days">Keep log entries for: </label>
				<input type="text" id="cron_log_retention_days" class="short-input" name="cron_log_retention_days" value="<?php echo esc_attr( $infinity23_cron_log_retention_days ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<small>Days</small>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Log entries older than this number of days are automatically removed.
						<br /><br />
						We recommend not choosing more than 7 days, since log files can grow large and take up disk space.
						<br /><br />
						<strong>Usage Examples:</strong>
						<ul>
							<li>1 week - <span class="code">7</span></li>
							<li>1 month - <span class="code">30</span></li>
							<li>3 months - <span class="code">90</span></li>
						</ul>
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<p class="infinity23-notice-text">Note: we do not recommend keeping cron logs for more than a few weeks, since the log files can grow large and take up space on your server.</p>

		</div><!--section-card-->

		<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="open">

			<h2>Cron Frequency & External Triggering: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

			<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

			<p>Control how often WordPress's built-in cron check runs on its own, and optionally add a password-protected URL to trigger it externally.</p>

			<h3>Limit Cron Runs:</h3>

			<div class="form-line">
				<label for="disable_wp_built_in_cron">Automatic Run Frequency: </label>
				<select id="disable_wp_built_in_cron" name="disable_wp_built_in_cron" class="infinity23-cron-frequency-select" data-target="infinity23-external-cron-wrap" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?>>
					<option value="default" <?php if( $infinity23_disable_wp_built_in_cron == 'default' ) { echo 'selected'; } ?>>Normal - runs on every site visit (recommended)</option>
					<option value="3_minutes" <?php if( $infinity23_disable_wp_built_in_cron == '3_minutes' ) { echo 'selected'; } ?>>Limit to once every 3 minutes</option>
					<option value="5_minutes" <?php if( $infinity23_disable_wp_built_in_cron == '5_minutes' ) { echo 'selected'; } ?>>Limit to once every 5 minutes</option>
					<option value="10_minutes" <?php if( $infinity23_disable_wp_built_in_cron == '10_minutes' ) { echo 'selected'; } ?>>Limit to once every 10 minutes</option>
					<option value="fully_disabled" <?php if( $infinity23_disable_wp_built_in_cron == 'fully_disabled' ) { echo 'selected'; } ?>>Fully disable automatic run (external cron only)</option>
				</select>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Controls how often WordPress checks for due tasks on its own, triggered by site visitors.
						<br /><br />
						The 3/5/10 minute limits work on their own - no password or external service required.
						<br /><br />
						<strong>Fully disable</strong> stops the automatic check completely. Nothing will trigger cron on this site unless External Cron Access below is also turned on.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<?php if( $infinity23_disable_wp_built_in_cron != 'default' && $infinity23_disable_wp_built_in_cron != 'fully_disabled' ) { ?>
				<div class="infinity23-warning-text">Tasks may run later than expected while this limit is active, depending on how often visitors reach the site.</div>
			<?php } // if( $infinity23_disable_wp_built_in_cron != 'default' && $infinity23_disable_wp_built_in_cron != 'fully_disabled' ) { ?>

			<?php if( $infinity23_disable_wp_built_in_cron == 'fully_disabled' && $infinity23_external_cron_enabled != 1 ) { ?>
				<div class="infinity23-warning-text">WP-Cron is fully disabled and External Cron Access is off - scheduled tasks will not run at all until one of them is enabled.</div>
			<?php } // if( $infinity23_disable_wp_built_in_cron == 'fully_disabled' && $infinity23_external_cron_enabled != 1 ) { ?>

			<h3>External Cron Access:</h3>

			<div id="infinity23-external-cron-wrap">

			<div class="form-line checkbox-line">
				<input type="checkbox" id="external_cron_enabled" name="external_cron_enabled" value="1" <?php if( $infinity23_external_cron_enabled == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<label for="external_cron_enabled">Add a password-protected URL to trigger WP-Cron from an external service, bypassing the limit above</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						wp-cron.php is a core WordPress file that already exists on every site - this password does not lock it down or control who can reach it.
						<br /><br />
						What it does is let a request that includes the correct password bypass the throttle/disable limit set above, so an external service can still reliably trigger cron even while that limit is active.
						<br /><br />
						Recommended when Automatic Run Frequency above is set to <strong>Fully disable</strong>, since without it nothing will trigger cron on this site.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="infinity23-toggle-fields" data-toggle-input="external_cron_enabled">

			<?php if( $infinity23_external_cron_password_decrypt_failed ) { ?>
				<div class="infinity23-warning-text">The saved external cron password could not be decrypted - this can happen if your site's authentication keys/salts were changed. Click Generate Random below, save, and update your external cron service with the new URL.</div>
			<?php } // if( $infinity23_external_cron_password_decrypt_failed ) { ?>

			<div class="form-line">
				<label for="external_cron_password">Password: </label>
				<input type="text" id="external_cron_password" class="medium-input" name="external_cron_password" value="<?php echo esc_attr( $infinity23_external_cron_password ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<button type="button" class="infinity23-upload-btn-style infinity23-generate-password-btn" data-target="external_cron_password">Generate Random</button>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Included in the URL below so your external cron service can bypass the limit set above.
						<br /><br />
						Some cron services let you send this as a header instead of putting it in the URL: <span class="code">X-Infinity23-Cron-Key</span>.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<h3>Cron Trigger URL (Recommended):</h3>

			<p>Add this URL to a free service like <a href="https://cron-job.org/en/" target="_blank">cron-job.org</a> and send the password as a request header, so it never shows up in access logs.</p>

			<p class="cron-external-url-box"><?php echo esc_html( site_url( 'wp-cron.php' ) ); ?></p>

			<p>Header name: <span class="code">X-Infinity23-Cron-Key</span></p>

			<p>Header value: the password above</p>

			<h3>Cron Trigger URL With Password (Fallback):</h3>

			<p>Only use this if your cron service cannot send custom headers. The password is part of this URL, so it will be saved in access logs. You can also open it to run WP-Cron now.</p>

			<p class="cron-external-url-box"><a href="<?php echo esc_url( infinity23_cron_mgmt_external_cron_url( $infinity23_external_cron_password ) ); ?>" target="_blank"><?php echo esc_html( infinity23_cron_mgmt_external_cron_url( $infinity23_external_cron_password ) ); ?></a></p>

			</div><!--infinity23-toggle-fields-->

			</div><!--infinity23-external-cron-wrap-->

		</div><!--section-card-->

		<?php infinity23_menu_visibility_field( 'cron_management_hide_menu', $infinity23_cron_management_hide_menu, $infinity23_module_display_name ); ?>

		<?php wp_nonce_field( 'infinity23_cron_mgmt_settings_action', 'infinity23_cron_mgmt_nonce' ); ?>

		<input type="submit" name="submit_cron_management" class="submit-btn" value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="additional-data-warp" data-card-default="open">

		<h2>Additional Data</h2>

		<ul>
			<li><strong>Automatic Revert On Deactivation</strong> - Any pause or interval change is reverted back to normal once Cron Management is deactivated, excluded, or the whole plugin is deactivated.</li>
			<li><strong>Troubleshooting Cron Issues</strong> - If a cron event stops behaving correctly, go to Include / Exclude and turn off every plugin, then turn them all back on. This resolves most cron issues.</li>
		</ul>

	</div><!--additional-data-warp-->

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_cron_mgmt_reset_action', 'infinity23_cron_mgmt_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn" data-confirm="Are you sure you want to reset all Cron Management settings?">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

		</div><!--infinity23-module-tab-content-->

		<?php if( $infinity23_cron_log_enabled == 1 ) { ?>

		<div class="infinity23-module-tab-content <?php if( $infinity23_cron_mgmt_active_tab !== 'logs' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="cron-mgmt-tab-logs">

		<div class="section-card" data-card-default="open">

			<h2>Cron Log Files:</h2>

			<?php $infinity23_cron_mgmt_log_files_list = infinity23_cron_mgmt_list_log_files(); ?>

			<?php if( empty( $infinity23_cron_mgmt_log_files_list ) ) { ?>

				<div class="cron-table-empty">No log files have been saved yet.</div>

			<?php } else { ?>

				<p>Click a day below to view its log content. Switch between other saved days using the tabs without leaving this page.</p>

				<div class="infinity23-cron-log-tabs">

					<?php foreach( $infinity23_cron_mgmt_log_files_list as $infinity23_cron_mgmt_log_file_item ) { ?>

						<?php if( $infinity23_cron_log_view_content !== null && $infinity23_cron_mgmt_log_file_item['filename'] === $infinity23_cron_log_view_filename ) { ?>
							<span class="infinity23-cron-log-tab infinity23-cron-log-tab-active"><?php echo esc_html( $infinity23_cron_mgmt_log_file_item['date_display'] ); ?></span>
						<?php } else { ?>
							<a class="infinity23-cron-log-tab" href="<?php echo esc_url( infinity23_cron_mgmt_log_view_url( $infinity23_cron_mgmt_log_file_item['filename'] ) ); ?>"><?php echo esc_html( $infinity23_cron_mgmt_log_file_item['date_display'] ); ?></a>
						<?php } // if( $infinity23_cron_log_view_content !== null && $infinity23_cron_mgmt_log_file_item['filename'] === $infinity23_cron_log_view_filename ) { ?>

					<?php } // foreach( $infinity23_cron_mgmt_log_files_list as $infinity23_cron_mgmt_log_file_item ) { ?>

				</div><!--infinity23-cron-log-tabs-->

				<?php if( $infinity23_cron_log_view_content !== null ) { ?>

					<p><a class="cron-action-btn" href="<?php echo esc_url( add_query_arg( 'cron_tab', 'logs', remove_query_arg( array( 'cron_log_view', 'infinity23_cron_log_nonce' ) ) ) ); ?>">&larr; Back To All Cron Logs</a></p>

					<h3>Log Content <small>(<?php echo esc_html( $infinity23_cron_log_view_filename ); ?>)</small></h3>

					<pre class="infinity23-cron-log-content"><?php echo esc_html( $infinity23_cron_log_view_content ); ?></pre>

				<?php } else { ?>

					<table class="infinity23-cron-table">
						<thead>
							<tr>
								<th>Date</th>
								<th>Size</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>

							<?php foreach( $infinity23_cron_mgmt_log_files_list as $infinity23_cron_mgmt_log_file_item ) { ?>

								<tr>
									<td><?php echo esc_html( $infinity23_cron_mgmt_log_file_item['date_display'] ); ?></td>
									<td><?php echo esc_html( infinity23_cron_mgmt_format_log_size( $infinity23_cron_mgmt_log_file_item['size'] ) ); ?></td>
									<td><a class="cron-action-btn cron-action-btn-edit" href="<?php echo esc_url( infinity23_cron_mgmt_log_view_url( $infinity23_cron_mgmt_log_file_item['filename'] ) ); ?>">View Log</a></td>
								</tr>

							<?php } // foreach( $infinity23_cron_mgmt_log_files_list as $infinity23_cron_mgmt_log_file_item ) { ?>

						</tbody>
						<tfoot>
							<tr>
								<td><strong>Total</strong></td>
								<td><strong><?php echo esc_html( infinity23_cron_mgmt_format_log_size( array_sum( wp_list_pluck( $infinity23_cron_mgmt_log_files_list, 'size' ) ) ) ); ?></strong></td>
								<td></td>
							</tr>
						</tfoot>
					</table>

				<?php } // if( $infinity23_cron_log_view_content !== null ) { ?>

			<?php } // if( empty( $infinity23_cron_mgmt_log_files_list ) ) { ?>

		</div><!--section-card-->

		<?php if( !empty( $infinity23_cron_mgmt_log_files_list ) ) { ?>

		<div class="infinity23-danger-zone-warp infinity23-log-danger-zone-warp" data-card-default="closed">

			<h2><span class="infinity23-danger-zone-icon">&#9888;</span> Remove Logs Danger Zone</h2>

			<p>Permanently deletes every saved cron log file below. This cannot be undone.</p>

			<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="infinity23_cron_mgmt_delete_all_logs" />
				<?php wp_nonce_field( infinity23_cron_mgmt_delete_all_logs_nonce_action(), 'infinity23_cron_delete_all_logs_nonce' ); ?>
				<button type="button" class="infinity23-reset-btn infinity23-danger-zone-btn" data-confirm="Are you sure you want to delete all saved cron log files? This action cannot be undone.">Delete All Cron Logs</button>
			</form>

		</div><!--infinity23-danger-zone-warp-->

		<?php } // if( !empty( $infinity23_cron_mgmt_log_files_list ) ) { ?>

		</div><!--infinity23-module-tab-content-->

		<?php } // if( $infinity23_cron_log_enabled == 1 ) { ?>

	</div><!--infinity23-module-tabs-wrap-->

</div><!--infinity23-warp-->

<?php } // if( !empty( $infinity23_cron_edit_data ) ) { ?>

<?php infinity23_modules_footer(); ?>
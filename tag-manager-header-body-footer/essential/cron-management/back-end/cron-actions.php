<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Cron Management - row actions: run now / reschedule
    // Only loaded when the module's activation code is on
    // ===========================================================================

    // ---------------------------------------
    // shared check - whether "Allow making changes and editing crons directly
    // from this page" is turned on. Without this, turning the setting off
    // only hid the buttons but the admin-post.php handlers below could still
    // be triggered directly with a valid nonce.
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_actions_allowed' ) ) {

        function infinity23_cron_mgmt_actions_allowed() {

            include( dirname(__FILE__) . "/../default-values.php" );

            $infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option( 'cron_management_settings' ) );
            $enable_cron_actions = intval( infinity23_pro_get_data( 'enable_cron_actions', $infinity23_data_array, $infinity23_defaults ) );

            return ( $enable_cron_actions == 1 );

        } // function infinity23_cron_mgmt_actions_allowed() {

    } // if( !function_exists( 'infinity23_cron_mgmt_actions_allowed' ) ) {

    if( !function_exists( 'infinity23_cron_mgmt_validate_request' ) ) {

        function infinity23_cron_mgmt_validate_request() {

            if( !current_user_can( 'manage_options' ) ) {
                wp_die( 'Permission denied.' );
            } // if( !current_user_can( 'manage_options' ) ) {

            if( !infinity23_cron_mgmt_actions_allowed() ) {
                wp_die( 'Cron actions are disabled in the Cron Management settings.' );
            } // if( !infinity23_cron_mgmt_actions_allowed() ) {

            // phpcs:disable WordPress.Security.NonceVerification.Missing -- collected here, verified with wp_verify_nonce() a few lines below before any of these values are used
            $hook = isset( $_GET['infinity23_cron_hook'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_cron_hook'] ) ) : '';
            $timestamp = isset( $_GET['infinity23_cron_timestamp'] ) ? intval( wp_unslash( $_GET['infinity23_cron_timestamp'] ) ) : 0;
            $args_key = isset( $_GET['infinity23_cron_args_key'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_cron_args_key'] ) ) : '';
            $nonce = isset( $_GET['infinity23_cron_row_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_cron_row_nonce'] ) ) : '';
            // phpcs:enable WordPress.Security.NonceVerification.Missing

            if( empty( $hook ) || empty( $timestamp ) || empty( $args_key ) ) {
                wp_die( 'Invalid request.' );
            } // if( empty( $hook ) || empty( $timestamp ) || empty( $args_key ) ) {

            $nonce_action = infinity23_cron_mgmt_row_nonce_action( $hook, $timestamp, $args_key );

            if( !wp_verify_nonce( $nonce, $nonce_action ) ) {
                wp_die( 'Link expired.' );
            } // if( !wp_verify_nonce( $nonce, $nonce_action ) ) {

            $row = infinity23_cron_mgmt_find_row( $hook, $timestamp, $args_key );

            if( empty( $row ) ) {
                wp_die( 'This cron event no longer exists - the list may have changed.' );
            } // if( empty( $row ) ) {

            return array(
                'hook'      => $hook,
                'timestamp' => $timestamp,
                'row'       => $row,
                'args_key'  => $args_key,
            );

        } // function infinity23_cron_mgmt_validate_request() {

    } // if( !function_exists( 'infinity23_cron_mgmt_validate_request' ) ) {

    // ---------------------------------------
    // run now - fires the hook callbacks immediately with the original args
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_handle_run_now' ) ) {

        function infinity23_cron_mgmt_handle_run_now() {

            $request = infinity23_cron_mgmt_validate_request();
            $args = isset( $request['row']['args'] ) ? $request['row']['args'] : array();

            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- re-fires an already scheduled WP-Cron hook by its existing name ("run now"), never defines a new hook
            do_action_ref_array( $request['hook'], $args );

            $infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-cron-management', 'cron_run' => '1' ), admin_url('admin.php') );
            wp_safe_redirect( $infinity23_redirect_url );
            exit;

        } // function infinity23_cron_mgmt_handle_run_now() {

    } // if( !function_exists( 'infinity23_cron_mgmt_handle_run_now' ) ) {

    add_action( 'admin_post_infinity23_cron_mgmt_run_now', 'infinity23_cron_mgmt_handle_run_now' );

    // ---------------------------------------
    // reschedule - clears the current instance and schedules it again starting now
    // only valid for recurring events - single events are refused
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_handle_reschedule' ) ) {

        function infinity23_cron_mgmt_handle_reschedule() {

            $request = infinity23_cron_mgmt_validate_request();
            $hook = $request['hook'];
            $timestamp = $request['timestamp'];
            $row = $request['row'];
            $args = isset( $row['args'] ) ? $row['args'] : array();
            $schedule = isset( $row['schedule'] ) ? $row['schedule'] : false;

            if( empty( $schedule ) ) {
                wp_die( 'This is a one-time event and cannot be rescheduled - use Run Now instead.' );
            } // if( empty( $schedule ) ) {

            wp_unschedule_event( $timestamp, $hook, $args );
            wp_schedule_event( time(), $schedule, $hook, $args );

            $infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-cron-management', 'cron_reschedule' => '1' ), admin_url('admin.php') );
            wp_safe_redirect( $infinity23_redirect_url );
            exit;

        } // function infinity23_cron_mgmt_handle_reschedule() {

    } // if( !function_exists( 'infinity23_cron_mgmt_handle_reschedule' ) ) {

    add_action( 'admin_post_infinity23_cron_mgmt_reschedule', 'infinity23_cron_mgmt_handle_reschedule' );

    // ---------------------------------------
    // pause and activate now happen through the "Cron Status" select in the
    // main edit form, handled inside infinity23_cron_mgmt_handle_update_time()
    // below - no separate GET-based handlers needed anymore
    // ---------------------------------------

    // ---------------------------------------
    // shared POST validator - used by the update time form submit below
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_validate_post_request' ) ) {

        function infinity23_cron_mgmt_validate_post_request() {

            if( !current_user_can( 'manage_options' ) ) {
                wp_die( 'Permission denied.' );
            } // if( !current_user_can( 'manage_options' ) ) {

            if( !infinity23_cron_mgmt_actions_allowed() ) {
                wp_die( 'Cron actions are disabled in the Cron Management settings.' );
            } // if( !infinity23_cron_mgmt_actions_allowed() ) {

            $hook = isset( $_POST['infinity23_cron_hook'] ) ? sanitize_text_field( wp_unslash( $_POST['infinity23_cron_hook'] ) ) : '';
            $timestamp = isset( $_POST['infinity23_cron_timestamp'] ) ? intval( wp_unslash( $_POST['infinity23_cron_timestamp'] ) ) : 0;
            $args_key = isset( $_POST['infinity23_cron_args_key'] ) ? sanitize_text_field( wp_unslash( $_POST['infinity23_cron_args_key'] ) ) : '';
            $nonce = isset( $_POST['infinity23_cron_row_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['infinity23_cron_row_nonce'] ) ) : '';

            if( empty( $hook ) || empty( $timestamp ) || empty( $args_key ) ) {
                wp_die( 'Invalid request.' );
            } // if( empty( $hook ) || empty( $timestamp ) || empty( $args_key ) ) {

            $nonce_action = infinity23_cron_mgmt_row_nonce_action( $hook, $timestamp, $args_key );

            if( !wp_verify_nonce( $nonce, $nonce_action ) ) {
                wp_die( 'Link expired.' );
            } // if( !wp_verify_nonce( $nonce, $nonce_action ) ) {

            $row = infinity23_cron_mgmt_find_row( $hook, $timestamp, $args_key );

            if( empty( $row ) ) {

                // the saved timestamp may be stale if WP-Cron already fired
                // this event again before the edit form was submitted -
                // fall back to matching by hook + args_key alone
                $fallback_match = infinity23_cron_mgmt_find_row_by_args_key( $hook, $args_key );

                if( empty( $fallback_match ) ) {
                    wp_die( 'This cron event no longer exists - the list may have changed.' );
                } // if( empty( $fallback_match ) ) {

                $timestamp = $fallback_match['timestamp'];
                $row = $fallback_match['row'];

            } // if( empty( $row ) ) {

            return array(
                'hook'      => $hook,
                'timestamp' => $timestamp,
                'row'       => $row,
            );

        } // function infinity23_cron_mgmt_validate_post_request() {

    } // if( !function_exists( 'infinity23_cron_mgmt_validate_post_request' ) ) {

    // ---------------------------------------
    // update time - unschedules the current instance and schedules it again
    // at the exact date/time chosen on the edit page, keeping the same
    // schedule (for recurring events) or as a single event (for one-time events)
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_handle_update_time' ) ) {

        function infinity23_cron_mgmt_handle_update_time() {

            $request = infinity23_cron_mgmt_validate_post_request();
            $hook = $request['hook'];
            $timestamp = $request['timestamp'];
            $row = $request['row'];
            $args = isset( $row['args'] ) ? $row['args'] : array();
            $old_schedule = isset( $row['schedule'] ) ? $row['schedule'] : false;

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above by infinity23_cron_mgmt_validate_post_request()
            $new_date = isset( $_POST['cron_edit_date'] ) ? sanitize_text_field( wp_unslash( $_POST['cron_edit_date'] ) ) : '';
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above by infinity23_cron_mgmt_validate_post_request()
            $new_time = isset( $_POST['cron_edit_time'] ) ? sanitize_text_field( wp_unslash( $_POST['cron_edit_time'] ) ) : '';

            if( empty( $new_date ) || empty( $new_time ) ) {
                wp_die( 'Please provide both a date and a time.' );
            } // if( empty( $new_date ) || empty( $new_time ) ) {

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above by infinity23_cron_mgmt_validate_post_request()
            $new_status = isset( $_POST['cron_edit_status'] ) ? sanitize_text_field( wp_unslash( $_POST['cron_edit_status'] ) ) : 'active';

            if( !in_array( $new_status, array( 'active', 'paused' ), true ) ) {
                $new_status = 'active';
            } // if( !in_array( $new_status, array( 'active', 'paused' ), true ) ) {

            // a one-time event is removed by WP-Cron right before it fires, so
            // blocking that single run would delete the event for good
            if( $new_status === 'paused' && empty( $old_schedule ) ) {
                wp_die( 'One-time cron events cannot be paused - pausing would delete the event when it runs.' );
            } // if( $new_status === 'paused' && empty( $old_schedule ) ) {

            $new_datetime = DateTime::createFromFormat( 'Y-m-d H:i:s', $new_date . ' ' . $new_time . ':00', wp_timezone() );

            if( empty( $new_datetime ) ) {
                wp_die( 'The date or time provided was not valid.' );
            } // if( empty( $new_datetime ) ) {

            $new_timestamp = $new_datetime->getTimestamp();

            $new_schedule = $old_schedule;

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above by infinity23_cron_mgmt_validate_post_request()
            if( !empty( $old_schedule ) && isset( $_POST['cron_edit_schedule'] ) ) {

                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above by infinity23_cron_mgmt_validate_post_request()
                $posted_schedule = sanitize_text_field( wp_unslash( $_POST['cron_edit_schedule'] ) );

                if( $posted_schedule === 'infinity23_default_interval' ) {

                    $existing_adjustment_for_default = infinity23_cron_mgmt_get_adjustment( $hook, $args );
                    $new_schedule = !empty( $existing_adjustment_for_default ) ? $existing_adjustment_for_default['original_schedule'] : $old_schedule;

                } else {

                    $available_schedules = wp_get_schedules();

                    if( empty( $posted_schedule ) || !isset( $available_schedules[ $posted_schedule ] ) ) {
                        wp_die( 'The selected interval was not valid.' );
                    } // if( empty( $posted_schedule ) || !isset( $available_schedules[ $posted_schedule ] ) ) {

                    $new_schedule = $posted_schedule;

                } // if( $posted_schedule === 'infinity23_default_interval' ) {

            } // if( !empty( $old_schedule ) && isset( $_POST['cron_edit_schedule'] ) ) {

            // use the LIVE scheduled timestamp, not the one from the page load -
            // if WP-Cron already fired since this edit page opened, the event may
            // have moved to a new timestamp, and unscheduling the stale one would
            // silently fail, leaving a duplicate event behind
            $live_timestamp_for_update = wp_next_scheduled( $hook, $args );

            if( !empty( $live_timestamp_for_update ) ) {
                wp_unschedule_event( $live_timestamp_for_update, $hook, $args );
            } // if( !empty( $live_timestamp_for_update ) ) {

            if( !empty( $new_schedule ) ) {
                wp_schedule_event( $new_timestamp, $new_schedule, $hook, $args );
            } else {
                wp_schedule_single_event( $new_timestamp, $hook, $args );
            } // if( !empty( $new_schedule ) ) {

            $existing_adjustment = infinity23_cron_mgmt_get_adjustment( $hook, $args );
            $original_schedule = !empty( $existing_adjustment ) ? $existing_adjustment['original_schedule'] : $old_schedule;
            $was_paused = ( !empty( $existing_adjustment ) && !empty( $existing_adjustment['is_paused'] ) );
            $interval_also_changed = ( !empty( $old_schedule ) && $new_schedule !== $original_schedule );

            if( $new_status === 'paused' ) {

                // the schedule set above in WordPress's own cron table is never
                // touched by pausing - only execution is blocked, see cron-pause-filter.php
                // note: original_schedule must still reflect the interval BEFORE this
                // submit, so a paused+interval-changed cron reverts correctly later
                infinity23_cron_mgmt_save_adjustment( $hook, $args, $old_schedule, true );
                $redirect_param = $interval_also_changed ? 'cron_paused_and_interval_updated' : 'cron_paused';

            } elseif( $interval_also_changed ) {

                infinity23_cron_mgmt_save_adjustment( $hook, $args, $old_schedule, false );
                $redirect_param = $was_paused ? 'cron_restored' : 'cron_interval_updated';

            } else {

                infinity23_cron_mgmt_clear_adjustment( $hook, $args );
                $redirect_param = $was_paused ? 'cron_restored' : 'cron_time_updated';

            } // if( $new_status === 'paused' ) {

            $infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-cron-management', $redirect_param => '1' ), admin_url('admin.php') );
            wp_safe_redirect( $infinity23_redirect_url );
            exit;

        } // function infinity23_cron_mgmt_handle_update_time() {

    } // if( !function_exists( 'infinity23_cron_mgmt_handle_update_time' ) ) {

    add_action( 'admin_post_infinity23_cron_mgmt_update_time', 'infinity23_cron_mgmt_handle_update_time' );

    // ---------------------------------------
    // revert changes - undoes whatever adjustment this plugin has made to the
    // cron, whether paused or interval-changed, and clears the adjustment
    // record. Paused crons never had their schedule touched, so reverting
    // just clears the record. Interval-changed crons get rescheduled back to
    // their original recurrence at the same next run timestamp.
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_handle_revert_changes' ) ) {

        function infinity23_cron_mgmt_handle_revert_changes() {

            $request = infinity23_cron_mgmt_validate_request();
            $hook = $request['hook'];
            $timestamp = $request['timestamp'];
            $row = $request['row'];
            $args = isset( $row['args'] ) ? $row['args'] : array();

            $adjustment = infinity23_cron_mgmt_get_adjustment( $hook, $args );

            if( empty( $adjustment ) ) {
                wp_die( 'This cron has no changes to revert.' );
            } // if( empty( $adjustment ) ) {

            $original_schedule = isset( $adjustment['original_schedule'] ) ? $adjustment['original_schedule'] : false;

            // use the LIVE scheduled timestamp, not the one from the page load -
            // WP-Cron may have already fired and re-added the event with the
            // changed interval at a new timestamp by the time this runs, so
            // unscheduling the stale timestamp would silently fail to match
            $live_timestamp = wp_next_scheduled( $hook, $args );

            if( !empty( $live_timestamp ) ) {

                $live_crons = _get_cron_array();
                $live_args_key = md5( serialize( $args ) );
                $live_schedule = isset( $live_crons[ $live_timestamp ][ $hook ][ $live_args_key ]['schedule'] ) ? $live_crons[ $live_timestamp ][ $hook ][ $live_args_key ]['schedule'] : false;

                // compare against the ORIGINAL schedule regardless of the paused
                // flag - a cron that was paused AND had its interval changed
                // still needs its interval restored here
                if( $live_schedule !== $original_schedule ) {

                    wp_unschedule_event( $live_timestamp, $hook, $args );

                    if( !empty( $original_schedule ) ) {
                        wp_schedule_event( $live_timestamp, $original_schedule, $hook, $args );
                    } else {
                        wp_schedule_single_event( $live_timestamp, $hook, $args );
                    } // if( !empty( $original_schedule ) ) {

                } // if( $live_schedule !== $original_schedule ) {

            } // if( !empty( $live_timestamp ) ) {

            // clearing the adjustment record also lifts the pause, since the
            // pause filter only blocks execution for hooks with a saved record
            infinity23_cron_mgmt_clear_adjustment( $hook, $args );

            $infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-cron-management', 'cron_changes_reverted' => '1' ), admin_url('admin.php') );
            wp_safe_redirect( $infinity23_redirect_url );
            exit;

        } // function infinity23_cron_mgmt_handle_revert_changes() {

    } // if( !function_exists( 'infinity23_cron_mgmt_handle_revert_changes' ) ) {

    add_action( 'admin_post_infinity23_cron_mgmt_revert_changes', 'infinity23_cron_mgmt_handle_revert_changes' );

    // ---------------------------------------
    // delete all logs - removes every saved cron log file at once
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_handle_delete_all_logs' ) ) {

        function infinity23_cron_mgmt_handle_delete_all_logs() {

            if( !current_user_can( 'manage_options' ) ) {
                wp_die( 'Permission denied.' );
            } // if( !current_user_can( 'manage_options' ) ) {

            $nonce = isset( $_POST['infinity23_cron_delete_all_logs_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['infinity23_cron_delete_all_logs_nonce'] ) ) : '';

            if( !wp_verify_nonce( $nonce, infinity23_cron_mgmt_delete_all_logs_nonce_action() ) ) {
                wp_die( 'Link expired.' );
            } // if( !wp_verify_nonce( $nonce, infinity23_cron_mgmt_delete_all_logs_nonce_action() ) ) {

            infinity23_cron_mgmt_delete_all_logs();

			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-cron-management', 'cron_tab' => 'logs', 'cron_logs_deleted' => '1' ), admin_url('admin.php') );
			wp_safe_redirect( $infinity23_redirect_url );
			exit;

		} // function infinity23_cron_mgmt_handle_delete_all_logs() {

    } // if( !function_exists( 'infinity23_cron_mgmt_handle_delete_all_logs' ) ) {

    add_action( 'admin_post_infinity23_cron_mgmt_delete_all_logs', 'infinity23_cron_mgmt_handle_delete_all_logs' );

?>
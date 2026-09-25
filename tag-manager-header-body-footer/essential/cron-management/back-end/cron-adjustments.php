<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Cron Management - unified adjustment tracking (pause + interval changes)
    // Every time this module pauses a cron or changes its interval, the original
    // schedule is saved here so it can be restored automatically when the module
    // is turned off, the Include / Exclude toggle is turned off, or the whole
    // plugin is deactivated. Adjustments are stored in their own dedicated
    // database table (see back-end/cron-db-functions.php), following the same
    // pattern used by Page Notes and Redirect 301/302.
    // ===========================================================================

    include( dirname(__FILE__) . "/cron-db-functions.php" );

    // ---------------------------------------
    // build a stable adjustment_key for a hook + args pair - stays the same
    // across reschedules, so the ORIGINAL schedule is only ever saved once
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_adjustment_name' ) ) {

        function infinity23_cron_mgmt_adjustment_name( $hook, $args ) {

            return md5( $hook . '|' . maybe_serialize( $args ) );

        } // function infinity23_cron_mgmt_adjustment_name( $hook, $args ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_adjustment_name' ) ) {

    // ---------------------------------------
    // get the saved adjustment row for a hook + args pair, or null
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_get_adjustment' ) ) {

        function infinity23_cron_mgmt_get_adjustment( $hook, $args ) {

            $adjustment_key = infinity23_cron_mgmt_adjustment_name( $hook, $args );
            $row = infinity23_cron_mgmt_db_get_by_key( $adjustment_key );

            if( empty( $row ) ) {
                return null;
            } // if( empty( $row ) ) {

            return array(
                'hook'              => $row->hook,
                'args'              => infinity23_convert_json_to_array( $row->args ),
                'original_schedule' => $row->original_schedule,
                'is_paused'         => intval( $row->is_paused ),
                'adjustment_key'    => $adjustment_key,
                'saved_on'          => $row->time_created_utc,
            );

        } // function infinity23_cron_mgmt_get_adjustment( $hook, $args ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_get_adjustment' ) ) {

    // ---------------------------------------
    // save or update an adjustment record - the original_schedule is only ever
    // written the first time a hook is touched, so later changes never lose
    // track of what the cron looked like before Infinity23 made any change to it
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_save_adjustment' ) ) {

        function infinity23_cron_mgmt_save_adjustment( $hook, $args, $current_schedule, $is_paused ) {

            $adjustment_key = infinity23_cron_mgmt_adjustment_name( $hook, $args );
            $existing = infinity23_cron_mgmt_get_adjustment( $hook, $args );

            $original_schedule = !empty( $existing ) ? $existing['original_schedule'] : $current_schedule;

            infinity23_cron_mgmt_db_upsert( $adjustment_key, $hook, infinity23_convert_array_to_json( $args ), $original_schedule, $is_paused );

            // flag read from the options cache by essential/loader.php, so the restore
            // query only ever runs when an adjustment was actually saved
            infinity23_update_option( 'cron_management_needs_restore', 1 );

        } // function infinity23_cron_mgmt_save_adjustment( $hook, $args, $current_schedule, $is_paused ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_save_adjustment' ) ) {

    // ---------------------------------------
    // remove an adjustment record once a cron is fully back on its original
    // schedule and is no longer paused, so it stops being tracked
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_clear_adjustment' ) ) {

        function infinity23_cron_mgmt_clear_adjustment( $hook, $args ) {

            $adjustment_key = infinity23_cron_mgmt_adjustment_name( $hook, $args );
            infinity23_cron_mgmt_db_delete( $adjustment_key );

        } // function infinity23_cron_mgmt_clear_adjustment( $hook, $args ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_clear_adjustment' ) ) {

    // ---------------------------------------
    // restore every tracked INTERVAL CHANGE back to its original schedule -
    // pausing never touches the real WP schedule (see cron-pause-filter.php),
    // so only interval changes need restoring here. Called when the module is
    // deactivated, toggled off in Include / Exclude, or the whole plugin is
    // deactivated - an interval change is a real mutation to WordPress's own
    // cron table and must not be left behind once this plugin stops running.
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_restore_all_interval_changes' ) ) {

        function infinity23_cron_mgmt_restore_all_interval_changes() {

            $adjustment_rows = infinity23_cron_mgmt_db_get_all();

            if( empty( $adjustment_rows ) ) {
                infinity23_delete_option( 'cron_management_needs_restore' );
                return;
            } // if( empty( $adjustment_rows ) ) {

            foreach( $adjustment_rows as $adjustment_row ) {

                $hook = $adjustment_row->hook;
                $args = infinity23_convert_json_to_array( $adjustment_row->args );
                $original_schedule = $adjustment_row->original_schedule;

                if( empty( $hook ) ) {
                    infinity23_cron_mgmt_db_delete( $adjustment_row->adjustment_key );
                    continue;
                } // if( empty( $hook ) ) {

                $next_scheduled = wp_next_scheduled( $hook, $args );

                // compare against the ORIGINAL schedule regardless of the paused
                // flag - a cron that was paused AND had its interval changed
                // still needs its interval restored here
                if( $next_scheduled ) {

                    $live_crons = _get_cron_array();
                    $live_args_key = md5( serialize( $args ) );
                    $live_schedule = isset( $live_crons[ $next_scheduled ][ $hook ][ $live_args_key ]['schedule'] ) ? $live_crons[ $next_scheduled ][ $hook ][ $live_args_key ]['schedule'] : false;

                    if( $live_schedule !== $original_schedule ) {

                        wp_unschedule_event( $next_scheduled, $hook, $args );

                        if( !empty( $original_schedule ) ) {
                            wp_schedule_event( $next_scheduled, $original_schedule, $hook, $args );
                        } else {
                            wp_schedule_single_event( $next_scheduled, $hook, $args );
                        } // if( !empty( $original_schedule ) ) {

                    } // if( $live_schedule !== $original_schedule ) {

                } // if( $next_scheduled ) {

                infinity23_cron_mgmt_db_delete( $adjustment_row->adjustment_key );

            } // foreach( $adjustment_rows as $adjustment_row ) {

            // every adjustment is restored - clear the flag so essential/loader.php
            // stops running this function on the next requests
            infinity23_delete_option( 'cron_management_needs_restore' );

        } // function infinity23_cron_mgmt_restore_all_interval_changes() {

    } // if( !function_exists( 'infinity23_cron_mgmt_restore_all_interval_changes' ) ) {

    register_deactivation_hook( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . "/index.php", 'infinity23_cron_mgmt_restore_all_interval_changes' );

?>
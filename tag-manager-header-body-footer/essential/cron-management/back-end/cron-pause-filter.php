<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Cron Management - non-destructive pause filter
    // A "paused" cron is NEVER removed from WordPress's own cron schedule. This
    // only blocks the hook's OTHER registered callbacks from running for that
    // one scheduled instance, for as long as this plugin/module is active.
    // WordPress core still unschedules and reschedules the event on every due
    // run before firing do_action(), so the schedule keeps ticking untouched.
    // Deactivating the module or plugin simply stops this filter from being
    // registered on the next request - nothing needs to be restored.
    // ===========================================================================

    if( !function_exists( 'infinity23_cron_mgmt_get_paused_signatures' ) ) {

        function infinity23_cron_mgmt_get_paused_signatures() {

            $adjustment_rows = infinity23_cron_mgmt_db_get_all();
            $paused_signatures = array();

            if( empty( $adjustment_rows ) ) {
                return $paused_signatures;
            } // if( empty( $adjustment_rows ) ) {

            foreach( $adjustment_rows as $adjustment_row ) {

                if( empty( $adjustment_row->is_paused ) ) {
                    continue;
                } // if( empty( $adjustment_row->is_paused ) ) {

                $hook = $adjustment_row->hook;
                $args = infinity23_convert_json_to_array( $adjustment_row->args );

                if( empty( $hook ) ) {
                    continue;
                } // if( empty( $hook ) ) {

                $paused_signatures[ $hook . '|' . maybe_serialize( $args ) ] = true;

            } // foreach( $adjustment_rows as $adjustment_row ) {

            return $paused_signatures;

        } // function infinity23_cron_mgmt_get_paused_signatures() {

    } // if( !function_exists( 'infinity23_cron_mgmt_get_paused_signatures' ) ) {

    if( !function_exists( 'infinity23_cron_mgmt_block_paused_hooks' ) ) {

        function infinity23_cron_mgmt_block_paused_hooks() {

            $paused_signatures = infinity23_cron_mgmt_get_paused_signatures();

            if( empty( $paused_signatures ) ) {
                return;
            } // if( empty( $paused_signatures ) ) {

            $paused_hooks = array();

            foreach( array_keys( $paused_signatures ) as $signature ) {
                $hook = explode( '|', $signature )[0];
                $paused_hooks[ $hook ] = true;
            } // foreach( array_keys( $paused_signatures ) as $signature ) {

            foreach( array_keys( $paused_hooks ) as $hook ) {

                add_action( $hook, function() use ( $hook, $paused_signatures ) {

                    $current_args = func_get_args();
                    $current_signature = $hook . '|' . maybe_serialize( $current_args );

                    if( isset( $paused_signatures[ $current_signature ] ) ) {

                        global $wp_filter;

                        // keep every callback of this hook, so the paused instance is
                        // the only one skipped - later instances of the same hook in
                        // this cron run (with other args) must still run normally
                        $infinity23_saved_callbacks = isset( $wp_filter[ $hook ] ) ? $wp_filter[ $hook ]->callbacks : array();

                        remove_all_actions( $hook );

                        $infinity23_callbacks_restored = false;

                        $infinity23_restore_callbacks = function( $pre = null ) use ( $hook, $infinity23_saved_callbacks, &$infinity23_callbacks_restored ) {

                            if( $infinity23_callbacks_restored ) {
                                return $pre;
                            } // if( $infinity23_callbacks_restored ) {

                            $infinity23_callbacks_restored = true;

                            foreach( $infinity23_saved_callbacks as $infinity23_priority => $infinity23_priority_callbacks ) {

                                foreach( $infinity23_priority_callbacks as $infinity23_callback_data ) {
                                    add_action( $hook, $infinity23_callback_data['function'], $infinity23_priority, $infinity23_callback_data['accepted_args'] );
                                } // foreach( $infinity23_priority_callbacks as $infinity23_callback_data ) {

                            } // foreach( $infinity23_saved_callbacks as $infinity23_priority => $infinity23_priority_callbacks ) {

                            return $pre;

                        };

                        // wp-cron.php unschedules every event right before firing it, so
                        // the callbacks are back before the next event of this request
                        // runs - shutdown is the fallback for anything fired after the loop
                        add_filter( 'pre_unschedule_event', $infinity23_restore_callbacks, -9999 );
                        add_action( 'shutdown', $infinity23_restore_callbacks, -9999 );

                    } // if( isset( $paused_signatures[ $current_signature ] ) ) {

                }, -9999, 20 );

            } // foreach( array_keys( $paused_hooks ) as $hook ) {

        } // function infinity23_cron_mgmt_block_paused_hooks() {

    } // if( !function_exists( 'infinity23_cron_mgmt_block_paused_hooks' ) ) {

?>
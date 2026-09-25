<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Cron Management - throttle/disable WP-Cron's automatic run and expose
    // a keyed external cron URL so it can still run through cron-job.org
    // ===========================================================================

    // ---------------------------------------
    // the url to give to an external cron service - carries the password as
    // a url parameter and always works, including when Fully Disable is set
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_external_cron_url' ) ) {

        function infinity23_cron_mgmt_external_cron_url( $password ) {

            // never pass doing_wp_cron here - wp-cron.php treats that parameter as its
            // internal lock value and exits when it does not match the stored lock
            return add_query_arg(
                array(
                    'infinity23_cron_key' => rawurlencode( $password ),
                ),
                site_url( 'wp-cron.php' )
            );

        } // function infinity23_cron_mgmt_external_cron_url( $password ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_external_cron_url' ) ) {

    // ---------------------------------------
    // the actual throttle/disable check - only runs while WP-Cron is executing
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_throttle_check' ) ) {

        function infinity23_cron_mgmt_throttle_check( $disable_wp_built_in_cron, $password ) {

            if( empty( $disable_wp_built_in_cron ) || $disable_wp_built_in_cron === 'default' ) {
                return;
            } // if( empty( $disable_wp_built_in_cron ) || $disable_wp_built_in_cron === 'default' ) {

            if( !defined( 'DOING_CRON' ) || !DOING_CRON ) {
                return;
            } // if( !defined( 'DOING_CRON' ) || !DOING_CRON ) {

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- external cron auth key from cron-job.org, not a WP session action; compared with hash_equals() below, a nonce cannot apply to a non-interactive cross-session request
            $request_key = isset( $_GET['infinity23_cron_key'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_cron_key'] ) ) : '';

            if( empty( $request_key ) && isset( $_SERVER['HTTP_X_INFINITY23_CRON_KEY'] ) ) {
                $request_key = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_INFINITY23_CRON_KEY'] ) );
            } // if( empty( $request_key ) && isset( $_SERVER['HTTP_X_INFINITY23_CRON_KEY'] ) ) {

            if( $disable_wp_built_in_cron === 'fully_disabled' ) {

                if( empty( $password ) || !hash_equals( strval( $password ), strval( $request_key ) ) ) {
                    wp_die( 'WP-Cron is disabled on this site. It can only run through the External Cron URL.', 'Cron Disabled', array( 'response' => 403 ) );
                } // if( !hash_equals( strval( $password ), strval( $request_key ) ) ) {

                return;

            } // if( $disable_wp_built_in_cron === 'fully_disabled' ) {

            if( !empty( $password ) && hash_equals( strval( $password ), strval( $request_key ) ) ) {
                return;
            } // if( !empty( $password ) && hash_equals( strval( $password ), strval( $request_key ) ) ) {

            $interval_seconds = 300;

            if( $disable_wp_built_in_cron === '3_minutes' ) {
                $interval_seconds = 180;
            } elseif( $disable_wp_built_in_cron === '5_minutes' ) {
                $interval_seconds = 300;
            } elseif( $disable_wp_built_in_cron === '10_minutes' ) {
                $interval_seconds = 600;
            } // if( $disable_wp_built_in_cron === '3_minutes' ) {

            $last_run = get_transient( 'infinity23_cron_mgmt_last_run' );
            $current_time = time();

            if( $last_run && ( $current_time - $last_run ) < $interval_seconds ) {

                $time_remaining = $interval_seconds - ( $current_time - $last_run );
                wp_die( 'WP-Cron was throttled by Infinity23. Please wait ' . intval( ceil( $time_remaining / 60 ) ) . ' more minute(s).', 'Cron Throttled', array( 'response' => 429 ) );

            } // if( $last_run && ( $current_time - $last_run ) < $interval_seconds ) {

            set_transient( 'infinity23_cron_mgmt_last_run', $current_time, $interval_seconds * 2 );

        } // function infinity23_cron_mgmt_throttle_check( $disable_wp_built_in_cron, $password ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_throttle_check' ) ) {

    // ---------------------------------------
    // entry point called from loader.php - hooks the check in early on wp_loaded
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_init_throttle' ) ) {

        function infinity23_cron_mgmt_init_throttle( $disable_wp_built_in_cron, $password ) {

            if( empty( $disable_wp_built_in_cron ) || $disable_wp_built_in_cron === 'default' ) {
                return;
            } // if( empty( $disable_wp_built_in_cron ) || $disable_wp_built_in_cron === 'default' ) {

            // WP-CLI defines DOING_CRON for "wp cron" commands - never block it
            if( defined( 'WP_CLI' ) && WP_CLI ) {
                return;
            } // if( defined( 'WP_CLI' ) && WP_CLI ) {

            // on normal page loads with Fully Disable, tell WordPress there are no
            // due jobs so spawn_cron() never fires its loopback request at all -
            // real cron requests (external url) skip this and go through the check below
            if( $disable_wp_built_in_cron === 'fully_disabled' && ( !defined( 'DOING_CRON' ) || !DOING_CRON ) ) {
                add_filter( 'pre_get_ready_cron_jobs', '__return_empty_array' );
            } // if( $disable_wp_built_in_cron === 'fully_disabled' && ( !defined( 'DOING_CRON' ) || !DOING_CRON ) ) {

            add_action( 'wp_loaded', function() use ( $disable_wp_built_in_cron, $password ) {
                infinity23_cron_mgmt_throttle_check( $disable_wp_built_in_cron, $password );
            }, -999 );

        } // function infinity23_cron_mgmt_init_throttle( $disable_wp_built_in_cron, $password ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_init_throttle' ) ) {

?>
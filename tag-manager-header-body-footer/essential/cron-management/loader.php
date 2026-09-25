<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading cron management page and data
    // ===========================================================================

    include( dirname(__FILE__) . "/internal-settings.php" );
    include( dirname(__FILE__) . "/../../include/settings.php" );

    // ===========================================================================
    // Loading admin page - admin only
    // ===========================================================================

    global $infinity23_essential_modules_array;

    if( is_admin() ) {

        add_action( 'admin_menu', function() {

            global $infinity23_essential_modules_array;

            $infinity23_cron_management_hook = add_submenu_page(
                'essential-include-exclude',
                $infinity23_essential_modules_array['cron_management']['module_display_name'],
                $infinity23_essential_modules_array['cron_management']['module_display_name'],
                'manage_options',
                $infinity23_essential_modules_array['cron_management']['admin_page_slug'],
                function() {
                    include( dirname(__FILE__) . "/admin-output.php" );
                }
            );

            add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_cron_management_hook ) {

                if( $hook !== $infinity23_cron_management_hook ) { return; }

                infinity23_enqueue_essential_styles();

            } ); // add_action( 'admin_enqueue_scripts'

        } ); // add_action( 'admin_menu'

        $infinity23_cron_mgmt_menu_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
        include( dirname(__FILE__) . "/default-values.php" );
        $infinity23_cron_mgmt_hide_menu = intval( infinity23_get_data( $infinity23_essential_modules_array['cron_management']['hide_menu_field'], $infinity23_cron_mgmt_menu_settings_data, $infinity23_defaults ) );

        if( $infinity23_cron_mgmt_hide_menu == 1 ) {

            global $infinity23_hidden_menu_slugs;
            $infinity23_hidden_menu_slugs[] = $infinity23_essential_modules_array['cron_management']['admin_page_slug'];

        } // if( $infinity23_cron_mgmt_hide_menu == 1 ) {

    } // if( is_admin() ) {

    // ===========================================================================
    // load row url/nonce helper functions unconditionally - so admin-output.php
    // can build (locked) action links even when the activation code is off
    // ===========================================================================

    include( dirname(__FILE__) . "/back-end/cron-row-helpers.php" );
    include( dirname(__FILE__) . "/back-end/cron-adjustments.php" );

    // ===========================================================================
    // Register extra intervals (weekly, monthly) so they show up alongside the
    // WordPress defaults everywhere a schedule is picked from
    // ===========================================================================

    if( !function_exists( 'infinity23_cron_mgmt_add_extra_schedules' ) ) {

        function infinity23_cron_mgmt_add_extra_schedules( $schedules ) {

            if( !isset( $schedules['infinity23_5_minutes'] ) ) {
                $schedules['infinity23_5_minutes'] = array(
                    'interval' => 300,
                    'display'  => 'Every 5 Minutes',
                );
            } // if( !isset( $schedules['infinity23_5_minutes'] ) ) {

            if( !isset( $schedules['infinity23_15_minutes'] ) ) {
                $schedules['infinity23_15_minutes'] = array(
                    'interval' => 900,
                    'display'  => 'Every 15 Minutes',
                );
            } // if( !isset( $schedules['infinity23_15_minutes'] ) ) {

            if( !isset( $schedules['infinity23_30_minutes'] ) ) {
                $schedules['infinity23_30_minutes'] = array(
                    'interval' => 1800,
                    'display'  => 'Every 30 Minutes',
                );
            } // if( !isset( $schedules['infinity23_30_minutes'] ) ) {

            if( !isset( $schedules['infinity23_45_minutes'] ) ) {
                $schedules['infinity23_45_minutes'] = array(
                    'interval' => 2700,
                    'display'  => 'Every 45 Minutes',
                );
            } // if( !isset( $schedules['infinity23_45_minutes'] ) ) {

            if( !isset( $schedules['infinity23_3_hours'] ) ) {
                $schedules['infinity23_3_hours'] = array(
                    'interval' => 10800,
                    'display'  => 'Every 3 Hours',
                );
            } // if( !isset( $schedules['infinity23_3_hours'] ) ) {

            if( !isset( $schedules['infinity23_6_hours'] ) ) {
                $schedules['infinity23_6_hours'] = array(
                    'interval' => 21600,
                    'display'  => 'Every 6 Hours',
                );
            } // if( !isset( $schedules['infinity23_6_hours'] ) ) {

            if( !isset( $schedules['infinity23_12_hours'] ) ) {
                $schedules['infinity23_12_hours'] = array(
                    'interval' => 43200,
                    'display'  => 'Every 12 Hours',
                );
            } // if( !isset( $schedules['infinity23_12_hours'] ) ) {

            if( !isset( $schedules['weekly'] ) ) {
                $schedules['weekly'] = array(
                    'interval' => 604800,
                    'display'  => 'Once Weekly',
                );
            } // if( !isset( $schedules['weekly'] ) ) {

            if( !isset( $schedules['infinity23_monthly'] ) ) {
                $schedules['infinity23_monthly'] = array(
                    'interval' => 2592000,
                    'display'  => 'Once Monthly',
                );
            } // if( !isset( $schedules['infinity23_monthly'] ) ) {

            return $schedules;

        } // function infinity23_cron_mgmt_add_extra_schedules( $schedules ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_add_extra_schedules' ) ) {

    add_filter( 'cron_schedules', 'infinity23_cron_mgmt_add_extra_schedules' );

    // ===========================================================================
    // Loading cron log functions and logging cron runs when the module is
    // active and the "Save Cron Logs" checkbox is enabled - runs on every
    // request (not just admin) so it also fires during wp-cron.php requests
    // ===========================================================================

    $infinity23_cron_management_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

    // log helpers are also needed by admin-output.php (log list / viewer) when the
    // module is off, so they load on admin too - throttle and pause only when active
    if( $infinity23_cron_management_code_is_active == 1 || is_admin() ) {
        include( dirname(__FILE__) . "/back-end/cron-log-functions.php" );
    } // if( $infinity23_cron_management_code_is_active == 1 || is_admin() ) {

    if( $infinity23_cron_management_code_is_active == 1 ) {

        include( dirname(__FILE__) . "/back-end/cron-throttle.php" );
        include( dirname(__FILE__) . "/back-end/cron-pause-filter.php" );

        $infinity23_cron_mgmt_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
        include( dirname(__FILE__) . "/default-values.php" );
        $infinity23_cron_mgmt_log_enabled = intval( infinity23_pro_get_data( 'cron_log_enabled', $infinity23_cron_mgmt_settings_data, $infinity23_defaults ) );
        $infinity23_cron_mgmt_enable_cron_actions = intval( infinity23_pro_get_data( 'enable_cron_actions', $infinity23_cron_mgmt_settings_data, $infinity23_defaults ) );
        $infinity23_cron_mgmt_disable_wp_built_in_cron = infinity23_pro_get_data( 'disable_wp_built_in_cron', $infinity23_cron_mgmt_settings_data, $infinity23_defaults );
        $infinity23_cron_mgmt_external_cron_password = infinity23_decrypt_value( infinity23_pro_get_data( 'external_cron_password', $infinity23_cron_mgmt_settings_data, $infinity23_defaults ), 'cron_management_external_password' );

        infinity23_cron_mgmt_init_logging( $infinity23_cron_mgmt_log_enabled );
        infinity23_cron_mgmt_init_throttle( $infinity23_cron_mgmt_disable_wp_built_in_cron, $infinity23_cron_mgmt_external_cron_password );

        // scheduled hooks only fire during a cron request, so the paused-hook
        // lookup is skipped on normal page loads to save a query per request
        if( wp_doing_cron() ) {
            infinity23_cron_mgmt_block_paused_hooks();
        } // if( wp_doing_cron() ) {

    } // if( $infinity23_cron_management_code_is_active == 1 ) {

    // ===========================================================================
    // Loading the cron row action handlers if the code is active
    // ===========================================================================

    if( is_admin() && $infinity23_cron_management_code_is_active == 1 ) {
        include( dirname(__FILE__) . "/back-end/cron-actions.php" );
    } // if( is_admin() && $infinity23_cron_management_code_is_active == 1 ) {

?>
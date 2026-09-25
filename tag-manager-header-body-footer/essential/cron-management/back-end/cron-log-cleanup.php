<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Cron Management - log folder path and cleanup only
    // Kept separate from cron-log-functions.php so the hourly cron can load
    // just this file for cleanup, without pulling in the logging/tracking code
    // ===========================================================================

    // ---------------------------------------
    // shared storage folder path - wp-content/infinity23/cron-log
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_get_log_folder' ) ) {

        function infinity23_cron_mgmt_get_log_folder() {

            return infinity23_get_storage_root_folder( 'cron-log' );

        } // function infinity23_cron_mgmt_get_log_folder() {

    } // if( !function_exists( 'infinity23_cron_mgmt_get_log_folder' ) ) {

    // ---------------------------------------
    // create the shared infinity23 root folder and the cron-log subfolder,
    // with a blank index.php in each to block directory listing
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_create_log_folder' ) ) {

        function infinity23_cron_mgmt_create_log_folder() {

            $root_folder = infinity23_get_storage_root_folder();
            $log_folder = infinity23_cron_mgmt_get_log_folder();

            if( !file_exists( $root_folder ) ) {
                wp_mkdir_p( $root_folder );
            } // if( !file_exists( $root_folder ) ) {

            if( !file_exists( $root_folder . '/index.php' ) ) {
                // phpcs:ignore PluginCheck.CodeAnalysis.WriteFile.PluginDirectoryWrite -- not the plugin's own folder (wp-content/plugins/infinity23/), which IS wiped on upgrade; $root_folder comes from infinity23_get_storage_root_folder(), a wp_upload_dir()-based path that survives an upgrade
                @file_put_contents( $root_folder . '/index.php', '' );
            } // if( !file_exists( $root_folder . '/index.php' ) ) {

            if( !file_exists( $log_folder ) ) {

                $created = wp_mkdir_p( $log_folder );

                if( !$created ) {
                    return false;
                } // if( !$created ) {

            } // if( !file_exists( $log_folder ) ) {

            if( !file_exists( $log_folder . '/index.php' ) ) {
                @file_put_contents( $log_folder . '/index.php', '' );
            } // if( !file_exists( $log_folder . '/index.php' ) ) {

            return true;

        } // function infinity23_cron_mgmt_create_log_folder() {

    } // if( !function_exists( 'infinity23_cron_mgmt_create_log_folder' ) ) {

    // ---------------------------------------
    // delete log files older than the retention setting - filenames are
    // parsed from their dd-mm-Y.log name, not their filesystem date
    // called from hourly-cron.php on the shared hourly cron
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_cleanup_old_logs' ) ) {

        function infinity23_cron_mgmt_cleanup_old_logs( $days_to_keep ) {

            $log_folder = infinity23_cron_mgmt_get_log_folder();

            if( !file_exists( $log_folder ) || !is_dir( $log_folder ) ) {
                return;
            } // if( !file_exists( $log_folder ) || !is_dir( $log_folder ) ) {

            $days_to_keep = max( 1, intval( $days_to_keep ) );

            // midnight today in the site timezone, minus (days - 1) whole days -
            // keeps exactly $days_to_keep daily files including today's
            $site_timezone = wp_timezone();
            $today_midnight = new DateTime( 'today', $site_timezone );
            $cutoff_time = $today_midnight->getTimestamp() - ( ( $days_to_keep - 1 ) * DAY_IN_SECONDS );

            $log_files = glob( $log_folder . '/*.log.php' );

            if( empty( $log_files ) ) {
                return;
            } // if( empty( $log_files ) ) {

            foreach( $log_files as $log_file ) {

                $filename = basename( $log_file );
                $date_parts = explode( '.', $filename )[0];
                // the ! resets unparsed fields to midnight instead of the current time
                $file_date = DateTime::createFromFormat( '!d-m-Y', $date_parts, $site_timezone );

                if( $file_date ) {

                    $file_timestamp = $file_date->getTimestamp();

                    if( $file_timestamp < $cutoff_time ) {
                        wp_delete_file( $log_file );
                    } // if( $file_timestamp < $cutoff_time ) {

                } // if( $file_date ) {

            } // foreach( $log_files as $log_file ) {

        } // function infinity23_cron_mgmt_cleanup_old_logs( $days_to_keep ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_cleanup_old_logs' ) ) {

    // ---------------------------------------
    // delete every saved cron log file at once - used by the "Delete All Cron
    // Logs" button in the admin-output.php Cron Log Files section
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_delete_all_logs' ) ) {

        function infinity23_cron_mgmt_delete_all_logs() {

            $log_folder = infinity23_cron_mgmt_get_log_folder();

            if( !file_exists( $log_folder ) || !is_dir( $log_folder ) ) {
                return;
            } // if( !file_exists( $log_folder ) || !is_dir( $log_folder ) ) {

            $all_files = glob( $log_folder . '/*' );

            if( !empty( $all_files ) ) {

                foreach( $all_files as $single_file ) {

                    if( is_file( $single_file ) ) {
                        wp_delete_file( $single_file );
                    } // if( is_file( $single_file ) ) {

                } // foreach( $all_files as $single_file ) {

            } // if( !empty( $all_files ) ) {

            global $wp_filesystem;

            if( !function_exists( 'WP_Filesystem' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            } // if( !function_exists( 'WP_Filesystem' ) ) {

            WP_Filesystem();

            // null on hosts where WP_Filesystem() needs ftp credentials - calling
            // rmdir() on it fataled the handler after the log files were gone
            if( empty( $wp_filesystem ) ) {
                return;
            } // if( empty( $wp_filesystem ) ) {

            $wp_filesystem->rmdir( $log_folder );

        } // function infinity23_cron_mgmt_delete_all_logs() {

    } // if( !function_exists( 'infinity23_cron_mgmt_delete_all_logs' ) ) {

?>
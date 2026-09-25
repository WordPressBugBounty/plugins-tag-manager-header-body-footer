<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Cron Management - cron run logging
    // Only loaded from loader.php - functions here are safe to call even when
    // logging is disabled, the enabled check happens in loader.php
    // ===========================================================================

    // ---------------------------------------
    // folder path + cleanup functions live in cron-log-cleanup.php so the
    // hourly cron can load just that file without pulling in tracking code
    // ---------------------------------------

    include( dirname(__FILE__) . "/cron-log-cleanup.php" );

    // ---------------------------------------
    // size limits for the daily log files - a log file stops growing once it
    // reaches the write limit (every anonymous /wp-cron.php hit writes to it),
    // and the admin viewer only reads the last part of a file past the view limit
    // ---------------------------------------

    if( !defined( 'INFINITY23_CRON_MGMT_LOG_MAX_BYTES' ) ) {
        define( 'INFINITY23_CRON_MGMT_LOG_MAX_BYTES', 5 * 1024 * 1024 );
    } // if( !defined( 'INFINITY23_CRON_MGMT_LOG_MAX_BYTES' ) ) {

    if( !defined( 'INFINITY23_CRON_MGMT_LOG_VIEW_MAX_BYTES' ) ) {
        define( 'INFINITY23_CRON_MGMT_LOG_VIEW_MAX_BYTES', 1024 * 1024 );
    } // if( !defined( 'INFINITY23_CRON_MGMT_LOG_VIEW_MAX_BYTES' ) ) {

    // ---------------------------------------
    // current time formatted like [13:49:05.221] - uses the site timezone
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_get_exact_time' ) ) {

        function infinity23_cron_mgmt_get_exact_time() {

            $exact_time = wp_date( 'H:i:s' ) . '.' . sprintf( '%03d', intval( microtime( true ) * 1000 ) % 1000 );

            return $exact_time;

        } // function infinity23_cron_mgmt_get_exact_time() {

    } // if( !function_exists( 'infinity23_cron_mgmt_get_exact_time' ) ) {

    // ---------------------------------------
    // append a line to today's log file, one file per day named dd-mm-Y.log
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_log_message' ) ) {

        function infinity23_cron_mgmt_log_message( $message ) {

            if( !infinity23_cron_mgmt_create_log_folder() ) {
                if( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- only runs when WP_DEBUG is on and the plugin's own log write already failed
                    error_log( $message );
                } // if( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                return;
            } // if( !infinity23_cron_mgmt_create_log_folder() ) {

            $log_folder = infinity23_cron_mgmt_get_log_folder();
            $today_date = wp_date( 'd-m-Y' );
            $log_file = $log_folder . '/' . $today_date . '.log.php';

            if( !file_exists( $log_file ) ) {
                @file_put_contents( $log_file, "<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>\n" );
            } // if( !file_exists( $log_file ) ) {

            // today's file reached its size limit - stop writing to it so repeated
            // wp-cron.php requests can never fill the disk
            clearstatcache( true, $log_file );

            if( filesize( $log_file ) >= INFINITY23_CRON_MGMT_LOG_MAX_BYTES ) {
                return;
            } // if( filesize( $log_file ) >= INFINITY23_CRON_MGMT_LOG_MAX_BYTES ) {

            $result = @file_put_contents( $log_file, $message . "\n", FILE_APPEND | LOCK_EX );

            if( $result === false && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- only runs when WP_DEBUG is on and the plugin's own log write already failed
                error_log( "Failed to write to cron log file: $log_file" );
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- only runs when WP_DEBUG is on and the plugin's own log write already failed
                error_log( $message );
            } // if( $result === false && defined( 'WP_DEBUG' ) && WP_DEBUG ) {

        } // function infinity23_cron_mgmt_log_message( $message ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_log_message' ) ) {

    // ---------------------------------------
    // log the overall start/end of this wp-cron request - runs immediately
    // when this file loads during a DOING_CRON request
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_track_cron_runs' ) ) {

        function infinity23_cron_mgmt_track_cron_runs() {

            if( !defined( 'DOING_CRON' ) || !DOING_CRON ) {
                return;
            } // if( !defined( 'DOING_CRON' ) || !DOING_CRON ) {

            $cron_run_id = uniqid();

            infinity23_cron_mgmt_log_message( "========== [" . infinity23_cron_mgmt_get_exact_time() . "] cron: {$cron_run_id}" . " ==========" );

            $cron_run_start = microtime( true );

            add_action( 'shutdown', function() use ( $cron_run_start ) {

                $duration = round( microtime( true ) - $cron_run_start, 2 );
                infinity23_cron_mgmt_log_message( "========== END: ({$duration}s)" );

            }, 9999 );

        } // function infinity23_cron_mgmt_track_cron_runs() {

    } // if( !function_exists( 'infinity23_cron_mgmt_track_cron_runs' ) ) {

    // ---------------------------------------
    // log each individual scheduled hook's start/end time and duration -
    // registered on wp_loaded so every hook due to run this request is caught
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_track_individual_hooks' ) ) {

        function infinity23_cron_mgmt_track_individual_hooks() {

            if( !defined( 'DOING_CRON' ) || !DOING_CRON ) {
                return;
            } // if( !defined( 'DOING_CRON' ) || !DOING_CRON ) {

            $crons_list = _get_cron_array();

            if( empty( $crons_list ) ) {
                return;
            } // if( empty( $crons_list ) ) {

            $tracked_hooks = array();

            foreach( $crons_list as $timestamp => $cronhooks ) {

                foreach( $cronhooks as $hook => $events ) {

                    // the same hook can sit at several timestamps - track it once
                    if( isset( $tracked_hooks[ $hook ] ) ) {
                        continue;
                    } // if( isset( $tracked_hooks[ $hook ] ) ) {

                    $tracked_hooks[ $hook ] = true;

                    add_action( $hook, function() use ( $hook ) {

                        global $infinity23_cron_mgmt_hook_start_times;

                        $infinity23_cron_mgmt_hook_start_times[ $hook ] = microtime( true );
                        infinity23_cron_mgmt_log_message( "[" . infinity23_cron_mgmt_get_exact_time() . "] Start: " . $hook );

                    }, -9999 );

                    add_action( $hook, function() use ( $hook ) {

                        global $infinity23_cron_mgmt_hook_start_times;

                        $start_time = isset( $infinity23_cron_mgmt_hook_start_times[ $hook ] ) ? $infinity23_cron_mgmt_hook_start_times[ $hook ] : microtime( true );
                        $duration = round( microtime( true ) - $start_time, 2 );
                        infinity23_cron_mgmt_log_message( "[" . infinity23_cron_mgmt_get_exact_time() . "] End: " . $hook . " (" . $duration . "s)" );

                    }, 9999 );

                } // foreach( $cronhooks as $hook => $events ) {

            } // foreach( $crons_list as $timestamp => $cronhooks ) {

        } // function infinity23_cron_mgmt_track_individual_hooks() {

    } // if( !function_exists( 'infinity23_cron_mgmt_track_individual_hooks' ) ) {

    // ---------------------------------------
    // entry point called from loader.php - starts logging only when the
    // "Save Cron Logs" checkbox (cron_log_enabled) is on
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_init_logging' ) ) {

        function infinity23_cron_mgmt_init_logging( $cron_log_enabled ) {

            if( $cron_log_enabled != 1 ) {
                return;
            } // if( $cron_log_enabled != 1 ) {

            infinity23_cron_mgmt_track_cron_runs();

            add_action( 'wp_loaded', 'infinity23_cron_mgmt_track_individual_hooks' );

        } // function infinity23_cron_mgmt_init_logging( $cron_log_enabled ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_init_logging' ) ) {

    // ---------------------------------------
    // list all saved log files (date, size), sorted newest first - used by
    // the admin log viewer to build the file list and the day-switch tabs
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_list_log_files' ) ) {

        function infinity23_cron_mgmt_list_log_files() {

            $log_folder = infinity23_cron_mgmt_get_log_folder();

            if( !file_exists( $log_folder ) || !is_dir( $log_folder ) ) {
                return array();
            } // if( !file_exists( $log_folder ) || !is_dir( $log_folder ) ) {

            $log_files = glob( $log_folder . '/*.log.php' );

            if( empty( $log_files ) ) {
                return array();
            } // if( empty( $log_files ) ) {

            $files_list = array();

            foreach( $log_files as $log_file ) {

                $filename = basename( $log_file );
                $date_parts = explode( '.', $filename )[0];
                $file_date = DateTime::createFromFormat( 'd-m-Y', $date_parts );

                if( !$file_date ) {
                    continue;
                } // if( !$file_date ) {

                $files_list[] = array(
                    'filename'     => $filename,
                    'timestamp'    => $file_date->getTimestamp(),
                    'date_display' => $file_date->format( 'd/m/Y' ),
                    'size'         => filesize( $log_file ),
                );

            } // foreach( $log_files as $log_file ) {

            $sort_column = array();

            foreach( $files_list as $file_item ) {
                $sort_column[] = $file_item['timestamp'];
            } // foreach( $files_list as $file_item ) {

            array_multisort( $sort_column, SORT_DESC, $files_list );

            return $files_list;

        } // function infinity23_cron_mgmt_list_log_files() {

    } // if( !function_exists( 'infinity23_cron_mgmt_list_log_files' ) ) {

    // ---------------------------------------
    // format a byte size into a short human readable string for the file list
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_format_log_size' ) ) {

        function infinity23_cron_mgmt_format_log_size( $bytes ) {

            if( $bytes >= 1048576 ) {
                return round( $bytes / 1048576, 2 ) . ' MB';
            } // if( $bytes >= 1048576 ) {

            if( $bytes >= 1024 ) {
                return round( $bytes / 1024, 2 ) . ' KB';
            } // if( $bytes >= 1024 ) {

            return $bytes . ' bytes';

        } // function infinity23_cron_mgmt_format_log_size( $bytes ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_format_log_size' ) ) {

    // ---------------------------------------
    // strictly validate a log filename matches dd-mm-Y.log before any file
    // read is attempted, using the same real date check as the cleanup
    // function so a value like 99-99-9999.log is also rejected
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_is_valid_log_filename' ) ) {

        function infinity23_cron_mgmt_is_valid_log_filename( $filename ) {

            if( !preg_match( '/^[0-9]{2}-[0-9]{2}-[0-9]{4}\.log\.php$/', $filename ) ) {
                return false;
            } // if( !preg_match(

            $date_parts = explode( '.', $filename )[0];
            $file_date = DateTime::createFromFormat( 'd-m-Y', $date_parts );

            if( !$file_date || $file_date->format( 'd-m-Y' ) !== $date_parts ) {
                return false;
            } // if( !$file_date || $file_date->format( 'd-m-Y' ) !== $date_parts ) {

            return true;

        } // function infinity23_cron_mgmt_is_valid_log_filename( $filename ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_is_valid_log_filename' ) ) {

    // ---------------------------------------
    // safely read a single log file's raw content - always validates the
    // filename first so this can never be used to read outside the log folder
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_read_log_file' ) ) {

        function infinity23_cron_mgmt_read_log_file( $filename ) {

            if( !infinity23_cron_mgmt_is_valid_log_filename( $filename ) ) {
                return false;
            } // if( !infinity23_cron_mgmt_is_valid_log_filename( $filename ) ) {

            $log_folder = infinity23_cron_mgmt_get_log_folder();
            $log_file = $log_folder . '/' . $filename;

            if( !file_exists( $log_file ) ) {
                return false;
            } // if( !file_exists( $log_file ) ) {

            $log_file_size = filesize( $log_file );

            // a large file is never loaded whole - only its last part is read,
            // with a note on top saying older lines are not shown
            if( $log_file_size > INFINITY23_CRON_MGMT_LOG_VIEW_MAX_BYTES ) {

                $log_handle = fopen( $log_file, 'rb' );

                if( !$log_handle ) {
                    return false;
                } // if( !$log_handle ) {

                fseek( $log_handle, -INFINITY23_CRON_MGMT_LOG_VIEW_MAX_BYTES, SEEK_END );
                $tail_content = fread( $log_handle, INFINITY23_CRON_MGMT_LOG_VIEW_MAX_BYTES );
                fclose( $log_handle );

                // the seek usually lands in the middle of a line - drop that partial line
                $tail_lines = explode( "\n", strval( $tail_content ), 2 );
                $tail_content = isset( $tail_lines[1] ) ? $tail_lines[1] : '';

                return "[This log file is " . infinity23_cron_mgmt_format_log_size( $log_file_size ) . " - only the last " . infinity23_cron_mgmt_format_log_size( INFINITY23_CRON_MGMT_LOG_VIEW_MAX_BYTES ) . " is shown below]\n\n" . $tail_content;

            } // if( $log_file_size > INFINITY23_CRON_MGMT_LOG_VIEW_MAX_BYTES ) {

            $raw_content = file_get_contents( $log_file );
            $content_lines = explode( "\n", $raw_content, 2 );

            return isset( $content_lines[1] ) ? $content_lines[1] : '';

        } // function infinity23_cron_mgmt_read_log_file( $filename ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_read_log_file' ) ) {

?>
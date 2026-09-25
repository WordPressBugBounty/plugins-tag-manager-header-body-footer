<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Cron Management - row url/nonce helpers
    // Kept in its own file so they load even when the module's activation code
    // is off, letting admin-output.php still build (locked) action links.
    // ===========================================================================

    if( !function_exists( 'infinity23_cron_mgmt_row_nonce_action' ) ) {

        function infinity23_cron_mgmt_row_nonce_action( $hook, $timestamp, $args_key ) {

            return 'infinity23_cron_row_action_' . md5( $hook . '|' . $timestamp . '|' . $args_key );

        } // function infinity23_cron_mgmt_row_nonce_action( $hook, $timestamp, $args_key ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_row_nonce_action' ) ) {

    if( !function_exists( 'infinity23_cron_mgmt_row_url' ) ) {

        function infinity23_cron_mgmt_row_url( $type, $hook, $timestamp, $args_key ) {

            $nonce_action = infinity23_cron_mgmt_row_nonce_action( $hook, $timestamp, $args_key );

            return add_query_arg(
                array(
                    'action'                   => 'infinity23_cron_mgmt_' . $type,
                    'infinity23_cron_hook'      => rawurlencode( $hook ),
                    'infinity23_cron_timestamp' => intval( $timestamp ),
                    'infinity23_cron_args_key'  => rawurlencode( $args_key ),
                    'infinity23_cron_row_nonce' => wp_create_nonce( $nonce_action ),
                ),
                admin_url( 'admin-post.php' )
            );

        } // function infinity23_cron_mgmt_row_url( $type, $hook, $timestamp, $args_key ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_row_url' ) ) {

    if( !function_exists( 'infinity23_cron_mgmt_find_row' ) ) {

        function infinity23_cron_mgmt_find_row( $hook, $timestamp, $args_key ) {

            $crons_list = _get_cron_array();

            if( empty( $crons_list ) ) { return null; }
            if( !isset( $crons_list[ $timestamp ][ $hook ][ $args_key ] ) ) { return null; }

            return $crons_list[ $timestamp ][ $hook ][ $args_key ];

        } // function infinity23_cron_mgmt_find_row( $hook, $timestamp, $args_key ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_find_row' ) ) {

    if( !function_exists( 'infinity23_cron_mgmt_find_row_by_args_key' ) ) {

        function infinity23_cron_mgmt_find_row_by_args_key( $hook, $args_key ) {

            $crons_list = _get_cron_array();

            if( empty( $crons_list ) ) { return null; }

            foreach( $crons_list as $timestamp => $cronhooks ) {

                if( isset( $cronhooks[ $hook ][ $args_key ] ) ) {

                    return array(
                        'timestamp' => $timestamp,
                        'row'       => $cronhooks[ $hook ][ $args_key ],
                    );

                } // if( isset( $cronhooks[ $hook ][ $args_key ] ) ) {

            } // foreach( $crons_list as $timestamp => $cronhooks ) {

            return null;

        } // function infinity23_cron_mgmt_find_row_by_args_key( $hook, $args_key ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_find_row_by_args_key' ) ) {

    if( !function_exists( 'infinity23_cron_mgmt_edit_url' ) ) {

        function infinity23_cron_mgmt_edit_url( $hook, $timestamp, $args_key ) {

            return add_query_arg(
                array(
                    'page'                => 'essential-cron-management',
                    'cron_edit_hook'      => rawurlencode( $hook ),
                    'cron_edit_timestamp' => intval( $timestamp ),
                    'cron_edit_args_key'  => rawurlencode( $args_key ),
                ),
                admin_url( 'admin.php' )
            );

        } // function infinity23_cron_mgmt_edit_url( $hook, $timestamp, $args_key ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_edit_url' ) ) {

    // ---------------------------------------
    // pause/restore module-name, nonce, url and restore-all functions now live
    // in back-end/cron-adjustments.php, shared with the interval-change system
    // ---------------------------------------

    // ---------------------------------------
    // curated list of intervals offered in the "New Interval" dropdown, in
    // display order - deliberately excludes every other schedule registered
    // by WordPress core or other plugins, so the select stays short and clear
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_curated_schedules' ) ) {

        function infinity23_cron_mgmt_curated_schedules() {

            return array(
                'infinity23_5_minutes'  => 'Every 5 Minutes',
                'infinity23_15_minutes' => 'Every 15 Minutes',
                'infinity23_30_minutes' => 'Every 30 Minutes',
                'infinity23_45_minutes' => 'Every 45 Minutes',
                'hourly'               => 'Every Hour',
                'infinity23_3_hours'    => 'Every 3 Hours',
                'infinity23_6_hours'    => 'Every 6 Hours',
                'infinity23_12_hours'   => 'Every 12 Hours',
                'daily'                => 'Once Daily',
                'weekly'               => 'Once Weekly',
                'infinity23_monthly'    => 'Once Monthly',
            );

        } // function infinity23_cron_mgmt_curated_schedules() {

    } // if( !function_exists( 'infinity23_cron_mgmt_curated_schedules' ) ) {

    // ---------------------------------------
    // resolve which plugin a cron hook's callback belongs to, by file path
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_get_callback_file' ) ) {

        function infinity23_cron_mgmt_get_callback_file( $callback ) {

            try {

                if( is_string( $callback ) && strpos( $callback, '::' ) !== false ) {
                    $callback = explode( '::', $callback );
                } // if( is_string( $callback ) && strpos( $callback, '::' ) !== false ) {

                if( is_array( $callback ) && isset( $callback[0] ) && isset( $callback[1] ) ) {

                    $reflection = new ReflectionMethod( $callback[0], $callback[1] );

                } elseif( is_string( $callback ) && function_exists( $callback ) ) {

                    $reflection = new ReflectionFunction( $callback );

                } elseif( is_object( $callback ) && ( $callback instanceof Closure ) ) {

                    $reflection = new ReflectionFunction( $callback );

                } else {

                    return '';

                } // if( is_array( $callback ) && isset( $callback[0] ) && isset( $callback[1] ) ) {

                return $reflection->getFileName();

            } catch ( Exception $e ) {

                return '';

            } // try

        } // function infinity23_cron_mgmt_get_callback_file( $callback ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_get_callback_file' ) ) {

    // ---------------------------------------
    // match a file path against installed plugins to get the plugin display name
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_get_plugin_name_from_file' ) ) {

        function infinity23_cron_mgmt_get_plugin_name_from_file( $file_path ) {

            static $plugins_list = null;

            if( $plugins_list === null ) {

                if( !function_exists( 'get_plugins' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                } // if( !function_exists( 'get_plugins' ) ) {

                $plugins_list = get_plugins();

            } // if( $plugins_list === null ) {

            $file_path = wp_normalize_path( $file_path );
            $plugin_dir = wp_normalize_path( WP_PLUGIN_DIR );

            if( strpos( $file_path, $plugin_dir ) !== 0 ) {
                return '';
            } // if( strpos( $file_path, $plugin_dir ) !== 0 ) {

            $relative_path = ltrim( str_replace( $plugin_dir, '', $file_path ), '/' );
            $plugin_folder = strtok( $relative_path, '/' );

            foreach( $plugins_list as $plugin_file => $plugin_data ) {

                if( strpos( $plugin_file, $plugin_folder . '/' ) === 0 ) {
                    return $plugin_data['Name'];
                } // if( strpos( $plugin_file, $plugin_folder . '/' ) === 0 ) {

            } // foreach( $plugins_list as $plugin_file => $plugin_data ) {

            return '';

        } // function infinity23_cron_mgmt_get_plugin_name_from_file( $file_path ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_get_plugin_name_from_file' ) ) {

    // ---------------------------------------
    // get the plugin name for a cron hook, checked from its registered callback(s)
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_get_plugin_name_for_hook' ) ) {

        function infinity23_cron_mgmt_get_plugin_name_for_hook( $hook ) {

            global $wp_filter;

            static $plugin_name_cache = array();

            if( isset( $plugin_name_cache[ $hook ] ) ) {
                return $plugin_name_cache[ $hook ];
            } // if( isset( $plugin_name_cache[ $hook ] ) ) {

            $plugin_name = '';

            if( isset( $wp_filter[ $hook ] ) && !empty( $wp_filter[ $hook ]->callbacks ) ) {

                foreach( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {

                    foreach( $callbacks as $callback_data ) {

                        $file_path = infinity23_cron_mgmt_get_callback_file( $callback_data['function'] );

                        if( !empty( $file_path ) ) {
                            $plugin_name = infinity23_cron_mgmt_get_plugin_name_from_file( $file_path );
                        } // if( !empty( $file_path ) ) {

                        if( !empty( $plugin_name ) ) {
                            break 2;
                        } // if( !empty( $plugin_name ) ) {

                    } // foreach( $callbacks as $callback_data ) {

                } // foreach( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {

            } // if( isset( $wp_filter[ $hook ] ) && !empty( $wp_filter[ $hook ]->callbacks ) ) {

            $plugin_name_cache[ $hook ] = $plugin_name;

            return $plugin_name;

        } // function infinity23_cron_mgmt_get_plugin_name_for_hook( $hook ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_get_plugin_name_for_hook' ) ) {

    // ---------------------------------------
    // view log nonce + url - keyed by filename since each log file is its
    // own dd-mm-Y.log resource, following the same pattern as the paused
    // restore nonce above
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_log_view_nonce_action' ) ) {

        function infinity23_cron_mgmt_log_view_nonce_action( $filename ) {

            return 'infinity23_cron_log_view_' . md5( $filename );

        } // function infinity23_cron_mgmt_log_view_nonce_action( $filename ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_log_view_nonce_action' ) ) {

    if( !function_exists( 'infinity23_cron_mgmt_log_view_url' ) ) {

        function infinity23_cron_mgmt_log_view_url( $filename ) {

            return add_query_arg(
                array(
                    'page'                     => 'essential-cron-management',
                    'cron_tab'                 => 'logs',
                    'cron_log_view'            => rawurlencode( $filename ),
                    'infinity23_cron_log_nonce' => wp_create_nonce( infinity23_cron_mgmt_log_view_nonce_action( $filename ) ),
                ),
                admin_url( 'admin.php' )
            );

        } // function infinity23_cron_mgmt_log_view_url( $filename ) {

    } // if( !function_exists( 'infinity23_cron_mgmt_log_view_url' ) ) {

    // ---------------------------------------
    // delete all logs nonce + url - single fixed action, no per-item key needed
    // ---------------------------------------

    if( !function_exists( 'infinity23_cron_mgmt_delete_all_logs_nonce_action' ) ) {

        function infinity23_cron_mgmt_delete_all_logs_nonce_action() {

            return 'infinity23_cron_delete_all_logs';

        } // function infinity23_cron_mgmt_delete_all_logs_nonce_action() {

    } // if( !function_exists( 'infinity23_cron_mgmt_delete_all_logs_nonce_action' ) ) {

    if( !function_exists( 'infinity23_cron_mgmt_delete_all_logs_url' ) ) {

        function infinity23_cron_mgmt_delete_all_logs_url() {

            return add_query_arg(
                array(
                    'action' => 'infinity23_cron_mgmt_delete_all_logs',
                    'infinity23_cron_delete_all_logs_nonce' => wp_create_nonce( infinity23_cron_mgmt_delete_all_logs_nonce_action() ),
                ),
                admin_url( 'admin-post.php' )
            );

        } // function infinity23_cron_mgmt_delete_all_logs_url() {

    } // if( !function_exists( 'infinity23_cron_mgmt_delete_all_logs_url' ) ) {

?>
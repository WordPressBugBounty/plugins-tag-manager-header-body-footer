<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Plugins/Themes Downloader - download links, ZIP creation and secure streaming
    // ===========================================================================

    include( dirname(__FILE__) . "/../default-values.php" );
    $infinity23_ptd_settings_raw = infinity23_get_option( $infinity23_settings_array_db_name );
    $infinity23_ptd_settings = infinity23_convert_json_to_array( $infinity23_ptd_settings_raw );

    $infinity23_ptd_enable_plugins = intval( infinity23_pro_get_data( 'enable_plugins_download', $infinity23_ptd_settings, $infinity23_defaults ) );
    $infinity23_ptd_enable_themes = intval( infinity23_pro_get_data( 'enable_themes_download', $infinity23_ptd_settings, $infinity23_defaults ) );

    // ---------------------------------------
    // temp folder + cleanup functions are loaded from loader.php, not here,
    // so they stay available even when the module's activation code is off
    // ---------------------------------------

    // ---------------------------------------
    // collect every file inside a plugin/theme folder
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_get_files' ) ) {

        function infinity23_ptd_get_files( $path ) {

            if( !file_exists( $path ) ) { return array(); }

            if( is_file( $path ) ) {
                return array( str_replace( '\\', '/', realpath( $path ) ) );
            } // if( is_file( $path ) ) {

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator( $path ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            $files_paths = array();

            foreach( $files as $file ) {

                if( !$file->isDir() ) {
                    $files_paths[] = str_replace( '\\', '/', $file->getRealPath() );
                } // if( !$file->isDir() ) {

            } // foreach( $files as $file ) {

            return $files_paths;

        } // function infinity23_ptd_get_files( $path ) {

    } // if( !function_exists( 'infinity23_ptd_get_files' ) ) {

    // ---------------------------------------
    // build the zip file using ZipArchive
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_create_zip' ) ) {

        function infinity23_ptd_create_zip( $zip_file_path, $files, $exclude_path ) {

            if( empty( $files ) || !class_exists( 'ZipArchive' ) ) { return false; }

            $zip = new ZipArchive();
            $result = $zip->open( $zip_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE );

            if( true !== $result ) { return false; }

            $exclude_from_relative_path = strlen( $exclude_path ) + 1;

            foreach( $files as $file_path ) {
                $zip->addFile( $file_path, substr( $file_path, $exclude_from_relative_path ) );
            } // foreach( $files as $file_path ) {

            // close() is what actually assembles the archive and renames the
            // working file into place, so it is the only reliable success
            // signal - returning true without checking it would send the
            // browser off to stream a file that was never created
            $infinity23_ptd_zip_closed = $zip->close();

            return ( true === $infinity23_ptd_zip_closed && file_exists( $zip_file_path ) );

        } // function infinity23_ptd_create_zip( $zip_file_path, $files, $exclude_path ) {

    } // if( !function_exists( 'infinity23_ptd_create_zip' ) ) {

    // ---------------------------------------
    // stream the zip file to the browser using its real name
    // the file is removed on shutdown, once this request is completely done with
    // it - infinity23_ptd_cleanup_old_zips() stays as the fallback for leftovers
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_send_zip' ) ) {

        function infinity23_ptd_send_zip( $real_file_name, $zip_file_path ) {

            if( !file_exists( $zip_file_path ) ) {
                wp_die( 'The ZIP file could not be found.' );
            } // if( !file_exists( $zip_file_path ) ) {

            // stop php/server-level compression from re-encoding the stream
            // after Content-Length is already set to the uncompressed size -
            // that mismatch is what corrupts the downloaded archive
            if( function_exists( 'apache_setenv' ) ) {
                // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- apache_setenv() only exists under mod_php, error-suppressed for every other SAPI
                @apache_setenv( 'no-gzip', '1' );
            } // if( function_exists( 'apache_setenv' ) ) {
            // phpcs:ignore WordPress.PHP.IniSet.Risky, WordPress.PHP.NoSilencedErrors.Discouraged -- must run per-request right before streaming, not from php.ini; suppressed since some hosts disable ini_set()
            @ini_set( 'zlib.output_compression', 'Off' );

            header( 'Content-Type: application/octet-stream' );
            header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $real_file_name ) . '"' );
            header( 'Content-Description: File Transfer' );
            header( 'Content-Transfer-Encoding: binary' );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate' );
            header( 'Pragma: public' );
            header( 'Content-Length: ' . filesize( $zip_file_path ) );

            while( ob_get_level() > 0 ) {
                ob_end_clean();
            } // while( ob_get_level() > 0 ) {

            flush();
            readfile( $zip_file_path );

            exit;

        } // function infinity23_ptd_send_zip( $real_file_name, $zip_file_path ) {

    } // if( !function_exists( 'infinity23_ptd_send_zip' ) ) {

    // ---------------------------------------
    // handle the download request - hooked to admin-post.php
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_handle_download' ) ) {

        function infinity23_ptd_handle_download() {

            // catch any stray output (a PHP notice, or whitespace before an
            // opening php tag in an included file) so it never leaks into
            // the binary zip stream and corrupts the download
            ob_start();

            $type = isset( $_GET['infinity23_ptd_type'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_ptd_type'] ) ) : '';
            $slug = isset( $_GET['infinity23_ptd_slug'] ) ? basename( sanitize_text_field( wp_unslash( $_GET['infinity23_ptd_slug'] ) ) ) : '';
            $nonce = isset( $_GET['infinity23_ptd_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_ptd_nonce'] ) ) : '';

            if( $slug === '.' || $slug === '..' ) {
                wp_die( 'Invalid request.' );
            } // if( $slug === '.' || $slug === '..' ) {

            if( empty( $type ) || empty( $slug ) || !wp_verify_nonce( $nonce, 'infinity23_ptd_download_' . $type . '_' . $slug ) ) {
                wp_die( 'Link expired.' );
            } // if( empty( $type ) || empty( $slug ) || !wp_verify_nonce(

            global $infinity23_ptd_enable_plugins, $infinity23_ptd_enable_themes;

            if( 'plugin' === $type ) {

                if( !current_user_can( 'activate_plugins' ) ) {
                    wp_die( 'Permission denied.' );
                } // if( !current_user_can( 'activate_plugins' ) ) {

                // turning the feature off only hid the links before - an old link
                // with a still-valid nonce kept working against the handler
                if( intval( $infinity23_ptd_enable_plugins ) !== 1 ) {
                    wp_die( 'Plugin downloads are disabled in the Plugins & Themes Downloader settings.' );
                } // if( intval( $infinity23_ptd_enable_plugins ) !== 1 ) {

                $source_dir = WP_PLUGIN_DIR;

            } elseif( 'theme' === $type ) {

                if( !current_user_can( 'switch_themes' ) ) {
                    wp_die( 'Permission denied.' );
                } // if( !current_user_can( 'switch_themes' ) ) {

                if( intval( $infinity23_ptd_enable_themes ) !== 1 ) {
                    wp_die( 'Theme downloads are disabled in the Plugins & Themes Downloader settings.' );
                } // if( intval( $infinity23_ptd_enable_themes ) !== 1 ) {

                $source_dir = get_theme_root();

            } else {
                wp_die( 'Invalid request.' );
            } // if( 'plugin' === $type ) {

            $item_path = $source_dir . '/' . $slug;

            if( !file_exists( $item_path ) ) {
                wp_die( 'The requested item could not be found.' );
            } // if( !file_exists( $item_path ) ) {

            include( dirname(__FILE__) . "/../default-values.php" );
            $settings = infinity23_convert_json_to_array( infinity23_get_option( 'plugins_and_themes_downloader_settings' ) );
            $zip_time_limit = intval( infinity23_pro_get_data( 'zip_time_limit', $settings, $infinity23_defaults ) );
            $include_version_in_filename = intval( infinity23_pro_get_data( 'include_version_in_filename', $settings, $infinity23_defaults ) );

            if( function_exists( 'set_time_limit' ) ) {
                // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- needed so large ZIP builds don't hit the host's default execution limit; already guarded by function_exists() and error-suppressed for hosts that disable it
                @set_time_limit( $zip_time_limit );
            } // if( function_exists( 'set_time_limit' ) ) {

            // the plugin/theme main folder is always kept inside the ZIP, so the file is ready to install as-is
            $exclude_path = $source_dir;
            $files = infinity23_ptd_get_files( $item_path );

            $temp_dir = infinity23_ptd_get_temp_dir();

            infinity23_ptd_cleanup_all_old_zips();

            $random_name = wp_generate_password( 20, false ) . '.zip';
            $zip_file_path = $temp_dir . '/' . $random_name;
            $real_file_name = $slug . '.zip';

            // clear this request's own zip once the request is completely over.
            // A shutdown callback is used instead of an unlink() after
            // readfile(), because php kills the script the moment the visitor
            // cancels the download or the execution limit is reached, and a
            // line sitting after readfile() would never run in those cases.
            // Registered here rather than next to readfile() so a build that
            // gets killed part way through is cleaned up too - that is what
            // leaves a ZipArchive working file (name.zip.008fa8ac) behind.
            // This can never cut a download short: shutdown is only reached
            // after readfile() has already pushed every byte out of the file
            register_shutdown_function( function() use ( $zip_file_path ) {

                $infinity23_ptd_leftovers = glob( $zip_file_path . '*' );

                if( empty( $infinity23_ptd_leftovers ) ) { return; }

                foreach( $infinity23_ptd_leftovers as $infinity23_ptd_leftover ) {

                    if( is_file( $infinity23_ptd_leftover ) ) {
                        wp_delete_file( $infinity23_ptd_leftover );
                    } // if( is_file( $infinity23_ptd_leftover ) ) {

                } // foreach( $infinity23_ptd_leftovers as $infinity23_ptd_leftover ) {

            } ); // register_shutdown_function(

            if( $include_version_in_filename == 1 ) {

                $item_version = '';

                if( 'plugin' === $type ) {

                    if( !function_exists( 'get_plugins' ) ) {
                        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    } // if( !function_exists( 'get_plugins' ) ) {

                    $all_plugins = get_plugins();

                    foreach( $all_plugins as $plugin_file => $plugin_data ) {

                        $plugin_parts = explode( '/', $plugin_file );

                        if( isset( $plugin_parts[0] ) && $plugin_parts[0] === $slug ) {
                            $item_version = $plugin_data['Version'];
                            break;
                        } // if( isset( $plugin_parts[0] ) && $plugin_parts[0] === $slug ) {

                    } // foreach( $all_plugins as $plugin_file => $plugin_data ) {

                } elseif( 'theme' === $type ) {

                    $theme_obj = wp_get_theme( $slug );
                    $item_version = $theme_obj->get('Version');

                } // if( 'plugin' === $type ) {

                if( !empty( $item_version ) ) {
                    $real_file_name = $slug . '.' . sanitize_file_name( $item_version ) . '.zip';
                } // if( !empty( $item_version ) ) {

            } // if( $include_version_in_filename == 1 ) {

            if( infinity23_ptd_create_zip( $zip_file_path, $files, $exclude_path ) ) {
                infinity23_ptd_send_zip( $real_file_name, $zip_file_path );
            } else {
                wp_die( 'The ZIP file could not be created.' );
            } // if( infinity23_ptd_create_zip(

        } // function infinity23_ptd_handle_download() {

    } // if( !function_exists( 'infinity23_ptd_handle_download' ) ) {

    add_action( 'admin_post_infinity23_ptd_download', 'infinity23_ptd_handle_download' );

    // ---------------------------------------
    // add "Download ZIP" link on the Plugins page
    // ---------------------------------------

    if( $infinity23_ptd_enable_plugins == 1 ) {

        if( !function_exists( 'infinity23_ptd_add_plugin_download_link' ) ) {

            function infinity23_ptd_add_plugin_download_link( $actions, $plugin_file ) {

                $plugin_parts = explode( '/', $plugin_file );

                if( !isset( $plugin_parts[0] ) ) { return $actions; }

                $slug = $plugin_parts[0];

                $link = add_query_arg(
                    array(
                        'action' => 'infinity23_ptd_download',
                        'infinity23_ptd_type' => 'plugin',
                        'infinity23_ptd_slug' => $slug,
                        'infinity23_ptd_nonce' => wp_create_nonce( 'infinity23_ptd_download_plugin_' . $slug ),
                    ),
                    admin_url( 'admin-post.php' )
                );

                $actions[] = '<a class="infinity23-ptd-download-link" href="' . esc_url( $link ) . '">Download</a>';

                return $actions;

            } // function infinity23_ptd_add_plugin_download_link( $actions, $plugin_file ) {

        } // if( !function_exists( 'infinity23_ptd_add_plugin_download_link' ) ) {

        add_filter( 'plugin_action_links', 'infinity23_ptd_add_plugin_download_link', 10, 2 );

    } // if( $infinity23_ptd_enable_plugins == 1 ) {

    // ---------------------------------------
    // add "Download ZIP" link on the Themes page - the badge CSS and the
    // click-binding JS are static and already live in the dashboard-tier
    // files (loaded on every admin page). Only the per-theme nonces are
    // genuinely dynamic and Themes-page-specific, so only those get
    // computed here, scoped to themes.php, and attached to the existing
    // infinity23_dashboard_js handle.
    // ---------------------------------------

    if( $infinity23_ptd_enable_themes == 1 ) {

        add_action( 'admin_enqueue_scripts', function( $hook ) {

            if( $hook !== 'themes.php' ) { return; }
            if( !current_user_can( 'switch_themes' ) ) { return; }

            $themes = wp_get_themes();
            $nonces = array();

            foreach( $themes as $theme_slug => $theme_obj ) {
                $nonces[ $theme_slug ] = wp_create_nonce( 'infinity23_ptd_download_theme_' . $theme_slug );
            } // foreach( $themes as $theme_slug => $theme_obj ) {

            $infinity23_ptd_js_vars  = 'var infinity23PtdNonces = ' . wp_json_encode( $nonces ) . ';';
            $infinity23_ptd_js_vars .= 'var infinity23PtdAjaxUrl = ' . wp_json_encode( admin_url( 'admin-post.php' ) ) . ';';
            $infinity23_ptd_js_vars .= 'var infinity23PtdLinkText = ' . wp_json_encode( 'Download' ) . ';';

            wp_add_inline_script( 'infinity23_dashboard_js', $infinity23_ptd_js_vars, 'before' );

        } ); // add_action( 'admin_enqueue_scripts'

    } // if( $infinity23_ptd_enable_themes == 1 ) {

?>
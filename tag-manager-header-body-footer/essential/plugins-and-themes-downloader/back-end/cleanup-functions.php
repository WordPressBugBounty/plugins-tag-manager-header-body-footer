<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Plugins/Themes Downloader - temp folder + old zip cleanup functions
    // Kept in its own file so it can be loaded independently from an external
    // system (e.g. a theme file) without loading the whole download handler.
    // ===========================================================================

    // ---------------------------------------
    // how many minutes to keep an old zip file before it gets deleted on cleanup
    // edit this value to change the cleanup time
    // ---------------------------------------

    if( !defined( 'INFINITY23_PLUGINS_AND_THEMES_CLEANUP_AFTER_X_MINUTES' ) ) {
        define( 'INFINITY23_PLUGINS_AND_THEMES_CLEANUP_AFTER_X_MINUTES', 1 );
    } // if( !defined( 'INFINITY23_PLUGINS_AND_THEMES_CLEANUP_AFTER_X_MINUTES' ) ) {

    // ---------------------------------------
    // normalize a path for comparing - windows mixes \ and /, and a trailing
    // slash would break a straight strpos() prefix test
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_normalize_path' ) ) {

        function infinity23_ptd_normalize_path( $infinity23_ptd_path ) {

            return rtrim( str_replace( '\\', '/', strval( $infinity23_ptd_path ) ), '/' );

        } // function infinity23_ptd_normalize_path( $infinity23_ptd_path ) {

    } // if( !function_exists( 'infinity23_ptd_normalize_path' ) ) {

    // ---------------------------------------
    // the two possible zip storage folders. "uploads" is the plugin's own
    // folder inside wp-content/uploads/infinity23/, "temp" is a folder inside
    // the server's own temporary directory, which is outside the site's public
    // folders entirely, so the file is not reachable by any url at all
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_get_uploads_dir' ) ) {

        function infinity23_ptd_get_uploads_dir() {

            return infinity23_get_storage_root_folder( 'asset-downloader' );

        } // function infinity23_ptd_get_uploads_dir() {

    } // if( !function_exists( 'infinity23_ptd_get_uploads_dir' ) ) {

    if( !function_exists( 'infinity23_ptd_get_system_temp_dir' ) ) {

        function infinity23_ptd_get_system_temp_dir() {

            return infinity23_ptd_normalize_path( get_temp_dir() ) . '/infinity23-asset-downloader';

        } // function infinity23_ptd_get_system_temp_dir() {

    } // if( !function_exists( 'infinity23_ptd_get_system_temp_dir' ) ) {

    // ---------------------------------------
    // both folders, always - every cleanup pass walks this list instead of
    // only the folder currently selected, so switching the setting can never
    // strand old zip files in the folder that is no longer in use
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_get_all_storage_dirs' ) ) {

        function infinity23_ptd_get_all_storage_dirs() {

            return array_unique( array( infinity23_ptd_get_uploads_dir(), infinity23_ptd_get_system_temp_dir() ) );

        } // function infinity23_ptd_get_all_storage_dirs() {

    } // if( !function_exists( 'infinity23_ptd_get_all_storage_dirs' ) ) {

    // ---------------------------------------
    // the storage folder the user picked on the settings page - defaults to
    // the server's temporary folder, which keeps the generated zip out of the
    // public folders without needing any server level rule (a .htaccess deny
    // does nothing on nginx, so the folder location is the only portable fix)
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_get_storage_location' ) ) {

        function infinity23_ptd_get_storage_location() {

            static $infinity23_ptd_cached_location = null;

            if( $infinity23_ptd_cached_location !== null ) {
                return $infinity23_ptd_cached_location;
            } // if( $infinity23_ptd_cached_location !== null ) {

            include( dirname(__FILE__) . "/../default-values.php" );

            $infinity23_ptd_settings = infinity23_convert_json_to_array( infinity23_get_option( 'plugins_and_themes_downloader_settings' ) );
            $infinity23_ptd_location = infinity23_pro_get_data( 'zip_storage_location', $infinity23_ptd_settings, $infinity23_defaults );

            if( !in_array( $infinity23_ptd_location, array( 'temp', 'uploads' ), true ) ) {
                $infinity23_ptd_location = 'temp';
            } // if( !in_array( $infinity23_ptd_location, array( 'temp', 'uploads' ), true ) ) {

            $infinity23_ptd_cached_location = $infinity23_ptd_location;

            return $infinity23_ptd_cached_location;

        } // function infinity23_ptd_get_storage_location() {

    } // if( !function_exists( 'infinity23_ptd_get_storage_location' ) ) {

    // ---------------------------------------
    // is this folder inside the site's public folders, meaning a browser could
    // in principle request a file from it - decides both whether the server's
    // temp folder is worth using at all, and whether a blank index.php is
    // needed inside it
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_dir_is_public' ) ) {

        function infinity23_ptd_dir_is_public( $infinity23_ptd_dir ) {

            $infinity23_ptd_dir = infinity23_ptd_normalize_path( $infinity23_ptd_dir );

            return ( strpos( $infinity23_ptd_dir, infinity23_ptd_normalize_path( ABSPATH ) ) === 0 || strpos( $infinity23_ptd_dir, infinity23_ptd_normalize_path( WP_CONTENT_DIR ) ) === 0 );

        } // function infinity23_ptd_dir_is_public( $infinity23_ptd_dir ) {

    } // if( !function_exists( 'infinity23_ptd_dir_is_public' ) ) {

    // ---------------------------------------
    // create a storage folder - returns false when the folder cannot be
    // created or written to, so the caller can fall back. The blank index.php
    // is only written when the folder is actually web served - inside the
    // server's own temp folder there is no url to block, so it would be a
    // stray file for nothing
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_prepare_storage_dir' ) ) {

        function infinity23_ptd_prepare_storage_dir( $infinity23_ptd_dir ) {

            if( !file_exists( $infinity23_ptd_dir ) ) {
                wp_mkdir_p( $infinity23_ptd_dir );
            } // if( !file_exists( $infinity23_ptd_dir ) ) {

            if( !is_dir( $infinity23_ptd_dir ) || !wp_is_writable( $infinity23_ptd_dir ) ) {
                return false;
            } // if( !is_dir( $infinity23_ptd_dir ) || !wp_is_writable( $infinity23_ptd_dir ) ) {

            if( !infinity23_ptd_dir_is_public( $infinity23_ptd_dir ) ) {
                return true;
            } // if( !infinity23_ptd_dir_is_public( $infinity23_ptd_dir ) ) {

            $infinity23_ptd_index_file = $infinity23_ptd_dir . '/index.php';

            if( !file_exists( $infinity23_ptd_index_file ) ) {
                // phpcs:ignore PluginCheck.CodeAnalysis.WriteFile.PluginDirectoryWrite -- not the plugin's own folder (wp-content/plugins/infinity23/), which IS wiped on upgrade; $dir comes from infinity23_get_storage_root_folder(), a wp_upload_dir()-based path that survives an upgrade
                file_put_contents( $infinity23_ptd_index_file, "<?php\n// Silence is golden.\n" );
            } // if( !file_exists( $infinity23_ptd_index_file ) ) {

            return true;

        } // function infinity23_ptd_prepare_storage_dir( $infinity23_ptd_dir ) {

    } // if( !function_exists( 'infinity23_ptd_prepare_storage_dir' ) ) {

    // ---------------------------------------
    // get (and create on first use) the zip storage folder actually in use
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_get_temp_dir' ) ) {

        function infinity23_ptd_get_temp_dir( $infinity23_ptd_force_location = '' ) {

            // the settings page passes the value it has just saved, because
            // infinity23_ptd_get_storage_location() caches statically and would
            // still be holding the pre-save value in that same request
            $infinity23_ptd_location = !empty( $infinity23_ptd_force_location ) ? $infinity23_ptd_force_location : infinity23_ptd_get_storage_location();

            $infinity23_ptd_uploads_dir = infinity23_ptd_get_uploads_dir();

            if( $infinity23_ptd_location !== 'temp' ) {

                infinity23_ptd_prepare_storage_dir( $infinity23_ptd_uploads_dir );

                return $infinity23_ptd_uploads_dir;

            } // if( $infinity23_ptd_location !== 'temp' ) {

            $infinity23_ptd_system_dir = infinity23_ptd_get_system_temp_dir();

            // get_temp_dir() falls back to WP_CONTENT_DIR when nothing else is
            // writable, which would put the zip straight back inside the public
            // folders - in that case the temp option buys nothing, so skip it
            if( !infinity23_ptd_dir_is_public( $infinity23_ptd_system_dir ) && infinity23_ptd_prepare_storage_dir( $infinity23_ptd_system_dir ) ) {
                return $infinity23_ptd_system_dir;
            } // if( !infinity23_ptd_dir_is_public( $infinity23_ptd_system_dir ) && infinity23_ptd_prepare_storage_dir( $infinity23_ptd_system_dir ) ) {

            // the server's temp folder is unusable or public on this host -
            // fall back so a download never fails just because of this setting
            infinity23_ptd_prepare_storage_dir( $infinity23_ptd_uploads_dir );

            return $infinity23_ptd_uploads_dir;

        } // function infinity23_ptd_get_temp_dir() {

    } // if( !function_exists( 'infinity23_ptd_get_temp_dir' ) ) {

    // ---------------------------------------
    // empty one storage folder and remove it. For the plugin's own uploads
    // folder the shared infinity23/ parent is removed too when it is left
    // empty - the system temp folder's parent is the server's own /tmp, so
    // that one is never touched
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_purge_storage_dir' ) ) {

        function infinity23_ptd_purge_storage_dir( $infinity23_ptd_dir ) {

            if( !is_dir( $infinity23_ptd_dir ) ) { return; }

            global $wp_filesystem;

            if( !function_exists( 'WP_Filesystem' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            } // if( !function_exists( 'WP_Filesystem' ) ) {

            WP_Filesystem();

            $files = glob( $infinity23_ptd_dir . '/*' );

            if( !empty( $files ) ) {

                foreach( $files as $file ) {

                    if( is_file( $file ) ) {
                        wp_delete_file( $file );
                    } // if( is_file( $file ) ) {

                } // foreach( $files as $file ) {

            } // if( !empty( $files ) ) {

            if( empty( $wp_filesystem ) ) { return; }

            $wp_filesystem->rmdir( $infinity23_ptd_dir );

            if( infinity23_ptd_normalize_path( $infinity23_ptd_dir ) !== infinity23_ptd_normalize_path( infinity23_ptd_get_uploads_dir() ) ) { return; }

            $parent_dir = dirname( $infinity23_ptd_dir );
            // glob() returns false, not an empty array, when the folder cannot be
            // read - count( false ) is a TypeError on php 8
            $parent_files = glob( $parent_dir . '/*' );
            $parent_files = !empty( $parent_files ) ? $parent_files : array();
            $only_index_left = ( count( $parent_files ) === 1 && basename( $parent_files[0] ) === 'index.php' );

            if( is_dir( $parent_dir ) && ( empty( $parent_files ) || $only_index_left ) ) {

                if( $only_index_left ) {
                    wp_delete_file( $parent_files[0] );
                } // if( $only_index_left ) {

                $wp_filesystem->rmdir( $parent_dir );

            } // if( is_dir( $parent_dir ) && ( empty( $parent_files ) || $only_index_left ) ) {

        } // function infinity23_ptd_purge_storage_dir( $infinity23_ptd_dir ) {

    } // if( !function_exists( 'infinity23_ptd_purge_storage_dir' ) ) {

    // ---------------------------------------
    // remove every storage folder the module may have written to, not just
    // the one currently selected - runs when the module gets deactivated
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_remove_temp_dir' ) ) {

        function infinity23_ptd_remove_temp_dir() {

            foreach( infinity23_ptd_get_all_storage_dirs() as $infinity23_ptd_storage_dir ) {
                infinity23_ptd_purge_storage_dir( $infinity23_ptd_storage_dir );
            } // foreach( infinity23_ptd_get_all_storage_dirs() as $infinity23_ptd_storage_dir ) {

        } // function infinity23_ptd_remove_temp_dir() {

    } // if( !function_exists( 'infinity23_ptd_remove_temp_dir' ) ) {

    // ---------------------------------------
    // empty and remove whichever storage folder is NOT currently selected -
    // runs right after the storage location setting is saved, so switching
    // from one folder to the other never leaves old zip files behind in the
    // folder the user just moved away from
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_purge_unused_storage_dir' ) ) {

        function infinity23_ptd_purge_unused_storage_dir( $infinity23_ptd_active_dir ) {

            $infinity23_ptd_active_dir = infinity23_ptd_normalize_path( $infinity23_ptd_active_dir );

            foreach( infinity23_ptd_get_all_storage_dirs() as $infinity23_ptd_storage_dir ) {

                if( infinity23_ptd_normalize_path( $infinity23_ptd_storage_dir ) !== $infinity23_ptd_active_dir ) {
                    infinity23_ptd_purge_storage_dir( $infinity23_ptd_storage_dir );
                } // if( infinity23_ptd_normalize_path( $infinity23_ptd_storage_dir ) !== $infinity23_ptd_active_dir ) {

            } // foreach( infinity23_ptd_get_all_storage_dirs() as $infinity23_ptd_storage_dir ) {

        } // function infinity23_ptd_purge_unused_storage_dir( $infinity23_ptd_active_dir ) {

    } // if( !function_exists( 'infinity23_ptd_purge_unused_storage_dir' ) ) {

    // ---------------------------------------
    // delete zip files older than 1 hour - runs before every new download
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_cleanup_old_zips' ) ) {

        function infinity23_ptd_cleanup_old_zips( $dir, $cleanup_after_minutes = INFINITY23_PLUGINS_AND_THEMES_CLEANUP_AFTER_X_MINUTES ) {

            if( !is_dir( $dir ) ) { return; }

            // *.zip* and not *.zip - while ZipArchive builds an archive it
            // writes to a working file named after the target with a random
            // suffix (name.zip.008fa8ac) and only renames it on a successful
            // close(). A build killed by the time limit leaves that working
            // file behind, and a plain *.zip pattern would never match it
            $files = glob( $dir . '/*.zip*' );

            if( empty( $files ) ) { return; }

            $max_age = $cleanup_after_minutes * MINUTE_IN_SECONDS;

            foreach( $files as $file ) {

                if( ( time() - filemtime( $file ) ) > $max_age ) {
                    wp_delete_file( $file );
                } // if( ( time() - filemtime( $file ) ) > $max_age ) {

            } // foreach( $files as $file ) {

        } // function infinity23_ptd_cleanup_old_zips( $dir, $cleanup_after_minutes = 10 ) {

    } // if( !function_exists( 'infinity23_ptd_cleanup_old_zips' ) ) {

    // ---------------------------------------
    // same cleanup, across both storage folders - this is what the download
    // handler and the hourly cron call, so a folder that is no longer selected
    // still gets swept instead of holding old zip files forever
    // ---------------------------------------

    if( !function_exists( 'infinity23_ptd_cleanup_all_old_zips' ) ) {

        function infinity23_ptd_cleanup_all_old_zips() {

            foreach( infinity23_ptd_get_all_storage_dirs() as $infinity23_ptd_storage_dir ) {
                infinity23_ptd_cleanup_old_zips( $infinity23_ptd_storage_dir );
            } // foreach( infinity23_ptd_get_all_storage_dirs() as $infinity23_ptd_storage_dir ) {

        } // function infinity23_ptd_cleanup_all_old_zips() {

    } // if( !function_exists( 'infinity23_ptd_cleanup_all_old_zips' ) ) {

?>
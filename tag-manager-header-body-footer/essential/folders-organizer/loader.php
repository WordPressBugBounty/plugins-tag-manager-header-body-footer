<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading folders-organizer plugin page and data
    // ===========================================================================

    include( dirname(__FILE__) . "/internal-settings.php" );
    include( dirname(__FILE__) . "/../../include/settings.php" );
    include( dirname(__FILE__) . "/back-end/folders-content-types.php" );
    include( dirname(__FILE__) . "/back-end/folders-db-functions.php" );

    global $infinity23_essential_modules_array;

    // ===========================================================================
    // Loading admin page - admin only
    // ===========================================================================

    if( is_admin() ) {

        add_action( 'admin_menu', function() {

            global $infinity23_essential_modules_array;

            $infinity23_folders_organizer_hook = add_submenu_page(
                'essential-include-exclude',
                $infinity23_essential_modules_array['folders_organizer']['module_display_name'],
                $infinity23_essential_modules_array['folders_organizer']['module_display_name'],
                'manage_options',
                $infinity23_essential_modules_array['folders_organizer']['admin_page_slug'],
                function() {
                    include( dirname(__FILE__) . "/admin-output.php" );
                }
            );

            add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_folders_organizer_hook ) {

                if( $hook !== $infinity23_folders_organizer_hook ) { return; }

                infinity23_enqueue_essential_styles();

            } ); // add_action( 'admin_enqueue_scripts'

        } ); // add_action( 'admin_menu'

        $infinity23_folders_organizer_menu_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
        include( dirname(__FILE__) . "/default-values.php" );
        $infinity23_folders_organizer_hide_menu = intval( infinity23_get_data( $infinity23_essential_modules_array['folders_organizer']['hide_menu_field'], $infinity23_folders_organizer_menu_settings_data, $infinity23_defaults ) );

        if( $infinity23_folders_organizer_hide_menu == 1 ) {

            global $infinity23_hidden_menu_slugs;
            $infinity23_hidden_menu_slugs[] = $infinity23_essential_modules_array['folders_organizer']['admin_page_slug'];

        } // if( $infinity23_folders_organizer_hide_menu == 1 ) {

    } // if( is_admin() ) {

    // ===========================================================================
    // defensive table install - only runs once per site, flag prevents an
    // extra query on every admin page load
    // ===========================================================================

    $infinity23_folders_organizer_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

    if( is_admin() && $infinity23_folders_organizer_code_is_active == 1 && intval( infinity23_get_option( 'folders_organizer_tables_installed', 0 ) ) !== 1 ) {
        infinity23_folders_organizer_install_table();
        infinity23_update_option( 'folders_organizer_tables_installed', 1 );
    } // if( is_admin() && $infinity23_folders_organizer_code_is_active == 1 && intval(

    // ===========================================================================
    // Loading the folders system - delete cleanup always, the shared sidebar
    // and the screen extras in admin only. Each screen checks its own content
    // type at runtime, nothing is resolved here (custom post types are only
    // known after the init hook has finished)
    // ===========================================================================

    if( $infinity23_folders_organizer_code_is_active == 1 ) {

        include( dirname(__FILE__) . "/back-end/folders-cleanup.php" );

        if( is_admin() ) {
            include( dirname(__FILE__) . "/back-end/folders-sidebar.php" );
            include( dirname(__FILE__) . "/back-end/folders-media-library.php" );
            include( dirname(__FILE__) . "/back-end/folders-pages-posts.php" );
        } // if( is_admin() ) {

    } // if( $infinity23_folders_organizer_code_is_active == 1 ) {

?>
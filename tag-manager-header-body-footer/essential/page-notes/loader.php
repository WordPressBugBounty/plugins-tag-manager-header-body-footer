<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading notes plugin page and data
    // ===========================================================================

    include( dirname(__FILE__) . "/internal-settings.php" );
    include( dirname(__FILE__) . "/../../include/settings.php" );
    include( dirname(__FILE__) . "/back-end/notes-db-functions.php" );

    // ===========================================================================
    // Loading admin page - admin only
    // ===========================================================================

    global $infinity23_essential_modules_array;

    if( is_admin() ) {

        add_action( 'admin_menu', function() use ( $infinity23_activation_db_name, $infinity23_settings_array_db_name ) {

            global $infinity23_essential_modules_array;

            $infinity23_notes_hook = add_submenu_page(
                'essential-include-exclude',
                $infinity23_essential_modules_array['notes']['module_display_name'],
                $infinity23_essential_modules_array['notes']['module_display_name'],
                'manage_options',
                $infinity23_essential_modules_array['notes']['admin_page_slug'],
                function() use ( $infinity23_activation_db_name, $infinity23_settings_array_db_name ) {
                    include( dirname(__FILE__) . "/admin-output.php" );
                }
            );

            add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_notes_hook ) {

                if( $hook !== $infinity23_notes_hook ) { return; }

                infinity23_enqueue_essential_styles();

            } ); // add_action( 'admin_enqueue_scripts'

        } ); // add_action( 'admin_menu'

        $infinity23_notes_menu_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
        include( dirname(__FILE__) . "/default-values.php" );
        $infinity23_notes_hide_menu = intval( infinity23_get_data( $infinity23_essential_modules_array['notes']['hide_menu_field'], $infinity23_notes_menu_settings_data, $infinity23_defaults ) );

        if( $infinity23_notes_hide_menu == 1 ) {

            global $infinity23_hidden_menu_slugs;
            $infinity23_hidden_menu_slugs[] = $infinity23_essential_modules_array['notes']['admin_page_slug'];

        } // if( $infinity23_notes_hide_menu == 1 ) {

    } // if( is_admin() ) {

    // ===========================================================================
    // Loading notes functions if the code is active
    // ===========================================================================

    $infinity23_notes_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

    // defensive check - creates the notes table if it does not exist yet
    // (covers sites that activated Page Notes before this table existed)
    // only runs once per site - a saved flag prevents this from querying the database on every admin page load
    if( is_admin() && $infinity23_notes_code_is_active == 1 && intval( infinity23_get_option( 'notes_table_installed', 0 ) ) !== 1 ) {
        infinity23_notes_install_table();
        infinity23_update_option( 'notes_table_installed', 1 );
    } // if( is_admin() && $infinity23_notes_code_is_active == 1 && intval( infinity23_get_option( 'notes_table_installed', 0 ) ) !== 1 ) {

    if( is_admin() && $infinity23_notes_code_is_active == 1 ) {
        include( dirname(__FILE__) . "/back-end/back-end-loader.php" );
    } // if( is_admin() && $infinity23_notes_code_is_active == 1 ) {

?>
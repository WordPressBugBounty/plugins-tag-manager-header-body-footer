<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading WordPress Settings page and data
    // ===========================================================================

    include( dirname(__FILE__) . "/internal-settings.php" );
    include( dirname(__FILE__) . "/../../include/settings.php" );
    include( dirname(__FILE__) . "/default-values.php" );

    global $infinity23_essential_modules_array;

    // ===========================================================================
    // Loading admin page - admin only - registered first so it appears
    // above Include / Exclude in the Essential submenu
    // ===========================================================================

    if( is_admin() ) {

        add_action( 'admin_menu', function() {

            global $infinity23_essential_modules_array;

            $infinity23_wordpress_settings_hook = add_submenu_page(
                'essential-include-exclude',
                $infinity23_essential_modules_array['wordpress_settings']['module_display_name'],
                $infinity23_essential_modules_array['wordpress_settings']['module_display_name'],
                'manage_options',
                $infinity23_essential_modules_array['wordpress_settings']['admin_page_slug'],
                function() {
                    include( dirname(__FILE__) . "/admin-output.php" );
                }
            );

            add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_wordpress_settings_hook ) {

                if( $hook !== $infinity23_wordpress_settings_hook ) { return; }

                infinity23_enqueue_essential_styles();

            } ); // add_action( 'admin_enqueue_scripts'

        } ); // add_action( 'admin_menu'

        $infinity23_wordpress_settings_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
        $infinity23_wordpress_settings_hide_menu = intval( infinity23_get_data( $infinity23_essential_modules_array['wordpress_settings']['hide_menu_field'], $infinity23_wordpress_settings_data_array, $infinity23_defaults ) );

        if( $infinity23_wordpress_settings_hide_menu == 1 ) {

            global $infinity23_hidden_menu_slugs;
            $infinity23_hidden_menu_slugs[] = $infinity23_essential_modules_array['wordpress_settings']['admin_page_slug'];

        } // if( $infinity23_wordpress_settings_hide_menu == 1 ) {

    } // if( is_admin() ) {

    // ===========================================================================
    // WordPress Settings module - only when the module is Activated
    // ===========================================================================

    $infinity23_wordpress_settings_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

    if( $infinity23_wordpress_settings_code_is_active == 1 ) {
        include( dirname(__FILE__) . "/back-end/back-end.php" );
    } // if( $infinity23_wordpress_settings_code_is_active == 1 ) {

?>
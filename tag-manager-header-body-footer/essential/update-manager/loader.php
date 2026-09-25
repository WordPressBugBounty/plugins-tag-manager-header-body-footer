<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading update-manager plugin page and data
    // ===========================================================================

    include( dirname(__FILE__) . "/internal-settings.php" );
    include( dirname(__FILE__) . "/../../include/settings.php" );

    global $infinity23_essential_modules_array;

    // ===========================================================================
    // Loading admin page - admin only
    // ===========================================================================

    if( is_admin() ) {

        add_action( 'admin_menu', function() {

        	global $infinity23_essential_modules_array;

        	$infinity23_update_manager_hook = add_submenu_page(
        		'essential-include-exclude',
        		$infinity23_essential_modules_array['update_manager']['module_display_name'],
        		$infinity23_essential_modules_array['update_manager']['module_display_name'],
        		'manage_options',
        		$infinity23_essential_modules_array['update_manager']['admin_page_slug'],
        		function() {
        			include( dirname(__FILE__) . "/admin-output.php" );
        		}
        	);

        	add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_update_manager_hook ) {

        		if( $hook !== $infinity23_update_manager_hook ) { return; }

        		infinity23_enqueue_essential_styles();

        	} ); // add_action( 'admin_enqueue_scripts'

        } ); // add_action( 'admin_menu'

        $infinity23_update_manager_menu_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
        include( dirname(__FILE__) . "/default-values.php" );
        $infinity23_update_manager_hide_menu = intval( infinity23_get_data( $infinity23_essential_modules_array['update_manager']['hide_menu_field'], $infinity23_update_manager_menu_settings_data, $infinity23_defaults ) );

        if( $infinity23_update_manager_hide_menu == 1 ) {

            global $infinity23_hidden_menu_slugs;
            $infinity23_hidden_menu_slugs[] = $infinity23_essential_modules_array['update_manager']['admin_page_slug'];

        } // if( $infinity23_update_manager_hide_menu == 1 ) {

    } // if( is_admin() ) {

    // ===========================================================================
    // Apply update behavior filters - only when the module is active
    // runs unconditionally (not only is_admin) since wp-cron triggers auto-updates
    // in the background outside of admin page loads
    // ===========================================================================

    $infinity23_update_manager_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

    if( $infinity23_update_manager_code_is_active == 1 ) {

        // $infinity23_defaults is already set by an earlier module in the essential/loader.php
        // scope, so an isset() check is never true - always load this module's own file
        include( dirname(__FILE__) . "/default-values.php" );

        include( dirname(__FILE__) . "/back-end/update-manager-functions.php" );

        infinity23_update_manager_apply_settings( $infinity23_settings_array_db_name, $infinity23_defaults );

    } // if( $infinity23_update_manager_code_is_active == 1 ) {

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading back-to-top plugin page and data
    // ===========================================================================

    include( dirname(__FILE__) . "/internal-settings.php" );
    include( dirname(__FILE__) . "/../../include/settings.php" );

    global $infinity23_essential_modules_array;

    // ===========================================================================
    // Migrate the old back_to_top_button key to back_to_top_settings
    // Runs once - the old row is deleted after the data is copied over
    // ===========================================================================

    $infinity23_old_back_to_top_data = infinity23_get_option( 'back_to_top_button', '' );

    if( !empty( $infinity23_old_back_to_top_data ) ) {

        if( empty( infinity23_get_option( $infinity23_settings_array_db_name, '' ) ) ) {
            infinity23_update_option( $infinity23_settings_array_db_name, $infinity23_old_back_to_top_data );
        } // if( empty( infinity23_get_option( $infinity23_settings_array_db_name, '' ) ) ) {

        infinity23_delete_option( 'back_to_top_button' );

    } // if( !empty( $infinity23_old_back_to_top_data ) ) {

    // ===========================================================================
    // Loading admin page - admin only
    // ===========================================================================

    if( is_admin() ) {

        add_action( 'admin_menu', function() {

        	global $infinity23_essential_modules_array;

        	$infinity23_back_to_top_hook = add_submenu_page(
        		'essential-include-exclude',
        		$infinity23_essential_modules_array['back_to_top']['module_display_name'],
        		$infinity23_essential_modules_array['back_to_top']['module_display_name'],
        		'manage_options',
        		$infinity23_essential_modules_array['back_to_top']['admin_page_slug'],
        		function() {
        			include( dirname(__FILE__) . "/admin-output.php" );
        		}
        	);

        	add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_back_to_top_hook ) {

        		if( $hook !== $infinity23_back_to_top_hook ) { return; }

        		infinity23_enqueue_essential_styles();

        	} ); // add_action( 'admin_enqueue_scripts'

        } ); // add_action( 'admin_menu'

        $infinity23_back_to_top_menu_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
        include( dirname(__FILE__) . "/default-values.php" );
        $infinity23_back_to_top_hide_menu = intval( infinity23_get_data( $infinity23_essential_modules_array['back_to_top']['hide_menu_field'], $infinity23_back_to_top_menu_settings_data, $infinity23_defaults ) );

        if( $infinity23_back_to_top_hide_menu == 1 ) {

            global $infinity23_hidden_menu_slugs;
            $infinity23_hidden_menu_slugs[] = $infinity23_essential_modules_array['back_to_top']['admin_page_slug'];

        } // if( $infinity23_back_to_top_hide_menu == 1 ) {

    } // if( is_admin() ) {

    // ===========================================================================
    // Loading front-end code if the code is active
    // ===========================================================================

    $infinity23_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

    if( !is_admin() && $infinity23_code_is_active == 1 ) {
    	include( dirname(__FILE__) . "/front-end/front-end.php" );
    } // if( !is_admin() && $infinity23_code_is_active == 1 ) {

?>
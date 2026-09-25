<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading privacy policy cookie consent plugin page and data
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

        	$infinity23_privacy_policy_hook = add_submenu_page(
        		'essential-include-exclude',
        		$infinity23_essential_modules_array['privacy_policy']['module_display_name'],
        		$infinity23_essential_modules_array['privacy_policy']['module_display_name'],
        		'manage_options',
        		$infinity23_essential_modules_array['privacy_policy']['admin_page_slug'],
        		function() {
        			include( dirname(__FILE__) . "/admin-output.php" );
        		}
        	);

        	add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_privacy_policy_hook ) {

        		if( $hook !== $infinity23_privacy_policy_hook ) { return; }

        		infinity23_enqueue_essential_styles();

        	} ); // add_action( 'admin_enqueue_scripts'

        } ); // add_action( 'admin_menu'

        $infinity23_privacy_policy_menu_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
        include( dirname(__FILE__) . "/default-values.php" );
        $infinity23_privacy_policy_hide_menu = intval( infinity23_get_data( $infinity23_essential_modules_array['privacy_policy']['hide_menu_field'], $infinity23_privacy_policy_menu_settings_data, $infinity23_defaults ) );

        if( $infinity23_privacy_policy_hide_menu == 1 ) {

            global $infinity23_hidden_menu_slugs;
            $infinity23_hidden_menu_slugs[] = $infinity23_essential_modules_array['privacy_policy']['admin_page_slug'];

        } // if( $infinity23_privacy_policy_hide_menu == 1 ) {

    } // if( is_admin() ) {

    // ===========================================================================
    // Loading front-end code if the code is active
    // ===========================================================================

    $infinity23_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

    if( !is_admin() && $infinity23_code_is_active == 1 ) {
    	include( dirname(__FILE__) . "/front-end/front-end.php" );
    } // if( !is_admin() && $infinity23_code_is_active == 1 ) {

?>
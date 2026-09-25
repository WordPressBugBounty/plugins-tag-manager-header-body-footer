<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading tag manager plugin page and data
    // ===========================================================================

    include( dirname(__FILE__) . "/internal-settings.php" );
    include( dirname(__FILE__) . "/../../include/settings.php" );

    global $infinity23_essential_modules_array;

    // ===========================================================================
    // Public action functions
    // Declared here and not in front-end.php because front-end.php only loads when !is_admin() and the activation code is 1. 
    // These functions are documented for developers to call from their theme or from an external file, so they must always exist.
    // When the module is off nothing is hooked to the action and they simply output nothing.
    // ===========================================================================

    // --------------------------------------------
    // output the head section codes
    // --------------------------------------------

    if( !function_exists( 'infinity23_tag_manager_head' ) ) {

        function infinity23_tag_manager_head() {

            do_action( 'infinity23_tag_manager_head' );

        } // function infinity23_tag_manager_head() {

    } // if( !function_exists( 'infinity23_tag_manager_head' ) ) {

    // --------------------------------------------
    // output the codes after the opening body tag
    // --------------------------------------------

    if( !function_exists( 'infinity23_tag_manager_below_body' ) ) {

        function infinity23_tag_manager_below_body() {

            do_action( 'infinity23_tag_manager_below_body' );

        } // function infinity23_tag_manager_below_body() {

    } // if( !function_exists( 'infinity23_tag_manager_below_body' ) ) {

    // --------------------------------------------
    // output the codes before the closing body tag
    // --------------------------------------------

    if( !function_exists( 'infinity23_tag_manager_before_closing_body' ) ) {

        function infinity23_tag_manager_before_closing_body() {

            do_action( 'infinity23_tag_manager_before_closing_body' );

        } // function infinity23_tag_manager_before_closing_body() {

    } // if( !function_exists( 'infinity23_tag_manager_before_closing_body' ) ) {

    // ===========================================================================
    // Loading admin page - admin only
    // ===========================================================================

    if( is_admin() ) {

        add_action( 'admin_menu', function() {

        	global $infinity23_essential_modules_array;

        	$infinity23_tag_manager_hook = add_submenu_page(
        		'essential-include-exclude',
        		$infinity23_essential_modules_array['tag_manager']['module_display_name'],
        		$infinity23_essential_modules_array['tag_manager']['module_display_name'],
        		'manage_options',
        		$infinity23_essential_modules_array['tag_manager']['admin_page_slug'],
        		function() {
        			include( dirname(__FILE__) . "/admin-output.php" );
        		}
        	);

        	add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_tag_manager_hook ) {

        		if( $hook !== $infinity23_tag_manager_hook ) { return; }

        		infinity23_enqueue_essential_styles();

        	} ); // add_action( 'admin_enqueue_scripts'

        } ); // add_action( 'admin_menu'

        $infinity23_tag_manager_menu_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
        include( dirname(__FILE__) . "/default-values.php" );
        $infinity23_tag_manager_hide_menu = intval( infinity23_get_data( $infinity23_essential_modules_array['tag_manager']['hide_menu_field'], $infinity23_tag_manager_menu_settings_data, $infinity23_defaults ) );

        if( $infinity23_tag_manager_hide_menu == 1 ) {

            global $infinity23_hidden_menu_slugs;
            $infinity23_hidden_menu_slugs[] = $infinity23_essential_modules_array['tag_manager']['admin_page_slug'];

        } // if( $infinity23_tag_manager_hide_menu == 1 ) {

    } // if( is_admin() ) {

    // ===========================================================================
    // Loading front-end code if the code is active
    // ===========================================================================

    $infinity23_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

    if( !is_admin() && $infinity23_code_is_active == 1 ) {
    	include( dirname(__FILE__) . "/front-end/front-end.php" );
    } // if( !is_admin() && $infinity23_code_is_active == 1 ) {

?>
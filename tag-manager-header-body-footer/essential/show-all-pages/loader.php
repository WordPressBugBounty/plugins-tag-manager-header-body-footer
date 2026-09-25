<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading show-all-pages plugin page and data
    // ===========================================================================

    include( dirname(__FILE__) . "/../../include/settings.php" );
    include( dirname(__FILE__) . "/internal-settings.php" );

    global $infinity23_essential_modules_array;

    // ===========================================================================
    // Loading admin page - admin only
    // ===========================================================================

    if( is_admin() ) {

        add_action( 'admin_menu', function() {

        	global $infinity23_essential_modules_array;

        	$infinity23_show_all_pages_hook = add_submenu_page(
        		'essential-include-exclude',
        		$infinity23_essential_modules_array['show_all_pages']['module_display_name'],
        		$infinity23_essential_modules_array['show_all_pages']['module_display_name'],
        		'manage_options',
        		$infinity23_essential_modules_array['show_all_pages']['admin_page_slug'],
        		function() {
        			include_once( dirname(__FILE__) . "/admin-output.php" );
        		}
        	);

        	add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_show_all_pages_hook ) {

        		if( $hook !== $infinity23_show_all_pages_hook ) { return; }

        		infinity23_enqueue_essential_styles();

        	} ); // add_action( 'admin_enqueue_scripts'

        } ); // add_action( 'admin_menu'

    } // if( is_admin() ) {

    // ===========================================================================
    // Ajax pagination handler
    // ===========================================================================

    add_action( 'wp_ajax_infinity23_show_all_pages_page', function() {

        if( !check_ajax_referer( 'infinity23_show_all_pages_ajax', 'nonce', false ) ) {
            wp_die( 'Invalid nonce', '', array( 'response' => 400 ) );
        } // if( !check_ajax_referer( 'infinity23_show_all_pages_ajax', 'nonce', false ) ) {

        if( !current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied', '', array( 'response' => 403 ) );
        } // if( !current_user_can( 'manage_options' ) ) {

        if( !defined( 'INFINITY23_SAP_AJAX' ) ) {
            define( 'INFINITY23_SAP_AJAX', true );
        } // if( !defined( 'INFINITY23_SAP_AJAX' ) ) {

        include_once( dirname(__FILE__) . "/admin-output.php" );

        wp_die();

    } ); // add_action( 'wp_ajax_infinity23_show_all_pages_page'

?>
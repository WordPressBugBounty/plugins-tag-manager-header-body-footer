<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading llms and robots editor plugin page
    // ===========================================================================

    include( dirname(__FILE__) . "/internal-settings.php" );
    include( dirname(__FILE__) . "/../../include/settings.php" );

    // ===========================================================================
    // Loading admin page - admin only
    // ===========================================================================

    if( is_admin() ) {

        add_action( 'admin_menu', function() {

        	$infinity23_llms_and_robots_editor_hook = add_submenu_page(
        		'essential-include-exclude',
        		'LLMs.txt & Robots.txt',
        		'LLMs.txt & Robots.txt',
        		'manage_options',
        		'essential-llms-and-robots-editor',
        		function() {
        			include( dirname(__FILE__) . "/admin-output.php" );
        		}
        	);

        	add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_llms_and_robots_editor_hook ) {

        		if( $hook !== $infinity23_llms_and_robots_editor_hook ) { return; }

        		infinity23_enqueue_essential_styles();

        	} ); // add_action( 'admin_enqueue_scripts'

        } ); // add_action( 'admin_menu'

    } // if( is_admin() ) {

?>
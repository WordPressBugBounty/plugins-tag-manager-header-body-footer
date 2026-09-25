<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading include-exclude plugin page and data
    // ===========================================================================

    include( dirname(__FILE__) . "/../../include/settings.php" );

    // ===========================================================================
    // Loading admin page - admin only
    // ===========================================================================

    if( is_admin() ) {

        add_action( 'admin_menu', function() {

            $infinity23_include_exclude_hook = add_submenu_page(
                'essential-include-exclude',
                'Include / Exclude',
                'Include / Exclude',
                'manage_options',
                'essential-include-exclude',
                function() {
                    include( dirname(__FILE__) . "/admin-output.php" );
                }
            );

            add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_include_exclude_hook ) {

                if( $hook !== $infinity23_include_exclude_hook ) { return; }

                infinity23_enqueue_essential_styles();

            } ); // add_action( 'admin_enqueue_scripts'

        } ); // add_action( 'admin_menu'

    } // if( is_admin() ) {

?>
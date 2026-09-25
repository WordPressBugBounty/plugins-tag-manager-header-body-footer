<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // --------------------------------------------
    // main database data info
    // --------------------------------------------

    global $wpdb, $infinity23_table_name;
    $infinity23_table_name = $wpdb->prefix . "infinity23_options"; // Database Table Name

    // --------------------------------------------
    // option name that flags "a Danger Zone reset just happened and no
    // module has been reactivated yet". Stored as a normal row in the
    // plugin's own infinity23_options table (via infinity23_get_option /
    // infinity23_update_option / infinity23_delete_option) - Danger Zone only
    // TRUNCATEs that table, it never drops it, and the table is reinstalled
    // empty right after anyway, so this flag survives the reset the same
    // way every other plugin option does.
    // --------------------------------------------

    if( !defined( 'INFINITY23_RESET_PENDING_OPTION' ) ) {
        define( 'INFINITY23_RESET_PENDING_OPTION', 'infinity23_reset_pending' );
    } // if( !defined( 'INFINITY23_RESET_PENDING_OPTION' ) ) {

    // --------------------------------------------
    // Pro version sales page url - single place to update it, read by
    // infinity23_pro_version_link() in pro-functions.php
    // --------------------------------------------

    if( !defined( 'INFINITY23_PRO_VERSION_LINK' ) ) {
        define( 'INFINITY23_PRO_VERSION_LINK', 'https://infinity23.com/' );
    } // if( !defined( 'INFINITY23_PRO_VERSION_LINK' ) ) {

?>
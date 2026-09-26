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

    // --------------------------------------------
    // License server endpoint - the only url this plugin ever sends a license
    // key to, read by infinity23_license_request() in
    // include/functions/pro-functions/license-functions.php. The trailing
    // slash matters: the request is sent with redirection => 0, so the url
    // has to be the exact one the server answers on without redirecting
    // --------------------------------------------

    if( !defined( 'INFINITY23_LICENSE_SERVER_URL' ) ) {
        define( 'INFINITY23_LICENSE_SERVER_URL', 'https://infinity23.com/activate-license/' );
    } // if( !defined( 'INFINITY23_LICENSE_SERVER_URL' ) ) {

    // --------------------------------------------
    // The secret the license messages are encrypted with, on top of https.
    // MUST MATCH the same numbered entry in $infinity23_shared_secrets in
    // activate-license/include/settings.php on the server.
    //
    // This is not a password. The plugin ships to every customer, so this
    // string sits in readable php on their server and anyone determined can
    // find it. What it buys: the request body is unreadable to anything
    // between the site and the server, and the answer cannot be forged - so
    // pointing infinity23.com at a fake server in a hosts file no longer hands
    // out a valid license.
    //
    // Only ever roll this by shipping a NEW version number, never by editing
    // this one - the server keeps answering old versions.
    // --------------------------------------------

    if( !defined( 'INFINITY23_LICENSE_KEY_VERSION' ) ) {
        define( 'INFINITY23_LICENSE_KEY_VERSION', 1 );
    } // if( !defined( 'INFINITY23_LICENSE_KEY_VERSION' ) ) {

    if( !defined( 'INFINITY23_LICENSE_SHARED_SECRET' ) ) {
        define( 'INFINITY23_LICENSE_SHARED_SECRET', '1a29dd282a9607963960b7e69fbe1c4b3157691948180fadae02da1c9828fad6' );
    } // if( !defined( 'INFINITY23_LICENSE_SHARED_SECRET' ) ) {

    // --------------------------------------------
    // the shape of the messages themselves. Bumped only when the fields change
    // in a way an older server would not understand - the server branches on
    // it, so a rewritten endpoint can serve old and new plugins side by side
    // --------------------------------------------

    if( !defined( 'INFINITY23_LICENSE_API_VERSION' ) ) {
        define( 'INFINITY23_LICENSE_API_VERSION', 1 );
    } // if( !defined( 'INFINITY23_LICENSE_API_VERSION' ) ) {

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Fresh Install - the install process used for a normal new install, and for
// every later reinstall, reactivation or recreated table
//
// Included only from install.php, and only the one time the main options
// table is actually being created
// ===========================================================================

// ----------------------------------------------
// Activate every module that is enabled by default on the Include / Exclude
// page, so a fresh install doesn't require opening each module's own page
// just to flip its own Activate toggle
// ----------------------------------------------

infinity23_activate_default_modules();

// ----------------------------------------------
// remember how this site got here - useful for support, since a fresh install
// and an upgrade from the old Tag Manager plugin behave differently
// ----------------------------------------------

infinity23_update_option( 'plugin_install_type', 'fresh_install' );

// ----------------------------------------------
// write the marker row in wp_options that says this site already went through
// the install process once.
//
// This is written on a fresh install too, and not only on an upgrade, on
// purpose - it is what stops a later reinstall (or a manually deleted table
// being recreated) from ever being treated as a first install and taking the
// Tag Manager upgrade path with its notice
// ----------------------------------------------

if( function_exists( 'infinity23_tm_upgrade_mark_install_decision' ) ) {
    infinity23_tm_upgrade_mark_install_decision( 'fresh_install' );
} // if( function_exists( 'infinity23_tm_upgrade_mark_install_decision' ) ) {

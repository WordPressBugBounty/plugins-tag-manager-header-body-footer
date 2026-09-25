<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

include( dirname(__FILE__) . '/../settings.php' ); // Load the files to get the database info
include_once( dirname(__FILE__) . '/../functions/main-functions.php' ); // Load infinity23_activate_default_modules()
include_once( dirname(__FILE__) . '/../../tag-manager-upgrade/loader.php' ); // Load the self contained Tag Manager upgrade folder

// ----------------------------------------------
// create the main options database
// ----------------------------------------------

global $wpdb;
global $infinity23_table_name;

$charset_collate = $wpdb->get_charset_collate();
require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); // Require to use dbDelta

// ----------------------------------------------
// asked BEFORE the table below is created, because creating it is exactly
// what would make the answer wrong. True means this plugin already ran on
// this site once - a reactivation, a reinstall, or a manually deleted table
// being recreated - and never a genuine first install
// ----------------------------------------------

$infinity23_previous_install_found = infinity23_tm_upgrade_previous_install_found( true );

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- schema install - SHOW TABLES / dbDelta on custom table, no WP core API; caching not yet implemented, tracked for later
if( $wpdb->get_var("SHOW TABLES LIKE '" . esc_sql( $infinity23_table_name ) . "' ") != $infinity23_table_name ) {

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- inherent to defining this plugin's dedicated table
    $infinity23_sql = "CREATE TABLE " . $infinity23_table_name . "(
    id INTEGER(11) UNSIGNED AUTO_INCREMENT,
    option_type VARCHAR(100) NOT NULL DEFAULT 'settings',
    option_name VARCHAR(200) NOT NULL DEFAULT '',
    option_value LONGTEXT,
    time_created_utc INT(11) DEFAULT NULL,
    time_updated_utc INT(11) DEFAULT NULL,
    data1 LONGTEXT DEFAULT NULL,
    data2 LONGTEXT DEFAULT NULL,
    data3 LONGTEXT DEFAULT NULL,
    data4 LONGTEXT DEFAULT NULL,
    data5 LONGTEXT DEFAULT NULL,
    data6 LONGTEXT DEFAULT NULL,
    data7 LONGTEXT DEFAULT NULL,
    data8 LONGTEXT DEFAULT NULL,
    data9 LONGTEXT DEFAULT NULL,
    data10 LONGTEXT DEFAULT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    dbDelta($infinity23_sql);

    // ----------------------------------------------
    // this block only runs the one time the table is actually being created -
    // a true first install, an upgrade from the old standalone Tag Manager
    // plugin, or a manually deleted database being recreated.
    //
    // There are two separate install processes and this is where one of them
    // is chosen:
    //
    //   tag-manager-upgrade/install-process.php - the old standalone
    //   "YYDevelopment - Tag Manager - Header, Body And Footer" plugin was
    //   found on this site, so its settings are carried over and Tag Manager
    //   is the only module left switched on
    //
    //   install-fresh.php - a normal new install, every module that is on by
    //   default on the Include / Exclude page is switched on
    //
    // The upgrade process is only ever allowed on a genuine first install -
    // a site with no trace of this plugin at all. A reactivation, a reinstall
    // or a recreated table always takes the fresh path, so the upgrade can
    // never replay itself and the upgrade notice can never come back
    // ----------------------------------------------

    if( $infinity23_previous_install_found !== true && infinity23_tm_upgrade_legacy_detected() ) {
        include( dirname(__FILE__) . '/../../tag-manager-upgrade/install-process.php' );
    } else {
        include( dirname(__FILE__) . '/install-fresh.php' );
    } // if( $infinity23_previous_install_found !== true && infinity23_tm_upgrade_legacy_detected() ) {

} // if( $wpdb->get_var("SHOW TABLES LIKE '" . esc_sql( $infinity23_table_name ) . "' ") != $infinity23_table_name ) {

// ----------------------------------------------
// record the first time the plugin was ever installed
// only inserts once - infinity23_add_option skips if the row already exists
// must run after the table exists, otherwise the insert fails silently
// ----------------------------------------------

infinity23_add_option( 'plugin_install_time', infinity23_current_timestamp() );

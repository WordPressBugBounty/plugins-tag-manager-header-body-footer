<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Tag Manager Upgrade - Install Process
//
// The install process used when the old standalone
// "YYDevelopment - Tag Manager - Header, Body And Footer" plugin was found on
// this site, which means Infinity23 is replacing it and not starting fresh.
//
// Included only from include/install-db/install.php, and only on a genuine
// first install - a site with no trace of Infinity23 anywhere. A reactivation,
// a reinstall or a recreated table always takes install-fresh.php instead.
//
// Two things are different from a fresh install:
//   1. every old Tag Manager setting is copied over first
//   2. Tag Manager is the only module left switched on - nothing else is
//      turned on behind the admin's back on a site that only ever asked for
//      a tag manager
// ===========================================================================

include_once( dirname(__FILE__) . '/loader.php' );

// ----------------------------------------------
// copy every old option, tag code box, lazy load script and note into the
// Infinity23 Tag Manager module, and keep a full untouched backup of the old
// data. The old wp_options rows and the old {prefix}yydev_tagmanager table
// are only read here - they are never changed or removed
// ----------------------------------------------

infinity23_tm_upgrade_migrate_data();

// ----------------------------------------------
// only Tag Manager is open after an upgrade - every other module starts off
// and waits for the admin to choose it on the Include / Exclude page
// ----------------------------------------------

infinity23_tm_upgrade_activate_tag_manager_only();

// ----------------------------------------------
// show the upgrade notice instead of the new install welcome notice
// ----------------------------------------------

infinity23_tm_upgrade_flag_notice();

// ----------------------------------------------
// remember how this site got here. The row in the plugin's own table is for
// the plugin to read, the marker row in wp_options is what makes sure this
// whole process can never run a second time on this site
// ----------------------------------------------

infinity23_update_option( 'plugin_install_type', 'tag_manager_upgrade' );

infinity23_tm_upgrade_mark_install_decision( 'tag_manager_upgrade' );

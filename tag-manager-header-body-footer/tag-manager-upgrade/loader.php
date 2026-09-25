<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Tag Manager Upgrade - self contained folder
//
// Everything needed to turn a site running the old standalone
// "YYDevelopment - Tag Manager - Header, Body And Footer" plugin into an
// Infinity23 site lives in this one folder and nowhere else.
//
// Nothing in here is part of the normal running plugin. It is only touched
// during the install process, so it can never interfere with the module code,
// and the whole folder can be deleted in one piece once the upgrade path is
// no longer needed - see the removal notes at the bottom of this file.
//
// Every function and constant in this folder is prefixed infinity23_tm_upgrade_
// / INFINITY23_TM_UPGRADE_ so no name here can ever collide with the rest of
// the plugin, or with the old plugin's own yydev_ functions.
//
// IMPORTANT - nothing in this folder ever deletes, renames or rewrites the old
// plugin's data. The old wp_options rows and the old {prefix}yydev_tagmanager
// table are read only and are left exactly as they are.
// ===========================================================================

	include_once( dirname(__FILE__) . "/detection.php" );   // is this an upgrade, and is this a genuine first install
	include_once( dirname(__FILE__) . "/legacy-data.php" ); // reading and parsing the old plugin's saved data
	include_once( dirname(__FILE__) . "/migration.php" );   // writing that data into the Tag Manager module

	// --------------------------------------------
	// The two files NOT included here, because they are only ever loaded at
	// the one moment they are needed:
	//
	//   install-process.php - the upgrade install process itself, included by
	//   include/install-db/install.php when it picks the upgrade path
	//
	//   upgrade-notice.php - the admin notice, included by notices/loader.php
	//   after the notices engine is loaded
	// --------------------------------------------

	// --------------------------------------------
	// To remove the upgrade path completely at a later version:
	//
	//   1. delete this whole tag-manager-upgrade/ folder
	//   2. in include/install-db/install.php remove the include of this
	//      loader and always include install-fresh.php
	//   3. in notices/loader.php remove the include of upgrade-notice.php
	//
	// Nothing else in the plugin references this folder.
	// --------------------------------------------

?>

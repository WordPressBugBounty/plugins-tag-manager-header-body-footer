<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Loading the admin notices system
// Every notice file added to this folder plugs into the same engine below
// ===========================================================================

	include( dirname(__FILE__) . "/notice-functions.php" );

	// --------------------------------------------
	// individual notices - add new notice files here as they are created
	// --------------------------------------------

	include( dirname(__FILE__) . "/notice-messages/review-notice.php" );
	include( dirname(__FILE__) . "/notice-messages/onboarding-notice.php" );
	// --------------------------------------------
	// the Tag Manager upgrade notice lives in the self contained
	// tag-manager-upgrade/ folder with the rest of the upgrade code, and is
	// only included here because it needs the notices engine above. Remove
	// this one line to drop it together with that folder
	// --------------------------------------------

	include( dirname(__FILE__) . "/../tag-manager-upgrade/upgrade-notice.php" );

?>
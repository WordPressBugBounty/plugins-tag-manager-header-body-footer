<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Loading the cron system
// ===========================================================================

	include( dirname(__FILE__) . "/hourly-cron.php" );
	include( dirname(__FILE__) . "/daily-cron.php" );

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Redirect 301/302 - Redirect Log cleanup, called from the daily cron
// ===========================================================================

	if( !function_exists( 'infinity23_redirect_log_cleanup_old_logs' ) ) {

		function infinity23_redirect_log_cleanup_old_logs( $retention_days, $max_count ) {

			infinity23_redirect_log_delete_older_than( $retention_days );
			infinity23_redirect_log_delete_excess( $max_count );

		} // function infinity23_redirect_log_cleanup_old_logs( $retention_days, $max_count ) {

	} // if( !function_exists( 'infinity23_redirect_log_cleanup_old_logs' ) ) {

?>
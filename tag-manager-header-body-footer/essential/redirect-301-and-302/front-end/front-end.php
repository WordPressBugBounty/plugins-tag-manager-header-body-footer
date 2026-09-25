<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Redirect 301/302 front-end output - runs the matching against the request
	// ===========================================================================

	include( dirname(__FILE__) . "/../../../include/settings.php" );
	include( dirname(__FILE__) . "/../internal-settings.php" );
	include( dirname(__FILE__) . "/../back-end/redirect-db-functions.php" );
	include( dirname(__FILE__) . "/../back-end/redirect-log-db-functions.php" );
	include( dirname(__FILE__) . "/../back-end/redirect-matching.php" );

	$infinity23_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	if( !empty( $infinity23_code_is_active ) ) {

		include( dirname(__FILE__) . "/../default-values.php" );

		$infinity23_redirect_log_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
		$infinity23_redirect_log_enabled = intval( infinity23_pro_get_data( 'redirect_log_enabled', $infinity23_redirect_log_settings_data, $infinity23_defaults ) );

		infinity23_redirect_run_matching( $infinity23_redirect_log_enabled );

	} // if( !empty( $infinity23_code_is_active ) ) {

?>
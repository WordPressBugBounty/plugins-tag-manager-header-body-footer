<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Force SSL & HTTPS back-end output - force ssl redirect + mixed content fixer
	// ===========================================================================

	include( dirname(__FILE__) . "/../../../include/settings.php" );
	include( dirname(__FILE__) . "/../internal-settings.php" );

	$infinity23_get_data_json = infinity23_get_option($infinity23_settings_array_db_name);
	$infinity23_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	if( !empty($infinity23_code_is_active) ) {

		$infinity23_ssl_data_array = infinity23_convert_json_to_array($infinity23_get_data_json);

		include( dirname(__FILE__) . "/../default-values.php" );

		$infinity23_force_ssl_checkbox = intval( infinity23_pro_get_data( 'force_ssl_checkbox', $infinity23_ssl_data_array, $infinity23_defaults ) );

		$infinity23_force_ssl_detection_mode = infinity23_pro_get_data( 'force_ssl_detection_mode', $infinity23_ssl_data_array, $infinity23_defaults );
		if( !in_array( $infinity23_force_ssl_detection_mode, array( 'standard', 'extended' ), true ) ) {
			$infinity23_force_ssl_detection_mode = 'extended';
		} // if( !in_array( $infinity23_force_ssl_detection_mode, array( 'standard', 'extended' ), true ) ) {

		$infinity23_force_ssl_localhost_mode = infinity23_pro_get_data( 'force_ssl_localhost_mode', $infinity23_ssl_data_array, $infinity23_defaults );
		if( !in_array( $infinity23_force_ssl_localhost_mode, array( 'force', 'exclude' ), true ) ) {
			$infinity23_force_ssl_localhost_mode = 'exclude';
		} // if( !in_array( $infinity23_force_ssl_localhost_mode, array( 'force', 'exclude' ), true ) ) {
		$infinity23_fix_mixed_content_checkbox = intval( infinity23_pro_get_data( 'fix_mixed_content_checkbox', $infinity23_ssl_data_array, $infinity23_defaults ) );

		// ===========================================================================
		// load the SSL-only functions - security-only functions will live in
		// their own security-functions.php file next to this one
		// ===========================================================================

		include( dirname(__FILE__) . "/ssl-functions.php" );

		if( $infinity23_force_ssl_checkbox == 1 ) {

			add_action( 'template_redirect', function() use ( $infinity23_force_ssl_detection_mode, $infinity23_force_ssl_localhost_mode ) {
				infinity23_ssl_maybe_redirect_to_https( $infinity23_force_ssl_detection_mode, $infinity23_force_ssl_localhost_mode );
			}, 0 );

			// checked immediately here (not hooked to admin_init) - WordPress
			// core calls auth_redirect() in wp-admin/admin.php BEFORE admin_init
			// ever fires, so an http request carrying an https-only auth cookie
			// gets denied by WordPress itself before our admin_init hook could
			// run. This file already loads on the 'init' hook (see essential/loader.php),
			// which always runs before auth_redirect(), so checking here catches
			// the request in time.
			if( is_admin() ) {
				infinity23_ssl_maybe_redirect_to_https( $infinity23_force_ssl_detection_mode, $infinity23_force_ssl_localhost_mode );
			} // if( is_admin() ) {

			add_action( 'login_init', function() use ( $infinity23_force_ssl_detection_mode, $infinity23_force_ssl_localhost_mode ) {
				infinity23_ssl_maybe_redirect_to_https( $infinity23_force_ssl_detection_mode, $infinity23_force_ssl_localhost_mode );
			}, 0 );

			add_action( 'rest_api_init', function() use ( $infinity23_force_ssl_detection_mode, $infinity23_force_ssl_localhost_mode ) {
				infinity23_ssl_maybe_redirect_to_https( $infinity23_force_ssl_detection_mode, $infinity23_force_ssl_localhost_mode );
			}, ~PHP_INT_MAX );

		} // if( $infinity23_force_ssl_checkbox == 1 ) {

		if( $infinity23_fix_mixed_content_checkbox == 1 ) {

			add_action( 'template_redirect', 'infinity23_ssl_start_output_buffer', 1 );
			add_action( 'shutdown', 'infinity23_ssl_end_output_buffer', 999 );

		} // if( $infinity23_fix_mixed_content_checkbox == 1 ) {

	} // if( !empty($infinity23_code_is_active) ) {

?>
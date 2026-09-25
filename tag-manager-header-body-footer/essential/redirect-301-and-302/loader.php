<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Loading redirect 301/302 page and data
	// ===========================================================================

	include( dirname(__FILE__) . "/internal-settings.php" );
	include( dirname(__FILE__) . "/../../include/settings.php" );
	include( dirname(__FILE__) . "/back-end/redirect-db-functions.php" );

	global $infinity23_essential_modules_array;

	// ===========================================================================
	// Loading admin page - admin only
	// ===========================================================================

	if( is_admin() ) {

		add_action( 'admin_menu', function() {

			global $infinity23_essential_modules_array;

			$infinity23_redirect_hook = add_submenu_page(
				'essential-include-exclude',
				$infinity23_essential_modules_array['redirect_301_and_302']['module_display_name'],
				$infinity23_essential_modules_array['redirect_301_and_302']['module_display_name'],
				'manage_options',
				$infinity23_essential_modules_array['redirect_301_and_302']['admin_page_slug'],
				function() {
					include( dirname(__FILE__) . "/admin-output.php" );
				}
			);

		add_action( 'admin_enqueue_scripts', function( $hook ) use ( $infinity23_redirect_hook ) {

			if( $hook !== $infinity23_redirect_hook ) { return; }

			infinity23_enqueue_essential_styles();

		} ); // add_action( 'admin_enqueue_scripts'

		} ); // add_action( 'admin_menu'

		$infinity23_redirect_menu_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
		include( dirname(__FILE__) . "/default-values.php" );
		$infinity23_redirect_hide_menu = intval( infinity23_get_data( $infinity23_essential_modules_array['redirect_301_and_302']['hide_menu_field'], $infinity23_redirect_menu_settings_data, $infinity23_defaults ) );

		if( $infinity23_redirect_hide_menu == 1 ) {

			global $infinity23_hidden_menu_slugs;
			$infinity23_hidden_menu_slugs[] = $infinity23_essential_modules_array['redirect_301_and_302']['admin_page_slug'];

		} // if( $infinity23_redirect_hide_menu == 1 ) {

		add_action( 'admin_post_infinity23_redirect_log_clear_all', function() {

			if( !current_user_can( 'manage_options' ) ) {
				wp_die( 'Permission denied.' );
			} // if( !current_user_can( 'manage_options' ) ) {

			check_admin_referer( 'infinity23_redirect_log_clear_all_action', 'infinity23_redirect_log_clear_all_nonce' );

			include_once( dirname(__FILE__) . "/back-end/redirect-log-db-functions.php" );
			infinity23_redirect_log_clear_all();

			wp_safe_redirect( admin_url( 'admin.php?page=essential-redirect-301-and-302&redirect_tab=logs' ) );
			exit;

		} ); // add_action( 'admin_post_infinity23_redirect_log_clear_all'

		add_action( 'wp_ajax_infinity23_redirect_generate_shortcode', function() use ( $infinity23_settings_array_db_name ) {

			if( !check_ajax_referer( 'infinity23_redirect_shortcode_action', 'nonce', false ) ) {
				wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
			} // if( !check_ajax_referer(

			if( !current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => 'Permission denied' ) );
			} // if( !current_user_can( 'manage_options' ) ) {

			include_once( dirname(__FILE__) . "/back-end/redirect-db-functions.php" );
			include_once( dirname(__FILE__) . "/default-values.php" );

			$infinity23_shortcode_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
			$infinity23_shortcode_enabled = intval( infinity23_pro_get_data( 'redirect_shortener_enabled', $infinity23_shortcode_settings_data, $infinity23_defaults ) );

			if( $infinity23_shortcode_enabled != 1 ) {
				wp_send_json_error( array( 'message' => 'The URL Shortener System is not enabled' ) );
			} // if( $infinity23_shortcode_enabled != 1 ) {

			$infinity23_shortcode_length = intval( infinity23_pro_get_data( 'redirect_shortener_code_length', $infinity23_shortcode_settings_data, $infinity23_defaults ) );
			$infinity23_shortcode_prefix = infinity23_pro_get_data( 'redirect_shortener_prefix', $infinity23_shortcode_settings_data, $infinity23_defaults );

			$infinity23_generated_url = infinity23_redirect_generate_unique_shortcode( $infinity23_shortcode_length, $infinity23_shortcode_prefix );

			if( empty( $infinity23_generated_url ) ) {
				wp_send_json_error( array( 'message' => 'Could not generate a unique short URL, please try again' ) );
			} // if( empty( $infinity23_generated_url ) ) {

			wp_send_json_success( array( 'request_url' => $infinity23_generated_url ) );

		} ); // add_action( 'wp_ajax_infinity23_redirect_generate_shortcode'

	} // if( is_admin() ) {

	// ===========================================================================
	// Loading front-end code if the code is active
	// ===========================================================================

	$infinity23_redirect_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	// defensive check - creates the redirect tables if they do not exist yet
	// (covers sites that activated Redirect 301/302 before this table system existed)
	// only runs once per site - a saved flag prevents this from querying the database on every admin page load
	if( is_admin() && $infinity23_redirect_code_is_active == 1 && intval( infinity23_get_option( 'redirect_301_and_302_tables_installed', 0 ) ) !== 1 ) {
		infinity23_redirect_install_tables();
		infinity23_update_option( 'redirect_301_and_302_tables_installed', 1 );
	} // if( is_admin() && $infinity23_redirect_code_is_active == 1 && intval( infinity23_get_option( 'redirect_301_and_302_tables_installed', 0 ) ) !== 1 ) {

	if( !is_admin() && $infinity23_redirect_code_is_active == 1 ) {
		include( dirname(__FILE__) . "/front-end/front-end.php" );
	} // if( !is_admin() && $infinity23_redirect_code_is_active == 1 ) {

?>
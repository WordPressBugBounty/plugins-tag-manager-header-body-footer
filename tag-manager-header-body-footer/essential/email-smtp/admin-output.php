<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// =================================================================================================================
	// Initial plugin load and getting default values
	// =================================================================================================================

	include( dirname(__FILE__) . "/../../include/settings.php" );
	include( dirname(__FILE__) . "/internal-settings.php" );
	include_once( dirname(__FILE__) . "/back-end/email-log-db-functions.php" );
	infinity23_load_essential_files();

	// --------------------------------------------
	// save a single provider password/api-key field: keeps the existing
	// encrypted value when the posted value is empty or is still the dot mask
	// --------------------------------------------

	if( !function_exists( 'infinity23_email_smtp_save_password_field' ) ) {

		function infinity23_email_smtp_save_password_field( $post_field_name, $existing_encrypted_value, $context ) {

			// the hidden "_touched" input is only set to 1 by JS the moment the
			// field's value actually changes (typing, paste, autofill, etc) -
			// this is what tells us the field was really changed, instead of
			// guessing from the posted value's content
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- every caller passes through the nonce-verified settings-save block in admin-output.php first
			$touched = isset( $_POST[ $post_field_name . '_touched' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $post_field_name . '_touched' ] ) ) : '0';

			if( $touched !== '1' ) {
				return $existing_encrypted_value;
			} // if( $touched !== '1' ) {

			// never run passwords/api keys through sanitize_text_field() - it silently
			// strips any "%" followed by two hex characters (percent-encoded sequences),
			// which can corrupt real passwords/api keys that happen to contain that pattern
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- every caller passes through the nonce-verified settings-save block in admin-output.php first; sanitize_text_field() is deliberately not used here since it corrupts percent-encoded sequences that can legitimately appear in passwords/API keys (see comment above) - the value is unslashed and later encrypted, never output as HTML or used in SQL
			$posted_value = isset( $_POST[ $post_field_name ] ) && !is_array( $_POST[ $post_field_name ] ) ? trim( wp_unslash( $_POST[ $post_field_name ] ) ) : '';
			$posted_value = infinity23_email_smtp_strip_invisible_chars( $posted_value );

			if( empty( $posted_value ) ) {
				return $existing_encrypted_value;
			} // if( empty( $posted_value ) ) {

			// second safety net independent of the JS "_touched" flag - if the posted
			// value is still only the dot mask (touched fired without a real change,
			// e.g. tab-in-tab-out on some autofill flows), keep the existing value
			if( preg_match( '/^\x{2022}+$/u', $posted_value ) ) {
				return $existing_encrypted_value;
			} // if( preg_match(

			return infinity23_encrypt_value( $posted_value, $context );

		} // function infinity23_email_smtp_save_password_field( $post_field_name, $existing_encrypted_value, $context ) {

	} // if( !function_exists( 'infinity23_email_smtp_save_password_field' ) ) {

	// --------------------------------------------
	// build the dot-mask display info for a single provider password/api-key field
	// --------------------------------------------

	if( !function_exists( 'infinity23_email_smtp_get_password_mask' ) ) {

		function infinity23_email_smtp_get_password_mask( $encrypted_value, $context ) {

			$decrypted = infinity23_decrypt_value( $encrypted_value, $context );

			if( empty( $decrypted ) && !empty( $encrypted_value ) ) {
				$decrypted = infinity23_decrypt_value( $encrypted_value, 'default' );
			} // if( empty( $decrypted ) && !empty( $encrypted_value ) ) {

			return array(
				'is_saved' => !empty( $encrypted_value ),
				'is_undecryptable' => ( !empty( $encrypted_value ) && empty( $decrypted ) ),
				'mask_length' => !empty( $decrypted ) ? strlen( $decrypted ) : 16,
			);

		} // function infinity23_email_smtp_get_password_mask( $encrypted_value, $context ) {

	} // if( !function_exists( 'infinity23_email_smtp_get_password_mask' ) ) {

	$infinity23_success_message = '';
	$infinity23_error_message   = '';

	if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {
		$infinity23_success_message = "The data was updated successfully";
	} // if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {

	include( dirname(__FILE__) . "/default-values.php" );

	// =================================================================================================================
	// Update the data if the form was submitted
	// =================================================================================================================

	// ----------------------------------------
	// reset all email smtp settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_email_smtp_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_email_smtp_reset_nonce'] ) ), 'infinity23_email_smtp_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_email_smtp_reset_nonce'] ) ) {

	// ----------------------------------------
	// code toggle activation form submit
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_email_smtp_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_email_smtp_activate_nonce'] ) ), 'infinity23_email_smtp_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'email_smtp', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "Email SMTP was activated successfully";
			} else {
				infinity23_module_deactivate_redirect( $infinity23_module_display_name );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_email_smtp_activate_nonce'] ) ) {

	// ----------------------------------------
	// Submit the main settings form
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_email_smtp_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_email_smtp_nonce'] ) ), 'infinity23_email_smtp_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();

			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_data_array['smtp_provider'] = infinity23_pro_field_sanitize( 'smtp_provider', $infinity23_existing_data_array, $infinity23_defaults );
			if( !in_array( $infinity23_data_array['smtp_provider'], array( 'wordpress_default', 'custom', 'gmail', 'amazon_ses', 'elastic_email', 'sendgrid', 'mailgun', 'maileroo' ), true ) ) {
				$infinity23_data_array['smtp_provider'] = 'wordpress_default';
			} // if( !in_array(

			// every provider's fields are saved every time regardless of which one is
			// currently selected, since the form always renders all of them - this is
			// what lets the user switch providers without losing earlier credentials

			$infinity23_data_array['smtp_custom_host'] = infinity23_pro_field_sanitize( 'smtp_custom_host', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['smtp_custom_port'] = infinity23_pro_field_sanitize( 'smtp_custom_port', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['smtp_custom_encryption'] = infinity23_pro_field_sanitize( 'smtp_custom_encryption', $infinity23_existing_data_array, $infinity23_defaults );
			if( !in_array( $infinity23_data_array['smtp_custom_encryption'], array( 'none', 'ssl', 'tls' ), true ) ) {
				$infinity23_data_array['smtp_custom_encryption'] = 'tls';
			} // if( !in_array(
			$infinity23_data_array['smtp_custom_auth_type'] = infinity23_pro_field_sanitize( 'smtp_custom_auth_type', $infinity23_existing_data_array, $infinity23_defaults );
			if( !in_array( $infinity23_data_array['smtp_custom_auth_type'], array( 'none', 'plain', 'login' ), true ) ) {
				$infinity23_data_array['smtp_custom_auth_type'] = 'plain';
			} // if( !in_array(
			$infinity23_data_array['smtp_custom_username'] = infinity23_email_smtp_strip_invisible_chars( infinity23_post_sanitize('smtp_custom_username', 'text') );
			$infinity23_data_array['smtp_custom_password'] = infinity23_email_smtp_save_password_field( 'smtp_custom_password', infinity23_pro_get_data( 'smtp_custom_password', $infinity23_existing_data_array, $infinity23_defaults ), 'email_smtp_custom_password' );

			$infinity23_data_array['smtp_gmail_username'] = infinity23_email_smtp_strip_invisible_chars( infinity23_post_sanitize('smtp_gmail_username', 'text') );
			$infinity23_data_array['smtp_gmail_password'] = infinity23_email_smtp_save_password_field( 'smtp_gmail_password', infinity23_pro_get_data( 'smtp_gmail_password', $infinity23_existing_data_array, $infinity23_defaults ), 'email_smtp_gmail_password' );

			$infinity23_data_array['smtp_ses_host'] = infinity23_pro_field_sanitize( 'smtp_ses_host', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['smtp_ses_port'] = infinity23_pro_field_sanitize( 'smtp_ses_port', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['smtp_ses_encryption'] = infinity23_pro_field_sanitize( 'smtp_ses_encryption', $infinity23_existing_data_array, $infinity23_defaults );
			if( !in_array( $infinity23_data_array['smtp_ses_encryption'], array( 'ssl', 'tls' ), true ) ) {
				$infinity23_data_array['smtp_ses_encryption'] = 'ssl';
			} // if( !in_array(
			$infinity23_data_array['smtp_ses_username'] = infinity23_email_smtp_strip_invisible_chars( infinity23_post_sanitize('smtp_ses_username', 'text') );
			$infinity23_data_array['smtp_ses_password'] = infinity23_email_smtp_save_password_field( 'smtp_ses_password', infinity23_pro_get_data( 'smtp_ses_password', $infinity23_existing_data_array, $infinity23_defaults ), 'email_smtp_ses_password' );

			$infinity23_data_array['smtp_elastic_connection_method'] = infinity23_pro_field_sanitize( 'smtp_elastic_connection_method', $infinity23_existing_data_array, $infinity23_defaults );
			if( !in_array( $infinity23_data_array['smtp_elastic_connection_method'], array( 'smtp', 'api' ), true ) ) {
				$infinity23_data_array['smtp_elastic_connection_method'] = 'smtp';
			} // if( !in_array(
			$infinity23_data_array['smtp_elastic_username'] = infinity23_email_smtp_strip_invisible_chars( infinity23_post_sanitize('smtp_elastic_username', 'text') );
			$infinity23_data_array['smtp_elastic_api_key'] = infinity23_email_smtp_save_password_field( 'smtp_elastic_api_key', infinity23_pro_get_data( 'smtp_elastic_api_key', $infinity23_existing_data_array, $infinity23_defaults ), 'email_smtp_elastic_api_key' );

			$infinity23_data_array['smtp_sendgrid_api_key'] = infinity23_email_smtp_save_password_field( 'smtp_sendgrid_api_key', infinity23_pro_get_data( 'smtp_sendgrid_api_key', $infinity23_existing_data_array, $infinity23_defaults ), 'email_smtp_sendgrid_api_key' );

			$infinity23_data_array['smtp_mailgun_host'] = infinity23_pro_field_sanitize( 'smtp_mailgun_host', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['smtp_mailgun_username'] = infinity23_email_smtp_strip_invisible_chars( infinity23_post_sanitize('smtp_mailgun_username', 'text') );
			$infinity23_data_array['smtp_mailgun_password'] = infinity23_email_smtp_save_password_field( 'smtp_mailgun_password', infinity23_pro_get_data( 'smtp_mailgun_password', $infinity23_existing_data_array, $infinity23_defaults ), 'email_smtp_mailgun_password' );

			$infinity23_data_array['smtp_maileroo_username'] = infinity23_email_smtp_strip_invisible_chars( infinity23_post_sanitize('smtp_maileroo_username', 'text') );
			$infinity23_data_array['smtp_maileroo_password'] = infinity23_email_smtp_save_password_field( 'smtp_maileroo_password', infinity23_pro_get_data( 'smtp_maileroo_password', $infinity23_existing_data_array, $infinity23_defaults ), 'email_smtp_maileroo_password' );

			$infinity23_data_array['from_email'] = infinity23_pro_field_sanitize( 'from_email', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['from_name'] = infinity23_pro_field_sanitize( 'from_name', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['force_from_checkbox'] = infinity23_pro_field_sanitize( 'force_from_checkbox', $infinity23_existing_data_array, $infinity23_defaults );
			if( !in_array( $infinity23_data_array['force_from_checkbox'], array( 'none', 'email', 'name', 'both' ), true ) ) {
				$infinity23_data_array['force_from_checkbox'] = 'none';
			} // if( !in_array(

			$infinity23_data_array['email_log_enabled'] = infinity23_pro_field_sanitize( 'email_log_enabled', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['email_log_save_message'] = infinity23_pro_field_sanitize( 'email_log_save_message', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['email_log_retention_days'] = infinity23_pro_field_sanitize( 'email_log_retention_days', $infinity23_existing_data_array, $infinity23_defaults );
			if( $infinity23_data_array['email_log_retention_days'] <= 0 ) {
				$infinity23_data_array['email_log_retention_days'] = $infinity23_defaults['email_log_retention_days']['value'];
			} // if( $infinity23_data_array['email_log_retention_days'] <= 0 ) {
			$infinity23_data_array['email_log_count_limit'] = infinity23_pro_field_sanitize( 'email_log_count_limit', $infinity23_existing_data_array, $infinity23_defaults );
			if( $infinity23_data_array['email_log_count_limit'] <= 0 ) {
				$infinity23_data_array['email_log_count_limit'] = $infinity23_defaults['email_log_count_limit']['value'];
			} // if( $infinity23_data_array['email_log_count_limit'] <= 0 ) {
			$infinity23_data_array['email_log_per_page'] = infinity23_pro_field_sanitize( 'email_log_per_page', $infinity23_existing_data_array, $infinity23_defaults );
			if( $infinity23_data_array['email_log_per_page'] <= 0 ) {
				$infinity23_data_array['email_log_per_page'] = $infinity23_defaults['email_log_per_page']['value'];
			} // if( $infinity23_data_array['email_log_per_page'] <= 0 ) {

			$infinity23_data_array['email_smtp_hide_menu'] = infinity23_pro_field_sanitize( 'email_smtp_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			if( $infinity23_data_array['email_log_enabled'] == 1 ) {
				include_once( dirname(__FILE__) . "/back-end/email-log-db-functions.php" );
				if( !infinity23_email_log_table_exists() ) {
					infinity23_email_log_install_table();
				} // if( !infinity23_email_log_table_exists() ) {
			} // if( $infinity23_data_array['email_log_enabled'] == 1 ) {

			infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-email-smtp', 'saved' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce( $_POST['infinity23_email_smtp_nonce'], 'infinity23_email_smtp_action' ) ) {

	} // elseif( isset( $_POST['infinity23_email_smtp_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_smtp_provider = infinity23_pro_get_data( 'smtp_provider', $infinity23_data_array, $infinity23_defaults );

	$infinity23_smtp_custom_host = infinity23_pro_get_data( 'smtp_custom_host', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_custom_port = infinity23_pro_get_data( 'smtp_custom_port', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_custom_encryption = infinity23_pro_get_data( 'smtp_custom_encryption', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_custom_auth_type = infinity23_pro_get_data( 'smtp_custom_auth_type', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_custom_username = infinity23_pro_get_data( 'smtp_custom_username', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_custom_password_mask = infinity23_email_smtp_get_password_mask( infinity23_pro_get_data( 'smtp_custom_password', $infinity23_data_array, $infinity23_defaults ), 'email_smtp_custom_password' );

	$infinity23_smtp_gmail_username = infinity23_pro_get_data( 'smtp_gmail_username', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_gmail_password_mask = infinity23_email_smtp_get_password_mask( infinity23_pro_get_data( 'smtp_gmail_password', $infinity23_data_array, $infinity23_defaults ), 'email_smtp_gmail_password' );

	$infinity23_smtp_ses_host = infinity23_pro_get_data( 'smtp_ses_host', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_ses_port = infinity23_pro_get_data( 'smtp_ses_port', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_ses_encryption = infinity23_pro_get_data( 'smtp_ses_encryption', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_ses_username = infinity23_pro_get_data( 'smtp_ses_username', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_ses_password_mask = infinity23_email_smtp_get_password_mask( infinity23_pro_get_data( 'smtp_ses_password', $infinity23_data_array, $infinity23_defaults ), 'email_smtp_ses_password' );

	$infinity23_smtp_elastic_connection_method = infinity23_pro_get_data( 'smtp_elastic_connection_method', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_elastic_username = infinity23_pro_get_data( 'smtp_elastic_username', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_elastic_api_key_mask = infinity23_email_smtp_get_password_mask( infinity23_pro_get_data( 'smtp_elastic_api_key', $infinity23_data_array, $infinity23_defaults ), 'email_smtp_elastic_api_key' );
	$infinity23_elastic_username_hidden_class = ( $infinity23_smtp_elastic_connection_method === 'api' ) ? 'infinity23-smtp-note-hidden' : '';

	$infinity23_smtp_sendgrid_api_key_mask = infinity23_email_smtp_get_password_mask( infinity23_pro_get_data( 'smtp_sendgrid_api_key', $infinity23_data_array, $infinity23_defaults ), 'email_smtp_sendgrid_api_key' );

	$infinity23_smtp_mailgun_host = infinity23_pro_get_data( 'smtp_mailgun_host', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_mailgun_username = infinity23_pro_get_data( 'smtp_mailgun_username', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_mailgun_password_mask = infinity23_email_smtp_get_password_mask( infinity23_pro_get_data( 'smtp_mailgun_password', $infinity23_data_array, $infinity23_defaults ), 'email_smtp_mailgun_password' );

	$infinity23_smtp_maileroo_username = infinity23_pro_get_data( 'smtp_maileroo_username', $infinity23_data_array, $infinity23_defaults );
	$infinity23_smtp_maileroo_password_mask = infinity23_email_smtp_get_password_mask( infinity23_pro_get_data( 'smtp_maileroo_password', $infinity23_data_array, $infinity23_defaults ), 'email_smtp_maileroo_password' );

	$infinity23_force_from_checkbox = infinity23_pro_get_data( 'force_from_checkbox', $infinity23_data_array, $infinity23_defaults );
	if( !in_array( $infinity23_force_from_checkbox, array( 'none', 'email', 'name', 'both' ), true ) ) {
		$infinity23_force_from_checkbox = ( intval( $infinity23_force_from_checkbox ) == 1 ) ? 'both' : 'none';
	} // if( !in_array(
	$infinity23_from_email = infinity23_pro_get_data( 'from_email', $infinity23_data_array, $infinity23_defaults );
	$infinity23_from_name = infinity23_pro_get_data( 'from_name', $infinity23_data_array, $infinity23_defaults );
	$infinity23_email_log_enabled = intval( infinity23_pro_get_data( 'email_log_enabled', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_email_log_save_message = intval( infinity23_pro_get_data( 'email_log_save_message', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_email_log_retention_days = intval( infinity23_pro_get_data( 'email_log_retention_days', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_email_log_count_limit = intval( infinity23_pro_get_data( 'email_log_count_limit', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_email_log_per_page = intval( infinity23_pro_get_data( 'email_log_per_page', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_email_smtp_hide_menu = intval( infinity23_pro_get_data( 'email_smtp_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

	// --------------------------------------------
	// active tab - url based instead of javascript, so each tab has its own link
	// --------------------------------------------

	$infinity23_email_smtp_active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'settings';

	if( !in_array( $infinity23_email_smtp_active_tab, array( 'settings', 'test', 'logs' ), true ) ) {
		$infinity23_email_smtp_active_tab = 'settings';
	} // if( !in_array( $infinity23_email_smtp_active_tab, array( 'settings', 'test', 'logs' ), true ) ) {

	$infinity23_email_smtp_tab_settings_url = esc_url( add_query_arg( 'tab', 'settings' ) );
	$infinity23_email_smtp_tab_test_url = esc_url( add_query_arg( 'tab', 'test' ) );
	$infinity23_email_smtp_tab_logs_url = esc_url( add_query_arg( 'tab', 'logs' ) );

	// --------------------------------------------
	// SMTP provider presets - host/port/encryption/username autofill plus a
	// short note on what each provider actually expects in Username/Password.
	// "Default WordPress" is a no-configuration option - it keeps the module
	// active (so From Name/Email locking still works) but skips SMTP entirely
	// and lets WordPress send mail the normal way.
	// --------------------------------------------

	$infinity23_smtp_providers = array(
		'wordpress_default' => array( 'label' => 'Default WordPress (No SMTP)', 'note' => 'No server settings needed - mail sends the normal WordPress way. Choose this if you just want to lock the From Name/Email below.' ),
		'gmail' => array( 'label' => 'Google (Gmail / Workspace)', 'note' => '' ),
		'amazon_ses' => array( 'label' => 'Amazon SES', 'note' => 'Use your SES SMTP credentials, not your AWS keys. Find the Host in SES > SMTP Settings. If port 465 fails, try 587.' ),
		'elastic_email' => array( 'label' => 'Elastic Email', 'note' => '' ),
		'sendgrid' => array( 'label' => 'SendGrid', 'note' => '' ),
		'mailgun' => array( 'label' => 'Mailgun', 'note' => 'Uses a different SMTP host per account region (US vs EU) - check your Mailgun account before entering the Host below.' ),
		'maileroo' => array( 'label' => 'Maileroo', 'note' => 'Username and Password are the SMTP credentials shown on your Maileroo domain page, not your account login.' ),
		'custom' => array( 'label' => 'Custom SMTP Server', 'note' => '' ),
	);

	$infinity23_smtp_provider_note = isset( $infinity23_smtp_providers[ $infinity23_smtp_provider ]['note'] ) ? $infinity23_smtp_providers[ $infinity23_smtp_provider ]['note'] : '';
	$infinity23_smtp_server_fields_hidden_class = ( $infinity23_smtp_provider === 'wordpress_default' ) ? 'infinity23-smtp-note-hidden' : '';

	// --------------------------------------------
	// example domain for the From Email tip below - uses the site's real
	// host (subdomains included, since that IS the domain being sent from),
	// falling back to a placeholder on localhost/IP dev environments
	// --------------------------------------------

	$infinity23_site_host = wp_parse_url( home_url(), PHP_URL_HOST );

	if( empty( $infinity23_site_host ) || strtolower( $infinity23_site_host ) === 'localhost' || filter_var( $infinity23_site_host, FILTER_VALIDATE_IP ) ) {
		$infinity23_email_smtp_domain_example = 'your-domain.com';
	} else {
		$infinity23_email_smtp_domain_example = $infinity23_site_host;
	} // if( empty( $infinity23_site_host ) ||

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp email-smtp-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Email SMTP Settings</h1>
		<p>Below you can connect an SMTP server so WordPress emails send through it instead of the server's default mail function. If you don't need SMTP, keep the provider set to Default WordPress and just fill in the From Email/Name below.</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_email_smtp_activate_action', 'infinity23_email_smtp_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Email SMTP</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<div class="infinity23-module-tabs-wrap">

		<div class="infinity23-module-tabs">
			<a href="<?php echo esc_url( $infinity23_email_smtp_tab_settings_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_email_smtp_active_tab === 'settings' ) { echo 'infinity23-module-tab-active'; } ?>">SMTP Settings</a>
			<a href="<?php echo esc_url( $infinity23_email_smtp_tab_test_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_email_smtp_active_tab === 'test' ) { echo 'infinity23-module-tab-active'; } ?>">Send Test Email</a>
			<?php if( $infinity23_email_log_enabled == 1 ) { ?>
				<a href="<?php echo esc_url( $infinity23_email_smtp_tab_logs_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_email_smtp_active_tab === 'logs' ) { echo 'infinity23-module-tab-active'; } ?>">Email Log</a>
			<?php } // if( $infinity23_email_log_enabled == 1 ) { ?>
		</div><!--infinity23-module-tabs-->

		<div class="infinity23-module-tab-content <?php if( $infinity23_email_smtp_active_tab !== 'settings' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="email-smtp-tab-settings">

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

			<div class="section-card" data-card-default="open">

				<h2>SMTP Server Settings:</h2>

				<div class="form-line">
					<label for="smtp_provider">SMTP Provider: </label>
					<select name="smtp_provider" id="smtp_provider">
						<?php foreach( $infinity23_smtp_providers as $infinity23_smtp_provider_key => $infinity23_smtp_provider_data ) { ?>
							<option value="<?php echo esc_attr( $infinity23_smtp_provider_key ); ?>" data-note="<?php echo esc_attr( $infinity23_smtp_provider_data['note'] ); ?>" <?php if( $infinity23_smtp_provider == $infinity23_smtp_provider_key ) { echo 'selected'; } ?>><?php echo esc_html( $infinity23_smtp_provider_data['label'] ); ?></option>
						<?php } // foreach( $infinity23_smtp_providers as $infinity23_smtp_provider_key => $infinity23_smtp_provider_data ) { ?>
					</select>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Choosing a provider fills in the Host, Port, Encryption and (when needed) Username fields below with that provider's usual values.
							<br /><br />
							You can still edit those fields afterwards - this is only a starting point, not a locked preset.
							<br /><br />
							Pick <strong>Default WordPress</strong> if you don't want to configure SMTP at all.
						</div>
						?
					</div><!--backtest-field-help-icon-->

					<p id="smtp_provider_note" class="default-img-url <?php if( empty( $infinity23_smtp_provider_note ) ) { echo 'infinity23-smtp-note-hidden'; } ?>"><?php echo esc_html( $infinity23_smtp_provider_note ); ?></p>
				</div><!--form-line-->

				<div id="infinity23-smtp-server-fields-wrap" class="<?php echo esc_attr( $infinity23_smtp_server_fields_hidden_class ); ?>">

				<div class="infinity23-smtp-provider-block <?php echo ( $infinity23_smtp_provider === 'custom' ) ? '' : 'infinity23-smtp-note-hidden'; ?>" data-provider="custom">

					<div class="form-line">
						<label for="smtp_custom_host">SMTP Host: </label>
						<input type="text" id="smtp_custom_host" class="long-input" name="smtp_custom_host" value="<?php echo esc_attr( $infinity23_smtp_custom_host ); ?>" placeholder="smtp.example.com" />
					</div><!--form-line-->

					<div class="form-line">
						<label for="smtp_custom_port">Port: </label>
						<input type="text" id="smtp_custom_port" class="short-input" name="smtp_custom_port" value="<?php echo esc_attr( $infinity23_smtp_custom_port ); ?>" />
					</div><!--form-line-->

					<div class="form-line">
						<label for="smtp_custom_encryption">Encryption: </label>
						<select name="smtp_custom_encryption" id="smtp_custom_encryption">
							<option value="none" <?php if( $infinity23_smtp_custom_encryption == 'none' ) { echo 'selected'; } ?>>None</option>
							<option value="ssl" <?php if( $infinity23_smtp_custom_encryption == 'ssl' ) { echo 'selected'; } ?>>SSL (usually port 465)</option>
							<option value="tls" <?php if( $infinity23_smtp_custom_encryption == 'tls' ) { echo 'selected'; } ?>>TLS (usually port 587)</option>
						</select>
					</div><!--form-line-->

					<h3>Authentication:</h3>

					<div class="form-line">
						<label for="smtp_custom_auth_type">Authentication Method: </label>
						<select name="smtp_custom_auth_type" id="smtp_custom_auth_type">
							<option value="none" <?php if( $infinity23_smtp_custom_auth_type === 'none' ) { echo 'selected'; } ?>>None</option>
							<option value="plain" <?php if( $infinity23_smtp_custom_auth_type === 'plain' ) { echo 'selected'; } ?>>Plain</option>
							<option value="login" <?php if( $infinity23_smtp_custom_auth_type === 'login' ) { echo 'selected'; } ?>>Login</option>
						</select>
					</div><!--form-line-->

					<div id="infinity23-smtp-custom-auth-fields-wrap" class="<?php if( $infinity23_smtp_custom_auth_type === 'none' ) { echo 'infinity23-smtp-note-hidden'; } ?>">

						<div class="form-line">
							<label for="smtp_custom_username">Username: </label>
							<input type="text" id="smtp_custom_username" class="long-input" name="smtp_custom_username" value="<?php echo esc_attr( $infinity23_smtp_custom_username ); ?>" autocomplete="off" />
						</div><!--form-line-->

						<div class="form-line">
							<label for="smtp_custom_password">Password: </label>
							<input type="password" id="smtp_custom_password" class="long-input infinity23-masked-password-field" name="smtp_custom_password" value="<?php echo $infinity23_smtp_custom_password_mask['is_saved'] ? esc_attr( str_repeat( '•', $infinity23_smtp_custom_password_mask['mask_length'] ) ) : ''; ?>" autocomplete="new-password" data-mask-active="<?php echo $infinity23_smtp_custom_password_mask['is_saved'] ? '1' : '0'; ?>" />
							<input type="hidden" id="smtp_custom_password_touched" name="smtp_custom_password_touched" value="0" />
						</div><!--form-line-->

						<?php if( $infinity23_smtp_custom_password_mask['is_undecryptable'] ) { ?>
							<p class="default-img-url">A password is saved but could not be read back - this usually happens after the site's security keys change. Re-enter and save the password to fix it.</p>
						<?php } // if( $infinity23_smtp_custom_password_mask['is_undecryptable'] ) { ?>

					</div><!--infinity23-smtp-custom-auth-fields-wrap-->

				</div><!--infinity23-smtp-provider-block-->

				<div class="infinity23-smtp-provider-block <?php echo ( $infinity23_smtp_provider === 'gmail' ) ? '' : 'infinity23-smtp-note-hidden'; ?>" data-provider="gmail">

					<div class="form-line">
						<label for="smtp_gmail_username">Gmail / Workspace Address: </label>
						<input type="text" id="smtp_gmail_username" class="long-input" name="smtp_gmail_username" value="<?php echo esc_attr( $infinity23_smtp_gmail_username ); ?>" autocomplete="off" />
					</div><!--form-line-->

					<div class="form-line">
						<label for="smtp_gmail_password">App Password: </label>
						<input type="password" id="smtp_gmail_password" class="long-input infinity23-masked-password-field" name="smtp_gmail_password" value="<?php echo $infinity23_smtp_gmail_password_mask['is_saved'] ? esc_attr( str_repeat( '•', $infinity23_smtp_gmail_password_mask['mask_length'] ) ) : ''; ?>" autocomplete="new-password" data-mask-active="<?php echo $infinity23_smtp_gmail_password_mask['is_saved'] ? '1' : '0'; ?>" />
						<input type="hidden" id="smtp_gmail_password_touched" name="smtp_gmail_password_touched" value="0" />

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								This is an App Password created in your Google account, not your normal login password.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

					<?php if( $infinity23_smtp_gmail_password_mask['is_undecryptable'] ) { ?>
						<p class="default-img-url">A password is saved but could not be read back - this usually happens after the site's security keys change. Re-enter and save the password to fix it.</p>
					<?php } // if( $infinity23_smtp_gmail_password_mask['is_undecryptable'] ) { ?>

				</div><!--infinity23-smtp-provider-block-->

				<div class="infinity23-smtp-provider-block <?php echo ( $infinity23_smtp_provider === 'amazon_ses' ) ? '' : 'infinity23-smtp-note-hidden'; ?>" data-provider="amazon_ses">

					<div class="form-line">
						<label for="smtp_ses_host">SMTP Host: </label>
						<?php // phpcs:ignore PluginCheck.CodeAnalysis.Offloading.OffloadedContent -- not a remote asset load; "email-smtp.us-east-1.amazonaws.com" is only the placeholder text of this text input, showing the admin an example Amazon SES hostname to type ?>
						<input type="text" id="smtp_ses_host" class="long-input" name="smtp_ses_host" value="<?php echo esc_attr( $infinity23_smtp_ses_host ); ?>" placeholder="email-smtp.us-east-1.amazonaws.com" />
					</div><!--form-line-->

					<div class="form-line">
						<label for="smtp_ses_port">Port: </label>
						<select name="smtp_ses_port" id="smtp_ses_port">
							<option value="465" <?php if( $infinity23_smtp_ses_port == 465 ) { echo 'selected'; } ?>>465 (SSL)</option>
							<option value="587" <?php if( $infinity23_smtp_ses_port == 587 ) { echo 'selected'; } ?>>587 (TLS)</option>
						</select>
					</div><!--form-line-->

					<div class="form-line">
						<label for="smtp_ses_encryption">Encryption: </label>
						<select name="smtp_ses_encryption" id="smtp_ses_encryption">
							<option value="ssl" <?php if( $infinity23_smtp_ses_encryption == 'ssl' ) { echo 'selected'; } ?>>SSL</option>
							<option value="tls" <?php if( $infinity23_smtp_ses_encryption == 'tls' ) { echo 'selected'; } ?>>TLS</option>
						</select>
					</div><!--form-line-->

					<div class="form-line">
						<label for="smtp_ses_username">SMTP Username: </label>
						<input type="text" id="smtp_ses_username" class="long-input" name="smtp_ses_username" value="<?php echo esc_attr( $infinity23_smtp_ses_username ); ?>" autocomplete="off" />
					</div><!--form-line-->

					<div class="form-line">
						<label for="smtp_ses_password">SMTP Password: </label>
						<input type="password" id="smtp_ses_password" class="long-input infinity23-masked-password-field" name="smtp_ses_password" value="<?php echo $infinity23_smtp_ses_password_mask['is_saved'] ? esc_attr( str_repeat( '•', $infinity23_smtp_ses_password_mask['mask_length'] ) ) : ''; ?>" autocomplete="new-password" data-mask-active="<?php echo $infinity23_smtp_ses_password_mask['is_saved'] ? '1' : '0'; ?>" />
						<input type="hidden" id="smtp_ses_password_touched" name="smtp_ses_password_touched" value="0" />

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								These are the dedicated SMTP credentials generated in the SES console, not your AWS access key/secret.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

					<?php if( $infinity23_smtp_ses_password_mask['is_undecryptable'] ) { ?>
						<p class="default-img-url">A password is saved but could not be read back - this usually happens after the site's security keys change. Re-enter and save the password to fix it.</p>
					<?php } // if( $infinity23_smtp_ses_password_mask['is_undecryptable'] ) { ?>

				</div><!--infinity23-smtp-provider-block-->

				<div class="infinity23-smtp-provider-block <?php echo ( $infinity23_smtp_provider === 'elastic_email' ) ? '' : 'infinity23-smtp-note-hidden'; ?>" data-provider="elastic_email">

					<h3>Connection Method:</h3>

					<div class="form-line">
						<label for="smtp_elastic_connection_method">Method: </label>
						<select name="smtp_elastic_connection_method" id="smtp_elastic_connection_method" class="infinity23-elastic-method-select">
							<option value="api" <?php if( $infinity23_smtp_elastic_connection_method === 'api' ) { echo 'selected'; } ?>>HTTP API (Recommended)</option>
							<option value="smtp" <?php if( $infinity23_smtp_elastic_connection_method === 'smtp' ) { echo 'selected'; } ?>>SMTP (Username &amp; Password)</option>
						</select>

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								HTTP API sends mail over a normal web request instead of an SMTP connection, using only the API Key below - this is recommended and works on most hosts.
								<br /><br />
								Only switch to SMTP if your setup specifically requires an SMTP connection.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

					<h3>Credentials:</h3>

					<div id="infinity23-elastic-username-wrap" class="<?php echo esc_attr( $infinity23_elastic_username_hidden_class ); ?>">
					<div class="form-line">
						<label for="smtp_elastic_username">SMTP Username: </label>
						<input type="text" id="smtp_elastic_username" class="long-input" name="smtp_elastic_username" value="<?php echo esc_attr( $infinity23_smtp_elastic_username ); ?>" autocomplete="off" placeholder="e.g. system@yourdomain.com" />

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								Elastic Email generates a dedicated SMTP Username and Password together as one credential pair - this is not your account login email.
								<br /><br />
								Find it under your Elastic Email account's SMTP credentials/settings page, where it was shown once when the credential was created.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->
					</div><!--infinity23-elastic-username-wrap-->

					<div class="form-line">
						<label for="smtp_elastic_api_key" id="infinity23-elastic-password-label"><?php echo ( $infinity23_smtp_elastic_connection_method === 'api' ) ? 'API Key: ' : 'SMTP Password: '; ?></label>
						<input type="password" id="smtp_elastic_api_key" class="long-input infinity23-masked-password-field" name="smtp_elastic_api_key" value="<?php echo $infinity23_smtp_elastic_api_key_mask['is_saved'] ? esc_attr( str_repeat( '•', $infinity23_smtp_elastic_api_key_mask['mask_length'] ) ) : ''; ?>" autocomplete="new-password" data-mask-active="<?php echo $infinity23_smtp_elastic_api_key_mask['is_saved'] ? '1' : '0'; ?>" />
						<input type="hidden" id="smtp_elastic_api_key_touched" name="smtp_elastic_api_key_touched" value="0" />

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								For HTTP API: this is your Elastic Email API Key, found on your account's API settings page.
								<br /><br />
								For SMTP: this is the Password shown together with the Username above when the SMTP credential was created - it is only shown once, so if it was lost, generate a new credential pair in Elastic Email and enter both values here.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

					<?php if( $infinity23_smtp_elastic_api_key_mask['is_undecryptable'] ) { ?>
						<p class="default-img-url">A password is saved but could not be read back - this usually happens after the site's security keys change. Re-enter and save it to fix it.</p>
					<?php } // if( $infinity23_smtp_elastic_api_key_mask['is_undecryptable'] ) { ?>

				</div><!--infinity23-smtp-provider-block-->

				<div class="infinity23-smtp-provider-block <?php echo ( $infinity23_smtp_provider === 'sendgrid' ) ? '' : 'infinity23-smtp-note-hidden'; ?>" data-provider="sendgrid">

					<div class="form-line">
						<label for="smtp_sendgrid_api_key">API Key: </label>
						<input type="password" id="smtp_sendgrid_api_key" class="long-input infinity23-masked-password-field" name="smtp_sendgrid_api_key" value="<?php echo $infinity23_smtp_sendgrid_api_key_mask['is_saved'] ? esc_attr( str_repeat( '•', $infinity23_smtp_sendgrid_api_key_mask['mask_length'] ) ) : ''; ?>" autocomplete="new-password" data-mask-active="<?php echo $infinity23_smtp_sendgrid_api_key_mask['is_saved'] ? '1' : '0'; ?>" />
						<input type="hidden" id="smtp_sendgrid_api_key_touched" name="smtp_sendgrid_api_key_touched" value="0" />

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								The Username is always the literal word apikey and is set automatically - only the API Key is needed here.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

					<?php if( $infinity23_smtp_sendgrid_api_key_mask['is_undecryptable'] ) { ?>
						<p class="default-img-url">An API Key is saved but could not be read back - this usually happens after the site's security keys change. Re-enter and save it to fix it.</p>
					<?php } // if( $infinity23_smtp_sendgrid_api_key_mask['is_undecryptable'] ) { ?>

				</div><!--infinity23-smtp-provider-block-->

				<div class="infinity23-smtp-provider-block <?php echo ( $infinity23_smtp_provider === 'mailgun' ) ? '' : 'infinity23-smtp-note-hidden'; ?>" data-provider="mailgun">

					<p class="infinity23-notice-text">Mailgun uses a different SMTP host depending on your account's region - check your Mailgun domain's Sending settings to confirm which one applies to you.</p>

					<div class="form-line">
						<label for="smtp_mailgun_host">SMTP Host: </label>
						<input type="text" id="smtp_mailgun_host" class="long-input" name="smtp_mailgun_host" value="<?php echo esc_attr( $infinity23_smtp_mailgun_host ); ?>" placeholder="smtp.mailgun.org" />

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								US region accounts use <span class="code">smtp.mailgun.org</span>.
								<br /><br />
								EU region accounts must use <span class="code">smtp.eu.mailgun.org</span> instead - using the wrong one will fail to authenticate no matter how correct your username/password are.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

					<div class="form-line">
						<label for="smtp_mailgun_username">SMTP Login: </label>
						<input type="text" id="smtp_mailgun_username" class="long-input" name="smtp_mailgun_username" value="<?php echo esc_attr( $infinity23_smtp_mailgun_username ); ?>" placeholder="postmaster@yourdomain.com" autocomplete="off" />
					</div><!--form-line-->

					<div class="form-line">
						<label for="smtp_mailgun_password">SMTP Password: </label>
						<input type="password" id="smtp_mailgun_password" class="long-input infinity23-masked-password-field" name="smtp_mailgun_password" value="<?php echo $infinity23_smtp_mailgun_password_mask['is_saved'] ? esc_attr( str_repeat( '•', $infinity23_smtp_mailgun_password_mask['mask_length'] ) ) : ''; ?>" autocomplete="new-password" data-mask-active="<?php echo $infinity23_smtp_mailgun_password_mask['is_saved'] ? '1' : '0'; ?>" />
						<input type="hidden" id="smtp_mailgun_password_touched" name="smtp_mailgun_password_touched" value="0" />
					</div><!--form-line-->

					<?php if( $infinity23_smtp_mailgun_password_mask['is_undecryptable'] ) { ?>
						<p class="default-img-url">A password is saved but could not be read back - this usually happens after the site's security keys change. Re-enter and save the password to fix it.</p>
					<?php } // if( $infinity23_smtp_mailgun_password_mask['is_undecryptable'] ) { ?>

				</div><!--infinity23-smtp-provider-block-->

				<div class="infinity23-smtp-provider-block <?php echo ( $infinity23_smtp_provider === 'maileroo' ) ? '' : 'infinity23-smtp-note-hidden'; ?>" data-provider="maileroo">

					<div class="form-line">
						<label for="smtp_maileroo_username">SMTP Username: </label>
						<input type="text" id="smtp_maileroo_username" class="long-input" name="smtp_maileroo_username" value="<?php echo esc_attr( $infinity23_smtp_maileroo_username ); ?>" autocomplete="off" />
					</div><!--form-line-->

					<div class="form-line">
						<label for="smtp_maileroo_password">SMTP Password: </label>
						<input type="password" id="smtp_maileroo_password" class="long-input infinity23-masked-password-field" name="smtp_maileroo_password" value="<?php echo $infinity23_smtp_maileroo_password_mask['is_saved'] ? esc_attr( str_repeat( '•', $infinity23_smtp_maileroo_password_mask['mask_length'] ) ) : ''; ?>" autocomplete="new-password" data-mask-active="<?php echo $infinity23_smtp_maileroo_password_mask['is_saved'] ? '1' : '0'; ?>" />
						<input type="hidden" id="smtp_maileroo_password_touched" name="smtp_maileroo_password_touched" value="0" />
					</div><!--form-line-->

					<?php if( $infinity23_smtp_maileroo_password_mask['is_undecryptable'] ) { ?>
						<p class="default-img-url">A password is saved but could not be read back - this usually happens after the site's security keys change. Re-enter and save the password to fix it.</p>
					<?php } // if( $infinity23_smtp_maileroo_password_mask['is_undecryptable'] ) { ?>

				</div><!--infinity23-smtp-provider-block-->

				<div class="form-line">
					<button type="button" id="infinity23-email-smtp-test-connection-btn" class="infinity23-email-smtp-test-connection-btn" data-nonce="<?php echo esc_attr( wp_create_nonce( 'infinity23_email_smtp_test_connection_action' ) ); ?>">Test Connection</button>
					<div id="infinity23-email-smtp-connection-result" class="infinity23-connection-result infinity23-smtp-note-hidden"></div>
				</div><!--form-line-->

				</div><!--infinity23-smtp-server-fields-wrap-->

			</div><!--section-card-->

			<div class="section-card" data-card-default="open">

				<h2>Email Sender Settings:</h2>

				<p class="infinity23-notice-text">TIP: use an address approved by your SMTP provider, like system@<?php echo esc_html( $infinity23_email_smtp_domain_example ); ?>, and force the sender settings to avoid emails not getting sent.</p>

				<div class="form-line">
					<label for="from_email">Sender Email Address: </label>
					<input type="text" id="from_email" class="long-input" name="from_email" value="<?php echo esc_attr( $infinity23_from_email ); ?>" placeholder="leave blank to use <?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							The email address recipients will see as the sender.
							<br /><br />
							Left blank, it falls back to your Admin Email (<span class="code"><?php echo esc_html( get_option( 'admin_email' ) ); ?></span>).
							<br /><br />
							<strong>Note:</strong> most SMTP providers (Gmail, SES, SendGrid, Mailgun, etc.) reject or replace this if it doesn't match the domain you authenticated with.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line">
					<label for="from_name">Sender Display Name: </label>
					<input type="text" id="from_name" class="long-input" name="from_name" value="<?php echo esc_attr( $infinity23_from_name ); ?>" placeholder="leave blank to use <?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							The name shown next to the sender email, for example "<span class="code"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span> &lt;<?php echo esc_html( get_option( 'admin_email' ) ); ?>&gt;".
							<br /><br />
							Left blank, it falls back to your Site Title (<span class="code"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>).
							<br /><br />
							Unlike the Sender Email, most providers do not restrict this - it can be set to anything, such as your business or support team name.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line infinity23-force-from-highlight">
					<label for="force_from_checkbox">Force Sender Settings (Blocks Plugins &amp; Themes From Changing It):</label>
					<select id="force_from_checkbox" name="force_from_checkbox">
						<option value="none" <?php if( $infinity23_force_from_checkbox === 'none' ) { echo 'selected'; } ?>>Do Not Force</option>
						<option value="email" <?php if( $infinity23_force_from_checkbox === 'email' ) { echo 'selected'; } ?>>Force Sender Email Only</option>
						<option value="name" <?php if( $infinity23_force_from_checkbox === 'name' ) { echo 'selected'; } ?>>Force Sender Name Only</option>
						<option value="both" <?php if( $infinity23_force_from_checkbox === 'both' ) { echo 'selected'; } ?>>Force Sender Email &amp; Sender Name</option>
					</select>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Some plugins (form builders, WooCommerce, membership plugins) set their own sender name or email on outgoing mail.
							<br /><br />
							Forcing the Email, the Name, or both makes the matching field above always win, no matter what another plugin tries to set.
							<br /><br />
							Choose Do Not Force if you want other plugins to still be able to set their own sender when they need to.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

			</div><!--section-card-->

			<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="open">

				<h2>Save Emails Log: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

				<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

				<p class="infinity23-notice-text">Warning: when you save email logs, some of the emails might contain sensitive data - make sure not to give access to the log view to other people.</p>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="email_log_enabled" name="email_log_enabled" value="1" <?php if( $infinity23_email_log_enabled == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
					<label for="email_log_enabled">Keep a log of every email that sent in this website (success and failed)</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							When enabled, every email sent through wp_mail() is recorded - the recipient, subject, status and, if it failed, the error message.
							<br /><br />
							A separate database table is created the first time this is saved as enabled.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="infinity23-toggle-fields" data-toggle-input="email_log_enabled">

					<div class="form-line checkbox-line">
						<input type="checkbox" id="email_log_save_message" name="email_log_save_message" value="1" <?php if( $infinity23_email_log_save_message == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
						<label for="email_log_save_message">Save text & html message in the log database (this will make the database size a lot bigger)</label>
					</div><!--form-line-->

					<div class="form-line">
						<label for="email_log_retention_days">Delete Logs Older Than (Days): </label>
						<input type="text" id="email_log_retention_days" class="short-input" name="email_log_retention_days" value="<?php echo esc_attr( $infinity23_email_log_retention_days ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								Log rows older than this many days are removed automatically once a day.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

					<div class="form-line">
						<label for="email_log_count_limit">Maximum Log Rows To Keep: </label>
						<input type="text" id="email_log_count_limit" class="medium-input" name="email_log_count_limit" value="<?php echo esc_attr( $infinity23_email_log_count_limit ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								If the log grows past this number of rows, the oldest rows are removed first so the table never grows unbounded.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

					<h3>Logs Pagination:</h3>

					<div class="form-line">
						<label for="email_log_per_page">Default Log Entries Per Page: </label>
						<select id="email_log_per_page" name="email_log_per_page" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?>>
							<?php foreach( array( 10, 25, 50, 100, 200 ) as $infinity23_email_log_per_page_default_option ) { ?>
								<option value="<?php echo esc_attr( $infinity23_email_log_per_page_default_option ); ?>" <?php if( $infinity23_email_log_per_page == $infinity23_email_log_per_page_default_option ) { echo 'selected'; } ?>><?php echo esc_html( $infinity23_email_log_per_page_default_option ); ?></option>
							<?php } // foreach( array( 10, 25, 50, 100, 200 ) as $infinity23_email_log_per_page_default_option ) { ?>
						</select>

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								How many log entries are shown per page on the Email Log tab, before you need to click Older to see more.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

				</div><!--infinity23-toggle-fields-->

			</div><!--section-card-->

			<?php infinity23_menu_visibility_field( 'email_smtp_hide_menu', $infinity23_email_smtp_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_email_smtp_action', 'infinity23_email_smtp_nonce' ); ?>

			<input type="submit" name="submit_email_smtp" class="submit-btn" value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_email_smtp_reset_action', 'infinity23_email_smtp_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

		</div><!--infinity23-module-tab-content-->

		<div class="infinity23-module-tab-content <?php if( $infinity23_email_smtp_active_tab !== 'test' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="email-smtp-tab-test">

	<?php

		// --------------------------------------------
		// build a dynamic default subject and message for the test email
		// form - a fixed subject/message on every send looks identical to
		// spam filters, so each page load gets a unique timestamp, a real
		// date/time, and a link back to the site
		// --------------------------------------------

		$infinity23_email_smtp_test_timestamp = infinity23_current_timestamp();
		$infinity23_email_smtp_test_subject_default = 'Email SMTP Test - ' . $infinity23_email_smtp_test_timestamp . ' - ' . get_bloginfo( 'name' );

		$infinity23_email_smtp_test_date = infinity23_format_timestamp( $infinity23_email_smtp_test_timestamp, get_option( 'date_format' ) );
		$infinity23_email_smtp_test_time = infinity23_format_timestamp( $infinity23_email_smtp_test_timestamp, get_option( 'time_format' ) );

		$infinity23_email_smtp_test_message_default = "This is a test email sent from the Email SMTP module in Infinity23 Plugin.\n\n";
		$infinity23_email_smtp_test_message_default .= "Website Name: " . get_bloginfo( 'name' ) . "\n";
		$infinity23_email_smtp_test_message_default .= "Date: " . $infinity23_email_smtp_test_date . "\n";
		$infinity23_email_smtp_test_message_default .= "Time: " . $infinity23_email_smtp_test_time . "\n\n";
		$infinity23_email_smtp_test_message_default .= "Sent From Website:\n";
		$infinity23_email_smtp_test_message_default .= home_url();

	?>

	<div class="section-card infinity23-email-smtp-test-card" data-card-default="open">

		<h2>Send A Test Email:</h2>

		<p>Fill in a recipient and, if you want, a custom subject and message, then send a test to confirm mail works.</p>

		<?php if( $infinity23_activate_code != 1 ) { ?>
			<p class="default-img-url">Email SMTP is not activated - a test email will still send using the site's default mail sending. Activate above to test your SMTP connection.</p>
		<?php } // if( $infinity23_activate_code != 1 ) { ?>

		<div id="infinity23-email-smtp-test-result" class="infinity23-message infinity23-smtp-note-hidden"></div>

		<div class="form-line">
			<label for="test_to_email">Send To: </label>
			<input type="text" id="test_to_email" class="long-input" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
		</div><!--form-line-->

		<div class="form-line">
			<label for="test_subject">Subject: </label>
			<input type="text" id="test_subject" class="very-long-input" value="<?php echo esc_attr( $infinity23_email_smtp_test_subject_default ); ?>" />
		</div><!--form-line-->

		<div class="form-line">
			<label for="test_message">Message: </label>
			<textarea id="test_message" class="infinity23-tagrow-textarea" style="height:220px;"><?php echo esc_textarea( $infinity23_email_smtp_test_message_default ); ?></textarea>
		</div><!--form-line-->

		<button type="button" class="infinity23-email-smtp-advance-btn">View Advance Settings</button>

		<div class="infinity23-email-smtp-advance-fields-wrap infinity23-email-smtp-advance-hidden">

			<h3>Test Force Sender Settings (Optional):</h3>

			<p class="default-img-url">Fill in a different name and/or email below to check whether Force Email Sender Settings above actually overrides them on this test send.</p>

			<div class="form-line">
				<label for="test_from_name">Send From Name: </label>
				<input type="text" id="test_from_name" class="medium-input" value="" placeholder="<?php echo esc_attr( !empty( $infinity23_from_name ) ? $infinity23_from_name : get_bloginfo( 'name' ) ); ?>" />
			</div><!--form-line-->

			<div class="form-line">
				<label for="test_from_email">Send From Email: </label>
				<input type="text" id="test_from_email" class="medium-input" value="" placeholder="<?php echo esc_attr( !empty( $infinity23_from_email ) ? $infinity23_from_email : get_option( 'admin_email' ) ); ?>" />
			</div><!--form-line-->

		</div><!--infinity23-email-smtp-advance-fields-wrap-->

		<button type="button" id="infinity23-email-smtp-test-send-btn" class="submit-btn submit-btn-small" data-nonce="<?php echo esc_attr( wp_create_nonce( 'infinity23_email_smtp_test_action' ) ); ?>">Send Test Email</button>

	</div><!--section-card-->

		</div><!--infinity23-module-tab-content-->

		<?php if( $infinity23_email_log_enabled == 1 ) { ?>

		<div class="infinity23-module-tab-content <?php if( $infinity23_email_smtp_active_tab !== 'logs' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="email-smtp-tab-logs">

			<?php

				$infinity23_email_log_page = isset( $_GET['log_page'] ) ? max( 1, intval( wp_unslash( $_GET['log_page'] ) ) ) : 1;
				$infinity23_email_log_status_filter = isset( $_GET['log_status'] ) ? sanitize_text_field( wp_unslash( $_GET['log_status'] ) ) : '';

				if( !in_array( $infinity23_email_log_status_filter, array( '', 'success', 'failed' ), true ) ) {
					$infinity23_email_log_status_filter = '';
				} // if( !in_array(

				$infinity23_email_log_per_page_choice = isset( $_GET['log_per_page'] ) ? intval( wp_unslash( $_GET['log_per_page'] ) ) : 0;
				$infinity23_email_log_per_page = $infinity23_email_log_per_page_choice > 0 ? $infinity23_email_log_per_page_choice : ( $infinity23_email_log_per_page > 0 ? $infinity23_email_log_per_page : 50 );

				$infinity23_email_log_table_exists = infinity23_email_log_table_exists();

				if( $infinity23_email_log_table_exists ) {

					$infinity23_email_log_total_rows = infinity23_email_log_get_total_count( $infinity23_email_log_status_filter );
					$infinity23_email_log_total_pages = max( 1, ceil( $infinity23_email_log_total_rows / $infinity23_email_log_per_page ) );
					$infinity23_email_log_offset = ( $infinity23_email_log_page - 1 ) * $infinity23_email_log_per_page;
					$infinity23_email_log_rows = infinity23_email_log_get_page( $infinity23_email_log_offset, $infinity23_email_log_per_page, $infinity23_email_log_status_filter );

				} else {

					$infinity23_email_log_total_rows = 0;
					$infinity23_email_log_total_pages = 1;
					$infinity23_email_log_rows = array();

				} // if( $infinity23_email_log_table_exists ) {

			?>

			<div class="section-card" data-card-default="open">

				<h2>Email Log:</h2>

				<?php if( !$infinity23_email_log_table_exists ) { ?>

					<div class="email-log-table-empty">The email log table has not been created yet - save the settings above with Email Log enabled to create it.</div>

				<?php } else { ?>

					<p class="infinity23-notice-text">Logs older than <?php echo esc_html( $infinity23_email_log_retention_days ); ?> days, or beyond the most recent <?php echo esc_html( number_format( $infinity23_email_log_count_limit ) ); ?> rows, are removed automatically once a day.</p>

					<form method="GET" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
						<input type="hidden" name="page" value="essential-email-smtp" />
						<input type="hidden" name="tab" value="logs" />

						<div class="form-line">
							<label for="log_status">Status: </label>
							<select id="log_status" name="log_status">
								<option value="" <?php if( $infinity23_email_log_status_filter === '' ) { echo 'selected'; } ?>>All</option>
								<option value="success" <?php if( $infinity23_email_log_status_filter === 'success' ) { echo 'selected'; } ?>>Success</option>
								<option value="failed" <?php if( $infinity23_email_log_status_filter === 'failed' ) { echo 'selected'; } ?>>Failed</option>
							</select>

							<label for="log_per_page">Entries Per Page: </label>
							<select id="log_per_page" name="log_per_page">
								<?php foreach( array( 10, 25, 50, 100, 200 ) as $infinity23_email_log_per_page_option ) { ?>
									<option value="<?php echo esc_attr( $infinity23_email_log_per_page_option ); ?>" <?php if( $infinity23_email_log_per_page == $infinity23_email_log_per_page_option ) { echo 'selected'; } ?>><?php echo esc_html( $infinity23_email_log_per_page_option ); ?></option>
								<?php } // foreach( array( 10, 25, 50, 100, 200 ) as $infinity23_email_log_per_page_option ) { ?>
							</select>

							<input type="submit" class="infinity23-inline-filter-btn" value="Filter Logs" />
						</div><!--form-line-->
					</form>

					<?php if( empty( $infinity23_email_log_rows ) ) { ?>

						<div class="email-log-table-empty">No emails have been logged yet.</div>

					<?php } else { ?>

						<div class="infinity23-email-log-table-wrap">
						<table class="infinity23-email-log-table">
							<thead>
								<tr>
									<th>Status</th>
									<th>To</th>
									<th>Subject</th>
									<th>Provider</th>
									<th>Date</th>
									<th>Error</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>

								<?php foreach( $infinity23_email_log_rows as $infinity23_email_log_row ) { ?>

									<tr class="<?php if( $infinity23_email_log_row['status'] === 'failed' ) { echo 'email-log-row-failed'; } ?>">
										<td>
											<?php if( $infinity23_email_log_row['status'] === 'failed' ) { ?>
												<span class="email-log-badge email-log-badge-failed">Failed</span>
											<?php } else { ?>
												<span class="email-log-badge email-log-badge-success">Success</span>
											<?php } // if( $infinity23_email_log_row['status'] === 'failed' ) { ?>
										</td>
										<td><?php echo esc_html( $infinity23_email_log_row['sent_to'] ); ?></td>
										<td><?php echo esc_html( $infinity23_email_log_row['subject'] ); ?></td>
										<td><?php echo esc_html( $infinity23_email_log_row['smtp_provider'] ); ?></td>
										<td><?php echo esc_html( infinity23_format_timestamp( $infinity23_email_log_row['time_created_utc'], 'd/m/Y H:i' ) ); ?></td>
										<td><?php echo esc_html( $infinity23_email_log_row['error_message'] ); ?></td>
										<td>
											<button type="button" class="infinity23-email-log-action-btn infinity23-email-log-view-btn" data-target="infinity23-email-log-message-<?php echo esc_attr( $infinity23_email_log_row['id'] ); ?>">View Message</button>
											<?php if( !empty( $infinity23_email_log_row['is_html'] ) && !empty( $infinity23_email_log_row['message'] ) ) { ?>
												<button type="button" class="infinity23-email-log-action-btn infinity23-email-log-view-html-btn" data-target="infinity23-email-log-html-<?php echo esc_attr( $infinity23_email_log_row['id'] ); ?>">View As HTML</button>
											<?php } // if( !empty( $infinity23_email_log_row['is_html'] ) && !empty( $infinity23_email_log_row['message'] ) ) { ?>
											<a class="infinity23-email-log-action-btn infinity23-email-log-action-btn-danger" data-confirm="Delete this log entry?" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'infinity23_email_log_delete', 'log_id' => $infinity23_email_log_row['id'] ), admin_url( 'admin-post.php' ) ), 'infinity23_email_log_delete_' . $infinity23_email_log_row['id'], 'infinity23_email_log_nonce' ) ); ?>">Delete</a>
										</td>
									</tr>

									<tr class="infinity23-email-log-message-row infinity23-smtp-note-hidden" id="infinity23-email-log-message-<?php echo esc_attr( $infinity23_email_log_row['id'] ); ?>">
										<td colspan="7">
											<?php if( !empty( $infinity23_email_log_row['message'] ) ) { ?>
												<pre class="infinity23-email-log-message-content"><?php echo esc_html( $infinity23_email_log_row['message'] ); ?></pre>
											<?php } else { ?>
												<p class="default-img-url">No message was saved for this entry.</p>
											<?php } // if( !empty( $infinity23_email_log_row['message'] ) ) { ?>
										</td>
									</tr>

									<?php if( !empty( $infinity23_email_log_row['is_html'] ) && !empty( $infinity23_email_log_row['message'] ) ) { ?>
									<tr class="infinity23-email-log-html-row infinity23-smtp-note-hidden" id="infinity23-email-log-html-<?php echo esc_attr( $infinity23_email_log_row['id'] ); ?>">
										<td colspan="7">
											<iframe class="infinity23-email-log-html-frame" sandbox="" srcdoc="<?php echo esc_attr( $infinity23_email_log_row['message'] ); ?>"></iframe>
										</td>
									</tr>
									<?php } // if( !empty( $infinity23_email_log_row['is_html'] ) && !empty( $infinity23_email_log_row['message'] ) ) { ?>

								<?php } // foreach( $infinity23_email_log_rows as $infinity23_email_log_row ) { ?>

							</tbody>
						</table>
						</div><!--infinity23-email-log-table-wrap-->

						<?php if( $infinity23_email_log_total_pages > 1 ) { ?>
							<p>
								<?php if( $infinity23_email_log_page > 1 ) { ?>
									<a class="infinity23-email-log-action-btn" href="<?php echo esc_url( add_query_arg( 'log_page', $infinity23_email_log_page - 1 ) ); ?>">&larr; Newer</a>
								<?php } // if( $infinity23_email_log_page > 1 ) { ?>
								Page <?php echo esc_html( $infinity23_email_log_page ); ?> of <?php echo esc_html( $infinity23_email_log_total_pages ); ?> (<?php echo esc_html( $infinity23_email_log_total_rows ); ?> total)
								<?php if( $infinity23_email_log_page < $infinity23_email_log_total_pages ) { ?>
									<a class="infinity23-email-log-action-btn" href="<?php echo esc_url( add_query_arg( 'log_page', $infinity23_email_log_page + 1 ) ); ?>">Older &rarr;</a>
								<?php } // if( $infinity23_email_log_page < $infinity23_email_log_total_pages ) { ?>
							</p>
						<?php } // if( $infinity23_email_log_total_pages > 1 ) { ?>

					<?php } // if( empty( $infinity23_email_log_rows ) ) { ?>

					<?php // Clear All Log Entries button moved to the Danger Zone section below ?>

				<?php } // if( !$infinity23_email_log_table_exists ) { ?>

			</div><!--section-card-->

			<?php if( $infinity23_email_log_table_exists && infinity23_email_log_get_total_count() > 0 ) { ?>

			<div class="infinity23-danger-zone-warp infinity23-log-danger-zone-warp" data-card-default="closed">

				<h2><span class="infinity23-danger-zone-icon">&#9888;</span> Remove Logs Danger Zone</h2>

				<p>Permanently deletes every email log entry below. This cannot be undone.</p>

				<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="infinity23_email_log_clear_all" />
					<?php wp_nonce_field( 'infinity23_email_log_clear_all_action', 'infinity23_email_log_clear_all_nonce' ); ?>
					<button type="button" class="infinity23-reset-btn infinity23-danger-zone-btn" data-confirm="Delete ALL email log entries? This action cannot be undone.">Clear All Log Entries</button>
				</form>

			</div><!--infinity23-danger-zone-warp-->

			<?php } // if( $infinity23_email_log_table_exists && infinity23_email_log_get_total_count() > 0 ) { ?>

		</div><!--infinity23-module-tab-content-->

		<?php } // if( $infinity23_email_log_enabled == 1 ) { ?>

	</div><!--infinity23-module-tabs-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
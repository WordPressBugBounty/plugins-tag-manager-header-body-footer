<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Email SMTP mail routing - reads the saved settings and hooks WordPress's
	// mail pipeline so wp_mail() sends through the configured SMTP server
	// ===========================================================================

	include( dirname(__FILE__) . "/../../../include/settings.php" );
	include( dirname(__FILE__) . "/../internal-settings.php" );

	$infinity23_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	// AJAX test actions below are registered unconditionally (not gated by
	// $infinity23_code_is_active) so both test actions keep working even when the module
	// is not yet activated, matching what admin-output.php tells the user

		// --------------------------------------------
		// shared troubleshooting tips shown alongside any connection/send
		// failure on this page, so the user always gets actionable next steps
		// --------------------------------------------

		if( !function_exists( 'infinity23_email_smtp_get_error_tips' ) ) {

			function infinity23_email_smtp_get_error_tips() {

				return array(
					'Recheck the SMTP connection settings above (Host, Port, Encryption, Username, Password) and make sure they are correct',
					'If the connection itself is fine, make sure the From Email address approved with your SMTP provider - sending from an unapproved domain gets blocked',
				);

			} // function infinity23_email_smtp_get_error_tips() {

		} // if( !function_exists( 'infinity23_email_smtp_get_error_tips' ) ) {

		// ===========================================================================
		// AJAX - send a test email (module must be active for this action to exist)
		// ===========================================================================

		add_action( 'wp_ajax_infinity23_email_smtp_send_test', function() {

			if( !check_ajax_referer( 'infinity23_email_smtp_test_action', 'nonce', false ) ) {
				wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
			} // if( !check_ajax_referer(

			if( !current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => 'Permission denied' ) );
			} // if( !current_user_can( 'manage_options' ) ) {

			$test_to = isset( $_POST['test_to_email'] ) ? sanitize_email( sanitize_text_field( wp_unslash( $_POST['test_to_email'] ) ) ) : '';
			$test_subject = isset( $_POST['test_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['test_subject'] ) ) : '';
			$test_message = isset( $_POST['test_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['test_message'] ) ) : '';

			if( empty( $test_subject ) ) {
				$test_subject = 'Email SMTP Test';
			} // if( empty( $test_subject ) ) {

			if( empty( $test_message ) ) {
				$test_message = 'This is a test email sent from the Email SMTP module.';
			} // if( empty( $test_message ) ) {

			if( empty( $test_to ) || !is_email( $test_to ) ) {
				wp_send_json_error( array( 'message' => 'The recipient email address is invalid' ) );
			} // if( empty( $test_to ) || !is_email( $test_to ) ) {

			$test_error_message = '';

			$infinity23_test_error_catcher = function( $wp_error ) use ( &$test_error_message ) {
				$test_error_message = $wp_error->get_error_message();
			};

			// optional sender override - only applies to this single test send,
			// never saved, and registered at PHP_INT_MAX so it wins over the
			// module's own Force Email Sender Settings filters (PHP_INT_MAX - 10)
			$test_from_email = isset( $_POST['test_from_email'] ) ? sanitize_email( sanitize_text_field( wp_unslash( $_POST['test_from_email'] ) ) ) : '';
			$test_from_name = isset( $_POST['test_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['test_from_name'] ) ) : '';

			$infinity23_test_from_email_filter = null;
			$infinity23_test_from_name_filter = null;

			if( !empty( $test_from_email ) && is_email( $test_from_email ) ) {

				$infinity23_test_from_email_filter = function( $current_email ) use ( $test_from_email ) {
					return $test_from_email;
				};

				add_filter( 'wp_mail_from', $infinity23_test_from_email_filter, PHP_INT_MAX );

			} // if( !empty( $test_from_email ) && is_email( $test_from_email ) ) {

			if( !empty( $test_from_name ) ) {

				$infinity23_test_from_name_filter = function( $current_name ) use ( $test_from_name ) {
					return $test_from_name;
				};

				add_filter( 'wp_mail_from_name', $infinity23_test_from_name_filter, PHP_INT_MAX );

			} // if( !empty( $test_from_name ) ) {

			add_action( 'wp_mail_failed', $infinity23_test_error_catcher );
			$test_sent = wp_mail( $test_to, $test_subject, $test_message );
			remove_action( 'wp_mail_failed', $infinity23_test_error_catcher );

			if( !empty( $infinity23_test_from_email_filter ) ) {
				remove_filter( 'wp_mail_from', $infinity23_test_from_email_filter, PHP_INT_MAX );
			} // if( !empty( $infinity23_test_from_email_filter ) ) {

			if( !empty( $infinity23_test_from_name_filter ) ) {
				remove_filter( 'wp_mail_from_name', $infinity23_test_from_name_filter, PHP_INT_MAX );
			} // if( !empty( $infinity23_test_from_name_filter ) ) {

			if( $test_sent ) {

				$infinity23_test_sent_message = '<strong class="infinity23-test-result-headline">Test email sent successfully</strong>';
				$infinity23_test_sent_message .= '<ul class="infinity23-test-result-reasons">';
				$infinity23_test_sent_message .= '<li>If it\'s not in your inbox, check your spam or junk folder</li>';
				$infinity23_test_sent_message .= '<li>Check your SMTP provider\'s own activity or sent log for the real delivery status</li>';
				$infinity23_test_sent_message .= '<li>Some providers block sending from an unverified domain - if you\'re having trouble, make sure your From address\'s domain is verified with your provider</li>';
				$infinity23_test_sent_message .= '</ul>';

				wp_send_json_success( array( 'message' => $infinity23_test_sent_message ) );

			} else {
				wp_send_json_error( array( 'message' => !empty( $test_error_message ) ? esc_html( $test_error_message ) : 'Unknown error', 'tips' => infinity23_email_smtp_get_error_tips() ) );
			} // if( $test_sent ) {

		} ); // add_action( 'wp_ajax_infinity23_email_smtp_send_test'

		// ===========================================================================
		// AJAX - test the SMTP connection without sending an email (module must be active for this action to exist)
		// ===========================================================================

		add_action( 'wp_ajax_infinity23_email_smtp_test_connection', function() {

			if( !check_ajax_referer( 'infinity23_email_smtp_test_connection_action', 'nonce', false ) ) {
				wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
			} // if( !check_ajax_referer(

			if( !current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => 'Permission denied' ) );
			} // if( !current_user_can( 'manage_options' ) ) {

			include( dirname(__FILE__) . "/../internal-settings.php" );
			include( dirname(__FILE__) . "/../default-values.php" );

			$test_provider = isset( $_POST['smtp_provider'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_provider'] ) ) : 'wordpress_default';

			if( $test_provider === 'wordpress_default' ) {
				wp_send_json_error( array( 'message' => 'Default WordPress does not use SMTP - nothing to test' ) );
			} // if( $test_provider === 'wordpress_default' ) {

			$infinity23_test_debug_lines = array();

			$infinity23_test_saved_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			// the saved secret may only be reused against the exact saved server
			// details - true when every posted field in $field_names equals its saved value
			$infinity23_test_matches_saved = function( $field_names ) use ( $infinity23_test_saved_data_array, $infinity23_defaults ) {

				foreach( $field_names as $field_name ) {

					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified by check_ajax_referer() in the enclosing wp_ajax handler before this closure is even defined
					$posted_value = isset( $_POST[ $field_name ] ) && !is_array( $_POST[ $field_name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) ) : '';
					$posted_value = trim( infinity23_email_smtp_strip_invisible_chars( $posted_value ) );
					$saved_value = trim( strval( infinity23_pro_get_data( $field_name, $infinity23_test_saved_data_array, $infinity23_defaults ) ) );

					if( $posted_value !== $saved_value ) {
						return false;
					} // if( $posted_value !== $saved_value ) {

				} // foreach( $field_names as $field_name ) {

				return true;

			};

			// resolve one posted field, falling back to the saved decrypted value
			// when the posted value is still the dot mask (field never focused) -
			// only allowed when $allow_saved_fallback is true, so the real saved
			// password is never sent to a server that differs from the saved one
			$infinity23_resolve_test_secret = function( $post_field_name, $saved_field_name, $context, $allow_saved_fallback = true ) use ( $infinity23_test_saved_data_array, $infinity23_defaults ) {

				// never run passwords/api keys through sanitize_text_field() - it silently
				// strips any "%" followed by two hex characters (percent-encoded sequences)
				// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce already verified by check_ajax_referer() in the enclosing wp_ajax handler before this closure is even defined; sanitize_text_field() is deliberately not used here since it corrupts percent-encoded sequences that can legitimately appear in passwords/API keys (see comment above) - value is unslashed and only used to test an SMTP/API connection, never output as HTML or used in SQL
				$posted_value = isset( $_POST[ $post_field_name ] ) && !is_array( $_POST[ $post_field_name ] ) ? trim( wp_unslash( $_POST[ $post_field_name ] ) ) : '';
				// match the same cleanup the Save path already applies, so a freshly
				// typed value tested here behaves identically to what gets saved
				$posted_value = infinity23_email_smtp_strip_invisible_chars( $posted_value );

				if( empty( $posted_value ) || preg_match( '/^\x{2022}+$/u', $posted_value ) ) {

					$infinity23_saved_encrypted = infinity23_pro_get_data( $saved_field_name, $infinity23_test_saved_data_array, $infinity23_defaults );

					if( !$allow_saved_fallback && !empty( $infinity23_saved_encrypted ) ) {
						wp_send_json_error( array( 'message' => 'The server details were changed from the saved ones - type the password again to test against the new server' ) );
					} // if( !$allow_saved_fallback && !empty( $infinity23_saved_encrypted ) ) {

					$decrypted = infinity23_decrypt_value( $infinity23_saved_encrypted, $context );

					if( empty( $decrypted ) && !empty( $infinity23_saved_encrypted ) ) {
						$decrypted = infinity23_decrypt_value( $infinity23_saved_encrypted, 'default' );
					} // if( empty( $decrypted ) && !empty( $infinity23_saved_encrypted ) ) {

					return $decrypted;

				} // if( !empty( $posted_value ) &&

				return $posted_value;

			};

			// Elastic Email's HTTP API mode never uses SMTP at all - test the API
			// key directly instead of building a bogus SMTP connection with an
			// empty username (the SMTP Username field is hidden in API mode)
			$test_elastic_connection_method = isset( $_POST['smtp_elastic_connection_method'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_elastic_connection_method'] ) ) : 'smtp';

			if( $test_provider === 'elastic_email' && $test_elastic_connection_method === 'api' ) {

				$test_api_key = $infinity23_resolve_test_secret( 'smtp_elastic_api_key', 'smtp_elastic_api_key', 'email_smtp_elastic_api_key' );

				if( empty( $test_api_key ) ) {
					wp_send_json_error( array( 'message' => 'API Key is empty' ) );
				} // if( empty( $test_api_key ) ) {

				// GET /v4/security/apikeys checks the key is valid without sending any
				// email - note it requires the key to have "View Access Tokens"
				// permission, so a send-only key will fail this even though sending works
				$test_api_response = wp_remote_get( 'https://api.elasticemail.com/v4/security/apikeys', array(
					'timeout' => 15,
					'headers' => array( 'X-ElasticEmail-ApiKey' => $test_api_key ),
				) );

				if( is_wp_error( $test_api_response ) ) {
					wp_send_json_error( array( 'message' => esc_html( $test_api_response->get_error_message() ), 'tips' => infinity23_email_smtp_get_error_tips() ) );
				} // if( is_wp_error( $test_api_response ) ) {

				$test_api_code = wp_remote_retrieve_response_code( $test_api_response );

				if( $test_api_code >= 200 && $test_api_code < 300 ) {
					wp_send_json_success( array( 'message' => 'Connection successful - the API Key was accepted' . "\n" . 'Make sure to fill in the Sender Settings below and send a test email to validate that everything is working.' ) );
				} // if( $test_api_code >= 200 && $test_api_code < 300 ) {

				if( $test_api_code === 401 || $test_api_code === 403 ) {
					wp_send_json_error( array( 'message' => 'The API Key was rejected (HTTP ' . intval( $test_api_code ) . ') - double check it was copied correctly', 'tips' => infinity23_email_smtp_get_error_tips() ) );
				} // if( $test_api_code === 401 || $test_api_code === 403 ) {

				wp_send_json_error( array( 'message' => 'HTTP ' . intval( $test_api_code ) . ' - if your API Key is scoped to send-only permissions, this check cannot verify it; use Send Test Email instead', 'tips' => infinity23_email_smtp_get_error_tips() ) );

			} // if( $test_provider === 'elastic_email' && $test_elastic_connection_method === 'api' ) {

			$test_host = '';
			$test_port = 587;
			$test_encryption = 'tls';
			$test_auth_type = 'none';
			$test_username = '';
			$test_password = '';

			if( $test_provider === 'custom' ) {

				$test_host = isset( $_POST['smtp_custom_host'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_custom_host'] ) ) : '';
				$test_port = isset( $_POST['smtp_custom_port'] ) ? intval( $_POST['smtp_custom_port'] ) : 587;
				$test_encryption = isset( $_POST['smtp_custom_encryption'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_custom_encryption'] ) ) : 'tls';
				$test_auth_type = isset( $_POST['smtp_custom_auth_type'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_custom_auth_type'] ) ) : 'plain';
				$test_username = isset( $_POST['smtp_custom_username'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_custom_username'] ) ) : '';
				$test_password = $infinity23_resolve_test_secret( 'smtp_custom_password', 'smtp_custom_password', 'email_smtp_custom_password', $infinity23_test_matches_saved( array( 'smtp_custom_host', 'smtp_custom_port', 'smtp_custom_username' ) ) );

			} elseif( $test_provider === 'gmail' ) {

				$test_host = 'smtp.gmail.com';
				$test_port = 587;
				$test_encryption = 'tls';
				$test_auth_type = 'plain';
				$test_username = isset( $_POST['smtp_gmail_username'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_gmail_username'] ) ) : '';
				$test_password = $infinity23_resolve_test_secret( 'smtp_gmail_password', 'smtp_gmail_password', 'email_smtp_gmail_password' );

			} elseif( $test_provider === 'amazon_ses' ) {

				$test_host = isset( $_POST['smtp_ses_host'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_ses_host'] ) ) : '';
				$test_port = isset( $_POST['smtp_ses_port'] ) ? intval( $_POST['smtp_ses_port'] ) : 465;
				$test_encryption = isset( $_POST['smtp_ses_encryption'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_ses_encryption'] ) ) : 'ssl';
				$test_auth_type = 'login';
				$test_username = isset( $_POST['smtp_ses_username'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_ses_username'] ) ) : '';
				$test_password = $infinity23_resolve_test_secret( 'smtp_ses_password', 'smtp_ses_password', 'email_smtp_ses_password', $infinity23_test_matches_saved( array( 'smtp_ses_host', 'smtp_ses_port', 'smtp_ses_username' ) ) );

			} elseif( $test_provider === 'elastic_email' ) {

				$test_host = 'smtp.elasticemail.com';
				$test_port = 2525;
				$test_encryption = 'tls';
				// forced to 'login' - auto-negotiate lets PHPMailer pick CRAM-MD5, which
				// Elastic Email advertises but rejects for API-key-style credentials
				$test_auth_type = 'login';
				$test_username = isset( $_POST['smtp_elastic_username'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_elastic_username'] ) ) : '';
				$test_password = $infinity23_resolve_test_secret( 'smtp_elastic_api_key', 'smtp_elastic_api_key', 'email_smtp_elastic_api_key' );

			} elseif( $test_provider === 'sendgrid' ) {

				$test_host = 'smtp.sendgrid.net';
				$test_port = 587;
				$test_encryption = 'tls';
				$test_auth_type = 'plain';
				$test_username = 'apikey';
				$test_password = $infinity23_resolve_test_secret( 'smtp_sendgrid_api_key', 'smtp_sendgrid_api_key', 'email_smtp_sendgrid_api_key' );

			} elseif( $test_provider === 'mailgun' ) {

				$test_host = isset( $_POST['smtp_mailgun_host'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_mailgun_host'] ) ) : 'smtp.mailgun.org';
				$test_port = 587;
				$test_encryption = 'tls';
				$test_auth_type = 'plain';
				$test_username = isset( $_POST['smtp_mailgun_username'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_mailgun_username'] ) ) : '';
				$test_password = $infinity23_resolve_test_secret( 'smtp_mailgun_password', 'smtp_mailgun_password', 'email_smtp_mailgun_password', $infinity23_test_matches_saved( array( 'smtp_mailgun_host', 'smtp_mailgun_username' ) ) );

			} elseif( $test_provider === 'maileroo' ) {

				$test_host = 'smtp.maileroo.com';
				$test_port = 587;
				$test_encryption = 'tls';
				$test_auth_type = 'plain';
				$test_username = isset( $_POST['smtp_maileroo_username'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_maileroo_username'] ) ) : '';
				$test_password = $infinity23_resolve_test_secret( 'smtp_maileroo_password', 'smtp_maileroo_password', 'email_smtp_maileroo_password' );

			} // if( $test_provider === 'custom' ) {

			if( empty( $test_host ) ) {
				wp_send_json_error( array( 'message' => 'SMTP Host is empty' ) );
			} // if( empty( $test_host ) ) {

			if( !class_exists( 'PHPMailer\PHPMailer\PHPMailer' ) ) {
				require_once( ABSPATH . WPINC . '/PHPMailer/PHPMailer.php' );
				require_once( ABSPATH . WPINC . '/PHPMailer/SMTP.php' );
				require_once( ABSPATH . WPINC . '/PHPMailer/Exception.php' );
			} // if( !class_exists( 'PHPMailer\PHPMailer\PHPMailer' ) ) {

			$test_mailer = new PHPMailer\PHPMailer\PHPMailer( true );

			try {

				$test_mailer->isSMTP();
				$test_mailer->Host = $test_host;
				$test_mailer->Port = $test_port;
				$test_mailer->Timeout = 10;

				// capture the raw server conversation so a real auth failure
				// shows the provider's exact rejection reason instead of only
				// PHPMailer's generic "could not authenticate" summary
				$test_mailer->SMTPDebug = 2;
				$test_mailer->Debugoutput = function( $debug_str, $debug_level ) use ( &$infinity23_test_debug_lines ) {
					$infinity23_test_debug_lines[] = trim( $debug_str );
				};

				if( $test_encryption === 'none' ) {
					$test_mailer->SMTPSecure = '';
					$test_mailer->SMTPAutoTLS = false;
				} else {
					$test_mailer->SMTPSecure = $test_encryption;
					$test_mailer->SMTPAutoTLS = ( $test_encryption === 'tls' );
				} // if( $test_encryption === 'none' ) {

				if( $test_auth_type !== 'none' ) {
					$test_mailer->SMTPAuth = true;
					$test_mailer->Username = $test_username;
					$test_mailer->Password = $test_password;
					// only force AuthType for Login - leave it empty otherwise so
					// PHPMailer auto-negotiates against what the server actually
					// advertises in its EHLO response, instead of forcing PLAIN
					// which some servers (Elastic Email included) reject outright
					if( $test_auth_type === 'login' ) {
						$test_mailer->AuthType = 'LOGIN';
					} // if( $test_auth_type === 'login' ) {
				} else {
					$test_mailer->SMTPAuth = false;
				} // if( $test_auth_type !== 'none' ) {

				if( $test_mailer->smtpConnect() ) {
					$test_mailer->smtpClose();
					wp_send_json_success( array( 'message' => 'Connection successful - the SMTP server accepted the connection' . ( $test_auth_type !== 'none' ? ' and login' : '' ) . "\n" . 'Make sure to fill in the Sender Settings below and send a test email to validate that everything is working.' ) );
				} else {

					// PHPMailer debug lines look like "SERVER -> CLIENT: 535 Authentication
					// failed" - the response code is not at the start of the line, so pull
					// out everything from the 3-digit code onward instead of matching ^\d{3}
					$infinity23_test_server_lines = array();
					foreach( $infinity23_test_debug_lines as $infinity23_test_debug_line ) {
						if( preg_match( '/(\d{3}[\s-].+)/', $infinity23_test_debug_line, $infinity23_test_debug_matches ) ) {
							$infinity23_test_server_lines[] = trim( $infinity23_test_debug_matches[1] );
						} // if( preg_match(
					} // foreach( $infinity23_test_debug_lines as $infinity23_test_debug_line ) {

					$infinity23_test_error_detail = !empty( $infinity23_test_server_lines ) ? implode( ' | ', $infinity23_test_server_lines ) : 'no response details captured';

					wp_send_json_error( array( 'message' => 'Could not connect to the SMTP server - server response: ' . esc_html( $infinity23_test_error_detail ), 'tips' => infinity23_email_smtp_get_error_tips() ) );

				} // if( $test_mailer->smtpConnect() ) {

			} catch( Exception $test_exception ) {

				$infinity23_test_server_lines = array();
				foreach( $infinity23_test_debug_lines as $infinity23_test_debug_line ) {
					if( preg_match( '/(\d{3}[\s-].+)/', $infinity23_test_debug_line, $infinity23_test_debug_matches ) ) {
						$infinity23_test_server_lines[] = trim( $infinity23_test_debug_matches[1] );
					} // if( preg_match(
				} // foreach( $infinity23_test_debug_lines as $infinity23_test_debug_line ) {

				$infinity23_test_error_detail = !empty( $infinity23_test_server_lines ) ? ' - server response: ' . implode( ' | ', $infinity23_test_server_lines ) : '';

				wp_send_json_error( array( 'message' => esc_html( $test_exception->getMessage() . $infinity23_test_error_detail ), 'tips' => infinity23_email_smtp_get_error_tips() ) );

			} // try

		} ); // add_action( 'wp_ajax_infinity23_email_smtp_test_connection'

		// ===========================================================================
		// Admin-post handlers - delete one email log row / clear the whole log.
		// Registered unconditionally, same as the two AJAX actions above, so they
		// work whenever the module is active regardless of $infinity23_code_is_active below.
		// ===========================================================================

		add_action( 'admin_post_infinity23_email_log_delete', function() {

			if( !current_user_can( 'manage_options' ) ) {
				wp_die( 'Permission denied.' );
			} // if( !current_user_can( 'manage_options' ) ) {

			$log_id = isset( $_GET['log_id'] ) ? intval( wp_unslash( $_GET['log_id'] ) ) : 0;
			$log_nonce = isset( $_GET['infinity23_email_log_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_email_log_nonce'] ) ) : '';

			if( empty( $log_id ) || !wp_verify_nonce( $log_nonce, 'infinity23_email_log_delete_' . $log_id ) ) {
				wp_die( 'Link expired.' );
			} // if( empty( $log_id ) ||

			include_once( dirname(__FILE__) . "/email-log-db-functions.php" );
			infinity23_email_log_delete_one( $log_id );

			wp_safe_redirect( admin_url( 'admin.php?page=essential-email-smtp&tab=logs' ) );
			exit;

		} ); // add_action( 'admin_post_infinity23_email_log_delete'

		add_action( 'admin_post_infinity23_email_log_clear_all', function() {

			if( !current_user_can( 'manage_options' ) ) {
				wp_die( 'Permission denied.' );
			} // if( !current_user_can( 'manage_options' ) ) {

			check_admin_referer( 'infinity23_email_log_clear_all_action', 'infinity23_email_log_clear_all_nonce' );

			include_once( dirname(__FILE__) . "/email-log-db-functions.php" );
			infinity23_email_log_clear_all();

			wp_safe_redirect( admin_url( 'admin.php?page=essential-email-smtp&tab=logs' ) );
			exit;

		} ); // add_action( 'admin_post_infinity23_email_log_clear_all'

		// mail routing (SMTP transport, sender override) only applies once the
		// module is actually activated - the two AJAX test actions above are
		// registered unconditionally further up, outside this check
		if( !empty( $infinity23_code_is_active ) ) {

		include( dirname(__FILE__) . "/../default-values.php" );

		$infinity23_email_smtp_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

		$infinity23_smtp_provider = infinity23_pro_get_data( 'smtp_provider', $infinity23_email_smtp_data_array, $infinity23_defaults );

		// --------------------------------------------
		// resolve host/port/encryption/auth/username/password from the fields
		// that belong to the currently selected provider only - the other
		// providers' saved fields are simply ignored here
		// --------------------------------------------

		if( !function_exists( 'infinity23_email_smtp_get_active_connection_details' ) ) {

			function infinity23_email_smtp_get_active_connection_details( $infinity23_smtp_provider, $infinity23_data_array, $infinity23_defaults, $provider_config ) {

				$details = array( 'host' => '', 'port' => 587, 'encryption' => 'tls', 'auth_type' => 'none', 'username' => '', 'password' => '' );

				if( $infinity23_smtp_provider === 'wordpress_default' ) {
					return $details;
				} // if( $infinity23_smtp_provider === 'wordpress_default' ) {

				if( $infinity23_smtp_provider === 'custom' ) {

					$details['host'] = infinity23_pro_get_data( 'smtp_custom_host', $infinity23_data_array, $infinity23_defaults );
					$details['port'] = infinity23_pro_get_data( 'smtp_custom_port', $infinity23_data_array, $infinity23_defaults );
					$details['encryption'] = infinity23_pro_get_data( 'smtp_custom_encryption', $infinity23_data_array, $infinity23_defaults );
					$details['auth_type'] = infinity23_pro_get_data( 'smtp_custom_auth_type', $infinity23_data_array, $infinity23_defaults );
					$details['username'] = infinity23_pro_get_data( 'smtp_custom_username', $infinity23_data_array, $infinity23_defaults );
					$infinity23_password_encrypted = infinity23_pro_get_data( 'smtp_custom_password', $infinity23_data_array, $infinity23_defaults );
					$details['password'] = infinity23_decrypt_value( $infinity23_password_encrypted, 'email_smtp_custom_password' );

					return $details;

				} // if( $infinity23_smtp_provider === 'custom' ) {

				if( isset( $provider_config[ $infinity23_smtp_provider ] ) ) {

					$preset = $provider_config[ $infinity23_smtp_provider ];
					$details['port'] = $preset['port'];
					$details['encryption'] = $preset['encryption'];
					$details['auth_type'] = $preset['auth_type'];
					$details['host'] = !empty( $preset['host'] ) ? $preset['host'] : '';

				} // if( isset( $provider_config[ $infinity23_smtp_provider ] ) ) {

				if( $infinity23_smtp_provider === 'gmail' ) {

					$details['username'] = infinity23_pro_get_data( 'smtp_gmail_username', $infinity23_data_array, $infinity23_defaults );
					$infinity23_password_encrypted = infinity23_pro_get_data( 'smtp_gmail_password', $infinity23_data_array, $infinity23_defaults );
					$details['password'] = infinity23_decrypt_value( $infinity23_password_encrypted, 'email_smtp_gmail_password' );

				} elseif( $infinity23_smtp_provider === 'amazon_ses' ) {

					$details['host'] = infinity23_pro_get_data( 'smtp_ses_host', $infinity23_data_array, $infinity23_defaults );
					$details['port'] = infinity23_pro_get_data( 'smtp_ses_port', $infinity23_data_array, $infinity23_defaults );
					$details['encryption'] = infinity23_pro_get_data( 'smtp_ses_encryption', $infinity23_data_array, $infinity23_defaults );
					$details['username'] = infinity23_pro_get_data( 'smtp_ses_username', $infinity23_data_array, $infinity23_defaults );
					$infinity23_password_encrypted = infinity23_pro_get_data( 'smtp_ses_password', $infinity23_data_array, $infinity23_defaults );
					$details['password'] = infinity23_decrypt_value( $infinity23_password_encrypted, 'email_smtp_ses_password' );

				} elseif( $infinity23_smtp_provider === 'elastic_email' ) {

					$details['connection_method'] = infinity23_pro_get_data( 'smtp_elastic_connection_method', $infinity23_data_array, $infinity23_defaults );
					$details['username'] = infinity23_pro_get_data( 'smtp_elastic_username', $infinity23_data_array, $infinity23_defaults );
					$infinity23_api_key_encrypted = infinity23_pro_get_data( 'smtp_elastic_api_key', $infinity23_data_array, $infinity23_defaults );
					$details['password'] = infinity23_decrypt_value( $infinity23_api_key_encrypted, 'email_smtp_elastic_api_key' );

				} elseif( $infinity23_smtp_provider === 'sendgrid' ) {

					$infinity23_api_key_encrypted = infinity23_pro_get_data( 'smtp_sendgrid_api_key', $infinity23_data_array, $infinity23_defaults );
					$details['username'] = 'apikey';
					$details['password'] = infinity23_decrypt_value( $infinity23_api_key_encrypted, 'email_smtp_sendgrid_api_key' );

				} elseif( $infinity23_smtp_provider === 'mailgun' ) {

					$details['host'] = infinity23_pro_get_data( 'smtp_mailgun_host', $infinity23_data_array, $infinity23_defaults );
					$details['username'] = infinity23_pro_get_data( 'smtp_mailgun_username', $infinity23_data_array, $infinity23_defaults );
					$infinity23_password_encrypted = infinity23_pro_get_data( 'smtp_mailgun_password', $infinity23_data_array, $infinity23_defaults );
					$details['password'] = infinity23_decrypt_value( $infinity23_password_encrypted, 'email_smtp_mailgun_password' );

				} elseif( $infinity23_smtp_provider === 'maileroo' ) {

					$details['username'] = infinity23_pro_get_data( 'smtp_maileroo_username', $infinity23_data_array, $infinity23_defaults );
					$infinity23_password_encrypted = infinity23_pro_get_data( 'smtp_maileroo_password', $infinity23_data_array, $infinity23_defaults );
					$details['password'] = infinity23_decrypt_value( $infinity23_password_encrypted, 'email_smtp_maileroo_password' );

				} // if( $infinity23_smtp_provider === 'gmail' ) {

				return $details;

			} // function infinity23_email_smtp_get_active_connection_details( $infinity23_smtp_provider, $infinity23_data_array, $infinity23_defaults, $provider_config ) {

		} // if( !function_exists(

		$infinity23_email_smtp_connection = infinity23_email_smtp_get_active_connection_details( $infinity23_smtp_provider, $infinity23_email_smtp_data_array, $infinity23_defaults, $infinity23_smtp_provider_config );

		$infinity23_smtp_host = $infinity23_email_smtp_connection['host'];
		$infinity23_smtp_port = $infinity23_email_smtp_connection['port'];
		$infinity23_smtp_encryption = $infinity23_email_smtp_connection['encryption'];
		$infinity23_smtp_auth_type = $infinity23_email_smtp_connection['auth_type'];
		$infinity23_smtp_username = $infinity23_email_smtp_connection['username'];
		$infinity23_smtp_password = $infinity23_email_smtp_connection['password'];
		$infinity23_smtp_elastic_connection_method = isset( $infinity23_email_smtp_connection['connection_method'] ) ? $infinity23_email_smtp_connection['connection_method'] : 'smtp';

		// ---------------------------------------
		// Email Log - record every email the site attempts to send, success or
		// failed, whenever the setting below is turned on
		// ---------------------------------------

		$infinity23_email_log_enabled = intval( infinity23_pro_get_data( 'email_log_enabled', $infinity23_email_smtp_data_array, $infinity23_defaults ) );
		$infinity23_email_log_save_message = intval( infinity23_pro_get_data( 'email_log_save_message', $infinity23_email_smtp_data_array, $infinity23_defaults ) );

		if( $infinity23_email_log_enabled == 1 ) {

			include_once( dirname(__FILE__) . "/email-log-db-functions.php" );

			add_action( 'wp_mail_succeeded', function( $mail_data ) use ( $infinity23_smtp_provider, $infinity23_email_log_save_message ) {

				$log_to = isset( $mail_data['to'] ) && is_array( $mail_data['to'] ) ? implode( ', ', $mail_data['to'] ) : ( isset( $mail_data['to'] ) ? $mail_data['to'] : '' );
				$log_message = ( $infinity23_email_log_save_message == 1 && isset( $mail_data['message'] ) ) ? $mail_data['message'] : '';

				infinity23_email_log_insert( $log_to, isset( $mail_data['subject'] ) ? $mail_data['subject'] : '', 'success', '', $infinity23_smtp_provider, $log_message );

			} );

			add_action( 'wp_mail_failed', function( $wp_error ) use ( $infinity23_smtp_provider, $infinity23_email_log_save_message ) {

				$error_mail_data = $wp_error->get_error_data();
				$error_mail_data = is_array( $error_mail_data ) ? $error_mail_data : array();

				$log_to = isset( $error_mail_data['to'] ) && is_array( $error_mail_data['to'] ) ? implode( ', ', $error_mail_data['to'] ) : ( isset( $error_mail_data['to'] ) ? $error_mail_data['to'] : '' );
				$log_message = ( $infinity23_email_log_save_message == 1 && isset( $error_mail_data['message'] ) ) ? $error_mail_data['message'] : '';

				infinity23_email_log_insert( $log_to, isset( $error_mail_data['subject'] ) ? $error_mail_data['subject'] : '', 'failed', $wp_error->get_error_message(), $infinity23_smtp_provider, $log_message );

			} );

		} // if( $infinity23_email_log_enabled == 1 ) {

		$infinity23_force_from_checkbox = infinity23_pro_get_data( 'force_from_checkbox', $infinity23_email_smtp_data_array, $infinity23_defaults );
		if( !in_array( $infinity23_force_from_checkbox, array( 'none', 'email', 'name', 'both' ), true ) ) {
			$infinity23_force_from_checkbox = ( intval( $infinity23_force_from_checkbox ) == 1 ) ? 'both' : 'none';
		} // if( !in_array(
		$infinity23_from_email = infinity23_pro_get_data( 'from_email', $infinity23_email_smtp_data_array, $infinity23_defaults );
		$infinity23_from_name = infinity23_pro_get_data( 'from_name', $infinity23_email_smtp_data_array, $infinity23_defaults );

		// no From Email/Name saved - fall back to the site's own admin email and
		// site name instead of WordPress's own default (wordpress@$domain),
		// which PHPMailer rejects on local/invalid domains
		if( empty( $infinity23_from_email ) ) {
			$infinity23_from_email = get_option( 'admin_email' );
		} // if( empty( $infinity23_from_email ) ) {

		if( empty( $infinity23_from_name ) ) {
			$infinity23_from_name = get_bloginfo( 'name' );
		} // if( empty( $infinity23_from_name ) ) {

		// when a field is forced, our own filter for that field runs last
		// (very high priority) so no other plugin's from-name/from-email
		// filter or phpmailer_init callback can win after ours
		$infinity23_email_smtp_priority = ( $infinity23_force_from_checkbox !== 'none' ) ? ( PHP_INT_MAX - 10 ) : 10;

		// ---------------------------------------
		// sender override - wp_mail_from / wp_mail_from_name filters
		// ---------------------------------------

		if( !empty( $infinity23_from_email ) && is_email( $infinity23_from_email ) ) {

			$infinity23_email_smtp_email_priority = in_array( $infinity23_force_from_checkbox, array( 'email', 'both' ), true ) ? ( PHP_INT_MAX - 10 ) : 10;

			add_filter( 'wp_mail_from', function( $current_email ) use ( $infinity23_from_email ) {
				return $infinity23_from_email;
			}, $infinity23_email_smtp_email_priority );

			if( !empty( $infinity23_from_name ) ) {

				$infinity23_email_smtp_name_priority = in_array( $infinity23_force_from_checkbox, array( 'name', 'both' ), true ) ? ( PHP_INT_MAX - 10 ) : 10;

				add_filter( 'wp_mail_from_name', function( $current_name ) use ( $infinity23_from_name ) {
					return $infinity23_from_name;
				}, $infinity23_email_smtp_name_priority );

			} // if( !empty( $infinity23_from_name ) ) {

		} // if( !empty( $infinity23_from_email ) && is_email( $infinity23_from_email ) ) {

		// ---------------------------------------
		// Elastic Email over HTTP API - short-circuits wp_mail() entirely
		// via pre_wp_mail (WP 5.7+), so PHPMailer/SMTP is never touched
		// ---------------------------------------

		if( $infinity23_smtp_provider === 'elastic_email' && $infinity23_smtp_elastic_connection_method === 'api' && !empty( $infinity23_smtp_password ) ) {

			if( !function_exists( 'infinity23_email_smtp_send_via_elastic_api' ) ) {

				function infinity23_email_smtp_send_via_elastic_api( $atts, $api_key, $infinity23_force_from_checkbox, $infinity23_from_email, $infinity23_from_name ) {

					$to = is_array( $atts['to'] ) ? $atts['to'] : array_map( 'trim', explode( ',', $atts['to'] ) );
					$recipients = array();

					foreach( $to as $to_address ) {
						if( is_email( $to_address ) ) {
							$recipients[] = $to_address;
						} // if( is_email( $to_address ) ) {
					} // foreach( $to as $to_address ) {

					if( empty( $recipients ) ) {
						return false;
					} // if( empty( $recipients ) ) {

					// when a field is not forced, let other plugins' wp_mail_from/wp_mail_from_name
					// filters still have a say - pre_wp_mail runs before wp_mail() ever applies
					// these filters itself, so this path must apply them manually or they never fire
					if( !in_array( $infinity23_force_from_checkbox, array( 'email', 'both' ), true ) ) {
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- invokes WP core's own wp_mail_from filter for compatibility, not a new hook
						$infinity23_from_email = apply_filters( 'wp_mail_from', $infinity23_from_email );
					} // if( !in_array(

					if( !in_array( $infinity23_force_from_checkbox, array( 'name', 'both' ), true ) ) {
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- invokes WP core's own wp_mail_from_name filter for compatibility, not a new hook
						$infinity23_from_name = apply_filters( 'wp_mail_from_name', $infinity23_from_name );
					} // if( !in_array(

					$is_html = ( stripos( wp_specialchars_decode( implode( ' ', (array) $atts['headers'] ) ), 'text/html' ) !== false );

					// pull Cc/Bcc/Reply-To out of the raw headers - wp_mail() passes headers
					// as either a single string or an array of "Name: value" lines
					$cc_recipients = array();
					$bcc_recipients = array();
					$reply_to_recipients = array();

					$header_lines = is_array( $atts['headers'] ) ? $atts['headers'] : explode( "\n", str_replace( "\r\n", "\n", $atts['headers'] ) );

					foreach( $header_lines as $header_line ) {

						if( strpos( $header_line, ':' ) === false ) { continue; }

						list( $header_name, $header_value ) = explode( ':', $header_line, 2 );
						$header_name = trim( $header_name );
						$header_value = trim( $header_value );

						if( empty( $header_value ) ) { continue; }

						$header_addresses = array_map( 'trim', explode( ',', $header_value ) );

						foreach( $header_addresses as $header_address ) {

							if( !is_email( $header_address ) ) { continue; }

							if( strcasecmp( $header_name, 'Cc' ) === 0 ) {
								$cc_recipients[] = $header_address;
							} elseif( strcasecmp( $header_name, 'Bcc' ) === 0 ) {
								$bcc_recipients[] = $header_address;
							} elseif( strcasecmp( $header_name, 'Reply-To' ) === 0 ) {
								$reply_to_recipients[] = $header_address;
							} // if( strcasecmp( $header_name, 'Cc' ) === 0 ) {

						} // foreach( $header_addresses as $header_address ) {

					} // foreach( $header_lines as $header_line ) {

					$recipients_block = array( 'To' => $recipients );

					if( !empty( $cc_recipients ) ) {
						$recipients_block['CC'] = $cc_recipients;
					} // if( !empty( $cc_recipients ) ) {

					if( !empty( $bcc_recipients ) ) {
						$recipients_block['BCC'] = $bcc_recipients;
					} // if( !empty( $bcc_recipients ) ) {

					$body = array(
						'Recipients' => $recipients_block,
						'Content' => array(
							'Subject' => $atts['subject'],
							'From' => !empty( $infinity23_from_name ) ? $infinity23_from_name . ' <' . $infinity23_from_email . '>' : $infinity23_from_email,
							'Body' => array(
								array( 'ContentType' => $is_html ? 'HTML' : 'PlainText', 'Content' => $atts['message'] ),
							),
						),
					);

					if( !empty( $reply_to_recipients[0] ) ) {
						$body['Content']['ReplyTo'] = $reply_to_recipients[0];
					} // if( !empty( $reply_to_recipients[0] ) ) {

					// note: attachments are not supported over the API method in this version

					// the "transactional" path is the correct endpoint for a single
					// wp_mail() call - the plain /v4/emails endpoint is for bulk/
					// campaign sends and expects a different request shape entirely
					$api_response = wp_remote_post( 'https://api.elasticemail.com/v4/emails/transactional', array(
						'timeout' => 15,
						'headers' => array( 'X-ElasticEmail-ApiKey' => $api_key, 'Content-Type' => 'application/json' ),
						'body' => wp_json_encode( $body ),
					) );

					// pre_wp_mail short-circuits wp_mail() before core fires wp_mail_succeeded / builds
					// the wp_mail_failed data, so both are passed manually - otherwise the Email Log
					// never records API sends
					$infinity23_elastic_mail_data = array(
						'to' => $recipients,
						'subject' => $atts['subject'],
						'message' => $atts['message'],
						'headers' => $atts['headers'],
						'attachments' => $atts['attachments'],
					);

					if( is_wp_error( $api_response ) ) {
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- invokes WP core's own wp_mail_failed action for compatibility, not a new hook
						do_action( 'wp_mail_failed', new WP_Error( 'elastic_api_error', $api_response->get_error_message(), $infinity23_elastic_mail_data ) );
						return false;
					} // if( is_wp_error( $api_response ) ) {

					$api_code = wp_remote_retrieve_response_code( $api_response );

					if( $api_code >= 200 && $api_code < 300 ) {
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- invokes WP core's own wp_mail_succeeded action for compatibility, not a new hook
						do_action( 'wp_mail_succeeded', $infinity23_elastic_mail_data );
						return true;
					} // if( $api_code >= 200 && $api_code < 300 ) {

					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- invokes WP core's own wp_mail_failed action for compatibility, not a new hook
					do_action( 'wp_mail_failed', new WP_Error( 'elastic_api_error', 'HTTP ' . intval( $api_code ) . ' - ' . wp_remote_retrieve_body( $api_response ), $infinity23_elastic_mail_data ) );
					return false;

				} // function infinity23_email_smtp_send_via_elastic_api( $atts, $api_key, $infinity23_force_from_checkbox, $infinity23_from_email, $infinity23_from_name ) {

			} // if( !function_exists(

			add_filter( 'pre_wp_mail', function( $unused_null, $atts ) use ( $infinity23_smtp_password, $infinity23_force_from_checkbox, $infinity23_from_email, $infinity23_from_name ) {
				return infinity23_email_smtp_send_via_elastic_api( $atts, $infinity23_smtp_password, $infinity23_force_from_checkbox, $infinity23_from_email, $infinity23_from_name );
			}, 10, 2 );

		} // if( $infinity23_smtp_provider === 'elastic_email' && $infinity23_smtp_elastic_connection_method === 'api'

		// ---------------------------------------
		// SMTP transport - phpmailer_init
		// ---------------------------------------

		add_action( 'phpmailer_init', function( $phpmailer ) use ( $infinity23_smtp_provider, $infinity23_smtp_host, $infinity23_smtp_port, $infinity23_smtp_encryption, $infinity23_smtp_auth_type, $infinity23_smtp_username, $infinity23_smtp_password, $infinity23_force_from_checkbox, $infinity23_from_email, $infinity23_from_name ) {

			if( $infinity23_smtp_provider === 'wordpress_default' || empty( $infinity23_smtp_host ) ) {
				return; // using the site's default mail sending - no SMTP override
			} // if( $infinity23_smtp_provider === 'wordpress_default' || empty( $infinity23_smtp_host ) ) {

			$phpmailer->isSMTP();
			$phpmailer->Host = $infinity23_smtp_host;
			$phpmailer->Port = intval( $infinity23_smtp_port );

			if( $infinity23_smtp_encryption === 'none' ) {
				$phpmailer->SMTPSecure = '';
				$phpmailer->SMTPAutoTLS = false;
			} else {
				$phpmailer->SMTPSecure = $infinity23_smtp_encryption; // 'ssl' or 'tls'
				$phpmailer->SMTPAutoTLS = ( $infinity23_smtp_encryption === 'tls' );
			} // if( $infinity23_smtp_encryption === 'none' ) {

			if( $infinity23_smtp_auth_type !== 'none' ) {
				$phpmailer->SMTPAuth = true;
				$phpmailer->Username = $infinity23_smtp_username;
				$phpmailer->Password = $infinity23_smtp_password;
				// only force AuthType for Login - leave it empty otherwise so
				// PHPMailer auto-negotiates against what the server actually
				// advertises in its EHLO response, instead of forcing PLAIN
				// which some servers (Elastic Email included) reject outright
				if( $infinity23_smtp_auth_type === 'login' ) {
					$phpmailer->AuthType = 'LOGIN';
				} // if( $infinity23_smtp_auth_type === 'login' ) {
			} else {
				$phpmailer->SMTPAuth = false;
			} // if( $infinity23_smtp_auth_type !== 'none' ) {

			// direct object override - only reached at our own hook priority,
			// so when a field is forced and registered at a very late priority,
			// this wins over any other plugin's phpmailer_init callback that
			// runs earlier and already changed From/FromName. Each field is
			// set independently so forcing only one never touches the other.
			if( in_array( $infinity23_force_from_checkbox, array( 'email', 'both' ), true ) && !empty( $infinity23_from_email ) && is_email( $infinity23_from_email ) ) {
				$phpmailer->From = $infinity23_from_email;
				$phpmailer->Sender = $infinity23_from_email;
			} // if( in_array(

			if( in_array( $infinity23_force_from_checkbox, array( 'name', 'both' ), true ) && !empty( $infinity23_from_name ) ) {
				$phpmailer->FromName = $infinity23_from_name;
			} // if( in_array(

			// the SMTP transcript records every MAIL FROM / RCPT TO line of every
			// email the site sends, so it is never tied to WP_DEBUG - turning that
			// on for an unrelated bug must not start writing recipient addresses
			// into wp-content/debug.log. Opt in with INFINITY23_EMAIL_SMTP_DEBUG
			// in wp-config.php instead, and only while actually tracing a problem.
			// The level stays at 2 on purpose - PHPMailer only hides the AUTH
			// credential lines below its own DEBUG_LOWLEVEL (4), so 3 and 4 would
			// write the base64 username and password straight into the log.
			if( defined( 'INFINITY23_EMAIL_SMTP_DEBUG' ) && INFINITY23_EMAIL_SMTP_DEBUG ) {
				$phpmailer->SMTPDebug = 2;
				$phpmailer->Debugoutput = function( $str, $level ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- only runs when INFINITY23_EMAIL_SMTP_DEBUG is on, mirrors PHPMailer's own debug output convention
					error_log( 'Email SMTP: ' . trim( $str ) );
				};
			} else {
				$phpmailer->SMTPDebug = 0;
			} // if( defined( 'INFINITY23_EMAIL_SMTP_DEBUG' ) && INFINITY23_EMAIL_SMTP_DEBUG ) {

		}, $infinity23_email_smtp_priority );

	} // if( !empty( $infinity23_code_is_active ) ) {

?>
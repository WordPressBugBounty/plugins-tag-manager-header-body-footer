<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name      = 'email_smtp_activate_code';
	$infinity23_settings_array_db_name  = 'email_smtp_settings';
	$infinity23_module_display_name = 'Email SMTP';

	// ===========================================================================
	// fixed connection details per provider - used by admin-output.php to know
	// which fields a provider needs, and by back-end.php to build the actual
	// SMTP connection without asking the user to re-enter these values
	// ===========================================================================

	$infinity23_smtp_provider_config = array(
		'gmail' => array( 'host' => 'smtp.gmail.com', 'port' => 587, 'encryption' => 'tls', 'auth_type' => 'plain' ),
		'amazon_ses' => array( 'host' => '', 'port' => 465, 'encryption' => 'ssl', 'auth_type' => 'login' ),
		// forced to 'login' - leaving this on auto-negotiate lets PHPMailer pick
		// CRAM-MD5 (Elastic Email advertises it but rejects it with "535 Access
		// denied" for API-key-style credentials), forcing LOGIN avoids that entirely
		'elastic_email' => array( 'host' => 'smtp.elasticemail.com', 'port' => 2525, 'encryption' => 'tls', 'auth_type' => 'login' ),
		'sendgrid' => array( 'host' => 'smtp.sendgrid.net', 'port' => 587, 'encryption' => 'tls', 'auth_type' => 'plain' ),
		// host is intentionally blank here (unlike the other hardcoded-host providers) -
		// Mailgun issues a different SMTP host per account region (US vs EU), so the
		// actual host is read from the user's own saved smtp_mailgun_host field instead
		'mailgun' => array( 'host' => '', 'port' => 587, 'encryption' => 'tls', 'auth_type' => 'plain' ),
		'maileroo' => array( 'host' => 'smtp.maileroo.com', 'port' => 587, 'encryption' => 'tls', 'auth_type' => 'plain' ),
	);

	// ===========================================================================
	// strip characters that don't print but can still get copy-pasted in from
	// a provider's dashboard (non-breaking space, zero-width space, BOM) -
	// these silently break SMTP authentication since they're invisible in the
	// input field but change the actual credential string. Kept here because
	// this is the one include both admin-output.php and back-end.php share -
	// back-end.php's AJAX handlers run through wp-admin/admin-ajax.php, which
	// never loads admin-output.php at all
	// ===========================================================================

	if( !function_exists( 'infinity23_email_smtp_strip_invisible_chars' ) ) {

		function infinity23_email_smtp_strip_invisible_chars( $value ) {

			$value = str_replace( array( "\xC2\xA0", "\xE2\x80\x8B", "\xEF\xBB\xBF" ), '', $value );
			$value = trim( $value );

			return $value;

		} // function infinity23_email_smtp_strip_invisible_chars( $value ) {

	} // if( !function_exists( 'infinity23_email_smtp_strip_invisible_chars' ) ) {

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['email_smtp']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['email_smtp']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['email_smtp']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['email_smtp']['admin_page_slug'] = 'essential-email-smtp';
	$infinity23_essential_modules_array['email_smtp']['hide_menu_field'] = 'email_smtp_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['email_smtp']['include_exclude_data_key'] = 'email_smtp';
	$infinity23_essential_modules_array['email_smtp']['include_exclude_title'] = 'Email SMTP';
	$infinity23_essential_modules_array['email_smtp']['include_exclude_description'] = 'Routes WordPress emails through Gmail, Amazon SES, Elastic Email or custom SMTP.';
	$infinity23_essential_modules_array['email_smtp']['include_exclude_icon_path'] = 'include-exclude/email-smtp.png';

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'smtp_provider' => array(
			'name'   => 'smtp_provider',
			'value'  => 'wordpress_default',
			'type'   => 'text',
			'access' => 'free',
		),

		'smtp_custom_host' => array(
			'name'   => 'smtp_custom_host',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_custom_port' => array(
			'name'   => 'smtp_custom_port',
			'value'  => 587,
			'type'   => 'intval',
			'access' => 'free',
		),
		'smtp_custom_encryption' => array(
			'name'   => 'smtp_custom_encryption',
			'value'  => 'tls',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_custom_auth_type' => array(
			'name'   => 'smtp_custom_auth_type',
			'value'  => 'plain',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_custom_username' => array(
			'name'   => 'smtp_custom_username',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_custom_password' => array(
			'name'   => 'smtp_custom_password',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),

		'smtp_gmail_username' => array(
			'name'   => 'smtp_gmail_username',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_gmail_password' => array(
			'name'   => 'smtp_gmail_password',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),

		'smtp_ses_host' => array(
			'name'   => 'smtp_ses_host',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_ses_port' => array(
			'name'   => 'smtp_ses_port',
			'value'  => 465,
			'type'   => 'intval',
			'access' => 'free',
		),
		'smtp_ses_encryption' => array(
			'name'   => 'smtp_ses_encryption',
			'value'  => 'ssl',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_ses_username' => array(
			'name'   => 'smtp_ses_username',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_ses_password' => array(
			'name'   => 'smtp_ses_password',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),

		'smtp_elastic_connection_method' => array(
			'name'   => 'smtp_elastic_connection_method',
			'value'  => 'api',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_elastic_username' => array(
			'name'   => 'smtp_elastic_username',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_elastic_api_key' => array(
			'name'   => 'smtp_elastic_api_key',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),

		'smtp_sendgrid_api_key' => array(
			'name'   => 'smtp_sendgrid_api_key',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),

		'smtp_mailgun_host' => array(
			'name'   => 'smtp_mailgun_host',
			'value'  => 'smtp.mailgun.org',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_mailgun_username' => array(
			'name'   => 'smtp_mailgun_username',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_mailgun_password' => array(
			'name'   => 'smtp_mailgun_password',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),

		'smtp_maileroo_username' => array(
			'name'   => 'smtp_maileroo_username',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'smtp_maileroo_password' => array(
			'name'   => 'smtp_maileroo_password',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),

		'from_email' => array(
			'name'   => 'from_email',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'from_name' => array(
			'name'   => 'from_name',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'force_from_checkbox' => array(
			'name'   => 'force_from_checkbox',
			'value'  => 'none',
			'type'   => 'text',
			'access' => 'free',
		),

		'email_log_enabled' => array(
			'name'      => 'email_log_enabled',
			'value'     => 0,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'email_log_save_message' => array(
			'name'      => 'email_log_save_message',
			'value'     => 1,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'email_log_retention_days' => array(
			'name'      => 'email_log_retention_days',
			'value'     => 30,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'email_log_count_limit' => array(
			'name'      => 'email_log_count_limit',
			'value'     => 50000,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'email_log_per_page' => array(
			'name'      => 'email_log_per_page',
			'value'     => 50,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),

		'email_smtp_hide_menu' => array(
			'name'   => 'email_smtp_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

?>
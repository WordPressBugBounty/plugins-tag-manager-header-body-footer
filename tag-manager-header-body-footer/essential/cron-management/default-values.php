<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'enable_cron_actions' => array(
			'name'      => 'enable_cron_actions',
			'value'     => 1,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'cron_log_enabled' => array(
			'name'      => 'cron_log_enabled',
			'value'     => 0,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'cron_log_retention_days' => array(
			'name'      => 'cron_log_retention_days',
			'value'     => 7,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'external_cron_enabled' => array(
			'name'      => 'external_cron_enabled',
			'value'     => 0,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'disable_wp_built_in_cron' => array(
			'name'      => 'disable_wp_built_in_cron',
			'value'     => 'default',
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'external_cron_password' => array(
			'name'      => 'external_cron_password',
			'value'     => '',
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'cron_management_hide_menu' => array(
			'name'   => 'cron_management_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

?>
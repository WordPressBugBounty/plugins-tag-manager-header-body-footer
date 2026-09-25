<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'disable_plugin_auto_updates' => array(
			'name'   => 'disable_plugin_auto_updates',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'disable_theme_auto_updates' => array(
			'name'   => 'disable_theme_auto_updates',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'core_updates_mode' => array(
			'name'   => 'core_updates_mode',
			'value'  => 'all',
			'type'   => 'text',
			'access' => 'free',
		),
		// internal only - the old checkbox, kept so a site that saved it before
		// this became a select keeps its choice until the page is saved again
		'disable_core_auto_updates' => array(
			'name'   => 'disable_core_auto_updates',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'hide_core_update_notice' => array(
			'name'   => 'hide_core_update_notice',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'force_block_updates_mode' => array(
			'name'      => 'force_block_updates_mode',
			'value'     => array( 'pro' => 'block', 'free' => 'ignore' ),
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'force_block_updates_notice' => array(
			'name'      => 'force_block_updates_notice',
			'value'     => array( 'pro' => 1, 'free' => 0 ),
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'force_block_plugin_updates' => array(
			'name'      => 'force_block_plugin_updates',
			'value'     => '',
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'force_block_theme_updates' => array(
			'name'      => 'force_block_theme_updates',
			'value'     => '',
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'update_manager_hide_menu' => array(
			'name'   => 'update_manager_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

?>
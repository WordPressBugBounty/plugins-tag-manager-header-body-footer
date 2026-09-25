<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'show_on_pages' => array(
			'name'   => 'show_on_pages',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'show_on_posts' => array(
			'name'   => 'show_on_posts',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'show_on_dashboard' => array(
			'name'   => 'show_on_dashboard',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'show_on_admin_table' => array(
			'name'   => 'show_on_admin_table',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'show_on_custom_post_types' => array(
			'name'   => 'show_on_custom_post_types',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'disabled_custom_post_types' => array(
			'name'   => 'disabled_custom_post_types',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'allow_multiple_notes' => array(
			'name'   => 'allow_multiple_notes',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'show_note_dates' => array(
			'name'   => 'show_note_dates',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'adjustable_textarea_height' => array(
			'name'   => 'adjustable_textarea_height',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'dashboard_adjustable_textarea_height' => array(
			'name'   => 'dashboard_adjustable_textarea_height',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'allow_view_all_notes' => array(
			'name'      => 'allow_view_all_notes',
			'value'     => 1,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'all_notes_allow_edit' => array(
			'name'      => 'all_notes_allow_edit',
			'value'     => 1,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'all_notes_allow_remove' => array(
			'name'      => 'all_notes_allow_remove',
			'value'     => 1,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'all_notes_table_direction' => array(
			'name'      => 'all_notes_table_direction',
			'value'     => is_rtl() ? 'rtl' : 'ltr',
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'notes_hide_menu' => array(
			'name'   => 'notes_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

?>
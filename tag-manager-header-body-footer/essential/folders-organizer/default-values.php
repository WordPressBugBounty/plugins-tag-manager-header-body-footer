<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// enabled_content_types holds only the types the admin saved (1 = on, 0 = off)
	// a type with no saved value yet (including a custom post type added later)
	// uses enabled_content_types_default - 1 means on, 0 means off
	$infinity23_defaults = array(
		'enabled_content_types' => array(
			'name'   => 'enabled_content_types',
			'value'  => array(),
			'type'   => 'array',
			'access' => 'free',
		),
		'enabled_content_types_default' => array(
			'name'   => 'enabled_content_types_default',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'custom_post_types_active' => array(
			'name'   => 'custom_post_types_active',
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
		'sidebar_position' => array(
			'name'   => 'sidebar_position',
			'value'  => 'left',
			'type'   => 'text',
			'access' => 'free',
		),
		'sidebar_width' => array(
			'name'   => 'sidebar_width',
			'value'  => '200px',
			'type'   => 'text',
			'access' => 'free',
		),
		'folders_organizer_hide_menu' => array(
			'name'   => 'folders_organizer_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'folders_limit_per_type' => array(
			'name'      => 'folders_limit_per_type',
			'value'     => array( 'pro' => 'unlimited', 'free' => '3' ),
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
	);

?>
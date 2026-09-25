<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'chat_number' => array(
			'name'   => 'chat_number',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'chat_message' => array(
			'name'   => 'chat_message',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'open_new_tab' => array(
			'name'   => 'open_new_tab',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'button_type' => array(
			'name'   => 'button_type',
			'value'  => 'button1',
			'type'   => 'text',
			'access' => 'free',
		),
		'icon_image_url' => array(
			'name'   => 'icon_image_url',
			'value'  => infinity23_plugin_url( 'images/whatsapp-button/whatsapp-button1.svg' ),
			'type'   => 'url',
			'access' => 'free',
		),
		'custom_icon_image_url' => array(
			'name'   => 'custom_icon_image_url',
			'value'  => '',
			'type'   => 'url',
			'access' => 'free',
		),
		'button_width' => array(
			'name'   => 'button_width',
			'value'  => 80,
			'type'   => 'intval',
			'access' => 'free',
		),
		'button_height' => array(
			'name'   => 'button_height',
			'value'  => 80,
			'type'   => 'intval',
			'access' => 'free',
		),
		'button_position' => array(
			'name'   => 'button_position',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'exclude_ids' => array(
			'name'   => 'exclude_ids',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'exclude_option' => array(
			'name'   => 'exclude_option',
			'value'  => 'none',
			'type'   => 'text',
			'access' => 'free',
		),
		'custom_css' => array(
			'name'      => 'custom_css',
			'value'     => '',
			'type'      => 'css',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'custom_css_active' => array(
			'name'      => 'custom_css_active',
			'value'     => 1,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'whatsapp_button_hide_menu' => array(
			'name'   => 'whatsapp_button_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

	// ----------------------------------------
	// default position struct used only when no button_position was saved yet
	// ----------------------------------------

	$infinity23_default_button_position = array(
		'hide_desktop' => 0,
		'h_position' => 'right',
		'h_spacing' => '30px',
		'v_position' => 'bottom',
		'v_spacing' => '30px',
		'z_index_desktop' => 9999,
		'hide_mobile' => 0,
		'mobile_width' => 1025,
		'mobile_mode' => 'inherit',
		'mobile_h_position' => 'right',
		'mobile_h_spacing' => '30px',
		'mobile_v_position' => 'bottom',
		'mobile_v_spacing' => '30px',
	);

?>
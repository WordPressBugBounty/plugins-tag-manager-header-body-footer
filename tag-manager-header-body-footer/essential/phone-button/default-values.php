<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'phone_number' => array(
			'name'   => 'phone_number',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'phone_number_new_tab' => array(
			'name'   => 'phone_number_new_tab',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'icon_image_url' => array(
			'name'   => 'icon_image_url',
			'value'  => infinity23_plugin_url( 'images/phone-button/phone-icon.svg' ),
			'type'   => 'url',
			'access' => 'free',
		),
		'simple_image_url' => array(
			'name'   => 'simple_image_url',
			'value'  => infinity23_plugin_url( 'images/phone-button/phone-button.svg' ),
			'type'   => 'url',
			'access' => 'free',
		),
		'button_display_type' => array(
			'name'   => 'button_display_type',
			'value'  => 'icon',
			'type'   => 'text',
			'access' => 'free',
		),
		'background' => array(
			'name'   => 'background',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'button_width' => array(
			'name'   => 'button_width',
			'value'  => 70,
			'type'   => 'intval',
			'access' => 'free',
		),
		'button_height' => array(
			'name'   => 'button_height',
			'value'  => 70,
			'type'   => 'intval',
			'access' => 'free',
		),
		'icon_width' => array(
			'name'   => 'icon_width',
			'value'  => 40,
			'type'   => 'intval',
			'access' => 'free',
		),
		'icon_height' => array(
			'name'   => 'icon_height',
			'value'  => 40,
			'type'   => 'intval',
			'access' => 'free',
		),
		'image_width' => array(
			'name'   => 'image_width',
			'value'  => 80,
			'type'   => 'intval',
			'access' => 'free',
		),
		'image_height' => array(
			'name'   => 'image_height',
			'value'  => 80,
			'type'   => 'intval',
			'access' => 'free',
		),
		'button_border' => array(
			'name'   => 'button_border',
			'value'  => '4px solid #fff',
			'type'   => 'text',
			'access' => 'free',
		),
		'border_radius' => array(
			'name'   => 'border_radius',
			'value'  => '50%',
			'type'   => 'text',
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
		'phone_button_hide_menu' => array(
			'name'   => 'phone_button_hide_menu',
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

	// ----------------------------------------
	// default border struct used only when no button_border was saved yet
	// (matches this module's original default before the shared border field existed)
	// ----------------------------------------

	$infinity23_default_button_border = array(
		'style' => 'solid',
		'color' => '#fff',
		'unit' => 'px',
		'top' => '4',
		'right' => '4',
		'bottom' => '4',
		'left' => '4',
		'linked' => true,
	);

	// ----------------------------------------
	// default background struct used only when no background was saved yet
	// (matches this module's original default gradient)
	// ----------------------------------------

	$infinity23_default_background = array(
		'type' => 'gradient',
		'solid_color' => '#00bc3c',
		'gradient_color_1' => '#00bc3c',
		'gradient_color_2' => '#008d32',
		'gradient_angle' => '180',
		'custom_css' => '',
	);

	// ----------------------------------------
	// default border radius struct used only when no border_radius was saved yet
	// (matches this module's original default before the shared border radius field existed)
	// ----------------------------------------

	$infinity23_default_border_radius = array(
		'unit' => '%',
		'top_left' => '50',
		'top_right' => '50',
		'bottom_right' => '50',
		'bottom_left' => '50',
		'linked' => true,
	);

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'icon_image_url' => array(
			'name'   => 'icon_image_url',
			'value'  => infinity23_plugin_url( 'images/back-to-top/back-to-top-icon.svg' ),
			'type'   => 'url',
			'access' => 'free',
		),
		'simple_image_url' => array(
			'name'   => 'simple_image_url',
			'value'  => infinity23_plugin_url( 'images/back-to-top/back-to-top-button.svg' ),
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
			'value'  => 45,
			'type'   => 'intval',
			'access' => 'free',
		),
		'button_height' => array(
			'name'   => 'button_height',
			'value'  => 45,
			'type'   => 'intval',
			'access' => 'free',
		),
		'icon_width' => array(
			'name'   => 'icon_width',
			'value'  => 20,
			'type'   => 'intval',
			'access' => 'free',
		),
		'icon_height' => array(
			'name'   => 'icon_height',
			'value'  => 20,
			'type'   => 'intval',
			'access' => 'free',
		),
		'image_width' => array(
			'name'   => 'image_width',
			'value'  => 50,
			'type'   => 'intval',
			'access' => 'free',
		),
		'image_height' => array(
			'name'   => 'image_height',
			'value'  => 50,
			'type'   => 'intval',
			'access' => 'free',
		),
		'button_border' => array(
			'name'   => 'button_border',
			'value'  => '2px solid #fff',
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
		'button_visibility' => array(
			'name'   => 'button_visibility',
			'value'  => 'on_scroll',
			'type'   => 'text',
			'access' => 'free',
		),
		'scroll_offset' => array(
			'name'   => 'scroll_offset',
			'value'  => 300,
			'type'   => 'intval',
			'access' => 'free',
		),
		'mobile_scroll_offset' => array(
			'name'   => 'mobile_scroll_offset',
			'value'  => 300,
			'type'   => 'intval',
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
		'back_to_top_hide_menu' => array(
			'name'   => 'back_to_top_hide_menu',
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
	// default background struct used only when no background was saved yet
	// (matches this module's original solid color default)
	// ----------------------------------------

	$infinity23_default_background = array(
		'type' => 'solid',
		'solid_color' => '#09547c',
		'gradient_color_1' => '#09547c',
		'gradient_color_2' => '#09547c',
		'gradient_angle' => '180',
		'custom_css' => '',
	);

	// ----------------------------------------
	// default border struct used only when no button_border was saved yet
	// (matches this module's original default before the shared border field existed)
	// ----------------------------------------

	$infinity23_default_button_border = array(
		'style' => 'solid',
		'color' => '#fff',
		'unit' => 'px',
		'top' => '2',
		'right' => '2',
		'bottom' => '2',
		'left' => '2',
		'linked' => true,
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
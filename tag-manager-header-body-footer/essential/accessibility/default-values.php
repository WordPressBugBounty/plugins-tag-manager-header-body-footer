<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'icon_image_url' => array(
			'name'   => 'icon_image_url',
			'value'  => infinity23_plugin_url( 'images/accessibility/accessibility-icon.svg' ),
			'type'   => 'url',
			'access' => 'free',
		),
		'simple_image_url' => array(
			'name'   => 'simple_image_url',
			'value'  => infinity23_plugin_url( 'images/accessibility/accessibility-button.svg' ),
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
			'value'  => 30,
			'type'   => 'intval',
			'access' => 'free',
		),
		'icon_height' => array(
			'name'   => 'icon_height',
			'value'  => 30,
			'type'   => 'intval',
			'access' => 'free',
		),
		'image_width' => array(
			'name'   => 'image_width',
			'value'  => 45,
			'type'   => 'intval',
			'access' => 'free',
		),
		'image_height' => array(
			'name'   => 'image_height',
			'value'  => 45,
			'type'   => 'intval',
			'access' => 'free',
		),
		'button_border' => array(
			'name'   => 'button_border',
			'value'  => 'none',
			'type'   => 'text',
			'access' => 'free',
		),
		'border_radius' => array(
			'name'   => 'border_radius',
			'value'  => '0px',
			'type'   => 'text',
			'access' => 'free',
		),
		'button_movement' => array(
			'name'   => 'button_movement',
			'value'  => 'fixed',
			'type'   => 'text',
			'access' => 'free',
		),
		'accessibility_page_url' => array(
			'name'   => 'accessibility_page_url',
			'value'  => '',
			'type'   => 'url',
			'access' => 'free',
		),
		'accessibility_page_url_new_tab' => array(
			'name'   => 'accessibility_page_url_new_tab',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'accessibility_background_color' => array(
			'name'   => 'accessibility_background_color',
			'value'  => '#09547c',
			'type'   => 'text',
			'access' => 'free',
		),
		'save_user_settings_checkbox' => array(
			'name'      => 'save_user_settings_checkbox',
			'value'     => 0,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'save_user_settings_minutes' => array(
			'name'      => 'save_user_settings_minutes',
			'value'     => 60,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'button_position' => array(
			'name'   => 'button_position',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'exclude_admin_bar_checkbox' => array(
			'name'   => 'exclude_admin_bar_checkbox',
			'value'  => 1,
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
		'translate_title' => array(
			'name'      => 'translate_title',
			'value'     => __( 'Accessibility', 'tag-manager-header-body-footer' ),
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'translate_increase_font' => array(
			'name'      => 'translate_increase_font',
			'value'     => __( 'Increase Font Size', 'tag-manager-header-body-footer' ),
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'translate_decrease_font' => array(
			'name'      => 'translate_decrease_font',
			'value'     => __( 'Decrease Font Size', 'tag-manager-header-body-footer' ),
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'translate_light_contrast' => array(
			'name'      => 'translate_light_contrast',
			'value'     => __( 'Light contrast', 'tag-manager-header-body-footer' ),
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'translate_high_contrast' => array(
			'name'      => 'translate_high_contrast',
			'value'     => __( 'High contrast', 'tag-manager-header-body-footer' ),
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'translate_highlight_links' => array(
			'name'      => 'translate_highlight_links',
			'value'     => __( 'Highlight Links', 'tag-manager-header-body-footer' ),
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'translate_reset' => array(
			'name'      => 'translate_reset',
			'value'     => __( 'Reset Settings', 'tag-manager-header-body-footer' ),
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'translate_accessibility_statement' => array(
			'name'      => 'translate_accessibility_statement',
			'value'     => __( 'Accessibility Statement', 'tag-manager-header-body-footer' ),
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
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
		'accessibility_hide_menu' => array(
			'name'   => 'accessibility_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

	// ----------------------------------------
	// default position struct used only when no button_position was saved yet
	// (matches this module's original top-left position from before the
	// shared button-position field existed)
	// ----------------------------------------

	$infinity23_default_button_position = array(
		'hide_desktop' => 0,
		'h_position' => 'left',
		'h_spacing' => '0px',
		'v_position' => 'top',
		'v_spacing' => '50px',
		'z_index_desktop' => 99999,
		'hide_mobile' => 0,
		'mobile_width' => 1025,
		'mobile_mode' => 'inherit',
		'mobile_h_position' => 'left',
		'mobile_h_spacing' => '0px',
		'mobile_v_position' => 'top',
		'mobile_v_spacing' => '0px',
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
		'style' => 'none',
		'color' => '#000000',
		'unit' => 'px',
		'top' => '0',
		'right' => '0',
		'bottom' => '0',
		'left' => '0',
		'linked' => true,
	);

	// ----------------------------------------
	// default border radius struct used only when no border_radius was saved yet
	// (matches this module's original default before the shared border radius field existed)
	// ----------------------------------------

	$infinity23_default_border_radius = array(
		'unit' => 'px',
		'top_left' => '0',
		'top_right' => '0',
		'bottom_right' => '0',
		'bottom_left' => '0',
		'linked' => true,
	);

?>
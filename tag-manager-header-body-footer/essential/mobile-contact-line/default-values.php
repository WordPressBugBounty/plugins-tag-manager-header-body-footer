<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'phone_display_checkbox' => array(
			'name'   => 'phone_display_checkbox',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'phone_button_href' => array(
			'name'   => 'phone_button_href',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'phone_button_text' => array(
			'name'   => 'phone_button_text',
			'value'  => __( 'Call Us Now', 'tag-manager-header-body-footer' ),
			'type'   => 'text',
			'access' => 'free',
		),
		'phone_background_color' => array(
			'name'   => 'phone_background_color',
			'value'  => '#008cd6',
			'type'   => 'text',
			'access' => 'free',
		),
		'phone_button_width' => array(
			'name'   => 'phone_button_width',
			'value'  => '50%',
			'type'   => 'text',
			'access' => 'free',
		),
		'phone_button_position' => array(
			'name'   => 'phone_button_position',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),

		'email_display_checkbox' => array(
			'name'   => 'email_display_checkbox',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'email_button_href' => array(
			'name'   => 'email_button_href',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'custom_contact_page_link' => array(
			'name'   => 'custom_contact_page_link',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'email_button_text' => array(
			'name'   => 'email_button_text',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'email_background_color' => array(
			'name'   => 'email_background_color',
			'value'  => '#dca100',
			'type'   => 'text',
			'access' => 'free',
		),
		'email_button_width' => array(
			'name'   => 'email_button_width',
			'value'  => '25%',
			'type'   => 'text',
			'access' => 'free',
		),
		'email_button_position' => array(
			'name'   => 'email_button_position',
			'value'  => 2,
			'type'   => 'intval',
			'access' => 'free',
		),

		'whatsapp_display_checkbox' => array(
			'name'   => 'whatsapp_display_checkbox',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'whatsapp_button_href' => array(
			'name'   => 'whatsapp_button_href',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'whatsapp_url_message' => array(
			'name'   => 'whatsapp_url_message',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'whatsapp_button_text' => array(
			'name'   => 'whatsapp_button_text',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'whatsapp_background_color' => array(
			'name'   => 'whatsapp_background_color',
			'value'  => '#09547c',
			'type'   => 'text',
			'access' => 'free',
		),
		'whatsapp_button_width' => array(
			'name'   => 'whatsapp_button_width',
			'value'  => '25%',
			'type'   => 'text',
			'access' => 'free',
		),
		'whatsapp_button_position' => array(
			'name'   => 'whatsapp_button_position',
			'value'  => 3,
			'type'   => 'intval',
			'access' => 'free',
		),

		'chat_display_checkbox' => array(
			'name'   => 'chat_display_checkbox',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'chat_button_href' => array(
			'name'   => 'chat_button_href',
			'value'  => '',
			'type'   => 'url',
			'access' => 'free',
		),
		'chat_button_text' => array(
			'name'   => 'chat_button_text',
			'value'  => __( 'Live Chat', 'tag-manager-header-body-footer' ),
			'type'   => 'text',
			'access' => 'free',
		),
		'chat_background_color' => array(
			'name'   => 'chat_background_color',
			'value'  => '#008cd6',
			'type'   => 'text',
			'access' => 'free',
		),
		'chat_button_width' => array(
			'name'   => 'chat_button_width',
			'value'  => '25%',
			'type'   => 'text',
			'access' => 'free',
		),
		'chat_button_position' => array(
			'name'   => 'chat_button_position',
			'value'  => 4,
			'type'   => 'intval',
			'access' => 'free',
		),

		'mobile_width' => array(
			'name'   => 'mobile_width',
			'value'  => 1025,
			'type'   => 'intval',
			'access' => 'free',
		),
		'hide_on_mobile_checkbox' => array(
			'name'   => 'hide_on_mobile_checkbox',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'hide_on_desktop_checkbox' => array(
			'name'   => 'hide_on_desktop_checkbox',
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
		'mobile_contact_line_hide_menu' => array(
			'name'   => 'mobile_contact_line_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

?>
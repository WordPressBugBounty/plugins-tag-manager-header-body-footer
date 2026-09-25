<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'head_tags' => array(
			'name'   => 'head_tags',
			'value'  => array(),
			'type'   => 'array',
			'access' => 'free',
		),
		'body_tags' => array(
			'name'   => 'body_tags',
			'value'  => array(),
			'type'   => 'array',
			'access' => 'free',
		),
		'footer_tags' => array(
			'name'   => 'footer_tags',
			'value'  => array(),
			'type'   => 'array',
			'access' => 'free',
		),
		'google_analytics_id' => array(
			'name'   => 'google_analytics_id',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'facebook_pixel_id' => array(
			'name'   => 'facebook_pixel_id',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'google_tag_manager_id' => array(
			'name'   => 'google_tag_manager_id',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'yandex_metrika_id' => array(
			'name'   => 'yandex_metrika_id',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'custom_lazy_load_js' => array(
			'name'   => 'custom_lazy_load_js',
			'value'  => array(),
			'type'   => 'array',
			'access' => 'free',
		),
		'lazy_load_time' => array(
			'name'   => 'lazy_load_time',
			'value'  => 6000,
			'type'   => 'intval',
			'access' => 'free',
		),
		'remove_custom_lazy_load_js_on_elementor' => array(
			'name'   => 'remove_custom_lazy_load_js_on_elementor',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'exclude_users' => array(
			'name'   => 'exclude_users',
			'value'  => 'none',
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
		'storage_notes' => array(
			'name'   => 'storage_notes',
			'value'  => array(),
			'type'   => 'array',
			'access' => 'free',
		),
		'wp_body_open' => array(
			'name'   => 'wp_body_open',
			'value'  => 'wp_body_open',
			'type'   => 'text',
			'access' => 'free',
		),
		'adjustable_textarea_height' => array(
			'name'   => 'adjustable_textarea_height',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'tag_manager_hide_menu' => array(
			'name'   => 'tag_manager_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

?>
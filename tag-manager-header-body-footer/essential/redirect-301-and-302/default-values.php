<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'redirect_col_redirect_type' => array(
			'name'   => 'redirect_col_redirect_type',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'redirect_col_request_url' => array(
			'name'   => 'redirect_col_request_url',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'redirect_col_destination_url' => array(
			'name'   => 'redirect_col_destination_url',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'redirect_col_redirect_query' => array(
			'name'   => 'redirect_col_redirect_query',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'redirect_col_get_parameters' => array(
			'name'   => 'redirect_col_get_parameters',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'redirect_col_notes' => array(
			'name'   => 'redirect_col_notes',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'redirect_col_advertising_platform' => array(
			'name'   => 'redirect_col_advertising_platform',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'redirect_col_redirects_amount' => array(
			'name'   => 'redirect_col_redirects_amount',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'redirect_advance_view_mode' => array(
			'name'      => 'redirect_advance_view_mode',
			'value'     => 'compact',
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'redirect_allow_bulk_edit' => array(
			'name'      => 'redirect_allow_bulk_edit',
			'value'     => array( 'pro' => 1, 'free' => 0 ),
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'redirect_shortener_enabled' => array(
			'name'      => 'redirect_shortener_enabled',
			'value'     => array( 'pro' => 1, 'free' => 0 ),
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'redirect_shortener_code_length' => array(
			'name'      => 'redirect_shortener_code_length',
			'value'     => 7,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'redirect_shortener_prefix' => array(
			'name'      => 'redirect_shortener_prefix',
			'value'     => '',
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'redirect_log_enabled' => array(
			'name'      => 'redirect_log_enabled',
			'value'     => 0,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'redirect_log_retention_days' => array(
			'name'      => 'redirect_log_retention_days',
			'value'     => 365,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'redirect_log_max_count' => array(
			'name'      => 'redirect_log_max_count',
			'value'     => 50000,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'redirect_log_per_page' => array(
			'name'      => 'redirect_log_per_page',
			'value'     => 50,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'redirect_301_and_302_hide_menu' => array(
			'name'   => 'redirect_301_and_302_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

?>
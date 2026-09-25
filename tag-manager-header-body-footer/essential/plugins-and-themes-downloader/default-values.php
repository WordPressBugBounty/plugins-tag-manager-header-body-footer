<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'enable_plugins_download' => array(
			'name'      => 'enable_plugins_download',
			'value'     => array( 'pro' => 1, 'free' => 0 ),
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'enable_themes_download' => array(
			'name'      => 'enable_themes_download',
			'value'     => array( 'pro' => 1, 'free' => 0 ),
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'include_version_in_filename' => array(
			'name'      => 'include_version_in_filename',
			'value'     => array( 'pro' => 1, 'free' => 0 ),
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'zip_time_limit' => array(
			'name'      => 'zip_time_limit',
			'value'     => 60,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'zip_storage_location' => array(
			'name'   => 'zip_storage_location',
			'value'  => 'temp',
			'type'   => 'text',
			'access' => 'free',
		),
		'plugins_and_themes_downloader_hide_menu' => array(
			'name'   => 'plugins_and_themes_downloader_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'force_ssl_checkbox' => array(
			'name'   => 'force_ssl_checkbox',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'force_ssl_detection_mode' => array(
			'name'   => 'force_ssl_detection_mode',
			'value'  => 'extended',
			'type'   => 'text',
			'access' => 'free',
		),
		'force_ssl_localhost_mode' => array(
			'name'   => 'force_ssl_localhost_mode',
			'value'  => 'exclude',
			'type'   => 'text',
			'access' => 'free',
		),
		'fix_mixed_content_checkbox' => array(
			'name'   => 'fix_mixed_content_checkbox',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'ssl_and_security_hide_menu' => array(
			'name'   => 'ssl_and_security_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

?>
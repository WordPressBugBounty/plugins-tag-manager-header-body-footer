<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_defaults = array(
		'save_scope' => array(
			'name'      => 'save_scope',
			'value'     => array( 'pro' => 1, 'free' => 0 ),
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'admin_bar_label_format' => array(
			'name'      => 'admin_bar_label_format',
			'value'     => 'text_only',
			'type'      => 'text',
			'access'    => 'pro',
			'downgrade' => 'reset',
		),
		'rtl_ltr_direction_change_hide_menu' => array(
			'name'   => 'rtl_ltr_direction_change_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

?>
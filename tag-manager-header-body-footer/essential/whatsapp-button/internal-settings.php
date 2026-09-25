<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'whatsapp_button_activate_code';
	$infinity23_settings_array_db_name = 'whatsapp_button_settings';
	$infinity23_module_display_name = 'Whatsapp Button';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['whatsapp_button']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['whatsapp_button']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['whatsapp_button']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['whatsapp_button']['admin_page_slug'] = 'essential-whatsapp-button';
	$infinity23_essential_modules_array['whatsapp_button']['hide_menu_field'] = 'whatsapp_button_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['whatsapp_button']['include_exclude_data_key'] = 'whatsapp_button';
	$infinity23_essential_modules_array['whatsapp_button']['include_exclude_title'] = 'Whatsapp Button';
	$infinity23_essential_modules_array['whatsapp_button']['include_exclude_description'] = 'Adds a fixed floating Whatsapp button that lets visitors message you directly.';
	$infinity23_essential_modules_array['whatsapp_button']['include_exclude_icon_path'] = 'include-exclude/whatsapp-button.png';

?>
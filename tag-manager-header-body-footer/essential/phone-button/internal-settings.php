<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'phone_button_activate_code';
	$infinity23_settings_array_db_name = 'phone_button_settings';
	$infinity23_module_display_name = 'Phone Button';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['phone_button']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['phone_button']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['phone_button']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['phone_button']['admin_page_slug'] = 'essential-phone-button';
	$infinity23_essential_modules_array['phone_button']['hide_menu_field'] = 'phone_button_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['phone_button']['include_exclude_data_key'] = 'phone_button';
	$infinity23_essential_modules_array['phone_button']['include_exclude_title'] = 'Phone Button';
	$infinity23_essential_modules_array['phone_button']['include_exclude_description'] = 'Adds a fixed floating phone button that lets visitors call you directly.';
	$infinity23_essential_modules_array['phone_button']['include_exclude_icon_path'] = 'include-exclude/phone-button.png';

?>
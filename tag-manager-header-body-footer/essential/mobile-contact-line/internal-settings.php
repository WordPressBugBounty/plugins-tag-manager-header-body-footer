<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'mobile_contact_line_activate_code';
	$infinity23_settings_array_db_name = 'mobile_contact_line_settings';
	$infinity23_module_display_name = 'Mobile Contact Line';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['mobile_contact_line']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['mobile_contact_line']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['mobile_contact_line']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['mobile_contact_line']['admin_page_slug'] = 'essential-mobile-contact-line';
	$infinity23_essential_modules_array['mobile_contact_line']['hide_menu_field'] = 'mobile_contact_line_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['mobile_contact_line']['include_exclude_data_key'] = 'mobile_contact_line';
	$infinity23_essential_modules_array['mobile_contact_line']['include_exclude_title'] = 'Mobile Contact Line';
	$infinity23_essential_modules_array['mobile_contact_line']['include_exclude_description'] = 'Adds a fixed mobile bottom bar with phone, email, whatsapp, chat and skype buttons.';
	$infinity23_essential_modules_array['mobile_contact_line']['include_exclude_icon_path'] = 'include-exclude/mobile-contact-line.png';

?>
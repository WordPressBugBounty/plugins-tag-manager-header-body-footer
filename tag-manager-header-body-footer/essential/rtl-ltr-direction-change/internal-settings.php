<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name     = 'rtl_ltr_direction_change_activate_code';
	$infinity23_settings_array_db_name = 'rtl_ltr_direction_change_settings';
	$infinity23_module_display_name = 'LTR/RTL Change';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['rtl_ltr_direction_change']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['rtl_ltr_direction_change']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['rtl_ltr_direction_change']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['rtl_ltr_direction_change']['admin_page_slug'] = 'essential-rtl-ltr-direction-change';
	$infinity23_essential_modules_array['rtl_ltr_direction_change']['hide_menu_field'] = 'rtl_ltr_direction_change_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['rtl_ltr_direction_change']['include_exclude_data_key'] = 'rtl_ltr_direction_change';
	$infinity23_essential_modules_array['rtl_ltr_direction_change']['include_exclude_title'] = 'LTR/RTL Change';
	$infinity23_essential_modules_array['rtl_ltr_direction_change']['include_exclude_description'] = 'Adds an admin bar button to flip the current admin screen between LTR and RTL.';
	$infinity23_essential_modules_array['rtl_ltr_direction_change']['include_exclude_icon_path'] = 'include-exclude/rtl-ltr-direction-change.png';

?>
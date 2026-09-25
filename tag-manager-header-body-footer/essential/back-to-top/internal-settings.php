<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name      = 'back_to_top_activate_code';
	$infinity23_settings_array_db_name  = 'back_to_top_settings';
	$infinity23_module_display_name = 'Back To Top';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['back_to_top']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['back_to_top']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['back_to_top']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['back_to_top']['admin_page_slug'] = 'essential-back-to-top';
	$infinity23_essential_modules_array['back_to_top']['hide_menu_field'] = 'back_to_top_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['back_to_top']['include_exclude_data_key'] = 'back_to_top';
	$infinity23_essential_modules_array['back_to_top']['include_exclude_title'] = 'Back To Top';
	$infinity23_essential_modules_array['back_to_top']['include_exclude_description'] = 'Displays a floating button that scrolls visitors back to the top of the page.';
	$infinity23_essential_modules_array['back_to_top']['include_exclude_icon_path'] = 'include-exclude/back-to-top.png';

?>
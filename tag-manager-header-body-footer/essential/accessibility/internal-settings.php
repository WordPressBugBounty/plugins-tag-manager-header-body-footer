<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name      = 'accessibility_activate_code';
	$infinity23_settings_array_db_name  = 'accessibility_settings';
	$infinity23_module_display_name = 'Accessibility';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['accessibility']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['accessibility']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['accessibility']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['accessibility']['admin_page_slug'] = 'essential-accessibility';
	$infinity23_essential_modules_array['accessibility']['hide_menu_field'] = 'accessibility_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['accessibility']['include_exclude_data_key'] = 'accessibility';
	$infinity23_essential_modules_array['accessibility']['include_exclude_title'] = 'Accessibility';
	$infinity23_essential_modules_array['accessibility']['include_exclude_description'] = 'Adds an accessibility button with font size, contrast, and link highlighting controls.';
	$infinity23_essential_modules_array['accessibility']['include_exclude_icon_path'] = 'include-exclude/accessibility.png';

?>
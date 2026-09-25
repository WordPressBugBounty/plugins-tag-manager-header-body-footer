<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'update_manager_activate_code';
	$infinity23_settings_array_db_name = 'update_manager_settings';
	$infinity23_module_display_name = 'Updates Manager';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['update_manager']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['update_manager']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['update_manager']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['update_manager']['admin_page_slug'] = 'essential-update-manager';
	$infinity23_essential_modules_array['update_manager']['hide_menu_field'] = 'update_manager_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['update_manager']['include_exclude_data_key'] = 'update_manager';
	$infinity23_essential_modules_array['update_manager']['include_exclude_title'] = 'Updates Manager';
	$infinity23_essential_modules_array['update_manager']['include_exclude_description'] = "Shows the plugin's tracked version data, so you know which version is installed.";
	$infinity23_essential_modules_array['update_manager']['include_exclude_icon_path'] = 'include-exclude/update-manager.png';

?>
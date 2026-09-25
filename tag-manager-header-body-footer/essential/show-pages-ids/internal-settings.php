<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'show_pages_ids_activate_code';
	$infinity23_settings_array_db_name = 'show_pages_ids_settings';
	$infinity23_module_display_name = 'Show Pages IDs';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['show_pages_ids']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['show_pages_ids']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['show_pages_ids']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['show_pages_ids']['admin_page_slug'] = 'essential-show-pages-ids';
	$infinity23_essential_modules_array['show_pages_ids']['hide_menu_field'] = 'show_pages_ids_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['show_pages_ids']['include_exclude_data_key'] = 'show_pages_ids';
	$infinity23_essential_modules_array['show_pages_ids']['include_exclude_title'] = 'Show Pages IDs';
	$infinity23_essential_modules_array['show_pages_ids']['include_exclude_description'] = 'Displays page and post IDs in the admin list columns and admin bar.';
	$infinity23_essential_modules_array['show_pages_ids']['include_exclude_icon_path'] = 'include-exclude/show-pages-ids.png';

?>
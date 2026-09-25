<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'duplicate_pages_activate_code';
	$infinity23_settings_array_db_name = 'duplicate_pages_settings';
	$infinity23_module_display_name = 'Duplicate Pages';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['duplicate_pages']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['duplicate_pages']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['duplicate_pages']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['duplicate_pages']['admin_page_slug'] = 'essential-duplicate-pages';
	$infinity23_essential_modules_array['duplicate_pages']['hide_menu_field'] = 'duplicate_pages_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['duplicate_pages']['include_exclude_data_key'] = 'duplicate_pages';
	$infinity23_essential_modules_array['duplicate_pages']['include_exclude_title'] = 'Duplicate Pages';
	$infinity23_essential_modules_array['duplicate_pages']['include_exclude_description'] = 'Duplicate pages, posts and custom post types with one click, including SEO data.';
	$infinity23_essential_modules_array['duplicate_pages']['include_exclude_icon_path'] = 'include-exclude/duplicate-pages.png';

?>

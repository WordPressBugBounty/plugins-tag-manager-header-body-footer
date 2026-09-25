<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_module_display_name = 'Show All Pages';

	// ===========================================================================
	// include exclude settings
	// this module has no activation_db_name / settings_array_db_name /
	// hide_menu_field of its own, so only the include/exclude fields are
	// registered here - do not add the other module setting fields without
	// confirming this module gets its own activation toggle first
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['show_all_pages']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['show_all_pages']['admin_page_slug'] = 'essential-show-all-pages';

	$infinity23_essential_modules_array['show_all_pages']['include_exclude_data_key'] = 'show_all_pages';
	$infinity23_essential_modules_array['show_all_pages']['include_exclude_title'] = 'Show All Pages';
	$infinity23_essential_modules_array['show_all_pages']['include_exclude_description'] = 'Displays all pages, posts, categories, tags and WooCommerce items in one view.';
	$infinity23_essential_modules_array['show_all_pages']['include_exclude_icon_path'] = 'include-exclude/show-all-pages.png';

?>
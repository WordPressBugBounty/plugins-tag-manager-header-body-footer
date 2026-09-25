<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'sticky_sidebar_activate_code';
	$infinity23_settings_array_db_name = 'sticky_sidebar_settings';
	$infinity23_module_display_name = 'Sticky Sidebar';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['sticky_sidebar']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['sticky_sidebar']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['sticky_sidebar']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['sticky_sidebar']['admin_page_slug'] = 'essential-sticky-sidebar';
	$infinity23_essential_modules_array['sticky_sidebar']['hide_menu_field'] = 'sticky_sidebar_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['sticky_sidebar']['include_exclude_data_key'] = 'sticky_sidebar';
	$infinity23_essential_modules_array['sticky_sidebar']['include_exclude_title'] = 'Sticky Sidebar';
	$infinity23_essential_modules_array['sticky_sidebar']['include_exclude_description'] = 'Keep the sidebar of your pages in view while visitors scroll down the page.';
	$infinity23_essential_modules_array['sticky_sidebar']['include_exclude_icon_path'] = 'include-exclude/sticky-sidebar.png';

?>

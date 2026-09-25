<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name     = 'notes_activate_code';
	$infinity23_settings_array_db_name = 'notes_settings';
	$infinity23_module_display_name = 'Page Notes';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['notes']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['notes']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['notes']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['notes']['admin_page_slug'] = 'essential-notes';
	$infinity23_essential_modules_array['notes']['hide_menu_field'] = 'notes_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['notes']['include_exclude_data_key'] = 'notes';
	$infinity23_essential_modules_array['notes']['include_exclude_title'] = 'Page Notes';
	$infinity23_essential_modules_array['notes']['include_exclude_description'] = 'Adds a notes panel to pages, posts and the dashboard for internal editor notes.';
	$infinity23_essential_modules_array['notes']['include_exclude_icon_path'] = 'include-exclude/page-post-notes.png';

?>
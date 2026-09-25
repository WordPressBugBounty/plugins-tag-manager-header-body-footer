<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'folders_organizer_activate_code';
	$infinity23_settings_array_db_name = 'folders_organizer_settings';
	$infinity23_module_display_name = 'Folders Organizer';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['folders_organizer']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['folders_organizer']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['folders_organizer']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['folders_organizer']['admin_page_slug'] = 'essential-folders-organizer';
	$infinity23_essential_modules_array['folders_organizer']['hide_menu_field'] = 'folders_organizer_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['folders_organizer']['include_exclude_data_key'] = 'folders_organizer';
	$infinity23_essential_modules_array['folders_organizer']['include_exclude_title'] = 'Folders Organizer';
	$infinity23_essential_modules_array['folders_organizer']['include_exclude_description'] = 'Organize your Media Library images into folders. Pages and posts coming later.';
	$infinity23_essential_modules_array['folders_organizer']['include_exclude_icon_path'] = 'include-exclude/folders-organizer.png';

?>
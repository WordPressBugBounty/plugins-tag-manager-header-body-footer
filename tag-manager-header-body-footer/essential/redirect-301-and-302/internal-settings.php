<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'redirect_301_and_302_activate_code';
	$infinity23_settings_array_db_name = 'redirect_301_and_302_settings';
	$infinity23_module_display_name = 'Redirect 301/302';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['redirect_301_and_302']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['redirect_301_and_302']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['redirect_301_and_302']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['redirect_301_and_302']['admin_page_slug'] = 'essential-redirect-301-and-302';
	$infinity23_essential_modules_array['redirect_301_and_302']['hide_menu_field'] = 'redirect_301_and_302_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['redirect_301_and_302']['include_exclude_data_key'] = 'redirect_301_and_302';
	$infinity23_essential_modules_array['redirect_301_and_302']['include_exclude_title'] = 'Redirect 301/302';
	$infinity23_essential_modules_array['redirect_301_and_302']['include_exclude_description'] = 'Create 301 and 302 redirects organized into folders, with a hit counter for each.';
	$infinity23_essential_modules_array['redirect_301_and_302']['include_exclude_icon_path'] = 'include-exclude/redirect-301-and-302.png';

?>
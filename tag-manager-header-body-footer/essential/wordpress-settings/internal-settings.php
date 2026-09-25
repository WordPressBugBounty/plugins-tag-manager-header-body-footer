<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'wordpress_settings_activate_code';
	$infinity23_settings_array_db_name = 'wordpress_settings';
	$infinity23_module_display_name = 'WordPress Settings';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['wordpress_settings']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['wordpress_settings']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['wordpress_settings']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['wordpress_settings']['admin_page_slug'] = 'essential-wordpress-settings';
	$infinity23_essential_modules_array['wordpress_settings']['hide_menu_field'] = 'wordpress_settings_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['wordpress_settings']['include_exclude_data_key'] = 'wordpress_settings';
	$infinity23_essential_modules_array['wordpress_settings']['include_exclude_title'] = 'WordPress Settings';
	$infinity23_essential_modules_array['wordpress_settings']['include_exclude_description'] = 'Edit the site logo, favicon, name and description, plus Gutenberg editor behavior.';
	$infinity23_essential_modules_array['wordpress_settings']['include_exclude_icon_path'] = 'include-exclude/wordpress-settings.png';

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'plugins_and_themes_downloader_activate_code';
	$infinity23_settings_array_db_name = 'plugins_and_themes_downloader_settings';
	$infinity23_module_display_name = 'Plugins/Themes Downloader';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['plugins_and_themes_downloader']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['plugins_and_themes_downloader']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['plugins_and_themes_downloader']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['plugins_and_themes_downloader']['admin_page_slug'] = 'essential-plugins-and-themes-downloader';
	$infinity23_essential_modules_array['plugins_and_themes_downloader']['hide_menu_field'] = 'plugins_and_themes_downloader_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['plugins_and_themes_downloader']['include_exclude_data_key'] = 'plugins_and_themes_downloader';
	$infinity23_essential_modules_array['plugins_and_themes_downloader']['include_exclude_title'] = 'Plugins/Themes Downloader';
	$infinity23_essential_modules_array['plugins_and_themes_downloader']['include_exclude_description'] = 'Download installed plugins and themes as ZIP files from the admin dashboard.';
	$infinity23_essential_modules_array['plugins_and_themes_downloader']['include_exclude_icon_path'] = 'include-exclude/plugins-and-themes-downloader.png';

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name      = 'tag_manager_activate_code';
	$infinity23_settings_array_db_name  = 'tag_manager_settings';
	$infinity23_module_display_name = 'Tag Manager';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['tag_manager']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['tag_manager']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['tag_manager']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['tag_manager']['admin_page_slug'] = 'essential-tag-manager';
	$infinity23_essential_modules_array['tag_manager']['hide_menu_field'] = 'tag_manager_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['tag_manager']['include_exclude_data_key'] = 'tag_manager';
	$infinity23_essential_modules_array['tag_manager']['include_exclude_title'] = 'Tag Manager';
	$infinity23_essential_modules_array['tag_manager']['include_exclude_description'] = 'Insert head, body and footer tracking codes like GTM, Analytics and Facebook Pixel.';
	$infinity23_essential_modules_array['tag_manager']['include_exclude_icon_path'] = 'include-exclude/tag-manager.png';

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name = 'cron_management_activate_code';
	$infinity23_settings_array_db_name = 'cron_management_settings';
	$infinity23_module_display_name = 'Cron Management';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['cron_management']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['cron_management']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['cron_management']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['cron_management']['admin_page_slug'] = 'essential-cron-management';
	$infinity23_essential_modules_array['cron_management']['hide_menu_field'] = 'cron_management_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['cron_management']['include_exclude_data_key'] = 'cron_management';
	$infinity23_essential_modules_array['cron_management']['include_exclude_title'] = 'Cron Management';
	$infinity23_essential_modules_array['cron_management']['include_exclude_description'] = 'View every scheduled WordPress cron event, run one now or reschedule it.';
	$infinity23_essential_modules_array['cron_management']['include_exclude_icon_path'] = 'include-exclude/cron-management.png';

?>
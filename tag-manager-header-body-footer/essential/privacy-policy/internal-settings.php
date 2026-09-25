<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name     = 'privacy_policy_activate_code';
	$infinity23_settings_array_db_name = 'privacy_policy_settings';
	$infinity23_module_display_name = 'Privacy Policy';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['privacy_policy']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['privacy_policy']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['privacy_policy']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['privacy_policy']['admin_page_slug'] = 'essential-privacy-policy';
	$infinity23_essential_modules_array['privacy_policy']['hide_menu_field'] = 'privacy_policy_hide_menu';

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['privacy_policy']['include_exclude_data_key'] = 'privacy_policy';
	$infinity23_essential_modules_array['privacy_policy']['include_exclude_title'] = 'Privacy Policy';
	$infinity23_essential_modules_array['privacy_policy']['include_exclude_description'] = 'Displays a cookie consent bar with a configurable accept button and privacy policy link.';
	$infinity23_essential_modules_array['privacy_policy']['include_exclude_icon_path'] = 'include-exclude/privacy-policy.png';

?>
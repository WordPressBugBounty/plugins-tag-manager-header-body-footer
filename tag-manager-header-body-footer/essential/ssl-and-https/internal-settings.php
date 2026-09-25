<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_activation_db_name      = 'ssl_and_security_activate_code';
	$infinity23_settings_array_db_name  = 'ssl_and_security_settings';
	$infinity23_module_display_name = 'Force SSL & HTTPS';

	// ===========================================================================
	// module settings
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['ssl_and_security']['activation_db_name'] = $infinity23_activation_db_name;
	$infinity23_essential_modules_array['ssl_and_security']['settings_array_db_name'] = $infinity23_settings_array_db_name;
	$infinity23_essential_modules_array['ssl_and_security']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['ssl_and_security']['admin_page_slug'] = 'essential-ssl-and-https';
	$infinity23_essential_modules_array['ssl_and_security']['hide_menu_field'] = 'ssl_and_security_hide_menu';
	$infinity23_essential_modules_array['ssl_and_security']['skip_auto_activate'] = true;

	// ===========================================================================
	// include exclude settings
	// ===========================================================================

	$infinity23_essential_modules_array['ssl_and_security']['include_exclude_data_key'] = 'ssl_and_security';
	$infinity23_essential_modules_array['ssl_and_security']['include_exclude_title'] = 'Force SSL & HTTPS';
	$infinity23_essential_modules_array['ssl_and_security']['include_exclude_description'] = 'Forces HTTPS on every request and rewrites leftover http:// links in the page output.';
	$infinity23_essential_modules_array['ssl_and_security']['include_exclude_icon_path'] = 'include-exclude/ssl-and-https.png';

?>
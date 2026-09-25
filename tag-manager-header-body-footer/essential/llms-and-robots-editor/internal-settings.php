<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_module_display_name = 'LLMs & Robots Editor';

	// ===========================================================================
	// include exclude settings
	// this module has no activation_db_name / settings_array_db_name /
	// hide_menu_field of its own, so only the include/exclude fields are
	// registered here - do not add the other module setting fields without
	// confirming this module gets its own activation toggle first
	// ===========================================================================

	global $infinity23_essential_modules_array;

	$infinity23_essential_modules_array['llms_and_robots_editor']['module_display_name'] = $infinity23_module_display_name;
	$infinity23_essential_modules_array['llms_and_robots_editor']['admin_page_slug'] = 'essential-llms-and-robots-editor';

	$infinity23_essential_modules_array['llms_and_robots_editor']['include_exclude_data_key'] = 'llms_and_robots_editor';
	$infinity23_essential_modules_array['llms_and_robots_editor']['include_exclude_title'] = 'LLMs & Robots Editor';
	$infinity23_essential_modules_array['llms_and_robots_editor']['include_exclude_description'] = 'Edit the llms.txt file directly from the WordPress admin panel.';
	$infinity23_essential_modules_array['llms_and_robots_editor']['include_exclude_icon_path'] = 'include-exclude/llms-and-robots-editor.png';

?>
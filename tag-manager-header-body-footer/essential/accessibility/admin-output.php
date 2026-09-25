<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// =================================================================================================================
	// Initial plugin load and getting default values
	// =================================================================================================================

	// --------------------------------------------
	// load essential files for the page (settings file, style, javascript etc...)
	// --------------------------------------------

	include( dirname(__FILE__) . "/../../include/settings.php" );
	include( dirname(__FILE__) . "/internal-settings.php" );
	infinity23_load_essential_files();

	$infinity23_success_message = '';
	$infinity23_error_message   = '';

	if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {
		$infinity23_success_message = "The data was updated successfully";
	} // if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {

	// ----------------------------------------
	// loading default values for the plugin first load
	// ----------------------------------------

	include( dirname(__FILE__) . "/default-values.php" );

	// =================================================================================================================
	// Update the data if the form was submitted
	// =================================================================================================================

	// ----------------------------------------
	// reset all accessibility settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_accessibility_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_accessibility_reset_nonce'] ) ), 'infinity23_accessibility_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_accessibility_reset_nonce'] ) ) {

	// ----------------------------------------
	// code toggle activation form submit
	// ----------------------------------------

	if( isset( $_POST['infinity23_accessibility_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_accessibility_activate_nonce'] ) ), 'infinity23_accessibility_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'accessibility', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "The accessibility button was activated successfully";
			} else {

				infinity23_module_deactivate_redirect( $infinity23_module_display_name );

			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_accessibility_activate_nonce'] ) ) {

	// ----------------------------------------
	// Submit the main form with data
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_accessibility_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_accessibility_nonce'] ) ), 'infinity23_accessibility_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_data_array['icon_image_url'] = infinity23_pro_field_sanitize( 'icon_image_url', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['simple_image_url'] = infinity23_pro_field_sanitize( 'simple_image_url', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['button_display_type'] = infinity23_pro_field_sanitize( 'button_display_type', $infinity23_existing_data_array, $infinity23_defaults );

			if( !in_array( $infinity23_data_array['button_display_type'], array( 'icon', 'image' ), true ) ) {
				$infinity23_data_array['button_display_type'] = 'icon';
			} // if( !in_array( $infinity23_data_array['button_display_type'], array( 'icon', 'image' ), true ) ) {

			$infinity23_background_type = infinity23_post_sanitize('background_type', 'text');

			if( !in_array( $infinity23_background_type, array( 'solid', 'gradient', 'custom_css', 'none' ), true ) ) {
				$infinity23_background_type = 'solid';
			} // if( !in_array( $infinity23_background_type, array( 'solid', 'gradient', 'custom_css', 'none' ), true ) ) {

			$infinity23_data_array['background'] = infinity23_convert_array_to_json( array(
				'type' => $infinity23_background_type,
				'solid_color' => infinity23_post_sanitize('background_solid_color', 'text'),
				'gradient_color_1' => infinity23_post_sanitize('background_gradient_color_1', 'text'),
				'gradient_color_2' => infinity23_post_sanitize('background_gradient_color_2', 'text'),
				'gradient_angle' => infinity23_post_sanitize('background_gradient_angle', 'intval'),
				'custom_css' => infinity23_post_sanitize('background_custom_css', 'text'),
			) );

			$infinity23_data_array['button_width'] = infinity23_pro_field_sanitize( 'button_width', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['button_height'] = infinity23_pro_field_sanitize( 'button_height', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['icon_width'] = infinity23_pro_field_sanitize( 'icon_width', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['icon_height'] = infinity23_pro_field_sanitize( 'icon_height', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['image_width'] = infinity23_pro_field_sanitize( 'image_width', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['image_height'] = infinity23_pro_field_sanitize( 'image_height', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_border_style = infinity23_post_sanitize('button_border_style', 'text');
			$infinity23_border_color = infinity23_post_sanitize('button_border_color', 'text');
			$infinity23_border_unit = infinity23_post_sanitize('button_border_unit', 'text');
			$infinity23_border_top = infinity23_post_sanitize('button_border_top', 'text');
			$infinity23_border_right = infinity23_post_sanitize('button_border_right', 'text');
			$infinity23_border_bottom = infinity23_post_sanitize('button_border_bottom', 'text');
			$infinity23_border_left = infinity23_post_sanitize('button_border_left', 'text');
			$infinity23_border_linked = infinity23_post_sanitize('button_border_linked', 'intval');

			if( !in_array( $infinity23_border_style, array( 'none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge' ), true ) ) {
				$infinity23_border_style = 'none';
			} // if( !in_array(

			if( !in_array( $infinity23_border_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
				$infinity23_border_unit = 'px';
			} // if( !in_array(

			$infinity23_data_array['button_border'] = infinity23_convert_array_to_json( array(
				'style' => $infinity23_border_style,
				'color' => $infinity23_border_color,
				'unit' => $infinity23_border_unit,
				'top' => $infinity23_border_top,
				'right' => $infinity23_border_right,
				'bottom' => $infinity23_border_bottom,
				'left' => $infinity23_border_left,
				'linked' => ( $infinity23_border_linked == 1 ),
			) );

			$infinity23_border_radius_unit = infinity23_post_sanitize('border_radius_unit', 'text');
			$infinity23_border_radius_top_left = infinity23_post_sanitize('border_radius_top_left', 'text');
			$infinity23_border_radius_top_right = infinity23_post_sanitize('border_radius_top_right', 'text');
			$infinity23_border_radius_bottom_right = infinity23_post_sanitize('border_radius_bottom_right', 'text');
			$infinity23_border_radius_bottom_left = infinity23_post_sanitize('border_radius_bottom_left', 'text');
			$infinity23_border_radius_linked = infinity23_post_sanitize('border_radius_linked', 'intval');

			if( !in_array( $infinity23_border_radius_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
				$infinity23_border_radius_unit = 'px';
			} // if( !in_array(

			$infinity23_data_array['border_radius'] = infinity23_convert_array_to_json( array(
				'unit' => $infinity23_border_radius_unit,
				'top_left' => $infinity23_border_radius_top_left,
				'top_right' => $infinity23_border_radius_top_right,
				'bottom_right' => $infinity23_border_radius_bottom_right,
				'bottom_left' => $infinity23_border_radius_bottom_left,
				'linked' => ( $infinity23_border_radius_linked == 1 ),
			) );

			$infinity23_data_array['button_movement'] = infinity23_pro_field_sanitize( 'button_movement', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['accessibility_page_url'] = infinity23_pro_field_sanitize( 'accessibility_page_url', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['accessibility_page_url_new_tab'] = infinity23_pro_field_sanitize( 'accessibility_page_url_new_tab', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['accessibility_background_color'] = infinity23_pro_field_sanitize( 'accessibility_background_color', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['save_user_settings_checkbox'] = infinity23_pro_field_sanitize( 'save_user_settings_checkbox', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['save_user_settings_minutes'] = infinity23_pro_field_sanitize( 'save_user_settings_minutes', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_button_position_hide_desktop = infinity23_post_sanitize('button_position_hide_desktop', 'intval');
			$infinity23_button_position_h_position = infinity23_post_sanitize('button_position_h_position', 'text');
			$infinity23_button_position_h_spacing_value = infinity23_post_sanitize('button_position_h_spacing', 'text');
			$infinity23_button_position_h_spacing_unit = infinity23_post_sanitize('button_position_h_spacing_unit', 'text');
			if( !in_array( $infinity23_button_position_h_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
				$infinity23_button_position_h_spacing_unit = 'px';
			} // if( !in_array( $infinity23_button_position_h_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
			$infinity23_button_position_h_spacing = $infinity23_button_position_h_spacing_value . $infinity23_button_position_h_spacing_unit;
			$infinity23_button_position_v_position = infinity23_post_sanitize('button_position_v_position', 'text');
			$infinity23_button_position_v_spacing_value = infinity23_post_sanitize('button_position_v_spacing', 'text');
			$infinity23_button_position_v_spacing_unit = infinity23_post_sanitize('button_position_v_spacing_unit', 'text');
			if( !in_array( $infinity23_button_position_v_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
				$infinity23_button_position_v_spacing_unit = 'px';
			} // if( !in_array( $infinity23_button_position_v_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
			$infinity23_button_position_v_spacing = $infinity23_button_position_v_spacing_value . $infinity23_button_position_v_spacing_unit;
			$infinity23_button_position_z_index_desktop = infinity23_post_sanitize('button_position_z_index_desktop', 'intval');
			$infinity23_button_position_hide_mobile = infinity23_post_sanitize('button_position_hide_mobile', 'intval');
			$infinity23_button_position_mobile_width = infinity23_post_sanitize('button_position_mobile_width', 'intval');
			$infinity23_button_position_mobile_mode = infinity23_post_sanitize('button_position_mobile_mode', 'text');
			$infinity23_button_position_mobile_h_position = infinity23_post_sanitize('button_position_mobile_h_position', 'text');
			$infinity23_button_position_mobile_h_spacing_value = infinity23_post_sanitize('button_position_mobile_h_spacing', 'text');
			$infinity23_button_position_mobile_h_spacing_unit = infinity23_post_sanitize('button_position_mobile_h_spacing_unit', 'text');
			if( !in_array( $infinity23_button_position_mobile_h_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
				$infinity23_button_position_mobile_h_spacing_unit = 'px';
			} // if( !in_array( $infinity23_button_position_mobile_h_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
			$infinity23_button_position_mobile_h_spacing = $infinity23_button_position_mobile_h_spacing_value . $infinity23_button_position_mobile_h_spacing_unit;
			$infinity23_button_position_mobile_v_position = infinity23_post_sanitize('button_position_mobile_v_position', 'text');
			$infinity23_button_position_mobile_v_spacing_value = infinity23_post_sanitize('button_position_mobile_v_spacing', 'text');
			$infinity23_button_position_mobile_v_spacing_unit = infinity23_post_sanitize('button_position_mobile_v_spacing_unit', 'text');
			if( !in_array( $infinity23_button_position_mobile_v_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
				$infinity23_button_position_mobile_v_spacing_unit = 'px';
			} // if( !in_array( $infinity23_button_position_mobile_v_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
			$infinity23_button_position_mobile_v_spacing = $infinity23_button_position_mobile_v_spacing_value . $infinity23_button_position_mobile_v_spacing_unit;
			if( !in_array( $infinity23_button_position_mobile_mode, array( 'inherit', 'custom' ), true ) ) {
				$infinity23_button_position_mobile_mode = 'inherit';
			} // if( !in_array( $infinity23_button_position_mobile_mode, array( 'inherit', 'custom' ), true ) ) {

			$infinity23_data_array['button_position'] = infinity23_convert_array_to_json( array(
				'hide_desktop' => $infinity23_button_position_hide_desktop,
				'h_position' => $infinity23_button_position_h_position,
				'h_spacing' => $infinity23_button_position_h_spacing,
				'v_position' => $infinity23_button_position_v_position,
				'v_spacing' => $infinity23_button_position_v_spacing,
				'z_index_desktop' => $infinity23_button_position_z_index_desktop,
				'hide_mobile' => $infinity23_button_position_hide_mobile,
				'mobile_width' => $infinity23_button_position_mobile_width,
				'mobile_mode' => $infinity23_button_position_mobile_mode,
				'mobile_h_position' => $infinity23_button_position_mobile_h_position,
				'mobile_h_spacing' => $infinity23_button_position_mobile_h_spacing,
				'mobile_v_position' => $infinity23_button_position_mobile_v_position,
				'mobile_v_spacing' => $infinity23_button_position_mobile_v_spacing,
			) );

			$infinity23_data_array['exclude_admin_bar_checkbox'] = infinity23_pro_field_sanitize( 'exclude_admin_bar_checkbox', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['exclude_ids'] = infinity23_pro_field_sanitize( 'exclude_ids', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['exclude_option'] = infinity23_post_sanitize('exclude_ids_option', 'text');

			if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {
				$infinity23_data_array['exclude_option'] = 'exclude';
			} // if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {

			$infinity23_data_array['translate_title'] = infinity23_pro_field_sanitize( 'translate_title', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['translate_increase_font'] = infinity23_pro_field_sanitize( 'translate_increase_font', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['translate_decrease_font'] = infinity23_pro_field_sanitize( 'translate_decrease_font', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['translate_light_contrast'] = infinity23_pro_field_sanitize( 'translate_light_contrast', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['translate_high_contrast'] = infinity23_pro_field_sanitize( 'translate_high_contrast', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['translate_highlight_links'] = infinity23_pro_field_sanitize( 'translate_highlight_links', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['translate_reset'] = infinity23_pro_field_sanitize( 'translate_reset', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['translate_accessibility_statement'] = infinity23_pro_field_sanitize( 'translate_accessibility_statement', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['custom_css'] = infinity23_pro_field_sanitize( 'custom_css', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['custom_css_active'] = infinity23_pro_field_sanitize( 'custom_css_active', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['accessibility_hide_menu'] = infinity23_pro_field_sanitize( 'accessibility_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			// ----------------------------------------
			// validation checks
			// ----------------------------------------

			$errors = array();

			if( $infinity23_data_array['button_display_type'] === 'image' ) {

				if( empty( $infinity23_data_array['simple_image_url'] ) ) {
					$errors[] = "Simple image URL is empty";
				} // if( empty( $infinity23_data_array['simple_image_url'] ) ) {

			} else {

				if( empty( $infinity23_data_array['icon_image_url'] ) ) {
					$errors[] = "Icon image URL is empty";
				} // if( empty( $infinity23_data_array['icon_image_url'] ) ) {

			} // if( $infinity23_data_array['button_display_type'] === 'image' ) {

			if( empty( $infinity23_data_array['button_width'] ) || empty( $infinity23_data_array['button_height'] ) ) {
				$errors[] = "Button Wrap Size is empty - please enter a width and height for the button.";
			} // if( empty( $infinity23_data_array['button_width'] ) || empty( $infinity23_data_array['button_height'] ) ) {

			if( empty( $infinity23_data_array['icon_width'] ) || empty( $infinity23_data_array['icon_height'] ) ) {
				$errors[] = "Icon Size is empty - please enter a width and height for the icon.";
			} // if( empty( $infinity23_data_array['icon_width'] ) || empty( $infinity23_data_array['icon_height'] ) ) {

			// ----------------------------------------
			// save or show errors
			// ----------------------------------------

			if( empty( $errors ) ) {

				infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
				$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-accessibility', 'saved' => '1' ), admin_url('admin.php') );
				infinity23_safe_redirect( $infinity23_redirect_url );

			} else {
				$infinity23_error_message = $errors;
			} // if( empty( $errors ) ) {

		} else { // if( wp_verify_nonce( $_POST['infinity23_accessibility_nonce'], 'infinity23_accessibility_action' ) ) {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce( $_POST['infinity23_accessibility_nonce'], 'infinity23_accessibility_action' ) ) {

	} // elseif( isset( $_POST['infinity23_accessibility_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	// ----------------------------------------
	// loading the plugin data if it exists on the database
	// ----------------------------------------

	// load all the plugin data from the database
	// if errors exist, $infinity23_data_array already holds the POST values so the user won't lose their changes
	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	// get the plugin data if it exists on the database
	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_button_display_type = infinity23_pro_get_data( 'button_display_type', $infinity23_data_array, $infinity23_defaults );

	if( !in_array( $infinity23_button_display_type, array( 'icon', 'image' ), true ) ) {
		$infinity23_button_display_type = 'icon';
	} // if( !in_array( $infinity23_button_display_type, array( 'icon', 'image' ), true ) ) {

	$infinity23_background = infinity23_pro_get_data( 'background', $infinity23_data_array, $infinity23_defaults );

	if( empty( $infinity23_background ) ) {
		$infinity23_background = infinity23_convert_array_to_json( infinity23_parse_background_to_struct( '', $infinity23_default_background ) );
	} // if( empty( $infinity23_background ) ) {

	$infinity23_button_width = infinity23_pro_get_data( 'button_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_button_height = infinity23_pro_get_data( 'button_height', $infinity23_data_array, $infinity23_defaults );
	$infinity23_button_movement = infinity23_pro_get_data( 'button_movement', $infinity23_data_array, $infinity23_defaults );
	$infinity23_icon_image_url = infinity23_pro_get_data( 'icon_image_url', $infinity23_data_array, $infinity23_defaults );
	$infinity23_simple_image_url = infinity23_pro_get_data( 'simple_image_url', $infinity23_data_array, $infinity23_defaults );
	$infinity23_button_position = infinity23_pro_get_data( 'button_position', $infinity23_data_array, $infinity23_defaults );

	if( empty( $infinity23_button_position ) ) {
		$infinity23_button_position = infinity23_convert_array_to_json( infinity23_parse_button_position_to_struct( '', $infinity23_data_array, $infinity23_default_button_position ) );
	} // if( empty( $infinity23_button_position ) ) {

	$infinity23_accessibility_background_color = infinity23_pro_get_data( 'accessibility_background_color', $infinity23_data_array, $infinity23_defaults );

	$infinity23_accessibility_page_url = infinity23_pro_get_data( 'accessibility_page_url', $infinity23_data_array, $infinity23_defaults );

	$infinity23_accessibility_page_url_new_tab = infinity23_pro_get_data( 'accessibility_page_url_new_tab', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_ids = infinity23_pro_get_data( 'exclude_ids', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_option = infinity23_pro_get_data( 'exclude_option', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_admin_bar_checkbox = infinity23_pro_get_data( 'exclude_admin_bar_checkbox', $infinity23_data_array, $infinity23_defaults );
	$infinity23_save_user_settings_checkbox = infinity23_pro_get_data( 'save_user_settings_checkbox', $infinity23_data_array, $infinity23_defaults );
	$infinity23_save_user_settings_minutes = infinity23_pro_get_data( 'save_user_settings_minutes', $infinity23_data_array, $infinity23_defaults );
	$infinity23_border_radius = infinity23_pro_get_data( 'border_radius', $infinity23_data_array, $infinity23_defaults );

	if( empty( $infinity23_border_radius ) ) {
		$infinity23_border_radius = infinity23_convert_array_to_json( infinity23_parse_border_radius_to_struct( '', $infinity23_default_border_radius ) );
	} // if( empty( $infinity23_border_radius ) ) {

	$infinity23_button_border = infinity23_pro_get_data( 'button_border', $infinity23_data_array, $infinity23_defaults );

	if( empty( $infinity23_button_border ) ) {
		$infinity23_button_border = infinity23_convert_array_to_json( infinity23_parse_border_to_struct( '', $infinity23_default_button_border ) );
	} // if( empty( $infinity23_button_border ) ) {

	$infinity23_icon_width = infinity23_pro_get_data( 'icon_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_icon_height = infinity23_pro_get_data( 'icon_height', $infinity23_data_array, $infinity23_defaults );
	$infinity23_image_width = infinity23_pro_get_data( 'image_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_image_height = infinity23_pro_get_data( 'image_height', $infinity23_data_array, $infinity23_defaults );
	$infinity23_translate_title = infinity23_pro_get_data( 'translate_title', $infinity23_data_array, $infinity23_defaults );
	$infinity23_translate_increase_font = infinity23_pro_get_data( 'translate_increase_font', $infinity23_data_array, $infinity23_defaults );
	$infinity23_translate_decrease_font = infinity23_pro_get_data( 'translate_decrease_font', $infinity23_data_array, $infinity23_defaults );
	$infinity23_translate_light_contrast = infinity23_pro_get_data( 'translate_light_contrast', $infinity23_data_array, $infinity23_defaults );
	$infinity23_translate_high_contrast = infinity23_pro_get_data( 'translate_high_contrast', $infinity23_data_array, $infinity23_defaults );
	$infinity23_translate_highlight_links = infinity23_pro_get_data( 'translate_highlight_links', $infinity23_data_array, $infinity23_defaults );
	$infinity23_translate_reset = infinity23_pro_get_data( 'translate_reset', $infinity23_data_array, $infinity23_defaults );
	$infinity23_translate_accessibility_statement = infinity23_pro_get_data( 'translate_accessibility_statement', $infinity23_data_array, $infinity23_defaults );
	$infinity23_accessibility_hide_menu = intval( infinity23_pro_get_data( 'accessibility_hide_menu', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_custom_css = infinity23_pro_get_data( 'custom_css', $infinity23_data_array, $infinity23_defaults );
	$infinity23_custom_css_active = intval( infinity23_pro_get_data( 'custom_css_active', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_custom_css_example = '';
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* moves the whole output further down the screen */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-accessibility {' . "\n";
	$infinity23_custom_css_example .= '    top: 180px !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* gives the button a dashed outline instead of a solid border, and rounds only two corners */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-accessibility .yy-button {' . "\n";
	$infinity23_custom_css_example .= '    border-width: 3px !important;' . "\n";
	$infinity23_custom_css_example .= '    border-style: dashed !important;' . "\n";
	$infinity23_custom_css_example .= '    border-color: #ffcc00 !important;' . "\n";
	$infinity23_custom_css_example .= '    border-top-left-radius: 20px !important;' . "\n";
	$infinity23_custom_css_example .= '    border-bottom-right-radius: 20px !important;' . "\n";
	$infinity23_custom_css_example .= '    border-top-right-radius: 0px !important;' . "\n";
	$infinity23_custom_css_example .= '    border-bottom-left-radius: 0px !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* gives the dropdown menu box a dark background and a matching dashed border */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-accessibility .yy-box {' . "\n";
	$infinity23_custom_css_example .= '    width: 260px !important;' . "\n";
	$infinity23_custom_css_example .= '    background-color: #1e1e1e !important;' . "\n";
	$infinity23_custom_css_example .= '    border-style: dashed !important;' . "\n";
	$infinity23_custom_css_example .= '    border-color: #ffcc00 !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* changes the title and link text color to fit the dark dropdown menu box */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-accessibility .yy-box .yy-title {' . "\n";
	$infinity23_custom_css_example .= '    color: #ffcc00 !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '.infinity23-accessibility .yy-box a {' . "\n";
	$infinity23_custom_css_example .= '    color: #eaeaea !important;' . "\n";
	$infinity23_custom_css_example .= '    border-top-color: #3a3a3a !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* keeps the hover state dark instead of the default light background */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-accessibility .yy-box a:hover {' . "\n";
	$infinity23_custom_css_example .= '    background-color: #2a2a2a !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* changes the background color of the accessibility statement link */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-accessibility a.accessibility-notice {' . "\n";
	$infinity23_custom_css_example .= '    background-color: #ffcc00 !important;' . "\n";
	$infinity23_custom_css_example .= '    color: #1e1e1e !important;' . "\n";
	$infinity23_custom_css_example .= '}';

?>

<?php
// =================================================================================================================
// Output the plugin form data to page
// =================================================================================================================
?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp accessibility-warp">

<?php
	// ----------------------------------------
	// show success and error messages
	// ----------------------------------------
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Accessibility Settings</h1>
		<p>Below you will be able to edit and make changes to the accessibility icon output:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_accessibility_activate_action', 'infinity23_accessibility_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Accessibility Button</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

			<div class="section-card" data-card-default="open">
			
				<h2>Basic Settings:</h2>

				<br />
				<div class="form-line">

					<?php

					$infinity23_default_icon_image_url = infinity23_plugin_url( 'images/accessibility/accessibility-icon.svg' );
					$infinity23_default_button_image_url = infinity23_plugin_url( 'images/accessibility/accessibility-button.svg' );

					$infinity23_preview_output_icon = '';
					$infinity23_preview_output_icon .= '<div class="infinity23-button-preview-wrap">';
					$infinity23_preview_output_icon .= '<div id="{preview_id}" style="background:' . esc_attr( infinity23_get_advanced_background_value( $infinity23_background ) ) . ';width:' . esc_attr( $infinity23_button_width ) . 'px;height:' . esc_attr( $infinity23_button_height ) . 'px;display:inline-flex;align-items:center;justify-content:center;' . infinity23_get_advanced_border_radius_css( $infinity23_border_radius, '0px' ) . infinity23_get_advanced_border_css( $infinity23_button_border, 'none' ) . '">';
					$infinity23_preview_output_icon .= '<img id="{image_id}" src="' . esc_url( $infinity23_icon_image_url ) . '" style="width:' . esc_attr( $infinity23_icon_width ) . 'px;height:' . esc_attr( $infinity23_icon_height ) . 'px;" />';
					$infinity23_preview_output_icon .= '</div><!--icon_button_preview-->';
					$infinity23_preview_output_icon .= '<small class="infinity23-preview-label">Button Preview</small>';
					$infinity23_preview_output_icon .= '</div><!--infinity23-button-preview-wrap-->';

					$infinity23_preview_output_image = '';
					$infinity23_preview_output_image .= '<div class="infinity23-button-preview-wrap">';
					$infinity23_preview_output_image .= '<div id="{preview_id}" style="background:transparent;width:' . esc_attr( $infinity23_image_width ) . 'px;height:' . esc_attr( $infinity23_image_height ) . 'px;display:inline-flex;align-items:center;justify-content:center;">';
					$infinity23_preview_output_image .= '<img id="{image_id}" src="' . esc_url( $infinity23_simple_image_url ) . '" style="width:' . esc_attr( $infinity23_image_width ) . 'px;height:' . esc_attr( $infinity23_image_height ) . 'px;" />';
					$infinity23_preview_output_image .= '</div><!--icon_button_preview-->';
					$infinity23_preview_output_image .= '<small class="infinity23-preview-label">Button Preview</small>';
					$infinity23_preview_output_image .= '</div><!--infinity23-button-preview-wrap-->';

					?>

					<?php infinity23_button_display_type_field( $infinity23_button_display_type, $infinity23_icon_image_url, $infinity23_simple_image_url, $infinity23_default_icon_image_url, $infinity23_default_button_image_url, $infinity23_preview_output_icon, $infinity23_preview_output_image ); ?>
				</div><!--form-line-->

				<div id="icon-view-fields-wrap" class="<?php if( $infinity23_button_display_type !== 'icon' ) { echo 'infinity23-position-hidden'; } ?>">

				<h3>Background Settings:</h3>

				<?php infinity23_advanced_background_field( $infinity23_background ); ?>

				<h3>Button Size:</h3>

				<div class="form-line sizes-line">
					<label>Button Size: </label>

					<div class="width-div">
						<input type="text" id="button_width" class='very-short-input' name="button_width" value="<?php echo esc_attr( $infinity23_button_width ); ?>" />
						<span class="size-label-width">width</span>
					</div>
					x
					<div class="height-div">
						<input type="text" id="button_height" class='very-short-input' name="button_height" value="<?php echo esc_attr( $infinity23_button_height ); ?>" />
						<span class="size-label-height">height</span>
					</div>
					<small>PX</small>
				</div><!--form-line-->

				<div class="form-line sizes-line">
					<label>Icon Size: </label>

					<div class="width-div">
						<input type="text" id="icon_width" class='very-short-input' name="icon_width" value="<?php echo esc_attr( $infinity23_icon_width ); ?>" />
						<span class="size-label-width">width</span>
					</div>
					x
					<div class="height-div">
						<input type="text" id="icon_height" class='very-short-input' name="icon_height" value="<?php echo esc_attr( $infinity23_icon_height ); ?>" />
						<span class="size-label-height">height</span>
					</div>
					<small>PX</small>
				</div><!--form-line-->

				<h3>Border & Round Corners Settings:</h3>

				<?php infinity23_advanced_border_field( $infinity23_button_border ); ?>

				<?php infinity23_advanced_border_radius_field( $infinity23_border_radius, 'border_radius', 'Round Corners (Border Radius):' ); ?>

				</div><!--icon-view-fields-wrap-->

				<div id="image-view-fields-wrap" class="<?php if( $infinity23_button_display_type !== 'image' ) { echo 'infinity23-position-hidden'; } ?>">

				<h3>Image Size:</h3>

				<div class="form-line sizes-line">
					<label>Image Size: </label>

					<div class="width-div">
						<input type="text" id="image_width" class='very-short-input' name="image_width" value="<?php echo esc_attr( $infinity23_image_width ); ?>" />
						<span class="size-label-width">width</span>
					</div>
					x
					<div class="height-div">
						<input type="text" id="image_height" class='very-short-input' name="image_height" value="<?php echo esc_attr( $infinity23_image_height ); ?>" />
						<span class="size-label-height">height</span>
					</div>
					<small>PX</small>
				</div><!--form-line-->

				</div><!--image-view-fields-wrap-->

				<?php infinity23_button_display_type_secondary_preview( $infinity23_button_display_type, $infinity23_preview_output_icon ); ?>

				<h3>Button Movement:</h3>

				<div class="form-line">
					<label for="button_movement">Button Movement (Static/Fixed): </label>
					<select name="button_movement" id="button_movement">
						<option value="absolute" <?php if( $infinity23_button_movement == 'absolute' ) { echo 'selected'; } ?>>Static</option>
						<option value="fixed" <?php if( $infinity23_button_movement == 'fixed' ) { echo 'selected'; } ?>>Fixed</option>
					</select>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							<strong>Static</strong> - the button stays in its fixed spot on the page and will scroll away with the rest of the content.
							<br /><br />
							<strong>Fixed</strong> - the button stays in the same place on the screen at all times, even when the visitor scrolls down the page.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<h3>Accessibility Page URL Line:</h3>

				<div class="form-line">
					<label for="accessibility_page_url">Accessibility Page URL: </label>
					<input type="text" id="accessibility_page_url" class="very-long-input" name="accessibility_page_url" value="<?php echo esc_url( $infinity23_accessibility_page_url ); ?>" />

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
		 					Insert here the page URL to the accessibility page if it exists.
							<br /><br />
							This will allow users to click on a button to view the accessibility data on the site.
						</div>
						?
					</div><!--backtest-field-help-icon-->

				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="accessibility_page_url_new_tab" name="accessibility_page_url_new_tab" value="1" <?php if( $infinity23_accessibility_page_url_new_tab == 1 ) { echo 'checked'; } ?> />
					<label for="accessibility_page_url_new_tab">Open accessibility page link in new tab</label>
				</div><!--form-line-->

				<div class="form-line background-color-line">
					<label for="accessibility_background_color">Accessibility Page Background color hex: </label>
					<input type="text" id="accessibility_background_color" name="accessibility_background_color" class="infinity23-color-picker" value="<?php echo esc_attr( $infinity23_accessibility_background_color ); ?>" />
				</div><!--form-line-->

			</div><!--section-card-->


			<div class="section-card" data-card-default="open">
				<h2>Responsive Display Settings:</h2>

				<?php infinity23_advanced_button_position_field( $infinity23_button_position ); ?>

			</div><!--section-card-->

			<div class="section-card" data-card-default="open">
				<h2>Exclude Rules:</h2>

				<h3>Exclude Changes to Elements on Page:</h3>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="exclude_admin_bar_checkbox" name="exclude_admin_bar_checkbox" value="1" <?php if( $infinity23_exclude_admin_bar_checkbox == 1 ) { echo 'checked'; } ?> />
					<label for="exclude_admin_bar_checkbox">Do not change the WordPress admin bar <small>(only visible to logged in users)</small></label>
				</div><!--form-line-->

				<h3>Include / Exclude Pages By ID:</h3>

				<?php infinity23_exclude_ids_field( $infinity23_exclude_ids, $infinity23_exclude_option ); ?>

			</div><!--section-card-->

			<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>">
				<h2>Translate Plugin Output: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

				<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

				<p>Change the text of the accessibility button output to any language or custom wording.</p>

				<div class="form-line">
					<label for="translate_title">Title: </label>
					<input type="text" id="translate_title" name="translate_title" value="<?php echo esc_attr( $infinity23_translate_title ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				</div><!--form-line-->

				<div class="form-line">
					<label for="translate_increase_font">Increase Font Size: </label>
					<input type="text" id="translate_increase_font" name="translate_increase_font" value="<?php echo esc_attr( $infinity23_translate_increase_font ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				</div><!--form-line-->

				<div class="form-line">
					<label for="translate_decrease_font">Decrease Font Size: </label>
					<input type="text" id="translate_decrease_font" name="translate_decrease_font" value="<?php echo esc_attr( $infinity23_translate_decrease_font ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				</div><!--form-line-->

				<div class="form-line">
					<label for="translate_light_contrast">Light Contrast: </label>
					<input type="text" id="translate_light_contrast" name="translate_light_contrast" value="<?php echo esc_attr( $infinity23_translate_light_contrast ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				</div><!--form-line-->

				<div class="form-line">
					<label for="translate_high_contrast">High Contrast: </label>
					<input type="text" id="translate_high_contrast" name="translate_high_contrast" value="<?php echo esc_attr( $infinity23_translate_high_contrast ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				</div><!--form-line-->

				<div class="form-line">
					<label for="translate_highlight_links">Highlight Links: </label>
					<input type="text" id="translate_highlight_links" name="translate_highlight_links" value="<?php echo esc_attr( $infinity23_translate_highlight_links ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				</div><!--form-line-->

				<div class="form-line">
					<label for="translate_reset">Reset Settings: </label>
					<input type="text" id="translate_reset" name="translate_reset" value="<?php echo esc_attr( $infinity23_translate_reset ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				</div><!--form-line-->

				<div class="form-line">
					<label for="translate_accessibility_statement">Accessibility Statement Link Text: </label>
					<input type="text" id="translate_accessibility_statement" name="translate_accessibility_statement" value="<?php echo esc_attr( $infinity23_translate_accessibility_statement ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				</div><!--form-line-->

			</div><!--section-card-->

			<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="open">
				<h2>Advanced Settings: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

				<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

				<h3>Remember User Settings:</h3>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="save_user_settings_checkbox" name="save_user_settings_checkbox" value="1" <?php if( $infinity23_save_user_settings_checkbox == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
					<label for="save_user_settings_checkbox">Keep the visitor accessibility choices when moving between pages <small>(saved in a cookie on the visitor browser)</small></label>
				</div><!--form-line-->

				<div class="form-line">
					<label for="save_user_settings_minutes">Save the settings for: </label>
					<input type="text" id="save_user_settings_minutes" class='short-input' name="save_user_settings_minutes" value="<?php echo esc_attr( $infinity23_save_user_settings_minutes ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
					<small>Minutes</small>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							How long the visitor accessibility choices are remembered before they go back to the site default.
							<br /><br />
							<strong>Usage Examples:</strong>
							<ul>
								<li>1 hour - <span class="code">60</span></li>
								<li>1 day - <span class="code">1440</span></li>
								<li>1 week - <span class="code">10080</span></li>
								<li>1 month - <span class="code">43200</span></li>
								<li>1 year - <span class="code">525600</span></li>
							</ul>
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

			</div><!--section-card-->

			<?php infinity23_custom_css_section_card( $infinity23_custom_css, $infinity23_custom_css_active, $infinity23_custom_css_example, 'custom_css', $infinity23_module_display_name ); ?>

			<?php infinity23_menu_visibility_field( 'accessibility_hide_menu', $infinity23_accessibility_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_accessibility_action', 'infinity23_accessibility_nonce' ); ?>

			<input type="submit" name="submit_accessibility" class='submit-btn' value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>


	<div class="additional-data-warp" data-card-default="open">

		<h2>Additional Data</h2>

		<h3>Exclude Elements From The Accessibility Changes <small>(Allow to keep any part of the page exactly as it is)</small></h3>

		<p class="additional-data-intro">Add this class in your own HTML when a section of the page should stay exactly as it is when a visitor turns the accessibility options on.</p>

		<ul>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">no-accessibility</span> - add it to any element you don't want the plugin to change, and the font size, contrast colors and link highlighting will skip that element and everything inside it.</p>
			</li>
		</ul>

		<button type="button" class="infinity23-code-example-btn">Code Example</button>
		<div class="infinity23-code-example-block">
			<pre><code><span class="infinity23-code-comment">// the class goes on the wrap element - everything inside it is skipped as well</span>
&lt;div class="<span class="infinity23-code-hl">no-accessibility</span>"&gt;
    &lt;p&gt;This section stays exactly as it is&lt;/p&gt;
&lt;/div&gt;</code></pre>
		</div><!--infinity23-code-example-block-->

		<h3>Button And Menu CSS Classes <small>(Allow to restyle or replace the plugin output from your own CSS)</small></h3>

		<p class="additional-data-intro">These are the main classes printed on the front-end output. Target them from your theme CSS to override the plugin design or to replace the button with your own.</p>

		<ul>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-accessibility</span> - the outer wrap of the whole output, holding the position on the screen, the spacing from the edges and the z-index.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">yy-button</span> - the button that opens and closes the accessibility menu. The icon is set as a background image on this element.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">yy-box</span> - the dropdown menu box that opens under the button and holds all the accessibility option links.</p>
			</li>
		</ul>

		<button type="button" class="infinity23-code-example-btn">Code Example</button>
		<div class="infinity23-code-example-block">
			<pre><code><span class="infinity23-code-comment">// moves the whole output further down the screen</span>
.infinity23-accessibility {
    top: 180px !important;
}

<span class="infinity23-code-comment">// gives the button a dashed outline instead of a solid border, and rounds only two corners</span>
.infinity23-accessibility .yy-button {
    border-width: 3px !important;
    border-style: dashed !important;
    border-color: #ffcc00 !important;
    border-top-left-radius: 20px !important;
    border-bottom-right-radius: 20px !important;
    border-top-right-radius: 0px !important;
    border-bottom-left-radius: 0px !important;
}

<span class="infinity23-code-comment">// gives the dropdown menu box a dark background and a matching dashed border</span>
.infinity23-accessibility .yy-box {
    width: 260px !important;
    background-color: #1e1e1e !important;
    border-style: dashed !important;
    border-color: #ffcc00 !important;
}

<span class="infinity23-code-comment">// changes the title and link text color to fit the dark dropdown menu box</span>
.infinity23-accessibility .yy-box .yy-title {
    color: #ffcc00 !important;
}

.infinity23-accessibility .yy-box a {
    color: #eaeaea !important;
    border-top-color: #3a3a3a !important;
}

<span class="infinity23-code-comment">// changes the background color of the accessibility statement link</span>
.infinity23-accessibility a.accessibility-notice {
    background-color: #ffcc00 !important;
    color: #1e1e1e !important;
}</code></pre>
		</div><!--infinity23-code-example-block-->

	</div><!--additional-data-warp-->

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_accessibility_reset_action', 'infinity23_accessibility_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
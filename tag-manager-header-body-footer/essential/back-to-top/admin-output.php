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
	// reset all back-to-top settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_back_to_top_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_back_to_top_reset_nonce'] ) ), 'infinity23_back_to_top_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_back_to_top_reset_nonce'] ) ) {

	// ----------------------------------------
	// code toggle activation form submit
	// ----------------------------------------

	if( isset( $_POST['infinity23_back_to_top_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_back_to_top_activate_nonce'] ) ), 'infinity23_back_to_top_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'back_to_top', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "The back to top button was activated successfully";
			} else {
				infinity23_module_deactivate_redirect( $infinity23_module_display_name );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_back_to_top_activate_nonce'] ) ) {

	// ----------------------------------------
	// Submit the main form with data
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_back_to_top_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_back_to_top_nonce'] ) ), 'infinity23_back_to_top_action' ) && current_user_can( 'manage_options' ) ) {

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

			$infinity23_data_array['button_visibility'] = infinity23_pro_field_sanitize( 'button_visibility', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['scroll_offset'] = infinity23_pro_field_sanitize( 'scroll_offset', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['mobile_scroll_offset'] = infinity23_pro_field_sanitize( 'mobile_scroll_offset', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['exclude_ids'] = infinity23_pro_field_sanitize( 'exclude_ids', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['exclude_option'] = infinity23_post_sanitize('exclude_ids_option', 'text');

			if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {
				$infinity23_data_array['exclude_option'] = 'exclude';
			} // if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {

			$infinity23_data_array['custom_css'] = infinity23_pro_field_sanitize( 'custom_css', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['custom_css_active'] = infinity23_pro_field_sanitize( 'custom_css_active', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['back_to_top_hide_menu'] = infinity23_pro_field_sanitize( 'back_to_top_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			// ----------------------------------------
			// validation checks

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
				$errors[] = "Button size is empty - please enter a width and height for the button.";
			} // if( empty( $infinity23_data_array['button_width'] ) || empty( $infinity23_data_array['button_height'] ) ) {

			// ----------------------------------------
			// save or show errors
			// ----------------------------------------

			if( empty( $errors ) ) {

				infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
				$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-back-to-top', 'saved' => '1' ), admin_url('admin.php') );
				infinity23_safe_redirect( $infinity23_redirect_url );

			} else {
				$infinity23_error_message = $errors;
			} // if( empty( $errors ) ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce( $_POST['infinity23_back_to_top_nonce'], 'infinity23_back_to_top_action' ) ) {

	} // elseif( isset( $_POST['infinity23_back_to_top_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code                    = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_button_display_type = infinity23_pro_get_data( 'button_display_type', $infinity23_data_array, $infinity23_defaults );

	if( !in_array( $infinity23_button_display_type, array( 'icon', 'image' ), true ) ) {
		$infinity23_button_display_type = 'icon';
	} // if( !in_array( $infinity23_button_display_type, array( 'icon', 'image' ), true ) ) {

	$infinity23_background                       = infinity23_pro_get_data( 'background', $infinity23_data_array, $infinity23_defaults );

	if( empty( $infinity23_background ) ) {
		$infinity23_background = infinity23_convert_array_to_json( infinity23_parse_background_to_struct( '', $infinity23_default_background ) );
	} // if( empty( $infinity23_background ) ) {

	$infinity23_button_width = infinity23_pro_get_data( 'button_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_button_height = infinity23_pro_get_data( 'button_height', $infinity23_data_array, $infinity23_defaults );
	$infinity23_border_radius                    = infinity23_pro_get_data( 'border_radius', $infinity23_data_array, $infinity23_defaults );

	if( empty( $infinity23_border_radius ) ) {
		$infinity23_border_radius = infinity23_convert_array_to_json( infinity23_parse_border_radius_to_struct( '', $infinity23_default_border_radius ) );
	} // if( empty( $infinity23_border_radius ) ) {

	$infinity23_button_border                    = infinity23_pro_get_data( 'button_border', $infinity23_data_array, $infinity23_defaults );

	if( empty( $infinity23_button_border ) ) {
		$infinity23_button_border = infinity23_convert_array_to_json( infinity23_parse_border_to_struct( '', $infinity23_default_button_border ) );
	} // if( empty( $infinity23_button_border ) ) {

	$infinity23_icon_image_url = infinity23_pro_get_data( 'icon_image_url', $infinity23_data_array, $infinity23_defaults );
	$infinity23_simple_image_url = infinity23_pro_get_data( 'simple_image_url', $infinity23_data_array, $infinity23_defaults );
	$infinity23_icon_width = infinity23_pro_get_data( 'icon_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_icon_height = infinity23_pro_get_data( 'icon_height', $infinity23_data_array, $infinity23_defaults );
	$infinity23_image_width = infinity23_pro_get_data( 'image_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_image_height = infinity23_pro_get_data( 'image_height', $infinity23_data_array, $infinity23_defaults );
	$infinity23_button_visibility = infinity23_pro_get_data( 'button_visibility', $infinity23_data_array, $infinity23_defaults );
	$infinity23_scroll_offset = infinity23_pro_get_data( 'scroll_offset', $infinity23_data_array, $infinity23_defaults );
	$infinity23_mobile_scroll_offset = infinity23_pro_get_data( 'mobile_scroll_offset', $infinity23_data_array, $infinity23_defaults );
	$infinity23_button_position                  = infinity23_pro_get_data( 'button_position', $infinity23_data_array, $infinity23_defaults );

	if( empty( $infinity23_button_position ) ) {
		$infinity23_button_position = infinity23_convert_array_to_json( infinity23_parse_button_position_to_struct( '', $infinity23_data_array, $infinity23_default_button_position ) );
	} // if( empty( $infinity23_button_position ) ) {

	$infinity23_exclude_ids                      = infinity23_pro_get_data( 'exclude_ids', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_option                   = infinity23_pro_get_data( 'exclude_option', $infinity23_data_array, $infinity23_defaults );
	$infinity23_back_to_top_hide_menu = intval( infinity23_pro_get_data( 'back_to_top_hide_menu', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_custom_css                       = infinity23_pro_get_data( 'custom_css', $infinity23_data_array, $infinity23_defaults );
	$infinity23_custom_css_active                = intval( infinity23_pro_get_data( 'custom_css_active', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_custom_css_example = '';
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* moves the button and gives it a dashed outline */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-back-to-top-wrap {' . "\n";
	$infinity23_custom_css_example .= '    right: 60px !important;' . "\n";
	$infinity23_custom_css_example .= '    bottom: 60px !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* changes the button size and border style */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-back-to-top-wrap .infinity23-back-to-top-btn {' . "\n";
	$infinity23_custom_css_example .= '    width: 60px !important;' . "\n";
	$infinity23_custom_css_example .= '    height: 60px !important;' . "\n";
	$infinity23_custom_css_example .= '    border-width: 3px !important;' . "\n";
	$infinity23_custom_css_example .= '    border-style: dashed !important;' . "\n";
	$infinity23_custom_css_example .= '}';

?>

<?php
// =================================================================================================================
// Output the plugin form data to page
// =================================================================================================================
?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp back-to-top-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Back To Top Settings</h1>
		<p>Below you will be able to edit and make changes to the back to top button output:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_back_to_top_activate_action', 'infinity23_back_to_top_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Back To Top Button</span>
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

					$infinity23_default_icon_image_url = infinity23_plugin_url( 'images/back-to-top/back-to-top-icon.svg' );
					$infinity23_default_button_image_url = infinity23_plugin_url( 'images/back-to-top/back-to-top-button.svg' );

					$infinity23_preview_output_icon = '';
					$infinity23_preview_output_icon .= '<div class="infinity23-button-preview-wrap">';
					$infinity23_preview_bg = esc_attr( infinity23_get_advanced_background_value( $infinity23_background ) );
					$infinity23_preview_output_icon .= '<div id="{preview_id}" style="background:' . $infinity23_preview_bg . ';width:' . esc_attr( $infinity23_button_width ) . 'px;height:' . esc_attr( $infinity23_button_height ) . 'px;display:inline-flex;align-items:center;justify-content:center;' . infinity23_get_advanced_border_radius_css( $infinity23_border_radius, '50%' ) . infinity23_get_advanced_border_css( $infinity23_button_border, 'none' ) . '">';
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

			</div><!--section-card-->


			<div class="section-card" data-card-default="open">
				<h2>Responsive Display Settings:</h2>

				<?php infinity23_advanced_button_position_field( $infinity23_button_position ); ?>

				<h3>Scroll Trigger Settings:</h3>

				<div class="form-line">
					<label for="button_visibility">Button Visibility: </label>
					<select name="button_visibility" id="button_visibility">
						<option value="on_scroll" <?php if( $infinity23_button_visibility == 'on_scroll' ) { echo 'selected'; } ?>>Show Only After Scrolling</option>
						<option value="always" <?php if( $infinity23_button_visibility == 'always' ) { echo 'selected'; } ?>>Always Visible</option>
					</select>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Always Visible keeps the button on the screen from the moment the page loads.
							<br /><br />
							Show Only After Scrolling hides the button until the visitor scrolls down past the distance set below.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="scroll-offset-fields-wrap">

				<div class="form-line">
					<label for="scroll_offset">Show Button After Scrolling: </label>
					<input type="text" id="scroll_offset" class='very-short-input' name="scroll_offset" value="<?php echo esc_attr( $infinity23_scroll_offset ); ?>" />
					<small>PX</small>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Controls how far down the visitor needs to scroll before the button appears.
							<br /><br />
							A smaller number shows the button sooner, a larger number keeps it hidden longer.
							<br /><br />
							Set it to <span class="code">0</span> to always show the button.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line">
					<label for="mobile_scroll_offset">Show Button After Scrolling (Mobile): </label>
					<input type="text" id="mobile_scroll_offset" class='very-short-input' name="mobile_scroll_offset" value="<?php echo esc_attr( $infinity23_mobile_scroll_offset ); ?>" />
					<small>PX</small>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Controls how far down the visitor needs to scroll before the button appears on mobile.
							<br /><br />
							This value is used below the mobile breaking point instead of the desktop value.
							<br /><br />
							Set it to <span class="code">0</span> to always show the button on mobile.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				</div><!--scroll-offset-fields-wrap-->

			</div><!--section-card-->

			<div class="section-card" data-card-default="open">
				<h2>Include / Exclude Pages By ID:</h2>

				<?php infinity23_exclude_ids_field( $infinity23_exclude_ids, $infinity23_exclude_option ); ?>

			</div><!--section-card-->

			<?php infinity23_custom_css_section_card( $infinity23_custom_css, $infinity23_custom_css_active, $infinity23_custom_css_example, 'custom_css', $infinity23_module_display_name ); ?>

			<?php infinity23_menu_visibility_field( 'back_to_top_hide_menu', $infinity23_back_to_top_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_back_to_top_action', 'infinity23_back_to_top_nonce' ); ?>

			<input type="submit" name="submit_back_to_top" class='submit-btn' value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="additional-data-warp" data-card-default="open">

		<h2>Additional Data</h2>

		<h3>Button CSS Classes <small>(Allow to restyle or replace the plugin output from your own CSS)</small></h3>

		<p class="additional-data-intro">These are the classes printed on the front-end output. Target them from your theme CSS to override the plugin design or to replace the button icon with your own.</p>

		<ul>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-back-to-top-wrap</span> - the outer wrap printed in the footer that holds the button.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-back-to-top-btn</span> - the button itself, holding the position on the screen, the size, the background color, the border and the icon image.</p>
			</li>
		</ul>

		<button type="button" class="infinity23-code-example-btn">Code Example</button>
		<div class="infinity23-code-example-block">
			<pre><code><span class="infinity23-code-comment">// the outer wrap that holds the button</span>
<span class="infinity23-code-hl">.infinity23-back-to-top-wrap</span> {
    display: block !important;
}

<span class="infinity23-code-comment">// moves the button, changes its size and color, or swap the icon with your own image</span>
.infinity23-back-to-top-wrap <span class="infinity23-code-hl">.infinity23-back-to-top-btn</span> {
    right: 60px !important;
    bottom: 60px !important;
    width: 60px !important;
    height: 60px !important;
    background-color: #000 !important;
    /* background-image: url(my-icon.svg) !important; */
}</code></pre>
		</div><!--infinity23-code-example-block-->

	</div><!--additional-data-warp-->

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_back_to_top_reset_action', 'infinity23_back_to_top_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
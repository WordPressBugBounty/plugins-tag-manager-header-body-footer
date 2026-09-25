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
	// reset all whatsapp button settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_whatsapp_button_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_whatsapp_button_reset_nonce'] ) ), 'infinity23_whatsapp_button_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_whatsapp_button_reset_nonce'] ) ) {

	// ----------------------------------------
	// code toggle activation form submit
	// ----------------------------------------

	if( isset( $_POST['infinity23_whatsapp_button_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_whatsapp_button_activate_nonce'] ) ), 'infinity23_whatsapp_button_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'whatsapp_button', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "The whatsapp button was activated successfully";
			} else {
				infinity23_module_deactivate_redirect( $infinity23_module_display_name );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_whatsapp_button_activate_nonce'] ) ) {

	// ----------------------------------------
	// Submit the main form with data
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_whatsapp_button_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_whatsapp_button_nonce'] ) ), 'infinity23_whatsapp_button_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_data_array['chat_number']                    = infinity23_sanitize_phone_number( infinity23_post_sanitize('chat_number', 'text') );
			$infinity23_data_array['chat_message']                   = infinity23_pro_field_sanitize( 'chat_message', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['open_new_tab']                   = infinity23_pro_field_sanitize( 'open_new_tab', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['button_type']                    = infinity23_post_sanitize('whatsapp_button_type', 'text');

			if( !in_array( $infinity23_data_array['button_type'], array( 'button1', 'button2', 'button3', 'custom' ), true ) ) {
				$infinity23_data_array['button_type'] = 'button1';
			} // if( !in_array(

			// ----------------------------------------
			// always keep the uploaded custom image url so it is not lost
			// when the user switches to one of the preset buttons
			// ----------------------------------------

			$infinity23_data_array['custom_icon_image_url'] = infinity23_pro_field_sanitize( 'custom_icon_image_url', $infinity23_existing_data_array, $infinity23_defaults );

			// ----------------------------------------
			// set icon_image_url based on button type
			// ----------------------------------------

			if( $infinity23_data_array['button_type'] === 'button1' ) {
				$infinity23_data_array['icon_image_url'] = infinity23_plugin_url( 'images/whatsapp-button/whatsapp-button1.svg' );
			} elseif( $infinity23_data_array['button_type'] === 'button2' ) {
				$infinity23_data_array['icon_image_url'] = infinity23_plugin_url( 'images/whatsapp-button/whatsapp-button2.svg' );
			} elseif( $infinity23_data_array['button_type'] === 'button3' ) {
				$infinity23_data_array['icon_image_url'] = infinity23_plugin_url( 'images/whatsapp-button/whatsapp-button3.svg' );
			} elseif( $infinity23_data_array['button_type'] === 'custom' ) {
				$infinity23_data_array['icon_image_url'] = $infinity23_data_array['custom_icon_image_url'];
			} // if( $infinity23_data_array['button_type'] === 'button1' ) {

			$infinity23_data_array['button_width']                   = infinity23_pro_field_sanitize( 'button_width', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['button_height']                  = infinity23_pro_field_sanitize( 'button_height', $infinity23_existing_data_array, $infinity23_defaults );

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

			$infinity23_data_array['exclude_ids']                    = infinity23_pro_field_sanitize( 'exclude_ids', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['exclude_option'] = infinity23_post_sanitize('exclude_ids_option', 'text');

			if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {
				$infinity23_data_array['exclude_option'] = 'exclude';
			} // if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {

			$infinity23_data_array['custom_css']                     = infinity23_pro_field_sanitize( 'custom_css', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['custom_css_active']               = infinity23_pro_field_sanitize( 'custom_css_active', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['whatsapp_button_hide_menu']      = infinity23_pro_field_sanitize( 'whatsapp_button_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			// ----------------------------------------
			// validation checks
			// ----------------------------------------

			$errors = array();

			if( empty( $infinity23_data_array['chat_number'] ) ) {
				$errors[] = "Whatsapp number is empty - please enter a phone number.";
			} // if( empty( $infinity23_data_array['chat_number'] ) ) {

			if( $infinity23_data_array['button_type'] === 'custom' && empty( $infinity23_data_array['icon_image_url'] ) ) {
				$errors[] = "Custom Image was selected but no image was uploaded - please choose an image.";
			} // if( $infinity23_data_array['button_type'] === 'custom' && empty( $infinity23_data_array['icon_image_url'] ) ) {

			if( empty( $infinity23_data_array['button_width'] ) || empty( $infinity23_data_array['button_height'] ) ) {
				$errors[] = "Button size is empty - please enter a width and height for the button.";
			} // if( empty( $infinity23_data_array['button_width'] ) || empty( $infinity23_data_array['button_height'] ) ) {

			// ----------------------------------------
			// save or show errors
			// ----------------------------------------

			if( empty( $errors ) ) {

				infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
				$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-whatsapp-button', 'saved' => '1' ), admin_url('admin.php') );
				infinity23_safe_redirect( $infinity23_redirect_url );

			} else {
				$infinity23_error_message = $errors;
			} // if( empty( $errors ) ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce( $_POST['infinity23_whatsapp_button_nonce'], 'infinity23_whatsapp_button_action' ) ) {

	} // elseif( isset( $_POST['infinity23_whatsapp_button_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code                   = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_chat_number = infinity23_pro_get_data( 'chat_number', $infinity23_data_array, $infinity23_defaults );
	$infinity23_chat_message = infinity23_pro_get_data( 'chat_message', $infinity23_data_array, $infinity23_defaults );
	$infinity23_button_type = infinity23_pro_get_data( 'button_type', $infinity23_data_array, $infinity23_defaults );
	$infinity23_icon_image_url = infinity23_pro_get_data( 'icon_image_url', $infinity23_data_array, $infinity23_defaults );
	$infinity23_custom_icon_image_url = infinity23_pro_get_data( 'custom_icon_image_url', $infinity23_data_array, $infinity23_defaults );

	if( empty( $infinity23_custom_icon_image_url ) ) {
		$infinity23_custom_icon_image_url = $infinity23_icon_image_url;
	} // if( empty( $infinity23_custom_icon_image_url ) ) {

	$infinity23_button_width = infinity23_pro_get_data( 'button_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_button_height = infinity23_pro_get_data( 'button_height', $infinity23_data_array, $infinity23_defaults );
	$infinity23_open_new_tab = infinity23_pro_get_data( 'open_new_tab', $infinity23_data_array, $infinity23_defaults );
	$infinity23_button_position                 = infinity23_pro_get_data( 'button_position', $infinity23_data_array, $infinity23_defaults );

	if( empty( $infinity23_button_position ) ) {
		$infinity23_button_position = infinity23_convert_array_to_json( infinity23_parse_button_position_to_struct( '', $infinity23_data_array, $infinity23_default_button_position ) );
	} // if( empty( $infinity23_button_position ) ) {

	$infinity23_exclude_ids                     = infinity23_pro_get_data( 'exclude_ids', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_option                  = infinity23_pro_get_data( 'exclude_option', $infinity23_data_array, $infinity23_defaults );
	$infinity23_custom_css                      = infinity23_pro_get_data( 'custom_css', $infinity23_data_array, $infinity23_defaults );
	$infinity23_custom_css_active               = intval( infinity23_pro_get_data( 'custom_css_active', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_whatsapp_button_hide_menu = intval( infinity23_pro_get_data( 'whatsapp_button_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

	// ----------------------------------------
	// preset image urls for admin preview
	// ----------------------------------------

	$infinity23_btn_url_1 = infinity23_plugin_url( 'images/whatsapp-button/whatsapp-button1.svg' );
	$infinity23_btn_url_2 = infinity23_plugin_url( 'images/whatsapp-button/whatsapp-button2.svg' );
	$infinity23_btn_url_3 = infinity23_plugin_url( 'images/whatsapp-button/whatsapp-button3.svg' );

	$infinity23_custom_css_example = '';
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* moves the button and gives it a dashed outline */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-whatsapp-button-wrap {' . "\n";
	$infinity23_custom_css_example .= '    right: 60px !important;' . "\n";
	$infinity23_custom_css_example .= '    bottom: 60px !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* changes the button size and border style */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-whatsapp-button-wrap .infinity23-whatsapp-button {' . "\n";
	$infinity23_custom_css_example .= '    width: 60px !important;' . "\n";
	$infinity23_custom_css_example .= '    height: 60px !important;' . "\n";
	$infinity23_custom_css_example .= '    background-size: 60px 60px !important;' . "\n";
	$infinity23_custom_css_example .= '}';

?>

<?php
// =================================================================================================================
// Output the plugin form data to page
// =================================================================================================================
?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp whatsapp-button-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Whatsapp Button Settings</h1>
		<p>Below you will be able to edit and make changes to the whatsapp button output:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_whatsapp_button_activate_action', 'infinity23_whatsapp_button_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Whatsapp Button</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

			<div class="section-card" data-card-default="open">

				<h2>Basic Settings:</h2>

				<div class="form-line">
					<label for="chat_number">Whatsapp Number: </label>
					<input type="text" id="chat_number" class="medium-input infinity23-phone-number-field" name="chat_number" value="<?php echo esc_attr( $infinity23_chat_number ); ?>" />

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Enter the whatsapp number in international format - no plus sign, no leading zero, no dashes or spaces.
							<br /><br />
							<strong>Usage Examples:</strong>
							<ul>
								<li>Israel - <span class="code">972521234567</span></li>
								<li>USA - <span class="code">12125551234</span></li>
								<li>UK - <span class="code">447911123456</span></li>
							</ul>
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line">
					<label for="chat_message">Pre-filled Message: </label>
					<input type="text" id="chat_message" class="very-long-input" name="chat_message" value="<?php echo esc_attr( $infinity23_chat_message ); ?>" />

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Optional. This text will be pre-filled in the whatsapp message field when the user opens the chat.
							<br /><br />
							<strong>Example:</strong>
							<ul>
								<li><span class="code">Hi, I found you on the website and I would like to get more information.</span></li>
							</ul>
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="open_new_tab" name="open_new_tab" value="1" <?php if( $infinity23_open_new_tab == 1 ) { echo 'checked'; } ?> />
					<label for="open_new_tab">Open whatsapp link in new tab</label>
				</div><!--form-line-->

				<h3>Button Image:</h3>

				<div class="form-line">
					<label for="whatsapp_button_type">Button Type: </label>
					<select name="whatsapp_button_type" id="whatsapp_button_type">
						<option value="button1" <?php if( $infinity23_button_type === 'button1' ) { echo 'selected'; } ?>>Whatsapp Button 1</option>
						<option value="button2" <?php if( $infinity23_button_type === 'button2' ) { echo 'selected'; } ?>>Whatsapp Button 2</option>
						<option value="button3" <?php if( $infinity23_button_type === 'button3' ) { echo 'selected'; } ?>>Whatsapp Button 3</option>
						<option value="custom" <?php if( $infinity23_button_type === 'custom' ) { echo 'selected'; } ?>>Custom Image</option>
					</select>
				</div><!--form-line-->

				<div class="form-line whatsapp-btn-type-select-wrap">
					<span class="whatsapp-btn-type-preview">
						<img src="<?php echo esc_url( $infinity23_btn_url_1 ); ?>" data-type="button1" alt="Button 1" />
						<img src="<?php echo esc_url( $infinity23_btn_url_2 ); ?>" data-type="button2" alt="Button 2" />
						<img src="<?php echo esc_url( $infinity23_btn_url_3 ); ?>" data-type="button3" alt="Button 3" />
						<span class="whatsapp-custom-upload-inline infinity23-upload-wrap">
							<img class="infinity23-upload-preview" data-type="custom" src="<?php echo esc_url( $infinity23_custom_icon_image_url ); ?>" alt="Custom" />
							<input type="button" class="infinity23-upload-btn infinity23-upload-btn-style" value="Choose Image..." />
							<input type="text" id="custom_icon_image_url" class="infinity23-upload-input very-long-input infinity23-whatsapp-custom-url-hidden" name="custom_icon_image_url" value="<?php echo esc_url( $infinity23_custom_icon_image_url ); ?>" />
						</span><!--whatsapp-custom-upload-inline-->
					</span><!--whatsapp-btn-type-preview-->
				</div><!--form-line-->

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

			</div><!--section-card-->

			<div class="section-card" data-card-default="open">
				<h2>Responsive Display Settings:</h2>

				<?php infinity23_advanced_button_position_field( $infinity23_button_position ); ?>

			</div><!--section-card-->

			<div class="section-card" data-card-default="open">
				<h2>Include / Exclude Pages By ID:</h2>

				<?php infinity23_exclude_ids_field( $infinity23_exclude_ids, $infinity23_exclude_option ); ?>

			</div><!--section-card-->

			<?php infinity23_custom_css_section_card( $infinity23_custom_css, $infinity23_custom_css_active, $infinity23_custom_css_example, 'custom_css', $infinity23_module_display_name ); ?>

			<?php infinity23_menu_visibility_field( 'whatsapp_button_hide_menu', $infinity23_whatsapp_button_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_whatsapp_button_action', 'infinity23_whatsapp_button_nonce' ); ?>

			<input type="submit" name="submit_whatsapp_button" class='submit-btn' value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="additional-data-warp" data-card-default="open">

		<h2>Additional Data</h2>

		<h3>Button CSS Classes <small>(Allow to restyle or replace the plugin output from your own CSS)</small></h3>

		<p class="additional-data-intro">These are the classes printed on the front-end output. Target them from your theme CSS to override the plugin design or to replace the button image with your own.</p>

		<ul>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-whatsapp-button-wrap</span> - the outer wrap printed in the footer that holds the button and its position on the screen.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-whatsapp-button</span> - the button itself, holding the size and the button image.</p>
			</li>
		</ul>

		<button type="button" class="infinity23-code-example-btn">Code Example</button>
		<div class="infinity23-code-example-block">
			<pre><code><span class="infinity23-code-comment">// moves the button on the screen</span>
<span class="infinity23-code-hl">.infinity23-whatsapp-button-wrap</span> {
    right: 60px !important;
    bottom: 60px !important;
}

<span class="infinity23-code-comment">// changes the button size, or swap the image with your own</span>
.infinity23-whatsapp-button-wrap <span class="infinity23-code-hl">.infinity23-whatsapp-button</span> {
    width: 60px !important;
    height: 60px !important;
    background-size: 60px 60px !important;
    /* background-image: url(my-icon.svg) !important; */
}</code></pre>
		</div><!--infinity23-code-example-block-->

	</div><!--additional-data-warp-->

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_whatsapp_button_reset_action', 'infinity23_whatsapp_button_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
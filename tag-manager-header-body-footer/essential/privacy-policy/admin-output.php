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
	// reset all privacy policy settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_privacy_policy_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_privacy_policy_reset_nonce'] ) ), 'infinity23_privacy_policy_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_privacy_policy_reset_nonce'] ) ) {

	// ----------------------------------------
	// code toggle activation form submit
	// ----------------------------------------

	if( isset( $_POST['infinity23_privacy_policy_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_privacy_policy_activate_nonce'] ) ), 'infinity23_privacy_policy_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'privacy_policy', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "The privacy policy bar was activated successfully";
			} else {
				infinity23_module_deactivate_redirect( $infinity23_module_display_name );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_privacy_policy_activate_nonce'] ) ) {

	// ----------------------------------------
	// Submit the main form with data
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_privacy_policy_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_privacy_policy_nonce'] ) ), 'infinity23_privacy_policy_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_data_array['message_text']                   = infinity23_pro_field_sanitize( 'message_text', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['privacy_link_text']              = infinity23_pro_field_sanitize( 'privacy_link_text', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['accept_button_text']             = infinity23_pro_field_sanitize( 'accept_button_text', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['privacy_policy_url']             = infinity23_pro_field_sanitize( 'privacy_policy_url', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['privacy_policy_url_new_tab']     = infinity23_pro_field_sanitize( 'privacy_policy_url_new_tab', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['bar_background_color']           = infinity23_pro_field_sanitize( 'bar_background_color', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['bar_text_color']                 = infinity23_pro_field_sanitize( 'bar_text_color', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['button_background_color']        = infinity23_pro_field_sanitize( 'button_background_color', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['button_text_color']              = infinity23_pro_field_sanitize( 'button_text_color', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['cookie_expiry_days']             = infinity23_pro_field_sanitize( 'cookie_expiry_days', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['hide_on_desktop_checkbox']       = infinity23_pro_field_sanitize( 'hide_on_desktop_checkbox', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['bar_position_vertical']          = infinity23_pro_field_sanitize( 'bar_position_vertical', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_bar_vertical_spacing_value = infinity23_post_sanitize('bar_vertical_spacing', 'text');
			$infinity23_bar_vertical_spacing_unit = infinity23_post_sanitize('bar_vertical_spacing_unit', 'text');
			if( !in_array( $infinity23_bar_vertical_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
				$infinity23_bar_vertical_spacing_unit = 'px';
			} // if( !in_array( $infinity23_bar_vertical_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
			$infinity23_data_array['bar_vertical_spacing']           = $infinity23_bar_vertical_spacing_value . $infinity23_bar_vertical_spacing_unit;
			$infinity23_data_array['bar_position_horizontal']        = infinity23_pro_field_sanitize( 'bar_position_horizontal', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_bar_horizontal_spacing_value = infinity23_post_sanitize('bar_horizontal_spacing', 'text');
			$infinity23_bar_horizontal_spacing_unit = infinity23_post_sanitize('bar_horizontal_spacing_unit', 'text');
			if( !in_array( $infinity23_bar_horizontal_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
				$infinity23_bar_horizontal_spacing_unit = 'px';
			} // if( !in_array( $infinity23_bar_horizontal_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
			$infinity23_data_array['bar_horizontal_spacing']         = $infinity23_bar_horizontal_spacing_value . $infinity23_bar_horizontal_spacing_unit;
			$infinity23_data_array['bar_width']                      = infinity23_pro_field_sanitize( 'bar_width', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['bar_width_unit']                 = infinity23_pro_field_sanitize( 'bar_width_unit', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['z_index']                        = infinity23_pro_field_sanitize( 'z_index', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['hide_on_mobile_checkbox']        = infinity23_pro_field_sanitize( 'hide_on_mobile_checkbox', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['mobile_mode'] = infinity23_pro_field_sanitize( 'mobile_mode', $infinity23_existing_data_array, $infinity23_defaults );

			if( !in_array( $infinity23_data_array['mobile_mode'], array( 'inherit', 'custom' ), true ) ) {
				$infinity23_data_array['mobile_mode'] = 'inherit';
			} // if( !in_array( $infinity23_data_array['mobile_mode'], array( 'inherit', 'custom' ), true ) ) {

			$infinity23_data_array['mobile_breakpoint']              = infinity23_pro_field_sanitize( 'mobile_breakpoint', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['mobile_bar_position_vertical']   = infinity23_pro_field_sanitize( 'mobile_bar_position_vertical', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_mobile_bar_vertical_spacing_value = infinity23_post_sanitize('mobile_bar_vertical_spacing', 'text');
			$infinity23_mobile_bar_vertical_spacing_unit = infinity23_post_sanitize('mobile_bar_vertical_spacing_unit', 'text');
			if( !in_array( $infinity23_mobile_bar_vertical_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
				$infinity23_mobile_bar_vertical_spacing_unit = 'px';
			} // if( !in_array( $infinity23_mobile_bar_vertical_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
			$infinity23_data_array['mobile_bar_vertical_spacing']    = $infinity23_mobile_bar_vertical_spacing_value . $infinity23_mobile_bar_vertical_spacing_unit;
			$infinity23_data_array['mobile_bar_position_horizontal'] = infinity23_pro_field_sanitize( 'mobile_bar_position_horizontal', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_mobile_bar_horizontal_spacing_value = infinity23_post_sanitize('mobile_bar_horizontal_spacing', 'text');
			$infinity23_mobile_bar_horizontal_spacing_unit = infinity23_post_sanitize('mobile_bar_horizontal_spacing_unit', 'text');
			if( !in_array( $infinity23_mobile_bar_horizontal_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
				$infinity23_mobile_bar_horizontal_spacing_unit = 'px';
			} // if( !in_array( $infinity23_mobile_bar_horizontal_spacing_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
			$infinity23_data_array['mobile_bar_horizontal_spacing']  = $infinity23_mobile_bar_horizontal_spacing_value . $infinity23_mobile_bar_horizontal_spacing_unit;
			$infinity23_data_array['mobile_bar_width']               = infinity23_pro_field_sanitize( 'mobile_bar_width', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['mobile_bar_width_unit']          = infinity23_pro_field_sanitize( 'mobile_bar_width_unit', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['exclude_ids']                    = infinity23_pro_field_sanitize( 'exclude_ids', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['exclude_option'] = infinity23_post_sanitize('exclude_ids_option', 'text');

			if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {
				$infinity23_data_array['exclude_option'] = 'exclude';
			} // if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {

			$infinity23_data_array['custom_css']                     = infinity23_pro_field_sanitize( 'custom_css', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['custom_css_active']               = infinity23_pro_field_sanitize( 'custom_css_active', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['privacy_policy_hide_menu']       = infinity23_pro_field_sanitize( 'privacy_policy_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			// ----------------------------------------
			// validation checks
			// ----------------------------------------

			$errors = array();

			if( empty( $infinity23_data_array['message_text'] ) ) {
				$errors[] = "Message text is empty";
			} // if( empty( $infinity23_data_array['message_text'] ) ) {

			if( empty( $infinity23_data_array['accept_button_text'] ) ) {
				$errors[] = "Accept button text is empty";
			} // if( empty( $infinity23_data_array['accept_button_text'] ) ) {

			if( empty( $infinity23_data_array['cookie_expiry_days'] ) ) {
				$errors[] = "Cookie expiry days is empty";
			} // if( empty( $infinity23_data_array['cookie_expiry_days'] ) ) {

			// ----------------------------------------
			// save or show errors
			// ----------------------------------------

			if( empty( $errors ) ) {

				infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
				$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-privacy-policy', 'saved' => '1' ), admin_url('admin.php') );
				infinity23_safe_redirect( $infinity23_redirect_url );

			} else {
				$infinity23_error_message = $errors;
			} // if( empty( $errors ) ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce( $_POST['infinity23_privacy_policy_nonce'], 'infinity23_privacy_policy_action' ) ) {

	} // elseif( isset( $_POST['infinity23_privacy_policy_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code                  = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_bar_background_color = infinity23_pro_get_data( 'bar_background_color', $infinity23_data_array, $infinity23_defaults );
	$infinity23_button_background_color = infinity23_pro_get_data( 'button_background_color', $infinity23_data_array, $infinity23_defaults );
	$infinity23_button_text_color = infinity23_pro_get_data( 'button_text_color', $infinity23_data_array, $infinity23_defaults );
	$infinity23_bar_text_color = infinity23_pro_get_data( 'bar_text_color', $infinity23_data_array, $infinity23_defaults );
	$infinity23_bar_position_vertical = infinity23_pro_get_data( 'bar_position_vertical', $infinity23_data_array, $infinity23_defaults );
	$infinity23_bar_vertical_spacing = infinity23_pro_get_data( 'bar_vertical_spacing', $infinity23_data_array, $infinity23_defaults );
	$infinity23_bar_position_horizontal = infinity23_pro_get_data( 'bar_position_horizontal', $infinity23_data_array, $infinity23_defaults );
	$infinity23_bar_horizontal_spacing = infinity23_pro_get_data( 'bar_horizontal_spacing', $infinity23_data_array, $infinity23_defaults );
	$infinity23_bar_vertical_spacing_split = infinity23_split_size_unit_value( $infinity23_bar_vertical_spacing );
	$infinity23_bar_horizontal_spacing_split = infinity23_split_size_unit_value( $infinity23_bar_horizontal_spacing );
	$infinity23_bar_width = infinity23_pro_get_data( 'bar_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_bar_width_unit = infinity23_pro_get_data( 'bar_width_unit', $infinity23_data_array, $infinity23_defaults );
	$infinity23_cookie_expiry_days = infinity23_pro_get_data( 'cookie_expiry_days', $infinity23_data_array, $infinity23_defaults );

	$infinity23_privacy_policy_url = infinity23_pro_get_data( 'privacy_policy_url', $infinity23_data_array, $infinity23_defaults );
	if( !isset( $infinity23_data_array['privacy_policy_url'] ) ) {
		$infinity23_privacy_policy_url = infinity23_privacy_policy_default_page_url();
	} // if( !isset( $infinity23_data_array['privacy_policy_url'] ) ) {

	$infinity23_privacy_policy_url_new_tab = infinity23_pro_get_data( 'privacy_policy_url_new_tab', $infinity23_data_array, $infinity23_defaults );
	$infinity23_message_text = infinity23_pro_get_data( 'message_text', $infinity23_data_array, $infinity23_defaults );
	$infinity23_privacy_link_text = infinity23_pro_get_data( 'privacy_link_text', $infinity23_data_array, $infinity23_defaults );
	$infinity23_accept_button_text = infinity23_pro_get_data( 'accept_button_text', $infinity23_data_array, $infinity23_defaults );
	$infinity23_hide_on_mobile_checkbox = infinity23_pro_get_data( 'hide_on_mobile_checkbox', $infinity23_data_array, $infinity23_defaults );
	$infinity23_hide_on_desktop_checkbox = infinity23_pro_get_data( 'hide_on_desktop_checkbox', $infinity23_data_array, $infinity23_defaults );
	$infinity23_mobile_mode = infinity23_pro_get_data( 'mobile_mode', $infinity23_data_array, $infinity23_defaults );
	$infinity23_mobile_bar_position_vertical = infinity23_pro_get_data( 'mobile_bar_position_vertical', $infinity23_data_array, $infinity23_defaults );
	$infinity23_mobile_bar_vertical_spacing = infinity23_pro_get_data( 'mobile_bar_vertical_spacing', $infinity23_data_array, $infinity23_defaults );
	$infinity23_mobile_bar_position_horizontal = infinity23_pro_get_data( 'mobile_bar_position_horizontal', $infinity23_data_array, $infinity23_defaults );
	$infinity23_mobile_bar_horizontal_spacing = infinity23_pro_get_data( 'mobile_bar_horizontal_spacing', $infinity23_data_array, $infinity23_defaults );
	$infinity23_mobile_bar_vertical_spacing_split = infinity23_split_size_unit_value( $infinity23_mobile_bar_vertical_spacing );
	$infinity23_mobile_bar_horizontal_spacing_split = infinity23_split_size_unit_value( $infinity23_mobile_bar_horizontal_spacing );
	$infinity23_mobile_bar_width = infinity23_pro_get_data( 'mobile_bar_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_mobile_bar_width_unit = infinity23_pro_get_data( 'mobile_bar_width_unit', $infinity23_data_array, $infinity23_defaults );
	$infinity23_mobile_breakpoint = infinity23_pro_get_data( 'mobile_breakpoint', $infinity23_data_array, $infinity23_defaults );
	$infinity23_z_index = infinity23_pro_get_data( 'z_index', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_ids                    = infinity23_pro_get_data( 'exclude_ids', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_option                 = infinity23_pro_get_data( 'exclude_option', $infinity23_data_array, $infinity23_defaults );
	$infinity23_custom_css                     = infinity23_pro_get_data( 'custom_css', $infinity23_data_array, $infinity23_defaults );
	$infinity23_custom_css_active              = intval( infinity23_pro_get_data( 'custom_css_active', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_privacy_policy_hide_menu = intval( infinity23_pro_get_data( 'privacy_policy_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_custom_css_example = '';
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* changes the bar background and its spacing from the edges */</span>' . "\n";
	$infinity23_custom_css_example .= '#infinity23-privacy-bar {' . "\n";
	$infinity23_custom_css_example .= '    background-color: #000 !important;' . "\n";
	$infinity23_custom_css_example .= '    padding: 20px 30px !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* changes the message text and the privacy policy link */</span>' . "\n";
	$infinity23_custom_css_example .= '#infinity23-privacy-bar .infinity23-privacy-bar-text {' . "\n";
	$infinity23_custom_css_example .= '    font-size: 14px !important;' . "\n";
	$infinity23_custom_css_example .= '    color: #fff !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* changes the accept button */</span>' . "\n";
	$infinity23_custom_css_example .= '#infinity23-privacy-bar .infinity23-privacy-bar-btn {' . "\n";
	$infinity23_custom_css_example .= '    background-color: #00bc3c !important;' . "\n";
	$infinity23_custom_css_example .= '    color: #fff !important;' . "\n";
	$infinity23_custom_css_example .= '    border-radius: 0px !important;' . "\n";
	$infinity23_custom_css_example .= '}';

?>

<?php
// =================================================================================================================
// Output the plugin form data to page
// =================================================================================================================
?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp privacy-policy-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Privacy Policy / Cookie Consent Settings</h1>
		<p>Below you will be able to edit and make changes to the cookie consent bar output:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_privacy_policy_activate_action', 'infinity23_privacy_policy_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Cookie Consent Bar</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

			<div class="section-card" data-card-default="open">

				<h2>Basic Settings:</h2>

				<h3>Bar Text:</h3>

				<div class="form-line">
					<label for="message_text">Cookie Message Text: </label>
					<br /><mark><small>Use <strong>{privacy-page-dont-edit}</strong> as a placeholder where the privacy policy link will appear.</small></mark>
					<br /><br />
					<textarea id="message_text" name="message_text" rows="4" class="very-long-input"><?php echo esc_textarea( $infinity23_message_text ); ?></textarea>
				</div><!--form-line-->

				<div class="form-line">
					<label for="privacy_link_text">Privacy Policy Link Text: </label>
					<input type="text" id="privacy_link_text" name="privacy_link_text" class="long-input" value="<?php echo esc_attr( $infinity23_privacy_link_text ); ?>" />
				</div><!--form-line-->

				<h3>Bar/Box Design:</h3>

				<div class="form-line background-color-line">
					<label for="bar_background_color">Bar Background Color: </label>
					<input type="text" id="bar_background_color" name="bar_background_color" class="infinity23-color-picker" value="<?php echo esc_attr( $infinity23_bar_background_color ); ?>" />
				</div><!--form-line-->

				<div class="form-line background-color-line">
					<label for="bar_text_color">Bar Text Color: </label>
					<input type="text" id="bar_text_color" name="bar_text_color" class="infinity23-color-picker" value="<?php echo esc_attr( $infinity23_bar_text_color ); ?>" />
				</div><!--form-line-->

				<h3>Button:</h3>

				<div class="form-line">
					<label for="accept_button_text">Accept Button Text: </label>
					<input type="text" id="accept_button_text" name="accept_button_text" class="medium-input" value="<?php echo esc_attr( $infinity23_accept_button_text ); ?>" />
				</div><!--form-line-->

				<div class="form-line background-color-line">
					<label for="button_background_color">Accept Button Background Color: </label>
					<input type="text" id="button_background_color" name="button_background_color" class="infinity23-color-picker" value="<?php echo esc_attr( $infinity23_button_background_color ); ?>" />
				</div><!--form-line-->

				<div class="form-line background-color-line">
					<label for="button_text_color">Accept Button Text Color: </label>
					<input type="text" id="button_text_color" name="button_text_color" class="infinity23-color-picker" value="<?php echo esc_attr( $infinity23_button_text_color ); ?>" />
				</div><!--form-line-->

				<h3>Privacy Policy Page:</h3>

				<div class="form-line">
					<label for="privacy_policy_url">Privacy Policy Page URL: </label>
					<br />
					<input type="text" id="privacy_policy_url" name="privacy_policy_url" class="very-long-input" value="<?php echo esc_url( $infinity23_privacy_policy_url ); ?>" />

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Loaded automatically from the page set in WordPress Settings, or override it here. Leave empty to hide the link.
						</div>
						?
					</div><!--backtest-field-help-icon-->

					<small class="default-img-url">It's recommended to also set the page in <a href="<?php echo esc_url( admin_url( 'options-privacy.php' ) ); ?>" target="_blank">WordPress Settings</a>.</small>

				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="privacy_policy_url_new_tab" name="privacy_policy_url_new_tab" value="1" <?php if( $infinity23_privacy_policy_url_new_tab == 1 ) { echo 'checked'; } ?> />
					<label for="privacy_policy_url_new_tab">Open privacy policy link in new tab</label>
				</div><!--form-line-->

				<h3>Consent Expiry Time (Saved In Visitor's Browser):</h3>

				<div class="form-line">
					<label for="cookie_expiry_days">Cookie Expiry (days): </label>
					<input type="text" id="cookie_expiry_days" name="cookie_expiry_days" class="short-input" value="<?php echo esc_attr( $infinity23_cookie_expiry_days ); ?>" />

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							How many days a visitor's choice is remembered before the bar shows again.
							<br /><br />
							<strong>Examples:</strong>
							<ul>
								<li>1 year - <span class="code">365</span></li>
								<li>6 months - <span class="code">180</span></li>
								<li>1 month - <span class="code">30</span></li>
							</ul>
						</div>
						?
					</div><!--backtest-field-help-icon-->

				</div><!--form-line-->

			</div><!--section-card-->

			<div class="section-card" data-card-default="open">
				<h2>Responsive Display Settings:</h2>

				<div class="infinity23-position-tabs-wrap" id="infinity23-position-details-privacy">

					<div class="infinity23-position-tabs">
						<button type="button" class="infinity23-position-tab-btn infinity23-position-tab-active" data-tab-target="infinity23-position-tab-desktop-privacy"><img src="<?php echo esc_url( infinity23_plugin_url( 'images/plugin-icons/desktop-icon.svg' ) ); ?>" class="infinity23-position-tab-icon" alt="" /> Desktop</button>
						<button type="button" class="infinity23-position-tab-btn" data-tab-target="infinity23-position-tab-mobile-privacy"><img src="<?php echo esc_url( infinity23_plugin_url( 'images/plugin-icons/mobile-icon.svg' ) ); ?>" class="infinity23-position-tab-icon" alt="" /> Mobile</button>
					</div><!--infinity23-position-tabs-->

					<div class="infinity23-position-tab-content" id="infinity23-position-tab-desktop-privacy">

						<div class="form-line checkbox-line">
							<input type="checkbox" id="hide_on_desktop_checkbox" name="hide_on_desktop_checkbox" value="1" <?php if( $infinity23_hide_on_desktop_checkbox == 1 ) { echo 'checked'; } ?> />
							<label for="hide_on_desktop_checkbox">Hide the bar on desktop</label>
						</div><!--form-line-->

						<div class="form-line">
							<label for="bar_position_vertical">Vertical Position: </label>
							<select name="bar_position_vertical" id="bar_position_vertical">
								<option value="bottom" <?php if( $infinity23_bar_position_vertical == 'bottom' ) { echo 'selected'; } ?>>Bottom</option>
								<option value="top" <?php if( $infinity23_bar_position_vertical == 'top' ) { echo 'selected'; } ?>>Top</option>
							</select>
							<label for="bar_vertical_spacing">x</label>
							<input type="text" id="bar_vertical_spacing" class='very-short-input' name="bar_vertical_spacing" value="<?php echo esc_attr( $infinity23_bar_vertical_spacing_split['value'] ); ?>" />
							<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
					<?php echo infinity23_unit_select_field( 'bar_vertical_spacing_unit', $infinity23_bar_vertical_spacing_split['unit'] ); ?>

							<div class="backtest-field-help-icon">
								<div class="data-help-content">
									Controls how far the bar is placed from the top or bottom edge of the screen.
									<br /><br />
									<strong>Usage Examples:</strong>
									<ul>
										<li>Fixed distance - <span class="code">20px</span></li>
										<li>Flush to edge - <span class="code">0px</span></li>
									</ul>
								</div>
								?
							</div><!--backtest-field-help-icon-->
						</div><!--form-line-->

						<div class="form-line">
							<label for="bar_position_horizontal">Horizontal Position: </label>
							<select name="bar_position_horizontal" id="bar_position_horizontal">
								<option value="left" <?php if( $infinity23_bar_position_horizontal == 'left' ) { echo 'selected'; } ?>>Left</option>
								<option value="right" <?php if( $infinity23_bar_position_horizontal == 'right' ) { echo 'selected'; } ?>>Right</option>
							</select>
							<label for="bar_horizontal_spacing">x</label>
							<input type="text" id="bar_horizontal_spacing" class='very-short-input' name="bar_horizontal_spacing" value="<?php echo esc_attr( $infinity23_bar_horizontal_spacing_split['value'] ); ?>" />
							<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
					<?php echo infinity23_unit_select_field( 'bar_horizontal_spacing_unit', $infinity23_bar_horizontal_spacing_split['unit'] ); ?>

							<div class="backtest-field-help-icon">
								<div class="data-help-content">
									Controls how far the bar is placed from the left or right edge of the screen.
									<br /><br />
									<strong>Usage Examples:</strong>
									<ul>
										<li>Fixed distance - <span class="code">20px</span></li>
										<li>Flush to edge - <span class="code">0px</span></li>
									</ul>
								</div>
								?
							</div><!--backtest-field-help-icon-->
						</div><!--form-line-->

						<div class="form-line">
							<label for="z_index">Z-index: </label>
							<input type="text" id="z_index" class='short-input' name="z_index" value="<?php echo esc_attr( $infinity23_z_index ); ?>" />

							<div class="backtest-field-help-icon">
								<div class="data-help-content">
									Z-index controls the stacking order of elements on the page (allow to show elements behind or in front each other).
									<br /><br />
									If another element appears on top of the cookie bar, increase this number.
									<br /><br />
									If you want other elements to appear in front of the bar (like a popup), play with the number and make it smaller.
								</div>
								?
							</div><!--backtest-field-help-icon-->
						</div><!--form-line-->

						<h3>Box Size:</h3>

						<div class="form-line">
							<label for="bar_width">Bar Width: </label>
							<input type="text" id="bar_width" name="bar_width" class="very-short-input" value="<?php echo esc_attr( $infinity23_bar_width ); ?>" />
							<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
					<?php echo infinity23_unit_select_field( 'bar_width_unit', $infinity23_bar_width_unit ); ?>
						</div><!--form-line-->

					</div><!--infinity23-position-tab-content-->

					<div class="infinity23-position-tab-content infinity23-position-tab-hidden" id="infinity23-position-tab-mobile-privacy">

						<div class="form-line checkbox-line">
							<input type="checkbox" id="hide_on_mobile_checkbox" name="hide_on_mobile_checkbox" value="1" <?php if( $infinity23_hide_on_mobile_checkbox == 1 ) { echo 'checked'; } ?> />
							<label for="hide_on_mobile_checkbox">Hide the bar on mobile</label>
						</div><!--form-line-->

						<div class="form-line">
							<label for="mobile_mode">Mobile Position: </label>
							<select name="mobile_mode" id="mobile_mode" class="infinity23-position-toggle-select" data-target="infinity23-position-mobile-custom-privacy">
								<option value="inherit" <?php if( $infinity23_mobile_mode === 'inherit' ) { echo 'selected'; } ?>>Inherit From Desktop</option>
								<option value="custom" <?php if( $infinity23_mobile_mode === 'custom' ) { echo 'selected'; } ?>>Choose Different Position</option>
							</select>
						</div><!--form-line-->

						<div class="infinity23-position-mobile-custom-wrap <?php if( $infinity23_mobile_mode !== 'custom' ) { echo 'infinity23-position-hidden'; } ?>" id="infinity23-position-mobile-custom-privacy">

							<div class="form-line">
								<label for="mobile_breakpoint">Mobile breaking point: </label>
								<input type="text" id="mobile_breakpoint" name="mobile_breakpoint" class="very-short-input" value="<?php echo esc_attr( $infinity23_mobile_breakpoint ); ?>" />
								<small>PX</small>

								<div class="backtest-field-help-icon">
									<div class="data-help-content">
										The screen width where the site switches between desktop and mobile.
										<br /><br />
										Below this width, the Mobile settings are used instead of the Desktop ones when choosing different positioning selected
									</div>
									?
								</div><!--backtest-field-help-icon-->
							</div><!--form-line-->

							<div class="form-line">
								<label for="mobile_bar_position_vertical">Vertical Position: </label>
								<select name="mobile_bar_position_vertical" id="mobile_bar_position_vertical">
									<option value="bottom" <?php if( $infinity23_mobile_bar_position_vertical == 'bottom' ) { echo 'selected'; } ?>>Bottom</option>
									<option value="top" <?php if( $infinity23_mobile_bar_position_vertical == 'top' ) { echo 'selected'; } ?>>Top</option>
								</select>
								<label for="mobile_bar_vertical_spacing">x</label>
								<input type="text" id="mobile_bar_vertical_spacing" class='very-short-input' name="mobile_bar_vertical_spacing" value="<?php echo esc_attr( $infinity23_mobile_bar_vertical_spacing_split['value'] ); ?>" />
								<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
					<?php echo infinity23_unit_select_field( 'mobile_bar_vertical_spacing_unit', $infinity23_mobile_bar_vertical_spacing_split['unit'] ); ?>

								<div class="backtest-field-help-icon">
									<div class="data-help-content">
										Controls how far the bar is placed from the top or bottom edge of the screen on mobile.
										<br /><br />
										<strong>Usage Examples:</strong>
										<ul>
											<li>Fixed distance - <span class="code">20px</span></li>
											<li>Flush to edge - <span class="code">0px</span></li>
										</ul>
									</div>
									?
								</div><!--backtest-field-help-icon-->
							</div><!--form-line-->

							<div class="form-line">
								<label for="mobile_bar_position_horizontal">Horizontal Position: </label>
								<select name="mobile_bar_position_horizontal" id="mobile_bar_position_horizontal">
									<option value="left" <?php if( $infinity23_mobile_bar_position_horizontal == 'left' ) { echo 'selected'; } ?>>Left</option>
									<option value="right" <?php if( $infinity23_mobile_bar_position_horizontal == 'right' ) { echo 'selected'; } ?>>Right</option>
								</select>
								<label for="mobile_bar_horizontal_spacing">x</label>
								<input type="text" id="mobile_bar_horizontal_spacing" class='very-short-input' name="mobile_bar_horizontal_spacing" value="<?php echo esc_attr( $infinity23_mobile_bar_horizontal_spacing_split['value'] ); ?>" />
								<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
					<?php echo infinity23_unit_select_field( 'mobile_bar_horizontal_spacing_unit', $infinity23_mobile_bar_horizontal_spacing_split['unit'] ); ?>

								<div class="backtest-field-help-icon">
									<div class="data-help-content">
										Controls how far the bar is placed from the left or right edge of the screen on mobile.
										<br /><br />
										<strong>Usage Examples:</strong>
										<ul>
											<li>Fixed distance - <span class="code">20px</span></li>
											<li>Flush to edge - <span class="code">0px</span></li>
										</ul>
									</div>
									?
								</div><!--backtest-field-help-icon-->
							</div><!--form-line-->

							<h3>Box Size:</h3>

							<div class="form-line">
								<label for="mobile_bar_width">Bar Width: </label>
								<input type="text" id="mobile_bar_width" name="mobile_bar_width" class="very-short-input" value="<?php echo esc_attr( $infinity23_mobile_bar_width ); ?>" />
								<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
					<?php echo infinity23_unit_select_field( 'mobile_bar_width_unit', $infinity23_mobile_bar_width_unit ); ?>
							</div><!--form-line-->

						</div><!--infinity23-position-mobile-custom-wrap-->

					</div><!--infinity23-position-tab-content-->

				</div><!--infinity23-position-tabs-wrap-->

			</div><!--section-card-->

			<div class="section-card" data-card-default="open">
				<h2>Include / Exclude Pages By ID:</h2>

				<?php infinity23_exclude_ids_field( $infinity23_exclude_ids, $infinity23_exclude_option ); ?>

			</div><!--section-card-->

			<?php infinity23_custom_css_section_card( $infinity23_custom_css, $infinity23_custom_css_active, $infinity23_custom_css_example, 'custom_css', $infinity23_module_display_name ); ?>

			<?php infinity23_menu_visibility_field( 'privacy_policy_hide_menu', $infinity23_privacy_policy_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_privacy_policy_action', 'infinity23_privacy_policy_nonce' ); ?>

			<input type="submit" name="submit_privacy_policy" class="submit-btn" value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="additional-data-warp" data-card-default="open">

		<h2>Additional Data</h2>

		<h3>Cookie Bar CSS Classes <small>(Allow to restyle or replace the plugin output from your own CSS)</small></h3>

		<p class="additional-data-intro">These are the names printed on the front-end output. Target them from your theme CSS to override the plugin design of the bar, the text or the accept button.</p>

		<ul>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-privacy-bar</span> - the id of the bar itself, holding the position on the screen, the width, the background color and the z-index.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-privacy-bar-text</span> - the message text inside the bar, including the privacy policy link.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-privacy-bar-btn</span> - the accept button that closes the bar and saves the visitor choice.</p>
			</li>
		</ul>

		<button type="button" class="infinity23-code-example-btn">Code Example</button>
		<div class="infinity23-code-example-block">
			<pre><code><span class="infinity23-code-comment">// changes the bar background and its spacing from the edges</span>
#<span class="infinity23-code-hl">infinity23-privacy-bar</span> {
    background-color: #000 !important;
    padding: 20px 30px !important;
}

<span class="infinity23-code-comment">// changes the message text and the privacy policy link</span>
#infinity23-privacy-bar <span class="infinity23-code-hl">.infinity23-privacy-bar-text</span> {
    font-size: 14px !important;
    color: #fff !important;
}

<span class="infinity23-code-comment">// changes the accept button</span>
#infinity23-privacy-bar <span class="infinity23-code-hl">.infinity23-privacy-bar-btn</span> {
    background-color: #00bc3c !important;
    color: #fff !important;
    border-radius: 0px !important;
}</code></pre>
		</div><!--infinity23-code-example-block-->

	</div><!--additional-data-warp-->

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_privacy_policy_reset_action', 'infinity23_privacy_policy_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
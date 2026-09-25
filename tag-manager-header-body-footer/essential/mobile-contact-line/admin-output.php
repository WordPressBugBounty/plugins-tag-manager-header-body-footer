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
	// reset all mobile contact line settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_mobile_contact_line_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_mobile_contact_line_reset_nonce'] ) ), 'infinity23_mobile_contact_line_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_mobile_contact_line_reset_nonce'] ) ) {

	// ----------------------------------------
	// code toggle activation form submit
	// ----------------------------------------

	if( isset( $_POST['infinity23_mobile_contact_line_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_mobile_contact_line_activate_nonce'] ) ), 'infinity23_mobile_contact_line_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'mobile_contact_line', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "The mobile contact line was activated successfully";
			} else {
				infinity23_module_deactivate_redirect( $infinity23_module_display_name );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_mobile_contact_line_activate_nonce'] ) ) {

	// ----------------------------------------
	// Submit the main form with data
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_mobile_contact_line_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_mobile_contact_line_nonce'] ) ), 'infinity23_mobile_contact_line_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_data_array['phone_display_checkbox']           = infinity23_pro_field_sanitize( 'phone_display_checkbox', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['phone_button_href']                = infinity23_sanitize_phone_number( infinity23_post_sanitize('phone_button_href', 'text'), true );
			$infinity23_data_array['phone_button_text']                = infinity23_pro_field_sanitize( 'phone_button_text', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['phone_background_color']           = infinity23_pro_field_sanitize( 'phone_background_color', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['phone_button_width']               = infinity23_pro_field_sanitize( 'phone_button_width', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['phone_button_position']            = infinity23_pro_field_sanitize( 'phone_button_position', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['email_display_checkbox']           = infinity23_pro_field_sanitize( 'email_display_checkbox', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_custom_contact_page_link_value       = infinity23_post_sanitize('custom_contact_page_link', 'intval');
			$infinity23_data_array['email_button_href']                = ( $infinity23_custom_contact_page_link_value == 1 ) ? infinity23_post_sanitize('email_button_href', 'url') : infinity23_post_sanitize('email_button_href', 'text');
			$infinity23_data_array['custom_contact_page_link']         = $infinity23_custom_contact_page_link_value;
			$infinity23_data_array['email_button_text']                = infinity23_pro_field_sanitize( 'email_button_text', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['email_background_color']           = infinity23_pro_field_sanitize( 'email_background_color', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['email_button_width']               = infinity23_pro_field_sanitize( 'email_button_width', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['email_button_position']            = infinity23_pro_field_sanitize( 'email_button_position', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['whatsapp_display_checkbox']        = infinity23_pro_field_sanitize( 'whatsapp_display_checkbox', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['whatsapp_button_href']             = infinity23_sanitize_phone_number( infinity23_post_sanitize('whatsapp_button_href', 'text') );
			$infinity23_data_array['whatsapp_url_message']             = infinity23_pro_field_sanitize( 'whatsapp_url_message', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['whatsapp_button_text']             = infinity23_pro_field_sanitize( 'whatsapp_button_text', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['whatsapp_background_color']        = infinity23_pro_field_sanitize( 'whatsapp_background_color', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['whatsapp_button_width']            = infinity23_pro_field_sanitize( 'whatsapp_button_width', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['whatsapp_button_position']         = infinity23_pro_field_sanitize( 'whatsapp_button_position', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['chat_display_checkbox']            = infinity23_pro_field_sanitize( 'chat_display_checkbox', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['chat_button_href']                 = infinity23_pro_field_sanitize( 'chat_button_href', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['chat_button_text']                 = infinity23_pro_field_sanitize( 'chat_button_text', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['chat_background_color']            = infinity23_pro_field_sanitize( 'chat_background_color', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['chat_button_width']                = infinity23_pro_field_sanitize( 'chat_button_width', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['chat_button_position']             = infinity23_pro_field_sanitize( 'chat_button_position', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['mobile_width']                     = infinity23_pro_field_sanitize( 'mobile_width', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['hide_on_mobile_checkbox']          = infinity23_pro_field_sanitize( 'hide_on_mobile_checkbox', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['hide_on_desktop_checkbox']         = infinity23_pro_field_sanitize( 'hide_on_desktop_checkbox', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['exclude_ids']                      = infinity23_pro_field_sanitize( 'exclude_ids', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['exclude_option'] = infinity23_post_sanitize('exclude_ids_option', 'text');

			if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {
				$infinity23_data_array['exclude_option'] = 'exclude';
			} // if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {

			$infinity23_data_array['custom_css']                       = infinity23_pro_field_sanitize( 'custom_css', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['custom_css_active']                = infinity23_pro_field_sanitize( 'custom_css_active', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['mobile_contact_line_hide_menu']    = infinity23_pro_field_sanitize( 'mobile_contact_line_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			// ----------------------------------------
			// validation checks
			// ----------------------------------------

			$errors = array();

			if( isset( $infinity23_data_array['phone_display_checkbox'] ) && $infinity23_data_array['phone_display_checkbox'] == 1 && empty( $infinity23_data_array['phone_button_href'] ) ) {
				$errors[] = "Phone button is enabled but phone number is empty.";
			} // if(

			if( isset( $infinity23_data_array['email_display_checkbox'] ) && $infinity23_data_array['email_display_checkbox'] == 1 && empty( $infinity23_data_array['email_button_href'] ) ) {
				$errors[] = "Email button is enabled but email / URL is empty.";
			} // if(

			if( isset( $infinity23_data_array['whatsapp_display_checkbox'] ) && $infinity23_data_array['whatsapp_display_checkbox'] == 1 && empty( $infinity23_data_array['whatsapp_button_href'] ) ) {
				$errors[] = "Whatsapp button is enabled but whatsapp number is empty.";
			} // if(

			// ----------------------------------------
			// save or show errors
			// ----------------------------------------

			if( empty( $errors ) ) {

				infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
				$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-mobile-contact-line', 'saved' => '1' ), admin_url('admin.php') );
				infinity23_safe_redirect( $infinity23_redirect_url );

			} else {

				$infinity23_error_message = $errors;

			} // if( empty( $errors ) ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce( $_POST['infinity23_mobile_contact_line_nonce'], 'infinity23_mobile_contact_line_action' ) ) {

	} // elseif( isset( $_POST['infinity23_mobile_contact_line_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code                = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_phone_display_checkbox = infinity23_pro_get_data( 'phone_display_checkbox', $infinity23_data_array, $infinity23_defaults );
	$infinity23_phone_button_href = infinity23_pro_get_data( 'phone_button_href', $infinity23_data_array, $infinity23_defaults );
	$infinity23_phone_button_text = infinity23_pro_get_data( 'phone_button_text', $infinity23_data_array, $infinity23_defaults );
	$infinity23_phone_background_color = infinity23_pro_get_data( 'phone_background_color', $infinity23_data_array, $infinity23_defaults );
	$infinity23_phone_button_width = infinity23_pro_get_data( 'phone_button_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_phone_button_position = infinity23_pro_get_data( 'phone_button_position', $infinity23_data_array, $infinity23_defaults );

	$infinity23_email_display_checkbox = infinity23_pro_get_data( 'email_display_checkbox', $infinity23_data_array, $infinity23_defaults );
	$infinity23_email_button_href = infinity23_pro_get_data( 'email_button_href', $infinity23_data_array, $infinity23_defaults );
	$infinity23_custom_contact_page_link = infinity23_pro_get_data( 'custom_contact_page_link', $infinity23_data_array, $infinity23_defaults );
	$infinity23_email_button_text = infinity23_pro_get_data( 'email_button_text', $infinity23_data_array, $infinity23_defaults );
	$infinity23_email_background_color = infinity23_pro_get_data( 'email_background_color', $infinity23_data_array, $infinity23_defaults );
	$infinity23_email_button_width = infinity23_pro_get_data( 'email_button_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_email_button_position = infinity23_pro_get_data( 'email_button_position', $infinity23_data_array, $infinity23_defaults );

	$infinity23_whatsapp_display_checkbox = infinity23_pro_get_data( 'whatsapp_display_checkbox', $infinity23_data_array, $infinity23_defaults );
	$infinity23_whatsapp_button_href = infinity23_pro_get_data( 'whatsapp_button_href', $infinity23_data_array, $infinity23_defaults );
	$infinity23_whatsapp_url_message = infinity23_pro_get_data( 'whatsapp_url_message', $infinity23_data_array, $infinity23_defaults );
	$infinity23_whatsapp_button_text = infinity23_pro_get_data( 'whatsapp_button_text', $infinity23_data_array, $infinity23_defaults );
	$infinity23_whatsapp_background_color = infinity23_pro_get_data( 'whatsapp_background_color', $infinity23_data_array, $infinity23_defaults );
	$infinity23_whatsapp_button_width = infinity23_pro_get_data( 'whatsapp_button_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_whatsapp_button_position = infinity23_pro_get_data( 'whatsapp_button_position', $infinity23_data_array, $infinity23_defaults );

	$infinity23_chat_display_checkbox = infinity23_pro_get_data( 'chat_display_checkbox', $infinity23_data_array, $infinity23_defaults );
	$infinity23_chat_button_href = infinity23_pro_get_data( 'chat_button_href', $infinity23_data_array, $infinity23_defaults );
	$infinity23_chat_button_text = infinity23_pro_get_data( 'chat_button_text', $infinity23_data_array, $infinity23_defaults );
	$infinity23_chat_background_color = infinity23_pro_get_data( 'chat_background_color', $infinity23_data_array, $infinity23_defaults );
	$infinity23_chat_button_width = infinity23_pro_get_data( 'chat_button_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_chat_button_position = infinity23_pro_get_data( 'chat_button_position', $infinity23_data_array, $infinity23_defaults );

	$infinity23_hide_on_mobile_checkbox = infinity23_pro_get_data( 'hide_on_mobile_checkbox', $infinity23_data_array, $infinity23_defaults );
	$infinity23_hide_on_desktop_checkbox = infinity23_pro_get_data( 'hide_on_desktop_checkbox', $infinity23_data_array, $infinity23_defaults );
	$infinity23_mobile_width = infinity23_pro_get_data( 'mobile_width', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_ids                  = infinity23_pro_get_data( 'exclude_ids', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_option                = infinity23_pro_get_data( 'exclude_option', $infinity23_data_array, $infinity23_defaults );
	$infinity23_custom_css                    = infinity23_pro_get_data( 'custom_css', $infinity23_data_array, $infinity23_defaults );
	$infinity23_custom_css_active             = intval( infinity23_pro_get_data( 'custom_css_active', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_mobile_contact_line_hide_menu = intval( infinity23_pro_get_data( 'mobile_contact_line_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_custom_css_example = '';
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* changes the height and border of the bar */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-mobile-line {' . "\n";
	$infinity23_custom_css_example .= '    height: 60px !important;' . "\n";
	$infinity23_custom_css_example .= '    border-top: 3px solid #000 !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* the spacer under the content must match the bar height */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-mobile-line-space {' . "\n";
	$infinity23_custom_css_example .= '    height: 62px !important;' . "\n";
	$infinity23_custom_css_example .= '}' . "\n\n";
	$infinity23_custom_css_example .= '<span class="infinity23-code-comment">/* changes the color and width of one button */</span>' . "\n";
	$infinity23_custom_css_example .= '.infinity23-mobile-line .infinity23-mcl-phone {' . "\n";
	$infinity23_custom_css_example .= '    background-color: #000 !important;' . "\n";
	$infinity23_custom_css_example .= '    width: 40% !important;' . "\n";
	$infinity23_custom_css_example .= '}';

?>

<?php
// =================================================================================================================
// Output the plugin form data to page
// =================================================================================================================
?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp mobile-contact-line-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Mobile Contact Line Settings</h1>
		<p>Below you will be able to edit and make changes to the mobile contact line buttons:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_mobile_contact_line_activate_action', 'infinity23_mobile_contact_line_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Mobile Contact Line</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

			<div class="section-card">
				<h2>What Does Mobile Contact Line Do?</h2>

				<p>This plugin creates a fixed bottom bar with quick-access contact buttons, like the example image below.</p>

				<img src="<?php echo esc_url( infinity23_plugin_url( 'images/mobile-contact-line/mobile-contact-line-example.png' ) ); ?>" style="max-width:500px;height:auto;margin:15px 0px 0px 0px;" />

			</div><!--section-card-->

			<div class="section-card" data-card-default="open">

				<h2>Phone Button: <span class="infinity23-mcl-title-icon" aria-hidden="true"><span class="infinity23-mcl-title-icon-inner infinity23-mcl-title-icon-phone"></span></span></h2>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="phone_display_checkbox" name="phone_display_checkbox" value="1" <?php if( $infinity23_phone_display_checkbox == 1 ) { echo 'checked'; } ?> />
					<label for="phone_display_checkbox">Display this button on the page</label>
				</div><!--form-line-->

				<div class="infinity23-toggle-fields" data-toggle-input="phone_display_checkbox">

				<div class="form-line">
					<label for="phone_button_href">Phone Number: </label>
					<input type="text" id="phone_button_href" class="medium-input infinity23-phone-number-field" name="phone_button_href" data-allow-plus="1" value="<?php echo esc_attr( $infinity23_phone_button_href ); ?>" />
					<small>Example: 0521234567</small>
				</div><!--form-line-->

				<div class="form-line">
					<label for="phone_button_text">Button Text: </label>
					<input type="text" id="phone_button_text" class="medium-input" name="phone_button_text" value="<?php echo esc_attr( $infinity23_phone_button_text ); ?>" />
					<small>Example: Call Us Now</small>
				</div><!--form-line-->

				<div class="form-line background-color-line">
					<label for="phone_background_color">Background Color: </label>
					<input type="text" id="phone_background_color" name="phone_background_color" class="infinity23-color-picker" value="<?php echo esc_attr( $infinity23_phone_background_color ); ?>" />
				</div><!--form-line-->

				<div class="form-line">
					<label for="phone_button_width">Button Width: </label>
					<input type="text" id="phone_button_width" class="very-short-input" name="phone_button_width" value="<?php echo esc_attr( $infinity23_phone_button_width ); ?>" />
					<small>Example: 50%</small>
				</div><!--form-line-->

				<div class="form-line">
					<label for="phone_button_position">Display Order: </label>
					<input type="text" id="phone_button_position" class="very-short-input" name="phone_button_position" value="<?php echo esc_attr( $infinity23_phone_button_position ); ?>" />
					<small>Position of button on the bar (1 = first)</small>
				</div><!--form-line-->

				</div><!--infinity23-toggle-fields-->

			</div><!--section-card-->


			<div class="section-card" data-card-default="open">

				<h2>Email / Contact Page Button: <span class="infinity23-mcl-title-icon" aria-hidden="true"><span class="infinity23-mcl-title-icon-inner infinity23-mcl-title-icon-email"></span></span></h2>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="email_display_checkbox" name="email_display_checkbox" value="1" <?php if( $infinity23_email_display_checkbox == 1 ) { echo 'checked'; } ?> />
					<label for="email_display_checkbox">Display this button on the page</label>
				</div><!--form-line-->

				<div class="infinity23-toggle-fields" data-toggle-input="email_display_checkbox">

				<div class="form-line">
					<label for="email_button_href">Email or URL: </label>
					<input type="text" id="email_button_href" class="medium-input" name="email_button_href" value="<?php echo esc_attr( $infinity23_email_button_href ); ?>" />
					<small>Example: email@email.com or https://site.com/contact/</small>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="custom_contact_page_link" name="custom_contact_page_link" value="1" <?php if( $infinity23_custom_contact_page_link == 1 ) { echo 'checked'; } ?> />
					<label for="custom_contact_page_link">Use field above as a custom URL instead of email address</label>
				</div><!--form-line-->

				<div class="form-line">
					<label for="email_button_text">Button Text: </label>
					<input type="text" id="email_button_text" class="medium-input" name="email_button_text" value="<?php echo esc_attr( $infinity23_email_button_text ); ?>" />
					<small>Example: Email Us</small>
				</div><!--form-line-->

				<div class="form-line background-color-line">
					<label for="email_background_color">Background Color: </label>
					<input type="text" id="email_background_color" name="email_background_color" class="infinity23-color-picker" value="<?php echo esc_attr( $infinity23_email_background_color ); ?>" />
				</div><!--form-line-->

				<div class="form-line">
					<label for="email_button_width">Button Width: </label>
					<input type="text" id="email_button_width" class="very-short-input" name="email_button_width" value="<?php echo esc_attr( $infinity23_email_button_width ); ?>" />
					<small>Example: 25%</small>
				</div><!--form-line-->

				<div class="form-line">
					<label for="email_button_position">Display Order: </label>
					<input type="text" id="email_button_position" class="very-short-input" name="email_button_position" value="<?php echo esc_attr( $infinity23_email_button_position ); ?>" />
					<small>Position of button on the bar (1 = first)</small>
				</div><!--form-line-->

				</div><!--infinity23-toggle-fields-->

			</div><!--section-card-->


			<div class="section-card" data-card-default="open">

				<h2>Whatsapp Button: <span class="infinity23-mcl-title-icon" aria-hidden="true"><span class="infinity23-mcl-title-icon-inner infinity23-mcl-title-icon-whatsapp"></span></span></h2>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="whatsapp_display_checkbox" name="whatsapp_display_checkbox" value="1" <?php if( $infinity23_whatsapp_display_checkbox == 1 ) { echo 'checked'; } ?> />
					<label for="whatsapp_display_checkbox">Display this button on the page</label>
				</div><!--form-line-->

				<div class="infinity23-toggle-fields" data-toggle-input="whatsapp_display_checkbox">

				<div class="form-line">
					<label for="whatsapp_button_href">Whatsapp Number: </label>
					<input type="text" id="whatsapp_button_href" class="medium-input infinity23-phone-number-field" name="whatsapp_button_href" value="<?php echo esc_attr( $infinity23_whatsapp_button_href ); ?>" />
					<small>Example: 9720521234567</small>
				</div><!--form-line-->

				<div class="form-line">
					<label for="whatsapp_url_message">Pre-filled Message: </label>
					<input type="text" id="whatsapp_url_message" class="long-input" name="whatsapp_url_message" value="<?php echo esc_attr( $infinity23_whatsapp_url_message ); ?>" />
					<small>Example: I contacted you from your website</small>
				</div><!--form-line-->

				<div class="form-line">
					<label for="whatsapp_button_text">Button Text: </label>
					<input type="text" id="whatsapp_button_text" class="medium-input" name="whatsapp_button_text" value="<?php echo esc_attr( $infinity23_whatsapp_button_text ); ?>" />
					<small>Example: Whatsapp</small>
				</div><!--form-line-->

				<div class="form-line background-color-line">
					<label for="whatsapp_background_color">Background Color: </label>
					<input type="text" id="whatsapp_background_color" name="whatsapp_background_color" class="infinity23-color-picker" value="<?php echo esc_attr( $infinity23_whatsapp_background_color ); ?>" />
				</div><!--form-line-->

				<div class="form-line">
					<label for="whatsapp_button_width">Button Width: </label>
					<input type="text" id="whatsapp_button_width" class="very-short-input" name="whatsapp_button_width" value="<?php echo esc_attr( $infinity23_whatsapp_button_width ); ?>" />
					<small>Example: 25%</small>
				</div><!--form-line-->

				<div class="form-line">
					<label for="whatsapp_button_position">Display Order: </label>
					<input type="text" id="whatsapp_button_position" class="very-short-input" name="whatsapp_button_position" value="<?php echo esc_attr( $infinity23_whatsapp_button_position ); ?>" />
					<small>Position of button on the bar (1 = first)</small>
				</div><!--form-line-->

				</div><!--infinity23-toggle-fields-->

			</div><!--section-card-->


			<div class="section-card" data-card-default="open">

				<h2>Chat Button: <span class="infinity23-mcl-title-icon" aria-hidden="true"><span class="infinity23-mcl-title-icon-inner infinity23-mcl-title-icon-chat"></span></span></h2>

				<p>This button can link to a live chat service or to any other URL you want - a contact form, a Waze or Google Maps location, a Facebook page, a price list, or anything else.</p>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="chat_display_checkbox" name="chat_display_checkbox" value="1" <?php if( $infinity23_chat_display_checkbox == 1 ) { echo 'checked'; } ?> />
					<label for="chat_display_checkbox">Display this button on the page</label>
				</div><!--form-line-->

				<div class="infinity23-toggle-fields" data-toggle-input="chat_display_checkbox">

				<div class="form-line">
					<label for="chat_button_href">Chat / Custom URL: </label>
					<input type="text" id="chat_button_href" class="long-input" name="chat_button_href" value="<?php echo esc_attr( $infinity23_chat_button_href ); ?>" />
					<small>Example: https://www.chat.com or any other page URL</small>
				</div><!--form-line-->

				<div class="form-line">
					<label for="chat_button_text">Button Text: </label>
					<input type="text" id="chat_button_text" class="medium-input" name="chat_button_text" value="<?php echo esc_attr( $infinity23_chat_button_text ); ?>" />
					<small>Example: Live Chat</small>
				</div><!--form-line-->

				<div class="form-line background-color-line">
					<label for="chat_background_color">Background Color: </label>
					<input type="text" id="chat_background_color" name="chat_background_color" class="infinity23-color-picker" value="<?php echo esc_attr( $infinity23_chat_background_color ); ?>" />
				</div><!--form-line-->

				<div class="form-line">
					<label for="chat_button_width">Button Width: </label>
					<input type="text" id="chat_button_width" class="very-short-input" name="chat_button_width" value="<?php echo esc_attr( $infinity23_chat_button_width ); ?>" />
					<small>Example: 25%</small>
				</div><!--form-line-->

				<div class="form-line">
					<label for="chat_button_position">Display Order: </label>
					<input type="text" id="chat_button_position" class="very-short-input" name="chat_button_position" value="<?php echo esc_attr( $infinity23_chat_button_position ); ?>" />
					<small>Position of button on the bar (1 = first)</small>
				</div><!--form-line-->

				</div><!--infinity23-toggle-fields-->

			</div><!--section-card-->


			<div class="section-card" data-card-default="open">

				<h2>Responsive Display Settings:</h2>

				<div class="form-line">
					<label for="mobile_width">Mobile breaking point: </label>
					<input type="text" id="mobile_width" class="very-short-input" name="mobile_width" value="<?php echo esc_attr( $infinity23_mobile_width ); ?>" />
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

				<div class="form-line checkbox-line">
					<input type="checkbox" id="hide_on_mobile_checkbox" name="hide_on_mobile_checkbox" value="1" <?php if( $infinity23_hide_on_mobile_checkbox == 1 ) { echo 'checked'; } ?> />
					<label for="hide_on_mobile_checkbox">Hide the button on mobile</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="hide_on_desktop_checkbox" name="hide_on_desktop_checkbox" value="1" <?php if( $infinity23_hide_on_desktop_checkbox == 1 ) { echo 'checked'; } ?> />
					<label for="hide_on_desktop_checkbox">Hide the button on desktop</label>
				</div><!--form-line-->

			</div><!--section-card-->


			<div class="section-card" data-card-default="open">

				<h2>Include / Exclude Pages By ID:</h2>

				<?php infinity23_exclude_ids_field( $infinity23_exclude_ids, $infinity23_exclude_option ); ?>

			</div><!--section-card-->

			<?php infinity23_custom_css_section_card( $infinity23_custom_css, $infinity23_custom_css_active, $infinity23_custom_css_example, 'custom_css', $infinity23_module_display_name ); ?>

			<?php infinity23_menu_visibility_field( 'mobile_contact_line_hide_menu', $infinity23_mobile_contact_line_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_mobile_contact_line_action', 'infinity23_mobile_contact_line_nonce' ); ?>

			<input type="submit" name="submit_mobile_contact_line" class='submit-btn' value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="additional-data-warp" data-card-default="open">

		<h2>Additional Data</h2>

		<h3>Contact Line CSS Classes <small>(Allow to restyle or replace the plugin output from your own CSS)</small></h3>

		<p class="additional-data-intro">These are the classes printed on the front-end output. Target them from your theme CSS to override the plugin design of the bar or of a single button.</p>

		<ul>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-mobile-line</span> - the bar itself, holding the position at the bottom of the screen, the height, the background and the z-index.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-mobile-line-space</span> - an empty spacer printed under the page content so the fixed bar does not cover the bottom of the site.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-mcl-phone</span> - the phone button. The same pattern works for the other buttons with infinity23-mcl-email, infinity23-mcl-whatsapp and infinity23-mcl-chat.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-mcl-icon</span> - the icon inside each button, so you can replace the default icon image with your own.</p>
			</li>
		</ul>

		<button type="button" class="infinity23-code-example-btn">Code Example</button>
		<div class="infinity23-code-example-block">
			<pre><code><span class="infinity23-code-comment">// changes the height and the border of the bar</span>
<span class="infinity23-code-hl">.infinity23-mobile-line</span> {
    height: 60px !important;
    border-top: 3px solid #000 !important;
}

<span class="infinity23-code-comment">// the spacer under the content must match the bar height</span>
<span class="infinity23-code-hl">.infinity23-mobile-line-space</span> {
    height: 62px !important;
}

<span class="infinity23-code-comment">// changes the color and the width of one button</span>
.infinity23-mobile-line <span class="infinity23-code-hl">.infinity23-mcl-phone</span> {
    background-color: #000 !important;
    width: 40% !important;
}

<span class="infinity23-code-comment">// swap the icon of that button with your own image</span>
.infinity23-mobile-line .infinity23-mcl-phone <span class="infinity23-code-hl">.infinity23-mcl-icon</span> {
    background-position: center center !important;
    /* background-image: url(my-icon.svg) !important; */
}</code></pre>
		</div><!--infinity23-code-example-block-->

	</div><!--additional-data-warp-->

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_mobile_contact_line_reset_action', 'infinity23_mobile_contact_line_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
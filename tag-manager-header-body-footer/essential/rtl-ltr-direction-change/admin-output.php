<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// =================================================================================================================
	// Initial plugin load and getting default values
	// =================================================================================================================

	include( dirname(__FILE__) . "/../../include/settings.php" );
	include( dirname(__FILE__) . "/internal-settings.php" );
	infinity23_load_essential_files();

	$infinity23_success_message = '';
	$infinity23_error_message   = '';

	if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {
		$infinity23_success_message = "The data was updated successfully";
	} // if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {

	include( dirname(__FILE__) . "/default-values.php" );

	// =================================================================================================================
	// Update the data if the form was submitted
	// =================================================================================================================

	// ----------------------------------------
	// reset all settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_rtl_ltr_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_rtl_ltr_reset_nonce'] ) ), 'infinity23_rtl_ltr_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_rtl_ltr_reset_nonce'] ) ) {

	// ----------------------------------------
	// toggle activation form submit
	// ----------------------------------------

	if( isset( $_POST['infinity23_rtl_ltr_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_rtl_ltr_activate_nonce'] ) ), 'infinity23_rtl_ltr_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'rtl_ltr_direction_change', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "LTR RTL Direction Change was activated successfully";
			} else {
				infinity23_module_deactivate_redirect( $infinity23_module_display_name );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_rtl_ltr_activate_nonce'] ) ) {

	// ----------------------------------------
	// main settings form submit
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_rtl_ltr_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_rtl_ltr_nonce'] ) ), 'infinity23_rtl_ltr_settings_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
			$infinity23_data_array['save_scope'] = infinity23_pro_field_sanitize( 'save_scope', $infinity23_data_array, $infinity23_defaults );
			$infinity23_data_array['admin_bar_label_format'] = infinity23_pro_field_sanitize( 'admin_bar_label_format', $infinity23_data_array, $infinity23_defaults );
			$infinity23_data_array['rtl_ltr_direction_change_hide_menu'] = infinity23_pro_field_sanitize( 'rtl_ltr_direction_change_hide_menu', $infinity23_data_array, $infinity23_defaults );

			if( !in_array( $infinity23_data_array['admin_bar_label_format'], array( 'full', 'short', 'text_only', 'icon_only' ) ) ) {
				$infinity23_data_array['admin_bar_label_format'] = 'full';
			} // if( !in_array(

			infinity23_update_option(
				$infinity23_settings_array_db_name,
				infinity23_convert_array_to_json($infinity23_data_array),
				infinity23_get_option_data( $infinity23_settings_array_db_name, 'data1' ),
				infinity23_get_option_data( $infinity23_settings_array_db_name, 'data2' ),
				infinity23_get_option_data( $infinity23_settings_array_db_name, 'data3' ),
				infinity23_get_option_data( $infinity23_settings_array_db_name, 'data4' ),
				infinity23_get_option_data( $infinity23_settings_array_db_name, 'data5' ),
				infinity23_get_option_data( $infinity23_settings_array_db_name, 'data6' ),
				infinity23_get_option_data( $infinity23_settings_array_db_name, 'data7' ),
				infinity23_get_option_data( $infinity23_settings_array_db_name, 'data8' ),
				infinity23_get_option_data( $infinity23_settings_array_db_name, 'data9' ),
				infinity23_get_option_data( $infinity23_settings_array_db_name, 'data10' )
			);

			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-rtl-ltr-direction-change', 'saved' => '1' ), admin_url('admin.php') );

			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_rtl_ltr_nonce'] ) ) {

	// =================================================================================================================
	// Load saved values
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_save_scope = intval( infinity23_pro_get_data( 'save_scope', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_admin_bar_label_format = infinity23_pro_get_data( 'admin_bar_label_format', $infinity23_data_array, $infinity23_defaults );
	$infinity23_rtl_ltr_direction_change_hide_menu = intval( infinity23_pro_get_data( 'rtl_ltr_direction_change_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp rtl-ltr-direction-change-warp">

<?php infinity23_show_message( $infinity23_success_message, $infinity23_error_message ); ?>

	<div class="page-header">
		<h1>LTR/RTL Change Settings</h1>
		<p>Adds a Direction button in the WordPress admin bar for logged in users. By default the change is not saved and resets on the next page load. Turn on Save Page below to have a page remember the direction you left it on.</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_rtl_ltr_activate_action', 'infinity23_rtl_ltr_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate LTR RTL Direction Change</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

			<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="open">

				<h2>Advanced Settings: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

				<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="save_scope" name="save_scope" value="1" <?php if( $infinity23_save_scope == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
					<label for="save_scope">Save direction style for pages on page reload</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Checked saves a direction for every admin screen you change, so it loads with that direction next time you open the same page. Only pages moved away from the site's own default direction are ever saved.
							<br /><br />
							Unchecked never stores a direction - every page loads with the default direction and the button only flips it for that view.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line">
					<label for="admin_bar_label_format">Admin Bar Button Text: </label>
					<select id="admin_bar_label_format" name="admin_bar_label_format" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?>>
						<option value="full" <?php if( $infinity23_admin_bar_label_format == 'full' ) { echo 'selected'; } ?>>Show Full Text (Direction: RTL)</option>
						<option value="text_only" <?php if( $infinity23_admin_bar_label_format == 'text_only' ) { echo 'selected'; } ?>>Show Direction Text Only (RTL)</option>
						<option value="icon_only" <?php if( $infinity23_admin_bar_label_format == 'icon_only' ) { echo 'selected'; } ?>>Show Icon Only</option>
						<option value="short" <?php if( $infinity23_admin_bar_label_format == 'short' ) { echo 'selected'; } ?>>Show Icon + Direction</option>
					</select>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Controls how the direction button in the WordPress admin bar is labeled.
							<br /><br />
							Show Full Text displays the full label, for example Direction: RTL.
							<br /><br />
							Show Icon + Direction shows the icon together with just the direction, for example RTL.
							<br /><br />
							Show Direction Only, No Icon removes the icon entirely and shows only the direction text.
							<br /><br />
							Show Icon Only removes all text, leaving just the icon.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

			</div><!--section-card-->

			<?php infinity23_menu_visibility_field( 'rtl_ltr_direction_change_hide_menu', $infinity23_rtl_ltr_direction_change_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_rtl_ltr_settings_action', 'infinity23_rtl_ltr_nonce' ); ?>

			<input type="submit" name="submit_rtl_ltr" class="submit-btn" value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_rtl_ltr_reset_action', 'infinity23_rtl_ltr_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn" data-confirm="Are you sure you want to reset all LTR/RTL Change settings? This clears every saved page direction too.">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings + Remove Saved Pages</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
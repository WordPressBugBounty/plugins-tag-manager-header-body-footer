<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// =================================================================================================================
	// Initial plugin load and getting default values
	// =================================================================================================================

	include( dirname(__FILE__) . "/../../include/settings.php" );
	include( dirname(__FILE__) . "/internal-settings.php" );
	include( dirname(__FILE__) . "/back-end/folders-content-types.php" );
	include( dirname(__FILE__) . "/back-end/folders-db-functions.php" );
	infinity23_load_essential_files();

	$infinity23_success_message = '';
	$infinity23_error_message = '';

	if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {
		$infinity23_success_message = "The data was updated successfully";
	} // if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {

	include( dirname(__FILE__) . "/default-values.php" );

	$infinity23_fo_content_types = infinity23_folders_organizer_get_content_types();

	// =================================================================================================================
	// Update the data if the form was submitted
	// =================================================================================================================

	if( isset( $_POST['infinity23_folders_organizer_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_folders_organizer_reset_nonce'] ) ), 'infinity23_folders_organizer_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			infinity23_folders_organizer_delete_all_folders();
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} elseif( isset( $_POST['infinity23_folders_organizer_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_folders_organizer_activate_nonce'] ) ), 'infinity23_folders_organizer_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'folders_organizer', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				infinity23_folders_organizer_install_table();
				$infinity23_success_message = "Folders Organizer was activated successfully";
			} else {
				infinity23_module_deactivate_redirect( $infinity23_module_display_name );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} elseif( isset( $_POST['infinity23_folders_organizer_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_folders_organizer_nonce'] ) ), 'infinity23_folders_organizer_settings_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is intval()'d and strictly compared below, safe regardless of slashing
			$infinity23_posted_types = ( isset( $_POST['enabled_content_types'] ) && is_array( $_POST['enabled_content_types'] ) ) ? $_POST['enabled_content_types'] : array();
			$infinity23_enabled_content_types = array();

			foreach( infinity23_folders_organizer_get_builtin_type_keys() as $infinity23_type_key ) {

				$infinity23_enabled_content_types[ $infinity23_type_key ] = ( isset( $infinity23_posted_types[ $infinity23_type_key ] ) && intval( $infinity23_posted_types[ $infinity23_type_key ] ) === 1 ) ? 1 : 0;

			} // foreach( infinity23_folders_organizer_get_builtin_type_keys() as $infinity23_type_key ) {

			$infinity23_data_array['enabled_content_types'] = $infinity23_enabled_content_types;

			$infinity23_data_array['custom_post_types_active'] = infinity23_pro_field_sanitize( 'custom_post_types_active', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_all_cpt_slugs = array_keys( infinity23_get_custom_post_types() );
			$infinity23_active_cpt_posted = array();

			if( isset( $_POST['custom_post_types_active_list'] ) && is_array( $_POST['custom_post_types_active_list'] ) ) {

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is sanitize_text_field(wp_unslash())'d inside the loop
				foreach( $_POST['custom_post_types_active_list'] as $infinity23_single_cpt_slug ) {

					if( is_array( $infinity23_single_cpt_slug ) ) { continue; } // if( is_array( $infinity23_single_cpt_slug ) ) {

					$infinity23_active_cpt_posted[] = sanitize_text_field( wp_unslash( $infinity23_single_cpt_slug ) );

				} // foreach( $_POST['custom_post_types_active_list'] as $infinity23_single_cpt_slug ) {

			} // if( isset( $_POST['custom_post_types_active_list'] ) && is_array(

			$infinity23_disabled_cpt_to_save = array_diff( $infinity23_all_cpt_slugs, $infinity23_active_cpt_posted );

			$infinity23_data_array['disabled_custom_post_types'] = implode( ',', $infinity23_disabled_cpt_to_save );

			$infinity23_data_array['sidebar_position'] = infinity23_css_sanitize( infinity23_post_sanitize('sidebar_position', 'text'), 'option', 'left', array( 'left', 'right' ) );
			$infinity23_sidebar_width_value = infinity23_post_sanitize('sidebar_width_value', 'text');
			$infinity23_sidebar_width_unit = infinity23_post_sanitize('sidebar_width_unit', 'text');

			if( !in_array( $infinity23_sidebar_width_unit, array( 'px', '%', 'em', 'rem' ), true ) ) {
				$infinity23_sidebar_width_unit = 'px';
			} // if( !in_array(

			$infinity23_data_array['sidebar_width'] = infinity23_css_sanitize( $infinity23_sidebar_width_value . $infinity23_sidebar_width_unit, 'size', '200px' );
			$infinity23_data_array['folders_organizer_hide_menu'] = infinity23_pro_field_sanitize( 'folders_organizer_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['folders_limit_per_type'] = infinity23_pro_field_sanitize( 'folders_limit_per_type', $infinity23_existing_data_array, $infinity23_defaults );

			if( !in_array( $infinity23_data_array['folders_limit_per_type'], infinity23_folders_organizer_get_limit_options(), true ) ) {
				$infinity23_data_array['folders_limit_per_type'] = infinity23_get_data( 'folders_limit_per_type', array(), $infinity23_defaults );
			} // if( !in_array( $infinity23_data_array['folders_limit_per_type'], infinity23_folders_organizer_get_limit_options(), true ) ) {

			infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-folders-organizer', 'saved' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_folders_organizer_reset_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_enabled_content_types = infinity23_pro_get_data( 'enabled_content_types', $infinity23_data_array, $infinity23_defaults );
	$infinity23_custom_post_types_active = intval( infinity23_pro_get_data( 'custom_post_types_active', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_disabled_custom_post_types = infinity23_pro_get_data( 'disabled_custom_post_types', $infinity23_data_array, $infinity23_defaults );
	$infinity23_folders_organizer_hide_menu = intval( infinity23_pro_get_data( 'folders_organizer_hide_menu', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_folders_limit_per_type = strval( infinity23_pro_get_data( 'folders_limit_per_type', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_sidebar_position = infinity23_pro_get_data( 'sidebar_position', $infinity23_data_array, $infinity23_defaults );
	$infinity23_sidebar_width = infinity23_pro_get_data( 'sidebar_width', $infinity23_data_array, $infinity23_defaults );

	$infinity23_disabled_cpt_array = array();

	if( !empty( $infinity23_disabled_custom_post_types ) ) {

		foreach( explode( ',', $infinity23_disabled_custom_post_types ) as $infinity23_single_disabled_cpt ) {

			$infinity23_single_disabled_cpt = trim( $infinity23_single_disabled_cpt );

			if( !empty( $infinity23_single_disabled_cpt ) ) {
				$infinity23_disabled_cpt_array[] = $infinity23_single_disabled_cpt;
			} // if( !empty( $infinity23_single_disabled_cpt ) ) {

		} // foreach( explode( ',', $infinity23_disabled_custom_post_types ) as $infinity23_single_disabled_cpt ) {

	} // if( !empty( $infinity23_disabled_custom_post_types ) ) {

	$infinity23_custom_post_types_list = infinity23_get_custom_post_types();
	$infinity23_cpt_toggle_rows = array();

	foreach( $infinity23_custom_post_types_list as $infinity23_cpt_slug => $infinity23_cpt_data ) {

		$infinity23_cpt_toggle_rows[] = array(
			'value' => $infinity23_cpt_slug,
			'label' => $infinity23_cpt_data['label'] . ' (' . $infinity23_cpt_slug . ')',
			'checked' => !in_array( $infinity23_cpt_slug, $infinity23_disabled_cpt_array, true ),
		);

	} // foreach( $infinity23_custom_post_types_list as $infinity23_cpt_slug => $infinity23_cpt_data ) {

	$infinity23_sidebar_width_value = '200';
	$infinity23_sidebar_width_unit = 'px';

	if( preg_match( '/^([0-9.]+)(px|%|em|rem)?$/i', trim( strval( $infinity23_sidebar_width ) ), $sidebar_width_matches ) ) {
		$infinity23_sidebar_width_value = $sidebar_width_matches[1];
		$infinity23_sidebar_width_unit = !empty( $sidebar_width_matches[2] ) ? strtolower( $sidebar_width_matches[2] ) : 'px';
	} // if( preg_match(

	if( !is_array( $infinity23_enabled_content_types ) ) {
		$infinity23_enabled_content_types = $infinity23_defaults['enabled_content_types'];
	} // if( !is_array( $infinity23_enabled_content_types ) ) {

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp folders-organizer-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Folders Organizer</h1>
		<p>Organize your Media Library images, pages, posts and custom post types into folders. Every content type has its own separate set of folders.</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_folders_organizer_activate_action', 'infinity23_folders_organizer_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Folders Organizer</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

			<form class="infinity23-form" method="POST" action="">

				<div class="section-card" data-card-default="open">

					<h2>Folders Settings:</h2>

					<h3>Where To Use Folders:</h3>

					<p>Choose which content types should have a folders system.</p>

					<?php foreach( infinity23_folders_organizer_get_builtin_type_keys() as $infinity23_type_key ) { ?>

						<?php $infinity23_type_data = $infinity23_fo_content_types[ $infinity23_type_key ]; ?>
						<?php $infinity23_type_checked = isset( $infinity23_enabled_content_types[ $infinity23_type_key ] ) ? ( intval( $infinity23_enabled_content_types[ $infinity23_type_key ] ) === 1 ) : ( intval( $infinity23_defaults['enabled_content_types_default'] ) === 1 ); ?>

						<div class="form-line checkbox-line">
							<input type="checkbox" id="enabled_content_types_<?php echo esc_attr( $infinity23_type_key ); ?>" name="enabled_content_types[<?php echo esc_attr( $infinity23_type_key ); ?>]" value="1" <?php if( $infinity23_type_checked ) { echo 'checked'; } ?> />
							<label for="enabled_content_types_<?php echo esc_attr( $infinity23_type_key ); ?>"><?php echo esc_html( $infinity23_type_data['label'] ); ?></label>
						</div><!--form-line-->

					<?php } // foreach( infinity23_folders_organizer_get_builtin_type_keys() as $infinity23_type_key ) { ?>

					<div class="infinity23-toggle-fields" data-toggle-input="enabled_content_types_posts">

						<?php infinity23_custom_post_types_section( 'custom_post_types_active', 'Show folders on custom post types as well', $infinity23_custom_post_types_active, 'custom_post_types_active_list', $infinity23_cpt_toggle_rows, 'Every custom post type on this site is listed below, each with its own separate set of folders. Turn a type off to hide folders there.' ); ?>

					</div><!--infinity23-toggle-fields-->

					<h3>Media Library Sidebar Position:</h3>

					<div class="form-line">
						<label for="sidebar_position">Show the folders sidebar on the: </label>
						<select name="sidebar_position" id="sidebar_position">
							<option value="left" <?php if( $infinity23_sidebar_position === 'left' ) { echo 'selected'; } ?>>Left</option>
							<option value="right" <?php if( $infinity23_sidebar_position === 'right' ) { echo 'selected'; } ?>>Right</option>
						</select>
					</div><!--form-line-->

					<div class="form-line">
						<label for="sidebar_width_value">Sidebar Width: </label>
						<input type="text" id="sidebar_width_value" class="very-short-input" name="sidebar_width_value" value="<?php echo esc_attr( $infinity23_sidebar_width_value ); ?>" />
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
						<?php echo infinity23_unit_select_field( 'sidebar_width_unit', $infinity23_sidebar_width_unit ); ?>

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								Controls how wide the folders sidebar is on the Media Library screen.
								<br /><br />
								<strong>Usage Examples:</strong>
								<ul>
									<li>Fixed width - <span class="code">200</span> px</li>
									<li>Wider sidebar - <span class="code">280</span> px</li>
								</ul>
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

				</div><!--section-card-->

				<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="open">

					<h2>Advanced Settings: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

					<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

					<div class="form-line">
						<label for="folders_limit_per_type">Maximum folders in each section: </label>
						<select name="folders_limit_per_type" id="folders_limit_per_type" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?>>
							<?php foreach( infinity23_folders_organizer_get_limit_options() as $infinity23_limit_option ) { ?>
								<option value="<?php echo esc_attr( $infinity23_limit_option ); ?>" <?php if( $infinity23_folders_limit_per_type === $infinity23_limit_option ) { echo 'selected'; } ?>><?php echo esc_html( $infinity23_limit_option === 'unlimited' ? 'Unlimited' : $infinity23_limit_option ); ?></option>
							<?php } // foreach( infinity23_folders_organizer_get_limit_options() as $infinity23_limit_option ) { ?>
						</select>

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								Controls how many folders can be created in each section (Images, Pages, Posts and each custom post type).
								<br /><br />
								The free version is limited to 3 folders in each section. Choose Unlimited for no limit.
								<br /><br />
								Folders that already exist are never removed when the limit is lowered - only creating new folders is blocked.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

				</div><!--section-card-->

				<?php infinity23_menu_visibility_field( 'folders_organizer_hide_menu', $infinity23_folders_organizer_hide_menu, $infinity23_module_display_name ); ?>

				<?php wp_nonce_field( 'infinity23_folders_organizer_settings_action', 'infinity23_folders_organizer_nonce' ); ?>

				<input type="submit" name="submit_folders_organizer" class="submit-btn" value="Save Changes" />

			</form>

	</div><!--main-form-wrap-->

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_folders_organizer_reset_action', 'infinity23_folders_organizer_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn" data-confirm="Are you sure you want to reset Folders Organizer? This will delete every folder you created.">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
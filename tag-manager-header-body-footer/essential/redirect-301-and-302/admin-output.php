<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// =================================================================================================================
	// Initial plugin load and getting default values
	// =================================================================================================================

	include( dirname(__FILE__) . "/../../include/settings.php" );
	include( dirname(__FILE__) . "/internal-settings.php" );
	include( dirname(__FILE__) . "/back-end/redirect-db-functions.php" );
	include( dirname(__FILE__) . "/back-end/redirect-log-db-functions.php" );
	infinity23_load_essential_files();

	$infinity23_success_message = '';
	$infinity23_error_message   = '';

	if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {
		$infinity23_success_message = "The data was updated successfully";
	} // if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {

	include( dirname(__FILE__) . "/default-values.php" );

	// =================================================================================================================
	// route to the single folder edit screen or the view-all-redirects screen
	// =================================================================================================================

	$infinity23_redirect_view_folder_id = isset( $_GET['folder_id'] ) ? intval( wp_unslash( $_GET['folder_id'] ) ) : 0;
	$infinity23_redirect_view_all_mode = ( isset( $_GET['view_all'] ) && $_GET['view_all'] == '1' ) ? 1 : 0;

	if( isset( $_GET['view'] ) && $_GET['view'] === 'folder' && ( !empty( $infinity23_redirect_view_folder_id ) || $infinity23_redirect_view_all_mode == 1 ) ) {

		include( dirname(__FILE__) . "/secondary-page.php" );
		return;

	} // if( isset( $_GET['view'] ) && $_GET['view'] === 'folder' && ( !empty( $infinity23_redirect_view_folder_id ) || $infinity23_redirect_view_all_mode == 1 ) ) {

	// =================================================================================================================
	// Update the data if the form was submitted
	// =================================================================================================================

	if( isset( $_POST['infinity23_redirect_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_redirect_reset_nonce'] ) ), 'infinity23_redirect_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );

			$infinity23_success_message = "The settings were reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} elseif( isset( $_POST['infinity23_redirect_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_redirect_activate_nonce'] ) ), 'infinity23_redirect_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'redirect_301_and_302', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {

				infinity23_redirect_install_tables();
				$infinity23_success_message = "Redirect 301/302 was activated successfully";

			} else {

				infinity23_module_deactivate_redirect( $infinity23_module_display_name );

			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} elseif( isset( $_POST['infinity23_redirect_settings_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_redirect_settings_nonce'] ) ), 'infinity23_redirect_settings_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
			$infinity23_data_array['redirect_col_redirect_type'] = infinity23_pro_field_sanitize( 'redirect_col_redirect_type', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_col_request_url'] = infinity23_pro_field_sanitize( 'redirect_col_request_url', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_col_destination_url'] = infinity23_pro_field_sanitize( 'redirect_col_destination_url', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_col_redirect_query'] = infinity23_pro_field_sanitize( 'redirect_col_redirect_query', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_col_get_parameters'] = infinity23_pro_field_sanitize( 'redirect_col_get_parameters', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_col_notes'] = infinity23_pro_field_sanitize( 'redirect_col_notes', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_col_advertising_platform'] = infinity23_pro_field_sanitize( 'redirect_col_advertising_platform', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_col_redirects_amount'] = infinity23_pro_field_sanitize( 'redirect_col_redirects_amount', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_advance_view_mode'] = infinity23_pro_field_sanitize( 'redirect_advance_view_mode', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_allow_bulk_edit'] = infinity23_pro_field_sanitize( 'redirect_allow_bulk_edit', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_shortener_enabled'] = infinity23_pro_field_sanitize( 'redirect_shortener_enabled', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_shortener_code_length'] = infinity23_pro_field_sanitize( 'redirect_shortener_code_length', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_shortener_prefix'] = infinity23_pro_field_sanitize( 'redirect_shortener_prefix', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_log_enabled'] = infinity23_pro_field_sanitize( 'redirect_log_enabled', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_log_retention_days'] = infinity23_pro_field_sanitize( 'redirect_log_retention_days', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_log_max_count'] = infinity23_pro_field_sanitize( 'redirect_log_max_count', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_log_per_page'] = infinity23_pro_field_sanitize( 'redirect_log_per_page', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_301_and_302_hide_menu'] = infinity23_pro_field_sanitize( 'redirect_301_and_302_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			if( !in_array( $infinity23_data_array['redirect_advance_view_mode'], array( 'compact', 'full' ), true ) ) {
				$infinity23_data_array['redirect_advance_view_mode'] = 'compact';
			} // if( !in_array( $infinity23_data_array['redirect_advance_view_mode'], array( 'compact', 'full' ), true ) ) {

			if( $infinity23_data_array['redirect_shortener_code_length'] < 5 || $infinity23_data_array['redirect_shortener_code_length'] > 10 ) {
				$infinity23_data_array['redirect_shortener_code_length'] = $infinity23_defaults['redirect_shortener_code_length']['value'];
			} // if( $infinity23_data_array['redirect_shortener_code_length'] < 5 || $infinity23_data_array['redirect_shortener_code_length'] > 10 ) {

			$infinity23_data_array['redirect_shortener_prefix'] = trim( $infinity23_data_array['redirect_shortener_prefix'], '/' );

			if( $infinity23_data_array['redirect_log_retention_days'] <= 0 ) {
				$infinity23_data_array['redirect_log_retention_days'] = $infinity23_defaults['redirect_log_retention_days']['value'];
			} // if( $infinity23_data_array['redirect_log_retention_days'] <= 0 ) {

			if( $infinity23_data_array['redirect_log_max_count'] <= 0 ) {
				$infinity23_data_array['redirect_log_max_count'] = $infinity23_defaults['redirect_log_max_count']['value'];
			} // if( $infinity23_data_array['redirect_log_max_count'] <= 0 ) {

			if( $infinity23_data_array['redirect_log_per_page'] <= 0 ) {
				$infinity23_data_array['redirect_log_per_page'] = $infinity23_defaults['redirect_log_per_page']['value'];
			} // if( $infinity23_data_array['redirect_log_per_page'] <= 0 ) {

			infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );

			if( $infinity23_data_array['redirect_log_enabled'] == 1 && !infinity23_redirect_log_table_exists() ) {
				infinity23_redirect_log_install_table();
				infinity23_update_option( 'redirect_log_tables_installed', 1 );
			} // if( $infinity23_data_array['redirect_log_enabled'] == 1 && !infinity23_redirect_log_table_exists() ) {

			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-redirect-301-and-302', 'saved' => '1', 'redirect_tab' => 'settings' ), admin_url('admin.php') );

			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} elseif( isset( $_POST['infinity23_redirect_nonce_main'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_redirect_nonce_main'] ) ), 'infinity23_redirect_action_main' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_form_submit_name = infinity23_post_sanitize('form_submit_name', 'text');

			if( empty( $infinity23_form_submit_name ) ) {

				$infinity23_error_message = array( "You have to choose a name for the redirect folder" );

			} else {

				$infinity23_form_slug_name = sanitize_text_field( str_replace( " ", "_", strtolower( trim( $infinity23_form_submit_name ) ) ) );

				if( infinity23_redirect_folder_slug_exists( $infinity23_form_slug_name ) ) {

					$infinity23_error_message = array( "The redirect name already exists please choose a different name" );

				} else {

					infinity23_redirect_add_folder( $infinity23_form_submit_name, $infinity23_form_slug_name );
					$infinity23_success_message = "The redirect folder was created successfully";

				} // if( infinity23_redirect_folder_slug_exists( $infinity23_form_slug_name ) ) {

			} // if( empty( $infinity23_form_submit_name ) ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} elseif( isset( $_POST['infinity23_redirect_nonce_remove'] ) ) {

		$infinity23_remove_folder_id = infinity23_post_sanitize('remove_redirect_id', 'intval');

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_redirect_nonce_remove'] ) ), 'infinity23_redirect_action_remove_' . $infinity23_remove_folder_id ) && current_user_can( 'manage_options' ) ) {

			if( !empty( $infinity23_remove_folder_id ) && !empty( infinity23_redirect_get_folder( $infinity23_remove_folder_id ) ) ) {

				infinity23_redirect_delete_folder( $infinity23_remove_folder_id );
				$infinity23_success_message = "The redirect folder #" . $infinity23_remove_folder_id . " was removed successfully";

			} else {
				$infinity23_error_message = array( "The redirect folder was not found" );
			} // if( !empty( $infinity23_remove_folder_id ) && !empty(

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_redirect_reset_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_redirect_301_and_302_hide_menu = intval( infinity23_pro_get_data( 'redirect_301_and_302_hide_menu', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_col_request_url = intval( infinity23_pro_get_data( 'redirect_col_request_url', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_col_destination_url = intval( infinity23_pro_get_data( 'redirect_col_destination_url', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_col_redirect_type = intval( infinity23_pro_get_data( 'redirect_col_redirect_type', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_col_notes = intval( infinity23_pro_get_data( 'redirect_col_notes', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_col_advertising_platform = intval( infinity23_pro_get_data( 'redirect_col_advertising_platform', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_col_redirect_query = intval( infinity23_pro_get_data( 'redirect_col_redirect_query', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_col_get_parameters = intval( infinity23_pro_get_data( 'redirect_col_get_parameters', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_col_redirects_amount = intval( infinity23_pro_get_data( 'redirect_col_redirects_amount', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_advance_view_mode = infinity23_pro_get_data( 'redirect_advance_view_mode', $infinity23_data_array, $infinity23_defaults );
	$infinity23_redirect_allow_bulk_edit = intval( infinity23_pro_get_data( 'redirect_allow_bulk_edit', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_shortener_enabled = intval( infinity23_pro_get_data( 'redirect_shortener_enabled', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_shortener_code_length = intval( infinity23_pro_get_data( 'redirect_shortener_code_length', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_shortener_prefix = infinity23_pro_get_data( 'redirect_shortener_prefix', $infinity23_data_array, $infinity23_defaults );
	$infinity23_redirect_log_enabled = intval( infinity23_pro_get_data( 'redirect_log_enabled', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_log_retention_days = intval( infinity23_pro_get_data( 'redirect_log_retention_days', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_log_max_count = intval( infinity23_pro_get_data( 'redirect_log_max_count', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_redirect_log_per_page = intval( infinity23_pro_get_data( 'redirect_log_per_page', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_redirect_log_filter_row_id = ( $infinity23_redirect_log_enabled == 1 && isset( $_GET['redirect_log_row_id'] ) ) ? intval( wp_unslash( $_GET['redirect_log_row_id'] ) ) : 0;
	$infinity23_redirect_logs_tab_is_default = ( $infinity23_redirect_log_enabled == 1 ) && ( !empty( $infinity23_redirect_log_filter_row_id ) || isset( $_GET['log_search'] ) || isset( $_GET['log_page'] ) || isset( $_GET['log_per_page'] ) );

	// --------------------------------------------
	// active tab - url based instead of javascript, so each tab has its own link
	// --------------------------------------------

	$infinity23_redirect_active_tab = isset( $_GET['redirect_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_tab'] ) ) : '';

	if( !in_array( $infinity23_redirect_active_tab, array( 'folders', 'settings', 'logs' ), true ) ) {
		$infinity23_redirect_active_tab = $infinity23_redirect_logs_tab_is_default ? 'logs' : 'folders';
	} // if( !in_array( $infinity23_redirect_active_tab, array( 'folders', 'settings', 'logs' ), true ) ) {

	$infinity23_redirect_tab_folders_url = esc_url( add_query_arg( 'redirect_tab', 'folders', remove_query_arg( array( 'log_page', 'log_search', 'log_per_page', 'redirect_log_row_id' ) ) ) );
	$infinity23_redirect_tab_settings_url = esc_url( add_query_arg( 'redirect_tab', 'settings', remove_query_arg( array( 'log_page', 'log_search', 'log_per_page', 'redirect_log_row_id' ) ) ) );
	$infinity23_redirect_tab_logs_url = esc_url( add_query_arg( 'redirect_tab', 'logs' ) );

	$infinity23_redirect_folders_list = $infinity23_activate_code == 1 ? infinity23_redirect_get_folders() : array();

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp redirect-301-and-302-warp no-floating-save">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Redirect 301/302</h1>
		<p>Below you can create redirect folders to group your 301 (permanent) and 302 (temporary) redirects:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_redirect_activate_action', 'infinity23_redirect_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Redirect 301/302</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<div class="infinity23-module-tabs-wrap">

		<div class="infinity23-module-tabs">
			<a href="<?php echo esc_url( $infinity23_redirect_tab_folders_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_redirect_active_tab === 'folders' ) { echo 'infinity23-module-tab-active'; } ?>">Redirect Folders</a>
			<a href="<?php echo esc_url( $infinity23_redirect_tab_settings_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_redirect_active_tab === 'settings' ) { echo 'infinity23-module-tab-active'; } ?>">Settings</a>
			<?php if( $infinity23_redirect_log_enabled == 1 ) { ?>
				<a href="<?php echo esc_url( $infinity23_redirect_tab_logs_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_redirect_active_tab === 'logs' ) { echo 'infinity23-module-tab-active'; } ?>">Redirect Log</a>
			<?php } // if( $infinity23_redirect_log_enabled == 1 ) { ?>
		</div><!--infinity23-module-tabs-->

		<div class="infinity23-module-tab-content <?php if( $infinity23_redirect_active_tab !== 'folders' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="redirect-tab-folders">

	<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

		<div class="section-card" data-card-default="open">

			<h2>Add Redirect Folder:</h2>

			<form class="infinity23-form" method="POST" action="">
				<?php wp_nonce_field( 'infinity23_redirect_action_main', 'infinity23_redirect_nonce_main' ); ?>

				<div class="form-line">
					<label for="form_submit_name">New Redirect Folder Name: </label>
					<input type="text" id="form_submit_name" class="long-input" name="form_submit_name" value="" />
					<input type="submit" name="submit_new_form" class="submit-btn infinity23-redirect-inline-btn" value="Add Redirect Folder" />
				</div><!--form-line-->

			</form>

		</div><!--section-card-->

		<div class="section-card" data-card-default="open">

			<h2>Redirect Folders:</h2>

			<div class="infinity23-redirect-table-wrap">
			<table class="infinity23-redirect-table">
				<thead>
					<tr>
						<th class="redirect-th-id">ID</th>
						<th>Folder Name</th>
						<th class="redirect-th-count-folder">Redirects Count</th>
						<th class="redirect-th-action">Edit</th>
						<th class="redirect-th-action">Remove</th>
					</tr>
				</thead>
				<tbody>

					<?php if( empty( $infinity23_redirect_folders_list ) ) { ?>

						<tr><td colspan="5">No redirect folders were found</td></tr>

					<?php } else { ?>

						<?php foreach( $infinity23_redirect_folders_list as $infinity23_redirect_folder ) { ?>

							<?php $infinity23_redirect_folder_edit_url = add_query_arg( array( 'view' => 'folder', 'folder_id' => $infinity23_redirect_folder->id ) ); ?>

							<tr>
								<td><a href="<?php echo esc_url( $infinity23_redirect_folder_edit_url ); ?>"><?php echo esc_html( $infinity23_redirect_folder->id ); ?></a></td>
								<td><?php if( infinity23_redirect_folder_is_favorite( $infinity23_redirect_folder ) ) { ?><span class="infinity23-redirect-favorite-badge" title="Was pinned to top from the folder's settings">&#9733;</span> <?php } ?><a href="<?php echo esc_url( $infinity23_redirect_folder_edit_url ); ?>"><?php echo esc_html( $infinity23_redirect_folder->name ); ?></a></td>
								<td><?php echo esc_html( infinity23_redirect_count_rows_by_folder( $infinity23_redirect_folder->id ) ); ?></td>
								<td>

									<a class="infinity23-redirect-action-btn" href="<?php echo esc_url( $infinity23_redirect_folder_edit_url ); ?>">Edit Redirects</a>

								</td>
								<td>

									<form class="infinity23-redirect-remove-form" method="POST" action="">
										<?php wp_nonce_field( 'infinity23_redirect_action_remove_' . $infinity23_redirect_folder->id, 'infinity23_redirect_nonce_remove' ); ?>
										<input type="hidden" name="remove_redirect_id" value="<?php echo esc_attr( $infinity23_redirect_folder->id ); ?>" />
										<button type="button" class="infinity23-redirect-action-btn infinity23-redirect-action-btn-danger infinity23-redirect-remove-btn" data-confirm="Are you sure you want to permanently remove this redirect folder and all its redirects?">Delete Folder</button>
									</form>

								</td>
							</tr>

						<?php } // foreach( $infinity23_redirect_folders_list as $infinity23_redirect_folder ) { ?>

					<?php } // if( empty( $infinity23_redirect_folders_list ) ) { ?>

				</tbody>
			</table>

		</div><!--infinity23-redirect-table-wrap-->

			<?php if( count( $infinity23_redirect_folders_list ) > 1 ) { ?>
				<p class="infinity23-redirect-view-all-wrap">
					<a class="infinity23-redirect-action-btn" href="<?php echo esc_url( add_query_arg( array( 'view' => 'folder', 'view_all' => '1' ), remove_query_arg( 'folder_id' ) ) ); ?>">View All Redirects On One Page (Bulk Edit)</a>
				</p><!--infinity23-redirect-view-all-wrap-->
			<?php } // if( count( $infinity23_redirect_folders_list ) > 1 ) { ?>

		</div><!--section-card-->

	</div><!--main-form-wrap-->

		</div><!--infinity23-module-tab-content-->

		<div class="infinity23-module-tab-content <?php if( $infinity23_redirect_active_tab !== 'settings' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="redirect-tab-settings">

	<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

	<form class="infinity23-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_redirect_settings_action', 'infinity23_redirect_settings_nonce' ); ?>

		<div class="section-card" data-card-default="open">

			<h2>Redirect Table Settings:</h2>

			<h3>Column Visibility:</h3>

			<p>Choose which columns should be visible in the redirects table and in the Add Redirect form. Hidden columns are not deleted, they are only hidden from view.</p>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="redirect_col_redirect_type" name="redirect_col_redirect_type" value="1" <?php if( $infinity23_redirect_col_redirect_type == 1 ) { echo 'checked'; } ?> />
				<label for="redirect_col_redirect_type">Show Type column</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="redirect_col_request_url" name="redirect_col_request_url" value="1" <?php if( $infinity23_redirect_col_request_url == 1 ) { echo 'checked'; } ?> />
				<label for="redirect_col_request_url">Show Request URL column</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="redirect_col_destination_url" name="redirect_col_destination_url" value="1" <?php if( $infinity23_redirect_col_destination_url == 1 ) { echo 'checked'; } ?> />
				<label for="redirect_col_destination_url">Show Destination URL column</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="redirect_col_redirect_query" name="redirect_col_redirect_query" value="1" <?php if( $infinity23_redirect_col_redirect_query == 1 ) { echo 'checked'; } ?> />
				<label for="redirect_col_redirect_query">Show Query column</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="redirect_col_get_parameters" name="redirect_col_get_parameters" value="1" <?php if( $infinity23_redirect_col_get_parameters == 1 ) { echo 'checked'; } ?> />
				<label for="redirect_col_get_parameters">Show GET Parameters column</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="redirect_col_notes" name="redirect_col_notes" value="1" <?php if( $infinity23_redirect_col_notes == 1 ) { echo 'checked'; } ?> />
				<label for="redirect_col_notes">Show Notes column</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="redirect_col_advertising_platform" name="redirect_col_advertising_platform" value="1" <?php if( $infinity23_redirect_col_advertising_platform == 1 ) { echo 'checked'; } ?> />
				<label for="redirect_col_advertising_platform">Show Affiliate Platform column</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="redirect_col_redirects_amount" name="redirect_col_redirects_amount" value="1" <?php if( $infinity23_redirect_col_redirects_amount == 1 ) { echo 'checked'; } ?> />
				<label for="redirect_col_redirects_amount">Show Redirects Count column</label>
			</div><!--form-line-->

		</div><!--section-card-->

		<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="open">

			<h2>Advanced Settings: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

			<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

			<h3>Bulk Redirect Insert:</h3>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="redirect_allow_bulk_edit" name="redirect_allow_bulk_edit" value="1" <?php if( $infinity23_redirect_allow_bulk_edit == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<label for="redirect_allow_bulk_edit">Allow the Bulk Redirect Insert tab on the Add Redirect screen</label>
			</div><!--form-line-->

			<h3>Redirect Form View:</h3>

			<div class="form-line">
				<label for="redirect_advance_view_mode">Form View Mode: </label>
				<select id="redirect_advance_view_mode" name="redirect_advance_view_mode" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?>>
					<option value="compact" <?php if( $infinity23_redirect_advance_view_mode === 'compact' ) { echo 'selected'; } ?>>Compact View (advance fields hidden behind a button)</option>
					<option value="full" <?php if( $infinity23_redirect_advance_view_mode === 'full' ) { echo 'selected'; } ?>>Full View (all fields always visible)</option>
				</select>
				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						<strong>Compact View</strong> - Redirect Query, Pass URL GET Parameters, Notes and Affiliate Platform are hidden behind a "View Advance Redirect Settings" button.
						<br /><br />
						<strong>Full View</strong> - every field is always visible on the Add Redirect form, no button needed.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<h3>URL Shortener System Settings:</h3>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="redirect_shortener_enabled" name="redirect_shortener_enabled" value="1" <?php if( $infinity23_redirect_shortener_enabled == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<label for="redirect_shortener_enabled">Enable the URL Shortener button on the Add Redirect screen</label>
			</div><!--form-line-->

			<div class="form-line">
				<label for="redirect_shortener_code_length">Short Code Length: </label>
				<select id="redirect_shortener_code_length" name="redirect_shortener_code_length" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?>>
					<?php for( $infinity23_shortener_length_option = 5; $infinity23_shortener_length_option <= 10; $infinity23_shortener_length_option++ ) { ?>
						<option value="<?php echo esc_attr( $infinity23_shortener_length_option ); ?>" <?php if( $infinity23_redirect_shortener_code_length == $infinity23_shortener_length_option ) { echo 'selected'; } ?>><?php echo esc_html( $infinity23_shortener_length_option ); ?></option>
					<?php } // for( $infinity23_shortener_length_option = 5; $infinity23_shortener_length_option <= 10; $infinity23_shortener_length_option++ ) { ?>
				</select>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						How many random letters and numbers the generated short code will contain, for example a length of 7 generates a code like <span class="code">yplzvhg</span>.
						<br /><br />
						Only lowercase letters and numbers are used, and every generated code is checked against your existing redirects so it can never duplicate one already in use.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line">
				<label for="redirect_shortener_prefix">Prefix Short URL Folder <small>(goes before the link like: /folder/short-url/)</small>: </label>
				<input type="text" id="redirect_shortener_prefix" class="medium-input" name="redirect_shortener_prefix" value="<?php echo esc_attr( $infinity23_redirect_shortener_prefix ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						A folder added before every generated short code, so the generated URL is prefixed with it. For example, entering <span class="code">go</span> generates a Request URL like <span class="code">/go/yplzvhg</span>.
						<br /><br />
						Leave empty to generate the short code directly at the site root, like <span class="code">/yplzvhg</span>.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

		</div><!--section-card-->

		<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="open">

			<h2>Save Redirects Log: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

			<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="redirect_log_enabled" name="redirect_log_enabled" value="1" <?php if( $infinity23_redirect_log_enabled == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<label for="redirect_log_enabled">Keep a log every time a visitor is redirected</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						When enabled, every time a visitor's URL matches a saved redirect and they are sent to the destination, a log entry is saved with the request URL, destination URL, redirect type, visitor IP, user agent and referrer.
						<br /><br />
						This only logs redirects that actually fire - visiting a normal page that has no redirect never creates a log entry.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line">
				<label for="redirect_log_retention_days">Delete Logs Older Than (Days): </label>
				<input type="text" id="redirect_log_retention_days" class="short-input" name="redirect_log_retention_days" value="<?php echo esc_attr( $infinity23_redirect_log_retention_days ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Log entries older than this number of days are automatically removed by the daily cron.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line">
				<label for="redirect_log_max_count">Maximum Log Entries To Keep: </label>
				<input type="text" id="redirect_log_max_count" class="short-input" name="redirect_log_max_count" value="<?php echo esc_attr( $infinity23_redirect_log_max_count ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						If the total number of saved log entries goes above this number, the oldest entries beyond this limit are removed by the daily cron, regardless of their age.
						<br /><br />
						Set this to a high number like 99999 if you mainly want logs cleared by age instead.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<h3>Logs Pagination:</h3>

			<div class="form-line">
				<label for="redirect_log_per_page">Default Log Entries Per Page: </label>
				<select id="redirect_log_per_page" name="redirect_log_per_page" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?>>
					<?php foreach( array( 10, 25, 50, 100, 200 ) as $infinity23_redirect_log_per_page_default_option ) { ?>
						<option value="<?php echo esc_attr( $infinity23_redirect_log_per_page_default_option ); ?>" <?php if( $infinity23_redirect_log_per_page == $infinity23_redirect_log_per_page_default_option ) { echo 'selected'; } ?>><?php echo esc_html( $infinity23_redirect_log_per_page_default_option ); ?></option>
					<?php } // foreach( array( 10, 25, 50, 100, 200 ) as $infinity23_redirect_log_per_page_default_option ) { ?>
				</select>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						How many log entries are shown per page on the Logs tab, before you need to click Older to see more.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

		</div><!--section-card-->

		<?php infinity23_menu_visibility_field( 'redirect_301_and_302_hide_menu', $infinity23_redirect_301_and_302_hide_menu, $infinity23_module_display_name ); ?>

		<input type="submit" name="submit_redirect_settings" class="submit-btn" value="Save Settings" />

	</form>

	</div><!--main-form-wrap-->

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_redirect_reset_action', 'infinity23_redirect_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn" data-confirm="Are you sure you want to reset all Redirect 301/302 settings? Your folders and redirects will not be affected.">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings (redirects and folders will not be affected)</button>
		</form>
	</div><!--infinity23-reset-wrap-->

		</div><!--infinity23-module-tab-content-->

		<?php if( $infinity23_redirect_log_enabled == 1 ) { ?>

		<div class="infinity23-module-tab-content <?php if( $infinity23_redirect_active_tab !== 'logs' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="redirect-tab-logs">

			<?php

				$infinity23_redirect_log_page = isset( $_GET['log_page'] ) ? max( 1, intval( wp_unslash( $_GET['log_page'] ) ) ) : 1;
				$infinity23_redirect_log_search = isset( $_GET['log_search'] ) ? sanitize_text_field( wp_unslash( $_GET['log_search'] ) ) : '';
				$infinity23_redirect_log_per_page_choice = isset( $_GET['log_per_page'] ) ? intval( wp_unslash( $_GET['log_per_page'] ) ) : 0;
				// the select only offers up to 200 - a hand-edited url asking for a
				// million rows would hydrate them all into objects and kill the page
				$infinity23_redirect_log_per_page_choice = min( $infinity23_redirect_log_per_page_choice, 200 );
				$infinity23_redirect_log_per_page = $infinity23_redirect_log_per_page_choice > 0 ? $infinity23_redirect_log_per_page_choice : ( $infinity23_redirect_log_per_page > 0 ? $infinity23_redirect_log_per_page : 50 );
				$infinity23_redirect_log_total = infinity23_redirect_log_count( $infinity23_redirect_log_filter_row_id, $infinity23_redirect_log_search );
				$infinity23_redirect_log_total_pages = max( 1, ceil( $infinity23_redirect_log_total / $infinity23_redirect_log_per_page ) );
				$infinity23_redirect_log_rows = infinity23_redirect_log_get_rows( $infinity23_redirect_log_page, $infinity23_redirect_log_per_page, $infinity23_redirect_log_filter_row_id, $infinity23_redirect_log_search );

			?>

			<div class="section-card" data-card-default="open">

				<h2>Redirect Logs:</h2>

				<?php if( empty( $infinity23_redirect_log_filter_row_id ) ) { ?>
					<p class="infinity23-notice-text">Logs older than <?php echo esc_html( $infinity23_redirect_log_retention_days ); ?> days, or beyond the most recent <?php echo esc_html( number_format( $infinity23_redirect_log_max_count ) ); ?> entries, are removed automatically once a day.</p>
				<?php } // if( empty( $infinity23_redirect_log_filter_row_id ) ) { ?>

				<?php if( !empty( $infinity23_redirect_log_filter_row_id ) ) { ?>

					<?php $infinity23_redirect_log_filtered_row = infinity23_redirect_get_row( $infinity23_redirect_log_filter_row_id ); ?>

					<?php if( !empty( $infinity23_redirect_log_filtered_row ) ) { ?>
						<p>Showing logs for <strong><?php echo esc_html( $infinity23_redirect_log_filtered_row->request_url ); ?></strong> only. <a href="<?php echo esc_url( remove_query_arg( array( 'redirect_log_row_id', 'log_page', 'log_search', 'log_per_page' ) ) ); ?>">View logs for all directs (main log page)</a></p>
					<?php } else { ?>
						<p>Showing logs for Redirect #<?php echo esc_html( $infinity23_redirect_log_filter_row_id ); ?> (redirect no longer exists) only. <a href="<?php echo esc_url( remove_query_arg( array( 'redirect_log_row_id', 'log_page', 'log_search', 'log_per_page' ) ) ); ?>">View logs for all directs (main log page)</a></p>
					<?php } // if( !empty( $infinity23_redirect_log_filtered_row ) ) { ?>

					<p class="infinity23-notice-text">This count can be lower than the redirect's total, since logging may have started after the redirect did, or older entries may have already been cleaned up.</p>

				<?php } // if( !empty( $infinity23_redirect_log_filter_row_id ) ) { ?>

				<form method="GET" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
					<input type="hidden" name="page" value="essential-redirect-301-and-302" />
					<?php if( !empty( $infinity23_redirect_log_filter_row_id ) ) { ?>
						<input type="hidden" name="redirect_log_row_id" value="<?php echo esc_attr( $infinity23_redirect_log_filter_row_id ); ?>" />
					<?php } // if( !empty( $infinity23_redirect_log_filter_row_id ) ) { ?>

					<div class="form-line">
						<label for="log_search">Search Request/Destination URL: </label>
						<input type="text" id="log_search" class="medium-input" name="log_search" value="<?php echo esc_attr( $infinity23_redirect_log_search ); ?>" />

						<label for="log_per_page">Entries Per Page: </label>
						<select id="log_per_page" name="log_per_page">
							<?php foreach( array( 10, 25, 50, 100, 200 ) as $infinity23_redirect_log_per_page_option ) { ?>
								<option value="<?php echo esc_attr( $infinity23_redirect_log_per_page_option ); ?>" <?php if( $infinity23_redirect_log_per_page == $infinity23_redirect_log_per_page_option ) { echo 'selected'; } ?>><?php echo esc_html( $infinity23_redirect_log_per_page_option ); ?></option>
							<?php } // foreach( array( 10, 25, 50, 100, 200 ) as $infinity23_redirect_log_per_page_option ) { ?>
						</select>

						<input type="submit" class="infinity23-inline-filter-btn" value="Filter Logs" />

						<?php if( !empty( $infinity23_redirect_log_search ) ) { ?>
							<a class="infinity23-redirect-action-btn" href="<?php echo esc_url( remove_query_arg( array( 'log_search', 'log_page' ) ) ); ?>">Clear Search</a>
						<?php } // if( !empty( $infinity23_redirect_log_search ) ) { ?>
					</div><!--form-line-->
				</form>

				<div class="infinity23-redirect-table-wrap">
				<table class="infinity23-redirect-table">
					<thead>
						<tr>
							<th>Date</th>
							<th>Request URL</th>
							<th>Actual Visited URL</th>
							<th>Destination URL</th>
							<th>Type</th>
							<th>IP Address</th>
							<th>Browser</th>
							<th>Platform</th>
							<th>Device</th>
							<th>User Agent</th>
							<th>Referrer</th>
						</tr>
					</thead>
					<tbody>

						<?php if( empty( $infinity23_redirect_log_rows ) ) { ?>

							<tr><td colspan="11">No log entries were found</td></tr>

						<?php } else { ?>

							<?php foreach( $infinity23_redirect_log_rows as $infinity23_redirect_log_row ) { ?>

								<tr>
									<td><?php echo esc_html( infinity23_format_timestamp( $infinity23_redirect_log_row->time_created_utc, 'd/m/Y H:i' ) ); ?></td>
									<td><?php echo esc_html( $infinity23_redirect_log_row->request_url ); ?></td>
									<td><?php echo esc_html( $infinity23_redirect_log_row->actual_request_url ); ?></td>
									<td><?php echo esc_html( $infinity23_redirect_log_row->destination_url ); ?></td>
									<td><?php echo esc_html( $infinity23_redirect_log_row->redirect_type ); ?></td>
									<td><?php echo esc_html( $infinity23_redirect_log_row->ip_address ); ?></td>
									<td><?php echo esc_html( $infinity23_redirect_log_row->browser ); ?></td>
									<td><?php echo esc_html( $infinity23_redirect_log_row->platform ); ?></td>
									<td><?php echo esc_html( $infinity23_redirect_log_row->device_type ); ?></td>
									<td><?php echo esc_html( $infinity23_redirect_log_row->user_agent ); ?></td>
									<td><?php echo esc_html( $infinity23_redirect_log_row->referrer ); ?></td>
								</tr>

							<?php } // foreach( $infinity23_redirect_log_rows as $infinity23_redirect_log_row ) { ?>

						<?php } // if( empty( $infinity23_redirect_log_rows ) ) { ?>

					</tbody>
				</table>
				</div><!--infinity23-redirect-table-wrap-->

				<?php infinity23_log_pagination( $infinity23_redirect_log_page, $infinity23_redirect_log_total_pages, 'infinity23-redirect-action-btn', $infinity23_redirect_log_total ); ?>

			</div><!--section-card-->

			<?php if( infinity23_redirect_log_count() > 0 ) { ?>

			<div class="infinity23-danger-zone-warp infinity23-log-danger-zone-warp" data-card-default="closed">

				<h2><span class="infinity23-danger-zone-icon">&#9888;</span> Remove Logs Danger Zone</h2>

				<p>Permanently deletes every redirect log entry below. This cannot be undone.</p>

				<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="infinity23_redirect_log_clear_all" />
					<?php wp_nonce_field( 'infinity23_redirect_log_clear_all_action', 'infinity23_redirect_log_clear_all_nonce' ); ?>
					<button type="button" class="infinity23-reset-btn infinity23-danger-zone-btn" data-confirm="Delete ALL redirect log entries? This action cannot be undone.">Clear All Log Entries</button>
				</form>

			</div><!--infinity23-danger-zone-warp-->

			<?php } // if( infinity23_redirect_log_count() > 0 ) { ?>

		</div><!--infinity23-module-tab-content-->

		<?php } // if( $infinity23_redirect_log_enabled == 1 ) { ?>

	</div><!--infinity23-module-tabs-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
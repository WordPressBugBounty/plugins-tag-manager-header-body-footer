<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// =================================================================================================================
	// Helper - read one field of one row from the bulk update form
	// Returns '' when the value is missing or arrived as an array, so a crafted
	// request can never reach esc_url_raw() / sanitize_text_field() with an array
	// =================================================================================================================

	if( !function_exists( 'infinity23_redirect_get_row_field' ) ) {

		function infinity23_redirect_get_row_field( $field_name, $row_id ) {

			// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- its only caller is inside the nonce-verified 'infinity23_redirect_update_rows_action_*' block further down this file; the isset()/is_array() checks below never touch the value, and the actual value is wp_unslash()'d on return
			if( !isset( $_POST[ $field_name ] ) || !is_array( $_POST[ $field_name ] ) ) {
				return '';
			} // if( !isset( $_POST[ $field_name ] ) || !is_array( $_POST[ $field_name ] ) ) {

			if( !isset( $_POST[ $field_name ][ $row_id ] ) || is_array( $_POST[ $field_name ][ $row_id ] ) ) {
				return '';
			} // if( !isset( $_POST[ $field_name ][ $row_id ] ) || is_array( $_POST[ $field_name ][ $row_id ] ) ) {

			return trim( wp_unslash( strval( $_POST[ $field_name ][ $row_id ] ) ) );
			// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		} // function infinity23_redirect_get_row_field( $field_name, $row_id ) {

	} // if( !function_exists( 'infinity23_redirect_get_row_field' ) ) {

	// =================================================================================================================
	// Load the folder being edited (skipped entirely in View All mode - no single folder context)
	// =================================================================================================================

	$infinity23_redirect_current_folder = $infinity23_redirect_view_all_mode ? null : infinity23_redirect_get_folder( $infinity23_redirect_view_folder_id );

	if( !$infinity23_redirect_view_all_mode && empty( $infinity23_redirect_current_folder ) ) {

		$infinity23_error_message = array( "The redirect folder was not found" );

	} else {

		// =================================================================================================================
		// Rename the folder (not available in View All mode)
		// =================================================================================================================

		if( !$infinity23_redirect_view_all_mode && isset( $_POST['infinity23_redirect_folder_rename_nonce'] ) ) {

			if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_redirect_folder_rename_nonce'] ) ), 'infinity23_redirect_folder_rename_action_' . $infinity23_redirect_view_folder_id ) && current_user_can( 'manage_options' ) ) {

				$infinity23_redirect_rename_value = infinity23_post_sanitize('folder_rename_name', 'text');

				if( empty( $infinity23_redirect_rename_value ) ) {

					$infinity23_error_message = array( "You have to choose a name for the redirect folder" );

				} else {

					$infinity23_redirect_rename_slug = sanitize_text_field( str_replace( " ", "_", strtolower( trim( $infinity23_redirect_rename_value ) ) ) );

					infinity23_redirect_update_folder( $infinity23_redirect_view_folder_id, $infinity23_redirect_rename_value, $infinity23_redirect_rename_slug );

					$infinity23_redirect_folder_favorite_value = infinity23_post_sanitize('folder_favorite', 'intval');
					infinity23_redirect_set_folder_favorite( $infinity23_redirect_view_folder_id, $infinity23_redirect_folder_favorite_value == 1 );

					$infinity23_redirect_current_folder = infinity23_redirect_get_folder( $infinity23_redirect_view_folder_id );

					$infinity23_success_message = "The redirect folder was renamed successfully";

				} // if( empty( $infinity23_redirect_rename_value ) ) {

			} else {
				$infinity23_error_message = array( "Form nonce was incorrect" );
			} // if( wp_verify_nonce(

		} // if( isset( $_POST['infinity23_redirect_folder_rename_nonce'] ) ) {

		// =================================================================================================================
		// Add a new redirect row (not available in View All mode)
		// =================================================================================================================

		if( !$infinity23_redirect_view_all_mode && isset( $_POST['infinity23_redirect_add_row_nonce'] ) ) {

			if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_redirect_add_row_nonce'] ) ), 'infinity23_redirect_add_row_action_' . $infinity23_redirect_view_folder_id ) && current_user_can( 'manage_options' ) ) {

				$infinity23_redirect_new_request_url = infinity23_post_sanitize('request_url', 'url');
				$infinity23_redirect_new_destination_url = infinity23_post_sanitize('destination_url', 'url');
				$infinity23_redirect_new_type = infinity23_post_sanitize('redirect_type', 'text');
				$infinity23_redirect_new_notes = infinity23_post_sanitize('notes', 'text');
				$infinity23_redirect_new_platform = infinity23_post_sanitize('advertising_platform', 'text');
				$infinity23_redirect_new_query = infinity23_post_sanitize('redirect_query', 'text');
				$infinity23_redirect_new_get_parameters = infinity23_post_sanitize('get_parameters', 'intval');

				$infinity23_redirect_add_row_errors = array();

				if( empty( $infinity23_redirect_new_request_url ) || empty( $infinity23_redirect_new_destination_url ) ) {
					$infinity23_redirect_add_row_errors[] = "You must fill both the Request URL and the Destination URL";
				} // if( empty( $infinity23_redirect_new_request_url ) || empty( $infinity23_redirect_new_destination_url ) ) {

				$infinity23_redirect_new_request_url_lower = strtolower( $infinity23_redirect_new_request_url );

				if( !strstr( $infinity23_redirect_new_request_url_lower, "http://" ) && !strstr( $infinity23_redirect_new_request_url_lower, "https://" ) && ( substr( $infinity23_redirect_new_request_url_lower, 0, 1 ) != '/' ) ) {
					$infinity23_redirect_add_row_errors[] = "The Request URL must start with http://, https:// or /";
				} // if( !strstr(

				$infinity23_redirect_new_destination_url_lower = strtolower( $infinity23_redirect_new_destination_url );

				if( !strstr( $infinity23_redirect_new_destination_url_lower, "http://" ) && !strstr( $infinity23_redirect_new_destination_url_lower, "https://" ) && ( substr( $infinity23_redirect_new_destination_url_lower, 0, 1 ) != '/' ) ) {
					$infinity23_redirect_add_row_errors[] = "The Destination URL must start with http://, https:// or /";
				} // if( !strstr(

				if( !empty( $infinity23_redirect_new_request_url ) && infinity23_redirect_request_url_exists( $infinity23_redirect_new_request_url ) ) {
					$infinity23_redirect_add_row_errors[] = "This Request URL already exists, please edit the existing redirect instead";
				} // if( !empty(

				if( !empty( $infinity23_redirect_add_row_errors ) ) {

					$infinity23_error_message = $infinity23_redirect_add_row_errors;

				} else {

					infinity23_redirect_add_row( array(
						'folder_id' => $infinity23_redirect_view_folder_id,
						'position' => infinity23_redirect_get_next_position( $infinity23_redirect_view_folder_id ),
						'request_url' => $infinity23_redirect_new_request_url,
						'destination_url' => $infinity23_redirect_new_destination_url,
						'redirect_type' => $infinity23_redirect_new_type,
						'notes' => $infinity23_redirect_new_notes,
						'advertising_platform' => $infinity23_redirect_new_platform,
						'redirect_query' => $infinity23_redirect_new_query,
						'get_parameters' => $infinity23_redirect_new_get_parameters,
					) );

					$infinity23_success_message = "The new redirect was added successfully";

				} // if( !empty( $infinity23_redirect_add_row_errors ) ) {

			} else {
				$infinity23_error_message = array( "Form nonce was incorrect" );
			} // if( wp_verify_nonce(

		} // if( isset( $_POST['infinity23_redirect_add_row_nonce'] ) ) {

		// =================================================================================================================
		// check if bulk redirect insert is allowed before processing the bulk add form
		// =================================================================================================================

		$infinity23_redirect_allow_bulk_edit_check = intval( infinity23_pro_get_data( 'redirect_allow_bulk_edit', infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) ), $infinity23_defaults ) );

		// =================================================================================================================
		// Add multiple redirect rows at once (Bulk Redirect tab)
		// =================================================================================================================

		if( !$infinity23_redirect_view_all_mode && $infinity23_redirect_allow_bulk_edit_check == 1 && isset( $_POST['infinity23_redirect_bulk_add_nonce'] ) ) {

			if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_redirect_bulk_add_nonce'] ) ), 'infinity23_redirect_bulk_add_action_' . $infinity23_redirect_view_folder_id ) && current_user_can( 'manage_options' ) ) {

				// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- these are raw multi-line blobs that get split into individual lines below; each resulting line is properly run through esc_url_raw()/sanitize_text_field() a few lines down, before any use
				$infinity23_redirect_bulk_request_raw = isset( $_POST['bulk_request_url'] ) && !is_array( $_POST['bulk_request_url'] ) ? wp_unslash( $_POST['bulk_request_url'] ) : '';
				$infinity23_redirect_bulk_destination_raw = isset( $_POST['bulk_destination_url'] ) && !is_array( $_POST['bulk_destination_url'] ) ? wp_unslash( $_POST['bulk_destination_url'] ) : '';
				$infinity23_redirect_bulk_type_raw = isset( $_POST['bulk_redirect_type'] ) && !is_array( $_POST['bulk_redirect_type'] ) ? wp_unslash( $_POST['bulk_redirect_type'] ) : '';
				// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$infinity23_redirect_bulk_request_lines = preg_split( '/\r\n|\r|\n/', $infinity23_redirect_bulk_request_raw );
				$infinity23_redirect_bulk_destination_lines = preg_split( '/\r\n|\r|\n/', $infinity23_redirect_bulk_destination_raw );
				$infinity23_redirect_bulk_type_lines = preg_split( '/\r\n|\r|\n/', $infinity23_redirect_bulk_type_raw );

				$infinity23_redirect_bulk_total_lines = max( count( $infinity23_redirect_bulk_request_lines ), count( $infinity23_redirect_bulk_destination_lines ), count( $infinity23_redirect_bulk_type_lines ) );

				$infinity23_redirect_bulk_added_count = 0;
				$infinity23_redirect_bulk_errors = array();
				$infinity23_redirect_bulk_seen_request_urls = array();

				for( $infinity23_redirect_bulk_i = 0; $infinity23_redirect_bulk_i < $infinity23_redirect_bulk_total_lines; $infinity23_redirect_bulk_i++ ) {

					$infinity23_redirect_bulk_request_url = isset( $infinity23_redirect_bulk_request_lines[ $infinity23_redirect_bulk_i ] ) ? esc_url_raw( trim( $infinity23_redirect_bulk_request_lines[ $infinity23_redirect_bulk_i ] ) ) : '';
					$infinity23_redirect_bulk_destination_url = isset( $infinity23_redirect_bulk_destination_lines[ $infinity23_redirect_bulk_i ] ) ? esc_url_raw( trim( $infinity23_redirect_bulk_destination_lines[ $infinity23_redirect_bulk_i ] ) ) : '';
					$infinity23_redirect_bulk_type = isset( $infinity23_redirect_bulk_type_lines[ $infinity23_redirect_bulk_i ] ) ? sanitize_text_field( trim( $infinity23_redirect_bulk_type_lines[ $infinity23_redirect_bulk_i ] ) ) : '';

					if( empty( $infinity23_redirect_bulk_request_url ) && empty( $infinity23_redirect_bulk_destination_url ) ) {
						continue;
					} // if( empty( $infinity23_redirect_bulk_request_url ) && empty( $infinity23_redirect_bulk_destination_url ) ) {

					$infinity23_redirect_bulk_line_number = $infinity23_redirect_bulk_i + 1;

					if( empty( $infinity23_redirect_bulk_request_url ) || empty( $infinity23_redirect_bulk_destination_url ) ) {
						$infinity23_redirect_bulk_errors[] = "Line " . $infinity23_redirect_bulk_line_number . " - both Request URL and Destination URL are required, this line was skipped";
						continue;
					} // if( empty( $infinity23_redirect_bulk_request_url ) || empty( $infinity23_redirect_bulk_destination_url ) ) {

					$infinity23_redirect_bulk_request_url_lower = strtolower( $infinity23_redirect_bulk_request_url );

					if( !strstr( $infinity23_redirect_bulk_request_url_lower, "http://" ) && !strstr( $infinity23_redirect_bulk_request_url_lower, "https://" ) && ( substr( $infinity23_redirect_bulk_request_url_lower, 0, 1 ) != '/' ) ) {
						$infinity23_redirect_bulk_errors[] = "Line " . $infinity23_redirect_bulk_line_number . " - Request URL must start with http://, https:// or /, this line was skipped";
						continue;
					} // if( !strstr(

					$infinity23_redirect_bulk_destination_url_lower = strtolower( $infinity23_redirect_bulk_destination_url );

					if( !strstr( $infinity23_redirect_bulk_destination_url_lower, "http://" ) && !strstr( $infinity23_redirect_bulk_destination_url_lower, "https://" ) && ( substr( $infinity23_redirect_bulk_destination_url_lower, 0, 1 ) != '/' ) ) {
						$infinity23_redirect_bulk_errors[] = "Line " . $infinity23_redirect_bulk_line_number . " - Destination URL must start with http://, https:// or /, this line was skipped";
						continue;
					} // if( !strstr(

					if( infinity23_redirect_request_url_exists( $infinity23_redirect_bulk_request_url ) ) {
						$infinity23_redirect_bulk_errors[] = "Line " . $infinity23_redirect_bulk_line_number . " - Request URL already exists, this line was skipped";
						continue;
					} // if( infinity23_redirect_request_url_exists( $infinity23_redirect_bulk_request_url ) ) {

					if( isset( $infinity23_redirect_bulk_seen_request_urls[ $infinity23_redirect_bulk_request_url_lower ] ) ) {
						$infinity23_redirect_bulk_errors[] = "Line " . $infinity23_redirect_bulk_line_number . " - Request URL is duplicated earlier in this same bulk insert, this line was skipped";
						continue;
					} // if( isset( $infinity23_redirect_bulk_seen_request_urls[ $infinity23_redirect_bulk_request_url_lower ] ) ) {

					if( !in_array( $infinity23_redirect_bulk_type, array( '301', '302' ), true ) ) {
						$infinity23_redirect_bulk_type = '301';
					} // if( !in_array( $infinity23_redirect_bulk_type, array( '301', '302' ), true ) ) {

					infinity23_redirect_add_row( array(
						'folder_id' => $infinity23_redirect_view_folder_id,
						'position' => infinity23_redirect_get_next_position( $infinity23_redirect_view_folder_id ),
						'request_url' => $infinity23_redirect_bulk_request_url,
						'destination_url' => $infinity23_redirect_bulk_destination_url,
						'redirect_type' => $infinity23_redirect_bulk_type,
						'notes' => '',
						'advertising_platform' => '',
						'redirect_query' => 'exact',
						'get_parameters' => 0,
					) );

					$infinity23_redirect_bulk_seen_request_urls[ $infinity23_redirect_bulk_request_url_lower ] = true;
					$infinity23_redirect_bulk_added_count++;

				} // for( $infinity23_redirect_bulk_i = 0; $infinity23_redirect_bulk_i < $infinity23_redirect_bulk_total_lines; $infinity23_redirect_bulk_i++ ) {

				if( $infinity23_redirect_bulk_added_count > 0 ) {
					$infinity23_success_message = $infinity23_redirect_bulk_added_count . " redirect(s) were added successfully";
				} // if( $infinity23_redirect_bulk_added_count > 0 ) {

				if( !empty( $infinity23_redirect_bulk_errors ) ) {
					$infinity23_error_message = $infinity23_redirect_bulk_errors;
				} // if( !empty( $infinity23_redirect_bulk_errors ) ) {

			} else {
				$infinity23_error_message = array( "Form nonce was incorrect" );
			} // if( wp_verify_nonce(

		} // if( !$infinity23_redirect_view_all_mode && isset( $_POST['infinity23_redirect_bulk_add_nonce'] ) ) {

		// =================================================================================================================
		// Remove a single redirect row
		// =================================================================================================================

		if( isset( $_GET['infinity23_redirect_remove_row'] ) && isset( $_GET['row_id'] ) ) {

			$infinity23_redirect_remove_row_id = intval( wp_unslash( $_GET['row_id'] ) );
			$infinity23_redirect_remove_row_nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

			if( wp_verify_nonce( $infinity23_redirect_remove_row_nonce, 'infinity23_redirect_remove_row_action_' . $infinity23_redirect_remove_row_id ) && current_user_can( 'manage_options' ) ) {

				if( !empty( $infinity23_redirect_remove_row_id ) && !empty( infinity23_redirect_get_row( $infinity23_redirect_remove_row_id ) ) ) {

					infinity23_redirect_delete_row( $infinity23_redirect_remove_row_id );
					$infinity23_success_message = "The redirect was removed successfully";

				} else {
					$infinity23_error_message = array( "The redirect was not found" );
				} // if( !empty( $infinity23_redirect_remove_row_id ) && !empty(

			} else {
				$infinity23_error_message = array( "Form nonce was incorrect" );
			} // if( wp_verify_nonce(

		} // if( isset( $_GET['infinity23_redirect_remove_row'] ) && isset( $_GET['row_id'] ) ) {

		// =================================================================================================================
		// Bulk update all redirect rows
		// =================================================================================================================

		if( isset( $_POST['infinity23_redirect_update_rows_nonce'] ) ) {

			if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_redirect_update_rows_nonce'] ) ), 'infinity23_redirect_update_rows_action_' . $infinity23_redirect_view_folder_id ) && current_user_can( 'manage_options' ) ) {

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is intval(wp_unslash())'d in the foreach below
				$infinity23_redirect_update_row_ids = isset( $_POST['redirect_row_ids'] ) && is_array( $_POST['redirect_row_ids'] ) ? $_POST['redirect_row_ids'] : array();
				$infinity23_redirect_update_errors = array();

				foreach( $infinity23_redirect_update_row_ids as $infinity23_redirect_update_single_id ) {

					if( is_array( $infinity23_redirect_update_single_id ) ) { continue; } // if( is_array( $infinity23_redirect_update_single_id ) ) {

					$infinity23_redirect_update_row_id = intval( wp_unslash( $infinity23_redirect_update_single_id ) );

					if( empty( $infinity23_redirect_update_row_id ) ) { continue; } // if( empty( $infinity23_redirect_update_row_id ) ) {

					$infinity23_redirect_update_request_url = esc_url_raw( infinity23_redirect_get_row_field( 'request_url', $infinity23_redirect_update_row_id ) );
					$infinity23_redirect_update_destination_url = esc_url_raw( infinity23_redirect_get_row_field( 'destination_url', $infinity23_redirect_update_row_id ) );
					$infinity23_redirect_update_type = sanitize_text_field( infinity23_redirect_get_row_field( 'redirect_type', $infinity23_redirect_update_row_id ) );
					$infinity23_redirect_update_notes = sanitize_text_field( infinity23_redirect_get_row_field( 'notes', $infinity23_redirect_update_row_id ) );
					$infinity23_redirect_update_platform = sanitize_text_field( infinity23_redirect_get_row_field( 'advertising_platform', $infinity23_redirect_update_row_id ) );
					$infinity23_redirect_update_query = sanitize_text_field( infinity23_redirect_get_row_field( 'redirect_query', $infinity23_redirect_update_row_id ) );
					$infinity23_redirect_update_get_parameters = intval( infinity23_redirect_get_row_field( 'get_parameters', $infinity23_redirect_update_row_id ) );
					$infinity23_redirect_update_folder_id = intval( infinity23_redirect_get_row_field( 'folder_id', $infinity23_redirect_update_row_id ) );

					if( empty( $infinity23_redirect_update_folder_id ) || empty( infinity23_redirect_get_folder( $infinity23_redirect_update_folder_id ) ) ) {
						$infinity23_redirect_update_folder_id = $infinity23_redirect_view_folder_id;
					} // if( empty( $infinity23_redirect_update_folder_id ) || empty( infinity23_redirect_get_folder( $infinity23_redirect_update_folder_id ) ) ) {

					// only the two known values are allowed - anything else falls back to the default
					if( !in_array( $infinity23_redirect_update_type, array( '301', '302' ), true ) ) {
						$infinity23_redirect_update_type = '301';
					} // if( !in_array( $infinity23_redirect_update_type, array( '301', '302' ), true ) ) {

					if( !in_array( $infinity23_redirect_update_query, array( 'exact', 'contain' ), true ) ) {
						$infinity23_redirect_update_query = 'exact';
					} // if( !in_array( $infinity23_redirect_update_query, array( 'exact', 'contain' ), true ) ) {

					$infinity23_redirect_update_request_url_lower = strtolower( $infinity23_redirect_update_request_url );

					if( !strstr( $infinity23_redirect_update_request_url_lower, "http://" ) && !strstr( $infinity23_redirect_update_request_url_lower, "https://" ) && ( substr( $infinity23_redirect_update_request_url_lower, 0, 1 ) != '/' ) ) {
						$infinity23_redirect_update_errors[] = "Request URL #" . $infinity23_redirect_update_row_id . " must start with http://, https:// or /";
						continue;
					} // if( !strstr(

					$infinity23_redirect_update_destination_url_lower = strtolower( $infinity23_redirect_update_destination_url );

					if( !strstr( $infinity23_redirect_update_destination_url_lower, "http://" ) && !strstr( $infinity23_redirect_update_destination_url_lower, "https://" ) && ( substr( $infinity23_redirect_update_destination_url_lower, 0, 1 ) != '/' ) ) {
						$infinity23_redirect_update_errors[] = "Destination URL #" . $infinity23_redirect_update_row_id . " must start with http://, https:// or /";
						continue;
					} // if( !strstr(

					$infinity23_redirect_existing_row = infinity23_redirect_get_row( $infinity23_redirect_update_row_id );

					if( empty( $infinity23_redirect_existing_row ) ) { continue; } // if( empty( $infinity23_redirect_existing_row ) ) {

					infinity23_redirect_update_row( $infinity23_redirect_update_row_id, array(
						'folder_id' => $infinity23_redirect_update_folder_id,
						'position' => $infinity23_redirect_existing_row->position,
						'request_url' => $infinity23_redirect_update_request_url,
						'destination_url' => $infinity23_redirect_update_destination_url,
						'redirect_type' => $infinity23_redirect_update_type,
						'notes' => $infinity23_redirect_update_notes,
						'advertising_platform' => $infinity23_redirect_update_platform,
						'redirects_amount' => $infinity23_redirect_existing_row->redirects_amount,
						'redirect_query' => $infinity23_redirect_update_query,
						'get_parameters' => $infinity23_redirect_update_get_parameters,
					) );

				} // foreach( $infinity23_redirect_update_row_ids as $infinity23_redirect_update_single_id ) {

				if( !empty( $infinity23_redirect_update_errors ) ) {
					$infinity23_error_message = $infinity23_redirect_update_errors;
				} else {
					$infinity23_success_message = "The redirects were updated successfully";
				} // if( !empty( $infinity23_redirect_update_errors ) ) {

			} else {
				$infinity23_error_message = array( "Form nonce was incorrect" );
			} // if( wp_verify_nonce(

		} // if( isset( $_POST['infinity23_redirect_update_rows_nonce'] ) ) {

	} // if( empty( $infinity23_redirect_current_folder ) ) {

	// =================================================================================================================
	// load the rows for this folder
	// =================================================================================================================

	if( $infinity23_redirect_view_all_mode ) {
		$infinity23_redirect_folder_rows = infinity23_redirect_get_all_rows();
	} elseif( !empty( $infinity23_redirect_current_folder ) ) {
		$infinity23_redirect_folder_rows = infinity23_redirect_get_rows_by_folder( $infinity23_redirect_view_folder_id );
	} else {
		$infinity23_redirect_folder_rows = array();
	} // if( $infinity23_redirect_view_all_mode ) {

	$infinity23_redirect_back_url = remove_query_arg( array( 'view', 'folder_id', 'view_all' ) );
	$infinity23_redirect_all_folders_list = infinity23_redirect_get_folders();

	// --------------------------------------------
	// active tab - url based instead of javascript, so each tab has its own link
	// --------------------------------------------

	$infinity23_redirect_folder_active_tab = ( isset( $_GET['folder_tab'] ) && $_GET['folder_tab'] === 'settings' ) ? 'settings' : 'add';
	$infinity23_redirect_folder_tab_add_url = esc_url( add_query_arg( 'folder_tab', 'add' ) );
	$infinity23_redirect_folder_tab_settings_url = esc_url( add_query_arg( 'folder_tab', 'settings' ) );

	// ---------------------------------------
	// load column visibility settings
	// ---------------------------------------

	$infinity23_redirect_col_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
	$infinity23_redirect_col_request_url = intval( infinity23_pro_get_data( 'redirect_col_request_url', $infinity23_redirect_col_settings_data, $infinity23_defaults ) );
	$infinity23_redirect_col_destination_url = intval( infinity23_pro_get_data( 'redirect_col_destination_url', $infinity23_redirect_col_settings_data, $infinity23_defaults ) );
	$infinity23_redirect_col_redirect_type = intval( infinity23_pro_get_data( 'redirect_col_redirect_type', $infinity23_redirect_col_settings_data, $infinity23_defaults ) );
	$infinity23_redirect_col_notes = intval( infinity23_pro_get_data( 'redirect_col_notes', $infinity23_redirect_col_settings_data, $infinity23_defaults ) );
	$infinity23_redirect_col_advertising_platform = intval( infinity23_pro_get_data( 'redirect_col_advertising_platform', $infinity23_redirect_col_settings_data, $infinity23_defaults ) );
	$infinity23_redirect_col_redirect_query = intval( infinity23_pro_get_data( 'redirect_col_redirect_query', $infinity23_redirect_col_settings_data, $infinity23_defaults ) );
	$infinity23_redirect_col_get_parameters = intval( infinity23_pro_get_data( 'redirect_col_get_parameters', $infinity23_redirect_col_settings_data, $infinity23_defaults ) );
	$infinity23_redirect_col_redirects_amount = intval( infinity23_pro_get_data( 'redirect_col_redirects_amount', $infinity23_redirect_col_settings_data, $infinity23_defaults ) );
	$infinity23_redirect_advance_view_mode = infinity23_pro_get_data( 'redirect_advance_view_mode', $infinity23_redirect_col_settings_data, $infinity23_defaults );
	$infinity23_redirect_allow_bulk_edit = intval( infinity23_pro_get_data( 'redirect_allow_bulk_edit', $infinity23_redirect_col_settings_data, $infinity23_defaults ) );
	$infinity23_redirect_shortener_enabled = intval( infinity23_pro_get_data( 'redirect_shortener_enabled', $infinity23_redirect_col_settings_data, $infinity23_defaults ) );
	$infinity23_redirect_log_enabled = intval( infinity23_pro_get_data( 'redirect_log_enabled', $infinity23_redirect_col_settings_data, $infinity23_defaults ) );

	$infinity23_redirect_folder_name_for_title = ( !$infinity23_redirect_view_all_mode && !empty( $infinity23_redirect_current_folder ) ) ? $infinity23_redirect_current_folder->name : '';

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp redirect-301-and-302-warp no-floating-save">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1><?php echo $infinity23_redirect_view_all_mode ? 'All Redirects' : 'Edit Redirect Folder'; ?></h1>
		<p><a href="<?php echo esc_url( $infinity23_redirect_back_url ); ?>">&larr; Back to Redirect Folders</a></p>
	</div><!--page-header-->

	<?php if( $infinity23_redirect_view_all_mode || !empty( $infinity23_redirect_current_folder ) ) { ?>

		<?php if( !$infinity23_redirect_view_all_mode ) { ?>

	<div class="infinity23-module-tabs-wrap">

		<div class="infinity23-module-tabs">
			<a href="<?php echo esc_url( $infinity23_redirect_folder_tab_add_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_redirect_folder_active_tab === 'add' ) { echo 'infinity23-module-tab-active'; } ?>">Add Redirect</a>
			<a href="<?php echo esc_url( $infinity23_redirect_folder_tab_settings_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_redirect_folder_active_tab === 'settings' ) { echo 'infinity23-module-tab-active'; } ?>">Folder Settings</a>
		</div><!--infinity23-module-tabs-->

		<div class="infinity23-module-tab-content <?php if( $infinity23_redirect_folder_active_tab !== 'add' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="redirect-edit-tab-add">

		<div class="section-card" data-card-default="open">

			<h2>Add Redirect<?php if( !empty( $infinity23_redirect_folder_name_for_title ) ) { echo ' To "' . esc_html( $infinity23_redirect_folder_name_for_title ) . '"'; } ?>:</h2>

			<div class="infinity23-redirect-add-tabs">
				<button type="button" class="infinity23-redirect-add-tab-btn infinity23-redirect-add-tab-active" data-tab-target="infinity23-redirect-tab-single">Insert Single Redirect</button>
				<button type="button" class="infinity23-redirect-add-tab-btn <?php if( $infinity23_redirect_allow_bulk_edit != 1 ) { echo 'infinity23-redirect-add-tab-disabled'; } ?>" data-tab-target="infinity23-redirect-tab-bulk">Insert Bulk Redirects <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></button>
			</div><!--infinity23-redirect-add-tabs-->

			<div class="infinity23-redirect-add-tab-content" id="infinity23-redirect-tab-single">

			<form class="infinity23-form" method="POST" action="">
				<?php wp_nonce_field( 'infinity23_redirect_add_row_action_' . $infinity23_redirect_view_folder_id, 'infinity23_redirect_add_row_nonce' ); ?>

				<div class="form-line <?php if( $infinity23_redirect_col_request_url != 1 ) { echo 'infinity23-col-hidden'; } ?>">
					<label for="request_url">Request URL: </label>
					<input type="text" id="request_url" class="very-long-input" name="request_url" value="" />

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							The URL on your own site that visitors will be redirected from.
							<br /><br />
							<strong>Usage Examples:</strong>
							<ul>
								<li>A relative path - <span class="code">/go/special-offer/</span></li>
								<li>The site root - <span class="code">/old-page/</span></li>
							</ul>
						</div>
						?
					</div><!--backtest-field-help-icon-->

					<?php if( $infinity23_redirect_shortener_enabled == 1 ) { ?>
						<button type="button" id="infinity23-generate-shortcode-btn" class="infinity23-redirect-action-btn" data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'infinity23_redirect_shortcode_action' ) ); ?>">Generate Short URL</button>
					<?php } // if( $infinity23_redirect_shortener_enabled == 1 ) { ?>
				</div><!--form-line-->

				<div class="form-line <?php if( $infinity23_redirect_col_destination_url != 1 ) { echo 'infinity23-col-hidden'; } ?>">
					<label for="destination_url">Destination URL: </label>
					<input type="text" id="destination_url" class="very-long-input" name="destination_url" value="" />

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							The URL visitors will land on after the redirect.
							<br /><br />
							<strong>Usage Examples:</strong>
							<ul>
								<li>A relative path on your own site - <span class="code">/about-us/</span></li>
								<li>A full external URL - <span class="code">https://www.example.com/about-us/</span></li>
							</ul>
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line <?php if( $infinity23_redirect_col_redirect_type != 1 ) { echo 'infinity23-col-hidden'; } ?>">
					<label for="redirect_type">Redirect Type: </label>
					<select id="redirect_type" name="redirect_type">
						<option value="301">301 (Permanent)</option>
						<option value="302">302 (Temporary)</option>
					</select>
				</div><!--form-line-->

				<?php if( $infinity23_redirect_advance_view_mode === 'compact' ) { ?>
					<button type="button" class="infinity23-redirect-advance-btn">View Advance Redirect Settings</button>
				<?php } // if( $infinity23_redirect_advance_view_mode === 'compact' ) { ?>

				<div class="infinity23-redirect-advance-fields-wrap <?php if( $infinity23_redirect_advance_view_mode === 'compact' ) { echo 'infinity23-redirect-advance-hidden'; } ?>">

				<div class="form-line <?php if( $infinity23_redirect_col_redirect_query != 1 ) { echo 'infinity23-col-hidden'; } ?>">
					<label for="redirect_query">Redirect Query: </label>
					<select id="redirect_query" name="redirect_query">
						<option value="exact">Exact Match</option>
						<option value="contain">Contain Request</option>
					</select>
					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							<strong>Exact Match</strong> - the redirect only fires when the visited URL matches the Request URL exactly.
							<br /><br />
							<strong>Contain Request</strong> - the redirect fires on any URL that contains the Request URL text anywhere inside it. Use this with caution, an incorrect value can accidentally redirect many pages on your site.
							<br /><br />
							<strong>Example</strong> - Request URL: <span class="code">/go/offer/</span>
							<ul>
								<li><strong>Exact Match</strong> only catches <span class="code">/go/offer/</span></li>
								<li><strong>Contain Request</strong> also catches any url that comes after <strong>/go/offer/</strong> like: <span class="code">/go/offer/another-page/</span> and <span class="code">/go/offer/new-page/</span></li>
							</ul>
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line <?php if( $infinity23_redirect_col_get_parameters != 1 ) { echo 'infinity23-col-hidden'; } ?>">
					<label for="get_parameters">Pass URL GET Parameters: </label>
					<select id="get_parameters" name="get_parameters">
						<option value="0">Allow</option>
						<option value="1">Block</option>
					</select>
					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							<strong>Allow</strong> - keeps any GET parameters from the visited URL and passes them onto the destination URL.
							<br /><br />
							<strong>Block</strong> - drops those parameters, useful when redirecting to external sites or when passing parameters causes problems.
							<br /><br />
							<strong>Example</strong> - Visited URL: <span class="code">/go/offer/?ref=abc</span>
							<ul>
								<li><strong>Allow</strong> redirects with <span class="code">?ref=abc</span> still attached</li>
								<li><strong>Block</strong> redirects with the parameters removed</li>
							</ul>
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line <?php if( $infinity23_redirect_col_notes != 1 ) { echo 'infinity23-col-hidden'; } ?>">
					<label for="notes">Notes: </label>
					<textarea id="notes" class="medium-input" name="notes"></textarea>
				</div><!--form-line-->

				<div class="form-line <?php if( $infinity23_redirect_col_advertising_platform != 1 ) { echo 'infinity23-col-hidden'; } ?>">
					<label for="advertising_platform">Affiliate Platform: </label>
					<input type="text" id="advertising_platform" class="medium-input" name="advertising_platform" value="" />
					<span class="infinity23-redirect-field-example">Examples: CJ, Impact, ClickBank, Amazon</span>
				</div><!--form-line-->

				</div><!--infinity23-redirect-advance-fields-wrap-->

				<input type="submit" name="submit_add_row" class="submit-btn" value="Add Redirect" />

			</form>

			</div><!--infinity23-redirect-tab-single-->

			<?php if( $infinity23_redirect_allow_bulk_edit == 1 ) { ?>

			<div class="infinity23-redirect-add-tab-content infinity23-redirect-tab-hidden" id="infinity23-redirect-tab-bulk">

				<p>Add multiple redirects at once. Line 1 in every box belongs together, line 2 in every box belongs together, and so on. Leave a line in Redirect Type empty to use 301 automatically.</p>

				<form class="infinity23-form" method="POST" action="">
					<?php wp_nonce_field( 'infinity23_redirect_bulk_add_action_' . $infinity23_redirect_view_folder_id, 'infinity23_redirect_bulk_add_nonce' ); ?>

					<div class="infinity23-redirect-bulk-columns">

						<div class="infinity23-redirect-bulk-col">
							<label for="bulk_request_url">Request URL: </label>
							<div class="infinity23-line-numbered-wrap">
								<div class="infinity23-line-numbers"><span>1</span></div>
								<textarea id="bulk_request_url" name="bulk_request_url" class="infinity23-line-numbered-textarea infinity23-textarea-adjustable" rows="10" placeholder="/go/offer-1/"></textarea>
							</div><!--infinity23-line-numbered-wrap-->
						</div><!--infinity23-redirect-bulk-col-->

						<div class="infinity23-redirect-bulk-col">
							<label for="bulk_destination_url">Destination URL: </label>
							<div class="infinity23-line-numbered-wrap">
								<div class="infinity23-line-numbers"><span>1</span></div>
								<textarea id="bulk_destination_url" name="bulk_destination_url" class="infinity23-line-numbered-textarea infinity23-textarea-adjustable" rows="10" placeholder="/page-1/"></textarea>
							</div><!--infinity23-line-numbered-wrap-->
						</div><!--infinity23-redirect-bulk-col-->

						<div class="infinity23-redirect-bulk-col infinity23-redirect-bulk-col-narrow">
							<label for="bulk_redirect_type">Redirect Type:

							<div class="backtest-field-help-icon">
								<div class="data-help-content">
									Each line number across the three boxes belongs to the same redirect. Line 1 in every box together becomes one redirect, line 2 becomes the next one, and so on.
									<br /><br />
									Leave a line in Redirect Type empty to use <span class="code">301</span> automatically.
									<br /><br />
									A line where Request URL or Destination URL is empty is skipped.
								</div>
								?
							</div><!--backtest-field-help-icon-->

							</label>
							<div class="infinity23-line-numbered-wrap">
								<div class="infinity23-line-numbers"><span>1</span></div>
								<textarea id="bulk_redirect_type" name="bulk_redirect_type" class="infinity23-line-numbered-textarea infinity23-textarea-adjustable" rows="10" placeholder="301"></textarea>
							</div><!--infinity23-line-numbered-wrap-->
						</div><!--infinity23-redirect-bulk-col-->

					</div><!--infinity23-redirect-bulk-columns-->

					<input type="submit" name="submit_bulk_add_rows" class="submit-btn" value="Add Bulk Redirects" />

				</form>

			</div><!--infinity23-redirect-tab-bulk-->

			<?php } // if( $infinity23_redirect_allow_bulk_edit == 1 ) { ?>

		</div><!--section-card-->

	<?php } // if( !$infinity23_redirect_view_all_mode ) { ?>

		<div class="section-card" data-card-default="open">

			<h2>Edit Redirects<?php if( !empty( $infinity23_redirect_folder_name_for_title ) ) { echo ' On "' . esc_html( $infinity23_redirect_folder_name_for_title ) . '"'; } ?>:</h2>

			<form class="infinity23-form" method="POST" action="">
				<?php wp_nonce_field( 'infinity23_redirect_update_rows_action_' . $infinity23_redirect_view_folder_id, 'infinity23_redirect_update_rows_nonce' ); ?>

				<div class="infinity23-redirect-table-wrap">
				<table class="infinity23-redirect-table infinity23-redirect-row-table">
					<thead>
						<tr>
							<th class="<?php if( $infinity23_redirect_col_request_url != 1 ) { echo 'infinity23-col-hidden'; } ?>">Request URL</th>
							<th class="<?php if( $infinity23_redirect_col_destination_url != 1 ) { echo 'infinity23-col-hidden'; } ?>">Destination URL</th>
							<th class="redirect-th-type <?php if( $infinity23_redirect_col_redirect_type != 1 ) { echo 'infinity23-col-hidden'; } ?>">Type</th>
							<th class="redirect-th-query <?php if( $infinity23_redirect_col_redirect_query != 1 ) { echo 'infinity23-col-hidden'; } ?>">Query</th>
							<th class="redirect-th-params <?php if( $infinity23_redirect_col_get_parameters != 1 ) { echo 'infinity23-col-hidden'; } ?>">GET Params</th>
							<th class="<?php if( $infinity23_redirect_col_notes != 1 ) { echo 'infinity23-col-hidden'; } ?>">Notes</th>
							<th class="<?php if( $infinity23_redirect_col_advertising_platform != 1 ) { echo 'infinity23-col-hidden'; } ?>">Affiliate Platform</th>
							<th class="redirect-th-count-row <?php if( $infinity23_redirect_col_redirects_amount != 1 ) { echo 'infinity23-col-hidden'; } ?>">Redirects Count</th>
							<th class="redirect-th-folder">Folder</th>
							<th class="redirect-th-remove">Remove</th>
						</tr>
					</thead>
					<tbody>

						<?php if( empty( $infinity23_redirect_folder_rows ) ) { ?>

							<tr><td colspan="10">No redirects were found</td></tr>

						<?php } else { ?>

							<?php foreach( $infinity23_redirect_folder_rows as $infinity23_redirect_row ) { ?>

								<?php $infinity23_redirect_remove_url = wp_nonce_url( add_query_arg( array( 'infinity23_redirect_remove_row' => '1', 'row_id' => $infinity23_redirect_row->id ) ), 'infinity23_redirect_remove_row_action_' . $infinity23_redirect_row->id ); ?>

								<tr>
									<td class="infinity23-col-hidden">
										<input type="hidden" name="redirect_row_ids[]" value="<?php echo esc_attr( $infinity23_redirect_row->id ); ?>" />
									</td>
									<td class="<?php if( $infinity23_redirect_col_request_url != 1 ) { echo 'infinity23-col-hidden'; } ?>"><input type="text" class="input-260" name="request_url[<?php echo esc_attr( $infinity23_redirect_row->id ); ?>]" value="<?php echo esc_attr( $infinity23_redirect_row->request_url ); ?>" /></td>
									<td class="<?php if( $infinity23_redirect_col_destination_url != 1 ) { echo 'infinity23-col-hidden'; } ?>"><input type="text" class="input-260" name="destination_url[<?php echo esc_attr( $infinity23_redirect_row->id ); ?>]" value="<?php echo esc_attr( $infinity23_redirect_row->destination_url ); ?>" /></td>
									<td class="<?php if( $infinity23_redirect_col_redirect_type != 1 ) { echo 'infinity23-col-hidden'; } ?>">
										<select name="redirect_type[<?php echo esc_attr( $infinity23_redirect_row->id ); ?>]">
											<option value="301" <?php if( $infinity23_redirect_row->redirect_type === '301' ) { echo 'selected'; } ?>>301 (Permanent)</option>
											<option value="302" <?php if( $infinity23_redirect_row->redirect_type === '302' ) { echo 'selected'; } ?>>302 (Temporary)</option>
										</select>
									</td>
									<td class="<?php if( $infinity23_redirect_col_redirect_query != 1 ) { echo 'infinity23-col-hidden'; } ?>">
										<select name="redirect_query[<?php echo esc_attr( $infinity23_redirect_row->id ); ?>]">
											<option value="exact" <?php if( $infinity23_redirect_row->redirect_query === 'exact' ) { echo 'selected'; } ?>>Exact Match</option>
											<option value="contain" <?php if( $infinity23_redirect_row->redirect_query === 'contain' ) { echo 'selected'; } ?>>Contain Request</option>
										</select>
									</td>
									<td class="<?php if( $infinity23_redirect_col_get_parameters != 1 ) { echo 'infinity23-col-hidden'; } ?>">
										<select name="get_parameters[<?php echo esc_attr( $infinity23_redirect_row->id ); ?>]">
											<option value="0" <?php if( intval( $infinity23_redirect_row->get_parameters ) === 0 ) { echo 'selected'; } ?>>Allow</option>
											<option value="1" <?php if( intval( $infinity23_redirect_row->get_parameters ) === 1 ) { echo 'selected'; } ?>>Block</option>
										</select>
									</td>
									<td class="<?php if( $infinity23_redirect_col_notes != 1 ) { echo 'infinity23-col-hidden'; } ?>"><textarea class="input-160" name="notes[<?php echo esc_attr( $infinity23_redirect_row->id ); ?>]"><?php echo esc_html( $infinity23_redirect_row->notes ); ?></textarea></td>
									<td class="<?php if( $infinity23_redirect_col_advertising_platform != 1 ) { echo 'infinity23-col-hidden'; } ?>"><input type="text" class="input-160" name="advertising_platform[<?php echo esc_attr( $infinity23_redirect_row->id ); ?>]" value="<?php echo esc_attr( $infinity23_redirect_row->advertising_platform ); ?>" /></td>
									<td class="<?php if( $infinity23_redirect_col_redirects_amount != 1 ) { echo 'infinity23-col-hidden'; } ?>">
										<?php if( $infinity23_redirect_log_enabled == 1 ) { ?>
											<?php
												$infinity23_redirect_row_log_url = add_query_arg(
													array(
														'page' => 'essential-redirect-301-and-302',
														'redirect_tab' => 'logs',
														'redirect_log_row_id' => $infinity23_redirect_row->id,
													),
													remove_query_arg( array( 'view', 'folder_id', 'view_all', 'folder_tab', 'log_page', 'log_search', 'log_per_page' ), admin_url( 'admin.php' ) )
												);
											?>
											<a href="<?php echo esc_url( $infinity23_redirect_row_log_url ); ?>" class="infinity23-redirect-analytics-btn"><?php echo esc_html( $infinity23_redirect_row->redirects_amount ); ?></a>
											<a href="<?php echo esc_url( $infinity23_redirect_row_log_url ); ?>" class="infinity23-redirect-analytics-label">View Logs</a>
										<?php } else { ?>
											<?php echo esc_html( $infinity23_redirect_row->redirects_amount ); ?>
										<?php } // if( $infinity23_redirect_log_enabled == 1 ) { ?>
									</td>
									<td>
										<select name="folder_id[<?php echo esc_attr( $infinity23_redirect_row->id ); ?>]">
											<?php foreach( $infinity23_redirect_all_folders_list as $infinity23_redirect_folder_option ) { ?>
												<option value="<?php echo esc_attr( $infinity23_redirect_folder_option->id ); ?>" <?php if( intval( $infinity23_redirect_row->folder_id ) === intval( $infinity23_redirect_folder_option->id ) ) { echo 'selected'; } ?>><?php echo esc_html( $infinity23_redirect_folder_option->name ); ?></option>
											<?php } // foreach( $infinity23_redirect_all_folders_list as $infinity23_redirect_folder_option ) { ?>
										</select>
									</td>
									<td><a class="infinity23-redirect-action-btn infinity23-redirect-action-btn-danger" href="<?php echo esc_url( $infinity23_redirect_remove_url ); ?>" data-confirm="Are you sure you want to permanently remove this redirect?">Remove</a></td>
								</tr>

							<?php } // foreach( $infinity23_redirect_folder_rows as $infinity23_redirect_row ) { ?>

						<?php } // if( empty( $infinity23_redirect_folder_rows ) ) { ?>

					</tbody>
				</table>
				</div><!--infinity23-redirect-table-wrap-->

				<?php if( !empty( $infinity23_redirect_folder_rows ) ) { ?>
					<input type="submit" name="submit_update_rows" class="submit-btn submit-btn-small" value="Update Redirects" />
				<?php } // if( !empty( $infinity23_redirect_folder_rows ) ) { ?>

			</form>

		</div><!--section-card-->

	<?php if( !$infinity23_redirect_view_all_mode ) { ?>

		</div><!--infinity23-module-tab-content-->

		<div class="infinity23-module-tab-content <?php if( $infinity23_redirect_folder_active_tab !== 'settings' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="redirect-edit-tab-folder-settings">

		<div class="section-card" data-card-default="open">

			<h2>Folder Settings:</h2>

			<form class="infinity23-form" method="POST" action="">
				<?php wp_nonce_field( 'infinity23_redirect_folder_rename_action_' . $infinity23_redirect_view_folder_id, 'infinity23_redirect_folder_rename_nonce' ); ?>

				<div class="form-line">
					<label for="folder_rename_name">Redirect Folder Name: </label>
					<input type="text" id="folder_rename_name" class="long-input" name="folder_rename_name" value="<?php echo esc_attr( $infinity23_redirect_current_folder->name ); ?>" />
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="folder_favorite" name="folder_favorite" value="1" <?php if( infinity23_redirect_folder_is_favorite( $infinity23_redirect_current_folder ) ) { echo 'checked'; } ?> />
					<label for="folder_favorite">Pin this folder to the top of the Redirect Folders list</label>
				</div><!--form-line-->

				<input type="submit" name="submit_folder_rename" class="submit-btn" value="Save Folder Settings" />

			</form>

		</div><!--section-card-->

		</div><!--infinity23-module-tab-content-->

	</div><!--infinity23-module-tabs-wrap-->

	<?php } // if( !$infinity23_redirect_view_all_mode ) { ?>

	<p><a href="<?php echo esc_url( $infinity23_redirect_back_url ); ?>">&larr; Back to Redirect Folders</a></p>

	<?php } // if( $infinity23_redirect_view_all_mode || !empty( $infinity23_redirect_current_folder ) ) { ?>

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// =================================================================================================================
	// Initial plugin load and getting default values
	// =================================================================================================================

	include( dirname(__FILE__) . "/../../include/settings.php" );
	include( dirname(__FILE__) . "/internal-settings.php" );
	include( dirname(__FILE__) . "/back-end/notes-db-functions.php" );
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
	// reset all notes settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_notes_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_notes_reset_nonce'] ) ), 'infinity23_notes_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			infinity23_notes_delete_all_rows();
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_notes_reset_nonce'] ) ) {

	// ----------------------------------------
	// toggle activation form submit
	// ----------------------------------------

	if( isset( $_POST['infinity23_notes_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_notes_activate_nonce'] ) ), 'infinity23_notes_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'notes', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {

				infinity23_notes_install_table();
				$infinity23_success_message = "Page Notes were activated successfully";

			} else {
				infinity23_module_deactivate_redirect( $infinity23_module_display_name );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_notes_activate_nonce'] ) ) {

	// ----------------------------------------
	// main settings form submit
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_notes_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_notes_nonce'] ) ), 'infinity23_notes_settings_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_data_array['show_on_pages'] = infinity23_pro_field_sanitize( 'show_on_pages', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_posts'] = infinity23_pro_field_sanitize( 'show_on_posts', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_dashboard'] = infinity23_pro_field_sanitize( 'show_on_dashboard', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_admin_table'] = infinity23_pro_field_sanitize( 'show_on_admin_table', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_custom_post_types'] = infinity23_pro_field_sanitize( 'show_on_custom_post_types', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_all_cpt_slugs = array_keys( infinity23_get_custom_post_types() );
			$infinity23_active_cpt_posted = array();

			if( isset( $_POST['notes_custom_post_types_active_list'] ) && is_array( $_POST['notes_custom_post_types_active_list'] ) ) {

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is sanitize_text_field(wp_unslash())'d inside the loop
				foreach( $_POST['notes_custom_post_types_active_list'] as $infinity23_single_cpt_slug ) {

					if( is_array( $infinity23_single_cpt_slug ) ) { continue; } // if( is_array( $infinity23_single_cpt_slug ) ) {

					$infinity23_active_cpt_posted[] = sanitize_text_field( wp_unslash( $infinity23_single_cpt_slug ) );

				} // foreach( $_POST['notes_custom_post_types_active_list'] as $infinity23_single_cpt_slug ) {

			} // if( isset( $_POST['notes_custom_post_types_active_list'] ) && is_array(

			$infinity23_disabled_cpt_to_save = array_diff( $infinity23_all_cpt_slugs, $infinity23_active_cpt_posted );

			$infinity23_data_array['disabled_custom_post_types'] = implode( ',', $infinity23_disabled_cpt_to_save );

			$infinity23_data_array['allow_multiple_notes'] = infinity23_pro_field_sanitize( 'allow_multiple_notes', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_note_dates'] = infinity23_pro_field_sanitize( 'show_note_dates', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['adjustable_textarea_height'] = infinity23_pro_field_sanitize( 'adjustable_textarea_height', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['dashboard_adjustable_textarea_height'] = infinity23_pro_field_sanitize( 'dashboard_adjustable_textarea_height', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['allow_view_all_notes'] = infinity23_pro_field_sanitize( 'allow_view_all_notes', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['all_notes_allow_edit'] = infinity23_pro_field_sanitize( 'all_notes_allow_edit', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['all_notes_allow_remove'] = infinity23_pro_field_sanitize( 'all_notes_allow_remove', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['all_notes_table_direction'] = infinity23_pro_field_sanitize( 'all_notes_table_direction', $infinity23_existing_data_array, $infinity23_defaults );

			if( !in_array( $infinity23_data_array['all_notes_table_direction'], array( 'ltr', 'rtl' ), true ) ) {
				$infinity23_data_array['all_notes_table_direction'] = is_rtl() ? 'rtl' : 'ltr';
			} // if( !in_array( $infinity23_data_array['all_notes_table_direction'], array( 'ltr', 'rtl' ), true ) ) {

			$infinity23_data_array['notes_hide_menu'] = infinity23_pro_field_sanitize( 'notes_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-notes', 'saved' => '1', 'notes_tab' => 'settings' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_notes_nonce'] ) ) {

	// =================================================================================================================
	// Load saved values
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code               = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_show_on_pages = intval( infinity23_pro_get_data( 'show_on_pages', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_posts = intval( infinity23_pro_get_data( 'show_on_posts', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_dashboard = intval( infinity23_pro_get_data( 'show_on_dashboard', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_custom_post_types = intval( infinity23_pro_get_data( 'show_on_custom_post_types', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_disabled_custom_post_types = infinity23_pro_get_data( 'disabled_custom_post_types', $infinity23_data_array, $infinity23_defaults );
	$infinity23_allow_multiple_notes = intval( infinity23_pro_get_data( 'allow_multiple_notes', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_admin_table = intval( infinity23_pro_get_data( 'show_on_admin_table', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_note_dates = intval( infinity23_pro_get_data( 'show_note_dates', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_allow_view_all_notes = intval( infinity23_pro_get_data( 'allow_view_all_notes', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_adjustable_textarea_height = intval( infinity23_pro_get_data( 'adjustable_textarea_height', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_dashboard_adjustable_textarea_height = intval( infinity23_pro_get_data( 'dashboard_adjustable_textarea_height', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_all_notes_table_direction = infinity23_pro_get_data( 'all_notes_table_direction', $infinity23_data_array, $infinity23_defaults );

	if( !in_array( $infinity23_all_notes_table_direction, array( 'ltr', 'rtl' ), true ) ) {
		$infinity23_all_notes_table_direction = is_rtl() ? 'rtl' : 'ltr';
	} // if( !in_array( $infinity23_all_notes_table_direction, array( 'ltr', 'rtl' ), true ) ) {

	$infinity23_all_notes_allow_edit = intval( infinity23_pro_get_data( 'all_notes_allow_edit', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_all_notes_allow_remove = intval( infinity23_pro_get_data( 'all_notes_allow_remove', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_notes_hide_menu = intval( infinity23_pro_get_data( 'notes_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

	// --------------------------------------------
	// build the custom post type toggle rows, same pattern as Folders Organizer
	// --------------------------------------------

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

	// --------------------------------------------
	// active tab - url based instead of javascript, so each tab has its own link
	// --------------------------------------------

	$infinity23_notes_active_tab = isset( $_GET['notes_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['notes_tab'] ) ) : '';

	if( !in_array( $infinity23_notes_active_tab, array( 'settings', 'all_notes' ), true ) ) {
		$infinity23_notes_active_tab = 'settings';
	} // if( !in_array( $infinity23_notes_active_tab, array( 'settings', 'all_notes' ), true ) ) {

	if( $infinity23_notes_active_tab === 'all_notes' && $infinity23_allow_view_all_notes != 1 ) {
		$infinity23_notes_active_tab = 'settings';
	} // if( $infinity23_notes_active_tab === 'all_notes' && $infinity23_allow_view_all_notes != 1 ) {

	$infinity23_notes_tab_settings_url = esc_url( add_query_arg( 'notes_tab', 'settings' ) );
	$infinity23_notes_tab_all_notes_url = esc_url( add_query_arg( 'notes_tab', 'all_notes' ) );

	// --------------------------------------------
	// build the All Notes data, grouped by post type - only when that tab is open
	// --------------------------------------------

	$infinity23_notes_all_groups = array();

	if( $infinity23_notes_active_tab === 'all_notes' ) {

		$infinity23_notes_all_rows = infinity23_notes_get_all_rows();

		if( !empty( $infinity23_notes_all_rows ) ) {

			foreach( $infinity23_notes_all_rows as $infinity23_notes_all_row ) {

				if( $infinity23_notes_all_row->page_post_id === 'dashboard' ) {

					$infinity23_notes_group_key = 'dashboard';
					$infinity23_notes_group_label = 'Dashboard';
					$infinity23_notes_row_title = 'Dashboard Notes';
					$infinity23_notes_row_edit_url = admin_url( 'index.php' );

				} else {

					$infinity23_notes_row_post = get_post( intval( $infinity23_notes_all_row->page_post_id ) );

					if( empty( $infinity23_notes_row_post ) ) {

						$infinity23_notes_group_key = 'deleted';
						$infinity23_notes_group_label = 'Deleted Pages/Posts';
						$infinity23_notes_row_title = 'ID ' . esc_html( $infinity23_notes_all_row->page_post_id ) . ' (no longer exists)';
						$infinity23_notes_row_edit_url = '';

					} else {

						$infinity23_notes_group_key = $infinity23_notes_row_post->post_type;
						$infinity23_notes_post_type_object = get_post_type_object( $infinity23_notes_row_post->post_type );
						$infinity23_notes_group_label = !empty( $infinity23_notes_post_type_object ) ? $infinity23_notes_post_type_object->labels->name : $infinity23_notes_row_post->post_type;
						$infinity23_notes_row_title = $infinity23_notes_row_post->post_title;
						$infinity23_notes_row_edit_url = get_edit_post_link( $infinity23_notes_row_post->ID, '' );

					} // if( empty( $infinity23_notes_row_post ) ) {

				} // if( $infinity23_notes_all_row->page_post_id === 'dashboard' ) {

				if( !isset( $infinity23_notes_all_groups[ $infinity23_notes_group_key ] ) ) {

					$infinity23_notes_all_groups[ $infinity23_notes_group_key ] = array(
						'label' => $infinity23_notes_group_label,
						'notes_count' => 0,
						'pages' => array(),
					);

				} // if( !isset( $infinity23_notes_all_groups[ $infinity23_notes_group_key ] ) ) {

				$infinity23_notes_page_key = strval( $infinity23_notes_all_row->page_post_id );

				if( !isset( $infinity23_notes_all_groups[ $infinity23_notes_group_key ]['pages'][ $infinity23_notes_page_key ] ) ) {

					$infinity23_notes_all_groups[ $infinity23_notes_group_key ]['pages'][ $infinity23_notes_page_key ] = array(
						'title' => $infinity23_notes_row_title,
						'edit_url' => $infinity23_notes_row_edit_url,
						'page_id' => $infinity23_notes_page_key,
						'texts' => array(),
					);

				} // if( !isset( $infinity23_notes_all_groups[ $infinity23_notes_group_key ]['pages'][ $infinity23_notes_page_key ] ) ) {

				$infinity23_notes_all_groups[ $infinity23_notes_group_key ]['notes_count']++;

				$infinity23_notes_all_groups[ $infinity23_notes_group_key ]['pages'][ $infinity23_notes_page_key ]['texts'][] = $infinity23_notes_all_row->note_text;

			} // foreach( $infinity23_notes_all_rows as $infinity23_notes_all_row ) {

			if( !function_exists( 'infinity23_notes_get_group_order_priority' ) ) {

				function infinity23_notes_get_group_order_priority( $group_key ) {

					if( $group_key === 'dashboard' ) { return 0; }
					if( $group_key === 'page' ) { return 1; }
					if( $group_key === 'post' ) { return 2; }
					if( $group_key === 'deleted' ) { return PHP_INT_MAX; }

					$infinity23_notes_post_type_obj = get_post_type_object( $group_key );
					$infinity23_notes_menu_position = ( !empty( $infinity23_notes_post_type_obj ) && $infinity23_notes_post_type_obj->menu_position !== null ) ? intval( $infinity23_notes_post_type_obj->menu_position ) : ( PHP_INT_MAX - 1 );

					return 3 + $infinity23_notes_menu_position;

				} // function infinity23_notes_get_group_order_priority( $group_key ) {

			} // if( !function_exists( 'infinity23_notes_get_group_order_priority' ) ) {

			uksort( $infinity23_notes_all_groups, function( $infinity23_notes_key_a, $infinity23_notes_key_b ) {

				$infinity23_notes_priority_a = infinity23_notes_get_group_order_priority( $infinity23_notes_key_a );
				$infinity23_notes_priority_b = infinity23_notes_get_group_order_priority( $infinity23_notes_key_b );

				if( $infinity23_notes_priority_a === $infinity23_notes_priority_b ) {
					return strcmp( $infinity23_notes_key_a, $infinity23_notes_key_b );
				} // if( $infinity23_notes_priority_a === $infinity23_notes_priority_b ) {

				return ( $infinity23_notes_priority_a < $infinity23_notes_priority_b ) ? -1 : 1;

			} ); // uksort( $infinity23_notes_all_groups

		} // if( !empty( $infinity23_notes_all_rows ) ) {

	} // if( $infinity23_notes_active_tab === 'all_notes' ) {

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp notes-warp">

<?php infinity23_show_message( $infinity23_success_message, $infinity23_error_message ); ?>

	<div class="page-header">
		<h1>Page Notes Settings</h1>
		<p>Control where notes appear across your site and how they behave.</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_notes_activate_action', 'infinity23_notes_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Page Notes</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<div class="infinity23-module-tabs-wrap">

		<div class="infinity23-module-tabs">
			<a href="<?php echo esc_url( $infinity23_notes_tab_settings_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_notes_active_tab === 'settings' ) { echo 'infinity23-module-tab-active'; } ?>">Settings</a>
			<?php if( $infinity23_allow_view_all_notes == 1 ) { ?>
				<a href="<?php echo esc_url( $infinity23_notes_tab_all_notes_url ); ?>" class="infinity23-module-tab-btn <?php if( $infinity23_notes_active_tab === 'all_notes' ) { echo 'infinity23-module-tab-active'; } ?>">View / Edit All Notes</a>
			<?php } // if( $infinity23_allow_view_all_notes == 1 ) { ?>
		</div><!--infinity23-module-tabs-->

		<div class="infinity23-module-tab-content <?php if( $infinity23_notes_active_tab !== 'settings' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="notes-tab-settings">

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

			<div class="section-card" data-card-default="open">

				<h2>Where To Show Notes:</h2>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_pages" name="show_on_pages" value="1" <?php if( $infinity23_show_on_pages == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_pages">Show notes on Pages</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_posts" name="show_on_posts" value="1" <?php if( $infinity23_show_on_posts == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_posts">Show notes on Posts</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_dashboard" name="show_on_dashboard" value="1" <?php if( $infinity23_show_on_dashboard == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_dashboard">Show notes on WordPress Dashboard</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_admin_table" name="show_on_admin_table" value="1" <?php if( $infinity23_show_on_admin_table == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_admin_table">Show a Notes column in the Pages/Posts list table (allow to see if notes exists for the page)</label>
				</div><!--form-line-->

				<?php infinity23_custom_post_types_section( 'show_on_custom_post_types', 'Show notes on Custom Post Types', $infinity23_show_on_custom_post_types, 'notes_custom_post_types_active_list', $infinity23_cpt_toggle_rows, 'Every custom post type on this site is listed below. Turn a type off to hide Page Notes there. By default every custom post type is on.' ); ?>

				<h3>Notes Behaviour:</h3>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="allow_multiple_notes" name="allow_multiple_notes" value="1" <?php if( $infinity23_allow_multiple_notes == 1 ) { echo 'checked'; } ?> />
					<label for="allow_multiple_notes">Allow adding multiple notes per page</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_note_dates" name="show_note_dates" value="1" <?php if( $infinity23_show_note_dates == 1 ) { echo 'checked'; } ?> />
					<label for="show_note_dates">Show the note creation date above each note</label>
				</div><!--form-line-->

				<h3>Note Box Behaviour:</h3>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="adjustable_textarea_height" name="adjustable_textarea_height" value="1" <?php if( $infinity23_adjustable_textarea_height == 1 ) { echo 'checked'; } ?> />
					<label for="adjustable_textarea_height">Auto-expand note box height as you type (Pages, Posts & Custom Post Types)</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							When enabled, a note box on a page, post or custom post type grows taller to fit its content as you type.
							<br /><br />
							When disabled, every note box there keeps a fixed height and scrolls internally once the content is taller than the box.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="dashboard_adjustable_textarea_height" name="dashboard_adjustable_textarea_height" value="1" <?php if( $infinity23_dashboard_adjustable_textarea_height == 1 ) { echo 'checked'; } ?> />
					<label for="dashboard_adjustable_textarea_height">Auto-expand note box height as you type (WordPress Dashboard)</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							When enabled, a note box on the WordPress Dashboard widget grows taller to fit its content as you type.
							<br /><br />
							When disabled, every note box there keeps a fixed height and scrolls internally once the content is taller than the box.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

			</div><!--section-card-->

			<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="open">

				<h2>Edit All Notes Page Settings: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

				<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="allow_view_all_notes" name="allow_view_all_notes" value="1" <?php if( $infinity23_allow_view_all_notes == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
					<label for="allow_view_all_notes">Allow viewing every saved note on one page</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							When enabled, an "All Notes" tab appears at the top of this page, listing every note saved across the site, grouped by page type.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="infinity23-toggle-fields" data-toggle-input="allow_view_all_notes">

					<h3>Editing Options:</h3>

					<div class="form-line checkbox-line">
						<input type="checkbox" id="all_notes_allow_edit" name="all_notes_allow_edit" value="1" <?php if( $infinity23_all_notes_allow_edit == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
						<label for="all_notes_allow_edit">Show an Edit button on the View / Edit All Notes page</label>
					</div><!--form-line-->

					<div class="form-line checkbox-line">
						<input type="checkbox" id="all_notes_allow_remove" name="all_notes_allow_remove" value="1" <?php if( $infinity23_all_notes_allow_remove == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
						<label for="all_notes_allow_remove">Show a Remove button on the View / Edit All Notes page</label>
					</div><!--form-line-->

					<h3>All Notes Table Direction:</h3>

					<div class="form-line">
						<label for="all_notes_table_direction">Table Direction: </label>
						<select id="all_notes_table_direction" name="all_notes_table_direction" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?>>
							<option value="ltr" <?php if( $infinity23_all_notes_table_direction === 'ltr' ) { echo 'selected'; } ?>>LTR (Left To Right)</option>
							<option value="rtl" <?php if( $infinity23_all_notes_table_direction === 'rtl' ) { echo 'selected'; } ?>>RTL (Right To Left)</option>
						</select>

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								Controls the text direction of the table on the "All Notes" tab.
								<br /><br />
								Defaults to your site's own language direction the first time this is saved.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

				</div><!--infinity23-toggle-fields-->

			</div><!--section-card-->

			<?php infinity23_menu_visibility_field( 'notes_hide_menu', $infinity23_notes_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_notes_settings_action', 'infinity23_notes_nonce' ); ?>

			<input type="submit" name="submit_notes" class="submit-btn" value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_notes_reset_action', 'infinity23_notes_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn" data-confirm="Are you sure you want to reset all Page Notes settings?">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

		</div><!--infinity23-module-tab-content-->

		<div class="infinity23-module-tab-content <?php if( $infinity23_notes_active_tab !== 'all_notes' ) { echo 'infinity23-module-tab-hidden'; } ?>" id="notes-tab-all-notes">

			<div class="section-card" data-card-default="open">

				<h2>All Notes:</h2>

				<p>Every note saved across the site, grouped by page type. Click a title to open that page and edit its note.</p>

				<?php global $infinity23_notes_show_note_dates_global; $infinity23_notes_show_note_dates_global = $infinity23_show_note_dates; ?>

				<?php wp_nonce_field( 'infinity23_notes_all_remove_page_action', 'infinity23_notes_all_remove_page_nonce' ); ?>

				<?php if( empty( $infinity23_notes_all_groups ) ) { ?>

					<p>No notes were found.</p>

				<?php } else { ?>

					<div class="infinity23-notes-all-table-direction-wrap" dir="<?php echo esc_attr( $infinity23_all_notes_table_direction ); ?>">

					<?php foreach( $infinity23_notes_all_groups as $infinity23_notes_group ) { ?>

						<div class="infinity23-notes-all-group">

						<h3><?php echo esc_html( $infinity23_notes_group['label'] ); ?> (<?php echo intval( $infinity23_notes_group['notes_count'] ); ?>)</h3>

						<table class="infinity23-notes-all-table">
							<thead>
								<tr>
									<th class="notes-col-title-head">Title</th>
									<th class="notes-col-notes-head">Notes</th>
									<?php if( $infinity23_all_notes_allow_edit == 1 || $infinity23_all_notes_allow_remove == 1 ) { ?>
										<th class="notes-col-actions-head">Actions</th>
									<?php } // if( $infinity23_all_notes_allow_edit == 1 || $infinity23_all_notes_allow_remove == 1 ) { ?>
								</tr>
							</thead>
							<tbody>

								<?php foreach( $infinity23_notes_group['pages'] as $infinity23_notes_page ) { ?>

									<tr class="infinity23-notes-all-row">
										<td class="notes-col-title">
											<?php if( !empty( $infinity23_notes_page['edit_url'] ) ) { ?>
												<a target="_blank" href="<?php echo esc_url( $infinity23_notes_page['edit_url'] ); ?>"><?php echo esc_html( $infinity23_notes_page['title'] ); ?></a>
											<?php } else { ?>
												<?php echo esc_html( $infinity23_notes_page['title'] ); ?>
											<?php } // if( !empty( $infinity23_notes_page['edit_url'] ) ) { ?>
										</td>
										<td class="notes-col-preview">

											<?php if( $infinity23_all_notes_allow_edit == 1 ) { ?>

											<div class="infinity23-notes-all-view-wrap">

												<?php foreach( $infinity23_notes_page['texts'] as $infinity23_notes_single_text ) { ?>
													<div class="infinity23-notes-all-view-text"><?php echo esc_html( $infinity23_notes_single_text ); ?></div>
												<?php } // foreach( $infinity23_notes_page['texts'] as $infinity23_notes_single_text ) { ?>

											</div><!--infinity23-notes-all-view-wrap-->

											<div class="infinity23-notes-all-edit-wrap infinity23-position-hidden">

												<?php

												$infinity23_notes_forced_context_id = $infinity23_notes_page['page_id'];

												if( $infinity23_notes_page['page_id'] === 'dashboard' ) {
													$infinity23_notes_is_dashboard = true;
												} else {
													$infinity23_notes_is_dashboard = false;
													$infinity23_notes_post_object = get_post( intval( $infinity23_notes_page['page_id'] ) );
												} // if( $infinity23_notes_page['page_id'] === 'dashboard' ) {

												include( dirname(__FILE__) . "/back-end/admin-notes-output.php" );

												?>

												<button type="button" class="infinity23-notes-all-action-btn infinity23-notes-all-action-btn-danger infinity23-notes-all-cancel-edit-btn">Cancel Changes</button>

											</div><!--infinity23-notes-all-edit-wrap-->

											<?php } else { ?>

												<?php foreach( $infinity23_notes_page['texts'] as $infinity23_notes_single_text ) { ?>
													<div class="infinity23-notes-all-view-text"><?php echo esc_html( $infinity23_notes_single_text ); ?></div>
												<?php } // foreach( $infinity23_notes_page['texts'] as $infinity23_notes_single_text ) { ?>

											<?php } // if( $infinity23_all_notes_allow_edit == 1 ) { ?>

										</td>
										<?php if( $infinity23_all_notes_allow_edit == 1 || $infinity23_all_notes_allow_remove == 1 ) { ?>
											<td class="notes-col-actions">
												<?php $infinity23_notes_page_notes_count = count( $infinity23_notes_page['texts'] ); ?>
												<?php if( $infinity23_all_notes_allow_edit == 1 ) { ?>
													<button type="button" class="infinity23-notes-all-action-btn infinity23-notes-all-edit-icon"><?php echo ( $infinity23_notes_page_notes_count > 1 ) ? 'Edit ' . intval( $infinity23_notes_page_notes_count ) . ' Notes' : 'Edit Note'; ?></button>
												<?php } // if( $infinity23_all_notes_allow_edit == 1 ) { ?>
												<?php if( $infinity23_all_notes_allow_remove == 1 ) { ?>
													<button type="button" class="infinity23-notes-all-action-btn infinity23-notes-all-action-btn-danger infinity23-notes-all-remove-page-btn" data-page-id="<?php echo esc_attr( $infinity23_notes_page['page_id'] ); ?>"><?php echo ( $infinity23_notes_page_notes_count > 1 ) ? 'Remove ' . intval( $infinity23_notes_page_notes_count ) . ' Notes' : 'Remove Note'; ?></button>
												<?php } // if( $infinity23_all_notes_allow_remove == 1 ) { ?>
											</td>
										<?php } // if( $infinity23_all_notes_allow_edit == 1 || $infinity23_all_notes_allow_remove == 1 ) { ?>
									</tr>

								<?php } // foreach( $infinity23_notes_group['pages'] as $infinity23_notes_page ) { ?>

							</tbody>
						</table>

						</div><!--infinity23-notes-all-group-->

					<?php } // foreach( $infinity23_notes_all_groups as $infinity23_notes_group ) { ?>

					</div><!--infinity23-notes-all-table-direction-wrap-->

				<?php } // if( empty( $infinity23_notes_all_groups ) ) { ?>

			</div><!--section-card-->

		</div><!--infinity23-module-tab-content-->

	</div><!--infinity23-module-tabs-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
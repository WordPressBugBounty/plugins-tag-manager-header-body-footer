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
	// reset all update manager settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_update_manager_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_update_manager_reset_nonce'] ) ), 'infinity23_update_manager_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			delete_transient( 'infinity23_plugin_icons_urls_cache' );
			delete_transient( 'infinity23_theme_icons_urls_cache' );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_update_manager_reset_nonce'] ) ) {

	// ----------------------------------------
	// code toggle activation form submit
	// ----------------------------------------

	if( isset( $_POST['infinity23_update_manager_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_update_manager_activate_nonce'] ) ), 'infinity23_update_manager_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'update_manager', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "Updates Manager was activated successfully";
			} else {

				infinity23_module_deactivate_redirect( $infinity23_module_display_name );

			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_update_manager_activate_nonce'] ) ) {

	// ----------------------------------------
	// Submit the main form with data
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_update_manager_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_update_manager_nonce'] ) ), 'infinity23_update_manager_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_data_array['disable_plugin_auto_updates'] = infinity23_pro_field_sanitize( 'disable_plugin_auto_updates', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['disable_theme_auto_updates'] = infinity23_pro_field_sanitize( 'disable_theme_auto_updates', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['core_updates_mode'] = infinity23_pro_field_sanitize( 'core_updates_mode', $infinity23_existing_data_array, $infinity23_defaults );
			if( !in_array( $infinity23_data_array['core_updates_mode'], array( 'all', 'security_only', 'none' ), true ) ) {
				$infinity23_data_array['core_updates_mode'] = 'all';
			} // if( !in_array( $infinity23_data_array['core_updates_mode'], array( 'all', 'security_only', 'none' ), true ) ) {
			$infinity23_data_array['disable_core_auto_updates'] = intval( infinity23_get_data( 'disable_core_auto_updates', $infinity23_existing_data_array, $infinity23_defaults ) );
			$infinity23_data_array['hide_core_update_notice'] = infinity23_pro_field_sanitize( 'hide_core_update_notice', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['force_block_updates_mode'] = infinity23_pro_field_sanitize( 'force_block_updates_mode', $infinity23_existing_data_array, $infinity23_defaults );

			if( !in_array( $infinity23_data_array['force_block_updates_mode'], array( 'block', 'ignore' ), true ) ) {
				$infinity23_data_array['force_block_updates_mode'] = 'block';
			} // if( !in_array( $infinity23_data_array['force_block_updates_mode'], array( 'block', 'ignore' ), true ) ) {

			$infinity23_data_array['force_block_updates_notice'] = infinity23_pro_field_sanitize( 'force_block_updates_notice', $infinity23_existing_data_array, $infinity23_defaults );

			if( !function_exists( 'get_plugins' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			} // if( !function_exists( 'get_plugins' ) ) {

			$infinity23_active_plugins_posted = array();

			if( isset( $_POST['force_block_plugin_updates'] ) && is_array( $_POST['force_block_plugin_updates'] ) ) {

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is sanitize_text_field(wp_unslash())'d inside the loop
				foreach( $_POST['force_block_plugin_updates'] as $infinity23_single_plugin_slug ) {

					if( is_array( $infinity23_single_plugin_slug ) ) { continue; }

					$infinity23_active_plugins_posted[] = sanitize_text_field( wp_unslash( $infinity23_single_plugin_slug ) );

				} // foreach( $_POST['force_block_plugin_updates'] as $infinity23_single_plugin_slug ) {

			} // if( isset( $_POST['force_block_plugin_updates'] ) && is_array( $_POST['force_block_plugin_updates'] ) ) {

			// the list of rows that were actually rendered, posted back with the
			// form. Subtracting from every installed plugin instead would block
			// anything installed between the page render and this save
			$infinity23_rendered_plugin_files = array();

			if( isset( $_POST['force_block_plugin_updates_rendered'] ) && !is_array( $_POST['force_block_plugin_updates_rendered'] ) ) {
				$infinity23_rendered_plugin_files = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_POST['force_block_plugin_updates_rendered'] ) ) ) ) );
			} // if( isset( $_POST['force_block_plugin_updates_rendered'] ) && !is_array( $_POST['force_block_plugin_updates_rendered'] ) ) {

			$infinity23_previously_blocked_plugins = array_filter( array_map( 'trim', explode( ',', strval( infinity23_get_data( 'force_block_plugin_updates', $infinity23_existing_data_array, $infinity23_defaults ) ) ) ) );
			$infinity23_blocked_plugins_to_save = array_unique( array_merge( array_diff( $infinity23_rendered_plugin_files, $infinity23_active_plugins_posted ), array_diff( $infinity23_previously_blocked_plugins, $infinity23_rendered_plugin_files ) ) );

			if( infinity23_is_pro() ) {
				$infinity23_data_array['force_block_plugin_updates'] = implode( ',', $infinity23_blocked_plugins_to_save );
			} else {
				$infinity23_data_array['force_block_plugin_updates'] = infinity23_get_data( 'force_block_plugin_updates', $infinity23_existing_data_array, $infinity23_defaults );
			} // if( infinity23_is_pro() ) {

			$infinity23_active_themes_posted = array();

			if( isset( $_POST['force_block_theme_updates'] ) && is_array( $_POST['force_block_theme_updates'] ) ) {

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is sanitize_text_field(wp_unslash())'d inside the loop
				foreach( $_POST['force_block_theme_updates'] as $infinity23_single_theme_slug ) {

					if( is_array( $infinity23_single_theme_slug ) ) { continue; }

					$infinity23_active_themes_posted[] = sanitize_text_field( wp_unslash( $infinity23_single_theme_slug ) );

				} // foreach( $_POST['force_block_theme_updates'] as $infinity23_single_theme_slug ) {

			} // if( isset( $_POST['force_block_theme_updates'] ) && is_array( $_POST['force_block_theme_updates'] ) ) {

			$infinity23_rendered_theme_slugs = array();

			if( isset( $_POST['force_block_theme_updates_rendered'] ) && !is_array( $_POST['force_block_theme_updates_rendered'] ) ) {
				$infinity23_rendered_theme_slugs = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_POST['force_block_theme_updates_rendered'] ) ) ) ) );
			} // if( isset( $_POST['force_block_theme_updates_rendered'] ) && !is_array( $_POST['force_block_theme_updates_rendered'] ) ) {

			$infinity23_previously_blocked_themes = array_filter( array_map( 'trim', explode( ',', strval( infinity23_get_data( 'force_block_theme_updates', $infinity23_existing_data_array, $infinity23_defaults ) ) ) ) );
			$infinity23_blocked_themes_to_save = array_unique( array_merge( array_diff( $infinity23_rendered_theme_slugs, $infinity23_active_themes_posted ), array_diff( $infinity23_previously_blocked_themes, $infinity23_rendered_theme_slugs ) ) );

			if( infinity23_is_pro() ) {
				$infinity23_data_array['force_block_theme_updates'] = implode( ',', $infinity23_blocked_themes_to_save );
			} else {
				$infinity23_data_array['force_block_theme_updates'] = infinity23_get_data( 'force_block_theme_updates', $infinity23_existing_data_array, $infinity23_defaults );
			} // if( infinity23_is_pro() ) {

			$infinity23_data_array['update_manager_hide_menu'] = infinity23_pro_field_sanitize( 'update_manager_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-update-manager', 'saved' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce( $_POST['infinity23_update_manager_nonce'], 'infinity23_update_manager_action' ) ) {

	} // elseif( isset( $_POST['infinity23_update_manager_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_disable_plugin_auto_updates = intval( infinity23_pro_get_data( 'disable_plugin_auto_updates', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_disable_theme_auto_updates = intval( infinity23_pro_get_data( 'disable_theme_auto_updates', $infinity23_data_array, $infinity23_defaults ) );
	// isset() first - infinity23_pro_get_data() returns the 'all' default when the
	// key is missing, which passes the check below and would silently drop the old
	// checkbox choice on every site that saved before this became a select
	$infinity23_core_updates_mode = isset( $infinity23_data_array['core_updates_mode'] ) ? infinity23_pro_get_data( 'core_updates_mode', $infinity23_data_array, $infinity23_defaults ) : '';
	if( !in_array( $infinity23_core_updates_mode, array( 'all', 'security_only', 'none' ), true ) ) {
		// nothing saved yet in the new field - fall back to the old checkbox
		$infinity23_core_updates_mode = ( intval( infinity23_pro_get_data( 'disable_core_auto_updates', $infinity23_data_array, $infinity23_defaults ) ) === 1 ) ? 'none' : 'all';
	} // if( !in_array( $infinity23_core_updates_mode, array( 'all', 'security_only', 'none' ), true ) ) {
	$infinity23_hide_core_update_notice = intval( infinity23_pro_get_data( 'hide_core_update_notice', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_force_block_updates_mode = infinity23_pro_get_data( 'force_block_updates_mode', $infinity23_data_array, $infinity23_defaults );

	if( !in_array( $infinity23_force_block_updates_mode, array( 'block', 'ignore' ), true ) ) {
		$infinity23_force_block_updates_mode = 'block';
	} // if( !in_array( $infinity23_force_block_updates_mode, array( 'block', 'ignore' ), true ) ) {

	$infinity23_force_block_updates_notice = intval( infinity23_pro_get_data( 'force_block_updates_notice', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_force_block_plugin_updates = infinity23_pro_get_data( 'force_block_plugin_updates', $infinity23_data_array, $infinity23_defaults );
	$infinity23_force_block_theme_updates = infinity23_pro_get_data( 'force_block_theme_updates', $infinity23_data_array, $infinity23_defaults );

	$infinity23_force_block_plugin_updates_array = array();

	if( !empty( $infinity23_force_block_plugin_updates ) ) {

		foreach( explode( ',', $infinity23_force_block_plugin_updates ) as $infinity23_single_blocked_plugin ) {

			$infinity23_single_blocked_plugin = trim( $infinity23_single_blocked_plugin );

			if( !empty( $infinity23_single_blocked_plugin ) ) {
				$infinity23_force_block_plugin_updates_array[] = $infinity23_single_blocked_plugin;
			} // if( !empty( $infinity23_single_blocked_plugin ) ) {

		} // foreach( explode( ',', $infinity23_force_block_plugin_updates ) as $infinity23_single_blocked_plugin ) {

	} // if( !empty( $infinity23_force_block_plugin_updates ) ) {

	$infinity23_force_block_theme_updates_array = array();

	if( !empty( $infinity23_force_block_theme_updates ) ) {

		foreach( explode( ',', $infinity23_force_block_theme_updates ) as $infinity23_single_blocked_theme ) {

			$infinity23_single_blocked_theme = trim( $infinity23_single_blocked_theme );

			if( !empty( $infinity23_single_blocked_theme ) ) {
				$infinity23_force_block_theme_updates_array[] = $infinity23_single_blocked_theme;
			} // if( !empty( $infinity23_single_blocked_theme ) ) {

		} // foreach( explode( ',', $infinity23_force_block_theme_updates ) as $infinity23_single_blocked_theme ) {

	} // if( !empty( $infinity23_force_block_theme_updates ) ) {

	$infinity23_update_manager_hide_menu = intval( infinity23_pro_get_data( 'update_manager_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

	// =================================================================================================================
	// Build the force-block plugins table
	// =================================================================================================================

	if( !function_exists( 'infinity23_update_manager_build_plugins_block_table' ) ) {

		function infinity23_update_manager_build_plugins_block_table( $blocked_plugins_array ) {

			if( !function_exists( 'get_plugins' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			} // if( !function_exists( 'get_plugins' ) ) {

			$icons_cache = infinity23_get_plugin_icons_urls_cache();
			$rows = array();

			$all_plugins = get_plugins();

			foreach( $all_plugins as $plugin_file => $plugin_data ) {

				$is_blocked = in_array( $plugin_file, $blocked_plugins_array, true );

				$icon_slug = ( strpos( $plugin_file, '/' ) !== false ) ? dirname( $plugin_file ) : basename( $plugin_file, '.php' );
				$icon_url = infinity23_get_plugin_icon_url( $icon_slug, $icons_cache );

				$icon_html = !empty( $icon_url ) ? '<img src="' . esc_url( $icon_url ) . '" alt="" />' : '';

				$rows[] = array(
					'value' => $plugin_file,
					'label' => $plugin_data['Name'],
					'checked' => !$is_blocked,
					'icon_html' => $icon_html,
					'extra' => $plugin_data['Version'],
				);

			} // foreach( $all_plugins as $plugin_file => $plugin_data ) {

			$rows_data = infinity23_get_toggle_table_rows_data( 'force_block_plugin_updates', $rows, !infinity23_is_pro() );

			$table = '';
			$table .= '<table class="infinity23-force-block-table">';
				$table .= '<thead>';
					$table .= '<tr>';
						$table .= '<th class="fb-col-icon">Icon</th>';
						$table .= '<th class="fb-col-block">Active Updates<br /><small>(Uncheck plugin to stop update)</small></th>';
						$table .= '<th class="fb-col-name">Name</th>';
						$table .= '<th class="fb-col-version">Current Plugin Version</th>';
					$table .= '</tr>';
				$table .= '</thead>';
				$table .= '<tbody>';

			foreach( $rows_data as $row_data ) {

				$table .= '<tr class="infinity23-fb-row ' . esc_attr( $row_data['row_class'] ) . '">';
					$table .= '<td class="fb-col-icon">' . $row_data['icon_html'] . '</td>';
					$table .= '<td class="fb-col-block">' . $row_data['checkbox_html'] . '</td>';
					$table .= '<td class="fb-col-name">' . esc_html( $row_data['label'] ) . '</td>';
					$table .= '<td class="fb-col-version">' . esc_html( $row_data['extra'] ) . '</td>';
				$table .= '</tr>';

			} // foreach( $rows_data as $row_data ) {

				$table .= '</tbody>';
			$table .= '</table>';

			infinity23_save_plugin_icons_urls_cache( $icons_cache );

			return $table;

		} // function infinity23_update_manager_build_plugins_block_table( $blocked_plugins_array ) {

	} // if( !function_exists( 'infinity23_update_manager_build_plugins_block_table' ) ) {

	// =================================================================================================================
	// Build the force-block themes table
	// =================================================================================================================

	if( !function_exists( 'infinity23_update_manager_build_themes_block_table' ) ) {

		function infinity23_update_manager_build_themes_block_table( $blocked_themes_array ) {

			$icons_cache = infinity23_get_theme_icons_urls_cache();
			$rows = array();

			$all_themes = wp_get_themes();

			foreach( $all_themes as $theme_slug => $theme_obj ) {

				$is_blocked = in_array( $theme_slug, $blocked_themes_array, true );
				$icon_url = infinity23_get_theme_icon_url( $theme_slug, $icons_cache );
				$icon_html = !empty( $icon_url ) ? '<img src="' . esc_url( $icon_url ) . '" alt="" />' : '';

				$rows[] = array(
					'value' => $theme_slug,
					'label' => $theme_obj->get('Name'),
					'checked' => !$is_blocked,
					'icon_html' => $icon_html,
					'extra' => $theme_obj->get('Version'),
				);

			} // foreach( $all_themes as $theme_slug => $theme_obj ) {

			$rows_data = infinity23_get_toggle_table_rows_data( 'force_block_theme_updates', $rows, !infinity23_is_pro() );

			$table = '';
			$table .= '<table class="infinity23-force-block-table">';
				$table .= '<thead>';
					$table .= '<tr>';
						$table .= '<th class="fb-col-icon">Icon</th>';
						$table .= '<th class="fb-col-block">Active Updates<br /><small>(Uncheck theme to stop update)</small></th>';
						$table .= '<th class="fb-col-name">Name</th>';
						$table .= '<th class="fb-col-version">Current Theme Version</th>';
					$table .= '</tr>';
				$table .= '</thead>';
				$table .= '<tbody>';

			foreach( $rows_data as $row_data ) {

				$table .= '<tr class="infinity23-fb-row ' . esc_attr( $row_data['row_class'] ) . '">';
					$table .= '<td class="fb-col-icon">' . $row_data['icon_html'] . '</td>';
					$table .= '<td class="fb-col-block">' . $row_data['checkbox_html'] . '</td>';
					$table .= '<td class="fb-col-name">' . esc_html( $row_data['label'] ) . '</td>';
					$table .= '<td class="fb-col-version">' . esc_html( $row_data['extra'] ) . '</td>';
				$table .= '</tr>';

			} // foreach( $rows_data as $row_data ) {

				$table .= '</tbody>';
			$table .= '</table>';

			infinity23_save_theme_icons_urls_cache( $icons_cache );

			return $table;

		} // function infinity23_update_manager_build_themes_block_table( $blocked_themes_array ) {

	} // if( !function_exists( 'infinity23_update_manager_build_themes_block_table' ) ) {

	$infinity23_update_manager_plugins_block_table = infinity23_update_manager_build_plugins_block_table( $infinity23_force_block_plugin_updates_array );
	$infinity23_update_manager_themes_block_table = infinity23_update_manager_build_themes_block_table( $infinity23_force_block_theme_updates_array );

	// =================================================================================================================
	// Check if WP_AUTO_UPDATE_CORE is disabled directly in wp-config.php
	// =================================================================================================================

	if( !function_exists( 'infinity23_update_manager_wp_config_core_updates_disabled' ) ) {

		function infinity23_update_manager_wp_config_core_updates_disabled() {

			$wp_config_path = ABSPATH . 'wp-config.php';

			if( !file_exists( $wp_config_path ) ) {
				return false;
			} // if( !file_exists( $wp_config_path ) ) {

			$wp_config_content = file_get_contents( $wp_config_path );

			if( strstr( $wp_config_content, "WP_AUTO_UPDATE_CORE', false" ) ||
				strstr( $wp_config_content, "WP_AUTO_UPDATE_CORE',false" ) ||
				strstr( $wp_config_content, "WP_AUTO_UPDATE_CORE','false'" ) ||
				strstr( $wp_config_content, "WP_AUTO_UPDATE_CORE', 'false'" )
			) {
				return true;
			} // if( strstr(

			return false;

		} // function infinity23_update_manager_wp_config_core_updates_disabled() {

	} // if( !function_exists( 'infinity23_update_manager_wp_config_core_updates_disabled' ) ) {

	$infinity23_update_manager_wp_config_core_disabled = infinity23_update_manager_wp_config_core_updates_disabled();

?>

<?php
// =================================================================================================================
// Output the plugin form data to page
// =================================================================================================================
?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp update-manager-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Updates Manager Settings</h1>
		<p>Below you will be able to control automatic updates and block updates for specific plugins and themes:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_update_manager_activate_action', 'infinity23_update_manager_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Updates Manager</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

			<div class="section-card" data-card-default="open">

				<h2>Update Behavior:</h2>

				<h3>Disable Updates To Plugins And Themes:</h3>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="disable_plugin_auto_updates" name="disable_plugin_auto_updates" value="1" <?php if( $infinity23_disable_plugin_auto_updates == 1 ) { echo 'checked'; } ?> />
					<label for="disable_plugin_auto_updates">Disable automatic updates of plugins</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="disable_theme_auto_updates" name="disable_theme_auto_updates" value="1" <?php if( $infinity23_disable_theme_auto_updates == 1 ) { echo 'checked'; } ?> />
					<label for="disable_theme_auto_updates">Disable automatic updates of themes</label>
				</div><!--form-line-->

				<h3>WordPress Core Updates:</h3>

				<div class="form-line">
					<label for="core_updates_mode">Core Wordpress Updates Settings: </label>
					<select id="core_updates_mode" name="core_updates_mode">
						<option value="all" <?php if( $infinity23_core_updates_mode === 'all' ) { echo 'selected'; } ?>>Allow all wordpress updates</option>
						<option value="security_only" <?php if( $infinity23_core_updates_mode === 'security_only' ) { echo 'selected'; } ?>>Block major updates but allow security releases updates</option>
						<option value="none" <?php if( $infinity23_core_updates_mode === 'none' ) { echo 'selected'; } ?>>Block all wordpress core updates</option>
					</select>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							<strong>Allow all wordpress updates</strong> - nothing is blocked, WordPress decides as it normally would.
							<br /><br />
							<strong>Block major updates but allow security releases updates</strong> - minor releases like 6.8.1 keep installing automatically, major version jumps like 6.8 to 6.9 are blocked. This is the safest option if you only want to avoid surprise upgrades.
							<br /><br />
							<strong>Block all wordpress core updates</strong> - nothing installs automatically, including security patches. Only pick this if you install core updates manually yourself.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<?php if( $infinity23_update_manager_wp_config_core_disabled ) { ?>
					<p class="infinity23-notice-text">Core updates are already disabled via WP_AUTO_UPDATE_CORE in wp-config.php - this overrides the setting above.

						<span class="backtest-field-help-icon">
							<span class="data-help-content">
								WordPress reads the WP_AUTO_UPDATE_CORE constant from wp-config.php before this plugin's own settings are applied.
								<br /><br />
								When it's set to false there, WordPress itself blocks all core auto-updates at the source - so changing the setting above will not bring auto-updates back.
								<br /><br />
								To restore control to this plugin's setting, remove or change the WP_AUTO_UPDATE_CORE line in wp-config.php.
							</span>
							?
						</span><!--backtest-field-help-icon-->
					</p>
				<?php } // if( $infinity23_update_manager_wp_config_core_disabled ) { ?>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="hide_core_update_notice" name="hide_core_update_notice" value="1" <?php if( $infinity23_hide_core_update_notice == 1 ) { echo 'checked'; } ?> />
					<label for="hide_core_update_notice">Hide the notification when a WordPress core update is available</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							WordPress shows its own notice across the admin area whenever a new core version is available, with a link to update now.
							<br /><br />
							Checking this removes that native notice completely - it does not affect the setting above, so core updates can still install automatically even while this notice stays hidden.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

			</div><!--section-card-->

			<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="open">

				<h2>Force Block Individual Plugin & Theme from updates: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

				<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

				<p>Toggle a specific plugin or theme off to block its updates, regardless of the Update Behavior settings above.</p>

				<h3>Force Block Status:</h3>

				<div class="form-line">
					<label for="force_block_updates_mode">Force Block Status: </label>
					<select id="force_block_updates_mode" name="force_block_updates_mode" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?>>
						<option value="block" <?php if( $infinity23_force_block_updates_mode === 'block' ) { echo 'selected'; } ?>>Enforce The Blocks Below</option>
						<option value="ignore" <?php if( $infinity23_force_block_updates_mode === 'ignore' ) { echo 'selected'; } ?>>Ignore All Blocks (Allow All Updates Temporarily)</option>
					</select>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Your saved blocks below stay exactly as you set them either way - this only controls whether they're currently enforced.
							<br /><br />
							Switch to <strong>Ignore All Blocks</strong> to temporarily allow updates for every plugin/theme you've blocked below - useful when you want to update one of them just once, without losing your saved selections.
							<br /><br />
							Switch back to <strong>Enforce The Blocks Below</strong> to restore blocking exactly as you had it.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="force_block_updates_notice" name="force_block_updates_notice" value="1" <?php if( $infinity23_force_block_updates_notice == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
					<label for="force_block_updates_notice">Show an admin notice while Ignore All Blocks is active</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							When checked, a notice is shown at the top of every admin page for as long as <strong>Ignore All Blocks</strong> is selected above.
							<br /><br />
							This works as a reminder that plugin/theme update blocking is temporarily off, with a link back to this page so it's never forgotten.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="infinity23-fb-tables-wrap <?php if( $infinity23_force_block_updates_mode === 'ignore' ) { echo 'infinity23-fb-tables-dimmed'; } ?>">

					<input type="hidden" name="force_block_plugin_updates_rendered" value="<?php echo esc_attr( implode( ',', array_keys( get_plugins() ) ) ); ?>" />
					<input type="hidden" name="force_block_theme_updates_rendered" value="<?php echo esc_attr( implode( ',', array_keys( wp_get_themes() ) ) ); ?>" />

					<h3 class="infinity23-fb-centered-title">Plugins that you want to block:</h3>

					<div class="infinity23-fb-table-wrap">
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
						<?php echo $infinity23_update_manager_plugins_block_table; ?>
					</div><!--infinity23-fb-table-wrap-->

					<h3 class="infinity23-fb-centered-title">Themes that you want to block:</h3>

					<div class="infinity23-fb-table-wrap">
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
						<?php echo $infinity23_update_manager_themes_block_table; ?>
					</div><!--infinity23-fb-table-wrap-->

				</div><!--infinity23-fb-tables-wrap-->

			</div><!--section-card-->

			<?php infinity23_menu_visibility_field( 'update_manager_hide_menu', $infinity23_update_manager_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_update_manager_action', 'infinity23_update_manager_nonce' ); ?>

			<input type="submit" name="submit_update_manager" class='submit-btn' value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_update_manager_reset_action', 'infinity23_update_manager_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
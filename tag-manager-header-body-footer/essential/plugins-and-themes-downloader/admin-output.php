<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// =================================================================================================================
	// Initial plugin load and getting default values
	// =================================================================================================================

	include( dirname(__FILE__) . "/../../include/settings.php" );
	include( dirname(__FILE__) . "/internal-settings.php" );
	infinity23_load_essential_files();

	$infinity23_success_message = '';
	$infinity23_error_message = '';

	if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {
		$infinity23_success_message = "The data was updated successfully";
	} // if( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {

	include( dirname(__FILE__) . "/default-values.php" );

	// =================================================================================================================
	// Update the data if the form was submitted
	// =================================================================================================================

	if( isset( $_POST['infinity23_ptd_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_ptd_reset_nonce'] ) ), 'infinity23_ptd_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			delete_transient( 'infinity23_plugin_icons_urls_cache' );
			delete_transient( 'infinity23_theme_icons_urls_cache' );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} elseif( isset( $_POST['infinity23_ptd_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_ptd_activate_nonce'] ) ), 'infinity23_ptd_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'plugins_and_themes_downloader', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "Plugins/Themes Downloader was activated successfully";
			} else {
				infinity23_ptd_remove_temp_dir();
				infinity23_module_deactivate_redirect( $infinity23_module_display_name );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} elseif( isset( $_POST['infinity23_ptd_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_ptd_nonce'] ) ), 'infinity23_ptd_settings_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_data_array['enable_plugins_download'] = infinity23_pro_field_sanitize( 'enable_plugins_download', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['enable_themes_download'] = infinity23_pro_field_sanitize( 'enable_themes_download', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['include_version_in_filename'] = infinity23_pro_field_sanitize( 'include_version_in_filename', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['zip_time_limit'] = infinity23_pro_field_sanitize( 'zip_time_limit', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['zip_storage_location'] = infinity23_pro_field_sanitize( 'zip_storage_location', $infinity23_existing_data_array, $infinity23_defaults );

			if( !in_array( $infinity23_data_array['zip_storage_location'], array( 'temp', 'uploads' ), true ) ) {
				$infinity23_data_array['zip_storage_location'] = 'temp';
			} // if( !in_array( $infinity23_data_array['zip_storage_location'], array( 'temp', 'uploads' ), true ) ) {

			$infinity23_data_array['plugins_and_themes_downloader_hide_menu'] = infinity23_pro_field_sanitize( 'plugins_and_themes_downloader_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );

			// the new location is live from here on, so clear out whichever
			// folder is no longer in use - otherwise zip files generated before
			// the switch would sit in the old folder until the next cleanup pass
			infinity23_ptd_purge_unused_storage_dir( infinity23_ptd_get_temp_dir( $infinity23_data_array['zip_storage_location'] ) );
			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-plugins-and-themes-downloader', 'saved' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_ptd_reset_nonce'] ) ) {

	// =================================================================================================================
	// Load saved values
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_enable_plugins_download = intval( infinity23_pro_get_data( 'enable_plugins_download', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_enable_themes_download = intval( infinity23_pro_get_data( 'enable_themes_download', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_zip_time_limit = intval( infinity23_pro_get_data( 'zip_time_limit', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_include_version_in_filename = intval( infinity23_pro_get_data( 'include_version_in_filename', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_zip_storage_location = infinity23_pro_get_data( 'zip_storage_location', $infinity23_data_array, $infinity23_defaults );
	if( !in_array( $infinity23_zip_storage_location, array( 'temp', 'uploads' ), true ) ) {
		$infinity23_zip_storage_location = 'temp';
	} // if( !in_array( $infinity23_zip_storage_location, array( 'temp', 'uploads' ), true ) ) {

	// resolved with the plain path helpers and not infinity23_ptd_get_temp_dir(),
	// so simply viewing this page never creates the folder - it is created on
	// the first real download instead
	$infinity23_zip_storage_path = ( $infinity23_zip_storage_location === 'temp' ) ? infinity23_ptd_get_system_temp_dir() : infinity23_ptd_get_uploads_dir();
	$infinity23_zip_storage_files = is_dir( $infinity23_zip_storage_path ) ? glob( $infinity23_zip_storage_path . '/*.zip' ) : array();
	$infinity23_zip_storage_count = is_array( $infinity23_zip_storage_files ) ? count( $infinity23_zip_storage_files ) : 0;

	$infinity23_plugins_and_themes_downloader_hide_menu = intval( infinity23_pro_get_data( 'plugins_and_themes_downloader_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

	// =================================================================================================================
	// Build the plugins list table
	// =================================================================================================================

	if( !function_exists( 'infinity23_ptd_build_plugins_table' ) ) {

		function infinity23_ptd_build_plugins_table( $infinity23_enable_plugins_download ) {

			if( !function_exists( 'get_plugins' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			} // if( !function_exists( 'get_plugins' ) ) {

			$icons_cache = infinity23_get_plugin_icons_urls_cache();

			$table = '';
			$table .= '<table class="infinity23-ptd-table">';
				$table .= '<thead>';
					$table .= '<tr>';
						$table .= '<th class="ptd-col-icon">Icon</th>';
						$table .= '<th class="ptd-col-name">Plugin Name</th>';
						$table .= '<th class="ptd-col-version">Version</th>';
						$table .= '<th class="ptd-col-download">Download</th>';
					$table .= '</tr>';
				$table .= '</thead>';
				$table .= '<tbody>';

			$all_plugins = get_plugins();

			foreach( $all_plugins as $plugin_file => $plugin_data ) {

				$plugin_parts = explode( '/', $plugin_file );
				$plugin_slug = isset( $plugin_parts[0] ) ? $plugin_parts[0] : '';

				if( empty( $plugin_slug ) ) { continue; }

				$plugin_name = esc_html( $plugin_data['Name'] );
				$plugin_version = esc_html( $plugin_data['Version'] );

				$plugin_icon_url = infinity23_get_plugin_icon_url( $plugin_slug, $icons_cache );

				if( !empty( $plugin_icon_url ) ) {
					$plugin_icon_html = '<img src="' . esc_url( $plugin_icon_url ) . '" alt="" />';
				} else {
					$plugin_icon_html = '<span class="infinity23-icon-placeholder"></span>';
				} // if( !empty( $plugin_icon_url ) ) {

				$table .= '<tr>';
					$table .= '<td class="ptd-col-icon">' . $plugin_icon_html . '</td>';
					$table .= '<td class="ptd-col-name">' . $plugin_name . '</td>';
					$table .= '<td class="ptd-col-version">' . $plugin_version . '</td>';

				if( $infinity23_enable_plugins_download == 1 ) {

					$plugin_download_link = add_query_arg(
						array(
							'action' => 'infinity23_ptd_download',
							'infinity23_ptd_type' => 'plugin',
							'infinity23_ptd_slug' => $plugin_slug,
							'infinity23_ptd_nonce' => wp_create_nonce( 'infinity23_ptd_download_list_plugin_' . $plugin_slug ),
						),
						admin_url( 'admin-post.php' )
					);

					$table .= '<td class="ptd-col-download"><a class="infinity23-ptd-download-btn" href="' . esc_url( $plugin_download_link ) . '">Download</a></td>';

				} else {
					$table .= '<td class="ptd-col-download">-</td>';
				} // if( $infinity23_enable_plugins_download == 1 ) {

				$table .= '</tr>';

			} // foreach( $all_plugins as $plugin_file => $plugin_data ) {

				$table .= '</tbody>';
			$table .= '</table>';

			infinity23_save_plugin_icons_urls_cache( $icons_cache );

			return $table;

		} // function infinity23_ptd_build_plugins_table( $infinity23_enable_plugins_download ) {

	} // if( !function_exists( 'infinity23_ptd_build_plugins_table' ) ) {

	// =================================================================================================================
	// Build the themes list table
	// =================================================================================================================

	if( !function_exists( 'infinity23_ptd_build_themes_table' ) ) {

		function infinity23_ptd_build_themes_table( $infinity23_enable_themes_download ) {

			$icons_cache = infinity23_get_theme_icons_urls_cache();

			$table = '';
			$table .= '<table class="infinity23-ptd-table">';
				$table .= '<thead>';
					$table .= '<tr>';
						$table .= '<th class="ptd-col-icon">Icon</th>';
						$table .= '<th class="ptd-col-name">Theme Name</th>';
						$table .= '<th class="ptd-col-version">Version</th>';
						$table .= '<th class="ptd-col-download">Download</th>';
					$table .= '</tr>';
				$table .= '</thead>';
				$table .= '<tbody>';

			$all_themes = wp_get_themes();

			foreach( $all_themes as $theme_slug => $theme_obj ) {

				$theme_name = esc_html( $theme_obj->get('Name') );
				$theme_version = esc_html( $theme_obj->get('Version') );

				$theme_icon_url = infinity23_get_theme_icon_url( $theme_slug, $icons_cache );

				if( !empty( $theme_icon_url ) ) {
					$theme_icon_html = '<img src="' . esc_url( $theme_icon_url ) . '" alt="" />';
				} else {
					$theme_icon_html = '<span class="infinity23-icon-placeholder"></span>';
				} // if( !empty( $theme_icon_url ) ) {

				$table .= '<tr>';
					$table .= '<td class="ptd-col-icon">' . $theme_icon_html . '</td>';
					$table .= '<td class="ptd-col-name">' . $theme_name . '</td>';
					$table .= '<td class="ptd-col-version">' . $theme_version . '</td>';

				if( $infinity23_enable_themes_download == 1 ) {

					$theme_download_link = add_query_arg(
						array(
							'action' => 'infinity23_ptd_download',
							'infinity23_ptd_type' => 'theme',
							'infinity23_ptd_slug' => $theme_slug,
							'infinity23_ptd_nonce' => wp_create_nonce( 'infinity23_ptd_download_list_theme_' . $theme_slug ),
						),
						admin_url( 'admin-post.php' )
					);

					$table .= '<td class="ptd-col-download"><a class="infinity23-ptd-download-btn" href="' . esc_url( $theme_download_link ) . '">Download</a></td>';

				} else {
					$table .= '<td class="ptd-col-download">-</td>';
				} // if( $infinity23_enable_themes_download == 1 ) {

				$table .= '</tr>';

			} // foreach( $all_themes as $theme_slug => $theme_obj ) {

				$table .= '</tbody>';
			$table .= '</table>';

			infinity23_save_theme_icons_urls_cache( $icons_cache );

			return $table;

		} // function infinity23_ptd_build_themes_table( $infinity23_enable_themes_download ) {

	} // if( !function_exists( 'infinity23_ptd_build_themes_table' ) ) {

	$infinity23_ptd_plugins_table = infinity23_ptd_build_plugins_table( 1 );
	$infinity23_ptd_themes_table = infinity23_ptd_build_themes_table( 1 );

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp plugins-and-themes-downloader-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Plugins/Themes Downloader Settings</h1>
		<p>Download installed plugins and themes as ZIP files directly from the WordPress admin, no FTP access needed.</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_ptd_activate_action', 'infinity23_ptd_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Plugins/Themes Downloader</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

		<?php wp_nonce_field( 'infinity23_ptd_settings_action', 'infinity23_ptd_nonce' ); ?>

		<div class="section-card" data-card-default="open">

			<h2>Installed Plugins:</h2>

			<p>Download any installed plugin as a ZIP file directly from this list, without visiting the Plugins page.</p>

			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_ptd_plugins_table; ?>

		</div><!--section-card-->

		<div class="section-card" data-card-default="open">

			<h2>Installed Themes:</h2>

			<p>Download any installed theme as a ZIP file directly from this list, without visiting the Themes page.</p>

			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_ptd_themes_table; ?>

		</div><!--section-card-->

		<div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="open">

			<h2>Advanced Settings: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

			<?php echo wp_kses_post( infinity23_pro_notice() ); ?>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="enable_plugins_download" name="enable_plugins_download" value="1" <?php if( $infinity23_enable_plugins_download == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<label for="enable_plugins_download">Add a "Download" link to plugins on the plugins page</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="enable_themes_download" name="enable_themes_download" value="1" <?php if( $infinity23_enable_themes_download == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<label for="enable_themes_download">Add a "Download" link to themes on the themes page</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="include_version_in_filename" name="include_version_in_filename" value="1" <?php if( $infinity23_include_version_in_filename == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<label for="include_version_in_filename">Include the version number in the downloaded file name (e.g. plugin-name.1.0.2.zip)</label>
			</div><!--form-line-->

			<h3>ZIP Settings:</h3>

			<div class="form-line">
				<label for="zip_storage_location">Where to store the ZIP file while it downloads: </label>
				<select id="zip_storage_location" name="zip_storage_location">
					<option value="temp" <?php if( $infinity23_zip_storage_location === 'temp' ) { echo 'selected'; } ?>>Server Temporary Folder (Recommended)</option>
					<option value="uploads" <?php if( $infinity23_zip_storage_location === 'uploads' ) { echo 'selected'; } ?>>WordPress Uploads Folder (only if you have problem downloading files)</option>
				</select>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						The ZIP file has to be written to disk before it can be sent to your browser. This controls where it is written.
						<br /><br />
						<strong>Server Temporary Folder</strong> writes it outside your site's public folders, so there is no web address that can reach it at all. This is the safest option and works the same on Apache, nginx and IIS.
						<br /><br />
						<strong>WordPress Uploads Folder</strong> writes it to wp-content/uploads/infinity23/asset-downloader/ instead. Use this only if your host blocks the temporary folder and downloads stop working.
						<br /><br />
						If the temporary folder cannot be used on your server, the uploads folder is used automatically, so downloads never break because of this setting.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line">
				<label for="zip_time_limit">Maximum time allowed to build a ZIP file (seconds): </label>
				<input type="number" id="zip_time_limit" name="zip_time_limit" class="short-input" min="30" step="1" value="<?php echo esc_attr( $infinity23_zip_time_limit ); ?>" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						In most cases, building the ZIP file does not take more than 60 seconds.
						<br /><br />
						On slower or weaker hosting, or for very large plugins/themes, it might take longer. If downloads are failing or timing out, increase this value. Your server's own PHP execution limit may still apply on top of this setting.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

		</div><!--section-card-->

		<?php infinity23_menu_visibility_field( 'plugins_and_themes_downloader_hide_menu', $infinity23_plugins_and_themes_downloader_hide_menu, $infinity23_module_display_name ); ?>

		<input type="submit" name="submit_ptd" class="submit-btn" value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="additional-data-warp" data-card-default="open">

		<h2>Additional Data</h2>

		<h3>ZIP Storage & Security</h3>

		<p class="additional-data-intro">Each download is built as a ZIP file under a randomized name, in the folder chosen in the "Where to store the ZIP file while it downloads" setting above - either the server's own temporary folder, which no web address can reach, or <span class="highlight-text">wp-content/uploads/infinity23/asset-downloader/</span>. The download link itself is nonce-protected and generated on the fly, and the real file name is never exposed in a URL. The folder is created automatically on the first download and is protected from directory listing by an included index.php file.</p>

		<p class="additional-data-intro">The ZIP file is deleted the moment the request ends, whether the download completed or was cancelled part way through, so nothing is left sitting on the server. As a safety net, both folders are also swept on every new download and once an hour by the plugin's cron - and if you change the storage setting, the folder you moved away from is emptied immediately on save.</p>

		<h3>Current Storage Folder</h3>

		<p class="additional-data-intro">On this server the ZIP files are written to <span class="highlight-text"><?php echo esc_html( $infinity23_zip_storage_path ); ?></span> - the folder is created on the first download. If the server blocks that folder, the WordPress uploads folder is used automatically instead.</p>

		<p class="additional-data-intro">ZIP files sitting in that folder right now: <span class="highlight-text"><?php echo intval( $infinity23_zip_storage_count ); ?></span> - reload this page straight after a download to confirm it went back to zero.</p>

	</div><!--additional-data-warp-->

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_ptd_reset_action', 'infinity23_ptd_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn" data-confirm="Are you sure you want to reset all Plugins/Themes Downloader settings?">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
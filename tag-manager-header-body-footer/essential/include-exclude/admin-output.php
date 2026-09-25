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

	if( isset( $_GET['module_off'] ) && $_GET['module_off'] == '1' && isset( $_GET['module_name'] ) ) {
		$infinity23_success_message = sanitize_text_field( wp_unslash( $_GET['module_name'] ) ) . " module was turned off successfully";
	} elseif( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) {
		$infinity23_success_message = "The settings were saved successfully";
	} // if( isset( $_GET['saved'] ) ) {

	if( isset( $_GET['reset'] ) && $_GET['reset'] == '1' ) {
		$infinity23_success_message = "The settings were reset successfully";
	} // if( isset( $_GET['reset'] ) ) {

	if( isset( $_GET['data_removed'] ) && $_GET['data_removed'] == '1' ) {
		$infinity23_success_message = "All plugin database tables and stored files were removed successfully";
	} // if( isset( $_GET['data_removed'] ) ) {

	// =================================================================================================================
	// Update the data if the form was submitted
	// =================================================================================================================

	if( isset( $_POST['infinity23_danger_zone_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_danger_zone_nonce'] ) ), 'infinity23_danger_zone_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_danger_zone_remove_all_data();

			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-include-exclude', 'data_removed' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_danger_zone_nonce'] ) ) {

	if( isset( $_POST['infinity23_include_exclude_reset_nonce'] ) ) {

			if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_include_exclude_reset_nonce'] ) ), 'infinity23_include_exclude_reset_action' ) && current_user_can( 'manage_options' ) ) {

				infinity23_delete_option( $infinity23_settings_array_db_name );

				if( intval( infinity23_get_option( INFINITY23_RESET_PENDING_OPTION, 0 ) ) == 1 ) {
					infinity23_delete_option( INFINITY23_RESET_PENDING_OPTION );
				} // if( intval( infinity23_get_option( INFINITY23_RESET_PENDING_OPTION, 0 ) ) == 1 ) {

				// the reset only cleared the saved checkbox states - each module's
				// own activate_code is a separate option and was never touched,
				// so without this it stays stuck at whatever it was before the
				// reset while essential/loader.php now excludes that module based
				// on the reverted defaults, making an active module appear "off"
				include( dirname(__FILE__) . "/default-values.php" );
				infinity23_sync_include_exclude_to_activation( $infinity23_defaults );

				$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-include-exclude', 'reset' => '1' ), admin_url('admin.php') );
				infinity23_safe_redirect( $infinity23_redirect_url );

			} else {
				$infinity23_error_message = array( "Form nonce was incorrect" );
			} // if( wp_verify_nonce(

		} // if( isset( $_POST['infinity23_include_exclude_reset_nonce'] ) ) {

		if( isset( $_POST['infinity23_include_exclude_nonce'] ) ) {

			if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_include_exclude_nonce'] ) ), 'infinity23_include_exclude_action' ) && current_user_can( 'manage_options' ) ) {

				global $infinity23_essential_modules_array;

				$infinity23_data_array = array();

				foreach( $infinity23_essential_modules_array as $infinity23_ie_module_key => $infinity23_ie_module_data ) {

					if( empty( $infinity23_ie_module_data['include_exclude_data_key'] ) ) { continue; }

					$infinity23_ie_field_key = $infinity23_ie_module_data['include_exclude_data_key'];
					$infinity23_data_array[ $infinity23_ie_field_key ] = infinity23_post_sanitize( $infinity23_ie_field_key, 'intval' );

				} // foreach( $infinity23_essential_modules_array as $infinity23_ie_module_key => $infinity23_ie_module_data ) {

				infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
				infinity23_sync_include_exclude_to_activation( $infinity23_data_array );

				// the reset (if one was pending) is done - this save is the
				// explicit action that ends it, whatever modules were chosen
				if( intval( infinity23_get_option( INFINITY23_RESET_PENDING_OPTION, 0 ) ) == 1 ) {
					infinity23_delete_option( INFINITY23_RESET_PENDING_OPTION );
				} // if( intval( infinity23_get_option( INFINITY23_RESET_PENDING_OPTION, 0 ) ) == 1 ) {

				$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-include-exclude', 'saved' => '1' ), admin_url('admin.php') );
				infinity23_safe_redirect( $infinity23_redirect_url );

			} else {
				$infinity23_error_message = array( "Form nonce was incorrect" );
			} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_include_exclude_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	include( dirname(__FILE__) . "/default-values.php" );

	global $infinity23_essential_modules_array;

	// --------------------------------------------
	// after a Danger Zone reset every module must render as off here, never
	// falling back to that module's normal default state, until this page
	// gets saved once - only then is the reset actually considered finished
	// --------------------------------------------

	if( intval( infinity23_get_option( INFINITY23_RESET_PENDING_OPTION, 0 ) ) == 1 ) {

		foreach( $infinity23_essential_modules_array as $infinity23_reset_module_data ) {

			if( empty( $infinity23_reset_module_data['include_exclude_data_key'] ) ) { continue; }

			$infinity23_data_array[ $infinity23_reset_module_data['include_exclude_data_key'] ] = 0;

		} // foreach( $infinity23_essential_modules_array as $infinity23_reset_module_data ) {

	} // if( get_option( 'infinity23_reset_pending' ) == 1 ) {

	// ===========================================================================
	// Module Row Output (registry driven) - used only on this page
	// ===========================================================================

	// --------------------------------------------
	// output one module's toggle row, reading title/description/icon/slug/
	// data key from $infinity23_essential_modules_array. Used so every module's row
	// only needs one function call instead of repeating the same HTML block.
	// --------------------------------------------

	if( !function_exists( 'infinity23_render_ie_module_row' ) ) {

		function infinity23_render_ie_module_row( $module_key, $infinity23_data_array, $infinity23_defaults, $row_class = '' ) {

			global $infinity23_essential_modules_array;

			if( !isset( $infinity23_essential_modules_array[ $module_key ] ) ) { return; }

			$module = $infinity23_essential_modules_array[ $module_key ];

			$include_value = intval( infinity23_get_data( $module['include_exclude_data_key'], $infinity23_data_array, $infinity23_defaults ) );

			$infinity23_module_menu_hidden = false;

			if( isset( $module['hide_menu_field'] ) && isset( $module['settings_array_db_name'] ) ) {

				$infinity23_module_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $module['settings_array_db_name'] ) );
				$infinity23_module_menu_hidden = ( intval( infinity23_get_data( $module['hide_menu_field'], $infinity23_module_settings_data, array( $module['hide_menu_field'] => 0 ) ) ) == 1 );

			} // if( isset( $module['hide_menu_field'] ) && isset( $module['settings_array_db_name'] ) ) {

			?>
			<div class="ie-plugin-row <?php echo esc_attr( $row_class ); ?>">
				<div class="ie-toggle-item <?php if( $include_value == 1 ) { echo 'ie-toggle-active'; } ?>" data-input="<?php echo esc_attr( $module['include_exclude_data_key'] ); ?>" data-group="free">
					<div class="ie-toggle-circle <?php if( $include_value == 1 ) { echo 'active'; } ?>"></div>
					<div class="ie-toggle-info">
						<span class="ie-toggle-title-wrap">
							<span class="ie-toggle-title"><?php echo esc_html( $module['include_exclude_title'] ); ?></span>
							<?php if( $infinity23_module_menu_hidden ) { ?>
								<span class="ie-menu-hidden-badge" title="This module's page is hidden from the WordPress sidebar menu">Menu Hidden</span>
							<?php } // if( $infinity23_module_menu_hidden ) { ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $module['admin_page_slug'] ) ); ?>" class="ie-module-settings-link" target="_blank" rel="noopener noreferrer">Settings</a>
						</span><!--ie-toggle-title-wrap-->
						<span class="ie-toggle-desc"><?php echo esc_html( $module['include_exclude_description'] ); ?></span>
					</div><!--ie-toggle-info-->
					<img class="ie-module-icon" src="<?php echo esc_url( infinity23_plugin_url( 'images/' . $module['include_exclude_icon_path'] ) ); ?>" alt="<?php echo esc_attr( $module['include_exclude_title'] ); ?>" />
				</div><!--ie-toggle-item-->
				<input type="hidden" id="<?php echo esc_attr( $module['include_exclude_data_key'] ); ?>" name="<?php echo esc_attr( $module['include_exclude_data_key'] ); ?>" value="<?php echo esc_attr( $include_value ); ?>" />
			</div><!--ie-plugin-row-->
			<?php

		} // function infinity23_render_ie_module_row( $module_key, $include_value, $row_class = '' ) {

	} // if( !function_exists( 'infinity23_render_ie_module_row' ) ) {

?>

<?php
// =================================================================================================================
// Output the plugin form data to page
// =================================================================================================================
?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp include-exclude-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Include / Exclude</h1>
		<p>Choose which plugin modules to include or exclude. Disabled modules will not be loaded by the system.</p>
	</div><!--page-header-->

	<form class="infinity23-form" method="POST" action="">

		<?php wp_nonce_field( 'infinity23_include_exclude_action', 'infinity23_include_exclude_nonce' ); ?>

		<div class="section-card" data-card-default="open">

			<div class="ie-card-header">
				<h2>Essential Modules:</h2>
				<button type="button" class="ie-select-all-btn" data-group="free"><span class="ie-btn-select">Select All</span><span class="ie-btn-sep"> / </span><span class="ie-btn-deselect">Deselect All</span></button>
			</div><!--ie-card-header-->

			<h3>Core Settings</h3>

			<?php infinity23_render_ie_module_row( 'wordpress_settings', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<?php infinity23_render_ie_module_row( 'update_manager', $infinity23_data_array, $infinity23_defaults ); ?>

			<?php infinity23_render_ie_module_row( 'email_smtp', $infinity23_data_array, $infinity23_defaults ); ?>

			<h3>Security</h3>

			<?php infinity23_render_ie_module_row( 'ssl_and_security', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<h3>Legal & Compliance</h3>

			<?php infinity23_render_ie_module_row( 'accessibility', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<?php infinity23_render_ie_module_row( 'privacy_policy', $infinity23_data_array, $infinity23_defaults ); ?>

			<h3>Floating Buttons</h3>

			<?php infinity23_render_ie_module_row( 'back_to_top', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<?php infinity23_render_ie_module_row( 'whatsapp_button', $infinity23_data_array, $infinity23_defaults ); ?>

			<?php infinity23_render_ie_module_row( 'phone_button', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<?php infinity23_render_ie_module_row( 'mobile_contact_line', $infinity23_data_array, $infinity23_defaults ); ?>

			<h3>Content & Site Tools</h3>

			<?php infinity23_render_ie_module_row( 'notes', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<?php infinity23_render_ie_module_row( 'tag_manager', $infinity23_data_array, $infinity23_defaults ); ?>

			<?php infinity23_render_ie_module_row( 'duplicate_pages', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<?php infinity23_render_ie_module_row( 'sticky_sidebar', $infinity23_data_array, $infinity23_defaults ); ?>

			<h3>SEO Tools</h3>

			<?php infinity23_render_ie_module_row( 'llms_and_robots_editor', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<?php infinity23_render_ie_module_row( 'show_all_pages', $infinity23_data_array, $infinity23_defaults ); ?>

			<h3>System & Maintenance</h3>

			<?php infinity23_render_ie_module_row( 'plugins_and_themes_downloader', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<?php infinity23_render_ie_module_row( 'cron_management', $infinity23_data_array, $infinity23_defaults ); ?>

			<?php infinity23_render_ie_module_row( 'redirect_301_and_302', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<?php infinity23_render_ie_module_row( 'show_pages_ids', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<?php infinity23_render_ie_module_row( 'folders_organizer', $infinity23_data_array, $infinity23_defaults, 'ie-row-clear' ); ?>

			<h3>Other Tools</h3>

			<?php infinity23_render_ie_module_row( 'rtl_ltr_direction_change', $infinity23_data_array, $infinity23_defaults ); ?>

		</div><!--section-card-->

		<?php /*

		// =================================================================================================================
		// This section if for Pro Addons, it's hidden but the code here incase we want it back
		// =================================================================================================================

		<div class="section-card" data-card-default="open">

			<div class="ie-card-header">
				<h2>Pro Addons: <?php echo infinity23_section_card_title_pro_label(); ?></h2>
				<button type="button" class="ie-select-all-btn" data-group="pro"><span class="ie-btn-select">Select All</span><span class="ie-btn-sep"> / </span><span class="ie-btn-deselect">Deselect All</span></button>
			</div><!--ie-card-header-->

			<?php echo infinity23_pro_notice(); ?>

		</div><!--section-card-->

		*/ ?>

		<input type="submit" name="submit_include_exclude" class="submit-btn" value="Save Settings" />

	</form>

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_include_exclude_reset_action', 'infinity23_include_exclude_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn" data-confirm="Are you sure you want to reset all include/exclude settings?">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings <small>(Only resets whether modules are active or inactive)</small></button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>


<div class="infinity23-danger-zone-warp" data-card-default="closed">

	<h2><span class="infinity23-danger-zone-icon">&#9888;</span> Danger Zone</h2>

	<p>This permanently removes every piece of data this plugin has stored on your site - all database tables listed below, plus the entire <span class="highlight-text">wp-content/infinity23/</span> folder and everything inside it. The plugin itself stays installed and active - only its stored data is removed.</p>

	<p>The Main Settings Table is emptied rather than dropped, and is automatically reinstalled empty right after the reset finishes - this is expected and does not mean any data was kept. Every module stays turned off until you open Include / Exclude and click Save Settings again.</p>

	<?php

		$infinity23_danger_zone_all_tables = infinity23_get_all_database_tables();
		$infinity23_danger_zone_dropped_tables = array();
		$infinity23_danger_zone_emptied_tables = array();

		foreach( $infinity23_danger_zone_all_tables as $infinity23_danger_zone_table ) {

			if( !empty( $infinity23_danger_zone_table['main'] ) ) {
				$infinity23_danger_zone_emptied_tables[] = $infinity23_danger_zone_table;
			} else {
				$infinity23_danger_zone_dropped_tables[] = $infinity23_danger_zone_table;
			} // if( !empty( $infinity23_danger_zone_table['main'] ) ) {

		} // foreach( $infinity23_danger_zone_all_tables as $infinity23_danger_zone_table ) {

	?>

	<?php if( !empty( $infinity23_danger_zone_dropped_tables ) ) { ?>

		<h3>Database Tables That Will Be Removed:</h3>

		<ul>
			<?php foreach( $infinity23_danger_zone_dropped_tables as $infinity23_danger_zone_table ) { ?>
				<li><?php echo esc_html( $infinity23_danger_zone_table['label'] ); ?> <small>(<?php echo esc_html( $infinity23_danger_zone_table['table_name'] ); ?>)</small></li>
			<?php } // foreach( $infinity23_danger_zone_dropped_tables as $infinity23_danger_zone_table ) { ?>
		</ul>

	<?php } // if( !empty( $infinity23_danger_zone_dropped_tables ) ) { ?>

	<?php if( !empty( $infinity23_danger_zone_emptied_tables ) ) { ?>

		<h3>Database Tables That Will Stay, But Have Their Data Removed:</h3>

		<ul>
			<?php foreach( $infinity23_danger_zone_emptied_tables as $infinity23_danger_zone_table ) { ?>
				<li><?php echo esc_html( $infinity23_danger_zone_table['label'] ); ?> <small>(<?php echo esc_html( $infinity23_danger_zone_table['table_name'] ); ?>)</small></li>
			<?php } // foreach( $infinity23_danger_zone_emptied_tables as $infinity23_danger_zone_table ) { ?>
		</ul>

	<?php } // if( !empty( $infinity23_danger_zone_emptied_tables ) ) { ?>

	<?php $infinity23_danger_zone_folders = infinity23_danger_zone_get_storage_folder_list(); ?>

	<?php if( !empty( $infinity23_danger_zone_folders ) ) { ?>

		<h3>Folders That Will Be Removed:</h3>

		<ul>
			<?php foreach( $infinity23_danger_zone_folders as $infinity23_danger_zone_folder ) { ?>
				<li><span class="highlight-text">wp-content/infinity23/<?php echo esc_html( $infinity23_danger_zone_folder ); ?>/</span> <small>(and everything inside it)</small></li>
			<?php } // foreach( $infinity23_danger_zone_folders as $infinity23_danger_zone_folder ) { ?>
		</ul>

	<?php } // if( !empty( $infinity23_danger_zone_folders ) ) { ?>

	<form method="POST" action="">
		<?php wp_nonce_field( 'infinity23_danger_zone_action', 'infinity23_danger_zone_nonce' ); ?>
		<button type="button" class="infinity23-reset-btn infinity23-danger-zone-btn" data-confirm="This will permanently delete ALL Infinity23 data - every table and stored file. This cannot be undone. Are you absolutely sure?">Remove All Plugin Data</button>
	</form>

</div><!--infinity23-danger-zone-warp-->

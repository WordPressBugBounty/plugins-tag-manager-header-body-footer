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

	if( isset( $_POST['infinity23_sticky_sidebar_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_sticky_sidebar_reset_nonce'] ) ), 'infinity23_sticky_sidebar_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_sticky_sidebar_reset_nonce'] ) ) {

	elseif( isset( $_POST['infinity23_sticky_sidebar_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_sticky_sidebar_activate_nonce'] ) ), 'infinity23_sticky_sidebar_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'sticky_sidebar', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "The Sticky Sidebar feature was activated successfully";
			} else {

				infinity23_module_deactivate_redirect( $infinity23_module_display_name );

			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_sticky_sidebar_activate_nonce'] ) ) {

	elseif( isset( $_POST['infinity23_sticky_sidebar_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_sticky_sidebar_nonce'] ) ), 'infinity23_sticky_sidebar_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			// ----------------------------------------
			// sticky sidebar elements
			// ----------------------------------------

			$infinity23_data_array['sidebar_selector'] = infinity23_pro_field_sanitize( 'sidebar_selector', $infinity23_existing_data_array, $infinity23_defaults );

			if( $infinity23_data_array['sidebar_selector'] === '' ) {
				$infinity23_data_array['sidebar_selector'] = $infinity23_defaults['sidebar_selector']['value'];
			} // if( $infinity23_data_array['sidebar_selector'] === '' ) {

			$infinity23_data_array['container_selector'] = infinity23_pro_field_sanitize( 'container_selector', $infinity23_existing_data_array, $infinity23_defaults );

			// ----------------------------------------
			// sticky behaviour
			// ----------------------------------------

			$infinity23_data_array['top_spacing'] = infinity23_pro_field_sanitize( 'top_spacing', $infinity23_existing_data_array, $infinity23_defaults );

			if( $infinity23_data_array['top_spacing'] < 0 ) {
				$infinity23_data_array['top_spacing'] = $infinity23_defaults['top_spacing']['value'];
			} // if( $infinity23_data_array['top_spacing'] < 0 ) {

			$infinity23_data_array['bottom_spacing'] = infinity23_pro_field_sanitize( 'bottom_spacing', $infinity23_existing_data_array, $infinity23_defaults );

			if( $infinity23_data_array['bottom_spacing'] < 0 ) {
				$infinity23_data_array['bottom_spacing'] = $infinity23_defaults['bottom_spacing']['value'];
			} // if( $infinity23_data_array['bottom_spacing'] < 0 ) {

			$infinity23_data_array['min_width'] = infinity23_pro_field_sanitize( 'min_width', $infinity23_existing_data_array, $infinity23_defaults );

			if( $infinity23_data_array['min_width'] < 0 ) {
				$infinity23_data_array['min_width'] = $infinity23_defaults['min_width']['value'];
			} // if( $infinity23_data_array['min_width'] < 0 ) {

			$infinity23_data_array['update_sidebar_height'] = infinity23_pro_field_sanitize( 'update_sidebar_height', $infinity23_existing_data_array, $infinity23_defaults );

			// ----------------------------------------
			// where to load the sticky sidebar
			// ----------------------------------------

			$infinity23_data_array['load_on_front_page'] = infinity23_pro_field_sanitize( 'load_on_front_page', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['load_on_blog_page'] = infinity23_pro_field_sanitize( 'load_on_blog_page', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['load_on_pages'] = infinity23_pro_field_sanitize( 'load_on_pages', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['load_on_posts'] = infinity23_pro_field_sanitize( 'load_on_posts', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['load_on_archives'] = infinity23_pro_field_sanitize( 'load_on_archives', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['load_on_search'] = infinity23_pro_field_sanitize( 'load_on_search', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['load_on_custom_post_types'] = infinity23_pro_field_sanitize( 'load_on_custom_post_types', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_all_cpt_slugs = array_keys( infinity23_get_custom_post_types() );
			$infinity23_active_cpt_posted = array();

			if( isset( $_POST['sticky_sidebar_custom_post_types_active_list'] ) && is_array( $_POST['sticky_sidebar_custom_post_types_active_list'] ) ) {

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is sanitize_text_field(wp_unslash())'d inside the loop
				foreach( $_POST['sticky_sidebar_custom_post_types_active_list'] as $infinity23_single_cpt_slug ) {

					if( is_array( $infinity23_single_cpt_slug ) ) { continue; } // if( is_array( $infinity23_single_cpt_slug ) ) {

					$infinity23_active_cpt_posted[] = sanitize_text_field( wp_unslash( $infinity23_single_cpt_slug ) );

				} // foreach( $_POST['sticky_sidebar_custom_post_types_active_list'] as $infinity23_single_cpt_slug ) {

			} // if( isset( $_POST['sticky_sidebar_custom_post_types_active_list'] ) && is_array(

			$infinity23_disabled_cpt_to_save = array_diff( $infinity23_all_cpt_slugs, $infinity23_active_cpt_posted );

			$infinity23_data_array['disabled_custom_post_types'] = implode( ',', $infinity23_disabled_cpt_to_save );

			$infinity23_data_array['exclude_ids'] = infinity23_pro_field_sanitize( 'exclude_ids', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['exclude_option'] = infinity23_post_sanitize('exclude_ids_option', 'text');

			if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {
				$infinity23_data_array['exclude_option'] = $infinity23_defaults['exclude_option']['value'];
			} // if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {

			$infinity23_data_array['sticky_sidebar_hide_menu'] = infinity23_pro_field_sanitize( 'sticky_sidebar_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-sticky-sidebar', 'saved' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_sticky_sidebar_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	$infinity23_sidebar_selector = infinity23_pro_get_data( 'sidebar_selector', $infinity23_data_array, $infinity23_defaults );
	$infinity23_container_selector = infinity23_pro_get_data( 'container_selector', $infinity23_data_array, $infinity23_defaults );

	$infinity23_top_spacing = intval( infinity23_pro_get_data( 'top_spacing', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_bottom_spacing = intval( infinity23_pro_get_data( 'bottom_spacing', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_min_width = intval( infinity23_pro_get_data( 'min_width', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_update_sidebar_height = intval( infinity23_pro_get_data( 'update_sidebar_height', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_load_on_front_page = intval( infinity23_pro_get_data( 'load_on_front_page', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_load_on_blog_page = intval( infinity23_pro_get_data( 'load_on_blog_page', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_load_on_pages = intval( infinity23_pro_get_data( 'load_on_pages', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_load_on_posts = intval( infinity23_pro_get_data( 'load_on_posts', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_load_on_archives = intval( infinity23_pro_get_data( 'load_on_archives', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_load_on_search = intval( infinity23_pro_get_data( 'load_on_search', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_load_on_custom_post_types = intval( infinity23_pro_get_data( 'load_on_custom_post_types', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_disabled_custom_post_types = infinity23_pro_get_data( 'disabled_custom_post_types', $infinity23_data_array, $infinity23_defaults );

	$infinity23_exclude_ids = infinity23_pro_get_data( 'exclude_ids', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_option = infinity23_pro_get_data( 'exclude_option', $infinity23_data_array, $infinity23_defaults );

	$infinity23_sticky_sidebar_hide_menu = intval( infinity23_pro_get_data( 'sticky_sidebar_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

	// --------------------------------------------
	// build the custom post type toggle rows, same pattern as Page Notes
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

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp sticky-sidebar-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Sticky Sidebar</h1>
		<p>Below you will be able to choose which sidebar stays in view while visitors scroll down the page, and on which pages it does that:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_sticky_sidebar_activate_action', 'infinity23_sticky_sidebar_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Sticky Sidebar</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

		<div class="section-card" data-card-default="open">

			<h2>Sticky Sidebar Elements:</h2>

			<div class="form-line">
				<label for="sidebar_selector">Sidebar Class Or ID: </label>
				<input type="text" id="sidebar_selector" class="medium-input" name="sidebar_selector" value="<?php echo esc_attr( $infinity23_sidebar_selector ); ?>" />

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						The class or ID of the sidebar of your theme, for example: #secondary or .sidebar
						<br /><br />
						Use a dot before a class name and a # before an ID. If you are not sure what to use, right click on your sidebar and choose Inspect to see its class or ID.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line">
				<label for="container_selector">Container Class Or ID (optional): </label>
				<input type="text" id="container_selector" class="medium-input" name="container_selector" value="<?php echo esc_attr( $infinity23_container_selector ); ?>" />

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						The element that holds both the content and the sidebar. The sidebar only moves inside of it, and stops at its bottom.
						<br /><br />
						Leave empty to use the element that holds the sidebar, this works for most themes.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

		</div><!--section-card-->

		<div class="section-card" data-card-default="open">

			<h2>Sticky Behaviour:</h2>

			<div class="form-line">
				<label for="top_spacing">Space From The Top Of The Window: </label>
				<input type="text" id="top_spacing" class='very-short-input' name="top_spacing" value="<?php echo esc_attr( $infinity23_top_spacing ); ?>" />
				<small>PX</small>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						The space that is left between the sidebar and the top of the window while the sidebar is sticky.
						<br /><br />
						If your site has a fixed header, use its height here. The WordPress admin bar is added automatically for logged in users.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line">
				<label for="bottom_spacing">Space From The Bottom Of The Window: </label>
				<input type="text" id="bottom_spacing" class='very-short-input' name="bottom_spacing" value="<?php echo esc_attr( $infinity23_bottom_spacing ); ?>" />
				<small>PX</small>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						The space that is left between the sidebar and the bottom of the window when the sidebar is taller than the window.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line">
				<label for="min_width">Turn Off On Screens Smaller Than: </label>
				<input type="text" id="min_width" class='very-short-input' name="min_width" value="<?php echo esc_attr( $infinity23_min_width ); ?>" />
				<small>PX</small>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						On smaller screens the sidebar is usually shown under the content, so it should not be sticky there.
						<br /><br />
						Use 0 to never turn it off by screen size. The sidebar is also turned off automatically when your theme moves it under the content.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="update_sidebar_height" name="update_sidebar_height" value="1" <?php if( $infinity23_update_sidebar_height == 1 ) { echo 'checked'; } ?> />
				<label for="update_sidebar_height">Keep the sidebar box at its full height while it moves</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						While the sidebar is sticky it is taken out of the page flow, so its box can become empty and lose its background color or border.
						<br /><br />
						When this is on, the box keeps its full height. Turn it off if it adds extra empty space to your page.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

		</div><!--section-card-->

		<div class="section-card" data-card-default="open">

			<h2>Where To Load The Sticky Sidebar:</h2>

			<h3>Page Types:</h3>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="load_on_front_page" name="load_on_front_page" value="1" <?php if( $infinity23_load_on_front_page == 1 ) { echo 'checked'; } ?> />
				<label for="load_on_front_page">Front page</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="load_on_blog_page" name="load_on_blog_page" value="1" <?php if( $infinity23_load_on_blog_page == 1 ) { echo 'checked'; } ?> />
				<label for="load_on_blog_page">Blog page</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="load_on_pages" name="load_on_pages" value="1" <?php if( $infinity23_load_on_pages == 1 ) { echo 'checked'; } ?> />
				<label for="load_on_pages">Pages</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="load_on_posts" name="load_on_posts" value="1" <?php if( $infinity23_load_on_posts == 1 ) { echo 'checked'; } ?> />
				<label for="load_on_posts">Posts</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="load_on_archives" name="load_on_archives" value="1" <?php if( $infinity23_load_on_archives == 1 ) { echo 'checked'; } ?> />
				<label for="load_on_archives">Archives (categories, tags, authors and dates)</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="load_on_search" name="load_on_search" value="1" <?php if( $infinity23_load_on_search == 1 ) { echo 'checked'; } ?> />
				<label for="load_on_search">Search results</label>
			</div><!--form-line-->

			<?php infinity23_custom_post_types_section( 'load_on_custom_post_types', 'Load the sticky sidebar on Custom Post Types', $infinity23_load_on_custom_post_types, 'sticky_sidebar_custom_post_types_active_list', $infinity23_cpt_toggle_rows, 'Every custom post type on this site is listed below. Turn a type off to stop the sticky sidebar there. By default every custom post type is on.' ); ?>

		</div><!--section-card-->

		<div class="section-card" data-card-default="open">

			<h2>Include / Exclude Pages By ID:</h2>

			<?php infinity23_exclude_ids_field( $infinity23_exclude_ids, $infinity23_exclude_option ); ?>

		</div><!--section-card-->

		<?php infinity23_menu_visibility_field( 'sticky_sidebar_hide_menu', $infinity23_sticky_sidebar_hide_menu, $infinity23_module_display_name ); ?>

		<?php wp_nonce_field( 'infinity23_sticky_sidebar_action', 'infinity23_sticky_sidebar_nonce' ); ?>

		<input type="submit" name="submit_sticky_sidebar" class='submit-btn' value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_sticky_sidebar_reset_action', 'infinity23_sticky_sidebar_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn" data-confirm="Are you sure you want to reset the Sticky Sidebar settings?">Reset Sticky Sidebar Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>

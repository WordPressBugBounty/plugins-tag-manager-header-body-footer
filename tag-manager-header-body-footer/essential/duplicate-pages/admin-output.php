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

	if( isset( $_POST['infinity23_duplicate_pages_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_duplicate_pages_reset_nonce'] ) ), 'infinity23_duplicate_pages_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_duplicate_pages_reset_nonce'] ) ) {

	elseif( isset( $_POST['infinity23_duplicate_pages_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_duplicate_pages_activate_nonce'] ) ), 'infinity23_duplicate_pages_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'duplicate_pages', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "The Duplicate Pages feature was activated successfully";
			} else {

				infinity23_module_deactivate_redirect( $infinity23_module_display_name );

			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_duplicate_pages_activate_nonce'] ) ) {

	elseif( isset( $_POST['infinity23_duplicate_pages_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_duplicate_pages_nonce'] ) ), 'infinity23_duplicate_pages_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			// ----------------------------------------
			// what to copy
			// ----------------------------------------

			$infinity23_data_array['copy_title'] = infinity23_pro_field_sanitize( 'copy_title', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_slug'] = infinity23_pro_field_sanitize( 'copy_slug', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_content'] = infinity23_pro_field_sanitize( 'copy_content', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_excerpt'] = infinity23_pro_field_sanitize( 'copy_excerpt', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_thumbnail'] = infinity23_pro_field_sanitize( 'copy_thumbnail', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_status'] = infinity23_pro_field_sanitize( 'copy_status', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_date'] = infinity23_pro_field_sanitize( 'copy_date', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_author'] = infinity23_pro_field_sanitize( 'copy_author', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_password'] = infinity23_pro_field_sanitize( 'copy_password', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_template'] = infinity23_pro_field_sanitize( 'copy_template', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_format'] = infinity23_pro_field_sanitize( 'copy_format', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_menu_order'] = infinity23_pro_field_sanitize( 'copy_menu_order', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_taxonomies'] = infinity23_pro_field_sanitize( 'copy_taxonomies', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_custom_fields'] = infinity23_pro_field_sanitize( 'copy_custom_fields', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['copy_seo_data'] = infinity23_pro_field_sanitize( 'copy_seo_data', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['excluded_custom_fields'] = infinity23_pro_field_sanitize( 'excluded_custom_fields', $infinity23_existing_data_array, $infinity23_defaults );

			// ----------------------------------------
			// the new copy
			// ----------------------------------------

			$infinity23_data_array['title_prefix'] = infinity23_pro_field_sanitize( 'title_prefix', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['title_suffix'] = infinity23_pro_field_sanitize( 'title_suffix', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['redirect_after'] = infinity23_pro_field_sanitize( 'redirect_after', $infinity23_existing_data_array, $infinity23_defaults );

			if( !in_array( $infinity23_data_array['redirect_after'], array( 'list', 'editor' ), true ) ) {
				$infinity23_data_array['redirect_after'] = $infinity23_defaults['redirect_after']['value'];
			} // if( !in_array( $infinity23_data_array['redirect_after'], array( 'list', 'editor' ), true ) ) {

			// ----------------------------------------
			// where to show the duplicate link
			// ----------------------------------------

			$infinity23_data_array['show_on_pages'] = infinity23_pro_field_sanitize( 'show_on_pages', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_posts'] = infinity23_pro_field_sanitize( 'show_on_posts', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_custom_post_types'] = infinity23_pro_field_sanitize( 'show_on_custom_post_types', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_all_cpt_slugs = array_keys( infinity23_get_custom_post_types() );
			$infinity23_active_cpt_posted = array();

			if( isset( $_POST['duplicate_pages_custom_post_types_active_list'] ) && is_array( $_POST['duplicate_pages_custom_post_types_active_list'] ) ) {

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is sanitize_text_field(wp_unslash())'d inside the loop
				foreach( $_POST['duplicate_pages_custom_post_types_active_list'] as $infinity23_single_cpt_slug ) {

					if( is_array( $infinity23_single_cpt_slug ) ) { continue; } // if( is_array( $infinity23_single_cpt_slug ) ) {

					$infinity23_active_cpt_posted[] = sanitize_text_field( wp_unslash( $infinity23_single_cpt_slug ) );

				} // foreach( $_POST['duplicate_pages_custom_post_types_active_list'] as $infinity23_single_cpt_slug ) {

			} // if( isset( $_POST['duplicate_pages_custom_post_types_active_list'] ) && is_array(

			$infinity23_disabled_cpt_to_save = array_diff( $infinity23_all_cpt_slugs, $infinity23_active_cpt_posted );

			$infinity23_data_array['disabled_custom_post_types'] = implode( ',', $infinity23_disabled_cpt_to_save );

			$infinity23_data_array['show_in_post_list'] = infinity23_pro_field_sanitize( 'show_in_post_list', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_in_admin_bar'] = infinity23_pro_field_sanitize( 'show_in_admin_bar', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_in_bulk_actions'] = infinity23_pro_field_sanitize( 'show_in_bulk_actions', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['duplicate_pages_hide_menu'] = infinity23_pro_field_sanitize( 'duplicate_pages_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-duplicate-pages', 'saved' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_duplicate_pages_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	$infinity23_copy_title = intval( infinity23_pro_get_data( 'copy_title', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_slug = intval( infinity23_pro_get_data( 'copy_slug', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_content = intval( infinity23_pro_get_data( 'copy_content', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_excerpt = intval( infinity23_pro_get_data( 'copy_excerpt', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_thumbnail = intval( infinity23_pro_get_data( 'copy_thumbnail', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_status = intval( infinity23_pro_get_data( 'copy_status', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_date = intval( infinity23_pro_get_data( 'copy_date', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_author = intval( infinity23_pro_get_data( 'copy_author', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_password = intval( infinity23_pro_get_data( 'copy_password', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_template = intval( infinity23_pro_get_data( 'copy_template', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_format = intval( infinity23_pro_get_data( 'copy_format', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_menu_order = intval( infinity23_pro_get_data( 'copy_menu_order', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_taxonomies = intval( infinity23_pro_get_data( 'copy_taxonomies', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_custom_fields = intval( infinity23_pro_get_data( 'copy_custom_fields', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_copy_seo_data = intval( infinity23_pro_get_data( 'copy_seo_data', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_excluded_custom_fields = infinity23_pro_get_data( 'excluded_custom_fields', $infinity23_data_array, $infinity23_defaults );

	$infinity23_title_prefix = infinity23_pro_get_data( 'title_prefix', $infinity23_data_array, $infinity23_defaults );
	$infinity23_title_suffix = infinity23_pro_get_data( 'title_suffix', $infinity23_data_array, $infinity23_defaults );
	$infinity23_redirect_after = infinity23_pro_get_data( 'redirect_after', $infinity23_data_array, $infinity23_defaults );

	$infinity23_show_on_pages = intval( infinity23_pro_get_data( 'show_on_pages', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_posts = intval( infinity23_pro_get_data( 'show_on_posts', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_custom_post_types = intval( infinity23_pro_get_data( 'show_on_custom_post_types', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_disabled_custom_post_types = infinity23_pro_get_data( 'disabled_custom_post_types', $infinity23_data_array, $infinity23_defaults );
	$infinity23_show_in_post_list = intval( infinity23_pro_get_data( 'show_in_post_list', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_in_admin_bar = intval( infinity23_pro_get_data( 'show_in_admin_bar', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_in_bulk_actions = intval( infinity23_pro_get_data( 'show_in_bulk_actions', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_duplicate_pages_hide_menu = intval( infinity23_pro_get_data( 'duplicate_pages_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

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

<div class="infinity23-warp duplicate-pages-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Duplicate Pages</h1>
		<p>Below you will be able to choose what is copied when you duplicate a page, post or custom post type, and where the Duplicate link is shown:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_duplicate_pages_activate_action', 'infinity23_duplicate_pages_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Duplicate Pages</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

		<div class="section-card" data-card-default="open">

			<h2>What To Copy:</h2>

			<h3>Content:</h3>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_title" name="copy_title" value="1" <?php if( $infinity23_copy_title == 1 ) { echo 'checked'; } ?> />
				<label for="copy_title">Title</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_slug" name="copy_slug" value="1" <?php if( $infinity23_copy_slug == 1 ) { echo 'checked'; } ?> />
				<label for="copy_slug">Slug (the page address)</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						The copy is created as a draft, so it will not conflict with the original until it is published.
						<br /><br />
						WordPress adds a number to the slug of the copy when it is published if the same slug already exists.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_content" name="copy_content" value="1" <?php if( $infinity23_copy_content == 1 ) { echo 'checked'; } ?> />
				<label for="copy_content">Content</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_excerpt" name="copy_excerpt" value="1" <?php if( $infinity23_copy_excerpt == 1 ) { echo 'checked'; } ?> />
				<label for="copy_excerpt">Excerpt</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_thumbnail" name="copy_thumbnail" value="1" <?php if( $infinity23_copy_thumbnail == 1 ) { echo 'checked'; } ?> />
				<label for="copy_thumbnail">Featured Image</label>
			</div><!--form-line-->

			<h3>Settings:</h3>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_status" name="copy_status" value="1" <?php if( $infinity23_copy_status == 1 ) { echo 'checked'; } ?> />
				<label for="copy_status">Status (keep the original status instead of creating a draft)</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						When this is off, every copy is created as a draft.
						<br /><br />
						When this is on, a published item is copied as published, unless the user does not have permission to publish, in which case it is created as pending.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_date" name="copy_date" value="1" <?php if( $infinity23_copy_date == 1 ) { echo 'checked'; } ?> />
				<label for="copy_date">Publish Date</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_author" name="copy_author" value="1" <?php if( $infinity23_copy_author == 1 ) { echo 'checked'; } ?> />
				<label for="copy_author">Author (otherwise the copy belongs to the user who duplicates)</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_password" name="copy_password" value="1" <?php if( $infinity23_copy_password == 1 ) { echo 'checked'; } ?> />
				<label for="copy_password">Password</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_template" name="copy_template" value="1" <?php if( $infinity23_copy_template == 1 ) { echo 'checked'; } ?> />
				<label for="copy_template">Page Template</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_format" name="copy_format" value="1" <?php if( $infinity23_copy_format == 1 ) { echo 'checked'; } ?> />
				<label for="copy_format">Post Format</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_menu_order" name="copy_menu_order" value="1" <?php if( $infinity23_copy_menu_order == 1 ) { echo 'checked'; } ?> />
				<label for="copy_menu_order">Menu Order</label>
			</div><!--form-line-->

			<h3>Taxonomies, Custom Fields & SEO:</h3>

			<p>Custom fields hold the extra data that plugins and page builders save for a page, such as ACF fields and Elementor data. SEO data is controlled separately, so you can turn it off without losing the other custom fields.</p>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_taxonomies" name="copy_taxonomies" value="1" <?php if( $infinity23_copy_taxonomies == 1 ) { echo 'checked'; } ?> />
				<label for="copy_taxonomies">Taxonomies (categories, tags and custom taxonomies)</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_custom_fields" name="copy_custom_fields" value="1" <?php if( $infinity23_copy_custom_fields == 1 ) { echo 'checked'; } ?> />
				<label for="copy_custom_fields">Custom Fields</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Copies every custom field of the original, including the ones saved by ACF, page builders and other plugins.
						<br /><br />
						The featured image and page template are controlled by their own settings above, and SEO fields by the setting below.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="copy_seo_data" name="copy_seo_data" value="1" <?php if( $infinity23_copy_seo_data == 1 ) { echo 'checked'; } ?> />
				<label for="copy_seo_data">SEO Data (Yoast SEO)</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Copies the SEO title, description, focus keyword, robots settings and the other Yoast SEO fields of the original.
						<br /><br />
						If you do not want the copy to share the same SEO data as the original, turn this off.
						<br /><br />
						SEO data saved by other plugins as custom fields is copied by the Custom Fields setting above.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line">
				<label for="excluded_custom_fields">Do Not Copy These Custom Fields: </label>
				<input type="text" id="excluded_custom_fields" class="very-long-input" name="excluded_custom_fields" value="<?php echo esc_attr( $infinity23_excluded_custom_fields ); ?>" />

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						A comma separated list of custom field names that must not be copied, for example: _yoast_wpseo_canonical, rank_math_*
						<br /><br />
						Use * to match any characters, so rank_math_* skips every field that starts with rank_math_.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

		</div><!--section-card-->

		<div class="section-card" data-card-default="open">

			<h2>The New Copy:</h2>

			<div class="form-line">
				<label for="title_prefix">Title Prefix: </label>
				<input type="text" id="title_prefix" class="medium-input" name="title_prefix" value="<?php echo esc_attr( $infinity23_title_prefix ); ?>" />

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Text added before the title of the copy, for example: Copy of
						<br /><br />
						Leave empty to add nothing.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line">
				<label for="title_suffix">Title Suffix: </label>
				<input type="text" id="title_suffix" class="medium-input" name="title_suffix" value="<?php echo esc_attr( $infinity23_title_suffix ); ?>" />

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Text added after the title of the copy, for example: (Copy)
						<br /><br />
						Leave empty to add nothing.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line">
				<label for="redirect_after">After Duplicating: </label>
				<select id="redirect_after" name="redirect_after">
					<option value="list" <?php if( $infinity23_redirect_after == 'list' ) { echo 'selected'; } ?>>Stay on the list</option>
					<option value="editor" <?php if( $infinity23_redirect_after == 'editor' ) { echo 'selected'; } ?>>Open the new copy in the editor</option>
				</select>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Choose where to go after a single item is duplicated.
						<br /><br />
						When duplicating several items with the bulk action, you always stay on the list.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

		</div><!--section-card-->

		<div class="section-card" data-card-default="open">

			<h2>Where To Show The Duplicate Link:</h2>

			<h3>Post Types:</h3>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="show_on_pages" name="show_on_pages" value="1" <?php if( $infinity23_show_on_pages == 1 ) { echo 'checked'; } ?> />
				<label for="show_on_pages">Pages</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="show_on_posts" name="show_on_posts" value="1" <?php if( $infinity23_show_on_posts == 1 ) { echo 'checked'; } ?> />
				<label for="show_on_posts">Posts</label>
			</div><!--form-line-->

			<?php infinity23_custom_post_types_section( 'show_on_custom_post_types', 'Allow duplicating Custom Post Types', $infinity23_show_on_custom_post_types, 'duplicate_pages_custom_post_types_active_list', $infinity23_cpt_toggle_rows, 'Every custom post type on this site is listed below. Turn a type off to hide the Duplicate link there. By default every custom post type is on.' ); ?>

			<h3>Locations:</h3>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="show_in_post_list" name="show_in_post_list" value="1" <?php if( $infinity23_show_in_post_list == 1 ) { echo 'checked'; } ?> />
				<label for="show_in_post_list">Post list (a Duplicate link under each title)</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="show_in_admin_bar" name="show_in_admin_bar" value="1" <?php if( $infinity23_show_in_admin_bar == 1 ) { echo 'checked'; } ?> />
				<label for="show_in_admin_bar">Admin bar (on the edit screen and when viewing the page on the site)</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="show_in_bulk_actions" name="show_in_bulk_actions" value="1" <?php if( $infinity23_show_in_bulk_actions == 1 ) { echo 'checked'; } ?> />
				<label for="show_in_bulk_actions">Bulk Actions menu (duplicate several items at once)</label>
			</div><!--form-line-->

		</div><!--section-card-->

		<?php infinity23_menu_visibility_field( 'duplicate_pages_hide_menu', $infinity23_duplicate_pages_hide_menu, $infinity23_module_display_name ); ?>

		<?php wp_nonce_field( 'infinity23_duplicate_pages_action', 'infinity23_duplicate_pages_nonce' ); ?>

		<input type="submit" name="submit_duplicate_pages" class='submit-btn' value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_duplicate_pages_reset_action', 'infinity23_duplicate_pages_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn" data-confirm="Are you sure you want to reset the Duplicate Pages settings?">Reset Duplicate Pages Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>

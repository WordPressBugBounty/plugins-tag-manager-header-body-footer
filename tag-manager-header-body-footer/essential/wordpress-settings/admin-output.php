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

	if( isset( $_POST['infinity23_wordpress_settings_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_wordpress_settings_reset_nonce'] ) ), 'infinity23_wordpress_settings_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_wordpress_settings_reset_nonce'] ) ) {

	elseif( isset( $_POST['infinity23_wordpress_settings_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_wordpress_settings_activate_nonce'] ) ), 'infinity23_wordpress_settings_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'wordpress_settings', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "The WordPress Settings feature was activated successfully";
			} else {

				infinity23_module_deactivate_redirect( $infinity23_module_display_name );

			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_wordpress_settings_activate_nonce'] ) ) {

	elseif( isset( $_POST['infinity23_wordpress_settings_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_wordpress_settings_nonce'] ) ), 'infinity23_wordpress_settings_action' ) && current_user_can( 'manage_options' ) ) {

			// ----------------------------------------
			// site identity fields - saved directly into WordPress core settings
			// ----------------------------------------

			$infinity23_new_site_name = infinity23_post_sanitize('site_name', 'text');
			$infinity23_new_site_description = infinity23_post_sanitize('site_description', 'text');
			$infinity23_new_site_logo_url = infinity23_post_sanitize('site_logo_url', 'url');
			$infinity23_new_site_favicon_url = infinity23_post_sanitize('site_favicon_url', 'url');

			// these write straight into WordPress core settings, so they only run
			// while the module is actually on, and the title/tagline writes only
			// when the value really changed - a save from a stale tab used to
			// revert a site title that had been edited somewhere else meanwhile
			$infinity23_site_identity_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

			if( $infinity23_site_identity_active == 1 ) {

				if( $infinity23_new_site_name !== get_option( 'blogname' ) ) {
					update_option( 'blogname', $infinity23_new_site_name );
				} // if( $infinity23_new_site_name !== get_option( 'blogname' ) ) {

				if( $infinity23_new_site_description !== get_option( 'blogdescription' ) ) {
					update_option( 'blogdescription', $infinity23_new_site_description );
				} // if( $infinity23_new_site_description !== get_option( 'blogdescription' ) ) {

				if( !empty( $infinity23_new_site_logo_url ) ) {

					$infinity23_logo_attachment_id = attachment_url_to_postid( $infinity23_new_site_logo_url );

					if( !empty( $infinity23_logo_attachment_id ) ) {
						set_theme_mod( 'custom_logo', $infinity23_logo_attachment_id );
					} // if( !empty( $infinity23_logo_attachment_id ) ) {

				} else {
					remove_theme_mod( 'custom_logo' );
				} // if( !empty( $infinity23_new_site_logo_url ) ) {

				if( !empty( $infinity23_new_site_favicon_url ) ) {

					$infinity23_favicon_attachment_id = attachment_url_to_postid( $infinity23_new_site_favicon_url );

					if( !empty( $infinity23_favicon_attachment_id ) ) {
						update_option( 'site_icon', $infinity23_favicon_attachment_id );
					} // if( !empty( $infinity23_favicon_attachment_id ) ) {

				} else {
					delete_option( 'site_icon' );
				} // if( !empty( $infinity23_new_site_favicon_url ) ) {

			} // if( $infinity23_site_identity_active == 1 ) {

			// ----------------------------------------
			// Gutenberg settings
			// ----------------------------------------

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_data_array['disable_block_editor'] = infinity23_pro_field_sanitize( 'disable_block_editor', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['disable_widgets_block_editor'] = infinity23_pro_field_sanitize( 'disable_widgets_block_editor', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['remove_gutenberg_frontend_css'] = infinity23_pro_field_sanitize( 'remove_gutenberg_frontend_css', $infinity23_existing_data_array, $infinity23_defaults );

			// ----------------------------------------
			// Media settings
			// ----------------------------------------

			$infinity23_data_array['stop_wp_images_resize'] = infinity23_pro_field_sanitize( 'stop_wp_images_resize', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['remove_default_image_title_on_upload'] = infinity23_pro_field_sanitize( 'remove_default_image_title_on_upload', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['remove_empty_image_title_tag'] = infinity23_pro_field_sanitize( 'remove_empty_image_title_tag', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['load_filename_in_media_library'] = infinity23_pro_field_sanitize( 'load_filename_in_media_library', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['sanitize_non_english_image_names'] = infinity23_pro_field_sanitize( 'sanitize_non_english_image_names', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['wordpress_settings_hide_menu'] = infinity23_pro_field_sanitize( 'wordpress_settings_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-wordpress-settings', 'saved' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_wordpress_settings_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_site_name = get_option( 'blogname' );
	$infinity23_site_description = get_option( 'blogdescription' );

	$infinity23_site_icon_id = intval( get_option( 'site_icon' ) );
	$infinity23_site_favicon_url = !empty( $infinity23_site_icon_id ) ? wp_get_attachment_image_url( $infinity23_site_icon_id, 'full' ) : '';

	$infinity23_custom_logo_id = intval( get_theme_mod( 'custom_logo' ) );
	$infinity23_site_logo_url = !empty( $infinity23_custom_logo_id ) ? wp_get_attachment_image_url( $infinity23_custom_logo_id, 'full' ) : '';

	$infinity23_disable_block_editor = intval( infinity23_pro_get_data( 'disable_block_editor', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_disable_widgets_block_editor = intval( infinity23_pro_get_data( 'disable_widgets_block_editor', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_remove_gutenberg_frontend_css = intval( infinity23_pro_get_data( 'remove_gutenberg_frontend_css', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_stop_wp_images_resize = intval( infinity23_pro_get_data( 'stop_wp_images_resize', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_remove_default_image_title_on_upload = intval( infinity23_pro_get_data( 'remove_default_image_title_on_upload', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_remove_empty_image_title_tag = intval( infinity23_pro_get_data( 'remove_empty_image_title_tag', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_load_filename_in_media_library = intval( infinity23_pro_get_data( 'load_filename_in_media_library', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_sanitize_non_english_image_names = intval( infinity23_pro_get_data( 'sanitize_non_english_image_names', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_wordpress_settings_hide_menu = intval( infinity23_pro_get_data( 'wordpress_settings_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp wordpress-settings-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>WordPress Settings</h1>
		<p>Below you will be able to edit the site identity and the Gutenberg editor behavior:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_wordpress_settings_activate_action', 'infinity23_wordpress_settings_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate WordPress Settings</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

		<div class="section-card" data-card-default="open">

			<h2>Site Identity:</h2>

			<p>The fields below are read directly from your WordPress site settings (the same ones under <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline' ) ); ?>" target="_blank" rel="noopener noreferrer">Appearance > Customize > Site Identity</a>). Saving here updates those settings, and any change made from <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline' ) ); ?>" target="_blank" rel="noopener noreferrer">the Customizer</a> will also show up here.</p>

			<h3>Site Images:</h3>

			<div class="form-line">

				<div class="infinity23-upload-wrap">
					<?php if( !empty( $infinity23_site_logo_url ) ) { ?>
						<img class="infinity23-upload-preview infinity23-logo-preview-img" id="site_logo_preview" src="<?php echo esc_url( $infinity23_site_logo_url ); ?>" />
					<?php } else { ?>
						<img class="infinity23-upload-preview infinity23-logo-preview-img" id="site_logo_preview" src="" style="display:none;" />
					<?php } // if( !empty( $infinity23_site_logo_url ) ) { ?>
					<label for="site_logo_url">Site Logo: </label>
					<input type="text" id="site_logo_url" class="infinity23-upload-input very-long-input" name="site_logo_url" value="<?php echo esc_url( $infinity23_site_logo_url ); ?>" />
					<input type="button" class="infinity23-upload-btn infinity23-upload-btn-style" value="Choose Image..." />

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							This sets the site logo used by your theme, the same logo shown under Appearance > Customize > Site Identity.
							<br /><br />
							Choose an image from the Media Library so WordPress can link it correctly.
						</div>
						?
					</div><!--backtest-field-help-icon-->

				</div><!--infinity23-upload-wrap-->

			</div><!--form-line-->

			<div class="form-line">

				<div class="infinity23-upload-wrap">
					<?php if( !empty( $infinity23_site_favicon_url ) ) { ?>
						<img class="infinity23-upload-preview infinity23-favicon-preview-img" id="site_favicon_preview" src="<?php echo esc_url( $infinity23_site_favicon_url ); ?>" />
					<?php } else { ?>
						<img class="infinity23-upload-preview infinity23-favicon-preview-img" id="site_favicon_preview" src="" style="display:none;" />
					<?php } // if( !empty( $infinity23_site_favicon_url ) ) { ?>
					<label for="site_favicon_url">Site Favicon: </label>
					<input type="text" id="site_favicon_url" class="infinity23-upload-input very-long-input" name="site_favicon_url" value="<?php echo esc_url( $infinity23_site_favicon_url ); ?>" />
					<input type="button" class="infinity23-upload-btn infinity23-upload-btn-style" value="Choose Image..." />

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							This sets the site icon used in the browser tab, the same setting under Appearance > Customize > Site Identity.
							<br /><br />
							For best results use a square image at least 512 x 512 pixels.
						</div>
						?
					</div><!--backtest-field-help-icon-->

				</div><!--infinity23-upload-wrap-->

			</div><!--form-line-->

			<h3>Site Details:</h3>

			<div class="form-line">
				<label for="site_name">Site Name: </label>
				<input type="text" id="site_name" class="very-long-input" name="site_name" value="<?php echo esc_attr( $infinity23_site_name ); ?>" />
			</div><!--form-line-->

			<div class="form-line">
				<label for="site_description">Site Description: </label>
				<input type="text" id="site_description" class="very-long-input" name="site_description" value="<?php echo esc_attr( $infinity23_site_description ); ?>" />
			</div><!--form-line-->

		</div><!--section-card-->

		<div class="section-card" data-card-default="open">

			<h2>Gutenberg Settings:</h2>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="disable_block_editor" name="disable_block_editor" value="1" <?php if( $infinity23_disable_block_editor == 1 ) { echo 'checked'; } ?> />
				<label for="disable_block_editor">Disable Gutenberg and use the classic editor instead</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Turns off the block editor for posts and pages, and brings back the classic editor.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="disable_widgets_block_editor" name="disable_widgets_block_editor" value="1" <?php if( $infinity23_disable_widgets_block_editor == 1 ) { echo 'checked'; } ?> />
				<label for="disable_widgets_block_editor">Convert the widgets editor back to the classic widgets editor</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Turns off the block editor for the Widgets screen only, and brings back the classic drag and drop widgets screen.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="remove_gutenberg_frontend_css" name="remove_gutenberg_frontend_css" value="1" <?php if( $infinity23_remove_gutenberg_frontend_css == 1 ) { echo 'checked'; } ?> />
				<label for="remove_gutenberg_frontend_css">Remove Gutenberg CSS from the front-end of the site</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Removes the default block library CSS file from the front-end of the site.
						<br /><br />
						Only enable this if your theme or page builder already provides its own styling for blocks.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

		</div><!--section-card-->

		<div class="section-card" data-card-default="open">

			<h2>Media Settings:</h2>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="stop_wp_images_resize" name="stop_wp_images_resize" value="1" <?php if( $infinity23_stop_wp_images_resize == 1 ) { echo 'checked'; } ?> />
				<label for="stop_wp_images_resize">Stop WordPress from creating multiple resized copies of every uploaded image</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						By default WordPress creates several extra resized copies of every image you upload (thumbnail, medium, large, etc).
						<br /><br />
						Enabling this stops those extra copies from being created, which saves disk space.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<h3>Image Title:</h3>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="remove_default_image_title_on_upload" name="remove_default_image_title_on_upload" value="1" <?php if( $infinity23_remove_default_image_title_on_upload == 1 ) { echo 'checked'; } ?> />
				<label for="remove_default_image_title_on_upload">Do not set the file name as the image Title automatically on upload</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="remove_empty_image_title_tag" name="remove_empty_image_title_tag" value="1" <?php if( $infinity23_remove_empty_image_title_tag == 1 ) { echo 'checked'; } ?> />
				<label for="remove_empty_image_title_tag">Remove the empty title attribute from images on the front-end</label>
			</div><!--form-line-->

			<div class="form-line checkbox-line">
				<input type="checkbox" id="load_filename_in_media_library" name="load_filename_in_media_library" value="1" <?php if( $infinity23_load_filename_in_media_library == 1 ) { echo 'checked'; } ?> />
				<label for="load_filename_in_media_library">Show the file name in the Media Library instead of an empty Title</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						When an image has no Title, the Media Library normally shows it blank.
						<br /><br />
						Enabling this shows the file name instead, so images are easier to find and identify.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

			<h3>Image File Names:</h3>

			<div class="form-line checkbox-line">
				<input type="checkbox" id="sanitize_non_english_image_names" name="sanitize_non_english_image_names" value="1" <?php if( $infinity23_sanitize_non_english_image_names == 1 ) { echo 'checked'; } ?> />
				<label for="sanitize_non_english_image_names">Rename uploaded images that contain characters outside English letters and numbers</label>

				<div class="backtest-field-help-icon">
					<div class="data-help-content">
						Some servers and CDNs have trouble loading images whose file name contains Hebrew, Arabic, or other non-English characters.
						<br /><br />
						Enabling this automatically renames any uploaded image whose file name is not made only of English letters, numbers, dashes, underscores, dots and spaces to a safe generated name.
					</div>
					?
				</div><!--backtest-field-help-icon-->
			</div><!--form-line-->

		</div><!--section-card-->

		<?php infinity23_menu_visibility_field( 'wordpress_settings_hide_menu', $infinity23_wordpress_settings_hide_menu, $infinity23_module_display_name ); ?>

		<?php wp_nonce_field( 'infinity23_wordpress_settings_action', 'infinity23_wordpress_settings_nonce' ); ?>

		<input type="submit" name="submit_wordpress_settings" class='submit-btn' value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_wordpress_settings_reset_action', 'infinity23_wordpress_settings_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn" data-confirm="Are you sure you want to reset the Gutenberg settings? Site name, description, logo and favicon will not be affected.">Reset Gutenberg Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
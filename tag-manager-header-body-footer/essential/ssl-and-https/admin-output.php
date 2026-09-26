<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// =================================================================================================================
	// Initial plugin load and getting default values
	// =================================================================================================================

	include( dirname(__FILE__) . "/../../include/settings.php" );
	include( dirname(__FILE__) . "/internal-settings.php" );
	include( dirname(__FILE__) . "/back-end/ssl-functions.php" );
	infinity23_load_essential_files();

	$infinity23_ssl_certificate_detected = infinity23_ssl_detect_certificate();

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
	// reset all ssl and security settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_ssl_and_security_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_ssl_and_security_reset_nonce'] ) ), 'infinity23_ssl_and_security_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_ssl_and_security_reset_nonce'] ) ) {

	// ----------------------------------------
	// code toggle activation form submit
	// ----------------------------------------

	if( isset( $_POST['infinity23_ssl_and_security_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_ssl_and_security_activate_nonce'] ) ), 'infinity23_ssl_and_security_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');

			if( $infinity23_new_activate_code == 1 && !$infinity23_ssl_certificate_detected ) {

				$infinity23_error_message = array( "SSL is not detected on this site - install a valid SSL certificate before activating Force SSL & HTTPS" );

			} else {

				infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
				infinity23_sync_activation_to_include_exclude( 'ssl_and_security', $infinity23_new_activate_code );

				if( $infinity23_new_activate_code == 1 ) {
					$infinity23_success_message = "Force SSL & HTTPS was activated successfully";
				} else {
					infinity23_module_deactivate_redirect( $infinity23_module_display_name );
				} // if( $infinity23_new_activate_code == 1 ) {

			} // if( $infinity23_new_activate_code == 1 && !$infinity23_ssl_certificate_detected ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_ssl_and_security_activate_nonce'] ) ) {

	// ----------------------------------------
	// Submit the main form with data
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_ssl_and_security_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_ssl_and_security_nonce'] ) ), 'infinity23_ssl_and_security_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_data_array['force_ssl_checkbox'] = infinity23_pro_field_sanitize( 'force_ssl_checkbox', $infinity23_existing_data_array, $infinity23_defaults );

			$infinity23_data_array['force_ssl_detection_mode'] = infinity23_pro_field_sanitize( 'force_ssl_detection_mode', $infinity23_existing_data_array, $infinity23_defaults );
			if( !in_array( $infinity23_data_array['force_ssl_detection_mode'], array( 'standard', 'extended' ), true ) ) {
				$infinity23_data_array['force_ssl_detection_mode'] = 'extended';
			} // if( !in_array( $infinity23_data_array['force_ssl_detection_mode'], array( 'standard', 'extended' ), true ) ) {

			$infinity23_data_array['force_ssl_localhost_mode'] = infinity23_pro_field_sanitize( 'force_ssl_localhost_mode', $infinity23_existing_data_array, $infinity23_defaults );
			if( !in_array( $infinity23_data_array['force_ssl_localhost_mode'], array( 'force', 'exclude' ), true ) ) {
				$infinity23_data_array['force_ssl_localhost_mode'] = 'exclude';
			} // if( !in_array( $infinity23_data_array['force_ssl_localhost_mode'], array( 'force', 'exclude' ), true ) ) {
			$infinity23_data_array['fix_mixed_content_checkbox'] = infinity23_pro_field_sanitize( 'fix_mixed_content_checkbox', $infinity23_existing_data_array, $infinity23_defaults );

			// the ssl fields are only rendered while a certificate is detected -
			// when the page was shown without them, keep what was already saved
			// instead of turning force https and the mixed content fixer off
			if( infinity23_post_sanitize( 'infinity23_ssl_fields_rendered', 'intval' ) !== 1 ) {

				foreach( array( 'force_ssl_checkbox', 'force_ssl_detection_mode', 'force_ssl_localhost_mode', 'fix_mixed_content_checkbox' ) as $infinity23_ssl_kept_field ) {
					$infinity23_data_array[ $infinity23_ssl_kept_field ] = infinity23_get_data( $infinity23_ssl_kept_field, $infinity23_existing_data_array, $infinity23_defaults );
				} // foreach( array( 'force_ssl_checkbox', 'force_ssl_detection_mode', 'force_ssl_localhost_mode', 'fix_mixed_content_checkbox' ) as $infinity23_ssl_kept_field ) {

			} // if( infinity23_post_sanitize( 'infinity23_ssl_fields_rendered', 'intval' ) !== 1 ) {
			$infinity23_data_array['ssl_and_security_hide_menu'] = infinity23_pro_field_sanitize( 'ssl_and_security_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-ssl-and-https', 'saved' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_ssl_and_security_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_force_ssl_checkbox = intval( infinity23_pro_get_data( 'force_ssl_checkbox', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_force_ssl_detection_mode = infinity23_pro_get_data( 'force_ssl_detection_mode', $infinity23_data_array, $infinity23_defaults );
	if( !in_array( $infinity23_force_ssl_detection_mode, array( 'standard', 'extended' ), true ) ) {
		$infinity23_force_ssl_detection_mode = 'extended';
	} // if( !in_array( $infinity23_force_ssl_detection_mode, array( 'standard', 'extended' ), true ) ) {

	$infinity23_force_ssl_localhost_mode = infinity23_pro_get_data( 'force_ssl_localhost_mode', $infinity23_data_array, $infinity23_defaults );
	if( !in_array( $infinity23_force_ssl_localhost_mode, array( 'force', 'exclude' ), true ) ) {
		$infinity23_force_ssl_localhost_mode = 'exclude';
	} // if( !in_array( $infinity23_force_ssl_localhost_mode, array( 'force', 'exclude' ), true ) ) {
	$infinity23_fix_mixed_content_checkbox = intval( infinity23_pro_get_data( 'fix_mixed_content_checkbox', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_ssl_and_security_hide_menu = intval( infinity23_pro_get_data( 'ssl_and_security_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp ssl-and-https-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Force SSL & HTTPS Settings</h1>
		<p>Below you will be able to edit and make changes to the SSL and security output:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_ssl_and_security_activate_action', 'infinity23_ssl_and_security_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<?php if( $infinity23_ssl_certificate_detected || $infinity23_activate_code == 1 ) { ?>

				<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
					<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
					<div class="toggle-info">
						<span class="toggle-title">Activate Force SSL & HTTPS</span>
					</div><!--toggle-info-->
				</div><!--toggle-item-->

			<?php } else { ?>

				<div class="toggle-item-locked">
					<div class="toggle-circle"></div>
					<div class="toggle-info">
						<span class="toggle-title">Activate Force SSL & HTTPS (SSL Not Detected)</span>
					</div><!--toggle-info-->
				</div><!--toggle-item-locked-->

			<?php } // if( $infinity23_ssl_certificate_detected || $infinity23_activate_code == 1 ) { ?>

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

			<div class="section-card" data-card-default="open">

				<h2>SSL Settings:</h2>

				<?php if( !$infinity23_ssl_certificate_detected ) { ?>
					<p class="infinity23-message infinity23-message-error">SSL is not detected on this site. You must install a valid SSL certificate before you can activate Force SSL & HTTPS
					<br />
					Activating without a valid certificate can make your site inaccessible.</p>
				<?php } // if( !$infinity23_ssl_certificate_detected ) { ?>

				<?php if( $infinity23_ssl_certificate_detected ) { ?>

				<input type="hidden" name="infinity23_ssl_fields_rendered" value="1" />

				<div class="form-line checkbox-line">
					<input type="checkbox" id="force_ssl_checkbox" name="force_ssl_checkbox" value="1" <?php if( $infinity23_force_ssl_checkbox == 1 ) { echo 'checked'; } ?> />
					<label for="force_ssl_checkbox">Force HTTPS on all requests</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Redirects any HTTP request to HTTPS with a 301 redirect, including the WordPress admin area, the login page, and REST API requests.
							<br /><br />
							The Detection Mode and Development Environment (Localhost) options below control exactly how and where this applies.
							<br /><br />
							WP-CLI commands and Let's Encrypt validation requests are never redirected.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="infinity23-toggle-fields" data-toggle-input="force_ssl_checkbox">

					<div class="form-line">
						<label for="force_ssl_detection_mode">HTTPS Detection Mode: </label>
						<select id="force_ssl_detection_mode" name="force_ssl_detection_mode">
							<option value="extended" <?php if( $infinity23_force_ssl_detection_mode === 'extended' ) { echo 'selected'; } ?>>Extended (Recommended)</option>
							<option value="standard" <?php if( $infinity23_force_ssl_detection_mode === 'standard' ) { echo 'selected'; } ?>>Standard</option>
						</select>

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								Controls how the plugin decides if a request is already using HTTPS.
								<br /><br />
								<strong>Extended</strong> also checks common reverse proxy and CDN headers (Cloudflare, CloudFront, a generic load balancer). Those headers are only trusted when the proxy connects from a private IP address.
								<br /><br />
								If your site is behind Cloudflare or another proxy with a public IP address, add <span class="code">define( 'INFINITY23_TRUST_PROXY_HEADERS', true );</span> to wp-config.php so the plugin can see the visitor is already on HTTPS.
								<br /><br />
								<strong>Standard</strong> only uses WordPress's own built in check, which is enough for most regular hosting but may cause a redirect loop behind some proxies or CDNs.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

					<div class="form-line checkbox-line">
						<input type="checkbox" id="fix_mixed_content_checkbox" name="fix_mixed_content_checkbox" value="1" <?php if( $infinity23_fix_mixed_content_checkbox == 1 ) { echo 'checked'; } ?> />
						<label for="fix_mixed_content_checkbox">Rewrite leftover http:// links in the page output to https://</label>

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								Fixes mixed-content warnings by rewriting your site's home URL and src="http://" attributes to https:// in the rendered page.
								<br /><br />
								This does not run in wp-admin, and skips XML output such as feeds and sitemaps.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

					<div class="form-line">
						<label for="force_ssl_localhost_mode">Development Environment (Localhost) Behavior: </label>
						<select id="force_ssl_localhost_mode" name="force_ssl_localhost_mode">
							<option value="exclude" <?php if( $infinity23_force_ssl_localhost_mode === 'exclude' ) { echo 'selected'; } ?>>Exclude Development Environments (Localhost) (Recommended)</option>
							<option value="force" <?php if( $infinity23_force_ssl_localhost_mode === 'force' ) { echo 'selected'; } ?>>Force On Every Server + Development Environment (Localhost)</option>
						</select>

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								<strong>Exclude Development Environments (Localhost)</strong> will not redirect to HTTPS if the site is detected as a development environment (localhost, 127.0.0.1, common local domains such as .local / .test / .localhost, or a private/internal IP address). This prevents redirect loops on local sites that do not have a valid SSL certificate installed.
								<br /><br />
								<strong>Force On Every Server</strong> applies the HTTPS redirect everywhere, including development environments (localhost) - only use this if that environment has a valid SSL certificate.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--form-line-->

				</div><!--infinity23-toggle-fields-->

				<?php } // if( $infinity23_ssl_certificate_detected ) { ?>

			</div><!--section-card-->

			<?php infinity23_menu_visibility_field( 'ssl_and_security_hide_menu', $infinity23_ssl_and_security_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_ssl_and_security_action', 'infinity23_ssl_and_security_nonce' ); ?>

			<input type="submit" name="submit_ssl_and_security" class='submit-btn' value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_ssl_and_security_reset_action', 'infinity23_ssl_and_security_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
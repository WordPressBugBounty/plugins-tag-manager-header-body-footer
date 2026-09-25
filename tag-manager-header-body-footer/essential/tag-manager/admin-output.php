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

	// ----------------------------------------
	// reset all tag manager settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_tag_manager_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_tag_manager_reset_nonce'] ) ), 'infinity23_tag_manager_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_tag_manager_reset_nonce'] ) ) {

	// ----------------------------------------
	// code toggle activation form submit
	// ----------------------------------------

	if( isset( $_POST['infinity23_tag_manager_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_tag_manager_activate_nonce'] ) ), 'infinity23_tag_manager_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'tag_manager', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "Tag Manager was activated successfully";
			} else {
				infinity23_module_deactivate_redirect( $infinity23_module_display_name );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_tag_manager_activate_nonce'] ) ) {

	// ----------------------------------------
	// Submit the main form with data
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_tag_manager_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_tag_manager_nonce'] ) ), 'infinity23_tag_manager_action' ) && current_user_can( 'manage_options' ) && current_user_can( 'unfiltered_html' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			// ----------------------------------------
			// collecting repeating tag rows
			// these go into data1-data5 instead of the main JSON:
			// data1=head_tags, data2=body_tags, data3=footer_tags, data4=custom_lazy_load_js, data5=storage_notes
			// ----------------------------------------

			$infinity23_head_tags = array();

			if( isset( $_POST['head_tags'] ) && is_array( $_POST['head_tags'] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is wp_unslash()'d below; raw HTML/JS is intentional here and gated by current_user_can('unfiltered_html') above
				foreach( $_POST['head_tags'] as $infinity23_single_tag ) {
					if( is_array( $infinity23_single_tag ) ) { continue; }
					$infinity23_single_tag = trim( wp_unslash( $infinity23_single_tag ) );
					if( !empty( $infinity23_single_tag ) ) {
						$infinity23_head_tags[] = $infinity23_single_tag;
					} // if( !empty( $infinity23_single_tag ) ) {
				} // foreach( $_POST['head_tags'] as $infinity23_single_tag ) {
			} // if( isset( $_POST['head_tags'] ) && is_array( $_POST['head_tags'] ) ) {

			$infinity23_body_tags = array();

			if( isset( $_POST['body_tags'] ) && is_array( $_POST['body_tags'] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is wp_unslash()'d below; raw HTML/JS is intentional here and gated by current_user_can('unfiltered_html') above
				foreach( $_POST['body_tags'] as $infinity23_single_tag ) {
					if( is_array( $infinity23_single_tag ) ) { continue; }
					$infinity23_single_tag = trim( wp_unslash( $infinity23_single_tag ) );
					if( !empty( $infinity23_single_tag ) ) {
						$infinity23_body_tags[] = $infinity23_single_tag;
					} // if( !empty( $infinity23_single_tag ) ) {
				} // foreach( $_POST['body_tags'] as $infinity23_single_tag ) {
			} // if( isset( $_POST['body_tags'] ) && is_array( $_POST['body_tags'] ) ) {

			$infinity23_footer_tags = array();

			if( isset( $_POST['footer_tags'] ) && is_array( $_POST['footer_tags'] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is wp_unslash()'d below; raw HTML/JS is intentional here and gated by current_user_can('unfiltered_html') above
				foreach( $_POST['footer_tags'] as $infinity23_single_tag ) {
					if( is_array( $infinity23_single_tag ) ) { continue; }
					$infinity23_single_tag = trim( wp_unslash( $infinity23_single_tag ) );
					if( !empty( $infinity23_single_tag ) ) {
						$infinity23_footer_tags[] = $infinity23_single_tag;
					} // if( !empty( $infinity23_single_tag ) ) {
				} // foreach( $_POST['footer_tags'] as $infinity23_single_tag ) {
			} // if( isset( $_POST['footer_tags'] ) && is_array( $_POST['footer_tags'] ) ) {

			$infinity23_custom_lazy_load_js = array();

			if( isset( $_POST['custom_lazy_load_js'] ) && is_array( $_POST['custom_lazy_load_js'] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is wp_unslash()'d below; raw HTML/JS is intentional here and gated by current_user_can('unfiltered_html') above
				foreach( $_POST['custom_lazy_load_js'] as $infinity23_single_tag ) {
					if( is_array( $infinity23_single_tag ) ) { continue; }
					$infinity23_single_tag = trim( wp_unslash( $infinity23_single_tag ) );
					if( !empty( $infinity23_single_tag ) ) {
						$infinity23_custom_lazy_load_js[] = $infinity23_single_tag;
					} // if( !empty( $infinity23_single_tag ) ) {
				} // foreach( $_POST['custom_lazy_load_js'] as $infinity23_single_tag ) {
			} // if( isset( $_POST['custom_lazy_load_js'] ) && is_array( $_POST['custom_lazy_load_js'] ) ) {

			// ----------------------------------------
			// collecting the rest of the fields
			// ----------------------------------------

			$infinity23_storage_notes = array();

			if( isset( $_POST['storage_notes'] ) && is_array( $_POST['storage_notes'] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each element is wp_unslash()'d below; raw HTML/JS is intentional here and gated by current_user_can('unfiltered_html') above
				foreach( $_POST['storage_notes'] as $infinity23_single_tag ) {
					if( is_array( $infinity23_single_tag ) ) { continue; }
					$infinity23_single_tag = trim( wp_unslash( $infinity23_single_tag ) );
					if( !empty( $infinity23_single_tag ) ) {
						$infinity23_storage_notes[] = $infinity23_single_tag;
					} // if( !empty( $infinity23_single_tag ) ) {
				} // foreach( $_POST['storage_notes'] as $infinity23_single_tag ) {
			} // if( isset( $_POST['storage_notes'] ) && is_array( $_POST['storage_notes'] ) ) {
			$infinity23_data_array['google_analytics_id'] = infinity23_pro_field_sanitize( 'google_analytics_id', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['facebook_pixel_id'] = infinity23_pro_field_sanitize( 'facebook_pixel_id', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['google_tag_manager_id'] = infinity23_pro_field_sanitize( 'google_tag_manager_id', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['yandex_metrika_id'] = infinity23_pro_field_sanitize( 'yandex_metrika_id', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['lazy_load_time'] = infinity23_pro_field_sanitize( 'lazy_load_time', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['remove_custom_lazy_load_js_on_elementor'] = infinity23_pro_field_sanitize( 'remove_custom_lazy_load_js_on_elementor', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['exclude_users'] = infinity23_pro_field_sanitize( 'exclude_users', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['exclude_ids'] = infinity23_pro_field_sanitize( 'exclude_ids', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['exclude_option'] = infinity23_post_sanitize('exclude_ids_option', 'text');

			if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {
				$infinity23_data_array['exclude_option'] = 'exclude';
			} // if( !in_array( $infinity23_data_array['exclude_option'], array( 'none', 'exclude', 'include' ), true ) ) {

			$infinity23_data_array['wp_body_open'] = infinity23_pro_field_sanitize( 'wp_body_open', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['adjustable_textarea_height'] = infinity23_pro_field_sanitize( 'adjustable_textarea_height', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['tag_manager_hide_menu'] = infinity23_pro_field_sanitize( 'tag_manager_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			infinity23_update_option(
				$infinity23_settings_array_db_name,
				infinity23_convert_array_to_json($infinity23_data_array),
				infinity23_convert_array_to_json($infinity23_head_tags),
				infinity23_convert_array_to_json($infinity23_body_tags),
				infinity23_convert_array_to_json($infinity23_footer_tags),
				infinity23_convert_array_to_json($infinity23_custom_lazy_load_js),
				infinity23_convert_array_to_json($infinity23_storage_notes)
			);

			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-tag-manager', 'saved' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect or your user is not allowed to save raw HTML and JavaScript code (unfiltered_html capability is required)" );
		} // if( wp_verify_nonce( $_POST['infinity23_tag_manager_nonce'], 'infinity23_tag_manager_action' ) ) {

	} // elseif( isset( $_POST['infinity23_tag_manager_nonce'] ) ) {

	// =================================================================================================================
	// load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	// head_tags, body_tags, footer_tags, custom_lazy_load_js and storage_notes come from data1-data5
	// instead of the main JSON, unless a failed submit already put them in scope above
	if( !isset( $infinity23_head_tags ) ) { $infinity23_head_tags = infinity23_get_option_data_array( $infinity23_settings_array_db_name, 'data1' ); }
	if( !isset( $infinity23_body_tags ) ) { $infinity23_body_tags = infinity23_get_option_data_array( $infinity23_settings_array_db_name, 'data2' ); }
	if( !isset( $infinity23_footer_tags ) ) { $infinity23_footer_tags = infinity23_get_option_data_array( $infinity23_settings_array_db_name, 'data3' ); }
	if( !isset( $infinity23_custom_lazy_load_js ) ) { $infinity23_custom_lazy_load_js = infinity23_get_option_data_array( $infinity23_settings_array_db_name, 'data4' ); }
	if( !isset( $infinity23_storage_notes ) ) { $infinity23_storage_notes = infinity23_get_option_data_array( $infinity23_settings_array_db_name, 'data5' ); }

	$infinity23_remove_custom_lazy_load_js_on_elementor = infinity23_pro_get_data( 'remove_custom_lazy_load_js_on_elementor', $infinity23_data_array, $infinity23_defaults );
	$infinity23_google_analytics_id = infinity23_pro_get_data( 'google_analytics_id', $infinity23_data_array, $infinity23_defaults );
	$infinity23_yandex_metrika_id = infinity23_pro_get_data( 'yandex_metrika_id', $infinity23_data_array, $infinity23_defaults );
	$infinity23_facebook_pixel_id = infinity23_pro_get_data( 'facebook_pixel_id', $infinity23_data_array, $infinity23_defaults );
	$infinity23_google_tag_manager_id = infinity23_pro_get_data( 'google_tag_manager_id', $infinity23_data_array, $infinity23_defaults );
	$infinity23_lazy_load_time = infinity23_pro_get_data( 'lazy_load_time', $infinity23_data_array, $infinity23_defaults );
	$infinity23_wp_body_open = infinity23_pro_get_data( 'wp_body_open', $infinity23_data_array, $infinity23_defaults );
	$infinity23_adjustable_textarea_height = intval( infinity23_pro_get_data( 'adjustable_textarea_height', $infinity23_data_array, $infinity23_defaults ) );

	$infinity23_tagrow_textarea_class = 'infinity23-tagrow-textarea infinity23-line-numbered-textarea';

	if( $infinity23_adjustable_textarea_height == 1 ) {
		$infinity23_tagrow_textarea_class .= ' infinity23-textarea-adjustable';
	} // if( $infinity23_adjustable_textarea_height == 1 ) {

	$infinity23_exclude_users = infinity23_pro_get_data( 'exclude_users', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_ids = infinity23_pro_get_data( 'exclude_ids', $infinity23_data_array, $infinity23_defaults );
	$infinity23_exclude_option = infinity23_pro_get_data( 'exclude_option', $infinity23_data_array, $infinity23_defaults );
	$infinity23_tag_manager_hide_menu = intval( infinity23_pro_get_data( 'tag_manager_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

	if( empty( $infinity23_head_tags ) ) { $infinity23_head_tags = array( '' ); }
	if( empty( $infinity23_body_tags ) ) { $infinity23_body_tags = array( '' ); }
	if( empty( $infinity23_footer_tags ) ) { $infinity23_footer_tags = array( '' ); }
	if( empty( $infinity23_custom_lazy_load_js ) ) { $infinity23_custom_lazy_load_js = array( '' ); }

	if( !is_array( $infinity23_storage_notes ) ) { $infinity23_storage_notes = array( $infinity23_storage_notes ); }
	if( empty( $infinity23_storage_notes ) ) { $infinity23_storage_notes = array( '' ); }

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp tag-manager-warp">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>Tag Manager Settings</h1>
		<p>Below you will be able to insert head, body and footer codes for tracking platforms like Google Analytics, Facebook Pixel and Google Tag Manager:</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_tag_manager_activate_action', 'infinity23_tag_manager_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Tag Manager</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

		<div class="tag-manager-columns">

			<div class="tm-col-tags">

			<div class="section-card" data-card-default="open">

				<h2>Tag Codes:</h2>

				<h3>Insert Code <span class="infinity23-tagrow-title-mark">Inside &lt;head&gt;</span> Tag</h3>
				<p class="infinity23-tagrow-desc">The code will be displayed between the opening &lt;head&gt; and the closing &lt;/head&gt; tags.</p>

				<div id="infinity23-tagrow-head-wrap" class="infinity23-tagrow-wrap">
					<?php foreach( $infinity23_head_tags as $infinity23_head_tag_item ) { ?>
						<div class="infinity23-tagrow-item">
							<div class="infinity23-line-numbered-wrap">
								<div class="infinity23-line-numbers"><span>1</span></div>
								<textarea class="<?php echo esc_attr( $infinity23_tagrow_textarea_class ); ?>" name="head_tags[]"><?php echo esc_textarea( $infinity23_head_tag_item ); ?></textarea>
							</div><!--infinity23-line-numbered-wrap-->
							<button type="button" class="infinity23-tagrow-remove"><img src="<?php echo esc_url( infinity23_plugin_url( 'images/plugin-icons/remove-icon.svg' ) ); ?>" alt="Remove" title="Remove Code" /></button>
						</div><!--infinity23-tagrow-item-->
					<?php } // foreach( $infinity23_head_tags as $infinity23_head_tag_item ) { ?>
				</div><!--infinity23-tagrow-head-wrap-->

				<button type="button" class="infinity23-tagrow-add" data-target="infinity23-tagrow-head-wrap">+ Add Another Head Tag</button>

				<h3>Insert tags <span class="infinity23-tagrow-title-mark">after &lt;body&gt;</span> tag</h3>
				<p class="infinity23-tagrow-desc">The code will be displayed below the opening &lt;body&gt; tag.</p>

				<div id="infinity23-tagrow-body-wrap" class="infinity23-tagrow-wrap">
					<?php foreach( $infinity23_body_tags as $infinity23_body_tag_item ) { ?>
						<div class="infinity23-tagrow-item">
							<div class="infinity23-line-numbered-wrap">
								<div class="infinity23-line-numbers"><span>1</span></div>
								<textarea class="<?php echo esc_attr( $infinity23_tagrow_textarea_class ); ?>" name="body_tags[]"><?php echo esc_textarea( $infinity23_body_tag_item ); ?></textarea>
							</div><!--infinity23-line-numbered-wrap-->
							<button type="button" class="infinity23-tagrow-remove"><img src="<?php echo esc_url( infinity23_plugin_url( 'images/plugin-icons/remove-icon.svg' ) ); ?>" alt="Remove" title="Remove Code" /></button>
						</div><!--infinity23-tagrow-item-->
					<?php } // foreach( $infinity23_body_tags as $infinity23_body_tag_item ) { ?>
				</div><!--infinity23-tagrow-body-wrap-->

				<button type="button" class="infinity23-tagrow-add" data-target="infinity23-tagrow-body-wrap">+ Add Another After Body Tag</button>

				<h3>Insert footer tags code <span class="infinity23-tagrow-title-mark">above &lt;/body&gt;</span> tag</h3>
				<p class="infinity23-tagrow-desc">The code will be displayed right above the end &lt;/body&gt; tag.</p>

				<div id="infinity23-tagrow-footer-wrap" class="infinity23-tagrow-wrap">
					<?php foreach( $infinity23_footer_tags as $infinity23_footer_tag_item ) { ?>
						<div class="infinity23-tagrow-item">
							<div class="infinity23-line-numbered-wrap">
								<div class="infinity23-line-numbers"><span>1</span></div>
								<textarea class="<?php echo esc_attr( $infinity23_tagrow_textarea_class ); ?>" name="footer_tags[]"><?php echo esc_textarea( $infinity23_footer_tag_item ); ?></textarea>
							</div><!--infinity23-line-numbered-wrap-->
							<button type="button" class="infinity23-tagrow-remove"><img src="<?php echo esc_url( infinity23_plugin_url( 'images/plugin-icons/remove-icon.svg' ) ); ?>" alt="Remove" title="Remove Code" /></button>
						</div><!--infinity23-tagrow-item-->
					<?php } // foreach( $infinity23_footer_tags as $infinity23_footer_tag_item ) { ?>
				</div><!--infinity23-tagrow-footer-wrap-->

				<button type="button" class="infinity23-tagrow-add" data-target="infinity23-tagrow-footer-wrap">+ Add Another Footer Tag</button>

			</div><!--section-card-->

			</div><!--tm-col-tags-->

			<div class="tm-col-lazy">

			<div class="section-card" data-card-default="open">

				<h2>Lazy Loading For Analytics:</h2>

				<div class="form-line infinity23-lazy-line">
					<div class="backtest-label-with-help">
						<label for="lazy_load_time">Load after X milliseconds <small>(on no activity)</small>: </label>

						<div class="backtest-field-help-icon">
							<div class="data-help-content">
								Lazy loading only starts loading the code once the user scrolls the page or makes some kind of movement with the mouse or keyboard.
								<br /><br />
								When there is no movement, the code will load automatically after the amount of time you set here.
								<br /><br />
								The value is set in milliseconds, for example <span class="code">1000</span> equals 1 second and <span class="code">5000</span> equals 5 seconds.
								<br /><br />
								If the lazy loading still affects your site speed, try making this number bigger - it will improve your page speed score.
							</div>
							?
						</div><!--backtest-field-help-icon-->
					</div><!--backtest-label-with-help-->

					<input type="text" id="lazy_load_time" class="short-input" name="lazy_load_time" value="<?php echo esc_attr( $infinity23_lazy_load_time ); ?>" />
					<small>Milliseconds (5000 = 5 seconds)</small>
				</div><!--form-line-->

				<h3>Analytics & Pixel IDs:</h3>

				<p>Add lazy load to analytics tools with the tracking ID below.

					<span class="backtest-field-help-icon">
						<span class="data-help-content">
							Lazy load delays the tracking code so it doesn't load with the page. It waits until the visitor scrolls or moves the mouse or keyboard.
							<br /><br />
							This is used mostly for improving page speed, since the tracking scripts stay out of the initial page load.
						</span>
						?
					</span><!--backtest-field-help-icon-->
				</p>

				<div class="form-line infinity23-lazy-line">
					<label for="google_analytics_id">Google Analytics ID: </label>
					<input type="text" id="google_analytics_id" class="medium-input" name="google_analytics_id" placeholder="UA-100000000-2" value="<?php echo esc_attr( $infinity23_google_analytics_id ); ?>" />
				</div><!--form-line-->

				<div class="form-line infinity23-lazy-line">
					<label for="facebook_pixel_id">Facebook Pixel ID: </label>
					<input type="text" id="facebook_pixel_id" class="medium-input" name="facebook_pixel_id" placeholder="1158434421232423" value="<?php echo esc_attr( $infinity23_facebook_pixel_id ); ?>" />
				</div><!--form-line-->

				<div class="form-line infinity23-lazy-line">
					<label for="google_tag_manager_id">Google Tag Manager ID: </label>
					<input type="text" id="google_tag_manager_id" class="medium-input" name="google_tag_manager_id" placeholder="GTM-SSSSSSS" value="<?php echo esc_attr( $infinity23_google_tag_manager_id ); ?>" />
				</div><!--form-line-->

				<div class="form-line infinity23-lazy-line">
					<label for="yandex_metrika_id">Yandex Metrika ID: </label>
					<input type="text" id="yandex_metrika_id" class="medium-input" name="yandex_metrika_id" placeholder="00000000" value="<?php echo esc_attr( $infinity23_yandex_metrika_id ); ?>" />
					<br /><br />
					<small>Yandex Metrika loads clickmap, trackLinks, accurateTrackBounce and webvisor set as true. If your Yandex code uses different settings this might not match.</small>
				</div><!--form-line-->

				<h3>Custom Lazy Load JS:</h3>
				<p class="infinity23-warning-text">Warning: don't insert &lt;script&gt;&lt;/script&gt; tags here</p>

				<div id="infinity23-tagrow-lazyjs-wrap" class="infinity23-tagrow-wrap">
					<?php foreach( $infinity23_custom_lazy_load_js as $infinity23_lazy_js_item ) { ?>
						<div class="infinity23-tagrow-item">
							<div class="infinity23-line-numbered-wrap">
								<div class="infinity23-line-numbers"><span>1</span></div>
								<textarea class="<?php echo esc_attr( $infinity23_tagrow_textarea_class ); ?>" name="custom_lazy_load_js[]"><?php echo esc_textarea( $infinity23_lazy_js_item ); ?></textarea>
							</div><!--infinity23-line-numbered-wrap-->
							<button type="button" class="infinity23-tagrow-remove"><img src="<?php echo esc_url( infinity23_plugin_url( 'images/plugin-icons/remove-icon.svg' ) ); ?>" alt="Remove" title="Remove Code" /></button>
						</div><!--infinity23-tagrow-item-->
					<?php } // foreach( $infinity23_custom_lazy_load_js as $infinity23_lazy_js_item ) { ?>
				</div><!--infinity23-tagrow-lazyjs-wrap-->

				<button type="button" class="infinity23-tagrow-add" data-target="infinity23-tagrow-lazyjs-wrap">+ Add Another Lazyload Script</button>

				<?php $infinity23_elementor_active = infinity23_is_elementor_active(); ?>

				<h3 <?php if( !$infinity23_elementor_active ) { echo 'style="display: none;"'; } ?>>Elementor Adjustments:</h3>

				<div class="form-line checkbox-line" <?php if( !$infinity23_elementor_active ) { echo 'style="display: none;"'; } ?>>
					<input type="checkbox" id="remove_custom_lazy_load_js_on_elementor" name="remove_custom_lazy_load_js_on_elementor" value="1" <?php if( $infinity23_remove_custom_lazy_load_js_on_elementor == 1 ) { echo 'checked'; } ?> />
					<label for="remove_custom_lazy_load_js_on_elementor">Don't load custom lazy code in the Elementor editor <small>(lazy code on elementor sometimes cause problems)</small></label>
				</div><!--form-line-->

			</div><!--section-card-->

			</div><!--tm-col-lazy-->

		</div><!--tag-manager-columns-->

			<div class="section-card storage-notes-card">

				<h2>Storage Notes <mark><small>(won't effect the site)</small></mark></h2>

				<p>You can insert here tags you don't need anymore and save them for later use.</p>

				<div id="infinity23-tagrow-notes-wrap" class="infinity23-tagrow-wrap">
					<?php foreach( $infinity23_storage_notes as $infinity23_storage_note_item ) { ?>
						<div class="infinity23-tagrow-item">
							<div class="infinity23-line-numbered-wrap">
								<div class="infinity23-line-numbers"><span>1</span></div>
								<textarea class="<?php echo esc_attr( $infinity23_tagrow_textarea_class ); ?>" name="storage_notes[]"><?php echo esc_textarea( $infinity23_storage_note_item ); ?></textarea>
							</div><!--infinity23-line-numbered-wrap-->
							<button type="button" class="infinity23-tagrow-remove"><img src="<?php echo esc_url( infinity23_plugin_url( 'images/plugin-icons/remove-icon.svg' ) ); ?>" alt="Remove" title="Remove Note" /></button>
						</div><!--infinity23-tagrow-item-->
					<?php } // foreach( $infinity23_storage_notes as $infinity23_storage_note_item ) { ?>
				</div><!--infinity23-tagrow-notes-wrap-->

				<button type="button" class="infinity23-tagrow-add" data-target="infinity23-tagrow-notes-wrap">+ Add Another Note</button>

			</div><!--section-card-->

			<div class="section-card" data-card-default="open">

				<h2>Exclude Code Rules:</h2>

				<h3>Exclude Code By User Role:</h3>

				<div class="form-line">
					<label for="exclude_users">Exclude Code For Users: </label>
					<select name="exclude_users" id="exclude_users">
						<option value="none" <?php if( $infinity23_exclude_users == 'none' ) { echo 'selected'; } ?>>Not Active (Default)</option>
						<option value="exclude_admin" <?php if( $infinity23_exclude_users == 'exclude_admin' ) { echo 'selected'; } ?>>Exclude Admins</option>
						<option value="exclude_all_users" <?php if( $infinity23_exclude_users == 'exclude_all_users' ) { echo 'selected'; } ?>>Exclude All Logged In Users</option>
					</select>
				</div><!--form-line-->

				<h3>Include / Exclude Pages By ID:</h3>

				<?php infinity23_exclude_ids_field( $infinity23_exclude_ids, $infinity23_exclude_option ); ?>

			</div><!--section-card-->

			<div class="section-card" data-card-default="open">

				<h2>Advanced Settings:</h2>

				<div class="form-line">
					<label for="wp_body_open">Loading Method For The Code After &lt;body&gt; Tag: </label>
					<select name="wp_body_open" id="wp_body_open">
						<option value="wp_body_open" <?php if( $infinity23_wp_body_open == 'wp_body_open' ) { echo 'selected'; } ?>>wp_body_open Hook (Recommended)</option>
						<option value="footer_fallback" <?php if( $infinity23_wp_body_open == 'footer_fallback' ) { echo 'selected'; } ?>>JavaScript Insertion (Not Recommended)</option>
					</select>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							This option controls the method used to insert the code after the opening &lt;body&gt; tag. Both methods place the code in the exact same spot - only the way it gets there is different.
							<br /><br />
							<span class="code">Theme Hook</span> uses the <span class="code">wp_body_open</span> action. Most themes support it, and when they do this is the best option to use.
							<br /><br />
							<span class="code">Auto Insert</span> scans the page output and inserts the code after the &lt;body&gt; tag on its own, without needing the wp_body_open action. Your theme still has to call wp_head and wp_footer, which almost every theme does. Use this only if your theme doesn't support the action, since it uses slightly more memory.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<h3>Tag Code Boxes:</h3>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="adjustable_textarea_height" name="adjustable_textarea_height" value="1" <?php if( $infinity23_adjustable_textarea_height == 1 ) { echo 'checked'; } ?> />
										<label for="adjustable_textarea_height">Auto-expand code boxes height as you type</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							When enabled, every tag code box on this page grows taller to fit its content as you type.
							<br /><br />
							When disabled, every box keeps a fixed height and scrolls internally once the content is taller than the box.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

			</div><!--section-card-->

			<?php infinity23_menu_visibility_field( 'tag_manager_hide_menu', $infinity23_tag_manager_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_tag_manager_action', 'infinity23_tag_manager_nonce' ); ?>

			<input type="submit" name="submit_tag_manager" class='submit-btn' value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="additional-data-warp" data-card-default="open">

		<h2>Additional Data</h2>

		<h3>Manual Code Output Functions <small>(Allow to output the code on external system in the wordpress folder)</small></h3>

		<p class="additional-data-intro">Use these functions to print the tag manager codes anywhere you want - in your theme, or in an external file that doesn't go through WordPress (a static site or a custom app in the same folder or a subfolder). In an external file you must load the <strong>WordPress Core wp-load</strong> file first, otherwise the functions will not exist.</p>

		<ul>
			<li>
				<p><span class="infinity23-type-badge">PHP Function</span> <span class="highlight-text">infinity23_tag_manager_head()</span> - outputs the head section codes (Analytics, Pixel, GTM, Yandex Metrika, lazy load script and your custom head tags).</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">PHP Function</span> <span class="highlight-text">infinity23_tag_manager_below_body()</span> - outputs the codes set to load right after the opening &lt;body&gt; tag, including the GTM noscript fallback. Useful when your theme doesn't support the wp_body_open action.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">PHP Function</span> <span class="highlight-text">infinity23_tag_manager_before_closing_body()</span> - outputs the footer codes right before the closing &lt;/body&gt; tag.</p>
			</li>
		</ul>

		<button type="button" class="infinity23-code-example-btn">Code Example</button>
		<div class="infinity23-code-example-block">
			<pre><code><span class="infinity23-code-comment">// loads the WordPress Core wp-load file - change the path so it matches where wp-load.php sits on your own system</span>
<span class="infinity23-code-hl">&lt;?php require_once( '/full/path/to/wordpress/wp-load.php' ); ?&gt;</span>

&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;meta charset="utf-8" /&gt;
    &lt;title&gt;My Page&lt;/title&gt;

    <span class="infinity23-code-comment">// prints the head codes - Analytics, Pixel, GTM, Yandex Metrika, the lazy load script and your custom head tags</span>
    <span class="infinity23-code-hl">&lt;?php infinity23_tag_manager_head(); ?&gt;</span>

&lt;/head&gt;
&lt;body&gt;

    <span class="infinity23-code-comment">// prints the codes set to load right after the opening body tag, including the GTM noscript fallback</span>
    <span class="infinity23-code-hl">&lt;?php infinity23_tag_manager_below_body(); ?&gt;</span>

    &lt;h1&gt;My Page Content&lt;/h1&gt;

    <span class="infinity23-code-comment">// prints the footer codes right before the closing body tag</span>
    <span class="infinity23-code-hl">&lt;?php infinity23_tag_manager_before_closing_body(); ?&gt;</span>

&lt;/body&gt;
&lt;/html&gt;</code></pre>
		</div><!--infinity23-code-example-block-->

	</div><!--additional-data-warp-->

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_tag_manager_reset_action', 'infinity23_tag_manager_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
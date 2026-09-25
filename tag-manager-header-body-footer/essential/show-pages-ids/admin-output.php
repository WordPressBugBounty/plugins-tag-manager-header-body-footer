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
	// reset all show pages ids settings
	// ----------------------------------------

	if( isset( $_POST['infinity23_show_pages_ids_reset_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_show_pages_ids_reset_nonce'] ) ), 'infinity23_show_pages_ids_reset_action' ) && current_user_can( 'manage_options' ) ) {

			infinity23_delete_option( $infinity23_settings_array_db_name );
			$infinity23_success_message = "The data was reset successfully";
			$infinity23_data_array = array();

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_show_pages_ids_reset_nonce'] ) ) {

	// ----------------------------------------
	// code toggle activation form submit
	// ----------------------------------------

	if( isset( $_POST['infinity23_show_pages_ids_activate_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_show_pages_ids_activate_nonce'] ) ), 'infinity23_show_pages_ids_activate_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_new_activate_code = infinity23_post_sanitize('activate_code', 'intval');
			infinity23_update_option( $infinity23_activation_db_name, $infinity23_new_activate_code );
			infinity23_sync_activation_to_include_exclude( 'show_pages_ids', $infinity23_new_activate_code );

			if( $infinity23_new_activate_code == 1 ) {
				$infinity23_success_message = "The show pages ids feature was activated successfully";
			} else {
				$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-include-exclude', 'saved' => '1' ), admin_url('admin.php') );
				infinity23_safe_redirect( $infinity23_redirect_url );
			} // if( $infinity23_new_activate_code == 1 ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_show_pages_ids_activate_nonce'] ) ) {

	// ----------------------------------------
	// main settings form submit
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_show_pages_ids_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_show_pages_ids_nonce'] ) ), 'infinity23_show_pages_ids_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_data_array = array();
			$infinity23_existing_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_data_array['show_on_pages']             = infinity23_pro_field_sanitize( 'show_on_pages', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_posts']             = infinity23_pro_field_sanitize( 'show_on_posts', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_media']             = infinity23_pro_field_sanitize( 'show_on_media', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_comments']          = infinity23_pro_field_sanitize( 'show_on_comments', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_categories']        = infinity23_pro_field_sanitize( 'show_on_categories', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_tags']              = infinity23_pro_field_sanitize( 'show_on_tags', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_users']              = infinity23_pro_field_sanitize( 'show_on_users', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_custom_post_types'] = infinity23_pro_field_sanitize( 'show_on_custom_post_types', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_woocommerce']       = infinity23_pro_field_sanitize( 'show_on_woocommerce', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_admin_bar']         = infinity23_pro_field_sanitize( 'show_on_admin_bar', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_on_body_class']        = infinity23_pro_field_sanitize( 'show_on_body_class', $infinity23_existing_data_array, $infinity23_defaults );
			$infinity23_data_array['show_pages_ids_hide_menu']  = infinity23_pro_field_sanitize( 'show_pages_ids_hide_menu', $infinity23_existing_data_array, $infinity23_defaults );

			infinity23_update_option( $infinity23_settings_array_db_name, infinity23_convert_array_to_json($infinity23_data_array) );
			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-show-pages-ids', 'saved' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_show_pages_ids_nonce'] ) ) {

	// =================================================================================================================
	// Load saved values when page load
	// =================================================================================================================

	if( !isset($infinity23_data_array) ) {
		$infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option($infinity23_settings_array_db_name) );
	}

	$infinity23_activate_code = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );
	$infinity23_show_on_pages = intval( infinity23_pro_get_data( 'show_on_pages', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_posts = intval( infinity23_pro_get_data( 'show_on_posts', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_media = intval( infinity23_pro_get_data( 'show_on_media', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_comments = intval( infinity23_pro_get_data( 'show_on_comments', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_categories = intval( infinity23_pro_get_data( 'show_on_categories', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_tags = intval( infinity23_pro_get_data( 'show_on_tags', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_users = intval( infinity23_pro_get_data( 'show_on_users', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_custom_post_types = intval( infinity23_pro_get_data( 'show_on_custom_post_types', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_woocommerce = intval( infinity23_pro_get_data( 'show_on_woocommerce', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_admin_bar = intval( infinity23_pro_get_data( 'show_on_admin_bar', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_on_body_class = intval( infinity23_pro_get_data( 'show_on_body_class', $infinity23_data_array, $infinity23_defaults ) );
	$infinity23_show_pages_ids_hide_menu = intval( infinity23_pro_get_data( 'show_pages_ids_hide_menu', $infinity23_data_array, $infinity23_defaults ) );

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp show-pages-ids-warp">

<?php infinity23_show_message( $infinity23_success_message, $infinity23_error_message ); ?>

	<div class="page-header">
		<h1>Show Pages IDs Settings</h1>
		<p>Control where page, post and term IDs are displayed across your admin panel and front-end.</p>
	</div><!--page-header-->

	<form class="infinity23-activate-form" method="POST" action="">
		<?php wp_nonce_field( 'infinity23_show_pages_ids_activate_action', 'infinity23_show_pages_ids_activate_nonce' ); ?>
		<input type="hidden" id="activate_code" name="activate_code" value="<?php echo esc_attr( $infinity23_activate_code ); ?>" />

		<div class="infinity23-active-code">

			<div class="toggle-item <?php if( $infinity23_activate_code == 1 ) { echo 'toggle-active'; } ?>" data-input="activate_code">
				<div class="toggle-circle <?php if( $infinity23_activate_code == 1 ) { echo 'active'; } ?>"></div>
				<div class="toggle-info">
					<span class="toggle-title">Activate Show Pages IDs</span>
				</div><!--toggle-info-->
			</div><!--toggle-item-->

		</div><!--infinity23-active-code-->
	</form><!--infinity23-activate-form-->

	<form class="infinity23-form" method="POST" action="">
		<div class="main-form-wrap <?php if( $infinity23_activate_code != 1 ) { echo 'infinity23-form-locked'; } ?>">

			<div class="section-card" data-card-default="open">

				<h2>Where To Show ID Column:</h2>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_pages" name="show_on_pages" value="1" <?php if( $infinity23_show_on_pages == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_pages">Show ID column on Pages list</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_posts" name="show_on_posts" value="1" <?php if( $infinity23_show_on_posts == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_posts">Show ID column on Posts list</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_media" name="show_on_media" value="1" <?php if( $infinity23_show_on_media == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_media">Show ID column on Media list</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_comments" name="show_on_comments" value="1" <?php if( $infinity23_show_on_comments == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_comments">Show ID column on Comments list</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_categories" name="show_on_categories" value="1" <?php if( $infinity23_show_on_categories == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_categories">Show ID column on Categories list</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_tags" name="show_on_tags" value="1" <?php if( $infinity23_show_on_tags == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_tags">Show ID column on Tags list</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_users" name="show_on_users" value="1" <?php if( $infinity23_show_on_users == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_users">Show ID column on Users list</label>
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_custom_post_types" name="show_on_custom_post_types" value="1" <?php if( $infinity23_show_on_custom_post_types == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_custom_post_types">Show ID column on Custom Post Types lists</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Adds the ID column to any public custom post type registered by your theme or another plugin, in addition to the built in Pages and Posts lists.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_woocommerce" name="show_on_woocommerce" value="1" <?php if( $infinity23_show_on_woocommerce == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_woocommerce">Show ID column on WooCommerce Products, Categories and Tags lists</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Adds the ID column to the WooCommerce Products list, and to the Product Categories and Product Tags list tables.
							<br /><br />
							This option only has an effect when WooCommerce is installed and active.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<h3>Additional Display Options:</h3>

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_admin_bar" name="show_on_admin_bar" value="1" <?php if( $infinity23_show_on_admin_bar == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_admin_bar">Show the current page ID in the top admin bar</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Displays the ID of the page, post, category or tag currently being viewed inside the WordPress admin bar, both on the front-end and in the admin area.
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

				<div class="form-line checkbox-line">
					<input type="checkbox" id="show_on_body_class" name="show_on_body_class" value="1" <?php if( $infinity23_show_on_body_class == 1 ) { echo 'checked'; } ?> />
					<label for="show_on_body_class">Add the page ID as a CSS class on the body tag</label>

					<div class="backtest-field-help-icon">
						<div class="data-help-content">
							Adds a class to the front-end body tag depending on the content type, useful for targeting a specific page with custom CSS.
							<br /><br />
							<strong>Class Patterns:</strong>
							<ul>
								<li>Pages, Posts, Home, WooCommerce Products - <span class="code">page-id-42</span></li>
								<li>Categories and WooCommerce Product Categories - <span class="code">category-id-42</span></li>
								<li>Tags and WooCommerce Product Tags - <span class="code">tag-id-42</span></li>
							</ul>
						</div>
						?
					</div><!--backtest-field-help-icon-->
				</div><!--form-line-->

			</div><!--section-card-->

			<?php infinity23_menu_visibility_field( 'show_pages_ids_hide_menu', $infinity23_show_pages_ids_hide_menu, $infinity23_module_display_name ); ?>

			<?php wp_nonce_field( 'infinity23_show_pages_ids_action', 'infinity23_show_pages_ids_nonce' ); ?>

			<input type="submit" name="submit_show_pages_ids" class="submit-btn" value="Save Changes" />

		</div><!--main-form-wrap-->
	</form>

	<div class="additional-data-warp" data-card-default="open">

		<h2>Additional Data</h2>

		<h3>Body CSS Classes <small>(Allow to target a specific page, category or tag from your own CSS)</small></h3>

		<ul>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">page-id-42</span> - added to Pages, Posts, the static Home page and WooCommerce Products, where 42 is replaced with the actual ID.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">category-id-42</span> - added to Category pages and WooCommerce Product Category pages, where 42 is replaced with the actual ID.</p>
			</li>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">tag-id-42</span> - added to Tag pages and WooCommerce Product Tag pages, where 42 is replaced with the actual ID.</p>
			</li>
		</ul>

		<button type="button" class="infinity23-code-example-btn">Code Example</button>
		<div class="infinity23-code-example-block">
			<pre><code><span class="infinity23-code-comment">// target one specific page by its ID</span>
body.<span class="infinity23-code-hl">page-id-42</span> {
    background-color: #f5f5f5 !important;
}

<span class="infinity23-code-comment">// target one specific category by its ID</span>
body.<span class="infinity23-code-hl">category-id-42</span> {
    background-color: #f5f5f5 !important;
}

<span class="infinity23-code-comment">// target one specific tag by its ID</span>
body.<span class="infinity23-code-hl">tag-id-42</span> {
    background-color: #f5f5f5 !important;
}</code></pre>
		</div><!--infinity23-code-example-block-->

		<h3>Admin Bar CSS Class <small>(Allow to restyle the ID shown in the top admin bar)</small></h3>

		<ul>
			<li>
				<p><span class="infinity23-type-badge">CSS Class</span> <span class="highlight-text">infinity23-toolbar-page-id</span> - the class printed on the admin bar node that shows the current page, post, category or tag ID.</p>
			</li>
		</ul>

		<button type="button" class="infinity23-code-example-btn">Code Example</button>
		<div class="infinity23-code-example-block">
			<pre><code><span class="infinity23-code-comment">// change the color of the ID text shown in the admin bar</span>
#wpadminbar .<span class="infinity23-code-hl">infinity23-toolbar-page-id</span> .ab-item {
    color: #ffcc00 !important;
}</code></pre>
		</div><!--infinity23-code-example-block-->

	</div><!--additional-data-warp-->

	<div class="infinity23-reset-wrap">
		<form method="POST" action="">
			<?php wp_nonce_field( 'infinity23_show_pages_ids_reset_action', 'infinity23_show_pages_ids_reset_nonce' ); ?>
			<?php infinity23_display_last_updated_time( $infinity23_settings_array_db_name ); ?>
			<button type="button" class="infinity23-reset-btn" data-confirm="Are you sure you want to reset all Show Pages IDs settings?">Reset All <?php echo esc_html( $infinity23_module_display_name ); ?> Settings</button>
		</form>
	</div><!--infinity23-reset-wrap-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
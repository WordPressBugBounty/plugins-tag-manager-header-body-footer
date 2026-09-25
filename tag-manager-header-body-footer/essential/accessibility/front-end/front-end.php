<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Accessibility front-end output
	// ===========================================================================

	include( dirname(__FILE__) . "/../../../include/settings.php" );
	include( dirname(__FILE__) . "/../internal-settings.php" );

	// see if the data exists on the database
	$infinity23_get_data_json = infinity23_get_option($infinity23_settings_array_db_name);
	$infinity23_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	if( !empty($infinity23_code_is_active) ) {

		// convert the json to usable array
		$infinity23_accessibility_data_array = infinity23_convert_json_to_array($infinity23_get_data_json);

		// ----------------------------------------
		// loading default values for the plugin first load
		// ----------------------------------------

		include( dirname(__FILE__) . "/../default-values.php" );

		// ---------------------------------------
		// extract all fields with defaults
		// ---------------------------------------

		$infinity23_button_display_type = infinity23_pro_get_data( 'button_display_type', $infinity23_accessibility_data_array, $infinity23_defaults );

		if( !in_array( $infinity23_button_display_type, array( 'icon', 'image' ), true ) ) {
			$infinity23_button_display_type = 'icon';
		} // if( !in_array( $infinity23_button_display_type, array( 'icon', 'image' ), true ) ) {

		$infinity23_background = infinity23_pro_get_data( 'background', $infinity23_accessibility_data_array, $infinity23_defaults );

		if( empty( $infinity23_background ) ) {
			$infinity23_background = infinity23_convert_array_to_json( infinity23_parse_background_to_struct( '', $infinity23_default_background ) );
		} // if( empty( $infinity23_background ) ) {

		$infinity23_button_width = infinity23_pro_get_data( 'button_width', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_button_height = infinity23_pro_get_data( 'button_height', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_button_movement = infinity23_pro_get_data( 'button_movement', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_icon_image_url = infinity23_pro_get_data( 'icon_image_url', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_simple_image_url = infinity23_pro_get_data( 'simple_image_url', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_button_position = infinity23_pro_get_data( 'button_position', $infinity23_accessibility_data_array, $infinity23_defaults );

		if( empty( $infinity23_button_position ) ) {
			$infinity23_button_position = infinity23_convert_array_to_json( infinity23_parse_button_position_to_struct( '', $infinity23_accessibility_data_array, $infinity23_default_button_position ) );
		} // if( empty( $infinity23_button_position ) ) {

		$infinity23_accessibility_background_color = infinity23_pro_get_data( 'accessibility_background_color', $infinity23_accessibility_data_array, $infinity23_defaults );

		$infinity23_accessibility_page_url = infinity23_pro_get_data( 'accessibility_page_url', $infinity23_accessibility_data_array, $infinity23_defaults );

		$infinity23_accessibility_page_url_new_tab = infinity23_pro_get_data( 'accessibility_page_url_new_tab', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_exclude_ids = infinity23_pro_get_data( 'exclude_ids', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_exclude_option = infinity23_pro_get_data( 'exclude_option', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_exclude_admin_bar_checkbox = infinity23_pro_get_data( 'exclude_admin_bar_checkbox', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_save_user_settings_checkbox = infinity23_pro_get_data( 'save_user_settings_checkbox', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_save_user_settings_minutes = infinity23_pro_get_data( 'save_user_settings_minutes', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_border_radius = infinity23_pro_get_data( 'border_radius', $infinity23_accessibility_data_array, $infinity23_defaults );

		if( empty( $infinity23_border_radius ) ) {
			$infinity23_border_radius = infinity23_convert_array_to_json( infinity23_parse_border_radius_to_struct( '', $infinity23_default_border_radius ) );
		} // if( empty( $infinity23_border_radius ) ) {

		$infinity23_button_border = infinity23_pro_get_data( 'button_border', $infinity23_accessibility_data_array, $infinity23_defaults );

		if( empty( $infinity23_button_border ) ) {
			$infinity23_button_border = infinity23_convert_array_to_json( infinity23_parse_border_to_struct( '', $infinity23_default_button_border ) );
		} // if( empty( $infinity23_button_border ) ) {

		$infinity23_icon_width = infinity23_pro_get_data( 'icon_width', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_icon_height = infinity23_pro_get_data( 'icon_height', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_image_width = infinity23_pro_get_data( 'image_width', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_image_height = infinity23_pro_get_data( 'image_height', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_translate_title = infinity23_pro_get_data( 'translate_title', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_translate_increase_font = infinity23_pro_get_data( 'translate_increase_font', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_translate_decrease_font = infinity23_pro_get_data( 'translate_decrease_font', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_translate_light_contrast = infinity23_pro_get_data( 'translate_light_contrast', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_translate_high_contrast = infinity23_pro_get_data( 'translate_high_contrast', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_translate_highlight_links = infinity23_pro_get_data( 'translate_highlight_links', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_translate_reset = infinity23_pro_get_data( 'translate_reset', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_translate_accessibility_statement = infinity23_pro_get_data( 'translate_accessibility_statement', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_custom_css = infinity23_pro_get_data( 'custom_css', $infinity23_accessibility_data_array, $infinity23_defaults );
		$infinity23_custom_css_active = intval( infinity23_pro_get_data( 'custom_css_active', $infinity23_accessibility_data_array, $infinity23_defaults ) );

		// ===========================================================================
		// add css to wp_head
		// ===========================================================================

		add_action( 'wp_head', function() use (
			$infinity23_button_display_type, $infinity23_background, $infinity23_button_width, $infinity23_button_height, $infinity23_button_movement,
			$infinity23_icon_image_url, $infinity23_simple_image_url, $infinity23_button_position,
			$infinity23_accessibility_background_color, $infinity23_exclude_ids, $infinity23_exclude_option,
			$infinity23_icon_width, $infinity23_icon_height, $infinity23_image_width, $infinity23_image_height,
			$infinity23_border_radius, $infinity23_button_border, $infinity23_custom_css, $infinity23_custom_css_active
		) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) { return; }

			include( dirname(__FILE__) . "/style.php" );

		} ); // add_action( 'wp_head'

		// ===========================================================================
		// add html to wp_footer
		// ===========================================================================

		add_action( 'wp_footer', function() use (
			$infinity23_accessibility_page_url, $infinity23_accessibility_page_url_new_tab,
			$infinity23_exclude_ids, $infinity23_exclude_option,
			$infinity23_translate_title, $infinity23_translate_increase_font, $infinity23_translate_decrease_font,
			$infinity23_translate_light_contrast, $infinity23_translate_high_contrast, $infinity23_translate_highlight_links,
			$infinity23_translate_reset, $infinity23_translate_accessibility_statement
		) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) { return; }

			$direction_code = 'infinity23_ltr';
			if( is_rtl() ) {
				$direction_code = 'infinity23_rtl';
			} // if( is_rtl() ) {

			?>
			<div class="infinity23-accessibility no-accessibility <?php echo esc_attr( $direction_code ); ?>">
				<div class="infinity23-accessibility-warp">

					<a class="yy-button" href="#" role="button" aria-expanded="false" aria-controls="infinity23-accessibility-box"><span class="screen-reader-text"><?php echo esc_html__( 'Accessibility', 'tag-manager-header-body-footer' ); ?></span></a>

					<div class="yy-box" id="infinity23-accessibility-box">
						<p class="yy-title"><span class="rtl"></span><?php echo esc_html( $infinity23_translate_title ); ?></p>
						<a href="#" class="increase-font-size"><?php echo esc_html( $infinity23_translate_increase_font ); ?></a>
						<a href="#" class="decrease-font-size"><?php echo esc_html( $infinity23_translate_decrease_font ); ?></a>
						<a href="#" class="light-contrast-color" role="button" aria-pressed="false"><?php echo esc_html( $infinity23_translate_light_contrast ); ?></a>
						<a href="#" class="high-contrast-color" role="button" aria-pressed="false"><?php echo esc_html( $infinity23_translate_high_contrast ); ?></a>
						<a href="#" class="highlight-links" role="button" aria-pressed="false"><?php echo esc_html( $infinity23_translate_highlight_links ); ?></a>
						<a href="#" class="rest-accessibility"><span class="rtl"></span><?php echo esc_html( $infinity23_translate_reset ); ?></a>

						<?php if( !empty( $infinity23_accessibility_page_url ) ) { ?>
							<a href="<?php echo esc_url( $infinity23_accessibility_page_url ); ?>" <?php if( $infinity23_accessibility_page_url_new_tab != 0 ) { echo 'target="_blank"'; } ?> class="accessibility-notice">
								<?php echo esc_html( $infinity23_translate_accessibility_statement ); ?>
							</a>
						<?php } // if( !empty( $infinity23_accessibility_page_url ) ) { ?>

					</div><!--yy-box-->

				</div><!--infinity23-accessibility-warp-->
			</div><!--infinity23-accessibility-->
			<?php

		} ); // add_action( 'wp_footer'

		// ===========================================================================
		// enqueue js file
		// ===========================================================================

		add_action( 'wp_enqueue_scripts', function() use ( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_admin_bar_checkbox, $infinity23_save_user_settings_checkbox, $infinity23_save_user_settings_minutes ) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) { return; }

	        $infinity23_plugin_version = infinity23_get_option( 'plugin_version' );
	        $js_file_path = infinity23_plugin_url( 'essential/accessibility/front-end/infinity23-accessibility.js' ) . '?' . $infinity23_plugin_version;
			wp_enqueue_script( 'infinity23_accessibility_js', $js_file_path, array( 'jquery' ), $infinity23_plugin_version, true );

			wp_localize_script( 'infinity23_accessibility_js', 'infinity23_accessibility_vars', array(
				'exclude_admin_bar' => intval( $infinity23_exclude_admin_bar_checkbox ),
				'save_settings' => intval( $infinity23_save_user_settings_checkbox ),
				'save_minutes' => intval( $infinity23_save_user_settings_minutes ),
			) );

		} ); // add_action( 'wp_enqueue_scripts'

	} // if( !empty($infinity23_code_is_active) ) {

?>
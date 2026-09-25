<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Whatsapp button front-end output
	// ===========================================================================

	// settings.php and internal-settings.php are already loaded by loader.php
	// copy the shared variable names into module specific names so another
	// module loaded in the same scope cannot overwrite them

	$infinity23_whatsapp_button_activation_db_name = $infinity23_activation_db_name;
	$infinity23_whatsapp_button_settings_db_name = $infinity23_settings_array_db_name;

	$infinity23_whatsapp_button_is_active = intval( infinity23_get_option( $infinity23_whatsapp_button_activation_db_name, 0 ) );

	if( !empty($infinity23_whatsapp_button_is_active) ) {

		$infinity23_whatsapp_button_data_json = infinity23_get_option($infinity23_whatsapp_button_settings_db_name);

		// convert the json to usable array
		$infinity23_whatsapp_button_data_array = infinity23_convert_json_to_array($infinity23_whatsapp_button_data_json);

		// ----------------------------------------
		// loading default values for the plugin first load
		// ----------------------------------------

		include( dirname(__FILE__) . "/../default-values.php" );

		$infinity23_whatsapp_button_defaults = $infinity23_defaults;

		// ---------------------------------------
		// extract all fields with defaults
		// ---------------------------------------

		$infinity23_chat_number = infinity23_pro_get_data( 'chat_number', $infinity23_whatsapp_button_data_array, $infinity23_whatsapp_button_defaults );
		$infinity23_chat_message = infinity23_pro_get_data( 'chat_message', $infinity23_whatsapp_button_data_array, $infinity23_whatsapp_button_defaults );
		$infinity23_icon_image_url = infinity23_pro_get_data( 'icon_image_url', $infinity23_whatsapp_button_data_array, $infinity23_whatsapp_button_defaults );
		$infinity23_button_width = infinity23_pro_get_data( 'button_width', $infinity23_whatsapp_button_data_array, $infinity23_whatsapp_button_defaults );
		$infinity23_button_height = infinity23_pro_get_data( 'button_height', $infinity23_whatsapp_button_data_array, $infinity23_whatsapp_button_defaults );
		$infinity23_button_position = infinity23_pro_get_data( 'button_position', $infinity23_whatsapp_button_data_array, $infinity23_whatsapp_button_defaults );

		if( empty( $infinity23_button_position ) ) {
			$infinity23_button_position = infinity23_convert_array_to_json( infinity23_parse_button_position_to_struct( '', $infinity23_whatsapp_button_data_array, $infinity23_default_button_position ) );
		} // if( empty( $infinity23_button_position ) ) {

		$infinity23_exclude_ids = infinity23_pro_get_data( 'exclude_ids', $infinity23_whatsapp_button_data_array, $infinity23_whatsapp_button_defaults );
		$infinity23_exclude_option = infinity23_pro_get_data( 'exclude_option', $infinity23_whatsapp_button_data_array, $infinity23_whatsapp_button_defaults );
		$infinity23_open_new_tab = infinity23_pro_get_data( 'open_new_tab', $infinity23_whatsapp_button_data_array, $infinity23_whatsapp_button_defaults );
		$infinity23_custom_css = infinity23_pro_get_data( 'custom_css', $infinity23_whatsapp_button_data_array, $infinity23_whatsapp_button_defaults );
		$infinity23_custom_css_active = intval( infinity23_pro_get_data( 'custom_css_active', $infinity23_whatsapp_button_data_array, $infinity23_whatsapp_button_defaults ) );

		// ---------------------------------------
		// build whatsapp url
		// ---------------------------------------

		$infinity23_chat_output_message = '';
		if( !empty( $infinity23_chat_message ) ) {
			$infinity23_chat_output_message = '&text=' . rawurlencode( $infinity23_chat_message );
		} // if( !empty( $infinity23_chat_message ) ) {

		$infinity23_whatsapp_url = 'https://api.whatsapp.com/send?phone=' . esc_attr( $infinity23_chat_number ) . $infinity23_chat_output_message;

		// ===========================================================================
		// add css to wp_head
		// ===========================================================================

		add_action( 'wp_head', function() use (
			$infinity23_icon_image_url, $infinity23_button_width, $infinity23_button_height,
			$infinity23_button_position, $infinity23_exclude_ids, $infinity23_exclude_option,
			$infinity23_custom_css, $infinity23_custom_css_active
		) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) { return; }

			$style = '';

			$style .= '.infinity23-whatsapp-button {';
				$style .= 'display: block;';
				$style .= 'width: ' . intval( $infinity23_button_width ) . 'px;';
				$style .= 'height: ' . intval( $infinity23_button_height ) . 'px;';
				$style .= 'background: url(' . esc_url( $infinity23_icon_image_url ) . ') no-repeat center center;';
				$style .= 'background-size: ' . intval( $infinity23_button_width ) . 'px ' . intval( $infinity23_button_height ) . 'px;';
				$style .= 'text-indent: -9999px;';
			$style .= '}';

			$style .= infinity23_get_button_position_css( $infinity23_button_position, '.infinity23-whatsapp-button-wrap' );

			if( $infinity23_custom_css_active == 1 ) {
				$style .= infinity23_get_custom_css_output( $infinity23_custom_css );
			} // if( $infinity23_custom_css_active == 1 ) {

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-sanitized values
			echo '<style>' . $style . '</style>';

		} ); // add_action( 'wp_head'

		// ===========================================================================
		// add html to wp_footer
		// ===========================================================================

		add_action( 'wp_footer', function() use ( $infinity23_whatsapp_url, $infinity23_open_new_tab, $infinity23_exclude_ids, $infinity23_exclude_option ) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) { return; }

			?>
			<div class="infinity23-whatsapp-button-wrap">
				<a href="<?php echo esc_url( $infinity23_whatsapp_url ); ?>" class="infinity23-whatsapp-button" aria-label="<?php echo esc_attr__( 'Contact us on Whatsapp', 'tag-manager-header-body-footer' ); ?>" <?php if( $infinity23_open_new_tab == 1 ) { echo 'target="_blank" rel="noopener noreferrer"'; } ?>><?php echo esc_html__( 'Whatsapp', 'tag-manager-header-body-footer' ); ?></a>
			</div><!--infinity23-whatsapp-button-wrap-->
			<?php

		} ); // add_action( 'wp_footer'

	} // if( !empty($infinity23_whatsapp_button_is_active) ) {

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Mobile contact line front-end output
	// ===========================================================================

	include( dirname(__FILE__) . "/../../../include/settings.php" );
	include( dirname(__FILE__) . "/../internal-settings.php" );

	$infinity23_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	if( !empty($infinity23_code_is_active) ) {

		$infinity23_get_data_json = infinity23_get_option($infinity23_settings_array_db_name);

		$infinity23_mcl_data_array = infinity23_convert_json_to_array($infinity23_get_data_json);

		// ----------------------------------------
		// loading default values for the plugin first load
		// ----------------------------------------

		include( dirname(__FILE__) . "/../default-values.php" );

		// ---------------------------------------
		// extract all fields with defaults
		// ---------------------------------------

		$infinity23_phone_display_checkbox = infinity23_pro_get_data( 'phone_display_checkbox', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_phone_button_href = infinity23_pro_get_data( 'phone_button_href', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_phone_button_text = infinity23_pro_get_data( 'phone_button_text', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_phone_background_color = infinity23_pro_get_data( 'phone_background_color', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_phone_button_width = infinity23_pro_get_data( 'phone_button_width', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_phone_button_position = infinity23_pro_get_data( 'phone_button_position', $infinity23_mcl_data_array, $infinity23_defaults );

		$infinity23_email_display_checkbox = infinity23_pro_get_data( 'email_display_checkbox', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_email_button_href = infinity23_pro_get_data( 'email_button_href', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_custom_contact_page_link = infinity23_pro_get_data( 'custom_contact_page_link', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_email_button_text = infinity23_pro_get_data( 'email_button_text', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_email_background_color = infinity23_pro_get_data( 'email_background_color', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_email_button_width = infinity23_pro_get_data( 'email_button_width', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_email_button_position = infinity23_pro_get_data( 'email_button_position', $infinity23_mcl_data_array, $infinity23_defaults );

		$infinity23_whatsapp_display_checkbox = infinity23_pro_get_data( 'whatsapp_display_checkbox', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_whatsapp_button_href = infinity23_pro_get_data( 'whatsapp_button_href', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_whatsapp_url_message = infinity23_pro_get_data( 'whatsapp_url_message', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_whatsapp_button_text = infinity23_pro_get_data( 'whatsapp_button_text', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_whatsapp_background_color = infinity23_pro_get_data( 'whatsapp_background_color', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_whatsapp_button_width = infinity23_pro_get_data( 'whatsapp_button_width', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_whatsapp_button_position = infinity23_pro_get_data( 'whatsapp_button_position', $infinity23_mcl_data_array, $infinity23_defaults );

		$infinity23_chat_display_checkbox = infinity23_pro_get_data( 'chat_display_checkbox', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_chat_button_href = infinity23_pro_get_data( 'chat_button_href', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_chat_button_text = infinity23_pro_get_data( 'chat_button_text', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_chat_background_color = infinity23_pro_get_data( 'chat_background_color', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_chat_button_width = infinity23_pro_get_data( 'chat_button_width', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_chat_button_position = infinity23_pro_get_data( 'chat_button_position', $infinity23_mcl_data_array, $infinity23_defaults );

		$infinity23_hide_on_mobile_checkbox = infinity23_pro_get_data( 'hide_on_mobile_checkbox', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_hide_on_desktop_checkbox = infinity23_pro_get_data( 'hide_on_desktop_checkbox', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_mobile_width = infinity23_pro_get_data( 'mobile_width', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_exclude_ids                  = infinity23_pro_get_data( 'exclude_ids', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_exclude_option                = infinity23_pro_get_data( 'exclude_option', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_custom_css                    = infinity23_pro_get_data( 'custom_css', $infinity23_mcl_data_array, $infinity23_defaults );
		$infinity23_custom_css_active             = intval( infinity23_pro_get_data( 'custom_css_active', $infinity23_mcl_data_array, $infinity23_defaults ) );

		// ===========================================================================
		// add css to wp_head
		// ===========================================================================

		add_action( 'wp_head', function() use (
			$infinity23_phone_display_checkbox, $infinity23_phone_background_color, $infinity23_phone_button_width,
			$infinity23_email_display_checkbox, $infinity23_email_background_color, $infinity23_email_button_width,
			$infinity23_whatsapp_display_checkbox, $infinity23_whatsapp_background_color, $infinity23_whatsapp_button_width,
			$infinity23_chat_display_checkbox, $infinity23_chat_background_color, $infinity23_chat_button_width,
			$infinity23_hide_on_mobile_checkbox, $infinity23_hide_on_desktop_checkbox,
			$infinity23_mobile_width, $infinity23_exclude_ids, $infinity23_exclude_option,
			$infinity23_custom_css, $infinity23_custom_css_active
		) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) { return; }

			$icons_img = infinity23_plugin_url( 'images/mobile-contact-line/mobile-line.svg' );

			$style = '';

			// --- base styles (always applied) ---

			$style .= '.infinity23-mobile-line, .infinity23-mobile-line-space {';
				$style .= 'display: none;';
			$style .= '}';

			$style .= '.infinity23-mobile-line {';
				$style .= 'background: #008cd6;';
				$style .= 'text-align: center;';
				$style .= 'position: fixed;';
				$style .= 'left: 0px;';
				$style .= 'right: 0px;';
				$style .= 'bottom: 0px;';
				$style .= 'z-index: 999999999;';
				$style .= 'border-top: 3px solid #fff;';
				$style .= 'width: 100%;';
				$style .= 'height: 47px;';
			$style .= '}';

			$style .= '.infinity23-mobile-line-space {';
				$style .= 'height: 49px;';
			$style .= '}';

			$style .= '.infinity23-mobile-line a {';
				$style .= 'font-size: 19px;';
				$style .= 'line-height: 19px;';
				$style .= 'padding: 2px 0px 2px 0px;';
				$style .= 'font-weight: bold;';
				$style .= 'color: #fff !important;';
				$style .= 'font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;';
				$style .= 'display: table-cell;';
				$style .= 'vertical-align: middle;';
				$style .= 'text-decoration: none;';
			$style .= '}';

			$style .= '.infinity23-mobile-line a span.infinity23-mcl-icon[aria-hidden="true"], .infinity23-mobile-line a span.infinity23-mcl-icon {';
				$style .= 'background: url(' . esc_url( $icons_img ) . ') no-repeat;';
				$style .= 'background-position: 9px 8px;';
				$style .= 'background-size: 410px 38px;';
				$style .= 'width: 50px;';
				$style .= 'height: 47px;';
				$style .= 'display: inline-block;';
				$style .= 'margin: 0px 5px 0px 5px;';
			$style .= '}';

			$style .= '.infinity23-mobile-line a span.infinity23-mcl-text {';
				$style .= 'display: inline-block;';
				$style .= 'line-height: 0px;';
				$style .= 'margin: -34px -2px 0px 0px;';
				$style .= 'padding: 0px;';
				$style .= 'vertical-align: middle;';
			$style .= '}';

			$style .= '.infinity23-mobile-line-rtl a span.infinity23-mcl-text {';
				$style .= 'margin: -34px 0px 0px 3px;';
			$style .= '}';

			if( $infinity23_phone_display_checkbox == 1 ) {
				$style .= '.infinity23-mobile-line a.infinity23-mcl-phone {';
					$style .= 'background: ' . infinity23_css_sanitize( $infinity23_phone_background_color, 'value', '#008cd6' ) . ';';
					$style .= 'width: ' . infinity23_css_sanitize( $infinity23_phone_button_width, 'size', '50%' ) . ';';
				$style .= '}';
				$style .= '.infinity23-mobile-line a.infinity23-mcl-phone span.infinity23-mcl-icon {';
					$style .= 'width: 40px;';
					$style .= 'background-position: 3px 7px;';
				$style .= '}';
				$style .= '.infinity23-mobile-line-rtl a.infinity23-mcl-phone span.infinity23-mcl-icon {';
					$style .= 'background-position: -370px 7px;';
				$style .= '}';
			} // if( $infinity23_phone_display_checkbox == 1 ) {

			if( $infinity23_email_display_checkbox == 1 ) {
				$style .= '.infinity23-mobile-line a.infinity23-mcl-email {';
					$style .= 'background: ' . infinity23_css_sanitize( $infinity23_email_background_color, 'value', '#dca100' ) . ';';
					$style .= 'width: ' . infinity23_css_sanitize( $infinity23_email_button_width, 'size', '25%' ) . ';';
				$style .= '}';
				$style .= '.infinity23-mobile-line a.infinity23-mcl-email span.infinity23-mcl-icon {';
					$style .= 'width: 50px;';
					$style .= 'background-position: -63px 7px;';
				$style .= '}';
			} // if( $infinity23_email_display_checkbox == 1 ) {

			if( $infinity23_whatsapp_display_checkbox == 1 ) {
				$style .= '.infinity23-mobile-line a.infinity23-mcl-whatsapp {';
					$style .= 'background: ' . infinity23_css_sanitize( $infinity23_whatsapp_background_color, 'value', '#09547c' ) . ';';
					$style .= 'width: ' . infinity23_css_sanitize( $infinity23_whatsapp_button_width, 'size', '25%' ) . ';';
				$style .= '}';
				$style .= '.infinity23-mobile-line a.infinity23-mcl-whatsapp span.infinity23-mcl-icon {';
					$style .= 'width: 45px;';
					$style .= 'background-position: -148px 6px;';
				$style .= '}';
			} // if( $infinity23_whatsapp_display_checkbox == 1 ) {

			if( $infinity23_chat_display_checkbox == 1 ) {
				$style .= '.infinity23-mobile-line a.infinity23-mcl-chat {';
					$style .= 'background: ' . infinity23_css_sanitize( $infinity23_chat_background_color, 'value', '#008cd6' ) . ';';
					$style .= 'width: ' . infinity23_css_sanitize( $infinity23_chat_button_width, 'size', '25%' ) . ';';
				$style .= '}';
				$style .= '.infinity23-mobile-line a.infinity23-mcl-chat span.infinity23-mcl-icon {';
					$style .= 'width: 46px;';
					$style .= 'background-position: -226px 7px;';
				$style .= '}';
			} // if( $infinity23_chat_display_checkbox == 1 ) {

			// --- show on mobile ---

			$style .= '@media only screen and (max-width: ' . intval( $infinity23_mobile_width ) . 'px) {';

				$style .= '.infinity23-mobile-line {';
					$style .= 'display: table;';
				$style .= '}';

				$style .= '.infinity23-mobile-line-space {';
					$style .= 'display: block;';
				$style .= '}';

				if( $infinity23_hide_on_mobile_checkbox == 1 ) {
					$style .= '.infinity23-mobile-line, .infinity23-mobile-line-space {';
						$style .= 'display: none !important;';
					$style .= '}';
				} // if( $infinity23_hide_on_mobile_checkbox == 1 ) {

			$style .= '}';

			// --- show/hide on desktop ---

			if( $infinity23_hide_on_desktop_checkbox == 1 ) {
				$style .= '@media only screen and (min-width: ' . ( intval( $infinity23_mobile_width ) + 1 ) . 'px) {';
					$style .= '.infinity23-mobile-line, .infinity23-mobile-line-space {';
						$style .= 'display: none !important;';
					$style .= '}';
				$style .= '}';
			} else {
				$style .= '@media only screen and (min-width: ' . ( intval( $infinity23_mobile_width ) + 1 ) . 'px) {';
					$style .= '.infinity23-mobile-line {';
						$style .= 'display: table;';
					$style .= '}';
					$style .= '.infinity23-mobile-line-space {';
						$style .= 'display: block;';
					$style .= '}';
				$style .= '}';
			} // if( $infinity23_hide_on_desktop_checkbox == 1 ) {

			if( $infinity23_custom_css_active == 1 ) {
				$style .= infinity23_get_custom_css_output( $infinity23_custom_css );
			} // if( $infinity23_custom_css_active == 1 ) {

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-sanitized values
			echo '<style>' . $style . '</style>';

		} ); // add_action( 'wp_head'

		// ===========================================================================
		// add html to wp_footer
		// ===========================================================================

		add_action( 'wp_footer', function() use (
			$infinity23_phone_display_checkbox, $infinity23_phone_button_href, $infinity23_phone_button_text, $infinity23_phone_button_position,
			$infinity23_email_display_checkbox, $infinity23_email_button_href, $infinity23_custom_contact_page_link, $infinity23_email_button_text, $infinity23_email_button_position,
			$infinity23_whatsapp_display_checkbox, $infinity23_whatsapp_button_href, $infinity23_whatsapp_url_message, $infinity23_whatsapp_button_text, $infinity23_whatsapp_button_position,
			$infinity23_chat_display_checkbox, $infinity23_chat_button_href, $infinity23_chat_button_text, $infinity23_chat_button_position,
			$infinity23_exclude_ids, $infinity23_exclude_option
		) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) { return; }

			// ---------------------------------------
			// build each button html
			// ---------------------------------------

			$phone_output = '';
			if( $infinity23_phone_display_checkbox == 1 ) {
				$phone_label = !empty( $infinity23_phone_button_text ) ? $infinity23_phone_button_text : __( 'Call Us', 'tag-manager-header-body-footer' );
				$phone_output .= '<a class="infinity23-mcl-phone" aria-label="' . esc_attr( $phone_label ) . '" href="tel:' . esc_attr( $infinity23_phone_button_href ) . '">';
				$phone_output .= '<span class="infinity23-mcl-icon" aria-hidden="true"></span>';
				if( !empty( $infinity23_phone_button_text ) ) {
					$phone_output .= '<span class="infinity23-mcl-text">' . esc_html( $infinity23_phone_button_text ) . '</span>';
				} // if( !empty( $infinity23_phone_button_text ) ) {
				$phone_output .= '</a>';
			} // if( $infinity23_phone_display_checkbox == 1 ) {

			$email_output = '';
			if( $infinity23_email_display_checkbox == 1 ) {
				if( $infinity23_custom_contact_page_link == 1 ) {
					$email_href = esc_url( $infinity23_email_button_href );
				} else {
					$email_href = 'mailto:' . esc_attr( $infinity23_email_button_href );
				} // if( $infinity23_custom_contact_page_link == 1 ) {
				$email_label = !empty( $infinity23_email_button_text ) ? $infinity23_email_button_text : __( 'Contact Us', 'tag-manager-header-body-footer' );
				$email_output .= '<a class="infinity23-mcl-email" aria-label="' . esc_attr( $email_label ) . '" href="' . $email_href . '">';
				$email_output .= '<span class="infinity23-mcl-icon" aria-hidden="true"></span>';
				if( !empty( $infinity23_email_button_text ) ) {
					$email_output .= '<span class="infinity23-mcl-text">' . esc_html( $infinity23_email_button_text ) . '</span>';
				} // if( !empty( $infinity23_email_button_text ) ) {
				$email_output .= '</a>';
			} // if( $infinity23_email_display_checkbox == 1 ) {

			$whatsapp_output = '';
			if( $infinity23_whatsapp_display_checkbox == 1 ) {
				$whatsapp_url = 'https://api.whatsapp.com/send?phone=' . rawurlencode( preg_replace( '/[^0-9]/', '', $infinity23_whatsapp_button_href ) );
				if( !empty( $infinity23_whatsapp_url_message ) ) {
					$whatsapp_url .= '&text=' . rawurlencode( $infinity23_whatsapp_url_message );
				} // if( !empty( $infinity23_whatsapp_url_message ) ) {
				$whatsapp_label = !empty( $infinity23_whatsapp_button_text ) ? $infinity23_whatsapp_button_text : __( 'Whatsapp', 'tag-manager-header-body-footer' );
				$whatsapp_output .= '<a class="infinity23-mcl-whatsapp" aria-label="' . esc_attr( $whatsapp_label ) . '" href="' . esc_url( $whatsapp_url ) . '">';
				$whatsapp_output .= '<span class="infinity23-mcl-icon" aria-hidden="true"></span>';
				if( !empty( $infinity23_whatsapp_button_text ) ) {
					$whatsapp_output .= '<span class="infinity23-mcl-text">' . esc_html( $infinity23_whatsapp_button_text ) . '</span>';
				} // if( !empty( $infinity23_whatsapp_button_text ) ) {
				$whatsapp_output .= '</a>';
			} // if( $infinity23_whatsapp_display_checkbox == 1 ) {

			$chat_output = '';
			if( $infinity23_chat_display_checkbox == 1 ) {
				$chat_label = !empty( $infinity23_chat_button_text ) ? $infinity23_chat_button_text : __( 'Chat', 'tag-manager-header-body-footer' );
				$chat_output .= '<a class="infinity23-mcl-chat" aria-label="' . esc_attr( $chat_label ) . '" href="' . esc_url( $infinity23_chat_button_href ) . '">';
				$chat_output .= '<span class="infinity23-mcl-icon" aria-hidden="true"></span>';
				if( !empty( $infinity23_chat_button_text ) ) {
					$chat_output .= '<span class="infinity23-mcl-text">' . esc_html( $infinity23_chat_button_text ) . '</span>';
				} // if( !empty( $infinity23_chat_button_text ) ) {
				$chat_output .= '</a>';
			} // if( $infinity23_chat_display_checkbox == 1 ) {

			// ---------------------------------------
			// sort buttons by position
			// ---------------------------------------

			$buttons_array = array(
				array( 'position' => intval( $infinity23_phone_button_position ), 'output' => $phone_output ),
				array( 'position' => intval( $infinity23_email_button_position ), 'output' => $email_output ),
				array( 'position' => intval( $infinity23_whatsapp_button_position ), 'output' => $whatsapp_output ),
				array( 'position' => intval( $infinity23_chat_button_position ), 'output' => $chat_output ),
			);

			usort( $buttons_array, function( $a, $b ) {
				return $a['position'] - $b['position'];
			} );

			$rtl_open  = '';
			$rtl_close = '';

			if( is_rtl() ) {
				$rtl_open  = '<div class="infinity23-mobile-line-rtl">';
				$rtl_close = '</div><!--infinity23-mobile-line-rtl-->';
			} // if( is_rtl() ) {

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed hardcoded markup
			echo $rtl_open;
			echo '<div class="infinity23-mobile-line">';

			foreach( $buttons_array as $button ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values
				echo $button['output'];
			} // foreach( $buttons_array as $button ) {

			echo '</div><!--infinity23-mobile-line-->';
			echo '<div class="infinity23-mobile-line-space"></div>';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed hardcoded markup
			echo $rtl_close;

		}, 99999999 ); // add_action( 'wp_footer'

	} // if( !empty($infinity23_code_is_active) ) {

?>
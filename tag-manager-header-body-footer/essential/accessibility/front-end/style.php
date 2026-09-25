<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	$infinity23_icons_img = infinity23_plugin_url( 'images/accessibility/accessibility-icons.svg' );

	// ---------------------------------------
	// parse the shared button position field into a struct
	// ---------------------------------------

	$infinity23_position_struct = infinity23_parse_button_position_to_struct( $infinity23_button_position );

	$infinity23_effective_mobile_v_position = ( $infinity23_position_struct['mobile_mode'] === 'custom' ) ? $infinity23_position_struct['mobile_v_position'] : $infinity23_position_struct['v_position'];

	// ---------------------------------------
	// use the image size instead of the button size when the simple image type is selected
	// ---------------------------------------

	$infinity23_effective_button_width = ( $infinity23_button_display_type === 'image' ) ? $infinity23_image_width : $infinity23_button_width;
	$infinity23_effective_button_height = ( $infinity23_button_display_type === 'image' ) ? $infinity23_image_height : $infinity23_button_height;

	// ---------------------------------------
	// dealing with admin bar margin
	// ---------------------------------------

	$infinity23_add_css_desktop_margin = '';
	$infinity23_add_css_mobile_margin = '';

	if( is_admin_bar_showing() && ( $infinity23_button_movement === 'fixed' ) ) {

		if( $infinity23_position_struct['v_position'] === 'top' ) {
			$infinity23_add_css_desktop_margin = 'margin-top: 32px;';
		} // if( $infinity23_position_struct['v_position'] === 'top' ) {

		if( $infinity23_effective_mobile_v_position === 'top' ) {
			$infinity23_add_css_mobile_margin = 'margin-top: 46px;';
		} // if( $infinity23_effective_mobile_v_position === 'top' ) {

	} // if( is_admin_bar_showing() && ( $infinity23_button_movement === 'fixed' ) ) {

	$infinity23_accessibility_style = '';

	$infinity23_accessibility_style .= '.infinity23-accessibility {';
		$infinity23_accessibility_style .= 'position: ' . infinity23_css_sanitize( $infinity23_button_movement, 'option', 'fixed', array( 'absolute', 'fixed' ) ) . ';';
		$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['h_position'], 'option', 'left', array( 'left', 'right' ) ) . ': ' . infinity23_css_sanitize( $infinity23_position_struct['h_spacing'], 'size', '0px' ) . ';';
		$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['v_position'], 'option', 'top', array( 'top', 'bottom' ) ) . ': ' . infinity23_css_sanitize( $infinity23_position_struct['v_spacing'], 'size', '50px' ) . ';';
		$infinity23_accessibility_style .= 'z-index: ' . intval( $infinity23_position_struct['z_index_desktop'] ) . ';';
		$infinity23_accessibility_style .= $infinity23_add_css_desktop_margin;
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '@media only screen and (min-width: ' . ( intval( $infinity23_position_struct['mobile_width'] ) + 1 ) . 'px) {';
		$infinity23_accessibility_style .= '.infinity23-accessibility {';
			if( !empty( $infinity23_position_struct['hide_desktop'] ) ) {
				$infinity23_accessibility_style .= 'display: none !important;';
			} // if( !empty( $infinity23_position_struct['hide_desktop'] ) ) {
		$infinity23_accessibility_style .= '}';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility .infinity23-accessibility-warp {';
		$infinity23_accessibility_style .= 'position: relative;';
		$infinity23_accessibility_style .= 'width: ' . intval( $infinity23_effective_button_width ) . 'px;';
		$infinity23_accessibility_style .= 'height: ' . intval( $infinity23_effective_button_height ) . 'px;';
		$infinity23_accessibility_style .= 'font-family: Arial, Helvetica, sans-serif;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility .yy-button {';
		$infinity23_accessibility_style .= 'width: ' . intval( $infinity23_effective_button_width ) . 'px;';
		$infinity23_accessibility_style .= 'height: ' . intval( $infinity23_effective_button_height ) . 'px;';
		if( $infinity23_button_display_type === 'image' ) {
			$infinity23_accessibility_style .= 'background: transparent url(' . esc_url( $infinity23_simple_image_url ) . ') no-repeat center center;';
			$infinity23_accessibility_style .= 'background-size: contain;';
		} else {
				$infinity23_accessibility_style .= infinity23_get_advanced_border_radius_css( $infinity23_border_radius, '0px' );
			$infinity23_accessibility_style .= infinity23_get_advanced_border_css( $infinity23_button_border, 'none' );
			$infinity23_accessibility_background_value = infinity23_get_advanced_background_value( $infinity23_background );
			if( $infinity23_accessibility_background_value === 'transparent' ) {
				$infinity23_accessibility_style .= 'background: transparent url(' . esc_url( $infinity23_icon_image_url ) . ') no-repeat center center;';
			} else {
				$infinity23_accessibility_style .= 'background: url(' . esc_url( $infinity23_icon_image_url ) . ') no-repeat center center, ' . $infinity23_accessibility_background_value . ';';
			} // if( $infinity23_accessibility_background_value === 'transparent' ) {
			if( !empty( $infinity23_icon_width ) && !empty( $infinity23_icon_height ) ) {
				$infinity23_accessibility_style .= 'background-size: ' . intval( $infinity23_icon_width ) . 'px ' . intval( $infinity23_icon_height ) . 'px, auto;';
			} else {
				$infinity23_accessibility_style .= 'background-size: contain, auto;';
			} // if( !empty( $infinity23_icon_width ) && !empty( $infinity23_icon_height ) ) {
		} // if( $infinity23_button_display_type === 'image' ) {
		$infinity23_accessibility_style .= 'text-indent: -9999px;';
		$infinity23_accessibility_style .= 'padding: 0px;';
		$infinity23_accessibility_style .= 'margin: 0px;';
		$infinity23_accessibility_style .= 'position: absolute;';
		$infinity23_accessibility_style .= 'top: 0px;';
		$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['h_position'], 'option', 'left', array( 'left', 'right' ) ) . ': 0px;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility .yy-box {';
		$infinity23_accessibility_style .= 'position: absolute;';
		if( $infinity23_position_struct['v_position'] === 'bottom' ) {
			$infinity23_accessibility_style .= 'bottom: ' . intval( $infinity23_effective_button_height ) . 'px;';
		} else {
			$infinity23_accessibility_style .= 'top: ' . intval( $infinity23_effective_button_height ) . 'px;';
		} // if( $infinity23_position_struct['v_position'] === 'bottom' ) {
		$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['h_position'], 'option', 'left', array( 'left', 'right' ) ) . ': 0px;';
		$infinity23_accessibility_style .= 'margin: 0px;';
		$infinity23_accessibility_style .= 'width: 180px;';
		$infinity23_accessibility_style .= 'border: 1px solid #aeaeae;';
		$infinity23_accessibility_style .= 'text-align: center;';
		$infinity23_accessibility_style .= 'background: #fff;';
		$infinity23_accessibility_style .= 'display: none;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility .yy-box .yy-title {';
		$infinity23_accessibility_style .= 'font-size: 20px;';
		$infinity23_accessibility_style .= 'font-weight: bold;';
		$infinity23_accessibility_style .= 'color: #494949;';
		$infinity23_accessibility_style .= 'padding: 8px 0px;';
		$infinity23_accessibility_style .= 'margin: 0px;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility .yy-box .yy-title span {';
		$infinity23_accessibility_style .= 'width: 20px;';
		$infinity23_accessibility_style .= 'height: 20px;';
		$infinity23_accessibility_style .= 'display: inline-block;';
		$infinity23_accessibility_style .= 'padding: 0px;';
		$infinity23_accessibility_style .= 'margin: 0px 5px -2px 5px;';
		$infinity23_accessibility_style .= 'background: url(' . esc_url( $infinity23_icons_img ) . ') no-repeat 0px 50%;';
		$infinity23_accessibility_style .= 'background-size: 55px 28px;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility .yy-box a {';
		$infinity23_accessibility_style .= 'display: block;';
		$infinity23_accessibility_style .= 'padding: 10px 0px;';
		$infinity23_accessibility_style .= 'margin: 0px;';
		$infinity23_accessibility_style .= 'color: #5c5c5c;';
		$infinity23_accessibility_style .= 'font-weight: bold;';
		$infinity23_accessibility_style .= 'font-size: 14px;';
		$infinity23_accessibility_style .= 'line-height: 17px;';
		$infinity23_accessibility_style .= 'border-top: 1px solid #ededed;';
		$infinity23_accessibility_style .= 'text-decoration: none;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility .yy-box a:hover {';
		$infinity23_accessibility_style .= 'text-decoration: none;';
		$infinity23_accessibility_style .= 'background: #f2f8fa;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility .yy-button:focus-visible,';
	$infinity23_accessibility_style .= '.infinity23-accessibility .yy-box a:focus-visible {';
		$infinity23_accessibility_style .= 'outline: 3px solid #ff0000;';
		$infinity23_accessibility_style .= 'outline-offset: 2px;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility .yy-box a.yy-option-active {';
		$infinity23_accessibility_style .= 'background: #e8f4f9;';
		$infinity23_accessibility_style .= 'color: #094b6b;';
		$infinity23_accessibility_style .= 'box-shadow: inset 4px 0px 0px 0px #00addf;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility a.not-active {';
		$infinity23_accessibility_style .= 'color: #a4a4a4;';
		$infinity23_accessibility_style .= 'cursor: not-allowed;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility a.not-active:hover {';
		$infinity23_accessibility_style .= 'background: transparent;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility a.rest-accessibility {';
		$infinity23_accessibility_style .= 'color: #00addf;';
		$infinity23_accessibility_style .= 'padding-left: 4px;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility a.rest-accessibility span {';
		$infinity23_accessibility_style .= 'width: 20px;';
		$infinity23_accessibility_style .= 'height: 20px;';
		$infinity23_accessibility_style .= 'display: inline-block;';
		$infinity23_accessibility_style .= 'padding: 0px;';
		$infinity23_accessibility_style .= 'margin: 0px 0px -6px -8px;';
		$infinity23_accessibility_style .= 'background: url(' . esc_url( $infinity23_icons_img ) . ') no-repeat -35px -5px;';
		$infinity23_accessibility_style .= 'background-size: 55px 28px;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility a.accessibility-notice {';
		$infinity23_accessibility_style .= 'font-size: 13px;';
		$infinity23_accessibility_style .= 'padding: 6px 0px;';
		$infinity23_accessibility_style .= 'margin: 0px;';
		$infinity23_accessibility_style .= 'background: ' . infinity23_css_sanitize( $infinity23_accessibility_background_color, 'value', '#09547c' ) . ';';
		$infinity23_accessibility_style .= 'text-decoration: underline;';
		$infinity23_accessibility_style .= 'color: #fff;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-accessibility a.accessibility-notice:hover {';
		$infinity23_accessibility_style .= 'background: ' . infinity23_css_sanitize( $infinity23_accessibility_background_color, 'value', '#09547c' ) . ';';
		$infinity23_accessibility_style .= 'text-decoration: underline;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.accessibility-high-contrast-color {';
		$infinity23_accessibility_style .= 'background-color: #000 !important;';
		$infinity23_accessibility_style .= 'color: #fff !important;';
		$infinity23_accessibility_style .= 'border-color: #fff !important;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.accessibility-high-contrast-color:not(span) {';
		$infinity23_accessibility_style .= 'background-image: none !important;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.accessibility-light-contrast-color {';
		$infinity23_accessibility_style .= 'background-color: #fff !important;';
		$infinity23_accessibility_style .= 'color: #000 !important;';
		$infinity23_accessibility_style .= 'border-color: #000 !important;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.accessibility-light-contrast-color:not(span) {';
		$infinity23_accessibility_style .= 'background-image: none !important;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-highlight-links a:not(.no-accessibility a) {';
		$infinity23_accessibility_style .= 'border: 5px dashed blue !important;';
		$infinity23_accessibility_style .= 'display: inline-block;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23-highlight-links a:focus:not(.no-accessibility a),';
	$infinity23_accessibility_style .= '.infinity23-highlight-links input:focus:not(.no-accessibility input),';
	$infinity23_accessibility_style .= '.infinity23-highlight-links textarea:focus:not(.no-accessibility textarea),';
	$infinity23_accessibility_style .= '.infinity23-highlight-links select:focus:not(.no-accessibility select),';
	$infinity23_accessibility_style .= '.infinity23-highlight-links button:focus:not(.no-accessibility button) {';
		$infinity23_accessibility_style .= 'border: 7px solid red !important;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23_ltr .yy-box .yy-title span {';
		$infinity23_accessibility_style .= 'margin-left: -9px;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23_rtl .yy-box .yy-title span {';
		$infinity23_accessibility_style .= 'margin: 0px 3px -2px 0px;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '.infinity23_rtl a.rest-accessibility span {';
		$infinity23_accessibility_style .= 'margin: 0px 0px -6px 2px;';
	$infinity23_accessibility_style .= '}';

	$infinity23_accessibility_style .= '@media only screen and (max-width: ' . intval( $infinity23_position_struct['mobile_width'] ) . 'px) {';

		$infinity23_accessibility_style .= '.infinity23-accessibility {';
			if( $infinity23_position_struct['mobile_mode'] === 'custom' ) {
				$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['h_position'], 'option', 'left', array( 'left', 'right' ) ) . ': auto;';
				$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['v_position'], 'option', 'top', array( 'top', 'bottom' ) ) . ': auto;';
				$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['mobile_h_position'], 'option', 'left', array( 'left', 'right' ) ) . ': ' . infinity23_css_sanitize( $infinity23_position_struct['mobile_h_spacing'], 'size', '0px' ) . ';';
				$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['mobile_v_position'], 'option', 'top', array( 'top', 'bottom' ) ) . ': ' . infinity23_css_sanitize( $infinity23_position_struct['mobile_v_spacing'], 'size', '0px' ) . ';';
			} // if( $infinity23_position_struct['mobile_mode'] === 'custom' ) {
			$infinity23_accessibility_style .= $infinity23_add_css_mobile_margin;
			$infinity23_accessibility_style .= 'z-index: ' . intval( $infinity23_position_struct['z_index_desktop'] ) . ';';
			$infinity23_accessibility_style .= 'display: block;';
			if( !empty( $infinity23_position_struct['hide_mobile'] ) ) {
				$infinity23_accessibility_style .= 'display: none;';
			} // if( !empty( $infinity23_position_struct['hide_mobile'] ) ) {
		$infinity23_accessibility_style .= '}';

		if( $infinity23_position_struct['mobile_mode'] === 'custom' ) {
			$infinity23_accessibility_style .= '.infinity23-accessibility .yy-button {';
				$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['h_position'], 'option', 'left', array( 'left', 'right' ) ) . ': auto;';
				$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['mobile_h_position'], 'option', 'left', array( 'left', 'right' ) ) . ': 0px;';
			$infinity23_accessibility_style .= '}';

			$infinity23_accessibility_style .= '.infinity23-accessibility .yy-box {';
				$infinity23_accessibility_style .= 'top: auto;';
				$infinity23_accessibility_style .= 'bottom: auto;';
				$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['h_position'], 'option', 'left', array( 'left', 'right' ) ) . ': auto;';
				if( $infinity23_position_struct['mobile_v_position'] === 'bottom' ) {
					$infinity23_accessibility_style .= 'bottom: ' . intval( $infinity23_effective_button_height ) . 'px;';
				} else {
					$infinity23_accessibility_style .= 'top: ' . intval( $infinity23_effective_button_height ) . 'px;';
				} // if( $infinity23_position_struct['mobile_v_position'] === 'bottom' ) {
				$infinity23_accessibility_style .= infinity23_css_sanitize( $infinity23_position_struct['mobile_h_position'], 'option', 'left', array( 'left', 'right' ) ) . ': 0px;';
			$infinity23_accessibility_style .= '}';
		} // if( $infinity23_position_struct['mobile_mode'] === 'custom' ) {

	$infinity23_accessibility_style .= '}';

	// custom css is always appended last, so it can override every rule above it - only when enabled
	if( $infinity23_custom_css_active == 1 ) {
		$infinity23_accessibility_style .= infinity23_get_custom_css_output( $infinity23_custom_css );
	} // if( $infinity23_custom_css_active == 1 ) {

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-sanitized values
	echo '<style>' . $infinity23_accessibility_style . '</style>';

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Privacy policy cookie consent front-end output
	// ===========================================================================

	include( dirname(__FILE__) . "/../../../include/settings.php" );
	include( dirname(__FILE__) . "/../internal-settings.php" );

	$infinity23_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	if( !empty( $infinity23_code_is_active ) ) {

		$infinity23_get_data_json = infinity23_get_option( $infinity23_settings_array_db_name );
		$infinity23_privacy_data_array = infinity23_convert_json_to_array( $infinity23_get_data_json );

		// ----------------------------------------
		// loading default values for the plugin first load
		// ----------------------------------------

		include( dirname(__FILE__) . "/../default-values.php" );

		// ---------------------------------------
		// extract all fields with defaults
		// ---------------------------------------

		$infinity23_bar_background_color = infinity23_pro_get_data( 'bar_background_color', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_button_background_color = infinity23_pro_get_data( 'button_background_color', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_button_text_color = infinity23_pro_get_data( 'button_text_color', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_bar_text_color = infinity23_pro_get_data( 'bar_text_color', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_bar_position_vertical = infinity23_pro_get_data( 'bar_position_vertical', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_bar_vertical_spacing = infinity23_pro_get_data( 'bar_vertical_spacing', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_bar_position_horizontal = infinity23_pro_get_data( 'bar_position_horizontal', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_bar_horizontal_spacing = infinity23_pro_get_data( 'bar_horizontal_spacing', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_bar_width = infinity23_pro_get_data( 'bar_width', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_bar_width_unit = infinity23_pro_get_data( 'bar_width_unit', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_cookie_expiry_days = infinity23_pro_get_data( 'cookie_expiry_days', $infinity23_privacy_data_array, $infinity23_defaults );
		
		$infinity23_privacy_policy_url = infinity23_pro_get_data( 'privacy_policy_url', $infinity23_privacy_data_array, $infinity23_defaults );
		if( !isset( $infinity23_privacy_data_array['privacy_policy_url'] ) ) {
			$infinity23_privacy_policy_url = infinity23_privacy_policy_default_page_url();
		} // if( !isset( $infinity23_privacy_data_array['privacy_policy_url'] ) ) {

		$infinity23_privacy_policy_url_new_tab = infinity23_pro_get_data( 'privacy_policy_url_new_tab', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_message_text = infinity23_pro_get_data( 'message_text', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_privacy_link_text = infinity23_pro_get_data( 'privacy_link_text', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_accept_button_text = infinity23_pro_get_data( 'accept_button_text', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_hide_on_mobile_checkbox = infinity23_pro_get_data( 'hide_on_mobile_checkbox', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_hide_on_desktop_checkbox = infinity23_pro_get_data( 'hide_on_desktop_checkbox', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_mobile_mode = infinity23_pro_get_data( 'mobile_mode', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_mobile_bar_position_vertical = infinity23_pro_get_data( 'mobile_bar_position_vertical', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_mobile_bar_vertical_spacing = infinity23_pro_get_data( 'mobile_bar_vertical_spacing', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_mobile_bar_position_horizontal = infinity23_pro_get_data( 'mobile_bar_position_horizontal', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_mobile_bar_horizontal_spacing = infinity23_pro_get_data( 'mobile_bar_horizontal_spacing', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_mobile_bar_width = infinity23_pro_get_data( 'mobile_bar_width', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_mobile_bar_width_unit = infinity23_pro_get_data( 'mobile_bar_width_unit', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_mobile_breakpoint = infinity23_pro_get_data( 'mobile_breakpoint', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_z_index = infinity23_pro_get_data( 'z_index', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_exclude_ids                    = infinity23_pro_get_data( 'exclude_ids', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_exclude_option                 = infinity23_pro_get_data( 'exclude_option', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_custom_css                     = infinity23_pro_get_data( 'custom_css', $infinity23_privacy_data_array, $infinity23_defaults );
		$infinity23_custom_css_active              = intval( infinity23_pro_get_data( 'custom_css_active', $infinity23_privacy_data_array, $infinity23_defaults ) );

		// ===========================================================================
		// add css and html to wp_footer
		// ===========================================================================

		add_action( 'wp_footer', function() use (
			$infinity23_bar_background_color, $infinity23_button_background_color, $infinity23_button_text_color,
			$infinity23_bar_text_color, $infinity23_bar_position_vertical, $infinity23_bar_vertical_spacing,
			$infinity23_bar_position_horizontal, $infinity23_bar_horizontal_spacing,
			$infinity23_bar_width, $infinity23_bar_width_unit, $infinity23_cookie_expiry_days,
			$infinity23_privacy_policy_url, $infinity23_privacy_policy_url_new_tab,
			$infinity23_message_text, $infinity23_privacy_link_text, $infinity23_accept_button_text,
			$infinity23_hide_on_mobile_checkbox, $infinity23_hide_on_desktop_checkbox,
			$infinity23_mobile_mode, $infinity23_mobile_bar_position_vertical,
			$infinity23_mobile_bar_vertical_spacing, $infinity23_mobile_bar_position_horizontal,
			$infinity23_mobile_bar_horizontal_spacing, $infinity23_mobile_bar_width, $infinity23_mobile_bar_width_unit,
			$infinity23_mobile_breakpoint, $infinity23_z_index, $infinity23_exclude_ids, $infinity23_exclude_option,
			$infinity23_custom_css, $infinity23_custom_css_active
		) {

			// ---------------------------------------
			// check if bar should output on this page
			// ---------------------------------------

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) {
				return;
			} // if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) {

			// ---------------------------------------
			// stop if the visitor already accepted the cookie notice
			// ---------------------------------------

			if( isset( $_COOKIE['infinity23_privacy_accepted'] ) ) {
				return;
			} // if( isset( $_COOKIE['infinity23_privacy_accepted'] ) ) {

			// ---------------------------------------
			// build horizontal alignment css
			// ---------------------------------------

			$desktop_left = 'auto';
			$desktop_right = 'auto';

			if( $infinity23_bar_position_horizontal === 'left' ) {
				$desktop_left = infinity23_css_sanitize( $infinity23_bar_horizontal_spacing, 'size', '0px' );
				$desktop_right = 'auto';
			} else {
				$desktop_left = 'auto';
				$desktop_right = infinity23_css_sanitize( $infinity23_bar_horizontal_spacing, 'size', '0px' );
			} // if( $infinity23_bar_position_horizontal === 'left' ) {

			if( $infinity23_mobile_mode === 'custom' ) {

				$mobile_left = 'auto';
				$mobile_right = 'auto';

				if( $infinity23_mobile_bar_position_horizontal === 'left' ) {
					$mobile_left = infinity23_css_sanitize( $infinity23_mobile_bar_horizontal_spacing, 'size', '0px' );
					$mobile_right = 'auto';
				} else {
					$mobile_left = 'auto';
					$mobile_right = infinity23_css_sanitize( $infinity23_mobile_bar_horizontal_spacing, 'size', '0px' );
				} // if( $infinity23_mobile_bar_position_horizontal === 'left' ) {

				$active_mobile_vertical         = $infinity23_mobile_bar_position_vertical;
				$active_mobile_vertical_spacing = infinity23_css_sanitize( $infinity23_mobile_bar_vertical_spacing, 'size', '0px' );
				$active_mobile_width            = $infinity23_mobile_bar_width;
				$active_mobile_width_unit       = $infinity23_mobile_bar_width_unit;

			} else {

				$mobile_left = $desktop_left;
				$mobile_right = $desktop_right;
				$active_mobile_vertical         = $infinity23_bar_position_vertical;
				$active_mobile_vertical_spacing = infinity23_css_sanitize( $infinity23_bar_vertical_spacing, 'size', '0px' );
				$active_mobile_width            = $infinity23_bar_width;
				$active_mobile_width_unit       = $infinity23_bar_width_unit;

			} // if( $mobile_different_position == 1 ) {

			?>
			<div id="infinity23-privacy-bar" style="display:none;">
				<span class="infinity23-privacy-bar-text">
					<?php
					if( !empty( $infinity23_privacy_policy_url ) ) {

						$privacy_link_html = '<a href="' . esc_url( $infinity23_privacy_policy_url ) . '"' . ( $infinity23_privacy_policy_url_new_tab == 1 ? ' target="_blank"' : '' ) . '>' . esc_html( $infinity23_privacy_link_text ) . '</a>';
						echo wp_kses_post( str_replace( '{privacy-page-dont-edit}', $privacy_link_html, $infinity23_message_text ) );

					} else {

						echo wp_kses_post( str_replace( '{privacy-page-dont-edit}', '', $infinity23_message_text ) );

					} // if( !empty( $infinity23_privacy_policy_url ) ) {
					?>
				</span>
				<button class="infinity23-privacy-bar-btn" onclick="infinity23_acceptPrivacyCookie()"><?php echo esc_html( $infinity23_accept_button_text ); ?></button>
			</div><!--infinity23-privacy-bar-->

			<style>
			<?php // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every value below is sanitized via infinity23_css_sanitize()/intval() before being echoed into this <style> block ?>

				#infinity23-privacy-bar {
					position: fixed;
					<?php echo infinity23_css_sanitize( $infinity23_bar_position_vertical, 'option', 'bottom', array( 'top', 'bottom' ) ); ?>: <?php echo infinity23_css_sanitize( $infinity23_bar_vertical_spacing, 'size', '0px' ); ?>;
					left: <?php echo ( $desktop_left === 'auto' ) ? 'auto' : infinity23_css_sanitize( $desktop_left, 'size', '0px' ); ?>;
					right: <?php echo ( $desktop_right === 'auto' ) ? 'auto' : infinity23_css_sanitize( $desktop_right, 'size', '0px' ); ?>;
					width: <?php echo infinity23_css_sanitize( $infinity23_bar_width, 'size', '100' ) . infinity23_css_sanitize( $infinity23_bar_width_unit, 'option', '%', array( '%', 'px', 'em', 'rem' ) ); ?>;
					background-color: <?php echo infinity23_css_sanitize( $infinity23_bar_background_color, 'value', '#333333' ); ?>;
					color: <?php echo infinity23_css_sanitize( $infinity23_bar_text_color, 'value', '#ffffff' ); ?>;
					padding: 14px 20px;
					z-index: <?php echo intval( $infinity23_z_index ); ?>;
					box-sizing: border-box;
					text-align: center;
				}

				@media only screen and (min-width: <?php echo ( intval( $infinity23_mobile_breakpoint ) + 1 ); ?>px) {

					#infinity23-privacy-bar {
						<?php if( $infinity23_hide_on_desktop_checkbox == 1 ) { ?>
						display: none !important;
						<?php } ?>
					}

				}

				#infinity23-privacy-bar .infinity23-privacy-bar-text {
					font-size: 16px;
					line-height: 1.5;
					color: <?php echo infinity23_css_sanitize( $infinity23_bar_text_color, 'value', '#ffffff' ); ?>;
					margin: 0px 15px 0px 0px;
					display: inline-block;
				}

				#infinity23-privacy-bar .infinity23-privacy-bar-text a {
					color: <?php echo infinity23_css_sanitize( $infinity23_bar_text_color, 'value', '#ffffff' ); ?>;
					text-decoration: underline;
				}

				#infinity23-privacy-bar .infinity23-privacy-bar-btn {
					background-color: <?php echo infinity23_css_sanitize( $infinity23_button_background_color, 'value', '#007cba' ); ?>;
					color: <?php echo infinity23_css_sanitize( $infinity23_button_text_color, 'value', '#ffffff' ); ?>;
					border: none;
					padding: 8px 20px;
					margin: 0px 10px;
					cursor: pointer;
					font-size: 14px;
					font-weight: bold;
					border-radius: 3px;
					vertical-align: middle;
				}

				@media only screen and (max-width: <?php echo intval( $infinity23_mobile_breakpoint ); ?>px) {

					#infinity23-privacy-bar {
						<?php echo infinity23_css_sanitize( $active_mobile_vertical, 'option', 'bottom', array( 'top', 'bottom' ) ); ?>: <?php echo infinity23_css_sanitize( $active_mobile_vertical_spacing, 'size', '0px' ); ?>;
						<?php if( $infinity23_bar_position_vertical !== $active_mobile_vertical ) { ?>
						<?php echo infinity23_css_sanitize( $infinity23_bar_position_vertical, 'option', 'bottom', array( 'top', 'bottom' ) ); ?>: auto;
						<?php } ?>
						left: <?php echo ( $mobile_left === 'auto' ) ? 'auto' : infinity23_css_sanitize( $mobile_left, 'size', '0px' ); ?>;
						right: <?php echo ( $mobile_right === 'auto' ) ? 'auto' : infinity23_css_sanitize( $mobile_right, 'size', '0px' ); ?>;
						width: <?php echo infinity23_css_sanitize( $active_mobile_width, 'size', '100' ) . infinity23_css_sanitize( $active_mobile_width_unit, 'option', '%', array( '%', 'px', 'em', 'rem' ) ); ?>;
						<?php if( $infinity23_hide_on_mobile_checkbox == 1 ) { ?>
						display: none !important;
						<?php } ?>
					}

				}

				<?php if( $infinity23_custom_css_active == 1 ) { ?>
					<?php echo infinity23_get_custom_css_output( $infinity23_custom_css ); ?>
				<?php } // if( $infinity23_custom_css_active == 1 ) { ?>

			<?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</style>
			<?php

		} ); // add_action( 'wp_footer'

		// ===========================================================================
		// output js inline in footer
		// ===========================================================================

		add_action( 'wp_footer', function() use ( $infinity23_cookie_expiry_days, $infinity23_exclude_ids, $infinity23_exclude_option ) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) {
				return;
			} // if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) {

			if( isset( $_COOKIE['infinity23_privacy_accepted'] ) ) {
				return;
			} // if( isset( $_COOKIE['infinity23_privacy_accepted'] ) ) {

			$expiry_days = intval( $infinity23_cookie_expiry_days );

			?>
			<script>

				(function() {

					// ====================================================================================================
					// Cookie helper functions
					// ====================================================================================================

					function infinity23_setCookie( name, value, days ) {

						var expires = '';

						if( days ) {
							var date = new Date();
							date.setTime( date.getTime() + ( days * 24 * 60 * 60 * 1000 ) );
							expires = '; expires=' + date.toUTCString();
						} // if( days ) {

						document.cookie = name + '=' + ( value || '' ) + expires + '; path=/';

					} // function infinity23_setCookie( name, value, days ) {

					function infinity23_getCookie( name ) {

						var nameEQ = name + '=';
						var ca = document.cookie.split(';');

						for( var i = 0; i < ca.length; i++ ) {
							var c = ca[i];
							while( c.charAt(0) === ' ' ) {
								c = c.substring(1, c.length);
							} // while( c.charAt(0) === ' ' ) {
							if( c.indexOf(nameEQ) === 0 ) {
								return c.substring( nameEQ.length, c.length );
							} // if( c.indexOf(nameEQ) === 0 ) {
						} // for( var i = 0; i < ca.length; i++ ) {

						return null;

					} // function infinity23_getCookie( name ) {

					// ====================================================================================================
					// Update body padding based on bar height
					// ====================================================================================================

					function infinity23_updateBodyPadding() {

						var bar = document.getElementById('infinity23-privacy-bar');

						if( !bar || window.getComputedStyle( bar ).display === 'none' ) {
							document.body.style.paddingBottom = '';
							return;
						} // if( !bar || window.getComputedStyle( bar ).display === 'none' ) {

						var barHeight = bar.offsetHeight;
						var verticalPos = window.getComputedStyle( bar ).bottom;

						if( verticalPos !== 'auto' && parseInt( verticalPos ) === 0 ) {
							document.body.style.paddingBottom = barHeight + 'px';
						} else {
							document.body.style.paddingBottom = '';
						} // if( verticalPos !== 'auto' && parseInt( verticalPos ) === 0 ) {

					} // function infinity23_updateBodyPadding() {

					// ====================================================================================================
					// Show bar if cookie not set
					// ====================================================================================================

					function infinity23_showPrivacyBar() {

						var bar = document.getElementById('infinity23-privacy-bar');

						if( !bar ) {
							return;
						} // if( !bar ) {

						if( !infinity23_getCookie('infinity23_privacy_accepted') ) {
							bar.style.display = 'block';
							infinity23_updateBodyPadding();
						} // if( !infinity23_getCookie('infinity23_privacy_accepted') ) {

					} // function infinity23_showPrivacyBar() {

					if( document.readyState === 'loading' ) {
						document.addEventListener( 'DOMContentLoaded', infinity23_showPrivacyBar );
					} else {
						infinity23_showPrivacyBar();
					} // if( document.readyState === 'loading' ) {

					window.addEventListener( 'resize', function() {
						infinity23_updateBodyPadding();
					}); // window.addEventListener( 'resize'

					// ====================================================================================================
					// Accept button - set cookie and hide bar
					// ====================================================================================================

					window.infinity23_acceptPrivacyCookie = function() {

						var bar = document.getElementById('infinity23-privacy-bar');

						infinity23_setCookie( 'infinity23_privacy_accepted', 'true', <?php echo intval( $expiry_days ); ?> );

						if( bar ) {
							bar.style.display = 'none';
						} // if( bar ) {

						document.body.style.paddingBottom = '';

					}; // window.infinity23_acceptPrivacyCookie

				})(); // (function() {

			</script>
			<?php

		}, 20 ); // add_action( 'wp_footer', 20

	} // if( !empty( $infinity23_code_is_active ) ) {

?>
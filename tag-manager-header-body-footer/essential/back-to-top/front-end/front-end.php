<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Back to top front-end output
	// ===========================================================================

	include( dirname(__FILE__) . "/../../../include/settings.php" );
	include( dirname(__FILE__) . "/../internal-settings.php" );

	$infinity23_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	if( !empty($infinity23_code_is_active) ) {

		$infinity23_get_data_json = infinity23_get_option($infinity23_settings_array_db_name);

		// convert the json to usable array
		$infinity23_back_to_top_data_array = infinity23_convert_json_to_array($infinity23_get_data_json);

		// ----------------------------------------
		// loading default values for the plugin first load
		// ----------------------------------------

		include( dirname(__FILE__) . "/../default-values.php" );

		// ---------------------------------------
		// extract all fields with defaults
		// ---------------------------------------

		$infinity23_button_display_type = infinity23_pro_get_data( 'button_display_type', $infinity23_back_to_top_data_array, $infinity23_defaults );

		if( !in_array( $infinity23_button_display_type, array( 'icon', 'image' ), true ) ) {
			$infinity23_button_display_type = 'icon';
		} // if( !in_array( $infinity23_button_display_type, array( 'icon', 'image' ), true ) ) {

		$infinity23_background = infinity23_pro_get_data( 'background', $infinity23_back_to_top_data_array, $infinity23_defaults );

		if( empty( $infinity23_background ) ) {
			$infinity23_background = infinity23_convert_array_to_json( infinity23_parse_background_to_struct( '', $infinity23_default_background ) );
		} // if( empty( $infinity23_background ) ) {

		$infinity23_button_width = infinity23_pro_get_data( 'button_width', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_button_height = infinity23_pro_get_data( 'button_height', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_border_radius = infinity23_pro_get_data( 'border_radius', $infinity23_back_to_top_data_array, $infinity23_defaults );

		if( empty( $infinity23_border_radius ) ) {
			$infinity23_border_radius = infinity23_convert_array_to_json( infinity23_parse_border_radius_to_struct( '', $infinity23_default_border_radius ) );
		} // if( empty( $infinity23_border_radius ) ) {

		$infinity23_button_border = infinity23_pro_get_data( 'button_border', $infinity23_back_to_top_data_array, $infinity23_defaults );

		if( empty( $infinity23_button_border ) ) {
			$infinity23_button_border = infinity23_convert_array_to_json( infinity23_parse_border_to_struct( '', $infinity23_default_button_border ) );
		} // if( empty( $infinity23_button_border ) ) {

		$infinity23_icon_image_url = infinity23_pro_get_data( 'icon_image_url', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_simple_image_url = infinity23_pro_get_data( 'simple_image_url', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_icon_width = infinity23_pro_get_data( 'icon_width', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_icon_height = infinity23_pro_get_data( 'icon_height', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_image_width = infinity23_pro_get_data( 'image_width', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_image_height = infinity23_pro_get_data( 'image_height', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_button_visibility = infinity23_pro_get_data( 'button_visibility', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_scroll_offset = infinity23_pro_get_data( 'scroll_offset', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_mobile_scroll_offset = infinity23_pro_get_data( 'mobile_scroll_offset', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_button_position = infinity23_pro_get_data( 'button_position', $infinity23_back_to_top_data_array, $infinity23_defaults );

		if( empty( $infinity23_button_position ) ) {
			$infinity23_button_position = infinity23_convert_array_to_json( infinity23_parse_button_position_to_struct( '', $infinity23_back_to_top_data_array, $infinity23_default_button_position ) );
		} // if( empty( $infinity23_button_position ) ) {

		$infinity23_button_position_struct_for_js = infinity23_parse_button_position_to_struct( $infinity23_button_position );
		$infinity23_mobile_width = $infinity23_button_position_struct_for_js['mobile_width'];

		$infinity23_exclude_ids = infinity23_pro_get_data( 'exclude_ids', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_exclude_option = infinity23_pro_get_data( 'exclude_option', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_custom_css = infinity23_pro_get_data( 'custom_css', $infinity23_back_to_top_data_array, $infinity23_defaults );
		$infinity23_custom_css_active = intval( infinity23_pro_get_data( 'custom_css_active', $infinity23_back_to_top_data_array, $infinity23_defaults ) );

		// ===========================================================================
		// load jquery on the front-end
		// ===========================================================================

		add_action( 'wp_enqueue_scripts', function() use ( $infinity23_exclude_ids, $infinity23_exclude_option ) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) { return; }

			wp_enqueue_script( 'jquery' );

		} ); // add_action( 'wp_enqueue_scripts'

		// ===========================================================================
		// add css to wp_head
		// ===========================================================================

		add_action( 'wp_head', function() use (
			$infinity23_button_display_type, $infinity23_image_width, $infinity23_image_height,
			$infinity23_background, $infinity23_button_width, $infinity23_button_height, $infinity23_border_radius,
			$infinity23_button_border, $infinity23_icon_image_url, $infinity23_simple_image_url, $infinity23_icon_width, $infinity23_icon_height,
			$infinity23_button_position, $infinity23_exclude_ids, $infinity23_exclude_option,
			$infinity23_button_visibility,
			$infinity23_custom_css, $infinity23_custom_css_active
		) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) { return; }

			$style = '';

			$style .= '.infinity23-back-to-top-btn {';
				$style .= 'position: fixed;';
				if( $infinity23_button_display_type === 'image' ) {
					$style .= 'width: ' . intval( $infinity23_image_width ) . 'px;';
					$style .= 'height: ' . intval( $infinity23_image_height ) . 'px;';
					$style .= 'background: transparent url(' . esc_url( $infinity23_simple_image_url ) . ') no-repeat center center;';
					$style .= 'background-size: contain;';
				} else {
					$style .= 'width: ' . intval( $infinity23_button_width ) . 'px;';
					$style .= 'height: ' . intval( $infinity23_button_height ) . 'px;';
					$infinity23_back_to_top_background_value = infinity23_get_advanced_background_value( $infinity23_background );
					if( $infinity23_back_to_top_background_value === 'transparent' ) {
						$style .= 'background: transparent url(' . esc_url( $infinity23_icon_image_url ) . ') no-repeat center center;';
					} else {
						$style .= 'background: url(' . esc_url( $infinity23_icon_image_url ) . ') no-repeat center center, ' . $infinity23_back_to_top_background_value . ';';
					} // if( $infinity23_back_to_top_background_value === 'transparent' ) {
					$style .= 'background-size: ' . intval( $infinity23_icon_width ) . 'px ' . intval( $infinity23_icon_height ) . 'px, auto;';
					$style .= infinity23_get_advanced_border_radius_css( $infinity23_border_radius, '50%' );
					$style .= infinity23_get_advanced_border_css( $infinity23_button_border, 'none' );
				} // if( $infinity23_button_display_type === 'image' ) {
				if( $infinity23_button_visibility === 'always' ) {
					$style .= 'display: block;';
				} else {
					$style .= 'display: none;';
				} // if( $infinity23_button_visibility === 'always' ) {
				$style .= 'text-indent: -9999px;';
			$style .= '}';

			$style .= infinity23_get_button_position_css( $infinity23_button_position, '.infinity23-back-to-top-btn' );

			if( $infinity23_custom_css_active == 1 ) {
				$style .= infinity23_get_custom_css_output( $infinity23_custom_css );
			} // if( $infinity23_custom_css_active == 1 ) {

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-sanitized values
			echo '<style>' . $style . '</style>';

		} ); // add_action( 'wp_head'

		// ===========================================================================
		// add html to wp_footer
		// ===========================================================================

		add_action( 'wp_footer', function() use ( $infinity23_exclude_ids, $infinity23_exclude_option ) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) { return; }

			?>
			<div class="infinity23-back-to-top-wrap">
				<a href="#" class="infinity23-back-to-top-btn" aria-label="<?php echo esc_attr__( 'Back to top of page', 'tag-manager-header-body-footer' ); ?>"><?php echo esc_html__( 'Back To Top', 'tag-manager-header-body-footer' ); ?></a>
			</div><!--infinity23-back-to-top-wrap-->
			<?php

		} ); // add_action( 'wp_footer'

		// ===========================================================================
		// output js in footer
		// ===========================================================================

		add_action( 'wp_footer', function() use ( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_scroll_offset, $infinity23_mobile_scroll_offset, $infinity23_mobile_width, $infinity23_button_visibility ) {

			if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) { return; }

			if( $infinity23_button_visibility === 'always' ) { return; }

				$js_output = '';
				$js_output .= '<script>';

					$js_output .= 'jQuery(document).ready(function($) {';

						$js_output .= 'var amountScrolled = ' . intval( $infinity23_scroll_offset ) . ';';
						$js_output .= 'var mobileAmountScrolled = ' . intval( $infinity23_mobile_scroll_offset ) . ';';
						$js_output .= 'var mobileBreakPoint = ' . intval( $infinity23_mobile_width ) . ';';
						$js_output .= 'var buttonVisible = false;';
						$js_output .= "var \$backToTopBtn = $('.infinity23-back-to-top-btn');";

						$js_output .= 'function infinity23BackToTopCheck() {';

							$js_output .= 'var currentOffset = amountScrolled;';
							$js_output .= 'if( $(window).width() <= mobileBreakPoint ) {';
								$js_output .= 'currentOffset = mobileAmountScrolled;';
							$js_output .= '}';

							$js_output .= 'if( $(window).scrollTop() >= currentOffset ) {';
								$js_output .= 'if( !buttonVisible ) {';
									$js_output .= 'buttonVisible = true;';
									$js_output .= "\$backToTopBtn.stop(true, true).fadeIn('slow');";
								$js_output .= '}';
							$js_output .= '} else {';
								$js_output .= 'if( buttonVisible ) {';
									$js_output .= 'buttonVisible = false;';
									$js_output .= "\$backToTopBtn.stop(true, true).fadeOut('slow');";
								$js_output .= '}';
							$js_output .= '}';

						$js_output .= '}'; // function infinity23BackToTopCheck() {

						$js_output .= 'var scrollTicking = false;';

						$js_output .= '$(window).on("scroll", function() {';

							$js_output .= 'if( !scrollTicking ) {';
								$js_output .= 'scrollTicking = true;';
								$js_output .= 'window.requestAnimationFrame(function() {';
									$js_output .= 'infinity23BackToTopCheck();';
									$js_output .= 'scrollTicking = false;';
								$js_output .= '});';
							$js_output .= '}';

						$js_output .= '});'; // $(window).on("scroll"

						$js_output .= 'infinity23BackToTopCheck();';

						$js_output .= "$('.infinity23-back-to-top-btn').click(function() {";
							$js_output .= "$('html, body').animate({ scrollTop: 0 }, 700);";
							$js_output .= 'return false;';
						$js_output .= '});'; // $('.infinity23-back-to-top-btn').click(function() {

					$js_output .= '});'; // jQuery(document).ready(function($) {

				$js_output .= '</script>';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-sanitized values
				echo $js_output;

		} ); // add_action( 'wp_footer', function() use ( $infinity23_exclude_ids_array ) {

	} // if( !empty($infinity23_code_is_active) ) {

?>
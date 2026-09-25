<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Tag manager front-end output
	// ===========================================================================

	include( dirname(__FILE__) . "/../../../include/settings.php" );
	include( dirname(__FILE__) . "/../internal-settings.php" );

	$infinity23_code_is_active = intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) );

	if( !empty($infinity23_code_is_active) ) {

		$infinity23_get_data_json = infinity23_get_option($infinity23_settings_array_db_name);
		$infinity23_tag_manager_data_array = infinity23_convert_json_to_array($infinity23_get_data_json);

		include( dirname(__FILE__) . "/../default-values.php" );

		$infinity23_head_tags = infinity23_get_option_data_array( $infinity23_settings_array_db_name, 'data1' );
		$infinity23_body_tags = infinity23_get_option_data_array( $infinity23_settings_array_db_name, 'data2' );
		$infinity23_footer_tags = infinity23_get_option_data_array( $infinity23_settings_array_db_name, 'data3' );
		$infinity23_custom_lazy_load_js = infinity23_get_option_data_array( $infinity23_settings_array_db_name, 'data4' );
		$infinity23_remove_custom_lazy_load_js_on_elementor = intval( infinity23_pro_get_data( 'remove_custom_lazy_load_js_on_elementor', $infinity23_tag_manager_data_array, $infinity23_defaults ) );
		$infinity23_google_analytics_id = infinity23_pro_get_data( 'google_analytics_id', $infinity23_tag_manager_data_array, $infinity23_defaults );
		$infinity23_yandex_metrika_id = infinity23_pro_get_data( 'yandex_metrika_id', $infinity23_tag_manager_data_array, $infinity23_defaults );
		$infinity23_facebook_pixel_id = infinity23_pro_get_data( 'facebook_pixel_id', $infinity23_tag_manager_data_array, $infinity23_defaults );
		$infinity23_google_tag_manager_id = infinity23_pro_get_data( 'google_tag_manager_id', $infinity23_tag_manager_data_array, $infinity23_defaults );
		$infinity23_lazy_load_time = intval( infinity23_pro_get_data( 'lazy_load_time', $infinity23_tag_manager_data_array, $infinity23_defaults ) );
		$infinity23_wp_body_open = infinity23_pro_get_data( 'wp_body_open', $infinity23_tag_manager_data_array, $infinity23_defaults );
		$infinity23_exclude_users = infinity23_pro_get_data( 'exclude_users', $infinity23_tag_manager_data_array, $infinity23_defaults );
		$infinity23_exclude_ids = infinity23_pro_get_data( 'exclude_ids', $infinity23_tag_manager_data_array, $infinity23_defaults );
		$infinity23_exclude_option = infinity23_pro_get_data( 'exclude_option', $infinity23_tag_manager_data_array, $infinity23_defaults );

		// ===========================================================================
		// helper functions
		// ===========================================================================

		if( !function_exists( 'infinity23_tag_manager_should_output' ) ) {

			function infinity23_tag_manager_should_output( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) {

				if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) {
					return false;
				} // if( !infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option ) ) {

				if( function_exists('current_user_can') && current_user_can('administrator') && ( $infinity23_exclude_users === 'exclude_admin' ) ) {
					return false;
				} // if( function_exists('current_user_can') ...

				if( is_user_logged_in() && ( $infinity23_exclude_users === 'exclude_all_users' ) ) {
					return false;
				} // if( is_user_logged_in() ...

				return true;

			} // function infinity23_tag_manager_should_output( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) {

		} // if( !function_exists( 'infinity23_tag_manager_should_output' ) ) {

		// ===========================================================================
		// build the lazy load head code
		// ===========================================================================

		$infinity23_header_lazyload_code = '';

		if( !empty( $infinity23_google_analytics_id ) || !empty( $infinity23_yandex_metrika_id ) || !empty( $infinity23_facebook_pixel_id ) || !empty( $infinity23_google_tag_manager_id ) || !empty( $infinity23_custom_lazy_load_js ) ) {

			$infinity23_header_lazyload_code .= '<script>';
			$infinity23_header_lazyload_code .= 'function infinity23_tag_manager_js_lazy_load() {';

				if( !empty( $infinity23_google_analytics_id ) ) {
					$infinity23_header_lazyload_code .= "var infinity23AnalyticsTag = document.createElement('script');";
					$infinity23_header_lazyload_code .= "infinity23AnalyticsTag.src = 'https://www.googletagmanager.com/gtag/js?id=" . esc_js( $infinity23_google_analytics_id ) . "';";
					$infinity23_header_lazyload_code .= "var infinity23FirstAnalyticsTag = document.getElementsByTagName('script')[0];";
					$infinity23_header_lazyload_code .= "infinity23FirstAnalyticsTag.parentNode.insertBefore(infinity23AnalyticsTag, infinity23FirstAnalyticsTag);";
					$infinity23_header_lazyload_code .= "window.dataLayer = window.dataLayer || [];";
					$infinity23_header_lazyload_code .= "function gtag(){dataLayer.push(arguments);}";
					$infinity23_header_lazyload_code .= "gtag('js', new Date());";
					$infinity23_header_lazyload_code .= "gtag('config', '" . esc_js( $infinity23_google_analytics_id ) . "');";
				} // if( !empty( $infinity23_google_analytics_id ) ) {

				if( !empty( $infinity23_facebook_pixel_id ) ) {
					$infinity23_header_lazyload_code .= "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');";
					$infinity23_header_lazyload_code .= "fbq('init', '" . esc_js( $infinity23_facebook_pixel_id ) . "');";
					$infinity23_header_lazyload_code .= "fbq('track', 'PageView');";
				} // if( !empty( $infinity23_facebook_pixel_id ) ) {

				if( !empty( $infinity23_google_tag_manager_id ) ) {
					$infinity23_header_lazyload_code .= "window.dataLayer = window.dataLayer || [];";
					$infinity23_header_lazyload_code .= "window.dataLayer.push({'gtm.start': new Date().getTime(), event: 'gtm.js'});";
					$infinity23_header_lazyload_code .= "var infinity23GtmScript = document.createElement('script');";
					$infinity23_header_lazyload_code .= "infinity23GtmScript.type = 'text/javascript';";
					$infinity23_header_lazyload_code .= "infinity23GtmScript.async = true;";
					$infinity23_header_lazyload_code .= "infinity23GtmScript.src = 'https://www.googletagmanager.com/gtm.js?id=" . esc_js( $infinity23_google_tag_manager_id ) . "';";
					$infinity23_header_lazyload_code .= "var infinity23FirstScript = document.getElementsByTagName('script')[0];";
					$infinity23_header_lazyload_code .= "infinity23FirstScript.parentNode.insertBefore(infinity23GtmScript, infinity23FirstScript);";
				} // if( !empty( $infinity23_google_tag_manager_id ) ) {

				if( !empty( $infinity23_yandex_metrika_id ) ) {
					$infinity23_header_lazyload_code .= "(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');";
					$infinity23_header_lazyload_code .= "ym(" . intval( $infinity23_yandex_metrika_id ) . ", 'init', {clickmap:true,trackLinks:true,accurateTrackBounce:true,webvisor:true});";
				} // if( !empty( $infinity23_yandex_metrika_id ) ) {

				if( !empty( $infinity23_custom_lazy_load_js ) && is_array( $infinity23_custom_lazy_load_js ) ) {

					$infinity23_output_custom_lazy_js = 1;

					if( $infinity23_remove_custom_lazy_load_js_on_elementor == 1 ) {
						$infinity23_request_url = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
						if( strstr( $infinity23_request_url, 'elementor-preview' ) ) {
							$infinity23_output_custom_lazy_js = 0;
						} // if( strstr( $infinity23_request_url, 'elementor-preview' ) ) {
					} // if( $infinity23_remove_custom_lazy_load_js_on_elementor == 1 ) {

					if( $infinity23_output_custom_lazy_js == 1 ) {
						$infinity23_header_lazyload_code .= implode( "\n", $infinity23_custom_lazy_load_js );
					} // if( $infinity23_output_custom_lazy_js == 1 ) {

				} // if( !empty( $infinity23_custom_lazy_load_js ) ) {

				$infinity23_header_lazyload_code .= "infinity23TagManagerStop = 1;";

			$infinity23_header_lazyload_code .= '}';

			$infinity23_header_lazyload_code .= 'var infinity23TagManagerStop = 0;';

			$infinity23_header_lazyload_code .= "setTimeout(infinity23TagManagerRunLazyLoad, {lazy_load_time_replace});";

			$infinity23_header_lazyload_code .= "function infinity23TagManagerRunLazyLoad() {";
				$infinity23_header_lazyload_code .= "if(infinity23TagManagerStop == 0) {";
					$infinity23_header_lazyload_code .= "infinity23_tag_manager_js_lazy_load();";
				$infinity23_header_lazyload_code .= "}";
			$infinity23_header_lazyload_code .= "}";

			$infinity23_header_lazyload_code .= "window.addEventListener('scroll', function(e) {";
				$infinity23_header_lazyload_code .= "if( this.scrollY > 10 && infinity23TagManagerStop == 0) {";
					$infinity23_header_lazyload_code .= "infinity23_tag_manager_js_lazy_load();";
				$infinity23_header_lazyload_code .= "}";
			$infinity23_header_lazyload_code .= "});";

			$infinity23_header_lazyload_code .= "document.addEventListener('mouseup', infinity23TagManagerRunEventLazyload);";
			$infinity23_header_lazyload_code .= "document.addEventListener('mousedown', infinity23TagManagerRunEventLazyload);";
			$infinity23_header_lazyload_code .= "document.addEventListener('click', infinity23TagManagerRunEventLazyload);";
			$infinity23_header_lazyload_code .= "document.addEventListener('mousemove', infinity23TagManagerRunEventLazyload);";
			$infinity23_header_lazyload_code .= "document.addEventListener('keypress', infinity23TagManagerRunEventLazyload);";
			$infinity23_header_lazyload_code .= "document.addEventListener('touchstart', infinity23TagManagerRunEventLazyload);";

			$infinity23_header_lazyload_code .= "function infinity23TagManagerRunEventLazyload() {";
				$infinity23_header_lazyload_code .= "if (typeof infinity23TagManagerStop !== 'undefined' && infinity23TagManagerStop === 0) {";
					$infinity23_header_lazyload_code .= "infinity23_tag_manager_js_lazy_load();";
				$infinity23_header_lazyload_code .= "}";
			$infinity23_header_lazyload_code .= "}";

			$infinity23_header_lazyload_code .= '</script>';

			if( !empty( $infinity23_yandex_metrika_id ) ) {
				$infinity23_header_lazyload_code .= "<noscript><div><img src='https://mc.yandex.ru/watch/" . intval( $infinity23_yandex_metrika_id ) . "' style='position:absolute; left:-9999px;' alt='' /></div></noscript>";
			} // if( !empty( $infinity23_yandex_metrika_id ) ) {

			if( !empty( $infinity23_facebook_pixel_id ) ) {
				$infinity23_header_lazyload_code .= "<noscript><img height='1' width='1' style='display:none' src='https://www.facebook.com/tr?id=" . esc_attr( $infinity23_facebook_pixel_id ) . "&ev=PageView&noscript=1' /></noscript>";
			} // if( !empty( $infinity23_facebook_pixel_id ) ) {

		} // if( !empty( $infinity23_google_analytics_id ) || !empty( $infinity23_yandex_metrika_id ) || !empty( $infinity23_facebook_pixel_id ) || !empty( $infinity23_google_tag_manager_id ) || !empty( $infinity23_custom_lazy_load_js ) ) {

		if( !is_array( $infinity23_head_tags ) ) {
			$infinity23_head_tags = array();
		} // if( !is_array( $infinity23_head_tags ) ) {

		if( !is_array( $infinity23_body_tags ) ) {
			$infinity23_body_tags = array();
		} // if( !is_array( $infinity23_body_tags ) ) {

		if( !is_array( $infinity23_footer_tags ) ) {
			$infinity23_footer_tags = array();
		} // if( !is_array( $infinity23_footer_tags ) ) {

		$infinity23_head_tags = array_filter( array_map( 'trim', $infinity23_head_tags ) );
		$infinity23_body_tags = array_filter( array_map( 'trim', $infinity23_body_tags ) );
		$infinity23_footer_tags = array_filter( array_map( 'trim', $infinity23_footer_tags ) );

		$infinity23_tag_manager_head_output = $infinity23_header_lazyload_code;

		if( !empty( $infinity23_header_lazyload_code ) && !empty( $infinity23_head_tags ) ) {
			$infinity23_tag_manager_head_output .= "\n";
		} // if( !empty( $infinity23_header_lazyload_code ) && !empty( $infinity23_head_tags ) ) {

		$infinity23_tag_manager_head_output .= implode( "\n", $infinity23_head_tags );

		// ===========================================================================
		// build the body lazy load code (GTM noscript)
		// ===========================================================================

		$infinity23_body_lazyload_code = '';

		if( !empty( $infinity23_google_tag_manager_id ) ) {
			$infinity23_body_lazyload_code .= '<!-- Google Tag Manager (noscript) -->';
			$infinity23_body_lazyload_code .= "<noscript><iframe src='https://www.googletagmanager.com/ns.html?id=" . esc_attr( $infinity23_google_tag_manager_id ) . "' height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>";
			$infinity23_body_lazyload_code .= '<!-- End Google Tag Manager (noscript) -->';
		} // if( !empty( $infinity23_google_tag_manager_id ) ) {

		$infinity23_tag_manager_body_output = $infinity23_body_lazyload_code . implode( "\n", $infinity23_body_tags );
		$infinity23_tag_manager_footer_output = implode( "\n", $infinity23_footer_tags );

		// ===========================================================================
		// output the head tags
		// ===========================================================================

		if( !empty( $infinity23_tag_manager_head_output ) ) {

			add_action( 'wp_head', function() use ( $infinity23_tag_manager_head_output, $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users, $infinity23_lazy_load_time ) {

				global $infinity23_tag_manager_head_printed;

				if( empty( $infinity23_tag_manager_head_printed ) ) {

					if( infinity23_tag_manager_should_output( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) ) {

						$infinity23_tag_manager_head_printed = 1;
						$output_code = str_replace( '{lazy_load_time_replace}', $infinity23_lazy_load_time, $infinity23_tag_manager_head_output );
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-sanitized values
						echo "\n" . $output_code . "\n";

					} // if( infinity23_tag_manager_should_output(

				} // if( empty( $infinity23_tag_manager_head_printed ) ) {

			}, 9999 ); // add_action( 'wp_head'

			add_action( 'infinity23_tag_manager_head', function() use ( $infinity23_tag_manager_head_output, $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users, $infinity23_lazy_load_time ) {

				global $infinity23_tag_manager_head_printed;

				if( empty( $infinity23_tag_manager_head_printed ) ) {

					if( infinity23_tag_manager_should_output( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) ) {

						$infinity23_tag_manager_head_printed = 1;
						$output_code = str_replace( '{lazy_load_time_replace}', $infinity23_lazy_load_time, $infinity23_tag_manager_head_output );
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-sanitized values
						echo "\n" . $output_code . "\n";

					} // if( infinity23_tag_manager_should_output(

				} // if( empty( $infinity23_tag_manager_head_printed ) ) {

			}, 9999 ); // add_action( 'infinity23_tag_manager_head'

		} // if( !empty( $infinity23_tag_manager_head_output ) ) {

		// ===========================================================================
		// output the below body tags
		// ===========================================================================

		if( !empty( $infinity23_tag_manager_body_output ) ) {

			if( $infinity23_wp_body_open == 'wp_body_open' ) {

				add_action( 'wp_body_open', function() use ( $infinity23_tag_manager_body_output, $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) {

					global $infinity23_tag_manager_body_printed;

					if( empty( $infinity23_tag_manager_body_printed ) ) {

						if( infinity23_tag_manager_should_output( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) ) {
							$infinity23_tag_manager_body_printed = 1;
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-sanitized values
							echo "\n" . $infinity23_tag_manager_body_output . "\n";
						} // if( infinity23_tag_manager_should_output(

					} // if( empty( $infinity23_tag_manager_body_printed ) ) {

				}, 1 ); // add_action( 'wp_body_open'

			} else { // if( $infinity23_wp_body_open == 'wp_body_open' ) {

				add_action( 'template_redirect', function() use ( $infinity23_tag_manager_body_output, $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) {

					if( !infinity23_tag_manager_should_output( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) ) {
						return;
					} // if( !infinity23_tag_manager_should_output( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) ) {

					$infinity23_tag_manager_ob_level = ob_get_level();

					add_action( 'shutdown', function() use ( $infinity23_tag_manager_ob_level ) {
						while( ob_get_level() > $infinity23_tag_manager_ob_level ) {
							ob_end_flush();
						} // while( ob_get_level() > $infinity23_tag_manager_ob_level ) {
					}, 0 ); // add_action( 'shutdown'

					ob_start( function( $infinity23_buffer ) use ( $infinity23_tag_manager_body_output ) {

						global $infinity23_tag_manager_body_printed;

						if( empty( $infinity23_tag_manager_body_printed ) ) {

							$infinity23_body_regex = '/<body[^>]*>/i';

							if( preg_match( $infinity23_body_regex, $infinity23_buffer, $infinity23_buffer_return, PREG_OFFSET_CAPTURE ) ) {

								$infinity23_tag_manager_body_printed = 1;
								$infinity23_body_tag = $infinity23_buffer_return[0][0];
								$infinity23_body_pos = $infinity23_buffer_return[0][1];
								$infinity23_new_code = $infinity23_body_tag . "\n" . $infinity23_tag_manager_body_output . "\n";
								$infinity23_buffer = substr_replace( $infinity23_buffer, $infinity23_new_code, $infinity23_body_pos, strlen( $infinity23_body_tag ) );

							} // if( preg_match(

						} // if( empty( $infinity23_tag_manager_body_printed ) ) {

						return $infinity23_buffer;

					} ); // ob_start(

				}, 1 ); // add_action( 'template_redirect'

			} // if( $infinity23_wp_body_open == 'wp_body_open' ) {

			add_action( 'infinity23_tag_manager_below_body', function() use ( $infinity23_tag_manager_body_output, $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) {

				global $infinity23_tag_manager_body_printed;

				if( empty( $infinity23_tag_manager_body_printed ) ) {

					if( infinity23_tag_manager_should_output( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) ) {
						$infinity23_tag_manager_body_printed = 1;
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-sanitized values
						echo "\n" . $infinity23_tag_manager_body_output . "\n";
					} // if( infinity23_tag_manager_should_output(

				} // if( empty( $infinity23_tag_manager_body_printed ) ) {

			}, 9999 ); // add_action( 'infinity23_tag_manager_below_body'

		} // if( !empty( $infinity23_tag_manager_body_output ) ) {

		// ===========================================================================
		// output the footer tags
		// ===========================================================================

		if( !empty( $infinity23_tag_manager_footer_output ) ) {

			add_action( 'wp_footer', function() use ( $infinity23_tag_manager_footer_output, $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) {

				global $infinity23_tag_manager_footer_printed;

				if( empty( $infinity23_tag_manager_footer_printed ) ) {

					if( infinity23_tag_manager_should_output( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) ) {
						$infinity23_tag_manager_footer_printed = 1;
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-sanitized values
						echo "\n" . $infinity23_tag_manager_footer_output . "\n";
					} // if( infinity23_tag_manager_should_output(

				} // if( empty( $infinity23_tag_manager_footer_printed ) ) {

			}, 9999 ); // add_action( 'wp_footer'

			add_action( 'infinity23_tag_manager_before_closing_body', function() use ( $infinity23_tag_manager_footer_output, $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) {

				global $infinity23_tag_manager_footer_printed;

				if( empty( $infinity23_tag_manager_footer_printed ) ) {

					if( infinity23_tag_manager_should_output( $infinity23_exclude_ids, $infinity23_exclude_option, $infinity23_exclude_users ) ) {
						$infinity23_tag_manager_footer_printed = 1;
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-sanitized values
						echo "\n" . $infinity23_tag_manager_footer_output . "\n";
					} // if( infinity23_tag_manager_should_output(

				} // if( empty( $infinity23_tag_manager_footer_printed ) ) {

			}, 9999 ); // add_action( 'infinity23_tag_manager_before_closing_body'

		} // if( !empty( $infinity23_tag_manager_footer_output ) ) {

	} // if( !empty($infinity23_code_is_active) ) {

?>
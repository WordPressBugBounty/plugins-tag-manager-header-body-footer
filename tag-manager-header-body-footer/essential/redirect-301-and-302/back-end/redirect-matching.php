<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Redirect 301/302 - front-end URL matching and redirect execution
	// ===========================================================================

	if( !function_exists( 'infinity23_redirect_fix_wrong_url' ) ) {

		function infinity23_redirect_fix_wrong_url( $url ) {

			$question_mark_count = substr_count( $url, "?" );

			if( $question_mark_count > 1 ) {

				$last_question_mark_index = strrpos( $url, "?" );

				if( $last_question_mark_index !== false && $last_question_mark_index != strlen( $url ) - 1 ) {
					$url = substr_replace( $url, "&", $last_question_mark_index, 1 );
				} // if( $last_question_mark_index !== false && $last_question_mark_index != strlen( $url ) - 1 ) {

			} // if( $question_mark_count > 1 ) {

			return $url;

		} // function infinity23_redirect_fix_wrong_url( $url ) {

	} // if( !function_exists( 'infinity23_redirect_fix_wrong_url' ) ) {

	if( !function_exists( 'infinity23_redirect_send_301' ) ) {

		function infinity23_redirect_send_301( $destination_url ) {

			$destination_url = infinity23_redirect_fix_wrong_url( $destination_url );
			$destination_url = esc_url_raw( $destination_url );

			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
			header( 'HTTP/1.1 301 Moved Permanently' );
			header( 'Location: ' . $destination_url );
			exit();

		} // function infinity23_redirect_send_301( $destination_url ) {

	} // if( !function_exists( 'infinity23_redirect_send_301' ) ) {

	if( !function_exists( 'infinity23_redirect_send_302' ) ) {

		function infinity23_redirect_send_302( $destination_url ) {

			$destination_url = infinity23_redirect_fix_wrong_url( $destination_url );
			$destination_url = esc_url_raw( $destination_url );

			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
			header( 'HTTP/1.1 302 Found' );
			header( 'Location: ' . $destination_url );
			exit();

		} // function infinity23_redirect_send_302( $destination_url ) {

	} // if( !function_exists( 'infinity23_redirect_send_302' ) ) {

	if( !function_exists( 'infinity23_redirect_get_protocol' ) ) {

		function infinity23_redirect_get_protocol() {

			$protocol = 'http';

			if( isset( $_SERVER["HTTPS"] ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER["HTTPS"] ) ) ) == "on" ) {
				$protocol .= "s";
			} // if( isset( $_SERVER["HTTPS"] ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER["HTTPS"] ) ) ) == "on" ) {

			return $protocol;

		} // function infinity23_redirect_get_protocol() {

	} // if( !function_exists( 'infinity23_redirect_get_protocol' ) ) {

	if( !function_exists( 'infinity23_redirect_fire_row' ) ) {

		function infinity23_redirect_fire_row( $matched_row, $get_parameters_from_url, $domains_name, $log_enabled = false, $actual_visited_url = '' ) {

			$destination_url = $matched_row->destination_url;

			if( !strstr( $destination_url, 'http://' ) && !strstr( $destination_url, 'https://' ) ) {
				$destination_url = $domains_name . $destination_url;
			} // if( !strstr( $destination_url, 'http://' ) && !strstr( $destination_url, 'https://' ) ) {

			$final_get_parameters = $get_parameters_from_url;

			if( $matched_row->get_parameters == 1 ) {
				$final_get_parameters = '';
			} // if( $matched_row->get_parameters == 1 ) {

			infinity23_redirect_increment_count( $matched_row->id );

			if( !empty( $log_enabled ) ) {

				$infinity23_redirect_log_user_agent = infinity23_redirect_log_get_user_agent();
				$infinity23_redirect_log_ua_parsed = infinity23_redirect_log_parse_user_agent( $infinity23_redirect_log_user_agent );
				$infinity23_redirect_log_destination_url = infinity23_redirect_log_strip_domain( $destination_url . $final_get_parameters, $domains_name );

				infinity23_redirect_log_insert( array(
					'redirect_row_id' => $matched_row->id,
					'request_url' => $matched_row->request_url,
					'actual_request_url' => $actual_visited_url,
					'destination_url' => $infinity23_redirect_log_destination_url,
					'redirect_type' => $matched_row->redirect_type,
					'ip_address' => infinity23_redirect_log_get_visitor_ip(),
					'country' => infinity23_redirect_log_get_visitor_country(),
					'user_agent' => $infinity23_redirect_log_user_agent,
					'browser' => $infinity23_redirect_log_ua_parsed['browser'],
					'platform' => $infinity23_redirect_log_ua_parsed['platform'],
					'device_type' => $infinity23_redirect_log_ua_parsed['device_type'],
					'referrer' => infinity23_redirect_log_get_referrer(),
				) );

			} // if( !empty( $log_enabled ) ) {

			if( $matched_row->redirect_type === '302' ) {
				infinity23_redirect_send_302( $destination_url . $final_get_parameters );
			} else {
				infinity23_redirect_send_301( $destination_url . $final_get_parameters );
			} // if( $matched_row->redirect_type === '302' ) {

		} // function infinity23_redirect_fire_row( $matched_row, $get_parameters_from_url, $domains_name, $log_enabled = false, $actual_visited_url = '' ) {

	} // if( !function_exists( 'infinity23_redirect_fire_row' ) ) {

	if( !function_exists( 'infinity23_redirect_run_matching' ) ) {

		function infinity23_redirect_run_matching( $log_enabled = false ) {

			if( defined( 'WP_CLI' ) || wp_doing_cron() || !isset( $_SERVER['HTTP_HOST'] ) || !isset( $_SERVER['REQUEST_URI'] ) ) {
				return;
			} // if( defined( 'WP_CLI' ) || wp_doing_cron() || !isset( $_SERVER['HTTP_HOST'] ) || !isset( $_SERVER['REQUEST_URI'] ) ) {

			// esc_url_raw() and not sanitize_text_field() - sanitize_text_field() strips every %XX sequence, which breaks hebrew / encoded urls
			$infinity23_request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$page_path_without_get_parameters = strval( wp_parse_url( $infinity23_request_uri, PHP_URL_PATH ) );
			$page_path_without_get_parameters = str_replace( array( "'", '"' ), array( '', '' ), $page_path_without_get_parameters );

			$infinity23_request_query = wp_parse_url( $infinity23_request_uri, PHP_URL_QUERY );
			$get_parameters_from_url = !empty( $infinity23_request_query ) ? '?' . $infinity23_request_query : '';

			$domains_name = get_option( 'home' );
			$home_path = wp_parse_url( $domains_name, PHP_URL_PATH );
			$home_path = $home_path ? rtrim( $home_path, '/' ) : '';

			$url_user_request = $page_path_without_get_parameters;

			if( $home_path !== '' && stripos( $url_user_request, $home_path ) === 0 ) {
				$url_user_request = substr( $url_user_request, strlen( $home_path ) );
			} // if( $home_path !== '' && stripos( $url_user_request, $home_path ) === 0 ) {

			if( $url_user_request === '' ) {
				$url_user_request = '/';
			} // if( $url_user_request === '' ) {

			$url_user_request = esc_url_raw( $url_user_request );

			$infinity23_redirect_actual_visited_url = esc_url_raw( $url_user_request . $get_parameters_from_url );

			if( strstr( $url_user_request, 'wp-login' ) || strstr( $url_user_request, 'https:' ) ) {
				return;
			} // if( strstr( $url_user_request, 'wp-login' ) || strstr( $url_user_request, 'https:' ) ) {

			if( empty( $url_user_request ) ) {
				return;
			} // if( empty( $url_user_request ) ) {

			$url_user_request_lower = strtolower( $url_user_request );
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $_SERVER values are never magic-quote-slashed by WordPress, and the whole composed string is run through esc_url_raw() right here
			$full_url_path = esc_url_raw( strtolower( infinity23_redirect_get_protocol() . '://' . $_SERVER['HTTP_HOST'] . $page_path_without_get_parameters ) );
			$url_without_slash = rtrim( $url_user_request, '/' );

			$urldecode_user_request = str_replace( array( "'", '"' ), array( '', '' ), urldecode( $url_user_request ) );
			$urldecode_user_request_without_slash = str_replace( array( "'", '"' ), array( '', '' ), urldecode( $url_without_slash ) );
			$url_with_end_slash = str_replace( array( "'", '"' ), array( '', '' ), $urldecode_user_request . '/' );

			// ---------------------------------------
			// single query - get every redirect row split into an exact-match
			// lookup array and a contain-match list, so matching only ever
			// costs one database query per request instead of two
			// ---------------------------------------

			$infinity23_redirect_matching_data = infinity23_redirect_get_matching_data();
			$infinity23_redirect_exact_lookup = $infinity23_redirect_matching_data['exact_lookup'];
			$contain_rows = $infinity23_redirect_matching_data['contain_rows'];

			// ---------------------------------------
			// exact match pass
			// ---------------------------------------

			$url_variants = array_unique( array( $url_user_request, $url_user_request_lower, $full_url_path, $url_without_slash, $urldecode_user_request, $urldecode_user_request_without_slash, $url_with_end_slash ) );

			$exact_matches = array();

			foreach( $infinity23_redirect_exact_lookup as $infinity23_redirect_request_url_key => $infinity23_redirect_rows_for_url ) {

				if( in_array( $infinity23_redirect_request_url_key, $url_variants, true ) ) {

					foreach( $infinity23_redirect_rows_for_url as $matched_row ) {
						$exact_matches[] = $matched_row;
					} // foreach( $infinity23_redirect_rows_for_url as $matched_row ) {

				} // if( in_array( $infinity23_redirect_request_url_key, $url_variants, true ) ) {

			} // foreach( $infinity23_redirect_exact_lookup as $infinity23_redirect_request_url_key => $infinity23_redirect_rows_for_url ) {

			if( !empty( $exact_matches ) ) {

				foreach( $exact_matches as $matched_row ) {
					infinity23_redirect_fire_row( $matched_row, $get_parameters_from_url, $domains_name, $log_enabled, $infinity23_redirect_actual_visited_url );
				} // foreach( $exact_matches as $matched_row ) {

			} // if( !empty( $exact_matches ) ) {

			// ---------------------------------------
			// contain match pass
			// ---------------------------------------

			if( !empty( $contain_rows ) ) {

				foreach( $contain_rows as $matched_row ) {

					$request_url = strtolower( trim( $matched_row->request_url ) );

					// an empty pattern would match every url on the site
					if( $request_url === '' ) {
						continue;
					} // if( $request_url === '' ) {

					$urldecode_request_url = urldecode( $request_url );
					$urldecode_request_url_with_slash = $urldecode_request_url . '/';
					$urldecode_user_request_lower = strtolower( $urldecode_user_request );

					if( strstr( $url_user_request_lower, $request_url ) || strstr( strtolower( $url_without_slash ), $request_url ) || strstr( $full_url_path, $request_url ) || strstr( $urldecode_user_request_lower, $urldecode_request_url ) || strstr( $urldecode_user_request_lower, $urldecode_request_url_with_slash ) ) {

						infinity23_redirect_fire_row( $matched_row, $get_parameters_from_url, $domains_name, $log_enabled, $infinity23_redirect_actual_visited_url );

					} // if( strstr(

				} // foreach( $contain_rows as $matched_row ) {

			} // if( !empty( $contain_rows ) ) {

		} // function infinity23_redirect_run_matching( $log_enabled = false ) {

	} // if( !function_exists( 'infinity23_redirect_run_matching' ) ) {

?>
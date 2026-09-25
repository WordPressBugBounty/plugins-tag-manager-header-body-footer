<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// SSL functions - ssl detection, force https redirect, mixed content fixer
	// ===========================================================================

	// ===========================================================================
	// check if the current request is already https - loops through a list of
	// known proxy/cdn headers instead of a chain of separate if statements, so
	// adding a new header signal later is a one-line array entry, not a new block
	// ===========================================================================

	if( !function_exists( 'infinity23_ssl_get_proxy_header_signals' ) ) {

		function infinity23_ssl_get_proxy_header_signals() {

			// each entry: server key => substring (or exact list) that counts as https
			return array(
				'HTTP_X_FORWARDED_PROTO' => array( 'type' => 'contains', 'match' => 'https' ),
				'HTTP_CF_VISITOR' => array( 'type' => 'contains', 'match' => 'https' ),
				'HTTP_CLOUDFRONT_FORWARDED_PROTO' => array( 'type' => 'exact', 'match' => 'https' ),
				'HTTP_X_FORWARDED_SSL' => array( 'type' => 'in_list', 'match' => array( 'on', '1' ) ),
				'HTTP_FRONT_END_HTTPS' => array( 'type' => 'in_list', 'match' => array( 'on', '1' ) ),
			);

		} // function infinity23_ssl_get_proxy_header_signals() {

	} // if( !function_exists( 'infinity23_ssl_get_proxy_header_signals' ) ) {

	if( !function_exists( 'infinity23_ssl_is_ssl_request' ) ) {

		function infinity23_ssl_is_ssl_request( $detection_mode = 'extended' ) {

			if( is_ssl() ) {
				return true;
			} // if( is_ssl() ) {

			if( $detection_mode !== 'extended' ) {
				return false;
			} // if( $detection_mode !== 'extended' ) {

			// without this, any visitor sends X-Forwarded-Proto: https on a plain
			// http request and the forced redirect is skipped for them, which is
			// exactly the downgrade this module exists to prevent
			if( !infinity23_proxy_headers_are_trusted() ) {
				return false;
			} // if( !infinity23_proxy_headers_are_trusted() ) {

			$infinity23_ssl_header_signals = apply_filters( 'infinity23_ssl_proxy_header_signals', infinity23_ssl_get_proxy_header_signals() );

			foreach( $infinity23_ssl_header_signals as $infinity23_ssl_header_key => $infinity23_ssl_header_rule ) {

				if( empty( $_SERVER[ $infinity23_ssl_header_key ] ) ) {
					continue;
				} // if( empty( $_SERVER[ $infinity23_ssl_header_key ] ) ) {

				$infinity23_ssl_header_value = strtolower( sanitize_text_field( wp_unslash( $_SERVER[ $infinity23_ssl_header_key ] ) ) );

				if( $infinity23_ssl_header_rule['type'] === 'contains' && strpos( $infinity23_ssl_header_value, $infinity23_ssl_header_rule['match'] ) !== false ) {
					return true;
				} // if( $infinity23_ssl_header_rule['type'] === 'contains' ...

				if( $infinity23_ssl_header_rule['type'] === 'exact' && $infinity23_ssl_header_value === $infinity23_ssl_header_rule['match'] ) {
					return true;
				} // if( $infinity23_ssl_header_rule['type'] === 'exact' ...

				if( $infinity23_ssl_header_rule['type'] === 'in_list' && in_array( $infinity23_ssl_header_value, $infinity23_ssl_header_rule['match'], true ) ) {
					return true;
				} // if( $infinity23_ssl_header_rule['type'] === 'in_list' ...

			} // foreach( $infinity23_ssl_header_signals as $infinity23_ssl_header_key => $infinity23_ssl_header_rule ) {

			return false;

		} // function infinity23_ssl_is_ssl_request( $detection_mode = 'extended' ) {

	} // if( !function_exists( 'infinity23_ssl_is_ssl_request' ) ) {

	// ===========================================================================
	// check if the current request is on a localhost / local development
	// environment - used to skip the force-ssl redirect even when the setting
	// is active, since local sites rarely have a valid ssl certificate
	// ===========================================================================

	if( !function_exists( 'infinity23_ssl_is_localhost_environment' ) ) {

		function infinity23_ssl_is_localhost_environment() {

			$infinity23_ssl_host = !empty( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
			$infinity23_ssl_host = preg_replace( '/:[0-9]+$/', '', $infinity23_ssl_host );

			if( empty( $infinity23_ssl_host ) ) {
				return false;
			} // if( empty( $infinity23_ssl_host ) ) {

			$infinity23_ssl_localhost_hosts = array( 'localhost', '127.0.0.1', '::1' );

			if( in_array( $infinity23_ssl_host, $infinity23_ssl_localhost_hosts, true ) ) {
				return true;
			} // if( in_array( $infinity23_ssl_host, $infinity23_ssl_localhost_hosts, true ) ) {

			$infinity23_ssl_localhost_suffixes = apply_filters( 'infinity23_ssl_localhost_suffixes', array( '.local', '.test', '.localhost', '.wp', '.docksal', '.vagrant' ) );

			foreach( $infinity23_ssl_localhost_suffixes as $infinity23_ssl_suffix ) {

				if( substr( $infinity23_ssl_host, -strlen( $infinity23_ssl_suffix ) ) === $infinity23_ssl_suffix ) {
					return true;
				} // if( substr( $infinity23_ssl_host, -strlen( $infinity23_ssl_suffix ) ) === $infinity23_ssl_suffix ) {

			} // foreach( $infinity23_ssl_localhost_suffixes as $infinity23_ssl_suffix ) {

			if( filter_var( $infinity23_ssl_host, FILTER_VALIDATE_IP ) && !filter_var( $infinity23_ssl_host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
				return true;
			} // if( filter_var( $infinity23_ssl_host, FILTER_VALIDATE_IP ) ...

			return false;

		} // function infinity23_ssl_is_localhost_environment() {

	} // if( !function_exists( 'infinity23_ssl_is_localhost_environment' ) ) {

	// ===========================================================================
	// detect if the site actually has a working ssl certificate - makes a real
	// request to the https version of the site and checks the handshake
	// succeeds, cached for an hour so this never runs on every page load
	// ===========================================================================

	if( !function_exists( 'infinity23_ssl_detect_certificate' ) ) {

		function infinity23_ssl_detect_certificate() {

			if( infinity23_ssl_is_localhost_environment() ) {
				return true;
			} // if( infinity23_ssl_is_localhost_environment() ) {

			$infinity23_ssl_cached_result = get_transient( 'infinity23_ssl_certificate_detected' );

			if( $infinity23_ssl_cached_result !== false ) {
				return ( $infinity23_ssl_cached_result === 'yes' );
			} // if( $infinity23_ssl_cached_result !== false ) {

			$infinity23_ssl_https_url = 'https://' . wp_parse_url( home_url(), PHP_URL_HOST );

			$infinity23_ssl_response = wp_remote_head( $infinity23_ssl_https_url, array(
				'timeout'   => 10,
				'sslverify' => true,
			) );

			$infinity23_ssl_detected = !is_wp_error( $infinity23_ssl_response ) && intval( wp_remote_retrieve_response_code( $infinity23_ssl_response ) ) > 0;

			set_transient( 'infinity23_ssl_certificate_detected', $infinity23_ssl_detected ? 'yes' : 'no', HOUR_IN_SECONDS );

			return $infinity23_ssl_detected;

		} // function infinity23_ssl_detect_certificate() {

	} // if( !function_exists( 'infinity23_ssl_detect_certificate' ) ) {

	// ===========================================================================
	// redirect http requests to https
	// ===========================================================================

	// ---------------------------------------
	// returns a host safe to build a redirect Location header from - never
	// trusts $_SERVER['HTTP_HOST'] directly (it can be spoofed by the client),
	// only uses it when it matches the site's own configured host or the same
	// host with/without a "www." prefix
	// ---------------------------------------

	if( !function_exists( 'infinity23_ssl_get_safe_redirect_host' ) ) {

		function infinity23_ssl_get_safe_redirect_host() {

			$infinity23_ssl_configured_host = strtolower( wp_parse_url( home_url(), PHP_URL_HOST ) );

			if( empty( $_SERVER['HTTP_HOST'] ) ) {
				return $infinity23_ssl_configured_host;
			} // if( empty( $_SERVER['HTTP_HOST'] ) ) {

			$infinity23_ssl_requested_host = strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) );

			$infinity23_ssl_configured_host_no_www = preg_replace( '/^www\./', '', $infinity23_ssl_configured_host );
			$infinity23_ssl_requested_host_no_www = preg_replace( '/^www\./', '', $infinity23_ssl_requested_host );

			if( $infinity23_ssl_configured_host_no_www === $infinity23_ssl_requested_host_no_www ) {
				return $infinity23_ssl_requested_host;
			} // if( $infinity23_ssl_configured_host_no_www === $infinity23_ssl_requested_host_no_www ) {

			return $infinity23_ssl_configured_host;

		} // function infinity23_ssl_get_safe_redirect_host() {

	} // if( !function_exists( 'infinity23_ssl_get_safe_redirect_host' ) ) {

	if( !function_exists( 'infinity23_ssl_should_skip_redirect' ) ) {

		function infinity23_ssl_should_skip_redirect( $detection_mode = 'extended', $localhost_mode = 'exclude' ) {

			// last way back in when https stops answering and wp-admin is only
			// reachable over http - add this to wp-config.php:
			// define( 'INFINITY23_SSL_DISABLE_FORCE_HTTPS', true );
			if( defined( 'INFINITY23_SSL_DISABLE_FORCE_HTTPS' ) && INFINITY23_SSL_DISABLE_FORCE_HTTPS === true ) {
				return true;
			} // if( defined( 'INFINITY23_SSL_DISABLE_FORCE_HTTPS' ) && INFINITY23_SSL_DISABLE_FORCE_HTTPS === true ) {

			if( infinity23_ssl_is_ssl_request( $detection_mode ) ) {
				return true;
			} // if( infinity23_ssl_is_ssl_request( $detection_mode ) ) {

			if( ( defined( 'WP_CLI' ) && WP_CLI ) || PHP_SAPI === 'cli' ) {
				return true;
			} // if( ( defined( 'WP_CLI' ) && WP_CLI ) || PHP_SAPI === 'cli' ) {

			if( empty( $_SERVER['HTTP_HOST'] ) ) {
				return true;
			} // if( empty( $_SERVER['HTTP_HOST'] ) ) {

			if( !empty( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/.well-known/acme-challenge/' ) === 0 ) {
				return true;
			} // if( !empty( $_SERVER['REQUEST_URI'] ) ...

			if( $localhost_mode === 'exclude' && infinity23_ssl_is_localhost_environment() ) {
				return true;
			} // if( $localhost_mode === 'exclude' && infinity23_ssl_is_localhost_environment() ) {

			return false;

		} // function infinity23_ssl_should_skip_redirect( $detection_mode = 'extended', $localhost_mode = 'exclude' ) {

	} // if( !function_exists( 'infinity23_ssl_should_skip_redirect' ) ) {

	if( !function_exists( 'infinity23_ssl_maybe_redirect_to_https' ) ) {

		function infinity23_ssl_maybe_redirect_to_https( $detection_mode = 'extended', $localhost_mode = 'exclude' ) {

			if( infinity23_ssl_should_skip_redirect( $detection_mode, $localhost_mode ) ) {
				return;
			} // if( infinity23_ssl_should_skip_redirect( $detection_mode, $localhost_mode ) ) {

			$infinity23_ssl_host = infinity23_ssl_get_safe_redirect_host();
			// esc_url_raw() and not sanitize_text_field() - sanitize_text_field() strips every %XX sequence, which breaks hebrew / encoded urls
			$infinity23_ssl_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';

			$infinity23_ssl_redirect_url = 'https://' . $infinity23_ssl_host . $infinity23_ssl_request_uri;
			$infinity23_ssl_redirect_url = apply_filters( 'infinity23_ssl_redirect_url', $infinity23_ssl_redirect_url );

			// 301 is permanent and browsers cache it, so a broken certificate on
			// an admin url locks the admin out even after the setting is fixed.
			// Only the public front-end keeps the permanent code
			$infinity23_ssl_redirect_status = 301;

			if( is_admin() || ( isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'wp-login.php' ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
				$infinity23_ssl_redirect_status = 302;
			} // if( is_admin() || ( isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'wp-login.php' ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {

			// the host was already checked against home_url() in
			// infinity23_ssl_get_safe_redirect_host() - allow it for this redirect
			// only, or wp_safe_redirect() swaps the www variant for /wp-admin/
			add_filter( 'allowed_redirect_hosts', function( $infinity23_ssl_allowed_hosts ) use ( $infinity23_ssl_host ) {
				$infinity23_ssl_allowed_hosts[] = $infinity23_ssl_host;
				return $infinity23_ssl_allowed_hosts;
			} ); // add_filter( 'allowed_redirect_hosts'

			wp_safe_redirect( $infinity23_ssl_redirect_url, $infinity23_ssl_redirect_status );
			exit;

		} // function infinity23_ssl_maybe_redirect_to_https( $detection_mode = 'extended', $localhost_mode = 'exclude' ) {

	} // if( !function_exists( 'infinity23_ssl_maybe_redirect_to_https' ) ) {

	// ===========================================================================
	// mixed content fixer - rewrite http:// links in the page output to https://
	// the pair list is filterable, so extra rules (og:image, srcset, etc.) can be
	// added later via the filter instead of editing this function
	// ===========================================================================

	if( !function_exists( 'infinity23_ssl_get_mixed_content_pairs' ) ) {

		function infinity23_ssl_get_mixed_content_pairs() {

			$infinity23_ssl_home_http = str_replace( 'https://', 'http://', home_url( '/' ) );
			$infinity23_ssl_alt_http = ( strpos( $infinity23_ssl_home_http, '://www.' ) !== false ) ? str_replace( '://www.', '://', $infinity23_ssl_home_http ) : str_replace( '://', '://www.', $infinity23_ssl_home_http );

			$infinity23_ssl_pairs = array(
				$infinity23_ssl_home_http   => str_replace( 'http://', 'https://', $infinity23_ssl_home_http ),
				$infinity23_ssl_alt_http    => str_replace( 'http://', 'https://', $infinity23_ssl_alt_http ),
				'src="http://'  => 'src="https://',
				"src='http://"  => "src='https://",
			);

			return $infinity23_ssl_pairs;

		} // function infinity23_ssl_get_mixed_content_pairs() {

	} // if( !function_exists( 'infinity23_ssl_get_mixed_content_pairs' ) ) {

	if( !function_exists( 'infinity23_ssl_fix_mixed_content' ) ) {

		function infinity23_ssl_fix_mixed_content( $html ) {

			if( strpos( $html, '<?xml' ) === 0 ) {
				return $html;
			} // if( strpos( $html, '<?xml' ) === 0 ) {

			$infinity23_ssl_pairs = apply_filters( 'infinity23_ssl_mixed_content_pairs', infinity23_ssl_get_mixed_content_pairs() );

			foreach( $infinity23_ssl_pairs as $infinity23_ssl_find => $infinity23_ssl_replace_with ) {
				$html = str_replace( $infinity23_ssl_find, $infinity23_ssl_replace_with, $html );
			} // foreach( $infinity23_ssl_pairs as $infinity23_ssl_find => $infinity23_ssl_replace_with ) {

			return $html;

		} // function infinity23_ssl_fix_mixed_content( $html ) {

	} // if( !function_exists( 'infinity23_ssl_fix_mixed_content' ) ) {

	if( !function_exists( 'infinity23_ssl_start_output_buffer' ) ) {

		function infinity23_ssl_start_output_buffer() {

			if( is_admin() ) {
				return;
			} // if( is_admin() ) {

			ob_start( 'infinity23_ssl_fix_mixed_content' );

		} // function infinity23_ssl_start_output_buffer() {

	} // if( !function_exists( 'infinity23_ssl_start_output_buffer' ) ) {

	if( !function_exists( 'infinity23_ssl_end_output_buffer' ) ) {

		function infinity23_ssl_end_output_buffer() {

			if( function_exists( 'wp_ob_end_flush_all' ) ) {
				wp_ob_end_flush_all();
			} elseif( ob_get_level() ) {
				ob_end_flush();
			} // if( function_exists( 'wp_ob_end_flush_all' ) ) {

		} // function infinity23_ssl_end_output_buffer() {

	} // if( !function_exists( 'infinity23_ssl_end_output_buffer' ) ) {

?>
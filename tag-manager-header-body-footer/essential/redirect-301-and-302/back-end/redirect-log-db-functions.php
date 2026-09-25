<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Redirect 301/302 - Redirect Log - PRO
// one dedicated table that records every time a visitor was actually sent
// through a saved redirect. Kept in its own table (not infinity23_options)
// so a busy site can log heavily without bloating the main settings table,
// and so the whole table can be dropped in one query when the plugin data
// is reset.
// ===========================================================================

	// ---------------------------------------
	// table name helper
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_get_table' ) ) {

		function infinity23_redirect_log_get_table() {

			global $wpdb;
			return $wpdb->prefix . 'infinity23_redirects_log';

		} // function infinity23_redirect_log_get_table() {

	} // if( !function_exists( 'infinity23_redirect_log_get_table' ) ) {

	// ---------------------------------------
	// create the table - called only when Redirect Log is Enabled and saved
	// ---------------------------------------

	include_once( dirname(__FILE__) . "/../../../include/install-db/install-redirect-log-db.php" );

	// ---------------------------------------
	// checks the real database, not just the saved flag option - used only
	// on settings save, never on every page load, since it costs a query
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_table_exists' ) ) {

		function infinity23_redirect_log_table_exists() {

			global $wpdb;
			$table = infinity23_redirect_log_get_table();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return ( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" ) === $table );

		} // function infinity23_redirect_log_table_exists() {

	} // if( !function_exists( 'infinity23_redirect_log_table_exists' ) ) {

	// ---------------------------------------
	// strip the site domain (and path prefix) from a destination url before
	// saving it to the log, so same-site redirects are stored as a relative
	// path instead of repeating the full domain on every log row. External
	// destinations (a different domain) are left untouched.
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_strip_domain' ) ) {

		function infinity23_redirect_log_strip_domain( $destination_url, $domains_name ) {

			if( empty( $domains_name ) || stripos( $destination_url, $domains_name ) !== 0 ) {
				return $destination_url;
			} // if( empty( $domains_name ) || stripos( $destination_url, $domains_name ) !== 0 ) {

			$stripped_url = substr( $destination_url, strlen( $domains_name ) );

			if( substr( $stripped_url, 0, 1 ) !== '/' ) {
				$stripped_url = '/' . $stripped_url;
			} // if( substr( $stripped_url, 0, 1 ) !== '/' ) {

			return $stripped_url;

		} // function infinity23_redirect_log_strip_domain( $destination_url, $domains_name ) {

	} // if( !function_exists( 'infinity23_redirect_log_strip_domain' ) ) {

	// ---------------------------------------
	// insert one log row
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_insert' ) ) {

		function infinity23_redirect_log_insert( $log_data ) {

			global $wpdb;
			$table = infinity23_redirect_log_get_table();

			$insert_data = array(
				'redirect_row_id' => $log_data['redirect_row_id'],
				'request_url' => $log_data['request_url'],
				'actual_request_url' => isset( $log_data['actual_request_url'] ) ? $log_data['actual_request_url'] : '',
				'destination_url' => $log_data['destination_url'],
				'redirect_type' => $log_data['redirect_type'],
				'ip_address' => $log_data['ip_address'],
				'country' => $log_data['country'],
				'user_agent' => $log_data['user_agent'],
				'browser' => $log_data['browser'],
				'platform' => $log_data['platform'],
				'device_type' => $log_data['device_type'],
				'referrer' => $log_data['referrer'],
				'time_created_utc' => infinity23_current_timestamp(),
			);
			$insert_format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
			$wpdb->insert( $table, $insert_data, $insert_format );

			// no proactive SHOW TABLES check here - this runs on every fired
			// redirect, so we only pay the recreate+retry cost the one time the
			// table is actually missing, instead of checking existence on every insert
			if( !empty( $wpdb->last_error ) ) {

				infinity23_redirect_log_install_table();
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
				$wpdb->insert( $table, $insert_data, $insert_format );

			} // if( !empty( $wpdb->last_error ) ) {

			infinity23_redirect_log_enforce_max_count();

		} // function infinity23_redirect_log_insert( $log_data ) {

	} // if( !function_exists( 'infinity23_redirect_log_insert' ) ) {

	// ---------------------------------------
	// hard cap applied at insert time. The daily cron was the only thing
	// trimming this table, which left anonymous traffic free to grow it without
	// any limit between runs. Checked on roughly one insert in 200, so a normal
	// redirect never pays for the extra count query
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_enforce_max_count' ) ) {

		function infinity23_redirect_log_enforce_max_count() {

			if( wp_rand( 1, 200 ) !== 1 ) {
				return;
			} // if( wp_rand( 1, 200 ) !== 1 ) {

			include( dirname(__FILE__) . "/../default-values.php" );

			$infinity23_redirect_log_settings_array = infinity23_convert_json_to_array( infinity23_get_option( 'redirect_301_and_302_settings' ) );
			$infinity23_redirect_log_hard_max_count = intval( infinity23_pro_get_data( 'redirect_log_max_count', $infinity23_redirect_log_settings_array, $infinity23_defaults ) );

			infinity23_redirect_log_delete_excess( $infinity23_redirect_log_hard_max_count );

		} // function infinity23_redirect_log_enforce_max_count() {

	} // if( !function_exists( 'infinity23_redirect_log_enforce_max_count' ) ) {

	// ---------------------------------------
	// total log row count - used for pagination
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_count' ) ) {

		function infinity23_redirect_log_count( $redirect_row_id = 0, $search = '' ) {

			global $wpdb;
			$table = infinity23_redirect_log_get_table();

			// every branch below passes one literal query string straight into
			// $wpdb->prepare() - kept as explicit branches (instead of building
			// $sql across several statements) so a static scanner can verify
			// each query directly at its prepare() call
			if( !empty( $redirect_row_id ) && !empty( $search ) ) {

				$like = '%' . $wpdb->esc_like( $search ) . '%';

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				return intval( $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM `" . esc_sql( $table ) . "` WHERE redirect_row_id = %d AND (request_url LIKE %s OR destination_url LIKE %s)",
					$redirect_row_id,
					$like,
					$like
				) ) );

			} // if( !empty( $redirect_row_id ) && !empty( $search ) ) {

			if( !empty( $redirect_row_id ) ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				return intval( $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM `" . esc_sql( $table ) . "` WHERE redirect_row_id = %d",
					$redirect_row_id
				) ) );

			} // if( !empty( $redirect_row_id ) ) {

			if( !empty( $search ) ) {

				$like = '%' . $wpdb->esc_like( $search ) . '%';

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				return intval( $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM `" . esc_sql( $table ) . "` WHERE (request_url LIKE %s OR destination_url LIKE %s)",
					$like,
					$like
				) ) );

			} // if( !empty( $search ) ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . esc_sql( $table ) . "`" ) ) );

		} // function infinity23_redirect_log_count( $redirect_row_id = 0, $search = '' ) {

	} // if( !function_exists( 'infinity23_redirect_log_count' ) ) {

	// ---------------------------------------
	// get a page of log rows, newest first - optionally filtered to a single
	// redirect row id (used by the "view logs" link on the Redirects count)
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_get_rows' ) ) {

		function infinity23_redirect_log_get_rows( $page = 1, $per_page = 50, $redirect_row_id = 0, $search = '' ) {

			global $wpdb;
			$table = infinity23_redirect_log_get_table();

			$page = max( 1, intval( $page ) );
			$per_page = max( 1, intval( $per_page ) );
			$offset = ( $page - 1 ) * $per_page;

			// every branch below passes one literal query string straight into
			// $wpdb->prepare() - kept as explicit branches (instead of building
			// $sql across several statements) so a static scanner can verify
			// each query directly at its prepare() call
			if( !empty( $redirect_row_id ) && !empty( $search ) ) {

				$like = '%' . $wpdb->esc_like( $search ) . '%';

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				return $wpdb->get_results( $wpdb->prepare(
					"SELECT * FROM `" . esc_sql( $table ) . "` WHERE redirect_row_id = %d AND (request_url LIKE %s OR destination_url LIKE %s) ORDER BY id DESC LIMIT %d OFFSET %d",
					$redirect_row_id,
					$like,
					$like,
					$per_page,
					$offset
				) );

			} // if( !empty( $redirect_row_id ) && !empty( $search ) ) {

			if( !empty( $redirect_row_id ) ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				return $wpdb->get_results( $wpdb->prepare(
					"SELECT * FROM `" . esc_sql( $table ) . "` WHERE redirect_row_id = %d ORDER BY id DESC LIMIT %d OFFSET %d",
					$redirect_row_id,
					$per_page,
					$offset
				) );

			} // if( !empty( $redirect_row_id ) ) {

			if( !empty( $search ) ) {

				$like = '%' . $wpdb->esc_like( $search ) . '%';

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
				return $wpdb->get_results( $wpdb->prepare(
					"SELECT * FROM `" . esc_sql( $table ) . "` WHERE (request_url LIKE %s OR destination_url LIKE %s) ORDER BY id DESC LIMIT %d OFFSET %d",
					$like,
					$like,
					$per_page,
					$offset
				) );

			} // if( !empty( $search ) ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			return $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM `" . esc_sql( $table ) . "` ORDER BY id DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			) );

		} // function infinity23_redirect_log_get_rows( $page = 1, $per_page = 50, $redirect_row_id = 0, $search = '' ) {

	} // if( !function_exists( 'infinity23_redirect_log_get_rows' ) ) {

	// ---------------------------------------
	// delete every log row - used by the "Clear All Log Entries" button
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_clear_all' ) ) {

		function infinity23_redirect_log_clear_all() {

			global $wpdb;
			$table = infinity23_redirect_log_get_table();

			$wpdb->query( "TRUNCATE TABLE `" . esc_sql( $table ) . "`" );

		} // function infinity23_redirect_log_clear_all() {

	} // if( !function_exists( 'infinity23_redirect_log_clear_all' ) ) {

	// ---------------------------------------
	// delete every log row older than X days
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_delete_older_than' ) ) {

		function infinity23_redirect_log_delete_older_than( $retention_days ) {

			global $wpdb;
			$table = infinity23_redirect_log_get_table();

			$retention_days = intval( $retention_days );

			if( $retention_days <= 0 ) {
				return;
			} // if( $retention_days <= 0 ) {

			$threshold = infinity23_current_timestamp() - ( $retention_days * DAY_IN_SECONDS );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->query( $wpdb->prepare( "DELETE FROM `" . esc_sql( $table ) . "` WHERE time_created_utc < %d", $threshold ) );

		} // function infinity23_redirect_log_delete_older_than( $retention_days ) {

	} // if( !function_exists( 'infinity23_redirect_log_delete_older_than' ) ) {

	// ---------------------------------------
	// keep only the newest X rows, delete everything beyond that count
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_delete_excess' ) ) {

		function infinity23_redirect_log_delete_excess( $max_count ) {

			global $wpdb;
			$table = infinity23_redirect_log_get_table();

			$max_count = intval( $max_count );

			if( $max_count <= 0 ) {
				return;
			} // if( $max_count <= 0 ) {

			$total = infinity23_redirect_log_count();

			if( $total <= $max_count ) {
				return;
			} // if( $total <= $max_count ) {

			$excess_count = $total - $max_count;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
			$wpdb->query( $wpdb->prepare( "DELETE FROM `" . esc_sql( $table ) . "` ORDER BY id ASC LIMIT %d", $excess_count ) );

		} // function infinity23_redirect_log_delete_excess( $max_count ) {

	} // if( !function_exists( 'infinity23_redirect_log_delete_excess' ) ) {

	// ---------------------------------------
	// visitor data helpers - country is NEVER looked up via an external
	// service, only read from a header a hosting/CDN layer already set on
	// the request (e.g. Cloudflare) - so it stays empty on sites without one
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_get_visitor_ip' ) ) {

		function infinity23_redirect_log_get_visitor_ip() {

			if( infinity23_proxy_headers_are_trusted() ) {

				if( !empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
					return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
				} // if( !empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {

				if( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
					$forwarded_ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
					return trim( $forwarded_ips[0] );
				} // if( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

			} // if( infinity23_proxy_headers_are_trusted() ) {

			if( !empty( $_SERVER['REMOTE_ADDR'] ) ) {
				return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			} // if( !empty( $_SERVER['REMOTE_ADDR'] ) ) {

			return '';

		} // function infinity23_redirect_log_get_visitor_ip() {

	} // if( !function_exists( 'infinity23_redirect_log_get_visitor_ip' ) ) {

	if( !function_exists( 'infinity23_redirect_log_get_visitor_country' ) ) {

		function infinity23_redirect_log_get_visitor_country() {

			if( infinity23_proxy_headers_are_trusted() ) {

				if( !empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
					return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) );
				} // if( !empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {

			} // if( infinity23_proxy_headers_are_trusted() ) {

			// set by a server module, not by the visitor, so it needs no proxy check
			if( !empty( $_SERVER['GEOIP_COUNTRY_CODE'] ) ) {
				return sanitize_text_field( wp_unslash( $_SERVER['GEOIP_COUNTRY_CODE'] ) );
			} // if( !empty( $_SERVER['GEOIP_COUNTRY_CODE'] ) ) {

			if( infinity23_proxy_headers_are_trusted() && !empty( $_SERVER['HTTP_X_COUNTRY_CODE'] ) ) {
				return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_COUNTRY_CODE'] ) );
			} // if( infinity23_proxy_headers_are_trusted() && !empty( $_SERVER['HTTP_X_COUNTRY_CODE'] ) ) {

			return '';

		} // function infinity23_redirect_log_get_visitor_country() {

	} // if( !function_exists( 'infinity23_redirect_log_get_visitor_country' ) ) {

	if( !function_exists( 'infinity23_redirect_log_get_user_agent' ) ) {

		function infinity23_redirect_log_get_user_agent() {

			if( !empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
				return sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
			} // if( !empty( $_SERVER['HTTP_USER_AGENT'] ) ) {

			return '';

		} // function infinity23_redirect_log_get_user_agent() {

	} // if( !function_exists( 'infinity23_redirect_log_get_user_agent' ) ) {

	if( !function_exists( 'infinity23_redirect_log_get_referrer' ) ) {

		function infinity23_redirect_log_get_referrer() {

			if( !empty( $_SERVER['HTTP_REFERER'] ) ) {
				return esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
			} // if( !empty( $_SERVER['HTTP_REFERER'] ) ) {

			return '';

		} // function infinity23_redirect_log_get_referrer() {

	} // if( !function_exists( 'infinity23_redirect_log_get_referrer' ) ) {

	// ---------------------------------------
	// parse browser / platform / device type out of the raw user agent string -
	// simple keyword matching, no external library or lookup service needed
	// ---------------------------------------

	if( !function_exists( 'infinity23_redirect_log_parse_user_agent' ) ) {

		function infinity23_redirect_log_parse_user_agent( $user_agent ) {

			$result = array(
				'browser' => 'Other',
				'platform' => 'Other',
				'device_type' => 'Desktop',
			);

			if( empty( $user_agent ) ) {
				return $result;
			} // if( empty( $user_agent ) ) {

			$ua = $user_agent;

			// ---------------------------------------
			// browser - order matters, Edge/Opera also contain "Chrome" in their UA
			// ---------------------------------------

			if( stripos( $ua, 'Edg' ) !== false ) {
				$result['browser'] = 'Edge';
			} elseif( stripos( $ua, 'OPR' ) !== false || stripos( $ua, 'Opera' ) !== false ) {
				$result['browser'] = 'Opera';
			} elseif( stripos( $ua, 'Chrome' ) !== false ) {
				$result['browser'] = 'Chrome';
			} elseif( stripos( $ua, 'Firefox' ) !== false ) {
				$result['browser'] = 'Firefox';
			} elseif( stripos( $ua, 'Safari' ) !== false ) {
				$result['browser'] = 'Safari';
			} elseif( stripos( $ua, 'MSIE' ) !== false || stripos( $ua, 'Trident' ) !== false ) {
				$result['browser'] = 'Internet Explorer';
			} // if( stripos( $ua, 'Edg' ) !== false ) {

			// ---------------------------------------
			// platform
			// ---------------------------------------

			if( stripos( $ua, 'Android' ) !== false ) {
				$result['platform'] = 'Android';
			} elseif( stripos( $ua, 'iPhone' ) !== false || stripos( $ua, 'iPad' ) !== false || stripos( $ua, 'iPod' ) !== false ) {
				$result['platform'] = 'iOS';
			} elseif( stripos( $ua, 'Windows' ) !== false ) {
				$result['platform'] = 'Windows';
			} elseif( stripos( $ua, 'Mac OS' ) !== false ) {
				$result['platform'] = 'Mac';
			} elseif( stripos( $ua, 'Linux' ) !== false ) {
				$result['platform'] = 'Linux';
			} // if( stripos( $ua, 'Android' ) !== false ) {

			// ---------------------------------------
			// device type
			// ---------------------------------------

			if( stripos( $ua, 'iPad' ) !== false || stripos( $ua, 'Tablet' ) !== false ) {
				$result['device_type'] = 'Tablet';
			} elseif( stripos( $ua, 'Mobile' ) !== false || stripos( $ua, 'iPhone' ) !== false || stripos( $ua, 'Android' ) !== false ) {
				$result['device_type'] = 'Mobile';
			} // if( stripos( $ua, 'iPad' ) !== false || stripos( $ua, 'Tablet' ) !== false ) {

			return $result;

		} // function infinity23_redirect_log_parse_user_agent( $user_agent ) {

	} // if( !function_exists( 'infinity23_redirect_log_parse_user_agent' ) ) {

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Notices - generic engine used by every admin notice in this folder
// ===========================================================================

	// --------------------------------------------
	// save the status of a single notice - each notice gets its own database row
	// $value is either a UTC timestamp (show again after this time) or the string 'stop'
	// --------------------------------------------

	if( !function_exists( 'infinity23_notices_update_status' ) ) {

		function infinity23_notices_update_status( $notice_id, $value ) {

			infinity23_update_option( $notice_id, $value, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'notice_settings' );

		} // function infinity23_notices_update_status( $notice_id, $value ) {

	} // if( !function_exists( 'infinity23_notices_update_status' ) ) {

	// --------------------------------------------
	// check if a notice should be displayed right now
	// $default_show_after is the UTC timestamp used the first time the notice
	// is ever checked, before the visitor clicked any button
	// --------------------------------------------

	if( !function_exists( 'infinity23_notices_should_show' ) ) {

		function infinity23_notices_should_show( $notice_id, $default_show_after = 0 ) {

		    // only the users who are allowed to dismiss a notice should ever see it
		    if( !current_user_can( 'manage_options' ) ) {
		        return false;
		    } // if( !current_user_can( 'manage_options' ) ) {

		    $status = infinity23_get_option( $notice_id, '' );

			if( $status === '' ) {
				return ( infinity23_current_timestamp() >= intval( $default_show_after ) );
			} // if( $status === '' ) {

			if( $status === 'stop' ) {
				return false;
			} // if( $status === 'stop' ) {

			return ( infinity23_current_timestamp() >= intval( $status ) );

		} // function infinity23_notices_should_show( $notice_id, $default_show_after = 0 ) {

	} // if( !function_exists( 'infinity23_notices_should_show' ) ) {

	// --------------------------------------------
	// build the no-javascript safe dismiss link
	// $type is either 'snooze' (ask again in a month) or 'stop' (never show again)
	// $redirect_to is where the click sends the browser after the dismiss is saved
	// --------------------------------------------

	if( !function_exists( 'infinity23_notices_dismiss_url' ) ) {

		function infinity23_notices_dismiss_url( $notice_id, $type, $redirect_to = '', $snooze_hours = 0 ) {

			if( empty( $redirect_to ) ) {
				$redirect_to = admin_url();
			} // if( empty( $redirect_to ) ) {

			$url = add_query_arg( array(
				'action'                 => 'infinity23_notice_dismiss',
				'notice'                 => $notice_id,
				'type'                   => $type,
				'snooze_hours'           => intval( $snooze_hours ),
				'redirect_to'            => rawurlencode( $redirect_to ),
				'infinity23_notice_nonce' => wp_create_nonce( 'infinity23_notice_action_' . $notice_id ),
			), admin_url( 'admin-post.php' ) );

			return $url;

		} // function infinity23_notices_dismiss_url( $notice_id, $type, $redirect_to = '', $snooze_hours = 0 ) {

	} // if( !function_exists( 'infinity23_notices_dismiss_url' ) ) {

	// --------------------------------------------
	// shared logic that actually saves the dismiss - used by both handlers below
	// --------------------------------------------

	if( !function_exists( 'infinity23_notices_process_dismiss' ) ) {

		function infinity23_notices_process_dismiss( $notice_id, $type, $snooze_hours = 0 ) {

			if( !current_user_can( 'manage_options' ) ) {
				return false;
			} // if( !current_user_can( 'manage_options' ) ) {

			if( $type === 'snooze' ) {
				$snooze_hours = ( $snooze_hours > 0 ) ? intval( $snooze_hours ) : 720;
				infinity23_notices_update_status( $notice_id, infinity23_current_timestamp() + ( $snooze_hours * HOUR_IN_SECONDS ) );
			} elseif( $type === 'stop' ) {
				infinity23_notices_update_status( $notice_id, 'stop' );
			} // if( $type === 'snooze' ) {

			return true;

		} // function infinity23_notices_process_dismiss( $notice_id, $type, $snooze_hours = 0 ) {

	} // if( !function_exists( 'infinity23_notices_process_dismiss' ) ) {

	// --------------------------------------------
	// full page load handler - this is what makes dismiss work even if
	// javascript is broken or disabled, since the button href points here
	// --------------------------------------------

	if( !function_exists( 'infinity23_notices_handle_dismiss' ) ) {

		function infinity23_notices_handle_dismiss() {

			if( !current_user_can( 'manage_options' ) ) {
				wp_die( 'Permission denied.' );
			} // if( !current_user_can( 'manage_options' ) ) {

			$notice_id = isset( $_GET['notice'] ) ? sanitize_text_field( wp_unslash( $_GET['notice'] ) ) : '';
			$type = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';
			$snooze_hours = isset( $_GET['snooze_hours'] ) ? intval( wp_unslash( $_GET['snooze_hours'] ) ) : 0;
			$nonce = isset( $_GET['infinity23_notice_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_notice_nonce'] ) ) : '';
			$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : admin_url();

			if( empty( $notice_id ) || !wp_verify_nonce( $nonce, 'infinity23_notice_action_' . $notice_id ) ) {
				wp_die( 'Security check failed' );
			} // if( empty( $notice_id ) || !wp_verify_nonce(

			infinity23_notices_process_dismiss( $notice_id, $type, $snooze_hours );

			add_filter( 'allowed_redirect_hosts', function( $hosts ) {
				$hosts[] = 'wordpress.org';
				return $hosts;
			} );

			wp_safe_redirect( $redirect_to );
			exit;

		} // function infinity23_notices_handle_dismiss() {

	} // if( !function_exists( 'infinity23_notices_handle_dismiss' ) ) {

	add_action( 'admin_post_infinity23_notice_dismiss', 'infinity23_notices_handle_dismiss' );

	// --------------------------------------------
	// ajax handler - only used when javascript is working, so the button can
	// fade out and save without a full page reload
	// --------------------------------------------

	if( !function_exists( 'infinity23_notices_handle_dismiss_ajax' ) ) {

		function infinity23_notices_handle_dismiss_ajax() {

			if( !current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => 'Permission denied.' ) );
			} // if( !current_user_can( 'manage_options' ) ) {

			$notice_id = isset( $_POST['notice'] ) ? sanitize_text_field( wp_unslash( $_POST['notice'] ) ) : '';
			$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
			$snooze_hours = isset( $_POST['snooze_hours'] ) ? intval( wp_unslash( $_POST['snooze_hours'] ) ) : 0;
			$nonce = isset( $_POST['infinity23_notice_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['infinity23_notice_nonce'] ) ) : '';

			if( empty( $notice_id ) || !wp_verify_nonce( $nonce, 'infinity23_notice_action_' . $notice_id ) ) {
				wp_die( 'Security check failed' );
			} // if( empty( $notice_id ) || !wp_verify_nonce(

			infinity23_notices_process_dismiss( $notice_id, $type, $snooze_hours );
			wp_die();

		} // function infinity23_notices_handle_dismiss_ajax() {

	} // if( !function_exists( 'infinity23_notices_handle_dismiss_ajax' ) ) {

	add_action( 'wp_ajax_infinity23_notice_dismiss', 'infinity23_notices_handle_dismiss_ajax' );

	// --------------------------------------------
	// render a notice box - reusable for every notice added to this folder
	// $args keys: notice_id, icon_url, plugin_name, message_html, buttons
	// each button: label, url, extra_class, target, dismiss_type, snooze_hours
	// button_click_action controls what clicking the button does:
	// dismiss'(default) - same tab, ajax dismiss, notice fades away
	// dismiss_new_tab' - opens url (usually target="_blank"), dismiss saved via the url itself (built with infinity23_notices_dismiss_url), notice fades in this tab
	// no_dismiss' - plain link, does nothing to the notice, notice stays exactly as is
	// --------------------------------------------

	if( !function_exists( 'infinity23_notices_render' ) ) {

		function infinity23_notices_render( $args ) {

			$notice_id = isset( $args['notice_id'] ) ? $args['notice_id'] : '';
			$icon_url = isset( $args['icon_url'] ) ? $args['icon_url'] : '';
			$plugin_name = isset( $args['plugin_name'] ) ? $args['plugin_name'] : '';
			$message_html = isset( $args['message_html'] ) ? $args['message_html'] : '';
			$buttons = isset( $args['buttons'] ) ? $args['buttons'] : array();
			$close_url = isset( $args['close_url'] ) ? $args['close_url'] : '';
			$dismiss_nonce = wp_create_nonce( 'infinity23_notice_action_' . $notice_id );

			$output = '';

			$output .= '<div class="infinity23-notice-style notice notice-info" data-notice-id="' . esc_attr( $notice_id ) . '">';

				if( !empty( $close_url ) ) {
					$output .= '<a href="' . esc_url( $close_url ) . '" class="infinity23-notice-close-x infinity23-notice-dismiss-link" data-click-action="dismiss" data-dismiss-type="stop" data-snooze-hours="0" data-dismiss-nonce="' . esc_attr( $dismiss_nonce ) . '">x</a>';
				} // if( !empty( $close_url ) ) {

				if( !empty( $icon_url ) ) {
					$output .= '<div class="infinity23-notice-icon">';
						$output .= '<img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $plugin_name ) . '" />';
					$output .= '</div><!--infinity23-notice-icon-->';
				} // if( !empty( $icon_url ) ) {

				$output .= '<div class="infinity23-notice-message">' . $message_html . '</div><!--infinity23-notice-message-->';

				if( !empty( $buttons ) ) {

					$output .= '<div class="infinity23-notice-buttons">';

					foreach( $buttons as $button ) {

						$extra_class = isset( $button['extra_class'] ) ? $button['extra_class'] : '';
						$open_in_new_tab = isset( $button['open_in_new_tab'] ) ? intval( $button['open_in_new_tab'] ) : 0;
						$click_action = isset( $button['button_click_action'] ) ? $button['button_click_action'] : 'dismiss';
						$dismiss_type = isset( $button['dismiss_type'] ) ? $button['dismiss_type'] : 'stop';
						$snooze_hours = isset( $button['snooze_hours'] ) ? intval( $button['snooze_hours'] ) : 0;

						if( $click_action === 'no_dismiss' ) {

							$output .= '<a class="infinity23-notice-btn ' . esc_attr( $extra_class ) . '"';
							$output .= ' href="' . esc_url( $button['url'] ) . '"';

							if( $open_in_new_tab === 1 ) {
								$output .= ' target="_blank"';
							} // if( $open_in_new_tab === 1 ) {

							$output .= '>' . esc_html( $button['label'] ) . '</a>';

						} else {

							$output .= '<a class="infinity23-notice-btn infinity23-notice-dismiss-link ' . esc_attr( $extra_class ) . '"';
							$output .= ' href="' . esc_url( $button['url'] ) . '"';
							$output .= ' data-click-action="' . esc_attr( $click_action ) . '"';
							$output .= ' data-dismiss-type="' . esc_attr( $dismiss_type ) . '"';
							$output .= ' data-snooze-hours="' . esc_attr( $snooze_hours ) . '"';
							$output .= ' data-dismiss-nonce="' . esc_attr( $dismiss_nonce ) . '"';

							if( $open_in_new_tab === 1 ) {
								$output .= ' target="_blank"';
							} // if( $open_in_new_tab === 1 ) {

							$output .= '>' . esc_html( $button['label'] ) . '</a>';

						} // if( $click_action === 'no_dismiss' ) {

				} // foreach( $buttons as $button ) {

					$output .= '</div><!--infinity23-notice-buttons-->';

				} // if( !empty( $buttons ) ) {

				$output .= '<div class="infinity23-notice-bottom-name">' . esc_html( $plugin_name ) . ' Plugin</div><!--infinity23-notice-bottom-name-->';

			$output .= '</div><!--infinity23-notice-style-->';

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values
			echo $output;

		} // function infinity23_notices_render( $args ) {

	} // if( !function_exists( 'infinity23_notices_render' ) ) {

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Review Notice - asks for a review 1 month after the plugin was installed
// ===========================================================================

	if( !function_exists( 'infinity23_review_notice_output' ) ) {

		function infinity23_review_notice_output( $snooze_hours ) {

			$plugin_name = 'Infinity23';
			$plugin_review_page = 'https://wordpress.org/plugins/tag-manager-header-body-footer/#reviews';
			$plugin_support_link = 'https://wordpress.org/support/plugin/tag-manager-header-body-footer/';
			$icon_image_path = infinity23_plugin_url( 'images/plugin-icon.png' );

			$current_page_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : admin_url();

			$message_html = '<p>We are happy to see that you have been using <b>' . esc_html( $plugin_name ) . '</b> plugin for a month. ';
			$message_html .= 'We work hard to improve this plugin, and if you are happy with it, please help us and <a href="' . esc_url( $plugin_review_page ) . '" target="_blank">share your experience with others</a>.</p>';
			$message_html .= '<p>If for some reason you are not happy, or you have a problem with the plugin, please submit a ticket at our <a href="' . esc_url( $plugin_support_link ) . '" target="_blank">wordpress support page</a> and we will be happy to help (-: </p>';

			$buttons = array(
				array(
					'label'               => 'Yes!!! This plugin saved my life I love it and I will be happy to give it 5 stars review :)',
					'url'                 => infinity23_notices_dismiss_url( 'review_notice', 'stop', $plugin_review_page ),
					'extra_class'         => 'infinity23-notice-btn-primary',
					'open_in_new_tab'     => 1,
					'button_click_action' => 'dismiss_new_tab',
					'dismiss_type'        => 'stop',
					'snooze_hours'        => '',
				),
				array(
					'label'               => 'I am busy dude ask me again later',
					'url'                 => infinity23_notices_dismiss_url( 'review_notice', 'snooze', $current_page_url, $snooze_hours ),
					'extra_class'         => '',
					'open_in_new_tab'     => 0,
					'button_click_action' => 'dismiss',
					'dismiss_type'        => 'snooze',
					'snooze_hours'        => $snooze_hours,
				),
				array(
					'label'               => 'Never show this message again :(',
					'url'                 => infinity23_notices_dismiss_url( 'review_notice', 'stop', $current_page_url ),
					'extra_class'         => 'infinity23-notice-btn-danger',
					'open_in_new_tab'     => 0,
					'button_click_action' => 'dismiss',
					'dismiss_type'        => 'stop',
					'snooze_hours'        => '',
				),
			);

			infinity23_notices_render( array(
				'notice_id'    => 'review_notice',
				'icon_url'     => $icon_image_path,
				'plugin_name'  => $plugin_name,
				'message_html' => $message_html,
				'buttons'      => $buttons,
			) );

		} // function infinity23_review_notice_output() {

	} // if( !function_exists( 'infinity23_review_notice_output' ) ) {

	// --------------------------------------------
	// notice settings - change these values to control the review notice
	// --------------------------------------------

	$infinity23_review_notice_id = 'review_notice'; // key name saved in the notices_status option in the database

	// set the wait time in hours - 1 day = 24 / 1 week = 168 / 1 month = 720
	$infinity23_review_notice_wait_after_install_hours = 720;

	// set the wait time in hours before asking again after "I am busy" is clicked - 1 day = 24 / 1 week = 168 / 1 month = 720
	$infinity23_review_notice_snooze_hours = 168;

	// set up the conditions to when to show the code
	$infinity23_review_notice_install_time = intval( infinity23_get_option( 'plugin_install_time', 0 ) );
	$infinity23_review_notice_show_after = !empty( $infinity23_review_notice_install_time ) ? $infinity23_review_notice_install_time + ( $infinity23_review_notice_wait_after_install_hours * HOUR_IN_SECONDS ) : infinity23_current_timestamp() + ( $infinity23_review_notice_wait_after_install_hours * HOUR_IN_SECONDS );

	if( infinity23_notices_should_show( $infinity23_review_notice_id, $infinity23_review_notice_show_after ) ) {
		add_action( 'admin_notices', function() use ( $infinity23_review_notice_snooze_hours ) {
			infinity23_review_notice_output( $infinity23_review_notice_snooze_hours );
		} );
	} // if( infinity23_notices_should_show(

?>
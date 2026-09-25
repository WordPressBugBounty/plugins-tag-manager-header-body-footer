<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Onboarding Notice - welcomes a new user right after install, closed with an X
// ===========================================================================

	if( !function_exists( 'infinity23_onboarding_notice_output' ) ) {

		function infinity23_onboarding_notice_output() {

			$plugin_name = 'Infinity23';
			$icon_image_path = infinity23_plugin_url( 'images/plugin-icon.png' );
			$include_exclude_url = admin_url( 'admin.php?page=essential-include-exclude' );

			$current_page_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : admin_url();

			$message_html = '<p>Welcome to <b>' . esc_html( $plugin_name ) . '</b>! Thanks for installing the plugin. ';
			$message_html .= 'Here some options that will help you get started:</p>';

			$buttons = array(
				array(
					'label'               => 'Choose Which Modules To Include / Exclude',
					'url'                 => $include_exclude_url,
					'extra_class'         => '',
					'open_in_new_tab'     => 0,
					'button_click_action' => 'no_dismiss',
					'dismiss_type'        => '',
					'snooze_hours'        => '',
				),
				array(
					'label'               => '&#9658; Watch Video Tutorial',
					'url'                 => 'https://www.yydevelopment.com/yydevelopment-wordpress-plugins/',
					'extra_class'         => 'infinity23-notice-btn-primary',
					'open_in_new_tab'     => 1,
					'button_click_action' => 'no_dismiss',
					'dismiss_type'        => '',
					'snooze_hours'        => '',
				),
				array(
					'label'               => "I Don't Need Help, Close box",
					'url'                 => infinity23_notices_dismiss_url( 'onboarding_notice', 'stop', $current_page_url ),
					'extra_class'         => 'infinity23-notice-btn-danger',
					'open_in_new_tab'     => 0,
					'button_click_action' => 'dismiss',
					'dismiss_type'        => 'stop',
					'snooze_hours'        => '',
				),
			);

			infinity23_notices_render( array(
				'notice_id'    => 'onboarding_notice',
				'icon_url'     => $icon_image_path,
				'plugin_name'  => $plugin_name,
				'message_html' => $message_html,
				'buttons'      => $buttons,
				'close_url'    => infinity23_notices_dismiss_url( 'onboarding_notice', 'stop', $current_page_url ),
			) );

		} // function infinity23_onboarding_notice_output() {

	} // if( !function_exists( 'infinity23_onboarding_notice_output' ) ) {

	// --------------------------------------------
	// notice settings - shows right after install, no wait time,
	// stays until the user closes it with the X button
	// --------------------------------------------

	$infinity23_onboarding_notice_id = 'onboarding_notice'; // key name saved in the notices_status option in the database

	if( infinity23_notices_should_show( $infinity23_onboarding_notice_id, 0 ) ) {
		add_action( 'admin_notices', function() {
			infinity23_onboarding_notice_output();
		} );
	} // if( infinity23_notices_should_show(

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Tag Manager Upgrade Notice - shown only on a site where the old standalone
// "YYDevelopment - Tag Manager - Header, Body And Footer" plugin was upgraded
// into Infinity23, so the admin is told what happened to their plugin and
// where their tags went
// ===========================================================================

	if( !function_exists( 'infinity23_tm_upgrade_notice_output' ) ) {

		function infinity23_tm_upgrade_notice_output() {

			$plugin_name = 'Infinity23';
			$icon_image_path = infinity23_plugin_url( 'images/include-exclude/tag-manager.png' );

			$tag_manager_url = admin_url( 'admin.php?page=essential-tag-manager' );
			$include_exclude_url = admin_url( 'admin.php?page=essential-include-exclude' );

			// the Previous Versions block on the old plugin's wordpress.org page.
			// The "Previous Versions" title has no anchor id of its own, so the
			// link points at the download button inside that block, which is the
			// only id on the page and sits right under the version dropdown
			$previous_versions_url = 'https://wordpress.org/plugins/tag-manager-header-body-footer/advanced/#download-previous-link';

			$current_page_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : admin_url();

			$message_html = '<p class="infinity23-notice-title">Tag Manager Plugin Became Infinity23 <mark>(everything that was free before stays free)</mark></p>';
			$message_html .= '<p><span class="infinity23-notice-strong-text">Infinity23 helps you replace 80% of your plugins with only one plugin.</span> Give it a look and check all the cool stuff you get on the <a href="' . esc_url( $include_exclude_url ) . '">include / exclude page</a>.';
			$message_html .= '<br />And if you prefer not to use the new plugin and want to go back to the old one, download a version older than 4 under the <a href="' . esc_url( $previous_versions_url ) . '" target="_blank">Previous Versions</a> title on the WordPress plugin page.</p>';

			$buttons = array(
				array(
					'label'               => 'Go To Tag Manager And Make Sure Your Tags Transferred Right',
					'url'                 => $tag_manager_url,
					'extra_class'         => '',
					'open_in_new_tab'     => 0,
					'button_click_action' => 'no_dismiss',
					'dismiss_type'        => '',
					'snooze_hours'        => '',
				),
				array(
					'label'               => 'View All The New Options That You Get Now',
					'url'                 => $include_exclude_url,
					'extra_class'         => 'infinity23-notice-btn-primary infinity23-notice-btn-strong',
					'open_in_new_tab'     => 0,
					'button_click_action' => 'no_dismiss',
					'dismiss_type'        => '',
					'snooze_hours'        => '',
				),
				array(
					'label'               => 'Got It, Close box',
					'url'                 => infinity23_notices_dismiss_url( 'tag_manager_upgrade_notice', 'stop', $current_page_url ),
					'extra_class'         => 'infinity23-notice-btn-danger',
					'open_in_new_tab'     => 0,
					'button_click_action' => 'dismiss',
					'dismiss_type'        => 'stop',
					'snooze_hours'        => '',
				),
			);

			infinity23_notices_render( array(
				'notice_id'    => 'tag_manager_upgrade_notice',
				'icon_url'     => $icon_image_path,
				'plugin_name'  => $plugin_name,
				'message_html' => $message_html,
				'buttons'      => $buttons,
				'close_url'    => infinity23_notices_dismiss_url( 'tag_manager_upgrade_notice', 'stop', $current_page_url ),
			) );

		} // function infinity23_tm_upgrade_notice_output() {

	} // if( !function_exists( 'infinity23_tm_upgrade_notice_output' ) ) {

	// --------------------------------------------
	// notice settings - only ever shows on a site that was upgraded from the
	// old standalone plugin. The install process sets tag_manager_upgrade_pending
	// to 1, and without that row this notice never renders at all, so a normal
	// new install never sees it
	// --------------------------------------------

	$infinity23_tm_upgrade_notice_id = 'tag_manager_upgrade_notice'; // key name saved in the notices_status option in the database

	if( intval( infinity23_get_option( 'tag_manager_upgrade_pending', 0 ) ) == 1 ) {

		if( infinity23_notices_should_show( $infinity23_tm_upgrade_notice_id, 0 ) ) {
			add_action( 'admin_notices', function() {
				infinity23_tm_upgrade_notice_output();
			} );
		} // if( infinity23_notices_should_show(

	} // if( intval( infinity23_get_option( 'tag_manager_upgrade_pending', 0 ) ) == 1 ) {

?>

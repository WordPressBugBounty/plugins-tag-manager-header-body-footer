<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ----------------------------------------
	// the default privacy page url is looked up only when no value was ever
	// saved - see infinity23_privacy_policy_default_page_url() below, so this
	// file can be included on every request without running a query
	// ----------------------------------------

	$infinity23_defaults = array(
		'message_text' => array(
			'name'   => 'message_text',
			'value'  => __( 'This site uses cookies and other tools to provide you with a better browsing experience, as outlined in the {privacy-page-dont-edit}', 'tag-manager-header-body-footer' ),
			'type'   => 'html',
			'access' => 'free',
		),
		'privacy_link_text' => array(
			'name'   => 'privacy_link_text',
			'value'  => __( 'privacy policy', 'tag-manager-header-body-footer' ),
			'type'   => 'text',
			'access' => 'free',
		),
		'accept_button_text' => array(
			'name'   => 'accept_button_text',
			'value'  => __( 'Accept', 'tag-manager-header-body-footer' ),
			'type'   => 'text',
			'access' => 'free',
		),
		'privacy_policy_url' => array(
			'name'   => 'privacy_policy_url',
			'value'  => '',
			'type'   => 'url',
			'access' => 'free',
		),
		'privacy_policy_url_new_tab' => array(
			'name'   => 'privacy_policy_url_new_tab',
			'value'  => 1,
			'type'   => 'intval',
			'access' => 'free',
		),
		'bar_background_color' => array(
			'name'   => 'bar_background_color',
			'value'  => '#333333',
			'type'   => 'text',
			'access' => 'free',
		),
		'bar_text_color' => array(
			'name'   => 'bar_text_color',
			'value'  => '#ffffff',
			'type'   => 'text',
			'access' => 'free',
		),
		'button_background_color' => array(
			'name'   => 'button_background_color',
			'value'  => '#007cba',
			'type'   => 'text',
			'access' => 'free',
		),
		'button_text_color' => array(
			'name'   => 'button_text_color',
			'value'  => '#ffffff',
			'type'   => 'text',
			'access' => 'free',
		),
		'cookie_expiry_days' => array(
			'name'   => 'cookie_expiry_days',
			'value'  => 365,
			'type'   => 'intval',
			'access' => 'free',
		),
		'hide_on_desktop_checkbox' => array(
			'name'   => 'hide_on_desktop_checkbox',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'bar_position_vertical' => array(
			'name'   => 'bar_position_vertical',
			'value'  => 'bottom',
			'type'   => 'text',
			'access' => 'free',
		),
		'bar_vertical_spacing' => array(
			'name'   => 'bar_vertical_spacing',
			'value'  => '0px',
			'type'   => 'text',
			'access' => 'free',
		),
		'bar_position_horizontal' => array(
			'name'   => 'bar_position_horizontal',
			'value'  => 'left',
			'type'   => 'text',
			'access' => 'free',
		),
		'bar_horizontal_spacing' => array(
			'name'   => 'bar_horizontal_spacing',
			'value'  => '0px',
			'type'   => 'text',
			'access' => 'free',
		),
		'bar_width' => array(
			'name'   => 'bar_width',
			'value'  => '100',
			'type'   => 'text',
			'access' => 'free',
		),
		'bar_width_unit' => array(
			'name'   => 'bar_width_unit',
			'value'  => '%',
			'type'   => 'text',
			'access' => 'free',
		),
		'z_index' => array(
			'name'   => 'z_index',
			'value'  => 999999,
			'type'   => 'intval',
			'access' => 'free',
		),
		'hide_on_mobile_checkbox' => array(
			'name'   => 'hide_on_mobile_checkbox',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
		'mobile_mode' => array(
			'name'   => 'mobile_mode',
			'value'  => 'inherit',
			'type'   => 'text',
			'access' => 'free',
		),
		'mobile_breakpoint' => array(
			'name'   => 'mobile_breakpoint',
			'value'  => 1025,
			'type'   => 'intval',
			'access' => 'free',
		),
		'mobile_bar_position_vertical' => array(
			'name'   => 'mobile_bar_position_vertical',
			'value'  => 'bottom',
			'type'   => 'text',
			'access' => 'free',
		),
		'mobile_bar_vertical_spacing' => array(
			'name'   => 'mobile_bar_vertical_spacing',
			'value'  => '0px',
			'type'   => 'text',
			'access' => 'free',
		),
		'mobile_bar_position_horizontal' => array(
			'name'   => 'mobile_bar_position_horizontal',
			'value'  => 'left',
			'type'   => 'text',
			'access' => 'free',
		),
		'mobile_bar_horizontal_spacing' => array(
			'name'   => 'mobile_bar_horizontal_spacing',
			'value'  => '0px',
			'type'   => 'text',
			'access' => 'free',
		),
		'mobile_bar_width' => array(
			'name'   => 'mobile_bar_width',
			'value'  => '100',
			'type'   => 'text',
			'access' => 'free',
		),
		'mobile_bar_width_unit' => array(
			'name'   => 'mobile_bar_width_unit',
			'value'  => '%',
			'type'   => 'text',
			'access' => 'free',
		),
		'exclude_ids' => array(
			'name'   => 'exclude_ids',
			'value'  => '',
			'type'   => 'text',
			'access' => 'free',
		),
		'exclude_option' => array(
			'name'   => 'exclude_option',
			'value'  => 'none',
			'type'   => 'text',
			'access' => 'free',
		),
		'custom_css' => array(
			'name'      => 'custom_css',
			'value'     => '',
			'type'      => 'css',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'custom_css_active' => array(
			'name'      => 'custom_css_active',
			'value'     => 1,
			'type'      => 'intval',
			'access'    => 'pro',
			'downgrade' => 'keep',
		),
		'privacy_policy_hide_menu' => array(
			'name'   => 'privacy_policy_hide_menu',
			'value'  => 0,
			'type'   => 'intval',
			'access' => 'free',
		),
	);

	// ----------------------------------------
	// find the url of the privacy policy page set in WordPress settings - called
	// only by admin-output.php / front-end.php when privacy_policy_url was never
	// saved, so the lookup never runs on regular page loads
	// ----------------------------------------

	if( !function_exists( 'infinity23_privacy_policy_default_page_url' ) ) {

		function infinity23_privacy_policy_default_page_url() {

			$privacy_url = '';
			$privacy_page_id = intval( get_option( 'wp_page_for_privacy_policy' ) );

			if( !empty( $privacy_page_id ) ) {
				$privacy_url = esc_url( get_permalink( $privacy_page_id ) );
			} // if( !empty( $privacy_page_id ) ) {

			if( empty( $privacy_url ) ) {
				$privacy_url = esc_url( get_privacy_policy_url() );
			} // if( empty( $privacy_url ) ) {

			return $privacy_url;

		} // function infinity23_privacy_policy_default_page_url() {

	} // if( !function_exists( 'infinity23_privacy_policy_default_page_url' ) ) {

?>
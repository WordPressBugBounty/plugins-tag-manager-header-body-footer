<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/*
Plugin Name: Infinity23 (Formerly Tag Manager) - One Plugin That Replaces All The Rest
Plugin URI:  https://infinity23.com/
Description: All in one that replace all the rest. Infinity23 combines accessibility, page & post notes, tag manager, floating contact buttons, back to top, phone button, WhatsApp button, mobile contact line, privacy policy cookie bar, show all pages and more into a single plugin.
Version:     4.1.2
Requires at least: 5.3
Requires PHP: 7.1
Author:      YYDevelopment
Author URI:  https://www.yydevelopment.com/
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: tag-manager-header-body-footer
Domain Path: /languages
*/

// ===================================================================================================
// Infinity23 - All In One plugin main file
// ===================================================================================================

	// ==================================================
	// Plugin version - read dynamically from the plugin header above
	// ==================================================

	if( !defined( 'INFINITY23_VERSION' ) ) {
		$infinity23_plugin_header = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
		define( 'INFINITY23_VERSION', $infinity23_plugin_header['Version'] );
	} // if( !defined( 'INFINITY23_VERSION' ) ) {

	// ==================================================
	// Include main settings and functions
	// ==================================================

	require( dirname(__FILE__) . "/include/settings.php" );
	require( dirname(__FILE__) . "/include/functions/main-functions.php" );
	require( dirname(__FILE__) . "/include/reset-plugin-data.php" );

	// ==================================================
	// install the plugin database when plugin activated via infinity23_install_database() function
	// if the user remove the database we will also install if from infinity23_load_all_options() function
	// ==================================================

	register_activation_hook( __FILE__, 'infinity23_install_database' );

	// ==================================================
	// Load all options into global cache once
	// ==================================================

	// loading the main settings data from the database
	infinity23_load_all_options();

	// set a value to globally get all the data
	global $infinity23_options_cache;

	// ==================================================
	// Checking plugin version, and update it if needed
	// ==================================================

	$infinity23_plugin_version = infinity23_get_option('plugin_version');

	if( $infinity23_plugin_version !== INFINITY23_VERSION ) {
		infinity23_update_option( 'plugin_version', INFINITY23_VERSION );
	} // if( $infinity23_plugin_version !== INFINITY23_VERSION ) {

	// ==================================================
	// Register the Essential main menu
	// ==================================================

	add_action( 'admin_menu', function() {

		add_menu_page(
			'Essential',
			'Essential',
			'manage_options',
			'essential-include-exclude',
			function() {},
			// infinity23_plugin_url( 'images/favicon.png' ),
			file_exists( dirname(__FILE__) . '/images/favicon.svg' ) ? 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( dirname(__FILE__) . '/images/favicon.svg' ) ) : 'dashicons-admin-generic',
			59
		);

	} ); // add_action( 'admin_menu'

	// ==================================================
	// Add a "Settings" link on the Plugins page that redirects to the Include / Exclude page
	// ==================================================

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {

		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=essential-include-exclude' ) ) . '">Settings</a>';
		array_unshift( $links, $settings_link );

		return $links;

	} ); // add_filter( 'plugin_action_links_'

	// ==================================================
	// Show a short plugin name on the Plugins page
	// ==================================================

	add_filter( 'all_plugins', function( $plugins ) {

		$infinity23_plugin_basename = plugin_basename( __FILE__ );

		if( isset( $plugins[ $infinity23_plugin_basename ] ) ) {
			$plugins[ $infinity23_plugin_basename ]['Name'] = 'Infinity23 (Formerly Tag Manager)';
		} // if( isset( $plugins[ $infinity23_plugin_basename ] ) ) {

		return $plugins;

	} ); // add_filter( 'all_plugins'

	// ==================================================
	// Load the Essential loader file
	// ==================================================

	add_action( 'init', function() {
		require_once( dirname(__FILE__) . "/essential/loader.php" );
	} );

	// ==================================================
	// Load the Notices system (admin only)
	// ==================================================

	if( is_admin() ) {
		add_action( 'init', function() {
			require( dirname(__FILE__) . "/notices/loader.php" );
		} );
	} // if( is_admin() ) {

	// ==================================================
	// Load the Dashboard Global Assets - CSS/JS loaded on every wp-admin
	// screen, not just the plugin's own settings pages
	// ==================================================

	if( is_admin() ) {
		add_action( 'admin_enqueue_scripts', function() {

			wp_register_style( 'infinity23_dashboard_style', infinity23_plugin_url( 'css-and-js/back-end/wp-dashboard-global/infinity23-dashboard-style.css' ), array(), INFINITY23_VERSION );
			wp_enqueue_style( 'infinity23_dashboard_style' );

			wp_register_script( 'infinity23_dashboard_js', infinity23_plugin_url( 'css-and-js/back-end/wp-dashboard-global/infinity23-dashboard-js.js' ), array( 'jquery' ), INFINITY23_VERSION, true );
			wp_enqueue_script( 'infinity23_dashboard_js' );

			// hide each module's own sidebar link when that module's hide_menu
			// setting is on - one combined rule instead of a separate
			// admin_head echo per module
			global $infinity23_hidden_menu_slugs;

			if( !empty( $infinity23_hidden_menu_slugs ) ) {

				$infinity23_hide_menu_css = '';

				foreach( $infinity23_hidden_menu_slugs as $infinity23_hidden_slug ) {
					$infinity23_hide_menu_css .= '#adminmenu a[href*="page=' . esc_attr( $infinity23_hidden_slug ) . '"] { display: none; } ';
				} // foreach( $infinity23_hidden_menu_slugs as $infinity23_hidden_slug ) {

				wp_add_inline_style( 'infinity23_dashboard_style', $infinity23_hide_menu_css );

			} // if( !empty( $infinity23_hidden_menu_slugs ) ) {

		} ); // add_action( 'admin_enqueue_scripts'
	} // if( is_admin() ) {

	// ==================================================
	// Load the Cron system
	// ==================================================

	require( dirname(__FILE__) . "/cron/loader.php" );

?>
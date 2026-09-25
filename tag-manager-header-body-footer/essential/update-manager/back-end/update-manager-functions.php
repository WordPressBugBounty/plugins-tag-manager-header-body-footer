<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Update Manager - applies the update behavior filters and admin notice
// Only included from loader.php when the module's activation code is 1
// ===========================================================================

	if( !function_exists( 'infinity23_update_manager_apply_settings' ) ) {

		function infinity23_update_manager_apply_settings( $infinity23_settings_array_db_name, $infinity23_defaults ) {

			$infinity23_update_manager_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

			$infinity23_disable_plugin_auto_updates = intval( infinity23_pro_get_data( 'disable_plugin_auto_updates', $infinity23_update_manager_data_array, $infinity23_defaults ) );
			$infinity23_disable_theme_auto_updates = intval( infinity23_pro_get_data( 'disable_theme_auto_updates', $infinity23_update_manager_data_array, $infinity23_defaults ) );
			// isset() first - infinity23_pro_get_data() returns the 'all' default when
			// the key is missing, which passes the whitelist check below and would
			// silently drop the old checkbox choice before the page is saved once
			$infinity23_core_updates_mode = isset( $infinity23_update_manager_data_array['core_updates_mode'] ) ? infinity23_pro_get_data( 'core_updates_mode', $infinity23_update_manager_data_array, $infinity23_defaults ) : '';

			// a site saved before this became a select has nothing in the new
			// field - carry the old checkbox over so its choice is not dropped
			if( !in_array( $infinity23_core_updates_mode, array( 'all', 'security_only', 'none' ), true ) ) {
				$infinity23_core_updates_mode = ( intval( infinity23_pro_get_data( 'disable_core_auto_updates', $infinity23_update_manager_data_array, $infinity23_defaults ) ) === 1 ) ? 'none' : 'all';
			} // if( !in_array( $infinity23_core_updates_mode, array( 'all', 'security_only', 'none' ), true ) ) {
			$infinity23_hide_core_update_notice = intval( infinity23_pro_get_data( 'hide_core_update_notice', $infinity23_update_manager_data_array, $infinity23_defaults ) );

			if( $infinity23_disable_plugin_auto_updates == 1 ) {
				// phpcs:ignore update_modification_detected -- this is the site admin's own "disable auto-updates" toggle for OTHER plugins (Update Manager module), not Infinity23 altering its own update routine
				add_filter( 'auto_update_plugin', '__return_false' );
			} // if( $infinity23_disable_plugin_auto_updates == 1 ) {

			if( $infinity23_disable_theme_auto_updates == 1 ) {
				add_filter( 'auto_update_theme', '__return_false' );
			} // if( $infinity23_disable_theme_auto_updates == 1 ) {

			if( $infinity23_core_updates_mode === 'security_only' ) {

				// minor core releases are the security patches, so they are left
				// running - only major version jumps and dev builds are blocked
				add_filter( 'allow_major_auto_core_updates', '__return_false' );
				add_filter( 'allow_dev_auto_core_updates', '__return_false' );

			} elseif( $infinity23_core_updates_mode === 'none' ) {

				add_filter( 'auto_update_core', '__return_false' );
				add_filter( 'allow_major_auto_core_updates', '__return_false' );
				add_filter( 'allow_minor_auto_core_updates', '__return_false' );
				add_filter( 'allow_dev_auto_core_updates', '__return_false' );

			} // if( $infinity23_core_updates_mode === 'security_only' ) {

			$infinity23_force_block_plugin_updates = infinity23_pro_get_data( 'force_block_plugin_updates', $infinity23_update_manager_data_array, $infinity23_defaults );
			$infinity23_force_block_theme_updates = infinity23_pro_get_data( 'force_block_theme_updates', $infinity23_update_manager_data_array, $infinity23_defaults );
			$infinity23_force_block_updates_mode = infinity23_pro_get_data( 'force_block_updates_mode', $infinity23_update_manager_data_array, $infinity23_defaults );
			$infinity23_force_block_updates_notice = intval( infinity23_pro_get_data( 'force_block_updates_notice', $infinity23_update_manager_data_array, $infinity23_defaults ) );

			if( is_admin() && $infinity23_force_block_updates_mode === 'ignore' && $infinity23_force_block_updates_notice == 1 ) {
				add_action( 'admin_notices', 'infinity23_update_manager_ignore_notice_output' );
			} // if( is_admin() && $infinity23_force_block_updates_mode === 'ignore' && $infinity23_force_block_updates_notice == 1 ) {

			if( $infinity23_force_block_updates_mode !== 'ignore' && !empty( $infinity23_force_block_plugin_updates ) ) {

				// phpcs:ignore plugin_updater_detected -- this only REMOVES entries from the update-check response to block specific OTHER plugins the admin listed; it never adds/injects an update entry, so it is not a self-updater for Infinity23 itself
				add_filter( 'site_transient_update_plugins', function( $value ) use ( $infinity23_force_block_plugin_updates ) {

					if( !isset( $value ) || !is_object( $value ) || empty( $value->response ) ) { return $value; }

					$infinity23_blocked_plugins_array = array_map( 'trim', explode( ',', $infinity23_force_block_plugin_updates ) );

					foreach( $infinity23_blocked_plugins_array as $infinity23_blocked_plugin_file ) {

						if( isset( $value->response[ $infinity23_blocked_plugin_file ] ) ) {
							unset( $value->response[ $infinity23_blocked_plugin_file ] );
						} // if( isset( $value->response[ $infinity23_blocked_plugin_file ] ) ) {

					} // foreach( $infinity23_blocked_plugins_array as $infinity23_blocked_plugin_file ) {

					return $value;

				} ); // add_filter( 'site_transient_update_plugins'

			} // if( $infinity23_force_block_updates_mode !== 'ignore' && !empty( $infinity23_force_block_plugin_updates ) ) {

			if( $infinity23_force_block_updates_mode !== 'ignore' && !empty( $infinity23_force_block_theme_updates ) ) {

				add_filter( 'site_transient_update_themes', function( $value ) use ( $infinity23_force_block_theme_updates ) {

					if( !isset( $value ) || !is_object( $value ) || empty( $value->response ) ) { return $value; }

					$infinity23_blocked_themes_array = array_map( 'trim', explode( ',', $infinity23_force_block_theme_updates ) );

					foreach( $infinity23_blocked_themes_array as $infinity23_blocked_theme_slug ) {

						if( isset( $value->response[ $infinity23_blocked_theme_slug ] ) ) {
							unset( $value->response[ $infinity23_blocked_theme_slug ] );
						} // if( isset( $value->response[ $infinity23_blocked_theme_slug ] ) ) {

					} // foreach( $infinity23_blocked_themes_array as $infinity23_blocked_theme_slug ) {

					return $value;

				} ); // add_filter( 'site_transient_update_themes'

			} // if( $infinity23_force_block_updates_mode !== 'ignore' && !empty( $infinity23_force_block_theme_updates ) ) {

			if( is_admin() && $infinity23_hide_core_update_notice == 1 ) {

				// update_nag is registered by wp-admin only after the init hook this code runs on,
				// so it has to be removed later - admin_head always fires before admin_notices
				add_action( 'admin_head', function() {
					remove_action( 'admin_notices', 'update_nag', 3 );
					remove_action( 'network_admin_notices', 'update_nag', 3 );
				} ); // add_action( 'admin_head'

			} // if( is_admin() && $infinity23_hide_core_update_notice == 1 ) {

		} // function infinity23_update_manager_apply_settings( $infinity23_settings_array_db_name, $infinity23_defaults ) {

	} // if( !function_exists( 'infinity23_update_manager_apply_settings' ) ) {

// ===========================================================================
// Update Manager - admin notice shown while Ignore All Blocks mode is active
// and the notice checkbox is enabled on the Update Manager settings page
// ===========================================================================

	if( !function_exists( 'infinity23_update_manager_ignore_notice_output' ) ) {

		function infinity23_update_manager_ignore_notice_output() {

			if( !current_user_can( 'manage_options' ) ) {
				return;
			} // if( !current_user_can( 'manage_options' ) ) {

			$settings_url = admin_url( 'admin.php?page=essential-update-manager' );

			?>

			<div class="notice notice-warning infinity23-um-ignore-notice">
				<strong class="infinity23-um-ignore-notice-title">Infinity23 - Updates Manager: Force Block Updates Is Temporarily Disabled</strong>
				<p>Force Block Updates is currently set to <strong>Ignore All Blocks</strong>, so every plugin and theme you have blocked is temporarily allowed to update.</p>
				<p><a href="<?php echo esc_url( $settings_url ); ?>">Go to the settings page</a> to turn blocking back on, or uncheck this notice option to stop showing this message.</p>
			</div><!--infinity23-um-ignore-notice-->
			<?php

		} // function infinity23_update_manager_ignore_notice_output() {

	} // if( !function_exists( 'infinity23_update_manager_ignore_notice_output' ) ) {

?>
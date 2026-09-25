<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Folders Organizer - list of content types that can have folders
	// Images, Pages and Posts are always listed, and every custom post type
	// registered with show_ui is added automatically. Each entry is its own
	// separate folders organizer. Only call these functions after the init
	// hook has finished, otherwise custom post types are not registered yet.
	// ===========================================================================

	if( !function_exists( 'infinity23_folders_organizer_get_content_types' ) ) {

		function infinity23_folders_organizer_get_content_types() {

			$content_types = array(
				'images' => array(
					'label' => 'Images (Media Library)',
					'items_label' => 'Files',
					'post_type' => 'attachment',
					'capability' => 'upload_files',
				),
				'pages' => array(
					'label' => 'Pages',
					'items_label' => 'Pages',
					'post_type' => 'page',
					'capability' => 'edit_pages',
				),
				'posts' => array(
					'label' => 'Posts',
					'items_label' => 'Posts',
					'post_type' => 'post',
					'capability' => 'edit_posts',
				),
			);

			$custom_post_types = infinity23_get_custom_post_types();

			foreach( $custom_post_types as $post_type_slug => $cpt_data ) {

				if( isset( $content_types[ $post_type_slug ] ) ) { continue; } // if( isset( $content_types[ $post_type_slug ] ) ) {

				$post_type_object = get_post_type_object( $post_type_slug );

				$content_types[ $post_type_slug ] = array(
					'label' => $cpt_data['label'] . ' (' . $post_type_slug . ')',
					'items_label' => $cpt_data['label'],
					'post_type' => $post_type_slug,
					'capability' => ( !empty( $post_type_object ) && isset( $post_type_object->cap->edit_posts ) ) ? $post_type_object->cap->edit_posts : 'edit_posts',
				);

			} // foreach( $custom_post_types as $post_type_slug => $cpt_data ) {

			return $content_types;

		} // function infinity23_folders_organizer_get_content_types() {

	} // if( !function_exists( 'infinity23_folders_organizer_get_content_types' ) ) {

	// ---------------------------------------
	// the built-in content type keys - images, pages, posts. Custom post
	// types are never part of this list - they are enabled/disabled via
	// custom_post_types_active and disabled_custom_post_types instead
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_builtin_type_keys' ) ) {

		function infinity23_folders_organizer_get_builtin_type_keys() {

			return array( 'images', 'pages', 'posts' );

		} // function infinity23_folders_organizer_get_builtin_type_keys() {

	} // if( !function_exists( 'infinity23_folders_organizer_get_builtin_type_keys' ) ) {

	// ---------------------------------------
	// only the content types the admin left enabled on the settings page
	// a type with no saved value yet falls back to the default state
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_enabled_content_types' ) ) {

		function infinity23_folders_organizer_get_enabled_content_types() {

			static $enabled_types_cache = null;

			if( $enabled_types_cache !== null ) {
				return $enabled_types_cache;
			} // if( $enabled_types_cache !== null ) {

			include( dirname(__FILE__) . "/../internal-settings.php" );
			include( dirname(__FILE__) . "/../default-values.php" );

			$settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
			$saved_types = infinity23_pro_get_data( 'enabled_content_types', $settings_data, $infinity23_defaults );
			$default_state = intval( infinity23_pro_get_data( 'enabled_content_types_default', $settings_data, $infinity23_defaults ) );
			$custom_post_types_active = intval( infinity23_pro_get_data( 'custom_post_types_active', $settings_data, $infinity23_defaults ) );
			$disabled_cpt_csv = infinity23_pro_get_data( 'disabled_custom_post_types', $settings_data, $infinity23_defaults );
			$disabled_cpt_array = array();

			if( !empty( $disabled_cpt_csv ) ) {

				foreach( explode( ',', $disabled_cpt_csv ) as $single_disabled_slug ) {

					$single_disabled_slug = trim( $single_disabled_slug );

					if( !empty( $single_disabled_slug ) ) {
						$disabled_cpt_array[] = $single_disabled_slug;
					} // if( !empty( $single_disabled_slug ) ) {

				} // foreach( explode( ',', $disabled_cpt_csv ) as $single_disabled_slug ) {

			} // if( !empty( $disabled_cpt_csv ) ) {

			if( !is_array( $saved_types ) ) {
				$saved_types = array();
			} // if( !is_array( $saved_types ) ) {

			$builtin_type_keys = infinity23_folders_organizer_get_builtin_type_keys();
			$enabled_types_cache = array();

			foreach( infinity23_folders_organizer_get_content_types() as $type_key => $type_data ) {

				if( in_array( $type_key, $builtin_type_keys, true ) ) {

					$type_state = isset( $saved_types[ $type_key ] ) ? intval( $saved_types[ $type_key ] ) : $default_state;

					if( $type_state === 1 ) {
						$enabled_types_cache[ $type_key ] = $type_data;
					} // if( $type_state === 1 ) {

				} else {

					if( $custom_post_types_active === 1 && !in_array( $type_key, $disabled_cpt_array, true ) ) {
						$enabled_types_cache[ $type_key ] = $type_data;
					} // if( $custom_post_types_active === 1 && !in_array(

				} // if( in_array( $type_key, $builtin_type_keys, true ) ) {

			} // foreach( infinity23_folders_organizer_get_content_types() as $type_key => $type_data ) {

			return $enabled_types_cache;

		} // function infinity23_folders_organizer_get_enabled_content_types() {

	} // if( !function_exists( 'infinity23_folders_organizer_get_enabled_content_types' ) ) {

	if( !function_exists( 'infinity23_folders_organizer_is_content_type_enabled' ) ) {

		function infinity23_folders_organizer_is_content_type_enabled( $content_type ) {

			$enabled_types = infinity23_folders_organizer_get_enabled_content_types();

			return isset( $enabled_types[ $content_type ] );

		} // function infinity23_folders_organizer_is_content_type_enabled( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_is_content_type_enabled' ) ) {

	// ---------------------------------------
	// can the current user use the folders of this content type
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_user_can_use' ) ) {

		function infinity23_folders_organizer_user_can_use( $content_type ) {

			$content_types = infinity23_folders_organizer_get_content_types();

			if( !isset( $content_types[ $content_type ] ) ) {
				return false;
			} // if( !isset( $content_types[ $content_type ] ) ) {

			return current_user_can( $content_types[ $content_type ]['capability'] );

		} // function infinity23_folders_organizer_user_can_use( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_user_can_use' ) ) {

	// ---------------------------------------
	// can the current user rename/delete the folders of this content type -
	// folders are shared between all users (not per user), so this needs the
	// post type's own edit_others_posts capability, not just the basic one
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_user_can_manage' ) ) {

		function infinity23_folders_organizer_user_can_manage( $content_type ) {

			if( !infinity23_folders_organizer_user_can_use( $content_type ) ) {
				return false;
			} // if( !infinity23_folders_organizer_user_can_use( $content_type ) ) {

			$content_types = infinity23_folders_organizer_get_content_types();
			$post_type_object = get_post_type_object( $content_types[ $content_type ]['post_type'] );
			$manage_capability = ( !empty( $post_type_object ) && isset( $post_type_object->cap->edit_others_posts ) ) ? $post_type_object->cap->edit_others_posts : 'edit_others_posts';

			return current_user_can( $manage_capability );

		} // function infinity23_folders_organizer_user_can_manage( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_user_can_manage' ) ) {

	// ---------------------------------------
	// keep only the item ids the current user is allowed to edit - used before
	// any move into / remove from a folder, so nobody can refile other users' items
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_filter_editable_ids' ) ) {

		function infinity23_folders_organizer_filter_editable_ids( $post_ids ) {

			$editable_ids = array();

			foreach( $post_ids as $single_post_id ) {

				$single_post_id = intval( $single_post_id );

				if( !empty( $single_post_id ) && current_user_can( 'edit_post', $single_post_id ) ) {
					$editable_ids[] = $single_post_id;
				} // if( !empty( $single_post_id ) && current_user_can( 'edit_post', $single_post_id ) ) {

			} // foreach( $post_ids as $single_post_id ) {

			return $editable_ids;

		} // function infinity23_folders_organizer_filter_editable_ids( $post_ids ) {

	} // if( !function_exists( 'infinity23_folders_organizer_filter_editable_ids' ) ) {

	// ---------------------------------------
	// which enabled content type the admin list screen being viewed belongs
	// to - upload.php is images, edit.php is resolved by its post_type.
	// returns an empty string on every other screen or when the type is off
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_current_content_type' ) ) {

		function infinity23_folders_organizer_get_current_content_type() {

			global $pagenow;

			static $current_type_cache = null;

			if( $current_type_cache !== null ) {
				return $current_type_cache;
			} // if( $current_type_cache !== null ) {

			$current_type_cache = '';

			if( !is_admin() ) {
				return $current_type_cache;
			} // if( !is_admin() ) {

			if( $pagenow === 'upload.php' ) {
				$post_type = 'attachment';
			} elseif( $pagenow === 'edit.php' ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only: just detects which admin list-table screen is being viewed, no state change
				$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : 'post';
			} else {
				return $current_type_cache;
			} // if( $pagenow === 'upload.php' ) {

			$content_type = infinity23_folders_organizer_get_content_type_for_post_type( $post_type );

			if( !empty( $content_type ) && infinity23_folders_organizer_is_content_type_enabled( $content_type ) ) {
				$current_type_cache = $content_type;
			} // if( !empty( $content_type ) && infinity23_folders_organizer_is_content_type_enabled( $content_type ) ) {

			return $current_type_cache;

		} // function infinity23_folders_organizer_get_current_content_type() {

	} // if( !function_exists( 'infinity23_folders_organizer_get_current_content_type' ) ) {

?>
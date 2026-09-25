<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Duplicate Pages - duplicate pages, posts and custom post types
	// ===========================================================================

	include( dirname(__FILE__) . "/../../../include/settings.php" );

	// ===========================================================================
	// Helper functions
	// ===========================================================================

	// ---------------------------------------
	// all saved settings of this module in one array (cached per request)
	// ---------------------------------------

	if( !function_exists( 'infinity23_duplicate_pages_get_settings' ) ) {

		function infinity23_duplicate_pages_get_settings() {

			static $infinity23_dp_cached_settings = null;

			if( $infinity23_dp_cached_settings !== null ) {
				return $infinity23_dp_cached_settings;
			} // if( $infinity23_dp_cached_settings !== null ) {

			global $infinity23_essential_modules_array;

			include( dirname(__FILE__) . "/../default-values.php" );

			$infinity23_dp_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_essential_modules_array['duplicate_pages']['settings_array_db_name'] ) );
			$infinity23_dp_settings = array();

			foreach( $infinity23_defaults as $infinity23_dp_field_key => $infinity23_dp_field_data ) {

				$infinity23_dp_settings[ $infinity23_dp_field_key ] = infinity23_pro_get_data( $infinity23_dp_field_key, $infinity23_dp_data_array, $infinity23_defaults );

				if( $infinity23_dp_field_data['type'] === 'intval' ) {
					$infinity23_dp_settings[ $infinity23_dp_field_key ] = intval( $infinity23_dp_settings[ $infinity23_dp_field_key ] );
				} // if( $infinity23_dp_field_data['type'] === 'intval' ) {

			} // foreach( $infinity23_defaults as $infinity23_dp_field_key => $infinity23_dp_field_data ) {

			if( !in_array( $infinity23_dp_settings['redirect_after'], array( 'list', 'editor' ), true ) ) {
				$infinity23_dp_settings['redirect_after'] = $infinity23_defaults['redirect_after']['value'];
			} // if( !in_array( $infinity23_dp_settings['redirect_after'], array( 'list', 'editor' ), true ) ) {

			// comma separated text lists become arrays
			$infinity23_dp_settings['disabled_custom_post_types'] = array_filter( array_map( 'trim', explode( ',', $infinity23_dp_settings['disabled_custom_post_types'] ) ) );
			$infinity23_dp_settings['excluded_custom_fields'] = array_filter( array_map( 'trim', explode( ',', $infinity23_dp_settings['excluded_custom_fields'] ) ) );

			$infinity23_dp_cached_settings = $infinity23_dp_settings;

			return $infinity23_dp_cached_settings;

		} // function infinity23_duplicate_pages_get_settings() {

	} // if( !function_exists( 'infinity23_duplicate_pages_get_settings' ) ) {

	// ---------------------------------------
	// is the duplicate link turned on for this post type
	// ---------------------------------------

	if( !function_exists( 'infinity23_duplicate_pages_is_type_enabled' ) ) {

		function infinity23_duplicate_pages_is_type_enabled( $post_type ) {

			$infinity23_dp_settings = infinity23_duplicate_pages_get_settings();

			if( $post_type === 'page' ) {
				return ( $infinity23_dp_settings['show_on_pages'] == 1 );
			} // if( $post_type === 'page' ) {

			if( $post_type === 'post' ) {
				return ( $infinity23_dp_settings['show_on_posts'] == 1 );
			} // if( $post_type === 'post' ) {

			if( $infinity23_dp_settings['show_on_custom_post_types'] != 1 ) {
				return false;
			} // if( $infinity23_dp_settings['show_on_custom_post_types'] != 1 ) {

			if( in_array( $post_type, $infinity23_dp_settings['disabled_custom_post_types'], true ) ) {
				return false;
			} // if( in_array( $post_type, $infinity23_dp_settings['disabled_custom_post_types'], true ) ) {

			// same list the settings page shows - custom post types with an admin screen
			$infinity23_dp_post_type_object = get_post_type_object( $post_type );

			return ( $infinity23_dp_post_type_object && $infinity23_dp_post_type_object->show_ui && !$infinity23_dp_post_type_object->_builtin );

		} // function infinity23_duplicate_pages_is_type_enabled( $post_type ) {

	} // if( !function_exists( 'infinity23_duplicate_pages_is_type_enabled' ) ) {

	// ---------------------------------------
	// can the current user duplicate this post
	// ---------------------------------------

	if( !function_exists( 'infinity23_duplicate_pages_user_can_duplicate' ) ) {

		function infinity23_duplicate_pages_user_can_duplicate( $post ) {

			$post = get_post( $post );

			if( !$post ) {
				return false;
			} // if( !$post ) {

			if( in_array( $post->post_status, array( 'trash', 'auto-draft' ), true ) ) {
				return false;
			} // if( in_array( $post->post_status, array( 'trash', 'auto-draft' ), true ) ) {

			if( !infinity23_duplicate_pages_is_type_enabled( $post->post_type ) ) {
				return false;
			} // if( !infinity23_duplicate_pages_is_type_enabled( $post->post_type ) ) {

			$infinity23_dp_post_type_object = get_post_type_object( $post->post_type );

			if( !$infinity23_dp_post_type_object ) {
				return false;
			} // if( !$infinity23_dp_post_type_object ) {

			return ( current_user_can( 'edit_post', $post->ID ) && current_user_can( $infinity23_dp_post_type_object->cap->create_posts ) );

		} // function infinity23_duplicate_pages_user_can_duplicate( $post ) {

	} // if( !function_exists( 'infinity23_duplicate_pages_user_can_duplicate' ) ) {

	// ---------------------------------------
	// the link that duplicates one post, with its own nonce for that post
	// ---------------------------------------

	if( !function_exists( 'infinity23_duplicate_pages_get_link' ) ) {

		function infinity23_duplicate_pages_get_link( $post_id ) {

			return add_query_arg(
				array(
					'action' => 'infinity23_duplicate_page',
					'infinity23_dp_post' => $post_id,
					'infinity23_dp_nonce' => wp_create_nonce( 'infinity23_duplicate_page_' . $post_id ),
				),
				admin_url( 'admin-post.php' )
			);

		} // function infinity23_duplicate_pages_get_link( $post_id ) {

	} // if( !function_exists( 'infinity23_duplicate_pages_get_link' ) ) {

	// ---------------------------------------
	// does a custom field name match the "do not copy" list, * is a wildcard
	// ---------------------------------------

	if( !function_exists( 'infinity23_duplicate_pages_meta_key_is_excluded' ) ) {

		function infinity23_duplicate_pages_meta_key_is_excluded( $meta_key, $excluded_patterns ) {

			foreach( $excluded_patterns as $excluded_pattern ) {

				if( strpos( $excluded_pattern, '*' ) === false ) {

					if( $excluded_pattern === $meta_key ) {
						return true;
					} // if( $excluded_pattern === $meta_key ) {

				} else {

					$excluded_regex = '#^' . str_replace( '\*', '.*', preg_quote( $excluded_pattern, '#' ) ) . '$#';

					if( preg_match( $excluded_regex, $meta_key ) ) {
						return true;
					} // if( preg_match( $excluded_regex, $meta_key ) ) {

				} // if( strpos( $excluded_pattern, '*' ) === false ) {

			} // foreach( $excluded_patterns as $excluded_pattern ) {

			return false;

		} // function infinity23_duplicate_pages_meta_key_is_excluded( $meta_key, $excluded_patterns ) {

	} // if( !function_exists( 'infinity23_duplicate_pages_meta_key_is_excluded' ) ) {

	// ---------------------------------------
	// does this value hold an object anywhere inside it - a serialized object in
	// a custom field is never real page data, it is only there to be unserialized
	// by something later, so the copy skips it
	// ---------------------------------------

	if( !function_exists( 'infinity23_duplicate_pages_value_has_object' ) ) {

		function infinity23_duplicate_pages_value_has_object( $value ) {

			if( is_object( $value ) ) {
				return true;
			} // if( is_object( $value ) ) {

			if( is_array( $value ) ) {

				foreach( $value as $single_value ) {

					if( infinity23_duplicate_pages_value_has_object( $single_value ) ) {
						return true;
					} // if( infinity23_duplicate_pages_value_has_object( $single_value ) ) {

				} // foreach( $value as $single_value ) {

			} // if( is_array( $value ) ) {

			return false;

		} // function infinity23_duplicate_pages_value_has_object( $value ) {

	} // if( !function_exists( 'infinity23_duplicate_pages_value_has_object' ) ) {

	// ---------------------------------------
	// is a custom field saved by an SEO plugin - Yoast SEO and other
	// plugins that keep their SEO data in custom fields
	// ---------------------------------------

	if( !function_exists( 'infinity23_duplicate_pages_is_seo_meta_key' ) ) {

		function infinity23_duplicate_pages_is_seo_meta_key( $meta_key ) {

			$seo_prefixes = array( '_yoast_wpseo_', 'rank_math_', '_seopress_', '_aioseop_', '_genesis_', '_tsf_', '_open_graph_', '_twitter_', '_social_image_', '_primary_term_' );

			// a few SEO fields are saved with no prefix at all
			$seo_exact_keys = array( 'redirect', 'exclude_local_search', 'exclude_from_archive' );

			if( in_array( $meta_key, $seo_exact_keys, true ) ) {
				return true;
			} // if( in_array( $meta_key, $seo_exact_keys, true ) ) {

			foreach( $seo_prefixes as $seo_prefix ) {

				if( strpos( $meta_key, $seo_prefix ) === 0 ) {
					return true;
				} // if( strpos( $meta_key, $seo_prefix ) === 0 ) {

			} // foreach( $seo_prefixes as $seo_prefix ) {

			return false;

		} // function infinity23_duplicate_pages_is_seo_meta_key( $meta_key ) {

	} // if( !function_exists( 'infinity23_duplicate_pages_is_seo_meta_key' ) ) {

	// ===========================================================================
	// Creating the copy
	// ===========================================================================

	// ---------------------------------------
	// duplicate one post according to the saved settings, returns the new
	// post id or 0 when the copy could not be created
	// ---------------------------------------

	if( !function_exists( 'infinity23_duplicate_pages_create_copy' ) ) {

		function infinity23_duplicate_pages_create_copy( $post ) {

			$infinity23_dp_settings = infinity23_duplicate_pages_get_settings();
			$infinity23_dp_post_type_object = get_post_type_object( $post->post_type );

			// ---------------------------------------
			// title
			// ---------------------------------------

			$infinity23_dp_new_title = '';

			if( $infinity23_dp_settings['copy_title'] == 1 ) {

				$infinity23_dp_title_prefix = $infinity23_dp_settings['title_prefix'];
				$infinity23_dp_title_suffix = $infinity23_dp_settings['title_suffix'];

				if( $infinity23_dp_title_prefix !== '' ) {
					$infinity23_dp_title_prefix .= ' ';
				} // if( $infinity23_dp_title_prefix !== '' ) {

				if( $infinity23_dp_title_suffix !== '' ) {
					$infinity23_dp_title_suffix = ' ' . $infinity23_dp_title_suffix;
				} // if( $infinity23_dp_title_suffix !== '' ) {

				$infinity23_dp_new_title = trim( $infinity23_dp_title_prefix . $post->post_title . $infinity23_dp_title_suffix );

			} // if( $infinity23_dp_settings['copy_title'] == 1 ) {

			// ---------------------------------------
			// status - a copy is a draft unless the original status is kept,
			// and a user who cannot publish gets it as pending instead
			// ---------------------------------------

			$infinity23_dp_new_status = 'draft';

			if( $infinity23_dp_settings['copy_status'] == 1 ) {

				$infinity23_dp_new_status = $post->post_status;

				if( in_array( $infinity23_dp_new_status, array( 'publish', 'future' ), true ) && !current_user_can( $infinity23_dp_post_type_object->cap->publish_posts ) ) {
					$infinity23_dp_new_status = 'pending';
				} // if( in_array( $infinity23_dp_new_status, array( 'publish', 'future' ), true ) && !current_user_can(

				// private status needs the publish capability, same as the editor itself
				if( $infinity23_dp_new_status === 'private' && !current_user_can( $infinity23_dp_post_type_object->cap->publish_posts ) ) {
					$infinity23_dp_new_status = 'draft';
				} // if( $infinity23_dp_new_status === 'private' && !current_user_can(

				// without the original date the copy gets today's date, and WordPress
				// publishes a scheduled post with a past date right away
				if( $infinity23_dp_new_status === 'future' && $infinity23_dp_settings['copy_date'] != 1 ) {
					$infinity23_dp_new_status = 'draft';
				} // if( $infinity23_dp_new_status === 'future' && $infinity23_dp_settings['copy_date'] != 1 ) {

				// a password protected original must never become a visible copy
				// when its password is not copied along with it
				if( $post->post_password !== '' && $infinity23_dp_settings['copy_password'] != 1 && in_array( $infinity23_dp_new_status, array( 'publish', 'future', 'private' ), true ) ) {
					$infinity23_dp_new_status = 'draft';
				} // if( $post->post_password !== '' && $infinity23_dp_settings['copy_password'] != 1 && in_array(

			} // if( $infinity23_dp_settings['copy_status'] == 1 ) {

			// ---------------------------------------
			// author - the original author only for a user who can edit other people's items
			// ---------------------------------------

			$infinity23_dp_new_author = get_current_user_id();

			if( $infinity23_dp_settings['copy_author'] == 1 && current_user_can( $infinity23_dp_post_type_object->cap->edit_others_posts ) ) {
				$infinity23_dp_new_author = $post->post_author;
			} // if( $infinity23_dp_settings['copy_author'] == 1 && current_user_can(

			// ---------------------------------------
			// the new post data
			// ---------------------------------------

			$infinity23_dp_new_post = array(
				'post_author' => $infinity23_dp_new_author,
				'post_content' => ( $infinity23_dp_settings['copy_content'] == 1 ) ? $post->post_content : '',
				'post_content_filtered' => ( $infinity23_dp_settings['copy_content'] == 1 ) ? $post->post_content_filtered : '',
				'post_title' => $infinity23_dp_new_title,
				'post_excerpt' => ( $infinity23_dp_settings['copy_excerpt'] == 1 ) ? $post->post_excerpt : '',
				'post_status' => $infinity23_dp_new_status,
				'post_type' => $post->post_type,
				'comment_status' => $post->comment_status,
				'ping_status' => $post->ping_status,
				'post_password' => ( $infinity23_dp_settings['copy_password'] == 1 ) ? $post->post_password : '',
				'post_name' => ( $infinity23_dp_settings['copy_slug'] == 1 ) ? $post->post_name : '',
				'post_parent' => $post->post_parent,
				'menu_order' => ( $infinity23_dp_settings['copy_menu_order'] == 1 ) ? $post->menu_order : 0,
			);

			// the modified date is set to now, otherwise the editor would show the old date as the last edit
			$infinity23_dp_set_modified = function( $data ) {

				$data['post_modified'] = current_time( 'mysql' );
				$data['post_modified_gmt'] = current_time( 'mysql', 1 );

				return $data;

			}; // $infinity23_dp_set_modified = function( $data ) {

			if( $infinity23_dp_settings['copy_date'] == 1 ) {

				$infinity23_dp_new_post['post_date'] = $post->post_date;
				$infinity23_dp_new_post['post_date_gmt'] = get_gmt_from_date( $post->post_date );

				add_filter( 'wp_insert_post_data', $infinity23_dp_set_modified, 1, 1 );

			} // if( $infinity23_dp_settings['copy_date'] == 1 ) {

			$infinity23_dp_new_id = wp_insert_post( wp_slash( $infinity23_dp_new_post ), true );

			if( $infinity23_dp_settings['copy_date'] == 1 ) {
				remove_filter( 'wp_insert_post_data', $infinity23_dp_set_modified, 1 );
			} // if( $infinity23_dp_settings['copy_date'] == 1 ) {

			if( is_wp_error( $infinity23_dp_new_id ) || empty( $infinity23_dp_new_id ) ) {
				return 0;
			} // if( is_wp_error( $infinity23_dp_new_id ) || empty( $infinity23_dp_new_id ) ) {

			// ---------------------------------------
			// taxonomies - categories, tags, custom taxonomies and the post format
			// ---------------------------------------

			$infinity23_dp_taxonomies = get_object_taxonomies( $post->post_type );

			// some plugins add support for post formats without registering the post_format taxonomy
			if( post_type_supports( $post->post_type, 'post-formats' ) && !in_array( 'post_format', $infinity23_dp_taxonomies, true ) ) {
				$infinity23_dp_taxonomies[] = 'post_format';
			} // if( post_type_supports( $post->post_type, 'post-formats' ) && !in_array(

			foreach( $infinity23_dp_taxonomies as $infinity23_dp_taxonomy ) {

				if( $infinity23_dp_taxonomy === 'post_format' ) {

					if( $infinity23_dp_settings['copy_format'] != 1 ) { continue; }

				} elseif( $infinity23_dp_settings['copy_taxonomies'] != 1 ) {
					continue;
				} // if( $infinity23_dp_taxonomy === 'post_format' ) {

				$infinity23_dp_term_slugs = wp_get_object_terms( $post->ID, $infinity23_dp_taxonomy, array( 'fields' => 'slugs', 'orderby' => 'term_order' ) );

				if( is_wp_error( $infinity23_dp_term_slugs ) ) { continue; }

				// an empty list also removes the default category WordPress adds to a new post
				wp_set_object_terms( $infinity23_dp_new_id, $infinity23_dp_term_slugs, $infinity23_dp_taxonomy );

			} // foreach( $infinity23_dp_taxonomies as $infinity23_dp_taxonomy ) {

			// ---------------------------------------
			// custom fields - featured image and template have their own
			// settings, SEO plugin fields have theirs, everything else
			// (ACF, page builders, etc) follows the custom fields setting
			// ---------------------------------------

			$infinity23_dp_meta_data = get_post_custom( $post->ID );

			// WordPress internal fields that belong to the original only
			$infinity23_dp_never_copy_fields = array( '_edit_lock', '_edit_last', '_wp_old_slug', '_wp_old_date', '_wp_trash_meta_status', '_wp_trash_meta_time', '_wp_desired_post_slug', '_pingme', '_encloseme' );

			if( !empty( $infinity23_dp_meta_data ) ) {

				foreach( $infinity23_dp_meta_data as $infinity23_dp_meta_key => $infinity23_dp_meta_values ) {

					if( in_array( $infinity23_dp_meta_key, $infinity23_dp_never_copy_fields, true ) ) { continue; }

					if( infinity23_duplicate_pages_meta_key_is_excluded( $infinity23_dp_meta_key, $infinity23_dp_settings['excluded_custom_fields'] ) ) { continue; }

					if( $infinity23_dp_meta_key === '_thumbnail_id' ) {

						if( $infinity23_dp_settings['copy_thumbnail'] != 1 ) { continue; }

					} elseif( $infinity23_dp_meta_key === '_wp_page_template' ) {

						if( $infinity23_dp_settings['copy_template'] != 1 ) { continue; }

					} elseif( infinity23_duplicate_pages_is_seo_meta_key( $infinity23_dp_meta_key ) ) {

						if( $infinity23_dp_settings['copy_seo_data'] != 1 ) { continue; }

					} elseif( $infinity23_dp_settings['copy_custom_fields'] != 1 ) {
						continue;
					} // if( $infinity23_dp_meta_key === '_thumbnail_id' ) {

					// clear anything another plugin already added so a field with several values is copied exactly
					delete_post_meta( $infinity23_dp_new_id, $infinity23_dp_meta_key );

					foreach( $infinity23_dp_meta_values as $infinity23_dp_meta_value ) {

						$infinity23_dp_meta_value_to_copy = $infinity23_dp_meta_value;

						if( is_serialized( $infinity23_dp_meta_value ) ) {

							// allowed_classes => false, so a crafted serialized object saved in a custom
							// field by any user who can edit a post can never be instantiated here.
							// maybe_unserialize() has no such guard, which is why it is not used
							// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize -- unserialize() with allowed_classes => false is the safe form and is deliberate here
							$infinity23_dp_meta_value_to_copy = unserialize( $infinity23_dp_meta_value, array( 'allowed_classes' => false ) );

							if( $infinity23_dp_meta_value_to_copy === false && $infinity23_dp_meta_value !== 'b:0;' ) { continue; }

							if( infinity23_duplicate_pages_value_has_object( $infinity23_dp_meta_value_to_copy ) ) { continue; }

						} // if( is_serialized( $infinity23_dp_meta_value ) ) {

						add_post_meta( $infinity23_dp_new_id, $infinity23_dp_meta_key, wp_slash( $infinity23_dp_meta_value_to_copy ) );

					} // foreach( $infinity23_dp_meta_values as $infinity23_dp_meta_value ) {

				} // foreach( $infinity23_dp_meta_data as $infinity23_dp_meta_key => $infinity23_dp_meta_values ) {

			} // if( !empty( $infinity23_dp_meta_data ) ) {

			return $infinity23_dp_new_id;

		} // function infinity23_duplicate_pages_create_copy( $post ) {

	} // if( !function_exists( 'infinity23_duplicate_pages_create_copy' ) ) {

	// ===========================================================================
	// Single item duplicate link (post list row and admin bar) - admin-post.php handler
	// ===========================================================================

	if( !function_exists( 'infinity23_duplicate_pages_handle_link' ) ) {

		function infinity23_duplicate_pages_handle_link() {

			$infinity23_dp_post_id = isset( $_GET['infinity23_dp_post'] ) ? absint( $_GET['infinity23_dp_post'] ) : 0;
			$infinity23_dp_nonce = isset( $_GET['infinity23_dp_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_dp_nonce'] ) ) : '';

			if( empty( $infinity23_dp_post_id ) || !wp_verify_nonce( $infinity23_dp_nonce, 'infinity23_duplicate_page_' . $infinity23_dp_post_id ) ) {
				wp_die( 'Link expired.' );
			} // if( empty( $infinity23_dp_post_id ) || !wp_verify_nonce(

			$infinity23_dp_post = get_post( $infinity23_dp_post_id );

			if( !$infinity23_dp_post || !infinity23_duplicate_pages_user_can_duplicate( $infinity23_dp_post ) ) {
				wp_die( 'Permission denied.' );
			} // if( !$infinity23_dp_post || !infinity23_duplicate_pages_user_can_duplicate( $infinity23_dp_post ) ) {

			$infinity23_dp_new_id = infinity23_duplicate_pages_create_copy( $infinity23_dp_post );

			if( empty( $infinity23_dp_new_id ) ) {
				wp_die( 'The copy could not be created.' );
			} // if( empty( $infinity23_dp_new_id ) ) {

			$infinity23_dp_settings = infinity23_duplicate_pages_get_settings();

			$infinity23_dp_redirect_url = '';

			if( $infinity23_dp_settings['redirect_after'] === 'editor' ) {
				$infinity23_dp_redirect_url = get_edit_post_link( $infinity23_dp_new_id, 'raw' );
			} // if( $infinity23_dp_settings['redirect_after'] === 'editor' ) {

			if( empty( $infinity23_dp_redirect_url ) ) {
				$infinity23_dp_redirect_url = add_query_arg( array( 'post_type' => $infinity23_dp_post->post_type, 'infinity23_duplicated' => 1 ), admin_url( 'edit.php' ) );
			} // if( empty( $infinity23_dp_redirect_url ) ) {

			infinity23_safe_redirect( $infinity23_dp_redirect_url );

		} // function infinity23_duplicate_pages_handle_link() {

	} // if( !function_exists( 'infinity23_duplicate_pages_handle_link' ) ) {

	// ===========================================================================
	// Post list row link
	// ===========================================================================

	if( !function_exists( 'infinity23_duplicate_pages_add_row_action' ) ) {

		function infinity23_duplicate_pages_add_row_action( $actions, $post ) {

			if( !infinity23_duplicate_pages_user_can_duplicate( $post ) ) {
				return $actions;
			} // if( !infinity23_duplicate_pages_user_can_duplicate( $post ) ) {

			$actions['infinity23_duplicate'] = '<a href="' . esc_url( infinity23_duplicate_pages_get_link( $post->ID ) ) . '">Duplicate</a>';

			return $actions;

		} // function infinity23_duplicate_pages_add_row_action( $actions, $post ) {

	} // if( !function_exists( 'infinity23_duplicate_pages_add_row_action' ) ) {

	// ===========================================================================
	// Bulk actions
	// ===========================================================================

	if( !function_exists( 'infinity23_duplicate_pages_add_bulk_action' ) ) {

		function infinity23_duplicate_pages_add_bulk_action( $bulk_actions ) {

			$bulk_actions['infinity23_duplicate'] = 'Duplicate';

			return $bulk_actions;

		} // function infinity23_duplicate_pages_add_bulk_action( $bulk_actions ) {

	} // if( !function_exists( 'infinity23_duplicate_pages_add_bulk_action' ) ) {

	// the bulk actions form is already verified by WordPress (bulk-posts nonce) before this runs
	if( !function_exists( 'infinity23_duplicate_pages_handle_bulk_action' ) ) {

		function infinity23_duplicate_pages_handle_bulk_action( $redirect_url, $action, $post_ids ) {

			if( $action !== 'infinity23_duplicate' ) {
				return $redirect_url;
			} // if( $action !== 'infinity23_duplicate' ) {

			$infinity23_dp_copied_count = 0;

			foreach( $post_ids as $infinity23_dp_bulk_post_id ) {

				$infinity23_dp_bulk_post = get_post( absint( $infinity23_dp_bulk_post_id ) );

				if( !$infinity23_dp_bulk_post || !infinity23_duplicate_pages_user_can_duplicate( $infinity23_dp_bulk_post ) ) { continue; }

				if( infinity23_duplicate_pages_create_copy( $infinity23_dp_bulk_post ) ) {
					$infinity23_dp_copied_count++;
				} // if( infinity23_duplicate_pages_create_copy( $infinity23_dp_bulk_post ) ) {

			} // foreach( $post_ids as $infinity23_dp_bulk_post_id ) {

			return add_query_arg( 'infinity23_duplicated', $infinity23_dp_copied_count, remove_query_arg( 'infinity23_duplicated', $redirect_url ) );

		} // function infinity23_duplicate_pages_handle_bulk_action( $redirect_url, $action, $post_ids ) {

	} // if( !function_exists( 'infinity23_duplicate_pages_handle_bulk_action' ) ) {

	// ===========================================================================
	// Admin bar link - on the edit screen and on the front-end of the page
	// ===========================================================================

	if( !function_exists( 'infinity23_duplicate_pages_add_admin_bar_link' ) ) {

		function infinity23_duplicate_pages_add_admin_bar_link( $wp_admin_bar ) {

			if( !is_user_logged_in() ) { return; }

			$infinity23_dp_bar_post = null;

			if( is_admin() ) {

				$infinity23_dp_screen = get_current_screen();

				if( $infinity23_dp_screen && $infinity23_dp_screen->base === 'post' && $infinity23_dp_screen->action !== 'add' ) {
					$infinity23_dp_bar_post = get_post();
				} // if( $infinity23_dp_screen && $infinity23_dp_screen->base === 'post' && $infinity23_dp_screen->action !== 'add' ) {

			} elseif( is_singular() ) {
				$infinity23_dp_bar_post = get_queried_object();
			} // if( is_admin() ) {

			if( !( $infinity23_dp_bar_post instanceof WP_Post ) || !infinity23_duplicate_pages_user_can_duplicate( $infinity23_dp_bar_post ) ) { return; }

			$wp_admin_bar->add_node( array(
				'id' => 'infinity23-duplicate-page',
				'title' => 'Duplicate',
				'href' => infinity23_duplicate_pages_get_link( $infinity23_dp_bar_post->ID ),
			) );

		} // function infinity23_duplicate_pages_add_admin_bar_link( $wp_admin_bar ) {

	} // if( !function_exists( 'infinity23_duplicate_pages_add_admin_bar_link' ) ) {

	// ===========================================================================
	// Wiring everything - functions above must be declared before this point
	// ===========================================================================

	$infinity23_dp_active_settings = infinity23_duplicate_pages_get_settings();

	// ---------------------------------------
	// admin area only
	// ---------------------------------------

	if( is_admin() ) {

		add_action( 'admin_post_infinity23_duplicate_page', 'infinity23_duplicate_pages_handle_link' );

		// success message after a duplicate - display only, nothing is changed by this request
		add_action( 'admin_notices', function() {

			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display-only count in the url, nothing is saved or changed
			$infinity23_dp_notice_count = isset( $_GET['infinity23_duplicated'] ) ? absint( $_GET['infinity23_duplicated'] ) : 0;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			if( $infinity23_dp_notice_count < 1 ) { return; }

			$infinity23_dp_notice_text = ( $infinity23_dp_notice_count == 1 ) ? '1 item was duplicated successfully.' : $infinity23_dp_notice_count . ' items were duplicated successfully.';

			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $infinity23_dp_notice_text ) . '</p></div>';

		} ); // add_action( 'admin_notices'

		if( $infinity23_dp_active_settings['show_in_post_list'] == 1 ) {

			add_filter( 'post_row_actions', 'infinity23_duplicate_pages_add_row_action', 10, 2 );
			add_filter( 'page_row_actions', 'infinity23_duplicate_pages_add_row_action', 10, 2 );

		} // if( $infinity23_dp_active_settings['show_in_post_list'] == 1 ) {

		if( $infinity23_dp_active_settings['show_in_bulk_actions'] == 1 ) {

			add_action( 'admin_init', function() {

				$infinity23_dp_all_post_types = get_post_types( array( 'show_ui' => true ), 'names' );

				foreach( $infinity23_dp_all_post_types as $infinity23_dp_single_post_type ) {

					if( !infinity23_duplicate_pages_is_type_enabled( $infinity23_dp_single_post_type ) ) { continue; }

					add_filter( 'bulk_actions-edit-' . $infinity23_dp_single_post_type, 'infinity23_duplicate_pages_add_bulk_action' );
					add_filter( 'handle_bulk_actions-edit-' . $infinity23_dp_single_post_type, 'infinity23_duplicate_pages_handle_bulk_action', 10, 3 );

				} // foreach( $infinity23_dp_all_post_types as $infinity23_dp_single_post_type ) {

			} ); // add_action( 'admin_init'

		} // if( $infinity23_dp_active_settings['show_in_bulk_actions'] == 1 ) {

	} // if( is_admin() ) {

	// ---------------------------------------
	// admin bar - admin area and front-end
	// ---------------------------------------

	if( $infinity23_dp_active_settings['show_in_admin_bar'] == 1 ) {
		add_action( 'admin_bar_menu', 'infinity23_duplicate_pages_add_admin_bar_link', 90 );
	} // if( $infinity23_dp_active_settings['show_in_admin_bar'] == 1 ) {

?>

<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Folders Organizer - remove a permanently deleted item from every folder of
// its content type, so saved folder lists never keep ids of items that are
// gone. loaded outside is_admin() on purpose - auto-drafts and trash are
// emptied by wp-cron, which can run on a front-end request
// ===========================================================================

	add_action( 'delete_post', function( $post_id ) {

		$post = get_post( $post_id );

		if( empty( $post ) ) { return; }

		if( in_array( $post->post_type, array( 'revision', 'nav_menu_item', 'customize_changeset', 'oembed_cache', 'user_request' ), true ) ) { return; }

		$content_type = infinity23_folders_organizer_get_content_type_for_post_type( $post->post_type );

		if( empty( $content_type ) ) { return; }

		infinity23_folders_organizer_remove_item_from_all_folders( $post_id, $content_type );

	} ); // add_action( 'delete_post'

?>
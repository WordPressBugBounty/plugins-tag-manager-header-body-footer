<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Folders Organizer - shared sidebar system for every admin list screen
// (Media Library, Pages, Posts and every custom post type). One render
// function, one list filter, one bulk action set and one ajax set - all
// driven by the content type of the screen being viewed, so a change here
// applies to every screen at once. Screen specific extras live in
// folders-media-library.php and folders-pages-posts.php.
// ===========================================================================

	// ---------------------------------------
	// helper - reads the saved sidebar position ('left' or 'right') and
	// returns the matching wrap CSS class
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_sidebar_position_class' ) ) {

		function infinity23_folders_organizer_get_sidebar_position_class() {

			include( dirname(__FILE__) . "/../internal-settings.php" );
			include( dirname(__FILE__) . "/../default-values.php" );
			$settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
			$sidebar_position = infinity23_css_sanitize( infinity23_pro_get_data( 'sidebar_position', $settings_data, $infinity23_defaults ), 'option', 'left', array( 'left', 'right' ) );

			return ( $sidebar_position === 'right' ) ? 'infinity23-fo-sidebar-right' : 'infinity23-fo-sidebar-left';

		} // function infinity23_folders_organizer_get_sidebar_position_class() {

	} // if( !function_exists( 'infinity23_folders_organizer_get_sidebar_position_class' ) ) {

	// ---------------------------------------
	// helper - reads the saved sidebar width
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_sidebar_width' ) ) {

		function infinity23_folders_organizer_get_sidebar_width() {

			include( dirname(__FILE__) . "/../internal-settings.php" );
			include( dirname(__FILE__) . "/../default-values.php" );
			$settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );
			$sidebar_width = infinity23_css_sanitize( infinity23_pro_get_data( 'sidebar_width', $settings_data, $infinity23_defaults ), 'size', '200px' );

			return $sidebar_width;

		} // function infinity23_folders_organizer_get_sidebar_width() {

	} // if( !function_exists( 'infinity23_folders_organizer_get_sidebar_width' ) ) {

	// ---------------------------------------
	// helper - the list screen url of a content type, used as the base of
	// every folder link in the sidebar
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_screen_base_url' ) ) {

		function infinity23_folders_organizer_get_screen_base_url( $content_type ) {

			$post_type = infinity23_folders_organizer_get_post_type_for_content_type( $content_type );

			if( $post_type === 'attachment' ) {
				return admin_url( 'upload.php?mode=list' );
			} // if( $post_type === 'attachment' ) {

			return admin_url( 'edit.php?post_type=' . $post_type );

		} // function infinity23_folders_organizer_get_screen_base_url( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_get_screen_base_url' ) ) {

	// ---------------------------------------
	// helper - the folder being viewed ('all', 'unassigned' or a folder id)
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_get_current_folder_param' ) ) {

		function infinity23_folders_organizer_get_current_folder_param() {

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only: which folder tab is being viewed, no state change
			return isset( $_GET['infinity23_folder'] ) ? sanitize_text_field( wp_unslash( $_GET['infinity23_folder'] ) ) : 'all';

		} // function infinity23_folders_organizer_get_current_folder_param() {

	} // if( !function_exists( 'infinity23_folders_organizer_get_current_folder_param' ) ) {

	// ---------------------------------------
	// body class - lets the shared CSS target only screens that have the sidebar
	// ---------------------------------------

	add_filter( 'admin_body_class', function( $classes ) {

		$content_type = infinity23_folders_organizer_get_current_content_type();

		if( empty( $content_type ) || !infinity23_folders_organizer_user_can_use( $content_type ) ) { return $classes; }

		return $classes . ' infinity23-fo-has-sidebar';

	} );

	// ---------------------------------------
	// buffer the whole admin page output and splice the folders sidebar in
	// as a real sibling of #posts-filter, so it renders beside the list
	// table on the very first paint - no JS-driven DOM move, no layout shift.
	// priority 20 so the Media Library list-mode redirect (priority 10) runs first
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_start_sidebar_buffer' ) ) {

		function infinity23_folders_organizer_start_sidebar_buffer() {

			$content_type = infinity23_folders_organizer_get_current_content_type();

			if( empty( $content_type ) ) { return; }
			if( !infinity23_folders_organizer_user_can_use( $content_type ) ) { return; }

			ob_start();

			add_action( 'shutdown', function() use ( $content_type ) {

				$infinity23_fo_page_html = ob_get_clean();
				$infinity23_fo_sidebar_html = infinity23_folders_organizer_render_sidebar( $content_type );

				if( !empty( $infinity23_fo_sidebar_html ) ) {

					$infinity23_fo_form_open_pos = strpos( $infinity23_fo_page_html, '<form id="posts-filter"' );

					if( $infinity23_fo_form_open_pos !== false ) {

						$infinity23_fo_form_close_pos = strpos( $infinity23_fo_page_html, '</form>', $infinity23_fo_form_open_pos );

						if( $infinity23_fo_form_close_pos !== false ) {

							$infinity23_fo_form_close_pos += strlen( '</form>' );

							$infinity23_fo_before_html = substr( $infinity23_fo_page_html, 0, $infinity23_fo_form_open_pos );
							$infinity23_fo_form_html = substr( $infinity23_fo_page_html, $infinity23_fo_form_open_pos, $infinity23_fo_form_close_pos - $infinity23_fo_form_open_pos );
							$infinity23_fo_after_html = substr( $infinity23_fo_page_html, $infinity23_fo_form_close_pos );

							$infinity23_fo_wrap_position_class = infinity23_folders_organizer_get_sidebar_position_class();

							if( $infinity23_fo_wrap_position_class === 'infinity23-fo-sidebar-right' ) {
								$infinity23_fo_wrap_inner_html = $infinity23_fo_form_html . $infinity23_fo_sidebar_html;
							} else {
								$infinity23_fo_wrap_inner_html = $infinity23_fo_sidebar_html . $infinity23_fo_form_html;
							} // if( $infinity23_fo_wrap_position_class === 'infinity23-fo-sidebar-right' ) {

							$infinity23_fo_page_html = $infinity23_fo_before_html . '<div class="infinity23-fo-media-flex-wrap ' . esc_attr( $infinity23_fo_wrap_position_class ) . '">' . $infinity23_fo_wrap_inner_html . '</div><!--infinity23-fo-media-flex-wrap-->' . $infinity23_fo_after_html;

						} // if( $infinity23_fo_form_close_pos !== false ) {

					} // if( $infinity23_fo_form_open_pos !== false ) {

				} // if( !empty( $infinity23_fo_sidebar_html ) ) {

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- full admin page buffer, WordPress core output already escaped
				echo $infinity23_fo_page_html;

			}, 0 ); // add_action( 'shutdown'

		} // function infinity23_folders_organizer_start_sidebar_buffer() {

	} // if( !function_exists( 'infinity23_folders_organizer_start_sidebar_buffer' ) ) {

	add_action( 'load-upload.php', 'infinity23_folders_organizer_start_sidebar_buffer', 20 );
	add_action( 'load-edit.php', 'infinity23_folders_organizer_start_sidebar_buffer', 20 );

	// ---------------------------------------
	// the static CSS/JS already live in the dashboard-tier files (loaded on
	// every admin page) - only the genuinely dynamic pieces (sidebar width,
	// the per-screen ajax config) get computed here and attached to those
	// same handles, scoped to just the screens where the sidebar can render
	// ---------------------------------------

	add_action( 'admin_enqueue_scripts', function() {

		$content_type = infinity23_folders_organizer_get_current_content_type();

		if( empty( $content_type ) ) { return; }
		if( !infinity23_folders_organizer_user_can_use( $content_type ) ) { return; }

		$infinity23_fo_sidebar_width = infinity23_folders_organizer_get_sidebar_width();

		wp_add_inline_style( 'infinity23_dashboard_style', 'body.infinity23-fo-has-sidebar .infinity23-fo-media-sidebar { flex: 0 0 ' . esc_html( $infinity23_fo_sidebar_width ) . '; width: ' . esc_html( $infinity23_fo_sidebar_width ) . '; }' );

		$infinity23_fo_config = array(
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'infinity23_fo_sidebar_ajax' ),
			'addNewNonce'   => wp_create_nonce( 'infinity23_fo_add_new' ),
			'contentType'   => $content_type,
			'currentFolder' => infinity23_folders_organizer_get_current_folder_param(),
			'baseUrl'       => infinity23_folders_organizer_get_screen_base_url( $content_type ),
		);

		wp_add_inline_script( 'infinity23_dashboard_js', 'var infinity23FoConfig = ' . wp_json_encode( $infinity23_fo_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );

	} ); // add_action( 'admin_enqueue_scripts'

	// ---------------------------------------
	// filter the list to the selected folder / unassigned / all
	// ---------------------------------------

	add_action( 'pre_get_posts', function( $query ) {

		if( !is_admin() || !$query->is_main_query() ) { return; }
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only: filters the list table by the folder tab being viewed, no state change
		if( !isset( $_GET['infinity23_folder'] ) ) { return; }

		$content_type = infinity23_folders_organizer_get_current_content_type();

		if( empty( $content_type ) ) { return; }
		if( $query->get( 'post_type' ) !== infinity23_folders_organizer_get_post_type_for_content_type( $content_type ) ) { return; }

		$folder_param = infinity23_folders_organizer_get_current_folder_param();

		if( $folder_param === 'unassigned' ) {

			$assigned_ids = infinity23_folders_organizer_get_all_assigned_image_ids( $content_type );

			if( !empty( $assigned_ids ) ) {
				$query->set( 'post__not_in', $assigned_ids );
			} // if( !empty( $assigned_ids ) ) {

		} elseif( ctype_digit( $folder_param ) ) {

			$ids = infinity23_folders_organizer_get_folder_image_ids( intval( $folder_param ) );
			$query->set( 'post__in', !empty( $ids ) ? $ids : array( 0 ) );

		} // if( $folder_param === 'unassigned' ) {

	} );

	// ---------------------------------------
	// sidebar - folder list with counts, one markup for every content type
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_render_sidebar' ) ) {

		function infinity23_folders_organizer_render_sidebar( $content_type ) {

			$content_types = infinity23_folders_organizer_get_content_types();

			if( !isset( $content_types[ $content_type ] ) ) { return ''; }
			if( !infinity23_folders_organizer_user_can_use( $content_type ) ) { return ''; }

			$type_data = $content_types[ $content_type ];
			$items_label = $type_data['items_label'];

			$folders = infinity23_folders_organizer_get_folders( $content_type );
			$current_folder_param = infinity23_folders_organizer_get_current_folder_param();
			$base_url = infinity23_folders_organizer_get_screen_base_url( $content_type );

			// every count is checked against items that still exist and are not in the trash
			$existing_lookup = array_flip( infinity23_folders_organizer_get_existing_item_ids( $content_type ) );
			$assigned_lookup = array();
			$folder_counts = array();

			foreach( $folders as $folder ) {
				$folder_lookup = array_flip( infinity23_folders_organizer_get_folder_ids_from_row( $folder ) );
				$folder_counts[ $folder->id ] = count( array_intersect_key( $folder_lookup, $existing_lookup ) );
				$assigned_lookup = $assigned_lookup + $folder_lookup;
			} // foreach( $folders as $folder ) {

			$all_items_count = count( $existing_lookup );
			$unassigned_count = count( array_diff_key( $existing_lookup, $assigned_lookup ) );

			ob_start();

			?>
			<div class="infinity23-fo-media-sidebar">

				<div class="infinity23-fo-add-folder">
					<span class="infinity23-fo-add-folder-title">Add New Folder</span>
					<div class="infinity23-fo-add-folder-row">
						<input type="text" id="infinity23-fo-new-folder-name" placeholder="Folder Name" />
						<button type="button" id="infinity23-fo-new-folder-btn" class="infinity23-fo-add-folder-btn" title="Add Folder">+</button>
					</div><!--infinity23-fo-add-folder-row-->
					<span class="infinity23-fo-add-folder-status" id="infinity23-fo-add-folder-status"></span>
				</div><!--infinity23-fo-add-folder-->

				<div class="infinity23-fo-media-sidebar-sep"></div>

				<a href="<?php echo esc_url( $base_url ); ?>" class="infinity23-fo-media-row infinity23-fo-media-row-all <?php if( $current_folder_param === 'all' ) { echo 'infinity23-fo-media-row-active'; } ?>">
					<span class="infinity23-fo-media-row-label">All <?php echo esc_html( $items_label ); ?></span>
					<span class="infinity23-fo-media-row-count"><?php echo intval( $all_items_count ); ?></span>
				</a>

				<a href="<?php echo esc_url( add_query_arg( 'infinity23_folder', 'unassigned', $base_url ) ); ?>" class="infinity23-fo-media-row infinity23-fo-media-row-unassigned <?php if( $current_folder_param === 'unassigned' ) { echo 'infinity23-fo-media-row-active'; } ?>">
					<span class="infinity23-fo-media-row-label">Unassigned <?php echo esc_html( $items_label ); ?></span>
					<span class="infinity23-fo-media-row-count"><?php echo intval( $unassigned_count ); ?></span>
				</a>

				<div class="infinity23-fo-media-sidebar-sep"></div>

				<div class="infinity23-fo-media-folder-list">

					<?php if( empty( $folders ) ) { ?>

						<p class="infinity23-fo-media-folder-empty">No folders yet.</p>

					<?php } else { ?>

						<?php foreach( $folders as $folder ) { ?>

							<div class="infinity23-fo-media-row-wrap" data-folder-id="<?php echo esc_attr( $folder->id ); ?>">

								<a href="<?php echo esc_url( add_query_arg( 'infinity23_folder', $folder->id, $base_url ) ); ?>" class="infinity23-fo-media-row <?php if( $current_folder_param === strval( $folder->id ) ) { echo 'infinity23-fo-media-row-active'; } ?>">
									<span class="infinity23-fo-media-row-icon">&#128193;</span>
									<span class="infinity23-fo-media-row-label"><?php echo esc_html( $folder->name ); ?></span>
									<span class="infinity23-fo-media-row-count"><?php echo intval( $folder_counts[ $folder->id ] ); ?></span>
								</a>

								<button type="button" class="infinity23-fo-media-row-edit-btn" title="Rename Folder">&#9998;</button>

								<div class="infinity23-fo-rename-form">
									<input type="text" class="infinity23-fo-rename-input" value="<?php echo esc_attr( $folder->name ); ?>" />
									<button type="button" class="infinity23-fo-rename-save-btn button">Save</button>
									<button type="button" class="infinity23-fo-rename-cancel-btn button">Cancel</button>
									<div class="infinity23-fo-rename-divider"></div>
									<button type="button" class="infinity23-fo-rename-delete-btn">Delete This Folder</button>
									<span class="infinity23-fo-rename-status"></span>
								</div><!--infinity23-fo-rename-form-->

							</div><!--infinity23-fo-media-row-wrap-->

						<?php } // foreach( $folders as $folder ) { ?>

					<?php } // if( empty( $folders ) ) { ?>

					<div class="infinity23-fo-media-row-wrap infinity23-fo-media-row-template" data-folder-id="0" style="display:none;">

						<a href="<?php echo esc_url( add_query_arg( 'infinity23_folder', '0', $base_url ) ); ?>" class="infinity23-fo-media-row">
							<span class="infinity23-fo-media-row-icon">&#128193;</span>
							<span class="infinity23-fo-media-row-label"></span>
							<span class="infinity23-fo-media-row-count">0</span>
						</a>

						<button type="button" class="infinity23-fo-media-row-edit-btn" title="Rename Folder">&#9998;</button>

						<div class="infinity23-fo-rename-form">
							<input type="text" class="infinity23-fo-rename-input" value="" />
							<button type="button" class="infinity23-fo-rename-save-btn button">Save</button>
							<button type="button" class="infinity23-fo-rename-cancel-btn button">Cancel</button>
							<div class="infinity23-fo-rename-divider"></div>
							<button type="button" class="infinity23-fo-rename-delete-btn">Delete This Folder</button>
							<span class="infinity23-fo-rename-status"></span>
						</div><!--infinity23-fo-rename-form-->

					</div><!--infinity23-fo-media-row-wrap-->

				</div><!--infinity23-fo-media-folder-list-->

				<div class="infinity23-fo-move-selected">

					<label for="infinity23-fo-move-select">Move checked items to:</label>

					<select id="infinity23-fo-move-select">
						<option value="">Choose folder...</option>
						<?php foreach( $folders as $folder ) { ?>
							<option value="<?php echo esc_attr( $folder->id ); ?>"><?php echo esc_html( $folder->name ); ?></option>
						<?php } // foreach( $folders as $folder ) { ?>
					</select>

					<button type="button" id="infinity23-fo-move-btn" class="button">Move Checked Items</button>

					<?php if( ctype_digit( $current_folder_param ) ) { ?>
						<button type="button" id="infinity23-fo-remove-btn" class="button">Remove Checked From This Folder</button>
					<?php } // if( ctype_digit( $current_folder_param ) ) { ?>

					<span class="infinity23-fo-move-status" id="infinity23-fo-move-status"></span>

				</div><!--infinity23-fo-move-selected-->

			</div><!--infinity23-fo-media-sidebar-->
			<?php

			return ob_get_clean();

		} // function infinity23_folders_organizer_render_sidebar( $content_type ) {

	} // if( !function_exists( 'infinity23_folders_organizer_render_sidebar' ) ) {

	// ---------------------------------------
	// bulk actions - move the selected items into a folder, or remove them
	// from the folder currently being viewed. registered per screen id so
	// the same code covers upload, edit-page, edit-post and edit-{cpt}
	// ---------------------------------------

	add_action( 'current_screen', function( $screen ) {

		$content_type = infinity23_folders_organizer_get_current_content_type();

		if( empty( $content_type ) ) { return; }
		if( !infinity23_folders_organizer_user_can_use( $content_type ) ) { return; }

		add_filter( 'bulk_actions-' . $screen->id, function( $actions ) use ( $content_type ) {

			$folders = infinity23_folders_organizer_get_folders( $content_type );

			foreach( $folders as $folder ) {
				$actions[ 'infinity23_move_to_folder_' . $folder->id ] = 'Move to: ' . $folder->name;
			} // foreach( $folders as $folder ) {

			if( ctype_digit( infinity23_folders_organizer_get_current_folder_param() ) ) {
				$actions['infinity23_remove_from_folder'] = 'Remove From This Folder';
			} // if( ctype_digit( infinity23_folders_organizer_get_current_folder_param() ) ) {

			return $actions;

		} );

		add_filter( 'handle_bulk_actions-' . $screen->id, function( $redirect_to, $doaction, $post_ids ) use ( $content_type ) {

			if( empty( $post_ids ) ) { return $redirect_to; }

			$post_ids = infinity23_folders_organizer_filter_editable_ids( array_map( 'intval', $post_ids ) );

			if( empty( $post_ids ) ) { return $redirect_to; }

			if( strpos( $doaction, 'infinity23_move_to_folder_' ) === 0 ) {

				$folder_id = intval( str_replace( 'infinity23_move_to_folder_', '', $doaction ) );
				$folder = infinity23_folders_organizer_get_folder( $folder_id );

				if( !empty( $folder ) && $folder->content_type === $content_type ) {
					infinity23_folders_organizer_add_images_to_folder( $folder_id, $post_ids );
					$redirect_to = add_query_arg( 'infinity23_moved', count( $post_ids ), $redirect_to );
				} // if( !empty( $folder ) && $folder->content_type === $content_type ) {

			} elseif( $doaction === 'infinity23_remove_from_folder' ) {

				$current_folder_param = infinity23_folders_organizer_get_current_folder_param();

				if( ctype_digit( $current_folder_param ) ) {
					infinity23_folders_organizer_remove_images_from_folder( intval( $current_folder_param ), $post_ids );
					$redirect_to = add_query_arg( 'infinity23_removed', count( $post_ids ), $redirect_to );
				} // if( ctype_digit( $current_folder_param ) ) {

			} // if( strpos( $doaction, 'infinity23_move_to_folder_' ) === 0 ) {

			return $redirect_to;

		}, 10, 3 );

	} ); // add_action( 'current_screen'

	// ---------------------------------------
	// confirmation message after a bulk move / remove
	// ---------------------------------------

	add_action( 'admin_notices', function() {

		if( empty( infinity23_folders_organizer_get_current_content_type() ) ) { return; }

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only: redirect-message counter for the admin_notices banner, matches WordPress core's own ?message= pattern, no state change
		if( !empty( $_GET['infinity23_moved'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . intval( $_GET['infinity23_moved'] ) . ' item(s) moved to the folder.</p></div>';
		} // if( !empty( $_GET['infinity23_moved'] ) ) {

		if( !empty( $_GET['infinity23_removed'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . intval( $_GET['infinity23_removed'] ) . ' item(s) removed from the folder.</p></div>';
		} // if( !empty( $_GET['infinity23_removed'] ) ) {
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

	} );

	// ---------------------------------------
	// ajax - shared validation for every sidebar request: nonce, an enabled
	// content type, and the capability of that content type
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_ajax_validate' ) ) {

		function infinity23_folders_organizer_ajax_validate() {

			if( !check_ajax_referer( 'infinity23_fo_sidebar_ajax', 'nonce', false ) ) {
				wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
			} // if( !check_ajax_referer(

			$content_type = isset( $_POST['content_type'] ) ? sanitize_text_field( wp_unslash( $_POST['content_type'] ) ) : '';

			if( empty( $content_type ) || !infinity23_folders_organizer_is_content_type_enabled( $content_type ) ) {
				wp_send_json_error( array( 'message' => 'Unknown content type' ) );
			} // if( empty( $content_type ) || !infinity23_folders_organizer_is_content_type_enabled( $content_type ) ) {

			if( !infinity23_folders_organizer_user_can_use( $content_type ) ) {
				wp_send_json_error( array( 'message' => 'Permission denied' ) );
			} // if( !infinity23_folders_organizer_user_can_use( $content_type ) ) {

			return $content_type;

		} // function infinity23_folders_organizer_ajax_validate() {

	} // if( !function_exists( 'infinity23_folders_organizer_ajax_validate' ) ) {

	// ---------------------------------------
	// ajax - a folder id posted from the sidebar, verified against the content type
	// ---------------------------------------

	if( !function_exists( 'infinity23_folders_organizer_ajax_get_posted_folder' ) ) {

		function infinity23_folders_organizer_ajax_get_posted_folder( $content_type, $field = 'folder_id' ) {

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- every caller of this helper runs infinity23_folders_organizer_ajax_validate() first, which already calls check_ajax_referer()
			$folder_id = isset( $_POST[ $field ] ) ? intval( $_POST[ $field ] ) : 0;
			$folder = !empty( $folder_id ) ? infinity23_folders_organizer_get_folder( $folder_id ) : null;

			if( empty( $folder ) || $folder->content_type !== $content_type ) {
				wp_send_json_error( array( 'message' => 'Missing folder' ) );
			} // if( empty( $folder ) || $folder->content_type !== $content_type ) {

			return $folder;

		} // function infinity23_folders_organizer_ajax_get_posted_folder( $content_type, $field = 'folder_id' ) {

	} // if( !function_exists( 'infinity23_folders_organizer_ajax_get_posted_folder' ) ) {

	// ---------------------------------------
	// ajax - create a new folder
	// ---------------------------------------

	add_action( 'wp_ajax_infinity23_fo_add_folder', function() {

		$content_type = infinity23_folders_organizer_ajax_validate();

		// folders are shared by every user, so creating one needs the same
		// capability as renaming or deleting one - otherwise a contributor can
		// add site-wide folders that nobody at their own level can remove
		if( !infinity23_folders_organizer_user_can_manage( $content_type ) ) {
			wp_send_json_error( array( 'message' => 'You do not have permission to create shared folders' ) );
		} // if( !infinity23_folders_organizer_user_can_manage( $content_type ) ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above by infinity23_folders_organizer_ajax_validate()
		$folder_name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

		if( empty( $folder_name ) ) {
			wp_send_json_error( array( 'message' => 'Please enter a folder name' ) );
		} // if( empty( $folder_name ) ) {

		$folder_slug = sanitize_text_field( str_replace( " ", "_", strtolower( trim( $folder_name ) ) ) );

		if( infinity23_folders_organizer_slug_exists( $content_type, $folder_slug ) ) {
			wp_send_json_error( array( 'message' => 'A folder with this name already exists' ) );
		} // if( infinity23_folders_organizer_slug_exists(

		$new_folder_id = infinity23_folders_organizer_add_folder( $content_type, $folder_name, $folder_slug );

		wp_send_json_success( array( 'folder_id' => $new_folder_id, 'folder_name' => $folder_name ) );

	} ); // add_action( 'wp_ajax_infinity23_fo_add_folder'

	// ---------------------------------------
	// ajax - rename an existing folder
	// ---------------------------------------

	add_action( 'wp_ajax_infinity23_fo_rename_folder', function() {

		$content_type = infinity23_folders_organizer_ajax_validate();

		if( !infinity23_folders_organizer_user_can_manage( $content_type ) ) {
			wp_send_json_error( array( 'message' => 'You do not have permission to rename shared folders' ) );
		} // if( !infinity23_folders_organizer_user_can_manage( $content_type ) ) {

		$folder = infinity23_folders_organizer_ajax_get_posted_folder( $content_type );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above by infinity23_folders_organizer_ajax_validate()
		$folder_name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

		if( empty( $folder_name ) ) {
			wp_send_json_error( array( 'message' => 'Please enter a folder name' ) );
		} // if( empty( $folder_name ) ) {

		$folder_slug = sanitize_text_field( str_replace( " ", "_", strtolower( trim( $folder_name ) ) ) );

		if( infinity23_folders_organizer_slug_exists( $content_type, $folder_slug, $folder->id ) ) {
			wp_send_json_error( array( 'message' => 'A folder with this name already exists' ) );
		} // if( infinity23_folders_organizer_slug_exists(

		infinity23_folders_organizer_update_folder_name( $folder->id, $folder_name, $folder_slug );

		wp_send_json_success( array( 'folder_id' => $folder->id, 'folder_name' => $folder_name ) );

	} ); // add_action( 'wp_ajax_infinity23_fo_rename_folder'

	// ---------------------------------------
	// ajax - delete a folder (the items inside are not deleted, only the folder row)
	// ---------------------------------------

	add_action( 'wp_ajax_infinity23_fo_delete_folder', function() {

		$content_type = infinity23_folders_organizer_ajax_validate();

		if( !infinity23_folders_organizer_user_can_manage( $content_type ) ) {
			wp_send_json_error( array( 'message' => 'You do not have permission to delete shared folders' ) );
		} // if( !infinity23_folders_organizer_user_can_manage( $content_type ) ) {

		$folder = infinity23_folders_organizer_ajax_get_posted_folder( $content_type );

		infinity23_folders_organizer_delete_folder( $folder->id );

		wp_send_json_success( array( 'folder_id' => $folder->id ) );

	} ); // add_action( 'wp_ajax_infinity23_fo_delete_folder'

	// ---------------------------------------
	// ajax - move the checked items into a folder
	// ---------------------------------------

	add_action( 'wp_ajax_infinity23_fo_move_images', function() {

		$content_type = infinity23_folders_organizer_ajax_validate();
		$folder = infinity23_folders_organizer_ajax_get_posted_folder( $content_type );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above by infinity23_folders_organizer_ajax_validate()
		$post_ids = isset( $_POST['post_ids'] ) && is_array( $_POST['post_ids'] ) ? array_map( 'intval', $_POST['post_ids'] ) : array();

		$post_ids = infinity23_folders_organizer_filter_editable_ids( $post_ids );

		if( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => 'Missing items' ) );
		} // if( empty( $post_ids ) ) {

		infinity23_folders_organizer_add_images_to_folder( $folder->id, $post_ids );

		wp_send_json_success( array( 'moved' => count( $post_ids ) ) );

	} ); // add_action( 'wp_ajax_infinity23_fo_move_images'

	// ---------------------------------------
	// ajax - remove the checked items from the folder currently being viewed
	// ---------------------------------------

	add_action( 'wp_ajax_infinity23_fo_remove_images', function() {

		$content_type = infinity23_folders_organizer_ajax_validate();
		$folder = infinity23_folders_organizer_ajax_get_posted_folder( $content_type, 'current_folder' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above by infinity23_folders_organizer_ajax_validate()
		$post_ids = isset( $_POST['post_ids'] ) && is_array( $_POST['post_ids'] ) ? array_map( 'intval', $_POST['post_ids'] ) : array();

		$post_ids = infinity23_folders_organizer_filter_editable_ids( $post_ids );

		if( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => 'Missing items' ) );
		} // if( empty( $post_ids ) ) {

		infinity23_folders_organizer_remove_images_from_folder( $folder->id, $post_ids );

		wp_send_json_success( array( 'removed' => count( $post_ids ) ) );

	} ); // add_action( 'wp_ajax_infinity23_fo_remove_images'

?>
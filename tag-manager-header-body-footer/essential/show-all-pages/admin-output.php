<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// =================================================================================================================
	// Initial plugin load and getting default values
	// =================================================================================================================

	include( dirname(__FILE__) . "/../../include/settings.php" );

	if( !defined( 'INFINITY23_SAP_AJAX' ) ) {

		infinity23_load_essential_files();

		$infinity23_sap_js_vars  = 'var sap_ajax_url = ' . wp_json_encode( esc_url( admin_url( 'admin-ajax.php' ) ) ) . ';';
		$infinity23_sap_js_vars .= 'var sap_ajax_nonce = ' . wp_json_encode( wp_create_nonce( 'infinity23_show_all_pages_ajax' ) ) . ';';

		wp_add_inline_script( 'infinity23_scripts', $infinity23_sap_js_vars, 'before' );

	} // if( !defined( 'INFINITY23_SAP_AJAX' ) ) {

	// =================================================================================================================
	// Helper - output pages as table
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_pages_table' ) ) {

		function infinity23_show_all_pages_table( $query, $start_number = 1 ) {

			$pages_table = '';
			$pages_table .= '<table class="infinity23-all-pages-table">';
				$pages_table .= '<thead>';
					$pages_table .= '<tr>';
						$pages_table .= '<th class="sap-col-num">#</th>';
						$pages_table .= '<th class="sap-col-date">Date</th>';
						$pages_table .= '<th class="sap-col-id">ID</th>';
						$pages_table .= '<th class="sap-col-title">Title</th>';
						$pages_table .= '<th class="sap-col-url">URL</th>';
						$pages_table .= '<th class="sap-col-seo">SEO <small>(loaded from yoast seo)</small></th>';
						$pages_table .= '<th class="sap-col-edit">Edit</th>';
						$pages_table .= '<th class="sap-col-hide-btn"></th>';
					$pages_table .= '</tr>';
				$pages_table .= '</thead>';

			$pages_count = intval( $start_number );

			while( $query->have_posts() ) {

				$query->the_post();

				$page_date    = get_the_date('d-m-Y');
				$page_url     = esc_url( urldecode( get_the_permalink() ) );
				$page_title   = esc_html( get_the_title() );
				$page_id      = esc_html( get_the_ID() );
				$edit_page = get_edit_post_link();
				$edit_page = !empty( $edit_page ) ? esc_url( $edit_page ) : '';

				// removed - the search is now handled inside the WP_Query itself

				$seo_title    = get_post_meta( get_the_ID(), '_yoast_wpseo_title', true );
				$seo_desc     = get_post_meta( get_the_ID(), '_yoast_wpseo_metadesc', true );
				$seo_noindex  = get_post_meta( get_the_ID(), '_yoast_wpseo_meta-robots-noindex', true );
				$seo_nofollow = get_post_meta( get_the_ID(), '_yoast_wpseo_meta-robots-nofollow', true );

				$pages_table .= '<tr>';
					$pages_table .= '<td class="sap-col-num">' . $pages_count . '</td>';
					$pages_table .= '<td class="sap-col-date">' . $page_date . '</td>';
					$pages_table .= '<td class="sap-col-id">' . $page_id . '</td>';
					$pages_table .= '<td class="sap-col-title">' . $page_title . '</td>';
					$pages_table .= '<td class="sap-col-url"><a target="_blank" href="' . $page_url . '">' . $page_url . '</a></td>';
				$seo_cell     = '';
				if( !empty( $seo_title ) )    { $seo_cell .= '<b>Title:</b> ' . esc_html( $seo_title ) . '<br />'; }
				if( !empty( $seo_desc ) )     { $seo_cell .= '<b>Description:</b> ' . esc_html( $seo_desc ) . '<br />'; }
				if( $seo_noindex == 1 )       { $seo_cell .= '<span class="seo-noindex">noindex</span> '; }
				if( $seo_nofollow == 1 )      { $seo_cell .= '<span class="seo-nofollow">nofollow</span>'; }
				if( empty( $seo_cell ) )      { $seo_cell = '-'; }
				$pages_table .= '<td class="sap-col-seo">' . $seo_cell . '</td>';

				$edit_cell = '';

				if( !empty( $edit_page ) ) {
					$edit_cell = '<a target="_blank" href="' . $edit_page . '">Edit</a>';
				} // if( !empty( $edit_page ) ) {

				if( !empty( $edit_page ) && infinity23_is_elementor_active() && !empty( get_post_meta( get_the_ID(), '_elementor_edit_mode', true ) ) ) {
					$elementor_edit = esc_url( str_replace('action=edit', 'action=elementor', $edit_page) );
					$edit_cell .= '<a target="_blank" href="' . $elementor_edit . '">Elementor</a>';
				} // if( !empty( $edit_page ) && infinity23_is_elementor_active()

				$pages_table .= '<td class="sap-col-edit">' . $edit_cell . '</td>';
			$pages_table .= '<td class="sap-col-hide-btn"><button class="sap-hide-row" title="Hide line">&#128065;</button></td>';

				$pages_table .= '</tr>';

				$pages_count++;

			} // while( $query->have_posts() ) {

			wp_reset_postdata();

			$pages_table .= '</table>';

			return $pages_table;

		} // function infinity23_show_all_pages_table( $query, $start_number = 1 ) {

	} // if( !function_exists( 'infinity23_show_all_pages_table' ) ) {

	// =================================================================================================================
	// Helper - count taxonomy terms without breaking on WP_Error
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_pages_count_terms' ) ) {

		function infinity23_show_all_pages_count_terms( $taxonomy ) {

			$terms_count = wp_count_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );

			if( is_wp_error( $terms_count ) ) {
				return 0;
			} // if( is_wp_error( $terms_count ) ) {

			return intval( $terms_count );

		} // function infinity23_show_all_pages_count_terms( $taxonomy ) {

	} // if( !function_exists( 'infinity23_show_all_pages_count_terms' ) ) {

	// =================================================================================================================
	// Helper - output categories/tags as table
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_categories_table' ) ) {

		function infinity23_show_all_categories_table( $cat_args, $start_number = 1 ) {

			$category_content = '';
			$categories = get_categories( $cat_args );
			$categories_count = count( $categories );

			if( $categories_count > 0 ) {

				$category_content .= '<table class="infinity23-all-pages-table">';
					$category_content .= '<thead>';
						$category_content .= '<tr>';
							$category_content .= '<th>#</th>';
							$category_content .= '<th>ID</th>';
							$category_content .= '<th>Title</th>';
							$category_content .= '<th>URL</th>';
							$category_content .= '<th>Edit</th>';
						$category_content .= '</tr>';
					$category_content .= '</thead>';

				$page_count = intval( $start_number );

				foreach( $categories as $category ) {

					$category_url      = esc_url( urldecode( get_category_link( $category->term_id ) ) );
					$edit_category_url = esc_url( strval( get_edit_term_link( $category->term_id ) ) );

					$category_content .= '<tr>';
						$category_content .= '<td>' . $page_count . '</td>';
						$category_content .= '<td><a target="_blank" href="' . $category_url . '">' . $category->term_id . '</a></td>';
						$category_content .= '<td><a target="_blank" href="' . $category_url . '">' . esc_html( $category->name ) . '</a></td>';
						$category_content .= '<td><a target="_blank" href="' . $category_url . '">' . $category_url . '</a></td>';
						$category_content .= '<td><a target="_blank" href="' . $edit_category_url . '">Edit</a></td>';
					$category_content .= '</tr>';

					$page_count++;

				} // foreach( $categories as $category ) {

				$category_content .= '</table>';

			} // if( $categories_count > 0 ) {

			return $category_content;

		} // function infinity23_show_all_categories_table( $cat_args, $start_number = 1 ) {

	} // if( !function_exists( 'infinity23_show_all_categories_table' ) ) {

	// =================================================================================================================
	// Helper - get the current page number of a section
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_pages_get_paged' ) ) {

		function infinity23_show_all_pages_get_paged( $paged_key ) {

			$paged = 1;

			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only: which page of the list is being viewed, no state change
			if( isset( $_GET[ $paged_key ] ) ) {
				$paged = intval( $_GET[ $paged_key ] );
			} // if( isset( $_GET[ $paged_key ] ) ) {
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			if( $paged < 1 ) {
				$paged = 1;
			} // if( $paged < 1 ) {

			return $paged;

		} // function infinity23_show_all_pages_get_paged( $paged_key ) {

	} // if( !function_exists( 'infinity23_show_all_pages_get_paged' ) ) {

	// =================================================================================================================
	// Helper - build one pagination link
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_pages_page_link' ) ) {

		function infinity23_show_all_pages_page_link( $base_args, $paged_key, $page_num, $label ) {

			$base_args[ $paged_key ] = intval( $page_num );

			$page_url = esc_url( admin_url( 'admin.php?' . http_build_query( $base_args ) ) );

			return '<a class="sap-page-link" href="' . $page_url . '" data-page="' . intval( $page_num ) . '">' . esc_html( $label ) . '</a>';

		} // function infinity23_show_all_pages_page_link( $base_args, $paged_key, $page_num, $label ) {

	} // if( !function_exists( 'infinity23_show_all_pages_page_link' ) ) {

	// =================================================================================================================
	// Helper - output the pagination bar of a section
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_pages_pagination' ) ) {

		function infinity23_show_all_pages_pagination( $paged_key, $paged, $total_items, $per_page ) {

			if( $per_page < 1 ) {
				return '';
			} // if( $per_page < 1 ) {

			$infinity23_total_pages = ceil( $total_items / $per_page );

			if( $infinity23_total_pages < 2 ) {
				return '';
			} // if( $infinity23_total_pages < 2 ) {

			$base_args = array();

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only: reflects the current URL's filter/search params back into pagination links, no state change
			foreach( $_GET as $get_key => $get_value ) {

				if( is_array( $get_value ) ) {
					continue;
				} // if( is_array( $get_value ) ) {

				$base_args[ sanitize_key( $get_key ) ] = sanitize_text_field( wp_unslash( $get_value ) );

			} // foreach( $_GET as $get_key => $get_value ) {

			$base_args['page'] = 'essential-show-all-pages';
			unset( $base_args[ $paged_key ] );

			$output = '';
			$output .= '<div class="sap-pagination">';
			$output .= '<span class="sap-pagination-info">Page ' . intval( $paged ) . ' of ' . intval( $infinity23_total_pages ) . ' - ' . intval( $total_items ) . ' items</span>';

			for( $page_num = 1; $page_num <= $infinity23_total_pages; $page_num++ ) {

				if( $page_num == $paged ) {
					$output .= '<span class="sap-page-current">' . intval( $page_num ) . '</span>';
				} else {
					$output .= infinity23_show_all_pages_page_link( $base_args, $paged_key, $page_num, $page_num );
				} // if( $page_num == $paged ) {

			} // for( $page_num = 1; $page_num <= $infinity23_total_pages; $page_num++ ) {

			$output .= '</div><!--sap-pagination-->';

			return $output;

		} // function infinity23_show_all_pages_pagination( $paged_key, $paged, $total_items, $per_page ) {

	} // if( !function_exists( 'infinity23_show_all_pages_pagination' ) ) {

	// =================================================================================================================
	// Helper - limit the search to the title only when the title option is selected
	// =================================================================================================================

	add_filter( 'posts_where', function( $where, $query ) {

		global $wpdb;

		$title_term = $query->get( 'infinity23_title_search' );

		if( !empty( $title_term ) ) {
			$where .= " AND {$wpdb->posts}.post_title LIKE '%" . esc_sql( $wpdb->esc_like( $title_term ) ) . "%'";
		} // if( !empty( $title_term ) ) {

		return $where;

	}, 10, 2 );

	// =================================================================================================================
	// Helper - read the yoast display filters from the request
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_pages_seo_filters' ) ) {

		function infinity23_show_all_pages_seo_filters() {

			$filters = array();
			$filters['hide_no_seo'] = 0;
			$filters['hide_noindex'] = 0;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only: SEO column display filter toggle, no state change
			if( isset( $_REQUEST['hide_no_seo'] ) && $_REQUEST['hide_no_seo'] === 'yes' ) {
				$filters['hide_no_seo'] = 1;
			} // if( isset( $_REQUEST['hide_no_seo'] ) && $_REQUEST['hide_no_seo'] === 'yes' ) {

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only: SEO column display filter toggle, no state change
			if( isset( $_REQUEST['hide_noindex'] ) && $_REQUEST['hide_noindex'] === 'yes' ) {
				$filters['hide_noindex'] = 1;
			} // if( isset( $_REQUEST['hide_noindex'] ) && $_REQUEST['hide_noindex'] === 'yes' ) {

			return $filters;

		} // function infinity23_show_all_pages_seo_filters() {

	} // if( !function_exists( 'infinity23_show_all_pages_seo_filters' ) ) {

	// =================================================================================================================
	// Helper - collect the post ids used by the yoast display filters (runs once per page load)
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_pages_seo_ids' ) ) {

		function infinity23_show_all_pages_seo_ids() {

			global $wpdb;

			static $seo_ids = NULL;

			if( $seo_ids !== NULL ) {
				return $seo_ids;
			} // if( $seo_ids !== NULL ) {

			$seo_ids = array();
			$seo_ids['has_seo'] = NULL;
			$seo_ids['noindex'] = NULL;

			if( !function_exists('yoast_breadcrumb') ) {
				return $seo_ids;
			} // if( !function_exists('yoast_breadcrumb') ) {

			$filters = infinity23_show_all_pages_seo_filters();

			if( $filters['hide_no_seo'] == 1 ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- core postmeta table, no WP API for a distinct post_id aggregate query across post types; caching not yet implemented, tracked for later
				$seo_ids['has_seo'] = $wpdb->get_col( "SELECT DISTINCT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` IN ( '_yoast_wpseo_title', '_yoast_wpseo_metadesc' ) AND `meta_value` != ''" );
				$seo_ids['has_seo'] = array_map( 'intval', (array) $seo_ids['has_seo'] );

			} // if( $filters['hide_no_seo'] == 1 ) {

			if( $filters['hide_noindex'] == 1 ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- core postmeta table, no WP API for a distinct post_id aggregate query across post types; caching not yet implemented, tracked for later
				$seo_ids['noindex'] = $wpdb->get_col( "SELECT DISTINCT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_yoast_wpseo_meta-robots-noindex' AND `meta_value` = '1'" );
				$seo_ids['noindex'] = array_map( 'intval', (array) $seo_ids['noindex'] );

			} // if( $filters['hide_noindex'] == 1 ) {

			return $seo_ids;

		} // function infinity23_show_all_pages_seo_ids() {

	} // if( !function_exists( 'infinity23_show_all_pages_seo_ids' ) ) {

	// =================================================================================================================
	// Helper - run a paginated post type query and return the table
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_pages_section' ) ) {

		function infinity23_show_all_pages_section( $post_type, $post_status, $paged_key, $per_page, $search_term, $search_by ) {

			$paged = infinity23_show_all_pages_get_paged( $paged_key );
			$inner = infinity23_show_all_pages_section_inner( $post_type, $post_status, $paged_key, $per_page, $search_term, $search_by, $paged );

			$wrap = '';
			$wrap .= '<div class="sap-section-wrap" data-mode="posts" data-post-type="' . esc_attr( $post_type ) . '" data-post-status="' . esc_attr( $post_status ) . '" data-paged-key="' . esc_attr( $paged_key ) . '" data-per-page="' . intval( $per_page ) . '" data-search-term="' . esc_attr( $search_term ) . '" data-search-by="' . esc_attr( $search_by ) . '">';
			$wrap .= $inner['results'];
			$wrap .= '</div><!--sap-section-wrap-->';

			$return = array();
			$return['count'] = $inner['count'];
			$return['results'] = $wrap;

			return $return;

		} // function infinity23_show_all_pages_section( $post_type, $post_status, $paged_key, $per_page, $search_term, $search_by ) {

	} // if( !function_exists( 'infinity23_show_all_pages_section' ) ) {

	// =================================================================================================================
	// Helper - build the inner html of a post type section
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_pages_section_inner' ) ) {

		function infinity23_show_all_pages_section_inner( $post_type, $post_status, $paged_key, $per_page, $search_term, $search_by, $paged ) {

			$query_args = array(
				'post_type' => $post_type,
				'post_status' => $post_status,
				'orderby' => 'date',
				'order' => 'desc',
				'posts_per_page' => $per_page,
				'paged' => $paged,
				'update_post_term_cache' => false,
				'ignore_sticky_posts' => true,
			);

			$seo_ids = infinity23_show_all_pages_seo_ids();

			if( is_array( $seo_ids['has_seo'] ) ) {
				$query_args['post__in'] = !empty( $seo_ids['has_seo'] ) ? $seo_ids['has_seo'] : array( 0 );
			} // if( is_array( $seo_ids['has_seo'] ) ) {

			if( is_array( $seo_ids['noindex'] ) && !empty( $seo_ids['noindex'] ) ) {
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- the noindex exclusion list is this module's entire purpose, list is admin-controlled and typically small
				$query_args['post__not_in'] = $seo_ids['noindex'];
			} // if( is_array( $seo_ids['noindex'] ) && !empty( $seo_ids['noindex'] ) ) {

			if( !empty( $search_term ) ) {

				if( $search_by === 'title' ) {
					$query_args['infinity23_title_search'] = $search_term;
				} else {
					$query_args['s'] = $search_term;
				} // if( $search_by === 'title' ) {

			} // if( !empty( $search_term ) ) {

			$query = new WP_Query( $query_args );

			$total_items = intval( $query->found_posts );
			$start_number = 1;

			if( $per_page > 0 ) {
				$start_number = ( ( $paged - 1 ) * $per_page ) + 1;
			} // if( $per_page > 0 ) {

			$pagination = infinity23_show_all_pages_pagination( $paged_key, $paged, $total_items, $per_page );

			$return = array();
			$return['count'] = $total_items;
			$return['results'] = $pagination . infinity23_show_all_pages_table( $query, $start_number ) . $pagination;

			return $return;

		} // function infinity23_show_all_pages_section_inner( $post_type, $post_status, $paged_key, $per_page, $search_term, $search_by, $paged ) {

	} // if( !function_exists( 'infinity23_show_all_pages_section_inner' ) ) {

	// =================================================================================================================
	// Helper - run a paginated taxonomy query and return the table
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_categories_section' ) ) {

		function infinity23_show_all_categories_section( $taxonomy, $paged_key, $per_page ) {

			$paged = infinity23_show_all_pages_get_paged( $paged_key );
			$inner = infinity23_show_all_categories_section_inner( $taxonomy, $paged_key, $per_page, $paged );

			$wrap = '';
			$wrap .= '<div class="sap-section-wrap" data-mode="terms" data-taxonomy="' . esc_attr( $taxonomy ) . '" data-paged-key="' . esc_attr( $paged_key ) . '" data-per-page="' . intval( $per_page ) . '">';
			$wrap .= $inner['results'];
			$wrap .= '</div><!--sap-section-wrap-->';

			$return = array();
			$return['count'] = $inner['count'];
			$return['results'] = $wrap;

			return $return;

		} // function infinity23_show_all_categories_section( $taxonomy, $paged_key, $per_page ) {

	} // if( !function_exists( 'infinity23_show_all_categories_section' ) ) {

	// =================================================================================================================
	// Helper - build the inner html of a taxonomy section
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_categories_section_inner' ) ) {

		function infinity23_show_all_categories_section_inner( $taxonomy, $paged_key, $per_page, $paged ) {

			$total_items = infinity23_show_all_pages_count_terms( $taxonomy );

			$cat_args = array( 'order' => 'desc', 'hide_empty' => 0, 'taxonomy' => $taxonomy );

			$start_number = 1;

			if( $per_page > 0 ) {
				$cat_args['number'] = $per_page;
				$cat_args['offset'] = ( $paged - 1 ) * $per_page;
				$start_number = ( ( $paged - 1 ) * $per_page ) + 1;
			} // if( $per_page > 0 ) {

			$pagination = infinity23_show_all_pages_pagination( $paged_key, $paged, $total_items, $per_page );

			$return = array();
			$return['count'] = $total_items;
			$return['results'] = $pagination . infinity23_show_all_categories_table( $cat_args, $start_number ) . $pagination;

			return $return;

		} // function infinity23_show_all_categories_section_inner( $taxonomy, $paged_key, $per_page, $paged ) {

	} // if( !function_exists( 'infinity23_show_all_categories_section_inner' ) ) {

	// =================================================================================================================
	// Ajax output - return only the requested section html and stop the rest of the page
	// =================================================================================================================

	if( !function_exists( 'infinity23_show_all_pages_ajax_section' ) ) {

		function infinity23_show_all_pages_ajax_section() {

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- this function only ever runs behind INFINITY23_SAP_AJAX, which is only defined by the wp_ajax_infinity23_show_all_pages_page handler in loader.php after check_ajax_referer() passes
			$mode = isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : 'posts';
			$paged_key = isset( $_POST['paged_key'] ) ? sanitize_key( $_POST['paged_key'] ) : '';
			$paged = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
			$per_page = isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 500;
			$allowed_per_page = array( 50, 100, 250, 500, 1000, -1 );

			if( !in_array( $per_page, $allowed_per_page, true ) ) {
				$per_page = 500;
			} // if( !in_array( $per_page, $allowed_per_page, true ) ) {

			if( $paged < 1 ) {
				$paged = 1;
			} // if( $paged < 1 ) {

			if( $mode === 'terms' ) {

				$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( $_POST['taxonomy'] ) : 'category';
				$inner = infinity23_show_all_categories_section_inner( $taxonomy, $paged_key, $per_page, $paged );

				return $inner['results'];

			} // if( $mode === 'terms' ) {

			$post_type = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : 'page';
			$post_status = isset( $_POST['post_status'] ) ? sanitize_key( $_POST['post_status'] ) : 'publish';
			$search_term = isset( $_POST['search_term'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['search_term'] ) ) ) : '';
			$search_by = ( isset( $_POST['search_by'] ) && $_POST['search_by'] === 'content' ) ? 'content' : 'title';
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			$inner = infinity23_show_all_pages_section_inner( $post_type, $post_status, $paged_key, $per_page, $search_term, $search_by, $paged );

			return $inner['results'];

		} // function infinity23_show_all_pages_ajax_section() {

	} // if( !function_exists( 'infinity23_show_all_pages_ajax_section' ) ) {

	if( defined( 'INFINITY23_SAP_AJAX' ) ) {

		if( ob_get_length() !== false ) {
			ob_clean();
		} // if( ob_get_length() !== false ) {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values
		echo infinity23_show_all_pages_ajax_section();
		return;

	} // if( defined( 'INFINITY23_SAP_AJAX' ) ) {

	// =================================================================================================================
	// Search form handling
	// =================================================================================================================

	$infinity23_yy_search_term = '';
	$infinity23_yy_search_by = 'title';

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only: search box/per-page controls for the list view, no state change
	if( isset( $_GET['pages_search_term'] ) && !empty( $_GET['pages_search_term'] ) && current_user_can( 'manage_options' ) ) {

		$infinity23_yy_search_term = trim( sanitize_text_field( wp_unslash( $_GET['pages_search_term'] ) ) );

		if( isset( $_GET['pages_search_by'] ) && $_GET['pages_search_by'] === 'content' ) {
			$infinity23_yy_search_by = 'content';
		} // if( isset( $_GET['pages_search_by'] ) && $_GET['pages_search_by'] === 'content' ) {

	} // if( isset( $_GET['pages_search_term'] ) && !empty( $_GET['pages_search_term'] ) && current_user_can( 'manage_options' ) ) {

	// =================================================================================================================
	// Entries per page handling
	// =================================================================================================================

	$infinity23_yy_per_page_options = array( 50, 100, 250, 500, 1000 );
	$infinity23_yy_per_page = 250;

	if( isset( $_GET['per_page'] ) ) {

		if( $_GET['per_page'] === 'all' ) {
			$infinity23_yy_per_page = -1;
		} elseif( in_array( intval( $_GET['per_page'] ), $infinity23_yy_per_page_options ) ) {
			$infinity23_yy_per_page = intval( $_GET['per_page'] );
		} // if( $_GET['per_page'] === 'all' ) {

	} // if( isset( $_GET['per_page'] ) ) {
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	// =================================================================================================================
	// Yoast SEO display filters
	// =================================================================================================================

	$infinity23_yy_seo_filters = infinity23_show_all_pages_seo_filters();
	$infinity23_yy_hide_no_seo = $infinity23_yy_seo_filters['hide_no_seo'];
	$infinity23_yy_hide_noindex = $infinity23_yy_seo_filters['hide_noindex'];

?>

<?php infinity23_modules_header( 'Show All Pages' ); ?>

<div class="infinity23-warp show-all-pages-warp no-floating-save">

	<div class="page-header">
		<h1>Show All Pages</h1>
		<p>All pages, posts, categories, tags, custom post types and WooCommerce items on your site.</p>
	</div><!--page-header-->

	<form class="sap-search-form" method="GET" action="">
		<input type="hidden" name="page" value="essential-show-all-pages" />
		<input type="hidden" name="per_page" value="<?php echo ( $infinity23_yy_per_page == -1 ) ? 'all' : intval( $infinity23_yy_per_page ); ?>" />
		<input type="hidden" name="hide_no_seo" value="<?php echo ( $infinity23_yy_hide_no_seo == 1 ) ? 'yes' : ''; ?>" />
		<input type="hidden" name="hide_noindex" value="<?php echo ( $infinity23_yy_hide_noindex == 1 ) ? 'yes' : ''; ?>" />

		<div class="form-line">
			<label for="pages_search_term">Search: </label>
			<input type="text" id="pages_search_term" name="pages_search_term" class="long-input" placeholder="Search term" value="<?php echo esc_attr( $infinity23_yy_search_term ); ?>" />

			<label for="pages_search_by">Search by: </label>
			<select id="pages_search_by" name="pages_search_by">
				<option value="title" <?php if( $infinity23_yy_search_by === 'title' ) { echo 'selected'; } ?>>Title</option>
				<option value="content" <?php if( $infinity23_yy_search_by === 'content' ) { echo 'selected'; } ?>>Title + Content</option>
			</select>

			<input type="submit" name="submit_search" class="submit-btn sap-search-submit" value="Search" />
		</div><!--form-line-->

	</form>

	<br />

	<?php

		$infinity23_top_pages_count = wp_count_posts('page');
		$infinity23_top_posts_count = wp_count_posts('post');
		$infinity23_top_pages_total = intval( $infinity23_top_pages_count->publish ) + intval( $infinity23_top_pages_count->private ) + intval( $infinity23_top_pages_count->future );
		$infinity23_top_posts_total = intval( $infinity23_top_posts_count->publish ) + intval( $infinity23_top_posts_count->private ) + intval( $infinity23_top_posts_count->future );
		$infinity23_top_cats_total = infinity23_show_all_pages_count_terms( 'category' );
		$infinity23_top_tags_total = infinity23_show_all_pages_count_terms( 'post_tag' );
		$infinity23_top_grand_total = $infinity23_top_pages_total + $infinity23_top_posts_total + $infinity23_top_cats_total + $infinity23_top_tags_total;

		$infinity23_top_cpt_total = 0;
		$infinity23_top_cpt_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );
		foreach( $infinity23_top_cpt_types as $infinity23_top_cpt_type ) {
			if( $infinity23_top_cpt_type->name === 'product' ) { continue; }
			$infinity23_top_cpt_count = wp_count_posts( $infinity23_top_cpt_type->name );
			$infinity23_top_cpt_total += intval( $infinity23_top_cpt_count->publish );
		} // foreach( $infinity23_top_cpt_types as $infinity23_top_cpt_type ) {
		$infinity23_top_grand_total = $infinity23_top_grand_total + $infinity23_top_cpt_total;

		$infinity23_top_woo_products = 0;
		$infinity23_top_woo_cats     = 0;
		$infinity23_top_woo_tags     = 0;

		if( class_exists('woocommerce') ) {
			$infinity23_top_woo_count    = wp_count_posts('product');
			$infinity23_top_woo_products = intval( $infinity23_top_woo_count->publish ) + intval( $infinity23_top_woo_count->private );
			$infinity23_top_woo_cats = infinity23_show_all_pages_count_terms( 'product_cat' );
			$infinity23_top_woo_tags = infinity23_show_all_pages_count_terms( 'product_tag' );
			$infinity23_top_grand_total  = $infinity23_top_grand_total + $infinity23_top_woo_products + $infinity23_top_woo_cats + $infinity23_top_woo_tags;
		} // if( class_exists('woocommerce') ) {

	?>

	<div class="sap-totals-box">
		<h2>Total pages on site: <b class="sap-totals-number"><?php echo intval( $infinity23_top_grand_total ); ?> Pages</b></h2>
		<small class="sap-totals-line sap-totals-line-first"><b>Wordpress:</b> <?php echo intval( $infinity23_top_pages_total ); ?> Pages + <?php echo intval( $infinity23_top_posts_total ); ?> Posts + <?php echo intval( $infinity23_top_cats_total ); ?> Categories + <?php echo intval( $infinity23_top_tags_total ); ?> Tags<?php if( $infinity23_top_cpt_total > 0 ) { echo ' + ' . intval( $infinity23_top_cpt_total ) . ' Custom Post Types'; } ?></small>
		<?php if( $infinity23_top_cpt_total > 0 ) { ?>
			<small class="sap-totals-line sap-totals-cpt"><b>Custom Post Types:</b>
			<?php
				$infinity23_top_cpt_parts = array();
				foreach( $infinity23_top_cpt_types as $infinity23_top_cpt_type ) {
					if( $infinity23_top_cpt_type->name === 'product' ) { continue; }
					$infinity23_top_cpt_single_count = wp_count_posts( $infinity23_top_cpt_type->name );
					$infinity23_top_cpt_single_total = intval( $infinity23_top_cpt_single_count->publish );
					if( $infinity23_top_cpt_single_total > 0 ) {
						$infinity23_top_cpt_parts[] = '<span class="sap-cpt-item">' . $infinity23_top_cpt_single_total . ' ' . esc_html( $infinity23_top_cpt_type->label ) . '</span>';
					} // if( $infinity23_top_cpt_single_total > 0 ) {
				} // foreach( $infinity23_top_cpt_types as $infinity23_top_cpt_type ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values
				echo implode( '<span class="sap-cpt-sep"> + </span>', $infinity23_top_cpt_parts );
			?>
			</small>
		<?php } // if( $infinity23_top_cpt_total > 0 ) { ?>
		<?php if( class_exists('woocommerce') ) { ?>
			<small class="sap-totals-line"><b>Woocommerce:</b> <?php echo intval( $infinity23_top_woo_products ); ?> Products + <?php echo intval( $infinity23_top_woo_cats ); ?> Categories + <?php echo intval( $infinity23_top_woo_tags ); ?> Tags</small>
		<?php } // if( class_exists('woocommerce') ) { ?>
	</div><!--sap-totals-box-->

	<div class="sap-filters-wrap">

	<span class="sap-filters-title">Display Filter</span>

	<div class="sap-col-toggles">

		<div class="sap-filter-row">
			<span class="sap-row-label">Show columns:</span>
			<span class="sap-row-items">
				<label><input type="checkbox" class="sap-col-toggle" data-col="sap-col-num"      checked /> #</label>
				<label><input type="checkbox" class="sap-col-toggle" data-col="sap-col-date"     checked /> Date</label>
				<label><input type="checkbox" class="sap-col-toggle" data-col="sap-col-id"       checked /> ID</label>
				<label><input type="checkbox" class="sap-col-toggle" data-col="sap-col-title"    checked /> Title</label>
				<label><input type="checkbox" class="sap-col-toggle" data-col="sap-col-url"      checked /> URL</label>
				<label><input type="checkbox" class="sap-col-toggle" data-col="sap-col-seo"      checked /> SEO</label>
				<label><input type="checkbox" class="sap-col-toggle" data-col="sap-col-edit"     checked /> Edit</label>
				<label><input type="checkbox" class="sap-col-toggle" data-col="sap-col-hide-btn" checked /> Hide Button</label>
			</span>
		</div><!--sap-filter-row-->

		<?php if( function_exists('yoast_breadcrumb') ) { ?>
		<div class="sap-filter-row">
			<span class="sap-row-label">Yoast SEO:</span>
			<span class="sap-row-items">
				<form class="sap-seo-filter-form" method="GET" action="">
					<input type="hidden" name="page" value="essential-show-all-pages" />
					<input type="hidden" name="pages_search_term" value="<?php echo esc_attr( $infinity23_yy_search_term ); ?>" />
					<input type="hidden" name="pages_search_by" value="<?php echo esc_attr( $infinity23_yy_search_by ); ?>" />
					<input type="hidden" name="per_page" value="<?php echo ( $infinity23_yy_per_page == -1 ) ? 'all' : intval( $infinity23_yy_per_page ); ?>" />
					<label><input type="checkbox" name="hide_no_seo" value="yes" <?php if( $infinity23_yy_hide_no_seo == 1 ) { echo 'checked'; } ?> /> Hide pages without SEO title or description</label>
					<label><input type="checkbox" name="hide_noindex" value="yes" <?php if( $infinity23_yy_hide_noindex == 1 ) { echo 'checked'; } ?> /> Hide pages excluded from sitemap (Yoast noindex)</label>
				</form>
			</span>
		</div><!--sap-filter-row-->
		<?php } // if( function_exists('yoast_breadcrumb') ) { ?>

	</div><!--sap-col-toggles-->

	<div class="sap-cards-buttons">
		<form class="sap-per-page-form" method="GET" action="">
			<input type="hidden" name="page" value="essential-show-all-pages" />
			<input type="hidden" name="pages_search_term" value="<?php echo esc_attr( $infinity23_yy_search_term ); ?>" />
			<input type="hidden" name="pages_search_by" value="<?php echo esc_attr( $infinity23_yy_search_by ); ?>" />
			<input type="hidden" name="hide_no_seo" value="<?php echo ( $infinity23_yy_hide_no_seo == 1 ) ? 'yes' : ''; ?>" />
			<input type="hidden" name="hide_noindex" value="<?php echo ( $infinity23_yy_hide_noindex == 1 ) ? 'yes' : ''; ?>" />

			<label for="per_page">Entries per page: </label>
			<select id="per_page" name="per_page">
				<?php foreach( $infinity23_yy_per_page_options as $infinity23_per_page_option ) { ?>
					<option value="<?php echo intval( $infinity23_per_page_option ); ?>" <?php if( $infinity23_yy_per_page == $infinity23_per_page_option ) { echo 'selected'; } ?>><?php echo intval( $infinity23_per_page_option ); ?></option>
				<?php } // foreach( $infinity23_yy_per_page_options as $infinity23_per_page_option ) { ?>
				<option value="all" <?php if( $infinity23_yy_per_page == -1 ) { echo 'selected'; } ?>>Show all</option>
			</select>
		</form>
		<button type="button" class="sap-open-all">Open All Sections</button>
		<span class="sap-buttons-sep">|</span>
		<button type="button" class="sap-collapse-all">Collapse All Sections</button>
	</div><!--sap-cards-buttons-->

	</div><!--sap-filters-wrap-->

	<?php

	// =================================================================================================================
	// Pages section card
	// =================================================================================================================

	$infinity23_published_pages_result = infinity23_show_all_pages_section( 'page', 'publish', 'paged_pages_published', $infinity23_yy_per_page, $infinity23_yy_search_term, $infinity23_yy_search_by );
	$infinity23_private_pages_result = infinity23_show_all_pages_section( 'page', 'private', 'paged_pages_private', $infinity23_yy_per_page, $infinity23_yy_search_term, $infinity23_yy_search_by );
	$infinity23_scheduled_pages_result = infinity23_show_all_pages_section( 'page', 'future', 'paged_pages_scheduled', $infinity23_yy_per_page, $infinity23_yy_search_term, $infinity23_yy_search_by );

	$infinity23_total_pages = $infinity23_published_pages_result['count'] + $infinity23_private_pages_result['count'] + $infinity23_scheduled_pages_result['count'];

	?>

	<div class="section-card" data-card-default="open">

		<h2>Pages <small>(<?php echo intval( $infinity23_total_pages ); ?> total)</small></h2>

		<?php if( $infinity23_published_pages_result['count'] > 0 ) { ?>
			<h3>Published Pages (<?php echo intval( $infinity23_published_pages_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_published_pages_result['results']; ?>
		<?php } // if( $infinity23_published_pages_result['count'] > 0 ) { ?>

		<?php if( $infinity23_private_pages_result['count'] > 0 ) { ?>
			<h3>Private Pages (<?php echo intval( $infinity23_private_pages_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_private_pages_result['results']; ?>
		<?php } // if( $infinity23_private_pages_result['count'] > 0 ) { ?>

		<?php if( $infinity23_scheduled_pages_result['count'] > 0 ) { ?>
			<h3>Scheduled Pages (<?php echo intval( $infinity23_scheduled_pages_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_scheduled_pages_result['results']; ?>
		<?php } // if( $infinity23_scheduled_pages_result['count'] > 0 ) { ?>

		<?php if( $infinity23_total_pages === 0 ) { ?>
			<p>No pages found.</p>
		<?php } // if( $infinity23_total_pages === 0 ) { ?>

	</div><!--section-card-->

	<?php

	// =================================================================================================================
	// Posts section card
	// =================================================================================================================

	$infinity23_published_posts_result = infinity23_show_all_pages_section( 'post', 'publish', 'paged_posts_published', $infinity23_yy_per_page, $infinity23_yy_search_term, $infinity23_yy_search_by );
	$infinity23_private_posts_result = infinity23_show_all_pages_section( 'post', 'private', 'paged_posts_private', $infinity23_yy_per_page, $infinity23_yy_search_term, $infinity23_yy_search_by );
	$infinity23_scheduled_posts_result = infinity23_show_all_pages_section( 'post', 'future', 'paged_posts_scheduled', $infinity23_yy_per_page, $infinity23_yy_search_term, $infinity23_yy_search_by );

	$infinity23_total_posts = $infinity23_published_posts_result['count'] + $infinity23_private_posts_result['count'] + $infinity23_scheduled_posts_result['count'];

	?>

	<div class="section-card" data-card-default="open">

		<h2>Posts <small>(<?php echo intval( $infinity23_total_posts ); ?> total)</small></h2>

		<?php if( $infinity23_published_posts_result['count'] > 0 ) { ?>
			<h3>Published Posts (<?php echo intval( $infinity23_published_posts_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_published_posts_result['results']; ?>
		<?php } // if( $infinity23_published_posts_result['count'] > 0 ) { ?>

		<?php if( $infinity23_private_posts_result['count'] > 0 ) { ?>
			<h3>Private Posts (<?php echo intval( $infinity23_private_posts_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_private_posts_result['results']; ?>
		<?php } // if( $infinity23_private_posts_result['count'] > 0 ) { ?>

		<?php if( $infinity23_scheduled_posts_result['count'] > 0 ) { ?>
			<h3>Scheduled Posts (<?php echo intval( $infinity23_scheduled_posts_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_scheduled_posts_result['results']; ?>
		<?php } // if( $infinity23_scheduled_posts_result['count'] > 0 ) { ?>

		<?php if( $infinity23_total_posts === 0 ) { ?>
			<p>No posts found.</p>
		<?php } // if( $infinity23_total_posts === 0 ) { ?>

	</div><!--section-card-->

	<?php

	// =================================================================================================================
	// Categories & Tags section card - skip on search
	// =================================================================================================================

	if( empty( $infinity23_yy_search_term ) ) {

		$infinity23_categories_result = infinity23_show_all_categories_section( 'category', 'paged_categories', $infinity23_yy_per_page );
		$infinity23_tags_result = infinity23_show_all_categories_section( 'post_tag', 'paged_tags', $infinity23_yy_per_page );

		$infinity23_total_cats_tags = $infinity23_categories_result['count'] + $infinity23_tags_result['count'];

	?>

	<div class="section-card" data-card-default="open">

		<h2>Categories &amp; Tags <small>(<?php echo intval( $infinity23_total_cats_tags ); ?> total)</small></h2>

		<?php if( $infinity23_categories_result['count'] > 0 ) { ?>
			<h3>Categories (<?php echo intval( $infinity23_categories_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_categories_result['results']; ?>
		<?php } // if( $infinity23_categories_result['count'] > 0 ) { ?>

		<?php if( $infinity23_tags_result['count'] > 0 ) { ?>
			<h3>Tags (<?php echo intval( $infinity23_tags_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_tags_result['results']; ?>
		<?php } // if( $infinity23_tags_result['count'] > 0 ) { ?>

		<?php if( $infinity23_total_cats_tags === 0 ) { ?>
			<p>No categories or tags found.</p>
		<?php } // if( $infinity23_total_cats_tags === 0 ) { ?>

	</div><!--section-card-->

	<?php } // if( empty( $infinity23_yy_search_term ) ) { ?>

	<?php

	// =================================================================================================================
	// Custom Post Types section card
	// =================================================================================================================

	$infinity23_post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );

	$infinity23_custom_types_to_show = array();
	foreach( $infinity23_post_types as $post_type ) {
		if( $post_type->name !== 'product' ) {
			$infinity23_custom_types_to_show[] = $post_type;
		} // if( $post_type->name !== 'product' ) {
	} // foreach( $infinity23_post_types as $post_type ) {

	if( !empty( $infinity23_custom_types_to_show ) ) {

		$infinity23_custom_total = 0;
		$infinity23_custom_results = array();

		foreach( $infinity23_custom_types_to_show as $post_type ) {

			$infinity23_cpt_result = infinity23_show_all_pages_section( $post_type->name, 'publish', 'paged_cpt_' . $post_type->name, $infinity23_yy_per_page, $infinity23_yy_search_term, $infinity23_yy_search_by );
			$infinity23_custom_total += $infinity23_cpt_result['count'];
			$infinity23_custom_results[] = array(
				'label'   => $post_type->label,
				'count'   => $infinity23_cpt_result['count'],
				'results' => $infinity23_cpt_result['results'],
			);

		} // foreach( $infinity23_custom_types_to_show as $post_type ) {

	?>

	<div class="section-card" data-card-default="open">

		<h2>Custom Post Types <small>(<?php echo intval( $infinity23_custom_total ); ?> total)</small></h2>

		<?php foreach( $infinity23_custom_results as $infinity23_cpt ) { ?>

			<?php if( $infinity23_cpt['count'] > 0 ) { ?>
				<h3><?php echo esc_html( $infinity23_cpt['label'] ); ?> (<?php echo intval( $infinity23_cpt['count'] ); ?>)</h3>
				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
				<?php echo $infinity23_cpt['results']; ?>
			<?php } // if( $infinity23_cpt['count'] > 0 ) { ?>

		<?php } // foreach( $infinity23_custom_results as $infinity23_cpt ) { ?>

		<?php if( $infinity23_custom_total === 0 ) { ?>
			<p>No custom post type entries found.</p>
		<?php } // if( $infinity23_custom_total === 0 ) { ?>

	</div><!--section-card-->

	<?php } // if( !empty( $infinity23_custom_types_to_show ) ) { ?>

	<?php

	// =================================================================================================================
	// WooCommerce section card
	// =================================================================================================================

	if( class_exists( 'woocommerce' ) ) {

		$infinity23_woo_products_result = infinity23_show_all_pages_section( 'product', 'publish', 'paged_products', $infinity23_yy_per_page, $infinity23_yy_search_term, $infinity23_yy_search_by );
		$infinity23_woo_private_result = infinity23_show_all_pages_section( 'product', 'private', 'paged_products_private', $infinity23_yy_per_page, $infinity23_yy_search_term, $infinity23_yy_search_by );

		$infinity23_woo_cats_result = array( 'count' => 0, 'results' => '' );
		$infinity23_woo_tags_result = array( 'count' => 0, 'results' => '' );

		if( empty( $infinity23_yy_search_term ) ) {
			$infinity23_woo_cats_result = infinity23_show_all_categories_section( 'product_cat', 'paged_product_cats', $infinity23_yy_per_page );
			$infinity23_woo_tags_result = infinity23_show_all_categories_section( 'product_tag', 'paged_product_tags', $infinity23_yy_per_page );
		} // if( empty( $infinity23_yy_search_term ) ) {

		$infinity23_woo_total = $infinity23_woo_products_result['count'] + $infinity23_woo_private_result['count'] + $infinity23_woo_cats_result['count'] + $infinity23_woo_tags_result['count'];

	?>

	<div class="section-card" data-card-default="open">

		<h2>WooCommerce <small>(<?php echo intval( $infinity23_woo_total ); ?> total)</small></h2>

		<?php if( $infinity23_woo_products_result['count'] > 0 ) { ?>
			<h3>Products (<?php echo intval( $infinity23_woo_products_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_woo_products_result['results']; ?>
		<?php } // if( $infinity23_woo_products_result['count'] > 0 ) { ?>

		<?php if( $infinity23_woo_private_result['count'] > 0 ) { ?>
			<h3>Private Products (<?php echo intval( $infinity23_woo_private_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_woo_private_result['results']; ?>
		<?php } // if( $infinity23_woo_private_result['count'] > 0 ) { ?>

		<?php if( $infinity23_woo_cats_result['count'] > 0 ) { ?>
			<h3>Product Categories (<?php echo intval( $infinity23_woo_cats_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_woo_cats_result['results']; ?>
		<?php } // if( $infinity23_woo_cats_result['count'] > 0 ) { ?>

		<?php if( $infinity23_woo_tags_result['count'] > 0 ) { ?>
			<h3>Product Tags (<?php echo intval( $infinity23_woo_tags_result['count'] ); ?>)</h3>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
			<?php echo $infinity23_woo_tags_result['results']; ?>
		<?php } // if( $infinity23_woo_tags_result['count'] > 0 ) { ?>

		<?php if( $infinity23_woo_total === 0 ) { ?>
			<p>No WooCommerce items found.</p>
		<?php } // if( $infinity23_woo_total === 0 ) { ?>

	</div><!--section-card-->

	<?php } // if( class_exists( 'woocommerce' ) ) { ?>

<!-- remove this code from the page -->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
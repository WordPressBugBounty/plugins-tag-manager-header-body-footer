<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// Sticky sidebar front-end output
	// ===========================================================================

	include( dirname(__FILE__) . "/../../../include/settings.php" );
	include( dirname(__FILE__) . "/../internal-settings.php" );
	include( dirname(__FILE__) . "/../default-values.php" );

	$infinity23_ss_data_array = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

	// ---------------------------------------
	// all saved fields with their default values
	// ---------------------------------------

	$infinity23_ss_settings = array();

	foreach( $infinity23_defaults as $infinity23_ss_field_key => $infinity23_ss_field_data ) {

		$infinity23_ss_settings[ $infinity23_ss_field_key ] = infinity23_pro_get_data( $infinity23_ss_field_key, $infinity23_ss_data_array, $infinity23_defaults );

		if( $infinity23_ss_field_data['type'] === 'intval' ) {
			$infinity23_ss_settings[ $infinity23_ss_field_key ] = intval( $infinity23_ss_settings[ $infinity23_ss_field_key ] );
		} // if( $infinity23_ss_field_data['type'] === 'intval' ) {

	} // foreach( $infinity23_defaults as $infinity23_ss_field_key => $infinity23_ss_field_data ) {

	// an empty sidebar selector can never work, so the default one is used instead
	if( trim( $infinity23_ss_settings['sidebar_selector'] ) === '' ) {
		$infinity23_ss_settings['sidebar_selector'] = $infinity23_defaults['sidebar_selector']['value'];
	} // if( trim( $infinity23_ss_settings['sidebar_selector'] ) === '' ) {

	$infinity23_ss_settings['disabled_custom_post_types'] = array_filter( array_map( 'trim', explode( ',', $infinity23_ss_settings['disabled_custom_post_types'] ) ) );

	// ===========================================================================
	// Check if the current page type is one of the pages the sticky sidebar loads on
	// ===========================================================================

	if( !function_exists( 'infinity23_sticky_sidebar_page_type_allowed' ) ) {

		function infinity23_sticky_sidebar_page_type_allowed( $infinity23_ss_settings ) {

			if( is_front_page() ) {
				return ( $infinity23_ss_settings['load_on_front_page'] == 1 );
			} // if( is_front_page() ) {

			if( is_home() ) {
				return ( $infinity23_ss_settings['load_on_blog_page'] == 1 );
			} // if( is_home() ) {

			if( is_page() ) {
				return ( $infinity23_ss_settings['load_on_pages'] == 1 );
			} // if( is_page() ) {

			if( is_singular() ) {

				$infinity23_ss_post_type = get_post_type();

				if( $infinity23_ss_post_type === 'post' ) {
					return ( $infinity23_ss_settings['load_on_posts'] == 1 );
				} // if( $infinity23_ss_post_type === 'post' ) {

				if( $infinity23_ss_post_type === 'attachment' ) {
					return false;
				} // if( $infinity23_ss_post_type === 'attachment' ) {

				// every other single item is a custom post type
				if( $infinity23_ss_settings['load_on_custom_post_types'] != 1 ) {
					return false;
				} // if( $infinity23_ss_settings['load_on_custom_post_types'] != 1 ) {

				return !in_array( $infinity23_ss_post_type, $infinity23_ss_settings['disabled_custom_post_types'], true );

			} // if( is_singular() ) {

			if( is_search() ) {
				return ( $infinity23_ss_settings['load_on_search'] == 1 );
			} // if( is_search() ) {

			if( is_archive() ) {
				return ( $infinity23_ss_settings['load_on_archives'] == 1 );
			} // if( is_archive() ) {

			return false;

		} // function infinity23_sticky_sidebar_page_type_allowed( $infinity23_ss_settings ) {

	} // if( !function_exists( 'infinity23_sticky_sidebar_page_type_allowed' ) ) {

	// ===========================================================================
	// The sticky sidebar script - it is printed inside of the page, so there are no extra files to load
	// ===========================================================================

	if( !function_exists( 'infinity23_sticky_sidebar_get_js' ) ) {

		function infinity23_sticky_sidebar_get_js() {

			return <<<'INFINITY23_STICKY_SIDEBAR_JS'
// ----------------------------------------------
// infinity23 sticky sidebar js code
// The movement logic is based on the Theia Sticky Sidebar script v1.7.0
// https://github.com/WeCodePixels/theia-sticky-sidebar
// Copyright 2013-2016 WeCodePixels and other contributors - released under the MIT license
// ----------------------------------------------

jQuery(document).ready(function($) {

	// ====================================================================================================
	// Sticky sidebar settings (saved on the admin page and sent from php)
	// ====================================================================================================

	if( typeof infinity23_sticky_sidebar_vars === 'undefined' ) { return; }

	var infinity23_ss_top_spacing = parseInt( infinity23_sticky_sidebar_vars.top_spacing ) || 0; // the space between the sidebar and the top of the window
	var infinity23_ss_bottom_spacing = parseInt( infinity23_sticky_sidebar_vars.bottom_spacing ) || 0; // the space between the sidebar and the bottom of the window
	var infinity23_ss_min_width = parseInt( infinity23_sticky_sidebar_vars.min_width ) || 0; // the script will not run on windows smaller than this
	var infinity23_ss_update_height = ( parseInt( infinity23_sticky_sidebar_vars.update_sidebar_height ) === 1 ); // keep the sidebar box at its full height while it moves

	// ====================================================================================================
	// Helper functions
	// ====================================================================================================

	// --------------------------------------
	// find elements by a selector, a wrong selector will never break the page
	// --------------------------------------

	function infinity23_ss_find( selector ) {

		try {
			return $( document ).find( selector );
		} catch( error ) {
			return $();
		} // try {

	} // function infinity23_ss_find( selector ) {

	// --------------------------------------
	// css values like "12px" or "auto" to a number
	// --------------------------------------

	function infinity23_ss_number( value ) {
		return parseFloat( value ) || 0;
	} // function infinity23_ss_number( value ) {

	// ====================================================================================================
	// Create the sticky sidebar
	// ====================================================================================================

	function infinity23_ss_create( $sidebar ) {

		// --------------------------------------
		// find the container, the sidebar will only move inside of it
		// --------------------------------------

		var $container = infinity23_ss_find( infinity23_sticky_sidebar_vars.container_selector ).first();

		// if the container was not found or it does not hold the sidebar we use the parent of the sidebar
		if( $container.length === 0 || !$.contains( $container[0], $sidebar[0] ) ) {
			$container = $sidebar.parent();
		} // if( $container.length === 0 || !$.contains( $container[0], $sidebar[0] ) ) {

		// --------------------------------------
		// move everything inside the sidebar into one inner div, this is the element that moves
		// the elements are moved with the browser and not with jQuery so scripts inside the sidebar will not run twice
		// --------------------------------------

		var $inner = $sidebar.children( '.infinity23-sticky-sidebar-inner' );

		if( $inner.length === 0 ) {

			var inner_element = document.createElement( 'div' );
			inner_element.className = 'infinity23-sticky-sidebar-inner';
			inner_element.style.display = 'flow-root'; // keeps floated elements and margins of the sidebar elements inside of the inner div

			while( $sidebar[0].firstChild ) {
				inner_element.appendChild( $sidebar[0].firstChild );
			} // while( $sidebar[0].firstChild ) {

			$sidebar[0].appendChild( inner_element );
			$inner = $( inner_element );

		} // if( $inner.length === 0 ) {

		// the moving inner div must be visible outside of the sidebar box (important, so a theme rule can not hide it),
		// and it is positioned from the sidebar so the sidebar can not be static
		$sidebar[0].style.setProperty( 'overflow', 'visible', 'important' );

		if( $sidebar.css( 'position' ) === 'static' ) {
			$sidebar.css( 'position', 'relative' );
		} // if( $sidebar.css( 'position' ) === 'static' ) {

		var previous_scroll_top = null; // used to know if the visitor scrolls up or down
		var update_scheduled = false;

		// --------------------------------------
		// the bottom of the container, without its padding and border
		// a container that only has floated elements has no height so we also check the height of its children
		// --------------------------------------

		function infinity23_ss_container_bottom() {

			var container_height = $container.height();

			$container.children().each( function() {
				container_height = Math.max( container_height, $( this ).outerHeight() );
			}); // $container.children().each( function() {

			var container_top = $container.offset().top + infinity23_ss_number( $container.css( 'border-top-width' ) ) + infinity23_ss_number( $container.css( 'padding-top' ) );

			return container_top + container_height;

		} // function infinity23_ss_container_bottom() {

		// --------------------------------------
		// put the sidebar back to its normal place
		// --------------------------------------

		function infinity23_ss_reset() {

			$inner.css({
				'position': 'static',
				'width': '',
				'left': '',
				'top': '',
				'transform': 'none'
			});

			$sidebar.css( 'min-height', '' ).removeClass( 'infinity23-sticky-sidebar-active' );

		} // function infinity23_ss_reset() {

		// ====================================================================================================
		// Set the sidebar position, runs on scroll, resize and when the page height changes
		// ====================================================================================================

		function infinity23_ss_update() {

			update_scheduled = false;

			// nothing to do when the sidebar is hidden
			if( !$inner.is( ':visible' ) ) { return; }

			// --------------------------------------
			// stop on small windows
			// --------------------------------------

			if( $( window ).width() < infinity23_ss_min_width ) {
				infinity23_ss_reset();
				previous_scroll_top = null;
				return;
			} // if( $( window ).width() < infinity23_ss_min_width ) {

			// --------------------------------------
			// stop when the theme moved the sidebar under the content (it is now as wide as the container)
			// --------------------------------------

			var sidebar_outer_width = $sidebar.outerWidth( $sidebar.css( 'float' ) === 'none' );

			if( sidebar_outer_width + 50 > $container.width() ) {
				infinity23_ss_reset();
				previous_scroll_top = null;
				return;
			} // if( sidebar_outer_width + 50 > $container.width() ) {

			// --------------------------------------
			// the spacing from the top of the window, a fixed admin bar is added to it
			// --------------------------------------

			var admin_bar_height = 0;
			var $admin_bar = $( '#wpadminbar' );

			if( $admin_bar.length && $admin_bar.css( 'position' ) === 'fixed' ) {
				admin_bar_height = $admin_bar.outerHeight();
			} // if( $admin_bar.length && $admin_bar.css( 'position' ) === 'fixed' ) {

			var top_spacing = infinity23_ss_top_spacing + admin_bar_height;
			var bottom_spacing = infinity23_ss_bottom_spacing;

			// --------------------------------------
			// measure the sidebar and the window
			// --------------------------------------

			var scroll_top = $( document ).scrollTop();
			var window_height = $( window ).height();

			var border_top = infinity23_ss_number( $sidebar.css( 'border-top-width' ) );
			var border_left = infinity23_ss_number( $sidebar.css( 'border-left-width' ) );
			var padding_top = border_top + infinity23_ss_number( $sidebar.css( 'padding-top' ) );
			var padding_bottom = infinity23_ss_number( $sidebar.css( 'border-bottom-width' ) ) + infinity23_ss_number( $sidebar.css( 'padding-bottom' ) );
			var padding_left = infinity23_ss_number( $sidebar.css( 'padding-left' ) );
			var padding_right = infinity23_ss_number( $sidebar.css( 'padding-right' ) );
			var margin_bottom = infinity23_ss_number( $sidebar.css( 'margin-bottom' ) );

			var sidebar_top = $sidebar.offset().top; // the sidebar does not move so this is always its original place
			var inner_height = $inner.outerHeight();
			var inner_width = $sidebar[0].clientWidth - padding_left - padding_right;

			var position = 'static';
			var top = 0;

			// --------------------------------------
			// the sidebar can only move after the visitor scrolled past its original place
			// --------------------------------------

			if( scroll_top >= sidebar_top + ( padding_top - top_spacing ) ) {

				var offset_top = padding_top + top_spacing;
				var offset_bottom = padding_bottom + margin_bottom + bottom_spacing;

				// the top and bottom of the sidebar inside the window, when the sidebar is fixed
				var window_offset_top = top_spacing;
				var window_offset_bottom;

				if( ( inner_height + offset_top + offset_bottom ) < window_height ) {
					// a short sidebar sticks to the top of the window
					window_offset_bottom = window_offset_top + inner_height;
				} else {
					// a tall sidebar moves with the page until its bottom is in view, then it sticks to the bottom of the window
					window_offset_bottom = window_height - margin_bottom - padding_bottom - bottom_spacing;
				} // if( ( inner_height + offset_top + offset_bottom ) < window_height ) {

				// the limits of the container, the sidebar can not go above or below them
				var static_limit_top = sidebar_top - scroll_top + padding_top;
				var static_limit_bottom = infinity23_ss_container_bottom() - scroll_top - padding_bottom - margin_bottom;

				var scroll_top_diff = previous_scroll_top - scroll_top;

				// where the sidebar is right now, relative to the top of the window
				top = $inner.offset().top - scroll_top;

				// a fixed sidebar does not move with the page, so we move it by the amount that the visitor scrolled
				if( $inner.css( 'position' ) === 'fixed' ) {
					top += scroll_top_diff;
				} // if( $inner.css( 'position' ) === 'fixed' ) {

				if( scroll_top_diff > 0 ) {
					top = Math.min( top, window_offset_top ); // scrolling up
				} else {
					top = Math.max( top, window_offset_bottom - inner_height ); // scrolling down
				} // if( scroll_top_diff > 0 ) {

				top = Math.max( top, static_limit_top );
				top = Math.min( top, static_limit_bottom - inner_height );

				// --------------------------------------
				// choose the position - fixed while the sidebar is at the top or bottom of the window,
				// static at the top of the container and absolute when it stays in the middle of the page or at the bottom of the container
				// --------------------------------------

				var same_height_as_container = ( $container.height() === inner_height );

				if( !same_height_as_container && top === window_offset_top ) {
					position = 'fixed';
				} else if( !same_height_as_container && top === window_offset_bottom - inner_height ) {
					position = 'fixed';
				} else if( scroll_top + top - sidebar_top - padding_top <= top_spacing ) {
					position = 'static';
				} else {
					position = 'absolute';
				} // if( !same_height_as_container && top === window_offset_top ) {

				var stuck_to_container_bottom = ( top === static_limit_bottom - inner_height );

			} // if( scroll_top >= sidebar_top + ( padding_top - top_spacing ) ) {

			// --------------------------------------
			// set the position
			// --------------------------------------

			if( position === 'fixed' ) {

				$inner.css({
					'position': 'fixed',
					'width': inner_width + 'px',
					'transform': 'translateY(' + top + 'px)',
					'left': ( $sidebar.offset().left + border_left + padding_left - $( document ).scrollLeft() ) + 'px',
					'top': '0px'
				});

			} else if( position === 'absolute' ) {

				var absolute_css = {
					'width': inner_width + 'px',
					'left': ''
				};

				// the sidebar stays where it is on the page, so the place is only set when it becomes absolute or when the bottom of the container moved
				if( $inner.css( 'position' ) !== 'absolute' || stuck_to_container_bottom ) {
					absolute_css['position'] = 'absolute';
					absolute_css['transform'] = 'translateY(' + ( scroll_top + top - sidebar_top - border_top ) + 'px)';
					absolute_css['top'] = '0px';
				} // if( $inner.css( 'position' ) !== 'absolute' || stuck_to_container_bottom ) {

				$inner.css( absolute_css );

			} else {
				infinity23_ss_reset();
			} // if( position === 'fixed' ) {

			// --------------------------------------
			// while the sidebar is moving it is out of the page flow, so we keep the sidebar box at its full height
			// (this keeps the sidebar background, the border and the height of the container)
			// --------------------------------------

			if( position !== 'static' ) {

				$sidebar.addClass( 'infinity23-sticky-sidebar-active' );

				if( infinity23_ss_update_height ) {

					var sidebar_min_height = inner_height;

					if( $sidebar.css( 'box-sizing' ) === 'border-box' ) {
						sidebar_min_height += padding_top + padding_bottom;
					} // if( $sidebar.css( 'box-sizing' ) === 'border-box' ) {

					$sidebar.css( 'min-height', sidebar_min_height + 'px' );

				} // if( infinity23_ss_update_height ) {

			} // if( position !== 'static' ) {

			previous_scroll_top = scroll_top;

		} // function infinity23_ss_update() {

		// --------------------------------------
		// run the update once for every screen frame, no matter how many scroll events happened
		// --------------------------------------

		function infinity23_ss_schedule() {

			if( update_scheduled ) { return; }

			update_scheduled = true;

			if( window.requestAnimationFrame ) {
				window.requestAnimationFrame( infinity23_ss_update );
			} else {
				setTimeout( infinity23_ss_update, 16 );
			} // if( window.requestAnimationFrame ) {

		} // function infinity23_ss_schedule() {

		// ====================================================================================================
		// Choosing when to update the sidebar position
		// ====================================================================================================

		// set the position when the page loads (incase the visitor loads the page when it is scrolled down)
		infinity23_ss_update();

		$( window ).on( 'scroll', infinity23_ss_schedule );

		// images and fonts change the height of the page after it loaded
		$( window ).on( 'load', infinity23_ss_schedule );

		$( window ).on( 'resize', function() {
			$inner.css( 'position', 'static' );
			infinity23_ss_schedule();
		}); // $( window ).on( 'resize', function() {

		// update when the height of the sidebar or the content changes (tabs, accordions, lazy loaded content and more)
		if( typeof ResizeObserver !== 'undefined' ) {

			var infinity23_ss_observer = new ResizeObserver( infinity23_ss_schedule );
			infinity23_ss_observer.observe( $inner[0] );
			infinity23_ss_observer.observe( $container[0] );

		} // if( typeof ResizeObserver !== 'undefined' ) {

	} // function infinity23_ss_create( $sidebar ) {

	// ====================================================================================================
	// Start the sticky sidebar on every sidebar we found
	// ====================================================================================================

	infinity23_ss_find( infinity23_sticky_sidebar_vars.sidebar_selector ).each( function() {
		infinity23_ss_create( $( this ) );
	}); // infinity23_ss_find( infinity23_sticky_sidebar_vars.sidebar_selector ).each( function() {

}); // jQuery(document).ready(function($) {
INFINITY23_STICKY_SIDEBAR_JS;

		} // function infinity23_sticky_sidebar_get_js() {

	} // if( !function_exists( 'infinity23_sticky_sidebar_get_js' ) ) {

	// ===========================================================================
	// load jquery, print the script and send the saved settings to it
	// ===========================================================================

	add_action( 'wp_enqueue_scripts', function() use ( $infinity23_ss_settings ) {

		if( !infinity23_sticky_sidebar_page_type_allowed( $infinity23_ss_settings ) ) { return; }

		if( !infinity23_module_should_display( $infinity23_ss_settings['exclude_ids'], $infinity23_ss_settings['exclude_option'] ) ) { return; }

		// no file - this name only holds the inline code below, and jquery is always printed before it
		wp_register_script( 'infinity23_sticky_sidebar_js', false, array( 'jquery' ), INFINITY23_VERSION, true );
		wp_enqueue_script( 'infinity23_sticky_sidebar_js' );

		$infinity23_ss_js_vars = array(
			'sidebar_selector' => $infinity23_ss_settings['sidebar_selector'],
			'container_selector' => $infinity23_ss_settings['container_selector'],
			'top_spacing' => $infinity23_ss_settings['top_spacing'],
			'bottom_spacing' => $infinity23_ss_settings['bottom_spacing'],
			'min_width' => $infinity23_ss_settings['min_width'],
			'update_sidebar_height' => $infinity23_ss_settings['update_sidebar_height'],
		);

		wp_add_inline_script( 'infinity23_sticky_sidebar_js', 'var infinity23_sticky_sidebar_vars = ' . wp_json_encode( $infinity23_ss_js_vars, JSON_HEX_TAG | JSON_HEX_AMP ) . ';', 'before' );

		wp_add_inline_script( 'infinity23_sticky_sidebar_js', infinity23_sticky_sidebar_get_js() );

	} ); // add_action( 'wp_enqueue_scripts'

?>

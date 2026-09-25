// ----------------------------------------------
// infinity23 accessibility js code
// ----------------------------------------------

jQuery(document).ready(function($) {

	// ====================================================================================================
	// Current accessibility state
	// ====================================================================================================

	var infinity23_accessibility_state = {
		font: 0,
		contrast: 'none',
		links: 0
	};

	// ====================================================================================================
	// Add the no-accessibility class to elements that should not be changed
	// ====================================================================================================

	if( typeof infinity23_accessibility_vars !== 'undefined' && infinity23_accessibility_vars.exclude_admin_bar == 1 ) {
		$('#wpadminbar').addClass('no-accessibility');
	} else {
		$('#wpadminbar').removeClass('no-accessibility');
	} // if( typeof infinity23_accessibility_vars !== 'undefined'

	// ====================================================================================================
	// Display and hide the main box when clicking on the accessibility button
	// ====================================================================================================

	$('.yy-button').click(function() {

		var accessibilityDisplay = $('.yy-box').css('display');

		if( accessibilityDisplay == 'none' ) {
			$('.yy-box').css('display', 'block');
			$('.yy-button').attr('aria-expanded', 'true');
		} else { // if( accessibilityDisplay == 'none' ) {
			$('.yy-box').css('display', 'none');
			$('.yy-button').attr('aria-expanded', 'false');
		} // } else { // if( accessibilityDisplay == 'none' ) {

		infinity23_accessibility_save_font_sizes();

		return false;

	}); // $('.yy-button').click(function() {

	// close the box if clicked outside
	$(document).on('click', function(e) {
		if( $(e.target).closest('.infinity23-accessibility').length === 0 ) {
			$('.yy-box').css('display', 'none');
			$('.yy-button').attr('aria-expanded', 'false');
		} // if( $(e.target).closest('.infinity23-accessibility').length === 0 ) {
	});

	// close the box when pressing the escape key
	$(document).on('keydown', function(e) {
		if( (e.key === 'Escape' || e.keyCode === 27) && $('.yy-box').css('display') !== 'none' ) {
			$('.yy-box').css('display', 'none');
			$('.yy-button').attr('aria-expanded', 'false');
			$('.yy-button').focus();
		} // if( (e.key === 'Escape' || e.keyCode === 27)
	});

	// ====================================================================================================
	// Reset all accessibility options
	// ====================================================================================================

	$('a.rest-accessibility').click(function() {

		accessibility_reset_font_size();
		infinity23_accessibility_reset_light_contrast();
		infinity23_accessibility_reset_height_contrast();
		$('body').removeClass('infinity23-highlight-links');

		infinity23_accessibility_state.font = 0;
		infinity23_accessibility_state.contrast = 'none';
		infinity23_accessibility_state.links = 0;
		infinity23_accessibility_write_cookie();

		$('a.increase-font-size, a.decrease-font-size').removeClass('not-active');
		$('.light-contrast-color, .high-contrast-color, .highlight-links').attr('aria-pressed', 'false').removeClass('yy-option-active');

		return false;

	}); // $('a.rest-accessibility').click(function() {

	// ====================================================================================================
	// Save the original font size of every element once
	// ====================================================================================================

	function infinity23_accessibility_save_font_sizes() {

		if( $('body').hasClass('infinity23-active-accessibility') ) { return; }

		$('*').each(function() {

			if( infinity23_accessibilityClassCheck($(this)) ) {

				var elementFontSize = parseInt($(this).css('font-size'));

				if( !$(this).data('yy-font-size') ) {
					$(this).attr('data-yy-font-size', elementFontSize);
				} // if( !$(this).data('yy-font-size') ) {

				$(this).css('font-size', elementFontSize + 'px');

			} // if( infinity23_accessibilityClassCheck($(this)) ) {

		}); // $('*').each(function() {

		$('body').addClass('infinity23-active-accessibility');

	} // function infinity23_accessibility_save_font_sizes() {

	// ====================================================================================================
	// Function that tests if we should make changes on this element
	// ====================================================================================================

	function infinity23_accessibilityClassCheck(className) {

		if( !( className.hasClass('no-accessibility') || className.parents('.no-accessibility').length ) ) {
			return 1;
		} else { // if( !( className.hasClass('no-accessibility') || className.parents('.no-accessibility').length ) ) {
			return 0;
		} // } else {

	} // function infinity23_accessibilityClassCheck(className) {

	// ====================================================================================================
	// Cookie functions
	// ====================================================================================================

	function infinity23_getCookie(key) {
		var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
		return keyValue ? keyValue[2] : null;
	} // function infinity23_getCookie(key) {

	function infinity23_setCookie(cookieName, cookievalue, cookieTime) {
		var expires = new Date();
		var secureFlag = ( window.location.protocol === 'https:' ) ? ';secure' : '';
		expires.setTime(expires.getTime() + (cookieTime));
		document.cookie = cookieName + '=' + cookievalue + ';path=/' + ';expires=' + expires.toUTCString() + ';SameSite=Lax' + secureFlag;
	} // function infinity23_setCookie(cookieName, cookievalue, cookieTime) {

	// ====================================================================================================
	// Saved settings
	// ====================================================================================================

	// --------------------------------------
	// check if saving is active
	// --------------------------------------

	function infinity23_accessibility_save_is_active() {

		if( typeof infinity23_accessibility_vars === 'undefined' ) { return false; }
		if( infinity23_accessibility_vars.save_settings != 1 ) { return false; }

		return true;

	} // function infinity23_accessibility_save_is_active() {

	// --------------------------------------
	// get the cookie duration in milliseconds
	// --------------------------------------

	function infinity23_accessibility_cookie_time() {

		var minutes = 60;

		if( typeof infinity23_accessibility_vars !== 'undefined' && parseInt( infinity23_accessibility_vars.save_minutes ) > 0 ) {
			minutes = parseInt( infinity23_accessibility_vars.save_minutes );
		} // if( typeof infinity23_accessibility_vars !== 'undefined'

		return minutes * 60 * 1000;

	} // function infinity23_accessibility_cookie_time() {

	// --------------------------------------
	// build a signature of the current admin settings
	// --------------------------------------

	function infinity23_accessibility_settings_signature() {

		var adminBar = 0;
		var minutes = 60;

		if( typeof infinity23_accessibility_vars !== 'undefined' ) {

			adminBar = ( parseInt( infinity23_accessibility_vars.exclude_admin_bar ) == 1 ) ? 1 : 0;

			if( parseInt( infinity23_accessibility_vars.save_minutes ) > 0 ) {
				minutes = parseInt( infinity23_accessibility_vars.save_minutes );
			} // if( parseInt( infinity23_accessibility_vars.save_minutes ) > 0 ) {

		} // if( typeof infinity23_accessibility_vars !== 'undefined' ) {

		return adminBar + '-' + minutes;

	} // function infinity23_accessibility_settings_signature() {

	// --------------------------------------
	// write the current state into one cookie
	// --------------------------------------

	function infinity23_accessibility_write_cookie() {

		if( !infinity23_accessibility_save_is_active() ) {
			infinity23_setCookie('infinity23-accessibility', '', -1);
			return;
		} // if( !infinity23_accessibility_save_is_active() ) {

		if( infinity23_accessibility_state.font == 0 && infinity23_accessibility_state.contrast == 'none' && infinity23_accessibility_state.links == 0 ) {
			infinity23_setCookie('infinity23-accessibility', '', -1);
			return;
		} // if( infinity23_accessibility_state.font == 0 &&

		var cookieData = {
			font: parseInt( infinity23_accessibility_state.font ),
			contrast: infinity23_accessibility_state.contrast,
			links: parseInt( infinity23_accessibility_state.links ),
			settings: infinity23_accessibility_settings_signature()
		};

		infinity23_setCookie('infinity23-accessibility', encodeURIComponent( JSON.stringify( cookieData ) ), infinity23_accessibility_cookie_time());

	} // function infinity23_accessibility_write_cookie() {

	// --------------------------------------
	// read the cookie into the state
	// --------------------------------------

	function infinity23_accessibility_read_cookie() {

		var cookieValue = infinity23_getCookie('infinity23-accessibility');

		if( cookieValue == null || cookieValue === '' ) { return; }

		var savedData = null;

		try {
			savedData = JSON.parse( decodeURIComponent( cookieValue ) );
		} catch( e ) {
			infinity23_setCookie('infinity23-accessibility', '', -1);
			return;
		} // try {

		if( savedData === null || typeof savedData !== 'object' || savedData instanceof Array ) {
			infinity23_setCookie('infinity23-accessibility', '', -1);
			return;
		} // if( savedData === null ||

		if( savedData.settings !== infinity23_accessibility_settings_signature() ) {
			infinity23_setCookie('infinity23-accessibility', '', -1);
			return;
		} // if( savedData.settings !== infinity23_accessibility_settings_signature() ) {

		var savedFont = parseInt( savedData.font );
		var savedContrast = String( savedData.contrast );
		var savedLinks = parseInt( savedData.links );

		if( isNaN(savedFont) || savedFont < -3 || savedFont > 3 ) {
			savedFont = 0;
		} // if( isNaN(savedFont) || savedFont < -3 || savedFont > 3 ) {

		if( savedContrast !== 'light' && savedContrast !== 'high' ) {
			savedContrast = 'none';
		} // if( savedContrast !== 'light' && savedContrast !== 'high' ) {

		if( savedLinks !== 1 ) {
			savedLinks = 0;
		} // if( savedLinks !== 1 ) {

		infinity23_accessibility_state.font = savedFont;
		infinity23_accessibility_state.contrast = savedContrast;
		infinity23_accessibility_state.links = savedLinks;

	} // function infinity23_accessibility_read_cookie() {

	// --------------------------------------
	// apply the saved settings when the page loads
	// --------------------------------------

	function infinity23_accessibility_load_saved_settings() {

		if( !infinity23_accessibility_save_is_active() ) {
			infinity23_setCookie('infinity23-accessibility', '', -1);
			return;
		} // if( !infinity23_accessibility_save_is_active() ) {

		infinity23_accessibility_read_cookie();

		if( infinity23_accessibility_state.font != 0 ) {
			infinity23_accessibility_save_font_sizes();
			infinity23_accessiblity_change_size();
		} // if( infinity23_accessibility_state.font != 0 ) {

		if( infinity23_accessibility_state.contrast == 'light' ) {
			infinity23_accessibility_light_contrast();
			$('.light-contrast-color').attr('aria-pressed', 'true').addClass('yy-option-active');
		} // if( infinity23_accessibility_state.contrast == 'light' ) {

		if( infinity23_accessibility_state.contrast == 'high' ) {
			infinity23_accessibility_height_contrast();
			$('.high-contrast-color').attr('aria-pressed', 'true').addClass('yy-option-active');
		} // if( infinity23_accessibility_state.contrast == 'high' ) {

		if( infinity23_accessibility_state.links == 1 ) {
			$('body').addClass('infinity23-highlight-links');
			$('.highlight-links').attr('aria-pressed', 'true').addClass('yy-option-active');
		} // if( infinity23_accessibility_state.links == 1 ) {

	} // function infinity23_accessibility_load_saved_settings() {

	// ====================================================================================================
	// Font size
	// ====================================================================================================

	$('.increase-font-size').click(function() {
		infinity23_accessiblity_font_resize(1);
		return false;
	}); // $('.increase-font-size').click(function() {

	$('.decrease-font-size').click(function() {
		infinity23_accessiblity_font_resize(-1);
		return false;
	}); // $('.decrease-font-size').click(function() {

	function infinity23_accessiblity_font_resize(resizeNumber) {

		infinity23_accessibility_save_font_sizes();

		var smallestFontSize = -3;
		var biggestFontSize = 3;

		var newFontStep = infinity23_accessibility_state.font + parseInt(resizeNumber);

		if( newFontStep > biggestFontSize ) {
			$('a.increase-font-size').addClass('not-active');
			return;
		} // if( newFontStep > biggestFontSize ) {

		if( newFontStep < smallestFontSize ) {
			$('a.decrease-font-size').addClass('not-active');
			return;
		} // if( newFontStep < smallestFontSize ) {

		infinity23_accessibility_state.font = newFontStep;
		infinity23_accessiblity_change_size();
		infinity23_accessibility_write_cookie();

		$('a.increase-font-size, a.decrease-font-size').removeClass('not-active');

		if( newFontStep == biggestFontSize ) {
			$('a.increase-font-size').addClass('not-active');
		} // if( newFontStep == biggestFontSize ) {

		if( newFontStep == smallestFontSize ) {
			$('a.decrease-font-size').addClass('not-active');
		} // if( newFontStep == smallestFontSize ) {

	} // function infinity23_accessiblity_font_resize(resizeNumber) {

	function infinity23_accessiblity_change_size() {

		var totalChange = infinity23_accessibility_state.font;

		$('[data-yy-font-size]').each(function() {

			var baseFontSize = parseInt( $(this).attr('data-yy-font-size') );

			if( !isNaN(baseFontSize) ) {
				$(this).css('font-size', (baseFontSize + totalChange) + 'px');
			} // if( !isNaN(baseFontSize) ) {

		}); // $('[data-yy-font-size]').each(function() {

	} // function infinity23_accessiblity_change_size() {

	function accessibility_reset_font_size() {

		infinity23_accessibility_state.font = 0;

		$('[data-yy-font-size]').each(function() {

			var baseFontSize = parseInt( $(this).attr('data-yy-font-size') );

			if( !isNaN(baseFontSize) ) {
				$(this).css('font-size', baseFontSize + 'px');
			} // if( !isNaN(baseFontSize) ) {

		}); // $('[data-yy-font-size]').each(function() {

	} // function accessibility_reset_font_size() {

	// ====================================================================================================
	// Light contrast
	// ====================================================================================================

	$('.light-contrast-color').click(function() {

		if( infinity23_accessibility_state.contrast == 'light' ) {
			infinity23_accessibility_reset_light_contrast();
			infinity23_accessibility_state.contrast = 'none';
			$('.light-contrast-color').attr('aria-pressed', 'false').removeClass('yy-option-active');
		} else {
			infinity23_accessibility_light_contrast();
			infinity23_accessibility_state.contrast = 'light';
			$('.light-contrast-color').attr('aria-pressed', 'true').addClass('yy-option-active');
			$('.high-contrast-color').attr('aria-pressed', 'false').removeClass('yy-option-active');
		} // if( infinity23_accessibility_state.contrast == 'light' ) {

		infinity23_accessibility_write_cookie();

		return false;

	}); // $('.light-contrast-color').click(function() {

	function infinity23_accessibility_light_contrast() {

		$('*').each(function() {

			if( infinity23_accessibilityClassCheck($(this)) ) {
				$(this).removeClass('accessibility-high-contrast-color');
				$(this).addClass('accessibility-light-contrast-color');
			} // if( infinity23_accessibilityClassCheck($(this)) ) {

		}); // $('*').each(function() {

	} // function infinity23_accessibility_light_contrast() {

	function infinity23_accessibility_reset_light_contrast() {

		$('.accessibility-light-contrast-color').removeClass('accessibility-light-contrast-color');

	} // function infinity23_accessibility_reset_light_contrast() {

	// ====================================================================================================
	// High contrast
	// ====================================================================================================

	$('.high-contrast-color').click(function() {

		if( infinity23_accessibility_state.contrast == 'high' ) {
			infinity23_accessibility_reset_height_contrast();
			infinity23_accessibility_state.contrast = 'none';
			$('.high-contrast-color').attr('aria-pressed', 'false').removeClass('yy-option-active');
		} else {
			infinity23_accessibility_height_contrast();
			infinity23_accessibility_state.contrast = 'high';
			$('.high-contrast-color').attr('aria-pressed', 'true').addClass('yy-option-active');
			$('.light-contrast-color').attr('aria-pressed', 'false').removeClass('yy-option-active');
		} // if( infinity23_accessibility_state.contrast == 'high' ) {

		infinity23_accessibility_write_cookie();

		return false;

	}); // $('.high-contrast-color').click(function() {

	function infinity23_accessibility_height_contrast() {

		$('*').each(function() {

			if( infinity23_accessibilityClassCheck($(this)) ) {
				$(this).removeClass('accessibility-light-contrast-color');
				$(this).addClass('accessibility-high-contrast-color');
			} // if( infinity23_accessibilityClassCheck($(this)) ) {

		}); // $('*').each(function() {

	} // function infinity23_accessibility_height_contrast() {

	function infinity23_accessibility_reset_height_contrast() {

		$('.accessibility-high-contrast-color').removeClass('accessibility-high-contrast-color');

	} // function infinity23_accessibility_reset_height_contrast() {

	// ====================================================================================================
	// Highlight links
	// ====================================================================================================

	$('.highlight-links').click(function() {

		$('body').toggleClass('infinity23-highlight-links');

		infinity23_accessibility_state.links = $('body').hasClass('infinity23-highlight-links') ? 1 : 0;

		$(this).attr('aria-pressed', infinity23_accessibility_state.links == 1 ? 'true' : 'false');
		$(this).toggleClass('yy-option-active', infinity23_accessibility_state.links == 1);

		infinity23_accessibility_write_cookie();

		return false;

	}); // $('.highlight-links').click(function() {

	// ====================================================================================================
	// Load the saved settings when the page loads
	// ====================================================================================================

	infinity23_accessibility_load_saved_settings();

}); // jQuery(document).ready(function($) {
// ===========================================
// Go Pro - open its sidebar link in a new tab. add_submenu_page() has no
// way to set target/rel on the link itself, so it's set client-side on the
// one matching anchor once the sidebar has rendered
// ===========================================

document.addEventListener( 'DOMContentLoaded', function() {
	var infinity23GoProLink = document.querySelector( '#adminmenu a[href*="page=infinity23-essential-go-pro"]' );
	if( infinity23GoProLink ) {
		infinity23GoProLink.setAttribute( 'target', '_blank' );
		infinity23GoProLink.setAttribute( 'rel', 'noopener' );
	} // if( infinity23GoProLink ) {
} );

// ===========================================
// Notices (Global) - dismiss button handlers, shared by every admin notice
// ===========================================

jQuery(document).ready(function($) {

	// --------------------------------------
	// button_click_action = "dismiss" - stop the normal link, fade out, save by ajax
	// --------------------------------------

	$(document).on('click', '.infinity23-notice-dismiss-link[data-click-action="dismiss"]', function(e) {

		e.preventDefault();

		var $notice = $(this).closest('.infinity23-notice-style');
		var noticeId = $notice.data('notice-id');
		var dismissType = $(this).data('dismiss-type');
		var snoozeHours = $(this).data('snooze-hours');
		var dismissNonce = $(this).data('dismiss-nonce');

		$notice.animate({ opacity: '0' }, 200, function() {
			$notice.css('display', 'none');
		});

		var data = {
			'action': 'infinity23_notice_dismiss',
			'notice': noticeId,
			'type': dismissType,
			'snooze_hours': snoozeHours,
			'infinity23_notice_nonce': dismissNonce
		};

		// sendBeacon (not $.post) so the save survives an immediate page
		// refresh - a normal AJAX request gets cancelled by navigation
		// before it reaches the server, which is why a fast refresh right
		// after clicking used to leave the notice un-dismissed
		if( navigator.sendBeacon ) {
			var beaconData = new FormData();
			for( var dataKey in data ) {
				beaconData.append( dataKey, data[dataKey] );
			} // for( var dataKey in data ) {
			navigator.sendBeacon( ajaxurl, beaconData );
		} else {
			$.post(ajaxurl, data, function(response) {});
		} // if( navigator.sendBeacon ) {

	}); // $(document).on('click', '.infinity23-notice-dismiss-link[data-click-action="dismiss"]'

	// --------------------------------------
	// button_click_action = "dismiss_new_tab" - let the real href navigate
	// (usually to a new tab), the url itself saves the dismiss, just fade this tab
	// --------------------------------------

	$(document).on('click', '.infinity23-notice-dismiss-link[data-click-action="dismiss_new_tab"]', function() {

		var $notice = $(this).closest('.infinity23-notice-style');
		$notice.animate({ opacity: '0' }, 200, function() {
			$notice.css('display', 'none');
		});

	}); // $(document).on('click', '.infinity23-notice-dismiss-link[data-click-action="dismiss_new_tab"]'

}); // jQuery(document).ready(function($) {

// ===========================================
// RTL/LTR admin bar button - flips the direction on screen, highlights the
// button when it no longer matches the site's default direction, and
// saves or clears the page's entry depending on the Save Page setting
// ===========================================

jQuery(document).ready(function($) {

	$('#wp-admin-bar-infinity23-rtl-ltr > .ab-item').on('click', function(e) {

		e.preventDefault();

		var newDirection = ( infinity23RtlLtrCurrentDirection === 'rtl' ) ? 'ltr' : 'rtl';
		infinity23RtlLtrCurrentDirection = newDirection;

		$('#wpbody').css('direction', newDirection);

		if( infinity23RtlLtrLabelFormat === 'icon_only' ) {
			// no label update needed - icon only has no text
		} else {
			var newLabelText = ( infinity23RtlLtrLabelFormat === 'short' || infinity23RtlLtrLabelFormat === 'text_only' ) ? newDirection.toUpperCase() : 'Direction: ' + newDirection.toUpperCase();
			$(this).find('.infinity23-rtl-ltr-label').text(newLabelText);
		} // if( infinity23RtlLtrLabelFormat === 'icon_only' ...

		if( newDirection === infinity23RtlLtrDefaultDirection ) {
			$('#wp-admin-bar-infinity23-rtl-ltr').removeClass('infinity23-rtl-ltr-non-default');
		} else {
			$('#wp-admin-bar-infinity23-rtl-ltr').addClass('infinity23-rtl-ltr-non-default');
		} // if( newDirection === infinity23RtlLtrDefaultDirection )

		var infinity23RtlLtrToggleData = {
			action: 'infinity23_rtl_ltr_toggle',
			nonce: infinity23RtlLtrNonce,
			direction: newDirection,
			page_key: infinity23RtlLtrPageKey
		};

		// sendBeacon (not $.post) so the save survives immediately
		// navigating away right after clicking the toggle
		if( navigator.sendBeacon ) {
			var infinity23RtlLtrBeaconData = new FormData();
			for( var infinity23RtlLtrDataKey in infinity23RtlLtrToggleData ) {
				infinity23RtlLtrBeaconData.append( infinity23RtlLtrDataKey, infinity23RtlLtrToggleData[infinity23RtlLtrDataKey] );
			} // for( var infinity23RtlLtrDataKey in infinity23RtlLtrToggleData ) {
			navigator.sendBeacon( ajaxurl, infinity23RtlLtrBeaconData );
		} else {
			$.post( ajaxurl, infinity23RtlLtrToggleData );
		} // if( navigator.sendBeacon ) {

	}); // $('#wp-admin-bar-infinity23-rtl-ltr > .ab-item').on('click'

}); // jQuery(document).ready(function($)

// ===========================================
// Plugins/Themes Downloader - theme download links (Themes page only -
// harmless no-op everywhere else since div.theme-actions won't exist)
// ===========================================

jQuery(document).ready(function($) {

	// --------------------------------------
	// button inside the actions row - slug is read from the closest
	// div.theme[data-slug] ancestor, the same way the theme grid itself
	// identifies each card
	// --------------------------------------

	// the theme download nonces only exist when the Plugins/Themes Downloader adds them on themes.php
	if( typeof infinity23PtdNonces === 'undefined' ) { return; }

	$('div.theme-actions').each(function() {

		var slug = $(this).closest('div.theme').attr('data-slug');
		var nonce = slug ? infinity23PtdNonces[slug] : '';

		if (!slug || !nonce) { return; }

		var url = infinity23PtdAjaxUrl +
			'?action=infinity23_ptd_download' +
			'&infinity23_ptd_type=theme' +
			'&infinity23_ptd_slug=' + encodeURIComponent(slug) +
			'&infinity23_ptd_nonce=' + encodeURIComponent(nonce);

		$(this).append('<a class="infinity23-ptd-theme-badge" href="' + url + '">' + infinity23PtdLinkText + '</a>');

	}); // $('div.theme-actions').each

});

// ===========================================
// Page Notes - meta box / dashboard widget
// ===========================================

jQuery(document).ready(function($) {

	// --------------------------------------
	// grow a note textarea to fit its content - only runs when the
	// infinity23-textarea-adjustable class is present on the textarea
	// --------------------------------------

	function infinity23_notes_auto_resize_textarea($textarea) {

		if( !$textarea.hasClass('infinity23-textarea-adjustable') ) { return; }

		var scrollTop = $(window).scrollTop();
		var minHeight = parseInt( $textarea.css('min-height') ) || 200;

		$textarea.css('height', 'auto');
		var thisHeight = $textarea.prop('scrollHeight');
		$textarea.css('height', Math.max(thisHeight, minHeight) + 'px');

		$(window).scrollTop(scrollTop);

	} // function infinity23_notes_auto_resize_textarea($textarea) {

	$('.infinity23-notes-wrap textarea').each(function() {
		infinity23_notes_auto_resize_textarea($(this));
	}); // $('.infinity23-notes-wrap textarea').each

	$(document).on('input', '.infinity23-notes-wrap textarea', function() {
		infinity23_notes_auto_resize_textarea($(this));
	}); // $(document).on('input', '.infinity23-notes-wrap textarea'

	// --------------------------------------
	// show the per-note remove icon only when there is more than one
	// note in the widget (relevant inside the All Notes table, where a
	// single note is removed via the Actions column instead)
	// --------------------------------------

	function infinity23_notes_update_remove_icon_visibility($wrap) {

		if( !$wrap.closest('.notes-col-preview').length ) { return; }

		var activeCount = $wrap.find('.infinity23-note-item').not('.infinity23-note-not-active').length;

		if( activeCount > 1 ) {
			$wrap.addClass('infinity23-notes-all-multiple');
		} else {
			$wrap.removeClass('infinity23-notes-all-multiple');
		} // if( activeCount > 1 ) {

	} // function infinity23_notes_update_remove_icon_visibility($wrap) {

	$('.notes-col-preview .infinity23-notes-wrap').each(function() {
		infinity23_notes_update_remove_icon_visibility( $(this) );
	}); // .each

	// --------------------------------------
	// add note - opens the first box, then clones the last one
	// --------------------------------------

	$(document).on('click', '.infinity23-notes-wrap .infinity23-notes-add-btn', function(e) {
		e.preventDefault();

		var $wrap = $(this).closest('.infinity23-notes-wrap');

		if( !$(this).hasClass('infinity23-notes-opened') ) {

			$(this).text('+ Add Another Note');
			$(this).addClass('infinity23-notes-opened');
			$wrap.find('.infinity23-note-item').removeClass('infinity23-note-not-active');
			$wrap.find('.infinity23-note-item textarea').first().focus();
			infinity23_notes_auto_resize_textarea( $wrap.find('.infinity23-note-item textarea').first() );

		} else {

			var $lastItem = $wrap.find('.infinity23-note-item').last();
			var $newItem  = $lastItem.clone();
			$newItem.find('textarea').val('');
			$newItem.find('.infinity23-note-row-id-input').val('0');
			$wrap.find('.infinity23-notes-items').append($newItem);
			infinity23_notes_auto_resize_textarea( $newItem.find('textarea') );

		} // if( !$(this).hasClass('infinity23-notes-opened') ) {

		infinity23_notes_update_remove_icon_visibility( $wrap );

	}); // $(document).on('click', '.infinity23-notes-add-btn'

	// --------------------------------------
	// remove note row
	// --------------------------------------

	$(document).on('click', '.infinity23-notes-wrap .infinity23-note-remove', function(e) {
		e.preventDefault();

		var $wrap = $(this).closest('.infinity23-notes-wrap');
		var $item = $(this).closest('.infinity23-note-item');

		// only ask for confirmation when the note actually has content
		if( $.trim( $item.find('textarea').val() ) !== '' ) {
			if( !confirm('Are you sure you want to permanently remove the page note?') ) { return; }
		} // if( $.trim(

		var $items = $wrap.find('.infinity23-note-item');

		if( $items.length > 1 ) {

			$item.remove();

		} else {

			if( $wrap.find('.infinity23-notes-add-btn').length ) {
				$item.addClass('infinity23-note-not-active');
			} // if( $wrap.find('.infinity23-notes-add-btn').length ) {

			$item.find('textarea').val('');
			$wrap.find('.infinity23-notes-add-btn').text('+ Add Note');
			$wrap.find('.infinity23-notes-add-btn').removeClass('infinity23-notes-opened');

		} // if( $items.length > 1 ) {

		infinity23_notes_update_remove_icon_visibility( $wrap );

	}); // $(document).on('click', '.infinity23-note-remove'

	// --------------------------------------
	// toggle text direction
	// --------------------------------------

	$(document).on('click', '.infinity23-notes-wrap .infinity23-note-direction', function(e) {
		e.preventDefault();

		var $item      = $(this).closest('.infinity23-note-item');
		var $textarea  = $item.find('textarea');
		var $input     = $item.find('.infinity23-note-direction-input');

		if( $textarea.hasClass('direction-rtl') ) {
			$textarea.removeClass('direction-rtl').addClass('direction-ltr');
			$input.val('direction-ltr');
		} else {
			$textarea.removeClass('direction-ltr').addClass('direction-rtl');
			$input.val('direction-rtl');
		} // if( $textarea.hasClass('direction-rtl') ) {

	}); // $(document).on('click', '.infinity23-note-direction'

	// --------------------------------------
	// add separator line
	// --------------------------------------

	$(document).on('click', '.infinity23-notes-wrap .infinity23-note-separator', function(e) {
		e.preventDefault();

		var $textarea = $(this).closest('.infinity23-note-item').find('textarea');
		var el        = $textarea[0];
		var separator = "\n--------------------------\n";
		var startPos  = el.selectionStart;
		var endPos    = el.selectionEnd;
		var scrollTop = el.scrollTop;

		// if the user never clicked inside the textarea the caret is still at
		// position 0, so insert at the end of the existing text instead
		if( document.activeElement !== el ) {
			startPos = el.value.length;
			endPos   = el.value.length;
		} // if( document.activeElement !== el ) {

		el.value = el.value.substring(0, startPos) + separator + el.value.substring(endPos, el.value.length);
		el.focus();
		el.selectionStart = startPos + separator.length;
		el.selectionEnd   = startPos + separator.length;
		el.scrollTop      = scrollTop;

	}); // $(document).on('click', '.infinity23-note-separator'

	// --------------------------------------
	// ajax save for dashboard
	// --------------------------------------

	$(document).on('click', '.infinity23-notes-wrap .infinity23-notes-save-btn', function(e) {
		e.preventDefault();

		var $wrap = $(this).closest('.infinity23-notes-wrap');
		var $btn = $(this);

		if( $btn.hasClass('infinity23-saving') ) { return; }

		$btn.addClass('infinity23-saving');

		var notesData      = [];
		var notesDirections = [];
		var notesRowIds     = [];

		$wrap.find('.infinity23-note-item').each(function() {
			notesData.push( $(this).find('textarea').val() );
			notesDirections.push( $(this).find('.infinity23-note-direction-input').val() );
			notesRowIds.push( $(this).find('.infinity23-note-row-id-input').val() );
		}); // $wrap.find('.infinity23-note-item').each

		$.post(
			ajaxurl,
			{
				action:                    'infinity23_notes_save',
				infinity23_notes_box_nonce:   $wrap.find('[name="infinity23_notes_box_nonce"]').val(),
				infinity23_notes_page_id: $wrap.find('.infinity23-notes-page-id').val(),
				infinity23_notes_data:    notesData,
				infinity23_notes_directions: notesDirections,
				infinity23_notes_row_ids: notesRowIds,
			},
			function( response ) {

				$btn.removeClass('infinity23-saving');

				var $msg = $wrap.find('.infinity23-notes-ajax-msg');

				if( response.success ) {

					$msg.text('Notes saved').show().delay(2000).fadeOut();

					var $cell = $wrap.closest('.notes-col-preview');

					if( $cell.length ) {

						var $viewWrap = $cell.find('.infinity23-notes-all-view-wrap');
						$viewWrap.empty();

						$wrap.find('.infinity23-note-item').not('.infinity23-note-not-active').each(function() {

							var noteText = $(this).find('textarea').val();

							if( $.trim(noteText) !== '' ) {
								$('<div class="infinity23-notes-all-view-text"></div>').text(noteText).appendTo($viewWrap);
							} // if( $.trim(noteText) !== '' ) {

						}); // .each

						$cell.find('.infinity23-notes-all-edit-wrap').addClass('infinity23-position-hidden');
						$viewWrap.removeClass('infinity23-position-hidden');

					} // if( $cell.length ) {

				} else {
					$msg.text('Error saving notes.').show().delay(2000).fadeOut();
				} // if( response.success ) {

			}
		); // $.post

	}); // $(document).on('click', '.infinity23-notes-save-btn'

});

// ===========================================
// Folders Organizer - shared sidebar
// ===========================================

jQuery(document).ready(function($) {

	// the folders config is only printed on the screens where the folders sidebar renders
	if( typeof infinity23FoConfig === 'undefined' ) { return; }

	// --------------------------------------
	// collect the checked ids from the native list table
	// --------------------------------------

	function infinity23FoGetCheckedIds() {

		var postIds = [];

		$('.wp-list-table tbody [type="checkbox"]').each(function() {
			if( $(this).prop('checked') ) {
				postIds.push( $(this).val() );
			} // if( $(this).prop('checked') ) {
		}); // .each

		return postIds;

	} // function infinity23FoGetCheckedIds() {

	// --------------------------------------
	// carry the open folder into the "Add New" link, so a new
	// upload / page / post lands in the folder being viewed
	// --------------------------------------

	var infinity23FoCurrentFolderParam = String( infinity23FoConfig.currentFolder );

	if( /^[0-9]+$/.test( infinity23FoCurrentFolderParam ) ) {

		$('.wrap .page-title-action').each(function() {
			var $link = $(this);
			var href = $link.attr('href');
			if( href ) {
				$link.attr('href', href + ( href.indexOf('?') !== -1 ? '&' : '?' ) + 'infinity23_folder=' + infinity23FoCurrentFolderParam + '&infinity23_fo_nonce=' + infinity23FoConfig.addNewNonce);
			} // if( href ) {
		}); // .each

	} // if( /^[0-9]+$/.test( infinity23FoCurrentFolderParam ) ) {

	// --------------------------------------
	// submit new folder on Enter key
	// --------------------------------------

	$('#infinity23-fo-new-folder-name').on('keydown', function(e) {

		if( e.key === 'Enter' || e.keyCode === 13 ) {
			e.preventDefault();
			$('#infinity23-fo-new-folder-btn').trigger('click');
		} // if( e.key === 'Enter'

	}); // $('#infinity23-fo-new-folder-name').on('keydown'

	// --------------------------------------
	// update the folders count in the Add New Folder title
	// --------------------------------------

	function infinity23FoUpdateFolderCount() {

		var $count = $('.infinity23-fo-folder-count');

		if( !$count.length ) { return; }

		$count.text( $('.infinity23-fo-media-folder-list .infinity23-fo-media-row-wrap').not('.infinity23-fo-media-row-template').length );

	} // function infinity23FoUpdateFolderCount() {

	// --------------------------------------
	// add new folder via ajax
	// --------------------------------------

	$('#infinity23-fo-new-folder-btn').on('click', function() {

		var name = $.trim( $('#infinity23-fo-new-folder-name').val() );

		if( name === '' ) { return; }

		$('#infinity23-fo-add-folder-status').html('<span class="infinity23-fo-add-folder-spinner"></span>Adding folder...');

		$.post( infinity23FoConfig.ajaxUrl, {
			action: 'infinity23_fo_add_folder',
			nonce: infinity23FoConfig.nonce,
			content_type: infinity23FoConfig.contentType,
			name: name
		}, function( response ) {

			if( response.success ) {

				$('#infinity23-fo-add-folder-status').text('');
				$('#infinity23-fo-new-folder-name').val('');
				$('.infinity23-fo-media-folder-empty').remove();

				var $newRow = $('.infinity23-fo-media-row-template').clone();
				$newRow.removeClass('infinity23-fo-media-row-template').show();
				$newRow.attr('data-folder-id', response.data.folder_id);
				$newRow.find('.infinity23-fo-media-row').attr('href', infinity23FoConfig.baseUrl + '&infinity23_folder=' + response.data.folder_id);
				$newRow.find('.infinity23-fo-media-row-label').text(response.data.folder_name);
				$newRow.find('.infinity23-fo-media-row-count').text('0');
				$newRow.find('.infinity23-fo-rename-input').val(response.data.folder_name);

				$('.infinity23-fo-media-folder-list').prepend($newRow);

				var $newOption = $('<option></option>').attr('value', response.data.folder_id).text(response.data.folder_name);
				$('#infinity23-fo-move-select').append($newOption);

				infinity23FoUpdateFolderCount();

			} else {
				$('#infinity23-fo-add-folder-status').text('');
				alert( response.data.message );
			} // if( response.success ) {

		}); // $.post

	}); // $('#infinity23-fo-new-folder-btn').on('click'

	// --------------------------------------
	// move checked items to a folder via ajax
	// --------------------------------------

	$('#infinity23-fo-move-btn').on('click', function() {

		var folderId = $('#infinity23-fo-move-select').val();
		var postIds = infinity23FoGetCheckedIds();

		if( !folderId || postIds.length === 0 ) {
			alert('Please choose a folder and check at least one item.');
			return;
		} // if( !folderId || postIds.length === 0 ) {

		$('#infinity23-fo-move-status').html('<span class="infinity23-fo-add-folder-spinner"></span>Moving...');

		$.post( infinity23FoConfig.ajaxUrl, {
			action: 'infinity23_fo_move_images',
			nonce: infinity23FoConfig.nonce,
			content_type: infinity23FoConfig.contentType,
			folder_id: folderId,
			post_ids: postIds
		}, function( response ) {

			if( response.success ) {
				location.reload();
			} else {
				$('#infinity23-fo-move-status').text('');
				alert( response.data.message );
			} // if( response.success ) {

		}); // $.post

	}); // $('#infinity23-fo-move-btn').on('click'

	// --------------------------------------
	// remove checked items from the folder currently being viewed
	// --------------------------------------

	$('#infinity23-fo-remove-btn').on('click', function() {

		var postIds = infinity23FoGetCheckedIds();

		if( postIds.length === 0 ) {
			alert('Please check at least one item.');
			return;
		} // if( postIds.length === 0 ) {

		$('#infinity23-fo-move-status').html('<span class="infinity23-fo-add-folder-spinner"></span>Removing...');

		$.post( infinity23FoConfig.ajaxUrl, {
			action: 'infinity23_fo_remove_images',
			nonce: infinity23FoConfig.nonce,
			content_type: infinity23FoConfig.contentType,
			current_folder: infinity23FoCurrentFolderParam,
			post_ids: postIds
		}, function( response ) {

			if( response.success ) {
				location.reload();
			} else {
				$('#infinity23-fo-move-status').text('');
				alert( response.data.message );
			} // if( response.success ) {

		}); // $.post

	}); // $('#infinity23-fo-remove-btn').on('click'

	// --------------------------------------
	// open rename mode for a folder row
	// --------------------------------------

	$(document).on('click', '.infinity23-fo-media-row-edit-btn', function(e) {

		e.preventDefault();
		var $wrap = $(this).closest('.infinity23-fo-media-row-wrap');
		$wrap.addClass('infinity23-fo-media-row-wrap-editing');
		$wrap.find('.infinity23-fo-rename-input').trigger('focus').trigger('select');

	}); // $(document).on('click', '.infinity23-fo-media-row-edit-btn'

	// --------------------------------------
	// cancel rename mode
	// --------------------------------------

	$(document).on('click', '.infinity23-fo-rename-cancel-btn', function(e) {

		e.preventDefault();
		var $wrap = $(this).closest('.infinity23-fo-media-row-wrap');
		var originalName = $wrap.find('.infinity23-fo-media-row-label').text();
		$wrap.find('.infinity23-fo-rename-input').val(originalName);
		$wrap.find('.infinity23-fo-rename-status').text('');
		$wrap.removeClass('infinity23-fo-media-row-wrap-editing');

	}); // $(document).on('click', '.infinity23-fo-rename-cancel-btn'

	// --------------------------------------
	// submit rename on Enter key
	// --------------------------------------

	$(document).on('keydown', '.infinity23-fo-rename-input', function(e) {

		if( e.key === 'Enter' || e.keyCode === 13 ) {
			e.preventDefault();
			$(this).closest('.infinity23-fo-rename-form').find('.infinity23-fo-rename-save-btn').trigger('click');
		} // if( e.key === 'Enter'

	}); // $(document).on('keydown', '.infinity23-fo-rename-input'

	// --------------------------------------
	// save rename via ajax
	// --------------------------------------

	$(document).on('click', '.infinity23-fo-rename-save-btn', function(e) {

		e.preventDefault();

		var $wrap = $(this).closest('.infinity23-fo-media-row-wrap');
		var folderId = $wrap.data('folder-id');
		var newName = $.trim( $wrap.find('.infinity23-fo-rename-input').val() );
		var $status = $wrap.find('.infinity23-fo-rename-status');

		if( newName === '' ) {
			alert('Please enter a folder name');
			return;
		} // if( newName === '' ) {

		$status.html('<span class="infinity23-fo-add-folder-spinner"></span>Saving...');

		$.post( infinity23FoConfig.ajaxUrl, {
			action: 'infinity23_fo_rename_folder',
			nonce: infinity23FoConfig.nonce,
			content_type: infinity23FoConfig.contentType,
			folder_id: folderId,
			name: newName
		}, function( response ) {

			if( response.success ) {

				$wrap.find('.infinity23-fo-media-row-label').text(response.data.folder_name);
				$wrap.find('.infinity23-fo-rename-input').val(response.data.folder_name);
				$wrap.removeClass('infinity23-fo-media-row-wrap-editing');
				$status.text('');

				$('#infinity23-fo-move-select option[value="' + folderId + '"]').text(response.data.folder_name);

			} else {
				$status.text('');
				alert( response.data.message );
			} // if( response.success ) {

		}); // $.post

	}); // $(document).on('click', '.infinity23-fo-rename-save-btn'

	// --------------------------------------
	// delete folder via ajax
	// --------------------------------------

	$(document).on('click', '.infinity23-fo-rename-delete-btn', function(e) {

		e.preventDefault();

		var $wrap = $(this).closest('.infinity23-fo-media-row-wrap');
		var folderId = $wrap.data('folder-id');
		var folderName = $wrap.find('.infinity23-fo-media-row-label').text();
		var $status = $wrap.find('.infinity23-fo-rename-status');

		if( !confirm('Are you sure you want to delete the folder "' + folderName + '"? The items inside it will not be deleted, only removed from this folder.') ) {
			return;
		} // if( !confirm(

		$status.html('<span class="infinity23-fo-add-folder-spinner"></span>Deleting...');

		$.post( infinity23FoConfig.ajaxUrl, {
			action: 'infinity23_fo_delete_folder',
			nonce: infinity23FoConfig.nonce,
			content_type: infinity23FoConfig.contentType,
			folder_id: folderId
		}, function( response ) {

			if( response.success ) {

				if( infinity23FoCurrentFolderParam === String(folderId) ) {
					location.reload();
					return;
				} // if( infinity23FoCurrentFolderParam === String(folderId) ) {

				$wrap.remove();
				$('#infinity23-fo-move-select option[value="' + folderId + '"]').remove();

				infinity23FoUpdateFolderCount();

				if( $('.infinity23-fo-media-folder-list .infinity23-fo-media-row-wrap').not('.infinity23-fo-media-row-template').length === 0 ) {
					$('.infinity23-fo-media-folder-list').append('<p class="infinity23-fo-media-folder-empty">No folders yet.</p>');
				} // if( $('.infinity23-fo-media-folder-list'

			} else {
				$status.text('');
				alert( response.data.message );
			} // if( response.success ) {

		}); // $.post

	}); // $(document).on('click', '.infinity23-fo-rename-delete-btn'

});
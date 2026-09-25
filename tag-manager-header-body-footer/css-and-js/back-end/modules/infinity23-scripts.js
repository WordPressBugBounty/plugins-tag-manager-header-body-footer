jQuery(document).ready(function($) {

    // ===========================================================================================================================================
    // Exclude IDs - row manager with AJAX title lookup
    // ===========================================================================================================================================

    // --------------------------------------
    // load page title by id via ajax
    // --------------------------------------

    function infinity23_load_exclude_title( $input ) {

        var $wrap = $input.closest('.infinity23-exclude-ids-wrap');
        var $row = $input.closest('.infinity23-exclude-id-row');
        var $title = $row.find('.infinity23-exclude-id-title');
        var val = $.trim( $input.val() );

        if( val === '' || isNaN(val) ) {
            $title.text('').removeClass('title-error');
            infinity23_collect_exclude_ids( $wrap );
            return;
        } // if( val === '' || isNaN(val) ) {

        $title.html('<span class="infinity23-exclude-id-spinner"></span>').removeClass('title-error');

        $.post( infinity23_ajax_url, {
            action: 'infinity23_get_page_title',
            nonce:   infinity23_get_title_nonce,
            post_id: val
        }, function( response ) {

            if( response.success ) {
                $title.text( response.data.title ).removeClass('title-error');
            } else {
                $title.text('Page not found').addClass('title-error');
            } // if( response.success ) {

            infinity23_collect_exclude_ids( $wrap );

        }); // $.post

    } // function infinity23_load_exclude_title( $input ) {

    // --------------------------------------
    // collect all ids and write to hidden input
    // --------------------------------------

    function infinity23_collect_exclude_ids( $wrap ) {

        var ids = [];
        var inputId = $wrap.data('input');

        $wrap.find('.infinity23-exclude-id-input').each(function() {
            var val = $.trim( $(this).val() );
            if( val !== '' && !isNaN(val) && parseInt(val) > 0 ) {
                ids.push( parseInt(val) );
            } // if( val !== '' ...
        }); // .each

        $('#' + inputId).val( ids.join(', ') );

    } // function infinity23_collect_exclude_ids( $wrap ) {

    // --------------------------------------
    // update title when user types in an id input
    // --------------------------------------

    $(document).on('change keyup', '.infinity23-exclude-id-input', function() {
        infinity23_collect_exclude_ids( $(this).closest('.infinity23-exclude-ids-wrap') );
    }); // $(document).on('change keyup'

    $(document).on('change', '.infinity23-exclude-id-input', function() {
        infinity23_load_exclude_title( $(this) );
    }); // $(document).on('change'

    // --------------------------------------
    // add new row
    // --------------------------------------

    $(document).on('click', '.infinity23-exclude-id-add', function() {

        var $wrap = $('#' + $(this).data('target'));
        var $lastRow = $wrap.find('.infinity23-exclude-id-row').last();
        var $newRow  = $lastRow.clone();
        $newRow.find('.infinity23-exclude-id-input').val('');
        $newRow.find('.infinity23-exclude-id-title').text('').removeClass('title-error');
        $wrap.append($newRow);

        infinity23_collect_exclude_ids( $wrap );

    }); // $(document).on('click', '.infinity23-exclude-id-add'

    // --------------------------------------
    // remove row
    // --------------------------------------

    $(document).on('click', '.infinity23-exclude-id-remove', function() {

        var $wrap = $(this).closest('.infinity23-exclude-ids-wrap');

        if( $wrap.find('.infinity23-exclude-id-row').length > 1 ) {
            $(this).closest('.infinity23-exclude-id-row').remove();
        } else {
            $(this).closest('.infinity23-exclude-id-row').find('.infinity23-exclude-id-input').val('');
            $(this).closest('.infinity23-exclude-id-row').find('.infinity23-exclude-id-title').text('').removeClass('title-error');
        } // if( $wrap.find

        infinity23_collect_exclude_ids( $wrap );

    }); // $(document).on('click', '.infinity23-exclude-id-remove'

    // ===========================================================================================================================================
    // Include Exclude - Select / Deselect All
    // ===========================================================================================================================================

    // --------------------------------------
    // handle button click
    // --------------------------------------

    $(document).on('click', '.include-exclude-warp .ie-btn-select', function(e) {

        e.stopPropagation();
        var group  = $(this).closest('.ie-select-all-btn').data('group');
        var $items = $('.include-exclude-warp .ie-toggle-item[data-group="' + group + '"]');

        $items.each(function() {

            var $item   = $(this);
            var $circle = $item.find('.ie-toggle-circle');
            var inputId = $item.data('input');
            var $hidden = $('#' + inputId);

            $circle.addClass('active');
            $item.addClass('ie-toggle-active');
            $hidden.val('1');
            $hidden.trigger('change');

        }); // $items.each

    }); // $(document).on('click', '.ie-btn-select'

    $(document).on('click', '.include-exclude-warp .ie-btn-deselect', function(e) {

        e.stopPropagation();
        var group  = $(this).closest('.ie-select-all-btn').data('group');
        var $items = $('.include-exclude-warp .ie-toggle-item[data-group="' + group + '"]');

        $items.each(function() {

            var $item   = $(this);
            var $circle = $item.find('.ie-toggle-circle');
            var inputId = $item.data('input');
            var $hidden = $('#' + inputId);

            $circle.removeClass('active');
            $item.removeClass('ie-toggle-active');
            $hidden.val('0');
            $hidden.trigger('change');

        }); // $items.each

    }); // $(document).on('click', '.ie-btn-deselect'

    // ===========================================================================================================================================
    // Include Exclude toggle
    // ===========================================================================================================================================

    // --------------------------------------
    // mark which modules are saved-active on page load, so the Settings
    // link only shows for modules that are actually saved and active -
    // never for a toggle the user just clicked but has not saved yet
    // --------------------------------

    $('.include-exclude-warp .ie-toggle-item.ie-toggle-active').addClass('ie-toggle-saved-on');

    // --------------------------------------
    // toggle on click
    // --------------------------------------

    $('.include-exclude-warp .ie-toggle-item').on('click', function(e) {

        if( $(e.target).closest('.ie-module-settings-link').length ) {
            return;
        } // if( $(e.target).closest(

        var $item   = $(this);
        var $circle = $item.find('.ie-toggle-circle');
        var inputId = $item.data('input');
        var $hidden = $('#' + inputId);
        var group   = $item.data('group');
        var $settingsLink = $item.find('.ie-module-settings-link');

        if( $circle.hasClass('active') ) {
            $circle.removeClass('active');
            $item.removeClass('ie-toggle-active');
            $hidden.val('0');
            $settingsLink.hide();
        } else {
            $circle.addClass('active');
            $item.addClass('ie-toggle-active');
            $hidden.val('1');
            if( $item.hasClass('ie-toggle-saved-on') ) {
                $settingsLink.show();
            } // if( $item.hasClass('ie-toggle-saved-on') ) {
        } // if( $circle.hasClass('active') ) {

        $hidden.trigger('change');

    }); // $('.include-exclude-warp .ie-toggle-item').on('click'

    // ===========================================================================================================================================
    // Whatsapp Button - button type preview
    // ===========================================================================================================================================

    // --------------------------------------
    // update preview image on select change
    // --------------------------------------

    function infinity23_whatsapp_update_type_preview() {

        var val = $('#whatsapp_button_type').val();
        $('.whatsapp-btn-type-preview img').removeClass('active-preview');
        $('.whatsapp-btn-type-preview img[data-type="' + val + '"]').addClass('active-preview');

        if( val === 'custom' ) {
            $('.whatsapp-btn-type-preview .infinity23-upload-preview').addClass('active-preview');
        } // if( val === 'custom' ) {

    } // function infinity23_whatsapp_update_type_preview() {

    $('#whatsapp_button_type').on('change', function() {
        infinity23_whatsapp_update_type_preview();
    }); // $('#whatsapp_button_type').on('change'

    $(document).on('click', '.whatsapp-btn-type-preview img', function() {
        var type = $(this).data('type');
        $('#whatsapp_button_type').val( type );
        infinity23_whatsapp_update_type_preview();
    }); // $(document).on('click', '.whatsapp-btn-type-preview img'

    $(document).on('change', '#custom_icon_image_url', function() {
        $('#whatsapp_button_type').val('custom');
        infinity23_whatsapp_update_type_preview();
    }); // $(document).on('change', '#custom_icon_image_url'

    infinity23_whatsapp_update_type_preview();

    // ===========================================================================================================================================
    // Reset Settings confirm alert
    // ===========================================================================================================================================

    $(document).on('click', '.infinity23-reset-btn', function() {

        var $btn = $(this);
        var confirmMsg = $btn.data('confirm') || 'Are you sure you want to reset all the settings? This action cannot be undone.';
        if( !confirm( confirmMsg ) ) { return; }
        infinity23_form_changed = false;
        $btn.closest('form').submit();

    }); // $(document).on('click', '.infinity23-reset-btn'

    // ===========================================================================================================================================
    // Toggle Switch
    // ===========================================================================================================================================

    $('.infinity23-active-code .toggle-item').on('click', function() {

        var $item = $(this);
        var $circle = $item.find('.toggle-circle');
        var $inputId = $item.data('input');
        var $hidden = $('#' + $inputId);
        var $form = $item.closest('.infinity23-activate-form');

        if( $circle.hasClass('active') ) {

            $circle.removeClass('active');
            $item.removeClass('toggle-active');
            $hidden.val('0');

        } else {

            $circle.addClass('active');
            $item.addClass('toggle-active');
            $hidden.val('1');

        } // if( $circle.hasClass('active') ) {

        infinity23_form_changed = false;
        $form.submit();

    }); // $('.infinity23-toggle-item').on('click'

    // ===========================================================================================================================================
    // Section Card Toggle
    // ===========================================================================================================================================

    // wrap card content in card-body and apply collapsed state
    $('.infinity23-warp .section-card, .infinity23-warp .additional-data-warp, .infinity23-danger-zone-warp').each(function() {
        var $card = $(this);
        var $h2 = $card.find('h2').first();
        var defaultState = $card.data('card-default');
        var $headerWrap = $h2.closest('.ie-card-header');
        if( $headerWrap.length ) {
            $headerWrap.nextAll().wrapAll('<div class="card-body"></div>');
        } else {
            $h2.nextAll().wrapAll('<div class="card-body"></div>');
        } // if( $headerWrap.length ) {
        if( defaultState === 'closed' ) {
            $card.find('.card-body').addClass('collapsed');
            $card.addClass('card-collapsed');
        }
    });

    // toggle on arrow click
    $(document).on('click', '.infinity23-warp .section-card h2, .infinity23-warp .additional-data-warp h2, .infinity23-danger-zone-warp h2', function(e) {
        e.stopPropagation();
        var $card = $(this).closest('.section-card, .additional-data-warp, .infinity23-danger-zone-warp');
        var $body = $card.find('.card-body');
        $body.toggleClass('collapsed');
        $card.toggleClass('card-collapsed');
    }); // $(document).on('click', '.section-card h2'

    // toggle collapsed card on click anywhere inside it
    $(document).on('click', '.infinity23-warp .section-card.card-collapsed, .infinity23-warp .additional-data-warp.card-collapsed, .infinity23-danger-zone-warp.card-collapsed', function(e) {
        var $card = $(this);
        var $body = $card.find('.card-body');
        $body.toggleClass('collapsed');
        $card.toggleClass('card-collapsed');
    }); // $(document).on('click', '.section-card.card-collapsed'

    // ===========================================================================================================================================
    // Global Media Uploader
    // ===========================================================================================================================================

    // open wp media uploader and set the selected image url to the input and preview img
    // usage: add class infinity23-upload-btn to any button
    // the button must be inside a wrap div that also contains:
    //   - an <input> with class infinity23-upload-input  (stores the url)
    //   - an <img>   with class infinity23-upload-preview (shows the preview)

    $(document).on('click', '.infinity23-upload-btn, .infinity23-upload-preview', function(e) {
        e.preventDefault();

        var $wrap = $(this).closest('.infinity23-upload-wrap');
        var $input = $wrap.find('.infinity23-upload-input');
        var $preview = $wrap.find('.infinity23-upload-preview');

        var uploader = wp.media({
            title: 'Select Image',
            button: { text: 'Use This Image' },
            multiple: false
        }).open().on('select', function() {
            var image = uploader.state().get('selection').first().toJSON();
            $input.val( image.url ).trigger('change');
            $preview.attr( 'src', image.url ).show();
            infinity23_form_changed = true;
        }); // .on('select'

    }); // $(document).on('click', '.infinity23-upload-btn, .infinity23-upload-preview'

    // update preview image when url input is changed manually
    $(document).on('change keyup', '.infinity23-upload-wrap .infinity23-upload-input', function() {
        var $wrap = $(this).closest('.infinity23-upload-wrap');
        var $preview = $wrap.find('.infinity23-upload-preview');
        var $inputVal = $(this).val();
        if( $inputVal ) {
            $preview.attr( 'src', $inputVal ).show();
        } else {
            $preview.attr( 'src', '' ).hide();
        } // if( $inputVal ) {
    }); // $(document).on('change keyup', '.infinity23-upload-wrap .infinity23-upload-input'

    // ===========================================================================================================================================
    // Unsaved changes detection + floating save button
    // ===========================================================================================================================================

    var infinity23_form_changed = false;
    var infinity23_floating_btn_closed = false;

    var $infinity23_main_warp = $('.infinity23-warp').first();

    if( $infinity23_main_warp.length && !$infinity23_main_warp.hasClass('no-floating-save') ) {
        $infinity23_main_warp.append('<div id="infinity23-floating-save-btn-wrap"><div id="infinity23-floating-save-inner"><button id="infinity23-floating-save-close">x</button><button id="infinity23-floating-save-btn">Save Changes</button></div></div>');
        infinity23_position_floating_btn();
    } // if( $infinity23_main_warp.length && !$infinity23_main_warp.hasClass('no-floating-save') ) {

    function infinity23_position_floating_btn() {
        if( !$infinity23_main_warp.length ) { return; }
        var warpRight = $(window).width() - ( $infinity23_main_warp.offset().left + $infinity23_main_warp.outerWidth() );
        $('#infinity23-floating-save-btn-wrap').css('right', warpRight + 30 + 'px');
    } // function infinity23_position_floating_btn() {

    if( $infinity23_main_warp.length && !$infinity23_main_warp.hasClass('no-floating-save') ) {
        $(window).on('resize', function() {
            infinity23_position_floating_btn();
        }); // $(window).on('resize'
    } // if( $infinity23_main_warp.length && !$infinity23_main_warp.hasClass('no-floating-save') ) {

    // mark form as changed on any input change
    $(document).on('change input', '.infinity23-warp .infinity23-form input, .infinity23-warp .infinity23-form select, .infinity23-warp .infinity23-form textarea', function() {
        infinity23_form_changed = true;
        if( !infinity23_floating_btn_closed ) {
            $('#infinity23-floating-save-btn-wrap').addClass('visible');
        } // if( !infinity23_floating_btn_closed ) {
    });

    // mark form as changed on color picker change
    $(document).on('infinity23-color-changed', '.infinity23-warp .infinity23-form .infinity23-color-picker', function() {
        infinity23_form_changed = true;
        if( !infinity23_floating_btn_closed ) {
            $('#infinity23-floating-save-btn-wrap').addClass('visible');
        } // if( !infinity23_floating_btn_closed ) {
    });

    // reset flag on form submit
    $(document).on('submit', '.infinity23-warp .infinity23-form', function() {

        $('.infinity23-exclude-ids-wrap').each(function() {
            infinity23_collect_exclude_ids( $(this) );
        }); // $('.infinity23-exclude-ids-wrap').each

        infinity23_form_changed = false;
        $('#infinity23-floating-save-btn-wrap').removeClass('visible');
    });

    // floating save button click - submit the nearest form
    $(document).on('click', '#infinity23-floating-save-close', function() {
        infinity23_floating_btn_closed = true;
        $('#infinity23-floating-save-btn-wrap').removeClass('visible');
    }); // $(document).on('click', '#infinity23-floating-save-close'

    $(document).on('click', '#infinity23-floating-save-btn-wrap #infinity23-floating-save-btn', function() {
        $('.infinity23-warp .infinity23-form').first().submit();
    }); // $(document).on('click', '#infinity23-floating-save-btn'

    // force Enter key to submit the main form
    $(document).on('keydown', '.infinity23-warp .infinity23-form input', function(e) {
        if( e.key === 'Enter' || e.keyCode === 13 ) {
            e.preventDefault();
            $(this).closest('.infinity23-form').find('.submit-btn').trigger('click');
        } // if( e.key === 'Enter'
    }); // $(document).on('keydown', '.infinity23-form input'

    // warn before leaving if changes exist
    $(window).on('beforeunload', function(e) {
        if( infinity23_form_changed ) {
            e.preventDefault();
            e.originalEvent.returnValue = '';
            return '';
        }
        if( $('.notes-warp .infinity23-notes-all-edit-wrap').not('.infinity23-position-hidden').length ) {
            e.preventDefault();
            e.originalEvent.returnValue = '';
            return '';
        }
    });

    // ===========================================================================================================================================
    // Adding color picker to every element 
    // with the class of infinity23-color-picker
    // ===========================================================================================================================================

    $('.infinity23-warp .infinity23-form .infinity23-color-picker').wpColorPicker({
        change: function( event, ui ) {
            infinity23_form_changed = true;
            $(this).val( ui.color.toString() ).trigger('infinity23-color-changed');
        },
        clear: function() {
            infinity23_form_changed = true;
            $(this).trigger('infinity23-color-changed');
        }
    });

    // ===========================================================================================================================================
    // Form Tooltips Helper
    // ===========================================================================================================================================

    // open tooltip on click
    $(document).on('click', '.infinity23-warp .backtest-field-help-icon', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $icon = $(this);
        var $content = $icon.find('.data-help-content');

        if( !$content.length ) { return; }

        var isActive = $content.hasClass('active');
        $('.infinity23-warp .data-help-content').removeClass('active');

        if( !isActive ) {
            $content.addClass('active');
            if( !$content.find('.backtest-field-help-close').length ) {
                $content.prepend('<button class="backtest-field-help-close">x</button>');
            }
        }
    });

    // prevent clicks inside tooltip from closing it
    $(document).on('click', '.infinity23-warp .data-help-content', function(e) {
        e.stopPropagation();
    });

    // close button
    $(document).on('click', '.infinity23-warp .backtest-field-help-close', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.data-help-content').removeClass('active');
    });

    // close on outside click
    $(document).on('click', function(e) {
        if( !$(e.target).closest('.infinity23-warp .backtest-field-help-icon').length ) {
            $('.infinity23-warp .data-help-content').removeClass('active');
        }
    });

    // close on Escape key
    $(document).on('keydown', function(e) {
        if( e.key === 'Escape' || e.keyCode === 27 ) {
            $('.infinity23-warp .data-help-content').removeClass('active');
        }
    });

    // ===========================================================================================================================================
    // Additional Data - Code Example Toggle
    // ===========================================================================================================================================

    $(document).on('click', '.infinity23-code-example-btn', function(e) {

        e.preventDefault();

        var $btn = $(this);
        var $block = $btn.next('.infinity23-code-example-block');

        if( !$block.length ) {
            $block = $btn.closest('li').find('.infinity23-code-example-block');
        } // if( !$block.length ) {

        if( $block.hasClass('active') ) {
            $block.removeClass('active');
            $btn.text('Code Example');
        } else {
            $block.addClass('active');
            $btn.text('Hide Code Example');
        }

    }); // $(document).on('click', '.infinity23-code-example-btn'

    // ===========================================================================================================================================
    // Checkbox Toggle Fields
    // ===========================================================================================================================================

    // show or hide a group of fields based on a checkbox
    // usage: wrap the fields in a div with class infinity23-toggle-fields
    // and add a data-toggle-input attribute holding the checkbox id

    // --------------------------------------
    // apply the state to a single wrap
    // --------------------------------------

    function infinity23_apply_toggle_fields( $wrap ) {

        var inputId = $wrap.data('toggle-input');
        var $checkbox = $('#' + inputId);

        if( !$checkbox.length ) { return; }

        if( $checkbox.is(':checked') ) {
            $wrap.show();
        } else {
            $wrap.hide();
        } // if( $checkbox.is(':checked') ) {

    } // function infinity23_apply_toggle_fields( $wrap ) {

    // --------------------------------------
    // set the state on page load
    // --------------------------------------

    $('.infinity23-warp .infinity23-toggle-fields').each(function() {
        infinity23_apply_toggle_fields( $(this) );
    }); // $('.infinity23-toggle-fields').each

    // --------------------------------------
    // update the state when the checkbox changes
    // --------------------------------------

    $(document).on('change', '.infinity23-warp input[type="checkbox"]', function() {

        var inputId = $(this).attr('id');

        $('.infinity23-warp .infinity23-toggle-fields[data-toggle-input="' + inputId + '"]').each(function() {
            infinity23_apply_toggle_fields( $(this) );
        }); // .each

    }); // $(document).on('change', 'input[type="checkbox"]'

    // ===========================================================================================================================================
    // Exclude IDs - hide rows section when Include/Exclude Option is Not Active
    // ===========================================================================================================================================

    // --------------------------------------
    // apply the state to a single select
    // --------------------------------------

    function infinity23_apply_exclude_option_toggle( $select ) {

        var targetId = $select.data('toggle-target');
        var $wrap = $('#' + targetId);

        if( $select.val() === 'none' ) {
            $wrap.addClass('infinity23-exclude-hidden');
        } else {
            $wrap.removeClass('infinity23-exclude-hidden');
        } // if( $select.val() === 'none' ) {

    } // function infinity23_apply_exclude_option_toggle( $select ) {

    // --------------------------------------
    // set the state on page load
    // --------------------------------------

    $('.infinity23-warp .infinity23-exclude-option-select').each(function() {
        infinity23_apply_exclude_option_toggle( $(this) );
    }); // $('.infinity23-exclude-option-select').each

    // --------------------------------------
    // update the state when the select changes
    // --------------------------------------

    $(document).on('change', '.infinity23-warp .infinity23-exclude-option-select', function() {
        infinity23_apply_exclude_option_toggle( $(this) );
    }); // $(document).on('change', '.infinity23-exclude-option-select'

    // ===========================================================================================================================================
    // Phone Number Fields - alert on invalid characters
    // ===========================================================================================================================================

    // --------------------------------------
    // check the field value on blur
    // --------------------------------------

    $(document).on('blur', '.infinity23-phone-number-field', function() {

        var $field = $(this);
        var val = $.trim( $field.val() );

        if( val === '' ) { return; }

        var allowPlus = $field.data('allow-plus') == 1;
        var hasPlus = allowPlus && (val.charAt(0) === '+');
        var testVal = hasPlus ? val.substring(1) : val;

        if( /[^0-9]/.test(testVal) || (!allowPlus && val.indexOf('+') !== -1) ) {

            var cleanVal = val.replace(/[^0-9]/g, '');
            if( hasPlus ) {
                cleanVal = '+' + cleanVal;
            } // if( hasPlus ) {

            $field.val(cleanVal);
            alert('Error: for the phone number to work you can only insert numbers, no spaces or other characters.');

        } // if( /[^0-9]/.test(testVal) ... {

    }); // $(document).on('blur', '.infinity23-phone-number-field'

    // ===========================================================================================================================================
    // Cron Management - confirm before running a row action
    // ===========================================================================================================================================

    $(document).on('click', '.cron-management-warp .cron-action-btn[data-confirm]', function(e) {

        var confirmMsg = $(this).data('confirm');

        if( !confirm( confirmMsg ) ) {
            e.preventDefault();
        } else {
            infinity23_form_changed = false;
        } // if( !confirm( confirmMsg ) ) {

    }); // $(document).on('click', '.cron-management-warp .cron-action-btn[data-confirm]'

    // ===========================================================================================================================================
    // Cron Management - generate random password button
    // ===========================================================================================================================================

    $(document).on('click', '.cron-management-warp .infinity23-generate-password-btn', function(e) {

        e.preventDefault();

        var $input = $('#' + $(this).data('target'));
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var newPassword = '';

        for( var i = 0; i < 32; i++ ) {
            newPassword += chars.charAt( Math.floor( Math.random() * chars.length ) );
        } // for( var i = 0; i < 32; i++ ) {

        $input.val( newPassword ).trigger('change');

    }); // $(document).on('click', '.infinity23-generate-password-btn'

    // ===========================================================================================================================================
    // Redirect 301/302 - confirm before removing a folder or a redirect row
    // ===========================================================================================================================================

    $(document).on('click', '.redirect-301-and-302-warp .infinity23-redirect-remove-btn[data-confirm]', function() {

        var confirmMsg = $(this).data('confirm');

        if( !confirm( confirmMsg ) ) { return; }

        infinity23_form_changed = false;
        $(this).closest('form').submit();

    }); // $(document).on('click', '.redirect-301-and-302-warp .infinity23-redirect-remove-btn[data-confirm]'

    $(document).on('click', '.redirect-301-and-302-warp .infinity23-redirect-action-btn[data-confirm]:not(.infinity23-redirect-remove-btn)', function(e) {

        var confirmMsg = $(this).data('confirm');

        if( !confirm( confirmMsg ) ) {
            e.preventDefault();
        } else {
            infinity23_form_changed = false;
        } // if( !confirm( confirmMsg ) ) {

    }); // $(document).on('click', '.redirect-301-and-302-warp .infinity23-redirect-action-btn[data-confirm]:not(.infinity23-redirect-remove-btn)'

    // --------------------------------------
    // show the advance fields and hide the button
    // --------------------------------------

    $(document).on('click', '.redirect-301-and-302-warp .infinity23-redirect-advance-btn', function() {

        var $btn = $(this);
        $btn.next('.infinity23-redirect-advance-fields-wrap').removeClass('infinity23-redirect-advance-hidden');
        $btn.hide();

    }); // $(document).on('click', '.redirect-301-and-302-warp .infinity23-redirect-advance-btn'

    // --------------------------------------
    // Redirect 301/302 - Add Redirect tabs (Single / Bulk)
    // --------------------------------------

    $(document).on('click', '.redirect-301-and-302-warp .infinity23-redirect-add-tab-btn', function() {

        var $btn = $(this);

        if( $btn.hasClass('infinity23-redirect-add-tab-disabled') ) {
            return;
        } // if( $btn.hasClass('infinity23-redirect-add-tab-disabled') ) {

        var target = $btn.data('tab-target');
        var $card = $btn.closest('.section-card');

        $card.find('.infinity23-redirect-add-tab-btn').removeClass('infinity23-redirect-add-tab-active');
        $btn.addClass('infinity23-redirect-add-tab-active');

        $card.find('.infinity23-redirect-add-tab-content').addClass('infinity23-redirect-tab-hidden');
        $card.find('#' + target).removeClass('infinity23-redirect-tab-hidden');

    }); // $(document).on('click', '.redirect-301-and-302-warp .infinity23-redirect-add-tab-btn'

    // ===========================================================================================================================================
    // Global Line Numbered Textarea System
    // Any textarea wrapped in a div with class infinity23-line-numbered-wrap and carrying the class
    // infinity23-line-numbered-textarea automatically gets synced line numbers. Used by Redirect 301/302
    // Bulk Redirect and Tag Manager code rows - reuse this for any future module that needs the same thing.
    // ===========================================================================================================================================

    function infinity23_line_numbers_update($textarea) {

        var $wrap = $textarea.closest('.infinity23-line-numbered-wrap');
        var $numbers = $wrap.find('.infinity23-line-numbers');
        var lineCount = $textarea.val().split('\n').length;
        var numbersHtml = '';

        for( var i = 1; i <= lineCount; i++ ) {
            numbersHtml += '<span>' + i + '</span>';
        } // for( var i = 1; i <= lineCount; i++ ) {

        $numbers.html(numbersHtml);

    } // function infinity23_line_numbers_update($textarea) {

    // --------------------------------------
    // grow a textarea to fit its content. When the textarea sits inside the
    // Bulk Redirect 3-column layout, every column is synced to the tallest
    // one so Request URL / Destination URL / Redirect Type stay lined up
    // --------------------------------------

    function infinity23_line_numbered_auto_resize($textarea) {

        if( !$textarea.hasClass('infinity23-textarea-adjustable') ) {
            return;
        } // if( !$textarea.hasClass('infinity23-textarea-adjustable') ) {

        var $bulkWrap = $textarea.closest('.infinity23-redirect-bulk-columns');
        var scrollTop = $(window).scrollTop();
        var minHeight = parseInt( $textarea.css('min-height') ) || 250;

        if( $bulkWrap.length ) {

            var maxHeight = minHeight;

            $bulkWrap.find('.infinity23-line-numbered-textarea').each(function() {
                $(this).css('height', 'auto');
                var thisHeight = $(this).prop('scrollHeight');
                if( thisHeight > maxHeight ) {
                    maxHeight = thisHeight;
                } // if( thisHeight > maxHeight ) {
            }); // .each

            $bulkWrap.find('.infinity23-line-numbered-textarea').css('height', maxHeight + 'px');

        } else {

            $textarea.css('height', 'auto');
            var thisHeight = $textarea.prop('scrollHeight');
            $textarea.css('height', Math.max(thisHeight, minHeight) + 'px');

        } // if( $bulkWrap.length ) {

        $(window).scrollTop(scrollTop);

    } // function infinity23_line_numbered_auto_resize($textarea) {

    // expose globally so inline scripts outside this closure (e.g. Tag Manager's add-row script) can reinit new rows
    window.infinity23UpdateLineNumbers = infinity23_line_numbers_update;
    window.infinity23AutoResizeTextarea = infinity23_line_numbered_auto_resize;

    $('.infinity23-line-numbered-textarea').each(function() {
        infinity23_line_numbers_update($(this));
        infinity23_line_numbered_auto_resize($(this));
    }); // .each

    $(document).on('input', '.infinity23-line-numbered-textarea', function() {
        infinity23_line_numbers_update($(this));
        infinity23_line_numbered_auto_resize($(this));
    }); // $(document).on('input', '.infinity23-line-numbered-textarea'

    $(document).on('scroll', '.infinity23-line-numbered-textarea', function() {
        var $wrap = $(this).closest('.infinity23-line-numbered-wrap');
        $wrap.find('.infinity23-line-numbers').scrollTop( $(this).scrollTop() );
    }); // $(document).on('scroll', '.infinity23-line-numbered-textarea'

    // ===========================================================================================================================================
    // Advanced Border Field - style select show/hide + linked sides sync
    // ===========================================================================================================================================

    // --------------------------------------
    // show or hide the details wrap based on the selected border style
    // the initial state is already set server-side via PHP (no flash on load) -
    // this only reacts to the person changing the select afterwards
    // --------------------------------------

    $(document).on('change', '.infinity23-border-style-select', function() {

        var $details = $('#' + $(this).data('target'));

        if ($(this).val() === 'none') {
            $details.addClass('infinity23-border-hidden');
        } else {
            $details.removeClass('infinity23-border-hidden');
        } // if ($(this).val() === 'none')

    }); // $(document).on('change', '.infinity23-border-style-select'

    // ===========================================================================================================================================
    // Background Field - type select show/hide
    // ===========================================================================================================================================

    $(document).on('change', '.infinity23-background-type-select', function() {

        var $wrap = $('#' + $(this).data('target'));
        var selectedType = $(this).val();

        $wrap.find('.infinity23-background-type-line').addClass('infinity23-position-hidden');
        $wrap.find('.infinity23-background-type-line[data-background-type="' + selectedType + '"]').removeClass('infinity23-position-hidden');

    }); // $(document).on('change', '.infinity23-background-type-select'

    // --------------------------------------
    // toggle the link icon - switching it on immediately syncs every side
    // to the top value, switching it off just stops future syncing
    // (the other sides are never locked/disabled)
    // --------------------------------------

    $(document).on('click', '.infinity23-border-link-btn', function() {

        var $btn = $(this);
        var $detailsWrap = $btn.closest('.infinity23-border-details-wrap');
        var $linkedInput = $detailsWrap.find('.infinity23-border-linked-input');

        if ($btn.hasClass('active')) {

            $btn.removeClass('active');
            $linkedInput.val('0');

        } else {

            $btn.addClass('active');
            $linkedInput.val('1');

            var topVal = $detailsWrap.find('.infinity23-border-side-top').val();
            $detailsWrap.find('.infinity23-border-side-right, .infinity23-border-side-bottom, .infinity23-border-side-left').val(topVal);

        } // if ($btn.hasClass('active'))

    }); // $(document).on('click', '.infinity23-border-link-btn'

    // --------------------------------------
    // while linked, typing in any one side copies the value to the other three
    // --------------------------------------

    $(document).on('input keyup', '.infinity23-border-side-top, .infinity23-border-side-right, .infinity23-border-side-bottom, .infinity23-border-side-left', function() {

        var $input = $(this);
        var $detailsWrap = $input.closest('.infinity23-border-details-wrap');
        var $linkBtn = $detailsWrap.find('.infinity23-border-link-btn');

        if ($linkBtn.hasClass('active')) {

            var val = $input.val();
            $detailsWrap.find('.infinity23-border-side-top, .infinity23-border-side-right, .infinity23-border-side-bottom, .infinity23-border-side-left').not($input).val(val);

        } // if ($linkBtn.hasClass('active'))

    }); // $(document).on('input keyup', '.infinity23-border-side-top...'

    // --------------------------------------
    // toggle the radius link icon - same pattern as the border link icon
    // --------------------------------------

    $(document).on('click', '.infinity23-radius-link-btn', function() {

        var $btn = $(this);
        var $detailsWrap = $btn.closest('.infinity23-border-details-wrap');
        var $linkedInput = $detailsWrap.find('.infinity23-radius-linked-input');

        if ($btn.hasClass('active')) {

            $btn.removeClass('active');
            $linkedInput.val('0');

        } else {

            $btn.addClass('active');
            $linkedInput.val('1');

            var topLeftVal = $detailsWrap.find('.infinity23-radius-corner-top-left').val();
            $detailsWrap.find('.infinity23-radius-corner-top-right, .infinity23-radius-corner-bottom-right, .infinity23-radius-corner-bottom-left').val(topLeftVal);

        } // if ($btn.hasClass('active'))

    }); // $(document).on('click', '.infinity23-radius-link-btn'

    // --------------------------------------
    // while linked, typing in any one corner copies the value to the other three
    // --------------------------------------

    $(document).on('input keyup', '.infinity23-radius-corner-top-left, .infinity23-radius-corner-top-right, .infinity23-radius-corner-bottom-right, .infinity23-radius-corner-bottom-left', function() {

        var $input = $(this);
        var $detailsWrap = $input.closest('.infinity23-border-details-wrap');
        var $linkBtn = $detailsWrap.find('.infinity23-radius-link-btn');

        if ($linkBtn.hasClass('active')) {

            var val = $input.val();
            $detailsWrap.find('.infinity23-radius-corner-top-left, .infinity23-radius-corner-top-right, .infinity23-radius-corner-bottom-right, .infinity23-radius-corner-bottom-left').not($input).val(val);

        } // if ($linkBtn.hasClass('active'))

    }); // $(document).on('input keyup', '.infinity23-radius-corner-top-left...'

    // ===========================================================================================================================================
    // Button Position Field - tab switch + mobile mode toggle
    // ===========================================================================================================================================

    // --------------------------------------
    // switch between the desktop and mobile tabs
    // --------------------------------------

    $(document).on('click', '.infinity23-position-tab-btn', function() {

        var $btn = $(this);
        var target = $btn.data('tab-target');
        var $wrap = $btn.closest('.infinity23-position-tabs-wrap');

        $wrap.find('.infinity23-position-tab-btn').removeClass('infinity23-position-tab-active');
        $btn.addClass('infinity23-position-tab-active');

        $wrap.find('.infinity23-position-tab-content').addClass('infinity23-position-tab-hidden');
        $wrap.find('#' + target).removeClass('infinity23-position-tab-hidden');

    }); // $(document).on('click', '.infinity23-position-tab-btn'

    // --------------------------------------
    // show or hide the mobile custom position fields based on the select
    // --------------------------------------

    $(document).on('change', '.infinity23-position-toggle-select', function() {

        var $select = $(this);
        var $target = $('#' + $select.data('target'));

        if ($select.val() === 'custom') {
            $target.removeClass('infinity23-position-hidden');
        } else {
            $target.addClass('infinity23-position-hidden');
        } // if ($select.val() === 'custom')

    }); // $(document).on('change', '.infinity23-position-toggle-select'

    // ===========================================================================================================================================
    // Update Manager - dim the block tables when Ignore All Blocks is selected
    // ===========================================================================================================================================

    $(document).on('change', '#force_block_updates_mode', function() {

        var $tablesWrap = $('.update-manager-warp .infinity23-fb-tables-wrap');

        if ($(this).val() === 'ignore') {
            $tablesWrap.addClass('infinity23-fb-tables-dimmed');
        } else {
            $tablesWrap.removeClass('infinity23-fb-tables-dimmed');
        } // if ($(this).val() === 'ignore')

    }); // $(document).on('change', '#force_block_updates_mode'

    // ===========================================================================================================================================
    // Update Manager - Force Block table row highlight
    // ===========================================================================================================================================

    $(document).on('change', '.infinity23-fb-checkbox', function() {

        var $row = $(this).closest('.infinity23-fb-row');

        if ($(this).is(':checked')) {
            $row.removeClass('fb-row-blocked');
        } else {
            $row.addClass('fb-row-blocked');
        } // if ($(this).is(':checked'))

    }); // $(document).on('change', '.infinity23-fb-checkbox'

    // ===========================================================================================================================================
    // Button Display Type Field (Global) - icon vs image toggle
    // shared by every module that uses infinity23_button_display_type_field()
    // ===========================================================================================================================================

    // --------------------------------------
    // show or hide the icon/image view wraps for the given module scope
    // --------------------------------------

    function infinity23_toggle_display_type_fields($scope) {

        var displayType = $scope.find('#button_display_type').val();

        if( displayType === 'image' ) {
            $scope.find('#icon-view-fields-wrap').hide();
            $scope.find('#image-view-fields-wrap').show();
            $scope.find('#icon_button_preview_2_wrap').hide();
            $scope.find('#icon-image-url-wrap').hide();
            $scope.find('#simple-image-url-wrap').show();
        } else {
            $scope.find('#icon-view-fields-wrap').show();
            $scope.find('#image-view-fields-wrap').hide();
            $scope.find('#icon_button_preview_2_wrap').show();
            $scope.find('#icon-image-url-wrap').show();
            $scope.find('#simple-image-url-wrap').hide();
        } // if( displayType === 'image' ) {

    } // function infinity23_toggle_display_type_fields($scope) {

    // --------------------------------------
    // tab click - switch type, toggle the wraps, then fire a custom event
    // so each module's own script can refresh its own preview (that part
    // stays module-specific since the preview fields differ per module)
    // --------------------------------------

    $(document).on('click', '.infinity23-warp .infinity23-button-type-tab-btn', function() {

        var $btn = $(this);
        var $scope = $btn.closest('.infinity23-warp');
        var newType = $btn.data('display-type');

        $scope.find('.infinity23-button-type-tab-btn').removeClass('infinity23-button-type-tab-active');
        $btn.addClass('infinity23-button-type-tab-active');

        $scope.find('#button_display_type').val(newType).trigger('change').trigger('infinity23-display-type-changed');

        infinity23_toggle_display_type_fields($scope);

    }); // $(document).on('click', '.infinity23-warp .infinity23-button-type-tab-btn'

    // ===========================================================================================================================================
    // Page Notes - All Notes table - collapsed view toggle
    // ===========================================================================================================================================

    $(document).on('click', '.notes-warp .infinity23-notes-all-edit-icon', function(e) {

        e.preventDefault();

        var $cell = $(this).closest('.infinity23-notes-all-row').find('.notes-col-preview');

        if( !$cell.find('.infinity23-notes-all-edit-wrap').hasClass('infinity23-position-hidden') ) {

            $cell.find('.infinity23-notes-all-cancel-edit-btn').trigger('click');
            return;

        } // if( !$cell.find('.infinity23-notes-all-edit-wrap').hasClass('infinity23-position-hidden') ) {

        $cell.find('.infinity23-notes-all-edit-wrap textarea').each(function() {
            $(this).data('infinity23-notes-original-text', $(this).val());
        }); // .each

        $cell.find('.infinity23-notes-all-view-wrap').addClass('infinity23-position-hidden');
        $cell.find('.infinity23-notes-all-edit-wrap').removeClass('infinity23-position-hidden');

    }); // $(document).on('click', '.notes-warp .infinity23-notes-all-edit-icon'

    // --------------------------------------
    // cancel changes - revert the widget's textareas and go back to view mode
    // --------------------------------------

    $(document).on('click', '.notes-warp .infinity23-notes-all-cancel-edit-btn', function() {

        var $cell = $(this).closest('.notes-col-preview');

        $cell.find('.infinity23-notes-all-edit-wrap textarea').each(function() {
            $(this).val( $(this).data('infinity23-notes-original-text') );
        }); // .each

        $cell.find('.infinity23-notes-all-edit-wrap').addClass('infinity23-position-hidden');
        $cell.find('.infinity23-notes-all-view-wrap').removeClass('infinity23-position-hidden');

    }); // $(document).on('click', '.notes-warp .infinity23-notes-all-cancel-edit-btn'

    // ===========================================================================================================================================
    // Page Notes - All Notes table - remove all notes for a page
    // ===========================================================================================================================================

    $(document).on('click', '.notes-warp .infinity23-notes-all-remove-page-btn', function() {

        if( !confirm('Are you sure you want to permanently remove all notes for this page?') ) { return; }

        var $btn = $(this);
        var $row = $btn.closest('.infinity23-notes-all-row');
        var $group = $row.closest('.infinity23-notes-all-group');
        var pageId = $btn.data('page-id');
        var nonce = $('#infinity23_notes_all_remove_page_nonce').val();

        $btn.prop('disabled', true);

        $.post( ajaxurl, {
            action: 'infinity23_notes_remove_all_for_page',
            infinity23_notes_all_remove_page_nonce: nonce,
            page_id: pageId
        }, function( response ) {

            if( response.success ) {

                $row.addClass('infinity23-notes-all-row-removing').animate({ opacity: 0.15 }, 500, function() {

                    $row.slideUp(500, function() {

                        $row.remove();

                        if( $group.find('.infinity23-notes-all-row').length === 0 ) {
                            $group.fadeOut(500, function() {
                                $group.remove();
                            });
                        } // if( $group.find('.infinity23-notes-all-row').length === 0 ) {

                    });

                });

            } else {
                $btn.prop('disabled', false);
                alert( response.data.message );
            } // if( response.success ) {

        }); // $.post

    }); // $(document).on('click', '.notes-warp .infinity23-notes-all-remove-page-btn'

    // ===========================================================================================================================================
    // Redirect 301/302 - URL Shortener generate short code button
    // ===========================================================================================================================================

    $(document).on('click', '#infinity23-generate-shortcode-btn', function(e) {

        e.preventDefault();

        var $btn = $(this);
        var $input = $('#request_url');
        var ajaxUrl = $btn.data('ajax-url');
        var nonce = $btn.data('nonce');
        var originalText = $btn.text();

        $btn.html('<span class="infinity23-exclude-id-spinner"></span>').prop('disabled', true);

        $.post( ajaxUrl, {
            action: 'infinity23_redirect_generate_shortcode',
            nonce: nonce
        }, function( response ) {

            $btn.text( originalText ).prop('disabled', false);

            if( response.success ) {
                $input.val( response.data.request_url ).trigger('change');
            } else {
                alert( response.data.message );
            } // if( response.success ) {

        }); // $.post

    }); // $(document).on('click', '#infinity23-generate-shortcode-btn'

    // ===========================================================================================================================================
    // Pro Locked Fields - disable editing of fields inside a pro-only section when the user does not have pro
    // ===========================================================================================================================================

    $('.infinity23-pro-locked').find('input, select, textarea').prop('disabled', true);

    // ===========================================================================================================================================
    // Pro Locked Fields - hover tooltip explaining why the field can't be edited, with
    // a link to the Pro version. Bound to each field's wrapper (never the disabled
    // input/select/textarea itself, since disabled elements don't reliably fire hover
    // events in every browser) so it works for a plain field, a Custom CSS textarea and
    // a toggle-table switch alike. Position is fixed relative to the field on show (not
    // tracked on every mousemove), and a short hide delay plus hover handlers on the
    // tooltip itself let the mouse travel from the field into the tooltip and click
    // the link, instead of the tooltip vanishing the moment the mouse leaves the field.
    // ===========================================================================================================================================

    var infinity23ProLockTooltipSelector = '.infinity23-pro-locked .form-line, .infinity23-pro-locked .infinity23-fb-toggle-label, .infinity23-pro-locked .infinity23-line-numbered-wrap';
    var $infinity23ProLockTooltip = $('<div class="infinity23-pro-lock-tooltip">This option is only available in the <a href="' + infinity23_pro_version_link + '" target="_blank" rel="noopener">Pro version</a></div>').appendTo('body');
    var infinity23ProLockTooltipHideTimer = null;

    function infinity23ShowProLockTooltip( $target ) {

        clearTimeout( infinity23ProLockTooltipHideTimer );

        var targetRect = $target[0].getBoundingClientRect();
        $infinity23ProLockTooltip.css({ top: (targetRect.bottom + 4) + 'px', left: targetRect.left + 'px' }).show();

    } // function infinity23ShowProLockTooltip( $target ) {

    function infinity23HideProLockTooltip() {

        infinity23ProLockTooltipHideTimer = setTimeout( function() {
            $infinity23ProLockTooltip.hide();
        }, 250 );

    } // function infinity23HideProLockTooltip() {

    $(document).on('mouseenter', infinity23ProLockTooltipSelector, function() {
        infinity23ShowProLockTooltip( $(this) );
    });

    $(document).on('mouseleave', infinity23ProLockTooltipSelector, function() {
        infinity23HideProLockTooltip();
    });

    $infinity23ProLockTooltip.on('mouseenter', function() {
        clearTimeout( infinity23ProLockTooltipHideTimer );
    });

    $infinity23ProLockTooltip.on('mouseleave', function() {
        infinity23HideProLockTooltip();
    });

    // ===========================================
    // Show All Pages - table columns, filters, and ajax pagination
    // ===========================================

    // --------------------------------------
    // show/hide table columns by checkbox toggle
    // --------------------------------------

    $('.sap-col-toggle').on('change', function() {

        var col  = $(this).data('col');
        var show = $(this).is(':checked');

        $('.show-all-pages-warp').toggleClass('hide-' + col, !show);

    }); // $('.sap-col-toggle').on('change'

    // --------------------------------------
    // row filters - SEO and noindex (full page reload)
    // --------------------------------------

    $('.sap-seo-filter-form input[type="checkbox"]').on('change', function() {
        $(this).closest('form').submit();
    }); // $('.sap-seo-filter-form input[type="checkbox"]').on('change'

    // --------------------------------------
    // hide row by eye button
    // --------------------------------------

    $(document).on('click', '.infinity23-all-pages-table .sap-hide-row', function() {
        $(this).closest('tr').fadeOut(400);
    }); // $(document).on('click', '.infinity23-all-pages-table .sap-hide-row'

    // --------------------------------------
    // open / collapse all section cards
    // --------------------------------------

    $('.sap-open-all').on('click', function() {
        $('.show-all-pages-warp .section-card').removeClass('card-collapsed').find('.card-body').removeClass('collapsed');
    }); // $('.sap-open-all').on('click'

    $('.sap-collapse-all').on('click', function() {
        $('.show-all-pages-warp .section-card').addClass('card-collapsed').find('.card-body').addClass('collapsed');
    }); // $('.sap-collapse-all').on('click'

    // --------------------------------------
    // reload the page when the entries per page changes
    // --------------------------------------

    $('#per_page').on('change', function() {
        $(this).closest('form').submit();
    }); // $('#per_page').on('change'

    // --------------------------------------
    // ajax pagination - load a page of a section without refreshing
    // --------------------------------------

    $(document).on('click', '.sap-section-wrap .sap-page-link', function(e) {

        e.preventDefault();

        var $link = $(this);
        var $wrap = $link.closest('.sap-section-wrap');
        var pagedKey = $wrap.data('paged-key');
        var paged = parseInt( $link.data('page') );

        $wrap.addClass('sap-loading');

        $.post( sap_ajax_url, {
            action: 'infinity23_show_all_pages_page',
            nonce: sap_ajax_nonce,
            mode: $wrap.data('mode'),
            post_type: $wrap.data('post-type') || '',
            post_status: $wrap.data('post-status') || '',
            taxonomy: $wrap.data('taxonomy') || '',
            paged_key: pagedKey,
            per_page: $wrap.data('per-page'),
            search_term: $wrap.data('search-term') || '',
            search_by: $wrap.data('search-by') || 'title',
            hide_no_seo: $('.sap-seo-filter-form input[name="hide_no_seo"]').is(':checked') ? 'yes' : '',
            hide_noindex: $('.sap-seo-filter-form input[name="hide_noindex"]').is(':checked') ? 'yes' : '',
            paged: paged,
            page_query: window.location.search
        }).done(function( response ) {

            $wrap.html( response ).removeClass('sap-loading');
            infinity23SapUpdateUrl( pagedKey, paged );

        }).fail(function( xhr ) {

            $wrap.removeClass('sap-loading');

            if( xhr.status === 400 || xhr.status === 403 ) {
                window.location.href = $link.attr('href');
                return;
            } // if( xhr.status === 400 || xhr.status === 403 ) {

            alert( 'Loading failed (' + xhr.status + ' ' + xhr.statusText + ')\n\n' + xhr.responseText.substring(0, 800) );

        }); // $.post

    }); // $(document).on('click', '.sap-page-link'

    // --------------------------------------
    // keep the url in sync with the current page numbers
    // --------------------------------------

    function infinity23SapUpdateUrl( pagedKey, paged ) {

        if( !window.history || !window.history.replaceState ) { return; }

        var params = new URLSearchParams( window.location.search );
        params.set( pagedKey, paged );
        window.history.replaceState( null, '', window.location.pathname + '?' + params.toString() );

    } // function infinity23SapUpdateUrl( pagedKey, paged ) {

    // ===========================================
    // Border / Border-Radius / Background / Image Live Preview - shared by
    // every module using the advanced border, border-radius, and background
    // fields (back-to-top, accessibility, phone-button). Values are read
    // straight from the DOM (already filled in by PHP), so this one copy
    // works correctly regardless of which module's page it runs on.
    // ===========================================

    function infinity23_update_border_preview() {

        var borderStyle = $('#button_border_style').val();
        var $previews = $('#icon_button_preview, #icon_button_preview_2');

        if( borderStyle === 'none' || !borderStyle ) {
            $previews.css('border-top', 'none');
            $previews.css('border-right', 'none');
            $previews.css('border-bottom', 'none');
            $previews.css('border-left', 'none');
            return;
        } // if( borderStyle === 'none' || !borderStyle ) {

        var $detailsWrap = $('#button_border_top').closest('.infinity23-border-details-wrap');
        var isLinked = $detailsWrap.find('.infinity23-border-link-btn').hasClass('active');

        var borderColor = $('#button_border_color').val() || '#000000';
        var borderUnit = $('#button_border_unit').val() || 'px';
        var borderTop = $('#button_border_top').val() || '0';
        var borderRight = isLinked ? borderTop : ( $('#button_border_right').val() || '0' );
        var borderBottom = isLinked ? borderTop : ( $('#button_border_bottom').val() || '0' );
        var borderLeft = isLinked ? borderTop : ( $('#button_border_left').val() || '0' );

        $previews.css('border-top', borderTop + borderUnit + ' ' + borderStyle + ' ' + borderColor);
        $previews.css('border-right', borderRight + borderUnit + ' ' + borderStyle + ' ' + borderColor);
        $previews.css('border-bottom', borderBottom + borderUnit + ' ' + borderStyle + ' ' + borderColor);
        $previews.css('border-left', borderLeft + borderUnit + ' ' + borderStyle + ' ' + borderColor);

    } // function infinity23_update_border_preview()

    function infinity23_update_border_radius_preview() {

        var $detailsWrap = $('#border_radius_top_left').closest('.infinity23-border-details-wrap');
        var isLinked = $detailsWrap.find('.infinity23-radius-link-btn').hasClass('active');

        var unit = $('#border_radius_unit').val() || 'px';
        var topLeft = $('#border_radius_top_left').val() || '0';
        var topRight = isLinked ? topLeft : ( $('#border_radius_top_right').val() || '0' );
        var bottomRight = isLinked ? topLeft : ( $('#border_radius_bottom_right').val() || '0' );
        var bottomLeft = isLinked ? topLeft : ( $('#border_radius_bottom_left').val() || '0' );

        var $previews = $('#icon_button_preview, #icon_button_preview_2');

        $previews.css('border-top-left-radius', topLeft + unit);
        $previews.css('border-top-right-radius', topRight + unit);
        $previews.css('border-bottom-right-radius', bottomRight + unit);
        $previews.css('border-bottom-left-radius', bottomLeft + unit);

    } // function infinity23_update_border_radius_preview()

    function infinity23_get_current_bg_color() {

        var bgType = $('#background_type').val();

        if( bgType === 'gradient' ) {
            var color1 = $('#background_gradient_color_1').val() || '#000000';
            var color2 = $('#background_gradient_color_2').val() || '#000000';
            var angle = parseInt( $('#background_gradient_angle').val() ) || 180;
            return 'linear-gradient(' + angle + 'deg, ' + color1 + ', ' + color2 + ')';
        } // if( bgType === 'gradient' ) {

        if( bgType === 'custom_css' ) {
            return $('#background_custom_css').val() || 'transparent';
        } // if( bgType === 'custom_css' ) {

        if( bgType === 'none' ) {
            return 'transparent';
        } // if( bgType === 'none' ) {

        return $('#background_solid_color').val();

    } // function infinity23_get_current_bg_color()

    function infinity23_update_preview() {

        if( $('#button_display_type').val() === 'image' ) {

            var imageWidth = parseInt( $('#image_width').val() ) || 0;
            var imageHeight = parseInt( $('#image_height').val() ) || 0;

            $('#simple_image_preview').css('width', imageWidth + 'px');
            $('#simple_image_preview').css('height', imageHeight + 'px');

            $('#simple_image_preview_img').css('width', imageWidth + 'px');
            $('#simple_image_preview_img').css('height', imageHeight + 'px');

            return;

        } // if( $('#button_display_type').val() === 'image' ) {

        var $previews = $('#icon_button_preview, #icon_button_preview_2');
        var $images   = $('#icon_image_preview, #icon_image_preview_2');

        var bgColor = infinity23_get_current_bg_color();
        var buttonWidth = parseInt( $('#button_width').val() ) || 0;
        var buttonHeight = parseInt( $('#button_height').val() ) || 0;
        var iconWidth = parseInt( $('#icon_width').val() ) || 0;
        var iconHeight = parseInt( $('#icon_height').val() ) || 0;

        $previews.css('width', buttonWidth + 'px');
        $previews.css('height', buttonHeight + 'px');
        $previews.css('background', bgColor);
        infinity23_update_border_radius_preview();
        infinity23_update_border_preview();

        $images.css('width', iconWidth + 'px');
        $images.css('height', iconHeight + 'px');

    } // function infinity23_update_preview()

    // --------------------------------------
    // display type toggle (icon vs image) and size/border/background bindings
    // --------------------------------------

    $('#button_display_type').on('infinity23-display-type-changed', function() {
        infinity23_update_preview();
    }); // $('#button_display_type').on('infinity23-display-type-changed'

    $('#image_width, #image_height').on('input', function() {
        infinity23_update_preview();
    }); // $('#image_width, #image_height').on('input'

    $('#button_width, #button_height').on('input', function() {
        infinity23_update_preview();
    }); // $('#button_width, #button_height').on('input'

    $('#icon_width, #icon_height').on('input', function() {
        infinity23_update_preview();
    }); // $('#icon_width, #icon_height').on('input'

    $('#transparent_background_checkbox').on('change', function() {
        infinity23_update_preview();
    }); // $('#transparent_background_checkbox').on('change'

    $('#border_radius_top_left, #border_radius_top_right, #border_radius_bottom_right, #border_radius_bottom_left, #border_radius_unit').on('input change keyup', function() {
        infinity23_update_border_radius_preview();
    }); // $('#border_radius_top_left, ...').on('input change keyup'

    $(document).on('click', '.infinity23-radius-link-btn', function() {
        infinity23_update_border_radius_preview();
    }); // $(document).on('click', '.infinity23-radius-link-btn'

    $('#button_border_style').on('change', function() {
        infinity23_update_preview();
    }); // $('#button_border_style').on('change'

    $(document).on('infinity23-color-changed', '#button_border_color', function() {
        infinity23_update_preview();
    }); // $(document).on('infinity23-color-changed', '#button_border_color'

    $('#button_border_unit, #button_border_top, #button_border_right, #button_border_bottom, #button_border_left').on('input change', function() {
        infinity23_update_preview();
    }); // $('#button_border_unit, #button_border_top, #button_border_right, #button_border_bottom, #button_border_left').on('input change'

    $(document).on('click', '.infinity23-border-link-btn', function() {
        infinity23_update_preview();
    }); // $(document).on('click', '.infinity23-border-link-btn'

    $('#background_type').on('change', function() {
        infinity23_update_preview();
    }); // $('#background_type').on('change'

    $(document).on('infinity23-color-changed', '#background_solid_color, #background_gradient_color_1, #background_gradient_color_2', function() {
        infinity23_update_preview();
    }); // $(document).on('infinity23-color-changed', '#background_solid_color, ...'

    $('#background_gradient_angle, #background_custom_css').on('input', function() {
        infinity23_update_preview();
    }); // $('#background_gradient_angle, #background_custom_css').on('input'

    $(document).on('change keyup', '#icon_image_url', function() {
        $('#icon_image_preview, #icon_image_preview_2').attr('src', $(this).val());
    }); // $(document).on('change keyup', '#icon_image_url'

    $(document).on('change keyup', '#simple_image_url', function() {
        $('#simple_image_preview_img').attr('src', $(this).val());
    }); // $(document).on('change keyup', '#simple_image_url'

    // --------------------------------------
    // back-to-top only - scroll trigger fields visibility (no-ops on other
    // modules' pages since #button_visibility/.scroll-offset-fields-wrap
    // simply don't exist there)
    // --------------------------------------

    function infinity23_toggle_scroll_offset_fields() {

        if( $('#button_visibility').val() === 'always' ) {
            $('.scroll-offset-fields-wrap').hide();
        } else {
            $('.scroll-offset-fields-wrap').show();
        } // if( $('#button_visibility').val() === 'always' ) {

    } // function infinity23_toggle_scroll_offset_fields()

    $('#button_visibility').on('change', function() {
        infinity23_toggle_scroll_offset_fields();
    }); // $('#button_visibility').on('change'

    infinity23_toggle_scroll_offset_fields();

    // ===========================================
    // Tag manager - repeating rows (head / body / footer / lazy load js)
    // ===========================================

    // --------------------------------------
    // add new row
    // --------------------------------------

    $(document).on('click', '.infinity23-tagrow-add', function() {

        var targetWrap = $(this).data('target');
        var $wrap = $('#' + targetWrap);
        var $lastRow = $wrap.find('.infinity23-tagrow-item').last();
        var $newRow = $lastRow.clone();
        $newRow.find('textarea').val('');
        $newRow.find('.infinity23-tagrow-remove').show();
        $wrap.append($newRow);

        if( typeof window.infinity23UpdateLineNumbers === 'function' ) {
            window.infinity23UpdateLineNumbers( $newRow.find('.infinity23-line-numbered-textarea') );
        } // if( typeof window.infinity23UpdateLineNumbers === 'function' ) {

        if( typeof window.infinity23AutoResizeTextarea === 'function' ) {
            window.infinity23AutoResizeTextarea( $newRow.find('.infinity23-line-numbered-textarea') );
        } // if( typeof window.infinity23AutoResizeTextarea === 'function' ) {

    }); // $(document).on('click', '.infinity23-tagrow-add'

    // --------------------------------------
    // alert if script tags are typed into the lazy load js rows
    // --------------------------------------

    $(document).on('input', '#infinity23-tagrow-lazyjs-wrap textarea', function() {

        var $textarea = $(this);
        var val = $textarea.val();
        var scriptTagRegex = /<\s*\/?\s*script[^>]*>/gi;

        if( scriptTagRegex.test(val) ) {

            alert('Error: do not insert <scr' + 'ipt></scr' + 'ipt> tags (the plugin already does it), insert only the rwa JavaScript code.');

            var cursorPos = this.selectionStart;
            var cleanedVal = val.replace(scriptTagRegex, '');
            var removedChars = val.length - cleanedVal.length;

            $textarea.val(cleanedVal);
            this.setSelectionRange(cursorPos - removedChars, cursorPos - removedChars);

            if( typeof window.infinity23UpdateLineNumbers === 'function' ) {
                window.infinity23UpdateLineNumbers( $textarea );
            } // if( typeof window.infinity23UpdateLineNumbers === 'function' ) {

        } // if( scriptTagRegex.test(val) ) {

    }); // $(document).on('input', '#infinity23-tagrow-lazyjs-wrap textarea'

    // --------------------------------------
    // remove row
    // --------------------------------------

    $(document).on('click', '.infinity23-tagrow-remove', function() {

        var $item = $(this).closest('.infinity23-tagrow-item');
        var $wrap = $item.closest('.infinity23-tagrow-wrap');

        if( $wrap.find('.infinity23-tagrow-item').length > 1 ) {
            $item.remove();
        } else {

            var $clearedTextarea = $item.find('textarea');
            $clearedTextarea.val('');

            if( typeof window.infinity23UpdateLineNumbers === 'function' ) {
                window.infinity23UpdateLineNumbers( $clearedTextarea );
            } // if( typeof window.infinity23UpdateLineNumbers === 'function' ) {

            if( typeof window.infinity23AutoResizeTextarea === 'function' ) {
                window.infinity23AutoResizeTextarea( $clearedTextarea );
            } // if( typeof window.infinity23AutoResizeTextarea === 'function' ) {

        } // if( $wrap.find('.infinity23-tagrow-item').length > 1 ) {

    }); // $(document).on('click', '.infinity23-tagrow-remove'

    // ======================================================================================
    // Email SMTP - provider preset autofill
    // ======================================================================================

    // ------------------------------------------
    // Email SMTP - provider preset autofill
    // ------------------------------------------

    $('#smtp_provider').on('change', function() {

        var selectedProvider = $(this).val();
        var $selected = $(this).find('option:selected');
        var presetNote = $selected.data('note');

        if( selectedProvider === 'wordpress_default' ) {
            $('#infinity23-smtp-server-fields-wrap').addClass('infinity23-smtp-note-hidden');
        } else {
            $('#infinity23-smtp-server-fields-wrap').removeClass('infinity23-smtp-note-hidden');
        } // if( selectedProvider === 'wordpress_default' )

        $('.infinity23-smtp-provider-block').addClass('infinity23-smtp-note-hidden');
        $('.infinity23-smtp-provider-block[data-provider="' + selectedProvider + '"]').removeClass('infinity23-smtp-note-hidden');

        if( presetNote ) {
            $('#smtp_provider_note').text(presetNote).removeClass('infinity23-smtp-note-hidden');
        } else {
            $('#smtp_provider_note').text('').addClass('infinity23-smtp-note-hidden');
        } // if( presetNote )

    }); // $('#smtp_provider').on('change'

    $('#smtp_custom_auth_type').on('change', function() {

        if( $(this).val() === 'none' ) {
            $('#infinity23-smtp-custom-auth-fields-wrap').addClass('infinity23-smtp-note-hidden');
        } else {
            $('#infinity23-smtp-custom-auth-fields-wrap').removeClass('infinity23-smtp-note-hidden');
        } // if( $(this).val() === 'none' )

    }); // $('#smtp_custom_auth_type').on('change'

    $('.infinity23-elastic-method-select').on('change', function() {

        if( $(this).val() === 'api' ) {
            $('#infinity23-elastic-username-wrap').addClass('infinity23-smtp-note-hidden');
            $('#infinity23-elastic-password-label').text('API Key: ');
        } else {
            $('#infinity23-elastic-username-wrap').removeClass('infinity23-smtp-note-hidden');
            $('#infinity23-elastic-password-label').text('SMTP Password: ');
        } // if( $(this).val() === 'api' )

    }); // $('.infinity23-elastic-method-select').on('change'

    // ------------------------------------------
    // Email SMTP - masked password fields, clear on focus and
    // mark as touched on any real change (typing, paste, autofill)
    // ------------------------------------------

    $(document).on('focus', '.infinity23-masked-password-field', function() {

        var $field = $(this);

        if( $field.val().indexOf('•') !== -1 ) {
            $field.val('');
        } // if( $field.val().indexOf('•') !== -1 )

    }); // $(document).on('focus', '.infinity23-masked-password-field'

    $(document).on('input', '.infinity23-masked-password-field', function() {

        var $field = $(this);
        $('#' + $field.attr('id') + '_touched').val('1');

    }); // $(document).on('input', '.infinity23-masked-password-field'

    // ------------------------------------------
    // Email SMTP - test connection (ajax, no email sent)
    // ------------------------------------------

    $('#infinity23-email-smtp-test-connection-btn').on('click', function() {

        var $btn = $(this);
        var $result = $('#infinity23-email-smtp-connection-result');

        $result.removeClass('infinity23-connection-result-success infinity23-connection-result-error').addClass('infinity23-smtp-note-hidden').text('');
        $btn.prop('disabled', true).text('Testing...');

        var formData = $btn.closest('form').serialize();

        $.post( ajaxurl, formData + '&action=infinity23_email_smtp_test_connection&nonce=' + $btn.data('nonce'), function( response ) {

            $btn.prop('disabled', false).text('Test Connection');

            if( response.success ) {
                $result.removeClass('infinity23-smtp-note-hidden').addClass('infinity23-connection-result-success').text(response.data.message);
            } else {

                var connectionErrorHtml = 'Connection failed - ' + response.data.message;

                if( response.data.tips && response.data.tips.length ) {
                    connectionErrorHtml += '<ul class="infinity23-test-result-reasons">';
                    $.each( response.data.tips, function( tipIndex, tipText ) {
                        connectionErrorHtml += '<li>' + tipText + '</li>';
                    });
                    connectionErrorHtml += '</ul>';
                } // if( response.data.tips && response.data.tips.length )

                $result.removeClass('infinity23-smtp-note-hidden').addClass('infinity23-connection-result-error').html(connectionErrorHtml);

            } // if( response.success )

        }).fail(function() {

            $btn.prop('disabled', false).text('Test Connection');
            $result.removeClass('infinity23-smtp-note-hidden').addClass('infinity23-connection-result-error').text('Connection test failed - request failed');

        }); // $.post

    }); // $('#infinity23-email-smtp-test-connection-btn').on('click'

    // ------------------------------------------
    // Email SMTP - show the advance fields and hide the button
    // ------------------------------------------

    $(document).on('click', '.email-smtp-warp .infinity23-email-smtp-advance-btn', function() {

        var $btn = $(this);
        $btn.next('.infinity23-email-smtp-advance-fields-wrap').removeClass('infinity23-email-smtp-advance-hidden');
        $btn.hide();

    }); // $(document).on('click', '.infinity23-email-smtp-advance-btn'

    // ------------------------------------------
    // Email SMTP - send test email via ajax
    // ------------------------------------------

    $('#infinity23-email-smtp-test-send-btn').on('click', function() {

        var $btn = $(this);
        var $result = $('#infinity23-email-smtp-test-result');

        $result.removeClass('infinity23-message-success infinity23-message-error').addClass('infinity23-smtp-note-hidden').text('');
        $btn.prop('disabled', true).text('Sending...');

        $.post( ajaxurl, {
            action: 'infinity23_email_smtp_send_test',
            nonce: $btn.data('nonce'),
            test_to_email: $('#test_to_email').val(),
            test_subject: $('#test_subject').val(),
            test_message: $('#test_message').val(),
            test_from_name: $('#test_from_name').val(),
            test_from_email: $('#test_from_email').val()
        }, function( response ) {

            $btn.prop('disabled', false).text('Send Test Email');

            if( response.success ) {
                $result.removeClass('infinity23-smtp-note-hidden').addClass('infinity23-message-success').html(response.data.message);
            } else {

                var sendErrorHtml = 'The test email could not be sent - ' + response.data.message;

                if( response.data.tips && response.data.tips.length ) {
                    sendErrorHtml += '<ul class="infinity23-test-result-reasons">';
                    $.each( response.data.tips, function( tipIndex, tipText ) {
                        sendErrorHtml += '<li>' + tipText + '</li>';
                    });
                    sendErrorHtml += '</ul>';
                } // if( response.data.tips && response.data.tips.length )

                $result.removeClass('infinity23-smtp-note-hidden').addClass('infinity23-message-error').html(sendErrorHtml);

            } // if( response.success )

        }).fail(function() {

            $btn.prop('disabled', false).text('Send Test Email');
            $result.removeClass('infinity23-smtp-note-hidden').addClass('infinity23-message-error').text('The test email could not be sent - request failed');

        }); // $.post

    }); // $('#infinity23-email-smtp-test-send-btn').on('click'

    // ------------------------------------------
    // Email SMTP - Email Log - view message toggle
    // ------------------------------------------

    $(document).on('click', '.infinity23-email-log-view-btn', function() {

        var $btn = $(this);
        var $row = $('#' + $btn.data('target'));

        if( $row.hasClass('infinity23-smtp-note-hidden') ) {
            $row.removeClass('infinity23-smtp-note-hidden');
            $btn.text('Hide Message');
        } else {
            $row.addClass('infinity23-smtp-note-hidden');
            $btn.text('View Message');
        } // if( $row.hasClass('infinity23-smtp-note-hidden') )

    }); // $(document).on('click', '.infinity23-email-log-view-btn'

    // --------------------------------------
    // view as html toggle
    // --------------------------------------

    $(document).on('click', '.infinity23-email-log-view-html-btn', function() {

        var $btn = $(this);
        var $row = $('#' + $btn.data('target'));

        if( $row.hasClass('infinity23-smtp-note-hidden') ) {
            $row.removeClass('infinity23-smtp-note-hidden');
            $btn.text('Hide HTML View');
        } else {
            $row.addClass('infinity23-smtp-note-hidden');
            $btn.text('View As HTML');
        } // if( $row.hasClass('infinity23-smtp-note-hidden') )

    }); // $(document).on('click', '.infinity23-email-log-view-html-btn'

});
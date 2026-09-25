<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Save notes to the dedicated notes database table
    // Called from save_post action and AJAX handler in loader.php
    // ===========================================================================

    // ---------------------------------------
    // get the page post id and context
    // ---------------------------------------

    // phpcs:disable WordPress.Security.NonceVerification.Missing -- both callers (save_post hook and wp_ajax_infinity23_notes_save in back-end-loader.php) verify infinity23_notes_box_nonce before including this file
    if( isset( $infinity23_notes_forced_page_id ) ) {
        $infinity23_notes_save_page_id = strval( $infinity23_notes_forced_page_id );
    } else {
        $infinity23_notes_save_page_id = isset( $_POST['infinity23_notes_page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['infinity23_notes_page_id'] ) ) : '';
    } // if( isset( $infinity23_notes_forced_page_id ) ) {
    // phpcs:enable WordPress.Security.NonceVerification.Missing

    $infinity23_notes_save_is_dashboard = ( $infinity23_notes_save_page_id === 'dashboard' );

    // ---------------------------------------
    // build the page_post_id used to group rows for this context
    // ---------------------------------------

    if( $infinity23_notes_save_is_dashboard ) {

        $infinity23_notes_page_post_id = 'dashboard';

    } else {

        $infinity23_notes_numeric_id = intval( $infinity23_notes_save_page_id );

        if( empty( $infinity23_notes_numeric_id ) ) { return; }

        if( !current_user_can( 'edit_post', $infinity23_notes_numeric_id ) ) { return; }

        $infinity23_notes_page_post_id = strval( $infinity23_notes_numeric_id );

    } // if( $infinity23_notes_save_is_dashboard ) {

    // ---------------------------------------
    // collect the posted rows
    // ---------------------------------------

    // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- both callers (save_post hook and wp_ajax_infinity23_notes_save in back-end-loader.php) verify infinity23_notes_box_nonce before including this file; each element is wp_unslash()'d/sanitized in the foreach loops below
    $infinity23_notes_raw        = isset( $_POST['infinity23_notes_data'] ) ? $_POST['infinity23_notes_data'] : array();
    $infinity23_notes_directions = isset( $_POST['infinity23_notes_directions'] ) && is_array( $_POST['infinity23_notes_directions'] ) ? $_POST['infinity23_notes_directions'] : array();
    $infinity23_notes_row_ids    = isset( $_POST['infinity23_notes_row_ids'] ) && is_array( $_POST['infinity23_notes_row_ids'] ) ? $_POST['infinity23_notes_row_ids'] : array();
    // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

    $infinity23_notes_kept_row_ids = array();

    if( is_array( $infinity23_notes_raw ) ) {

        foreach( $infinity23_notes_raw as $infinity23_notes_index => $infinity23_note_text ) {

            if( is_array( $infinity23_note_text ) || is_object( $infinity23_note_text ) ) { continue; }

            $infinity23_note_text      = sanitize_textarea_field( wp_unslash( strval( $infinity23_note_text ) ) );
            $infinity23_note_direction = 'direction-ltr';

            if( isset( $infinity23_notes_directions[ $infinity23_notes_index ] ) && !is_array( $infinity23_notes_directions[ $infinity23_notes_index ] ) && !is_object( $infinity23_notes_directions[ $infinity23_notes_index ] ) ) {
                $infinity23_note_direction = sanitize_text_field( wp_unslash( strval( $infinity23_notes_directions[ $infinity23_notes_index ] ) ) );
            } // if( isset( $infinity23_notes_directions[ $infinity23_notes_index ] ) ...

            // only allow known direction values
            if( !in_array( $infinity23_note_direction, array( 'direction-ltr', 'direction-rtl' ) ) ) {
                $infinity23_note_direction = 'direction-ltr';
            } // if( !in_array( $infinity23_note_direction...

            $infinity23_note_row_id = 0;

            if( isset( $infinity23_notes_row_ids[ $infinity23_notes_index ] ) && !is_array( $infinity23_notes_row_ids[ $infinity23_notes_index ] ) && !is_object( $infinity23_notes_row_ids[ $infinity23_notes_index ] ) ) {
                $infinity23_note_row_id = intval( $infinity23_notes_row_ids[ $infinity23_notes_index ] );
            } // if( isset( $infinity23_notes_row_ids[ $infinity23_notes_index ] ) ...

            // only accept a row id that really belongs to this page - otherwise a
            // crafted request could update or delete another page's notes
            if( !empty( $infinity23_note_row_id ) ) {

                $infinity23_note_existing_row = infinity23_notes_get_row( $infinity23_note_row_id );

                if( empty( $infinity23_note_existing_row ) || strval( $infinity23_note_existing_row->page_post_id ) !== $infinity23_notes_page_post_id ) {
                    $infinity23_note_row_id = 0;
                } // if( empty( $infinity23_note_existing_row ) || strval( $infinity23_note_existing_row->page_post_id ) !== $infinity23_notes_page_post_id ) {

            } // if( !empty( $infinity23_note_row_id ) ) {

            // empty text - remove the row if it already existed, otherwise skip it
            if( empty( trim( $infinity23_note_text ) ) ) {

                if( !empty( $infinity23_note_row_id ) ) {
                    infinity23_notes_delete_row( $infinity23_note_row_id );
                } // if( !empty( $infinity23_note_row_id ) ) {

                continue;

            } // if( empty( trim( $infinity23_note_text ) ) ) {

            $infinity23_notes_row_data = array(
                'page_post_id' => $infinity23_notes_page_post_id,
                'note_text'    => $infinity23_note_text,
                'direction'    => $infinity23_note_direction,
                'position'     => $infinity23_notes_index + 1,
            );

            if( !empty( $infinity23_note_row_id ) ) {

                infinity23_notes_update_row( $infinity23_note_row_id, $infinity23_notes_row_data );
                $infinity23_notes_kept_row_ids[] = $infinity23_note_row_id;

            } else {

                $infinity23_notes_kept_row_ids[] = infinity23_notes_add_row( $infinity23_notes_row_data );

            } // if( !empty( $infinity23_note_row_id ) ) {

        } // foreach( $infinity23_notes_raw as $infinity23_notes_index => $infinity23_note_text ) {

    } // if( is_array( $infinity23_notes_raw ) ) {

    // ---------------------------------------
    // remove any existing rows that were not posted back (deleted on the client side)
    // ---------------------------------------

    $infinity23_notes_existing_rows = infinity23_notes_get_rows_by_page( $infinity23_notes_page_post_id );

    if( !empty( $infinity23_notes_existing_rows ) ) {

        foreach( $infinity23_notes_existing_rows as $infinity23_notes_existing_row ) {

            if( !in_array( intval( $infinity23_notes_existing_row->id ), $infinity23_notes_kept_row_ids ) ) {
                infinity23_notes_delete_row( $infinity23_notes_existing_row->id );
            } // if( !in_array(

        } // foreach( $infinity23_notes_existing_rows as $infinity23_notes_existing_row ) {

    } // if( !empty( $infinity23_notes_existing_rows ) ) {

?>
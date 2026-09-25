<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Notes meta box and dashboard widget output
    // ===========================================================================

    include( dirname(__FILE__) . "/../../../include/settings.php" );

    // ---------------------------------------
    // detect context - dashboard or post/page
    // ---------------------------------------

    $infinity23_notes_is_dashboard = isset( $infinity23_notes_is_dashboard ) ? $infinity23_notes_is_dashboard : false;

    if( $infinity23_notes_is_dashboard ) {

        $infinity23_notes_context_id       = 'dashboard';
        $infinity23_notes_page_post_id_val = 'dashboard';

    } else {

        if( isset( $infinity23_notes_post_object ) && is_object( $infinity23_notes_post_object ) && isset( $infinity23_notes_post_object->ID ) ) {
            $infinity23_notes_context_id = intval( $infinity23_notes_post_object->ID );
        } elseif( isset( $infinity23_notes_forced_context_id ) && $infinity23_notes_forced_context_id !== 'dashboard' ) {
            $infinity23_notes_context_id = intval( $infinity23_notes_forced_context_id );
        } else {
            $infinity23_notes_context_id = intval( get_the_ID() );
        } // if( isset( $infinity23_notes_post_object ) ...
        $infinity23_notes_page_post_id_val = strval( $infinity23_notes_context_id );

    } // if( $infinity23_notes_is_dashboard ) {

    // ---------------------------------------
    // load saved notes from the dedicated notes database table
    // ---------------------------------------

    $infinity23_notes_saved_rows = infinity23_notes_get_rows_by_page( $infinity23_notes_page_post_id_val );

    $infinity23_saved_notes = array();

    if( !empty( $infinity23_notes_saved_rows ) ) {

        foreach( $infinity23_notes_saved_rows as $infinity23_notes_saved_row ) {

            $infinity23_saved_notes[] = array(
                'row_id'    => intval( $infinity23_notes_saved_row->id ),
                'text'      => $infinity23_notes_saved_row->note_text,
                'direction' => $infinity23_notes_saved_row->direction,
                'created'   => $infinity23_notes_saved_row->time_created_utc,
            );

        } // foreach( $infinity23_notes_saved_rows as $infinity23_notes_saved_row ) {

    } // if( !empty( $infinity23_notes_saved_rows ) ) {

    // ---------------------------------------
    // default direction from theme
    // ---------------------------------------

    $infinity23_notes_default_direction = is_rtl() ? 'direction-rtl' : 'direction-ltr';

    // ---------------------------------------
    // default add button state - closed unless notes already exist
    // ---------------------------------------

    $infinity23_notes_button_text  = '+ Add Note';
    $infinity23_notes_button_class = '';
    $infinity23_notes_hide_class   = 'infinity23-note-not-active';

    if( !empty( $infinity23_saved_notes ) ) {
        $infinity23_notes_button_text  = '+ Add Another Note';
        $infinity23_notes_button_class = 'infinity23-notes-opened';
        $infinity23_notes_hide_class   = '';
    } // if( !empty( $infinity23_saved_notes ) ) {

    // ---------------------------------------
    // load settings to check allow_multiple_notes
    // ---------------------------------------

    include( dirname(__FILE__) . "/../default-values.php" );
    include( dirname(__FILE__) . "/../internal-settings.php" );
    $infinity23_notes_settings_raw = infinity23_get_option( $infinity23_settings_array_db_name );
    $infinity23_notes_settings     = infinity23_convert_json_to_array( $infinity23_notes_settings_raw );
    $infinity23_allow_multiple     = intval( infinity23_pro_get_data( 'allow_multiple_notes', $infinity23_notes_settings, $infinity23_defaults ) );

    // ---------------------------------------
    // adjustable textarea height - controls whether the note box grows to
    // fit its content, same pattern as $infinity23_tagrow_textarea_class in
    // Tag Manager's admin-output.php
    // ---------------------------------------

    $infinity23_notes_adjustable_textarea_height = intval( infinity23_pro_get_data( 'adjustable_textarea_height', $infinity23_notes_settings, $infinity23_defaults ) );
    $infinity23_notes_dashboard_adjustable_textarea_height = intval( infinity23_pro_get_data( 'dashboard_adjustable_textarea_height', $infinity23_notes_settings, $infinity23_defaults ) );

    $infinity23_notes_textarea_class = '';

    if( $infinity23_notes_is_dashboard ) {

        if( $infinity23_notes_dashboard_adjustable_textarea_height == 1 ) {
            $infinity23_notes_textarea_class = 'infinity23-textarea-adjustable';
        } // if( $infinity23_notes_dashboard_adjustable_textarea_height == 1 ) {

    } else {

        if( $infinity23_notes_adjustable_textarea_height == 1 ) {
            $infinity23_notes_textarea_class = 'infinity23-textarea-adjustable';
        } // if( $infinity23_notes_adjustable_textarea_height == 1 ) {

    } // if( $infinity23_notes_is_dashboard ) {

    // ---------------------------------------
    // when multiple notes are off there is no add button
    // so the note box must always stay visible
    // ---------------------------------------

    if( $infinity23_allow_multiple != 1 ) {
        $infinity23_notes_hide_class = '';
    } // if( $infinity23_allow_multiple != 1 ) {

    // ---------------------------------------
    // image paths
    // ---------------------------------------

    $infinity23_notes_img_remove    = infinity23_plugin_url( 'images/plugin-icons/remove-icon.svg' );
    $infinity23_notes_img_direction = infinity23_plugin_url( 'images/plugin-icons/text-direction.svg' );
    $infinity23_notes_img_separator = infinity23_plugin_url( 'images/plugin-icons/separator-icon.svg' );

?>

<div class="infinity23-notes-wrap">

    <?php wp_nonce_field( 'infinity23_notes_action_' . $infinity23_notes_page_post_id_val, 'infinity23_notes_box_nonce' ); ?>
    <input type="hidden" class="infinity23-notes-page-id" name="infinity23_notes_page_id" value="<?php echo esc_attr( $infinity23_notes_page_post_id_val ); ?>" />

    <div class="infinity23-notes-items">

    <?php

    if( !empty( $infinity23_saved_notes ) ) {

        foreach( $infinity23_saved_notes as $infinity23_note ) {

            $infinity23_note_row_id    = isset( $infinity23_note['row_id'] ) ? intval( $infinity23_note['row_id'] ) : 0;
            $infinity23_note_text      = isset( $infinity23_note['text'] ) ? $infinity23_note['text'] : '';
            $infinity23_note_direction = isset( $infinity23_note['direction'] ) ? $infinity23_note['direction'] : $infinity23_notes_default_direction;
            global $infinity23_notes_show_note_dates_global;

            $infinity23_note_created   = isset( $infinity23_note['created'] ) ? intval( $infinity23_note['created'] ) : 0;
            $infinity23_note_created_label = ( !empty( $infinity23_notes_show_note_dates_global ) && !empty( $infinity23_note_created ) ) ? infinity23_format_timestamp( $infinity23_note_created, 'd/m/Y H:i' ) : '';

            ?>
            <div class="infinity23-note-item">
                <?php if( !empty( $infinity23_note_created_label ) ) { ?>
                <span class="infinity23-note-created"><?php echo esc_html( $infinity23_note_created_label ); ?></span>
                <?php } // if( !empty( $infinity23_note_created_label ) ) { ?>
                <textarea class="<?php echo esc_attr( trim( $infinity23_note_direction . ' ' . $infinity23_notes_textarea_class ) ); ?>" name="infinity23_notes_data[]"><?php echo esc_textarea( $infinity23_note_text ); ?></textarea>
                <input type="hidden" class="infinity23-note-direction-input" name="infinity23_notes_directions[]" value="<?php echo esc_attr( $infinity23_note_direction ); ?>" />
                <input type="hidden" class="infinity23-note-row-id-input" name="infinity23_notes_row_ids[]" value="<?php echo esc_attr( $infinity23_note_row_id ); ?>" />
                <a href="#" class="infinity23-note-remove"><img src="<?php echo esc_url( $infinity23_notes_img_remove ); ?>" alt="Remove" title="Remove Note" /></a>
                <a href="#" class="infinity23-note-direction"><img src="<?php echo esc_url( $infinity23_notes_img_direction ); ?>" alt="Direction" title="Toggle Text Direction" /></a>
                <a href="#" class="infinity23-note-separator"><img src="<?php echo esc_url( $infinity23_notes_img_separator ); ?>" alt="Separator" title="Add Separator" /></a>
            </div><!--infinity23-note-item-->
            <?php

        } // foreach( $infinity23_saved_notes as $infinity23_note ) {

    } else {

        ?>
        <div class="infinity23-note-item <?php echo esc_attr( $infinity23_notes_hide_class ); ?>">
            <textarea class="<?php echo esc_attr( trim( $infinity23_notes_default_direction . ' ' . $infinity23_notes_textarea_class ) ); ?>" name="infinity23_notes_data[]"></textarea>
            <input type="hidden" class="infinity23-note-direction-input" name="infinity23_notes_directions[]" value="<?php echo esc_attr( $infinity23_notes_default_direction ); ?>" />
            <input type="hidden" class="infinity23-note-row-id-input" name="infinity23_notes_row_ids[]" value="0" />
            <a href="#" class="infinity23-note-remove"><img src="<?php echo esc_url( $infinity23_notes_img_remove ); ?>" alt="Remove" title="Remove Note" /></a>
            <a href="#" class="infinity23-note-direction"><img src="<?php echo esc_url( $infinity23_notes_img_direction ); ?>" alt="Direction" title="Toggle Text Direction" /></a>
            <a href="#" class="infinity23-note-separator"><img src="<?php echo esc_url( $infinity23_notes_img_separator ); ?>" alt="Separator" title="Add Separator" /></a>
        </div><!--infinity23-note-item-->
        <?php

    } // if( !empty( $infinity23_saved_notes ) ) {

    ?>

    </div><!--infinity23-notes-items-->

    <?php if( $infinity23_allow_multiple == 1 ) { ?>
        <a href="#" class="direction-ltr infinity23-notes-add-btn <?php echo esc_attr( $infinity23_notes_button_class ); ?>"><?php echo esc_html( $infinity23_notes_button_text ); ?></a>
    <?php } // if( $infinity23_allow_multiple == 1 ) { ?>

    <a href="#" class="infinity23-notes-save-btn <?php if( $infinity23_notes_is_dashboard ) { echo 'infinity23-notes-dashboard-save'; } ?>">Save Changes</a>
    <div class="infinity23-notes-ajax-msg"></div>

</div><!--infinity23-notes-wrap-->
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ======================================================================================================================================================
// This page holds functions that output shared code and UI onto admin forms
// ======================================================================================================================================================

// ===========================================================================
// Toggle Table - shared field output (global)
// ===========================================================================

    // --------------------------------------------
    // output a reusable toggle table: an optional icon column, an on/off
    // toggle column, a name column and an optional extra text column. Used
    // by Update Manager (plugins/themes) and Folders Organizer (custom post
    // types) so the table markup, CSS and JS toggle behavior only ever need
    // to change in one place. $rows is an array of associative arrays, each
    // with 'value', 'label', 'checked' and optionally 'icon_html' and 'extra'.
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_toggle_table_rows_data' ) ) {

        function infinity23_get_toggle_table_rows_data( $checkbox_name, $rows, $disabled = false ) {

            $rows_data = array();

            foreach( $rows as $row ) {

                $is_checked = !empty( $row['checked'] );

                $checkbox_html = '<label class="infinity23-fb-toggle-label infinity23-fb-toggle-labeled"><input type="checkbox" class="infinity23-fb-checkbox" name="' . esc_attr( $checkbox_name ) . '[]" value="' . esc_attr( $row['value'] ) . '" ' . ( $is_checked ? 'checked' : '' ) . ' ' . ( $disabled ? 'disabled' : '' ) . ' /><span class="infinity23-fb-toggle-circle"></span></label>';

                $rows_data[] = array(
                    'row_class'     => $is_checked ? '' : 'fb-row-blocked',
                    'icon_html'     => !empty( $row['icon_html'] ) ? $row['icon_html'] : '<span class="infinity23-icon-placeholder"></span>',
                    'checkbox_html' => $checkbox_html,
                    'label'         => $row['label'],
                    'extra'         => isset( $row['extra'] ) ? $row['extra'] : '',
                    'value'         => $row['value'],
                );

            } // foreach( $rows as $row ) {

            return $rows_data;

        } // function infinity23_get_toggle_table_rows_data( $checkbox_name, $rows, $disabled = false ) {

    } // if( !function_exists( 'infinity23_get_toggle_table_rows_data' ) ) {

    // --------------------------------------------
    // output the shared "Custom Post Types" toggle table block - intro
    // paragraph, toggle table and empty state. The module's own h3 stays
    // in the calling file so this can nest inside an existing wrap.
    // --------------------------------------------

    if( !function_exists( 'infinity23_custom_post_types_toggle_table_block' ) ) {

        function infinity23_custom_post_types_toggle_table_block( $checkbox_name, $rows, $intro_text ) {

            if( empty( $rows ) ) {

                echo '<p>No custom post types were found on this site.</p>';
                return;

            } // if( empty( $rows ) ) {

            ?>
            <p><?php echo esc_html( $intro_text ); ?></p>
            <?php

            $rows_data = infinity23_get_toggle_table_rows_data( $checkbox_name, $rows );

            $table = '';
            $table .= '<table class="infinity23-force-block-table infinity23-cpt-toggle-table">';
                $table .= '<thead>';
                    $table .= '<tr>';
                        $table .= '<th class="fb-col-block">Active</th>';
                        $table .= '<th class="fb-col-name">Name</th>';
                    $table .= '</tr>';
                $table .= '</thead>';
                $table .= '<tbody>';

            foreach( $rows_data as $row_data ) {

                $table .= '<tr class="infinity23-fb-row ' . esc_attr( $row_data['row_class'] ) . '">';
                    $table .= '<td class="fb-col-block">' . $row_data['checkbox_html'] . '</td>';
                    $table .= '<td class="fb-col-name">' . esc_html( $row_data['label'] ) . '</td>';
                $table .= '</tr>';

            } // foreach( $rows_data as $row_data ) {

                $table .= '</tbody>';
            $table .= '</table>';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values via infinity23_get_toggle_table_rows_data()
            echo $table;

        } // function infinity23_custom_post_types_toggle_table_block( $checkbox_name, $rows, $intro_text ) {

    } // if( !function_exists( 'infinity23_custom_post_types_toggle_table_block' ) ) {

    // --------------------------------------------
    // output the FULL "Custom Post Types:" section - h3 title (always
    // visible), the enable checkbox, and the toggle-hidden intro+table
    // block. Every module that lets the admin turn a feature on/off per
    // custom post type MUST call this instead of building the h3/checkbox/
    // wrap markup itself, so the structure can never drift between modules
    // again. $checkbox_id doubles as the field name and the toggle-fields
    // data-toggle-input target.
    // --------------------------------------------

    if( !function_exists( 'infinity23_custom_post_types_section' ) ) {

        function infinity23_custom_post_types_section( $checkbox_id, $checkbox_label, $checkbox_checked, $table_checkbox_name, $rows, $intro_text ) {

            ?>
            <h3>Custom Post Types:</h3>

            <div class="form-line checkbox-line">
                <input type="checkbox" id="<?php echo esc_attr( $checkbox_id ); ?>" name="<?php echo esc_attr( $checkbox_id ); ?>" value="1" <?php if( $checkbox_checked == 1 ) { echo 'checked'; } ?> />
                <label for="<?php echo esc_attr( $checkbox_id ); ?>"><?php echo esc_html( $checkbox_label ); ?></label>
            </div><!--form-line-->

            <div class="infinity23-toggle-fields" data-toggle-input="<?php echo esc_attr( $checkbox_id ); ?>">

                <?php infinity23_custom_post_types_toggle_table_block( $table_checkbox_name, $rows, $intro_text ); ?>

            </div><!--infinity23-toggle-fields-->
            <?php

        } // function infinity23_custom_post_types_section( $checkbox_id, $checkbox_label, $checkbox_checked, $table_checkbox_name, $rows, $intro_text ) {

    } // if( !function_exists( 'infinity23_custom_post_types_section' ) ) {

// ===========================================================================
// Exclude Pages By ID - shared field output
// ===========================================================================

    // --------------------------------------------
    // output the full "Exclude Pages By ID" field - description text, hidden
    // input and repeating id rows. Used by every module so the wording and
    // markup only ever need to change in one place.
    // --------------------------------------------

    // --------------------------------------------
    // check if the Show Pages IDs module is included via Include / Exclude
    // this module has no activation toggle of its own - it is only ever
    // on or off through the include_exclude_plugins option
    // --------------------------------------------

    if( !function_exists( 'infinity23_show_pages_ids_is_active' ) ) {

        function infinity23_show_pages_ids_is_active() {

            $ie_data = infinity23_convert_json_to_array( infinity23_get_option( 'include_exclude_plugins' ) );
            $ie_active = ( intval( infinity23_get_data( 'show_pages_ids', $ie_data, array( 'show_pages_ids' => 1 ) ) ) == 1 );

            $infinity23_activate_code_active = ( intval( infinity23_get_option( 'show_pages_ids_activate_code', 0 ) ) == 1 );

            return ( $ie_active && $infinity23_activate_code_active );

        } // function infinity23_show_pages_ids_is_active() {

    } // if( !function_exists( 'infinity23_show_pages_ids_is_active' ) ) {

    if( !function_exists( 'infinity23_exclude_ids_field' ) ) {

        function infinity23_exclude_ids_field( $infinity23_exclude_ids, $infinity23_exclude_option = 'exclude', $field_id = 'exclude_ids' ) {

            $show_pages_ids_active = infinity23_show_pages_ids_is_active();

            ?>
            <div class="form-line">
                <label for="<?php echo esc_attr( $field_id ); ?>_option">Include/Exclude Option: </label>
                <select name="<?php echo esc_attr( $field_id ); ?>_option" id="<?php echo esc_attr( $field_id ); ?>_option" class="infinity23-exclude-option-select" data-toggle-target="<?php echo esc_attr( $field_id ); ?>_rows_wrap">
                    <option value="exclude" <?php if( $infinity23_exclude_option === 'exclude' ) { echo 'selected'; } ?>>Exclude Pages By ID</option>
                    <option value="include" <?php if( $infinity23_exclude_option === 'include' ) { echo 'selected'; } ?>>Include Only On Pages</option>
                    <option value="none" <?php if( $infinity23_exclude_option === 'none' ) { echo 'selected'; } ?>>Not Active</option>
                </select>
            </div><!--form-line-->

            <div id="<?php echo esc_attr( $field_id ); ?>_rows_wrap" class="infinity23-exclude-rows-wrap <?php if( $infinity23_exclude_option === 'none' ) { echo 'infinity23-exclude-hidden'; } ?>">

            <p class="exclude-ids-lable">
                Enter the ID of each page you want to exclude below.

                <span class="backtest-field-help-icon">
                    <span class="data-help-content">
                        Every page on your site has its own unique ID number. Enter that ID below to exclude/include that specific page from this feature.
                        <br /><br />
                        <?php if( $show_pages_ids_active ) { ?>
                            The <strong>Show Pages IDs</strong> module is active, so the ID of the page you're currently viewing is shown in the admin bar at the top of the screen, and also appears as a column in your Pages/Posts lists.
                        <?php } else { ?>
                            This plugin includes a <strong>Show Pages IDs</strong> module that displays the ID of the page you're viewing right in the admin bar, so you don't have to go looking for it. <br /><br /> The addon isn't active yet - turn it on from the <a href="<?php echo esc_url( admin_url( 'admin.php?page=essential-include-exclude' ) ); ?>" target="_blank">Include / Exclude</a> page, and also make sure it's active on the addon page.
                        <?php } // if( $show_pages_ids_active ) { ?>
                    </span>
                    ?
                </span><!--backtest-field-help-icon-->
            </p>

            <input type="hidden" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $infinity23_exclude_ids ); ?>" />

            <div id="infinity23-exclude-ids-wrap" class="infinity23-exclude-ids-wrap" data-input="<?php echo esc_attr( $field_id ); ?>">
                <?php

                $infinity23_exclude_ids_list = array();

                if( !empty( $infinity23_exclude_ids ) ) {
                    foreach( explode( ',', $infinity23_exclude_ids ) as $eid ) {
                        $eid = intval( trim( $eid ) );
                        if( !empty( $eid ) ) {
                            $infinity23_exclude_ids_list[] = $eid;
                        } // if( !empty( $eid ) ) {
                    } // foreach
                } // if( !empty( $infinity23_exclude_ids ) ) {

                if( !empty( $infinity23_exclude_ids_list ) ) {
                    foreach( $infinity23_exclude_ids_list as $eid ) {

                        $infinity23_exclude_id_post = get_post( $eid );
                        $infinity23_exclude_id_title_text = 'Page not found';
                        $infinity23_exclude_id_title_class = 'title-error';

                        if( !empty( $infinity23_exclude_id_post ) ) {
                            $infinity23_exclude_id_title_text = $infinity23_exclude_id_post->post_title;
                            $infinity23_exclude_id_title_class = '';
                        } // if( !empty( $infinity23_exclude_id_post ) ) {

                        ?>
                        <div class="infinity23-exclude-id-row">
                            <button type="button" class="infinity23-exclude-id-remove"><span class="infinity23-remove-icon"></span></button>
                            <input type="text" class="infinity23-exclude-id-input very-short-input" value="<?php echo esc_attr( $eid ); ?>" placeholder="Page ID" />
                            <span class="infinity23-exclude-id-title <?php echo esc_attr( $infinity23_exclude_id_title_class ); ?>"><?php echo esc_html( $infinity23_exclude_id_title_text ); ?></span>
                        </div><!--infinity23-exclude-id-row-->
                    <?php } // foreach
                } else { ?>
                    <div class="infinity23-exclude-id-row">
                        <button type="button" class="infinity23-exclude-id-remove"><span class="infinity23-remove-icon"></span></button>
                        <input type="text" class="infinity23-exclude-id-input very-short-input" value="" placeholder="Page ID" />
                        <span class="infinity23-exclude-id-title"></span>
                    </div><!--infinity23-exclude-id-row-->
                <?php } // if( !empty( $infinity23_exclude_ids_list ) ) {
                ?>
            </div><!--infinity23-exclude-ids-wrap-->

            <br />
            <button type="button" class="infinity23-exclude-id-add" data-target="infinity23-exclude-ids-wrap">+ Exclude Another Page ID</button>

            </div><!--infinity23-exclude-rows-wrap-->
            <?php

        } // function infinity23_exclude_ids_field( $infinity23_exclude_ids, $field_id = 'exclude_ids' ) {

    } // if( !function_exists( 'infinity23_exclude_ids_field' ) ) {

// ===========================================================================
// Unit Select Field - shared field output (global)
// ===========================================================================

    // --------------------------------------------
    // output a single unit <select> (px/%/em/rem) with one shared CSS class,
    // used everywhere a size or spacing field lets the user pick a unit -
    // border widths, border radius, button position spacing, privacy policy
    // bar spacing/width. $field_id is the full id/name to use (caller already
    // includes any "_unit" suffix), $selected_unit is the currently saved value.
    // --------------------------------------------

    if( !function_exists( 'infinity23_unit_select_field' ) ) {

        function infinity23_unit_select_field( $field_id, $selected_unit ) {

            $units = array( 'px', '%', 'em', 'rem' );

            $output = '';
            $output .= '<select id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_id ) . '" class="infinity23-unit-select">';

            foreach( $units as $unit_value ) {
                $output .= '<option value="' . esc_attr( $unit_value ) . '" ' . ( $selected_unit === $unit_value ? 'selected' : '' ) . '>' . esc_html( $unit_value ) . '</option>';
            } // foreach( $units as $unit_value ) {

            $output .= '</select>';

            return $output;

        } // function infinity23_unit_select_field( $field_id, $selected_unit ) {

    } // if( !function_exists( 'infinity23_unit_select_field' ) ) {

// ===========================================================================
// Border Settings - shared field output
// ===========================================================================

    // --------------------------------------------
    // output the border radius field with its help icon. Used by every
    // module so the wording only ever needs to change in one place. Does
    // NOT print the "Border Settings:" h3 - that stays in the calling file.
    // --------------------------------------------

    if( !function_exists( 'infinity23_border_radius_field' ) ) {

        function infinity23_border_radius_field( $infinity23_border_radius, $label = 'Border Radius:' ) {

            ?>
            <div class="form-line">
                <label for="border_radius"><?php echo esc_html( $label ); ?> </label>
                <input type="text" id="border_radius" class='short-input' name="border_radius" value="<?php echo esc_attr( $infinity23_border_radius ); ?>" />

                <div class="backtest-field-help-icon">
                    <div class="data-help-content">
                        When this is turned on, the page will be hidden from the Essential tab on the WordPress sidebar menu.
                        <br /><br />
                        The page keeps working even when hidden - you can still open it directly, or reach it anytime from the Include / Exclude page.
                        <br /><br />
                        This is useful for keeping the Essential menu clean, showing only the settings pages you actually use often.
                        <br /><br />
                        Requires a page refresh after saving to see the change.
                    </div>
                    ?
                </div><!--backtest-field-help-icon-->
            </div><!--form-line-->
            <?php

        } // function infinity23_border_radius_field( $infinity23_border_radius, $label = 'Border Radius:' ) {

    } // if( !function_exists( 'infinity23_border_radius_field' ) ) {

    // --------------------------------------------
    // output the full advanced border-radius builder - a unit select, a
    // "Link all corners" icon and a width input per corner. Stored as one
    // JSON string in the same field the old infinity23_border_radius_field()
    // used to save, so infinity23_parse_border_radius_to_struct() in
    // main-functions.php can read both old plain css strings and this new
    // JSON format.
    // --------------------------------------------

    if( !function_exists( 'infinity23_advanced_border_radius_field' ) ) {

        function infinity23_advanced_border_radius_field( $raw_border_radius_value, $field_id = 'border_radius', $label = 'Round Corners (Border Radius):' ) {

            $radius_struct = infinity23_parse_border_radius_to_struct( $raw_border_radius_value );
            $link_active_class = !empty( $radius_struct['linked'] ) ? 'active' : '';

            ?>
            <div class="infinity23-border-details-wrap" id="infinity23-radius-details-<?php echo esc_attr( $field_id ); ?>">

                <?php if( !empty( $label ) ) { ?>
                    <label><?php echo esc_html( $label ); ?></label>
                <?php } // if( !empty( $label ) ) { ?>

                <div class="infinity23-border-widths-wrap">

                    <button type="button" class="infinity23-radius-link-btn <?php echo esc_attr( $link_active_class ); ?>" title="Link all corners together"><span class="infinity23-border-link-icon"></span></button>

                    <span class="infinity23-border-width-item">
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_top_left" class="infinity23-radius-corner-top-left" name="<?php echo esc_attr( $field_id ); ?>_top_left" value="<?php echo esc_attr( $radius_struct['top_left'] ); ?>" />
                        <label for="<?php echo esc_attr( $field_id ); ?>_top_left">Top Left</label>
                    </span>

                    <span class="infinity23-border-width-item">
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_top_right" class="infinity23-radius-corner-top-right" name="<?php echo esc_attr( $field_id ); ?>_top_right" value="<?php echo esc_attr( $radius_struct['top_right'] ); ?>" />
                        <label for="<?php echo esc_attr( $field_id ); ?>_top_right">Top Right</label>
                    </span>

                    <span class="infinity23-border-width-item">
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_bottom_right" class="infinity23-radius-corner-bottom-right" name="<?php echo esc_attr( $field_id ); ?>_bottom_right" value="<?php echo esc_attr( $radius_struct['bottom_right'] ); ?>" />
                        <label for="<?php echo esc_attr( $field_id ); ?>_bottom_right">Bottom Right</label>
                    </span>

                    <span class="infinity23-border-width-item">
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_bottom_left" class="infinity23-radius-corner-bottom-left" name="<?php echo esc_attr( $field_id ); ?>_bottom_left" value="<?php echo esc_attr( $radius_struct['bottom_left'] ); ?>" />
                        <label for="<?php echo esc_attr( $field_id ); ?>_bottom_left">Bottom Left</label>
                    </span>

                    <span class="infinity23-border-width-item infinity23-border-unit-item infinity23-border-unit-item-last">
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
                        <?php echo infinity23_unit_select_field( $field_id . '_unit', $radius_struct['unit'] ); ?>
                        <label for="<?php echo esc_attr( $field_id ); ?>_unit">Unit</label>
                    </span>

                </div><!--infinity23-border-widths-wrap-->

                <input type="hidden" name="<?php echo esc_attr( $field_id ); ?>_linked" class="infinity23-radius-linked-input" value="<?php echo !empty( $radius_struct['linked'] ) ? '1' : '0'; ?>" />

            </div><!--infinity23-border-details-wrap-->
            <?php

        } // function infinity23_advanced_border_radius_field( $raw_border_radius_value, $field_id = 'border_radius', $label = 'Round Corners:' ) {

    } // if( !function_exists( 'infinity23_advanced_border_radius_field' ) ) {

    // --------------------------------------------
    // output the border field with its help icon. Used by every module so
    // the wording only ever needs to change in one place. Does NOT print
    // the "Border Settings:" h3 - that stays in the calling file.
    // --------------------------------------------

    if( !function_exists( 'infinity23_border_field' ) ) {

        function infinity23_border_field( $infinity23_button_border, $label = 'Border:' ) {

            ?>
            <div class="form-line">
                <label for="button_border"><?php echo esc_html( $label ); ?> </label>
                <input type="text" id="button_border" class='medium-input' name="button_border" value="<?php echo esc_attr( $infinity23_button_border ); ?>" />

                <div class="backtest-field-help-icon">
                    <div class="data-help-content">
                        Border adds a visible outline around the button. The format is: thickness style color.
                        <br /><br />
                        <strong>Usage Examples:</strong>
                        <ul>
                            <li>Solid white border - <span class="code">2px solid #fff</span></li>
                            <li>Dashed black border - <span class="code">3px dashed #000</span></li>
                            <li>No border - <span class="code">none</span></li>
                        </ul>
                    </div>
                    ?
                </div><!--backtest-field-help-icon-->
            </div><!--form-line-->
            <?php

        } // function infinity23_border_field( $infinity23_button_border, $label = 'Border:' ) {

    } // if( !function_exists( 'infinity23_border_field' ) ) {

// ===========================================================================
// Advanced Border Field - shared field output (style + per-side width/unit/color)
// ===========================================================================

    // --------------------------------------------
    // output the full advanced border builder - border type, color, unit and
    // a width per side (top/right/bottom/left). Stored as one JSON string in
    // the same field the old infinity23_border_field() used to save, so
    // infinity23_parse_border_to_struct() in main-functions.php can read both
    // old plain css border strings and this new JSON format.
    // --------------------------------------------

    if( !function_exists( 'infinity23_advanced_border_field' ) ) {

        function infinity23_advanced_border_field( $raw_border_value, $field_id = 'button_border', $label = 'Border:' ) {

            $border_struct = infinity23_parse_border_to_struct( $raw_border_value );

            $allowed_styles = array(
                'none' => 'None',
                'solid' => 'Solid',
                'dashed' => 'Dashed',
                'dotted' => 'Dotted',
                'double' => 'Double',
                'groove' => 'Groove',
                'ridge' => 'Ridge',
            );

            // PHP decides the hidden state up front, so the wrap never flashes
            // visible on load waiting for JS to hide it - JS only toggles this
            // class afterwards, on user interaction.
            $wrap_hidden_class = ( $border_struct['style'] === 'none' ) ? 'infinity23-border-hidden' : '';
            $link_active_class = !empty( $border_struct['linked'] ) ? 'active' : '';

            ?>
            <div class="form-line infinity23-border-type-line">
                <label for="<?php echo esc_attr( $field_id ); ?>_style"><?php echo esc_html( $label ); ?> </label>
                <select id="<?php echo esc_attr( $field_id ); ?>_style" name="<?php echo esc_attr( $field_id ); ?>_style" class="infinity23-border-style-select short-input" data-target="infinity23-border-details-<?php echo esc_attr( $field_id ); ?>">
                    <?php foreach( $allowed_styles as $style_value => $style_label ) { ?>
                        <option value="<?php echo esc_attr( $style_value ); ?>" <?php if( $border_struct['style'] === $style_value ) { echo 'selected'; } ?>><?php echo esc_html( $style_label ); ?></option>
                    <?php } // foreach( $allowed_styles as $style_value => $style_label ) { ?>
                </select>

                <div class="backtest-field-help-icon">
                    <div class="data-help-content">
                        Choose the border style, then set its color and the width of each side below.
                        <br /><br />
                        Set the type to <strong>None</strong> to remove the border completely.
                    </div>
                    ?
                </div><!--backtest-field-help-icon-->
            </div><!--form-line-->

            <div class="infinity23-border-details-wrap <?php echo esc_attr( $wrap_hidden_class ); ?>" id="infinity23-border-details-<?php echo esc_attr( $field_id ); ?>">

                <div class="form-line background-color-line infinity23-border-color-line">
                    <label for="<?php echo esc_attr( $field_id ); ?>_color">Color: </label>
                    <input type="text" id="<?php echo esc_attr( $field_id ); ?>_color" name="<?php echo esc_attr( $field_id ); ?>_color" class="infinity23-color-picker" value="<?php echo esc_attr( $border_struct['color'] ); ?>" />
                </div><!--form-line-->

                <div class="infinity23-border-widths-wrap">

                    <button type="button" class="infinity23-border-link-btn <?php echo esc_attr( $link_active_class ); ?>" title="Link all sides together"><span class="infinity23-border-link-icon"></span></button>

                    <span class="infinity23-border-width-item">
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_top" class="infinity23-border-side-top" name="<?php echo esc_attr( $field_id ); ?>_top" value="<?php echo esc_attr( $border_struct['top'] ); ?>" />
                        <label for="<?php echo esc_attr( $field_id ); ?>_top">Top</label>
                    </span>

                    <span class="infinity23-border-width-item">
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_right" class="infinity23-border-side-right" name="<?php echo esc_attr( $field_id ); ?>_right" value="<?php echo esc_attr( $border_struct['right'] ); ?>" />
                        <label for="<?php echo esc_attr( $field_id ); ?>_right">Right</label>
                    </span>

                    <span class="infinity23-border-width-item">
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_bottom" class="infinity23-border-side-bottom" name="<?php echo esc_attr( $field_id ); ?>_bottom" value="<?php echo esc_attr( $border_struct['bottom'] ); ?>" />
                        <label for="<?php echo esc_attr( $field_id ); ?>_bottom">Bottom</label>
                    </span>

                    <span class="infinity23-border-width-item">
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_left" class="infinity23-border-side-left" name="<?php echo esc_attr( $field_id ); ?>_left" value="<?php echo esc_attr( $border_struct['left'] ); ?>" />
                        <label for="<?php echo esc_attr( $field_id ); ?>_left">Left</label>
                    </span>

                    <span class="infinity23-border-width-item infinity23-border-unit-item infinity23-border-unit-item-last">
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
                        <?php echo infinity23_unit_select_field( $field_id . '_unit', $border_struct['unit'] ); ?>
                        <label for="<?php echo esc_attr( $field_id ); ?>_unit">Unit</label>
                    </span>

                </div><!--infinity23-border-widths-wrap-->

                <input type="hidden" name="<?php echo esc_attr( $field_id ); ?>_linked" class="infinity23-border-linked-input" value="<?php echo !empty( $border_struct['linked'] ) ? '1' : '0'; ?>" />

            </div><!--infinity23-border-details-wrap-->
            <?php

        } // function infinity23_advanced_border_field( $raw_border_value, $field_id = 'button_border', $label = 'Border:' ) {

    } // if( !function_exists( 'infinity23_advanced_border_field' ) ) {

// ===========================================================================
// Background Field - shared field output (solid/gradient/custom css/none)
// ===========================================================================

    // --------------------------------------------
    // output the full background builder - type select plus the matching
    // sub-fields (solid color / gradient two colors + angle / custom css
    // value). Saved as one JSON string. Visibility toggling is handled by
    // the global infinity23-background-type-select JS in scripts.js.
    // --------------------------------------------

    if( !function_exists( 'infinity23_advanced_background_field' ) ) {

        function infinity23_advanced_background_field( $raw_background_value, $field_id = 'background', $label = 'Background Style:' ) {

            $bg_struct = infinity23_parse_background_to_struct( $raw_background_value );

            ?>
            <div class="form-line">
                <label for="<?php echo esc_attr( $field_id ); ?>_type"><?php echo esc_html( $label ); ?> </label>
                <select id="<?php echo esc_attr( $field_id ); ?>_type" name="<?php echo esc_attr( $field_id ); ?>_type" class="infinity23-background-type-select" data-target="infinity23-background-details-<?php echo esc_attr( $field_id ); ?>">
                    <option value="solid" <?php if( $bg_struct['type'] === 'solid' ) { echo 'selected'; } ?>>Solid Color</option>
                    <option value="gradient" <?php if( $bg_struct['type'] === 'gradient' ) { echo 'selected'; } ?>>Linear Gradient</option>
                    <option value="custom_css" <?php if( $bg_struct['type'] === 'custom_css' ) { echo 'selected'; } ?>>Custom Background CSS</option>
                    <option value="none" <?php if( $bg_struct['type'] === 'none' ) { echo 'selected'; } ?>>None (Transparent)</option>
                </select>

                <div class="backtest-field-help-icon">
                    <div class="data-help-content">
                        <strong>Solid Color</strong> - a single flat background color.
                        <br /><br />
                        <strong>Linear Gradient</strong> - blends two colors together at an angle you choose.
                        <br /><br />
                        <strong>Custom CSS</strong> - enter any valid CSS background value yourself.
                        <br /><br />
                        <strong>None</strong> - no background color, useful when the icon image already includes its own background.
                    </div>
                    ?
                </div><!--backtest-field-help-icon-->
            </div><!--form-line-->

            <div class="infinity23-background-details-wrap" id="infinity23-background-details-<?php echo esc_attr( $field_id ); ?>">

                <div class="infinity23-background-type-line <?php if( $bg_struct['type'] !== 'solid' ) { echo 'infinity23-position-hidden'; } ?>" data-background-type="solid">
                    <div class="form-line background-color-line">
                        <label for="<?php echo esc_attr( $field_id ); ?>_solid_color">Color: </label>
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_solid_color" name="<?php echo esc_attr( $field_id ); ?>_solid_color" class="infinity23-color-picker" value="<?php echo esc_attr( $bg_struct['solid_color'] ); ?>" />
                    </div><!--form-line-->
                </div><!--infinity23-background-type-line-->

                <div class="infinity23-background-type-line <?php if( $bg_struct['type'] !== 'gradient' ) { echo 'infinity23-position-hidden'; } ?>" data-background-type="gradient">
                    <div class="form-line background-color-line">
                        <label for="<?php echo esc_attr( $field_id ); ?>_gradient_color_1">Color 1: </label>
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_gradient_color_1" name="<?php echo esc_attr( $field_id ); ?>_gradient_color_1" class="infinity23-color-picker" value="<?php echo esc_attr( $bg_struct['gradient_color_1'] ); ?>" />
                    </div><!--form-line-->
                    <div class="form-line background-color-line">
                        <label for="<?php echo esc_attr( $field_id ); ?>_gradient_color_2">Color 2: </label>
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_gradient_color_2" name="<?php echo esc_attr( $field_id ); ?>_gradient_color_2" class="infinity23-color-picker" value="<?php echo esc_attr( $bg_struct['gradient_color_2'] ); ?>" />
                    </div><!--form-line-->
                    <div class="form-line">
                        <label for="<?php echo esc_attr( $field_id ); ?>_gradient_angle">Angle: </label>
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_gradient_angle" class="very-short-input" name="<?php echo esc_attr( $field_id ); ?>_gradient_angle" value="<?php echo esc_attr( $bg_struct['gradient_angle'] ); ?>" />
                        <small>Degrees</small>
                    </div><!--form-line-->
                </div><!--infinity23-background-type-line-->

                <div class="infinity23-background-type-line <?php if( $bg_struct['type'] !== 'custom_css' ) { echo 'infinity23-position-hidden'; } ?>" data-background-type="custom_css">
                    <div class="form-line">
                        <label for="<?php echo esc_attr( $field_id ); ?>_custom_css">Background Value: </label>
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_custom_css" class="long-input" name="<?php echo esc_attr( $field_id ); ?>_custom_css" value="<?php echo esc_attr( $bg_struct['custom_css'] ); ?>" />

                        <div class="backtest-field-help-icon">
                            <div class="data-help-content">
                                Accepts any valid CSS background value (images are not supported here).
                                <br /><br />
                                <strong>Usage Examples:</strong>
                                <ul>
                                    <li>Solid color - <span class="code">#00bc3c</span></li>
                                    <li>Gradient - <span class="code">linear-gradient(180deg, #00bc3c, #008d32)</span></li>
                                    <li>Gradient at an angle - <span class="code">linear-gradient(45deg, #ff9966, #ff5e62)</span></li>
                                    <li>3 color gradient - <span class="code">linear-gradient(180deg, #00bc3c, #00e14e, #008d32)</span></li>
                                    <li>Radial gradient - <span class="code">radial-gradient(circle, #00e14e, #008d32)</span></li>
                                    <li>Semi transparent color - <span class="code">rgba(0, 188, 60, 0.8)</span></li>
                                </ul>
                            </div>
                            ?
                        </div><!--backtest-field-help-icon-->
                    </div><!--form-line-->
                </div><!--infinity23-background-type-line-->

            </div><!--infinity23-background-details-wrap-->
            <?php

        } // function infinity23_advanced_background_field( $raw_background_value, $field_id = 'background', $label = 'Background:' ) {

    } // if( !function_exists( 'infinity23_advanced_background_field' ) ) {

// ===========================================================================
// Button Display Type Field - shared field output (global icon vs image toggle)
// ===========================================================================

    // --------------------------------------------
    // output the full "Choose Button Type" section - tabs, help icon, and
    // both upload wraps (icon + simple image), each with its own preview,
    // upload input and default URL line. $infinity23_preview_output_icon and
    // $infinity23_preview_output_image must still contain the {preview_id}/{image_id}
    // placeholders - this function replaces them with the fixed ids every
    // module already shares (icon_button_preview/icon_image_preview and
    // simple_image_preview/simple_image_preview_img). Everything a module
    // needs to vary (backgrounds, borders, sizes, extra fields) stays out of
    // this function and lives in the module's own icon/image view wraps.
    // --------------------------------------------

    if( !function_exists( 'infinity23_button_display_type_field' ) ) {

        function infinity23_button_display_type_field( $infinity23_button_display_type, $icon_image_url, $simple_image_url, $default_icon_image_url, $default_button_image_url, $infinity23_preview_output_icon, $infinity23_preview_output_image ) {

            ?>
            <h3 class="infinity23-inline-h3">Choose Button Type:</h3>

            <div class="infinity23-button-type-tabs">
                <button type="button" class="infinity23-button-type-tab-btn <?php if( $infinity23_button_display_type === 'icon' ) { echo 'infinity23-button-type-tab-active'; } ?>" data-display-type="icon">Icon (Advanced Styling)</button>
                <button type="button" class="infinity23-button-type-tab-btn <?php if( $infinity23_button_display_type === 'image' ) { echo 'infinity23-button-type-tab-active'; } ?>" data-display-type="image">Image (Simpler Editing)</button>

                <div class="backtest-field-help-icon">
                    <div class="data-help-content">
                        <strong>Icon (Advanced Styling)</strong> - upload a small icon and the plugin builds the button around it: background color, size, border and round corners are all controlled below.
                        <br /><br />
                        <strong>Simple Image</strong> - upload a complete, ready-made transparent button image (PNG or SVG). No background, border or round corners are added - only the image size is set.
                    </div>
                    ?
                </div><!--backtest-field-help-icon-->
            </div><!--infinity23-button-type-tabs-->

            <div id="icon-image-url-wrap" class="<?php if( $infinity23_button_display_type !== 'icon' ) { echo 'infinity23-position-hidden'; } ?>">

                <h3 class="infinity23-inline-h3">Custom Icon Image URL:</h3>

                <div class="infinity23-upload-wrap">

                    <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
                    <?php echo str_replace( array( '{preview_id}', '{image_id}' ), array( 'icon_button_preview', 'icon_image_preview' ), $infinity23_preview_output_icon ); ?>

                    <br /><br />
                    <label for="icon_image_url">Custom Icon Image URL: </label>
                    <input type="text" id="icon_image_url" class="infinity23-upload-input very-long-input" name="icon_image_url" value="<?php echo esc_url( $icon_image_url ); ?>" />
                    <input type="button" class="infinity23-upload-btn infinity23-upload-btn-style" value="Choose Image..." />

                </div><!--infinity23-upload-wrap-->

                <small class='default-img-url'>Default URL: <span id="default-icon-img-url-value"><?php echo esc_url( $default_icon_image_url ); ?></span></small>

            </div><!--icon-image-url-wrap-->

            <div id="simple-image-url-wrap" class="<?php if( $infinity23_button_display_type !== 'image' ) { echo 'infinity23-position-hidden'; } ?>">

                <h3 class="infinity23-inline-h3">Custom Simple Image URL:</h3>

                <div class="infinity23-upload-wrap">

                    <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
                    <?php echo str_replace( array( '{preview_id}', '{image_id}' ), array( 'simple_image_preview', 'simple_image_preview_img' ), $infinity23_preview_output_image ); ?>

                    <br /><br />
                    <label for="simple_image_url">Custom Simple Image URL: </label>
                    <input type="text" id="simple_image_url" class="infinity23-upload-input very-long-input" name="simple_image_url" value="<?php echo esc_url( $simple_image_url ); ?>" />
                    <input type="button" class="infinity23-upload-btn infinity23-upload-btn-style" value="Choose Image..." />

                </div><!--infinity23-upload-wrap-->

                <small class='default-img-url'>Default URL: <span id="default-simple-img-url-value"><?php echo esc_url( $default_button_image_url ); ?></span></small>

            </div><!--simple-image-url-wrap-->

            <input type="hidden" id="button_display_type" name="button_display_type" value="<?php echo esc_attr( $infinity23_button_display_type ); ?>" />
            <?php

        } // function infinity23_button_display_type_field(

    } // if( !function_exists( 'infinity23_button_display_type_field' ) ) {

    // --------------------------------------------
    // output the secondary icon preview shown further down the page while
    // Icon type is selected (hidden entirely when Image type is selected)
    // --------------------------------------------

    if( !function_exists( 'infinity23_button_display_type_secondary_preview' ) ) {

        function infinity23_button_display_type_secondary_preview( $infinity23_button_display_type, $infinity23_preview_output_icon ) {

            ?>
            <div id="icon_button_preview_2_wrap" class="<?php if( $infinity23_button_display_type === 'image' ) { echo 'infinity23-position-hidden'; } ?>">
            <br />
            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
            <?php echo str_replace( array( '{preview_id}', '{image_id}' ), array( 'icon_button_preview_2', 'icon_image_preview_2' ), $infinity23_preview_output_icon ); ?>
            </div><!--icon_button_preview_2_wrap-->
            <?php

        } // function infinity23_button_display_type_secondary_preview(

    } // if( !function_exists( 'infinity23_button_display_type_secondary_preview' ) ) {

// ===========================================================================
// Button Position Field - shared field output (desktop/mobile tabbed positioning)
// ===========================================================================

    // --------------------------------------------
    // output the full desktop/mobile button position builder - two tabs,
    // desktop position + hide + z-index, and mobile hide + breakpoint +
    // inherit/custom position + z-index. Stored as one JSON string.
    // --------------------------------------------

    if( !function_exists( 'infinity23_advanced_button_position_field' ) ) {

        function infinity23_advanced_button_position_field( $raw_position_value, $field_id = 'button_position' ) {

            $position_struct = infinity23_parse_button_position_to_struct( $raw_position_value );
            $mobile_custom_hidden_class = ( $position_struct['mobile_mode'] !== 'custom' ) ? 'infinity23-position-hidden' : '';

            $h_spacing_split = infinity23_split_size_unit_value( $position_struct['h_spacing'] );
            $v_spacing_split = infinity23_split_size_unit_value( $position_struct['v_spacing'] );
            $mobile_h_spacing_split = infinity23_split_size_unit_value( $position_struct['mobile_h_spacing'] );
            $mobile_v_spacing_split = infinity23_split_size_unit_value( $position_struct['mobile_v_spacing'] );

            ?>
            <div class="infinity23-position-tabs-wrap" id="infinity23-position-details-<?php echo esc_attr( $field_id ); ?>">

                <div class="infinity23-position-tabs">
                    <button type="button" class="infinity23-position-tab-btn infinity23-position-tab-active" data-tab-target="infinity23-position-tab-desktop-<?php echo esc_attr( $field_id ); ?>"><img src="<?php echo esc_url( infinity23_plugin_url( 'images/plugin-icons/desktop-icon.svg' ) ); ?>" class="infinity23-position-tab-icon" alt="" /> Desktop</button>
                    <button type="button" class="infinity23-position-tab-btn" data-tab-target="infinity23-position-tab-mobile-<?php echo esc_attr( $field_id ); ?>"><img src="<?php echo esc_url( infinity23_plugin_url( 'images/plugin-icons/mobile-icon.svg' ) ); ?>" class="infinity23-position-tab-icon" alt="" /> Mobile</button>
                </div><!--infinity23-position-tabs-->

                <div class="infinity23-position-tab-content" id="infinity23-position-tab-desktop-<?php echo esc_attr( $field_id ); ?>">

                    <div class="form-line checkbox-line">
                        <input type="checkbox" id="<?php echo esc_attr( $field_id ); ?>_hide_desktop" name="<?php echo esc_attr( $field_id ); ?>_hide_desktop" value="1" <?php if( !empty( $position_struct['hide_desktop'] ) ) { echo 'checked'; } ?> />
                        <label for="<?php echo esc_attr( $field_id ); ?>_hide_desktop">Hide the button on desktop</label>
                    </div><!--form-line-->

                    <div class="form-line">
                        <label for="<?php echo esc_attr( $field_id ); ?>_h_position">Horizontal Position: </label>
                        <select name="<?php echo esc_attr( $field_id ); ?>_h_position" id="<?php echo esc_attr( $field_id ); ?>_h_position">
                            <option value="left" <?php if( $position_struct['h_position'] === 'left' ) { echo 'selected'; } ?>>Left</option>
                            <option value="right" <?php if( $position_struct['h_position'] === 'right' ) { echo 'selected'; } ?>>Right</option>
                        </select>
                        <label for="<?php echo esc_attr( $field_id ); ?>_h_spacing">x</label>
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_h_spacing" class="very-short-input" name="<?php echo esc_attr( $field_id ); ?>_h_spacing" value="<?php echo esc_attr( $h_spacing_split['value'] ); ?>" />
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
                        <?php echo infinity23_unit_select_field( $field_id . '_h_spacing_unit', $h_spacing_split['unit'] ); ?>

                        <div class="backtest-field-help-icon">
                            <div class="data-help-content">
                                Controls how far the button is placed from the left or right edge of the screen.
                                <br /><br />
                                <strong>Usage Examples:</strong>
                                <ul>
                                    <li>Fixed distance - <span class="code">20px</span></li>
                                    <li>Relative distance - <span class="code">5%</span></li>
                                    <li>Flush to edge - <span class="code">0px</span></li>
                                </ul>
                            </div>
                            ?
                        </div><!--backtest-field-help-icon-->
                    </div><!--form-line-->

                    <div class="form-line">
                        <label for="<?php echo esc_attr( $field_id ); ?>_v_position">Vertical Position: </label>
                        <select name="<?php echo esc_attr( $field_id ); ?>_v_position" id="<?php echo esc_attr( $field_id ); ?>_v_position">
                            <option value="top" <?php if( $position_struct['v_position'] === 'top' ) { echo 'selected'; } ?>>Top</option>
                            <option value="bottom" <?php if( $position_struct['v_position'] === 'bottom' ) { echo 'selected'; } ?>>Bottom</option>
                        </select>
                        <label for="<?php echo esc_attr( $field_id ); ?>_v_spacing">x</label>
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_v_spacing" class="very-short-input" name="<?php echo esc_attr( $field_id ); ?>_v_spacing" value="<?php echo esc_attr( $v_spacing_split['value'] ); ?>" />
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
                        <?php echo infinity23_unit_select_field( $field_id . '_v_spacing_unit', $v_spacing_split['unit'] ); ?>

                        <div class="backtest-field-help-icon">
                            <div class="data-help-content">
                                Controls how far the button is placed from the top or bottom edge of the screen.
                                <br /><br />
                                <strong>Usage Examples:</strong>
                                <ul>
                                    <li>Fixed distance - <span class="code">50px</span></li>
                                    <li>Relative distance - <span class="code">10%</span></li>
                                    <li>Flush to edge - <span class="code">0px</span></li>
                                </ul>
                            </div>
                            ?
                        </div><!--backtest-field-help-icon-->
                    </div><!--form-line-->

                    <div class="form-line">
                        <label for="<?php echo esc_attr( $field_id ); ?>_z_index_desktop">Z-index: </label>
                        <input type="text" id="<?php echo esc_attr( $field_id ); ?>_z_index_desktop" class="short-input" name="<?php echo esc_attr( $field_id ); ?>_z_index_desktop" value="<?php echo esc_attr( $position_struct['z_index_desktop'] ); ?>" />

                        <div class="backtest-field-help-icon">
                            <div class="data-help-content">
                                Z-index controls the stacking order of elements on the page.
                                <br /><br />
                                If another element appears on top of the button, increase this number.
                            </div>
                            ?
                        </div><!--backtest-field-help-icon-->
                    </div><!--form-line-->

                </div><!--infinity23-position-tab-content-->

                <div class="infinity23-position-tab-content infinity23-position-tab-hidden" id="infinity23-position-tab-mobile-<?php echo esc_attr( $field_id ); ?>">

                    <div class="form-line checkbox-line">
                        <input type="checkbox" id="<?php echo esc_attr( $field_id ); ?>_hide_mobile" name="<?php echo esc_attr( $field_id ); ?>_hide_mobile" value="1" <?php if( !empty( $position_struct['hide_mobile'] ) ) { echo 'checked'; } ?> />
                        <label for="<?php echo esc_attr( $field_id ); ?>_hide_mobile">Hide the button on mobile</label>
                    </div><!--form-line-->

                    <div class="form-line">
                        <label for="<?php echo esc_attr( $field_id ); ?>_mobile_mode">Mobile Position: </label>
                        <select name="<?php echo esc_attr( $field_id ); ?>_mobile_mode" id="<?php echo esc_attr( $field_id ); ?>_mobile_mode" class="infinity23-position-toggle-select" data-target="infinity23-position-mobile-custom-<?php echo esc_attr( $field_id ); ?>">
                            <option value="inherit" <?php if( $position_struct['mobile_mode'] === 'inherit' ) { echo 'selected'; } ?>>Inherit From Desktop</option>
                            <option value="custom" <?php if( $position_struct['mobile_mode'] === 'custom' ) { echo 'selected'; } ?>>Choose Different Position</option>
                        </select>
                    </div><!--form-line-->

                    <div class="infinity23-position-mobile-custom-wrap <?php echo esc_attr( $mobile_custom_hidden_class ); ?>" id="infinity23-position-mobile-custom-<?php echo esc_attr( $field_id ); ?>">

                        <div class="form-line">
                            <label for="<?php echo esc_attr( $field_id ); ?>_mobile_width">Mobile breaking point: </label>
                            <input type="text" id="<?php echo esc_attr( $field_id ); ?>_mobile_width" class="very-short-input" name="<?php echo esc_attr( $field_id ); ?>_mobile_width" value="<?php echo esc_attr( $position_struct['mobile_width'] ); ?>" />
                            <small>PX</small>

                            <div class="backtest-field-help-icon">
                                <div class="data-help-content">
                                    The screen width where the site switches between desktop and mobile.
                                    <br /><br />
                                    Below this width, the Mobile position set here is used instead of the Desktop position above.
                                </div>
                                ?
                            </div><!--backtest-field-help-icon-->
                        </div><!--form-line-->

                        <div class="form-line">
                            <label for="<?php echo esc_attr( $field_id ); ?>_mobile_h_position">Horizontal Position: </label>
                            <select name="<?php echo esc_attr( $field_id ); ?>_mobile_h_position" id="<?php echo esc_attr( $field_id ); ?>_mobile_h_position">
                                <option value="left" <?php if( $position_struct['mobile_h_position'] === 'left' ) { echo 'selected'; } ?>>Left</option>
                                <option value="right" <?php if( $position_struct['mobile_h_position'] === 'right' ) { echo 'selected'; } ?>>Right</option>
                            </select>
                            <label for="<?php echo esc_attr( $field_id ); ?>_mobile_h_spacing">x</label>
                            <input type="text" id="<?php echo esc_attr( $field_id ); ?>_mobile_h_spacing" class="very-short-input" name="<?php echo esc_attr( $field_id ); ?>_mobile_h_spacing" value="<?php echo esc_attr( $mobile_h_spacing_split['value'] ); ?>" />
                            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
                            <?php echo infinity23_unit_select_field( $field_id . '_mobile_h_spacing_unit', $mobile_h_spacing_split['unit'] ); ?>
                        </div><!--form-line-->

                        <div class="form-line">
                            <label for="<?php echo esc_attr( $field_id ); ?>_mobile_v_position">Vertical Position: </label>
                            <select name="<?php echo esc_attr( $field_id ); ?>_mobile_v_position" id="<?php echo esc_attr( $field_id ); ?>_mobile_v_position">
                                <option value="top" <?php if( $position_struct['mobile_v_position'] === 'top' ) { echo 'selected'; } ?>>Top</option>
                                <option value="bottom" <?php if( $position_struct['mobile_v_position'] === 'bottom' ) { echo 'selected'; } ?>>Bottom</option>
                            </select>
                            <label for="<?php echo esc_attr( $field_id ); ?>_mobile_v_spacing">x</label>
                            <input type="text" id="<?php echo esc_attr( $field_id ); ?>_mobile_v_spacing" class="very-short-input" name="<?php echo esc_attr( $field_id ); ?>_mobile_v_spacing" value="<?php echo esc_attr( $mobile_v_spacing_split['value'] ); ?>" />
                            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped values ?>
                            <?php echo infinity23_unit_select_field( $field_id . '_mobile_v_spacing_unit', $mobile_v_spacing_split['unit'] ); ?>
                        </div><!--form-line-->

                    </div><!--infinity23-position-mobile-custom-wrap-->

                </div><!--infinity23-position-tab-content-->

            </div><!--infinity23-position-tabs-wrap-->
            <?php

        } // function infinity23_advanced_button_position_field( $raw_position_value, $field_id = 'button_position' ) {

    } // if( !function_exists( 'infinity23_advanced_button_position_field' ) ) {

// ===========================================================================
// Custom CSS - shared field output (global, reusable by every module)
// ===========================================================================

    // --------------------------------------------
    // output the enable checkbox + custom css textarea. Used by every module
    // that adds a Custom CSS option, so the wording and markup only ever
    // change here. The textarea starts empty - the example code lives in a
    // separate collapsed code block instead of being prefilled into the field.
    // --------------------------------------------

    if( !function_exists( 'infinity23_custom_css_field' ) ) {

        function infinity23_custom_css_field( $infinity23_custom_css, $infinity23_custom_css_active, $field_id = 'custom_css', $infinity23_module_display_name = '' ) {

            ?>
            <div class="form-line checkbox-line">
                <input type="checkbox" id="<?php echo esc_attr( $field_id ); ?>_active" name="<?php echo esc_attr( $field_id ); ?>_active" value="1" <?php if( $infinity23_custom_css_active == 1 ) { echo 'checked'; } ?> <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?> />
                <label for="<?php echo esc_attr( $field_id ); ?>_active">Enable Custom CSS on the front-end of the site</label>
            </div><!--form-line-->

            <div class="form-line">
                <label for="<?php echo esc_attr( $field_id ); ?>">Add Custom CSS to <?php echo esc_html( $infinity23_module_display_name ); ?> module: </label>

                <div class="backtest-field-help-icon">
                    <div class="data-help-content">
                        Insert your own CSS code here to further customize this module's output.
                        <br /><br />
                        This code is always printed after every other CSS on the page, so it can override the default styling.
                        <br /><br />
                        The code only runs when <strong>Enable Custom CSS</strong> above is checked.
                        <br /><br />
                        <strong>Warning:</strong> incorrect CSS can visually break this module's output - only paste code you trust.
                    </div>
                    ?
                </div><!--backtest-field-help-icon-->
            </div><!--form-line-->

            <div class="infinity23-line-numbered-wrap">
                <div class="infinity23-line-numbers"><span>1</span></div>
                <textarea id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_id ); ?>" class="infinity23-tagrow-textarea infinity23-line-numbered-textarea infinity23-textarea-adjustable" <?php echo esc_attr( infinity23_pro_disabled_attr() ); ?>><?php echo esc_textarea( $infinity23_custom_css ); ?></textarea>
            </div><!--infinity23-line-numbered-wrap-->
            <?php

        } // function infinity23_custom_css_field( $infinity23_custom_css, $infinity23_custom_css_active, $field_id = 'custom_css' ) {

    } // if( !function_exists( 'infinity23_custom_css_field' ) ) {

    // --------------------------------------------
    // output a toggleable "Show Example CSS" code block, using the same
    // pattern as the Additional Data code examples. $example_code_html must
    // already contain the correct, module-specific selectors, with
    // infinity23-code-comment spans for the explanatory lines - the caller
    // builds it since the real selectors differ per module.
    // --------------------------------------------

    if( !function_exists( 'infinity23_custom_css_example_block' ) ) {

        function infinity23_custom_css_example_block( $example_code_html, $infinity23_module_display_name = '' ) {

            if( empty( $example_code_html ) ) { return; }

            ?>
            <button type="button" class="infinity23-code-example-btn">Code Example For <?php echo esc_html( $infinity23_module_display_name ); ?> Module</button>
            <div class="infinity23-code-example-block">
                <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by the caller from already-escaped values ?>
                <pre><code><?php echo $example_code_html; ?></code></pre>
            </div><!--infinity23-code-example-block-->
            <?php

        } // function infinity23_custom_css_example_block( $example_code_html, $infinity23_module_display_name = '' ) {

    } // if( !function_exists( 'infinity23_custom_css_example_block' ) ) {

    // --------------------------------------------
    // output the full Custom CSS section-card (pro label, pro notice, enable
    // checkbox, field, example block). Closed by default so it never takes
    // over the settings page on load.
    // --------------------------------------------

    if( !function_exists( 'infinity23_custom_css_section_card' ) ) {

        function infinity23_custom_css_section_card( $infinity23_custom_css, $infinity23_custom_css_active, $example_code_html = '', $field_id = 'custom_css', $infinity23_module_display_name = '' ) {

            $card_default_state = 'open';

            ?>
            <div class="section-card <?php echo esc_attr( infinity23_pro_locked_class() ); ?>" data-card-default="<?php echo esc_attr( $card_default_state ); ?>">

                <h2>Custom CSS: <?php echo wp_kses_post( infinity23_section_card_title_pro_label() ); ?></h2>

                <?php echo wp_kses_post( infinity23_pro_notice() ); ?>

                <?php infinity23_custom_css_field( $infinity23_custom_css, $infinity23_custom_css_active, $field_id, $infinity23_module_display_name ); ?>

                <?php infinity23_custom_css_example_block( $example_code_html, $infinity23_module_display_name ); ?>

            </div><!--section-card-->
            <?php

        } // function infinity23_custom_css_section_card( $infinity23_custom_css, $infinity23_custom_css_active, $example_code_html = '', $field_id = 'custom_css' ) {

    } // if( !function_exists( 'infinity23_custom_css_section_card' ) ) {

// ===========================================================================
// Menu Visibility - shared field output
// ===========================================================================

    // --------------------------------------------
    // output the full "Hide From Menu" box - h3 title and checkbox. Used by
    // every module's own admin-output.php so the wording only ever needs to
    // change in one place.
    // --------------------------------------------

    if( !function_exists( 'infinity23_menu_visibility_field' ) ) {

        function infinity23_menu_visibility_field( $field_id, $hide_menu_value, $infinity23_module_display_name ) {

            ?>
            <div class="infinity23-menu-visibility-box">

                <h3>Essential Sidebar Menu Visibility:</h3>

                <div class="form-line checkbox-line">
                    <input type="checkbox" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_id ); ?>" value="1" <?php if( $hide_menu_value == 1 ) { echo 'checked'; } ?> />
                    <label for="<?php echo esc_attr( $field_id ); ?>">Hide <?php echo esc_html( $infinity23_module_display_name ); ?> from WordPress sidebar menu (remove page from essential tab)</label>

                    <div class="backtest-field-help-icon">
                        <div class="data-help-content">
                            When this option is selected, the page will be hidden from the Essential tab on the WordPress sidebar menu.
                            <br /><br />
                            The page keeps working even when hidden - when needed, you can still open it directly from the Include / Exclude page.
                            <br /><br />
                            The idea is to let you organize the Essential menu so it only shows the options you actually use often.
                        </div>
                        ?
                    </div><!--backtest-field-help-icon-->
                </div><!--form-line-->

            </div><!--infinity23-menu-visibility-box-->
            <?php

        } // function infinity23_menu_visibility_field( $field_id, $hide_menu_value, $infinity23_module_display_name ) {

    } // if( !function_exists( 'infinity23_menu_visibility_field' ) ) {

?>
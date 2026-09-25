<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Pro functions 
// ===========================================================================

    // --------------------------------------------
    // single dummy pro/license check - swap the return value (or add real license
    // validation here later) once a pro version actually exists
    // --------------------------------------------

    if( !function_exists( 'infinity23_is_pro' ) ) {

        function infinity23_is_pro() {
            return false;
        } // function infinity23_is_pro() {

    } // if( !function_exists( 'infinity23_is_pro' ) ) {

    // --------------------------------------------
    // functions that allow to get the urls and dir path to the plugin
    // --------------------------------------------

    // creating pro label to show on section cards that only aviliable on pro versions
    if( !function_exists( 'infinity23_section_card_title_pro_label' ) ) {

        function infinity23_section_card_title_pro_label() {

            $output = '';

            if( infinity23_is_pro() ) {
                $output .= "<span class='pro-badge pro-badge-active'>PRO</span>";
            } else {
                $output .= "<span class='pro-badge'>PRO</span>";
            } // if( infinity23_is_pro() ) {

            // $output .= "<span class='pro-badge-free'>Free For Now</span>";

            return $output;

        } // function infinity23_section_card_title_pro_label() {

    } // if( !function_exists( 'infinity23_section_card_title_pro_label' ) ) {

    // --------------------------------------------
    // single source of truth for the Pro version sales page url - every pro
    // notice/tooltip link must go through this, never a hardcoded url
    // --------------------------------------------

    if( !function_exists( 'infinity23_pro_version_link' ) ) {

        function infinity23_pro_version_link() {
            return INFINITY23_PRO_VERSION_LINK;
        } // function infinity23_pro_version_link() {

    } // if( !function_exists( 'infinity23_pro_version_link' ) ) {

    // --------------------------------------------
    // function that show a line that explain how to get access to the pro version
    // --------------------------------------------

    if( !function_exists( 'infinity23_pro_notice' ) ) {

        function infinity23_pro_notice() {

            if( infinity23_is_pro() ) {
                return '';
            } // if( infinity23_is_pro() ) {

            $output = '';
            $output .= '<div class="infinity23-pro-notice">';
            $output .= '<p>This section is only available in the <a href="' . esc_url( infinity23_pro_version_link() ) . '" target="_blank" rel="noopener">Pro version</a>.</p>';
            $output .= '</div><!--infinity23-pro-notice-->';

            return $output;

        } // function infinity23_pro_notice() {

    } // if( !function_exists( 'infinity23_pro_notice' ) ) {

    // --------------------------------------------
    // shared save helper for every field - access/downgrade/value/type come
    // straight from that field's own entry in $infinity23_defaults:
    // access:    'free' - no pro check. 'pro' - gated below.
    // downgrade: 'keep'  - saved value stays and keeps applying when pro is off
    //            'reset' - saved value falls back to default when pro is off
    // --------------------------------------------

    if( !function_exists( 'infinity23_pro_field_sanitize' ) ) {

        function infinity23_pro_field_sanitize( $input, $infinity23_data_array, $infinity23_defaults ) {

            $infinity23_default_entry = isset( $infinity23_defaults[$input] ) ? $infinity23_defaults[$input] : array();
            $access                  = isset( $infinity23_default_entry['access'] ) ? $infinity23_default_entry['access'] : 'free';
            $downgrade               = isset( $infinity23_default_entry['downgrade'] ) ? $infinity23_default_entry['downgrade'] : 'reset';
            $type                    = isset( $infinity23_default_entry['type'] ) ? $infinity23_default_entry['type'] : 'text';

            if( $access === 'free' || infinity23_is_pro() ) {
                return infinity23_post_sanitize( $input, $type );
            } // if( $access === 'free' || infinity23_is_pro() ) {

            if( $downgrade === 'keep' ) {
                return infinity23_get_data( $input, $infinity23_data_array, $infinity23_defaults );
            } // if( $downgrade === 'keep' ) {

            return infinity23_get_data( $input, array(), $infinity23_defaults );

        } // function infinity23_pro_field_sanitize( $input, $infinity23_data_array, $infinity23_defaults ) {

    } // if( !function_exists( 'infinity23_pro_field_sanitize' ) ) {

    // --------------------------------------------
    // pro-aware runtime read - use this (never a raw array/option read) whenever
    // a module's own back-end/front-end code applies a gated field's value to
    // actual behavior, not just displaying it on the settings page:
    // 'free' and 'keep' fields read the normal stored-or-default value.
    // 'reset' fields return the default live, every call, whenever pro is off -
    // regardless of what is actually stored, so the effect stops immediately
    // instead of waiting for the next save.
    // --------------------------------------------

    if( !function_exists( 'infinity23_pro_get_data' ) ) {

        function infinity23_pro_get_data( $key, $infinity23_data_array, $infinity23_defaults ) {

            $infinity23_default_entry = isset( $infinity23_defaults[$key] ) ? $infinity23_defaults[$key] : array();
            $access                  = isset( $infinity23_default_entry['access'] ) ? $infinity23_default_entry['access'] : 'free';
            $downgrade               = isset( $infinity23_default_entry['downgrade'] ) ? $infinity23_default_entry['downgrade'] : 'reset';

            if( $access === 'pro' && $downgrade === 'reset' && !infinity23_is_pro() ) {
                return infinity23_get_data( $key, array(), $infinity23_defaults );
            } // if( $access === 'pro' && $downgrade === 'reset' && !infinity23_is_pro() ) {

            return infinity23_get_data( $key, $infinity23_data_array, $infinity23_defaults );

        } // function infinity23_pro_get_data( $key, $infinity23_data_array, $infinity23_defaults ) {

    } // if( !function_exists( 'infinity23_pro_get_data' ) ) {

    // --------------------------------------------
    // css class added to a pro-only section wrap when the user does not have
    // pro - JS hooks into this class later to lock/disable the fields inside it
    // --------------------------------------------

    if( !function_exists( 'infinity23_pro_locked_class' ) ) {

        function infinity23_pro_locked_class() {

            if( infinity23_is_pro() ) {
                return '';
            } // if( infinity23_is_pro() ) {

            return 'infinity23-pro-locked';

        } // function infinity23_pro_locked_class() {

    } // if( !function_exists( 'infinity23_pro_locked_class' ) ) {

    // --------------------------------------------
    // html "disabled" attribute for a single pro-only field - server rendered,
    // so the field is locked from the very first page load, not only after JS runs
    // --------------------------------------------

    if( !function_exists( 'infinity23_pro_disabled_attr' ) ) {

        function infinity23_pro_disabled_attr() {

            if( infinity23_is_pro() ) {
                return '';
            } // if( infinity23_is_pro() ) {

            return 'disabled';

        } // function infinity23_pro_disabled_attr() {

    } // if( !function_exists( 'infinity23_pro_disabled_attr' ) ) {



?>
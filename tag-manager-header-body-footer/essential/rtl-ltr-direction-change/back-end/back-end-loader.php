<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // LTR RTL Direction Change - admin bar toggle, per-page saving with data slot sharding
    // ===========================================================================

    // ======================================================================================================================================================
    // Free Options - storage plumbing, not gated by pro
    // ======================================================================================================================================================

    // ---------------------------------------
    // build a best-effort unique key for the current admin screen
    // uses the post/page/tag id when one is present in the URL
    // ---------------------------------------

    if( !function_exists( 'infinity23_rtl_ltr_get_page_key' ) ) {

        function infinity23_rtl_ltr_get_page_key() {

            global $pagenow;

            $key = sanitize_key( $pagenow );

            // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only: builds a cache-key string identifying which admin screen is open, no state change
            if( isset( $_GET['post'] ) && !is_array( $_GET['post'] ) && !empty( $_GET['post'] ) ) {
                $key .= '_post_' . intval( $_GET['post'] );
            } elseif( isset( $_GET['page_id'] ) && !is_array( $_GET['page_id'] ) && !empty( $_GET['page_id'] ) ) {
                $key .= '_pageid_' . intval( $_GET['page_id'] );
            } elseif( isset( $_GET['tag_ID'] ) && !is_array( $_GET['tag_ID'] ) && !empty( $_GET['tag_ID'] ) ) {
                $key .= '_tag_' . intval( $_GET['tag_ID'] );
            } elseif( isset( $_GET['page'] ) && !is_array( $_GET['page'] ) && !empty( $_GET['page'] ) ) {
                $key .= '_page_' . sanitize_key( wp_unslash( $_GET['page'] ) );
            } // if( isset( $_GET['post'] ) ...
            // phpcs:enable WordPress.Security.NonceVerification.Recommended

            return $key;

        } // function infinity23_rtl_ltr_get_page_key() {

    } // if( !function_exists( 'infinity23_rtl_ltr_get_page_key' ) ) {

    // ---------------------------------------
    // maximum number of page entries kept inside a single data slot before
    // new pages start filling the next slot (data1 through data10)
    // ---------------------------------------

    if( !defined( 'INFINITY23_RTL_LTR_MAX_PAGES_PER_SLOT' ) ) {
        define( 'INFINITY23_RTL_LTR_MAX_PAGES_PER_SLOT', 2500 );
    } // if( !defined( 'INFINITY23_RTL_LTR_MAX_PAGES_PER_SLOT' ) ) {

    // ---------------------------------------
    // read every data1-data10 slot for this module, decoded into arrays
    // ---------------------------------------

    if( !function_exists( 'infinity23_rtl_ltr_get_all_data_slots' ) ) {

        function infinity23_rtl_ltr_get_all_data_slots() {

            $slots = array();

            for( $i = 1; $i <= 10; $i++ ) {
                $slots[ $i ] = infinity23_get_option_data_array( 'rtl_ltr_direction_change_settings', 'data' . $i );
            } // for( $i = 1; $i <= 10; $i++ ) {

            return $slots;

        } // function infinity23_rtl_ltr_get_all_data_slots() {

    } // if( !function_exists( 'infinity23_rtl_ltr_get_all_data_slots' ) ) {

    // ---------------------------------------
    // write every data1-data10 slot back in one single update
    // ---------------------------------------

    if( !function_exists( 'infinity23_rtl_ltr_save_all_data_slots' ) ) {

        function infinity23_rtl_ltr_save_all_data_slots( $slots ) {

            infinity23_update_option(
                'rtl_ltr_direction_change_settings',
                infinity23_get_option( 'rtl_ltr_direction_change_settings' ),
                infinity23_convert_array_to_json( $slots[1] ),
                infinity23_convert_array_to_json( $slots[2] ),
                infinity23_convert_array_to_json( $slots[3] ),
                infinity23_convert_array_to_json( $slots[4] ),
                infinity23_convert_array_to_json( $slots[5] ),
                infinity23_convert_array_to_json( $slots[6] ),
                infinity23_convert_array_to_json( $slots[7] ),
                infinity23_convert_array_to_json( $slots[8] ),
                infinity23_convert_array_to_json( $slots[9] ),
                infinity23_convert_array_to_json( $slots[10] )
            );

        } // function infinity23_rtl_ltr_save_all_data_slots( $slots ) {

    } // if( !function_exists( 'infinity23_rtl_ltr_save_all_data_slots' ) ) {

    // ---------------------------------------
    // read a single page's saved direction, searching every data slot
    // ---------------------------------------

    if( !function_exists( 'infinity23_rtl_ltr_get_page_direction' ) ) {

        function infinity23_rtl_ltr_get_page_direction( $page_key ) {

            $slots = infinity23_rtl_ltr_get_all_data_slots();

            for( $i = 1; $i <= 10; $i++ ) {
                if( isset( $slots[ $i ][ $page_key ] ) ) {
                    return $slots[ $i ][ $page_key ];
                } // if( isset( $slots[ $i ][ $page_key ] ) ) {
            } // for( $i = 1; $i <= 10; $i++ ) {

            return '';

        } // function infinity23_rtl_ltr_get_page_direction( $page_key ) {

    } // if( !function_exists( 'infinity23_rtl_ltr_get_page_direction' ) ) {

    // ---------------------------------------
    // save or update a single page's direction - reuses the slot the page
    // already lives in, otherwise picks the first slot with room, and only
    // moves on to the next data slot once the current one is full
    // ---------------------------------------

    if( !function_exists( 'infinity23_rtl_ltr_save_page_direction' ) ) {

        function infinity23_rtl_ltr_save_page_direction( $page_key, $new_direction ) {

            $slots = infinity23_rtl_ltr_get_all_data_slots();
            $target_slot = 0;

            for( $i = 1; $i <= 10; $i++ ) {
                if( isset( $slots[ $i ][ $page_key ] ) ) {
                    $target_slot = $i;
                    break;
                } // if( isset( $slots[ $i ][ $page_key ] ) ) {
            } // for( $i = 1; $i <= 10; $i++ ) {

            if( $target_slot === 0 ) {

                for( $i = 1; $i <= 10; $i++ ) {
                    if( count( $slots[ $i ] ) < INFINITY23_RTL_LTR_MAX_PAGES_PER_SLOT ) {
                        $target_slot = $i;
                        break;
                    } // if( count( $slots[ $i ] ) < INFINITY23_RTL_LTR_MAX_PAGES_PER_SLOT ) {
                } // for( $i = 1; $i <= 10; $i++ ) {

                if( $target_slot === 0 ) {
                    $target_slot = 10;
                } // if( $target_slot === 0 ) {

            } // if( $target_slot === 0 ) {

            $slots[ $target_slot ][ $page_key ] = $new_direction;

            infinity23_rtl_ltr_save_all_data_slots( $slots );

        } // function infinity23_rtl_ltr_save_page_direction( $page_key, $new_direction ) {

    } // if( !function_exists( 'infinity23_rtl_ltr_save_page_direction' ) ) {

    // ---------------------------------------
    // remove a single page's saved direction from whichever slot holds it
    // ---------------------------------------

    if( !function_exists( 'infinity23_rtl_ltr_remove_page_direction' ) ) {

        function infinity23_rtl_ltr_remove_page_direction( $page_key ) {

            $slots = infinity23_rtl_ltr_get_all_data_slots();
            $found = false;

            for( $i = 1; $i <= 10; $i++ ) {
                if( isset( $slots[ $i ][ $page_key ] ) ) {
                    unset( $slots[ $i ][ $page_key ] );
                    $found = true;
                } // if( isset( $slots[ $i ][ $page_key ] ) ) {
            } // for( $i = 1; $i <= 10; $i++ ) {

            if( !$found ) { return; }

            infinity23_rtl_ltr_save_all_data_slots( $slots );

        } // function infinity23_rtl_ltr_remove_page_direction( $page_key ) {

    } // if( !function_exists( 'infinity23_rtl_ltr_remove_page_direction' ) ) {

    // ======================================================================================================================================================
    // Paid Options - fields gated by pro, always read through infinity23_pro_get_data()
    // ======================================================================================================================================================

    // ---------------------------------------
    // read the direction that should apply to the current admin screen -
    // only pages saved while Save Page was selected return a value
    // ---------------------------------------

    if( !function_exists( 'infinity23_rtl_ltr_get_current_direction' ) ) {

        function infinity23_rtl_ltr_get_current_direction( $page_key ) {

            $infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option( 'rtl_ltr_direction_change_settings' ) );
            include( dirname(__FILE__) . "/../default-values.php" );
            $save_scope = intval( infinity23_pro_get_data( 'save_scope', $infinity23_data_array, $infinity23_defaults ) );

            if( $save_scope != 1 ) {
                return '';
            } // if( $save_scope != 1 ) {

            return infinity23_rtl_ltr_get_page_direction( $page_key );

        } // function infinity23_rtl_ltr_get_current_direction( $page_key ) {

    } // if( !function_exists( 'infinity23_rtl_ltr_get_current_direction' ) ) {

    // ---------------------------------------
    // read which admin bar button label format is currently saved -
    // 'full' shows "Direction: RTL", 'short' shows just "RTL"
    // ---------------------------------------

    if( !function_exists( 'infinity23_rtl_ltr_get_admin_bar_label_format' ) ) {

        function infinity23_rtl_ltr_get_admin_bar_label_format() {

            $infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option( 'rtl_ltr_direction_change_settings' ) );
            include( dirname(__FILE__) . "/../default-values.php" );

            return infinity23_pro_get_data( 'admin_bar_label_format', $infinity23_data_array, $infinity23_defaults );

        } // function infinity23_rtl_ltr_get_admin_bar_label_format() {

    } // if( !function_exists( 'infinity23_rtl_ltr_get_admin_bar_label_format' ) ) {

    // ---------------------------------------
    // apply the saved direction to the current admin screen
    // ---------------------------------------

    add_action( 'admin_enqueue_scripts', function() {

        if( !current_user_can( 'manage_options' ) ) { return; }

        $page_key = infinity23_rtl_ltr_get_page_key();
        $current_direction = infinity23_rtl_ltr_get_current_direction( $page_key );

        if( empty( $current_direction ) ) { return; }

        $current_direction = infinity23_css_sanitize( $current_direction, 'option', '', array( 'ltr', 'rtl' ) );

        if( empty( $current_direction ) ) { return; }

        wp_add_inline_style( 'infinity23_dashboard_style', '#wpbody { direction: ' . $current_direction . '; }' );

    } ); // add_action( 'admin_enqueue_scripts'

    // ---------------------------------------
    // admin bar button - label reflects the effective direction, and the
    // button gets a highlighted class whenever that direction is not the
    // site's own default direction
    // ---------------------------------------

    add_action( 'admin_bar_menu', function( $wp_admin_bar ) {

        if( !is_admin() ) { return; }

        if( !current_user_can( 'manage_options' ) ) { return; }

        $page_key = infinity23_rtl_ltr_get_page_key();
        $current_direction = infinity23_rtl_ltr_get_current_direction( $page_key );
        $default_direction = is_rtl() ? 'rtl' : 'ltr';
        $effective_direction = !empty( $current_direction ) ? $current_direction : $default_direction;
        $extra_class = ( $effective_direction !== $default_direction ) ? 'infinity23-rtl-ltr-non-default' : '';

        $infinity23_admin_bar_label_format = infinity23_rtl_ltr_get_admin_bar_label_format();

        if( $infinity23_admin_bar_label_format === 'icon_only' ) {
            $extra_class = trim( $extra_class . ' infinity23-rtl-ltr-icon-only-item' );
        } // if( $infinity23_admin_bar_label_format === 'icon_only' ) {

        if( $infinity23_admin_bar_label_format === 'icon_only' ) {
            $infinity23_admin_bar_label_text = '';
        } elseif( $infinity23_admin_bar_label_format === 'short' || $infinity23_admin_bar_label_format === 'text_only' ) {
            $infinity23_admin_bar_label_text = strtoupper( $effective_direction );
        } else {
            $infinity23_admin_bar_label_text = 'Direction: ' . strtoupper( $effective_direction );
        } // if( $infinity23_admin_bar_label_format === 'icon_only' ) {

        $infinity23_admin_bar_icon_html = ( $infinity23_admin_bar_label_format === 'text_only' ) ? '' : '<span class="ab-icon dashicons dashicons-controls-repeat infinity23-rtl-ltr-icon"></span>';
        $infinity23_admin_bar_label_html = '<span class="infinity23-rtl-ltr-label">' . esc_html( $infinity23_admin_bar_label_text ) . '</span>';
        $infinity23_admin_bar_wrap_class = ( $infinity23_admin_bar_label_format === 'icon_only' ) ? 'infinity23-rtl-ltr-title-wrap infinity23-rtl-ltr-icon-only' : 'infinity23-rtl-ltr-title-wrap';
        $infinity23_admin_bar_title_html = '<span class="' . esc_attr( $infinity23_admin_bar_wrap_class ) . '">' . $infinity23_admin_bar_icon_html . $infinity23_admin_bar_label_html . '</span>';

        $wp_admin_bar->add_menu( array(
            'id'    => 'infinity23-rtl-ltr',
            'title' => $infinity23_admin_bar_title_html,
            'href'  => '#',
            'meta'  => array( 'class' => $extra_class ),
        ) );

    }, 999 ); // add_action( 'admin_bar_menu'

    // ---------------------------------------
    // admin bar click - flips the direction on screen, highlights the
    // button when it no longer matches the site's default direction, and
    // saves or clears the page's entry depending on the Save Page setting
    // ---------------------------------------

    add_action( 'admin_enqueue_scripts', function() {

        if( !current_user_can( 'manage_options' ) ) { return; }

        $page_key = infinity23_rtl_ltr_get_page_key();
        $current_direction = infinity23_rtl_ltr_get_current_direction( $page_key );
        $default_direction = is_rtl() ? 'rtl' : 'ltr';
        $effective_direction = !empty( $current_direction ) ? $current_direction : $default_direction;
        $infinity23_admin_bar_label_format = infinity23_rtl_ltr_get_admin_bar_label_format();

        $infinity23_rtl_ltr_js_vars  = 'var infinity23RtlLtrDefaultDirection = ' . wp_json_encode( $default_direction ) . ';';
        $infinity23_rtl_ltr_js_vars .= 'var infinity23RtlLtrCurrentDirection = ' . wp_json_encode( $effective_direction ) . ';';
        $infinity23_rtl_ltr_js_vars .= 'var infinity23RtlLtrLabelFormat = ' . wp_json_encode( $infinity23_admin_bar_label_format ) . ';';
        $infinity23_rtl_ltr_js_vars .= 'var infinity23RtlLtrPageKey = ' . wp_json_encode( $page_key ) . ';';
        $infinity23_rtl_ltr_js_vars .= 'var infinity23RtlLtrNonce = ' . wp_json_encode( wp_create_nonce( 'infinity23_rtl_ltr_nonce' ) ) . ';';

        wp_add_inline_script( 'infinity23_dashboard_js', $infinity23_rtl_ltr_js_vars, 'before' );

    } ); // add_action( 'admin_enqueue_scripts'

    // ---------------------------------------
    // ajax handler - saves the page's direction when it was changed away
    // from the site's default and Save Page is selected, otherwise clears
    // any previously saved entry for this page
    // ---------------------------------------

    add_action( 'wp_ajax_infinity23_rtl_ltr_toggle', function() {

        if( !check_ajax_referer( 'infinity23_rtl_ltr_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
        } // if( !check_ajax_referer(

        if( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        } // if( !current_user_can( 'manage_options' ) ) {

        $new_direction = isset( $_POST['direction'] ) ? sanitize_text_field( wp_unslash( $_POST['direction'] ) ) : '';

        if( !in_array( $new_direction, array( 'ltr', 'rtl' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid direction' ) );
        } // if( !in_array(

        $page_key = isset( $_POST['page_key'] ) ? sanitize_key( wp_unslash( $_POST['page_key'] ) ) : '';

        if( empty( $page_key ) ) {
            $page_key = infinity23_rtl_ltr_get_page_key();
        } // if( empty( $page_key ) ) {

        $default_direction = is_rtl() ? 'rtl' : 'ltr';

        if( $new_direction === $default_direction ) {

            infinity23_rtl_ltr_remove_page_direction( $page_key );

        } else {

            $infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option( 'rtl_ltr_direction_change_settings' ) );
            include( dirname(__FILE__) . "/../default-values.php" );
            $save_scope = intval( infinity23_pro_get_data( 'save_scope', $infinity23_data_array, $infinity23_defaults ) );

            if( $save_scope == 1 ) {
                infinity23_rtl_ltr_save_page_direction( $page_key, $new_direction );
            } // if( $save_scope == 1 ) {

        } // if( $new_direction === $default_direction ) {

        wp_send_json_success( array( 'direction' => $new_direction ) );

    } ); // add_action( 'wp_ajax_infinity23_rtl_ltr_toggle'

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Loading notes settings, meta boxes, dashboard widget, save and ajax
    // ===========================================================================

    include( dirname(__FILE__) . "/../default-values.php" );

    $infinity23_notes_settings_raw = infinity23_get_option( $infinity23_settings_array_db_name );
    $infinity23_notes_settings     = infinity23_convert_json_to_array( $infinity23_notes_settings_raw );

    $infinity23_notes_show_on_pages             = intval( infinity23_pro_get_data( 'show_on_pages', $infinity23_notes_settings, $infinity23_defaults ) );
    $infinity23_notes_show_on_posts             = intval( infinity23_pro_get_data( 'show_on_posts', $infinity23_notes_settings, $infinity23_defaults ) );
    $infinity23_notes_show_on_dashboard         = intval( infinity23_pro_get_data( 'show_on_dashboard', $infinity23_notes_settings, $infinity23_defaults ) );
    $infinity23_notes_show_on_custom_post_types = intval( infinity23_pro_get_data( 'show_on_custom_post_types', $infinity23_notes_settings, $infinity23_defaults ) );
    $infinity23_notes_disabled_custom_post_types = infinity23_pro_get_data( 'disabled_custom_post_types', $infinity23_notes_settings, $infinity23_defaults );
    $infinity23_notes_disabled_cpt_array = array();

    if( !empty( $infinity23_notes_disabled_custom_post_types ) ) {

        foreach( explode( ',', $infinity23_notes_disabled_custom_post_types ) as $infinity23_notes_single_disabled_cpt ) {

            $infinity23_notes_single_disabled_cpt = trim( $infinity23_notes_single_disabled_cpt );

            if( !empty( $infinity23_notes_single_disabled_cpt ) ) {
                $infinity23_notes_disabled_cpt_array[] = $infinity23_notes_single_disabled_cpt;
            } // if( !empty( $infinity23_notes_single_disabled_cpt ) ) {

        } // foreach( explode( ',', $infinity23_notes_disabled_custom_post_types ) as $infinity23_notes_single_disabled_cpt ) {

    } // if( !empty( $infinity23_notes_disabled_custom_post_types ) ) {

    $infinity23_notes_show_on_admin_table       = intval( infinity23_pro_get_data( 'show_on_admin_table', $infinity23_notes_settings, $infinity23_defaults ) );

    global $infinity23_notes_show_note_dates_global;
    $infinity23_notes_show_note_dates_global = intval( infinity23_pro_get_data( 'show_note_dates', $infinity23_notes_settings, $infinity23_defaults ) );

    global $infinity23_notes_adjustable_textarea_height_global;
    $infinity23_notes_adjustable_textarea_height_global = intval( infinity23_pro_get_data( 'adjustable_textarea_height', $infinity23_notes_settings, $infinity23_defaults ) );

    // ---------------------------------------
    // register notes column in pages/posts/custom post types tables
    // loads all existing note ids in a single query for performance
    // ---------------------------------------

    if( $infinity23_notes_show_on_admin_table == 1 ) {

        if( !function_exists( 'infinity23_notes_add_column' ) ) {

            function infinity23_notes_add_column( $columns ) {

                $columns['infinity23_notes_column'] = 'Notes';
                return $columns;

            } // function infinity23_notes_add_column( $columns ) {

        } // if( !function_exists( 'infinity23_notes_add_column' ) ) {

        if( !function_exists( 'infinity23_notes_render_column' ) ) {

            function infinity23_notes_render_column( $column_name, $post_id ) {

                if( $column_name !== 'infinity23_notes_column' ) { return; }

                // wordpress fires both the generic manage_posts/pages_custom_column hook and the
                // per post type hook for the same row - print the value only once per row
                static $infinity23_notes_printed_ids = array();

                if( isset( $infinity23_notes_printed_ids[ $post_id ] ) ) { return; }

                $infinity23_notes_printed_ids[ $post_id ] = true;

                global $infinity23_notes_existing_ids;

                if( !is_array( $infinity23_notes_existing_ids ) ) {
                    $infinity23_notes_existing_ids = infinity23_notes_get_all_pages_with_notes();
                } // if( !is_array( $infinity23_notes_existing_ids ) ) {

                if( in_array( strval( $post_id ), $infinity23_notes_existing_ids ) ) {
                    echo 'Yes';
                } else {
                    echo '-';
                } // if( in_array(

            } // function infinity23_notes_render_column( $column_name, $post_id ) {

        } // if( !function_exists( 'infinity23_notes_render_column' ) ) {

        add_action( 'admin_init', function() use ( $infinity23_notes_show_on_pages, $infinity23_notes_show_on_posts, $infinity23_notes_show_on_custom_post_types, $infinity23_notes_disabled_cpt_array ) {

            global $pagenow;

            // the notes column only exists on the pages/posts list tables
            if( $pagenow !== 'edit.php' ) { return; }

            if( $infinity23_notes_show_on_pages == 1 ) {

                add_filter( 'manage_pages_columns', 'infinity23_notes_add_column' );
                add_action( 'manage_pages_custom_column', 'infinity23_notes_render_column', 10, 2 );

            } // if( $infinity23_notes_show_on_pages == 1 ) {

            if( $infinity23_notes_show_on_posts == 1 ) {

                add_filter( 'manage_posts_columns', 'infinity23_notes_add_column' );
                add_action( 'manage_posts_custom_column', 'infinity23_notes_render_column', 10, 2 );

            } // if( $infinity23_notes_show_on_posts == 1 ) {

            if( $infinity23_notes_show_on_custom_post_types == 1 ) {

                $built_in_types = array( 'page', 'post', 'attachment', 'revision', 'nav_menu_item' );
                $all_post_types = get_post_types( array( 'public' => true ), 'names' );

                foreach( $all_post_types as $post_type ) {

                    if( !in_array( $post_type, $built_in_types ) ) {

                        // same check the meta box already does - without it a
                        // disabled post type kept an empty Notes column
                        if( in_array( $post_type, $infinity23_notes_disabled_cpt_array, true ) ) {
                            continue;
                        } // if( in_array( $post_type, $infinity23_notes_disabled_cpt_array, true ) ) {

                        add_filter( 'manage_' . $post_type . '_posts_columns', 'infinity23_notes_add_column' );
                        add_action( 'manage_' . $post_type . '_posts_custom_column', 'infinity23_notes_render_column', 10, 2 );

                    } // if( !in_array( $post_type, $built_in_types ) ) {

                } // foreach( $all_post_types as $post_type ) {

            } // if( $infinity23_notes_show_on_custom_post_types == 1 ) {

        } ); // add_action( 'admin_init'

    } // if( $infinity23_notes_show_on_admin_table == 1 ) {

    // ---------------------------------------
    // register meta box on pages
    // ---------------------------------------

    if( $infinity23_notes_show_on_pages == 1 ) {

        add_action( 'add_meta_boxes', function() {
            add_meta_box(
                'infinity23_notes',
                'Page Notes',
                function( $post ) {
                    $infinity23_notes_post_object = $post;
                    include( dirname(__FILE__) . "/admin-notes-output.php" );
                },
                'page',
                'side'
            );
        } ); // add_action( 'add_meta_boxes'

    } // if( $infinity23_notes_show_on_pages == 1 ) {

    // ---------------------------------------
    // register meta box on posts
    // ---------------------------------------

    if( $infinity23_notes_show_on_posts == 1 ) {

        add_action( 'add_meta_boxes', function() {
            add_meta_box(
                'infinity23_notes_post',
                'Post Notes',
                function( $post ) {
                    $infinity23_notes_post_object = $post;
                    include( dirname(__FILE__) . "/admin-notes-output.php" );
                },
                'post',
                'side'
            );
        } ); // add_action( 'add_meta_boxes'

    } // if( $infinity23_notes_show_on_posts == 1 ) {

    // ---------------------------------------
    // register meta box on custom post types
    // ---------------------------------------

    if( $infinity23_notes_show_on_custom_post_types == 1 ) {

        add_action( 'add_meta_boxes', function() use ( $infinity23_notes_disabled_cpt_array ) {

            $built_in_types = array( 'page', 'post', 'attachment', 'revision', 'nav_menu_item' );
            $all_post_types = get_post_types( array( 'public' => true ), 'names' );

            foreach( $all_post_types as $post_type ) {

                if( !in_array( $post_type, $built_in_types ) ) {

                    if( in_array( $post_type, $infinity23_notes_disabled_cpt_array, true ) ) {
                        continue;
                    } // if( in_array( $post_type, $infinity23_notes_disabled_cpt_array, true ) ) {

                    add_meta_box(
                        'infinity23_notes_' . $post_type,
                        'Notes',
                        function( $post ) {
                            $infinity23_notes_post_object = $post;
                            include( dirname(__FILE__) . "/admin-notes-output.php" );
                        },
                        $post_type,
                        'side'
                    );

                } // if( !in_array( $post_type, $built_in_types ) ) {

            } // foreach( $all_post_types as $post_type ) {

        } ); // add_action( 'add_meta_boxes'

    } // if( $infinity23_notes_show_on_custom_post_types == 1 ) {

    // ---------------------------------------
    // register dashboard widget
    // ---------------------------------------

    if( $infinity23_notes_show_on_dashboard == 1 ) {

        add_action( 'wp_dashboard_setup', function() {

            // dashboard notes are internal - admins and editors only
            if( !current_user_can( 'edit_others_posts' ) ) { return; }

            wp_add_dashboard_widget(
                'infinity23_notes_dashboard',
                'Dashboard Notes',
                function() {
                    $infinity23_notes_is_dashboard = true;
                    include( dirname(__FILE__) . "/admin-notes-output.php" );
                }
            );

        } ); // add_action( 'wp_dashboard_setup'

    } // if( $infinity23_notes_show_on_dashboard == 1 ) {

    // ---------------------------------------
    // save notes on post/page update
    // ---------------------------------------

    add_action( 'save_post', function( $post_id ) {

        if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) { return; }

        if( wp_is_post_revision( $post_id ) ) { return; }
        if( wp_is_post_autosave( $post_id ) ) { return; }
        if( !isset( $_POST['infinity23_notes_box_nonce'] ) ) { return; }
        if( !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_notes_box_nonce'] ) ), 'infinity23_notes_action_' . $post_id ) ) { return; }
        if( !current_user_can( 'edit_post', $post_id ) ) { return; }

        // the id always comes from the post being saved, never from the form
        $infinity23_notes_forced_page_id = $post_id;

        include( dirname(__FILE__) . "/admin-save.php" );

    } ); // add_action( 'save_post'

    // ---------------------------------------
    // remove the notes row when a page/post is permanently deleted
    // ---------------------------------------

    add_action( 'before_delete_post', function( $post_id ) {

        $infinity23_notes_delete_id = intval( $post_id );

        if( empty( $infinity23_notes_delete_id ) ) { return; }

        infinity23_notes_delete_rows_by_page( strval( $infinity23_notes_delete_id ) );

    } ); // add_action( 'before_delete_post'

    // ---------------------------------------
    // ajax save for dashboard notes
    // ---------------------------------------

    add_action( 'wp_ajax_infinity23_notes_save', function() {

        if( !isset( $_POST['infinity23_notes_box_nonce'] ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed.' ) );
        } // if( !isset( $_POST['infinity23_notes_box_nonce'] ) ) {

        $infinity23_notes_ajax_page_id = isset( $_POST['infinity23_notes_page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['infinity23_notes_page_id'] ) ) : '';

        if( !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_notes_box_nonce'] ) ), 'infinity23_notes_action_' . $infinity23_notes_ajax_page_id ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed.' ) );
        } // if( !wp_verify_nonce(

        if( $infinity23_notes_ajax_page_id === 'dashboard' ) {

            if( !current_user_can( 'edit_others_posts' ) ) {
                wp_send_json_error( array( 'message' => 'Permission denied.' ) );
            } // if( !current_user_can( 'edit_others_posts' ) ) {

        } else {

            if( !current_user_can( 'edit_post', intval( $infinity23_notes_ajax_page_id ) ) ) {
                wp_send_json_error( array( 'message' => 'Permission denied.' ) );
            } // if( !current_user_can( 'edit_post'

        } // if( $infinity23_notes_ajax_page_id === 'dashboard' ) {

        include( dirname(__FILE__) . "/admin-save.php" );

        wp_send_json_success( array( 'message' => 'Notes saved.' ) );

    } ); // add_action( 'wp_ajax_infinity23_notes_save'

    // ---------------------------------------
    // ajax remove all notes for a single page from the All Notes table
    // ---------------------------------------

    add_action( 'wp_ajax_infinity23_notes_remove_all_for_page', function() {

        if( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied.' ) );
        } // if( !current_user_can( 'manage_options' ) ) {

        if( !isset( $_POST['infinity23_notes_all_remove_page_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_notes_all_remove_page_nonce'] ) ), 'infinity23_notes_all_remove_page_action' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed.' ) );
        } // if( !isset( $_POST['infinity23_notes_all_remove_page_nonce'] ) ...

        $infinity23_data_array = infinity23_convert_json_to_array( infinity23_get_option( 'notes_settings' ) );
        include( dirname(__FILE__) . "/../default-values.php" );

        if( intval( infinity23_pro_get_data( 'all_notes_allow_remove', $infinity23_data_array, $infinity23_defaults ) ) !== 1 ) {
            wp_send_json_error( array( 'message' => 'Permission denied.' ) );
        } // if( intval( infinity23_pro_get_data( 'all_notes_allow_remove', $infinity23_data_array, $infinity23_defaults ) ) !== 1 ) {

        $infinity23_notes_remove_page_id = isset( $_POST['page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['page_id'] ) ) : '';

        if( empty( $infinity23_notes_remove_page_id ) ) {
            wp_send_json_error( array( 'message' => 'Invalid page.' ) );
        } // if( empty( $infinity23_notes_remove_page_id ) ) {

        infinity23_notes_delete_rows_by_page( $infinity23_notes_remove_page_id );

        wp_send_json_success( array( 'message' => 'Notes removed.' ) );

    } ); // add_action( 'wp_ajax_infinity23_notes_remove_all_for_page'

?>
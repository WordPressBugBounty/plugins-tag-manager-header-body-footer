<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    // ===========================================================================
    // Show Pages IDs - display page/post/term IDs across the admin and front-end
    // ===========================================================================

    include( dirname(__FILE__) . "/../../../include/settings.php" );
    include( dirname(__FILE__) . "/../internal-settings.php" );
    include( dirname(__FILE__) . "/../default-values.php" );

    $infinity23_spi_settings_data = infinity23_convert_json_to_array( infinity23_get_option( $infinity23_settings_array_db_name ) );

    $infinity23_spi_show_on_pages             = intval( infinity23_pro_get_data( 'show_on_pages', $infinity23_spi_settings_data, $infinity23_defaults ) );
    $infinity23_spi_show_on_posts             = intval( infinity23_pro_get_data( 'show_on_posts', $infinity23_spi_settings_data, $infinity23_defaults ) );
    $infinity23_spi_show_on_media             = intval( infinity23_pro_get_data( 'show_on_media', $infinity23_spi_settings_data, $infinity23_defaults ) );
    $infinity23_spi_show_on_comments          = intval( infinity23_pro_get_data( 'show_on_comments', $infinity23_spi_settings_data, $infinity23_defaults ) );
    $infinity23_spi_show_on_categories        = intval( infinity23_pro_get_data( 'show_on_categories', $infinity23_spi_settings_data, $infinity23_defaults ) );
    $infinity23_spi_show_on_tags              = intval( infinity23_pro_get_data( 'show_on_tags', $infinity23_spi_settings_data, $infinity23_defaults ) );
    $infinity23_spi_show_on_users             = intval( infinity23_pro_get_data( 'show_on_users', $infinity23_spi_settings_data, $infinity23_defaults ) );
    $infinity23_spi_show_on_custom_post_types = intval( infinity23_pro_get_data( 'show_on_custom_post_types', $infinity23_spi_settings_data, $infinity23_defaults ) );
    $infinity23_spi_show_on_woocommerce       = intval( infinity23_pro_get_data( 'show_on_woocommerce', $infinity23_spi_settings_data, $infinity23_defaults ) );
    $infinity23_spi_show_on_admin_bar         = intval( infinity23_pro_get_data( 'show_on_admin_bar', $infinity23_spi_settings_data, $infinity23_defaults ) );
    $infinity23_spi_show_on_body_class        = intval( infinity23_pro_get_data( 'show_on_body_class', $infinity23_spi_settings_data, $infinity23_defaults ) );

    // ===========================================================================
    // Admin bar - display the ID number on the front-end and admin area
    // ===========================================================================

    if( $infinity23_spi_show_on_admin_bar == 1 ) {

        if( !function_exists( 'infinity23_spi_adding_id_number_to_admin_bar' ) ) {

            function infinity23_spi_adding_id_number_to_admin_bar( $wp_admin_bar ) {

                global $wp_query;

                $post_id_num = "";
                $post_type = get_post_type();

                if( $post_type === 'page' || $post_type === 'post' || is_single() ) {
                    $post_id_num = "ID: " . get_the_ID();
                } // if( $post_type === 'page' || $post_type === 'post' || is_single() ) {

                if( is_category() || is_tag() ) {

                    $id_type = "";
                    if( is_category() ) { $id_type = "Category "; }
                    if( is_tag() ) { $id_type = "Tag "; }

                    $post_id_num = $id_type . "ID: " . $wp_query->get_queried_object_id();

                } // if( is_category() || is_tag() ) {

                if( is_home() ) {
                    $blog_page_id = get_option( 'page_for_posts' );
                    if( !empty( $blog_page_id ) ) {
                        $post_id_num = "ID: " . $blog_page_id;
                    } // if( !empty( $blog_page_id ) ) {
                } // if( is_home() ) {

                if( class_exists( 'woocommerce' ) ) {

                    if( $post_type === 'product' ) {
                        $post_id_num = "ID: " . get_the_ID();
                    } // if( $post_type === 'product' ) {

                    if( is_product_category() || is_product_tag() ) {

                        $id_type = "";
                        if( is_product_category() ) { $id_type = "Category "; }
                        if( is_product_tag() ) { $id_type = "Tag "; }

                        $post_id_num = $id_type . "ID: " . $wp_query->get_queried_object_id();

                    } // if( is_product_category() || is_product_tag() ) {

                } // if( class_exists( 'woocommerce' ) ) {

                if( !empty( $post_id_num ) ) {

                    $args = array(
                        'id'    => 'infinity23_page_id',
                        'title' => $post_id_num,
                        'href'  => '',
                        'meta'  => array( 'class' => 'infinity23-toolbar-page-id' )
                    );

                    $wp_admin_bar->add_node( $args );

                } // if( !empty( $post_id_num ) ) {

            } // function infinity23_spi_adding_id_number_to_admin_bar( $wp_admin_bar ) {

        } // if( !function_exists( 'infinity23_spi_adding_id_number_to_admin_bar' ) ) {

        add_action( 'admin_bar_menu', 'infinity23_spi_adding_id_number_to_admin_bar', 9999 );

    } // if( $infinity23_spi_show_on_admin_bar == 1 ) {

    // ===========================================================================
    // Body class - add the page id class to the front-end body tag
    // ===========================================================================

    if( $infinity23_spi_show_on_body_class == 1 ) {

        add_filter( 'body_class', function( $classes ) {

            global $wp_query;

            $post_id_class = "";
            $post_type = get_post_type();

            if( $post_type === 'page' || $post_type === 'post' || is_single() ) {
                $post_id_class = "page-id-" . get_the_ID();
            } // if( $post_type === 'page' || $post_type === 'post' || is_single() ) {

            if( is_category() || is_tag() ) {

                $id_type = "";
                if( is_category() ) { $id_type = "category"; }
                if( is_tag() ) { $id_type = "tag"; }

                $post_id_class = $id_type . "-id-" . $wp_query->get_queried_object_id();

            } // if( is_category() || is_tag() ) {

            if( is_home() ) {
                $blog_page_id = get_option( 'page_for_posts' );
                if( !empty( $blog_page_id ) ) {
                    $post_id_class = "page-id-" . $blog_page_id;
                } // if( !empty( $blog_page_id ) ) {
            } // if( is_home() ) {

            if( class_exists( 'woocommerce' ) ) {

                if( $post_type === 'product' ) {
                    $post_id_class = "page-id-" . get_the_ID();
                } // if( $post_type === 'product' ) {

                if( is_product_category() || is_product_tag() ) {

                    $id_type = "";
                    if( is_product_category() ) { $id_type = "category"; }
                    if( is_product_tag() ) { $id_type = "tag"; }

                    $post_id_class = $id_type . "-id-" . $wp_query->get_queried_object_id();

                } // if( is_product_category() || is_product_tag() ) {

            } // if( class_exists( 'woocommerce' ) ) {

            if( isset( $post_id_class ) && !empty( $post_id_class ) ) {
                $classes[] = $post_id_class;
            } // if( isset( $post_id_class ) && !empty( $post_id_class ) ) {

            return $classes;

        } ); // add_filter( 'body_class'

    } // if( $infinity23_spi_show_on_body_class == 1 ) {

    // ===========================================================================
    // Admin area only - table columns
    // ===========================================================================

    if( is_admin() ) {

        // ---------------------------------------
        // shared column title and render helpers
        // ---------------------------------------

        if( !function_exists( 'infinity23_spi_add_id_title_to_table' ) ) {

            function infinity23_spi_add_id_title_to_table( $columns ) {
                return array_merge( $columns, array( 'show_id_num' => 'ID' ) );
            } // function infinity23_spi_add_id_title_to_table( $columns ) {

        } // if( !function_exists( 'infinity23_spi_add_id_title_to_table' ) ) {

        if( !function_exists( 'infinity23_spi_add_id_number_to_id_column' ) ) {

            function infinity23_spi_add_id_number_to_id_column( $column, $id ) {

                // wordpress fires both the generic manage_posts/pages_custom_column hook and the
                // per post type hook for the same row - print the id only once per row
                static $infinity23_spi_printed_ids = array();

                if( $column === 'show_id_num' && !isset( $infinity23_spi_printed_ids[ $id ] ) ) {
                    $infinity23_spi_printed_ids[ $id ] = true;
                    echo esc_html( $id );
                } // if( $column === 'show_id_num' && !isset( $infinity23_spi_printed_ids[ $id ] ) ) {

            } // function infinity23_spi_add_id_number_to_id_column( $column, $id ) {

        } // if( !function_exists( 'infinity23_spi_add_id_number_to_id_column' ) ) {

        if( !function_exists( 'infinity23_spi_add_id_number_to_categories_tags' ) ) {

            function infinity23_spi_add_id_number_to_categories_tags( $content, $column_name, $term_id ) {

                if( $column_name === 'show_id_num' ) {
                    return esc_html( $term_id );
                } // if( $column_name === 'show_id_num' ) {

                return $content;

            } // function infinity23_spi_add_id_number_to_categories_tags( $content, $column_name, $term_id ) {

        } // if( !function_exists( 'infinity23_spi_add_id_number_to_categories_tags' ) ) {

        // ---------------------------------------
        // pages
        // ---------------------------------------

        if( $infinity23_spi_show_on_pages == 1 ) {
            add_filter( 'manage_pages_columns', 'infinity23_spi_add_id_title_to_table', 1 );
            add_action( 'manage_pages_custom_column', 'infinity23_spi_add_id_number_to_id_column', 2, 2 );
        } // if( $infinity23_spi_show_on_pages == 1 ) {

        // ---------------------------------------
        // posts
        // ---------------------------------------

        if( $infinity23_spi_show_on_posts == 1 ) {
            add_filter( 'manage_posts_columns', 'infinity23_spi_add_id_title_to_table', 1 );
            add_action( 'manage_posts_custom_column', 'infinity23_spi_add_id_number_to_id_column', 2, 2 );
        } // if( $infinity23_spi_show_on_posts == 1 ) {

        // ---------------------------------------
        // media
        // ---------------------------------------

        if( $infinity23_spi_show_on_media == 1 ) {
            add_filter( 'manage_media_columns', 'infinity23_spi_add_id_title_to_table', 1 );
            add_action( 'manage_media_custom_column', 'infinity23_spi_add_id_number_to_id_column', 2, 2 );
        } // if( $infinity23_spi_show_on_media == 1 ) {

        // ---------------------------------------
        // comments
        // ---------------------------------------

        if( $infinity23_spi_show_on_comments == 1 ) {
            add_filter( 'manage_edit-comments_columns', 'infinity23_spi_add_id_title_to_table', 1 );
            add_action( 'manage_comments_custom_column', 'infinity23_spi_add_id_number_to_id_column', 2, 2 );
        } // if( $infinity23_spi_show_on_comments == 1 ) {

        // ---------------------------------------
        // categories
        // ---------------------------------------

        if( $infinity23_spi_show_on_categories == 1 ) {
            add_filter( 'manage_edit-category_columns', 'infinity23_spi_add_id_title_to_table', 1 );
            add_filter( 'manage_category_custom_column', 'infinity23_spi_add_id_number_to_categories_tags', 2, 3 );
        } // if( $infinity23_spi_show_on_categories == 1 ) {

        // ---------------------------------------
        // tags
        // ---------------------------------------

        if( $infinity23_spi_show_on_tags == 1 ) {
            add_filter( 'manage_edit-post_tag_columns', 'infinity23_spi_add_id_title_to_table', 1 );
            add_filter( 'manage_post_tag_custom_column', 'infinity23_spi_add_id_number_to_categories_tags', 2, 3 );
        } // if( $infinity23_spi_show_on_tags == 1 ) {

        // ---------------------------------------
        // users
        // ---------------------------------------

        if( $infinity23_spi_show_on_users == 1 ) {

            add_filter( 'manage_users_columns', 'infinity23_spi_add_id_title_to_table', 1 );

            add_filter( 'manage_users_custom_column', function( $value, $column_name, $user_id ) {

                if( $column_name === 'show_id_num' ) {
                    $value = esc_html( $user_id );
                } // if( $column_name === 'show_id_num' ) {

                return $value;

            }, 1, 3 ); // add_filter( 'manage_users_custom_column'

        } // if( $infinity23_spi_show_on_users == 1 ) {

        // ---------------------------------------
        // custom post types (excludes core types and woocommerce products,
        // which are covered by their own toggle above)
        // ---------------------------------------

        if( $infinity23_spi_show_on_custom_post_types == 1 ) {

            add_action( 'admin_init', function() {

                $built_in_types = array( 'page', 'post', 'attachment', 'revision', 'nav_menu_item', 'product' );
                $all_post_types = get_post_types( array( 'public' => true ), 'names' );

                foreach( $all_post_types as $post_type ) {

                    if( !in_array( $post_type, $built_in_types ) ) {

                        add_filter( 'manage_' . $post_type . '_posts_columns', 'infinity23_spi_add_id_title_to_table', 1 );
                        add_action( 'manage_' . $post_type . '_posts_custom_column', 'infinity23_spi_add_id_number_to_id_column', 2, 2 );

                    } // if( !in_array( $post_type, $built_in_types ) ) {

                } // foreach( $all_post_types as $post_type ) {

            } ); // add_action( 'admin_init'

        } // if( $infinity23_spi_show_on_custom_post_types == 1 ) {

        // ---------------------------------------
        // woocommerce products, product categories and product tags
        // ---------------------------------------

        if( $infinity23_spi_show_on_woocommerce == 1 && class_exists( 'woocommerce' ) ) {

            add_filter( 'manage_product_posts_columns', 'infinity23_spi_add_id_title_to_table', 1 );
            add_action( 'manage_product_posts_custom_column', 'infinity23_spi_add_id_number_to_id_column', 2, 2 );

            add_filter( 'manage_edit-product_cat_columns', 'infinity23_spi_add_id_title_to_table', 1 );
            add_filter( 'manage_edit-product_tag_columns', 'infinity23_spi_add_id_title_to_table', 1 );

            add_filter( 'manage_product_cat_custom_column', 'infinity23_spi_add_id_number_to_categories_tags', 2, 3 );
            add_filter( 'manage_product_tag_custom_column', 'infinity23_spi_add_id_number_to_categories_tags', 2, 3 );

        } // if( $infinity23_spi_show_on_woocommerce == 1 && class_exists( 'woocommerce' ) ) {

    } // if( is_admin() ) {

?>
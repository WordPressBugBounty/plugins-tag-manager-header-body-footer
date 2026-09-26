<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Including essential systems to the plugin
// ===========================================================================

    // --------------------------------------
    // Module registry - filled in by each module's own block below
    // --------------------------------------

    global $infinity23_essential_modules_array;
    $infinity23_essential_modules_array = array();

    // --------------------------------------
    // Hidden menu slugs - filled in by each module's own hide_menu check
    // below, consumed by the dashboard-tier admin_enqueue_scripts hook in
    // index.php to build one combined #adminmenu hide rule
    // --------------------------------------

    global $infinity23_hidden_menu_slugs;
    $infinity23_hidden_menu_slugs = array();

    // --------------------------------------
    // Include and exclude page
    // --------------------------------------

    include( dirname(__FILE__) . "/include-exclude/loader.php" );

    // --------------------------------------
    // load include/exclude settings to check which plugins are active
    // --------------------------------------

    // getting the database data to see which plugin are active and which are not
    $infinity23_ie_data = infinity23_convert_json_to_array( infinity23_get_option('include_exclude_plugins') );

    // loading default values incase there is no data existing about the plugins
    include( dirname(__FILE__) . "/include-exclude/default-values.php" );
    $infinity23_include_exclude_defaults = $infinity23_defaults;

    // --------------------------------------
    // after a Danger Zone reset no module may load, not even the ones that are
    // on by default, until Include / Exclude is saved once (that save clears
    // the pending flag) - the same rule the Include / Exclude page displays
    // --------------------------------------

    if( intval( infinity23_get_option( INFINITY23_RESET_PENDING_OPTION, 0 ) ) == 1 ) {

        $infinity23_ie_data = array();

        foreach( $infinity23_include_exclude_defaults as $infinity23_reset_module_key => $infinity23_reset_module_default ) {
            $infinity23_include_exclude_defaults[ $infinity23_reset_module_key ] = 0;
        } // foreach( $infinity23_include_exclude_defaults as $infinity23_reset_module_key => $infinity23_reset_module_default ) {

    } // if( intval( infinity23_get_option( INFINITY23_RESET_PENDING_OPTION, 0 ) ) == 1 ) {

    // --------------------------------------
    // WordPress Settings - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the wordpress-settings/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/wordpress-settings/internal-settings.php" );

    $infinity23_load_wordpress_settings = intval( infinity23_get_data( 'wordpress_settings', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_wordpress_settings == 1 ) {
        include( dirname(__FILE__) . "/wordpress-settings/loader.php" );
    } // if( $infinity23_load_wordpress_settings == 1 ) {

    // --------------------------------------
    // Update Manager - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the update-manager/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/update-manager/internal-settings.php" );

    $infinity23_load_update_manager = intval( infinity23_get_data( 'update_manager', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_update_manager == 1 ) {
        include( dirname(__FILE__) . "/update-manager/loader.php" );
    } // if( $infinity23_load_update_manager == 1 ) {

    // --------------------------------------
    // Force SSL & HTTPS - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the ssl-and-https/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/ssl-and-https/internal-settings.php" );

    $infinity23_load_ssl_and_security = intval( infinity23_get_data( 'ssl_and_security', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_ssl_and_security == 1 ) {
        include( dirname(__FILE__) . "/ssl-and-https/loader.php" );
    } // if( $infinity23_load_ssl_and_security == 1 ) {

    // --------------------------------------
    // Email SMTP - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the email-smtp/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/email-smtp/internal-settings.php" );

    $infinity23_load_email_smtp = intval( infinity23_get_data( 'email_smtp', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_email_smtp == 1 ) {
        include( dirname(__FILE__) . "/email-smtp/loader.php" );
    } // if( $infinity23_load_email_smtp == 1 ) {

    // --------------------------------------
    // Accessibility - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the accessibility/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/accessibility/internal-settings.php" );

    $infinity23_load_accessibility = intval( infinity23_get_data( 'accessibility', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_accessibility == 1 ) {
        include( dirname(__FILE__) . "/accessibility/loader.php" );
    } // if( $infinity23_load_accessibility == 1 ) {

    // --------------------------------------
    // Privacy Policy - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the privacy-policy/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/privacy-policy/internal-settings.php" );

    $infinity23_load_privacy_policy = intval( infinity23_get_data( 'privacy_policy', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_privacy_policy == 1 ) {
        include( dirname(__FILE__) . "/privacy-policy/loader.php" );
    } // if( $infinity23_load_privacy_policy == 1 ) {

    // --------------------------------------
    // Back To Top - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the back-to-top/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/back-to-top/internal-settings.php" );

    $infinity23_load_back_to_top = intval( infinity23_get_data( 'back_to_top', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_back_to_top == 1 ) {
        include( dirname(__FILE__) . "/back-to-top/loader.php" );
    } // if( $infinity23_load_back_to_top == 1 ) {

    // --------------------------------------
    // Whatsapp Button - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the whatsapp-button/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/whatsapp-button/internal-settings.php" );

    $infinity23_load_whatsapp_button = intval( infinity23_get_data( 'whatsapp_button', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_whatsapp_button == 1 ) {
        include( dirname(__FILE__) . "/whatsapp-button/loader.php" );
    } // if( $infinity23_load_whatsapp_button == 1 ) {

    // --------------------------------------
    // Phone Button - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the phone-button/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/phone-button/internal-settings.php" );

    $infinity23_load_phone_button = intval( infinity23_get_data( 'phone_button', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_phone_button == 1 ) {
        include( dirname(__FILE__) . "/phone-button/loader.php" );
    } // if( $infinity23_load_phone_button == 1 ) {

    // --------------------------------------
    // Mobile Contact Line - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the mobile-contact-line/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/mobile-contact-line/internal-settings.php" );

    $infinity23_load_mobile_contact_line = intval( infinity23_get_data( 'mobile_contact_line', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_mobile_contact_line == 1 ) {
        include( dirname(__FILE__) . "/mobile-contact-line/loader.php" );
    } // if( $infinity23_load_mobile_contact_line == 1 ) {

    // --------------------------------------
    // Page Notes - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the page-notes/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/page-notes/internal-settings.php" );

    $infinity23_load_notes = intval( infinity23_get_data( 'notes', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_notes == 1 ) {
        include( dirname(__FILE__) . "/page-notes/loader.php" );
    } // if( $infinity23_load_notes == 1 ) {

    // --------------------------------------
    // Tag Manager - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the tag-manager/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/tag-manager/internal-settings.php" );

    $infinity23_load_tag_manager = intval( infinity23_get_data( 'tag_manager', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_tag_manager == 1 ) {
        include( dirname(__FILE__) . "/tag-manager/loader.php" );
    } // if( $infinity23_load_tag_manager == 1 ) {

    // --------------------------------------
    // Duplicate Pages - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the duplicate-pages/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/duplicate-pages/internal-settings.php" );

    $infinity23_load_duplicate_pages = intval( infinity23_get_data( 'duplicate_pages', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_duplicate_pages == 1 ) {
        include( dirname(__FILE__) . "/duplicate-pages/loader.php" );
    } // if( $infinity23_load_duplicate_pages == 1 ) {

    // --------------------------------------
    // Sticky Sidebar - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the sticky-sidebar/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/sticky-sidebar/internal-settings.php" );

    $infinity23_load_sticky_sidebar = intval( infinity23_get_data( 'sticky_sidebar', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_sticky_sidebar == 1 ) {
        include( dirname(__FILE__) . "/sticky-sidebar/loader.php" );
    } // if( $infinity23_load_sticky_sidebar == 1 ) {

    // --------------------------------------
    // LLMs & Robots Editor - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the llms-and-robots-editor/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/llms-and-robots-editor/internal-settings.php" );

    $infinity23_load_llms_and_robots_editor = intval( infinity23_get_data( 'llms_and_robots_editor', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_llms_and_robots_editor == 1 ) {
        include( dirname(__FILE__) . "/llms-and-robots-editor/loader.php" );
    } // if( $infinity23_load_llms_and_robots_editor == 1 ) {

    // --------------------------------------
    // Show All Pages - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the show-all-pages/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/show-all-pages/internal-settings.php" );

    $infinity23_load_show_all_pages = intval( infinity23_get_data( 'show_all_pages', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_show_all_pages == 1 ) {
        include( dirname(__FILE__) . "/show-all-pages/loader.php" );
    } // if( $infinity23_load_show_all_pages == 1 ) {

    // --------------------------------------
    // Plugins/Themes Downloader - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the plugins-and-themes-downloader/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/plugins-and-themes-downloader/internal-settings.php" );

    $infinity23_load_plugins_and_themes_downloader = intval( infinity23_get_data( 'plugins_and_themes_downloader', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_plugins_and_themes_downloader == 1 ) {
        include( dirname(__FILE__) . "/plugins-and-themes-downloader/loader.php" );
    } // if( $infinity23_load_plugins_and_themes_downloader == 1 ) {

    // --------------------------------------
    // Cron Management - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the cron-management/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/cron-management/internal-settings.php" );

    $infinity23_load_cron_management = intval( infinity23_get_data( 'cron_management', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_cron_management == 1 ) {
        include( dirname(__FILE__) . "/cron-management/loader.php" );
    } elseif( intval( infinity23_get_option( 'cron_management_needs_restore', 0 ) ) == 1 ) {
        include( dirname(__FILE__) . "/cron-management/back-end/cron-adjustments.php" );
        infinity23_cron_mgmt_restore_all_interval_changes();
    } // if( $infinity23_load_cron_management == 1 ) {

    // --------------------------------------
    // Redirect 301/302 - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the redirect-301-and-302/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/redirect-301-and-302/internal-settings.php" );

    $infinity23_load_redirect_301_and_302 = intval( infinity23_get_data( 'redirect_301_and_302', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_redirect_301_and_302 == 1 ) {
        include( dirname(__FILE__) . "/redirect-301-and-302/loader.php" );
    } // if( $infinity23_load_redirect_301_and_302 == 1 ) {

    // --------------------------------------
    // Show Pages IDs - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the show-pages-ids/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/show-pages-ids/internal-settings.php" );

    $infinity23_load_show_pages_ids = intval( infinity23_get_data( 'show_pages_ids', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_show_pages_ids == 1 ) {
        include( dirname(__FILE__) . "/show-pages-ids/loader.php" );
    } // if( $infinity23_load_show_pages_ids == 1 ) {

    // --------------------------------------
    // Folders Organizer - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the folders-organizer/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/folders-organizer/internal-settings.php" );

    $infinity23_load_folders_organizer = intval( infinity23_get_data( 'folders_organizer', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_folders_organizer == 1 ) {
        include( dirname(__FILE__) . "/folders-organizer/loader.php" );
    } // if( $infinity23_load_folders_organizer == 1 ) {

    // --------------------------------------
    // LTR/RTL Direction Change - self-registering module (registry entry + conditional
    // loader live in this one block). To fully remove this module: comment
    // out this whole block and delete the rtl-ltr-direction-change/ folder.
    // --------------------------------------

    include( dirname(__FILE__) . "/rtl-ltr-direction-change/internal-settings.php" );

    $infinity23_load_rtl_ltr_direction_change = intval( infinity23_get_data( 'rtl_ltr_direction_change', $infinity23_ie_data, $infinity23_include_exclude_defaults ) );

    if( $infinity23_load_rtl_ltr_direction_change == 1 ) {
        include( dirname(__FILE__) . "/rtl-ltr-direction-change/loader.php" );
    } // if( $infinity23_load_rtl_ltr_direction_change == 1 ) {

    // --------------------------------------
    // License - sidebar link that redirects to the License tab of Include / Exclude.
    // Registered right before Go Pro so it always sits just above it. Not a real
    // settings page, so it is not a module and has no registry entry.
    // The redirect runs on the load-{hook} action, before any admin output starts.
    // --------------------------------------

    $infinity23_hide_license = intval( infinity23_get_data( 'hide_license', infinity23_convert_json_to_array( infinity23_get_option( 'include_exclude_global_settings' ) ), $infinity23_global_defaults ) );

    add_action( 'admin_menu', function() use ( $infinity23_hide_license ) {

        if( $infinity23_hide_license == 1 ) { return; }

        $infinity23_license_hook = add_submenu_page(
            'essential-include-exclude',
            'License',
            'License',
            'manage_options',
            'infinity23-essential-license',
            function() {
            }
        );

        add_action( 'load-' . $infinity23_license_hook, function() {

            wp_safe_redirect( admin_url( 'admin.php?page=essential-include-exclude&tab=license' ) );
            exit;

        } ); // add_action( 'load-' . $infinity23_license_hook

    } ); // add_action( 'admin_menu'

    // --------------------------------------
    // Go Pro - external link submenu item, always the last item in the
    // Essential sidebar regardless of module include/exclude toggles. Not a
    // real settings page (menu_slug is the raw Pro version url, so WordPress
    // links straight to it there), so it is not a module and has no registry entry.
    // Only shown when the site is not Pro - hidden once infinity23_is_pro() is true,
    // and there is no setting to hide it any other way.
    // --------------------------------------

    if( !infinity23_is_pro() ) {

        add_action( 'admin_menu', function() {

            add_submenu_page(
                'essential-include-exclude',
                'Go Pro',
                '<span class="infinity23-menu-go-pro">Upgrade To Pro</span>',
                'manage_options',
                'infinity23-essential-go-pro',
                function() {

                    add_filter( 'allowed_redirect_hosts', function( $hosts ) {
                        $hosts[] = wp_parse_url( infinity23_pro_version_link(), PHP_URL_HOST );
                        return $hosts;
                    } );

                    wp_safe_redirect( infinity23_pro_version_link() );
                    exit;

                }
            );

        } ); // add_action( 'admin_menu'

    } // if( !infinity23_is_pro() ) {

?>
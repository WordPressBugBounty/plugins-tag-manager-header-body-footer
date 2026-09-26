<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// This page holds every license status code and the exact wording a site
// owner reads for it - the only place those sentences exist
// ===========================================================================

// EVERY sentence a site owner ever reads about their license is in here, and
// nowhere else. The license server sends a number; this list turns it into
// words. Nothing written on that server is ever printed in wp-admin.
//
// To see the whole list laid out, run ==LICENSE-CODES==.php in the project
// root - it reads this same file, so it can never drift out of date.
//
// Per entry:
//   type     success or error - picks the colour of the message box
//   label    short name for our own listings, never shown to a site owner
//   message  exactly what the site owner reads
//   side     server = arrives over the wire, plugin = worked out locally with
//            no request at all, both = either side can produce it (only 0)
//   keeps    whether a saved license SURVIVES this code. Anything that is not
//            a clear answer about a real key must keep it. This field is not a
//            note - infinity23_license_activate() branches on it, so a new code
//            cannot be added without saying what it does to a live license
//   live     yes = actually sent today, reserved = number is claimed and the
//            wording is ready, but nothing sends it yet
//
// NEVER reuse a retired number. A plugin already installed on a customer site
// keeps the meaning it shipped with, forever.
//
// The matching numbers on the server are at the top of
// activate-license/index.php on infinity23.com. Adding a code means touching
// both - this file for the wording, that file for when it is sent.


    if( !function_exists( 'infinity23_license_status_list' ) ) {

        function infinity23_license_status_list() {

            $infinity23_license_support_line = ' If this keeps happening please contact our support through ' . INFINITY23_PRO_VERSION_LINK;

            $infinity23_license_status_codes = array();

            // ---------------------------------------
            // worked out on this site, never sent by the server. All negative,
            // so one arriving over the wire is refused as unknown
            // ---------------------------------------

            $infinity23_license_status_codes[-6] = array(
                'type'    => 'error',
                'label'   => 'License data incomplete',
                'message' => 'The license saved on this site is missing its expiry date, so it cannot be trusted and Pro is switched off. Enter your license key again to fix it.',
                'side'    => 'plugin',
                'keeps'   => 'yes',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[-5] = array(
                'type'    => 'error',
                'label'   => 'This host cannot encrypt',
                'message' => 'This site cannot activate a license because its PHP installation is missing the encryption support the plugin needs (OpenSSL with AES-256-GCM). Ask your hosting company to enable it.' . $infinity23_license_support_line,
                'side'    => 'plugin',
                'keeps'   => 'yes',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[-4] = array(
                'type'    => 'error',
                'label'   => 'Saved key unreadable',
                'message' => 'A license key is saved on this site but can no longer be read - this happens after the plugin data is reset or the site is restored from a partial backup. Enter your license key again to fix it.',
                'side'    => 'plugin',
                'keeps'   => 'yes',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[-3] = array(
                'type'    => 'error',
                'label'   => 'Outgoing requests blocked',
                'message' => 'This site is set up to block outgoing connections, so it cannot reach the license server. Ask whoever manages the site to allow connections to infinity23.com in wp-config.php.',
                'side'    => 'plugin',
                'keeps'   => 'yes',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[-2] = array(
                'type'    => 'error',
                'label'   => 'Different domain',
                'message' => 'This license was activated for a different domain, so the Pro features are switched off here. Activate it again on this domain to switch them back on',
                'side'    => 'plugin',
                'keeps'   => 'yes',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[-1] = array(
                'type'    => 'error',
                'label'   => 'Server unreachable',
                'message' => 'We could not reach the license server just now, so nothing was changed on this site. Please try again in a few minutes',
                'side'    => 'plugin',
                'keeps'   => 'yes',
                'live'    => 'yes',
            );

            // ---------------------------------------
            // sent by the server
            // ---------------------------------------

            // the only code both sides produce - the server sends it when
            // something is wrong on its end, and the plugin falls back to it
            // whenever an answer cannot be read at all
            $infinity23_license_status_codes[0] = array(
                'type'    => 'error',
                'label'   => 'Unknown',
                'message' => 'Something went wrong while checking the license and we could not tell why.' . $infinity23_license_support_line,
                'side'    => 'both',
                'keeps'   => 'yes',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[1] = array(
                'type'    => 'success',
                'label'   => 'Activated',
                'message' => 'The license was activated successfully - every Pro feature is now unlocked on this site',
                'side'    => 'server',
                'keeps'   => 'yes',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[2] = array(
                'type'    => 'error',
                'label'   => 'Key not found',
                'message' => 'That license key was not found - check it was copied exactly as it appears in your purchase email',
                'side'    => 'server',
                'keeps'   => 'no',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[3] = array(
                'type'    => 'error',
                'label'   => 'Expired',
                'message' => 'That license key has expired - renew it and then activate it again',
                'side'    => 'server',
                'keeps'   => 'no',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[4] = array(
                'type'    => 'error',
                'label'   => 'Disabled',
                'message' => 'That license key is no longer active.' . $infinity23_license_support_line,
                'side'    => 'server',
                'keeps'   => 'no',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[5] = array(
                'type'    => 'error',
                'label'   => 'Key seat limit',
                'message' => 'That license key is already in use on the maximum number of sites it allows - remove it from one of them, or upgrade to a larger license',
                'side'    => 'server',
                'keeps'   => 'no',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[6] = array(
                'type'    => 'error',
                'label'   => 'Domain not approved',
                'message' => 'This domain is not approved for that license key.' . $infinity23_license_support_line,
                'side'    => 'server',
                'keeps'   => 'no',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[7] = array(
                'type'    => 'error',
                'label'   => 'Not active on this domain',
                'message' => 'This domain is not approved for that license key.' . $infinity23_license_support_line,
                'side'    => 'server',
                'keeps'   => 'no',
                'live'    => 'reserved',
            );

            $infinity23_license_status_codes[8] = array(
                'type'    => 'error',
                'label'   => 'Too many tries',
                'message' => 'Too many activation attempts for this key - please wait an hour and try again. Nothing was changed on this site',
                'side'    => 'server',
                'keeps'   => 'yes',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[9] = array(
                'type'    => 'error',
                'label'   => 'Bad request',
                'message' => 'Something went wrong while checking the license and we could not tell why.' . $infinity23_license_support_line,
                'side'    => 'server',
                'keeps'   => 'yes',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[10] = array(
                'type'    => 'success',
                'label'   => 'Deactivated',
                'message' => 'The license was removed from this site - the Pro features are switched off again',
                'side'    => 'server',
                'keeps'   => 'no',
                'live'    => 'yes',
            );

            $infinity23_license_status_codes[11] = array(
                'type'    => 'error',
                'label'   => 'Account full',
                'message' => 'Your account has reached the maximum number of sites it can activate.' . $infinity23_license_support_line,
                'side'    => 'server',
                'keeps'   => 'yes',
                'live'    => 'yes',
            );

            // ---------------------------------------
            // numbers claimed ahead of time. The wording is ready so the day
            // one of these starts being sent, only the server changes - every
            // plugin already installed understands it straight away. A plugin
            // that predates a number shows the "unknown" wording instead, which
            // is why claiming them early is worth doing
            // ---------------------------------------

            $infinity23_license_status_codes[12] = array(
                'type'    => 'error',
                'label'   => 'Payment failed',
                'message' => 'The subscription for this license has a payment problem, so it is paused. Update your payment details and the Pro features come straight back on.',
                'side'    => 'server',
                'keeps'   => 'no',
                'live'    => 'reserved',
            );

            $infinity23_license_status_codes[13] = array(
                'type'    => 'error',
                'label'   => 'Cancelled or refunded',
                'message' => 'This license was cancelled, so it can no longer be used.' . $infinity23_license_support_line,
                'side'    => 'server',
                'keeps'   => 'no',
                'live'    => 'reserved',
            );

            $infinity23_license_status_codes[14] = array(
                'type'    => 'error',
                'label'   => 'Plugin too old',
                'message' => 'This version of the plugin is too old to activate a license. Update Infinity23 to the latest version, then activate again.',
                'side'    => 'server',
                'keeps'   => 'yes',
                'live'    => 'reserved',
            );

            $infinity23_license_status_codes[15] = array(
                'type'    => 'error',
                'label'   => 'Wrong product',
                'message' => 'That license key is for a different product, not Infinity23 - check you copied the right one.',
                'side'    => 'server',
                'keeps'   => 'no',
                'live'    => 'reserved',
            );

            return $infinity23_license_status_codes;

        } // function infinity23_license_status_list() {

    } // if( !function_exists( 'infinity23_license_status_list' ) ) {

?>

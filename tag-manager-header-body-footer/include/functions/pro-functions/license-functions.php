<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// This page talks to the license server and holds the saved license state
// that infinity23_is_pro() reads
// ===========================================================================

// ===========================================================================
// STATUS CODES
//
// Every number below is matched one for one at the top of
// activate-license/index.php on infinity23.com. The server sends ONLY the
// number - all wording lives in infinity23_license_status_message() here, so
// nothing written on the server can ever be printed inside wp-admin.
//
// -2   the saved license was activated for a different domain. Plugin side
//      only, worked out locally with no request at all - see
//      infinity23_license_is_active().
// -1   could not reach the license server. Plugin side only - the server can
//      never send a negative number, so this can never be spoofed.
//  0   unknown or unreadable answer - tell the site owner to contact support.
//      An empty, truncated or nonsense response also lands here, because
//      intval() of anything unexpected is 0.
//  1   activated, or still valid on a re-check
//  2   license key not found
//  3   license key expired
//  4   license key disabled by us
//  5   seat limit reached
//  6   domain not approved for this key
//  7   reserved, never sent yet - see the server file
//  8   too many tries, blocked for an hour
//  9   bad request - the plugin sent something the server rejected
// 10   deactivated
// 11   the account behind the key is out of room (too many keys, or too many
//      domains across them). Different from 5, which is one key being full.
//
// A code that is not in this list is treated as 0. Never reuse a retired
// number - old plugin versions keep the old meaning forever.
// ===========================================================================

    // --------------------------------------------
    // the option row every piece of license state lives in
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_option_name' ) ) {

        function infinity23_license_option_name() {

            return 'license_data';

        } // function infinity23_license_option_name() {

    } // if( !function_exists( 'infinity23_license_option_name' ) ) {

    // --------------------------------------------
    // the saved license state as an array. Reads the already loaded options
    // cache, so this costs no database query no matter how often it is called
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_get_data' ) ) {

        function infinity23_license_get_data() {

            // a global rather than a static, so the two writers below can clear it -
            // a static could not be reset, leaving infinity23_is_pro() answering
            // with the pre-activation state for the rest of that same request
            global $infinity23_license_data_cache;

            if( !isset( $infinity23_license_data_cache ) ) {
                $infinity23_license_data_cache = infinity23_convert_json_to_array( infinity23_get_option( infinity23_license_option_name() ) );
            } // if( !isset( $infinity23_license_data_cache ) ) {

            if( !is_array( $infinity23_license_data_cache ) ) {
                $infinity23_license_data_cache = array();
            } // if( !is_array( $infinity23_license_data_cache ) ) {

            return $infinity23_license_data_cache;

        } // function infinity23_license_get_data() {

    } // if( !function_exists( 'infinity23_license_get_data' ) ) {

    // --------------------------------------------
    // is the license currently good. This is the ONLY thing infinity23_is_pro()
    // asks, and it never touches the network - a site with an expired or missing
    // license simply behaves as free, with no request and no waiting
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_is_active' ) ) {

        function infinity23_license_is_active() {

            return ( infinity23_license_local_state() === 1 );

        } // function infinity23_license_is_active() {

    } // if( !function_exists( 'infinity23_license_is_active' ) ) {

    // --------------------------------------------
    // the license state worked out purely from what is saved here - 1 when it
    // is good, otherwise the status code saying why not. No request, no query,
    // just the options cache, so this is safe to call from anywhere.
    //
    // Returns -2 when the site is not on the domain the license was activated
    // for. That covers a site copied to localhost, moved to a staging
    // subdomain, or moved to a new domain entirely: pro switches off the
    // instant the domain stops matching, with no round trip and no waiting for
    // the next check. The key is still saved, so re-activating on the new
    // domain is one click if that domain is approved.
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_local_state' ) ) {

        function infinity23_license_local_state() {

            $infinity23_license_data = infinity23_license_get_data();

            if( !isset( $infinity23_license_data['status'] ) || intval( $infinity23_license_data['status'] ) !== 1 ) {
                return 0;
            } // if( !isset( $infinity23_license_data['status'] ) || intval( $infinity23_license_data['status'] ) !== 1 ) {

            $infinity23_license_saved_domain = isset( $infinity23_license_data['domain'] ) ? $infinity23_license_data['domain'] : '';

            if( $infinity23_license_saved_domain !== '' && $infinity23_license_saved_domain !== infinity23_license_get_site_identity() ) {
                return -2;
            } // if( $infinity23_license_saved_domain !== '' && $infinity23_license_saved_domain !== infinity23_license_get_site_identity() ) {

            $infinity23_license_expires = isset( $infinity23_license_data['expires'] ) ? intval( $infinity23_license_data['expires'] ) : 0;

            // every licence carries a real date - a perpetual one is written
            // 100 years out rather than left blank, so there is no "0 means
            // forever" case here. A saved licence with no date is incomplete,
            // not perpetual, and the site owner is told to enter the key again
            // rather than quietly being given Pro with no end date
            if( $infinity23_license_expires < 1 ) {
                return -6;
            } // if( $infinity23_license_expires < 1 ) {

            if( $infinity23_license_expires < infinity23_current_timestamp() ) {
                return 3;
            } // if( $infinity23_license_expires < infinity23_current_timestamp() ) {

            // a key IS stored, but it will not decrypt - the encryption master
            // key is gone (a Danger Zone reset, a half restored backup). The
            // raw value has to be checked, not just the decrypted one: empty
            // raw means no key was ever saved, while a full raw that decrypts
            // to nothing means a real key is sitting there unreadable, and the
            // site owner has to be told rather than left wondering
            if( !infinity23_license_key_is_readable() ) {
                return -4;
            } // if( !infinity23_license_key_is_readable() ) {

            return 1;

        } // function infinity23_license_local_state() {

    } // if( !function_exists( 'infinity23_license_local_state' ) ) {

    // --------------------------------------------
    // can the saved key still be decrypted. A stored key that will not decrypt
    // means the encryption master key is gone (a Danger Zone reset, a half
    // restored backup) - the raw value has to be checked too, because an empty
    // raw value means no key was ever saved, which is a different thing.
    //
    // Memoized as a BOOLEAN, not as the key itself: infinity23_is_pro() asks
    // this on every call, and without the memo that is a full AES-256-GCM
    // decrypt dozens of times per admin page. Keeping only the yes/no means
    // the decrypted key is never held in memory any longer than the one call
    // that actually needs to send it.
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_key_is_readable' ) ) {

        function infinity23_license_key_is_readable() {

            global $infinity23_license_key_readable_cache;

            if( isset( $infinity23_license_key_readable_cache ) ) {
                return $infinity23_license_key_readable_cache;
            } // if( isset( $infinity23_license_key_readable_cache ) ) {

            $infinity23_license_data = infinity23_license_get_data();

            // nothing saved at all - not the same as unreadable, and not this
            // function's problem to report
            if( empty( $infinity23_license_data['key'] ) ) {
                $infinity23_license_key_readable_cache = true;
                return true;
            } // if( empty( $infinity23_license_data['key'] ) ) {

            $infinity23_license_key_readable_cache = ( infinity23_license_get_key() !== '' );

            return $infinity23_license_key_readable_cache;

        } // function infinity23_license_key_is_readable() {

    } // if( !function_exists( 'infinity23_license_key_is_readable' ) ) {

    // --------------------------------------------
    // the saved license key, decrypted. Only called when a key actually has to
    // be sent to the server - never stored back anywhere in decrypted form
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_get_key' ) ) {

        function infinity23_license_get_key() {

            $infinity23_license_data = infinity23_license_get_data();

            if( empty( $infinity23_license_data['key'] ) ) {
                return '';
            } // if( empty( $infinity23_license_data['key'] ) ) {

            return infinity23_decrypt_value( $infinity23_license_data['key'], 'license_key' );

        } // function infinity23_license_get_key() {

    } // if( !function_exists( 'infinity23_license_get_key' ) ) {

    // --------------------------------------------
    // the saved key with the middle hidden, for showing which key is in use
    // without printing the whole thing into the page source
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_mask_key' ) ) {

        function infinity23_license_mask_key( $infinity23_license_key ) {

            $infinity23_license_key = trim( $infinity23_license_key );

            if( $infinity23_license_key === '' ) {
                return '';
            } // if( $infinity23_license_key === '' ) {

            if( strlen( $infinity23_license_key ) <= 10 ) {
                return str_repeat( '*', strlen( $infinity23_license_key ) );
            } // if( strlen( $infinity23_license_key ) <= 10 ) {

            return substr( $infinity23_license_key, 0, 6 ) . str_repeat( '*', 8 ) . substr( $infinity23_license_key, -4 );

        } // function infinity23_license_mask_key( $infinity23_license_key ) {

    } // if( !function_exists( 'infinity23_license_mask_key' ) ) {

    // --------------------------------------------
    // the domain this license is being used on - host only, lowercase, no www
    // and no port, so the same site always reports the same string.
    //
    // Memoized, like every other read in this file: infinity23_is_pro() lands
    // here dozens of times on one admin page, and home_url() is not free - it
    // fires the home_url filter every call, which translation plugins hook
    // heavily. The answer cannot change inside one request
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_get_domain' ) ) {

        function infinity23_license_get_domain() {

            global $infinity23_license_domain_cache;

            if( isset( $infinity23_license_domain_cache ) ) {
                return $infinity23_license_domain_cache;
            } // if( isset( $infinity23_license_domain_cache ) ) {

            $infinity23_license_host = wp_parse_url( home_url(), PHP_URL_HOST );

            if( empty( $infinity23_license_host ) ) {
                $infinity23_license_domain_cache = '';
                return '';
            } // if( empty( $infinity23_license_host ) ) {

            $infinity23_license_host = strtolower( $infinity23_license_host );

            if( strpos( $infinity23_license_host, 'www.' ) === 0 ) {
                $infinity23_license_host = substr( $infinity23_license_host, 4 );
            } // if( strpos( $infinity23_license_host, 'www.' ) === 0 ) {

            $infinity23_license_domain_cache = $infinity23_license_host;

            return $infinity23_license_host;

        } // function infinity23_license_get_domain() {

    } // if( !function_exists( 'infinity23_license_get_domain' ) ) {

    // --------------------------------------------
    // is this a development copy rather than a real site.
    //
    // Worked out from the domain alone, and the license server works the same
    // thing out for itself from the domain it receives - this is never sent as
    // a claim the server has to believe. The names below cannot be bought or
    // pointed at a public server, so a real site can never land here by
    // accident.
    //
    // A local site runs Pro on any valid key without using up a seat, so a
    // developer's copies of their client sites cost them nothing.
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_is_local_site' ) ) {

        function infinity23_license_is_local_site() {

            global $infinity23_license_is_local_cache;

            if( isset( $infinity23_license_is_local_cache ) ) {
                return $infinity23_license_is_local_cache;
            } // if( isset( $infinity23_license_is_local_cache ) ) {

            $infinity23_license_is_local_cache = infinity23_license_work_out_is_local();

            return $infinity23_license_is_local_cache;

        } // function infinity23_license_is_local_site() {

    } // if( !function_exists( 'infinity23_license_is_local_site' ) ) {

    // --------------------------------------------
    // the actual test behind infinity23_license_is_local_site(), split out only
    // so that function stays a plain memo
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_work_out_is_local' ) ) {

        function infinity23_license_work_out_is_local() {

            $infinity23_license_host = infinity23_license_get_domain();

            if( in_array( $infinity23_license_host, array( 'localhost', '127.0.0.1', '::1', '[::1]' ), true ) ) {
                return true;
            } // if( in_array( $infinity23_license_host, array( 'localhost', '127.0.0.1', '::1', '[::1]' ), true ) ) {

            $infinity23_local_endings = array( '.localhost', '.local', '.test', '.ddev.site', '.lndo.site' );

            foreach( $infinity23_local_endings as $infinity23_single_ending ) {

                if( substr( $infinity23_license_host, - strlen( $infinity23_single_ending ) ) === $infinity23_single_ending ) {
                    return true;
                } // if( substr( $infinity23_license_host, - strlen( $infinity23_single_ending ) ) === $infinity23_single_ending ) {

            } // foreach( $infinity23_local_endings as $infinity23_single_ending ) {

            if( filter_var( $infinity23_license_host, FILTER_VALIDATE_IP ) !== false ) {

                if( filter_var( $infinity23_license_host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
                    return true;
                } // if( filter_var( ... FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {

            } // if( filter_var( $infinity23_license_host, FILTER_VALIDATE_IP ) !== false ) {

            return false;

        } // function infinity23_license_work_out_is_local() {

    } // if( !function_exists( 'infinity23_license_work_out_is_local' ) ) {

    // --------------------------------------------
    // the folder this install sits in, for a local site only.
    //
    // Ten local copies all answer as plain "localhost", which makes them
    // impossible to tell apart. The path is what actually separates them
    // (localhost/Admin-Websites/wp-school.co.il), so it is sent for local
    // sites so each one gets its own rate limit bucket instead of all ten
    // sharing localhost's.
    //
    // NOT sent for a real site - the host is enough there, and a customer's
    // folder layout is none of our business.
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_get_site_path' ) ) {

        function infinity23_license_get_site_path() {

            global $infinity23_license_path_cache;

            if( isset( $infinity23_license_path_cache ) ) {
                return $infinity23_license_path_cache;
            } // if( isset( $infinity23_license_path_cache ) ) {

            $infinity23_license_path_cache = '';

            if( !infinity23_license_is_local_site() ) {
                return '';
            } // if( !infinity23_license_is_local_site() ) {

            $infinity23_license_path = wp_parse_url( home_url(), PHP_URL_PATH );

            if( empty( $infinity23_license_path ) ) {
                return '';
            } // if( empty( $infinity23_license_path ) ) {

            $infinity23_license_path = strtolower( trim( $infinity23_license_path, '/' ) );

            // only characters a folder name can have
            $infinity23_license_path = preg_replace( '/[^a-z0-9\.\-_\/]/', '', $infinity23_license_path );

            // the server only reads 100 characters, and the LAST folder is the
            // one that names the site - so drop whole folders off the FRONT
            // until it fits, never cut the end. Cutting the end would turn
            // .../clients/wp-school.co.il into .../clients/wp-schoo and refuse
            // a perfectly good install with "this domain is not approved"
            if( strlen( $infinity23_license_path ) > 100 ) {

                $infinity23_path_folders = explode( '/', $infinity23_license_path );
                $infinity23_kept_folders = array();
                $infinity23_kept_length = 0;

                foreach( array_reverse( $infinity23_path_folders ) as $infinity23_single_folder ) {

                    $infinity23_needed_length = ( $infinity23_kept_length === 0 ) ? strlen( $infinity23_single_folder ) : $infinity23_kept_length + 1 + strlen( $infinity23_single_folder );

                    if( $infinity23_needed_length > 100 ) {
                        break;
                    } // if( $infinity23_needed_length > 100 ) {

                    array_unshift( $infinity23_kept_folders, $infinity23_single_folder );
                    $infinity23_kept_length = $infinity23_needed_length;

                } // foreach( array_reverse( $infinity23_path_folders ) as $infinity23_single_folder ) {

                if( empty( $infinity23_kept_folders ) ) {
                    // one folder longer than the whole cap on its own - nothing
                    // to do but cut it, and it was never going to match anyway
                    $infinity23_license_path = substr( end( $infinity23_path_folders ), 0, 100 );
                } else {
                    $infinity23_license_path = implode( '/', $infinity23_kept_folders );
                } // if( empty( $infinity23_kept_folders ) ) {

            } // if( strlen( $infinity23_license_path ) > 100 ) {

            $infinity23_license_path_cache = $infinity23_license_path;

            return $infinity23_license_path;

        } // function infinity23_license_get_site_path() {

    } // if( !function_exists( 'infinity23_license_get_site_path' ) ) {

    // --------------------------------------------
    // what this install is LOCKED to - the thing that has to still match for a
    // saved license to keep working here.
    //
    // For a real site that is just the domain. For a local one it has to be
    // the domain AND the folder: ten local installs all answer as plain
    // "localhost", so locking to the domain alone would let a license
    // activated in one folder keep working in every other folder on that
    // machine. Locking to localhost/admin-websites/wp-school.co.il means one
    // install, in one folder, exactly as the server approved it.
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_get_site_identity' ) ) {

        function infinity23_license_get_site_identity() {

            $infinity23_license_identity = infinity23_license_get_domain();
            $infinity23_license_path = infinity23_license_get_site_path();

            if( $infinity23_license_path !== '' ) {
                $infinity23_license_identity .= '/' . $infinity23_license_path;
            } // if( $infinity23_license_path !== '' ) {

            return $infinity23_license_identity;

        } // function infinity23_license_get_site_identity() {

    } // if( !function_exists( 'infinity23_license_get_site_identity' ) ) {

    // --------------------------------------------
    // a random id for this one WordPress install, made once and kept. Lets the
    // server tell two installs on the same domain apart (a staging copy in a
    // subfolder, a multisite subsite) without us sending any site content
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_get_install_id' ) ) {

        function infinity23_license_get_install_id() {

            $infinity23_install_id = infinity23_get_option( 'license_install_id' );

            if( is_string( $infinity23_install_id ) && preg_match( '/^[a-f0-9]{32}$/', $infinity23_install_id ) === 1 ) {
                return $infinity23_install_id;
            } // if( is_string( $infinity23_install_id ) && preg_match( ... ) === 1 ) {

            $infinity23_install_id = bin2hex( random_bytes( 16 ) );

            infinity23_update_option( 'license_install_id', $infinity23_install_id );

            return $infinity23_install_id;

        } // function infinity23_license_get_install_id() {

    } // if( !function_exists( 'infinity23_license_get_install_id' ) ) {

    // --------------------------------------------
    // read one number out of the server's answer, or null when the value is not
    // a plain whole number. The shape has to be checked BEFORE intval() runs:
    // intval() of array( 1 ) is 1, and intval() of '1; DROP TABLE x' is also 1,
    // so a bare intval() would quietly accept both as status 1
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_read_number' ) ) {

        function infinity23_license_read_number( $infinity23_license_value ) {

            if( is_int( $infinity23_license_value ) ) {
                return $infinity23_license_value;
            } // if( is_int( $infinity23_license_value ) ) {

            // a 32 bit php build cannot hold a timestamp past January 2038, so
            // json_decode() hands back a FLOAT for anything bigger - which a
            // licence expiring 100 years out always is. Take it when it is a
            // whole number and clamp it to what this build can actually store:
            // a date further away than the build can count to is still "not
            // expired", so clamping reads correctly either way
            if( is_float( $infinity23_license_value ) && !is_nan( $infinity23_license_value ) && !is_infinite( $infinity23_license_value ) && floor( $infinity23_license_value ) === $infinity23_license_value ) {

                if( $infinity23_license_value >= PHP_INT_MAX ) {
                    return PHP_INT_MAX;
                } // if( $infinity23_license_value >= PHP_INT_MAX ) {

                if( $infinity23_license_value <= PHP_INT_MIN ) {
                    return PHP_INT_MIN;
                } // if( $infinity23_license_value <= PHP_INT_MIN ) {

                return intval( $infinity23_license_value );

            } // if( is_float( ... ) && floor( ... ) === $infinity23_license_value ) {

            if( is_string( $infinity23_license_value ) && preg_match( '/^-?[0-9]{1,12}$/', $infinity23_license_value ) === 1 ) {
                return intval( $infinity23_license_value );
            } // if( is_string( $infinity23_license_value ) && preg_match( ... ) === 1 ) {

            return null;

        } // function infinity23_license_read_number( $infinity23_license_value ) {

    } // if( !function_exists( 'infinity23_license_read_number' ) ) {

    // ===========================================================================
    // Message encryption
    //
    // Both directions are AES-256-GCM, on top of the https the request already
    // travels over. GCM is authenticated encryption, so the same step that
    // hides the message also proves who wrote it - there is no separate
    // signature to get wrong.
    //
    // Request and answer use DIFFERENT derived keys from the same shared
    // secret, so an answer captured off the wire can never be replayed back at
    // the server as a request.
    //
    // Worth being honest about the limit: this plugin ships to every customer,
    // so the shared secret sits in readable php on their server. This is not a
    // password and it does not make the plugin uncrackable. What it buys is
    // that nothing sitting between the site and the server can read or forge a
    // license message - including a fake server pointed at by a hosts file.
    // ===========================================================================

    // --------------------------------------------
    // the key for one direction. 'request' and 'answer' derive differently on
    // purpose - see above
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_message_key' ) ) {

        function infinity23_license_message_key( $infinity23_license_direction ) {

            return hash( 'sha256', INFINITY23_LICENSE_SHARED_SECRET . '|' . $infinity23_license_direction . '|' . intval( INFINITY23_LICENSE_KEY_VERSION ), true );

        } // function infinity23_license_message_key( $infinity23_license_direction ) {

    } // if( !function_exists( 'infinity23_license_message_key' ) ) {

    // --------------------------------------------
    // can this WordPress host encrypt at all. Checked before anything is sent,
    // so a host without it gets its own clear answer instead of a failed
    // request it cannot explain
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_can_encrypt' ) ) {

        function infinity23_license_can_encrypt() {

            if( !function_exists( 'openssl_encrypt' ) || !function_exists( 'openssl_decrypt' ) || !function_exists( 'openssl_get_cipher_methods' ) ) {
                return false;
            } // if( !function_exists( 'openssl_encrypt' ) || ... ) {

            return in_array( 'aes-256-gcm', openssl_get_cipher_methods(), true );

        } // function infinity23_license_can_encrypt() {

    } // if( !function_exists( 'infinity23_license_can_encrypt' ) ) {

    // --------------------------------------------
    // turn an array into one base64 blob for sending, or '' if it cannot be done
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_encrypt_message' ) ) {

        function infinity23_license_encrypt_message( $infinity23_message_array, $infinity23_license_direction ) {

            if( !infinity23_license_can_encrypt() ) {
                return '';
            } // if( !infinity23_license_can_encrypt() ) {

            $infinity23_message_iv = random_bytes( 12 );
            $infinity23_message_tag = '';

            $infinity23_message_encrypted = openssl_encrypt( wp_json_encode( $infinity23_message_array ), 'aes-256-gcm', infinity23_license_message_key( $infinity23_license_direction ), OPENSSL_RAW_DATA, $infinity23_message_iv, $infinity23_message_tag, '', 16 );

            if( $infinity23_message_encrypted === false || empty( $infinity23_message_tag ) ) {
                return '';
            } // if( $infinity23_message_encrypted === false || empty( $infinity23_message_tag ) ) {

            return base64_encode( $infinity23_message_iv . $infinity23_message_tag . $infinity23_message_encrypted );

        } // function infinity23_license_encrypt_message( $infinity23_message_array, $infinity23_license_direction ) {

    } // if( !function_exists( 'infinity23_license_encrypt_message' ) ) {

    // --------------------------------------------
    // read one base64 blob back into an array, or null when it cannot be read.
    // A blob that was tampered with fails the gcm tag check and lands on null,
    // never on something half decoded
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_decrypt_message' ) ) {

        function infinity23_license_decrypt_message( $infinity23_message_blob, $infinity23_license_direction ) {

            if( !infinity23_license_can_encrypt() || !is_string( $infinity23_message_blob ) ) {
                return null;
            } // if( !infinity23_license_can_encrypt() || !is_string( $infinity23_message_blob ) ) {

            $infinity23_message_raw = base64_decode( $infinity23_message_blob, true );

            // 12 byte iv + 16 byte tag, so anything shorter cannot be one of ours
            if( $infinity23_message_raw === false || strlen( $infinity23_message_raw ) < 29 ) {
                return null;
            } // if( $infinity23_message_raw === false || strlen( $infinity23_message_raw ) < 29 ) {

            $infinity23_message_decrypted = openssl_decrypt( substr( $infinity23_message_raw, 28 ), 'aes-256-gcm', infinity23_license_message_key( $infinity23_license_direction ), OPENSSL_RAW_DATA, substr( $infinity23_message_raw, 0, 12 ), substr( $infinity23_message_raw, 12, 16 ) );

            if( $infinity23_message_decrypted === false ) {
                return null;
            } // if( $infinity23_message_decrypted === false ) {

            $infinity23_message_array = json_decode( $infinity23_message_decrypted, true );

            return is_array( $infinity23_message_array ) ? $infinity23_message_array : null;

        } // function infinity23_license_decrypt_message( $infinity23_message_blob, $infinity23_license_direction ) {

    } // if( !function_exists( 'infinity23_license_decrypt_message' ) ) {

    // --------------------------------------------
    // is this site allowed to make outbound requests at all. WordPress can be
    // told to block every external call in wp-config.php, and when it is, the
    // fix is on the site owner's side, not ours - so it gets its own answer
    // rather than looking like our server being down
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_external_requests_blocked' ) ) {

        function infinity23_license_external_requests_blocked() {

            if( !defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || !WP_HTTP_BLOCK_EXTERNAL ) {
                return false;
            } // if( !defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || !WP_HTTP_BLOCK_EXTERNAL ) {

            if( !defined( 'WP_ACCESSIBLE_HOSTS' ) ) {
                return true;
            } // if( !defined( 'WP_ACCESSIBLE_HOSTS' ) ) {

            $infinity23_license_server_host = wp_parse_url( INFINITY23_LICENSE_SERVER_URL, PHP_URL_HOST );

            foreach( explode( ',', WP_ACCESSIBLE_HOSTS ) as $infinity23_allowed_host ) {

                $infinity23_allowed_host = strtolower( trim( $infinity23_allowed_host ) );

                if( $infinity23_allowed_host === '' ) {
                    continue;
                } // if( $infinity23_allowed_host === '' ) {

                // WP_ACCESSIBLE_HOSTS allows a *.example.com style wildcard
                if( strpos( $infinity23_allowed_host, '*.' ) === 0 ) {

                    if( substr( strtolower( $infinity23_license_server_host ), - ( strlen( $infinity23_allowed_host ) - 1 ) ) === substr( $infinity23_allowed_host, 1 ) ) {
                        return false;
                    } // if( substr( ... ) === substr( $infinity23_allowed_host, 1 ) ) {

                    continue;

                } // if( strpos( $infinity23_allowed_host, '*.' ) === 0 ) {

                if( $infinity23_allowed_host === strtolower( $infinity23_license_server_host ) ) {
                    return false;
                } // if( $infinity23_allowed_host === strtolower( $infinity23_license_server_host ) ) {

            } // foreach( explode( ',', WP_ACCESSIBLE_HOSTS ) as $infinity23_allowed_host ) {

            return true;

        } // function infinity23_license_external_requests_blocked() {

    } // if( !function_exists( 'infinity23_license_external_requests_blocked' ) ) {

    // --------------------------------------------
    // ask the license server about a key. Returns an array with three integers
    // and nothing else, whatever the server actually replied.
    //
    // This function is the single gate between their server and this plugin.
    // Everything it enforces matters, so keep all of it:
    //  - the answer must decrypt with our own shared secret, so a forged or
    //    tampered answer never gets read at all
    //  - the nonce in the answer must be the one we just sent, so an answer
    //    captured from another site cannot be replayed at this one
    //  - only 'status', 'expires' and 'plan' are ever read out, each through
    //    infinity23_license_read_number()
    //  - the body is capped at 1kb, by WordPress and again here
    // Never add a field that carries text, html or a url - a message the server
    // controls is a message that can be printed in somebody's wp-admin.
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_request' ) ) {

        function infinity23_license_request( $infinity23_license_action, $infinity23_license_key ) {

            $infinity23_license_answer = array( 'status' => 0, 'expires' => 0, 'plan' => 0 );

            if( infinity23_license_external_requests_blocked() ) {
                $infinity23_license_answer['status'] = -3;
                return $infinity23_license_answer;
            } // if( infinity23_license_external_requests_blocked() ) {

            // never quietly fall back to sending this in the clear - an
            // attacker who can strip the encryption is exactly who that would
            // help. A host that cannot do it gets told so instead
            if( !infinity23_license_can_encrypt() ) {
                $infinity23_license_answer['status'] = -5;
                return $infinity23_license_answer;
            } // if( !infinity23_license_can_encrypt() ) {

            $infinity23_license_domain = infinity23_license_get_domain();

            if( $infinity23_license_domain === '' || $infinity23_license_key === '' ) {
                $infinity23_license_answer['status'] = 9;
                return $infinity23_license_answer;
            } // if( $infinity23_license_domain === '' || $infinity23_license_key === '' ) {

            // a fresh nonce per request - the answer has to carry the same one
            // back or it is not an answer to this question
            $infinity23_license_nonce = bin2hex( random_bytes( 16 ) );

            $infinity23_license_message = array(
                'api_version'    => intval( INFINITY23_LICENSE_API_VERSION ),
                'license_action' => $infinity23_license_action,
                'license_key'    => $infinity23_license_key,
                'domain'         => $infinity23_license_domain,
                'install_id'     => infinity23_license_get_install_id(),
                'plugin_version' => INFINITY23_VERSION,
                'site_path'      => infinity23_license_get_site_path(),
                'nonce'          => $infinity23_license_nonce,
                'time'           => time(),
            );

            $infinity23_license_blob = infinity23_license_encrypt_message( $infinity23_license_message, 'request' );

            if( $infinity23_license_blob === '' ) {
                $infinity23_license_answer['status'] = -5;
                return $infinity23_license_answer;
            } // if( $infinity23_license_blob === '' ) {

            // only the key version travels in the clear, so the server knows
            // which secret to open the blob with. Everything else is inside it
            $infinity23_license_response = wp_remote_post( INFINITY23_LICENSE_SERVER_URL, array(
                'timeout'             => 15,
                'redirection'         => 0,
                'sslverify'           => true,
                'limit_response_size' => 1024,
                'body'                => array(
                    'v' => intval( INFINITY23_LICENSE_KEY_VERSION ),
                    'd' => $infinity23_license_blob,
                ),
            ) );

            // the server was not reachable at all - a negative code the server
            // itself can never send, so this stays clearly separate from a real
            // refusal and the caller knows not to change anything locally
            if( is_wp_error( $infinity23_license_response ) ) {
                $infinity23_license_answer['status'] = -1;
                return $infinity23_license_answer;
            } // if( is_wp_error( $infinity23_license_response ) ) {

            if( intval( wp_remote_retrieve_response_code( $infinity23_license_response ) ) !== 200 ) {
                $infinity23_license_answer['status'] = -1;
                return $infinity23_license_answer;
            } // if( intval( wp_remote_retrieve_response_code( $infinity23_license_response ) ) !== 200 ) {

            $infinity23_license_body = wp_remote_retrieve_body( $infinity23_license_response );

            // a real answer is a few hundred characters - anything bigger is
            // not something this plugin is willing to look inside
            if( !is_string( $infinity23_license_body ) || strlen( $infinity23_license_body ) > 1024 ) {
                return $infinity23_license_answer;
            } // if( !is_string( $infinity23_license_body ) || strlen( $infinity23_license_body ) > 1024 ) {

            $infinity23_license_outer = json_decode( $infinity23_license_body, true );

            if( !is_array( $infinity23_license_outer ) || !isset( $infinity23_license_outer['d'] ) ) {
                return $infinity23_license_answer;
            } // if( !is_array( $infinity23_license_outer ) || !isset( $infinity23_license_outer['d'] ) ) {

            $infinity23_license_decoded = infinity23_license_decrypt_message( $infinity23_license_outer['d'], 'answer' );

            // could not open it with our own secret, so whoever answered is not
            // our license server - treat it as no answer at all
            if( $infinity23_license_decoded === null || !isset( $infinity23_license_decoded['status'] ) ) {
                return $infinity23_license_answer;
            } // if( $infinity23_license_decoded === null || !isset( $infinity23_license_decoded['status'] ) ) {

            // an answer to somebody else's question, replayed at this site
            $infinity23_license_answer_nonce = isset( $infinity23_license_decoded['nonce'] ) && is_string( $infinity23_license_decoded['nonce'] ) ? $infinity23_license_decoded['nonce'] : '';

            if( !hash_equals( $infinity23_license_nonce, $infinity23_license_answer_nonce ) ) {
                return $infinity23_license_answer;
            } // if( !hash_equals( $infinity23_license_nonce, $infinity23_license_answer_nonce ) ) {

            $infinity23_license_status = infinity23_license_read_number( $infinity23_license_decoded['status'] );

            // a code this version does not know about is treated as unknown
            // rather than guessed at. A negative number can only come from this
            // file, so one arriving over the wire is refused here too.
            if( $infinity23_license_status === null || !in_array( $infinity23_license_status, array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15 ), true ) ) {
                return $infinity23_license_answer;
            } // if( $infinity23_license_status === null || !in_array( ... ) ) {

            $infinity23_license_answer['status'] = $infinity23_license_status;

            if( isset( $infinity23_license_decoded['expires'] ) ) {

                $infinity23_license_expires = infinity23_license_read_number( $infinity23_license_decoded['expires'] );

                if( $infinity23_license_expires !== null ) {
                    $infinity23_license_answer['expires'] = max( 0, $infinity23_license_expires );
                } // if( $infinity23_license_expires !== null ) {

            } // if( isset( $infinity23_license_decoded['expires'] ) ) {

            if( isset( $infinity23_license_decoded['plan'] ) ) {

                $infinity23_license_plan = infinity23_license_read_number( $infinity23_license_decoded['plan'] );

                if( $infinity23_license_plan !== null ) {
                    $infinity23_license_answer['plan'] = max( 0, $infinity23_license_plan );
                } // if( $infinity23_license_plan !== null ) {

            } // if( isset( $infinity23_license_decoded['plan'] ) ) {

            return $infinity23_license_answer;

        } // function infinity23_license_request( $infinity23_license_action, $infinity23_license_key ) {

    } // if( !function_exists( 'infinity23_license_request' ) ) {

    // --------------------------------------------
    // write the license state. Called only from the two functions below, so
    // there is one place that decides what "active" looks like on disk
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_save_data' ) ) {

        function infinity23_license_save_data( $infinity23_license_key, $infinity23_license_answer ) {

            $infinity23_license_data_array = array();

            $infinity23_license_data_array['status']  = intval( $infinity23_license_answer['status'] ) === 1 ? 1 : 0;
            $infinity23_license_data_array['expires'] = intval( $infinity23_license_answer['expires'] );
            $infinity23_license_data_array['plan']    = intval( $infinity23_license_answer['plan'] );
            $infinity23_license_data_array['checked'] = infinity23_current_timestamp();
            // the domain this key was approved for. If the site later answers
            // from a different domain - a copy on localhost, a move to a
            // subdomain - infinity23_license_local_state() sees the mismatch and
            // turns pro off locally, without asking the server anything
            $infinity23_license_data_array['domain']  = infinity23_license_get_site_identity();
            $infinity23_license_data_array['key']     = $infinity23_license_key === '' ? '' : infinity23_encrypt_value( $infinity23_license_key, 'license_key' );

            infinity23_update_option( infinity23_license_option_name(), infinity23_convert_array_to_json( $infinity23_license_data_array ) );

            global $infinity23_license_data_cache;
            global $infinity23_license_key_readable_cache;
            $infinity23_license_data_cache = $infinity23_license_data_array;
            $infinity23_license_key_readable_cache = null;

        } // function infinity23_license_save_data( $infinity23_license_key, $infinity23_license_answer ) {

    } // if( !function_exists( 'infinity23_license_save_data' ) ) {

    // --------------------------------------------
    // activate a key typed in by the site owner. Returns the status code so the
    // calling page can show the matching message.
    //
    // Anything other than a clear yes or a clear no leaves the saved license
    // exactly as it was: -1 (server unreachable), 0 (unreadable answer) and 8
    // (rate limited) must never switch a working license off, or a minute of
    // downtime on our end turns pro off on every customer site at once.
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_activate' ) ) {

        function infinity23_license_activate( $infinity23_license_key ) {

            $infinity23_license_key = strtoupper( trim( $infinity23_license_key ) );

            if( $infinity23_license_key === '' || preg_match( '/^[A-Z0-9\-]{8,64}$/', $infinity23_license_key ) !== 1 ) {
                return 2;
            } // if( $infinity23_license_key === '' || preg_match( ... ) !== 1 ) {

            $infinity23_license_answer = infinity23_license_request( 'activate', $infinity23_license_key );

            if( $infinity23_license_answer['status'] === 1 ) {

                infinity23_license_save_data( $infinity23_license_key, $infinity23_license_answer );
                return 1;

            } // if( $infinity23_license_answer['status'] === 1 ) {

            // whether a saved license survives this answer is read straight off
            // that code's own 'keeps' entry in infinity23_license_status_list(),
            // so the list is the single place that decides it - a new code can
            // never be added without saying what it does to a saved license.
            // A code we somehow do not know is treated as "keep", which is
            // always the safe direction
            $infinity23_license_status_codes = infinity23_license_status_list();
            $infinity23_license_keeps = isset( $infinity23_license_status_codes[ $infinity23_license_answer['status'] ]['keeps'] ) ? $infinity23_license_status_codes[ $infinity23_license_answer['status'] ]['keeps'] : 'yes';

            // a real refusal about a real key - drop whatever was saved so the
            // site stops behaving as pro straight away. The row is removed rather
            // than zeroed, so "no license here" always looks the same on disk
            if( $infinity23_license_keeps === 'no' ) {

                infinity23_delete_option( infinity23_license_option_name() );

                global $infinity23_license_data_cache;
                global $infinity23_license_key_readable_cache;
                $infinity23_license_data_cache = array();
                $infinity23_license_key_readable_cache = null;

                return $infinity23_license_answer['status'];

            } // if( $infinity23_license_keeps === 'no' ) {

            return $infinity23_license_answer['status'];

        } // function infinity23_license_activate( $infinity23_license_key ) {

    } // if( !function_exists( 'infinity23_license_activate' ) ) {

    // --------------------------------------------
    // give the license up on this site. The local row is cleared whatever the
    // server answers - the site owner asked to stop using the key here, so a
    // server that is momentarily down must not keep them locked in
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_deactivate' ) ) {

        function infinity23_license_deactivate() {

            $infinity23_license_key = infinity23_license_get_key();

            if( $infinity23_license_key !== '' ) {
                infinity23_license_request( 'deactivate', $infinity23_license_key );
            } // if( $infinity23_license_key !== '' ) {

            infinity23_delete_option( infinity23_license_option_name() );

            global $infinity23_license_data_cache;
            global $infinity23_license_key_readable_cache;
            $infinity23_license_data_cache = array();
            $infinity23_license_key_readable_cache = null;

            return 10;

        } // function infinity23_license_deactivate() {

    } // if( !function_exists( 'infinity23_license_deactivate' ) ) {

    // --------------------------------------------
    // the wording for one status code, read out of the list in
    // include/functions/pro-functions/license-status-codes.php - which is the only file the
    // sentences themselves live in. A code that is not in that list falls back
    // to the unknown wording rather than showing a site owner nothing at all
    // --------------------------------------------

    if( !function_exists( 'infinity23_license_status_message' ) ) {

        function infinity23_license_status_message( $infinity23_license_status ) {

            $infinity23_license_status_codes = infinity23_license_status_list();
            $infinity23_license_status = intval( $infinity23_license_status );

            if( !isset( $infinity23_license_status_codes[ $infinity23_license_status ] ) ) {
                $infinity23_license_status = 0;
            } // if( !isset( $infinity23_license_status_codes[ $infinity23_license_status ] ) ) {

            return array(
                'type'    => $infinity23_license_status_codes[ $infinity23_license_status ]['type'],
                'message' => $infinity23_license_status_codes[ $infinity23_license_status ]['message'],
            );

        } // function infinity23_license_status_message( $infinity23_license_status ) {

    } // if( !function_exists( 'infinity23_license_status_message' ) ) {

?>

<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// This page holds global functions to encrypt and decrypt sensitive values
// (passwords, API secrets, etc) before saving them to the database
// ===========================================================================

	// --------------------------------------------
	// the plugin's own master key - a random value generated once on first use
	// and kept in the plugin's own options table. Deliberately NOT derived from
	// wp_salt(): rotating the WordPress salt keys, or moving the site without
	// wp-config.php, would change that value and every saved password would
	// stop decrypting. This key only ever changes if the plugin's own data is
	// reset, so saved credentials keep working through a salt change or a move
	// --------------------------------------------

	if( !function_exists( 'infinity23_encryption_get_master_key' ) ) {

		function infinity23_encryption_get_master_key() {

			// a key kept in the same table as the ciphertext is decrypted by the
			// same database dump that leaks the ciphertext. Defining this in
			// wp-config.php moves it out of the database entirely:
			// define( 'INFINITY23_ENCRYPTION_KEY', 'a long random string' );
			if( defined( 'INFINITY23_ENCRYPTION_KEY' ) && !empty( INFINITY23_ENCRYPTION_KEY ) ) {
				return strval( INFINITY23_ENCRYPTION_KEY );
			} // if( defined( 'INFINITY23_ENCRYPTION_KEY' ) && !empty( INFINITY23_ENCRYPTION_KEY ) ) {

			$infinity23_master_key = infinity23_get_option( 'encryption_master_key' );

			if( !empty( $infinity23_master_key ) ) {
				return $infinity23_master_key;
			} // if( !empty( $infinity23_master_key ) ) {

			$infinity23_master_key = bin2hex( random_bytes( 32 ) );
			infinity23_update_option( 'encryption_master_key', $infinity23_master_key );

			return $infinity23_master_key;

		} // function infinity23_encryption_get_master_key() {

	} // if( !function_exists( 'infinity23_encryption_get_master_key' ) ) {

	// --------------------------------------------
	// derive a unique key per context, so different modules never share the
	// exact same encryption key even though they all derive from one master key
	// --------------------------------------------

	if( !function_exists( 'infinity23_encryption_get_key' ) ) {

		function infinity23_encryption_get_key( $context ) {

			$context = !empty( $context ) ? strval( $context ) : 'default';

			return hash( 'sha256', infinity23_encryption_get_master_key() . '|' . $context, true );

		} // function infinity23_encryption_get_key( $context ) {

	} // if( !function_exists( 'infinity23_encryption_get_key' ) ) {

	// --------------------------------------------
	// encrypt a plain text value before saving - returns a prefixed base64
	// string, or the original value unchanged if openssl is unavailable.
	// Uses AES-256-GCM (authenticated encryption) - the auth tag is stored
	// alongside the ciphertext so a tampered value fails to decrypt instead
	// of silently returning garbage. $context should be a short unique
	// string per field/module (e.g. 'email_smtp_password') so each field
	// gets its own encryption key - the SAME $context must be passed again
	// when decrypting that value.
	// --------------------------------------------

	if( !function_exists( 'infinity23_encrypt_value' ) ) {

		function infinity23_encrypt_value( $plain_value, $context = 'default' ) {

			if( empty( $plain_value ) ) {
				return '';
			} // if( empty( $plain_value ) ) {

			// the fallback is marked, never bare. Without the marker a stored
			// password looks identical to an encrypted one, so nothing can tell
			// that this host saved it in the clear and nothing ever re-encrypts it
			if( !function_exists( 'openssl_encrypt' ) || !in_array( 'aes-256-gcm', openssl_get_cipher_methods(), true ) ) {
				return 'infinity23_plain:' . $plain_value;
			} // if( !function_exists( 'openssl_encrypt' ) ...

			$key = infinity23_encryption_get_key( $context );
			$iv = random_bytes( 12 );
			$tag = '';
			$encrypted = openssl_encrypt( $plain_value, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16 );

			if( $encrypted === false || empty( $tag ) ) {
				return 'infinity23_plain:' . $plain_value;
			} // if( $encrypted === false || empty( $tag ) ) {

			return 'infinity23_enc2:' . base64_encode( $iv . $tag . $encrypted );

		} // function infinity23_encrypt_value( $plain_value, $context = 'default' ) {

	} // if( !function_exists( 'infinity23_encrypt_value' ) ) {

	// --------------------------------------------
	// decrypt a value saved by infinity23_encrypt_value() - returns the value
	// unchanged if it was never encrypted (old plain text data), so existing
	// saved values keep working until the field is resaved. Understands both
	// the new AES-256-GCM format ('infinity23_enc2:') and the OLD AES-256-CBC
	// format ('infinity23_enc:') saved before this upgrade, so migrating never
	// resets an existing saved credential. $context MUST match exactly what
	// was passed to infinity23_encrypt_value() for this value, otherwise
	// decryption fails and an empty string is returned.
	// --------------------------------------------

	if( !function_exists( 'infinity23_decrypt_value' ) ) {

		function infinity23_decrypt_value( $stored_value, $context = 'default' ) {

			if( empty( $stored_value ) ) {
				return '';
			} // if( empty( $stored_value ) ) {

			// new format - AES-256-GCM (authenticated)
			if( strpos( $stored_value, 'infinity23_enc2:' ) === 0 ) {

				if( !function_exists( 'openssl_decrypt' ) ) {
					return '';
				} // if( !function_exists( 'openssl_decrypt' ) ) {

				$raw = base64_decode( substr( $stored_value, strlen( 'infinity23_enc2:' ) ) );

				// 12 byte iv + 16 byte auth tag minimum
				if( $raw === false || strlen( $raw ) < 28 ) {
					return '';
				} // if( $raw === false || strlen( $raw ) < 28 ) {

				$iv = substr( $raw, 0, 12 );
				$tag = substr( $raw, 12, 16 );
				$encrypted = substr( $raw, 28 );
				$key = infinity23_encryption_get_key( $context );
				$decrypted = openssl_decrypt( $encrypted, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag );

				return ( $decrypted !== false ) ? $decrypted : '';

			} // if( strpos( $stored_value, 'infinity23_enc2:' ) === 0 ) {

			// old format - AES-256-CBC (unauthenticated) - kept only so a value
			// saved before this upgrade still decrypts until it is resaved
			if( strpos( $stored_value, 'infinity23_enc:' ) === 0 ) {

				if( !function_exists( 'openssl_decrypt' ) ) {
					return '';
				} // if( !function_exists( 'openssl_decrypt' ) ) {

				$raw = base64_decode( substr( $stored_value, strlen( 'infinity23_enc:' ) ) );

				if( $raw === false || strlen( $raw ) < 16 ) {
					return '';
				} // if( $raw === false || strlen( $raw ) < 16 ) {

				$iv = substr( $raw, 0, 16 );
				$encrypted = substr( $raw, 16 );
				$key = infinity23_encryption_get_key( $context );
				$decrypted = openssl_decrypt( $encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );

				return ( $decrypted !== false ) ? $decrypted : '';

			} // if( strpos( $stored_value, 'infinity23_enc:' ) === 0 ) {

			// saved on a host with no usable openssl - the marker is what makes
			// that visible instead of silent, and it is dropped again on read
			if( strpos( $stored_value, 'infinity23_plain:' ) === 0 ) {
				return substr( $stored_value, strlen( 'infinity23_plain:' ) );
			} // if( strpos( $stored_value, 'infinity23_plain:' ) === 0 ) {

			return $stored_value;

		} // function infinity23_decrypt_value( $stored_value, $context = 'default' ) {

	} // if( !function_exists( 'infinity23_decrypt_value' ) ) {

?>
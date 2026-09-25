<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// ===========================================================================
// Install database
// ===========================================================================

	// --------------------------------------------
	// include the install file to create the database
	// --------------------------------------------

	if( !function_exists( 'infinity23_install_database' ) ) {

		function infinity23_install_database() {
			include( dirname(__FILE__) . "/../../include/install-db/install.php" );
		} // function infinity23_install_database() {

	} // if( !function_exists( 'infinity23_install_database' ) ) {

// ===========================================================================
// global functions for the plugin
// ===========================================================================

    // --------------------------------------------
    // functions that allow to get the urls and dir path to the plugin
    // --------------------------------------------

    // getting the plugin path (dir path)
    if( !function_exists( 'infinity23_plugin_dir_path' ) ) {

        function infinity23_plugin_dir_path( $file_path = '' ) {

            $base = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
            if( !empty( $file_path ) ) { return $base . $file_path;}
            return $base;

        } // function infinity23_plugin_dir_path( $file_path = '' ) {

    } // if( !function_exists( 'infinity23_plugin_dir_path' ) ) {

    // getting the plugin url page (mostly for loading images)
    if( !function_exists( 'infinity23_plugin_url' ) ) {

        function infinity23_plugin_url( $file_path = '' ) {

            $base = plugin_dir_url( dirname( dirname( __FILE__ ) ) );
            if( !empty( $file_path ) ) {return $base . $file_path;}
            return $base;

        } // function infinity23_plugin_url( $file_path = '' ) {

    } // if( !function_exists( 'infinity23_plugin_url' ) ) {

    // --------------------------------------------
    // load essential files for the page 
    // (settings file, style, javascript etc...)
    // --------------------------------------------

    if( !function_exists( 'infinity23_load_essential_files' ) ) {

        function infinity23_load_essential_files() {

            // load the media uploader
            wp_enqueue_media();

            // load the color picker script (its style is registered/enqueued
            // per-module on admin_enqueue_scripts by infinity23_enqueue_essential_styles()
            // below, alongside infinity23_style, so both land in the real <head>)
            wp_enqueue_script( 'wp-color-picker' );

            // register and enqueue the plugin's own backend script - loaded
            // in the footer, so enqueuing it late still prints correctly
            wp_register_script( 'infinity23_scripts', infinity23_plugin_url( 'css-and-js/back-end/modules/infinity23-scripts.js' ), array( 'jquery' ), INFINITY23_VERSION, true );

            $infinity23_scripts_inline_vars  = 'var infinity23_pro_version_link = ' . wp_json_encode( esc_url( infinity23_pro_version_link() ) ) . ';';
            $infinity23_scripts_inline_vars .= 'var infinity23_ajax_url = ' . wp_json_encode( esc_url( admin_url( 'admin-ajax.php' ) ) ) . ';';
            $infinity23_scripts_inline_vars .= 'var infinity23_get_title_nonce = ' . wp_json_encode( wp_create_nonce( 'infinity23_get_page_title' ) ) . ';';

            wp_add_inline_script( 'infinity23_scripts', $infinity23_scripts_inline_vars, 'before' );
            wp_enqueue_script( 'infinity23_scripts' );

        } // function infinity23_load_essential_files() {

    } // if( !function_exists( 'infinity23_load_essential_files' ) ) {

    // --------------------------------------------
    // register/enqueue the styles infinity23_load_essential_files() used to
    // handle - called from each module's own loader.php on
    // admin_enqueue_scripts, scoped to that module's own admin page, so both
    // land in the real <head> instead of being force-printed late from
    // inside the page body
    // --------------------------------------------

    if( !function_exists( 'infinity23_enqueue_essential_styles' ) ) {

        function infinity23_enqueue_essential_styles() {

            wp_enqueue_style( 'wp-color-picker' );

            wp_register_style( 'infinity23_style', infinity23_plugin_url( 'css-and-js/back-end/modules/infinity23-style.css' ), array(), INFINITY23_VERSION );
            wp_enqueue_style( 'infinity23_style' );

        } // function infinity23_enqueue_essential_styles() {

    } // if( !function_exists( 'infinity23_enqueue_essential_styles' ) ) {

// ===========================================================================
// Elementor Helper Functions
// ===========================================================================

    // --------------------------------------------
    // check if elementor plugin is active
    // --------------------------------------------

    if( !function_exists( 'infinity23_is_elementor_active' ) ) {

        function infinity23_is_elementor_active() {

            return did_action( 'elementor/loaded' ) ? true : false;

        } // function infinity23_is_elementor_active() {

    } // if( !function_exists( 'infinity23_is_elementor_active' ) ) {

// ===========================================================================
// Custom Post Types Helper (Global)
// ===========================================================================

    // --------------------------------------------
    // every custom post type registered with show_ui, keyed by slug. Shared
    // so any module can list the same custom post types without repeating
    // its own get_post_types() call. Only call after the init hook has
    // finished, otherwise custom post types are not registered yet.
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_custom_post_types' ) ) {

        function infinity23_get_custom_post_types() {

            $custom_post_types = get_post_types( array( '_builtin' => false, 'show_ui' => true ), 'objects' );
            $result = array();

            foreach( $custom_post_types as $post_type_slug => $post_type_object ) {

                $result[ $post_type_slug ] = array(
                    'label' => $post_type_object->labels->name,
                    'post_type' => $post_type_slug,
                );

            } // foreach( $custom_post_types as $post_type_slug => $post_type_object ) {

            return $result;

        } // function infinity23_get_custom_post_types() {

    } // if( !function_exists( 'infinity23_get_custom_post_types' ) ) {


// ===========================================================================
// Timestamp functions
// ===========================================================================

    // --------------------------------------------
    // get current UTC timestamp for saving to the database
    // --------------------------------------------

    if( !function_exists( 'infinity23_current_timestamp' ) ) {

        function infinity23_current_timestamp() {

            return time();

        } // function infinity23_current_timestamp() {

    } // if( !function_exists( 'infinity23_current_timestamp' ) ) {

    // --------------------------------------------
    // convert a stored UTC timestamp to the WordPress site timezone for display
    // $format default is d/m/Y H:i - can be overridden per use case
    // --------------------------------------------

    if( !function_exists( 'infinity23_format_timestamp' ) ) {

        function infinity23_format_timestamp( $utc_timestamp, $format = 'd/m/Y H:i' ) {

            if( empty( $utc_timestamp ) ) {
                return '';
            } // if( empty( $utc_timestamp ) ) {

            $timezone_string = get_option( 'timezone_string' );

            if( !empty( $timezone_string ) ) {
                $timezone = new DateTimeZone( $timezone_string );
            } else {
                $gmt_offset = get_option( 'gmt_offset' );
                $offset_sign = $gmt_offset >= 0 ? '+' : '-';
                $offset_hours = abs( intval( $gmt_offset ) );
                $offset_minutes = abs( round( ( $gmt_offset - intval( $gmt_offset ) ) * 60 ) );
                $timezone = new DateTimeZone( sprintf( '%s%02d:%02d', $offset_sign, $offset_hours, $offset_minutes ) );
            } // if( !empty( $timezone_string ) ) {

            $date = new DateTime( '@' . intval( $utc_timestamp ) );
            $date->setTimezone( $timezone );

            return $date->format( $format );

        } // function infinity23_format_timestamp( $utc_timestamp, $format ) {

    } // if( !function_exists( 'infinity23_format_timestamp' ) ) {

// ===========================================================================
// Admin messages output function
// ===========================================================================

    // --------------------------------------------
    // output success or error message
    // --------------------------------------------

    if( !function_exists( 'infinity23_show_message' ) ) {

        function infinity23_show_message( $infinity23_success_message = '', $infinity23_error_message = '' ) {

            if( !empty( $infinity23_success_message ) ) { ?>
                <div class="infinity23-message infinity23-message-success"><?php echo esc_html( $infinity23_success_message ); ?></div>
            <?php } // if( !empty( $infinity23_success_message ) ) {

            if( !empty( $infinity23_error_message ) ) {

                $errors = is_array( $infinity23_error_message ) ? $infinity23_error_message : array( $infinity23_error_message ); ?>
                <div class="infinity23-message infinity23-message-error">
                    <b>Error - The form was not submitted</b>
                    <ul class="infinity23-message-error-list">
                        <?php foreach( $errors as $error ) { ?>
                            <li><?php echo esc_html( $error ); ?></li>
                        <?php } // foreach( $errors as $error ) { ?>
                    </ul>
                </div>
            <?php } // if( !empty( $infinity23_error_message ) ) {

        } // function infinity23_show_message( $infinity23_success_message, $infinity23_error_message ) {

    } // if( !function_exists( 'infinity23_show_message' ) ) {

// ===========================================================================
// form checks functions
// ===========================================================================

    // --------------------------------------------
    // get a value from a data array with a default fallback
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_data' ) ) {

        function infinity23_get_data( $key, $array, $infinity23_defaults = array() ) {

            if( isset( $array[$key] ) ) {
                return $array[$key];
            } // if( isset( $array[$key] ) ) {

            if( isset( $infinity23_defaults[$key] ) ) {

                if( is_array( $infinity23_defaults[$key] ) && isset( $infinity23_defaults[$key]['value'] ) ) {

                    $infinity23_default_value = $infinity23_defaults[$key]['value'];

                    if( is_array( $infinity23_default_value ) && isset( $infinity23_default_value['pro'] ) && isset( $infinity23_default_value['free'] ) ) {
                        return infinity23_is_pro() ? $infinity23_default_value['pro'] : $infinity23_default_value['free'];
                    } // if( is_array( $infinity23_default_value ) && isset( $infinity23_default_value['pro'] ) && isset( $infinity23_default_value['free'] ) ) {

                    return $infinity23_default_value;

                } // if( is_array( $infinity23_defaults[$key] ) && isset( $infinity23_defaults[$key]['value'] ) ) {

                return $infinity23_defaults[$key];

            } // if( isset( $infinity23_defaults[$key] ) ) {

            return '';

        } // function infinity23_get_data( $key, $array, $infinity23_defaults = array() ) {

    } // if( !function_exists( 'infinity23_get_data' ) ) {

// --------------------------------------------
    // dealing with post fields and sanitizing before saving to the database
    // $type options:
    //   'text'   - plain text, no HTML allowed (default)
    //   'url'    - full URL sanitization
    //   'intval' - whole numbers only
    //   'float'  - decimal numbers (e.g. 1.5, 99.9)
    //   'html'   - allows safe HTML tags like <b> <a> <p>, strips dangerous tags like <script>
    // --------------------------------------------

    // --------------------------------------------
    // strip the patterns that must never reach a <style> block, repeating until
    // the string stops changing - a single pass is defeated by nesting, since
    // javasjavascript:cript: turns back into javascript: after one replace
    // --------------------------------------------

    if( !function_exists( 'infinity23_strip_dangerous_css' ) ) {

        function infinity23_strip_dangerous_css( $infinity23_css_value ) {

            $infinity23_css_value = strval( $infinity23_css_value );

            do {

                $infinity23_css_before_replace = $infinity23_css_value;
                $infinity23_css_value = str_ireplace( array( '</style', 'javascript:', 'expression(' ), array( '', '', '' ), $infinity23_css_value );

            } while( $infinity23_css_value !== $infinity23_css_before_replace );

            return $infinity23_css_value;

        } // function infinity23_strip_dangerous_css( $infinity23_css_value ) {

    } // if( !function_exists( 'infinity23_strip_dangerous_css' ) ) {

    if( !function_exists( 'infinity23_post_sanitize' ) ) {

        function infinity23_post_sanitize($input, $type = 'text') {

            $value = '';

            // collect value data if exists - arrays are not supported here
            // also trim spaces and convert data to text with strval
            // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- shared generic sanitizer used by every module; each caller already verifies its own nonce before reaching this point, and every $type branch below properly wp_unslash()'s $value before using it
            if( isset($_POST[$input]) && !is_array($_POST[$input]) ) {
                $value = trim( strval( $_POST[$input] ) );
            }
            // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            // sanitize the code before insert to the database
            // this runs even when the value is '0' because '0' is empty() in php

            // an unknown type must never fall past every branch below and return
            // the raw $_POST value, so anything not handled here becomes plain text
            if( !in_array( $type, array( 'text', 'url', 'intval', 'float', 'html', 'css' ), true ) ) {
                $type = 'text';
            } // if( !in_array( $type, array( 'text', 'url', 'intval', 'float', 'html', 'css' ), true ) ) {

            if( $type === 'text' || empty($type) ) {
                $value = sanitize_text_field( wp_unslash( $value ) );
            }

            if( $type === 'url' ) {
                $value = esc_url_raw( wp_unslash( $value ) );
            }

            if( $type === 'intval' ) {
                $value = intval($value);
            }

            if( $type === 'float' ) {
                $value = floatval($value);
            }

            if( $type === 'html' ) {
                $value = wp_kses_post( wp_unslash( $value ) );
            }

            if( $type === 'css' ) {
                $value = wp_strip_all_tags( wp_unslash( $value ), false );
                $value = infinity23_strip_dangerous_css( $value );
            }

            return $value;

        } // function infinity23_post_sanitize($input, $type = 'text') {

    } // if( !function_exists( 'infinity23_post_sanitize' ) ) {

    // --------------------------------------------
    // sanitize a phone or whatsapp number field before saving to the database
    // strips everything except digits, keeping a leading + if one was typed
    // --------------------------------------------

    if( !function_exists( 'infinity23_sanitize_phone_number' ) ) {

        function infinity23_sanitize_phone_number( $value, $allow_plus = false ) {

            $value = trim( strval( $value ) );
            $has_plus = ( $allow_plus && substr( $value, 0, 1 ) === '+' );
            $value = preg_replace( '/[^0-9]/', '', $value );

            if( $has_plus ) {
                $value = '+' . $value;
            } // if( $has_plus ) {

            return $value;

        } // function infinity23_sanitize_phone_number( $value, $allow_plus = false ) {

    } // if( !function_exists( 'infinity23_sanitize_phone_number' ) ) {

    // --------------------------------------------
    // convert array into json to save to database
    // --------------------------------------------

    if( !function_exists( 'infinity23_convert_array_to_json' ) ) {

        function infinity23_convert_array_to_json($array) {

            $json_output = '';

            if( !empty($array) ) {
                $json_output = json_encode($array, JSON_UNESCAPED_UNICODE);
            } // if( !empty($array) ) {

            return $json_output;

        } // function infinity23_convert_array_to_json($array) {

    } // if( !function_exists( 'infinity23_convert_array_to_json' ) ) {

    // --------------------------------------------
    // convert json back into array to read from database
    // --------------------------------------------

    if( !function_exists( 'infinity23_convert_json_to_array' ) ) {

        function infinity23_convert_json_to_array($json) {

            $array_output = array();

            if( !empty($json) ) {
                $decoded = json_decode($json, true);
                if( is_array($decoded) ) {
                    $array_output = $decoded;
                } // if( is_array($decoded) ) {
            } // if( !empty($json) ) {

            return $array_output;

        } // function infinity23_convert_json_to_array($json) {

    } // if( !function_exists( 'infinity23_convert_json_to_array' ) ) {

// ===========================================================================
// CSS sanitize function - use before printing admin values into a style tag
// esc_attr() does not escape ; or } so it is not safe for inline css output
// ===========================================================================

    // --------------------------------------------
    // sanitize a value before printing it inside a <style> tag
    // $type options:
    //   'value'  - free text css value like border or border-radius (default)
    //   'background' - background value that allows gradients and rgba
    //   'size'   - number with an optional px % em rem vh vw pt suffix
    //   'option' - must exist in the $allowed array or the default is returned
    // $allowed is only used by the 'option' type
    // --------------------------------------------

    if( !function_exists( 'infinity23_css_sanitize' ) ) {

        function infinity23_css_sanitize( $value, $type = 'value', $default = '', $allowed = array() ) {

            $value = trim( strval( $value ) );

            // free text css value - strip every character that can break the declaration
            if( $type === 'value' || empty($type) ) {
                $value = str_replace( array( ';', '{', '}', '<', '>', '(', ')', ':', '"', "'", '\\' ), '', $value );
                $value = preg_replace( '/@import/i', '', $value );
                $value = trim( $value );
                if( $value === '' ) { $value = $default; }
            }

            // background value - keeps parentheses so gradients and rgba still work
            if( $type === 'background' ) {
                $value = str_replace( array( ';', '{', '}', '<', '>', '"', "'", '\\' ), '', $value );
                $value = preg_replace( '/@import/i', '', $value );
                $value = preg_replace( '/url\s*\(/i', '', $value );
                $value = preg_replace( '/expression\s*\(/i', '', $value );
                $value = trim( $value );
                if( $value === '' ) { $value = $default; }
            }

            // size value - number with an optional unit
            if( $type === 'size' ) {
                if( !preg_match( '/^-?[0-9]*\.?[0-9]+(px|%|em|rem|vh|vw|pt)?$/i', $value ) ) {
                    $value = $default;
                }
            }

            // select box value - must be one of the allowed options
            if( $type === 'option' ) {
                if( !in_array( $value, $allowed, true ) ) {
                    $value = $default;
                }
            }

            return $value;

        } // function infinity23_css_sanitize( $value, $type = 'value', $default = '', $allowed = array() ) {

    } // if( !function_exists( 'infinity23_css_sanitize' ) ) {

    // --------------------------------------------
    // build the border-radius CSS line for a module's front-end style output,
    // from that module's saved border_radius field
    // --------------------------------------------

    // --------------------------------------------
    // split a saved size value like "30px" or "5%" into its numeric value
    // and unit parts, so a field can display them as two separate inputs
    // (a plain number field and a unit select) instead of one combined text
    // --------------------------------------------

    if( !function_exists( 'infinity23_split_size_unit_value' ) ) {

        function infinity23_split_size_unit_value( $raw_value, $default_unit = 'px' ) {

            $raw_value = trim( strval( $raw_value ) );

            $result = array(
                'value' => '0',
                'unit' => $default_unit,
            );

            if( preg_match( '/^(-?[0-9]*\.?[0-9]+)(px|%|em|rem)?$/i', $raw_value, $matches ) ) {

                $result['value'] = $matches[1];
                $result['unit'] = !empty( $matches[2] ) ? strtolower( $matches[2] ) : $default_unit;

            } // if( preg_match(

            return $result;

        } // function infinity23_split_size_unit_value( $raw_value, $default_unit = 'px' ) {

    } // if( !function_exists( 'infinity23_split_size_unit_value' ) ) {

    if( !function_exists( 'infinity23_get_border_radius_css' ) ) {

        function infinity23_get_border_radius_css( $infinity23_border_radius, $radius_default = '0px' ) {

            return 'border-radius: ' . infinity23_css_sanitize( $infinity23_border_radius, 'value', $radius_default ) . ';';

        } // function infinity23_get_border_radius_css( $infinity23_border_radius, $radius_default = '0px' ) {

    } // if( !function_exists( 'infinity23_get_border_radius_css' ) ) {

    // --------------------------------------------
    // build the border CSS line for a module's front-end style output, from
    // that module's saved button_border field
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_border_css' ) ) {

        function infinity23_get_border_css( $infinity23_button_border, $border_default = 'none' ) {

            return 'border: ' . infinity23_css_sanitize( $infinity23_button_border, 'value', $border_default ) . ';';

        } // function infinity23_get_border_css( $infinity23_button_border, $border_default = 'none' ) {

    } // if( !function_exists( 'infinity23_get_border_css' ) ) {

    // --------------------------------------------
    // parse a saved border value into a struct array with style/color/unit
    // and a width per side. Understands both the new JSON format saved by
    // infinity23_advanced_border_field() and the OLD plain css border string
    // (e.g. "2px solid #fff" or "none") saved by modules not migrated yet -
    // so upgrading a module never resets an existing border.
    // --------------------------------------------

    if( !function_exists( 'infinity23_parse_border_to_struct' ) ) {

        function infinity23_parse_border_to_struct( $raw_value, $struct_defaults = array() ) {

            $struct = array(
                'style' => 'none',
                'color' => '#000000',
                'unit' => 'px',
                'top' => '0',
                'right' => '0',
                'bottom' => '0',
                'left' => '0',
                'linked' => true,
            );

            if( !empty( $struct_defaults ) ) {
                $struct = array_merge( $struct, $struct_defaults );
            } // if( !empty( $struct_defaults ) ) {

            $raw_value = trim( strval( $raw_value ) );

            if( empty( $raw_value ) || strtolower( $raw_value ) === 'none' ) {
                return $struct;
            } // if( empty( $raw_value ) || strtolower( $raw_value ) === 'none' ) {

            if( substr( $raw_value, 0, 1 ) === '{' ) {

                $decoded = infinity23_convert_json_to_array( $raw_value );

                if( !empty( $decoded ) && isset( $decoded['style'] ) ) {

                    $struct['style'] = isset( $decoded['style'] ) ? $decoded['style'] : $struct['style'];
                    $struct['color'] = isset( $decoded['color'] ) ? $decoded['color'] : $struct['color'];
                    $struct['unit'] = isset( $decoded['unit'] ) ? $decoded['unit'] : $struct['unit'];
                    $struct['top'] = isset( $decoded['top'] ) ? $decoded['top'] : $struct['top'];
                    $struct['right'] = isset( $decoded['right'] ) ? $decoded['right'] : $struct['right'];
                    $struct['bottom'] = isset( $decoded['bottom'] ) ? $decoded['bottom'] : $struct['bottom'];
                    $struct['left'] = isset( $decoded['left'] ) ? $decoded['left'] : $struct['left'];
                    $struct['linked'] = isset( $decoded['linked'] ) ? !empty( $decoded['linked'] ) : ( $struct['right'] === $struct['top'] && $struct['bottom'] === $struct['top'] && $struct['left'] === $struct['top'] );

                    return $struct;

                } // if( !empty( $decoded ) && isset( $decoded['style'] ) ) {

            } // if( substr( $raw_value, 0, 1 ) === '{' ) {

            if( preg_match( '/^([0-9.]+)(px|%|em|rem)?\s+(solid|dashed|dotted|double|groove|ridge|inset|outset)\s+(.+)$/i', $raw_value, $matches ) ) {

                $struct['top'] = $matches[1];
                $struct['right'] = $matches[1];
                $struct['bottom'] = $matches[1];
                $struct['left'] = $matches[1];
                $struct['unit'] = !empty( $matches[2] ) ? strtolower( $matches[2] ) : 'px';
                $struct['style'] = strtolower( $matches[3] );
                $struct['color'] = trim( $matches[4] );
                $struct['linked'] = true;

            } // if( preg_match(

            return $struct;

        } // function infinity23_parse_border_to_struct( $raw_value ) {

    } // if( !function_exists( 'infinity23_parse_border_to_struct' ) ) {

    // --------------------------------------------
    // build a 4-side border CSS block (border-top/right/bottom/left) from a
    // saved border value. Replaces infinity23_get_border_css() for modules
    // migrated to infinity23_advanced_border_field().
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_advanced_border_css' ) ) {

        function infinity23_get_advanced_border_css( $raw_value, $default_raw = 'none' ) {

            $struct = infinity23_parse_border_to_struct( !empty( $raw_value ) ? $raw_value : $default_raw );

            $allowed_styles = array( 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset' );
            $style = infinity23_css_sanitize( $struct['style'], 'option', 'none', array_merge( array( 'none' ), $allowed_styles ) );

            if( $style === 'none' ) {
                return 'border: none;';
            } // if( $style === 'none' ) {

            $color = infinity23_css_sanitize( $struct['color'], 'value', '#000000' );
            $unit = infinity23_css_sanitize( $struct['unit'], 'option', 'px', array( 'px', '%', 'em', 'rem' ) );

            $css = '';
            $css .= 'border-top: ' . infinity23_css_sanitize( floatval( $struct['top'] ) . $unit, 'size', '0px' ) . ' ' . $style . ' ' . $color . ';';
            $css .= 'border-right: ' . infinity23_css_sanitize( floatval( $struct['right'] ) . $unit, 'size', '0px' ) . ' ' . $style . ' ' . $color . ';';
            $css .= 'border-bottom: ' . infinity23_css_sanitize( floatval( $struct['bottom'] ) . $unit, 'size', '0px' ) . ' ' . $style . ' ' . $color . ';';
            $css .= 'border-left: ' . infinity23_css_sanitize( floatval( $struct['left'] ) . $unit, 'size', '0px' ) . ' ' . $style . ' ' . $color . ';';

            return $css;

        } // function infinity23_get_advanced_border_css( $raw_value, $default_raw = 'none' ) {

    } // if( !function_exists( 'infinity23_get_advanced_border_css' ) ) {

    // --------------------------------------------
    // parse a saved border-radius value into a struct array with unit and a
    // width per corner. Understands both the new JSON format saved by
    // infinity23_advanced_border_radius_field() and the OLD plain css string
    // (e.g. "10px" or "50%") saved by modules not migrated yet.
    // --------------------------------------------

    if( !function_exists( 'infinity23_parse_border_radius_to_struct' ) ) {

        function infinity23_parse_border_radius_to_struct( $raw_value, $struct_defaults = array() ) {

            $struct = array(
                'unit' => 'px',
                'top_left' => '0',
                'top_right' => '0',
                'bottom_right' => '0',
                'bottom_left' => '0',
                'linked' => true,
            );

            if( !empty( $struct_defaults ) ) {
                $struct = array_merge( $struct, $struct_defaults );
            } // if( !empty( $struct_defaults ) ) {

            $raw_value = trim( strval( $raw_value ) );

            if( empty( $raw_value ) ) {
                return $struct;
            } // if( empty( $raw_value ) ) {

            if( substr( $raw_value, 0, 1 ) === '{' ) {

                $decoded = infinity23_convert_json_to_array( $raw_value );

                if( !empty( $decoded ) && isset( $decoded['unit'] ) ) {

                    $struct['unit'] = isset( $decoded['unit'] ) ? $decoded['unit'] : $struct['unit'];
                    $struct['top_left'] = isset( $decoded['top_left'] ) ? $decoded['top_left'] : $struct['top_left'];
                    $struct['top_right'] = isset( $decoded['top_right'] ) ? $decoded['top_right'] : $struct['top_right'];
                    $struct['bottom_right'] = isset( $decoded['bottom_right'] ) ? $decoded['bottom_right'] : $struct['bottom_right'];
                    $struct['bottom_left'] = isset( $decoded['bottom_left'] ) ? $decoded['bottom_left'] : $struct['bottom_left'];
                    $struct['linked'] = isset( $decoded['linked'] ) ? !empty( $decoded['linked'] ) : ( $struct['top_right'] === $struct['top_left'] && $struct['bottom_right'] === $struct['top_left'] && $struct['bottom_left'] === $struct['top_left'] );

                    return $struct;

                } // if( !empty( $decoded ) && isset( $decoded['unit'] ) ) {

            } // if( substr( $raw_value, 0, 1 ) === '{' ) {

            if( preg_match( '/^([0-9.]+)(px|%|em|rem)?$/i', $raw_value, $matches ) ) {

                $struct['top_left'] = $matches[1];
                $struct['top_right'] = $matches[1];
                $struct['bottom_right'] = $matches[1];
                $struct['bottom_left'] = $matches[1];
                $struct['unit'] = !empty( $matches[2] ) ? strtolower( $matches[2] ) : 'px';
                $struct['linked'] = true;

            } // if( preg_match(

            return $struct;

        } // function infinity23_parse_border_radius_to_struct( $raw_value ) {

    } // if( !function_exists( 'infinity23_parse_border_radius_to_struct' ) ) {

    // --------------------------------------------
    // build a 4-corner border-radius CSS block from a saved border-radius
    // value. Replaces infinity23_get_border_radius_css() for modules migrated
    // to infinity23_advanced_border_radius_field().
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_advanced_border_radius_css' ) ) {

        function infinity23_get_advanced_border_radius_css( $raw_value, $default_raw = '0px' ) {

            $struct = infinity23_parse_border_radius_to_struct( !empty( $raw_value ) ? $raw_value : $default_raw );
            $unit = infinity23_css_sanitize( $struct['unit'], 'option', 'px', array( 'px', '%', 'em', 'rem' ) );

            $css = '';
            $css .= 'border-top-left-radius: ' . infinity23_css_sanitize( floatval( $struct['top_left'] ) . $unit, 'size', '0px' ) . ';';
            $css .= 'border-top-right-radius: ' . infinity23_css_sanitize( floatval( $struct['top_right'] ) . $unit, 'size', '0px' ) . ';';
            $css .= 'border-bottom-right-radius: ' . infinity23_css_sanitize( floatval( $struct['bottom_right'] ) . $unit, 'size', '0px' ) . ';';
            $css .= 'border-bottom-left-radius: ' . infinity23_css_sanitize( floatval( $struct['bottom_left'] ) . $unit, 'size', '0px' ) . ';';

            return $css;

        } // function infinity23_get_advanced_border_radius_css( $raw_value, $default_raw = '0px' ) {

    } // if( !function_exists( 'infinity23_get_advanced_border_radius_css' ) ) {

    // --------------------------------------------
    // parse a saved background value into a struct - type is
    // solid/gradient/custom_css/none. Same JSON pattern as border/radius.
    // --------------------------------------------

    if( !function_exists( 'infinity23_parse_background_to_struct' ) ) {

        function infinity23_parse_background_to_struct( $raw_value, $struct_defaults = array() ) {

            $struct = array(
                'type' => 'solid',
                'solid_color' => '#00bc3c',
                'gradient_color_1' => '#00bc3c',
                'gradient_color_2' => '#008d32',
                'gradient_angle' => '180',
                'custom_css' => '',
            );

            if( !empty( $struct_defaults ) ) {
                $struct = array_merge( $struct, $struct_defaults );
            } // if( !empty( $struct_defaults ) ) {

            $raw_value = trim( strval( $raw_value ) );

            if( !empty( $raw_value ) && substr( $raw_value, 0, 1 ) === '{' ) {

                $decoded = infinity23_convert_json_to_array( $raw_value );

                if( !empty( $decoded ) && isset( $decoded['type'] ) ) {
                    $struct = array_merge( $struct, $decoded );
                } // if( !empty( $decoded ) && isset( $decoded['type'] ) ) {

            } // if( !empty( $raw_value ) && substr( $raw_value, 0, 1 ) === '{' ) {

            if( !in_array( $struct['type'], array( 'solid', 'gradient', 'custom_css', 'none' ), true ) ) {
                $struct['type'] = 'solid';
            } // if( !in_array(

            return $struct;

        } // function infinity23_parse_background_to_struct( $raw_value, $struct_defaults = array() ) {

    } // if( !function_exists( 'infinity23_parse_background_to_struct' ) ) {

    // --------------------------------------------
    // build the right-hand css background value (no property name) from a
    // saved background struct - solid color, gradient, custom css value, or
    // 'transparent' for none. Caller decides how to combine it with an icon
    // background-image layer.
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_advanced_background_value' ) ) {

        function infinity23_get_advanced_background_value( $raw_value, $default_raw = 'solid' ) {

            $struct = infinity23_parse_background_to_struct( !empty( $raw_value ) ? $raw_value : $default_raw );

            if( $struct['type'] === 'none' ) {
                return 'transparent';
            } // if( $struct['type'] === 'none' ) {

            if( $struct['type'] === 'gradient' ) {

                $color1 = infinity23_css_sanitize( $struct['gradient_color_1'], 'value', '#00bc3c' );
                $color2 = infinity23_css_sanitize( $struct['gradient_color_2'], 'value', '#008d32' );
                $angle = intval( $struct['gradient_angle'] );

                return 'linear-gradient(' . $angle . 'deg, ' . $color1 . ', ' . $color2 . ')';

            } // if( $struct['type'] === 'gradient' ) {

            if( $struct['type'] === 'custom_css' ) {
                return infinity23_css_sanitize( $struct['custom_css'], 'background', 'transparent' );
            } // if( $struct['type'] === 'custom_css' ) {

            return infinity23_css_sanitize( $struct['solid_color'], 'value', '#00bc3c' );

        } // function infinity23_get_advanced_background_value( $raw_value, $default_raw = 'solid' ) {

    } // if( !function_exists( 'infinity23_get_advanced_background_value' ) ) {

// ===========================================================================
// Settings functions - load, update, add and delete settings type options
// These functions work with the global cache and only handle option_type = 'settings'
// ===========================================================================

    // --------------------------------------------
    // Load all options into global on page load
    // --------------------------------------------

    global $infinity23_options_cache, $infinity23_options_data_cache, $infinity23_options_time_cache;
    $infinity23_options_cache = array();
    $infinity23_options_data_cache = array();
    $infinity23_options_time_cache = array();

    if( !function_exists( 'infinity23_load_all_options' ) ) {

        function infinity23_load_all_options() {

            global $wpdb, $infinity23_table_name, $infinity23_options_cache, $infinity23_options_data_cache, $infinity23_options_time_cache;

            // make a call to the database options in case it exists we run suppress_errors in case the database doesn't exist so it won't return error
            // it's used mainly if we remove the database while the plugin is active. If the database returns empty, we will recreate it and make another call to the db
            // data1-data5 are loaded here too, in the same single query, so large repeating content (like Tag Manager tag rows) can live outside option_value without adding a query
            $wpdb->suppress_errors( true );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
            $results = $wpdb->get_results(
                "SELECT `option_name`, `option_value`, `time_created_utc`, `time_updated_utc`, `data1`, `data2`, `data3`, `data4`, `data5`, `data6`, `data7`, `data8`, `data9`, `data10` FROM `" . esc_sql( $infinity23_table_name ) . "`",
                ARRAY_A
            );
            $wpdb->suppress_errors( false );

            // if the database is empty we will recreate the database install process and make another call for the data
            // we do that mainly in case the user removes the database manually
            if( !empty( $wpdb->last_error ) ) {
                infinity23_install_database();
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
                $results = $wpdb->get_results(
                    "SELECT `option_name`, `option_value`, `time_created_utc`, `time_updated_utc`, `data1`, `data2`, `data3`, `data4`, `data5`, `data6`, `data7`, `data8`, `data9`, `data10` FROM `" . esc_sql( $infinity23_table_name ) . "`",
                    ARRAY_A
                );
            } // if( !empty( $wpdb->last_error ) ) {

            if( !empty( $results ) ) {

                foreach( $results as $row ) {
                    $infinity23_options_cache[ $row['option_name'] ] = $row['option_value'];
                    $infinity23_options_data_cache[ $row['option_name'] ] = array(
                        'data1' => $row['data1'],
                        'data2' => $row['data2'],
                        'data3' => $row['data3'],
                        'data4' => $row['data4'],
                        'data5' => $row['data5'],
                        'data6' => $row['data6'],
                        'data7' => $row['data7'],
                        'data8' => $row['data8'],
                        'data9' => $row['data9'],
                        'data10' => $row['data10'],
                    );
                    $infinity23_options_time_cache[ $row['option_name'] ] = array(
                        'time_created_utc' => $row['time_created_utc'],
                        'time_updated_utc' => $row['time_updated_utc'],
                    );
                } // foreach( $results as $row ) {

            } // if( !empty( $results ) ) {

        } // function infinity23_load_all_options() {

    } // if( !function_exists( 'infinity23_load_all_options' ) ) {

    // --------------------------------------------
    // Get an option value
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_option' ) ) {

        function infinity23_get_option( $option_name, $default = '' ) {

            global $infinity23_options_cache;

            if( isset( $infinity23_options_cache[ $option_name ] ) ) {
                return $infinity23_options_cache[ $option_name ];
            } // if( isset( $infinity23_options_cache[ $option_name ] ) ) {

            return $default;

        } // function infinity23_get_option( $option_name, $default = '' ) {

    } // if( !function_exists( 'infinity23_get_option' ) ) {

    // --------------------------------------------
    // Output "Last updated: [date] on [time]" for a settings option,
    // using the WordPress site date/time format. Outputs nothing if
    // the option was never saved.
    // --------------------------------------------

    if( !function_exists( 'infinity23_display_last_updated_time' ) ) {

        function infinity23_display_last_updated_time( $option_name ) {

            $last_updated_utc = infinity23_get_option_time( $option_name, 'updated' );

            if( empty( $last_updated_utc ) ) { return; }

            $site_date_format = get_option( 'date_format' );
            $day_pos = stripos( $site_date_format, 'd' );
            $month_pos = stripos( $site_date_format, 'm' );

            if( $day_pos !== false && ( $month_pos === false || $day_pos < $month_pos ) ) {
                $numeric_date_format = 'd/m/y';
            } else {
                $numeric_date_format = 'm/d/y';
            } // if( $day_pos !== false &&

            $date_output = infinity23_format_timestamp( $last_updated_utc, $numeric_date_format );
            $time_output = infinity23_format_timestamp( $last_updated_utc, get_option( 'time_format' ) );

                        echo '<span class="infinity23-last-updated-time">Settings was last saved on ' . esc_html( $date_output ) . ' at ' . esc_html( $time_output ) . '</span>';

        } // function infinity23_display_last_updated_time( $option_name ) {

    } // if( !function_exists( 'infinity23_display_last_updated_time' ) ) {

    // --------------------------------------------
    // Get a saved option's created/updated UTC timestamp
    // $type is either 'created' or 'updated'
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_option_time' ) ) {

        function infinity23_get_option_time( $option_name, $type = 'updated' ) {

            global $infinity23_options_time_cache;

            $cache_key = ( $type === 'created' ) ? 'time_created_utc' : 'time_updated_utc';

            if( isset( $infinity23_options_time_cache[ $option_name ][ $cache_key ] ) ) {
                return $infinity23_options_time_cache[ $option_name ][ $cache_key ];
            } // if( isset( $infinity23_options_time_cache[ $option_name ][ $cache_key ] ) ) {

            return '';

        } // function infinity23_get_option_time( $option_name, $type = 'updated' ) {

    } // if( !function_exists( 'infinity23_get_option_time' ) ) {

    // --------------------------------------------
    // Get a raw data point (data1-data5) saved alongside an option
    // Used for large repeating content kept out of the main option_value JSON
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_option_data' ) ) {

        function infinity23_get_option_data( $option_name, $data_slot, $default = '' ) {

            global $infinity23_options_data_cache;

            if( isset( $infinity23_options_data_cache[ $option_name ][ $data_slot ] ) && $infinity23_options_data_cache[ $option_name ][ $data_slot ] !== NULL ) {
                return $infinity23_options_data_cache[ $option_name ][ $data_slot ];
            } // if( isset( $infinity23_options_data_cache[ $option_name ][ $data_slot ] ) && ...

            return $default;

        } // function infinity23_get_option_data( $option_name, $data_slot, $default = '' ) {

    } // if( !function_exists( 'infinity23_get_option_data' ) ) {

    // --------------------------------------------
    // Get a data point already decoded from JSON into an array, with a default fallback
    // This is the data-slot equivalent of infinity23_get_data() - compares against
    // the data cache instead of the main option JSON
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_option_data_array' ) ) {

        function infinity23_get_option_data_array( $option_name, $data_slot, $default = array() ) {

            $raw_value = infinity23_get_option_data( $option_name, $data_slot, '' );

            if( empty( $raw_value ) ) {
                return $default;
            } // if( empty( $raw_value ) ) {

            $decoded_value = infinity23_convert_json_to_array( $raw_value );

            if( empty( $decoded_value ) ) {
                return $default;
            } // if( empty( $decoded_value ) ) {

            return $decoded_value;

        } // function infinity23_get_option_data_array( $option_name, $data_slot, $default = array() ) {

    } // if( !function_exists( 'infinity23_get_option_data_array' ) ) {

    // --------------------------------------------
    // Update an option value (insert or update)
    // --------------------------------------------

    if( !function_exists( 'infinity23_update_option' ) ) {

        function infinity23_update_option( $option_name, $option_value, $data1 = NULL, $data2 = NULL, $data3 = NULL, $data4 = NULL, $data5 = NULL, $data6 = NULL, $data7 = NULL, $data8 = NULL, $data9 = NULL, $data10 = NULL, $option_type = 'settings' ) {

            global $wpdb, $infinity23_table_name, $infinity23_options_cache, $infinity23_options_data_cache, $infinity23_options_time_cache;

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM `" . esc_sql( $infinity23_table_name ) . "` WHERE `option_name` = %s",
                $option_name
            ) );

            if( $exists ) {

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
                $wpdb->update(
                    $infinity23_table_name,
                    array(
                        'option_type'      => $option_type,
                        'option_value'     => $option_value,
                        'time_updated_utc' => infinity23_current_timestamp(),
                        'data1'            => $data1,
                        'data2'            => $data2,
                        'data3'            => $data3,
                        'data4'            => $data4,
                        'data5'            => $data5,
                        'data6'            => $data6,
                        'data7'            => $data7,
                        'data8'            => $data8,
                        'data9'            => $data9,
                        'data10'           => $data10,
                    ),
                    array( 'option_name' => $option_name ),
                    array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
                    array( '%s' )
                );

            } else {

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
                $wpdb->insert(
                    $infinity23_table_name,
                    array(
                        'option_type'      => $option_type,
                        'option_name'      => $option_name,
                        'option_value'     => $option_value,
                        'time_created_utc' => infinity23_current_timestamp(),
                        'time_updated_utc' => infinity23_current_timestamp(),
                        'data1'            => $data1,
                        'data2'            => $data2,
                        'data3'            => $data3,
                        'data4'            => $data4,
                        'data5'            => $data5,
                        'data6'            => $data6,
                        'data7'            => $data7,
                        'data8'            => $data8,
                        'data9'            => $data9,
                        'data10'           => $data10,
                    ),
                    array( '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
                );

            } // if( $exists ) {

            $infinity23_options_cache[ $option_name ] = $option_value;

            $infinity23_options_data_cache[ $option_name ] = array(
                'data1' => $data1,
                'data2' => $data2,
                'data3' => $data3,
                'data4' => $data4,
                'data5' => $data5,
                'data6' => $data6,
                'data7' => $data7,
                'data8' => $data8,
                'data9' => $data9,
                'data10' => $data10,
            );

            if( !isset( $infinity23_options_time_cache[ $option_name ]['time_created_utc'] ) ) {
                $infinity23_options_time_cache[ $option_name ]['time_created_utc'] = infinity23_current_timestamp();
            } // if( !isset( $infinity23_options_time_cache[ $option_name ]['time_created_utc'] ) ) {

            $infinity23_options_time_cache[ $option_name ]['time_updated_utc'] = infinity23_current_timestamp();

        } // function infinity23_update_option( $option_name, $option_value, $data1, $data2, $data3, $data4, $data5, $data6, $data7, $data8, $data9, $data10, $option_type ) {

    } // if( !function_exists( 'infinity23_update_option' ) ) {

    // --------------------------------------------
    // Add an option value (only if it doesn't exist)
    // --------------------------------------------

    if( !function_exists( 'infinity23_add_option' ) ) {

        function infinity23_add_option( $option_name, $option_value, $data1 = NULL, $data2 = NULL, $data3 = NULL, $data4 = NULL, $data5 = NULL, $data6 = NULL, $data7 = NULL, $data8 = NULL, $data9 = NULL, $data10 = NULL ) {

            global $wpdb, $infinity23_table_name, $infinity23_options_cache;

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM `" . esc_sql( $infinity23_table_name ) . "` WHERE `option_name` = %s",
                $option_name
            ) );

            if( !$exists ) {

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no WP core API for direct queries
                $wpdb->insert(
                    $infinity23_table_name,
                    array(
                        'option_type'      => 'settings',
                        'option_name'      => $option_name,
                        'option_value'     => $option_value,
                        'time_created_utc' => infinity23_current_timestamp(),
                        'time_updated_utc' => infinity23_current_timestamp(),
                        'data1'            => $data1,
                        'data2'            => $data2,
                        'data3'            => $data3,
                        'data4'            => $data4,
                        'data5'            => $data5,
                        'data6'            => $data6,
                        'data7'            => $data7,
                        'data8'            => $data8,
                        'data9'            => $data9,
                        'data10'           => $data10,
                    ),
                    array( '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
                );

                $infinity23_options_cache[ $option_name ] = $option_value;

                return true;

            } // if( !$exists ) {

            return false;

        } // function infinity23_add_option( $option_name, $option_value, $data1, $data2, $data3, $data4, $data5, $data6, $data7, $data8, $data9, $data10 ) {

    } // if( !function_exists( 'infinity23_add_option' ) ) {

    // --------------------------------------------
    // Delete an option
    // --------------------------------------------

    if( !function_exists( 'infinity23_delete_option' ) ) {

        function infinity23_delete_option( $option_name ) {

            global $wpdb, $infinity23_table_name, $infinity23_options_cache, $infinity23_options_data_cache, $infinity23_options_time_cache;

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no WP core API for direct queries; caching not yet implemented, tracked for later
            $wpdb->delete(
                $infinity23_table_name,
                array( 'option_name' => $option_name ),
                array( '%s' )
            );

            // clear every cache for this option, not only the value cache -
            // otherwise a reset still shows the old data slots and "last saved" time
            unset( $infinity23_options_cache[ $option_name ] );
            unset( $infinity23_options_data_cache[ $option_name ] );
            unset( $infinity23_options_time_cache[ $option_name ] );

        } // function infinity23_delete_option( $option_name ) {

    } // if( !function_exists( 'infinity23_delete_option' ) ) {

    // --------------------------------------------
    // Update only option_value and keep data1-data10 exactly as they are -
    // use this for any settings form whose option row also holds data slots
    // written by other code (cron adjustments, tag rows, saved pages, etc.)
    // --------------------------------------------

    if( !function_exists( 'infinity23_update_option_keep_data' ) ) {

        function infinity23_update_option_keep_data( $option_name, $option_value, $option_type = 'settings' ) {

            infinity23_update_option(
                $option_name,
                $option_value,
                infinity23_get_option_data( $option_name, 'data1', NULL ),
                infinity23_get_option_data( $option_name, 'data2', NULL ),
                infinity23_get_option_data( $option_name, 'data3', NULL ),
                infinity23_get_option_data( $option_name, 'data4', NULL ),
                infinity23_get_option_data( $option_name, 'data5', NULL ),
                infinity23_get_option_data( $option_name, 'data6', NULL ),
                infinity23_get_option_data( $option_name, 'data7', NULL ),
                infinity23_get_option_data( $option_name, 'data8', NULL ),
                infinity23_get_option_data( $option_name, 'data9', NULL ),
                infinity23_get_option_data( $option_name, 'data10', NULL ),
                $option_type
            );

        } // function infinity23_update_option_keep_data( $option_name, $option_value, $option_type = 'settings' ) {

    } // if( !function_exists( 'infinity23_update_option_keep_data' ) ) {

// ===========================================================================
// Plugin Icon Lookup - shared by Update Manager and Plugins/Themes Downloader
// ===========================================================================

    // --------------------------------------------
    // get a plugin's icon url from the WordPress.org API, cached for a week
    // returns an empty string if the plugin has no icon or is not hosted on
    // WordPress.org (e.g. premium/custom plugins)
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_plugin_icons_urls_cache' ) ) {

        function infinity23_get_plugin_icons_urls_cache() {

            $icons_cache = get_transient( 'infinity23_plugin_icons_urls_cache' );

            if( $icons_cache === false ) {
                $icons_cache = array();
            } // if( $icons_cache === false ) {

            return $icons_cache;

        } // function infinity23_get_plugin_icons_urls_cache() {

    } // if( !function_exists( 'infinity23_get_plugin_icons_urls_cache' ) ) {

    if( !function_exists( 'infinity23_save_plugin_icons_urls_cache' ) ) {

        function infinity23_save_plugin_icons_urls_cache( $icons_cache ) {

            set_transient( 'infinity23_plugin_icons_urls_cache', $icons_cache, DAY_IN_SECONDS );

        } // function infinity23_save_plugin_icons_urls_cache( $icons_cache ) {

    } // if( !function_exists( 'infinity23_save_plugin_icons_urls_cache' ) ) {

    if( !function_exists( 'infinity23_get_plugin_icon_from_update_transient' ) ) {

        function infinity23_get_plugin_icon_from_update_transient( $plugin_slug ) {

            // update-core.php never calls the wp.org API for icons - it reads
            // whatever is already sitting in this transient. Premium plugins
            // with their own update-checker library (Elementor Pro, etc.)
            // inject their own response into it, including their own icons,
            // hosted on their own servers - not on ps.w.org
            $update_data = get_site_transient( 'update_plugins' );
            $icon_url = '';

            if( empty( $update_data ) ) {
                return $icon_url;
            } // if( empty( $update_data ) ) {

            $groups_to_check = array();

            if( !empty( $update_data->response ) ) {
                $groups_to_check[] = $update_data->response;
            } // if( !empty( $update_data->response ) ) {

            if( !empty( $update_data->no_update ) ) {
                $groups_to_check[] = $update_data->no_update;
            } // if( !empty( $update_data->no_update ) ) {

            foreach( $groups_to_check as $group ) {

                foreach( $group as $plugin_info ) {

                    $info_slug = isset( $plugin_info->slug ) ? $plugin_info->slug : '';

                    if( $info_slug !== $plugin_slug ) { continue; }

                    if( !empty( $plugin_info->icons ) ) {

                        if( !empty( $plugin_info->icons['2x'] ) ) {
                            $icon_url = $plugin_info->icons['2x'];
                        } elseif( !empty( $plugin_info->icons['1x'] ) ) {
                            $icon_url = $plugin_info->icons['1x'];
                        } elseif( !empty( $plugin_info->icons['svg'] ) ) {
                            $icon_url = $plugin_info->icons['svg'];
                        } elseif( !empty( $plugin_info->icons['default'] ) ) {
                            $icon_url = $plugin_info->icons['default'];
                        } // if( !empty( $plugin_info->icons['2x'] ) ) {

                    } // if( !empty( $plugin_info->icons ) ) {

                    break 2;

                } // foreach( $group as $plugin_info ) {

            } // foreach( $groups_to_check as $group ) {

            return $icon_url;

        } // function infinity23_get_plugin_icon_from_update_transient( $plugin_slug ) {

    } // if( !function_exists( 'infinity23_get_plugin_icon_from_update_transient' ) ) {

    if( !function_exists( 'infinity23_get_plugin_icon_url' ) ) {

        function infinity23_get_plugin_icon_url( $plugin_slug, &$icons_cache ) {

            if( isset( $icons_cache[ $plugin_slug ] ) ) {
                return $icons_cache[ $plugin_slug ];
            } // if( isset( $icons_cache[ $plugin_slug ] ) ) {

            $icon_url = infinity23_get_plugin_icon_from_update_transient( $plugin_slug );

            if( !empty( $icon_url ) ) {

                $icons_cache[ $plugin_slug ] = $icon_url;

                return $icon_url;

            } // if( !empty( $icon_url ) ) {

            if( !function_exists( 'plugins_api' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
            } // if( !function_exists( 'plugins_api' ) ) {

            $icon_url = '';

            $api_response = plugins_api( 'plugin_information', array(
                'slug' => $plugin_slug,
                'fields' => array(
                    'icons' => true,
                    'short_description' => false,
                    'description' => false,
                    'sections' => false,
                    'tested' => false,
                    'requires' => false,
                    'rating' => false,
                    'ratings' => false,
                    'downloaded' => false,
                    'downloadlink' => false,
                    'last_updated' => false,
                    'added' => false,
                    'tags' => false,
                    'compatibility' => false,
                    'homepage' => false,
                    'versions' => false,
                    'donate_link' => false,
                    'reviews' => false,
                    'banners' => false,
                    'contributors' => false,
                ),
            ) );

            if( !is_wp_error( $api_response ) && !empty( $api_response->icons ) ) {

                if( !empty( $api_response->icons['2x'] ) ) {
                    $icon_url = $api_response->icons['2x'];
                } elseif( !empty( $api_response->icons['1x'] ) ) {
                    $icon_url = $api_response->icons['1x'];
                } elseif( !empty( $api_response->icons['svg'] ) ) {
                    $icon_url = $api_response->icons['svg'];
                } elseif( !empty( $api_response->icons['default'] ) ) {
                    $icon_url = $api_response->icons['default'];
                } // if( !empty( $api_response->icons['2x'] ) ) {

            } // if( !is_wp_error( $api_response ) && !empty( $api_response->icons ) ) {

            $icons_cache[ $plugin_slug ] = $icon_url;

            return $icon_url;

        } // function infinity23_get_plugin_icon_url( $plugin_slug, &$icons_cache ) {

    } // if( !function_exists( 'infinity23_get_plugin_icon_url' ) ) {

// ===========================================================================
// Theme Icon Lookup - shared by Update Manager and any future theme table
// ===========================================================================

    // --------------------------------------------
    // get/save the cached theme icons array - same pattern as the plugin
    // icons cache, kept as its own separate transient
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_theme_icons_urls_cache' ) ) {

        function infinity23_get_theme_icons_urls_cache() {

            $icons_cache = get_transient( 'infinity23_theme_icons_urls_cache' );

            if( $icons_cache === false ) {
                $icons_cache = array();
            } // if( $icons_cache === false ) {

            return $icons_cache;

        } // function infinity23_get_theme_icons_urls_cache() {

    } // if( !function_exists( 'infinity23_get_theme_icons_urls_cache' ) ) {

    if( !function_exists( 'infinity23_save_theme_icons_urls_cache' ) ) {

        function infinity23_save_theme_icons_urls_cache( $icons_cache ) {

            set_transient( 'infinity23_theme_icons_urls_cache', $icons_cache, DAY_IN_SECONDS );

        } // function infinity23_save_theme_icons_urls_cache( $icons_cache ) {

    } // if( !function_exists( 'infinity23_save_theme_icons_urls_cache' ) ) {

    // --------------------------------------------
    // get a theme's screenshot url from the WordPress.org API - themes don't
    // have an "icons" field like plugins, they use a screenshot image instead
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_theme_icon_url' ) ) {

        function infinity23_get_theme_icon_url( $theme_slug, &$icons_cache ) {

            if( isset( $icons_cache[ $theme_slug ] ) ) {
                return $icons_cache[ $theme_slug ];
            } // if( isset( $icons_cache[ $theme_slug ] ) ) {

            // check the theme's own local screenshot.png/.jpg/.gif first -
            // this is the same file WordPress's own Appearance > Themes page
            // reads, and it exists for custom/premium themes too, not just
            // themes hosted on WordPress.org
            $theme_obj = wp_get_theme( $theme_slug );
            $local_screenshot_url = $theme_obj->exists() ? $theme_obj->get_screenshot() : '';

            if( !empty( $local_screenshot_url ) ) {

                $icons_cache[ $theme_slug ] = $local_screenshot_url;

                return $local_screenshot_url;

            } // if( !empty( $local_screenshot_url ) ) {

            if( !function_exists( 'themes_api' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/theme.php' );
            } // if( !function_exists( 'themes_api' ) ) {

            $icon_url = '';

            $api_response = themes_api( 'theme_information', array(
                'slug' => $theme_slug,
                'fields' => array(
                    'screenshot_url' => true,
                    'description' => false,
                    'sections' => false,
                    'rating' => false,
                    'ratings' => false,
                    'downloaded' => false,
                    'downloadlink' => false,
                    'last_updated' => false,
                    'homepage' => false,
                    'tags' => false,
                    'template' => false,
                    'parent' => false,
                    'versions' => false,
                    'reviews' => false,
                    'num_ratings' => false,
                ),
            ) );

            if( !is_wp_error( $api_response ) && !empty( $api_response->screenshot_url ) ) {

                $icon_url = $api_response->screenshot_url;

                if( strpos( $icon_url, '//' ) === 0 ) {
                    $icon_url = 'https:' . $icon_url;
                } // if( strpos( $icon_url, '//' ) === 0 ) {

            } // if( !is_wp_error( $api_response ) && !empty( $api_response->screenshot_url ) ) {

            $icons_cache[ $theme_slug ] = $icon_url;

            return $icon_url;

        } // function infinity23_get_theme_icon_url( $theme_slug, &$icons_cache ) {

    } // if( !function_exists( 'infinity23_get_theme_icon_url' ) ) {

// ===========================================================================
// Default Module Activation - activates every module enabled by default on
// the Include / Exclude page. Called from install.php on a true first
// install (table gets CREATEd), and from Danger Zone's reset function since
// that only TRUNCATEs the main table (never drops it), so install.php's
// "table missing" check never fires there.
// ===========================================================================

	if( !function_exists( 'infinity23_activate_default_modules' ) ) {

		function infinity23_activate_default_modules() {

			include_once( dirname(__FILE__) . '/../../essential/loader.php' );
			include( dirname(__FILE__) . '/../../essential/include-exclude/default-values.php' );

			$infinity23_install_ie_defaults = $infinity23_defaults;

			global $infinity23_essential_modules_array;

			if( !empty( $infinity23_essential_modules_array ) ) {

				foreach( $infinity23_essential_modules_array as $infinity23_install_module_data ) {

					if( empty( $infinity23_install_module_data['include_exclude_data_key'] ) || empty( $infinity23_install_module_data['activation_db_name'] ) ) {
						continue;
					} // if( empty(

					$infinity23_install_ie_key = $infinity23_install_module_data['include_exclude_data_key'];

					if( !empty( $infinity23_install_module_data['skip_auto_activate'] ) ) {
						continue;
					} // if( !empty( $infinity23_install_module_data['skip_auto_activate'] ) ) {

					if( isset( $infinity23_install_ie_defaults[ $infinity23_install_ie_key ] ) && intval( $infinity23_install_ie_defaults[ $infinity23_install_ie_key ] ) == 1 ) {
						infinity23_add_option( $infinity23_install_module_data['activation_db_name'], 1 );
					} // if( isset(

				} // foreach( $infinity23_essential_modules_array as $infinity23_install_module_data ) {

			} // if( !empty( $infinity23_essential_modules_array ) ) {

		} // function infinity23_activate_default_modules() {

	} // if( !function_exists( 'infinity23_activate_default_modules' ) ) {

// ===========================================================================
// Module Activation Sync - keeps a module's own Activate toggle and its
// Include / Exclude toggle in sync with each other
// ===========================================================================

    // --------------------------------------------
    // map of every module's Include / Exclude key to that module's own
    // activation_db_name - add a new line here whenever a new module gets
    // its own Activate toggle
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_module_activation_map' ) ) {

        function infinity23_get_module_activation_map() {

            global $infinity23_essential_modules_array;

            $activation_map = array();

            if( empty( $infinity23_essential_modules_array ) ) {
                return $activation_map;
            } // if( empty( $infinity23_essential_modules_array ) ) {

            foreach( $infinity23_essential_modules_array as $module_data ) {
                if( empty( $module_data['include_exclude_data_key'] ) || empty( $module_data['activation_db_name'] ) ) {
                    continue;
                } // if( empty( $module_data['include_exclude_data_key'] ) || empty( $module_data['activation_db_name'] ) ) {

                $activation_map[ $module_data['include_exclude_data_key'] ] = $module_data['activation_db_name'];
            } // foreach( $infinity23_essential_modules_array as $module_data ) {

            return $activation_map;

        } // function infinity23_get_module_activation_map() {

    } // if( !function_exists( 'infinity23_get_module_activation_map' ) ) {

    // --------------------------------------------
    // called from a module's own Activate form handler - keeps the
    // Include / Exclude checkbox equal to the module's own toggle
    // --------------------------------------------

    if( !function_exists( 'infinity23_sync_activation_to_include_exclude' ) ) {

        function infinity23_sync_activation_to_include_exclude( $ie_key, $new_value ) {

            $ie_data = infinity23_convert_json_to_array( infinity23_get_option( 'include_exclude_plugins' ) );
            $ie_data[ $ie_key ] = intval( $new_value );
            infinity23_update_option( 'include_exclude_plugins', infinity23_convert_array_to_json( $ie_data ) );

        } // function infinity23_sync_activation_to_include_exclude( $ie_key, $new_value ) {

    } // if( !function_exists( 'infinity23_sync_activation_to_include_exclude' ) ) {

    // --------------------------------------------
    // called from the Include / Exclude form handler - keeps every module's
    // own Activate toggle equal to what was just submitted there
    // --------------------------------------------

    if( !function_exists( 'infinity23_sync_include_exclude_to_activation' ) ) {

        function infinity23_sync_include_exclude_to_activation( $infinity23_data_array ) {

            $activation_map = infinity23_get_module_activation_map();

            foreach( $activation_map as $ie_key => $infinity23_activation_db_name ) {

                if( isset( $infinity23_data_array[ $ie_key ] ) ) {

                    $infinity23_sync_new_value = intval( $infinity23_data_array[ $ie_key ] );

                    // ssl & security is never switched on from here without the same
                    // certificate check its own Activate toggle runs - an old saved
                    // force https setting would otherwise start redirecting right away
                    if( $ie_key === 'ssl_and_security' && $infinity23_sync_new_value === 1 && intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) ) !== 1 ) {

                        global $infinity23_essential_modules_array;

                        if( isset( $infinity23_essential_modules_array['ssl_and_security'] ) ) {

                            include_once( dirname(__FILE__) . "/../../essential/ssl-and-https/back-end/ssl-functions.php" );

                            if( !infinity23_ssl_detect_certificate() ) {
                                $infinity23_sync_new_value = 0;
                                infinity23_sync_activation_to_include_exclude( 'ssl_and_security', 0 );
                            } // if( !infinity23_ssl_detect_certificate() ) {

                        } // if( isset( $infinity23_essential_modules_array['ssl_and_security'] ) ) {

                    } // if( $ie_key === 'ssl_and_security' && $infinity23_sync_new_value === 1 && intval( infinity23_get_option( $infinity23_activation_db_name, 0 ) ) !== 1 ) {

                    infinity23_update_option( $infinity23_activation_db_name, $infinity23_sync_new_value );

                } // if( isset( $infinity23_data_array[ $ie_key ] ) ) {

            } // foreach( $activation_map as $ie_key => $infinity23_activation_db_name ) {

        } // function infinity23_sync_include_exclude_to_activation( $infinity23_data_array ) {

    } // if( !function_exists( 'infinity23_sync_include_exclude_to_activation' ) ) {

    // --------------------------------------------
    // called from a module's own Activate form handler when the module was
    // just turned off - redirects to Include / Exclude and shows a message
    // naming the module, instead of the generic "settings were saved" message
    // --------------------------------------------

    if( !function_exists( 'infinity23_safe_redirect' ) ) {

        function infinity23_safe_redirect( $infinity23_redirect_url ) {

            if( !headers_sent() ) {
                wp_safe_redirect( $infinity23_redirect_url );
            } else {
                echo '<meta http-equiv="refresh" content="0;url=' . esc_url( $infinity23_redirect_url ) . '" />';
                echo '<script>window.location.href = "' . esc_url( $infinity23_redirect_url ) . '";</script>';
            } // if( !headers_sent() ) {

            exit;

        } // function infinity23_safe_redirect( $infinity23_redirect_url ) {

    } // if( !function_exists( 'infinity23_safe_redirect' ) ) {

    if( !function_exists( 'infinity23_module_deactivate_redirect' ) ) {

        function infinity23_module_deactivate_redirect( $infinity23_module_display_name ) {

            $infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-include-exclude', 'module_off' => '1', 'module_name' => rawurlencode( $infinity23_module_display_name ) ), admin_url('admin.php') );

            infinity23_safe_redirect( $infinity23_redirect_url );

        } // function infinity23_module_deactivate_redirect( $infinity23_module_display_name ) {

    } // if( !function_exists( 'infinity23_module_deactivate_redirect' ) ) {

    // ---------------------------------------
    // shared storage root folder - wp-content/uploads/infinity23, or a module's
    // own subfolder inside it when $infinity23_subfolder is given. PREFERRED
    // call for any module needing a folder to write generated files to -
    // never hardcode wp_upload_dir() or WP_CONTENT_DIR inline
    // ---------------------------------------

    if( !function_exists( 'infinity23_get_storage_root_folder' ) ) {

        function infinity23_get_storage_root_folder( $infinity23_subfolder = '' ) {

            $infinity23_upload_dir = wp_upload_dir();
            $infinity23_root_folder = $infinity23_upload_dir['basedir'] . '/infinity23';

            if( !empty( $infinity23_subfolder ) ) {
                $infinity23_root_folder .= '/' . $infinity23_subfolder;
            } // if( !empty( $infinity23_subfolder ) ) {

            return $infinity23_root_folder;

        } // function infinity23_get_storage_root_folder( $infinity23_subfolder = '' ) {

    } // if( !function_exists( 'infinity23_get_storage_root_folder' ) ) {

    // --------------------------------------------
    // can the proxy/cdn headers on this request be trusted - X-Forwarded-Proto,
    // CF-Connecting-IP and friends are ordinary request headers, so any visitor
    // can send them. They only mean something when the request really did come
    // through a proxy: either the proxy runs on this machine (private/loopback
    // REMOTE_ADDR), or the site owner declared it in wp-config.php with
    // define( 'INFINITY23_TRUST_PROXY_HEADERS', true );
    // PREFERRED call before reading ANY HTTP_X_FORWARDED_* / HTTP_CF_* header
    // --------------------------------------------

    if( !function_exists( 'infinity23_proxy_headers_are_trusted' ) ) {

        function infinity23_proxy_headers_are_trusted() {

            if( defined( 'INFINITY23_TRUST_PROXY_HEADERS' ) && INFINITY23_TRUST_PROXY_HEADERS === true ) {
                return true;
            } // if( defined( 'INFINITY23_TRUST_PROXY_HEADERS' ) && INFINITY23_TRUST_PROXY_HEADERS === true ) {

            $infinity23_remote_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

            if( empty( $infinity23_remote_address ) ) {
                return false;
            } // if( empty( $infinity23_remote_address ) ) {

            // a public REMOTE_ADDR means the visitor is talking straight to php,
            // so every header on that request is under their own control
            return ( filter_var( $infinity23_remote_address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false );

        } // function infinity23_proxy_headers_are_trusted() {

    } // if( !function_exists( 'infinity23_proxy_headers_are_trusted' ) ) {

// ===========================================================================
// AJAX - get page title by ID
// ===========================================================================

    // --------------------------------------------
    // handle ajax request to get page/post title by id
    // --------------------------------------------

    add_action( 'wp_ajax_infinity23_get_page_title', function() {

        if( !check_ajax_referer( 'infinity23_get_page_title', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
        } // if( !check_ajax_referer(

        if( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        } // if( !current_user_can( 'manage_options' ) ) {

        $post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : 0;

        if( empty( $post_id ) ) {
            wp_send_json_error( array( 'message' => 'Invalid ID' ) );
        } // if( empty( $post_id ) ) {

        $post = get_post( $post_id );

        if( empty( $post ) ) {
            wp_send_json_error( array( 'message' => 'Page not found' ) );
        } // if( empty( $post ) ) {

        wp_send_json_success( array( 'title' => $post->post_title ) );

    } ); // add_action( 'wp_ajax_infinity23_get_page_title'

// ===========================================================================
// Admin page header banner and review footer
// ===========================================================================

    // --------------------------------------------
    // output the plugin breadcrumb shown at the top of module admin pages
    // $parent_label / $parent_slug describe the top-level menu this module
    // lives under (e.g. Essential, WooCommerce). Pass $parent_label = '' to
    // skip the parent crumb entirely for a module with no parent menu.
    // --------------------------------------------

    if( !function_exists( 'infinity23_modules_header' ) ) {

        function infinity23_modules_header( $page_title = '', $parent_label = 'Essential', $parent_slug = 'essential-include-exclude' ) {

            if( empty( $page_title ) ) {
                $page_title = get_admin_page_title();
            } // if( empty( $page_title ) ) {

            ?>
            <div class="infinity23-modules-header">
                <span class="infinity23-breadcrumb">
                    <span class="infinity23-breadcrumb-root">Infinity23</span>
                    <?php if( !empty( $parent_label ) ) { ?>
                        <span class="infinity23-breadcrumb-sep">›</span>
                        <a class="infinity23-breadcrumb-link" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $parent_slug ) ); ?>"><?php echo esc_html( $parent_label ); ?></a>
                    <?php } // if( !empty( $parent_label ) ) { ?>
                    <?php if( !empty( $page_title ) ) { ?>
                        <span class="infinity23-breadcrumb-sep">›</span>
                        <span class="infinity23-breadcrumb-current"><?php echo esc_html( $page_title ); ?></span>
                    <?php } // if( !empty( $page_title ) ) { ?>
                </span><!--infinity23-breadcrumb-->
            </div><!--infinity23-modules-header-->
        <?php } // function infinity23_modules_header( $page_title = '', $parent_label = 'Essential', $parent_slug = 'essential-include-exclude' ) {

    } // if( !function_exists( 'infinity23_modules_header' ) ) {

    // --------------------------------------------
    // output the review request footer shown at the bottom of module admin pages
    // --------------------------------------------

    if( !function_exists( 'infinity23_modules_footer' ) ) {

        function infinity23_modules_footer() { ?>
            <div class="infinity23-modules-footer">
                <span class="infinity23-modules-footer-heart">♥</span>
                <span class="infinity23-modules-footer-text">Like this plugin? A short review would mean a lot to us.</span>
                <a href="https://wordpress.org/support/plugin/tag-manager-header-body-footer/reviews/#new-post" target="_blank" class="infinity23-modules-footer-btn">Leave a Review</a>
            </div><!--infinity23-modules-footer-->
        <?php } // function infinity23_modules_footer() {

    } // if( !function_exists( 'infinity23_modules_footer' ) ) {

    // --------------------------------------------
    // find the current page/post id being viewed - shared by every module
    // that needs to know which page it is currently outputting on
    // --------------------------------------------

    if( !function_exists( 'infinity23_find_current_page_id' ) ) {

        function infinity23_find_current_page_id() {

            static $infinity23_cached_page_id = null;

            if( $infinity23_cached_page_id !== null ) {
                return $infinity23_cached_page_id;
            } // if( $infinity23_cached_page_id !== null ) {

            $post_id_num = 0;

            if( is_single() || is_page() ) {
                $post_id_num = get_the_ID();
            } // if( is_single() || is_page() ) {

            if( is_home() ) {
                $blog_page_id = get_option( 'page_for_posts' );
                if( !empty( $blog_page_id ) ) {
                    $post_id_num = $blog_page_id;
                } // if( !empty( $blog_page_id ) ) {
            } // if( is_home() ) {

            $infinity23_cached_page_id = intval( $post_id_num );

            return $infinity23_cached_page_id;

        } // function infinity23_find_current_page_id() {

    } // if( !function_exists( 'infinity23_find_current_page_id' ) ) {

    // --------------------------------------------
    // check if a module should display on the given page id, based on that
    // module's own exclude ids array - shared by every module using the
    // Exclude Pages By ID field, so the rule only needs to change here
    // --------------------------------------------

    if( !function_exists( 'infinity23_exclude_include_module_display_based_on_ids_array' ) ) {

        function infinity23_exclude_include_module_display_based_on_ids_array( $page_id, $infinity23_exclude_ids_array, $infinity23_exclude_option = 'exclude' ) {

            if( $infinity23_exclude_option === 'none' ) {
                return true;
            } // if( $infinity23_exclude_option === 'none' ) {

            if( $infinity23_exclude_option === 'include' ) {
                return ( !empty( $infinity23_exclude_ids_array ) && in_array( $page_id, $infinity23_exclude_ids_array ) );
            } // if( $infinity23_exclude_option === 'include' ) {

            if( !empty( $infinity23_exclude_ids_array ) && in_array( $page_id, $infinity23_exclude_ids_array ) ) {
                return false;
            } // if( !empty( $infinity23_exclude_ids_array ) && in_array( $page_id, $infinity23_exclude_ids_array ) ) {

            return true;

        } // function infinity23_exclude_include_module_display_based_on_ids_array( $page_id, $infinity23_exclude_ids_array, $infinity23_exclude_option = 'exclude' ) {

    } // if( !function_exists( 'infinity23_exclude_include_module_display_based_on_ids_array' ) ) {

    // --------------------------------------------
    // parse a comma separated ids string (like "14, 16, 23") into a clean
    // array of positive integers - shared so every module stops repeating
    // its own explode/trim/intval loop just to build this array
    // --------------------------------------------

    if( !function_exists( 'infinity23_parse_ids_csv' ) ) {

        function infinity23_parse_ids_csv( $ids_string ) {

            $ids_array = array();

            if( empty( $ids_string ) ) {
                return $ids_array;
            } // if( empty( $ids_string ) ) {

            $ids_explode = explode( ',', $ids_string );

            foreach( $ids_explode as $single_id ) {

                $single_id = intval( trim( $single_id ) );

                if( !empty( $single_id ) ) {
                    $ids_array[] = $single_id;
                } // if( !empty( $single_id ) ) {

            } // foreach( $ids_explode as $single_id ) {

            return $ids_array;

        } // function infinity23_parse_ids_csv( $ids_string ) {

    } // if( !function_exists( 'infinity23_parse_ids_csv' ) ) {

    // --------------------------------------------
    // single call that does everything a module's front-end needs - finds
    // the current page id (cached), parses the saved comma separated
    // exclude ids string, and applies the none/exclude/include rule.
    // Every module's front-end closures should call ONLY this function -
    // no manual ids array building, no separate page id lookup, and no
    // per-module should_output() wrapper function needed anymore.
    // --------------------------------------------

    if( !function_exists( 'infinity23_module_should_display' ) ) {

        function infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option = 'exclude' ) {

            $page_id = infinity23_find_current_page_id();
            $infinity23_exclude_ids_array = infinity23_parse_ids_csv( $infinity23_exclude_ids );

            return infinity23_exclude_include_module_display_based_on_ids_array( $page_id, $infinity23_exclude_ids_array, $infinity23_exclude_option );

        } // function infinity23_module_should_display( $infinity23_exclude_ids, $infinity23_exclude_option = 'exclude' ) {

    } // if( !function_exists( 'infinity23_module_should_display' ) ) {

    // --------------------------------------------
    // parse a saved button-position value into a struct with desktop/mobile
    // hide, position, spacing and z-index. $legacy_array is a module's own
    // $infinity23_data_array, used to build the struct from the OLD separate fields
    // (horizontal_position, mobile_width, etc.) only when $raw_value is empty,
    // so migrating a module never resets an existing position.
    // --------------------------------------------

    if( !function_exists( 'infinity23_parse_button_position_to_struct' ) ) {

        function infinity23_parse_button_position_to_struct( $raw_value, $legacy_array = array(), $struct_defaults = array() ) {

            $struct = array(
                'hide_desktop' => 0,
                'h_position' => 'right',
                'h_spacing' => '30px',
                'v_position' => 'bottom',
                'v_spacing' => '30px',
                'z_index_desktop' => 9999,
                'hide_mobile' => 0,
                'mobile_width' => 1025,
                'mobile_mode' => 'inherit',
                'mobile_h_position' => 'right',
                'mobile_h_spacing' => '30px',
                'mobile_v_position' => 'bottom',
                'mobile_v_spacing' => '30px',
            );

            if( !empty( $struct_defaults ) ) {
                $struct = array_merge( $struct, $struct_defaults );
            } // if( !empty( $struct_defaults ) ) {

            if( !empty( $raw_value ) ) {

                $decoded = infinity23_convert_json_to_array( $raw_value );

                if( !empty( $decoded ) ) {
                    return array_merge( $struct, $decoded );
                } // if( !empty( $decoded ) ) {

            } // if( !empty( $raw_value ) ) {

            if( !empty( $legacy_array ) ) {

                $struct['hide_desktop'] = isset( $legacy_array['hide_on_desktop_checkbox'] ) ? intval( $legacy_array['hide_on_desktop_checkbox'] ) : $struct['hide_desktop'];
                $struct['h_position'] = isset( $legacy_array['horizontal_position'] ) ? $legacy_array['horizontal_position'] : $struct['h_position'];
                $struct['h_spacing'] = isset( $legacy_array['horizontal_spacing'] ) ? $legacy_array['horizontal_spacing'] : $struct['h_spacing'];
                $struct['v_position'] = isset( $legacy_array['vertical_position'] ) ? $legacy_array['vertical_position'] : $struct['v_position'];
                $struct['v_spacing'] = isset( $legacy_array['vertical_spacing'] ) ? $legacy_array['vertical_spacing'] : $struct['v_spacing'];
                $struct['z_index_desktop'] = isset( $legacy_array['z_index'] ) ? $legacy_array['z_index'] : $struct['z_index_desktop'];
                $struct['hide_mobile'] = isset( $legacy_array['hide_on_mobile_checkbox'] ) ? intval( $legacy_array['hide_on_mobile_checkbox'] ) : $struct['hide_mobile'];
                $struct['mobile_width'] = isset( $legacy_array['mobile_width'] ) ? $legacy_array['mobile_width'] : $struct['mobile_width'];
                $struct['mobile_mode'] = ( isset( $legacy_array['mobile_button_position_checkbox'] ) && intval( $legacy_array['mobile_button_position_checkbox'] ) == 1 ) ? 'custom' : 'inherit';
                $struct['mobile_h_position'] = isset( $legacy_array['mobile_horizontal_position'] ) ? $legacy_array['mobile_horizontal_position'] : $struct['mobile_h_position'];
                $struct['mobile_h_spacing'] = isset( $legacy_array['mobile_horizontal_spacing'] ) ? $legacy_array['mobile_horizontal_spacing'] : $struct['mobile_h_spacing'];
                $struct['mobile_v_position'] = isset( $legacy_array['mobile_vertical_position'] ) ? $legacy_array['mobile_vertical_position'] : $struct['mobile_v_position'];
                $struct['mobile_v_spacing'] = isset( $legacy_array['mobile_vertical_spacing'] ) ? $legacy_array['mobile_vertical_spacing'] : $struct['mobile_v_spacing'];

            } // if( !empty( $legacy_array ) ) {

            return $struct;

        } // function infinity23_parse_button_position_to_struct( $raw_value, $legacy_array = array() ) {

    } // if( !function_exists( 'infinity23_parse_button_position_to_struct' ) ) {

    // --------------------------------------------
    // build a simple desktop + mobile media-query CSS block for a single
    // wrap selector (position, hide, z-index). Modules with sub-elements
    // that also need repositioning (like Accessibility's yy-button/yy-box)
    // should use infinity23_parse_button_position_to_struct() directly instead.
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_button_position_css' ) ) {

        function infinity23_get_button_position_css( $raw_value, $selector ) {

            $struct = infinity23_parse_button_position_to_struct( $raw_value );

            $style = '';

            $style .= $selector . ' {';
                $style .= 'position: fixed;';
                $style .= infinity23_css_sanitize( $struct['h_position'], 'option', 'right', array( 'left', 'right' ) ) . ': ' . infinity23_css_sanitize( $struct['h_spacing'], 'size', '30px' ) . ';';
                $style .= infinity23_css_sanitize( $struct['v_position'], 'option', 'bottom', array( 'top', 'bottom' ) ) . ': ' . infinity23_css_sanitize( $struct['v_spacing'], 'size', '30px' ) . ';';
                $style .= 'z-index: ' . intval( $struct['z_index_desktop'] ) . ';';
                if( !empty( $struct['hide_desktop'] ) ) {
                    $style .= 'display: none;';
                } // if( !empty( $struct['hide_desktop'] ) ) {
            $style .= '}';

            $style .= '@media only screen and (max-width: ' . intval( $struct['mobile_width'] ) . 'px) {';
                $style .= $selector . ' {';
                    if( $struct['mobile_mode'] === 'custom' ) {
                        $style .= infinity23_css_sanitize( $struct['h_position'], 'option', 'right', array( 'left', 'right' ) ) . ': auto;';
                        $style .= infinity23_css_sanitize( $struct['v_position'], 'option', 'bottom', array( 'top', 'bottom' ) ) . ': auto;';
                        $style .= infinity23_css_sanitize( $struct['mobile_h_position'], 'option', 'right', array( 'left', 'right' ) ) . ': ' . infinity23_css_sanitize( $struct['mobile_h_spacing'], 'size', '30px' ) . ';';
                        $style .= infinity23_css_sanitize( $struct['mobile_v_position'], 'option', 'bottom', array( 'top', 'bottom' ) ) . ': ' . infinity23_css_sanitize( $struct['mobile_v_spacing'], 'size', '30px' ) . ';';
                    } // if( $struct['mobile_mode'] === 'custom' ) {
                    $style .= 'z-index: ' . intval( $struct['z_index_desktop'] ) . ';';
                    if( !empty( $struct['hide_mobile'] ) ) {
                        $style .= 'display: none !important;';
                    } // if( !empty( $struct['hide_mobile'] ) ) {
                $style .= '}';
            $style .= '}';

            return $style;

        } // function infinity23_get_button_position_css( $raw_value, $selector ) {

    } // if( !function_exists( 'infinity23_get_button_position_css' ) ) {

    // --------------------------------------------
    // build the printable custom css block from a saved custom css field.
    // Global function, reused by every module that adds a Custom CSS option.
    // Strips tags/breakout attempts a second time (defense in depth on top of
    // the 'css' save-time sanitize type), and is meant to be appended to the
    // very END of a module's own <style> output so it overrides everything else.
    // --------------------------------------------

    if( !function_exists( 'infinity23_get_custom_css_output' ) ) {

        function infinity23_get_custom_css_output( $infinity23_custom_css ) {

            if( empty( trim( strval( $infinity23_custom_css ) ) ) ) {
                return '';
            } // if( empty( trim( strval( $infinity23_custom_css ) ) ) ) {

            $infinity23_custom_css = wp_strip_all_tags( $infinity23_custom_css, false );
            $infinity23_custom_css = infinity23_strip_dangerous_css( $infinity23_custom_css );

            return "\n" . $infinity23_custom_css . "\n";

        } // function infinity23_get_custom_css_output( $infinity23_custom_css ) {

    } // if( !function_exists( 'infinity23_get_custom_css_output' ) ) {

// ======================================================================================================================================================
// loading more functions pages
// ======================================================================================================================================================

include_once( dirname(__FILE__) . "/pro-functions.php" );
include_once( dirname(__FILE__) . "/form-output-functions.php" );
include_once( dirname(__FILE__) . "/encryption-functions.php" );


?>
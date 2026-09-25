<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

	// ===========================================================================
	// This page allows editing llms.txt and robots.txt from the admin panel
	// ===========================================================================

	// --------------------------------------------
	// load essential files for the page (settings file, style, javascript etc...)
	// --------------------------------------------

	include( dirname(__FILE__) . "/../../include/settings.php" );
	include( dirname(__FILE__) . "/internal-settings.php" );
	infinity23_load_essential_files();

	if( !current_user_can( 'manage_options' ) || ( is_multisite() && !is_super_admin() ) ) {
		wp_die( 'Insufficient permissions.' );
	} // if( !current_user_can( 'manage_options' ) || ( is_multisite() && !is_super_admin() ) ) {

	$infinity23_success_message = '';
	$infinity23_error_message   = '';

	if( isset( $_GET['llms_saved'] ) && $_GET['llms_saved'] == '1' ) {
		$infinity23_success_message = "The llms.txt file was saved successfully";
	} // if( isset( $_GET['llms_saved'] ) && $_GET['llms_saved'] == '1' ) {

	if( isset( $_GET['llms_deleted'] ) && $_GET['llms_deleted'] == '1' ) {
		$infinity23_success_message = "The llms.txt file was deleted successfully";
	} // if( isset( $_GET['llms_deleted'] ) && $_GET['llms_deleted'] == '1' ) {

	if( isset( $_GET['robots_saved'] ) && $_GET['robots_saved'] == '1' ) {
		$infinity23_success_message = "The robots.txt file was saved successfully";
	} // if( isset( $_GET['robots_saved'] ) && $_GET['robots_saved'] == '1' ) {

	if( isset( $_GET['robots_deleted'] ) && $_GET['robots_deleted'] == '1' ) {
		$infinity23_success_message = "The robots.txt file was deleted successfully";
	} // if( isset( $_GET['robots_deleted'] ) && $_GET['robots_deleted'] == '1' ) {

	// =================================================================================================================
	// Update the data if the form was submitted
	// =================================================================================================================

	// ----------------------------------------
	// save llms.txt
	// ----------------------------------------

	if( isset( $_POST['infinity23_llms_txt_save'] ) && isset( $_POST['infinity23_llms_txt_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_llms_txt_nonce'] ) ), 'infinity23_llms_txt_action' ) && current_user_can( 'manage_options' ) ) {

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw plain-text file content by design (llms.txt), admin-only (manage_options), written to a text file, never output as HTML or used in a query
			$infinity23_llms_content = ( isset( $_POST['infinity23_llms_txt_content'] ) && !is_array( $_POST['infinity23_llms_txt_content'] ) ) ? wp_unslash( $_POST['infinity23_llms_txt_content'] ) : '';
			$infinity23_llms_filepath = ABSPATH . 'llms.txt';

			$infinity23_llms_content = ltrim( str_replace( "\xEF\xBB\xBF", '', $infinity23_llms_content ) );

			// a .txt file is served with no charset, so a browser falls back to
			// latin-1 and utf-8 bytes show up as garbage characters. The BOM is
			// what makes it detect utf-8, so it is added only when the content
			// actually has non-ascii characters in it - an english-only file stays
			// clean, which is what strict parsers want
			$infinity23_llms_content_to_write = preg_match( '/[^\x00-\x7F]/', $infinity23_llms_content ) ? "\xEF\xBB\xBF" . $infinity23_llms_content : $infinity23_llms_content;

			// phpcs:ignore PluginCheck.CodeAnalysis.WriteFile.ABSPATHDetected -- llms.txt must live at the site root to be discoverable by crawlers, wp_upload_dir() would place it in the wrong location
			$infinity23_llms_write_result = file_put_contents( $infinity23_llms_filepath, $infinity23_llms_content_to_write );

			if( $infinity23_llms_write_result === false ) {
				$infinity23_error_message = array( "The file could not be written. Check that " . esc_html( ABSPATH ) . " is writable by the server." );
			} else {

				$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-llms-and-robots-editor', 'llms_saved' => '1' ), admin_url('admin.php') );
				infinity23_safe_redirect( $infinity23_redirect_url );

			} // if( $infinity23_llms_write_result === false ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // if( isset( $_POST['infinity23_llms_txt_save'] ) ) {

	// ----------------------------------------
	// delete llms.txt
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_llms_txt_delete'] ) && isset( $_POST['infinity23_llms_txt_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_llms_txt_nonce'] ) ), 'infinity23_llms_txt_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_llms_filepath = ABSPATH . 'llms.txt';

			if( file_exists( $infinity23_llms_filepath ) ) {
				wp_delete_file( $infinity23_llms_filepath );
			} // if( file_exists( $infinity23_llms_filepath ) ) {

			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-llms-and-robots-editor', 'llms_deleted' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_llms_txt_delete'] ) ) {

	// ----------------------------------------
	// save robots.txt
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_robots_txt_save'] ) && isset( $_POST['infinity23_robots_txt_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_robots_txt_nonce'] ) ), 'infinity23_robots_txt_action' ) && current_user_can( 'manage_options' ) ) {

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw plain-text file content by design (robots.txt), admin-only (manage_options), written to a text file, never output as HTML or used in a query
			$infinity23_robots_content = ( isset( $_POST['infinity23_robots_txt_content'] ) && !is_array( $_POST['infinity23_robots_txt_content'] ) ) ? wp_unslash( $_POST['infinity23_robots_txt_content'] ) : '';
			$infinity23_robots_filepath = ABSPATH . 'robots.txt';

			$infinity23_robots_content = ltrim( str_replace( "\xEF\xBB\xBF", '', $infinity23_robots_content ) );

			// same rule as llms.txt above - the BOM only goes in when the content
			// is not plain ascii, so a normal english robots.txt has no bytes in
			// front of the first User-agent line
			$infinity23_robots_content_to_write = preg_match( '/[^\x00-\x7F]/', $infinity23_robots_content ) ? "\xEF\xBB\xBF" . $infinity23_robots_content : $infinity23_robots_content;

			// phpcs:ignore PluginCheck.CodeAnalysis.WriteFile.ABSPATHDetected -- robots.txt must live at the site root to be discoverable by crawlers, wp_upload_dir() would place it in the wrong location
			$infinity23_robots_write_result = file_put_contents( $infinity23_robots_filepath, $infinity23_robots_content_to_write );

			if( $infinity23_robots_write_result === false ) {
				$infinity23_error_message = array( "The file could not be written. Check that " . esc_html( ABSPATH ) . " is writable by the server." );
			} else {

				$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-llms-and-robots-editor', 'robots_saved' => '1' ), admin_url('admin.php') );
				infinity23_safe_redirect( $infinity23_redirect_url );

			} // if( $infinity23_robots_write_result === false ) {

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_robots_txt_save'] ) ) {

	// ----------------------------------------
	// delete robots.txt
	// ----------------------------------------

	elseif( isset( $_POST['infinity23_robots_txt_delete'] ) && isset( $_POST['infinity23_robots_txt_nonce'] ) ) {

		if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['infinity23_robots_txt_nonce'] ) ), 'infinity23_robots_txt_action' ) && current_user_can( 'manage_options' ) ) {

			$infinity23_robots_filepath = ABSPATH . 'robots.txt';

			if( file_exists( $infinity23_robots_filepath ) ) {
				wp_delete_file( $infinity23_robots_filepath );
			} // if( file_exists( $infinity23_robots_filepath ) ) {

			$infinity23_redirect_url = add_query_arg( array( 'page' => 'essential-llms-and-robots-editor', 'robots_deleted' => '1' ), admin_url('admin.php') );
			infinity23_safe_redirect( $infinity23_redirect_url );

		} else {
			$infinity23_error_message = array( "Form nonce was incorrect" );
		} // if( wp_verify_nonce(

	} // elseif( isset( $_POST['infinity23_robots_txt_delete'] ) ) {

	// =================================================================================================================
	// read a text file as utf-8 without the BOM - only converts when the file
	// is not already valid utf-8, works without the mbstring extension, and
	// never returns an empty string for a file that has content (an empty
	// editor followed by Save would wipe the real file)
	// =================================================================================================================

	if( !function_exists( 'infinity23_llms_robots_read_file_as_utf8' ) ) {

		function infinity23_llms_robots_read_file_as_utf8( $file_path ) {

			$file_content = file_get_contents( $file_path );

			if( $file_content === false ) {
				return '';
			} // if( $file_content === false ) {

			if( function_exists( 'mb_check_encoding' ) && !mb_check_encoding( $file_content, 'UTF-8' ) ) {

				$converted_content = mb_convert_encoding( $file_content, 'UTF-8', 'ISO-8859-1' );

				if( !empty( $converted_content ) ) {
					$file_content = $converted_content;
				} // if( !empty( $converted_content ) ) {

			} // if( function_exists( 'mb_check_encoding' ) && !mb_check_encoding( $file_content, 'UTF-8' ) ) {

			return str_replace( "\xEF\xBB\xBF", '', $file_content );

		} // function infinity23_llms_robots_read_file_as_utf8( $file_path ) {

	} // if( !function_exists( 'infinity23_llms_robots_read_file_as_utf8' ) ) {

	// =================================================================================================================
	// load llms.txt file data
	// =================================================================================================================

	$infinity23_llms_filepath = ABSPATH . 'llms.txt';
	$infinity23_llms_file_url = home_url( '/llms.txt' );
	$infinity23_llms_exists = file_exists( $infinity23_llms_filepath );
	$infinity23_llms_content = $infinity23_llms_exists ? infinity23_llms_robots_read_file_as_utf8( $infinity23_llms_filepath ) : '';

	// =================================================================================================================
	// load robots.txt file data
	// =================================================================================================================

	$infinity23_robots_filepath = ABSPATH . 'robots.txt';
	$infinity23_robots_file_url = home_url( '/robots.txt' );
	$infinity23_robots_exists = file_exists( $infinity23_robots_filepath );
	$infinity23_robots_content = $infinity23_robots_exists ? infinity23_llms_robots_read_file_as_utf8( $infinity23_robots_filepath ) : '';

?>

<?php infinity23_modules_header( $infinity23_module_display_name ); ?>

<div class="infinity23-warp llms-and-robots-warp no-floating-save">

<?php
	infinity23_show_message( $infinity23_success_message, $infinity23_error_message );
?>

	<div class="page-header">
		<h1>LLMs.txt & Robots.txt Editor</h1>
		<p>Below you will be able to view and edit the llms.txt and robots.txt files in your WordPress root folder:</p>
	</div><!--page-header-->

	<div class="section-card" data-card-default="open">

		<h2>LLMs.txt File <span class="infinity23-txt-editor-badge <?php echo $infinity23_llms_exists ? 'exists' : ''; ?>"><?php echo $infinity23_llms_exists ? 'File exists' : 'Not created yet'; ?></span></h2>

		<p>
			<?php if( $infinity23_llms_exists ) { ?>
				Save your content to update <code>llms.txt</code> in your WordPress root.
			<?php } else { ?>
				Save your content to create <code>llms.txt</code> in your WordPress root.
			<?php } // if( $infinity23_llms_exists ) { ?>
		</p>

		<form class="infinity23-form" method="POST" action="">
			<?php wp_nonce_field( 'infinity23_llms_txt_action', 'infinity23_llms_txt_nonce' ); ?>

			<div class="form-line">
				<textarea name="infinity23_llms_txt_content" id="infinity23_llms_txt_content" class="infinity23-txt-editor-textarea" spellcheck="false"><?php echo esc_textarea( $infinity23_llms_content ); ?></textarea>
			</div><!--form-line-->

			<input type="submit" name="infinity23_llms_txt_save" class="submit-btn" value="Save llms.txt" />

			<p class="infinity23-txt-editor-filepath-note">Public URL: <a href="<?php echo esc_url( $infinity23_llms_file_url ); ?>" target="_blank"><?php echo esc_html( $infinity23_llms_file_url ); ?></a></p>

			<?php if( $infinity23_llms_exists ) { ?>
				<br />
				<button type="submit" name="infinity23_llms_txt_delete" class="infinity23-txt-editor-delete-btn" onclick="return confirm('Are you sure you want to delete llms.txt? This cannot be undone.');">Delete llms.txt File</button>
			<?php } // if( $infinity23_llms_exists ) { ?>

		</form>

	</div><!--section-card-->

	<div class="section-card" data-card-default="open">

		<h2>Robots.txt File <span class="infinity23-txt-editor-badge <?php echo $infinity23_robots_exists ? 'exists' : ''; ?>"><?php echo $infinity23_robots_exists ? 'File exists' : 'Not created yet'; ?></span></h2>

		<p>
			<?php if( $infinity23_robots_exists ) { ?>
				Save your content to update <code>robots.txt</code> in your WordPress root.
			<?php } else { ?>
				Save your content to create <code>robots.txt</code> in your WordPress root.
			<?php } // if( $infinity23_robots_exists ) { ?>
		</p>

		<form class="infinity23-form" method="POST" action="">
			<?php wp_nonce_field( 'infinity23_robots_txt_action', 'infinity23_robots_txt_nonce' ); ?>

			<div class="form-line">
				<textarea name="infinity23_robots_txt_content" id="infinity23_robots_txt_content" class="infinity23-txt-editor-textarea" spellcheck="false"><?php echo esc_textarea( $infinity23_robots_content ); ?></textarea>
			</div><!--form-line-->

			<input type="submit" name="infinity23_robots_txt_save" class="submit-btn" value="Save robots.txt" />

			<p class="infinity23-txt-editor-filepath-note">Public URL: <a href="<?php echo esc_url( $infinity23_robots_file_url ); ?>" target="_blank"><?php echo esc_html( $infinity23_robots_file_url ); ?></a></p>

			<?php if( $infinity23_robots_exists ) { ?>
				<br />
				<button type="submit" name="infinity23_robots_txt_delete" class="infinity23-txt-editor-delete-btn" onclick="return confirm('Are you sure you want to delete robots.txt? This cannot be undone.');">Delete robots.txt File</button>
			<?php } // if( $infinity23_robots_exists ) { ?>

		</form>

	</div><!--section-card-->

</div><!--infinity23-warp-->

<?php infinity23_modules_footer(); ?>
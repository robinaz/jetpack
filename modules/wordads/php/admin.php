<?php

/**
 * The standard set of admin pages for the user if Jetpack is installed
 */
class WordAds_Admin {

	/**
	 * @since 4.4
	 */
	function __construct() {
		global $wordads;

		if ( current_user_can( 'manage_options' ) && isset( $_GET['wordads_debug'] ) ) {
			WordAds_API::update_wordads_status_from_api();
			WordAds_API::update_tos_status_from_api();
			add_action( 'admin_notices', array( $this, 'debug_output' ) );
		}
	}

	/**
	 * Output the API connection debug
	 * @since 4.4
	 */
	function debug_output() {
		global $wordads, $wordads_tos_response, $wordads_status_response;
		$response = 'tos' == $_GET['wordads_debug'] ? $wordads_tos_response : $wordads_status_response;
		if ( empty( $response ) ) {
			$response = 'No response from API :(';
		} else {
			$response = print_r( $response, 1 );
		}

		$tos = $wordads->option( 'wordads_tos' ) ?
			'<span style="color:green;">Yes</span>' :
			'<span style="color:red;">No</span>';
		$status = $wordads->option( 'wordads_approved' ) ?
			'<span style="color:green;">Yes</span>' :
			'<span style="color:red;">No</span>';

		$type = $wordads->option( 'wordads_tos' ) && $wordads->option( 'wordads_approved' ) ?
			'updated' :
			'error';

		echo <<<HTML
		<div class="notice $type is-dismissible">
			<p>TOS: $tos | Status: $status</p>
			<pre>$response</pre>
		</div>
HTML;
	}
}

global $wordads_admin;
$wordads_admin = new WordAds_Admin();

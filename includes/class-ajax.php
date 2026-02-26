<?php
/**
 * AJAX handlers — test connection and clear cache.
 *
 * @package LDAP_Employee_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LDAP_ED_Ajax {

	public function __construct() {
		add_action( 'wp_ajax_ldap_ed_test_connection', array( $this, 'test_connection' ) );
		add_action( 'wp_ajax_ldap_ed_clear_cache',     array( $this, 'clear_cache' ) );
	}

	/** AJAX: Test LDAP connection with current saved settings. */
	public function test_connection() {
		check_ajax_referer( 'ldap_ed_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ldap-employee-directory' ) ), 403 );
		}

		$settings  = get_option( LDAP_ED_OPTION_KEY, array() );
		$connector = new LDAP_ED_Connector( $settings );
		$result    = $connector->test_connection();

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/** AJAX: Clear the users transient cache. */
	public function clear_cache() {
		check_ajax_referer( 'ldap_ed_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ldap-employee-directory' ) ), 403 );
		}

		( new LDAP_ED_Cache() )->purge();

		wp_send_json_success( array( 'message' => __( 'Cache cleared successfully.', 'ldap-employee-directory' ) ) );
	}
}

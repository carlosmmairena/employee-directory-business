<?php
/**
 * Runs when the plugin is uninstalled (deleted from WordPress admin).
 * Removes all plugin options and transients.
 *
 * @package LDAP_Employee_Directory
 */

// WordPress confirms uninstall context before calling this file.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove settings.
delete_option( 'ldap_ed_settings' );

// Remove the users cache transient.
delete_transient( 'ldap_ed_users' );

// Multisite: remove per-site options.
if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids', 'number' => 0 ) );
	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );
		delete_option( 'ldap_ed_settings' );
		delete_transient( 'ldap_ed_users' );
		restore_current_blog();
	}
}

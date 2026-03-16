<?php
/**
 * Beaver Builder module — frontend render template.
 * The $module and $settings variables are provided by FLBuilder.
 *
 * @package LDAP_Staff_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Build field list.
$ldap_ed_fields_raw = $settings->fields_to_show ?? array( 'name', 'email', 'title', 'department' );
$ldap_ed_fields     = is_array( $ldap_ed_fields_raw ) ? implode( ',', $ldap_ed_fields_raw ) : $ldap_ed_fields_raw;
$ldap_ed_per_page   = absint( $settings->per_page ?? 20 );
$ldap_ed_search     = $settings->enable_search ?? 'true';

// CSS variables are now enqueued via LDAP_ED_BB_Module::enqueue_scripts() during wp_enqueue_scripts.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- do_shortcode() returns safe HTML; shortcode attribute values are individually escaped above.
echo do_shortcode(
	'[ldap_directory search="' . esc_attr( $ldap_ed_search ) . '" per_page="' . absint( $ldap_ed_per_page ) . '" fields="' . esc_attr( $ldap_ed_fields ) . '"]'
);

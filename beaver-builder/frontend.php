<?php
/**
 * Beaver Builder module — frontend render template.
 * The $module and $settings variables are provided by FLBuilder.
 *
 * @package LDAP_Employee_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Build field list.
$ldap_ed_fields_raw = $settings->fields_to_show ?? array( 'name', 'email', 'title', 'department' );
$ldap_ed_fields     = is_array( $ldap_ed_fields_raw ) ? implode( ',', $ldap_ed_fields_raw ) : $ldap_ed_fields_raw;
$ldap_ed_per_page   = absint( $settings->per_page ?? 20 );
$ldap_ed_search     = $settings->enable_search ?? 'true';
$ldap_ed_columns    = absint( $settings->columns ?? 3 );

// Inject dynamic CSS variables scoped to this module instance.
$ldap_ed_uid             = $module->node;
$ldap_ed_primary_color   = ! empty( $settings->primary_color )  ? '#' . ltrim( $settings->primary_color, '#' )  : '#0073aa';
$ldap_ed_card_bg         = ! empty( $settings->card_bg_color )  ? '#' . ltrim( $settings->card_bg_color, '#' )   : '#ffffff';
$ldap_ed_text_color      = ! empty( $settings->text_color )     ? '#' . ltrim( $settings->text_color, '#' )      : '#3c434a';
$ldap_ed_font_size       = absint( $settings->font_size ?? 14 );
$ldap_ed_gap             = absint( $settings->gap ?? 20 );
$ldap_ed_border_radius   = absint( $settings->border_radius ?? 6 );
?>

<style>
.fl-node-<?php echo esc_attr( $ldap_ed_uid ); ?> .ldap-directory-wrap {
	--ldap-primary-color: <?php echo esc_attr( $ldap_ed_primary_color ); ?>;
	--ldap-card-bg:       <?php echo esc_attr( $ldap_ed_card_bg ); ?>;
	--ldap-text-color:    <?php echo esc_attr( $ldap_ed_text_color ); ?>;
	--ldap-font-size:     <?php echo esc_attr( $ldap_ed_font_size ); ?>px;
	--ldap-gap:           <?php echo esc_attr( $ldap_ed_gap ); ?>px;
	--ldap-card-radius:   <?php echo esc_attr( $ldap_ed_border_radius ); ?>px;
	--ldap-columns:       <?php echo esc_attr( $ldap_ed_columns ); ?>;
}
<?php
if ( ! empty( $settings->custom_css ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS sanitized on save via wp_strip_all_tags(); esc_html() would break CSS selectors.
	echo wp_strip_all_tags( $settings->custom_css );
}
?>
</style>

<?php
echo do_shortcode(
	'[ldap_directory search="' . esc_attr( $ldap_ed_search ) . '" per_page="' . esc_attr( $ldap_ed_per_page ) . '" fields="' . esc_attr( $ldap_ed_fields ) . '"]'
);

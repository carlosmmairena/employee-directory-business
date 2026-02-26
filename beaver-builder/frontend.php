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
$fields_raw = $settings->fields_to_show ?? array( 'name', 'email', 'title', 'department' );
$fields     = is_array( $fields_raw ) ? implode( ',', $fields_raw ) : $fields_raw;
$per_page   = absint( $settings->per_page ?? 20 );
$search     = $settings->enable_search ?? 'true';
$columns    = absint( $settings->columns ?? 3 );

// Inject dynamic CSS variables scoped to this module instance.
$uid             = $module->node;
$primary_color   = ! empty( $settings->primary_color )  ? '#' . ltrim( $settings->primary_color, '#' )  : '#0073aa';
$card_bg         = ! empty( $settings->card_bg_color )  ? '#' . ltrim( $settings->card_bg_color, '#' )   : '#ffffff';
$text_color      = ! empty( $settings->text_color )     ? '#' . ltrim( $settings->text_color, '#' )      : '#3c434a';
$font_size       = absint( $settings->font_size ?? 14 );
$gap             = absint( $settings->gap ?? 20 );
$border_radius   = absint( $settings->border_radius ?? 6 );
?>

<style>
.fl-node-<?php echo esc_attr( $uid ); ?> .ldap-directory-wrap {
	--ldap-primary-color: <?php echo esc_attr( $primary_color ); ?>;
	--ldap-card-bg:       <?php echo esc_attr( $card_bg ); ?>;
	--ldap-text-color:    <?php echo esc_attr( $text_color ); ?>;
	--ldap-font-size:     <?php echo esc_attr( $font_size ); ?>px;
	--ldap-gap:           <?php echo esc_attr( $gap ); ?>px;
	--ldap-card-radius:   <?php echo esc_attr( $border_radius ); ?>px;
	--ldap-columns:       <?php echo esc_attr( $columns ); ?>;
}
<?php
if ( ! empty( $settings->custom_css ) ) {
	echo wp_strip_all_tags( $settings->custom_css );
}
?>
</style>

<?php
echo do_shortcode(
	'[ldap_directory search="' . esc_attr( $search ) . '" per_page="' . esc_attr( $per_page ) . '" fields="' . esc_attr( $fields ) . '"]'
);

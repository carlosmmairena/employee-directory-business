<?php
/**
 * Admin settings page view.
 *
 * @package LDAP_Employee_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap ldap-ed-admin-wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php settings_errors(); ?>

	<div class="ldap-ed-admin-layout">

		<!-- ===== Settings form ===== -->
		<div class="ldap-ed-settings-col">
			<form method="post" action="options.php">
				<?php
				settings_fields( 'ldap_ed_settings_group' );
				do_settings_sections( 'employee-directory-business' );
				submit_button();
				?>
			</form>
		</div>

		<!-- ===== Sidebar tools ===== -->
		<div class="ldap-ed-sidebar-col">

			<!-- Test connection card -->
			<div class="ldap-ed-card ldap-ed-card--connection">
				<h2><?php esc_html_e( 'Test Connection', 'employee-directory-business' ); ?></h2>
				<p><?php esc_html_e( 'Verify that WordPress can reach the LDAP server with the current settings.', 'employee-directory-business' ); ?></p>
				<button type="button" id="ldap-ed-test-btn" class="button button-primary">
					<?php esc_html_e( 'Test Connection', 'employee-directory-business' ); ?>
				</button>
				<div id="ldap-ed-test-result" class="ldap-ed-test-result" aria-live="polite"></div>
			</div>

			<!-- Cache card -->
			<div class="ldap-ed-card ldap-ed-card--cache">
				<h2><?php esc_html_e( 'Cache', 'employee-directory-business' ); ?></h2>
				<p><?php esc_html_e( 'User data is cached to reduce LDAP queries. Click below to force a refresh.', 'employee-directory-business' ); ?></p>
				<button type="button" id="ldap-ed-clear-cache-btn" class="button button-secondary">
					<?php esc_html_e( 'Clear Cache', 'employee-directory-business' ); ?>
				</button>
				<div id="ldap-ed-cache-result" class="ldap-ed-test-result" aria-live="polite"></div>
			</div>

			<!-- Shortcode reference card -->
			<div class="ldap-ed-card ldap-ed-card--usage">
				<h2><?php esc_html_e( 'Usage', 'employee-directory-business' ); ?></h2>
				<p><?php esc_html_e( 'Insert this shortcode in any post or page:', 'employee-directory-business' ); ?></p>
				<code>[ldap_directory]</code>
				<p class="ldap-ed-card__label"><?php esc_html_e( 'With custom attributes:', 'employee-directory-business' ); ?></p>
				<code>[ldap_directory search="true" per_page="10" fields="name,email,title"]</code>
			</div>

		</div><!-- /.ldap-ed-sidebar-col -->
	</div><!-- /.ldap-ed-admin-layout -->
</div><!-- /.wrap -->

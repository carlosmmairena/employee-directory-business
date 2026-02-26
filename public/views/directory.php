<?php
/**
 * Public directory template.
 *
 * Variables available from class-shortcode.php:
 *   $users         — array of user arrays (name, email, title, department)
 *   $fields        — array of field keys to display
 *   $per_page      — int, items per page
 *   $enable_search — bool, show search input
 *
 * @package LDAP_Employee_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Palette for avatar backgrounds — one color is deterministically assigned per employee.
$ldap_avatar_palette = array(
	'#4f7df3', '#7c5cbf', '#0e9b8a',
	'#2e9e4f', '#c0392b', '#d35400',
	'#1a7bbf', '#8e44ad',
);
?>
<div
	class="ldap-directory-wrap"
	data-per-page="<?php echo esc_attr( $per_page ); ?>"
	data-total="<?php echo esc_attr( count( $users ) ); ?>"
>

	<?php if ( $enable_search ) : ?>
	<div class="ldap-search-wrap">
		<label for="ldap-search-input" class="screen-reader-text">
			<?php esc_html_e( 'Search employees', 'ldap-employee-directory' ); ?>
		</label>
		<input
			type="search"
			id="ldap-search-input"
			class="ldap-search"
			placeholder="<?php esc_attr_e( 'Search employee…', 'ldap-employee-directory' ); ?>"
			aria-label="<?php esc_attr_e( 'Search employee', 'ldap-employee-directory' ); ?>"
		>
	</div>
	<?php endif; ?>

	<div class="ldap-directory-grid" aria-live="polite">

		<?php if ( empty( $users ) ) : ?>
			<p class="ldap-no-results"><?php esc_html_e( 'No employees found.', 'ldap-employee-directory' ); ?></p>
		<?php else : ?>
			<?php foreach ( $users as $user ) :
				// Compute initials (up to 2 characters) from the display name.
				$ldap_name     = $user['name'] ?? '';
				$ldap_parts    = preg_split( '/\s+/', trim( $ldap_name ), 2 );
				$ldap_initials = strtoupper( substr( $ldap_parts[0] ?? '', 0, 1 ) );
				if ( ! empty( $ldap_parts[1] ) ) {
					$ldap_initials .= strtoupper( substr( $ldap_parts[1], 0, 1 ) );
				}
				// Fall back to first letter of email when name is absent.
				if ( '' === $ldap_initials && ! empty( $user['email'] ) ) {
					$ldap_initials = strtoupper( substr( $user['email'], 0, 1 ) );
				}

				// Deterministic background color derived from the name.
				$ldap_color_idx = abs( crc32( $ldap_name ) ) % count( $ldap_avatar_palette );
				$ldap_avatar_bg = $ldap_avatar_palette[ $ldap_color_idx ];
			?>
			<article
				class="ldap-employee-card"
				data-name="<?php echo esc_attr( strtolower( $user['name'] ) ); ?>"
				data-email="<?php echo esc_attr( strtolower( $user['email'] ) ); ?>"
				data-title="<?php echo esc_attr( strtolower( $user['title'] ) ); ?>"
				data-department="<?php echo esc_attr( strtolower( $user['department'] ) ); ?>"
			>
				<div
					class="ldap-card-avatar"
					aria-hidden="true"
					style="--ldap-avatar-bg:<?php echo esc_attr( $ldap_avatar_bg ); ?>"
				><?php echo esc_html( $ldap_initials ); ?></div>

				<?php if ( in_array( 'name', $fields, true ) && ! empty( $user['name'] ) ) : ?>
				<h3 class="ldap-name"><?php echo esc_html( $user['name'] ); ?></h3>
				<?php endif; ?>

				<?php if ( in_array( 'title', $fields, true ) && ! empty( $user['title'] ) ) : ?>
				<p class="ldap-title"><?php echo esc_html( $user['title'] ); ?></p>
				<?php endif; ?>

				<?php if ( in_array( 'department', $fields, true ) && ! empty( $user['department'] ) ) : ?>
				<p class="ldap-department">
					<span class="ldap-dept-badge"><?php echo esc_html( $user['department'] ); ?></span>
				</p>
				<?php endif; ?>

				<?php if ( in_array( 'email', $fields, true ) && ! empty( $user['email'] ) ) : ?>
				<a class="ldap-email" href="mailto:<?php echo esc_attr( $user['email'] ); ?>">
					<?php echo esc_html( $user['email'] ); ?>
				</a>
				<?php endif; ?>
			</article>
			<?php endforeach; ?>
		<?php endif; ?>

	</div><!-- /.ldap-directory-grid -->

	<p class="ldap-no-results ldap-no-results--search" style="display:none" aria-live="polite">
		<?php esc_html_e( 'No employees match your search.', 'ldap-employee-directory' ); ?>
	</p>

	<nav class="ldap-pagination" aria-label="<?php esc_attr_e( 'Directory pagination', 'ldap-employee-directory' ); ?>">
		<button type="button" class="ldap-btn ldap-prev" disabled>
			&laquo; <?php esc_html_e( 'Previous', 'ldap-employee-directory' ); ?>
		</button>
		<span class="ldap-page-info"></span>
		<button type="button" class="ldap-btn ldap-next">
			<?php esc_html_e( 'Next', 'ldap-employee-directory' ); ?> &raquo;
		</button>
	</nav>

</div><!-- /.ldap-directory-wrap -->

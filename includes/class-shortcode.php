<?php
/**
 * Shortcode [ldap_directory] — renders the employee directory.
 *
 * @package LDAP_Employee_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LDAP_ED_Shortcode {

	public function __construct() {
		add_shortcode( 'ldap_directory', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Register front-end CSS and JS, and eagerly enqueue when the shortcode is
	 * present in the current singular post's content.
	 */
	public function register_assets() {
		wp_register_style(
			'ldap-ed-public',
			LDAP_ED_URL . 'public/css/directory.css',
			array(),
			LDAP_ED_VERSION
		);

		wp_register_script(
			'ldap-ed-public',
			LDAP_ED_URL . 'public/js/directory.js',
			array(),
			LDAP_ED_VERSION,
			true
		);

		// Enqueue early (in <head>) when the shortcode is directly in the post content.
		// Page builder integrations fall back to the enqueue inside render().
		global $post;
		if ( is_singular() && $post instanceof WP_Post && has_shortcode( $post->post_content, 'ldap_directory' ) ) {
			$this->enqueue_assets();
		}
	}

	/**
	 * Enqueue the registered assets and inject any custom CSS.
	 * Safe to call multiple times — WordPress ignores duplicate enqueues.
	 */
	private function enqueue_assets() {
		wp_enqueue_style( 'ldap-ed-public' );
		wp_enqueue_script( 'ldap-ed-public' );

		$settings   = get_option( LDAP_ED_OPTION_KEY, array() );
		$custom_css = $settings['custom_css'] ?? '';
		if ( ! empty( $custom_css ) ) {
			wp_add_inline_style( 'ldap-ed-public', $custom_css );
		}
	}

	/**
	 * Render the directory HTML.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render( $atts ) {
		// Enqueue assets here as a fallback for page builders (Elementor, Beaver Builder)
		// that do not store shortcodes in post_content and therefore bypass register_assets().
		$this->enqueue_assets();

		$settings = get_option( LDAP_ED_OPTION_KEY, array() );

		$atts = shortcode_atts(
			array(
				'search'   => $settings['enable_search'] ?? '1',
				'per_page' => $settings['per_page'] ?? 20,
				'fields'   => implode( ',', (array) ( $settings['fields'] ?? array( 'name', 'email', 'title', 'department' ) ) ),
			),
			$atts,
			'ldap_directory'
		);

		// Resolve field list from the shortcode attribute.
		$allowed_fields = array( 'name', 'email', 'title', 'department' );
		$fields         = array_intersect(
			array_map( 'trim', explode( ',', $atts['fields'] ) ),
			$allowed_fields
		);

		// Retrieve users (from cache or live LDAP).
		$users = $this->get_users( $settings );

		if ( is_wp_error( $users ) ) {
			return '<p class="ldap-ed-error">' . esc_html( $users->get_error_message() ) . '</p>';
		}

		// Build data attributes for the JS layer.
		$per_page      = absint( $atts['per_page'] );
		$enable_search = filter_var( $atts['search'], FILTER_VALIDATE_BOOLEAN );

		ob_start();
		include LDAP_ED_DIR . 'public/views/directory.php';
		return ob_get_clean();
	}

	/**
	 * Return users from cache or fetch fresh from LDAP.
	 *
	 * @param array $settings Plugin settings.
	 * @return array|\WP_Error
	 */
	private function get_users( array $settings ) {
		$ttl   = absint( $settings['cache_ttl'] ?? 60 ) * 60; // convert minutes → seconds
		$cache = new LDAP_ED_Cache( LDAP_ED_CACHE_KEY, $ttl );
		$users = $cache->get();

		if ( false === $users ) {
			$connector = new LDAP_ED_Connector( $settings );
			$users     = $connector->get_users();

			if ( ! is_wp_error( $users ) ) {
				$cache->set( $users );
			}
		}

		return $users;
	}
}

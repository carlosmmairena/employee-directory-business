<?php
/**
 * Admin panel — settings page registration and rendering.
 *
 * @package LDAP_Employee_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LDAP_ED_Admin {

	public function __construct() {
		add_action( 'admin_menu',            array( $this, 'add_menu' ) );
		add_action( 'admin_init',            array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_notices',         array( $this, 'maybe_show_ldap_extension_notice' ) );
	}

	/**
	 * Show an admin notice if the PHP LDAP extension is not loaded at runtime.
	 */
	public function maybe_show_ldap_extension_notice() {
		if ( extension_loaded( 'ldap' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'LDAP Employee Directory requires the PHP LDAP extension, which is not currently enabled on this server. The directory will not function until the extension is loaded.', 'ldap-employee-directory' )
		);
	}

	/** Add the settings sub-menu under "Settings". */
	public function add_menu() {
		add_options_page(
			__( 'LDAP Employee Directory — Settings', 'ldap-employee-directory' ),
			__( 'LDAP Directory', 'ldap-employee-directory' ),
			'manage_options',
			'ldap-employee-directory',
			array( $this, 'render_settings_page' )
		);
	}

	/** Register plugin options via Settings API. */
	public function register_settings() {
		register_setting(
			'ldap_ed_settings_group',
			LDAP_ED_OPTION_KEY,
			array( $this, 'sanitize_settings' )
		);

		// --- LDAP Connection section ---
		add_settings_section(
			'ldap_ed_section_connection',
			__( 'LDAP Connection', 'ldap-employee-directory' ),
			'__return_false',
			'ldap-employee-directory'
		);

		// Text/number fields get label_for so the <th> label links to the input.
		$connection_text_fields = array(
			'server'    => __( 'LDAPS Server', 'ldap-employee-directory' ),
			'port'      => __( 'Port', 'ldap-employee-directory' ),
			'bind_dn'   => __( 'Bind DN', 'ldap-employee-directory' ),
			'bind_pass' => __( 'Bind Password', 'ldap-employee-directory' ),
			'base_ou'   => __( 'Base OU', 'ldap-employee-directory' ),
			'ca_cert'   => __( 'CA Certificate Path (.pem)', 'ldap-employee-directory' ),
		);

		foreach ( $connection_text_fields as $id => $label ) {
			add_settings_field(
				'ldap_ed_' . $id,
				$label,
				array( $this, 'render_field_' . $id ),
				'ldap-employee-directory',
				'ldap_ed_section_connection',
				array( 'label_for' => 'ldap_ed_' . $id )
			);
		}

		// Checkbox field — inline label in callback; skip label_for to avoid double-label.
		add_settings_field(
			'ldap_ed_verify_ssl',
			__( 'Verify SSL Certificate', 'ldap-employee-directory' ),
			array( $this, 'render_field_verify_ssl' ),
			'ldap-employee-directory',
			'ldap_ed_section_connection'
		);

		// Checkbox — no label_for.
		add_settings_field(
			'ldap_ed_exclude_disabled',
			__( 'Exclude Disabled Accounts', 'ldap-employee-directory' ),
			array( $this, 'render_field_exclude_disabled' ),
			'ldap-employee-directory',
			'ldap_ed_section_connection'
		);

		// --- Display section ---
		add_settings_section(
			'ldap_ed_section_display',
			__( 'Display Options', 'ldap-employee-directory' ),
			'__return_false',
			'ldap-employee-directory'
		);

		// Multi-checkbox — no label_for.
		add_settings_field(
			'ldap_ed_fields',
			__( 'Fields to Show', 'ldap-employee-directory' ),
			array( $this, 'render_field_fields' ),
			'ldap-employee-directory',
			'ldap_ed_section_display'
		);

		add_settings_field(
			'ldap_ed_per_page',
			__( 'Items per Page', 'ldap-employee-directory' ),
			array( $this, 'render_field_per_page' ),
			'ldap-employee-directory',
			'ldap_ed_section_display',
			array( 'label_for' => 'ldap_ed_per_page' )
		);

		// Checkbox — no label_for.
		add_settings_field(
			'ldap_ed_enable_search',
			__( 'Enable Search Bar', 'ldap-employee-directory' ),
			array( $this, 'render_field_enable_search' ),
			'ldap-employee-directory',
			'ldap_ed_section_display'
		);

		add_settings_field(
			'ldap_ed_custom_css',
			__( 'Custom CSS', 'ldap-employee-directory' ),
			array( $this, 'render_field_custom_css' ),
			'ldap-employee-directory',
			'ldap_ed_section_display',
			array( 'label_for' => 'ldap_ed_custom_css' )
		);

		// --- Cache section ---
		add_settings_section(
			'ldap_ed_section_cache',
			__( 'Cache', 'ldap-employee-directory' ),
			'__return_false',
			'ldap-employee-directory'
		);

		add_settings_field(
			'ldap_ed_cache_ttl',
			__( 'Cache TTL (minutes)', 'ldap-employee-directory' ),
			array( $this, 'render_field_cache_ttl' ),
			'ldap-employee-directory',
			'ldap_ed_section_cache',
			array( 'label_for' => 'ldap_ed_cache_ttl' )
		);
	}

	/** Sanitize and validate settings before saving. */
	public function sanitize_settings( $input ) {
		$clean    = array();
		$existing = get_option( LDAP_ED_OPTION_KEY, array() );

		$clean['server']        = $this->sanitize_ldap_server( $input['server'] ?? '', $existing['server'] ?? '' );
		$clean['port']          = absint( $input['port'] ?? 636 );
		$clean['bind_dn']       = sanitize_text_field( $input['bind_dn'] ?? '' );
		$clean['base_ou']       = sanitize_text_field( $input['base_ou'] ?? '' );
		$clean['verify_ssl']        = isset( $input['verify_ssl'] ) ? '1' : '0';
		$clean['ca_cert']           = sanitize_text_field( $input['ca_cert'] ?? '' );
		$clean['exclude_disabled']  = isset( $input['exclude_disabled'] ) ? '1' : '0';
		$clean['per_page']          = absint( $input['per_page'] ?? 20 );
		$clean['enable_search'] = isset( $input['enable_search'] ) ? '1' : '0';
		$clean['custom_css']    = wp_strip_all_tags( $input['custom_css'] ?? '' );
		$clean['cache_ttl']     = absint( $input['cache_ttl'] ?? 60 );

		// Allowed field keys.
		$allowed_fields  = array( 'name', 'email', 'title', 'department', 'phone' );
		$clean['fields'] = array();
		if ( ! empty( $input['fields'] ) && is_array( $input['fields'] ) ) {
			foreach ( $input['fields'] as $field ) {
				if ( in_array( $field, $allowed_fields, true ) ) {
					$clean['fields'][] = $field;
				}
			}
		}

		// Only update password if a new one was supplied.
		$clean['bind_pass'] = ! empty( $input['bind_pass'] )
			? $input['bind_pass']
			: ( $existing['bind_pass'] ?? '' );

		// Settings changed — purge both TTL transient and stale option since the
		// LDAP server or connection parameters may have changed.
		( new LDAP_ED_Cache() )->purge();

		return $clean;
	}

	/**
	 * Sanitize the LDAP server URL, allowing only ldap:// and ldaps:// schemes.
	 *
	 * @param string $raw      Raw submitted value.
	 * @param string $previous Previously saved value (fallback on invalid scheme).
	 * @return string
	 */
	private function sanitize_ldap_server( $raw, $previous ) {
		$value = trim( $raw );

		if ( '' === $value ) {
			return '';
		}

		if ( preg_match( '#^(ldaps?)://#i', $value, $matches ) ) {
			$scheme    = strtolower( $matches[1] );
			$remainder = substr( $value, strlen( $matches[0] ) );
			return $scheme . '://' . sanitize_text_field( $remainder );
		}

		add_settings_error(
			LDAP_ED_OPTION_KEY,
			'ldap_ed_invalid_server_scheme',
			/* translators: %s: the submitted server value */
			sprintf(
				__( 'Invalid LDAP server URL "%s". The URL must begin with ldap:// or ldaps://. The previous value has been kept.', 'ldap-employee-directory' ),
				esc_html( $value )
			),
			'error'
		);

		return $previous;
	}

	/** Enqueue admin CSS and JS only on the plugin settings page. */
	public function enqueue_assets( $hook ) {
		if ( 'settings_page_ldap-employee-directory' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'ldap-ed-admin',
			LDAP_ED_URL . 'admin/css/admin.css',
			array(),
			LDAP_ED_VERSION
		);

		wp_enqueue_script(
			'ldap-ed-admin',
			LDAP_ED_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			LDAP_ED_VERSION,
			true
		);

		wp_localize_script(
			'ldap-ed-admin',
			'ldapEdAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ldap_ed_admin_nonce' ),
				'i18n'    => array(
					'testing'    => __( 'Testing…', 'ldap-employee-directory' ),
					'clearing'   => __( 'Clearing…', 'ldap-employee-directory' ),
					'cacheCleared' => __( 'Cache cleared.', 'ldap-employee-directory' ),
				),
			)
		);
	}

	/** Render the main settings page. */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		require LDAP_ED_DIR . 'admin/views/settings-page.php';
	}

	// -------------------------------------------------------------------------
	// Field renderers
	// -------------------------------------------------------------------------

	private function get_option( $key, $default = '' ) {
		$settings = get_option( LDAP_ED_OPTION_KEY, array() );
		return $settings[ $key ] ?? $default;
	}

	/** @param array $args Settings field args passed by the Settings API. */
	public function render_field_server( $args = array() ) {
		printf(
			'<input type="text" id="%1$s" name="%2$s[server]" value="%3$s" class="regular-text" placeholder="ldaps://directory.example.com">',
			esc_attr( $args['label_for'] ),
			esc_attr( LDAP_ED_OPTION_KEY ),
			esc_attr( $this->get_option( 'server', 'ldaps://' ) )
		);
	}

	/** @param array $args Settings field args passed by the Settings API. */
	public function render_field_port( $args = array() ) {
		printf(
			'<input type="number" id="%1$s" name="%2$s[port]" value="%3$s" class="small-text" min="1" max="65535">',
			esc_attr( $args['label_for'] ),
			esc_attr( LDAP_ED_OPTION_KEY ),
			esc_attr( $this->get_option( 'port', 636 ) )
		);
	}

	/** @param array $args Settings field args passed by the Settings API. */
	public function render_field_bind_dn( $args = array() ) {
		printf(
			'<input type="text" id="%1$s" name="%2$s[bind_dn]" value="%3$s" class="regular-text" placeholder="cn=admin,dc=example,dc=com">',
			esc_attr( $args['label_for'] ),
			esc_attr( LDAP_ED_OPTION_KEY ),
			esc_attr( $this->get_option( 'bind_dn' ) )
		);
	}

	/** @param array $args Settings field args passed by the Settings API. */
	public function render_field_bind_pass( $args = array() ) {
		// Never echo the saved password back into the page.
		printf(
			'<input type="password" id="%1$s" name="%2$s[bind_pass]" value="" class="regular-text" autocomplete="new-password" placeholder="%3$s">',
			esc_attr( $args['label_for'] ),
			esc_attr( LDAP_ED_OPTION_KEY ),
			esc_attr__( '(leave blank to keep current)', 'ldap-employee-directory' )
		);
	}

	/** @param array $args Settings field args passed by the Settings API. */
	public function render_field_base_ou( $args = array() ) {
		printf(
			'<input type="text" id="%1$s" name="%2$s[base_ou]" value="%3$s" class="regular-text" placeholder="ou=employees,dc=example,dc=com">',
			esc_attr( $args['label_for'] ),
			esc_attr( LDAP_ED_OPTION_KEY ),
			esc_attr( $this->get_option( 'base_ou' ) )
		);
	}

	public function render_field_verify_ssl() {
		printf(
			'<label><input type="checkbox" name="%1$s[verify_ssl]" value="1" %2$s> %3$s</label>',
			esc_attr( LDAP_ED_OPTION_KEY ),
			checked( '1', $this->get_option( 'verify_ssl', '1' ), false ),
			esc_html__( 'Enable certificate verification (disable for self-signed certs)', 'ldap-employee-directory' )
		);
	}

	public function render_field_exclude_disabled() {
		printf(
			'<label><input type="checkbox" name="%1$s[exclude_disabled]" value="1" %2$s> %3$s</label><p class="description">%4$s</p>',
			esc_attr( LDAP_ED_OPTION_KEY ),
			checked( '1', $this->get_option( 'exclude_disabled', '0' ), false ),
			esc_html__( 'Exclude disabled accounts from the directory', 'ldap-employee-directory' ),
			esc_html__( 'Uses the Active Directory userAccountControl attribute. Leave unchecked for OpenLDAP and other servers.', 'ldap-employee-directory' )
		);
	}

	/** @param array $args Settings field args passed by the Settings API. */
	public function render_field_ca_cert( $args = array() ) {
		printf(
			'<input type="text" id="%1$s" name="%2$s[ca_cert]" value="%3$s" class="regular-text" placeholder="/etc/ssl/certs/ca.pem"><p class="description">%4$s</p>',
			esc_attr( $args['label_for'] ),
			esc_attr( LDAP_ED_OPTION_KEY ),
			esc_attr( $this->get_option( 'ca_cert' ) ),
			esc_html__( 'Full server path to the CA certificate file. Used when SSL verification is enabled.', 'ldap-employee-directory' )
		);
	}

	public function render_field_fields() {
		$saved = $this->get_option( 'fields', array( 'name', 'email', 'title', 'department' ) );
		$items = array(
			'name'       => __( 'Full Name', 'ldap-employee-directory' ),
			'email'      => __( 'Email', 'ldap-employee-directory' ),
			'title'      => __( 'Job Title', 'ldap-employee-directory' ),
			'department' => __( 'Department', 'ldap-employee-directory' ),
			'phone'      => __( 'Phone', 'ldap-employee-directory' ),
		);
		foreach ( $items as $key => $label ) {
			printf(
				'<label style="margin-right:12px"><input type="checkbox" name="%1$s[fields][]" value="%2$s" %3$s> %4$s</label>',
				esc_attr( LDAP_ED_OPTION_KEY ),
				esc_attr( $key ),
				checked( in_array( $key, (array) $saved, true ), true, false ),
				esc_html( $label )
			);
		}
	}

	/** @param array $args Settings field args passed by the Settings API. */
	public function render_field_per_page( $args = array() ) {
		printf(
			'<input type="number" id="%1$s" name="%2$s[per_page]" value="%3$d" class="small-text" min="1" max="500">',
			esc_attr( $args['label_for'] ),
			esc_attr( LDAP_ED_OPTION_KEY ),
			absint( $this->get_option( 'per_page', 20 ) )
		);
	}

	public function render_field_enable_search() {
		printf(
			'<label><input type="checkbox" name="%1$s[enable_search]" value="1" %2$s> %3$s</label>',
			esc_attr( LDAP_ED_OPTION_KEY ),
			checked( '1', $this->get_option( 'enable_search', '1' ), false ),
			esc_html__( 'Show search field above the directory', 'ldap-employee-directory' )
		);
	}

	/** @param array $args Settings field args passed by the Settings API. */
	public function render_field_custom_css( $args = array() ) {
		printf(
			'<textarea id="%1$s" name="%2$s[custom_css]" rows="8" class="large-text code">%3$s</textarea><p class="description">%4$s</p>',
			esc_attr( $args['label_for'] ),
			esc_attr( LDAP_ED_OPTION_KEY ),
			esc_textarea( $this->get_option( 'custom_css' ) ),
			esc_html__( 'Custom CSS appended to the directory stylesheet.', 'ldap-employee-directory' )
		);
	}

	/** @param array $args Settings field args passed by the Settings API. */
	public function render_field_cache_ttl( $args = array() ) {
		printf(
			'<input type="number" id="%1$s" name="%2$s[cache_ttl]" value="%3$d" class="small-text" min="1"> %4$s',
			esc_attr( $args['label_for'] ),
			esc_attr( LDAP_ED_OPTION_KEY ),
			absint( $this->get_option( 'cache_ttl', 60 ) ),
			esc_html__( 'minutes', 'ldap-employee-directory' )
		);
	}
}

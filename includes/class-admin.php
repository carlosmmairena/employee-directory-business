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
	}

	/** Add the settings sub-menu under "Settings". */
	public function add_menu() {
		add_options_page(
			__( 'LDAP Employee Directory', 'ldap-employee-directory' ),
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

		$connection_fields = array(
			'server'     => __( 'LDAPS Server', 'ldap-employee-directory' ),
			'port'       => __( 'Port', 'ldap-employee-directory' ),
			'bind_dn'    => __( 'Bind DN', 'ldap-employee-directory' ),
			'bind_pass'  => __( 'Bind Password', 'ldap-employee-directory' ),
			'base_ou'    => __( 'Base OU', 'ldap-employee-directory' ),
			'verify_ssl' => __( 'Verify SSL Certificate', 'ldap-employee-directory' ),
			'ca_cert'    => __( 'CA Certificate Path (.pem)', 'ldap-employee-directory' ),
		);

		foreach ( $connection_fields as $id => $label ) {
			add_settings_field(
				'ldap_ed_' . $id,
				$label,
				array( $this, 'render_field_' . $id ),
				'ldap-employee-directory',
				'ldap_ed_section_connection'
			);
		}

		// --- Display section ---
		add_settings_section(
			'ldap_ed_section_display',
			__( 'Display Options', 'ldap-employee-directory' ),
			'__return_false',
			'ldap-employee-directory'
		);

		$display_fields = array(
			'fields'         => __( 'Fields to Show', 'ldap-employee-directory' ),
			'per_page'       => __( 'Items per Page', 'ldap-employee-directory' ),
			'enable_search'  => __( 'Enable Search Bar', 'ldap-employee-directory' ),
			'custom_css'     => __( 'Custom CSS', 'ldap-employee-directory' ),
		);

		foreach ( $display_fields as $id => $label ) {
			add_settings_field(
				'ldap_ed_' . $id,
				$label,
				array( $this, 'render_field_' . $id ),
				'ldap-employee-directory',
				'ldap_ed_section_display'
			);
		}

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
			'ldap_ed_section_cache'
		);
	}

	/** Sanitize and validate settings before saving. */
	public function sanitize_settings( $input ) {
		$clean = array();

		$clean['server']        = esc_url_raw( trim( $input['server'] ?? '' ) );
		$clean['port']          = absint( $input['port'] ?? 636 );
		$clean['bind_dn']       = sanitize_text_field( $input['bind_dn'] ?? '' );
		$clean['base_ou']       = sanitize_text_field( $input['base_ou'] ?? '' );
		$clean['verify_ssl']    = isset( $input['verify_ssl'] ) ? '1' : '0';
		$clean['ca_cert']       = sanitize_text_field( $input['ca_cert'] ?? '' );
		$clean['per_page']      = absint( $input['per_page'] ?? 20 );
		$clean['enable_search'] = isset( $input['enable_search'] ) ? '1' : '0';
		$clean['custom_css']    = wp_strip_all_tags( $input['custom_css'] ?? '' );
		$clean['cache_ttl']     = absint( $input['cache_ttl'] ?? 60 );

		// Allowed field keys.
		$allowed_fields = array( 'name', 'email', 'title', 'department' );
		$clean['fields'] = array();
		if ( ! empty( $input['fields'] ) && is_array( $input['fields'] ) ) {
			foreach ( $input['fields'] as $field ) {
				if ( in_array( $field, $allowed_fields, true ) ) {
					$clean['fields'][] = $field;
				}
			}
		}

		// Only update password if a new one was supplied.
		$existing = get_option( LDAP_ED_OPTION_KEY, array() );
		$clean['bind_pass'] = ! empty( $input['bind_pass'] )
			? $input['bind_pass']
			: ( $existing['bind_pass'] ?? '' );

		// Invalidate cache when settings change.
		( new LDAP_ED_Cache() )->flush();

		return $clean;
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

		wp_localize_script( 'ldap-ed-admin', 'ldapEdAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ldap_ed_admin_nonce' ),
			'i18n'    => array(
				'testing'    => __( 'Testing…', 'ldap-employee-directory' ),
				'clearing'   => __( 'Clearing…', 'ldap-employee-directory' ),
				'cacheCleared' => __( 'Cache cleared.', 'ldap-employee-directory' ),
			),
		) );
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

	public function render_field_server() {
		$val = esc_attr( $this->get_option( 'server', 'ldaps://' ) );
		echo "<input type='text' name='" . esc_attr( LDAP_ED_OPTION_KEY ) . "[server]' value='{$val}' class='regular-text' placeholder='ldaps://directory.example.com'>";
	}

	public function render_field_port() {
		$val = esc_attr( $this->get_option( 'port', 636 ) );
		echo "<input type='number' name='" . esc_attr( LDAP_ED_OPTION_KEY ) . "[port]' value='{$val}' class='small-text' min='1' max='65535'>";
	}

	public function render_field_bind_dn() {
		$val = esc_attr( $this->get_option( 'bind_dn' ) );
		echo "<input type='text' name='" . esc_attr( LDAP_ED_OPTION_KEY ) . "[bind_dn]' value='{$val}' class='regular-text' placeholder='cn=admin,dc=example,dc=com'>";
	}

	public function render_field_bind_pass() {
		// Never echo the saved password back into the page.
		echo "<input type='password' name='" . esc_attr( LDAP_ED_OPTION_KEY ) . "[bind_pass]' value='' class='regular-text' autocomplete='new-password' placeholder='(leave blank to keep current)'>";
	}

	public function render_field_base_ou() {
		$val = esc_attr( $this->get_option( 'base_ou' ) );
		echo "<input type='text' name='" . esc_attr( LDAP_ED_OPTION_KEY ) . "[base_ou]' value='{$val}' class='regular-text' placeholder='ou=employees,dc=example,dc=com'>";
	}

	public function render_field_verify_ssl() {
		$checked = checked( '1', $this->get_option( 'verify_ssl', '1' ), false );
		echo "<label><input type='checkbox' name='" . esc_attr( LDAP_ED_OPTION_KEY ) . "[verify_ssl]' value='1' {$checked}> " . esc_html__( 'Enable certificate verification (disable for self-signed certs)', 'ldap-employee-directory' ) . '</label>';
	}

	public function render_field_ca_cert() {
		$val = esc_attr( $this->get_option( 'ca_cert' ) );
		echo "<input type='text' name='" . esc_attr( LDAP_ED_OPTION_KEY ) . "[ca_cert]' value='{$val}' class='regular-text' placeholder='/etc/ssl/certs/ca.pem'>";
		echo '<p class="description">' . esc_html__( 'Full server path to the CA certificate file. Used when SSL verification is enabled.', 'ldap-employee-directory' ) . '</p>';
	}

	public function render_field_fields() {
		$saved  = $this->get_option( 'fields', array( 'name', 'email', 'title', 'department' ) );
		$option = LDAP_ED_OPTION_KEY;
		$items  = array(
			'name'       => __( 'Full Name', 'ldap-employee-directory' ),
			'email'      => __( 'Email', 'ldap-employee-directory' ),
			'title'      => __( 'Job Title', 'ldap-employee-directory' ),
			'department' => __( 'Department', 'ldap-employee-directory' ),
		);
		foreach ( $items as $key => $label ) {
			$checked = in_array( $key, (array) $saved, true ) ? 'checked' : '';
			echo "<label style='margin-right:12px'><input type='checkbox' name='" . esc_attr( $option ) . "[fields][]' value='" . esc_attr( $key ) . "' {$checked}> " . esc_html( $label ) . '</label>';
		}
	}

	public function render_field_per_page() {
		$val = absint( $this->get_option( 'per_page', 20 ) );
		echo "<input type='number' name='" . esc_attr( LDAP_ED_OPTION_KEY ) . "[per_page]' value='{$val}' class='small-text' min='1' max='500'>";
	}

	public function render_field_enable_search() {
		$checked = checked( '1', $this->get_option( 'enable_search', '1' ), false );
		echo "<label><input type='checkbox' name='" . esc_attr( LDAP_ED_OPTION_KEY ) . "[enable_search]' value='1' {$checked}> " . esc_html__( 'Show search field above the directory', 'ldap-employee-directory' ) . '</label>';
	}

	public function render_field_custom_css() {
		$val = esc_textarea( $this->get_option( 'custom_css' ) );
		echo "<textarea name='" . esc_attr( LDAP_ED_OPTION_KEY ) . "[custom_css]' rows='8' class='large-text code'>{$val}</textarea>";
		echo '<p class="description">' . esc_html__( 'Custom CSS appended to the directory stylesheet.', 'ldap-employee-directory' ) . '</p>';
	}

	public function render_field_cache_ttl() {
		$val = absint( $this->get_option( 'cache_ttl', 60 ) );
		echo "<input type='number' name='" . esc_attr( LDAP_ED_OPTION_KEY ) . "[cache_ttl]' value='{$val}' class='small-text' min='1'> " . esc_html__( 'minutes', 'ldap-employee-directory' );
	}
}

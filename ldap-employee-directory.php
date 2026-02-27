<?php
/**
 * Plugin Name: Employee Directory Business
 * Plugin URI:  https://wordpress.org/plugins/employee-directory-business/
 * Description: Connects to LDAPS to display an employee directory from an OU. Supports Elementor, Beaver Builder and a native shortcode.
 * Version:     1.0.3
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author:      Carlos Mairena
 * Author URI:  https://carlosmmairena.com
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: employee-directory-business
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'LDAP_ED_VERSION',     '1.0.3' );
define( 'LDAP_ED_FILE',        __FILE__ );
define( 'LDAP_ED_DIR',         plugin_dir_path( __FILE__ ) );
define( 'LDAP_ED_URL',         plugin_dir_url( __FILE__ ) );
define( 'LDAP_ED_OPTION_KEY',  'ldap_ed_settings' );
define( 'LDAP_ED_CACHE_KEY',   'ldap_ed_users' );
define( 'LDAP_ED_STALE_KEY',   'ldap_ed_users_stale' );

/**
 * Flush rewrite rules on deactivation (nothing registered currently, kept for future use).
 */
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Autoload plugin classes.
 *
 * @param string $class Class name.
 */
function ldap_ed_autoload( $class ) {
	$map = array(
		'LDAP_ED_Connector'         => LDAP_ED_DIR . 'includes/class-ldap-connector.php',
		'LDAP_ED_Cache'             => LDAP_ED_DIR . 'includes/class-cache.php',
		'LDAP_ED_Admin'             => LDAP_ED_DIR . 'includes/class-admin.php',
		'LDAP_ED_Ajax'              => LDAP_ED_DIR . 'includes/class-ajax.php',
		'LDAP_ED_Shortcode'         => LDAP_ED_DIR . 'includes/class-shortcode.php',
		'LDAP_ED_Elementor_Widget'  => LDAP_ED_DIR . 'elementor/class-elementor-widget.php',
		'LDAP_ED_BB_Module'         => LDAP_ED_DIR . 'beaver-builder/class-bb-module.php',
	);

	if ( isset( $map[ $class ] ) && file_exists( $map[ $class ] ) ) {
		require_once $map[ $class ];
	}
}
spl_autoload_register( 'ldap_ed_autoload' );

/**
 * Bootstrap the plugin after all plugins are loaded.
 */
function ldap_ed_init() {
	// Core classes.
	new LDAP_ED_Admin();
	new LDAP_ED_Ajax();
	new LDAP_ED_Shortcode();

	// Page builder integrations (only when builders are active).
	if ( did_action( 'elementor/loaded' ) ) {
		add_action( 'elementor/widgets/register', 'ldap_ed_register_elementor_widget' );
	}

	if ( class_exists( 'FLBuilder' ) ) {
		add_action( 'init', 'ldap_ed_register_bb_module', 20 );
	}
}
add_action( 'plugins_loaded', 'ldap_ed_init' );

/**
 * Register the Elementor widget.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
 */
function ldap_ed_register_elementor_widget( $widgets_manager ) {
	require_once LDAP_ED_DIR . 'elementor/class-elementor-widget.php';
	$widgets_manager->register( new LDAP_ED_Elementor_Widget() );
}

/**
 * Register the Beaver Builder module.
 */
function ldap_ed_register_bb_module() {
	require_once LDAP_ED_DIR . 'beaver-builder/class-bb-module.php';
}

<?php

/** * Plugin Name: Employee Directory Business
 * Description: Plugin para mostrar un directorio de empleados desde CSV o Google Workspace. Compatible con Beaver Builder.
 * Version: 1.0.0 
 * Author: Carlos Mairena López
 * Author URI: https://carlosmmairena.com
 * License: GPL2 
 */

if (!defined('ABSPATH')) exit;

define('EDIR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('EDIR_PLUGIN_URL', plugin_dir_url(__FILE__)); 

// Includes 
require_once EDIR_PLUGIN_PATH . 'includes/class-admin.php'; 
require_once EDIR_PLUGIN_PATH . 'includes/class-csv-importer.php'; 
require_once EDIR_PLUGIN_PATH . 'includes/class-shortcode.php'; 

register_activation_hook(__FILE__, function() {
    if (! wp_next_scheduled( 'edir_cron_sync_event' )) {
        wp_schedule_event( time(), 'hourly' /** 'hourly', 'twicedaily', 'daily' */, 'edir_cron_sync_event' );
    }
});

register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook( 'edir_cron_sync_event' );
});

// Hooks 
add_action('admin_menu',            [ 'EDir_Admin', 'add_menu_page' ]);
add_action('admin_init',            [ 'EDir_Admin', 'register_settings' ]);
register_activation_hook( __FILE__, [ 'EDir_CSV_Importer', 'install_db' ]);
add_action('edir_cron_sync_event',  [ 'EDir_CSV_Importer', 'sync_from_csv' ]);

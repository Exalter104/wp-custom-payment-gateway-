<?php
//Plugin ka main file. Isme basic hooks, plugin info, activation/deactivation ka code aayega.
/**
 * Plugin Name: Custom WP Login Customizer
 * Plugin URI:  https://yourwebsite.com
 * Description: A custom WordPress login page customizer with advanced features.
 * Version: 1.0.0
 * Author: Exarth
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * Text Domain: custom-wp-login
 */

if (!defined('ABSPATH')) {
    exit; // Direct access restriction
}

// Plugin Constants
define('WP_SECURE_LOGIN_PATH', plugin_dir_path(__FILE__));
define('WP_SECURE_LOGIN_URL', plugin_dir_url(__FILE__));
define('WP_SECURE_LOGIN_VERSION', '1.0.0');

// Include Core Plugin Files
require_once WP_SECURE_LOGIN_PATH . 'admin/menu.php';
require_once WP_SECURE_LOGIN_PATH . 'includes/settings.php';


// Activation & Deactivation Hooks
function custom_wp_login_activate() {
    // Activation logic
}
register_activation_hook(__FILE__, 'custom_wp_login_activate');

function custom_wp_login_deactivate() {
    // Deactivation logic
}
register_deactivation_hook(__FILE__, 'custom_wp_login_deactivate');

// Initialize the Plugin
function custom_wp_login_init() {
    //  Custom_WP_Login();
}
add_action('plugins_loaded', 'custom_wp_login_init');
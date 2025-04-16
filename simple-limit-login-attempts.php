<?php
/**
 * Plugin Name: Simple Limit Login Attempts
 * Plugin URI: https://exarth.com
 * Plugin Description: A simple plugin to limit login attempts and protect against brute force attacks.
 * Version: 1.0.0
 * Author: Exarth
 * Author URI: https://exarth.com
 * License: MIT
 * License URI: https://choosealicense.com/licenses/mit/
 * Text Domain: simple-limit-login-attempts
 */

// SECURITY CHECK
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// DEFINE CONSTANT
define( 'SLLA_VERSION', '1.0.0' );
define( 'SLLA_PLUGIN_ID', 'simple-limit-login-attempts' );
define( 'SLLA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SLLA_PLUGIN_URL', plugins_url( '', __FILE__ ) );

// INCLUDING FILES
require_once SLLA_PLUGIN_DIR . 'includes/class-slla-core.php';
require_once SLLA_PLUGIN_DIR . 'includes/class-slla-admin.php';
require_once SLLA_PLUGIN_DIR . 'includes/class-slla-lockout.php';
require_once SLLA_PLUGIN_DIR . 'includes/class-slla-logger.php';
require_once SLLA_PLUGIN_DIR . 'includes/database.php';

// ENQUEUE ADMIN STYLES AND SCRIPTS
function slla_enqueue_admin_styles() {
    // Load styles and scripts only on SLLA admin pages
    $screen = get_current_screen();
    if ( strpos( $screen->id, 'slla-' ) !== false ) {
        // Enqueue Google Fonts (Poppins)
        wp_enqueue_style( 'slla-google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap', array(), null );
        
        // Enqueue Plugin Styles with Cache Busting
        $css_version = SLLA_VERSION . '.' . time(); // Add timestamp to bust cache
        wp_enqueue_style( 'slla-admin-dashboard', SLLA_PLUGIN_URL . '/assets/css/admin-dashboard.css', array(), $css_version );
        wp_enqueue_style( 'dashicons' ); // Enqueue Dashicons

        // Enqueue JavaScript with Cache Busting
        $js_version = SLLA_VERSION . '.' . time(); // Add timestamp to bust cache
        wp_enqueue_script( 'slla-admin-js', SLLA_PLUGIN_URL . '/assets/js/admin-settings.js', array( 'jquery' ), $js_version, true );
        // Pass translated strings to JavaScript
        wp_localize_script( 'slla-admin-js', 'sllaSettings', array(
            'defaultErrorMessage' => __( 'Custom error message for failed login attempts.', 'simple-limit-login-attempts' ),
        ));
    }
}
add_action( 'admin_enqueue_scripts', 'slla_enqueue_admin_styles' );

// INITIALIZE THE PLUGIN
function slla_init() {
    $slla_core = new SLLA_Core();
}
add_action( 'plugins_loaded', 'slla_init' );

// DEACTIVATION HOOK
register_deactivation_hook( __FILE__, 'slla_remove_activation_notice_flag' );

function slla_remove_activation_notice_flag() {
    delete_option( 'slla_plugin_activated_notice' );
}

// CREATE CUSTOM DATABASE TABLE ON PLUGIN ACTIVATION
register_activation_hook( __FILE__, 'slla_create_logs_table' );
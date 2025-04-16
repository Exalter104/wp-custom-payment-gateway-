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
if ( file_exists( SLLA_PLUGIN_DIR . 'includes/class-slla-core.php' ) ) {
    require_once SLLA_PLUGIN_DIR . 'includes/class-slla-core.php';
} else {
    error_log( 'Simple Limit Login Attempts: Core file missing.' );
}

if ( file_exists( SLLA_PLUGIN_DIR . 'includes/class-slla-admin.php' ) ) {
    require_once SLLA_PLUGIN_DIR . 'includes/class-slla-admin.php';
} else {
    error_log( 'Simple Limit Login Attempts: Admin file missing.' );
}

if ( file_exists( SLLA_PLUGIN_DIR . 'includes/class-slla-lockout.php' ) ) {
    require_once SLLA_PLUGIN_DIR . 'includes/class-slla-lockout.php';
} else {
    error_log( 'Simple Limit Login Attempts: Lockout file missing.' );
}

if ( file_exists( SLLA_PLUGIN_DIR . 'includes/class-slla-logger.php' ) ) {
    require_once SLLA_PLUGIN_DIR . 'includes/class-slla-logger.php';
} else {
    error_log( 'Simple Limit Login Attempts: Logger file missing.' );
}

if ( file_exists( SLLA_PLUGIN_DIR . 'includes/database.php' ) ) {
    require_once SLLA_PLUGIN_DIR . 'includes/database.php';
} else {
    error_log( 'Simple Limit Login Attempts: Database file missing.' );
}

if ( file_exists( SLLA_PLUGIN_DIR . 'includes/helpers/class-slla-helpers.php' ) ) {
    require_once SLLA_PLUGIN_DIR . 'includes/helpers/class-slla-helpers.php';
} else {
    error_log( 'Simple Limit Login Attempts: Helpers file missing.' );
}

// ENQUEUE ADMIN STYLES AND SCRIPTS
function slla_enqueue_admin_styles() {
    // Load styles and scripts only on SLLA admin pages
    $screen = get_current_screen();
    if ( strpos( $screen->id, 'slla-' ) !== false ) {
        // Enqueue Google Fonts (Poppins)
        wp_enqueue_style( 'slla-google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap', array(), null );
        
        // Enqueue Plugin Styles with Cache Busting
        $css_version = SLLA_VERSION . '.' . time();
        wp_enqueue_style( 'slla-admin-dashboard', SLLA_PLUGIN_URL . '/assets/css/admin-dashboard.css', array(), $css_version );
        wp_enqueue_style( 'dashicons' );

        // Enqueue JavaScript with Cache Busting
        $js_version = SLLA_VERSION . '.' . time();
        wp_enqueue_script( 'slla-admin-js', SLLA_PLUGIN_URL . '/assets/js/admin-settings.js', array( 'jquery' ), $js_version, true );
        // Pass translated strings to JavaScript
        wp_localize_script( 'slla-admin-js', 'sllaSettings', array(
            'defaultErrorMessage' => __( 'Custom error message for failed login attempts.', 'simple-limit-login-attempts' ),
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'slla_admin_nonce' ),
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

// UNINSTALL HOOK (CodeCanyon Standard)
register_uninstall_hook( __FILE__, 'slla_uninstall' );

function slla_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'slla_logs';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    delete_option( 'slla_max_attempts' );
    delete_option( 'slla_lockout_duration' );
    delete_option( 'slla_safelist_ips' );
    delete_option( 'slla_denylist_ips' );
    delete_option( 'slla_custom_error_message' );
    delete_option( 'slla_gdpr_compliance' );
    delete_option( 'slla_enable_auto_updates' );
    delete_option( 'slla_email_notifications' );
    delete_option( 'slla_strong_password' );
    delete_option( 'slla_setup_code' );
    delete_option( 'slla_block_countries' );
    delete_option( 'slla_premium_activated' );
    delete_option( 'slla_plugin_activated_notice' );
}

// CREATE CUSTOM DATABASE TABLE ON PLUGIN ACTIVATION
register_activation_hook( __FILE__, 'slla_create_logs_table' );
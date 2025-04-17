<?php
/**
 * Plugin Name: Simple Limit Login Attempts
 * Plugin URI: https://exarth.com
 * Plugin Description: A simple plugin to limit login attempts and protect against brute force attacks.
 * Version: 1.0.0
 * Author: Exarth
 * Author URI: https://exarth.com
* License: GPL-2.0-or-later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-limit-login-attempts
 */

// SECURITY CHECK
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include Composer autoload file for Twilio SDK
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use Twilio\Rest\Client;

/**
 * Get Twilio Client Instance
 * @return Client|null
 */
function slla_get_twilio_client() {
    $account_sid = get_option( 'slla_twilio_account_sid', '' );
    $auth_token  = get_option( 'slla_twilio_auth_token', '' );

    if ( empty( $account_sid ) || empty( $auth_token ) ) {
        return null; // Credentials nahi hain, null return karo
    }

    try {
        return new Client( $account_sid, $auth_token );
    } catch ( Exception $e ) {
        error_log( 'Twilio Client Initialization Error: ' . $e->getMessage() );
        return null;
    }
}

/**
 * Send SMS Notification via Twilio
 * @param string $message The message to send
 * @return bool Whether the message was sent successfully
 */
function slla_send_sms_notification( $message ) {
    $twilio_client = slla_get_twilio_client();
    if ( ! $twilio_client ) {
        error_log( 'Twilio Client not initialized. Cannot send SMS.' );
        return false;
    }

    $twilio_phone = get_option( 'slla_twilio_phone_number', '' );
    $admin_phone  = get_option( 'slla_admin_phone_number', '' );

    if ( empty( $twilio_phone ) || empty( $admin_phone ) ) {
        error_log( 'Twilio or Admin phone number missing. Cannot send SMS.' );
        return false;
    }

    try {
        $twilio_client->messages->create(
            $admin_phone, // To number
            [
                'from' => $twilio_phone, // From number
                'body' => $message,
            ]
        );
        return true;
    } catch ( Exception $e ) {
        error_log( 'Twilio SMS Sending Error: ' . $e->getMessage() );
        return false;
    }
}

// Example: Failed login attempt pe SMS bhejo
function slla_on_failed_login_attempt( $username ) {
    if ( get_option( 'slla_enable_sms_notifications', 0 ) != 1 ) {
        return; // SMS notifications disabled hain
    }

    $message = sprintf(
        __( 'Failed login attempt by %s on %s', 'simple-limit-login-attempts' ),
        $username,
        home_url()
    );

    if ( ! slla_send_sms_notification( $message ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . __( 'Failed to send SMS notification. Check Twilio settings.', 'simple-limit-login-attempts' ) . '</p></div>';
        });
    }
}

// Hook into WordPress login attempt
add_action( 'wp_login_failed', function( $username ) {
    slla_on_failed_login_attempt( $username );
});

// DEFINE CONSTANT
define( 'SLLA_VERSION', '1.0.0' );
define( 'SLLA_PLUGIN_ID', 'simple-limit-login-attempts' );
define( 'SLLA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SLLA_PLUGIN_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

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

if ( file_exists( SLLA_PLUGIN_DIR . 'includes/class-slla-core.php' ) ) {
    require_once SLLA_PLUGIN_DIR . 'includes/class-slla-core.php';
} else {
    error_log( 'Simple Limit Login Attempts: Core file missing.' );
    add_action( 'admin_notices', function() {
        echo '<div class="notice notice-error"><p>' . __( 'Simple Limit Login Attempts: Core file is missing. Plugin functionality may be limited.', 'simple-limit-login-attempts' ) . '</p></div>';
    });
}

// ENQUEUE ADMIN STYLES AND SCRIPTS
$css_version = SLLA_VERSION;
$js_version = SLLA_VERSION;
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
add_action( 'wp_login_failed', function( $username ) {
    $lockout = new SLLA_Lockout( new SLLA_Core() );
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Increment failed attempts
    $transient_key = 'slla_attempts_' . md5( $ip );
    $attempts = get_transient( $transient_key );
    if ( $attempts === false ) {
        $attempts = 0;
    }
    set_transient( $transient_key, $attempts + 1, DAY_IN_SECONDS );

    // Check and set lockout
    $lockout->check_and_set_lockout( $ip );

    // Send SMS for failed attempt
    slla_on_failed_login_attempt( $username );
});
// INITIALIZE THE PLUGIN
function slla_init() {
    global $slla_core;
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
    delete_option( 'slla_twilio_account_sid' );
    delete_option( 'slla_twilio_auth_token' );
    delete_option( 'slla_twilio_phone_number' );
    delete_option( 'slla_admin_phone_number' );
    delete_option( 'slla_enable_sms_notifications' );
}

// CREATE CUSTOM DATABASE TABLE ON PLUGIN ACTIVATION
register_activation_hook( __FILE__, 'slla_create_logs_table' );
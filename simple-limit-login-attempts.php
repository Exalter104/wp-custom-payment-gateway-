<?php
/**
 * Plugin Name: Simple Limit Login Attempts
 * Plugin URI: https://exarth.com
 * Description: Block excessive login attempts and protect your site against brute force attacks. Simple, yet powerful tools to improve site performance.
 * Version: 1.0.1
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

// DEFINE CONSTANT
define( 'SLLA_VERSION', '1.0.0' );
define( 'SLLA_PLUGIN_ID', 'simple-limit-login-attempts' );
define( 'SLLA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SLLA_PLUGIN_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

// INCLUDING FILES
require_once SLLA_PLUGIN_DIR . 'includes/class-slla-twilio.php';

$required_files = [
    'includes/class-slla-core.php' => 'Core',
    'includes/class-slla-admin.php' => 'Admin',
    'includes/class-slla-lockout.php' => 'Lockout',
    'includes/class-slla-logger.php' => 'Logger',
    'includes/database.php' => 'Database',
    'includes/helpers/class-slla-helpers.php' => 'Helpers',
    'includes/class-slla-2fa.php' => '2FA',
    'includes/class-slla-geoblock.php' => 'GeoBlock',
    'includes/class-slla-suspicious-behavior.php' => 'Suspicious Behavior', // Added Suspicious Behavior class
];

foreach ( $required_files as $file => $name ) {
    if ( file_exists( SLLA_PLUGIN_DIR . $file ) ) {
        require_once SLLA_PLUGIN_DIR . $file;
        if ( $file === 'includes/class-slla-2fa.php' ) {
            new SLLA_2FA();
        } elseif ( $file === 'includes/class-slla-geoblock.php' ) {
            new SLLA_GeoBlock(); // Initialize GeoBlock class
        } elseif ( $file === 'includes/class-slla-suspicious-behavior.php' ) {
            new SLLA_Suspicious_Behavior(); // Initialize Suspicious Behavior class
        }
    } else {
        error_log( "Simple Limit Login Attempts: {$name} file missing." );
        if ( $file === 'includes/class-slla-core.php' ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __( 'Simple Limit Login Attempts: Core file is missing. Plugin functionality may be limited.', 'simple-limit-login-attempts' ) . '</p></div>';
            });
        }
    }
}

// ENQUEUE ADMIN STYLES AND SCRIPTS
$css_version = SLLA_VERSION;
$js_version = SLLA_VERSION;

function slla_enqueue_admin_styles() {
    $screen = get_current_screen();
    if ( strpos( $screen->id, 'slla-' ) !== false ) {
        wp_enqueue_style( 'slla-google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap', array(), null );
        $css_version = SLLA_VERSION . '.' . time();
        wp_enqueue_style( 'slla-admin-dashboard', SLLA_PLUGIN_URL . '/assets/css/admin-dashboard.css', array(), $css_version );
        wp_enqueue_style( 'dashicons' );
        $js_version = SLLA_VERSION . '.' . time();
        wp_enqueue_script( 'slla-admin-js', SLLA_PLUGIN_URL . '/assets/js/admin-settings.js', array( 'jquery' ), $js_version, true );
        // Enqueue Chart.js for the dashboard chart
        wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.3', true );
        wp_localize_script( 'slla-admin-js', 'sllaSettings', array(
            'defaultErrorMessage' => __( 'Custom error message for failed login attempts.', 'simple-limit-login-attempts' ),
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'slla_admin_nonce' ),
        ));
    }
}
add_action( 'admin_enqueue_scripts', 'slla_enqueue_admin_styles' );

// Hook for failed login attempts
add_action('wp_login_failed', function($username) {
    $logger = new SLLA_Logger();
    $lockout = new SLLA_Lockout(new SLLA_Core());
    $ip = $_SERVER['REMOTE_ADDR'];
    // Log the failed attempt
    $logger->log_failed_attempt($username, $ip);
    // Increment failed attempts count
    $transient_key = 'slla_attempts_' . md5($ip);
    $attempts = get_transient($transient_key);
    if ($attempts === false) {
        $attempts = 0;
    }
    set_transient($transient_key, $attempts + 1, DAY_IN_SECONDS);
    // Check and set lockout
    $lockout->check_and_set_lockout($ip);
    // Trigger Twilio SMS notification
    SLLA_Twilio::on_failed_login_attempt($username);
});

// Hook for successful login
add_action('wp_login', function($user_login, $user) {
    $logger = new SLLA_Logger();
    $ip = $_SERVER['REMOTE_ADDR'];
    $logger->log_successful_login($user_login, $ip);
}, 10, 2);

// INITIALIZE THE PLUGIN
function slla_init() {
    global $slla_core;
    $slla_core = new SLLA_Core();
}
add_action('plugins_loaded', 'slla_init');

// DEACTIVATION HOOK
register_deactivation_hook(__FILE__, 'slla_remove_activation_notice_flag');
function slla_remove_activation_notice_flag() {
    delete_option('slla_plugin_activated_notice');
}

// UNINSTALL HOOK
register_uninstall_hook(__FILE__, 'slla_uninstall');
function slla_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'slla_logs';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $options = [
        'slla_max_attempts',
        'slla_lockout_duration',
        'slla_safelist_ips',
        'slla_denylist_ips',
        'slla_custom_error_message',
        'slla_gdpr_compliance',
        'slla_enable_auto_updates',
        'slla_email_notifications',
        'slla_strong_password',
        'slla_setup_code',
        'slla_block_countries',
        'slla_premium_activated',
        'slla_plugin_activated_notice',
        'slla_twilio_account_sid',
        'slla_twilio_auth_token',
        'slla_twilio_phone_number',
        'slla_admin_phone_number',
        'slla_enable_sms_notifications',
        'slla_ipstack_api_key', // Added for Geo-Blocking
        'slla_allowed_countries', // Added for Geo-Blocking
        'slla_blocked_attempts', // Added for Geo-Blocking logs
        'slla_failed_attempts', // Added for Suspicious Behavior logs
    ];
    foreach ($options as $option) {
        delete_option($option);
    }
}

// CREATE CUSTOM DATABASE TABLE ON PLUGIN ACTIVATION
register_activation_hook(__FILE__, 'slla_create_logs_table');

// Remove WordPress version and footer text
function remove_admin_footer_text() {
    return '';
}
add_filter('admin_footer_text', 'remove_admin_footer_text');

function remove_wp_version_footer() {
    return '';
}
add_filter('update_footer', 'remove_wp_version_footer', 9999);
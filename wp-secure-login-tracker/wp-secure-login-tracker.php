<?php

/**
 * Plugin Name: WP Secure Login Tracker
 * Plugin URI:  https://exarth.com
 * Description: A simple plugin to track user logins and logouts in WordPress.
 * Version: 1.0.0
 * Author: Exarth
 * Author URI: https://exarth.com
 * License: GPL v2 or later
 * Text Domain: wp-login-secure-track
 */

// Prevent Direct Access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Constants
define( 'WSLT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WSLT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once WSLT_PLUGIN_DIR . 'includes/database.php';
require_once WSLT_PLUGIN_DIR . 'includes/login_tracker.php';
require_once WSLT_PLUGIN_DIR . 'includes/database.php';
require_once WSLT_PLUGIN_DIR . 'includes/login_tracker.php';
// require_once WSLT_PLUGIN_DIR . 'includes/activity-logs.php';
require_once WSLT_PLUGIN_DIR . 'includes/settings.php';
require_once WSLT_PLUGIN_DIR . 'admin/menu.php';
require_once WSLT_PLUGIN_DIR . 'admin/logs-page.php';



// Activation Hook
register_activation_hook( __FILE__, 'wslt_activate_plugin' );
function wslt_activate_plugin() {
    wslt_create_table();
}

// Deactivation Hook
register_deactivation_hook( __FILE__, 'wslt_deactivate_plugin' );
function wslt_deactivate_plugin() {
    // Optional clean-up
}



// Export Logs Handler
add_action('admin_post_wslt_export_logs', 'wslt_export_logs_function');

function wslt_export_logs_function() {
    if ( ! current_user_can('manage_options') ) {
        wp_die('Unauthorized user');
    }

    if ( ! isset($_POST['wslt_export_logs_nonce_field']) || ! wp_verify_nonce($_POST['wslt_export_logs_nonce_field'], 'wslt_export_logs_nonce') ) {
        wp_die('Invalid nonce');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'wslt_login_logs';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="login_logs.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('User ID', 'Username', 'Role', 'Login Time', 'IP Address', 'User Agent'));

    $logs = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    foreach ($logs as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

function wslt_enqueue_admin_styles($hook) {
    if (strpos($hook, 'wslt-logs') !== false) {
        wp_enqueue_style(
            'wslt-admin-style',
            plugin_dir_url(__FILE__) . 'assets/css/wslt-admin-style.css',
            array(),
            '1.0.0'
        );
    }
}
add_action('admin_enqueue_scripts', 'wslt_enqueue_admin_styles');

?>
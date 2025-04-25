<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Admin_Stats
 * Handles statistics-related functionality for the Simple Limit Login Attempts plugin.
 */
class SLLA_Admin_Stats {
    public function get_total_successful_logins() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE type = %s", 'successful_login' ) );
        return (int) $count ?: 0; // Cast to int and return 0 if null
    }

    public function get_total_lockouts() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE type = %s", 'lockout' ) );
        return (int) $count ?: 0; // Cast to int and return 0 if null
    }

    public function get_total_failed_attempts() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $since = current_time( 'mysql', 1 );
        $since = date( 'Y-m-d H:i:s', strtotime( $since . ' -24 hours' ) );
        $count = $wpdb->get_var( $wpdb->prepare( 
            "SELECT COUNT(*) FROM $table_name WHERE type = %s AND time >= %s", 
            'failed_attempt', 
            $since 
        ) );
        return (int) $count ?: 0; // Cast to int and return 0 if null
    }
}
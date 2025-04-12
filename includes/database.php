<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Creates the custom database table for storing logs on plugin activation.
 */
function slla_create_logs_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'slla_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        type VARCHAR(20) NOT NULL,
        ip VARCHAR(100) NOT NULL,
        username VARCHAR(255) DEFAULT '',
        time DATETIME NOT NULL,
        reason TEXT DEFAULT '',
        PRIMARY KEY (id),
        INDEX idx_type (type),
        INDEX idx_ip (ip)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Migrate existing successful logins
    $successful_logs = get_option( 'slla_successful_logins', array() );
    if ( ! empty( $successful_logs ) ) {
        foreach ( $successful_logs as $log ) {
            $wpdb->insert(
                $table_name,
                array(
                    'type' => 'successful_login',
                    'ip' => sanitize_text_field( $log['ip'] ),
                    'username' => sanitize_text_field( $log['username'] ),
                    'time' => $log['time'],
                ),
                array( '%s', '%s', '%s', '%s' )
            );
        }
        delete_option( 'slla_successful_logins' );
    }

    // Migrate existing lockout logs
    $lockout_logs = get_option( 'slla_lockout_logs', array() );
    if ( ! empty( $lockout_logs ) ) {
        foreach ( $lockout_logs as $log ) {
            $wpdb->insert(
                $table_name,
                array(
                    'type' => 'lockout',
                    'ip' => sanitize_text_field( $log['ip'] ),
                    'time' => $log['time'],
                    'reason' => sanitize_text_field( $log['reason'] ),
                ),
                array( '%s', '%s', '%s', '%s' )
            );
        }
        delete_option( 'slla_lockout_logs' );
    }
}
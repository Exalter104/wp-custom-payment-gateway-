<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SLLA_Logger {
    public function log_successful_login( $username, $ip ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';

        $wpdb->insert(
            $table_name,
            array(
                'type' => 'successful_login',
                'ip' => sanitize_text_field( $ip ),
                'username' => sanitize_text_field( $username ),
                'time' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s' )
        );
    }

    public function log_lockout_event( $ip, $reason ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';

        $wpdb->insert(
            $table_name,
            array(
                'type' => 'lockout',
                'ip' => sanitize_text_field( $ip ),
                'time' => current_time( 'mysql' ),
                'reason' => sanitize_text_field( $reason ),
            ),
            array( '%s', '%s', '%s', '%s' )
        );
    }
}
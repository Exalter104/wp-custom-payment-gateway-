<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SLLA_Logger {
    private $core;

    public function __construct( $core ) {
        $this->core = $core;
    }

    public function log_successful_login( $user_login, $user ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';

        $ip = $this->core->get_ip_address();
        $login_time = current_time( 'mysql' );

        // Insert into database
        $wpdb->insert(
            $table_name,
            array(
                'type' => 'successful_login',
                'ip' => sanitize_text_field( $ip ),
                'username' => sanitize_text_field( $user_login ),
                'time' => $login_time,
            ),
            array( '%s', '%s', '%s', '%s' )
        );

        // Keep only the latest 50 entries
        $this->trim_logs( 'successful_login', 50 );
    }

    public function log_lockout_event( $ip, $reason = 'Too many failed attempts' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';

        $lockout_time = current_time( 'mysql' );

        // Insert into database
        $wpdb->insert(
            $table_name,
            array(
                'type' => 'lockout',
                'ip' => sanitize_text_field( $ip ),
                'time' => $lockout_time,
                'reason' => sanitize_text_field( $reason ),
            ),
            array( '%s', '%s', '%s', '%s' )
        );

        // Keep only the latest 50 entries
        $this->trim_logs( 'lockout', 50 );
    }

    private function trim_logs( $type, $limit = 50 ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';

        // Get total count of logs for this type
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE type = %s",
            $type
        ) );

        // If count exceeds limit, delete oldest entries
        if ( $count > $limit ) {
            $delete_count = $count - $limit;
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM $table_name WHERE type = %s ORDER BY time ASC LIMIT %d",
                $type,
                $delete_count
            ) );
        }
    }
}
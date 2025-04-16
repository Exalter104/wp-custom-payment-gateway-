<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SLLA_Lockout {
    private $core;
    private $logger;

    public function __construct( $core ) {
        $this->core = $core;
        $this->logger = new SLLA_Logger();
    }

    public function check_and_set_lockout( $ip ) {
        $transient_key = 'slla_attempts_' . md5( $ip );
        $lockout_key = 'slla_lockout_' . md5( $ip );
        $max_attempts = get_option( 'slla_max_attempts', 5 ); // Use setting
        $lockout_duration = get_option( 'slla_lockout_duration', 15 ) * MINUTE_IN_SECONDS; // Use setting

        // Debugging: Log the IP and transient keys
        error_log( "Checking lockout for IP: $ip" );
        error_log( "Attempts transient key: $transient_key" );
        error_log( "Lockout transient key: $lockout_key" );

        $attempts = get_transient( $transient_key );

        // Debugging: Log the current attempts
        error_log( "Current attempts for IP $ip: " . ( $attempts !== false ? $attempts : 0 ) );

        if ( $attempts && $attempts >= $max_attempts ) {
            // Set the lockout transient with start time, duration, and actual IP
            $lockout_data = array(
                'start_time' => time(),
                'duration' => $lockout_duration,
                'ip' => $ip, // Store the actual IP address
            );
            $result = set_transient( $lockout_key, $lockout_data, $lockout_duration );

            // Debugging: Log if transient was set successfully
            if ( $result ) {
                error_log( "Lockout transient set for IP $ip: Success" );
            } else {
                error_log( "Lockout transient set for IP $ip: Failed" );
                global $wpdb;
                error_log( "Database error (if any): " . $wpdb->last_error );
            }
            error_log( "Lockout data: " . print_r( $lockout_data, true ) );

            $this->logger->log_lockout_event( $ip, 'Too many failed attempts' );
            delete_transient( $transient_key );

            // Send email notification to admin
            $this->send_lockout_notification( $ip );

            return true;
        }

        return false;
    }

    public function check_lockout( $user, $username, $password ) {
        $ip = $this->core->get_ip_address();
        $lockout_key = 'slla_lockout_' . md5( $ip );

        // Debugging: Log the IP being checked for lockout
        error_log( "Checking lockout status for IP: $ip" );

        $lockout_data = get_transient( $lockout_key );
        if ( $lockout_data ) {
            $lockout_duration = get_option( 'slla_lockout_duration', 15 ); // Use setting
            $error = new WP_Error( 'locked_out', sprintf(
                __( 'Too many failed attempts. You are locked out for %d minutes.', 'simple-limit-login-attempts' ),
                $lockout_duration
            ) );
            return $error;
        }

        return $user;
    }

    public function send_lockout_notification( $ip ) {
        $to = get_option( 'admin_email' );
        $subject = __( 'Lockout Notification - Simple Limit Login Attempts', 'simple-limit-login-attempts' );
        $message = sprintf(
            __( "A user with IP %s has been locked out due to too many failed login attempts.\n\nTime: %s\n", 'simple-limit-login-attempts' ),
            $ip,
            current_time( 'mysql' )
        );
        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

        // Debugging: Log email details to check if this method is called
        error_log( "Sending lockout email to: $to" );
        error_log( "Subject: $subject" );
        error_log( "Message: $message" );

        $result = wp_mail( $to, $subject, $message, $headers );

        // Debugging: Log the result of wp_mail
        error_log( "wp_mail result: " . ( $result ? 'Success' : 'Failed' ) );
    }
}
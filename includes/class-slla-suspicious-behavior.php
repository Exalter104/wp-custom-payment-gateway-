<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Suspicious_Behavior
 * Handles suspicious behavior detection for the Simple Limit Login Attempts plugin.
 */
class SLLA_Suspicious_Behavior {
    public function __construct() {
        // Hook into failed login attempts
        add_action( 'wp_login_failed', array( $this, 'log_failed_attempt' ) );
    }

    /**
     * Log the failed login attempt and check for suspicious behavior.
     *
     * @param string $username The username attempting to log in.
     */
    public function log_failed_attempt( $username ) {
        $ip = $this->get_client_ip();
        $timestamp = current_time( 'mysql' );

        // Log the failed attempt
        $failed_attempts = get_option( 'slla_failed_attempts', array() );
        $failed_attempts[] = array(
            'username' => $username,
            'ip'       => $ip,
            'time'     => $timestamp,
        );
        update_option( 'slla_failed_attempts', $failed_attempts );

        // Check for suspicious behavior
        $this->check_suspicious_behavior();
    }

    /**
     * Get the client's IP address.
     *
     * @return string The client's IP address.
     */
    private function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $ip;
    }

    /**
     * Check for suspicious behavior based on failed login attempts.
     */
    private function check_suspicious_behavior() {
        $failed_attempts = get_option( 'slla_failed_attempts', array() );
        if ( empty( $failed_attempts ) ) {
            return;
        }

        // Time window for analysis (5 minutes = 300 seconds)
        $time_window = 300;
        $current_time = strtotime( current_time( 'mysql' ) );
        $recent_attempts = array();

        // Filter attempts within the last 5 minutes
        foreach ( $failed_attempts as $attempt ) {
            $attempt_time = strtotime( $attempt['time'] );
            if ( ( $current_time - $attempt_time ) <= $time_window ) {
                $recent_attempts[] = $attempt;
            }
        }

        // Rule 1: Check if one IP used multiple usernames
        $ip_usernames = array();
        foreach ( $recent_attempts as $attempt ) {
            $ip = $attempt['ip'];
            $username = $attempt['username'];
            if ( ! isset( $ip_usernames[$ip] ) ) {
                $ip_usernames[$ip] = array();
            }
            if ( ! in_array( $username, $ip_usernames[$ip] ) ) {
                $ip_usernames[$ip][] = $username;
            }
        }

        foreach ( $ip_usernames as $ip => $usernames ) {
            if ( count( $usernames ) >= 3 ) {
                $this->notify_admin( $ip, 'Multiple usernames from IP', 'IP ' . $ip . ' used ' . count( $usernames ) . ' different usernames: ' . implode( ', ', $usernames ) );
            }
        }

        // Rule 2: Check if one username used multiple IPs
        $username_ips = array();
        foreach ( $recent_attempts as $attempt ) {
            $username = $attempt['username'];
            $ip = $attempt['ip'];
            if ( ! isset( $username_ips[$username] ) ) {
                $username_ips[$username] = array();
            }
            if ( ! in_array( $ip, $username_ips[$username] ) ) {
                $username_ips[$username][] = $ip;
            }
        }

        foreach ( $username_ips as $username => $ips ) {
            if ( count( $ips ) >= 3 ) {
                $this->notify_admin( $username, 'Multiple IPs for username', 'Username ' . $username . ' used ' . count( $ips ) . ' different IPs: ' . implode( ', ', $ips ) );
            }
        }
    }

    /**
     * Notify the admin about suspicious behavior.
     *
     * @param string $identifier The IP or username involved.
     * @param string $reason The reason for the notification.
     * @param string $details Additional details about the suspicious behavior.
     */
    private function notify_admin( $identifier, $reason, $details ) {
        $admin_email = get_option( 'admin_email' );
        $subject = __( 'Suspicious Behavior Detected on ', 'simple-limit-login-attempts' ) . get_bloginfo( 'name' );
        $message = __( 'Suspicious behavior detected on your site.', 'simple-limit-login-attempts' ) . "\n\n";
        $message .= __( 'Reason: ', 'simple-limit-login-attempts' ) . $reason . "\n";
        $message .= __( 'Details: ', 'simple-limit-login-attempts' ) . $details . "\n";
        $message .= __( 'Time: ', 'simple-limit-login-attempts' ) . current_time( 'mysql' ) . "\n";
        $email_sent = wp_mail( $admin_email, $subject, $message );

        if ( $email_sent ) {
            error_log( 'Suspicious Behavior: Admin notification sent for ' . $identifier );
        } else {
            error_log( 'Suspicious Behavior: Failed to send admin notification for ' . $identifier );
        }
    }
}
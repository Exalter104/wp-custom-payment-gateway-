<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Logger
 * Handles logging functionality for the Simple Limit Login Attempts plugin.
 */
class SLLA_Logger {
    /**
     * Get the client IP address.
     *
     * @return string The IP address or '-' if invalid/not found.
     */
    private function get_client_ip() {
        $ip = '';

        // Check for Cloudflare IP
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        // Check for other proxies
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip_list[0]);
        }
        // Fallback to REMOTE_ADDR
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Validate IP
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return '-'; // Fallback if IP is invalid
    }

    /**
     * Log an event to the slla_logs table.
     *
     * @param string $event_type The type of event (e.g., 'failed_attempt', 'successful_login', 'lockout').
     * @param string $username The username associated with the event.
     * @param string $details Additional details about the event.
     * @return bool True if the event was logged successfully, false otherwise.
     */
    public function log_event($event_type, $username = '', $details = '') {
        global $wpdb;

        $ip = $this->get_client_ip();
        $table_name = $wpdb->prefix . 'slla_logs';
        $gdpr_compliance = get_option('slla_gdpr_compliance', 0);

        if ($gdpr_compliance) {
            $ip = '-'; // Do not store IP if GDPR compliance is enabled
        }

        // Debug: Log the data being inserted
        $current_time = current_time('mysql');
        error_log('Logging Event - Type: ' . $event_type . ', Username: ' . $username . ', IP: ' . $ip . ', Details: ' . $details . ', Time: ' . $current_time);

        $result = $wpdb->insert(
            $table_name,
            array(
                'type'       => sanitize_text_field($event_type),
                'ip'         => $ip,
                'username'   => sanitize_text_field($username),
                'reason'     => sanitize_text_field($details),
                'time'       => $current_time,
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );

        // Debug: Log the result of the insert
        if ($result === false) {
            error_log('Failed to log event: ' . $wpdb->last_error);
            return false;
        } else {
            error_log('Event logged successfully: ' . $event_type);
            return true;
        }
    }

    /**
     * Log a successful login event.
     *
     * @param string $username The username of the user who logged in.
     * @param string $ip The IP address of the user.
     */
    public function log_successful_login($username, $ip) {
        $this->log_event('successful_login', $username, '');
    }

    /**
     * Log a lockout event.
     *
     * @param string $ip The IP address that was locked out.
     * @param string $reason The reason for the lockout.
     */
    public function log_lockout_event($ip, $reason) {
        $this->log_event('lockout', '', $reason);
    }

    /**
     * Log a failed login attempt.
     *
     * @param string $username The username that attempted to log in.
     * @param string $ip The IP address of the user.
     */
    public function log_failed_attempt($username, $ip) {
        $this->log_event('failed_attempt', $username, '');
    }
}
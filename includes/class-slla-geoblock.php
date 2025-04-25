<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_GeoBlock
 * Handles Geo-Blocking functionality for the Simple Limit Login Attempts plugin.
 */
class SLLA_GeoBlock {
    private $api_key;

    public function __construct() {
        $this->api_key = get_option( 'slla_ipstack_api_key', '' );
        add_action( 'wp_login_failed', array( $this, 'check_geoblock' ) );
    }

    /**
     * Check if the login attempt should be blocked based on the user's country.
     *
     * @param string $username The username attempting to log in.
     */
    public function check_geoblock( $username ) {
        $ip = $this->get_client_ip();
        $country = $this->get_country_from_ip( $ip );

        // Allowed countries (set in settings)
        $allowed_countries = get_option( 'slla_allowed_countries', array( 'PK' ) ); // Default: Pakistan
        if ( ! in_array( $country, $allowed_countries ) ) {
            // Block the login attempt
            $this->log_blocked_attempt( $username, $ip, $country );
            wp_die( __( 'Login attempt blocked: Your location could not be determined or is not allowed. Please contact the site administrator.', 'simple-limit-login-attempts' ) );
        }
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
     * Get the country code from the IP address using ipstack API.
     *
     * @param string $ip The IP address to check.
     * @return string The country code or 'Unknown' if not found.
     */
    private function get_country_from_ip( $ip ) {
        if ( empty( $this->api_key ) ) {
            error_log( 'GeoBlock Error: ipstack API key is missing.' );
            return 'Unknown';
        }

        $url = "http://api.ipstack.com/{$ip}?access_key={$this->api_key}";
        $response = wp_remote_get( $url, array( 'timeout' => 5 ) ); // Add timeout to avoid hanging

        if ( is_wp_error( $response ) ) {
            error_log( 'GeoBlock Error: Failed to connect to ipstack API for IP ' . $ip . '. Error: ' . $response->get_error_message() );
            return 'Unknown';
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        // Log the raw response for debugging
        error_log( 'GeoBlock: ipstack API raw response for IP ' . $ip . ': ' . $body );

        if ( $response_code !== 200 ) {
            error_log( 'GeoBlock Error: ipstack API returned HTTP code ' . $response_code . ' for IP ' . $ip );
            return 'Unknown';
        }

        $data = json_decode( $body, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( 'GeoBlock Error: Failed to decode ipstack API response for IP ' . $ip . '. JSON Error: ' . json_last_error_msg() );
            return 'Unknown';
        }

        if ( isset( $data['error'] ) ) {
            error_log( 'GeoBlock Error: ipstack API error for IP ' . $ip . ': ' . print_r( $data['error'], true ) );
            return 'Unknown';
        }

        if ( ! isset( $data['country_code'] ) ) {
            error_log( 'GeoBlock Error: Unable to retrieve country code for IP ' . $ip . '. Response: ' . print_r( $data, true ) );
            return 'Unknown';
        }

        error_log( 'GeoBlock: IP ' . $ip . ' resolved to country ' . $data['country_code'] );
        return $data['country_code'];
    }

    /**
     * Log the blocked attempt and notify the admin via email.
     *
     * @param string $username The username attempting to log in.
     * @param string $ip The IP address of the attempt.
     * @param string $country The country of the IP address.
     */
    private function log_blocked_attempt( $username, $ip, $country ) {
        // Log the blocked attempt
        $log = get_option( 'slla_blocked_attempts', array() );
        $log[] = array(
            'username' => $username,
            'ip'       => $ip,
            'country'  => $country,
            'time'     => current_time( 'mysql' ),
        );
        update_option( 'slla_blocked_attempts', $log );

        // Send email notification to admin
        $admin_email = get_option( 'admin_email' );
        $subject = __( 'Blocked Login Attempt on ', 'simple-limit-login-attempts' ) . get_bloginfo( 'name' );
        $message = __( 'A login attempt was blocked due to Geo-Blocking.', 'simple-limit-login-attempts' ) . "\n\n";
        $message .= __( 'Username: ', 'simple-limit-login-attempts' ) . $username . "\n";
        $message .= __( 'IP: ', 'simple-limit-login-attempts' ) . $ip . "\n";
        $message .= __( 'Country: ', 'simple-limit-login-attempts' ) . ( $country ?: 'Unknown (Failed to detect country)' ) . "\n";
        $message .= __( 'Time: ', 'simple-limit-login-attempts' ) . current_time( 'mysql' ) . "\n";
        $email_sent = wp_mail( $admin_email, $subject, $message );

        if ( $email_sent ) {
            error_log( 'GeoBlock: Admin notification sent for blocked attempt from IP ' . $ip );
        } else {
            error_log( 'GeoBlock: Failed to send admin notification for blocked attempt from IP ' . $ip );
        }
    }
}
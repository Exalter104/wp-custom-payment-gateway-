<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Twilio
 * Handles Twilio-related functionality for the Simple Limit Login Attempts plugin.
 */
class SLLA_Twilio {
    /**
     * Get Twilio client instance.
     *
     * @return \Twilio\Rest\Client|null
     */
    public static function get_twilio_client() {
        $account_sid = get_option( 'slla_twilio_account_sid', '' );
        $auth_token  = get_option( 'slla_twilio_auth_token', '' );

        if ( empty( $account_sid ) || empty( $auth_token ) ) {
            error_log( 'Twilio Client not initialized: Missing account SID or auth token.' );
            return null;
        }

        try {
            return new \Twilio\Rest\Client( $account_sid, $auth_token );
        } catch ( Exception $e ) {
            error_log( 'Twilio Client Initialization Error: ' . $e->getMessage() );
            return null;
        }
    }

    /**
     * Validate Twilio credentials.
     *
     * @param string $account_sid Twilio Account SID.
     * @param string $auth_token Twilio Auth Token.
     * @return bool True if credentials are valid, false otherwise.
     */
    public static function validate_credentials($account_sid, $auth_token) {
        try {
            // Initialize Twilio client with provided credentials
            $client = new \Twilio\Rest\Client($account_sid, $auth_token);
            
            // Make a test API call to validate credentials (e.g., fetch account details)
            $client->accounts($account_sid)->fetch();
            
            return true; // Credentials are valid
        } catch (\Twilio\Exceptions\RestException $e) {
            error_log('Twilio credential validation failed: ' . $e->getMessage());
            return false; // Credentials are invalid
        }
    }

    /**
     * Send SMS notification using Twilio.
     *
     * @param string $message Message to send.
     * @return bool True if SMS sent successfully, false otherwise.
     */
    public static function send_sms_notification( $message ) {
        $twilio_client = self::get_twilio_client();
        if ( ! $twilio_client ) {
            error_log( 'Twilio Client not initialized. Cannot send SMS.' );
            return false;
        }

        $twilio_phone = get_option( 'slla_twilio_phone_number', '' );
        $admin_phone  = get_option( 'slla_admin_phone_number', '' );

        if ( empty( $twilio_phone ) || empty( $admin_phone ) ) {
            error_log( 'Twilio or Admin phone number missing. Cannot send SMS.' );
            return false;
        }

        try {
            $twilio_client->messages->create(
                $admin_phone,
                [
                    'from' => $twilio_phone,
                    'body' => $message,
                ]
            );
            error_log( 'Twilio SMS sent successfully to: ' . $admin_phone );
            return true;
        } catch ( Exception $e ) {
            error_log( 'Twilio SMS Sending Error: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Handle SMS notification for failed login attempts.
     *
     * @param string $username Username of the user who failed to log in.
     */
    public static function on_failed_login_attempt( $username ) {
        if ( get_option( 'slla_enable_sms_notifications', 0 ) != 1 ) {
            return;
        }

        $message = sprintf(
            __( 'Failed login attempt by %s on %s', 'simple-limit-login-attempts' ),
            $username,
            home_url()
        );

        if ( ! self::send_sms_notification( $message ) ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __( 'Failed to send SMS notification. Check Twilio settings.', 'simple-limit-login-attempts' ) . '</p></div>';
            });
        }
    }

    /**
     * Get Twilio SMS usage for the current day.
     *
     * @return array Usage data or error message.
     */
    public static function get_twilio_usage() {
        $twilio_client = self::get_twilio_client();
        if ( ! $twilio_client ) {
            return [ 'error' => __( 'Twilio configuration incomplete.', 'simple-limit-login-attempts' ) ];
        }

        try {
            $usage = $twilio_client->usage->records->daily->read(
                array(
                    'category' => 'sms',
                    'startDate' => date('Y-m-d'),
                    'endDate' => date('Y-m-d'),
                ),
                1
            );

            if ( ! empty( $usage ) ) {
                $record = $usage[0];
                $used = $record->usage;
                $limit = 9; // Twilio free account limit (can be fetched dynamically if account is upgraded)
                $remaining = max( 0, $limit - $used );
                return [
                    'used' => $used,
                    'remaining' => $remaining,
                    'limit' => $limit,
                ];
            } else {
                return [ 'error' => __( 'Unable to fetch Twilio usage data.', 'simple-limit-login-attempts' ) ];
            }
        } catch ( Exception $e ) {
            error_log( 'Twilio Usage Check Failed: ' . $e->getMessage() );
            return [ 'error' => __( 'Error fetching Twilio usage: ' . $e->getMessage(), 'simple-limit-login-attempts' ) ];
        }
    }
}
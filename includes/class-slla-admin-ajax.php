<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Admin_Ajax
 * Handles AJAX-related functionality for the Simple Limit Login Attempts plugin.
 */
class SLLA_Admin_Ajax {
    private $logs;

    public function __construct( $logs ) {
        $this->logs = $logs;
        add_action( 'wp_ajax_slla_get_recent_failed_attempts', array( $this, 'ajax_get_recent_failed_attempts' ) );
        add_action( 'wp_ajax_slla_validate_twilio_credentials', array( $this, 'validate_twilio_credentials' ) );
    }

    public function ajax_get_recent_failed_attempts() {
        check_ajax_referer( 'slla_admin_nonce', 'nonce' );

        $recent_attempts = $this->logs->get_recent_failed_attempts( 5 );
        $html = '';

        if ( empty( $recent_attempts ) ) {
            $html = '<p>' . __( 'No recent failed login attempts.', 'simple-limit-login-attempts' ) . '</p>';
        } else {
            $html .= '<ul class="slla-notification-list">';
            foreach ( $recent_attempts as $index => $attempt ) {
                $html .= '<li class="slla-notification-card">';
                $html .= '<div class="slla-notification-row">';
                $html .= '<span class="slla-notification-message">' . __( 'Failed Attempt', 'simple-limit-login-attempts' ) . '</span>';
                $html .= '<span class="slla-notification-username">' . esc_html( $attempt->username ) . '</span>';
                $html .= '</div>';
                $html .= '<div class="slla-notification-row">';
                $html .= '<span class="slla-notification-ip">' . esc_html( 'IP: ' . $attempt->ip ) . '</span>';
                $html .= '<span class="slla-notification-time">' . esc_html( $attempt->time ) . '</span>';
                $html .= '</div>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        }

        wp_send_json_success( array( 'html' => $html ) );
    }

    public function validate_twilio_credentials() {
        check_ajax_referer( 'slla_validate_twilio_nonce', 'nonce' );

        $account_sid = isset( $_POST['account_sid'] ) ? sanitize_text_field( $_POST['account_sid'] ) : '';
        $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( $_POST['auth_token'] ) : '';
        $phone_number = isset( $_POST['phone_number'] ) ? sanitize_text_field( $_POST['phone_number'] ) : '';

        if ( empty( $account_sid ) || empty( $auth_token ) || empty( $phone_number ) ) {
            wp_send_json_error( array( 'message' => __( 'Please fill in all Twilio credentials.', 'simple-limit-login-attempts' ) ) );
        }

        try {
            require_once SLLA_PLUGIN_DIR . 'vendor/autoload.php';
            $client = new Twilio\Rest\Client( $account_sid, $auth_token );

            // Test the credentials by fetching account details
            $client->account->fetch();

            // Validate the phone number by checking if it's a valid Twilio number
            $phone = $client->lookups->v1->phoneNumbers( $phone_number )->fetch();

            if ( $phone ) {
                wp_send_json_success();
            } else {
                wp_send_json_error( array( 'message' => __( 'Invalid Twilio phone number.', 'simple-limit-login-attempts' ) ) );
            }
        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => __( 'Invalid Twilio credentials.', 'simple-limit-login-attempts' ) ) );
        }
    }
}
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Admin_Notifications
 * Handles notification-related functionality for the Simple Limit Login Attempts plugin.
 */
class SLLA_Admin_Notifications {
    private $admin;

    public function __construct( $admin ) {
        $this->admin = $admin;
    }

    public function log_failed_attempt( $username, $ip ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $time = current_time( 'mysql' );

        // Insert the failed attempt into the logs
        $wpdb->insert(
            $table_name,
            array(
                'username' => $username,
                'ip'       => $ip,
                'time'     => $time,
                'type'     => 'failed_attempt',
                'reason'   => __( 'Invalid credentials', 'simple-limit-login-attempts' ),
            ),
            array( '%s', '%s', '%s', '%s', '%s' )
        );

        // Send notifications if premium user and notifications are enabled
        if ( $this->admin->is_premium_active() ) {
            $this->send_notifications( $username, $ip, $time );
        }
    }

    public function send_notifications( $username, $ip, $time ) {
        // Check if email notifications are enabled
        if ( get_option( 'slla_enable_email_notifications', 0 ) ) {
            $this->send_email_notification( $username, $ip, $time );
        }

        // Check if SMS notifications are enabled
        if ( get_option( 'slla_enable_sms_notifications', 0 ) ) {
            $this->send_sms_notification( $username, $ip, $time );
        }
    }

    public function send_email_notification( $username, $ip, $time ) {
        $admin_email = get_option( 'slla_admin_email', get_option( 'admin_email' ) );
        if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
            error_log( 'Email Notification Failed: Invalid admin email address.' );
            return;
        }

        // Fetch Geo-Location
        $location = 'Unknown';
        $geo_response = wp_remote_get( "http://ip-api.com/json/{$ip}?fields=city,country" );
        if ( ! is_wp_error( $geo_response ) && wp_remote_retrieve_response_code( $geo_response ) === 200 ) {
            $geo_data = json_decode( wp_remote_retrieve_body( $geo_response ), true );
            if ( ! empty( $geo_data['city'] ) && ! empty( $geo_data['country'] ) ) {
                $location = esc_html( $geo_data['city'] . ', ' . $geo_data['country'] );
            }
        } else {
            error_log( 'Geo-Location API Error: Could not fetch location for IP ' . $ip );
        }

        $subject = __( 'Failed Login Attempt Notification', 'simple-limit-login-attempts' );

        // HTML Email Content with Geo-Location
        $message = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Failed Login Attempt Notification</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    overflow: hidden;
                }
                .header {
                    background-color: #ff6f61;
                    color: #ffffff;
                    padding: 20px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .content {
                    padding: 20px;
                    color: #333333;
                }
                .content p {
                    margin: 10px 0;
                    line-height: 1.6;
                }
                .highlight {
                    font-weight: bold;
                    color: #ff6f61;
                }
                .footer {
                    background-color: #f4f4f4;
                    padding: 10px;
                    text-align: center;
                    font-size: 12px;
                    color: #666666;
                }
                .footer a {
                    color: #ff6f61;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Failed Login Attempt Alert</h1>
                </div>
                <div class="content">
                    <p>Dear Admin,</p>
                    <p>A failed login attempt was detected on your website. Here are the details:</p>
                    <p><span class="highlight">Username:</span> ' . esc_html( $username ) . '</p>
                    <p><span class="highlight">IP Address:</span> ' . esc_html( $ip ) . '</p>
                    <p><span class="highlight">Location:</span> ' . $location . '</p>
                    <p><span class="highlight">Time:</span> ' . esc_html( $time ) . '</p>
                    <p>Please review the logs in your WordPress dashboard for more details.</p>
                </div>
                <div class="footer">
                    <p>Sent from <a href="' . esc_url( home_url() ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a></p>
                </div>
            </div>
        </body>
        </html>';

        // Set headers for HTML email
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . esc_html( get_bloginfo( 'name' ) ) . ' <' . get_option( 'admin_email' ) . '>',
        );

        // Send the email
        $sent = wp_mail( $admin_email, $subject, $message, $headers );

        if ( ! $sent ) {
            error_log( 'Email Notification Failed: Could not send email to ' . $admin_email );
        } else {
            error_log( 'Email Notification Sent: To: ' . $admin_email );
        }
    }

    public function send_sms_notification( $username, $ip, $time ) {
        $phone_number = get_option( 'slla_admin_phone_number', '' );
        $account_sid = get_option( 'slla_twilio_account_sid', '' );
        $auth_token = get_option( 'slla_twilio_auth_token', '' );
        $twilio_phone_number = get_option( 'slla_twilio_phone_number', '' );

        // Check if all required Twilio settings are provided
        if ( empty( $phone_number ) || empty( $account_sid ) || empty( $auth_token ) || empty( $twilio_phone_number ) ) {
            error_log( 'SMS Notification Failed: Missing Twilio credentials or admin phone number.' );
            return;
        }

        $message = sprintf(
            __( 'Failed login attempt detected: Username: %s, IP: %s, Time: %s', 'simple-limit-login-attempts' ),
            $username,
            $ip,
            $time
        );

        try {
            require_once SLLA_PLUGIN_DIR . 'vendor/autoload.php';
            $client = new Twilio\Rest\Client( $account_sid, $auth_token );

            $client->messages->create(
                $phone_number,
                array(
                    'from' => $twilio_phone_number,
                    'body' => $message,
                )
            );

            error_log( "SMS Notification Sent: To: $phone_number, Message: $message" );
        } catch ( Exception $e ) {
            error_log( "SMS Notification Failed: " . $e->getMessage() );
        }
    }
}
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SLLA_Lockout {
    private $core;
    private $logger;

    public function __construct( $core, $logger ) {
        $this->core = $core;
        $this->logger = $logger;
        $this->init();
    }

    public function init() {
        add_action( 'wp_login_failed', array( $this, 'track_failed_attempt' ) );
        add_filter( 'authenticate', array( $this, 'check_lockout' ), 30, 3 );
    }

    public function track_failed_attempt() {
        $ip = $this->core->get_ip_address();
        $transient_key = 'slla_attempts_' . md5( $ip );

        // Check if already locked out
        if ( $this->check_and_set_lockout( $ip ) ) {
            return; // Already locked out, no need to increment attempts
        }

        // Get current attempts
        $attempts = get_transient( $transient_key );

        if ( false === $attempts ) {
            // First attempt
            $attempts = 1;
        } else {
            // Increment attempts
            $attempts++;
        }

        // Store the attempts with 15 minutes expiration
        set_transient( $transient_key, $attempts, 15 * MINUTE_IN_SECONDS );

        // Check again for lockout after incrementing
        $this->check_and_set_lockout( $ip );

        return $attempts;
    }

    public function check_and_set_lockout( $ip ) {
        $transient_key = 'slla_attempts_' . md5( $ip );
        $lockout_key = 'slla_lockout_' . md5( $ip );
        $max_attempts = 5; // Default limit
        $lockout_duration = 15 * MINUTE_IN_SECONDS; // 15 minutes

        // Get current attempts
        $attempts = get_transient( $transient_key );

        // If attempts exceed the limit, set lockout
        if ( $attempts && $attempts >= $max_attempts ) {
            // Set lockout transient
            set_transient( $lockout_key, true, $lockout_duration );

            // Log the lockout event
            $this->logger->log_lockout_event( $ip, 'Too many failed attempts' );

            // Reset attempts after lockout
            delete_transient( $transient_key );

            return true; // IP is locked out
        }

        return false; // IP is not locked out
    }

    public function check_lockout( $user, $username, $password ) {
        $ip = $this->core->get_ip_address();
        $lockout_key = 'slla_lockout_' . md5( $ip );

        // Check if IP is locked out
        if ( get_transient( $lockout_key ) ) {
            $lockout_duration = 15; // In minutes
            $error = new WP_Error( 'locked_out', sprintf(
                __( 'Too many failed attempts. You are locked out for %d minutes.', 'simple-limit-login-attempts' ),
                $lockout_duration
            ) );
            return $error;
        }

        return $user;
    }
}
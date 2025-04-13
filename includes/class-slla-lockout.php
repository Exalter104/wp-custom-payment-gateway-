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

        $attempts = get_transient( $transient_key );

        if ( $attempts && $attempts >= $max_attempts ) {
            set_transient( $lockout_key, true, $lockout_duration );
            $this->logger->log_lockout_event( $ip, 'Too many failed attempts' );
            delete_transient( $transient_key );
            return true;
        }

        return false;
    }

    public function check_lockout( $user, $username, $password ) {
        $ip = $this->core->get_ip_address();
        $lockout_key = 'slla_lockout_' . md5( $ip );

        if ( get_transient( $lockout_key ) ) {
            $lockout_duration = get_option( 'slla_lockout_duration', 15 ); // Use setting
            $error = new WP_Error( 'locked_out', sprintf(
                __( 'Too many failed attempts. You are locked out for %d minutes.', 'simple-limit-login-attempts' ),
                $lockout_duration
            ) );
            return $error;
        }

        return $user;
    }
}
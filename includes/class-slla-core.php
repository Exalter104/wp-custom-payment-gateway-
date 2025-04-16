<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SLLA_Core {
    private $lockout;
    private $logger;
    private $admin;

    public function __construct() {
        $this->lockout = new SLLA_Lockout( $this );
        $this->logger = new SLLA_Logger();
        $this->admin = new SLLA_Admin();

        // Initialize Admin
        $this->admin->init();

        // Hooks
        add_action( 'wp_login_failed', array( $this, 'handle_login_failed' ) );
        add_action( 'wp_login', array( $this, 'handle_login_success' ), 10, 2 );
        add_filter( 'authenticate', array( $this, 'check_lockout' ), 30, 3 );
        add_filter( 'wp_login_errors', array( $this, 'display_remaining_attempts' ), 10, 2 );
        add_filter( 'shake_error_codes', array( $this, 'add_shake_error_codes' ) );
    }

    public function get_ip_address() {
        return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';
    }

    public function handle_login_failed( $username ) {
        $ip = $this->get_ip_address();
        $transient_key = 'slla_attempts_' . md5( $ip );
        $attempts = get_transient( $transient_key );

        if ( false === $attempts ) {
            $attempts = 0;
        }

        $attempts++;
        $result = set_transient( $transient_key, $attempts, HOUR_IN_SECONDS );

        // Debugging: Log transient set status
        error_log( "Failed attempt for IP $ip: Attempt #$attempts" );
        error_log( "Transient set result for $transient_key: " . ( $result ? 'Success' : 'Failed' ) );

        // Log the failed attempt in the database
        $this->logger->log_failed_attempt( $username, $ip );

        $this->lockout->check_and_set_lockout( $ip );
    }

    public function handle_login_success( $user_login, $user ) {
        $ip = $this->get_ip_address();
        $transient_key = 'slla_attempts_' . md5( $ip );
        delete_transient( $transient_key );

        $this->logger->log_successful_login( $user->user_login, $ip );
    }

    public function check_lockout( $user, $username, $password ) {
        return $this->lockout->check_lockout( $user, $username, $password );
    }

    public function add_shake_error_codes( $shake_error_codes ) {
        $shake_error_codes[] = 'locked_out';
        return $shake_error_codes;
    }

    public function display_remaining_attempts( $errors, $redirect_to ) {
        $ip = $this->get_ip_address();
        if ( empty( $ip ) ) {
            return $errors;
        }

        $transient_key = 'slla_attempts_' . md5( $ip );
        $attempts = get_transient( $transient_key );
        $max_attempts = get_option( 'slla_max_attempts', 5 );

        // Debugging: Log attempts and max attempts
        error_log( "Display remaining attempts for IP $ip: Current attempts = " . ( $attempts !== false ? $attempts : 0 ) . ", Max attempts = $max_attempts" );

        $lockout_key = 'slla_lockout_' . md5( $ip );
        if ( get_transient( $lockout_key ) ) {
            // Lockout message already handled in SLLA_Lockout::check_lockout
            return $errors;
        } elseif ( $attempts !== false && ( isset( $errors->errors['invalid_username'] ) || isset( $errors->errors['incorrect_password'] ) ) ) {
            $remaining_attempts = max( 0, $max_attempts - $attempts );
            $errors->add( 'slla_remaining_attempts', sprintf(
                __( 'You have %d attempt(s) remaining before lockout.', 'simple-limit-login-attempts' ),
                $remaining_attempts
            ), 'message' );
        }

        return $errors;
    }
}
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
        set_transient( $transient_key, $attempts, HOUR_IN_SECONDS );

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
}
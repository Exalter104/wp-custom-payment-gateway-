<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_2FA
 * Handles Two-Factor Authentication functionality.
 */
class SLLA_2FA {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize 2FA functionality.
     */
    public function init() {
        add_action( 'wp_login', array( $this, 'maybe_require_2fa' ), 10, 2 );
        add_filter( 'login_form', array( $this, 'display_otp_form' ) );
        add_action( 'wp_authenticate', array( $this, 'verify_otp' ), 10, 2 );
    }

    /**
     * Check if 2FA is required after successful login.
     *
     * @param string $user_login Username of the user.
     * @param WP_User $user WP_User object of the logged-in user.
     */
    public function maybe_require_2fa( $user_login, $user ) {
        error_log( 'maybe_require_2fa called for user: ' . $user_login );
        // Skip if OTP was just verified
        if ( get_transient( 'slla_2fa_verified_' . $user->ID ) ) {
            error_log( 'Skipping maybe_require_2fa: OTP already verified for user ID: ' . $user->ID );
            delete_transient( 'slla_2fa_verified_' . $user->ID );
            return;
        }
        if ( ! $this->is_2fa_enabled() ) {
            error_log( '2FA not enabled or premium not active' );
            return;
        }
        error_log( 'Proceeding with 2FA for user: ' . $user_login );

        // Store user ID in session to verify later
        $this->set_2fa_session( $user->ID );
        // Generate and send OTP
        $otp = $this->generate_otp();
        $this->store_otp( $user->ID, $otp );
        $this->send_otp( $user, $otp );
        // Prevent full login until OTP is verified
        error_log( 'Redirecting to OTP form' );
        wp_logout();
        wp_redirect( wp_login_url() . '?slla_2fa=1' );
        exit;
    }

    /**
     * Check if 2FA is enabled and user is premium.
     *
     * @return bool
     */
    public function is_2fa_enabled() {
        $admin = new SLLA_Admin();
        return $admin->is_premium_active() && get_option( 'slla_enable_2fa', 0 ) == 1;
    }

    /**
     * Set 2FA session data.
     *
     * @param int $user_id User ID.
     */
    public function set_2fa_session( $user_id ) {
        if ( ! session_id() ) {
            session_start();
        }
        $_SESSION['slla_2fa_user_id'] = $user_id;
        error_log( '2FA Session set for user ID: ' . $user_id );
    }

    /**
     * Get 2FA session data.
     *
     * @return int|null User ID or null.
     */
    public function get_2fa_session() {
        if ( ! session_id() ) {
            session_start();
        }
        return isset( $_SESSION['slla_2fa_user_id'] ) ? absint( $_SESSION['slla_2fa_user_id'] ) : null;
    }

    /**
     * Clear 2FA session data.
     */
    public function clear_2fa_session() {
        if ( ! session_id() ) {
            session_start();
        }
        unset( $_SESSION['slla_2fa_user_id'] );
    }

    /**
     * Generate a 6-digit OTP.
     *
     * @return string
     */
    public function generate_otp() {
        return str_pad( rand( 0, 999999 ), 6, '0', STR_PAD_LEFT );
    }

    /**
     * Store OTP in user meta (temporary).
     *
     * @param int $user_id User ID.
     * @param string $otp OTP to store.
     */
    public function store_otp( $user_id, $otp ) {
        update_user_meta( $user_id, 'slla_2fa_otp', $otp );
        update_user_meta( $user_id, 'slla_2fa_otp_time', time() );
    }

    /**
     * Get stored OTP for a user.
     *
     * @param int $user_id User ID.
     * @return string|null
     */
    public function get_stored_otp( $user_id ) {
        return get_user_meta( $user_id, 'slla_2fa_otp', true );
    }

    /**
     * Send OTP via SMS or Email (with fallback).
     *
     * @param WP_User $user WP_User object.
     * @param string $otp OTP to send.
     * @return bool
     */
    public function send_otp( $user, $otp ) {
        $phone_number = get_user_meta( $user->ID, 'slla_phone_number', true );
        if ( empty( $phone_number ) ) {
            $phone_number = get_option( 'slla_admin_phone_number', '' );
        }
        $user_email = $user->user_email;

        if ( empty( $phone_number ) && empty( $user_email ) ) {
            error_log( '2FA Failed: No phone number or email available for user ' . $user->ID );
            return false;
        }

        // Prepare the OTP message
        $message = sprintf(
            __( 'Your 2FA OTP for %s is %s. Valid for 10 minutes.', 'simple-limit-login-attempts' ),
            home_url(),
            $otp
        );

        // Try sending via SMS
        $sms_sent = false;
        if ( ! empty( $phone_number ) ) {
            $sms_sent = SLLA_Twilio::send_sms_notification( $message );
            if ( $sms_sent ) {
                error_log( '2FA OTP sent via SMS to: ' . $phone_number );
                // Clear the limit exceeded transient if SMS is successful
                delete_transient( 'slla_twilio_limit_exceeded' );
            } else {
                error_log( '2FA SMS Failed: Falling back to email for user ' . $user->ID );
                // Set a transient to indicate SMS limit exceeded
                set_transient( 'slla_twilio_limit_exceeded', true, DAY_IN_SECONDS );
            }
        }

        // Fallback to email if SMS fails or phone number is not available
        if ( ! $sms_sent && ! empty( $user_email ) ) {
            $subject = __( 'Your 2FA Code - Simple Limit Login Attempts', 'simple-limit-login-attempts' );
            $email_message = sprintf(
                __( "Your 2FA OTP for %s is %s. Valid for 10 minutes.\n\nIf you did not request this code, please ignore this email.", 'simple-limit-login-attempts' ),
                home_url(),
                $otp
            );
            $email_sent = wp_mail( $user_email, $subject, $email_message );

            if ( $email_sent ) {
                error_log( '2FA OTP sent via email to: ' . $user_email );
                return true;
            } else {
                error_log( '2FA Email Failed: Unable to send OTP to ' . $user_email );
                return false;
            }
        }

        return $sms_sent;
    }

    /**
     * Display OTP form on login page if needed.
     */
    public function display_otp_form() {
        if ( ! isset( $_GET['slla_2fa'] ) || $_GET['slla_2fa'] != '1' ) {
            error_log( 'slla_2fa parameter missing or invalid' );
            return;
        }
        $user_id = $this->get_2fa_session();
        if ( ! $user_id ) {
            error_log( 'No user ID found in 2FA session' );
            return;
        }
        error_log( 'Displaying OTP form' );

        // Display message if SMS limit exceeded
        if ( get_transient( 'slla_twilio_limit_exceeded' ) ) {
            echo '<p style="color: red;">' . __( 'SMS limit exceeded. 2FA code has been sent to your email.', 'simple-limit-login-attempts' ) . '</p>';
        }

        require_once SLLA_PLUGIN_DIR . 'templates/otp-verification.php';
        exit;
    }

    /**
     * Verify OTP during login.
     *
     * @param string $username Username.
     * @param string $password Password.
     */
    public function verify_otp( $username, $password ) {
        error_log( 'verify_otp called' );
        if ( ! isset( $_POST['slla_2fa_otp'] ) ) {
            error_log( 'slla_2fa_otp not set in POST' );
            return;
        }
        error_log( 'Processing OTP: ' . sanitize_text_field( $_POST['slla_2fa_otp'] ) );
        $user_id = $this->get_2fa_session();
        if ( ! $user_id ) {
            error_log( 'No user ID in 2FA session' );
            wp_die( __( '2FA session expired. Please try logging in again.', 'simple-limit-login-attempts' ) );
        }
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            error_log( 'User not found for ID: ' . $user_id );
            wp_die( __( 'User not found.', 'simple-limit-login-attempts' ) );
        }
        $submitted_otp = sanitize_text_field( $_POST['slla_2fa_otp'] );
        $stored_otp = $this->get_stored_otp( $user_id );
        $otp_time = get_user_meta( $user_id, 'slla_2fa_otp_time', true );
        if ( ( time() - $otp_time ) > 600 ) {
            error_log( 'OTP expired for user ID: ' . $user_id );
            wp_die( __( 'OTP expired. Please try logging in again.', 'simple-limit-login-attempts' ) );
        }
        if ( $submitted_otp !== $stored_otp ) {
            error_log( 'Invalid OTP entered: ' . $submitted_otp . ' (Stored: ' . $stored_otp . ')' );
            wp_die( __( 'Invalid OTP. Please try again.', 'simple-limit-login-attempts' ) );
        }
        error_log( 'OTP verified for user ID: ' . $user_id );
        // Set flag to prevent maybe_require_2fa loop
        set_transient( 'slla_2fa_verified_' . $user_id, true, 30 );
        $this->clear_2fa_session();
        delete_user_meta( $user_id, 'slla_2fa_otp' );
        delete_user_meta( $user_id, 'slla_2fa_otp_time' );
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );
        // Avoid triggering maybe_require_2fa again
        remove_action( 'wp_login', array( $this, 'maybe_require_2fa' ), 10 );
        do_action( 'wp_login', $user->user_login, $user );
        error_log( 'Redirecting to dashboard for user ID: ' . $user_id );
        wp_redirect( admin_url() );
        exit;
    }
}
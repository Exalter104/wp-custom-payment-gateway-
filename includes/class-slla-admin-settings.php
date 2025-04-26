<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Admin_Settings
 * Handles settings-related functionality for the Simple Limit Login Attempts plugin.
 */
class SLLA_Admin_Settings {
    private $admin;

    public function __construct( $admin ) {
        $this->admin = $admin;
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function register_settings() {
        // General Settings (Settings Page)
        register_setting( 'slla_settings_group', 'slla_max_attempts', array(
            'default' => 5,
            'sanitize_callback' => array( $this, 'sanitize_max_attempts' )
        ));
        register_setting( 'slla_settings_group', 'slla_lockout_duration', array(
            'default' => 15,
            'sanitize_callback' => array( $this, 'sanitize_lockout_duration' )
        ));
        register_setting( 'slla_settings_group', 'slla_safelist_ips', array(
            'default' => '',
            'sanitize_callback' => array( $this, 'sanitize_textarea' )
        ));
        register_setting( 'slla_settings_group', 'slla_denylist_ips', array(
            'default' => '',
            'sanitize_callback' => array( $this, 'sanitize_textarea' )
        ));
        register_setting( 'slla_settings_group', 'slla_custom_error_message', array(
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting( 'slla_settings_group', 'slla_gdpr_compliance', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));

        // Security Checklist Settings (Settings Page)
        register_setting( 'slla_settings_group', 'slla_enable_auto_updates', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));
        register_setting( 'slla_settings_group', 'slla_email_notifications', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));
        register_setting( 'slla_settings_group', 'slla_strong_password', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));
        register_setting( 'slla_settings_group', 'slla_enable_2fa', array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 0,
        ));

        // Notifications Settings (Notifications Page)
        register_setting( 'slla_notifications_group', 'slla_twilio_account_sid', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));
        register_setting( 'slla_notifications_group', 'slla_twilio_auth_token', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));
        register_setting( 'slla_notifications_group', 'slla_twilio_phone_number', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));
        register_setting( 'slla_notifications_group', 'slla_enable_email_notifications', array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 0,
        ));
        register_setting( 'slla_notifications_group', 'slla_enable_sms_notifications', array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 0,
        ));
        register_setting( 'slla_notifications_group', 'slla_admin_phone_number', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));

        // Geo-Blocking Settings (Geo-Blocking Page)
        register_setting( 
            'slla_geoblocking_group', // Separate group for Geo-Blocking
            'slla_ipstack_api_key', 
            array( 
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            ) 
        );
        register_setting( 
            'slla_geoblocking_group', // Separate group for Geo-Blocking
            'slla_allowed_countries', 
            array( 
                'sanitize_callback' => array( $this, 'sanitize_allowed_countries' ),
                'default' => array( 'PK' )
            ) 
        );

        // Premium Settings (Settings Page)
        register_setting( 'slla_settings_group', 'slla_setup_code', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting( 'slla_settings_group', 'slla_block_countries', array(
            'sanitize_callback' => array( $this, 'sanitize_textarea' )
        ));

        // General Settings Section
        add_settings_section(
            'slla_general_settings',
            __( 'General Settings', 'simple-limit-login-attempts' ),
            array( $this, 'general_settings_callback' ),
            'slla-settings'
        );

        add_settings_field(
            'slla_max_attempts',
            __( 'Maximum Failed Attempts', 'simple-limit-login-attempts' ),
            array( $this, 'max_attempts_callback' ),
            'slla-settings',
            'slla_general_settings'
        );

        add_settings_field(
            'slla_lockout_duration',
            __( 'Lockout Duration (minutes)', 'simple-limit-login-attempts' ),
            array( $this, 'lockout_duration_callback' ),
            'slla-settings',
            'slla_general_settings'
        );

        add_settings_field(
            'slla_safelist_ips',
            __( 'Safelist IPs', 'simple-limit-login-attempts' ),
            array( $this, 'safelist_ips_callback' ),
            'slla-settings',
            'slla_general_settings'
        );

        add_settings_field(
            'slla_denylist_ips',
            __( 'Denylist IPs', 'simple-limit-login-attempts' ),
            array( $this, 'denylist_ips_callback' ),
            'slla-settings',
            'slla_general_settings'
        );

        add_settings_field(
            'slla_custom_error_message',
            __( 'Custom Error Message', 'simple-limit-login-attempts' ),
            array( $this, 'custom_error_message_callback' ),
            'slla-settings',
            'slla_general_settings'
        );

        add_settings_field(
            'slla_gdpr_compliance',
            __( 'GDPR Compliance', 'simple-limit-login-attempts' ),
            array( $this, 'gdpr_compliance_callback' ),
            'slla-settings',
            'slla_general_settings'
        );

        // Security Checklist Section
        add_settings_section(
            'slla_security_checklist',
            __( 'Login Security Checklist', 'simple-limit-login-attempts' ),
            array( $this, 'security_checklist_callback' ),
            'slla-settings'
        );

        add_settings_field(
            'slla_enable_auto_updates',
            __( 'Enable Auto Updates', 'simple-limit-login-attempts' ),
            array( $this, 'enable_auto_updates_callback' ),
            'slla-settings',
            'slla_security_checklist'
        );

        add_settings_field(
            'slla_email_notifications',
            __( 'Enable Email Notifications', 'simple-limit-login-attempts' ),
            array( $this, 'email_notifications_callback' ),
            'slla-settings',
            'slla_security_checklist'
        );

        add_settings_field(
            'slla_strong_password',
            __( 'Enforce Strong Passwords', 'simple-limit-login-attempts' ),
            array( $this, 'strong_password_callback' ),
            'slla-settings',
            'slla_security_checklist'
        );

        add_settings_field(
            'slla_enable_2fa',
            __( 'Enable Two-Factor Authentication (2FA)', 'simple-limit-login-attempts' ),
            array( $this, 'enable_2fa_callback' ),
            'slla-settings',
            'slla_security_checklist'
        );

        // Premium Settings Section
        add_settings_section(
            'slla_premium_settings',
            __( 'Premium Settings', 'simple-limit-login-attempts' ),
            array( $this, 'premium_settings_callback' ),
            'slla-premium-settings'
        );

        add_settings_field(
            'slla_setup_code',
            __( 'Setup Code', 'simple-limit-login-attempts' ),
            array( $this, 'setup_code_callback' ),
            'slla-premium-settings',
            'slla_premium_settings'
        );

        add_settings_field(
            'slla_block_countries',
            __( 'Block Countries', 'simple-limit-login-attempts' ),
            array( $this, 'block_countries_callback' ),
            'slla-premium-settings',
            'slla_premium_settings'
        );
    }

    public function general_settings_callback() {
        echo '<p class="description">' . __( 'Configure the general settings for login attempt limits.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function security_checklist_callback() {
        echo '<p class="description">' . __( 'Enhance your site security with these options.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function premium_settings_callback() {
        echo '<p class="description">' . __( 'Configure premium settings for advanced features.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function max_attempts_callback() {
        $max_attempts = get_option( 'slla_max_attempts', 5 );
        echo '<input type="number" name="slla_max_attempts" value="' . esc_attr( $max_attempts ) . '" min="1" class="slla-input" />';
    }

    public function lockout_duration_callback() {
        $lockout_duration = get_option( 'slla_lockout_duration', 15 );
        echo '<input type="number" name="slla_lockout_duration" value="' . esc_attr( $lockout_duration ) . '" min="1" class="slla-input" />';
    }

    public function safelist_ips_callback() {
        $safelist_ips = get_option( 'slla_safelist_ips', '' );
        echo '<textarea name="slla_safelist_ips" rows="5" class="slla-input">' . esc_textarea( $safelist_ips ) . '</textarea>';
        echo '<p class="description">' . __( 'Enter one IP address per line to safelist.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function denylist_ips_callback() {
        $denylist_ips = get_option( 'slla_denylist_ips', '' );
        echo '<textarea name="slla_denylist_ips" rows="5" class="slla-input">' . esc_textarea( $denylist_ips ) . '</textarea>';
        echo '<p class="description">' . __( 'Enter one IP address per line to denylist.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function custom_error_message_callback() {
        $custom_error_message = get_option( 'slla_custom_error_message', '' );
        echo '<input type="text" name="slla_custom_error_message" value="' . esc_attr( $custom_error_message ) . '" class="slla-input" />';
        echo '<p class="description">' . __( 'Custom error message for failed login attempts.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function gdpr_compliance_callback() {
        $gdpr_compliance = get_option( 'slla_gdpr_compliance', 0 );
        echo '<input type="checkbox" name="slla_gdpr_compliance" value="1" ' . checked( 1, $gdpr_compliance, false ) . ' />';
        echo '<p class="description">' . __( 'Enable GDPR compliance (do not store sensitive data in logs).', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function enable_auto_updates_callback() {
        $enable_auto_updates = get_option( 'slla_enable_auto_updates', 0 );
        echo '<input type="checkbox" name="slla_enable_auto_updates" value="1" ' . checked( 1, $enable_auto_updates, false ) . ' />';
        echo '<p class="description">' . __( 'Enable automatic updates for the plugin.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function email_notifications_callback() {
        $email_notifications = get_option( 'slla_email_notifications', 0 );
        if ( ! $this->admin->is_premium_active() ) {
            echo '<input type="checkbox" disabled />';
            echo '<p class="description">' . __( 'Enable email notifications for failed login attempts. (Premium feature)', 'simple-limit-login-attempts' ) . '</p>';
        } else {
            echo '<input type="checkbox" name="slla_email_notifications" value="1" ' . checked( 1, $email_notifications, false ) . ' />';
            echo '<p class="description">' . __( 'Enable email notifications for failed login attempts.', 'simple-limit-login-attempts' ) . '</p>';
        }
    }

    public function strong_password_callback() {
        $strong_password = get_option( 'slla_strong_password', 0 );
        if ( ! $this->admin->is_premium_active() ) {
            echo '<input type="checkbox" disabled />';
            echo '<p class="description">' . __( 'Enforce strong passwords for users. (Premium feature)', 'simple-limit-login-attempts' ) . '</p>';
        } else {
            echo '<input type="checkbox" name="slla_strong_password" value="1" ' . checked( 1, $strong_password, false ) . ' />';
            echo '<p class="description">' . __( 'Enforce strong passwords for users.', 'simple-limit-login-attempts' ) . '</p>';
        }
    }

    public function enable_2fa_callback() {
        $enable_2fa = get_option( 'slla_enable_2fa', 0 );
        if ( ! $this->admin->is_premium_active() ) {
            echo '<input type="checkbox" disabled />';
            echo '<p class="description">' . __( 'Enable Two-Factor Authentication via SMS. (Premium feature)', 'simple-limit-login-attempts' ) . '</p>';
        } else {
            echo '<input type="checkbox" name="slla_enable_2fa" value="1" ' . checked( 1, $enable_2fa, false ) . ' />';
            echo '<p class="description">' . __( 'Enable Two-Factor Authentication via SMS.', 'simple-limit-login-attempts' ) . '</p>';
        }
    }

    public function setup_code_callback() {
        $setup_code = get_option( 'slla_setup_code', '' );
        echo '<input type="text" name="slla_setup_code" value="' . esc_attr( $setup_code ) . '" class="slla-input" />';
        echo '<p class="description">' . __( 'Enter the setup code to activate premium features.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function block_countries_callback() {
        $block_countries = get_option( 'slla_block_countries', '' );
        echo '<textarea name="slla_block_countries" rows="5" class="slla-input">' . esc_textarea( $block_countries ) . '</textarea>';
        echo '<p class="description">' . __( 'Enter country codes (e.g., US, CN) to block, one per line. (Premium feature)', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function sanitize_max_attempts( $value ) {
        $value = absint( $value );
        return max( 1, $value );
    }

    public function sanitize_lockout_duration( $value ) {
        $value = absint( $value );
        return max( 1, $value );
    }

    public function sanitize_textarea( $value ) {
        return sanitize_textarea_field( $value );
    }

    public function sanitize_allowed_countries( $value ) {
        // If no value is provided, return the default (Pakistan)
        if ( empty( $value ) || ! is_array( $value ) ) {
            return array( 'PK' );
        }

        // Valid country codes
        $valid_countries = array( 'PK', 'US', 'IN', 'GB', 'CA', 'AU', 'DE', 'FR', 'CN', 'JP' );

        // Sanitize each country code and ensure it's valid
        $sanitized = array();
        foreach ( $value as $country ) {
            $country = sanitize_text_field( $country );
            if ( in_array( $country, $valid_countries ) ) {
                $sanitized[] = $country;
            }
        }

        // If no valid countries after sanitization, return default
        return empty( $sanitized ) ? array( 'PK' ) : $sanitized;
    }
}
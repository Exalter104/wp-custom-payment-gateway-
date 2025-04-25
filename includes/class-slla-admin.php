<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Admin
 * Main admin class for the Simple Limit Login Attempts plugin.
 */
class SLLA_Admin {
    private $settings;
    private $pages;
    private $logs;
    private $insights;
    private $notifications;
    private $ajax;
    private $stats;

    public function __construct() {
        $this->load_dependencies();
        $this->init();
    }

    private function load_dependencies() {
        require_once SLLA_PLUGIN_DIR . 'includes/class-slla-admin-settings.php';
        require_once SLLA_PLUGIN_DIR . 'includes/class-slla-admin-pages.php';
        require_once SLLA_PLUGIN_DIR . 'includes/class-slla-admin-logs.php';
        require_once SLLA_PLUGIN_DIR . 'includes/class-slla-admin-insights.php';
        require_once SLLA_PLUGIN_DIR . 'includes/class-slla-admin-notifications.php';
        require_once SLLA_PLUGIN_DIR . 'includes/class-slla-admin-ajax.php';
        require_once SLLA_PLUGIN_DIR . 'includes/class-slla-admin-stats.php';

        $this->settings = new SLLA_Admin_Settings( $this );
        $this->pages = new SLLA_Admin_Pages( $this );
        $this->logs = new SLLA_Admin_Logs( $this ); // Pass $this as $admin
        $this->insights = new SLLA_Admin_Insights();
        $this->notifications = new SLLA_Admin_Notifications( $this );
        $this->ajax = new SLLA_Admin_Ajax( $this->logs );
        $this->stats = new SLLA_Admin_Stats();
    }

    public function init() {
        // Register all settings
        add_action( 'admin_init', array( $this, 'register_all_settings' ) );
    }

    public function is_premium_active() {
        return get_option( 'slla_premium_activated', 0 ) == 1;
    }

    // Register all settings
    public function register_all_settings() {
        // General Settings
        $this->register_general_settings();

        // Login Security Checklist
        $this->register_security_checklist_settings();

        // Geo-Blocking Settings
        $this->register_geoblock_settings();
    }

    // General Settings
    private function register_general_settings() {
        // Register settings
        register_setting( 'slla_settings_group', 'slla_max_attempts' );
        register_setting( 'slla_settings_group', 'slla_lockout_duration' );
        register_setting( 'slla_settings_group', 'slla_safelist_ips' );
        register_setting( 'slla_settings_group', 'slla_denylist_ips' );
        register_setting( 'slla_settings_group', 'slla_custom_error_message' );
        register_setting( 'slla_settings_group', 'slla_gdpr_compliance' );

        // Add General Settings section
        add_settings_section(
            'slla_general_section',
            __( 'General Settings', 'simple-limit-login-attempts' ),
            array( $this, 'general_section_callback' ),
            'slla-settings'
        );

        // Maximum Failed Attempts
        add_settings_field(
            'slla_max_attempts',
            __( 'Maximum Failed Attempts', 'simple-limit-login-attempts' ),
            array( $this, 'max_attempts_callback' ),
            'slla-settings',
            'slla_general_section'
        );

        // Lockout Duration
        add_settings_field(
            'slla_lockout_duration',
            __( 'Lockout Duration (minutes)', 'simple-limit-login-attempts' ),
            array( $this, 'lockout_duration_callback' ),
            'slla-settings',
            'slla_general_section'
        );

        // Safelist IPs
        add_settings_field(
            'slla_safelist_ips',
            __( 'Safelist IPs', 'simple-limit-login-attempts' ),
            array( $this, 'safelist_ips_callback' ),
            'slla-settings',
            'slla_general_section'
        );

        // Denylist IPs
        add_settings_field(
            'slla_denylist_ips',
            __( 'Denylist IPs', 'simple-limit-login-attempts' ),
            array( $this, 'denylist_ips_callback' ),
            'slla-settings',
            'slla_general_section'
        );

        // Custom Error Message
        add_settings_field(
            'slla_custom_error_message',
            __( 'Custom Error Message', 'simple-limit-login-attempts' ),
            array( $this, 'custom_error_message_callback' ),
            'slla-settings',
            'slla_general_section'
        );

        // GDPR Compliance
        add_settings_field(
            'slla_gdpr_compliance',
            __( 'GDPR Compliance', 'simple-limit-login-attempts' ),
            array( $this, 'gdpr_compliance_callback' ),
            'slla-settings',
            'slla_general_section'
        );
    }

    public function general_section_callback() {
        echo '<p>' . __( 'Configure general settings for login attempt limits.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function max_attempts_callback() {
        $value = get_option( 'slla_max_attempts', 5 );
        echo '<input type="number" name="slla_max_attempts" id="slla_max_attempts" value="' . esc_attr( $value ) . '" min="1" class="slla-input" />';
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Set the maximum number of failed login attempts before a user is locked out.', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    public function lockout_duration_callback() {
        $value = get_option( 'slla_lockout_duration', 15 );
        echo '<input type="number" name="slla_lockout_duration" id="slla_lockout_duration" value="' . esc_attr( $value ) . '" min="1" class="slla-input" />';
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Set the duration (in minutes) a user will be locked out after reaching the maximum failed attempts.', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    public function safelist_ips_callback() {
        $value = get_option( 'slla_safelist_ips', '' );
        echo '<textarea name="slla_safelist_ips" id="slla_safelist_ips" rows="5" class="slla-input slla-ip-input">' . esc_textarea( $value ) . '</textarea>';
        echo '<p class="description">' . __( 'Enter one IP address per line to safelist.', 'simple-limit-login-attempts' ) . '</p>';
        echo '<div class="slla-ip-error" style="color: #ff6f61; display: none;">' . __( 'Invalid IP address detected. Please enter valid IPs (e.g., 192.168.1.1).', 'simple-limit-login-attempts' ) . '</div>';
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Enter IP addresses that should never be locked out. One IP per line.', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    public function denylist_ips_callback() {
        $value = get_option( 'slla_denylist_ips', '' );
        echo '<textarea name="slla_denylist_ips" id="slla_denylist_ips" rows="5" class="slla-input slla-ip-input">' . esc_textarea( $value ) . '</textarea>';
        echo '<p class="description">' . __( 'Enter one IP address per line to denylist.', 'simple-limit-login-attempts' ) . '</p>';
        echo '<div class="slla-ip-error" style="color: #ff6f61; display: none;">' . __( 'Invalid IP address detected. Please enter valid IPs (e.g., 192.168.1.1).', 'simple-limit-login-attempts' ) . '</div>';
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Enter IP addresses that should always be blocked from logging in. One IP per line.', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    public function custom_error_message_callback() {
        $value = get_option( 'slla_custom_error_message', '' );
        echo '<input type="text" name="slla_custom_error_message" id="slla_custom_error_message" value="' . esc_attr( $value ) . '" class="slla-input" />';
        echo '<p class="description">' . __( 'Custom error message for failed login attempts.', 'simple-limit-login-attempts' ) . '</p>';
        echo '<div class="slla-error-preview"><strong>' . __( 'Preview:', 'simple-limit-login-attempts' ) . '</strong> <span id="slla_error_preview">' . esc_html( $value ) . '</span></div>';
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Set a custom error message to display when a user exceeds the maximum failed login attempts.', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    public function gdpr_compliance_callback() {
        $value = get_option( 'slla_gdpr_compliance', 0 );
        echo '<input type="checkbox" name="slla_gdpr_compliance" id="slla_gdpr_compliance" value="1"' . checked( 1, $value, false ) . ' />';
        echo '<p class="description">' . __( 'Enable GDPR compliance (do not store sensitive data in logs).', 'simple-limit-login-attempts' ) . '</p>';
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Enable this to avoid storing sensitive data (e.g., IP addresses) in logs, ensuring GDPR compliance.', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    // Login Security Checklist Settings
    private function register_security_checklist_settings() {
        // Register settings
        register_setting( 'slla_settings_group', 'slla_enable_auto_updates' );
        register_setting( 'slla_settings_group', 'slla_email_notifications' );
        register_setting( 'slla_settings_group', 'slla_strong_password' );
        register_setting( 'slla_settings_group', 'slla_enable_2fa' );

        // Add Security Checklist section
        add_settings_section(
            'slla_security_checklist_section',
            __( 'Login Security Checklist', 'simple-limit-login-attempts' ),
            array( $this, 'security_checklist_section_callback' ),
            'slla-settings'
        );

        // Enable Auto Updates
        add_settings_field(
            'slla_enable_auto_updates',
            __( 'Enable Auto Updates', 'simple-limit-login-attempts' ),
            array( $this, 'enable_auto_updates_callback' ),
            'slla-settings',
            'slla_security_checklist_section'
        );

        // Enable Email Notifications
        add_settings_field(
            'slla_email_notifications',
            __( 'Enable Email Notifications', 'simple-limit-login-attempts' ),
            array( $this, 'email_notifications_callback' ),
            'slla-settings',
            'slla_security_checklist_section'
        );

        // Enforce Strong Passwords
        add_settings_field(
            'slla_strong_password',
            __( 'Enforce Strong Passwords', 'simple-limit-login-attempts' ),
            array( $this, 'strong_password_callback' ),
            'slla-settings',
            'slla_security_checklist_section'
        );

        // Enable Two-Factor Authentication
        add_settings_field(
            'slla_enable_2fa',
            __( 'Enable Two-Factor Authentication (2FA)', 'simple-limit-login-attempts' ),
            array( $this, 'enable_2fa_callback' ),
            'slla-settings',
            'slla_security_checklist_section'
        );
    }

    public function security_checklist_section_callback() {
        echo '<p>' . __( 'Enhance your site security with these options.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function enable_auto_updates_callback() {
        $value = get_option( 'slla_enable_auto_updates', 0 );
        echo '<input type="checkbox" name="slla_enable_auto_updates" id="slla_enable_auto_updates" value="1"' . checked( 1, $value, false ) . ' />';
        echo '<p class="description">' . __( 'Enable automatic updates for the plugin.', 'simple-limit-login-attempts' ) . '</p>';
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Enable automatic updates for the plugin.', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    public function email_notifications_callback() {
        $value = get_option( 'slla_email_notifications', 0 );
        if ( ! $this->is_premium_active() ) {
            echo '<input type="checkbox" disabled />';
            echo '<p class="description">' . __( 'Enable email notifications for failed login attempts. (Premium feature)', 'simple-limit-login-attempts' ) . '</p>';
        } else {
            echo '<input type="checkbox" name="slla_email_notifications" id="slla_email_notifications" value="1"' . checked( 1, $value, false ) . ' />';
            echo '<p class="description">' . __( 'Enable email notifications for failed login attempts.', 'simple-limit-login-attempts' ) . '</p>';
        }
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Receive email notifications for failed login attempts. (Premium feature)', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    public function strong_password_callback() {
        $value = get_option( 'slla_strong_password', 0 );
        if ( ! $this->is_premium_active() ) {
            echo '<input type="checkbox" disabled />';
            echo '<p class="description">' . __( 'Enforce strong passwords for users. (Premium feature)', 'simple-limit-login-attempts' ) . '</p>';
        } else {
            echo '<input type="checkbox" name="slla_strong_password" id="slla_strong_password" value="1"' . checked( 1, $value, false ) . ' />';
            echo '<p class="description">' . __( 'Enforce strong passwords for users.', 'simple-limit-login-attempts' ) . '</p>';
        }
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Force users to use strong passwords. (Premium feature)', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    public function enable_2fa_callback() {
        $value = get_option( 'slla_enable_2fa', 0 );
        if ( ! $this->is_premium_active() ) {
            echo '<input type="checkbox" disabled />';
            echo '<p class="description">' . __( 'Enable Two-Factor Authentication via SMS. (Premium feature)', 'simple-limit-login-attempts' ) . '</p>';
        } else {
            echo '<input type="checkbox" name="slla_enable_2fa" id="slla_enable_2fa" value="1"' . checked( 1, $value, false ) . ' />';
            echo '<p class="description">' . __( 'Enable Two-Factor Authentication via SMS.', 'simple-limit-login-attempts' ) . '</p>';
        }
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Add an extra layer of security with 2FA via SMS. (Premium feature)', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    // Register settings for Geo-Blocking
    public function register_geoblock_settings() {
        // Register ipstack API key setting with sanitization callback
        register_setting( 
            'slla_settings_group', 
            'slla_ipstack_api_key', 
            array( 
                'sanitize_callback' => array( $this, 'sanitize_ipstack_api_key' ) 
            ) 
        );
        
        // Register allowed countries setting with sanitization callback
        register_setting( 
            'slla_settings_group', 
            'slla_allowed_countries', 
            array( 
                'sanitize_callback' => array( $this, 'sanitize_allowed_countries' ) 
            ) 
        );

        // Add settings section for Geo-Blocking
        add_settings_section(
            'slla_geoblock_section',
            __( 'Geo-Blocking Settings', 'simple-limit-login-attempts' ),
            array( $this, 'geoblock_section_callback' ),
            'slla-settings'
        );

        // Add ipstack API key field
        add_settings_field(
            'slla_ipstack_api_key',
            __( 'ipstack API Key', 'simple-limit-login-attempts' ),
            array( $this, 'ipstack_api_key_callback' ),
            'slla-settings',
            'slla_geoblock_section'
        );

        // Add allowed countries field
        add_settings_field(
            'slla_allowed_countries',
            __( 'Allowed Countries', 'simple-limit-login-attempts' ),
            array( $this, 'allowed_countries_callback' ),
            'slla-settings',
            'slla_geoblock_section'
        );
    }

    public function geoblock_section_callback() {
        echo '<p>' . __( 'Configure Geo-Blocking settings to restrict login attempts by country.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function ipstack_api_key_callback() {
        $value = get_option( 'slla_ipstack_api_key', '' );
        echo '<input type="text" name="slla_ipstack_api_key" id="slla_ipstack_api_key" value="' . esc_attr( $value ) . '" size="40" class="slla-input" />';
        echo '<p class="description">' . __( 'Enter your ipstack API key. Get one from <a href="https://ipstack.com/" target="_blank">ipstack.com</a>.', 'simple-limit-login-attempts' ) . '</p>';
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Enter your ipstack API key to enable Geo-Blocking.', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    public function allowed_countries_callback() {
        $value = get_option( 'slla_allowed_countries', array( 'PK' ) );
        $countries = array(
            'PK' => 'Pakistan',
            'US' => 'United States',
            'IN' => 'India',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'CN' => 'China',
            'JP' => 'Japan',
            // Add more countries as needed
        );
        echo '<select name="slla_allowed_countries[]" id="slla_allowed_countries" multiple size="5" class="slla-input">';
        foreach ( $countries as $code => $name ) {
            $selected = in_array( $code, $value ) ? 'selected' : '';
            echo "<option value='{$code}' {$selected}>{$name}</option>";
        }
        echo '</select>';
        echo '<p class="description">' . __( 'Select the countries allowed to login. Hold Ctrl (or Cmd) to select multiple countries.', 'simple-limit-login-attempts' ) . '</p>';
        echo '<span class="slla-tooltip"><span class="dashicons dashicons-info-outline"></span>';
        echo '<span class="slla-tooltip-text">' . __( 'Select the countries allowed to login. Hold Ctrl (or Cmd) to select multiple countries.', 'simple-limit-login-attempts' ) . '</span></span>';
    }

    // Sanitization callback for ipstack API key
    public function sanitize_ipstack_api_key( $value ) {
        // Trim whitespace and sanitize as a string
        $value = trim( $value );
        return sanitize_text_field( $value );
    }

    // Sanitization callback for allowed countries
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

    // Getter methods for templates
    public function get_logs() {
        return $this->logs;
    }

    public function get_insights() {
        return $this->insights;
    }

    public function get_stats() {
        return $this->stats;
    }

    public function get_notifications() {
        return $this->notifications;
    }
}
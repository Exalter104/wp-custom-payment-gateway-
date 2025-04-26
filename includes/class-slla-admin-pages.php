<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Admin_Pages
 * Handles admin page rendering for the Simple Limit Login Attempts plugin.
 */
class SLLA_Admin_Pages {
    private $admin;

    public function __construct($admin) {
        $this->admin = $admin;
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Limit Login Attempts', 'simple-limit-login-attempts' ),
            __( 'Limit Login Attempts', 'simple-limit-login-attempts' ),
            'manage_options',
            'slla-dashboard',
            array( $this, 'render_dashboard_page' ),
            'dashicons-shield',
            80
        );

        add_submenu_page(
            'slla-dashboard',
            __( 'Dashboard', 'simple-limit-login-attempts' ),
            __( 'Dashboard', 'simple-limit-login-attempts' ),
            'manage_options',
            'slla-dashboard',
            array( $this, 'render_dashboard_page' )
        );

        add_submenu_page(
            'slla-dashboard',
            __( 'Settings', 'simple-limit-login-attempts' ),
            __( 'Settings', 'simple-limit-login-attempts' ),
            'manage_options',
            'slla-settings',
            array( $this, 'render_settings_page' )
        );

        add_submenu_page(
            'slla-dashboard',
            __( 'Geo-Blocking', 'simple-limit-login-attempts' ),
            __( 'Geo-Blocking', 'simple-limit-login-attempts' ),
            'manage_options',
            'slla-geoblocking',
            array( $this, 'render_geoblocking_page' )
        );

        add_submenu_page(
            'slla-dashboard',
            __( 'Logs', 'simple-limit-login-attempts' ),
            __( 'Logs', 'simple-limit-login-attempts' ),
            'manage_options',
            'slla-logs',
            array( $this, 'render_logs_page' )
        );

        add_submenu_page(
            'slla-dashboard',
            __( 'Tools', 'simple-limit-login-attempts' ),
            __( 'Tools', 'simple-limit-login-attempts' ),
            'manage_options',
            'slla-tools',
            array( $this, 'render_tools_page' )
        );

        add_submenu_page(
            'slla-dashboard',
            __( 'Notifications', 'simple-limit-login-attempts' ),
            __( 'Notifications', 'simple-limit-login-attempts' ),
            'manage_options',
            'slla-notifications',
            array( $this, 'render_notifications_page' )
        );

        add_submenu_page(
            'slla-dashboard',
            __( 'Premium', 'simple-limit-login-attempts' ),
            __( 'Premium', 'simple-limit-login-attempts' ),
            'manage_options',
            'slla-premium',
            array( $this, 'render_premium_page' )
        );
    }

    public function render_dashboard_page() {
        $admin = $this->admin; // Ensure $admin is set for the template
        require_once SLLA_PLUGIN_DIR . 'templates/dashboard.php';
    }

    public function render_settings_page() {
        $admin = $this->admin;
        require_once SLLA_PLUGIN_DIR . 'templates/settings.php';
    }

    public function render_geoblocking_page() {
        $admin = $this->admin;
        require_once SLLA_PLUGIN_DIR . 'templates/geoblocking.php';
    }

    public function render_logs_page() {
        $admin = $this->admin;
        require_once SLLA_PLUGIN_DIR . 'templates/logs.php';
    }

    public function render_tools_page() {
        $admin = $this->admin;
        require_once SLLA_PLUGIN_DIR . 'templates/tools.php';
    }

    public function render_notifications_page() {
        $admin = $this->admin;
        require_once SLLA_PLUGIN_DIR . 'templates/notifications.php';
    }

    public function render_premium_page() {
        $admin = $this->admin;
        require_once SLLA_PLUGIN_DIR . 'templates/premium.php';
    }
}
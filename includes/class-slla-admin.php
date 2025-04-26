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
        // No need for register_all_settings since SLLA_Admin_Settings handles it
    }

    public function is_premium_active() {
        return get_option( 'slla_premium_activated', 0 ) == 1;
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
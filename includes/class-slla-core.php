<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SLLA_Core {
    private $logger;
    private $lockout;
    private $admin;

    public function __construct() {
        $this->load_dependencies();
        $this->init();
    }

    private function load_dependencies() {
        require_once SLLA_PLUGIN_DIR . 'includes/class-slla-logger.php';
        require_once SLLA_PLUGIN_DIR . 'includes/class-slla-lockout.php';
        require_once SLLA_PLUGIN_DIR . 'includes/class-slla-admin.php';

        $this->logger = new SLLA_Logger( $this );
        $this->lockout = new SLLA_Lockout( $this, $this->logger );
        $this->admin = new SLLA_Admin( $this );
    }

    public function init() {
        // Admin notice hook add kar rahe hain
        add_action( 'admin_notices', array( $this, 'show_admin_notice' ) );

        // Check karte hain ke plugin pehli dafa activate hua hai ya nahi
        if ( get_option( 'slla_plugin_activated_notice' ) !== 'yes' ) {
            $this->set_activation_notice_flag();
        }
    }

    public function set_activation_notice_flag() {
        update_option( 'slla_plugin_activated_notice', 'yes' );
    }

    public function show_admin_notice() {
        if ( is_admin() && get_option( 'slla_plugin_activated_notice' ) !== 'yes' ) {
            ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e( 'Simple Limit Login Attempts plugin is now active! Configure the settings to get started.', 'simple-limit-login-attempts' ); ?>
    </p>
</div>
<?php
        }
    }

    public function get_ip_address() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip_list = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            $ip = trim( $ip_list[0] );
        }
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            $ip = '0.0.0.1';
        }
        return sanitize_text_field( $ip );
    }
}
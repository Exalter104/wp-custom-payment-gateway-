<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SLLA_Admin {
    private $core;

    public function __construct( $core ) {
        $this->core = $core;
        $this->init();
    }

    public function init() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_notices', array( $this, 'display_attempts' ) );
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
    }

    public function render_dashboard_page() {
        ?>
<div class="wrap">
    <h1><?php _e( 'Limit Login Attempts Dashboard', 'simple-limit-login-attempts' ); ?></h1>

    <!-- Failed Attempts Section -->
    <h2><?php _e( 'Failed Attempts', 'simple-limit-login-attempts' ); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'IP Address', 'simple-limit-login-attempts' ); ?></th>
                <th><?php _e( 'Failed Attempts', 'simple-limit-login-attempts' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $this->display_failed_attempts_table(); ?>
        </tbody>
    </table>

    <!-- Locked IPs Section -->
    <h2><?php _e( 'Locked IPs', 'simple-limit-login-attempts' ); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'IP Address', 'simple-limit-login-attempts' ); ?></th>
                <th><?php _e( 'Lockout Time', 'simple-limit-login-attempts' ); ?></th>
                <th><?php _e( 'Remaining Time', 'simple-limit-login-attempts' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $this->display_locked_ips_table(); ?>
        </tbody>
    </table>

    <!-- Successful Logins Section -->
    <h2><?php _e( 'Successful Logins', 'simple-limit-login-attempts' ); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Username', 'simple-limit-login-attempts' ); ?></th>
                <th><?php _e( 'IP Address', 'simple-limit-login-attempts' ); ?></th>
                <th><?php _e( 'Login Time', 'simple-limit-login-attempts' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $this->display_successful_logins_table(); ?>
        </tbody>
    </table>

    <!-- Lockout Logs Section -->
    <h2><?php _e( 'Lockout Logs', 'simple-limit-login-attempts' ); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'IP Address', 'simple-limit-login-attempts' ); ?></th>
                <th><?php _e( 'Lockout Time', 'simple-limit-login-attempts' ); ?></th>
                <th><?php _e( 'Reason', 'simple-limit-login-attempts' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $this->display_lockout_logs_table(); ?>
        </tbody>
    </table>
</div>
<?php
    }

    public function display_attempts() {
        $ip = $this->core->get_ip_address();
        $transient_key = 'slla_attempts_' . md5( $ip );
        $attempts = get_transient( $transient_key );
        echo '<p>Failed attempts for IP ' . esc_html( $ip ) . ': ' . ( $attempts ? $attempts : 0 ) . '</p>';
    }

    private function display_failed_attempts_table() {
        global $wpdb;
        $transients = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_transient_slla_attempts_%'" );
        
        if ( ! empty( $transients ) ) {
            foreach ( $transients as $transient ) {
                $ip_hash = str_replace( '_transient_slla_attempts_', '', $transient->option_name );
                $attempts = $transient->option_value;
                ?>
<tr>
    <td><?php echo esc_html( $ip_hash ); // Ideally, we need to map hash to IP ?></td>
    <td><?php echo esc_html( $attempts ); ?></td>
</tr>
<?php
            }
        } else {
            ?>
<tr>
    <td colspan="2"><?php _e( 'No failed attempts found.', 'simple-limit-login-attempts' ); ?></td>
</tr>
<?php
        }
    }

    private function display_locked_ips_table() {
        global $wpdb;
        $transients = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_transient_slla_lockout_%'" );
        
        if ( ! empty( $transients ) ) {
            foreach ( $transients as $transient ) {
                $ip_hash = str_replace( '_transient_slla_lockout_', '', $transient->option_name );
                $timeout = $wpdb->get_var( $wpdb->prepare(
                    "SELECT option_value FROM $wpdb->options WHERE option_name = %s",
                    '_transient_timeout_slla_lockout_' . $ip_hash
                ) );
                $remaining_time = max( 0, $timeout - time() );
                $remaining_minutes = round( $remaining_time / 60 );
                $lockout_time = date( 'Y-m-d H:i:s', $timeout - (15 * MINUTE_IN_SECONDS) );
                ?>
<tr>
    <td><?php echo esc_html( $ip_hash ); // Ideally, map hash to IP ?></td>
    <td><?php echo esc_html( $lockout_time ); ?></td>
    <td><?php echo esc_html( $remaining_minutes ); ?> minutes</td>
</tr>
<?php
            }
        } else {
            ?>
<tr>
    <td colspan="3"><?php _e( 'No locked IPs found.', 'simple-limit-login-attempts' ); ?></td>
</tr>
<?php
        }
    }

    private function display_successful_logins_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $logs = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE type = %s ORDER BY time DESC LIMIT 50",
            'successful_login'
        ) );

        if ( ! empty( $logs ) ) {
            foreach ( $logs as $log ) {
                ?>
<tr>
    <td><?php echo esc_html( $log->username ); ?></td>
    <td><?php echo esc_html( $log->ip ); ?></td>
    <td><?php echo esc_html( $log->time ); ?></td>
</tr>
<?php
            }
        } else {
            ?>
<tr>
    <td colspan="3"><?php _e( 'No successful logins found.', 'simple-limit-login-attempts' ); ?></td>
</tr>
<?php
        }
    }

    private function display_lockout_logs_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $logs = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE type = %s ORDER BY time DESC LIMIT 50",
            'lockout'
        ) );

        if ( ! empty( $logs ) ) {
            foreach ( $logs as $log ) {
                ?>
<tr>
    <td><?php echo esc_html( $log->ip ); ?></td>
    <td><?php echo esc_html( $log->time ); ?></td>
    <td><?php echo esc_html( $log->reason ); ?></td>
</tr>
<?php
            }
        } else {
            ?>
<tr>
    <td colspan="3"><?php _e( 'No lockout logs found.', 'simple-limit-login-attempts' ); ?></td>
</tr>
<?php
        }
    }
}
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SLLA_Admin {
    public function init() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_notices', array( $this, 'display_attempts' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_admin_menu() {
        // Main Menu Page
        add_menu_page(
            __( 'Limit Login Attempts', 'simple-limit-login-attempts' ),
            __( 'Limit Login Attempts', 'simple-limit-login-attempts' ),
            'manage_options',
            'slla-dashboard',
            array( $this, 'render_dashboard_page' ),
            'dashicons-shield',
            80
        );

        // Submenu Pages
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
    }

    public function register_settings() {
        // General Settings
        register_setting( 'slla_settings_group', 'slla_max_attempts', array( 'default' => 5 ) );
        register_setting( 'slla_settings_group', 'slla_lockout_duration', array( 'default' => 15 ) );

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

        // Security Checklist Settings
        register_setting( 'slla_security_checklist_group', 'slla_enable_auto_updates' );
    }

    public function general_settings_callback() {
        echo '<p>' . __( 'Configure the general settings for login attempt limits.', 'simple-limit-login-attempts' ) . '</p>';
    }

    public function max_attempts_callback() {
        $max_attempts = get_option( 'slla_max_attempts', 5 );
        echo '<input type="number" name="slla_max_attempts" value="' . esc_attr( $max_attempts ) . '" min="1" class="slla-input" />';
    }

    public function lockout_duration_callback() {
        $lockout_duration = get_option( 'slla_lockout_duration', 15 );
        echo '<input type="number" name="slla_lockout_duration" value="' . esc_attr( $lockout_duration ) . '" min="1" class="slla-input" />';
    }

    public function render_dashboard_page() {
        ?>
<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Limit Login Attempts Dashboard', 'simple-limit-login-attempts' ); ?></h1>

    <!-- Quick Stats Section -->
    <div class="slla-card slla-quick-stats">
        <h2><?php _e( 'Quick Stats', 'simple-limit-login-attempts' ); ?></h2>
        <div class="slla-stats-grid">
            <div class="slla-stat">
                <span
                    class="slla-stat-label"><?php _e( 'Total Successful Logins', 'simple-limit-login-attempts' ); ?></span>
                <span class="slla-stat-value"><?php echo esc_html( $this->get_total_successful_logins() ); ?></span>
            </div>
            <div class="slla-stat">
                <span class="slla-stat-label"><?php _e( 'Total Lockouts', 'simple-limit-login-attempts' ); ?></span>
                <span class="slla-stat-value"><?php echo esc_html( $this->get_total_lockouts() ); ?></span>
            </div>
            <div class="slla-stat">
                <span
                    class="slla-stat-label"><?php _e( 'Total Failed Attempts', 'simple-limit-login-attempts' ); ?></span>
                <span class="slla-stat-value"><?php echo esc_html( $this->get_total_failed_attempts() ); ?></span>
            </div>
        </div>
    </div>

    <div class="slla-grid">
        <!-- Failed Attempts Section -->
        <div class="slla-card slla-failed-attempts">
            <h2><?php _e( 'Failed Attempts', 'simple-limit-login-attempts' ); ?></h2>
            <?php $this->display_failed_attempts(); ?>
        </div>

        <!-- Locked IPs Section -->
        <div class="slla-card slla-locked-ips">
            <h2><?php _e( 'Locked IPs', 'simple-limit-login-attempts' ); ?></h2>
            <table class="slla-dashboard-table">
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
        </div>

        <!-- Security Checklist Section -->
        <div class="slla-card slla-security-checklist">
            <h2><?php _e( 'Login Security Checklist', 'simple-limit-login-attempts' ); ?></h2>
            <form method="post" action="options.php">
                <?php
                        settings_fields( 'slla_security_checklist_group' );
                        ?>
                <ul>
                    <li>
                        <input type="checkbox" disabled>
                        <label><?php _e( 'Enable email notifications (Premium)', 'simple-limit-login-attempts' ); ?></label>
                    </li>
                    <li>
                        <input type="checkbox" disabled>
                        <label><?php _e( 'Implement strong password policies (Premium)', 'simple-limit-login-attempts' ); ?></label>
                    </li>
                    <li>
                        <input type="checkbox" name="slla_enable_auto_updates" value="1"
                            <?php checked( 1, get_option( 'slla_enable_auto_updates', 0 ) ); ?>>
                        <label><?php _e( 'Enable automatic updates', 'simple-limit-login-attempts' ); ?></label>
                    </li>
                </ul>
                <?php submit_button( __( 'Save Checklist', 'simple-limit-login-attempts' ), 'primary', 'submit', false ); ?>
            </form>
        </div>

        <!-- Upgrade to Premium Section -->
        <div class="slla-card slla-premium-promotion">
            <h2><?php _e( 'Upgrade to Premium', 'simple-limit-login-attempts' ); ?></h2>
            <p><?php _e( 'Unlock advanced features like email notifications, detailed analytics, and more!', 'simple-limit-login-attempts' ); ?>
            </p>
            <a href="#" class="slla-upgrade-btn"><?php _e( 'Get Started', 'simple-limit-login-attempts' ); ?></a>
        </div>
    </div>
</div>
<?php
    }

    public function render_settings_page() {
        ?>
<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Limit Login Attempts Settings', 'simple-limit-login-attempts' ); ?></h1>
    <div class="slla-card slla-settings">
        <form method="post" action="options.php">
            <?php
                    settings_fields( 'slla_settings_group' );
                    do_settings_sections( 'slla-settings' );
                    submit_button( __( 'Save Changes', 'simple-limit-login-attempts' ), 'primary slla-submit-btn', 'submit', false );
                    ?>
        </form>
    </div>
</div>
<?php
    }

    public function render_logs_page() {
        ?>
<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Limit Login Attempts Logs', 'simple-limit-login-attempts' ); ?></h1>
    <!-- Successful Logins Section -->
    <div class="slla-card slla-successful-logins">
        <h2><?php _e( 'Successful Logins', 'simple-limit-login-attempts' ); ?></h2>
        <table class="slla-dashboard-table">
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
        <a href="#" class="slla-view-more-btn"><?php _e( 'View More', 'simple-limit-login-attempts' ); ?></a>
    </div>
    <!-- Lockout Logs Section -->
    <div class="slla-card slla-lockout-logs">
        <h2><?php _e( 'Lockout Logs', 'simple-limit-login-attempts' ); ?></h2>
        <table class="slla-dashboard-table">
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
</div>
<?php
    }

    public function render_tools_page() {
        // Handle Clear Logs Action
        if ( isset( $_POST['slla_clear_logs'] ) ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'slla_logs';
            $wpdb->query( "TRUNCATE TABLE $table_name" );
            ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e( 'All logs have been cleared successfully.', 'simple-limit-login-attempts' ); ?></p>
</div>
<?php
        }

        ?>
<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Limit Login Attempts Tools', 'simple-limit-login-attempts' ); ?></h1>
    <div class="slla-card">
        <h2><?php _e( 'Tools', 'simple-limit-login-attempts' ); ?></h2>
        <p><?php _e( 'Tools to manage your login attempts and security settings.', 'simple-limit-login-attempts' ); ?>
        </p>
        <form method="post">
            <button type="submit" name="slla_clear_logs"
                class="button button-primary"><?php _e( 'Clear All Logs', 'simple-limit-login-attempts' ); ?></button>
        </form>
    </div>
</div>
<?php
    }

    private function get_total_successful_logins() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE type = %s", 'successful_login' ) );
    }

    private function get_total_lockouts() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE type = %s", 'lockout' ) );
    }

    private function get_total_failed_attempts() {
        global $wpdb;
        $transients = $wpdb->get_results( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE '_transient_slla_attempts_%'" );
        $total_attempts = 0;
        if ( ! empty( $transients ) ) {
            foreach ( $transients as $transient ) {
                $total_attempts += (int) $transient->option_value;
            }
        }
        return $total_attempts;
    }

    private function display_failed_attempts() {
        $total_attempts = $this->get_total_failed_attempts();
        $class = 'slla-failed-attempts-circle';
        if ( $total_attempts == 0 ) {
            $class .= ' slla-failed-attempts-low';
        } elseif ( $total_attempts <= 3 ) {
            $class .= ' slla-failed-attempts-medium';
        } else {
            $class .= ' slla-failed-attempts-high';
        }
        ?>
<div class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $total_attempts ); ?></div>
<div class="slla-failed-attempts-text">
    <?php printf( __( '%d failed attempts in the last 24 hours', 'simple-limit-login-attempts' ), $total_attempts ); ?>
</div>
<?php
    }

    private function display_locked_ips_table() {
        $locked_ips = array();
        $transients = get_transient( 'slla_locked_ips' );

        if ( ! empty( $transients ) ) {
            foreach ( $transients as $transient ) {
                $ip = str_replace( 'slla_lockout_', '', $transient->option_name );
                $lockout_time = get_transient( $transient->option_name );
                $remaining_time = $lockout_time - time();
                $remaining_minutes = max( 0, floor( $remaining_time / 60 ) );

                $locked_ips[] = array(
                    'ip' => $ip,
                    'lockout_time' => date( 'Y-m-d H:i:s', $lockout_time ),
                    'remaining_time' => $remaining_minutes . ' minutes',
                );
            }
        }

        if ( empty( $locked_ips ) ) {
            ?>
<tr>
    <td colspan="3"><?php _e( 'No locked IPs found.', 'simple-limit-login-attempts' ); ?></td>
</tr>
<?php
        } else {
            foreach ( $locked_ips as $locked_ip ) {
                ?>
<tr>
    <td><?php echo esc_html( $locked_ip['ip'] ); ?></td>
    <td><?php echo esc_html( $locked_ip['lockout_time'] ); ?></td>
    <td><?php echo esc_html( $locked_ip['remaining_time'] ); ?></td>
</tr>
<?php
            }
        }
    }

    private function display_successful_logins_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE type = %s ORDER BY time DESC LIMIT 5", 'successful_login' ) );

        if ( empty( $logs ) ) {
            ?>
<tr>
    <td colspan="3"><?php _e( 'No successful logins found.', 'simple-limit-login-attempts' ); ?></td>
</tr>
<?php
        } else {
            foreach ( $logs as $log ) {
                ?>
<tr>
    <td><?php echo esc_html( $log->username ); ?></td>
    <td><?php echo esc_html( $log->ip ); ?></td>
    <td><?php echo esc_html( $log->time ); ?></td>
</tr>
<?php
            }
        }
    }

    private function display_lockout_logs_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE type = %s ORDER BY time DESC LIMIT 5", 'lockout' ) );

        if ( empty( $logs ) ) {
            ?>
<tr>
    <td colspan="3"><?php _e( 'No lockout logs found.', 'simple-limit-login-attempts' ); ?></td>
</tr>
<?php
        } else {
            foreach ( $logs as $log ) {
                ?>
<tr>
    <td><?php echo esc_html( $log->ip ); ?></td>
    <td><?php echo esc_html( $log->time ); ?></td>
    <td><?php echo esc_html( $log->reason ); ?></td>
</tr>
<?php
            }
        }
    }

    public function display_attempts() {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';
        $transient_key = 'slla_attempts_' . md5( $ip );
        $attempts = get_transient( $transient_key );

        if ( $attempts ) {
            ?>
<div class="notice notice-error">
    <p><?php printf( __( 'Failed attempts for IP %s: %d', 'simple-limit-login-attempts' ), esc_html( $ip ), esc_html( $attempts ) ); ?>
    </p>
</div>
<?php
        }
    }
}
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Admin
 * Handles the admin functionality for the Simple Limit Login Attempts plugin.
 */
class SLLA_Admin {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->init();
        // Add AJAX action for Twilio credentials validation
        add_action( 'wp_ajax_slla_validate_twilio_credentials', array( $this, 'validate_twilio_credentials' ) );
    }

    /**
     * Initialize admin functionality.
     */
    public function init() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_notices', array( $this, 'display_attempts' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        // Add AJAX action for real-time notifications
        add_action( 'wp_ajax_slla_get_recent_failed_attempts', array( $this, 'ajax_get_recent_failed_attempts' ) );
    }
/**
     * Add admin menu pages.
     */
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

  /**
     * Render the notifications page.
     */
    public function render_notifications_page() {
        require_once SLLA_PLUGIN_DIR . 'templates/notifications.php';
    }
    /**
     * Register settings for the plugin.
     */
    public function register_settings() {
        // General Settings
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

        register_setting( 'slla_notifications_group', 'slla_twilio_account_sid', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ));

        register_setting( 'slla_notifications_group', 'slla_twilio_auth_token', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ));

        register_setting( 'slla_notifications_group', 'slla_twilio_phone_number', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ));

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

        // Security Checklist Settings
        register_setting( 'slla_security_checklist_group', 'slla_enable_auto_updates' );
        register_setting( 'slla_security_checklist_group', 'slla_email_notifications' ); // Premium
        register_setting( 'slla_security_checklist_group', 'slla_strong_password' ); // Premium

        // Premium Settings
        register_setting( 'slla_premium_group', 'slla_setup_code', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting( 'slla_premium_group', 'slla_block_countries', array(
            'sanitize_callback' => array( $this, 'sanitize_textarea' )
        ));

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

        // New Settings for Email and SMS Notifications
        register_setting( 'slla_notifications_group', 'slla_enable_email_notifications', array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ));

        register_setting( 'slla_notifications_group', 'slla_enable_sms_notifications', array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ));

        register_setting( 'slla_notifications_group', 'slla_admin_phone_number', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ));
    }

    /**
     * Callback for the general settings section.
     */
    public function general_settings_callback() {
        echo '<p class="description">' . __( 'Configure the general settings for login attempt limits.', 'simple-limit-login-attempts' ) . '</p>';
    }

    /**
     * Callback for the premium settings section.
     */
    public function premium_settings_callback() {
        echo '<p class="description">' . __( 'Configure premium settings for advanced features.', 'simple-limit-login-attempts' ) . '</p>';
    }

    /**
     * Callback for the max attempts field.
     */
    public function max_attempts_callback() {
        $max_attempts = get_option( 'slla_max_attempts', 5 );
        echo '<input type="number" name="slla_max_attempts" value="' . esc_attr( $max_attempts ) . '" min="1" class="slla-input" />';
    }

    /**
     * Callback for the lockout duration field.
     */
    public function lockout_duration_callback() {
        $lockout_duration = get_option( 'slla_lockout_duration', 15 );
        echo '<input type="number" name="slla_lockout_duration" value="' . esc_attr( $lockout_duration ) . '" min="1" class="slla-input" />';
    }

    /**
     * Callback for the safelist IPs field.
     */
    public function safelist_ips_callback() {
        $safelist_ips = get_option( 'slla_safelist_ips', '' );
        echo '<textarea name="slla_safelist_ips" rows="5" class="slla-input">' . esc_textarea( $safelist_ips ) . '</textarea>';
        echo '<p class="description">' . __( 'Enter one IP address per line to safelist.', 'simple-limit-login-attempts' ) . '</p>';
    }

    /**
     * Callback for the denylist IPs field.
     */
    public function denylist_ips_callback() {
        $denylist_ips = get_option( 'slla_denylist_ips', '' );
        echo '<textarea name="slla_denylist_ips" rows="5" class="slla-input">' . esc_textarea( $denylist_ips ) . '</textarea>';
        echo '<p class="description">' . __( 'Enter one IP address per line to denylist.', 'simple-limit-login-attempts' ) . '</p>';
    }

    /**
     * Callback for the custom error message field.
     */
    public function custom_error_message_callback() {
        $custom_error_message = get_option( 'slla_custom_error_message', '' );
        echo '<input type="text" name="slla_custom_error_message" value="' . esc_attr( $custom_error_message ) . '" class="slla-input" />';
        echo '<p class="description">' . __( 'Custom error message for failed login attempts.', 'simple-limit-login-attempts' ) . '</p>';
    }

    /**
     * Callback for the GDPR compliance field.
     */
    public function gdpr_compliance_callback() {
        $gdpr_compliance = get_option( 'slla_gdpr_compliance', 0 );
        echo '<input type="checkbox" name="slla_gdpr_compliance" value="1" ' . checked( 1, $gdpr_compliance, false ) . ' />';
        echo '<p class="description">' . __( 'Enable GDPR compliance (do not store sensitive data in logs).', 'simple-limit-login-attempts' ) . '</p>';
    }

    /**
     * Callback for the setup code field.
     */
    public function setup_code_callback() {
        $setup_code = get_option( 'slla_setup_code', '' );
        echo '<input type="text" name="slla_setup_code" value="' . esc_attr( $setup_code ) . '" class="slla-input" />';
        echo '<p class="description">' . __( 'Enter the setup code to activate premium features.', 'simple-limit-login-attempts' ) . '</p>';
    }

    /**
     * Callback for the block countries field.
     */
    public function block_countries_callback() {
        $block_countries = get_option( 'slla_block_countries', '' );
        echo '<textarea name="slla_block_countries" rows="5" class="slla-input">' . esc_textarea( $block_countries ) . '</textarea>';
        echo '<p class="description">' . __( 'Enter country codes (e.g., US, CN) to block, one per line. (Premium feature)', 'simple-limit-login-attempts' ) . '</p>';
    }

    /**
     * Sanitize the max attempts value.
     *
     * @param mixed $value The value to sanitize.
     * @return int The sanitized value.
     */
    public function sanitize_max_attempts( $value ) {
        $value = absint( $value );
        return max( 1, $value );
    }

    /**
     * Sanitize the lockout duration value.
     *
     * @param mixed $value The value to sanitize.
     * @return int The sanitized value.
     */
    public function sanitize_lockout_duration( $value ) {
        $value = absint( $value );
        return max( 1, $value );
    }

    /**
     * Sanitize textarea input.
     *
     * @param mixed $value The value to sanitize.
     * @return string The sanitized value.
     */
    public function sanitize_textarea( $value ) {
        return sanitize_textarea_field( $value );
    }

    /**
     * Render the dashboard page.
     */
    public function render_dashboard_page() {
        require_once SLLA_PLUGIN_DIR . 'templates/dashboard.php';
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        require_once SLLA_PLUGIN_DIR . 'templates/settings.php';
    }

    /**
     * Render the logs page.
     */
    public function render_logs_page() {
        require_once SLLA_PLUGIN_DIR . 'templates/logs.php';
    }

    /**
     * Render the tools page.
     */
    public function render_tools_page() {
        require_once SLLA_PLUGIN_DIR . 'templates/tools.php';
    }

    /**
     * Render the premium page.
     */
    public function render_premium_page() {
        require_once SLLA_PLUGIN_DIR . 'templates/premium.php';
    }

    /**
     * Check if premium features are active.
     *
     * @return bool True if premium is active, false otherwise.
     */
    public function is_premium_active() {
        return get_option( 'slla_premium_activated', 0 ) == 1;
    }

    /**
     * Get the total number of successful logins.
     *
     * @return int The total number of successful logins.
     */
    public function get_total_successful_logins() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE type = %s", 'successful_login' ) );
    }

    /**
     * Get the total number of lockouts.
     *
     * @return int The total number of lockouts.
     */
    public function get_total_lockouts() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE type = %s", 'lockout' ) );
    }

    /**
     * Get the total number of failed attempts in the last 24 hours.
     *
     * @return int The total number of failed attempts.
     */
    public function get_total_failed_attempts() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $since = current_time( 'mysql', 1 );
        $since = date( 'Y-m-d H:i:s', strtotime( $since . ' -24 hours' ) );
        return $wpdb->get_var( $wpdb->prepare( 
            "SELECT COUNT(*) FROM $table_name WHERE type = %s AND time >= %s", 
            'failed_attempt', 
            $since 
        ) );
    }

    /**
     * Display failed attempts in a visual format.
     */
    public function display_failed_attempts() {
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

    /**
     * Display AI insights based on failed attempts.
     */
    public function display_ai_insights() {
        $insights = $this->get_ai_insights();
        ?>
<p style="color: #e0e0e0;"><?php _e( 'Basic AI Analysis of Failed Attempts (Free)', 'simple-limit-login-attempts' ); ?>
</p>
<?php if ($insights['suspicious_ips'] === __( 'None detected', 'simple-limit-login-attempts' ) && $insights['common_time'] === __( 'Not enough data', 'simple-limit-login-attempts' )) : ?>
<p style="color: #666666;">
    <?php _e( 'No failed attempts recorded in the last 24 hours to analyze.', 'simple-limit-login-attempts' ); ?></p>
<?php else : ?>
<ul style="list-style: none; padding: 0;">
    <li style="margin-bottom: 10px;">
        <?php printf( __( 'Suspicious IPs: %s', 'simple-limit-login-attempts' ), esc_html( $insights['suspicious_ips'] ) ); ?>
    </li>
    <li style="margin-bottom: 10px;">
        <?php printf( __( 'Most Common Time of Attempts: %s', 'simple-limit-login-attempts' ), esc_html( $insights['common_time'] ) ); ?>
    </li>
    <li style="margin-bottom: 10px;">
        <?php printf( __( 'Recommendation: %s', 'simple-limit-login-attempts' ), esc_html( $insights['recommendation'] ) ); ?>
    </li>
</ul>
<?php endif; ?>
<p style="color: #b0b0b0; font-size: 12px;">
    <?php _e( 'Upgrade to Premium for advanced IP intelligence and country blocking.', 'simple-limit-login-attempts' ); ?>
</p>
<?php
    }

    /**
     * Get AI insights for failed attempts.
     *
     * @return array An array containing AI insights.
     */
    private function get_ai_insights() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $since = current_time( 'mysql', 1 );
        $since = date( 'Y-m-d H:i:s', strtotime( $since . ' -24 hours' ) );
    
        // Get failed attempts in the last 24 hours
        $failed_attempts = $wpdb->get_results( $wpdb->prepare( 
            "SELECT ip, time FROM $table_name WHERE type = %s AND time >= %s", 
            'failed_attempt', 
            $since 
        ) );
    
        $ip_counts = [];
        $hours = [];
        foreach ( $failed_attempts as $attempt ) {
            $ip = $attempt->ip;
            $hour = date( 'H', strtotime( $attempt->time ) );
            $ip_counts[$ip] = isset( $ip_counts[$ip] ) ? $ip_counts[$ip] + 1 : 1;
            $hours[$hour] = isset( $hours[$hour] ) ? $hours[$hour] + 1 : 1;
        }
    
        // Identify suspicious IPs (more than 3 attempts)
        $suspicious_ips = array_filter( $ip_counts, function( $count ) {
            return $count > 3;
        } );
        $suspicious_ips_list = implode( ', ', array_keys( $suspicious_ips ) );
        if ( empty( $suspicious_ips_list ) ) {
            $suspicious_ips_list = __( 'None detected', 'simple-limit-login-attempts' );
        }
    
        // Find the most common hour for failed attempts
        $common_time = __( 'Not enough data', 'simple-limit-login-attempts' );
        if ( !empty( $hours ) ) {
            $common_hour = array_search( max( $hours ), $hours );
            $common_time = $common_hour !== false ? sprintf( '%02d:00 - %02d:00', $common_hour, $common_hour + 1 ) : __( 'Not enough data', 'simple-limit-login-attempts' );
        }
    
        // Generate a simple recommendation
        $recommendation = __( 'Monitor your logs regularly.', 'simple-limit-login-attempts' );
        if ( count( $suspicious_ips ) > 0 ) {
            $recommendation = __( 'Consider adding these IPs to the denylist.', 'simple-limit-login-attempts' );
        }
    
        return [
            'suspicious_ips' => $suspicious_ips_list,
            'common_time' => $common_time,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * Display filtered logs in a table.
     *
     * @param string $date_filter The date filter to apply.
     * @param string $event_type The event type to filter.
     */
    public function display_filtered_logs_table( $date_filter, $event_type ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $query = "SELECT * FROM $table_name WHERE 1=1";
        $args = array();

        if ( $event_type ) {
            $query .= " AND type = %s";
            $args[] = $event_type;
        }

        if ( $date_filter ) {
            $since = current_time( 'mysql', 1 );
            if ( $date_filter === 'today' ) {
                $since = date( 'Y-m-d 00:00:00', strtotime( $since ) );
            } elseif ( $date_filter === 'last_7_days' ) {
                $since = date( 'Y-m-d H:i:s', strtotime( $since . ' -7 days' ) );
            } elseif ( $date_filter === 'last_30_days' ) {
                $since = date( 'Y-m-d H:i:s', strtotime( $since . ' -30 days' ) );
            }
            $query .= " AND time >= %s";
            $args[] = $since;
        }

        $query .= " ORDER BY time DESC LIMIT 50";
        $logs = $wpdb->get_results( $wpdb->prepare( $query, $args ) );

        if ( empty( $logs ) ) {
            ?>
<tr class="slla-log-row">
    <td colspan="5"><?php _e( 'No logs found.', 'simple-limit-login-attempts' ); ?></td>
</tr>
<?php
        } else {
            $row_index = 0;
            foreach ( $logs as $log ) {
                $row_index++;
                ?>
<tr class="slla-log-row" style="--row-delay: <?php echo $row_index; ?>;">
    <td><?php echo esc_html( $log->username ); ?></td>
    <td><?php echo esc_html( $log->ip ); ?></td>
    <td><?php echo esc_html( $log->time ); ?></td>
    <td class="slla-event-type slla-event-type-<?php echo esc_attr( $log->type ); ?>">
        <?php echo esc_html( $log->type ); ?>
    </td>
    <td><?php echo esc_html( $log->reason ); ?></td>
</tr>
<?php
            }
        }
    }

    /**
     * Display admin notices for failed attempts.
     */
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

    /**
     * Get recent failed login attempts for real-time notifications.
     *
     * @param int $limit Number of recent attempts to fetch.
     * @return array List of recent failed attempts.
     */
    public function get_recent_failed_attempts( $limit = 5 ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE type = %s ORDER BY time DESC LIMIT %d",
            'failed_attempt',
            $limit
        );
        return $wpdb->get_results( $query );
    }

    /**
     * Log a failed login attempt and send notifications if enabled.
     *
     * @param string $username The username attempting to log in.
     * @param string $ip The IP address of the user.
     */
    public function log_failed_attempt( $username, $ip ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $time = current_time( 'mysql' );

        // Insert the failed attempt into the logs
        $wpdb->insert(
            $table_name,
            array(
                'username' => $username,
                'ip'       => $ip,
                'time'     => $time,
                'type'     => 'failed_attempt',
                'reason'   => __( 'Invalid credentials', 'simple-limit-login-attempts' ),
            ),
            array( '%s', '%s', '%s', '%s', '%s' )
        );

        // Send notifications if premium user and notifications are enabled
        if ( $this->is_premium_active() ) {
            $this->send_notifications( $username, $ip, $time );
        }
    }

    /**
     * Send email and SMS notifications for a failed login attempt.
     *
     * @param string $username The username attempting to log in.
     * @param string $ip The IP address of the user.
     * @param string $time The timestamp of the attempt.
     */
    private function send_notifications( $username, $ip, $time ) {
        // Check if email notifications are enabled
        if ( get_option( 'slla_enable_email_notifications', 0 ) ) {
            $this->send_email_notification( $username, $ip, $time );
        }

        // Check if SMS notifications are enabled
        if ( get_option( 'slla_enable_sms_notifications', 0 ) ) {
            $this->send_sms_notification( $username, $ip, $time );
        }
    }

    /**
     * Send an email notification for a failed login attempt.
     *
     * @param string $username The username attempting to log in.
     * @param string $ip The IP address of the user.
     * @param string $time The timestamp of the attempt.
     */
    private function send_email_notification( $username, $ip, $time ) {
        $admin_email = get_option( 'admin_email' );
        $subject = __( 'Failed Login Attempt Notification', 'simple-limit-login-attempts' );
        $message = sprintf(
            __( "A failed login attempt was detected:\n\nUsername: %s\nIP: %s\nTime: %s", 'simple-limit-login-attempts' ),
            $username,
            $ip,
            $time
        );

        wp_mail( $admin_email, $subject, $message );
    }

    /**
     * Send an SMS notification for a failed login attempt.
     *
     * @param string $username The username attempting to log in.
     * @param string $ip The IP address of the user.
     * @param string $time The timestamp of the attempt.
     */
    private function send_sms_notification( $username, $ip, $time ) {
        $phone_number = get_option( 'slla_admin_phone_number', '' );
        $account_sid = get_option( 'slla_twilio_account_sid', '' );
        $auth_token = get_option( 'slla_twilio_auth_token', '' );
        $twilio_phone_number = get_option( 'slla_twilio_phone_number', '' );

        // Check if all required Twilio settings are provided
        if ( empty( $phone_number ) || empty( $account_sid ) || empty( $auth_token ) || empty( $twilio_phone_number ) ) {
            error_log( 'SMS Notification Failed: Missing Twilio credentials or admin phone number.' );
            return;
        }

        $message = sprintf(
            __( 'Failed login attempt detected: Username: %s, IP: %s, Time: %s', 'simple-limit-login-attempts' ),
            $username,
            $ip,
            $time
        );

        try {
            // Include Twilio PHP Library
            require_once SLLA_PLUGIN_DIR . 'vendor/autoload.php';

            $client = new Twilio\Rest\Client( $account_sid, $auth_token );

            $client->messages->create(
                $phone_number,
                array(
                    'from' => $twilio_phone_number,
                    'body' => $message,
                )
            );

            error_log( "SMS Notification Sent: To: $phone_number, Message: $message" );
        } catch ( Exception $e ) {
            error_log( "SMS Notification Failed: " . $e->getMessage() );
        }
    }

    /**
     * AJAX handler to fetch recent failed attempts.
     */
    public function ajax_get_recent_failed_attempts() {
        check_ajax_referer( 'slla_admin_nonce', 'nonce' );

        $recent_attempts = $this->get_recent_failed_attempts( 5 );
        $html = '';

        if ( empty( $recent_attempts ) ) {
            $html = '<p>' . __( 'No recent failed login attempts.', 'simple-limit-login-attempts' ) . '</p>';
        } else {
            $html .= '<ul class="slla-notification-list">';
            foreach ( $recent_attempts as $index => $attempt ) {
                $html .= '<li class="slla-notification-card">';
                $html .= '<div class="slla-notification-row">';
                $html .= '<span class="slla-notification-message">' . __( 'Failed Attempt', 'simple-limit-login-attempts' ) . '</span>';
                $html .= '<span class="slla-notification-username">' . esc_html( $attempt->username ) . '</span>';
                $html .= '</div>';
                $html .= '<div class="slla-notification-row">';
                $html .= '<span class="slla-notification-ip">' . esc_html( 'IP: ' . $attempt->ip ) . '</span>';
                $html .= '<span class="slla-notification-time">' . esc_html( $attempt->time ) . '</span>';
                $html .= '</div>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        }

        wp_send_json_success( array( 'html' => $html ) );
    }

    /**
     * Validate Twilio credentials via AJAX.
     */
    public function validate_twilio_credentials() {
        check_ajax_referer( 'slla_validate_twilio_nonce', 'nonce' );

        $account_sid = isset( $_POST['account_sid'] ) ? sanitize_text_field( $_POST['account_sid'] ) : '';
        $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( $_POST['auth_token'] ) : '';
        $phone_number = isset( $_POST['phone_number'] ) ? sanitize_text_field( $_POST['phone_number'] ) : '';

        if ( empty( $account_sid ) || empty( $auth_token ) || empty( $phone_number ) ) {
            wp_send_json_error( array( 'message' => __( 'Please fill in all Twilio credentials.', 'simple-limit-login-attempts' ) ) );
        }

        try {
            require_once SLLA_PLUGIN_DIR . 'vendor/autoload.php';

            $client = new Twilio\Rest\Client( $account_sid, $auth_token );

            // Test the credentials by fetching account details
            $client->account->fetch();

            // Validate the phone number by checking if it's a valid Twilio number
            $phone = $client->lookups->v1->phoneNumbers( $phone_number )->fetch();

            if ( $phone ) {
                wp_send_json_success();
            } else {
                wp_send_json_error( array( 'message' => __( 'Invalid Twilio phone number.', 'simple-limit-login-attempts' ) ) );
            }
        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => __( 'Invalid Twilio credentials.', 'simple-limit-login-attempts' ) ) );
        }
    }
}
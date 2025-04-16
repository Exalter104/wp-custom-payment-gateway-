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
            __( 'Premium', 'simple-limit-login-attempts' ),
            __( 'Premium', 'simple-limit-login-attempts' ),
            'manage_options',
            'slla-premium',
            array( $this, 'render_premium_page' )
        );
    }

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
    }

    public function general_settings_callback() {
        echo '<p class="description">' . __( 'Configure the general settings for login attempt limits.', 'simple-limit-login-attempts' ) . '</p>';
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

    private function render_submenu() {
        $pages = array(
            'slla-dashboard' => __( 'Dashboard', 'simple-limit-login-attempts' ),
            'slla-settings' => __( 'Settings', 'simple-limit-login-attempts' ),
            'slla-logs' => __( 'Logs', 'simple-limit-login-attempts' ),
            'slla-tools' => __( 'Tools', 'simple-limit-login-attempts' ),
            'slla-premium' => __( 'Premium', 'simple-limit-login-attempts' ),
        );
        $current_page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'slla-dashboard';
        ?>
<div class="slla-submenu">
    <?php foreach ( $pages as $slug => $title ) : ?>
    <a href="<?php echo admin_url( 'admin.php?page=' . $slug ); ?>"
        class="<?php echo $current_page === $slug ? 'current' : ''; ?>">
        <?php echo esc_html( $title ); ?>
    </a>
    <?php endforeach; ?>
</div>
<?php
    }

    public function render_dashboard_page() {
        ?>
<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Limit Login Attempts Dashboard', 'simple-limit-login-attempts' ); ?></h1>
    <?php $this->render_submenu(); ?>

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

        <!-- AI Insights Section (Free Feature) -->
        <div class="slla-card slla-ai-insights">
            <h2><?php _e( 'AI Insights (Free)', 'simple-limit-login-attempts' ); ?></h2>
            <?php $this->display_ai_insights(); ?>
        </div>

        <!-- Security Checklist Section -->
        <div class="slla-card slla-security-checklist">
            <h2><?php _e( 'Login Security Checklist', 'simple-limit-login-attempts' ); ?></h2>
            <form method="post" action="options.php">
                <?php settings_fields( 'slla_security_checklist_group' ); ?>
                <ul>
                    <li>
                        <input type="checkbox" name="slla_email_notifications" value="1"
                            <?php checked( 1, get_option( 'slla_email_notifications', 0 ) ); ?>
                            <?php echo $this->is_premium_active() ? '' : 'disabled'; ?>>
                        <label><?php _e( 'Enable email notifications (Premium)', 'simple-limit-login-attempts' ); ?></label>
                    </li>
                    <li>
                        <input type="checkbox" name="slla_strong_password" value="1"
                            <?php checked( 1, get_option( 'slla_strong_password', 0 ) ); ?>
                            <?php echo $this->is_premium_active() ? '' : 'disabled'; ?>>
                        <label><?php _e( 'Implement strong password policies (Premium)', 'simple-limit-login-attempts' ); ?></label>
                    </li>
                    <li>
                        <input type="checkbox" name="slla_enable_auto_updates" value="1"
                            <?php checked( 1, get_option( 'slla_enable_auto_updates', 0 ) ); ?>>
                        <label><?php _e( 'Enable automatic updates', 'simple-limit-login-attempts' ); ?></label>
                    </li>
                </ul>
                <?php submit_button( __( 'Save Checklist', 'simple-limit-login-attempts' ), 'primary slla-submit-btn', 'submit', false ); ?>
            </form>
        </div>

        <!-- Upgrade to Premium Section -->
        <div class="slla-card slla-premium-promotion">
            <h2><?php _e( 'Upgrade to Premium', 'simple-limit-login-attempts' ); ?></h2>
            <p><?php _e( 'Unlock advanced features like email notifications, IP intelligence, country blocking, and more!', 'simple-limit-login-attempts' ); ?>
            </p>
            <a href="<?php echo admin_url( 'admin.php?page=slla-premium' ); ?>"
                class="slla-upgrade-btn"><?php _e( 'Get Started', 'simple-limit-login-attempts' ); ?></a>
        </div>
    </div>
</div>
<?php
    }

    public function render_settings_page() {
        // Handle reset settings action
        if (isset($_POST['slla_reset_settings']) && check_admin_referer('slla_reset_settings_nonce', 'slla_reset_nonce')) {
            // Reset settings to default values
            update_option('slla_max_attempts', 5);
            update_option('slla_lockout_duration', 15);
            update_option('slla_safelist_ips', '');
            update_option('slla_denylist_ips', '');
            update_option('slla_custom_error_message', '');
            update_option('slla_gdpr_compliance', 0);
            ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e('Settings have been reset to default values.', 'simple-limit-login-attempts'); ?></p>
</div>
<?php
        }
        ?>
<div class="slla-dashboard-wrap">
    <h1><?php _e('Limit Login Attempts Settings', 'simple-limit-login-attempts'); ?></h1>
    <?php $this->render_submenu(); ?>
    <div class="slla-card slla-settings">
        <h2><?php _e('General Settings', 'simple-limit-login-attempts'); ?></h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('slla_settings_group');
            ?>
            <div class="slla-settings-grid">
                <!-- Maximum Failed Attempts -->
                <div class="slla-setting-item">
                    <label
                        for="slla_max_attempts"><?php _e('Maximum Failed Attempts', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Set the maximum number of failed login attempts before a user is locked out.', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <input type="number" name="slla_max_attempts" id="slla_max_attempts"
                        value="<?php echo esc_attr(get_option('slla_max_attempts', 5)); ?>" min="1"
                        class="slla-input" />
                </div>

                <!-- Lockout Duration -->
                <div class="slla-setting-item">
                    <label
                        for="slla_lockout_duration"><?php _e('Lockout Duration (minutes)', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Set the duration (in minutes) a user will be locked out after reaching the maximum failed attempts.', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <input type="number" name="slla_lockout_duration" id="slla_lockout_duration"
                        value="<?php echo esc_attr(get_option('slla_lockout_duration', 15)); ?>" min="1"
                        class="slla-input" />
                </div>

                <!-- Safelist IPs -->
                <div class="slla-setting-item">
                    <label for="slla_safelist_ips"><?php _e('Safelist IPs', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Enter IP addresses that should never be locked out. One IP per line.', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <textarea name="slla_safelist_ips" id="slla_safelist_ips" rows="5"
                        class="slla-input slla-ip-input"><?php echo esc_textarea(get_option('slla_safelist_ips', '')); ?></textarea>
                    <p class="description">
                        <?php _e('Enter one IP address per line to safelist.', 'simple-limit-login-attempts'); ?></p>
                    <div class="slla-ip-error" style="color: #ff6f61; display: none;">
                        <?php _e('Invalid IP address detected. Please enter valid IPs (e.g., 192.168.1.1).', 'simple-limit-login-attempts'); ?>
                    </div>
                </div>

                <!-- Denylist IPs -->
                <div class="slla-setting-item">
                    <label for="slla_denylist_ips"><?php _e('Denylist IPs', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Enter IP addresses that should always be blocked from logging in. One IP per line.', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <textarea name="slla_denylist_ips" id="slla_denylist_ips" rows="5"
                        class="slla-input slla-ip-input"><?php echo esc_textarea(get_option('slla_denylist_ips', '')); ?></textarea>
                    <p class="description">
                        <?php _e('Enter one IP address per line to denylist.', 'simple-limit-login-attempts'); ?></p>
                    <div class="slla-ip-error" style="color: #ff6f61; display: none;">
                        <?php _e('Invalid IP address detected. Please enter valid IPs (e.g., 192.168.1.1).', 'simple-limit-login-attempts'); ?>
                    </div>
                </div>

                <!-- Custom Error Message -->
                <div class="slla-setting-item">
                    <label
                        for="slla_custom_error_message"><?php _e('Custom Error Message', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Set a custom error message to display when a user exceeds the maximum failed login attempts.', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <input type="text" name="slla_custom_error_message" id="slla_custom_error_message"
                        value="<?php echo esc_attr(get_option('slla_custom_error_message', '')); ?>"
                        class="slla-input" />
                    <p class="description">
                        <?php _e('Custom error message for failed login attempts.', 'simple-limit-login-attempts'); ?>
                    </p>
                    <div class="slla-error-preview">
                        <strong><?php _e('Preview:', 'simple-limit-login-attempts'); ?></strong>
                        <span
                            id="slla_error_preview"><?php echo esc_html(get_option('slla_custom_error_message', '')); ?></span>
                    </div>
                </div>

                <!-- GDPR Compliance -->
                <div class="slla-setting-item">
                    <label for="slla_gdpr_compliance"><?php _e('GDPR Compliance', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Enable this to avoid storing sensitive data (e.g., IP addresses) in logs, ensuring GDPR compliance.', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <input type="checkbox" name="slla_gdpr_compliance" id="slla_gdpr_compliance" value="1"
                        <?php checked(1, get_option('slla_gdpr_compliance', 0)); ?> />
                    <p class="description">
                        <?php _e('Enable GDPR compliance (do not store sensitive data in logs).', 'simple-limit-login-attempts'); ?>
                    </p>
                </div>
            </div>
            <?php
            submit_button(__('Save Changes', 'simple-limit-login-attempts'), 'primary slla-submit-btn', 'submit', false);
            ?>
        </form>

        <!-- Reset Settings Form -->
        <form method="post" action="" style="margin-top: 20px;"
            onsubmit="return confirm('<?php _e('Are you sure you want to reset all settings to default values?', 'simple-limit-login-attempts'); ?>');">
            <?php wp_nonce_field('slla_reset_settings_nonce', 'slla_reset_nonce'); ?>
            <button type="submit" name="slla_reset_settings"
                class="button slla-reset-btn"><?php _e('Reset Settings to Default', 'simple-limit-login-attempts'); ?></button>
        </form>

        <!-- Upgrade to Premium Section -->
        <div class="slla-premium-footer">
            <h3><?php _e('Upgrade to Premium for Our Login Firewall', 'simple-limit-login-attempts'); ?></h3>
            <a href="<?php echo admin_url('admin.php?page=slla-premium'); ?>"
                class="slla-upgrade-btn"><?php _e('Try for FREE', 'simple-limit-login-attempts'); ?></a>
        </div>
    </div>
</div>
<?php
    }

    public function render_logs_page() {
        $date_filter = isset( $_GET['date_filter'] ) ? sanitize_text_field( $_GET['date_filter'] ) : '';
        $event_type = isset( $_GET['event_type'] ) ? sanitize_text_field( $_GET['event_type'] ) : '';

        // Handle clear logs action
        if ( isset( $_POST['slla_clear_logs'] ) && check_admin_referer( 'slla_clear_logs_nonce', 'slla_clear_logs_nonce' ) ) {
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
    <h1><?php _e( 'Limit Login Attempts Logs', 'simple-limit-login-attempts' ); ?></h1>
    <?php $this->render_submenu(); ?>
    <div class="slla-card slla-logs">
        <h2><?php _e( 'Logs', 'simple-limit-login-attempts' ); ?></h2>
        <form method="get" action="" class="slla-logs-filter-form">
            <input type="hidden" name="page" value="slla-logs" />
            <label for="date_filter"><?php _e( 'Date Range', 'simple-limit-login-attempts' ); ?></label>
            <select name="date_filter" id="date_filter" class="slla-input">
                <option value="" <?php selected( $date_filter, '' ); ?>>
                    <?php _e( 'All Time', 'simple-limit-login-attempts' ); ?></option>
                <option value="today" <?php selected( $date_filter, 'today' ); ?>>
                    <?php _e( 'Today', 'simple-limit-login-attempts' ); ?></option>
                <option value="last_7_days" <?php selected( $date_filter, 'last_7_days' ); ?>>
                    <?php _e( 'Last 7 Days', 'simple-limit-login-attempts' ); ?></option>
                <option value="last_30_days" <?php selected( $date_filter, 'last_30_days' ); ?>>
                    <?php _e( 'Last 30 Days', 'simple-limit-login-attempts' ); ?></option>
            </select>

            <label for="event_type"><?php _e( 'Event Type', 'simple-limit-login-attempts' ); ?></label>
            <select name="event_type" id="event_type" class="slla-input">
                <option value="" <?php selected( $event_type, '' ); ?>>
                    <?php _e( 'All Events', 'simple-limit-login-attempts' ); ?></option>
                <option value="successful_login" <?php selected( $event_type, 'successful_login' ); ?>>
                    <?php _e( 'Successful Logins', 'simple-limit-login-attempts' ); ?></option>
                <option value="lockout" <?php selected( $event_type, 'lockout' ); ?>>
                    <?php _e( 'Lockouts', 'simple-limit-login-attempts' ); ?></option>
                <option value="failed_attempt" <?php selected( $event_type, 'failed_attempt' ); ?>>
                    <?php _e( 'Failed Attempts', 'simple-limit-login-attempts' ); ?></option>
            </select>

            <button type="submit"
                class="button slla-filter-btn"><?php _e( 'Filter', 'simple-limit-login-attempts' ); ?></button>
        </form>

        <!-- Clear Logs Form -->
        <form method="post" action="" class="slla-clear-logs-form">
            <?php wp_nonce_field( 'slla_clear_logs_nonce', 'slla_clear_logs_nonce' ); ?>
            <button type="submit" name="slla_clear_logs"
                class="button slla-clear-btn"><?php _e( 'Clear All Logs', 'simple-limit-login-attempts' ); ?></button>
        </form>

        <div class="slla-logs-table-wrap">
            <table class="slla-dashboard-table">
                <thead>
                    <tr>
                        <th><?php _e( 'Username', 'simple-limit-login-attempts' ); ?></th>
                        <th><?php _e( 'IP Address', 'simple-limit-login-attempts' ); ?></th>
                        <th><?php _e( 'Time', 'simple-limit-login-attempts' ); ?></th>
                        <th><?php _e( 'Event Type', 'simple-limit-login-attempts' ); ?></th>
                        <th><?php _e( 'Details', 'simple-limit-login-attempts' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $this->display_filtered_logs_table( $date_filter, $event_type ); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
    }

    public function render_tools_page() {
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
    <?php $this->render_submenu(); ?>
    <div class="slla-card slla-tools">
        <h2><?php _e( 'Tools', 'simple-limit-login-attempts' ); ?></h2>
        <form method="post">
            <button type="submit" name="slla_clear_logs"
                class="button"><?php _e( 'Clear All Logs', 'simple-limit-login-attempts' ); ?></button>
        </form>
    </div>
</div>
<?php
    }

    public function render_premium_page() {
        if ( isset( $_POST['slla_setup_code'] ) && ! empty( $_POST['slla_setup_code'] ) ) {
            $entered_code = sanitize_text_field( $_POST['slla_setup_code'] );
            if ( $entered_code === 'PREMIUM123' ) {
                update_option( 'slla_premium_activated', 1 );
                ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e( 'Premium features activated successfully!', 'simple-limit-login-attempts' ); ?></p>
</div>
<?php
            } else {
                ?>
<div class="notice notice-error is-dismissible">
    <p><?php _e( 'Invalid setup code. Please try again.', 'simple-limit-login-attempts' ); ?></p>
</div>
<?php
            }
        }

        if ( $this->is_premium_active() ) {
            // Redirect to settings page for premium settings
            ?>
<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Premium Features Activated', 'simple-limit-login-attempts' ); ?></h1>
    <?php $this->render_submenu(); ?>
    <div class="slla-card">
        <h2><?php _e( 'Congratulations!', 'simple-limit-login-attempts' ); ?></h2>
        <p><?php _e( 'You have successfully activated the premium features. Configure your premium settings below.', 'simple-limit-login-attempts' ); ?>
        </p>
        <a href="<?php echo admin_url( 'admin.php?page=slla-settings' ); ?>"
            class="slla-upgrade-btn"><?php _e( 'Go to Settings', 'simple-limit-login-attempts' ); ?></a>
    </div>
</div>
<?php
        } else {
            ?>
<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Simple Limit Login Attempts Premium', 'simple-limit-login-attempts' ); ?></h1>
    <?php $this->render_submenu(); ?>

    <!-- Header Section -->
    <div class="slla-premium-header">
        <h2><?php _e( 'Full-Feature Login Protection with a Premium Plan', 'simple-limit-login-attempts' ); ?></h2>
        <p><?php _e( 'Get access to advanced security features to protect your site from brute force attacks.', 'simple-limit-login-attempts' ); ?>
        </p>
        <form method="post" action="" class="slla-premium-activation-form">
            <input type="text" name="slla_setup_code" id="slla_setup_code"
                placeholder="<?php _e( 'Enter Your Setup Code', 'simple-limit-login-attempts' ); ?>"
                class="slla-input" />
            <button type="submit"
                class="slla-upgrade-btn"><?php _e( 'Activate Premium', 'simple-limit-login-attempts' ); ?></button>
        </form>
        <p class="description">
            <?php _e( 'If you purchased a premium plan, check your email for the setup code (Setup Code: PREMIUM123).', 'simple-limit-login-attempts' ); ?>
        </p>
    </div>

    <!-- Why Upgrade Section -->
    <div class="slla-premium-why-upgrade">
        <h2><?php _e( 'Why Should I Consider Premium?', 'simple-limit-login-attempts' ); ?></h2>
        <p><?php _e( 'The premium version includes advanced features like email notifications, country blocking, and auto blocklist. With these tools, your site will be more secure and protected against malicious login attempts.', 'simple-limit-login-attempts' ); ?>
        </p>
    </div>

    <!-- Features Comparison -->
    <div class="slla-premium-features">
        <h2><?php _e( 'Features Comparison', 'simple-limit-login-attempts' ); ?></h2>
        <table class="slla-premium-table">
            <thead>
                <tr>
                    <th><?php _e( 'Feature', 'simple-limit-login-attempts' ); ?></th>
                    <th><?php _e( 'Free', 'simple-limit-login-attempts' ); ?></th>
                    <th><?php _e( 'Premium', 'simple-limit-login-attempts' ); ?></th>
                    <th><?php _e( 'Premium+', 'simple-limit-login-attempts' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong><?php _e( 'Limit Number of Retry Attempts', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Set the maximum number of failed login attempts before lockout.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Configurable Lockout Timing', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Set the duration a user is locked out after failed attempts.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Safelist/Denylist IPs', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Manually safelist or denylist IP addresses.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Performance Optimizer', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Enable GDPR compliance to avoid storing sensitive data.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Successful & Failed Login Logs', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Track successful logins and failed attempts.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Basic AI Insights', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Get basic AI analysis of failed login attempts.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Email Notifications', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Receive email alerts for lockouts and failed attempts.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-cross">✘</span></td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Strong Password Policies', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Enforce strong passwords for users.', 'simple-limit-login-attempts' ); ?></p>
                    </td>
                    <td><span class="slla-cross">✘</span></td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Block by Country', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Block login attempts from specific countries.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-cross">✘</span></td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Auto Blocklist', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Automatically add malicious IPs to the blocklist.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-cross">✘</span></td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Advanced AI Insights', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Detailed AI analysis with IP intelligence.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-cross">✘</span></td>
                    <td><span class="slla-cross">✘</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Access to Malicious IP Database', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Sync with a cloud database of known malicious IPs.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-cross">✘</span></td>
                    <td><span class="slla-cross">✘</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Unblock Admin with Ease', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Unblock admin users via email or secret code.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-cross">✘</span></td>
                    <td><span class="slla-cross">✘</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td>
                        <strong><?php _e( 'Premium Support', 'simple-limit-login-attempts' ); ?></strong>
                        <p class="description">
                            <?php _e( 'Receive 1-on-1 technical support via email.', 'simple-limit-login-attempts' ); ?>
                        </p>
                    </td>
                    <td><span class="slla-cross">✘</span></td>
                    <td><span class="slla-check">✔</span></td>
                    <td><span class="slla-check">✔</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button class="slla-installed-btn"
                            disabled><?php _e( 'Installed', 'simple-limit-login-attempts' ); ?></button>
                    </td>
                    <td>
                        <a href="#activate-premium"
                            class="slla-upgrade-btn"><?php _e( 'Get Started Free', 'simple-limit-login-attempts' ); ?></a>
                    </td>
                    <td>
                        <a href="#activate-premium"
                            class="slla-upgrade-btn"><?php _e( 'Get Started Free', 'simple-limit-login-attempts' ); ?></a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Premium Features Highlights -->
    <div class="slla-premium-highlights">
        <div class="slla-highlight-card">
            <span class="dashicons dashicons-shield-alt"></span>
            <h3><?php _e( 'Email Notifications', 'simple-limit-login-attempts' ); ?></h3>
            <p><?php _e( 'Get notified via email whenever a lockout or suspicious activity occurs.', 'simple-limit-login-attempts' ); ?>
            </p>
        </div>
        <div class="slla-highlight-card">
            <span class="dashicons dashicons-lock"></span>
            <h3><?php _e( 'Block by Country', 'simple-limit-login-attempts' ); ?></h3>
            <p><?php _e( 'Prevent login attempts from specific countries to enhance security.', 'simple-limit-login-attempts' ); ?>
            </p>
        </div>
        <div class="slla-highlight-card">
            <span class="dashicons dashicons-admin-network"></span>
            <h3><?php _e( 'Auto Blocklist', 'simple-limit-login-attempts' ); ?></h3>
            <p><?php _e( 'Automatically block IPs after repeated failed attempts.', 'simple-limit-login-attempts' ); ?>
            </p>
        </div>
        <div class="slla-highlight-card">
            <span class="dashicons dashicons-visibility"></span>
            <h3><?php _e( 'Advanced AI Insights', 'simple-limit-login-attempts' ); ?></h3>
            <p><?php _e( 'Leverage AI to identify and block malicious IPs with detailed analysis.', 'simple-limit-login-attempts' ); ?>
            </p>
        </div>
    </div>
</div>
<?php
        }
    }

    private function is_premium_active() {
        return get_option( 'slla_premium_activated', 0 ) == 1;
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
        $table_name = $wpdb->prefix . 'slla_logs';
        $since = current_time( 'mysql', 1 );
        $since = date( 'Y-m-d H:i:s', strtotime( $since . ' -24 hours' ) );
        return $wpdb->get_var( $wpdb->prepare( 
            "SELECT COUNT(*) FROM $table_name WHERE type = %s AND time >= %s", 
            'failed_attempt', 
            $since 
        ) );
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

    private function display_ai_insights() {
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
        if (!empty($hours)) { // Check if $hours is not empty
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
    private function display_filtered_logs_table( $date_filter, $event_type ) {
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
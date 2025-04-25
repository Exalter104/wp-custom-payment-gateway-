<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Handle reset settings action
if (isset($_POST['slla_reset_settings']) && check_admin_referer('slla_reset_settings_nonce', 'slla_reset_nonce')) {
    // Reset settings to default values
    update_option('slla_max_attempts', 5);
    update_option('slla_lockout_duration', 15);
    update_option('slla_safelist_ips', '');
    update_option('slla_denylist_ips', '');
    update_option('slla_custom_error_message', '');
    update_option('slla_gdpr_compliance', 0);
    update_option('slla_enable_auto_updates', 0);
    update_option('slla_email_notifications', 0);
    update_option('slla_strong_password', 0);
    update_option('slla_enable_2fa', 0);
    update_option('slla_ipstack_api_key', '');
    update_option('slla_allowed_countries', array('PK'));
    ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e('Settings have been reset to default values.', 'simple-limit-login-attempts'); ?></p>
</div>
<?php
}
?>

<div class="slla-dashboard-wrap">
    <h1><?php _e('Limit Login Attempts Settings', 'simple-limit-login-attempts'); ?></h1>
    <?php SLLA_Helpers::render_submenu(); ?>
    <div class="slla-card slla-settings">
        <h2><?php _e('General Settings', 'simple-limit-login-attempts'); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('slla_settings_group'); ?>
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
            <?php submit_button(__('Save Changes', 'simple-limit-login-attempts'), 'primary slla-submit-btn', 'submit', false); ?>
        </form>

        <!-- Login Security Checklist -->
        <h2><?php _e('Login Security Checklist', 'simple-limit-login-attempts'); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('slla_security_checklist_group'); ?>
            <div class="slla-settings-grid">
                <!-- Enable Auto Updates -->
                <div class="slla-setting-item">
                    <label
                        for="slla_enable_auto_updates"><?php _e('Enable Auto Updates', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Enable automatic updates for the plugin.', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <input type="checkbox" name="slla_enable_auto_updates" id="slla_enable_auto_updates" value="1"
                        <?php checked(1, get_option('slla_enable_auto_updates', 0)); ?> />
                    <p class="description">
                        <?php _e('Enable automatic updates for the plugin.', 'simple-limit-login-attempts'); ?>
                    </p>
                </div>

                <!-- Enable Email Notifications -->
                <div class="slla-setting-item">
                    <label
                        for="slla_email_notifications"><?php _e('Enable Email Notifications', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Receive email notifications for failed login attempts. (Premium feature)', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <?php
                    $admin = new SLLA_Admin();
                    if ( ! $admin->is_premium_active() ) {
                        ?>
                    <input type="checkbox" disabled />
                    <p class="description">
                        <?php _e('Enable email notifications for failed login attempts. (Premium feature)', 'simple-limit-login-attempts'); ?>
                    </p>
                    <?php
                    } else {
                        ?>
                    <input type="checkbox" name="slla_email_notifications" id="slla_email_notifications" value="1"
                        <?php checked(1, get_option('slla_email_notifications', 0)); ?> />
                    <p class="description">
                        <?php _e('Enable email notifications for failed login attempts.', 'simple-limit-login-attempts'); ?>
                    </p>
                    <?php
                    }
                    ?>
                </div>

                <!-- Enforce Strong Passwords -->
                <div class="slla-setting-item">
                    <label
                        for="slla_strong_password"><?php _e('Enforce Strong Passwords', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Force users to use strong passwords. (Premium feature)', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <?php
                    if ( ! $admin->is_premium_active() ) {
                        ?>
                    <input type="checkbox" disabled />
                    <p class="description">
                        <?php _e('Enforce strong passwords for users. (Premium feature)', 'simple-limit-login-attempts'); ?>
                    </p>
                    <?php
                    } else {
                        ?>
                    <input type="checkbox" name="slla_strong_password" id="slla_strong_password" value="1"
                        <?php checked(1, get_option('slla_strong_password', 0)); ?> />
                    <p class="description">
                        <?php _e('Enforce strong passwords for users.', 'simple-limit-login-attempts'); ?>
                    </p>
                    <?php
                    }
                    ?>
                </div>

                <!-- Enable Two-Factor Authentication -->
                <div class="slla-setting-item">
                    <label
                        for="slla_enable_2fa"><?php _e('Enable Two-Factor Authentication (2FA)', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Add an extra layer of security with 2FA via SMS. (Premium feature)', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <?php
                    if ( ! $admin->is_premium_active() ) {
                        ?>
                    <input type="checkbox" disabled />
                    <p class="description">
                        <?php _e('Enable Two-Factor Authentication via SMS. (Premium feature)', 'simple-limit-login-attempts'); ?>
                    </p>
                    <?php
                    } else {
                        ?>
                    <input type="checkbox" name="slla_enable_2fa" id="slla_enable_2fa" value="1"
                        <?php checked(1, get_option('slla_enable_2fa', 0)); ?> />
                    <p class="description">
                        <?php _e('Enable Two-Factor Authentication via SMS.', 'simple-limit-login-attempts'); ?>
                    </p>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <?php submit_button(__('Save Changes', 'simple-limit-login-attempts'), 'primary slla-submit-btn', 'submit', false); ?>
        </form>

        <!-- Geo-Blocking Settings -->
        <h2><?php _e('Geo-Blocking Settings', 'simple-limit-login-attempts'); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('slla_settings_group'); ?>
            <div class="slla-settings-grid">
                <!-- ipstack API Key -->
                <div class="slla-setting-item">
                    <label for="slla_ipstack_api_key"><?php _e('ipstack API Key', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Enter your ipstack API key to enable Geo-Blocking.', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <input type="text" name="slla_ipstack_api_key" id="slla_ipstack_api_key"
                        value="<?php echo esc_attr(get_option('slla_ipstack_api_key', '')); ?>" size="40"
                        class="slla-input" />
                    <p class="description">
                        <?php _e('Enter your ipstack API key. Get one from <a href="https://ipstack.com/" target="_blank">ipstack.com</a>.', 'simple-limit-login-attempts'); ?>
                    </p>
                </div>

                <!-- Allowed Countries -->
                <div class="slla-setting-item">
                    <label for="slla_allowed_countries"><?php _e('Allowed Countries', 'simple-limit-login-attempts'); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e('Select the countries allowed to login. Hold Ctrl (or Cmd) to select multiple countries.', 'simple-limit-login-attempts'); ?></span>
                        </span>
                    </label>
                    <?php
                    $value = get_option('slla_allowed_countries', array('PK'));
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
                    ?>
                    <select name="slla_allowed_countries[]" id="slla_allowed_countries" multiple size="5"
                        class="slla-input">
                        <?php
                        foreach ($countries as $code => $name) {
                            $selected = in_array($code, $value) ? 'selected' : '';
                            echo "<option value='{$code}' {$selected}>{$name}</option>";
                        }
                        ?>
                    </select>
                    <p class="description">
                        <?php _e('Select the countries allowed to login. Hold Ctrl (or Cmd) to select multiple countries.', 'simple-limit-login-attempts'); ?>
                    </p>
                </div>
            </div>
            <?php submit_button(__('Save Changes', 'simple-limit-login-attempts'), 'primary slla-submit-btn', 'submit', false); ?>
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
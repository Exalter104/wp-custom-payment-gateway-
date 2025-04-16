<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

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
    <?php SLLA_Helpers::render_submenu(); ?>
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
    <?php SLLA_Helpers::render_submenu(); ?>

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
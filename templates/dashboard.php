<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Limit Login Attempts Dashboard', 'simple-limit-login-attempts' ); ?></h1>
    <?php SLLA_Helpers::render_submenu(); ?>

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
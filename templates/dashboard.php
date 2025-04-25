<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Limit Login Attempts Dashboard', 'simple-limit-login-attempts' ); ?></h1>
    <?php SLLA_Helpers::render_submenu(); ?>

    <!-- Two-Column Layout -->
    <div class="slla-dashboard-container">
        <!-- Main Content (Left Column) -->
        <div class="slla-main-content">
            <!-- Quick Stats Section -->
            <div class="slla-card slla-quick-stats">
                <h2><?php _e( 'Quick Stats', 'simple-limit-login-attempts' ); ?></h2>
                <div class="slla-stats-grid">
                    <div class="slla-stat">
                        <span
                            class="slla-stat-label"><?php _e( 'Total Successful Logins', 'simple-limit-login-attempts' ); ?></span>
                        <span
                            class="slla-stat-value"><?php echo esc_html( $admin->get_stats()->get_total_successful_logins() ); ?></span>
                    </div>
                    <div class="slla-stat">
                        <span
                            class="slla-stat-label"><?php _e( 'Total Lockouts', 'simple-limit-login-attempts' ); ?></span>
                        <span
                            class="slla-stat-value"><?php echo esc_html( $admin->get_stats()->get_total_lockouts() ); ?></span>
                    </div>
                    <div class="slla-stat">
                        <span
                            class="slla-stat-label"><?php _e( 'Total Failed Attempts', 'simple-limit-login-attempts' ); ?></span>
                        <span
                            class="slla-stat-value"><?php echo esc_html( $admin->get_stats()->get_total_failed_attempts() ); ?></span>
                    </div>
                </div>
            </div>

            <div class="slla-grid">
                <!-- Failed Attempts Section -->
                <div class="slla-card slla-failed-attempts">
                    <h2><?php _e( 'Failed Attempts', 'simple-limit-login-attempts' ); ?></h2>
                    <?php $admin->get_logs()->display_failed_attempts(); ?>
                </div>

                <!-- AI Insights Section (Free Feature) -->
                <div class="slla-card slla-ai-ins slla-ai-insights">
                    <h2><?php _e( 'AI Insights (Free)', 'simple-limit-login-attempts' ); ?></h2>
                    <?php $admin->get_insights()->display_ai_insights(); ?>
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
                                    <?php echo $admin->is_premium_active() ? '' : 'disabled'; ?>>
                                <label><?php _e( 'Enable email notifications (Premium)', 'simple-limit-login-attempts' ); ?></label>
                            </li>
                            <li>
                                <input type="checkbox" name="slla_strong_password" value="1"
                                    <?php checked( 1, get_option( 'slla_strong_password', 0 ) ); ?>
                                    <?php echo $admin->is_premium_active() ? '' : 'disabled'; ?>>
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

                <!-- Blocked Attempts (Geo-Blocking) Section -->
                <div class="slla-card slla-blocked-attempts">
                    <h2><?php _e( 'Blocked Attempts (Geo-Blocking)', 'simple-limit-login-attempts' ); ?></h2>
                    <?php
                    $blocked_attempts = get_option( 'slla_blocked_attempts', array() );
                    if ( empty( $blocked_attempts ) ) {
                        echo '<p>' . __( 'No blocked attempts yet.', 'simple-limit-login-attempts' ) . '</p>';
                    } else {
                        echo '<ul>';
                        foreach ( $blocked_attempts as $attempt ) {
                            echo '<li>';
                            echo __( 'Blocked Attempt', 'simple-limit-login-attempts' ) . ' | ';
                            echo __( 'Username: ', 'simple-limit-login-attempts' ) . esc_html( $attempt['username'] ) . ' | ';
                            echo __( 'IP: ', 'simple-limit-login-attempts' ) . esc_html( $attempt['ip'] ) . ' | ';
                            echo __( 'Country: ', 'simple-limit-login-attempts' ) . esc_html( $attempt['country'] ) . ' | ';
                            echo __( 'Time: ', 'simple-limit-login-attempts' ) . esc_html( $attempt['time'] );
                            echo '</li>';
                        }
                        echo '</ul>';
                    }
                    ?>
                </div>

                <!-- Twilio SMS Usage Section -->
                <div class="slla-card slla-twilio-usage">
                    <h2><?php _e( 'Twilio SMS Usage', 'simple-limit-login-attempts' ); ?></h2>
                    <?php
                    $twilio_usage = SLLA_Twilio::get_twilio_usage();
                    if ( isset( $twilio_usage['error'] ) ) {
                        echo '<p style="color: red;">' . esc_html( $twilio_usage['error'] ) . '</p>';
                    } else {
                        echo '<p>' . sprintf( __( 'Used: %d messages', 'simple-limit-login-attempts' ), $twilio_usage['used'] ) . '</p>';
                        echo '<p>' . sprintf( __( 'Remaining: %d messages', 'simple-limit-login-attempts' ), $twilio_usage['remaining'] ) . '</p>';
                        echo '<p>' . sprintf( __( 'Daily Limit: %d messages', 'simple-limit-login-attempts' ), $twilio_usage['limit'] ) . '</p>';
                        if ( $twilio_usage['remaining'] == 0 ) {
                            echo '<p style="color: red;">' . __( 'SMS limit exceeded. 2FA codes are being sent via email.', 'simple-limit-login-attempts' ) . '</p>';
                        }
                    }
                    ?>
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

        <!-- Sidebar (Right Column) -->
        <div class="slla-sidebar">
            <!-- Real-Time Notifications -->
            <div class="slla-card slla-real-time-notifications">
                <h2><?php _e( 'Real-Time Notifications', 'simple-limit-login-attempts' ); ?></h2>
                <?php
                $recent_attempts = $admin->get_logs()->get_recent_failed_attempts( 5 );
                if ( empty( $recent_attempts ) ) {
                    ?>
                <p><?php _e( 'No recent failed login attempts.', 'simple-limit-login-attempts' ); ?></p>
                <?php
                } else {
                    ?>
                <ul class="slla-notification-list">
                    <?php foreach ( $recent_attempts as $index => $attempt ) : ?>
                    <li class="slla-notification-card">
                        <div class="slla-notification-row">
                            <span
                                class="slla-notification-message"><?php _e( 'Failed Attempt', 'simple-limit-login-attempts' ); ?></span>
                            <span
                                class="slla-notification-username"><?php echo esc_html( $attempt->username ); ?></span>
                            <span class="slla-notification-ip"><?php echo esc_html( 'IP: ' . $attempt->ip ); ?></span>
                            <span class="slla-notification-time"><?php echo esc_html( $attempt->time ); ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php
                }

                if ( ! $admin->is_premium_active() ) {
                    ?>
                <p class="slla-premium-notice">
                    <?php _e( 'Upgrade to Premium for email and SMS notifications!', 'simple-limit-login-attempts' ); ?>
                </p>
                <a href="<?php echo admin_url( 'admin.php?page=slla-premium' ); ?>" class="slla-upgrade-btn">
                    <?php _e( 'Upgrade Now', 'simple-limit-login-attempts' ); ?>
                </a>
                </p>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
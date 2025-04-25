<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Admin_Insights
 * Handles AI insights-related functionality for the Simple Limit Login Attempts plugin.
 */
class SLLA_Admin_Insights {
    public function display_ai_insights() {
        $insights = $this->get_ai_insights();
        ?>
<div class="slla-insights-card"
    style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-top: 20px;">
    <h3 style="color: #333; margin-top: 0;">
        <?php _e( 'Basic AI Analysis of Failed Attempts (Free)', 'simple-limit-login-attempts' ); ?></h3>
    <?php if ($insights['total_attempts'] === 0) : ?>
    <p style="color: #666666;">
        <?php _e( 'No failed attempts recorded in the last 24 hours to analyze.', 'simple-limit-login-attempts' ); ?>
    </p>
    <?php else : ?>
    <ul style="list-style: none; padding: 0;">
        <li style="margin-bottom: 10px;">
            <strong><?php _e( 'Total Failed Attempts:', 'simple-limit-login-attempts' ); ?></strong>
            <?php echo esc_html( $insights['total_attempts'] ); ?>
        </li>
        <li style="margin-bottom: 10px;">
            <strong><?php _e( 'Suspicious IPs:', 'simple-limit-login-attempts' ); ?></strong>
            <?php echo esc_html( $insights['suspicious_ips'] ); ?>
        </li>
        <li style="margin-bottom: 10px;">
            <strong><?php _e( 'Most Targeted Username:', 'simple-limit-login-attempts' ); ?></strong>
            <?php echo esc_html( $insights['most_targeted_username'] ); ?>
        </li>
        <li style="margin-bottom: 10px;">
            <strong><?php _e( 'Most Common Time of Attempts:', 'simple-limit-login-attempts' ); ?></strong>
            <?php echo esc_html( $insights['common_time'] ); ?>
        </li>
        <li style="margin-bottom: 10px;">
            <strong><?php _e( 'Recommendation:', 'simple-limit-login-attempts' ); ?></strong>
            <?php echo esc_html( $insights['recommendation'] ); ?>
        </li>
    </ul>
    <?php endif; ?>
    <p style="color: #b0b0b0; font-size: 12px;">
        <?php _e( 'Upgrade to Premium for advanced IP intelligence and country blocking.', 'simple-limit-login-attempts' ); ?>
    </p>
</div>
<?php
    }

    private function get_ai_insights() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';

        // Get the timezone offset for the site
        $timezone_offset = get_option('gmt_offset');
        $since = current_time('mysql'); // Local time based on WordPress timezone settings
        $since = date('Y-m-d H:i:s', strtotime($since . ' -24 hours'));

        // Debug: Log the $since value to check the time range
        error_log('AI Insights - Since Time: ' . $since);

        // Get failed attempts in the last 24 hours (fixed column names: ip_address to ip, event_type to type)
        $failed_attempts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ip, username, time FROM $table_name WHERE type = %s AND time >= %s",
                'failed_attempt',
                $since
            )
        );

        // Debug: Log the number of failed attempts fetched
        error_log('AI Insights - Failed Attempts Count: ' . count($failed_attempts));

        $ip_counts = [];
        $username_counts = [];
        $hours = [];
        $total_attempts = count($failed_attempts);

        // Analyze failed attempts
        foreach ($failed_attempts as $attempt) {
            $ip = sanitize_text_field($attempt->ip); // Fixed column name
            $username = sanitize_text_field($attempt->username);
            $time = strtotime($attempt->time);

            // Adjust time to site's timezone
            $time = $time + ($timezone_offset * 3600);
            $hour = gmdate('H', $time);

            // Count IPs
            $ip_counts[$ip] = isset($ip_counts[$ip]) ? $ip_counts[$ip] + 1 : 1;

            // Count usernames
            $username_counts[$username] = isset($username_counts[$username]) ? $username_counts[$username] + 1 : 1;

            // Count hours
            $hours[$hour] = isset($hours[$hour]) ? $hours[$hour] + 1 : 1;
        }

        // Total failed attempts
        if ($total_attempts === 0) {
            return [
                'total_attempts' => 0,
                'suspicious_ips' => __( 'None detected', 'simple-limit-login-attempts' ),
                'most_targeted_username' => __( 'None detected', 'simple-limit-login-attempts' ),
                'common_time' => __( 'Not enough data', 'simple-limit-login-attempts' ),
                'recommendation' => __( 'Monitor your logs regularly.', 'simple-limit-login-attempts' ),
            ];
        }

        // Identify suspicious IPs (more than 3 attempts)
        $suspicious_ips = array_filter($ip_counts, function($count) {
            return $count > 3;
        });
        $suspicious_ips_list = implode(', ', array_keys($suspicious_ips));
        if (empty($suspicious_ips_list)) {
            $suspicious_ips_list = __( 'None detected', 'simple-limit-login-attempts' );
        }

        // Identify most targeted username
        $most_targeted_username = array_search(max($username_counts), $username_counts);
        if ($most_targeted_username === false || empty($most_targeted_username)) {
            $most_targeted_username = __( 'None detected', 'simple-limit-login-attempts' );
        }

        // Find the most common hour for failed attempts
        $common_time = __( 'Not enough data', 'simple-limit-login-attempts' );
        if (!empty($hours)) {
            $common_hour = array_search(max($hours), $hours);
            if ($common_hour !== false) {
                $common_time = sprintf('%02d:00 - %02d:00', $common_hour, $common_hour + 1);
            }
        }

        // Generate a simple recommendation
        $recommendation = __( 'Monitor your logs regularly.', 'simple-limit-login-attempts' );
        if (count($suspicious_ips) > 0) {
            $recommendation = __( 'Consider adding these IPs to the denylist.', 'simple-limit-login-attempts' );
        } elseif ($total_attempts > 10) {
            $recommendation = __( 'High number of failed attempts detected. Consider enabling 2FA.', 'simple-limit-login-attempts' );
        }

        return [
            'total_attempts' => $total_attempts,
            'suspicious_ips' => $suspicious_ips_list,
            'most_targeted_username' => $most_targeted_username,
            'common_time' => $common_time,
            'recommendation' => $recommendation,
        ];
    }
}
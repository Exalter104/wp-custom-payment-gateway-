<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Admin_Logs
 * Handles logs-related functionality for the Simple Limit Login Attempts plugin.
 */
class SLLA_Admin_Logs {
    private $admin;

    public function __construct($admin) {
        $this->admin = $admin;
        add_action( 'admin_notices', array( $this, 'display_attempts' ) );
    }

    public function display_filtered_logs_table( $date_filter, $event_type ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $query = "SELECT * FROM {$table_name} WHERE 1=1"; // Use curly braces for safe interpolation
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

    public function get_recent_failed_attempts( $limit = 5 ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slla_logs';
        $query = "SELECT * FROM {$table_name} WHERE type = %s ORDER BY time DESC LIMIT %d";
        $query = $wpdb->prepare( $query, 'failed_attempt', $limit );
        return $wpdb->get_results( $query );
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

    public function display_failed_attempts() {
        $total_attempts = $this->admin->get_stats()->get_total_failed_attempts();
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
}
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

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
    <?php SLLA_Helpers::render_submenu(); ?>
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
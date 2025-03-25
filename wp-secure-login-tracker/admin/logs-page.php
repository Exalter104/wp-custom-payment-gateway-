<?php
// Display Logs Page Function
function wslt_display_logs_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wslt_login_logs';

    // Clear logs functionality
    if ( isset($_POST['clear_logs']) && check_admin_referer('clear_logs_nonce', 'clear_logs_nonce_field') ) {
        include_once plugin_dir_path(__FILE__) . '../includes/clear-logs.php';
    }

    // Get all user roles
    global $wp_roles;
    $roles = $wp_roles->roles;

    // Role filter
    $selected_role = isset($_GET['role_filter']) ? sanitize_text_field($_GET['role_filter']) : '';

    $query = "SELECT l.*, u.ID, u.user_login FROM $table_name l 
              LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID";

    if ($selected_role) {
        $query .= " LEFT JOIN {$wpdb->usermeta} um ON um.user_id = u.ID 
                    AND um.meta_key = '{$wpdb->prefix}capabilities' 
                    WHERE um.meta_value LIKE '%$selected_role%'";
    }

    $query .= " ORDER BY l.login_time DESC LIMIT 50";
    $logs = $wpdb->get_results($query);
    ?>

<div class="wslt-container">
    <h2>Login Logs</h2>

    <!-- Filter Form -->
    <form class="wslt-filter-form" method="get">
        <input type="hidden" name="page" value="wslt-logs" />
        <label for="role_filter">Filter by Roles:</label>
        <select name="role_filter" id="role_filter">
            <option value="">All Roles</option>
            <?php foreach ( $roles as $role_key => $role ): ?>
            <option value="<?php echo esc_attr($role_key); ?>" <?php selected($selected_role, $role_key); ?>>
                <?php echo esc_html($role['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Filter">
    </form>

    <!-- Buttons -->

    <div class="wslt-buttons">
        <!-- Export Logs -->
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="wslt_export_logs">
            <?php wp_nonce_field('wslt_export_logs_nonce', 'wslt_export_logs_nonce_field'); ?>
            <input type="submit" value="Export Logs CSV">
        </form>

        <!-- Clear Logs -->
        <form method="post" onsubmit="return confirm('Are you sure you want to clear all logs?');">
            <?php wp_nonce_field('clear_logs_nonce', 'clear_logs_nonce_field'); ?>
            <input type="submit" name="clear_logs" value="Clear Logs">
        </form>
    </div>
    <!-- Logs Table -->
    <table class="wslt-log-table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Login Time</th>
                <th>IP Address</th>
                <th>User Agent</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $logs ) : ?>
            <?php foreach ( $logs as $log ) : 
                        $user = get_userdata($log->user_id);
                        $role = $user ? implode(', ', $user->roles) : 'N/A';
                    ?>
            <tr>
                <td><?php echo esc_html($log->user_id); ?></td>
                <td><?php echo esc_html($log->user_login); ?></td>
                <td><?php echo esc_html($role); ?></td>
                <td><?php echo esc_html($log->login_time); ?></td>
                <td><?php echo esc_html($log->ip_address); ?></td>
                <td><?php echo esc_html($log->user_agent); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php else : ?>
            <tr>
                <td colspan="6">No logs found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php
}
?>
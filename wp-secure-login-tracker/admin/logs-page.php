<?php
// Function to display the Login Logs page
function wslt_display_logs_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wslt_login_logs';

    // Handle clear logs functionality
    if (isset($_POST['clear_logs']) && check_admin_referer('clear_logs_nonce', 'clear_logs_nonce_field')) {
        include_once plugin_dir_path(__FILE__) . '../includes/clear-logs.php';
    }

    // Retrieve all user roles
    global $wp_roles;
    $roles = $wp_roles->roles;

    // Apply role filter if selected
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
    <h2>Exalters Login Logs</h2>

    <!-- Role Filter Form -->
    <form class="wslt-filter-form" method="get">
        <input type="hidden" name="page" value="wslt-logs" />
        <label for="role_filter">Filter by Roles:</label>
        <select name="role_filter" id="role_filter">
            <option value="">All Roles</option>
            <?php foreach ($roles as $role_key => $role) : ?>
            <option value="<?php echo esc_attr($role_key); ?>" <?php selected($selected_role, $role_key); ?>>
                <?php echo esc_html($role['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Filter">
        <!-- Search Bar -->
        <input style="padding: 4px;" type="text" id="searchInput" placeholder="Search by Username or IP"
            class="regular-text">
        <button style="padding: 3px; background-color: #416937;
" type="button" id="searchButton" class="button button-primary">Search</button>

    </form>


    <!-- Logs Table -->
    <table class="wslt-log-table" id="logTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">User ID</th>
                <th onclick="sortTable(1)">Username</th>
                <th onclick="sortTable(2)">Role</th>
                <th onclick="sortTable(3)">Login Time</th>
                <th onclick="sortTable(4)">IP Address</th>
                <th onclick="sortTable(5)">User Agent</th>
            </tr>
        </thead>
        <tbody id="logTableBody">
            <?php if ($logs) : ?>
            <?php foreach ($logs as $log) :
                        $user = get_userdata($log->user_id);
                        $role = ($user && !empty($user->roles)) ? esc_html(implode(', ', $user->roles)) : 'N/A';
                    ?>
            <tr>
                <td><?php echo esc_html($log->user_id); ?></td>
                <td><?php echo esc_html($log->user_login); ?></td>
                <td><span
                        class="role-badge role-<?php echo esc_attr(strtolower($role)); ?>"><?php echo esc_html($role); ?></span>
                </td>
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

    <!-- Export and Clear Logs Buttons -->
    <div class="wslt-buttons" style="margin-top: 20px;">
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="wslt_export_logs">
            <?php wp_nonce_field('wslt_export_logs_nonce', 'wslt_export_logs_nonce_field'); ?>
            <input type="submit" value="Export Logs CSV">
        </form>
        <form method="post" onsubmit="return confirm('Are you sure you want to clear all logs?');">
            <?php wp_nonce_field('clear_logs_nonce', 'clear_logs_nonce_field'); ?>
            <input type="submit" name="clear_logs" value="Clear Logs">
        </form>
    </div>
</div>

<script>
// Search Functionality
document.getElementById('searchButton').addEventListener('click', function() {
    let searchValue = document.getElementById('searchInput').value.toLowerCase();
    let rows = document.querySelectorAll('#logTableBody tr');
    rows.forEach(row => {
        let username = row.cells[1].textContent.toLowerCase();
        let ip = row.cells[4].textContent.toLowerCase();
        row.style.display = (username.includes(searchValue) || ip.includes(searchValue)) ? '' : 'none';
    });
});

// Sort Table Function
function sortTable(n) {
    let table = document.getElementById("logTable");
    let rows = Array.from(table.rows).slice(1);
    let sortedRows = rows.sort((a, b) => a.cells[n].textContent.localeCompare(b.cells[n].textContent));
    sortedRows.forEach(row => table.appendChild(row));
}
</script>

<style>
/* Styling for role badges */
.role-badge {
    padding: 5px 10px;
    border-radius: 5px;
    color: white;
    font-weight: bold;
}

.role-administrator {
    background-color: #416937;

}

.role-editor {
    background-color: blue;
}

.role-author {
    background-color: green;
}

.role-contributor {
    background-color: orange;
}

.role-subscriber {
    background-color: darkslateblue;
}
</style>
<?php } ?>
<?php
global $wpdb;
$table = $wpdb->prefix . 'wslt_login_logs';

// Delete all logs
$wpdb->query("DELETE FROM $table");

// Add admin notice
add_action('admin_notices', function() {
    echo '<div class="notice notice-success is-dismissible"><p>All login logs cleared successfully.</p></div>';
});
?>
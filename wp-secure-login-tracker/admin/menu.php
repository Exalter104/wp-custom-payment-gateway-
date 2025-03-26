<?php



// Admin menu aur submenus add karne ke liye. (Example: Login Logs, Settings).
add_action('admin_menu', 'wslt_add_admin_menu');

function wslt_add_admin_menu() {
    add_menu_page(
        ' Login Logs',
        'Login Logs',
        'manage_options',
        'wslt-logs',
        'wslt_display_logs_page',
        'dashicons-shield', // Nice security-related icon
        80
    );
}
?>
<?php
add_action('wp_login', 'log_login_activity', 10, 2);

function log_login_activity($user_login, $user) {
    global $wpdb;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $login_time = current_time('mysql');

    $wpdb->insert(
        $wpdb->prefix . 'wslt_login_logs',
        array(
            'user_id' => $user->ID,
            'user_login' => $user_login,
            'login_time' => $login_time,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent
        )
    );
}
?>
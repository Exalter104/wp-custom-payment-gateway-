<?php
function wslt_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wslt_login_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        user_login varchar(60) NOT NULL,
        login_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        ip_address varchar(100) NOT NULL,
        user_agent text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
?>
<?php
defined('ABSPATH') || exit;

function exarth_create_payment_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_payments';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        amount varchar(255) NOT NULL,
        phone varchar(255) NOT NULL,
        gateway varchar(255) NOT NULL,
        status varchar(50) NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    
    // Add version option for future updates
    add_option('exarth_payment_table_version', '1.0');
}
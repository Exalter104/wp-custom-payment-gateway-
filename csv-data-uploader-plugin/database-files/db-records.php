<?php
// dynamic table creation if plugin is activated

function csv_data_uploader_table(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'exarth_records';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(120) DEFAULT NULL,
        email VARCHAR(50) DEFAULT NULL,
        picture VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
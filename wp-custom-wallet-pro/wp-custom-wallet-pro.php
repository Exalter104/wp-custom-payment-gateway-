<?php
/*
Plugin Name: WP Custom Wallet Pro
Plugin URI: https://yourwebsite.com/
Description: A custom wallet system for Easypaisa , JazzCash for secure transections.
Version: 1.0
Author: Exarth
*/


defined('ABSPATH') || exit;
register_activation_hook(__FILE__, 'exarth_create_payment_table');
function exarth_create_payment_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_payments';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        amount varchar(255) NOT NULL,
        phone varchar(20) NOT NULL,
        gateway varchar(50) NOT NULL,
        status varchar(20) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
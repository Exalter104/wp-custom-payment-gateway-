<?php
//Plugin ka main file. Isme basic hooks, plugin info, activation/deactivation ka code aayega.
/**
 * Plugin Name: CSV Data Uploader
 * Plugin URI:  https://exarth.com
 * Description: CSV data uploader with advanced features.
 * Version: 1.0.0
 * Author: Exarth
 * Author URI: https://exarth.com
 * License: GPL v2 or later
 * Text Domain: csv-data-uploader
 */

if (!defined('ABSPATH')) {
    exit; // Direct access restriction
}

// Define plugin constants
 define('CSV_DATA_UPLOADER_PATH', plugin_dir_path(__FILE__)); // Plugin path
 require_once(CSV_DATA_UPLOADER_PATH."/admin-view/admin-side-view.php");
 require_once(CSV_DATA_UPLOADER_PATH."/database-files/db-records.php");


 // ✅ Activation Hook REGISTERED HERE
register_activation_hook(__FILE__, 'csv_data_uploader_table');

// ✅ Enqueue JS files
add_action('wp_enqueue_scripts', 'csv_data_uploader_scripts');

function csv_data_uploader_scripts(){
    wp_enqueue_script(
        'csv-data-uploader-script',
        plugin_dir_url(__FILE__) . 'assets/scripts.js',
        array('jquery'),
        '1.0',
        true
    );
}
// ajax call
wp_localize_script(
    'csv-data-uploader-script',
    'csv_data_uploader_ajax_object',
    array(
        'ajax_url' => admin_url('admin-ajax.php'),
      
    )
);
// capture ajax requests
add_action("wp_ajax_csv_data_uploader", "csv_data_uploader_handler");// when user logged in
add_action("wp_ajax_nopriv_csv_data_uploader", "csv_data_uploader_handler");// when user is logged out
function csv_data_uploader_handler(){
    echo json_encode(array("message"=>"Hello from the server"));
}


// ✅ Enqueue CSS files
add_action('wp_enqueue_scripts', 'exarth_enqueue_csv_styles');

function exarth_enqueue_csv_styles() {
    wp_register_style(
        'exarth-csv-style',
        plugins_url('assets/css/csv-uploader-style.css', __FILE__),
        array(),
        '1.0.0',
        'all'
    );
    wp_enqueue_style('exarth-csv-style');
}
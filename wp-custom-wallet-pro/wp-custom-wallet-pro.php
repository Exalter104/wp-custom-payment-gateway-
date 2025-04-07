<?php
/*
Plugin Name: WP Custom Wallet Pro
Plugin URI: https://yourwebsite.com/
Description: A custom wallet system for Easypaisa, JazzCash for secure transactions.
Version: 1.0
Author: Exarth
*/
defined('ABSPATH') || exit;



// Define Constants
define('EXARTH_PLUGIN_VERSION', '1.0');
define('EXARTH_PLUGIN_DIR', plugin_dir_path(__FILE__));



// Import required files for database and shortcode
require_once plugin_dir_path(__FILE__) . 'database/table.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';



// Register Activation Hook
register_activation_hook(__FILE__, 'exarth_create_payment_table');


// Enqueue Assets
add_action('wp_enqueue_scripts', 'exarth_enqueue_assets');
function exarth_enqueue_assets() {
    wp_enqueue_style('exarth-montserrat-font', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap', array(), null);
    wp_enqueue_style('exarth-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), EXARTH_PLUGIN_VERSION . '.' . time());
    wp_enqueue_script('exarth-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), EXARTH_PLUGIN_VERSION, true);

    wp_localize_script('exarth-script', 'exarth_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('exarth_payment_nonce')
    ));
}

// Add Shortcode for Payment Form
add_shortcode('exarth_payment', 'exarth_payment_form_shortcode');
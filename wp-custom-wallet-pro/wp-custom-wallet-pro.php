<?php
/*
Plugin Name: WP Custom Wallet Pro
Plugin URI: https://yourwebsite.com/
Description: A custom wallet system for Easypaisa , JazzCash for secure transections.
Version: 1.0
Author: Exarth
*/


defined('ABSPATH') || exit;

// Define Constants
define('EXARTH_PLUGIN_VERSION', '1.0');
define('EXARTH_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Register Activation Hook
register_activation_hook(__FILE__, 'exarth_create_payment_table');


// Import Database Table Creation File
require_once(EXARTH_PLUGIN_DIR . 'database/table.php');
require_once(EXARTH_PLUGIN_DIR . 'includes/shortcode-form.php');

// Include required files
register_activation_hook(__FILE__, 'exarth_create_payment_table');


// Enqueue Assets
add_action('wp_enqueue_scripts', 'exarth_enqueue_assets');
function exarth_enqueue_assets() {
    wp_enqueue_style('exarth-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), EXARTH_PLUGIN_VERSION);
    wp_enqueue_script('exarth-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), EXARTH_PLUGIN_VERSION, true);
}

// Add Shortcode for Payment Form
add_shortcode('exarth_payment', 'exarth_payment_form_shortcode');
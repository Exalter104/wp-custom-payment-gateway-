<?php
/**
 * Plugin Name: Simple Short Code
 * Plugin URI:  https://yourwebsite.com
 * Description: A custom WordPress Shortcode plugin to show static data.
 * Version: 1.0.0
 * Author: Exarth
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * Text Domain: Shortcode-plugin
 */

if (!defined('ABSPATH')) {
    exit; // Direct access restriction
}

// Plugin Constants
define('SHORTCODE_PLUGIN_PATH', plugin_dir_path(__FILE__)); // Plugin path
// path constants
require_once SHORTCODE_PLUGIN_PATH . 'main-file.php';
require_once SHORTCODE_PLUGIN_PATH . 'short-code-file.php';
?>
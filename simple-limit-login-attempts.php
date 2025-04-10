<?php


/**
 * Plugin Name: Simple Limit Login Attempts
 * Plugin URI : https://exarth.com
 * Plugin Description: A simple plugin to limit login attempts and protect against brute force attacks.
 * Version: 1.0.0
 * Author: Exarth
 * Author URI: https://exarth.com
 * License: MIT
 * License URI: https://choosealicense.com/licenses/mit/
 * Text Domain: simple-limit-login-attempts
 */



// SECURITY CHECK
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


// DEFINE CONSTANT
define( 'SLLA_VERSION', '1.0.0' );
define( 'SLLA_PLUGIN_ID', 'simple-limit-login-attempts' );
define( 'SLLA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SLLA_PLUGIN_URL', plugins_url( '', __FILE__ ) );


//INCLUDING FILES
require_once SLLA_PLUGIN_DIR . 'includes/class-slla-core.php';


// INITIALIZE THE PLUGIN
function slla_init(){
    new SSL_Core();

}

add_action('plugins_loaded', 'slla_init');

?>
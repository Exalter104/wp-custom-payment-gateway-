<?php

// Enqueue admin CSS
function wp_secure_login_enqueue_admin_styles() {
    wp_enqueue_style( 'wp-secure-login-admin-style', plugin_dir_url( __FILE__ ) . '../assets/css/admin-style.css', array(), '1.0', 'all' );
}
add_action( 'admin_enqueue_scripts', 'wp_secure_login_enqueue_admin_styles' );


?>
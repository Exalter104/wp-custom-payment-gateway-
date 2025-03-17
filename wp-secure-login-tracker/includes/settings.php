<?php
//Plugin settings page ka backend (options API).

if (!defined('ABSPATH')) {
    exit;
}

// Register settings
function wp_secure_login_register_settings() {
    register_setting('wp_secure_login_options_group', 'wp_secure_login_background_color');

    add_settings_section(
        'wp_secure_login_main_section', 
        'Customize Login Page', 
        'wp_secure_login_section_description', 
        'wp-secure-login'
    );

    add_settings_field(
        'wp_secure_login_background_color', 
        'Background Color', 
        'wp_secure_login_background_color_callback', 
        'wp-secure-login', 
        'wp_secure_login_main_section'
    );
}
add_action('admin_init', 'wp_secure_login_register_settings');

// Section Description
function wp_secure_login_section_description() {
    echo '<p>Customize the login page appearance</p>';
}

// Field Callback for Background Color Picker
function wp_secure_login_background_color_callback() {
    $color = get_option('wp_secure_login_background_color', '#ffffff');
    echo '<input type="color" name="wp_secure_login_background_color" value="' . esc_attr($color) . '">';
}
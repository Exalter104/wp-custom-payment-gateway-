<?php
//Admin menu aur submenus add karne ke liye. (Example: Login Logs, Settings).

if (!defined('ABSPATH')) {
    exit;
}

// Function to add menu in admin panel
function wp_secure_login_add_menu() {
    add_menu_page(
        'WP Secure Login',     // Page Title
        'Secure Login',        // Menu Title
        'manage_options',      // Capability
        'wp-secure-login',     // Menu Slug
        'wp_secure_login_settings_page', // Callback Function
        'dashicons-lock',      // Icon
        80                    // Position
    );
}
add_action('admin_menu', 'wp_secure_login_add_menu');

// Callback Function for Settings Page
if (!function_exists('wp_secure_login_settings_page')) {
    function wp_secure_login_settings_page() {
        ?>
<div class="wrap">
    <h1>WP Secure Login Settings</h1>
    <form method="post" action="options.php">
        <?php
                settings_fields('wp_secure_login_options_group');
                do_settings_sections('wp-secure-login');
                submit_button();
                ?>
    </form>
</div>
<?php
    }
}
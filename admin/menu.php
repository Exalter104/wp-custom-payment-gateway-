<?php
// Settings Register Function
/**
* WordPress ko bata sakein ke hum ek naya setting option
* save karna chahte hain. WordPress settings ko database
* me store karta hai, aur agar hum register_setting() ka
* use na karein to WordPress ko pata nahi
* chalega ke yeh setting "legal" hai ya nahi.


* Iska Faida Kya Hai?

* WordPress isko apni settings API ke andar register kar lega.
* Jab bhi admin form submit karega, WordPress is setting ko automatically save kar lega.
* Security ke liye sanitize function ensure karega ke koi XSS attack na ho.

*/
// Direct access restriction  if some one openmy plugin in browser
if (!defined("ABSPATH")) {
    exit; // Direct access restriction
}

// Function to add menue to the admin panel
function custom_wp_login_add_menu(){
    add_menu_page(
    'Custom WP Login', // Page Title
    'Login Customizer', // Menu Title
    'manage_options', // Capability
    'custom-wp-login', // Menu Slug
    'custom_wp_login_settings_page', // Callback Function
    'dashicons-admin-generic', // Icon
    80 );// Position
}

//Callback Function to display the admin settings page
function custom_wp_login_settings_page() {
    ?>

<div class="wrap">
    <h1>Custom WP Login Settings</h1>

    <!--Setting Form -->
    <form action="options.php" method="post">
        <?php
        
        // Secure form feilds & setting Page
        settings_fields('custom_wp_login_options_group');

        // Display Sections & Fields
        do_settings_sections('custom-wp-login');

        // Submit Button
        submit_button();
        
        ?>
    </form>
</div>
<?php
}

// Register settings, sections, and fields

function custom_wp_login_register_settings() {

    // Register a setting for storing custom message
    //custom_wp_login_options_group: Security group name jo hum settings page me use kar rahe hain.
    //custom_wp_login_option: Database me save hone wali option key ka naam.
    register_setting('custom_wp_login_options_group', 'custom_wp_login_option');

    // Create a new section in the settings page
    add_settings_section(
        'custom_wp_login_section',
        'Login Page Customization',
        'custom_wp_login_section_callback',
        'custom-wp-login'
    );

    // Add a field for custom message input
    add_settings_field(
        'custom_wp_login_option_field',
        'Custom Message',
        'custom_wp_login_option_callback',
        'custom-wp-login',
        'custom_wp_login_section'
    );
}

add_action('admin_init', 'custom_wp_login_register_settings');

// Section Description
function custom_wp_login_section_callback() {
    echo '<p>Customize your login page settings here.</p>';
}

// Input Field for Custom Message
function custom_wp_login_option_callback() {
    $option = get_option('custom_wp_login_option');
    echo '<input type="text" name="custom_wp_login_option" value="' . esc_attr($option) . '" />';
}


// Hook to add menu in admin panel
add_action('admin_menu', 'custom_wp_login_add_menu');

?>
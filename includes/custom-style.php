<?php
// Apply custom styles to login page
function custom_wp_login_apply_styles() {
    $bg_color = get_option('custom_wp_login_background_color', '#ffffff');
    echo "<style>:root { --login-bg-color: " . esc_attr($bg_color) . "; }</style>";
}
add_action('login_head', 'custom_wp_login_apply_styles');
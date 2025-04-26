<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Handle reset settings action
if ( isset( $_POST['slla_reset_settings'] ) && check_admin_referer( 'slla_reset_settings_nonce', 'slla_reset_settings_nonce' ) ) {
    global $wpdb;
    
    // Reset all plugin settings
    $options = array(
        'slla_enable_email_notifications',
        'slla_enable_sms_notifications',
        'slla_admin_phone_number',
        'slla_twilio_account_sid',
        'slla_twilio_auth_token',
        'slla_twilio_phone_number',
        // Add other settings here if needed
    );
    
    foreach ( $options as $option ) {
        delete_option( $option );
    }
    
    // Optionally reset other plugin data (e.g., custom database tables)
    ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e( 'All settings have been reset successfully.', 'simple-limit-login-attempts' ); ?></p>
</div>
<?php
}
?>

<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Limit Login Attempts Tools', 'simple-limit-login-attempts' ); ?></h1>
    <?php SLLA_Helpers::render_submenu(); ?>
    <div class="slla-card slla-tools">
        <h2><?php _e( 'Tools', 'simple-limit-login-attempts' ); ?></h2>
        <form method="post" class="slla-reset-settings-form">
            <?php wp_nonce_field( 'slla_reset_settings_nonce', 'slla_reset_settings_nonce' ); ?>
            <button type="submit" name="slla_reset_settings" class="button slla-reset-btn">
                <?php _e( 'Reset All Settings', 'simple-limit-login-attempts' ); ?>
            </button>
        </form>
    </div>
</div>
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['slla_clear_logs'] ) ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'slla_logs';
    $wpdb->query( "TRUNCATE TABLE $table_name" );
    ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e( 'All logs have been cleared successfully.', 'simple-limit-login-attempts' ); ?></p>
</div>
<?php
}
?>

<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Limit Login Attempts Tools', 'simple-limit-login-attempts' ); ?></h1>
    <?php SLLA_Helpers::render_submenu(); ?>
    <div class="slla-card slla-tools">
        <h2><?php _e( 'Tools', 'simple-limit-login-attempts' ); ?></h2>
        <form method="post">
            <button type="submit" name="slla_clear_logs"
                class="button"><?php _e( 'Clear All Logs', 'simple-limit-login-attempts' ); ?></button>
        </form>
    </div>
</div>
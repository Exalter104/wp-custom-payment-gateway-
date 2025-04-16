<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SLLA_Helpers {
    /**
     * Render the submenu navigation for admin pages.
     */
    public static function render_submenu() {
        $pages = array(
            'slla-dashboard' => __( 'Dashboard', 'simple-limit-login-attempts' ),
            'slla-settings' => __( 'Settings', 'simple-limit-login-attempts' ),
            'slla-logs' => __( 'Logs', 'simple-limit-login-attempts' ),
            'slla-tools' => __( 'Tools', 'simple-limit-login-attempts' ),
            'slla-premium' => __( 'Premium', 'simple-limit-login-attempts' ),
        );
        $current_page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'slla-dashboard';
        ?>
<div class="slla-submenu">
    <?php foreach ( $pages as $slug => $title ) : ?>
    <a href="<?php echo admin_url( 'admin.php?page=' . $slug ); ?>"
        class="<?php echo $current_page === $slug ? 'current' : ''; ?>">
        <?php echo esc_html( $title ); ?>
    </a>
    <?php endforeach; ?>
</div>
<?php
    }
}
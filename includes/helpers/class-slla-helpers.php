<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SLLA_Helpers
 * Helper functions for the Simple Limit Login Attempts plugin.
 */
class SLLA_Helpers {

    /**
     * Render the submenu navigation.
     */
    public static function render_submenu() {
        global $submenu;
        $parent_slug = 'slla-dashboard';
        $current_page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'slla-dashboard';

        // Check if submenu items are available
        if ( ! isset( $submenu[$parent_slug] ) ) {
            echo '<p>No submenu items found for ' . esc_html( $parent_slug ) . '</p>';
            return;
        }

        // Check if premium is active
        $admin = new SLLA_Admin();
        $is_premium = $admin->is_premium_active();

        ?>
<div class="slla-submenu">
    <?php foreach ( $submenu[$parent_slug] as $item ) : ?>
    <?php
                $slug = $item[2];
                $title = $item[0];

                // Skip premium pages in free version
                if ( ! $is_premium && in_array( $slug, [ 'slla-geo-blocking', 'slla-notifications' ] ) ) {
                    continue;
                }

                $class = ( $current_page === $slug ) ? 'slla-submenu-item active' : 'slla-submenu-item';
                ?>
    <a href="<?php echo esc_url( admin_url( "admin.php?page=$slug" ) ); ?>" class="<?php echo esc_attr( $class ); ?>">
        <?php echo esc_html( $title ); ?>
    </a>
    <?php endforeach; ?>
</div>
<?php
    }
}
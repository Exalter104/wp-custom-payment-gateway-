<?php
// Ensure this file is not accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get blocked IPs list
$blocked_ips = get_option('wp_secure_blocked_ips', []);

?>

<div class="wrap">
    <h2>Blocked IPs</h2>
    <table class="wp-list-table widefat fixed">
        <thead>
            <tr>
                <th>IP Address</th>
                <th>Unblock</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($blocked_ips as $ip => $expire_time) : ?>
            <tr>
                <td><?php echo esc_html($ip); ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="unblock_ip" value="<?php echo esc_attr($ip); ?>">
                        <button type="submit" class="button button-primary">Unblock</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
// Handle IP unblock request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['unblock_ip'])) {
    $ip = sanitize_text_field($_POST['unblock_ip']);
    $ip_block = new WP_Secure_Login_IP_Block();
    if ($ip_block->unblock_ip($ip)) {
        echo '<div class="updated"><p>IP ' . esc_html($ip) . ' has been unblocked.</p></div>';
    } else {
        echo '<div class="error"><p>Failed to unblock IP.</p></div>';
    }
}
?>
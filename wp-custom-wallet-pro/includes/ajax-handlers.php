<?php
defined('ABSPATH') || exit;

function exarth_save_payment_callback() {
    // Nonce verification
    check_ajax_referer('exarth_payment_nonce', 'nonce');

    // Form data sanitize karte hain
    $amount = sanitize_text_field($_POST['amount']);
    $phone = sanitize_text_field($_POST['phone']);
    $gateway = sanitize_text_field($_POST['gateway']);

    // Validation checks
    if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
        wp_send_json_error(array('message' => 'Invalid amount.'));
    }
    if (empty($phone) || !preg_match('/^\d{11}$/', $phone)) {
        wp_send_json_error(array('message' => 'Invalid phone number.'));
    }
    if (empty($gateway) || !in_array($gateway, array('easypaisa', 'jazzcash', 'sadapay', 'nayapay'))) {
        wp_send_json_error(array('message' => 'Invalid payment gateway.'));
    }

    // Database mein save karo
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_payments';

    $result = $wpdb->insert(
        $table_name,
        array(
            'amount' => $amount,
            'phone' => $phone,
            'gateway' => $gateway,
            'status' => 'pending',
            'created_at' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%s', '%s')
    );

    // Agar database mein save nahi hua
    if ($result === false) {
        wp_send_json_error(array('message' => 'Failed to save payment: ' . $wpdb->last_error));
    }

    wp_send_json_success(array('message' => 'Payment saved successfully!'));
}

add_action('wp_ajax_exarth_save_payment', 'exarth_save_payment_callback');
add_action('wp_ajax_nopriv_exarth_save_payment', 'exarth_save_payment_callback');
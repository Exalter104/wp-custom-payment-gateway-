<?php

defined('ABSPATH') || exit;

// AJAX handler for saving payment process
add_action('wp_ajax_exarth_save_payment', 'exarth_save_payment_callback');
add_action('wp_ajax_nopriv_exarth_save_payment', 'exarth_save_payment_callback');


function exarth_save_payment_callback() {

    // Verify nonce
    check_ajax_referer('exarth_payment_nonce', 'nonce');

    // get form data
    
    $amount = sanitize_text_field($_POST['amount']);
    $phone = sanitize_text_field($_POST['phone']);

    $gateway = sanitize_text_field($_POST['gateway']);


    // Validate form data
    if (empty($amount) || empty($phone) || empty($gateway)) {
        wp_send_json_error(array('message' => 'All fields are required.'));
        wp_die();
    }

    // save the form data in database

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
    if ($result === false) {
        wp_send_json_error(array('message' => 'Failed to save payment.'));
    }

    wp_send_json_success(array('message' => 'Payment saved successfully.'));

}
?>
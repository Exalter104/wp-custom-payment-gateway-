<?php
defined('ABSPATH') || exit;

function exarth_payment_form_shortcode() {
    ob_start();
    ?>
<div class="exarth-payment-wrapper">
    <h2 class="exarth-form-title">Custom Payment Gateways</h2>
    <form class="exarth-form" method="POST" action="">
        <div class="exarth-form-group">
            <label for="amount">Amount:</label>
            <input type="text" id="amount" name="amount" placeholder="Enter amount" required>
        </div>

        <div class="exarth-form-group">
            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" placeholder="Enter phone number" required>
        </div>

        <div class="exarth-form-group">
            <label for="gateway">Payment Gateway:</label>
            <select id="gateway" name="gateway" required>
                <option value="" disabled selected>Select a gateway</option>
                <option value="easypaisa">EasyPaisa</option>
                <option value="jazzcash">JazzCash</option>
                <option value="sadapay">SadaPay</option>
                <option value="nayapay">NayaPay</option>
            </select>
        </div>

        <button type="submit" class="exarth-submit-button">Submit Payment</button>
    </form>
</div>
<?php
    return ob_get_clean();
}
?>
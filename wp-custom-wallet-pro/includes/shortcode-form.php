<?php

defined('ABSPATH') || exit;

function exarth_payment_form_shortcode(){
    ob_start();
    ?>

<form class="exarth-form" method="POST" action="">
    <label for="amount">Amount:</label><br>
    <input type="text" id="amount" name="amount" placeholder="Enter amount" required><br>

    <label for="phone">Phone Number:</label><br>
    <input type="text" id="phone" name="phone" placeholder="Enter phone number" required><br>

    <label for="gateway">Payment Gateway:</label><br>
    <select id="gateway" name="gateway" required>
        <option value="easypaisa">EasyPaisa</option>
        <option value="jazzcash">JazzCash</option>
    </select><br>

    <button type="submit">Submit Payment</button>
</form>
<?php
return ob_get_clean(); // Return the buffered output
}
?>
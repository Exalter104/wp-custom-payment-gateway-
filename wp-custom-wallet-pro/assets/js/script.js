jQuery(document).ready(function($) {
    $('#exarth-payment-form').on('submit', function(e) {
        e.preventDefault(); // Form ko default submit hone se rokta hai

        // Form ke values lete hain
        var amount = $('#amount').val();
        var phone = $('#phone').val();
        var gateway = $('#gateway').val();

        // Validation checks
        if (!amount || isNaN(amount) || amount <= 0) {
            alert('Please enter a valid amount (positive number).');
            return;
        }
        if (!phone || !/^\d{11}$/.test(phone)) {
            alert('Please enter a valid 11-digit phone number.');
            return;
        }
        if (!gateway) {
            alert('Please select a payment gateway.');
            return;
        }

        // Agar sab sahi hai, toh AJAX call karte hain
        var formData = {
            action: 'exarth_save_payment',
            nonce: exarth_ajax.nonce,
            amount: amount,
            phone: phone,
            gateway: gateway
        };

        $.ajax({
            url: exarth_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Payment saved successfully!');
                    $('#exarth-payment-form')[0].reset(); // Form reset kar deta hai
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX error: ' + error);
            }
        });
    });
});
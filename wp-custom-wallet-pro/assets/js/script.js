// Exarth Payment Gateway Script
jQuery(document).ready(function($) {
  $('#exarth-payment-form').on('submit', function(e) {
e.preventDefault(); // Prevent the default form submission

// Get form data
var formData = {
    action: 'exarth_save_payment',
    nonce: exarth_ajax.nonce,
    amount: $('#amount').val(),
    phone: $('#phone').val(),
    gateway: $('#gateway').val(),
};
// Ajax call to save the form data
$.ajax({
     // WordPress AJAX URL
    url: exarth_ajax.ajax_url,
    type: 'POST',
    data: formData,
    success: function(response) {
        if (response.success) {
            // Handle success response
            alert('Payment processed successfully!');
            $('#exarth-payment-form')[0].reset(); // Reset the form
        } else {
            // Handle error response
            alert('Error processing payment: ' + response.data.message);
        }
    },
    error: function(xhr, status, error) {
        // Handle AJAX error
        alert('AJAX error: ' + error);
    }

});
});
});
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Notifications', 'simple-limit-login-attempts' ); ?></h1>
    <?php SLLA_Helpers::render_submenu(); ?>

    <div class="slla-card slla-real-time-notifications">
        <h2><?php _e( 'Notification Settings', 'simple-limit-login-attempts' ); ?></h2>
        <?php if ( $this->is_premium_active() ) : ?>
        <form method="post" action="options.php" id="slla-notification-settings-form">
            <?php settings_fields( 'slla_notifications_group' ); ?>
            <ul>
                <li>
                    <input type="checkbox" name="slla_enable_email_notifications" value="1"
                        <?php checked( 1, get_option( 'slla_enable_email_notifications', 0 ) ); ?>>
                    <label><?php _e( 'Enable Email Notifications', 'simple-limit-login-attempts' ); ?></label>
                </li>
                <li>
                    <input type="checkbox" name="slla_enable_sms_notifications" value="1"
                        <?php checked( 1, get_option( 'slla_enable_sms_notifications', 0 ) ); ?>>
                    <label><?php _e( 'Enable SMS Notifications', 'simple-limit-login-attempts' ); ?></label>
                </li>
                <li>
                    <label><?php _e( 'Admin Phone Number for SMS', 'simple-limit-login-attempts' ); ?></label>
                    <input type="text" name="slla_admin_phone_number"
                        value="<?php echo esc_attr( get_option( 'slla_admin_phone_number', '' ) ); ?>"
                        placeholder="+1234567890">
                    <span class="slla-error-message" id="admin-phone-error"></span>
                </li>
                <li>
                    <label><?php _e( 'Twilio Account SID', 'simple-limit-login-attempts' ); ?></label>
                    <input type="text" name="slla_twilio_account_sid" id="twilio-account-sid"
                        value="<?php echo esc_attr( get_option( 'slla_twilio_account_sid', '' ) ); ?>"
                        placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                    <span class="slla-error-message" id="twilio-sid-error"></span>
                </li>
                <li>
                    <label><?php _e( 'Twilio Auth Token', 'simple-limit-login-attempts' ); ?></label>
                    <input type="text" name="slla_twilio_auth_token" id="twilio-auth-token"
                        value="<?php echo esc_attr( get_option( 'slla_twilio_auth_token', '' ) ); ?>"
                        placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                    <span class="slla-error-message" id="twilio-token-error"></span>
                </li>
                <li>
                    <label><?php _e( 'Twilio Phone Number', 'simple-limit-login-attempts' ); ?></label>
                    <input type="text" name="slla_twilio_phone_number" id="twilio-phone-number"
                        value="<?php echo esc_attr( get_option( 'slla_twilio_phone_number', '' ) ); ?>"
                        placeholder="+1234567890">
                    <span class="slla-error-message" id="twilio-phone-error"></span>
                </li>
            </ul>
            <?php submit_button( __( 'Save Notification Settings', 'simple-limit-login-attempts' ), 'primary slla-submit-btn', 'submit', false ); ?>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('slla-notification-settings-form');
            const twilioSidInput = document.getElementById('twilio-account-sid');
            const twilioTokenInput = document.getElementById('twilio-auth-token');
            const twilioPhoneInput = document.getElementById('twilio-phone-number');
            const adminPhoneInput = document.querySelector('input[name="slla_admin_phone_number"]');
            const submitButton = form.querySelector('button[type="submit"]');

            // Phone number validation regex
            const phoneRegex = /^\+[1-9]\d{1,14}$/;

            // Function to show error message
            function showError(element, message) {
                element.textContent = message;
                element.style.color = '#ff6f61';
            }

            // Function to clear error message
            function clearError(element) {
                element.textContent = '';
            }

            // Validate phone numbers
            function validatePhone(input, errorElement) {
                if (!phoneRegex.test(input.value)) {
                    showError(errorElement, 'Please enter a valid phone number (e.g., +1234567890)');
                    input.classList.add('slla-input-error');
                    return false;
                } else {
                    clearError(errorElement);
                    input.classList.remove('slla-input-error');
                    return true;
                }
            }

            // Validate Twilio credentials via AJAX
            function validateTwilioCredentials() {
                const accountSid = twilioSidInput.value;
                const authToken = twilioTokenInput.value;
                const twilioPhone = twilioPhoneInput.value;

                if (!accountSid || !authToken || !twilioPhone) {
                    return false; // Skip validation if fields are empty
                }

                const data = {
                    action: 'slla_validate_twilio_credentials',
                    account_sid: accountSid,
                    auth_token: authToken,
                    phone_number: twilioPhone,
                    nonce: '<?php echo wp_create_nonce( "slla_validate_twilio_nonce" ); ?>'
                };

                fetch('<?php echo admin_url( "admin-ajax.php" ); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams(data).toString()
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            clearError(document.getElementById('twilio-sid-error'));
                            clearError(document.getElementById('twilio-token-error'));
                            clearError(document.getElementById('twilio-phone-error'));
                            twilioSidInput.classList.remove('slla-input-error');
                            twilioTokenInput.classList.remove('slla-input-error');
                            twilioPhoneInput.classList.remove('slla-input-error');
                            submitButton.disabled = false;
                        } else {
                            showError(document.getElementById('twilio-sid-error'), result.data.message);
                            showError(document.getElementById('twilio-token-error'), result.data.message);
                            showError(document.getElementById('twilio-phone-error'), result.data.message);
                            twilioSidInput.classList.add('slla-input-error');
                            twilioTokenInput.classList.add('slla-input-error');
                            twilioPhoneInput.classList.add('slla-input-error');
                            submitButton.disabled = true;
                        }
                    })
                    .catch(error => {
                        showError(document.getElementById('twilio-sid-error'),
                            'Error validating credentials.');
                        showError(document.getElementById('twilio-token-error'),
                            'Error validating credentials.');
                        showError(document.getElementById('twilio-phone-error'),
                            'Error validating credentials.');
                        twilioSidInput.classList.add('slla-input-error');
                        twilioTokenInput.classList.add('slla-input-error');
                        twilioPhoneInput.classList.add('slla-input-error');
                        submitButton.disabled = true;
                    });
            }

            adminPhoneInput.addEventListener('input', () => {
                validatePhone(adminPhoneInput, document.getElementById('admin-phone-error'));
            });

            twilioSidInput.addEventListener('input', validateTwilioCredentials);
            twilioTokenInput.addEventListener('input', validateTwilioCredentials);
            twilioPhoneInput.addEventListener('input', () => {
                validatePhone(twilioPhoneInput, document.getElementById('twilio-phone-error'));
                validateTwilioCredentials();
            });

            validatePhone(adminPhoneInput, document.getElementById('admin-phone-error'));
            validatePhone(twilioPhoneInput, document.getElementById('twilio-phone-error'));
            validateTwilioCredentials();
        });
        </script>
        <?php else : ?>
        <p class="slla-premium-notice">
            <?php _e( 'Upgrade to Premium to enable email and SMS notifications!', 'simple-limit-login-attempts' ); ?>
            <a href="<?php echo admin_url( 'admin.php?page=slla-premium' ); ?>" class="slla-upgrade-btn">
                <?php _e( 'Upgrade Now', 'simple-limit-login-attempts' ); ?>
            </a>
        </p>
        <?php endif; ?>
    </div>
</div>
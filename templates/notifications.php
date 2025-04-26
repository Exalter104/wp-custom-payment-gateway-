<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Create an instance of SLLA_Admin to call the method
$admin = new SLLA_Admin();
?>

<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Notifications', 'simple-limit-login-attempts' ); ?></h1>
    <?php SLLA_Helpers::render_submenu(); ?>

    <?php if ( $admin->is_premium_active() ) : ?>
    <form method="post" action="options.php" id="slla-notification-settings-form">
        <?php settings_fields( 'slla_notifications_group' ); ?>
        <div class="slla-settings-grid">
            <!-- Enable Email Notifications -->
            <div class="slla-setting-item">
                <label>
                    <input type="checkbox" name="slla_enable_email_notifications" value="1"
                        <?php checked( 1, get_option( 'slla_enable_email_notifications', 0 ) ); ?>>
                    <?php _e( 'Enable Email Notifications', 'simple-limit-login-attempts' ); ?>
                </label>
                <p class="description">
                    <?php _e( 'Receive timely alerts and updates via email when a lockout occurs.', 'simple-limit-login-attempts' ); ?>
                </p>
            </div>

            <!-- Enable SMS Notifications -->
            <div class="slla-setting-item">
                <label>
                    <input type="checkbox" name="slla_enable_sms_notifications" value="1"
                        <?php checked( 1, get_option( 'slla_enable_sms_notifications', 0 ) ); ?>>
                    <?php _e( 'Enable SMS Notifications', 'simple-limit-login-attempts' ); ?>
                </label>
                <p class="description">
                    <?php _e( 'Get SMS notifications for lockouts and failed login attempts.', 'simple-limit-login-attempts' ); ?>
                </p>
            </div>

            <!-- Admin Phone Number -->
            <div class="slla-setting-item">
                <div class="slla-form-group">
                    <label><?php _e( 'Admin Phone Number for SMS', 'simple-limit-login-attempts' ); ?></label>
                    <div class="slla-input-wrapper">
                        <input type="text" name="slla_admin_phone_number" id="slla-admin-phone-number"
                            value="<?php echo esc_attr( get_option( 'slla_admin_phone_number', '' ) ); ?>"
                            placeholder="+1234567890">
                    </div>
                    <span class="slla-error-message" id="admin-phone-error"></span>
                    <p class="description">
                        <?php _e( 'Enter the admin phone number to receive SMS notifications (e.g., +1234567890).', 'simple-limit-login-attempts' ); ?>
                    </p>
                </div>
            </div>

            <!-- Twilio Account SID -->
            <div class="slla-setting-item">
                <div class="slla-form-group">
                    <label><?php _e( 'Twilio Account SID', 'simple-limit-login-attempts' ); ?></label>
                    <div class="slla-input-wrapper">
                        <input type="text" name="slla_twilio_account_sid" id="twilio-account-sid"
                            value="<?php echo esc_attr( get_option( 'slla_twilio_account_sid', '' ) ); ?>"
                            placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                    </div>
                    <span class="slla-error-message" id="twilio-sid-error"></span>
                    <p class="description">
                        <?php _e( 'Your Twilio Account SID for sending SMS notifications.', 'simple-limit-login-attempts' ); ?>
                    </p>
                </div>
            </div>

            <!-- Twilio Auth Token -->
            <div class="slla-setting-item">
                <div class="slla-form-group">
                    <label><?php _e( 'Twilio Auth Token', 'simple-limit-login-attempts' ); ?></label>
                    <div class="slla-input-wrapper">
                        <input type="text" name="slla_twilio_auth_token" id="twilio-auth-token"
                            value="<?php echo esc_attr( get_option( 'slla_twilio_auth_token', '' ) ); ?>"
                            placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                    </div>
                    <span class="slla-error-message" id="twilio-token-error"></span>
                    <p class="description">
                        <?php _e( 'Your Twilio Auth Token for authentication.', 'simple-limit-login-attempts' ); ?>
                    </p>
                </div>
            </div>

            <!-- Twilio Phone Number -->
            <div class="slla-setting-item">
                <div class="slla-form-group">
                    <label><?php _e( 'Twilio Phone Number', 'simple-limit-login-attempts' ); ?></label>
                    <div class="slla-input-wrapper">
                        <input type="text" name="slla_twilio_phone_number" id="twilio-phone-number"
                            value="<?php echo esc_attr( get_option( 'slla_twilio_phone_number', '' ) ); ?>"
                            placeholder="+1234567890">
                    </div>
                    <span class="slla-error-message" id="twilio-phone-error"></span>
                    <p class="description">
                        <?php _e( 'The Twilio phone number used to send SMS notifications (e.g., +1234567890).', 'simple-limit-login-attempts' ); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Submit Button with Yellow Gradient -->
        <div class="slla-form-group">
            <?php submit_button( __( 'Save Notification Settings', 'simple-limit-login-attempts' ), 'primary', 'submit', false, array( 'class' => 'button primary slla-submit-btn', 'style' => 'background: linear-gradient(90deg, #FBBF24, #F59E0B);' ) ); ?>
            <style>
            #submit.slla-submit-btn:hover {
                background: linear-gradient(90deg, #F59E0B, #D97706) !important;
            }

            #submit.slla-submit-btn:active {
                background: linear-gradient(90deg, #F59E0B, #D97706) !important;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1) !important;
            }
            </style>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('slla-notification-settings-form');
        const twilioSidInput = document.getElementById('twilio-account-sid');
        const twilioTokenInput = document.getElementById('twilio-auth-token');
        const twilioPhoneInput = document.getElementById('twilio-phone-number');
        const adminPhoneInput = document.getElementById('slla-admin-phone-number');
        const submitButton = form.querySelector('button[type="submit"]');

        // Phone number validation regex
        const phoneRegex = /^\+[1-9]\d{1,14}$/;

        // Function to show error message
        function showError(element, message) {
            element.textContent = message;
            element.style.color = '#EF4444';
            element.style.display = 'flex';
            element.style.alignItems = 'center';
            element.innerHTML = '<span class="dashicons dashicons-warning" style="margin-right: 5px;"></span>' +
                message;
        }

        // Function to clear error message
        function clearError(element) {
            element.textContent = '';
            element.style.display = 'none';
        }

        // Validate phone numbers
        function validatePhone(input, errorElement) {
            if (input.value && !phoneRegex.test(input.value)) {
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
                submitButton.disabled = false;
                return; // Skip validation if fields are empty
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
                    showError(document.getElementById('twilio-sid-error'), 'Error validating credentials.');
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
    <div class="slla-premium-promotion">
        <h3><?php _e( 'Unlock Email & SMS Notifications with Premium', 'simple-limit-login-attempts' ); ?></h3>
        <p><?php _e( 'Upgrade to Premium to enable real-time email and SMS notifications for lockouts and failed login attempts.', 'simple-limit-login-attempts' ); ?>
        </p>
        <a href="<?php echo admin_url( 'admin.php?page=slla-premium' ); ?>" class="slla-upgrade-btn">
            <?php _e( 'Upgrade Now', 'simple-limit-login-attempts' ); ?>
        </a>
    </div>
    <?php endif; ?>
</div>
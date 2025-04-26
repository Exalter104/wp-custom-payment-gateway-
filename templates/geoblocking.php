<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Handle reset settings action
 */
function slla_handle_reset_geoblocking_settings() {
    if ( isset( $_POST['slla_reset_geoblocking_settings'] ) && check_admin_referer( 'slla_reset_geoblocking_settings_nonce', 'slla_reset_geoblocking_nonce' ) ) {
        update_option( 'slla_ipstack_api_key', '' );
        update_option( 'slla_allowed_countries', array( 'PK' ) );
        update_option( 'slla_ipstack_usage_count', 0 );
        update_option( 'slla_blocked_attempts', 0 );
        update_option( 'slla_usage_period_start', gmdate( 'Y-m-01' ) );
        ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e( 'Geo-Blocking settings have been reset to default values.', 'simple-limit-login-attempts' ); ?></p>
</div>
<?php
    }
}
add_action( 'admin_init', 'slla_handle_reset_geoblocking_settings' );

/**
 * Register settings with sanitization callbacks
 */
function slla_register_geoblocking_settings() {
    register_setting( 'slla_geoblocking_group', 'slla_ipstack_api_key', array(
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    register_setting( 'slla_geoblocking_group', 'slla_allowed_countries', array(
        'sanitize_callback' => 'slla_sanitize_allowed_countries',
    ) );
}
add_action( 'admin_init', 'slla_register_geoblocking_settings' );

/**
 * Sanitize allowed countries
 *
 * @param array $input The input array of country codes.
 * @return array Sanitized array of country codes.
 */
function slla_sanitize_allowed_countries( $input ) {
    if ( ! is_array( $input ) ) {
        return array( 'PK' ); // Default to Pakistan if input is invalid
    }
    $countries = array( 'PK', 'US', 'IN', 'GB', 'CA', 'AU', 'DE', 'FR', 'CN', 'JP' );
    return array_intersect( $input, $countries ); // Only allow valid country codes
}

/**
 * Increment API usage counter
 */
function slla_increment_api_usage() {
    $current_month = gmdate( 'Y-m-01' );
    $usage_period_start = get_option( 'slla_usage_period_start', $current_month );

    // Reset counter if month has changed
    if ( $usage_period_start !== $current_month ) {
        update_option( 'slla_ipstack_usage_count', 0 );
        update_option( 'slla_blocked_attempts', 0 );
        update_option( 'slla_usage_period_start', $current_month );
    }

    $usage_count = (int) get_option( 'slla_ipstack_usage_count', 0 );
    update_option( 'slla_ipstack_usage_count', $usage_count + 1 );
}

/**
 * Increment blocked attempts counter
 */
function slla_increment_blocked_attempts() {
    $blocked_attempts = (int) get_option( 'slla_blocked_attempts', 0 );
    update_option( 'slla_blocked_attempts', $blocked_attempts + 1 );
}

/**
 * Check if login is allowed based on Geo-Blocking
 * This function should be called during login attempt
 */
function slla_check_geoblocking( $ip = '' ) {
    if ( empty( $ip ) ) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $api_key = get_option( 'slla_ipstack_api_key', '' );
    if ( empty( $api_key ) ) {
        return true; // Allow login if API key is not set
    }

    $url = "http://api.ipstack.com/{$ip}?access_key={$api_key}";
    $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

    // Increment API usage counter
    slla_increment_api_usage();

    if ( is_wp_error( $response ) ) {
        return true; // Allow login on error to avoid blocking due to API failure
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( isset( $data['country_code'] ) ) {
        $allowed_countries = get_option( 'slla_allowed_countries', array( 'PK' ) );
        $is_allowed = in_array( $data['country_code'], $allowed_countries );

        if ( ! $is_allowed ) {
            slla_increment_blocked_attempts();
        }

        return $is_allowed;
    }

    return true; // Allow login if country cannot be detected
}

// Hook into WordPress login to check Geo-Blocking
add_filter( 'authenticate', 'slla_geoblocking_authenticate', 100, 3 );
function slla_geoblocking_authenticate( $user, $username, $password ) {
    if ( is_wp_error( $user ) ) {
        return $user;
    }

    $is_allowed = slla_check_geoblocking();
    if ( ! $is_allowed ) {
        return new WP_Error( 'geoblocking_denied', __( 'Login is not allowed from your location.', 'simple-limit-login-attempts' ) );
    }

    return $user;
}
?>

<div class="slla-dashboard-wrap">
    <h1><?php _e( 'Geo-Blocking Settings', 'simple-limit-login-attempts' ); ?></h1>
    <?php SLLA_Helpers::render_submenu(); ?>

    <!-- API Usage Monitoring Section -->
    <div class="slla-card">
        <h2><?php _e( 'API Usage Monitoring', 'simple-limit-login-attempts' ); ?></h2>
        <div class="slla-usage-cards">
            <?php
            // Ensure options are integers
            $usage_count = (int) get_option( 'slla_ipstack_usage_count', 0 );
            $blocked_attempts = (int) get_option( 'slla_blocked_attempts', 0 );
            $usage_period_start = get_option( 'slla_usage_period_start', gmdate( 'Y-m-01' ) );
            $usage_period_end = gmdate( 'Y-m-t', strtotime( $usage_period_start ) );
            $api_limit = 100; // ipstack free plan limit

            // Manually sync with ipstack dashboard (temporary fix)
            if ( $usage_count < 7 ) {
                update_option( 'slla_ipstack_usage_count', 7 );
                $usage_count = 7;
            }
            ?>
            <div class="slla-usage-card">
                <h3>API Requests Used</h3>
                <p class="slla-usage-value">
                    <?php echo esc_html( $usage_count ); ?>/<?php echo esc_html( $api_limit ); ?></p>
            </div>
            <div class="slla-usage-card">
                <h3>Blocked Attempts</h3>
                <p class="slla-usage-value"><?php echo esc_html( $blocked_attempts ); ?></p>
            </div>
            <div class="slla-usage-card">
                <h3>Usage Period</h3>
                <p class="slla-usage-value"><?php echo esc_html( $usage_period_start ); ?> to
                    <?php echo esc_html( $usage_period_end ); ?></p>
            </div>
        </div>
        <?php if ( $usage_count >= $api_limit ) : ?>
        <p style="color: red;"><strong>Warning:</strong> You have reached your API request limit for this month.
            Consider upgrading to a paid plan on ipstack.com.</p>
        <?php endif; ?>
    </div>

    <div class="slla-card slla-settings">
        <form method="post" action="options.php">
            <?php settings_fields( 'slla_geoblocking_group' ); ?>
            <div class="slla-settings-grid">
                <!-- ipstack API Key -->
                <div class="slla-setting-item">
                    <label for="slla_ipstack_api_key"><?php _e( 'ipstack API Key', 'simple-limit-login-attempts' ); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e( 'Enter your ipstack API key to enable Geo-Blocking.', 'simple-limit-login-attempts' ); ?></span>
                        </span>
                    </label>
                    <input type="text" name="slla_ipstack_api_key" id="slla_ipstack_api_key"
                        value="<?php echo esc_attr( get_option( 'slla_ipstack_api_key', '' ) ); ?>" size="40"
                        class="slla-input" />
                    <p class="description">
                        <?php _e( 'Enter your ipstack API key. Get one from <a href="https://ipstack.com/" target="_blank">ipstack.com</a>.', 'simple-limit-login-attempts' ); ?>
                    </p>
                </div>

                <!-- Allowed Countries -->
                <div class="slla-setting-item">
                    <label
                        for="slla_allowed_countries"><?php _e( 'Allowed Countries', 'simple-limit-login-attempts' ); ?>
                        <span class="slla-tooltip">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span
                                class="slla-tooltip-text"><?php _e( 'Select the countries allowed to login. Hold Ctrl (or Cmd) to select multiple countries.', 'simple-limit-login-attempts' ); ?></span>
                        </span>
                    </label>
                    <?php
                    $value = get_option( 'slla_allowed_countries', array( 'PK' ) );
                    $countries = array(
                        'PK' => 'Pakistan',
                        'US' => 'United States',
                        'IN' => 'India',
                        'GB' => 'United Kingdom',
                        'CA' => 'Canada',
                        'AU' => 'Australia',
                        'DE' => 'Germany',
                        'FR' => 'France',
                        'CN' => 'China',
                        'JP' => 'Japan',
                    );
                    ?>
                    <select name="slla_allowed_countries[]" id="slla_allowed_countries" multiple size="3"
                        class="slla-input">
                        <?php
                        foreach ( $countries as $code => $name ) {
                            $selected = in_array( $code, $value ) ? 'selected' : '';
                            echo "<option value='{$code}' {$selected}>{$name}</option>";
                        }
                        ?>
                    </select>
                    <p class="description">
                        <?php _e( 'Select the countries allowed to login. Hold Ctrl (or Cmd) to select multiple countries.', 'simple-limit-login-attempts' ); ?>
                    </p>
                </div>
            </div>
            <?php submit_button( __( 'Save Changes', 'simple-limit-login-attempts' ), 'primary slla-submit-btn', 'submit', false ); ?>
        </form>

        <!-- Reset Geo-Blocking Settings Form -->
        <form method="post" action="" style="margin-top: 20px;"
            onsubmit="return confirm('<?php _e( 'Are you sure you want to reset Geo-Blocking settings to default values?', 'simple-limit-login-attempts' ); ?>');">
            <?php wp_nonce_field( 'slla_reset_geoblocking_settings_nonce', 'slla_reset_geoblocking_nonce' ); ?>
            <button type="submit" name="slla_reset_geoblocking_settings"
                class="button slla-reset-btn"><?php _e( 'Reset Geo-Blocking Settings to Default', 'simple-limit-login-attempts' ); ?></button>
        </form>
    </div>

    <!-- Inline CSS for consistent layout -->
    <style>
    .slla-settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .slla-setting-item {
        margin-bottom: 20px;
    }

    .slla-setting-item label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .slla-setting-item .slla-input {
        width: 100%;
        max-width: 400px;
        padding: 8px;
        box-sizing: border-box;
    }

    .slla-setting-item .description {
        color: #666;
        font-size: 12px;
        margin-top: 5px;
    }

    .slla-tooltip {
        position: relative;
        display: inline-block;
        cursor: help;
    }

    .slla-tooltip .slla-tooltip-text {
        visibility: hidden;
        width: 200px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 5px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        margin-left: -100px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .slla-tooltip:hover .slla-tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    .slla-reset-btn {
        background-color: #f7c948;
        border-color: #f7c948;
        color: #fff;
    }

    .slla-reset-btn:hover {
        background-color: #e0b73f;
        border-color: #e0b73f;
    }

    /* Styles for horizontal card layout */
    .slla-usage-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
    }

    .slla-usage-card {
        flex: 1;
        min-width: 200px;
        background-color: #FFFFFFFF;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .slla-usage-card h3 {
        margin: 0 0 10px 0;
        font-size: 16px;
        color: #333;
    }

    .slla-usage-card .slla-usage-value {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
        color: #0B0C0CFF;
    }
    </style>
</div>
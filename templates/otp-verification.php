<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e( 'Two-Factor Authentication', 'simple-limit-login-attempts' ); ?></title>
    <?php wp_head(); ?>
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f4f4f9;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .slla-otp-container {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
        max-width: 400px;
        width: 100%;
    }

    .slla-otp-container h2 {
        margin-bottom: 20px;
        color: #333;
    }

    .slla-otp-container input[type="text"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }

    .slla-otp-container input[type="submit"] {
        background-color: #0073aa;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .slla-otp-container input[type="submit"]:hover {
        background-color: #005d87;
    }
    </style>
</head>

<body>
    <div class="slla-otp-container">
        <h2><?php _e( 'Two-Factor Authentication', 'simple-limit-login-attempts' ); ?></h2>
        <p><?php _e( 'An OTP has been sent to your registered phone number. Please enter it below.', 'simple-limit-login-attempts' ); ?>
        </p>
        <form method="post" action="<?php echo esc_url( wp_login_url() ); ?>">
            <input type="text" name="slla_2fa_otp"
                placeholder="<?php _e( 'Enter OTP', 'simple-limit-login-attempts' ); ?>" required />
            <input type="hidden" name="slla_2fa_verify" value="1" />
            <input type="submit" value="<?php _e( 'Verify OTP', 'simple-limit-login-attempts' ); ?>" />
        </form>
    </div>
    <?php wp_footer(); ?>
</body>

</html>
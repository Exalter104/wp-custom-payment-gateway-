<?php
/**
 * YE CLASS PLUGIN KE MAIN LOGIC KO MANAGE KAREGI
 * (E.G., ATTEMPTS TRACKING, LOCKOUT, SETTINGS).
 */



// AGAR FILE DIRECT ACCESS HO RAHI HAI TOH EXIT KARDO
if ( ! defined( 'ABSPATH' ) ) {
    exit; // EXIT IF ACCESSED DIRECTLY
}



// MAIN CORE CLASS DEFINE KAR RAHE HAIN
class SLLA_Core {

    // CONSTRUCTOR FUNCTION JISME INIT FUNCTION KO CALL KARTE HAIN
    public function __construct() {

        // FUTURE HOOKS KE LIYE PLACEHOLDER
        $this->init();
    }



    // INIT FUNCTION JO HOOKS KO REGISTER KARTA HAI
    public function init() {

        // ADMIN NOTICE HOOK ADD KAR RAHE HAIN
        add_action( 'admin_notices', array( $this, 'show_admin_notice' ) );


        // CHECK KARTE HAIN KE PLUGIN PEHLI DAFA ACTIVATE HUA HAI YA NAHI
        if ( get_option( 'slla_plugin_activated_notice' ) !== 'yes' ) {
            
            // AGAR PEHLI DAFA ACTIVATE HUA HAI TO ADMIN_INIT HOOK MEIN FLAG SET KARTE HAIN
            add_action( 'admin_init', array( $this, 'set_activation_notice_flag' ) );
        }
    }


    // PLUGIN ACTIVATION NOTICE FLAG SET KARNE WALA FUNCTION
    public function set_activation_notice_flag() {
        update_option( 'slla_plugin_activated_notice', 'yes' );
    }

    
    // ADMIN NOTICE DISPLAY KARNE WALA FUNCTION
    public function show_admin_notice() {
        // AGAR ADMIN PANEL MEIN HAIN AUR NOTICE ABHI TAK DISPLAY NAHI HUA TOH
        if ( is_admin() && get_option( 'slla_plugin_activated_notice' ) !== 'yes' ) {
            ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e( 'Simple Limit Login Attempts plugin is now active! Configure the settings to get started.', 'simple-limit-login-attempts' ); ?>
    </p>
</div>
<?php
        }
    }
}
?>
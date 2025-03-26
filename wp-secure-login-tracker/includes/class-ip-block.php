<?php
// Ensure this file is not accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WP_Secure_Login_IP_Block {
    
    private $failed_attempts_key = 'wp_secure_failed_attempts'; // Store failed login attempts
    private $blocked_ips_key = 'wp_secure_blocked_ips'; // Store blocked IPs
    private $max_attempts = 5; // Number of attempts before blocking
    private $block_duration = 120; // Block duration in seconds (1 hour)

    // Get client IP address
    private function get_client_ip() {
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }

    // Log a failed login attempt
    public function log_failed_attempt($username) {
        $ip = $this->get_client_ip();
        $failed_attempts = get_option($this->failed_attempts_key, []);

        // If IP already has failed attempts, increase the count
        if (isset($failed_attempts[$ip])) {
            $failed_attempts[$ip]['count'] += 1;
            $failed_attempts[$ip]['last_attempt'] = time();
        } else {
            // New failed attempt
            $failed_attempts[$ip] = [
                'count' => 1,
                'last_attempt' => time(),
                'username' => $username
            ];
        }

        // If max attempts reached, block the IP
        if ($failed_attempts[$ip]['count'] >= $this->max_attempts) {
            $this->block_ip($ip);
        }

        // Update failed attempts in database
        update_option($this->failed_attempts_key, $failed_attempts);
    }

    // Block an IP address
    private function block_ip($ip) {
        $blocked_ips = get_option($this->blocked_ips_key, []);
        $blocked_ips[$ip] = time() + $this->block_duration; // Block for specified duration

        // Save blocked IPs to database
        update_option($this->blocked_ips_key, $blocked_ips);
    }

    // Check if an IP is blocked
    public function is_ip_blocked() {
        $ip = $this->get_client_ip();
        $blocked_ips = get_option($this->blocked_ips_key, []);

        // If IP is in the blocked list and still in block duration
        if (isset($blocked_ips[$ip]) && $blocked_ips[$ip] > time()) {
            return true;
        }

        // Remove expired IPs from block list
        if (isset($blocked_ips[$ip]) && $blocked_ips[$ip] <= time()) {
            unset($blocked_ips[$ip]);
            update_option($this->blocked_ips_key, $blocked_ips);
        }

        return false;
    }

    // Unblock an IP (Admin function)
    public function unblock_ip($ip) {
        $blocked_ips = get_option($this->blocked_ips_key, []);
        if (isset($blocked_ips[$ip])) {
            unset($blocked_ips[$ip]);
            update_option($this->blocked_ips_key, $blocked_ips);
            return true;
        }
        return false;
    }
}
?>
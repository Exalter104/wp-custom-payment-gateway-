=== Simple Limit Login Attempts ===
Contributors: exarth
Tags: security, login, brute force, limit login attempts
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.0.0
License: MIT
License URI: https://choosealicense.com/licenses/mit/

== Description ==
A simple plugin to limit login attempts and protect your WordPress site against brute force attacks. It tracks failed login attempts, locks out IPs after a specified number of failed attempts, and logs successful logins and lockout events for monitoring.

Key Features:
- Limit login attempts to prevent brute force attacks.
- Lock out IPs after a set number of failed attempts.
- Log successful logins and lockout events in a custom database table.
- View detailed logs in the admin dashboard.

== Installation ==
1. Upload the `simple-limit-login-attempts` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the "Limit Login Attempts" menu in the admin panel to view logs and monitor activity.

== Frequently Asked Questions ==
= How many failed attempts are allowed before a lockout? =
By default, the plugin allows 5 failed attempts before locking out an IP for 15 minutes. This can be customized in future updates.

= Where are the logs stored? =
Logs are stored in a custom database table (`wp_slla_logs`) for efficient management.

== Changelog ==
= 1.0.0 =
* Initial release with login attempt limiting, lockout, and logging features.

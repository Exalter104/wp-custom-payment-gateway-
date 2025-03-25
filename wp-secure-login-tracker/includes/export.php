<?php
global $wpdb;
$table = $wpdb->prefix . 'secure_login_logs';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="login_logs.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, array('User ID', 'Username', 'Role', 'Login Time', 'IP Address', 'User Agent'));

$results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

foreach ( $results as $row ) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
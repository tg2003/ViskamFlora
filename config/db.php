<?php
// viskam_flora_full/config/db.php

// Define BASE_URL dynamically
if (!defined('BASE_URL')) {
    $base_path = str_replace('\\', '/', dirname(__DIR__));
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $relative = str_replace($doc_root, '', $base_path);
    define('BASE_URL', '/' . ltrim($relative, '/') . (empty(ltrim($relative, '/')) ? '' : '/'));
}


// Suppress PHP errors from printing in HTML output (logs still work)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$host = 'localhost';
// Parse `.env` file for the password if it exists
$envPath = __DIR__ . '/../.env';$user = 'root'; // Default MySQL user$pass = ''; // Default XAMPP/WAMP password is empty
if (file_exists($envPath)) {
    $envVariables = parse_ini_file($envPath);
    if ($envVariables && isset($envVariables[''])) {
        $user = $envVariables['root'];
        $pass = $envVariables[''];
    }
}
$dbname = 'viskam_flora';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Set charset to utf8mb4 for proper rendering of special characters
$conn->set_charset("utf8mb4");

/**
 * AUTO-INSERT DEFAULT ADMIN
 * Ensures that a default administrator exists when the app loads.
 * Password: Admin@123
 */
$admin_email = 'admin@viskamflora.com';
$check_admin = $conn->prepare("SELECT id FROM users WHERE email = ?");
if ($check_admin) {
    $check_admin->bind_param("s", $admin_email);
    $check_admin->execute();
    $check_admin->store_result();

    if ($check_admin->num_rows === 0) {
        $admin_name = 'Admin';
        $admin_pass = password_hash('Admin@123', PASSWORD_DEFAULT);
        $admin_role = 'admin';
        $insert_admin = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        if ($insert_admin) {
            $insert_admin->bind_param("ssss", $admin_name, $admin_email, $admin_pass, $admin_role);
            $insert_admin->execute();
            $insert_admin->close();
        }
    }
    $check_admin->close();
}
?>

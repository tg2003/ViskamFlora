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
$envPath = __DIR__ . '/../.env';
$pass = ''; // Default XAMPP/WAMP password is empty
if (file_exists($envPath)) {
    $envVariables = parse_ini_file($envPath);
    if ($envVariables && isset($envVariables['DB_PASS'])) {
        $user = $envVariables['USER_NAME'];
        $pass = $envVariables['DB_PASS'];
    }
}
$dbname = 'viskam_flora';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Set charset to utf8mb4 for proper rendering of special characters
$conn->set_charset("utf8mb4");
?>

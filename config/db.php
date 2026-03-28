<?php
// viskam_flora_full/config/db.php

// Suppress PHP errors from printing in HTML output (logs still work)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP/WAMP password is empty
$dbname = 'viskam_flora';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Set charset to utf8mb4 for proper rendering of special characters
$conn->set_charset("utf8mb4");
?>

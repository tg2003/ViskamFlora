<?php
// viskam_flora_full/includes/helpers.php

// Function to format price
function format_price($amount) {
    return "LKR " . number_format($amount, 2);
}

// Function to sanitize user inputs
function sanitize_input($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Ensure user is logged in, else redirect
function require_login() {
    if (!is_logged_in()) {
        header("Location: /viskam_flora_full/auth/login_page.php");
        exit();
    }
}

// Get fallback image if file doesn't exist
function get_image_url($img_name) {
    $path = __DIR__ . '/../uploads/' . $img_name;
    if (file_exists($path) && !empty($img_name)) {
        return '/viskam_flora_full/uploads/' . $img_name;
    }
    // Fallback to placeholder service using a nice nature/floral placeholder
    return 'https://images.unsplash.com/photo-1563241527-2804ec6fc970?q=80&w=400&auto=format&fit=crop';
}
?>

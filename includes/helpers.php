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
        header("Location: /Test%20by%20antigravity/viskam_flora_full/auth/login_page.php");
        exit();
    }
}

// Get a working image URL — handles full img tags, URLs, local filenames, or empty values
function get_image_url($img_name) {

    // Curated Unsplash pools per product type (filename prefix)
    static $pools = [
        'bouquet'    => [
            'https://images.unsplash.com/photo-1563241527-2804ec6fc970?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1490750967868-88df5691cc85?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1455659817273-f96807779a8a?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1508610048659-a06b669e3321?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1487530811015-780780169e9e?q=60&w=300&auto=format&fit=crop',
        ],
        'gift_men'   => [
            'https://images.unsplash.com/photo-1536031232-e7bb4d0f52ee?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=60&w=300&auto=format&fit=crop',
        ],
        'gift_women' => [
            'https://images.unsplash.com/photo-1612817288484-6f916006741a?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1596462502278-27bfdc403348?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1526045612212-70caf35c14df?q=60&w=300&auto=format&fit=crop',
        ],
        'choc'       => [
            'https://images.unsplash.com/photo-1549007994-cb92caebd54b?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1553452118-621e1f7a8f24?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1511381939415-e44015466834?q=60&w=300&auto=format&fit=crop',
        ],
        'card'       => [
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1607344645866-009c320b63e0?q=60&w=300&auto=format&fit=crop',
        ],
        'box'        => [
            'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?q=60&w=300&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1607344645866-009c320b63e0?q=60&w=300&auto=format&fit=crop',
        ],
    ];
    $default = 'https://images.unsplash.com/photo-1563241527-2804ec6fc970?q=60&w=300&auto=format&fit=crop';

    // Empty value
    if (empty($img_name)) {
        return $default;
    }

    // Full <img> HTML tag stored in DB — extract the src
    if (strpos($img_name, '<img') !== false) {
        if (preg_match('/src=["\']([^"\']+)["\']/', $img_name, $m)) {
            $src = $m[1];
            // If the extracted src is a local relative file
            if (strpos($src, 'http') !== 0 && strpos($src, '/') !== 0) {
                return '/Test%20by%20antigravity/viskam_flora_full/' . $src;
            }
            return $src;
        }
        return $default;
    }

    // Already a full URL
    if (strpos($img_name, 'http') === 0) {
        return $img_name;
    }

    // Direct string is a local path e.g. "uploads/image.jpg"
    if (strpos($img_name, '/') !== false && strpos($img_name, 'http') === false) {
        if (strpos($img_name, '/') !== 0) {
             return '/Test%20by%20antigravity/viskam_flora_full/' . $img_name;
        }
    }

    // Local file — check uploads directory first
    $path = __DIR__ . '/../uploads/' . $img_name;
    if (file_exists($path)) {
        return '/Test%20by%20antigravity/viskam_flora_full/uploads/' . $img_name;
    }

    // Local filename like "bouquet_1.jpg", "choc_2.jpg" etc. — map to Unsplash pool
    $base = strtolower(pathinfo($img_name, PATHINFO_FILENAME)); // e.g. "bouquet_1"
    // Extract number from filename for deterministic pool selection
    preg_match('/(\d+)$/', $base, $num);
    $idx = isset($num[1]) ? ((int)$num[1] - 1) : 0;

    foreach ($pools as $prefix => $urls) {
        if (strpos($base, $prefix) === 0) {
            return $urls[$idx % count($urls)];
        }
    }

    return $default;
}

?>


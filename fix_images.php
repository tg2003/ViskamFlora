<?php
// fix_images.php — Run this ONCE from browser to fix the products table image column
// Access via: http://localhost/Test%20by%20antigravity/viskam_flora_full/fix_images.php
// DELETE this file after running!

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/db.php';

$log = [];

// Step 1: Add the image column if it doesn't exist
$col_check = $conn->query("SHOW COLUMNS FROM products LIKE 'image'");
if ($col_check->num_rows === 0) {
    if ($conn->query("ALTER TABLE products ADD COLUMN image VARCHAR(500) DEFAULT NULL")) {
        $log[] = "✅ Added 'image' column to products table.";
    } else {
        $log[] = "❌ Error adding column: " . $conn->error;
    }
} else {
    $log[] = "ℹ️ 'image' column already exists.";
    // Make it bigger in case it's VARCHAR(255) and storing long URLs
    $conn->query("ALTER TABLE products MODIFY COLUMN image VARCHAR(500) DEFAULT NULL");
    $log[] = "✅ Resized 'image' column to VARCHAR(500).";
}

// Step 2: Map beautiful Unsplash images per category
$category_images = [
    // Flower Bouquets (cat 1): use floral images
    'flower-bouquets' => [
        'https://images.unsplash.com/photo-1563241527-2804ec6fc970?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1490750967868-88df5691cc85?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1455659817273-f96807779a8a?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1487530811015-780780169e9e?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1508610048659-a06b669e3321?q=80&w=400&auto=format&fit=crop',
    ],
    // Gifts for Men (cat 2)
    'gifts-for-men' => [
        'https://images.unsplash.com/photo-1536031232-e7bb4d0f52ee?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=400&auto=format&fit=crop',
    ],
    // Gifts for Women (cat 3)
    'gifts-for-women' => [
        'https://images.unsplash.com/photo-1612817288484-6f916006741a?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1596462502278-27bfdc403348?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1526045612212-70caf35c14df?q=80&w=400&auto=format&fit=crop',
    ],
    // Chocolates (cat 4)
    'chocolates' => [
        'https://images.unsplash.com/photo-1549007994-cb92caebd54b?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1553452118-621e1f7a8f24?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1511381939415-e44015466834?q=80&w=400&auto=format&fit=crop',
    ],
    // Birthday Cards (cat 5)
    'birthday-cards' => [
        'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1607344645866-009c320b63e0?q=80&w=400&auto=format&fit=crop',
    ],
    // Gift Boxes (cat 6)
    'gift-boxes' => [
        'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?q=80&w=400&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1607344645866-009c320b63e0?q=80&w=400&auto=format&fit=crop',
    ],
];

// Step 3: Get all products and update their image to a proper Unsplash URL
$products_result = $conn->query("SELECT p.id, p.image, c.slug FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC");
if (!$products_result) {
    $log[] = "❌ Could not query products: " . $conn->error;
} else {
    $counters = [];
    $updated = 0;
    $skipped = 0;
    
    while ($row = $products_result->fetch_assoc()) {
        $product_id = $row['id'];
        $cat_slug   = $row['slug'];
        $current_img = $row['image'];
        
        // Check if already a proper URL (not an img tag, not a local file path, not null/empty)
        $is_proper_url = (!empty($current_img) 
            && strpos($current_img, 'http') === 0 
            && strpos($current_img, '<img') === false
            && strpos($current_img, 'C:\\') === false
            && strpos($current_img, '/xampp/') === false);
        
        if ($is_proper_url) {
            $skipped++;
            continue;
        }
        
        // Pick an image from the category pool
        $pool = $category_images[$cat_slug] ?? ['https://images.unsplash.com/photo-1563241527-2804ec6fc970?q=80&w=400&auto=format&fit=crop'];
        $idx = ($counters[$cat_slug] ?? 0) % count($pool);
        $counters[$cat_slug] = $idx + 1;
        $new_url = $pool[$idx];
        
        $stmt = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
        $stmt->bind_param("si", $new_url, $product_id);
        $stmt->execute();
        $updated++;
    }
    
    $log[] = "✅ Updated $updated products with proper image URLs.";
    $log[] = "ℹ️ Skipped $skipped products that already had valid URLs.";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Image Fix Script | Viskam Flora</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 60px auto; padding: 20px; }
        h1 { color: #FF1E7A; }
        .log { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .log p { margin: 6px 0; font-size: 1rem; }
        .btn { display: inline-block; margin-top: 20px; background: #FF1E7A; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🌸 Viskam Flora — Image Fix Script</h1>
    <div class="log">
        <?php foreach ($log as $line): ?>
            <p><?= htmlspecialchars($line) ?></p>
        <?php endforeach; ?>
    </div>
    <a class="btn" href="/Test%20by%20antigravity/viskam_flora_full/products/index.php">→ Go to Shop to verify</a>
    <p style="color:#999; margin-top:20px; font-size:0.85rem;">⚠️ Delete this file after running: <code>fix_images.php</code></p>
</body>
</html>

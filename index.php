<?php
// viskam_flora_full/index.php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

// Fetch Categories
$cat_query = "SELECT * FROM categories ORDER BY id ASC";
$categories = $conn->query($cat_query);

// Fetch Featured Products
$feat_query = "SELECT p.*, c.name as category_name 
               FROM products p 
               LEFT JOIN categories c ON p.category_id = c.id 
               WHERE p.is_featured = 1 
               LIMIT 8";
$featured_products = $conn->query($feat_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viskam Flora | Premium Flowers & Gifts</title>
    <meta name="description" content="Viskam Flora provides fresh flowers, premium gifts, chocolates, and bespoke arrangements for your loved ones.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/viskam_flora_full/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <!-- Hero Section -->
    <header class="hero">
        <div class="container">
            <h1>Say It With Elegance</h1>
            <p>Discover our curated collection of fresh blooms, premium chocolates, and thoughtful gifts tailored for every special moment.</p>
            <a href="/viskam_flora_full/products/index.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 15px 30px;">Shop Now</a>
            <div style="margin-top:20px;">
                <a href="/viskam_flora_full/orders/wedding_page.php" style="color:var(--primary-color); font-weight:bold; text-decoration:underline;">Or explore our Wedding & Event arrangements ➞</a>
            </div>
        </div>
    </header>

    <!-- Categories Section -->
    <section class="categories-preview container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="category-grid">
            <?php while($cat = $categories->fetch_assoc()): ?>
                <a href="/viskam_flora_full/products/index.php?cat=<?= $cat['slug'] ?>" class="category-item">
                    <div class="category-circle">
                        <!-- Use a generic floral placeholder based on category name -->
                        <img src="https://source.unsplash.com/150x150/?<?= urlencode($cat['name']) ?>,floral" alt="<?= htmlspecialchars($cat['name']) ?>" onerror="this.src='https://images.unsplash.com/photo-1563241527-2804ec6fc970?q=80&w=150&auto=format&fit=crop'">
                    </div>
                    <h4><?= htmlspecialchars($cat['name']) ?></h4>
                </a>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="products-section container" style="padding-top:20px;">
        <h2 class="section-title" style="margin-bottom:20px;">Hot & Featured</h2>
        <p class="text-center mb-4 text-muted">Handpicked favorites loved by our customers.</p>
        
        <div class="product-grid">
            <?php while($product = $featured_products->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-badge">Hot</div>
                    <img src="<?= htmlspecialchars(get_image_url($product['image'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                    
                    <div class="product-info">
                        <span style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;"><?= htmlspecialchars($product['category_name']) ?></span>
                        <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-price"><?= format_price($product['price']) ?></p>
                        
                        <div class="product-actions">
                            <a href="/viskam_flora_full/products/detail.php?id=<?= $product['id'] ?>" class="btn btn-outline" style="flex: 1;">View Details</a>
                            <form action="/viskam_flora_full/cart/cart_page.php" method="POST" style="flex: 1; display:flex;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-primary" style="width:100%;">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>

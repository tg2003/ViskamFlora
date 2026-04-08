<?php
// viskam_flora_full/products/index.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$cat_slug = isset($_GET['cat']) ? sanitize_input($conn, $_GET['cat']) : '';
$search_query = isset($_GET['q']) ? sanitize_input($conn, $_GET['q']) : '';

// Base query
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";

$params = [];
$types = "";

if ($cat_slug) {
    $query .= " AND c.slug = ?";
    $params[] = $cat_slug;
    $types .= "s";
}

if ($search_query) {
    $query .= " AND (p.name LIKE ? OR p.short_desc LIKE ?)";
    $like_q = "%$search_query%";
    $params[] = $like_q;
    $params[] = $like_q;
    $types .= "ss";
}

$query .= " ORDER BY p.id DESC";

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch categories for sidebar
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | Viskam Flora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- Page Banner Strip -->
    <div style="background: linear-gradient(135deg, #FF1E7A 0%, #c0134f 100%); color:#fff; padding: 22px 0; margin-bottom: 0;">
        <div class="container" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1 style="font-size:1.8rem; margin:0; color:#fff;">
                    <?php
                        if($search_query) echo "Search: <em style='font-style:italic;'>" . htmlspecialchars($search_query) . "</em>";
                        elseif($cat_slug) echo htmlspecialchars(str_replace('-', ' ', ucwords($cat_slug)));
                        else echo "Shop All";
                    ?>
                </h1>
                <p style="margin:4px 0 0; opacity:0.85; font-size:0.9rem;">Handpicked flowers &amp; gifts, delivered with love 🌸</p>
            </div>
            <nav style="font-size:0.85rem; opacity:0.85;">
                <a href="<?= BASE_URL ?>index.php" style="color:#fff;">Home</a>
                <span style="margin:0 8px;">›</span>
                <span>Shop</span>
            </nav>
        </div>
    </div>

    <div class="container products-layout" style="margin: 40px auto; min-height: 60vh;">
        
        <!-- Sidebar Filters -->
        <aside class="products-sidebar">
            <div style="background:#fff; padding:24px; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm); position:sticky; top:100px; border-top:4px solid var(--primary-color);">
                <h3 style="margin-bottom:18px; font-size:1.1rem; border-bottom:2px solid var(--accent-color); padding-bottom:10px; color:var(--text-main); font-family:'Outfit',sans-serif;">Categories</h3>
                <ul class="products-cat-list" style="list-style:none; padding:0;">
                    <li style="margin-bottom:6px;">
                        <a href="index.php" style="display:block; padding:8px 12px; border-radius:8px; color: <?= !$cat_slug ? '#fff' : 'var(--text-main)' ?>; background: <?= !$cat_slug ? 'var(--primary-color)' : 'transparent' ?>; font-weight: <?= !$cat_slug ? '700' : '400' ?>; transition:all 0.2s;">All Products</a>
                    </li>
                    <?php while($c = $categories->fetch_assoc()): ?>
                        <li style="margin-bottom:6px;">
                            <a href="?cat=<?= $c['slug'] ?><?= $search_query ? '&q='.urlencode($search_query) : '' ?>" 
                               style="display:block; padding:8px 12px; border-radius:8px; color: <?= $cat_slug === $c['slug'] ? '#fff' : 'var(--text-main)' ?>; background: <?= $cat_slug === $c['slug'] ? 'var(--primary-color)' : 'transparent' ?>; font-weight: <?= $cat_slug === $c['slug'] ? '700' : '400' ?>; transition:all 0.2s;">
                                <?= htmlspecialchars($c['name']) ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </aside>

        <!-- Product List -->
        <main class="products-main">
            <div style="margin-bottom: 24px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid var(--accent-color); padding-bottom:14px;">
                <h2 style="font-size:1.6rem; margin:0; color:var(--text-main);">
                    <?php 
                        if($search_query) echo "Results for &lsquo;" . htmlspecialchars($search_query) . "&rsquo;";
                        elseif($cat_slug) echo htmlspecialchars(str_replace('-', ' ', ucwords($cat_slug)));
                        else echo "All Products";
                    ?>
                </h2>
                <span style="background:var(--accent-color); color:var(--primary-color); font-weight:700; padding:5px 14px; border-radius:50px; font-size:0.85rem;"><?= $result->num_rows ?> items found</span>
            </div>

            <?php if($result->num_rows > 0): ?>
                <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
                    <?php while($product = $result->fetch_assoc()): ?>
                        <div class="product-card">
                            <a href="detail.php?id=<?= $product['id'] ?>" style="display:block; overflow:hidden; border-radius:var(--border-radius-sm);">
                                <img src="<?= htmlspecialchars(get_image_url($product['image'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image" loading="lazy" style="height:200px; transition:transform 0.4s ease;">
                            </a>
                            <div class="product-info">
                                <span style="font-size:0.75rem; color:var(--primary-color); text-transform:uppercase; letter-spacing:1px; font-weight:600;"><?= htmlspecialchars($product['category_name']) ?></span>
                                <a href="detail.php?id=<?= $product['id'] ?>">
                                    <h3 class="product-title" style="font-size:1rem; margin:6px 0;"><?= htmlspecialchars($product['name']) ?></h3>
                                </a>
                                <p class="product-price" style="font-size:1.15rem; margin-bottom:12px;"><?= format_price($product['price']) ?></p>
                                
                                <div class="product-actions" style="margin-top:auto;">
                                    <form action="<?= BASE_URL ?>cart/cart_page.php" method="POST" style="width:100%;">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit" class="btn btn-primary" style="width: 100%; padding:10px; border-radius:50px;">🛒 Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="background:#fff; padding:50px; text-align:center; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm);">
                    <div style="font-size:3rem; margin-bottom:15px;">🌸</div>
                    <h3 style="color:var(--text-muted);">No products found matching your criteria.</h3>
                    <a href="index.php" class="btn btn-outline" style="margin-top:20px;">Clear Filters</a>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>


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
    <link rel="stylesheet" href="/viskam_flora_full/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container" style="display: flex; gap: 40px; margin: 40px auto; min-height: 60vh;">
        
        <!-- Sidebar Filters -->
        <aside style="width: 250px; flex-shrink: 0;">
            <div style="background:#fff; padding:20px; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm); position:sticky; top:100px;">
                <h3 style="margin-bottom:20px; font-size:1.2rem; border-bottom:1px solid #eee; padding-bottom:10px;">Categories</h3>
                <ul style="list-style:none;">
                    <li style="margin-bottom:10px;">
                        <a href="index.php" style="color: <?= !$cat_slug ? 'var(--primary-color)' : 'var(--text-main)' ?>; font-weight: <?= !$cat_slug ? 'bold' : 'normal' ?>;">All Products</a>
                    </li>
                    <?php while($c = $categories->fetch_assoc()): ?>
                        <li style="margin-bottom:10px;">
                            <a href="?cat=<?= $c['slug'] ?><?= $search_query ? '&q='.urlencode($search_query) : '' ?>" 
                               style="color: <?= $cat_slug === $c['slug'] ? 'var(--primary-color)' : 'var(--text-main)' ?>; font-weight: <?= $cat_slug === $c['slug'] ? 'bold' : 'normal' ?>;">
                                <?= htmlspecialchars($c['name']) ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </aside>

        <!-- Product List -->
        <main style="flex-grow: 1;">
            <div style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
                <h2>
                    <?php 
                        if($search_query) echo "Search results for '" . htmlspecialchars($search_query) . "'";
                        elseif($cat_slug) {
                            // Quick way to get category name from result if exists, or just show slug
                            $c_name = str_replace('-', ' ', ucwords($cat_slug)); 
                            echo htmlspecialchars($c_name);
                        }
                        else echo "All Products";
                    ?>
                </h2>
                <span class="text-muted"><?= $result->num_rows ?> items found</span>
            </div>

            <?php if($result->num_rows > 0): ?>
                <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
                    <?php while($product = $result->fetch_assoc()): ?>
                        <div class="product-card">
                            <a href="detail.php?id=<?= $product['id'] ?>" style="display:block;">
                                <img src="<?= htmlspecialchars(get_image_url($product['image'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image" style="height:200px;">
                            </a>
                            <div class="product-info">
                                <span style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase;"><?= htmlspecialchars($product['category_name']) ?></span>
                                <a href="detail.php?id=<?= $product['id'] ?>">
                                    <h3 class="product-title" style="font-size:1rem; margin:10px 0;"><?= htmlspecialchars($product['name']) ?></h3>
                                </a>
                                <p class="product-price" style="font-size:1.1rem;"><?= format_price($product['price']) ?></p>
                                
                                <div class="product-actions" style="margin-top:auto;">
                                    <!-- Direct add to cart -->
                                    <form action="/viskam_flora_full/cart/cart_page.php" method="POST" style="width:100%;">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit" class="btn btn-primary" style="width: 100%; padding:8px;">Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="background:#fff; padding:50px; text-align:center; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm);">
                    <h3 style="color:var(--text-muted);">No products found matching your criteria.</h3>
                    <a href="index.php" class="btn btn-outline" style="margin-top:20px;">Clear Filters</a>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

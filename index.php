<?php
// index.php
$pageTitle = 'Viskam Flora – Fresh Flowers & Gifts';
require_once __DIR__ . '/includes/navbar.php';
$db = getDB();

// Featured products
$featured = $db->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 AND p.status = 'active' LIMIT 8")->fetch_all(MYSQLI_ASSOC);

// Categories
$categories = $db->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
?>

<div class="hero">
    <h1>🌸 Fresh Flowers & Gifts</h1>
    <p>Handcrafted bouquets delivered with love across Sri Lanka</p>
    <a href="/products/index.php" class="btn btn-primary" style="font-size:1.05rem;padding:12px 28px;">Shop Now</a>
    &nbsp;
    <a href="./orders/wedding_page.php" class="btn btn-secondary" style="font-size:1.05rem;padding:12px 28px;">Wedding Packages</a>
</div>

<h2 style="color:#2d5016;margin-bottom:16px;">Shop by Category</h2>
<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:30px;">
    <?php foreach ($categories as $cat): ?>
        <a href="/viskam_flora_full/products/index.php?category=<?= $cat['id'] ?>" class="btn btn-secondary"><?= htmlspecialchars($cat['name']) ?></a>
    <?php endforeach; ?>
</div>

<h2 style="color:#2d5016;margin-bottom:4px;">Featured Products</h2>
<div class="product-grid">
    <?php foreach ($featured as $p): ?>
        <div class="product-card">
            <div class="img-placeholder" style="height:180px;"><?= $p['image'] ? "<img src='" . htmlspecialchars($p['image']) . "' alt=''>" : '🌺' ?></div>
            <div class="info">
                <h3><?= htmlspecialchars($p['name']) ?></h3>
                <div class="price"><?= formatPrice($p['price']) ?></div>
                <div class="product-action" style="display:flex;gap:8px;align-items:center;margin-top:10px;">
                    <div class="qty-group" style="display:flex;align-items:center;border:1px solid #ccc;border-radius:6px;overflow:hidden;">
                        <button class="qty-btn btn btn-sm btn-secondary" data-action="dec" style="border-radius:0;">-</button>
                        <input class="qty-input" type="number" value="1" min="1" style="width:40px;border:none;text-align:center;padding:5px 0;">
                        <button class="qty-btn btn btn-sm btn-secondary" data-action="inc" style="border-radius:0;">+</button>
                    </div>
                    <button class="btn btn-primary btn-sm btn-add-cart" data-id="<?= $p['id'] ?>">Add to Cart</button>
                </div>
                <a href="./products/detail.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" style="margin-top:8px;width:100%;">View Details</a>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($featured)): ?>
        <p class="no-data" style="grid-column:1/-1;">No featured products yet. <a href="/products/index.php">Browse all products</a></p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
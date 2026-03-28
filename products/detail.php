<?php
// viskam_flora_full/products/detail.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch Product Details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$product = $result->fetch_assoc();

// Fetch Related Products (same category)
$rel_stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$rel_stmt->bind_param("ii", $product['category_id'], $id);
$rel_stmt->execute();
$related_products = $rel_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | Viskam Flora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/viskam_flora_full/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- Breadcrumb -->
    <div class="container" style="margin-top: 20px; margin-bottom: 20px; font-size: 0.9rem; color: var(--text-muted);">
        <a href="/viskam_flora_full/index.php">Home</a> / 
        <a href="index.php?cat=<?= htmlspecialchars($product['category_slug']) ?>"><?= htmlspecialchars($product['category_name']) ?></a> / 
        <span style="color:var(--text-main); font-weight:600;"><?= htmlspecialchars($product['name']) ?></span>
    </div>

    <!-- Product Detail Area -->
    <div class="container" style="display:flex; flex-wrap:wrap; gap:50px; margin-bottom:80px;">
        <!-- Image Gallery (Simplified to single image for now) -->
        <div style="flex:1; min-width:300px;">
            <img src="<?= htmlspecialchars(get_image_url($product['image'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%; height:auto; border-radius:var(--border-radius-lg); box-shadow:var(--shadow-md);">
        </div>
        
        <!-- Product Info -->
        <div style="flex:1; min-width:300px;">
            <h1 style="font-size: 2.5rem; margin-bottom:10px;"><?= htmlspecialchars($product['name']) ?></h1>
            <p style="font-size:1.8rem; color:var(--primary-color); font-weight:bold; margin-bottom:20px;"><?= format_price($product['price']) ?></p>
            
            <p style="font-size:1.1rem; color:var(--text-muted); margin-bottom:30px; line-height:1.8;">
                <?= htmlspecialchars($product['short_desc']) ?>
            </p>

            <form action="/viskam_flora_full/cart/cart_page.php" method="POST" style="background:#fff; padding:30px; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm);">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div style="display:flex; align-items:flex-end; gap:20px;">
                    <div>
                        <label style="display:block; margin-bottom:10px; font-weight:bold;">Quantity</label>
                        <div class="qty-control" style="background:#f9f9f9; padding:5px; border-radius:5px; border:1px solid #ddd;">
                            <button type="button" class="qty-btn" onclick="updateDetailQty(-1)">-</button>
                            <input type="number" name="qty" id="detail_qty" value="1" min="1" max="<?= $product['stock_qty'] ?>" class="qty-input" style="border:none; background:transparent; font-size:1.1rem; width:40px;">
                            <button type="button" class="qty-btn" onclick="updateDetailQty(1)">+</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="flex-grow:1; padding:15px; font-size:1.1rem; display:flex; justify-content:center; align-items:center; gap:10px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        Add to Cart
                    </button>
                </div>
                
                <?php if($product['stock_qty'] < 5): ?>
                    <p style="color:#d9534f; margin-top:15px; font-size:0.9rem;"><strong>Hurry!</strong> Only <?= $product['stock_qty'] ?> left in stock.</p>
                <?php else: ?>
                    <p style="color:#5cb85c; margin-top:15px; font-size:0.9rem;">✓ In Stock & Ready to Deliver</p>
                <?php endif; ?>
            </form>

            <div style="margin-top:40px;">
                <h3 style="font-size:1.2rem; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px;">Product Description</h3>
                <div style="color:var(--text-main); line-height:1.7;">
                    <?php 
                        $long = $product['long_desc'];
                        if(empty($long)) {
                            // Generate generic long description if missing
                            echo "<p>Treat your loved ones to this beautifully crafted arrangement. At Viskam Flora, we source only the freshest components to ensure your gifts arrive in pristine condition, creating unforgettable memories.</p>";
                        } else {
                            echo nl2br(htmlspecialchars($long));
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if($related_products->num_rows > 0): ?>
    <section class="products-section container" style="padding-top:0; border-top:1px solid #eee; margin-top:50px;">
        <h2 class="section-title" style="margin-top:50px;">You May Also Like</h2>
        <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
            <?php while($rel = $related_products->fetch_assoc()): ?>
                <div class="product-card">
                    <a href="detail.php?id=<?= $rel['id'] ?>">
                        <img src="<?= htmlspecialchars(get_image_url($rel['image'])) ?>" class="product-image" style="height:180px;">
                        <h3 class="product-title" style="font-size:1rem; margin-top:10px;"><?= htmlspecialchars($rel['name']) ?></h3>
                        <p class="product-price" style="font-size:1rem;"><?= format_price($rel['price']) ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

    <script>
        function updateDetailQty(change) {
            const input = document.getElementById('detail_qty');
            let newQty = parseInt(input.value) + change;
            let max = parseInt(input.getAttribute('max'));
            if(newQty >= 1 && newQty <= max) {
                input.value = newQty;
            }
        }
    </script>
    <?php include __DIR__ . '/../includes/footer.php'; ?>

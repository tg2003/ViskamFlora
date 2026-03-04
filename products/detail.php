<?php
// products/detail.php
require_once __DIR__ . '/../includes/helpers.php';
$db = getDB();
$id = sanitizeInt($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'active'");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) { redirect('/products/index.php', 'Product not found.', 'danger'); }

$pageTitle = htmlspecialchars($product['name']) . ' – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';

// Related products
$related = $db->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 'active' LIMIT 4");
$related->bind_param('ii', $product['category_id'], $id);
$related->execute();
$relatedProducts = $related->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<p style="color:#888;margin-bottom:16px;"><a href="/products/index.php">Shop</a> › <?= htmlspecialchars($product['cat_name'] ?? '') ?> › <?= htmlspecialchars($product['name']) ?></p>

<div class="product-detail">
    <div>
        <?php if ($product['image']): ?>
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <?php else: ?>
            <div class="img-placeholder" style="height:320px;border-radius:8px;font-size:5rem;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#c8e6c9,#a5d6a7);">🌺</div>
        <?php endif; ?>
    </div>
    <div>
        <small style="color:#888;"><?= htmlspecialchars($product['cat_name'] ?? '') ?></small>
        <h1 style="color:#2d5016;margin:8px 0;"><?= htmlspecialchars($product['name']) ?></h1>
        <div style="font-size:1.6rem;font-weight:bold;color:#2d5016;margin:12px 0;"><?= formatPrice($product['price']) ?></div>
        <p style="color:#555;line-height:1.7;margin-bottom:16px;"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <p style="margin-bottom:16px;">
            Stock: <strong style="color:<?= $product['stock'] > 0 ? '#2d5016' : '#c0392b' ?>;">
                <?= $product['stock'] > 0 ? $product['stock'] . ' available' : 'Out of Stock' ?>
            </strong>
        </p>
        <?php if ($product['stock'] > 0): ?>
        <div class="product-action" style="display:flex;gap:10px;align-items:center;margin-bottom:16px;">
            <div class="qty-group" style="display:flex;align-items:center;border:1px solid #ccc;border-radius:6px;overflow:hidden;">
                <button class="qty-btn btn btn-secondary" data-action="dec" style="border-radius:0;padding:9px 14px;">-</button>
                <input class="qty-input" type="number" value="1" min="1" max="<?= $product['stock'] ?>" style="width:55px;border:none;text-align:center;padding:9px 0;font-size:1rem;">
                <button class="qty-btn btn btn-secondary" data-action="inc" style="border-radius:0;padding:9px 14px;">+</button>
            </div>
            <button class="btn btn-primary btn-add-cart" data-id="<?= $product['id'] ?>" style="flex:1;">🛒 Add to Cart</button>
        </div>
        <?php endif; ?>
        <a href="/orders/checkout.php?buy_now=<?= $product['id'] ?>" class="btn btn-warning" style="width:100%;text-align:center;display:block;">Buy Now</a>
    </div>
</div>

<?php if (!empty($relatedProducts)): ?>
<h3 style="margin:30px 0 12px;color:#2d5016;">Related Products</h3>
<div class="product-grid">
    <?php foreach ($relatedProducts as $rp): ?>
    <div class="product-card">
        <div class="img-placeholder" style="height:150px;"><?= $rp['image'] ? "<img src='".htmlspecialchars($rp['image'])."' alt='' style='width:100%;height:150px;object-fit:cover;'>" : '🌺' ?></div>
        <div class="info">
            <h3><?= htmlspecialchars($rp['name']) ?></h3>
            <div class="price"><?= formatPrice($rp['price']) ?></div>
            <a href="/products/detail.php?id=<?= $rp['id'] ?>" class="btn btn-secondary btn-sm" style="margin-top:8px;display:block;text-align:center;">View</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

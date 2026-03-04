<?php
// products/index.php
$pageTitle = 'Shop – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
$db = getDB();

$catId   = sanitizeInt($_GET['category'] ?? 0);
$search  = sanitize($_GET['search'] ?? '');
$page    = max(1, sanitizeInt($_GET['page'] ?? 1));
$perPage = 12;
$offset  = ($page - 1) * $perPage;

// Build WHERE
$where  = "p.status = 'active'";
$params = [];
$types  = '';

if ($catId) {
    $where .= " AND p.category_id = ?";
    $params[] = $catId;
    $types   .= 'i';
}
if ($search) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $like     = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

// Count
$countStmt = $db->prepare("SELECT COUNT(*) as total FROM products p WHERE $where");
if ($params) { $countStmt->bind_param($types, ...$params); }
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];

// Fetch products
$stmt = $db->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $where ORDER BY p.created_at DESC LIMIT ? OFFSET ?");
$params[] = $perPage; $params[] = $offset; $types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Categories for filter
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<h2 class="page-title">Shop All Products</h2>

<!-- Filters -->
<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;align-items:center;">
    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search flowers..." style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;">
        <select name="category" style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $catId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="/products/index.php" class="btn btn-secondary">Clear</a>
    </form>
    <span style="margin-left:auto;color:#666;font-size:.9rem;"><?= $total ?> products found</span>
</div>

<div class="product-grid">
    <?php foreach ($products as $p): ?>
    <div class="product-card">
        <div class="img-placeholder" style="height:180px;"><?= $p['image'] ? "<img src='".htmlspecialchars($p['image'])."' alt='' style='width:100%;height:180px;object-fit:cover;'>" : '🌺' ?></div>
        <div class="info">
            <small style="color:#888;"><?= htmlspecialchars($p['cat_name'] ?? '') ?></small>
            <h3><?= htmlspecialchars($p['name']) ?></h3>
            <div class="price"><?= formatPrice($p['price']) ?></div>
            <small style="color:<?= $p['stock'] > 0 ? '#2d5016' : '#c0392b' ?>;">
                <?= $p['stock'] > 0 ? "In Stock ({$p['stock']})" : 'Out of Stock' ?>
            </small>
            <div class="product-action" style="display:flex;gap:8px;align-items:center;margin-top:10px;">
                <div class="qty-group" style="display:flex;align-items:center;border:1px solid #ccc;border-radius:6px;overflow:hidden;">
                    <button class="qty-btn btn btn-sm btn-secondary" data-action="dec" style="border-radius:0;">-</button>
                    <input class="qty-input" type="number" value="1" min="1" max="<?= $p['stock'] ?>" style="width:40px;border:none;text-align:center;padding:5px 0;">
                    <button class="qty-btn btn btn-sm btn-secondary" data-action="inc" style="border-radius:0;">+</button>
                </div>
                <?php if ($p['stock'] > 0): ?>
                    <button class="btn btn-primary btn-sm btn-add-cart" data-id="<?= $p['id'] ?>">+ Cart</button>
                <?php else: ?>
                    <button class="btn btn-secondary btn-sm" disabled>Sold Out</button>
                <?php endif; ?>
            </div>
            <a href="/products/detail.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" style="margin-top:8px;width:100%;display:block;text-align:center;">View Details</a>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($products)): ?>
        <p class="no-data" style="grid-column:1/-1;">No products found.</p>
    <?php endif; ?>
</div>

<?php
$baseUrl = '/products/index.php?' . http_build_query(array_filter(['search' => $search, 'category' => $catId]));
echo paginate($total, $page, $perPage, $baseUrl . '&');
require_once __DIR__ . '/../includes/footer.php';
?>

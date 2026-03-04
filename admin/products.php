<?php
// admin/products.php
$pageTitle = 'Manage Products – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
requireAdmin();
$db = getDB();

$products   = $db->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Editing?
$editing = null;
if (isset($_GET['edit'])) {
    $eid   = sanitizeInt($_GET['edit']);
    $estmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $estmt->bind_param('i', $eid);
    $estmt->execute();
    $editing = $estmt->get_result()->fetch_assoc();
}
?>

<div class="admin-layout">
    <div class="admin-sidebar">
        <ul>
            <li><a href="/admin/dashboard.php">📊 Dashboard</a></li>
            <li><a href="/admin/products.php" class="active">📦 Products</a></li>
            <li><a href="/admin/orders.php">🛍️ Orders</a></li>
            <li><a href="/admin/users.php">👥 Users</a></li>
            <li><a href="/admin/arrangements.php">💐 Wedding</a></li>
            <li><hr></li>
            <li><a href="/index.php">🌸 View Site</a></li>
            <li><a href="/auth/logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="admin-main">
        <h2 class="page-title">Manage Products</h2>

        <!-- Add / Edit Form -->
        <div class="card mb-2">
            <div class="card-body">
                <h3 style="margin-bottom:16px;color:#2d5016;"><?= $editing ? 'Edit Product' : 'Add New Product' ?></h3>
                <form method="POST" action="/products/products_backend.php">
                    <?php csrfField(); ?>
                    <input type="hidden" name="action" value="<?= $editing ? 'update' : 'add' ?>">
                    <?php if ($editing): ?><input type="hidden" name="id" value="<?= $editing['id'] ?>"><?php endif; ?>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group">
                            <label>Product Name *</label>
                            <input type="text" name="name" required value="<?= htmlspecialchars($editing['name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id">
                                <option value="">Uncategorized</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($editing['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Price (LKR) *</label>
                            <input type="number" name="price" step="0.01" min="0" required value="<?= $editing['price'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock" min="0" value="<?= $editing['stock'] ?? 0 ?>">
                        </div>
                        <div class="form-group">
                            <label>Image URL</label>
                            <input type="text" name="image" placeholder="https://..." value="<?= htmlspecialchars($editing['image'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="active" <?= ($editing['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($editing['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:6px;font-weight:normal;">
                            <input type="checkbox" name="is_featured" value="1" <?= ($editing['is_featured'] ?? 0) ? 'checked' : '' ?>>
                            Featured on homepage
                        </label>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary"><?= $editing ? 'Update Product' : 'Add Product' ?></button>
                        <?php if ($editing): ?><a href="/admin/products.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <table class="table">
            <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['name']) ?><?= $p['is_featured'] ? ' ⭐' : '' ?></td>
                    <td><?= htmlspecialchars($p['cat_name'] ?? '–') ?></td>
                    <td><?= formatPrice($p['price']) ?></td>
                    <td><?= $p['stock'] ?></td>
                    <td><span class="badge <?= $p['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td style="display:flex;gap:4px;flex-wrap:wrap;">
                        <a href="/admin/products.php?edit=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="/products/products_backend.php?action=toggle_status&id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">Toggle</a>
                        <form method="POST" action="/products/products_backend.php" style="display:inline;">
                            <?php csrfField(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete '<?= htmlspecialchars($p['name']) ?>'?">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

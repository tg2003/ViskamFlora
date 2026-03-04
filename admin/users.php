<?php
// admin/users.php
$pageTitle = 'Manage Users – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
requireAdmin();
$db = getDB();

$page    = max(1, sanitizeInt($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;
$search  = sanitize($_GET['search'] ?? '');

$where  = '1=1';
$params = [];
$types  = '';
if ($search) {
    $where    = 'name LIKE ? OR email LIKE ?';
    $like     = "%$search%";
    $params[] = $like; $params[] = $like;
    $types   .= 'ss';
}

$cnt = $db->prepare("SELECT COUNT(*) as c FROM users WHERE $where");
if ($params) $cnt->bind_param($types, ...$params);
$cnt->execute();
$total = $cnt->get_result()->fetch_assoc()['c'];

$params[] = $perPage; $params[] = $offset; $types .= 'ii';
$stmt = $db->prepare("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count FROM users u WHERE $where ORDER BY u.created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="admin-layout">
    <div class="admin-sidebar">
        <ul>
            <li><a href="/admin/dashboard.php">📊 Dashboard</a></li>
            <li><a href="/admin/products.php">📦 Products</a></li>
            <li><a href="/admin/orders.php">🛍️ Orders</a></li>
            <li><a href="/admin/users.php" class="active">👥 Users</a></li>
            <li><a href="/admin/arrangements.php">💐 Wedding</a></li>
            <li><hr></li>
            <li><a href="/index.php">🌸 View Site</a></li>
            <li><a href="/auth/logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="admin-main">
        <h2 class="page-title">Manage Users</h2>

        <form method="GET" style="display:flex;gap:8px;margin-bottom:16px;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email..." style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;flex:1;">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="/admin/users.php" class="btn btn-secondary">Clear</a>
        </form>

        <table class="table">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Orders</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['phone'] ?? '–') ?></td>
                    <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-danger' : 'badge-info' ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= $u['order_count'] ?></td>
                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <form method="POST" action="/admin/admin_backend.php" style="display:inline;">
                            <?php csrfField(); ?>
                            <input type="hidden" name="action" value="toggle_role">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-warning btn-sm" data-confirm="Change role for <?= htmlspecialchars($u['name']) ?>?"><?= $u['role'] === 'admin' ? 'Make Customer' : 'Make Admin' ?></button>
                        </form>
                        <form method="POST" action="/admin/admin_backend.php" style="display:inline;">
                            <?php csrfField(); ?>
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete user <?= htmlspecialchars($u['name']) ?>?">Delete</button>
                        </form>
                        <?php else: ?>
                            <span style="color:#888;font-size:.85rem;">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr><td colspan="8" class="no-data">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php echo paginate($total, $page, $perPage, '/admin/users.php?' . ($search ? "search=$search&" : '')); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

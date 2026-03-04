<?php
// admin/orders.php
$pageTitle = 'Manage Orders – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
requireAdmin();
$db = getDB();

$statusFilter = sanitize($_GET['status'] ?? '');
$page    = max(1, sanitizeInt($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

$where  = '1=1';
$params = [];
$types  = '';
if ($statusFilter) {
    $where  = 'o.status = ?';
    $params[] = $statusFilter;
    $types   .= 's';
}

$cntStmt = $db->prepare("SELECT COUNT(*) as c FROM orders o WHERE $where");
if ($params) $cntStmt->bind_param($types, ...$params);
$cntStmt->execute();
$total = $cntStmt->get_result()->fetch_assoc()['c'];

$params[] = $perPage; $params[] = $offset; $types .= 'ii';
$stmt = $db->prepare("SELECT o.*, u.name as customer FROM orders o JOIN users u ON o.user_id = u.id WHERE $where ORDER BY o.created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$statusColors = ['pending'=>'badge-warning','confirmed'=>'badge-info','processing'=>'badge-info','shipped'=>'badge-secondary','delivered'=>'badge-success','cancelled'=>'badge-danger'];
$allStatuses  = ['pending','confirmed','processing','shipped','delivered','cancelled'];
?>

<div class="admin-layout">
    <div class="admin-sidebar">
        <ul>
            <li><a href="/admin/dashboard.php">📊 Dashboard</a></li>
            <li><a href="/admin/products.php">📦 Products</a></li>
            <li><a href="/admin/orders.php" class="active">🛍️ Orders</a></li>
            <li><a href="/admin/users.php">👥 Users</a></li>
            <li><a href="/admin/arrangements.php">💐 Wedding</a></li>
            <li><hr></li>
            <li><a href="/index.php">🌸 View Site</a></li>
            <li><a href="/auth/logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="admin-main">
        <h2 class="page-title">Manage Orders</h2>

        <!-- Status filter -->
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
            <a href="/admin/orders.php" class="btn btn-sm <?= !$statusFilter ? 'btn-primary' : 'btn-secondary' ?>">All (<?= $db->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'] ?>)</a>
            <?php foreach ($allStatuses as $s): ?>
                <?php $c = $db->query("SELECT COUNT(*) as c FROM orders WHERE status='$s'")->fetch_assoc()['c']; ?>
                <a href="/admin/orders.php?status=<?= $s ?>" class="btn btn-sm <?= $statusFilter === $s ? 'btn-primary' : 'btn-secondary' ?>"><?= ucfirst($s) ?> (<?= $c ?>)</a>
            <?php endforeach; ?>
        </div>

        <table class="table">
            <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Delivery Date</th><th>Status</th><th>Payment</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['customer']) ?></td>
                    <td><?= formatPrice($o['total_amount']) ?></td>
                    <td><?= htmlspecialchars($o['delivery_date']) ?></td>
                    <td><span class="badge <?= $statusColors[$o['status']] ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td><span class="badge <?= $o['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>"><?= ucfirst($o['payment_status']) ?></span></td>
                    <td><a href="/orders/order_detail.php?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="no-data">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php echo paginate($total, $page, $perPage, '/admin/orders.php?' . ($statusFilter ? "status=$statusFilter&" : '')); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

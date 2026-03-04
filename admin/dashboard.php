<?php
// admin/dashboard.php
$pageTitle = 'Admin Dashboard – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
requireAdmin();
$db = getDB();

// Stats
$stats = [
    'Total Orders'    => $db->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'],
    'Pending Orders'  => $db->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'],
    'Total Products'  => $db->query("SELECT COUNT(*) as c FROM products WHERE status='active'")->fetch_assoc()['c'],
    'Total Users'     => $db->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c'],
    'Revenue (LKR)'   => number_format($db->query("SELECT COALESCE(SUM(total_amount),0) as t FROM orders WHERE status='delivered'")->fetch_assoc()['t'], 2),
    'Wedding Inquiries' => $db->query("SELECT COUNT(*) as c FROM wedding_arrangements WHERE status='new'")->fetch_assoc()['c'],
];

// Recent orders
$recentOrders = $db->query("SELECT o.id, o.status, o.total_amount, o.created_at, u.name as customer FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$statusColors = ['pending'=>'badge-warning','confirmed'=>'badge-info','processing'=>'badge-info','shipped'=>'badge-secondary','delivered'=>'badge-success','cancelled'=>'badge-danger'];
?>

<div class="admin-layout">
    <div class="admin-sidebar">
        <ul>
            <li><a href="/admin/dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="/admin/products.php">📦 Products</a></li>
            <li><a href="/admin/orders.php">🛍️ Orders</a></li>
            <li><a href="/admin/users.php">👥 Users</a></li>
            <li><a href="/admin/arrangements.php">💐 Wedding</a></li>
            <li><hr></li>
            <li><a href="/index.php">🌸 View Site</a></li>
            <li><a href="/auth/logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="admin-main">
        <h2 class="page-title">Dashboard</h2>

        <div class="stats-grid">
            <?php foreach ($stats as $label => $val): ?>
            <div class="stat-card">
                <div class="stat-num"><?= $val ?></div>
                <div class="stat-label"><?= $label ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <h3 style="color:#2d5016;margin-bottom:12px;">Recent Orders</h3>
        <table class="table">
            <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td>#<?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['customer']) ?></td>
                    <td><?= formatPrice($o['total_amount']) ?></td>
                    <td><span class="badge <?= $statusColors[$o['status']] ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><a href="/orders/order_detail.php?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

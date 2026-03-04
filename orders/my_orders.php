<?php
// orders/my_orders.php
$pageTitle = 'My Orders – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
requireLogin();

$db     = getDB();
$userId = $_SESSION['user_id'];
$page   = max(1, sanitizeInt($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$total  = $db->prepare("SELECT COUNT(*) as c FROM orders WHERE user_id = ?");
$total->bind_param('i', $userId);
$total->execute();
$count = $total->get_result()->fetch_assoc()['c'];

$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param('iii', $userId, $perPage, $offset);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$statusColors = [
    'pending' => 'badge-warning', 'confirmed' => 'badge-info', 'processing' => 'badge-info',
    'shipped' => 'badge-secondary', 'delivered' => 'badge-success', 'cancelled' => 'badge-danger'
];
?>

<h2 class="page-title">My Orders</h2>

<?php if (empty($orders)): ?>
    <div class="no-data">
        <p>You haven't placed any orders yet.</p>
        <a href="/products/index.php" class="btn btn-primary mt-2">Start Shopping</a>
    </div>
<?php else: ?>
<table class="table">
    <thead>
        <tr><th>Order #</th><th>Date</th><th>Delivery</th><th>Total</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
            <td>#<?= $o['id'] ?></td>
            <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
            <td><?= htmlspecialchars($o['delivery_date']) ?><br><small><?= htmlspecialchars($o['delivery_time']) ?></small></td>
            <td><?= formatPrice($o['total_amount']) ?></td>
            <td><span class="badge <?= $statusColors[$o['status']] ?? 'badge-secondary' ?>"><?= ucfirst($o['status']) ?></span></td>
            <td>
                <a href="/orders/order_detail.php?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">View</a>
                <?php if (in_array($o['status'], ['pending','confirmed'])): ?>
                <form method="POST" action="/orders/orders_backend.php" style="display:inline;">
                    <?php csrfField(); ?>
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Cancel this order?">Cancel</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php echo paginate($count, $page, $perPage, '/orders/my_orders.php'); ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

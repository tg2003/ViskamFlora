<?php
// orders/order_detail.php
$pageTitle = 'Order Detail – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
requireLogin();

$db      = getDB();
$orderId = sanitizeInt($_GET['id'] ?? 0);

// Allow admin to view any order; customers only their own
if (isAdmin()) {
    $stmt = $db->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->bind_param('i', $orderId);
} else {
    $stmt = $db->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?");
    $stmt->bind_param('ii', $orderId, $_SESSION['user_id']);
}
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) redirect(isAdmin() ? '/admin/orders.php' : '/orders/my_orders.php', 'Order not found.', 'danger');

// Items
$items = $db->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items->bind_param('i', $orderId);
$items->execute();
$orderItems = $items->get_result()->fetch_all(MYSQLI_ASSOC);

$statusColors = [
    'pending' => 'badge-warning', 'confirmed' => 'badge-info', 'processing' => 'badge-info',
    'shipped' => 'badge-secondary', 'delivered' => 'badge-success', 'cancelled' => 'badge-danger'
];
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <h2 class="page-title" style="margin:0;">Order #<?= $orderId ?></h2>
    <span class="badge <?= $statusColors[$order['status']] ?? 'badge-secondary' ?>" style="font-size:1rem;padding:7px 14px;"><?= ucfirst($order['status']) ?></span>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;flex-wrap:wrap;">
    <div class="card"><div class="card-body">
        <h4 style="color:#2d5016;margin-bottom:12px;">Order Info</h4>
        <p><strong>Order Date:</strong> <?= date('d M Y H:i', strtotime($order['created_at'])) ?></p>
        <p><strong>Delivery Date:</strong> <?= htmlspecialchars($order['delivery_date']) ?></p>
        <p><strong>Delivery Time:</strong> <?= htmlspecialchars($order['delivery_time']) ?></p>
        <p><strong>Payment:</strong> <?= ucfirst(str_replace('_',' ',$order['payment_method'])) ?></p>
        <p><strong>Payment Status:</strong> <?= ucfirst($order['payment_status']) ?></p>
        <?php if ($order['notes']): ?>
        <p><strong>Notes:</strong> <?= htmlspecialchars($order['notes']) ?></p>
        <?php endif; ?>
    </div></div>
    <div class="card"><div class="card-body">
        <h4 style="color:#2d5016;margin-bottom:12px;">Delivery Address</h4>
        <p><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
        <?php if (isAdmin()): ?>
        <hr style="margin:12px 0;">
        <h4 style="color:#2d5016;margin-bottom:8px;">Customer</h4>
        <p><?= htmlspecialchars($order['customer_name']) ?></p>
        <p><?= htmlspecialchars($order['customer_email']) ?></p>
        <?php endif; ?>
    </div></div>
</div>

<div class="card"><div class="card-body">
    <h4 style="color:#2d5016;margin-bottom:16px;">Ordered Items</h4>
    <table class="table">
        <thead><tr><th>Product</th><th>Unit Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
            <?php foreach ($orderItems as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= formatPrice($item['unit_price']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= formatPrice($item['unit_price'] * $item['quantity']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right;font-weight:bold;">Total</td>
                <td style="font-weight:bold;color:#2d5016;"><?= formatPrice($order['total_amount']) ?></td>
            </tr>
        </tfoot>
    </table>
</div></div>

<?php if (isAdmin()): ?>
<div class="card" style="margin-top:20px;"><div class="card-body">
    <h4 style="color:#2d5016;margin-bottom:12px;">Update Order Status</h4>
    <form method="POST" action="/orders/orders_backend.php" style="display:flex;gap:10px;flex-wrap:wrap;">
        <?php csrfField(); ?>
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="order_id" value="<?= $orderId ?>">
        <select name="status" style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;">
            <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Update Status</button>
    </form>
</div></div>
<?php endif; ?>

<div class="mt-2">
    <?php if (isAdmin()): ?>
        <a href="/admin/orders.php" class="btn btn-secondary">← Back to Orders</a>
    <?php else: ?>
        <a href="/orders/my_orders.php" class="btn btn-secondary">← My Orders</a>
        <?php if (in_array($order['status'], ['pending','confirmed'])): ?>
        <form method="POST" action="/orders/orders_backend.php" style="display:inline;margin-left:8px;">
            <?php csrfField(); ?>
            <input type="hidden" name="action" value="cancel">
            <input type="hidden" name="order_id" value="<?= $orderId ?>">
            <button type="submit" class="btn btn-danger" data-confirm="Cancel this order?">Cancel Order</button>
        </form>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

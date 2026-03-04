<?php
// orders/order_success.php
$pageTitle = 'Order Placed! – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
requireLogin();

$orderId = sanitizeInt($_GET['order_id'] ?? 0);
if (!$orderId) redirect('/orders/my_orders.php');

$db   = getDB();
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $orderId, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) redirect('/orders/my_orders.php');
?>

<div style="text-align:center;padding:50px 20px;">
    <div style="font-size:4rem;margin-bottom:16px;">🎉</div>
    <h2 style="color:#2d5016;font-size:1.8rem;margin-bottom:10px;">Order Placed Successfully!</h2>
    <p style="color:#666;font-size:1.05rem;margin-bottom:6px;">Thank you for your order. We'll have it delivered fresh!</p>
    <p>Order ID: <strong>#<?= $orderId ?></strong></p>
    <p>Delivery on: <strong><?= htmlspecialchars($order['delivery_date']) ?> (<?= htmlspecialchars($order['delivery_time']) ?>)</strong></p>
    <p>Total: <strong style="color:#2d5016;"><?= formatPrice($order['total_amount']) ?></strong></p>
    <p>Payment: <strong><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></strong></p>
    <div style="margin-top:28px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="/orders/order_detail.php?id=<?= $orderId ?>" class="btn btn-primary">View Order Details</a>
        <a href="/orders/my_orders.php" class="btn btn-secondary">All My Orders</a>
        <a href="/products/index.php" class="btn btn-secondary">Continue Shopping</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
// viskam_flora_full/orders/order_success.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify user owns this order
$stmt = $conn->prepare("SELECT id, total_amount, payment_method, created_at FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Order not found or access denied.";
    exit();
}

$order = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success | Viskam Flora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container" style="margin: 80px auto; min-height: 50vh; text-align:center; max-width:600px;">
        <div style="background:#fff; padding:50px; border-radius:var(--border-radius-md); box-shadow:var(--shadow-md);">
            <div style="width:80px; height:80px; background:#d4edda; color:#28a745; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            
            <h1 style="color:var(--primary-color); margin-bottom:10px;">Order Placed Successfully!</h1>
            <p class="text-muted" style="font-size:1.1rem; margin-bottom:30px;">Thank you for shopping with Viskam Flora. Your order is being processed.</p>
            
            <div style="background:#f9f9f9; padding:20px; border-radius:var(--border-radius-sm); text-align:left; margin-bottom:30px; display:inline-block; min-width:100%;">
                <p style="margin-bottom:10px;"><strong>Order Number:</strong> #ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></p>
                <p style="margin-bottom:10px;"><strong>Total Amount:</strong> <?= format_price($order['total_amount']) ?></p>
                <p style="margin-bottom:10px;"><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
                <p style="margin-bottom:0;"><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
            </div>
            
            <div style="display:flex; justify-content:center; gap:20px;">
                <a href="<?= BASE_URL ?>orders/my_orders.php" class="btn btn-outline">View My Orders</a>
                <a href="<?= BASE_URL ?>products/index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>


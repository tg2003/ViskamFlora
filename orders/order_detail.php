<?php
// viskam_flora_full/orders/order_detail.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get Order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    header("Location: my_orders.php");
    exit();
}

$order = $order_result->fetch_assoc();

// Get Order Items
$item_stmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();
$items = $item_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Detail #ORD-<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?> | Viskam Flora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container" style="margin: 40px auto; max-width: 800px; min-height: 60vh;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2>Order Details</h2>
            <a href="my_orders.php" class="btn btn-outline" style="padding:5px 15px;">← Back to Orders</a>
        </div>

        <div style="background:#fff; padding:30px; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm); margin-bottom:30px;">
            <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:20px; border-bottom:1px solid #eee; padding-bottom:20px; margin-bottom:20px;">
                <div>
                    <h3 style="margin-bottom:10px;">Order #ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></h3>
                    <p class="text-muted" style="margin-bottom:5px;">Placed on: <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                    <p class="text-muted" style="margin-bottom:0;">Payment: <?= htmlspecialchars($order['payment_method']) ?></p>
                </div>
                <div style="text-align:right;">
                    <p style="margin-bottom:10px; font-weight:bold; font-size:1.1rem;">Status: <span style="color:var(--primary-color);"><?= htmlspecialchars($order['status']) ?></span></p>
                    <h3 style="color:var(--primary-color);">Total: <?= format_price($order['total_amount']) ?></h3>
                </div>
            </div>

            <h4 style="margin-bottom:15px;">Shipping Details</h4>
            <div style="background:#f9f9f9; padding:15px; border-radius:var(--border-radius-sm); margin-bottom:30px;">
                <p><strong>Method:</strong> <?= htmlspecialchars($order['delivery_method']) ?></p>
                <p style="margin-top:10px; line-height:1.5;"><strong>Address:</strong><br>
                <?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
            </div>

            <h4 style="margin-bottom:15px;">Items Ordered</h4>
            <table style="width:100%; border-collapse:collapse;">
                <thead style="border-bottom:2px solid #eee; text-align:left;">
                    <tr>
                        <th style="padding:10px; width:60px;"></th>
                        <th style="padding:10px;">Product</th>
                        <th style="padding:10px;">Price</th>
                        <th style="padding:10px; text-align:center;">Qty</th>
                        <th style="padding:10px; text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $items->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:10px;">
                                <img src="<?= htmlspecialchars(get_image_url($item['image'])) ?>" alt="" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">
                            </td>
                            <td style="padding:10px; font-weight:500;">
                                <a href="<?= BASE_URL ?>products/detail.php?id=<?= $item['product_id'] ?>"><?= htmlspecialchars($item['name']) ?></a>
                            </td>
                            <td style="padding:10px; color:var(--text-muted);"><?= format_price($item['price']) ?></td>
                            <td style="padding:10px; text-align:center;"><?= $item['quantity'] ?></td>
                            <td style="padding:10px; text-align:right; font-weight:bold;"><?= format_price($item['price'] * $item['quantity']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>


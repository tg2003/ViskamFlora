<?php
// viskam_flora_full/orders/my_orders.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user_id = $_SESSION['user_id'];

// Fetch User Orders
$stmt = $conn->prepare("SELECT id, total_amount, status, created_at, delivery_method FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Viskam Flora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .status-Pending { background: #ffeeba; color: #856404; }
        .status-Paid { background: #b8daff; color: #004085; }
        .status-Shipped { background: #c3e6cb; color: #155724; }
        .status-Delivered { background: #28a745; color: white; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container" style="margin: 40px auto; min-height: 60vh;">
        <h2 class="mb-4">My Orders</h2>

        <div style="background:#fff; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm); overflow:hidden;">
            <?php if ($orders->num_rows === 0): ?>
                <div style="padding:40px; text-align:center;">
                    <p class="text-muted mb-4">You haven't placed any orders yet.</p>
                    <a href="<?= BASE_URL ?>products/index.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead style="background:#f4f6f9; border-bottom:1px solid #ddd;">
                        <tr>
                            <th style="padding:15px 20px;">Order ID</th>
                            <th style="padding:15px 20px;">Date</th>
                            <th style="padding:15px 20px;">Total</th>
                            <th style="padding:15px 20px;">Delivery</th>
                            <th style="padding:15px 20px;">Status</th>
                            <th style="padding:15px 20px; text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = $orders->fetch_assoc()): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:15px 20px; font-weight:bold;">#ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td style="padding:15px 20px;"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                <td style="padding:15px 20px; color:var(--primary-color); font-weight:bold;"><?= format_price($order['total_amount']) ?></td>
                                <td style="padding:15px 20px;"><?= htmlspecialchars($order['delivery_method']) ?></td>
                                <td style="padding:15px 20px;">
                                    <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </td>
                                <td style="padding:15px 20px; text-align:right;">
                                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-outline" style="padding:5px 15px; font-size:0.9rem;">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>


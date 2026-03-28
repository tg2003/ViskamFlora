<?php
// viskam_flora_full/admin/orders.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ensure user is admin
if (!is_admin()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$success = '';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitize_input($conn, $_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    if ($stmt->execute()) {
        $success = "Order #ORD-" . str_pad($order_id, 5, '0', STR_PAD_LEFT) . " status updated to $status.";
    }
}

// Fetch Orders
$orders = $conn->query("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .details-row { display: none; background: #fdfdfd; }
        .details-row.active { display: table-row; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="admin-layout">
        <aside class="admin-sidebar" style="min-height: calc(100vh - 70px);">
            <h3 style="margin-bottom:20px; color:var(--accent-color); padding-left:10px;">Admin Panel</h3>
            <nav>
                <a href="dashboard.php">Dashboard Summary</a>
                <a href="products.php">Manage Products</a>
                <a href="orders.php" class="active">Manage Orders</a>
                <a href="users.php">Manage Users</a>
                <a href="arrangements.php">Wedding Requests</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <h2 class="mb-4">Manage Orders</h2>
            
            <?php if ($success): ?><div style="background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:15px;"><?= $success ?></div><?php endif; ?>

            <div style="background:#fff; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead style="background:#f4f6f9; border-bottom:1px solid #ddd;">
                        <tr>
                            <th style="padding:15px;">Order ID</th>
                            <th style="padding:15px;">Customer</th>
                            <th style="padding:15px;">Date</th>
                            <th style="padding:15px;">Total</th>
                            <th style="padding:15px;">Delivery</th>
                            <th style="padding:15px;">Status</th>
                            <th style="padding:15px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($ord = $orders->fetch_assoc()): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:15px; font-weight:bold;">#ORD-<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td style="padding:15px;">
                                    <?= htmlspecialchars($ord['user_name']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($ord['user_email']) ?></small>
                                </td>
                                <td style="padding:15px;"><?= date('M j, Y H:i', strtotime($ord['created_at'])) ?></td>
                                <td style="padding:15px; color:var(--primary-color); font-weight:bold;"><?= format_price($ord['total_amount']) ?></td>
                                <td style="padding:15px;"><?= htmlspecialchars($ord['delivery_method']) ?></td>
                                <td style="padding:15px;">
                                    <form action="" method="POST" style="display:flex; gap:10px;">
                                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                        <select name="status" class="form-control" style="padding:5px; width:auto; font-size:0.9rem;">
                                            <option value="Pending" <?= $ord['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Paid" <?= $ord['status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
                                            <option value="Shipped" <?= $ord['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                            <option value="Delivered" <?= $ord['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary" style="padding:5px 10px; font-size:0.8rem;">Update</button>
                                    </form>
                                </td>
                                <td style="padding:15px; text-align:right;">
                                    <button onclick="toggleDetails(<?= $ord['id'] ?>)" class="btn btn-outline" style="padding:5px 10px; font-size:0.8rem;">Details ↓</button>
                                </td>
                            </tr>
                            <tr id="details-<?= $ord['id'] ?>" class="details-row">
                                <td colspan="7" style="padding:20px; border-bottom:2px solid #ddd;">
                                    <div style="display:flex; gap:40px;">
                                        <div style="flex:1;">
                                            <h4 style="margin-bottom:10px; font-size:1rem;">Shipping Address</h4>
                                            <p style="background:#fff; padding:10px; border:1px solid #eee; border-radius:5px; font-size:0.9rem;">
                                                <?= nl2br(htmlspecialchars($ord['shipping_address'])) ?: 'No address (Pickup)' ?>
                                            </p>
                                        </div>
                                        <div style="flex:2;">
                                            <h4 style="margin-bottom:10px; font-size:1rem;">Order Items</h4>
                                            <?php
                                                // Fetch items for this order precisely
                                                $items = $conn->query("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = " . $ord['id']);
                                            ?>
                                            <table style="width:100%; font-size:0.9rem; border-collapse:collapse;">
                                                <?php while($itm = $items->fetch_assoc()): ?>
                                                    <tr>
                                                        <td style="padding:5px 0;">• <?= htmlspecialchars($itm['name']) ?></td>
                                                        <td style="padding:5px 0; text-align:center;">x<?= $itm['quantity'] ?></td>
                                                        <td style="padding:5px 0; text-align:right; font-weight:bold; color:var(--text-main);"><?= format_price($itm['price'] * $itm['quantity']) ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function toggleDetails(id) {
            const row = document.getElementById('details-' + id);
            row.classList.toggle('active');
        }
    </script>
</body>
</html>


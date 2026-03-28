<?php
// viskam_flora_full/admin/dashboard.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ensure user is admin
if (!is_admin()) {
    header("Location: /Test%20by%20antigravity/viskam_flora_full/index.php");
    exit();
}

// Fetch stats
$users_count = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$orders_count = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$products_count = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$revenue = $conn->query("SELECT SUM(total_amount) as r FROM orders WHERE status != 'Pending'")->fetch_assoc()['r'];

// Recent orders
$recent_orders = $conn->query("SELECT o.id, o.total_amount, o.status, o.created_at, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Viskam Flora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Test%20by%20antigravity/viskam_flora_full/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3 style="margin-bottom:20px; color:var(--accent-color); padding-left:10px;">Admin Panel</h3>
            <nav>
                <a href="dashboard.php" class="active">Dashboard Summary</a>
                <a href="products.php">Manage Products</a>
                <a href="orders.php">Manage Orders</a>
                <a href="users.php">Manage Users</a>
                <a href="arrangements.php">Wedding Requests</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <h2 class="mb-4">Dashboard Overview</h2>
            
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin-bottom:40px;">
                <div style="background:#fff; padding:20px; border-radius:var(--border-radius-sm); box-shadow:var(--shadow-sm); border-left:4px solid #007bff;">
                    <h4 style="color:var(--text-muted); margin-bottom:10px;">Total Revenue</h4>
                    <h2 style="color:var(--text-main);"><?= format_price($revenue ?? 0) ?></h2>
                </div>
                <div style="background:#fff; padding:20px; border-radius:var(--border-radius-sm); box-shadow:var(--shadow-sm); border-left:4px solid #28a745;">
                    <h4 style="color:var(--text-muted); margin-bottom:10px;">Total Orders</h4>
                    <h2 style="color:var(--text-main);"><?= $orders_count ?></h2>
                </div>
                <div style="background:#fff; padding:20px; border-radius:var(--border-radius-sm); box-shadow:var(--shadow-sm); border-left:4px solid #17a2b8;">
                    <h4 style="color:var(--text-muted); margin-bottom:10px;">Total Products</h4>
                    <h2 style="color:var(--text-main);"><?= $products_count ?></h2>
                </div>
                <div style="background:#fff; padding:20px; border-radius:var(--border-radius-sm); box-shadow:var(--shadow-sm); border-left:4px solid #ffc107;">
                    <h4 style="color:var(--text-muted); margin-bottom:10px;">Total Customers</h4>
                    <h2 style="color:var(--text-main);"><?= $users_count ?></h2>
                </div>
            </div>

            <div style="background:#fff; padding:20px; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3>Recent Orders</h3>
                    <a href="orders.php" class="btn btn-outline" style="padding:5px 15px;">View All</a>
                </div>
                
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead style="background:#f4f6f9; border-bottom:1px solid #ddd;">
                        <tr>
                            <th style="padding:12px;">Order ID</th>
                            <th style="padding:12px;">Customer</th>
                            <th style="padding:12px;">Date</th>
                            <th style="padding:12px;">Total</th>
                            <th style="padding:12px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($ord = $recent_orders->fetch_assoc()): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:12px; font-weight:bold;">#ORD-<?= $ord['id'] ?></td>
                                <td style="padding:12px;"><?= htmlspecialchars($ord['customer_name']) ?></td>
                                <td style="padding:12px;"><?= date('M j, Y', strtotime($ord['created_at'])) ?></td>
                                <td style="padding:12px;"><?= format_price($ord['total_amount']) ?></td>
                                <td style="padding:12px;">
                                    <span style="padding:4px 8px; border-radius:12px; font-size:0.8rem; font-weight:bold; 
                                        <?= $ord['status']==='Pending'?'background:#ffeeba;color:#856404;':($ord['status']==='Delivered'?'background:#c3e6cb;color:#155724;':'background:#b8daff;color:#004085;') ?>
                                    ">
                                        <?= htmlspecialchars($ord['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>
</html>


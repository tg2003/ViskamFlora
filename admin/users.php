<?php
// viskam_flora_full/admin/users.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ensure user is admin
if (!is_admin()) {
    header("Location: /viskam_flora_full/index.php");
    exit();
}

$success = '';
$error = '';

// Handle Role Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $target_id = (int)$_POST['user_id'];
    $role = sanitize_input($conn, $_POST['role']);
    
    // Prevent self-demotion directly
    if ($target_id === $_SESSION['user_id']) {
        $error = "You cannot change your own role.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $target_id);
        if ($stmt->execute()) {
            $success = "User role updated successfully.";
        } else {
            $error = "Failed to update user role.";
        }
    }
}

// Fetch Users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/viskam_flora_full/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="admin-layout">
        <aside class="admin-sidebar" style="min-height: calc(100vh - 70px);">
            <h3 style="margin-bottom:20px; color:var(--accent-color); padding-left:10px;">Admin Panel</h3>
            <nav>
                <a href="dashboard.php">Dashboard Summary</a>
                <a href="products.php">Manage Products</a>
                <a href="orders.php">Manage Orders</a>
                <a href="users.php" class="active">Manage Users</a>
                <a href="arrangements.php">Wedding Requests</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <h2 class="mb-4">Registered Users</h2>
            
            <?php if ($error): ?><div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; margin-bottom:15px;"><?= $error ?></div><?php endif; ?>
            <?php if ($success): ?><div style="background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:15px;"><?= $success ?></div><?php endif; ?>

            <div style="background:#fff; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead style="background:#f4f6f9; border-bottom:1px solid #ddd;">
                        <tr>
                            <th style="padding:15px;">ID</th>
                            <th style="padding:15px;">Name</th>
                            <th style="padding:15px;">Email</th>
                            <th style="padding:15px;">Registered</th>
                            <th style="padding:15px;">Role</th>
                            <th style="padding:15px; text-align:right;">Change Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($u = $users->fetch_assoc()): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:15px;"><?= $u['id'] ?></td>
                                <td style="padding:15px; font-weight:bold;"><?= htmlspecialchars($u['name']) ?></td>
                                <td style="padding:15px; color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
                                <td style="padding:15px;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                                <td style="padding:15px;">
                                    <?php if($u['role'] === 'admin'): ?>
                                        <span style="background:#cce5ff; color:#004085; padding:5px 10px; border-radius:12px; font-size:0.8rem; font-weight:bold;">Admin</span>
                                    <?php else: ?>
                                        <span style="background:#e2e3e5; color:#383d41; padding:5px 10px; border-radius:12px; font-size:0.8rem; font-weight:bold;">Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:15px; text-align:right;">
                                    <?php if($u['id'] !== $_SESSION['user_id']): ?>
                                        <form action="" method="POST" style="display:inline-flex; gap:10px;">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <select name="role" class="form-control" style="padding:5px; width:auto; font-size:0.9rem;">
                                                <option value="customer" <?= $u['role'] == 'customer' ? 'selected' : '' ?>>Customer</option>
                                                <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <button type="submit" class="btn btn-outline" style="padding:5px 10px; font-size:0.8rem;">Save</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:0.9rem;">(You)</span>
                                    <?php endif; ?>
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

<?php
// viskam_flora_full/admin/add_admin.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ensure user is admin
if (!is_admin()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($conn, $_POST['name']);
    $email = sanitize_input($conn, $_POST['email']);
    $password = $_POST['password']; // Will be hashed
    $confirm_password = $_POST['confirm_password'];
    $address = sanitize_input($conn, $_POST['address']);
    $phone = sanitize_input($conn, $_POST['phone']);

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Name, Email, and Password are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin';
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, address, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $hashed_password, $role, $address, $phone);
            
            if ($stmt->execute()) {
                $success = "New admin account created successfully!";
                // Reset fields
                $name = $email = $address = $phone = '';
            } else {
                $error = "Failed to create account. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Admin | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="admin-layout">
        <aside class="admin-sidebar" style="min-height: calc(100vh - 70px);">
            <h3 style="margin-bottom:20px; color:var(--accent-color); padding-left:10px; font-family:'Inter', sans-serif; font-size:1.2rem;">Admin Panel</h3>
            <nav>
                <a href="dashboard.php">Dashboard Summary</a>
                <a href="products.php">Manage Products</a>
                <a href="orders.php">Manage Orders</a>
                <a href="users.php" class="active">Manage Users</a>
                <a href="arrangements.php">Wedding Requests</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div style="max-width: 800px; margin: 0 auto;">
                <div style="display:flex; align-items:center; gap:15px; margin-bottom:2rem;">
                    <a href="users.php" style="color:var(--text-muted); font-size:0.9rem;">&larr; Back to Users</a>
                    <h2 style="margin:0;">Add New Admin</h2>
                </div>

                <?php if ($error): ?>
                    <div style="background:#fff5f5; color:#c53030; padding:15px; border-radius:var(--border-radius-sm); border-left:4px solid #f56565; margin-bottom:20px; box-shadow:var(--shadow-sm);">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div style="background:#f0fff4; color:#276749; padding:15px; border-radius:var(--border-radius-sm); border-left:4px solid #48bb78; margin-bottom:20px; box-shadow:var(--shadow-sm);">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <div style="background:#fff; border-radius:var(--border-radius-md); padding:40px; box-shadow:var(--shadow-md); border:1px solid rgba(0,0,0,0.05);">
                    <form action="" method="POST">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required placeholder="John Doe">
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required placeholder="john@example.com">
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                            <div class="form-group">
                                <label for="password">Password * (Min 6 chars)</label>
                                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="••••••••">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>" placeholder="+94 77 123 4567">
                        </div>

                        <div class="form-group">
                            <label for="address">Residential Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3" placeholder="No. 123, Flower Lane, Colombo"><?= isset($address) ? htmlspecialchars($address) : '' ?></textarea>
                        </div>

                        <div style="margin-top:30px; display:flex; gap:15px;">
                            <button type="submit" class="btn btn-primary" style="flex:1;">Create Admin Account</button>
                            <a href="users.php" class="btn btn-outline" style="flex:1; text-decoration:none;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

</body>
</html>

<?php
// viskam_flora_full/auth/login_page.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

if (is_logged_in()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($conn, $_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify password safely
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: " . BASE_URL . "admin/dashboard.php");
                } else {
                    // Return user to original intended page or home
                    $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : BASE_URL . 'index.php';
                    unset($_SESSION['redirect_after_login']);
                    header("Location: " . $redirect);
                }
                exit();
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "Invalid credentials.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Viskam Flora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container" style="max-width: 450px; margin: 80px auto; padding: 40px; background: #fff; border-radius: var(--border-radius-md); box-shadow: var(--shadow-sm);">
        <h2 class="text-center mb-4">Welcome Back</h2>

        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Demo credentials info -->
         <!-- Admin: admin@viskamflora.com / Admin@123 -->
          
        <!-- <div style="background: #e2e3e5; color: #383d41; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size:0.85rem;">
            <strong>Demo Account:</strong><br>
            Admin: admin@viskamflora.com / Admin@123
        </div> -->

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        
        <p class="text-center mt-2" style="font-size: 0.9rem; margin-top: 20px;">
            Don't have an account? <a href="register_page.php" style="color: var(--primary-color); font-weight: bold;">Sign up here</a>
        </p>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>


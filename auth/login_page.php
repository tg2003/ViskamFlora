<?php
// auth/login_page.php
$pageTitle = 'Login – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
$redirect = sanitize($_GET['redirect'] ?? '/index.php');
if (isLoggedIn()) header('Location: ' . $redirect) and exit;
?>

<div class="form-box">
    <h2>🌸 Login</h2>
    <form method="POST" action="/viskam_flora_full/auth/login_backend.php">
        <?php csrfField(); ?>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
    </form>
    <p class="mt-2 text-center">Don't have an account? <a href="/viskam_flora_full/auth/register_page.php">Register</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

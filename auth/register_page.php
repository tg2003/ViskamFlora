<?php
// auth/register_page.php
$pageTitle = 'Register – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
if (isLoggedIn()) redirect('/ViskamFlora/index.php');
?>

<div class="form-box">
    <h2>🌸 Create Account</h2>
    <form method="POST" action="/ViskamFlora/auth/register_backend.php">
        <?php csrfField(); ?>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required placeholder="Your Name">
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" placeholder="+94 77 000 0000">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Min 6 characters">
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required placeholder="Repeat password">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Create Account</button>
    </form>
    <p class="mt-2 text-center">Already have an account? <a href="/ViskamFlora/auth/login_page.php">Login</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
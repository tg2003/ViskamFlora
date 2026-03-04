<?php
// includes/navbar.php
require_once __DIR__ . '/helpers.php';
$cartCount = cartCount();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Viskam Flora' ?></title>
    <link rel="stylesheet" href="/viskam_flora_full/assets/css/style.css">
</head>

<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="<?php echo $_SERVER['REQUEST_URI']; ?>/index.php">🌸 Viskam Flora</a>
        </div>
        <ul class="nav-links">
            <li><a href="/viskam_flora_full/index.php">Home</a></li>
            <li><a href="/viskam_flora_full/products/index.php">Shop</a></li>
            <li><a href="/viskam_flora_full/orders/wedding_page.php">Wedding</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="/viskam_flora_full/cart/cart_page.php">Cart (<?= $cartCount ?>)</a></li>
                <li><a href="/viskam_flora_full/orders/my_orders.php">My Orders</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="/viskam_flora_full/admin/dashboard.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="/viskam_flora_full/auth/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="/viskam_flora_full/auth/login_page.php">Login</a></li>
                <li><a href="/viskam_flora_full/auth/register_page.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <main class="container">
        <?php showFlash(); ?>
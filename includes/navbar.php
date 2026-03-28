<?php
// viskam_flora_full/includes/navbar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<nav class="navbar">
    <div class="container nav-container">
        <div class="nav-logo">
            <a href="<?= BASE_URL ?>index.php" style="display:flex; align-items:center; gap:4px; text-decoration:none;">
                <span style="color:var(--primary-color); font-size:1.3rem; font-weight:900; font-family:'Playfair Display', serif; letter-spacing:1px;">VISKAM</span>
                <span style="color:var(--text-main); font-size:1.3rem; font-weight:700; font-family:'Playfair Display', serif;"> Flora</span>
            </a>
        </div>
        
        <div class="search-bar">
            <!-- Basic search form -->
            <form action="<?= BASE_URL ?>products/index.php" method="GET" style="display:flex;">
                <input type="text" name="q" placeholder="Search flowers, gifts..." required>
                <button type="submit" style="background:none;border:none;cursor:pointer;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </form>
        </div>

        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
            <li><a href="<?= BASE_URL ?>products/index.php">Shop All</a></li>
            <li><a href="<?= BASE_URL ?>orders/wedding_page.php">Weddings & Events</a></li>
        </ul>

        <div class="nav-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>admin/dashboard.php" class="btn btn-outline" style="padding: 5px 15px;">Admin</a>
                <?php endif; ?>
                <div style="position:relative; display:inline-block;" class="profile-dropdown-container">
                    <a href="#" style="font-weight:bold; color:var(--primary-color);">Hello, <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?> ▼</a>
                    <div class="profile-dropdown" style="display:none; position:absolute; right:0; background:#fff; box-shadow:0 4px 8px rgba(0,0,0,0.1); border-radius:5px; padding:10px; min-width:120px; z-index:1001;">
                        <a href="<?= BASE_URL ?>orders/my_orders.php" style="display:block; padding:8px; color:#333;">My Orders</a>
                        <hr style="margin:5px 0; border:0; border-top:1px solid #eee;">
                        <a href="<?= BASE_URL ?>auth/logout.php" style="display:block; padding:8px; color:red;">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>auth/login_page.php" style="font-weight:600;">Login</a>
                <a href="<?= BASE_URL ?>auth/register_page.php" class="btn btn-primary" style="padding: 8px 16px;">Sign Up</a>
            <?php endif; ?>
            
            <a href="<?= BASE_URL ?>cart/cart_page.php" class="cart-icon" aria-label="Cart">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <?php if($cart_count > 0): ?>
                    <span class="cart-count"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</nav>

<script>
    // Simple dropdown toggle
    const profileContainer = document.querySelector('.profile-dropdown-container');
    if(profileContainer) {
        profileContainer.addEventListener('click', (e) => {
            const dropdown = profileContainer.querySelector('.profile-dropdown');
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        });
        document.addEventListener('click', (e) => {
            if(!profileContainer.contains(e.target)) {
                profileContainer.querySelector('.profile-dropdown').style.display = 'none';
            }
        });
    }
</script>


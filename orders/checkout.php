<?php
// orders/checkout.php
$pageTitle = 'Checkout – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
requireLogin();

$db     = getDB();
$userId = $_SESSION['user_id'];

// Buy now: single product
$buyNow   = sanitizeInt($_GET['buy_now'] ?? 0);
$cartItems = [];

if ($buyNow) {
    $stmt = $db->prepare("SELECT p.id as product_id, p.name, p.price, p.stock, p.image, 1 as quantity FROM products p WHERE p.id = ? AND p.status = 'active'");
    $stmt->bind_param('i', $buyNow);
    $stmt->execute();
    $cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $stmt = $db->prepare("SELECT c.quantity, p.id as product_id, p.name, p.price, p.stock, p.image FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

if (empty($cartItems)) redirect('/cart/cart_page.php', 'Your cart is empty.', 'danger');

$total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));
$user  = currentUser();
?>

<h2 class="page-title">Checkout</h2>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;flex-wrap:wrap;">
    <!-- Order Form -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom:16px;color:#2d5016;">Delivery Details</h3>
            <form method="POST" action="/orders/orders_backend.php">
                <?php csrfField(); ?>
                <input type="hidden" name="action" value="place_order">
                <input type="hidden" name="buy_now" value="<?= $buyNow ?>">
                <?php foreach ($cartItems as $item): ?>
                    <input type="hidden" name="items[]" value="<?= $item['product_id'] ?>:<?= $item['quantity'] ?>">
                <?php endforeach; ?>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="recipient_name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required placeholder="+94 77 000 0000">
                </div>
                <div class="form-group">
                    <label>Delivery Address</label>
                    <textarea name="delivery_address" required placeholder="Street, City, Province"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Delivery Date</label>
                    <input type="date" name="delivery_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                </div>
                <div class="form-group">
                    <label>Delivery Time</label>
                    <select name="delivery_time">
                        <option value="9AM-12PM">9:00 AM – 12:00 PM</option>
                        <option value="12PM-3PM">12:00 PM – 3:00 PM</option>
                        <option value="3PM-6PM">3:00 PM – 6:00 PM</option>
                        <option value="6PM-9PM">6:00 PM – 9:00 PM</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method">
                        <option value="cod">Cash on Delivery</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="online">Online Payment</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Special Notes (optional)</label>
                    <textarea name="notes" placeholder="Any special message or delivery instructions..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;font-size:1.05rem;padding:12px;">Place Order – <?= formatPrice($total) ?></button>
            </form>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="card" style="height:fit-content;">
        <div class="card-body">
            <h3 style="margin-bottom:16px;color:#2d5016;">Order Summary</h3>
            <?php foreach ($cartItems as $item): ?>
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #eee;">
                <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                <span><?= formatPrice($item['price'] * $item['quantity']) ?></span>
            </div>
            <?php endforeach; ?>
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span>Subtotal</span><span><?= formatPrice($total) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;color:#888;">
                <span>Delivery</span><span>Free</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:1.15rem;font-weight:bold;color:#2d5016;border-top:2px solid #2d5016;padding-top:10px;margin-top:8px;">
                <span>Total</span><span><?= formatPrice($total) ?></span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

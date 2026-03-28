<?php
// viskam_flora_full/cart/cart_page.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    if ($action === 'add' && $product_id > 0) {
        $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $qty;
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
        // Redirect to prevent form resubmission
        header("Location: cart_page.php");
        exit();
    }
    
    if ($action === 'update' && $product_id > 0) {
        $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
    }
    
    if ($action === 'remove' && $product_id > 0) {
        unset($_SESSION['cart'][$product_id]);
    }
}

// Fetch Cart Products
$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $query = "SELECT id, name, price, image FROM products WHERE id IN ($ids)";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $row['qty'] = $_SESSION['cart'][$row['id']];
        $row['subtotal'] = $row['price'] * $row['qty'];
        $total_price += $row['subtotal'];
        $cart_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Viskam Flora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/viskam_flora_full/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container" style="margin: 40px auto; min-height: 60vh;">
        <h2 class="mb-4">Your Shopping Cart</h2>

        <?php if (empty($cart_items)): ?>
            <div style="background:#fff; padding:50px; text-align:center; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm);">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ddd" stroke-width="2" style="margin-bottom:20px;">
                    <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <h3 class="text-muted mb-4">Your cart is currently empty.</h3>
                <a href="/viskam_flora_full/products/index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div style="display:flex; flex-wrap:wrap; gap:30px;">
                <!-- Cart Items List -->
                <div style="flex:2; min-width:300px;">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="<?= htmlspecialchars(get_image_url($item['image'])) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img">
                            
                            <div class="cart-item-details">
                                <a href="/viskam_flora_full/products/detail.php?id=<?= $item['id'] ?>"><h4 style="margin-bottom:5px;"><?= htmlspecialchars($item['name']) ?></h4></a>
                                <div class="cart-item-price"><?= format_price($item['price']) ?></div>
                            </div>
                            
                            <form action="" method="POST" style="display:flex; align-items:center; gap:10px;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                
                                <div class="qty-control">
                                    <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" class="qty-input" onchange="this.form.submit()">
                                </div>
                            </form>

                            <div style="width: 100px; text-align:right; font-weight:bold;">
                                <?= format_price($item['subtotal']) ?>
                            </div>

                            <form action="" method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" style="background:none; border:none; color:#d9534f; cursor:pointer;" title="Remove Setup">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="margin-top:20px;">
                        <a href="/viskam_flora_full/products/index.php" class="btn btn-outline">← Continue Shopping</a>
                    </div>
                </div>

                <!-- Order Summary -->
                <div style="flex:1; min-width:300px;">
                    <div style="background:#fff; padding:30px; border-radius:var(--border-radius-md); box-shadow:var(--shadow-md); position:sticky; top:100px;">
                        <h3 class="mb-4" style="border-bottom:1px solid #eee; padding-bottom:15px;">Order Summary</h3>
                        
                        <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:var(--text-muted);">
                            <span>Subtotal (<?= array_sum($_SESSION['cart']) ?> items)</span>
                            <span><?= format_price($total_price) ?></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:var(--text-muted);">
                            <span>Shipping</span>
                            <span>Calculated at checkout</span>
                        </div>
                        
                        <div style="display:flex; justify-content:space-between; margin-top:20px; padding-top:20px; border-top:2px solid #eee; font-weight:bold; font-size:1.2rem;">
                            <span>Total</span>
                            <span style="color:var(--primary-color);"><?= format_price($total_price) ?></span>
                        </div>
                        
                        <a href="/viskam_flora_full/orders/checkout.php" class="btn btn-primary mt-4" style="width:100%; font-size:1.1rem; padding:15px;">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

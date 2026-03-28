<?php
// viskam_flora_full/orders/checkout.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// If not logged in, save intended destination and redirect to login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = '/Test%20by%20antigravity/viskam_flora_full/orders/checkout.php';
    header("Location: /Test%20by%20antigravity/viskam_flora_full/auth/login_page.php");
    exit();
}

// Redirect to cart if empty
if (empty($_SESSION['cart'])) {
    header("Location: /Test%20by%20antigravity/viskam_flora_full/cart/cart_page.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';

// Calculate Total
$total_price = 0;
$cart_items = [];
$ids = implode(',', array_keys($_SESSION['cart']));
$query = "SELECT id, name, price FROM products WHERE id IN ($ids)";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $row['qty'] = $_SESSION['cart'][$row['id']];
    $row['subtotal'] = $row['price'] * $row['qty'];
    $total_price += $row['subtotal'];
    $cart_items[] = $row;
}

// Fetch user's address to pre-fill
$user_stmt = $conn->prepare("SELECT name, phone, address FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_details = $user_stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_method = isset($_POST['delivery_method']) ? sanitize_input($conn, $_POST['delivery_method']) : 'Standard';
    $payment_method = isset($_POST['payment_method']) ? sanitize_input($conn, $_POST['payment_method']) : 'COD';
    $shipping_address = isset($_POST['shipping_address']) ? sanitize_input($conn, $_POST['shipping_address']) : '';
    
    // Add delivery charge if applicable
    $delivery_charge = ($delivery_method === 'Express') ? 1000 : (($delivery_method === 'Pickup') ? 0 : 400);
    $final_total = $total_price + $delivery_charge;

    if (empty($shipping_address) && $delivery_method !== 'Pickup') {
        $error = "Shipping address is required for delivery.";
    } else {
        // Begin Transaction
        $conn->begin_transaction();
        
        try {
            // Insert Order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, delivery_method, payment_method, shipping_address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("idsss", $user_id, $final_total, $delivery_method, $payment_method, $shipping_address);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            
            // Insert Order Items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $item_stmt->bind_param("iiid", $order_id, $item['id'], $item['qty'], $item['price']);
                $item_stmt->execute();
                
                // Optional: Reduce stock level
                $conn->query("UPDATE products SET stock_qty = stock_qty - " . $item['qty'] . " WHERE id = " . $item['id']);
            }
            
            $conn->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to success
            header("Location: order_success.php?id=" . $order_id);
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to process order. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Viskam Flora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Test%20by%20antigravity/viskam_flora_full/assets/css/style.css">
    <script>
        function updateSummary() {
            const subtotal = <?= $total_price ?>;
            let delivery = 400; // Standard
            
            const method = document.querySelector('input[name="delivery_method"]:checked').value;
            if(method === 'Express') delivery = 1000;
            if(method === 'Pickup') delivery = 0;
            
            document.getElementById('shipping_cost').innerText = "LKR " + delivery.toFixed(2);
            document.getElementById('final_total').innerText = "LKR " + (subtotal + delivery).toFixed(2);
            
            // Hide address if pickup
            const addrDiv = document.getElementById('address_section');
            if(method === 'Pickup') {
                addrDiv.style.opacity = '0.5';
                document.getElementById('shipping_address').required = false;
            } else {
                addrDiv.style.opacity = '1';
                document.getElementById('shipping_address').required = true;
            }
        }
    </script>
</head>
<body onload="updateSummary()">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container" style="margin: 40px auto; min-height: 60vh;">
        <h2 class="mb-4">Secure Checkout</h2>

        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" style="display:flex; flex-wrap:wrap; gap:40px;">
            
            <!-- Checkout Details -->
            <div style="flex:2; min-width:300px;">
                <!-- Delivery Method -->
                <div style="background:#fff; padding:30px; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm); margin-bottom:30px;">
                    <h3 style="margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Delivery Method</h3>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <label style="display:flex; align-items:center; gap:10px; padding:15px; border:1px solid #ddd; border-radius:var(--border-radius-sm); cursor:pointer;">
                            <input type="radio" name="delivery_method" value="Standard" checked onchange="updateSummary()">
                            <div style="flex-grow:1;">
                                <strong>Standard Delivery</strong><br>
                                <span class="text-muted" style="font-size:0.9rem;">2-3 Business Days (LKR 400)</span>
                            </div>
                        </label>
                        <label style="display:flex; align-items:center; gap:10px; padding:15px; border:1px solid #ddd; border-radius:var(--border-radius-sm); cursor:pointer;">
                            <input type="radio" name="delivery_method" value="Express" onchange="updateSummary()">
                            <div style="flex-grow:1;">
                                <strong>Express Same-Day Delivery</strong><br>
                                <span class="text-muted" style="font-size:0.9rem;">Order before 2PM (LKR 1,000)</span>
                            </div>
                        </label>
                        <label style="display:flex; align-items:center; gap:10px; padding:15px; border:1px solid #ddd; border-radius:var(--border-radius-sm); cursor:pointer;">
                            <input type="radio" name="delivery_method" value="Pickup" onchange="updateSummary()">
                            <div style="flex-grow:1;">
                                <strong>Store Pickup</strong><br>
                                <span class="text-muted" style="font-size:0.9rem;">Collect from Colombo Outlet (Free)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Shipping Details -->
                <div id="address_section" style="background:#fff; padding:30px; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm); margin-bottom:30px; transition: opacity 0.3s;">
                    <h3 style="margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Shipping Address</h3>
                    
                    <div class="form-group">
                        <label>Recipient Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user_details['name']) ?>" readonly style="background:#f9f9f9;">
                    </div>
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user_details['phone'] ?? '') ?>" readonly style="background:#f9f9f9;">
                        <small class="text-muted">Update your profile to change contact details.</small>
                    </div>
                    <div class="form-group">
                        <label for="shipping_address">Detailed Address *</label>
                        <textarea id="shipping_address" name="shipping_address" class="form-control" rows="4" required><?= htmlspecialchars($user_details['address'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Payment Method -->
                <div style="background:#fff; padding:30px; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm);">
                    <h3 style="margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Payment Method</h3>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <label style="display:flex; align-items:center; gap:10px; padding:15px; border:1px solid #ddd; border-radius:var(--border-radius-sm); cursor:pointer;">
                            <input type="radio" name="payment_method" value="COD" checked>
                            <div style="flex-grow:1;">
                                <strong>Cash on Delivery (COD)</strong><br>
                                <span class="text-muted" style="font-size:0.9rem;">Pay with cash when your order arrives.</span>
                            </div>
                        </label>
                        <!-- Simulated Card Payment -->
                        <label style="display:flex; align-items:center; gap:10px; padding:15px; border:1px solid #ddd; border-radius:var(--border-radius-sm); cursor:pointer;">
                            <input type="radio" name="payment_method" value="Card">
                            <div style="flex-grow:1;">
                                <strong>Credit/Debit Card (Demo)</strong><br>
                                <span class="text-muted" style="font-size:0.9rem;">Simulates a successful card transaction.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Summary Sidebar -->
            <div style="flex:1; min-width:300px;">
                <div style="background:#fff; padding:30px; border-radius:var(--border-radius-md); box-shadow:var(--shadow-md); position:sticky; top:100px;">
                    <h3 class="mb-4" style="border-bottom:1px solid #eee; padding-bottom:15px;">Order Summary</h3>
                    
                    <div style="max-height: 250px; overflow-y:auto; margin-bottom:20px;">
                        <?php foreach($cart_items as $item): ?>
                            <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.95rem;">
                                <span style="flex-basis: 70%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= $item['qty'] ?>x <?= htmlspecialchars($item['name']) ?></span>
                                <span><?= format_price($item['subtotal']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="border-top:1px solid #eee; padding-top:15px; display:flex; justify-content:space-between; margin-bottom:10px; color:var(--text-muted);">
                        <span>Subtotal</span>
                        <span><?= format_price($total_price) ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:var(--text-muted);">
                        <span>Shipping/Delivery</span>
                        <span id="shipping_cost">LKR 400.00</span>
                    </div>
                    
                    <div style="display:flex; justify-content:space-between; margin-top:20px; padding-top:20px; border-top:2px solid #eee; font-weight:bold; font-size:1.3rem;">
                        <span>Total to Pay</span>
                        <span id="final_total" style="color:var(--primary-color);">...</span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary mt-4" style="width:100%; font-size:1.1rem; padding:15px;">Place Order</button>
                    
                    <p class="text-muted text-center mt-3" style="font-size:0.8rem;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:5px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        Secure Encrypted Checkout
                    </p>
                </div>
            </div>
        </form>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>


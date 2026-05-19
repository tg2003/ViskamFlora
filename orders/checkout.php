<?php
// viskam_flora_full/orders/checkout.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// If not logged in, save intended destination and redirect to login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . 'orders/checkout.php';
    header("Location: " . BASE_URL . "auth/login_page.php");
    exit();
}

// Redirect to cart if empty
if (empty($_SESSION['cart'])) {
    header("Location: " . BASE_URL . "cart/cart_page.php");
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
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
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

        <form id="checkout_form" action="" method="POST" style="display:flex; flex-wrap:wrap; gap:40px;">
            
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
                                <strong>Credit/Debit Card</strong><br>
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

    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.95); z-index:10000; align-items:center; justify-content:center; flex-direction:column;">
        <div style="width:60px; height:60px; border:5px solid #f3f3f3; border-top:5px solid #004f98; border-radius:50%; animation:spin 1s linear infinite;"></div>
        <p style="margin-top:25px; font-weight:600; color:#333; font-size:1.1rem; font-family:'Inter', sans-serif;">Redirecting to Commercial Bank Secure Gateway...</p>
        <style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
    </div>

    <!-- Meiranpay Modal Overlay (Renamed to Commercial Bank) -->
    <div id="paymentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(5px);">
        <div style="background:#fff; width:100%; max-width:450px; padding:35px; border-radius:16px; box-shadow:0 20px 40px rgba(0,0,0,0.2); position:relative; font-family:'Inter', sans-serif;">
            <span onclick="closePaymentModal()" style="position:absolute; right:20px; top:15px; font-size:24px; cursor:pointer; color:#999;">&times;</span>
            
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:30px;">
                <div style="background:#004f98; color:white; width:44px; height:44px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:1.3rem; font-family:serif;">CB</div>
                <h2 style="margin:0; font-size:1.5rem; color:#004f98; font-weight:800;">Commercial Bank</h2>
            </div>

            <div style="margin-bottom:25px;">
                <label style="font-weight:600; color:#1e293b; font-size:0.95rem; display:block; margin-bottom:10px;">Card Type</label>
                <div style="display:flex; gap:15px;">
                    <label style="flex:1; border:1px solid #cbd5e1; border-radius:8px; padding:12px; display:flex; align-items:center; gap:10px; cursor:pointer;" onclick="document.getElementById('logo_visa').style.display='block'; document.getElementById('logo_master').style.display='none';">
                        <input type="radio" name="sim_card_type" value="VISA" checked>
                        <span style="font-weight:600; color:#1e293b;">VISA</span>
                    </label>
                    <label style="flex:1; border:1px solid #cbd5e1; border-radius:8px; padding:12px; display:flex; align-items:center; gap:10px; cursor:pointer;" onclick="document.getElementById('logo_visa').style.display='none'; document.getElementById('logo_master').style.display='flex';">
                        <input type="radio" name="sim_card_type" value="MASTER">
                        <span style="font-weight:600; color:#1e293b;">Mastercard</span>
                    </label>
                </div>
            </div>

            <div style="margin-bottom:25px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <label style="font-weight:600; color:#1e293b; font-size:0.95rem;">Card Number</label>
                    
                </div>
                <p style="font-size:0.8rem; color:#64748b; margin-bottom:10px;">Enter the 16-digit card number on the card</p>
                <div style="position:relative;">
                    <input type="text" id="sim_card_num" autocomplete="off" placeholder="0000 - 0000 - 0000 - 0000" maxlength="25" style="width:100%; padding:14px 45px 14px 65px; border:1px solid #cbd5e1; border-radius:8px; font-size:1rem; outline:none; transition:0.2s; color:#333;" oninput="formatCard(this)">
                    <div id="card_logo_container" style="position:absolute; left:15px; top:16px; display:flex;">
                        <div id="logo_visa" style="font-weight:900; font-style:italic; color:#1a1f71; font-size:1.1rem; line-height:1; margin-top:-2px;">VISA</div>
                        <div id="logo_master" style="display:none; align-items:center;">
                            <div style="width:14px; height:14px; border-radius:50%; background:#ea001b; opacity:0.9;"></div>
                            <div style="width:14px; height:14px; border-radius:50%; background:#f79e1b; opacity:0.9; margin-left:-6px;"></div>
                        </div>
                    </div>
                    <div id="card_check" style="position:absolute; right:15px; top:14px; color:#00aa00; display:none;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" fill="#00aaff"/><path d="M10 15.5l-3.5-3.5 1.41-1.41L10 12.67l7.59-7.59L19 6.5l-9 9z" fill="#fff"/></svg>
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                <div style="flex:1;">
                    <label style="font-weight:600; color:#1e293b; display:block; font-size:0.95rem;">CVV Number</label>
                    <span style="font-size:0.8rem; color:#64748b;">Enter the 3 digit number</span>
                </div>
                <div style="width:140px; position:relative;">
                    <input type="text" id="sim_cvv" autocomplete="off" placeholder="123" maxlength="3" style="width:100%; padding:14px; border:1px solid #cbd5e1; border-radius:8px; font-size:1rem; text-align:center; outline:none; color:#333;">
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:35px;">
                <div style="flex:1;">
                    <label style="font-weight:600; color:#1e293b; display:block; font-size:0.95rem;">Expiry Date</label>
                    <span style="font-size:0.8rem; color:#64748b;">Enter the expiration date</span>
                </div>
                <div style="width:140px; display:flex; gap:10px;">
                    <input type="text" id="sim_exp_m" autocomplete="off" placeholder="MM" maxlength="2" style="width:50%; padding:14px; border:1px solid #cbd5e1; border-radius:8px; font-size:1rem; text-align:center; outline:none; color:#333;">
                    <input type="text" id="sim_exp_y" autocomplete="off" placeholder="YY" maxlength="2" style="width:50%; padding:14px; border:1px solid #cbd5e1; border-radius:8px; font-size:1rem; text-align:center; outline:none; color:#333;">
                </div>
            </div>

            <button type="button" id="pay_now_btn" onclick="processPayment()" style="width:100%; background:#004f98; color:#fff; padding:16px; border:none; border-radius:8px; font-weight:600; font-size:1.1rem; cursor:pointer; transition:0.2s;">Pay Now</button>
            <div id="payment_error" style="color:#ea001b; margin-top:15px; font-size:0.9rem; text-align:center; display:none;"></div>
        </div>
    </div>

    <script>
        document.getElementById('checkout_form').addEventListener('submit', function(e) {
            let method = document.querySelector('input[name="payment_method"]:checked').value;
            if (method === 'Card') {
                if (document.getElementById('paymentModal').style.display === 'none') {
                    e.preventDefault();
                    
                    // Pre-validate address before opening payment
                    let addr = document.getElementById('shipping_address');
                    let dMethod = document.querySelector('input[name="delivery_method"]:checked').value;
                    if (dMethod !== 'Pickup' && addr.value.trim() === '') {
                        alert("Please enter a shipping address.");
                        addr.focus();
                        return;
                    }
                    
                    // Show loading overlay first
                    document.getElementById('loadingOverlay').style.display = 'flex';
                    
                    setTimeout(() => {
                        document.getElementById('loadingOverlay').style.display = 'none';
                        document.getElementById('paymentModal').style.display = 'flex';
                    }, 2000); // 2 second delay to simulate redirect
                }
            }
        });

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function formatCard(el) {
            let val = el.value.replace(/\D/g, '');
            let formatted = val.match(/.{1,4}/g);
            el.value = formatted ? formatted.join(' - ') : '';
            
            if(val.length === 16) {
                document.getElementById('card_check').style.display = 'block';
            } else {
                document.getElementById('card_check').style.display = 'none';
            }
        }

        function processPayment() {
            let err = document.getElementById('payment_error');
            err.style.display = 'none';
            
            let cc = document.getElementById('sim_card_num').value.replace(/\D/g, '');
            let cvv = document.getElementById('sim_cvv').value.trim();
            let expM = document.getElementById('sim_exp_m').value.trim();
            let expY = document.getElementById('sim_exp_y').value.trim();
            
            if(cc.length < 13 || cc.length > 19) {
                err.innerText = "Invalid Card Number length."; err.style.display = 'block'; return;
            }
            if(!/^\d{3}$/.test(cvv)) {
                err.innerText = "CVV must be 3 digits."; err.style.display = 'block'; return;
            }
            if(!/^\d{2}$/.test(expM) || parseInt(expM) < 1 || parseInt(expM) > 12) {
                err.innerText = "Invalid Expiry Month."; err.style.display = 'block'; return;
            }
            if(!/^\d{2}$/.test(expY)) {
                err.innerText = "Invalid Expiry Year."; err.style.display = 'block'; return;
            }
            
            let btn = document.getElementById('pay_now_btn');
            btn.innerHTML = '<span style="display:inline-block; animation:spin 1s linear infinite; margin-right:8px;">&#8635;</span> Verifying...';
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
            
            setTimeout(() => {
                btn.innerHTML = '&#10004; Payment Successful';
                btn.style.background = '#00bb2d';
                btn.style.opacity = '1';
                
                setTimeout(() => {
                    document.getElementById('checkout_form').submit();
                }, 1000);
            }, 1500); // 1.5 seconds verification delay
        }
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
// cart/cart_backend.php
require_once __DIR__ . '/../includes/helpers.php';

// AJAX requests
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) || ($_SERVER['CONTENT_TYPE'] ?? '') === 'application/x-www-form-urlencoded';

if (!isLoggedIn()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {
        jsonResponse(false, 'Please login to use the cart.');
    }
    redirect('/auth/login_page.php', 'Please login first.');
}

$action = sanitize($_POST['action'] ?? $_GET['action'] ?? '');
$db     = getDB();
$userId = $_SESSION['user_id'];

switch ($action) {

    case 'add':
        $productId = sanitizeInt($_POST['product_id'] ?? 0);
        $quantity  = max(1, sanitizeInt($_POST['quantity'] ?? 1));

        if (!$productId) jsonResponse(false, 'Invalid product.');

        // Check product exists and has stock
        $stmt = $db->prepare("SELECT id, stock FROM products WHERE id = ? AND status = 'active'");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        if (!$product) jsonResponse(false, 'Product not found.');
        if ($product['stock'] < $quantity) jsonResponse(false, 'Insufficient stock.');

        // Insert or update cart
        $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $stmt->bind_param('iiii', $userId, $productId, $quantity, $quantity);
        if ($stmt->execute()) {
            // Get new cart count
            $cnt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
            $cnt->bind_param('i', $userId);
            $cnt->execute();
            $count = (int)($cnt->get_result()->fetch_assoc()['total'] ?? 0);
            jsonResponse(true, 'Added to cart!', ['cart_count' => $count]);
        }
        jsonResponse(false, 'Failed to add to cart.');
        break;

    case 'update':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) redirect('/cart/cart_page.php', 'Invalid request.', 'danger');
        $cartId  = sanitizeInt($_POST['cart_id'] ?? 0);
        $qty     = max(1, sanitizeInt($_POST['quantity'] ?? 1));
        $stmt    = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param('iii', $qty, $cartId, $userId);
        $stmt->execute();
        redirect('/cart/cart_page.php', 'Cart updated.');
        break;

    case 'remove':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) redirect('/cart/cart_page.php', 'Invalid request.', 'danger');
        $cartId = sanitizeInt($_POST['cart_id'] ?? 0);
        $stmt   = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $cartId, $userId);
        $stmt->execute();
        redirect('/cart/cart_page.php', 'Item removed from cart.');
        break;

    case 'clear':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) redirect('/cart/cart_page.php', 'Invalid request.', 'danger');
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        redirect('/cart/cart_page.php', 'Cart cleared.');
        break;

    default:
        redirect('/cart/cart_page.php');
}

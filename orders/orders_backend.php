<?php
// orders/orders_backend.php
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();

$action = sanitize($_POST['action'] ?? $_GET['action'] ?? '');
$db     = getDB();
$userId = $_SESSION['user_id'];

switch ($action) {

    case 'place_order':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) redirect('/orders/checkout.php', 'Invalid request.', 'danger');

        $address     = sanitize($_POST['delivery_address'] ?? '');
        $delivDate   = sanitize($_POST['delivery_date'] ?? '');
        $delivTime   = sanitize($_POST['delivery_time'] ?? '');
        $payMethod   = sanitize($_POST['payment_method'] ?? 'cod');
        $notes       = sanitize($_POST['notes'] ?? '');
        $buyNow      = sanitizeInt($_POST['buy_now'] ?? 0);
        $itemsRaw    = $_POST['items'] ?? [];

        if (empty($address) || empty($delivDate)) {
            redirect('/orders/checkout.php', 'Delivery address and date are required.', 'danger');
        }

        // Build items array
        $orderItems = [];
        if (!empty($itemsRaw)) {
            foreach ($itemsRaw as $raw) {
                [$pid, $qty] = explode(':', $raw);
                $orderItems[] = ['product_id' => (int)$pid, 'quantity' => max(1,(int)$qty)];
            }
        }

        if (empty($orderItems)) redirect('/cart/cart_page.php', 'No items in order.', 'danger');

        // Verify stock and calculate total
        $total = 0;
        $verifiedItems = [];
        foreach ($orderItems as $oi) {
            $stmt = $db->prepare("SELECT id, name, price, stock FROM products WHERE id = ? AND status = 'active'");
            $stmt->bind_param('i', $oi['product_id']);
            $stmt->execute();
            $p = $stmt->get_result()->fetch_assoc();
            if (!$p) redirect('/orders/checkout.php', 'Product not available.', 'danger');
            if ($p['stock'] < $oi['quantity']) redirect('/orders/checkout.php', "{$p['name']} has insufficient stock.", 'danger');
            $total += $p['price'] * $oi['quantity'];
            $verifiedItems[] = ['product_id' => $p['id'], 'quantity' => $oi['quantity'], 'price' => $p['price']];
        }

        // Create order
        $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, delivery_date, delivery_time, payment_method, notes) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('idssss s', $userId, $total, $address, $delivDate, $delivTime, $payMethod, $notes);
        // Fix bind
        $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, delivery_date, delivery_time, payment_method, notes) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('idssss', $userId, $total, $address, $delivDate, $delivTime, $payMethod);
        // Use 7 params properly
        $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, delivery_date, delivery_time, payment_method, notes) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('idsssss', $userId, $total, $address, $delivDate, $delivTime, $payMethod, $notes);
        if (!$stmt->execute()) redirect('/orders/checkout.php', 'Failed to place order.', 'danger');

        $orderId = $db->insert_id;

        // Insert order items & decrement stock
        foreach ($verifiedItems as $vi) {
            $si = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?,?,?,?)");
            $si->bind_param('iiid', $orderId, $vi['product_id'], $vi['quantity'], $vi['price']);
            $si->execute();
            $su = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $su->bind_param('ii', $vi['quantity'], $vi['product_id']);
            $su->execute();
        }

        // Clear cart (unless buy now)
        if (!$buyNow) {
            $sc = $db->prepare("DELETE FROM cart WHERE user_id = ?");
            $sc->bind_param('i', $userId);
            $sc->execute();
        }

        redirect('/orders/order_success.php?order_id=' . $orderId);
        break;

    case 'cancel':
        $orderId = sanitizeInt($_POST['order_id'] ?? 0);
        if (!$orderId) redirect('/orders/my_orders.php', 'Invalid order.', 'danger');
        // Verify ownership
        $chk = $db->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
        $chk->bind_param('ii', $orderId, $userId);
        $chk->execute();
        $order = $chk->get_result()->fetch_assoc();
        if (!$order) redirect('/orders/my_orders.php', 'Order not found.', 'danger');
        if (!in_array($order['status'], ['pending','confirmed'])) redirect('/orders/my_orders.php', 'This order cannot be cancelled.', 'danger');

        // Restore stock
        $items = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $items->bind_param('i', $orderId);
        $items->execute();
        foreach ($items->get_result()->fetch_all(MYSQLI_ASSOC) as $oi) {
            $ru = $db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $ru->bind_param('ii', $oi['quantity'], $oi['product_id']);
            $ru->execute();
        }

        $upd = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $upd->bind_param('i', $orderId);
        $upd->execute();
        redirect('/orders/my_orders.php', 'Order #' . $orderId . ' has been cancelled.');
        break;

    // Admin actions
    case 'update_status':
        requireAdmin();
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid CSRF token.');
        $orderId = sanitizeInt($_POST['order_id'] ?? 0);
        $status  = sanitize($_POST['status'] ?? '');
        $validStatuses = ['pending','confirmed','processing','shipped','delivered','cancelled'];
        if (!$orderId || !in_array($status, $validStatuses)) redirect('/admin/orders.php', 'Invalid data.', 'danger');
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $orderId);
        $stmt->execute();
        redirect('/admin/orders.php?id=' . $orderId, 'Order status updated to ' . $status . '.');
        break;

    default:
        redirect('/orders/my_orders.php');
}

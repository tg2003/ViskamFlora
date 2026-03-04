<?php
// cart/cart_page.php
$pageTitle = 'My Cart – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
requireLogin();

$db     = getDB();
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.stock, p.image
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.added_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
?>

<h2 class="page-title">🛒 My Cart</h2>

<?php if (empty($items)): ?>
    <div class="no-data">
        <p>Your cart is empty.</p>
        <a href="/products/index.php" class="btn btn-primary mt-2">Continue Shopping</a>
    </div>
<?php else: ?>

<table class="table cart-table">
    <thead>
        <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th></tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td>
                <a href="/products/detail.php?id=<?= $item['product_id'] ?>" style="text-decoration:none;color:#333;">
                    <?= htmlspecialchars($item['name']) ?>
                </a>
            </td>
            <td><?= formatPrice($item['price']) ?></td>
            <td>
                <form method="POST" action="/cart/cart_backend.php" style="display:inline-flex;gap:4px;align-items:center;">
                    <?php csrfField(); ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" style="width:55px;padding:5px;border:1px solid #ccc;border-radius:4px;">
                    <button type="submit" class="btn btn-secondary btn-sm">Update</button>
                </form>
            </td>
            <td><?= formatPrice($item['price'] * $item['quantity']) ?></td>
            <td>
                <form method="POST" action="/cart/cart_backend.php" style="display:inline;">
                    <?php csrfField(); ?>
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Remove this item?">Remove</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="cart-total">Total: <?= formatPrice($total) ?></div>

<div style="display:flex;gap:12px;justify-content:flex-end;flex-wrap:wrap;">
    <a href="/products/index.php" class="btn btn-secondary">Continue Shopping</a>
    <form method="POST" action="/cart/cart_backend.php" style="display:inline;">
        <?php csrfField(); ?>
        <input type="hidden" name="action" value="clear">
        <button type="submit" class="btn btn-danger" data-confirm="Clear entire cart?">Clear Cart</button>
    </form>
    <a href="/orders/checkout.php" class="btn btn-primary">Proceed to Checkout →</a>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

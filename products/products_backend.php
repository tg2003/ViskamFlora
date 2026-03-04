<?php
// products/products_backend.php
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$action = sanitize($_POST['action'] ?? $_GET['action'] ?? '');
$db     = getDB();

switch ($action) {

    case 'add':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid CSRF token.');
        $name     = sanitize($_POST['name'] ?? '');
        $catId    = sanitizeInt($_POST['category_id'] ?? 0);
        $desc     = sanitize($_POST['description'] ?? '');
        $price    = sanitizeFloat($_POST['price'] ?? 0);
        $stock    = sanitizeInt($_POST['stock'] ?? 0);
        $featured = sanitizeInt($_POST['is_featured'] ?? 0);
        $status   = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
        $image    = sanitize($_POST['image'] ?? '');

        if (empty($name) || $price <= 0) jsonResponse(false, 'Name and price are required.');

        $slug = makeSlug($name);
        // Ensure slug uniqueness
        $chk = $db->prepare("SELECT id FROM products WHERE slug = ?");
        $chk->bind_param('s', $slug);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) $slug .= '-' . time();

        $stmt = $db->prepare("INSERT INTO products (category_id, name, slug, description, price, stock, image, is_featured, status) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('isssdiiss', $catId, $name, $slug, $desc, $price, $stock, $image, $featured, $status);
        if ($stmt->execute()) {
            redirect('/admin/products.php', 'Product added successfully!');
        }
        jsonResponse(false, 'Failed to add product.');
        break;

    case 'update':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid CSRF token.');
        $id       = sanitizeInt($_POST['id'] ?? 0);
        $name     = sanitize($_POST['name'] ?? '');
        $catId    = sanitizeInt($_POST['category_id'] ?? 0);
        $desc     = sanitize($_POST['description'] ?? '');
        $price    = sanitizeFloat($_POST['price'] ?? 0);
        $stock    = sanitizeInt($_POST['stock'] ?? 0);
        $featured = sanitizeInt($_POST['is_featured'] ?? 0);
        $status   = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
        $image    = sanitize($_POST['image'] ?? '');

        if (!$id || empty($name) || $price <= 0) jsonResponse(false, 'Invalid data.');

        $stmt = $db->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image=?, is_featured=?, status=? WHERE id=?");
        $stmt->bind_param('issdiissi', $catId, $name, $desc, $price, $stock, $image, $featured, $status, $id);
        if ($stmt->execute()) {
            redirect('/admin/products.php', 'Product updated!');
        }
        jsonResponse(false, 'Update failed.');
        break;

    case 'delete':
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid CSRF token.');
        $id = sanitizeInt($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Invalid ID.');
        $stmt = $db->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            redirect('/admin/products.php', 'Product deleted.');
        }
        jsonResponse(false, 'Delete failed.');
        break;

    case 'toggle_status':
        $id = sanitizeInt($_GET['id'] ?? 0);
        if (!$id) redirect('/admin/products.php', 'Invalid ID.', 'danger');
        $stmt = $db->prepare("UPDATE products SET status = IF(status='active','inactive','active') WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        redirect('/admin/products.php', 'Product status updated.');
        break;

    default:
        redirect('/admin/products.php', 'Unknown action.', 'danger');
}

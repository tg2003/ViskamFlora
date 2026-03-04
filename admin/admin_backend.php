<?php
// admin/admin_backend.php
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$action = sanitize($_POST['action'] ?? $_GET['action'] ?? '');
$db     = getDB();

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    redirect('/admin/dashboard.php', 'Invalid CSRF token.', 'danger');
}

switch ($action) {

    case 'toggle_role':
        $userId = sanitizeInt($_POST['user_id'] ?? 0);
        if (!$userId || $userId == $_SESSION['user_id']) {
            redirect('/admin/users.php', 'Invalid operation.', 'danger');
        }
        $stmt = $db->prepare("UPDATE users SET role = IF(role='admin','customer','admin') WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        redirect('/admin/users.php', 'User role updated.');
        break;

    case 'delete_user':
        $userId = sanitizeInt($_POST['user_id'] ?? 0);
        if (!$userId || $userId == $_SESSION['user_id']) {
            redirect('/admin/users.php', 'Cannot delete this user.', 'danger');
        }
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        redirect('/admin/users.php', 'User deleted.');
        break;

    case 'update_arrangement':
        $id     = sanitizeInt($_POST['arrangement_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        $notes  = sanitize($_POST['admin_notes'] ?? '');
        $validStatuses = ['new','contacted','quoted','confirmed','completed','cancelled'];
        if (!$id || !in_array($status, $validStatuses)) {
            redirect('/admin/arrangements.php', 'Invalid data.', 'danger');
        }
        $stmt = $db->prepare("UPDATE wedding_arrangements SET status = ?, admin_notes = ? WHERE id = ?");
        $stmt->bind_param('ssi', $status, $notes, $id);
        $stmt->execute();
        redirect('/admin/arrangements.php?view=' . $id, 'Arrangement updated.');
        break;

    case 'add_category':
        $name = sanitize($_POST['name'] ?? '');
        $desc = sanitize($_POST['description'] ?? '');
        if (empty($name)) redirect('/admin/products.php', 'Category name required.', 'danger');
        $slug = makeSlug($name);
        $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?,?,?)");
        $stmt->bind_param('sss', $name, $slug, $desc);
        $stmt->execute();
        redirect('/admin/products.php', 'Category added.');
        break;

    case 'delete_category':
        $id = sanitizeInt($_POST['category_id'] ?? 0);
        if (!$id) redirect('/admin/products.php', 'Invalid ID.', 'danger');
        // Nullify product category before delete
        $db->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?")->bind_param('i', $id) && $db->query("UPDATE products SET category_id = NULL WHERE category_id = $id");
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        redirect('/admin/products.php', 'Category deleted.');
        break;

    default:
        redirect('/admin/dashboard.php', 'Unknown action.', 'danger');
}

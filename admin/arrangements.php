<?php
// admin/arrangements.php
$pageTitle = 'Wedding Arrangements – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
requireAdmin();
$db = getDB();

$statusFilter = sanitize($_GET['status'] ?? '');
$page    = max(1, sanitizeInt($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

$where  = '1=1';
$params = [];
$types  = '';
if ($statusFilter) {
    $where    = 'status = ?';
    $params[] = $statusFilter;
    $types   .= 's';
}

$cnt = $db->prepare("SELECT COUNT(*) as c FROM wedding_arrangements WHERE $where");
if ($params) $cnt->bind_param($types, ...$params);
$cnt->execute();
$total = $cnt->get_result()->fetch_assoc()['c'];

$params[] = $perPage; $params[] = $offset; $types .= 'ii';
$stmt = $db->prepare("SELECT * FROM wedding_arrangements WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$arrangements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$statusColors = ['new'=>'badge-warning','contacted'=>'badge-info','quoted'=>'badge-secondary','confirmed'=>'badge-success','completed'=>'badge-success','cancelled'=>'badge-danger'];

// Show single arrangement if ?view= is set
$viewing = null;
if (isset($_GET['view'])) {
    $vid   = sanitizeInt($_GET['view']);
    $vstmt = $db->prepare("SELECT * FROM wedding_arrangements WHERE id = ?");
    $vstmt->bind_param('i', $vid);
    $vstmt->execute();
    $viewing = $vstmt->get_result()->fetch_assoc();
}
?>

<div class="admin-layout">
    <div class="admin-sidebar">
        <ul>
            <li><a href="/admin/dashboard.php">📊 Dashboard</a></li>
            <li><a href="/admin/products.php">📦 Products</a></li>
            <li><a href="/admin/orders.php">🛍️ Orders</a></li>
            <li><a href="/admin/users.php">👥 Users</a></li>
            <li><a href="/admin/arrangements.php" class="active">💐 Wedding</a></li>
            <li><hr></li>
            <li><a href="/index.php">🌸 View Site</a></li>
            <li><a href="/auth/logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="admin-main">
        <h2 class="page-title">Wedding Arrangements</h2>

        <?php if ($viewing): ?>
        <!-- Detail view -->
        <div class="card mb-2">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h3 style="color:#2d5016;">Inquiry #<?= $viewing['id'] ?></h3>
                    <a href="/admin/arrangements.php" class="btn btn-secondary btn-sm">← Back</a>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <p><strong>Name:</strong> <?= htmlspecialchars($viewing['contact_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($viewing['contact_email']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($viewing['contact_phone']) ?></p>
                        <p><strong>Wedding Date:</strong> <?= htmlspecialchars($viewing['wedding_date']) ?></p>
                        <p><strong>Venue:</strong> <?= htmlspecialchars($viewing['venue'] ?: '–') ?></p>
                        <p><strong>Guests:</strong> <?= $viewing['guest_count'] ?: '–' ?></p>
                        <p><strong>Budget:</strong> <?= htmlspecialchars(str_replace('_',' ',$viewing['budget_range']) ?: '–') ?></p>
                    </div>
                    <div>
                        <p><strong>Arrangements:</strong> <?= htmlspecialchars($viewing['arrangement_types'] ?: '–') ?></p>
                        <p><strong>Colors:</strong> <?= htmlspecialchars($viewing['color_preferences'] ?: '–') ?></p>
                        <p><strong>Special Requests:</strong><br><?= nl2br(htmlspecialchars($viewing['special_requests'] ?: '–')) ?></p>
                        <p><strong>Submitted:</strong> <?= date('d M Y H:i', strtotime($viewing['created_at'])) ?></p>
                    </div>
                </div>
                <hr style="margin:16px 0;">
                <h4 style="margin-bottom:10px;">Update Status & Notes</h4>
                <form method="POST" action="/admin/admin_backend.php" style="display:flex;flex-direction:column;gap:10px;">
                    <?php csrfField(); ?>
                    <input type="hidden" name="action" value="update_arrangement">
                    <input type="hidden" name="arrangement_id" value="<?= $viewing['id'] ?>">
                    <select name="status" style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;max-width:220px;">
                        <?php foreach (['new','contacted','quoted','confirmed','completed','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $viewing['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="admin_notes" style="border:1px solid #ccc;border-radius:6px;padding:8px;min-height:80px;" placeholder="Internal admin notes..."><?= htmlspecialchars($viewing['admin_notes'] ?? '') ?></textarea>
                    <div><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status filters -->
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
            <a href="/admin/arrangements.php" class="btn btn-sm <?= !$statusFilter ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <?php foreach (['new','contacted','quoted','confirmed','completed','cancelled'] as $s): ?>
                <a href="/admin/arrangements.php?status=<?= $s ?>" class="btn btn-sm <?= $statusFilter === $s ? 'btn-primary' : 'btn-secondary' ?>"><?= ucfirst($s) ?></a>
            <?php endforeach; ?>
        </div>

        <table class="table">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Wedding Date</th><th>Budget</th><th>Status</th><th>Submitted</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($arrangements as $a): ?>
                <tr>
                    <td>#<?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['contact_name']) ?></td>
                    <td><?= htmlspecialchars($a['contact_email']) ?></td>
                    <td><?= htmlspecialchars($a['wedding_date']) ?></td>
                    <td><?= htmlspecialchars(str_replace('_',' ',$a['budget_range']) ?: '–') ?></td>
                    <td><span class="badge <?= $statusColors[$a['status']] ?? 'badge-secondary' ?>"><?= ucfirst($a['status']) ?></span></td>
                    <td><?= date('d M Y', strtotime($a['created_at'])) ?></td>
                    <td><a href="/admin/arrangements.php?view=<?= $a['id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($arrangements)): ?>
                    <tr><td colspan="8" class="no-data">No inquiries found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php echo paginate($total, $page, $perPage, '/admin/arrangements.php?' . ($statusFilter ? "status=$statusFilter&" : '')); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

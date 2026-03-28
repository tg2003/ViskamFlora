<?php
// viskam_flora_full/admin/arrangements.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ensure user is admin
if (!is_admin()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$success = '';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['req_id'], $_POST['status'])) {
    $req_id = (int)$_POST['req_id'];
    $status = sanitize_input($conn, $_POST['status']);
    
    $stmt = $conn->prepare("UPDATE wedding_arrangements SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $req_id);
    if ($stmt->execute()) {
        $success = "Arrangement request #$req_id updated to $status.";
    }
}

// Fetch Requests
$requests = $conn->query("SELECT * FROM wedding_arrangements ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedding Requests | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .details-row { display: none; background: #fdfdfd; }
        .details-row.active { display: table-row; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="admin-layout">
        <aside class="admin-sidebar" style="min-height: calc(100vh - 70px);">
            <h3 style="margin-bottom:20px; color:var(--accent-color); padding-left:10px;">Admin Panel</h3>
            <nav>
                <a href="dashboard.php">Dashboard Summary</a>
                <a href="products.php">Manage Products</a>
                <a href="orders.php">Manage Orders</a>
                <a href="users.php">Manage Users</a>
                <a href="arrangements.php" class="active">Wedding Requests</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <h2 class="mb-4">Wedding & Event Inquiries</h2>
            
            <?php if ($success): ?><div style="background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:15px;"><?= $success ?></div><?php endif; ?>

            <div style="background:#fff; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead style="background:#f4f6f9; border-bottom:1px solid #ddd;">
                        <tr>
                            <th style="padding:15px;">ID</th>
                            <th style="padding:15px;">Client Name</th>
                            <th style="padding:15px;">Contact</th>
                            <th style="padding:15px;">Event Date</th>
                            <th style="padding:15px;">Status</th>
                            <th style="padding:15px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($req = $requests->fetch_assoc()): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:15px; font-weight:bold;">#<?= $req['id'] ?></td>
                                <td style="padding:15px;"><?= htmlspecialchars($req['name']) ?></td>
                                <td style="padding:15px;">
                                    <?= htmlspecialchars($req['phone']) ?><br>
                                    <small class="text-muted"><a href="mailto:<?= htmlspecialchars($req['email']) ?>"><?= htmlspecialchars($req['email']) ?></a></small>
                                </td>
                                <td style="padding:15px; color:var(--primary-color); font-weight:bold;"><?= date('M j, Y', strtotime($req['event_date'])) ?></td>
                                <td style="padding:15px;">
                                    <form action="" method="POST" style="display:flex; gap:10px;">
                                        <input type="hidden" name="req_id" value="<?= $req['id'] ?>">
                                        <select name="status" class="form-control" style="padding:5px; width:auto; font-size:0.9rem;">
                                            <option value="Pending" <?= $req['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Reviewed" <?= $req['status'] == 'Reviewed' ? 'selected' : '' ?>>Reviewed</option>
                                            <option value="Accepted" <?= $req['status'] == 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                                            <option value="Completed" <?= $req['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                        <button type="submit" class="btn btn-outline" style="padding:5px 10px; font-size:0.8rem;">Save</button>
                                    </form>
                                </td>
                                <td style="padding:15px; text-align:right;">
                                    <button onclick="toggleDetails(<?= $req['id'] ?>)" class="btn btn-primary" style="padding:5px 10px; font-size:0.8rem;">View Details</button>
                                </td>
                            </tr>
                            <tr id="req-<?= $req['id'] ?>" class="details-row">
                                <td colspan="6" style="padding:20px; border-bottom:2px solid #ddd; background:#fefefe;">
                                    <div style="display:flex; gap:40px;">
                                        <div style="flex:1;">
                                            <p style="margin-bottom:10px;"><strong>Venue:</strong> <?= htmlspecialchars($req['venue']) ?: 'Not specified' ?></p>
                                            <p style="margin-bottom:10px;"><strong>Submitted On:</strong> <?= date('F j, Y g:i a', strtotime($req['created_at'])) ?></p>
                                            <?php if($req['user_id']): ?>
                                                <p style="color:var(--primary-color); font-size:0.9rem;">Registered Customer Account</p>
                                            <?php endif; ?>
                                        </div>
                                        <div style="flex:2;">
                                            <p style="margin-bottom:5px;"><strong>Client Requirements & Vision:</strong></p>
                                            <div style="background:#fff; padding:15px; border:1px solid #eee; border-radius:5px; font-size:0.95rem; line-height:1.6;">
                                                <?= nl2br(htmlspecialchars($req['details'])) ?: 'No additional details provided.' ?>
                                            </div>
                                            
                                            <div style="margin-top:15px; text-align:right;">
                                                <a href="mailto:<?= htmlspecialchars($req['email']) ?>?subject=Viskam Flora - Wedding Inquiry #<?= $req['id'] ?>" class="btn btn-primary" style="padding:8px 15px; font-size:0.9rem;">Send Email to Client</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function toggleDetails(id) {
            const row = document.getElementById('req-' + id);
            row.classList.toggle('active');
        }
    </script>
</body>
</html>


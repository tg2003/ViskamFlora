<?php
// orders/wedding_backend.php
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/orders/wedding_page.php');
if (!verifyCsrf($_POST['csrf_token'] ?? '')) redirect('/orders/wedding_page.php', 'Invalid request.', 'danger');

$name         = sanitize($_POST['contact_name'] ?? '');
$email        = sanitize($_POST['contact_email'] ?? '');
$phone        = sanitize($_POST['contact_phone'] ?? '');
$weddingDate  = sanitize($_POST['wedding_date'] ?? '');
$venue        = sanitize($_POST['venue'] ?? '');
$guestCount   = sanitizeInt($_POST['guest_count'] ?? 0);
$budget       = sanitize($_POST['budget_range'] ?? '');
$arrangements = $_POST['arrangements'] ?? [];
$colors       = sanitize($_POST['color_preferences'] ?? '');
$special      = sanitize($_POST['special_requests'] ?? '');
$userId       = isLoggedIn() ? $_SESSION['user_id'] : null;

if (empty($name) || empty($email) || empty($phone) || empty($weddingDate)) {
    redirect('/orders/wedding_page.php', 'Name, email, phone and wedding date are required.', 'danger');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('/orders/wedding_page.php', 'Invalid email address.', 'danger');
}

$arrangementStr = implode(', ', array_map('htmlspecialchars', $arrangements));

$db   = getDB();
$stmt = $db->prepare("
    INSERT INTO wedding_arrangements
        (user_id, contact_name, contact_email, contact_phone, wedding_date, venue, guest_count, budget_range, arrangement_types, color_preferences, special_requests)
    VALUES (?,?,?,?,?,?,?,?,?,?,?)
");
$stmt->bind_param('isssssissss', $userId, $name, $email, $phone, $weddingDate, $venue, $guestCount, $budget, $arrangementStr, $colors, $special);

if ($stmt->execute()) {
    redirect('/orders/wedding_page.php', 'Your wedding inquiry has been submitted! We\'ll contact you within 24 hours.');
}
redirect('/orders/wedding_page.php', 'Failed to submit inquiry. Please try again.', 'danger');

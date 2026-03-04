<?php
// auth/register_backend.php
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/auth/register_page.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    redirect('/ViskamFlora/auth/register_page.php', 'Invalid request.', 'danger');
}

$name     = sanitize($_POST['name'] ?? '');
$email    = sanitize($_POST['email'] ?? '');
$phone    = sanitize($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

// Validate
if (empty($name) || empty($email) || empty($password)) {
    redirect('/ViskamFlora/auth/register_page.php', 'Name, email, and password are required.', 'danger');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('/ViskamFlora/auth/register_page.php', 'Invalid email address.', 'danger');
}
if (strlen($password) < 6) {
    redirect('/ViskamFlora/auth/register_page.php', 'Password must be at least 6 characters.', 'danger');
}
if ($password !== $confirm) {
    redirect('/ViskamFlora/auth/register_page.php', 'Passwords do not match.', 'danger');
}

$db = getDB();

// Check email uniqueness
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    redirect('/ViskamFlora/auth/register_page.php', 'This email is already registered.', 'danger');
}

// Insert
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt   = $db->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $name, $email, $phone, $hashed);
if (!$stmt->execute()) {
    redirect('/ViskamFlora/auth/register_page.php', 'Registration failed. Please try again.', 'danger');
}

$userId = $db->insert_id;

// Auto login
$_SESSION['user_id'] = $userId;
$_SESSION['name']    = $name;
$_SESSION['email']   = $email;
$_SESSION['role']    = 'customer';

redirect('/ViskamFlora/index.php', 'Account created! Welcome to Viskam Flora, ' . $name . '!');

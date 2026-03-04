<?php
// auth/login_backend.php
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/viskam_flora_full/auth/login_page.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    redirect('/viskam_flora_full/auth/login_page.php', 'Invalid request. Please try again.', 'danger');
}

$email    = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$redirect = sanitize($_POST['redirect'] ?? '/viskam_flora_full/index.php');

if (empty($email) || empty($password)) {
    redirect('/viskam_flora_full/auth/login_page.php', 'Email and password are required.', 'danger');
}

$db   = getDB();
$stmt = $db->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    redirect('/viskam_flora_full/auth/login_page.php', 'Invalid email or password.', 'danger');
}

// Set session

$_SESSION['user_id'] = $user['id'];
$_SESSION['name']    = $user['name'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'];

// Redirect
if ($user['role'] === 'admin') {
    redirect('/viskam_flora_full/admin/dashboard.php', 'Welcome back, ' . $user['name'] . '!');
}

redirect('/viskam_flora_full/index.php', 'Welcome back, ' . $user['name'] . '!');

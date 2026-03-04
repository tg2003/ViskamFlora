<?php
// includes/helpers.php

require_once __DIR__ . '/../config/db.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Auth helpers ──────────────────────────────────────────────
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /auth/login_page.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        http_response_code(403);
        die('Access denied.');
    }
}

function currentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, phone, address, role FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// ─── Sanitization ──────────────────────────────────────────────
function sanitize($value) {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function sanitizeInt($value) {
    return (int) $value;
}

function sanitizeFloat($value) {
    return (float) $value;
}

// ─── Response helpers ──────────────────────────────────────────
function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
    header("Location: $url");
    exit;
}

function flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function showFlash() {
    $flash = flash();
    if ($flash) {
        $type = $flash['type'] === 'success' ? 'success' : 'danger';
        echo "<div class='alert alert-{$type}'>" . htmlspecialchars($flash['message']) . "</div>";
    }
}

// ─── Slug generator ────────────────────────────────────────────
function makeSlug($text) {
    return preg_replace('/[^a-z0-9-]/', '-', strtolower(trim($text)));
}

// ─── Price formatter ───────────────────────────────────────────
function formatPrice($amount) {
    return 'LKR ' . number_format($amount, 2);
}

// ─── Cart count ────────────────────────────────────────────────
function cartCount() {
    if (!isLoggedIn()) return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return (int)($result['total'] ?? 0);
}

// ─── CSRF helpers ──────────────────────────────────────────────
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    echo '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

// ─── Pagination ────────────────────────────────────────────────
function paginate($total, $page, $perPage, $url) {
    $totalPages = ceil($total / $perPage);
    if ($totalPages <= 1) return '';
    $html = '<nav><ul class="pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $page ? ' active' : '';
        $sep = strpos($url, '?') !== false ? '&' : '?';
        $html .= "<li class='page-item{$active}'><a class='page-link' href='{$url}{$sep}page={$i}'>{$i}</a></li>";
    }
    $html .= '</ul></nav>';
    return $html;
}

<?php
/**
 * Admin auth bootstrap (used by every admin page except login)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'gc_maxlifetime'  => 7200,
    ]);
}

define('NIIB_INIT', true);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/recaptcha.php';

// Session timeout (60 min)
$timeout = 3600;
if (!empty($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: ' . ADMIN_URL . '/login.php?timeout=1');
    exit;
}
$_SESSION['admin_last_activity'] = time();

if (empty($_SESSION['admin_id'])) {
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

// Reload admin info
try {
    $stmt = db()->prepare("SELECT id, name, email, role, avatar FROM admin_users WHERE id=? AND status=1");
    $stmt->execute([$_SESSION['admin_id']]);
    $current_admin = $stmt->fetch();
    if (!$current_admin) {
        session_destroy();
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
} catch (Throwable $e) {
    die('DB error. Please run install.php.');
}

function require_super_admin() {
    global $current_admin;
    if ($current_admin['role'] !== 'super_admin') {
        http_response_code(403);
        die('Access denied — Super Admin only.');
    }
}

<?php
/**
 * Database Configuration
 * NIIB Institute
 */

// Prevent direct access
if (!defined('NIIB_INIT')) {
    http_response_code(403);
    exit('Forbidden');
}

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'u562121569_newimagephp');
define('DB_USER', 'u562121569_newimagephp');
define('DB_PASS', '2>L;?6X&5tz');
define('DB_CHARSET', 'utf8mb4');

/* ─────────────────────────────────────────────────────────────────────
 * SITE BASE URL — fully auto-detecting
 * ─────────────────────────────────────────────────────────────────────
 *
 * The same code runs unchanged on:
     *   • Local subfolder:  https://www.newimageinstitute.com
 *   • Live root domain: https://www.newimageinstitute.com
 *   • Live subfolder:   https://example.com/some/path/
 *
 * SITE_BASE_PATH is detected by comparing the app folder against the
 * web server's DOCUMENT_ROOT. SITE_URL is built from scheme + host +
 * that base path. Only set SITE_URL_OVERRIDE if auto-detection fails
 * for your hosting setup (rare — e.g. unusual reverse-proxy headers).
 * ───────────────────────────────────────────────────────────────────── */
define('SITE_URL_OVERRIDE', 'https://newimageinstitute.com');

if (!defined('SITE_BASE_PATH')) {
    $_niib_app_dir  = str_replace('\\', '/', dirname(__DIR__));
    $_niib_doc_root = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
    if ($_niib_doc_root !== '' && stripos($_niib_app_dir, $_niib_doc_root) === 0) {
        define('SITE_BASE_PATH', rtrim(substr($_niib_app_dir, strlen($_niib_doc_root)), '/'));
    } else {
        define('SITE_BASE_PATH', '');
    }
    unset($_niib_app_dir, $_niib_doc_root);
}

if (SITE_URL_OVERRIDE !== '') {
    define('SITE_URL', rtrim(SITE_URL_OVERRIDE, '/'));
} else {
    $_niib_https = (
        (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ||
        (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ||
        (($_SERVER['SERVER_PORT'] ?? '') == 443)
    );
    $_niib_scheme = $_niib_https ? 'https' : 'http';
    $_niib_host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('SITE_URL', $_niib_scheme . '://' . $_niib_host . SITE_BASE_PATH);
    unset($_niib_https, $_niib_scheme, $_niib_host);
}
define('ADMIN_URL',  SITE_URL . '/admin');
define('UPLOAD_URL', SITE_URL . '/uploads');
define('ASSETS_URL', SITE_URL . '/assets');

// Server paths
define('BASE_PATH', dirname(__DIR__));
define('ADMIN_PATH', BASE_PATH . '/admin');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Environment
define('ENVIRONMENT', 'development'); // 'development' or 'production'

if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

/**
 * PDO connection
 */
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (ENVIRONMENT === 'development') {
                die('Database connection failed: ' . $e->getMessage());
            } else {
                error_log('DB connection error: ' . $e->getMessage());
                die('Service temporarily unavailable.');
            }
        }
    }
    return $pdo;
}

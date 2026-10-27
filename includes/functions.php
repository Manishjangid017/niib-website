<?php
/**
 * Common helper functions
 */

if (!defined('NIIB_INIT')) { http_response_code(403); exit('Forbidden'); }

// ---------------------------------------------------------
// Output escaping
// ---------------------------------------------------------
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function clean($value) {
    return trim(strip_tags((string)$value));
}

// ---------------------------------------------------------
// CSRF protection
// ---------------------------------------------------------
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify() {
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ---------------------------------------------------------
// Redirect
// ---------------------------------------------------------
function redirect($url) {
    if (!headers_sent()) {
        header('Location: ' . $url);
    } else {
        echo '<script>window.location.href=' . json_encode($url) . ';</script>';
    }
    exit;
}

// ---------------------------------------------------------
// Flash messages
// ---------------------------------------------------------
function set_flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function get_flash($type) {
    if (!empty($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

// ---------------------------------------------------------
// Site settings retrieval
// ---------------------------------------------------------
function get_settings() {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $rows = db()->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
            foreach ($rows as $row) $cache[$row['setting_key']] = $row['setting_value'];
        } catch (Throwable $e) { $cache = []; }
    }
    return $cache;
}

function setting($key, $default = '') {
    $s = get_settings();
    return $s[$key] ?? $default;
}

function get_seo_settings() {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $rows = db()->query("SELECT setting_key, setting_value FROM seo_settings")->fetchAll();
            foreach ($rows as $row) $cache[$row['setting_key']] = $row['setting_value'];
        } catch (Throwable $e) { $cache = []; }
    }
    return $cache;
}

function seo($key, $default = '') {
    $s = get_seo_settings();
    return $s[$key] ?? $default;
}

// ---------------------------------------------------------
// Slug generator
// ---------------------------------------------------------
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

function unique_slug($table, $title, $ignoreId = 0) {
    $slug = slugify($title);
    $base = $slug;
    $i = 2;
    while (true) {
        $stmt = db()->prepare("SELECT id FROM `{$table}` WHERE slug = ? AND id <> ? LIMIT 1");
        $stmt->execute([$slug, $ignoreId]);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i;
        $i++;
    }
}

// ---------------------------------------------------------
// File upload helper
// ---------------------------------------------------------
function upload_file($fileInput, $subfolder = 'media', $allowed = ['jpg','jpeg','png','gif','webp','svg','pdf','doc','docx'], $maxBytes = 10485760) {
    if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) return ['ok' => false, 'error' => 'No file uploaded'];

    $file = $_FILES[$fileInput];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) return ['ok' => false, 'error' => 'File type not allowed'];
    if ($file['size'] > $maxBytes) return ['ok' => false, 'error' => 'File too large (max ' . round($maxBytes / 1048576) . 'MB)'];

    $dir = UPLOAD_PATH . '/' . $subfolder;
    if (!is_dir($dir)) @mkdir($dir, 0755, true);

    $newName = uniqid('f_') . '_' . time() . '.' . $ext;
    $target = $dir . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $target)) return ['ok' => false, 'error' => 'Move failed'];

    $relPath = 'uploads/' . $subfolder . '/' . $newName;

    // Save to media library if available
    try {
        $stmt = db()->prepare("INSERT INTO media_library (file_name, original_name, file_path, file_type, file_size, uploaded_by) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$newName, $file['name'], $relPath, $ext, $file['size'], $_SESSION['admin_id'] ?? null]);
    } catch (Throwable $e) {}

    return ['ok' => true, 'path' => $relPath, 'filename' => $newName];
}

function delete_uploaded_file($relPath) {
    if (!$relPath) return;
    $abs = BASE_PATH . '/' . ltrim($relPath, '/');
    if (is_file($abs) && strpos(realpath($abs), realpath(UPLOAD_PATH)) === 0) {
        @unlink($abs);
    }
}

// ---------------------------------------------------------
// Image URL helper
// ---------------------------------------------------------
function img_url($path, $default = 'assets/images/placeholder.svg') {
    if (empty($path)) $path = $default;
    if (preg_match('~^https?://~', $path)) return $path;
    return SITE_URL . '/' . ltrim($path, '/');
}

function url($path = '') {
    return SITE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Resolve any href value (DB-stored, relative, or absolute) to a fully-qualified URL.
 * Use everywhere we render user-/admin-supplied URLs so they never break when clicked
 * from a deeper route like /blog/{slug} or /course/{slug}.
 *
 *   niib_link('courses.php')      → http://host/niibinstitute/courses.php
 *   niib_link('/about')           → http://host/about            (root-absolute preserved)
 *   niib_link('https://x.com')    → https://x.com                (already absolute)
 *   niib_link('')                 → '#'                          (safe fallback)
 */
function niib_link($u) {
    $u = trim((string)$u);
    if ($u === '') return '#';
    if (preg_match('~^(https?:)?//~i', $u)) return $u;     // already absolute
    if (strpos($u, '/') === 0)              return $u;     // root-absolute
    if (strpos($u, '#') === 0)              return $u;     // pure fragment
    if (preg_match('~^(mailto:|tel:|javascript:)~i', $u)) return $u;
    return rtrim(SITE_URL, '/') . '/' . ltrim($u, '/');
}

// ---------------------------------------------------------
// Excerpt
// ---------------------------------------------------------
function excerpt($text, $words = 25) {
    $text = strip_tags($text);
    $arr = preg_split('/\s+/', trim($text));
    if (count($arr) <= $words) return $text;
    return implode(' ', array_slice($arr, 0, $words)) . '...';
}

// ---------------------------------------------------------
// Pagination
// ---------------------------------------------------------
function paginate($total, $perPage, $current, $baseUrl) {
    $totalPages = max(1, (int)ceil($total / $perPage));
    if ($totalPages < 2) return '';
    $current = max(1, min($current, $totalPages));
    $glue = (strpos($baseUrl, '?') === false) ? '?' : '&';

    $html = '<nav><ul class="pagination">';
    $html .= '<li class="page-item ' . ($current == 1 ? 'disabled' : '') . '"><a class="page-link" href="' . ($current == 1 ? '#' : $baseUrl . $glue . 'page=' . ($current - 1)) . '">&laquo;</a></li>';

    $start = max(1, $current - 2);
    $end = min($totalPages, $current + 2);

    for ($i = $start; $i <= $end; $i++) {
        $html .= '<li class="page-item ' . ($i == $current ? 'active' : '') . '"><a class="page-link" href="' . $baseUrl . $glue . 'page=' . $i . '">' . $i . '</a></li>';
    }

    $html .= '<li class="page-item ' . ($current == $totalPages ? 'disabled' : '') . '"><a class="page-link" href="' . ($current == $totalPages ? '#' : $baseUrl . $glue . 'page=' . ($current + 1)) . '">&raquo;</a></li>';
    $html .= '</ul></nav>';
    return $html;
}

// ---------------------------------------------------------
// Date formatter
// ---------------------------------------------------------
function fdate($dt, $fmt = 'd M Y') {
    if (!$dt || $dt === '0000-00-00' || $dt === '0000-00-00 00:00:00') return '';
    return date($fmt, strtotime($dt));
}

// ---------------------------------------------------------
// Admin logging
// ---------------------------------------------------------
function log_admin_action($action, $module = '', $description = '') {
    try {
        $stmt = db()->prepare("INSERT INTO admin_activity_logs (admin_id, action, module, description, ip_address, user_agent) VALUES (?,?,?,?,?,?)");
        $stmt->execute([
            $_SESSION['admin_id'] ?? null,
            $action,
            $module,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        ]);
    } catch (Throwable $e) {}
}

// ---------------------------------------------------------
// Menu builder
// ---------------------------------------------------------
function get_menu_items($location = 'header') {
    static $cache = [];
    if (isset($cache[$location])) return $cache[$location];
    $stmt = db()->prepare("SELECT * FROM menu_items WHERE (menu_location = ? OR menu_location = 'both') AND status = 1 ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$location]);
    $rows = $stmt->fetchAll();
    $tree = [];
    foreach ($rows as $r) $tree[$r['parent_id']][] = $r;
    $cache[$location] = $tree;
    return $tree;
}

function render_menu($tree, $parentId = 0, $level = 0) {
    if (empty($tree[$parentId])) return '';
    $html = '';
    foreach ($tree[$parentId] as $item) {
        $hasChildren = !empty($tree[$item['id']]);
        $url = $item['url'];
        if (!preg_match('~^https?://~i', $url) && strpos($url, '/') !== 0) {
            $url = SITE_URL . '/' . ltrim($url, '/');
        }
        if ($level === 0) {
            $html .= '<li class="nav-item ' . ($hasChildren ? 'dropdown' : '') . '">';
            $html .= '<a class="nav-link ' . ($hasChildren ? 'dropdown-toggle' : '') . '" href="' . e($url) . '" target="' . e($item['target']) . '" ' . ($hasChildren ? 'data-bs-toggle="dropdown"' : '') . '>' . e($item['title']) . '</a>';
            if ($hasChildren) {
                $html .= '<ul class="dropdown-menu">';
                foreach ($tree[$item['id']] as $child) {
                    $curl = $child['url'];
                    if (!preg_match('~^https?://~i', $curl) && strpos($curl, '/') !== 0) $curl = SITE_URL . '/' . ltrim($curl, '/');
                    $html .= '<li><a class="dropdown-item" href="' . e($curl) . '" target="' . e($child['target']) . '">' . e($child['title']) . '</a></li>';
                }
                $html .= '</ul>';
            }
            $html .= '</li>';
        } else {
            $html .= '<li><a href="' . e($url) . '">' . e($item['title']) . '</a></li>';
        }
    }
    return $html;
}

// ---------------------------------------------------------
// Header/Footer custom code
// ---------------------------------------------------------
function render_header_codes($position = 'head') {
    try {
        $stmt = db()->prepare("SELECT code FROM header_codes WHERE status = 1 AND position = ?");
        $stmt->execute([$position]);
        $out = '';
        foreach ($stmt->fetchAll() as $row) $out .= "\n" . $row['code'];
        return $out;
    } catch (Throwable $e) { return ''; }
}

function render_footer_codes() {
    try {
        $rows = db()->query("SELECT code FROM footer_codes WHERE status = 1")->fetchAll();
        $out = '';
        foreach ($rows as $row) $out .= "\n" . $row['code'];
        return $out;
    } catch (Throwable $e) { return ''; }
}

function render_custom_css() {
    try {
        $row = db()->query("SELECT code FROM custom_css WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch();
        return $row ? $row['code'] : '';
    } catch (Throwable $e) { return ''; }
}

function render_custom_js() {
    try {
        $row = db()->query("SELECT code FROM custom_js WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch();
        return $row ? $row['code'] : '';
    } catch (Throwable $e) { return ''; }
}

// ---------------------------------------------------------
// HTML Blocks renderer
// ---------------------------------------------------------
function render_html_blocks($pageSlug = null) {
    try {
        $pdo = db();
        // Get blocks that are either global (page_slug is empty/null) or match the current page
        if ($pageSlug) {
            $stmt = $pdo->prepare("SELECT * FROM html_blocks WHERE status = 1 AND (page_slug = '' OR page_slug IS NULL OR page_slug = ?) ORDER BY sort_order, id");
            $stmt->execute([$pageSlug]);
        } else {
            $stmt = $pdo->query("SELECT * FROM html_blocks WHERE status = 1 AND (page_slug = '' OR page_slug IS NULL) ORDER BY sort_order, id");
        }
        $blocks = $stmt->fetchAll();
        $html = '';
        foreach ($blocks as $block) {
            $cssClass = !empty($block['css_class']) ? ' ' . e($block['css_class']) : '';
            $html .= '<div class="html-block' . $cssClass . '" data-block-key="' . e($block['block_key']) . '">';
            $html .= $block['html_content'];
            $html .= '</div>';
        }
        return $html;
    } catch (Throwable $e) { return ''; }
}

// ---------------------------------------------------------
// Get socials
// ---------------------------------------------------------
function get_social_links() {
    try {
        $rows = db()->query("SELECT * FROM social_links WHERE status = 1 ORDER BY sort_order")->fetchAll();
        return $rows;
    } catch (Throwable $e) { return []; }
}

// ---------------------------------------------------------
// Current URL
// ---------------------------------------------------------
function current_url() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    return $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
}

// ---------------------------------------------------------
// Validate email
// ---------------------------------------------------------
function valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Output filter: add loading="lazy" + decoding="async" to <img> tags
 * that don't already have them. Run on the buffered HTML in footer.php.
 */
function niib_optimize_images_html(string $html): string {
    // Only mutate <img> tags
    return preg_replace_callback('/<img\b([^>]*)>/i', function ($m) {
        $attrs = $m[1];

        // Skip images that are explicitly marked as above-the-fold / eager
        // (hero, logo brand, background images, or already has fetchpriority/loading)
        if (preg_match('/\bclass="[^"]*\b(brand|hero-bg|fr-hero-bg|loc-hero-bg)\b/i', $attrs)) {
            return $m[0];
        }
        // Skip if already has fetchpriority="high" or loading="eager" (LCP images)
        if (preg_match('/\bfetchpriority\s*=\s*["\']?high/i', $attrs)) {
            return $m[0];
        }
        if (preg_match('/\bloading\s*=\s*["\']?eager/i', $attrs)) {
            return $m[0];
        }

        if (!preg_match('/\bloading=/i', $attrs)) {
            $attrs .= ' loading="lazy"';
        }
        if (!preg_match('/\bdecoding=/i', $attrs)) {
            $attrs .= ' decoding="async"';
        }
        if (!preg_match('/\balt=/i', $attrs)) {
            $attrs .= ' alt=""';   // empty alt is valid for decorative imagery
        }
        return '<img' . $attrs . '>';
    }, $html);
}

<?php
require __DIR__ . '/includes/init.php';
http_response_code(404);

// Check redirects first
$pdo = db();
try {
    $reqUri = $_SERVER['REQUEST_URI'] ?? '';
    $reqPath = parse_url($reqUri, PHP_URL_PATH);
    if ($reqPath) {
        if (defined('SITE_BASE_PATH') && SITE_BASE_PATH !== '') {
            $reqPath = preg_replace('#^' . preg_quote(SITE_BASE_PATH, '#') . '(?=/|$)#', '', $reqPath);
        }
        $stmt = $pdo->prepare("SELECT * FROM redirects WHERE status=1 AND (old_url=? OR old_url=?) LIMIT 1");
        $stmt->execute([$reqPath, $reqUri]);
        $rd = $stmt->fetch();
        if ($rd) {
            $pdo->prepare("UPDATE redirects SET hits = hits + 1 WHERE id=?")->execute([$rd['id']]);
            $code = $rd['type']==='301' ? 301 : 302;
            header('Location: ' . $rd['new_url'], true, $code);
            exit;
        }
    }
} catch (Throwable $e) {}

// Pattern 301s for legacy WP URL groups (post-migration cleanup)
$legacyPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$legacyMap = ['#^/classes/#'=>'/courses','#^/services/#'=>'/courses','#^/jp_students_result/#'=>'/track-certificate','#^/cpt_[a-z]+/#'=>'/courses','#^/product/#'=>'/courses','#^/product-category/#'=>'/courses'];
foreach ($legacyMap as $re=>$t) { if (preg_match($re, $legacyPath)) { header('Location: ' . rtrim(SITE_URL,'/') . $t, true, 301); exit; } }

$home = SITE_URL . '/';
$page_seo['title']  = '404 - Page Not Found';
$page_seo['robots'] = 'noindex, nofollow';

// Server-side fallback redirect after 5 seconds (works even with JS disabled)
header('Refresh: 5; url=' . $home);

include 'includes/header.php';
?>
<section class="text-center" style="padding:100px 0">
<div class="container">
    <h1 style="font-size:8rem;font-weight:800;color:var(--primary);line-height:1">404</h1>
    <h2>Page Not Found</h2>
    <p class="text-muted">The page you're looking for doesn't exist or has been moved.</p>
    <p class="text-muted" style="margin-top:10px">
        Redirecting to the homepage in <strong id="countdown" style="color:var(--secondary-container,#fe5e1e)">5</strong> seconds…
    </p>
    <a href="<?= e($home) ?>" class="btn btn-primary btn-lg mt-3"><i class="bi bi-house-door"></i> Go to Homepage Now</a>
</div>
</section>
<script>
(function(){
    var n = 5;
    var el = document.getElementById('countdown');
    var t = setInterval(function(){
        n--;
        if (el) el.textContent = n;
        if (n <= 0) { clearInterval(t); window.location.replace(<?= json_encode($home) ?>); }
    }, 1000);
})();
</script>
<?php include 'includes/footer.php'; ?>

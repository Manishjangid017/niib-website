<?php
require __DIR__ . '/includes/init.php';
header('Content-Type: application/xml; charset=utf-8');

$pdo  = db();
$base = rtrim(SITE_URL, '/');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
   . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
   . 'xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

/** Emit one <url> entry. */
function sm_url(string $loc, ?string $lastmod = null, string $changefreq = 'weekly', float $priority = 0.7, ?string $imageLoc = null, ?string $imageTitle = null): void {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($loc, ENT_QUOTES | ENT_XML1) . "</loc>\n";
    if ($lastmod) echo "    <lastmod>$lastmod</lastmod>\n";
    echo "    <changefreq>$changefreq</changefreq>\n";
    echo "    <priority>" . number_format($priority, 1) . "</priority>\n";
    if ($imageLoc) {
        echo "    <image:image>\n";
        echo "      <image:loc>" . htmlspecialchars($imageLoc, ENT_QUOTES | ENT_XML1) . "</image:loc>\n";
        if ($imageTitle) echo "      <image:title>" . htmlspecialchars($imageTitle, ENT_QUOTES | ENT_XML1) . "</image:title>\n";
        echo "    </image:image>\n";
    }
    echo "  </url>\n";
}

// -------- Static / core routes --------
$today = date('Y-m-d');
sm_url($base . '/',              $today, 'daily',   1.0);
sm_url($base . '/about',         $today, 'monthly', 0.8);
sm_url($base . '/courses',       $today, 'weekly',  0.9);
sm_url($base . '/blog',          $today, 'daily',   0.9);
sm_url($base . '/contact',       $today, 'monthly', 0.7);
sm_url($base . '/franchise',     $today, 'monthly', 0.8);
sm_url($base . '/placement',     $today, 'monthly', 0.7);
sm_url($base . '/locations',     $today, 'monthly', 0.7);

// -------- Pages --------
try {
    foreach ($pdo->query("SELECT slug, updated_at, banner_image FROM pages WHERE status=1") as $r) {
        // SEO-friendly URL: /{slug} (was /page/{slug}, now 301'd)
        $loc      = $base . '/' . rawurlencode($r['slug']);
        $mod      = substr((string)$r['updated_at'], 0, 10) ?: $today;
        $imgUrl   = !empty($r['banner_image']) ? $base . '/' . ltrim($r['banner_image'], '/') : null;
        sm_url($loc, $mod, 'monthly', 0.6, $imgUrl, $r['slug']);
    }
} catch (Throwable $e) {}

// -------- Courses --------
try {
    foreach ($pdo->query("SELECT slug, title, updated_at, image FROM courses WHERE status=1") as $r) {
        $loc    = $base . '/course/' . rawurlencode($r['slug']);
        $mod    = substr((string)$r['updated_at'], 0, 10) ?: $today;
        $imgUrl = !empty($r['image']) ? $base . '/' . ltrim($r['image'], '/') : null;
        sm_url($loc, $mod, 'weekly', 0.9, $imgUrl, $r['title']);
    }
} catch (Throwable $e) {}

// -------- Course categories (filter URLs) --------
try {
    foreach ($pdo->query("SELECT slug, name FROM course_categories WHERE status=1") as $r) {
        $loc = $base . '/courses.php?cat=' . rawurlencode($r['slug']);
        sm_url($loc, $today, 'weekly', 0.6);
    }
} catch (Throwable $e) {}

// -------- Blog posts --------
try {
    foreach ($pdo->query("SELECT slug, title, updated_at, publish_date, featured_image FROM blog_posts WHERE status=1") as $r) {
        $loc    = $base . '/blog/' . rawurlencode($r['slug']);
        $mod    = substr((string)($r['updated_at'] ?: $r['publish_date']), 0, 10) ?: $today;
        $imgUrl = !empty($r['featured_image']) ? $base . '/' . ltrim($r['featured_image'], '/') : null;
        sm_url($loc, $mod, 'monthly', 0.8, $imgUrl, $r['title']);
    }
} catch (Throwable $e) {}

// -------- Blog categories --------
try {
    foreach ($pdo->query("SELECT slug, name FROM blog_categories WHERE status=1") as $r) {
        $loc = $base . '/blog.php?cat=' . rawurlencode($r['slug']);
        sm_url($loc, $today, 'weekly', 0.5);
    }
} catch (Throwable $e) {}

echo '</urlset>';

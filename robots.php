<?php
require __DIR__ . '/includes/init.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$base    = rtrim(SITE_URL, '/');
$sitemap = $base . '/sitemap.xml';

// Allow the admin to override entirely via SEO settings
$custom = trim((string)seo('robots_txt', ''));
if ($custom !== '') {
    echo $custom;
    if (stripos($custom, 'Sitemap:') === false) {
        echo "\n\nSitemap: {$sitemap}";
    }
    exit;
}

// Sensible default robots.txt
echo "# robots.txt — " . setting('site_name', 'NIIB Institute') . "\n";
echo "# Last updated: " . date('Y-m-d') . "\n\n";

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /api/\n";
echo "Disallow: /config/\n";
echo "Disallow: /includes/\n";
echo "Disallow: /database/\n";
echo "Disallow: /install.php\n";
echo "Disallow: /uploads/media/\n";
echo "Disallow: /*?q=\n";
echo "Disallow: /*?page=\n";
echo "Allow: /uploads/courses/\n";
echo "Allow: /uploads/blogs/\n";
echo "Allow: /uploads/sliders/\n";
echo "Allow: /assets/\n\n";

// Block aggressive AI crawlers? (commented for now — admin can enable in SEO module if desired)
// echo "User-agent: GPTBot\nDisallow: /\n\n";
// echo "User-agent: ClaudeBot\nDisallow: /\n\n";

echo "User-agent: Googlebot-Image\n";
echo "Allow: /uploads/\n";
echo "Allow: /assets/images/\n\n";

echo "Sitemap: {$sitemap}\n";

<?php
require __DIR__ . '/includes/init.php';
$pdo = db();
$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: ' . SITE_URL . '/'); exit; }

$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug=? AND status=1");
$stmt->execute([$slug]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); include __DIR__.'/404.php'; exit; }

$page_seo['title']       = $p['meta_title'] ?: ($p['title'] . ' - ' . setting('site_name'));
$page_seo['description'] = $p['meta_description'] ?: niib_seo_clean_text($p['content']);
$page_seo['keywords']    = $p['meta_keywords'];
$page_seo['og_title']       = $p['meta_title'] ?: $p['title'];
$page_seo['og_description'] = $p['meta_description'] ?: $page_seo['description'];
if (!empty($p['canonical_url'])) $page_seo['canonical'] = $p['canonical_url'];
if (!empty($p['og_image']))      $page_seo['og_image']  = img_url($p['og_image']);
if (!empty($p['schema_code']))   $page_seo['schema']    = $p['schema_code'];

niib_seo_set_breadcrumbs([
    ['name' => 'Home', 'url' => SITE_URL . '/'],
    ['name' => $p['title']],
]);

niib_seo_add_schema([
    '@context'    => 'https://schema.org',
    '@type'       => 'WebPage',
    'name'        => $p['title'],
    'url'         => SITE_URL . '/' . rawurlencode($p['slug']),
    'description' => $page_seo['description'],
    'isPartOf'    => ['@id' => SITE_URL . '#website'],
    'inLanguage'  => 'en-IN',
]);

/* ---------------------------------------------------------------
 * Dispatch by page type
 * --------------------------------------------------------------- */
$page_type = $p['page_type'] ?? 'informative';

if ($page_type === 'location') {
    $td = !empty($p['template_data']) ? json_decode($p['template_data'], true) : [];
    // Floating Call + WhatsApp: use branch phone if available
    if (!empty($td['contact']['phone'])) {
        $page_whatsapp = $td['contact']['phone'];
        $page_phone    = $td['contact']['phone'];
        $topbar_phone  = $td['contact']['phone'];
    }
    // Topbar email: branch-specific address per page
    $__branchEmail = [
        'patna-east-region'           => 'niib.patna@newimageinstitute.com',
        'naraingarh-strategic-center' => 'niib.naraingarh@newimageinstitute.com',
        'lucknow-central-region'      => 'niib.lucknow@newimageinstitute.com',
    ];
    if (!empty($td['contact']['email'])) {
        $topbar_email = $td['contact']['email'];
    } elseif (isset($__branchEmail[$p['slug']])) {
        $topbar_email = $__branchEmail[$p['slug']];
    }
    // Fetch location social links
    $locStmt = $pdo->prepare("SELECT * FROM locations WHERE page_id=? LIMIT 1");
    $locStmt->execute([$p['id']]);
    $locRec = $locStmt->fetch();
    if ($locRec) {
        $locSocials = [];
        if (!empty($locRec['social_facebook']))  $locSocials[] = ['platform'=>'Facebook', 'url'=>$locRec['social_facebook'], 'icon'=>'bi-facebook'];
        if (!empty($locRec['social_instagram'])) $locSocials[] = ['platform'=>'Instagram', 'url'=>$locRec['social_instagram'], 'icon'=>'bi-instagram'];
        if (!empty($locRec['social_youtube']))   $locSocials[] = ['platform'=>'YouTube', 'url'=>$locRec['social_youtube'], 'icon'=>'bi-youtube'];
        if (!empty($locRec['social_twitter']))   $locSocials[] = ['platform'=>'Twitter', 'url'=>$locRec['social_twitter'], 'icon'=>'bi-twitter-x'];
        if (!empty($locSocials)) {
            $custom_topbar_socials = $locSocials;
        }
    }

    include __DIR__ . '/includes/header.php';
    include __DIR__ . '/includes/page-location.php';
    include __DIR__ . '/includes/footer.php';
    exit;
}

/* ----- Vision & Mission (custom designed layout) ----- */
if ($p['slug'] === 'vision-and-mission') {
    include __DIR__ . '/includes/header.php';
    include __DIR__ . '/includes/page-vision-mission.php';
    include __DIR__ . '/includes/footer.php';
    exit;
}

/* ----- Informative (default) ----- */
include 'includes/header.php';
?>

<section class="page-titlebar">
    <div class="container">
        <nav class="pt-crumbs">
            <a href="<?= SITE_URL ?>/">Home</a>
            <i class="bi bi-chevron-right"></i>
            <span><?= e($p['title']) ?></span>
        </nav>
        <h1 class="pt-title"><?= e($p['title']) ?></h1>
        <?php if (!empty($p['meta_description'])): ?>
            <p class="pt-lede"><?= e($p['meta_description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section>
    <div class="container" style="max-width:880px;">
        <article class="detail-content" style="font-size:var(--body-lg);color:var(--on-surface);line-height:1.8;">
            <?= $p['content'] ?>
        </article>
    </div>
</section>

<?php
// Render custom HTML blocks for this page
echo render_html_blocks($p['slug']);
?>

<?php include 'includes/footer.php'; ?>

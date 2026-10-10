<?php
require __DIR__ . '/includes/init.php';
$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug='about' AND status=1");
$stmt->execute();
$page = $stmt->fetch() ?: [
    'title' => 'About Us', 'content' => '', 'banner_image' => '',
    'meta_title' => 'About Us', 'meta_description' => '', 'meta_keywords' => '',
    'canonical_url' => '', 'schema_code' => '',
];

$page_seo['title'] = $page['meta_title'] ?: 'About NIIB Institute - Premier Beauty Academy';
$page_seo['description'] = $page['meta_description'] ?: 'Founded in 2004, NIIB Institute has grown into one of India\'s leading beauty education institutions with 35+ branch centres.';
$page_seo['keywords'] = $page['meta_keywords'];
if ($page['canonical_url']) $page_seo['canonical'] = $page['canonical_url'];
if ($page['schema_code']) $page_seo['schema'] = $page['schema_code'];

niib_seo_set_breadcrumbs([
    ['name' => 'Home',     'url' => SITE_URL . '/'],
    ['name' => 'About Us'],
]);

niib_seo_add_schema([
    '@context'    => 'https://schema.org',
    '@type'       => 'AboutPage',
    'name'        => 'About NIIB Institute',
    'url'         => SITE_URL . '/about',
    'description' => $page_seo['description'],
    'mainEntity'  => ['@id' => SITE_URL . '#organization'],
]);

// Directors / leadership (managed in Admin → Team)
$team = $pdo->query("SELECT * FROM team_members WHERE status=1 ORDER BY sort_order, id LIMIT 4")->fetchAll();

// Stats band (managed in Admin → Counters)
$counters = $pdo->query("SELECT * FROM counters WHERE status=1 ORDER BY sort_order, id LIMIT 4")->fetchAll();

// Mission & Vision (managed in Admin → HTML Blocks: about_mission / about_vision)
$blocks = [];
$bstmt = $pdo->prepare("SELECT block_key, title, html_content FROM html_blocks WHERE status=1 AND block_key IN ('about_mission','about_vision')");
$bstmt->execute();
foreach ($bstmt->fetchAll() as $b) $blocks[$b['block_key']] = $b;

// Render a plain-text bio (stored with blank lines) as clean paragraphs
function about_bio_paragraphs($bio) {
    $out = '';
    foreach (preg_split('/\n\s*\n/', trim((string)$bio)) as $para) {
        $para = trim($para);
        if ($para !== '') $out .= '<p>' . nl2br(e($para)) . '</p>';
    }
    return $out;
}

include 'includes/header.php';
?>

<style>
/* ---- About page (scoped) ---- */
.about-stats-band{ background: var(--primary); color:#fff; padding: 64px 0; }
.about-stats{ display:grid; grid-template-columns:1fr 1fr; gap: 32px var(--gutter); }
@media (min-width:768px){ .about-stats{ grid-template-columns: repeat(4,1fr); } }
.about-stats .stat .num i{ font-size:26px; color:var(--secondary-container); display:block; margin-bottom:12px; }

.mv-grid{ display:grid; grid-template-columns:1fr; gap: var(--gutter); }
@media (min-width:900px){ .mv-grid{ grid-template-columns:1fr 1fr; } }
.mv-card{
    background: var(--surface-container-low);
    border: 1px solid var(--outline-variant);
    border-top: 3px solid var(--secondary-container);
    border-radius: var(--radius-md);
    padding: 44px 40px;
}
.mv-card .mv-ico{
    width:64px; height:64px; border-radius:var(--radius-full);
    background: var(--primary); color: var(--secondary-fixed);
    display:flex; align-items:center; justify-content:center;
    font-size:28px; margin-bottom:24px;
}
.mv-card h3{ font-size: var(--headline-sm); margin:0 0 16px; }
.mv-card p{ color: var(--on-surface-variant); font-size: var(--body-md); line-height:1.75; margin:0 0 14px; }
.mv-card p:last-child{ margin-bottom:0; }

.about-leaders{ display:grid; grid-template-columns:1fr; gap:48px; }
.leader-card{
    display:grid; grid-template-columns:1fr; gap:0;
    background: var(--surface); border:1px solid var(--outline-variant);
    border-radius: var(--radius-md); overflow:hidden;
}
@media (min-width:768px){ .leader-card{ grid-template-columns: 320px 1fr; } }
.leader-card .lc-photo{ width:100%; height:100%; min-height:340px; object-fit:cover; object-position:top center; }
.leader-card .lc-body{ padding: 40px; }
.leader-card .lc-role{
    font-family:var(--font-body); font-size:12px; font-weight:700;
    letter-spacing:.22em; text-transform:uppercase; color:var(--secondary); margin-bottom:8px;
}
.leader-card .lc-name{ font-family:var(--font-headline); font-size: var(--headline-sm); margin:0 0 18px; color:var(--on-surface); }
.leader-card .lc-body p{ color:var(--on-surface-variant); font-size:15px; line-height:1.8; margin:0 0 14px; }
.leader-card .lc-body p:last-child{ margin-bottom:0; }
.leader-card .lc-socials{ margin-top:22px; display:flex; gap:12px; }
.leader-card .lc-socials a{ color:var(--primary); font-size:18px; }
.leader-card .lc-socials a:hover{ color:var(--secondary-container); }

.about-prose p{ color:var(--on-surface-variant); font-size:var(--body-md); line-height:1.8; margin:0 0 18px; }
</style>

<!-- Title Bar -->
<section class="page-titlebar">
    <div class="container">
        <nav class="pt-crumbs">
            <a href="<?= SITE_URL ?>/">Home</a>
            <i class="bi bi-chevron-right"></i>
            <span>About Us</span>
        </nav>
        <h1 class="pt-title"><?= e($page['title']) ?></h1>
        <?php if (!empty($page['meta_description'])): ?>
        <p class="pt-lede"><?= e($page['meta_description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Our Story -->
<section>
    <div class="container">
        <div class="split-grid">
            <div>
                <span class="section-eyebrow">Our Story</span>
                <h2>Here We Began:<br>Our Journey</h2>
                <div class="about-prose" style="margin-top:24px;">
                    <?= $page['content'] ?>
                </div>
            </div>
            <div class="image-frame">
                <img src="<?= e(img_url($page['banner_image'] ?: 'assets/images/design/about-journey.jpg')) ?>" alt="About NIIB Institute" style="height:560px;width:100%;object-fit:cover;border-radius:var(--radius-sm);">
            </div>
        </div>
    </div>
</section>

<!-- Stats Band -->
<?php if ($counters): ?>
<section class="about-stats-band">
    <div class="container">
        <div class="about-stats">
            <?php foreach ($counters as $cn): ?>
            <div class="stat">
                <span class="num">
                    <?php if (!empty($cn['icon'])): ?><i class="bi <?= e($cn['icon']) ?>"></i><?php endif; ?>
                    <span data-counter="<?= (int)$cn['value'] ?>">0</span><?= e($cn['suffix']) ?>
                </span>
                <span class="label"><?= e($cn['label']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Mission & Vision -->
<?php if (!empty($blocks['about_mission']) || !empty($blocks['about_vision'])): ?>
<section class="surface-bg">
    <div class="container">
        <div class="section-title">
            <span class="section-eyebrow">Our Purpose</span>
            <h2>Mission &amp; Vision</h2>
        </div>
        <div class="mv-grid">
            <?php if (!empty($blocks['about_mission'])): ?>
            <div class="mv-card">
                <div class="mv-ico"><i class="bi bi-bullseye"></i></div>
                <h3><?= e($blocks['about_mission']['title'] ?: 'Our Mission') ?></h3>
                <?= $blocks['about_mission']['html_content'] ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($blocks['about_vision'])): ?>
            <div class="mv-card">
                <div class="mv-ico"><i class="bi bi-eye-fill"></i></div>
                <h3><?= e($blocks['about_vision']['title'] ?: 'Our Vision') ?></h3>
                <?= $blocks['about_vision']['html_content'] ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Leadership -->
<?php if ($team): ?>
<section>
    <div class="container">
        <div class="section-title">
            <span class="section-eyebrow">Leadership</span>
            <h2>The Visionaries Behind NIIB</h2>
            <p style="color:var(--on-surface-variant);max-width:680px;margin:16px auto 0;">
                Guided by experienced leaders who have shaped beauty education in India for two decades.
            </p>
        </div>
        <div class="about-leaders">
            <?php foreach ($team as $tm): ?>
            <div class="leader-card">
                <img class="lc-photo" src="<?= e(img_url($tm['image'])) ?>" alt="<?= e($tm['name']) ?>">
                <div class="lc-body">
                    <?php if (!empty($tm['designation'])): ?><div class="lc-role"><?= e($tm['designation']) ?></div><?php endif; ?>
                    <h3 class="lc-name"><?= e($tm['name']) ?></h3>
                    <?= about_bio_paragraphs($tm['bio']) ?>
                    <?php if (!empty($tm['linkedin']) || !empty($tm['facebook']) || !empty($tm['instagram']) || !empty($tm['twitter'])): ?>
                    <div class="lc-socials">
                        <?php if (!empty($tm['linkedin'])): ?><a href="<?= e(niib_link($tm['linkedin'])) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a><?php endif; ?>
                        <?php if (!empty($tm['instagram'])): ?><a href="<?= e(niib_link($tm['instagram'])) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a><?php endif; ?>
                        <?php if (!empty($tm['facebook'])): ?><a href="<?= e(niib_link($tm['facebook'])) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a><?php endif; ?>
                        <?php if (!empty($tm['twitter'])): ?><a href="<?= e(niib_link($tm['twitter'])) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section>
    <div class="container surface-bg" style="background:var(--surface-container-low);padding:80px 40px;border:1px solid var(--outline-variant);text-align:center;">
        <h2 style="font-size:clamp(36px,4vw,56px);">Begin Your Professional Journey</h2>
        <p style="color:var(--on-surface-variant);max-width:520px;margin:0 auto 32px;">Take the first step toward an extraordinary career in beauty and aesthetics with India's trusted beauty education institution.</p>
        <a href="<?= SITE_URL ?>/contact" class="btn btn-cta btn-lg">Schedule a Visit</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

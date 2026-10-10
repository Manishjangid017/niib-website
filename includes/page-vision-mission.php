<?php
/**
 * Attractive template for the Vision & Mission page.
 * Text is parsed from pages.content (editable in Admin → Pages) by its <h2> sections,
 * so the copy stays fully manageable while the layout stays designed.
 */
if (!defined('NIIB_INIT')) { http_response_code(403); exit('Forbidden'); }

$content = $p['content'] ?? '';
$sections = [];
if (preg_match_all('/<h2[^>]*>(.*?)<\/h2>(.*?)(?=<h2|$)/is', $content, $mm, PREG_SET_ORDER)) {
    foreach ($mm as $seg) {
        $sections[] = ['head' => trim($seg[1]), 'body' => trim($seg[2])];
    }
}
$mission = $sections[0] ?? ['head' => 'Our Mission', 'body' => $content];
$vision  = $sections[1] ?? null;

$missionImg = !empty($p['banner_image']) ? $p['banner_image'] : 'assets/images/design/about-classroom.jpg';
$visionImg  = 'uploads/media/f_6a5f5f7955d7c_1784635257.jpeg';

$values = [
    ['icon' => 'bi-award-fill',            'title' => 'Excellence',   'desc' => 'Uncompromising standards across every program we teach.'],
    ['icon' => 'bi-lightbulb-fill',        'title' => 'Innovation',   'desc' => 'Modern, technology-driven and industry-focused training.'],
    ['icon' => 'bi-rocket-takeoff-fill',   'title' => 'Empowerment',  'desc' => 'Turning students into confident professionals & entrepreneurs.'],
    ['icon' => 'bi-shield-check',          'title' => 'Trust',        'desc' => 'A trusted name in beauty education since 2004.'],
];
?>

<style>
/* ---- Vision & Mission (scoped) ---- */
.vm-sec{ padding: 88px 0; }
.vm-split{ align-items:center; }
.vm-img{ position:relative; }
.vm-img img{ width:100%; height:520px; object-fit:cover; border-radius:var(--radius-sm); }
.vm-img.vm-img-full img{ height:auto; object-fit:initial; }
.vm-eyebrow-num{ font-family:var(--font-headline); font-size:64px; line-height:1; color:var(--secondary-container); opacity:.18; font-weight:700; display:block; margin-bottom:-18px; }
.vm-copy h2{ font-size: clamp(30px,4vw,50px); margin:8px 0 22px; line-height:1.08; }
.vm-body{ color:var(--on-surface-variant); font-size:var(--body-lg); line-height:1.85; }
.vm-body p{ margin:0 0 18px; }
.vm-body p:last-child{ margin-bottom:0; }
.vm-badge{
    position:absolute; left:24px; bottom:24px; right:24px;
    background:rgba(23,3,56,.86); backdrop-filter:blur(6px);
    color:#fff; padding:20px 24px; border-radius:var(--radius-sm);
    display:flex; align-items:center; gap:16px;
}
.vm-badge .b-num{ font-family:var(--font-headline); font-size:40px; line-height:1; color:var(--secondary-fixed); }
.vm-badge .b-txt{ font-size:13px; letter-spacing:.06em; color:rgba(255,255,255,.82); }

.vm-values{ padding:88px 0; }
.vm-values .section-title{ margin-bottom:56px; }
.vm-values-grid{ display:grid; grid-template-columns:1fr 1fr; gap:28px; }
@media (min-width:900px){ .vm-values-grid{ grid-template-columns:repeat(4,1fr); } }
.vm-value{ text-align:center; padding:16px; }
.vm-value .ico{ width:70px;height:70px;border-radius:var(--radius-full); background:rgba(254,94,30,.14); color:var(--secondary-container); display:flex;align-items:center;justify-content:center; font-size:30px; margin:0 auto 20px; }
.vm-value h4{ color:#fff; font-size:var(--headline-sm); margin:0 0 10px; }
.vm-value p{ color:rgba(255,255,255,.68); font-size:14px; line-height:1.6; margin:0; }

.vm-cta{ text-align:center; background:var(--surface-container-low); border:1px solid var(--outline-variant); padding:80px 40px; }
@media (max-width:767px){ .vm-sec{ padding:56px 0; } .vm-img img{ height:360px; } }
</style>

<!-- Hero -->
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

<!-- Mission -->
<section class="vm-sec">
    <div class="container">
        <div class="split-grid vm-split">
            <div class="vm-img vm-img-full">
                <img src="<?= e(img_url($missionImg)) ?>" alt="<?= e($mission['head']) ?> — NIIB Institute">
                <div class="vm-badge">
                    <span class="b-num">22+</span>
                    <span class="b-txt">Years empowering beauty<br>professionals across India</span>
                </div>
            </div>
            <div class="vm-copy">
                <span class="section-eyebrow">Our Purpose</span>
                <span class="vm-eyebrow-num">01</span>
                <h2><?= e($mission['head'] ?: 'Our Mission') ?></h2>
                <div class="vm-body"><?= $mission['body'] ?></div>
            </div>
        </div>
    </div>
</section>

<!-- Vision -->
<?php if ($vision): ?>
<section class="surface-bg vm-sec">
    <div class="container">
        <div class="split-grid vm-split">
            <div class="vm-copy">
                <span class="section-eyebrow">Looking Ahead</span>
                <span class="vm-eyebrow-num">02</span>
                <h2><?= e($vision['head'] ?: 'Our Vision') ?></h2>
                <div class="vm-body"><?= $vision['body'] ?></div>
            </div>
            <div class="vm-img">
                <img src="<?= e(img_url($visionImg)) ?>" alt="<?= e($vision['head']) ?> — NIIB Institute">
                <div class="vm-badge">
                    <span class="b-num">1000+</span>
                    <span class="b-txt">Lives transformed into<br>successful careers</span>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Values -->
<section class="dark-section vm-values">
    <div class="container">
        <div class="section-title">
            <span class="section-eyebrow" style="color:var(--secondary-fixed)">What Drives Us</span>
            <h2 style="color:#fff;">The Values Behind Every Student We Shape</h2>
        </div>
        <div class="vm-values-grid">
            <?php foreach ($values as $v): ?>
            <div class="vm-value">
                <div class="ico"><i class="bi <?= e($v['icon']) ?>"></i></div>
                <h4><?= e($v['title']) ?></h4>
                <p><?= e($v['desc']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section>
    <div class="container vm-cta">
        <h2 style="font-size:clamp(34px,4vw,54px);">Build a Career That Lasts a Lifetime</h2>
        <p style="color:var(--on-surface-variant);max-width:560px;margin:0 auto 32px;">Join thousands of successful graduates who started their journey with NIIB Institute.</p>
        <a href="<?= SITE_URL ?>/courses" class="btn btn-cta btn-lg" style="margin-right:12px;">Explore Courses</a>
        <a href="<?= SITE_URL ?>/contact" class="btn btn-primary btn-lg">Get in Touch</a>
    </div>
</section>

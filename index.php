<?php
require __DIR__ . '/includes/init.php';
$pdo = db();

$page_seo['title']       = seo('global_meta_title','NIIB Institute');
$page_seo['description'] = seo('global_meta_description','');
$page_seo['keywords']    = seo('global_meta_keywords','');
$page_seo['og_title']       = $page_seo['title'];
$page_seo['og_description'] = $page_seo['description'];

$sectionsRaw = $pdo->query("SELECT * FROM homepage_sections WHERE status=1 ORDER BY sort_order, id")->fetchAll();
$sections = [];
foreach ($sectionsRaw as $s) $sections[$s['section_key']] = $s;

$slide = $pdo->query("SELECT * FROM sliders WHERE status=1 ORDER BY sort_order LIMIT 1")->fetch();

// Homepage FAQ schema (if FAQ section enabled)
try {
    $homeFaqs = $pdo->query("SELECT question, answer FROM faq WHERE status=1 ORDER BY sort_order LIMIT 6")->fetchAll();
    if ($homeFaqs) niib_seo_add_schema(niib_seo_faq($homeFaqs));
} catch (Throwable $e) {}

include 'includes/header.php';
?>

<!-- ============ HERO ============ -->
<?php if ($slide): ?>
<section class="hero">
    <div class="hero-media">
        <img src="<?= e(img_url($slide['image'])) ?>" alt="<?= e($slide['title']) ?>">
    </div>
    <div class="container hero-grid">
        <div>
            <?php if ($slide['subtitle']): ?><span class="hero-eyebrow"><?= e($slide['subtitle']) ?></span><?php endif; ?>
            <h1 class="hero-title"><?= e($slide['title']) ?></h1>
            <p class="hero-sub"><?= e($slide['description']) ?></p>
            <div class="hero-actions">
                <?php if ($slide['button_text']): ?><a href="<?= e(niib_link($slide['button_url'])) ?>" class="btn btn-cta"><?= e($slide['button_text']) ?></a><?php endif; ?>
                <?php if ($slide['button_text_2']): ?><a href="<?= e(niib_link($slide['button_url_2'])) ?>" class="btn btn-ghost-light"><?= e($slide['button_text_2']) ?></a><?php endif; ?>
            </div>
        </div>

        <div class="d-none-mobile lead-form">
            <h2>Start Your Journey</h2>
                <p style="margin-bottom: 0px;">Request a free career consultation and course brochure today.</p>
            <!-- <form id="leadForm" class="">
                
                <div class="form-group">
                    <input type="text" name="name" placeholder="Full Name" aria-label="Full Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address" aria-label="Email Address" required>
                </div>
                <div class="form-group">
                    <input type="tel" name="phone" placeholder="Phone Number" aria-label="Phone Number" required>
                </div>
                <div class="form-group">
                    <input type="text" name="city" placeholder="City" aria-label="City" required>
                </div>
                <div class="form-group">
                    <select name="course_name" aria-label="Interested Category">
                        <option value="">Interested Category</option>
                        <?php
                        $catList = $pdo->query("SELECT name FROM course_categories WHERE status=1 ORDER BY sort_order")->fetchAll();
                        foreach($catList as $c): ?>
                            <option><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?= niib_recaptcha_widget() ?>
                <button type="submit">Submit Application</button>
                <div id="leadMsg" style="margin-top:10px;"></div>
            </form> -->
            <div id="crm-form-container-56"></div>
<script>
(function() {
    var container = document.getElementById('crm-form-container-56');
    if (container) {
        var baseUrl = 'http://crm.mediagarh.com/CRM/forms/public/56';
        var currentUrl = window.location.href;
        var iframeSrc = baseUrl + '?embed=1&landing_url=' + encodeURIComponent(currentUrl);
        
        var iframe = document.createElement('iframe');
        iframe.id = 'crm-form-56';
        iframe.src = iframeSrc;
        iframe.width = '100%';
        iframe.height = '560';
        iframe.frameBorder = '0';
        iframe.style.border = '0';
        iframe.style.background = 'transparent';
        container.appendChild(iframe);
    }
})();
</script>

        </div>
    </div>
</section>
<style>@media(max-width:1023px){.d-none-mobile{display:none}}</style>
<?php endif; ?>

<!-- ============ COURSES BENTO ============ -->
<?php if (!empty($sections['courses'])):
    $cs = $sections['courses'];
    $courses = $pdo->query("SELECT c.*, cat.name AS cat_name, cat.slug AS cat_slug FROM courses c LEFT JOIN course_categories cat ON cat.id = c.category_id WHERE c.status=1 ORDER BY c.is_featured DESC, c.id ASC LIMIT 5")->fetchAll();
?>
<section>
    <div class="container">
        <div class="section-title">
            <span class="section-eyebrow"><?= e($cs['subtitle']) ?></span>
            <h2><?= e($cs['title']) ?></h2>
        </div>

        <?php if (count($courses) >= 5): ?>
        <div class="bento-grid">
            <a class="bento-card featured" href="<?= SITE_URL ?>/course/<?= e($courses[0]['slug']) ?>">
                <img src="<?= e(img_url($courses[0]['image'])) ?>" alt="<?= e($courses[0]['title']) ?>">
                <div class="bento-content">
                    <span class="tag">Flagship Program</span>
                    <h3><?= e($courses[0]['title']) ?></h3>
                    <p><?= e(excerpt($courses[0]['short_description'],20)) ?></p>
                    <span class="view-link">View Curriculum</span>
                </div>
            </a>
            <a class="bento-card tall" href="<?= SITE_URL ?>/course/<?= e($courses[1]['slug']) ?>">
                <img src="<?= e(img_url($courses[1]['image'])) ?>" alt="<?= e($courses[1]['title']) ?>">
                <div class="bento-content">
                    <h3><?= e($courses[1]['title']) ?></h3>
                    <p><?= e(excerpt($courses[1]['short_description'],18)) ?></p>
                    <span class="view-link">View Curriculum</span>
                </div>
            </a>
            <?php for ($i = 2; $i < 5; $i++): ?>
            <a class="bento-card third" href="<?= SITE_URL ?>/course/<?= e($courses[$i]['slug']) ?>">
                <img src="<?= e(img_url($courses[$i]['image'])) ?>" alt="<?= e($courses[$i]['title']) ?>">
                <div class="bento-content">
                    <h3 style="font-size:24px;"><?= e($courses[$i]['title']) ?></h3>
                    <span class="view-link">View Course</span>
                </div>
            </a>
            <?php endfor; ?>
        </div>
        <?php else: ?>
        <div class="course-list-grid">
            <?php foreach ($courses as $c): ?>
            <div class="course-card">
                <img class="card-img" src="<?= e(img_url($c['image'])) ?>" alt="<?= e($c['title']) ?>">
                <div class="card-body">
                    <span class="cat"><?= e($c['cat_name']) ?></span>
                    <h5><a href="<?= SITE_URL ?>/course/<?= e($c['slug']) ?>"><?= e($c['title']) ?></a></h5>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($cs['button_text'])): ?>
        <div class="text-center" style="margin-top:60px;">
            <a href="<?= e(niib_link($cs['button_url'])) ?>" class="btn btn-primary"><?= e($cs['button_text']) ?></a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- ============ WHY CHOOSE / STATS ============ -->
<?php if (!empty($sections['why_choose'])):
    $w = $sections['why_choose'];
    $counters = $pdo->query("SELECT * FROM counters WHERE status=1 ORDER BY sort_order LIMIT 4")->fetchAll();
?>
<section class="dark-section">
    <div class="container split-grid">
        <div>
            <h2 style="font-style:italic;font-size:clamp(28px,4vw,48px)"><?= e($w['title']) ?></h2>
            <?= $w['content'] ?>
            <?php if ($counters): ?>
            <div class="stat-grid" style="margin-top:48px;">
                <?php foreach ($counters as $cn): ?>
                <div class="stat">
                    <span class="num"><span data-counter="<?= (int)$cn['value'] ?>">0</span><?= e($cn['suffix']) ?></span>
                    <span class="label"><?= e($cn['label']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($w['button_text'])): ?>
            <div style="margin-top:40px;"><a href="<?= e(niib_link($w['button_url'])) ?>" class="btn btn-cta"><?= e($w['button_text']) ?></a></div>
            <?php endif; ?>
        </div>
        <div class="image-frame">
            <img src="<?= e(img_url('uploads/media/f_6a5f6237b1966_1784635959.jpeg')) ?>" alt="NIIB Classroom">
            <div class="float-badge">
                <i class="bi bi-award-fill" style="font-size:32px;margin-bottom:12px;display:block"></i>
                <p class="badge-quote">"The gold standard in aesthetic education."</p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ PARTNERS ============ -->
<?php if (!empty($sections['partners'])): $p = $sections['partners']; ?>
<section class="partners-section">
    <div class="container">
        <p class="partners-eyebrow"><?= e($p['title']) ?></p>
        <div class="partner-grid">
            <div>L'ORÉAL</div>
            <div>MAC</div>
            <div>NATURALS</div>
            <div>LAKMÉ</div>
            <div>TONI &amp; GUY</div>
            <div>VLCC</div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ LOCATION SHOWCASE SECTIONS ============ -->
<?php include 'includes/home-showcase.php'; ?>

<!-- ============ FINAL CTA ============ -->
<?php if (!empty($sections['cta'])): $c = $sections['cta']; ?>
<section class="cta-section">
    <img class="cta-bg" src="<?= e(img_url('assets/images/design/cta-campus.jpg')) ?>" alt="">
    <div class="container cta-content">
        <h2><?= e($c['title']) ?></h2>
        <p><?= e($c['content'] ? strip_tags($c['content']) : 'Admissions are now open. Secure your place in the next generation of beauty masters.') ?></p>
        <div class="cta-actions">
            <?php if ($c['button_text']): ?><a href="<?= e(niib_link($c['button_url'])) ?>" class="btn btn-cta btn-lg"><?= e($c['button_text']) ?></a><?php endif; ?>
            <a href="<?= SITE_URL ?>/uploads/media/f_6a68699522f4c_1785227669.pdf" target="_blank" rel="noopener" class="btn btn-ghost-light btn-lg">Download Prospectus</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php 
// Render custom HTML blocks for homepage
echo render_html_blocks('home');
?>

<!-- ============ FAQ ============ -->
<?php if (!empty($sections['faq'])):
    $f = $sections['faq'];
    $faqs = $pdo->query("SELECT * FROM faq WHERE status=1 ORDER BY sort_order LIMIT 6")->fetchAll();
?>
<section class="surface-bg">
<div class="container">
    <div class="section-title">
        <span class="section-eyebrow"><?= e($f['subtitle']) ?></span>
        <h2><?= e($f['title']) ?></h2>
    </div>
    <div class="accordion">
        <?php foreach ($faqs as $i => $q): ?>
        <div class="accordion-item <?= $i===0?'open':'' ?>">
            <button type="button"><?= e($q['question']) ?></button>
            <div class="accordion-body"><?= nl2br(e($q['answer'])) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

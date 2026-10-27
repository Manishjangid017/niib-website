<?php
require __DIR__ . '/includes/init.php';
$pdo = db();
$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: courses.php'); exit; }

$stmt = $pdo->prepare("SELECT c.*, cat.name AS cat_name, cat.slug AS cat_slug FROM courses c LEFT JOIN course_categories cat ON cat.id=c.category_id WHERE c.slug=? AND c.status=1");
$stmt->execute([$slug]);
$c = $stmt->fetch();
if (!$c) { http_response_code(404); include __DIR__.'/404.php'; exit; }

$page_seo['title']       = $c['meta_title'] ?: ($c['title'] . ' - ' . setting('site_name'));
$page_seo['description'] = $c['meta_description'] ?: niib_seo_clean_text($c['short_description']);
$page_seo['keywords']    = $c['meta_keywords'];
if ($c['canonical_url']) $page_seo['canonical'] = $c['canonical_url'];
if ($c['og_image']) $page_seo['og_image']  = img_url($c['og_image']);
elseif ($c['image']) $page_seo['og_image']  = img_url($c['image']);

niib_seo_set_breadcrumbs([
    ['name' => 'Home',    'url' => SITE_URL . '/'],
    ['name' => 'Courses', 'url' => SITE_URL . '/courses'],
    ['name' => $c['cat_name'] ?: 'Programs', 'url' => $c['cat_slug'] ? SITE_URL . '/courses?cat=' . $c['cat_slug'] : null],
    ['name' => $c['title']],
]);

if ($c['schema_code']) {
    $page_seo['schema'] = $c['schema_code'];
} else {
    niib_seo_add_schema(niib_seo_course($c, $c['cat_name'] ?? ''));
}

// Fetch related from same category; top up from other courses if needed for the carousel
$related = $pdo->prepare("SELECT * FROM courses WHERE category_id=? AND id<>? AND status=1 ORDER BY RAND() LIMIT 8");
$related->execute([$c['category_id'], $c['id']]);
$related = $related->fetchAll();

if (count($related) < 4) {
    $exclude = array_merge([$c['id']], array_column($related, 'id'));
    $place   = implode(',', array_fill(0, count($exclude), '?'));
    $extraNeeded = 8 - count($related);
    $extra = $pdo->prepare("SELECT * FROM courses WHERE status=1 AND id NOT IN ($place) ORDER BY RAND() LIMIT $extraNeeded");
    $extra->execute($exclude);
    $related = array_merge($related, $extra->fetchAll());
}

include 'includes/header.php';
?>

<style>
/* ============================================================
   Course Details — wider, tight, symmetrical layout
   ============================================================ */
.course-details-page .container { max-width: 1440px; }

/* Drop the global section{padding:120px 0} on course pages — sections set their own */
.course-details-page > section { padding: 0; }
.course-details-page .page-banner { padding-top: 110px !important; padding-bottom: 80px !important; }
.course-details-page .page-banner h1 { font-size: clamp(36px, 4.4vw, 56px); line-height: 1.1; }
.course-details-page .page-banner-grid { gap: 56px; align-items: center; }
.course-details-page .page-banner-img img { border-radius: 14px; box-shadow: 0 24px 60px rgba(0,0,0,.35); }

.course-details-page .cd-body { padding-top: 56px !important; padding-bottom: 56px !important; background: var(--surface-container-low, #f6f3f2); }
.course-details-page .dark-section { padding: 80px 0 !important; }
.course-details-page .course-detail-grid { gap: 40px; }
@media (min-width: 1100px){
    .course-details-page .course-detail-grid { grid-template-columns: minmax(0,1fr) 360px; gap: 48px; align-items: flex-start; }
}

/* Content card: gives the long imported HTML a unified white surface */
.course-details-page .detail-content {
    background: #fff;
    padding: clamp(24px, 4vw, 48px);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(23,3,56,.06);
    min-width: 0;
}
.course-details-page .detail-content > h2:first-of-type { margin-top: 0; }
.course-details-page .detail-content .section-eyebrow { color: var(--secondary, #ab3600); }

/* Imported sections: tight rhythm — kill the inline margin:48px from the importer */
.course-details-page .detail-content > section { margin: 0 0 20px !important; padding-top: 16px !important; }
.course-details-page .detail-content > section:first-of-type { padding-top: 8px !important; }
.course-details-page .detail-content > section:last-of-type { margin-bottom: 0 !important; }
/* Faint divider between sections (skip the gradient CTA section) */
.course-details-page .detail-content > section + section:not([style*="linear-gradient"]) {
    border-top: 1px solid rgba(203,196,208,.5);
}

/* Section eyebrows are smaller spans — pull them tight to the heading */
.course-details-page .detail-content section > span:first-child { margin-bottom: 4px !important; }

/* Imported H3: align with the page's serif headline + tight bottom margin */
.course-details-page .detail-content section h3 {
    font-family: var(--font-headline, 'Bodoni Moda', Georgia, serif) !important;
    font-size: clamp(22px, 2.4vw, 30px) !important;
    margin: 0 0 10px !important;
}

/* Inner grids / lists / paragraphs — kill stray top space */
.course-details-page .detail-content section > div[style*="margin-top"] { margin-top: 4px !important; }
.course-details-page .detail-content section > ul { margin-top: 4px !important; margin-bottom: 0 !important; }
.course-details-page .detail-content section > p { margin-bottom: 8px !important; }
.course-details-page .detail-content section > p:last-child { margin-bottom: 0 !important; }
.course-details-page .detail-content section > div:last-child { margin-bottom: 0 !important; }

/* Meta strip (first imported block) — tighter own margins */
.course-details-page .detail-content > div[style*="grid-template-columns:repeat(auto-fit"] { margin: 0 0 16px !important; }

/* Closing CTA banner (gradient) — flush with section above */
.course-details-page .detail-content section[style*="linear-gradient"] {
    padding: 28px !important;
    margin: 24px 0 0 !important;
    border-top: 0 !important;
}

/* Eligibility + related carousel */
.course-details-page .detail-content > h3 { margin-top: 24px !important; margin-bottom: 8px !important; }
.course-details-page .related-carousel { margin-top: 40px; padding-top: 28px; border-top: 1px solid var(--outline-variant); }

/* Sticky sidebar */
.course-details-page .detail-sidebar {
    background: #fff;
    border: 1px solid var(--outline-variant);
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 8px 32px rgba(23,3,56,.06);
    top: 100px;
}
.course-details-page .detail-sidebar .price { margin-bottom: 14px; }
.course-details-page .detail-list li { padding: 12px 0; font-size: 14px; }
.course-details-page .detail-list .lbl { color: var(--on-surface-variant); font-weight: 600; letter-spacing: .04em; text-transform: uppercase; font-size: 12px; }
.course-details-page .detail-list .val { font-weight: 600; color: var(--primary); text-align: right; max-width: 60%; }

/* Mobile */
@media (max-width: 768px){
    .course-details-page .detail-content { padding: 20px 18px; border-radius: 10px; }
    .course-details-page .detail-content > section { margin: 0 0 16px !important; padding-top: 14px !important; }
}

/* ============================================================
   Course Modules — native <details> accordion
   ============================================================ */
.course-details-page .cm-item { transition: box-shadow .2s ease, border-color .2s ease; }
.course-details-page .cm-item:hover { box-shadow: 0 6px 18px rgba(23,3,56,.08); border-color: #b9b0c2 !important; }
.course-details-page .cm-item[open] { box-shadow: 0 8px 22px rgba(23,3,56,.08); border-color: #b9b0c2 !important; }
.course-details-page .cm-sum { list-style: none !important; }
.course-details-page .cm-sum::-webkit-details-marker { display: none; }
.course-details-page .cm-sum::marker { display: none; content: ''; }
.course-details-page .cm-item[open] .cm-chev { transform: rotate(45deg); background: #ffdbcf !important; }
.course-details-page .cm-item:hover .cm-chev { background: #ffdbcf !important; }
</style>

<div class="course-details-page">

<!-- Hero -->
<section class="page-banner dark-section">
    <div class="container page-banner-grid">
        <div>
            <div class="crumbs" style="color:var(--secondary-fixed);"><a style="color:var(--secondary-fixed);" href="<?= SITE_URL ?>/courses">Courses</a> / <?= e($c['cat_name'] ?: 'Programs') ?></div>
            <h1 style="color:#fff;"><?= e($c['title']) ?></h1>
            <p style="font-size:var(--body-lg);color:rgba(255,255,255,.85);margin-top:24px;max-width:560px;"><?= e($c['short_description']) ?></p>
            <div style="display:flex;gap:16px;margin-top:32px;flex-wrap:wrap;">
                <a href="#enquire" class="btn btn-cta">Enquire Now</a>
                <?php if ($c['brochure']): ?>
                <a href="<?= e(img_url($c['brochure'])) ?>" class="btn btn-ghost-light" target="_blank">Download Brochure</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="page-banner-img">
            <img src="<?= e(img_url($c['image'])) ?>" alt="<?= e($c['title']) ?>">
        </div>
    </div>
</section>

<!-- Course Details -->
<section class="cd-body">
<div class="container">
<div class="course-detail-grid">
    <div class="detail-content">
        <span class="section-eyebrow">Curriculum Overview</span>
        <h2 style="font-size:clamp(28px,3vw,40px);margin:8px 0 24px;">Transforming Passion Into Professional Mastery.</h2>
        <?= str_replace(['<h2', '</h2>'], ['<h3', '</h3>'], $c['full_description']) ?>

        <?php if ($c['eligibility']): ?>
        <h3 style="margin-top:48px;">Eligibility</h3>
        <p><?= nl2br(e($c['eligibility'])) ?></p>
        <?php endif; ?>

        <?php if ($related): ?>
        <div class="related-carousel" data-carousel>
            <div class="related-head">
                <h2>Related Programs</h2>
                <div class="related-controls">
                    <button type="button" class="related-arrow" data-dir="prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" class="related-arrow" data-dir="next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
            <div class="related-track" data-track>
                <?php foreach ($related as $r): ?>
                <article class="related-card">
                    <a href="<?= SITE_URL ?>/course/<?= e($r['slug']) ?>" class="related-img">
                        <img src="<?= e(img_url($r['image'])) ?>" alt="<?= e($r['title']) ?>" loading="lazy">
                    </a>
                    <div class="related-body">
                        <span class="related-cat"><?= e($r['level']) ?: 'Program' ?></span>
                        <h3><a href="<?= SITE_URL ?>/course/<?= e($r['slug']) ?>"><?= e($r['title']) ?></a></h3>
                        <p class="related-excerpt"><?= e(excerpt($r['short_description'], 14)) ?></p>
                        <div class="related-foot">
                            <div class="related-price">
                                <?php if ($r['original_fees']): ?><span class="old"><?= e($r['original_fees']) ?></span><?php endif; ?>
                                <?= e($r['fees']) ?>
                            </div>
                            <a href="<?= SITE_URL ?>/course/<?= e($r['slug']) ?>" class="related-link">View <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <div class="related-dots" data-dots></div>
        </div>
        <?php endif; ?>
    </div>

    <aside>
        <div class="detail-sidebar">
            <div class="price">
                <?php if($c['original_fees']): ?><span class="old"><?= e($c['original_fees']) ?></span><?php endif; ?>
                <?= e($c['fees']) ?>
            </div>
            <ul class="detail-list">
                <li><span class="lbl">Duration</span><span class="val"><?= e($c['duration']) ?></span></li>
                <li><span class="lbl">Level</span><span class="val"><?= e($c['level']) ?></span></li>
                <li><span class="lbl">Language</span><span class="val"><?= e($c['language']) ?></span></li>
                <?php if ($c['students_count']): ?><li><span class="lbl">Students</span><span class="val"><?= (int)$c['students_count'] ?>+</span></li><?php endif; ?>
                <?php if ($c['cat_name']): ?><li><span class="lbl">Category</span><span class="val"><?= e($c['cat_name']) ?></span></li><?php endif; ?>
            </ul>

            <div id="enquire" style="margin-top:24px;padding-top:24px;border-top:1px solid var(--outline-variant);">
                <p style="font-family:var(--font-headline);font-size:var(--headline-sm);color:var(--primary);">Have Questions? Get in touch!</p>
                <!-- <form id="enquiryForm" style="margin-top:16px;">
                    <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>">
                    <input type="hidden" name="course_name" value="<?= e($c['title']) ?>">
                    <div class="form-group" style="margin-bottom:12px;"><input class="form-control" name="name" placeholder="Your Name" required style="padding:12px;border:1px solid var(--outline-variant);background:#fff;font-family:inherit;width:100%;"></div>
                    <div class="form-group" style="margin-bottom:12px;"><input type="email" class="form-control" name="email" placeholder="Email" required style="padding:12px;border:1px solid var(--outline-variant);background:#fff;font-family:inherit;width:100%;"></div>
                    <div class="form-group" style="margin-bottom:12px;"><input class="form-control" name="phone" placeholder="Phone" style="padding:12px;border:1px solid var(--outline-variant);background:#fff;font-family:inherit;width:100%;"></div>
                    <div class="form-group" style="margin-bottom:12px;"><input class="form-control" name="city" placeholder="City" style="padding:12px;border:1px solid var(--outline-variant);background:#fff;font-family:inherit;width:100%;"></div>
                    <div class="form-group" style="margin-bottom:12px;"><textarea class="form-control" name="message" rows="3" placeholder="Message" style="padding:12px;border:1px solid var(--outline-variant);background:#fff;font-family:inherit;width:100%;"></textarea></div>
                    <?= niib_recaptcha_widget() ?>
                    <button class="btn btn-cta" style="width:100%;">Submit Enquiry</button>
                    <div id="enquiryMsg" style="margin-top:10px;font-size:14px;"></div>
                </form> -->

                <div id="crm-form-container-56"></div>
<script>
(function() {
    var cid = 'crm-form-container-56';
    function load() {
        var c = document.getElementById(cid);
        if (!c || c.dataset.loaded) return;
        c.dataset.loaded = '1';
        var iframe = document.createElement('iframe');
        iframe.id = 'crm-form-56';
        iframe.src = 'https://crm.mediagarh.com/CRM/forms/public/56?embed=1&landing_url=' + encodeURIComponent(window.location.href);
        iframe.width = '100%'; iframe.height = '560'; iframe.frameBorder = '0';
        iframe.style.border = '0'; iframe.style.background = 'transparent';
        c.appendChild(iframe);
    }
    if ('IntersectionObserver' in window) {
        var el = document.getElementById(cid);
        if (el) { var io = new IntersectionObserver(function(e){ if(e[0].isIntersecting){io.disconnect();load();} },{rootMargin:'300px'}); io.observe(el); }
    } else { load(); }
})();
</script>

            </div>
        </div>
    </aside>
</div>
</div>
</section>

<!-- legacy industry-recognition block removed -->

</div><!-- /.course-details-page -->

<script>
/* Course Modules: now uses native <details>/<summary> elements — no JS needed.
   Older imported content (pre-accordion) gets a fallback enhancement below. */
(function(){
    var root = document.querySelector('.course-details-page .detail-content');
    if (!root) return;
    // Legacy fallback: if content still has the old div+ul module pattern, wrap into <details>
    root.querySelectorAll('div').forEach(function(d){
        var head = null, body = null;
        for (var i = 0; i < d.children.length; i++) {
            var ch = d.children[i];
            if (!head && ch.tagName === 'DIV') head = ch;
            else if (!body && ch.tagName === 'UL') body = ch;
        }
        if (!head || !body) return;
        var badge = head.querySelector('span');
        if (!badge || !/^\s*\d+\s*$/.test(badge.textContent)) return;
        // Build a <details>/<summary> wrapper around the existing nodes
        var det = document.createElement('details');
        det.className = 'cm-item';
        det.style.cssText = 'background:#fff;border:1px solid #cbc4d0;border-radius:10px;overflow:hidden';
        var sum = document.createElement('summary');
        sum.className = 'cm-sum';
        sum.style.cssText = 'display:flex;align-items:center;gap:14px;padding:18px 20px;cursor:pointer;list-style:none';
        // move header children into summary
        while (head.firstChild) sum.appendChild(head.firstChild);
        var chev = document.createElement('span');
        chev.className = 'cm-chev';
        chev.style.cssText = 'margin-left:auto;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;background:#fcf9f8;color:#fe5e1e;border-radius:50%;font-size:18px;font-weight:300;transition:transform .25s';
        chev.textContent = '+';
        sum.appendChild(chev);
        // restyle the ul for indented body
        body.style.padding = '0 20px 18px 84px';
        body.style.margin = '0';
        det.appendChild(sum);
        det.appendChild(body);
        // replace the original card with the details element
        d.parentNode.replaceChild(det, d);
        // open first one
        if (!d.parentNode || d.parentNode.querySelector('details:first-of-type') === det) det.open = true;
    });
})();
</script>

<?php include 'includes/footer.php'; ?>

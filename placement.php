<?php
require __DIR__ . '/includes/init.php';
$pdo = db();

$page_seo['title']       = 'Student Success & Placement - ' . setting('site_name');
$page_seo['description'] = 'Discover how NIIB graduates are leading the global beauty industry. Placement stories, partner brands and career outcomes.';
$page_seo['keywords']    = 'NIIB placements, beauty industry careers, makeup artist placement, NIIB success stories';

niib_seo_set_breadcrumbs([
    ['name' => 'Home',            'url' => SITE_URL . '/'],
    ['name' => 'Success Stories'],
]);

niib_seo_add_schema([
    '@context'    => 'https://schema.org',
    '@type'       => 'CollectionPage',
    'name'        => 'Student Success & Placement',
    'url'         => SITE_URL . '/placement',
    'description' => $page_seo['description'],
    'isPartOf'    => ['@id' => SITE_URL . '#website'],
]);

$counters = $pdo->query("SELECT * FROM counters WHERE status=1 ORDER BY sort_order LIMIT 3")->fetchAll();

include 'includes/header.php';
?>

<!-- Hero -->
<section class="dark-section" style="padding-top:120px;padding-bottom:80px;">
    <div class="container">
        <div class="page-banner-grid">
            <div>
                <div class="crumbs" style="color:var(--secondary-fixed);">Alumni Outcomes</div>
                <h1 style="color:#fff;">Your Future,<br><em style="color:var(--primary-fixed-dim);font-style:italic">Perfectly Crafted.</em></h1>
                <p style="font-size:var(--body-lg);color:rgba(255,255,255,.85);max-width:540px;margin:24px 0 0;">Join the 50,000+ alumni who have transformed their passion into prestigious careers across the world's most influential beauty brands.</p>

                <div class="stat-grid" style="margin-top:48px;max-width:520px;">
                    <?php foreach ($counters as $cn): ?>
                    <div class="stat">
                        <span class="num"><span data-counter="<?= (int)$cn['value'] ?>">0</span><?= e($cn['suffix']) ?></span>
                        <span class="label"><?= e($cn['label']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="page-banner-img" style="position:relative;height:520px;">
                <img src="<?= e(img_url('assets/images/design/placement-hero.jpg')) ?>" alt="NIIB graduate" style="height:100%;width:100%;object-fit:cover;border-radius:var(--radius-sm);">
                <div style="position:absolute;bottom:24px;left:24px;right:24px;background:var(--secondary-container);padding:24px;color:#fff;border-radius:var(--radius-sm);">
                    <p style="margin:0;font-family:var(--font-headline);font-size:var(--headline-sm);font-style:italic;color:#fff;">"The gold standard in clinical education."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Alumni credibility sections retired; success showcase added below -->

<!-- Where will your path lead -->
<section>
<div class="container">
    <div class="section-title">
        <span class="section-eyebrow">Career Outcomes</span>
        <h2>Where will your path lead?</h2>
    </div>
    <div class="career-path">
        <div class="career-card">
            <span class="section-eyebrow">Clinical</span>
            <h3>Clinical Aesthetics</h3>
            <p style="color:var(--on-surface-variant);">A high-demand path inside dermatology clinics, medi-spas and pharma research labs.</p>
            <ul style="list-style:none;padding:0;margin-top:24px;font-size:var(--body-md);color:var(--primary);">
                <li style="padding:8px 0;border-bottom:1px dashed var(--outline-variant);">▸ Medical Aesthetician</li>
                <li style="padding:8px 0;border-bottom:1px dashed var(--outline-variant);">▸ Skin Therapist</li>
                <li style="padding:8px 0;">▸ Dermatology Assistant</li>
            </ul>
        </div>
        <div class="career-card dark">
            <span class="section-eyebrow" style="color:var(--secondary-fixed)">Editorial</span>
            <h3>Creative Artistry</h3>
            <p style="color:rgba(255,255,255,.8);">Build your portfolio across runways, magazine spreads, film sets and high-end bridal.</p>
            <ul style="list-style:none;padding:0;margin-top:24px;font-size:var(--body-md);color:#fff;">
                <li style="padding:8px 0;border-bottom:1px dashed rgba(255,255,255,.15);">▸ Editorial Makeup Artist</li>
                <li style="padding:8px 0;border-bottom:1px dashed rgba(255,255,255,.15);">▸ Couture Hair Stylist</li>
                <li style="padding:8px 0;">▸ Fashion Week Specialist</li>
            </ul>
        </div>
        <div class="career-card orange">
            <h3 style="color:#fff;">Salon Management</h3>
            <p style="color:rgba(255,255,255,.9);">From boutique studio owner to multi-city chain operator. Build your brand on solid business foundations.</p>
            <ul style="list-style:none;padding:0;margin-top:24px;font-size:var(--body-md);color:#fff;">
                <li style="padding:8px 0;border-bottom:1px dashed rgba(255,255,255,.15);">▸ Salon Owner / Director</li>
                <li style="padding:8px 0;border-bottom:1px dashed rgba(255,255,255,.15);">▸ Franchise Partner</li>
                <li style="padding:8px 0;">▸ Beauty Brand Founder</li>
            </ul>
        </div>
        <div class="career-card">
            <span class="section-eyebrow">Wellness</span>
            <h3>Global Wellness &amp; Spa</h3>
            <p style="color:var(--on-surface-variant);">5-star hotels, destination resorts and luxury cruise lines actively recruit our wellness graduates.</p>
            <ul style="list-style:none;padding:0;margin-top:24px;font-size:var(--body-md);color:var(--primary);">
                <li style="padding:8px 0;border-bottom:1px dashed var(--outline-variant);">▸ Spa Therapist</li>
                <li style="padding:8px 0;border-bottom:1px dashed var(--outline-variant);">▸ Wellness Director</li>
                <li style="padding:8px 0;">▸ Resort Beauty Manager</li>
            </ul>
        </div>
    </div>
</div>
</section>

<!-- Success showcase: Stories of Transformation, Student Testimonials, Video Testimonials, Accreditations -->
<?php $showcase_sections = ['stories','student','video','accreditations']; include 'includes/home-showcase.php'; ?>

<!-- CTA -->
<section>
<div class="container surface-bg" style="background:var(--surface-container-low);padding:80px 40px;text-align:center;border:1px solid var(--outline-variant);">
    <h2 style="font-size:clamp(36px,4vw,56px);margin-bottom:24px;">Start Your Own Success Story.</h2>
    <p style="color:var(--on-surface-variant);max-width:620px;margin:0 auto 40px;font-size:var(--body-lg);">Our admissions for the 2026 academic year are now open. Secure your seat in the most prestigious beauty institute in India.</p>
    <div class="placement-cta-row">
        <a href="<?= SITE_URL ?>/uploads/media/f_6a68699522f4c_1785227669.pdf" target="_blank" rel="noopener" class="btn btn-cta btn-lg">Download Prospectus</a>
        <a href="<?= SITE_URL ?>/contact" class="btn btn-ghost btn-lg">Speak to a Career Advisor</a>
    </div>
</div>
</section>

<?php include 'includes/footer.php'; ?>

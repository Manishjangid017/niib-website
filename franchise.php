<?php
require __DIR__ . '/includes/init.php';

$page_seo['title']       = 'Own a NIIB Institute Franchise | ' . setting('site_name');
$page_seo['description'] = 'Build a profitable education business and transform careers in the booming beauty & wellness sector. Partner with India\'s leading beauty academy.';
$page_seo['keywords']    = 'NIIB franchise, beauty institute franchise India, education franchise, beauty academy partnership';

$franchisePhone = '+91 78891-11907';
$franchiseEmail = 'headexpansion@newimageinstitute.com';

// Override the site's floating WhatsApp + Call buttons for THIS page only (does not change global settings)
$page_whatsapp = $franchisePhone;
$page_phone    = $franchisePhone;

// Show the Franchise Inquiry variant in the site-wide enquiry popup on this page only.
$popup_franchise_form = true;

niib_seo_set_breadcrumbs([
    ['name' => 'Home',      'url' => SITE_URL . '/'],
    ['name' => 'Franchise'],
]);

// FranchiseOpportunity / Service schema
niib_seo_add_schema([
    '@context'    => 'https://schema.org',
    '@type'       => 'Service',
    'serviceType' => 'Franchise Opportunity',
    'name'        => 'NIIB Institute Franchise',
    'provider'    => ['@id' => SITE_URL . '#organization'],
    'description' => $page_seo['description'],
    'url'         => SITE_URL . '/franchise',
    'areaServed'  => ['@type' => 'Country', 'name' => 'India'],
    'offers'      => [
        '@type'         => 'Offer',
        'priceCurrency' => 'INR',
        'price'         => '3000000',
        'availability'  => 'https://schema.org/InStock',
        'description'   => 'Minimum franchise investment ₹30 Lakhs+',
    ],
]);

include 'includes/header.php';
?>

<!-- =========== HERO =========== -->
<section class="fr-hero">
    <div class="fr-hero-bg">
        <img src="<?= e(img_url('assets/images/design/cta-campus.jpg')) ?>" alt="NIIB Institute Reception">
        <div class="fr-hero-gradient"></div>
    </div>
    <div class="container fr-hero-inner">
        <span class="fr-hero-pill">Premier Partnership Opportunity</span>
        <h1>Own a NIIB Institute<br>Franchise</h1>
        <p>Build a Profitable Education Business &amp; Transform Careers in the booming beauty and wellness sector.</p>
        <div class="fr-hero-actions">
            <a href="<?= SITE_URL ?>/uploads/media/f_6a5f512650773_1784631590.pdf" target="_blank" rel="noopener" class="btn btn-cta btn-lg fr-btn-icon"><i class="bi bi-file-earmark-text-fill"></i> Franchise Proposal</a>
            <a href="tel:<?= e(preg_replace('/[^0-9+]/','',$franchisePhone)) ?>" class="btn btn-ghost-light btn-lg fr-btn-icon"><i class="bi bi-telephone-fill"></i> <?= e($franchisePhone) ?></a>
        </div>
    </div>
</section>

<!-- =========== STAT BAR =========== -->
<div class="fr-stat-bar-wrap">
<div class="container">
    <div class="fr-stat-bar">
        <div class="fr-stat">
            <span class="num" data-counter="22">0</span><span class="suffix">+</span>
            <span class="label">Years of Legacy</span>
        </div>
        <div class="fr-stat">
            <span class="num" data-counter="30">0</span><span class="suffix">+</span>
            <span class="label">Academies</span>
        </div>
        <div class="fr-stat">
            <span class="num" data-counter="15">0</span><span class="suffix">k+</span>
            <span class="label">Students</span>
        </div>
        <div class="fr-stat">
            <span class="num" data-counter="98">0</span><span class="suffix">%</span>
            <span class="label">Success Rate</span>
        </div>
    </div>
</div>
</div>

<!-- =========== ABOUT NIIB =========== -->
<section class="fr-about">
<div class="container">
    <div class="fr-about-grid">
        <div class="fr-about-img-wrap">
            <div class="fr-blur-blob"></div>
            <img src="<?= e(img_url('uploads/media/f_6a5f57dd43282_1784633309.jpeg')) ?>" alt="NIIB Institute Training">
            <div class="fr-img-accent"></div>
        </div>
        <div class="fr-about-text">
            <span class="section-eyebrow">Our Heritage</span>
            <h2>About NIIB Institute</h2>
            <p>NIIB Institute is a professional beauty, wellness, and skill-development training organization committed to delivering industry-oriented education. Established with the vision of creating skilled professionals, NIIB Institute offers structured programs designed to meet current industry demands.</p>
            <div class="fr-check-grid">
                <div class="fr-check"><i class="bi bi-check-circle-fill"></i> Hair styling &amp; technology</div>
                <div class="fr-check"><i class="bi bi-check-circle-fill"></i> Beauty &amp; skincare</div>
                <div class="fr-check"><i class="bi bi-check-circle-fill"></i> Professional makeup</div>
                <div class="fr-check"><i class="bi bi-check-circle-fill"></i> Salon &amp; spa management</div>
            </div>
            <a href="<?= SITE_URL ?>/about" class="btn btn-primary fr-learn">Learn More</a>
        </div>
    </div>
</div>
</section>

<!-- =========== WHY CHOOSE (BENTO) =========== -->
<section class="fr-why surface-bg">
<div class="container">
    <div class="section-title">
        <span class="section-eyebrow">The Advantage</span>
        <h2>Why Choose a NIIB Institute Franchise?</h2>
    </div>
    <div class="fr-bento">
        <div class="fr-bento-card fr-bento-1 fr-bento-light">
            <i class="bi bi-graph-up-arrow fr-bento-icon"></i>
            <h3>High-Demand Industry</h3>
            <p>The beauty and wellness sector is witnessing rapid growth, increasing the need for trained professionals.</p>
        </div>
        <div class="fr-bento-card fr-bento-2 fr-bento-dark">
            <i class="bi bi-currency-exchange fr-bento-icon"></i>
            <h3>Attractive Returns</h3>
            <p>NIIB Institute's structured franchise model is designed for scalability and high-profit margins.</p>
        </div>
        <div class="fr-bento-card fr-bento-3 fr-bento-light">
            <i class="bi bi-shield-check fr-bento-icon"></i>
            <h3>Trusted Brand</h3>
            <p>Benefit from global brand credibility.</p>
        </div>
        <div class="fr-bento-card fr-bento-4 fr-bento-orange">
            <i class="bi bi-mortarboard-fill fr-bento-icon"></i>
            <h3>Industry-Relevant Curriculum</h3>
            <p>Our courses are developed by top-tier professionals to ensure students receive updated, practical training.</p>
        </div>
        <div class="fr-bento-card fr-bento-5 fr-bento-light">
            <i class="bi bi-headset fr-bento-icon"></i>
            <h3>Expert Support</h3>
            <p>Full operational &amp; marketing guidance.</p>
        </div>
    </div>
</div>
</section>

<!-- =========== SUPPORT ECOSYSTEM =========== -->
<section class="fr-support">
<div class="container">
    <div class="fr-support-grid">
        <div class="fr-support-intro">
            <span class="section-eyebrow">End-to-End Assistance</span>
            <h2>Our Comprehensive Support Ecosystem</h2>
            <p>We don't just grant a license; we build a partnership. From the moment you sign, we are with you at every step.</p>
            <div class="fr-cta-box">
                <h3>Ready to Start?</h3>
                <p>Talk to our expansion head today.</p>
                <a href="tel:<?= e(preg_replace('/[^0-9+]/','',$franchisePhone)) ?>" class="fr-cta-box-link">Connect Now <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        <div class="fr-support-cards">
            <div class="fr-support-card">
                <i class="bi bi-geo-alt-fill"></i>
                <h4>Site Selection</h4>
                <p>Strategic assistance in identifying high-traffic locations for maximum visibility.</p>
            </div>
            <div class="fr-support-card">
                <i class="bi bi-rulers"></i>
                <h4>Interior Design</h4>
                <p>Complete guidance on infrastructure setup to match the NIIB premium clinical aesthetic.</p>
            </div>
            <div class="fr-support-card">
                <i class="bi bi-person-plus-fill"></i>
                <h4>Staff Recruitment</h4>
                <p>Professional hiring and intensive training for your academic and sales teams.</p>
            </div>
            <div class="fr-support-card">
                <i class="bi bi-megaphone-fill"></i>
                <h4>Marketing Support</h4>
                <p>Pre-launch buzz creation and sustained digital marketing support for lead generation.</p>
            </div>
        </div>
    </div>
</div>
</section>

<!-- =========== INVESTMENT =========== -->
<section class="fr-invest dark-section">
    <svg class="fr-invest-rings" viewBox="0 0 400 400" fill="none">
        <circle cx="200" cy="200" r="180" stroke="white" stroke-width="2"/>
        <circle cx="200" cy="200" r="140" stroke="white" stroke-width="2"/>
        <circle cx="200" cy="200" r="100" stroke="white" stroke-width="2"/>
    </svg>
    <div class="container fr-invest-inner">
        <div class="section-title">
            <span class="section-eyebrow" style="color:var(--secondary-container)">The Commitment</span>
            <h2 style="color:#fff;">Investment &amp; Space Requirements</h2>
        </div>
        <div class="fr-invest-grid">
            <div class="fr-invest-card">
                <i class="bi bi-layout-text-window-reverse"></i>
                <h3>Space Required</h3>
                <p class="fr-invest-big">2,000 Sq. Ft.</p>
                <p class="fr-invest-sub">Strategic location on main roads or educational hubs preferred to ensure student walk-ins.</p>
            </div>
            <div class="fr-invest-card">
                <i class="bi bi-cash-stack"></i>
                <h3>Total Investment</h3>
                <p class="fr-invest-big">₹30 Lakhs+</p>
                <p class="fr-invest-sub">Covers license fee, interior setup, initial inventory, branding, and staff training.</p>
            </div>
        </div>
        <p class="fr-invest-note">*(Exact investment may vary depending on city tier, property type, and institute size.)</p>
    </div>
</section>

<!-- =========== TESTIMONIALS =========== -->
<section class="fr-testimonials">
<style>
.fr-testi-carousel{ position:relative; margin-top:8px; }
.fr-testi-track{ display:flex; gap:24px; overflow-x:auto; scroll-snap-type:x mandatory; scroll-behavior:smooth; padding:6px 2px; scrollbar-width:none; -ms-overflow-style:none; }
.fr-testi-track::-webkit-scrollbar{ display:none; }
.fr-testi-slide{ flex:0 0 calc(33.333% - 16px); scroll-snap-align:start; }
.fr-testi-slide img{ width:100%; aspect-ratio:1 / 1; object-fit:cover; display:block; border-radius:var(--radius-sm); box-shadow:0 12px 34px rgba(23,3,56,.12); }
.fr-testi-arrow{ position:absolute; top:50%; transform:translateY(-50%); width:46px; height:46px; border-radius:50%; border:none; background:var(--primary); color:#fff; cursor:pointer; z-index:3; display:flex; align-items:center; justify-content:center; font-size:20px; box-shadow:0 8px 20px rgba(23,3,56,.25); transition:background .2s ease; }
.fr-testi-arrow:hover{ background:var(--secondary-container); }
.fr-testi-prev{ left:-12px; }
.fr-testi-next{ right:-12px; }
@media (max-width:900px){ .fr-testi-slide{ flex-basis:calc(50% - 12px); } }
@media (max-width:600px){ .fr-testi-slide{ flex-basis:100%; } .fr-testi-arrow{ width:40px; height:40px; } .fr-testi-prev{ left:2px; } .fr-testi-next{ right:2px; } }
</style>
<div class="container">
    <div class="section-title">
        <span class="section-eyebrow">Success Stories</span>
        <h2>Voices of our Franchise Partners</h2>
    </div>
    <?php
      $frTesti = glob(__DIR__ . '/uploads/franchise-testimonials/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
      natsort($frTesti);
      $frTesti = array_values($frTesti);
    ?>
    <?php if ($frTesti): ?>
    <div class="fr-testi-carousel">
      <button type="button" class="fr-testi-arrow fr-testi-prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
      <div class="fr-testi-track">
        <?php foreach ($frTesti as $ft): $rel = str_replace(__DIR__ . '/', '', $ft); ?>
          <div class="fr-testi-slide"><img src="<?= e(img_url($rel)) ?>" alt="NIIB Franchise Partner Testimonial" loading="lazy"></div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="fr-testi-arrow fr-testi-next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
    </div>
    <?php endif; ?>
</div>
</section>
<script>
(function(){
  var track = document.querySelector('.fr-testi-track');
  if (!track) return;
  function step(){ var c = track.querySelector('.fr-testi-slide'); if(!c) return track.clientWidth; var g=parseInt(getComputedStyle(track).columnGap||getComputedStyle(track).gap||'0',10)||0; return c.getBoundingClientRect().width + g; }
  var prev = document.querySelector('.fr-testi-prev'), next = document.querySelector('.fr-testi-next');
  if (prev) prev.addEventListener('click', function(){ (track.scrollLeft<=4) ? track.scrollTo({left:track.scrollWidth,behavior:'smooth'}) : track.scrollBy({left:-step(),behavior:'smooth'}); });
  if (next) next.addEventListener('click', function(){ (track.scrollLeft+track.clientWidth>=track.scrollWidth-4) ? track.scrollTo({left:0,behavior:'smooth'}) : track.scrollBy({left:step(),behavior:'smooth'}); });
})();
</script>

<!-- =========== STEPS =========== -->
<section class="fr-steps">
<div class="container">
    <div class="section-title">
        <span class="section-eyebrow">The Path Forward</span>
        <h2>5 Steps to Own Your Future</h2>
    </div>
    <div class="fr-steps-wrap">
        <div class="fr-steps-line"></div>
        <div class="fr-steps-grid">
            <div class="fr-step">
                <div class="fr-step-circle">01</div>
                <h3 style="font-size:22px;">Enquiry</h3>
                <p>Connect with our franchise team online.</p>
            </div>
            <div class="fr-step">
                <div class="fr-step-circle">02</div>
                <h3 style="font-size:22px;">Application</h3>
                <p>Share your business profile and location details.</p>
            </div>
            <div class="fr-step">
                <div class="fr-step-circle">03</div>
                <h3 style="font-size:22px;">Approval</h3>
                <p>Direct discussion with management &amp; site visit.</p>
            </div>
            <div class="fr-step">
                <div class="fr-step-circle">04</div>
                <h3 style="font-size:22px;">Setup</h3>
                <p>Staff training and interior transformation.</p>
            </div>
            <div class="fr-step">
                <div class="fr-step-circle fr-step-active">05</div>
                <h3 style="font-size:22px;">Launch</h3>
                <p>Grand opening with full marketing support.</p>
            </div>
        </div>
    </div>
</div>
</section>

<!-- =========== LEAD FORM =========== -->
<section class="fr-lead" id="enquire">
<div class="container">
    <div class="fr-lead-grid">
        <div class="fr-lead-info">
            <h2>Ready to Transform Lives?</h2>
            <p>Join the NIIB family and become a leader in the vocational training space. Fill out the form, and our franchise development head will reach out to you within 24 hours.</p>
            <div class="fr-lead-contacts">
                <a href="mailto:<?= e($franchiseEmail) ?>" class="fr-lead-contact">
                    <span class="fr-lead-icon"><i class="bi bi-envelope-fill"></i></span>
                    <div>
                        <p class="fr-lead-label">Email Us</p>
                        <p class="fr-lead-val"><?= e($franchiseEmail) ?></p>
                    </div>
                </a>
                <a href="tel:<?= e(preg_replace('/[^0-9+]/','',$franchisePhone)) ?>" class="fr-lead-contact">
                    <span class="fr-lead-icon"><i class="bi bi-telephone-fill"></i></span>
                    <div>
                        <p class="fr-lead-label">Call Directly</p>
                        <p class="fr-lead-val"><?= e($franchisePhone) ?></p>
                    </div>
                </a>
            </div>
        </div>

        <div class="fr-form-card">
            <p style="font-size:32px; font-weight:600; text-align:center;">Franchise Inquiry</p>
            <!-- <form id="franchiseForm">
                <input type="hidden" name="source" value="franchise">
                <input type="hidden" name="course_name" value="Franchise Enquiry">
                <div class="fr-form-row two">
                    <div class="fr-field">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Enter your name" required>
                    </div>
                    <div class="fr-field">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" placeholder="+91 XXXX XXX XXX">
                    </div>
                </div>
                <div class="fr-field">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="example@mail.com" required>
                </div>
                <div class="fr-field">
                    <label>City of Interest</label>
                    <input type="text" name="city" placeholder="Which city are you looking at?">
                </div>
                <div class="fr-field">
                    <label>Investment Range</label>
                    <select name="message">
                        <option value="₹30 - ₹40 Lakhs">₹30 - ₹40 Lakhs</option>
                        <option value="₹40 - ₹50 Lakhs">₹40 - ₹50 Lakhs</option>
                        <option value="Above ₹50 Lakhs">Above ₹50 Lakhs</option>
                    </select>
                </div>
                <?= niib_recaptcha_widget() ?>
                <button type="submit" class="btn btn-cta btn-lg fr-form-submit">Request Call Back</button>
                <div id="franchiseMsg"></div>
            </form> -->

            <div id="crm-form-container-57"></div>
<script>
(function() {
    var cid = 'crm-form-container-57';
    function load() {
        var c = document.getElementById(cid);
        if (!c || c.dataset.loaded) return;
        c.dataset.loaded = '1';
        var iframe = document.createElement('iframe');
        iframe.id = 'crm-form-57';
        iframe.src = 'https://crm.mediagarh.com/CRM/forms/public/57?embed=1&landing_url=' + encodeURIComponent(window.location.href);
        iframe.width = '100%'; iframe.height = '550'; iframe.frameBorder = '0';
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
</div>
</section>

<?php include 'includes/footer.php'; ?>

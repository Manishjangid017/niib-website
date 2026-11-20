<?php
define('NIIB_INIT', true);
define('NIIB_SKIP_OPTIMIZE', true);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$logo_src = 'https://newimageinstitute.com/assets/images/logo.svg';
try {
    $logo_db = setting('site_logo');
    if (!empty($logo_db)) $logo_src = 'https://newimageinstitute.com/' . ltrim($logo_db, '/');
} catch (Throwable $ex) {}

/* ---- Editable content (change here) ---- */
$phone_display = '+91 78891-11907';
$phone_tel     = '+9178891 11907';
$topbar_email  = 'usheadexpansion@newimageinstitute.com';
$wa_number     = '917889111907';

$img_base = 'https://newimageinstitute.com/uploads/lp-images/';

// Hero + About backgrounds
$hero_bg  = $img_base . 'banner-image.webp';
$about_bg = $img_base . 'about-image.webp';

// Counter: [target, suffix, label, abbreviate(k)]
$stats = [
  [22, '+', 'Years of Legacy', false],
  [26, '+', 'Academies',       false],
  [7, '',  'States',          false],
  [1500, '+', 'Students',     true],   // shows 1.5k+
];

// About chips
$about_chips = [
  'Hair styling & technology',
  'Beauty & skincare',
  'Professional makeup',
  'Salon & spa management',
];

// Why choose (zig-zag) — [title, description, image]
$why_items = [
  ['Year-Round Demand',
   'The beauty and wellness sector is witnessing rapid growth, increasing the need for trained professionals.',
   $img_base . 'why-choose-img-1.webp'],
  ['Multiple Revenue Streams',
   'Course fees, practical salon services, product kits and workshops.',
   $img_base . 'why-choose-img-2.webp'],
  ['Trusted Since 2004',
   '22+ years in beauty education, 26 academies across 7 states.',
   $img_base . 'why-choose-img-3.webp'],
  ['Accredited Curriculum',
   'Certification aligned with NSDC, B&WSSC, IAO and Pathway to Success (UK).',
   $img_base . 'why-choose-img-4.webp'],
  ['End-to-End Support',
   'Site selection, interiors, staff training, branch marketing and lead support.',
   $img_base . 'why-choose-img-5.webp'],
];

/* ---------- Assistance ecosystem ---------- */
$spark      = $img_base . 'sparkle.svg';              // reusable sparkle (SVG)
$assist_bg  = $img_base . 'support-bg-img.webp';     // single bg image (4 corner gradients)
$hub_img    = $img_base . 'ecosystem-img.webp';            // sun rays + rings image (no text, no center navy circle)

// [icon, number, title, description]
$assist_items = [
  ['bi-geo-alt',   '01', 'Site Selection',     'Strategic assistance in identifying high-traffic locations for maximum visibility.'],
  ['bi-lightbulb', '02', 'Interior Design',    'Complete guidance on infrastructure setup to match the NIIB premium clinical aesthetic.'],
  ['bi-scissors',  '03', 'Staff Recruitment',  'Professional hiring and intensive training for your academic and sales teams.'],
  ['bi-stars',     '04', 'Marketing Support',  'Pre-launch buzz creation and sustained digital marketing support for lead generation.'],
];

// Sparkles: [left %, top %, size px, animation delay s]
$sparks = [
  [15.9, 13.6, 26, 0],
  [84.4, 13.6, 26, .8],
  [66.3, 19.5, 34, 1.6],
  [7.9,  50.8, 18, .4],
  [92.6, 50.8, 18, 1.2],
  [16.1, 88.0, 22, 2.0],
  [84.5, 87.9, 26, .6],
];

/* ---------- Testimonials (Swiper) ---------- */
$arrow_prev  = $img_base . 'left-arrow-icon.webp';          // icon only (brush) — bg is CSS
$arrow_next  = $img_base . 'right-arrow-icon.webp';          // icon only (brush) — bg is CSS
$tcard_bg    = $img_base . 'success-story-bg-img.svg'; // notched card shape (desktop/tablet)
$tcard_bg_m  = '';                                     // optional mobile card bg image (leave '' for gradient)
$swirl       = $img_base . 'swirl-arrow.svg';          // curved arrow decoration

// Shared strip under each testimonial: [type, value, label]  (type: num | icon)
$t_stats = [
  ['num',  '30+',               'Successful Academies'],
  ['num',  '20+',               'Years of Legacy'],
  ['icon', 'bi-graph-up-arrow', 'Low Investment, High Return'],
  ['icon', 'bi-shield-check',   'Trusted Business'],
  ['icon', 'bi-life-preserver', 'End-to-End Franchise Support'],
];

$testimonials = [
    [
        'Ms.',
        'Surjit Kaur',
        'NIIB Franchise Owner · Rajpura',
        'With NIIB’s trusted name and expert support, starting my own franchise was smooth and stress-free. The best part is the quick growth and student trust I received.',
        'smooth and stress-free',
        $img_base . 'success-story-img-1.webp'
    ],
    [
        'Ms.',
        'Sunaina',
        'NIIB Franchise Owner · Fatehgarh',
        'Partnering with NIIB Institute as a Franchise Owner has been a highly professional experience. The brand offers complete operational support, structured training, and effective marketing strategies, which helped me establish and grow my center in a very systematic way.',
        '',
        $img_base . 'success-story-img-2.webp'
    ],
    [
        'Ms.',
        'Deepika',
        'NIIB Franchise Owner · Fatehgarh',
        'Partnering with NIIB Institute as a Franchise Owner has been a highly professional experience. The brand offers complete operational support, structured training, and effective marketing strategies, which helped me establish and grow my center in a very systematic way.',
        '',
        $img_base . 'success-story-img-3.webp'
    ],
    [
        'Ms.',
        'Navjot Kaur',
        'NIIB Franchise Owner · Kharar',
        "NIIB is more than just a franchise — it's a supportive partnership. From day one, their transparent process and expert guidance made the journey smooth. I started with many questions, but NIIB helped me with setup, training, and marketing, and today my institute is growing confidently.",
        '',
        $img_base . 'success-story-img-4.webp'
    ],
    [
        'Mr.',
        'Mayank Dixit',
        'NIIB Franchise Owner · Lucknow',
        'Partnering with NIIB as a franchise owner has been a game-changer for me. The brand provides professional staff training and step-by-step guidance, making the process smooth and structured. With strong brand value and continuous mentorship, my academy has achieved steady growth and excellent returns. NIIB truly stands as a profit-driven franchise partner.',
        '',
        $img_base . 'success-story-img-5.webp'
    ],
    [
        'Mrs.',
        'NIIB Franchise Owner',
        'NIIB Franchise Owner · Noida',
        "As a NIIB franchise owner, I'm extremely satisfied with the complete support system they provide. From professional staff training to strong marketing support and smooth business setup guidance, NIIB ensures you're never alone in the journey. It's a high-return opportunity backed by a reliable and experienced team.",
        '',
        $img_base . 'success-story-img-6.webp'
    ],
];

$steps = [
  ['01', 'Enquiry',     'Connect with our franchise team online.',          $img_base . 'step-icon-1.webp'],
  ['02', 'Application', 'Share your business profile and location details.', $img_base . 'step-icon-2.webp'],
  ['03', 'Approval',    'Direct discussion with management & site visit.',   $img_base . 'five-step-icon-3.svg'],
  ['04', 'Setup',       'Staff training and interior transformation.',       $img_base . 'five-step-icon-4.svg'],
  ['05', 'Launch',      'Grand opening with full marketing support.',        $img_base . 'five-step-icon-5.svg'],
];

/* ---------- Commitment (Investment & Space) ---------- */
// Big stats: [value, label, tone (orange | navy)]
$commit_stats = [
  ['1500+', 'Sq. Ft. space required',  'orange'],
  ['₹25+',  'Lakhs total investment',  'navy'],
];
$commit_items = ['License fee', 'Interior setup', 'Initial inventory', 'Branding', 'Staff training'];
$commit_note  = '*Exact investment may vary depending on city tier, property type and institute size.';


/* ---------- CTA + Footer ---------- */
$cta_bg       = $img_base . 'cta-img-1.webp';      // single image with all the circle shapes (desktop/tablet)
$cta_bg_m     = '';                                 // optional separate mobile bg image ('' = reuse desktop image)
$cta_avatars  = $img_base . 'Avatars.webp';  // single image with the 5 alumni avatars
$cta_rating   = '4.9/5 · 26+ academies, 7 states';
$dev_name     = 'Mediagarh';
$dev_url      = 'https://mediagarh.com/'; 

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Franchise Inquiry 2026 — New Image International Beauty Institute</title>
  <meta name="description" content="Own a NIIB franchise and build a profitable education business in India's booming beauty &amp; wellness sector. Apply for a franchise partnership with New Image International Beauty Institute.">
  <meta name="robots" content="noindex, nofollow">
  <?php
  $favicon_url = 'https://newimageinstitute.com/assets/images/favicon.ico';
  try { $fav = setting('site_favicon'); if (!empty($fav)) $favicon_url = 'https://newimageinstitute.com/' . ltrim($fav, '/'); } catch (Throwable $ex) {}
  ?>
  <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($favicon_url) ?>">
  <link rel="shortcut icon" href="<?= htmlspecialchars($favicon_url) ?>">
  <link rel="apple-touch-icon" href="<?= htmlspecialchars($favicon_url) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <!-- Literata -->
  <link href="https://fonts.googleapis.com/css2?family=Literata:ital,opsz,wght@0,7..72,300..900;1,7..72,300..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Swiper -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.1.14/swiper-bundle.min.css">

  <?php include __DIR__ . '/../includes/lp-styles.php'; ?>

  <!-- Franchise Landing Page — dedicated stylesheet (must load AFTER lp-styles) -->
  <link rel="stylesheet" href="https://newimageinstitute.com/assets/css/franchise-lp.css">

  <!-- enables scroll-reveal only when JS is available -->
  <script>document.documentElement.classList.add('niib-js');</script>

  <?= render_header_codes('head') ?>
  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-K5GSV965');</script>
  <!-- End Google Tag Manager -->
</head>
<body class="niib-fr">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K5GSV965"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?= render_header_codes('body_start') ?>

<!-- ================= HEADER ================= -->
<header class="niib-header" id="top">
  <div class="niib-container niib-header__inner">
    <a href="#top" class="niib-logo" aria-label="New Image International Beauty Institute">
      <img src="<?= htmlspecialchars($logo_src) ?>" alt="NIIB" width="120" height="48">
    </a>

    <nav class="niib-nav" id="niibNav" aria-label="Main navigation">
      <ul>
        <li><a href="#about">About</a></li>
        <li><a href="#advantage">Advantage</a></li>
        <li><a href="#assistance">Assistance</a></li>
        <li><a href="#commitment">Commitment</a></li>
        <li><a href="#story">Story</a></li>
        <li><a href="#path">Path</a></li>
      </ul>
    </nav>

    <a href="#franchise-form" class="niib-btn niib-btn--sm niib-header__cta">Enquire Now</a>

    <button class="niib-burger" id="niibBurger" aria-label="Toggle menu" aria-expanded="false" aria-controls="niibNav">
      <span></span><span></span><span></span>
    </button>
  </div>

  <!-- Orange top strip -->
  <div class="niib-topbar">
    <div class="niib-container niib-topbar__inner">
      <div class="niib-topbar__left">
        <span class="niib-dot"></span>
        <span class="niib-topbar__text">Admission Open 2026 —</span>
        <a href="#franchise-form" class="niib-topbar__link">Free Counselling Available</a>
      </div>
      <div class="niib-topbar__right">
        <a href="tel:<?= preg_replace('/\s+/', '', $phone_tel) ?>"><i class="bi bi-telephone"></i><span><?= htmlspecialchars($phone_display) ?></span></a>
        <a href="mailto:<?= htmlspecialchars($topbar_email) ?>" class="niib-topbar__mail"><i class="bi bi-envelope"></i><span><?= htmlspecialchars($topbar_email) ?></span></a>
      </div>
    </div>
  </div>
</header>
<div class="niib-overlay" id="niibOverlay"></div>

<main>
<!-- ================= HERO ================= -->
<section class="niib-hero" id="hero" style="--hero-bg:url('<?= htmlspecialchars($hero_bg) ?>');">
  <div class="niib-container">
    <div class="niib-hero__grid">

      <div class="niib-hero__content">
        <span class="niib-badge"><span class="niib-dot"></span>Premier Partnership Opportunity</span>
        <h1>Own a NIIB Institute Franchise</h1>
        <p class="niib-hero__sub">Build a Profitable Education Business &amp; Transform Careers in the booming beauty and wellness sector.</p>
        <div class="niib-hero__btns">
          <a href="#franchise-form" class="niib-btn"><i class="bi bi-file-earmark-text"></i> Franchise Proposal</a>
          <a href="tel:<?= preg_replace('/\s+/', '', $phone_tel) ?>" class="niib-btn niib-btn--ghost"><i class="bi bi-telephone"></i> Book a Visit to NIIB HQ</a>
        </div>
      </div>

      <div class="niib-form-card" id="franchise-form">
        <h2>Franchise Inquiry</h2>
        <p class="niib-form-card__sub">Tell us about yourself — we’ll take it from here.</p>
        <form class="niib-form" id="niibForm" method="post" action="" novalidate>
          <!-- Row 1: Name + Phone -->
          <div class="niib-field">
            <label for="f_name">Full Name <span aria-hidden="true">*</span></label>
            <input type="text" id="f_name" name="name" placeholder="Your name" autocomplete="name" required>
          </div>
          <div class="niib-field">
            <label for="f_mobile">Phone Number <span aria-hidden="true">*</span></label>
            <input type="tel" id="f_mobile" name="mobile" placeholder="+91 00000 00000" autocomplete="tel" inputmode="tel" required>
          </div>
          <!-- Row 2: Email + City -->
          <div class="niib-field">
            <label for="f_email">Email Address <span aria-hidden="true">*</span></label>
            <input type="email" id="f_email" name="email" placeholder="you@example.com" autocomplete="email" required>
          </div>
          <div class="niib-field">
            <label for="f_city">City <span aria-hidden="true">*</span></label>
            <input type="text" id="f_city" name="city" placeholder="Preferred city" autocomplete="address-level2" required>
          </div>
          <!-- Q1: Investment budget -->
          <div class="niib-field niib-field--full">
            <label for="f_investment">What is your planned investment budget for the franchise? <span aria-hidden="true">*</span></label>
            <select id="f_investment" name="investment" required>
              <option value="" disabled selected>Select a range</option>
              <option value="₹25–30 lakh">₹25–30 lakh</option>
              <option value="₹30–50 lakh">₹30–50 lakh</option>
              <option value="Above ₹50 lakh">Above ₹50 lakh</option>
            </select>
          </div>
          <!-- Q2: Commercial space -->
          <div class="niib-field niib-field--full">
            <label for="f_space_access">Do you own or have access to suitable commercial space? <span aria-hidden="true">*</span></label>
            <select id="f_space_access" name="space_access" required>
              <option value="" disabled selected>Select an option</option>
              <option value="Yes, I own a suitable property">Yes, I own a suitable property</option>
              <option value="I can arrange rented space">I can arrange rented space</option>
              <option value="I am still searching for space">I am still searching for space</option>
              <option value="No, I do not have access to space currently">No, I do not have access to space currently</option>
            </select>
          </div>
          <!-- Q3: Space size -->
          <div class="niib-field niib-field--full">
            <label for="f_space_size">What is the approximate size of your available space? <span aria-hidden="true">*</span></label>
            <select id="f_space_size" name="space_size" required>
              <option value="" disabled selected>Select an option</option>
              <option value="⁠Below 1,500 sq. ft.">Below 1,500 sq. ft.</option>
              <option value="1,500–2,500 sq. ft.">1,500–2,500 sq. ft.</option>
              <option value="Above 2,500 sq. ft.">Above 2,500 sq. ft.</option>
              <option value="Not yet finalised">Not yet finalised</option>
            </select>
          </div>
          <!-- Q4: Location in Haryana -->
          <div class="niib-field niib-field--full">
            <label for="f_location">Location you want to start in Haryana?</label>
            <input type="text" id="f_location" name="location" placeholder="e.g. Gurugram, Faridabad">
          </div>
          <!-- Honeypot -->
          <input type="text" name="website" class="niib-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
          <p class="niib-form__msg" id="niibFormMsg" role="status"></p>
          <button type="submit" class="niib-btn niib-btn--block" id="niibFormBtn">Request Franchise Proposal</button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ================= COUNTER ================= -->
<section class="niib-stats" aria-label="NIIB in numbers">
  <div class="niib-container stats-container">
    <div class="niib-stats__box">
      <?php foreach ($stats as [$target, $suffix, $label, $abbr]): ?>
      <div class="niib-stat">
        <span class="niib-stat__num"
              data-target="<?= (int)$target ?>"
              data-suffix="<?= htmlspecialchars($suffix) ?>"
              <?= $abbr ? 'data-abbr="1"' : '' ?>>0<?= htmlspecialchars($suffix) ?></span>
        <span class="niib-stat__label"><?= htmlspecialchars($label) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= ABOUT NIIB ================= -->
<section class="niib-about" id="about" aria-labelledby="aboutTitle">
  <div class="niib-container">
    <div class="niib-about__card niib-reveal" style="--about-bg:url('<?= htmlspecialchars($about_bg) ?>');">

      <div class="niib-est" aria-label="Established 2004">
        <span class="niib-est__label">Established</span>
        <span class="niib-est__year">2004</span>
      </div>

      <div class="niib-about__content">
        <span class="niib-eyebrow niib-eyebrow--light">Our Heritage</span>
        <h2 class="niib-about__title" id="aboutTitle">About <span>NIIB</span> Institute</h2>
        <blockquote class="niib-about__quote">
          A professional beauty, wellness and skill-development training organization committed to industry-oriented education.
        </blockquote>
        <p class="niib-about__text">
          Established with the vision of creating skilled professionals, NIIB Institute offers structured programs designed to meet current industry demands.
        </p>
        <ul class="niib-chips">
          <?php foreach ($about_chips as $chip): ?>
          <li><i class="bi bi-check2"></i><?= htmlspecialchars($chip) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="#story" class="niib-btn niib-btn--sm">Learn More</a>
      </div>

    </div>
  </div>
</section>

<!-- ================= WHY CHOOSE (ZIG-ZAG) ================= -->
<section class="niib-adv overflow-hidden" id="advantage" aria-labelledby="advTitle">
  <div class="niib-container">

    <span class="niib-eyebrow niib-reveal">The Advantage</span>
    <div class="niib-adv__head">
      <h2 class="niib-adv__title niib-reveal" id="advTitle">Why Choose a NIIB Beauty Institute <em>Franchise?</em></h2>
      <p class="niib-adv__lead niib-reveal">A proven education brand, a growing industry and a complete playbook — five reasons to partner with NIIB.</p>
    </div>

    <div class="niib-adv__list">
      <?php foreach ($why_items as [$w_title, $w_desc, $w_img]): ?>
      <article class="niib-adv-card niib-reveal">
        <div class="niib-adv-card__body">
          <h3><?= htmlspecialchars($w_title) ?></h3>
          <p><?= htmlspecialchars($w_desc) ?></p>
        </div>
        <div class="niib-adv-card__img">
          <img src="<?= htmlspecialchars($w_img) ?>" alt="<?= htmlspecialchars($w_title) ?>" loading="lazy" width="400" height="260">
        </div>
      </article>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- ================= ASSISTANCE — SUPPORT ECOSYSTEM ================= -->
<section class="niib-assist" id="assistance" aria-labelledby="assistTitle"
         style="--assist-bg:url('<?= htmlspecialchars($assist_bg) ?>');">

  <!-- sparkles (spark.svg reused everywhere) -->
  <?php foreach ($sparks as [$sl, $st, $ss, $sd]): ?>
  <img class="niib-spark" src="<?= htmlspecialchars($spark) ?>" alt="" aria-hidden="true" loading="lazy"
       style="left:<?= $sl ?>%;top:<?= $st ?>%;--s:<?= $ss ?>px;--d:<?= $sd ?>s;">
  <?php endforeach; ?>

  <div class="niib-container">
    <header class="niib-assist__head">
      <span class="niib-eyebrow niib-eyebrow--center niib-reveal"><i class="bi bi-search"></i> End-to-End Assistance</span>
      <h2 class="niib-assist__title niib-reveal" id="assistTitle">Our Comprehensive <em>Support</em> Ecosystem</h2>
      <p class="niib-assist__lead niib-reveal">We don’t just grant a license; we build a partnership. From the moment you sign, we are with you at every step.</p>
    </header>

    <div class="niib-eco">

      <!-- center hub: single image + navy circle on top -->
      <div class="niib-hub niib-reveal">
        <img src="<?= htmlspecialchars($hub_img) ?>" alt="" aria-hidden="true" loading="lazy">
        <div class="niib-hub__core">
          <strong>NIIB</strong>
          <span>Partner Support</span>
        </div>
      </div>

      <?php foreach ($assist_items as $i => [$a_icon, $a_num, $a_title, $a_desc]): ?>
      <article class="niib-eco-card c<?= $i + 1 ?> niib-reveal">
        <div class="niib-eco-card__top">
          <span class="niib-eco-card__icon"><i class="bi <?= htmlspecialchars($a_icon) ?>"></i></span>
          <span class="niib-eco-card__num"><?= htmlspecialchars($a_num) ?></span>
        </div>
        <h3><?= htmlspecialchars($a_title) ?></h3>
        <p><?= htmlspecialchars($a_desc) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= SUCCESS STORIES (SWIPER) ================= -->
<section class="niib-voices overflow-hidden" id="story" aria-labelledby="voicesTitle">
  <div class="niib-container success-story-container">
    <header class="niib-voices__head">
      <span class="niib-eyebrow niib-eyebrow--center niib-reveal">Success Stories</span>
      <h2 class="niib-voices__title niib-reveal" id="voicesTitle"><em>Voices</em> of our Franchise Partners</h2>
    </header>

    <div class="niib-tslider niib-reveal">
      <button type="button" class="niib-t-arrow niib-t-prev" aria-label="Previous story">
        <img class="niib-t-icon" src="<?= htmlspecialchars($arrow_prev) ?>" alt="" aria-hidden="true">
      </button>

      <div class="swiper niib-tswiper">
        <div class="swiper-wrapper">
          <?php foreach ($testimonials as [$t_sal, $t_name, $t_role, $t_quote, $t_hl, $t_photo]):
            $quote_html = htmlspecialchars($t_quote);
            if ($t_hl !== '') {
              $quote_html = str_replace(htmlspecialchars($t_hl), '<em>' . htmlspecialchars($t_hl) . '</em>', $quote_html);
            }
          ?>
          <div class="swiper-slide">
            <article class="niib-tcard" style="--tcard-bg:url('<?= htmlspecialchars($tcard_bg) ?>');<?= $tcard_bg_m !== '' ? "--tcard-bg-m:url('" . htmlspecialchars($tcard_bg_m) . "');" : '' ?>">

              <img class="niib-tcard__swirl" src="<?= htmlspecialchars($swirl) ?>" alt="" aria-hidden="true" loading="lazy">

              <div class="niib-tcard__body">
                <h3 class="niib-tcard__name"><?= htmlspecialchars($t_sal) ?> <strong><?= htmlspecialchars($t_name) ?></strong></h3>
                <span class="niib-tcard__line" aria-hidden="true"></span>
                <p class="niib-tcard__role"><?= htmlspecialchars($t_role) ?></p>
                <p class="niib-tcard__quote">“<?= $quote_html ?>”</p>

                <ul class="niib-tcard__stats">
                  <?php foreach ($t_stats as [$s_type, $s_val, $s_label]): ?>
                  <li>
                    <?php if ($s_type === 'num'): ?>
                      <strong><?= htmlspecialchars($s_val) ?></strong>
                    <?php else: ?>
                      <i class="bi <?= htmlspecialchars($s_val) ?>"></i>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($s_label) ?></span>
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>

              <div class="niib-tcard__photo">
                <img src="<?= htmlspecialchars($t_photo) ?>" alt="<?= htmlspecialchars($t_sal . ' ' . $t_name) ?>" loading="lazy">
              </div>
            </article>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="swiper-pagination niib-tpag"></div>
      </div>

      <button type="button" class="niib-t-arrow niib-t-next" aria-label="Next story">
        <img class="niib-t-icon" src="<?= htmlspecialchars($arrow_next) ?>" alt="" aria-hidden="true">
      </button>
    </div>
  </div>
</section>

<!-- ================= PATH — 5 STEPS TO OWN YOUR FUTURE ================= -->
<!-- paste right AFTER the Success Stories </section> and BEFORE </main> -->
<section class="niib-path" id="path" aria-labelledby="pathTitle">
  <div class="niib-container niib-path__container">
    <header class="niib-path__head">
      <span class="niib-eyebrow niib-reveal">The Path Forward</span>
      <h2 class="niib-path__title niib-reveal" id="pathTitle">5 Steps to <em>Own</em> Your Future</h2>
    </header>

    <ol class="niib-steps">
      <?php foreach ($steps as $pi => [$p_num, $p_title, $p_desc, $p_icon]): ?>
      <li class="niib-step" style="--i:<?= $pi ?>;">
        <img class="niib-step__icon" src="<?= htmlspecialchars($p_icon) ?>" alt="" aria-hidden="true" loading="lazy">
        <div class="niib-step__card">
          <span class="niib-step__num"><?= htmlspecialchars($p_num) ?></span>
          <span class="niib-step__line" aria-hidden="true"></span>
          <h3><?= htmlspecialchars($p_title) ?></h3>
          <p><?= htmlspecialchars($p_desc) ?></p>
        </div>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- ================= COMMITMENT — INVESTMENT & SPACE ================= -->
<!-- paste right AFTER the Assistance </section> and BEFORE the Success Stories section -->
<section class="niib-commit" id="commitment" aria-labelledby="commitTitle">
  <div class="niib-container">
    <div class="niib-commit__grid">

      <div class="niib-commit__left">
        <span class="niib-eyebrow niib-reveal">The Commitment</span>
        <h2 class="niib-commit__title niib-reveal" id="commitTitle">Investment &amp; <em>Space</em> Requirements</h2>

        <div class="niib-commit__stats">
          <?php foreach ($commit_stats as [$c_val, $c_label, $c_tone]): ?>
          <div class="niib-big niib-big--<?= htmlspecialchars($c_tone) ?> niib-reveal">
            <strong><?= htmlspecialchars($c_val) ?></strong>
            <span><?= htmlspecialchars($c_label) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <aside class="niib-sum niib-reveal" aria-label="Investment summary">
        <div class="niib-sum__card">
          <span class="niib-sum__eyebrow">Investment Summary</span>
          <h3 class="niib-sum__title">What your franchise covers</h3>

          <ul class="niib-sum__list">
            <?php foreach ($commit_items as $ci => $c_item): ?>
            <li>
              <span class="niib-sum__check"><i class="bi bi-check2"></i></span>
              <span class="niib-sum__name"><?= htmlspecialchars($c_item) ?></span>
              <span class="niib-sum__num"><?= str_pad($ci + 1, 2, '0', STR_PAD_LEFT) ?></span>
            </li>
            <?php endforeach; ?>
          </ul>

          <p class="niib-sum__note"><?= htmlspecialchars($commit_note) ?></p>
          <a href="#franchise-form" class="niib-btn niib-btn--block">Get Franchise Proposal</a>
        </div>
      </aside>

    </div>
  </div>
</section>

<!-- ================= CTA ================= -->
<!-- paste right AFTER the Path </section> and BEFORE </main> -->
<section class="niib-cta" id="cta" aria-labelledby="ctaTitle">
  <div class="niib-container">
    <div class="niib-cta__frame niib-reveal">
      <div class="niib-cta__card" style="--cta-bg:url('<?= htmlspecialchars($cta_bg) ?>');<?= $cta_bg_m !== '' ? "--cta-bg-m:url('" . htmlspecialchars($cta_bg_m) . "');" : '' ?>">
        <div class="niib-cta__grid">

          <div class="niib-cta__content">
            <span class="niib-cta__badge"><i class="bi bi-check-square-fill"></i>Admissions Open 2026</span>
            <h2 class="niib-cta__title" id="ctaTitle">Ready to Own Your<br class="niib-cta__br"> NIIB Beauty <em>Franchise?</em></h2>
            <p class="niib-cta__text">Join India’s trusted beauty education brand. Low investment, complete training and launch support — your academy awaits.</p>
          </div>

          <div class="niib-cta__side">
            <div class="niib-cta__btns">
              <a href="#franchise-form" class="niib-cta__btn niib-cta__btn--solid">Request Proposal <i class="bi bi-arrow-right"></i></a>
              <a href="tel:<?= preg_replace('/\s+/', '', $phone_tel) ?>" class="niib-cta__btn niib-cta__btn--outline">Call Us Now</a>
            </div>
            <div class="niib-cta__proof">
              <img src="<?= htmlspecialchars($cta_avatars) ?>" alt="Our alumni" loading="lazy">
              <span><?= htmlspecialchars($cta_rating) ?></span>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= FOOTER ================= -->
<!-- paste right AFTER the existing </main> and BEFORE the floating WhatsApp button -->
<footer class="niib-footer">
  <div class="niib-container niib-footer__inner">
    <p class="niib-footer__copy">© <?= date('Y') ?> New Image International Beauty Institute. All rights reserved.</p>
    <p class="niib-footer__dev">Designed and Developed By <b target="_blank" rel="noopener"><?= htmlspecialchars($dev_name) ?></b></p>
  </div>
</footer>
</main>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/<?= htmlspecialchars($wa_number) ?>" class="lp-float-wa" target="_blank" rel="noopener" aria-label="WhatsApp us">
  <i class="bi bi-whatsapp"></i>
</a>
<!-- Floating Call Button -->
<a href="tel:+<?= htmlspecialchars($wa_number) ?>" class="lp-float-call" aria-label="Call us">
  <i class="bi bi-telephone-fill"></i>
</a>

<script src="https://cdn.jsdelivr.net/npm/swiper@11.1.14/swiper-bundle.min.js"></script>
<script>
(function () {
  /* ---------- Mobile menu ---------- */
  var body = document.body,
      burger = document.getElementById('niibBurger'),
      overlay = document.getElementById('niibOverlay'),
      nav = document.getElementById('niibNav');

  function setMenu(open) {
    body.classList.toggle('menu-open', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  burger.addEventListener('click', function () { setMenu(!body.classList.contains('menu-open')); });
  overlay.addEventListener('click', function () { setMenu(false); });
  nav.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setMenu(false); });
  window.addEventListener('resize', function () { if (window.innerWidth > 991) setMenu(false); });

  /* ---------- Counter ---------- */
  var nums = document.querySelectorAll('.niib-stat__num');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function fmt(el, v) {
    var suffix = el.dataset.suffix || '';
    if (el.dataset.abbr) {
      var k = v / 1000;
      return (k >= 1 ? (Math.round(k * 10) / 10).toString().replace(/\.0$/, '') + 'k' : Math.round(v)) + suffix;
    }
    return Math.round(v).toLocaleString('en-IN') + suffix;
  }
  function run(el) {
    var target = parseFloat(el.dataset.target) || 0, dur = 2000, start = null;
    if (reduce) { el.textContent = fmt(el, target); return; }
    function step(ts) {
      if (!start) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);            // easeOutCubic
      el.textContent = fmt(el, target * eased);
      if (p < 1) requestAnimationFrame(step); else el.textContent = fmt(el, target);
    }
    requestAnimationFrame(step);
  }
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { run(en.target); io.unobserve(en.target); }
      });
    }, { threshold: 0.4 });
    nums.forEach(function (n) { io.observe(n); });
  } else {
    nums.forEach(run);
  }

  /* ---------- Scroll reveal ---------- */
  var reveals = document.querySelectorAll('.niib-reveal');
  if ('IntersectionObserver' in window && !reduce) {
    var rio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('is-in'); rio.unobserve(en.target); }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    reveals.forEach(function (r) { rio.observe(r); });
  } else {
    reveals.forEach(function (r) { r.classList.add('is-in'); });
  }

  /* ---------- Testimonials slider (Swiper) ---------- */
  if (window.Swiper && document.querySelector('.niib-tswiper')) {
    new Swiper('.niib-tswiper', {
      loop: true,
      speed: 700,
      slidesPerView: 1,
      spaceBetween: 24,
      grabCursor: true,
      keyboard: { enabled: true },
      autoplay: reduce ? false : { delay: 5500, disableOnInteraction: false, pauseOnMouseEnter: true },
      navigation: { prevEl: '.niib-t-prev', nextEl: '.niib-t-next' },
      pagination: false,
      a11y: { enabled: true }
    });
  }

  /* ---------- Arrow icon flip (top to bottom) on click ---------- */
  document.querySelectorAll('.niib-t-arrow').forEach(function (btn) {
    var icon = btn.querySelector('.niib-t-icon'), turns = 0;
    if (!icon) return;
    btn.addEventListener('click', function () {
      turns++;
      icon.style.transform = 'rotateX(' + (turns * 180) + 'deg)';
    });
  });

  /* ---------- Form — API submit ---------- */
  var form = document.getElementById('niibForm'),
      msg  = document.getElementById('niibFormMsg'),
      btn  = document.getElementById('niibFormBtn');

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    msg.className = 'niib-form__msg';

    var name        = form.name.value.trim(),
        mobile      = form.mobile.value.replace(/\D/g, ''),
        email       = form.email.value.trim(),
        city        = form.city.value.trim(),
        investment  = form.investment.value,
        spaceAccess = form.space_access.value,
        spaceSize   = form.space_size.value,
        location    = form.location.value.trim();

    // Honeypot
    if (form.website.value) return;

    // Validation
    if (!name) { showErr('Please enter your full name.'); return; }
    if (mobile.length < 10) { showErr('Please enter a valid 10-digit mobile number.'); return; }
    if (!/^\S+@\S+\.\S+$/.test(email)) { showErr('Please enter a valid email address.'); return; }
    if (!city) { showErr('Please enter your city.'); return; }
    if (!investment) { showErr('Please select your investment budget.'); return; }
    if (!spaceAccess) { showErr('Please select a space access option.'); return; }
    if (!spaceSize) { showErr('Please select your available space size.'); return; }

    btn.disabled = true;
    btn.textContent = 'Submitting…';

    // UTM params
    var params = new URLSearchParams(window.location.search);

    var payload = {
      form_id:       57,
      first_name:    name,
      last_name:     '',
      email:         email,
      phone:         mobile,
      utm_source:    params.get('utm_source')   || '',
      utm_medium:    params.get('utm_medium')   || '',
      utm_campaign:  params.get('utm_campaign') || '',
      landing_url:   window.location.href,
      referrer_url:  document.referrer || '',
      custom_fields: {
        'field_field_1792822783043_oev9z': {
          label: 'Name', type: 'text', value: name, map_to: 'first_name'
        },
        'field_field_1792822783899_wuj8k': {
          label: 'Phone Number', type: 'phone', value: mobile, map_to: 'phone_number'
        },
        'field_field_1792822786163_qwehl': {
          label: 'Email Address', type: 'email', value: email, map_to: 'email'
        },
        'field_field_1792822792594_cf79s': {
          label: 'City', type: 'text', value: city, map_to: 'city'
        },
        'field_field_1795090325082_wfabi': {
          label: 'What is your planned investment budget for the franchise?',
          type: 'select', value: investment, map_to: 'investment_range'
        },
        'field_field_1795090376749_9n77e': {
          label: 'Do you own or have access to suitable commercial space?',
          type: 'select', value: spaceAccess, map_to: 'suitable_commercial_space'
        },
        'field_field_1795090475931_ruv4r': {
          label: 'What is the approximate size of your available space?',
          type: 'select', value: spaceSize, map_to: 'approximate_size_of_your_available_space'
        },
        'field_field_1795090520798_7w670': {
          label: 'Location you want to start in Haryana?',
          type: 'text', value: location, map_to: 'start_in_haryana'
        }
      }
    };

    fetch('https://crm.mediagarh.com/CRM/api/leads/create', {
      method:  'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Api-Key':    'b62d20f5cd5e82d3ee1cfe7fc85cccbbe43db37e36830923557c912602afe023'
      },
      body: JSON.stringify(payload)
    })
    .then(function (r) {
      if (!r.ok) throw new Error('Server error ' + r.status);
      return r.json();
    })
    .then(function () {
      window.location.href = 'https://newimageinstitute.com/thank-you';
    })
    .catch(function () {
      showErr('Something went wrong. Please try again or call us directly.');
      btn.disabled = false;
      btn.textContent = 'Request Franchise Proposal';
    });

    function showErr(text) {
      msg.textContent = text;
      msg.classList.add('is-err');
    }
  });

  /* ---------- Path staircase animation ---------- */
  var stepEls = document.querySelectorAll('.niib-step');
  function showStep(s) { s.classList.add('is-in'); }
  if ('IntersectionObserver' in window && !reduce) {
    var stacked = window.matchMedia('(max-width: 767.98px)').matches;
    var sio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        var group = stacked ? [en.target] : Array.prototype.slice.call(en.target.querySelectorAll('.niib-step'));
        group.forEach(showStep);
        sio.unobserve(en.target);
      });
    }, { threshold: stacked ? 0.3 : 0.2, rootMargin: '0px 0px -40px 0px' });
    var stepTargets = stacked ? stepEls : document.querySelectorAll('.niib-steps');
    Array.prototype.forEach.call(stepTargets, function (t) { sio.observe(t); });
  } else {
    Array.prototype.forEach.call(stepEls, showStep);
  }

})();
</script>
</body>
</html>
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
$phone_display = '+91 78890-15507';
$phone_tel     = '+9178890 15507';
$topbar_email  = 'info@newimageinstitute.com';
$wa_number     = '919780055576';

// Hero background image (add manually)
$hero_bg = 'https://newimageinstitute.com/uploads/lp-images/banner-image.webp';

// Counter: [target, suffix, label, abbreviate(k)]
$stats = [
  [1, '+', 'Years of Legacy', false],
  [1, '+', 'Academies',       false],
  [1, '',  'States',          false],
  [1000, '+', 'Students',     true],   // shows 1k+
];
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

  <?php include __DIR__ . '/../includes/lp-styles.php'; ?>

  <!-- Franchise Landing Page — dedicated stylesheet (must load AFTER lp-styles) -->
  <link rel="stylesheet" href="https://newimageinstitute.com/assets/css/franchise-lp.css">

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
          <div class="niib-field">
            <label for="f_name">Full Name</label>
            <input type="text" id="f_name" name="name" placeholder="Your name" autocomplete="name" required>
          </div>
          <div class="niib-field">
            <label for="f_mobile">Mobile Number</label>
            <input type="tel" id="f_mobile" name="mobile" placeholder="+91 00000 00000" autocomplete="tel" inputmode="tel" required>
          </div>
          <div class="niib-field niib-field--full">
            <label for="f_email">Email Address</label>
            <input type="email" id="f_email" name="email" placeholder="you@example.com" autocomplete="email" required>
          </div>
          <div class="niib-field">
            <label for="f_city">City</label>
            <input type="text" id="f_city" name="city" placeholder="Preferred city" autocomplete="address-level2">
          </div>
          <div class="niib-field">
            <label for="f_space">Space (sq. ft.)</label>
            <input type="text" id="f_space" name="space" placeholder="e.g. 1500" inputmode="numeric">
          </div>
          <div class="niib-field niib-field--full">
            <label for="f_msg">Message (optional)</label>
            <textarea id="f_msg" name="message" placeholder="Tell us about your plans"></textarea>
          </div>
          <input type="text" name="website" class="niib-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
          <p class="niib-form__msg" id="niibFormMsg" role="status"></p>
          <button type="submit" class="niib-btn niib-btn--block">Request Franchise Proposal</button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ================= COUNTER ================= -->
<section class="niib-stats" aria-label="NIIB in numbers">
  <div class="niib-container">
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
</main>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/<?= htmlspecialchars($wa_number) ?>" class="lp-float-wa" target="_blank" rel="noopener" aria-label="WhatsApp us">
  <i class="bi bi-whatsapp"></i>
</a>
<!-- Floating Call Button -->
<a href="tel:+<?= htmlspecialchars($wa_number) ?>" class="lp-float-call" aria-label="Call us">
  <i class="bi bi-telephone-fill"></i>
</a>

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

  /* ---------- Form (basic validation + submit hook) ---------- */
  var form = document.getElementById('niibForm'),
      msg = document.getElementById('niibFormMsg');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    msg.className = 'niib-form__msg';
    var name = form.name.value.trim(),
        mobile = form.mobile.value.replace(/\D/g, ''),
        email = form.email.value.trim();
    if (form.website.value) return;                                   // honeypot
    if (!name || mobile.length < 10 || !/^\S+@\S+\.\S+$/.test(email)) {
      msg.textContent = 'Please enter a valid name, mobile number and email.';
      msg.classList.add('is-err'); return;
    }
    /* TODO: apni existing lead-submit endpoint yahan connect karo */
    // fetch('/franchise-submit.php', { method:'POST', body:new FormData(form) })
    //   .then(r => r.json()).then(...);
    msg.textContent = 'Thank you! Our team will contact you shortly.';
    msg.classList.add('is-ok');
    form.reset();
  });
})();
</script>
</body>
</html>
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

$phone_display = '+91 78891-11907';
$phone_tel     = '+917889111907';
$wa_number     = '917889111907';
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thank You — NIIB Franchise Inquiry</title>
  <meta name="robots" content="noindex, nofollow">
  <?php
  $favicon_url = 'https://newimageinstitute.com/assets/images/favicon.ico';
  try { $fav = setting('site_favicon'); if (!empty($fav)) $favicon_url = 'https://newimageinstitute.com/' . ltrim($fav, '/'); } catch (Throwable $ex) {}
  ?>
  <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($favicon_url) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Literata:ital,wght@0,300..900;1,300..900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://newimageinstitute.com/assets/css/franchise-lp.css">

  <!-- Google Tag Manager -->
  <script async src="https://www.googletagmanager.com/gtm.js?id=GTM-MWRVQW62"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
  </script>
  <!-- End Google Tag Manager -->

  <style>
    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      margin: 0; padding: 0;
      font-family: 'DM Sans', system-ui, sans-serif;
      background: #fcf9f8;
      color: #1c1245;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ---- Header ---- */
    .ty-header {
      background: #fff;
      padding: 0 24px;
      height: 72px;
      display: flex;
      align-items: center;
      box-shadow: 0 1px 0 rgba(0,0,0,.07);
    }
    @media (min-width: 768px) { .ty-header { padding: 0 80px; } }
    .ty-header img { height: 52px; width: auto; display: block; object-fit: contain; }

    /* ---- Main ---- */
    .ty-main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 24px;
    }

    .ty-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(28, 18, 69, .10);
      padding: 56px 48px 48px;
      max-width: 600px;
      width: 100%;
      text-align: center;
    }
    @media (max-width: 575px) {
      .ty-card { padding: 40px 24px 36px; }
    }

    /* checkmark circle */
    .ty-icon {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: linear-gradient(135deg, #ff7842 0%, #ff4900 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 28px;
      box-shadow: 0 12px 32px rgba(255, 73, 0, .30);
      animation: ty-pop .5s cubic-bezier(.34,1.56,.64,1) both;
    }
    .ty-icon i {
      font-size: 36px;
      color: #fff;
      line-height: 1;
    }
    @keyframes ty-pop {
      from { transform: scale(0); opacity: 0; }
      to   { transform: scale(1); opacity: 1; }
    }

    .ty-badge {
      display: inline-block;
      background: #fff3ee;
      color: #ff4900;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .18em;
      text-transform: uppercase;
      padding: 5px 14px;
      border-radius: 100px;
      margin-bottom: 16px;
    }

    .ty-title {
      font-family: 'Literata', Georgia, serif;
      font-size: clamp(26px, 5vw, 38px);
      font-weight: 700;
      color: #1c1245;
      line-height: 1.2;
      margin: 0 0 16px;
    }
    .ty-title em {
      font-style: italic;
      color: #ff4900;
    }

    .ty-sub {
      font-size: 16px;
      color: #49454e;
      line-height: 1.7;
      margin: 0 0 36px;
      max-width: 440px;
      margin-left: auto;
      margin-right: auto;
    }

    /* what happens next */
    .ty-steps {
      display: flex;
      flex-direction: column;
      gap: 14px;
      text-align: left;
      background: #fdf5f1;
      border-radius: 12px;
      padding: 24px 22px;
      margin-bottom: 36px;
    }
    .ty-step {
      display: flex;
      align-items: flex-start;
      gap: 14px;
    }
    .ty-step__num {
      flex-shrink: 0;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: linear-gradient(135deg, #ff7842 0%, #ff4900 100%);
      color: #fff;
      font-size: 12px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: 1px;
    }
    .ty-step__text { font-size: 14px; color: #3b3345; line-height: 1.6; }
    .ty-step__text strong { color: #1c1245; font-weight: 700; display: block; margin-bottom: 2px; }

    /* CTA buttons */
    .ty-btns {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    @media (min-width: 480px) {
      .ty-btns { flex-direction: row; justify-content: center; }
    }
    .ty-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 14px 28px;
      font-family: 'DM Sans', sans-serif;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: .06em;
      text-decoration: none;
      border-radius: 4px;
      transition: transform .2s ease, box-shadow .2s ease;
      white-space: nowrap;
    }
    .ty-btn--primary {
      background: linear-gradient(90deg, #ff7842 0%, #ff4900 100%);
      color: #fff;
    }
    .ty-btn--primary:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(255,73,0,.30); }
    .ty-btn--outline {
      background: #fff;
      color: #1c1245;
      border: 1.5px solid #d0c8e0;
    }
    .ty-btn--outline:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(28,18,69,.10); border-color: #1c1245; }

    /* ---- Footer ---- */
    .ty-footer {
      background: #1c1245;
      padding: 20px 24px;
      text-align: center;
    }
    .ty-footer p { margin: 0; font-size: 12px; color: rgba(255,255,255,.5); }

    /* ---- Floating buttons ---- */
    .lp-float-wa, .lp-float-call {
      position: fixed;
      width: 52px; height: 52px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 22px; text-decoration: none;
      box-shadow: 0 8px 20px rgba(0,0,0,.18);
      z-index: 999;
      transition: transform .25s ease;
    }
    .lp-float-wa  { background: #25d366; bottom: 28px; left: 28px; }
    .lp-float-wa:hover  { transform: scale(1.08); }
    .lp-float-call { background: linear-gradient(90deg, #ff7842 0%, #ff4900 100%); bottom: 90px; left: 28px; }
    .lp-float-call:hover { transform: scale(1.08); }
  </style>
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MWRVQW62"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<!-- Header -->
<header class="ty-header">
  <a href="https://newimageinstitute.com/" aria-label="New Image International Beauty Institute">
    <img src="<?= htmlspecialchars($logo_src, ENT_QUOTES) ?>" alt="NIIB Institute Logo"
         onerror="this.style.display='none'">
  </a>
</header>

<!-- Main -->
<main class="ty-main">
  <div class="ty-card">

    <div class="ty-icon">
      <i class="bi bi-check-lg"></i>
    </div>

    <span class="ty-badge">Inquiry Received</span>
    <h1 class="ty-title">Thank You for Your<br><em>Franchise Inquiry!</em></h1>
    <p class="ty-sub">We've received your details and our franchise team will get in touch with you shortly. Here's what happens next.</p>

    <!-- What happens next -->
    <div class="ty-steps">
      <div class="ty-step">
        <span class="ty-step__num">1</span>
        <div class="ty-step__text">
          <strong>Team Review</strong>
          Our franchise team will review your inquiry within 24 hours.
        </div>
      </div>
      <div class="ty-step">
        <span class="ty-step__num">2</span>
        <div class="ty-step__text">
          <strong>Counselling Call</strong>
          A dedicated franchise counsellor will call you to discuss the opportunity in detail.
        </div>
      </div>
      <div class="ty-step">
        <span class="ty-step__num">3</span>
        <div class="ty-step__text">
          <strong>HQ Visit &amp; Proposal</strong>
          Visit our headquarters, see the model in person, and receive your franchise proposal.
        </div>
      </div>
    </div>

    <!-- CTAs -->
    <div class="ty-btns">
      <a href="https://newimageinstitute.com/franchise" class="ty-btn ty-btn--outline">
        <i class="bi bi-arrow-left"></i> Back to Franchise Page
      </a>
      <a href="tel:<?= htmlspecialchars($phone_tel, ENT_QUOTES) ?>" class="ty-btn ty-btn--primary">
        <i class="bi bi-telephone-fill"></i> <?= htmlspecialchars($phone_display, ENT_QUOTES) ?>
      </a>
    </div>

  </div>
</main>

<!-- Footer -->
<footer class="ty-footer">
  <p>&copy; <?= date('Y') ?> New Image International Beauty Institute. All rights reserved.</p>
</footer>

<!-- Floating WhatsApp -->
<a href="https://wa.me/<?= htmlspecialchars($wa_number, ENT_QUOTES) ?>" class="lp-float-wa" target="_blank" rel="noopener" aria-label="WhatsApp us">
  <i class="bi bi-whatsapp"></i>
</a>
<!-- Floating Call -->
<a href="tel:+<?= htmlspecialchars($wa_number, ENT_QUOTES) ?>" class="lp-float-call" aria-label="Call us">
  <i class="bi bi-telephone-fill"></i>
</a>

</body>
</html>

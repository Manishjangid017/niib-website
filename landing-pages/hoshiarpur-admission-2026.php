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
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Phagwara Admission 2026 — New Image International Beauty Institute</title>
  <meta name="description" content="Enrol at NIIB Phagwara Signature Learning Center. World-class beauty &amp; aesthetics education. Admission open for 2026 batch.">
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
  <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,wght@0,400..900;1,400..900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <?php include __DIR__ . '/../includes/lp-styles.php'; ?>

  <style>
    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body { margin: 0; padding: 0; background: #fcf9f8; font-family: 'Manrope', system-ui, sans-serif; }

      .lp-foot-main {
        max-width:1280px;
      margin:40px auto;
      justify-content: space-between;
      }

      .lp-foot-contact {
        max-width:400px;

      }

    /* HEADER */
    .lp-topbar {
      position: sticky; top: 0; z-index: 999;
      background: #ffffff;
      padding: 0 20px; height: 72px;
      display: flex; align-items: center; justify-content: space-between;
      box-shadow: 0 2px 16px rgba(0,0,0,.10);
    }
    @media (min-width:768px) { .lp-topbar { padding: 0 80px; } }
    .lp-topbar-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; pointer-events: none; user-select: none; }
    .lp-topbar-logo img { height: 54px; width: auto; display: block; object-fit: contain; }
    .lp-topbar-logo span { font-family: 'Bodoni Moda', Georgia, serif; font-size: 18px; font-weight: 600; color: #170338; letter-spacing: .02em; white-space: nowrap; }
    .lp-topbar-cta { display: inline-flex; align-items: center; justify-content: center; padding: 10px 24px; background: #fe5e1e; color: #fff; font-size: 13px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; text-decoration: none; transition: background .2s ease, transform .2s ease; white-space: nowrap; }
    .lp-topbar-cta:hover { background: #ab3600; transform: translateY(-1px); }

    /* FOOTER */
    .lp-foot { background: #170338; color: rgba(255,255,255,.75); padding: 48px 20px 28px; font-size: 13px; line-height: 1.7; }
    @media (min-width:768px) { .lp-foot { padding: 48px 80px 28px; } }
    .lp-foot p { margin: 0; }
    .lp-foot-main { display:flex; flex-direction:column; align-items:center; gap:28px; margin-bottom:32px; }
    @media (min-width:768px) { .lp-foot-main { flex-direction:row; align-items:flex-start; gap:48px; } }
    .lp-foot-logo-wrap { display:flex; flex-direction:column; align-items:center; gap:10px; }
    @media (min-width:768px) { .lp-foot-logo-wrap { align-items:flex-start; min-width:180px; } }
    .lp-foot-logo { height:60px; width:auto; display:block; object-fit:contain; filter:brightness(0) invert(1); }
    .lp-foot-brand { font-family:'Bodoni Moda',Georgia,serif; font-size:15px; font-weight:600; color:#fff; letter-spacing:.02em; line-height:1.3; text-align:center; }
    @media (min-width:768px) { .lp-foot-brand { text-align:left; } }
    .lp-foot-contact { display:flex; flex-direction:column; gap:12px; flex:1; }
    .lp-foot-contact-row { display:flex; align-items:flex-start; gap:10px; color:rgba(255,255,255,.75); font-size:13px; line-height:1.5; }
    .lp-foot-contact-row a { color:rgba(255,255,255,.75); text-decoration:none; transition:color .2s; }
    .lp-foot-contact-row a:hover { color:#fff; }
    .lp-foot-contact-row .foot-icon { color:#fe5e1e; font-size:16px; margin-top:1px; flex-shrink:0; }
    .lp-foot-divider { border:0; border-top:1px solid rgba(255,255,255,.1); margin:0 0 16px; }
    .lp-foot-bottom { display:flex; flex-direction:column; align-items:center; gap:4px; text-align:center; }
    .lp-foot-copy { color:rgba(255,255,255,.6); font-size:13px; }
    .lp-foot-dev { color:rgba(255,255,255,.35); font-size:12px; }
    .lp-foot-dev a { color:rgba(255,255,255,.45); text-decoration:none; }
    .lp-foot-dev a:hover { color:rgba(255,255,255,.8); }

    /* FLOATING BUTTONS */
    .lp-float-wa, .lp-float-call {
      position:fixed; width:56px; height:56px; border-radius:50%;
      display:flex; align-items:center; justify-content:center;
      color:#fff; font-size:24px; text-decoration:none;
      box-shadow:0 10px 24px rgba(0,0,0,.2); z-index:999;
      transition:all .25s ease;
    }
    .lp-float-wa  { background:#25d366; bottom:28px; left:28px; }
    .lp-float-wa:hover  { background:#1ebe5d; transform:scale(1.08); }
    .lp-float-call { background:#fe5e1e; bottom:96px; left:28px; }
    .lp-float-call:hover { background:#ab3600; transform:scale(1.08); }
  </style>

  <?= render_header_codes('head') ?>
</head>
<body>
<?= render_header_codes('body_start') ?>

<!-- HEADER -->
<header class="lp-topbar" role="banner">
  <div class="lp-topbar-logo" aria-label="New Image International Beauty Institute">
    <img src="<?= htmlspecialchars($logo_src, ENT_QUOTES) ?>" alt="NIIB Institute Logo" onerror="this.style.display='none';this.nextElementSibling.style.display='inline'">
    <span style="display:none">NIIB Institute</span>
  </div>
  <a href="#lead-form" class="lp-topbar-cta">Admission Open</a>
</header>


<div id="loc-template">

  <section class="lp-hero">
    <div class="lp-hero-bg">
      <img src="https://newimageinstitute.com/uploads/media/f_6a423350c21d0_1782723408.webp" alt="NIIB Phagwara Campus">
    </div>
    <div class="lp-container lp-hero-inner">
      <div class="lp-hero-grid">

        <div class="lp-hero-content">
          <span class="lp-hero-badge">Premium Beauty Education</span>
          <h1 class="lp-hero-title">Best Beauty Institute in Phagwara</h1>
          <div class="lp-hero-ctas">
            <a class="lp-btn lp-btn-primary" href="#lead-form">Apply Now</a>
            <a class="lp-btn lp-btn-outline" href="#lead-form">Book Free Consultation</a>
          </div>
        </div>

        <div class="lp-hero-formwrap">
          <div class="lp-hero-form" id="lead-form">
            <p class="lp-hero-form-title">Book a Free Consultation</p>
            <p class="lp-hero-form-sub">Share your details and our counsellor will call you back.</p>
            <div id="crm-form-container-hero"></div>
            <script>
            (function(){
              var cid = 'crm-form-container-hero';
              function load(){
                var c = document.getElementById(cid);
                if (!c || c.dataset.loaded) return; c.dataset.loaded = '1';
                var f = document.createElement('iframe');
                f.src = 'https://crm.mediagarh.com/CRM/forms/public/50?embed=1&landing_url=' + encodeURIComponent(window.location.href);
                f.width = '100%'; f.height = '420'; f.frameBorder = '0';
                f.style.border = '0'; f.style.background = 'transparent';
                c.appendChild(f);
              }
              if ('IntersectionObserver' in window) {
                var el = document.getElementById(cid);
                if (el) { var io = new IntersectionObserver(function(e){ if(e[0].isIntersecting){io.disconnect();load();} },{rootMargin:'400px'}); io.observe(el); }
              } else { load(); }
            })();
            </script>
          </div>
        </div>

      </div>
    </div>
  </section>

  <section class="lp-section">
    <div class="lp-container">
      <div class="lp-intro-grid">
        <div>
          <h2 class="lp-h2">Excellence in Aesthetic Education &mdash; Phagwara</h2>
          <p class="lp-lede">Welcome to NIIB Institute's Phagwara center, part of India's premier network of beauty academies. We're committed to delivering world-class aesthetic and beauty education.</p>
          <p class="lp-lede">With state-of-the-art facilities and experienced mentors, we prepare beauty professionals for global careers.</p>
          <div class="lp-stats">
            <div class="lp-stat">
              <span class="lp-stat-value">22+</span>
              <span class="lp-stat-label">Years Legacy</span>
            </div>
            <div class="lp-stat">
              <span class="lp-stat-value">25k+</span>
              <span class="lp-stat-label">Students Trained</span>
            </div>
          </div>
        </div>
        <div class="lp-intro-image-wrap">
          <div class="lp-intro-image">
            <img src="https://newimageinstitute.com/uploads/media/f_6a43ab9c97263_1782819740.jpg" alt="NIIB Phagwara Classroom">
          </div>
          <div class="lp-intro-quote">"NIIB elevates beauty career standards across India."</div>
        </div>
      </div>
    </div>
  </section>

  <section class="lp-section">
    <div class="lp-container">
      <div class="lp-center-head">
        <span class="lp-tagline">Design Your Career Path</span>
        <h2 class="lp-h2">Explore Your Career Opportunities</h2>
        <p>Turn your passion into a successful profession with NIIB. Our industry-focused beauty courses prepare you for high-demand careers in the beauty, wellness, and aesthetics industry.</p>
      </div>
      <div class="lp-careers2-grid">

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">brush</span>
          <h3>Professional Makeup Artist</h3>
          <p>Work with bridal clients, fashion shoots, celebrities, and events.</p>
        </div>

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">favorite</span>
          <h3>Bridal Makeup Specialist</h3>
          <p>Become an expert in bridal makeup and wedding transformations.</p>
        </div>

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">content_cut</span>
          <h3>Hair Stylist &amp; Hair Designer</h3>
          <p>Build a career in premium salons or become an independent hairstylist.</p>
        </div>

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">spa</span>
          <h3>Cosmetologist</h3>
          <p>Provide professional skincare, hair, and beauty treatments.</p>
        </div>

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">colorize</span>
          <h3>Nail Artist &amp; Nail Technician</h3>
          <p>Master nail extensions, nail art, gel nails, and salon services.</p>
        </div>

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">self_care</span>
          <h3>Skin &amp; Beauty Therapist</h3>
          <p>Offer advanced facial treatments, skincare therapies, and wellness services.</p>
        </div>

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">school</span>
          <h3>Beauty Trainer / Educator</h3>
          <p>Teach aspiring beauty professionals at institutes and academies.</p>
        </div>

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">storefront</span>
          <h3>Salon Manager</h3>
          <p>Manage salon operations, customer experience, and business growth.</p>
        </div>

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">diversity_3</span>
          <h3>Beauty Consultant</h3>
          <p>Work with premium beauty brands and cosmetic companies.</p>
        </div>

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">photo_camera</span>
          <h3>Beauty Influencer / Content Creator</h3>
          <p>Build your personal brand through social media and collaborations.</p>
        </div>

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">work</span>
          <h3>Freelance Beauty Professional</h3>
          <p>Work independently with flexible timings and higher earning potential.</p>
        </div>

        <div class="lp-career2-card">
          <span class="material-symbols-outlined">store</span>
          <h3>Salon &amp; Beauty Studio Owner</h3>
          <p>Start and grow your own successful beauty business.</p>
        </div>

      </div>
    </div>
  </section>

  <section class="lp-section lp-ach">
    <div class="lp-container">
      <div class="lp-center-head">
        <h2 class="lp-h2">Achievements &amp; Recognition</h2>
        <p>At NIIB, excellence is reflected through industry recognition, student success, and years of dedicated beauty education.</p>
      </div>
      <div class="lp-ach-grid">

        <div class="lp-ach-card">
          <span class="material-symbols-outlined">workspace_premium</span>
          <span class="lp-ach-val">22+</span>
          <h3>Years of Excellence</h3>
          <p>Delivering professional beauty education since 2004.</p>
        </div>

        <div class="lp-ach-card">
          <span class="material-symbols-outlined">groups</span>
          <span class="lp-ach-val">50,000+</span>
          <h3>Successful Alumni</h3>
          <p>Helping students build rewarding careers across India and abroad.</p>
        </div>

        <div class="lp-ach-card">
          <span class="material-symbols-outlined">apartment</span>
          <span class="lp-ach-val">35+</span>
          <h3>Modern Campuses</h3>
          <p>World-class training infrastructure across multiple locations.</p>
        </div>

        <div class="lp-ach-card">
          <span class="material-symbols-outlined">handshake</span>
          <span class="lp-ach-val">100%</span>
          <h3>Placement Support</h3>
          <p>Career guidance, interview preparation, and placement assistance.</p>
        </div>

        <div class="lp-ach-card">
          <span class="material-symbols-outlined">public</span>
          <h3>International Curriculum</h3>
          <p>Industry-oriented education with advanced practical training.</p>
        </div>

        <div class="lp-ach-card">
          <span class="material-symbols-outlined">diversity_3</span>
          <h3>Expert Faculty</h3>
          <p>Learn from experienced beauty professionals and certified trainers.</p>
        </div>

        <div class="lp-ach-card">
          <span class="material-symbols-outlined">back_hand</span>
          <h3>Hands-on Practical Learning</h3>
          <p>Real salon exposure with live demonstrations and practical sessions.</p>
        </div>

        <div class="lp-ach-card">
          <span class="material-symbols-outlined">verified</span>
          <h3>Trusted Beauty Education Brand</h3>
          <p>One of India's leading institutes in beauty, cosmetology, aesthetics &amp; wellness.</p>
        </div>

      </div>
    </div>
  </section>

  <section class="lp-section lp-awards">
    <div class="lp-container">
      <div class="lp-center-head">
        <span class="lp-tagline">Award &amp; Events</span>
        <h2 class="lp-h2">Celebrated for Excellence</h2>
        <p>Recognised on national and international platforms &mdash; honoured by leading personalities of the industry.</p>
      </div>
      <div class="lp-awards-grid">

        <div class="lp-award-card">
          <img data-lb-group="awards" data-lb-index="0" src="https://newimageinstitute.com/assets/images/awards/award1.jpg" alt="NIIB Award" loading="lazy" class="lp-lightbox-img">
        </div>

        <div class="lp-award-card">
          <img data-lb-group="awards" data-lb-index="1" src="https://newimageinstitute.com/assets/images/awards/award2.jpg" alt="NIIB Award" loading="lazy" class="lp-lightbox-img">
        </div>

        <div class="lp-award-card">
          <img data-lb-group="awards" data-lb-index="2" src="https://newimageinstitute.com/assets/images/awards/award3.jpg" alt="NIIB Award" loading="lazy" class="lp-lightbox-img">
        </div>

        <div class="lp-award-card">
          <img data-lb-group="awards" data-lb-index="3" src="https://newimageinstitute.com/assets/images/awards/award4.jpg" alt="NIIB Award" loading="lazy" class="lp-lightbox-img">
        </div>

        <div class="lp-award-card">
          <img data-lb-group="awards" data-lb-index="4" src="https://newimageinstitute.com/assets/images/awards/award5.jpg" alt="NIIB Award" loading="lazy" class="lp-lightbox-img">
        </div>

        <div class="lp-award-card">
          <img data-lb-group="awards" data-lb-index="5" src="https://newimageinstitute.com/assets/images/awards/award6.jpg" alt="NIIB Award" loading="lazy" class="lp-lightbox-img">
        </div>

      </div>
    </div>
  </section>

  <section class="lp-section">
    <div class="lp-container">
      <div class="lp-center-head">
        <h2 class="lp-h2">Why Choose NIIB?</h2>
        <p>NIIB is committed to delivering world-class beauty education through practical training, expert mentorship, and career-focused learning.</p>
      </div>
      <div class="lp-why-grid">

        <div class="lp-why-card">
          <div class="lp-why-ico"><span class="material-symbols-outlined">menu_book</span></div>
          <h3>Industry-Oriented Curriculum</h3>
          <p>Learn the latest beauty, cosmetology, hair, skin, makeup, and aesthetics techniques.</p>
        </div>

        <div class="lp-why-card">
          <div class="lp-why-ico"><span class="material-symbols-outlined">school</span></div>
          <h3>Experienced Faculty</h3>
          <p>Get trained by certified professionals with years of industry experience.</p>
        </div>

        <div class="lp-why-card">
          <div class="lp-why-ico"><span class="material-symbols-outlined">handshake</span></div>
          <h3>100% Placement Support</h3>
          <p>Receive career guidance, interview preparation, and placement assistance.</p>
        </div>

        <div class="lp-why-card">
          <div class="lp-why-ico"><span class="material-symbols-outlined">back_hand</span></div>
          <h3>Practical Hands-on Training</h3>
          <p>Gain real salon experience with live models and professional equipment.</p>
        </div>

        <div class="lp-why-card">
          <div class="lp-why-ico"><span class="material-symbols-outlined">apartment</span></div>
          <h3>Modern Infrastructure</h3>
          <p>Train in fully equipped classrooms, advanced beauty labs, and salon environments.</p>
        </div>

        <div class="lp-why-card">
          <div class="lp-why-ico"><span class="material-symbols-outlined">public</span></div>
          <h3>International Standard Education</h3>
          <p>Develop professional skills that meet global beauty industry standards.</p>
        </div>

      </div>
    </div>
  </section>

  <section class="lp-section lp-gallery">
    <div class="lp-container">
      <div class="lp-section-head">
        <div>
          <h2 class="lp-h2">Recognized Excellence</h2>
          <p class="lp-gallery-sub">State-of-the-art facilities and training</p>
        </div>
      </div>
      <div class="lp-gallery-grid">

        <div class="lp-gallery-item">
          <img src="https://newimageinstitute.com/uploads/media/f_6a43a1377704f_1782817079.jpg" alt="NIIB Phagwara Campus" loading="lazy">
        </div>

        <div class="lp-gallery-item">
          <img src="https://newimageinstitute.com/uploads/media/f_6a439de5009a2_1782816229.jpg" alt="NIIB Phagwara Campus" loading="lazy">
        </div>

        <div class="lp-gallery-item">
          <img src="https://newimageinstitute.com/uploads/media/f_6a439de4f3a9e_1782816228.jpg" alt="NIIB Phagwara Campus" loading="lazy">
        </div>

        <div class="lp-gallery-item">
          <img src="https://newimageinstitute.com/uploads/media/f_6a43912ea707d_1782812974.jpg" alt="NIIB Phagwara Campus" loading="lazy">
        </div>

        <div class="lp-gallery-item">
          <img src="https://newimageinstitute.com/uploads/media/f_6a423350c03d7_1782723408.webp" alt="NIIB Phagwara Campus" loading="lazy">
        </div>

        <div class="lp-gallery-item">
          <img src="https://newimageinstitute.com/uploads/courses/f_6a3e28d16c026_1782458577.png" alt="NIIB Phagwara Campus" loading="lazy">
        </div>

      </div>
    </div>
  </section>

  <section class="lp-section">
    <div class="lp-container">
      <div class="lp-section-head">
        <div>
          <span class="lp-tagline">In Their Words</span>
          <h2 class="lp-h2">Student Testimonials</h2>
        </div>
        <div class="lp-carousel-nav">
          <button type="button" class="lp-arrow lp-imgtst-prev" aria-label="Previous"><span class="material-symbols-outlined">chevron_left</span></button>
          <button type="button" class="lp-arrow lp-imgtst-next" aria-label="Next"><span class="material-symbols-outlined">chevron_right</span></button>
        </div>
      </div>
      <div class="lp-imgtst-track">

        <div class="lp-imgtst-card">
          <img src="https://newimageinstitute.com/assets/images/testimonials/t1.jpg" alt="NIIB Student Testimonial" loading="lazy">
        </div>

        <div class="lp-imgtst-card">
          <img src="https://newimageinstitute.com/assets/images/testimonials/t2.jpg" alt="NIIB Student Testimonial" loading="lazy">
        </div>

        <div class="lp-imgtst-card">
          <img src="https://newimageinstitute.com/assets/images/testimonials/t3.jpg" alt="NIIB Student Testimonial" loading="lazy">
        </div>

        <div class="lp-imgtst-card">
          <img src="https://newimageinstitute.com/assets/images/testimonials/t4.jpg" alt="NIIB Student Testimonial" loading="lazy">
        </div>

        <div class="lp-imgtst-card">
          <img src="https://newimageinstitute.com/assets/images/testimonials/t5.jpg" alt="NIIB Student Testimonial" loading="lazy">
        </div>

      </div>
    </div>
  </section>

  <section class="lp-section lp-vid">
    <div class="lp-container">
      <div class="lp-section-head">
        <div>
          <span class="lp-tagline" style="color:var(--lp-secondary-fixed)">Watch Their Stories</span>
          <h2 class="lp-h2">Video Testimonials</h2>
        </div>
        <div class="lp-carousel-nav">
          <button type="button" class="lp-arrow lp-vid-prev" aria-label="Previous"><span class="material-symbols-outlined">chevron_left</span></button>
          <button type="button" class="lp-arrow lp-vid-next" aria-label="Next"><span class="material-symbols-outlined">chevron_right</span></button>
        </div>
      </div>
      <div class="lp-vid-track">

        <div class="lp-vid-card">
          <video controls preload="metadata" playsinline>
            <source src="https://newimageinstitute.com/assets/videos/video1.mp4" type="video/mp4">
          </video>
          <div class="lp-vid-cap">Simmi Talwar — From Student to Teacher at NIIB</div>
        </div>

        <div class="lp-vid-card">
          <video controls preload="metadata" playsinline>
            <source src="https://newimageinstitute.com/assets/videos/video2.mp4" type="video/mp4">
          </video>
          <div class="lp-vid-cap">Twinkle — Free Beauty Course under the Govt Scheme</div>
        </div>

        <div class="lp-vid-card">
          <video controls preload="metadata" playsinline>
            <source src="https://newimageinstitute.com/assets/videos/video3.mp4" type="video/mp4">
          </video>
          <div class="lp-vid-cap">Nancy — The Impact of NIIB Training</div>
        </div>

        <div class="lp-vid-card">
          <video controls preload="metadata" playsinline>
            <source src="https://newimageinstitute.com/assets/videos/video4.mp4" type="video/mp4">
          </video>
          <div class="lp-vid-cap">Harpreet — Placed as a Skin Therapist</div>
        </div>

        <div class="lp-vid-card">
          <video controls preload="metadata" playsinline>
            <source src="https://newimageinstitute.com/assets/videos/video5.mp4" type="video/mp4">
          </video>
          <div class="lp-vid-cap">Jasmeet Kaur — A Proud NIIB Success Story</div>
        </div>

      </div>
    </div>
  </section>

  <section class="lp-accred">
    <div class="lp-container">
      <span class="lp-accred-label">Globally Recognized Accreditations</span>
      <div class="lp-accred-list">

        <img class="lp-accred-logo" src="https://newimageinstitute.com/uploads/accreditations/nsdc.jpg" alt="NSDC" title="NSDC" loading="lazy">

        <img class="lp-accred-logo" src="https://newimageinstitute.com/uploads/accreditations/bwssc.png" alt="B&WSSC" title="B&WSSC" loading="lazy">

        <img class="lp-accred-logo" src="https://newimageinstitute.com/uploads/accreditations/iao-new.jpg" alt="IAO" title="IAO" loading="lazy">

        <img class="lp-accred-logo" src="https://newimageinstitute.com/uploads/accreditations/pathway-to-success.jpg" alt="Pathway to Success" title="Pathway to Success" loading="lazy">

      </div>
    </div>
  </section>

</div><!-- /#loc-template -->


<!-- LIGHTBOX -->
<div id="loc-template-lightbox" role="dialog" aria-modal="true">
  <button type="button" class="lb-btn lb-close" aria-label="Close"><span class="material-symbols-outlined" style="font-size:34px">close</span></button>
  <button type="button" class="lb-btn lb-prev" aria-label="Previous"><span class="material-symbols-outlined" style="font-size:32px">chevron_left</span></button>
  <button type="button" class="lb-btn lb-next" aria-label="Next"><span class="material-symbols-outlined" style="font-size:32px">chevron_right</span></button>
  <img alt="">
  <div class="lb-counter"></div>
</div>


<!-- FOOTER -->
<footer class="lp-foot" role="contentinfo">
  <div class="lp-foot-main">
    <div class="lp-foot-logo-wrap">
      <img class="lp-foot-logo"
           src="<?= htmlspecialchars($logo_src, ENT_QUOTES) ?>"
           alt="New Image International Beauty Institute Logo"
           onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
      <span style="display:none" class="lp-foot-brand">NIIB Institute</span>
      <span class="lp-foot-brand" style="margin-top:4px; max-width:400px; font-size:12px;">Founded in 2004, New Image International Beauty Institute (NIIB) is one of India's most prestigious beauty academies — delivering world-class education in cosmetology, makeup, hair, aesthetics, and wellness across 25+ campuses.</span>
    </div>
    <div class="lp-foot-contact">
      <?php
        try {
          $f_email = setting('site_email');
        } catch (Throwable $e) {
          $f_email = '';
        }
      ?>
      <div class="lp-foot-contact-row">
        <i class="bi bi-geo-alt-fill foot-icon"></i>
        <span>Grand Trunk Rd, opposite Hanuman Gari Mandir, above Punjab and Sind Bank, Sondhi Chowk, Phagwara, Punjab 144401</span>
      </div>
      <?php if (!empty($f_email)): ?>
      <div class="lp-foot-contact-row">
        <i class="bi bi-envelope-fill foot-icon"></i>
        <a href="mailto:<?= htmlspecialchars($f_email, ENT_QUOTES) ?>"><?= htmlspecialchars($f_email, ENT_QUOTES) ?></a>
      </div>
      <?php endif; ?>
      <div class="lp-foot-contact-row">
        <i class="bi bi-telephone-fill foot-icon"></i>
        <a href="tel:+919780055576">+91 97800 55576</a>
      </div>
    </div>
  </div>
  <hr class="lp-foot-divider">
  <div class="lp-foot-bottom">
    <p class="lp-foot-copy">&copy; 2026 New Image International Beauty Institute. All rights reserved.</p>
    <p class="lp-foot-dev">Design &amp; Developed by Mediagarh</p>
  </div>
</footer>


<!-- Floating WhatsApp Button -->
<a href="https://wa.me/919780055576" class="lp-float-wa" target="_blank" rel="noopener" aria-label="WhatsApp us">
  <i class="bi bi-whatsapp"></i>
</a>
<!-- Floating Call Button -->
<a href="tel:+919780055576" class="lp-float-call" aria-label="Call us">
  <i class="bi bi-telephone-fill"></i>
</a>


<script>
(function(){

  /* Testimonials slider */
  var tstTrack = document.querySelector('#loc-template .lp-tst-track');
  if (tstTrack) {
    function tstStep(){ var c=tstTrack.querySelector('.lp-tst-card'); if(!c) return tstTrack.clientWidth; var g=parseInt(getComputedStyle(tstTrack).columnGap||getComputedStyle(tstTrack).gap||'0',10)||0; return c.getBoundingClientRect().width+g; }
    document.querySelectorAll('#loc-template .lp-tst-prev').forEach(function(b){ b.addEventListener('click',function(){ tstTrack.scrollLeft<=4?tstTrack.scrollTo({left:tstTrack.scrollWidth,behavior:'smooth'}):tstTrack.scrollBy({left:-tstStep(),behavior:'smooth'}); }); });
    document.querySelectorAll('#loc-template .lp-tst-next').forEach(function(b){ b.addEventListener('click',function(){ tstTrack.scrollLeft+tstTrack.clientWidth>=tstTrack.scrollWidth-4?tstTrack.scrollTo({left:0,behavior:'smooth'}):tstTrack.scrollBy({left:tstStep(),behavior:'smooth'}); }); });
  }

  /* Image testimonials slider */
  var imgTrack = document.querySelector('#loc-template .lp-imgtst-track');
  if (imgTrack) {
    function imgStep(){ var c=imgTrack.querySelector('.lp-imgtst-card'); if(!c) return imgTrack.clientWidth; var g=parseInt(getComputedStyle(imgTrack).columnGap||getComputedStyle(imgTrack).gap||'0',10)||0; return c.getBoundingClientRect().width+g; }
    document.querySelectorAll('#loc-template .lp-imgtst-prev').forEach(function(b){ b.addEventListener('click',function(){ imgTrack.scrollLeft<=4?imgTrack.scrollTo({left:imgTrack.scrollWidth,behavior:'smooth'}):imgTrack.scrollBy({left:-imgStep(),behavior:'smooth'}); }); });
    document.querySelectorAll('#loc-template .lp-imgtst-next').forEach(function(b){ b.addEventListener('click',function(){ imgTrack.scrollLeft+imgTrack.clientWidth>=imgTrack.scrollWidth-4?imgTrack.scrollTo({left:0,behavior:'smooth'}):imgTrack.scrollBy({left:imgStep(),behavior:'smooth'}); }); });
  }

  /* Video testimonials slider */
  var vidTrack = document.querySelector('#loc-template .lp-vid-track');
  if (vidTrack) {
    function vidStep(){ var c=vidTrack.querySelector('.lp-vid-card'); if(!c) return vidTrack.clientWidth; var g=parseInt(getComputedStyle(vidTrack).columnGap||getComputedStyle(vidTrack).gap||'0',10)||0; return c.getBoundingClientRect().width+g; }
    document.querySelectorAll('#loc-template .lp-vid-prev').forEach(function(b){ b.addEventListener('click',function(){ vidTrack.scrollLeft<=4?vidTrack.scrollTo({left:vidTrack.scrollWidth,behavior:'smooth'}):vidTrack.scrollBy({left:-vidStep(),behavior:'smooth'}); }); });
    document.querySelectorAll('#loc-template .lp-vid-next').forEach(function(b){ b.addEventListener('click',function(){ vidTrack.scrollLeft+vidTrack.clientWidth>=vidTrack.scrollWidth-4?vidTrack.scrollTo({left:0,behavior:'smooth'}):vidTrack.scrollBy({left:vidStep(),behavior:'smooth'}); }); });
  }

  /* Lightbox */
  var lbGroups = {
    awards: [
      'https://newimageinstitute.com/assets/images/awards/award1.jpg',
      'https://newimageinstitute.com/assets/images/awards/award2.jpg',
      'https://newimageinstitute.com/assets/images/awards/award3.jpg',
      'https://newimageinstitute.com/assets/images/awards/award4.jpg',
      'https://newimageinstitute.com/assets/images/awards/award5.jpg',
      'https://newimageinstitute.com/assets/images/awards/award6.jpg'
    ]
  };
  var box = document.getElementById('loc-template-lightbox');
  if (box) {
    var lbImg=box.querySelector('img'), counter=box.querySelector('.lb-counter'), images=[], idx=0;
    function show(i){ if(!images.length) return; idx=(i+images.length)%images.length; lbImg.src=images[idx]; counter.textContent=(idx+1)+' / '+images.length; box.classList.add('is-open'); document.body.style.overflow='hidden'; }
    function close(){ box.classList.remove('is-open'); document.body.style.overflow=''; }
    document.querySelectorAll('#loc-template .lp-lightbox-img').forEach(function(el){ el.addEventListener('click',function(){ images=lbGroups[el.getAttribute('data-lb-group')||'awards']||[]; show(parseInt(el.dataset.lbIndex,10)||0); }); });
    box.querySelector('.lb-close').addEventListener('click',close);
    box.querySelector('.lb-prev').addEventListener('click',function(e){ e.stopPropagation(); show(idx-1); });
    box.querySelector('.lb-next').addEventListener('click',function(e){ e.stopPropagation(); show(idx+1); });
    box.addEventListener('click',function(e){ if(e.target===box) close(); });
    document.addEventListener('keydown',function(e){ if(!box.classList.contains('is-open')) return; if(e.key==='Escape') close(); else if(e.key==='ArrowLeft') show(idx-1); else if(e.key==='ArrowRight') show(idx+1); });
  }

})();
</script>

</body>
</html>

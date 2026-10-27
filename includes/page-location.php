<?php
/**
 * Location Page Template (body partial)
 * Rendered inside the site's default header.php / footer.php.
 * Uses scoped CSS under #loc-template — no Tailwind, no conflicts with the theme.
 * Expects: $p (page row), $td (decoded template_data), $page_seo.
 */
if (!defined('NIIB_INIT')) { http_response_code(403); exit; }

// Safe defaults
$td = array_replace_recursive([
    'hero'    => ['badge'=>'','title'=>'','cta1'=>'','cta1_url'=>'#','cta2'=>'','cta2_url'=>'#'],
    'intro'   => ['heading'=>'','para1'=>'','para2'=>'','stat1_value'=>'','stat1_label'=>'','stat2_value'=>'','stat2_label'=>'','image'=>'','quote'=>''],
    'courses' => ['tagline'=>'','heading'=>'','course_ids'=>[]],
    'career'  => ['heading'=>'','subheading'=>'','feature_icon'=>'brush','feature_title'=>'','feature_desc'=>'','cards'=>[]],
    'gallery' => ['heading'=>'','subheading'=>'','images'=>[]],
    'accreditations' => [],
    'contact' => ['heading'=>'','subheading'=>'','phone'=>'','email'=>'','address'=>'','cities'=>[]],
], is_array($td ?? null) ? $td : []);

// Resolve selected courses (live from courses table)
$selected_courses = [];
$cids = array_values(array_filter(array_map('intval', (array)($td['courses']['course_ids'] ?? []))));
if (!empty($cids)) {
    $place = implode(',', array_fill(0, count($cids), '?'));
    $stmt = db()->prepare("SELECT c.id, c.title, c.slug, c.image, cc.name AS category_name FROM courses c LEFT JOIN course_categories cc ON cc.id=c.category_id WHERE c.status=1 AND c.id IN ($place)");
    $stmt->execute($cids);
    $byId = [];
    foreach ($stmt->fetchAll() as $row) $byId[(int)$row['id']] = $row;
    foreach ($cids as $cid) if (isset($byId[$cid])) $selected_courses[] = $byId[$cid];
}

$hero_img = !empty($p['banner_image']) ? img_url($p['banner_image']) : img_url('assets/images/og-image.svg');

/* ---------------------------------------------------------------------------
 * Shared "career / achievements / awards / why-choose / testimonials" sections.
 * Content is identical across every location, so it lives here (not per-page).
 * Rendered only when the page opts in via template_data.extra_sections = true,
 * which lets us roll it out one location at a time.
 * ------------------------------------------------------------------------- */
$show_extra = !empty($td['extra_sections']);

$career_paths = [
    ['icon'=>'brush',          'title'=>'Professional Makeup Artist',        'desc'=>'Work with bridal clients, fashion shoots, celebrities, and events.'],
    ['icon'=>'favorite',       'title'=>'Bridal Makeup Specialist',          'desc'=>'Become an expert in bridal makeup and wedding transformations.'],
    ['icon'=>'content_cut',    'title'=>'Hair Stylist & Hair Designer',      'desc'=>'Build a career in premium salons or become an independent hairstylist.'],
    ['icon'=>'spa',            'title'=>'Cosmetologist',                     'desc'=>'Provide professional skincare, hair, and beauty treatments.'],
    ['icon'=>'colorize',       'title'=>'Nail Artist & Nail Technician',     'desc'=>'Master nail extensions, nail art, gel nails, and salon services.'],
    ['icon'=>'self_care',      'title'=>'Skin & Beauty Therapist',           'desc'=>'Offer advanced facial treatments, skincare therapies, and wellness services.'],
    ['icon'=>'school',         'title'=>'Beauty Trainer / Educator',         'desc'=>'Teach aspiring beauty professionals at institutes and academies.'],
    ['icon'=>'storefront',     'title'=>'Salon Manager',                     'desc'=>'Manage salon operations, customer experience, and business growth.'],
    ['icon'=>'diversity_3',    'title'=>'Beauty Consultant',                 'desc'=>'Work with premium beauty brands and cosmetic companies.'],
    ['icon'=>'photo_camera',   'title'=>'Beauty Influencer / Content Creator','desc'=>'Build your personal brand through social media and collaborations.'],
    ['icon'=>'work',           'title'=>'Freelance Beauty Professional',     'desc'=>'Work independently with flexible timings and higher earning potential.'],
    ['icon'=>'store',          'title'=>'Salon & Beauty Studio Owner',       'desc'=>'Start and grow your own successful beauty business.'],
];

$achievements = [
    ['value'=>'22+',     'title'=>'Years of Excellence',          'desc'=>'Delivering professional beauty education since 2004.',                          'icon'=>'workspace_premium'],
    ['value'=>'50,000+', 'title'=>'Successful Alumni',            'desc'=>'Helping students build rewarding careers across India and abroad.',             'icon'=>'groups'],
    ['value'=>'35+',     'title'=>'Modern Campuses',             'desc'=>'World-class training infrastructure across multiple locations.',                'icon'=>'apartment'],
    ['value'=>'100%',    'title'=>'Placement Support',           'desc'=>'Career guidance, interview preparation, and placement assistance.',             'icon'=>'handshake'],
    ['value'=>'',        'title'=>'International Curriculum',      'desc'=>'Industry-oriented education with advanced practical training.',                 'icon'=>'public'],
    ['value'=>'',        'title'=>'Expert Faculty',               'desc'=>'Learn from experienced beauty professionals and certified trainers.',          'icon'=>'diversity_3'],
    ['value'=>'',        'title'=>'Hands-on Practical Learning',  'desc'=>'Real salon exposure with live demonstrations and practical sessions.',          'icon'=>'back_hand'],
    ['value'=>'',        'title'=>'Trusted Beauty Education Brand','desc'=>'One of India\'s leading institutes in beauty, cosmetology, aesthetics & wellness.','icon'=>'verified'],
];

$why_choose = [
    ['icon'=>'menu_book',  'title'=>'Industry-Oriented Curriculum',    'desc'=>'Learn the latest beauty, cosmetology, hair, skin, makeup, and aesthetics techniques.'],
    ['icon'=>'school',     'title'=>'Experienced Faculty',            'desc'=>'Get trained by certified professionals with years of industry experience.'],
    ['icon'=>'handshake',  'title'=>'100% Placement Support',         'desc'=>'Receive career guidance, interview preparation, and placement assistance.'],
    ['icon'=>'back_hand',  'title'=>'Practical Hands-on Training',     'desc'=>'Gain real salon experience with live models and professional equipment.'],
    ['icon'=>'apartment',  'title'=>'Modern Infrastructure',          'desc'=>'Train in fully equipped classrooms, advanced beauty labs, and salon environments.'],
    ['icon'=>'public',     'title'=>'International Standard Education', 'desc'=>'Develop professional skills that meet global beauty industry standards.'],
];

$loc_testimonials = [
    ['name'=>'Priya Sharma',   'text'=>'Joining NIIB was the best decision of my career. The practical training and expert guidance helped me become a confident professional makeup artist. Today, I work with bridal clients and earn independently.'],
    ['name'=>'Simran Kaur',    'text'=>'The trainers at NIIB are incredibly supportive and knowledgeable. Every session was practical, and I learned industry techniques that boosted my confidence.'],
    ['name'=>'Muskan Verma',   'text'=>'I started with zero experience, but NIIB transformed my passion into a profession. Their placement support helped me begin my career immediately after completing my course.'],
    ['name'=>'Neha Arora',     'text'=>'The hands-on training and modern infrastructure made learning enjoyable. I highly recommend NIIB to anyone serious about building a career in the beauty industry.'],
    ['name'=>'Amandeep Kaur',  'text'=>'From makeup to skincare and hairstyling, every module was well-structured. The faculty encouraged me throughout my journey, and today I\'m successfully working as a freelance beauty professional.'],
    ['name'=>'Riya Malhotra',  'text'=>'NIIB gave me the skills, confidence, and industry exposure I needed to start my own beauty studio. I\'m grateful for the knowledge and mentorship I received.'],
];

$award_images = [
    'assets/images/awards/award1.jpg','assets/images/awards/award2.jpg','assets/images/awards/award3.jpg',
    'assets/images/awards/award4.jpg','assets/images/awards/award5.jpg','assets/images/awards/award6.jpg',
];

/* Lightbox image sets — gallery and awards cycle independently. */
$lb_groups = [];
if (!empty($td['gallery']['images'])) {
    $lb_groups['gallery'] = array_values(array_map(function($g){ return img_url($g); }, $td['gallery']['images']));
}
if ($show_extra && !empty($award_images)) {
    $lb_groups['awards'] = array_values(array_map(function($a){ return img_url($a); }, $award_images));
}

$image_testimonials = [
    'assets/images/testimonials/t1.jpg','assets/images/testimonials/t2.jpg','assets/images/testimonials/t3.jpg',
    'assets/images/testimonials/t4.jpg','assets/images/testimonials/t5.jpg',
];

$video_testimonials = [
    ['src'=>'assets/videos/video1.mp4','title'=>'Simmi Talwar — From Student to Teacher at NIIB'],
    ['src'=>'assets/videos/video2.mp4','title'=>'Twinkle — Free Beauty Course under the Govt Scheme'],
    ['src'=>'assets/videos/video3.mp4','title'=>'Nancy — The Impact of NIIB Training'],
    ['src'=>'assets/videos/video4.mp4','title'=>'Harpreet — Placed as a Skin Therapist'],
    ['src'=>'assets/videos/video5.mp4','title'=>'Jasmeet Kaur — A Proud NIIB Success Story'],
];
?>

<?php /* Bodoni Moda already loads non-blocking from header.php — no duplicate needed here. */?>
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet"></noscript>

<?php include __DIR__ . '/loc-styles.php'; ?>

<div id="loc-template">

  <!-- HERO -->
  <section class="lp-hero">
    <div class="lp-hero-bg"><img src="<?= e($hero_img) ?>" alt=""></div>
    <div class="lp-container lp-hero-inner">
      <div class="lp-hero-grid">
        <div class="lp-hero-content">
          <?php if (!empty($td['hero']['badge'])): ?>
            <span class="lp-hero-badge"><?= e($td['hero']['badge']) ?></span>
          <?php endif; ?>
          <h1 class="lp-hero-title"><?= e($td['hero']['title'] ?: $p['title']) ?></h1>
          <div class="lp-hero-ctas">
            <?php if (!empty($td['hero']['cta1'])): ?>
              <a class="lp-btn lp-btn-primary" href="<?= e(niib_link($td['hero']['cta1_url'] ?: '#lead-form')) ?>"><?= e($td['hero']['cta1']) ?></a>
            <?php endif; ?>
            <?php if (!empty($td['hero']['cta2'])): ?>
              <a class="lp-btn lp-btn-outline" href="<?= e(niib_link($td['hero']['cta2_url'] ?: '#')) ?>"><?= e($td['hero']['cta2']) ?></a>
            <?php endif; ?>
          </div>
        </div>
        <?php
          $heroCity = $td['contact']['cities'][0] ?? '';
        ?>
        <div class="lp-hero-formwrap">
          <div class="lp-hero-form">
            <p class="lp-hero-form-title">Book a Free Consultation</p>
            <p class="lp-hero-form-sub">Share your details and our counsellor will call you back.</p>
              <div id="crm-form-container-50"></div>
              <script>
              (function() {
                  var cid = 'crm-form-container-50';
                  function load() {
                      var c = document.getElementById(cid);
                      if (!c || c.dataset.loaded) return;
                      c.dataset.loaded = '1';
                      var iframe = document.createElement('iframe');
                      iframe.id = 'crm-form-50';
                      iframe.src = 'https://crm.mediagarh.com/CRM/forms/public/50?embed=1&landing_url=' + encodeURIComponent(window.location.href);
                      iframe.width = '100%'; iframe.height = '420'; iframe.frameBorder = '0';
                      iframe.style.border = '0'; iframe.style.background = 'transparent';
                      c.appendChild(iframe);
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



  <!-- INTRO -->
  <?php if (!empty($td['intro']['heading']) || !empty($td['intro']['para1'])): ?>
  <section class="lp-section">
    <div class="lp-container">
      <div class="lp-intro-grid">
        <div>
          <?php if (!empty($td['intro']['heading'])): ?>
            <h2 class="lp-h2"><?= e($td['intro']['heading']) ?></h2>
          <?php endif; ?>
          <?php if (!empty($td['intro']['para1'])): ?>
            <p class="lp-lede"><?= nl2br(e($td['intro']['para1'])) ?></p>
          <?php endif; ?>
          <?php if (!empty($td['intro']['para2'])): ?>
            <p class="lp-lede"><?= nl2br(e($td['intro']['para2'])) ?></p>
          <?php endif; ?>
          <?php if (!empty($td['intro']['stat1_value']) || !empty($td['intro']['stat2_value'])): ?>
          <div class="lp-stats">
            <?php if (!empty($td['intro']['stat1_value'])): ?>
              <div class="lp-stat">
                <span class="lp-stat-value"><?= e($td['intro']['stat1_value']) ?></span>
                <span class="lp-stat-label"><?= e($td['intro']['stat1_label']) ?></span>
              </div>
            <?php endif; ?>
            <?php if (!empty($td['intro']['stat2_value'])): ?>
              <div class="lp-stat">
                <span class="lp-stat-value"><?= e($td['intro']['stat2_value']) ?></span>
                <span class="lp-stat-label"><?= e($td['intro']['stat2_label']) ?></span>
              </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php if (!empty($td['intro']['image']) || !empty($td['intro']['quote'])): ?>
        <div class="lp-intro-image-wrap">
          <?php if (!empty($td['intro']['image'])): ?>
            <div class="lp-intro-image"><img src="<?= e(img_url($td['intro']['image'])) ?>" alt=""></div>
          <?php endif; ?>
          <?php if (!empty($td['intro']['quote'])): ?>
            <div class="lp-intro-quote">"<?= e($td['intro']['quote']) ?>"</div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- CITY CONTENT (full narrative from location page docs) -->
  <?php if (!empty($td['content_block'])): $cb = $td['content_block']; ?>
  <section class="lp-section lp-cityblock">
    <div class="lp-container">
      <div class="lp-cityblock-grid">
        <div class="lp-cityblock-main">
          <?php if (!empty($cb['heading'])): ?><h2 class="lp-h2"><?= e($cb['heading']) ?></h2><?php endif; ?>
          <?php foreach (($cb['paragraphs'] ?? []) as $cbp): ?>
            <p><?= e($cbp) ?></p>
          <?php endforeach; ?>
        </div>
        <?php if (!empty($cb['features'])): ?>
        <aside class="lp-cityblock-aside">
          <?php if (!empty($cb['features_heading'])): ?><h3><?= e($cb['features_heading']) ?></h3><?php endif; ?>
          <ul class="lp-feature-list">
            <?php foreach ($cb['features'] as $cbf): ?>
              <li><span class="material-symbols-outlined">check_circle</span><span><?= e($cbf) ?></span></li>
            <?php endforeach; ?>
          </ul>
        </aside>
        <?php endif; ?>
      </div>
      <?php if (!empty($cb['closing'])): ?>
        <p class="lp-cityblock-closing"><?= e($cb['closing']) ?></p>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- COURSES -->
  <?php if (!empty($selected_courses)): ?>
  <section class="lp-section lp-courses">
    <div class="lp-container">
      <div class="lp-section-head">
        <div>
          <?php if (!empty($td['courses']['tagline'])): ?>
            <span class="lp-tagline"><?= e($td['courses']['tagline']) ?></span>
          <?php endif; ?>
          <h2 class="lp-h2"><?= e($td['courses']['heading']) ?></h2>
        </div>
        <div class="lp-section-actions">
          <a class="lp-link" href="<?= e(SITE_URL) ?>/courses">View All Courses</a>
          <div class="lp-carousel-nav">
            <button type="button" class="lp-arrow lp-prev" aria-label="Previous"><span class="material-symbols-outlined">chevron_left</span></button>
            <button type="button" class="lp-arrow lp-next" aria-label="Next"><span class="material-symbols-outlined">chevron_right</span></button>
          </div>
        </div>
      </div>
      <div class="lp-carousel">
        <button type="button" class="lp-arrow-mobile prev lp-prev" aria-label="Previous"><span class="material-symbols-outlined">chevron_left</span></button>
        <button type="button" class="lp-arrow-mobile next lp-next" aria-label="Next"><span class="material-symbols-outlined">chevron_right</span></button>
        <div class="lp-carousel-track">
          <?php foreach ($selected_courses as $idx => $cs):
            $label = sprintf('%02d. %s', $idx + 1, $cs['category_name'] ?: 'Course');
            $href  = SITE_URL . '/course/' . rawurlencode($cs['slug']);
          ?>
          <a class="lp-course-card" href="<?= e($href) ?>">
            <div class="lp-course-img">
              <img src="<?= e(img_url($cs['image'] ?: '')) ?>" alt="<?= e($cs['title']) ?>">
              <div class="lp-course-overlay"><span>View Details</span></div>
            </div>
            <span class="lp-course-label"><?= e($label) ?></span>
            <h3 class="lp-course-title"><?= e($cs['title']) ?></h3>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($show_extra): ?>
  <!-- ================= CAREER OPPORTUNITIES (12) ================= -->
  <section class="lp-section">
    <div class="lp-container">
      <div class="lp-center-head">
        <span class="lp-tagline">Design Your Career Path</span>
        <h2 class="lp-h2">Explore Your Career Opportunities</h2>
        <p>Turn your passion into a successful profession with NIIB. Our industry-focused beauty courses prepare you for high-demand careers in the beauty, wellness, and aesthetics industry.</p>
      </div>
      <div class="lp-careers2-grid">
        <?php foreach ($career_paths as $cp): ?>
        <div class="lp-career2-card">
          <span class="material-symbols-outlined"><?= e($cp['icon']) ?></span>
          <h3><?= e($cp['title']) ?></h3>
          <p><?= e($cp['desc']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ================= ACHIEVEMENTS & RECOGNITION ================= -->
  <section class="lp-section lp-ach">
    <div class="lp-container">
      <div class="lp-center-head">
        <h2 class="lp-h2">Achievements &amp; Recognition</h2>
        <p>At NIIB, excellence is reflected through industry recognition, student success, and years of dedicated beauty education.</p>
      </div>
      <div class="lp-ach-grid">
        <?php foreach ($achievements as $ac): ?>
        <div class="lp-ach-card">
          <span class="material-symbols-outlined"><?= e($ac['icon']) ?></span>
          <?php if (!empty($ac['value'])): ?><span class="lp-ach-val"><?= e($ac['value']) ?></span><?php endif; ?>
          <h3><?= e($ac['title']) ?></h3>
          <p><?= e($ac['desc']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ================= AWARD & EVENTS ================= -->
  <section class="lp-section lp-awards">
    <div class="lp-container">
      <div class="lp-center-head">
        <span class="lp-tagline">Award &amp; Events</span>
        <h2 class="lp-h2">Celebrated for Excellence</h2>
        <p>Recognised on national and international platforms &mdash; honoured by leading personalities of the industry.</p>
      </div>
      <div class="lp-awards-grid">
        <?php foreach ($award_images as $awIdx => $aw): ?>
        <div class="lp-award-card"><img data-lb-group="awards" data-lb-index="<?= (int)$awIdx ?>" src="<?= e(img_url($aw)) ?>" alt="NIIB Institute Award &amp; Recognition" loading="lazy" class="lp-lightbox-img"></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ================= WHY CHOOSE NIIB ================= -->
  <section class="lp-section">
    <div class="lp-container">
      <div class="lp-center-head">
        <h2 class="lp-h2">Why Choose NIIB?</h2>
        <p>NIIB is committed to delivering world-class beauty education through practical training, expert mentorship, and career-focused learning.</p>
      </div>
      <div class="lp-why-grid">
        <?php foreach ($why_choose as $wc): ?>
        <div class="lp-why-card">
          <div class="lp-why-ico"><span class="material-symbols-outlined"><?= e($wc['icon']) ?></span></div>
          <h3><?= e($wc['title']) ?></h3>
          <p><?= e($wc['desc']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- CAREER (legacy bento — shown only when the new sections are off) -->
  <?php if (!$show_extra && (!empty($td['career']['heading']) || !empty($td['career']['cards']))): ?>
  <section class="lp-section">
    <div class="lp-container">
      <div class="lp-career-head">
        <h2 class="lp-h2"><?= e($td['career']['heading']) ?></h2>
        <?php if (!empty($td['career']['subheading'])): ?>
          <p><?= e($td['career']['subheading']) ?></p>
        <?php endif; ?>
      </div>
      <div class="lp-career-grid">
        <?php if (!empty($td['career']['feature_title'])): ?>
        <div class="lp-career-feature">
          <span class="material-symbols-outlined lp-feature-icon"><?= e($td['career']['feature_icon'] ?: 'brush') ?></span>
          <h4><?= e($td['career']['feature_title']) ?></h4>
          <p><?= e($td['career']['feature_desc']) ?></p>
        </div>
        <?php endif; ?>
        <?php foreach ($td['career']['cards'] as $i => $cc): $variant = 'v' . (($i % 4) + 1); ?>
          <div class="lp-career-card <?= e($variant) ?>">
            <span class="material-symbols-outlined"><?= e($cc['icon'] ?: 'star') ?></span>
            <h4><?= e($cc['title']) ?></h4>
            <p><?= e($cc['desc']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- GALLERY -->
  <?php if (!empty($td['gallery']['images'])): ?>
  <section class="lp-section lp-gallery">
    <div class="lp-container">
      <div class="lp-section-head">
        <div>
          <h2 class="lp-h2"><?= e($td['gallery']['heading']) ?></h2>
          <?php if (!empty($td['gallery']['subheading'])): ?>
            <p class="lp-gallery-sub"><?= e($td['gallery']['subheading']) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <div class="lp-gallery-grid">
        <?php foreach ($td['gallery']['images'] as $gIdx => $gi): ?>
          <div class="lp-gallery-item">
            <img data-lb-group="gallery" data-lb-index="<?= (int)$gIdx ?>" src="<?= e(img_url($gi)) ?>" alt="" class="lp-lightbox-img">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php endif; ?>

  <!-- ================= TESTIMONIALS SLIDER ================= -->
  <?php if ($show_extra): ?>
  <section class="lp-section lp-tst">
    <div class="lp-container">
      <div class="lp-section-head">
        <div>
          <span class="lp-tagline">Success Stories</span>
          <h2 class="lp-h2">Read Our Students' Transformative Journeys</h2>
        </div>
        <div class="lp-carousel-nav">
          <button type="button" class="lp-arrow lp-tst-prev" aria-label="Previous"><span class="material-symbols-outlined">chevron_left</span></button>
          <button type="button" class="lp-arrow lp-tst-next" aria-label="Next"><span class="material-symbols-outlined">chevron_right</span></button>
        </div>
      </div>
      <div class="lp-tst-track">
        <?php foreach ($loc_testimonials as $t): ?>
        <div class="lp-tst-card">
          <span class="lp-tst-badge">&rdquo;</span>
          <div class="lp-tst-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <p class="lp-tst-quote"><?= e($t['text']) ?></p>
          <div class="lp-tst-name"><?= e($t['name']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ================= IMAGE TESTIMONIALS ================= -->
  <?php if ($show_extra && !empty($image_testimonials)): ?>
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
        <?php foreach ($image_testimonials as $it): ?>
        <div class="lp-imgtst-card"><img src="<?= e(img_url($it)) ?>" alt="NIIB Institute Student Testimonial" loading="lazy"></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ================= VIDEO TESTIMONIALS ================= -->
  <?php if ($show_extra && !empty($video_testimonials)): ?>
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
        <?php foreach ($video_testimonials as $vt): ?>
        <div class="lp-vid-card">
          <video controls preload="metadata" playsinline>
            <source src="<?= e(img_url($vt['src'])) ?>" type="video/mp4">
          </video>
          <div class="lp-vid-cap"><?= e($vt['title']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ACCREDITATIONS -->
  <?php
    $knownAccredLogos = [
        'nsdc'               => 'uploads/accreditations/nsdc.jpg',
        'b&wssc'             => 'uploads/accreditations/bwssc.png',
        'bwssc'              => 'uploads/accreditations/bwssc.png',
        'iao'                => 'uploads/accreditations/iao-new.jpg',
        'pathway to success' => 'uploads/accreditations/pathway-to-success.jpg',
    ];
    $cleanAccreditations = [];
    if (!empty($td['accreditations']) && is_array($td['accreditations'])) {
        foreach ($td['accreditations'] as $a) {
            $aLabel = is_array($a) ? ($a['label'] ?? '') : (string)$a;
            $aLogo  = is_array($a) ? ($a['logo']  ?? '') : '';
            // Skip corrupted warning/array strings
            if (stripos($aLabel, 'warning') !== false || stripos($aLabel, 'array to string') !== false || trim($aLabel) === 'Array') {
                continue;
            }
            if ($aLogo === '') {
                $lower = strtolower(trim($aLabel));
                if (isset($knownAccredLogos[$lower])) {
                    $aLogo = $knownAccredLogos[$lower];
                }
            }
            if ($aLabel !== '' || $aLogo !== '') {
                $cleanAccreditations[] = ['label' => $aLabel, 'logo' => $aLogo];
            }
        }
    }
    // Fallback to standard 4 logos if none configured or if all were corrupted
    if (empty($cleanAccreditations)) {
        $cleanAccreditations = [
            ['label' => 'NSDC', 'logo' => 'uploads/accreditations/nsdc.jpg'],
            ['label' => 'B&WSSC', 'logo' => 'uploads/accreditations/bwssc.png'],
            ['label' => 'IAO', 'logo' => 'uploads/accreditations/iao-new.jpg'],
            ['label' => 'Pathway to Success', 'logo' => 'uploads/accreditations/pathway-to-success.jpg'],
        ];
    }
  ?>
  <section class="lp-accred">
    <div class="lp-container">
      <h2 class="lp-accred-label">Globally Recognized Accreditations</h2>
      <div class="lp-accred-list">
        <?php foreach ($cleanAccreditations as $a): ?>
          <?php if (!empty($a['logo'])): ?>
            <img class="lp-accred-logo" src="<?= e(img_url($a['logo'])) ?>" alt="<?= e($a['label']) ?>" title="<?= e($a['label']) ?>" loading="lazy">
          <?php else: ?>
            <span><?= e($a['label']) ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($lb_groups)): ?>
  <div id="loc-template-lightbox" role="dialog" aria-modal="true">
    <button type="button" class="lb-btn lb-close" aria-label="Close"><span class="material-symbols-outlined" style="font-size:34px">close</span></button>
    <button type="button" class="lb-btn lb-prev" aria-label="Previous"><span class="material-symbols-outlined" style="font-size:32px">chevron_left</span></button>
    <button type="button" class="lb-btn lb-next" aria-label="Next"><span class="material-symbols-outlined" style="font-size:32px">chevron_right</span></button>
    <img alt="">
    <div class="lb-counter"></div>
  </div>
  <?php endif; ?>

  <!-- LEAD FORM -->
  <section id="lead-form" class="lp-section">
    <div class="lp-container">
      <div class="lp-lead">
        <div class="lp-lead-info">
          <h2 class="lp-h2"><?= e($td['contact']['heading'] ?: 'Have Questions? Get in touch!') ?></h2>
          <?php if (!empty($td['contact']['subheading'])): ?>
            <p class="lp-lead-sub"><?= e($td['contact']['subheading']) ?></p>
          <?php endif; ?>
          <div class="lp-lead-rows">
            <?php if (!empty($td['contact']['phone'])): ?>
              <div class="lp-lead-row"><span class="material-symbols-outlined">call</span><a href="tel:<?= e($td['contact']['phone']) ?>"><?= e($td['contact']['phone']) ?></a></div>
            <?php endif; ?>
            <?php if (!empty($td['contact']['email'])): ?>
              <div class="lp-lead-row"><span class="material-symbols-outlined">mail</span><a href="mailto:<?= e($td['contact']['email']) ?>"><?= e($td['contact']['email']) ?></a></div>
            <?php endif; ?>
            <?php if (!empty($td['contact']['address'])): ?>
              <div class="lp-lead-row"><span class="material-symbols-outlined">location_on</span><?php if (!empty($td['contact']['map_link'])): ?><a href="<?= e($td['contact']['map_link']) ?>" target="_blank" rel="noopener" title="View on Google Maps"><?= e($td['contact']['address']) ?></a><?php else: ?><span><?= e($td['contact']['address']) ?></span><?php endif; ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="lp-lead-form">
          <form id="locLeadForm" method="post" action="<?= e(SITE_URL) ?>/api/enquiry.php">
            <input type="hidden" name="source_page" value="<?= e($p['slug']) ?>">
            <div class="lp-lead-row-pair">
              <div class="lp-field"><label>Full Name</label><input type="text" name="name" required placeholder="Your name"></div>
              <div class="lp-field"><label>Email Address</label><input type="email" name="email" required placeholder="example@email.com"></div>
            </div>
            <div class="lp-lead-row-pair">
              <div class="lp-field"><label>Phone Number</label><input type="tel" name="phone" required placeholder="+91"></div>
              <div class="lp-field"><label>Select City</label>
                <select name="city">
                  <?php foreach ($td['contact']['cities'] as $c): ?>
                    <option value="<?= e($c) ?>"><?= e($c) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?= niib_recaptcha_widget() ?>
            <button type="submit" class="lp-lead-submit">Enquire Today</button>
            <div id="locLeadMsg" class="lp-hf-msg"></div>
          </form>
        </div>
      </div>
    </div>
  </section>

</div>

<script>
(function(){
  /* ----- AJAX Forms ----- */
  function setupAjaxForm(formId, msgId) {
    var form = document.getElementById(formId);
    if (!form) return;
    var msg = document.getElementById(msgId);
    var btn = form.querySelector('button[type=submit]');
    var ENDPOINT = <?= json_encode(SITE_URL . '/api/enquiry.php') ?>;
    function getToken(){
      var cfg = window.NIIB_RECAPTCHA;
      if (!cfg || !window.grecaptcha) return Promise.resolve('');
      if (cfg.mode === 'v3') {
        return new Promise(function(res){ try { grecaptcha.ready(function(){ grecaptcha.execute(cfg.siteKey,{action:cfg.action||'submit'}).then(function(t){res(t||'');}).catch(function(){res('');}); }); } catch(e){ res(''); } });
      }
      return Promise.resolve((typeof grecaptcha.getResponse==='function') ? (grecaptcha.getResponse()||'') : '');
    }
    function setMsg(kind, text){
      if (!msg) return;
      var color = kind==='success' ? '#0a7d33' : (kind==='error' ? '#c0271e' : 'var(--lp-text-soft)');
      msg.innerHTML = '<div style="color:'+color+';font-weight:600;margin-top:10px;">'+text+'</div>';
    }
    var busy=false;
    form.addEventListener('submit', function(e){
      e.preventDefault();
      if (busy) return; busy=true; if(btn) btn.disabled=true;
      setMsg('info','Sending…');
      getToken().then(function(token){
        var cfg = window.NIIB_RECAPTCHA;
        if (cfg && cfg.mode==='v2' && !token){ setMsg('error','Please complete the reCAPTCHA challenge.'); busy=false; if(btn)btn.disabled=false; return; }
        var fd = new FormData(form);
        if (token) fd.set('g-recaptcha-response', token);
        return fetch(ENDPOINT,{method:'POST',body:fd,credentials:'same-origin'})
          .then(function(r){return r.json();})
          .then(function(data){
            setMsg(data.ok?'success':'error', data.message||(data.ok?'Thank you! We will contact you soon.':'Something went wrong.'));
            if (data.ok){ form.reset(); }
          })
          .catch(function(){ setMsg('error','Network error. Please try again.'); })
          .then(function(){ busy=false; if(btn)btn.disabled=false; });
      });
    });
  }
  setupAjaxForm('locLeadForm', 'locLeadMsg');

  /* ----- Course carousel ----- */
  var carousel = document.querySelector('#loc-template .lp-carousel');
  if (carousel) {
    var track = carousel.querySelector('.lp-carousel-track');
    function stepSize(){
      var item = track.querySelector('.lp-course-card');
      if (!item) return track.clientWidth;
      var style = getComputedStyle(track);
      var gap = parseInt(style.columnGap || style.gap || '0', 10) || 0;
      return item.getBoundingClientRect().width + gap;
    }
    function atEnd(){ return track.scrollLeft + track.clientWidth >= track.scrollWidth - 4; }
    function atStart(){ return track.scrollLeft <= 4; }
    function next(){ atEnd() ? track.scrollTo({ left: 0, behavior: 'smooth' }) : track.scrollBy({ left: stepSize(), behavior: 'smooth' }); }
    function prev(){ atStart() ? track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' }) : track.scrollBy({ left: -stepSize(), behavior: 'smooth' }); }
    document.querySelectorAll('#loc-template .lp-prev').forEach(function(b){ b.addEventListener('click', prev); });
    document.querySelectorAll('#loc-template .lp-next').forEach(function(b){ b.addEventListener('click', next); });
    var timer = null;
    function start(){ stop(); timer = setInterval(next, 5000); }
    function stop(){ if (timer) { clearInterval(timer); timer = null; } }
    carousel.addEventListener('mouseenter', stop);
    carousel.addEventListener('mouseleave', start);
    carousel.addEventListener('touchstart', stop, { passive: true });
    start();
  }

  /* ----- Testimonials slider ----- */
  var tstTrack = document.querySelector('#loc-template .lp-tst-track');
  if (tstTrack) {
    function tstStep(){
      var c = tstTrack.querySelector('.lp-tst-card');
      if (!c) return tstTrack.clientWidth;
      var g = parseInt(getComputedStyle(tstTrack).columnGap || getComputedStyle(tstTrack).gap || '0', 10) || 0;
      return c.getBoundingClientRect().width + g;
    }
    document.querySelectorAll('#loc-template .lp-tst-prev').forEach(function(b){
      b.addEventListener('click', function(){
        (tstTrack.scrollLeft <= 4) ? tstTrack.scrollTo({ left: tstTrack.scrollWidth, behavior:'smooth' }) : tstTrack.scrollBy({ left: -tstStep(), behavior:'smooth' });
      });
    });
    document.querySelectorAll('#loc-template .lp-tst-next').forEach(function(b){
      b.addEventListener('click', function(){
        (tstTrack.scrollLeft + tstTrack.clientWidth >= tstTrack.scrollWidth - 4) ? tstTrack.scrollTo({ left: 0, behavior:'smooth' }) : tstTrack.scrollBy({ left: tstStep(), behavior:'smooth' });
      });
    });
  }

  /* ----- Image testimonials slider ----- */
  var imgTrack = document.querySelector('#loc-template .lp-imgtst-track');
  if (imgTrack) {
    function imgStep(){
      var c = imgTrack.querySelector('.lp-imgtst-card');
      if (!c) return imgTrack.clientWidth;
      var g = parseInt(getComputedStyle(imgTrack).columnGap || getComputedStyle(imgTrack).gap || '0', 10) || 0;
      return c.getBoundingClientRect().width + g;
    }
    document.querySelectorAll('#loc-template .lp-imgtst-prev').forEach(function(b){
      b.addEventListener('click', function(){
        (imgTrack.scrollLeft <= 4) ? imgTrack.scrollTo({ left: imgTrack.scrollWidth, behavior:'smooth' }) : imgTrack.scrollBy({ left: -imgStep(), behavior:'smooth' });
      });
    });
    document.querySelectorAll('#loc-template .lp-imgtst-next').forEach(function(b){
      b.addEventListener('click', function(){
        (imgTrack.scrollLeft + imgTrack.clientWidth >= imgTrack.scrollWidth - 4) ? imgTrack.scrollTo({ left: 0, behavior:'smooth' }) : imgTrack.scrollBy({ left: imgStep(), behavior:'smooth' });
      });
    });
  }

  /* ----- Video testimonials slider ----- */
  var vidTrack = document.querySelector('#loc-template .lp-vid-track');
  if (vidTrack) {
    function vidStep(){
      var c = vidTrack.querySelector('.lp-vid-card');
      if (!c) return vidTrack.clientWidth;
      var g = parseInt(getComputedStyle(vidTrack).columnGap || getComputedStyle(vidTrack).gap || '0', 10) || 0;
      return c.getBoundingClientRect().width + g;
    }
    document.querySelectorAll('#loc-template .lp-vid-prev').forEach(function(b){
      b.addEventListener('click', function(){
        (vidTrack.scrollLeft <= 4) ? vidTrack.scrollTo({ left: vidTrack.scrollWidth, behavior:'smooth' }) : vidTrack.scrollBy({ left: -vidStep(), behavior:'smooth' });
      });
    });
    document.querySelectorAll('#loc-template .lp-vid-next').forEach(function(b){
      b.addEventListener('click', function(){
        (vidTrack.scrollLeft + vidTrack.clientWidth >= vidTrack.scrollWidth - 4) ? vidTrack.scrollTo({ left: 0, behavior:'smooth' }) : vidTrack.scrollBy({ left: vidStep(), behavior:'smooth' });
      });
    });
  }

  /* ----- Lightbox (gallery + awards cycle independently) ----- */
  var lbGroups = <?= json_encode($lb_groups, JSON_UNESCAPED_SLASHES) ?>;
  var box = document.getElementById('loc-template-lightbox');
  if (box) {
    var lbImg = box.querySelector('img');
    var counter = box.querySelector('.lb-counter');
    var images = [];
    var idx = 0;
    function show(i){
      if (!images.length) return;
      idx = (i + images.length) % images.length;
      lbImg.src = images[idx];
      counter.textContent = (idx + 1) + ' / ' + images.length;
      box.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }
    function close(){ box.classList.remove('is-open'); document.body.style.overflow = ''; }
    document.querySelectorAll('#loc-template .lp-lightbox-img').forEach(function(el){
      el.addEventListener('click', function(){
        images = lbGroups[el.getAttribute('data-lb-group') || 'gallery'] || [];
        show(parseInt(el.dataset.lbIndex, 10) || 0);
      });
    });
    box.querySelector('.lb-close').addEventListener('click', close);
    box.querySelector('.lb-prev').addEventListener('click', function(e){ e.stopPropagation(); show(idx - 1); });
    box.querySelector('.lb-next').addEventListener('click', function(e){ e.stopPropagation(); show(idx + 1); });
    box.addEventListener('click', function(e){ if (e.target === box) close(); });
    document.addEventListener('keydown', function(e){
      if (!box.classList.contains('is-open')) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowLeft') show(idx - 1);
      else if (e.key === 'ArrowRight') show(idx + 1);
    });
  }
})();
</script>

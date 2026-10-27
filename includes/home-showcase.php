<?php
/**
 * Homepage showcase — the credibility sections from the location pages, reused on
 * the homepage: Achievements, Recognized Excellence (gallery), Celebrated for
 * Excellence (awards), Why Choose NIIB?, Success Stories, Student Testimonials,
 * Video Testimonials, and Globally Recognized Accreditations.
 *
 * Shares the #loc-template stylesheet via loc-styles.php (single source of truth).
 * Gallery + accreditations are pulled from a reference location page so the homepage
 * automatically stays in sync when those are updated on the location pages.
 */
if (!defined('NIIB_INIT')) { http_response_code(403); exit; }

/* Optional section filter: a host page may set $showcase_sections (array) before including
   this file to render only some sections. Unset => all sections (homepage behaviour). */
$SC_ON = (isset($showcase_sections) && is_array($showcase_sections))
    ? $showcase_sections
    : ['achievements','gallery','awards','why','stories','student','video','accreditations'];
$sc_show = function($k) use ($SC_ON){ return in_array($k, $SC_ON, true); };

/* ---- shared, hardcoded content (identical to the location pages) ---- */
$achievements = [
    ['value'=>'22+',     'title'=>'Years of Excellence',           'desc'=>'Delivering professional beauty education since 2004.',                            'icon'=>'workspace_premium'],
    ['value'=>'50,000+', 'title'=>'Successful Alumni',             'desc'=>'Helping students build rewarding careers across India and abroad.',                'icon'=>'groups'],
    ['value'=>'35+',     'title'=>'Modern Campuses',               'desc'=>'World-class training infrastructure across multiple locations.',                   'icon'=>'apartment'],
    ['value'=>'100%',    'title'=>'Placement Support',             'desc'=>'Career guidance, interview preparation, and placement assistance.',                'icon'=>'handshake'],
    ['value'=>'',        'title'=>'International Curriculum',       'desc'=>'Industry-oriented education with advanced practical training.',                    'icon'=>'public'],
    ['value'=>'',        'title'=>'Expert Faculty',                'desc'=>'Learn from experienced beauty professionals and certified trainers.',             'icon'=>'diversity_3'],
    ['value'=>'',        'title'=>'Hands-on Practical Learning',   'desc'=>'Real salon exposure with live demonstrations and practical sessions.',            'icon'=>'back_hand'],
    ['value'=>'',        'title'=>'Trusted Beauty Education Brand', 'desc'=>"One of India's leading institutes in beauty, cosmetology, aesthetics & wellness.", 'icon'=>'verified'],
];
$why_choose = [
    ['icon'=>'menu_book', 'title'=>'Industry-Oriented Curriculum',     'desc'=>'Learn the latest beauty, cosmetology, hair, skin, makeup, and aesthetics techniques.'],
    ['icon'=>'school',    'title'=>'Experienced Faculty',             'desc'=>'Get trained by certified professionals with years of industry experience.'],
    ['icon'=>'handshake', 'title'=>'100% Placement Support',          'desc'=>'Receive career guidance, interview preparation, and placement assistance.'],
    ['icon'=>'back_hand', 'title'=>'Practical Hands-on Training',      'desc'=>'Gain real salon experience with live models and professional equipment.'],
    ['icon'=>'apartment', 'title'=>'Modern Infrastructure',           'desc'=>'Train in fully equipped classrooms, advanced beauty labs, and salon environments.'],
    ['icon'=>'public',    'title'=>'International Standard Education',  'desc'=>'Develop professional skills that meet global beauty industry standards.'],
];
$loc_testimonials = [
    ['name'=>'Priya Sharma',  'text'=>'Joining NIIB was the best decision of my career. The practical training and expert guidance helped me become a confident professional makeup artist. Today, I work with bridal clients and earn independently.'],
    ['name'=>'Simran Kaur',   'text'=>'The trainers at NIIB are incredibly supportive and knowledgeable. Every session was practical, and I learned industry techniques that boosted my confidence.'],
    ['name'=>'Muskan Verma',  'text'=>'I started with zero experience, but NIIB transformed my passion into a profession. Their placement support helped me begin my career immediately after completing my course.'],
    ['name'=>'Neha Arora',    'text'=>'The hands-on training and modern infrastructure made learning enjoyable. I highly recommend NIIB to anyone serious about building a career in the beauty industry.'],
    ['name'=>'Amandeep Kaur', 'text'=>"From makeup to skincare and hairstyling, every module was well-structured. The faculty encouraged me throughout my journey, and today I'm successfully working as a freelance beauty professional."],
    ['name'=>'Riya Malhotra', 'text'=>"NIIB gave me the skills, confidence, and industry exposure I needed to start my own beauty studio. I'm grateful for the knowledge and mentorship I received."],
];
$award_images = [
    'assets/images/awards/award1.jpg','assets/images/awards/award2.jpg','assets/images/awards/award3.jpg',
    'assets/images/awards/award4.jpg','assets/images/awards/award5.jpg','assets/images/awards/award6.jpg',
];
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

/* ---- gallery + accreditations pulled from a reference location page ---- */
$sc = [
    'gallery' => ['heading'=>'Recognized Excellence','subheading'=>'State-of-the-art facilities and training','images'=>[]],
    'accreditations' => [],
];
try {
    $refStmt = db()->prepare("SELECT template_data FROM pages WHERE slug=? AND status=1 LIMIT 1");
    $refStmt->execute(['ludhiana-state-hub']);
    $refTd = json_decode($refStmt->fetchColumn() ?: '', true);
    if (is_array($refTd)) {
        if (!empty($refTd['gallery']['images'])) $sc['gallery'] = $refTd['gallery'];
        if (!empty($refTd['accreditations']))    $sc['accreditations'] = $refTd['accreditations'];
    }
} catch (Throwable $e) {}

/* ---- lightbox groups ---- */
$lb_groups = [];
if ($sc_show('gallery') && !empty($sc['gallery']['images'])) $lb_groups['gallery'] = array_values(array_map(function($g){ return img_url($g); }, $sc['gallery']['images']));
if ($sc_show('awards')  && !empty($award_images))            $lb_groups['awards']  = array_values(array_map(function($a){ return img_url($a); }, $award_images));
?>
<?php
// Bodoni Moda is already loaded non-blocking from header.php — do NOT load it again.
// Only load Material Symbols here (non-blocking).
// Add preconnect for fonts.googleapis.com if not already emitted (it is in header.php,
// but repeated preconnect is harmless; the browser deduplicates them).
?>
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet"></noscript>
<?php include __DIR__ . '/loc-styles.php'; ?>

<div id="loc-template" class="home-showcase">

  <?php if ($sc_show('achievements')): ?>
  <!-- ACHIEVEMENTS & RECOGNITION -->
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
  <?php endif; ?>

  <!-- RECOGNIZED EXCELLENCE (gallery) -->
  <?php if ($sc_show('gallery') && !empty($sc['gallery']['images'])): ?>
  <section class="lp-section lp-gallery">
    <div class="lp-container">
      <div class="lp-section-head">
        <div>
          <h2 class="lp-h2"><?= e($sc['gallery']['heading']) ?></h2>
          <?php if (!empty($sc['gallery']['subheading'])): ?>
            <p class="lp-gallery-sub"><?= e($sc['gallery']['subheading']) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <div class="lp-gallery-grid">
        <?php foreach ($sc['gallery']['images'] as $gIdx => $gi): ?>
          <div class="lp-gallery-item">
            <img data-lb-group="gallery" data-lb-index="<?= (int)$gIdx ?>" src="<?= e(img_url($gi)) ?>" alt="" class="lp-lightbox-img">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($sc_show('awards')): ?>
  <!-- CELEBRATED FOR EXCELLENCE (awards) -->
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
  <?php endif; ?>

  <?php if ($sc_show('why')): ?>
  <!-- WHY CHOOSE NIIB -->
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

  <?php if ($sc_show('stories')): ?>
  <!-- SUCCESS STORIES (testimonials slider) -->
  <section class="lp-section lp-tst">
    <div class="lp-container">
      <div class="lp-section-head">
        <div>
          <span class="lp-tagline">Success Stories</span>
          <h2 class="lp-h2">Stories of Transformation</h2>
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

  <!-- STUDENT TESTIMONIALS (image) -->
  <?php if ($sc_show('student') && !empty($image_testimonials)): ?>
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

  <!-- VIDEO TESTIMONIALS -->
  <?php if ($sc_show('video') && !empty($video_testimonials)): ?>
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

  <!-- GLOBALLY RECOGNIZED ACCREDITATIONS -->
  <?php if ($sc_show('accreditations') && !empty($sc['accreditations'])): ?>
  <section class="lp-accred">
    <div class="lp-container">
      <span class="lp-accred-label">Globally Recognized Accreditations</span>
      <div class="lp-accred-list">
        <?php foreach ($sc['accreditations'] as $a): ?>
          <?php
            $aLabel = is_array($a) ? ($a['label'] ?? '') : $a;
            $aLogo  = is_array($a) ? ($a['logo']  ?? '') : '';
          ?>
          <?php if ($aLogo !== ''): ?>
            <img class="lp-accred-logo" src="<?= e(img_url($aLogo)) ?>" alt="<?= e($aLabel) ?>" title="<?= e($aLabel) ?>" loading="lazy">
          <?php else: ?>
            <span><?= e($aLabel) ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($lb_groups)): ?>
  <div id="loc-template-lightbox" role="dialog" aria-modal="true">
    <button type="button" class="lb-btn lb-close" aria-label="Close"><span class="material-symbols-outlined" style="font-size:34px">close</span></button>
    <button type="button" class="lb-btn lb-prev" aria-label="Previous"><span class="material-symbols-outlined" style="font-size:32px">chevron_left</span></button>
    <button type="button" class="lb-btn lb-next" aria-label="Next"><span class="material-symbols-outlined" style="font-size:32px">chevron_right</span></button>
    <img alt="">
    <div class="lb-counter"></div>
  </div>
  <?php endif; ?>

</div>

<script>
(function(){
  function slider(trackSel, prevSel, nextSel){
    var track = document.querySelector(trackSel);
    if (!track) return;
    function step(){
      var c = track.querySelector(':scope > *');
      if (!c) return track.clientWidth;
      var g = parseInt(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '0', 10) || 0;
      return c.getBoundingClientRect().width + g;
    }
    document.querySelectorAll(prevSel).forEach(function(b){
      b.addEventListener('click', function(){
        (track.scrollLeft <= 4) ? track.scrollTo({ left: track.scrollWidth, behavior:'smooth' }) : track.scrollBy({ left: -step(), behavior:'smooth' });
      });
    });
    document.querySelectorAll(nextSel).forEach(function(b){
      b.addEventListener('click', function(){
        (track.scrollLeft + track.clientWidth >= track.scrollWidth - 4) ? track.scrollTo({ left: 0, behavior:'smooth' }) : track.scrollBy({ left: step(), behavior:'smooth' });
      });
    });
  }
  slider('#loc-template.home-showcase .lp-tst-track',    '#loc-template.home-showcase .lp-tst-prev',    '#loc-template.home-showcase .lp-tst-next');
  slider('#loc-template.home-showcase .lp-imgtst-track', '#loc-template.home-showcase .lp-imgtst-prev', '#loc-template.home-showcase .lp-imgtst-next');
  slider('#loc-template.home-showcase .lp-vid-track',    '#loc-template.home-showcase .lp-vid-prev',    '#loc-template.home-showcase .lp-vid-next');

  /* Lightbox (gallery + awards cycle independently) */
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
    document.querySelectorAll('#loc-template.home-showcase .lp-lightbox-img').forEach(function(el){
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

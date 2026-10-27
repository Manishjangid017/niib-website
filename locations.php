<?php
require __DIR__ . '/includes/init.php';
$pdo = db();

/* Accreditation badge per branch (matches on city / state / region label, case-insensitive):
   NSDC for one set of cities, B&WSSC for another, Pathway to Success for the rest. */
function niib_loc_logo($loc){
    $hay = mb_strtolower(($loc['city'] ?? '').' '.($loc['state'] ?? '').' '.($loc['region_label'] ?? ''));
    $nsdc  = ['jalandhar','ludhiana','phagwara','hoshiarpur','amritsar','chandigarh','sector 34'];
    $bwssc = ['kharar','moga','mohali','rajpura','khanna','lucknow','noida','jammu','rudrapur'];
    foreach ($nsdc  as $k){ if (mb_strpos($hay, $k) !== false) return ['uploads/accreditations/nsdc.jpg', 'NSDC']; }
    foreach ($bwssc as $k){ if (mb_strpos($hay, $k) !== false) return ['uploads/accreditations/bwssc.png', 'B&WSSC']; }
    return ['uploads/accreditations/pathway-to-success.jpg', 'Pathway to Success'];
}

$page_seo['title']       = 'Our Locations - ' . setting('site_name');
$page_seo['description'] = 'Find an NIIB Institute campus near you. Premier beauty academies across Punjab, Chandigarh, Jammu & Kashmir, Uttar Pradesh, Bihar, Uttarakhand and more.';
$page_seo['keywords']    = 'NIIB locations, beauty institute Punjab, beauty academy Chandigarh, NIIB centers, NIIB campuses';

niib_seo_set_breadcrumbs([
    ['name' => 'Home',      'url' => SITE_URL . '/'],
    ['name' => 'Locations'],
]);

// Fetch and group locations with linked pages
$rows = $pdo->query("SELECT l.*, p.slug as page_slug FROM locations l LEFT JOIN pages p ON p.id = l.page_id WHERE l.status=1 ORDER BY l.state_order, l.state, l.sort_order, l.id")->fetchAll();

// LocalBusiness schema for each featured location
foreach ($rows as $loc) {
    if (!empty($loc['is_featured'])) {
        niib_seo_add_schema(niib_seo_local_business($loc));
    }
}
$grouped = [];
$stateOrder = [];   // state => order
$stateSlug  = [];   // state => slug
foreach ($rows as $r) {
    $grouped[$r['state']][] = $r;
    $stateOrder[$r['state']] = (int)$r['state_order'];
    $stateSlug[$r['state']]  = $r['state_slug'];
}

$totalStates = count($grouped);
$totalCenters = count($rows);

// Map pin coordinates per state (left/top % within map circle)
$mapPins = [
    'punjab'         => ['top'=>'40%', 'left'=>'30%', 'label'=>'PUNJAB'],
    'chandigarh'     => ['top'=>'42%', 'left'=>'38%', 'label'=>'CHANDIGARH'],
    'jammu-kashmir'  => ['top'=>'20%', 'left'=>'32%', 'label'=>'JAMMU & KASHMIR'],
    'uttarakhand'    => ['top'=>'40%', 'left'=>'45%', 'label'=>'UTTARAKHAND'],
    'uttar-pradesh'  => ['top'=>'55%', 'left'=>'55%', 'label'=>'UTTAR PRADESH'],
    'bihar'          => ['top'=>'60%', 'left'=>'70%', 'label'=>'BIHAR'],
];

include 'includes/header.php';
?>

<!-- =============== HERO =============== -->
<section class="loc-hero">
    <div class="loc-hero-bg">
        <img src="<?= e(img_url('assets/images/design/cta-campus.jpg')) ?>" alt="">
    </div>
    <div class="container loc-hero-inner">
        <span class="section-eyebrow" style="color:var(--secondary-fixed)">National Presence</span>
        <h1>Our Centers</h1>
        <p>Empowering beauty professionals across North India. From the heart of Punjab to the valleys of Kashmir and the plains of Bihar.</p>
    </div>
</section>

<!-- =============== VISUAL MAP =============== -->
<section class="loc-map">
    <div class="container">
        <div class="loc-map-grid">
            <div class="loc-map-text">
                <h2>Expanding Horizons</h2>
                <p>NIIB is rapidly expanding its footprint to bring world-class clinical and beauty education to your doorstep. Our stylized map highlights our strategic presence across North and Eastern India.</p>
                <ul class="loc-map-stats">
                    <li><span class="bar"></span><span class="label"><?= $totalStates ?> States &amp; Territories</span></li>
                    <li><span class="bar"></span><span class="label"><?= $totalCenters ?>+ Premium Centers</span></li>
                </ul>
            </div>
            <div class="loc-map-vis">
                <div class="loc-map-circle">
                    <div class="loc-map-ring"></div>
                    <?php foreach ($grouped as $state => $items):
                        $slug = $stateSlug[$state] ?? '';
                        if (!isset($mapPins[$slug])) continue;
                        $pin = $mapPins[$slug];
                    ?>
                    <a href="#state-<?= e($slug) ?>" class="loc-pin" style="top:<?= $pin['top'] ?>; left:<?= $pin['left'] ?>;" title="<?= e($state) ?>">
                        <span class="loc-dot"></span>
                        <span class="loc-tip"><?= e($pin['label']) ?></span>
                    </a>
                    <?php endforeach; ?>
                    <div class="loc-map-watermark">NIIB</div>
                </div>
                <div class="loc-map-legend">
                    <span class="dot"></span>
                    <span>Active NIIB Regions</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =============== LOCATIONS BY STATE =============== -->
<section class="loc-list">
<div class="container">
    <?php
    $render_featured_card = function($loc) {
        $__lg = niib_loc_logo($loc);
        if (!empty($loc['page_slug'])) {
            $hubHref = SITE_URL . '/' . e($loc['page_slug']);
            $hubExtra = '';
            $hubLabel = 'VIEW CAMPUS';
        } elseif (!empty($loc['custom_link'])) {
            $hubHref = e($loc['custom_link']);
            $hubExtra = ' target="_blank" rel="noopener"';
            $hubLabel = 'VIEW CAMPUS';
        } else {
            $hubHref = SITE_URL . '/contact?city=' . urlencode($loc['city']);
            $hubExtra = '';
            $hubLabel = 'VIEW CAMPUS';
        }
        ?>
        <article class="loc-card loc-card-featured">
            <span class="loc-label"><?= e($loc['region_label']) ?></span>
            <div class="loc-name-row" style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px">
                <h3 style="margin:0"><?= e($loc['city']) ?></h3>
               
            </div>
            <div class="loc-meta">
                <?php if ($loc['address']): ?>
                <div class="loc-meta-row"><i class="bi bi-geo-alt-fill"></i><span><?= e($loc['address']) ?></span></div>
                <?php endif; ?>
                <?php if ($loc['phone']): ?>
                <div class="loc-meta-row"><i class="bi bi-telephone-fill"></i><a href="tel:<?= e($loc['phone']) ?>"><?= e($loc['phone']) ?></a></div>
                <?php endif; ?>
                <?php if ($loc['email']): ?>
                <div class="loc-meta-row"><i class="bi bi-envelope-fill"></i><a href="mailto:<?= e($loc['email']) ?>"><?= e($loc['email']) ?></a></div>
                <?php endif; ?>
            </div>
            <div class="loc-foot">
                <a class="loc-cta" href="<?= $hubHref ?>"<?= $hubExtra ?>><?= $hubLabel ?></a>
                <?php if ($loc['map_link']): ?>
                <a class="loc-icon" target="_blank" rel="noopener" href="<?= e($loc['map_link']) ?>" title="Directions"><i class="bi bi-sign-turn-right-fill"></i></a>
                <?php endif; ?>
            </div>
        </article>
        <?php
    };

    $render_simple_card = function($loc) {
        $__lg = niib_loc_logo($loc);
        if (!empty($loc['page_slug'])) {
            $simpleHref = SITE_URL . '/' . e($loc['page_slug']);
            $simpleExtra = '';
            $simpleCTA = 'EXPLORE CENTER';
        } elseif (!empty($loc['custom_link'])) {
            $simpleHref = e($loc['custom_link']);
            $simpleExtra = ' target="_blank" rel="noopener"';
            $simpleCTA = 'CONTACT HUB';
        } else {
            $simpleHref = SITE_URL . '/contact?city=' . urlencode($loc['city']);
            $simpleExtra = '';
            $simpleCTA = 'CONTACT HUB';
        }
        ?>
        <article class="loc-card loc-card-simple">
            <div class="loc-name-row" style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px">
                <h4 style="margin:0"><?= e($loc['city']) ?></h4>
                <img class="loc-badge" src="<?= e(img_url($__lg[0])) ?>" alt="<?= e($__lg[1]) ?> certified" title="<?= e($__lg[1]) ?>" loading="lazy" style="height:44px;width:auto;max-width:100px;flex-shrink:0;object-fit:contain">
            </div>
            <p class="loc-sub"><?= e($loc['region_label']) ?></p>
            <a class="loc-cta-sm" href="<?= $simpleHref ?>"<?= $simpleExtra ?>><?= $simpleCTA ?> <i class="bi bi-arrow-right"></i></a>
        </article>
        <?php
    };
    ?>

    <?php 
    // PREPROCESS TO MERGE CHANDIGARH INTO PUNJAB IF NEEDED
    $punjabKey = null;
    $chandigarhKey = null;
    foreach (array_keys($grouped) as $k) {
        if (strtolower($k) === 'punjab') $punjabKey = $k;
        if (strpos(strtolower($k), 'chandigarh') !== false) $chandigarhKey = $k;
    }
    if ($punjabKey && $chandigarhKey && isset($grouped[$chandigarhKey])) {
        foreach ($grouped[$chandigarhKey] as $cLoc) {
            $grouped[$punjabKey][] = $cLoc;
        }
        unset($grouped[$chandigarhKey]); // this hides the original Chandigarh section
    }

    foreach ($grouped as $state => $items):
        $slug = $stateSlug[$state] ?? slugify($state);
        
        if (strtolower($state) === 'punjab'):
            $nsdc_order = ['Ludhiana', 'Sector 34-A Hub', 'Jalandhar', 'Phagwara', 'Chandigarh', 'Hoshiarpur', 'Amritsar'];
            $nsdc_cards = [];
            $other_cards = [];
            foreach ($items as $loc) {
                $city = trim($loc['city']);
                $pos = array_search($city, $nsdc_order);
                if ($pos !== false) {
                    if ($city === 'Sector 34-A Hub' || $city === 'Chandigarh (Sector 34-A Hub)') {
                        $loc['city'] = 'Chandigarh'; // rename as requested
                    }
                    $nsdc_cards[$pos] = $loc;
                } else {
                    $other_cards[] = $loc;
                }
            }
            ksort($nsdc_cards); // maintain requested order
            
            // Move Ludhiana 2 to the first position of other_cards
            $ludhiana2_idx = -1;
            foreach ($other_cards as $idx => $oc) {
                if (trim($oc['city']) === 'Ludhiana 2') {
                    $ludhiana2_idx = $idx;
                    break;
                }
            }
            if ($ludhiana2_idx !== -1) {
                $l2_card = $other_cards[$ludhiana2_idx];
                unset($other_cards[$ludhiana2_idx]);
                array_unshift($other_cards, $l2_card);
            }
        ?>
        <div class="loc-state" id="state-<?= e($slug) ?>">
            <div style="margin-bottom:56px;">
                <div class="loc-state-head">
                    <h2>NSDC Linked Centres</h2>
                    <span class="rule"></span>
                </div>
                <p style="color:var(--on-surface-variant); font-size:16px; margin-bottom:32px; max-width:900px; line-height:1.6;">Our NSDC linked centres are company-owned centres committed to providing students with industry-relevant skills, professional training, and meaningful career opportunities. These centres fall under the NSDC Market Led Program, strengthening our focus on industry-aligned training and skill development. Our NSDC linkage reflects our commitment to maintaining high training standards and helping students take confident steps towards successful placements and rewarding careers. Certification through these centres is exclusively for NSDC certification purposes.</p>
                
                <?php if (!empty($nsdc_cards)): ?>
                <div class="loc-grid loc-grid-featured">
                    <?php foreach ($nsdc_cards as $loc): ?>
                        <?php $render_featured_card($loc); ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($other_cards)): ?>
            <div>
                <div class="loc-state-head" style="margin-bottom: 24px;">
                    <h2>Other Centres</h2>
                    <span class="rule"></span>
                </div>
                <div class="loc-state-head">
                    <p style="color:var(--on-surface-variant); font-size:16px; margin-bottom:32px; max-width:900px; line-height:1.6;">Our other centres are linked with BWSSC and Pathways to Success, providing students with industry-relevant training and skill development opportunities. These centres support students in gaining practical knowledge and enhancing their professional skills. Certification through these centres is applicable only for BWSSC and Pathways to Success, as per the respective certification framework.</p>
                
                    <!--<span class="rule"></span>-->
                </div>
                <div class="loc-grid loc-grid-featured">
                    <?php foreach ($other_cards as $loc): ?>
                        <?php 
                        // Render using featured or simple based on their database setting
                        if ((int)$loc['is_featured'] === 1) {
                            $render_featured_card($loc);
                        } else {
                            $render_simple_card($loc);
                        }
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php else: // NOT PUNJAB, normal rendering ?>
        
        <?php
            // split by featured
            $featured = array_filter($items, fn($i) => (int)$i['is_featured'] === 1);
            $simple   = array_filter($items, fn($i) => (int)$i['is_featured'] === 0);
        ?>
        <div class="loc-state" id="state-<?= e($slug) ?>">
            <div class="loc-state-head">
                <h2><?= e($state) ?></h2>
                <span class="rule"></span>
            </div>

            <?php if (!empty($featured)): ?>
            <div class="loc-grid loc-grid-featured">
                <?php foreach ($featured as $loc): ?>
                    <?php $render_featured_card($loc); ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($simple)): ?>
            <div class="loc-grid loc-grid-simple">
                <?php foreach ($simple as $loc): ?>
                    <?php $render_simple_card($loc); ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if (empty($grouped)): ?>
    <div class="empty-state"><i class="bi bi-geo-alt"></i><p>No locations to display yet. Add some from the admin panel.</p></div>
    <?php endif; ?>
</div>
</section>

<!-- =============== GROWING NETWORK / CTA =============== -->
<section class="dark-section loc-network">
<div class="container">
    <div class="split-grid">
            <div class="col-lg-6 col-12">
                <h2 style="color:#fff;">A Growing Network of Mastery</h2>
                <p style="color:rgba(255,255,255,.78);font-size:var(--body-lg);max-width:520px;">
                    Our presence across Northern and Eastern India ensures that aspiring professionals have access to specialized aesthetic modules and global certifications like CIDESCO.
                </p>
            </div>
            <div class="col-lg-6 col-12">
            <div class="stat-grid" style="margin-top:32px;max-width:520px;">
                <div class="stat">
                    <span class="num" style="color:var(--secondary-container);"><?= max(35, $totalCenters) ?>+</span>
                    <span class="label">Strategic Centers</span>
                </div>
                <div class="stat">
                    <span class="num" style="color:var(--secondary-container);">15K+</span>
                    <span class="label">Global Careers</span>
                </div>
        </div>
    </div>
    </div>
</div>
</section>

<!-- =============== LEAD GEN FORM =============== -->
<section class="loc-leadform">
<div class="container">
    <div class="text-center" style="max-width:720px;margin:0 auto 48px;">
        <h2 style="font-size:clamp(36px,5vw,64px);">Have Questions? Get in Touch!</h2>
        <p style="color:var(--on-surface-variant);font-size:var(--body-lg);">Connect with our regional admission experts to begin your transformation journey.</p>
    </div>
    <div class="loc-lead-card">
        <!-- <form id="contactForm">
            <div class="lead-grid">
                <div class="lead-field"><i class="bi bi-person-fill"></i><input type="text" name="name" placeholder="Name" required></div>
                <div class="lead-field"><i class="bi bi-envelope-fill"></i><input type="email" name="email" placeholder="Email Address" required></div>
                <div class="lead-field"><i class="bi bi-telephone-fill"></i><input type="tel" name="phone" placeholder="Phone"></div>
                <div class="lead-field"><i class="bi bi-building"></i><input type="text" name="city" placeholder="City"></div>
                <div class="lead-field"><i class="bi bi-geo-alt-fill"></i>
                    <select name="subject">
                        <option value="" disabled selected>Select Nearest Region</option>
                        <?php foreach (array_keys($grouped) as $st): ?>
                            <option><?= e($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="lead-field lead-field-full"><i class="bi bi-chat-dots-fill"></i><textarea name="message" rows="4" placeholder="How can we help you? Feel free to get in touch!"></textarea></div>
            </div>
            <?= niib_recaptcha_widget() ?>
            <button type="submit" class="btn btn-cta btn-lg" style="margin-top:24px;">Submit Inquiry</button>
            <div id="contactMsg" style="margin-top:16px;"></div>
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
        iframe.width = '100%'; iframe.height = '440'; iframe.frameBorder = '0';
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
</section>

<?php include 'includes/footer.php'; ?>

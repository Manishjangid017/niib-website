<?php
require __DIR__ . '/includes/init.php';
$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug='contact' AND status=1");
$stmt->execute();
$page = $stmt->fetch() ?: ['title'=>'Contact Us','banner_image'=>'','content'=>'','meta_title'=>'Contact Us','meta_description'=>'','meta_keywords'=>'','canonical_url'=>'','schema_code'=>''];

$page_seo['title'] = $page['meta_title'] ?: $page['title'].' - '.setting('site_name');
$page_seo['description'] = $page['meta_description'] ?: 'Get in touch with NIIB Institute for admissions, course information and consultations.';
$page_seo['keywords'] = $page['meta_keywords'];
if ($page['schema_code']) $page_seo['schema'] = $page['schema_code'];

niib_seo_set_breadcrumbs([
    ['name' => 'Home',    'url' => SITE_URL . '/'],
    ['name' => 'Contact'],
]);

niib_seo_add_schema([
    '@context'    => 'https://schema.org',
    '@type'       => 'ContactPage',
    'name'        => 'Contact NIIB Institute',
    'url'         => SITE_URL . '/contact',
    'description' => $page_seo['description'],
    'mainEntity'  => ['@id' => SITE_URL . '#organization'],
]);

include 'includes/header.php';
?>

<section class="page-titlebar">
    <div class="container">
        <nav class="pt-crumbs">
            <a href="<?= SITE_URL ?>/">Home</a>
            <i class="bi bi-chevron-right"></i>
            <span>Contact</span>
        </nav>
        <h1 class="pt-title">Begin Your <em>Journey</em></h1>
        <p class="pt-lede">Speak with our admissions team about courses, careers and clinical training. Your transformation starts here.</p>
    </div>
</section>

<section>
<div class="container">
    <div class="contact-grid" style="display:block;">
        <div class="contact-form">
            <h2 style="font-family:var(--font-headline);font-size:var(--headline-md);color:var(--primary);">Send us a message</h2>
            <!-- <form id="contactForm" style="margin-top:24px;">
                <div class="form-row two">
                    <div><label>Name *</label><input name="name" required></div>
                    <div><label>Email *</label><input type="email" name="email" required></div>
                </div>
                <div class="form-row two">
                    <div><label>Phone</label><input name="phone"></div>
                    <div><label>City</label><input name="city" placeholder="Your City"></div>
                </div>
                <div class="form-row">
                    <div><label>Subject</label><input name="subject"></div>
                </div>
                <div class="form-row">
                    <div><label>Message *</label><textarea name="message" rows="5" required></textarea></div>
                </div>
                <?= niib_recaptcha_widget() ?>
                <button class="btn btn-cta btn-lg">Send Message</button>
                <div id="contactMsg"></div>
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

    </div>
</section>

<!-- =============== MAP / VISIT US =============== -->
<section class="map-section surface-bg" style="padding-top:60px;">
<div class="container">
    <div class="section-title" style="margin-bottom:48px;">
        <span class="section-eyebrow">Where to find us</span>
        <h2>Visit Our Office</h2>
        <p style="color:var(--on-surface-variant);max-width:580px;margin:16px auto 0;">Walk into any campus during working hours, or schedule an admissions visit by appointment.</p>
    </div>

    <div class="map-wrap">
        <div class="map-cards">
            <div class="map-card active" data-lat="30.7333" data-lng="76.7794" data-q="SCO 60-61 Sector 34A Chandigarh Mukut Hospital">
                <span class="map-eyebrow">Corporate HQ</span>
                <h3>Chandigarh</h3>
                <p class="map-addr">SCO 60-61, Sector 34 A, Chandigarh,<br>Near Mukut Hospital</p>
                <div class="map-meta">
                    <a href="tel:+919646055557"><i class="bi bi-telephone-fill"></i> +91 96460 55557</a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=SCO+60-61+Sector+34A+Chandigarh" target="_blank" rel="noopener" class="map-link"><i class="bi bi-geo-alt-fill"></i> Get Directions</a>
                </div>
            </div>

            <div class="map-card" data-lat="31.3260" data-lng="75.5762" data-q="Shakti Tower Vishal Mega Mart Jalandhar">
                <span class="map-eyebrow">Flagship Campus</span>
                <h3>Jalandhar</h3>
                <p class="map-addr">Shakti Tower, 2nd Floor,<br>Above Vishal Mega Mart, Jalandhar</p>
                <div class="map-meta">
                    <a href="tel:+919914655557"><i class="bi bi-telephone-fill"></i> +91 99146 55557</a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=Shakti+Tower+Vishal+Mega+Mart+Jalandhar" target="_blank" rel="noopener" class="map-link"><i class="bi bi-geo-alt-fill"></i> Get Directions</a>
                </div>
            </div>
        </div>

        <div class="map-frame">
            <iframe id="campusMap"
                src="https://maps.google.com/maps?q=SCO%2060-61%20Sector%2034A%20Chandigarh%20Mukut%20Hospital&t=&z=16&ie=UTF8&iwloc=&output=embed"
                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</div>
</section>

<script>
(function(){
    var cards = document.querySelectorAll('.map-card');
    var iframe = document.getElementById('campusMap');
    cards.forEach(function(card){
        card.addEventListener('click', function(){
            cards.forEach(function(c){ c.classList.remove('active'); });
            card.classList.add('active');
            var q = encodeURIComponent(card.dataset.q || '');
            iframe.src = 'https://maps.google.com/maps?q=' + q + '&t=&z=16&ie=UTF8&iwloc=&output=embed';
        });
    });
})();
</script>

<?php include 'includes/footer.php'; ?>

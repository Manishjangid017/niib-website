<?php if (!defined('NIIB_INIT')) { http_response_code(403); exit; }
$footerMenu = get_menu_items('footer');
?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="brand-block">
                <a href="<?= SITE_URL ?>/" class="brand"><?= e(setting('site_name','NIIB Institute')) ?></a>
                <p><?= e(setting('footer_text','Defining the professional and artistic standards for the modern beauty professional.')) ?></p>

                <?php if (setting('site_phone') || setting('site_email') || setting('site_address')): ?>
                <div class="footer-contact">
                    <?php if (setting('site_phone')): ?>
                    <a href="tel:<?= e(setting('site_phone')) ?>" class="contact-row">
                        <span class="contact-icon"><i class="bi bi-telephone-fill"></i></span>
                        <span class="contact-text">
                            <span class="contact-label">Call us</span>
                            <span class="contact-value"><?= e(setting('site_phone')) ?></span>
                        </span>
                    </a>
                    <?php endif; ?>
                    <?php if (setting('site_email')): ?>
                    <a href="mailto:<?= e(setting('site_email')) ?>" class="contact-row">
                        <span class="contact-icon"><i class="bi bi-envelope-fill"></i></span>
                        <span class="contact-text">
                            <span class="contact-label">Email us</span>
                            <span class="contact-value"><?= e(setting('site_email')) ?></span>
                        </span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="footer-socials">
                    <?php foreach (get_social_links() as $s): ?>
                        <a href="<?= e($s['url']) ?>" target="_blank" rel="noopener" title="<?= e($s['platform']) ?>"><i class="bi <?= e($s['icon']) ?>"></i></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="footer-col">
                <p style="text-transform:uppercase; color:white;"><b>Courses </b></p>
                <ul>
                    <?php
                    try {
                        $popular = db()->query("SELECT title, slug FROM courses WHERE status=1 ORDER BY is_featured DESC, id DESC LIMIT 5")->fetchAll();
                        foreach ($popular as $c): ?>
                        <li><a href="<?= SITE_URL ?>/course/<?= e($c['slug']) ?>"><?= e($c['title']) ?></a></li>
                    <?php endforeach; } catch (Throwable $e) {} ?>
                </ul>
            </div>

            <div class="footer-col">
             <p style="text-transform:uppercase; color:white;"><b>Institution </b></p>
                <ul>
                    <li><a href="<?= SITE_URL ?>/about">About Us</a></li>
                    <li><a href="<?= SITE_URL ?>/placement">Placements</a></li>
                    <li><a href="<?= SITE_URL ?>/franchise">Franchise</a></li>
                    <li><a href="<?= SITE_URL ?>/blog">Blog</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <p style="text-transform:uppercase; color:white;"><b>Quick Links</b></p>
                <ul>
                    <?php foreach (($footerMenu[0] ?? []) as $item):
                        $url = $item['url'];
                        if (!preg_match('~^https?://~i', $url) && strpos($url, '/') !== 0) $url = SITE_URL . '/' . ltrim($url, '/');
                    ?>
                    <li><a href="<?= e($url) ?>"><?= e($item['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <?php
        // Recovered local/service pages — internal-link directory
        try { $fdPages = db()->query("SELECT slug,title FROM pages WHERE page_type='informative' AND status=1 AND id>64 ORDER BY title")->fetchAll(); }
        catch (Throwable $e) { $fdPages = []; }
        if ($fdPages):
            $fdSvc = ['bridal-makeup','hydra-facial','keratin-treatment','rebonding','botox-hair-therapy','stone-massage','bamboo-massage','hair-extensions','nail-art-nail-extension','laser-courses','short-term-courses','certificate-courses','certificate-in-reflexology','certificate-in-rejuve','certificate-in-sport-massage','certificate-in-stone-therapy','certificate-in-western-spa-therapy','certificate-in-salon-management','certificate-in-salon-marketing'];
            $fdLoc = []; $fdSv = [];
            foreach ($fdPages as $rp) { if (in_array($rp['slug'], $fdSvc, true)) { $fdSv[] = $rp; } else { $fdLoc[] = $rp; } }
        ?>
        <div class="footer-directory">
            <?php if ($fdLoc): ?>
            <div class="fd-block">
                <h4>Beauty Courses &amp; Institutes by City</h4>
                <ul class="fd-links">
                    <?php foreach ($fdLoc as $p): ?><li><a href="<?= SITE_URL ?>/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></li><?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <?php if ($fdSv): ?>
            <div class="fd-block">
                <h4>Our Services &amp; Specialised Courses</h4>
                <ul class="fd-links">
                    <?php foreach ($fdSv as $p): ?><li><a href="<?= SITE_URL ?>/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></li><?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        <style>
        .footer-directory{ border-top:1px solid rgba(255,255,255,.08); margin-top:36px; padding-top:28px; }
        .footer-directory h4{ font-size:14px; color:#fff; letter-spacing:.02em; margin:0 0 14px; }
        .footer-directory .fd-block{ margin-bottom:22px; }
        .footer-directory .fd-links{ columns:4; column-gap:26px; list-style:none; padding:0; margin:0; }
        @media(max-width:900px){ .footer-directory .fd-links{ columns:2; } }
        @media(max-width:520px){ .footer-directory .fd-links{ columns:1; } }
        .footer-directory .fd-links li{ break-inside:avoid; margin:0 0 6px; }
        .footer-directory .fd-links a{ font-size:12.5px; line-height:1.5; color:rgba(255,255,255,.58); text-decoration:none; }
        .footer-directory .fd-links a:hover{ color:var(--secondary-container,#fe5e1e); }
        </style>

        <?php endif; ?>

        <div class="footer-bottom">
            <?= e(setting('copyright_text','© ' . date('Y') . ' NIIB Institute. Excellence in Beauty & Professional Education.')) ?>
        </div>
    </div>
</footer>

<!-- Floating WhatsApp + Call (a page may override the number via $page_whatsapp / $page_phone) -->
<?php $floatWa = $page_whatsapp ?? setting('site_whatsapp'); if ($floatWa): ?>
<a href="https://wa.me/<?= e(preg_replace('/[^0-9]/','', $floatWa)) ?>" class="float-whatsapp" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
<?php endif; ?>
<?php $floatPhone = $page_phone ?? setting('site_phone'); if ($floatPhone): ?>
<a href="tel:<?= e(preg_replace('/[^0-9+]/','', $floatPhone)) ?>" class="float-call" aria-label="Call"><i class="bi bi-telephone-fill"></i></a>
<?php endif; ?>

<script src="<?= ASSETS_URL ?>/js/main.js?v=7"></script>
<?= render_footer_codes() ?>
<script><?= render_custom_js() ?></script>

<?php
try {
    $popup = db()->query("SELECT * FROM popup_settings WHERE status=1 ORDER BY id DESC LIMIT 1")->fetch();
    if ($popup):
        // Encode popup data as JSON — JS will build and inject the DOM after LCP fires.
        // This keeps popup HTML out of the initial parse so the hero (LCP element)
        // is not competing with the popup image for bandwidth on first load.
        $popupJson = json_encode([
            'image'       => $popup['image'] ? img_url($popup['image']) : '',
            'title'       => $popup['title'],
            'description' => $popup['description'],
            'button_text' => $popup['button_text'] ?? '',
            'button_url'  => !empty($popup['button_url']) ? niib_link($popup['button_url']) : '',
            'show_once'   => (int)$popup['show_once'],
            'delay'       => (int)$popup['delay_seconds'] * 1000,
            'img_alt'     => $popup['title'] ?: 'NIIB Institute offer',
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES);
?>
<script>
(function(){
    var cfg = <?= $popupJson ?>;
    if (cfg.show_once && localStorage.getItem('niib_popup_seen')) return;

    function buildPopup() {
        // Overlay
        var overlay = document.createElement('div');
        overlay.className = 'popup-overlay';
        overlay.id = 'sitePopup';

        // Inner box
        var box = document.createElement('div');
        box.className = 'popup';

        // Close button
        var btn = document.createElement('button');
        btn.className = 'close';
        btn.innerHTML = '&times;';
        btn.setAttribute('aria-label', 'Close');
        btn.onclick = function(){ overlay.classList.remove('active'); };
        box.appendChild(btn);

        // Image (lazy — not competing with LCP hero)
        if (cfg.image) {
            var img = document.createElement('img');
            img.src = cfg.image;
            img.alt = cfg.img_alt;
            img.loading = 'lazy';
            img.decoding = 'async';
            box.appendChild(img);
        }

        // Title
        if (cfg.title) {
            var h4 = document.createElement('h4');
            h4.textContent = cfg.title;
            box.appendChild(h4);
        }

        // Description
        if (cfg.description) {
            var p = document.createElement('p');
            p.style.color = 'var(--on-surface-variant)';
            p.textContent = cfg.description;
            box.appendChild(p);
        }

        // CTA button
        if (cfg.button_text && cfg.button_url) {
            var a = document.createElement('a');
            a.href = cfg.button_url;
            a.className = 'btn btn-cta';
            a.style.cssText = 'display:block;text-align:center';
            a.textContent = cfg.button_text;
            box.appendChild(a);
        }

        overlay.appendChild(box);
        // Close on backdrop click
        overlay.addEventListener('click', function(e){
            if (e.target === overlay) overlay.classList.remove('active');
        });
        document.body.appendChild(overlay);

        // Show after delay
        setTimeout(function(){
            overlay.classList.add('active');
            if (cfg.show_once) localStorage.setItem('niib_popup_seen', '1');
        }, cfg.delay);
    }

    // Wait for LCP/load event before injecting popup DOM
    if (document.readyState === 'complete') {
        setTimeout(buildPopup, 300);
    } else {
        window.addEventListener('load', function(){ setTimeout(buildPopup, 300); });
    }
})();
</script>
<?php endif; } catch (Throwable $e) {} ?>

<?php include __DIR__ . '/popup-enquiry.php'; ?>

</body>
</html>

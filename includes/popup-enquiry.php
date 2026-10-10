<?php
/**
 * Site-wide compact enquiry popup.
 * Included from footer.php, so it renders on every page.
 *
 * SELF-CONTAINED: it binds its own submit handler and posts to /api/enquiry.php.
 * It does NOT rely on handleForm() in main.js — that dependency was the original
 * bug (main.js runs before this markup exists in the footer, so the listener was
 * never attached and the form fell back to a native page reload).
 *
 * Enquiries are delivered to whatever address is set in Admin → SMTP
 * ("Enquiries delivered to" = smtp_to_admin), via api/enquiry.php → niib_mail_notify_admin().
 */
if (!defined('NIIB_INIT')) { http_response_code(403); exit; }

/* Locations for the dropdown — dynamic, grouped by state. */
$peLocs = [];
try {
    foreach (db()->query("SELECT city, state FROM locations WHERE status=1 ORDER BY state, city") as $l) {
        $peLocs[$l['state'] ?: 'Other'][] = $l['city'];
    }
} catch (Throwable $e) {}

/* On the franchise page (which sets $popup_franchise_form before the footer include),
   the popup shows the Franchise Inquiry form instead of the generic enquiry form. */
$peFranchise = !empty($popup_franchise_form);
?>
<style>
#enquiryPopup .pe-popup{ max-width:420px; padding:28px 26px 24px; }
#enquiryPopup .pe-popup h4{ margin-bottom:4px; }
#enquiryPopup .pe-sub{ color:var(--on-surface-variant); font-size:14px; margin-bottom:18px; }
#enquiryPopup .pe-grid{ display:grid; grid-template-columns:1fr 1fr; gap:10px; }
#enquiryPopup .pe-grid .pe-full{ grid-column:1 / -1; }
#enquiryPopup .pe-field label{ display:block; font-size:12px; font-weight:600; letter-spacing:.02em; color:var(--on-surface-variant); margin-bottom:4px; }
#enquiryPopup .pe-field input,
#enquiryPopup .pe-field select{ width:100%; padding:9px 11px; font-size:14px; border:1px solid var(--outline); background:#fff; color:var(--on-surface); }
#enquiryPopup .pe-field input:focus,
#enquiryPopup .pe-field select:focus{ outline:none; border-color:var(--primary); }
#enquiryPopup .pe-popup button[type=submit]{ width:100%; margin-top:14px; }
#enquiryPopup .pe-popup button[disabled]{ opacity:.6; cursor:default; }
#enquiryPopup #popupEnquiryMsg{ margin-top:10px; font-size:13px; }
#enquiryPopup #popupEnquiryMsg:empty{ margin-top:0; }
@media (max-width:480px){
  #enquiryPopup .pe-grid{ grid-template-columns:1fr; }
  #enquiryPopup .pe-popup{ padding:24px 18px 20px; }
}
</style>

<div class="popup-overlay" id="enquiryPopup" role="dialog" aria-modal="true" aria-labelledby="peTitle">
  <div class="popup pe-popup">
    <button type="button" class="close" data-pe-close aria-label="Close">&times;</button>
    <h4 id="peTitle"><?= e($peFranchise ? 'Franchise Inquiry' : setting('popup_form_title', 'Book a Free Consultation')) ?></h4>
    <p class="pe-sub"><?= e($peFranchise ? 'Share your details and our franchise team will call you back.' : setting('popup_form_subtitle', 'Share your details and our counsellor will call you back.')) ?></p>

    <form id="popupEnquiryForm">
      <input type="hidden" name="source" value="<?= $peFranchise ? 'Franchise Popup' : 'Website Popup' ?>">
      <?php if ($peFranchise): ?><input type="hidden" name="course_name" value="Franchise Enquiry"><?php endif; ?>
      <div class="pe-grid">
        <div class="pe-field">
          <label for="pe-name">Name *</label>
          <input id="pe-name" name="name" required placeholder="Your name">
        </div>
        <div class="pe-field">
          <label for="pe-phone">Phone *</label>
          <input id="pe-phone" name="phone" type="tel" required placeholder="+91">
        </div>
        <div class="pe-field pe-full">
          <label for="pe-email">Email *</label>
          <input id="pe-email" name="email" type="email" required placeholder="example@email.com">
        </div>
        <?php if ($peFranchise): ?>
        <div class="pe-field pe-full">
          <label for="pe-city">City of Interest</label>
          <input id="pe-city" name="city" type="text" placeholder="Which city are you looking at?">
        </div>
        <div class="pe-field pe-full">
          <label for="pe-invest">Investment Range</label>
          <select id="pe-invest" name="message">
            <option value="₹30 - ₹40 Lakhs">₹30 - ₹40 Lakhs</option>
            <option value="₹40 - ₹50 Lakhs">₹40 - ₹50 Lakhs</option>
            <option value="Above ₹50 Lakhs">Above ₹50 Lakhs</option>
          </select>
        </div>
        <?php else: ?>
        <div class="pe-field pe-full">
          <label for="pe-location">Location</label>
          <select id="pe-location" name="city">
            <option value="">Select your nearest location</option>
            <?php foreach ($peLocs as $state => $cities): ?>
              <optgroup label="<?= e($state) ?>">
                <?php foreach ($cities as $city): ?>
                  <option value="<?= e($city) ?>"><?= e($city) ?></option>
                <?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
      </div>
      <?= niib_recaptcha_widget() ?>
      <button type="submit" class="btn btn-cta"><?= $peFranchise ? 'Request Call Back' : 'Request a Call Back' ?></button>
      <div id="popupEnquiryMsg"></div>
    </form>
  </div>
</div>

<?php
/* The promo popup (popup_settings) renders from footer.php too. Read its timing so
   this form never opens into it and gets covered a moment later. */
$pePromo = ['active' => false, 'delay' => 0, 'once' => false];
try {
    if ($r = db()->query("SELECT delay_seconds, show_once FROM popup_settings WHERE status=1 ORDER BY id DESC LIMIT 1")->fetch()) {
        $pePromo = ['active' => true, 'delay' => (int)$r['delay_seconds'] * 1000, 'once' => (bool)$r['show_once']];
    }
} catch (Throwable $e) {}
?>
<script>
(function(){
  var overlay = document.getElementById('enquiryPopup');
  if (!overlay) return;
  var form = document.getElementById('popupEnquiryForm');
  var msg  = document.getElementById('popupEnquiryMsg');
  var btn  = form ? form.querySelector('button[type=submit]') : null;

  var DELAY    = <?= (int)setting('popup_form_delay', 3) * 1000 ?>;
  var PROMO    = <?= json_encode($pePromo) ?>;
  var ENDPOINT = <?= json_encode(SITE_URL . '/api/enquiry.php') ?>;

  // If the promo popup is still going to fire this pageview, queue up behind it.
  var promoWillShow = PROMO.active && !(PROMO.once && localStorage.getItem('niib_popup_seen'));
  if (promoWillShow) DELAY = Math.max(DELAY, PROMO.delay + 800);

  function open(){ overlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
  function close(){ overlay.classList.remove('active'); document.body.style.overflow = ''; try { localStorage.setItem('niib_popup_dismissed', String(Date.now())); } catch(e){} }

  // Don't stack two overlays — wait for any other open popup to be dismissed.
  function otherPopupOpen(){
    var o = document.querySelectorAll('.popup-overlay.active');
    for (var i = 0; i < o.length; i++) { if (o[i] !== overlay) return true; }
    return false;
  }
  function openWhenClear(){
    if (!otherPopupOpen()) { open(); return; }
    var tries = 0;
    var t = setInterval(function(){
      if (!otherPopupOpen()) { clearInterval(t); open(); }
      else if (++tries > 120) { clearInterval(t); }   // give up after ~60s
    }, 500);
  }

  // 1-hour cooldown: once the user closes the popup, it stays closed for 1 hour.
  var PE_COOLDOWN_MS = 60 * 60 * 1000;
  var peLastClosed = 0;
  try { peLastClosed = parseInt(localStorage.getItem('niib_popup_dismissed') || '0', 10) || 0; } catch(e){}
  if (!peLastClosed || (Date.now() - peLastClosed) >= PE_COOLDOWN_MS) {
    setTimeout(openWhenClear, DELAY);
  }

  overlay.addEventListener('click', function(e){
    if (e.target === overlay || e.target.hasAttribute('data-pe-close')) close();
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && overlay.classList.contains('active')) close();
  });

  /* ---- reCAPTCHA token (uses the site-wide window.NIIB_RECAPTCHA / grecaptcha) ---- */
  function getToken(){
    var cfg = window.NIIB_RECAPTCHA;
    if (!cfg || !window.grecaptcha) return Promise.resolve('');
    if (cfg.mode === 'v3') {
      return new Promise(function(resolve){
        try {
          grecaptcha.ready(function(){
            grecaptcha.execute(cfg.siteKey, { action: cfg.action || 'submit' })
              .then(function(tok){ resolve(tok || ''); })
              .catch(function(){ resolve(''); });
          });
        } catch (e) { resolve(''); }
      });
    }
    // v2
    return Promise.resolve(
      (typeof grecaptcha.getResponse === 'function') ? (grecaptcha.getResponse() || '') : ''
    );
  }

  function setMsg(kind, text){
    if (!msg) return;
    if (kind === 'info') {
      msg.innerHTML = '<div class="alert" style="border-color:var(--outline);background:rgba(0,0,0,.03);">' + text + '</div>';
    } else {
      msg.innerHTML = '<div class="alert alert-' + kind + '">' + text + '</div>';
    }
  }

  /* ---- own submit handler — no dependency on main.js ---- */
  var busy = false;
  if (form) form.addEventListener('submit', function(e){
    e.preventDefault();
    if (busy) return;
    busy = true;
    if (btn) btn.disabled = true;
    setMsg('info', 'Sending…');

    getToken().then(function(token){
      var cfg = window.NIIB_RECAPTCHA;
      if (cfg && cfg.mode === 'v2' && !token) {
        setMsg('error', 'Please complete the reCAPTCHA challenge.');
        busy = false; if (btn) btn.disabled = false; return;
      }
      var fd = new FormData(form);
      if (token) fd.set('g-recaptcha-response', token);

      return fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(data){
          setMsg(data.ok ? 'success' : 'error', data.message || (data.ok ? 'Thank you!' : 'Something went wrong.'));
          if (data.ok) {
            form.reset();
            if (window.grecaptcha && typeof grecaptcha.reset === 'function' &&
                window.NIIB_RECAPTCHA && window.NIIB_RECAPTCHA.mode === 'v2') grecaptcha.reset();
            setTimeout(close, 2600);
          }
        })
        .catch(function(){ setMsg('error', 'Network error. Please try again.'); })
        .then(function(){ busy = false; if (btn) btn.disabled = false; });
    });
  });
})();
</script>

<?php if (defined('NIIB_LOC_STYLES')) return; define('NIIB_LOC_STYLES', 1); ?>
<style>
/* ================================================================
   Location Page — scoped CSS (#loc-template only)
   ================================================================ */
#loc-template {
  --lp-primary:#170338;
  --lp-secondary:#fe5e1e;
  --lp-secondary-dark:#ab3600;
  --lp-secondary-fixed:#ffdbcf;
  --lp-bg:#fcf9f8;
  --lp-text:#1c1b1b;
  --lp-text-soft:#49454e;
  --lp-surface-low:#f6f3f2;
  --lp-surface-high:#eae7e7;
  --lp-outline:#cbc4d0;
  --lp-white:#ffffff;
  --lp-container:1280px;
  --lp-pad-x:20px;
  --lp-gap:24px;
  --lp-section:56px;

  font-family:'Manrope',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
  color:var(--lp-text);
  background:var(--lp-bg);
  font-size:16px;
  line-height:1.6;
}
@media (min-width:768px){ #loc-template { --lp-pad-x:80px; --lp-section:72px; } }

#loc-template *, #loc-template *::before, #loc-template *::after { box-sizing:border-box; }
#loc-template img { max-width:100%; height:auto; display:block; }
#loc-template a { color:inherit; text-decoration:none; }
#loc-template h1,#loc-template h2,#loc-template h3,#loc-template h4 { font-family:'Bodoni Moda',Georgia,serif; margin:0; line-height:1.2; color:var(--lp-primary); }
#loc-template p { margin:0; }
#loc-template button { font-family:inherit; cursor:pointer; }
#loc-template .lp-container { max-width:var(--lp-container); margin:0 auto; padding:0 var(--lp-pad-x); }
#loc-template .lp-section { padding-top:var(--lp-section); padding-bottom:var(--lp-section); }

#loc-template .material-symbols-outlined {
  font-family:'Material Symbols Outlined';
  font-weight:normal; font-style:normal;
  line-height:1; letter-spacing:normal; text-transform:none;
  display:inline-block; white-space:nowrap; word-wrap:normal; direction:ltr;
  font-variation-settings:'FILL' 0,'wght' 300,'GRAD' 0,'opsz' 24;
}

/* ---------- HERO ---------- */
#loc-template .lp-hero {
  position:relative;
  min-height:80vh;
  height:auto;
  padding:56px 0;
  display:flex; align-items:center;
  overflow:hidden;
  color:#fff;
}
#loc-template .lp-hero-bg { position:absolute; inset:0; z-index:0; }
#loc-template .lp-hero-bg img { width:100%; height:100%; object-fit:cover; }
#loc-template .lp-hero-bg::after {
  content:""; position:absolute; inset:0;
  background:linear-gradient(180deg, rgba(23,3,56,.55), rgba(23,3,56,.35));
}
#loc-template .lp-hero-inner { position:relative; z-index:1; width:100%; }
#loc-template .lp-hero-content { max-width:640px; }
#loc-template .lp-hero-badge {
  font-size:14px; font-weight:700; letter-spacing:.2em; text-transform:uppercase;
  color:var(--lp-secondary-fixed); display:block; margin-bottom:16px;
}
#loc-template .lp-hero-title {
  font-size:clamp(40px, 6vw, 72px); font-weight:700; letter-spacing:-.02em; color:#fff;
  margin-bottom:32px;
}
#loc-template .lp-hero-ctas { display:flex; gap:16px; flex-direction:column; }
@media (min-width:640px){ #loc-template .lp-hero-ctas { flex-direction:row; } }
#loc-template .lp-btn {
  display:inline-flex; align-items:center; justify-content:center;
  padding:14px 36px; font-size:14px; font-weight:700;
  letter-spacing:.1em; text-transform:uppercase;
  transition:all .25s ease; border:1px solid transparent; cursor:pointer;
}
#loc-template .lp-btn-primary { background:var(--lp-secondary); color:#fff; }
#loc-template .lp-btn-primary:hover { background:var(--lp-secondary-dark); transform:translateY(-2px); box-shadow:0 12px 24px rgba(0,0,0,.18); }
#loc-template .lp-btn-outline { border-color:#fff; color:#fff; background:transparent; }
#loc-template .lp-btn-outline:hover { background:#fff; color:var(--lp-primary); }

/* ---------- Hero enquiry form (right side) ---------- */
#loc-template .lp-hero-grid { display:grid; grid-template-columns:1fr; gap:36px; align-items:center; width:100%; }
@media (min-width:992px){ #loc-template .lp-hero-grid { grid-template-columns:1.1fr .9fr; gap:48px; } }
#loc-template .lp-hero-formwrap { display:flex; justify-content:flex-end; }
#loc-template .lp-hero-form { width:100%; max-width:420px; background:rgba(255,255,255,.97); color:var(--lp-text); padding:26px 24px 22px; border-top:4px solid var(--lp-secondary); box-shadow:0 24px 60px rgba(0,0,0,.30); }
#loc-template .lp-hero-form-title { font-family:'Bodoni Moda',serif; font-size:24px; font-weight:500; color:var(--lp-primary); margin-bottom:4px; line-height:1.2; }
#loc-template .lp-hero-form-sub { font-size:14px; color:var(--lp-text-soft); margin-bottom:16px; }
#loc-template .lp-hf-field { margin-bottom:11px; }
#loc-template .lp-hf-field label { display:block; font-size:12px; font-weight:600; letter-spacing:.02em; color:var(--lp-text-soft); margin-bottom:4px; text-transform:none; }
#loc-template .lp-hf-field input, #loc-template .lp-hf-field select { width:100%; padding:11px 12px; font-size:14px; border:1px solid var(--lp-outline); background:#fff; color:var(--lp-text); }
#loc-template .lp-hf-field input:focus, #loc-template .lp-hf-field select:focus { outline:none; border-color:var(--lp-secondary); }
#loc-template .lp-hf-submit { width:100%; margin-top:6px; background:var(--lp-secondary); color:#fff; border:none; padding:13px; font-size:13px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; cursor:pointer; transition:background .2s ease; }
#loc-template .lp-hf-submit:hover { background:var(--lp-secondary-dark); }
#loc-template .lp-hf-submit[disabled] { opacity:.6; cursor:default; }
#loc-template .lp-hf-msg { margin-top:10px; font-size:13px; }
@media (max-width:991px){
  #loc-template .lp-hero-formwrap { justify-content:stretch; }
  #loc-template .lp-hero-form { max-width:none; }
}

/* ---------- INTRO ---------- */
#loc-template .lp-intro-grid { display:grid; grid-template-columns:1fr; gap:var(--lp-gap); align-items:center; }
@media (min-width:900px){ #loc-template .lp-intro-grid { grid-template-columns:7fr 5fr; } }
/* Full-width content when the intro has no side image */
#loc-template .lp-intro-grid:not(:has(.lp-intro-image-wrap)){ grid-template-columns:1fr; }
#loc-template .lp-h2 {
  font-size:clamp(32px, 4.2vw, 48px); font-weight:600; color:var(--lp-primary); margin-bottom:24px;
}
#loc-template .lp-lede { font-size:18px; color:var(--lp-text-soft); line-height:1.7; margin-bottom:16px; }
#loc-template .lp-stats { display:grid; grid-template-columns:1fr 1fr; gap:var(--lp-gap); margin-top:24px; }
#loc-template .lp-stat { border-left:4px solid var(--lp-secondary); padding-left:16px; }
#loc-template .lp-stat-value { display:block; font-family:'Bodoni Moda',serif; font-size:32px; font-weight:500; color:var(--lp-primary); }
#loc-template .lp-stat-label { font-size:14px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--lp-text-soft); }
#loc-template .lp-intro-image-wrap { position:relative; }
#loc-template .lp-intro-image { aspect-ratio:4/5; overflow:hidden; background:var(--lp-surface-high); }
#loc-template .lp-intro-image img { width:100%; height:100%; object-fit:cover; }
#loc-template .lp-intro-quote {
  position:absolute; bottom:-24px; left:-24px; background:var(--lp-primary); color:#fff;
  padding:20px 24px; max-width:240px; font-style:italic; font-size:14px; letter-spacing:.05em;
}
@media (max-width:899px){ #loc-template .lp-intro-quote { display:none; } }

/* ---------- COURSES ---------- */
#loc-template .lp-courses { background:var(--lp-surface-low); }
#loc-template .lp-section-head {
  display:flex; justify-content:space-between; align-items:flex-end; gap:24px;
  margin-bottom:40px; flex-wrap:wrap;
}
#loc-template .lp-tagline { font-size:14px; font-weight:700; letter-spacing:.2em; text-transform:uppercase; color:var(--lp-secondary-dark); display:block; margin-bottom:8px; }
#loc-template .lp-section-actions { display:flex; align-items:center; gap:24px; }
#loc-template .lp-link { font-size:14px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--lp-primary); text-decoration:underline; text-underline-offset:6px; }
#loc-template .lp-carousel-nav { display:none; gap:8px; }
@media (min-width:768px){ #loc-template .lp-carousel-nav { display:flex; } }
#loc-template .lp-arrow {
  width:42px; height:42px; display:inline-flex; align-items:center; justify-content:center;
  border:1px solid var(--lp-outline); background:#fff; color:var(--lp-primary);
  transition:all .2s ease; border-radius:0;
}
#loc-template .lp-arrow:hover { background:var(--lp-primary); color:#fff; }
#loc-template .lp-carousel { position:relative; }
#loc-template .lp-carousel-track {
  display:flex; gap:var(--lp-gap);
  overflow-x:auto; scroll-snap-type:x mandatory; scroll-behavior:smooth;
  padding-bottom:8px; scrollbar-width:none; -ms-overflow-style:none;
}
#loc-template .lp-carousel-track::-webkit-scrollbar { display:none; }
#loc-template .lp-course-card { flex:0 0 100%; scroll-snap-align:start; display:block; }
@media (min-width:768px){ #loc-template .lp-course-card { flex-basis:calc(50% - 12px); } }
@media (min-width:1024px){ #loc-template .lp-course-card { flex-basis:calc(33.333% - 16px); } }
#loc-template .lp-course-img { aspect-ratio:2/3; overflow:hidden; position:relative; margin-bottom:16px; background:var(--lp-surface-high); }
#loc-template .lp-course-img img { width:100%; height:100%; object-fit:cover; transition:transform .7s ease; }
#loc-template .lp-course-card:hover .lp-course-img img { transform:scale(1.1); }
#loc-template .lp-course-overlay {
  position:absolute; inset:0; background:rgba(23,3,56,.2);
  display:flex; align-items:center; justify-content:center;
  opacity:0; transition:opacity .3s ease;
}
#loc-template .lp-course-card:hover .lp-course-overlay { opacity:1; }
#loc-template .lp-course-overlay span { background:#fff; color:var(--lp-primary); padding:12px 24px; font-size:14px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; }
#loc-template .lp-course-label { display:block; font-size:14px; font-weight:700; letter-spacing:.2em; text-transform:uppercase; color:var(--lp-text-soft); margin-bottom:6px; }
#loc-template .lp-course-title { font-family:'Bodoni Moda',serif; font-size:24px; font-weight:500; color:var(--lp-primary); }
#loc-template .lp-arrow-mobile {
  position:absolute; top:50%; transform:translateY(-50%);
  width:42px; height:42px; border-radius:50%;
  background:rgba(255,255,255,.95); border:1px solid var(--lp-outline); color:var(--lp-primary);
  display:flex; align-items:center; justify-content:center; box-shadow:0 8px 24px rgba(0,0,0,.18); z-index:5;
}
#loc-template .lp-arrow-mobile.prev { left:8px; }
#loc-template .lp-arrow-mobile.next { right:8px; }
@media (min-width:768px){ #loc-template .lp-arrow-mobile { display:none; } }

/* ---------- CAREER ---------- */
#loc-template .lp-career-head { text-align:center; margin-bottom:40px; }
#loc-template .lp-career-head p { font-size:18px; color:var(--lp-text-soft); max-width:640px; margin:16px auto 0; }
#loc-template .lp-career-grid { display:grid; grid-template-columns:1fr; gap:16px; }
@media (min-width:900px){
  #loc-template .lp-career-grid { grid-template-columns:repeat(4, 1fr); grid-template-rows:1fr 1fr; height:600px; }
}
#loc-template .lp-career-feature {
  background:var(--lp-primary); color:#fff; padding:32px;
  display:flex; flex-direction:column; justify-content:flex-end;
  position:relative; overflow:hidden;
  min-height:240px;
}
@media (min-width:900px){ #loc-template .lp-career-feature { grid-column:span 2; grid-row:span 2; } }
#loc-template .lp-career-feature .lp-feature-icon {
  position:absolute; top:24px; right:24px; font-size:120px; opacity:.12; transition:opacity .3s;
}
#loc-template .lp-career-feature:hover .lp-feature-icon { opacity:.22; }
#loc-template .lp-career-feature h4 { font-size:32px; color:#fff; margin-bottom:8px; }
#loc-template .lp-career-feature p { opacity:.85; }
#loc-template .lp-career-card {
  padding:28px; display:flex; flex-direction:column; justify-content:center;
}
#loc-template .lp-career-card .material-symbols-outlined { font-size:28px; margin-bottom:14px; color:var(--lp-primary); }
#loc-template .lp-career-card h4 { font-family:'Manrope',sans-serif; font-size:14px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--lp-primary); margin-bottom:8px; }
#loc-template .lp-career-card p { font-size:14px; color:var(--lp-text-soft); }
#loc-template .lp-career-card.v1 { background:var(--lp-surface-high); border-bottom:4px solid var(--lp-secondary); }
#loc-template .lp-career-card.v2,
#loc-template .lp-career-card.v3 { background:#fff; border:1px solid rgba(203,196,208,.4); box-shadow:0 2px 8px rgba(0,0,0,.04); }
#loc-template .lp-career-card.v4 { background:var(--lp-secondary); color:#fff; }
#loc-template .lp-career-card.v4 .material-symbols-outlined,
#loc-template .lp-career-card.v4 h4 { color:#fff; }
#loc-template .lp-career-card.v4 p { color:#fff; opacity:.9; }

/* ---------- GALLERY ---------- */
#loc-template .lp-gallery { background:linear-gradient(135deg,#170338 0%,#2d1b4e 100%); color:#fff; }
#loc-template .lp-gallery h2 { color:#fff; margin-bottom:16px; }
#loc-template .lp-gallery .lp-section-head { align-items:center; }
#loc-template .lp-gallery-sub { font-size:18px; opacity:.85; max-width:560px; }
#loc-template .lp-gallery-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:16px; }
@media (min-width:768px){ #loc-template .lp-gallery-grid { grid-template-columns:repeat(3, 1fr); } }
#loc-template .lp-gallery-item { aspect-ratio:1/1; overflow:hidden; }
#loc-template .lp-gallery-item img { width:100%; height:100%; object-fit:cover; cursor:zoom-in; transition:filter .5s ease, transform .5s ease; }
#loc-template .lp-gallery-item img:hover { filter:grayscale(1); }

/* Lightbox */
#loc-template-lightbox {
  position:fixed; inset:0; z-index:9999;
  background:rgba(0,0,0,.92);
  display:none; align-items:center; justify-content:center;
  backdrop-filter:blur(6px);
}
#loc-template-lightbox.is-open { display:flex; }
#loc-template-lightbox img { max-width:92vw; max-height:88vh; object-fit:contain; box-shadow:0 20px 60px rgba(0,0,0,.6); }
#loc-template-lightbox .lb-btn {
  position:absolute; background:rgba(255,255,255,.1); border:0; color:#fff;
  width:48px; height:48px; border-radius:50%;
  display:flex; align-items:center; justify-content:center; cursor:pointer; transition:background .2s;
}
#loc-template-lightbox .lb-btn:hover { background:rgba(255,255,255,.22); }
#loc-template-lightbox .lb-close { top:20px; right:20px; background:transparent; }
#loc-template-lightbox .lb-prev { left:20px; top:50%; transform:translateY(-50%); }
#loc-template-lightbox .lb-next { right:20px; top:50%; transform:translateY(-50%); }
#loc-template-lightbox .lb-counter { position:absolute; bottom:20px; left:50%; transform:translateX(-50%); color:rgba(255,255,255,.8); font-size:14px; letter-spacing:.1em; }

/* ---------- ACCREDITATIONS ---------- */
#loc-template .lp-accred { background:#fff; border-top:1px solid rgba(203,196,208,.4); border-bottom:1px solid rgba(203,196,208,.4); padding:48px 0; text-align:center; }
#loc-template .lp-accred-label { font-size:14px; font-weight:700; letter-spacing:.3em; text-transform:uppercase; color:var(--lp-text-soft); display:block; margin-bottom:24px; }
#loc-template .lp-accred-list { display:flex; flex-wrap:wrap; justify-content:center; align-items:center; gap:40px 56px; }
#loc-template .lp-accred-list span { font-family:'Bodoni Moda',serif; font-size:28px; font-weight:500; color:var(--lp-primary); }
#loc-template .lp-accred-logo { height:70px; width:170px; object-fit:contain; object-position:center; display:block; transition:transform .3s ease; }
#loc-template .lp-accred-logo:hover { transform:scale(1.06); }
@media (max-width:900px){ #loc-template .lp-accred-list { gap:28px 36px; } #loc-template .lp-accred-logo { height:58px; width:135px; } }
@media (max-width:560px){ #loc-template .lp-accred-list { gap:22px 28px; } #loc-template .lp-accred-logo { height:48px; width:110px; } #loc-template .lp-accred-list span { font-size:22px; } }

/* ---------- City content block (per-page narrative from location docs) ---------- */
#loc-template .lp-cityblock{ background:var(--lp-surface-low); }
/* content block flows straight into the courses section (same background) — no doubled gap */
#loc-template .lp-cityblock + .lp-courses{ padding-top:0; }
#loc-template .lp-cityblock-grid{ display:grid; grid-template-columns:1.6fr 1fr; gap:48px; align-items:start; }
/* Full-width content when there is no features aside (no blank right column) */
#loc-template .lp-cityblock-grid:not(:has(.lp-cityblock-aside)){ grid-template-columns:1fr; }
#loc-template .lp-cityblock-main .lp-h2{ margin-bottom:20px; }
#loc-template .lp-cityblock-main p{ color:var(--lp-text-soft); font-size:16px; line-height:1.85; margin:0 0 16px; }
#loc-template .lp-cityblock-main p:last-child{ margin-bottom:0; }
#loc-template .lp-cityblock-aside{ background:var(--lp-white); border:1px solid var(--lp-outline); border-top:4px solid var(--lp-secondary); padding:28px 26px; }
#loc-template .lp-cityblock-aside h3{ font-family:'Bodoni Moda',serif; font-size:20px; font-weight:500; color:var(--lp-primary); margin:0 0 16px; line-height:1.3; }
#loc-template .lp-feature-list{ list-style:none; margin:0; padding:0; }
#loc-template .lp-feature-list li{ display:flex; align-items:flex-start; gap:10px; padding:10px 0; border-bottom:1px solid rgba(203,196,208,.4); color:var(--lp-text); font-size:14.5px; line-height:1.5; }
#loc-template .lp-feature-list li:last-child{ border-bottom:none; padding-bottom:0; }
#loc-template .lp-feature-list .material-symbols-outlined{ color:var(--lp-secondary); font-size:20px; flex-shrink:0; margin-top:1px; }
#loc-template .lp-cityblock-closing{ margin:36px 0 0; padding:22px 26px; background:var(--lp-white); border-left:4px solid var(--lp-secondary); color:var(--lp-text); font-size:16px; line-height:1.75; }
@media (max-width:900px){
  #loc-template .lp-cityblock-grid{ grid-template-columns:1fr; gap:28px; }
}

/* ---------- LEAD FORM ---------- */
#loc-template .lp-lead { display:flex; flex-direction:column; background:var(--lp-surface-low); border:1px solid rgba(203,196,208,.4); overflow:hidden; }
@media (min-width:900px){ #loc-template .lp-lead { flex-direction:row; } }
#loc-template .lp-lead-info { background:var(--lp-primary); color:#fff; padding:48px; flex:1; }
#loc-template .lp-lead-info h2 { color:#fff; margin-bottom:20px; }
#loc-template .lp-lead-sub { font-size:18px; opacity:.85; margin-bottom:32px; }
#loc-template .lp-lead-rows { display:flex; flex-direction:column; gap:16px; }
#loc-template .lp-lead-row { display:flex; align-items:center; gap:14px; }
#loc-template .lp-lead-row .material-symbols-outlined { color:var(--lp-secondary-fixed); }
#loc-template .lp-lead-row a, #loc-template .lp-lead-row span { color:#fff; font-weight:700; font-size:14px; letter-spacing:.1em; }
#loc-template .lp-lead-form { background:#fff; padding:48px; flex:1; }
#loc-template .lp-lead-form .lp-field { margin-bottom:24px; }
#loc-template .lp-lead-form label { display:block; font-size:12px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--lp-primary); margin-bottom:6px; }
#loc-template .lp-lead-form input, #loc-template .lp-lead-form select {
  width:100%; border:0; border-bottom:1px solid var(--lp-outline);
  background:transparent; padding:8px 0; font-size:15px; color:var(--lp-text);
  outline:none; border-radius:0;
}
#loc-template .lp-lead-form input:focus, #loc-template .lp-lead-form select:focus { border-bottom-color:var(--lp-primary); }
#loc-template .lp-lead-row-pair { display:grid; grid-template-columns:1fr; gap:24px; }
@media (min-width:600px){ #loc-template .lp-lead-row-pair { grid-template-columns:1fr 1fr; } }
#loc-template .lp-lead-submit { background:var(--lp-secondary); color:#fff; border:0; padding:18px 48px; font-size:14px; font-weight:700; letter-spacing:.2em; text-transform:uppercase; transition:all .25s; margin-top:24px; }
#loc-template .lp-lead-submit:hover { background:var(--lp-secondary-dark); box-shadow:0 12px 24px rgba(0,0,0,.18); }

/* ---------- CAREER OPPORTUNITIES (12) ---------- */
#loc-template .lp-center-head { text-align:center; margin-bottom:48px; }
#loc-template .lp-center-head .lp-tagline { display:block; }
#loc-template .lp-center-head p { font-size:18px; color:var(--lp-text-soft); max-width:680px; margin:16px auto 0; }
#loc-template .lp-careers2-grid { display:grid; grid-template-columns:1fr; gap:16px; }
@media (min-width:600px){ #loc-template .lp-careers2-grid { grid-template-columns:repeat(2,1fr); } }
@media (min-width:1000px){ #loc-template .lp-careers2-grid { grid-template-columns:repeat(3,1fr); } }
#loc-template .lp-career2-card { background:#fff; border:1px solid rgba(203,196,208,.5); border-left:3px solid var(--lp-secondary); padding:24px; transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
#loc-template .lp-career2-card:hover { transform:translateY(-4px); box-shadow:0 16px 40px rgba(23,3,56,.10); border-left-color:var(--lp-primary); }
#loc-template .lp-career2-card .material-symbols-outlined { font-size:30px; color:var(--lp-secondary); margin-bottom:12px; }
#loc-template .lp-career2-card h4 { font-family:'Manrope',sans-serif; font-size:16px; font-weight:700; color:var(--lp-primary); margin-bottom:6px; }
#loc-template .lp-career2-card p { font-size:14px; color:var(--lp-text-soft); }

/* ---------- ACHIEVEMENTS ---------- */
#loc-template .lp-ach { background:var(--lp-primary); color:#fff; }
#loc-template .lp-ach h2 { color:#fff; }
#loc-template .lp-ach .lp-center-head p { color:rgba(255,255,255,.8); }
#loc-template .lp-ach-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media (min-width:768px){ #loc-template .lp-ach-grid { grid-template-columns:repeat(4,1fr); } }
#loc-template .lp-ach-card { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.12); padding:28px 22px; text-align:center; }
#loc-template .lp-ach-card .material-symbols-outlined { font-size:34px; color:var(--lp-secondary-fixed); margin-bottom:10px; }
#loc-template .lp-ach-val { font-family:'Bodoni Moda',serif; font-size:40px; color:var(--lp-secondary); line-height:1; display:block; margin-bottom:6px; }
#loc-template .lp-ach-card h4 { color:#fff; font-family:'Manrope',sans-serif; font-size:16px; font-weight:700; margin-bottom:8px; }
#loc-template .lp-ach-card p { color:rgba(255,255,255,.72); font-size:13px; line-height:1.5; }

/* ---------- AWARD & EVENTS ---------- */
#loc-template .lp-awards { background:var(--lp-surface-low); }
#loc-template .lp-awards-grid { display:grid; grid-template-columns:1fr; gap:20px; align-items:stretch; }
@media (min-width:640px){ #loc-template .lp-awards-grid { grid-template-columns:repeat(2,1fr); } }
@media (min-width:1000px){ #loc-template .lp-awards-grid { grid-template-columns:repeat(3,1fr); } }
#loc-template .lp-award-card { background:#fff; border:1px solid rgba(203,196,208,.5); overflow:hidden; box-shadow:0 6px 20px rgba(23,3,56,.06); transition:transform .3s ease, box-shadow .3s ease; display:block; }
#loc-template .lp-award-card:hover { transform:translateY(-4px); box-shadow:0 18px 44px rgba(23,3,56,.14); }
#loc-template .lp-award-card img { width:100%; height:100%; aspect-ratio:1 / 1; object-fit:cover; object-position:top; display:block; cursor:zoom-in; transition:transform .5s ease; }
#loc-template .lp-award-card:hover img { transform:scale(1.05); }
@supports not (aspect-ratio: 1 / 1) {
  #loc-template .lp-award-card img { height:280px; }
  @media (min-width:640px){ #loc-template .lp-award-card img { height:300px; } }
  @media (min-width:1000px){ #loc-template .lp-award-card img { height:320px; } }
}

/* ---------- WHY CHOOSE ---------- */
#loc-template .lp-why-grid { display:grid; grid-template-columns:1fr; gap:20px; }
@media (min-width:600px){ #loc-template .lp-why-grid { grid-template-columns:repeat(2,1fr); } }
@media (min-width:1000px){ #loc-template .lp-why-grid { grid-template-columns:repeat(3,1fr); } }
#loc-template .lp-why-card { padding:28px; background:#fff; border:1px solid rgba(203,196,208,.5); border-top:3px solid var(--lp-secondary); }
#loc-template .lp-why-ico { width:56px; height:56px; border-radius:50%; background:rgba(254,94,30,.12); color:var(--lp-secondary); display:flex; align-items:center; justify-content:center; margin-bottom:16px; }
#loc-template .lp-why-ico .material-symbols-outlined { font-size:28px; }
#loc-template .lp-why-card h4 { font-family:'Manrope',sans-serif; font-size:18px; font-weight:700; color:var(--lp-primary); margin-bottom:8px; }
#loc-template .lp-why-card p { font-size:14px; color:var(--lp-text-soft); line-height:1.6; }

/* ---------- TESTIMONIALS SLIDER ---------- */
#loc-template .lp-tst { background:var(--lp-surface-low); }
#loc-template .lp-tst-track { display:flex; gap:var(--lp-gap); overflow-x:auto; scroll-snap-type:x mandatory; scroll-behavior:smooth; padding-bottom:8px; scrollbar-width:none; -ms-overflow-style:none; }
#loc-template .lp-tst-track::-webkit-scrollbar { display:none; }
#loc-template .lp-tst-card { flex:0 0 100%; scroll-snap-align:start; background:#fff; border:1px solid rgba(203,196,208,.5); padding:36px; position:relative; }
@media (min-width:768px){ #loc-template .lp-tst-card { flex-basis:calc(50% - 12px); } }
@media (min-width:1100px){ #loc-template .lp-tst-card { flex-basis:calc(33.333% - 16px); } }
#loc-template .lp-tst-badge { position:absolute; top:18px; right:28px; font-family:'Bodoni Moda',serif; font-size:72px; color:var(--lp-secondary); opacity:.16; line-height:1; }
#loc-template .lp-tst-stars { color:#f5a623; letter-spacing:3px; margin-bottom:14px; font-size:15px; }
#loc-template .lp-tst-quote { font-size:15px; color:var(--lp-text-soft); line-height:1.7; font-style:italic; margin-bottom:20px; }
#loc-template .lp-tst-name { font-family:'Bodoni Moda',serif; font-size:20px; color:var(--lp-primary); }

/* ---------- IMAGE TESTIMONIALS (slider) ---------- */
#loc-template .lp-imgtst-track { display:flex; gap:var(--lp-gap); overflow-x:auto; scroll-snap-type:x mandatory; scroll-behavior:smooth; padding-bottom:8px; scrollbar-width:none; -ms-overflow-style:none; }
#loc-template .lp-imgtst-track::-webkit-scrollbar { display:none; }
#loc-template .lp-imgtst-card { flex:0 0 82%; scroll-snap-align:center; }
@media (min-width:640px){ #loc-template .lp-imgtst-card { flex-basis:calc(50% - 12px); } }
@media (min-width:1000px){ #loc-template .lp-imgtst-card { flex-basis:calc(33.333% - 16px); } }
#loc-template .lp-imgtst-card img { width:100%; height:auto; display:block; border:1px solid rgba(203,196,208,.5); box-shadow:0 8px 24px rgba(23,3,56,.08); }

/* ---------- VIDEO TESTIMONIALS ---------- */
#loc-template .lp-vid { background:var(--lp-primary); color:#fff; }
#loc-template .lp-vid h2 { color:#fff; }
#loc-template .lp-vid .lp-center-head p { color:rgba(255,255,255,.8); }
#loc-template .lp-vid-track { display:flex; gap:var(--lp-gap); overflow-x:auto; scroll-snap-type:x mandatory; scroll-behavior:smooth; padding-bottom:8px; scrollbar-width:none; -ms-overflow-style:none; }
#loc-template .lp-vid-track::-webkit-scrollbar { display:none; }
#loc-template .lp-vid-card { flex:0 0 82%; scroll-snap-align:center; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.14); overflow:hidden; }
@media (min-width:700px){ #loc-template .lp-vid-card { flex-basis:calc(50% - 12px); } }
@media (min-width:1050px){ #loc-template .lp-vid-card { flex-basis:calc(33.333% - 16px); } }
#loc-template .lp-vid-card video { width:100%; height:auto; display:block; background:#000; }
#loc-template .lp-vid .lp-section-head h2 { color:#fff; }
#loc-template .lp-vid-cap { padding:16px 18px; font-size:14px; font-weight:700; color:#fff; letter-spacing:.02em; }
</style>

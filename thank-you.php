<?php
if (!defined('NIIB_INIT')) {
    require_once __DIR__ . '/includes/init.php';
}

$page_seo['title']       = 'Thank You | ' . setting('site_name', 'NIIB Institute');
$page_seo['description'] = 'Thank you for reaching out to NIIB Institute. We have received your enquiry and will get back to you shortly.';
$page_seo['robots']      = 'noindex, nofollow';
$page_seo['canonical']   = SITE_URL . '/thank-you';

include __DIR__ . '/includes/header.php';
?>

<style>
/* ── Thank You Page — Light Theme ────────────────────── */
.ty-section {
    min-height: 72vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    background: #f6f3f2;
    position: relative;
    overflow: hidden;
}

/* decorative soft blobs */
.ty-section::before,
.ty-section::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}
.ty-section::before {
    width: 480px; height: 480px;
    background: radial-gradient(circle, rgba(254,94,30,.10) 0%, transparent 70%);
    top: -140px; right: -100px;
}
.ty-section::after {
    width: 360px; height: 360px;
    background: radial-gradient(circle, rgba(23,3,56,.07) 0%, transparent 70%);
    bottom: -100px; left: -80px;
}

.ty-card {
    background: #ffffff;
    border: 1px solid #e8e2de;
    border-radius: 24px;
    padding: 60px 50px;
    max-width: 620px;
    width: 100%;
    text-align: center;
    position: relative;
    z-index: 1;
    box-shadow: 0 8px 40px rgba(23,3,56,.08), 0 2px 8px rgba(23,3,56,.04);
}

.ty-icon-wrap {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fe5e1e, #ff8c42);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 28px;
    box-shadow: 0 8px 28px rgba(254,94,30,.30);
    animation: ty-pop .5s cubic-bezier(.34,1.56,.64,1) both;
}
.ty-icon-wrap i {
    font-size: 40px;
    color: #fff;
}

@keyframes ty-pop {
    from { transform: scale(0); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}

.ty-card h1 {
    font-family: 'Bodoni Moda', Georgia, serif;
    font-size: clamp(28px, 5vw, 38px);
    font-weight: 700;
    color: #170338;
    margin: 0 0 14px;
    line-height: 1.2;
}

.ty-card .ty-sub {
    font-size: 16px;
    color: #5a5068;
    line-height: 1.7;
    margin: 0 0 36px;
}

.ty-card .ty-sub strong {
    color: #fe5e1e;
    font-weight: 600;
}

.ty-divider {
    width: 48px;
    height: 3px;
    background: linear-gradient(90deg, #fe5e1e, #ff8c42);
    border-radius: 2px;
    margin: 0 auto 32px;
}

.ty-steps {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 40px;
}

.ty-step {
    background: #faf8f7;
    border: 1px solid #ede8e4;
    border-radius: 14px;
    padding: 20px 16px;
    flex: 1;
    min-width: 140px;
    max-width: 170px;
    text-align: center;
    transition: box-shadow .2s, transform .2s;
}
.ty-step:hover {
    box-shadow: 0 6px 20px rgba(23,3,56,.08);
    transform: translateY(-2px);
}
.ty-step i {
    font-size: 26px;
    color: #fe5e1e;
    display: block;
    margin-bottom: 10px;
}
.ty-step span {
    font-size: 13px;
    color: #5a5068;
    line-height: 1.5;
    display: block;
    font-weight: 500;
}

.ty-actions {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
}

.ty-actions .btn-primary-ty {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 30px;
    background: linear-gradient(135deg, #fe5e1e, #ff7a40);
    color: #fff;
    border-radius: 50px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    transition: transform .2s, box-shadow .2s;
    box-shadow: 0 6px 20px rgba(254,94,30,.30);
}
.ty-actions .btn-primary-ty:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(254,94,30,.40);
    color: #fff;
    text-decoration: none;
}

.ty-actions .btn-outline-ty {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 30px;
    background: transparent;
    color: #170338;
    border: 1.5px solid #c8bfda;
    border-radius: 50px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    transition: background .2s, border-color .2s, transform .2s;
}
.ty-actions .btn-outline-ty:hover {
    background: #f0eaf8;
    border-color: #170338;
    transform: translateY(-2px);
    color: #170338;
    text-decoration: none;
}

@media (max-width: 540px) {
    .ty-card {
        padding: 40px 22px;
    }
    .ty-step {
        min-width: 120px;
    }
}
</style>

<section class="ty-section">
    <div class="ty-card">

        <div class="ty-icon-wrap">
            <i class="bi bi-check-lg"></i>
        </div>

        <h1>Thank You!</h1>
        <div class="ty-divider"></div>

        <p class="ty-sub">
            Your enquiry has been received successfully.<br>
            Our team will reach out to you within <strong>24 hours</strong>.
        </p>

        <div class="ty-steps">
            <div class="ty-step">
                <i class="bi bi-envelope-check"></i>
                <span>Confirmation sent to your email</span>
            </div>
            <div class="ty-step">
                <i class="bi bi-person-lines-fill"></i>
                <span>Counsellor assigned to your query</span>
            </div>
            <div class="ty-step">
                <i class="bi bi-telephone-inbound"></i>
                <span>Expect a call within 24 hours</span>
            </div>
        </div>

        <div class="ty-actions">
            <a href="<?= SITE_URL ?>/" class="btn-primary-ty">
                <i class="bi bi-house-fill"></i> Back to Home
            </a>
            <a href="<?= SITE_URL ?>/courses" class="btn-outline-ty">
                <i class="bi bi-mortarboard-fill"></i> Explore Courses
            </a>
        </div>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

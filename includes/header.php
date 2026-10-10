<?php
if (!defined('NIIB_INIT')) { http_response_code(403); exit; }
$settings = get_settings();
$socials = get_social_links();
$menuTree = get_menu_items('header');
$current_path = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#170338">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<title><?= e($page_seo['title']) ?></title>
<meta name="description" content="<?= e($page_seo['description']) ?>">
<?php if (!empty($page_seo['keywords'])): ?><meta name="keywords" content="<?= e($page_seo['keywords']) ?>"><?php endif; ?>
<meta name="robots" content="<?= e($page_seo['robots']) ?>">
<meta name="googlebot" content="<?= e($page_seo['robots']) ?>, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
<meta name="author" content="<?= e($page_seo['article_author'] ?: setting('site_name','NIIB Institute')) ?>">
<meta name="publisher" content="<?= e(setting('site_name','NIIB Institute')) ?>">
<meta name="copyright" content="© <?= date('Y') ?> <?= e(setting('site_name','NIIB Institute')) ?>">
<meta name="referrer" content="strict-origin-when-cross-origin">
<link rel="canonical" href="<?= e($page_seo['canonical']) ?>">

<!-- Open Graph -->
<meta property="og:locale" content="<?= e($page_seo['og_locale'] ?? 'en_IN') ?>">
<meta property="og:site_name" content="<?= e(setting('site_name','NIIB Institute')) ?>">
<meta property="og:title" content="<?= e($page_seo['og_title'] ?: $page_seo['title']) ?>">
<meta property="og:description" content="<?= e($page_seo['og_description'] ?: $page_seo['description']) ?>">
<meta property="og:image" content="<?= e($page_seo['og_image']) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= e($page_seo['og_title'] ?: $page_seo['title']) ?>">
<meta property="og:type" content="<?= e($page_seo['og_type']) ?>">
<meta property="og:url" content="<?= e($page_seo['canonical']) ?>">
<?php if (!empty($page_seo['article_published'])): ?>
<meta property="article:published_time" content="<?= e($page_seo['article_published']) ?>">
<?php endif; ?>
<?php if (!empty($page_seo['article_modified'])): ?>
<meta property="article:modified_time" content="<?= e($page_seo['article_modified']) ?>">
<?php endif; ?>
<?php if (!empty($page_seo['article_author'])): ?>
<meta property="article:author" content="<?= e($page_seo['article_author']) ?>">
<?php endif; ?>
<?php if (!empty($page_seo['article_section'])): ?>
<meta property="article:section" content="<?= e($page_seo['article_section']) ?>">
<?php endif; ?>

<!-- Twitter -->
<meta name="twitter:card" content="<?= e(seo('twitter_card','summary_large_image')) ?>">
<?php if (seo('twitter_site','')): ?><meta name="twitter:site" content="<?= e(seo('twitter_site','')) ?>"><?php endif; ?>
<meta name="twitter:title" content="<?= e($page_seo['og_title'] ?: $page_seo['title']) ?>">
<meta name="twitter:description" content="<?= e($page_seo['og_description'] ?: $page_seo['description']) ?>">
<meta name="twitter:image" content="<?= e($page_seo['og_image']) ?>">

<!-- Favicons -->
<link rel="icon" type="image/svg+xml" href="<?= e(img_url(setting('site_favicon','assets/images/favicon.svg'))) ?>">
<link rel="shortcut icon" href="<?= e(img_url(setting('site_favicon','assets/images/favicon.svg'))) ?>">
<link rel="apple-touch-icon" href="<?= e(img_url(setting('site_favicon','assets/images/favicon.svg'))) ?>">

<!-- Performance: preconnect + dns-prefetch for external assets -->
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="dns-prefetch" href="https://fonts.googleapis.com">
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

<!-- Sitemap + RSS hints for crawlers -->
<link rel="sitemap" type="application/xml" title="Sitemap" href="<?= SITE_URL ?>/sitemap.xml">

<!-- Stylesheets -->
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,wght@0,400..900;1,400..900&family=Manrope:wght@300..800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= ASSETS_URL ?>/css/style.css?v=34" rel="stylesheet">

<!-- Structured data (Organization, WebSite, Breadcrumbs, page-specific) -->
<?= niib_seo_render_schema() ?>
<?php /* Legacy schema fallbacks (kept for backwards compatibility with admin SEO module) */ ?>
<?php $orgSchema = seo('organization_schema'); if (empty($GLOBALS['niib_schema_queue']) && $orgSchema): ?>
<script type="application/ld+json"><?= $orgSchema ?></script>
<?php endif; ?>
<?php if (!empty($page_seo['schema'])): ?>
<script type="application/ld+json"><?= $page_seo['schema'] ?></script>
<?php endif; ?>

<?= render_header_codes('head') ?>
<?= niib_recaptcha_head() ?>
<style id="admin-custom-css"><?= render_custom_css() ?></style>
</head>
<body>
<?= render_header_codes('body_start') ?>

<header class="site-header">
    <div class="topbar">
        <div class="container topbar-inner">
            <div class="topbar-left">
                <span class="tb-tagline"><i class="bi bi-stars"></i> Admissions Open 2026 &mdash; <a href="<?= SITE_URL ?>/contact">Free Counselling Available</a></span>
            </div>
            <div class="topbar-right">
                <?php $tbPhone = $topbar_phone ?? setting('site_phone'); if ($tbPhone): ?>
                <a href="tel:<?= e($tbPhone) ?>" class="tb-link"><i class="bi bi-telephone-fill"></i> <?= e($tbPhone) ?></a>
                <?php endif; ?>
                <?php $tbEmail = $topbar_email ?? setting('site_email'); if ($tbEmail): ?>
                <a href="mailto:<?= e($tbEmail) ?>" class="tb-link"><i class="bi bi-envelope-fill"></i> <?= e($tbEmail) ?></a>
                <?php endif; ?>
                <?php
                $header_socials = $custom_topbar_socials ?? get_social_links();
                if ($header_socials): ?>
                <span class="tb-socials">
                    <?php foreach (array_slice($header_socials, 0, 4) as $s): ?>
                    <a href="<?= e($s['url']) ?>" target="_blank" rel="noopener" title="<?= e($s['platform']) ?>"><i class="bi <?= e($s['icon']) ?>"></i></a>
                    <?php endforeach; ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="nav-wrap"><div class="container nav-row">
        <a class="brand" href="<?= SITE_URL ?>/">
            <?php $logo = setting('site_logo'); if ($logo && file_exists(BASE_PATH . '/' . ltrim($logo,'/'))): ?>
                <img src="<?= e(img_url($logo)) ?>" alt="<?= e(setting('site_name','NIIB Institute')) ?>">
            <?php else: ?>
                <span><?= e(setting('site_name','NIIB Institute')) ?></span>
            <?php endif; ?>
        </a>

        <button class="menu-toggle" aria-label="Toggle menu" onclick="document.body.classList.toggle('nav-open')">
            <i class="bi bi-list"></i>
        </button>

        <button class="menu-close" aria-label="Close menu" onclick="document.body.classList.remove('nav-open')">
            <i class="bi bi-x-lg"></i>
        </button>

        <ul class="main-menu">
            <?php
            function _niib_resolve_url($u) {
                if (preg_match('~^https?://~i', $u) || strpos($u,'/')===0) return $u;
                return SITE_URL . '/' . ltrim($u, '/');
            }
            // Pre-fetch states (one query, cached) for any Locations menu item
            $_loc_states = null;
            $get_loc_states = function() use (&$_loc_states) {
                if ($_loc_states !== null) return $_loc_states;
                try {
                    $stmt = db()->query("SELECT DISTINCT state, state_slug, state_order FROM locations WHERE status=1 ORDER BY state_order, state");
                    $_loc_states = $stmt->fetchAll();
                } catch (Throwable $e) { $_loc_states = []; }
                return $_loc_states;
            };

            foreach (($menuTree[0] ?? []) as $item):
                $url = _niib_resolve_url($item['url']);
                $children = $menuTree[$item['id']] ?? [];

                // If this is the Locations link, replace any static children
                // with the live states from the locations table.
                $isLocations = (basename($item['url']) === 'locations.php' || $item['url'] === 'locations.php');
                if ($isLocations) {
                    $states = $get_loc_states();
                    $children = array_map(function($s){
                        return [
                            'title'  => $s['state'],
                            'url'    => 'locations.php#state-' . $s['state_slug'],
                            'target' => '_self',
                        ];
                    }, $states);
                }

                $hasChildren = !empty($children);
                $isActive    = ($current_path === basename($item['url'])) || ($current_path === '' && $item['url']==='index.php');
            ?>
                <li class="<?= $hasChildren ? 'has-dropdown' : '' ?>">
                    <a href="<?= e($url) ?>" class="<?= $isActive?'active':'' ?>" target="<?= e($item['target']) ?>"><?= e($item['title']) ?><?php if($hasChildren): ?> <i class="bi bi-chevron-down" style="font-size:11px;margin-left:4px;"></i><?php endif; ?></a>
                    <?php if ($hasChildren): ?>
                    <button type="button" class="submenu-toggle" aria-label="Toggle <?= e($item['title']) ?> submenu" aria-expanded="false"><i class="bi bi-chevron-down"></i></button>
                    <ul class="submenu">
                        <?php foreach ($children as $sub):
                            $surl = _niib_resolve_url($sub['url']);
                        ?>
                            <li><a href="<?= e($surl) ?>" target="<?= e($sub['target'] ?? '_self') ?>"><?= e($sub['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            <li class="nav-cta"><a href="<?= SITE_URL ?>/contact" class="btn btn-cta btn-sm">Enquire Now</a></li>
            <li class="mobile-contact">
                <?php $mcPhone = $topbar_phone ?? setting('site_phone'); if ($mcPhone): ?>
                <a href="tel:<?= e($mcPhone) ?>" class="mc-row"><i class="bi bi-telephone-fill"></i> <span><?= e($mcPhone) ?></span></a>
                <?php endif; ?>
                <?php $mcEmail = $topbar_email ?? setting('site_email'); if ($mcEmail): ?>
                <a href="mailto:<?= e($mcEmail) ?>" class="mc-row"><i class="bi bi-envelope-fill"></i> <span><?= e($mcEmail) ?></span></a>
                <?php endif; ?>
            </li>
        </ul>
        <div class="nav-backdrop" onclick="document.body.classList.remove('nav-open')"></div>
    </div></div>
</header>

<main>

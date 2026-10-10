<?php
/**
 * Bootstrap - included by every public page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'gc_maxlifetime'  => 7200,
    ]);
}

define('NIIB_INIT', true);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/seo.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/recaptcha.php';

// SEO defaults for page — overridden per template
$page_seo = [
    'title'           => seo('global_meta_title', 'NIIB Institute'),
    'description'     => seo('global_meta_description', ''),
    'keywords'        => seo('global_meta_keywords', ''),
    'canonical'       => niib_seo_canonical(),
    'og_title'        => seo('og_title', ''),
    'og_description'  => seo('og_description', ''),
    'og_image'        => img_url(seo('og_image', 'assets/images/og-image.svg')),
    'og_type'         => seo('og_type', 'website'),
    'og_locale'       => 'en_IN',
    'robots'          => seo('robots_meta', 'index, follow'),
    'schema'          => '',
    'article_published' => '',
    'article_modified'  => '',
    'article_author'    => '',
    'article_section'   => '',
];

// Queue global site-wide schemas (Organization + WebSite with SearchAction)
niib_seo_add_schema(niib_seo_organization());
niib_seo_add_schema(niib_seo_website());

// Default breadcrumbs — page templates can override
$page_breadcrumbs = [
    ['name' => 'Home', 'url' => SITE_URL . '/'],
];

// Output filter: lazy-load + decoding hints on all <img> tags
if (!defined('NIIB_SKIP_OPTIMIZE')) {
    ob_start('niib_optimize_images_html');
}

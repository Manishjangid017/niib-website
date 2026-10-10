<?php
/**
 * SEO Library — Schema.org JSON-LD generators + meta helpers
 * Included automatically by init.php; functions are namespaced with niib_seo_*
 */
if (!defined('NIIB_INIT')) { http_response_code(403); exit; }

/**
 * Print a Schema.org JSON-LD block.
 */
function niib_seo_jsonld($data): string {
    if (!$data) return '';
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}

/**
 * Site-wide Organization / EducationalOrganization schema.
 * Pulls from site_settings so it always reflects the live data.
 */
function niib_seo_organization(): array {
    $base = SITE_URL;
    $logo = setting('site_logo') ? rtrim($base,'/') . '/' . ltrim(setting('site_logo'),'/') : '';

    $socials = [];
    foreach (get_social_links() as $s) {
        if (!empty($s['url'])) $socials[] = $s['url'];
    }

    $org = [
        '@context' => 'https://schema.org',
        '@type'    => 'EducationalOrganization',
        '@id'      => $base . '#organization',
        'name'     => setting('site_name', 'NIIB Institute'),
        'alternateName' => 'New Image International Beauty Institute',
        'url'      => $base . '/',
        'description' => setting('site_tagline', '') ?: seo('global_meta_description',''),
        'foundingDate' => '2004',
    ];
    if ($logo) {
        $org['logo'] = ['@type'=>'ImageObject', 'url'=>$logo];
    }
    if ($socials) $org['sameAs'] = $socials;
    if (setting('site_phone'))   $org['telephone'] = setting('site_phone');
    if (setting('site_email'))   $org['email']     = setting('site_email');
    if (setting('site_address')) {
        $org['address'] = ['@type'=>'PostalAddress', 'streetAddress'=>setting('site_address'), 'addressCountry'=>'IN'];
    }
    return $org;
}

/**
 * Site-wide WebSite schema with SearchAction → enables Google sitelinks search.
 */
function niib_seo_website(): array {
    $base = SITE_URL;
    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'WebSite',
        '@id'             => $base . '#website',
        'url'             => $base . '/',
        'name'            => setting('site_name', 'NIIB Institute'),
        'description'     => seo('global_meta_description', ''),
        'publisher'       => ['@id' => $base . '#organization'],
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => $base . '/blog.php?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
}

/**
 * BreadcrumbList schema.
 * @param array $items each item is ['name' => ..., 'url' => ...] (url optional for the last)
 */
function niib_seo_breadcrumbs(array $items): array {
    $list = [];
    foreach (array_values($items) as $i => $it) {
        $entry = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $it['name'] ?? '',
        ];
        if (!empty($it['url'])) $entry['item'] = $it['url'];
        $list[] = $entry;
    }
    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $list,
    ];
}

/**
 * Article / BlogPosting schema for a blog post row.
 */
function niib_seo_article(array $b, string $cat_name = ''): array {
    $base  = SITE_URL;
    $url   = $base . '/blog/' . rawurlencode($b['slug']);
    $image = $b['featured_image'] ? rtrim($base,'/') . '/' . ltrim($b['featured_image'],'/') : '';
    $words = str_word_count(strip_tags($b['content'] ?? ''));

    $data = [
        '@context'         => 'https://schema.org',
        '@type'            => 'BlogPosting',
        'mainEntityOfPage' => ['@type'=>'WebPage','@id'=>$url],
        'headline'         => mb_substr($b['title'], 0, 110),
        'description'      => strip_tags($b['short_description'] ?? ''),
        'datePublished'    => date('c', strtotime($b['publish_date'] ?? $b['created_at'] ?? 'now')),
        'dateModified'     => date('c', strtotime($b['updated_at'] ?? $b['publish_date'] ?? 'now')),
        'author'           => ['@type'=>'Person', 'name'=>$b['author'] ?? setting('site_name')],
        'publisher'        => ['@id' => $base . '#organization'],
        'inLanguage'       => 'en-IN',
        'wordCount'        => $words,
        'url'              => $url,
    ];
    if ($image) $data['image'] = $image;
    if ($cat_name) $data['articleSection'] = $cat_name;
    if (!empty($b['tags'])) {
        $tags = array_filter(array_map('trim', explode(',', $b['tags'])));
        if ($tags) $data['keywords'] = $tags;
    }
    return $data;
}

/**
 * Course schema for a course row.
 */
function niib_seo_course(array $c, string $cat_name = ''): array {
    $base  = SITE_URL;
    $url   = $base . '/course/' . rawurlencode($c['slug']);
    $image = $c['image'] ? rtrim($base,'/') . '/' . ltrim($c['image'],'/') : '';

    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Course',
        'name'        => $c['title'],
        'description' => strip_tags($c['short_description'] ?? '') ?: strip_tags($c['full_description'] ?? ''),
        'provider'    => ['@id' => $base . '#organization'],
        'url'         => $url,
        'inLanguage'  => $c['language'] ?? 'en-IN',
    ];
    if ($image) $data['image'] = $image;
    if (!empty($c['rating']) && (float)$c['rating'] > 0 && (int)$c['students_count'] > 0) {
        $data['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => number_format((float)$c['rating'], 1),
            'reviewCount' => max(1, (int)$c['students_count']),
            'bestRating'  => '5',
            'worstRating' => '1',
        ];
    }
    if (!empty($c['fees'])) {
        $price = preg_replace('/[^\d.]/', '', $c['fees']);
        if ($price !== '') {
            $data['offers'] = [
                '@type'         => 'Offer',
                'category'      => 'Educational',
                'price'         => $price,
                'priceCurrency' => 'INR',
                'availability'  => 'https://schema.org/InStock',
                'url'           => $url,
            ];
        }
    }
    if (!empty($c['duration'])) {
        $data['timeRequired'] = $c['duration'];
    }
    if ($cat_name) $data['about'] = $cat_name;
    return $data;
}

/**
 * FAQPage schema from FAQ rows.
 */
function niib_seo_faq(array $faqs): array {
    $items = [];
    foreach ($faqs as $f) {
        $items[] = [
            '@type'          => 'Question',
            'name'           => $f['question'],
            'acceptedAnswer' => ['@type'=>'Answer', 'text'=>strip_tags($f['answer'])],
        ];
    }
    return [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $items,
    ];
}

/**
 * LocalBusiness schema for a location row.
 */
function niib_seo_local_business(array $loc): array {
    $base = SITE_URL;
    $data = [
        '@context' => 'https://schema.org',
        '@type'    => 'EducationalOrganization',
        'name'     => setting('site_name', 'NIIB Institute') . ' — ' . $loc['city'],
        'url'      => $base . '/locations#state-' . ($loc['state_slug'] ?? ''),
        'address'  => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $loc['address'] ?? '',
            'addressLocality' => $loc['city'],
            'addressRegion'   => $loc['state'] ?? '',
            'addressCountry'  => 'IN',
        ],
        'parentOrganization' => ['@id' => $base . '#organization'],
    ];
    if (!empty($loc['phone'])) $data['telephone'] = $loc['phone'];
    if (!empty($loc['email'])) $data['email']     = $loc['email'];
    if (!empty($loc['map_link'])) $data['hasMap'] = $loc['map_link'];
    return $data;
}

/**
 * Convert a raw blog title / description into a clean meta string.
 */
function niib_seo_clean_text(string $text, int $limit = 160): string {
    $text = preg_replace('/\s+/', ' ', trim(strip_tags($text)));
    if (mb_strlen($text) <= $limit) return $text;
    $cut = mb_substr($text, 0, $limit);
    return rtrim(mb_substr($cut, 0, mb_strrpos($cut, ' ') ?: $limit)) . '…';
}

/**
 * Build canonical URL for the current request, stripping query strings unless allowed.
 */
function niib_seo_canonical(array $allowed_params = ['slug','cat','q','page']): string {
    $base = SITE_URL;
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (defined('SITE_BASE_PATH') && SITE_BASE_PATH !== '') {
        $path = preg_replace('#^' . preg_quote(SITE_BASE_PATH, '#') . '(?=/|$)#', '', $path);
        if ($path === '') $path = '/';
    }
    $qs   = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?: '';
    $kept = [];
    if ($qs) {
        parse_str($qs, $params);
        foreach ($allowed_params as $k) {
            if (isset($params[$k]) && $params[$k] !== '') $kept[$k] = $params[$k];
        }
    }
    $out = rtrim($base, '/') . ($path === '/' ? '/' : $path);
    if ($kept) $out .= '?' . http_build_query($kept);
    return $out;
}

/**
 * Push a schema block to a global queue rendered in the <head>.
 */
function niib_seo_add_schema($data): void {
    global $niib_schema_queue;
    if (!isset($niib_schema_queue)) $niib_schema_queue = [];
    $niib_schema_queue[] = $data;
}

/**
 * Render all queued schema blocks.
 */
function niib_seo_render_schema(): string {
    global $niib_schema_queue;
    if (empty($niib_schema_queue)) return '';
    $out = '';
    foreach ($niib_schema_queue as $d) $out .= niib_seo_jsonld($d);
    return $out;
}

/**
 * Set page breadcrumbs (registers schema + makes them available to template).
 */
function niib_seo_set_breadcrumbs(array $items): void {
    global $page_breadcrumbs;
    $page_breadcrumbs = $items;
    niib_seo_add_schema(niib_seo_breadcrumbs($items));
}

<?php
// Allow CLI execution without auth
if (php_sapi_name() === 'cli') {
    require __DIR__.'/../../includes/init.php';
    $pdo = db();
} else {
    require __DIR__.'/../includes/auth.php';
    $pdo = db();
}

// Get all locations without pages
$locations = $pdo->query("SELECT * FROM locations WHERE status=1 AND page_id IS NULL ORDER BY state, city")->fetchAll();

if (empty($locations)) {
    die("No locations found that need pages.\n");
}

echo "Generating pages for " . count($locations) . " locations...\n\n";

foreach ($locations as $loc) {
    $city = $loc['city'];
    $state = $loc['state'];
    $region_label = $loc['region_label'] ?: 'Learning Center';

    // Create page title and slug
    $title = "Master the Art of Beauty in $city";
    $slug = strtolower(str_replace(' ', '-', "$city-{$region_label}"));
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');

    // Check if slug already exists
    $existing = $pdo->query("SELECT id FROM pages WHERE slug = " . $pdo->quote($slug))->fetch();
    if ($existing) {
        echo "  [SKIP] $city - Page with slug '$slug' already exists\n";
        continue;
    }

    // Create location page data
    $template = [
        'hero' => [
            'badge' => 'Premium Beauty Education',
            'title' => $title,
            'cta1' => 'Download Brochure',
            'cta1_url' => '/contact',
            'cta2' => 'Our Courses',
            'cta2_url' => '/courses',
        ],
        'intro' => [
            'heading' => "Excellence in Aesthetic Education - $city",
            'para1' => "Welcome to NIIB Institute's $city center, part of India's premier network of beauty academies. We're committed to delivering world-class aesthetic and beauty education.",
            'para2' => "With state-of-the-art facilities and experienced mentors, we prepare beauty professionals for global careers.",
            'stat1_value' => '22+',
            'stat1_label' => 'Years Legacy',
            'stat2_value' => '25k+',
            'stat2_label' => 'Students Trained',
            'image' => '',
            'quote' => 'NIIB elevates beauty career standards across India.',
        ],
        'courses' => [
            'tagline' => 'Our Expertise',
            'heading' => 'Signature Courses',
            'course_ids' => [],
        ],
        'career' => [
            'heading' => 'Design Your Career Path',
            'subheading' => 'Build expertise in aesthetic excellence',
            'feature_icon' => 'brush',
            'feature_title' => 'Specialized Training',
            'feature_desc' => 'Learn from industry experts with global certifications',
            'cards' => [
                ['icon' => 'storefront', 'title' => 'Salon Management', 'desc' => 'Run successful beauty enterprises'],
                ['icon' => 'people-fill', 'title' => 'Client Relations', 'desc' => 'Build lasting professional relationships'],
                ['icon' => 'award-fill', 'title' => 'Global Certification', 'desc' => 'Achieve CIDESCO and international credentials'],
            ],
        ],
        'gallery' => [
            'heading' => 'Recognized Excellence',
            'subheading' => 'State-of-the-art facilities and training',
            'images' => [],
        ],
        'accreditations' => ['CIDESCO', 'VTCT', 'NSDC', 'SKILL INDIA'],
        'contact' => [
            'heading' => 'Have Questions? Get in touch!',
            'subheading' => "Connect with our $city team",
            'phone' => $loc['phone'] ?: '',
            'email' => $loc['email'] ?: '',
            'address' => $loc['address'] ?: '',
            'cities' => [$city],
        ],
    ];

    // Insert page
    $data = [
        'title' => $title,
        'slug' => $slug,
        'page_type' => 'location',
        'banner_image' => '',
        'content' => '',
        'template_data' => json_encode($template, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        'meta_title' => "Beauty Courses & Academy in $city - NIIB Institute",
        'meta_description' => "Professional beauty education in $city. Enroll in CIDESCO-certified courses at NIIB Institute's $region_label.",
        'meta_keywords' => "beauty academy $city, beauty courses $city, NIIB $city, aesthetic education",
        'canonical_url' => '',
        'schema_code' => '',
        'status' => 1,
        'sort_order' => 0,
    ];

    $cols = array_keys($data);
    $placeholders = array_fill(0, count($cols), '?');

    try {
        $stmt = $pdo->prepare("INSERT INTO pages (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $placeholders) . ")");
        $stmt->execute(array_values($data));
        $pageId = (int)$pdo->lastInsertId();

        // Update location with page_id
        $pdo->prepare("UPDATE locations SET page_id = ? WHERE id = ?")->execute([$pageId, $loc['id']]);

        echo "  [OK] $city -> Page ID: $pageId (slug: $slug)\n";
    } catch (Exception $e) {
        echo "  [ERROR] $city - " . $e->getMessage() . "\n";
    }
}

echo "\nDone! All location pages have been created and linked.\n";
echo "You can now access them in the Pages module to customize the content.\n";

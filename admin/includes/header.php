<?php
// must be included AFTER auth.php and after $page_title is set
$page_title = $page_title ?? 'Admin';
$active = $active ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title) ?> - NIIB Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="icon" href="<?= e(img_url(setting('site_favicon','assets/images/favicon.svg'))) ?>">
<link href="<?= ADMIN_URL ?>/assets/css/admin.css?v=4" rel="stylesheet">
</head>
<?php
$nav_groups = [
    'Website Content' => ['icon' => 'bi-globe2', 'items' => [
        ['key'=>'settings',          'href'=>'modules/settings.php',          'icon'=>'bi-gear-fill',              'label'=>'Site Settings'],
        ['key'=>'smtp',              'href'=>'modules/smtp.php',              'icon'=>'bi-envelope-paper-fill',    'label'=>'SMTP / Email'],
        ['key'=>'homepage',          'href'=>'modules/homepage.php',          'icon'=>'bi-house-door-fill',        'label'=>'Homepage Sections'],
        ['key'=>'sliders',           'href'=>'modules/sliders.php',           'icon'=>'bi-card-image',             'label'=>'Hero Sliders'],
        ['key'=>'pages',             'href'=>'modules/pages.php',             'icon'=>'bi-file-earmark-text-fill', 'label'=>'Pages'],
        ['key'=>'html_blocks',       'href'=>'modules/html_blocks.php',       'icon'=>'bi-layout-text-window',     'label'=>'HTML Blocks'],
        ['key'=>'menu',              'href'=>'modules/menu.php',              'icon'=>'bi-list-nested',            'label'=>'Menus'],
    ]],
    'Courses & Blog' => ['icon' => 'bi-journals', 'items' => [
        ['key'=>'course_categories', 'href'=>'modules/course_categories.php', 'icon'=>'bi-tags-fill',              'label'=>'Course Categories'],
        ['key'=>'courses',           'href'=>'modules/courses.php',           'icon'=>'bi-mortarboard-fill',       'label'=>'Courses'],
        ['key'=>'blog_categories',   'href'=>'modules/blog_categories.php',   'icon'=>'bi-tag-fill',               'label'=>'Blog Categories'],
        ['key'=>'blogs',             'href'=>'modules/blogs.php',             'icon'=>'bi-journal-text',           'label'=>'Blog Posts'],
]],
    'Sections' => ['icon' => 'bi-layout-text-window-reverse', 'items' => [
        ['key'=>'testimonials',      'href'=>'modules/testimonials.php',      'icon'=>'bi-chat-quote-fill',        'label'=>'Testimonials'],
        ['key'=>'faq',              'href'=>'modules/faq.php',                'icon'=>'bi-question-circle-fill',   'label'=>'FAQ'],
        ['key'=>'gallery',           'href'=>'modules/gallery.php',           'icon'=>'bi-images',                 'label'=>'Gallery'],
        ['key'=>'team',              'href'=>'modules/team.php',              'icon'=>'bi-people-fill',            'label'=>'Team'],
        ['key'=>'counters',          'href'=>'modules/counters.php',          'icon'=>'bi-bar-chart-fill',         'label'=>'Counters'],
        ['key'=>'why_choose',       'href'=>'modules/why_choose.php',       'icon'=>'bi-stars',                  'label'=>'Why Choose Us'],
        ['key'=>'banners',           'href'=>'modules/banners.php',           'icon'=>'bi-image-alt',              'label'=>'Banners'],
        ['key'=>'locations',        'href'=>'modules/locations.php',       'icon'=>'bi-geo-alt-fill',           'label'=>'Locations'],
        ['key'=>'social',           'href'=>'modules/social.php',           'icon'=>'bi-share-fill',             'label'=>'Social Links'],
        ['key'=>'popup',            'href'=>'modules/popup.php',            'icon'=>'bi-window',                 'label'=>'Popup'],
    ]],
    'Inquiries' => ['icon' => 'bi-inbox-fill', 'items' => [
        ['key'=>'contacts',          'href'=>'modules/contacts.php',          'icon'=>'bi-envelope-fill',          'label'=>'Contact Messages'],
        ['key'=>'enquiries',         'href'=>'modules/enquiries.php',         'icon'=>'bi-chat-dots-fill',         'label'=>'Course Enquiries'],
        ['key'=>'newsletter',        'href'=>'modules/newsletter.php',        'icon'=>'bi-megaphone-fill',         'label'=>'Newsletter'],
    ]],
    'SEO & Code' => ['icon' => 'bi-search-heart-fill', 'items' => [
        ['key'=>'seo',               'href'=>'modules/seo.php',               'icon'=>'bi-search',                 'label'=>'SEO Settings'],
        ['key'=>'header_codes',      'href'=>'modules/header_codes.php',      'icon'=>'bi-code-slash',             'label'=>'Header Codes'],
        ['key'=>'footer_codes',      'href'=>'modules/footer_codes.php',      'icon'=>'bi-code-square',            'label'=>'Footer Codes'],
        ['key'=>'custom_css',        'href'=>'modules/custom_css.php',        'icon'=>'bi-filetype-css',           'label'=>'Custom CSS'],
        ['key'=>'custom_js',         'href'=>'modules/custom_js.php',         'icon'=>'bi-filetype-js',            'label'=>'Custom JS'],
        ['key'=>'redirects',         'href'=>'modules/redirects.php',         'icon'=>'bi-arrow-left-right',       'label'=>'Redirects'],
    ]],
    'System' => ['icon' => 'bi-hdd-stack-fill', 'items' => [
        ['key'=>'media',             'href'=>'modules/media.php',             'icon'=>'bi-collection-fill',        'label'=>'Media Library'],
        ['key'=>'users',             'href'=>'modules/users.php',             'icon'=>'bi-person-fill-gear',       'label'=>'Admin Users'],
    ]],
];
?>
<body>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <a href="<?= ADMIN_URL ?>/index.php"><span class="logo-mark">N</span> <span class="logo-text">NIIB Admin</span></a>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= ADMIN_URL ?>/index.php" class="<?= $active==='dashboard'?'active':'' ?>"><i class="bi bi-grid-fill"></i> Dashboard</a>

        <?php foreach ($nav_groups as $groupLabel => $group):
            $isOpen = false;
            foreach ($group['items'] as $i) if ($i['key'] === $active) { $isOpen = true; break; }
        ?>
        <div class="nav-group<?= $isOpen ? ' open' : '' ?>">
            <button type="button" class="nav-group-toggle">
                <i class="bi <?= e($group['icon']) ?>"></i>
                <span><?= e($groupLabel) ?></span>
                <i class="bi bi-chevron-down nav-group-chev"></i>
            </button>
            <div class="nav-group-items">
                <?php foreach ($group['items'] as $i): ?>
                    <a href="<?= ADMIN_URL ?>/<?= $i['href'] ?>" class="<?= $active===$i['key']?'active':'' ?>"><i class="bi <?= e($i['icon']) ?>"></i> <?= e($i['label']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="nav-divider"></div>
        <a href="<?= SITE_URL ?>" target="_blank"><i class="bi bi-globe2"></i> Visit Website</a>
        <a href="<?= ADMIN_URL ?>/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </nav>
</aside>
<script>
(function(){
    document.querySelectorAll('.sidebar-nav .nav-group-toggle').forEach(function(btn){
        btn.addEventListener('click', function(){
            btn.parentElement.classList.toggle('open');
        });
    });
})();
</script>

<div class="admin-main">
    <header class="admin-topbar">
        <button class="sidebar-toggle d-lg-none" onclick="document.body.classList.toggle('sidebar-open')"><i class="bi bi-list"></i></button>
        <h5 class="m-0"><?= e($page_title) ?></h5>
        <div class="ms-auto admin-user">
            <span class="d-none d-md-inline me-2"><i class="bi bi-person-circle"></i> <?= e($current_admin['name']) ?> <small class="text-muted">(<?= e(str_replace('_',' ',$current_admin['role'])) ?>)</small></span>
            <a href="<?= ADMIN_URL ?>/logout.php" class="btn btn-sm btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </header>

    <main class="admin-content">
        <?php if ($f = get_flash('success')): ?><div class="alert alert-success"><?= e($f) ?></div><?php endif; ?>
        <?php if ($f = get_flash('error')): ?><div class="alert alert-danger"><?= e($f) ?></div><?php endif; ?>

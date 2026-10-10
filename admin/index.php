<?php
require __DIR__ . '/includes/auth.php';
$page_title = 'Dashboard';
$active = 'dashboard';

$pdo = db();
function safe_count($pdo, $sql, $params = []) {
    try { $s = $pdo->prepare($sql); $s->execute($params); return (int)$s->fetchColumn(); } catch (Throwable $e) { return 0; }
}

$stats = [
    'enquiries'   => safe_count($pdo, "SELECT COUNT(*) FROM enquiry_forms"),
    'unread_enq'  => safe_count($pdo, "SELECT COUNT(*) FROM enquiry_forms WHERE is_read = 0"),
    'contacts'    => safe_count($pdo, "SELECT COUNT(*) FROM contact_messages"),
    'unread_ct'   => safe_count($pdo, "SELECT COUNT(*) FROM contact_messages WHERE is_read = 0"),
    'courses'     => safe_count($pdo, "SELECT COUNT(*) FROM courses WHERE status = 1"),
    'blogs'       => safe_count($pdo, "SELECT COUNT(*) FROM blog_posts WHERE status = 1"),
    'pages'       => safe_count($pdo, "SELECT COUNT(*) FROM pages WHERE status = 1"),
    'subscribers' => safe_count($pdo, "SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 1"),
];

$recent_contacts = [];
$recent_enquiries = [];
try {
    $recent_contacts = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 5")->fetchAll();
    $recent_enquiries = $pdo->query("SELECT * FROM enquiry_forms ORDER BY id DESC LIMIT 5")->fetchAll();
} catch (Throwable $e) {}

include __DIR__ . '/includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon bg-1"><i class="bi bi-chat-dots-fill"></i></div>
            <div>
                <h3><?= $stats['enquiries'] ?></h3>
                <p>Course Enquiries <?php if($stats['unread_enq']): ?><span class="badge bg-danger ms-1"><?= $stats['unread_enq'] ?> new</span><?php endif; ?></p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon bg-2"><i class="bi bi-envelope-fill"></i></div>
            <div>
                <h3><?= $stats['contacts'] ?></h3>
                <p>Contact Messages <?php if($stats['unread_ct']): ?><span class="badge bg-danger ms-1"><?= $stats['unread_ct'] ?> new</span><?php endif; ?></p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon bg-3"><i class="bi bi-mortarboard-fill"></i></div>
            <div><h3><?= $stats['courses'] ?></h3><p>Active Courses</p></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon bg-4"><i class="bi bi-journal-text"></i></div>
            <div><h3><?= $stats['blogs'] ?></h3><p>Blog Posts</p></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon bg-5"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div><h3><?= $stats['pages'] ?></h3><p>Pages</p></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon bg-6"><i class="bi bi-megaphone-fill"></i></div>
            <div><h3><?= $stats['subscribers'] ?></h3><p>Newsletter Subscribers</p></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span><i class="bi bi-envelope-fill"></i> Recent Contact Messages</span>
                <a href="modules/contacts.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_contacts)): ?>
                    <div class="text-center text-muted py-4">No messages yet.</div>
                <?php else: ?>
                <table class="table mb-0">
                    <thead><tr><th>Name</th><th>Email</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recent_contacts as $m): ?>
                    <tr>
                        <td><?= e($m['name']) ?> <?php if(!$m['is_read']): ?><span class="badge bg-danger">new</span><?php endif; ?></td>
                        <td><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></td>
                        <td><?= fdate($m['created_at']) ?></td>
                        <td><a href="modules/contacts.php?view=<?= (int)$m['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span><i class="bi bi-chat-dots-fill"></i> Recent Course Enquiries</span>
                <a href="modules/enquiries.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_enquiries)): ?>
                    <div class="text-center text-muted py-4">No enquiries yet.</div>
                <?php else: ?>
                <table class="table mb-0">
                    <thead><tr><th>Name</th><th>Course</th><th>Phone</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent_enquiries as $m): ?>
                    <tr>
                        <td><?= e($m['name']) ?></td>
                        <td><?= e($m['course_name'] ?: '-') ?></td>
                        <td><?= e($m['phone']) ?></td>
                        <td><?= fdate($m['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-lightning-fill"></i> Quick Actions</div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3 col-6"><a href="modules/courses.php?action=add" class="btn btn-outline-primary w-100"><i class="bi bi-plus-circle"></i> Add Course</a></div>
            <div class="col-md-3 col-6"><a href="modules/blogs.php?action=add" class="btn btn-outline-primary w-100"><i class="bi bi-plus-circle"></i> Add Blog</a></div>
            <div class="col-md-3 col-6"><a href="modules/pages.php?action=add" class="btn btn-outline-primary w-100"><i class="bi bi-plus-circle"></i> Add Page</a></div>
            <div class="col-md-3 col-6"><a href="modules/settings.php" class="btn btn-outline-primary w-100"><i class="bi bi-gear"></i> Settings</a></div>
            <div class="col-md-3 col-6"><a href="modules/seo.php" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> SEO Settings</a></div>
            <div class="col-md-3 col-6"><a href="modules/header_codes.php" class="btn btn-outline-primary w-100"><i class="bi bi-code"></i> Header Codes</a></div>
            <div class="col-md-3 col-6"><a href="modules/custom_css.php" class="btn btn-outline-primary w-100"><i class="bi bi-filetype-css"></i> Custom CSS</a></div>
            <div class="col-md-3 col-6"><a href="<?= SITE_URL ?>" target="_blank" class="btn btn-outline-secondary w-100"><i class="bi bi-globe2"></i> View Site</a></div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
require __DIR__ . '/includes/init.php';
$pdo = db();

$catSlug = trim($_GET['cat'] ?? '');
$q = trim($_GET['q'] ?? '');
$perPage = 9;
$page = max(1,(int)($_GET['page'] ?? 1));
$offset = ($page-1)*$perPage;

$where = "c.status=1"; $params = [];
if ($catSlug !== '') { $where .= " AND cat.slug = ?"; $params[] = $catSlug; }
if ($q !== '') { $where .= " AND (c.title LIKE ? OR c.short_description LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }

$stmt = $pdo->prepare("SELECT c.*, cat.name AS cat_name, cat.slug AS cat_slug FROM courses c LEFT JOIN course_categories cat ON cat.id=c.category_id WHERE $where ORDER BY c.is_featured DESC, c.id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$courses = $stmt->fetchAll();

$ct = $pdo->prepare("SELECT COUNT(*) FROM courses c LEFT JOIN course_categories cat ON cat.id=c.category_id WHERE $where");
$ct->execute($params);
$total = (int)$ct->fetchColumn();

$categories = $pdo->query("SELECT * FROM course_categories WHERE status=1 ORDER BY sort_order, name")->fetchAll();

$page_seo['title'] = 'Signature Courses - ' . setting('site_name');
$page_seo['description'] = 'Browse our complete catalogue of professional beauty and aesthetics courses at NIIB Institute.';

niib_seo_set_breadcrumbs([
    ['name' => 'Home',    'url' => SITE_URL . '/'],
    ['name' => 'Courses'],
]);

// ItemList schema for the course collection
if ($courses) {
    $items = [];
    foreach ($courses as $i => $c) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'url'      => SITE_URL . '/course/' . rawurlencode($c['slug']),
            'name'     => $c['title'],
        ];
    }
    niib_seo_add_schema([
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'numberOfItems'   => count($items),
        'itemListElement' => $items,
    ]);
}

include 'includes/header.php';
?>

<section class="page-titlebar">
    <div class="container">
        <nav class="pt-crumbs">
            <a href="<?= SITE_URL ?>/">Home</a>
            <i class="bi bi-chevron-right"></i>
            <span>Courses</span>
        </nav>
        <h1 class="pt-title">Signature <em>Courses</em></h1>
        <p class="pt-lede">Curated programs combining clinical precision with editorial artistry. Each pathway is designed to forge industry-ready professionals.</p>
    </div>
</section>

<style>
.courses-layout{display:grid;grid-template-columns:1fr;gap:32px;}
/* Sidebar stacks in normal flow on mobile/tablet; it only becomes a sticky
   left column on desktop, where the 2-column layout actually exists.
   (Making it sticky at all widths caused the sidebar to slide over the
   course cards on the single-column mobile/tablet layout.) */
.courses-layout aside{position:static;}
.courses-layout .courses-main{min-width:0;}
@media(min-width:1024px){
    .courses-layout{grid-template-columns:280px minmax(0,1fr);gap:48px;align-items:start;}
    .courses-layout aside{position:sticky;top:100px;}
}
.courses-layout .meta-line{color:var(--on-surface-variant);margin-bottom:32px;font-size:var(--label-lg);letter-spacing:0.1em;text-transform:uppercase;}
.courses-layout .card-desc{color:var(--on-surface-variant);font-size:var(--body-md);margin-bottom:16px;}
.courses-layout .sidebar-title{font-family:var(--font-headline);font-size:var(--headline-sm);margin-bottom:16px;color:var(--primary);font-weight:500;}
/* Force 2-column course grid inside courses page (override global 3-col rule) */
.courses-layout .course-list-grid{grid-template-columns:1fr;}

.course-list-grid .card-body h2 {
    font-size: 28px;
}
@media(min-width:640px){
    .courses-layout .course-list-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media(min-width:1024px){
    .courses-layout .course-list-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
}
</style>

<section>
<div class="container">
    <div class="courses-layout">

        <aside>
            <form class="search-box">
                <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search courses...">
                <button type="submit"><i class="bi bi-search"></i></button>
            </form>

            <h3 class="sidebar-title">Categories</h3>
            <ul class="sidebar-list">
                <li><a href="<?= SITE_URL ?>/courses" class="<?= $catSlug===''?'active':'' ?>">All Courses</a></li>
                <?php foreach ($categories as $c): ?>
                    <li><a href="?cat=<?= e($c['slug']) ?>" class="<?= $catSlug===$c['slug']?'active':'' ?>"><?= e($c['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div class="courses-main">
            <p class="meta-line">Showing <?= count($courses) ?> of <?= $total ?> courses</p>

            <div class="course-list-grid">
                <?php foreach ($courses as $c): ?>
                <div class="course-card">
                    <img class="card-img" src="<?= e(img_url($c['image'])) ?>" alt="<?= e($c['title']) ?>">
                    <div class="card-body">
                        <?php if($c['cat_name']): ?><span class="cat"><?= e($c['cat_name']) ?></span><?php endif; ?>
                        <h2><a href="<?= SITE_URL ?>/course/<?= e($c['slug']) ?>"><?= e($c['title']) ?></a></h2>
                        <p class="card-desc"><?= e(excerpt($c['short_description'],16)) ?></p>
                        <div class="meta">
                            <span><i class="bi bi-clock"></i> <?= e($c['duration']) ?></span>
                            <span><i class="bi bi-bar-chart"></i> <?= e($c['level']) ?></span>
                        </div>
                        <div class="card-footer">
                            <div class="price">
                                <?php if($c['original_fees']): ?><span class="old"><?= e($c['original_fees']) ?></span><?php endif; ?>
                                <?= e($c['fees']) ?>
                            </div>
                            <a href="<?= SITE_URL ?>/course/<?= e($c['slug']) ?>" class="btn btn-ghost btn-sm">Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($courses)): ?>
                <div class="empty-state" style="grid-column:1/-1;">
                    <i class="bi bi-search"></i>
                    <p>No courses found. Try different search terms.</p>
                </div>
                <?php endif; ?>
            </div>

            <?php
            $base = SITE_URL.'/courses.php?'.http_build_query(array_filter(['cat'=>$catSlug,'q'=>$q]));
            echo paginate($total, $perPage, $page, $base);
            ?>
        </div>
    </div>
</div>
</section>

<?php include 'includes/footer.php'; ?>

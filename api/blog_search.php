<?php
require __DIR__ . '/../includes/init.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = db();

$q       = trim($_GET['q'] ?? '');
$catSlug = trim($_GET['cat'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset  = ($page - 1) * $perPage;

$where  = "b.status = 1";
$params = [];
if ($catSlug !== '') { $where .= " AND cat.slug = ?"; $params[] = $catSlug; }
if ($q !== '')       { $where .= " AND (b.title LIKE ? OR b.short_description LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }

// total
$ct = $pdo->prepare("SELECT COUNT(*) FROM blog_posts b LEFT JOIN blog_categories cat ON cat.id = b.category_id WHERE $where");
$ct->execute($params);
$total = (int)$ct->fetchColumn();

// rows
$stmt = $pdo->prepare("SELECT b.*, cat.name AS cat_name, cat.slug AS cat_slug
                       FROM blog_posts b LEFT JOIN blog_categories cat ON cat.id = b.category_id
                       WHERE $where
                       ORDER BY b.publish_date DESC, b.id DESC
                       LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Render markup that mirrors blog.php's .post-grid contents
ob_start();
if (empty($posts)) {
    echo '<div class="empty-state" style="grid-column:1/-1;"><i class="bi bi-journal-x"></i><p>No posts found.</p></div>';
} else {
    foreach ($posts as $bp) {
        ?>
        <article class="blog-card">
            <a href="<?= SITE_URL ?>/blog/<?= e($bp['slug']) ?>"><img class="card-img" src="<?= e(img_url($bp['featured_image'])) ?>" alt="<?= e($bp['title']) ?>" loading="lazy"></a>
            <div class="card-body">
                <div class="blog-meta"><i class="bi bi-calendar3"></i> <?= fdate($bp['publish_date']) ?> &nbsp;·&nbsp; <?= e($bp['cat_name']) ?></div>
                <h5><a href="<?= SITE_URL ?>/blog/<?= e($bp['slug']) ?>"><?= e($bp['title']) ?></a></h5>
                <p class="post-excerpt"><?= e(excerpt($bp['short_description'], 18)) ?></p>
                <a href="<?= SITE_URL ?>/blog/<?= e($bp['slug']) ?>" class="read-article">Read Article →</a>
            </div>
        </article>
        <?php
    }
}
$html = ob_get_clean();

echo json_encode([
    'ok'    => true,
    'html'  => $html,
    'total' => $total,
    'shown' => count($posts),
    'q'     => $q,
    'cat'   => $catSlug,
    'page'  => $page,
]);

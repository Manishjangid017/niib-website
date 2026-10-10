<?php
require __DIR__ . '/includes/init.php';
$pdo = db();
$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: blog.php'); exit; }

$stmt = $pdo->prepare("SELECT b.*, cat.name AS cat_name, cat.slug AS cat_slug FROM blog_posts b LEFT JOIN blog_categories cat ON cat.id=b.category_id WHERE b.slug=? AND b.status=1");
$stmt->execute([$slug]);
$b = $stmt->fetch();
if (!$b) { http_response_code(404); include __DIR__.'/404.php'; exit; }

$pdo->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?")->execute([$b['id']]);

$page_seo['title']       = $b['meta_title'] ?: ($b['title'] . ' - ' . setting('site_name'));
$page_seo['description'] = $b['meta_description'] ?: niib_seo_clean_text($b['short_description']);
$page_seo['keywords']    = $b['meta_keywords'] ?: $b['tags'];
$page_seo['og_type']     = 'article';
$page_seo['article_published'] = date('c', strtotime($b['publish_date'] ?: $b['created_at'] ?: 'now'));
$page_seo['article_modified']  = date('c', strtotime($b['updated_at'] ?: $b['publish_date']));
$page_seo['article_author']    = $b['author'];
$page_seo['article_section']   = $b['cat_name'];
if ($b['canonical_url']) $page_seo['canonical'] = $b['canonical_url'];
if ($b['og_image'])      $page_seo['og_image']  = img_url($b['og_image']);
elseif ($b['featured_image']) $page_seo['og_image'] = img_url($b['featured_image']);

// Breadcrumbs
niib_seo_set_breadcrumbs([
    ['name' => 'Home', 'url' => SITE_URL . '/'],
    ['name' => 'Blog', 'url' => SITE_URL . '/blog'],
    ['name' => $b['cat_name'] ?: 'Article', 'url' => $b['cat_slug'] ? SITE_URL . '/blog?cat=' . $b['cat_slug'] : null],
    ['name' => $b['title']],
]);

// Page-level Article schema
if ($b['schema_code']) {
    $page_seo['schema'] = $b['schema_code'];
} else {
    niib_seo_add_schema(niib_seo_article($b, $b['cat_name'] ?? ''));
}

// Reading time calculation
$wordCount    = str_word_count(strip_tags($b['content']));
$readingTime  = max(1, (int)ceil($wordCount / 220));

// Related and recent
$related = $pdo->prepare("SELECT * FROM blog_posts WHERE category_id=? AND id<>? AND status=1 ORDER BY publish_date DESC LIMIT 3");
$related->execute([$b['category_id'], $b['id']]);
$related = $related->fetchAll();

$recent = $pdo->prepare("SELECT id, title, slug, featured_image, publish_date FROM blog_posts WHERE id<>? AND status=1 ORDER BY publish_date DESC LIMIT 4");
$recent->execute([$b['id']]);
$recent = $recent->fetchAll();

$categoriesAll = $pdo->query("SELECT c.id, c.name, c.slug, COUNT(b.id) AS c FROM blog_categories c LEFT JOIN blog_posts b ON b.category_id=c.id AND b.status=1 WHERE c.status=1 GROUP BY c.id, c.name, c.slug ORDER BY c DESC, c.name LIMIT 6")->fetchAll();

$shareUrl   = current_url();
$shareTitle = rawurlencode($b['title']);
$shareU     = rawurlencode($shareUrl);

include 'includes/header.php';
?>

<!-- =========== READING PROGRESS BAR =========== -->
<div class="blog-progress"><div class="blog-progress-bar" data-progress></div></div>

<!-- =========== HERO =========== -->
<section class="blog-hero">
    <?php if ($b['featured_image']): ?>
    <div class="blog-hero-bg" style="background-image:url('<?= e(img_url($b['featured_image'])) ?>')"></div>
    <?php endif; ?>
    <div class="container blog-hero-inner">
        <nav class="blog-crumbs">
            <a href="<?= SITE_URL ?>/">Home</a>
            <i class="bi bi-chevron-right"></i>
            <a href="<?= SITE_URL ?>/blog">The Journal</a>
            <?php if ($b['cat_slug']): ?>
            <i class="bi bi-chevron-right"></i>
            <a href="<?= SITE_URL ?>/blog?cat=<?= e($b['cat_slug']) ?>"><?= e($b['cat_name']) ?></a>
            <?php endif; ?>
        </nav>
        <?php if ($b['cat_name']): ?>
        <a class="blog-cat-pill" href="<?= SITE_URL ?>/blog?cat=<?= e($b['cat_slug']) ?>"><?= e($b['cat_name']) ?></a>
        <?php endif; ?>
        <h1 class="blog-hero-title"><?= e($b['title']) ?></h1>
        <?php if ($b['short_description']): ?>
        <p class="blog-hero-sub"><?= e($b['short_description']) ?></p>
        <?php endif; ?>
        <div class="blog-hero-meta">
            <div class="blog-author-inline">
                <div class="blog-avatar"><span><?= e(strtoupper(mb_substr($b['author'],0,1))) ?></span></div>
                <div>
                    <div class="blog-author-name"><?= e($b['author']) ?></div>
                    <div class="blog-author-role">NIIB Editorial</div>
                </div>
            </div>
            <div class="blog-meta-stats">
                <span><i class="bi bi-calendar3"></i> <?= fdate($b['publish_date'], 'd M, Y') ?></span>
                <span><i class="bi bi-clock"></i> <?= $readingTime ?> min read</span>
                <span><i class="bi bi-eye"></i> <?= number_format((int)$b['views']) ?> views</span>
            </div>
        </div>
    </div>
</section>

<!-- =========== ARTICLE + SIDEBAR =========== -->
<section class="blog-detail">
<div class="container">
<div class="blog-detail-grid">

    <!-- LEFT: article -->
    <article class="blog-article">
        <!-- Sticky share rail (desktop) -->
        <aside class="blog-share-rail" aria-label="Share this post">
            <span class="share-label">Share</span>
            <a class="share-btn" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareU ?>" title="Share on Facebook"><i class="bi bi-facebook"></i></a>
            <a class="share-btn" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?= $shareU ?>&text=<?= $shareTitle ?>" title="Share on Twitter"><i class="bi bi-twitter-x"></i></a>
            <a class="share-btn" target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareU ?>" title="Share on LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a class="share-btn" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text=<?= $shareTitle ?>%20<?= $shareU ?>" title="Share on WhatsApp"><i class="bi bi-whatsapp"></i></a>
            <button type="button" class="share-btn" data-copy="<?= e($shareUrl) ?>" title="Copy link"><i class="bi bi-link-45deg"></i></button>
        </aside>

        <div class="blog-content detail-content">
            <?= $b['content'] ?>
        </div>

        <?php if ($b['tags']): ?>
        <div class="blog-tags-row">
            <strong>Tagged:</strong>
            <?php foreach (explode(',', $b['tags']) as $tag): if (!trim($tag)) continue; ?>
                <span class="blog-tag-chip"><?= e(trim($tag)) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Inline share strip (mobile-visible) -->
        <div class="blog-share-strip">
            <span>Share this article</span>
            <div>
                <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareU ?>" title="Facebook"><i class="bi bi-facebook"></i></a>
                <a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?= $shareU ?>&text=<?= $shareTitle ?>" title="Twitter"><i class="bi bi-twitter-x"></i></a>
                <a target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareU ?>" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                <a target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text=<?= $shareTitle ?>%20<?= $shareU ?>" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                <button type="button" data-copy="<?= e($shareUrl) ?>" title="Copy link"><i class="bi bi-link-45deg"></i></button>
            </div>
        </div>

        <!-- Author bio strip -->
        <div class="blog-author-strip">
            <div class="blog-avatar blog-avatar-lg"><span><?= e(strtoupper(mb_substr($b['author'],0,1))) ?></span></div>
            <div class="blog-author-body">
                <span class="blog-author-eyebrow">Written by</span>
                <h4><?= e($b['author']) ?></h4>
                <p>Industry educator at NIIB Institute — sharing insights from 22+ years of beauty &amp; clinical training.</p>
                <a href="<?= SITE_URL ?>/blog" class="blog-author-link">More from this author <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </article>

    <!-- RIGHT: sticky sidebar -->
    <aside class="blog-sidebar-right">
        <!-- Newsletter -->
        <div class="bs-card bs-newsletter">
            <i class="bi bi-envelope-paper-fill bs-card-icon"></i>
            <h4>Join Our Newsletter</h4>
            <p>Get weekly career tips, beauty trends and exclusive offers from NIIB direct to your inbox.</p>
            <form id="newsletterForm">
                <input type="email" name="email" placeholder="your@email.com" required>
                <?= niib_recaptcha_widget() ?>
                <button type="submit" class="btn btn-cta">Subscribe</button>
                <div id="newsletterMsg" class="bs-form-msg"></div>
            </form>
        </div>

        <!-- Recent posts -->
        <?php if ($recent): ?>
        <div class="bs-card">
            <span class="bs-card-eyebrow">Latest Articles</span>
            <h4 class="bs-card-title">Trending This Week</h4>
            <ul class="bs-recent">
                <?php foreach ($recent as $i => $rp): ?>
                <li>
                    <a href="<?= SITE_URL ?>/blog/<?= e($rp['slug']) ?>">
                        <div class="bs-recent-num"><?= sprintf('%02d', $i + 1) ?></div>
                        <div class="bs-recent-body">
                            <h6><?= e($rp['title']) ?></h6>
                            <p><i class="bi bi-calendar3"></i> <?= fdate($rp['publish_date'], 'd M, Y') ?></p>
                        </div>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Categories -->
        <?php if ($categoriesAll): ?>
        <div class="bs-card">
            <span class="bs-card-eyebrow">Browse Topics</span>
            <h4 class="bs-card-title">Categories</h4>
            <ul class="bs-cats">
                <?php foreach ($categoriesAll as $c): ?>
                <li><a href="<?= SITE_URL ?>/blog?cat=<?= e($c['slug']) ?>"><span><?= e($c['name']) ?></span><em><?= (int)$c['c'] ?></em></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- CTA -->
        <div class="bs-card bs-cta-card">
            <i class="bi bi-mortarboard-fill bs-card-icon"></i>
            <h4>Start Your Career</h4>
            <p>Explore NIIB's industry-recognized programs in beauty, makeup, hair and more.</p>
            <a href="<?= SITE_URL ?>/courses" class="btn btn-cta">Browse Courses</a>
        </div>
    </aside>

</div>
</div>
</section>

<!-- =========== RELATED ARTICLES =========== -->
<?php if ($related): ?>
<section class="blog-related surface-bg">
<div class="container">
    <div class="section-title">
        <span class="section-eyebrow">Continue Reading</span>
        <h2>You Might Also Like</h2>
    </div>
    <div class="blog-related-grid">
        <?php foreach ($related as $r): ?>
        <article class="blog-card">
            <a href="<?= SITE_URL ?>/blog/<?= e($r['slug']) ?>"><img class="card-img" src="<?= e(img_url($r['featured_image'])) ?>" alt="<?= e($r['title']) ?>" loading="lazy"></a>
            <div class="card-body">
                <div class="blog-meta"><i class="bi bi-calendar3"></i> <?= fdate($r['publish_date']) ?></div>
                <h5><a href="<?= SITE_URL ?>/blog/<?= e($r['slug']) ?>"><?= e($r['title']) ?></a></h5>
                <p style="color:var(--on-surface-variant);font-size:var(--body-md);margin:8px 0 16px;"><?= e(excerpt($r['short_description'],16)) ?></p>
                <a href="<?= SITE_URL ?>/blog/<?= e($r['slug']) ?>" style="font-size:var(--label-lg);font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--primary);border-bottom:1px solid var(--primary);padding-bottom:2px;">Read Article →</a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div>
</section>
<?php endif; ?>

<script>
(function(){
    // Reading progress bar
    var bar = document.querySelector('[data-progress]');
    var article = document.querySelector('.blog-article');
    if (bar && article) {
        function updateProgress(){
            var top = article.offsetTop;
            var height = article.offsetHeight;
            var scrolled = window.scrollY - top + window.innerHeight * 0.3;
            var pct = Math.max(0, Math.min(100, (scrolled / height) * 100));
            bar.style.width = pct + '%';
        }
        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();
    }

    // Copy link buttons
    document.querySelectorAll('[data-copy]').forEach(function(btn){
        btn.addEventListener('click', function(){
            var url = btn.dataset.copy;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function(){
                    var orig = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check2"></i>';
                    btn.classList.add('is-copied');
                    setTimeout(function(){ btn.innerHTML = orig; btn.classList.remove('is-copied'); }, 1600);
                });
            }
        });
    });
})();
</script>

<?php include 'includes/footer.php'; ?>

<?php
require __DIR__ . '/includes/init.php';
$pdo = db();

$catSlug = trim($_GET['cat'] ?? '');
$q = trim($_GET['q'] ?? '');
$perPage = 6;
$page = max(1,(int)($_GET['page'] ?? 1));
$offset = ($page-1)*$perPage;

$where = "b.status=1"; $params = [];
if ($catSlug !== '') { $where .= " AND cat.slug = ?"; $params[] = $catSlug; }
if ($q !== '') { $where .= " AND (b.title LIKE ? OR b.short_description LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }

$stmt = $pdo->prepare("SELECT b.*, cat.name AS cat_name, cat.slug AS cat_slug FROM blog_posts b LEFT JOIN blog_categories cat ON cat.id=b.category_id WHERE $where ORDER BY b.publish_date DESC, b.id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$ct = $pdo->prepare("SELECT COUNT(*) FROM blog_posts b LEFT JOIN blog_categories cat ON cat.id=b.category_id WHERE $where");
$ct->execute($params);
$total = (int)$ct->fetchColumn();

$categories = $pdo->query("SELECT * FROM blog_categories WHERE status=1 ORDER BY name")->fetchAll();

$page_seo['title'] = 'Editorial - ' . setting('site_name');
$page_seo['description'] = 'Insights, trends and career guidance from the experts at NIIB Institute.';

niib_seo_set_breadcrumbs([
    ['name' => 'Home', 'url' => SITE_URL . '/'],
    ['name' => 'Blog'],
]);

niib_seo_add_schema([
    '@context'    => 'https://schema.org',
    '@type'       => 'Blog',
    'name'        => 'NIIB Editorial — The Journal',
    'url'         => SITE_URL . '/blog',
    'description' => 'Industry insights, career guidance and the latest from our master educators.',
    'publisher'   => ['@id' => SITE_URL . '#organization'],
]);

include 'includes/header.php';
?>

<!-- =========== TITLE BAR =========== -->
<section class="page-titlebar">
    <div class="container">
        <nav class="pt-crumbs">
            <a href="<?= SITE_URL ?>/">Home</a>
            <i class="bi bi-chevron-right"></i>
            <span>Blog</span>
        </nav>
        <h1 class="pt-title">The <em>Journal</em></h1>
        <p class="pt-lede">Industry insights, career guidance and the latest from our master educators.</p>
    </div>
</section>

<style>
.blog-layout{}
@media(min-width:1024px){
    .blog-layout{grid-template-columns:minmax(0,1fr) 300px;gap:60px;align-items:start;}
}
.blog-layout .blog-main{min-width:0;}
.blog-layout .blog-sidebar{position:sticky;top:100px;}
.blog-layout .post-grid{display:grid;grid-template-columns:1fr;gap:24px;}
@media(min-width:640px){.blog-layout .post-grid{grid-template-columns:repeat(3,1fr);}}
.blog-layout .read-article{font-size:var(--label-lg);font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--primary);border-bottom:1px solid var(--primary);padding-bottom:2px;display:inline-block;}
.blog-layout .meta-info{color:var(--on-surface-variant);margin-bottom:32px;font-size:var(--label-lg);letter-spacing:0.1em;text-transform:uppercase;}
.blog-layout .post-excerpt{color:var(--on-surface-variant);font-size:var(--body-md);margin-bottom:16px;}
.blog-layout .sidebar-title{font-family:var(--font-headline);font-size:var(--headline-sm);margin:32px 0 16px;color:var(--primary);font-weight:500;}
</style>

<section>
<div class="container">
<div class="blog-layout">

<div class="blog-main" data-blog-results>
    <h2 class="meta-info" data-blog-meta>Showing <?= count($posts) ?> of <?= $total ?> posts</h2>

    <div class="post-grid" data-blog-grid>
        <?php foreach ($posts as $bp): ?>
        <article class="blog-card">
            <a href="<?= SITE_URL ?>/blog/<?= e($bp['slug']) ?>"><img class="card-img" src="<?= e(img_url($bp['featured_image'])) ?>" alt="<?= e($bp['title']) ?>"></a>
            <div class="card-body">
                <div class="blog-meta"><i class="bi bi-calendar3"></i> <?= fdate($bp['publish_date']) ?> &nbsp;·&nbsp; <?= e($bp['cat_name']) ?></div>
                <h2 style="font-size:28px"><a href="<?= SITE_URL ?>/blog/<?= e($bp['slug']) ?>"><?= e($bp['title']) ?></a></h2>
                <p class="post-excerpt"><?= e(excerpt($bp['short_description'],18)) ?></p>
                <a href="<?= SITE_URL ?>/blog/<?= e($bp['slug']) ?>" class="read-article">Read Article →</a>
            </div>
        </article>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?>
        <div class="empty-state" style="grid-column:1/-1;"><i class="bi bi-journal-x"></i><p>No posts found.</p></div>
        <?php endif; ?>
    </div>

    <div data-blog-pagination>
        <?php $base = SITE_URL.'/blog.php?'.http_build_query(array_filter(['cat'=>$catSlug,'q'=>$q])); echo paginate($total,$perPage,$page,$base); ?>
    </div>
</div>
<!-- 
<aside class="blog-sidebar">
    <form class="search-box" data-blog-form onsubmit="event.preventDefault();">
        <input class="form-control" type="search" name="q" placeholder="Search posts..." value="<?= e($q) ?>" autocomplete="off" data-blog-input>
        <button type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
        <span class="search-spinner" data-blog-spinner aria-hidden="true"><i class="bi bi-arrow-repeat"></i></span>
    </form>
    <h3 class="sidebar-title">Categories</h3>
    <ul class="sidebar-list" data-blog-cats>
        <li><a href="#" data-cat="" class="<?= $catSlug===''?'active':'' ?>">All Posts</a></li>
        <?php foreach ($categories as $c): ?>
            <li><a href="#" data-cat="<?= e($c['slug']) ?>" class="<?= $catSlug===$c['slug']?'active':'' ?>"><?= e($c['name']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</aside> -->

</div>
</div>
</section>

<script>
(function(){
    var form    = document.querySelector('[data-blog-form]');
    var input   = document.querySelector('[data-blog-input]');
    var grid    = document.querySelector('[data-blog-grid]');
    var meta    = document.querySelector('[data-blog-meta]');
    var pager   = document.querySelector('[data-blog-pagination]');
    var spinner = document.querySelector('[data-blog-spinner]');
    var catsBox = document.querySelector('[data-blog-cats]');
    if (!form || !input || !grid) return;

    var endpoint = '<?= SITE_URL ?>/api/blog_search.php';
    var state    = { q: input.value || '', cat: <?= json_encode($catSlug) ?> };
    var t        = null;
    var inflight = null;

    function busy(on){
        if (!spinner) return;
        spinner.classList.toggle('is-active', !!on);
        grid.style.opacity = on ? '.55' : '1';
        grid.style.transition = 'opacity .15s ease';
    }

    function setUrl(){
        var params = new URLSearchParams();
        if (state.q)   params.set('q', state.q);
        if (state.cat) params.set('cat', state.cat);
        var qs = params.toString();
        var url = '<?= SITE_URL ?>/blog' + (qs ? '?' + qs : '');
        history.replaceState(null, '', url);
    }

    function fetchResults(){
        if (inflight && inflight.abort) inflight.abort();
        busy(true);
        var ctrl = (typeof AbortController !== 'undefined') ? new AbortController() : null;
        inflight = ctrl;
        var qs = new URLSearchParams({ q: state.q, cat: state.cat }).toString();

        fetch(endpoint + '?' + qs, { signal: ctrl ? ctrl.signal : undefined })
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (!data || !data.ok) return;
                grid.innerHTML = data.html;
                if (meta) meta.textContent = 'Showing ' + data.shown + ' of ' + data.total + ' posts';
                if (pager) pager.innerHTML = '';   // hide pagination during search; full page reload restores it
                setUrl();
            })
            .catch(function(err){ if (err && err.name === 'AbortError') return; })
            .finally(function(){ busy(false); inflight = null; });
    }

    // Live typing — debounced
    input.addEventListener('input', function(){
        clearTimeout(t);
        var v = input.value.trim();
        t = setTimeout(function(){
            if (v === state.q) return;
            state.q = v;
            fetchResults();
        }, 220);
    });

    // Press Enter does an immediate search
    form.addEventListener('submit', function(){
        clearTimeout(t);
        state.q = input.value.trim();
        fetchResults();
    });

    // Category clicks → AJAX too (and switches active state)
    if (catsBox){
        catsBox.addEventListener('click', function(e){
            var a = e.target.closest('a[data-cat]');
            if (!a) return;
            e.preventDefault();
            state.cat = a.dataset.cat || '';
            catsBox.querySelectorAll('a').forEach(function(x){ x.classList.remove('active'); });
            a.classList.add('active');
            fetchResults();
        });
    }
})();
</script>

<style>
.search-spinner{
    position: absolute;
    right: 56px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--secondary-container);
    font-size: 16px;
    opacity: 0;
    pointer-events: none;
    transition: opacity .15s ease;
}
.search-spinner.is-active{
    opacity: 1;
}
.search-spinner.is-active i{
    animation: searchSpin 1s linear infinite;
    display: inline-block;
}
@keyframes searchSpin { to { transform: rotate(360deg); } }
</style>

<?php include 'includes/footer.php'; ?>

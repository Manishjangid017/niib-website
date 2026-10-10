<?php
require __DIR__.'/../includes/auth.php';
$page_title='SEO Settings'; $active='seo';
$pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $keys = ['global_meta_title','global_meta_description','global_meta_keywords',
            'og_title','og_description','og_image','og_type',
            'twitter_card','twitter_site','robots_meta','organization_schema','robots_txt'];
        if (!empty($_FILES['og_image_file']['name'])) {
            $u = upload_file('og_image_file','media',['jpg','jpeg','png','webp','svg']);
            if ($u['ok']) $_POST['og_image'] = $u['path'];
        }
        $stmt=$pdo->prepare("INSERT INTO seo_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        foreach ($keys as $k) {
            if (isset($_POST[$k])) $stmt->execute([$k, (string)$_POST[$k]]);
        }
        log_admin_action('update','seo','Updated SEO settings');
        set_flash('success','SEO settings saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/seo.php');
}

$s = get_seo_settings();
include __DIR__.'/../includes/header.php';
?>
<div class="page-header"><h4>SEO Settings</h4>
<div><a href="<?= SITE_URL ?>/sitemap.xml" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-map"></i> Sitemap</a>
<a href="<?= SITE_URL ?>/robots.txt" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-robot"></i> robots.txt</a></div>
</div>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-header">Global Meta</div><div class="card-body">
    <div class="mb-3"><label class="form-label">Meta Title</label><input name="global_meta_title" class="form-control" value="<?= e($s['global_meta_title'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="global_meta_description" class="form-control" rows="2"><?= e($s['global_meta_description'] ?? '') ?></textarea></div>
    <div class="mb-3"><label class="form-label">Meta Keywords</label><input name="global_meta_keywords" class="form-control" value="<?= e($s['global_meta_keywords'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label">Robots Meta</label><input name="robots_meta" class="form-control" value="<?= e($s['robots_meta'] ?? 'index, follow') ?>"></div>
</div></div>
<div class="card"><div class="card-header">Open Graph (Facebook/LinkedIn)</div><div class="card-body">
    <div class="mb-3"><label class="form-label">OG Title</label><input name="og_title" class="form-control" value="<?= e($s['og_title'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label">OG Description</label><textarea name="og_description" class="form-control" rows="2"><?= e($s['og_description'] ?? '') ?></textarea></div>
    <div class="mb-3"><label class="form-label">OG Type</label><input name="og_type" class="form-control" value="<?= e($s['og_type'] ?? 'website') ?>"></div>
    <div class="mb-3"><label class="form-label">OG Image</label>
        <?php if(!empty($s['og_image'])): ?><img src="<?= e(img_url($s['og_image'])) ?>" class="preview-img"><br><?php endif; ?>
        <input type="text" name="og_image" class="form-control mt-2" value="<?= e($s['og_image'] ?? '') ?>" placeholder="Image path">
        <input type="file" name="og_image_file" class="form-control mt-2" accept="image/*">
    </div>
</div></div>
<div class="card"><div class="card-header">Twitter Card</div><div class="card-body">
    <div class="mb-3"><label class="form-label">Twitter Card Type</label>
        <select name="twitter_card" class="form-select">
            <option value="summary" <?= ($s['twitter_card']??'')==='summary'?'selected':'' ?>>summary</option>
            <option value="summary_large_image" <?= ($s['twitter_card']??'')==='summary_large_image'?'selected':'' ?>>summary_large_image</option>
        </select>
    </div>
    <div class="mb-3"><label class="form-label">Twitter @site Handle</label><input name="twitter_site" class="form-control" value="<?= e($s['twitter_site'] ?? '') ?>"></div>
</div></div>
</div>

<div class="col-lg-5">
<div class="card"><div class="card-header">Organization Schema (JSON-LD)</div><div class="card-body">
    <textarea name="organization_schema" class="code-editor" rows="10"><?= e($s['organization_schema'] ?? '') ?></textarea>
</div></div>
<div class="card"><div class="card-header">robots.txt Editor</div><div class="card-body">
    <textarea name="robots_txt" class="code-editor" rows="10"><?= e($s['robots_txt'] ?? '') ?></textarea>
    <small class="text-muted">Sitemap auto-generated at /sitemap.xml</small>
</div></div>
<button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Save SEO</button>
</div>
</div>
</form>
<?php include __DIR__.'/../includes/footer.php'; ?>

<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/crud.php';
$page_title='Blog Posts'; $active='blogs';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$pdo = db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        if ($title === '') throw new Exception('Title is required.');
        $slug = trim($_POST['slug'] ?? '') ?: $title;
        $slug = unique_slug('blog_posts', $slug, $editId);
        $data = [
            'category_id'=>(int)($_POST['category_id'] ?? 0) ?: null,
            'title'=>$title,
            'slug'=>$slug,
            'short_description'=>trim($_POST['short_description'] ?? ''),
            'content'=>$_POST['content'] ?? '',
            'author'=>trim($_POST['author'] ?? 'Admin'),
            'tags'=>trim($_POST['tags'] ?? ''),
            'publish_date'=>trim($_POST['publish_date'] ?? date('Y-m-d')) ?: date('Y-m-d'),
            'meta_title'=>trim($_POST['meta_title'] ?? ''),
            'meta_description'=>trim($_POST['meta_description'] ?? ''),
            'meta_keywords'=>trim($_POST['meta_keywords'] ?? ''),
            'canonical_url'=>trim($_POST['canonical_url'] ?? ''),
            'schema_code'=>$_POST['schema_code'] ?? '',
            'is_featured'=>isset($_POST['is_featured']) ? 1 : 0,
            'status'=>isset($_POST['status']) ? 1 : 0,
        ];
        if (!empty($_FILES['featured_image']['name'])) {
            $u = upload_file('featured_image','blogs');
            if ($u['ok']) $data['featured_image'] = $u['path'];
        } elseif (!empty($_POST['existing_featured_image'])) {
            $data['featured_image'] = $_POST['existing_featured_image'];
        }
        if ($editId>0) {
            $sets=[];$vals=[];
            foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}
            $vals[]=$editId;
            $pdo->prepare("UPDATE blog_posts SET ".implode(',',$sets).", updated_at=NOW() WHERE id=?")->execute($vals);
            log_admin_action('update','blogs','Updated blog #'.$editId);
            set_flash('success','Blog updated.');
        } else {
            $cols=array_keys($data);$place=array_fill(0,count($cols),'?');
            $pdo->prepare("INSERT INTO blog_posts (`".implode('`,`',$cols)."`) VALUES (".implode(',',$place).")")->execute(array_values($data));
            log_admin_action('create','blogs','Created blog post');
            set_flash('success','Blog post added.');
        }
    } catch (Throwable $e) { set_flash('error', $e->getMessage()); }
    redirect(ADMIN_URL.'/modules/blogs.php');
}

if ($action==='delete' && $id>0) {
    try { crud_delete('blog_posts',$id,['featured_image']); log_admin_action('delete','blogs','Deleted blog #'.$id); set_flash('success','Deleted.'); }
    catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/blogs.php');
}
if ($action==='toggle' && $id>0) { crud_toggle('blog_posts',$id); redirect(ADMIN_URL.'/modules/blogs.php'); }

include __DIR__ . '/../includes/header.php';

if ($action==='add' || $action==='edit') {
    $b = ['id'=>0,'category_id'=>'','title'=>'','slug'=>'','short_description'=>'','content'=>'','featured_image'=>'',
          'author'=>'Admin','tags'=>'','publish_date'=>date('Y-m-d'),'meta_title'=>'','meta_description'=>'','meta_keywords'=>'',
          'canonical_url'=>'','schema_code'=>'','is_featured'=>0,'status'=>1];
    if ($action==='edit' && $id>0) { $row = crud_fetch('blog_posts',$id); if ($row) $b = $row; }
    $cats = $pdo->query("SELECT id,name FROM blog_categories WHERE status=1 ORDER BY name")->fetchAll();
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Blog':'Add Blog' ?></h4>
<a href="<?= ADMIN_URL ?>/modules/blogs.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post" enctype="multipart/form-data">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
<input type="hidden" name="existing_featured_image" value="<?= e($b['featured_image']) ?>">
<div class="row g-3">
<div class="col-lg-8">
    <div class="card"><div class="card-header">Content</div><div class="card-body">
        <div class="mb-3"><label class="form-label">Title *</label><input id="bt" name="title" class="form-control" value="<?= e($b['title']) ?>" required></div>
        <div class="mb-3"><label class="form-label">Slug</label><input name="slug" data-slug-source="bt" class="form-control" value="<?= e($b['slug']) ?>"></div>
        <div class="mb-3"><label class="form-label">Short Description</label><textarea name="short_description" class="form-control" rows="2"><?= e($b['short_description']) ?></textarea></div>
        <div class="mb-3"><label class="form-label">Content</label><textarea name="content" class="form-control rich-editor" rows="12"><?= e($b['content']) ?></textarea></div>
    </div></div>
    <div class="card"><div class="card-header">SEO</div><div class="card-body">
        <div class="mb-3"><label class="form-label">Meta Title</label><input name="meta_title" class="form-control" value="<?= e($b['meta_title']) ?>"></div>
        <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2"><?= e($b['meta_description']) ?></textarea></div>
        <div class="mb-3"><label class="form-label">Meta Keywords</label><input name="meta_keywords" class="form-control" value="<?= e($b['meta_keywords']) ?>"></div>
        <div class="mb-3"><label class="form-label">Canonical URL</label><input name="canonical_url" class="form-control" value="<?= e($b['canonical_url']) ?>"></div>
        <div class="mb-3"><label class="form-label">Schema JSON-LD</label><textarea name="schema_code" class="code-editor" rows="5"><?= e($b['schema_code']) ?></textarea></div>
    </div></div>
</div>
<div class="col-lg-4">
    <div class="card"><div class="card-header">Settings</div><div class="card-body">
        <div class="mb-3"><label class="form-label">Category</label>
            <select name="category_id" class="form-select"><option value="">— Select —</option>
                <?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id']==$b['category_id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3"><label class="form-label">Author</label><input name="author" class="form-control" value="<?= e($b['author']) ?>"></div>
        <div class="mb-3"><label class="form-label">Tags (comma-separated)</label><input name="tags" class="form-control" value="<?= e($b['tags']) ?>"></div>
        <div class="mb-3"><label class="form-label">Publish Date</label><input type="date" name="publish_date" class="form-control" value="<?= e($b['publish_date']) ?>"></div>
        <div class="form-check form-switch"><input type="checkbox" name="is_featured" class="form-check-input" id="bf" value="1" <?= $b['is_featured']?'checked':'' ?>><label for="bf" class="form-check-label">Featured</label></div>
        <div class="form-check form-switch"><input type="checkbox" name="status" class="form-check-input" id="bs" value="1" <?= $b['status']?'checked':'' ?>><label for="bs" class="form-check-label">Active</label></div>
    </div></div>
    <div class="card"><div class="card-header">Featured Image</div><div class="card-body">
        <?php if ($b['featured_image']): ?><img id="bp" src="<?= e(img_url($b['featured_image'])) ?>" class="preview-img"><?php else: ?><img id="bp" class="preview-img" style="display:none"><?php endif; ?>
        <input type="file" name="featured_image" class="form-control mt-2" data-preview="bp" accept="image/*">
    </div></div>
    <button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Save Post</button>
</div></div>
</form>
<?php } else {
    $search = trim($_GET['q'] ?? '');
    $perPage = 20; $page = max(1,(int)($_GET['page']??1)); $offset=($page-1)*$perPage;
    $where='1=1';$params=[];
    if ($search!=='') { $where.=" AND (b.title LIKE ? OR b.slug LIKE ?)"; $params[]="%$search%"; $params[]="%$search%"; }
    $stmt = $pdo->prepare("SELECT b.*, c.name AS cat_name FROM blog_posts b LEFT JOIN blog_categories c ON c.id=b.category_id WHERE $where ORDER BY b.id DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params); $rows = $stmt->fetchAll();
    $ct = $pdo->prepare("SELECT COUNT(*) FROM blog_posts b WHERE $where"); $ct->execute($params); $total = (int)$ct->fetchColumn();
?>
<div class="page-header"><h4>Blog Posts (<?= $total ?>)</h4><a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Post</a></div>
<div class="card"><div class="card-body">
<form class="mb-3 d-flex gap-2"><input class="form-control" name="q" placeholder="Search..." value="<?= e($search) ?>"><button class="btn btn-primary">Search</button></form>
<div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Author</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
    <td><img src="<?= e(img_url($r['featured_image'])) ?>" class="thumb"></td>
    <td><strong><?= e($r['title']) ?></strong></td>
    <td><?= e($r['cat_name'] ?: '-') ?></td>
    <td><?= e($r['author']) ?></td>
    <td><?= fdate($r['publish_date']) ?></td>
    <td><?= $r['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
    <td>
        <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
        <a class="btn btn-sm btn-outline-warning" href="?action=toggle&id=<?= $r['id'] ?>"><i class="bi bi-power"></i></a>
        <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
        <a class="btn btn-sm btn-outline-info" target="_blank" href="<?= SITE_URL ?>/blog/<?= e($r['slug']) ?>"><i class="bi bi-eye"></i></a>
    </td>
</tr>
<?php endforeach; ?>
<?php if (empty($rows)): ?><tr><td colspan="7" class="text-center text-muted py-4">No posts.</td></tr><?php endif; ?>
</tbody></table>
</div>
<?= paginate($total,$perPage,$page,ADMIN_URL.'/modules/blogs.php?q='.urlencode($search)) ?>
</div></div>
<?php } ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

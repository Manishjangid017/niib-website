<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='HTML Blocks'; $active='html_blocks';
$pdo=db();
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0);

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $blockKey = preg_replace('/[^a-z0-9_-]/','', strtolower(trim($_POST['block_key']??'')));
        if ($blockKey==='') throw new Exception('Block key is required.');
        
        $data=[
            'block_key'=>$blockKey,
            'title'=>trim($_POST['title']??''),
            'html_content'=>$_POST['html_content']??'',
            'css_class'=>trim($_POST['css_class']??''),
            'page_slug'=>trim($_POST['page_slug']??''),
            'sort_order'=>(int)($_POST['sort_order']??0),
            'status'=>isset($_POST['status'])?1:0
        ];

        if ($editId>0){
            $sets=[]; $vals=[]; foreach($data as $k=>$v){$sets[]="`$k`=?"; $vals[]=$v;} $vals[]=$editId;
            $pdo->prepare("UPDATE html_blocks SET ".implode(',',$sets)." WHERE id=?")->execute($vals);
        } else {
            $cols=array_keys($data); $place=array_fill(0,count($cols),'?');
            $pdo->prepare("INSERT INTO html_blocks (`".implode('`,`',$cols)."`) VALUES (".implode(',',$place).")")->execute(array_values($data));
        }
        set_flash('success','HTML Block saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/html_blocks.php');
}
if ($action==='toggle' && $id>0) { crud_toggle('html_blocks',$id); redirect(ADMIN_URL.'/modules/html_blocks.php'); }
if ($action==='delete' && $id>0) { crud_delete('html_blocks',$id); set_flash('success','Deleted.'); redirect(ADMIN_URL.'/modules/html_blocks.php'); }

include __DIR__.'/../includes/header.php';

if ($action==='edit' && $id>0) {
    $b = crud_fetch('html_blocks',$id);
    if (!$b) { set_flash('error','Block not found'); redirect(ADMIN_URL.'/modules/html_blocks.php'); }
?>
<div class="page-header"><h4>Edit HTML Block: <?= e($b['block_key']) ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/html_blocks.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post"><?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
<input type="hidden" name="existing_block_key" value="<?= e($b['block_key']) ?>">
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-header">Block Content</div><div class="card-body">
    <div class="mb-3"><label class="form-label">Title</label><input name="title" class="form-control" value="<?= e($b['title']) ?>"></div>
    <div class="mb-3"><label class="form-label">HTML Content</label><textarea name="html_content" class="code-editor" rows="12"><?= e($b['html_content']) ?></textarea>
    <p class="text-muted small mt-2">You can use HTML tags, inline CSS, and JavaScript code here.</p></div>
</div></div></div>
<div class="col-lg-4">
<div class="card"><div class="card-header">Settings</div><div class="card-body">
    <div class="mb-3"><label class="form-label">Block Key *</label><input name="block_key" class="form-control" value="<?= e($b['block_key']) ?>" required>
    <p class="text-muted small">Unique identifier (e.g., hero-banner, cta-section)</p></div>
    <div class="mb-3"><label class="form-label">Page Slug</label><input name="page_slug" class="form-control" value="<?= e($b['page_slug']) ?>" placeholder="leave empty for all pages">
    <p class="text-muted small">Leave empty to show on all pages, or enter specific page slug</p></div>
    <div class="mb-3"><label class="form-label">CSS Class</label><input name="css_class" class="form-control" value="<?= e($b['css_class']) ?>" placeholder="custom-class">
    <p class="text-muted small">Optional custom CSS class for styling</p></div>
    <div class="mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$b['sort_order'] ?>"></div>
    <div class="form-check form-switch"><input type="checkbox" name="status" id="hbs" value="1" class="form-check-input" <?= $b['status']?'checked':'' ?>><label for="hbs" class="form-check-label">Enable Block</label></div>
</div></div>
<button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Save Block</button>
</div></div></form>
<?php } else {
    $rows = $pdo->query("SELECT * FROM html_blocks ORDER BY sort_order, id")->fetchAll();
?>
<div class="page-header"><h4>HTML Blocks</h4></div>
<div class="card"><div class="card-body">
<p class="text-muted small mb-3">Create custom HTML sections/blocks to display on your pages. Leave page slug empty to show on all pages.</p>
<div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Block Key</th><th>Title</th><th>Page</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><code><?= e($r['block_key']) ?></code></td><td><?= e($r['title']) ?></td><td><?= e($r['page_slug']?:'All Pages') ?></td>
<td><?= (int)$r['sort_order'] ?></td>
<td><?= $r['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
<td>
    <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
    <a class="btn btn-sm btn-outline-warning" href="?action=toggle&id=<?= $r['id'] ?>"><i class="bi bi-power"></i></a>
    <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
</td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php if(empty($rows)): ?><p class="text-center text-muted py-3">No HTML blocks yet. Create your first block.</p><?php endif; ?>
</div></div>
<?php } ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

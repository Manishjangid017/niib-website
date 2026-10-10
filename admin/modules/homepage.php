<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Homepage Sections'; $active='homepage';
$pdo=db();
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0);

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $data=['section_key'=>preg_replace('/[^a-z0-9_]/','', strtolower(trim($_POST['section_key']??''))),
            'title'=>trim($_POST['title']??''),'subtitle'=>trim($_POST['subtitle']??''),
            'content'=>$_POST['content']??'','button_text'=>trim($_POST['button_text']??''),
            'button_url'=>trim($_POST['button_url']??''),'sort_order'=>(int)($_POST['sort_order']??0),
            'status'=>isset($_POST['status'])?1:0];
        if (!empty($_FILES['image']['name'])) { $u=upload_file('image','media'); if($u['ok']) $data['image']=$u['path']; }
        elseif (!empty($_POST['existing_image'])) $data['image']=$_POST['existing_image'];
        if ($data['section_key']==='') throw new Exception('Section key required');

        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE homepage_sections SET ".implode(',',$sets)." WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO homepage_sections (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/homepage.php');
}
if ($action==='toggle' && $id>0) { crud_toggle('homepage_sections',$id); redirect(ADMIN_URL.'/modules/homepage.php'); }

include __DIR__.'/../includes/header.php';

if ($action==='edit' && $id>0) {
    $s = crud_fetch('homepage_sections',$id);
    if (!$s) { set_flash('error','Section not found'); redirect(ADMIN_URL.'/modules/homepage.php'); }
?>
<div class="page-header"><h4>Edit Homepage Section: <?= e($s['section_key']) ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/homepage.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
<input type="hidden" name="section_key" value="<?= e($s['section_key']) ?>">
<input type="hidden" name="existing_image" value="<?= e($s['image']) ?>">
<div class="row g-3"><div class="col-lg-8">
<div class="card"><div class="card-body">
    <div class="mb-3"><label class="form-label">Subtitle</label><input name="subtitle" class="form-control" value="<?= e($s['subtitle']) ?>"></div>
    <div class="mb-3"><label class="form-label">Title</label><input name="title" class="form-control" value="<?= e($s['title']) ?>"></div>
    <div class="mb-3"><label class="form-label">Content (HTML)</label><textarea name="content" class="form-control" rows="8"><?= e($s['content']) ?></textarea></div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Button Text</label><input name="button_text" class="form-control" value="<?= e($s['button_text']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Button URL</label><input name="button_url" class="form-control" value="<?= e($s['button_url']) ?>"></div>
    </div>
</div></div></div>
<div class="col-lg-4">
<div class="card"><div class="card-header">Settings</div><div class="card-body">
    <div class="mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$s['sort_order'] ?>"></div>
    <div class="form-check form-switch"><input type="checkbox" name="status" id="hps" value="1" class="form-check-input" <?= $s['status']?'checked':'' ?>><label for="hps" class="form-check-label">Enable Section</label></div>
</div></div>
<div class="card"><div class="card-header">Image</div><div class="card-body">
    <?php if($s['image']):?><img id="hpi" src="<?= e(img_url($s['image'])) ?>" class="preview-img"><?php else: ?><img id="hpi" class="preview-img" style="display:none"><?php endif; ?>
    <input type="file" name="image" data-preview="hpi" class="form-control mt-2" accept="image/*">
</div></div>
<button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Save Section</button>
</div></div></form>
<?php } else {
    $rows = $pdo->query("SELECT * FROM homepage_sections ORDER BY sort_order, id")->fetchAll();
?>
<div class="page-header"><h4>Homepage Sections</h4></div>
<div class="card"><div class="card-body">
<p class="text-muted small">These sections appear on the homepage in order. Toggle them on/off or edit their content.</p>
<div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Section Key</th><th>Title</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><code><?= e($r['section_key']) ?></code></td><td><?= e($r['title']) ?></td>
<td><?= (int)$r['sort_order'] ?></td>
<td><?= $r['status']?'<span class="badge bg-success">On</span>':'<span class="badge bg-secondary">Off</span>' ?></td>
<td>
    <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i> Edit</a>
    <a class="btn btn-sm btn-outline-warning" href="?action=toggle&id=<?= $r['id'] ?>"><i class="bi bi-power"></i></a>
</td></tr>
<?php endforeach; ?>
</tbody></table></div>
</div></div>
<?php } ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

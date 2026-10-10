<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Redirect Manager'; $active='redirects';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $data=['old_url'=>trim($_POST['old_url']??''),'new_url'=>trim($_POST['new_url']??''),
            'type'=>in_array($_POST['type']??'301',['301','302'])?$_POST['type']:'301',
            'status'=>isset($_POST['status'])?1:0];
        if ($data['old_url']==='' || $data['new_url']==='') throw new Exception('Old/new URL required');
        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE redirects SET ".implode(',',$sets)." WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO redirects (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/redirects.php');
}
if ($action==='delete' && $id>0) { crud_delete('redirects',$id); redirect(ADMIN_URL.'/modules/redirects.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('redirects',$id); redirect(ADMIN_URL.'/modules/redirects.php'); }

include __DIR__.'/../includes/header.php';
if ($action==='add' || $action==='edit') {
    $r=['id'=>0,'old_url'=>'','new_url'=>'','type'=>'301','status'=>1];
    if ($action==='edit' && $id>0){$row=crud_fetch('redirects',$id); if($row)$r=$row;}
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Redirect':'Add Redirect' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/redirects.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<div class="card"><div class="card-body"><div class="row g-3">
<div class="col-md-5"><label class="form-label">Old URL *</label><input name="old_url" class="form-control" required value="<?= e($r['old_url']) ?>" placeholder="/old-page or full URL"></div>
<div class="col-md-5"><label class="form-label">New URL *</label><input name="new_url" class="form-control" required value="<?= e($r['new_url']) ?>"></div>
<div class="col-md-2"><label class="form-label">Type</label>
    <select name="type" class="form-select"><option value="301" <?= $r['type']==='301'?'selected':'' ?>>301 (Permanent)</option><option value="302" <?= $r['type']==='302'?'selected':'' ?>>302 (Temporary)</option></select>
</div>
<div class="col-md-3"><div class="form-check form-switch"><input type="checkbox" name="status" id="rs" class="form-check-input" value="1" <?= $r['status']?'checked':'' ?>><label for="rs" class="form-check-label">Active</label></div></div>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save</button>
</form>
<?php } else { $rows=$pdo->query("SELECT * FROM redirects ORDER BY id DESC")->fetchAll(); ?>
<div class="page-header"><h4>Redirects</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Redirect</a></div>
<div class="card"><div class="card-body">
<p class="text-muted small">Redirects are processed by <code>404.php</code>. Add entries for old URLs that should redirect to new locations.</p>
<div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Old URL</th><th>New URL</th><th>Type</th><th>Hits</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><code><?= e($r['old_url']) ?></code></td><td><code><?= e($r['new_url']) ?></code></td>
<td><span class="badge bg-info"><?= e($r['type']) ?></span></td><td><?= (int)$r['hits'] ?></td>
<td><?= $r['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
<td>
    <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
    <a class="btn btn-sm btn-outline-warning" href="?action=toggle&id=<?= $r['id'] ?>"><i class="bi bi-power"></i></a>
    <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
</td></tr>
<?php endforeach; if(empty($rows)): ?><tr><td colspan="6" class="text-center text-muted py-4">No redirects yet.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php } ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

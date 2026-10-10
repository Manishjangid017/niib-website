<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Social Links'; $active='social';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $data=['platform'=>trim($_POST['platform']??''),'url'=>trim($_POST['url']??''),
            'icon'=>trim($_POST['icon']??''),'sort_order'=>(int)($_POST['sort_order']??0),
            'status'=>isset($_POST['status'])?1:0];
        if ($data['platform']==='' || $data['url']==='') throw new Exception('Platform and URL required');
        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE social_links SET ".implode(',',$sets)." WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO social_links (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/social.php');
}
if ($action==='delete' && $id>0) { crud_delete('social_links',$id); redirect(ADMIN_URL.'/modules/social.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('social_links',$id); redirect(ADMIN_URL.'/modules/social.php'); }

include __DIR__.'/../includes/header.php';
if ($action==='add' || $action==='edit') {
    $s=['id'=>0,'platform'=>'','url'=>'','icon'=>'','sort_order'=>0,'status'=>1];
    if ($action==='edit' && $id>0){$r=crud_fetch('social_links',$id); if($r)$s=$r;}
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Link':'Add Link' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/social.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
<div class="card"><div class="card-body"><div class="row g-3">
<div class="col-md-4"><label class="form-label">Platform *</label><input name="platform" class="form-control" required value="<?= e($s['platform']) ?>"></div>
<div class="col-md-5"><label class="form-label">URL *</label><input type="url" name="url" class="form-control" required value="<?= e($s['url']) ?>"></div>
<div class="col-md-3"><label class="form-label">Icon Class</label><input name="icon" class="form-control" value="<?= e($s['icon']) ?>" placeholder="bi-facebook"></div>
<div class="col-md-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$s['sort_order'] ?>"></div>
<div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input type="checkbox" name="status" id="slst" value="1" class="form-check-input" <?= $s['status']?'checked':'' ?>><label for="slst" class="form-check-label">Active</label></div></div>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save</button>
</form>
<?php } else { $rows=$pdo->query("SELECT * FROM social_links ORDER BY sort_order")->fetchAll(); ?>
<div class="page-header"><h4>Social Links</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Link</a></div>
<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Icon</th><th>Platform</th><th>URL</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><i class="bi <?= e($r['icon']) ?>"></i></td><td><?= e($r['platform']) ?></td>
<td><a href="<?= e($r['url']) ?>" target="_blank" rel="noopener"><?= e($r['url']) ?></a></td>
<td><?= $r['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
<td>
    <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
    <a class="btn btn-sm btn-outline-warning" href="?action=toggle&id=<?= $r['id'] ?>"><i class="bi bi-power"></i></a>
    <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
</td></tr>
<?php endforeach; ?>
</tbody></table></div></div></div>
<?php } ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

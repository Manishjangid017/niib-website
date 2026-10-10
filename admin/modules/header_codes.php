<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Header Codes'; $active='header_codes';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $data=['name'=>trim($_POST['name']??''),'code'=>$_POST['code']??'',
            'position'=>in_array($_POST['position']??'head',['head','body_start'])?$_POST['position']:'head',
            'status'=>isset($_POST['status'])?1:0];
        if ($data['name']==='') throw new Exception('Name required');
        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE header_codes SET ".implode(',',$sets).", updated_at=NOW() WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO header_codes (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        log_admin_action('update','header_codes','Header code saved');
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/header_codes.php');
}
if ($action==='delete' && $id>0) { crud_delete('header_codes',$id); redirect(ADMIN_URL.'/modules/header_codes.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('header_codes',$id); redirect(ADMIN_URL.'/modules/header_codes.php'); }

include __DIR__.'/../includes/header.php';
if ($action==='add' || $action==='edit') {
    $c=['id'=>0,'name'=>'','code'=>'','position'=>'head','status'=>1];
    if ($action==='edit' && $id>0){$r=crud_fetch('header_codes',$id); if($r)$c=$r;}
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Header Code':'Add Header Code' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/header_codes.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
<div class="card"><div class="card-body">
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" required value="<?= e($c['name']) ?>" placeholder="e.g. Google Analytics"></div>
    <div class="col-md-3"><label class="form-label">Position</label>
        <select name="position" class="form-select">
            <option value="head" <?= $c['position']==='head'?'selected':'' ?>>Inside &lt;head&gt;</option>
            <option value="body_start" <?= $c['position']==='body_start'?'selected':'' ?>>After &lt;body&gt;</option>
        </select>
    </div>
    <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input type="checkbox" name="status" id="hcst" value="1" class="form-check-input" <?= $c['status']?'checked':'' ?>><label class="form-check-label" for="hcst">Active</label></div></div>
    <div class="col-12"><label class="form-label">Code (HTML / JS / Meta)</label>
        <textarea name="code" class="code-editor" rows="14"><?= e($c['code']) ?></textarea>
        <small class="text-muted">Paste raw code (Google Analytics, Tag Manager, Pixel, verification meta, etc).</small>
    </div>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save Code</button>
</form>
<?php } else { $rows=$pdo->query("SELECT * FROM header_codes ORDER BY id DESC")->fetchAll(); ?>
<div class="page-header"><h4>Header Codes</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Code</a></div>
<div class="card"><div class="card-body">
<p class="text-muted small">Header codes load automatically on every frontend page inside <code>&lt;head&gt;</code> or right after <code>&lt;body&gt;</code>.</p>
<div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Name</th><th>Position</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><strong><?= e($r['name']) ?></strong></td><td><code><?= e($r['position']) ?></code></td>
<td><?= $r['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
<td><?= fdate($r['updated_at'],'d M Y H:i') ?></td>
<td>
    <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
    <a class="btn btn-sm btn-outline-warning" href="?action=toggle&id=<?= $r['id'] ?>"><i class="bi bi-power"></i></a>
    <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
</td></tr>
<?php endforeach; if(empty($rows)): ?><tr><td colspan="5" class="text-center text-muted py-4">No codes added.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php } ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

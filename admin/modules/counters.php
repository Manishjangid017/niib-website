<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Counters'; $active='counters';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $data=['label'=>trim($_POST['label']??''),'value'=>trim($_POST['value']??''),
            'suffix'=>trim($_POST['suffix']??'+'),'icon'=>trim($_POST['icon']??''),
            'sort_order'=>(int)($_POST['sort_order']??0),'status'=>isset($_POST['status'])?1:0];
        if ($data['label']==='' || $data['value']==='') throw new Exception('Label and value required');
        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE counters SET ".implode(',',$sets)." WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO counters (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/counters.php');
}
if ($action==='delete' && $id>0) { crud_delete('counters',$id); redirect(ADMIN_URL.'/modules/counters.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('counters',$id); redirect(ADMIN_URL.'/modules/counters.php'); }

include __DIR__.'/../includes/header.php';
if ($action==='add' || $action==='edit') {
    $c=['id'=>0,'label'=>'','value'=>'','suffix'=>'+','icon'=>'','sort_order'=>0,'status'=>1];
    if ($action==='edit' && $id>0){$r=crud_fetch('counters',$id); if($r)$c=$r;}
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Counter':'Add Counter' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/counters.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
<div class="card"><div class="card-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Label *</label><input name="label" class="form-control" required value="<?= e($c['label']) ?>"></div>
<div class="col-md-3"><label class="form-label">Value *</label><input name="value" class="form-control" required value="<?= e($c['value']) ?>"></div>
<div class="col-md-3"><label class="form-label">Suffix</label><input name="suffix" class="form-control" value="<?= e($c['suffix']) ?>" placeholder="+"></div>
<div class="col-md-6"><label class="form-label">Icon (Bootstrap Icons class)</label><input name="icon" class="form-control" value="<?= e($c['icon']) ?>" placeholder="bi-people-fill"></div>
<div class="col-md-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$c['sort_order'] ?>"></div>
<div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input type="checkbox" name="status" id="cstn" value="1" class="form-check-input" <?= $c['status']?'checked':'' ?>><label for="cstn" class="form-check-label">Active</label></div></div>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save</button>
</form>
<?php } else { $rows=$pdo->query("SELECT * FROM counters ORDER BY sort_order, id")->fetchAll(); ?>
<div class="page-header"><h4>Counters</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Counter</a></div>
<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Icon</th><th>Label</th><th>Value</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><i class="bi <?= e($r['icon']) ?>"></i></td><td><?= e($r['label']) ?></td>
<td><strong><?= e($r['value']).e($r['suffix']) ?></strong></td><td><?= (int)$r['sort_order'] ?></td>
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

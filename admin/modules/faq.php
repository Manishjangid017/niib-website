<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='FAQ'; $active='faq';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $data=['question'=>trim($_POST['question']??''),'answer'=>trim($_POST['answer']??''),
            'category'=>trim($_POST['category']??'general'),'sort_order'=>(int)($_POST['sort_order']??0),
            'status'=>isset($_POST['status'])?1:0];
        if ($data['question']==='') throw new Exception('Question required');
        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE faq SET ".implode(',',$sets)." WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO faq (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/faq.php');
}
if ($action==='delete' && $id>0) { crud_delete('faq',$id); redirect(ADMIN_URL.'/modules/faq.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('faq',$id); redirect(ADMIN_URL.'/modules/faq.php'); }

include __DIR__.'/../includes/header.php';
if ($action==='add' || $action==='edit') {
    $f=['id'=>0,'question'=>'','answer'=>'','category'=>'general','sort_order'=>0,'status'=>1];
    if ($action==='edit' && $id>0){$r=crud_fetch('faq',$id); if($r)$f=$r;}
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit FAQ':'Add FAQ' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/faq.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
<div class="card"><div class="card-body"><div class="row g-3">
<div class="col-md-9"><label class="form-label">Question *</label><input name="question" class="form-control" required value="<?= e($f['question']) ?>"></div>
<div class="col-md-3"><label class="form-label">Category</label><input name="category" class="form-control" value="<?= e($f['category']) ?>"></div>
<div class="col-12"><label class="form-label">Answer *</label><textarea name="answer" class="form-control" rows="6" required><?= e($f['answer']) ?></textarea></div>
<div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$f['sort_order'] ?>"></div>
<div class="col-md-4 d-flex align-items-end"><div class="form-check form-switch"><input type="checkbox" name="status" id="fs" value="1" class="form-check-input" <?= $f['status']?'checked':'' ?>><label for="fs" class="form-check-label">Active</label></div></div>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save</button>
</form>
<?php } else { $rows=$pdo->query("SELECT * FROM faq ORDER BY category, sort_order, id")->fetchAll(); ?>
<div class="page-header"><h4>FAQ</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add FAQ</a></div>
<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Question</th><th>Category</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><?= e($r['question']) ?></td><td><span class="badge bg-secondary"><?= e($r['category']) ?></span></td>
<td><?= (int)$r['sort_order'] ?></td>
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

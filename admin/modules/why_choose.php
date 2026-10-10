<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Why Choose Us'; $active='why_choose';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $data=['title'=>trim($_POST['title']??''),'description'=>trim($_POST['description']??''),
            'icon'=>trim($_POST['icon']??''),'sort_order'=>(int)($_POST['sort_order']??0),
            'status'=>isset($_POST['status'])?1:0];
        if (!empty($_FILES['image']['name'])) { $u=upload_file('image','media'); if($u['ok']) $data['image']=$u['path']; }
        elseif (!empty($_POST['existing_image'])) $data['image']=$_POST['existing_image'];
        if ($data['title']==='') throw new Exception('Title required');
        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE why_choose_us SET ".implode(',',$sets)." WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO why_choose_us (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/why_choose.php');
}
if ($action==='delete' && $id>0) { crud_delete('why_choose_us',$id,['image']); redirect(ADMIN_URL.'/modules/why_choose.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('why_choose_us',$id); redirect(ADMIN_URL.'/modules/why_choose.php'); }

include __DIR__.'/../includes/header.php';
if ($action==='add' || $action==='edit') {
    $w=['id'=>0,'title'=>'','description'=>'','icon'=>'','image'=>'','sort_order'=>0,'status'=>1];
    if ($action==='edit' && $id>0){$r=crud_fetch('why_choose_us',$id); if($r)$w=$r;}
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit':'Add' ?> Item</h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/why_choose.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$w['id'] ?>"><input type="hidden" name="existing_image" value="<?= e($w['image']) ?>">
<div class="card"><div class="card-body"><div class="row g-3">
<div class="col-md-8"><label class="form-label">Title *</label><input name="title" class="form-control" required value="<?= e($w['title']) ?>"></div>
<div class="col-md-4"><label class="form-label">Icon (BI class)</label><input name="icon" class="form-control" value="<?= e($w['icon']) ?>"></div>
<div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= e($w['description']) ?></textarea></div>
<div class="col-md-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$w['sort_order'] ?>"></div>
<div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input type="checkbox" name="status" id="wcs" value="1" class="form-check-input" <?= $w['status']?'checked':'' ?>><label class="form-check-label" for="wcs">Active</label></div></div>
<div class="col-md-6"><label class="form-label">Image (optional)</label>
    <?php if($w['image']):?><img id="wcp" src="<?= e(img_url($w['image'])) ?>" class="preview-img"><?php else: ?><img id="wcp" class="preview-img" style="display:none"><?php endif; ?>
    <input type="file" name="image" data-preview="wcp" class="form-control mt-2" accept="image/*">
</div>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save</button>
</form>
<?php } else { $rows=$pdo->query("SELECT * FROM why_choose_us ORDER BY sort_order, id")->fetchAll(); ?>
<div class="page-header"><h4>Why Choose Us</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Item</a></div>
<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Icon</th><th>Title</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><i class="bi <?= e($r['icon']) ?>"></i></td><td><?= e($r['title']) ?></td><td><?= (int)$r['sort_order'] ?></td>
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

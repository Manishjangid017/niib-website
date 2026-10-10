<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Testimonials'; $active='testimonials';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $data=['name'=>trim($_POST['name']??''),'designation'=>trim($_POST['designation']??''),
            'company'=>trim($_POST['company']??''),'review'=>trim($_POST['review']??''),
            'rating'=>max(1,min(5,(int)($_POST['rating']??5))),'sort_order'=>(int)($_POST['sort_order']??0),
            'status'=>isset($_POST['status'])?1:0];
        if ($data['name']==='') throw new Exception('Name required');
        if (!empty($_FILES['image']['name'])) { $u=upload_file('image','testimonials'); if($u['ok']) $data['image']=$u['path']; }
        elseif (!empty($_POST['existing_image'])) $data['image']=$_POST['existing_image'];
        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE testimonials SET ".implode(',',$sets)." WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO testimonials (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/testimonials.php');
}
if ($action==='delete' && $id>0) { crud_delete('testimonials',$id,['image']); redirect(ADMIN_URL.'/modules/testimonials.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('testimonials',$id); redirect(ADMIN_URL.'/modules/testimonials.php'); }

include __DIR__.'/../includes/header.php';
if ($action==='add' || $action==='edit') {
    $t=['id'=>0,'name'=>'','designation'=>'','company'=>'','review'=>'','rating'=>5,'image'=>'','sort_order'=>0,'status'=>1];
    if ($action==='edit' && $id>0){$r=crud_fetch('testimonials',$id); if($r)$t=$r;}
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Testimonial':'Add Testimonial' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/testimonials.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
<input type="hidden" name="existing_image" value="<?= e($t['image']) ?>">
<div class="row g-3"><div class="col-lg-8"><div class="card"><div class="card-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" required value="<?= e($t['name']) ?>"></div>
<div class="col-md-6"><label class="form-label">Designation</label><input name="designation" class="form-control" value="<?= e($t['designation']) ?>"></div>
<div class="col-md-6"><label class="form-label">Company</label><input name="company" class="form-control" value="<?= e($t['company']) ?>"></div>
<div class="col-md-3"><label class="form-label">Rating</label><select name="rating" class="form-select"><?php for($i=1;$i<=5;$i++): ?><option value="<?= $i ?>" <?= $t['rating']==$i?'selected':'' ?>><?= $i ?> Stars</option><?php endfor; ?></select></div>
<div class="col-md-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$t['sort_order'] ?>"></div>
<div class="col-12"><label class="form-label">Review *</label><textarea name="review" class="form-control" rows="4" required><?= e($t['review']) ?></textarea></div>
</div></div></div></div>
<div class="col-lg-4"><div class="card"><div class="card-header">Image & Status</div><div class="card-body">
    <?php if ($t['image']): ?><img id="tp" src="<?= e(img_url($t['image'])) ?>" class="preview-img"><?php else: ?><img id="tp" class="preview-img" style="display:none"><?php endif; ?>
    <input type="file" name="image" data-preview="tp" class="form-control mt-2" accept="image/*">
    <div class="form-check form-switch mt-3"><input type="checkbox" name="status" id="ts" value="1" class="form-check-input" <?= $t['status']?'checked':'' ?>><label class="form-check-label" for="ts">Active</label></div>
</div></div>
<button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Save</button>
</div></div></form>
<?php } else { $rows=$pdo->query("SELECT * FROM testimonials ORDER BY sort_order, id DESC")->fetchAll(); ?>
<div class="page-header"><h4>Testimonials</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add</a></div>
<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th></th><th>Name</th><th>Designation</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><img src="<?= e(img_url($r['image'])) ?>" class="thumb"></td><td><?= e($r['name']) ?></td>
<td><?= e($r['designation']) ?><br><small class="text-muted"><?= e($r['company']) ?></small></td>
<td><?= str_repeat('★',(int)$r['rating']) ?></td>
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

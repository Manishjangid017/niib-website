<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Course Categories'; $active='course_categories';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $name=trim($_POST['name']??''); if($name==='') throw new Exception('Name required');
        $slug=trim($_POST['slug']??'')?:$name; $slug=unique_slug('course_categories',$slug,$editId);
        $data=['name'=>$name,'slug'=>$slug,'description'=>trim($_POST['description']??''),
            'icon'=>trim($_POST['icon']??''),'sort_order'=>(int)($_POST['sort_order']??0),
            'status'=>isset($_POST['status'])?1:0];
        if (!empty($_FILES['image']['name'])) { $u=upload_file('image','courses'); if($u['ok']) $data['image']=$u['path']; }
        elseif (!empty($_POST['existing_image'])) $data['image']=$_POST['existing_image'];
        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE course_categories SET ".implode(',',$sets)." WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO course_categories (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/course_categories.php');
}
if ($action==='delete' && $id>0) { crud_delete('course_categories',$id,['image']); redirect(ADMIN_URL.'/modules/course_categories.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('course_categories',$id); redirect(ADMIN_URL.'/modules/course_categories.php'); }

include __DIR__.'/../includes/header.php';

if ($action==='add' || $action==='edit') {
    $c=['id'=>0,'name'=>'','slug'=>'','description'=>'','icon'=>'','image'=>'','sort_order'=>0,'status'=>1];
    if ($action==='edit' && $id>0){$r=crud_fetch('course_categories',$id); if($r)$c=$r;}
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Category':'Add Category' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/course_categories.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
<input type="hidden" name="existing_image" value="<?= e($c['image']) ?>">
<div class="card"><div class="card-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Name *</label><input id="cn" name="name" class="form-control" required value="<?= e($c['name']) ?>"></div>
<div class="col-md-6"><label class="form-label">Slug</label><input name="slug" data-slug-source="cn" class="form-control" value="<?= e($c['slug']) ?>"></div>
<div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control"><?= e($c['description']) ?></textarea></div>
<div class="col-md-4"><label class="form-label">Icon (Bootstrap Icons)</label><input name="icon" class="form-control" value="<?= e($c['icon']) ?>" placeholder="bi-laptop"></div>
<div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$c['sort_order'] ?>"></div>
<div class="col-md-4 d-flex align-items-end"><div class="form-check form-switch"><input type="checkbox" name="status" id="cs" value="1" class="form-check-input" <?= $c['status']?'checked':'' ?>><label class="form-check-label" for="cs">Active</label></div></div>
<div class="col-md-6"><label class="form-label">Image (optional)</label>
    <?php if ($c['image']): ?><img id="cip" src="<?= e(img_url($c['image'])) ?>" class="preview-img"><?php else: ?><img id="cip" class="preview-img" style="display:none"><?php endif; ?>
    <input type="file" name="image" data-preview="cip" class="form-control mt-2" accept="image/*">
</div>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save</button>
</form>
<?php } else {
    $rows=$pdo->query("SELECT * FROM course_categories ORDER BY sort_order, name")->fetchAll();
?>
<div class="page-header"><h4>Course Categories</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Category</a></div>
<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Icon</th><th>Name</th><th>Slug</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><i class="bi <?= e($r['icon']) ?>"></i></td><td><?= e($r['name']) ?></td><td><code><?= e($r['slug']) ?></code></td>
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

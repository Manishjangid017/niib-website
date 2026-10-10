<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Blog Categories'; $active='blog_categories';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $name=trim($_POST['name']??''); if($name==='') throw new Exception('Name required');
        $slug=trim($_POST['slug']??'')?:$name; $slug=unique_slug('blog_categories',$slug,$editId);
        $data=['name'=>$name,'slug'=>$slug,'description'=>trim($_POST['description']??''),'status'=>isset($_POST['status'])?1:0];
        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE blog_categories SET ".implode(',',$sets)." WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO blog_categories (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/blog_categories.php');
}
if ($action==='delete' && $id>0) { crud_delete('blog_categories',$id); redirect(ADMIN_URL.'/modules/blog_categories.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('blog_categories',$id); redirect(ADMIN_URL.'/modules/blog_categories.php'); }

include __DIR__.'/../includes/header.php';

if ($action==='add' || $action==='edit') {
    $c=['id'=>0,'name'=>'','slug'=>'','description'=>'','status'=>1];
    if ($action==='edit' && $id>0){$r=crud_fetch('blog_categories',$id); if($r)$c=$r;}
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Category':'Add Category' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/blog_categories.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
<div class="card"><div class="card-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Name *</label><input id="bn" name="name" class="form-control" required value="<?= e($c['name']) ?>"></div>
<div class="col-md-6"><label class="form-label">Slug</label><input name="slug" data-slug-source="bn" class="form-control" value="<?= e($c['slug']) ?>"></div>
<div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control"><?= e($c['description']) ?></textarea></div>
<div class="col-md-4"><div class="form-check form-switch"><input type="checkbox" name="status" id="bs2" class="form-check-input" value="1" <?= $c['status']?'checked':'' ?>><label class="form-check-label" for="bs2">Active</label></div></div>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save</button>
</form>
<?php } else { $rows=$pdo->query("SELECT * FROM blog_categories ORDER BY name")->fetchAll(); ?>
<div class="page-header"><h4>Blog Categories</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Category</a></div>
<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Name</th><th>Slug</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><?= e($r['name']) ?></td><td><code><?= e($r['slug']) ?></code></td>
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

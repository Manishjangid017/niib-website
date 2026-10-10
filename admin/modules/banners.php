<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Banners'; $active='banners';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $data=['name'=>trim($_POST['name']??''),'location'=>trim($_POST['location']??''),
            'link_url'=>trim($_POST['link_url']??''),'alt_text'=>trim($_POST['alt_text']??''),
            'status'=>isset($_POST['status'])?1:0];
        if (!empty($_FILES['image']['name'])) { $u=upload_file('image','media'); if(!$u['ok']) throw new Exception($u['error']); $data['image']=$u['path']; }
        elseif (!empty($_POST['existing_image'])) $data['image']=$_POST['existing_image'];
        if ($data['name']==='') throw new Exception('Name required');
        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE banners SET ".implode(',',$sets)." WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO banners (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/banners.php');
}
if ($action==='delete' && $id>0) { crud_delete('banners',$id,['image']); redirect(ADMIN_URL.'/modules/banners.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('banners',$id); redirect(ADMIN_URL.'/modules/banners.php'); }

include __DIR__.'/../includes/header.php';
if ($action==='add' || $action==='edit') {
    $b=['id'=>0,'name'=>'','location'=>'','image'=>'','link_url'=>'','alt_text'=>'','status'=>1];
    if ($action==='edit' && $id>0){$r=crud_fetch('banners',$id); if($r)$b=$r;}
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Banner':'Add Banner' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/banners.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><input type="hidden" name="existing_image" value="<?= e($b['image']) ?>">
<div class="card"><div class="card-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" required value="<?= e($b['name']) ?>"></div>
<div class="col-md-6"><label class="form-label">Location (e.g. about_top)</label><input name="location" class="form-control" value="<?= e($b['location']) ?>"></div>
<div class="col-md-6"><label class="form-label">Link URL</label><input name="link_url" class="form-control" value="<?= e($b['link_url']) ?>"></div>
<div class="col-md-6"><label class="form-label">Alt Text</label><input name="alt_text" class="form-control" value="<?= e($b['alt_text']) ?>"></div>
<div class="col-12"><label class="form-label">Image *</label>
    <?php if($b['image']):?><img id="bnp" src="<?= e(img_url($b['image'])) ?>" class="preview-img"><?php else: ?><img id="bnp" class="preview-img" style="display:none"><?php endif; ?>
    <input type="file" name="image" data-preview="bnp" class="form-control mt-2" accept="image/*" <?= $b['id']?'':'required' ?>>
</div>
<div class="col-md-3"><div class="form-check form-switch mt-2"><input type="checkbox" name="status" id="bnst" value="1" class="form-check-input" <?= $b['status']?'checked':'' ?>><label for="bnst" class="form-check-label">Active</label></div></div>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save</button>
</form>
<?php } else { $rows=$pdo->query("SELECT * FROM banners ORDER BY id DESC")->fetchAll(); ?>
<div class="page-header"><h4>Banners</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Banner</a></div>
<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Image</th><th>Name</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><img src="<?= e(img_url($r['image'])) ?>" class="thumb" style="width:90px;height:50px"></td>
<td><?= e($r['name']) ?></td><td><code><?= e($r['location']) ?></code></td>
<td><?= $r['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
<td>
    <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
    <a class="btn btn-sm btn-outline-warning" href="?action=toggle&id=<?= $r['id'] ?>"><i class="bi bi-power"></i></a>
    <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
</td></tr>
<?php endforeach; if(empty($rows)): ?><tr><td colspan="5" class="text-center text-muted py-4">No banners.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php } ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

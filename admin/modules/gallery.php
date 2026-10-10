<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Gallery'; $active='gallery';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $data=['title'=>trim($_POST['title']??''),'category'=>trim($_POST['category']??''),
            'alt_text'=>trim($_POST['alt_text']??''),'sort_order'=>(int)($_POST['sort_order']??0),
            'status'=>isset($_POST['status'])?1:0];
        if (!empty($_FILES['image']['name'])) { $u=upload_file('image','gallery'); if(!$u['ok']) throw new Exception($u['error']); $data['image']=$u['path']; }
        elseif (!empty($_POST['existing_image'])) $data['image']=$_POST['existing_image'];
        elseif (empty($data['image'])) throw new Exception('Image required');

        if ($editId>0){$sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE gallery SET ".implode(',',$sets)." WHERE id=?")->execute($vals);}
        else {$cols=array_keys($data);$pdo->prepare("INSERT INTO gallery (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));}
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/gallery.php');
}
if ($action==='delete' && $id>0) { crud_delete('gallery',$id,['image']); redirect(ADMIN_URL.'/modules/gallery.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('gallery',$id); redirect(ADMIN_URL.'/modules/gallery.php'); }

include __DIR__.'/../includes/header.php';
if ($action==='add' || $action==='edit') {
    $g=['id'=>0,'title'=>'','category'=>'','image'=>'','alt_text'=>'','sort_order'=>0,'status'=>1];
    if ($action==='edit' && $id>0){$r=crud_fetch('gallery',$id); if($r)$g=$r;}
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Image':'Add Image' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/gallery.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
<input type="hidden" name="existing_image" value="<?= e($g['image']) ?>">
<div class="card"><div class="card-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Title</label><input name="title" class="form-control" value="<?= e($g['title']) ?>"></div>
<div class="col-md-6"><label class="form-label">Category</label><input name="category" class="form-control" value="<?= e($g['category']) ?>"></div>
<div class="col-md-6"><label class="form-label">Alt Text (SEO)</label><input name="alt_text" class="form-control" value="<?= e($g['alt_text']) ?>"></div>
<div class="col-md-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$g['sort_order'] ?>"></div>
<div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input type="checkbox" name="status" id="gst" value="1" class="form-check-input" <?= $g['status']?'checked':'' ?>><label for="gst" class="form-check-label">Active</label></div></div>
<div class="col-12"><label class="form-label">Image *</label>
    <?php if ($g['image']): ?><img id="gp" src="<?= e(img_url($g['image'])) ?>" class="preview-img"><?php else: ?><img id="gp" class="preview-img" style="display:none"><?php endif; ?>
    <input type="file" name="image" data-preview="gp" class="form-control mt-2" accept="image/*" <?= $g['id']?'':'required' ?>>
</div>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save</button>
</form>
<?php } else { $rows=$pdo->query("SELECT * FROM gallery ORDER BY sort_order, id DESC")->fetchAll(); ?>
<div class="page-header"><h4>Gallery</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Image</a></div>
<div class="card"><div class="card-body">
<div class="row g-3">
<?php foreach($rows as $r): ?>
<div class="col-md-3 col-sm-6">
    <div class="card h-100"><img src="<?= e(img_url($r['image'])) ?>" style="height:160px;object-fit:cover">
    <div class="card-body p-2">
        <div class="small"><?= e($r['title'] ?: '(untitled)') ?></div>
        <div class="small text-muted"><?= e($r['category']) ?></div>
        <div class="mt-2">
            <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
            <a class="btn btn-sm btn-outline-warning" href="?action=toggle&id=<?= $r['id'] ?>"><i class="bi bi-power"></i></a>
            <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
        </div>
    </div></div>
</div>
<?php endforeach; if(empty($rows)): ?><div class="col-12 text-center text-muted py-4">No images yet.</div><?php endif; ?>
</div></div></div>
<?php } ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

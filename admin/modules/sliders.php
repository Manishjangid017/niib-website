<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Hero Sliders'; $active='sliders';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $data = [
            'title'=>trim($_POST['title']??''),
            'subtitle'=>trim($_POST['subtitle']??''),
            'description'=>trim($_POST['description']??''),
            'button_text'=>trim($_POST['button_text']??''),
            'button_url'=>trim($_POST['button_url']??''),
            'button_text_2'=>trim($_POST['button_text_2']??''),
            'button_url_2'=>trim($_POST['button_url_2']??''),
            'text_align'=>in_array($_POST['text_align']??'left',['left','center','right'])?$_POST['text_align']:'left',
            'sort_order'=>(int)($_POST['sort_order']??0),
            'status'=>isset($_POST['status'])?1:0,
        ];
        if (!empty($_FILES['image']['name'])) { $u=upload_file('image','sliders'); if($u['ok']) $data['image']=$u['path']; }
        elseif (!empty($_POST['existing_image'])) $data['image']=$_POST['existing_image'];

        $editId=(int)($_POST['id']??0);
        if ($editId>0) {
            $sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE sliders SET ".implode(',',$sets)." WHERE id=?")->execute($vals);
        } else {
            $cols=array_keys($data);$pdo->prepare("INSERT INTO sliders (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));
        }
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/sliders.php');
}
if ($action==='delete' && $id>0) { crud_delete('sliders',$id,['image']); redirect(ADMIN_URL.'/modules/sliders.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('sliders',$id); redirect(ADMIN_URL.'/modules/sliders.php'); }

include __DIR__.'/../includes/header.php';

if ($action==='add' || $action==='edit') {
    $s = ['id'=>0,'title'=>'','subtitle'=>'','description'=>'','image'=>'','button_text'=>'','button_url'=>'','button_text_2'=>'','button_url_2'=>'','text_align'=>'left','sort_order'=>0,'status'=>1];
    if ($action==='edit' && $id>0) { $r=crud_fetch('sliders',$id); if($r)$s=$r; }
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Slider':'Add Slider' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/sliders.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
<input type="hidden" name="existing_image" value="<?= e($s['image']) ?>">
<div class="row g-3"><div class="col-lg-8">
<div class="card"><div class="card-header">Slide Content</div><div class="card-body">
    <div class="mb-3"><label class="form-label">Subtitle</label><input name="subtitle" class="form-control" value="<?= e($s['subtitle']) ?>"></div>
    <div class="mb-3"><label class="form-label">Title</label><input name="title" class="form-control" value="<?= e($s['title']) ?>"></div>
    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= e($s['description']) ?></textarea></div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Button 1 Text</label><input name="button_text" class="form-control" value="<?= e($s['button_text']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Button 1 URL</label><input name="button_url" class="form-control" value="<?= e($s['button_url']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Button 2 Text</label><input name="button_text_2" class="form-control" value="<?= e($s['button_text_2']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Button 2 URL</label><input name="button_url_2" class="form-control" value="<?= e($s['button_url_2']) ?>"></div>
    </div>
</div></div></div>
<div class="col-lg-4">
<div class="card"><div class="card-header">Settings</div><div class="card-body">
    <div class="mb-3"><label class="form-label">Text Align</label>
        <select name="text_align" class="form-select"><?php foreach(['left','center','right'] as $a): ?><option <?= $s['text_align']===$a?'selected':'' ?>><?= $a ?></option><?php endforeach; ?></select>
    </div>
    <div class="mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$s['sort_order'] ?>"></div>
    <div class="form-check form-switch"><input type="checkbox" name="status" id="ss" value="1" class="form-check-input" <?= $s['status']?'checked':'' ?>><label class="form-check-label" for="ss">Active</label></div>
</div></div>
<div class="card"><div class="card-header">Background Image</div><div class="card-body">
    <?php if ($s['image']): ?><img id="sp" src="<?= e(img_url($s['image'])) ?>" class="preview-img"><?php else: ?><img id="sp" class="preview-img" style="display:none"><?php endif; ?>
    <input type="file" name="image" data-preview="sp" class="form-control mt-2" accept="image/*">
    <small class="text-muted">Recommended: 1920×900px</small>
</div></div>
<button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Save</button>
</div></div></form>
<?php } else {
    $rows=$pdo->query("SELECT * FROM sliders ORDER BY sort_order, id")->fetchAll();
?>
<div class="page-header"><h4>Sliders</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Slider</a></div>
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Image</th><th>Title</th><th>Subtitle</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><img src="<?= e(img_url($r['image'])) ?>" class="thumb" style="width:80px;height:45px"></td>
<td><?= e($r['title']) ?></td><td><?= e($r['subtitle']) ?></td><td><?= (int)$r['sort_order'] ?></td>
<td><?= $r['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
<td>
    <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
    <a class="btn btn-sm btn-outline-warning" href="?action=toggle&id=<?= $r['id'] ?>"><i class="bi bi-power"></i></a>
    <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
</td></tr>
<?php endforeach; if(empty($rows)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No sliders.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php } ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

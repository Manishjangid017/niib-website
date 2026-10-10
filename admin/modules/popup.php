<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Popup Settings'; $active='popup';
$pdo=db();
$existing = $pdo->query("SELECT * FROM popup_settings ORDER BY id ASC LIMIT 1")->fetch();
if (!$existing) {
    $pdo->exec("INSERT INTO popup_settings (title, status) VALUES ('', 0)");
    $existing = $pdo->query("SELECT * FROM popup_settings ORDER BY id ASC LIMIT 1")->fetch();
}
$id = (int)$existing['id'];

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $data=['title'=>trim($_POST['title']??''),'description'=>trim($_POST['description']??''),
            'button_text'=>trim($_POST['button_text']??''),'button_url'=>trim($_POST['button_url']??''),
            'delay_seconds'=>(int)($_POST['delay_seconds']??3),
            'show_once'=>isset($_POST['show_once'])?1:0,
            'popup_type'=>in_array($_POST['popup_type']??'message',['image','form','message'])?$_POST['popup_type']:'message',
            'status'=>isset($_POST['status'])?1:0];
        if (!empty($_FILES['image']['name'])) { $u=upload_file('image','media'); if($u['ok']) $data['image']=$u['path']; }
        elseif (!empty($_POST['existing_image'])) $data['image']=$_POST['existing_image'];

        $sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$id;
        $pdo->prepare("UPDATE popup_settings SET ".implode(',',$sets)." WHERE id=?")->execute($vals);
        set_flash('success','Popup updated.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/popup.php');
}

include __DIR__.'/../includes/header.php';
?>
<div class="page-header"><h4>Popup Settings</h4></div>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<input type="hidden" name="existing_image" value="<?= e($existing['image']) ?>">
<div class="row g-3"><div class="col-lg-8">
<div class="card"><div class="card-header">Popup Content</div><div class="card-body">
    <div class="mb-3"><label class="form-label">Title</label><input name="title" class="form-control" value="<?= e($existing['title']) ?>"></div>
    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= e($existing['description']) ?></textarea></div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Button Text</label><input name="button_text" class="form-control" value="<?= e($existing['button_text']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Button URL</label><input name="button_url" class="form-control" value="<?= e($existing['button_url']) ?>"></div>
    </div>
    <div class="mt-3"><label class="form-label">Image (optional)</label>
        <?php if($existing['image']):?><img id="ppp" src="<?= e(img_url($existing['image'])) ?>" class="preview-img"><?php else: ?><img id="ppp" class="preview-img" style="display:none"><?php endif; ?>
        <input type="file" name="image" data-preview="ppp" class="form-control mt-2" accept="image/*">
    </div>
</div></div></div>

<div class="col-lg-4">
<div class="card"><div class="card-header">Settings</div><div class="card-body">
    <div class="mb-3"><label class="form-label">Popup Type</label>
        <select name="popup_type" class="form-select"><?php foreach(['message','image','form'] as $pt): ?><option <?= $existing['popup_type']===$pt?'selected':'' ?>><?= $pt ?></option><?php endforeach; ?></select>
    </div>
    <div class="mb-3"><label class="form-label">Delay (seconds)</label><input type="number" name="delay_seconds" class="form-control" value="<?= (int)$existing['delay_seconds'] ?>" min="0"></div>
    <div class="form-check form-switch"><input type="checkbox" name="show_once" id="so" value="1" class="form-check-input" <?= $existing['show_once']?'checked':'' ?>><label class="form-check-label" for="so">Show only once per visitor</label></div>
    <div class="form-check form-switch"><input type="checkbox" name="status" id="ps" value="1" class="form-check-input" <?= $existing['status']?'checked':'' ?>><label class="form-check-label" for="ps">Enable Popup</label></div>
</div></div>
<button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Save Popup</button>
</div></div></form>
<?php include __DIR__.'/../includes/footer.php'; ?>

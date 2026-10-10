<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/crud.php';
$page_title='Menu Management'; $active='menu';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $data = [
            'title'=>trim($_POST['title']??''),
            'url'=>trim($_POST['url']??''),
            'parent_id'=>(int)($_POST['parent_id']??0),
            'menu_location'=>in_array($_POST['menu_location']??'header',['header','footer','both'])?$_POST['menu_location']:'header',
            'target'=>($_POST['target']??'_self')==='_blank'?'_blank':'_self',
            'icon'=>trim($_POST['icon']??''),
            'sort_order'=>(int)($_POST['sort_order']??0),
            'status'=>isset($_POST['status'])?1:0,
        ];
        if ($data['title']==='') throw new Exception('Title required.');
        $editId=(int)($_POST['id']??0);
        if ($editId>0) {
            $sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE menu_items SET ".implode(',',$sets)." WHERE id=?")->execute($vals);
        } else {
            $cols=array_keys($data);$pdo->prepare("INSERT INTO menu_items (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($data));
        }
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/menu.php');
}
if ($action==='delete' && $id>0) { crud_delete('menu_items',$id); redirect(ADMIN_URL.'/modules/menu.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('menu_items',$id); redirect(ADMIN_URL.'/modules/menu.php'); }

include __DIR__.'/../includes/header.php';

if ($action==='add' || $action==='edit') {
    $m=['id'=>0,'title'=>'','url'=>'','parent_id'=>0,'menu_location'=>'header','target'=>'_self','icon'=>'','sort_order'=>0,'status'=>1];
    if ($action==='edit' && $id>0) { $r=crud_fetch('menu_items',$id); if($r)$m=$r; }
    $parents = $pdo->query("SELECT id,title FROM menu_items WHERE parent_id=0 AND id<>".(int)$m['id']." ORDER BY sort_order")->fetchAll();
    $pages = $pdo->query("SELECT slug,title FROM pages WHERE status=1")->fetchAll();
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit Menu Item':'Add Menu Item' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/menu.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
<div class="card"><div class="card-body">
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Title *</label><input name="title" class="form-control" value="<?= e($m['title']) ?>" required></div>
    <div class="col-md-6"><label class="form-label">URL</label><input name="url" class="form-control" value="<?= e($m['url']) ?>" placeholder="e.g. about, courses.php, https://..."></div>
    <div class="col-md-4"><label class="form-label">Parent</label>
        <select name="parent_id" class="form-select"><option value="0">— Top Level —</option>
        <?php foreach($parents as $p): ?><option value="<?= $p['id'] ?>" <?= $p['id']==$m['parent_id']?'selected':'' ?>><?= e($p['title']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4"><label class="form-label">Location</label>
        <select name="menu_location" class="form-select"><?php foreach(['header','footer','both'] as $loc): ?><option <?= $m['menu_location']===$loc?'selected':'' ?>><?= $loc ?></option><?php endforeach; ?></select>
    </div>
    <div class="col-md-4"><label class="form-label">Target</label>
        <select name="target" class="form-select"><option value="_self" <?= $m['target']==='_self'?'selected':'' ?>>Same Tab</option><option value="_blank" <?= $m['target']==='_blank'?'selected':'' ?>>New Tab</option></select>
    </div>
    <div class="col-md-4"><label class="form-label">Icon (Bootstrap Icons class)</label><input name="icon" class="form-control" value="<?= e($m['icon']) ?>" placeholder="e.g. bi-house"></div>
    <div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$m['sort_order'] ?>"></div>
    <div class="col-md-4 d-flex align-items-end"><div class="form-check form-switch"><input type="checkbox" name="status" id="ms" value="1" class="form-check-input" <?= $m['status']?'checked':'' ?>><label class="form-check-label" for="ms">Active</label></div></div>
    <?php if ($pages): ?>
    <div class="col-12"><label class="form-label">Or link to a page:</label>
        <div class="d-flex flex-wrap gap-2">
        <?php foreach ($pages as $pg): ?>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelector('input[name=url]').value='page/<?= e($pg['slug']) ?>'"><?= e($pg['title']) ?></button>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save</button>
</form>
<?php } else {
    $rows = $pdo->query("SELECT m.*, p.title AS parent_title FROM menu_items m LEFT JOIN menu_items p ON p.id=m.parent_id ORDER BY m.menu_location, m.parent_id, m.sort_order")->fetchAll();
?>
<div class="page-header"><h4>Menu Items</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Menu Item</a></div>
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Title</th><th>URL</th><th>Location</th><th>Parent</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><?= e($r['title']) ?></td><td><code><?= e($r['url']) ?></code></td>
<td><span class="badge bg-secondary"><?= e($r['menu_location']) ?></span></td>
<td><?= e($r['parent_title'] ?? '—') ?></td><td><?= (int)$r['sort_order'] ?></td>
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

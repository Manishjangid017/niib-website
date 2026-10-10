<?php
require __DIR__.'/../includes/auth.php';
require_super_admin();
$page_title='Admin Users'; $active='users';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId=(int)($_POST['id']??0);
        $name=trim($_POST['name']??''); $email=trim($_POST['email']??'');
        $role=in_array($_POST['role']??'editor',['super_admin','editor'])?$_POST['role']:'editor';
        $status=isset($_POST['status'])?1:0;
        $pw=$_POST['password']??'';
        if ($name==='' || !valid_email($email)) throw new Exception('Name and valid email required');
        // unique email check
        $check=$pdo->prepare("SELECT id FROM admin_users WHERE email=? AND id<>?"); $check->execute([$email,$editId]);
        if ($check->fetch()) throw new Exception('Email already in use');

        if ($editId>0) {
            if ($pw!=='' && strlen($pw)>=4) {
                $pdo->prepare("UPDATE admin_users SET name=?,email=?,role=?,status=?,password=? WHERE id=?")
                    ->execute([$name,$email,$role,$status,password_hash($pw,PASSWORD_BCRYPT),$editId]);
            } else {
                $pdo->prepare("UPDATE admin_users SET name=?,email=?,role=?,status=? WHERE id=?")
                    ->execute([$name,$email,$role,$status,$editId]);
            }
        } else {
            if (strlen($pw)<4) throw new Exception('Password too short');
            $pdo->prepare("INSERT INTO admin_users (name,email,role,status,password) VALUES (?,?,?,?,?)")
                ->execute([$name,$email,$role,$status,password_hash($pw,PASSWORD_BCRYPT)]);
        }
        log_admin_action('save','users','User saved');
        set_flash('success','Saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/users.php');
}
if ($action==='delete' && $id>0 && $id !== (int)$current_admin['id']) {
    $pdo->prepare("DELETE FROM admin_users WHERE id=?")->execute([$id]);
    set_flash('success','Deleted.');
    redirect(ADMIN_URL.'/modules/users.php');
}

include __DIR__.'/../includes/header.php';

if ($action==='add' || $action==='edit') {
    $u=['id'=>0,'name'=>'','email'=>'','role'=>'editor','status'=>1];
    if ($action==='edit' && $id>0){
        $r = $pdo->prepare("SELECT * FROM admin_users WHERE id=?"); $r->execute([$id]); $row=$r->fetch();
        if ($row) $u=$row;
    }
?>
<div class="page-header"><h4><?= $action==='edit'?'Edit User':'Add User' ?></h4><a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/users.php"><i class="bi bi-arrow-left"></i> Back</a></div>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
<div class="card"><div class="card-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" required value="<?= e($u['name']) ?>"></div>
<div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="<?= e($u['email']) ?>"></div>
<div class="col-md-6"><label class="form-label">Password <?= $u['id']?'(leave blank to keep)':'*' ?></label><input type="password" name="password" class="form-control" <?= $u['id']?'':'required' ?>></div>
<div class="col-md-3"><label class="form-label">Role</label>
    <select name="role" class="form-select">
        <option value="super_admin" <?= $u['role']==='super_admin'?'selected':'' ?>>Super Admin</option>
        <option value="editor" <?= $u['role']==='editor'?'selected':'' ?>>Editor</option>
    </select>
</div>
<div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input type="checkbox" name="status" id="ust" class="form-check-input" value="1" <?= $u['status']?'checked':'' ?>><label for="ust" class="form-check-label">Active</label></div></div>
</div></div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save User</button>
</form>
<?php } else { $rows=$pdo->query("SELECT * FROM admin_users ORDER BY id DESC")->fetchAll(); ?>
<div class="page-header"><h4>Admin Users</h4><a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add User</a></div>
<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last Login</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><?= e($r['name']) ?><?= $r['id']==$current_admin['id']?' <span class="badge bg-info">You</span>':'' ?></td>
<td><?= e($r['email']) ?></td><td><span class="badge bg-secondary"><?= e(str_replace('_',' ',$r['role'])) ?></span></td>
<td><?= $r['last_login']?fdate($r['last_login'],'d M Y H:i'):'-' ?></td>
<td><?= $r['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
<td>
    <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
    <?php if ($r['id']!=$current_admin['id']): ?>
    <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
    <?php endif; ?>
</td></tr>
<?php endforeach; ?>
</tbody></table></div></div></div>
<?php } ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

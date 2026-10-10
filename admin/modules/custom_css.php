<?php
require __DIR__.'/../includes/auth.php';
$page_title='Custom CSS'; $active='custom_css';
$pdo=db();
$row = $pdo->query("SELECT * FROM custom_css ORDER BY id ASC LIMIT 1")->fetch();
if (!$row) { $pdo->exec("INSERT INTO custom_css (name,code,status) VALUES ('Global CSS','',1)"); $row = $pdo->query("SELECT * FROM custom_css ORDER BY id ASC LIMIT 1")->fetch(); }

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        // sanitize: keep CSS, remove </style> tags as a basic safety guard
        $code = preg_replace('~</?\s*style[^>]*>~i','', $_POST['code'] ?? '');
        $pdo->prepare("UPDATE custom_css SET code=?, status=?, updated_at=NOW() WHERE id=?")
            ->execute([$code, isset($_POST['status'])?1:0, $row['id']]);
        log_admin_action('update','custom_css','Custom CSS updated');
        set_flash('success','Custom CSS saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/custom_css.php');
}

include __DIR__.'/../includes/header.php';
?>
<div class="page-header"><h4>Custom CSS</h4></div>
<form method="post"><?= csrf_field() ?>
<div class="card"><div class="card-body">
<p class="text-muted small">CSS added here loads globally on every page. Use this for styling tweaks without editing core files.</p>
<div class="form-check form-switch mb-3"><input type="checkbox" name="status" id="ccst" class="form-check-input" value="1" <?= $row['status']?'checked':'' ?>><label for="ccst" class="form-check-label">Enable Custom CSS</label></div>
<textarea name="code" class="code-editor" rows="22"><?= e($row['code']) ?></textarea>
</div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save CSS</button>
</form>
<?php include __DIR__.'/../includes/footer.php'; ?>

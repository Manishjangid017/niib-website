<?php
require __DIR__.'/../includes/auth.php';
$page_title='Custom JavaScript'; $active='custom_js';
$pdo=db();
$row = $pdo->query("SELECT * FROM custom_js ORDER BY id ASC LIMIT 1")->fetch();
if (!$row) { $pdo->exec("INSERT INTO custom_js (name,code,status) VALUES ('Global JS','',1)"); $row = $pdo->query("SELECT * FROM custom_js ORDER BY id ASC LIMIT 1")->fetch(); }

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $code = preg_replace('~</?\s*script[^>]*>~i','', $_POST['code'] ?? '');
        $pdo->prepare("UPDATE custom_js SET code=?, status=?, updated_at=NOW() WHERE id=?")
            ->execute([$code, isset($_POST['status'])?1:0, $row['id']]);
        log_admin_action('update','custom_js','Custom JS updated');
        set_flash('success','Custom JS saved.');
    } catch(Throwable $e){set_flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/modules/custom_js.php');
}

include __DIR__.'/../includes/header.php';
?>
<div class="page-header"><h4>Custom JavaScript</h4></div>
<form method="post"><?= csrf_field() ?>
<div class="card"><div class="card-body">
<p class="text-muted small">JS added here loads globally before the closing &lt;/body&gt; tag.</p>
<div class="form-check form-switch mb-3"><input type="checkbox" name="status" id="cjst" class="form-check-input" value="1" <?= $row['status']?'checked':'' ?>><label for="cjst" class="form-check-label">Enable Custom JS</label></div>
<textarea name="code" class="code-editor" rows="22"><?= e($row['code']) ?></textarea>
</div></div>
<button class="btn btn-primary mt-3"><i class="bi bi-check2-circle"></i> Save JS</button>
</form>
<?php include __DIR__.'/../includes/footer.php'; ?>

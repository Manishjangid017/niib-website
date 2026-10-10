<?php
require __DIR__.'/../includes/auth.php';
$page_title='Newsletter Subscribers'; $active='newsletter';
$pdo=db();
if (!empty($_GET['action']) && $_GET['action']==='delete' && (int)($_GET['id']??0)>0) {
    $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id=?")->execute([(int)$_GET['id']]);
    set_flash('success','Deleted.');
    redirect(ADMIN_URL.'/modules/newsletter.php');
}
if (!empty($_GET['export']) && $_GET['export']==='csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="subscribers_'.date('Ymd').'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out,['ID','Email','Status','Date']);
    foreach ($pdo->query("SELECT * FROM newsletter_subscribers ORDER BY id DESC") as $r) {
        fputcsv($out,[$r['id'],$r['email'],$r['status']?'Active':'Inactive',$r['created_at']]);
    }
    fclose($out); exit;
}
$rows = $pdo->query("SELECT * FROM newsletter_subscribers ORDER BY id DESC")->fetchAll();
include __DIR__.'/../includes/header.php';
?>
<div class="page-header"><h4>Newsletter Subscribers (<?= count($rows) ?>)</h4>
<a href="?export=csv" class="btn btn-outline-success btn-sm"><i class="bi bi-download"></i> Export CSV</a></div>
<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Email</th><th>Subscribed</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['email']) ?></td><td><?= fdate($r['created_at'],'d M Y H:i') ?></td>
<td><?= $r['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
<td><a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a></td></tr>
<?php endforeach; if (empty($rows)): ?><tr><td colspan="4" class="text-center text-muted py-4">No subscribers.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

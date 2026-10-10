<?php
require __DIR__.'/../includes/auth.php';
$page_title='Contact Messages'; $active='contacts';
$pdo=db();

if (!empty($_GET['del']) && (int)$_GET['del']>0 && csrf_verify_get()) { /* placeholder */ }

if (!empty($_GET['action']) && $_GET['action']==='delete' && (int)($_GET['id']??0)>0) {
    $pdo->prepare("DELETE FROM contact_messages WHERE id=?")->execute([(int)$_GET['id']]);
    set_flash('success','Deleted.');
    redirect(ADMIN_URL.'/modules/contacts.php');
}
if (!empty($_GET['action']) && $_GET['action']==='read' && (int)($_GET['id']??0)>0) {
    $pdo->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([(int)$_GET['id']]);
    redirect(ADMIN_URL.'/modules/contacts.php?view='.(int)$_GET['id']);
}
if (!empty($_GET['export']) && $_GET['export']==='csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="contacts_'.date('Ymd').'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out,['ID','Name','Email','Phone','City','Subject','Message','Date']);
    foreach ($pdo->query("SELECT * FROM contact_messages ORDER BY id DESC") as $r) {
        fputcsv($out,[$r['id'],$r['name'],$r['email'],$r['phone'],$r['city'] ?? '',$r['subject'],$r['message'],$r['created_at']]);
    }
    fclose($out);
    exit;
}

$view = (int)($_GET['view']??0);
if ($view>0) {
    $stmt=$pdo->prepare("SELECT * FROM contact_messages WHERE id=?"); $stmt->execute([$view]); $msg=$stmt->fetch();
    if ($msg && !$msg['is_read']) $pdo->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([$view]);
}

$perPage=25; $page=max(1,(int)($_GET['page']??1)); $offset=($page-1)*$perPage;
$rows=$pdo->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT $perPage OFFSET $offset")->fetchAll();
$total=(int)$pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

function csrf_verify_get(){return true;}

include __DIR__.'/../includes/header.php';
?>
<div class="page-header"><h4>Contact Messages (<?= $total ?>)</h4>
<a href="?export=csv" class="btn btn-outline-success btn-sm"><i class="bi bi-download"></i> Export CSV</a></div>

<?php if ($view>0 && !empty($msg)): ?>
<div class="card mb-3"><div class="card-header d-flex justify-content-between">Message from <?= e($msg['name']) ?> <a href="<?= ADMIN_URL ?>/modules/contacts.php" class="btn btn-sm btn-outline-secondary">Close</a></div>
<div class="card-body">
<table class="table">
<tr><th width="180">Name</th><td><?= e($msg['name']) ?></td></tr>
<tr><th>Email</th><td><a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a></td></tr>
<tr><th>Phone</th><td><?= e($msg['phone']) ?></td></tr>
<tr><th>City</th><td><?= e($msg['city'] ?? '') ?></td></tr>
<tr><th>Subject</th><td><?= e($msg['subject']) ?></td></tr>
<tr><th>Date</th><td><?= e($msg['created_at']) ?></td></tr>
<tr><th>IP</th><td><?= e($msg['ip_address']) ?></td></tr>
<tr><th>Message</th><td><?= nl2br(e($msg['message'])) ?></td></tr>
</table>
</div></div>
<?php endif; ?>

<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>City</th><th>Subject</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr class="<?= $r['is_read']?'':'table-warning' ?>">
    <td><?= e($r['name']) ?></td>
    <td><?= e($r['email']) ?></td>
    <td><?= e($r['phone']) ?></td>
    <td><?= e($r['city'] ?? '') ?></td>
    <td><?= e($r['subject']) ?></td>
    <td><?= fdate($r['created_at'],'d M Y H:i') ?></td>
    <td><?= $r['is_read']?'<span class="badge bg-success">Read</span>':'<span class="badge bg-danger">New</span>' ?></td>
    <td>
        <a class="btn btn-sm btn-outline-primary" href="?view=<?= $r['id'] ?>"><i class="bi bi-eye"></i></a>
        <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
    </td>
</tr>
<?php endforeach; if (empty($rows)): ?><tr><td colspan="8" class="text-center text-muted py-4">No messages.</td></tr><?php endif; ?>
</tbody></table></div>
<?= paginate($total,$perPage,$page,ADMIN_URL.'/modules/contacts.php') ?>
</div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

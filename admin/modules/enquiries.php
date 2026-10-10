<?php
require __DIR__.'/../includes/auth.php';
$page_title='Course Enquiries'; $active='enquiries';
$pdo=db();

if (!empty($_GET['action']) && $_GET['action']==='delete' && (int)($_GET['id']??0)>0) {
    $pdo->prepare("DELETE FROM enquiry_forms WHERE id=?")->execute([(int)$_GET['id']]);
    set_flash('success','Deleted.');
    redirect(ADMIN_URL.'/modules/enquiries.php');
}
if (!empty($_GET['export']) && $_GET['export']==='csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="enquiries_'.date('Ymd').'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out,['ID','Name','Email','Phone','City','Course','Message','Source','Date']);
    foreach ($pdo->query("SELECT * FROM enquiry_forms ORDER BY id DESC") as $r) {
        fputcsv($out,[$r['id'],$r['name'],$r['email'],$r['phone'],$r['city'] ?? '',$r['course_name'],$r['message'],$r['source'],$r['created_at']]);
    }
    fclose($out); exit;
}
$view = (int)($_GET['view']??0);
if ($view>0) { $stmt=$pdo->prepare("SELECT * FROM enquiry_forms WHERE id=?");$stmt->execute([$view]);$msg=$stmt->fetch();
    if ($msg && !$msg['is_read']) $pdo->prepare("UPDATE enquiry_forms SET is_read=1 WHERE id=?")->execute([$view]);
}

$perPage=25; $page=max(1,(int)($_GET['page']??1)); $offset=($page-1)*$perPage;
$rows=$pdo->query("SELECT * FROM enquiry_forms ORDER BY id DESC LIMIT $perPage OFFSET $offset")->fetchAll();
$total=(int)$pdo->query("SELECT COUNT(*) FROM enquiry_forms")->fetchColumn();

include __DIR__.'/../includes/header.php';
?>
<div class="page-header"><h4>Course Enquiries (<?= $total ?>)</h4>
<a href="?export=csv" class="btn btn-outline-success btn-sm"><i class="bi bi-download"></i> Export CSV</a></div>
<?php if ($view>0 && !empty($msg)): ?>
<div class="card mb-3"><div class="card-header d-flex justify-content-between">Enquiry from <?= e($msg['name']) ?> <a href="<?= ADMIN_URL ?>/modules/enquiries.php" class="btn btn-sm btn-outline-secondary">Close</a></div>
<div class="card-body"><table class="table">
<tr><th width="180">Name</th><td><?= e($msg['name']) ?></td></tr>
<tr><th>Email</th><td><a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a></td></tr>
<tr><th>Phone</th><td><a href="tel:<?= e($msg['phone']) ?>"><?= e($msg['phone']) ?></a></td></tr>
<tr><th>City</th><td><?= e($msg['city'] ?? '') ?></td></tr>
<tr><th>Course</th><td><?= e($msg['course_name']) ?></td></tr>
<tr><th>Source</th><td><?= e($msg['source']) ?></td></tr>
<tr><th>Date</th><td><?= e($msg['created_at']) ?></td></tr>
<tr><th>Message</th><td><?= nl2br(e($msg['message'])) ?></td></tr>
</table></div></div>
<?php endif; ?>
<div class="card"><div class="card-body"><div class="table-responsive">
<table class="table table-hover"><thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>City</th><th>Course</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr class="<?= $r['is_read']?'':'table-warning' ?>">
    <td><?= e($r['name']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['email']) ?></td>
    <td><?= e($r['city'] ?? '') ?></td>
    <td><?= e($r['course_name']) ?></td>
    <td><?= fdate($r['created_at'],'d M Y H:i') ?></td>
    <td><?= $r['is_read']?'<span class="badge bg-success">Read</span>':'<span class="badge bg-danger">New</span>' ?></td>
    <td>
        <a class="btn btn-sm btn-outline-primary" href="?view=<?= $r['id'] ?>"><i class="bi bi-eye"></i></a>
        <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
    </td>
</tr>
<?php endforeach; if (empty($rows)): ?><tr><td colspan="8" class="text-center text-muted py-4">No enquiries.</td></tr><?php endif; ?>
</tbody></table></div>
<?= paginate($total,$perPage,$page,ADMIN_URL.'/modules/enquiries.php') ?>
</div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

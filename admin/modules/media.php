<?php
require __DIR__.'/../includes/auth.php';
$page_title='Media Library'; $active='media';
$pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify() && !empty($_FILES['files']['name'][0])) {
    $files = $_FILES['files'];
    $count=0;
    for ($i=0;$i<count($files['name']);$i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
        $tmp = ['name'=>$files['name'][$i],'type'=>$files['type'][$i],'tmp_name'=>$files['tmp_name'][$i],'error'=>$files['error'][$i],'size'=>$files['size'][$i]];
        $_FILES['__one'] = $tmp;
        $u = upload_file('__one','media',['jpg','jpeg','png','gif','webp','svg','pdf','doc','docx','mp4'], 256 * 1024 * 1024);
        if ($u['ok']) $count++;
    }
    set_flash('success', $count.' file(s) uploaded.');
    redirect(ADMIN_URL.'/modules/media.php');
}
// Single delete
if (!empty($_GET['action']) && $_GET['action']==='delete' && (int)($_GET['id']??0)>0) {
    $r = $pdo->prepare("SELECT file_path FROM media_library WHERE id=?"); $r->execute([(int)$_GET['id']]); $row=$r->fetch();
    if ($row) delete_uploaded_file($row['file_path']);
    $pdo->prepare("DELETE FROM media_library WHERE id=?")->execute([(int)$_GET['id']]);
    redirect(ADMIN_URL.'/modules/media.php');
}

// Bulk delete
if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify() && !empty($_POST['bulk_action']) && $_POST['bulk_action']==='delete' && !empty($_POST['media_ids'])) {
    $ids = array_filter(array_map('intval', (array)$_POST['media_ids']));
    if (!empty($ids)) {
        $deletedCount = 0;
        foreach ($ids as $id) {
            $r = $pdo->prepare("SELECT file_path FROM media_library WHERE id=?");
            $r->execute([$id]);
            $row = $r->fetch();
            if ($row) {
                delete_uploaded_file($row['file_path']);
                $pdo->prepare("DELETE FROM media_library WHERE id=?")->execute([$id]);
                $deletedCount++;
            }
        }
        set_flash('success', $deletedCount.' file(s) deleted.');
    }
    redirect(ADMIN_URL.'/modules/media.php?page='.(int)($_POST['current_page']??1));
}

$perPage=24; $page=max(1,(int)($_GET['page']??1)); $offset=($page-1)*$perPage;
$rows = $pdo->query("SELECT * FROM media_library ORDER BY id DESC LIMIT $perPage OFFSET $offset")->fetchAll();
$total = (int)$pdo->query("SELECT COUNT(*) FROM media_library")->fetchColumn();

include __DIR__.'/../includes/header.php';
?>
<div class="page-header">
    <h4>Media Library (<?= $total ?>)</h4>
    <div id="bulkActions" style="display:none;" class="d-flex gap-2">
        <span id="selectedCount" class="badge bg-info align-self-center">0 selected</span>
        <button type="button" class="btn btn-sm btn-danger" onclick="bulkDelete()"><i class="bi bi-trash"></i> Delete Selected</button>
        <button type="button" class="btn btn-sm btn-secondary" onclick="clearSelection()">Cancel</button>
    </div>
</div>

<div class="card mb-3"><div class="card-body">
<form method="post" enctype="multipart/form-data" class="d-flex gap-2 flex-wrap"><?= csrf_field() ?>
<input type="file" name="files[]" class="form-control" multiple accept="image/*,application/pdf,video/mp4" required>
<button class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
</form>
</div></div>

<div class="card mb-3" id="bulkSelectCard" style="display:none;">
    <div class="card-body d-flex align-items-center gap-3">
        <input type="checkbox" id="selectAll" class="form-check-input" style="width:20px;height:20px;cursor:pointer;" onchange="toggleSelectAll(this)">
        <label for="selectAll" class="mb-0" style="cursor:pointer;flex-grow:1;">Select All on This Page</label>
    </div>
</div>
<form id="bulkDeleteForm" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="bulk_action" value="delete">
    <input type="hidden" name="current_page" value="<?= $page ?>">

    <div class="row g-3">
    <?php foreach ($rows as $r):
        $isImg = in_array(strtolower($r['file_type']),['jpg','jpeg','png','gif','webp','svg']);
    ?>
    <div class="col-md-3 col-sm-4 col-6">
        <div class="card h-100 media-item" data-id="<?= $r['id'] ?>">
            <div class="position-relative">
                <?php if($isImg): ?><img src="<?= e(img_url($r['file_path'])) ?>" style="height:140px;object-fit:cover;display:block">
                <?php else: ?><div class="text-center p-4 bg-light" style="height:140px;display:flex;align-items:center;justify-content:center;flex-direction:column"><i class="bi bi-file-earmark" style="font-size:3rem"></i><br><small><?= e(strtoupper($r['file_type'])) ?></small></div><?php endif; ?>
                <div class="position-absolute top-0 start-0 p-2">
                    <input type="checkbox" name="media_ids[]" value="<?= $r['id'] ?>" class="form-check-input media-checkbox" style="width:20px;height:20px;cursor:pointer;" onchange="updateSelection()">
                </div>
            </div>
            <div class="card-body p-2 small">
                <div class="text-truncate" title="<?= e($r['original_name']) ?>"><?= e($r['original_name']) ?></div>
                <div class="text-muted"><?= round($r['file_size']/1024) ?> KB</div>
                <div class="mt-2 d-flex gap-1">
                    <a class="btn btn-sm btn-outline-primary" href="<?= e(img_url($r['file_path'])) ?>" target="_blank" title="Open"><i class="bi bi-link-45deg"></i></a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText('<?= e(img_url($r['file_path'])) ?>');this.innerHTML='<i class=bi bi-check></i>'" title="Copy URL"><i class="bi bi-clipboard"></i></button>
                    <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>" title="Delete"><i class="bi bi-trash"></i></a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; if(empty($rows)): ?><div class="col-12 text-center text-muted py-4">No media yet.</div><?php endif; ?>
    </div>
</form>
<?= paginate($total,$perPage,$page,ADMIN_URL.'/modules/media.php') ?>

<style>
.media-item {
    transition: all 0.2s;
    position: relative;
}

.media-item .media-checkbox:checked ~ * {
    opacity: 0.7;
}

.media-item:has(.media-checkbox:checked) {
    border: 2px solid #0d6efd;
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.3);
}

.media-item .form-check-input {
    background: white;
    border: 2px solid #ccc;
}

.media-item .form-check-input:checked {
    background: #0d6efd;
    border-color: #0d6efd;
}

.media-item .form-check-input:hover {
    cursor: pointer;
    border-color: #0d6efd;
}

#bulkActions {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
(function() {
    const mediaItems = document.querySelectorAll('.media-item');
    const checkboxes = document.querySelectorAll('.media-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    const bulkActionsDiv = document.getElementById('bulkActions');
    const bulkSelectCard = document.getElementById('bulkSelectCard');
    const selectedCountSpan = document.getElementById('selectedCount');

    // Show bulk select card if there are items
    if (mediaItems.length > 0) {
        bulkSelectCard.style.display = 'block';
    }

    function updateSelection() {
        const checkedCount = document.querySelectorAll('.media-checkbox:checked').length;
        const allChecked = checkboxes.length > 0 && checkboxes.length === checkedCount;

        // Update "Select All" checkbox state
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = allChecked;
        }

        // Show/hide bulk actions
        if (checkedCount > 0) {
            bulkActionsDiv.style.display = 'flex';
            selectedCountSpan.textContent = checkedCount + ' selected';
        } else {
            bulkActionsDiv.style.display = 'none';
            selectAllCheckbox.checked = false;
        }
    }

    function toggleSelectAll(checkbox) {
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateSelection();
    }

    function clearSelection() {
        checkboxes.forEach(cb => cb.checked = false);
        selectAllCheckbox.checked = false;
        bulkActionsDiv.style.display = 'none';
    }

    function bulkDelete() {
        const checkedCount = document.querySelectorAll('.media-checkbox:checked').length;
        if (checkedCount === 0) {
            alert('Please select at least one file to delete');
            return;
        }

        if (confirm('Are you sure you want to delete ' + checkedCount + ' file(s)? This action cannot be undone.')) {
            document.getElementById('bulkDeleteForm').submit();
        }
    }

    // Expose functions globally
    window.updateSelection = updateSelection;
    window.toggleSelectAll = toggleSelectAll;
    window.clearSelection = clearSelection;
    window.bulkDelete = bulkDelete;
})();
</script>

<?php include __DIR__.'/../includes/footer.php'; ?>

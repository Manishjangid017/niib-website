<?php
require __DIR__ . '/../includes/auth.php';
$page_title = 'Courses';
$active = 'courses';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$pdo = db();

// Handle POST (save / update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    try {
        $data = [
            'category_id'       => (int)($_POST['category_id'] ?? 0) ?: null,
            'title'             => trim($_POST['title'] ?? ''),
            'slug'              => trim($_POST['slug'] ?? ''),
            'short_description' => $_POST['short_description'] ?? '',
            'full_description'  => $_POST['full_description'] ?? '',
            'duration'          => trim($_POST['duration'] ?? ''),
            'fees'              => trim($_POST['fees'] ?? ''),
            'original_fees'     => trim($_POST['original_fees'] ?? ''),
            'eligibility'       => $_POST['eligibility'] ?? '',
            'level'             => trim($_POST['level'] ?? ''),
            'language'          => trim($_POST['language'] ?? ''),
            'video_url'         => trim($_POST['video_url'] ?? ''),
            'is_featured'       => isset($_POST['is_featured']) ? 1 : 0,
            'meta_title'        => trim($_POST['meta_title'] ?? ''),
            'meta_description'  => trim($_POST['meta_description'] ?? ''),
            'meta_keywords'     => trim($_POST['meta_keywords'] ?? ''),
            'canonical_url'     => trim($_POST['canonical_url'] ?? ''),
            'schema_code'       => $_POST['schema_code'] ?? '',
            'status'            => isset($_POST['status']) ? 1 : 0,
        ];
        if (empty($data['title'])) throw new Exception('Title is required.');
        if (empty($data['slug'])) $data['slug'] = $data['title'];
        $editId = (int)($_POST['id'] ?? 0);
        $data['slug'] = unique_slug('courses', $data['slug'], $editId);

        // image
        if (!empty($_FILES['image']['name'])) {
            $u = upload_file('image','courses');
            if ($u['ok']) $data['image'] = $u['path'];
        }
        if (!empty($_POST['existing_image']) && empty($data['image'])) $data['image'] = $_POST['existing_image'];

        // brochure
        if (!empty($_FILES['brochure']['name'])) {
            $u = upload_file('brochure','brochures',['pdf','doc','docx']);
            if ($u['ok']) $data['brochure'] = $u['path'];
        }
        if (!empty($_POST['existing_brochure']) && empty($data['brochure'])) $data['brochure'] = $_POST['existing_brochure'];

        if ($editId > 0) {
            $sets = [];$vals = [];
            foreach ($data as $k=>$v){ $sets[]="`$k`=?"; $vals[]=$v; }
            $vals[]=$editId;
            $pdo->prepare("UPDATE courses SET ".implode(',',$sets).", updated_at=NOW() WHERE id=?")->execute($vals);
            log_admin_action('update','courses','Updated course #'.$editId);
            set_flash('success','Course updated.');
        } else {
            $cols = array_keys($data);
            $place = array_fill(0,count($cols),'?');
            $pdo->prepare("INSERT INTO courses (`".implode('`,`',$cols)."`) VALUES (".implode(',',$place).")")->execute(array_values($data));
            log_admin_action('create','courses','Created new course');
            set_flash('success','Course added.');
        }
    } catch (Throwable $e) {
        set_flash('error', $e->getMessage());
    }
    redirect(ADMIN_URL . '/modules/courses.php');
}

// Delete
if ($action === 'delete' && $id > 0) {
    try {
        $row = $pdo->prepare("SELECT image, brochure FROM courses WHERE id=?"); $row->execute([$id]); $r = $row->fetch();
        if ($r) { delete_uploaded_file($r['image']); delete_uploaded_file($r['brochure']); }
        $pdo->prepare("DELETE FROM courses WHERE id=?")->execute([$id]);
        log_admin_action('delete','courses','Deleted course #'.$id);
        set_flash('success','Course deleted.');
    } catch (Throwable $e) { set_flash('error', $e->getMessage()); }
    redirect(ADMIN_URL . '/modules/courses.php');
}

// Toggle status
if ($action === 'toggle' && $id > 0) {
    $pdo->prepare("UPDATE courses SET status = 1 - status WHERE id=?")->execute([$id]);
    redirect(ADMIN_URL . '/modules/courses.php');
}

include __DIR__ . '/../includes/header.php';

// Show form
if ($action === 'add' || $action === 'edit') {
    $course = [
        'id'=>0,'category_id'=>'','title'=>'','slug'=>'','short_description'=>'','full_description'=>'',
        'duration'=>'','fees'=>'','original_fees'=>'','eligibility'=>'','level'=>'Beginner','language'=>'English',
        'image'=>'','brochure'=>'','video_url'=>'','is_featured'=>0,
        'meta_title'=>'','meta_description'=>'','meta_keywords'=>'','canonical_url'=>'','schema_code'=>'','status'=>1
    ];
    if ($action === 'edit' && $id > 0) {
        $r = $pdo->prepare("SELECT * FROM courses WHERE id=?"); $r->execute([$id]); $course = $r->fetch() ?: $course;
    }
    $cats = $pdo->query("SELECT id,name FROM course_categories WHERE status=1 ORDER BY name")->fetchAll();
?>
<div class="page-header">
    <h4><?= $action==='edit'?'Edit Course':'Add Course' ?></h4>
    <a href="<?= ADMIN_URL ?>/modules/courses.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$course['id'] ?>">
    <input type="hidden" name="existing_image" value="<?= e($course['image']) ?>">
    <input type="hidden" name="existing_brochure" value="<?= e($course['brochure']) ?>">

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card"><div class="card-header">Basic Info</div><div class="card-body">
                <div class="mb-3"><label class="form-label">Title *</label><input id="course_title" name="title" class="form-control" required value="<?= e($course['title']) ?>"></div>
                <div class="mb-3"><label class="form-label">Slug</label><input name="slug" id="course_slug" data-slug-source="course_title" class="form-control" value="<?= e($course['slug']) ?>" placeholder="auto-generated"></div>
                <div class="mb-3"><label class="form-label">Short Description</label><textarea name="short_description" class="form-control" rows="2"><?= e($course['short_description']) ?></textarea></div>
                <div class="mb-3"><label class="form-label">Full Description</label><textarea name="full_description" class="form-control rich-editor" rows="14"><?= e($course['full_description']) ?></textarea></div>
                <div class="mb-3"><label class="form-label">Eligibility</label><textarea name="eligibility" class="form-control" rows="2"><?= e($course['eligibility']) ?></textarea></div>
            </div></div>

            <div class="card"><div class="card-header">SEO</div><div class="card-body">
                <div class="mb-3"><label class="form-label">Meta Title</label><input name="meta_title" class="form-control" value="<?= e($course['meta_title']) ?>"></div>
                <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2"><?= e($course['meta_description']) ?></textarea></div>
                <div class="mb-3"><label class="form-label">Meta Keywords</label><input name="meta_keywords" class="form-control" value="<?= e($course['meta_keywords']) ?>"></div>
                <div class="mb-3"><label class="form-label">Canonical URL</label><input name="canonical_url" class="form-control" value="<?= e($course['canonical_url']) ?>"></div>
                <div class="mb-3"><label class="form-label">Schema JSON-LD</label><textarea name="schema_code" class="form-control code-editor" rows="6"><?= e($course['schema_code']) ?></textarea></div>
            </div></div>
        </div>

        <div class="col-lg-4">
            <div class="card"><div class="card-header">Settings</div><div class="card-body">
                <div class="mb-3"><label class="form-label">Category</label>
                    <select name="category_id" class="form-select"><option value="">— Select —</option>
                        <?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id']==$course['category_id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Duration</label><input name="duration" class="form-control" value="<?= e($course['duration']) ?>" placeholder="e.g. 6 Months"></div>
                <div class="row g-2"><div class="col-6"><label class="form-label">Fees</label><input name="fees" class="form-control" value="<?= e($course['fees']) ?>"></div>
                <div class="col-6"><label class="form-label">Original Fees</label><input name="original_fees" class="form-control" value="<?= e($course['original_fees']) ?>"></div></div>
                <div class="mb-3 mt-3"><label class="form-label">Level</label>
                    <select name="level" class="form-select">
                        <?php foreach(['Beginner','Intermediate','Advanced'] as $lv): ?><option <?= $course['level']==$lv?'selected':'' ?>><?= $lv ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Language</label><input name="language" class="form-control" value="<?= e($course['language']) ?>"></div>
                <div class="mb-3"><label class="form-label">Video URL (YouTube)</label><input name="video_url" class="form-control" value="<?= e($course['video_url']) ?>"></div>
                <div class="form-check form-switch mb-2"><input type="checkbox" class="form-check-input" name="is_featured" id="ft" value="1" <?= $course['is_featured']?'checked':'' ?>><label class="form-check-label" for="ft">Featured</label></div>
                <div class="form-check form-switch mb-3"><input type="checkbox" class="form-check-input" name="status" id="st" value="1" <?= $course['status']?'checked':'' ?>><label class="form-check-label" for="st">Active</label></div>
            </div></div>

            <div class="card"><div class="card-header">Media</div><div class="card-body">
                <div class="mb-3"><label class="form-label">Course Image</label>
                    <?php if ($course['image']): ?><img id="imgPrev" src="<?= e(img_url($course['image'])) ?>" class="preview-img"><?php else: ?><img id="imgPrev" class="preview-img" style="display:none"><?php endif; ?>
                    <input type="file" name="image" class="form-control mt-2" data-preview="imgPrev" accept="image/*">
                </div>
                <div class="mb-3"><label class="form-label">Brochure (PDF)</label>
                    <?php if ($course['brochure']): ?><div><a href="<?= e(img_url($course['brochure'])) ?>" target="_blank"><i class="bi bi-file-pdf"></i> Current</a></div><?php endif; ?>
                    <input type="file" name="brochure" class="form-control mt-2" accept=".pdf,.doc,.docx">
                </div>
            </div></div>

            <button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Save Course</button>
        </div>
    </div>
</form>

<?php } else { // LIST
    $search = trim($_GET['q'] ?? '');
    $perPage = 20;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page-1)*$perPage;
    $where = '1=1'; $params = [];
    if ($search !== '') { $where .= " AND (c.title LIKE ? OR c.slug LIKE ?)"; $params[]="%$search%"; $params[]="%$search%"; }
    $stmt = $pdo->prepare("SELECT c.*, cat.name AS cat_name FROM courses c LEFT JOIN course_categories cat ON cat.id=c.category_id WHERE $where ORDER BY c.id DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $ct = $pdo->prepare("SELECT COUNT(*) FROM courses c WHERE $where"); $ct->execute($params); $total = (int)$ct->fetchColumn();
?>
<div class="page-header">
    <h4>Courses (<?= $total ?>)</h4>
    <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Course</a>
</div>
<div class="card"><div class="card-body">
<form class="mb-3 d-flex gap-2"><input class="form-control" name="q" placeholder="Search courses..." value="<?= e($search) ?>"><button class="btn btn-primary">Search</button></form>
<div class="table-responsive">
<table class="table table-hover">
    <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Fees</th><th>Featured</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
    <tr>
        <td><img src="<?= e(img_url($r['image'])) ?>" class="thumb"></td>
        <td><strong><?= e($r['title']) ?></strong><br><small class="text-muted"><?= e($r['slug']) ?></small></td>
        <td><?= e($r['cat_name'] ?: '-') ?></td>
        <td><?= e($r['fees']) ?></td>
        <td><?= $r['is_featured']?'<span class="badge bg-success">Yes</span>':'<span class="badge bg-secondary">No</span>' ?></td>
        <td><?= $r['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
        <td>
            <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
            <a class="btn btn-sm btn-outline-warning" href="?action=toggle&id=<?= $r['id'] ?>"><i class="bi bi-power"></i></a>
            <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
            <a class="btn btn-sm btn-outline-info" target="_blank" href="<?= SITE_URL ?>/course/<?= e($r['slug']) ?>"><i class="bi bi-eye"></i></a>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($rows)): ?><tr><td colspan="7" class="text-center text-muted py-4">No courses found.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
<?= paginate($total, $perPage, $page, ADMIN_URL.'/modules/courses.php?q='.urlencode($search)) ?>
</div></div>
<?php } ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * Locations Module with Dynamic Page Linking
 *
 * Each location can be linked to a dedicated landing page created in the Pages module.
 * These location pages use the "location" page type with a customizable template.
 *
 * How it works:
 * 1. Each location has a page_id field that links to a page record
 * 2. Locations are fetched with their linked pages on the frontend
 * 3. "VIEW HUB" buttons on the public locations page link to these custom pages
 * 4. Location pages display custom hero, courses, career paths, gallery, and contact forms
 *
 * To create pages for new locations:
 * - Add the location via this module
 * - Either manually create a page in the Pages module and link it, OR
 * - The admin can run the generator script: admin/modules/.generate-location-pages-backup.php
 */
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/crud.php';
$page_title = 'Locations';
$active     = 'locations';

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);
$pdo    = db();

// Auto-migrate social columns if they don't exist on the live database
try {
    $pdo->query("SELECT social_facebook FROM locations LIMIT 1");
} catch (Exception $e) {
    try {
        $pdo->exec("ALTER TABLE locations 
            ADD COLUMN social_facebook VARCHAR(255) NULL, 
            ADD COLUMN social_instagram VARCHAR(255) NULL, 
            ADD COLUMN social_youtube VARCHAR(255) NULL, 
            ADD COLUMN social_twitter VARCHAR(255) NULL");
    } catch (Exception $err) {
        // Ignore if already added or permission denied
    }
}

// -------- SAVE --------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    try {
        $editId = (int)($_POST['id'] ?? 0);
        $data = [
            'state'        => trim($_POST['state'] ?? ''),
            'state_slug'   => slugify(trim($_POST['state_slug'] ?: $_POST['state'])),
            'state_order'  => (int)($_POST['state_order'] ?? 0),
            'city'         => trim($_POST['city'] ?? ''),
            'region_label' => trim($_POST['region_label'] ?? ''),
            'address'      => trim($_POST['address'] ?? ''),
            'phone'        => trim($_POST['phone'] ?? ''),
            'email'        => trim($_POST['email'] ?? ''),
            'map_link'     => trim($_POST['map_link'] ?? ''),
            'custom_link'  => trim($_POST['custom_link'] ?? ''),
            'social_facebook'  => trim($_POST['social_facebook'] ?? ''),
            'social_instagram' => trim($_POST['social_instagram'] ?? ''),
            'social_youtube'   => trim($_POST['social_youtube'] ?? ''),
            'social_twitter'   => trim($_POST['social_twitter'] ?? ''),
            'is_featured'  => isset($_POST['is_featured']) ? 1 : 0,
            'sort_order'   => (int)($_POST['sort_order'] ?? 0),
            'status'       => isset($_POST['status']) ? 1 : 0,
        ];
        if ($data['state'] === '' || $data['city'] === '') {
            throw new Exception('State and City are required.');
        }
        if ($editId > 0) {
            $sets=[]; $vals=[];
            foreach ($data as $k=>$v) { $sets[] = "`$k`=?"; $vals[] = $v; }
            $vals[] = $editId;
            $pdo->prepare("UPDATE locations SET " . implode(',', $sets) . " WHERE id=?")->execute($vals);
        } else {
            $cols=array_keys($data);
            $pdo->prepare("INSERT INTO locations (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")
                ->execute(array_values($data));
        }
        log_admin_action('save','locations','Saved location: ' . $data['city']);
        set_flash('success','Location saved.');
    } catch (Throwable $e) { set_flash('error', $e->getMessage()); }
    redirect(ADMIN_URL.'/modules/locations.php');
}

if ($action === 'delete' && $id > 0) { crud_delete('locations', $id); set_flash('success','Location deleted.'); redirect(ADMIN_URL.'/modules/locations.php'); }
if ($action === 'toggle' && $id > 0) { crud_toggle('locations', $id); redirect(ADMIN_URL.'/modules/locations.php'); }
if ($action === 'feature' && $id > 0) { crud_toggle('locations', $id, 'is_featured'); redirect(ADMIN_URL.'/modules/locations.php'); }

include __DIR__ . '/../includes/header.php';

// ---------- ADD / EDIT FORM ----------
if ($action === 'add' || $action === 'edit') {
    $l = [
        'id'=>0,'state'=>'','state_slug'=>'','state_order'=>0,'city'=>'',
        'region_label'=>'Strategic Center','address'=>'','phone'=>'','email'=>'',
        'map_link'=>'','custom_link'=>'','social_facebook'=>'','social_instagram'=>'','social_youtube'=>'','social_twitter'=>'',
        'is_featured'=>0,'sort_order'=>0,'status'=>1,
    ];
    if ($action === 'edit' && $id > 0) {
        $r = crud_fetch('locations', $id);
        if ($r) $l = $r;
    }

    // Existing states for autosuggest
    $existingStates = $pdo->query("SELECT DISTINCT state, state_slug, state_order FROM locations ORDER BY state_order, state")->fetchAll();

    // Get linked page info
    $linkedPage = null;
    if ($action === 'edit' && $id > 0 && !empty($l['page_id'])) {
        $linkedPage = $pdo->query("SELECT id, title, slug FROM pages WHERE id = " . (int)$l['page_id'])->fetch();
    }
?>
<div class="page-header">
    <h4><?= $action==='edit' ? 'Edit Location' : 'Add Location' ?></h4>
    <a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/locations.php"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card"><div class="card-header">Location</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">State *</label>
                        <input name="state" class="form-control" required value="<?= e($l['state']) ?>" list="state-options" placeholder="e.g. Punjab">
                        <datalist id="state-options">
                            <?php foreach($existingStates as $s): ?>
                                <option value="<?= e($s['state']) ?>"><?= e($s['state']) ?></option>
                            <?php endforeach; ?>
                        </datalist>
                        <small class="text-muted">Locations are grouped by state on the public page.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">State Slug</label>
                        <input name="state_slug" class="form-control" value="<?= e($l['state_slug']) ?>" placeholder="auto from state">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">State Order</label>
                        <input type="number" name="state_order" class="form-control" value="<?= (int)$l['state_order'] ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">City *</label>
                        <input name="city" class="form-control" required value="<?= e($l['city']) ?>" placeholder="e.g. Ludhiana">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Region Label</label>
                        <input name="region_label" class="form-control" value="<?= e($l['region_label']) ?>" placeholder="e.g. State Hub / Strategic Center" list="label-options">
                        <datalist id="label-options">
                            <option value="State Hub">
                            <option value="Strategic Center">
                            <option value="Signature Learning Center">
                            <option value="Union Territory Hub">
                            <option value="North Region">
                            <option value="Central Region">
                            <option value="East Region">
                            <option value="Hill Region">
                        </datalist>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input name="address" class="form-control" value="<?= e($l['address']) ?>" placeholder="Optional — only shown on featured cards">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input name="phone" class="form-control" value="<?= e($l['phone']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= e($l['email']) ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Google Maps Link</label>
                        <input type="url" name="map_link" class="form-control" value="<?= e($l['map_link']) ?>" placeholder="https://maps.google.com/?q=...">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Custom Link (VIEW HUB destination)</label>
                        <input type="url" name="custom_link" class="form-control" value="<?= e($l['custom_link']) ?>" placeholder="https://... (leave blank to default to /contact?city=...)">
                        <small class="text-muted">If set, the VIEW HUB button on the public locations page links here instead of the contact form.</small>
                    </div>

                    <div class="col-12 mt-4">
                        <h5>Social Media Links</h5>
                        <p class="text-muted small mb-3">Leave blank to use default site-wide social links in the header.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-facebook" style="color:#1877F2;"></i> Facebook</label>
                                <input type="url" name="social_facebook" class="form-control" value="<?= e($l['social_facebook'] ?? '') ?>" placeholder="https://facebook.com/...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-instagram" style="color:#E4405F;"></i> Instagram</label>
                                <input type="url" name="social_instagram" class="form-control" value="<?= e($l['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-youtube" style="color:#FF0000;"></i> YouTube</label>
                                <input type="url" name="social_youtube" class="form-control" value="<?= e($l['social_youtube'] ?? '') ?>" placeholder="https://youtube.com/...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-twitter-x"></i> Twitter / X</label>
                                <input type="url" name="social_twitter" class="form-control" value="<?= e($l['social_twitter'] ?? '') ?>" placeholder="https://twitter.com/...">
                            </div>
                        </div>
                    </div>
                </div>
            </div></div>
        </div>

        <div class="col-lg-4">
            <?php if ($action === 'edit' && $linkedPage): ?>
            <div class="card mb-3"><div class="card-header">Location Page</div><div class="card-body">
                <p class="small text-muted mb-2">Linked to location landing page:</p>
                <p class="mb-2"><strong><?= e($linkedPage['title']) ?></strong></p>
                <div class="d-flex gap-2">
                    <a href="<?= ADMIN_URL ?>/modules/pages.php?action=edit&id=<?= $linkedPage['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                        <i class="bi bi-pencil"></i> Edit Page
                    </a>
                    <a href="<?= SITE_URL ?>/<?= e($linkedPage['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-box-arrow-up-right"></i> View
                    </a>
                </div>
            </div></div>
            <?php endif; ?>

            <div class="card"><div class="card-header">Display</div><div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Sort Order (within state)</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= (int)$l['sort_order'] ?>">
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" class="form-check-input" id="ft" name="is_featured" value="1" <?= $l['is_featured']?'checked':'' ?>>
                    <label class="form-check-label" for="ft">Featured — show as full detail card (with address &amp; phone)</label>
                </div>
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="st" name="status" value="1" <?= $l['status']?'checked':'' ?>>
                    <label class="form-check-label" for="st">Active</label>
                </div>
            </div></div>

            <button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Save Location</button>
        </div>
    </div>
</form>
<?php } else {
    // ---------- LIST ----------
    $rows = $pdo->query("SELECT * FROM locations ORDER BY state_order, state, sort_order, id")->fetchAll();
    $byState = [];
    foreach ($rows as $r) $byState[$r['state']][] = $r;
?>
<div class="page-header">
    <h4>Locations (<?= count($rows) ?> across <?= count($byState) ?> states)</h4>
    <a class="btn btn-primary" href="?action=add"><i class="bi bi-plus-circle"></i> Add Location</a>
</div>

<?php if (empty($byState)): ?>
    <div class="card"><div class="card-body text-center py-5 text-muted">No locations yet. <a href="?action=add">Add your first location</a>.</div></div>
<?php else: foreach ($byState as $state => $items): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><?= e($state) ?></strong>
            <span class="text-muted small"><?= count($items) ?> location<?= count($items)===1?'':'s' ?></span>
        </div>
        <div class="table-responsive">
        <table class="table mb-0 table-hover">
            <thead>
                <tr>
                    <th>City</th>
                    <th>Region Label</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Page</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th>Order</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $r):
                $page = !empty($r['page_id']) ? $pdo->query("SELECT id, title, slug FROM pages WHERE id = " . (int)$r['page_id'])->fetch() : null;
            ?>
                <tr>
                    <td><strong><?= e($r['city']) ?></strong></td>
                    <td><small class="text-muted"><?= e($r['region_label']) ?></small></td>
                    <td><small><?= e($r['address'] ?: '—') ?></small></td>
                    <td><small><?= e($r['phone'] ?: '—') ?></small></td>
                    <td>
                        <?php if ($page): ?>
                            <a href="<?= ADMIN_URL ?>/modules/pages.php?action=edit&id=<?= $page['id'] ?>" class="btn btn-xs btn-outline-info" style="padding:2px 6px;font-size:11px;">
                                <i class="bi bi-pencil-square"></i> <?= e(substr($page['title'], 0, 20)) ?>
                            </a>
                        <?php else: ?>
                            <span class="badge bg-light text-muted">No page</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?action=feature&id=<?= $r['id'] ?>" class="text-decoration-none">
                            <?= $r['is_featured'] ? '<span class="badge bg-warning text-dark">★ Featured</span>' : '<span class="badge bg-light text-muted">Simple</span>' ?>
                        </a>
                    </td>
                    <td>
                        <a href="?action=toggle&id=<?= $r['id'] ?>" class="text-decoration-none">
                            <?= $r['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                        </a>
                    </td>
                    <td><small><?= (int)$r['sort_order'] ?></small></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
                        <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
<?php endforeach; endif; ?>
<?php } ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>

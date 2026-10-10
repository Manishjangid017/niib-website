<?php
require __DIR__ . '/../includes/auth.php';
$page_title = 'Site Settings';
$active = 'settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    try {
        // Handle file uploads
        foreach (['site_logo' => 'settings', 'site_favicon' => 'settings'] as $field => $folder) {
            if (!empty($_FILES[$field]['name'])) {
                $up = upload_file($field, $folder, ['jpg','jpeg','png','gif','webp','svg','ico']);
                if ($up['ok']) {
                    $_POST[$field] = $up['path'];
                }
            }
        }
        $fields = [
            'site_name','site_tagline','site_email','site_phone','site_whatsapp','site_address',
            'site_logo','site_favicon','footer_text','copyright_text','working_hours','google_maps','maintenance_mode'
        ];
        $stmt = db()->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        foreach ($fields as $f) {
            if (isset($_POST[$f])) $stmt->execute([$f, trim((string)$_POST[$f])]);
        }
        log_admin_action('update', 'settings', 'Updated site settings');
        set_flash('success', 'Settings saved successfully.');
    } catch (Throwable $e) {
        set_flash('error', 'Save failed: ' . $e->getMessage());
    }
    redirect(ADMIN_URL . '/modules/settings.php');
}

$s = get_settings();
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><h4>Site Settings</h4></div>
<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">General Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Website Name</label><input name="site_name" class="form-control" value="<?= e($s['site_name'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Tagline</label><input name="site_tagline" class="form-control" value="<?= e($s['site_tagline'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input name="site_email" type="email" class="form-control" value="<?= e($s['site_email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input name="site_phone" class="form-control" value="<?= e($s['site_phone'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">WhatsApp Number</label><input name="site_whatsapp" class="form-control" value="<?= e($s['site_whatsapp'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Working Hours</label><input name="working_hours" class="form-control" value="<?= e($s['working_hours'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label">Address</label><textarea name="site_address" class="form-control" rows="2"><?= e($s['site_address'] ?? '') ?></textarea></div>
                        <div class="col-12"><label class="form-label">Google Maps Embed</label><textarea name="google_maps" class="form-control" rows="3"><?= e($s['google_maps'] ?? '') ?></textarea></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Footer</div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">Footer Text</label><textarea name="footer_text" class="form-control"><?= e($s['footer_text'] ?? '') ?></textarea></div>
                    <div class="mb-3"><label class="form-label">Copyright Text</label><input name="copyright_text" class="form-control" value="<?= e($s['copyright_text'] ?? '') ?>"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Branding</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <img id="logoPrev" src="<?= e(img_url($s['site_logo'] ?? 'assets/images/logo.svg')) ?>" class="preview-img"><br>
                        <input type="file" name="site_logo" class="form-control mt-2" data-preview="logoPrev" accept="image/*">
                        <input type="hidden" name="site_logo" value="<?= e($s['site_logo'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Favicon</label>
                        <img id="favPrev" src="<?= e(img_url($s['site_favicon'] ?? 'assets/images/favicon.svg')) ?>" class="preview-img" style="max-width:60px">
                        <input type="file" name="site_favicon" class="form-control mt-2" data-preview="favPrev" accept="image/*">
                        <input type="hidden" name="site_favicon" value="<?= e($s['site_favicon'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Maintenance</div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="maintenance_mode" value="0">
                        <input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" id="mm" <?= !empty($s['maintenance_mode']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="mm">Maintenance mode</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Save All Settings</button>
        </div>
    </div>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>

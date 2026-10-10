<?php
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../includes/mailer.php';

$page_title = 'SMTP Settings';
$active     = 'smtp';

$pdo = db();
$testResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    try {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'save') {
            $smtpFields = [
                'smtp_enabled'      => isset($_POST['smtp_enabled']) ? '1' : '0',
                'smtp_host'         => trim($_POST['smtp_host'] ?? ''),
                'smtp_port'         => (string)(int)($_POST['smtp_port'] ?? 587),
                'smtp_encryption'   => in_array($_POST['smtp_encryption'] ?? '', ['none','tls','ssl'], true) ? $_POST['smtp_encryption'] : 'tls',
                'smtp_username'     => trim($_POST['smtp_username'] ?? ''),
                'smtp_from_email'   => trim($_POST['smtp_from_email'] ?? ''),
                'smtp_from_name'    => trim($_POST['smtp_from_name'] ?? ''),
                'smtp_to_admin'     => trim($_POST['smtp_to_admin'] ?? ''),
                'smtp_send_confirmation' => isset($_POST['smtp_send_confirmation']) ? '1' : '0',
                'smtp_timeout'      => (string)max(5, (int)($_POST['smtp_timeout'] ?? 15)),
            ];
            // Only update password if a new one was supplied (don't overwrite with empty)
            if (isset($_POST['smtp_password']) && $_POST['smtp_password'] !== '') {
                $smtpFields['smtp_password'] = $_POST['smtp_password'];
            }

            $stSmtp = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, 'smtp')
                                 ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
            foreach ($smtpFields as $k => $v) $stSmtp->execute([$k, $v]);

            $captchaFields = [
                'recaptcha_enabled'      => isset($_POST['recaptcha_enabled']) ? '1' : '0',
                'recaptcha_mode'         => in_array($_POST['recaptcha_mode'] ?? 'v3', ['v2','v3'], true) ? $_POST['recaptcha_mode'] : 'v3',
                'recaptcha_site_key'     => trim($_POST['recaptcha_site_key'] ?? ''),
                'recaptcha_v3_threshold' => (string)max(0.0, min(1.0, (float)($_POST['recaptcha_v3_threshold'] ?? 0.5))),
                'recaptcha_v3_action'    => preg_replace('/[^a-z0-9_]/i', '', $_POST['recaptcha_v3_action'] ?? 'submit') ?: 'submit',
            ];
            if (isset($_POST['recaptcha_secret_key']) && $_POST['recaptcha_secret_key'] !== '') {
                $captchaFields['recaptcha_secret_key'] = trim($_POST['recaptcha_secret_key']);
            }
            $stCap = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, 'recaptcha')
                                 ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
            foreach ($captchaFields as $k => $v) $stCap->execute([$k, $v]);

            log_admin_action('save', 'smtp', 'Updated SMTP & reCAPTCHA settings');
            set_flash('success', 'Settings saved.');
            redirect(ADMIN_URL.'/modules/smtp.php');
        }

        if ($action === 'test') {
            $to = trim($_POST['test_to'] ?? '');
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) throw new Exception('Enter a valid test email address.');

            $html = niib_mail_template(
                'SMTP Test',
                'This is a test email from your NIIB Institute admin panel.',
                [
                    ['label' => 'Sent at',     'value' => date('d M Y, H:i')],
                    ['label' => 'From',        'value' => setting('smtp_from_email', setting('site_email','-'))],
                    ['label' => 'SMTP host',   'value' => setting('smtp_host')],
                    ['label' => 'Encryption',  'value' => strtoupper(setting('smtp_encryption','tls'))],
                ],
                '<p>If you received this email, your SMTP configuration is working correctly. Frontend forms will now deliver enquiries to <strong>' . htmlspecialchars(niib_mail_to_admin(), ENT_QUOTES) . '</strong>.</p>',
                'Visit Website',
                SITE_URL
            );
            $res = niib_mail_send(['email' => $to, 'name' => 'Test Recipient'], 'NIIB SMTP test email', $html);
            $testResult = $res;
            if ($res['ok']) {
                set_flash('success', 'Test email sent successfully to ' . $to);
            } else {
                set_flash('error', 'Test send failed: ' . $res['error']);
            }
        }
    } catch (Throwable $e) {
        set_flash('error', $e->getMessage());
    }
}

include __DIR__ . '/../includes/header.php';

$cfg = niib_mail_config();
$pwHas = setting('smtp_password', '') !== '';
$capCfg = niib_recaptcha_config();
$capSecretHas = setting('recaptcha_secret_key', '') !== '';
?>

<div class="page-header">
    <h4>SMTP Settings</h4>
    <span class="badge bg-<?= $cfg['smtp_enabled'] ? 'success' : 'secondary' ?>">
        <i class="bi bi-<?= $cfg['smtp_enabled'] ? 'check-circle' : 'pause-circle' ?>"></i>
        <?= $cfg['smtp_enabled'] ? 'Enabled' : 'Disabled' ?>
    </span>
</div>

<!-- Two separate top-level forms (no nesting!). Save form fields live in the left column,
     its submit button uses the HTML5 form="smtpSaveForm" attribute to link from the right column. -->

<form method="post" id="smtpSaveForm">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
</form>
<form method="post" id="smtpTestForm">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="test">
</form>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">SMTP Server</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Host</label>
                        <input form="smtpSaveForm" name="smtp_host" class="form-control" value="<?= e($cfg['smtp_host']) ?>" placeholder="smtp.gmail.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Port</label>
                        <input form="smtpSaveForm" type="number" name="smtp_port" class="form-control" value="<?= (int)$cfg['smtp_port'] ?>" placeholder="587">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Encryption</label>
                        <select form="smtpSaveForm" name="smtp_encryption" class="form-select">
                            <option value="tls"  <?= $cfg['smtp_encryption']==='tls'?'selected':'' ?>>TLS (STARTTLS — port 587)</option>
                            <option value="ssl"  <?= $cfg['smtp_encryption']==='ssl'?'selected':'' ?>>SSL (port 465)</option>
                            <option value="none" <?= $cfg['smtp_encryption']==='none'?'selected':'' ?>>None (port 25, not recommended)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Timeout (sec)</label>
                        <input form="smtpSaveForm" type="number" name="smtp_timeout" class="form-control" value="<?= (int)$cfg['smtp_timeout'] ?>" min="5" max="60">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input form="smtpSaveForm" class="form-check-input" type="checkbox" id="smtp_enabled" name="smtp_enabled" value="1" <?= $cfg['smtp_enabled'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="smtp_enabled">Use SMTP for outgoing mail</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Authentication</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input form="smtpSaveForm" name="smtp_username" class="form-control" value="<?= e($cfg['smtp_username']) ?>" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password / App Password</label>
                        <input form="smtpSaveForm" type="password" name="smtp_password" class="form-control" value="" placeholder="<?= $pwHas ? '••••••••  (leave blank to keep current)' : 'Enter password or Gmail App Password' ?>" autocomplete="new-password">
                        <small class="text-muted">For Gmail, generate an App Password &mdash; regular passwords are blocked.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Google reCAPTCHA <small class="text-muted">— spam protection for all public forms</small></span>
                <span class="badge bg-<?= $capCfg['enabled'] && $capCfg['site_key'] && ($capSecretHas || $capCfg['secret_key']) ? 'success' : 'secondary' ?>">
                    <?= $capCfg['enabled'] && $capCfg['site_key'] && ($capSecretHas || $capCfg['secret_key']) ? 'Active ('.strtoupper($capCfg['mode']).')' : 'Inactive' ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input form="smtpSaveForm" class="form-check-input" type="checkbox" id="recaptcha_enabled" name="recaptcha_enabled" value="1" <?= $capCfg['enabled'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="recaptcha_enabled">Enable reCAPTCHA</label>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Mode</label>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="form-check">
                                <input form="smtpSaveForm" class="form-check-input" type="radio" name="recaptcha_mode" id="rcmode_v3" value="v3" <?= $capCfg['mode']==='v3'?'checked':'' ?>>
                                <label class="form-check-label" for="rcmode_v3"><strong>Score based (v3)</strong> &mdash; <small class="text-muted">verify with a score, no challenge</small></label>
                            </div>
                            <div class="form-check">
                                <input form="smtpSaveForm" class="form-check-input" type="radio" name="recaptcha_mode" id="rcmode_v2" value="v2" <?= $capCfg['mode']==='v2'?'checked':'' ?>>
                                <label class="form-check-label" for="rcmode_v2"><strong>Challenge (v2)</strong> &mdash; <small class="text-muted">"I'm not a robot" checkbox</small></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site Key</label>
                        <input form="smtpSaveForm" name="recaptcha_site_key" class="form-control" value="<?= e($capCfg['site_key']) ?>" placeholder="6Lc...site key from Google" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Secret Key</label>
                        <input form="smtpSaveForm" type="password" name="recaptcha_secret_key" class="form-control" value="" placeholder="<?= $capSecretHas ? '••••••••  (leave blank to keep current)' : '6Lc...secret key from Google' ?>" autocomplete="new-password">
                    </div>
                    <div class="col-md-6 recaptcha-v3-only">
                        <label class="form-label">v3 Score Threshold <small class="text-muted">(0.0 = lax · 1.0 = strict)</small></label>
                        <input form="smtpSaveForm" type="number" name="recaptcha_v3_threshold" class="form-control" min="0" max="1" step="0.1" value="<?= e(number_format($capCfg['v3_threshold'], 1)) ?>">
                        <small class="text-muted">Google recommends 0.5. Lower allows more submissions through.</small>
                    </div>
                    <div class="col-md-6 recaptcha-v3-only">
                        <label class="form-label">v3 Action Name</label>
                        <input form="smtpSaveForm" name="recaptcha_v3_action" class="form-control" value="<?= e($capCfg['v3_action']) ?>" placeholder="submit">
                        <small class="text-muted">Lowercase letters / digits / underscore only. Used for analytics in the Google admin console.</small>
                    </div>
                </div>
                <div class="alert alert-light border mt-3 mb-0 small">
                    <i class="bi bi-info-circle"></i>
                    Get your keys at <a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener">google.com/recaptcha/admin</a>.
                    Choose <strong>reCAPTCHA v3</strong> for score-based, or <strong>reCAPTCHA v2 → "I'm not a robot" Checkbox</strong> for challenge mode.
                    Both modes apply automatically to: contact, course enquiry, location enquiry, franchise, homepage lead and newsletter forms.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Sender &amp; Routing</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">From email <span class="text-danger">*</span></label>
                        <input form="smtpSaveForm" type="email" name="smtp_from_email" class="form-control" value="<?= e($cfg['smtp_from_email']) ?>" placeholder="admissions@yourdomain.com" required>
                        <small class="text-muted">Reply-To headers will use the form submitter's email automatically.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From name</label>
                        <input form="smtpSaveForm" name="smtp_from_name" class="form-control" value="<?= e($cfg['smtp_from_name']) ?>" placeholder="NIIB Institute">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Enquiries delivered to <span class="text-danger">*</span></label>
                        <input form="smtpSaveForm" type="email" name="smtp_to_admin" class="form-control" value="<?= e($cfg['smtp_to_admin']) ?>" placeholder="admin@yourdomain.com" required>
                        <small class="text-muted">All contact / enquiry / franchise / newsletter notifications land here.</small>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input form="smtpSaveForm" class="form-check-input" type="checkbox" id="conf" name="smtp_send_confirmation" value="1" <?= $cfg['smtp_send_confirmation'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="conf">Auto-send "thanks" email to user</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Save</div>
            <div class="card-body">
                <p class="text-muted small">Form submissions on the public site (contact, course enquiry, franchise, newsletter) will deliver via these SMTP settings.</p>
                <button type="submit" form="smtpSaveForm" class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Save SMTP Settings</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Send Test Email</div>
            <div class="card-body">
                <p class="text-muted small">Sends a branded test email using the values currently saved. Save settings first if you've changed anything.</p>
                <div class="mb-2">
                    <input form="smtpTestForm" type="email" name="test_to" class="form-control" placeholder="recipient@example.com" required>
                </div>
                <button type="submit" form="smtpTestForm" class="btn btn-outline-primary w-100"><i class="bi bi-send"></i> Send test email</button>
            </div>
        </div>

        <?php if ($testResult): ?>
        <div class="card">
            <div class="card-header"><?= $testResult['ok'] ? '✅ Test result' : '❌ Test result' ?></div>
            <div class="card-body">
                <?php if ($testResult['ok']): ?>
                    <p class="text-success small mb-0">Email accepted by the SMTP server.</p>
                <?php else: ?>
                    <p class="text-danger small"><?= e($testResult['error']) ?></p>
                <?php endif; ?>
                <?php if (!empty($testResult['log'])): ?>
                    <details open>
                        <summary class="small text-muted" style="cursor:pointer">SMTP conversation log</summary>
                        <pre class="bg-dark text-light p-2 small" style="font-size:11px;max-height:300px;overflow:auto;border-radius:4px;"><?= e(implode("\n", $testResult['log'])) ?></pre>
                    </details>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">Common Presets</div>
            <div class="card-body small">
                <strong>Gmail</strong><br>
                Host: smtp.gmail.com · Port: 587 · TLS<br>
                <em>Use a Google App Password.</em>
                <hr>
                <strong>Office 365 / Outlook</strong><br>
                Host: smtp.office365.com · Port: 587 · TLS
                <hr>
                <strong>cPanel / hosting</strong><br>
                Host: mail.yourdomain.com · Port: 465 · SSL
                <hr>
                <strong>Mailgun</strong><br>
                Host: smtp.mailgun.org · Port: 587 · TLS
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    function syncMode(){
        var isV3 = document.getElementById('rcmode_v3').checked;
        document.querySelectorAll('.recaptcha-v3-only').forEach(function(el){
            el.style.display = isV3 ? '' : 'none';
        });
    }
    var v2 = document.getElementById('rcmode_v2');
    var v3 = document.getElementById('rcmode_v3');
    if (v2 && v3) {
        v2.addEventListener('change', syncMode);
        v3.addEventListener('change', syncMode);
        syncMode();
    }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

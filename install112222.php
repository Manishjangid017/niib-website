<?php
/**
 * NIIB Institute - Installer
 * Run once: http://localhost/niibinstitute/install.php
 * Delete this file after successful installation.
 */

define('NIIB_INIT', true);
require __DIR__ . '/config/database.php';

$messages = [];
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'install') {
        try {
            // Step 1: connect to MySQL server (no DB) and create database
            $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Step 2: reconnect with database selected
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $sql = file_get_contents(__DIR__ . '/database/niibinstitute.sql');
            if (!$sql) throw new Exception('SQL file not found at /database/niibinstitute.sql');

            // Strip CREATE DATABASE / USE / comments (handled above)
            $sql = preg_replace('~^\s*--.*$~m', '', $sql);
            $sql = preg_replace('~^\s*CREATE\s+DATABASE.*?;~im', '', $sql);
            $sql = preg_replace('~^\s*USE\s+`?\w+`?\s*;~im', '', $sql);

            // Execute statement by statement
            $statements = preg_split('/;\s*[\r\n]+/', $sql);
            $count = 0;
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '') continue;
                $pdo->exec($stmt);
                $count++;
            }

            // Update admin password with a fresh hash
            $hash = password_hash('admin123', PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE admin_users SET password = ? WHERE email = 'admin@admin.com'");
            $upd->execute([$hash]);

            $messages[] = "Database installed successfully. {$count} statements executed.";
            $messages[] = "Admin login: admin@admin.com / admin123";
            $messages[] = "<strong>IMPORTANT: Delete install.php now!</strong>";
        } catch (Throwable $e) {
            $errors[] = 'Install failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>NIIB Installer</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f1f5f9}.card{max-width:600px;margin:60px auto;box-shadow:0 6px 30px rgba(0,0,0,.1)}</style>
</head>
<body>
<div class="card">
    <div class="card-body p-4">
        <h3>NIIB Institute Installer</h3>
        <p class="text-muted">This will create the database <code><?= htmlspecialchars(DB_NAME) ?></code> and all tables.</p>

        <?php foreach ($messages as $m): ?>
            <div class="alert alert-success"><?= $m ?></div>
        <?php endforeach; ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>

        <ul class="list-unstyled small">
            <li><strong>Host:</strong> <?= htmlspecialchars(DB_HOST) ?></li>
            <li><strong>Database:</strong> <?= htmlspecialchars(DB_NAME) ?></li>
            <li><strong>User:</strong> <?= htmlspecialchars(DB_USER) ?></li>
        </ul>

        <form method="post">
            <input type="hidden" name="action" value="install">
            <button type="submit" class="btn btn-primary">Run Installation</button>
            <a href="admin/" class="btn btn-outline-secondary">Go to Admin</a>
            <a href="index.php" class="btn btn-outline-secondary">Go to Website</a>
        </form>
    </div>
</div>
</body>
</html>

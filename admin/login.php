<?php
if (session_status() === PHP_SESSION_NONE) session_start();
define('NIIB_INIT', true);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid security token. Refresh and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!valid_email($email) || strlen($password) < 4) {
            $error = 'Please enter valid credentials.';
        } else {
            try {
                $stmt = db()->prepare("SELECT * FROM admin_users WHERE email = ? AND status = 1 LIMIT 1");
                $stmt->execute([$email]);
                $admin = $stmt->fetch();
                if ($admin && password_verify($password, $admin['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_last_activity'] = time();
                    db()->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);
                    log_admin_action('login', 'auth', 'Admin logged in');
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid email or password.';
                }
            } catch (Throwable $e) {
                $error = 'Database error. Run install.php first.';
            }
        }
    }
}

$timeout = !empty($_GET['timeout']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login - <?= e(setting('site_name','NIIB Institute')) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,wght@0,400..900;1,400..900&family=Manrope:wght@300..800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="icon" href="<?= e(img_url(setting('site_favicon','assets/images/favicon.svg'))) ?>">
<style>
:root{
    --primary:#170338;
    --primary-container:#2d1b4e;
    --secondary-container:#fe5e1e;
    --secondary:#ab3600;
    --secondary-fixed:#ffdbcf;
    --background:#fcf9f8;
    --surface-container-low:#f6f3f2;
    --on-surface:#1c1b1b;
    --on-surface-variant:#49454e;
    --outline-variant:#cbc4d0;
    --primary-fixed-dim:#d2bcfa;
    --font-headline:'Bodoni Moda', Georgia, serif;
    --font-body:'Manrope', system-ui, -apple-system, sans-serif;
}
*{box-sizing:border-box}
body{
    margin:0;
    font-family:var(--font-body);
    color:var(--on-surface);
    background:var(--background);
    min-height:100vh;
    display:flex;
    -webkit-font-smoothing:antialiased;
}

/* Left panel — branded editorial */
.login-brand{
    flex:1;
    background:var(--primary);
    color:#fff;
    position:relative;
    overflow:hidden;
    padding:60px 80px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
}
.login-brand::before{
    content:'';
    position:absolute;
    top:-20%;right:-20%;
    width:600px;height:600px;
    border-radius:50%;
    background:radial-gradient(circle, rgba(254,94,30,.18) 0%, transparent 70%);
    pointer-events:none;
}
.login-brand::after{
    content:'';
    position:absolute;
    bottom:-30%;left:-10%;
    width:500px;height:500px;
    border-radius:50%;
    background:radial-gradient(circle, rgba(210,188,250,.12) 0%, transparent 70%);
    pointer-events:none;
}
.brand-top{position:relative;z-index:2;}
.brand-eyebrow{
    font-size:13px;
    letter-spacing:.3em;
    text-transform:uppercase;
    font-weight:700;
    color:var(--secondary-fixed);
    margin-bottom:24px;
}
.brand-title{
    font-family:var(--font-headline);
    font-size:clamp(36px, 4vw, 64px);
    font-weight:700;
    letter-spacing:-0.02em;
    line-height:1.05;
    margin:0 0 24px;
}
.brand-title em{
    font-style:italic;
    color:var(--primary-fixed-dim);
}
.brand-subtitle{
    color:rgba(255,255,255,.75);
    font-size:17px;
    line-height:1.6;
    max-width:440px;
    margin:0;
}
.brand-bottom{position:relative;z-index:2;}
.brand-stats{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:24px;
    margin-bottom:32px;
}
.brand-stat{
    border-left:1px solid rgba(151,131,189,.4);
    padding-left:18px;
}
.brand-stat .n{
    font-family:var(--font-headline);
    font-size:32px;
    line-height:1;
    margin-bottom:6px;
    font-weight:600;
}
.brand-stat .l{
    font-size:11px;
    letter-spacing:.15em;
    text-transform:uppercase;
    color:rgba(255,255,255,.55);
    font-weight:700;
}
.brand-bottom .meta{
    font-size:12px;
    letter-spacing:.15em;
    text-transform:uppercase;
    color:rgba(235,221,255,.45);
}

/* Right panel — login card */
.login-panel{
    width:520px;
    padding:60px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    background:var(--background);
}
.login-head{margin-bottom:40px;}
.login-eyebrow{
    font-size:12px;
    letter-spacing:.3em;
    text-transform:uppercase;
    color:var(--secondary);
    font-weight:700;
    margin-bottom:12px;
}
.login-head h2{
    font-family:var(--font-headline);
    font-size:42px;
    font-weight:600;
    margin:0 0 12px;
    color:var(--primary);
    letter-spacing:-0.01em;
}
.login-head p{
    color:var(--on-surface-variant);
    font-size:15px;
    margin:0;
}

/* Alerts */
.alert{
    padding:14px 18px;
    border-left:3px solid;
    margin-bottom:24px;
    font-size:14px;
    background:rgba(186,26,26,.06);
    border-color:var(--secondary-container);
    color:#93000a;
}
.alert.warning{
    background:rgba(254,94,30,.07);
    color:var(--secondary);
}

/* Form */
.form-group{margin-bottom:20px;position:relative;}
.form-group label{
    display:block;
    font-size:12px;
    font-weight:700;
    letter-spacing:.12em;
    text-transform:uppercase;
    color:var(--primary);
    margin-bottom:8px;
}
.form-group input{
    width:100%;
    padding:14px 16px 14px 46px;
    font-family:var(--font-body);
    font-size:15px;
    color:var(--on-surface);
    background:var(--surface-container-low);
    border:1px solid var(--outline-variant);
    border-radius:2px;
    transition:all .2s;
}
.form-group input:focus{
    outline:none;
    border-color:var(--primary);
    background:#fff;
    box-shadow:0 0 0 3px rgba(23,3,56,.08);
}
.form-group .icon{
    position:absolute;
    top:38px;
    left:16px;
    color:var(--on-surface-variant);
    font-size:18px;
}

/* Button */
.btn-submit{
    width:100%;
    background:var(--secondary-container);
    color:#fff;
    border:none;
    padding:18px;
    font-family:var(--font-body);
    font-size:14px;
    font-weight:700;
    letter-spacing:.18em;
    text-transform:uppercase;
    cursor:pointer;
    border-radius:2px;
    transition:all .2s ease;
    box-shadow:0 8px 20px rgba(254,94,30,.25);
    margin-top:8px;
}
.btn-submit:hover{
    background:var(--secondary);
    transform:translateY(-2px);
    box-shadow:0 14px 28px rgba(254,94,30,.32);
}
.btn-submit i{margin-right:8px;}

.login-foot{
    margin-top:32px;
    padding-top:24px;
    border-top:1px solid var(--outline-variant);
    text-align:center;
}
.login-foot .demo{
    font-size:12px;
    letter-spacing:.1em;
    text-transform:uppercase;
    color:var(--on-surface-variant);
    margin-bottom:8px;
    font-weight:700;
}
.login-foot .creds{
    font-family:var(--font-headline);
    font-style:italic;
    color:var(--primary);
    font-size:15px;
}
.login-foot .home-link{
    display:inline-block;
    margin-top:18px;
    font-size:12px;
    letter-spacing:.15em;
    text-transform:uppercase;
    color:var(--on-surface-variant);
    text-decoration:none;
    font-weight:700;
    border-bottom:1px solid var(--outline-variant);
    padding-bottom:2px;
    transition:all .2s;
}
.login-foot .home-link:hover{
    color:var(--primary);
    border-color:var(--primary);
}

/* Responsive */
@media (max-width: 992px){
    body{flex-direction:column;}
    .login-brand{padding:50px 32px;min-height:300px;}
    .login-brand .brand-bottom{display:none;}
    .login-panel{width:100%;padding:48px 32px;}
}
@media (max-width: 480px){
    .login-brand{padding:40px 24px;}
    .login-panel{padding:40px 24px;}
    .brand-title{font-size:34px;}
    .login-head h2{font-size:32px;}
}
</style>
</head>
<body>

<!-- LEFT: Brand panel -->
<aside class="login-brand">
    <div class="brand-top">
        <div class="brand-eyebrow"><?= e(setting('site_name','NIIB Institute')) ?> · Admin</div>
        <h1 class="brand-title">Mastering the<br><em>Art &amp; Science</em><br>of Beauty.</h1>
        <p class="brand-subtitle">Manage courses, enquiries, content and SEO from one prestigious command centre.</p>
    </div>

    <div class="brand-bottom">
        <div class="brand-stats">
            <div class="brand-stat">
                <div class="n">50k+</div>
                <div class="l">Alumni Worldwide</div>
            </div>
            <div class="brand-stat">
                <div class="n">25+</div>
                <div class="l">Global Campuses</div>
            </div>
            <div class="brand-stat">
                <div class="n">CIDESCO</div>
                <div class="l">Accreditation</div>
            </div>
            <div class="brand-stat">
                <div class="n">20+</div>
                <div class="l">Years of Excellence</div>
            </div>
        </div>
        <p class="meta">© <?= date('Y') ?> · Editorial Clinical Education</p>
    </div>
</aside>

<!-- RIGHT: Login panel -->
<main class="login-panel">
    <div class="login-head">
        <div class="login-eyebrow">Secure Access</div>
        <h2>Welcome back.</h2>
        <p>Sign in to your administrative dashboard.</p>
    </div>

    <?php if ($timeout): ?>
        <div class="alert warning"><i class="bi bi-clock-history"></i> Your session has timed out. Please sign in again.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email">Email Address</label>
            <i class="bi bi-envelope-fill icon"></i>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <i class="bi bi-lock-fill icon"></i>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn-submit">
            <i class="bi bi-arrow-right-circle-fill"></i> Sign In to Dashboard
        </button>
    </form>

    <div class="login-foot">
        <a href="<?= SITE_URL ?>" class="home-link"><i class="bi bi-arrow-left"></i> Back to website</a>
    </div>
</main>

</body>
</html>

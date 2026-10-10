<?php
require __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'message'=>'Invalid request']); exit; }

try {
    $email = trim($_POST['email'] ?? '');
    if (!valid_email($email)) { echo json_encode(['ok'=>false,'message'=>'Enter a valid email.']); exit; }
    $cap = niib_recaptcha_verify($_POST['g-recaptcha-response'] ?? '');
    if (!$cap['ok']) { echo json_encode(['ok'=>false,'message'=>$cap['error']]); exit; }

    $stmt = db()->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE status=1");
    $stmt->execute([$email]);

    // Notify admin (no user confirmation by default — keeps inbox quiet for sub-only)
    $adminTo = niib_mail_to_admin();
    if ($adminTo) {
        $html = niib_mail_template(
            'New Newsletter Subscriber',
            'A visitor has joined your newsletter list.',
            [
                ['label' => 'Email', 'value' => $email],
                ['label' => 'Time',  'value' => date('d M Y, H:i')],
            ]
        );
        niib_mail_send($adminTo, '[NIIB] New newsletter subscriber', $html);
    }

    // Welcome email to subscriber
    $welcome = niib_mail_template(
        'Welcome to the NIIB Journal',
        'Thanks for joining! You\'ll receive weekly insights, course launches and exclusive offers from our master educators.',
        [],
        '<p>Get started by exploring our latest articles and signature courses.</p>',
        'Read the Journal',
        SITE_URL . '/blog'
    );
    niib_mail_send(['email' => $email], 'Welcome to NIIB Institute', $welcome);

    echo json_encode(['ok'=>true,'message'=>'Subscribed!']);
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'message'=>'Something went wrong.']);
}

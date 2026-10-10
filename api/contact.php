<?php
require __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false,'message'=>'Invalid request']); exit;
}

try {
    $name = clean($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $city = clean($_POST['city'] ?? '');
    $subject = clean($_POST['subject'] ?? '');
    $message = clean($_POST['message'] ?? '');
    if ($name === '' || !valid_email($email) || $message === '') {
        echo json_encode(['ok'=>false,'message'=>'Please fill all required fields with valid data.']); exit;
    }
    $cap = niib_recaptcha_verify($_POST['g-recaptcha-response'] ?? '');
    if (!$cap['ok']) { echo json_encode(['ok'=>false,'message'=>$cap['error']]); exit; }
    // basic spam guard: max 5 per IP per hour
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $count = db()->prepare("SELECT COUNT(*) FROM contact_messages WHERE ip_address=? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $count->execute([$ip]);
    if ((int)$count->fetchColumn() > 5) {
        echo json_encode(['ok'=>false,'message'=>'Too many submissions. Please try again later.']); exit;
    }
    $stmt = db()->prepare("INSERT INTO contact_messages (name,email,phone,city,subject,message,ip_address) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$name,$email,$phone,$city,$subject,$message,$ip]);

    // Send emails: admin notification + user confirmation
    niib_mail_notify_admin(
        'New Contact Message',
        'A new contact message has been submitted on the website. Reply directly to this email to reach ' . $name . '.',
        [
            ['label' => 'Name',    'value' => $name],
            ['label' => 'Email',   'value' => $email],
            ['label' => 'Phone',   'value' => $phone ?: '—'],
            ['label' => 'City',    'value' => $city ?: '—'],
            ['label' => 'Subject', 'value' => $subject ?: '—'],
            ['label' => 'Message', 'value' => $message],
        ],
        $email,   // reply-to
        $email    // user confirmation
    );

    echo json_encode(['ok'=>true,'message'=>'Thank you! Your message has been sent. We will get back to you shortly.']);
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'message'=>'Sorry, something went wrong. Try again.']);
}

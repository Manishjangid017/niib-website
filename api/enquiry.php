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
    $message = clean($_POST['message'] ?? '');
    $course_id = (int)($_POST['course_id'] ?? 0) ?: null;
    $course_name = clean($_POST['course_name'] ?? '');

    if ($name === '' || !valid_email($email)) {
        echo json_encode(['ok'=>false,'message'=>'Please enter your name and a valid email.']); exit;
    }
    $cap = niib_recaptcha_verify($_POST['g-recaptcha-response'] ?? '');
    if (!$cap['ok']) { echo json_encode(['ok'=>false,'message'=>$cap['error']]); exit; }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $count = db()->prepare("SELECT COUNT(*) FROM enquiry_forms WHERE ip_address=? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $count->execute([$ip]);
    if ((int)$count->fetchColumn() > 5) {
        echo json_encode(['ok'=>false,'message'=>'Too many submissions. Try again later.']); exit;
    }

    $source = clean($_POST['source'] ?? 'website');
    $stmt = db()->prepare("INSERT INTO enquiry_forms (name,email,phone,city,course_id,course_name,message,source,ip_address) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$name,$email,$phone,$city,$course_id,$course_name,$message,$source,$ip]);

    $isFranchise = stripos($source, 'franchise') !== false;
    $subjectAdmin = $isFranchise ? 'New Franchise Enquiry' : 'New Course Enquiry';
    $intro = $isFranchise
        ? 'A new franchise partnership enquiry has been submitted. Reply directly to this email to reach ' . $name . '.'
        : 'A new course enquiry has been submitted. Reply directly to this email to reach ' . $name . '.';

    niib_mail_notify_admin(
        $subjectAdmin,
        $intro,
        [
            ['label' => 'Name',    'value' => $name],
            ['label' => 'Email',   'value' => $email],
            ['label' => 'Phone',   'value' => $phone ?: '—'],
            ['label' => 'City',    'value' => $city ?: '—'],
            ['label' => 'Course',  'value' => $course_name ?: '—'],
            ['label' => 'Source',  'value' => $source],
            ['label' => 'Message', 'value' => $message ?: '—'],
        ],
        $email,
        $email
    );

    echo json_encode(['ok'=>true,'message'=>'Thank you! We will contact you soon.']);
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'message'=>'Sorry, something went wrong. Try again.']);
}

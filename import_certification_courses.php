<?php
/**
 * One-shot importer — Certification courses from the NIIB PDF.
 *
 * What it does:
 *   • Creates beauty categories (idempotent).
 *   • Upserts 4 courses (Self Grooming, Eyelash Extension, Cosmetology, Russian Hair Do).
 *   • If the course already exists (matched by slug), only updates content + meta.
 *   • Image field stays editable — admin uploads PDF visuals via the Courses module.
 *
 * Visit: http://localhost/niibinstitute/import_certification_courses.php?run=1
 * After it reports success, DELETE THIS FILE.
 */

require __DIR__ . '/includes/init.php';
$pdo = db();

// Tiny safety: require ?run=1 (or CLI) so casual visitors don't trigger it
$is_cli = (php_sapi_name() === 'cli');
if (!$is_cli && ($_GET['run'] ?? '') !== '1') {
    echo '<pre style="font-family:system-ui;padding:24px;background:#f6f3f2;color:#170338">';
    echo "NIIB — Certification Course Importer\n";
    echo "------------------------------------\n";
    echo "Append ?run=1 to the URL to execute.\n";
    echo "Delete this file after running.\n";
    echo '</pre>';
    exit;
}

/* ------------------------------------------------------------
 * 1. Ensure required beauty categories exist
 * ------------------------------------------------------------ */
function ensure_category(PDO $pdo, string $name, string $slug, string $icon = 'bi-stars'): int {
    $st = $pdo->prepare("SELECT id FROM course_categories WHERE slug = ?");
    $st->execute([$slug]);
    $row = $st->fetch();
    if ($row) return (int)$row['id'];
    $pdo->prepare("INSERT INTO course_categories (name, slug, description, icon, status) VALUES (?,?,?,?,1)")
        ->execute([$name, $slug, $name . ' programs at NIIB Institute', $icon]);
    return (int)$pdo->lastInsertId();
}

$cat_grooming   = ensure_category($pdo, 'Beauty Foundation',  'beauty-foundation',  'bi-stars');
$cat_lash       = ensure_category($pdo, 'Eyelash & Brows',    'eyelash-brows',      'bi-eye');
$cat_cosmetology= ensure_category($pdo, 'Cosmetology',        'cosmetology',        'bi-flower3');
$cat_hair       = ensure_category($pdo, 'Hair Styling',       'hair-styling',       'bi-scissors');

/* ------------------------------------------------------------
 * 2. Shared HTML helpers — beautiful formatting
 * ------------------------------------------------------------ */
function lp_meta_strip(array $items): string {
    $html = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin:24px 0 32px;padding:18px;background:linear-gradient(135deg,#fff,#f6f3f2);border:1px solid #cbc4d0;border-radius:8px">';
    foreach ($items as $label => $value) {
        $html .= '<div style="display:flex;flex-direction:column;gap:2px">'
              .  '<span style="font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#49454e">' . htmlspecialchars($label) . '</span>'
              .  '<strong style="font-size:15px;color:#170338">' . htmlspecialchars($value) . '</strong>'
              .  '</div>';
    }
    return $html . '</div>';
}

function lp_section(string $eyebrow, string $title, string $body): string {
    return '<section style="margin:48px 0">'
        . '<span style="display:inline-block;font-size:12px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:#fe5e1e;margin-bottom:10px">' . htmlspecialchars($eyebrow) . '</span>'
        . '<h2 style="font-family:\'Bodoni Moda\',Georgia,serif;font-size:clamp(26px,3.4vw,36px);margin:0 0 16px;color:#170338">' . htmlspecialchars($title) . '</h2>'
        . $body
        . '</section>';
}

function lp_highlights(array $items): string {
    $html = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;margin-top:16px">';
    foreach ($items as $title => $desc) {
        $html .= '<div style="padding:18px;background:#fcf9f8;border-left:4px solid #fe5e1e;border-radius:0 8px 8px 0">'
              .  '<strong style="display:block;font-size:15px;color:#170338;margin-bottom:6px">' . htmlspecialchars($title) . '</strong>'
              .  '<span style="font-size:14px;color:#49454e;line-height:1.55">' . htmlspecialchars($desc) . '</span>'
              .  '</div>';
    }
    return $html . '</div>';
}

function lp_modules(array $modules): string {
    $html = '<div class="cm-list" style="display:flex;flex-direction:column;gap:10px;margin-top:14px">';
    $i = 1;
    foreach ($modules as $title => $points) {
        $num = sprintf('%02d', $i++);
        $open = ($i === 2) ? ' open' : ''; // first module open by default
        $html .= '<details class="cm-item"' . $open . ' style="background:#fff;border:1px solid #cbc4d0;border-radius:10px;overflow:hidden">'
              .  '<summary class="cm-sum" style="display:flex;align-items:center;gap:14px;padding:18px 20px;cursor:pointer;list-style:none">'
              .  '<span class="cm-num" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:#170338;color:#fff;font-weight:700;font-size:13px;border-radius:50%;flex:0 0 36px">' . $num . '</span>'
              .  '<strong style="font-size:16px;color:#170338;flex:1">' . htmlspecialchars($title) . '</strong>'
              .  '<span class="cm-chev" aria-hidden="true" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;background:#fcf9f8;color:#fe5e1e;border-radius:50%;font-size:18px;font-weight:300;line-height:1;transition:transform .25s">+</span>'
              .  '</summary>'
              .  '<ul style="margin:0;padding:0 20px 18px 84px;color:#49454e;line-height:1.7">';
        foreach ($points as $p) $html .= '<li>' . htmlspecialchars($p) . '</li>';
        $html .= '</ul></details>';
    }
    return $html . '</div>';
}

function lp_chip_list(array $items): string {
    $html = '<div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:12px">';
    foreach ($items as $it) {
        $html .= '<span style="padding:8px 16px;background:#ffdbcf;color:#832700;font-size:13px;font-weight:600;border-radius:999px">' . htmlspecialchars($it) . '</span>';
    }
    return $html . '</div>';
}

function lp_steps(array $steps): string {
    $html = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-top:16px">';
    $n = 1;
    foreach ($steps as $title => $desc) {
        $html .= '<div style="padding:18px;background:linear-gradient(135deg,#170338,#2d1b4e);color:#fff;border-radius:10px;position:relative">'
              .  '<span style="position:absolute;top:12px;right:14px;font-family:\'Bodoni Moda\',serif;font-size:36px;font-weight:700;color:rgba(255,255,255,.18)">' . $n++ . '</span>'
              .  '<strong style="display:block;font-size:14px;margin-bottom:6px;color:#ffdbcf;letter-spacing:.06em;text-transform:uppercase">' . htmlspecialchars($title) . '</strong>'
              .  '<span style="font-size:14px;opacity:.9;line-height:1.5">' . htmlspecialchars($desc) . '</span>'
              .  '</div>';
    }
    return $html . '</div>';
}

function lp_faqs(array $faqs): string {
    $html = '<div style="display:flex;flex-direction:column;gap:10px;margin-top:16px">';
    foreach ($faqs as $q => $a) {
        $html .= '<details style="padding:16px 20px;background:#fff;border:1px solid #cbc4d0;border-radius:10px;cursor:pointer">'
              .  '<summary style="font-weight:600;color:#170338;font-size:15px;list-style:none;outline:none;display:flex;justify-content:space-between;align-items:center;cursor:pointer">'
              .  htmlspecialchars($q)
              .  '<span style="color:#fe5e1e;font-size:22px;font-weight:300">+</span>'
              .  '</summary>'
              .  '<p style="margin:12px 0 0;color:#49454e;font-size:14px;line-height:1.7">' . htmlspecialchars($a) . '</p>'
              .  '</details>';
    }
    return $html . '</div>';
}

function lp_cta(string $title, array $bullets, string $closing): string {
    $html = '<section style="margin:48px 0 16px;padding:40px;background:linear-gradient(135deg,#170338,#2d1b4e);color:#fff;border-radius:14px;text-align:center">'
        . '<h2 style="font-family:\'Bodoni Moda\',Georgia,serif;font-size:clamp(26px,3.4vw,38px);margin:0 0 18px;color:#fff">' . htmlspecialchars($title) . '</h2>'
        . '<ul style="list-style:none;padding:0;margin:0 auto 22px;max-width:520px;display:flex;flex-direction:column;gap:8px">';
    foreach ($bullets as $b) {
        $html .= '<li style="display:flex;align-items:center;justify-content:center;gap:10px;color:#ffdbcf;font-size:15px;font-weight:600">'
              .  '<span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:#fe5e1e;color:#fff;border-radius:50%;font-size:12px">&#10003;</span>'
              .  htmlspecialchars($b)
              .  '</li>';
    }
    $html .= '</ul>'
        . '<p style="font-size:14px;letter-spacing:.18em;text-transform:uppercase;color:#ffdbcf;margin:0">' . htmlspecialchars($closing) . '</p>'
        . '</section>';
    return $html;
}

/* ------------------------------------------------------------
 * 3. Course content builders
 * ------------------------------------------------------------ */
function build_self_grooming(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Certification in Self Grooming at New Image International Beauty Institute (NIIB) is designed for individuals who want to enhance their personal appearance, confidence, communication, and professional image.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">This short-term professional course provides practical knowledge of skincare, basic makeup, hair styling, dressing sense, etiquette, and personality development. Students develop confidence and present themselves professionally in social, academic, and workplace environments.</p>';

    $meta = lp_meta_strip([
        'Course Code'        => 'NIIB-CSG-01',
        'Duration'           => '10 Days',
        'Training Hours'     => '80 Hours',
        'Maximum Level'      => 'Foundation Level',
        'Departments Covered'=> 'Grooming · Makeup · Hair · Skin',
    ]);

    $highlights = lp_highlights([
        'Personal Grooming Essentials' => 'Learn daily grooming practices for a polished and professional appearance.',
        'Basic Makeup Techniques'      => 'Master everyday makeup and professional grooming makeup looks.',
        'Hair Styling Fundamentals'    => 'Learn quick and effective hairstyling techniques.',
        'Skin Care Knowledge'          => 'Understand skincare routines for healthy and glowing skin.',
        'Personality Development'      => 'Improve confidence, communication, and self-presentation skills.',
        'Professional Image Building'  => 'Develop workplace-ready grooming standards.',
    ]);

    $modules = lp_modules([
        'Personal Grooming Basics' => ['Personal Hygiene', 'Grooming Standards', 'Appearance Management'],
        'Skin Care'                => ['Skin Types', 'Daily Skin Care Routine', 'Cleansing & Moisturizing'],
        'Basic Makeup'             => ['Foundation Basics', 'Day Makeup', 'Office Makeup', 'Natural Makeup Look'],
        'Hair Styling'             => ['Hair Care', 'Basic Hair Styling', 'Quick Professional Hairstyles'],
        'Personality Development'  => ['Communication Skills', 'Confidence Building', 'Body Language', 'Professional Etiquette'],
        'Image Enhancement'        => ['Wardrobe Selection', 'Color Coordination', 'Personal Branding'],
    ]);

    $practical = '<ul style="margin:12px 0 0;padding-left:20px;color:#49454e;line-height:1.8">'
               . '<li>Live Demonstrations</li>'
               . '<li>Grooming Practice Sessions</li>'
               . '<li>Makeup Practice</li>'
               . '<li>Hairstyling Workshops</li>'
               . '<li>Personality Development Activities</li>'
               . '</ul>';

    $faqs = lp_faqs([
        'Is this course suitable for beginners?' => 'Yes, it is designed for complete beginners.',
        'Will I learn makeup?'                   => 'Yes, basic makeup and grooming techniques are included.',
        'Is practical training included?'        => 'Yes, practical sessions are part of the program.',
        'Will I receive certification?'          => 'Yes, professional certification is provided.',
    ]);

    $cta = lp_cta('Transform Your Confidence', [
        '80 Hours Training', 'Personal Grooming Skills', 'Personality Development', 'Professional Certification',
    ], 'Admissions Open Now — Apply Today & Upgrade Your Personal & Professional Image.');

    return [
        'title'    => 'Certification in Self Grooming',
        'slug'     => 'certification-in-self-grooming',
        'category' => 'beauty-foundation',
        'short'    => 'Build confidence and a polished professional image — grooming, basic makeup, skincare, hair, and personality development in a focused 10-day program.',
        'duration' => '10 Days',
        'hours'    => '80 Hours',
        'level'    => 'Foundation',
        'code'     => 'NIIB-CSG-01',
        'meta_title' => 'Certification in Self Grooming Course – NIIB Institute',
        'meta_desc'  => 'Join NIIB\'s 10-day Self Grooming certification: skincare, makeup, hair styling, personality development & professional image building.',
        'html'     => $meta
                    . lp_section('Overview', 'Programme Overview', $intro)
                    . lp_section('Highlights', 'Key Takeaways & Highlights', $highlights)
                    . lp_section('Curriculum', 'Course Modules', $modules)
                    . lp_section('Practical', 'Practical Training', $practical)
                    . lp_section('Who It\'s For', 'Eligibility', lp_chip_list(['Students','Job Seekers','Working Professionals','Homemakers']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior experience required.</p>')
                    . lp_section('Outcomes', 'Career Benefits', lp_chip_list(['Improved Professional Image','Better Interview Confidence','Enhanced Communication','Personal Brand Development','Better Social Presence']))
                    . lp_section('FAQ', 'Frequently Asked Questions', $faqs)
                    . $cta,
    ];
}

function build_eyelash(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Certification in Eyelash Extension at NIIB is a specialized short-term professional course for aspiring beauty professionals who want to master eyelash enhancement services.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">Students learn extension application techniques, lash mapping, lash lifting, lash tinting, client consultation, safety procedures, and aftercare through extensive practical training — building a profitable skill that\'s in high demand in salons, beauty studios, and freelance businesses.</p>';

    $meta = lp_meta_strip([
        'Course Code'        => 'NIIB-CEE-01',
        'Duration'           => '10 Days',
        'Training Hours'     => '80 Hours',
        'Maximum Level'      => 'Professional Foundation',
        'Departments Covered'=> 'Lash Extensions · Lift · Tint',
    ]);

    $highlights = lp_highlights([
        'Professional Lash Extension Training' => 'Learn classic and advanced eyelash extension techniques.',
        'Lash Mapping Expertise'               => 'Master eye shape analysis and lash customization.',
        'Lash Lift & Tint Training'            => 'Learn trending lash enhancement services.',
        'Client Consultation Skills'           => 'Understand client requirements and service recommendations.',
        'Practical Learning'                   => 'Gain confidence through extensive hands-on practice.',
        'Business Development'                 => 'Learn pricing, marketing, and client retention strategies.',
    ]);

    $modules = lp_modules([
        'Introduction to Lash Industry' => ['Lash Industry Overview','Client Consultation','Professional Ethics'],
        'Eye Anatomy & Safety'          => ['Eye Structure Basics','Hygiene Standards','Safety Procedures'],
        'Lash Extension Fundamentals'   => ['Lash Types','Lash Length Selection','Lash Curl Selection','Adhesive Knowledge'],
        'Lash Application Techniques'   => ['Classic Lash Extensions','Lash Isolation Techniques','Lash Placement Methods'],
        'Lash Lift & Tint'              => ['Lash Lifting Concepts','Lash Tinting Procedures','Client Aftercare'],
        'Business & Branding'           => ['Pricing Strategies','Social Media Promotion','Portfolio Development'],
    ]);

    $practical = '<ul style="margin:12px 0 0;padding-left:20px;color:#49454e;line-height:1.8">'
               . '<li>Live Demonstrations</li>'
               . '<li>Lash Application Practice</li>'
               . '<li>Lash Lift Practice</li>'
               . '<li>Lash Tint Practice</li>'
               . '<li>Client Consultation Practice</li>'
               . '</ul>';

    $faqs = lp_faqs([
        'Is this course beginner friendly?'          => 'Yes, beginners can easily join.',
        'Will I practice on models?'                 => 'Yes, practical training is included.',
        'Can I start freelancing after this course?' => 'Yes, the course prepares students for freelance opportunities.',
        'Is certification provided?'                 => 'Yes, professional certification is awarded.',
    ]);

    $cta = lp_cta('Become A Professional Lash Artist', [
        '80 Hours Professional Training','Lash Extension Mastery','Lash Lift & Tint Training','Industry Certification','Business Development Skills',
    ], 'Admissions Open Now — Apply Today & Start Your Career As A Professional Lash Technician.');

    return [
        'title'    => 'Certification in Eyelash Extension',
        'slug'     => 'certification-in-eyelash-extension',
        'category' => 'eyelash-brows',
        'short'    => 'Master lash extensions, lifting, tinting, mapping, and client consultation — a profitable, in-demand skill built in 10 intensive days of hands-on training.',
        'duration' => '10 Days',
        'hours'    => '80 Hours',
        'level'    => 'Professional Foundation',
        'code'     => 'NIIB-CEE-01',
        'meta_title' => 'Certification in Eyelash Extension Course – NIIB Institute',
        'meta_desc'  => 'Train as a professional lash artist at NIIB. Classic & volume lashes, lash lift, tint, mapping, business skills — 80 hours of certified hands-on training.',
        'html'     => $meta
                    . lp_section('Overview', 'Programme Overview', $intro)
                    . lp_section('Highlights', 'Key Takeaways & Highlights', $highlights)
                    . lp_section('Curriculum', 'Course Modules', $modules)
                    . lp_section('Practical', 'Practical Training', $practical)
                    . lp_section('Who It\'s For', 'Eligibility', lp_chip_list(['Beauty Enthusiasts','Makeup Artists','Salon Professionals','Beginners']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior experience required.</p>')
                    . lp_section('Careers', 'Career Opportunities', lp_chip_list(['Lash Technician','Lash Artist','Beauty Professional','Freelance Lash Expert','Salon Lash Specialist','Beauty Entrepreneur']))
                    . lp_section('FAQ', 'Frequently Asked Questions', $faqs)
                    . $cta,
    ];
}

function build_cosmetology(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Certification in Cosmetology at NIIB is a professional foundation program for students beginning their journey in the beauty and wellness industry.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">It delivers practical training in hair care, skincare, makeup application, beauty therapy, salon services, and client consultation — through live demonstrations, practical workshops, and hands-on learning experiences.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">The program is an excellent starting point for careers in salons, beauty clinics, wellness centers, or onward advanced cosmetology education.</p>';

    $meta = lp_meta_strip([
        'Course Code'        => 'NIIB-CC-01',
        'Duration'           => '3 Months',
        'Training Hours'     => '360 Hours',
        'Maximum Level'      => 'Foundation Professional',
        'Departments Covered'=> 'Hair · Skin · Makeup · Nails · Therapy',
    ]);

    $levels = lp_chip_list(['Hair Foundation','Skin Care Foundation','Makeup Fundamentals','Beauty Therapy','Professional Salon Skills']);

    $highlights = lp_highlights([
        'Complete Beauty Foundation' => 'Build a strong understanding of essential beauty and cosmetology services.',
        'Hair Care Training'         => 'Learn hair care treatments, styling basics, and professional hair services.',
        'Skin Care Expertise'        => 'Understand skin types, facial procedures, and skincare recommendations.',
        'Makeup Fundamentals'        => 'Master day makeup, party makeup, and professional makeup application basics.',
        'Beauty Therapy Skills'      => 'Develop practical salon-ready beauty service skills.',
        'Client Consultation'        => 'Learn professional communication and client handling techniques.',
        'Real Salon Exposure'        => 'Gain confidence through practical demos and supervised training.',
        'Career-Focused Learning'    => 'Prepare for entry-level beauty industry opportunities.',
    ]);

    $modules = lp_modules([
        'Introduction to Cosmetology' => ['Beauty Industry Overview','Professional Ethics','Personal Grooming','Client Consultation','Salon Etiquette'],
        'Hair Care Fundamentals'      => ['Hair Structure','Hair Types','Hair Washing Techniques','Hair Spa Basics','Basic Hair Styling','Blow Dry Techniques'],
        'Skin Care Fundamentals'      => ['Skin Analysis','Skin Types','Cleansing Techniques','Facial Procedures','Skin Care Products','Home Care Recommendations'],
        'Makeup Fundamentals'         => ['Makeup Tools & Products','Foundation Application','Day Makeup','Party Makeup','Eye Makeup Basics','Lip Makeup Techniques'],
        'Beauty Therapy'              => ['Eyebrow Shaping','Threading Techniques','Waxing Basics','Hand & Foot Care','Beauty Service Procedures'],
        'Nail Care Basics'            => ['Nail Structure','Manicure Procedures','Pedicure Procedures','Nail Hygiene','Basic Nail Polish Application'],
        'Salon Operations'            => ['Client Management','Service Consultation','Appointment Handling','Customer Service','Professional Conduct'],
    ]);

    $practical = '<p style="color:#49454e;margin:0 0 10px">Students receive practical exposure through:</p>'
               . '<ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8">'
               . '<li>Live Demonstrations</li><li>Hands-On Practice</li><li>Beauty Workshops</li>'
               . '<li>Makeup Practice Sessions</li><li>Hair Styling Practice</li><li>Facial Practice Sessions</li>'
               . '<li>Real Client Exposure</li></ul>';

    $whyChoose = lp_chip_list([
        '100% Practical Training','Industry Expert Trainers','Modern Infrastructure','Real Client Exposure',
        'Placement Assistance','Industry Certification','Career Guidance','Advanced Learning Pathways',
    ]);

    $apply = lp_steps([
        'Submit Inquiry'      => 'Connect with our admissions team.',
        'Career Counselling'  => 'Receive professional guidance.',
        'Complete Enrollment' => 'Submit documents and register.',
        'Start Training'      => 'Begin your cosmetology journey.',
    ]);

    $faqs = lp_faqs([
        'Is this course suitable for beginners?'    => 'Yes. It is designed specifically for beginners.',
        'Will I learn hair, skin, and makeup?'      => 'Yes. All three areas are covered in the curriculum.',
        'Does the course include practical training?'=> 'Yes. Practical training is a major part of the course.',
        'Will I receive certification?'             => 'Yes. Professional certification is awarded upon successful completion.',
        'Can I work in a salon after completing this course?' => 'Yes. Students are prepared for entry-level salon opportunities.',
    ]);

    $cta = lp_cta('Start Your Beauty Career With NIIB', [
        '360 Hours Professional Training','Hair, Skin & Makeup Education','Practical Learning Experience','Industry Certification','Placement Assistance',
    ], 'Admissions Open Now — Apply Today & Begin Your Journey Towards Becoming A Professional Cosmetologist.');

    return [
        'title'    => 'Certification in Cosmetology',
        'slug'     => 'certification-in-cosmetology',
        'category' => 'cosmetology',
        'short'    => 'A 3-month, 360-hour professional foundation in hair, skin, makeup, beauty therapy and salon operations — the launchpad for a serious cosmetology career.',
        'duration' => '3 Months',
        'hours'    => '360 Hours',
        'level'    => 'Foundation Professional',
        'code'     => 'NIIB-CC-01',
        'meta_title' => 'Certification in Cosmetology Course – NIIB Institute',
        'meta_desc'  => '3-month Cosmetology certification at NIIB: hair, skin, makeup, therapy, nail & salon training with placement assistance and industry certification.',
        'html'     => $meta
                    . lp_section('Overview', 'Programme Overview', $intro)
                    . lp_section('Levels', 'Course Levels', $levels)
                    . lp_section('Highlights', 'Key Takeaways & Highlights', $highlights)
                    . lp_section('Curriculum', 'Course Modules', $modules)
                    . lp_section('Practical', 'Practical Training', $practical)
                    . lp_section('Soft Skills', 'Professional Skills Development', lp_chip_list(['Communication Skills','Customer Service Excellence','Professional Presentation','Product Recommendations','Salon Etiquette','Teamwork & Professionalism']))
                    . lp_section('Who It\'s For', 'Eligibility', lp_chip_list(['Minimum Age: 15 Years','10th Pass','12th Pass','Graduates','Working Professionals']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior beauty industry experience required.</p>')
                    . lp_section('Careers', 'Career Opportunities', lp_chip_list(['Assistant Cosmetologist','Beauty Therapist','Salon Assistant','Makeup Assistant','Hair Assistant','Beauty Consultant','Salon Executive','Freelance Beauty Professional']))
                    . lp_section('Why NIIB', 'Why Choose NIIB?', $whyChoose)
                    . lp_section('Apply', 'How To Apply', $apply)
                    . lp_section('FAQ', 'Frequently Asked Questions', $faqs)
                    . $cta,
    ];
}

function build_russian_hair(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Certification in Russian Hair Do at NIIB is a specialized short-term professional program for aspiring hairstylists and beauty professionals who want to master the art of elegant Russian hairstyles.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">It focuses on trendy bridal hairdos, party hairstyles, textured buns, braids, volume techniques, and modern styling concepts widely used in the beauty and bridal industry.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">Students receive practical training through live demonstrations, mannequin practice, and professional styling workshops, helping them develop confidence and creativity in hairstyling.</p>';

    $meta = lp_meta_strip([
        'Course Code'        => 'NIIB-CRHD-01',
        'Duration'           => '10 Days',
        'Training Hours'     => '80 Hours',
        'Maximum Level'      => 'Professional Foundation',
        'Departments Covered'=> 'Russian · Bridal · Fashion · Finishing',
    ]);

    $levels = lp_chip_list(['Hair Styling Foundation','Russian Hair Techniques','Bridal Hair Styling','Advanced Hair Finishing','Professional Practice']);

    $highlights = lp_highlights([
        'Professional Russian Hair Styling' => 'Learn the most popular and trending Russian hairstyle techniques.',
        'Bridal Hair Do Expertise'          => 'Master bridal buns, wedding hairstyles, and elegant hair transformations.',
        'Volume Creation Techniques'        => 'Learn professional teasing, padding, and volume-building methods.',
        'Advanced Braiding Skills'          => 'Create intricate and modern braid styles.',
        'Trend-Based Hairstyling'           => 'Stay updated with current bridal and fashion hairstyle trends.',
        'Practical Learning'                => 'Build confidence through extensive hands-on practice.',
        'Portfolio Development'             => 'Create a professional hairstyling portfolio for career growth.',
    ]);

    $modules = lp_modules([
        'Introduction to Hair Styling'  => ['Hair Styling Fundamentals','Hair Preparation Techniques','Product Knowledge','Tool Handling'],
        'Russian Hair Do Basics'        => ['Sectioning Techniques','Volume Creation','Hair Structuring','Foundation Building'],
        'Bridal Hair Styling'           => ['Bridal Buns','Reception Hairstyles','Engagement Hairstyles','Wedding Hair Concepts'],
        'Braiding Techniques'           => ['Classic Braids','Fishtail Braids','Dutch Braids','Modern Bridal Braids'],
        'Advanced Russian Hair Styling' => ['Textured Buns','Soft Romantic Hairstyles','Fashion Hair Looks','Creative Styling Techniques'],
        'Hair Finishing Techniques'     => ['Hair Accessories Placement','Hair Fixing Methods','Long-Lasting Hairstyling','Final Presentation'],
        'Portfolio Development'         => ['Hairstyle Photography','Client Presentation','Social Media Portfolio Creation'],
    ]);

    $practical = '<p style="color:#49454e;margin:0 0 10px">Students receive:</p>'
               . '<ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8">'
               . '<li>Live Demonstrations</li><li>Hairstyling Practice Sessions</li><li>Bridal Hair Workshops</li>'
               . '<li>Russian Hair Do Practice</li><li>Portfolio Building Activities</li><li>Industry Masterclasses</li></ul>';

    $whyChoose = lp_chip_list([
        '100% Practical Training','Expert Hair Trainers','Modern Styling Techniques','Industry-Focused Curriculum',
        'Portfolio Development','Placement Assistance','Entrepreneurship Support','Professional Certification',
    ]);

    $apply = lp_steps([
        'Submit Inquiry'      => 'Connect with our admissions team.',
        'Career Counselling'  => 'Receive professional guidance.',
        'Complete Enrollment' => 'Submit documents and register.',
        'Start Training'      => 'Begin your hairstyling journey.',
    ]);

    $faqs = lp_faqs([
        'Is this course suitable for beginners?'  => 'Yes. The course is beginner-friendly.',
        'Will I learn bridal hairstyles?'         => 'Yes. Bridal hairstyling is one of the major components of the course.',
        'Is practical training included?'         => 'Yes. Practical sessions form the foundation of the program.',
        'Will I receive certification?'           => 'Yes. Professional certification is awarded after successful completion.',
        'Can I work as a freelance hairstylist?'  => 'Yes. The course prepares students for both salon jobs and freelance opportunities.',
    ]);

    $cta = lp_cta('Become A Professional Russian Hair Artist', [
        '80 Hours Professional Training','Bridal Hair Styling Expertise','Advanced Russian Hair Techniques','Portfolio Development','Industry Certification','Placement Assistance',
    ], 'Admissions Open Now — Apply Today & Start Your Journey Towards Becoming A Professional Russian Hair Styling Expert.');

    return [
        'title'    => 'Certification in Russian Hair Do',
        'slug'     => 'certification-in-russian-hair-do',
        'category' => 'hair-styling',
        'short'    => 'Master elegant Russian hairstyles, bridal buns, braiding & volume techniques — a focused 80-hour program for hairstylists and aspiring bridal artists.',
        'duration' => '10 Days',
        'hours'    => '80 Hours',
        'level'    => 'Professional Foundation',
        'code'     => 'NIIB-CRHD-01',
        'meta_title' => 'Certification in Russian Hair Do Course – NIIB Institute',
        'meta_desc'  => 'Trending Russian Hair Do certification at NIIB. Bridal buns, braids, volume & finishing techniques with portfolio development and placement assistance.',
        'html'     => $meta
                    . lp_section('Overview', 'Programme Overview', $intro)
                    . lp_section('Levels', 'Course Levels', $levels)
                    . lp_section('Highlights', 'Key Takeaways & Highlights', $highlights)
                    . lp_section('Curriculum', 'Course Modules', $modules)
                    . lp_section('Practical', 'Practical Training', $practical)
                    . lp_section('Soft Skills', 'Professional Skills Development', lp_chip_list(['Client Consultation','Hairstyle Recommendations','Professional Communication','Personal Branding','Social Media Promotion']))
                    . lp_section('Who It\'s For', 'Eligibility', lp_chip_list(['Beauty Enthusiasts','Hair Stylists','Makeup Artists','Salon Professionals','Beginners']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior hairstyling experience required.</p>')
                    . lp_section('Careers', 'Career Opportunities', lp_chip_list(['Hair Stylist','Bridal Hair Artist','Russian Hair Specialist','Fashion Hair Stylist','Freelance Hair Artist','Salon Hairstylist','Beauty Professional','Bridal Studio Professional']))
                    . lp_section('Why NIIB', 'Why Choose NIIB?', $whyChoose)
                    . lp_section('Apply', 'How To Apply', $apply)
                    . lp_section('FAQ', 'Frequently Asked Questions', $faqs)
                    . $cta,
    ];
}

/* ------------------------------------------------------------
 * 4. Upsert helper
 * ------------------------------------------------------------ */
function upsert_course(PDO $pdo, array $c, int $category_id): array {
    $st = $pdo->prepare("SELECT id, image FROM courses WHERE slug = ?");
    $st->execute([$c['slug']]);
    $existing = $st->fetch();

    $data = [
        'category_id'       => $category_id,
        'title'             => $c['title'],
        'slug'              => $c['slug'],
        'short_description' => $c['short'],
        'full_description'  => $c['html'],
        'duration'          => $c['duration'] . ' / ' . $c['hours'],
        'eligibility'       => 'No prior experience required. Open to students, job seekers, working professionals, and beauty enthusiasts.',
        'level'             => $c['level'],
        'language'          => 'English / Hindi',
        'is_featured'       => 1,
        'meta_title'        => $c['meta_title'],
        'meta_description'  => $c['meta_desc'],
        'meta_keywords'     => $c['title'] . ', ' . $c['code'] . ', NIIB Institute, certification course, beauty training',
        'status'            => 1,
    ];

    if ($existing) {
        // Preserve existing image — admin may have uploaded already
        $sets = []; $vals = [];
        foreach ($data as $k => $v) { $sets[] = "`$k`=?"; $vals[] = $v; }
        $vals[] = $existing['id'];
        $pdo->prepare("UPDATE courses SET " . implode(',', $sets) . ", updated_at=NOW() WHERE id=?")->execute($vals);
        return ['status' => 'updated', 'id' => (int)$existing['id'], 'image_kept' => !empty($existing['image'])];
    }

    $cols = array_keys($data);
    $place = array_fill(0, count($cols), '?');
    $pdo->prepare("INSERT INTO courses (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $place) . ")")
        ->execute(array_values($data));
    return ['status' => 'inserted', 'id' => (int)$pdo->lastInsertId(), 'image_kept' => false];
}

/* ------------------------------------------------------------
 * 5. Run import
 * ------------------------------------------------------------ */
$catMap = [
    'beauty-foundation' => $cat_grooming,
    'eyelash-brows'     => $cat_lash,
    'cosmetology'       => $cat_cosmetology,
    'hair-styling'      => $cat_hair,
];
$courses = [build_self_grooming(), build_eyelash(), build_cosmetology(), build_russian_hair()];

$results = [];
foreach ($courses as $c) {
    $catId = $catMap[$c['category']] ?? null;
    $r = upsert_course($pdo, $c, (int)$catId);
    $results[] = ['title' => $c['title'], 'slug' => $c['slug'], 'result' => $r];
}

/* ------------------------------------------------------------
 * 6. Output report
 * ------------------------------------------------------------ */
?><!doctype html><html><head><meta charset="utf-8"><title>NIIB Course Import — Done</title>
<style>
body{font-family:'Manrope',system-ui,sans-serif;background:#f6f3f2;color:#170338;margin:0;padding:40px 20px}
.card{max-width:780px;margin:0 auto;background:#fff;border-radius:14px;padding:36px;box-shadow:0 12px 40px rgba(0,0,0,.06)}
h1{font-family:'Bodoni Moda',Georgia,serif;margin:0 0 8px;font-size:32px}
p.lead{color:#49454e;margin:0 0 28px}
.row{display:grid;grid-template-columns:1fr auto;gap:14px;align-items:center;padding:14px 0;border-bottom:1px solid #e5e2e1}
.row:last-child{border:0}
.tag{padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.tag.ins{background:#d4edda;color:#155724}
.tag.upd{background:#fff3cd;color:#856404}
a.btn{display:inline-block;margin-top:24px;padding:12px 24px;background:#170338;color:#fff;text-decoration:none;border-radius:6px;font-weight:700;letter-spacing:.06em}
.warn{background:#fff3cd;border-left:4px solid #ffc107;padding:14px 18px;margin-top:24px;border-radius:6px;color:#856404;font-size:14px;line-height:1.6}
</style></head><body>
<div class="card">
<h1>✔ Import complete</h1>
<p class="lead">4 certification courses processed. Categories created/verified. All content is now editable from the admin Courses module.</p>
<?php foreach ($results as $r): ?>
<div class="row">
  <div>
    <strong style="display:block;color:#170338"><?= htmlspecialchars($r['title']) ?></strong>
    <code style="color:#49454e;font-size:13px">/<?= htmlspecialchars($r['slug']) ?></code>
  </div>
  <span class="tag <?= $r['result']['status'] === 'inserted' ? 'ins' : 'upd' ?>"><?= htmlspecialchars($r['result']['status']) ?></span>
</div>
<?php endforeach; ?>
<div class="warn">
  <strong>Next step — upload course images:</strong><br>
  PDF images can't be auto-extracted. Open <a href="<?= e(ADMIN_URL) ?>/modules/courses.php">Admin → Courses</a>, edit each course, and upload its featured image from your PDF.<br><br>
  <strong>Security:</strong> delete <code>import_certification_courses.php</code> from the project root now that the import is done.
</div>
<a class="btn" href="<?= e(ADMIN_URL) ?>/modules/courses.php">Open Admin → Courses</a>
<a class="btn" style="background:#fe5e1e;margin-left:8px" href="<?= e(SITE_URL) ?>/courses">View Public Courses Page</a>
</div>
</body></html>

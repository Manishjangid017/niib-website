<?php
/**
 * One-shot importer — 5 Featured Cosmetology courses from the NIIB PDF.
 * Same visual format as import_specialization_courses.php / import_certification_courses.php
 * so all courses render identically on the public course-details page.
 *
 * Visit: http://localhost/niibinstitute/import_featured_courses.php?run=1
 * (CLI also supported: php import_featured_courses.php)
 * Delete this file once imported.
 */

require __DIR__ . '/includes/init.php';
$pdo = db();

$is_cli = (php_sapi_name() === 'cli');
if (!$is_cli && ($_GET['run'] ?? '') !== '1') {
    echo '<pre style="font-family:system-ui;padding:24px;background:#f6f3f2;color:#170338">';
    echo "NIIB — Featured Course Importer\n-----------------------------------\nAppend ?run=1 to the URL to execute.\nDelete this file after running.\n";
    echo '</pre>'; exit;
}

/* ---------------- Category (reuse if present, create otherwise) ---------------- */
function ensure_category(PDO $pdo, string $name, string $slug, string $icon = 'bi-stars'): int {
    $st = $pdo->prepare("SELECT id FROM course_categories WHERE slug = ?");
    $st->execute([$slug]);
    $row = $st->fetch();
    if ($row) return (int)$row['id'];
    $pdo->prepare("INSERT INTO course_categories (name, slug, description, icon, status) VALUES (?,?,?,?,1)")
        ->execute([$name, $slug, $name . ' programs at NIIB Institute', $icon]);
    return (int)$pdo->lastInsertId();
}
$cat_cosmetology = ensure_category($pdo, 'Cosmetology', 'cosmetology', 'bi-gem');

/* ---------------- Shared HTML helpers (identical to specialization importer) ---------------- */
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
        . '<h3 style="font-family:\'Bodoni Moda\',Georgia,serif;font-size:clamp(26px,3.4vw,36px);margin:0 0 16px;color:#170338">' . htmlspecialchars($title) . '</h3>'
        . $body
        . '</section>';
}
function lp_highlights(array $items): string {
    $html = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;margin-top:16px">';
    foreach ($items as $title => $desc) {
        $html .= '<div style="padding:18px;background:#fcf9f8;border-left:4px solid #fe5e1e;border-radius:0 8px 8px 0">'
              .  '<h4 style="display:block;font-family:inherit;font-weight:700;font-size:15px;color:#170338;margin:0 0 6px">' . htmlspecialchars($title) . '</h4>'
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
        $open = ($i === 2) ? ' open' : '';
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
              .  '<h4 style="display:block;font-family:inherit;font-weight:700;font-size:14px;margin:0 0 6px;color:#ffdbcf;letter-spacing:.06em;text-transform:uppercase">' . htmlspecialchars($title) . '</h4>'
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
        . '<h3 style="font-family:\'Bodoni Moda\',Georgia,serif;font-size:clamp(26px,3.4vw,38px);margin:0 0 18px;color:#fff">' . htmlspecialchars($title) . '</h3>'
        . '<ul style="list-style:none;padding:0;margin:0 auto 22px;max-width:520px;display:flex;flex-direction:column;gap:8px">';
    foreach ($bullets as $b) {
        $html .= '<li style="display:flex;align-items:center;justify-content:center;gap:10px;color:#ffdbcf;font-size:15px;font-weight:600">'
              .  '<span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:#fe5e1e;color:#fff;border-radius:50%;font-size:12px">&#10003;</span>'
              .  htmlspecialchars($b)
              .  '</li>';
    }
    $html .= '</ul><p style="font-size:14px;letter-spacing:.18em;text-transform:uppercase;color:#ffdbcf;margin:0">' . htmlspecialchars($closing) . '</p></section>';
    return $html;
}

/* ============================================================
 * 1. NATIONAL DIPLOMA IN COSMETOLOGY
 * ============================================================ */
function build_national_diploma(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The National Diploma in Cosmetology at New Image International Beauty Institute (NIIB) is a comprehensive professional beauty education program designed for aspiring beauty professionals who wish to build successful careers in the beauty, wellness, and cosmetology industry.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This advanced program combines theoretical knowledge with intensive practical training across Hair, Skin, Makeup, Nail Technology, Aesthetics, Wellness Therapies, and Salon Management. Students gain industry-ready skills through hands-on practice, live demonstrations, workshops, and real client exposure.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">The course is carefully structured to transform beginners into highly skilled beauty professionals capable of working in premium salons, wellness centers, beauty clinics, fashion industries, and entrepreneurial ventures.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-NDC-01','Duration'=>'3 Years','Training Hours'=>'3600 Hours','Maximum Level'=>'Level 5','Departments'=>'Hair · Skin · Makeup · Nails · Aesthetics · PMU · Spa · Salon Management']);
    return [
        'title'=>'National Diploma in Cosmetology','slug'=>'national-diploma-in-cosmetology','code'=>'NIIB-NDC-01',
        'duration'=>'3 Years','hours'=>'3600 Hours','level'=>'Level 5',
        'short'=>'A 3-year, 3600-hour flagship cosmetology diploma covering hair, skin, makeup, nails, aesthetics, PMU, wellness and salon management — built to transform beginners into premium-salon-ready beauty professionals.',
        'meta_title'=>'National Diploma in Cosmetology – NIIB Institute','meta_desc'=>'NIIB National Diploma in Cosmetology: 3-year, 3600-hour comprehensive cosmetology program covering hair, skin, makeup, nails, PMU, wellness and salon management with placement support.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels','<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-top:14px">'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Hair</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3, 4, 5</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Skin</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3, 4, 5</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Makeup</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3, 4</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Nail Technology</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Semi-Permanent Makeup</strong><span style="color:#49454e;font-size:14px">Level 1, 2</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Wellness &amp; Spa</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3</span></div>'
                . '</div>')
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Comprehensive Beauty Education'=>'Develop expertise across multiple beauty disciplines including hair, skin, makeup, nails, aesthetics, wellness, and business management.',
                'Industry-Focused Curriculum'=>'Designed according to current salon, spa, and beauty industry requirements to ensure maximum employability.',
                'Advanced Practical Training'=>'Gain extensive hands-on experience through practical sessions, workshops, demonstrations, and live client training.',
                'Professional Makeup Expertise'=>'Master Bridal Makeup, HD Makeup, Airbrush Makeup, Editorial Makeup, Fashion Makeup, and Celebrity Makeup techniques.',
                'Advanced Hair Designing'=>'Learn cutting, styling, coloring, balayage, keratin, rebonding, smoothening, and advanced transformation techniques.',
                'Skin & Aesthetic Treatments'=>'Acquire professional knowledge of advanced facials, HydraFacial, Chemical Peel, Skin Analysis, and Rejuvenation Therapies.',
                'Nail Technology Mastery'=>'Develop expertise in Nail Extensions, Gel Nails, Acrylic Nails, Nail Art, 3D Nail Art, and Advanced Nail Designs.',
                'Semi-Permanent Makeup Training'=>'Learn Microblading, Lip Blush, BB Glow, Brow Pigmentation, and PMU Procedures.',
                'Wellness & Spa Therapies'=>'Master body therapies, massage techniques, relaxation therapies, and wellness treatments.',
                'Entrepreneurial Development'=>'Learn salon operations, business development, client management, and marketing strategies.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Foundation Beauty Science'=>['Professional Ethics','Client Consultation','Hygiene & Sanitation','Product Knowledge','Safety Procedures'],
                'Hair Designing'=>['Hair Science','Hair Cutting','Hair Styling','Hair Coloring','Global Color','Highlights','Balayage','Keratin Treatment','Smoothening','Rebonding','Hair Botox'],
                'Skin Aesthetics'=>['Skin Analysis','Facial Treatments','Advanced Facials','Hydra Facial','Chemical Peel','Skin Rejuvenation','Acne Treatments','Anti-Aging Procedures'],
                'Professional Makeup'=>['Basic Makeup','Party Makeup','Engagement Makeup','Reception Makeup','Bridal Makeup','HD Makeup','Airbrush Makeup','Fashion Makeup','Editorial Makeup','Portfolio Makeup'],
                'Nail Technology'=>['Nail Anatomy','Nail Extensions','Gel Extensions','Acrylic Extensions','Nail Art','3D Nail Art','Nail Maintenance'],
                'Semi-Permanent Makeup'=>['Microblading','Lip Blush','BB Glow','Ombre Brows','PMU Techniques'],
                'Wellness & Spa'=>['Swedish Massage','Aroma Therapy','Stone Therapy','Body Polishing','Spa Therapies'],
                'Salon Management'=>['Client Handling','Team Management','Inventory Control','Marketing Strategies','Business Development','Salon Operations'],
            ]))
            . lp_section('Practical','Practical Training','<p style="color:#49454e;margin:0 0 10px">At NIIB, practical learning forms the foundation of professional success. Students receive:</p><ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Demonstrations</li><li>Real Client Practice</li><li>Workshop Training</li><li>Professional Photoshoots</li><li>Industry Masterclasses</li><li>Skill Assessments</li><li>Portfolio Development</li><li>Internship Opportunities</li></ul>')
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['Minimum Age: 15 Years','10th Pass','Graduates','Working Professionals','Beauty Enthusiasts']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior beauty experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Professional Cosmetologist','Senior Beauty Therapist','Hair Designer','Hair Color Specialist','Professional Makeup Artist','Bridal Makeup Expert','Nail Technician','Nail Artist','Skin Aesthetician','Beauty Consultant','Spa Therapist','Beauty Trainer','Salon Manager','Beauty Influencer','Freelance Beauty Professional','Salon Owner','Beauty Entrepreneur']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Training','Industry Expert Faculty','International Standard Curriculum','Modern Infrastructure','Placement Assistance','Certification','Entrepreneurship Support']))
            . lp_section('Apply','How To Apply',lp_steps([
                'Submit Inquiry'=>'Fill out the admission inquiry form or contact our admissions team.',
                'Career Counselling'=>'Receive personalized career guidance and course consultation.',
                'Complete Enrollment'=>'Submit documents and complete registration formalities.',
                'Start Your Journey'=>'Begin your professional training and transform your passion into a successful career.',
            ]))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes. The course starts from foundational concepts and progresses to advanced professional training.',
                'Will I get practical training?'=>'Yes. NIIB focuses heavily on practical learning and live demonstrations.',
                'Is certification provided?'=>'Yes. Students receive certification upon successful completion of the course.',
                'Can I open my own salon after completing the course?'=>'Absolutely. The course includes salon management and entrepreneurship training.',
                'Is placement assistance available?'=>'Yes. NIIB provides career guidance and placement support.',
                'What makes this course different?'=>'Its comprehensive curriculum covering Hair, Skin, Makeup, Nails, PMU, Wellness, and Business Management makes it one of the most complete beauty education programs available.',
            ]))
            . lp_cta('Ready To Become A Professional Beauty Expert?',['3600 Hours Professional Training','Industry-Oriented Curriculum','Expert Faculty','Practical Learning Environment','Placement Assistance','Entrepreneurship Support'],'Admissions Open Now — Apply Today & Begin Your Journey Towards Success.'),
    ];
}

/* ============================================================
 * 2. MASTERS IN COSMETOLOGY
 * ============================================================ */
function build_masters_in_cosmetology(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Masters in Cosmetology at New Image International Beauty Institute (NIIB) is a premium professional program designed for individuals who aspire to become industry-leading beauty professionals, trainers, consultants, and entrepreneurs.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This advanced master\'s program provides in-depth education and practical expertise in Hair Designing, Advanced Skin Treatments, Professional Makeup, Nail Technology, Spa Therapy, Body Treatments, and Salon Management.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">Students gain extensive hands-on experience through practical workshops, live demonstrations, client consultations, industry masterclasses, and real-world salon exposure. The curriculum is designed to develop both technical excellence and business understanding, ensuring graduates are prepared for leadership roles within the beauty and wellness industry.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-MIC-01','Duration'=>'2 Years','Training Hours'=>'2400 Hours','Maximum Level'=>'Level 4','Departments'=>'Hair · Skin · Makeup · Nails · Spa · Body Therapy · Salon Mgmt · Advanced Aesthetics']);
    return [
        'title'=>'Masters in Cosmetology','slug'=>'masters-in-cosmetology','code'=>'NIIB-MIC-01',
        'duration'=>'2 Years','hours'=>'2400 Hours','level'=>'Level 4',
        'short'=>'A 2-year, 2400-hour premium masters program in cosmetology covering hair, skin, makeup, nails, spa, body therapy and salon management — built for leadership roles in the beauty industry.',
        'meta_title'=>'Masters in Cosmetology – NIIB Institute','meta_desc'=>'NIIB Masters in Cosmetology: 2-year, 2400-hour advanced beauty program covering hair, skin, makeup, nails, spa, body therapy and salon management with placement support.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels','<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-top:14px">'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Hair</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3, 4</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Skin</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3, 4</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Makeup</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3, 4</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Nail Technology</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3, 4</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Spa Therapy</strong><span style="color:#49454e;font-size:14px">Western, Oriental & Ayurvedic Spa</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Body Therapy</strong><span style="color:#49454e;font-size:14px">Aroma, Relaxation & Wellness Therapy</span></div>'
                . '</div>')
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Master Advanced Cosmetology Skills'=>'Develop expertise across multiple beauty disciplines with advanced industry-focused training.',
                'Complete Professional Development'=>'Learn technical skills, client consultation, business operations, and entrepreneurship.',
                'Advanced Hair Designing'=>'Master haircutting, hairstyling, hair coloring, balayage, highlights, keratin treatments, smoothening, rebonding, and hair transformations.',
                'Professional Makeup Artistry'=>'Gain expertise in Bridal Makeup, HD Makeup, Airbrush Makeup, Fashion Makeup, Editorial Makeup, and Celebrity Makeup.',
                'Advanced Skin & Aesthetic Treatments'=>'Learn HydraFacial, Chemical Peels, Skin Analysis, Anti-Aging Treatments, Acne Solutions, and Skin Rejuvenation Techniques.',
                'Nail Technology Excellence'=>'Master Acrylic Extensions, Gel Extensions, Nail Art, 3D Nail Art, and Luxury Nail Services.',
                'Spa & Wellness Expertise'=>'Learn body therapies, massage techniques, wellness treatments, and spa management practices.',
                'Real Client Exposure'=>'Gain confidence through practical sessions and supervised client services.',
                'Industry-Focused Learning'=>'Curriculum designed according to current beauty industry requirements and market trends.',
                'Leadership & Business Skills'=>'Develop salon management, marketing, team leadership, and customer relationship skills.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Professional Beauty Science'=>['Anatomy & Physiology','Skin Science','Hair Science','Beauty Industry Standards','Hygiene & Safety','Professional Ethics'],
                'Advanced Hair Designing'=>['Precision Hair Cutting','Hair Styling Techniques','Hair Coloring','Balayage','Highlights','Global Color','Keratin Treatment','Hair Botox','Smoothening','Rebonding'],
                'Advanced Skin Aesthetics'=>['Skin Analysis','Advanced Facials','Hydra Facial','Chemical Peel','Skin Rejuvenation','Pigmentation Treatments','Acne Treatments','Anti-Aging Solutions'],
                'Professional Makeup'=>['Basic Makeup','Party Makeup','Engagement Makeup','Bridal Makeup','HD Makeup','Airbrush Makeup','Editorial Makeup','Fashion Makeup','Portfolio Makeup'],
                'Nail Technology'=>['Nail Anatomy','Nail Extensions','Acrylic Nails','Gel Nails','Nail Sculpting','Nail Art','3D Nail Art','Luxury Nail Services'],
                'Spa Therapy'=>['Swedish Massage','Deep Tissue Massage','Aroma Therapy','Stone Therapy','Body Polishing','Spa Rituals'],
                'Body Therapy'=>['Body Treatments','Relaxation Therapy','Wellness Therapy','Detox Programs','Body Care Procedures'],
                'Salon & Business Management'=>['Client Consultation','Customer Service','Inventory Management','Team Leadership','Marketing & Branding','Business Development','Salon Operations'],
            ]))
            . lp_section('Practical','Practical Training','<p style="color:#49454e;margin:0 0 10px">Students receive intensive practical training through:</p><ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Demonstrations</li><li>Workshop Sessions</li><li>Client Handling Practice</li><li>Industry Seminars</li><li>Professional Photoshoots</li><li>Portfolio Development</li><li>Internship Opportunities</li><li>Advanced Skill Assessments</li></ul>')
            . lp_section('Soft Skills','Professional Skills Development',lp_chip_list(['Client Communication','Professional Consultation','Product Recommendation','Customer Relationship Management','Retail Sales Skills','Personal Branding','Business Growth Strategies']))
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['Minimum Age: 15 Years','10th Pass','12th Pass','Graduates','Working Professionals','Existing Beauty Professionals']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Master Cosmetologist','Senior Beauty Therapist','Advanced Hair Designer','Hair Color Specialist','Professional Makeup Artist','Bridal Makeup Expert','Nail Technician','Nail Studio Owner','Spa Therapist','Wellness Consultant','Skin Aesthetician','Beauty Trainer','Beauty Consultant','Salon Manager','Beauty Influencer','Freelance Beauty Professional','Beauty Entrepreneur','Academy Trainer','Salon Owner']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Training','Expert Faculty','Advanced Infrastructure','International Standard Curriculum','Placement Assistance','Entrepreneurship Support','Professional Certification']))
            . lp_section('Apply','How To Apply',lp_steps([
                'Submit Your Inquiry'=>'Complete the admission inquiry form and connect with our admissions team.',
                'Career Counselling'=>'Receive personalized guidance based on your career goals.',
                'Complete Enrollment'=>'Submit required documents and complete registration.',
                'Begin Your Journey'=>'Start your advanced cosmetology training and build your future in the beauty industry.',
            ]))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes. The course is structured from foundation to advanced professional levels.',
                'How much practical training is included?'=>'The program focuses heavily on hands-on practical learning and live demonstrations.',
                'Will I receive certification?'=>'Yes. Students receive industry-recognized certification after successful completion.',
                'Can I open my own salon after this course?'=>'Yes. Business management and entrepreneurship modules are included.',
                'Is placement support available?'=>'Yes. NIIB provides career guidance and placement assistance.',
                'What makes this course different?'=>'This master\'s program combines advanced beauty education, practical training, leadership development, and business management into one comprehensive curriculum.',
            ]))
            . lp_cta('Become a Master Beauty Professional',['2400 Hours Professional Training','Advanced Beauty Education','Expert Faculty','Real Client Exposure','Placement Assistance','Entrepreneurship Support'],'Admissions Open Now — Apply Today & Master the Art of Beauty & Wellness.'),
    ];
}

/* ============================================================
 * 3. POST GRADUATE DIPLOMA IN COSMETOLOGY
 * ============================================================ */
function build_pg_diploma(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Post Graduate Diploma in Cosmetology at New Image International Beauty Institute (NIIB) is a prestigious professional program designed for students who aspire to become advanced beauty professionals, salon experts, beauty consultants, and entrepreneurs.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This intensive program combines advanced theoretical education with extensive practical training across Hair Designing, Professional Makeup, Skin Aesthetics, Nail Technology, Wellness Therapies, and Salon Management.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">Students gain hands-on experience through practical workshops, live demonstrations, industry masterclasses, portfolio development, and real client exposure. The program prepares students for rewarding careers in salons, spas, beauty clinics, wellness centers, fashion industries, and entrepreneurship opportunities.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-PGDC-01','Duration'=>'1.5 Years','Training Hours'=>'1800 Hours','Maximum Level'=>'Level 4','Departments'=>'Hair · Skin · Makeup · Nails · Aesthetics · PMU · Spa · Salon Management']);
    return [
        'title'=>'Post Graduate Diploma in Cosmetology','slug'=>'post-graduate-diploma-in-cosmetology','code'=>'NIIB-PGDC-01',
        'duration'=>'1.5 Years','hours'=>'1800 Hours','level'=>'Level 4',
        'short'=>'A 1.5-year, 1800-hour postgraduate cosmetology program combining advanced hair, skin, makeup, nails, PMU, wellness and salon management training with portfolio development and placement support.',
        'meta_title'=>'Post Graduate Diploma in Cosmetology – NIIB Institute','meta_desc'=>'NIIB Post Graduate Diploma in Cosmetology: 1.5-year, 1800-hour advanced beauty program with hair, skin, makeup, nails, PMU, wellness and salon management training.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels','<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-top:14px">'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Hair</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3, 4</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Skin</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3, 4</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Makeup</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3, 4</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Nail Technology</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Aesthetics</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Wellness &amp; Spa</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3</span></div>'
                . '</div>')
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Advanced Professional Training'=>'Develop professional-level expertise across multiple beauty disciplines through industry-oriented education.',
                'Comprehensive Cosmetology Education'=>'Master Hair, Skin, Makeup, Nails, Aesthetics, Wellness, and Client Management under one program.',
                'Professional Makeup Artistry'=>'Learn Bridal Makeup, HD Makeup, Airbrush Makeup, Fashion Makeup, Editorial Makeup, and Celebrity Makeup techniques.',
                'Advanced Hair Designing'=>'Gain expertise in Hair Cutting, Hair Styling, Hair Coloring, Balayage, Highlights, Keratin, Smoothening, and Rebonding.',
                'Skin & Aesthetic Treatments'=>'Learn Skin Analysis, Hydra Facial, Chemical Peel, Advanced Facials, Anti-Aging Treatments, and Skin Rejuvenation.',
                'Nail Technology Mastery'=>'Master Gel Extensions, Acrylic Extensions, Nail Sculpting, Nail Art, and Luxury Nail Services.',
                'Semi-Permanent Makeup Training'=>'Learn Microblading, Lip Blush, BB Glow, Ombre Brows, and PMU Techniques.',
                'Business & Entrepreneurship Skills'=>'Develop salon management, customer service, branding, and business growth strategies.',
                'Real Client Experience'=>'Build confidence through live demonstrations, practical workshops, and client consultations.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Beauty Science & Professional Foundation'=>['Beauty Industry Introduction','Professional Ethics','Client Consultation','Hygiene & Safety Standards','Product Knowledge'],
                'Advanced Hair Designing'=>['Hair Science','Hair Cutting','Hair Styling','Hair Coloring','Balayage','Highlights','Global Color','Keratin Treatment','Smoothening','Rebonding','Hair Botox'],
                'Advanced Skin Aesthetics'=>['Skin Analysis','Skin Consultation','Advanced Facial Techniques','Hydra Facial','Chemical Peel','Anti-Aging Treatments','Pigmentation Solutions','Acne Treatments'],
                'Professional Makeup Artistry'=>['Basic Makeup','Party Makeup','Engagement Makeup','Bridal Makeup','HD Makeup','Airbrush Makeup','Fashion Makeup','Editorial Makeup','Portfolio Makeup'],
                'Nail Technology'=>['Nail Anatomy','Acrylic Extensions','Gel Extensions','Nail Sculpting','Nail Art','3D Nail Art','Luxury Nail Services'],
                'Semi-Permanent Makeup'=>['Microblading','Lip Blush','BB Glow','Ombre Brows','PMU Safety Standards'],
                'Wellness & Spa Therapies'=>['Swedish Massage','Aroma Therapy','Stone Therapy','Body Polishing','Relaxation Therapies'],
                'Salon Management'=>['Client Handling','Inventory Management','Team Coordination','Marketing & Branding','Retail Sales','Business Development'],
            ]))
            . lp_section('Practical','Practical Training','<p style="color:#49454e;margin:0 0 10px">At NIIB, students learn through practical exposure and real-world training environments. Students receive:</p><ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Demonstrations</li><li>Practical Workshops</li><li>Real Client Exposure</li><li>Portfolio Building</li><li>Industry Seminars</li><li>Professional Photoshoots</li><li>Internship Opportunities</li><li>Advanced Skill Assessments</li></ul>')
            . lp_section('Soft Skills','Professional Development',lp_chip_list(['Client Consultation Skills','Customer Service Excellence','Product Recommendation Techniques','Communication Skills','Personal Branding','Business Growth Strategies','Salon Operations']))
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['Minimum Age: 15 Years','10th Pass','12th Pass','Graduates','Working Professionals','Beauty Enthusiasts']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior beauty industry experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Professional Cosmetologist','Beauty Therapist','Senior Makeup Artist','Bridal Makeup Expert','Hair Designer','Hair Color Specialist','Nail Technician','Nail Artist','Skin Aesthetician','Beauty Consultant','Spa Therapist','Wellness Consultant','Beauty Trainer','Salon Manager','Freelance Beauty Professional','Beauty Influencer','Salon Owner','Beauty Entrepreneur']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Training','Industry Expert Trainers','Advanced Infrastructure','International Standard Curriculum','Placement Assistance','Entrepreneurship Support','Professional Certification']))
            . lp_section('Apply','How To Apply',lp_steps([
                'Submit Inquiry'=>'Fill out the admission inquiry form and connect with our admissions team.',
                'Career Counselling'=>'Receive personalized career guidance and course consultation.',
                'Complete Enrollment'=>'Submit required documents and complete registration.',
                'Start Your Training'=>'Begin your journey toward becoming an industry-ready beauty professional.',
            ]))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes. The course starts with foundational concepts and progresses to advanced professional training.',
                'How much practical training is included?'=>'The program focuses extensively on hands-on practical learning.',
                'Will I receive certification?'=>'Yes. Students receive professional certification upon successful completion.',
                'Can I open my own salon after completing this course?'=>'Yes. Salon management and entrepreneurship modules are included.',
                'Is placement support available?'=>'Yes. NIIB provides career guidance and placement assistance.',
                'What makes this program unique?'=>'It combines advanced cosmetology training, practical exposure, business education, and career development into one comprehensive course.',
            ]))
            . lp_cta('Become an Advanced Beauty Professional',['1800 Hours Professional Training','Advanced Industry Curriculum','Expert Faculty','Real Client Exposure','Placement Assistance','Entrepreneurship Support'],'Admissions Open Now — Apply Today & Build Your Future in the Beauty & Wellness Industry.'),
    ];
}

/* ============================================================
 * 4. ADVANCE DIPLOMA IN AESTHETICS & HAIR DESIGNING
 * ============================================================ */
function build_advance_diploma_aesthetics_hair(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Advance Diploma in Aesthetics & Hair Designing at New Image International Beauty Institute (NIIB) is a comprehensive beauty education program designed for aspiring beauty professionals who want to build expertise in Hair Designing, Aesthetics, Skin Care, Makeup, Nail Technology, and Wellness Therapies.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This advanced diploma combines practical training with professional knowledge, enabling students to develop the technical and creative skills required in today\'s beauty and wellness industry. Through live demonstrations, workshops, practical sessions, and real client exposure, students gain confidence and industry-ready experience.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">The program is ideal for individuals who are passionate about beauty, creativity, fashion, and helping others look and feel their best.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-ADAHD-01','Duration'=>'1 Year','Training Hours'=>'1200 Hours','Maximum Level'=>'Level 3','Departments'=>'Hair · Skin · Makeup · Nails · Spa · Body Therapy · Advanced Aesthetics']);
    return [
        'title'=>'Advance Diploma in Aesthetics & Hair Designing','slug'=>'advance-diploma-in-aesthetics-hair-designing','code'=>'NIIB-ADAHD-01',
        'duration'=>'1 Year','hours'=>'1200 Hours','level'=>'Level 3',
        'short'=>'A 1-year, 1200-hour advanced diploma in aesthetics and hair designing — covering hair, skin, makeup, nails, and wellness with hands-on practical training and placement support.',
        'meta_title'=>'Advance Diploma in Aesthetics & Hair Designing – NIIB Institute','meta_desc'=>'NIIB Advance Diploma in Aesthetics & Hair Designing: 1-year, 1200-hour program covering hair, skin, makeup, nails, spa and wellness therapies with placement support.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels','<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-top:14px">'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Hair</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Skin</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Makeup</strong><span style="color:#49454e;font-size:14px">Level 1, 2</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Nail Technology</strong><span style="color:#49454e;font-size:14px">Level 1, 2</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Spa Therapy</strong><span style="color:#49454e;font-size:14px">Level 1, 2</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Body Therapy</strong><span style="color:#49454e;font-size:14px">Level 1, 2</span></div>'
                . '</div>')
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Complete Aesthetics & Hair Education'=>'Develop expertise across hair, skin, makeup, nail technology, and wellness services through a professionally designed curriculum.',
                'Advanced Hair Designing Skills'=>'Master haircutting, hairstyling, hair coloring, balayage, highlights, hair spa, keratin treatment, smoothening, and rebonding.',
                'Professional Aesthetic Training'=>'Learn advanced skincare procedures, facial treatments, skin analysis, Hydra Facial concepts, and beauty therapies.',
                'Professional Makeup Techniques'=>'Gain knowledge of Party Makeup, Engagement Makeup, Bridal Makeup, HD Makeup, and Fashion Makeup.',
                'Nail Technology Fundamentals'=>'Learn nail extensions, gel nails, acrylic nails, nail art, and nail maintenance services.',
                'Wellness & Spa Knowledge'=>'Understand body therapies, relaxation treatments, massage techniques, and client wellness services.',
                'Hands-On Practical Learning'=>'Build confidence through extensive practical sessions, workshops, and supervised client training.',
                'Trend-Based Education'=>'Stay updated with modern beauty trends, techniques, and industry requirements.',
                'Client Consultation Skills'=>'Learn effective communication, consultation methods, and personalized service delivery.',
                'Business & Entrepreneurial Skills'=>'Develop salon management, customer service, marketing, and business growth strategies.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Beauty Foundation'=>['Beauty Industry Introduction','Hygiene & Sanitation','Professional Ethics','Client Consultation','Product Knowledge'],
                'Hair Designing'=>['Hair Science','Hair Cutting','Hair Styling','Hair Ironing','Hair Curling','Hair Coloring','Global Color','Highlights','Balayage','Hair Spa','Keratin Treatment','Smoothening','Rebonding'],
                'Aesthetics & Skin Care'=>['Skin Analysis','Skin Consultation','Cleansing Procedures','Facial Techniques','Advanced Facial Treatments','Hydra Facial Awareness','Skin Rejuvenation Concepts','Acne Care','Pigmentation Care'],
                'Professional Makeup'=>['Basic Makeup','Day Makeup','Evening Makeup','Party Makeup','Engagement Makeup','Bridal Makeup','HD Makeup','Fashion Makeup'],
                'Nail Technology'=>['Nail Anatomy','Manicure','Pedicure','Gel Extensions','Acrylic Extensions','Nail Art','3D Nail Art'],
                'Spa & Body Therapy'=>['Swedish Massage','Aroma Therapy','Body Polishing','Relaxation Therapy','Spa Rituals','Wellness Treatments'],
            ]))
            . lp_section('Practical','Practical Training','<p style="color:#49454e;margin:0 0 10px">Students receive extensive practical exposure through:</p><ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Demonstrations</li><li>Practical Workshops</li><li>Real Client Practice</li><li>Industry Seminars</li><li>Portfolio Development</li><li>Professional Photoshoots</li><li>Skill Assessments</li><li>Industry Masterclasses</li></ul>')
            . lp_section('Soft Skills','Professional Skills Development',lp_chip_list(['Client Communication Skills','Professional Consultation Techniques','Customer Service Excellence','Product Recommendation Skills','Retail Sales Knowledge','Personal Branding','Business Development Skills']))
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['Minimum Age: 15 Years','10th Pass','12th Pass','Graduates','Working Professionals','Beauty Enthusiasts']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior beauty industry experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Hair Designer','Hair Stylist','Hair Color Specialist','Beauty Therapist','Skin Consultant','Aesthetician','Makeup Artist','Bridal Makeup Artist','Nail Technician','Nail Artist','Spa Therapist','Wellness Consultant','Beauty Trainer','Beauty Advisor','Freelance Beauty Professional','Salon Manager','Salon Owner','Beauty Entrepreneur']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Training','Expert Trainers','Modern Infrastructure','Industry-Oriented Curriculum','Placement Assistance','Entrepreneurship Support','Industry Certification']))
            . lp_section('Apply','How To Apply',lp_steps([
                'Submit Inquiry'=>'Fill out the admission inquiry form and connect with our admissions team.',
                'Career Counselling'=>'Receive personalized career guidance and course consultation.',
                'Complete Enrollment'=>'Submit documents and complete registration formalities.',
                'Begin Your Training'=>'Start your journey toward becoming a professional beauty expert.',
            ]))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes. The course starts with foundational concepts and gradually progresses to advanced professional training.',
                'Does the course include practical training?'=>'Yes. Practical learning is a major component of the program.',
                'Will I receive certification?'=>'Yes. Students receive professional certification after successful completion.',
                'Can I start my own salon after this course?'=>'Yes. The course includes business and salon management concepts.',
                'Is placement assistance available?'=>'Yes. NIIB provides career guidance and placement support.',
                'What makes this course unique?'=>'The course combines Hair Designing, Aesthetics, Makeup, Nails, and Wellness into one comprehensive professional diploma program.',
            ]))
            . lp_cta('Build Your Future In Beauty & Aesthetics',['1200 Hours Professional Training','Advanced Hair & Aesthetics Curriculum','Expert Faculty','Practical Learning Environment','Placement Assistance','Entrepreneurship Support'],'Admissions Open Now — Apply Today & Begin Your Journey Towards Professional Success.'),
    ];
}

/* ============================================================
 * 5. ADVANCE DIPLOMA IN COSMETOLOGY
 * ============================================================ */
function build_advance_diploma_cosmetology(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Advance Diploma in Cosmetology at New Image International Beauty Institute (NIIB) is a career-focused professional program designed for aspiring beauty professionals who want to build expertise in Hair, Skin, Makeup, Nail Technology, and Wellness Services.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This intensive program combines advanced theoretical knowledge with extensive practical training, helping students develop industry-ready skills in beauty care, hair services, skin treatments, makeup artistry, and client consultation.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">Students gain valuable hands-on experience through practical workshops, live demonstrations, real client exposure, and industry-oriented training sessions. The course is designed to transform beginners into confident beauty professionals capable of working in salons, spas, wellness centers, beauty clinics, and entrepreneurial ventures.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-ADC-01','Duration'=>'6 Months','Training Hours'=>'720 Hours','Maximum Level'=>'Level 3','Departments'=>'Hair · Skin · Makeup · Nails · Spa · Body Therapy · Beauty Therapy']);
    return [
        'title'=>'Advance Diploma in Cosmetology','slug'=>'advance-diploma-in-cosmetology','code'=>'NIIB-ADC-01',
        'duration'=>'6 Months','hours'=>'720 Hours','level'=>'Level 3',
        'short'=>'A 6-month, 720-hour career-focused cosmetology diploma covering hair, skin, makeup, nails, spa and wellness services — designed to make beginners industry-ready.',
        'meta_title'=>'Advance Diploma in Cosmetology – NIIB Institute','meta_desc'=>'NIIB Advance Diploma in Cosmetology: 6-month, 720-hour beauty program covering hair, skin, makeup, nails, spa and wellness services with placement support.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels','<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-top:14px">'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Hair</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Skin</strong><span style="color:#49454e;font-size:14px">Level 1, 2, 3</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Makeup</strong><span style="color:#49454e;font-size:14px">Level 1, 2</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Nail Technology</strong><span style="color:#49454e;font-size:14px">Level 1, 2</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Spa Therapy</strong><span style="color:#49454e;font-size:14px">Level 1, 2</span></div>'
                . '<div style="padding:16px;background:#fcf9f8;border:1px solid #cbc4d0;border-radius:10px"><strong style="display:block;color:#170338;margin-bottom:6px">Body Therapy</strong><span style="color:#49454e;font-size:14px">Level 1, 2</span></div>'
                . '</div>')
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Comprehensive Beauty Education'=>'Develop professional skills across hair, skin, makeup, nails, and wellness therapies through an integrated curriculum.',
                'Advanced Hair Training'=>'Master haircutting, hairstyling, hair coloring, highlights, global color, keratin treatments, smoothening, rebonding, and hair spa therapies.',
                'Professional Skin Care Knowledge'=>'Learn skin analysis, facial treatments, advanced facials, skin rejuvenation concepts, and beauty therapy procedures.',
                'Makeup Artistry Skills'=>'Gain expertise in Party Makeup, Engagement Makeup, Bridal Makeup, HD Makeup, and Professional Makeup Techniques.',
                'Nail Technology Training'=>'Learn manicure, pedicure, gel extensions, acrylic extensions, nail art, and nail care services.',
                'Wellness & Spa Education'=>'Develop knowledge of massage therapies, relaxation treatments, body polishing, and wellness services.',
                'Hands-On Practical Learning'=>'Build confidence through practical workshops, demonstrations, and supervised client training.',
                'Industry Exposure'=>'Participate in seminars, workshops, beauty events, and trend-based learning sessions.',
                'Client Consultation Skills'=>'Learn professional communication, consultation methods, and personalized beauty recommendations.',
                'Career-Focused Curriculum'=>'Training designed according to modern beauty industry requirements and salon standards.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Beauty Foundation'=>['Introduction to Beauty Industry','Professional Ethics','Hygiene & Sanitation','Client Consultation','Product Knowledge','Safety Standards'],
                'Hair Designing'=>['Hair Science','Hair Cutting','Hair Styling','Hair Ironing','Hair Curling','Hair Spa','Hair Coloring','Global Color','Highlights','Keratin Treatment','Smoothening','Rebonding'],
                'Skin & Beauty Therapy'=>['Skin Analysis','Skin Consultation','Cleansing Procedures','Facial Techniques','Advanced Facial Treatments','Skin Rejuvenation','Acne Care','Pigmentation Care'],
                'Professional Makeup'=>['Basic Makeup','Day Makeup','Evening Makeup','Party Makeup','Engagement Makeup','Bridal Makeup','HD Makeup'],
                'Nail Technology'=>['Nail Anatomy','Manicure','Pedicure','Gel Extensions','Acrylic Extensions','Nail Art','Nail Maintenance'],
                'Spa & Wellness'=>['Swedish Massage','Aroma Therapy','Body Polishing','Relaxation Therapy','Wellness Treatments','Spa Rituals'],
            ]))
            . lp_section('Practical','Practical Training','<p style="color:#49454e;margin:0 0 10px">At NIIB, practical training is at the heart of every program. Students receive:</p><ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Demonstrations</li><li>Practical Workshops</li><li>Real Client Practice</li><li>Industry Seminars</li><li>Professional Photoshoots</li><li>Portfolio Development</li><li>Skill Assessments</li><li>Beauty Events Participation</li></ul>')
            . lp_section('Soft Skills','Professional Skills Development',lp_chip_list(['Client Communication','Professional Consultation','Customer Service Excellence','Product Recommendation Skills','Retail Sales Techniques','Personal Branding','Salon Operations']))
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['Minimum Age: 15 Years','10th Pass','12th Pass','Graduates','Working Professionals','Beauty Enthusiasts']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No previous beauty industry experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Professional Cosmetologist','Beauty Therapist','Hair Stylist','Hair Technician','Makeup Artist','Bridal Makeup Artist','Skin Consultant','Beauty Advisor','Nail Technician','Nail Artist','Spa Therapist','Wellness Consultant','Beauty Trainer','Salon Executive','Freelance Beauty Professional','Salon Owner','Beauty Entrepreneur']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Training','Expert Trainers','Industry-Oriented Curriculum','Modern Infrastructure','Placement Assistance','Entrepreneurship Support','Professional Certification']))
            . lp_section('Apply','How To Apply',lp_steps([
                'Submit Inquiry'=>'Fill out the admission inquiry form and connect with our admissions team.',
                'Career Counselling'=>'Receive personalized guidance regarding your career goals and course selection.',
                'Complete Enrollment'=>'Submit required documents and complete registration.',
                'Begin Your Training'=>'Start your professional journey in the beauty and wellness industry.',
            ]))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes. The course begins with fundamental concepts and progresses to advanced practical training.',
                'Does the course include practical training?'=>'Yes. Practical learning is one of the core components of the program.',
                'Will I receive certification?'=>'Yes. Students receive professional certification upon successful completion.',
                'Can I work in salons after this course?'=>'Yes. The program is designed to make students industry-ready.',
                'Is placement assistance available?'=>'Yes. NIIB provides placement support and career guidance.',
                'Can I start my own beauty business after completing the course?'=>'Absolutely. The course includes professional and business development concepts to help students become entrepreneurs.',
            ]))
            . lp_cta('Start Your Beauty Career With Confidence',['720 Hours Professional Training','Industry-Focused Curriculum','Expert Faculty','Practical Learning Environment','Placement Assistance','Entrepreneurship Support'],'Admissions Open Now — Apply Today & Take The First Step Towards A Successful Career In Beauty & Wellness.'),
    ];
}

/* ---------------- Upsert helper (preserves existing image) ---------------- */
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
        'meta_keywords'     => $c['title'] . ', ' . $c['code'] . ', NIIB Institute, cosmetology diploma, beauty training',
        'status'            => 1,
    ];
    if ($existing) {
        $sets = []; $vals = [];
        foreach ($data as $k => $v) { $sets[] = "`$k`=?"; $vals[] = $v; }
        $vals[] = $existing['id'];
        $pdo->prepare("UPDATE courses SET " . implode(',', $sets) . ", updated_at=NOW() WHERE id=?")->execute($vals);
        return ['status'=>'updated','id'=>(int)$existing['id']];
    }
    $cols = array_keys($data);
    $place = array_fill(0, count($cols), '?');
    $pdo->prepare("INSERT INTO courses (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $place) . ")")
        ->execute(array_values($data));
    return ['status'=>'inserted','id'=>(int)$pdo->lastInsertId()];
}

/* ---------------- Run ---------------- */
$courses = [
    build_national_diploma(),
    build_masters_in_cosmetology(),
    build_pg_diploma(),
    build_advance_diploma_aesthetics_hair(),
    build_advance_diploma_cosmetology(),
];
$results = [];
foreach ($courses as $c) {
    $r = upsert_course($pdo, $c, $cat_cosmetology);
    $results[] = ['title'=>$c['title'],'slug'=>$c['slug'],'result'=>$r];
}

/* ---------------- Report ---------------- */
?><!doctype html><html><head><meta charset="utf-8"><title>NIIB Featured Course Import — Done</title>
<style>
body{font-family:'Manrope',system-ui,sans-serif;background:#f6f3f2;color:#170338;margin:0;padding:40px 20px}
.card{max-width:780px;margin:0 auto;background:#fff;border-radius:14px;padding:36px;box-shadow:0 12px 40px rgba(0,0,0,.06)}
h1{font-family:'Bodoni Moda',Georgia,serif;margin:0 0 8px;font-size:32px}
p.lead{color:#49454e;margin:0 0 28px}
.row{display:grid;grid-template-columns:1fr auto;gap:14px;align-items:center;padding:14px 0;border-bottom:1px solid #e5e2e1}
.row:last-child{border:0}
.tag{padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.tag.ins{background:#d4edda;color:#155724}.tag.upd{background:#fff3cd;color:#856404}
a.btn{display:inline-block;margin-top:24px;padding:12px 24px;background:#170338;color:#fff;text-decoration:none;border-radius:6px;font-weight:700;letter-spacing:.06em}
.warn{background:#fff3cd;border-left:4px solid #ffc107;padding:14px 18px;margin-top:24px;border-radius:6px;color:#856404;font-size:14px;line-height:1.6}
</style></head><body>
<div class="card">
<h1>&#10004; Import complete</h1>
<p class="lead">5 Featured Cosmetology courses processed in the <strong>Cosmetology</strong> category. All content is editable from <a href="<?= e(ADMIN_URL) ?>/modules/courses.php">Admin &rarr; Courses</a>.</p>
<?php foreach ($results as $r): ?>
<div class="row">
  <div>
    <strong style="display:block;color:#170338"><?= htmlspecialchars($r['title']) ?></strong>
    <code style="color:#49454e;font-size:13px">/course/<?= htmlspecialchars($r['slug']) ?></code>
  </div>
  <span class="tag <?= $r['result']['status']==='inserted'?'ins':'upd' ?>"><?= htmlspecialchars($r['result']['status']) ?></span>
</div>
<?php endforeach; ?>
<div class="warn">
  <strong>Next &mdash; upload course images:</strong> open each course in <a href="<?= e(ADMIN_URL) ?>/modules/courses.php">Admin &rarr; Courses</a> and upload the featured image from your PDF.<br><br>
  <strong>Security:</strong> delete <code>import_featured_courses.php</code> from the project root now that the import is done.
</div>
<a class="btn" href="<?= e(ADMIN_URL) ?>/modules/courses.php">Open Admin &rarr; Courses</a>
<a class="btn" style="background:#fe5e1e;margin-left:8px" href="<?= e(SITE_URL) ?>/courses">View Public Courses Page</a>
</div>
</body></html>

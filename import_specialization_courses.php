<?php
/**
 * One-shot importer — 7 Specialization (Master) courses from the NIIB PDF.
 * Same visual format as import_certification_courses.php so all courses
 * render identically on the public course page.
 *
 * Visit: http://localhost/niibinstitute/import_specialization_courses.php?run=1
 * (CLI also supported: php import_specialization_courses.php)
 * Delete this file once imported.
 */

require __DIR__ . '/includes/init.php';
$pdo = db();

$is_cli = (php_sapi_name() === 'cli');
if (!$is_cli && ($_GET['run'] ?? '') !== '1') {
    echo '<pre style="font-family:system-ui;padding:24px;background:#f6f3f2;color:#170338">';
    echo "NIIB — Specialization Course Importer\n-----------------------------------\nAppend ?run=1 to the URL to execute.\nDelete this file after running.\n";
    echo '</pre>'; exit;
}

/* ---------------- Categories (reuse if present, create otherwise) ---------------- */
function ensure_category(PDO $pdo, string $name, string $slug, string $icon = 'bi-stars'): int {
    $st = $pdo->prepare("SELECT id FROM course_categories WHERE slug = ?");
    $st->execute([$slug]);
    $row = $st->fetch();
    if ($row) return (int)$row['id'];
    $pdo->prepare("INSERT INTO course_categories (name, slug, description, icon, status) VALUES (?,?,?,?,1)")
        ->execute([$name, $slug, $name . ' programs at NIIB Institute', $icon]);
    return (int)$pdo->lastInsertId();
}
$cat_hair    = ensure_category($pdo, 'Hair Design',          'hair-design',            'bi-scissors');
$cat_makeup  = ensure_category($pdo, 'Makeup Artistry',      'makeup-artistry',        'bi-palette');
$cat_aesth   = ensure_category($pdo, 'Aesthetics & Laser',   'aesthetics-laser',       'bi-droplet');
$cat_pmu     = ensure_category($pdo, 'Semi Permanent Makeup','semi-permanent-makeup',  'bi-brush');
$cat_nail    = ensure_category($pdo, 'Nail Technology',      'nail-technology',        'bi-stars');
$cat_well    = ensure_category($pdo, 'Wellness & Massage',   'wellness-massage',       'bi-flower3');
$cat_cook    = ensure_category($pdo, 'Cooking & Culinary',   'cooking-culinary',       'bi-cup-hot');

/* ---------------- Shared HTML helpers (identical to certification importer) ---------------- */
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
        . '<h2 style="font-family:\'Bodoni Moda\',Georgia,serif;font-size:clamp(26px,3.4vw,38px);margin:0 0 18px;color:#fff">' . htmlspecialchars($title) . '</h2>'
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
 * 1. MASTER IN HAIR DESIGNING
 * ============================================================ */
function build_master_hair(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Master in Hair Designing at NIIB is an advanced professional program designed for aspiring hair artists who wish to build expertise in haircutting, hairstyling, hair coloring, and chemical hair treatments.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This intensive course provides extensive practical training and industry exposure to help students master modern hair trends, transformation techniques, and salon procedures.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">The program prepares students for successful careers in premium salons, fashion industries, beauty academies, and independent entrepreneurship.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-MHD-01','Duration'=>'5 Months','Training Hours'=>'600 Hours','Maximum Level'=>'Level 3','Departments Covered'=>'Hair Designing · Chemical Services']);
    return [
        'title'=>'Master in Hair Designing','slug'=>'master-in-hair-designing','category'=>'hair-design','code'=>'NIIB-MHD-01',
        'duration'=>'5 Months','hours'=>'600 Hours','level'=>'Level 3',
        'short'=>'A 5-month, 600-hour advanced hair designing program covering cutting, styling, coloring, and chemical treatments — built for aspiring premium-salon hair artists.',
        'meta_title'=>'Master in Hair Designing – NIIB Institute','meta_desc'=>'NIIB Master in Hair Designing: 600-hour program covering haircutting, styling, balayage, ombre, keratin, and salon-ready chemical treatments with placement support.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels',lp_chip_list(['Hair Foundation','Hair Styling','Hair Coloring','Advanced Hair Chemical Services','Salon Professional Skills']))
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Professional Hair Cutting Techniques'=>'Master basic to advanced haircutting techniques for men and women.',
                'Advanced Hair Styling'=>'Learn party hairstyles, bridal hairstyles, tong setting, curls, waves, buns, and modern styling techniques.',
                'Hair Coloring Expertise'=>'Develop expertise in global color, highlights, lowlights, balayage, ombre, and color correction.',
                'Chemical Hair Treatments'=>'Learn keratin, smoothening, rebonding, hair botox, and advanced treatment procedures.',
                'Trend-Based Learning'=>'Stay updated with current salon trends and international hair fashion techniques.',
                'Real Client Exposure'=>'Practice on live models under professional supervision.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Hair Science'=>['Hair Structure','Hair Growth Cycle','Scalp Analysis','Hair Consultation','Product Knowledge'],
                'Hair Cutting'=>['Basic Haircuts','Layer Cut','Graduation Cut','Bob Cut','Pixie Cut','Advanced Precision Cutting'],
                'Hair Styling'=>['Blow Dry','Ironing','Curling','Tong Setting','Party Hairstyles','Bridal Hairstyles','Fashion Hair Styling'],
                'Hair Coloring'=>['Global Color','Root Touch Up','Highlights','Lowlights','Balayage','Ombre','Fashion Colors'],
                'Hair Treatments'=>['Hair Spa','Keratin Treatment','Smoothening','Rebonding','Hair Botox','Deep Conditioning Treatments'],
                'Salon Management'=>['Client Consultation','Service Recommendations','Retail Sales','Customer Handling','Professional Ethics'],
            ]))
            . lp_section('Practical','Practical Training','<ul style="margin:12px 0 0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Demonstrations</li><li>Hands-On Practice</li><li>Real Client Exposure</li><li>Workshop Training</li><li>Portfolio Development</li><li>Industry Masterclasses</li></ul>')
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['10th Pass','12th Pass','Graduates','Working Professionals']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Hair Stylist','Hair Designer','Hair Color Specialist','Hair Technician','Salon Professional','Hair Trainer','Freelance Hair Artist','Salon Owner']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Training','Industry Expert Faculty','Modern Salon Infrastructure','International Standard Curriculum','Placement Assistance','Entrepreneurship Support','Industry Certification']))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes, the course starts from fundamentals and progresses to advanced levels.',
                'Will I learn hair coloring?'=>'Yes, including balayage, highlights, ombre, and global coloring.',
                'Is practical training included?'=>'Yes, practical training is a major component of the course.',
                'Will I receive certification?'=>'Yes, professional certification is provided upon successful completion.',
            ]))
            . lp_cta('Become A Professional Hair Artist',['600 Hours Training','Advanced Hair Designing Skills','Live Client Practice','Industry Certification','Placement Assistance'],'Admissions Open Now — Apply Today & Build Your Career In Professional Hair Designing.'),
    ];
}

/* ============================================================
 * 2. MASTER IN MAKEUP
 * ============================================================ */
function build_master_makeup(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Master in Makeup at NIIB is a specialized professional program designed for aspiring makeup artists who want to build a successful career in the beauty, fashion, bridal, entertainment, and media industries.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This advanced program focuses on developing artistic skills, technical expertise, and professional confidence through extensive practical training — from makeup fundamentals to advanced bridal transformations, HD makeup, airbrush techniques, editorial looks, and fashion makeup artistry.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">With hands-on training, live demonstrations, portfolio creation, and industry exposure, students graduate ready to work as professional makeup artists or launch their own beauty business.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-MMU-01','Duration'=>'4 Months','Training Hours'=>'480 Hours','Maximum Level'=>'Level 3','Departments Covered'=>'Professional Makeup · Bridal · HD · Airbrush · Editorial']);
    return [
        'title'=>'Master in Makeup','slug'=>'master-in-makeup','category'=>'makeup-artistry','code'=>'NIIB-MMU-01',
        'duration'=>'4 Months','hours'=>'480 Hours','level'=>'Level 3',
        'short'=>'A 480-hour professional makeup masterclass — bridal, HD, airbrush, editorial, fashion — with portfolio building and placement support.',
        'meta_title'=>'Master in Makeup Course – NIIB Institute','meta_desc'=>'NIIB Master in Makeup: 4-month bridal, HD, airbrush & editorial makeup artistry training with portfolio development and industry certification.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels',lp_chip_list(['Makeup Foundation','Professional Makeup Techniques','Bridal Makeup Artistry','HD & Airbrush Makeup','Fashion & Editorial Makeup','Portfolio Development']))
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Professional Makeup Mastery'=>'Learn the complete journey from beginner makeup techniques to advanced professional artistry.',
                'Bridal Makeup Expertise'=>'Master engagement, reception, traditional, contemporary, and luxury bridal looks.',
                'HD Makeup Training'=>'Learn high-definition makeup techniques suitable for photography, events, and professional shoots.',
                'Airbrush Makeup Techniques'=>'Develop skills in flawless airbrush application for long-lasting professional results.',
                'Fashion & Editorial Makeup'=>'Create runway, magazine, celebrity-inspired, and creative editorial looks.',
                'Face Analysis & Correction'=>'Learn facial structure analysis, contouring, highlighting, color correction, and skin preparation.',
                'Product Knowledge'=>'Gain understanding of professional makeup brands, tools, brushes, and application techniques.',
                'Portfolio Building'=>'Develop a professional makeup portfolio to showcase your skills and attract clients.',
                'Client Consultation Skills'=>'Learn how to understand client needs and create customized makeup looks.',
                'Industry Exposure'=>'Participate in workshops, photoshoots, beauty events, and masterclasses.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Makeup Foundation'=>['Introduction to Makeup Industry','Professional Ethics','Makeup Artist Responsibilities','Client Consultation','Product Knowledge','Brush Knowledge & Maintenance'],
                'Skin Preparation'=>['Skin Analysis','Skin Types','Cleansing & Toning','Moisturizing','Primers & Base Preparation','Makeup Hygiene Standards'],
                'Basic Makeup Techniques'=>['Foundation Application','Concealing Techniques','Powder Application','Face Structuring','Blush Application','Lip Techniques'],
                'Eye Makeup Artistry'=>['Eyebrow Shaping','Eyeliner Techniques','Smokey Eyes','Cut Crease','Glitter Eyes','Soft Glam Eyes','Lash Application'],
                'Advanced Makeup Techniques'=>['Color Correction','Contouring','Highlighting','Face Sculpting','Long-Wear Makeup Techniques','Professional Finishing'],
                'Bridal Makeup'=>['Traditional Bridal Makeup','Punjabi Bridal Makeup','Reception Makeup','Engagement Makeup','Contemporary Bridal Looks','Luxury Bridal Transformations'],
                'HD Makeup'=>['HD Product Understanding','HD Skin Preparation','HD Base Creation','Camera-Friendly Makeup','Photography Makeup Techniques'],
                'Airbrush Makeup'=>['Airbrush Equipment Knowledge','Airbrush Product Application','Flawless Base Techniques','Long-Lasting Makeup Methods'],
                'Fashion & Editorial Makeup'=>['Ramp Makeup','Editorial Makeup','Creative Makeup','Fashion Photoshoot Makeup','Celebrity Inspired Looks'],
                'Professional Portfolio Development'=>['Photoshoot Planning','Model Selection','Portfolio Building','Social Media Branding','Client Presentation Skills'],
            ]))
            . lp_section('Practical','Practical Training','<p style="color:#49454e;margin:0 0 10px">At NIIB, students receive extensive practical training through:</p><ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Makeup Demonstrations</li><li>Hands-On Practice Sessions</li><li>Real Bridal Makeup Practice</li><li>Fashion Makeup Workshops</li><li>Editorial Makeup Sessions</li><li>Photoshoot Participation</li><li>Industry Masterclasses</li><li>Portfolio Development Projects</li></ul>')
            . lp_section('Soft Skills','Professional Skills Development',lp_chip_list(['Client Communication','Bridal Consultation','Makeup Product Recommendations','Personal Branding','Social Media Marketing','Business Development','Freelancing Strategies']))
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['Minimum Age: 15 Years','10th Pass','12th Pass','Graduates','Working Professionals','Beauty Enthusiasts']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior makeup experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Professional Makeup Artist','Bridal Makeup Artist','Celebrity Makeup Artist','Fashion Makeup Artist','Editorial Makeup Artist','Freelance Makeup Artist','Beauty Consultant','Beauty Influencer','Makeup Trainer','Beauty Advisor','Studio Makeup Artist','Bridal Studio Owner','Beauty Entrepreneur']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Training','Expert Makeup Trainers','Advanced Studio Infrastructure','Trend-Focused Curriculum','Portfolio Development','Placement Assistance','Entrepreneurship Support','Professional Certification']))
            . lp_section('Apply','How To Apply',lp_steps([
                'Submit Inquiry'=>'Connect with our admissions team and complete your inquiry.',
                'Career Counselling'=>'Receive expert guidance regarding your makeup career path.',
                'Complete Enrollment'=>'Submit documents and complete registration.',
                'Start Your Training'=>'Begin your journey toward becoming a professional makeup artist.',
            ]))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes. The course starts with makeup fundamentals and progresses to advanced professional techniques.',
                'Will I learn bridal makeup?'=>'Yes. Bridal makeup is one of the major specializations covered in this course.',
                'Is airbrush makeup included?'=>'Yes. Students receive dedicated training in professional airbrush makeup techniques.',
                'Will I build a professional portfolio?'=>'Yes. Portfolio development is included to help students showcase their skills professionally.',
                'Can I start freelancing after the course?'=>'Yes. The course prepares students for both employment and freelance opportunities.',
                'Is certification provided?'=>'Yes. Professional certification is awarded upon successful completion.',
            ]))
            . lp_cta('Become A Professional Makeup Artist',['480 Hours Professional Training','Bridal Makeup Expertise','HD & Airbrush Makeup','Portfolio Development','Placement Assistance','Industry Certification'],'Admissions Open Now — Apply Today & Start Your Journey Towards Becoming A Professional Makeup Artist.'),
    ];
}

/* ============================================================
 * 3. MASTER IN AESTHETICS
 * ============================================================ */
function build_master_aesthetics(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Master in Aesthetics at NIIB is a specialized professional program designed for individuals who aspire to build expertise in advanced skincare, beauty treatments, facial therapies, and aesthetic procedures.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This intensive course provides comprehensive training in skin analysis, advanced facial treatments, skin rejuvenation techniques, professional consultation, and modern aesthetic practices.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">The program is ideal for aspiring aestheticians, beauty therapists, skincare specialists, and professionals seeking to establish successful careers in salons, beauty clinics, wellness centers, and aesthetic studios.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-MAE-01','Duration'=>'2 Months','Training Hours'=>'240 Hours','Maximum Level'=>'Level 3','Departments Covered'=>'Advanced Skin Care · Aesthetics · Facial Therapies']);
    return [
        'title'=>'Master in Aesthetics','slug'=>'master-in-aesthetics','category'=>'aesthetics-laser','code'=>'NIIB-MAE-01',
        'duration'=>'2 Months','hours'=>'240 Hours','level'=>'Level 3',
        'short'=>'Build expertise in advanced skincare, facials, rejuvenation, hydra facial, chemical peel awareness and acne/pigmentation management in 240 hours.',
        'meta_title'=>'Master in Aesthetics Course – NIIB Institute','meta_desc'=>'NIIB Master in Aesthetics: 240-hour advanced skin care, facial therapies, hydra facial, chemical peel awareness and rejuvenation training with placement support.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels',lp_chip_list(['Skin Foundation','Professional Skin Analysis','Advanced Facial Treatments','Aesthetic Procedures','Client Consultation & Practice']))
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Advanced Skin Knowledge'=>'Develop a deep understanding of skin structure, skin conditions, and treatment planning.',
                'Professional Skin Analysis'=>'Learn to identify skin types, concerns, and customized treatment solutions.',
                'Advanced Facial Techniques'=>'Master professional facial therapies designed to improve skin health and appearance.',
                'Skin Rejuvenation Procedures'=>'Learn modern rejuvenation methods used in professional beauty clinics and salons.',
                'Hydra Facial Concepts'=>'Understand advanced cleansing, exfoliation, hydration, and skin enhancement procedures.',
                'Chemical Peel Awareness'=>'Gain knowledge of professional exfoliation procedures and treatment protocols.',
                'Anti-Aging Solutions'=>'Learn techniques used to improve skin texture, hydration, and youthful appearance.',
                'Client Consultation Skills'=>'Develop professional consultation techniques for personalized skincare services.',
                'Practical Learning Environment'=>'Gain confidence through extensive hands-on practice and live demonstrations.',
                'Industry-Focused Training'=>'Stay updated with current skincare trends and beauty industry standards.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Skin Science Fundamentals'=>['Skin Structure','Skin Functions','Skin Types','Skin Conditions','Skin Disorders Awareness','Professional Ethics'],
                'Professional Skin Analysis'=>['Client Consultation','Skin Assessment Techniques','Treatment Planning','Record Maintenance','Product Selection'],
                'Facial Treatments'=>['Basic Facials','Deep Cleansing Facial','Hydrating Facial','Brightening Facial','Anti-Aging Facial','Specialized Facial Protocols'],
                'Advanced Aesthetic Treatments'=>['Skin Rejuvenation Concepts','Advanced Skin Care Procedures','Exfoliation Techniques','Professional Treatment Planning','Skin Enhancement Procedures'],
                'Hydra Facial Concepts'=>['Skin Preparation','Cleansing Procedures','Exfoliation Techniques','Hydration Procedures','Post-Treatment Care'],
                'Chemical Peel Awareness'=>['Peel Fundamentals','Client Assessment','Treatment Precautions','Aftercare Recommendations'],
                'Acne & Pigmentation Management'=>['Acne Care Protocols','Pigmentation Management','Skin Brightening Techniques','Customized Treatment Plans'],
                'Professional Consultation & Client Care'=>['Client Communication','Treatment Recommendations','Product Advice','Customer Service Excellence','Client Retention Strategies'],
            ]))
            . lp_section('Practical','Practical Training','<p style="color:#49454e;margin:0 0 10px">Students receive extensive practical exposure through:</p><ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Demonstrations</li><li>Hands-On Training Sessions</li><li>Real Client Practice</li><li>Skin Analysis Workshops</li><li>Facial Treatment Practice</li><li>Industry Seminars</li><li>Professional Assessments</li><li>Skill Development Activities</li></ul>')
            . lp_section('Soft Skills','Professional Skills Development',lp_chip_list(['Client Consultation Skills','Skin Care Recommendations','Product Knowledge','Customer Service Excellence','Personal Branding','Professional Ethics','Business Development Fundamentals']))
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['Minimum Age: 15 Years','10th Pass','12th Pass','Graduates','Working Professionals','Beauty Enthusiasts']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Professional Aesthetician','Skin Therapist','Beauty Therapist','Skin Consultant','Facial Specialist','Beauty Advisor','Wellness Consultant','Skincare Trainer','Clinic Assistant','Beauty Clinic Executive','Freelance Skin Specialist','Beauty Entrepreneur']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Training','Expert Faculty','Advanced Infrastructure','Industry-Oriented Curriculum','Placement Assistance','Entrepreneurship Support','Professional Certification','Modern Treatment Knowledge']))
            . lp_section('Apply','How To Apply',lp_steps([
                'Submit Inquiry'=>'Contact our admissions team and complete your inquiry.',
                'Career Counselling'=>'Receive personalized guidance regarding your skincare career path.',
                'Complete Enrollment'=>'Submit documents and complete registration formalities.',
                'Start Your Training'=>'Begin your journey toward becoming a professional aesthetician.',
            ]))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes. The course starts with skin fundamentals and progresses to advanced aesthetic concepts.',
                'Will I learn professional skin analysis?'=>'Yes. Skin analysis is a core component of the program.',
                'Does the course include practical training?'=>'Yes. Practical learning forms a major part of the curriculum.',
                'Can I work in beauty clinics after this course?'=>'Yes. Graduates can work in salons, clinics, wellness centers, and skincare studios.',
                'Is certification provided?'=>'Yes. Students receive professional certification upon successful completion.',
                'Can I start my own skincare business?'=>'Yes. Entrepreneurship and client management concepts are included in the program.',
            ]))
            . lp_cta('Become A Professional Aesthetician',['240 Hours Professional Training','Advanced Skin Care Education','Live Client Practice','Industry Certification','Placement Assistance','Entrepreneurship Support'],'Admissions Open Now — Apply Today & Begin Your Journey Towards Becoming A Professional Skin & Aesthetics Expert.'),
    ];
}

/* ============================================================
 * 4. MASTER IN SEMI PERMANENT MAKEUP
 * ============================================================ */
function build_master_pmu(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Master in Semi Permanent Makeup at NIIB is a specialized professional program designed for aspiring beauty professionals who want to master the art of Semi Permanent Makeup (PMU).</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This advanced training program provides comprehensive education in Microblading, Lip Blush, BB Glow, Ombre Brows, Brow Pigmentation, and Scalp Micropigmentation techniques.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">The course is designed to meet modern beauty industry demands and prepare students for successful careers in beauty clinics, PMU studios, salons, and independent entrepreneurship.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-MSPM-01','Duration'=>'2 Months','Training Hours'=>'240 Hours','Maximum Level'=>'Level 3','Departments Covered'=>'Microblading · Lip Blush · BB Glow · Brow & Scalp PMU']);
    return [
        'title'=>'Master in Semi Permanent Makeup','slug'=>'master-in-semi-permanent-makeup','category'=>'semi-permanent-makeup','code'=>'NIIB-MSPM-01',
        'duration'=>'2 Months','hours'=>'240 Hours','level'=>'Level 3',
        'short'=>'Master Microblading, Lip Blush, BB Glow, Ombre Brows & Scalp Micropigmentation in 240 hours of focused PMU training with portfolio development.',
        'meta_title'=>'Master in Semi Permanent Makeup – NIIB Institute','meta_desc'=>'NIIB PMU master program: Microblading, Lip Blush, BB Glow, Ombre Brows, Scalp Micropigmentation with hands-on training and industry certification.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels',lp_chip_list(['PMU Foundation','Brow Enhancement Techniques','Lip Enhancement Techniques','BB Glow Procedures','Advanced PMU Applications','Professional Client Practice']))
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Complete Semi Permanent Makeup Education'=>'Master the most in-demand PMU services offered in modern beauty clinics and studios.',
                'Professional Microblading Training'=>'Learn eyebrow mapping, shaping, pigment selection, and natural-looking brow enhancement techniques.',
                'Lip Blush Expertise'=>'Master lip neutralization, lip tinting, lip blush, and lip color enhancement procedures.',
                'BB Glow Treatment Knowledge'=>'Learn advanced skin enhancement techniques for radiant and even-looking skin.',
                'Ombre & Powder Brows'=>'Develop expertise in modern eyebrow enhancement procedures.',
                'Scalp Micropigmentation Awareness'=>'Gain knowledge of scalp enhancement and pigmentation concepts.',
                'Color Theory & Pigment Selection'=>'Understand pigment science, skin undertones, and color correction techniques.',
                'Hygiene & Safety Protocols'=>'Learn industry-standard sanitation and client safety procedures.',
                'Practical Client Training'=>'Build confidence through supervised practical sessions and live demonstrations.',
                'Business & Branding Skills'=>'Learn client acquisition, consultation, pricing, and business growth strategies.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Introduction to Semi Permanent Makeup'=>['PMU Industry Overview','Professional Ethics','Client Consultation','Treatment Planning','Consent Procedures','Documentation Standards'],
                'Skin Anatomy & Color Theory'=>['Skin Structure','Skin Types','Healing Process','Pigment Science','Color Theory','Undertone Analysis'],
                'Brow Mapping & Design'=>['Face Shape Analysis','Brow Measurements','Symmetry Techniques','Brow Designing','Client Customization'],
                'Microblading Techniques'=>['Blade Knowledge','Stroke Patterns','Hair Stroke Creation','Pigment Implantation Concepts','Brow Enhancement Techniques','Touch-Up Procedures'],
                'Ombre & Powder Brows'=>['Ombre Brow Concepts','Powder Brow Effects','Gradient Techniques','Pigment Placement','Brow Perfection Methods'],
                'Lip Blush & Lip Tinting'=>['Lip Anatomy','Lip Neutralization Concepts','Lip Blush Techniques','Lip Color Enhancement','Lip Shaping Procedures'],
                'BB Glow Treatment'=>['Skin Preparation','BB Glow Concepts','Serum Application Methods','Treatment Protocols','Post Care Procedures'],
                'Scalp Micropigmentation Awareness'=>['Scalp Analysis','Hair Density Concepts','Pigmentation Awareness','Consultation Process'],
                'PMU Safety & Hygiene'=>['Sterilization Procedures','Infection Prevention','Client Safety Standards','Workplace Hygiene','Equipment Maintenance'],
                'Business & Professional Development'=>['Client Consultation','Pricing Strategies','Portfolio Development','Social Media Branding','Business Growth Techniques','Customer Retention'],
            ]))
            . lp_section('Practical','Practical Training','<p style="color:#49454e;margin:0 0 10px">At NIIB, practical learning is a major focus of the program. Students receive:</p><ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Demonstrations</li><li>Hands-On Practice Sessions</li><li>Brow Mapping Exercises</li><li>Microblading Practice</li><li>Lip Blush Demonstrations</li><li>BB Glow Workshops</li><li>Client Consultation Training</li><li>Portfolio Development</li></ul>')
            . lp_section('Soft Skills','Professional Skills Development',lp_chip_list(['Client Communication','Consultation Techniques','Treatment Planning','Product Knowledge','Personal Branding','Social Media Marketing','Freelancing Skills','Business Management Fundamentals']))
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['Minimum Age: 18 Years','10th Pass','12th Pass','Graduates','Beauty Professionals','Makeup Artists','Beauty Enthusiasts']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior PMU experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Semi Permanent Makeup Artist','Microblading Specialist','Lip Blush Artist','Brow Artist','PMU Technician','Beauty Clinic Specialist','Beauty Consultant','Freelance PMU Artist','Studio Professional','Beauty Trainer','PMU Entrepreneur','Beauty Business Owner']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Training','Expert PMU Trainers','Modern Infrastructure','Industry-Focused Curriculum','Portfolio Development','Placement Assistance','Entrepreneurship Support','Professional Certification']))
            . lp_section('Apply','How To Apply',lp_steps([
                'Submit Inquiry'=>'Connect with our admissions team and complete your inquiry.',
                'Career Counselling'=>'Receive guidance regarding PMU career opportunities.',
                'Complete Enrollment'=>'Submit required documents and complete registration.',
                'Start Your Training'=>'Begin your journey toward becoming a professional PMU artist.',
            ]))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes. The course starts from fundamentals and progresses to advanced PMU concepts.',
                'Will I learn Microblading?'=>'Yes. Microblading is one of the core modules of the program.',
                'Does the course include Lip Blush training?'=>'Yes. Students receive dedicated Lip Blush and Lip Tinting education.',
                'Is practical training included?'=>'Yes. Practical sessions form a major component of the course.',
                'Can I start my own PMU studio after this course?'=>'Yes. Business development and entrepreneurship concepts are included.',
                'Will I receive certification?'=>'Yes. Students receive professional certification upon successful completion.',
            ]))
            . lp_cta('Become A Professional PMU Artist',['240 Hours Professional Training','Microblading Expertise','Lip Blush Training','BB Glow Education','Portfolio Development','Industry Certification','Placement Assistance'],'Admissions Open Now — Apply Today & Start Your Journey Towards Becoming A Professional Semi Permanent Makeup Artist.'),
    ];
}

/* ============================================================
 * 5. MASTER IN NAIL TECHNOLOGY
 * ============================================================ */
function build_master_nail(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Master in Nail Technology at NIIB is a specialized professional program designed for individuals who want to build expertise in modern nail care, nail extensions, nail art, and advanced nail enhancement services.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This comprehensive course combines technical knowledge with practical training, enabling students to master industry-leading techniques in Acrylic Extensions, Gel Extensions, Nail Sculpting, 3D Nail Art, Luxury Nail Services, and Nail Studio Management.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">The program prepares students for successful careers in salons, nail studios, beauty clinics, fashion industries, and entrepreneurship.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-MNT-01','Duration'=>'4 Months','Training Hours'=>'480 Hours','Maximum Level'=>'Level 3','Departments Covered'=>'Nail Tech · Extensions · Art · Gel · Acrylic']);
    return [
        'title'=>'Master in Nail Technology','slug'=>'master-in-nail-technology','category'=>'nail-technology','code'=>'NIIB-MNT-01',
        'duration'=>'4 Months','hours'=>'480 Hours','level'=>'Level 3',
        'short'=>'480-hour Master in Nail Technology covering acrylic and gel extensions, 3D nail art, nail sculpting, luxury services and studio management.',
        'meta_title'=>'Master in Nail Technology Course – NIIB Institute','meta_desc'=>'NIIB Master in Nail Technology: acrylic, gel, 3D nail art, sculpting, luxury nail services with portfolio development and placement assistance.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels',lp_chip_list(['Nail Foundation','Nail Enhancement Techniques','Nail Extension Systems','Advanced Nail Art','Luxury Nail Services','Professional Client Practice']))
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Complete Nail Technology Education'=>'Master professional nail services from basic nail care to advanced nail artistry.',
                'Acrylic Extension Expertise'=>'Learn professional acrylic nail extension application, maintenance, and removal procedures.',
                'Gel Extension Mastery'=>'Develop expertise in gel nail extensions, overlays, and nail strengthening systems.',
                'Advanced Nail Art Techniques'=>'Create modern, trendy, and creative nail art designs using professional techniques.',
                '3D Nail Art Training'=>'Learn dimensional nail art designs using advanced decoration and sculpting methods.',
                'Nail Sculpting Skills'=>'Master professional nail shaping, extension building, and correction techniques.',
                'Luxury Nail Services'=>'Gain expertise in premium nail treatments and salon-quality services.',
                'Client Consultation Skills'=>'Learn personalized nail consultation and service recommendation techniques.',
                'Portfolio Development'=>'Build a professional portfolio showcasing your nail artistry and technical skills.',
                'Industry-Focused Learning'=>'Stay updated with the latest nail trends, products, and international techniques.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Introduction to Nail Technology'=>['Nail Industry Overview','Professional Ethics','Client Consultation','Nail Hygiene & Safety','Product Knowledge','Workplace Standards'],
                'Nail Science'=>['Nail Anatomy','Nail Structure','Nail Growth Cycle','Nail Disorders Awareness','Nail Health Assessment','Client Record Management'],
                'Basic Nail Services'=>['Professional Manicure','Professional Pedicure','Cuticle Care','Nail Shaping','Nail Polishing Techniques','Hand & Foot Care'],
                'Gel Nail Technology'=>['Gel Product Knowledge','Gel Application Techniques','Gel Overlays','Gel Extensions','Gel Maintenance','Gel Removal Procedures'],
                'Acrylic Nail Technology'=>['Acrylic Product Knowledge','Acrylic Extensions','Acrylic Overlays','Nail Building Techniques','Acrylic Maintenance','Safe Removal Methods'],
                'Nail Sculpting'=>['Sculpting Forms','Nail Architecture','Advanced Nail Shapes','Nail Corrections','Professional Finishing'],
                'Nail Art Techniques'=>['Basic Nail Art','Brush Nail Art','Marble Effects','Glitter Designs','Foil Nail Art','French Nail Art','Trend-Based Nail Designs'],
                'Advanced Nail Art'=>['3D Nail Art','Stone Work','Embellishments','Luxury Nail Designs','Bridal Nail Art','Fashion Nail Art'],
                'Nail Extension Maintenance'=>['Refill Techniques','Nail Repair Procedures','Extension Removal','Client Aftercare','Troubleshooting Techniques'],
                'Nail Studio Management'=>['Client Consultation','Pricing Strategies','Customer Service','Retail Sales','Social Media Promotion','Business Development'],
            ]))
            . lp_section('Practical','Practical Training','<p style="color:#49454e;margin:0 0 10px">At NIIB, students receive extensive practical exposure through:</p><ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Demonstrations</li><li>Hands-On Practice Sessions</li><li>Extension Application Practice</li><li>Nail Art Workshops</li><li>Real Client Exposure</li><li>Trend-Based Projects</li><li>Portfolio Development</li><li>Industry Masterclasses</li></ul>')
            . lp_section('Soft Skills','Professional Skills Development',lp_chip_list(['Client Communication','Consultation Skills','Nail Product Recommendations','Personal Branding','Social Media Marketing','Freelancing Skills','Customer Retention Strategies','Business Management Fundamentals']))
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['Minimum Age: 15 Years','10th Pass','12th Pass','Graduates','Working Professionals','Beauty Enthusiasts']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior nail industry experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Professional Nail Technician','Nail Artist','Nail Extension Specialist','Acrylic Nail Specialist','Gel Nail Specialist','Luxury Nail Artist','Nail Studio Executive','Nail Trainer','Beauty Consultant','Freelance Nail Artist','Nail Studio Owner','Beauty Entrepreneur']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Training','Expert Nail Trainers','Modern Infrastructure','Industry-Focused Curriculum','Portfolio Development','Placement Assistance','Entrepreneurship Support','Professional Certification']))
            . lp_section('Apply','How To Apply',lp_steps([
                'Submit Inquiry'=>'Connect with our admissions team and complete your inquiry.',
                'Career Counselling'=>'Receive guidance regarding nail technology career opportunities.',
                'Complete Enrollment'=>'Submit required documents and complete registration.',
                'Start Your Training'=>'Begin your journey toward becoming a professional nail technician.',
            ]))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes. The course starts with nail fundamentals and progresses to advanced professional techniques.',
                'Will I learn both acrylic and gel extensions?'=>'Yes. Students receive comprehensive training in both acrylic and gel nail systems.',
                'Is nail art included in the course?'=>'Yes. From basic nail art to advanced 3D and luxury nail art techniques are covered.',
                'Will I receive practical training?'=>'Yes. Practical sessions form a major part of the curriculum.',
                'Can I start my own nail studio after this course?'=>'Yes. Business and nail studio management concepts are included.',
                'Will I receive certification?'=>'Yes. Students receive professional certification upon successful completion.',
            ]))
            . lp_cta('Become A Professional Nail Artist',['480 Hours Professional Training','Acrylic & Gel Extension Mastery','Advanced Nail Art Techniques','Portfolio Development','Placement Assistance','Industry Certification'],'Admissions Open Now — Apply Today & Start Your Journey Towards Becoming A Professional Nail Technician & Nail Artist.'),
    ];
}

/* ============================================================
 * 6. MASTER IN WELLNESS & MASSAGES
 * ============================================================ */
function build_master_wellness(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Master in Wellness & Massages at NIIB is a specialized professional program designed for individuals who want to build expertise in wellness therapies, spa treatments, body massages, and holistic relaxation techniques.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This comprehensive course provides in-depth training in Swedish Massage, Deep Tissue Massage, Aromatherapy, Stone Therapy, Body Polishing, Spa Rituals, and Wellness Consultation.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">The program is ideal for aspiring wellness therapists, spa professionals, massage practitioners, and entrepreneurs who wish to establish successful careers in wellness centers, spas, resorts, salons, and luxury hospitality industries.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-MWM-01','Duration'=>'3 Months','Training Hours'=>'360 Hours','Maximum Level'=>'Level 3','Departments Covered'=>'Wellness · Spa · Massage · Aromatherapy']);
    return [
        'title'=>'Master in Wellness & Massages','slug'=>'master-in-wellness-massages','category'=>'wellness-massage','code'=>'NIIB-MWM-01',
        'duration'=>'3 Months','hours'=>'360 Hours','level'=>'Level 3',
        'short'=>'360-hour Wellness & Massage masterclass — Swedish, Deep Tissue, Aromatherapy, Stone Therapy and Spa Rituals — for spa, resort and wellness careers.',
        'meta_title'=>'Master in Wellness & Massages – NIIB Institute','meta_desc'=>'NIIB Master in Wellness & Massages: 360-hour Swedish, deep tissue, aromatherapy, stone therapy & spa ritual training with placement and entrepreneurship support.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels',lp_chip_list(['Wellness Foundation','Professional Massage Techniques','Spa Therapy Services','Advanced Body Treatments','Client Consultation & Wellness Planning','Professional Practice']))
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Complete Wellness Education'=>'Develop expertise in professional wellness therapies and body treatment procedures.',
                'Swedish Massage Mastery'=>'Learn the world\'s most popular massage therapy technique for relaxation and stress relief.',
                'Deep Tissue Therapy'=>'Understand advanced body massage techniques focused on muscle relaxation and tension release.',
                'Aromatherapy Treatments'=>'Learn the therapeutic use of essential oils in wellness and massage services.',
                'Hot Stone Therapy'=>'Gain knowledge of stone therapy concepts and wellness applications.',
                'Body Polishing & Spa Rituals'=>'Master luxury spa treatments and professional body care services.',
                'Stress Management Techniques'=>'Learn wellness practices designed to promote relaxation and overall well-being.',
                'Professional Consultation Skills'=>'Develop personalized wellness assessment and treatment recommendation techniques.',
                'Practical Client Training'=>'Build confidence through supervised practical sessions and live demonstrations.',
                'Hospitality & Spa Industry Exposure'=>'Prepare for opportunities within luxury spas, resorts, wellness centers, and salons.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Introduction to Wellness Therapy'=>['Wellness Industry Overview','Professional Ethics','Health & Safety Standards','Client Consultation','Wellness Assessment','Professional Conduct'],
                'Human Anatomy & Physiology'=>['Body Structure Fundamentals','Muscular System Basics','Circulatory System Awareness','Stress & Relaxation Concepts','Body Assessment Techniques'],
                'Swedish Massage'=>['Massage Fundamentals','Effleurage Techniques','Petrissage Techniques','Friction Techniques','Relaxation Protocols','Client Comfort Procedures'],
                'Deep Tissue Massage'=>['Deep Pressure Techniques','Muscle Relaxation Concepts','Stress Relief Procedures','Body Recovery Awareness','Advanced Massage Methods'],
                'Aromatherapy'=>['Essential Oil Knowledge','Oil Blending Concepts','Aromatherapy Applications','Wellness Treatments','Relaxation Techniques'],
                'Hot Stone Therapy'=>['Stone Therapy Concepts','Stone Placement Techniques','Heat Therapy Awareness','Relaxation Procedures','Safety Guidelines'],
                'Body Treatments'=>['Body Polishing','Body Scrubs','Body Wrap Concepts','Skin Conditioning Treatments','Wellness Rituals'],
                'Spa Therapy Services'=>['Spa Operations','Spa Treatment Protocols','Luxury Wellness Services','Guest Care Standards','Spa Consultation Techniques'],
                'Professional Client Care'=>['Client Communication','Consultation Techniques','Treatment Recommendations','Customer Service Excellence','Client Retention Strategies'],
                'Business & Career Development'=>['Personal Branding','Wellness Business Concepts','Social Media Promotion','Professional Portfolio Development','Entrepreneurship Fundamentals'],
            ]))
            . lp_section('Practical','Practical Training','<p style="color:#49454e;margin:0 0 10px">At NIIB, practical training is the foundation of professional development. Students receive:</p><ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Demonstrations</li><li>Hands-On Massage Practice</li><li>Wellness Workshops</li><li>Spa Treatment Simulations</li><li>Real Client Exposure</li><li>Industry Seminars</li><li>Professional Assessments</li><li>Skill Development Activities</li></ul>')
            . lp_section('Soft Skills','Professional Skills Development',lp_chip_list(['Professional Communication','Client Consultation','Wellness Planning','Customer Service Excellence','Personal Branding','Business Development','Teamwork & Professional Conduct']))
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['Minimum Age: 15 Years','10th Pass','12th Pass','Graduates','Working Professionals','Wellness Enthusiasts']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior wellness industry experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Wellness Therapist','Massage Therapist','Spa Therapist','Aromatherapy Consultant','Body Treatment Specialist','Wellness Consultant','Spa Executive','Resort Spa Professional','Salon Wellness Expert','Holistic Wellness Practitioner','Wellness Trainer','Wellness Entrepreneur']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Training','Expert Trainers','Modern Infrastructure','Industry-Oriented Curriculum','Placement Assistance','Entrepreneurship Support','Professional Certification','Real Client Exposure']))
            . lp_section('Apply','How To Apply',lp_steps([
                'Submit Inquiry'=>'Connect with our admissions team and complete your inquiry.',
                'Career Counselling'=>'Receive personalized guidance regarding wellness industry opportunities.',
                'Complete Enrollment'=>'Submit required documents and complete registration.',
                'Start Your Training'=>'Begin your journey toward becoming a professional wellness therapist.',
            ]))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes. The course starts with wellness fundamentals and gradually progresses to advanced massage techniques.',
                'Will I learn different types of massage therapies?'=>'Yes. Swedish Massage, Deep Tissue Massage, Aromatherapy, and Spa Therapy concepts are included.',
                'Does the course include practical training?'=>'Yes. Practical learning forms a major component of the curriculum.',
                'Can I work in spas and wellness centers after this course?'=>'Yes. Graduates can work in spas, salons, resorts, wellness centers, and hospitality industries.',
                'Is certification provided?'=>'Yes. Students receive professional certification upon successful completion.',
                'Can I start my own wellness business?'=>'Yes. Business development and entrepreneurship concepts are included.',
            ]))
            . lp_cta('Become A Professional Wellness Therapist',['360 Hours Professional Training','Spa & Massage Expertise','Hands-On Practical Learning','Industry Certification','Placement Assistance','Entrepreneurship Support'],'Admissions Open Now — Apply Today & Start Your Journey Towards Becoming A Professional Wellness & Spa Expert.'),
    ];
}

/* ============================================================
 * 7. MASTER IN COOKING & CULINARY ARTS
 * ============================================================ */
function build_master_culinary(): array {
    $intro = '<p style="font-size:18px;color:#49454e;line-height:1.7;margin:0 0 14px">The Master in Cooking & Culinary Arts at NIIB is a comprehensive professional program designed for aspiring chefs, culinary enthusiasts, food entrepreneurs, and hospitality professionals.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0 0 14px">This advanced course combines culinary theory with extensive hands-on kitchen training — food preparation, cooking techniques, bakery and confectionery, menu planning, food presentation, and kitchen management.</p>'
           . '<p style="font-size:16px;color:#49454e;line-height:1.7;margin:0">The program prepares graduates for successful careers in hotels, restaurants, bakeries, catering businesses, cruise lines, and food entrepreneurship.</p>';
    $meta = lp_meta_strip(['Course Code'=>'NIIB-MCC-01','Duration'=>'1 Year','Training Hours'=>'1200 Hours','Maximum Level'=>'Level 4','Departments Covered'=>'Cookery · Bakery · Continental · Indian · International']);
    return [
        'title'=>'Master in Cooking & Culinary Arts','slug'=>'master-in-cooking-culinary-arts','category'=>'cooking-culinary','code'=>'NIIB-MCC-01',
        'duration'=>'1 Year','hours'=>'1200 Hours','level'=>'Level 4',
        'short'=>'1-year, 1200-hour Master in Culinary Arts covering Indian, Continental, International cuisines, bakery, confectionery, presentation & kitchen management.',
        'meta_title'=>'Master in Cooking & Culinary Arts – NIIB Institute','meta_desc'=>'NIIB Master in Cooking & Culinary Arts: 1-year, 1200-hour chef training in Indian, Continental, International cuisines, bakery, confectionery and kitchen management.',
        'html'=>$meta
            . lp_section('Overview','Programme Overview',$intro)
            . lp_section('Levels','Course Levels',lp_chip_list(['Culinary Foundation','Professional Food Production','Bakery & Confectionery','International Cuisine','Advanced Culinary Techniques','Kitchen Management','Entrepreneurship Development']))
            . lp_section('Highlights','Key Takeaways & Highlights',lp_highlights([
                'Complete Culinary Education'=>'Develop expertise in professional cooking, food production, bakery, and international cuisines.',
                'Professional Kitchen Training'=>'Learn industry-standard cooking techniques through extensive practical sessions.',
                'Indian & International Cuisine'=>'Master authentic recipes and cooking methods from multiple culinary traditions.',
                'Bakery & Confectionery Expertise'=>'Learn cakes, pastries, breads, desserts, chocolates, and advanced baking techniques.',
                'Food Presentation & Plating'=>'Develop creative food styling and presentation skills used in professional kitchens.',
                'Menu Planning Skills'=>'Learn menu creation, costing, and recipe standardization.',
                'Kitchen Management'=>'Understand kitchen operations, inventory control, food safety, and team coordination.',
                'Entrepreneurship Development'=>'Gain knowledge to start and manage your own food business, bakery, café, or catering service.',
                'Industry Exposure'=>'Participate in workshops, culinary competitions, food festivals, and masterclasses.',
                'Practical Learning Environment'=>'Build confidence through daily kitchen practice and real-world culinary projects.',
            ]))
            . lp_section('Curriculum','Course Modules',lp_modules([
                'Culinary Foundation'=>['Introduction to Culinary Arts','Kitchen Organization','Professional Kitchen Etiquette','Culinary Terminology','Food Safety & Hygiene','Kitchen Equipment Handling'],
                'Basic Food Production'=>['Cutting Techniques','Stocks & Sauces','Soups Preparation','Basic Cooking Methods','Vegetable Cookery','Food Preparation Skills'],
                'Indian Cuisine'=>['North Indian Cuisine','South Indian Cuisine','Punjabi Cuisine','Regional Specialties','Traditional Gravies','Tandoori Preparations'],
                'Continental Cuisine'=>['Continental Soups','Sauces & Dressings','Pasta Preparation','Pizza Making','Grilled Dishes','European Cuisine Fundamentals'],
                'International Cuisine'=>['Italian Cuisine','Chinese Cuisine','Thai Cuisine','Mexican Cuisine','Fusion Cooking','Global Culinary Trends'],
                'Bakery & Confectionery'=>['Bread Making','Cakes & Gateaux','Pastries','Cookies & Biscuits','Chocolates','Dessert Preparation'],
                'Advanced Bakery'=>['Fondant Cakes','Wedding Cakes','Designer Cakes','Artisan Breads','Modern Desserts','Dessert Presentation'],
                'Food Presentation & Plating'=>['Plate Designing','Garnishing Techniques','Food Styling','Presentation Standards','Fine Dining Concepts'],
                'Kitchen Management'=>['Inventory Control','Food Costing','Menu Planning','Kitchen Operations','Staff Coordination','Quality Control'],
                'Culinary Entrepreneurship'=>['Restaurant Business Fundamentals','Café Setup Concepts','Catering Management','Food Business Marketing','Social Media Branding','Business Development'],
            ]))
            . lp_section('Practical','Practical Training','<p style="color:#49454e;margin:0 0 10px">At NIIB, practical kitchen training forms the core of the program. Students receive:</p><ul style="margin:0;padding-left:20px;color:#49454e;line-height:1.8"><li>Live Cooking Demonstrations</li><li>Hands-On Kitchen Training</li><li>Bakery Workshops</li><li>Recipe Development Sessions</li><li>Food Presentation Projects</li><li>Industry Seminars</li><li>Culinary Competitions</li><li>Professional Assessments</li></ul>')
            . lp_section('Soft Skills','Professional Skills Development',lp_chip_list(['Kitchen Communication','Team Coordination','Customer Service Awareness','Menu Planning','Food Costing','Business Development','Professional Work Ethics']))
            . lp_section('Who It\'s For','Eligibility',lp_chip_list(['Minimum Age: 15 Years','10th Pass','12th Pass','Graduates','Working Professionals','Food Enthusiasts']) . '<p style="margin:14px 0 0;font-size:14px;color:#49454e">No prior culinary experience required.</p>')
            . lp_section('Careers','Career Opportunities',lp_chip_list(['Professional Chef','Commis Chef','Bakery Chef','Pastry Chef','Sous Chef','Catering Professional','Kitchen Executive','Restaurant Professional','Food Stylist','Culinary Trainer','Cruise Line Chef','Hotel Chef','Café Owner','Bakery Owner','Catering Business Owner','Food Entrepreneur']))
            . lp_section('Why NIIB','Why Choose NIIB?',lp_chip_list(['100% Practical Kitchen Training','Expert Chef Trainers','Modern Kitchen Infrastructure','Industry-Oriented Curriculum','Placement Assistance','Entrepreneurship Support','Professional Certification','Industry Exposure']))
            . lp_section('Apply','How To Apply',lp_steps([
                'Submit Inquiry'=>'Connect with our admissions team and complete your inquiry.',
                'Career Counselling'=>'Receive personalized guidance regarding culinary career opportunities.',
                'Complete Enrollment'=>'Submit required documents and complete registration.',
                'Start Your Training'=>'Begin your journey toward becoming a professional culinary expert.',
            ]))
            . lp_section('FAQ','Frequently Asked Questions',lp_faqs([
                'Is this course suitable for beginners?'=>'Yes. The course starts with culinary fundamentals and progresses to advanced professional techniques.',
                'Will I learn bakery and confectionery?'=>'Yes. Bakery and confectionery training is a major component of the program.',
                'Does the course include practical kitchen training?'=>'Yes. Practical learning forms the foundation of the course.',
                'Can I start my own bakery or café after this course?'=>'Yes. Entrepreneurship and food business management concepts are included.',
                'Is certification provided?'=>'Yes. Students receive professional certification upon successful completion.',
                'Are placement opportunities available?'=>'Yes. NIIB provides career guidance and placement assistance.',
            ]))
            . lp_cta('Become A Professional Chef & Culinary Expert',['1200 Hours Professional Training','Indian & International Cuisine','Bakery & Confectionery Mastery','Professional Kitchen Experience','Placement Assistance','Industry Certification','Entrepreneurship Support'],'Admissions Open Now — Apply Today & Start Your Journey Towards Becoming A Professional Chef & Culinary Entrepreneur.'),
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
        'eligibility'       => 'No prior experience required. Open to students, job seekers, working professionals, and enthusiasts.',
        'level'             => $c['level'],
        'language'          => 'English / Hindi',
        'is_featured'       => 1,
        'meta_title'        => $c['meta_title'],
        'meta_description'  => $c['meta_desc'],
        'meta_keywords'     => $c['title'] . ', ' . $c['code'] . ', NIIB Institute, master course, beauty training',
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
$catMap = [
    'hair-design'           => $cat_hair,
    'makeup-artistry'       => $cat_makeup,
    'aesthetics-laser'      => $cat_aesth,
    'semi-permanent-makeup' => $cat_pmu,
    'nail-technology'       => $cat_nail,
    'wellness-massage'      => $cat_well,
    'cooking-culinary'      => $cat_cook,
];
$courses = [
    build_master_hair(),
    build_master_makeup(),
    build_master_aesthetics(),
    build_master_pmu(),
    build_master_nail(),
    build_master_wellness(),
    build_master_culinary(),
];
$results = [];
foreach ($courses as $c) {
    $catId = $catMap[$c['category']] ?? null;
    $r = upsert_course($pdo, $c, (int)$catId);
    $results[] = ['title'=>$c['title'],'slug'=>$c['slug'],'result'=>$r];
}

/* ---------------- Report ---------------- */
?><!doctype html><html><head><meta charset="utf-8"><title>NIIB Master Course Import — Done</title>
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
<h1>✔ Import complete</h1>
<p class="lead">7 Master / Specialization courses processed. Categories created or reused. All content is editable from <a href="<?= e(ADMIN_URL) ?>/modules/courses.php">Admin → Courses</a>.</p>
<?php foreach ($results as $r): ?>
<div class="row">
  <div>
    <strong style="display:block;color:#170338"><?= htmlspecialchars($r['title']) ?></strong>
    <code style="color:#49454e;font-size:13px">/<?= htmlspecialchars($r['slug']) ?></code>
  </div>
  <span class="tag <?= $r['result']['status']==='inserted'?'ins':'upd' ?>"><?= htmlspecialchars($r['result']['status']) ?></span>
</div>
<?php endforeach; ?>
<div class="warn">
  <strong>Next — upload course images:</strong> open each course in <a href="<?= e(ADMIN_URL) ?>/modules/courses.php">Admin → Courses</a> and upload the featured image from your PDF.<br><br>
  <strong>Security:</strong> delete <code>import_specialization_courses.php</code> from the project root now that the import is done.
</div>
<a class="btn" href="<?= e(ADMIN_URL) ?>/modules/courses.php">Open Admin → Courses</a>
<a class="btn" style="background:#fe5e1e;margin-left:8px" href="<?= e(SITE_URL) ?>/courses">View Public Courses Page</a>
</div>
</body></html>

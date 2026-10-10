<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/crud.php';
$page_title='Pages'; $active='pages';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$pdo = db();

/* ---------------------------------------------------------------
 * Auto-migration: add page_type + template_data columns once
 * --------------------------------------------------------------- */
try {
    $cols = $pdo->query("SHOW COLUMNS FROM pages")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('page_type', $cols, true)) {
        $pdo->exec("ALTER TABLE pages ADD COLUMN page_type VARCHAR(20) NOT NULL DEFAULT 'informative' AFTER slug");
    }
    if (!in_array('template_data', $cols, true)) {
        $pdo->exec("ALTER TABLE pages ADD COLUMN template_data LONGTEXT NULL AFTER content");
    }
} catch (Throwable $e) { /* ignore — column may exist */ }

/* ---------------------------------------------------------------
 * Helpers: parse repeater arrays from form
 * --------------------------------------------------------------- */
function loc_collect_repeater($prefix, $keys) {
    $out = [];
    if (empty($_POST[$prefix]) || !is_array($_POST[$prefix])) return $out;
    $rows = $_POST[$prefix];
    $first = $keys[0];
    if (!isset($rows[$first]) || !is_array($rows[$first])) return $out;
    $count = count($rows[$first]);
    for ($i = 0; $i < $count; $i++) {
        $item = [];
        $hasValue = false;
        foreach ($keys as $k) {
            $v = trim((string)($rows[$k][$i] ?? ''));
            $item[$k] = $v;
            if ($v !== '') $hasValue = true;
        }
        // existing image fallback for repeater image fields
        foreach ($keys as $k) {
            if (substr($k, -6) === '_image' || $k === 'image') {
                $existing = $rows['existing_'.$k][$i] ?? '';
                if (!empty($_FILES[$prefix.'_'.$k]['name'][$i]) && $_FILES[$prefix.'_'.$k]['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpField = $prefix.'_'.$k.'_'.$i;
                    $_FILES[$tmpField] = [
                        'name'     => $_FILES[$prefix.'_'.$k]['name'][$i],
                        'type'     => $_FILES[$prefix.'_'.$k]['type'][$i],
                        'tmp_name' => $_FILES[$prefix.'_'.$k]['tmp_name'][$i],
                        'error'    => $_FILES[$prefix.'_'.$k]['error'][$i],
                        'size'     => $_FILES[$prefix.'_'.$k]['size'][$i],
                    ];
                    $u = upload_file($tmpField, 'pages');
                    if ($u['ok']) { $item[$k] = $u['path']; $hasValue = true; }
                } elseif ($existing !== '') {
                    $item[$k] = $existing;
                    $hasValue = true;
                }
            }
        }
        if ($hasValue) $out[] = $item;
    }
    return $out;
}

/* ---------------------------------------------------------------
 * Bulk actions: delete / activate / deactivate selected pages
 * Runs BEFORE the add/edit save handler; guarded by bulk_action + CSRF.
 * --------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['bulk_action']) && csrf_verify()) {
    $ids  = array_values(array_filter(array_map('intval', (array)($_POST['ids'] ?? [])), function($v){ return $v > 0; }));
    $bulk = $_POST['bulk_action'];
    if (empty($ids)) {
        set_flash('error','No pages selected.');
    } elseif ($bulk === 'delete') {
        $n = 0; foreach ($ids as $pid) { crud_delete('pages', $pid, ['banner_image']); $n++; }
        set_flash('success', $n.' page'.($n===1?'':'s').' deleted.');
    } elseif ($bulk === 'activate' || $bulk === 'deactivate') {
        $st = $bulk === 'activate' ? 1 : 0;
        $in = implode(',', array_fill(0, count($ids), '?'));
        $pdo->prepare("UPDATE pages SET status=? WHERE id IN ($in)")->execute(array_merge([$st], $ids));
        set_flash('success', count($ids).' page'.(count($ids)===1?'':'s').' '.($st?'activated':'deactivated').'.');
    } else {
        set_flash('error','Unknown bulk action.');
    }
    redirect(ADMIN_URL.'/modules/pages.php');
}

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    try {
        $editId = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        if ($title==='') throw new Exception('Title is required.');
        $slug = trim($_POST['slug'] ?? '') ?: $title;
        $slug = unique_slug('pages',$slug,$editId);
        $page_type = ($_POST['page_type'] ?? 'informative') === 'location' ? 'location' : 'informative';

        $data = [
            'title'=>$title,
            'slug'=>$slug,
            'page_type'=>$page_type,
            'content'=>$_POST['content'] ?? '',
            'meta_title'=>trim($_POST['meta_title'] ?? ''),
            'meta_description'=>trim($_POST['meta_description'] ?? ''),
            'meta_keywords'=>trim($_POST['meta_keywords'] ?? ''),
            'canonical_url'=>trim($_POST['canonical_url'] ?? ''),
            'schema_code'=>$_POST['schema_code'] ?? '',
            'status'=>isset($_POST['status'])?1:0,
            'sort_order'=>(int)($_POST['sort_order'] ?? 0),
        ];
        // Banner image: prefer direct upload, then media library, then existing
        if (!empty($_FILES['banner_image']['name'])) {
            $u=upload_file('banner_image','pages');
            if($u['ok']) $data['banner_image']=$u['path'];
        } elseif (!empty($_POST['banner_image'])) {
            $data['banner_image']=trim($_POST['banner_image']);
        } elseif (!empty($_POST['existing_banner_image'])) {
            $data['banner_image']=$_POST['existing_banner_image'];
        }

        // Collect location template fields into JSON
        if ($page_type === 'location') {
            $loc = $_POST['loc'] ?? [];

            // Fetch existing template_data if editing
            $existing_td = [];
            if ($editId > 0) {
                $curRow = crud_fetch('pages', $editId);
                if (!empty($curRow['template_data'])) {
                    $existing_td = json_decode($curRow['template_data'], true) ?: [];
                }
            }

            // Intro image: prefer direct upload, then media library, then existing
            $intro_image = $loc['intro_image_existing'] ?? '';
            if (!empty($_FILES['loc_intro_image']['name'])) {
                $u = upload_file('loc_intro_image','pages');
                if ($u['ok']) $intro_image = $u['path'];
            } elseif (!empty($loc['intro_image_existing'])) {
                $intro_image = trim($loc['intro_image_existing']);
            }

            // Gallery images (existing from media library + direct uploads)
            $gallery = [];
            if (!empty($loc['gallery_existing']) && is_array($loc['gallery_existing'])) {
                foreach ($loc['gallery_existing'] as $g) {
                    $g = trim($g);
                    if ($g !== '' && !in_array($g, $gallery)) {
                        $gallery[] = $g;
                    }
                }
            }
            if (!empty($_FILES['loc_gallery']['name']) && is_array($_FILES['loc_gallery']['name'])) {
                foreach ($_FILES['loc_gallery']['name'] as $i => $name) {
                    if (!$name || $_FILES['loc_gallery']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $tmp = 'loc_gallery_'.$i;
                    $_FILES[$tmp] = [
                        'name'=>$name,
                        'type'=>$_FILES['loc_gallery']['type'][$i],
                        'tmp_name'=>$_FILES['loc_gallery']['tmp_name'][$i],
                        'error'=>$_FILES['loc_gallery']['error'][$i],
                        'size'=>$_FILES['loc_gallery']['size'][$i],
                    ];
                    $u = upload_file($tmp,'pages');
                    if ($u['ok'] && !in_array($u['path'], $gallery)) {
                        $gallery[] = $u['path'];
                    }
                }
            }

            // Known logo mappings for standard accreditation bodies
            $knownAccredLogos = [
                'nsdc'               => 'uploads/accreditations/nsdc.jpg',
                'b&wssc'             => 'uploads/accreditations/bwssc.png',
                'bwssc'              => 'uploads/accreditations/bwssc.png',
                'iao'                => 'uploads/accreditations/iao-new.jpg',
                'pathway to success' => 'uploads/accreditations/pathway-to-success.jpg',
            ];

            // Build map from existing accreditations if any had custom logos
            $existingLogoMap = [];
            if (!empty($existing_td['accreditations']) && is_array($existing_td['accreditations'])) {
                foreach ($existing_td['accreditations'] as $ea) {
                    if (is_array($ea) && !empty($ea['label']) && !empty($ea['logo'])) {
                        $existingLogoMap[strtolower(trim($ea['label']))] = $ea['logo'];
                    }
                }
            }

            $rawAccred = trim($loc['accreditations'] ?? '');
            if ($rawAccred === '' || stripos($rawAccred, 'warning') !== false || stripos($rawAccred, 'array to string') !== false) {
                $savedAccreditations = [
                    ['label' => 'NSDC', 'logo' => 'uploads/accreditations/nsdc.jpg'],
                    ['label' => 'B&WSSC', 'logo' => 'uploads/accreditations/bwssc.png'],
                    ['label' => 'IAO', 'logo' => 'uploads/accreditations/iao-new.jpg'],
                    ['label' => 'Pathway to Success', 'logo' => 'uploads/accreditations/pathway-to-success.jpg'],
                ];
            } else {
                $items = array_values(array_filter(array_map('trim', explode(',', $rawAccred))));
                $savedAccreditations = [];
                foreach ($items as $item) {
                    $lower = strtolower($item);
                    $logo = '';
                    if (isset($knownAccredLogos[$lower])) {
                        $logo = $knownAccredLogos[$lower];
                    } elseif (isset($existingLogoMap[$lower])) {
                        $logo = $existingLogoMap[$lower];
                    }
                    $savedAccreditations[] = [
                        'label' => $item,
                        'logo'  => $logo,
                    ];
                }
            }

            $template = [
                'hero' => [
                    'badge'    => trim($loc['hero_badge'] ?? ''),
                    'title'    => trim($loc['hero_title'] ?? ''),
                    'cta1'     => trim($loc['hero_cta1'] ?? ''),
                    'cta1_url' => trim($loc['hero_cta1_url'] ?? ''),
                    'cta2'     => trim($loc['hero_cta2'] ?? ''),
                    'cta2_url' => trim($loc['hero_cta2_url'] ?? ''),
                ],
                'intro' => [
                    'heading'      => trim($loc['intro_heading'] ?? ''),
                    'para1'        => trim($loc['intro_para1'] ?? ''),
                    'para2'        => trim($loc['intro_para2'] ?? ''),
                    'stat1_value'  => trim($loc['stat1_value'] ?? ''),
                    'stat1_label'  => trim($loc['stat1_label'] ?? ''),
                    'stat2_value'  => trim($loc['stat2_value'] ?? ''),
                    'stat2_label'  => trim($loc['stat2_label'] ?? ''),
                    'image'        => $intro_image,
                    'quote'        => trim($loc['intro_quote'] ?? ''),
                ],
                'courses' => [
                    'tagline'    => trim($loc['courses_tagline'] ?? ''),
                    'heading'    => trim($loc['courses_heading'] ?? ''),
                    'course_ids' => array_values(array_filter(array_map('intval', (array)($_POST['course_ids'] ?? [])))),
                ],
                'career' => [
                    'heading'      => trim($loc['career_heading'] ?? ''),
                    'subheading'   => trim($loc['career_subheading'] ?? ''),
                    'feature_icon' => trim($loc['career_feature_icon'] ?? ''),
                    'feature_title'=> trim($loc['career_feature_title'] ?? ''),
                    'feature_desc' => trim($loc['career_feature_desc'] ?? ''),
                    'cards'        => loc_collect_repeater('career_card', ['icon','title','desc']),
                ],
                'gallery' => [
                    'heading'    => trim($loc['gallery_heading'] ?? ''),
                    'subheading' => trim($loc['gallery_subheading'] ?? ''),
                    'images'     => $gallery,
                ],
                'accreditations' => $savedAccreditations,
                'contact' => [
                    'heading'    => trim($loc['contact_heading'] ?? ''),
                    'subheading' => trim($loc['contact_subheading'] ?? ''),
                    'phone'      => trim($loc['contact_phone'] ?? ''),
                    'email'      => trim($loc['contact_email'] ?? ''),
                    'address'    => trim($loc['contact_address'] ?? ''),
                    'cities'     => array_values(array_filter(array_map('trim', explode(',', $loc['contact_cities'] ?? '')))),
                    'map_link'   => trim($loc['contact_map_link'] ?? ($existing_td['contact']['map_link'] ?? '')),
                ],
                'extra_sections' => isset($loc['extra_sections']) ? (int)$loc['extra_sections'] : ($existing_td['extra_sections'] ?? 1),
            ];

            // Preserve content_block if present
            if (!empty($existing_td['content_block'])) {
                $template['content_block'] = $existing_td['content_block'];
            }

            $data['template_data'] = json_encode($template, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $data['template_data'] = null;
        }

        if ($editId>0) {
            $sets=[];$vals=[];foreach($data as $k=>$v){$sets[]="`$k`=?";$vals[]=$v;}$vals[]=$editId;
            $pdo->prepare("UPDATE pages SET ".implode(',',$sets)." WHERE id=?")->execute($vals);
        } else {
            $cols=array_keys($data);$place=array_fill(0,count($cols),'?');
            $pdo->prepare("INSERT INTO pages (`".implode('`,`',$cols)."`) VALUES (".implode(',',$place).")")->execute(array_values($data));
        }
        set_flash('success','Saved.');
    } catch(Throwable $e){ set_flash('error',$e->getMessage()); }
    redirect(ADMIN_URL.'/modules/pages.php');
}

if ($action==='delete' && $id>0) { crud_delete('pages',$id,['banner_image']); set_flash('success','Deleted.'); redirect(ADMIN_URL.'/modules/pages.php'); }
if ($action==='toggle' && $id>0) { crud_toggle('pages',$id); redirect(ADMIN_URL.'/modules/pages.php'); }
if ($action==='duplicate' && $id>0) {
    try {
        $src = crud_fetch('pages',$id);
        if (!$src) throw new Exception('Source page not found.');
        unset($src['id'], $src['created_at'], $src['updated_at']);
        $src['title']      = trim($src['title']).' (Copy)';
        $src['slug']       = unique_slug('pages', $src['title']);
        $src['status']     = 0; // duplicate starts as draft so it doesn't go live by accident
        $src['sort_order'] = (int)($src['sort_order'] ?? 0);
        $cols  = array_keys($src);
        $place = array_fill(0, count($cols), '?');
        $pdo->prepare("INSERT INTO pages (`".implode('`,`',$cols)."`) VALUES (".implode(',',$place).")")
            ->execute(array_values($src));
        $newId = (int)$pdo->lastInsertId();
        set_flash('success','Page duplicated as draft. Review and publish when ready.');
        redirect(ADMIN_URL.'/modules/pages.php?action=edit&id='.$newId);
    } catch (Throwable $e) {
        set_flash('error','Duplicate failed: '.$e->getMessage());
        redirect(ADMIN_URL.'/modules/pages.php');
    }
}

include __DIR__.'/../includes/header.php';

if ($action==='add' || $action==='edit') {
    $p = [
        'id'=>0,'title'=>'','slug'=>'','page_type'=>'informative','banner_image'=>'','content'=>'','template_data'=>'',
        'meta_title'=>'','meta_description'=>'','meta_keywords'=>'','canonical_url'=>'','schema_code'=>'',
        'status'=>1,'sort_order'=>0
    ];
    if ($action==='edit' && $id>0) { $r=crud_fetch('pages',$id); if($r) $p=array_merge($p,$r); }

    // Decode template data for location pages (use sensible defaults so the form renders even for new pages)
    $td = [];
    if (!empty($p['template_data'])) {
        $dec = json_decode($p['template_data'], true);
        if (is_array($dec)) $td = $dec;
    }
    $td = array_replace_recursive([
        'hero'    => ['badge'=>'','title'=>'','cta1'=>'Download Brochure','cta1_url'=>'#','cta2'=>'Our Courses','cta2_url'=>'#'],
        'intro'   => ['heading'=>'','para1'=>'','para2'=>'','stat1_value'=>'','stat1_label'=>'','stat2_value'=>'','stat2_label'=>'','image'=>'','quote'=>''],
        'courses' => ['tagline'=>'Our Expertise','heading'=>'Signature Courses','course_ids'=>[]],
        'career'  => ['heading'=>'Design Your Career Path','subheading'=>'','feature_icon'=>'brush','feature_title'=>'','feature_desc'=>'','cards'=>[]],
        'gallery' => ['heading'=>'Recognized Excellence','subheading'=>'','images'=>[]],
        'accreditations' => [],
        'contact' => ['heading'=>'Have Questions? Get in touch!','subheading'=>'','phone'=>'','email'=>'','address'=>'','cities'=>[]],
    ], $td);

    // Load all active courses for the picker
    $all_courses = $pdo->query("SELECT c.id, c.title, c.slug, c.image, cc.name AS category_name FROM courses c LEFT JOIN course_categories cc ON cc.id = c.category_id WHERE c.status=1 ORDER BY c.is_featured DESC, c.title ASC")->fetchAll();
    $selected_course_ids = array_map('intval', (array)($td['courses']['course_ids'] ?? []));
?>
<div class="page-header">
    <h4><?= $action==='edit'?'Edit Page':'Add Page' ?></h4>
    <a class="btn btn-outline-secondary btn-sm" href="<?= ADMIN_URL ?>/modules/pages.php"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<form method="post" enctype="multipart/form-data" id="pageForm">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
<input type="hidden" name="existing_banner_image" value="<?= e($p['banner_image']) ?>">

<div class="row g-3">
<div class="col-lg-8">

    <div class="card mb-3"><div class="card-header d-flex justify-content-between align-items-center">
        <span>Page Type</span>
    </div><div class="card-body">
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Type *</label>
                <select name="page_type" id="page_type" class="form-select">
                    <option value="informative" <?= $p['page_type']==='informative'?'selected':'' ?>>Informative (default theme)</option>
                    <option value="location"    <?= $p['page_type']==='location'?'selected':'' ?>>Location (custom landing template)</option>
                </select>
                <small class="text-muted">URL will be <code><?= e(SITE_URL) ?>/{slug}</code></small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Title *</label>
                <input id="pgt" name="title" class="form-control" value="<?= e($p['title']) ?>" required>
            </div>
            <div class="col-md-12">
                <label class="form-label">Slug</label>
                <input name="slug" data-slug-source="pgt" class="form-control" value="<?= e($p['slug']) ?>" placeholder="auto-generated from title">
            </div>
        </div>
    </div></div>

    <!-- INFORMATIVE: free-form HTML content -->
    <div class="card mb-3" id="block-informative">
        <div class="card-header">Content (Informative Page)</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">HTML Content</label>
                <textarea name="content" class="form-control rich-editor" rows="14"><?= e($p['content']) ?></textarea>
                <small class="text-muted">Rendered inside your normal theme header/footer.</small>
            </div>
        </div>
    </div>

    <!-- LOCATION: structured fields for the custom landing template -->
    <div class="card mb-3" id="block-location">
        <div class="card-header">Location Page Template — Dynamic Sections</div>
        <div class="card-body">

            <h6 class="text-primary border-bottom pb-2 mb-3">1. Hero Section</h6>
            <div class="row g-2 mb-3">
                <div class="col-md-12"><label class="form-label small">Hero Banner Image</label>
                    <?php if (!empty($p['banner_image'])): ?>
                        <div class="mb-2"><img src="<?= e(img_url($p['banner_image'])) ?>" style="max-height:120px;border-radius:8px"></div>
                    <?php endif; ?>
                    <input type="hidden" name="banner_image" value="<?= e($p['banner_image']) ?>">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openMediaPicker(selectHeroBannerImage)"><i class="bi bi-image"></i> Select from Library</button>
                        <label class="btn btn-sm btn-outline-secondary mb-0"><i class="bi bi-upload"></i> Upload <input type="file" id="heroUpload" class="form-control" accept="image/*" style="display:none"></label>
                    </div>
                </div>
                <div class="col-md-6"><label class="form-label small">Badge Text</label>
                    <input name="loc[hero_badge]" class="form-control" value="<?= e($td['hero']['badge']) ?>" placeholder="Premier Beauty Education"></div>
                <div class="col-md-6"><label class="form-label small">Hero Title *</label>
                    <input name="loc[hero_title]" class="form-control" value="<?= e($td['hero']['title']) ?>" placeholder="Master the Art of Beauty in Chandigarh"></div>
                <div class="col-md-3"><label class="form-label small">Primary CTA Label</label>
                    <input name="loc[hero_cta1]" class="form-control" value="<?= e($td['hero']['cta1']) ?>"></div>
                <div class="col-md-3"><label class="form-label small">Primary CTA URL</label>
                    <input name="loc[hero_cta1_url]" class="form-control" value="<?= e($td['hero']['cta1_url']) ?>"></div>
                <div class="col-md-3"><label class="form-label small">Secondary CTA Label</label>
                    <input name="loc[hero_cta2]" class="form-control" value="<?= e($td['hero']['cta2']) ?>"></div>
                <div class="col-md-3"><label class="form-label small">Secondary CTA URL</label>
                    <input name="loc[hero_cta2_url]" class="form-control" value="<?= e($td['hero']['cta2_url']) ?>"></div>
            </div>

            <h6 class="text-primary border-bottom pb-2 mb-3">2. Introduction Section</h6>
            <div class="row g-2 mb-3">
                <div class="col-md-12"><label class="form-label small">Heading</label>
                    <input name="loc[intro_heading]" class="form-control" value="<?= e($td['intro']['heading']) ?>"></div>
                <div class="col-md-12"><label class="form-label small">Paragraph 1</label>
                    <textarea name="loc[intro_para1]" class="form-control" rows="3"><?= e($td['intro']['para1']) ?></textarea></div>
                <div class="col-md-12"><label class="form-label small">Paragraph 2</label>
                    <textarea name="loc[intro_para2]" class="form-control" rows="3"><?= e($td['intro']['para2']) ?></textarea></div>
                <div class="col-md-3"><label class="form-label small">Stat 1 Value</label>
                    <input name="loc[stat1_value]" class="form-control" value="<?= e($td['intro']['stat1_value']) ?>" placeholder="22+"></div>
                <div class="col-md-3"><label class="form-label small">Stat 1 Label</label>
                    <input name="loc[stat1_label]" class="form-control" value="<?= e($td['intro']['stat1_label']) ?>" placeholder="Years Legacy"></div>
                <div class="col-md-3"><label class="form-label small">Stat 2 Value</label>
                    <input name="loc[stat2_value]" class="form-control" value="<?= e($td['intro']['stat2_value']) ?>" placeholder="25k+"></div>
                <div class="col-md-3"><label class="form-label small">Stat 2 Label</label>
                    <input name="loc[stat2_label]" class="form-control" value="<?= e($td['intro']['stat2_label']) ?>" placeholder="Students Trained"></div>
                <div class="col-md-12"><label class="form-label small">Intro Image</label>
                    <input type="hidden" name="loc[intro_image_existing]" value="<?= e($td['intro']['image']) ?>">
                    <?php if (!empty($td['intro']['image'])): ?>
                        <div class="mb-2"><img src="<?= e(img_url($td['intro']['image'])) ?>" style="max-height:120px;border-radius:8px"></div>
                    <?php endif; ?>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openMediaPicker(selectIntroImage)"><i class="bi bi-image"></i> Select from Library</button>
                        <label class="btn btn-sm btn-outline-secondary mb-0"><i class="bi bi-upload"></i> Upload <input type="file" id="introUpload" class="form-control" accept="image/*" style="display:none"></label>
                    </div></div>
                <div class="col-md-12"><label class="form-label small">Pull Quote (optional)</label>
                    <input name="loc[intro_quote]" class="form-control" value="<?= e($td['intro']['quote']) ?>" placeholder="NIIB elevates beauty career standards..."></div>
            </div>

            <h6 class="text-primary border-bottom pb-2 mb-3">3. Signature Courses</h6>
            <div class="row g-2 mb-2">
                <div class="col-md-6"><label class="form-label small">Tagline</label>
                    <input name="loc[courses_tagline]" class="form-control" value="<?= e($td['courses']['tagline']) ?>"></div>
                <div class="col-md-6"><label class="form-label small">Heading</label>
                    <input name="loc[courses_heading]" class="form-control" value="<?= e($td['courses']['heading']) ?>"></div>
            </div>
            <div class="mb-2 d-flex align-items-center justify-content-between">
                <label class="form-label small mb-0"><strong>Select Courses to Display</strong> &mdash; pulled live from your Courses module</label>
                <div class="d-flex gap-2">
                    <input type="text" id="course-picker-search" class="form-control form-control-sm" placeholder="Search courses…" style="width:220px">
                    <span class="badge bg-primary align-self-center" id="course-picker-count"><?= count($selected_course_ids) ?> selected</span>
                </div>
            </div>
            <?php if (empty($all_courses)): ?>
                <div class="alert alert-warning small mb-3">No active courses found. <a href="<?= ADMIN_URL ?>/modules/courses.php?action=add" target="_blank">Add a course →</a></div>
            <?php else: ?>
            <div id="course-picker" class="row g-2 mb-3" style="max-height:380px;overflow-y:auto;border:1px solid #e5e2e1;border-radius:6px;padding:10px;background:#fafafa">
                <?php foreach ($all_courses as $cs):
                    $cid = (int)$cs['id'];
                    $checked = in_array($cid, $selected_course_ids, true);
                ?>
                <div class="col-md-6 course-picker-item" data-title="<?= e(strtolower($cs['title'].' '.$cs['category_name'])) ?>">
                    <label class="d-flex align-items-center gap-2 p-2 border rounded bg-white" style="cursor:pointer;<?= $checked?'border-color:#0d6efd;background:#e7f0ff':'' ?>">
                        <input type="checkbox" name="course_ids[]" value="<?= $cid ?>" class="form-check-input m-0 course-picker-cb" <?= $checked?'checked':'' ?>>
                        <?php if (!empty($cs['image'])): ?>
                            <img src="<?= e(img_url($cs['image'])) ?>" style="width:42px;height:42px;object-fit:cover;border-radius:4px">
                        <?php else: ?>
                            <div style="width:42px;height:42px;background:#e5e2e1;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#999"><i class="bi bi-image"></i></div>
                        <?php endif; ?>
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="text-truncate fw-semibold small"><?= e($cs['title']) ?></div>
                            <div class="text-truncate text-muted" style="font-size:11px"><?= e($cs['category_name'] ?: '—') ?></div>
                        </div>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
            <small class="text-muted d-block mb-4">Courses display in the order they appear here (featured first). Title, image and link are pulled automatically from the Courses module &mdash; edit there to keep them in sync.</small>
            <?php endif; ?>

            <h6 class="text-primary border-bottom pb-2 mb-3">4. Career Paths</h6>
            <div class="row g-2 mb-2">
                <div class="col-md-6"><label class="form-label small">Heading</label>
                    <input name="loc[career_heading]" class="form-control" value="<?= e($td['career']['heading']) ?>"></div>
                <div class="col-md-6"><label class="form-label small">Subheading</label>
                    <input name="loc[career_subheading]" class="form-control" value="<?= e($td['career']['subheading']) ?>"></div>
                <div class="col-md-2"><label class="form-label small">Feature Icon (Material)</label>
                    <input name="loc[career_feature_icon]" class="form-control form-control-sm" value="<?= e($td['career']['feature_icon']) ?>" placeholder="brush"></div>
                <div class="col-md-4"><label class="form-label small">Feature Card Title</label>
                    <input name="loc[career_feature_title]" class="form-control form-control-sm" value="<?= e($td['career']['feature_title']) ?>"></div>
                <div class="col-md-6"><label class="form-label small">Feature Card Description</label>
                    <input name="loc[career_feature_desc]" class="form-control form-control-sm" value="<?= e($td['career']['feature_desc']) ?>"></div>
            </div>
            <div id="career-repeater" class="repeater-block mb-3">
                <?php
                $careerCards = $td['career']['cards'];
                if (empty($careerCards)) $careerCards = [['icon'=>'','title'=>'','desc'=>'']];
                foreach ($careerCards as $cc): ?>
                <div class="repeater-row border rounded p-2 mb-2 bg-light">
                    <div class="row g-2">
                        <div class="col-md-2"><label class="form-label small">Icon (Material name)</label>
                            <input name="career_card[icon][]" class="form-control form-control-sm" value="<?= e($cc['icon']) ?>" placeholder="storefront"></div>
                        <div class="col-md-3"><label class="form-label small">Title</label>
                            <input name="career_card[title][]" class="form-control form-control-sm" value="<?= e($cc['title']) ?>"></div>
                        <div class="col-md-6"><label class="form-label small">Description</label>
                            <input name="career_card[desc][]" class="form-control form-control-sm" value="<?= e($cc['desc']) ?>"></div>
                        <div class="col-md-1"><label class="form-label small">&nbsp;</label><button type="button" class="btn btn-sm btn-outline-danger remove-row w-100"><i class="bi bi-x"></i></button></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mb-4" data-add-row="career-repeater"><i class="bi bi-plus"></i> Add Card</button>

            <h6 class="text-primary border-bottom pb-2 mb-3">5. Recognition / Gallery</h6>
            <div class="row g-2 mb-3">
                <div class="col-md-6"><label class="form-label small">Heading</label>
                    <input name="loc[gallery_heading]" class="form-control" value="<?= e($td['gallery']['heading']) ?>"></div>
                <div class="col-md-6"><label class="form-label small">Subheading</label>
                    <input name="loc[gallery_subheading]" class="form-control" value="<?= e($td['gallery']['subheading']) ?>"></div>
                <div class="col-md-12">
                    <label class="form-label small">Gallery Images</label>
                    <div id="galleryContainer" class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach (($td['gallery']['images'] ?? []) as $gi): if(!$gi) continue; ?>
                        <div class="position-relative gallery-thumb" style="width:100px;height:80px">
                            <img src="<?= e(img_url($gi)) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:6px">
                            <input type="hidden" name="loc[gallery_existing][]" value="<?= e($gi) ?>">
                            <button type="button" class="btn btn-xs btn-danger" style="position:absolute;top:2px;right:2px;padding:2px 4px;font-size:10px" onclick="this.parentElement.remove()">×</button>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openMediaPicker(selectGalleryImages)"><i class="bi bi-images"></i> Add from Library</button>
                        <label class="btn btn-sm btn-outline-secondary mb-0"><i class="bi bi-upload"></i> Upload <input type="file" id="galleryUpload" class="form-control" multiple accept="image/*" style="display:none"></label>
                    </div>
                </div>
            </div>

            <h6 class="text-primary border-bottom pb-2 mb-3">6. Accreditations</h6>
            <div class="mb-3">
                <label class="form-label small">Accreditation labels (comma-separated)</label>
                <?php
                $accDisplayLabels = [];
                if (!empty($td['accreditations']) && is_array($td['accreditations'])) {
                    foreach ($td['accreditations'] as $acc) {
                        if (is_array($acc)) {
                            $lbl = trim($acc['label'] ?? '');
                            if ($lbl !== '') $accDisplayLabels[] = $lbl;
                        } elseif (is_string($acc)) {
                            if (stripos($acc, 'warning') === false && trim($acc) !== 'Array' && trim($acc) !== '') {
                                $accDisplayLabels[] = trim($acc);
                            }
                        }
                    }
                }
                if (empty($accDisplayLabels)) {
                    $accDisplayLabels = ['NSDC', 'B&WSSC', 'IAO', 'Pathway to Success'];
                }
                ?>
                <input name="loc[accreditations]" class="form-control" value="<?= e(implode(', ', $accDisplayLabels)) ?>" placeholder="NSDC, B&WSSC, IAO, Pathway to Success">
                <small class="text-muted">Standard bodies (NSDC, B&WSSC, IAO, Pathway to Success) automatically display official accreditation badges.</small>
            </div>

            <div class="form-check form-switch mb-4 p-3 bg-light rounded border">
                <input type="hidden" name="loc[extra_sections]" value="0">
                <input type="checkbox" name="loc[extra_sections]" class="form-check-input ms-0 me-2" id="loc_extra" value="1" <?= (!isset($td['extra_sections']) || !empty($td['extra_sections'])) ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold" for="loc_extra">Enable Extended Sections (Matches Patna: 12 Career Opportunities grid, Achievements, Awards, Why Choose NIIB, Testimonials)</label>
            </div>

            <h6 class="text-primary border-bottom pb-2 mb-3">7. Contact / Lead Form</h6>
            <div class="row g-2">
                <div class="col-md-6"><label class="form-label small">Heading</label>
                    <input name="loc[contact_heading]" class="form-control" value="<?= e($td['contact']['heading']) ?>"></div>
                <div class="col-md-6"><label class="form-label small">Subheading</label>
                    <input name="loc[contact_subheading]" class="form-control" value="<?= e($td['contact']['subheading']) ?>"></div>
                <div class="col-md-4"><label class="form-label small">Phone</label>
                    <input name="loc[contact_phone]" class="form-control" value="<?= e($td['contact']['phone']) ?>"></div>
                <div class="col-md-4"><label class="form-label small">Email</label>
                    <input name="loc[contact_email]" class="form-control" value="<?= e($td['contact']['email']) ?>"></div>
                <div class="col-md-4"><label class="form-label small">Address</label>
                    <input name="loc[contact_address]" class="form-control" value="<?= e($td['contact']['address']) ?>"></div>
                <div class="col-md-12"><label class="form-label small">City dropdown options (comma-separated)</label>
                    <input name="loc[contact_cities]" class="form-control" value="<?= e(implode(', ', $td['contact']['cities'])) ?>" placeholder="Chandigarh, Ambala, Ludhiana"></div>
                <div class="col-md-12"><label class="form-label small">Google Maps Link (Optional)</label>
                    <input name="loc[contact_map_link]" class="form-control" value="<?= e($td['contact']['map_link'] ?? '') ?>" placeholder="https://maps.google.com/..."></div>
            </div>

        </div>
    </div>

    <div class="card"><div class="card-header">SEO</div><div class="card-body">
        <div class="mb-3"><label class="form-label">Meta Title</label><input name="meta_title" class="form-control" value="<?= e($p['meta_title']) ?>"></div>
        <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2"><?= e($p['meta_description']) ?></textarea></div>
        <div class="mb-3"><label class="form-label">Meta Keywords</label><input name="meta_keywords" class="form-control" value="<?= e($p['meta_keywords']) ?>"></div>
        <div class="mb-3"><label class="form-label">Canonical URL</label><input name="canonical_url" class="form-control" value="<?= e($p['canonical_url']) ?>"></div>
        <div class="mb-3"><label class="form-label">Schema JSON-LD</label><textarea name="schema_code" class="code-editor" rows="5"><?= e($p['schema_code']) ?></textarea></div>
    </div></div>
</div>

<div class="col-lg-4">
    <div class="card"><div class="card-header">Settings</div><div class="card-body">
        <div class="mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)$p['sort_order'] ?>"></div>
        <div class="form-check form-switch"><input type="checkbox" name="status" class="form-check-input" id="ps" value="1" <?= $p['status']?'checked':'' ?>><label for="ps" class="form-check-label">Active</label></div>
    </div></div>

    <div class="card" id="banner-block-informative"><div class="card-header">Banner Image</div><div class="card-body">
        <?php if ($p['banner_image']): ?><img id="pbp" src="<?= e(img_url($p['banner_image'])) ?>" class="preview-img"><?php else: ?><img id="pbp" class="preview-img" style="display:none"><?php endif; ?>
        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openMediaPicker(selectBannerImage)"><i class="bi bi-image"></i> From Library</button>
            <label class="btn btn-sm btn-outline-secondary mb-0"><i class="bi bi-upload"></i> Upload <input type="file" name="banner_image" class="form-control" accept="image/*" style="display:none"></label>
        </div>
        <small class="text-muted d-block mt-2">For Location pages, use the hero image inside the template section.</small>
    </div></div>

    <button class="btn btn-primary w-100 mt-3"><i class="bi bi-check2-circle"></i> Save Page</button>
</div></div>
</form>

<script>
// Media picker callbacks for all image fields
function selectBannerImage(item) {
    document.querySelector('input[name="banner_image"]').value = item.path;
    const preview = document.getElementById('pbp');
    preview.src = item.url;
    preview.style.display = 'block';
}

function selectHeroBannerImage(item) {
    document.querySelector('input[name="banner_image"]').value = item.path;
    const img = document.querySelector('.row.g-2.mb-3 img');
    if (img) {
        img.src = item.url;
        img.style.display = 'block';
    } else {
        const container = document.querySelector('.row.g-2.mb-3');
        const newImg = document.createElement('img');
        newImg.src = item.url;
        newImg.style.cssText = 'max-height:120px;border-radius:8px;margin-bottom:10px;display:block';
        container.insertBefore(newImg, container.querySelector('input[type="hidden"]').nextElementSibling);
    }
}

function selectIntroImage(item) {
    document.querySelector('input[name="loc[intro_image_existing]"]').value = item.path;
    const container = document.querySelector('input[name="loc[intro_image_existing]"]').closest('.col-md-12');
    let img = container.querySelector('img');
    if (!img) {
        img = document.createElement('img');
        img.style.cssText = 'max-height:120px;border-radius:8px;margin-bottom:10px;display:block;margin-top:2px';
        container.querySelector('input[type="hidden"]').parentElement.insertBefore(img, container.querySelector('input[type="hidden"]').nextElementSibling);
    }
    img.src = item.url;
    img.style.display = 'block';
}

function selectGalleryImages(item) {
    const container = document.getElementById('galleryContainer');

    // Check if already exists
    const existing = Array.from(container.querySelectorAll('input[type="hidden"]')).some(inp => inp.value === item.path);
    if (existing) {
        alert('This image is already in the gallery');
        return;
    }

    const thumb = document.createElement('div');
    thumb.className = 'position-relative gallery-thumb';
    thumb.style.cssText = 'width:100px;height:80px';

    const img = document.createElement('img');
    img.src = item.url;
    img.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:6px';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'loc[gallery_existing][]';
    input.value = item.path;

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-xs btn-danger';
    btn.style.cssText = 'position:absolute;top:2px;right:2px;padding:2px 4px;font-size:10px';
    btn.textContent = '×';
    btn.onclick = function() { thumb.remove(); };

    thumb.appendChild(img);
    thumb.appendChild(input);
    thumb.appendChild(btn);
    container.appendChild(thumb);
}

// Handle file uploads
document.addEventListener('DOMContentLoaded', function() {
    // Hero upload
    const heroUpload = document.getElementById('heroUpload');
    if (heroUpload) {
        heroUpload.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.querySelector('input[name="banner_image"]').value = '';
                    const preview = document.querySelector('.row.g-2.mb-3 img');
                    if (preview) preview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Intro upload
    const introUpload = document.getElementById('introUpload');
    if (introUpload) {
        introUpload.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.querySelector('input[name="loc[intro_image_existing]"]').value = '';
                    const container = document.querySelector('input[name="loc[intro_image_existing]"]').closest('.col-md-12');
                    let img = container.querySelector('img');
                    if (!img) {
                        img = document.createElement('img');
                        img.style.cssText = 'max-height:120px;border-radius:8px;margin-bottom:10px;display:block;margin-top:2px';
                        container.querySelector('input[type="hidden"]').parentElement.insertBefore(img, container.querySelector('input[type="hidden"]').nextElementSibling);
                    }
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<script>
(function(){
    function syncBlocks(){
        var t = document.getElementById('page_type').value;
        document.getElementById('block-informative').style.display = (t==='informative') ? '' : 'none';
        document.getElementById('block-location').style.display    = (t==='location')    ? '' : 'none';
        document.getElementById('banner-block-informative').style.display = (t==='informative') ? '' : 'none';
    }
    document.getElementById('page_type').addEventListener('change', syncBlocks);
    syncBlocks();

    // Repeater rows: add / remove
    document.querySelectorAll('[data-add-row]').forEach(function(btn){
        btn.addEventListener('click', function(){
            var c = document.getElementById(btn.dataset.addRow);
            if (!c) return;
            var rows = c.querySelectorAll('.repeater-row');
            if (rows.length === 0) return;
            var clone = rows[0].cloneNode(true);
            clone.querySelectorAll('input, textarea, select').forEach(function(el){
                if (el.type === 'file') el.value = '';
                else if (el.type === 'hidden') el.value = '';
                else el.value = '';
            });
            // remove preview images in clone
            clone.querySelectorAll('img').forEach(function(im){ im.remove(); });
            c.appendChild(clone);
        });
    });
    // Course picker: search filter + selection count + visual state
    (function(){
        var search = document.getElementById('course-picker-search');
        var picker = document.getElementById('course-picker');
        if (!picker) return;
        var countEl = document.getElementById('course-picker-count');
        function refreshCount(){
            var n = picker.querySelectorAll('.course-picker-cb:checked').length;
            if (countEl) countEl.textContent = n + ' selected';
        }
        picker.addEventListener('change', function(e){
            var cb = e.target.closest('.course-picker-cb');
            if (!cb) return;
            var lbl = cb.closest('label');
            if (cb.checked) { lbl.style.borderColor = '#0d6efd'; lbl.style.background = '#e7f0ff'; }
            else { lbl.style.borderColor = ''; lbl.style.background = ''; }
            refreshCount();
        });
        if (search) {
            search.addEventListener('input', function(){
                var q = search.value.trim().toLowerCase();
                picker.querySelectorAll('.course-picker-item').forEach(function(it){
                    it.style.display = (!q || it.dataset.title.indexOf(q) !== -1) ? '' : 'none';
                });
            });
        }
    })();

    document.addEventListener('click', function(e){
        var rm = e.target.closest('.remove-row');
        if (!rm) return;
        var row = rm.closest('.repeater-row');
        var block = row && row.parentElement;
        if (block && block.querySelectorAll('.repeater-row').length > 1) row.remove();
        else { // clear instead of delete last
            row.querySelectorAll('input, textarea, select').forEach(function(el){
                if (el.type !== 'hidden') el.value = '';
            });
        }
    });
})();
</script>

<?php } else {
    $rows = $pdo->query("SELECT * FROM pages ORDER BY sort_order ASC, id DESC")->fetchAll();
?>
<div class="page-header"><h4>Pages</h4><a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Page</a></div>
<form method="post" id="bulkForm">
<?= csrf_field() ?>
<input type="hidden" name="bulk_action" id="bulk_action_field" value="">
<div class="card"><div class="card-body">
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <select id="bulkActionSelect" class="form-select form-select-sm" style="width:auto;min-width:160px">
            <option value="">Bulk actions…</option>
            <option value="activate">Activate</option>
            <option value="deactivate">Deactivate</option>
            <option value="delete">Delete</option>
        </select>
        <button type="button" id="bulkApplyBtn" class="btn btn-sm btn-primary"><i class="bi bi-lightning-charge"></i> Apply</button>
        <span class="badge bg-secondary align-self-center" id="bulkCount">0 selected</span>
    </div>
<div class="table-responsive"><table class="table table-hover">
<thead><tr>
    <th style="width:34px"><input type="checkbox" id="checkAll" class="form-check-input" title="Select all"></th>
    <th>Title</th><th>Type</th><th>Slug</th><th>Status</th><th>Actions</th>
</tr></thead><tbody>
<?php foreach ($rows as $r): $pt = $r['page_type'] ?? 'informative'; ?>
<tr>
    <td><input type="checkbox" class="form-check-input row-check" name="ids[]" value="<?= $r['id'] ?>"></td>
    <td><?= e($r['title']) ?></td>
    <td><span class="badge bg-<?= $pt==='location'?'info':'secondary' ?>"><?= e(ucfirst($pt)) ?></span></td>
    <td><code><?= e($r['slug']) ?></code></td>
    <td><?= $r['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
    <td>
        <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= $r['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></a>
        <a class="btn btn-sm btn-outline-success" href="?action=duplicate&id=<?= $r['id'] ?>" title="Duplicate" onclick="return confirm('Create a draft copy of &quot;<?= e(addslashes($r['title'])) ?>&quot;?');"><i class="bi bi-files"></i></a>
        <a class="btn btn-sm btn-outline-warning" href="?action=toggle&id=<?= $r['id'] ?>" title="Toggle Active"><i class="bi bi-power"></i></a>
        <a class="btn btn-sm btn-outline-danger confirm-delete" href="?action=delete&id=<?= $r['id'] ?>" title="Delete"><i class="bi bi-trash"></i></a>
        <a class="btn btn-sm btn-outline-info" target="_blank" href="<?= SITE_URL ?>/<?= e($r['slug']) ?>" title="View"><i class="bi bi-eye"></i></a>
    </td>
</tr>
<?php endforeach; ?>
<?php if (empty($rows)): ?><tr><td colspan="6" class="text-center text-muted py-4">No pages.</td></tr><?php endif; ?>
</tbody></table></div>
</div></div>
</form>
<script>
(function(){
    var form = document.getElementById('bulkForm');
    if (!form) return;
    var checkAll = document.getElementById('checkAll');
    function rowChecks(){ return Array.prototype.slice.call(form.querySelectorAll('.row-check')); }
    function selected(){ return rowChecks().filter(function(c){ return c.checked; }); }
    var countEl = document.getElementById('bulkCount');
    function refresh(){
        var all = rowChecks(), n = selected().length;
        countEl.textContent = n + ' selected';
        countEl.className = 'badge align-self-center ' + (n ? 'bg-primary' : 'bg-secondary');
        checkAll.checked = all.length > 0 && n === all.length;
        checkAll.indeterminate = n > 0 && n < all.length;
    }
    checkAll.addEventListener('change', function(){
        rowChecks().forEach(function(c){ c.checked = checkAll.checked; });
        refresh();
    });
    form.addEventListener('change', function(e){
        if (e.target.classList && e.target.classList.contains('row-check')) refresh();
    });
    document.getElementById('bulkApplyBtn').addEventListener('click', function(){
        var action = document.getElementById('bulkActionSelect').value;
        var n = selected().length;
        if (!action) { alert('Choose a bulk action first.'); return; }
        if (n === 0) { alert('Select at least one page using the checkboxes.'); return; }
        var noun = 'page' + (n === 1 ? '' : 's');
        var msg = (action === 'delete')
            ? 'Delete ' + n + ' selected ' + noun + '? This cannot be undone.'
            : (action.charAt(0).toUpperCase() + action.slice(1)) + ' ' + n + ' selected ' + noun + '?';
        if (!confirm(msg)) return;
        document.getElementById('bulk_action_field').value = action;
        form.submit();
    });
    refresh();
})();
</script>
<?php } ?>
<?php include __DIR__.'/../includes/media-picker-modal.php'; ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

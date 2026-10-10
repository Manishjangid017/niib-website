<?php
/**
 * Simple CRUD helper for admin modules with similar patterns.
 * Provides save / delete / toggle / list helpers reused across modules.
 */

if (!defined('NIIB_INIT')) { http_response_code(403); exit; }

function crud_save($table, $allowedFields, $files = [], $required = []) {
    $pdo = db();
    $id  = (int)($_POST['id'] ?? 0);
    $data = [];
    foreach ($allowedFields as $f) {
        if (is_array($f)) {
            // ['name'=>'is_x','default'=>0]
            $name = $f['name'];
            $data[$name] = isset($_POST[$name]) ? (int)$_POST[$name] : ($f['default'] ?? 0);
        } else {
            $data[$f] = isset($_POST[$f]) ? trim((string)$_POST[$f]) : '';
        }
    }
    foreach ($required as $r) {
        if (empty($data[$r])) throw new Exception(ucfirst(str_replace('_',' ',$r)) . ' is required.');
    }

    // file uploads
    foreach ($files as $field => $opts) {
        $folder = $opts['folder'] ?? 'media';
        $allowed = $opts['allowed'] ?? ['jpg','jpeg','png','gif','webp','svg'];
        if (!empty($_FILES[$field]['name'])) {
            $up = upload_file($field, $folder, $allowed);
            if ($up['ok']) $data[$field] = $up['path'];
        } elseif (!empty($_POST['existing_'.$field])) {
            $data[$field] = $_POST['existing_'.$field];
        }
    }

    // auto slug for unique_slug-required tables
    if (!empty($data['slug']) || isset($data['slug'])) {
        if (in_array('slug', $allowedFields, true) || array_key_exists('slug',$data)) {
            // ok
        }
    }

    if ($id > 0) {
        $sets = []; $vals = [];
        foreach ($data as $k=>$v) { $sets[] = "`$k`=?"; $vals[] = $v; }
        $vals[] = $id;
        $pdo->prepare("UPDATE `$table` SET ".implode(',',$sets)." WHERE id=?")->execute($vals);
        return $id;
    } else {
        $cols = array_keys($data);
        $place = array_fill(0,count($cols),'?');
        $pdo->prepare("INSERT INTO `$table` (`".implode('`,`',$cols)."`) VALUES (".implode(',',$place).")")->execute(array_values($data));
        return (int)$pdo->lastInsertId();
    }
}

function crud_delete($table, $id, $fileFields = []) {
    $id = (int)$id;
    if ($id <= 0) return;
    $pdo = db();
    if ($fileFields) {
        $stmt = $pdo->prepare("SELECT `".implode('`,`',$fileFields)."` FROM `$table` WHERE id=?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if ($r) {
            foreach ($r as $v) delete_uploaded_file($v);
        }
    }
    $pdo->prepare("DELETE FROM `$table` WHERE id=?")->execute([$id]);
}

function crud_toggle($table, $id, $column = 'status') {
    db()->prepare("UPDATE `$table` SET `$column` = 1 - `$column` WHERE id=?")->execute([(int)$id]);
}

function crud_fetch($table, $id) {
    $stmt = db()->prepare("SELECT * FROM `$table` WHERE id=?");
    $stmt->execute([(int)$id]);
    return $stmt->fetch();
}

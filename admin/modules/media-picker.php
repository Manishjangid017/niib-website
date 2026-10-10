<?php
/**
 * Media Picker AJAX Endpoint
 * Returns media library items as JSON for the media picker modal
 */
require __DIR__.'/../includes/auth.php';
$pdo = db();

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';
$type = $_GET['type'] ?? 'image'; // image, all
$page = max(1, (int)($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    // Build query
    $query = "SELECT id, file_name, original_name, file_path, file_type, file_size FROM media_library WHERE 1=1";
    $params = [];

    // Filter by type
    if ($type === 'image') {
        $query .= " AND file_type IN ('jpg','jpeg','png','gif','webp','svg')";
    }

    // Search filter
    if ($search !== '') {
        $query .= " AND (original_name LIKE ? OR file_name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $query .= " ORDER BY id DESC";

    // Get total count
    $countQuery = str_replace(
        "SELECT id, file_name, original_name, file_path, file_type, file_size",
        "SELECT COUNT(*) as cnt",
        $query
    );
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $total = (int)$stmt->fetch()['cnt'];

    // Get paginated results
    $query .= " LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(array_merge($params, [$perPage, $offset]));
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format response
    $media = [];
    foreach ($items as $item) {
        $isImg = in_array(strtolower($item['file_type']), ['jpg','jpeg','png','gif','webp','svg']);
        $media[] = [
            'id' => (int)$item['id'],
            'name' => $item['original_name'],
            'path' => $item['file_path'],
            'url' => img_url($item['file_path']),
            'type' => $item['file_type'],
            'size' => $item['file_size'],
            'isImage' => $isImg,
            'thumbnail' => $isImg ? img_url($item['file_path']) : null,
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $media,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'pages' => ceil($total / $perPage),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}

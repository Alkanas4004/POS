<?php
require_once 'config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR barcode LIKE :search)";
    $params['search'] = "%$search%";
}

if (!empty($category) && $category !== 'all') {
    $sql .= " AND category = :category";
    $params['category'] = $category;
}

$sql .= " ORDER BY name LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$products = $stmt->fetchAll();

// جلب العدد الإجمالي
$countSql = "SELECT COUNT(*) as total FROM products WHERE 1=1";
if (!empty($search)) {
    $countSql .= " AND (name LIKE :search OR barcode LIKE :search)";
}
if (!empty($category) && $category !== 'all') {
    $countSql .= " AND category = :category";
}
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
    if ($key !== 'limit' && $key !== 'offset') {
        $countStmt->bindValue($key, $value);
    }
}
$countStmt->execute();
$total = $countStmt->fetch()['total'];

echo json_encode([
    'success' => true,
    'data' => $products,
    'total' => $total,
    'page' => $page,
    'limit' => $limit
]);
?>

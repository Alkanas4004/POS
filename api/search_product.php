<?php
require_once 'config.php';

$barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';

if (empty($barcode)) {
    echo json_encode(['success' => false, 'message' => 'الباركود مطلوب']);
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = :barcode");
$stmt->execute(['barcode' => $barcode]);
$product = $stmt->fetch();

if ($product) {
    echo json_encode(['success' => true, 'product' => $product]);
} else {
    echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
}
?>

<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف المنتج مطلوب']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    echo json_encode(['success' => true, 'message' => 'تم حذف المنتج بنجاح']);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>

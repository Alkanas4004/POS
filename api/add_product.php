<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

$required = ['barcode', 'name', 'purchase_price', 'selling_price'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "حقل $field مطلوب"]);
        exit();
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO products (barcode, name, description, purchase_price, selling_price, quantity, min_quantity, expiry_date, category) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['barcode'],
        $data['name'],
        $data['description'] ?? '',
        $data['purchase_price'],
        $data['selling_price'],
        $data['quantity'] ?? 0,
        $data['min_quantity'] ?? 5,
        $data['expiry_date'] ?? null,
        $data['category'] ?? ''
    ]);
    
    echo json_encode(['success' => true, 'message' => 'تم إضافة المنتج بنجاح', 'id' => $pdo->lastInsertId()]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>

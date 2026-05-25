<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'السلة فارغة']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // إنشاء الفاتورة
    $invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    $stmt = $pdo->prepare("INSERT INTO sales (invoice_number, subtotal, discount, tax, total_amount, paid_amount, change_amount, payment_method, user_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $invoice_number,
        $data['subtotal'],
        $data['discount'],
        $data['tax'] ?? 0,
        $data['total'],
        $data['paid'],
        $data['change'],
        $data['payment_method'],
        $data['user_id'] ?? 1
    ]);
    
    $sale_id = $pdo->lastInsertId();
    
    // إضافة عناصر الفاتورة وتحديث المخزون
    foreach ($data['cart'] as $item) {
        // إضافة تفاصيل الفاتورة
        $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$sale_id, $item['id'], $item['quantity'], $item['price'], $item['total']]);
        
        // تحديث المخزون
        $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['id']]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'invoice_number' => $invoice_number,
        'message' => 'تمت عملية البيع بنجاح'
    ]);
    
} catch(Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

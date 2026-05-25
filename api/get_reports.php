<?php
require_once 'config.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'dashboard';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$response = [];

switch($type) {
    case 'dashboard':
        // مبيعات اليوم
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $response['today_sales'] = $stmt->fetch()['total'];
        
        // مبيعات الشهر
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE MONTH(created_at) = MONTH(CURDATE())");
        $stmt->execute();
        $response['month_sales'] = $stmt->fetch()['total'];
        
        // عدد المنتجات
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
        $response['total_products'] = $stmt->fetch()['total'];
        
        // منتجات مخزون منخفض
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE quantity <= min_quantity");
        $response['low_stock'] = $stmt->fetch()['total'];
        
        // مبيعات آخر 7 أيام
        $stmt = $pdo->query("SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as total 
                             FROM sales WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                             GROUP BY DATE(created_at) ORDER BY date");
        $response['weekly_sales'] = $stmt->fetchAll();
        
        // أفضل 5 منتجات مبيعاً
        $stmt = $pdo->query("SELECT p.name, SUM(si.quantity) as total_sold 
                             FROM sale_items si JOIN products p ON si.product_id = p.id 
                             GROUP BY p.id ORDER BY total_sold DESC LIMIT 5");
        $response['top_products'] = $stmt->fetchAll();
        break;
        
    case 'sales':
        $stmt = $pdo->prepare("SELECT * FROM sales WHERE DATE(created_at) BETWEEN :start AND :end ORDER BY created_at DESC");
        $stmt->execute(['start' => $start_date, 'end' => $end_date]);
        $response['sales'] = $stmt->fetchAll();
        $response['start_date'] = $start_date;
        $response['end_date'] = $end_date;
        break;
        
    case 'stock':
        $stmt = $pdo->query("SELECT * FROM products ORDER BY quantity ASC");
        $response['products'] = $stmt->fetchAll();
        break;
}

echo json_encode(['success' => true, 'data' => $response]);
?>

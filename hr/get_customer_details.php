<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف العميل مطلوب']);
    exit;
}

$customer_id = $_GET['id'];

try {
    // جلب تفاصيل العميل
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'العميل غير موجود']);
        exit;
    }
    
    // جلب الطلبات (إذا كان الجدول موجود)
    $orders = [];
    $orders_count = 0;
    $total_purchases = 0;
    
    try {
        $stmt = $pdo->prepare("SELECT id, order_date, total_amount, status FROM orders WHERE customer_id = ? ORDER BY order_date DESC LIMIT 10");
        $stmt->execute([$customer_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // حساب إجمالي المشتريات
        $stmt = $pdo->prepare("SELECT COUNT(*) as orders_count, COALESCE(SUM(total_amount), 0) as total_purchases FROM orders WHERE customer_id = ?");
        $stmt->execute([$customer_id]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        $orders_count = $summary['orders_count'];
        $total_purchases = $summary['total_purchases'];
    } catch (Exception $e) {
        // الجدول غير موجود أو خطأ آخر
    }
    
    // جلب المعاملات المالية (إذا كان الجدول موجود)
    $transactions = [];
    try {
        $stmt = $pdo->prepare("SELECT transaction_date, type, amount, description FROM customer_transactions WHERE customer_id = ? ORDER BY transaction_date DESC LIMIT 10");
        $stmt->execute([$customer_id]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // الجدول غير موجود أو خطأ آخر
    }
    
    echo json_encode([
        'success' => true,
        'customer' => $customer,
        'orders' => $orders,
        'transactions' => $transactions,
        'orders_count' => $orders_count,
        'total_purchases' => $total_purchases
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>

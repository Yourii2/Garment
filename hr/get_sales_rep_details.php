<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف المندوب مطلوب']);
    exit;
}

try {
    $sales_rep_id = $_GET['id'];
    
    // جلب بيانات المندوب
    $stmt = $pdo->prepare("SELECT * FROM sales_reps WHERE id = ?");
    $stmt->execute([$sales_rep_id]);
    $sales_rep = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sales_rep) {
        echo json_encode(['success' => false, 'message' => 'المندوب غير موجود']);
        exit;
    }
    
    // جلب إحصائيات المبيعات (إذا كان الجدول موجود)
    $stats = ['sales_count' => 0, 'total_sales' => 0, 'total_commission' => 0, 'average_sale' => 0];
    $recent_sales = [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as sales_count,
                SUM(total_amount) as total_sales,
                SUM(commission_amount) as total_commission,
                AVG(total_amount) as average_sale
            FROM sales 
            WHERE sales_rep_id = ?
        ");
        $stmt->execute([$sales_rep_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // جلب المبيعات الأخيرة
        $stmt = $pdo->prepare("
            SELECT s.*, c.name as customer_name
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            WHERE s.sales_rep_id = ? 
            ORDER BY s.sale_date DESC, s.id DESC 
            LIMIT 10
        ");
        $stmt->execute([$sales_rep_id]);
        $recent_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        // جدول المبيعات غير موجود، استخدم القيم الافتراضية
    }
    
    echo json_encode([
        'success' => true,
        'sales_rep' => $sales_rep,
        'sales_count' => $stats['sales_count'] ?? 0,
        'total_sales' => $stats['total_sales'] ?? 0,
        'total_commission' => $stats['total_commission'] ?? 0,
        'average_sale' => $stats['average_sale'] ?? 0,
        'recent_sales' => $recent_sales
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>

<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

$cutting_order_id = $_GET['cutting_order_id'] ?? 0;
$stage_id = $_GET['stage_id'] ?? 0;

try {
    $stmt = $pdo->prepare("
        SELECT 
            co.quantity_ordered,
            co.cutting_number,
            p.name as product_name,
            COALESCE(SUM(wa.quantity_assigned), 0) as total_assigned
        FROM cutting_orders co
        JOIN products p ON co.product_id = p.id
        LEFT JOIN worker_assignments wa ON co.id = wa.cutting_order_id AND wa.stage_id = ?
        WHERE co.id = ?
        GROUP BY co.id
    ");
    $stmt->execute([$stage_id, $cutting_order_id]);
    $order_info = $stmt->fetch();
    
    if ($order_info) {
        $available = $order_info['quantity_ordered'] - $order_info['total_assigned'];
        
        echo json_encode([
            'success' => true,
            'quantity_ordered' => $order_info['quantity_ordered'],
            'total_assigned' => $order_info['total_assigned'],
            'quantity_available' => $available,
            'cutting_number' => $order_info['cutting_number'],
            'product_name' => $order_info['product_name']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'أمر القص غير موجود']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>
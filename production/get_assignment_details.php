<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف المهمة مطلوب']);
    exit;
}

try {
    $assignment_id = $_GET['id'];
    
    $stmt = $pdo->prepare("
        SELECT 
            wa.*,
            w.full_name as worker_name,
            ms.name as stage_name,
            p.name as product_name,
            p.code as product_code,
            co.cutting_number
        FROM worker_assignments wa
        JOIN workers w ON wa.worker_id = w.id
        JOIN manufacturing_stages ms ON wa.stage_id = ms.id
        JOIN cutting_orders co ON wa.cutting_order_id = co.id
        JOIN products p ON co.product_id = p.id
        WHERE wa.id = ?
    ");
    $stmt->execute([$assignment_id]);
    $assignment = $stmt->fetch();
    
    if ($assignment) {
        echo json_encode([
            'success' => true,
            'worker_name' => $assignment['worker_name'],
            'stage_name' => $assignment['stage_name'],
            'product_name' => $assignment['product_name'],
            'product_code' => $assignment['product_code'],
            'cutting_number' => $assignment['cutting_number'],
            'quantity_completed' => $assignment['quantity_completed'],
            'quantity_finished' => $assignment['quantity_finished'] ?? 0
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'المهمة غير موجودة']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>



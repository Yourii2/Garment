<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

$stage_id = $_GET['stage_id'] ?? 0;
$quantity = $_GET['quantity'] ?? 1;

try {
    $stmt = $pdo->prepare("
        SELECT name, is_paid, cost_per_unit 
        FROM manufacturing_stages 
        WHERE id = ?
    ");
    $stmt->execute([$stage_id]);
    $stage = $stmt->fetch();
    
    if ($stage) {
        $total_amount = $stage['is_paid'] ? ($quantity * $stage['cost_per_unit']) : 0;
        
        echo json_encode([
            'success' => true,
            'stage_name' => $stage['name'],
            'is_paid' => $stage['is_paid'],
            'cost_per_unit' => $stage['cost_per_unit'],
            'total_amount' => $total_amount,
            'message' => $stage['is_paid'] 
                ? "مرحلة مدفوعة الأجر: {$stage['cost_per_unit']} ج.م للقطعة الواحدة. إجمالي الأجر: {$total_amount} ج.م"
                : "مرحلة غير مدفوعة الأجر"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'المرحلة غير موجودة']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>
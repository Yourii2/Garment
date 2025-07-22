<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

$product_id = $_GET['product_id'] ?? 0;

try {
    // جلب مراحل التصنيع المرتبطة بالمنتج
    $stmt = $pdo->prepare("
        SELECT DISTINCT ms.id, ms.name, ms.is_paid, ms.default_cost, ms.sort_order
        FROM manufacturing_stages ms
        LEFT JOIN product_stages ps ON ms.id = ps.stage_id
        WHERE (ps.product_id = ? OR ps.product_id IS NULL) AND ms.is_active = 1
        ORDER BY ms.sort_order ASC, ms.name ASC
    ");
    $stmt->execute([$product_id]);
    $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // إذا لم نجد مراحل مخصصة للمنتج، نجلب جميع المراحل النشطة
    if (empty($stages)) {
        $stmt = $pdo->prepare("
            SELECT id, name, is_paid, default_cost, sort_order
            FROM manufacturing_stages 
            WHERE is_active = 1
            ORDER BY sort_order ASC, name ASC
        ");
        $stmt->execute();
        $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode($stages);
    
} catch (Exception $e) {
    error_log("Error in get_product_stages_list.php: " . $e->getMessage());
    echo json_encode([]);
}
?>




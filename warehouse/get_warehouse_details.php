<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف المخزن مطلوب']);
    exit;
}

try {
    $warehouse_id = $_GET['id'];
    
    // جلب بيانات المخزن الأساسية
    $stmt = $pdo->prepare("SELECT * FROM branches WHERE id = ? AND type = 'warehouse'");
    $stmt->execute([$warehouse_id]);
    $warehouse = $stmt->fetch();
    
    if (!$warehouse) {
        echo json_encode(['success' => false, 'message' => 'المخزن غير موجود']);
        exit;
    }
    
    // حساب الإحصائيات
    $stmt = $pdo->prepare("SELECT COUNT(*) as fabric_count FROM fabric_types WHERE branch_id = ?");
    $stmt->execute([$warehouse_id]);
    $fabric_stats = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as accessory_count FROM accessories WHERE branch_id = ?");
    $stmt->execute([$warehouse_id]);
    $accessory_stats = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT SUM(COALESCE(current_quantity, 0)) as total_quantity FROM fabric_types WHERE branch_id = ?");
    $stmt->execute([$warehouse_id]);
    $quantity_stats = $stmt->fetch();
    
    // إضافة الإحصائيات للمخزن
    $warehouse['fabric_count'] = $fabric_stats['fabric_count'];
    $warehouse['accessory_count'] = $accessory_stats['accessory_count'];
    $warehouse['total_quantity'] = $quantity_stats['total_quantity'] ?? 0;
    
    // جلب عينة من الأقمشة
    $stmt = $pdo->prepare("
        SELECT name, current_quantity, unit
        FROM fabric_types
        WHERE branch_id = ? AND current_quantity > 0
        ORDER BY name LIMIT 5
    ");
    $stmt->execute([$warehouse_id]);
    $fabrics = $stmt->fetchAll();
    
    // جلب عينة من الإكسسوارات
    $stmt = $pdo->prepare("
        SELECT name, current_quantity, unit
        FROM accessories
        WHERE branch_id = ? AND current_quantity > 0
        ORDER BY name LIMIT 5
    ");
    $stmt->execute([$warehouse_id]);
    $accessories = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'warehouse' => $warehouse,
        'fabrics' => $fabrics,
        'accessories' => $accessories
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>


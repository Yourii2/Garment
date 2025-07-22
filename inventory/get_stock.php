<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

try {
    $type = $_GET['type'] ?? '';
    $id = intval($_GET['id'] ?? 0);
    
    if (!$id || !in_array($type, ['fabric', 'accessory'])) {
        throw new Exception('معاملات غير صحيحة');
    }
    
    if ($type === 'fabric') {
        $stmt = $pdo->prepare("SELECT COALESCE(current_quantity, 0) as quantity FROM fabric_types WHERE id = ?");
    } else {
        $stmt = $pdo->prepare("SELECT COALESCE(current_quantity, 0) as quantity FROM accessories WHERE id = ?");
    }
    
    $stmt->execute([$id]);
    $quantity = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'quantity' => number_format($quantity, 2)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
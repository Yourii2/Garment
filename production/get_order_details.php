<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف الأمر مطلوب']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT product_id FROM cutting_orders WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $order = $stmt->fetch();
    
    if ($order) {
        echo json_encode(['success' => true, 'product_id' => $order['product_id']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'الأمر غير موجود']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
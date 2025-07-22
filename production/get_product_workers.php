<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if (!isset($_GET['product_id'])) {
    echo json_encode([]);
    exit;
}

try {
    // البحث في جدول workers
    $stmt = $pdo->prepare("
        SELECT id, name as full_name 
        FROM workers 
        WHERE is_active = 1
        ORDER BY name
    ");
    $stmt->execute();
    $workers = $stmt->fetchAll();
    
    echo json_encode($workers);
} catch (Exception $e) {
    echo json_encode([]);
}
?>


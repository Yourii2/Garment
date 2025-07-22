<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if (!isset($_GET['product_id'])) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

$product_id = $_GET['product_id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            ps.*,
            ms.name as stage_name,
            ms.estimated_time_minutes,
            ms.cost_per_unit
        FROM product_stages ps
        JOIN manufacturing_stages ms ON ps.stage_id = ms.id
        WHERE ps.product_id = ?
        ORDER BY ps.stage_order
    ");
    $stmt->execute([$product_id]);
    $stages = $stmt->fetchAll();
    
    echo json_encode(['stages' => $stages]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
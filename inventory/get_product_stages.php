<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([]);
    exit;
}

$product_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT stage_id FROM product_stages WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $stages = $stmt->fetchAll();
    
    echo json_encode($stages);
} catch (Exception $e) {
    echo json_encode([]);
}
?>
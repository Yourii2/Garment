<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if (isset($_GET['phone'])) {
    $phone = $_GET['phone'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, name, address FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $customer = $stmt->fetch();
        
        if ($customer) {
            echo json_encode([
                'exists' => true,
                'customer' => $customer
            ]);
        } else {
            echo json_encode(['exists' => false]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'رقم الهاتف مطلوب']);
}
?>
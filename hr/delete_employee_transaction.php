<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صحيحة']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $transaction_id = $input['transaction_id'];
    
    if (empty($transaction_id)) {
        echo json_encode(['success' => false, 'message' => 'معرف المعاملة مطلوب']);
        exit;
    }
    
    $stmt = $pdo->prepare("DELETE FROM employee_transactions WHERE id = ?");
    $result = $stmt->execute([$transaction_id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'تم حذف المعاملة بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في حذف المعاملة']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>
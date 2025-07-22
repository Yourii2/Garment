<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صحيحة']);
    exit;
}

try {
    $employee_id = $_POST['employee_id'];
    $transaction_type = $_POST['transaction_type'];
    $amount = $_POST['amount'];
    $transaction_date = $_POST['transaction_date'];
    $description = $_POST['description'] ?? '';
    
    // التحقق من صحة البيانات
    if (empty($employee_id) || empty($transaction_type) || empty($amount) || empty($transaction_date)) {
        echo json_encode(['success' => false, 'message' => 'جميع الحقول مطلوبة']);
        exit;
    }
    
    // التحقق من وجود الموظف
    $stmt = $pdo->prepare("SELECT id FROM employees WHERE id = ?");
    $stmt->execute([$employee_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'الموظف غير موجود']);
        exit;
    }
    
    // إضافة المعاملة
    $stmt = $pdo->prepare("
        INSERT INTO employee_transactions 
        (employee_id, transaction_type, amount, transaction_date, description, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([$employee_id, $transaction_type, $amount, $transaction_date, $description]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'تم إضافة المعاملة بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في إضافة المعاملة']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>
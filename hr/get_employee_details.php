<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف الموظف مطلوب']);
    exit;
}

try {
    $employee_id = $_GET['id'];
    
    // جلب بيانات الموظف
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        echo json_encode(['success' => false, 'message' => 'الموظف غير موجود']);
        exit;
    }
    
    // جلب المعاملات المالية
    $stmt = $pdo->prepare("SELECT * FROM employee_transactions WHERE employee_id = ? ORDER BY transaction_date DESC, id DESC");
    $stmt->execute([$employee_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // حساب الملخص المالي
    $stmt = $pdo->prepare("
        SELECT 
            transaction_type,
            SUM(amount) as total_amount
        FROM employee_transactions 
        WHERE employee_id = ? 
        GROUP BY transaction_type
    ");
    $stmt->execute([$employee_id]);
    $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $financial_summary = [
        'total_salaries' => 0,
        'total_bonuses' => 0,
        'total_deductions' => 0,
        'total_advances' => 0,
        'total_overtime' => 0
    ];
    
    foreach ($summary as $item) {
        switch ($item['transaction_type']) {
            case 'salary':
                $financial_summary['total_salaries'] = $item['total_amount'];
                break;
            case 'bonus':
                $financial_summary['total_bonuses'] = $item['total_amount'];
                break;
            case 'deduction':
                $financial_summary['total_deductions'] = $item['total_amount'];
                break;
            case 'advance':
                $financial_summary['total_advances'] = $item['total_amount'];
                break;
            case 'overtime':
                $financial_summary['total_overtime'] = $item['total_amount'];
                break;
        }
    }
    
    // حساب صافي المستحقات
    $financial_summary['net_amount'] = $financial_summary['total_salaries'] + 
                                     $financial_summary['total_bonuses'] + 
                                     $financial_summary['total_overtime'] - 
                                     $financial_summary['total_deductions'] - 
                                     $financial_summary['total_advances'];
    
    echo json_encode([
        'success' => true,
        'employee' => $employee,
        'transactions' => $transactions,
        'total_salaries' => $financial_summary['total_salaries'],
        'total_bonuses' => $financial_summary['total_bonuses'],
        'total_deductions' => $financial_summary['total_deductions'],
        'total_advances' => $financial_summary['total_advances'],
        'total_overtime' => $financial_summary['total_overtime'],
        'net_amount' => $financial_summary['net_amount']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>
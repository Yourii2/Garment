<?php
require_once '../config/config.php';

try {
    // تحديث الموظفين الموجودين بدون employee_code
    $stmt = $pdo->query("SELECT id FROM employees WHERE employee_code IS NULL OR employee_code = ''");
    $employees = $stmt->fetchAll();
    
    foreach ($employees as $employee) {
        $employee_code = 'EMP' . str_pad($employee['id'], 4, '0', STR_PAD_LEFT);
        
        $updateStmt = $pdo->prepare("UPDATE employees SET employee_code = ? WHERE id = ?");
        $updateStmt->execute([$employee_code, $employee['id']]);
        
        echo "✅ تم تحديث الموظف رقم {$employee['id']} بالكود {$employee_code}<br>";
    }
    
    echo "<br>✅ تم تحديث جميع أكواد الموظفين بنجاح";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
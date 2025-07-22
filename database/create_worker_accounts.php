<?php
require_once '../config/config.php';

try {
    echo "<h3>إنشاء جدول حسابات العمال</h3>";
    
    // إنشاء جدول حسابات العمال
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS worker_accounts (
            id INT PRIMARY KEY AUTO_INCREMENT,
            worker_id INT NOT NULL,
            assignment_id INT,
            amount DECIMAL(10,2) NOT NULL,
            type ENUM('earning', 'deduction', 'advance') NOT NULL,
            description TEXT,
            date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (worker_id) REFERENCES workers(id),
            FOREIGN KEY (assignment_id) REFERENCES worker_assignments(id)
        )
    ");
    echo "✅ تم إنشاء جدول worker_accounts<br>";
    
    // إضافة حقل الرصيد الحالي لجدول العمال
    $pdo->exec("
        ALTER TABLE workers 
        ADD COLUMN current_balance DECIMAL(10,2) DEFAULT 0 AFTER phone
    ");
    echo "✅ تم إضافة حقل الرصيد الحالي لجدول العمال<br>";
    
    echo "<br><strong>✅ تم إعداد نظام حسابات العمال بنجاح</strong>";
    
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "⚠️ الحقول موجودة مسبق<|im_start|>فذ<br>";
    } else {
        echo "❌ خطأ: " . $e->getMessage();
    }
}
?>


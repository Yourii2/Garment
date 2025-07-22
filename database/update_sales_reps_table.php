<?php
require_once '../config/config.php';

try {
    echo "<h3>تحديث جدول المناديب</h3>";
    
    // التحقق من وجود عمود payment_type
    $stmt = $pdo->query("SHOW COLUMNS FROM sales_reps LIKE 'payment_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE sales_reps ADD COLUMN payment_type ENUM('salary_only', 'commission_only', 'salary_commission') NOT NULL DEFAULT 'commission_only'");
        echo "✅ تم إضافة عمود payment_type<br>";
    } else {
        echo "ℹ️ عمود payment_type موجود<br>";
    }
    
    // التحقق من وجود عمود salary_type
    $stmt = $pdo->query("SHOW COLUMNS FROM sales_reps LIKE 'salary_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE sales_reps ADD COLUMN salary_type ENUM('daily', 'weekly', 'monthly') NULL");
        echo "✅ تم إضافة عمود salary_type<br>";
    } else {
        echo "ℹ️ عمود salary_type موجود<br>";
    }
    
    // التحقق من وجود عمود salary_amount
    $stmt = $pdo->query("SHOW COLUMNS FROM sales_reps LIKE 'salary_amount'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE sales_reps ADD COLUMN salary_amount DECIMAL(10,2) DEFAULT 0");
        echo "✅ تم إضافة عمود salary_amount<br>";
    } else {
        echo "ℹ️ عمود salary_amount موجود<br>";
    }
    
    // التحقق من وجود عمود commission_type
    $stmt = $pdo->query("SHOW COLUMNS FROM sales_reps LIKE 'commission_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE sales_reps ADD COLUMN commission_type ENUM('percentage', 'fixed_amount') NULL");
        echo "✅ تم إضافة عمود commission_type<br>";
    } else {
        echo "ℹ️ عمود commission_type موجود<br>";
    }
    
    // التحقق من وجود عمود commission_value
    $stmt = $pdo->query("SHOW COLUMNS FROM sales_reps LIKE 'commission_value'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE sales_reps ADD COLUMN commission_value DECIMAL(10,2) DEFAULT 0");
        echo "✅ تم إضافة عمود commission_value<br>";
    } else {
        echo "ℹ️ عمود commission_value موجود<br>";
    }
    
    // حذف عمود commission_rate القديم إذا كان موجود
    $stmt = $pdo->query("SHOW COLUMNS FROM sales_reps LIKE 'commission_rate'");
    if ($stmt->rowCount() > 0) {
        // نقل البيانات من commission_rate إلى commission_value
        $pdo->exec("UPDATE sales_reps SET commission_value = commission_rate, commission_type = 'percentage' WHERE commission_rate > 0");
        $pdo->exec("ALTER TABLE sales_reps DROP COLUMN commission_rate");
        echo "✅ تم نقل البيانات من commission_rate إلى commission_value وحذف العمود القديم<br>";
    }
    
    // عرض هيكل الجدول الجديد
    echo "<h4>هيكل جدول sales_reps الجديد:</h4>";
    $stmt = $pdo->query("DESCRIBE sales_reps");
    $columns = $stmt->fetchAll();
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})<br>";
    }
    
    echo "<br><strong>تم تحديث الجدول بنجاح</strong>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>